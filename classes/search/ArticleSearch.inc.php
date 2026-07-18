<?php
declare(strict_types=1);

/**
 * @file classes/search/ArticleSearch.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArticleSearch
 * @ingroup search
 * @see ArticleSearchDAO
 *
 * @brief Class for retrieving article search results.
 */

// Search types
define('ARTICLE_SEARCH_AUTHOR',             0x00000001);
define('ARTICLE_SEARCH_TITLE',              0x00000002);
define('ARTICLE_SEARCH_ABSTRACT',           0x00000004);
define('ARTICLE_SEARCH_DISCIPLINE',         0x00000008);
define('ARTICLE_SEARCH_SUBJECT',            0x00000010);
define('ARTICLE_SEARCH_TYPE',               0x00000020);
define('ARTICLE_SEARCH_COVERAGE',           0x00000040);
define('ARTICLE_SEARCH_GALLEY_FILE',        0x00000080);
define('ARTICLE_SEARCH_SUPPLEMENTARY_FILE', 0x00000100);
define('ARTICLE_SEARCH_SUPPLEMENTARY_FILE_METADATA', 0x00000300);
define('ARTICLE_SEARCH_INDEX_TERMS',        0x00000078);

define('ARTICLE_SEARCH_DEFAULT_RESULT_LIMIT', 20);

import('classes.search.ArticleSearchIndex');
import('lib.pkp.classes.core.VirtualArrayIterator');

class ArticleSearch {

    /**
     * Parses a search query string.
     * Supports +/-, AND/OR, parens
     * @param mixed $query
     * @return array
     */
    public static function _parseQuery($query) {
        $queryString = (string) $query;
        $count = preg_match_all('/(\+|\-|)("[^"]+"|\(|\)|[^\s\)]+)/', $queryString, $matches);
        
        if ($count === false) {
            $count = 0;
            $matches = [[], []];
        }
        
        $pos = 0;
        $keywords = self::_parseQueryInternal($matches[1], $matches[2], $pos, (int) $count);
        return $keywords;
    }

    /**
     * Query parsing helper routine.
     * @param array $signTokens
     * @param array $tokens
     * @param int $pos
     * @param int $total
     * @return array
     */
    public static function _parseQueryInternal($signTokens, $tokens, &$pos, $total) {
        $return = ['+' => [], '' => [], '-' => []];
        $postBool = '';
        $preBool = '';
        $sign = '+';

        $notOperator = PKPString::strtolower(__('search.operator.not'));
        $andOperator = PKPString::strtolower(__('search.operator.and'));
        $orOperator = PKPString::strtolower(__('search.operator.or'));
        
        $totalInt = (int) $total;
        while ($pos < $totalInt) {
            if (!empty($signTokens[$pos])) {
                $sign = $signTokens[$pos];
            }
            
            $token = PKPString::strtolower($tokens[$pos++]);
            
            switch ($token) {
                case $notOperator:
                    $sign = '-';
                    break;
                case ')':
                    return $return;
                case '(':
                    $token = self::_parseQueryInternal($signTokens, $tokens, $pos, $totalInt);
                    // fall-through intended
                default:
                    $postBool = '';
                    if ($pos < $totalInt) {
                        $peek = PKPString::strtolower($tokens[$pos]);
                        if ($peek === $orOperator) {
                            $postBool = 'or';
                            $pos++;
                        } elseif ($peek === $andOperator) {
                            $postBool = 'and';
                            $pos++;
                        }
                    }
                    $bool = empty($postBool) ? $preBool : $postBool;
                    $preBool = $postBool;
                    if ($bool === 'or') {
                        $sign = '';
                    }
                    
                    if (is_array($token)) {
                        $k = $token;
                    } else {
                        $articleSearchIndex = new ArticleSearchIndex();
                        $k = $articleSearchIndex->filterKeywords($token, true);
                    }
                    if (!empty($k)) {
                        $return[$sign][] = $k;
                    }
                    $sign = '';
                    break;
            }
        }
        return $return;
    }

