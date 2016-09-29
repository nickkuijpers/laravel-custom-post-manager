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
selected page template and authentication. You can extend this framework by adding new custom fields as described below. Ships with default custom fields.

## Installation # TO DO

Install the package via composer:

```
composer require niku-solutions/cms
```

Register the following class into the 'providers' array in your config app.php

```
Niku\Cms\CmsServiceProvider::class,
```

Enable the API where the frontend is communicating with by adding the following into your routes.php.

```
Niku\Cms\Cms::routes();
```

You need to run the following artisan command to publish the required assets and views.

```
php artisan vendor:publish --tag=niku-cms
```

### Asset installation

As i advice you, for default websites, to keep the frontend and the backand decoupled, you have to define the following into your gulpfile.js.
You don't have to do anything in there, but it gives you the possibility to add new custom fields like editors and datepickers.

In the niku-cms.js you will see a Bootstrap 3 function, you can disable this if you are not using Bootstrap but this will make sure the
sidebar of the single post view is fixed for usability.

```
mix.webpack([
    'vendor/niku-cms/niku-cms.js',
], 'public/js/vendor/niku-cms/niku-cms.js');
mix.sass([
    'vendor/niku-cms/niku-cms.scss',
], 'public/css/vendor/niku-cms/niku-cms.css');
```

### Demo and testing

In the config/niku-cms.php you will see a demo variable. If you enable this, you can open up the cms by requesting the following url. The variable will be dynamically added
to the Vue component and will be used as the post type for saving the post. To see the power of the post types, try changing the {post_type} variable into something like
'post' and you will be adding and listing posts to the 'post' post type in the database.
```
http://domain.com/niku-cms/demo/{post_type}
```

## Usage # TO DO
* Write usage description

## Extending the framework # TO DO

* Adding custom fields with custom javascript and css
* changing the html of the pages

## Future features # TO DO
* Taxonomies like categories
* User authorization

## Changelog

For changes and updates, please see our [CHANGELOG](CHANGELOG.md).

## Security Vulnerabilities

If you find any security vulnerabilities, please send a direct e-mail to Nick Kuijpers at n.kuijpers@niku-solutions.nl.

## License

The MIT License (MIT). Please see [MIT license](http://opensource.org/licenses/MIT) for more information.
