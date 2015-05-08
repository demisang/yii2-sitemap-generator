yii2-sitemap-generator
===================

Yii2 component for generate sitemap.xml files

Installation
------------
Run
```code
php composer.phar require "demi/sitemap-generator" "~1.0"
```
or


Add to composer.json in your project
```json
{
	"require":
	{
  		"demi/sitemap-generator": "~1.0"
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
    'controllerMap' => [
        'sitemap' => [
            'class' => 'demi\sitemap\SitemapController',
            'modelsPath' => '@console/models/sitemap', // Sitemap-data models directory
            'modelsNamespace' => 'console\models\sitemap', // Namespace in [[modelsPath]] files
            'savePathAlias' => '@frontend/web', // Where would be placed the generated sitemap-files
            'sitemapFileName' => 'sitemap.xml', // Name of main sitemap-file in [[savePathAlias]] directory
        ],
    ],
];
```



TBD: creating sitemap-data models



Usage
-----
Run Yii console command in project root:
```code
php ./yii sitemap
```
then check "http://site/sitemap.xml" file