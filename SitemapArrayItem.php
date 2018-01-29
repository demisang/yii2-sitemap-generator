<?php
/**
 * @copyright Copyright (c) 2018 Ivan Orlov
 * @license   https://github.com/demisang/yii2-sitemap-generator/blob/master/LICENSE
 * @link      https://github.com/demisang/yii2-sitemap-generator#readme
 * @author    Ivan Orlov <gnasimed@gmail.com>
 */

namespace demi\sitemap;

class SitemapArrayItem
{
    public $item = [];

    public function __construct(array $item)
    {
        $this->item = $item;
    }

    public function getItemParam($name, $default = null)
    {
        return isset($this->item[$name]) ? $this->item[$name] : $default;
    }

    public function getSitemapLoc()
    {
        return $this->getItemParam('loc');
    }

    public function getSitemapLastmod()
    {
        return $this->getItemParam('lastmod');
    }

    public function getSitemapChangefreq()
    {
        return $this->getItemParam('changefreq');
    }

    public function getSitemapPriority()
    {
        return $this->getItemParam('priority');
    }

    public function getSitemapAlternateLinks()
    {
        return $this->getItemParam('alternateLinks', []);
    }
}
