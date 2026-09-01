<?php

namespace Modules\ErrorAlerts\Support;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\ErrorAlerts\Mail\ErrorAlertMail;
use Throwable;

/**
 * Decide si un error se avisa y lo envia SIN retrasar la respuesta.
 *
 * Como funciona:
 *  1. En el momento del error solo se arma el contexto en memoria y se
 *     consulta el anti-flood (cache). Milisegundos.
 *  2. El envio se agenda con $app->terminating(), que Laravel ejecuta despues
 *     de haber enviado la respuesta al navegador. El usuario nunca espera al
 *     SMTP.
 *  3. Cualquier fallo al enviar se registra en el log y nunca se propaga, con
 *     un candado que evita bucles de reporte.
 */
class ErrorReporter
{
    /** Evita que un error dentro del propio reporter se reporte de nuevo. */
    protected bool $reporting = false;

    /** @var string[] fingerprints ya agendados en este request */
    protected array $queued = [];

    public function __construct(protected Application $app, protected Throttle $throttle)
    {
    }

    public function report(Throwable $e): void
    {
        if ($this->reporting) {
            return;
        }

        try {
            if (! $this->shouldReport($e)) {
                return;
            }

            $context = ErrorContext::fromException($e);
            $fingerprint = $context->get('fingerprint');

            if (isset($this->queued[$fingerprint])) {
                return;
            }

            if (! $this->throttle->shouldSend($fingerprint)) {
                return;
            }

            $this->queued[$fingerprint] = true;

            // Aqui esta la clave: el correo sale despues de la respuesta.
            $this->app->terminating(function () use ($context, $fingerprint) {
                $this->send($context, $this->throttle->pullRepeats($fingerprint));
            });
        } catch (Throwable $inner) {
            $this->logFailure($inner);
        }
    }

    public function send(ErrorContext $context, int $repeats = 0): void
    {
        $recipients = (array) config('erroralerts.to', []);

        if (empty($recipients)) {
            return;
        }

        $this->reporting = true;

        try {
            Mail::to($recipients)->send(new ErrorAlertMail($context, $repeats));
        } catch (Throwable $e) {
            $this->logFailure($e);
        } finally {
            $this->reporting = false;
        }
    }

    public function shouldReport(Throwable $e): bool
    {
        if (! config('erroralerts.enabled', true)) {
            return false;
        }

        $environments = (array) config('erroralerts.report.environments', []);

        if (! empty($environments) && ! in_array($this->app->environment(), $environments, true)) {
            return false;
        }

        foreach ((array) config('erroralerts.report.ignore', []) as $ignored) {
            if (class_exists($ignored) && $e instanceof $ignored) {
                return false;
            }
        }

        foreach ((array) config('erroralerts.report.always', []) as $always) {
            if (class_exists($always) && $e instanceof $always) {
                return true;
            }
        }

        // Los abort(403) del POS llegan como HttpException con ese status.
        $status = method_exists($e, 'getStatusCode') ? (int) $e->getStatusCode() : 500;

        if ($status === 403) {
            return true;
        }

        return $status >= (int) config('erroralerts.report.min_status', 500);
    }

    protected function logFailure(Throwable $e): void
    {
        try {
            Log::warning('[erroralerts] no se pudo enviar la alerta: '.$e->getMessage());
        } catch (Throwable $ignored) {
            // Si ni el log responde, no hay nada mas que hacer.
        }
    }
}
