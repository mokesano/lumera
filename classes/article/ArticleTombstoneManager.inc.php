<?php
declare(strict_types=1);

/**
 * @file classes/article/ArticleTombstoneManager.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArticleTombstoneManager
 * @ingroup article
 *
 * @brief Class defining basic operations for article tombstones.
 *
 * WIZDAM MODERNIZATION:
 * - PHP 8.x Compatibility (Constructor, Ref removal)
 * - Code Cleanup (Redundant calls)
 * - Strict Typing
 */


class ArticleTombstoneManager {
    
    /**
     * Constructor
     */
    public function __construct() {
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ArticleTombstoneManager() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ArticleTombstoneManager uses deprecated constructor. Please refactor to __construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Insert a tombstone for a deleted article.
     * @param Article $article
     * @param Journal $journal
     */
    public function insertArticleTombstone($article, $journal) {
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $section = $sectionDao->getSection($article->getSectionId());

        // PHP 8 Safety: Ensure section exists
        if (!$section) return;

        $tombstoneDao = DAORegistry::getDAO('DataObjectTombstoneDAO'); /* @var $tombstoneDao DataObjectTombstoneDAO */
        
        // delete article tombstone -- to ensure that there aren't more than one tombstone for this article
        $tombstoneDao->deleteByDataObjectId((int) $article->getId());
        
        // Removed redundant $sectionDao->getSection call here

        $setSpec = urlencode((string) $journal->getPath()) . ':' . urlencode((string) $section->getLocalizedAbbrev());
        $oaiIdentifier = 'oai:' . Config::getVar('oai', 'repository_id') . ':' . 'article/' . $article->getId();
        
        $OAISetObjectsIds = array(
            ASSOC_TYPE_JOURNAL => (int) $journal->getId(),
            ASSOC_TYPE_SECTION => (int) $section->getId(),
        );

        $articleTombstone = $tombstoneDao->newDataObject();
        $articleTombstone->setDataObjectId((int) $article->getId());
        $articleTombstone->stampDateDeleted();
        $articleTombstone->setSetSpec($setSpec);
        $articleTombstone->setSetName($section->getLocalizedTitle());
        $articleTombstone->setOAIIdentifier($oaiIdentifier);
        $articleTombstone->setOAISetObjectsIds($OAISetObjectsIds);
        
        $tombstoneDao->insertObject($articleTombstone);

        // Hook Dispatch: Objects passed by value (handle)
        if (HookRegistry::dispatch('ArticleTombstoneManager::insertArticleTombstone', array($articleTombstone, $article, $journal))) return;
    }
}

?>