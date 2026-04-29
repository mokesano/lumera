<?php
declare(strict_types=1);

/**
 * @file pages/rt/RTHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RTHandler
 * @ingroup pages_rt
 *
 * @brief Handle Reading Tools requests.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.rt.RT');

import('classes.rt.ojs.RTDAO');
import('classes.rt.ojs.JournalRT');

import('pages.article.ArticleHandler');

class RTHandler extends ArticleHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        // [WIZDAM FIX] Ambil objek Request via Singleton
        $request = Application::get()->getRequest();
        
        // [WIZDAM FIX] Teruskan $request ke parent constructor
        // Ini mencegah "Too few arguments" error karena ArticleHandler membutuhkannya
        parent::__construct($request);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function RTHandler($request = null) {
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
     * Display the article metadata
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function metadata($args, $request = null) {
        // [WIZDAM] Request Singleton
        $request = $request ?? Application::get()->getRequest();
        
        $router = $request->getRouter();
        $this->setupTemplate($request);
        $articleId = isset($args[0]) ? $args[0] : 0;
        $galleyId = isset($args[1]) ? (int) $args[1] : 0;
        $this->validate($request, $articleId, $galleyId);

        $journal = $router->getContext($request);
        $issue = $this->issue;
        $article = $this->article;

        $rtDao = DAORegistry::getDAO('RTDAO');
        $journalRt = $rtDao->getJournalRTByJournal($journal);

        if (!$journalRt || !$journalRt->getViewMetadata()) {
            $request->redirect(null, $router->getRequestedPage($request));
        }

        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $section = $sectionDao->getSection($article->getSectionId(), $journal->getId(), true);

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('articleId', $articleId);
        $templateMgr->assign('galleyId', $galleyId);
        $templateMgr->assign('journalRt', $journalRt);
        $templateMgr->assign('article', $article);
        $templateMgr->assign('issue', $issue);
        $templateMgr->assign('section', $section);
        $templateMgr->assign('journalSettings', $journal->getSettings());
        // consider public identifiers
        $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
        $templateMgr->assign('pubIdPlugins', $pubIdPlugins);
        $templateMgr->assign('ccLicenseBadge', Application::getCCLicenseBadge($article->getLicenseURL()));
        $templateMgr->display('rt/metadata.tpl');
    }

    /**
     * Display an RT search context
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function context($args, $request = null) {
        // [WIZDAM] Request Singleton
        $request = $request ?? Application::get()->getRequest();

        $router = $request->getRouter();
        $this->setupTemplate($request);
        $articleId = isset($args[0]) ? $args[0] : 0;
        $galleyId = isset($args[1]) ? (int) $args[1] : 0;
        $contextId = isset($args[2]) ? (int) $args[2] : 0;

        $this->validate($request, $articleId, $galleyId);
        $journal = $router->getContext($request);
        $article = $this->article;

        $rtDao = DAORegistry::getDAO('RTDAO');
        $journalRt = $rtDao->getJournalRTByJournal($journal);

        $context = $rtDao->getContext($contextId);
        $version = null;
        if ($context) $version = $rtDao->getVersion($context->getVersionId(), $journal->getId());

        if (!$context || !$version || !$journalRt || $journalRt->getVersion() == null || $journalRt->getVersion() !=  $context->getVersionId()) {
            $request->redirect(null, 'article', 'view', [$articleId, $galleyId]);
        }

        // Deal with the post and URL parameters for each search
        // so that the client browser can properly submit the forms
        // with a minimum of client-side processing.
        $searches = [];
        // Some searches use parameters other than the "default" for
        // the search (i.e. keywords, author name, etc). If additional
        // parameters are used, they should be displayed as part of the
        // form for ALL searches in that context.
        $searchParams = [];
        foreach ($context->getSearches() as $search) {
            $params = [];
            $searchParams += $this->_getParameterNames($search->getSearchUrl());
            if ($search->getSearchPost()) {
                $searchParams += $this->_getParameterNames($search->getSearchPost());
                $postParams = explode('&', $search->getSearchPost());
                foreach ($postParams as $param) {
                    // Split name and value from each parameter
                    $nameValue = explode('=', $param);
                    if (!isset($nameValue[0])) break;

                    $name = $nameValue[0];
                    $value = trim(isset($nameValue[1])?$nameValue[1]:'');
                    if (!empty($name)) $params[] = ['name' => $name, 'value' => $value];
                }
            }

            $search->postParams = $params;
            $searches[] = $search;
        }

        // Remove duplicate extra form elements and get their values
        $searchParams = array_unique($searchParams);
        $searchValues = [];

        foreach ($searchParams as $key => $param) switch ($param) {
            case 'author':
                $searchValues[$param] = $article->getAuthorString();
                break;
            case 'coverageGeo':
                $searchValues[$param] = $article->getLocalizedCoverageGeo();
                break;
            case 'title':
                $searchValues[$param] = $article->getLocalizedTitle();
                break;
            default:
                // UNKNOWN parameter! Remove it from the list.
                unset($searchParams[$key]);
                break;
        }

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('articleId', $articleId);
        $templateMgr->assign('galleyId', $galleyId);
        $templateMgr->assign('article', $article);
        $templateMgr->assign('version', $version);
        $templateMgr->assign('context', $context);
        $templateMgr->assign('searches', $searches);
        $templateMgr->assign('searchParams', $searchParams);
        $templateMgr->assign('searchValues', $searchValues);
        
        // [SECURITY FIX] Amankan 'defineTerm' (string teks) dengan trim() dan escape XSS dengan htmlspecialchars()
        $defineTermInput = trim((string) $request->getUserVar('defineTerm'));
        $templateMgr->assign('defineTerm', htmlspecialchars($defineTermInput, ENT_QUOTES, 'UTF-8'));
        
        $templateMgr->assign('keywords', explode(';', $article->getLocalizedSubject()));
        $templateMgr->assign('coverageGeo', $article->getLocalizedCoverageGeo());
        $templateMgr->assign('journalSettings', $journal->getSettings());
        $templateMgr->display('rt/context.tpl');
    }

    /**
     * Display citation information
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function captureCite($args = [], $request = null): void {
        if (!$request) $request = Application::get()->getRequest();
    
        // [WIZDAM] getFormats — paling awal, sebelum setupTemplate/validate
        // agar tidak ada output apapun yang mencemari JSON response
        if ((bool) $request->getUserVar('getFormats')) {
            $downloadOnly = [
                'ProCiteCitationPlugin',
                'RefManCitationPlugin',
                'EndNoteCitationPlugin',
            ];
            $citationPlugins = PluginRegistry::loadCategory('citationFormats');
            uasort($citationPlugins, function($a, $b) {
                return strcmp($a->getDisplayName(), $b->getDisplayName());
            });
            $formats = [];
            foreach ($citationPlugins as $plugin) {
                if (in_array(get_class($plugin), $downloadOnly, true)) continue;
                $formats[] = [
                    'plugin' => get_class($plugin),
                    'label'  => $plugin->getDisplayName(),
                ];
            }
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($formats);
            exit;
        }
    
        // Normal flow mulai di sini
        $router = $request->getRouter();
        $this->setupTemplate($request);
    
        $articleId = isset($args[0]) ? (int) $args[0] : 0;
        $galleyId  = isset($args[1]) ? (int) $args[1] : 0;
        $citeType  = isset($args[2]) ? trim((string) $args[2]) : null;
    
        $this->validate($request, $articleId, $galleyId);
        $journal = $router->getContext($request);
        $issue   = $this->issue;
        $article = $this->article;
    
        $rtDao     = DAORegistry::getDAO('RTDAO');
        $journalRt = $rtDao->getJournalRTByJournal($journal);
    
        if (!$journalRt || !$journalRt->getCaptureCite()) {
            $request->redirect(null, $router->getRequestedPage($request));
            return;
        }
    
        $citationPlugins = PluginRegistry::loadCategory('citationFormats');
        uasort($citationPlugins, function($a, $b) {
            return strcmp($a->getDisplayName(), $b->getDisplayName());
        });
    
        if ($citeType && isset($citationPlugins[$citeType])) {
            $citationPlugin = $citationPlugins[$citeType];
        } else {
            $firstKey       = array_key_first($citationPlugins);
            $citationPlugin = $firstKey ? $citationPlugins[$firstKey] : null;
        }
    
        if (!$citationPlugin) {
            $request->redirect(null, $router->getRequestedPage($request));
            return;
        }
    
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('articleId',       $articleId);
        $templateMgr->assign('galleyId',        $galleyId);
        $templateMgr->assign('citationPlugins', $citationPlugins);
        $templateMgr->assign('citationPlugin',  $citationPlugin);
        $templateMgr->assign('article',         $article);
        $templateMgr->assign('issue',           $issue);
        $templateMgr->assign('journal',         $journal);
    
        if ((bool) $request->getUserVar('isModal')) {
            echo $citationPlugin->fetchCitation($article, $issue, $journal);
            exit;
        }
    
        $citationPlugin->displayCitation($article, $issue, $journal);
    }

    /**
     * Display a printer-friendly version of the article
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function printerFriendly($args, $request = null) {
        // [WIZDAM] Request Singleton
        $request = $request ?? Application::get()->getRequest();

        $router = $request->getRouter();
        $articleId = isset($args[0]) ? $args[0] : 0;
        $galleyId = isset($args[1]) ? $args[1] : 0;

        $this->validate($request, $articleId, $galleyId);
        $journal = $router->getContext($request);
        $issue = $this->issue;
        $article = $this->article;

        $this->setupTemplate($request);

        $rtDao = DAORegistry::getDAO('RTDAO');
        $journalRt = $rtDao->getJournalRTByJournal($journal);

        if (!$journalRt || !$journalRt->getPrinterFriendly()) {
            $request->redirect(null, $router->getRequestedPage($request));
        }

        $articleGalleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        if ($journal->getSetting('enablePublicGalleyId')) {
            $galley = $articleGalleyDao->getGalleyByBestGalleyId($galleyId, $article->getId());
        } else {
            $galley = $articleGalleyDao->getGalley($galleyId, $article->getId());
        }

        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $section = $sectionDao->getSection($article->getSectionId(), $journal->getId(), true);

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('galley', $galley);
        $templateMgr->assign('article', $article);
        $templateMgr->assign('section', $section);
        $templateMgr->assign('issue', $issue);
        $templateMgr->assign('journal', $journal);
        $templateMgr->assign('articleId', $articleId);
        $templateMgr->assign('galleyId', $galleyId);

        // Use the article's CSS file, if set.
        if ($galley && $galley->isHTMLGalley() && $styleFile = $galley->getStyleFile()) {
            $templateMgr->addStyleSheet($router->url($request, null, 'article', 'viewFile', [
                $article->getId(),
                $galley->getBestGalleyId($journal),
                $styleFile->getFileId()
            ]));
        }

        $templateMgr->display('rt/printerFriendly.tpl');
    }

    /**
     * Display the "Email Colleague" form
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function emailColleague($args, $request = null) {
        // [WIZDAM] Request Singleton
        $request = $request ?? Application::get()->getRequest();

        $router = $request->getRouter();
        $this->setupTemplate($request);
        $articleId = isset($args[0]) ? $args[0] : 0;
        $galleyId = isset($args[1]) ? (int) $args[1] : 0;

        $this->validate($request, $articleId, $galleyId);
        $journal = $router->getContext($request);
        $issue = $this->issue;
        $article = $this->article;

        $rtDao = DAORegistry::getDAO('RTDAO');
        $journalRt = $rtDao->getJournalRTByJournal($journal);
        $user = $request->getUser();

        if (!$journalRt || !$journalRt->getEmailOthers() || !$user) {
            $request->redirect(null, $router->getRequestedPage($request));
        }

        import('classes.mail.MailTemplate');
        $email = new MailTemplate('EMAIL_LINK');

        // [SECURITY FIX] Amankan 'send' sebagai flag boolean with (int) trim()
        $sendFlag = (int) trim((string) $request->getUserVar('send'));
        
        if ($sendFlag && !$email->hasErrors()) {
            $email->send();

            $templateMgr = TemplateManager::getManager();
            $templateMgr->display('rt/sent.tpl');
        } else {
            // [SECURITY FIX] Amankan 'continued' sebagai flag boolean dengan (int) trim()
            $continuedFlag = (int) trim((string) $request->getUserVar('continued'));
            
            if (!$continuedFlag) {
                $primaryAuthor = $article->getAuthors();
                $primaryAuthor = $primaryAuthor[0];

                $email->setSubject('[' . $journal->getLocalizedSetting('initials') . '] ' . strip_tags($article->getLocalizedTitle()));
                $email->assignParams([
                    // ... (parameter email)
                    'articleTitle' => strip_tags($article->getLocalizedTitle()),
                    'volume' => $issue?$issue->getVolume():null,
                    'number' => $issue?$issue->getNumber():null,
                    'year' => $issue?$issue->getYear():null,
                    'authorName' => $primaryAuthor->getFullName(),
                    'articleUrl' => $router->url($request, null, 'article', 'view', [$article->getBestArticleId()])
                ]);
            }
            $email->displayEditForm($router->url($request, null, null, 'emailColleague', [$articleId, $galleyId]), null, 'rt/email.tpl', ['op' => 'emailColleague']);
        }
    }

    /**
     * Display the "email author" form
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function emailAuthor($args, $request = null) {
        // [WIZDAM] Request Singleton
        $request = $request ?? Application::get()->getRequest();

        $router = $request->getRouter();
        $this->setupTemplate($request);
        $articleId = isset($args[0]) ? $args[0] : 0;
        $galleyId = isset($args[1]) ? (int) $args[1] : 0;

        $this->validate($request, $articleId, $galleyId);
        $journal = $router->getContext($request);
        $article = $this->article;

        $rtDao = DAORegistry::getDAO('RTDAO');
        $journalRt = $rtDao->getJournalRTByJournal($journal);
        $user = $request->getUser();

        if (!$journalRt || !$journalRt->getEmailAuthor() || !$user) {
            $request->redirect(null, $router->getRequestedPage($request));
        }

        import('classes.mail.MailTemplate');
        $email = new MailTemplate();
        $email->setAddressFieldsEnabled(false);

        // [SECURITY FIX] Amankan 'send' sebagai flag boolean dengan (int) trim()
        $sendFlag = (int) trim((string) $request->getUserVar('send'));
        
        if ($sendFlag && !$email->hasErrors()) {
            $email->send();

            $templateMgr = TemplateManager::getManager();
            $templateMgr->display('rt/sent.tpl');
        } else {
            // [SECURITY FIX] Amankan 'continued' sebagai flag boolean dengan (int) trim()
            $continuedFlag = (int) trim((string) $request->getUserVar('continued'));
            
            if (!$continuedFlag) {
                $primaryAuthor = $article->getAuthors();
                $primaryAuthor = $primaryAuthor[0];

                $email->setSubject('[' . $journal->getLocalizedSetting('initials') . '] ' . strip_tags($article->getLocalizedTitle()));
                $email->assignParams([
                    // ... (parameter email)
                    'articleTitle' => strip_tags($article->getLocalizedTitle()),
                    'volume' => $issue?$issue->getVolume():null,
                    'number' => $issue?$issue->getNumber():null,
                    'year' => $issue?$issue->getYear():null,
                    'authorName' => $primaryAuthor->getFullName(),
                    'articleUrl' => $router->url($request, null, 'article', 'view', [$article->getBestArticleId()])
                ]);
            }
            $email->displayEditForm($router->url($request, null, null, 'emailColleague', [$articleId, $galleyId]), null, 'rt/email.tpl', ['op' => 'emailColleague']);
        }
    }

    /**
     * Display a list of supplementary files
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function suppFiles($args, $request = null) {
        // [WIZDAM] Request Singleton
        $request = $request ?? Application::get()->getRequest();

        $router = $request->getRouter();
        $this->setupTemplate($request);
        $articleId = isset($args[0]) ? $args[0] : 0;
        $galleyId = isset($args[1]) ? (int) $args[1] : 0;

        $this->validate($request, $articleId, $galleyId);
        $journal = $router->getContext($request);
        $article = $this->article;

        $rtDao = DAORegistry::getDAO('RTDAO');
        $journalRt = $rtDao->getJournalRTByJournal($journal);

        if (!$journalRt || !$journalRt->getSupplementaryFiles()) {
            $request->redirect(null, $router->getRequestedPage($request));
        }

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('articleId', $articleId);
        $templateMgr->assign('galleyId', $galleyId);
        $templateMgr->assign('journalRt', $journalRt);
        $templateMgr->assign('article', $article);
        $templateMgr->assign('journalSettings', $journal->getSettings());
        $templateMgr->display('rt/suppFiles.tpl');
    }

    /**
     * Display the metadata of a supplementary file
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function suppFileMetadata($args, $request = null) {
        // [WIZDAM] Request Singleton
        $request = $request ?? Application::get()->getRequest();

        $router = $request->getRouter();
        $this->setupTemplate($request);
        AppLocale::requireComponents(LOCALE_COMPONENT_APP_AUTHOR);
        $articleId = isset($args[0]) ? $args[0] : 0;
        $galleyId = isset($args[1]) ? (int) $args[1] : 0;
        $suppFileId = isset($args[2]) ? (int) $args[2] : 0;

        $this->validate($request, $articleId, $galleyId);
        $journal = $router->getContext($request);
        $article = $this->article;

        $rtDao = DAORegistry::getDAO('RTDAO');
        $journalRt = $rtDao->getJournalRTByJournal($journal);

        $suppFileDao = DAORegistry::getDAO('SuppFileDAO');
        $suppFile = $suppFileDao->getSuppFile($suppFileId, $article->getId());

        if (!$journalRt || !$journalRt->getSupplementaryFiles() || !$suppFile) {
            $request->redirect(null, $router->getRequestedPage($request));
        }

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('articleId', $articleId);
        $templateMgr->assign('galleyId', $galleyId);
        $templateMgr->assign('suppFile', $suppFile);
        $templateMgr->assign('journalRt', $journalRt);
        $templateMgr->assign('issue', $this->issue);
        $templateMgr->assign('article', $article);
        $templateMgr->assign('journalSettings', $journal->getSettings());
        // consider public identifiers
        $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
        $templateMgr->assign('pubIdPlugins', $pubIdPlugins);
        $templateMgr->display('rt/suppFileView.tpl');
    }

    /**
     * Display the "finding references" search engine list
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function findingReferences($args, $request = null) {
        // [WIZDAM] Request Singleton
        $request = $request ?? Application::get()->getRequest();

        $router = $request->getRouter();
        $this->setupTemplate($request);
        $articleId = isset($args[0]) ? $args[0] : 0;
        $galleyId = isset($args[1]) ? (int) $args[1] : 0;

        $this->validate($request, $articleId, $galleyId);
        $journal = $router->getContext($request);
        $article = $this->article;

        $rtDao = DAORegistry::getDAO('RTDAO');
        $journalRt = $rtDao->getJournalRTByJournal($journal);

        if (!$journalRt || !$journalRt->getFindingReferences()) {
            $request->redirect(null, $router->getRequestedPage($request));
        }

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('articleId', $articleId);
        $templateMgr->assign('galleyId', $galleyId);
        $templateMgr->assign('journalRt', $journalRt);
        $templateMgr->assign('article', $article);
        $templateMgr->display('rt/findingReferences.tpl');
    }

    /**
     * Get parameter values: Used internally for RT searches
     * @param string $value
     * @return array
     */
    public function _getParameterNames($value) {
        $matches = null;
        PKPString::regexp_match_all('/\{\$([a-zA-Z0-9]+)\}/', $value, $matches);
        // Remove the entire string from the matches list
        return $matches[1];
    }
}
?>