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
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.search.ArticleSearch');
import('classes.handler.Handler');

class SearchHandler extends Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        // [WIZDAM] Replaced create_function with anonymous function
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
     * @param object|null $request PKPRequest
     */
    public function index($args = [], $request = null) {
        $this->validate();
        $this->search($args, $request);
    }

    /**
     * Private function to transmit current filter values
     * to the template.
     * @param object $request PKPRequest
     * @param object $templateMgr TemplateManager
     * @param array $searchFilters
     */
    public function _assignSearchFilters($request, $templateMgr, $searchFilters) {
        // Get the journal id (if any).
        $journal = $searchFilters['searchJournal'];
        $journalId = ($journal ? $journal->getId() : null);
        $searchFilters['searchJournal'] = $journalId;

        // Assign all filters except for dates which need special treatment.
        $templateSearchFilters = [];
        foreach($searchFilters as $filterName => $filterValue) {
            if (in_array($filterName, ['fromDate', 'toDate'])) continue;
            $templateSearchFilters[$filterName] = $filterValue;
        }

        // Find out whether we have active/empty filters.
        $hasActiveFilters = false;
        $hasEmptyFilters = false;
        foreach($templateSearchFilters as $filterName => $filterValue) {
            // The main query and journal selector will always be displayed
            // apart from other filters.
            if (in_array($filterName, ['query', 'searchJournal', 'siteSearch'])) continue;
            if (empty($filterValue)) {
                $hasEmptyFilters = true;
            } else {
                $hasActiveFilters = true;
            }
        }

        // Assign the filters to the template.
        $templateMgr->assign($templateSearchFilters);

        // Special case: publication date filters.
        foreach(['From', 'To'] as $fromTo) {
            // [WIZDAM] Fixed deprecated string interpolation syntax ${var} -> {$var}
            $month = $request->getUserVar("date{$fromTo}Month");
            $day = $request->getUserVar("date{$fromTo}Day");
            $year = (int) $request->getUserVar("date{$fromTo}Year");
            
            if (empty($year)) {
                $date = '--';
                $hasEmptyFilters = true;
            } else {
                $defaultMonth = ($fromTo == 'From' ? 1 : 12);
                $defaultDay = ($fromTo == 'From' ? 1 : 31);
                $date = date(
                    'Y-m-d H:i:s',
                    mktime(
                        0, 0, 0, empty($month) ? $defaultMonth : (int) $month,
                        empty($day) ? $defaultDay : (int) $day, $year
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
        $templateMgr->assign(compact('hasEmptyFilters', 'hasActiveFilters'));

        // Assign the year range.
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $yearRange = $publishedArticleDao->getArticleYearRange($journalId);
        
        // [WIZDAM] Safety check for yearRange
        $yearRangeStart = isset($yearRange[1]) ? (int) substr($yearRange[1], 0, 4) : date('Y');
        $yearRangeEnd = isset($yearRange[0]) ? (int) substr($yearRange[0], 0, 4) : date('Y');
        
        $startYear = '-' . (date('Y') - $yearRangeStart);
        if ($yearRangeEnd >= date('Y')) {
            $endYear = '+' . ($yearRangeEnd - date('Y'));
        } else {
            $endYear = ($yearRangeEnd - (int)date('Y'));
        }
        $templateMgr->assign(compact('startYear', 'endYear'));

        // Assign journal options.
        if ($searchFilters['siteSearch']) {
            $journalDao = DAORegistry::getDAO('JournalDAO');
            $journals = $journalDao->getJournalTitles(true);
            $templateMgr->assign('journalOptions', ['' => AppLocale::Translate('search.allJournals')] + $journals);
        }
    }

    /**
     * Show the search form
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function search($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();

        // Get and transform active filters.
        $searchFilters = ArticleSearch::getSearchFilters($request);
        
        $keywords = ArticleSearch::getKeywordsFromSearchFilters($searchFilters);

        // Get the range info.
        $rangeInfo = $this->getRangeInfo('search');

        // Retrieve results.
        $error = '';
        $results = ArticleSearch::retrieveResults(
            $searchFilters['searchJournal'], $keywords, $error,
            $searchFilters['fromDate'], $searchFilters['toDate'],
            $rangeInfo
        );

        // Prepare and display the search template.
        $this->setupTemplate($request);
        
        $templateMgr = TemplateManager::getManager();
        $templateMgr->setCacheability(CACHEABILITY_NO_STORE);
        $templateMgr->assign('jsLocaleKeys', ['search.noKeywordError']);
        $this->_assignSearchFilters($request, $templateMgr, $searchFilters);
        $templateMgr->assign('results', $results);
        $templateMgr->assign('error', $error);
        
        $templateMgr->display('search/search.tpl');
    }

    /**
     * Show index of published articles by author.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function authors($args, $request = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();
        $this->validate();
        $this->setupTemplate($request, true);

        $journal = $request->getJournal();
        $authorDao = DAORegistry::getDAO('AuthorDAO');
        
        import('classes.user.UserDAO');
        $userDao = DAORegistry::getDAO('UserDAO');

        if (isset($args[0]) && $args[0] == 'view') {
            $firstName = trim((string) $request->getUserVar('firstName'));
            $middleName = trim((string) $request->getUserVar('middleName'));
            $lastName = trim((string) $request->getUserVar('lastName'));
            $affiliation = trim((string) $request->getUserVar('affiliation'));
            $country = trim((string) $request->getUserVar('country'));
            
            // --- LOGIC BARU: PREPARE PROFILE DATA ---
            
            // 1. Get Author ID (From Request or Rescue by Name)
            $authorId = $request->getUserVar('authorId');
            if (!$authorId && $firstName && $lastName) {
                $authorId = $authorDao->getAuthorIdByName($firstName, $lastName);
            }

            // 2. Get Author Basic Data
            $authorData = array('email' => null, 'url' => null, 'orcid' => null);
            if ($authorId) {
                $authorData = $authorDao->getAuthorAdditionalData($authorId);
            }

            // 3. User Matching & Profile Data
            $matchData = $userDao->getAuthorUserMatch(
                $firstName,
                $lastName,
                $authorData['email'],
                $authorData['orcid']
            );

            // 4. Process Affiliations
            $affiliationsArray = array();
            if (!empty($affiliation)) {
                $affiliationsArray = array_filter(explode("\n", $affiliation), 'trim');
            }

            // 5. Assign to Template (Menggantikan PHP Injection di TPL)
            $templateMgr = TemplateManager::getManager();
            
            $templateMgr->assign('authorId', $authorId);
            $templateMgr->assign('authorEmail', $authorData['email']);
            $templateMgr->assign('authorUrl', $authorData['url']);
            $templateMgr->assign('authorOrcid', $authorData['orcid']);
            
            // [PERBAIKAN 1]: Tambahkan ?? null dan fallback yang aman untuk halaman View Detail Penulis
            $matchedUser = $matchData['user'] ?? new User();
            $templateMgr->assign('matchedUserId', $matchData['userId'] ?? null);
            $templateMgr->assign('hasProfileImage', $matchData['hasImage'] ?? false);
            $templateMgr->assign('profileImageUrl', $matchData['imgUrl'] ?? '');
            $templateMgr->assign('user',            $matchedUser);
            
            $templateMgr->assign('affiliations', $affiliationsArray);
            
            // --- END LOGIC BARU: PREPARE PROFILE DATA ---

            $publishedArticles = $authorDao->getPublishedArticlesForAuthor(
                $journal ? $journal->getId() : null, 
                $firstName, 
                $middleName, 
                $lastName, 
                $affiliation, 
                $country
            );

            // Inject User Match Data
            foreach ($publishedArticles as $article) {
                $authors = $article->getAuthors();
                foreach ($authors as $author) {
                    $matchData = $userDao->getAuthorUserMatch(
                        $author->getFirstName(), 
                        $author->getLastName(), 
                        $author->getEmail(), 
                        $author->getData('orcid')
                    );

                    // [PERBAIKAN 2]: Terapkan !empty dan ?? untuk daftar artikel di dalam halaman View
                    if (!empty($matchData['found'])) {
                        $author->setData('id', $matchData['userId'] ?? null);
                        $author->setData('isVerifiedAuthor', true);
                        $author->setData('userGender', $matchData['gender'] ?? '');
                        $author->setData('hasProfileImage', $matchData['hasImage'] ?? false);
                        $author->setData('profileImageUrl', $matchData['imgUrl'] ?? '');
                        $author->setData('userInterests', $matchData['interests'] ?? '');
                        $author->setData('userSalutation', $matchData['salutation'] ?? '');
                        $author->setData('userPhone', $matchData['phone'] ?? '');
                        $author->setData('userFax', $matchData['fax'] ?? '');
                    } else {
                        $author->setData('isVerifiedAuthor', false);
                    }
                }
            }

            $journals = [];
            $issues = [];
            $sections = [];
            $issuesUnavailable = [];

            $issueDao = DAORegistry::getDAO('IssueDAO');
            $sectionDao = DAORegistry::getDAO('SectionDAO');
            $journalDao = DAORegistry::getDAO('JournalDAO');

            foreach ($publishedArticles as $article) {
                $articleId = $article->getId();
                $issueId = $article->getIssueId();
                $sectionId = $article->getSectionId();
                $journalId = $article->getJournalId();

                if (!isset($issues[$issueId])) {
                    import('classes.issue.IssueAction');
                    $issue = $issueDao->getIssueById($issueId);
                    $issues[$issueId] = $issue;
                    $issuesUnavailable[$issueId] = IssueAction::subscriptionRequired($issue) && (!IssueAction::subscribedUser($journal, $issueId, $articleId) && !IssueAction::subscribedDomain($journal, $issueId, $articleId));
                }
                if (!isset($journals[$journalId])) {
                    $journals[$journalId] = $journalDao->getById($journalId);
                }
                if (!isset($sections[$sectionId])) {
                    $sections[$sectionId] = $sectionDao->getSection($sectionId, $journalId, true);
                }
            }

            if (empty($publishedArticles)) {
                $request->redirect(null, $request->getRequestedPage());
            }

            $templateMgr = TemplateManager::getManager();
            $templateMgr->assign('publishedArticles', $publishedArticles);
            $templateMgr->assign('issues', $issues);
            $templateMgr->assign('issuesUnavailable', $issuesUnavailable);
            $templateMgr->assign('sections', $sections);
            $templateMgr->assign('journals', $journals);
            $templateMgr->assign('firstName', $firstName);
            $templateMgr->assign('middleName', $middleName);
            $templateMgr->assign('lastName', $lastName);
            $templateMgr->assign('affiliation', $affiliation);

            $countryDao = DAORegistry::getDAO('CountryDAO');
            $countryObj = $countryDao->getCountry($country);
            $templateMgr->assign('country', $countryObj);

            $templateMgr->display('search/authorDetails.tpl');

        } else {
            $searchInitial = trim((string) $request->getUserVar('searchInitial'));
            $searchInitial = preg_match('/^[A-Z]$/i', $searchInitial) ? strtoupper($searchInitial) : '';
            
            $rangeInfo = $this->getRangeInfo('authors');

            $authorsFactory = $authorDao->getAuthorsAlphabetizedByJournal(
                isset($journal) ? $journal->getId() : null,
                $searchInitial,
                $rangeInfo
            );
            
            $authors = $authorsFactory->toArray();

            foreach ($authors as $key => $author) {
                // 1. Fix Missing ID using Logic from AuthorDAO
                if (empty($author->getId())) {
                    $recoveredId = $authorDao->getAuthorIdByName(
                        $author->getFirstName(), 
                        $author->getLastName()
                    );
                    
                    if ($recoveredId) {
                        $author->setData('id', $recoveredId);
                    }
                }

                // 2. Check User Match
                $matchData = $userDao->getAuthorUserMatch(
                    $author->getFirstName(),
                    $author->getLastName(),
                    $author->getEmail(),
                    $author->getData('orcid')
                );

                // [PERBAIKAN 3]: Terapkan !empty dan ?? untuk daftar abjad penulis (Author Index)
                if (!empty($matchData['found'])) {
                    $author->setData('id', $matchData['userId'] ?? null); // Overwrite with UserID
                    $author->setData('isVerifiedAuthor', true);
                    $author->setData('userGender', $matchData['gender'] ?? '');
                    $author->setData('hasProfileImage', $matchData['hasImage'] ?? false);
                    $author->setData('profileImageUrl', $matchData['imgUrl'] ?? '');
                    $author->setData('userInterests', $matchData['interests'] ?? '');
                    $author->setData('userSalutation', $matchData['salutation'] ?? '');
                    $author->setData('userPhone', $matchData['phone'] ?? '');
                    $author->setData('userFax', $matchData['fax'] ?? '');
                } else {
                    $author->setData('isVerifiedAuthor', false);
                    // ID remains as set in Step 1
                }
                
                $authors[$key] = $author;
            }

            import('lib.pkp.classes.core.VirtualArrayIterator');
            $itemsPerPage = ($rangeInfo && $rangeInfo->isValid()) ? $rangeInfo->getCount() : max(1, count($authors));
            $authorsIterator = new VirtualArrayIterator($authors, $authorsFactory->getCount(), $authorsFactory->getPage(), $itemsPerPage);

            $templateMgr = TemplateManager::getManager();
            $templateMgr->assign('searchInitial', $request->getUserVar('searchInitial'));
            $templateMgr->assign('alphaList', explode(' ', __('common.alphaList')));
            $templateMgr->assign('authors', $authorsIterator); 
            
            $templateMgr->display('search/authorIndex.tpl');
        }
    }

    /**
     * Show index of published articles by title.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function titles($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();
        $this->setupTemplate($request, true);

        $journal = $request->getJournal();

        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');

        $rangeInfo = $this->getRangeInfo('search');

        $articleIds = $publishedArticleDao->getPublishedArticleIdsAlphabetizedByJournal(isset($journal) ? $journal->getId() : null);
        $totalResults = count($articleIds);
        $articleIds = array_slice($articleIds, $rangeInfo->getCount() * ($rangeInfo->getPage()-1), $rangeInfo->getCount());
        
        // --- [WIZDAM HOTFIX] Ekstraksi Data ---
        // Alih-alih langsung membungkus ke Iterator, kita tampung dulu formatResults
        $resultsArray = ArticleSearch::formatResults($articleIds);
        
        $sections = [];
        $issues = [];
        $journals = [];
        
        // Ekstrak objek section, issue, dan journal untuk mensuplai kebutuhan template Smarty
        // dan mencegah PHP 8.4 "Trying to access array offset on null"
        foreach ($resultsArray as $result) {
            if (isset($result['section'])) {
                $sections[$result['section']->getId()] = $result['section'];
            }
            if (isset($result['issue'])) {
                $issues[$result['issue']->getId()] = $result['issue'];
            }
            if (isset($result['journal'])) {
                $journals[$result['journal']->getId()] = $result['journal'];
            }
        }
        
        import('lib.pkp.classes.core.VirtualArrayIterator');
        // Masukkan array yang sudah diekstrak tadi ke Iterator
        $results = new VirtualArrayIterator($resultsArray, $totalResults, $rangeInfo->getPage(), $rangeInfo->getCount());

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('results', $results);
        
        // --- [WIZDAM HOTFIX] Injeksi Data ke Template ---
        $templateMgr->assign('sections', $sections);
        $templateMgr->assign('issues', $issues);
        $templateMgr->assign('journals', $journals);
        // ------------------------------------------------
        
        $templateMgr->display('search/titleIndex.tpl');
    }

    /**
     * Display categories.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function categories($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();
        $this->setupTemplate($request);

        $site = $request->getSite();
        $journal = $request->getJournal();

        $categoryDao = DAORegistry::getDAO('CategoryDAO');
        $cache = $categoryDao->getCache();

        if ($journal || !$site->getSetting('categoriesEnabled') || !$cache) {
            $request->redirect('index');
        }

        // Sort by category name
        // [WIZDAM] Replaced create_function with anonymous function
        uasort($cache, function($a, $b) {
            $catA = $a['category']; 
            $catB = $b['category']; 
            return strcasecmp($catA->getLocalizedName(), $catB->getLocalizedName());
        });

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('categories', $cache);
        $templateMgr->display('search/categories.tpl');
    }

    /**
     * Display category contents.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function category($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $categoryId = (int) array_shift($args);

        $this->validate();
        $this->setupTemplate($request, true, 'categories');

        $site = $request->getSite();
        $journal = $request->getJournal();

        $categoryDao = DAORegistry::getDAO('CategoryDAO');
        $cache = $categoryDao->getCache();

        if ($journal || !$site->getSetting('categoriesEnabled') || !$cache || !isset($cache[$categoryId])) {
            $request->redirect('index');
        }

        $journals = $cache[$categoryId]['journals'];
        $category = $cache[$categoryId]['category'];

        // Sort by journal name
        // [WIZDAM] Replaced create_function with anonymous function
        uasort($journals, function($a, $b) {
            return strcasecmp($a->getLocalizedTitle(), $b->getLocalizedTitle());
        });

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('journals', $journals);
        $templateMgr->assign('category', $category);
        $templateMgr->assign('journalFilesPath', $request->getBaseUrl() . '/' . Config::getVar('files', 'public_files_dir') . '/journals/');
        $templateMgr->display('search/category.tpl');
    }

    /**
     * Setup common template variables.
     * @param object|null $request PKPRequest
     * @param boolean $subclass set to true if caller is below this handler in the hierarchy
     * @param string $op Current operation (for breadcrumb construction)
     */
    public function setupTemplate($request = null, $subclass = false, $op = 'index') {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        parent::setupTemplate();
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('helpTopicId', 'user.searchAndBrowse');

        $opMap = [
            'index' => 'navigation.search',
            'categories' => 'navigation.categories'
        ];

        $templateMgr->assign('pageHierarchy',
            $subclass ? [[$request->url(null, 'search', $op), $opMap[$op]]]
                : []
        );

        $journal = $request->getJournal();
        if (!$journal || !$journal->getSetting('restrictSiteAccess')) {
            $templateMgr->setCacheability(CACHEABILITY_PUBLIC);
        }
    }
}
?>