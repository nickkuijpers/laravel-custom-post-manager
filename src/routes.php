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
    	Route::group(['as' => 'niku-cms.'], function () {

	        Route::get('/123123/{abc}', '\Niku\Cms\Http\Controllers\cmsController@index')->name('post_type');

	    });
    } 
}
