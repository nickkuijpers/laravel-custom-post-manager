<?php

namespace Niku\Cms\Http\Controllers\Cms;

use Illuminate\Http\Request;
use Niku\Cms\Http\Controllers\CmsController;

class ShowTaxonomyPosts extends CmsController
{
	/**
	 * Display a single post
	 */
	public function init(Request $request, $postType, $id, $subPostType)
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

		$post = $this->findPostInstance($postTypeModel, $request, $postType, $id, 'show_post');
		if(!$post){
			$errorMessages = 'Post does not exist.';
			if(array_has($postTypeModel->errorMessages, 'post_does_not_exist')){
				$errorMessages = $postTypeModel->errorMessages['post_does_not_exist'];
			}
			return $this->abort($errorMessages);
		}

		// Lets get the post type of the sub post type object
		$subPostTypeModel = $this->getPostType($subPostType);
		if(!$subPostTypeModel){
			$errorMessages = 'You are not authorized to do this.';
    		if(array_has($postTypeModel->errorMessages, 'sub_post_type_does_not_exist')){
    			$errorMessages = $postTypeModel->errorMessages['sub_post_type_does_not_exist'];
    		}
    		return $this->abort($errorMessages);
		}

		$collection = collect([
			'objects' => $post->posts()->where('post_type', '=', $subPostTypeModel->identifier)->get()->toArray(),
		]);

		// Returning the full collection
		return response()->json($collection);
	}
}
