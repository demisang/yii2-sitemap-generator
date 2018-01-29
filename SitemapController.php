<?php
/**
 * @copyright Copyright (c) 2018 Ivan Orlov
 * @license   https://github.com/demisang/yii2-sitemap-generator/blob/master/LICENSE
 * @link      https://github.com/demisang/yii2-sitemap-generator#readme
 * @author    Ivan Orlov <gnasimed@gmail.com>
 */

namespace demi\sitemap;

use yii\console\Controller;

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
    /** @var array Default config for sitemap builder */
    public $builderConfig = [
        'urlsPerFile' => 10000,
    ];

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
        $dataHandler = new SitemapDataHandler($this->savePathAlias, $this->sitemapFileName, [
            'builderConfig' => $this->builderConfig,
        ]);

        $models = $dataHandler->getModelsClasses($this->modelsPath, $this->modelsNamespace);
        $dataHandler->handleModels($models);
    }
}
