<?php

namespace Niku\Cms\Http\Controllers\Cms;

use Illuminate\Http\Request;
use Niku\Cms\Http\Controllers\CmsController;

class AttachPostsTaxonomyController extends CmsController
{
	/**
	 * Display a single post
	 */
	public function init(Request $request, $postType, $id)
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

		dd(json_encode($request->all()));

		// Where sql to get all posts by post_Type
		$where[] = ['id', '=', $id];

		$post = $postTypeModel::where($where)->first();
		if(!$post){
			return $this->abort('No posts connected to this taxonomy.');
		}

		$collection = collect([
			'posts' => $post->posts()->get()->toArray(),
		]);

		// Returning the full collection
		return response()->json($collection);
	}
}
