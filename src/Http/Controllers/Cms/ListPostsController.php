<?php

namespace Niku\Cms\Http\Controllers\Cms;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Niku\Cms\Http\Controllers\CmsController;

class ListPostsController extends CmsController
{
	public function init(Request $request, $postType)
    {
    	dd($request);
    	// Lets validate if the post type exists and if so, continue.
    	$postTypeModel = $this->getPostType($postType);
    	if(!$postTypeModel){
    		return $this->abort('You are not authorized to do this.');
    	}

        // If the user can only see his own posts
        if($postTypeModel->userCanOnlySeeHisOwnPosts){
            $where[] = ['post_author', '=', Auth::user()->id];
        }

        // Lets check if we have configured a custom post type identifer
        if(!empty($postTypeModel->identifier)){
        	$postType = $postTypeModel->identifier;
        }

        // Where sql to get all posts by post_Type
        $where[] = ['post_type', '=', $postType];

        // Query the database
		$posts = $postTypeModel::where($where)->select([
			'id',
    		'post_title',
    		'post_name',
    		'status',
    		'post_type',
		])->with('postmeta')->get();

		// Returning the objects
		$objects = [
			'label' => $postTypeModel->label,
			'objects' => $posts
		];

    	return response()->json($objects);
    }
}
