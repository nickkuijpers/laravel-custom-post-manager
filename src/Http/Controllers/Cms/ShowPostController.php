<?php

namespace Niku\Cms\Http\Controllers\Cms;

use Illuminate\Http\Request;
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
    		if(array_has($postTypeModel->errorMessages, 'post_type_does_not_exist')){
    			$errorMessages = $postTypeModel->errorMessages['post_type_does_not_exist'];
    		}
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
        $collection['templates'] = $this->mergeCollectionWithView($postTypeModel->view, $collection);

		// Merge the configuration values
		$config = [];
		if($postTypeModel->config){
			$config = $postTypeModel->config;
		}

        $collection['config'] = $config;

        // Adding public config
        if($postTypeModel->skipCreation){
        	$collection['config']['skip_creation'] = $postTypeModel->skipCreation;
        } else {
        	$collection['config']['skip_creation'] = false;
		}
		
		// Adding public config
        if($postTypeModel->disableEditOnlyCheck){
        	$collection['config']['disable_edit_only_check'] = $postTypeModel->disableEditOnlyCheck;
        } else {
        	$collection['config']['disable_edit_only_check'] = false;
        }

        // Lets check if there are any manipulators active
        $collection = $this->showConditional($postTypeModel, $collection);

        // Convert items to array by json decoding them
        $collection = $this->inArrayMutator($postTypeModel, $collection);

        // Lets check if there are any manipulators active
        $collection = $this->showMutator($postTypeModel, $collection);

        // Cleaning up the output
        unset($collection['postmeta']);

        // Returning the full collection
    	return response()->json($collection);
    }

    protected function showConditional($postTypeModel, $collection)
    {
    	foreach($collection['templates'] as $groupKey => $groupValue){

			foreach($groupValue['customFields'] as $key => $value){

				// Receiving the custom field
				$customField = $this->getCustomFieldObject($postTypeModel, $key);

				// if($postTypeModel->enableAllSpecificFieldsUpdate){
					$collection = $this->fieldSpecificVisibilityManager($collection, $customField, $postTypeModel, $groupKey, $key);
				// } else if(array_has($customField, 'single_field_updateable.active') && $customField['single_field_updateable']['active']){
					// dd('dasd');
					// $collection = $this->fieldSpecificVisibilityManager($collection, $customField, $postTypeModel, $groupKey, $key);
				// }

			}

		}

		return $collection;
    }

    protected function fieldSpecificVisibilityManager($collection, $customField, $postTypeModel, $groupKey, $key)
    {
		// Lets see if we have a mutator registered
		if(array_has($customField, 'conditional')){

			// Hiding values if operator is not met
			if(array_has($customField['conditional'], 'show_when')){

				$type = 'AND';
				if(array_key_exists('type', $customField['conditional'])){
					if($customField['conditional']['type'] == 'AND'){
						$type = 'AND';
					} else if($customField['conditional']['type'] == 'OR'){
						$type = 'OR';
					}
				}

				switch($type){
					case 'AND':

						$display = true;

						foreach($customField['conditional']['show_when'] as $conditionKey => $conditionValue){

							$conditionalCustomFieldValue = $this->getCustomFieldValue($postTypeModel, $collection, $conditionValue['custom_field']);
							$conditionCheck = $this->conditionTest($conditionValue['value'], $conditionValue['operator'], $conditionalCustomFieldValue);

							if($conditionCheck === false){
								$display = false;
							}

						}

						if($display === false){
							$collection['templates'][$groupKey]['customFields'][$key] = [];
						}

					break;
					case 'OR':

						$display = false;

						foreach($customField['conditional']['show_when'] as $conditionKey => $conditionValue){

							$conditionalCustomFieldValue = $this->getCustomFieldValue($postTypeModel, $collection, $conditionValue['custom_field']);
							$conditionCheck = $this->conditionTest($conditionValue['value'], $conditionValue['operator'], $conditionalCustomFieldValue);

							if($conditionCheck === true){
								$display = true;
							}

						}

						if($display === false){
							$collection['templates'][$groupKey]['customFields'][$key] = [];
						}

					break;
				}

			}

			// Hiding values if operator is not met
			if(array_has($customField['conditional'], 'override_when')){

				// Reset the item, we need to override all the values
				foreach($customField['conditional']['override_when'] as $conditionKey => $conditionValue){

					$conditionalCustomFieldValue = $this->getCustomFieldValue($postTypeModel, $collection, $conditionValue['custom_field']);

					if($this->conditionTest($conditionValue['value'], $conditionValue['operator'], $conditionalCustomFieldValue) !== false){
						foreach($conditionValue['override'] as $overrideKey => $overrideValue){
							$collection['templates'][$groupKey]['customFields'][$key][$overrideKey] = [];
						}
					}
				}

				$overrideables = [];
				foreach($customField['conditional']['override_when'] as $conditionKey => $conditionValue){

					$conditionalCustomFieldValue = $this->getCustomFieldValue($postTypeModel, $collection, $conditionValue['custom_field']);

					if($this->conditionTest($conditionValue['value'], $conditionValue['operator'], $conditionalCustomFieldValue) !== false){

						// Lets foreach the items we need to override
						foreach($conditionValue['override'] as $overrideKey => $overrideValue){

							// If it is a array, we need to save it as a array, else its just a value
							if(is_array($overrideValue)){

								// Foreaching all the overrideables of the inner array
								foreach($overrideValue as $innerKey => $innerValue){
									$collection['templates'][$groupKey]['customFields'][$key][$overrideKey][$innerKey] = $overrideValue[$innerKey];
								}

							} else {
								$collection['templates'][$groupKey]['customFields'][$key][$overrideKey] = $overrideValue;
							}

						}

					}

				}

			}

		}

		return $collection;
    }

    // Lets check if there are any manipulators active for showing the post
	protected function showMutator($postTypeModel, $collection)
	{
		foreach($collection['templates'] as $groupKey => $groupValue){

			foreach($groupValue['customFields'] as $key => $value){

				// Receiving the custom field
				$customField = $this->getCustomFieldObject($postTypeModel, $key);

				// Lets see if we have a mutator registered
				if(array_has($customField, 'mutator') && !empty($customField['mutator'])){
					if(method_exists(new $customField['mutator'], 'out')){
						$customField = (new $customField['mutator'])->out($customField, $collection, $key, $postTypeModel);

						$holdValue = $collection['templates'][$groupKey]['customFields'][$key]['value'];

						// Lets append the new data to the array
						$collection['templates'][$groupKey]['customFields'][$key] = $customField;

						// Add the holded value back
						$collection['templates'][$groupKey]['customFields'][$key]['value'] = $holdValue;
					}
				}

			}

		}

		return $collection;
	}

	// Lets check if there are any manipulators active for showing the post
	protected function inArrayMutator($postTypeModel, $collection)
	{
		foreach($collection['templates'] as $groupKey => $groupValue){

			foreach($groupValue['customFields'] as $key => $value){

				// Receiving the custom field
				$customField = $this->getCustomFieldObject($postTypeModel, $key);

				// Lets see if we have a mutator registered
				if(array_has($customField, 'is_array') && $customField['is_array']){

					$holdValue = '';
					if(array_key_exists('value', $value)){
						$holdValue = json_decode($value['value']);
					}

					// Add the holded value back
					$collection['templates'][$groupKey]['customFields'][$key]['value'] = $holdValue;
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

	                // When output is disabled, we need to remove the fields from the arrays
	                if(array_key_exists('output', $customField) && !$customField['output']){
	                	unset($view[$templateKey]['customFields'][$customFieldKey]);
	                }
	            }
	        }
        }

        return $view;
    }
}
