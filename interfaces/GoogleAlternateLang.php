<?php

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