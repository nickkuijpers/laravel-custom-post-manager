<?php

namespace Niku\Cms\Http\Controllers\Cms;

use Illuminate\Support\Facades\Auth;
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
    		$errorMessages = 'You are not authorized to do this.';
    		return $this->abort($errorMessages);
    	}

    	// Check if the post type has a identifier
    	if(empty($postTypeModel->identifier)){
    		$errorMessages = 'The post type does not have a identifier.';
    		if(array_has($postTypeModel->errorMessages, 'post_type_identifier_does_not_exist')){
    			$errorMessages = $postTypeModel->errorMessages['post_type_identifier_does_not_exist'];
    		}
    		return $this->abort($errorMessages);
    	}

        // If the user can only see his own posts
        if($postTypeModel->userCanOnlySeeHisOwnPosts){
            $where[] = ['post_author', '=', Auth::user()->id];
        }

    	// Finding the post with the post_name instead of the id
        if($postTypeModel->getPostByPostName){
        	$where[] = ['post_name', '=', $id];
        } else {
        	$where[] = ['id', '=', $id];
        }

        // Query only the post type requested
        $where[] = ['post_type', '=', $postTypeModel->identifier];

    	// Adding a custom query functionality so we can manipulate the find by the config
		if($postTypeModel->appendCustomWhereQueryToCmsPosts){
			foreach($postTypeModel->appendCustomWhereQueryToCmsPosts as $key => $value){
				$where[] = [$value[0], $value[1], $value[2]];
			}
		}

    	// Find the post
    	$post = $postTypeModel::where($where)->first();

    	// Lets validate if the post exist
    	if(!$post){
	    	$errorMessages = 'Post does not exist.';
    		if(array_has($postTypeModel->errorMessages, 'post_does_not_exist')){
    			$errorMessages = $postTypeModel->errorMessages['post_does_not_exist'];
    		}
    		return $this->abort($errorMessages);
    	}

    	// Delete the post
    	$post->delete();

    	// Lets fire events as registered in the post type
        $this->triggerEvent('on_delete', $postTypeModel, $post->id, []);

        $successMessage = 'Post succesfully deleted.';
		if(array_has($postTypeModel->successMessage, 'post_deleted')){
			$successMessage = $postTypeModel->successMessage['post_deleted'];
		}

        // Return the response
    	return response()->json([
    		'code' => 'success',
    		'message' => $successMessage,
    		'post' => [
    			'id' => $post->id,
    			'post_title' => $post->post_title,
    			'post_name' => $post->post_name,
				'status' => $post->status,
				'post_type' => $post->post_type,
				'created_at' => $post->created_at,
				'updated_at' => $post->updated_at,
    		],
    	], 200);
    }
}
