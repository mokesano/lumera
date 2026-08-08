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
 * @brief Handle site index requests.
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
     * Show index of published articles by author.
     * 
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function authors($args, $request = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();
        $this->validate();
        $this->setupTemplate($request, true);

        $journal = $request->getJournal();
        
        /** @var AuthorDAO $authorDao */
        $authorDao = DAORegistry::getDAO('AuthorDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');

        if (isset($args[0]) && $args[0] === 'view') {
            $firstName = trim((string) $request->getUserVar('firstName'));
            $middleName = trim((string) $request->getUserVar('middleName'));
            $lastName = trim((string) $request->getUserVar('lastName'));
            $affiliation = trim((string) $request->getUserVar('affiliation'));
            $country = trim((string) $request->getUserVar('country'));
            
            // 1. Get Author ID
            $authorId = $request->getUserVar('authorId');
            if (!$authorId && $firstName && $lastName && $authorDao) {
                $authorId = $authorDao->getAuthorIdByName($firstName, $lastName);
            }

            // 2. Get Author Basic Data
            $authorData = ['email' => null, 'url' => null, 'orcid' => null];
            if ($authorId && $authorDao) {
                $fetchedData = $authorDao->getAuthorAdditionalData($authorId);
                if (is_array($fetchedData)) {
                    $authorData = array_merge($authorData, $fetchedData);
                }
            }

            // 3. User Matching & Profile Data
            $matchData = [];
            if ($userDao) {
                $matchData = $userDao->getAuthorUserMatch(
                    $firstName,
                    $lastName,
                    $authorData['email'] ?? null,
                    $authorData['orcid'] ?? null
                ) ?: [];
            }

            // 4. Process Affiliations
            $affiliationsArray = !empty($affiliation) 
                ? array_filter(explode("\n", $affiliation), 'trim') 
                : [];

            // 5. Assign to Template
            $templateMgr = TemplateManager::getManager($request);
            
            // [WIZDAM] Micro-payloads
            $templateMgr->assign([
                'authorId' => $authorId,
                'authorEmail' => $authorData['email'] ?? null,
                'authorUrl' => $authorData['url'] ?? null,
                'authorOrcid' => $authorData['orcid'] ?? null,
                'matchedUserId' => $matchData['userId'] ?? null,
                'hasProfileImage' => !empty($matchData['hasImage']),
                'profileImageUrl' => $matchData['imgUrl'] ?? '',
                'user' => $matchData['user'] ?? new User(),
                'affiliations' => $affiliationsArray
            ]);

            $publishedArticles = $authorDao ? $authorDao->getPublishedArticlesForAuthor(
                $journal ? $journal->getId() : null, 
                $firstName, 
                $middleName, 
                $lastName, 
                $affiliation, 
                $country
            ) : [];

            // Inject User Match Data
            foreach ($publishedArticles as $article) {
                $authors = $article->getAuthors();
                foreach ($authors as $author) {
                    $authorMatchData = $userDao ? $userDao->getAuthorUserMatch(
                        $author->getFirstName(), 
                        $author->getLastName(), 
                        $author->getEmail(), 
                        $author->getData('orcid')
                    ) : [];

                    if (!empty($authorMatchData['found'])) {
                        $author->setData('id', $authorMatchData['userId'] ?? null);
                        $author->setData('isVerifiedAuthor', true);
                        $author->setData('userGender', $authorMatchData['gender'] ?? '');
                        $author->setData('hasProfileImage', !empty($authorMatchData['hasImage']));
                        $author->setData('profileImageUrl', $authorMatchData['imgUrl'] ?? '');
                        $author->setData('userInterests', $authorMatchData['interests'] ?? '');
                        $author->setData('userSalutation', $authorMatchData['salutation'] ?? '');
                        $author->setData('userPhone', $authorMatchData['phone'] ?? '');
                        $author->setData('userFax', $authorMatchData['fax'] ?? '');
                    } else {
                        $author->setData('isVerifiedAuthor', false);
                    }
                }
            }

            $journals = [];
            $issues = [];
            $sections = [];
            $issuesUnavailable = [];

            /** @var IssueDAO $issueDao */
            $issueDao = DAORegistry::getDAO('IssueDAO');
            /** @var SectionDAO $sectionDao */
            $sectionDao = DAORegistry::getDAO('SectionDAO');
            /** @var JournalDAO $journalDao */
            $journalDao = DAORegistry::getDAO('JournalDAO');

            foreach ($publishedArticles as $article) {
                $articleId = $article->getId();
                $issueId = $article->getIssueId();
                $sectionId = $article->getSectionId();
                $journalId = $article->getJournalId();

                if (!isset($issues[$issueId]) && $issueDao) {
                    import('classes.issue.IssueAction');
                    $issue = $issueDao->getIssueById($issueId);
                    $issues[$issueId] = $issue;
                    $issuesUnavailable[$issueId] = $issue && IssueAction::subscriptionRequired($issue) 
                        && (!IssueAction::subscribedUser($journal, $issueId, $articleId) 
                        && !IssueAction::subscribedDomain($journal, $issueId, $articleId));
                }
                if (!isset($journals[$journalId]) && $journalDao) {
                    $journals[$journalId] = $journalDao->getById($journalId);
                }
                if (!isset($sections[$sectionId]) && $sectionDao) {
                    $sections[$sectionId] = $sectionDao->getSection($sectionId, $journalId, true);
                }
            }

            if (empty($publishedArticles)) {
                $request->redirect(null, $request->getRequestedPage());
            }

            // [WIZDAM] Micro-payloads
            $templateMgr->assign([
                'publishedArticles' => $publishedArticles,
                'issues' => $issues,
                'issuesUnavailable' => $issuesUnavailable,
                'sections' => $sections,
                'journals' => $journals,
                'firstName' => $firstName,
                'middleName' => $middleName,
                'lastName' => $lastName,
                'affiliation' => $affiliation
            ]);

            /** @var CountryDAO $countryDao */
            $countryDao = DAORegistry::getDAO('CountryDAO');
            $countryObj = $countryDao ? $countryDao->getCountry($country) : null;
            $templateMgr->assign('country', $countryObj);

            $templateMgr->display('search/authorDetails.tpl');

        } else {
            $searchInitial = trim((string) $request->getUserVar('searchInitial'));
            $searchInitial = preg_match('/^[A-Z]$/i', $searchInitial) ? strtoupper($searchInitial) : '';
            
            $rangeInfo = $this->getRangeInfo('authors');

            $authorsFactory = $authorDao ? $authorDao->getAuthorsAlphabetizedByJournal(
                $journal ? $journal->getId() : null,
                $searchInitial,
                $rangeInfo
            ) : null;
            
            $authors = $authorsFactory ? $authorsFactory->toArray() : [];

            foreach ($authors as $key => $author) {
                // 1. Fix Missing ID
                if (empty($author->getId()) && $authorDao) {
                    $recoveredId = $authorDao->getAuthorIdByName(
                        $author->getFirstName(), 
                        $author->getLastName()
                    );
                    if ($recoveredId) {
                        $author->setData('id', $recoveredId);
                    }
                }

                // 2. Check User Match
                $matchData = $userDao ? $userDao->getAuthorUserMatch(
                    $author->getFirstName(),
                    $author->getLastName(),
                    $author->getEmail(),
                    $author->getData('orcid')
                ) : [];

                if (!empty($matchData['found'])) {
                    $author->setData('id', $matchData['userId'] ?? null);
                    $author->setData('isVerifiedAuthor', true);
                    $author->setData('userGender', $matchData['gender'] ?? '');
                    $author->setData('hasProfileImage', !empty($matchData['hasImage']));
                    $author->setData('profileImageUrl', $matchData['imgUrl'] ?? '');
                    $author->setData('userInterests', $matchData['interests'] ?? '');
                    $author->setData('userSalutation', $matchData['salutation'] ?? '');
                    $author->setData('userPhone', $matchData['phone'] ?? '');
                    $author->setData('userFax', $matchData['fax'] ?? '');
                } else {
                    $author->setData('isVerifiedAuthor', false);
                }
                
                $authors[$key] = $author;
            }

            import('lib.pkp.classes.core.VirtualArrayIterator');
            $totalCount = $authorsFactory ? $authorsFactory->getCount() : count($authors);
            $currentPage = $authorsFactory ? $authorsFactory->getPage() : 1;
            $itemsPerPage = ($rangeInfo && $rangeInfo->isValid()) ? $rangeInfo->getCount() : max(1, count($authors));
            $authorsIterator = new VirtualArrayIterator($authors, $totalCount, $currentPage, $itemsPerPage);

            $templateMgr = TemplateManager::getManager($request);
            $templateMgr->assign([
                'searchInitial' => $request->getUserVar('searchInitial'),
                'alphaList' => explode(' ', __('common.alphaList')),
                'authors' => $authorsIterator
            ]);
            
            $templateMgr->display('search/authorIndex.tpl');
        }
    }

    /**
     * Show index of published articles by title.
     * 
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function titles($args, $request = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();
        $this->setupTemplate($request, true);

        $journal = $request->getJournal();

        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');

        $rangeInfo = $this->getRangeInfo('search');

        $articleIds = $publishedArticleDao 
            ? $publishedArticleDao->getPublishedArticleIdsAlphabetizedByJournal($journal ? $journal->getId() : null) 
            : [];
        $totalResults = count($articleIds);
        
        $offset = $rangeInfo ? $rangeInfo->getCount() * ($rangeInfo->getPage() - 1) : 0;
        $limit = $rangeInfo ? $rangeInfo->getCount() : $totalResults;
        $articleIds = array_slice($articleIds, $offset, $limit);
        
        $resultsArray = ArticleSearch::formatResults($articleIds, $journal);
        
        $sections = [];
        $issues = [];
        $journals = [];
        
        foreach ($resultsArray as $result) {
            if (isset($result['section']) && is_object($result['section'])) {
                $sections[$result['section']->getId()] = $result['section'];
            }
            if (isset($result['issue']) && is_object($result['issue'])) {
                $issues[$result['issue']->getId()] = $result['issue'];
            }
            if (isset($result['journal']) && is_object($result['journal'])) {
                $journals[$result['journal']->getId()] = $result['journal'];
            }
        }
        
        import('lib.pkp.classes.core.VirtualArrayIterator');
        $results = new VirtualArrayIterator(
            $resultsArray, 
            $totalResults, 
            $rangeInfo ? $rangeInfo->getPage() : 1, 
            $rangeInfo ? $rangeInfo->getCount() : $totalResults
        );

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'results' => $results,
            'sections' => $sections,
            'issues' => $issues,
            'journals' => $journals
        ]);
        
        $templateMgr->display('search/titleIndex.tpl');
    }

    /**
     * Display categories.
     * 
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function categories($args, $request = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();
        $this->setupTemplate($request);

        $site = $request->getSite();
        $journal = $request->getJournal();

        /** @var CategoryDAO $categoryDao */
        $categoryDao = DAORegistry::getDAO('CategoryDAO');
        $cache = $categoryDao ? $categoryDao->getCache() : null;

        if ($journal || !$site->getSetting('categoriesEnabled') || !$cache) {
            $request->redirect(null, 'index');
        }

        uasort($cache, function($a, $b) {
            $catA = $a['category'] ?? null; 
            $catB = $b['category'] ?? null; 
            if (!$catA || !$catB) return 0;
            return strcasecmp($catA->getLocalizedName(), $catB->getLocalizedName());
        });

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('categories', $cache);
        $templateMgr->display('search/categories.tpl');
    }

    /**
     * Display category contents.
     * 
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function category($args, $request = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $categoryId = (int) array_shift($args);

        $this->validate();
        $this->setupTemplate($request, true, 'categories');

        $site = $request->getSite();
        $journal = $request->getJournal();

        /** @var CategoryDAO $categoryDao */
        $categoryDao = DAORegistry::getDAO('CategoryDAO');
        $cache = $categoryDao ? $categoryDao->getCache() : null;

        if ($journal || !$site->getSetting('categoriesEnabled') || !$cache || !isset($cache[$categoryId])) {
            $request->redirect(null, 'index');
        }

        $journals = $cache[$categoryId]['journals'] ?? [];
        $category = $cache[$categoryId]['category'] ?? null;

        if (is_array($journals)) {
            uasort($journals, function($a, $b) {
                return strcasecmp($a->getLocalizedTitle(), $b->getLocalizedTitle());
            });
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'journals' => $journals,
            'category' => $category,
            'journalFilesPath' => $request->getBaseUrl() . '/' . Config::getVar('files', 'public_files_dir') . '/journals/'
        ]);
        $templateMgr->display('search/category.tpl');
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