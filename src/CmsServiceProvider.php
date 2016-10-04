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
        // Register migrations only if its laravel 5.3 or heigher
        $laravel = app();
		$version = $laravel::VERSION;
		$version = (float) $version;
		if($version >= 5.3){
			$this->loadMigrationsFrom(__DIR__.'/../database/migrations');
		}

        // Register translations
        $this->loadTranslationsFrom(__DIR__.'/../translations', 'niku-assets');

        // Register config
        $this->publishes([
            __DIR__.'/../config/'. 'niku-cms.php' => config_path('niku-cms.php'),
        ], 'niku-config');

        // Register Vue components
        $this->publishes([
        	__DIR__.'/../resources/assets/js/vendor/skins' => public_path('js/vendor/niku-cms/skins'),
            __DIR__.'/../resources/assets/js/' => base_path('resources/assets/js/vendor/niku-cms'),
            __DIR__.'/../resources/assets/sass/' => base_path('resources/assets/sass/vendor/niku-cms'),
            __DIR__.'/../resources/assets/css/' => base_path('resources/assets/css/vendor/niku-cms'),
        ], 'niku-assets');

        // Register views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'niku-cms');

        // Register copying views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/niku-cms'),
        ], 'niku-assets');
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
