# Laravel Post Manager

[![Latest Stable Version](https://poser.pugx.org/niku-solutions/cms/v/stable)](https://packagist.org/packages/niku-solutions/cms)
[![Total Downloads](https://poser.pugx.org/niku-solutions/cms/downloads)](https://packagist.org/packages/niku-solutions/cms)
[![Latest Unstable Version](https://poser.pugx.org/niku-solutions/cms/v/unstable)](https://packagist.org/packages/niku-solutions/cms)
[![License](https://poser.pugx.org/niku-solutions/cms/license)](https://packagist.org/packages/niku-solutions/cms)
[![Monthly Downloads](https://poser.pugx.org/niku-solutions/cms/d/monthly)](https://packagist.org/packages/niku-solutions/cms)
[![Daily Downloads](https://poser.pugx.org/niku-solutions/cms/d/daily)](https://packagist.org/packages/niku-solutions/cms)

A codeable post manager for Laravel with custom fields. Extendable as you wish. Define your required fields in the config
and see the magic. It will automatically display the custom fields added in the niku-cms.php config file and will
take care of the database management.

Vue.js and Vue Resource is required. But as Laravel 5.3 ships this by default, you will be able to install this easily.
This package gives you the possibility to easily add a user interface to manage post types with custom fields based on the
selected page template and authentication. This package includes default custom fields but you can extend it very
easily as you read the 'Extending the custom fields' section.

You will be able to create a user interface for content management in minutes.

> :warning: This is still a work in progress, and breaking changes may be introduced.

## Impression

| ![Impression 1] | ![Impression 2] | ![Impression 3] | ![Impression 4] |
|-----------------|-----------------|-----------------|-----------------|
| ![Impression 5] | ![Impression 6] | ![Impression 7] | ![Impression 8] |

[Impression 1]:  https://niku-solutions.nl/laravel-niku-cms/impression1.png
[Impression 2]:  https://niku-solutions.nl/laravel-niku-cms/impression2.png
[Impression 3]:  https://niku-solutions.nl/laravel-niku-cms/impression3.png
[Impression 4]:  https://niku-solutions.nl/laravel-niku-cms/impression4.png
[Impression 5]:  https://niku-solutions.nl/laravel-niku-cms/impression5.png
[Impression 6]:  https://niku-solutions.nl/laravel-niku-cms/impression6.png
[Impression 7]:  https://niku-solutions.nl/laravel-niku-cms/impression7.png
[Impression 8]:  https://niku-solutions.nl/laravel-niku-cms/impression8.png

## Installation

Install the package via composer:

```
composer require niku-solutions/cms
```

Register the following class into the 'providers' array in your config/app.php

```php
Niku\Cms\CmsServiceProvider::class,
```

Enable the API where the frontend is communicating with by adding the following into your routes/web.php.

```php
Niku\Cms\Cms::routes();
```

You need to run the following artisan command to publish the required assets and views.

```
php artisan vendor:publish --tag=niku-assets
php artisan vendor:publish --tag=niku-config
```

If you have previously installed this package and you want to update the assets, use the following command. This will override your previous files.

```
php artisan vendor:publish --tag=niku-assets --force
```

Migrate the database tables by running:

```
php artisan migrate
```

### Asset installation

#### gulpfile.js

As I advice you, for default websites, to keep the frontend and the backend decoupled, you have to define the following into your gulpfile.js.
You don't have to do anything in there, but it gives you the possibility to add new custom fields like editors and datepickers.

*In the niku-cms.js you will see a Bootstrap 3 function, you can disable this if you are not using Bootstrap but this will make sure the
sidebar of the single post view is fixed for usability.*

Make sure you add it to the same existing elixir function as where your Vue instance is created.

```javascript
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

```javascript
require('./vendor/niku-cms/init-niku-cms.js');
```

Now, in the Vue instance already added by Laravel, you need to add the following data object. If it already exists, you
only need to add the nikuCms section. If it doesn't exist yet, you also need to add the data object.

```javascript
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
});
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

If you are not authorized and you want to test the demo, make sure you disable the userMustBeLoggedIn in the config/niku-cms.php for post types you wish to test and the the attachment post type.

```
http://domain.com/niku-cms/demo/{post_type}
```

## Usage

### Backend usage

After testing the demo URL and the CMS is working, you can implement it into your application. Make sure you add
the required assets in each page you use the CMS component. As this CMS is based on Vue, you must of course include
your default assets.

You can change the `post_type` variable in the `<component>` to change the post type of the CMS.

```blade
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
    <niku-cms-media-manager></niku-cms-media-manager>
    ...
    <script src="{{ asset('js/vendor/niku-cms/vendor.js') }}"></script>
    <script src="{{ asset('js/vendor/niku-cms/niku-cms.js') }}"></script>
</body>
```

Before you are able to use the post types, you need to whitelist and setup the required custom fields and templates in the config/niku-cms.php file.

For each custom post type defined, you can set authorization rules in the config to define the permissions of a post type.

```php
'authorization' => [
    'userMustBeLoggedIn' => 1,
    'userCanOnlySeeHisOwnPosts' => 0,
    'allowedUserEmailAddresses' => [],
],
```

You can view the config/niku-cms.php to view all options, we've set one demo post type which you can read and reuse for multiple post types.

It is possible to set validation rules for each post type as you add the following array key to the custom field like this:

```php
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

```php
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

### Frontend usage

If you want to display the posts by post type, you can use the following methods.

#### Single pages

Enable the following type in your routes/web.php.

```php
Route::get({post_name}, 'PageController@singlePage');
```

Next in your PageController, you do the following:

```php
public function single($post_name)
{
    $page = Posts::where([
        ['status', '=', '1'],
        ['post_type', '=', 'page'],
        ['post_name', '=', $post_name]
    ])->with('postmeta')->firstOrFail();
    return view('static.singlepage', compact('page'));
}
```

You can display it in blade like this.

```blade
@extends('static.layouts.default')

@section('metatitle', $page->post_title)
@section('metacontent', $page->getMeta('excerpt'))

@section('title', $page->post_title)
@section('content')
    <div class="main-post-content">
        {{ $page->post_content }}
    </div>
@endsection
```

#### Blog

If you want a blog like method, you can do the following.

Enable the following type in your routes/web.php.

```php
Route::get('blog', 'BlogController@blog');
Route::get('blog/{slug}', 'BlogController@singleBlog');
```

Next you enable the required methods in the controller.

```php
public function blog()
{
    $posts = Posts::where([
        ['status', '=', '1'],
        ['post_type', '=', 'post']
    ])->with('postmeta')->get();
    return view('static.blog', compact('posts'));
}
```

And then in your view, you do the following. This syntax will be recreated in the future to make it more fluent but for now it works.

```blade
@foreach($posts as $post)
    <div class="row">
        @if(!empty($post->getMeta('image')))
            <?php
            $image = json_decode($post->getMeta('image'));
            $image = $image->url;
            ?>
            <div class="col-md-3">
                <img src="{{ $image }}" class="img-responsive">
            </div>
        @endif
        <div class="col-md-8">
            <h2>{{ $post->post_title }}</h2>
            <p>{!! $post->getMeta('excerpt') !!}</p>
            <br/>
            <a class="btn btn-default" href="/blog/{{ $post->post_name }}">Read more</a>
        </div>
    </div>
@endforeach
```

## Extending the custom fields and defining your own

You can define your own custom fields by registering them in the resources/assets/js/vendor/niku-cms/components/customFields directory. After creating a Vue component, you
need to register it in the init-niku-cms.js or above your existing Vue instance. After registering the component, you can define the component name in the custom field like this.

config/niku-cms.php

```php
'text' => [
    'component' => 'niku-cms-text-customfield',
    'label' => 'Text',
    'value' => '',
    'validation' => 'required',
],
```

Your init-niku-js or Vue instance

```javascript
Vue.component('niku-cms-text-customfield', require('./components/customFields/text.vue'));
```

Your component
```vue
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

In our main single-page Vue component, we will receive all the custom fields enabled in the niku-cms.php and foreach include the component. This means you have got full access
to the variables defined in your custom field array in the niku-cms.php config. You are required to return the name and value of the object to make sure we can automatically display
old user input when the post is edited like this.

The `name="{{ data.id }}"` will be used as custom field name, the `v-model="input"` as a method to manipulate the input of the value and the value `value="{{ data.value }}"` to insert the data
received out of the database when editing it.

You can register your own components like this.

```javascript
mix.scripts([ // Vendor scripts like tinymce and datepickers
        'vendor/niku-cms/vendor/tinymce.min.js',
        'vendor/niku-cms/vendor/jquery-3.1.1.min.js',
        ...
        // Your custom libraries
        ...
        'vendor/niku-cms/vendor/jquery-ui.js',
    ], 'public/js/vendor/niku-cms/vendor.js')
```

If you want you can change the HTML and styling of the post manager but I advice you not to so you can update the package easily and enjoy the future releases.

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
* Conditional custom fields based on template selection
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
* Menu manager
* Enable media manager video and file uploads
* Beautify media manager
* Advanced search for post types
* Backup your CMS content
* Custom posts type revision
* Media gallery inside WYSIWYG editor
* Media resize on upload and optimize it for web

## Help

Here are the solutions for some common issues.

#### Console error with a message about fragment component

If you receive a message like this, make sure you check you gulpfile.js and validate if you have included the components in the same
elixir function as where you have included your main Vue instance.

#### Laravel is not Defined

This issue means you have not set the csrfToken which Laravel and Vue Resource requires to prevent man in the middle attacks.
To solve this you can add the following code in the header.

```blade
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
