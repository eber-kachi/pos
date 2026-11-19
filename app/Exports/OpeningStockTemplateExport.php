<?php

namespace App\Exports;

use App\PurchaseLine;
use App\Variation;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Plantilla de stock inicial para una ubicación, ya rellenada con los productos del negocio
 * que administran stock. Si el producto ya tiene stock inicial en esa ubicación, la cantidad
 * y el costo vienen con el valor actual (al reimportar se REEMPLAZA con lo que quede en el archivo).
 *
 * Columnas (el orden lo usa OpeningStockImport):
 *   A SKU | B Producto (informativo) | C Ubicación | D Cantidad | E Costo unitario (antes de impuesto)
 *   | F Lote | G Fecha de vencimiento
 */
class OpeningStockTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    const HEADINGS = [
        'SKU',
        'Producto (no modificar)',
        'Ubicación',
        'Cantidad',
        'Costo unitario (antes de impuesto)',
        'Lote (opcional)',
        'Fecha de vencimiento (opcional)',
    ];

    protected $businessId;

    protected $locationId;

    protected $locationName;

    public function __construct($businessId, $locationId, $locationName)
    {
        $this->businessId = $businessId;
        $this->locationId = $locationId;
        $this->locationName = $locationName;
    }

    public function headings(): array
    {
        return self::HEADINGS;
    }

    public function array(): array
    {
        $variations = Variation::join('products AS P', 'variations.product_id', '=', 'P.id')
            ->join('product_variations AS PV', 'variations.product_variation_id', '=', 'PV.id')
            ->where('P.business_id', $this->businessId)
            ->where('P.enable_stock', 1)
            ->whereNull('variations.deleted_at')
            ->whereNotNull('variations.sub_sku')
            ->orderBy('P.name')
            ->orderBy('variations.id')
            ->select([
                'variations.id',
                'variations.sub_sku',
                'variations.name AS variation_name',
                'variations.default_purchase_price',
                'P.name AS product_name',
                'P.type',
                'PV.name AS product_variation_name',
            ])
            ->get();

        // Stock inicial ya registrado en esta ubicación: variation_id => [cantidad, costo, lote, vencimiento]
        $existentes = PurchaseLine::join('transactions AS T', 'purchase_lines.transaction_id', '=', 'T.id')
            ->where('T.business_id', $this->businessId)
            ->where('T.location_id', $this->locationId)
            ->where('T.type', 'opening_stock')
            ->select('purchase_lines.*')
            ->get()
            ->groupBy('variation_id');

        $rows = [];
        foreach ($variations as $v) {
            $nombre = $v->product_name;
            if ($v->type == 'variable') {
                $nombre .= ' - '.$v->product_variation_name.' - '.$v->variation_name;
            }

            $lineas = $existentes->get($v->id);
            if ($lineas && $lineas->count()) {
                $linea = $lineas->first();
                $cantidad = (float) $lineas->sum('quantity');
                $costo = (float) $linea->purchase_price;
                $lote = $linea->lot_number;
                $vence = ! empty($linea->exp_date) ? \Carbon\Carbon::parse($linea->exp_date)->format(session('business.date_format') ?: 'Y-m-d') : '';
            } else {
                $cantidad = '';
                $costo = (float) $v->default_purchase_price;
                $lote = '';
                $vence = '';
            }

            $rows[] = [$v->sub_sku, $nombre, $this->locationName, $cantidad, $costo, $lote, $vence];
        }

        return $rows;
    }
}
