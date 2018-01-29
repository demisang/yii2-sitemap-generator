<?php
/**
 * @copyright Copyright (c) 2018 Ivan Orlov
 * @license   https://github.com/demisang/yii2-sitemap-generator/blob/master/LICENSE
 * @link      https://github.com/demisang/yii2-sitemap-generator#readme
 * @author    Ivan Orlov <gnasimed@gmail.com>
 */

namespace demi\sitemap;

use Yii;
use yii\base\BaseObject;
use yii\db\ActiveRecord;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use demi\sitemap\interfaces\Basic;
use demi\sitemap\interfaces\GoogleAlternateLang;
use demi\sitemap\interfaces\GoogleImage;

/**
 * Class SitemapDataHandler
 * @package demi\sitemap
 *
 * @property SitemapBuilder $builder
 */
class SitemapDataHandler extends BaseObject
{
    /** @var string Path to saving sitemap-files. As webroot: "http://example.com" */
    public $savePathAlias = '@frontend/web';
    /** @var string Name of sitemap-file, saved to webroot: "http://example.com/sitemap.xml" */
    public $sitemapFileName = 'sitemap.xml';
    /** @var array Default config for sitemap builder */
    public $builderConfig = [
        'urlsPerFile' => 10000,
    ];

    /** @var SitemapBuilder Configured builder("file writer") */
    private $_builder;
    /** @var array xml-schemas collected by Model implements */
    private $_schemas = [];
    /** @var string Original site language. Need for swith app languages */
    protected static $_appLanguage;

    public function __construct($savePathAlias, $sitemapFileName, $config = [])
    {
        $this->savePathAlias = $savePathAlias;
        $this->sitemapFileName = $sitemapFileName;

        parent::__construct($config);
    }

    /**
     * Get list of sitemap model instancies
     *
     * @param string $modelsPath Alias to sitemap-models dir
     * @param string $modelsNamespace
     *
     * @throws \yii\base\InvalidConfigException
     * @return ActiveRecord[]|Basic[]
     */
    public function getModelsClasses($modelsPath, $modelsNamespace)
    {
        $path = Yii::getAlias($modelsPath);
        $files = @scandir($path);

        if ($files === false) {
            throw new InvalidConfigException('Path to sitemap models "' . $modelsPath . '"(' .
                $path . ') is incorrect');
        }

        $buffer = [];
        foreach ($files as $file) {
            if ($file == '.' || $file == '..' || !is_file($path . DIRECTORY_SEPARATOR . $file)) {
                continue;
            }

            // Search only .php files
            $ext = substr($file, -4);
            if ($ext !== '.php') {
                continue;
            }

            // Combine model class name
            $className = $modelsNamespace . '\\' . str_replace($ext, '', $file);

            // Create new model instance
            $model = Yii::createObject($className);

            if (!$model instanceof Basic) {
                $this->printMessage('Warning: model "' . $className . '" does not implement interface ' .
                    '"demi\sitemap\interfaces\Basic"');

                continue;
            }

            // Add class to list
            $buffer[] = $model;

            // Analysis xml-schemas by $model interfaces
            $this->collectSchemas($model);
        }

        return $buffer;
    }

    /**
     * Start sitemap generate
     *
     * @param Basic[] $models
     */
    public function handleModels(array $models)
    {
        $builder = $this->builder;

        $builder->start();

        foreach ($models as $model) {
            $languages = (array)(isset($model->sitemapLanguages) ? $model->sitemapLanguages : Yii::$app->language);

            if (count($languages) > 1) {
                // Handle separately for each language
                foreach ($languages as $language) {
                    // Swith App language to $language
                    if (isset($model->sitemapSwithLanguages) && $model->sitemapSwithLanguages) {
                        static::setLanguage($language);
                    }

                    $this->handleModel($model, $language);

                    // Restore App language
                    static::restoreLanguage();
                }
            } else {
                // Handle only one language
                $this->handleModel($model, null);
            }
        }

        $builder->finish();
    }

    /**
     * Handle all model content rows
     *
     * @param Basic|ActiveRecord $model
     * @param string|null $lang
     */
    public function handleModel(Basic $model, $lang = null)
    {
        // Handle links to static items
        $items = $model->getSitemapItems($lang);
        if (is_array($items)) {
            // Foreach static model content
            foreach ($items as $item) {
                /* @var $item Basic|GoogleImage|GoogleAlternateLang|ActiveRecord */
                $this->handleItem($item, $lang);
            }
        }

        // Search models in db
        $query = $model->getSitemapItemsQuery($lang);
        if ($query instanceof \yii\db\Query) {
            // Foreach batch models
            $batchSize = static::getModelBatchSize($model);
            foreach ($query->each($batchSize) as $item) {
                /* @var $item Basic|GoogleImage|GoogleAlternateLang|ActiveRecord */
                $this->handleItem($item, $lang);
            }
        }
    }

