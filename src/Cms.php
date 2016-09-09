<?php

namespace Niku\Cms;

use Illuminate\Support\Facades\Route;
use Niku\Cms\RouteRegistrar;

class Cms
{
     
    /**
     * Get a Cms route registrar.
     *
     * @param  array  $options
     * @return RouteRegistrar
     */
    public static function routes($callback = null, array $options = [])
    {
        $callback = $callback ?: function ($router) {
            $router->all();
        };

        $options = array_merge($options, [
            'namespace' => '\Niku\Cms\Http\Controllers',
        ]);

        Route::group($options, function ($router) use ($callback) {
            $callback(new RouteRegistrar($router));
        });
    } 
}