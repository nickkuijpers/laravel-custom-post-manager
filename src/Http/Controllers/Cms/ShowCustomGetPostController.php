<?php

namespace Niku\Cms\Http\Controllers\Cms;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Niku\Cms\Http\Controllers\CmsController;

class ShowCustomGetPostController extends CmsController
{
	/**
     * Display a single post
     */
    public function init(Request $request, $postType, $id, $method, $customId = '')
    {
    	// Lets validate if the post type exists and if so, continue.
		$postTypeModel = $this->getPostType($postType);
    	if(!$postTypeModel){
    		$errorMessages = 'You are not authorized to do this.';
    		return $this->abort($errorMessages);
		}

		$config = $this->getConfig($postTypeModel);

    	// Check if the post type has a identifier
    	if(empty($postTypeModel->identifier)){
    		$errorMessages = 'The post type does not have a identifier.';
    		if(array_has($postTypeModel->errorMessages, 'post_type_identifier_does_not_exist')){
    			$errorMessages = $postTypeModel->errorMessages['post_type_identifier_does_not_exist'];
    		}
    		return $this->abort($errorMessages, $config);
		}

	    $post = $this->findPostInstance($postTypeModel, $request, $postType, $id, 'show_post');
		if(!$post){
			$errorMessages = 'Post does not exist.';
			if(array_has($postTypeModel->errorMessages, 'post_does_not_exist')){
				$errorMessages = $postTypeModel->errorMessages['post_does_not_exist'];
			}
			return $this->abort($errorMessages, $config);
        }

        if(method_exists($postTypeModel, 'show_custom_get_' . $method)){
            $methodToEdit = 'show_custom_get_' . $method;
            return $postTypeModel->$methodToEdit($request, $id, $customId, $post);
        } else {
            $errorMessages = 'The post type does not have the custom show get method ' . $method . '.';
            if(array_has($postTypeModel->errorMessages, 'post_type_does_not_have_the_support_custom_show_getmethod')){
                $errorMessages = $postTypeModel->errorMessages['post_type_does_not_have_the_support_custom_show_getmethod'];
            }
            return $this->abort($errorMessages);
        }
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
}
