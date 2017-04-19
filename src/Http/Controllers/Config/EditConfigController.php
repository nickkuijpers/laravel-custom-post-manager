<?php

namespace Niku\Cms\Http\Controllers\Config;

use Illuminate\Http\Request;
use Niku\Cms\Http\Controllers\ConfigController;

class EditConfigController extends ConfigController
{
	/**
	 * The manager of the database communication for adding and manipulating posts
	 */
	public function init(Request $request, $group)
	{
		// Lets validate if the post type exists and if so, continue.
		$postTypeModel = $this->getPostType($group);
		if(!$postTypeModel){
			return $this->abort('You are not authorized to do this.');
		}

		// Recieving the values
		$configMeta = $request->all();

		// Unsetting values which we do not need
		$unsetValues = ['_token'];
		foreach($unsetValues as $value){
			unset($configMeta[$value]);
		}

		dd($configMeta);

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

		// Lets first delete the old repeater values, because if we do not do this,
		// it will keep adding it self because we are not
		if(strpos($key, '_repeater_') !== false) {
			NikuConfig::where('option_name', 'like', $explodedValue[0] . '_' . $explodedValue[1] . '_%')->delete();
		}

		// Lets update or create the fields in the post request
		foreach($configMeta as $index => $value){

			NikuConfig::updateOrCreate(
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

}
