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

        // Creating and cleaning up the request so we get all custom fields
        $postmeta = $request->all();
        $unsetValues = ['_token', '_posttype', '_id', 'post_title', 'post_name', 'post_content', 'template', 'status'];
        foreach($unsetValues as $value){
            unset($postmeta[$value]);
        }

        // Receive the default validations required for the post
        $validationRules = $postTypeModel->defaultValidationRules;

        // Getting the template structure
        $template = $postTypeModel->templates[$request->template];

        // Appending required validations to the default validations of the post
        foreach($postmeta as $key => $value){

        	// Lets validate if the array key exists
        	if(array_key_exists($key, $template['customFields'])){
        		if(!empty($template['customFields'][$key]['validation'])){
		            $validationRules[$key] = $template['customFields'][$key]['validation'];
		        }
        	}
        }

        // Validating the postname of the given ID to make sure it can be
        // updated and it is not overriding a other duplicated postname.
        // If the user can only see his own posts
        if($postTypeModel->userCanOnlySeeHisOwnPosts){
            $where[] = ['post_author', '=', Auth::user()->id];
        }

		$where[] = ['id', '=', $request->get('_id')];
		$where[] = ['post_type', '=', $postType];

		$post = $postTypeModel::where($where)->first();
		if(!$post){
			return $this->abort('Post does not exist.');
		}

    	// Validate the post
    	$this->validatePost($request, $post, $validationRules);

        // Saving the post data
    	$post->post_title = $request->get('post_title');
    	$post->post_name = $this->sanitizeUrl($request->get('post_name'));
    	$post->post_content = $request->get('post_content');
    	$post->status = $request->get('status');
    	$post->post_type = $post->post_type;

        // Check if user is logged in to set the author id
        if(Auth::check()){
            $post->post_author = Auth::user()->id;
        } else {
            $post->post_author = 0;
        }

        $post->template = $request->get('template');
    	$post->save();

        // Deleting all current postmeta's out of the database so we can recreate it.
        $post->postmeta()->delete();

        // Saving the custom fields to the database as post meta
        foreach($postmeta as $key => $value){

            // Lets validate if we have whitelisted the custom field, if not we
        	// do not want it to be saved in our database to prevent garbage.
        	if (
        		array_key_exists($key, $template['customFields'])
        		||
        		(strpos($key, '_repeater_') !== false)
        	) {

	            $object = [
	                'meta_key' => $key,
	                'meta_value' => $value,
	            ];

	            $post->postmeta()->create($object);
	        }
        }

    	return response()->json([
    		'code' => 'success',
    		'message' => 'Post succesfully editted',
    	], 200);
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
