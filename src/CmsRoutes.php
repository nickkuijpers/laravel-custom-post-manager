<?php

namespace Niku\Cms;

use Illuminate\Contracts\Routing\Registrar as Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class CmsRoutes
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

			// Creating and updating posts
			Route::post('/niku-cms/media', '\Niku\Cms\Http\Controllers\mediaController@post')->name('mediamanagerpost');

			// Listing all posts by post type
			Route::post('/niku-cms/{post_type}', '\Niku\Cms\Http\Controllers\Cms\ListPostsController@init')->name('list');

			// Returning the single post result
			Route::post('/niku-cms/{post_type}/show/{id}', '\Niku\Cms\Http\Controllers\Cms\ShowPostController@init')->name('show');

			// Deleting a post
			Route::post('/niku-cms/{post_type}/delete/{id}', '\Niku\Cms\Http\Controllers\Cms\DeletePostController@init')->name('delete');

			// Editting a post
			Route::post('/niku-cms/{post_type}/edit', '\Niku\Cms\Http\Controllers\Cms\EditPostController@init')->name('edit');

			// Creating a post
			Route::post('/niku-cms/{post_type}/create', '\Niku\Cms\Http\Controllers\Cms\CreatePostController@init')->name('create');

		});
	}
}
