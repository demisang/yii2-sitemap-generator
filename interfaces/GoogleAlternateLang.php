<?php
/**
 * @copyright Copyright (c) 2018 Ivan Orlov
 * @license   https://github.com/demisang/yii2-sitemap-generator/blob/master/LICENSE
 * @link      https://github.com/demisang/yii2-sitemap-generator#readme
 * @author    Ivan Orlov <gnasimed@gmail.com>
 */

namespace demi\sitemap\interfaces;

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
     * Get list of alternate links
     *
     * @return array
     */
    public function getSitemapAlternateLinks();
}
