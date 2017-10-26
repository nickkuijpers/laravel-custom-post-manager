<?php

namespace App\Cms\PostTypes;

use Niku\Cms\Http\NikuPosts;

class Pages extends NikuPosts
{
	// The label of the custom post type
	public $label = 'Pages';

	// Custom post type identifer
	public $identifier = 'page';

	// Users can only view their own posts when this is set to true
	public $userCanOnlySeeHisOwnPosts = false;

	// Default required values for posts
	public $defaultValidationRules = [
		'post_title' => 'required',
		'status' => 'required',
		'post_name' => 'required',
	];

	public $config = [

	];

	// Setting up the template structure
	public $templates = [
		'default' => [

			'label' => 'Default',

			'customFields' => [
				'text' => [
					'component' => 'niku-cms-text-customfield',
					'label' => 'Text',
					'value' => '',
					'validation' => 'required',
				],
				'PostMultiselect' => [
					'component' => 'niku-cms-posttype-multiselect',
					'label' => 'Post multiselect',
					'post_type' => ['page'],
					'validation' => 'required',
				],
				'periods' => [
					'component' => 'niku-cms-repeater-customfield',
					'label' => 'Perioden',
					'validation' => 'required',
					'customFields' => [

						'label' => [
							'component' => 'niku-cms-text-customfield',
							'label' => 'Label',
							'value' => '',
							'validation' => '',
						],

						'boolean' => [
							'component' => 'niku-cms-boolean-customfield',
							'label' => 'Boolean button',
							'value' => '',
							'validation' => '',
						],

					]
				],
			],
		],
	];

}
