<?php
declare(strict_types=1);

/**
 * @file pages/gateway/GatewayHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GatewayHandler
 * @ingroup pages_gateway
 *
 * @brief Handle external gateway requests.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.handler.Handler');

class GatewayHandler extends Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function GatewayHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::GatewayHandler(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Index handler.
     * @param array $args
     * @param PKPRequest $request
     */
    public function index($args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();
        
        $request->redirect(null, 'index');
    }

    /**
     * Handle LOCKSS requests.
     * @param array $args
     * @param PKPRequest $request
     */
    public function lockss($args, $request) {
        $this->validate();
        $this->setupTemplate();

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journal = $request->getJournal();
        $templateMgr = TemplateManager::getManager();

        if ($journal != null) {
            if (!$journal->getSetting('enableLockss')) {
                $request->redirect(null, 'index');
            }

            $year = (int) $request->getUserVar('year');

            $issueDao = DAORegistry::getDAO('IssueDAO');

            // FIXME Should probably go in IssueDAO or a subclass
            // [WIZDAM] Logic adjusted: checking if year was provided via user var (which cast to 0 if missing/invalid string)
            // Original code checked `isset($year)` on a variable that came from `$request->getUserVar`. 
            // In strict PHP 8, getUserVar returns null if missing. Casting null to int gives 0.
            // If the user actually passed "year=2023", it's 2023. If missing, it's 0.
            // Original logic `if (isset($year))` is always true for local variable unless unset.
            // We should check if the original input was present.
            
            $yearInput = $request->getUserVar('year');
            
            if ($yearInput !== null) {
                $year = (int) $yearInput;
                $result = $issueDao->retrieve(
                    'SELECT * FROM issues WHERE journal_id = ? AND year = ? AND published = 1 ORDER BY current DESC, year ASC, volume ASC, number ASC',
                    [(int)$journal->getId(), $year]
                );
                if ($result->RecordCount() == 0) {
                    $yearInput = null; // Mark as unset for logic flow below
                }
            }

            if ($yearInput === null) {
                $showInfo = true;
                $result = $issueDao->retrieve(
                    'SELECT MAX(year) FROM issues WHERE journal_id = ? AND published = 1',
                    [(int)$journal->getId()]
                );
                list($year) = $result->fields;
                $result = $issueDao->retrieve(
                    'SELECT * FROM issues WHERE journal_id = ? AND year = ? AND published = 1 ORDER BY current DESC, year ASC, volume ASC, number ASC',
                    [(int)$journal->getId(), $year]
                );
            } else {
                $showInfo = false;
            }

            $issues = new DAOResultFactory($result, $issueDao, '_returnIssueFromRow');

            $prevYear = null;
            $nextYear = null;
            if ($yearInput !== null || isset($year)) {
                $result = $issueDao->retrieve(
                    'SELECT MAX(year) FROM issues WHERE journal_id = ? AND published = 1 AND year < ?',
                    [(int)$journal->getId(), $year]
                );
                list($prevYear) = $result->fields;

                $result = $issueDao->retrieve(
                    'SELECT MIN(year) FROM issues WHERE journal_id = ? AND published = 1 AND year > ?',
                    [(int)$journal->getId(), $year]
                );
                list($nextYear) = $result->fields;
            }

            // [WIZDAM] Removed assign_by_ref
            $templateMgr->assign('journal', $journal);
            $templateMgr->assign('issues', $issues);
            $templateMgr->assign('year', $year);
            $templateMgr->assign('prevYear', $prevYear);
            $templateMgr->assign('nextYear', $nextYear);
            $templateMgr->assign('showInfo', $showInfo);

            $locales = $journal->getSupportedLocaleNames();
            if (!isset($locales) || empty($locales)) {
                $localeNames = AppLocale::getAllLocales();
                $primaryLocale = AppLocale::getPrimaryLocale();
                $locales = [$primaryLocale => $localeNames[$primaryLocale]];
            }
            $templateMgr->assign('locales', $locales);

        } else {
            $journalDao = DAORegistry::getDAO('JournalDAO');
            $journals = $journalDao->getJournals(true);
            $templateMgr->assign('journals', $journals);
        }

        $templateMgr->display('gateway/lockss.tpl');
    }

    /**
     * Handle requests for gateway plugins.
     * @param array $args
     * @param PKPRequest $request
     */
    public function plugin($args, $request) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();
        
        $pluginName = array_shift($args);

        $plugins = PluginRegistry::loadCategory('gateways');
        if (isset($pluginName) && isset($plugins[$pluginName])) {
            $plugin = $plugins[$pluginName];
            if (!$plugin->fetch($args, $request)) {
                $request->redirect(null, 'index');
            }
        } else {
            $request->redirect(null, 'index');
        }
    }
}
?>