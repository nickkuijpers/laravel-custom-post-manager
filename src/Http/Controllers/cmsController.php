<?php
namespace Niku\Cms\Http\Controllers;

use App\Http\Controllers\Controller;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Niku\Cms\Http\NikuPosts;

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

			// Resetting the rule variable so no validation rules are resused
			$rule = '';

			// Setting the path to get the validation rules
			if(strpos($key, '_repeater_') !== false) {
				$explodedValue = explode('_', $key);

				// For each all groups to get the validation
				foreach($postTypeModel->view as $templateKey => $template){
					if(array_has($template, 'customFields.' . $explodedValue[0] . '.customFields.' . $explodedValue[3] . '.validation')){
						$rule = $template['customFields'][$explodedValue[0]]['customFields'][$explodedValue[3]]['validation'];
					}
				}

			} else {

				// For each all groups to get the validation
				foreach($postTypeModel->view as $templateKey => $template){
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

		// dd($validationRules);
		return $validationRules;
	}

	protected function getValidationsKeys($postTypeModel)
	{
		$validationsKeys = [];
		foreach($postTypeModel->view as $key => $value){

			foreach($value['customFields'] as $innerKey => $innerValue){

				$validationsKeys[$innerKey] = $innerKey;
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

			// Lets validate if there is a mutator for this value
			$value = $this->saveMutator($postTypeModel, $key, $value, $post, $request->toArray());

			switch($key){
				case 'post_title':
					$post->$key = $value;
				break;
				case 'post_content':
					$post->$key = $value;
				break;
				case 'post_excerpt':
					$post->$key = $value;
				break;
				case 'template':
					$post->$key = $value;
				break;
				case 'post_password':
					$post->$key = $value;
				break;
				case 'post_name':

					// Validate if we need to sanitize the post name or not.
					if(!$postTypeModel->disableSanitizingPostName){
						$post->post_name = $this->sanitizeUrl($request['post_name']);
					} else {
						$post->post_name = $request['post_name'];
					}

				break;
				case 'menu_order':
					$post->$key = $value;
				break;
				case 'status':
					$post->$key = $value;
				break;
				case 'post_author':
					$post->$key = $value;
				break;
				case 'updated_at':

					// We need to convert the input to a normal format based on the custom field setting
					$createdAtCustomField = $this->getCustomFieldObject($postTypeModel, 'updated_at');

					// Convert the date by the inserted format data in the custom field setting
					$convertedUpdatedAtDate = Carbon::createFromFormat($createdAtCustomField['date_format_php'], $request->get('updated_at'));

					// Save the converted date to the model
					$post->updated_at = $convertedUpdatedAtDate;

				break;
				case 'created_at':

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
		if(Auth::check()){
			$post->post_author = Auth::user()->id;
		} else {
			$post->post_author = 0;
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

	protected function removeUnregistratedFields($request, $postTypeModel)
	{
		$whitelisted = [];
		$whitelisted[] = 'template';

		// Lets foreach all the customfields so we can add it to the save array
		foreach($postTypeModel->view as $group){
			foreach($group['customFields'] as $key => $value){

				// Validating if this field can be saved
				if(array_key_exists('saveable', $value) && $value['saveable'] == false){

				} else {
					$whitelisted[] = $key;
				}
			}
		}

		$newRequest = $request->only($whitelisted);

		$request = new Request;

		foreach($newRequest as $key => $value){
			$request[$key] = $key;
		}

		return $request;
	}

	protected function removeUnrequiredMetas($postmeta)
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

		return $postmeta;
	}

	protected function savePostMetaToDatabase($postmeta, $postTypeModel, $post)
	{
		// Presetting a empty array so we can append pivot values to the sync function.
		$pivotValue = [];

		// Saving the meta values to the database
		foreach($postmeta as $key => $value){

			// Lets validate if there is a mutator for this value
			$value = $this->saveMutator($postTypeModel, $key, $value, $post, $postmeta);

			// Processing the repeater type values
			if((strpos($key, '_repeater_') !== false)){

				// Explode the value
				$explodedValue = explode('_', $key);

				// Foreaching all templates to validate if the key exists somewhere in a group
				foreach($postTypeModel->view as $templateKey => $template){

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
			foreach($postTypeModel->view as $templateKey => $template){

				if(array_has($template, 'customFields.' . $key)){

					// Lets get the custom field object from our niku-cms config
					$customFieldObject = $template['customFields'][$key];

					// When the custom field is marked as taxonomy, we need to
					// attach and sync the connections in the pivot table.
					if(isset($customFieldObject['type']) && $customFieldObject['type'] == 'taxonomy'){

						// Validate if there is any value given
						if(!empty($value)){

							// In the config of this custom field we have defined which post types this post
							// can be connected too. We need to add this to the where query to validate.
							$customfieldPostTypes = $this->getPostTypeIdentifiers($customFieldObject['post_type']);

							// Lets decode the json array with all the taxonomy id's
							foreach(json_decode($value) as $valueItem){

								// For each post id give, we need to query the database and validate if this
								// taxonomie of the connect post does exist and we got permission to it.
								$taxonomyPost = NikuPosts::where('id', '=', $valueItem)
									->whereIn('post_type', $customfieldPostTypes)
									->first();

								// If there is a taxonomy result, we can safely add it to the pivot.
								if($taxonomyPost) {
									$pivotValue[$valueItem] = ['taxonomy' => $key];
								}

							}

						}

					// Lets save the post meta to the database if it is not a taxonomy
					} else {

						// Saving it to the database
						$object = [
							'meta_key' => $key,
							'meta_value' => $value,
						];

						if(is_array($value)){
							$object['meta_value'] = json_encode($value);
						}

						// Update or create the meta key of the post
						$post->postmeta()->updateOrCreate([
							'meta_key' => $key
						], $object);
					}

					// Unsetting the value
					unset($postmeta[$key]);
					continue;
				}

			}

		}

		// Saving the sync to the database, if we do this inside the loop
		// it will delete the old ones so we need to prepare the array.
		$post->taxonomies()->sync($pivotValue);
	}

	// Lets check if there are any manipulators active for showing the post
	protected function saveMutator($postTypeModel, $key, $value, $post, $postmeta)
	{
		$post = $post->toArray();
		$postmeta = $postmeta;
		$postRequest = array_merge($post, $postmeta);

		// Receiving the custom field
		$customField = $this->getCustomFieldObject($postTypeModel, $key);

		if(!empty($customField)){

			// Lets see if we have a mutator registered
			if(array_has($customField, 'mutator') && !empty($customFields['mutator'])){

				if(method_exists(new $customField['mutator'], 'in')){
					$mutatorValue = (new $customField['mutator'])->in($value, $postRequest);

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

	public function getCustomFieldObject($postTypeModel, $key)
	{
		// Processing all other type values
		foreach($postTypeModel->view as $templateKey => $template){

			if(array_has($template, 'customFields.' . $key)){

				// As soon as we find the custom field object, lets return it.
				return $template['customFields'][$key];

			}

		}
	}

	public function getAllCustomFieldsKeys($postTypeModel)
	{
		$customFieldKeys = [];

		// Processing all other type values
		foreach($postTypeModel->view as $templateKey => $template){

			foreach($template['customFields'] as $key => $value){
				if(array_has($template, 'customFields.' . $key)){

					// As soon as we find the custom field object, lets return it.
					$customFieldKeys[$key] = $key;
				}

			}

		}

		return $customFieldKeys;
	}

	public function getCustomFieldValue($postTypeModel, $collection, $key)
	{
		// Get the custom field
		$customField = $this->getCustomFieldObject($postTypeModel, $key);
		$value = '';
		if(array_key_exists('value', $customField)){
			$value = $customField['value'];
		}
		if(array_has($collection, 'postmeta.' . $key)){
			$value = $collection['postmeta'][$key]['meta_value'];
		}

		return $value;
	}

	public function conditionTest($value, $operator, $conditionValue)
	{
		// echo $value;
		// echo '</br>';
		// echo $operator;
		// echo '</br>';
		// echo $conditionValue;
		// echo '</br>';
		// dd($value);
		switch($operator) {
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
	public function triggerEvent($action, $postTypeModel, $post)
	{
		if(!empty($postTypeModel->events)){
			if(array_key_exists($action, $postTypeModel->events)){
				foreach($postTypeModel->events[$action] as $event) {
					event(new $event($post));
				}
			}
		}
	}

	/**
	 * Abort the request
	 */
	public function abort($message = 'Not authorized.')
	{
		return response()->json([
			'code' => 'error',
			'status' => $message,
		], 422);
	}

	protected function removeValuesByConditionalLogic($postmeta, $postTypeModel, $collection)
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

									$conditionalCustomFieldValue = $this->getCustomFieldValue($postTypeModel, $postmetaCollection, $conditionValue['custom_field']);

									// If the condition is met, we need to remove the validation
									if($this->conditionTest($conditionValue['value'], $conditionValue['operator'], $conditionalCustomFieldValue) === false){
										$display = false;
									}

								}

								if(!$display){
									$postmeta[$key] = NULL;
								}

							}

						}

					}
				}

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

									$conditionalCustomFieldValue = $this->getCustomFieldValue($postTypeModel, $postmetaCollection, $conditionValue['custom_field']);

									// If the condition is met, we need to remove the validation
									if($this->conditionTest($conditionValue['value'], $conditionValue['operator'], $conditionalCustomFieldValue) === false){
										$display = false;
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
    	$allKeys = $this->getAllCustomFieldsKeys($postTypeModel);

    	foreach($postmeta as $key => $value){
    		if(in_array($key, $allKeys)){
    			$whitelistedCustomFields[$key] = $value;
    		}
    	}

    	return $whitelistedCustomFields;
    }

}
