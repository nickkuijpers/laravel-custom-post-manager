<?php

namespace Niku\Cms\Http\Controllers\Cms;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Niku\Cms\Http\Controllers\CmsController;

class SpecificFieldsEditPostController extends CmsController
{
	/**
	 * The manager of the database communication for adding and manipulating posts
	 */
	public function init(Request $request, $postType, $id)
	{
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

		// Validate if we need to validate a other post type before showing this post type
		$validateBefore = $this->validatePostTypeBefore($request, $postTypeModel, $id);
		if($validateBefore['status'] === false){
			$errorMessages = $validateBefore['message'];
    		return $this->abort($errorMessages, $validateBefore['config']);
		}

		$sanitizedKeys = $this->getValidationsKeys($postTypeModel);
		foreach($sanitizedKeys as $saniKey => $saniValue){
			$sanitizedKeys[$saniKey] = '';
		}

		$verifiedFields = [];
		$reloadFields = [];
		$reloadFieldsMethod = 'none';

		// For each custom field given, we need to validate the permission
		foreach($request->all() as $key => $value){

			// Lets check if the custom field exists and if we got permission
			$customFieldObject = $this->getCustomFieldObject($postTypeModel, $key);
			if(array_has($customFieldObject, 'single_field_updateable.active')){
				if($customFieldObject['single_field_updateable']['active']){
					$verifiedFields[] = $key;

					if(array_has($customFieldObject, 'single_field_updateable.reload_fields')){
						if(is_array($customFieldObject['single_field_updateable']['reload_fields'])){
							$reloadFieldsMethod = 'specific';
							$reloadFields[] = $customFieldObject['single_field_updateable']['reload_fields'];
						} else if ($customFieldObject['single_field_updateable']['reload_fields'] == '*'){
							$reloadFieldsMethod = 'all';							
							$reloadFields[] = $sanitizedKeys;
						}
					}
				}
			}

		}

		// If updating all specific fields is enabled, we override the verified fields
		if($postTypeModel->enableAllSpecificFieldsUpdate){
			$whitelistedCustomFields = $this->getWhitelistedCustomFields($postTypeModel, $request->all());
	
			$reloadFields = $sanitizedKeys;

			// If there is a exlusion active, lets progress that
			if(is_array($postTypeModel->excludeSpecificFieldsFromUpdate)){
				foreach($postTypeModel->excludeSpecificFieldsFromUpdate as $excludeKey => $excludeValue){
					unset($whitelistedCustomFields[$excludeValue]);
					unset($reloadFields[$excludeValue]);
				}
			}

		} else {
			$whitelistedCustomFields = $this->getWhitelistedCustomFields($postTypeModel, $request->only($verifiedFields));
		}

		$toValidateKeys = [];
		foreach($whitelistedCustomFields as $whiteKey => $whiteValue){
			$customFieldObject = $this->getCustomFieldObject($postTypeModel, $whiteKey);
			if(is_array($customFieldObject)){
				if(array_key_exists('saveable', $customFieldObject)){
					if($customFieldObject['saveable'] === false){

					} else {
						$toValidateKeys[$whiteKey] = $customFieldObject;
					}
				}	 
			}
		}

		// Validating the request
		$validationRules = $this->validatePostFields($toValidateKeys, $request, $postTypeModel, true);
		
		// Unset unrequired post meta keys
		$whitelistedCustomFields = $this->removeUnrequiredMetas($whitelistedCustomFields, $postTypeModel);

		// Get the post instance
		$post = $this->findPostInstance($postTypeModel, $request, $postType, $id);
		if(!$post){
			$errorMessages = 'Post does not exist.';
			if(array_has($postTypeModel->errorMessages, 'post_does_not_exist')){
				$errorMessages = $postTypeModel->errorMessages['post_does_not_exist'];
			}
			return $this->abort($errorMessages);
		}

		// Need to remove validations if the logic is false
		$logicValidations = $this->removeValidationsByConditionalLogic($whitelistedCustomFields, $postTypeModel, $post);
		foreach($logicValidations as $postmetaKey => $postmetaValue){
			if($postmetaValue === false){
				if(array_key_exists($postmetaKey, $validationRules)){
					unset($validationRules[$postmetaKey]);
					unset($whitelistedCustomFields[$postmetaKey]);
				}
			}
		}

		$this->validatePost($request, $post, $validationRules);

		$fullRequest = $request;

		// Regenerate the request to pass it thru existing code
		$request = new Request;
		foreach($whitelistedCustomFields as $postmetaKey => $postmetaValue){
			$request[$postmetaKey] = $postmetaValue;
		}

		// Manipulate the request so we can empty out the values where the conditional field is not shown
		$whitelistedCustomFields = $this->removeValuesByConditionalLogic($whitelistedCustomFields, $postTypeModel, $post);

		// Saving the post values to the database
		$post = $this->savePostToDatabase('edit', $post, $postTypeModel, $request, $postType, true);

		// Saving the post meta values to the database
		$this->savePostMetaToDatabase($whitelistedCustomFields, $postTypeModel, $post);

		// Lets fire events as registered in the post type
		$this->triggerEvent('on_edit', $postTypeModel, $post->id, $whitelistedCustomFields);

		$successMessage = 'Field succesfully updated.';
		if(array_has($postTypeModel->successMessage, 'field_updated')){
			$successMessage = $postTypeModel->successMessage['field_updated'];
		}

		// Lets return the response
		return response()->json([
			'code' => 'success',
			'message' => $successMessage,
			'action' => 'edit',
			'post' => [
				'id' => $post->id,
				'post_title' => $post->post_title,
				'post_name' => $post->post_name,
				'status' => $post->status,
				'post_type' => $post->post_type,
				'created_at' => $post->created_at,
				'updated_at' => $post->updated_at,
			],
			'fields_updated' => $whitelistedCustomFields,
			'fields_given' => $fullRequest->all(),
			'reload_fields_method' => $reloadFieldsMethod,
			'reload_fields' => $reloadFields,
		], 200);
	}

	/**
	 * Validating the creation and change of a post
	 */
	protected function validatePost($request, $post, $validationRules)
	{
		$currentValidationRuleKeys = [];
		foreach($request->all() as $requestKey => $requestValue){
			$currentValidationRuleKeys[$requestKey] = $requestKey;
		}

		$validationRules = $this->validateFieldByConditionalLogic($validationRules, $post, $post);

		// Lets receive the current items from the post type validation array
		if(array_key_exists('post_name', $validationRules) && !is_array($validationRules['post_name'])){

			$exploded = explode('|', $validationRules['post_name']);

			$validationRules['post_name'] = [];

			foreach($exploded as $key => $value){
				$validationRules['post_name'][] = $value;
			}
		}

		// Lets validate if a post_name is required.
		if(!$post->disableDefaultPostName){

			// If we are edditing the current existing post, we must remove the unique check
			if($request->get('post_name') == $post->post_name){

				$validationRules['post_name'] = 'required';

			// If this is not a existing post name, we need to validate if its unique. They are changing the post name.
			} else {

				// Make sure that only the post_name of the requested post_type is unique
				$validationRules['post_name'][] = 'required';
				$validationRules['post_name'][] = Rule::unique('cms_posts')->where(function ($query) use ($post) {
					return $query->where('post_type', $post->identifier);
				});

			}

		}

		$newValidationRules = [];
		foreach($currentValidationRuleKeys as $finalKeys => $finalValues){
			if(array_key_exists($finalKeys, $validationRules)){
				$newValidationRules[$finalKeys] = $validationRules[$finalKeys];
			}
		}

		return $this->validate($request, $newValidationRules);
	}
}
