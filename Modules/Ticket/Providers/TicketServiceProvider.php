<?php

namespace Modules\Ticket\Providers;

use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\ServiceProvider;

class TicketServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('ticket.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'ticket'
        );
    }

    public function registerViews()
    {
        $viewPath = resource_path('views/modules/ticket');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path.'/modules/ticket';
        }, config('view.paths')), [$sourcePath]), 'ticket');
    }

    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/ticket');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'ticket');
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'ticket');
        }
    }

    public function registerFactories()
    {
        if (! app()->environment('production') && $this->app->runningInConsole()) {
            app(Factory::class)->load(__DIR__.'/../Database/factories');
        }
    }

    public function provides()
    {
        return [];
    }
}
