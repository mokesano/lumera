<?php
declare(strict_types=1);

/**
 * @file classes/search/ArticleSearchIndex.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArticleSearchIndex
 * @ingroup search
 *
 * @brief Class to maintain the article search index.
 * [WIZDAM EDITION] High Performance & PHP 8+ Compatible.
 */

import('lib.pkp.classes.search.SearchFileParser');
import('lib.pkp.classes.search.SearchHTMLParser');
import('lib.pkp.classes.search.SearchHelperParser');
import('classes.journal.Journal'); // [WIZDAM] Explicit Import

define('SEARCH_STOPWORDS_FILE', 'lib/pkp/registry/stopwords.txt');
define('SEARCH_KEYWORD_MAX_LENGTH', 40);

class ArticleSearchIndex {

    /**
     * Constructor.
     */
    public function __construct() {
    }

    /**
     * Signal to the indexing back-end that the metadata of an article changed.
     * @param Article $article
     */
    public function articleMetadataChanged($article): void {
        if (!($article instanceof Article)) return;
        
        $hookResult = HookRegistry::dispatch(
            'ArticleSearchIndex::articleMetadataChanged',
            [$article]
        );

        if ($hookResult === false || is_null($hookResult)) {
            $authorText = [];
            $authors = $article->getAuthors();
            
            foreach ($authors as $author) {
                // [WIZDAM] Using array_filter to safely add non-null strings
                $authorText[] = $author->getFirstName();
                $authorText[] = $author->getMiddleName();
                $authorText[] = $author->getLastName();
                
                $affiliations = $author->getAffiliation(null);
                if (is_array($affiliations)) $authorText = array_merge($authorText, $affiliations);
                
                $bios = $author->getBiography(null);
                if (is_array($bios)) {
                    foreach ($bios as $bio) {
                        $authorText[] = strip_tags((string) $bio);
                    }
                }
            }

            $articleId = $article->getId();
            $this->_updateTextIndex((int) $articleId, ARTICLE_SEARCH_AUTHOR, $authorText);
            $this->_updateTextIndex((int) $articleId, ARTICLE_SEARCH_TITLE, $article->getTitle(null));
            $this->_updateTextIndex((int) $articleId, ARTICLE_SEARCH_ABSTRACT, $article->getAbstract(null));

            $this->_updateTextIndex((int) $articleId, ARTICLE_SEARCH_DISCIPLINE, (array) $article->getDiscipline(null));
            $this->_updateTextIndex((int) $articleId, ARTICLE_SEARCH_SUBJECT, array_merge(array_values((array) $article->getSubjectClass(null)), array_values((array) $article->getSubject(null))));
            $this->_updateTextIndex((int) $articleId, ARTICLE_SEARCH_TYPE, $article->getType(null));
            $this->_updateTextIndex((int) $articleId, ARTICLE_SEARCH_COVERAGE, array_merge(array_values((array) $article->getCoverageGeo(null)), array_values((array) $article->getCoverageChron(null)), array_values((array) $article->getCoverageSample(null))));
        }
    }

    /**
     * Signal to the indexing back-end that an article file changed.
     * @param int $articleId
     * @param int $type
     * @param int $fileId
     */
    public function articleFileChanged(int $articleId, int $type, int $fileId): void {
        $hookResult = HookRegistry::dispatch(
            'ArticleSearchIndex::articleFileChanged',
            [$articleId, $type, $fileId]
        );

        if ($hookResult === false || is_null($hookResult)) {
            import('classes.file.ArticleFileManager');
            $fileManager = new ArticleFileManager($articleId);
            $file = $fileManager->getFile($fileId);

            $parser = null;
            if (isset($file)) {
                $parser = SearchFileParser::fromFile($file);
            }

            if (isset($parser)) {
                if ($parser->open()) {
                    /** @var ArticleSearchDAO $searchDao */
                    $searchDao = DAORegistry::getDAO('ArticleSearchDAO');
                    $objectId = $searchDao->insertObject($articleId, $type, $fileId);

                    $position = 0;
                    while(($text = $parser->read()) !== false) {
                        // [WIZDAM] Ensure object ID is integer
                        $this->_indexObjectKeywords((int) $objectId, (string) $text, $position);
                    }
                    $parser->close();
                }
            }
        }
    }

