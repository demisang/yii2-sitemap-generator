<?php

namespace demi\sitemap;

use yii\console\Controller;

class SitemapController extends Controller
{
    public function actionIndex()
    {
        $this->stdout('OK');
    }

    public function getHelpSummary()
    {
        return 'Console command for generate sitemap files for Models specified in console config';
    }
} 