<?php

namespace Niku\Cms;

use Illuminate\Support\Facades\Route;
use Niku\Cms\CmsRoutes;

class Cms
{
    /**
     * Get a Cms route registrar.
     *
     * @param  array  $options
     * @return RouteRegistrar
     */
    public static function configRoutes($registeredPostTypes)
    {
		// Config routes
		Route::post('/config/{group}/show', '\Niku\Cms\Http\Controllers\Config\ShowConfigController@init')->name('show');
		Route::post('/config/{group}/edit', '\Niku\Cms\Http\Controllers\Config\EditConfigController@init')->name('edit');
    }

    public static function mediaManagerRoutes($route, $name = 'niku-cms')
    {
       Route::group($route, function ($object) use ($name) {

			// Custom media post creation
			Route::post('/media', '\Niku\Cms\Http\Controllers\MediaController@post')->name('mediamanagerpost');
		});
    }

    public static function postTypeRoutes($postTypeConfig = [])
    {		
    	$postTypes = '';
    	$i = 0;
    	foreach($postTypeConfig['register_post_types'] as $key => $value) {
    		$i++;

    		if($i == 1){
    			$postTypes .= $value;
    		} else {
    			$postTypes .= '|' . $value;
    		}
    	}

       	Route::group([
       		'middleware' => 'posttypes:' . $postTypes
       	], function ($object) {

			// Crud listing all posts by post type
			Route::post('/{post_type}', '\Niku\Cms\Http\Controllers\Cms\ListPostsController@init')->name('list');
			Route::post('/{post_type}/show/{id}', '\Niku\Cms\Http\Controllers\Cms\ShowPostController@init')->name('show');

			// Taxonomy
			Route::post('/{post_type}/show/{id}/posts', '\Niku\Cms\Http\Controllers\Cms\ShowTaxonomyPosts@init')->name('show');
			Route::post('/{post_type}/show/{id}/posts/attach', '\Niku\Cms\Http\Controllers\Cms\AttachPostsTaxonomyController@init')->name('show');

			Route::post('/{post_type}/delete/{id}', '\Niku\Cms\Http\Controllers\Cms\DeletePostController@init')->name('delete');
			Route::post('/{post_type}/edit/{id}', '\Niku\Cms\Http\Controllers\Cms\EditPostController@init')->name('edit');
			Route::post('/{post_type}/create', '\Niku\Cms\Http\Controllers\Cms\CreatePostController@init')->name('create');
		});
    }
}
