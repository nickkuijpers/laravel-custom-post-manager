<?php
namespace Niku\Cms\Http\Controllers;

use App\Http\Controllers\Controller;
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

			if(!$postTypeModel->authorized()){
				return false;
			}

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
		// If the user needs to be authenticated, we need to make
		// sure we are not allowing the user to view the posts.
		if(!$this->userMustBeLoggedIn($postTypeModel)){
			return false;
		}

		// If users can only view their own posts, we need to make
		// sure that the users are logged in before continueing.
		if(!$this->userCanOnlySeeHisOwnPosts($postTypeModel)){
			return false;
		}

		return true;
	}

	/**
	 * The user must be logged in to view the post(s)
	 */
	protected function userMustBeLoggedIn($postTypeModel)
	{
		if($postTypeModel->userMustBeLoggedIn){
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

	protected function validatePostFields($postmeta, $request, $postTypeModel)
	{
		// Receive the default validations required for the post
		$validationRules = $postTypeModel->defaultValidationRules;

		// Getting the template structure
		$template = $postTypeModel->templates[$request->template];

		// Appending required validations to the default validations of the post
		foreach($postmeta as $key => $value){

			// Resetting the rule variable so no validation rules are resused
			$rule = '';

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

		return $validationRules;
	}

	protected function savePostToDatabase($post, $postTypeModel, $request, $postType)
	{
		// Saving the post data
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

		return $post;
	}

	protected function removeUnrequiredMetas($postmeta)
	{
		$unsetValues = ['_token', '_posttype', '_id', 'post_title', 'post_name', 'post_content', 'template', 'status'];

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

							// Validate if there is any value given
							if(!empty($value)){

								foreach(json_decode($value) as $valueItem){
									$pivotValue[$valueItem] = ['taxonomy' => $key];
								}

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
}
