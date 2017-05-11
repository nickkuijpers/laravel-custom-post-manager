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

        // If the user can only see his own posts
        if($postTypeModel->userCanOnlySeeHisOwnPosts){
            $where[] = ['post_author', '=', Auth::user()->id];
        }

    	// Where sql to get all posts by post_Type
    	$where[] = ['id', '=', $id];

    	// Find the post
    	$post = $postTypeModel::where($where);

    	// Delete the post
    	$post->delete();

    	return response()->json([
    		'code' => 'success',
    		'message' => 'Posts succesfully deleted',
    	], 200);
    }
}