    /**
     * Helper to merge keyword results.
     * @param mixed $journal
     * @param array $keywords
     * @param mixed $publishedFrom
     * @param mixed $publishedTo
     * @return array
     */
    public static function _getMergedArray($journal, $keywords, $publishedFrom, $publishedTo) {
        $resultsPerKeyword = Config::getVar('search', 'results_per_keyword');
        $resultCacheHours = Config::getVar('search', 'result_cache_hours');
        $resultsPerKeyword = is_numeric($resultsPerKeyword) ? (int) $resultsPerKeyword : 100;
        $resultCacheHours = is_numeric($resultCacheHours) ? (int) $resultCacheHours : 24;

        $mergedKeywords = ['+' => [], '' => [], '-' => []];
        foreach ($keywords as $type => $keyword) {
            if (!empty($keyword['+'])) {
                $mergedKeywords['+'][] = ['type' => $type, '+' => $keyword['+'], '' => [], '-' => []];
            }
            if (!empty($keyword[''])) {
                $mergedKeywords[''][] = ['type' => $type, '+' => [], '' => $keyword[''], '-' => []];
            }
            if (!empty($keyword['-'])) {
                $mergedKeywords['-'][] = ['type' => $type, '+' => [], '' => $keyword['-'], '-' => []];
            }
        }
        
        $mergedResults = self::_getMergedKeywordResults($journal, $mergedKeywords, null, $publishedFrom, $publishedTo, $resultsPerKeyword, $resultCacheHours);

        return $mergedResults;
    }

    /**
     * Recursive helper for _getMergedArray.
     * @param mixed $journal
     * @param array $keyword
     * @param mixed $type
     * @param mixed $publishedFrom
     * @param mixed $publishedTo
     * @param int $resultsPerKeyword
     * @param int $resultCacheHours
     * @return array
     */
    public static function _getMergedKeywordResults($journal, $keyword, $type, $publishedFrom, $publishedTo, $resultsPerKeyword, $resultCacheHours) {
        $mergedResults = [];

        if (isset($keyword['type'])) {
            $type = $keyword['type'];
        }

        foreach ($keyword['+'] as $phrase) {
            $results = self::_getMergedPhraseResults($journal, $phrase, $type, $publishedFrom, $publishedTo, $resultsPerKeyword, $resultCacheHours);
            if (empty($mergedResults)) {
                $mergedResults = $results;
            } else {
                foreach ($mergedResults as $articleId => $count) {
                    if (isset($results[$articleId])) {
                        $mergedResults[$articleId] += $results[$articleId];
                    } else {
                        unset($mergedResults[$articleId]);
                    }
                }
            }
        }

        if (!empty($mergedResults) || empty($keyword['+'])) {
            foreach ($keyword[''] as $phrase) {
                $results = self::_getMergedPhraseResults($journal, $phrase, $type, $publishedFrom, $publishedTo, $resultsPerKeyword, $resultCacheHours);
                foreach ($results as $articleId => $count) {
                    if (isset($mergedResults[$articleId])) {
                        $mergedResults[$articleId] += $count;
                    } elseif (empty($keyword['+'])) {
                        $mergedResults[$articleId] = $count;
                    }
                }
            }

            foreach ($keyword['-'] as $phrase) {
                $results = self::_getMergedPhraseResults($journal, $phrase, $type, $publishedFrom, $publishedTo, $resultsPerKeyword, $resultCacheHours);
                foreach ($results as $articleId => $count) {
                    if (isset($mergedResults[$articleId])) {
                        unset($mergedResults[$articleId]);
                    }
                }
            }
        }

        return $mergedResults;
    }

    /**
     * Recursive helper for _getMergedArray.
     * @param mixed $journal
     * @param array $phrase
     * @param mixed $type
     * @param mixed $publishedFrom
     * @param mixed $publishedTo
     * @param int $resultsPerKeyword
     * @param int $resultCacheHours
     * @return array
     */
    public static function _getMergedPhraseResults($journal, $phrase, $type, $publishedFrom, $publishedTo, $resultsPerKeyword, $resultCacheHours) {
        if (isset($phrase['+'])) {
            return self::_getMergedKeywordResults($journal, $phrase, $type, $publishedFrom, $publishedTo, $resultsPerKeyword, $resultCacheHours);
        }

        $mergedResults = [];
        /** @var ArticleSearchDAO $articleSearchDao */
        $articleSearchDao = DAORegistry::getDAO('ArticleSearchDAO');
        $results = $articleSearchDao->getPhraseResults(
            $journal,
            $phrase,
            $publishedFrom,
            $publishedTo,
            $type,
            $resultsPerKeyword,
            $resultCacheHours
        );
        
        if ($results && is_object($results)) {
            while (!$results->eof()) {
                $result = $results->next();
                $articleId = (int) $result['article_id'];
                $count = (int) $result['count'];
                if (!isset($mergedResults[$articleId])) {
                    $mergedResults[$articleId] = $count;
                } else {
                    $mergedResults[$articleId] += $count;
                }
            }
        }
        return $mergedResults;
    }

