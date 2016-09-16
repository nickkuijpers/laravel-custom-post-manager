<?php

return [

	/**
	 * Defining all custom fields
	 */
	'customFields' => [		
		'text' => 'Niku\Cms\Customfields\Text',
		'textarea' => 'Niku\Cms\Customfields\Textarea',
		'editor' => 'Niku\Cms\Customfields\Editor',		
		'file' => 'Niku\Cms\Customfields\File',		
		'image' => 'Niku\Cms\Customfields\Image',
		'gallery' => 'Niku\Cms\Customfields\Gallery',
		'datepicker' => 'Niku\Cms\Customfields\Datepicker',
		'timepicker' => 'Niku\Cms\Customfields\Timepicker',
		'colorpicker' => 'Niku\Cms\Customfields\Colorpicker',
		'checkbox' => 'Niku\Cms\Customfields\Checkbox',
		'radio' => 'Niku\Cms\Customfields\Radio',
		'map' => 'Niku\Cms\Customfields\Map',
	],

	/**
	 * Adding custom post types
	 */
    'post_types' => [
    	'page' => [
    		'label' => 'Pagina\'s',    		
    		'authenticate' => [
    			'auth',
    			'isAdmin',
    		],
    		'template' => [
    			'layouts/default' => [
    				'label' => 'Standaard pagina',
    				'customFields' => [
    					'headerImage' => [
    						'label' => 'Header afbeelding',
    						'type' => 'image',
    						'value' => '',
    					],
    				],
    			],
    			'layouts/sidebar-left' => [
    				'label' => 'Sidebar rechts pagina',
    				'customFields' => [
    					
    				],
    			],
    			'layouts/sidebar-right' => [
    				'label' => 'Sidebar rechts pagina',
    				'customFields' => [
    					
    				],
    			],
    			'layouts/contact' => [
    				'label' => 'Contact pagina',
    				'customFields' => [
    					
    				],
    			],
    		], 
    	],
    	'post' => [

    	],
    	'faq' => [

    	]
    ],

];
