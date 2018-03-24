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

		if(method_exists($postTypeModel, 'override_create_post')){
			return $postTypeModel->override_create_post($request);
		}

		// Override post meta when we need to skip creation
		if($postTypeModel->skipCreation === true){

			$allFieldKeys = collect($this->getValidationsKeys($postTypeModel))->map(function($value, $key){
				return '';
			})->toArray();

			// $request = $this->resetRequestValues($request);
			// foreach($allFieldKeys as $toSaveKey => $toSaveValue){
			// 	$configValue = $this->getCustomFieldValue($postTypeModel, $postTypeModel, $toSaveKey);
			// 	$request[$toSaveKey] = $configValue;
			// }

			$postmeta = $request->all();

			// Getting the post instance where we can add upon
			$post = $postTypeModel;
			$post->status = 'concept';

		} else {

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
		}

        // Lets check if we have configured a custom post type identifer
        if(!empty($post->identifier)){
        	$postType = $post->identifier;
        }

		if(method_exists($postTypeModel, 'on_create_check')){
			$onCheck = $postTypeModel->on_create_check($postTypeModel, $post->id, $postmeta);
			if($onCheck['continue'] === false){
				$errorMessages = 'You are not authorized to do this.';
				if(array_key_exists('message', $onCheck)){
					$errorMessages = $onCheck['message'];
				}
				return $this->abort($errorMessages);
			}
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

		$config = $this->getConfig($postTypeModel);

		$post = [
			'id' => $post->id,
			'post_title' => $post->post_title,
			'post_name' => $post->post_name,
			'status' => $post->status,
			'post_type' => $post->post_type,
			'created_at' => $post->created_at,
			'updated_at' => $post->updated_at,
		];

		if(method_exists($postTypeModel, 'override_create_config_response')){
			$config = $postTypeModel->override_create_config_response($postTypeModel, $post->id, $config, $request);
		}

        // Return the response
    	return response()->json([
			'config' => $config,
    		'code' => 'success',
    		'message' => $successMessage,
    		'action' => 'create',
    		'post' => $post,
    	], 200);
    }

	public function getConfig($postTypeModel)
	{
		// Merge the configuration values
		$config = [];
		if($postTypeModel->config){
			$config = $postTypeModel->config;
		}

        $config = $config;

        // Adding public config
        if($postTypeModel->skipCreation){
			$config['skip_creation'] = $postTypeModel->skipCreation;
			if($postTypeModel->skipToRouteName){
				$config['skip_to_route_name'] = $postTypeModel->skipToRouteName;
			}
        } else {
			$config['skip_creation'] = false;
			$config['skip_to_route_name'] = '';
		}

		// Adding public config
        if($postTypeModel->disableEditOnlyCheck){
        	$config['disable_edit_only_check'] = $postTypeModel->disableEditOnlyCheck;
        } else {
        	$config['disable_edit_only_check'] = false;
		}

		if($postTypeModel->disableEdit){
        	$config['disable_edit'] = $postTypeModel->disableEdit;
        } else {
        	$config['disable_edit'] = false;
		}

		if($postTypeModel->disableDelete){
        	$config['disable_delete'] = $postTypeModel->disableDelete;
        } else {
        	$config['disable_delete'] = false;
		}

		if($postTypeModel->disableCreate){
        	$config['disable_create'] = $postTypeModel->disableCreate;
        } else {
        	$config['disable_create'] = false;
		}

		if($postTypeModel->getPostByPostName){
        	$config['get_post_by_postname'] = $postTypeModel->getPostByPostName;
        } else {
        	$config['get_post_by_postname'] = false;
		}

		$allKeys = collect($this->getValidationsKeys($postTypeModel));

		// Adding public config
        if($postTypeModel->enableAllSpecificFieldsUpdate){
        	$config['specific_fields']['enable_all'] = $postTypeModel->enableAllSpecificFieldsUpdate;
			$config['specific_fields']['exclude_fields'] = $postTypeModel->excludeSpecificFieldsFromUpdate;
			$config['specific_fields']['enabled_fields'] = $allKeys->keys();
        } else {
        	$config['specific_fields']['enable_all'] = $postTypeModel->enableAllSpecificFieldsUpdate;
			$config['specific_fields']['exclude_fields'] = $postTypeModel->excludeSpecificFieldsFromUpdate;
			$config['specific_fields']['enabled_fields'] = $allKeys->where('single_field_updateable.active', 'true')->keys();
		}

		return $config;
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
