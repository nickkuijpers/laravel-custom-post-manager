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

        	// Setting the path to get the validation rules
			if(strpos($key, '_repeater_') !== false) {
				$explodedValue = explode('_', $key);

				// For each all groups to get the validation
				foreach($postTypeModel->templates as $templateKey => $template){
					if(array_has($template, 'customFields.' . $explodedValue[0] . '.customFields.' . $explodedValue[3] . '.validation')){
						$rule = $template['customFields'][$explodedValue[0]]['customFields'][$explodedValue[3]]['validation'];
					}
				}

			} else {

				// For each all groups to get the validation
				foreach($postTypeModel->templates as $templateKey => $template){
					if(array_has($template, 'customFields.' . $key . '.validation')){
						$rule = $template['customFields'][$key]['validation'];
					}
				}

			}

			// Appending the validation rules to the validation array
			if(!empty($rule)){
				$validationRules[$key] = $rule;
			}

        }

    	// Validate the post
    	$this->validatePost($postTypeModel, $request, $validationRules);

        // Saving the post data
        $post = $postTypeModel;
    	$post->post_title = $request->get('post_title');
    	$post->post_name = $this->sanitizeUrl($request->get('post_name'));
    	$post->post_content = $request->get('post_content');
    	$post->status = $request->get('status');
    	if($postTypeModel->isTaxonomy){
    		$post->taxonomy = $postTypeModel->taxonomyName;
    	}
    	$post->post_type = $postType;

        // Check if user is logged in to set the author id
        if(Auth::check()){
            $post->post_author = Auth::user()->id;
        } else {
            $post->post_author = 0;
        }

        $post->template = $request->get('template');
    	$post->save();

        // Presetting a empty array so we can append pivot values to the sync function.
        $pivotValue = [];

        // Saving the meta values to the database
        foreach($postmeta as $key => $value){

        	// Processing the repeater type values
        	if((strpos($key, '_repeater_') !== false)){

        		// Explode the value
        		$explodedValue = explode('_', $key);

        		// Foreaching all templates to validate if the key exists somewhere in a group
        		foreach($postTypeModel->templates as $templateKey => $template){

        			if(array_has($template, 'customFields.' . $explodedValue[0] . '.customFields.' . $explodedValue[3])){

        				// Saving it to the database
        				$object = [
			                'meta_key' => $key,
			                'meta_value' => $value,
			            ];

			            $post->postmeta()->create($object);

			            // Unsetting the value
		        		unset($postmeta[$key]);
		        		continue;

        			}

        		}

        	}

        	// Processing all other type values
        	foreach($postTypeModel->templates as $templateKey => $template){

        		if(array_has($template, 'customFields.' . $key)){

        			// When the custom field is marked as taxonomy, we need to
        			// attach and sync the connections in the pivot table.
        			$customFieldObject = $template['customFields'][$key];

        			if(array_has($customFieldObject, 'type')){
        				if($customFieldObject['type'] == 'taxonomy'){

	        				foreach(json_decode($value) as $valueItem){
	        					$pivotValue[$valueItem] = ['taxonomy' => $key];
	        				}

		        		}
		        	}

	        		// Saving it to the database
					$object = [
		                'meta_key' => $key,
		                'meta_value' => $value,
		            ];

		            $post->postmeta()->create($object);

		            // Unsetting the value
	        		unset($postmeta[$key]);
	        		continue;

		        }

        	}

        }

        // Saving the sync to the database, if we do this inside the loop
    	// it will delete the old ones so we need to prepare the array.
    	$post->taxonomies()->sync($pivotValue);

    	return response()->json([
    		'code' => 'success',
    		'message' => 'Posts succesfully created.',
    		'action' => 'create',
    		'post' => [
    			'postType' => $post->post_type,
    			'id' => $post->id
    		]
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
