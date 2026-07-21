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
 * @brief Article XML galley model object.
 */

import('classes.article.ArticleHTMLGalley');
import('classes.article.SuppFileDAO');

class ArticleXMLGalley extends ArticleHTMLGalley {
    
    /** @var string Name of parent plugin */
    public string $parentPluginName;

    /**
     * Constructor.
     * @param string $parentPluginName
     */
    public function __construct(string $parentPluginName) {
        $this->parentPluginName = $parentPluginName;
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     * @param string $parentPluginName
     */
    public function ArticleXMLGalley(string $parentPluginName = '') {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        // [LUMERA FIX] Simplified direct call instead of func_get_args overhead
        $this->__construct($parentPluginName);
    }

    /**
     * Check if galley is an HTML galley.
     * @return boolean
     */
    public function isHTMLGalley(): bool {
        $fileType = $this->getFileType();
        // [LUMERA FIX] Strict comparison and cleaner matching
        return in_array($fileType, [
            'application/xhtml',
            'application/xhtml+xml',
            'text/html',
            'application/xml',
            'text/xml'
        ], true);
    }

    /**
     * Get results of XSLT transform from file cache
     * @param string $key
     * @return FileCache
     */
    public function _getXSLTCache(string $key) {
        // [LUMERA FIX] Modern static array initialization
        static $caches = [];
        
        if (!isset($caches[$key])) {
            $cacheManager = CacheManager::getManager();
            $caches[$key] = $cacheManager->getFileCache(
                'xsltGalley', $key,
                [$this, '_xsltCacheMiss'] // [LUMERA] Modern array syntax
            );

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
     * @return void
     */
    public function _xsltCacheMiss($cache) {
        $journal = Request::getJournal();
        $xmlGalleyPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);

        if (!$xmlGalleyPlugin) {
            return;
        }

        $xsltRenderer = $xmlGalleyPlugin->getSetting($journal->getId(), 'XSLTrenderer');

        // get command for external XSLT tool
        if ($xsltRenderer === 'external') {
            $xsltRenderer = $xmlGalleyPlugin->getSetting($journal->getId(), 'externalXSLT');
        }

        // choose the configured stylesheet: built-in, or custom
        $xslStylesheet = $xmlGalleyPlugin->getSetting($journal->getId(), 'XSLstylesheet');
        $xslSheet = '';

        switch ($xslStylesheet) {
            case 'NLM':
                if ($this->isPdfGalley()) {
                    $xslSheet = $xmlGalleyPlugin->getPluginPath() . '/transform/nlm/nlm-fo.xsl';
                } else {
                    $xslSheet = $xmlGalleyPlugin->getPluginPath() . '/transform/nlm/nlm-xhtml.xsl';
                }
                break;
            case 'custom': // [LUMERA FIX] Changed typo from 'custom'; to 'custom':
                import('classes.file.JournalFileManager');
                $journalFileManager = new JournalFileManager($journal);
                $customXSL = $xmlGalleyPlugin->getSetting($journal->getId(), 'customXSL');
                if ($customXSL) {
                    $xslSheet = $journalFileManager->filesDir . $customXSL;
                }
                break;
        }

        if ($xslSheet !== '') {
            // transform the XML using whatever XSLT processor we have available
            $contents = $this->transformXSLT($this->getFilePath(), $xslSheet, $xsltRenderer);

            // if all goes well, cache the results of the XSLT transformation
            if ($contents) {
                $cache->setEntireCache($contents);
            }
        }
    }

    /**
     * Return string containing an XHTML fragment generated from the XML/XSL source
     * This function performs any necessary filtering, like image URL replacement.
     * @return string
     */
    public function getHTMLContents(): string {
        $xmlGalleyPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);

        $isEnabled = true;
        if (method_exists($xmlGalleyPlugin, 'getEnabled')) {
            $isEnabled = $xmlGalleyPlugin->getEnabled();
        }

        if (!$xmlGalleyPlugin || !$isEnabled) {
            return parent::getHTMLContents();
        }

        $cache = $this->_getXSLTCache($this->getFileName() . '-' . $this->getId());
        $contents = $cache->getContents();

        if ($contents === '') {
            return parent::getHTMLContents();
        }

        // Replace image references
        $images = $this->getImageFiles();
        $journal = Request::getJournal();

        if ($images !== null) {
            foreach ($images as $image) {
                $imageUrl = Request::url(null, 'article', 'viewFile', [
                    $this->getArticleId(), 
                    $this->getBestGalleyId($journal), 
                    $image->getFileId()
                ]);
                $contents = preg_replace(
                    '/(src|href)\s*=\s*"([^"]*' . preg_quote($image->getOriginalFileName(), '/') . ')"/i',
                    '$1="' . $imageUrl . '"',
                    $contents
                );
            }
        }

        // Perform replacement for ojs://... URLs
        $contents = PKPString::regexp_replace_callback(
            '/(<[^<>]*")[Oo][Jj][Ss]:\/\/([^"]+)("[^<>]*>)/',
            [$this, '_handleOjsUrl'], // [LUMERA] Modern array syntax
            $contents
        );

        /** @var SuppFileDAO $suppFileDao */
        $suppFileDao = DAORegistry::getDAO('SuppFileDAO'); // [LUMERA FIX] Use local variable, not $this->
        $suppFiles = $suppFileDao->getSuppFilesByArticle($this->getArticleId());

        if ($suppFiles) {
            foreach ($suppFiles as $supp) {
                $suppUrl = Request::url(null, 'article', 'downloadSuppFile', [
                    $this->getArticleId(), 
                    $supp->getBestSuppFileId($journal)
                ]);

                $contents = preg_replace(
                    '/href="' . preg_quote($supp->getOriginalFileName(), '/') . '"/',
                    'href="' . $suppUrl . '"',
                    $contents
                );
            }
        }

        // [LUMERA FIX] Strict comparison and safe constant check
        if (defined('LOCALE_ENCODING') && LOCALE_ENCODING === 'iso-8859-1') {
            $contents = PKPString::utf2html($contents);
        }

        return $contents;
    }

    /**
     * Output PDF generated from the XML/XSL/FO source to browser
     * This function performs any necessary filtering, like image URL replacement.
     * @return boolean
     */
    public function viewFileContents(): bool {
        import('lib.pkp.classes.file.FileManager');
        $fileManager = new FileManager();
        
        $pdfFileName = CacheManager::getFileCachePath() . DIRECTORY_SEPARATOR . 
                       'fc-xsltGalley-' . str_replace('.' . $fileManager->parseFileExtension($this->getFileName()), '.pdf', $this->getFileName());

        // if file does not exist or is outdated, regenerate it from FO
        if (!$fileManager->fileExists($pdfFileName) || filemtime($pdfFileName) < filemtime($this->getFilePath())) {
            $cache = $this->_getXSLTCache($this->getFileName() . '-' . $this->getId());
            $contents = $cache->getContents();
            
            if ($contents === '') {
                return false;
            }

            // Replace image references
            $images = $this->getImageFiles();
            if ($images !== null) {
                foreach ($images as $image) {
                    $contents = preg_replace(
                        '/src\s*=\s*"([^"]*)' . preg_quote($image->getOriginalFileName(), '/') . '([^"]*)"/i',
                        'src="${1}' . dirname($this->getFilePath()) . DIRECTORY_SEPARATOR . $image->getFileName() . '$2"',
                        $contents
                    );
                }
            }

            /** @var SuppFileDAO $suppFileDao */
            $suppFileDao = DAORegistry::getDAO('SuppFileDAO');
            $suppFiles = $suppFileDao->getSuppFilesByArticle($this->getArticleId());

            if ($suppFiles) {
                $journal = Request::getJournal();
                foreach ($suppFiles as $supp) {
                    $suppUrl = Request::url(null, 'article', 'downloadSuppFile', [
                        $this->getArticleId(), 
                        $supp->getBestSuppFileId($journal)
                    ]);
                    $contents = preg_replace(
                        '/external-destination\s*=\s*"([^"]*)' . preg_quote($supp->getOriginalFileName(), '/') . '([^"]*)"/i',
                        'external-destination="' . $suppUrl . '"',
                        $contents
                    );
                }
            }

            // create temporary FO file and write the contents
            import('classes.file.TemporaryFileManager');
            $temporaryFileManager = new TemporaryFileManager();
            $tempFoName = $temporaryFileManager->getBasePath() . $this->getFileName() . '-' . $this->getId() . '.fo';

            $temporaryFileManager->writeFile($tempFoName, $contents);

            $journal = Request::getJournal();
            $xmlGalleyPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);

            if ($xmlGalleyPlugin) {
                $fopCommand = str_replace(
                    ['%fo', '%pdf'], 
                    [$tempFoName, $pdfFileName], 
                    $xmlGalleyPlugin->getSetting($journal->getId(), 'externalFOP')
                );

                $fopCommand = escapeshellcmd($fopCommand);
                exec($fopCommand . ' 2>&1', $output, $status);

                if ($status !== 0) {
                    if (!empty($output)) {
                        echo implode("\n", $output);
                        $cache->flush(); 
                    }
                    $temporaryFileManager->deleteFile($tempFoName);
                    return false;
                }
            }

            $temporaryFileManager->deleteFile($tempFoName);
        }

        $fileManager->downloadFile($pdfFileName, $this->getFileType(), true);
        return true;
    }

