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

			// Config routes
			Route::post('/niku-cms/config/{group}/show', '\Niku\Cms\Http\Controllers\Config\ShowConfigController@init')->name('show');
			Route::post('/niku-cms/config/{group}/edit', '\Niku\Cms\Http\Controllers\Config\EditConfigController@init')->name('edit');

			// Custom media post creation
			Route::post('/niku-cms/media', '\Niku\Cms\Http\Controllers\MediaController@post')->name('mediamanagerpost');

			// Crud listing all posts by post type
			Route::post('/niku-cms/{post_type}', '\Niku\Cms\Http\Controllers\Cms\ListPostsController@init')->name('list');
			Route::post('/niku-cms/{post_type}/show/{id}', '\Niku\Cms\Http\Controllers\Cms\ShowPostController@init')->name('show');

			// Taxonomy
			Route::post('/niku-cms/{post_type}/show/{id}/posts', '\Niku\Cms\Http\Controllers\Cms\ShowTaxonomyPosts@init')->name('show');
			Route::post('/niku-cms/{post_type}/show/{id}/posts/attach', '\Niku\Cms\Http\Controllers\Cms\AttachPostsTaxonomyController@init')->name('show');

			Route::post('/niku-cms/{post_type}/delete/{id}', '\Niku\Cms\Http\Controllers\Cms\DeletePostController@init')->name('delete');
			Route::post('/niku-cms/{post_type}/edit', '\Niku\Cms\Http\Controllers\Cms\EditPostController@init')->name('edit');
			Route::post('/niku-cms/{post_type}/create', '\Niku\Cms\Http\Controllers\Cms\CreatePostController@init')->name('create');

		});
	}
}
