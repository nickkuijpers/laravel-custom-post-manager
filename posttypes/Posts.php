<?php

namespace App\Cms\PostTypes;

use Niku\Cms\Http\NikuPosts;

class Posts extends NikuPosts
{
	// The label of the custom post type
	public $label = 'Posts';

	// Custom post type identifer
	public $identifier = 'post';

	// Users can only view their own posts when this is set to true
	public $userCanOnlySeeHisOwnPosts = false;

	// Disable post_name requirement, this will random generate a string
    public $disablePostName = true;

	// Register events based on the actions
    public $events = [
        'on_create' => [
            //
        ],
        'on_browse' => [
            //
        ],
        'on_read' => [
            //
        ],
        'on_edit' => [
            //
        ],
        'on_delete' => [
            //
        ],
    ];

	// Setting up the template structure
	public $view = [
		'default' => [

			'label' => 'Default',

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