    /**
     * Signal that all files assigned to an article changed.
     * @param Article $article
     */
    public function articleFilesChanged($article): void {
        if (!($article instanceof Article)) return;

        $hookResult = HookRegistry::dispatch(
            'ArticleSearchIndex::articleFilesChanged',
            [$article]
        );

        if ($hookResult === false || is_null($hookResult)) {
            /** @var SuppFileDAO $fileDao */
            $fileDao = DAORegistry::getDAO('SuppFileDAO');
            $files = $fileDao->getSuppFilesByArticle($article->getId());
            
            foreach ($files as $file) {
                if ($file->getFileId()) {
                    // [WIZDAM] Explicit casting to int to satisfy ArticleSearchIndex::articleFileChanged(int, int, int)
                    $this->articleFileChanged(
                        (int) $article->getId(), 
                        (int) ARTICLE_SEARCH_SUPPLEMENTARY_FILE, 
                        (int) $file->getFileId()
                    );
                }
                $this->suppFileMetadataChanged($file);
            }
            unset($files);

            /** @var ArticleGalleyDAO $fileDao */
            $fileDao = DAORegistry::getDAO('ArticleGalleyDAO');
            $files = $fileDao->getGalleysByArticle($article->getId());
            
            foreach ($files as $file) {
                if ($file->getFileId()) {
                    $this->articleFileChanged($article->getId(), ARTICLE_SEARCH_GALLEY_FILE, $file->getFileId());
                }
            }
        }
    }

    /**
     * Signal that a file was deleted.
     * @param int $articleId
     * @param int|null $type optional
     * @param int|null $assocId optional
     * @return bool
     */
    public function articleFileDeleted(int $articleId, ?int $type = null, ?int $assocId = null): bool {
        $hookResult = HookRegistry::dispatch(
            'ArticleSearchIndex::articleFileDeleted',
            [$articleId, $type, $assocId]
        );

        if ($hookResult === false || is_null($hookResult)) {
            /** @var ArticleSearchDAO $searchDao */
            $searchDao = DAORegistry::getDAO('ArticleSearchDAO');
            
            // [WIZDAM FIX] Pisahkan eksekusi dan return value
            $searchDao->deleteArticleKeywords($articleId, $type, $assocId);
            
            return true;
        }

        return (bool) $hookResult; // Return hook result if available
    }

    /**
     * Signal that supp file metadata changed.
     * @param SuppFile $suppFile
     */
    public function suppFileMetadataChanged($suppFile): void {
        if (!($suppFile instanceof SuppFile)) return;
        
        $hookResult = HookRegistry::dispatch(
            'ArticleSearchIndex::suppFileMetadataChanged',
            [$suppFile]
        );

        if ($hookResult === false || is_null($hookResult)) {
            $articleId = $suppFile->getArticleId();
            $this->_updateTextIndex(
                (int) $articleId,
                ARTICLE_SEARCH_SUPPLEMENTARY_FILE_METADATA,
                array_merge(
                    array_values((array) $suppFile->getTitle(null)),
                    array_values((array) $suppFile->getCreator(null)),
                    array_values((array) $suppFile->getSubject(null)),
                    array_values((array) $suppFile->getTypeOther(null)),
                    array_values((array) $suppFile->getDescription(null)),
                    array_values((array) $suppFile->getSource(null))
                ),
                (int) $suppFile->getFileId()
            );
        }
    }

    /**
     * Signal to the indexing back-end that the metadata of an article was deleted.
     * @param int $articleId
     */
    public function articleDeleted(int $articleId): void {
        HookRegistry::dispatch(
            'ArticleSearchIndex::articleDeleted',
            [$articleId]
        );
        // Note: Actual deletion logic should occur in a hook or ArticleSearchDAO
    }

    /**
     * Let the indexing back-end know that the current transaction
     * finished so that the index can be batch-updated.
     */
    public function articleChangesFinished(): void {
        HookRegistry::dispatch(
            'ArticleSearchIndex::articleChangesFinished'
        );
    }

    /**
     * Split a string into a clean array of keywords.
     * @param string|array $text
     * @param bool $allowWildcards
     * @return array<string> of keywords
     */
    public function filterKeywords($text, bool $allowWildcards = false): array {
        $minLength = (int) Config::getVar('search', 'min_word_length');
        $stopwords = $this->_loadStopwords();

        // Join multiple lines
        if (is_array($text)) $text = join("\n", $text);

        // [OPTIMASI] HTML decode & strip tags
        $text = html_entity_decode($text, ENT_COMPAT, 'UTF-8');
        $cleanText = strip_tags($text);

        // [OPTIMASI] Gunakan native mb_strtolower (C-level speed)
        $cleanText = mb_strtolower($cleanText, 'UTF-8');

        // [OPTIMASI] Regex Tokenizer (\w+) jauh lebih cepat
        // \w+ ensures we get words, /u enables Unicode mode.
        preg_match_all('/\w+/u', $cleanText, $matches);
        $words = $matches[0];

        $keywords = [];
        foreach ($words as $k) {
            // [OPTIMASI] Cek panjang string
            if (mb_strlen($k) < $minLength) continue;
            
            // [OPTIMASI] Cek numeric & stopwords dengan isset (O(1) lookup)
            if (!is_numeric($k) && !isset($stopwords[$k])) {
                $keywords[] = mb_substr($k, 0, SEARCH_KEYWORD_MAX_LENGTH);
            }
        }
        return $keywords;
    }

