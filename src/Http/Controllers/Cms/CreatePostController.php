<?php

namespace Niku\Cms\Http\Controllers\Cms;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Niku\Cms\Http\Controllers\CmsController;

class CreatePostController extends CmsController
{
	/**
     * The manager of the database communication for adding and manipulating posts
     */
    public function init(Request $request, $postType)
    {
    	// Lets validate if the post type exists and if so, continue.
    	$postTypeModel = $this->getPostType($postType);
    	if(!$postTypeModel){
    		return $this->abort('You are not authorized to do this.');
        }

        // Check if the post type has a identifier
    	if(empty($postTypeModel->identifier)){
    		return $this->abort('The post type does not have a identifier.');
    	}

    	// Receive the post meta values
        $postmeta = $request->all();

        // Validating the request
        $validationRules = $this->validatePostFields($request->all(), $request, $postTypeModel);

        // Validate the post
        $this->validatePost($postTypeModel, $request, $validationRules);

        // Unset unrequired post meta keys
        $postmeta = $this->removeUnrequiredMetas($postmeta);

        // Getting the post instance where we can add upon
        $post = $postTypeModel;

        // Lets check if we have configured a custom post type identifer
        if(!empty($post->identifier)){
        	$postType = $post->identifier;
        }

        // Saving the post values to the database
    	$post = $this->savePostToDatabase($post, $postTypeModel, $request, $postType);

        // Saving the post meta values to the database
        $this->savePostMetaToDatabase($postmeta, $postTypeModel, $post);

        // Lets fire events as registered in the post type
        $this->triggerEvent('on_create', $postTypeModel, $post->id);

        // Return the response
    	return response()->json([
    		'code' => 'success',
    		'message' => 'Posts succesfully created.',
    		'action' => 'create',
    		'post' => $post
    	], 200);
    }

    /**
     * Validating the creation and change of a post
     */
    protected function validatePost($postTypeModel, $request, $validationRules)
    {
    	// Lets receive the current items from the post type validation array
    	if(array_key_exists('post_name', $validationRules) && !is_array($validationRules['post_name'])){

	    	$exploded = explode('|', $validationRules['post_name']);

	    	$validationRules['post_name'] = [];

	    	foreach($exploded as $key => $value){
	    		$validationRules['post_name'][] = $value;
	    	}
		}

        // Lets validate if a post_name is required.
        if(!$postTypeModel->disablePostName){

        	// Make sure that only the post_name of the requested post_type is unique
	        $validationRules['post_name'][] = 'required';
	        $validationRules['post_name'][] = Rule::unique('cms_posts')->where(function ($query) use ($postTypeModel) {
			    return $query->where('post_type', $postTypeModel->identifier);
			});

        }

        return $this->validate($request, $validationRules);
    }
}
