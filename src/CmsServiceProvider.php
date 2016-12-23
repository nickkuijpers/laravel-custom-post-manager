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

        // Register config
        $this->publishes([
            __DIR__.'/../config/'. 'niku-cms.php' => config_path('niku-cms.php'),
        ], 'niku-config');
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
