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
	"require": {
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
Edit "./console/config/main.php"
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
"./environments/prod/console/config/main-local.php"
```php
'components' => [
    // fix console create url
    'urlManager' => [
        'baseUrl' => 'http://example.com',
    ],
],
```
"./environments/dev/console/config/main-local.php"
```php
'components' => [
    // fix console create url
    'urlManager' => [
        'baseUrl' => 'http://example.local',
    ],
],
```
run command
```code
php ./init
```

OR just apply same config to your "/console/config/main-local.php"

ALSO you can merge urlManager rules from frontend(or common) to console config.
Just change "/console/config/main.php" file:
```php
// get config of urlManager from frontend for correctly create urls in console app
$frontend = require(__DIR__ . '/../../frontend/config/main.php');
$frontendUrlManager = [
    'components' => [
        'urlManager' => $frontend['components']['urlManager'],
    ],
];

// ...

// Merge frontend urlManager config with console application main config
return yii\helpers\ArrayHelper::merge($frontendUrlManager, [
    'id' => 'app-console',
    // ...
];
```

Also useful append .gitignore for ignore all generated sitemaps files:
```code
# sitemaps
/frontend/web/sitemap*.xml
```


TBD: creating sitemap-data models



Usage
-----
Run Yii console command in project root:
```code
php ./yii sitemap
```
then check "http://site/sitemap.xml" file