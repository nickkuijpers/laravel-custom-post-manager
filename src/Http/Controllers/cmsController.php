<?php
namespace Niku\Cms\Http\Controllers;

use Carbon\Carbon;

use Illuminate\Http\Request;
use Niku\Cms\Http\NikuPosts;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Niku\Cms\Http\Controllers\Cms\CheckPostController;

class CmsController extends Controller
{
	/**
	 * Validating if the post type exists and returning the model.
	 */
	protected function getPostType($post_type)
	{
		// Receive the config variable where we have whitelisted all models
		$nikuConfig = config('niku-cms');

		// Validating if the model exists in the array
		if(array_key_exists($post_type, $nikuConfig['post_types'])){

			// Setting the model class
			$postTypeModel = new $nikuConfig['post_types'][$post_type];

			// Lets validate if the request has got the correct authorizations set
			if(!$this->authorizations($postTypeModel)){
				return false;
			}

			return $postTypeModel;

		} else {
			return false;
		}
	}

	protected function authorizations($postTypeModel)
	{
		// If users can only view their own posts, we need to make
		// sure that the users are logged in before continueing.
		if(!$this->userCanOnlySeeHisOwnPosts($postTypeModel)){
			return false;
		}

		return true;
	}

	/**
	 * If the user can only see his own post(s)
	 */
	protected function userCanOnlySeeHisOwnPosts($postTypeModel)
	{
		if($postTypeModel->userCanOnlySeeHisOwnPosts){
			if(!Auth::check()){
				return false;
			} else {
				return true;
			}
		} else {
			return true;
		}
	}

	/**
	 * Function for sanitizing slugs
	 */
	protected function sanitizeUrl($url)
	{
		$url = $url;
		$url = preg_replace('~[^\\pL0-9_]+~u', '-', $url); // substitutes anything but letters, numbers and '_' with separator
		$url = trim($url, "-");
		$url = iconv("utf-8", "us-ascii//TRANSLIT", $url); // TRANSLIT does the whole job
		$url = strtolower($url);
		$url = preg_replace('~[^-a-z0-9_]+~', '', $url); // keep only letters, numbers, '_' and separator
		return $url;
	}

	protected function validatePostFields($postmeta, $request, $postTypeModel, $singleFieldUpdate = false)
	{
		// Receive the default validations required for the post
		$validationRules = [];

		// Getting the template structure and validating if it exists
		if(empty($request->template)){
			$request->template = 'default';
		}
		$template = $postTypeModel->view[$request->template];
		
		// Possibility to update a single field if whitelabeled
		if($singleFieldUpdate){
			$validationFields = $postmeta;
 		} else {
 			$validationFields = $this->getValidationsKeys($postTypeModel);
		 }

		// Appending required validations to the default validations of the post
		foreach($validationFields as $key => $value){
			
			if(array_key_exists('validation', $value) && !empty($value['validation'])){
				$validationRules[$key] = $value['validation'];
			}

		}

		return $validationRules;
	}

	protected function getValidationsKeys($postTypeModel)
	{
		$validationsKeys = [];

		// Processing all other type values
		foreach($postTypeModel->view as $viewKey => $viewValue){

			// For each custom field
			foreach($viewValue['customFields'] as $customfieldKey => $customfieldValue){

				$validationsKeys[$customfieldKey] = $customfieldValue;
		
				if(array_key_exists('customFields', $customfieldValue)){

					foreach($customfieldValue['customFields'] as $innerKey => $innerValue){

						$validationsKeys[$innerKey] = $innerValue;

						if(array_key_exists('customFields', $innerValue)){

							foreach($innerValue['customFields'] as $innerKey => $innerValue){
 
								$validationsKeys[$innerKey] = $innerValue;
								
							}

						}

					}

				}

			}

		}
	
		return $validationsKeys;
	}

