<?php
declare(strict_types=1);

/**
 * @file pages/information/InformationHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InformationHandler
 * @ingroup pages_information
 *
 * @brief Display journal information.
 */


import('classes.handler.Handler');

class InformationHandler extends Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function InformationHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Display the information page for the journal.
     * * @param array $args
     * @param mixed $request
     */
    public function index(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $this->validate(null, $request);
        $this->setupTemplate();

        $journal = $request->getJournal();

        if ($journal === null) {
            $request->redirect('index');
            return;
        }

        $page = $args[0] ?? null;

        switch($page) {
            case 'readers':
                $content = $journal->getLocalizedSetting('readerInformation');
                $pageTitle = 'navigation.infoForReaders.long';
                $pageCrumbTitle = 'navigation.infoForReaders';
                break;
            case 'authors':
                $content = $journal->getLocalizedSetting('authorInformation');
                $pageTitle = 'navigation.infoForAuthors.long';
                $pageCrumbTitle = 'navigation.infoForAuthors';
                break;
            case 'librarians':
                $content = $journal->getLocalizedSetting('librarianInformation');
                $pageTitle = 'navigation.infoForLibrarians.long';
                $pageCrumbTitle = 'navigation.infoForLibrarians';
                break;
            case 'competingInterestGuidelines':
                $content = $journal->getLocalizedSetting('competingInterestGuidelines');
                $pageTitle = 'navigation.competingInterestGuidelines';
                $pageCrumbTitle = 'navigation.competingInterestGuidelines';
                break;
            case 'sampleCopyrightWording':
                AppLocale::requireComponents(LOCALE_COMPONENT_APP_MANAGER);
                $content = __('manager.setup.authorCopyrightNotice.sample');
                $pageTitle = 'manager.setup.copyrightNotice';
                $pageCrumbTitle = 'manager.setup.copyrightNotice';
                break;
            default:
                $request->redirect($journal->getPath());
                return;
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('pageCrumbTitle', $pageCrumbTitle);
        $templateMgr->assign('pageTitle', $pageTitle);
        $templateMgr->assign('content', $content);
        $templateMgr->display('information/information.tpl');
    }

    /**
     * Shortcut to readers info
     * @param array $args
     * @param mixed $request
     */
    public function readers(array $args = [], $request = null) {
        $this->index(['readers'], $request);
    }

    /**
     * Shortcut to authors info
     * @param array $args
     * @param mixed $request
     */
    public function authors(array $args = [], $request = null) {
        $this->index(['authors'], $request);
    }

    /**
     * Shortcut to librarians info
     * @param array $args
     * @param mixed $request
     */
    public function librarians(array $args = [], $request = null) {
        $this->index(['librarians'], $request);
    }

    /**
     * Shortcut to competing interest guidelines
     * @param array $args
     * @param mixed $request
     */
    public function competingInterestGuidelines(array $args = [], $request = null) {
        $this->index(['competingInterestGuidelines'], $request);
    }

    /**
     * Shortcut to sample copyright wording
     * @param array $args
     * @param mixed $request
     */
    public function sampleCopyrightWording(array $args = [], $request = null) {
        $this->index(['sampleCopyrightWording'], $request);
    }

    /**
     * Initialize the template.
     * @param mixed $request
     * @return void
     */
    public function setupTemplate($request = NULL) {
        parent::setupTemplate();
        
        // [WIZDAM] Fetch request strictly for internal usage
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        
        $templateMgr = TemplateManager::getManager($request);
        
        if (!$journal || !$journal->getSetting('restrictSiteAccess')) {
            $templateMgr->setCacheability(CACHEABILITY_PUBLIC);
        }
    }
}
?>