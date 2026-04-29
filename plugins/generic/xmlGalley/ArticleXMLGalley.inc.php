<?php
declare(strict_types=1);

/**
 * @file plugins/generic/xmlGalley/ArticleXMLGalley.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArticleXMLGalley
 * @ingroup plugins_generic_xmlGalley
 *
 * @brief Article XML galley model object
 * MODERNIZED FOR SCHOLARWIZDAM FORK (PHP 7/8 Ready)
 */

import('classes.article.ArticleHTMLGalley');
import('classes.article.SuppFileDAO');

class ArticleXMLGalley extends ArticleHTMLGalley {
    
    /** @var string Name of parent plugin */
    public $parentPluginName;

    /**
     * Constructor.
     * @param string $parentPluginName
     */
    public function __construct($parentPluginName) {
        $this->parentPluginName = $parentPluginName;
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ArticleXMLGalley() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Check if galley is an HTML galley.
     * @return boolean
     */
    public function isHTMLGalley() {
        switch ($this->getFileType()) {
            case 'application/xhtml':
            case 'application/xhtml+xml':
            case 'text/html':
            case 'application/xml':
            case 'text/xml':
                return true;
            default: return false;
        }
    }

    /**
     * Get results of XSLT transform from file cache
     * @param string $key
     * @return FileCache
     */
    public function _getXSLTCache($key) {
        static $caches;
        if (!isset($caches)) {
            $caches = array();
        }

        if (!isset($caches[$key])) {
            $cacheManager = CacheManager::getManager();
            $caches[$key] = $cacheManager->getFileCache(
                'xsltGalley', $key,
                array($this, '_xsltCacheMiss')
            );

            // Check to see if the data is outdated
            $cacheTime = $caches[$key]->getCacheTime();

            if ($cacheTime !== null && $cacheTime < filemtime($this->getFilePath())) {
                $caches[$key]->flush();
            }

        }
        return $caches[$key];
    }

    /**
     * Re-run the XSLT transformation on a stale (or missing) cache
     * @param FileCache $cache
     * @return boolean
     */
    public function _xsltCacheMiss($cache) {
        static $contents;
        if (!isset($contents)) {
            $journal = Request::getJournal();
            $xmlGalleyPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);

            $xsltRenderer = $xmlGalleyPlugin->getSetting($journal->getId(), 'XSLTrenderer');

            // get command for external XSLT tool
            if ($xsltRenderer == "external") $xsltRenderer = $xmlGalleyPlugin->getSetting($journal->getId(), 'externalXSLT');

            // choose the configured stylesheet: built-in, or custom
            $xslStylesheet = $xmlGalleyPlugin->getSetting($journal->getId(), 'XSLstylesheet');
            switch ($xslStylesheet) {
                case 'NLM':
                    // if the XML galley is a PDF galley then render the XSL-FO stylesheet
                    if ($this->isPdfGalley()) {
                        $xslSheet = $xmlGalleyPlugin->getPluginPath() . '/transform/nlm/nlm-fo.xsl';
                    } else {
                        $xslSheet = $xmlGalleyPlugin->getPluginPath() . '/transform/nlm/nlm-xhtml.xsl';
                    }
                    break;
                case 'custom';
                    // get file path for custom XSL sheet
                    import('classes.file.JournalFileManager');
                    $journalFileManager = new JournalFileManager($journal);
                    $xslSheet = $journalFileManager->filesDir . $xmlGalleyPlugin->getSetting($journal->getId(), 'customXSL');
                    break;
            }

            // transform the XML using whatever XSLT processor we have available
            $contents = $this->transformXSLT($this->getFilePath(), $xslSheet, $xsltRenderer);

            // if all goes well, cache the results of the XSLT transformation
            if ($contents) $cache->setEntireCache($contents);
        }
        return null;
    }

    /**
     * Return string containing an XHTML fragment generated from the XML/XSL source
     * This function performs any necessary filtering, like image URL replacement.
     * @return string
     */
    public function getHTMLContents() {
        $xmlGalleyPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);

        if ( !$xmlGalleyPlugin ) return parent::getHTMLContents();
        if ( !$xmlGalleyPlugin->getEnabled() ) return parent::getHTMLContents();

        $cache = $this->_getXSLTCache($this->getFileName() . '-' . $this->getId());
        $contents = $cache->getContents();

        if ($contents == "") return parent::getHTMLContents();

        // Replace image references
        $images = $this->getImageFiles();
        $journal = Request::getJournal();

        if ($images !== null) {
            foreach ($images as $image) {
                $imageUrl = Request::url(null, 'article', 'viewFile', array($this->getArticleId(), $this->getBestGalleyId($journal), $image->getFileId()));
                $contents = preg_replace(
                    '/(src|href)\s*=\s*"([^"]*' . preg_quote($image->getOriginalFileName()) . ')"/i',
                    '$1="' . $imageUrl . '"',
                    $contents
                );
            }
        }

        // Perform replacement for ojs://... URLs
        $contents = PKPString::regexp_replace_callback(
            '/(<[^<>]*")[Oo][Jj][Ss]:\/\/([^"]+)("[^<>]*>)/',
            array($this, '_handleOjsUrl'),
            $contents
        );

        // Replace supplementary file references
        $this->suppFileDao = DAORegistry::getDAO('SuppFileDAO');
        $suppFiles = $this->suppFileDao->getSuppFilesByArticle($this->getArticleId());

