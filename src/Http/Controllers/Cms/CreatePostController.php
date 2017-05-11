<?php

namespace Niku\Cms\Http\Controllers\Cms;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Niku\Cms\Http\Controllers\CmsController;

class CreatePostController extends CmsController
{
	/**
     * The manager of the database communication for adding and manipulating posts
     */
    public function init(Request $request, $postType)
    {
    	// Lets validate if the post type exists and if so, continue.
    	$postTypeModel = $this->getPostType($postType);
    	if(!$postTypeModel){
    		return $this->abort('You are not authorized to do this.');
    	}

    	// Receive the post meta values
        $postmeta = $request->all();

        // Validating the request
        $validationRules = $this->validatePostFields($request->all(), $request, $postTypeModel);
		$this->validatePost($postTypeModel, $request, $validationRules);

        // Unset unrequired post meta keys
        $postmeta = $this->removeUnrequiredMetas($postmeta);

        // Getting the post instance where we can add upon
        $post = $postTypeModel;

        // Saving the post values to the database
    	$post = $this->savePostToDatabase($post, $postTypeModel, $request, $postType);

        // Saving the post meta values to the database
        $this->savePostMetaToDatabase($postmeta, $postTypeModel, $post);

    	return response()->json([
    		'code' => 'success',
    		'message' => 'Posts succesfully created.',
    		'action' => 'create',
    		'post' => $post
    	], 200);
    }

    /**
     * Validating the creation and change of a post
     */
    protected function validatePost($postTypeModel, $request, $validationRules)
    {
        // Validating the postname of the given ID to make sure it can be
        // updated and it is not overriding a other duplicated postname.
        $post = $postTypeModel::where([
            ['post_name', '=', $request->get('post_name')],
            ['post_type', '=', $request->get('_posttype')]
        ])->select(['post_name'])->first();

        if($post){
            $validationRules['post_name'] = 'required|unique:cms_posts';
        } else {
            $validationRules['post_name'] = 'required';
        }

        return $this->validate($request, $validationRules);
    }
}
