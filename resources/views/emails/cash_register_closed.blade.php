<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, Helvetica, sans-serif; color:#1f2937;">
    <p>Hola,</p>

    <p>{{ __('cash_register.register_closed_mail_intro') }}</p>

    <table cellpadding="6" style="border-collapse:collapse; font-size:14px;">
        <tr>
            <td style="color:#6b7280;">{{ __('business.business') }}:</td>
            <td><strong>{{ $data['business_name'] ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td style="color:#6b7280;">{{ __('sale.location') }}:</td>
            <td>{{ $data['location_name'] ?? '-' }}</td>
        </tr>
        <tr>
            <td style="color:#6b7280;">{{ __('report.user') }}:</td>
            <td>{{ $data['user_name'] ?? '-' }}</td>
        </tr>
        <tr>
            <td style="color:#6b7280;">{{ __('cash_register.open_time') }}:</td>
            <td>{{ $data['open_time'] ?? '-' }}</td>
        </tr>
        <tr>
            <td style="color:#6b7280;">{{ __('cash_register.register_closed_at') }}:</td>
            <td>{{ $data['close_time'] ?? '-' }}</td>
        </tr>
        @if(isset($data['closing_amount']))
        <tr>
            <td style="color:#6b7280;">{{ __('cash_register.closing_amount') }}:</td>
            <td><strong>{{ $data['closing_amount'] }}</strong></td>
        </tr>
        @endif
    </table>

    <p>{{ __('cash_register.register_closed_mail_attachment') }}</p>

    <p style="color:#6b7280; font-size:12px;">{{ config('app.name', 'Tu Caja') }}</p>
</body>
</html>
