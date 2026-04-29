<?php
declare(strict_types=1);

/**
 * @file plugins/generic/webFeed/WebFeedGatewayPlugin.inc.php
 *
 * @class WebFeedGatewayPlugin
 * @ingroup plugins_generic_webFeed
 *
 * @brief Gateway component of web feed plugin
 * * MODERNIZED FOR WIZDAM FORK
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
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Hide this plugin from the management interface (it's subsidiary)
     * @return string
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
     * Get display name
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.webfeed.displayName');
    }

    /**
     * Get description
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.webfeed.description');
    }

    /**
     * Get the web feed plugin
     * @return object
     */
    public function getWebFeedPlugin() {
        $plugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
        return $plugin;
    }

    /**
     * Override the builtin to get the correct plugin path.
     */
    public function getPluginPath(): string {
        $plugin = $this->getWebFeedPlugin();
        return $plugin->getPluginPath();
    }

    /**
     * Override the builtin to get the correct template path.
     * @return string
     */
    public function getTemplatePath(): string {
        $plugin = $this->getWebFeedPlugin();
        return $plugin->getTemplatePath() . 'templates/';
    }

    /**
     * Get whether or not this plugin is enabled.
     * @return boolean
     */
    public function getEnabled(): bool {
        $plugin = $this->getWebFeedPlugin();
        return $plugin->getEnabled();
    }

    /**
     * Get the management verbs for this plugin (overridden to none).
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        return [];
    }

    /**
     * Handle fetch requests for this plugin.
     */
    public function fetch($args, $request = null) {
        if (!$request) $request = Application::getRequest();
        AppLocale::requireComponents([LOCALE_COMPONENT_APPLICATION_COMMON]);

        $journal = $request->getJournal();
        if (!$journal) return false;

        $issueDao = DAORegistry::getDAO('IssueDAO');
        $issue = $issueDao->getCurrentIssue($journal->getId(), true);
        if (!$issue) return false;

        $webFeedPlugin = $this->getWebFeedPlugin();
        if (!$webFeedPlugin->getEnabled()) return false;

        $type = array_shift($args);
        $typeMap = ['rss' => 'rss.tpl', 'rss2' => 'rss2.tpl', 'atom' => 'atom.tpl'];
        $mimeTypeMap = [
            'rss' => 'application/rdf+xml',
            'rss2' => 'application/rss+xml',
            'atom' => 'application/atom+xml'
        ];

        if (!isset($typeMap[$type])) return false;

        // --- Logika Data Artikel (Tetap sama seperti asli) ---
        $displayItems = $webFeedPlugin->getSetting($journal->getId(), 'displayItems');
        $recentItems = (int) $webFeedPlugin->getSetting($journal->getId(), 'recentItems');
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');

        if ($displayItems == 'recent' && $recentItems > 0) {
            import('lib.pkp.classes.db.DBResultRange');
            $rangeInfo = new DBResultRange($recentItems, 1);
            $publishedArticleObjects = $publishedArticleDao->getPublishedArticlesByJournalId(
                $journal->getId(), $rangeInfo, true
            );
            $publishedArticles = [];
            while ($publishedArticle = $publishedArticleObjects->next()) {
                $publishedArticles[]['articles'][] = $publishedArticle;
            }
        } else {
            $publishedArticles = $publishedArticleDao->getPublishedArticlesInSections(
                $issue->getId(), true
            );
        }

        $versionDao = DAORegistry::getDAO('VersionDAO');
        $version = $versionDao->getCurrentVersion();

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('ojsVersion', $version->getVersionString());
        $templateMgr->assign('publishedArticles', $publishedArticles);
        $templateMgr->assign('journal', $journal);
        $templateMgr->assign('issue', $issue);
        $templateMgr->assign('showToc', true);

        // Header Content-Type
        header('Content-Type: ' . $mimeTypeMap[$type] . '; charset=utf-8');

        $templateMgr->display(
            $this->getTemplatePath() . $typeMap[$type],
            $mimeTypeMap[$type]
        );

        return true;
    }
}

?>