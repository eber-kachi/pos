<?php

namespace Modules\ErrorAlerts\Providers;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;
use Modules\ErrorAlerts\Console\TestErrorAlertCommand;
use Modules\ErrorAlerts\Support\ErrorReporter;
use Modules\ErrorAlerts\Support\Throttle;

class ErrorAlertsServiceProvider extends ServiceProvider
{
    protected string $moduleNameLower = 'erroralerts';

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', $this->moduleNameLower);

        $this->app->singleton(Throttle::class);
        $this->app->singleton(ErrorReporter::class, fn ($app) => new ErrorReporter($app, $app->make(Throttle::class)));
    }

    public function boot()
    {
        $this->registerViews();

        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path($this->moduleNameLower.'.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([TestErrorAlertCommand::class]);
        }

        $this->hookIntoExceptionHandler();
    }

    /**
     * Se engancha al handler de excepciones sin modificar
     * app/Exceptions/Handler.php.
     *
     * El callback no devuelve false, asi que el log normal de Laravel sigue
     * funcionando igual que antes.
     */
    protected function hookIntoExceptionHandler(): void
    {
        if (! config('erroralerts.enabled', true)) {
            return;
        }

        try {
            $handler = $this->app->make(ExceptionHandler::class);

            if (! method_exists($handler, 'reportable')) {
                return;
            }

            $handler->reportable(function (\Throwable $e) {
                $this->app->make(ErrorReporter::class)->report($e);
            });
        } catch (\Throwable $e) {
            // Si el handler no se puede resolver, la app sigue normal.
        }
    }

    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->moduleNameLower);
        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([$sourcePath => $viewPath], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path.'/modules/'.$this->moduleNameLower;
        }, config('view.paths', [])), [$sourcePath]), $this->moduleNameLower);
    }

    public function provides()
    {
        return [ErrorReporter::class, Throttle::class];
    }
}
