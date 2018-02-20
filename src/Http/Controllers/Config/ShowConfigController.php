<?php

namespace Niku\Cms\Http\Controllers\Config;

use Illuminate\Support\Facades\Auth;
use Niku\Cms\Http\Controllers\ConfigController;
use Niku\Cms\Http\NikuConfig;

class ShowConfigController extends ConfigController
{
	/**
	 * Display a single post
	 */
	public function init($group)
	{
		// Lets validate if the post type exists and if so, continue.
		$postTypeModel = $this->getPostType($group);
		if(!$postTypeModel){
    		$errorMessages = 'You are not authorized to do this.';
    		return $this->abort($errorMessages);
    	}

		// Receiving the current data of the group
		$config = NikuConfig::where('group', '=', $group)
			->get()
			->keyBy('option_name')
			->toArray();

		$templates = $postTypeModel->view;

		// Appending the key added in the config to the array
        // so we can use it very easliy in the component.
        foreach($templates as $key => $template){

	    	if(!empty($template['customFields'])){

	    		// For each custom fields
	            foreach($template['customFields'] as $ckey => $customField){
	                $templates[$key]['customFields'][$ckey]['id'] = $ckey;

	                // Adding the values to the result
	            	if(!empty($config[$ckey])){
	            		$templates[$key]['customFields'][$ckey]['value'] = $config[$ckey]['option_value'];
	            	}
	            }
	        }
	    }

		$collection = collect([
			'config' => $this->getConfig($postTypeModel),
			'templates' => $templates,
		]);

		return response()->json($collection);
	}

	public function getConfig($postTypeModel)
	{
		// Merge the configuration values
		$config = [];
		if($postTypeModel->config){
			$config = $postTypeModel->config;
		}

        $config = $config;

        // Adding public config
        if($postTypeModel->skipCreation){
			$config['skip_creation'] = $postTypeModel->skipCreation;
			if($postTypeModel->skipToRouteName){
				$config['skip_to_route_name'] = $postTypeModel->skipToRouteName;
			}
        } else {
			$config['skip_creation'] = false;
			$config['skip_to_route_name'] = '';
		}
		
		// Adding public config
        if($postTypeModel->disableEditOnlyCheck){
        	$config['disable_edit_only_check'] = $postTypeModel->disableEditOnlyCheck;
        } else {
        	$config['disable_edit_only_check'] = false;
		}

		if($postTypeModel->disableEdit){
        	$config['disable_edit'] = $postTypeModel->disableEdit;
        } else {
        	$config['disable_edit'] = false;
		}

		if($postTypeModel->disableDelete){
        	$config['disable_delete'] = $postTypeModel->disableDelete;
        } else {
        	$config['disable_delete'] = false;
		}

		if($postTypeModel->disableCreate){
        	$config['disable_create'] = $postTypeModel->disableCreate;
        } else {
        	$config['disable_create'] = false;
		}
		
		if($postTypeModel->getPostByPostName){
        	$config['get_post_by_postname'] = $postTypeModel->getPostByPostName;
        } else {
        	$config['get_post_by_postname'] = false;
		}

		$allKeys = collect($this->getValidationsKeys($postTypeModel));

		// Adding public config
        if($postTypeModel->enableAllSpecificFieldsUpdate){
        	$config['specific_fields']['enable_all'] = $postTypeModel->enableAllSpecificFieldsUpdate;
			$config['specific_fields']['exclude_fields'] = $postTypeModel->excludeSpecificFieldsFromUpdate;			
			$config['specific_fields']['enabled_fields'] = $allKeys->keys();
        } else {
        	$config['specific_fields']['enable_all'] = $postTypeModel->enableAllSpecificFieldsUpdate;
			$config['specific_fields']['exclude_fields'] = $postTypeModel->excludeSpecificFieldsFromUpdate;			
			$config['specific_fields']['enabled_fields'] = $allKeys->where('single_field_updateable.active', 'true')->keys();
		}

		return $config;
	}

}
