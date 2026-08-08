<?php
declare(strict_types=1);

/**
 * @file pages/search/SearchHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SearchHandler
 * @ingroup pages_search
 *
 * @brief Handle general search requests, AND serve as the shared base
 * class (constructor, setupTemplate()) for AuthorSearchHandler,
 * TitleSearchHandler, dan CategorySearchHandler.
 *
 * [WIZDAM] Sebelumnya SATU class ini menangani search+authors+titles+
 * categories+category sekaligus (617 baris). Dipecah menjadi 4 class:
 * - SearchHandler (file ini): index(), search(), _assignSearchFilters(),
 *   setupTemplate() -- yang terakhir dipakai bersama oleh ketiga subclass
 *   di bawah lewat inheritance.
 * - AuthorSearchHandler: authors()
 * - TitleSearchHandler: titles()
 * - CategorySearchHandler: categories(), category()
 * Tidak ada logika yang diubah saat pemecahan ini -- termasuk seluruh
 * perbaikan N+1 di authors() (memoization getAuthorUserMatch, dst) tetap
 * utuh, cuma dipindah lokasi filenya.
 */

import('classes.search.ArticleSearch');
import('classes.handler.Handler');

class SearchHandler extends Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->addCheck(new HandlerValidatorCustom(
            $this, 
            false, 
            null, 
            null, 
            function($journal) {
                return !$journal || $journal->getSetting('publishingMode') != PUBLISHING_MODE_NONE;
            }, 
            [Application::get()->getRequest()->getJournal()]
        ));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SearchHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Show the search form
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function index($args = [], $request = null) {
        $this->validate();
        $this->search($args, $request);
    }

    /**
     * Private function to transmit current filter values to the template.
     * 
     * @param PKPRequest $request
     * @param TemplateManager $templateMgr
     * @param array $searchFilters
     */
    public function _assignSearchFilters($request, $templateMgr, $searchFilters) {
        // Get the journal id (if any).
        $journal = $searchFilters['searchJournal'] ?? null;
        $journalId = $journal ? (int) $journal->getId() : null;
        $searchFilters['searchJournal'] = $journalId;

        // Assign all filters except for dates which need special treatment.
        $templateSearchFilters = [];
        foreach ($searchFilters as $filterName => $filterValue) {
            if (in_array($filterName, ['fromDate', 'toDate'], true)) continue;
            $templateSearchFilters[$filterName] = $filterValue;
        }

        // Find out whether we have active/empty filters.
        $hasActiveFilters = false;
        $hasEmptyFilters = false;
        foreach ($templateSearchFilters as $filterName => $filterValue) {
            if (in_array($filterName, ['query', 'searchJournal', 'siteSearch'], true)) continue;
            if (empty($filterValue)) {
                $hasEmptyFilters = true;
            } else {
                $hasActiveFilters = true;
            }
        }

        // Assign the filters to the template.
        $templateMgr->assign($templateSearchFilters);

        // Special case: publication date filters.
        foreach (['From', 'To'] as $fromTo) {
            $month = $request->getUserVar("date{$fromTo}Month");
            $day = $request->getUserVar("date{$fromTo}Day");
            $year = (int) $request->getUserVar("date{$fromTo}Year");
            
            if (empty($year)) {
                $date = '--';
                $hasEmptyFilters = true;
            } else {
                $defaultMonth = ($fromTo === 'From') ? 1 : 12;
                $defaultDay = ($fromTo === 'From') ? 1 : 31;
                $date = date(
                    'Y-m-d H:i:s',
                    mktime(
                        0, 0, 0, 
                        empty($month) ? $defaultMonth : (int) $month,
                        empty($day) ? $defaultDay : (int) $day, 
                        $year
                    )
                );
                $hasActiveFilters = true;
            }
            $templateMgr->assign([
                "date{$fromTo}Month" => $month,
                "date{$fromTo}Day" => $day,
                "date{$fromTo}Year" => $year,
                "date{$fromTo}" => $date
            ]);
        }

        // Assign filter flags to the template.
        $templateMgr->assign([
            'hasEmptyFilters' => $hasEmptyFilters,
            'hasActiveFilters' => $hasActiveFilters
        ]);

        // Assign the year range.
        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $yearRange = $publishedArticleDao ? $publishedArticleDao->getArticleYearRange($journalId) : [];
        
        // [WIZDAM] FIX: getArticleYearRange() sekarang mengembalikan integer, bukan string
        // Tidak perlu substr() lagi karena nilai sudah berupa tahun (integer)
        $currentYear = (int) date('Y');
        $yearRangeStart = isset($yearRange[1]) ? (int) $yearRange[1] : $currentYear;
        $yearRangeEnd = isset($yearRange[0]) ? (int) $yearRange[0] : $currentYear;
        
        $startYear = '-' . ($currentYear - $yearRangeStart);
        $endYear = ($yearRangeEnd >= $currentYear) 
            ? '+' . ($yearRangeEnd - $currentYear) 
            : (string) ($yearRangeEnd - $currentYear);
        
        $templateMgr->assign([
            'startYear' => $startYear,
            'endYear' => $endYear
        ]);

        // Assign journal options.
        if (!empty($searchFilters['siteSearch'])) {
            /** @var JournalDAO $journalDao */
            $journalDao = DAORegistry::getDAO('JournalDAO');
            $journals = $journalDao ? $journalDao->getJournalTitles(true) : [];
            $journalOptions = ['' => __('search.allJournals')] + (array) $journals;
            $templateMgr->assign('journalOptions', $journalOptions);
        }
    }

    /**
     * Show the search form
     * 
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function search($args, $request = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();

        $searchFilters = ArticleSearch::getSearchFilters($request);
        $keywords = ArticleSearch::getKeywordsFromSearchFilters($searchFilters);
        $rangeInfo = $this->getRangeInfo('search');

        $error = '';
        $results = ArticleSearch::retrieveResults(
            $searchFilters['searchJournal'] ?? null, 
            $keywords, 
            $error,
            $searchFilters['fromDate'] ?? null, 
            $searchFilters['toDate'] ?? null,
            $rangeInfo
        );

        $this->setupTemplate($request);
        
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->setCacheability(CACHEABILITY_NO_STORE);
        
        // [WIZDAM] Micro-payloads
        $templateMgr->assign([
            'jsLocaleKeys' => ['search.noKeywordError'],
            'results' => $results,
            'error' => $error
        ]);
        
        $this->_assignSearchFilters($request, $templateMgr, $searchFilters);
        $templateMgr->display('search/search.tpl');
    }

    /**
     * Setup common template variables.
     * 
     * @param PKPRequest|null $request
     * @param bool $subclass
     * @param string $op
     */
    public function setupTemplate($request = null, $subclass = false, $op = 'index') {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        parent::setupTemplate();
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('helpTopicId', 'user.searchAndBrowse');

        $opMap = [
            'index' => 'navigation.search',
            'categories' => 'navigation.categories'
        ];

        $router = $request->getRouter();
        $pageHierarchy = [];
        if ($subclass && isset($opMap[$op])) {
            $pageHierarchy = [[$router->url($request, null, 'search', $op), $opMap[$op], false]];
        }
        
        $templateMgr->assign('pageHierarchy', $pageHierarchy);

        $journal = $request->getJournal();
        if (!$journal || !$journal->getSetting('restrictSiteAccess')) {
            $templateMgr->setCacheability(CACHEABILITY_PUBLIC);
        }
    }
    
}
?>