	protected function savePostToDatabase($action, $post, $postTypeModel, $request, $singleFieldUpdate = false)
	{
		// Remove unregistrated fields
		if($singleFieldUpdate){
			$request = $request;
 		} else {
 			$request = $this->removeUnregistratedFields($request, $postTypeModel);
		 }
		 
		// Lets map all the items
		foreach($request->all() as $key => $value){

			switch($key){
				case 'post_title':
					$value = $this->saveMutator($postTypeModel, $key, $value, $post, $request->toArray());
					$post->$key = $value;
				break;
				case 'post_content':
					$value = $this->saveMutator($postTypeModel, $key, $value, $post, $request->toArray());
					$post->$key = $value;
				break;
				case 'post_excerpt':
					$value = $this->saveMutator($postTypeModel, $key, $value, $post, $request->toArray());
					$post->$key = $value;
				break;
				case 'template':
					$value = $this->saveMutator($postTypeModel, $key, $value, $post, $request->toArray());
					$post->$key = $value;
				break;
				case 'post_password':
					$value = $this->saveMutator($postTypeModel, $key, $value, $post, $request->toArray());
					$post->$key = $value;
				break;
				case 'post_name':

					$value = $this->saveMutator($postTypeModel, $key, $value, $post, $request->toArray());

					// Validate if we need to sanitize the post name or not.
					if(!$postTypeModel->disableSanitizingPostName){
						$post->post_name = $this->sanitizeUrl($request['post_name']);
					} else {
						$post->post_name = $request['post_name'];
					}

				break;
				case 'post_parent':
					$value = $this->saveMutator($postTypeModel, $key, $value, $post, $request->toArray());
					$post->$key = $value;
				break;
				case 'menu_order':
					$value = $this->saveMutator($postTypeModel, $key, $value, $post, $request->toArray());
					$post->$key = $value;
				break;
				case 'status':
					$value = $this->saveMutator($postTypeModel, $key, $value, $post, $request->toArray());
					$post->$key = $value;
				break;
				case 'post_author':
					$value = $this->saveMutator($postTypeModel, $key, $value, $post, $request->toArray());
					$post->$key = $value;
				break;
				case 'custom':
					$value = $this->saveMutator($postTypeModel, $key, $value, $post, $request->toArray());
					$post->$key = $value;
				break;
				case 'updated_at':

					$value = $this->saveMutator($postTypeModel, $key, $value, $post, $request->toArray());

					// We need to convert the input to a normal format based on the custom field setting
					$createdAtCustomField = $this->getCustomFieldObject($postTypeModel, 'updated_at');

					// Convert the date by the inserted format data in the custom field setting
					$convertedUpdatedAtDate = Carbon::createFromFormat($createdAtCustomField['date_format_php'], $request->get('updated_at'));

					// Save the converted date to the model
					$post->updated_at = $convertedUpdatedAtDate;

				break;
				case 'created_at':

					$value = $this->saveMutator($postTypeModel, $key, $value, $post, $request->toArray());

					if($action == 'create'){

						// We need to convert the input to a normal format based on the custom field setting
						$createdAtCustomField = $this->getCustomFieldObject($postTypeModel, 'created_at');

						// Convert the date by the inserted format data in the custom field setting
						$convertedCreatedAtDate = Carbon::createFromFormat($createdAtCustomField['date_format_php'], $request->get('created_at'));

						// Save the converted date to the model
						$post->created_at = $convertedCreatedAtDate;

					}

				break;
			}
		}

		// If the post_name is requested as random in the post type, we create a unique random string
		if($action == 'create'){
			if($postTypeModel->makePostNameRandom){
				$post->post_name = $this->randomUniqueString();
			}
		}
 
		// Setting some global settings
		$post->post_type = $postTypeModel->identifier;

		// Check if user is logged in to set the author id
		if($postTypeModel->userCanOnlySeeHisOwnPosts){
			if(Auth::check()){
				$post->post_author = Auth::user()->id;
			} else {
				$post->post_author = 0;
			}
		}

		$post->save();

		return $post;
	}

	protected function randomUniqueString()
	{
        $done = 0;
        while(!$done){

            // Creating a unique identifier
            $uniqueIdentifier = str_replace('/', '-',uniqid(str_random(36)));

            // Lets validate if there is none already to prevent error
            $existingValidation = NikuPosts::where([
                ['post_name', '=', $uniqueIdentifier]
            ])->count();

            // Continue as long as there is no unique value
            if($existingValidation === 0){
                $done = 1;
            }

        }

        return $uniqueIdentifier;
	}

	public function resetRequestValues($request)
	{
		foreach($request->all() as $key => $value){
			unset($request[$key]);
		}
		return $request;
	}

	// Removing request values, based on input when they are not saveable
	protected function removeUnregistratedFields($request, $postTypeModel)
	{
		$whitelisted = [];
		$whitelisted[] = 'template';

		foreach($request->all() as $requestKey => $requestValue){
			$customField = $this->getCustomFieldObject($postTypeModel, $requestKey);
			if($customField){
				if(array_key_exists('saveable', $customField) && $customField['saveable'] == false){
				
				} else {
					$whitelisted[] = $requestKey;
				}
			}
		}
    
		$newRequest = $request->only($whitelisted);

		$request = $this->resetRequestValues($request);
		foreach($newRequest as $key => $value){
			$request[$key] = $value;
		}

		return $request;
	}

