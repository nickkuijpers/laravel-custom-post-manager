<?php
/**
 * Adding custom post types
 */

return [

    'post_types' => [
        'page' => App\Cms\PostTypes\Pages::class,
        'attachment' => App\Cms\PostTypes\Attachment::class,
        //
    ],

    'config_types' => [
        'defaultsettings' => App\Cms\ConfigTypes\DefaultSettings::class,
        //
    ]

];
