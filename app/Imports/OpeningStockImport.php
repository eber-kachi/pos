<?php

namespace App\Imports;

use App\BusinessLocation;
use App\PurchaseLine;
use App\Transaction;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Variation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Importa el stock inicial desde un Excel (plantilla de OpeningStockTemplateExport).
 *
 * Columnas: A SKU | B Producto (se ignora) | C Ubicación | D Cantidad | E Costo unitario (antes de impuesto)
 *           | F Lote | G Fecha de vencimiento
 *
 * Reglas:
 *  - Filas vacías o con la cantidad en blanco se omiten.
 *  - Se valida TODO el archivo primero; si hay errores se muestran todos y no se guarda nada.
 *  - Si el producto ya tiene stock inicial en esa ubicación, se REEMPLAZA (no se suma): el sistema ajusta
 *    solo la diferencia de existencias. No se puede dejar un stock inicial menor a lo ya vendido/ajustado.
 *
 * Uso: $res = (new OpeningStockImport($business_id, $user_id))->importFile($archivo);
 */
class OpeningStockImport
{
    protected $businessId;

    protected $userId;

    protected $productUtil;

    protected $transactionUtil;

    protected $errores = [];

    protected $creados = 0;

    protected $reemplazados = 0;

    protected $sinCambios = 0;

    protected $omitidas = 0;

    /** @var \Illuminate\Support\Collection id => nombre (ubicaciones permitidas) */
    protected $locations;

    public function __construct($businessId, $userId)
    {
        $this->businessId = $businessId;
        $this->userId = $userId;
        $this->productUtil = app(ProductUtil::class);
        $this->transactionUtil = app(TransactionUtil::class);
    }

    public function importFile(UploadedFile $file): array
    {
        $parsed = Excel::toArray([], $file);

        return $this->import($parsed[0] ?? []);
    }