	protected function removeUnrequiredMetas($postmeta, $postTypeModel)
	{
		$unsetValues = [
			'_token',
			'_posttype',
			'_id',
			'post_title',
			'post_name',			
			'post_content',
			'template',
			'status',
			'created_at',
			'updated_at',
		];
 
		foreach($unsetValues as $value){
			unset($postmeta[$value]);
		}

		foreach($postmeta as $key => $value){
			$customFieldObject = $this->getCustomFieldObject($postTypeModel, $key);

			if(!is_array($customFieldObject)){
				unset($postmeta[$key]);
			}
		}
 

		return $postmeta;
	}

	// Saving the post meta to the database
	protected function savePostMetaToDatabase($postmeta, $postTypeModel, $post, $request = [], $customFieldKey = '')
	{
		// Presetting a empty array so we can append pivot values to the sync function.
		$pivotValue = [];
		$object = [];
 
		$postmeta = $this->removeUnrequiredMetas($postmeta, $postTypeModel);

		if(!empty($customFieldKey)){
			if($request->hasFile('file')){
				$postmeta[$customFieldKey] = '';
			}
		}
 
		// Saving the meta values to the database.
		foreach($postmeta as $key => $value){

			$customFieldObject = $this->getCustomFieldObject($postTypeModel, $key);
			if($customFieldObject){
				if(array_key_exists('saveable', $customFieldObject)){
					if($customFieldObject['saveable'] === false){
						continue;
					}
				}
			}

			// Lets validate if there is a mutator for this value.
			$value = $this->saveMutator($postTypeModel, $key, $value, $post, $postmeta, $request);

			// If the custom field does not exist, we may not save it.
			$customFieldObject = $this->getCustomFieldObject($postTypeModel, $key);
			if(!is_array($customFieldObject)){
				continue;
			}

			$type = 'simple';
			if(array_key_exists('type', $customFieldObject)){
				$type = $customFieldObject['type'];
			}
			switch($type){
				case 'taxonomy':
	
					if(!array_key_exists('post_type', $customFieldObject) && empty($customFieldObject['post_type'])){
						continue;
					}
					
					$customfieldPostTypes = $this->getPostTypeIdentifiers($customFieldObject['post_type']);

					if(!is_array($customfieldPostTypes)){
						continue;
					}

					if(!is_array($value)){
						$value = json_decode($value, true);
					}
					
					// Yes, double. 
					if(!is_array($value)){
						continue;
					}
					
					foreach($value as $valueKey => $valueItem){

						$where = [];

						if($postTypeModel->userCanOnlySeeHisOwnPosts){
							$where[] = ['post_author', '=', Auth::user()->id];
						}

						$where[] = ['id', '=', $valueItem];
						
						// For each post id give, we need to query the database and validate if this
						// taxonomie of the connect post does exist and we got permission to it.
						$taxonomyPost = NikuPosts::where($where)
							->whereIn('post_type', $customfieldPostTypes)
							->first();

						// If there is a taxonomy result, we can safely add it to the pivot.
						if($taxonomyPost) {
							$pivotValue[$valueItem] = ['taxonomy' => $key];
						}

					}

					$post->taxonomies()->sync($pivotValue);

				break;
				default:

					$object[$key] = $value;

				break;
			}
   
		}

		$post->saveMetas($object);
		
		return $post;
	}

	// Lets check if there are any manipulators active for showing the post
	protected function saveMutator($postTypeModel, $key, $value, $post, $postmeta, $request = [])
	{
		$post = $post->toArray();
		$postmeta = $postmeta;
		$postRequest = array_merge($post, $postmeta);

		// Receiving the custom field
		$customField = $this->getCustomFieldObject($postTypeModel, $key);
		
		if(!empty($customField)){

			// Lets see if we have a mutator registered
			if(array_has($customField, 'mutator') && !empty($customField['mutator'])){

				if(method_exists(new $customField['mutator'], 'in')){
					$mutatorValue = (new $customField['mutator'])->in($customField, $postRequest, $value, $key, $postTypeModel, $request);

					// Lets set the new value to the existing value
					$value = $mutatorValue;
				}

			}

		}

		return $value;
	}

	/**
	 * Get the post type real identifiers how it is saved in the database
	 */
	protected function getPostTypeIdentifiers($postTypes)
	{
		$postTypeIdentifiers = [];

		foreach($postTypes as $postTypeKey => $value){

			$postTypeModel = $this->getPostType($value);
			if($postTypeModel){

				// Add the real identifier to the array
				array_push($postTypeIdentifiers, $postTypeModel->identifier);
			}
		}

		return $postTypeIdentifiers;
	}

	// Return the custom field object based on the identifier
	public function getCustomFieldObject($postTypeModel, $key)
	{
		$allKeys = $this->getValidationsKeys($postTypeModel);

		if(array_key_exists($key, $allKeys)){
			return $allKeys[$key];
		}
		
		return false;
	}

