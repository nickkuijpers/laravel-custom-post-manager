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
    public function init(Request $request, $postType, $id, $method, $customId)
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
            return $postTypeModel->$methodToEdit($request);
        } else {
            $errorMessages = 'The post type does not have the custom show get method ' . $method . '.';
            if(array_has($postTypeModel->errorMessages, 'post_type_does_not_have_the_support_custom_show_getmethod')){
                $errorMessages = $postTypeModel->errorMessages['post_type_does_not_have_the_support_custom_show_getmethod'];
            }
            return $this->abort($errorMessages);
        }
    }
}
