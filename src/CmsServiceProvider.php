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
            __DIR__.'/../config/niku-cms.php' => config_path('niku-cms.php'),
        ], 'niku-config');

        // Register the default post types
        $this->publishes([
            __DIR__.'/../PostTypes' => app_path('/Cms/PostTypes'),
        ], 'niku-posttypes');

        $this->publishes([
            __DIR__.'/../ConfigTypes' => app_path('/Cms/ConfigTypes'),
        ], 'niku-posttypes');

    }
}
