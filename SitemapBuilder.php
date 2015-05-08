<?php

namespace demi\sitemap;

use Yii;
use yii\base\Object;
use yii\console\Exception;

class SitemapBuilder extends Object
{
    /** @var string Path to saving sitemap-files. As webroot: "http://example.com" */
    public $savePathAlias = '@frontend/web';
    /** @var string Name of sitemap-file, saved to [[savePathAlias]]: "sitemap.xml" */
    public $sitemapFileName = 'sitemap.xml';
    /** @var array List of sitemap schemas */
    public $schemas = [
        'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"',
    ];
    /** @var int Max count of <url> per one file */
    public $urlsPerFile = 10000;

    /** @var int Count items in current file */
    private $_itemsCount = 0;
    /** @var resource File pointer */
    private $_file;
    /** @var array List of xml-files for sitemap index */
    private $_filesList = [];

    public function writeHeader()
    {
        $header = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $header .= '<urlset ' . implode(PHP_EOL . "\t", $this->schemas) . '>' . PHP_EOL;

        $this->appendToFile($header);
    }

    public function writeFooter()
    {
        $footer = '</urlset>';

        $this->appendToFile($footer);

        // Close current file
        return @fclose($this->_file);
    }

    /**
     * Append to opened file new content
     *
     * @param string $content
     *
     * @throws \yii\console\Exception
     * @return int
     */
    public function appendToFile($content)
    {
        $result = @fwrite($this->_file, $content);

        if ($result === false) {
            throw new Exception("I'm can not wrtite to file");
        }

        return $result;
    }

    /**
     * Checks file pointer
     *
     * @return bool
     */
    public function isFileClosed()
    {
        return !is_resource($this->_file);
    }

    /**
     * Create/rewrite new file with increment number
     *
     * @return resource Pointer of created file
     * @throws \yii\console\Exception
     */
    public function beginNewFile()
    {
        $nameParts = explode('.', $this->sitemapFileName);
        $ext = array_pop($nameParts);
        $filename = implode('.', $nameParts) . (count($this->_filesList) + 1) . '.' . $ext;

        $fullPath = Yii::getAlias($this->savePathAlias) . DIRECTORY_SEPARATOR . $filename;

        $f = @fopen($fullPath, 'w');

        if ($f === false) {
            throw new Exception("I'm cannot create file '$fullPath'");
        }

        // Add new filename to list of exists
        $this->_filesList[] = $filename;

        $this->_file = $f;

        // Write sitemap header
        $this->writeHeader();
        // Reset count of items
        $this->_itemsCount = 0;

        return $this->_file;
    }

    /**
     * Create new url node object
     *
     * @param array $config Object config
     *
     * @return SitemapUrlNode
     */
    public function newUrl($config = [])
    {
        return Yii::createObject(array_merge([
            'class' => SitemapUrlNode::className(),
        ]), $config);
    }

    /**
     * Append new <url> section to current file
     *
     * @param SitemapUrlNode $url
     *
     * @return bool
     */
    public function writeUrl(SitemapUrlNode $url)
    {
        // if count of written items more than allowed per one xml sitemap file
        if (++$this->_itemsCount > $this->urlsPerFile) {
            // Ending of current file
            $this->writeFooter();

            // Starting new file
            $this->beginNewFile();

            return $this->writeUrl($url);
        }

        // Write new <url> section to current file
        return (bool)$this->appendToFile($url . PHP_EOL);
    }

    public function start()
    {
        $this->beginNewFile();
    }

    public function finish()
    {
        // complete file if needed
        if (!$this->isFileClosed()) {
            $this->writeFooter();
        }

        $savePath = Yii::getAlias($this->savePathAlias);
        $sitemapFilename = $savePath . DIRECTORY_SEPARATOR . $this->sitemapFileName;

        if (count($this->_filesList) === 1) {
            // rename created file to actual sitemap name
            $oldName = $savePath . DIRECTORY_SEPARATOR . $this->_filesList[0];

            if (!rename($oldName, $sitemapFilename)) {
                throw new Exception("I'm cannot rename file from '$oldName' to '$sitemapFilename'");
            }

            return true;
        }

        // if count of files greater than 1 - create sitemap-index file

        $this->_file = @fopen($sitemapFilename, 'w');

        $xml = [];
        $xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml[] = '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($this->_filesList as $file) {
            $xml[] = "\t<sitemap>";
            $xml[] = "\t\t<loc>" . Yii::$app->urlManager->baseUrl . '/' . $file . '</loc>';
            // @todo set actual lastmod value
            $xml[] = "\t\t<lastmod>" . date(DATE_W3C) . '</lastmod>';
            $xml[] = "\t</sitemap>";
        }

        $xml[] = "</sitemapindex>";

        $this->appendToFile(implode(PHP_EOL, $xml));

        // Close current file
        return @fclose($this->_file);
    }
}