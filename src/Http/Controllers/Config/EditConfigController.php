<?php

namespace Niku\Cms\Http\Controllers\Config;

use Illuminate\Http\Request;
use Niku\Cms\Http\Controllers\ConfigController;
use Niku\Cms\Http\NikuConfig;

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

		// Receiving validation rules from config
		$validationRules = [];
		foreach ($configMeta as $key => $value) {

			// Setting the path to get the validation rules
			if(strpos($key, '_repeater_') !== false) {
				$explodedValue = explode('_', $key);

				// Removing the old repeater values so we can resave them all, if we do
				// not do this, it will keep having the old values in the database.
				NikuConfig::where([
					['option_name', 'like', $explodedValue[0] . '_' . $explodedValue[1] . '_%'],
					['group', '=', $group]
				])->delete();

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

		// Validate the request
		if(!empty($validationRules)){
			$this->validatePost($request, $validationRules);
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
