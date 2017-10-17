<?php

namespace App\Cms\PostTypes;

use Niku\Cms\Http\NikuPosts;

class Attachments extends NikuPosts
{
	// The label of the custom post type
	public $label = 'Attachments';

	// Custom post type identifer
	public $identifier = 'attachment';

	// Users can only view their own posts when this is set to true
	public $userCanOnlySeeHisOwnPosts = false;

	// Default required values for posts
	public $defaultValidationRules = [
		'post_title' => 'required',
        'status' => 'required',
        'post_name' => 'required',
	];

	// Setting up the template structure
	public $view = [
		'default' => [
			'customFields' => [
				'text' => [
                    'component' => 'niku-cms-text-customfield',
                    'label' => 'Text',
                    'value' => '',
                    'validation' => 'required',
                ],
			],
		],
	];
}
