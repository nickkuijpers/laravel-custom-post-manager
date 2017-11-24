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

		// If the user can only see his own posts
		if($postTypeModel->userCanOnlySeeHisOwnPosts){
			$where[] = ['post_author', '=', Auth::user()->id];
		}

		// Query only the post type requested
        $where[] = ['post_type', '=', $postTypeModel->identifier];

		// Where sql to get all posts by post_Type
		$where[] = ['id', '=', $id];

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
