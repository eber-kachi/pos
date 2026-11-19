<?php

namespace App\Console\Commands;

use App\Utils\TempPdfService;
use Illuminate\Console\Command;

/**
 * Elimina los PDF temporales (storage/app/tmp). Se ejecuta a diario a las 22:00.
 */
class CleanTempPdf extends Command
{
    /**
     * Opción --minutes: solo borra los más antiguos que N minutos (0 = todos).
     *
     * @var string
     */
    protected $signature = 'tmp:clean-pdf {--minutes=60}';

    /**
     * @var string
     */
    protected $description = 'Elimina los archivos PDF temporales de storage/app/tmp';

    public function handle()
    {
        $minutes = (int) $this->option('minutes');

        $deleted = app(TempPdfService::class)->cleanOlderThanMinutes($minutes);

        $this->info("PDF temporales eliminados: {$deleted}");

        return 0;
    }
}
