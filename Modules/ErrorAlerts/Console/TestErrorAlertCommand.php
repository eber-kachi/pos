<?php

namespace Modules\ErrorAlerts\Console;

use Illuminate\Console\Command;
use Modules\ErrorAlerts\Support\ErrorContext;
use Modules\ErrorAlerts\Support\ErrorReporter;

class TestErrorAlertCommand extends Command
{
    protected $signature = 'erroralerts:test {--real : Lanza una excepcion real en vez de una simulada}';

    protected $description = 'Envia una alerta de prueba a los destinatarios configurados';

    public function handle(ErrorReporter $reporter): int
    {
        $recipients = (array) config('erroralerts.to', []);

        if (empty($recipients)) {
            $this->error('No hay destinatarios en erroralerts.to');

            return self::FAILURE;
        }

        $this->line('Destinatarios: '.implode(', ', $recipients));
        $this->line('Etiqueta: '.config('erroralerts.tag'));

        $exception = new \RuntimeException('Alerta de prueba del modulo ErrorAlerts. Si recibes este correo, esta todo bien.');

        if ($this->option('real')) {
            throw $exception;
        }

        // Envio directo, sin pasar por el anti-flood.
        $reporter->send(ErrorContext::fromException($exception));

        $this->info('Alerta enviada. Revisa la bandeja (y la carpeta de spam la primera vez).');

        return self::SUCCESS;
    }
}