    /**
     * [WIZDAM EDITION] Includes Memory Cleanup logic
     * Rebuild the search index.
     * @param bool $log
     * @param Journal|null $journal
     */
    public function rebuildIndex(bool $log = false, $journal = null): void {
        if ($journal instanceof Journal) die(__('search.cli.rebuildIndex.indexingByJournalNotSupported') . "\n");

        $hookResult = HookRegistry::dispatch('ArticleSearchIndex::rebuildIndex', [$log, $journal]);

        if ($hookResult === false || is_null($hookResult)) {
            if ($log) echo __('search.cli.rebuildIndex.clearingIndex') . ' ... ';
            /** @var ArticleSearchDAO $searchDao */
            $searchDao = DAORegistry::getDAO('ArticleSearchDAO');
            $searchDao->clearIndex();
            if ($log) echo __('search.cli.rebuildIndex.done') . "\n";

            /** @var JournalDAO $journalDao */
            $journalDao = DAORegistry::getDAO('JournalDAO');
            /** @var ArticleDAO $articleDao */
            $articleDao = DAORegistry::getDAO('ArticleDAO');

            $journals = $journalDao->getJournals();
            $wasGcEnabled = gc_enabled();
            if ($wasGcEnabled) gc_disable();

            while (!$journals->eof()) {
                /** @var Journal $journal */
                $journal = $journals->next();
                $numIndexed = 0;
                if ($log) echo __('search.cli.rebuildIndex.indexing', ['journalName' => $journal->getLocalizedTitle()]) . ' ... ';

                $articles = $articleDao->getArticlesByJournalId($journal->getId());
                while ($article = $articles->next()) {
                    /** @var Article $article */
                    if ($article->getDateSubmitted()) {
                        $this->articleMetadataChanged($article);
                        $this->articleFilesChanged($article);
                        $numIndexed++;
                    }
                    // [WIZDAM] Safe memory release
                    unset($article);
                }
                unset($articles);

                // [WIZDAM CRITICAL] Clear Keyword Cache & Force GC after each journal
                // Ini mencegah array cache membengkak hingga ratusan MB
                $searchDao->clearKeywordCache();
                
                if (function_exists('gc_collect_cycles')) gc_collect_cycles();

                if ($log) echo __('search.cli.rebuildIndex.result', ['numIndexed' => $numIndexed]) . "\n";
                unset($journal);
            }
            if ($wasGcEnabled) gc_enable();
        }
    }

    //
    // Private helper methods
    //
    
    /**
     * Index a block of text.
     * @param int $objectId
     * @param string $text
     * @param int $position - passed by reference for sequential indexing
     */
    public function _indexObjectKeywords(int $objectId, string $text, &$position): void {
        /** @var ArticleSearchDAO $searchDao */
        $searchDao = DAORegistry::getDAO('ArticleSearchDAO');
        $keywords = $this->filterKeywords($text);
        
        foreach ($keywords as $keyword) {
            if ($searchDao->insertObjectKeyword($objectId, $keyword, $position) !== null) {
                $position += 1;
            }
        }
    }

    /**
     * Add a block of text to the search index.
     * @param int $articleId
     * @param int $type
     * @param mixed $text (string|array)
     * @param int|null $assocId optional
     */
    public function _updateTextIndex(int $articleId, int $type, $text, ?int $assocId = null): void {
        /** @var ArticleSearchDAO $searchDao */
        $searchDao = DAORegistry::getDAO('ArticleSearchDAO');
        $objectId = $searchDao->insertObject((int) $articleId, (int) $type, (int) $assocId);
        $position = 0;
        
        // [WIZDAM] Fix: Prepare variables for pass-by-reference
        $posInt = (int) $position; 
        $objIdInt = (int) $objectId;
        $textStr = (string) (is_array($text) ? implode(' ', $text) : $text);
        
        // Now pass the variables, not the expression/casting results
        $this->_indexObjectKeywords($objIdInt, $textStr, $posInt);
    }

    /**
     * Return list of stopwords.
     * @return array<string, int> with stopwords as keys
     */
    public function _loadStopwords(): array {
        static $searchStopwords;

        if (!isset($searchStopwords)) {
            if (file_exists(SEARCH_STOPWORDS_FILE)) {
                $searchStopwords = array_count_values(array_filter(
                    file(SEARCH_STOPWORDS_FILE), 
                    function($a) {
                        $a = trim((string) $a);
                        return !empty($a) && $a[0] != '#';
                    }
                ));
            } else {
                $searchStopwords = [];
            }
            $searchStopwords[''] = 1;
        }

        return $searchStopwords;
    }
}
?>