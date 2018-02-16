<?php

namespace Niku\Cms\Http\Controllers\Cms;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Niku\Cms\Http\Controllers\CmsController;

class CreatePostController extends CmsController
{
    public function init(Request $request, $postType)
    {
    	$oldRequest = $request;

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

		if($postTypeModel->disableCreate){
        	$errorMessages = 'The post type does not support creating.';
    		if(array_has($postTypeModel->errorMessages, 'post_type_identifier_does_not_support_create')){
    			$errorMessages = $postTypeModel->errorMessages['post_type_identifier_does_not_support_create'];
    		}
    		return $this->abort($errorMessages);
		}
		
		// Override post meta when we need to skip creation
		if($postTypeModel->skipCreation === false){
			
			$allFieldKeys = collect($this->getValidationsKeys($postTypeModel))->map(function($value, $key){
				return '';
			})->toArray();
 
			// Receive the post meta values
			$postmeta = $request->all();		

			foreach($postmeta as $postmetaKey => $postmetaValue){
				$allFieldKeys[$postmetaKey] = $postmeta[$postmetaKey];
			}
			
			$postmeta = $allFieldKeys;
 
			// Validating the request
			$validationRules = $this->validatePostFields($request->all(), $request, $postTypeModel);

			// Validate the post
			$this->validatePost($postTypeModel, $request, $validationRules);

			 // Getting the post instance where we can add upon
			$post = $postTypeModel;

		} else {
	 
			$allFieldKeys = collect($this->getValidationsKeys($postTypeModel))->map(function($value, $key){
				return '';
			})->toArray();

			$request = $this->resetRequestValues($request);	
			foreach($allFieldKeys as $toSaveKey => $toSaveValue){
				$configValue = $this->getCustomFieldValue($postTypeModel, $postTypeModel, $toSaveKey);
				$request[$toSaveKey] = $configValue;
			}

			$postmeta = $request->all();

			// Getting the post instance where we can add upon
			$post = $postTypeModel;
			$post->status = 'concept';
		}

        // Lets check if we have configured a custom post type identifer
        if(!empty($post->identifier)){
        	$postType = $post->identifier;
        }

        // Saving the post values to the database
    	$post = $this->savePostToDatabase('create', $post, $postTypeModel, $oldRequest);
 
        // Saving the post meta values to the database
		$this->savePostMetaToDatabase($postmeta, $postTypeModel, $post);
		
        // Lets fire events as registered in the post type
        $this->triggerEvent('on_create_event', $postTypeModel, $post->id, $postmeta);

        $successMessage = 'Post succesfully created.';
		if(array_has($postTypeModel->successMessage, 'post_created')){
			$successMessage = $postTypeModel->successMessage['post_created'];
		}

        // Return the response
    	return response()->json([
    		'code' => 'success',
    		'message' => $successMessage,
    		'action' => 'create',
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
        if(!$postTypeModel->disableDefaultPostName){

        	// Make sure that only the post_name of the requested post_type is unique
	        $validationRules['post_name'][] = 'required';
	        $validationRules['post_name'][] = Rule::unique('cms_posts')->where(function ($query) use ($postTypeModel) {
			    return $query->where('post_type', $postTypeModel->identifier);
			});

        }

        return $this->validate($request, $validationRules);
    }
}
