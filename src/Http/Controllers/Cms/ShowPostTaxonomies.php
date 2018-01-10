<?php

namespace Niku\Cms\Http\Controllers\Cms;

use Niku\Cms\Http\Controllers\CmsController;

class ShowPostTaxonomies extends CmsController
{
	/**
	 * Display a single post
	 */
	public function init($postType, $id, $subPostType)
	{
		// Lets validate if the post type exists and if so, continue.
		$postTypeModel = $this->getPostType($postType);
		if(!$postTypeModel){
			return $this->abort('You are not authorized to do this.');
		}

		// Check if the post type has a identifier
    	if(empty($postTypeModel->identifier)){
    		return $this->abort('The post type does not have a identifier.');
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
			return $this->abort('No posts connected to this taxonomy.');
		}

		// Lets get the post type of the sub post type object
		$subPostTypeModel = $this->getPostType($subPostType);
		if(!$subPostTypeModel){
			return $this->abort('You are not authorized to do this.');
		}

		$collection = collect([
			'objects' => $post->taxonomies()->where('post_type', '=', $subPostTypeModel->identifier)->get()->toArray(),
		]);

		// Returning the full collection
		return response()->json($collection);
	}
}
