<?php

namespace Niku\Cms\Http\Controllers\Cms;

use Niku\Cms\Http\Controllers\CmsController;

class ShowPostController extends CmsController
{
	/**
     * Display a single post
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

        $post = NikuPosts::where($where)->first();
        $postmeta = $post->postmeta()->select(['meta_key', 'meta_value'])->get();
        $postmeta = $postmeta->keyBy('meta_key');
        $postmeta = $postmeta->toArray();
        $post = $post->toArray();

        $collection = collect([
            'post' => $post,
            'postmeta' => $postmeta
        ]);

    	return response()->json($collection);
    }
}
