<?php

namespace Niku\Cms\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Niku\Cms\Http\Config;
use Niku\Cms\Http\Posts;

class ConfigController extends Controller
{
	/**
	 * Display a single post
	 */
	public function show($group)
	{
		// Validate if the user is logged in
		if(! $this->userIsLoggedIn($group)){
			return $this->abort('User not authorized.');
		}
		// User email validation
		if ($this->userHasWhitelistedEmail($group)) {
			return $this->abort('User email is not whitelisted.');
		}
		// Validate if the post type exists
		$nikuConfig = config("niku-cms.config.{$group}");
		if(empty($nikuConfig)){
			return collect([
				'code' => 'error',
				'status' => 'Post type does not exist'
			]);
		}
		$config = Config::where('group', '=', $group)
			->get()
			->keyBy('option_name')
			->toArray();
		$data = $nikuConfig['view'];
		// Appending the key added in the config to the array
        // so we can use it very easliy in the component.
    	if(!empty($data['customFields'])){
    		// For each custom fields
            foreach($data['customFields'] as $ckey => $customField){
                $data['customFields'][$ckey]['id'] = $ckey;
                // Adding the values to the result
            	if(!empty($config[$ckey])){
            		$data['customFields'][$ckey]['value'] = $config[$ckey]['option_value'];
            	}
            }
        }
		$collection = collect([
			'config' => $config,
			'data' => $data,
		]);
		return response()->json($collection);
	}

	/**
	 * The manager of the database communication for adding and manipulating posts
	 */
	public function configManager(Request $request, $group)
	{
		// Validate if the user is logged in
		if(! $this->userIsLoggedIn($group)){
			return $this->abort('User not authorized.');
		}
		// User email validation
		if ($this->userHasWhitelistedEmail($group)) {
			return $this->abort('User email is not whitelisted.');
		}
		// Creating and cleaning up the request so we get all custom fields
		$configMeta = $request->all();
		$unsetValues = ['_token'];
		foreach($unsetValues as $value){
			unset($configMeta[$value]);
		}
		// Receiving validation rules from config
		$repeaterArray = [];
		foreach ($configMeta as $key => $value) {
			// Setting the path to get the validation rules
			if(strpos($key, '_repeater_') !== false) {
				$explodedValue = explode('_', $key);
				// We need to make a array of all repeaters so we can update it specific later
				$repeaterArray[] = $key;
				$rule = config("niku-cms.config.{$group}.view.customFields.{$explodedValue[0]}.customFields.{$explodedValue[3]}.validation");
			} else {
				$rule = config("niku-cms.config.{$group}.view.customFields.{$key}.validation");
			}
			if (! empty($rule) ) {
				$validationRules[$key] = $rule;
			}
		}
		// Validate the post
		if(!empty($validationRules)){
			$this->validatePost($request, $validationRules);
		}
		// Lets first delete the old repeater values
		if(strpos($key, '_repeater_') !== false) {
			Config::where('option_name', 'like', $explodedValue[0] . '_' . $explodedValue[1] . '_%')->delete();
		}
		// Lets update or create the fields in the post request
		foreach($configMeta as $index => $value){
			Config::updateOrCreate(
				['option_name' => $index],
				[
					'option_value' => $value,
					'group' => $group
				]
			);
		}
		return response()->json([
			'code' => 'success',
			'message' => 'Instellingen succesvol opgeslagen'
		]);
	}
	/**
	 * Validating the creation and change of a post
	 */
	protected function validatePost($request, $validationRules)
	{
		return $this->validate($request, $validationRules);
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
	public function userIsLoggedIn($group)
	{
		if(config("niku-cms.config.{$group}.authorization.userMustBeLoggedIn")){
			return Auth::check();
		} else {
			return true;
		}
	}
	protected function abort($message = 'Not authorized.')
	{
		return response()->json([
			'code' => 'error',
			'status' => $message,
		]);
	}
	protected function userHasWhitelistedEmail($group)
	{
		$emailAddresses = config("niku-cms.config.{$group}.authorization.allowedUserEmailAddresses");
		return (!empty($emailAddresses) && !in_array( Auth::user()->email, $emailAddresses));
	}
}