    /**
     * Return string containing the transformed XML output.
     * This function applies an XSLT transform to a given XML source.
     * @param string $xmlFile
     * @param string $xslFile
     * @param string $xsltType
     * @param array|null $arguments
     * @return string|false
     */
    public function transformXSLT(string $xmlFile, string $xslFile, string $xsltType = '', ?array $arguments = null) {
        $fileManager = new FileManager();
        if (!$fileManager->fileExists($xmlFile) || !$fileManager->fileExists($xslFile)) {
            return false;
        }

        // XSL/DOM modules processing
        if (extension_loaded('xsl') && extension_loaded('dom')) {
            if ($xsltType === 'Native' || $xsltType === 'PHP5' || $xsltType === '') {
                $xmlDom = new DOMDocument('1.0', 'UTF-8');
                $xmlDom->substituteEntities = true;
                $xmlDom->resolveExternals = true;
                $xmlDom->load($xmlFile);

                $xslDom = new DOMDocument('1.0', 'UTF-8');
                $xslDom->load($xslFile);

                $proc = new XsltProcessor();
                $proc->importStylesheet($xslDom);

                if ($arguments !== null) {
                    foreach ($arguments as $param => $value) {
                        $proc->setParameter('', (string) $param, (string) $value);
                    }
                }

                return $proc->transformToXML($xmlDom);
            }
        }

        if ($xsltType !== '') {
            // external command-line renderer
            // [LUMERA FIX] Use str_contains (via polyfill) for cleaner check
            if (!str_contains($xsltType, '%xsl')) {
                return false;
            }

            $xsltCommand = str_replace(['%xsl', '%xml'], [$xslFile, $xmlFile], $xsltType);
            $xsltCommand = escapeshellcmd($xsltCommand);

            exec($xsltCommand . ' 2>&1', $output, $status);

            // [LUMERA FIX] Check $status !== 0 for errors
            if ($status !== 0) {
                if (!empty($output)) {
                    echo implode("\n", $output);
                }
                return false;
            }

            return implode("\n", $output);
        }

        return false;
    }

}
?>