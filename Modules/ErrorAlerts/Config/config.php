<?php

return [
    'name' => 'ErrorAlerts',

    'enabled' => env('ERROR_ALERTS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Destinatarios
    |--------------------------------------------------------------------------
    | Separar varios con coma en el .env.
    */
    'to' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ERROR_ALERTS_TO', 'eber.kachi96@gmail.com'))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Etiqueta principal del asunto
    |--------------------------------------------------------------------------
    | El asunto queda: [TAG][ENV][STATUS] Clase: mensaje
    | Ej: [TuCaja][PROD][500] QueryException: Unknown column 'location_id'
    */
    'tag' => env('ERROR_ALERTS_TAG', env('APP_NAME', 'Laravel')),

    /*
    |--------------------------------------------------------------------------
    | Anti-flood
    |--------------------------------------------------------------------------
    | throttle_minutes : un mismo error (mismo fingerprint) no se reenvia
    |                    hasta pasados estos minutos; se cuentan las repeticiones
    |                    y se informan en el siguiente correo.
    | max_per_hour     : tope global de correos por hora, para que un pico no
    |                    llene la bandeja ni bloquee el SMTP.
    */
    'throttle_minutes' => (int) env('ERROR_ALERTS_THROTTLE_MINUTES', 10),
    'max_per_hour' => (int) env('ERROR_ALERTS_MAX_PER_HOUR', 30),

    /*
    |--------------------------------------------------------------------------
    | Que se reporta
    |--------------------------------------------------------------------------
    */
    'report' => [
        // Codigo HTTP minimo a reportar (500 = errores del servidor).
        'min_status' => (int) env('ERROR_ALERTS_MIN_STATUS', 500),

        // Excepciones que igual se reportan aunque su status sea < 500.
        // Aqui entran los errores de permisos, que si interesan.
        'always' => [
            \Illuminate\Auth\Access\AuthorizationException::class,
            \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException::class,
            \Spatie\Permission\Exceptions\UnauthorizedException::class,
        ],

        // Ruido de usuarios: nunca se reporta.
        'ignore' => [
            \Illuminate\Validation\ValidationException::class,
            \Illuminate\Auth\AuthenticationException::class,
            \Illuminate\Session\TokenMismatchException::class,
            \Illuminate\Database\Eloquent\ModelNotFoundException::class,
            \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
            \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,
            \Illuminate\Http\Exceptions\ThrottleRequestsException::class,
        ],

        // Entornos en los que se envian alertas. Vacio = todos.
        'environments' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('ERROR_ALERTS_ENVIRONMENTS', ''))
        ))),
    ],

    /*
    |--------------------------------------------------------------------------
    | Contenido del correo
    |--------------------------------------------------------------------------
    */
    'trace_lines' => (int) env('ERROR_ALERTS_TRACE_LINES', 25),
    'include_request' => env('ERROR_ALERTS_INCLUDE_REQUEST', true),

    // Claves que nunca deben salir en el correo.
    'redact' => [
        'password', 'password_confirmation', 'current_password', 'new_password',
        'token', '_token', 'api_token', 'secret', 'authorization',
        'aes_key', 'authorization_id', 'account_id', 'account_credit',
        'card_number', 'card_security', 'card_month', 'card_year', 'cvv',
    ],
];
