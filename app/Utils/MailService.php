<?php

namespace App\Utils;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Servicio central de envío de correos.
 *
 * - En entornos de desarrollo/pruebas (local, testing, staging) los correos se
 *   REDIRIGEN a una dirección de prueba (MAIL_OVERRIDE_TO), para no enviar a clientes reales.
 * - En producción se envían a los destinatarios reales.
 */
class MailService
{
    /**
     * Entornos en los que se redirige el correo a la dirección de prueba.
     */
    protected function overrideEnvironments(): array
    {
        return (array) config('mail.override_envs', ['local', 'testing', 'staging', 'dev', 'development']);
    }

    /**
     * Resuelve los destinatarios finales según el entorno.
     *
     * @param  string|array  $to
     * @return array
     */
    public function resolveRecipients($to): array
    {
        $to = is_array($to) ? $to : [$to];
        $to = array_values(array_filter(array_map('trim', $to)));

        if (in_array(app()->environment(), $this->overrideEnvironments())) {
            $override = config('mail.override_to');
            if (! empty($override)) {
                return is_array($override) ? $override : [$override];
            }
        }

        return $to;
    }

    /**
     * Envía un Mailable a uno o varios destinatarios (respetando la redirección por entorno).
     *
     * @param  string|array  $to
     * @return bool
     */
    public function send($to, Mailable $mailable): bool
    {
        $recipients = $this->resolveRecipients($to);

        if (empty($recipients)) {
            return false;
        }

        try {
            Mail::to($recipients)->send($mailable);

            return true;
        } catch (\Throwable $e) {
            Log::error('MailService: fallo al enviar correo', [
                'error' => $e->getMessage(),
                'to' => $recipients,
            ]);

            return false;
        }
    }
}
