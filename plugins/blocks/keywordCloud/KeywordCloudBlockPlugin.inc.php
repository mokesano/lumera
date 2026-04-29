<?php
declare(strict_types=1);

/**
 * @file plugins/blocks/keywordCloud/KeywordCloudBlockPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class KeywordCloudBlockPlugin
 * @ingroup plugins_blocks_keyword_cloud
 *
 * @brief Class for keyword cloud block plugin
 * [WIZDAM STATUS] DEPRECATED UI PATTERN. Safe for PHP 8, but recommended for removal.
 */

import('lib.pkp.classes.plugins.BlockPlugin');

define('KEYWORD_BLOCK_MAX_ITEMS', 20);
define('KEYWORD_BLOCK_CACHE_DAYS', 2);

class KeywordCloudBlockPlugin extends BlockPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function KeywordCloudBlockPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::KeywordCloudBlockPlugin(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Get the display name of this plugin.
     * @return String
     */
    public function getDisplayName(): string {
        return __('plugins.block.keywordCloud.displayName');
    }

    /**
     * Get a description of the plugin.
     */
    public function getDescription(): string {
        return __('plugins.block.keywordCloud.description');
    }

    /**
     * Cache Miss Handler
     * [MODERNISASI] Removed & form $cache signature
     */
    public function _cacheMiss($cache, $id) {
        $keywordMap = array();
        // [MODERNISASI] Hapus referensi &
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $publishedArticles = $publishedArticleDao->getPublishedArticlesByJournalId($cache->getCacheId());
        
        while ($publishedArticle = $publishedArticles->next()) {
            $keywords = array_map('trim', explode(';', $publishedArticle->getLocalizedSubject()));
            foreach ($keywords as $keyword) {
                if (!empty($keyword)) {
                    if (!isset($keywordMap[$keyword])) $keywordMap[$keyword] = 0;
                    $keywordMap[$keyword]++;
                }
            }
            unset($publishedArticle);
        }
        arsort($keywordMap, SORT_NUMERIC);

        $i=0;
        $newKeywordMap = array();
        foreach ($keywordMap as $k => $v) {
            $newKeywordMap[$k] = $v;
            if ($i++ >= KEYWORD_BLOCK_MAX_ITEMS) break;
        }

        $cache->setEntireCache($newKeywordMap);
        return $newKeywordMap[$id];
    }

    /**
     * Get the HTML contents for this block.
     * @param $templateMgr object
     * @param $request PKPRequest
     * @return string
     */
    public function getContents($templateMgr, $request = null) {
        // 1. GUNAKAN LOGIKA MODERN
        $journal = $request->getJournal();
        if (!$journal) return '';

        // 2. KEMBALIKAN LOGIKA ORIGINAL ANDA (CACHE)
        // [MODERNISASI] Hapus referensi &
        $cacheManager = CacheManager::getManager();
        
        // PHP 8 Callback: array($this, '_cacheMiss') tanpa &
        $cache = $cacheManager->getFileCache('keywords_' . AppLocale::getLocale(), $journal->getId(), array($this, '_cacheMiss'));
        
        if (time() - $cache->getCacheTime() > 60 * 60 * 24 * KEYWORD_BLOCK_CACHE_DAYS) $cache->flush();

        $keywords = $cache->getContents();
        if (empty($keywords)) return '';

        $maxOccurs = array_shift(array_values($keywords));
        ksort($keywords);
        
        // Gunakan assign, bukan assign_by_ref
        $templateMgr->assign('cloudKeywords', $keywords);
        $templateMgr->assign('maxOccurs', $maxOccurs);

        // 3. SOLUSI BYPASS PARENT (WAJIB)
        $templateFilename = $this->getBlockTemplateFilename($request);
        if ($templateFilename === null) return '';
        
        return $templateMgr->fetch($this->getTemplatePath() . $templateFilename);
    }
}
?>