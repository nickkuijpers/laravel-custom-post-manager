<?php

namespace Niku\Cms\Http\Controllers\Cms;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Niku\Cms\Http\Controllers\CmsController;

class CheckPostController extends CmsController
{
    public function init(Request $request, $postType, $id)
    {
		// Lets validate if the post type exists and if so, continue.
		$postTypeModel = $this->getPostType($postType);
    	if(!$postTypeModel){
    		$errorMessages = 'You are not authorized to do this.';
    		return $this->abort($errorMessages);
		}

		if(method_exists($postTypeModel, 'override_check_post')){
			return $postTypeModel->override_check_post($id, $request);
		}

		$result = $this->execute($request, $postType, $id);
		if($result->code == 'failure'){
			return response()->json($result->errors, 422);
		}

		$config = $this->getConfig($postTypeModel);

		if(method_exists($postTypeModel, 'override_check_config_response')){
			$config = $postTypeModel->override_check_config_response($postTypeModel, $result->post->id, $config, $request);
		}

		return response()->json([
    		'code' => 'success',
    		'message' => $result->message,
			'action' => 'check',
			'config' => $config,
    		'post' => [
    			'id' => $result->post->id,
    			'post_title' => $result->post->post_title,
    			'post_name' => $result->post->post_name,
				'status' => $result->post->status,
				'post_type' => $result->post->post_type,
				'created_at' => $result->post->created_at,
				'updated_at' => $result->post->updated_at,
    		],
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

	public function internal($request, $postType, $id)
	{
		$result = $this->execute($request, $postType, $id);

		if($result->code == 'failure'){
			return $result;
		}

		return [
			'code' => 'success',
    		'message' => $result->message,
    		'action' => 'check',
    		'post' => [
    			'id' => $result->post->id,
    			'post_title' => $result->post->post_title,
    			'post_name' => $result->post->post_name,
				'status' => $result->post->status,
				'post_type' => $result->post->post_type,
				'created_at' => $result->post->created_at,
				'updated_at' => $result->post->updated_at,
    		],
		];
	}

	public function execute($request, $postType, $id)
	{
		$postTypeModel = $this->getPostType($postType);
    	if(!$postTypeModel){
    		$errorMessages = 'You are not authorized to do this.';
    		return (object) [
				'code' => 'failure',
				'validation' => false,
				'errors' => $errorMessages,
			];
		}

		$config = $this->getConfig($postTypeModel);

    	// Check if the post type has a identifier
    	if(empty($postTypeModel->identifier)){
			$errorMessages = 'The post type does not have a identifier.';
    		if(array_has($postTypeModel->errorMessages, 'post_type_identifier_does_not_exist')){
				$errorMessages = $postTypeModel->errorMessages['post_type_identifier_does_not_exist'];
    		}
    		return (object) [
				'code' => 'failure',
				'validation' => false,
				'errors' => $errorMessages,
			];
    	}

        // Get the post instance
        $post = $this->findPostInstance($postTypeModel, $request, $postType, $id, 'check_post');
        if(!$post){
			$errorMessages = 'Post does not exist.';
    		if(array_has($postTypeModel->errorMessages, 'post_does_not_exist')){
				$errorMessages = $postTypeModel->errorMessages['post_does_not_exist'];
			}
			return (object) [
				'code' => 'failure',
				'validation' => false,
				'errors' => $errorMessages,
			];
		}

		$allFieldKeys = $this->getValidationsKeys($postTypeModel);

		$secondRequest = new Request;

        foreach($allFieldKeys as $toSaveKey => $toSaveValue){
			$configValue = $this->getCustomFieldValueWithoutConfig($postTypeModel, $post, $toSaveKey);
            $secondRequest[$toSaveKey] = $configValue;
		}

        // Receive the post meta values
		$postmeta = $secondRequest->all();

        // Validating the request
		$validationRules = $this->validatePostFields($secondRequest->all(), $secondRequest, $postTypeModel);

		// Manipulate the request so we can empty out the values where the conditional field is not shown
		$postmeta = $this->removeValuesByConditionalLogic($postmeta, $postTypeModel, $post);
		$logicValidations = $this->removeValidationsByConditionalLogic($postmeta, $postTypeModel, $post);

		foreach($logicValidations as $postmetaKey => $postmetaValue){
			if($postmetaValue === false){
				if(array_key_exists($postmetaKey, $validationRules)){
					unset($validationRules[$postmetaKey]);
				}
			}
		}

		$validatedFields = $this->validatePost($secondRequest, $post, $validationRules);
		if($validatedFields['status'] === false){
			$errors = $validatedFields['errors'];
			return (object) [
				'code' => 'failure',
				'validation' => true,
				'errors' => $errors->messages(),
				'config' => $config,
			];
		}

		if(method_exists($postTypeModel, 'on_check_check')){
			$onCheck = $postTypeModel->on_check_check($postTypeModel, $post->id, $postmeta);
			if($onCheck['continue'] === false){
				$errorMessages = 'You are not authorized to do this.';
				if(array_key_exists('message', $onCheck)){
					$errorMessages = $onCheck['message'];
				}
				return (object) [
					'code' => 'failure',
					'validation' => false,
					'errors' => $errorMessages,
					'config' => $config,
				];
			}
		}

        // Lets fire events as registered in the post type
        $this->triggerEvent('on_check_event', $postTypeModel, $post->id, $postmeta);

        $successMessage = 'Post succesfully checked.';
		if(array_has($postTypeModel->successMessage, 'post_checked')){
			$successMessage = $postTypeModel->successMessage['post_checked'];
		}

		return (object) [
			'code' => 'success',
			'post' => $post,
			'message' => $successMessage,
			'config' => $config,
		];

	}

    /**
     * Validating the creation and change of a post
     */
    protected function validatePost($request, $post, $validationRules)
    {
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

		$validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
			return [
				'status' => false,
				'errors' => $validator->errors()
			];
        } else {
			return true;
		}
    }

}