	public function getCustomFieldValue($postTypeModel, $collection, $key)
	{		
		if(is_object($collection)){
			$collection = $collection->toArray();
		} else {
			$collection = $collection;
		}

		// Get the custom field
		$customField = $this->getCustomFieldObject($postTypeModel, $key);
		if(empty($customField)){
			return '';
		}
		
		$value = '';
		if(array_key_exists('value', $customField)){
			$value = $customField['value'];
		}

		if(array_key_exists('postmeta', $collection)){
			foreach($collection['postmeta'] as $collectionKey => $collectionValue){
				if($key == $collectionValue['meta_key']){
					if($collectionValue['meta_value']){
						$value = $collectionValue['meta_value'];
					}
				}
			}
		}
		
		return $value;
	}

	public function getCustomFieldValueWithoutConfig($postTypeModel, $collection, $key)
	{		
		$value = '';

		if(is_object($collection)){
			$collection = $collection->toArray();
		} else {
			$collection = $collection;
		}

		// Get the custom field
		$customField = $this->getCustomFieldObject($postTypeModel, $key);
		foreach($collection as $collectionKey => $collectionValue){
			if($collectionKey == 'postmeta'){
				if(is_array($collectionValue)){
					foreach($collectionValue as $metaKey => $metaValue){
						if($key == $metaValue['meta_key']){
							if($metaValue['meta_value']){
								$value = $metaValue['meta_value'];
							}
						}
					}
				}
			} else {
				if($key == $collectionKey){
					$value = $collectionValue;
				}
			}
		}
  
		return $value;
	}

	public function conditionTest($value, $operator, $conditionValue)
	{
		switch($operator) {
			case 'in_array':
				if(! is_array($conditionValue)){
					$conditionValue = json_decode($conditionValue);
					if(is_array($conditionValue)){
						if(in_array($value, $conditionValue)){
							return true;
						}
					}
				} else {
					if(in_array($value, $conditionValue)){
						return true;
					}
				}

				return false;
			break;
			case 'not_in_array':
				if(! is_array($conditionValue)){
					$conditionValue = json_decode($conditionValue);
					if(is_array($conditionValue)){
						if(in_array($value, $conditionValue)){
							return false;
						}
					}
				} else {
					if(in_array($value, $conditionValue)){
						return false;
					}
				}
				return true;
			break;
			case '==':
				if($value == $conditionValue){
					return true;
				} else {
					return false;
				}
			break;
			case '===':
				if($value === $conditionValue){
					return true;
				} else {
					return false;
				}
			break;
				case '!=':
				if(!$value != $conditionValue){
					return true;
				} else {
					return false;
				}
			break;
			case '<>';
				if($value <> $conditionValue){
					return true;
				} else {
					return false;
				}
			break;
			case '!==':
				if($value !== $conditionValue){
					return true;
				} else {
					return false;
				}
			break;
			case '<':
				if($value < $conditionValue){
					return true;
				} else {
					return false;
				}
			break;
			case '>':
				if($value > $conditionValue){
					return true;
				} else {
					return false;
				}
			break;
			case '<=':
				if($value <= $conditionValue){
					return true;
				} else {
					return false;
				}
			break;
			case '>=':
				if($value >= $conditionValue){
					return true;
				} else {
					return false;
				}
			break;
			case '<=>':
				if($value <=> $conditionValue){
					return true;
				} else {
					return false;
				}
			break;
			default:
				return false;
			break;
		}
	}

	/**
	 * Integrate events based on the action
	 */
	public function triggerEvent($action, $postTypeModel, $post, $postmeta)
	{
		if(method_exists($postTypeModel, $action)){
			$postTypeModel->$action($postTypeModel, $post, $postmeta);
		}
	}

	public function validatePostTypeBefore($postmeta, $postTypeModel, $id)
	{
		if($postTypeModel->validatePostTypeBefore){
			if(count($postTypeModel->validatePostTypeBefore) > 0){
				$i = 0;
				foreach($postTypeModel->validatePostTypeBefore as $key => $value){
					$i++;

					// Does the before post type exist?
					$postTypeModelBefore = $this->getPostType($key);
					if(!$postTypeModelBefore){
						return [
							'status' => false,
							'message' => 'Validation of post type does not exist',	
						];
					}

					$validationResult = (new CheckPostController)->internal($postmeta, $key, $id, 'array');
					if(is_array($validationResult)){
						if(array_key_exists('code', $validationResult) && $validationResult['code'] == 'success'){
							continue;
						}
					} else if (is_object($validationResult)){
						if($validationResult->code && $validationResult->code == 'success'){
							continue;
						}
					}

					if($validationResult->errors){
						if(is_array($validationResult->errors)){
							if(count($validationResult->errors) > 0){
								return [
									'status' => false,
									'message' => 'Validation of post type does not exist',	
									'config' => [
										'return_to' => $value['return_to'],
										'route_identifier' => $value['route_identifier'],
										'errors' => $validationResult->errors,
									],
								];
							}
						}
					}
				}
			}

		}

		return [
			'status' => true,
			'message' => ''
		];
	}

