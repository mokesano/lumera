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
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $journal = $request->getJournal();
        $templateMgr = TemplateManager::getManager($request);

        if (!$journal) {
            /** @var JournalDAO $journalDao */
            $journalDao = DAORegistry::getDAO('JournalDAO');
            $journals = $journalDao->getJournals(true);
            $templateMgr->assign('journals', $journals);
            $templateMgr->display('gateway/lockss.tpl');
            return;
        }

        if (!$journal->getSetting('enableLockss')) {
            $request->redirect(null, 'index');
            return;
        }

        $yearInput = $request->getUserVar('year');
        $targetYear = $yearInput !== null ? (int) $yearInput : null;
        $journalId = (int) $journal->getId();

        /** @var IssueDAO $issueDao */
        $issueDao = DAORegistry::getDAO('IssueDAO');

        $result = null;
        $showInfo = false;

        if ($targetYear !== null) {
            $result = $issueDao->retrieve(
                'SELECT * FROM issues WHERE journal_id = ? AND year = ? AND published = 1 ORDER BY current DESC, year ASC, volume ASC, number ASC',
                [$journalId, $targetYear]
            );

            if ($result->EOF) {
                $targetYear = null;
                $showInfo = true;
            }
        }

        if ($targetYear === null) {
            $showInfo = true;
            $latestYearResult = $issueDao->retrieve(
                'SELECT MAX(year) AS max_year FROM issues WHERE journal_id = ? AND published = 1',
                [$journalId]
            );
            
            if (!$latestYearResult->EOF) {
                $row = $latestYearResult->getRowAssoc(false);
                $targetYear = (int) ($row['max_year'] ?? 0);
            } else {
                $targetYear = 0;
            }
            $latestYearResult->Close();

            $result = $issueDao->retrieve(
                'SELECT * FROM issues WHERE journal_id = ? AND year = ? AND published = 1 ORDER BY current DESC, year ASC, volume ASC, number ASC',
                [$journalId, $targetYear]
            );
        }

        $prevYear = null;
        $nextYear = null;
        if ($targetYear > 0) {
            $rangeResult = $issueDao->retrieve(
                'SELECT 
                    MAX(CASE WHEN year < ? THEN year END) AS prev_year,
                    MIN(CASE WHEN year > ? THEN year END) AS next_year
                 FROM issues 
                 WHERE journal_id = ? AND published = 1',
                [$targetYear, $targetYear, $journalId]
            );

            if (!$rangeResult->EOF) {
                $row = $rangeResult->getRowAssoc(false);
                if (isset($row['prev_year']) && $row['prev_year'] !== null) {
                    $prevYear = (int) $row['prev_year'];
                }
                if (isset($row['next_year']) && $row['next_year'] !== null) {
                    $nextYear = (int) $row['next_year'];
                }
            }
            $rangeResult->Close();
        }

        $issues = new DAOResultFactory($result, $issueDao, '_returnIssueFromRow');

        $templateMgr->assign([
            'journal'    => $journal,
            'issues'     => $issues,
            'year'       => $targetYear,
            'prevYear'   => $prevYear,
            'nextYear'   => $nextYear,
            'showInfo'   => $showInfo
        ]);

        $locales = $journal->getSupportedLocaleNames();
        if (empty($locales)) {
            $localeNames = AppLocale::getAllLocales();
            $primaryLocale = AppLocale::getPrimaryLocale();
            $locales = [$primaryLocale => $localeNames[$primaryLocale] ?? 'Unknown'];
        }
        $templateMgr->assign('locales', $locales);

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