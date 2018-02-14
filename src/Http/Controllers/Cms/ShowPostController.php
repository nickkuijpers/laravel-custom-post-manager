<?php

namespace Niku\Cms\Http\Controllers\Cms;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Niku\Cms\Http\Controllers\CmsController;

class ShowPostController extends CmsController
{
	/**
     * Display a single post
     */
    public function init(Request $request, $postType, $id)
    {
        // Lets validate if the post type exists and if so, continue.
		$postTypeModel = $this->getPostType($postType);
    	if(!$postTypeModel){
    		$errorMessages = 'You are not authorized to do this.';
    		return $this->abort($errorMessages);
    	}

    	// Check if the post type has a identifier
    	if(empty($postTypeModel->identifier)){
    		$errorMessages = 'The post type does not have a identifier.';
    		if(array_has($postTypeModel->errorMessages, 'post_type_identifier_does_not_exist')){
    			$errorMessages = $postTypeModel->errorMessages['post_type_identifier_does_not_exist'];
    		}
    		return $this->abort($errorMessages);
		}
		
		// Validate if we need to validate a other post type before showing this post type
		$validateBefore = $this->validatePostTypeBefore($request, $postTypeModel, $id);
		if($validateBefore['status'] === false){
			$errorMessages = $validateBefore['message'];
    		return $this->abort($errorMessages, $validateBefore['config'], 'post_validation_error');
		}

        // If the user can only see his own posts
        if($postTypeModel->userCanOnlySeeHisOwnPosts){
            $where[] = ['post_author', '=', Auth::user()->id];
        }

        // Finding the post with the post_name instead of the id
        if($postTypeModel->getPostByPostName){
        	$where[] = ['post_name', '=', $id];
        } else {
        	$where[] = ['id', '=', $id];
        }

        // Query only the post type requested
        $where[] = ['post_type', '=', $postTypeModel->identifier];

        // Adding a custom query functionality so we can manipulate the find by the config
		if($postTypeModel->appendCustomWhereQueryToCmsPosts){
			foreach($postTypeModel->appendCustomWhereQueryToCmsPosts as $key => $value){
				$where[] = [$value[0], $value[1], $value[2]];
			}
		}

        // If the ID is empty, that means we are returning the frame to create a new post.
        if($id == '0'){
	        $post = $postTypeModel;
	    } else {
	    	$post = $postTypeModel::where($where)->first();
	    }

	    // Validate if the post exists
	    if(!$post){
        	$errorMessages = 'Post does not exist.';
    		if(array_has($postTypeModel->errorMessages, 'post_does_not_exist')){
    			$errorMessages = $postTypeModel->errorMessages['post_does_not_exist'];
    		}
    		return $this->abort($errorMessages);
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

        $postArray = $post->toArray();

        $postArraySanitized = [];

        if(array_has($postArray, 'id')){
        	$postArraySanitized['id'] = $postArray['id'];
		}

		if(array_has($postArray, 'post_title')){
        	$postArraySanitized['post_title'] = $postArray['post_title'];
		}

		if(array_has($postArray, 'post_name')){
        	$postArraySanitized['post_name'] = $postArray['post_name'];
		}

		if(array_has($postArray, 'status')){
        	$postArraySanitized['status'] = $postArray['status'];
		}

		if(array_has($postArray, 'post_type')){
        	$postArraySanitized['post_type'] = $postArray['post_type'];
		}

		if(array_has($postArray, 'created_at')){
        	$postArraySanitized['created_at'] = $postArray['created_at'];
		}

		if(array_has($postArray, 'updated_at')){
        	$postArraySanitized['updated_at'] = $postArray['updated_at'];
		}

		// Format the collection
        $collection = [
            'post' => $postArraySanitized,
            'postmeta' => $postmeta,
		];

        // Mergin the collection with the data and custom fields
        $collection['templates'] = $this->mergeCollectionWithView($postTypeModel->view, $collection, $postTypeModel);

		// Merge the configuration values
		$config = [];
		if($postTypeModel->config){
			$config = $postTypeModel->config;
		}

        $collection['config'] = $config;

        // Adding public config
        if($postTypeModel->skipCreation){
			$collection['config']['skip_creation'] = $postTypeModel->skipCreation;
			if($postTypeModel->skipToRouteName){
				$collection['config']['skip_to_route_name'] = $postTypeModel->skipToRouteName;
			}
        } else {
			$collection['config']['skip_creation'] = false;
			$collection['config']['skip_to_route_name'] = '';
		}
		
		// Adding public config
        if($postTypeModel->disableEditOnlyCheck){
        	$collection['config']['disable_edit_only_check'] = $postTypeModel->disableEditOnlyCheck;
        } else {
        	$collection['config']['disable_edit_only_check'] = false;
		}

		$allKeys = collect($this->getValidationsKeys($postTypeModel));

		// Adding public config
        if($postTypeModel->enableAllSpecificFieldsUpdate){
        	$collection['config']['specific_fields']['enable_all'] = $postTypeModel->enableAllSpecificFieldsUpdate;
			$collection['config']['specific_fields']['exclude_fields'] = $postTypeModel->excludeSpecificFieldsFromUpdate;			
			$collection['config']['specific_fields']['enabled_fields'] = $allKeys->keys();
        } else {
        	$collection['config']['specific_fields']['enable_all'] = $postTypeModel->enableAllSpecificFieldsUpdate;
			$collection['config']['specific_fields']['exclude_fields'] = $postTypeModel->excludeSpecificFieldsFromUpdate;			
			$collection['config']['specific_fields']['enabled_fields'] = $allKeys->where('single_field_updateable.active', 'true')->keys();
		}
		
        // Lets check if there are any manipulators active
        $collection = $this->showConditional($postTypeModel, $collection);

        // Lets check if there are any manipulators active
        $collection = $this->showMutator($postTypeModel, $collection);

        // Cleaning up the output
        unset($collection['postmeta']);

        // Returning the full collection
    	return response()->json($collection);
    }

    // Lets check if there are any manipulators active for showing the post
	protected function showMutator($postTypeModel, $collection)
	{
		foreach($collection['templates'] as $groupKey => $groupValue){

			foreach($groupValue['customFields'] as $key => $value){

				$customField = $this->getCustomFieldObject($postTypeModel, $key);

				if(array_has($customField, 'mutator') && !empty($customField['mutator'])){
					if(method_exists(new $customField['mutator'], 'out')){
						$customField = (new $customField['mutator'])->out($customField, $collection, $key, $postTypeModel);
						$holdValue = $collection['templates'][$groupKey]['customFields'][$key]['value'];
						$collection['templates'][$groupKey]['customFields'][$key] = $customField;
						$collection['templates'][$groupKey]['customFields'][$key]['value'] = $holdValue;
					}
				}

				if(array_key_exists('customFields', $value)){
					foreach($value['customFields'] as $innerKey => $innerValue){

						$customField = $this->getCustomFieldObject($postTypeModel, $innerKey);

						if(array_has($customField, 'mutator') && !empty($customField['mutator'])){
							if(method_exists(new $customField['mutator'], 'out')){
								$customField = (new $customField['mutator'])->out($customField, $collection, $innerKey, $postTypeModel);

								$holdValue = $collection['templates'][$groupKey]['customFields'][$key]['customFields'][$innerKey]['value'];
								$collection['templates'][$groupKey]['customFields'][$key]['customFields'][$innerKey] = $customField;
								$collection['templates'][$groupKey]['customFields'][$key]['customFields'][$innerKey]['value'] = $holdValue;
							}
						}

						if(array_key_exists('customFields', $innerValue)){
							foreach($innerValue['customFields'] as $innerInnerKey => $innerInnerValue){

								$customField = $this->getCustomFieldObject($postTypeModel, $innerInnerKey);

								if(array_has($customField, 'mutator') && !empty($customField['mutator'])){
									if(method_exists(new $customField['mutator'], 'out')){
										$customField = (new $customField['mutator'])->out($customField, $collection, $innerInnerKey, $postTypeModel);

										$holdValue = $collection['templates'][$groupKey]['customFields'][$key]['customFields'][$innerKey]['customFields'][$innerInnerKey]['value'];
										$collection['templates'][$groupKey]['customFields'][$key]['customFields'][$innerKey]['customFields'][$innerInnerKey] = $customField;
										$collection['templates'][$groupKey]['customFields'][$key]['customFields'][$innerKey]['customFields'][$innerInnerKey]['value'] = $holdValue;
									}
								}

							}
						}

					}
				}

			}

		}

		return $collection;
	}

	/**
	 * Get all the post meta keys of the post
	 */
	protected function retrieveConfigPostMetas($post, $postTypeModel)
	{
		$metaKeys = [];
		$metaTaxonomies = [];

		$allKeys = $this->getValidationsKeys($postTypeModel);
		foreach($allKeys as $key => $value){

			$customFieldObject = $this->getCustomFieldObject($postTypeModel, $key);

			if(isset($customFieldObject['type']) && $customFieldObject['type'] == 'taxonomy'){

				// We need to get the values from the taxonomy table
				$customfieldPostTypes = $this->getPostTypeIdentifiers($customFieldObject['post_type']);

				// Lets query the post to retrieve all the connected ids
				$taxonomyIds = $post->taxonomies()->whereIn('post_type', $customfieldPostTypes)
					->get();

				// Lets foreach all the posts because we only need the id
				$ids = [];
				foreach($taxonomyIds as $value){
					array_push($ids, $value->id);
				}

				$ids = json_encode($ids);

				$metaTaxonomies[$key] = [
					'meta_key' => $key,
					'meta_value' => $ids,
				];

			// The other items are default
			} else {

				// Register it to the main array so we can query it later
				array_push($metaKeys, $key);

			}

		}
 
		// Lets query the database to get only the values where we have registered the meta keys
		$postmetaSimple = $post->postmeta()
			->whereIn('meta_key', $metaKeys)
			->select(['meta_key', 'meta_value'])
			->get()
			->keyBy('meta_key')
			->toArray();
 
		// Lets attach the default post columns
		$defaultPostData = [];

		$defaultPostData['post_title'] = [
			'meta_key' => 'post_title',
			'meta_value' => $post->post_title,
		];

		$defaultPostData['post_name'] = [
			'meta_key' => 'post_name',
			'meta_value' => $post->post_name,
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
    protected function mergeCollectionWithView($view, $collection, $postTypeModel)
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
			  
					if(array_key_exists($customFieldKey, $postmeta)){
						$view[$templateKey]['customFields'][$customFieldKey]['value'] = $postmeta[$customFieldKey]['meta_value'];
					}

	                // When output is disabled, we need to remove the fields from the arrays
	                if(array_key_exists('output', $customField) && !$customField['output']){
	                	unset($view[$templateKey]['customFields'][$customFieldKey]);
					}
					
					// If the array custom fields is not empty
					if(!empty($customField['customFields'])){

						// We foreach all custom fields in this template section
						foreach($customField['customFields'] as $innerCustomFieldKey => $innerCustomField){
					
							// Lets set the key to the array
							$view[$templateKey]['customFields'][$customFieldKey]['customFields'][$innerCustomFieldKey]['id'] = $innerCustomFieldKey;
					
							if(array_key_exists($innerCustomFieldKey, $postmeta)){
								$view[$templateKey]['customFields'][$customFieldKey]['customFields'][$innerCustomFieldKey]['value'] = $postmeta[$innerCustomFieldKey]['meta_value'];
							}

							// When output is disabled, we need to remove the fields from the arrays
							if(array_key_exists('output', $innerCustomField) && !$innerCustomField['output']){
								unset($view[$templateKey]['customFields'][$customFieldKey]['customFields'][$innerCustomFieldKey]);
							}
					
						}

						// If the array custom fields is not empty
						if(!empty($innerCustomField['customFields'])){

							// We foreach all custom fields in this template section
							foreach($innerCustomField['customFields'] as $innerInnerCustomFieldKey => $innerInnerCustomField){
						
								// Lets set the key to the array
								$view[$templateKey]['customFields'][$customFieldKey]['customFields'][$innerCustomFieldKey]['customFields'][$innerInnerCustomFieldKey]['id'] = $innerInnerCustomFieldKey;
						
								if(array_key_exists($innerInnerCustomFieldKey, $postmeta)){
									$view[$templateKey]['customFields'][$customFieldKey]['customFields'][$innerCustomFieldKey]['customFields'][$innerInnerCustomFieldKey]['value'] = $postmeta[$innerInnerCustomFieldKey]['meta_value'];
								}

								// When output is disabled, we need to remove the fields from the arrays
								if(array_key_exists('output', $innerInnerCustomField) && !$innerInnerCustomField['output']){
									unset($view[$templateKey]['customFields'][$customFieldKey]['customFields'][$innerCustomFieldKey]['customFields'][$innerInnerCustomFieldKey]);
								}
						
							}
						}
					}
	            }
	        }
		}
 
        return $view;
    }
}
