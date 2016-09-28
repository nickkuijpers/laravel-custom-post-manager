<?php

namespace Niku\Cms;

use Illuminate\Contracts\Routing\Registrar as Router;
use Illuminate\Support\Facades\Route;

class RouteRegistrar
{
    /**
     * The router implementation.
     *
     * @var Router
     */
    protected $router;

    /**
     * Create a new route registrar instance.
     *
     * @param  Router  $router
     * @return void
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Register routes for transient tokens, clients, and personal access tokens.
     *
     * @return void
     */
    public function all()
    {
        $this->cmsRoutes();
    }

    /**
     * Register the routes needed for authorization.
     *
     * @return void
     */
    public function cmsRoutes()
    {
    	Route::group([
            'as' => 'niku-cms.',
            'middleware' => 'auth'
        ], function () {

            Route::get('/niku-cmstest/{post_type}', '\Niku\Cms\Http\Controllers\cmsController@test')->name('post_type');
	        Route::get('/niku-cms/{post_type}', '\Niku\Cms\Http\Controllers\cmsController@index')->name('post_type');
            Route::get('/niku-cms/show/{id}', '\Niku\Cms\Http\Controllers\cmsController@show')->name('post_type');
            Route::post('/niku-cms/{post_type}/{action}', '\Niku\Cms\Http\Controllers\cmsController@postManagement')->name('post_type');
            Route::delete('/niku-cms/delete/{id}', '\Niku\Cms\Http\Controllers\cmsController@delete')->name('post_type');

	    });
    }
}
