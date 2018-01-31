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
			if(array_has($postTypeModel->errorMessages, 'post_type_does_not_exist')){
				$errorMessages = $postTypeModel->errorMessages['post_type_does_not_exist'];
			}
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
							$reloadFields[] = $this->getAllCustomFieldsKeys($postTypeModel);
						}
					}
				}
			}

		}

		$postmeta = $request->only($verifiedFields);

		// Validating the request
		$validationRules = $this->validatePostFields($postmeta, $request, $postTypeModel, true);

		// Unset unrequired post meta keys
		$postmeta = $this->removeUnrequiredMetas($postmeta);

		// Get the post instance
		$post = $this->findPostInstance($postTypeModel, $request, $postType, $id);
		if(!$post){
			$errorMessages = 'Post does not exist.';
			if(array_has($postTypeModel->errorMessages, 'post_does_not_exist')){
				$errorMessages = $postTypeModel->errorMessages['post_does_not_exist'];
			}
			return $this->abort($errorMessages);
		}

		$this->validatePost($request, $post, $validationRules);

		$fullRequest = $request;

		// Regenerate the request to pass it thru existing code
		$request = new Request;
		foreach($postmeta as $postmetaKey => $postmetaValue){
			$request->$postmetaKey = $postmetaValue;
		}

		// Manipulate the request so we can empty out the values where the conditional field is not shown
		$postmeta = $this->removeValuesByConditionalLogic($postmeta, $postTypeModel, $post);

		// Saving the post values to the database
		$post = $this->savePostToDatabase('edit', $post, $postTypeModel, $request, $postType, true);

		// Saving the post meta values to the database
		$this->savePostMetaToDatabase($postmeta, $postTypeModel, $post);

		// Lets fire events as registered in the post type
		$this->triggerEvent('on_edit', $postTypeModel, $post->id);

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
			'fields_updated' => $verifiedFields,
			'fields_given' => $fullRequest->all(),
			'reload_fields_method' => $reloadFieldsMethod,
			'reload_fields' => $reloadFields,
		], 200);
	}

	protected function findPostInstance($postTypeModel, $request, $postType, $id)
	{
		// Validating the postname of the given ID to make sure it can be
		// updated and it is not overriding a other duplicated postname.
		// If the user can only see his own posts
		if($postTypeModel->userCanOnlySeeHisOwnPosts){
			$where[] = ['post_author', '=', Auth::user()->id];
		}

		// Lets check if we have configured a custom post type identifer
		if(!empty($postTypeModel->identifier)){
			$postType = $postTypeModel->identifier;
		}

		// Finding the post with the post_name instead of the id
		if($postTypeModel->getPostByPostName){
			$where[] = ['post_name', '=', $id];
		} else {
			$where[] = ['id', '=', $id];
		}

		$where[] = ['post_type', '=', $postType];

		// Adding a custom query functionality so we can manipulate the find by the config
		if($postTypeModel->appendCustomWhereQueryToCmsPosts){
			foreach($postTypeModel->appendCustomWhereQueryToCmsPosts as $key => $value){
				$where[] = [$value[0], $value[1], $value[2]];
			}
		}

		return $postTypeModel::where($where)->first();
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
			$newValidationRules[$finalKeys] = $validationRules[$finalKeys];
		}

		return $this->validate($request, $newValidationRules);
	}
}
