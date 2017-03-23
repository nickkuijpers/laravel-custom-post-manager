<?php

namespace Niku\Cms\Http\Controllers\Cms;

use Niku\Cms\Http\Controllers\CmsController;

class EditPostController extends CmsController
{
	/**
     * The manager of the database communication for adding and manipulating posts
     */
    public function init(Request $request, $post_type, $action)
    {
        // Validate if the user is logged in
        if(! $this->userIsLoggedIn($post_type)){
            return $this->abort('User not authorized.');
        }

        // User email validation
        if ($this->userHasWhitelistedEmail($post_type)) {
            return $this->abort('User email is not whitelisted.');
        }

        $validationRules = [
            'post_title' => 'required',
            'status' => 'required',
            'post_name' => 'required',
        ];

        // Creating and cleaning up the request so we get all custom fields
        $postmeta = $request->all();
        $unsetValues = ['_token', '_posttype', '_id', 'post_title', 'post_name', 'post_content', 'template', 'status'];
        foreach($unsetValues as $value){
            unset($postmeta[$value]);
        }

        foreach ($postmeta as $key => $value) {
            $rule = config("niku-cms.post_types.{$post_type}.view.templates.{$request->template}.customFields.{$key}.validation");

            if (! empty($rule)) {
                $validationRules[$key] = $rule;
            }
        }

    	// Validate the post
    	$this->validatePost($request, $validationRules);

        // If the user can only see his own posts
        if($this->userCanOnlySeeHisOwnPosts($post_type)) {
            $where[] = ['post_author', '=', Auth::user()->id];
        }

		$where[] = ['id', '=', $request->get('_id')];

		$post = NikuPosts::where($where)->first();

        // Saving the post data
    	$post->post_title = $request->get('post_title');
    	$post->post_name = $this->sanitizeUrl($request->get('post_name'));
    	$post->post_content = $request->get('post_content');
    	$post->status = $request->get('status');
    	$post->post_type = $post_type;

        // Check if user is logged in to set the author id
        if(Auth::check()){
            $post->post_author = Auth::user()->id;
        } else {
            $post->post_author = 0;
        }

        $post->template = $request->get('template');
    	$post->save();

        // Deleting all current postmeta rows
        $post->postmeta()->delete();

        // Saving the custom fields to the database as post meta
        foreach($postmeta as $key => $value){
            $object = [
                'meta_key' => $key,
                'meta_value' => $value,
            ];
            $post->postmeta()->create($object);
        }

    	return response()->json('success');
    }

    /**
     * Validating the creation and change of a post
     */
    protected function validatePost($request, $validationRules)
    {
        // Validating the postname of the given ID to make sure it can be
        // updated and it is not overriding a other duplicated postname.
        $post = NikuPosts::where([
            ['id', '=', $request->get('_id')],
            ['post_type', '=', $request->get('_posttype')]
        ])->select(['post_name'])->first();

		if( $request->get('post_name') == $post->post_name ) {
	    	$validationRules['post_name'] = 'required';
	    } else {
	    	$validationRules['post_name'] = 'required|unique:cms_posts';
	    }

        return $this->validate($request, $validationRules);
    }
}