	/**
	 * Abort the request
	 */
	public function abort($message = 'Not authorized.', $config = '', $code = 'error')
	{
		return response()->json([
			'code' => $code,
			'errors' => [
				$code => [
					0 => $message,
				],
			],
			'config' => $config,
		], 422);
	}

	protected function removeValuesByConditionalLogic($postmeta, $postTypeModel, $collection)
    {
		$allKeys = $this->getValidationsKeys($postTypeModel);
		 
		foreach($allKeys as $key => $customField){
			
			$display = $this->validateValueForLogic($customField, $postTypeModel, $collection);

			if($display === false){
				unset($postmeta[$key]);								
				// $postmeta[$key] = null;
			}

		}
		
		return $postmeta;
	}

	public function validateValueForLogic($customField, $postTypeModel, $collection)
	{
		$display = true;

		if(array_key_exists('conditional', $customField) && array_key_exists('show_when', $customField['conditional'])){
			
			if(array_key_exists('AND', $customField['conditional']['show_when'])){
			
				foreach($customField['conditional']['show_when']['AND'] as $conditionKey => $conditionValue){

					$conditionalCustomFieldValue = $this->getCustomFieldValue($postTypeModel, $collection, $conditionValue['custom_field']);
					$conditionCheck = $this->conditionTest($conditionValue['value'], $conditionValue['operator'], $conditionalCustomFieldValue);

					if($conditionCheck === false){
						$display = false;
					}

				}

			}
			
			if($display === true){
				
				$displayOr = false;
				if(array_key_exists('OR', $customField['conditional']['show_when'])){
					
					foreach($customField['conditional']['show_when']['OR'] as $conditionKey => $conditionValue){

						$conditionalCustomFieldValue = $this->getCustomFieldValue($postTypeModel, $collection, $conditionValue['custom_field']);
						$conditionCheck = $this->conditionTest($conditionValue['value'], $conditionValue['operator'], $conditionalCustomFieldValue);
						if($conditionCheck === true){
							$displayOr = true;
						}

					}

					if($displayOr === false){
						$display = false;
					}
					
				}

			}

		}

		return $display;
	}
	
	// Returning false values by array keys of the items which we need to exclude out of the validation array
	protected function removeValidationsByConditionalLogic($postmeta, $postTypeModel, $collection)
    {
		$allKeys = $this->getValidationsKeys($postTypeModel);
 
		foreach($allKeys as $key => $customField){

			if(array_key_exists($key, $postmeta)){
				$postmeta[$key] = true;
			}

			$display = $this->validateValueForLogic($customField, $postTypeModel, $collection);

			if($display === false){					
				$postmeta[$key] = false;
			} else {
				$postmeta[$key] = true;
			}
		}

		return $postmeta;
    }

    /**
     *
     */
    protected function validateFieldByConditionalLogic($validationRules, $postTypeModel, $collection)
    {
    	foreach($postTypeModel->view as $groupKey => $groupValue){

			foreach($groupValue['customFields'] as $key => $value){

				// Receiving the custom field
				$customField = $this->getCustomFieldObject($postTypeModel, $key);

				// First check if we have enabled the 'single_field_updateable'
				if(array_has($customField, 'single_field_updateable.active')){
					if($customField['single_field_updateable']['active']){

						// Lets see if we have a mutator registered
						if(array_has($customField, 'conditional')){

							// Hiding values if operator is not met
							if(array_has($customField['conditional'], 'show_when')){

								$display = true;
								foreach($customField['conditional']['show_when'] as $conditionKey => $conditionValue){
									$conditionStatus = false;

									// Convert structure to new object
									$postmetaCollection = [
										'postmeta' => $collection->postmeta->keyBy('meta_key')->toArray()
									];

									if(array_key_exists('custom_field', $conditionValue)){
										$conditionalCustomFieldValue = $this->getCustomFieldValue($postTypeModel, $postmetaCollection, $conditionValue['custom_field']);

										// If the condition is met, we need to remove the validation
										if($this->conditionTest($conditionValue['value'], $conditionValue['operator'], $conditionalCustomFieldValue) === false){
											$display = false;
										}
									}

								}

								if(!$display){
									unset($validationRules[$key]);
								}

							}

							// Hiding values if operator is not met
							if(array_has($customField['conditional'], 'override_when')){

								$display = true;
								foreach($customField['conditional']['override_when'] as $conditionKey => $conditionValue){
									$conditionStatus = false;

									// Convert structure to new object
									$postmetaCollection = [
										'postmeta' => $collection->postmeta->keyBy('meta_key')->toArray()
									];

									$conditionalCustomFieldValue = $this->getCustomFieldValue($postTypeModel, $postmetaCollection, $conditionValue['custom_field']);

									// If the condition is met, we need to remove the validation
									if($this->conditionTest($conditionValue['value'], $conditionValue['operator'], $conditionalCustomFieldValue) !== false){
										if(array_has($conditionValue, 'override.validation')){

											$validationRule = $conditionValue['override']['validation'];
											$validationRules[$key] = $validationRule;
										}
									}

								}

							}

						}

					}
				}

			}

		}

		return $validationRules;
    }

