<?php
declare(strict_types=1);

/**
 * @file plugins/gateways/resolver/ResolverPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ResolverPlugin
 * @ingroup plugins_gateways_resolver
 *
 * @brief Simple resolver gateway plugin.
 */

import('classes.plugins.GatewayPlugin');

class ResolverPlugin extends GatewayPlugin {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function ResolverPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Called as a plugin is registered to the registry.
     * @param string $category
     * @param string $path
     * @return bool
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        $this->addLocaleData();
        return $success;
    }

    /**
     * Get the name of the settings file to be installed on new journal creation.
     * @return string|null
     */
    public function getContextSpecificPluginSettingsFile(): ?string {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * Get the name of this plugin. The name must be unique within its category.
     * @return string
     */
    public function getName(): string {
        return 'ResolverPlugin';
    }

    /**
     * Get the display name of this plugin.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.gateways.resolver.displayName');
    }

    /**
     * Get the description of this plugin.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.gateways.resolver.description');
    }

    /**
     * Handle fetch requests for this plugin.
     * @param array $args
     * @param mixed $request
     * @return bool|void
     */
    public function fetch($args, $request) {
        if (!$this->getEnabled()) {
            return false;
        }

        $scheme = array_shift($args);
        switch ($scheme) {
            case 'doi':
                $doi = implode('/', $args);
                $journal = $request->getJournal();
                /** @var PublishedArticleDAO $publishedArticleDao */
                $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
                $article = $publishedArticleDao->getPublishedArticleByPubId('doi', $doi, $journal ? (int) $journal->getId() : null);
                if ($article instanceof PublishedArticle) {
                    $request->redirect(null, 'article', 'view', $article->getBestArticleId());
                }
                break;
                
            case 'vnp': // Volume, number, page
            case 'ynp': // Volume, number, year, page
                // This can only be used from within a journal context
                $journal = $request->getJournal();
                if (!$journal) {
                    break;
                }

                $volume = null;
                $year = null;

                if ($scheme === 'vnp') {
                    $volume = (int) array_shift($args);
                } elseif ($scheme === 'ynp') {
                    $year = (int) array_shift($args);
                }
                $number = (string) array_shift($args);
                $page = (int) array_shift($args);

                /** @var IssueDAO $issueDao */
                $issueDao = DAORegistry::getDAO('IssueDAO');
                $issues = $issueDao->getPublishedIssuesByNumber((int) $journal->getId(), $volume, $number, $year);

                // Ensure only one issue matched, and fetch it.
                $issue = $issues->next();
                if (!$issue || $issues->next()) {
                    break;
                }

                /** @var PublishedArticleDAO $publishedArticleDao */
                $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
                $articles = $publishedArticleDao->getPublishedArticles((int) $issue->getId());
                
                foreach ($articles as $article) {
                    $matches = null;
                    if (PKPString::regexp_match_get('/^[Pp][Pp]?[.]?[ ]?(\d+)$/', (string) $article->getPages(), $matches)) {
                        if (is_array($matches) && isset($matches[1])) {
                            $matchedPage = (int) $matches[1];
                            if ($page === $matchedPage) {
                                $request->redirect(null, 'article', 'view', $article->getBestArticleId());
                            }
                        }
                    }
                    if (PKPString::regexp_match_get('/^[Pp][Pp]?[.]?[ ]?(\d+)[ ]?-[ ]?([Pp][Pp]?[.]?[ ]?)?(\d+)$/', (string) $article->getPages(), $matches)) {
                        if (is_array($matches) && isset($matches[1], $matches[3])) {
                            $matchedPageFrom = (int) $matches[1];
                            $matchedPageTo = (int) $matches[3];
                            if ($page >= $matchedPageFrom && ($page < $matchedPageTo || ($page === $matchedPageTo && $matchedPageFrom === $matchedPageTo))) {
                                $request->redirect(null, 'article', 'view', $article->getBestArticleId());
                            }
                        }
                    }
                }
                break;
        }

        // Failure.
        header("HTTP/1.0 500 Internal Server Error");
        $templateMgr = TemplateManager::getManager();
        AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON);
        $templateMgr->assign('message', 'plugins.gateways.resolver.errors.errorMessage');
        $templateMgr->display('common/message.tpl');
        exit;
    }

    /**
     * Sanitize string for CSV output.
     * @param mixed $string
     * @return string
     */
    public function sanitize($string): string {
        return str_replace("\t", " ", strip_tags((string) $string));
    }

    /**
     * Export holdings data.
     */
    public function exportHoldings() {
        // Lumera Singleton Fallback
        $request = Application::get()->getRequest();
        
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        /** @var IssueDAO $issueDao */
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $journals = $journalDao->getJournals(true);

        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename=holdings.txt');
        echo "title\tissn\te_issn\tstart_date\tend_date\tembargo_months\tembargo_days\tjournal_url\tvol_start\tvol_end\tiss_start\tiss_end\n";
        
        while ($journal = $journals->next()) {
            $issues = $issueDao->getPublishedIssues((int) $journal->getId());
            $startDate = null;
            $endDate = null;
            $startNumber = null;
            $endNumber = null;
            $startVolume = null;
            $endVolume = null;
            
            while ($issue = $issues->next()) {
                $datePublished = $issue->getDatePublished();
                $timestamp = $datePublished !== null ? strtotime((string) $datePublished) : null;
                
                if ($startDate === null || ($timestamp !== null && $startDate > $timestamp)) {
                    $startDate = $timestamp;
                }
                if ($endDate === null || ($timestamp !== null && $endDate < $timestamp)) {
                    $endDate = $timestamp;
                }
                
                $volume = (int) $issue->getVolume();
                if ($startVolume === null || $startVolume > $volume) {
                    $startVolume = $volume;
                }
                if ($endVolume === null || $endVolume < $volume) {
                    $endVolume = $volume;
                }
                
                $number = (int) $issue->getNumber();
                if ($startNumber === null || $startNumber > $number) {
                    $startNumber = $number;
                }
                if ($endNumber === null || $endNumber < $number) {
                    $endNumber = $number;
                }
            }

            echo $this->sanitize($journal->getLocalizedTitle()) . "\t";
            echo $this->sanitize((string) $journal->getSetting('printIssn')) . "\t";
            echo $this->sanitize((string) $journal->getSetting('onlineIssn')) . "\t";
            echo $this->sanitize($startDate === null ? '' : date('Y-m-d', $startDate)) . "\t"; // start_date
            echo $this->sanitize($endDate === null ? '' : date('Y-m-d', $endDate)) . "\t"; // end_date
            echo $this->sanitize('') . "\t"; // embargo_months
            echo $this->sanitize('') . "\t"; // embargo_days
            echo $request->url($journal->getPath()) . "\t"; // journal_url
            echo $this->sanitize((string) $startVolume) . "\t"; // vol_start
            echo $this->sanitize((string) $endVolume) . "\t"; // vol_end
            echo $this->sanitize((string) $startNumber) . "\t"; // iss_start
            echo $this->sanitize((string) $endNumber) . "\n"; // iss_end
        }
    }

    /**
     * Get management verbs.
     * @param array $verbs
     * @param mixed $request
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        $verbs = parent::getManagementVerbs($verbs, $request);
        if (Validation::isSiteAdmin() && $this->getEnabled()) {
            $verbs[] = [
                'exportHoldings',
                __('plugins.gateways.resolver.exportHoldings')
            ];
        }
        return $verbs;
    }

    /**
     * Management handler.
     * @param string $verb
     * @param array $args
     * @param string|null $message DEPRECATED (use NotificationManager)
     * @param array|null $messageParams DEPRECATED
     * @param mixed $request
     * @return bool
     */
    public function manage(string $verb, array $args, ?string &$message = null, ?array &$messageParams = null, $request = null): bool {
        switch ($verb) {
            case 'exportHoldings':
                if (Validation::isSiteAdmin() && $this->getEnabled()) {
                    $this->exportHoldings();
                    return true;
                }
                break;
        }
        return parent::manage($verb, $args, $message, $messageParams, $request);
    }

}
?>