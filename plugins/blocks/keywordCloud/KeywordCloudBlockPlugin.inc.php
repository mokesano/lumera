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
 * @brief Class for keyword cloud block plugin.
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
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.block.keywordCloud.displayName');
    }

    /**
     * Get a description of the plugin.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.block.keywordCloud.description');
    }

    /**
     * Cache Miss Handler
     * @param FileCache $cache
     * @param string $id
     * @return int|null
     */
    public function _cacheMiss($cache, $id) {
        $keywordMap = [];
        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $publishedArticles = $publishedArticleDao->getPublishedArticlesByJournalId($cache->getCacheId());
        
        while ($publishedArticle = $publishedArticles->next()) {
            $subject = (string) $publishedArticle->getLocalizedSubject();
            $keywords = array_map('trim', explode(';', $subject));
            
            foreach ($keywords as $keyword) {
                if ($keyword !== '') {
                    if (!isset($keywordMap[$keyword])) {
                        $keywordMap[$keyword] = 0;
                    }
                    $keywordMap[$keyword]++;
                }
            }
        }
        
        arsort($keywordMap, SORT_NUMERIC);

        $newKeywordMap = [];
        $i = 0;
        foreach ($keywordMap as $k => $v) {
            $newKeywordMap[$k] = $v;
            $i++;
            if ($i >= KEYWORD_BLOCK_MAX_ITEMS) {
                break;
            }
        }

        $cache->setEntireCache($newKeywordMap);

        return $newKeywordMap[$id] ?? null;
    }

    /**
     * Get the HTML contents for this block.
     * @param TemplateManager $templateMgr
     * @param PKPRequest|null $request
     * @return string
     */
    public function getContents($templateMgr, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        
        $journal = $request->getJournal();
        if (!$journal) {
            return '';
        }

        $cacheManager = CacheManager::getManager();
        $cache = $cacheManager->getFileCache(
            'keywords_' . AppLocale::getLocale(), 
            (int) $journal->getId(), 
            [$this, '_cacheMiss']
        );
        
        if (time() - $cache->getCacheTime() > (60 * 60 * 24 * KEYWORD_BLOCK_CACHE_DAYS)) {
            $cache->flush();
        }

        $keywords = $cache->getContents();
        if (empty($keywords)) {
            return '';
        }

        $maxOccurs = reset($keywords);
        ksort($keywords);

        $templateMgr->assign('cloudKeywords', $keywords);
        $templateMgr->assign('maxOccurs', $maxOccurs);

        $templateFilename = $this->getBlockTemplateFilename($request);
        if ($templateFilename === null || $templateFilename === '') {
            return '';
        }
        
        return $templateMgr->fetch($this->getTemplatePath() . $templateFilename);
    }

}
?>