    /**
     * Sparse array helper.
     * @param mixed $mergedResults
     * @return array
     */
    public static function _getSparseArray($mergedResults) {
        if (!is_array($mergedResults)) {
            return [];
        }

        $resultCount = count($mergedResults);
        $results = [];
        $i = 0;
        foreach ($mergedResults as $articleId => $count) {
            $articleIdInt = (int) $articleId;
            $countInt = (int) $count;
            
            $frequencyIndicator = ($resultCount * $countInt) + $i++;
            $results[$frequencyIndicator] = $articleIdInt;
        }
        krsort($results);
        return $results;
    }

    /**
     * Retrieve the search filters from the request.
     * @param mixed $request
     * @return array All search filters (empty and active)
     */
    public static function getSearchFilters($request) {
        $searchFilters = [
            'query' => $request->getUserVar('query'),
            'searchJournal' => $request->getUserVar('searchJournal'),
            'abstract' => $request->getUserVar('abstract'),
            'authors' => $request->getUserVar('authors'),
            'title' => $request->getUserVar('title'),
            'galleyFullText' => $request->getUserVar('galleyFullText'),
            'suppFiles' => $request->getUserVar('suppFiles'),
            'discipline' => $request->getUserVar('discipline'),
            'subject' => $request->getUserVar('subject'),
            'type' => $request->getUserVar('type'),
            'coverage' => $request->getUserVar('coverage'),
            'indexTerms' => $request->getUserVar('indexTerms')
        ];

        $simpleQuery = $request->getUserVar('simpleQuery');
        if (!empty($simpleQuery)) {
            $searchType = $request->getUserVar('searchField');
            if (array_key_exists($searchType, $searchFilters)) {
                $searchFilters[$searchType] = $simpleQuery;
            }
        }

        $fromDate = $request->getUserDateVar('dateFrom', 1, 1);
        $searchFilters['fromDate'] = ($fromDate === null || $fromDate === false) ? null : date('Y-m-d H:i:s', (int) $fromDate);
        
        $toDate = $request->getUserDateVar('dateTo', 32, 12, null, 23, 59, 59);
        $searchFilters['toDate'] = ($toDate === null || $toDate === false) ? null : date('Y-m-d H:i:s', (int) $toDate);

        $journal = $request->getJournal();
        $siteSearch = !((bool) $journal);
        
        if ($siteSearch) {
            /** @var JournalDAO $journalDao */
            $journalDao = DAORegistry::getDAO('JournalDAO');
            if (!empty($searchFilters['searchJournal'])) {
                // [CASTING DI BODY] Casting ke int sebelum dilempar ke DAO
                $journal = $journalDao->getById((int) $searchFilters['searchJournal']);
            } elseif (array_key_exists('journalTitle', $request->getUserVars())) {
                $journals = $journalDao->getJournals(
                    false, null, 'title',
                    'title', 'is', $request->getUserVar('journalTitle')
                );
                if ($journals && is_object($journals) && $journals->getCount() === 1) {
                    $journal = $journals->next();
                }
            }
        }
        $searchFilters['searchJournal'] = $journal;
        $searchFilters['siteSearch'] = $siteSearch;

        return $searchFilters;
    }

    /**
     * Load the keywords array from a given search filter.
     * @param array $searchFilters
     * @return array required by ArticleSearch::retrieveResults()
     */
    public static function getKeywordsFromSearchFilters($searchFilters) {
        if (!is_array($searchFilters)) {
            return [];
        }

        $indexFieldMap = self::getIndexFieldMap();
        $indexFieldMap[ARTICLE_SEARCH_INDEX_TERMS] = 'indexTerms';
        $keywords = [];
        
        if (isset($searchFilters['query']) && !empty($searchFilters['query'])) {
            $keywords[''] = $searchFilters['query'];
        }
        
        foreach ($indexFieldMap as $bitmap => $searchField) {
            if (isset($searchFilters[$searchField]) && !empty($searchFilters[$searchField])) {
                $keywords[$bitmap] = $searchFilters[$searchField];
            }
        }
        return $keywords;
    }