        if ($suppFiles) {
            foreach ($suppFiles as $supp) {
                $journal = Request::getJournal();
                $suppUrl = Request::url(null, 'article', 'downloadSuppFile', array($this->getArticleId(), $supp->getBestSuppFileId($journal)));

                $contents = preg_replace(
                    '/href="' . preg_quote($supp->getOriginalFileName()) . '"/',
                    'href="' . $suppUrl . '"',
                    $contents
                );
            }
        }

        if (LOCALE_ENCODING == "iso-8859-1") $contents = PKPString::utf2html($contents);

        return $contents;
    }

    /**
     * Output PDF generated from the XML/XSL/FO source to browser
     * This function performs any necessary filtering, like image URL replacement.
     * @return boolean
     */
    public function viewFileContents() {
        import('lib.pkp.classes.file.FileManager');
        $fileManager = new FileManager();
        $pdfFileName = CacheManager::getFileCachePath() . DIRECTORY_SEPARATOR . 'fc-xsltGalley-' . str_replace($fileManager->parseFileExtension($this->getFileName()), 'pdf', $this->getFileName());

        // if file does not exist or is outdated, regenerate it from FO
        if (!$fileManager->fileExists($pdfFileName) || filemtime($pdfFileName) < filemtime($this->getFilePath()) ) {

            $cache = $this->_getXSLTCache($this->getFileName() . '-' . $this->getId());
            $contents = $cache->getContents();
            if ($contents == "") return false;

            // Replace image references
            $images = $this->getImageFiles();

            if ($images !== null) {
                foreach ($images as $image) {
                    $contents = preg_replace(
                        '/src\s*=\s*"([^"]*)' . preg_quote($image->getOriginalFileName()) . '([^"]*)"/i',
                        'src="${1}' . dirname($this->getFilePath()) . DIRECTORY_SEPARATOR . $image->getFileName() . '$2"',
                        $contents );
                }
            }

            // Replace supplementary file references
            $this->suppFileDao = DAORegistry::getDAO('SuppFileDAO');
            $suppFiles = $this->suppFileDao->getSuppFilesByArticle($this->getArticleId());

            if ($suppFiles) {
                $journal = Request::getJournal();
                foreach ($suppFiles as $supp) {
                    $suppUrl = Request::url(null, 'article', 'downloadSuppFile', array($this->getArticleId(), $supp->getBestSuppFileId($journal)));
                    $contents = preg_replace(
                        '/external-destination\s*=\s*"([^"]*)' . preg_quote($supp->getOriginalFileName()) . '([^"]*)"/i',
                        'external-destination="' . $suppUrl . '"',
                        $contents
                    );
                }
            }

            // create temporary FO file and write the contents
            import('classes.file.TemporaryFileManager');
            $temporaryFileManager = new TemporaryFileManager();
            $tempFoName = $temporaryFileManager->filesDir . $this->getFileName() . '-' . $this->getId() . '.fo';

            $temporaryFileManager->writeFile($tempFoName, $contents);

            $journal = Request::getJournal();
            $xmlGalleyPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);

            $fopCommand = str_replace(array('%fo', '%pdf'), 
                    array($tempFoName, $pdfFileName), 
                    $xmlGalleyPlugin->getSetting($journal->getId(), 'externalFOP'));

            // Escape shell command for security
            $fopCommand = escapeshellcmd($fopCommand);

            // run the shell command and get the results
            exec($fopCommand . ' 2>&1', $contents, $status);

            // if there is an error, spit out the shell results to aid debugging
            if ($status != false) {
                if ($contents != '') {
                    echo implode("\n", $contents);
                    $cache->flush(); // clear the XSL cache in case it's a FO error
                    return true;
                } else return false;
            }

            $fileManager->deleteFile($tempFoName);
        }

        $fileManager->downloadFile($pdfFileName, $this->getFileType(), true);
        return true;
    }

    /**
     * Return string containing the transformed XML output.
     * This function applies an XSLT transform to a given XML source.
     */
    public function transformXSLT($xmlFile, $xslFile, $xsltType = "", $arguments = null) {
        $fileManager = new FileManager();
        if (!$fileManager->fileExists($xmlFile) || !$fileManager->fileExists($xslFile)) return false;

        // XSL/DOM modules processing
        if ( extension_loaded('xsl') && extension_loaded('dom') ) {
            // Note: 'PHP5' is kept alongside 'Native' purely to prevent crashes if the database 
            // has not been updated via the settings form yet.
            if ( $xsltType == "Native" || $xsltType == "PHP5" || $xsltType == "" ) {
                $xmlDom = new DOMDocument("1.0", "UTF-8");
                $xmlDom->substituteEntities = true;
                $xmlDom->resolveExternals = true;
                $xmlDom->load($xmlFile);

                $xslDom = new DOMDocument("1.0", "UTF-8");
                $xslDom->load($xslFile);

                $proc = new XsltProcessor();
                $proc->importStylesheet($xslDom);

                foreach ((array) $arguments as $param => $value) {
                    $proc->setParameter('', $param, $value);
                }

                $contents = $proc->transformToXML($xmlDom);
                return $contents;
            }
        }

        if ( $xsltType != "" ) {
            // external command-line renderer
            if ( strpos($xsltType, '%xsl') === false ) return false;

            $xsltCommand = str_replace(array('%xsl', '%xml'), array($xslFile, $xmlFile), $xsltType);

            // Escape shell command for security
            $xsltCommand = escapeshellcmd($xsltCommand);

            exec($xsltCommand . ' 2>&1', $contents, $status);

            if ($status != false) {
                if ($contents != '') {
                    echo implode("\n", $contents);
                    return true;
                } else return false;
            }

            return implode("\n", $contents);
        } else {
            return false;
        }
    }
}
?>