    /**
     * Handle sitemap item
     *
     * @param Basic|GoogleImage|GoogleAlternateLang|ActiveRecord $item
     * @param string $lang
     *
     * @return bool
     */
    public function handleItem($item, $lang)
    {
        if (is_array($item)) {
            $item = new SitemapArrayItem($item);
            $loc = $item->getSitemapLoc();
            if (empty($loc)) {
                $this->printMessage('Warning: item "' . var_export($item->item, true) .
                    '" does not have a "loc" value');

                return false;
            }
        } elseif (!$item instanceof Basic) {
            $this->printMessage('Warning: item "' . var_export($item, true) .
                '" does not compatible with "demi\sitemap\interfaces\Basic" or Array');

            return false;
        }

        $builder = $this->builder;
        $url = $builder->newUrl();

        // Basic attributes
        $url->loc($item->getSitemapLoc($lang))
            ->lastmod($item->getSitemapLastmod($lang))
            ->changefreq($item->getSitemapChangefreq($lang))
            ->priority($item->getSitemapPriority($lang));

        // Google image attribute
        if ($item instanceof GoogleImage) {
            $images = $item->getSitemapMaterialImages($item, $lang);

            foreach ($images as $image) {
                $url->addImage(
                    $item->getSitemapImageLoc($image, $lang),
                    $item->getSitemapImageGeoLocation($image, $lang),
                    $item->getSitemapImageCaption($image, $lang),
                    $item->getSitemapImageTitle($image, $lang),
                    $item->getSitemapImageLicense($image, $lang)
                );
            }
        }

        // Google alternate links
        if ($item instanceof GoogleAlternateLang || $item instanceof SitemapArrayItem) {
            $links = $item->getSitemapAlternateLinks();

            foreach ($links as $hreflang => $href) {
                $url->addAlternateLink($hreflang, $href);
            }
        }

        return $builder->writeUrl($url);
    }

    /**
     * Get model sitemapBatchSize attribute
     *
     * @param Basic|ActiveRecord $model
     * @param int $defaultValue
     *
     * @return int
     */
    public static function getModelBatchSize(Basic $model, $defaultValue = 10)
    {
        return isset($model->sitemapBatchSize) ? $model->sitemapBatchSize : $defaultValue;
    }

    /**
     * @return SitemapBuilder
     */
    public function getBuilder()
    {
        if ($this->_builder === null) {
            $config = ArrayHelper::merge([
                'class' => SitemapBuilder::className(),
                'savePathAlias' => $this->savePathAlias,
                'sitemapFileName' => $this->sitemapFileName,
                'schemas' => $this->_schemas,
            ], $this->builderConfig);

            $this->_builder = Yii::createObject($config);
        }

        return $this->_builder;
    }

    /**
     * Analysis xml-schemas by $model interfaces
     *
     * @param Basic|GoogleImage|GoogleAlternateLang $model
     */
    public function collectSchemas(Basic $model)
    {
        $this->_schemas['basic'] = 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';

        if ($model instanceof GoogleImage) {
            $this->_schemas['image'] = 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"';
        }

        if ($model instanceof GoogleAlternateLang) {
            $this->_schemas['alternate'] = 'xmlns:xhtml="http://www.w3.org/1999/xhtml"';
        }
    }

    /**
     * Show error in console mode
     *
     * @param string $text
     * @param int $fg foreground
     * @param int $bg background
     */
    public function printMessage($text, $fg = Console::FG_YELLOW, $bg = Console::BG_BLACK)
    {
        if (Yii::$app instanceof \yii\console\Application && Yii::$app->controller->interactive) {
            Yii::$app->controller->stdout($text . PHP_EOL, $fg, $bg);
        }
    }

    /**
     * Set new app language and save original language for future restore
     *
     * @param string $lang
     */
    public static function setLanguage($lang)
    {
        static::$_appLanguage = Yii::$app->language;

        Yii::$app->language = $lang;
    }

    /**
     * Restore app language, saved in [[setLanguage]] function
     */
    public static function restoreLanguage()
    {
        if (static::$_appLanguage !== null) {
            Yii::$app->language = static::$_appLanguage;
        }
    }
}
