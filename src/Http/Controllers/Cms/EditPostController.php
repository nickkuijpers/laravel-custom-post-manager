<?php

namespace Niku\Cms\Http\Controllers\Cms;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Niku\Cms\Http\Controllers\CmsController;

class EditPostController extends CmsController
{
	/**
     * The manager of the database communication for adding and manipulating posts
     */
    public function init(Request $request, $postType)
    {
        $postTypeModel = $this->getPostType($postType);
    	if(!$postTypeModel){
    		return $this->abort('You are not authorized to do this.');
    	}

    	// Receive the post meta values
        $postmeta = $request->all();

        // Validating the request
        $validationRules = $this->validatePostFields($request->all(), $request, $postTypeModel);

        // Unset unrequired post meta keys
        $postmeta = $this->removeUnrequiredMetas($postmeta);

        // Get the post instance
        $post = $this->findPostInstance($postTypeModel, $request, $postType);
        if(!$post){
			return $this->abort('Post does not exist.');
		}

		$this->validatePost($request, $post, $validationRules);

		// Saving the post values to the database
    	$post = $this->savePostToDatabase($post, $postTypeModel, $request, $postType);

        // Deleting all current postmeta's out of the database so we can recreate it.
        $post->postmeta()->delete();

        // Saving the post meta values to the database
        $this->savePostMetaToDatabase($postmeta, $postTypeModel, $post);

    	return response()->json([
    		'code' => 'success',
    		'message' => 'Post succesfully editted',
    	], 200);
    }

    protected function findPostInstance($postTypeModel, $request, $postType)
    {
    	// Validating the postname of the given ID to make sure it can be
        // updated and it is not overriding a other duplicated postname.
        // If the user can only see his own posts
        if($postTypeModel->userCanOnlySeeHisOwnPosts){
            $where[] = ['post_author', '=', Auth::user()->id];
        }

		$where[] = ['id', '=', $request->get('_id')];
		$where[] = ['post_type', '=', $postType];

		return $postTypeModel::where($where)->first();
    }

    /**
     * Validating the creation and change of a post
     */
    protected function validatePost($request, $post, $validationRules)
    {
		if($request->get('post_name') == $post->post_name){
	    	$validationRules['post_name'] = 'required';
	    } else {
	    	$validationRules['post_name'] = 'required|unique:cms_posts';
	    }

        return $this->validate($request, $validationRules);
    }
}
