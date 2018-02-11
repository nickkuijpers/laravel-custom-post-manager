<?php

namespace App\Cms\PostTypes;

use Niku\Cms\Http\NikuPosts;

class Posts extends NikuPosts
{
	// The label of the custom post type
	public $label = 'Posts';

	// Does the user have to be logged in to view the posts?
	public $userMustBeLoggedIn = false;

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

	/**
     * Determine if the user is authorized to make this request.
     * You can create some custom function here to manipulate
     * the functionalty on some certain custom actions.
     */
    public function authorized()
    {
        return true;
    }

}
