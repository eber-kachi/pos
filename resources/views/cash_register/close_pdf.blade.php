<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; color:#111827; font-size:12px; }
        h2 { margin:0 0 4px; font-size:16px; }
        h3 { margin:16px 0 4px; font-size:13px; }
        .muted { color:#6b7280; }
        table { width:100%; border-collapse:collapse; margin-top:8px; }
        th, td { border:1px solid #d1d5db; padding:6px 8px; text-align:left; }
        th { background:#f3f4f6; }
        td.num, th.num { text-align:right; }
        .total td, .total th { font-weight:bold; background:#f9fafb; }
        .formula { margin-top:10px; padding:8px 10px; background:#f3f4f6; border:1px solid #d1d5db; border-radius:4px; }
    </style>
</head>
<body>
    @php
        $sym = $currency_symbol !== '' ? $currency_symbol.' ' : '';
        $money = function ($v) use ($sym) { return $sym.number_format((float) $v, 2); };

        $apertura = (float) ($register_details->cash_in_hand ?? 0);
        $reembolso = (float) ($register_details->total_refund ?? 0);
        $gastos = (float) ($register_details->total_expense ?? 0);
        $venta = (float) ($register_details->total_sale ?? 0) + $reembolso; // venta bruta
        $total_calc = $apertura + $venta - $reembolso - $gastos;
    @endphp

    <h2>{{ __('cash_register.register_details') }}</h2>
    <div class="muted">
        {{ $data['business_name'] ?? '' }}
        @if(!empty($data['location_name'])) · {{ $data['location_name'] }} @endif
    </div>

    <table>
        <tr><th style="width:40%">{{ __('report.user') }}</th><td>{{ $data['user_name'] ?? '-' }}</td></tr>
        <tr><th>{{ __('cash_register.open_time') }}</th><td>{{ $data['open_time'] ?? '-' }}</td></tr>
        <tr><th>{{ __('cash_register.register_closed_at') }}</th><td>{{ $data['close_time'] ?? '-' }}</td></tr>
    </table>

    {{-- Detalle por método de pago --}}
    <table>
        <thead><tr><th>Detalle</th><th class="num" style="width:30%">{{ __('sale.total') }}</th></tr></thead>
        <tbody>
            <tr><td>{{ __('cash_register.cash_in_hand') }} (Apertura)</td><td class="num">{{ $money($apertura) }}</td></tr>
            @foreach($payment_types as $key => $label)
                @php($field = 'total_'.$key)
                <tr>
                    <td>{{ $label }}</td>
                    <td class="num">{{ $money($register_details->$field ?? 0) }}</td>
                </tr>
            @endforeach
            <tr class="total"><td>Total ventas</td><td class="num">{{ $money($register_details->total_sale ?? 0) }}</td></tr>
            <tr><td>Total reembolsos</td><td class="num">{{ $money($reembolso) }}</td></tr>
            <tr><td>Total gastos</td><td class="num">{{ $money($gastos) }}</td></tr>
            @if(isset($data['closing_amount']))
                <tr class="total"><td>{{ __('cash_register.closing_amount') }}</td><td class="num">{{ $money($data['closing_amount']) }}</td></tr>
            @endif
        </tbody>
    </table>

    {{-- Fórmula del total esperado --}}
    <div class="formula">
        <strong>Total</strong> = {{ $money($apertura) }} (Apertura) + {{ $money($venta) }} (Venta) − {{ $money($reembolso) }} (Reembolso) − {{ $money($gastos) }} (Gastos) = <strong>{{ $money($total_calc) }}</strong>
    </div>

    {{-- Detalle de los productos vendidos --}}
    @if(!empty($details['product_details']) && count($details['product_details']) > 0)
        <h3>Detalles de los productos vendidos</h3>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>SKU</th>
                    <th class="num">Cantidad</th>
                    <th class="num">{{ __('sale.total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($details['product_details'] as $p)
                    <tr>
                        <td>{{ $p->product_name }}{{ !empty($p->product_variation_name) && $p->product_variation_name !== 'DUMMY' ? ' - '.$p->product_variation_name : '' }}</td>
                        <td>{{ $p->sku }}</td>
                        <td class="num">{{ (float) $p->total_quantity + 0 }}</td>
                        <td class="num">{{ $money($p->total_amount) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Nota de cierre --}}
    <p style="margin-top:12px;"><strong>{{ __('cash_register.closing_note') }}:</strong> {{ $register_details->closing_note ?? '' }}</p>

    <p class="muted" style="margin-top:16px;">{{ config('app.name', 'Tu Caja') }}</p>
</body>
</html>
