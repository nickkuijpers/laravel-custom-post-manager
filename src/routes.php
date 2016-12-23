<?php

namespace Niku\Cms;

use Illuminate\Contracts\Routing\Registrar as Router;
use Illuminate\Support\Facades\Auth;
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
			'as' => 'niku-cms.'
		], function () {

			/**
			 * Config pages
			 */
			Route::get('/niku-cms/config/{group}/edit', '\Niku\Cms\Http\Controllers\configController@show')->name('configedit');
			Route::post('/niku-cms/config/{group}/edit', '\Niku\Cms\Http\Controllers\configController@configManager')->name('configsaver');

			// Listing all posts by post type
			Route::get('/niku-cms/{post_type}/{sort_name}/{sort_order}/{take}/{offset}', '\Niku\Cms\Http\Controllers\cmsController@index')->name('list');

			// Returning the single post result
			Route::get('/niku-cms/{post_type}/show/{id}', '\Niku\Cms\Http\Controllers\cmsController@show')->name('show');

			// Deleting a post
			Route::delete('/niku-cms/{post_type}/delete/{id}', '\Niku\Cms\Http\Controllers\cmsController@delete')->name('post_type');

			// Recieving custom fields based on post type and template
			Route::post('/niku-cms/{post_type}/receiveview', '\Niku\Cms\Http\Controllers\cmsController@receiveView')->name('custom_fields');

			// Creating and updating posts
			Route::post('/niku-cms/{post_type}/{action}', '\Niku\Cms\Http\Controllers\cmsController@postManagement')->name('createedit');

			// Creating and updating posts
			Route::post('/niku-cms/media', '\Niku\Cms\Http\Controllers\mediaController@post')->name('mediamanagerpost');
		});
	}
}
