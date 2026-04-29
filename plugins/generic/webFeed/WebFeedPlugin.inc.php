<?php
declare(strict_types=1);

/**
 * @file plugins/generic/webFeed/WebFeedPlugin.inc.php
 *
 * @class WebFeedPlugin
 * @ingroup plugins_block_webFeed
 *
 * @brief Web Feeds plugin class — MODERNIZED FOR PHP 7.4+ & OJS FORK
 */


import('lib.pkp.classes.plugins.GenericPlugin');

class WebFeedPlugin extends GenericPlugin {

    /**
     * Get the display name of this plugin
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.webfeed.displayName');
    }

    /**
     * Get the description of this plugin
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.webfeed.description');
    }

    /**
     * Register plugin and assign hooks.
     * @param string $category
     * @param string $path
     * @param int|null $mainContextId
     * @return bool True if plugin initialized successfully
     */
    public function register(string $category, string $path): bool {
        if (parent::register($category, $path)) {

            if ($this->getEnabled()) {
                HookRegistry::register('TemplateManager::display', [$this, 'callbackAddLinks']);
                HookRegistry::register('PluginRegistry::loadCategory', [$this, 'callbackLoadCategory']);
                HookRegistry::register('LoadHandler', [$this, 'callbackHandleShortURL']);
            }

            return true;
        }
        return false;
    }

    /**
     * Settings for new context creation
     * @return string|null
     */
    public function getContextSpecificPluginSettingsFile(): ?string {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * Register block & gateway plugins.
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function callbackLoadCategory($hookName, $args) {
        $category = $args[0];

        switch ($category) {
            case 'blocks':
                $this->import('WebFeedBlockPlugin');
                $blockPlugin = new WebFeedBlockPlugin();
                $blockPlugin->parentPluginName = $this->getName();
                $args[1][$blockPlugin->getSeq()][$blockPlugin->getPluginPath()] = $blockPlugin;
                break;

            case 'gateways':
                $this->import('WebFeedGatewayPlugin');
                $gatewayPlugin = new WebFeedGatewayPlugin();
                $gatewayPlugin->parentPluginName = $this->getName();
                $args[1][$gatewayPlugin->getSeq()][$gatewayPlugin->getPluginPath()] = $gatewayPlugin;
                break;
        }

        return false;
    }

    /**
     * Insert RSS/Atom headers into <head>
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function callbackAddLinks($hookName, $args) {

        if (!$this->getEnabled()) return false;

        $request = Application::getRequest();
        if (!is_a($request->getRouter(), 'PKPPageRouter')) return false;

        $templateManager = $args[0];

        $currentJournal = $templateManager->get_template_vars('currentJournal');
        if (!$currentJournal) return false;

        $requestedPage = $request->getRequestedPage();

        $issueDao = DAORegistry::getDAO('IssueDAO');
        $currentIssue = $issueDao->getCurrentIssue($currentJournal->getId(), true);

        $displayPage = $this->getSetting($currentJournal->getId(), 'displayPage');
        $journalTitle = $this->sanitize($currentJournal->getLocalizedTitle());

        if (
            $currentIssue &&
            (
                $displayPage === 'all'
                || ($displayPage === 'homepage' &&
                    (empty($requestedPage) || $requestedPage === 'index' || $requestedPage === 'issue'))
                || ($displayPage === 'issue' && $displayPage === $requestedPage)
            )
        ) {
            $additionalHeadData = $templateManager->get_template_vars('additionalHeadData');
            $baseUrl = rtrim($currentJournal->getUrl(), '/');

            $feedUrl1 = '<link rel="alternate" type="application/atom+xml" title="' . $journalTitle .
                        ' (atom+xml)" href="' . $baseUrl . '/gateway/plugin/WebFeedGatewayPlugin/atom" />';
            $feedUrl2 = '<link rel="alternate" type="application/rdf+xml" title="' . $journalTitle .
                        ' (rdf+xml)" href="' . $baseUrl . '/gateway/plugin/WebFeedGatewayPlugin/rss" />';
            $feedUrl3 = '<link rel="alternate" type="application/rss+xml" title="' . $journalTitle .
                        ' (rss+xml)" href="' . $baseUrl . '/gateway/plugin/WebFeedGatewayPlugin/rss2" />';

            $templateManager->assign('additionalHeadData',
                $additionalHeadData . "\n\t" . $feedUrl1 . "\n\t" . $feedUrl2 . "\n\t" . $feedUrl3
            );
        }

        return false;
    }

    /**
     * Support short URLs (journal/feed/atom)
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function callbackHandleShortURL($hookName, $args) {

        if (!$this->getEnabled()) return false;

        $page = $args[0];
        $op   = $args[1];

        if ($page !== 'feed') return false;

        switch ($op) {
            case 'atom':
                Request::redirect(null, 'gateway', 'plugin',
                    ['WebFeedGatewayPlugin', 'atom']);
                break;

            case 'rss':
                Request::redirect(null, 'gateway', 'plugin',
                    ['WebFeedGatewayPlugin', 'rss']);
                break;

            case 'rss2':
                Request::redirect(null, 'gateway', 'plugin',
                    ['WebFeedGatewayPlugin', 'rss2']);
                break;

            default:
                Request::redirect(null, 'index');
        }

        return false;
    }

    /**
     * Management verbs
     * @param array $verbs
     * @param null $request
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {

        $verbs = parent::getManagementVerbs($verbs, $request);

        if ($this->getEnabled($request)) {
            $verbs[] = ['settings', __('plugins.generic.webfeed.settings')];
        }

        return $verbs;
    }

    /**
     * Manage function
     * @param string $verb
     * @param array $args
     * @param string $message
     * @param array $messageParams
     * @param null $request
     * @return bool
     */
    public function manage(string $verb, array $args, string $message, array $messageParams, $request = null): bool {

        if (!parent::manage($verb, $args, $message, $messageParams, $request)) return false;

        if (!$request) $request = Application::getRequest();

        switch ($verb) {

            case 'settings':
                $journal = $request->getJournal();

                AppLocale::requireComponents(
                    LOCALE_COMPONENT_APPLICATION_COMMON,
                    LOCALE_COMPONENT_PKP_MANAGER
                );

                $templateMgr = TemplateManager::getManager();
                $templateMgr->register_function('plugin_url', [$this, 'smartyPluginUrl']);

                $this->import('SettingsForm');
                $form = new SettingsForm($this, $journal->getId());

                if ($request->getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        $request->redirect(null, null, 'plugins');
                        return false;
                    }
                }

                $form->initData();
                $form->display();
                return true;

            default:
                // Unknown management verb
                assert(false);
                return false;
        }
    }

    /**
     * Clean the journal title
     * @param string $string
     * @return string
     */
    public function sanitize($string) {
        return htmlspecialchars(strip_tags($string));
    }
}

?>