@php
    $tags = $error['tags'] ?? [];
    $palette = ['500' => '#dd4b39', '403' => '#f39c12'];
    $color = $palette[(string) ($error['status'] ?? 500)] ?? '#dd4b39';
@endphp
<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#2c3e50">
<div style="max-width:760px;margin:0 auto;padding:18px">

    <div style="background:{{ $color }};color:#fff;padding:14px 18px;border-radius:6px 6px 0 0">
        <div style="font-size:12px;letter-spacing:1px;opacity:.9">
            [{{ $tag }}] [{{ strtoupper($tags['entorno'] ?? '') }}] [{{ $error['status'] ?? '' }}]
            @if (! empty($tags['modulo'])) [{{ $tags['modulo'] }}] @endif
        </div>
        <div style="font-size:17px;font-weight:700;margin-top:4px">
            {{ class_basename($error['class'] ?? '') }}
        </div>
    </div>

    <div style="background:#fff;padding:18px;border:1px solid #e4e7ea;border-top:0">
        <p style="margin:0 0 14px;font-size:15px;line-height:1.5">{{ $error['message'] ?? '' }}</p>

        <p style="margin:0 0 16px;font-size:13px;color:#7f8c8d">
            <code>{{ $error['file'] ?? '' }}:{{ $error['line'] ?? '' }}</code>
        </p>

        @if ($repeats > 0)
            <div style="background:#fcf8e3;border:1px solid #faebcc;padding:10px;border-radius:4px;margin-bottom:16px;font-size:13px">
                Este error se repitio <strong>{{ $repeats }}</strong> vez(ces) mas desde el ultimo aviso.
            </div>
        @endif

        <table cellpadding="6" cellspacing="0" style="width:100%;border-collapse:collapse;font-size:13px">
            <tbody>
            @foreach ($tags as $key => $value)
                <tr style="border-bottom:1px solid #f0f2f4">
                    <td style="width:130px;color:#7f8c8d;text-transform:capitalize">{{ $key }}</td>
                    <td style="word-break:break-all"><strong>{{ $value }}</strong></td>
                </tr>
            @endforeach
            <tr style="border-bottom:1px solid #f0f2f4">
                <td style="color:#7f8c8d">Huella</td>
                <td><code>{{ $error['fingerprint'] ?? '' }}</code></td>
            </tr>
            <tr>
                <td style="color:#7f8c8d">Fecha</td>
                <td>{{ $error['happened_at'] ?? '' }}</td>
            </tr>
            </tbody>
        </table>

        @if (! empty($error['previous']))
            <h4 style="margin:20px 0 6px;font-size:14px">Excepcion original</h4>
            <p style="margin:0;font-size:13px">
                {{ class_basename($error['previous']['class']) }}: {{ $error['previous']['message'] }}<br>
                <code style="color:#7f8c8d">{{ $error['previous']['file'] }}:{{ $error['previous']['line'] }}</code>
            </p>
        @endif

        @if (! empty($error['request']))
            <h4 style="margin:20px 0 6px;font-size:14px">Datos de la peticion</h4>
            <pre style="background:#f7f7f9;border:1px solid #e4e7ea;padding:10px;border-radius:4px;font-size:12px;white-space:pre-wrap;word-break:break-all">{{ json_encode($error['request'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
        @endif

        @if (! empty($error['trace']))
            <h4 style="margin:20px 0 6px;font-size:14px">Traza</h4>
            <pre style="background:#f7f7f9;border:1px solid #e4e7ea;padding:10px;border-radius:4px;font-size:12px;white-space:pre-wrap;word-break:break-all">@foreach ($error['trace'] as $line){{ $line }}
@endforeach</pre>
        @endif
    </div>

    <div style="padding:12px 4px;font-size:11px;color:#95a5a6">
        {{ $appName }} - alerta automatica del modulo ErrorAlerts.
        Para dejar de recibirlas: ERROR_ALERTS_ENABLED=false
    </div>
</div>
</body>
</html>