    public function getWhitelistedCustomFields($postTypeModel, $postmeta)
    {
    	$whitelistedCustomFields = [];
    	$allKeys = $this->getValidationsKeys($postTypeModel);
		
		foreach($postmeta as $key => $value){
    		if(array_key_exists($key, $allKeys)){
				$customFieldObject = $this->getCustomFieldObject($postTypeModel, $key);
				if(!empty($customFieldObject)){
					$whitelistedCustomFields[$key] = $value;
				}
    		}
    	}

    	return $whitelistedCustomFields;
    }

	protected function findPostInstance($postTypeModel, $request, $postType, $id, $action = '')
    {
		if($id == "0"){
			$post = $postTypeModel;
		} else {

			$where = [];

			// If the user can only see his own posts
			if($postTypeModel->userCanOnlySeeHisOwnPosts){
				$where[] = ['post_author', '=', Auth::user()->id];
			}

			// Adding a custom query functionality so we can manipulate the find by the config
			if($postTypeModel->appendCustomWhereQueryToCmsPosts){
				foreach($postTypeModel->appendCustomWhereQueryToCmsPosts as $key => $value){
					$where[] = [$value[0], $value[1], $value[2]];
				}
			}

			// Lets check if we have configured a custom post type identifer
			if(!empty($postTypeModel->identifier)){
				$postType = $postTypeModel->identifier;
			}
			
			// Finding the post with the post_name instead of the id
			if(!empty($postTypeModel->getPostByCustom)){
				$where[] = [$postTypeModel->getPostByCustom, '=', $id];
			} else {
				if($postTypeModel->getPostByPostName){
					$where[] = ['post_name', '=', $id];
				} else {
					$where[] = ['id', '=', $id];
				}
			}

			$postTypeAliases = [];
			$postTypeIsArray = false;
			if(!empty($postTypeModel->postTypeAliases)){
				if(is_array($postTypeModel->postTypeAliases)){
					if(count($postTypeModel->postTypeAliases) > 0){
						$postTypeIsArray = true;
						$postTypeAliases = $postTypeModel->postTypeAliases;
						$postTypeAliases[] = $postType;
					}
				}
			}

			$appendQuery = false;

			switch($action){
				case 'check_post':
					if(method_exists($postTypeModel, 'append_show_check_query')){
						$appendQuery = true;
					}
				break;
				case 'delete_post':
					if(method_exists($postTypeModel, 'append_show_delete_query')){
						$appendQuery = true;
					}
				break;
				case 'edit_post':
					if(method_exists($postTypeModel, 'append_show_edit_query')){
						$appendQuery = true;
					}
				break;
				case 'show_post':
					if(method_exists($postTypeModel, 'append_show_get_query')){
						$appendQuery = true;
					}
				break;
				case 'specific_field_post':
					if(method_exists($postTypeModel, 'append_show_specific_field_query')){
						$appendQuery = true;
					}
				break;
				default:
					if(method_exists($postTypeModel, 'append_show_crud_query')){
						$appendQuery = true;
					}
				break;
			}
				
			// Query the database
			$appendQuery = false;
			$post = $postTypeModel::where($where)
				->when($appendQuery, function ($query) use ($postTypeModel, $request, $action){
					switch($action){
						case 'check_post':
							return $postTypeModel->append_show_check_query($query, $postTypeModel, $request);		
						break;
						case 'delete_post':
							return $postTypeModel->append_show_delete_query($query, $postTypeModel, $request);		
						break;
						case 'edit_post':
							return $postTypeModel->append_show_edit_query($query, $postTypeModel, $request);		
						break;
						case 'show_post':
							return $postTypeModel->append_show_get_query($query, $postTypeModel, $request);		
						break;
						case 'specific_field_post':
							return $postTypeModel->append_show_specific_field_query($query, $postTypeModel, $request);	
						break;
						default:
							return $postTypeModel->append_show_crud_query($query, $postTypeModel, $request);		
						break;
					}
				})

				// When there are multiple post types
				->when($postTypeIsArray, function ($query) use ($postTypeAliases, $postTypeModel){
					if($postTypeModel->disableDefaultSettings !== true){
						return $query->whereIn('post_type', $postTypeAliases);
					}
					
				// false
				}, function($query) use ($postType, $postTypeModel) {
					if($postTypeModel->disableDefaultSettings !== true){
						if(!empty($postType)){
							return $query->where('post_type', $postType);
						}
					}
				})
				
				->with('postmeta')
				->first();
		}

		return $post;
	}
	
