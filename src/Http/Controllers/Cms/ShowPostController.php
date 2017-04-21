<?php

namespace Niku\Cms\Http\Controllers\Cms;

use Niku\Cms\Http\Controllers\CmsController;

class ShowPostController extends CmsController
{
	/**
     * Display a single post
     */
    public function init($postType, $id)
    {
        // Lets validate if the post type exists and if so, continue.
    	$postTypeModel = $this->getPostType($postType);
    	if(!$postTypeModel){
    		return $this->abort('You are not authorized to do this.');
    	}

        // If the user can only see his own posts
        if($postTypeModel->userCanOnlySeeHisOwnPosts){
            $where[] = ['post_author', '=', Auth::user()->id];
        }

        // Where sql to get all posts by post_Type
        $where[] = ['id', '=', $id];

        if($id == 0){
	        $post = $postTypeModel;
	    } else {
	    	$post = $postTypeModel::where($where)->first();
	    }

	    if(!$post){
        	return $this->abort('Post does not exist.');
        }

        $postmeta = $post->postmeta()->select(['meta_key', 'meta_value'])->get();
        $postmeta = $postmeta->keyBy('meta_key');

        $collection = collect([
            'post' => $post->toArray(),
            'postmeta' => $postmeta->toArray()
        ]);

        // Mergin the collection with the data and custom fields
        $collection['templates'] = $this->mergeCollectionWithView($postTypeModel->templates, $collection);

        // Merge the configuration values
        $collection['config'] = $postTypeModel->config;

        // Returning the full collection
    	return response()->json($collection);
    }

    /**
     * Appending the key added in the config to the array
     * so we can use it very easliy in the component.
     */
    protected function mergeCollectionWithView($view, $collection)
    {
    	$post = $collection['post'];
    	$postmeta = $collection['postmeta'];

    	// Convert the custom fields to a set up
    	foreach($view as $key => $template){
        	if(!empty($template['customFields'])){
	            foreach($template['customFields'] as $ckey => $customField){
	                $view[$key]['customFields'][$ckey]['id'] = $ckey;
	                if(!empty($post)){
	                	if(!empty($postmeta[$ckey])){
	                		$view[$key]['customFields'][$ckey]['value'] = $postmeta[$ckey]['meta_value'];
	                	}
	                }
	            }
	        }
        }

        return $view;
    }
}
