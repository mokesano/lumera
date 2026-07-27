<?php
declare(strict_types=1);

/**
 * @file plugins/generic/webFeed/WebFeedGatewayPlugin.inc.php
 * 
 * Copyright (c) 2017-2026 Sangia Lumera Publishing.
 * Copyright (c) 2024-2026 Rochmady and Code Lumera Team.
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING
 *
 * @class WebFeedGatewayPlugin
 * @ingroup plugins_generic_webFeed
 *
 * @brief Gateway component of web feed plugin.
 */

import('classes.plugins.GatewayPlugin');

class WebFeedGatewayPlugin extends GatewayPlugin {

    /** @var string Name of parent plugin */
    public $parentPluginName;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function WebFeedGatewayPlugin() {
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
     * Hide this plugin from the management interface (it's subsidiary).
     * @return bool
     */
    public function getHideManagement(): bool {
        return true;
    }

    /**
     * Get the name of this plugin.
     * @return string
     */
    public function getName(): string {
        return 'WebFeedGatewayPlugin';
    }

    /**
     * Get display name.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.webfeed.displayName');
    }

    /**
     * Get description.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.webfeed.description');
    }

    /**
     * Get the web feed plugin.
     * @return GatewayPlugin|null
     */
    public function getWebFeedPlugin() {
        return PluginRegistry::getPlugin('generic', $this->parentPluginName);
    }

    /**
     * Override the builtin to get the correct plugin path.
     * @return string
     */
    public function getPluginPath(): string {
        $plugin = $this->getWebFeedPlugin();
        return $plugin ? $plugin->getPluginPath() : '';
    }

    /**
     * Override the builtin to get the correct template path.
     * @return string
     */
    public function getTemplatePath(): string {
        $plugin = $this->getWebFeedPlugin();
        return $plugin ? $plugin->getTemplatePath() . 'templates/' : '';
    }

    /**
     * Get whether or not this plugin is enabled.
     * @return bool
     */
    public function getEnabled(): bool {
        $plugin = $this->getWebFeedPlugin();
        return $plugin ? $plugin->getEnabled() : false;
    }

    /**
     * Get the management verbs for this plugin (overridden to none).
     * @param array $verbs
     * @param Request $request
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        return [];
    }

    /**
     * Handle fetch requests for this plugin.
     * @param array $args
     * @param Request $request
     * @return bool
     */
    public function fetch($args, $request = null) {
        // Lumera Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        AppLocale::requireComponents([LOCALE_COMPONENT_APPLICATION_COMMON]);

        $journal = $request->getJournal();
        if (!$journal) {
            return false;
        }

        /** @var IssueDAO $issueDao */
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $issue = $issueDao->getCurrentIssue((int) $journal->getId(), true);
        if (!$issue) {
            return false;
        }

        $webFeedPlugin = $this->getWebFeedPlugin();
        if (!$webFeedPlugin || !$webFeedPlugin->getEnabled()) {
            return false;
        }

        $type = array_shift($args);
        $typeMap = [
            'rss' => 'rss.tpl',
            'rss2' => 'rss2.tpl',
            'atom' => 'atom.tpl'
        ];
        $mimeTypeMap = [
            'rss' => 'application/rdf+xml',
            'rss2' => 'application/rss+xml',
            'atom' => 'application/atom+xml'
        ];

        if (!isset($typeMap[$type])) {
            return false;
        }

        $journalId = (int) $journal->getId();
        $displayItems = (string) $webFeedPlugin->getSetting($journalId, 'displayItems');
        $recentItems = (int) $webFeedPlugin->getSetting($journalId, 'recentItems');
        
        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $publishedArticles = [];

        if ($displayItems === 'recent' && $recentItems > 0) {
            import('lib.pkp.classes.db.DBResultRange');
            $rangeInfo = new DBResultRange($recentItems, 1);
            $publishedArticleObjects = $publishedArticleDao->getPublishedArticlesByJournalId(
                $journalId, $rangeInfo, true
            );
            
            if ($publishedArticleObjects) {
                while ($publishedArticle = $publishedArticleObjects->next()) {
                    $publishedArticles[] = $publishedArticle;
                }
            }
        } else {
            $publishedArticles = $publishedArticleDao->getPublishedArticlesInSections(
                (int) $issue->getId(), true
            );
        }

        /** @var VersionDAO $versionDao */
        $versionDao = DAORegistry::getDAO('VersionDAO');
        $version = $versionDao->getCurrentVersion();
        $versionString = $version ? $version->getVersionString() : 'Unknown';

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('ojsVersion', $versionString);
        $templateMgr->assign('publishedArticles', $publishedArticles);
        $templateMgr->assign('journal', $journal);
        $templateMgr->assign('issue', $issue);
        $templateMgr->assign('showToc', true);

        header('Content-Type: ' . $mimeTypeMap[$type] . '; charset=utf-8');

        $templateMgr->display(
            $this->getTemplatePath() . $typeMap[$type],
            $mimeTypeMap[$type]
        );

        return true;
    }

}
?>