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
    public static function configRoutes($groupConfig)
    {
		$groups = '';
    	$i = 0;
    	foreach($groupConfig['register_groups'] as $key => $value) {
    		$i++;

    		if($i == 1){
    			$groups .= $value;
    		} else {
    			$groups .= '|' . $value;
    		}
    	}

       	Route::group([
       		'middleware' => 'groups:' . $groups
       	], function ($object) {

			Route::post('/config/{group}/show', '\Niku\Cms\Http\Controllers\Config\ShowConfigController@init')->name('show');
			Route::post('/config/{group}/edit', '\Niku\Cms\Http\Controllers\Config\EditConfigController@init')->name('edit');

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
			Route::post('/{post_type}/show/{identifier}', '\Niku\Cms\Http\Controllers\Cms\ShowPostController@init')->name('show');
			Route::get('/{post_type}/show/{identifier}/{method}/{customid?}', '\Niku\Cms\Http\Controllers\Cms\ShowCustomGetPostController@init')->name('show');
			Route::post('/{post_type}/show/{identifier}/{method}/{customid?}', '\Niku\Cms\Http\Controllers\Cms\ShowCustomGetPostController@init')->name('show');
			
			// Taxonomy
			Route::post('/{post_type}/show/{identifier}/taxonomies/{sub_post_type}', '\Niku\Cms\Http\Controllers\Cms\ShowPostTaxonomies@init')->name('show');
			Route::post('/{post_type}/show/{identifier}/posts/{sub_post_type}', '\Niku\Cms\Http\Controllers\Cms\ShowTaxonomyPosts@init')->name('show');
			
			// Crud
			Route::post('/{post_type}/check/{identifier}', '\Niku\Cms\Http\Controllers\Cms\CheckPostController@init')->name('check');
			Route::post('/{post_type}/delete/{identifier}', '\Niku\Cms\Http\Controllers\Cms\DeletePostController@init')->name('delete');
			Route::post('/{post_type}/edit/{identifier}', '\Niku\Cms\Http\Controllers\Cms\EditPostController@init')->name('edit');
			Route::post('/{post_type}/create', '\Niku\Cms\Http\Controllers\Cms\CreatePostController@init')->name('create');
			
			Route::post('/{post_type}/custom/edit/{method?}', '\Niku\Cms\Http\Controllers\Cms\EditCustomPostController@init')->name('create');
			Route::post('/{post_type}/custom/{method?}', '\Niku\Cms\Http\Controllers\Cms\CustomPostController@init')->name('create');
			
			Route::post('/{post_type}/edit/{identifier}/taxonomy', '\Niku\Cms\Http\Controllers\Cms\TaxonomyController@init')->name('taxonomy');
			
			// Single custom field updation
			Route::post('/{post_type}/edit-specific-fields/{identifier}', '\Niku\Cms\Http\Controllers\Cms\SpecificFieldsEditPostController@init')->name('single');
			Route::post('/{post_type}/edit-specific-file/{identifier}/{key}', '\Niku\Cms\Http\Controllers\Cms\SpecificFileEditPostController@init')->name('single');
			
			// Listing the posts
			Route::post('/{post_type}/{method?}/{id?}', '\Niku\Cms\Http\Controllers\Cms\ListPostsController@init')->name('list');
		});
    }
}
