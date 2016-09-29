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

			// Authentication middlewares
			'authenticate' => [
				'auth',
				'isAdmin',
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

							'header_afbeelding' => [
								'component' => 'niku-cms-text-customfield',
								'label' => 'Header afbeelding',
								'type' => 'image',
								'value' => ''
							],

							'header_afbeel123ding' => [
								'component' => 'niku-cms-text-customfield',
								'label' => 'Header afbeelding',
								'type' => 'image',
								'value' => ''
							],

							'header_afbeeld33ing' => [
								'component' => 'niku-cms-text-customfield',
								'label' => 'Header afbeelding',
								'type' => 'image',
								'value' => ''
							],

							'header_afbeeldi44ng' => [
								'component' => 'niku-cms-text-customfield',
								'label' => 'Header afbeelding',
								'type' => 'image',
								'value' => ''
							],

							'header_afbeeld55ing' => [
								'component' => 'niku-cms-text-customfield',
								'label' => 'Header afbeelding',
								'type' => 'image',
								'value' => ''
							],

						],
					],

					'sidebar-left' => [
						'label' => 'Sidebar rechts pagina',
						'template' => 'sidebar-left',
						'customFields' => [

							'header_afbeeld55ing' => [
								'component' => 'niku-cms-text-customfield',
								'label' => 'Header afbeelding',
								'type' => 'image',
								'value' => ''
							],

							'header_afbeeld55ing' => [
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
