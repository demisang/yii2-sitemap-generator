<?php

namespace demi\sitemap\interfaces;

use yii\db\ActiveRecord;

/**
 * Interface GoogleAlternateLang
 *
 * @url https://support.google.com/webmasters/answer/2620865
 *
 * @package demi\sitemap\interfaces
 */
interface GoogleAlternateLang
{
    /**
     * Get list of alternate links for $material
     *
     * @param static|self|mixed $material
     *
     * @return array
     */
    public function getSitemapAlternateLinks($material);
} 