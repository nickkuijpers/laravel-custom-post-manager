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

    	// Check if the post type has a identifier
    	if(empty($postTypeModel->identifier)){
    		return $this->abort('The post type does not have a identifier.');
    	}

        // If the user can only see his own posts
        if($postTypeModel->userCanOnlySeeHisOwnPosts){
            $where[] = ['post_author', '=', Auth::user()->id];
        }

        // Where sql to get all posts by post_Type
        $where[] = ['id', '=', $id];

        // Query only the post type requested
        $where[] = ['post_type', '=', $postTypeModel->identifier];

        // If the ID is empty, that means we are returning the frame to create a new post.
        if($id == 0){
	        $post = $postTypeModel;
	    } else {
	    	$post = $postTypeModel::where($where)->first();
	    }

	    // Validate if the post exists
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
		if(!empty($post->created_at)){
			if(!empty($createdAtCustomField['date_format_php'])){
				$post->created = $post->created_at->format($createdAtCustomField['date_format_php']);
			}
		}

		// Retrieve all the post meta's and taxonomies
		$postmeta = $this->retrieveConfigPostMetas($post, $postTypeModel);

		// Lets fire events as registered in the post type
        $this->triggerEvent('on_read', $postTypeModel, $post->id);

		// Format the collection
        $collection = collect([
            'post' => $post->toArray(),
            'postmeta' => $postmeta,
        ]);

        // Mergin the collection with the data and custom fields
        $collection['templates'] = $this->mergeCollectionWithView($postTypeModel->view, $collection);

		// Merge the configuration values
		$config = [];
		if($postTypeModel->config){
			$config = $postTypeModel->config;
		}

        $collection['config'] = $config;

        // Returning the full collection
    	return response()->json($collection);
    }

	/**
	 * Get all the post meta keys of the post
	 */
	protected function retrieveConfigPostMetas($post, $postTypeModel)
	{
		$metaKeys = [];
		$metaTaxonomies = [];

		// Lets foreach all the views so we can make a big array of all the required post meta's to show
		foreach($postTypeModel->view as $template => $value){

			// Lets foreach all the custom fields
			foreach($value['customFields'] as $customFieldIdentifer => $customField) {

				// When the custom field is marked as taxonomy, we need to
				// attach and sync the connections in the pivot table.
				if(isset($customField['type']) && $customField['type'] == 'taxonomy'){

					// We need to get the values from the taxonomy table
					$customfieldPostTypes = $this->getPostTypeIdentifiers($customField['post_type']);

					// Lets query the post to retrieve all the connected ids
					$taxonomyIds = $post->taxonomies()->whereIn('post_type', $customfieldPostTypes)
						->get();

					// Lets foreach all the posts because we only need the id
					$ids = [];
					foreach($taxonomyIds as $value){
						array_push($ids, $value->id);
					}

					$ids = json_encode($ids);

					$metaTaxonomies[$customFieldIdentifer] = [
						'meta_key' => $customFieldIdentifer,
						'meta_value' => $ids,
					];

				// The other items are default
				} else {

					// Register it to the main array so we can query it later
					array_push($metaKeys, $customFieldIdentifer);

				}

			}

		}

		// Lets query the database to get only the values where we have registered the meta keys
		$postmetaSimple = $post->postmeta()
			->whereIn('meta_key', $metaKeys)
			->select(['meta_key', 'meta_value'])
			->get()
			->keyBy('meta_key')
			->toArray();

		// Attachment of default post data
		$defaultPostData = [
			'post_title' => [
				'meta_key' => 'post_title',
				'meta_value' => $post->post_title,
			],
			'post_name' => [
				'meta_key' => 'post_name',
				'meta_value' => $post->post_name,
			],
		];

		// Lets merge all the types of configs
		$postmeta = array_merge($postmetaSimple, $metaTaxonomies, $defaultPostData);

		// Return the post meta's
		return $postmeta;
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
							if(!empty($post['created_at'])){
	            				$view[$templateKey]['customFields'][$customFieldKey]['id'] = $customFieldKey;
								$view[$templateKey]['customFields'][$customFieldKey]['value'] = $post['created_at'];
							}

	            		break;

	            		// If we find the customFieldKey updated_at, we know it is in the config file
	            		case 'updated_at':

							// Because of that we will add the post updated_at value to the custom field
							if(!empty($post['updated_at'])){
	            				$view[$templateKey]['customFields'][$customFieldKey]['id'] = $customFieldKey;
								$view[$templateKey]['customFields'][$customFieldKey]['value'] = $post['updated_at'];
							}

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
