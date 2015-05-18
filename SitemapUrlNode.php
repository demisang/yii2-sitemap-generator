<?php

namespace demi\sitemap;

use demi\sitemap\interfaces\Basic;
use yii\base\Object;

class SitemapUrlNode extends Object
{
    public $loc;
    public $lastmod;
    public $changefreq;
    public $priority;
    public $images = [];
    public $alternateLinks = [];

    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    /**
     * Convert this url node object to valid xml-string
     *
     * @return string
     */
    public function __toString()
    {
        if (empty($this->loc)) {
            // Empty loc is not allowed in sitemap.xml
            return '';
        }

        $url = ['<url>'];

        $url[] = "\t<loc>" . static::prepareUrl($this->loc) . '</loc>';

        // Basic data
        if ($this->lastmod !== null) {
            $lastmod = is_int($this->lastmod) ? $this->lastmod : strtotime($this->lastmod);
            $url[] = "\t<lastmod>" . date(DATE_W3C, $lastmod) . '</lastmod>';
        }

        if ($this->changefreq !== null) {
            $url[] = "\t<changefreq>{$this->changefreq}</changefreq>";
        }

        if ($this->priority !== null) {
            $url[] = "\t<priority>{$this->priority}</priority>";
        }

        // Google images data
        foreach ($this->images as $image) {
            $url[] = "\t<image:image>";
            $url[] = "\t\t<image:loc>" . static::prepareUrl($image['loc']) . '</image:loc>';

            if ($image['caption'] !== null) {
                $url[] = "\t\t<image:caption>" . $image['caption'] . '</image:caption>';
            }
            if ($image['geoLocation'] !== null) {
                $url[] = "\t\t<image:geo_location>" . $image['geoLocation'] . '</image:geo_location>';
            }
            if ($image['title'] !== null) {
                $url[] = "\t\t<image:title>" . $image['title'] . '</image:title>';
            }
            if ($image['license'] !== null) {
                $url[] = "\t\t<image:license>" . $image['license'] . '</image:license>';
            }

            $url[] = "\t</image:image>";
        }

        // Google alternate hreflang data
        foreach ($this->alternateLinks as $hreflang => $href) {
            $url[] = "\t" . '<xhtml:link rel="alternate" hreflang="' . $hreflang . '" ' . 'href="' .
                static::prepareUrl($href) . '" />';
        }

        $url[] = '</url>';

        return implode(PHP_EOL, $url);
    }

    /**
     * Set location value(url)
     *
     * @param string $loc
     *
     * @return $this
     */
    public function loc($loc)
    {
        $this->loc = $loc;

        return $this;
    }

    /**
     * Set last modificated time value
     *
     * @param string $lastmod String applicable to strtotime() function
     *
     * @return $this
     */
    public function lastmod($lastmod)
    {
        $this->lastmod = $lastmod;

        return $this;
    }

    /**
     * Set the regularity of content changing for [[loc]]
     *
     * @param string $changefreq
     *
     * @return $this
     */
    public function changefreq($changefreq)
    {
        $this->changefreq = $changefreq;

        return $this;
    }

    /**
     * Set priority value
     * May be between 0.0 - 1.0
     *
     * @param string $priority
     *
     * @return $this
     */
    public function priority($priority = Basic::PRIORITY_5)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Add image to [[images]] set
     *
     * @param string $loc
     * @param string|null $geoLocation
     * @param string|null $caption
     * @param string|null $title
     * @param string|null $license
     *
     * @return $this
     */
    public function addImage($loc, $geoLocation = null, $caption = null, $title = null, $license = null)
    {
        if (empty($loc)) {
            return $this;
        }

        $image = [
            'loc' => $loc,
            'geoLocation' => $geoLocation,
            'caption' => $caption,
            'title' => $title,
            'license' => $license,
        ];
        $this->images[] = $image;

        return $this;
    }

    /**
     * Add alternate link to [[alternateLinks]] set
     *
     * @param string $hreflang Language code
     * @param string $href     Absolute url to view material on $hreflang language
     *
     * @return $this
     */
    public function addAlternateLink($hreflang, $href)
    {
        $this->alternateLinks[$hreflang] = $href;

        return $this;
    }

    /**
     * Prepare url to place in <loc> tag
     *
     * @param string $url original url
     *
     * @return string
     */
    public static function prepareUrl($url)
    {
        // $url = urlencode($url);

        $replacement = [
            '&' => '&amp;',
            "'" => '&apos;',
            '"' => '&quot;',
            '>' => '&gt;',
            '<' => '&lt;',
        ];

        $url = str_replace(array_keys($replacement), array_values($replacement), $url);

        return $url;
    }
}