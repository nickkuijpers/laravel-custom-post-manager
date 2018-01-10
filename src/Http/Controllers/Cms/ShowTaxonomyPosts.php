<?php

namespace Niku\Cms\Http\Controllers\Cms;

use Niku\Cms\Http\Controllers\CmsController;

class ShowTaxonomyPosts extends CmsController
{
	/**
	 * Display a single post
	 */
	public function init($postType, $id, $subPostType)
	{
		// Lets validate if the post type exists and if so, continue.
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

		// If the user can only see his own posts
		if($postTypeModel->userCanOnlySeeHisOwnPosts){
			$where[] = ['post_author', '=', Auth::user()->id];
		}

		// Finding the post with the post_name instead of the id
        if($postTypeModel->getPostByPostName){
        	$where[] = ['post_name', '=', $id];
        } else {
        	$where[] = ['id', '=', $id];
        }

		// Query only the post type requested
        $where[] = ['post_type', '=', $postTypeModel->identifier];

        // Adding a custom query functionality so we can manipulate the find by the config
		if($postTypeModel->appendCustomWhereQueryToCmsPosts){
			foreach($postTypeModel->appendCustomWhereQueryToCmsPosts as $key => $value){
				$where[] = [$value[0], $value[1], $value[2]];
			}
		}

		$post = $postTypeModel::where($where)->first();
		if(!$post){
			$errorMessages = 'No posts connected to this taxonomy.';
    		if(array_has($postTypeModel->errorMessages, 'no_taxonomy_posts_connected')){
    			$errorMessages = $postTypeModel->errorMessages['no_taxonomy_posts_connected'];
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
