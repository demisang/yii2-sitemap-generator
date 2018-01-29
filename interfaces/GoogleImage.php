<?php
/**
 * @copyright Copyright (c) 2018 Ivan Orlov
 * @license   https://github.com/demisang/yii2-sitemap-generator/blob/master/LICENSE
 * @link      https://github.com/demisang/yii2-sitemap-generator#readme
 * @author    Ivan Orlov <gnasimed@gmail.com>
 */

namespace demi\sitemap\interfaces;

/**
 * Interface GoogleImage
 *
 * @url https://support.google.com/webmasters/answer/178636
 *
 * @package demi\sitemap\interfaces
 */
interface GoogleImage
{
    /**
     * Get list on images assigned to material
     *
     * @param static|self|mixed $material
     * @param string|null $lang Required language of items content
     *
     * @return array
     */
    public function getSitemapMaterialImages($material, $lang = null);

    /**
     * [REQIRED] The URL of the image.
     *
     * In some cases, the image URL may not be on the same domain as your main site. This is fine,
     * as long as both domains are verified in Webmaster Tools. If, for example, you use a content delivery network
     * (CDN) to host your images, make sure that the hosting site is verified in Webmaster Tools OR that you submit
     * your sitemap using robots.txt. In addition, make sure that your robots.txt file doesn’t disallow
     * the crawling of any content you want indexed.
     *
     * @param mixed $image      Image element from [[getSitemapMaterialImages]]
     * @param string|null $lang Required language of item content
     *
     * @return string
     */
    public function getSitemapImageLoc($image, $lang = null);

    /**
     * [OPTIONAL] The geographic location of the image.
     *
     * @example <image:geo_location>Limerick, Ireland</image:geo_location>.
     *
     * @param mixed $image      Image element from [[getSitemapMaterialImages]]
     * @param string|null $lang Required language of item content
     *
     * @return string
     */
    public function getSitemapImageGeoLocation($image, $lang = null);

    /**
     * [OPTIONAL] The caption of the image.
     *
     * @param mixed $image      Image element from [[getSitemapMaterialImages]]
     * @param string|null $lang Required language of item content
     *
     * @return string
     */
    public function getSitemapImageCaption($image, $lang = null);

    /**
     * [OPTIONAL] The title of the image.
     *
     * @param mixed $image      Image element from [[getSitemapMaterialImages]]
     * @param string|null $lang Required language of item content
     *
     * @return string
     */
    public function getSitemapImageTitle($image, $lang = null);

    /**
     * [OPTIONAL] A URL to the license of the image.
     *
     * @param mixed $image      Image element from [[getSitemapMaterialImages]]
     * @param string|null $lang Required language of item content
     *
     * @return string
     */
    public function getSitemapImageLicense($image, $lang = null);
}
