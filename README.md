# Laravel Post Manager

[![Latest Stable Version](https://poser.pugx.org/niku-solutions/cms/v/stable)](https://packagist.org/packages/niku-solutions/cms)
[![Latest Unstable Version](https://poser.pugx.org/niku-solutions/cms/v/unstable)](https://packagist.org/packages/niku-solutions/cms)
[![License](https://poser.pugx.org/niku-solutions/cms/license)](https://packagist.org/packages/niku-solutions/cms)
[![Monthly Downloads](https://poser.pugx.org/niku-solutions/cms/d/monthly)](https://packagist.org/packages/niku-solutions/cms)

A API based codeable post manager for Laravel with custom fields. Extendable as you wish. Based on the API request, you will receive the post type configurations
in a way where you can build your front-end with. We will take care of the CRUD functionality with support of taxonomies, media management and post meta.

We use our package internally in our projects to remove the need of basic post management. We are now able to setup advanced dashboard functionality for all type of
post data like Pages, Posts, Products and whatever post type or category you require. You can add or remove custom fields in no time with no need to touch the
database as the package does that automatically for you and save the data and shows it to you when displaying the editting form.

> We are working on a decoupled front-end package in Vue.js and Axios which makes it possible to interact with the API in your Laravel project or Single Page Application.

#### Features
* Custom post types
* Configuration pages
* Taxonomies like categories
* Media manager with upload functionality and management
* Repeating custom field groups
* Custom fields
* Validation rules for custom fields
* Conditional custom fields based on template selection
* Easy default user authentication based on if a user is logged in
* Possibility to let users only view their own posts
* Menu management support, you will need our front-end package for that.

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

You need to run the following artisan command to publish the required config file to register your post types.

```
php artisan vendor:publish --tag=niku-config
```

If you run the following vendor publish, you will receive a example set of post types to use

```
php artisan vendor:publish --tag=niku-posttypes
```

Migrate the database tables by running:

```
php artisan migrate
```


### Usage

Before you are able to use the post types, you need to whitelist and setup the required custom fields and templates in the config/niku-cms.php file.

```php
return [
    'post_types' => [

        // Default
        'attachment' => App\Cms\PostTypes\Attachment::class,

        // CMS
        'page' => App\Cms\PostTypes\Pages::class,
        'posts' => App\Cms\PostTypes\Pages::class,
        'posts-category' => App\Cms\PostTypes\PostsCategory::class,

    ];
```

For each post type registered, you can set up default data and custom fields. You can add validations to the validation array key of the custom field you insert. All Laravel validation rules
will be supported as it will only pass it thru to the validator class.

```php
namespace App\Cms\PostTypes;

use Niku\Cms\Http\NikuPosts;

class Pages extends NikuPosts
{
    // The label of the custom post type
    public $label = 'Pages';

    // Does the user have to be logged in to view the posts?
    public $userMustBeLoggedIn = true;

    // Users can only view their own posts when this is set to true
    public $userCanOnlySeeHisOwnPosts = false;

    // Default required values for posts
    public $defaultValidationRules = [
        'post_title' => 'required',
        'status' => 'required',
        'post_name' => 'required',
    ];

    public $config = [

    ];

    // Setting up the template structure
    public $templates = [
        'default' => [
            'customFields' => [
                'post_content' => [
                    'component' => 'niku-cms-text-customfield',
                    'label' => 'Text',
                    'value' => '',
                    'validation' => 'required',
                ],
                'author' => [
                    'component' => 'niku-cms-text-customfield',
                    'label' => 'Author',
                    'validation' => 'required',
                ],
                // more custom fields
            ],
        ],
    ];

    /**
     * Determine if the user is authorized to make this request.
     * You can create some custom function here to manipulate
     * the functionalty on some certain custom actions.
     */
    public function authorized()
    {
        return true;
    }

}

```

Do you want to change the custom fields displayed based on the template? You can add multiple views which are selectable in the frontend for the end user and change the visible custom fields.

```php
public $templates = [
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
    'sidebar' => [
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
];
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

#### Switching templates

If you have enabled more than 1 post type template in the config/niku-cms.php, you will see a option appear in the backend to switch between templates. When you have
selected one template, you can switch views in the frontend like this.

```blade
@extends('static.layouts.' . $posts->template)
```

## Extending the custom fields and defining your own

You can create your own custom fields by using the registered component identifier to identify which Vue component you need to show.

```php
'text' => [
    'component' => 'niku-cms-text-customfield',
    'label' => 'Text',
    'value' => '',
    'validation' => 'required',
],
```

Registrate your component with they key you define in the post type config.

```javascript
Vue.component('niku-cms-text-customfield', require('./components/customFields/text.vue'));
```

And for example use the following code structure

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

## Security Vulnerabilities

If you find any security vulnerabilities, please send a direct e-mail to Nick Kuijpers at n.kuijpers@niku-solutions.nl.

## License

The MIT License (MIT). Please see [MIT license](http://opensource.org/licenses/MIT) for more information.
