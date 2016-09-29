<?php

/**
 * Adding custom post types
 */

return [

    'demo' => 0,

    // Define the required 'whitelisted' post types
    'post_types' => [

        // The custom post type
        'page' => [

            // Authorization
            'authorization' => [
                'userMustBeLoggedIn' => 0,
                'userCanOnlySeeHisOwnPosts' => 0,
            ],

            'view' => [

                // The page title
                'label' => 'Pagina\'s',

                // If there is more than one template defined, a select
                // box will appear so you can switch between them.
                'templates' => [

                    'default' => [
                        'label' => 'Standaard pagina',
                        'template' => 'default',

                        // Defining custom fields for the current page template
                        'customFields' => [

                            'sidebar_title' => [
                                'component' => 'niku-cms-text-customfield',
                                'label' => 'Sidebar titel',
                                'type' => 'image',
                                'value' => ''
                            ],

                            'sidebar_content' => [
                                'component' => 'niku-cms-text-customfield',
                                'label' => 'Sidebar tekst',
                                'type' => 'image',
                                'value' => ''
                            ],

                            'bottom_left_text' => [
                                'component' => 'niku-cms-text-customfield',
                                'label' => 'Links onder tekst',
                                'type' => 'image',
                                'value' => ''
                            ],

                            'bottom_right_text' => [
                                'component' => 'niku-cms-text-customfield',
                                'label' => 'Rechts onder tekst',
                                'type' => 'image',
                                'value' => ''
                            ]

                        ],
                    ],

                    'sidebar-left' => [
                        'label' => 'Sidebar rechts pagina',
                        'template' => 'sidebar-left',
                        'customFields' => [

                            'heade12312355ing' => [
                                'component' => 'niku-cms-text-customfield',
                                'label' => 'Header afbeelding',
                                'type' => 'image',
                                'value' => ''
                            ],

                            'header123ld55ing' => [
                                'component' => 'niku-cms-text-customfield',
                                'label' => 'Header afbeelding',
                                'type' => 'image',
                                'value' => ''
                            ],


                        ],
                    ],

                    'contact' => [
                        'label' => 'Contact pagina',
                        'template' => 'contact',
                        'customFields' => [

                        ],
                    ],
                ],
            ],
        ],
    ],

];
