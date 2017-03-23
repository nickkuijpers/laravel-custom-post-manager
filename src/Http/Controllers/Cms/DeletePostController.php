<?php

namespace Niku\Cms\Http\Controllers\Cms;

use Niku\Cms\Http\Controllers\CmsController;

class DeletePostController extends CmsController
{
	/**
     * Delete a single post
     */
    public function init($post_type, $id)
    {
        // Validate if the user is logged in
        if(! $this->userIsLoggedIn($post_type)){
            return $this->abort('User not authorized.');
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
    	$where[] = ['id', '=', $id];

        // If the user can only see his own posts
        if($this->userCanOnlySeeHisOwnPosts($post_type)) {
            $where[] = ['post_author', '=', Auth::user()->id];
        }

    	$post = NikuPosts::where($where);
    	$post->delete();

    	return response()->json('success');
    }
}
