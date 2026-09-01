# Modulo ErrorAlerts

Avisos por correo de los errores de la aplicacion, con etiquetas y sin
interferir con el flujo del usuario.

## Como no interfiere

El proyecto tiene `QUEUE_CONNECTION=sync`, asi que un job en cola se ejecutaria
dentro del request y el SMTP (puerto 465) agregaria segundos a cada error. Para
evitarlo el modulo hace dos cosas:

1. **En el momento del error** solo arma el contexto en memoria y consulta el
   anti-flood en cache. Sin consultas SQL ni IO de red.
2. **El correo se envia en el callback `terminating()` de Laravel**, que corre
   despues de que la respuesta ya salio al navegador. El usuario no espera al
   SMTP en ningun momento.

Si el envio falla, se registra en el log y nunca se propaga: hay un candado que
evita que un error del propio reporter genere un bucle de correos.

## Que se reporta

| Se avisa | No se avisa |
|---|---|
| Excepciones no capturadas (500) | Validaciones de formulario |
| Errores de permisos (403, `AuthorizationException`, `UnauthorizedException`) | 404 y rutas inexistentes |
| Errores de base de datos, conexion, tipos | CSRF expirado, login fallido |
| Errores en comandos de consola | Rate limit |

Todo se ajusta en `Config/config.php` (`report.always`, `report.ignore`,
`report.min_status`).

## Etiquetas

El asunto llega listo para filtrar en Gmail:

```
[TuCaja][PROD][500] QueryException: Unknown column 'location_id'
[TuCaja][LOCAL][403] AuthorizationException: This action is unauthorized
```

Y el cuerpo incluye estas etiquetas automaticas:

| Etiqueta | Contenido |
|---|---|
| entorno | local / production |
| estado | codigo HTTP |
| tipo | clase de la excepcion |
| modulo | el modulo donde nacio el error, o `core` |
| origen | web o consola |
| metodo / url / ruta | de la peticion |
| usuario | id y usuario autenticado, o `invitado` |
| empresa / sucursal | de la sesion |
| ip / agente | del cliente |
| huella | fingerprint para agrupar repeticiones |

Ademas: mensaje, archivo y linea, excepcion original, datos de la peticion
(con las claves sensibles censuradas) y la traza recortada.

## Anti-flood

Un bug en un bucle no llena la bandeja:

- El mismo error (misma huella) no se reenvia hasta pasados
  `throttle_minutes` (10 por defecto). Las repeticiones se cuentan y el
  siguiente correo dice *"se repitio N veces mas"*.
- Tope global de `max_per_hour` correos (30 por defecto).

La huella normaliza los numeros del mensaje, asi que
`Unknown id 45` y `Unknown id 912` se agrupan como el mismo problema.

## Configuracion

En el `.env`:

```dotenv
ERROR_ALERTS_ENABLED=true
ERROR_ALERTS_TO=eber.kachi96@gmail.com
ERROR_ALERTS_TAG=TuCaja
ERROR_ALERTS_THROTTLE_MINUTES=10
ERROR_ALERTS_MAX_PER_HOUR=30

# Vacio = avisa en todos los entornos. Para avisar solo en produccion:
# ERROR_ALERTS_ENVIRONMENTS=production
```

Varios destinatarios: `ERROR_ALERTS_TO=uno@x.com,dos@y.com`

## Probar

```bash
php artisan erroralerts:test          # envia un correo de prueba
php artisan erroralerts:test --real   # lanza una excepcion real
```

## Datos sensibles

Nunca salen en el correo las claves listadas en `config.redact`: contrasenas,
tokens, y las credenciales bancarias del modulo QrPayment (`aes_key`,
`authorization_id`, `account_credit`, datos de tarjeta). Los valores muy largos
se recortan a 500 caracteres.

## Cero cambios en el core

El modulo se engancha con `$handler->reportable(...)` desde su
ServiceProvider. **No se modifica `app/Exceptions/Handler.php`.** El callback no
devuelve `false`, asi que el log normal de Laravel sigue funcionando igual.

## Limitaciones

- Los errores fatales de PHP que no llegan al handler (agotar memoria, timeout
  del proceso) no generan correo. Para eso hace falta un
  `register_shutdown_function`, que no esta implementado.
- Los errores de JavaScript del navegador no se capturan.
- Con `CACHE_DRIVER=file` (el habitual) el anti-flood funciona por servidor. En
  varios servidores conviene usar redis o memcached para compartir el conteo.
