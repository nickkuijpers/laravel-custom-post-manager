<?php

namespace Niku\Cms\Http\Controllers\Cms;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Niku\Cms\Http\Controllers\CmsController;

class EditCustomPostController extends CmsController
{
    public function init(Request $request, $postType, $method = '')
    {
        $postTypeModel = $this->getPostType($postType);
    	if(!$postTypeModel){
    		$errorMessages = 'You are not authorized to do this.';
    		return $this->abort($errorMessages);
    	}

		// Disable editting of form
		if($postTypeModel->enableCustomPostMethod !== true){
        	$errorMessages = 'The post type does not support the custom method.';
    		if(array_has($postTypeModel->errorMessages, 'post_type_does_not_support_custom_method')){
    			$errorMessages = $postTypeModel->errorMessages['post_type_does_not_support_custom_method'];
    		}
    		return $this->abort($errorMessages);
		}

		if($postTypeModel->enableCustomValidations === true){
			$validationRules = $this->validatePostFields($request->all(), $request, $postTypeModel);
			$this->validate($request, $validationRules);
		}

		if(empty($method)){
			if(method_exists($postTypeModel, 'edit_custom_post')){
				return $postTypeModel->edit_custom_post($request);
			} else {
				$errorMessages = 'The post type does not have the custom method.';
				if(array_has($postTypeModel->errorMessages, 'post_type_does_not_have_the_support_custom_method')){
					$errorMessages = $postTypeModel->errorMessages['post_type_does_not_have_the_support_custom_method'];
				}
				return $this->abort($errorMessages);
			}

		} else {

			if(method_exists($postTypeModel, 'edit_custom_post_' . $method)){
				$methodToEdit = 'edit_custom_post_' . $method;
				return $postTypeModel->$methodToEdit($request);
			} else {
				$errorMessages = 'The post type does not have the custom method ' . $method . '.';
				if(array_has($postTypeModel->errorMessages, 'post_type_does_not_have_the_support_custom_method')){
					$errorMessages = $postTypeModel->errorMessages['post_type_does_not_have_the_support_custom_method'];
				}
				return $this->abort($errorMessages);
			}

		}

	}

}
