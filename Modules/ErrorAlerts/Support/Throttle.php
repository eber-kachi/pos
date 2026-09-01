<?php

namespace Modules\ErrorAlerts\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Anti-flood basado en cache.
 *
 *  - un mismo fingerprint no se reenvia hasta pasados throttle_minutes
 *  - existe un tope global de correos por hora
 *  - mientras un error esta silenciado se cuentan sus repeticiones, y ese
 *    conteo se informa en el siguiente correo
 */
class Throttle
{
    protected const PREFIX = 'erroralerts:';

    /** Marca el error y dice si toca enviar correo. */
    public function shouldSend(string $fingerprint): bool
    {
        $minutes = max(1, (int) config('erroralerts.throttle_minutes', 10));
        $key = self::PREFIX.'seen:'.$fingerprint;

        try {
            // add() es atomico: solo el primero en la ventana obtiene true.
            $first = Cache::add($key, 1, now()->addMinutes($minutes));
        } catch (\Throwable $e) {
            return true; // sin cache disponible, mejor avisar
        }

        if (! $first) {
            $this->increment($fingerprint);

            return false;
        }

        return $this->underHourlyCap();
    }

    /** Repeticiones acumuladas desde el ultimo correo de este error. */
    public function pullRepeats(string $fingerprint): int
    {
        $key = self::PREFIX.'count:'.$fingerprint;

        try {
            $count = (int) Cache::get($key, 0);
            Cache::forget($key);
        } catch (\Throwable $e) {
            return 0;
        }

        return $count;
    }

    protected function increment(string $fingerprint): void
    {
        $key = self::PREFIX.'count:'.$fingerprint;

        try {
            if (! Cache::has($key)) {
                Cache::put($key, 1, now()->addDay());

                return;
            }

            Cache::increment($key);
        } catch (\Throwable $e) {
            // sin cache: se ignora el conteo
        }
    }

    protected function underHourlyCap(): bool
    {
        $cap = (int) config('erroralerts.max_per_hour', 30);

        if ($cap <= 0) {
            return true;
        }

        $key = self::PREFIX.'hour:'.now()->format('YmdH');

        try {
            if (! Cache::has($key)) {
                Cache::put($key, 1, now()->addHour());

                return true;
            }

            return Cache::increment($key) <= $cap;
        } catch (\Throwable $e) {
            return true;
        }
    }
}
