<?php

namespace App\Cms\PostTypes;

use Niku\Cms\Http\NikuPosts;

class Attachments extends NikuPosts
{
	protected $config = [
		'authorization' => [
			'userMustBeLoggedIn' => 0,
			'userCanOnlySeeHisOwnPosts' => 0,
		],
		'view' => [
			'label' => 'Media manager',
			'config' => [
				'slugChangeable' => true,
			],
			'templates' => [
				'default' => [
					'label' => 'Media manager',
					'template' => 'default',
					'customFields' => [
					],
				],
			],
		],
	];
}
