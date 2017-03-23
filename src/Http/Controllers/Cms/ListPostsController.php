<?php

namespace Niku\Cms\Http\Controllers\Cms;

use Niku\Cms\Http\Controllers\CmsController;

class ListPostsController extends CmsController
{
	/**
     * Return the custom fields based on the config
     */
    public function init(Request $request)
    {
        $postType = $request->get('_post_type');
        $id = $request->get('_id');

        // Validate if the user is logged in
        if(! $this->userIsLoggedIn($postType)){
            return $this->abort('User not authorized.');
        }

        // User email validation
        if ($this->userHasWhitelistedEmail($postType)) {
            return $this->abort('User email is not whitelisted.');
        }

        $nikuConfig = config("niku-cms.post_types.{$postType}");
        // Validate if the post type exists
        if(empty($nikuConfig)){
            return collect([
                'code' => 'doesnotexist',
                'status' => 'Post type does not exist'
            ]);
        }

        // Returning the view
        $view = $nikuConfig['view'];

        // Lets now fill the custom fields with data out of database
        $post = NikuPosts::find($id);
        if(!empty($post)){
            $postmeta = $post->postmeta()->select(['meta_key', 'meta_value'])->get()->keyBy('meta_key')->toArray();
        }

        // Appending the key added in the config to the array
        // so we can use it very easliy in the component.
        foreach($view['templates'] as $key => $template){
        	if(!empty($template['customFields'])){
	            foreach($template['customFields'] as $ckey => $customField){
	                $view['templates'][$key]['customFields'][$ckey]['id'] = $ckey;
	                if(!empty($post)){
	                	if(!empty($postmeta[$ckey])){
	                		$view['templates'][$key]['customFields'][$ckey]['value'] = $postmeta[$ckey]['meta_value'];
	                	}
	                }
	            }
	        }
        }

        return $view;
    }
}
