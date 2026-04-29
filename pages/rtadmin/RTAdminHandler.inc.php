<?php
declare(strict_types=1);

/**
 * @file pages/rtadmin/RTAdminHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RTAdminHandler
 * @ingroup pages_rtadmin
 *
 * @brief Handle Reading Tools administration requests.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 * [WIZDAM CLEANUP] Amputasi total fitur "Validate URLs" dan cURL usang.
 */

import('classes.rt.ojs.JournalRTAdmin');
import('classes.handler.Handler');

class RTAdminHandler extends Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();

        $this->addCheck(new HandlerValidatorJournal($this));
        $this->addCheck(new HandlerValidatorRoles($this, true, null, null, [ROLE_ID_SITE_ADMIN, ROLE_ID_JOURNAL_MANAGER]));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function RTAdminHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::RTAdminHandler(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Index page
     * @param array $args
     * @param PKPRequest $request
     */
    public function index($args = [], $request = null) {
        $this->validate();
        
        if (!$request) $request = Application::get()->getRequest();
        
        $journal = $request->getJournal();
        $user = $request->getUser();
        
        if ($journal) {
            $rtDao = DAORegistry::getDAO('RTDAO');
            $rt = $rtDao->getJournalRTByJournal($journal);
            $version = null;
            if (isset($rt)) {
                $version = $rtDao->getVersion($rt->getVersion(), $journal->getId());
            }

            $this->setupTemplate();
            $templateMgr = TemplateManager::getManager();
            $templateMgr->assign('helpTopicId', 'journal.managementPages.readingTools');
            $templateMgr->assign('versionTitle', isset($version) ? $version->getTitle() : null);
            $templateMgr->assign('enabled', $rt->getEnabled());

            $templateMgr->display('rtadmin/index.tpl');
        } elseif ($user) {
            $journalDao = DAORegistry::getDAO('JournalDAO');
            $roleDao = DAORegistry::getDAO('RoleDAO');

            $journals = [];
            $allJournals = $journalDao->getJournals();
            $allJournals = $allJournals->toArray();

            foreach ($allJournals as $journal) {
                if ($roleDao->userHasRole($journal->getId(), $user->getId(), ROLE_ID_JOURNAL_MANAGER)) {
                    $journals[] = $journal;
                }
            }

            $this->setupTemplate();
            $templateMgr = TemplateManager::getManager();
            $templateMgr->assign('journals', $journals);
            $templateMgr->assign('helpTopicId', 'journal.managementPages.readingTools');
            $templateMgr->display('rtadmin/journals.tpl');
        } else {
            Validation::redirectLogin();
        }
    }

    /**
     * Setup common template variables.
     * @param bool $subclass
     * @param object $version
     * @param object $context
     * @param object $search
     */
    public function setupTemplate($subclass = false, $version = null, $context = null, $search = null) {
        parent::setupTemplate();
        AppLocale::requireComponents(
            LOCALE_COMPONENT_CORE_READER, 
            LOCALE_COMPONENT_APP_MANAGER
        );
        $templateMgr = TemplateManager::getManager();
        
        $request = Application::get()->getRequest();

        $pageHierarchy = [
            [$request->url(null, 'user'), 'navigation.user'], 
            [$request->url(null, 'manager'), 'manager.journalManagement']
        ];

        if ($subclass) $pageHierarchy[] = [$request->url(null, 'rtadmin'), 'rt.readingTools'];

        if ($version) {
            $pageHierarchy[] = [$request->url(null, 'rtadmin', 'versions'), 'rt.versions'];
            $pageHierarchy[] = [$request->url(null, 'rtadmin', 'editVersion', $version->getVersionId()), $version->getTitle(), true];
            if ($context) {
                $pageHierarchy[] = [$request->url(null, 'rtadmin', 'contexts', $version->getVersionId()), 'rt.contexts'];
                $pageHierarchy[] = [$request->url(null, 'rtadmin', 'editContext', [$version->getVersionId(), $context->getContextId()]), $context->getAbbrev(), true];
                if ($search) {
                    $pageHierarchy[] = [$request->url(null, 'rtadmin', 'searches', [$version->getVersionId(), $context->getContextId()]), 'rt.searches'];
                    $pageHierarchy[] = [$request->url(null, 'rtadmin', 'editSearch', [$version->getVersionId(), $context->getContextId(), $search->getSearchId()]), $search->getTitle(), true];
                }
            }
        }
        $templateMgr->assign('pageHierarchy', $pageHierarchy);
    }
}
?>