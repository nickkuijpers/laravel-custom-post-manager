<?php

/**
 * Adding custom post types
 */

return [

    'demo' => 1,

    // Define the required 'whitelisted' post types
    'post_types' => [

        // The custom post type
        'page' => [

            // Authorization
            'authorization' => [
                'userMustBeLoggedIn' => 1,
                'userCanOnlySeeHisOwnPosts' => 0,
                'allowedUserEmailAddresses' => [],
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

                        	'post_content' => [
                                'component' => 'niku-cms-editor-customfield',
                                'label' => 'Text',
                                'value' => '',
                                'validation' => 'required',
                            ],

                            'text' => [
                                'component' => 'niku-cms-text-customfield',
                                'label' => 'Text',
                                'value' => '',
                                'validation' => 'required',
                            ],

                            'image' => [
                                'component' => 'niku-cms-image-customfield',
                                'label' => 'Image',
                                'value' => ''
                            ],

                            'textarea' => [
                                'component' => 'niku-cms-textarea-customfield',
                                'label' => 'Textarea',
                                'value' => '123'
                            ],

                            'select' => [
                                'component' => 'niku-cms-select-customfield',
                                'label' => 'Select',
                                'options' => [
                                    '' => '',
                                    'value' => 'Label'
                                ]
                            ],

                        ],
                    ],


                ],
            ],
        ],

        // The custom post type
        'post' => [
            'authorization' => [
                'userMustBeLoggedIn' => 1,
                'userCanOnlySeeHisOwnPosts' => 0,
            ],
            'view' => [
                'label' => 'Standaard bericht',
                'templates' => [
                    'default' => [
                        'label' => 'Berichten pagina',
                        'template' => 'default',
                        'customFields' => [
                        	'post_content' => [
                                'component' => 'niku-cms-editor-customfield',
                                'label' => 'Text',
                                'value' => '',
                                'validation' => 'required',
                            ],
                            'text' => [
                                'component' => 'niku-cms-text-customfield',
                                'label' => 'Sidebar titel',
                                'type' => 'text',
                                'value' => ''
                            ],
                            'image' => [
                                'component' => 'niku-cms-image-customfield',
                                'label' => 'Image',
                                'type' => 'upload',
                                'value' => ''
                            ],
                            'textarea' => [
                                'component' => 'niku-cms-textarea-customfield',
                                'label' => 'Textarea',
                                'value' => '123'
                            ],
                            'select' => [
                                'component' => 'niku-cms-select-customfield',
                                'label' => 'Select',
                                'options' => [
                                    '' => '',
                                    'test' => 'test'
                                ],
                                'value' => ''
                            ],
                        ],
                    ],
                ],
            ],
        ],

        // The custom post type
        'attachment' => [
            'authorization' => [
                'userMustBeLoggedIn' => 1,
                'userCanOnlySeeHisOwnPosts' => 0,
            ],
            'view' => [
                'label' => 'Media manager',
                'templates' => [
                    'default' => [
                        'label' => 'Media manager',
                        'template' => 'default',
                        'customFields' => [
                        ],
                    ],
                ],
            ],
        ],

    ],

];
