<?php

namespace App\Cms\ConfigTypes;

use Niku\Cms\Http\NikuConfig;

class DefaultSettings extends NikuConfig
{
	// The label of the custom post type
	public $label = 'Default settings';

	// Does the user have to be logged in to view the posts?
	public $userMustBeLoggedIn = true;

	// Default required values for posts
	public $defaultValidationRules = [
		//
	];

	public $config = [
		//
	];

	// Setting up the template structure
	public $templates = [
		'default' => [
			'customFields' => [
				'text' => [
					'component' => 'niku-cms-text-customfield',
					'label' => 'Text',
					'value' => '',
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
							'validation' => 'required',
						],

						'boolean' => [
							'component' => 'niku-cms-boolean-customfield',
							'label' => 'Boolean button',
							'value' => '',
							'validation' => 'required',
						],

					]
				],
			],
		],
	];

}