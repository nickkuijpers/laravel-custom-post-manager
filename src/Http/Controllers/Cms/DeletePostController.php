<?php

namespace Niku\Cms\Http\Controllers\Cms;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Niku\Cms\Http\Controllers\CmsController;

class DeletePostController extends CmsController
{
	/**
     * Delete a single post
     */
    public function init(Request $request, $postType, $id)
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

		$post = $this->findPostInstance($postTypeModel, $request, $postType, $id, 'delete_post');
		if(!$post){
			$errorMessages = 'Post does not exist.';
			if(array_has($postTypeModel->errorMessages, 'post_does_not_exist')){
				$errorMessages = $postTypeModel->errorMessages['post_does_not_exist'];
			}
			return $this->abort($errorMessages);
		}

		if($postTypeModel->disableDelete){
        	$errorMessages = 'The post type does not support deleting.';
    		if(array_has($postTypeModel->errorMessages, 'post_type_identifier_does_not_support_deleting')){
    			$errorMessages = $postTypeModel->errorMessages['post_type_identifier_does_not_support_deleting'];
    		}
    		return $this->abort($errorMessages);
		}

		if(method_exists($postTypeModel, 'on_delete_check')){	
			$onCheck = $postTypeModel->on_delete_check($postTypeModel, $post->id, []);			
			if($onCheck['continue'] === false){
				$errorMessages = 'You are not authorized to do this.';
				if(array_key_exists('message', $onCheck)){
					$errorMessages = $onCheck['message'];
				}
				return $this->abort($errorMessages);
			}
		}

    	// Delete the post
    	$post->delete();

    	// Lets fire events as registered in the post type
        $this->triggerEvent('on_delete_event', $postTypeModel, $post->id, []);

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
