<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .header { background: #3c8dbc; color: #fff; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .details { background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; padding: 15px; margin: 15px 0; }
        .details dt { font-weight: bold; float: left; width: 120px; clear: left; }
        .details dd { margin-left: 130px; }
        .footer { text-align: center; color: #999; font-size: 12px; padding: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>@lang('ticket::lang.new_ticket')</h2>
    </div>

    <div class="content">
        <p>Se ha creado un nuevo ticket de soporte.</p>

        <div class="details">
            <dl>
                <dt>@lang('ticket::lang.code')</dt>
                <dd>{{ $ticket->code }}</dd>
                <dt>@lang('ticket::lang.subject')</dt>
                <dd>{{ $ticket->subject }}</dd>
                <dt>@lang('ticket::lang.priority')</dt>
                <dd>@lang("ticket::lang.{$ticket->priority}")</dd>
                <dt>@lang('ticket::lang.description')</dt>
                <dd>{{ $ticket->description }}</dd>
                <dt>@lang('ticket::lang.customer')</dt>
                <dd>{{ $ticket->user->user_full_name ?? '' }}</dd>
            </dl>
        </div>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
    </div>
</body>
</html>
