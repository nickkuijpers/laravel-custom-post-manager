# Laravel Post Manager

[![Latest Stable Version](https://poser.pugx.org/niku-solutions/cms/v/stable)](https://packagist.org/packages/niku-solutions/cms)
[![Total Downloads](https://poser.pugx.org/niku-solutions/cms/downloads)](https://packagist.org/packages/niku-solutions/cms)
[![Latest Unstable Version](https://poser.pugx.org/niku-solutions/cms/v/unstable)](https://packagist.org/packages/niku-solutions/cms)
[![License](https://poser.pugx.org/niku-solutions/cms/license)](https://packagist.org/packages/niku-solutions/cms)
[![Monthly Downloads](https://poser.pugx.org/niku-solutions/cms/d/monthly)](https://packagist.org/packages/niku-solutions/cms)
[![Daily Downloads](https://poser.pugx.org/niku-solutions/cms/d/daily)](https://packagist.org/packages/niku-solutions/cms)

A codeable post manager for Laravel with custom fields. Extendable as you wish. Define your required fields in the config
and see the magic. It will automaticly display the custom fields added in the niku-cms.php config file and will
take care of the database management.

Vue.js and Vue Resource is required. But as Laravel 5.3 ships this with default you will be able to install this easily.
This package gives you the possibility to easily add a user interface to manage post types with custom fields based on the
selected page template and authentication. This package includes default custom fields but you can extend it very
easily as you read the 'Extending the custom fields' section.

You will be able to create a user interface for content management in minutes.

## Impression

| ![Impression 1](https://niku-solutions.nl/laravel-niku-cms/impression1.png)  | ![Impression 2](https://niku-solutions.nl/laravel-niku-cms/impression2.png)  | ![Impression 3](https://niku-solutions.nl/laravel-niku-cms/impression3.png)  | ![Impression 4](https://niku-solutions.nl/laravel-niku-cms/impression4.png)  |
| ------------- | ------------- | ------------- | ------------- |
| ![Impression 5](https://niku-solutions.nl/laravel-niku-cms/impression5.png)  | ![Impression 6](https://niku-solutions.nl/laravel-niku-cms/impression6.png)  | ![Impression 7](https://niku-solutions.nl/laravel-niku-cms/impression7.png)  | ![Impression 8](https://niku-solutions.nl/laravel-niku-cms/impression8.png)  |

## Installation

Install the package via composer:

```
composer require niku-solutions/cms
```

Register the following class into the 'providers' array in your config app.php

```
Niku\Cms\CmsServiceProvider::class,
```

Enable the API where the frontend is communicating with by adding the following into your web.php.

```
Niku\Cms\Cms::routes();
```

You need to run the following artisan command to publish the required assets and views.

```
php artisan vendor:publish --tag=niku-assets
php artisan vendor:publish --tag=niku-config
```

Migrate the database tables by running:

```
php artisan migrate
```

### Asset installation

#### gulpfile.js

As i advice you, for default websites, to keep the frontend and the backand decoupled, you have to define the following into your gulpfile.js.
You don't have to do anything in there, but it gives you the possibility to add new custom fields like editors and datepickers.

*In the niku-cms.js you will see a Bootstrap 3 function, you can disable this if you are not using Bootstrap but this will make sure the
sidebar of the single post view is fixed for usability.*

Make sure you add it to the same existing elixir function as where your vue instance is created.

```
elixir(mix => {
	...
    mix.scripts([ // Vendor scripts like tinymce and datepickers
        'vendor/niku-cms/vendor/tinymce.min.js',
        // 'vendor/niku-cms/vendor/jquery-3.1.1.min.js',
        'vendor/niku-cms/vendor/jquery-ui.js',
    ], 'public/js/vendor/niku-cms/vendor.js')
    .webpack([ // Custom scripting
        'vendor/niku-cms/niku-cms.js',
    ], 'public/js/vendor/niku-cms/niku-cms.js')
    .styles([ // Vendor styling like tinymce and datepickers
        'vendor/niku-cms/vendor/jquery-ui.css',
    ], 'public/css/vendor/niku-cms/vendor.css')
    .sass([ // Custom styling
        'vendor/niku-cms/mediamanager.scss',
        'vendor/niku-cms/dropzone.scss',
        'vendor/niku-cms/niku-cms.scss',
    ], 'public/css/vendor/niku-cms/niku-cms.css');
    ...
});
```

#### app.js

Include the following require function above the starting of the Vue instance in your app.js.

```
require('./vendor/niku-cms/init-niku-cms.js');
```

Now, in the Vue instance already added by Laravel, you need to add the following data object. If its already existing,
you only need to add the nikuCms section. If its not existing, you also need to add the data object.

```
const app = new Vue({
	el: 'body',
	data: {
	    'nikuCms': {
	        view: 'niku-cms-list-posts',
	        data: {},
	        postType: 'page',
	        mediaManager: {'display': 0},
	        notification: {'display': 0, 'type': '', 'message': ''}
	    },
    }
},
```

#### Final step

Run gulp in the terminal and the installation is done!

```
gulp
```

### Demo and testing

In the config/niku-cms.php you will see a demo variable. If you enable this, you can open up the cms by requesting the following url. The variable will be dynamically added
to the Vue component and will be used as the post type for saving the post. To see the power of the post types, try changing the {post_type} variable into something like
'post' and you will be adding and listing posts to the 'post' post type in the database.

*In the demo view, the component name is dynamically created. I advice you to hardcode this value when you are using this in a
certain page like http://yourdomain.com/cms/page where the component name is page or the type of post type you require.*

The `page` post type is whitelisted in the config/niku-cms.php so you will be able to use this demo setup.

```
http://domain.com/niku-cms/demo/{post_type}
```

## Usage

After testing the demo url and the CMS is working, you can implement it into your application. Make sure you add
the required assets in each page you use the CMS component. As this CMS is based on Vue, you must ofcourse include
your default assets.

You can change the `post_type` variable in the <compontent> to change the post type of the CMS.

```
<head>
	...
	<link media="all" type="text/css" rel="stylesheet" href="{{ asset('css/vendor/niku-cms/vendor.css') }}">
    <link media="all" type="text/css" rel="stylesheet" href="{{ asset('css/vendor/niku-cms/niku-cms.css') }}">
</head>
<body>
	<niku-cms-spinner></niku-cms-spinner>
	<niku-cms-notification v-bind:notification="nikuCms.notification"></niku-cms-notification>
	...
    <component :is="nikuCms.view" post_type="{{ $post_type }}"></component>
	...
	<script src="{{ asset('js/vendor/niku-cms/vendor.js') }}"></script>
    <script src="{{ asset('js/vendor/niku-cms/niku-cms.js') }}"></script>
</body>
```

Before you are able to use the post types, you need to whitelist and setup the required custom fields and templates in the config/niku-cms.php file.

For each custom post type defined, you can set authorization rules in the config to define the permissions of a post type.

```
'authorization' => [
    'userMustBeLoggedIn' => 1,
    'userCanOnlySeeHisOwnPosts' => 0,
    'allowedUserEmailAddresses' => [],
],
```

You can view the config/niku-cms.php to view all options, we've set one demo post type which you can read and reuse for multiple post types.

It is possible to set validation rules for each post type as you add the following array key to the custom field like this:

```
'customFields' => [
    'telephone' => [
        'component' => 'niku-cms-text-customfield',
        'label' => 'Telephone number',
        'value' => '',
        'validation' => 'required|number',
    ],
]
```

Do you want to change the custom fields displayed based on the template? You can add multiple views which are selectable in the frontend for the end user.

```
'templates' => [
    'default' => [
        'label' => 'Default page',
        'template' => 'default',
        'customFields' => [
            'text' => [
                'component' => 'niku-cms-text-customfield',
                'label' => 'Text',
                'value' => '',
                'validation' => 'required',
            ]
		]
	],
	'sidebar-layout' => [
        'label' => 'Sidebar layout',
        'template' => 'sidebar-layout',
        'customFields' => [
            'text' => [
                'component' => 'niku-cms-text-customfield',
                'label' => 'Text',
                'value' => '',
                'validation' => 'required',
            ]
		]
	],
]
```

## Extending the custom fields and defining your own

You can define your own custom fields by registering them in the resources/assets/js/vendor/niku-cms/components/customFields directory. After creating a Vue component, you
need to register it in the init-niku-cms.js or above your existing Vue instance. After registering the component, you can define the component name in the custom field like this.

niku-config.php

```
'text' => [
    'component' => 'niku-cms-text-customfield',
    'label' => 'Text',
    'value' => '',
    'validation' => 'required',
],
```

Your init-niku-js or Vue instance

```
Vue.component('niku-cms-text-customfield', require('./components/customFields/text.vue'));
```

Your component
```
<template>
    <div class="form-group">
        <label for="post_name" class="col-sm-3 control-label">{{ data.label }}:</label>
        <div class="col-sm-9">
            <input type="text" name="{{ data.id }}" v-model="input" class="form-control" value="{{ data.value }}">
        </div>
    </div>
</template>
<script>
export default {
    data () {
        return {
            'input': '',
        }
    },
    props: {
        'data': ''
    },
    ready () {
    }
}
</script>
```

In our main single page Vue component, we will receive all the custom fields enabled in the niku-cms.php and foreach include the component. This means you have got full access
to the variables defined in your custom field array in the niku-cms.php config. You are required to return the name and value of the object to make sure we can automaticly display
old user input when the post is editted like this.

The `name="{{ data.id }}` will be used as custom field name, the `v-model="input"` as a method to manipulate the input of the value and the value `value="{{ data.value }}"` to insert the data
received out of the database when editting it.

You can register your own components like this.

```
mix.scripts([ // Vendor scripts like tinymce and datepickers
        'vendor/niku-cms/vendor/tinymce.min.js',
        'vendor/niku-cms/vendor/jquery-3.1.1.min.js',
        ...
		// Your custom libraries
        ...
        'vendor/niku-cms/vendor/jquery-ui.js',
    ], 'public/js/vendor/niku-cms/vendor.js')
```

If you want you can change the HTML and styling of the post manager but i advice you not to so you can update the package easily and enjoy the future releases.

## Custom fields

#### Existing
* Text
* Textarea
* WYSIWYG editor
* Select
* Image upload

#### Future milestones
* Gallery
* Colorpicker
* Icon
* Checkbox
* Radio
* Repeater
* Google Maps
* Datepicker
* Range slider
* HTML
* Hidden
* Password
* Switch
* Multiple select
* File upload

## Features

#### Existing
* Custom post types
* Media manager
* Custom fields
* Custom validation rules for custom fields
* Include the component in your own template
* Conditionial custom fields based on template selection
* Add user authentication rules because now you are required to be authenticated to use the CMS.
* Possibility to add validation rules into the config for custom fields
* Media management with interface so custom fields can keep that as default

#### Future milestones
* Taxonomies like categories
* Translations, now you need to hardcode the language
* Dynamically manipulate the table headers of the list overview by the config file
* Adding post meta fields into the overview list page
* Pagination
* Creation of a global option page where website wide data is stored
* Vue 2.0

## Help

Here are the solutions for some common issues.

##### Console error with a message about fragment component

If you receive a message like this, make sure you check you gulpfile.js and validate if you have included the components in the same
elixir function as where you have included your main vue instance.

##### Laravel is not Defined

This issue means you have not set the csrfToken which Laravel and Vue Resource requires to prevent man in the middle attacks.
To solve this you can add the following code in the header.

```
<script>
window.Laravel = <?php echo json_encode([
    'csrfToken' => csrf_token(),
]); ?>
</script>
```

## Security Vulnerabilities

If you find any security vulnerabilities, please send a direct e-mail to Nick Kuijpers at n.kuijpers@niku-solutions.nl.

## License

The MIT License (MIT). Please see [MIT license](http://opensource.org/licenses/MIT) for more information.
