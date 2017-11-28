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
        $this->triggerEvent('on_create', $postTypeModel, $post);

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
        // Validating the postname of the given ID to make sure it can be
        // updated and it is not overriding a other duplicated postname.
        $post = $postTypeModel::where([
            ['post_name', '=', $request->get('post_name')],
            ['post_type', '=', $postTypeModel->identifier]
        ])->select(['post_name'])->first();

        $validationRules = [];

        // Lets validate if a post_name is required. If not, we generate a random string.
        if(!$postTypeModel->disablePostName){

        	// Make sure that only the post_name of the requested post_type is unique
	        $validationRules['post_name'] = [
	        	'required',
	        	Rule::unique('cms_posts')->where(function ($query) use ($postTypeModel) {
				    return $query->where('post_type', $postTypeModel->identifier);
				})
	        ];

        }

        return $this->validate($request, $validationRules);
    }
}