	public function showConditional($postTypeModel, $collection)
    {
    	foreach($collection['templates'] as $groupKey => $groupValue){

			foreach($groupValue['customFields'] as $key => $value){

				// Receiving the custom field
				$customField = $this->getCustomFieldObject($postTypeModel, $key);

				$collection = $this->fieldSpecificVisibilityManager($collection, $customField, $postTypeModel, $groupKey, $key, 1);

				if(array_key_exists('customFields', $value)){

					foreach($value['customFields'] as $innerKey => $innerValue){

						// Receiving the custom field
						$customField = $this->getCustomFieldObject($postTypeModel, $innerKey);

						$collection = $this->fieldSpecificVisibilityManager($collection, $customField, $postTypeModel, $groupKey, $key, 2, $innerKey);

						if(array_key_exists('customFields', $innerValue)){

							foreach($innerValue['customFields'] as $innerInnerKey => $innerInnerValue){
 
								// Receiving the custom field
								$customField = $this->getCustomFieldObject($postTypeModel, $innerInnerKey);

								$collection = $this->fieldSpecificVisibilityManager($collection, $customField, $postTypeModel, $groupKey, $key, 3, $innerKey, $innerInnerKey);
								
							}

						}

					}

				}


			}

		}

		return $collection;
    }

    public function fieldSpecificVisibilityManager($collection, $customField, $postTypeModel, $groupKey, $key, $level = 1, $innerKey = null, $innerInnerKey = null)
    {
		// Lets see if we have a mutator registered
		if(array_key_exists('conditional', $customField)){
			
			// Hiding values if operator is not met
			if(array_key_exists('show_when', $customField['conditional'])){
				
				$display = true;

				if(array_key_exists('AND', $customField['conditional']['show_when'])){
					
					foreach($customField['conditional']['show_when']['AND'] as $conditionKey => $conditionValue){

						$conditionalCustomFieldValue = $this->getCustomFieldValue($postTypeModel, $collection, $conditionValue['custom_field']);
						$conditionCheck = $this->conditionTest($conditionValue['value'], $conditionValue['operator'], $conditionalCustomFieldValue);

						if($conditionCheck === false){
							$display = false;
						}

					}

				}
				
				if($display === true){
					
					$displayOr = false;
					if(array_key_exists('OR', $customField['conditional']['show_when'])){
						
						foreach($customField['conditional']['show_when']['OR'] as $conditionKey => $conditionValue){

							$conditionalCustomFieldValue = $this->getCustomFieldValue($postTypeModel, $collection, $conditionValue['custom_field']);
							$conditionCheck = $this->conditionTest($conditionValue['value'], $conditionValue['operator'], $conditionalCustomFieldValue);
							if($conditionCheck === true){
								$displayOr = true;
							}

						}

						if($displayOr === false){
							$display = false;
						}
						
					}

				}

				if($display === false){
					switch($level){
						case 1:
							$collection['templates'][$groupKey]['customFields'][$key] = [];
						break;
						case 2:
							$collection['templates'][$groupKey]['customFields'][$key]['customFields'][$innerKey] = [];
						break;
						case 3:
							$collection['templates'][$groupKey]['customFields'][$key]['customFields'][$innerKey]['customFields'][$innerInnerKey] = [];
						break;
					}
				}

			}

			// Hiding values if operator is not met
			if(array_has($customField['conditional'], 'override_when')){

				// Reset the item, we need to override all the values
				foreach($customField['conditional']['override_when'] as $conditionKey => $conditionValue){

					$conditionalCustomFieldValue = $this->getCustomFieldValue($postTypeModel, $collection, $conditionValue['custom_field']);

					if($this->conditionTest($conditionValue['value'], $conditionValue['operator'], $conditionalCustomFieldValue) !== false){
						foreach($conditionValue['override'] as $overrideKey => $overrideValue){
							switch($level){
								case 1:
									$collection['templates'][$groupKey]['customFields'][$key][$overrideKey] = [];
								break;
								case 2:
									$collection['templates'][$groupKey]['customFields'][$key]['customFields'][$innerKey][$overrideKey] = [];
								break;
								case 3:
									$collection['templates'][$groupKey]['customFields'][$key]['customFields'][$innerKey]['customFields'][$innerInnerKey][$overrideKey] = [];
								break;
							}
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
									switch($level){
										case 1:
											$collection['templates'][$groupKey]['customFields'][$key][$overrideKey][$innerKey] = $overrideValue[$innerKey];
										break;
										case 2:
											$collection['templates'][$groupKey]['customFields'][$key]['customFields'][$innerKey][$overrideKey][$innerKey] = $overrideValue[$innerKey];
										break;
										case 3:
											$collection['templates'][$groupKey]['customFields'][$key]['customFields'][$innerKey]['customFields'][$innerInnerKey][$overrideKey][$innerKey] = $overrideValue[$innerKey];
										break;
									}
								}

							} else {
						
								switch($level){
									case 1:
										$collection['templates'][$groupKey]['customFields'][$key][$overrideKey] = $overrideValue;
									break;
									case 2:
										$collection['templates'][$groupKey]['customFields'][$key]['customFields'][$innerKey][$overrideKey] = $overrideValue;
									break;
									case 3:
										$collection['templates'][$groupKey]['customFields'][$key]['customFields'][$innerKey]['customFields'][$innerInnerKey][$overrideKey] = $overrideValue;
									break;
								}
							}

						}

					}

				}

			}

		}

		return $collection;
	}
	
	public function getMetaValueOutPostMetaArray($post, $key)
	{
		if(array_key_exists('postmeta', $post)){
			foreach($post['postmeta'] as $metaKey => $metaValue){
				if($metaValue['meta_key'] == $key){
					return $metaValue['meta_value'];
				}
			}
		}

		return '';
	}

	public function addValuesToCollection($collection, $toMerge)
	{
		foreach($collection['templates'] as $groupKey => $groupValue){

            foreach($groupValue['customFields'] as $customFieldKey => $customFieldValue){
            
                if(array_key_exists($customFieldKey, $toMerge)){
                    foreach($toMerge[$customFieldKey] as $toMergeKey => $toMergeValue){
                        $collection['templates'][$groupKey]['customFields'][$customFieldKey][$toMergeKey] = $toMergeValue;
                    }
                }

                if(array_key_exists('customFields', $customFieldValue)){
                    foreach($customFieldValue['customFields'] as $innerKey => $innerValue){
                    
                        if(array_key_exists($innerKey, $toMerge)){
                            foreach($toMerge[$innerKey] as $toMergeKey => $toMergeValue){
                                $collection['templates'][$groupKey]['customFields'][$customFieldKey]['customFields'][$innerKey][$toMergeKey] = $toMergeValue;
							}
						}       
						
						 if(array_key_exists('customFields', $innerValue)){
							foreach($innerValue['customFields'] as $innerInnerKey => $innerInnerValue){
							
								if(array_key_exists($innerInnerKey, $toMerge)){
									foreach($toMerge[$innerInnerKey] as $toMergeKey => $toMergeValue){
										$collection['templates'][$groupKey]['customFields'][$customFieldKey]['customFields'][$innerKey]['customFields'][$innerInnerKey][$toMergeKey] = $toMergeValue;
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
	
	// public function getValidationRulesById($templates, $key)
	// {
	// 	$validationRules = [];
		
	// 	foreach($templates as $groupKey => $groupValue){

    //         foreach($groupValue['customFields'] as $customFieldKey => $customFieldValue){
            
    //             if(array_key_exists($customFieldKey, $toMerge)){
    //                 foreach($toMerge[$customFieldKey] as $toMergeKey => $toMergeValue){
    //                     $collection['templates'][$groupKey]['customFields'][$customFieldKey][$toMergeKey] = $toMergeValue;
    //                 }
    //             }

    //             if(array_key_exists('customFields', $customFieldValue)){
    //                 foreach($customFieldValue['customFields'] as $innerKey => $innerValue){
                    
    //                     if(array_key_exists($innerKey, $toMerge)){
    //                         foreach($toMerge[$innerKey] as $toMergeKey => $toMergeValue){
    //                             $collection['templates'][$groupKey]['customFields'][$customFieldKey]['customFields'][$innerKey][$toMergeKey] = $toMergeValue;
	// 						}
	// 					}       
						
	// 					 if(array_key_exists('customFields', $innerValue)){
	// 						foreach($innerValue['customFields'] as $innerInnerKey => $innerInnerValue){
							
	// 							if(array_key_exists($innerInnerKey, $toMerge)){
	// 								foreach($toMerge[$innerInnerKey] as $toMergeKey => $toMergeValue){
	// 									$collection['templates'][$groupKey]['customFields'][$customFieldKey]['customFields'][$innerKey]['customFields'][$innerInnerKey][$toMergeKey] = $toMergeValue;
	// 								}
	// 							}       

	// 						}
	// 					}

    //                 }
    //             }

    //         }
	// 	}
		
	// 	dd($validationRules);
		// return $validationRules;
	// }

}
