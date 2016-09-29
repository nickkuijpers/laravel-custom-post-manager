# Laravel NIKU CMS

[![Latest Stable Version](https://poser.pugx.org/niku-solutions/cms/v/stable)](https://packagist.org/packages/niku-solutions/cms)
[![Total Downloads](https://poser.pugx.org/niku-solutions/cms/downloads)](https://packagist.org/packages/niku-solutions/cms)
[![Latest Unstable Version](https://poser.pugx.org/niku-solutions/cms/v/unstable)](https://packagist.org/packages/niku-solutions/cms)
[![License](https://poser.pugx.org/niku-solutions/cms/license)](https://packagist.org/packages/niku-solutions/cms)
[![Monthly Downloads](https://poser.pugx.org/niku-solutions/cms/d/monthly)](https://packagist.org/packages/niku-solutions/cms)
[![Daily Downloads](https://poser.pugx.org/niku-solutions/cms/d/daily)](https://packagist.org/packages/niku-solutions/cms)

A codeable CMS for Laravel with post types and custom fields. Extendable as you wish.

Vue.js and Vue Resource is required. But as Laravel 5.3 ships this with default you will be able to install this easily.
This package gives you the possibility to easily add a user interface to manage post types with custom fields based on the
selected page template and authentication. This package includes default custom fields
but you can extend is very easily as you read the 'Extending the custom fields' section.

## Impression

| ![Impression 1](https://niku-solutions.nl/laravel-niku-cms/image1.png)  | ![Impression 2](https://niku-solutions.nl/laravel-niku-cms/image2.png)  | ![Impression 3](https://niku-solutions.nl/laravel-niku-cms/image3.png)  | ![Impression 4](https://niku-solutions.nl/laravel-niku-cms/image3.png)  |
| ------------- | ------------- | ------------- |
| ![Impression 5](https://niku-solutions.nl/laravel-niku-cms/image4.png)  | ![Impression 6](https://niku-solutions.nl/laravel-niku-cms/image5.png)  | ![Impression 7](https://niku-solutions.nl/laravel-niku-cms/image6.png)  | ![Impression 8](https://niku-solutions.nl/laravel-niku-cms/image3.png)  |

*For now you are required to be authenticated to use the CMS api's. In future releases i will be adding rules for authentication like viewable in the config/niku-cms.php*

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
php artisan vendor:publish --tag=niku-cms
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

```

elixir(mix => {
	...
	mix.webpack([
	    'vendor/niku-cms/niku-cms.js',
	], 'public/js/vendor/niku-cms/niku-cms.js');
	mix.sass([
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

## Usage # TO DO

After testing the demo url and the CMS is working, you can implement it into your application. Make sure you add
the required assets in each page you use the CMS component. As this CMS is based on Vue, you must ofcourse include
your default assets.

You can change the `post_type` variable in the <compontent> to change the post type of the CMS.

```
<head>
	...
	<link media="all" type="text/css" rel="stylesheet" href="{{ asset('css/vendor/niku-cms/niku-cms.css') }}">
</head>
<body>
	<niku-cms-spinner></niku-cms-spinner>
	<niku-cms-notification v-bind:notification="nikuCms.notification"></niku-cms-notification>
	...
	<component :is="nikuCms.view" post_type="page"></component>
	...
	<script src="{{ asset('js/vendor/niku-cms/niku-cms.js') }}"></script>
</body>
```

Before you are able to use the post types, you need to whitelist and setup the required custom fields and templates in the config/niku-cms.php file.

`# TO DO write documation about structure in config/niku-cms.php file`

## Extending the custom fields # TO DO

* Adding custom fields with custom javascript and css
* changing the html of the pages

## Future features
* Taxonomies like categories
* ~~Add user authentication rules because now you are required to be authenticated to use the CMS.~~
* Translations, now you need to hardcode the language

## Changelog

For changes and updates, please see our [CHANGELOG](CHANGELOG.md).

## Help

Here are the solutions for some common issues.

### Laravel is not Defined

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
