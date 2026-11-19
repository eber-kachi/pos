<?php

namespace App\Utils;

use Illuminate\Support\Str;

/**
 * Maneja PDFs TEMPORALES: los guarda en storage/app/tmp, permite obtener su
 * ruta/contenido, eliminarlos, y limpiar los antiguos (para el comando diario).
 */
class TempPdfService
{
    /**
     * Carpeta temporal (se crea si no existe).
     */
    public function dir(): string
    {
        $dir = storage_path('app/tmp');
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        return $dir;
    }

    /**
     * Ruta absoluta de un archivo temporal.
     */
    public function path(string $name): string
    {
        return $this->dir().DIRECTORY_SEPARATOR.basename($name);
    }

    /**
     * Guarda el contenido del PDF y devuelve ['name' => ..., 'path' => ...].
     */
    public function store(string $content, ?string $name = null): array
    {
        if (empty($name)) {
            $name = 'tmp_'.date('Ymd_His').'_'.Str::lower(Str::random(6)).'.pdf';
        }
        $name = basename($name);
        $path = $this->path($name);

        file_put_contents($path, $content);

        return ['name' => $name, 'path' => $path];
    }

    /**
     * Devuelve el contenido de un archivo temporal, o null si no existe.
     */
    public function get(string $name): ?string
    {
        $path = $this->path($name);

        return is_file($path) ? file_get_contents($path) : null;
    }

    /**
     * Elimina un archivo temporal.
     */
    public function delete(string $name): bool
    {
        $path = $this->path($name);
        if (is_file($path)) {
            return @unlink($path);
        }

        return false;
    }

    /**
     * Elimina los archivos temporales con más de $minutes minutos de antigüedad.
     * $minutes = 0 elimina todos. Devuelve cuántos borró.
     */
    public function cleanOlderThanMinutes(int $minutes = 60): int
    {
        $dir = $this->dir();
        $deleted = 0;
        $threshold = time() - ($minutes * 60);

        foreach (glob($dir.DIRECTORY_SEPARATOR.'*') as $file) {
            if (! is_file($file)) {
                continue;
            }
            if ($minutes <= 0 || filemtime($file) < $threshold) {
                if (@unlink($file)) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }
}
