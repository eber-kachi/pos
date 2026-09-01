<?php

namespace Modules\ErrorAlerts\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Throwable;

/**
 * Arma las etiquetas del error y su huella (fingerprint).
 *
 * Todo el trabajo aqui es en memoria: sin consultas ni IO, para que capturar
 * un error no le agregue tiempo perceptible al request.
 */
class ErrorContext
{
    protected array $data;

    protected function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function fromException(Throwable $e): self
    {
        $status = static::statusOf($e);
        $request = request();
        $isHttp = $request !== null && ! app()->runningInConsole();

        $tags = [
            'entorno' => app()->environment(),
            'estado' => $status,
            'tipo' => get_class($e),
            'modulo' => static::moduleOf($e),
            'origen' => $isHttp ? 'web' : 'consola',
        ];

        if ($isHttp) {
            $tags['metodo'] = $request->method();
            $tags['url'] = Str::limit($request->fullUrl(), 300);
            $tags['ruta'] = optional($request->route())->getName() ?: '-';
            $tags['ip'] = $request->ip();
            $tags['agente'] = Str::limit((string) $request->userAgent(), 160);
        } else {
            $tags['comando'] = implode(' ', array_slice((array) ($_SERVER['argv'] ?? []), 1)) ?: '-';
        }

        $tags = array_merge($tags, static::userTags());

        return new self([
            'exception' => $e,
            'status' => $status,
            'class' => get_class($e),
            'message' => static::cleanMessage($e->getMessage()),
            'file' => static::relativePath($e->getFile()),
            'line' => $e->getLine(),
            'fingerprint' => static::fingerprint($e),
            'tags' => $tags,
            'trace' => static::trace($e),
            'request' => $isHttp ? static::requestData($request) : [],
            'previous' => $e->getPrevious() ? [
                'class' => get_class($e->getPrevious()),
                'message' => static::cleanMessage($e->getPrevious()->getMessage()),
                'file' => static::relativePath($e->getPrevious()->getFile()),
                'line' => $e->getPrevious()->getLine(),
            ] : null,
            'happened_at' => now()->toDateTimeString(),
        ]);
    }

    public function get(string $key, $default = null)
    {
        return Arr::get($this->data, $key, $default);
    }

    public function toArray(): array
    {
        $data = $this->data;
        unset($data['exception']);

        return $data;
    }

    /** Asunto con las etiquetas al frente para filtrar en el correo. */
    public function subject(string $tag): string
    {
        return sprintf(
            '[%s][%s][%s] %s: %s',
            $tag,
            strtoupper((string) $this->get('tags.entorno')),
            $this->get('status'),
            class_basename($this->get('class')),
            Str::limit($this->get('message'), 110)
        );
    }

    /**
     * Huella estable del error: agrupa las repeticiones del mismo problema
     * ignorando ids y numeros variables del mensaje.
     */
    public static function fingerprint(Throwable $e): string
    {
        $normalized = preg_replace('/\d+/', 'N', (string) $e->getMessage());

        return substr(md5(get_class($e).'|'.$e->getFile().'|'.$e->getLine().'|'.$normalized), 0, 12);
    }

    protected static function statusOf(Throwable $e): int
    {
        if (method_exists($e, 'getStatusCode')) {
            return (int) $e->getStatusCode();
        }

        if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return 403;
        }

        return 500;
    }

    /** Detecta si el error nace en un modulo, para etiquetarlo. */
    protected static function moduleOf(Throwable $e): string
    {
        $file = str_replace('\\', '/', $e->getFile());

        if (preg_match('#/Modules/([^/]+)/#', $file, $m)) {
            return $m[1];
        }

        foreach (array_slice($e->getTrace(), 0, 15) as $frame) {
            $candidate = str_replace('\\', '/', (string) ($frame['file'] ?? ''));

            if (preg_match('#/Modules/([^/]+)/#', $candidate, $m)) {
                return $m[1];
            }
        }

        return 'core';
    }

    /** Etiquetas de usuario/empresa/sucursal sin tocar la base de datos. */
    protected static function userTags(): array
    {
        $tags = [];

        try {
            if (auth()->hasUser()) {
                $user = auth()->user();
                $tags['usuario'] = ($user->id ?? '?').' - '.($user->username ?? $user->email ?? '');
            } else {
                $tags['usuario'] = 'invitado';
            }
        } catch (Throwable $e) {
            $tags['usuario'] = 'desconocido';
        }

        try {
            if (session()->isStarted()) {
                $tags['empresa'] = session('user.business_id') ?: '-';
                $tags['sucursal'] = session('qrpayment.location_id') ?: '-';
            }
        } catch (Throwable $e) {
            // sin sesion disponible
        }

        return $tags;
    }

    protected static function trace(Throwable $e): array
    {
        $lines = [];
        $limit = (int) config('erroralerts.trace_lines', 25);

        foreach ($e->getTrace() as $i => $frame) {
            if ($i >= $limit) {
                break;
            }

            $lines[] = sprintf(
                '#%d %s(%s): %s%s%s()',
                $i,
                static::relativePath((string) ($frame['file'] ?? '[internal]')),
                $frame['line'] ?? '-',
                $frame['class'] ?? '',
                $frame['type'] ?? '',
                $frame['function'] ?? ''
            );
        }

        return $lines;
    }

    protected static function requestData($request): array
    {
        if (! config('erroralerts.include_request', true) || ! $request) {
            return [];
        }

        try {
            return static::redact($request->except(config('erroralerts.redact', [])));
        } catch (Throwable $e) {
            return [];
        }
    }

    /** Oculta valores sensibles en cualquier nivel del arreglo. */
    protected static function redact(array $data): array
    {
        $keys = array_map('strtolower', (array) config('erroralerts.redact', []));

        foreach ($data as $key => $value) {
            if (in_array(strtolower((string) $key), $keys, true)) {
                $data[$key] = '***';
                continue;
            }

            if (is_array($value)) {
                $data[$key] = static::redact($value);
            } elseif (is_string($value) && strlen($value) > 500) {
                $data[$key] = Str::limit($value, 500);
            }
        }

        return $data;
    }

    protected static function cleanMessage(string $message): string
    {
        return trim(preg_replace('/\s+/', ' ', $message));
    }

    protected static function relativePath(string $path): string
    {
        return str_replace(base_path().DIRECTORY_SEPARATOR, '', $path);
    }
}
