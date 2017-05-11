<?php

namespace Niku\Cms\Http\Controllers\Cms;

use Niku\Cms\Http\Controllers\CmsController;

class ShowPostController extends CmsController
{
	/**
     * Display a single post
     */
    public function init($postType, $id)
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

        // Where sql to get all posts by post_Type
        $where[] = ['id', '=', $id];

        if($id == 0){
	        $post = $postTypeModel;
	    } else {
	    	$post = $postTypeModel::where($where)->first();
	    }

	    if(!$post){
        	return $this->abort('Post does not exist.');
        }

		// Converting the updated_at to the input picker in the front-end
        $updatedAtCustomField = $this->getCustomFieldObject($postTypeModel, 'updated_at');

        // If we have not set a custom date format, we will not touch this formatting
        if(!empty($updatedAtCustomField['date_format_php'])){
        	$post->created = $post->updated_at->format($updatedAtCustomField['date_format_php']);
        }

		// Converting the created_at to the input picker in the front-end
        $createdAtCustomField = $this->getCustomFieldObject($postTypeModel, 'created_at');

        // If we have not set a custom date format, we will not touch this formatting
        if(!empty($createdAtCustomField['date_format_php'])){
        	$post->created = $post->created_at->format($createdAtCustomField['date_format_php']);
        }

        $postmeta = $post->postmeta()->select(['meta_key', 'meta_value'])->get();
        $postmeta = $postmeta->keyBy('meta_key');

        $collection = collect([
            'post' => $post->toArray(),
            'postmeta' => $postmeta->toArray()
        ]);

        // Mergin the collection with the data and custom fields
        $collection['templates'] = $this->mergeCollectionWithView($postTypeModel->templates, $collection);

        // Merge the configuration values
        $collection['config'] = $postTypeModel->config;

        // Returning the full collection
    	return response()->json($collection);
    }

    /**
     * Appending the key added in the config to the array
     * so we can use it very easliy in the component.
     */
    protected function mergeCollectionWithView($view, $collection)
    {
    	$post = $collection['post'];
    	$postmeta = $collection['postmeta'];

    	// Foreaching all templates in the custom field configuration file
    	foreach($view as $templateKey => $template){

    		// If the array custom fields is not empty
        	if(!empty($template['customFields'])){

        		// We foreach all custom fields in this template section
	            foreach($template['customFields'] as $customFieldKey => $customField){

	            	// Setting post data to the custom fields
	            	switch($customFieldKey){

	            		// If we find the customFieldKey created_at, we know it is in the config file
	            		case 'created_at':

	            			// Because of that we will add the post created_at value to the custom field
	            			$view[$templateKey]['customFields'][$customFieldKey]['id'] = $customFieldKey;
	            			$view[$templateKey]['customFields'][$customFieldKey]['value'] = $post['created_at'];

	            		break;

	            		// If we find the customFieldKey updated_at, we know it is in the config file
	            		case 'updated_at':

	            			// Because of that we will add the post updated_at value to the custom field
	            			$view[$templateKey]['customFields'][$customFieldKey]['id'] = $customFieldKey;
	            			$view[$templateKey]['customFields'][$customFieldKey]['value'] = $post['updated_at'];

	            		break;
	            	}

	            	// Lets set the key to the array
	                $view[$templateKey]['customFields'][$customFieldKey]['id'] = $customFieldKey;

	                // If the post is not empty
	                if(!empty($post)){

	                	// We will validate if the post meta is not empty
	                	if(!empty($postmeta[$customFieldKey])){

	                		// So we can add it to the value
	                		$view[$templateKey]['customFields'][$customFieldKey]['value'] = $postmeta[$customFieldKey]['meta_value'];
	                	}
	                }
	            }
	        }
        }

        return $view;
    }
}
