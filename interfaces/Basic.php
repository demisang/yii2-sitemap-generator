<?php

namespace demi\sitemap\interfaces;

/**
 * Basic interface for general sitemap.xml file
 *
 * @package demi\sitemap\interfaces
 */
interface Basic
{
    const CHANGEFREQ_ALWAYS = 'always';
    const CHANGEFREQ_HOURLY = 'hourly';
    const CHANGEFREQ_DAILY = 'daily';
    const CHANGEFREQ_WEEKLY = 'weekly';
    const CHANGEFREQ_MONTHLY = 'monthly';
    const CHANGEFREQ_YEARLY = 'yearly';
    const CHANGEFREQ_NEVER = 'never';

    const PRIORITY_0 = '0.0';
    const PRIORITY_1 = '0.1';
    const PRIORITY_2 = '0.2';
    const PRIORITY_3 = '0.3';
    const PRIORITY_4 = '0.4';
    const PRIORITY_5 = '0.5';
    const PRIORITY_6 = '0.6';
    const PRIORITY_7 = '0.7';
    const PRIORITY_8 = '0.8';
    const PRIORITY_9 = '0.9';
    const PRIORITY_10 = '1.0';

    /**
     * Get list of static content items on the specified $lang
     *
     * @param string|null $lang Language code
     *
     * @return array
     */
    public function getSitemapItems($lang = null);

    /**
     * Get [[ActiveQuery]] for searh models the specified $lang
     *
     * @param string|null $lang Language code for searching models
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSitemapItemsQuery($lang = null);

    /**
     * [REQIRED] URL of the page. This URL must begin with the protocol (such as http) and end with a trailing slash,
     * if your web server requires it. This value must be less than 2,048 characters.
     * @url http://www.sitemaps.org/protocol.html#locdef
     *
     * @param string|null $lang language of items content
     *
     * @return string
     */
    public function getSitemapLoc($lang = null);

    /**
     * [OPTIONAL] The date of last modification of the file. This date should be in W3C Datetime format.
     * This format allows you to omit the time portion, if desired, and use YYYY-MM-DD.
     *
     * Note that this tag is separate from the If-Modified-Since (304) header the server can return,
     * and search engines may use the information from both sources differently.
     * @url http://www.sitemaps.org/protocol.html#lastmoddef
     *
     * @param string|null $lang language of items content
     *
     * @return string
     */
    public function getSitemapLastmod($lang = null);

    /**
     * [OPTIONAL] How frequently the page is likely to change. This value provides general information to
     * search engines and may not correlate exactly to how often they crawl the page.
     *
     * Valid values are:
     *
     * always
     * hourly
     * daily
     * weekly
     * monthly
     * yearly
     * never
     *
     * The value "always" should be used to describe documents that change each time they are accessed.
     * The value "never" should be used to describe archived URLs.
     *
     * Please note that the value of this tag is considered a hint and not a command. Even though search engine
     * crawlers may consider this information when making decisions, they may crawl pages marked "hourly"
     * less frequently than that, and they may crawl pages marked "yearly" more frequently than that.
     * Crawlers may periodically crawl pages marked "never" so that they can handle unexpected
     * changes to those pages.
     *
     * @url http://www.sitemaps.org/protocol.html#changefreqdef
     *
     * @param string|null $lang language of items content
     *
     * @return string
     */
    public function getSitemapChangefreq($lang = null);

    /**
     * [OPTIONAL] The priority of this URL relative to other URLs on your site. Valid values range from 0.0 to 1.0.
     * This value does not affect how your pages are compared to pages on other sites—it only lets
     * the search engines know which pages you deem most important for the crawlers.
     *
     * The default priority of a page is 0.5.
     *
     * Please note that the priority you assign to a page is not likely to influence the position of your URLs
     * in a search engine's result pages. Search engines may use this information when selecting between URLs
     * on the same site, so you can use this tag to increase the likelihood that your most important pages
     * are present in a search index.
     *
     * Also, please note that assigning a high priority to all of the URLs on your site is not likely to help you.
     * Since the priority is relative, it is only used to select between URLs on your site.
     * @url http://www.sitemaps.org/protocol.html#prioritydef
     *
     * @param string|null $lang language of items content
     *
     * @return string
     */
    public function getSitemapPriority($lang = null);
} 