    public function import(array $rows): array
    {
        $this->errores = [];
        $this->creados = $this->reemplazados = $this->sinCambios = $this->omitidas = 0;

        $this->locations = BusinessLocation::forDropdown($this->businessId, false, false, false);

        // Encabezado: debe ser el de la plantilla nueva
        $primera = mb_strtolower(trim((string) ($rows[0][0] ?? '')));
        $segunda = mb_strtolower(trim((string) ($rows[0][1] ?? '')));
        if ($primera === 'sku' && str_starts_with($segunda, 'producto')) {
            array_shift($rows);
        } else {
            return $this->fail('El archivo no tiene el formato esperado. Descargue la plantilla nueva desde esta pantalla y llénela.');
        }

        // ---- Fase 1: validar todo ----
        $items = [];
        $vistos = [];
        foreach ($rows as $i => $row) {
            $fila = $i + 2; // fila real en Excel (con encabezado)
            $item = $this->validarFila($fila, $row, $vistos);
            if ($item) {
                $items[] = $item;
            }
        }

        if (! empty($this->errores)) {
            return $this->result();
        }
        if (empty($items)) {
            return $this->fail('No hay filas con cantidad para importar. Escriba la cantidad en las filas de los productos que quiera cargar.');
        }

        // ---- Fase 2: guardar (todo o nada) ----
        DB::beginTransaction();
        try {
            foreach ($items as $item) {
                $this->guardar($item);
            }
            if (! empty($this->errores)) {
                DB::rollBack();

                return $this->result();
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $this->result();
    }

    protected function validarFila($fila, array $row, array &$vistos)
    {
        $sku = $this->texto($row[0] ?? '');
        $ubicacion = $this->texto($row[2] ?? '');
        $cantidadRaw = $row[3] ?? '';
        $costoRaw = $row[4] ?? '';
        $lote = $this->texto($row[5] ?? '');
        $venceRaw = $row[6] ?? '';

        if ($sku === '' && trim((string) $cantidadRaw) === '') {
            return null; // fila vacía
        }
        if (trim((string) $cantidadRaw) === '') {
            $this->omitidas++;

            return null; // sin cantidad: no se carga
        }

        $errores = [];

        // Producto
        $info = null;
        if ($sku === '') {
            $errores[] = 'falta el SKU';
        } else {
            $info = Variation::where('variations.sub_sku', $sku)
                ->join('products AS P', 'variations.product_id', '=', 'P.id')
                ->leftJoin('tax_rates AS TR', 'P.tax', '=', 'TR.id')
                ->where('P.business_id', $this->businessId)
                ->select(['P.id', 'P.enable_stock', 'variations.id AS variation_id',
                    'TR.amount AS tax_percent', 'TR.id AS tax_id'])
                ->first();
            if (! $info) {
                $errores[] = "no existe un producto con el SKU \"$sku\"";
            } elseif ($info->enable_stock == 0) {
                $errores[] = "el producto \"$sku\" no tiene activada la opción \"Administrar stock\"";
            }
        }

        // Ubicación
        $locationId = null;
        if ($ubicacion === '') {
            if ($this->locations->count() === 1) {
                $locationId = $this->locations->keys()->first();
            } else {
                $errores[] = 'falta la ubicación';
            }
        } else {
            $locationId = $this->locations->search(function ($name) use ($ubicacion) {
                return mb_strtolower(trim($name)) === mb_strtolower($ubicacion);
            });
            if ($locationId === false) {
                $errores[] = "la ubicación \"$ubicacion\" no existe o no tiene permiso para usarla";
                $locationId = null;
            }
        }

        // Cantidad y costo
        $cantidad = $this->numero($cantidadRaw);
        if ($cantidad === null) {
            $errores[] = 'la cantidad no es un número válido';
        } elseif ($cantidad <= 0) {
            $errores[] = 'la cantidad debe ser mayor que 0';
        }

        $costo = null;
        if (trim((string) $costoRaw) === '') {
            $errores[] = 'falta el costo unitario';
        } else {
            $costo = $this->numero($costoRaw);
            if ($costo === null || $costo < 0) {
                $errores[] = 'el costo unitario debe ser un número igual o mayor que 0';
            }
        }

        // Fecha de vencimiento
        $vence = null;
        if (trim((string) $venceRaw) !== '') {
            $vence = $this->fecha($venceRaw);
            if ($vence === null) {
                $formato = session('business.date_format') ?: 'Y-m-d';
                $errores[] = "la fecha de vencimiento no es válida (use el formato $formato)";
            }
        }

        // Duplicados dentro del archivo
        if ($info && $locationId) {
            $clave = $info->variation_id.'|'.$locationId;
            if (isset($vistos[$clave])) {
                $errores[] = "el SKU \"$sku\" está repetido en la fila {$vistos[$clave]} para la misma ubicación";
            } else {
                $vistos[$clave] = $fila;
            }
        }

        if (! empty($errores)) {
            $this->errores[] = "Fila $fila: ".implode('; ', $errores).'.';

            return null;
        }

        return [
            'fila' => $fila,
            'sku' => $sku,
            'info' => $info,
            'location_id' => $locationId,
            'cantidad' => $cantidad,
            'costo' => $costo,
            'lote' => $lote !== '' ? $lote : null,
            'vence' => $vence,
        ];
    }

    /**
     * Crea el stock inicial o, si ya existe, lo reemplaza ajustando solo la diferencia.
     */
    protected function guardar(array $item)
    {
        $info = $item['info'];
        $locationId = $item['location_id'];
        $cantidad = $item['cantidad'];
        $costo = $item['costo'];

        $impuesto = $this->productUtil->calc_percentage($costo, ! empty($info->tax_percent) ? $info->tax_percent : 0);
        $costoConImpuesto = $costo + $impuesto;

        // Transacción de stock inicial del producto en la ubicación
        $transaction = Transaction::where('business_id', $this->businessId)
            ->where('location_id', $locationId)
            ->where('type', 'opening_stock')
            ->where('opening_stock_product_id', $info->id)
            ->first();

        if (! $transaction) {
            $fecha = \Carbon\Carbon::createFromFormat('Y-m-d', session('financial_year.start'))->toDateTimeString();
            $transaction = new Transaction;
            $transaction->type = 'opening_stock';
            $transaction->status = 'received';
            $transaction->opening_stock_product_id = $info->id;
            $transaction->business_id = $this->businessId;
            $transaction->transaction_date = $fecha;
            $transaction->location_id = $locationId;
            $transaction->payment_status = 'paid';
            $transaction->created_by = $this->userId;
            $transaction->total_before_tax = 0;
            $transaction->final_total = 0;
            $transaction->save();
        }

        $lineas = PurchaseLine::where('transaction_id', $transaction->id)
            ->where('variation_id', $info->variation_id)
            ->orderBy('id')
            ->get();

        $datos = [
            'item_tax' => $impuesto,
            'tax_id' => ! empty($info->tax_id) ? $info->tax_id : null,
            'pp_without_discount' => $costo,
            'purchase_price' => $costo,
            'purchase_price_inc_tax' => $costoConImpuesto,
            'exp_date' => $item['vence'],
            'lot_number' => $item['lote'],
        ];

        if ($lineas->isEmpty()) {
            $transaction->purchase_lines()->create($datos + [
                'product_id' => $info->id,
                'variation_id' => $info->variation_id,
                'quantity' => $cantidad,
            ]);
            $this->productUtil->updateProductQuantity($locationId, $info->id, $info->variation_id, $cantidad, 0, null, false);
            $this->creados++;
        } else {
            $linea = $lineas->first();
            $extras = $lineas->slice(1);

            // Si hubo líneas duplicadas (por importaciones anteriores), se unifican en una sola.
            foreach ($extras as $extra) {
                if ($extra->quantity_used > 0) {
                    $this->errores[] = "Fila {$item['fila']}: el SKU \"{$item['sku']}\" tiene stock inicial repetido con movimientos (ventas/ajustes). Edítelo manualmente desde el producto.";

                    return;
                }
            }

            if ($cantidad < $linea->quantity_used) {
                $this->errores[] = "Fila {$item['fila']}: el SKU \"{$item['sku']}\" ya tiene {$linea->quantity_used} unidades vendidas o ajustadas; el stock inicial no puede ser menor ({$cantidad}).";

                return;
            }

            $anterior = (float) $linea->quantity;
            $sinCambios = (float) $anterior === (float) $cantidad
                && (float) $linea->purchase_price === (float) $costo
                && (string) $linea->lot_number === (string) $item['lote']
                && (string) $linea->exp_date === (string) $item['vence']
                && $extras->isEmpty();

            foreach ($extras as $extra) {
                $this->productUtil->decreaseProductQuantity($info->id, $extra->variation_id, $locationId, $extra->quantity);
                $extra->delete();
            }

            $linea->fill($datos);
            $linea->quantity = $cantidad;
            $linea->save();

            if ($cantidad != $anterior) {
                $this->productUtil->updateProductQuantity($locationId, $info->id, $info->variation_id, $cantidad, $anterior, null, false);
            }

            $sinCambios ? $this->sinCambios++ : $this->reemplazados++;
        }

        // Recalcular totales de la transacción
        $total = PurchaseLine::where('transaction_id', $transaction->id)
            ->get()
            ->sum(fn ($l) => $l->purchase_price_inc_tax * $l->quantity);
        $transaction->total_before_tax = $total;
        $transaction->final_total = $total;
        $transaction->save();

        // Ajustar ventas con stock negativo (mismo proceso que el formulario manual)
        $transaction->load('purchase_lines.product');
        $this->transactionUtil->adjustMappingPurchaseSellAfterEditingPurchase('received', $transaction, collect());
        $this->productUtil->adjustStockOverSelling($transaction);
    }

    // ----------------- utilidades -----------------

    protected function texto($valor): string
    {
        if (is_float($valor) && floor($valor) == $valor) {
            $valor = (int) $valor; // SKU numérico: 12345.0 -> 12345
        }

        return trim((string) $valor);
    }

    protected function numero($valor)
    {
        if (is_int($valor) || is_float($valor)) {
            return (float) $valor;
        }
        $valor = trim((string) $valor);
        if ($valor === '') {
            return null;
        }
        if (is_numeric($valor)) {
            return (float) $valor;
        }
        $convertido = $this->productUtil->num_uf($valor);

        return is_numeric($convertido) ? (float) $convertido : null;
    }

    protected function fecha($valor)
    {
        try {
            if (is_numeric($valor) && $valor > 20000) { // fecha de Excel guardada como número
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($valor)->format('Y-m-d');
            }
            $texto = trim((string) $valor);
            foreach (array_filter([session('business.date_format'), 'Y-m-d']) as $formato) {
                $d = \DateTime::createFromFormat('!'.$formato, $texto);
                $err = \DateTime::getLastErrors();
                if ($d && (! $err || ($err['warning_count'] == 0 && $err['error_count'] == 0))) {
                    return $d->format('Y-m-d');
                }
            }
        } catch (\Throwable $e) {
        }

        return null;
    }

    protected function fail($mensaje): array
    {
        $this->errores[] = $mensaje;

        return $this->result();
    }

    protected function result(): array
    {
        if (! empty($this->errores)) {
            $max = 15;
            $mensaje = 'No se importó nada. Corrija estos errores y vuelva a subir el archivo: '
                .implode(' ', array_slice($this->errores, 0, $max));
            if (count($this->errores) > $max) {
                $mensaje .= ' ... y '.(count($this->errores) - $max).' errores más.';
            }
            $exito = false;
        } else {
            $mensaje = "Stock inicial importado: {$this->creados} nuevos, {$this->reemplazados} reemplazados";
            if ($this->sinCambios > 0) {
                $mensaje .= ", {$this->sinCambios} sin cambios";
            }
            if ($this->omitidas > 0) {
                $mensaje .= ", {$this->omitidas} filas omitidas por no tener cantidad";
            }
            $mensaje .= '.';
            $exito = true;
        }

        return [
            'exito' => $exito,
            'creados' => $this->creados,
            'reemplazados' => $this->reemplazados,
            'sin_cambios' => $this->sinCambios,
            'omitidas' => $this->omitidas,
            'errores' => $this->errores,
            'mensaje' => $mensaje,
        ];
    }
}
