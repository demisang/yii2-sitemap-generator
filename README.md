yii2-sitemap-generator
===================

Yii2 component for generate sitemap.xml files

Installation
------------
Run
```code
composer require "demi/sitemap-generator" "~1.0"
```

Configuration
-------------
Edit "/console/config/main.php"
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

Because generator working in console, Yii create absolute url should know site base url, so configure it:
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

The re-init project local config:
```code
php ./init
```
OR just apply same config to your "/console/config/main-local.php"

Also you can merge urlManager rules from frontend(or common) to console config.<br />
Just change "/console/config/main.php" file:
```php
// get config of urlManager from frontend for correctly create urls in console app
$frontend = require(__DIR__ . '/../../frontend/config/main.php');

return [
    'id' => 'app-console',
    'components' => [
        'urlManager' => $frontend['components']['urlManager'],
    ],
];
```

Also useful append .gitignore for ignore all generated sitemaps files:
```code
# sitemaps
/frontend/web/sitemap*.xml
```


Sitemap-data models (important!)
-------------------
You should create special sitemap-models for each your model, that should be in result sitemap.xml.<br />
So let's create a maximal sitemap model extended from you `Post` model and attach interfaces:<br />
/console/models/sitemap/SitemapPost.php
```php
<?php

namespace console\models\sitemap;

use Yii;
use yii\helpers\Url;
use common\models\Post;
use demi\sitemap\interfaces\Basic;
use demi\sitemap\interfaces\GoogleAlternateLang;
use demi\sitemap\interfaces\GoogleImage;

class SitemapPost extends Post implements Basic, GoogleImage, GoogleAlternateLang
{
    /**
     * Handle materials by selecting batch of elements.
     * Increase this value and got more handling speed but more memory usage.
     *
     * @var int
     */
    public $sitemapBatchSize = 10;
    /**
     * List of available site languages
     *
     * @var array [langId => langCode]
     */
    public $sitemapLanguages = [
        'en',
        'ru-RU',
    ];
    /**
     * If TRUE - Yii::$app->language will be switched for each sitemapLanguages and restored after.
     *
     * @var bool
     */
    public $sitemapSwithLanguages = true;

    /* BEGIN OF Basic INTERFACE */

    /**
     * @inheritdoc
     */
    public function getSitemapItems($lang = null)
    {
        // Add to sitemap.xml links to regular pages
        return [
            // site/index
            [
                'loc' => Url::to(['/site/index', 'lang' => $lang]),
                'lastmod' => time(),
                'changefreq' => static::CHANGEFREQ_DAILY,
                'priority' => static::PRIORITY_10,
                'alternateLinks' => [
                    'en' => Url::to(['/site/index', 'lang' => 'en']),
                    'ru' => Url::to(['/site/index', 'lang' => 'ru']),
                ],
            ],
            // post/index
            [
                'loc' => Url::to(['/post/index', 'lang' => $lang]),
                'lastmod' => time(),
                'changefreq' => static::CHANGEFREQ_DAILY,
                'priority' => static::PRIORITY_10,
                'alternateLinks' => [
                    'en' => Url::to(['/post/index', 'lang' => 'en']),
                    'ru' => Url::to(['/post/index', 'lang' => 'ru']),
                ],
            ],
            // ... you can add more regular pages if needed, but I recommend
            // separate pages related only for current model class
        ];
    }

    /**
     * @inheritdoc
     */
    public function getSitemapItemsQuery($lang = null)
    {
        // Base select query for current model
        return static::find()
            ->select(['id', 'title', 'date', 'updated_at'])
            ->where(['status' => Post::STATUS_ACTIVE])
            ->orderBy(['date' => SORT_DESC]);
    }

    /**
     * @inheritdoc
     */
    public function getSitemapLoc($lang = null)
    {
        // Return absolute url to Post model view page
        return Url::to(['/post/view', 'id' => $this->id], true);
    }

    /**
     * @inheritdoc
     */
    public function getSitemapLastmod($lang = null)
    {
        return $this->updated_at;
    }

    /**
     * @inheritdoc
     */
    public function getSitemapChangefreq($lang = null)
    {
        return static::CHANGEFREQ_MONTHLY;
    }

    /**
     * @inheritdoc
     */
    public function getSitemapPriority($lang = null)
    {
        return static::PRIORITY_8;
    }

    /* END OF Basic INTERFACE */
    /* BEGIN OF GoogleImage INTERFACE */

    /**
     * @inheritdoc
     *
     * @param self $material
     */
    public function getSitemapMaterialImages($material, $lang = null)
    {
        // List of Post related images without scheme (news logo eg.)
        $images = [];
        // "/uploads/post/1.jpg"
        $images[] = $this->logo;
        // You can add more images (if Post have a photo gallery etc.)

        // !important! You can return array of any elements(Objects eg. $this->images relation), because its elements
        // will be foreached and become as $image argument for $this->getSitemapImageLoc($image)

        return $images;
    }

    /**
     * @inheritdoc
     */
    public function getSitemapImageLoc($image, $lang = null)
    {
        // Return absolute url to each Post image
        // @see $image argument becomes from $this->getSitemapMaterialImages()
        return Yii::$app->urlManager->baseUrl . $image;
    }

    /**
     * @inheritdoc
     */
    public function getSitemapImageGeoLocation($image, $lang = null)
    {
        // Location name string, for example: "Limerick, Ireland"
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getSitemapImageCaption($image, $lang = null)
    {
        // Image caption, simply use Post title
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getSitemapImageTitle($image, $lang = null)
    {
        // Image title, simply use Post title
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getSitemapImageLicense($image, $lang = null)
    {
        return null;
    }

    /* END OF GoogleImage INTERFACE */
    /* BEGIN OF GoogleAlternateLang INTERFACE */

    /**
     * @inheritdoc
     */
    public function getSitemapAlternateLinks()
    {
        // Generate altername links for all site language versions for this Post
        $buffer = [];

        foreach ($this->sitemapLanguages as $langCode) {
            $buffer[$langCode] = $this->getSitemapLoc($langCode);
            // or eg.: $buffer[$langCode] = Url::to(['post/view', 'id' => $this->id, 'lang' => $langCode]);
        }

        return $buffer;
    }

    /* END OF GoogleAlternateLang INTERFACE */
}

```

If you sitemap model doesn't have images or only one site language - just remove interfaces
`GoogleImage` and/or `GoogleAlternateLang` and it's functions.<br />

Generator automaticly searching all your sitemap models by config path: `/console/models/sitemap/*`


Usage
-----
Run Yii console command in project root:
```code
./yii sitemap
```
then check "http://site/sitemap.xml" file
