# Laravel NIKU CMS

[![Latest Stable Version](https://poser.pugx.org/niku-solutions/cms/v/stable)](https://packagist.org/packages/niku-solutions/cms)
[![Total Downloads](https://poser.pugx.org/niku-solutions/cms/downloads)](https://packagist.org/packages/niku-solutions/cms)
[![Latest Unstable Version](https://poser.pugx.org/niku-solutions/cms/v/unstable)](https://packagist.org/packages/niku-solutions/cms)
[![License](https://poser.pugx.org/niku-solutions/cms/license)](https://packagist.org/packages/niku-solutions/cms)
[![Monthly Downloads](https://poser.pugx.org/niku-solutions/cms/d/monthly)](https://packagist.org/packages/niku-solutions/cms)
[![Daily Downloads](https://poser.pugx.org/niku-solutions/cms/d/daily)](https://packagist.org/packages/niku-solutions/cms)

A codeable CMS for Laravel with post types and custom fields. Extendable as you wish.

This package will easily add a user interface to manage post types with custom fields based on the selected page template.
You can easliy define the post types with authentication and fill in the required custom fields.

It's very easy to add custom fields like Maps, repeaters etc. Comes with a preset of custom fields.

## Installation # TO DO

Install the package via composer:

```
composer require niku-solutions/cms
```

Register the following class:

```
Niku\Cms\CmsServiceProvider::class,
```

Publish the vendor files:

```
php artisan vendor:publish --tag=niku-cms
```


* include javascript
* include css
* include components
* vendor publish

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
