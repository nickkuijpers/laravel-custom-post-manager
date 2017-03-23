<?php

return [

	// Define the required 'whitelisted' post types
	'post_types' => [
		App\PostTypes\Cms\Employee::class,
		App\PostTypes\Cms\Helpdesk::class,
	],

];
