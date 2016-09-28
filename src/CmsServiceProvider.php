<?php

namespace Niku\Cms;

use Illuminate\Support\ServiceProvider;

class CmsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register migrations
        // $this->loadMigrationsFrom(__DIR__.'/migrations');

        // Register translations
        $this->loadTranslationsFrom(__DIR__.'/translations', 'niku-cms');

        // Register config
        $this->publishes([
            __DIR__.'/../config/'. 'niku-cms.php' => config_path('niku-cms.php'),
        ], 'niku-cms');

        // Register Vue components
        $this->publishes([
            __DIR__.'/../resources/assets/js/' => base_path('resources/assets/js/vendor/niku-cms'),
            __DIR__.'/../resources/assets/css/' => base_path('resources/assets/sass/vendor/niku-cms'),
        ], 'niku-cms');

        // Register views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'niku-cms');

        // Register copying views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/niku-cms'),
        ], 'niku-cms');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        include __DIR__.'/routes.php';
    }
}
