<?php

namespace Niku\Cms\Http\Controllers\Cms;

use Niku\Cms\Http\Controllers\CmsController;

class ReceiveViewController extends CmsController
{
	/**
	 * Display a list based on the post type
	 */
	public function init($post_type)
	{
		// Lets validate if the post type exists and if so, continue.
		$postType = $this->getPostType($post_type);
		if(!$postType){
			return $this->abort('Post type is not whitelisted.');
		}

		// Do some validations which can be manipulated by setting the model values
		$rules = $this->validateRules($postType);
		if(!$rules){
			return $this->abort('You are not authorized to do this.');
		}

		// Validate if the user is logged in
		if(!$this->userIsLoggedIn($post_type)){
			return $this->abort('User not authorized or post type is not registered.');
		}

		// User email validation
		if ($this->userHasWhitelistedEmail($post_type)) {
			return $this->abort('User email is not whitelisted.');
		}

		// If the user can only see his own posts
		if($this->userCanOnlySeeHisOwnPosts($post_type)) {
			$where[] = ['post_author', '=', Auth::user()->id];
		}

		// Where sql to get all posts by post_Type
		$where[] = ['post_type', '=', $post_type];

		// Returning the view data like the page label
		$objects['label'] = config("niku-cms.post_types.{$post_type}.view.label");

		$posts = NikuPosts::where($where)->select([
			'id',
			'post_title',
			'post_name',
			'status',
			'post_type',
		])->with('postmeta')->get();

		// Returning the objects
		$objects['objects'] = $posts;

		return response()->json($objects);
	}
}
