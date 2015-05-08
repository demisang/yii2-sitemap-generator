<?php

namespace demi\sitemap;

use Yii;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use yii\db\ActiveRecord;
use yii\helpers\Console;
use demi\sitemap\interfaces\Basic;
use demi\sitemap\interfaces\GoogleAlternateLang;
use demi\sitemap\interfaces\GoogleImage;

/**
 * Class SitemapController
 * @package demi\sitemap
 *
 * @property SitemapBuilder $builder
 */
class SitemapController extends Controller
{
    /** @var string Alias to directory contains sitemap-models */
    public $modelsPath = '@console/models/sitemap';
    /** @var string Namespace of sitemap-models files */
    public $modelsNamespace = 'console\models\sitemap';
    /** @var string Path to saving sitemap-files. As webroot: "http://example.com" */
    public $savePathAlias = '@frontend/web';
    /** @var string Name of sitemap-file, saved to webroot: "http://example.com/sitemap.xml" */
    public $sitemapFileName = 'sitemap.xml';

    /** @var SitemapBuilder Configured builder("file writer") */
    private $_builder;
    /** @var array xml-schemas collected by Model implements */
    private $_schemas = [];

    /**
     * @inheritdoc
     */
    public function getHelpSummary()
    {
        return 'Console command for generate sitemap files for Models specified in console config';
    }

    /**
     * Index action for run sitemap creation
     */
    public function actionIndex()
    {
        $models = $this->getModelsClasses();
        $this->handleModels($models);
    }

    /**
     * Get list of sitemap model instancies
     *
     * @return ActiveRecord[]
     * @throws \yii\base\InvalidConfigException
     */
    public function getModelsClasses()
    {
        $path = Yii::getAlias($this->modelsPath);
        $files = @scandir($path);

        if ($files === false) {
            throw new InvalidConfigException('Path to sitemap models "' . $this->modelsPath . '"(' .
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
            $className = $this->modelsNamespace . '\\' . str_replace($ext, '', $file);

            // Create new model instance
            $model = new $className;

            if (!$model instanceof Basic) {
                if ($this->interactive) {
                    $this->stdout('Warning: model "' . $className . '" does not implement interface ' .
                        '"demi\sitemap\interfaces\Basic"' . PHP_EOL, Console::FG_YELLOW, Console::BG_BLACK);
                    $this->stdout('Warning: model "' . $className . '" does not implement interface ' .
                        '"demi\sitemap\interfaces\Basic"' . PHP_EOL, Console::FG_YELLOW, Console::BG_BLACK);
                }

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
            $languages = (array)(isset($model->languages) ? $model->languages : Yii::$app->language);

            if (count($languages) > 1) {
                // Handle separately for each language
                foreach ($languages as $language) {
                    $this->handleModel($model, $language);
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
        $builder = $this->builder;
        $query = $model->getSitemapItemsQuery($lang);

        foreach ($query->each(static::getModelBatchSize($model)) as $item) {
            /* @var $item Basic|GoogleImage|GoogleAlternateLang|ActiveRecord */

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
            if ($item instanceof GoogleAlternateLang) {
                $links = $item->getSitemapAlternateLinks($item);

                foreach ($links as $hreflang => $href) {
                    $url->addAlternateLink($hreflang, $href);
                }
            }

            $builder->writeUrl($url);
        }
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
            $this->_builder = Yii::createObject([
                'class' => SitemapBuilder::className(),
                'savePathAlias' => $this->savePathAlias,
                'sitemapFileName' => $this->sitemapFileName,
                'schemas' => $this->_schemas,
            ]);
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
}