    /**
     * Format results.
     * @param mixed $results
     * @return array
     */
    public static function formatResults($results) {
        if (!is_array($results) || empty($results)) {
            return [];
        }

        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        /** @var IssueDAO $issueDao */
        $issueDao = DAORegistry::getDAO('IssueDAO');
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        /** @var SectionDAO $sectionDao */
        $sectionDao = DAORegistry::getDAO('SectionDAO');

        $publishedArticleCache = [];
        $articleCache = [];
        $issueCache = [];
        $issueAvailabilityCache = [];
        $journalCache = [];
        $sectionCache = [];

        $returner = [];
        foreach ($results as $articleId) {
            $articleIdInt = (int) $articleId;
            if (!isset($articleCache[$articleIdInt])) {
                $publishedArticleCache[$articleIdInt] = $publishedArticleDao->getPublishedArticleByArticleId($articleIdInt);
                $articleCache[$articleIdInt] = $articleDao->getArticle($articleIdInt);
            }
            
            $article = $articleCache[$articleIdInt];
            $publishedArticle = $publishedArticleCache[$articleIdInt];
            if ($publishedArticle && $article) {
                $sectionId = (int) $article->getSectionId();
                if (!isset($sectionCache[$sectionId])) {
                    $sectionCache[$sectionId] = $sectionDao->getSection($sectionId);
                }

                $journalId = (int) $article->getJournalId();
                if (!isset($journalCache[$journalId])) {
                    $journalCache[$journalId] = $journalDao->getById($journalId);
                }

                $issueId = (int) $publishedArticle->getIssueId();
                if (!isset($issueCache[$issueId])) {
                    $issue = $issueDao->getIssueById($issueId);
                    $issueCache[$issueId] = $issue;
                    import('classes.issue.IssueAction');
                    $issueAvailabilityCache[$issueId] = !IssueAction::subscriptionRequired($issue) || 
                        IssueAction::subscribedUser($journalCache[$journalId], $issueId, $articleIdInt) || 
                        IssueAction::subscribedDomain($journalCache[$journalId], $issueId, $articleIdInt);
                }

                if (!$issueCache[$issueId]->getPublished()) {
                    continue;
                }

                $returner[] = [
                    'article' => $article,
                    'publishedArticle' => $publishedArticleCache[$articleIdInt],
                    'issue' => $issueCache[$issueId],
                    'journal' => $journalCache[$journalId],
                    'issueAvailable' => $issueAvailabilityCache[$issueId],
                    'section' => $sectionCache[$sectionId]
                ];
            }
        }
        return $returner;
    }

    /**
     * Return an array of search results matching the supplied keyword IDs.
     * @param mixed $journal
     * @param mixed $keywords
     * @param mixed $error
     * @param mixed $publishedFrom
     * @param mixed $publishedTo
     * @param mixed $rangeInfo
     * @return VirtualArrayIterator
     */
    public static function retrieveResults($journal, $keywords, $error, $publishedFrom = null, $publishedTo = null, $rangeInfo = null) {
        if ($rangeInfo && is_object($rangeInfo) && method_exists($rangeInfo, 'isValid') && $rangeInfo->isValid()) {
            $page = (int) $rangeInfo->getPage();
            $itemsPerPage = (int) $rangeInfo->getCount();
        } else {
            $page = 1;
            $itemsPerPage = ARTICLE_SEARCH_DEFAULT_RESULT_LIMIT;
        }

        $totalResults = 0;
        $results = HookRegistry::dispatch(
            'ArticleSearch::retrieveResults',
            [$journal, $keywords, $publishedFrom, $publishedTo, $page, $itemsPerPage, &$totalResults, &$error]
        );

        if ($results === false) {
            if (!is_array($keywords)) {
                $keywords = [];
            }
            
            foreach ($keywords as $searchType => $query) {
                $keywords[$searchType] = self::_parseQuery((string) $query);
            }

            $mergedResults = self::_getMergedArray($journal, $keywords, $publishedFrom, $publishedTo);
            $results = self::_getSparseArray($mergedResults);
            
            if (!is_array($results)) {
                $results = [];
            }
            
            $totalResults = count($results);

            $offset = $itemsPerPage * ($page - 1);
            $length = max($totalResults - $offset, 0);
            $length = min($itemsPerPage, $length);
            
            if ($length === 0) {
                $results = [];
            } else {
                $results = array_slice($results, $offset, $length);
            }
        }

        $formattedResults = self::formatResults($results);

        return new VirtualArrayIterator($formattedResults, $totalResults, $page, $itemsPerPage);
    }

    /**
     * Get index field map.
     * @return array
     */
    public static function getIndexFieldMap() {
        return [
            ARTICLE_SEARCH_AUTHOR => 'authors',
            ARTICLE_SEARCH_TITLE => 'title',
            ARTICLE_SEARCH_ABSTRACT => 'abstract',
            ARTICLE_SEARCH_GALLEY_FILE => 'galleyFullText',
            ARTICLE_SEARCH_SUPPLEMENTARY_FILE => 'suppFiles',
            ARTICLE_SEARCH_SUPPLEMENTARY_FILE_METADATA => 'suppFiles',
            ARTICLE_SEARCH_DISCIPLINE => 'discipline',
            ARTICLE_SEARCH_SUBJECT => 'subject',
            ARTICLE_SEARCH_TYPE => 'type',
            ARTICLE_SEARCH_COVERAGE => 'coverage'
        ];
    }

}
?>