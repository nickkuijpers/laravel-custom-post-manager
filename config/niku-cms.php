<?php

return [

	// Define the required 'whitelisted' post types
	'post_types' => [
		App\PostTypes\Cms\Attachments::class,
		App\PostTypes\Cms\Posts::class,
	],

];
