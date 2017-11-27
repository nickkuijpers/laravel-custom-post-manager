<?php

namespace Niku\Cms\Http\Controllers\Cms;

use Niku\Cms\Http\Controllers\CmsController;

class DeletePostController extends CmsController
{
	/**
     * Delete a single post
     */
    public function init($postType, $id)
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

        // If the user can only see his own posts
        if($postTypeModel->userCanOnlySeeHisOwnPosts){
            $where[] = ['post_author', '=', Auth::user()->id];
        }

        // Query only the post type requested
        $where[] = ['post_type', '=', $postTypeModel->identifier];

    	// Where sql to get all posts by post_Type
    	$where[] = ['id', '=', $id];

    	// Find the post
    	$post = $postTypeModel::where($where)->first();

    	// Lets validate if the post exist
    	if(!$post){
	    	return response()->json([
	    		'code' => 'failure',
	    		'message' => 'Post does not exist',
	    	], 422);
    	}

    	// Delete the post
    	$post->delete();

    	// Lets fire events as registered in the post type
        $this->triggerEvent('on_delete', $postTypeModel, $post);

        // Return the response
    	return response()->json([
    		'code' => 'success',
    		'message' => 'Posts succesfully deleted',
    		'object' => $post,
    	], 200);
    }
}
