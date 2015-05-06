yii2-sitemap-generator
===================

Yii2 component for generate sitemap.xml files

Installation
------------
Run
```code
php composer.phar require "demi/sitemap-generator" "dev-master"
```
or


Add to composer.json in your project
```json
{
	"require":
	{
  		"demi/sort": "dev-master"
	}
}
```
then run command
```code
php composer.phar update
```

Configuration
-------------
Edit /console/config/main.php
```php
return [
    // ...
    'controllerMap' => [
        'sitemap' => 'demi\sitemap\SitemapController',
    ],
    // ...
];
```