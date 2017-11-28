<?php

namespace App\Cms\PostTypes;

use Niku\Cms\Http\NikuPosts;

class CategoryPosts extends NikuPosts
{
    // The label of the custom post type
    public $label = 'Post categories';

    // Users can only view their own posts when this is set to true
    public $userCanOnlySeeHisOwnPosts = false;

    // Disable post_name requirement, this will random generate a string
    public $disableDefaultPostName = false;
    public $disableSanitizingPostName = true;
    public $makePostNameRandom = true;

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

                'description' => [
                    'component' => 'niku-cms-editor-customfield',
                    'label' => 'Description',
                ],

                'posts' => [
                    'component' => 'niku-cms-category-posts-customfield',
                    'label' => 'Associated posts',
                    'config' => [
                        'sub_post_type' => 'posts',
                        'posts_edit_url_identifier' => 'Single',
                    ],
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
