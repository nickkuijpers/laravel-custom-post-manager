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

	// Get the post by the post_name column instead of the id
    public $getPostByPostName = false;

    // Append a custom query to the CmsPosts table
    public $appendCustomWhereQueryToCmsPosts = [
        ['status', '=', NULL],
    ];

	// Disable post_name requirement, this will random generate a string
    public $disableDefaultPostName = false;
    public $disableSanitizingPostName = true;
    public $makePostNameRandom = true;

	// Skip creationg and create identifier to edit    
    public $skipCreation = true;

    // Enable single field saving, creation must be skipped.
    public $enableAllSpecificFieldsUpdate = true;    
    public $excludeSpecificFieldsFromUpdate = ['aanvullende_informatie'];
    public $disableEditOnlyCheck = false;

    public $errorMessages = [
        'post_type_does_not_exist' => 'The post type does not exist.',
        'post_type_identifier_does_not_exist' => 'The post type identifier does not exist.',
        'post_does_not_exist' => 'The post does not exist.',
        'sub_post_type_does_not_exist' => 'The sub post type does not exist',
        'no_taxonomy_posts_connected' => 'There are no posts connected to this taxonomy.',
    ];

    public $successMessage = [
        'post_created' => 'Post successful created.',
        'post_deleted' => 'Post successful deleted',
        'post_updated' => 'Post successful updated',
    ];

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
				'mutated_value' => [
                	'component' => 'niku-cms-text-customfield',
                    'label' => 'Text',
                    'value' => '',
                    'validation' => 'required',
                    'saveable' => true,
                	'mutator' => 'App\Cms\Mutators\PostNameMutator',
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
