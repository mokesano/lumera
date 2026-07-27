<?php
declare(strict_types=1);

/**
 * @file plugins/generic/usageEvent/UsageEventPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UsageEventPlugin
 * @ingroup plugins_generic_usageEvent
 *
 * @brief Provide usage event to other statistics plugins.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

// Our own and OA-S classification types.
define('USAGE_EVENT_PLUGIN_CLASSIFICATION_BOT', 'bot');
define('USAGE_EVENT_PLUGIN_CLASSIFICATION_ADMIN', 'administrative');

class UsageEventPlugin extends GenericPlugin {

    //
    // Implement methods from PKPPlugin.
    //
    /**
     * Register the plugin.
     * @see LazyLoadPlugin::register()
     * @param string $category
     * @param string $path
     * @return bool
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);

        if ($success) {
            // Register callbacks.
            HookRegistry::register('TemplateManager::display', [$this, 'getUsageEvent']);
            HookRegistry::register('ArticleHandler::viewFile', [$this, 'getUsageEvent']);
            HookRegistry::register('ArticleHandler::viewRemoteGalley', [$this, 'getUsageEvent']);
            HookRegistry::register('ArticleHandler::downloadFile', [$this, 'getUsageEvent']);
            HookRegistry::register('ArticleHandler::downloadSuppFile', [$this, 'getUsageEvent']);
            HookRegistry::register('IssueHandler::viewFile', [$this, 'getUsageEvent']);
            HookRegistry::register('FileManager::downloadFileFinished', [$this, 'getUsageEvent']);
        }

        return $success;
    }

    /**
     * Get the display name of this plugin.
     * @see PKPPlugin::getDisplayName()
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.usageEvent.displayName');
    }

    /**
     * Get the description of this plugin.
     * @see PKPPlugin::getDescription()
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.usageEvent.description');
    }

    /**
     * Determine whether or not the plugin is enabled.
     * @see LazyLoadPlugin::getEnabled()
     * @param null|Request $request
     * @return bool
     */
    public function getEnabled($request = null): bool {
        return true;
    }

    /**
     * Determine whether or not the plugin is a site plugin.
     * @see PKPPlugin::isSitePlugin()
     * @return bool
     */
    public function isSitePlugin(): bool {
        return true;
    }

    /**
     * Get management verbs.
     * @see GenericPlugin::getManagementVerbs()
     * @param array $verbs
     * @param null|Request $request
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        return [];
    }


    //
    // Public methods.
    //
    /**
     * Get the unique site id.
     * @return string|null
     */
    public function getUniqueSiteId() {
        return $this->getSetting(0, 'uniqueSiteId');
    }


    //
    // Hook implementations.
    //
    /**
     * Get usage event and pass it to the registered plugins, if any.
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function getUsageEvent($hookName, $args) {
        // Check if we have a registration to receive the usage event.
        $hooks = HookRegistry::getHooks();
        
        if (isset($hooks['UsageEventPlugin::getUsageEvent'])) {
            $usageEvent = $this->_buildUsageEvent($hookName, $args);
            
            // [WIZDAM PROTOCOL] Dispatch Logic
            if ($usageEvent !== null && $usageEvent !== false) {
                $dispatchArgs = array_merge([$hookName, $usageEvent], $args);
                HookRegistry::dispatch('UsageEventPlugin::getUsageEvent', $dispatchArgs);
            }
        }
        return false;
    }


    //
    // Private helper methods.
    //
    /**
     * Build a usage event.
     * @param string $hookName
     * @param array $args
     * @return array|bool|null
     */
    private function _buildUsageEvent($hookName, $args) {
        // Finished downloading a file?
        if ($hookName === 'FileManager::downloadFileFinished') {
            return null;
        }

        $request = Application::get()->getRequest();
        $router = $request->getRouter();
        $templateMgr = $args[0] ?? null;

        // We are just interested in page requests.
        if (!($router instanceof PageRouter)) {
            return false;
        }

        // Check whether we are in journal context.
        $journal = $router->getContext($request);
        if (!$journal) {
            return false;
        }

        // Prepare request information.
        $downloadSuccess = false;
        $idParams = [];
        $canonicalUrlParams = [];
        
        // Initialize objects to null
        $pubObject = null;
        $assocType = null;
        $canonicalUrlOp = '';

        switch ($hookName) {
            // Article abstract and HTML galley.
            case 'TemplateManager::display':
                $page = $router->getRequestedPage($request);
                $op = $router->getRequestedOp($request);

                // First check for a journal index page view.
                if (($page === 'index' || $page === '') && $op === 'index') {
                    $pubObject = $templateMgr ? $templateMgr->get_template_vars('currentJournal') : null;
                    
                    if ($pubObject instanceof Journal) {
                        $assocType = ASSOC_TYPE_JOURNAL;
                        $canonicalUrlOp = '';
                        $downloadSuccess = true;
                        break;
                    } else {
                        return false;
                    }
                }

                // We are interested in access to the article abstract/galley, issue view page.
                $wantedPages = ['article', 'issue'];
                $wantedOps = ['view', 'articleView'];

                if (!in_array($page, $wantedPages, true) || !in_array($op, $wantedOps, true)) {
                    return false;
                }

                $issue = $templateMgr ? $templateMgr->get_template_vars('issue') : null;
                $galley = $templateMgr ? $templateMgr->get_template_vars('galley') : null;
                $article = $templateMgr ? $templateMgr->get_template_vars('article') : null;

                // If there is no published object, there is no usage event.
                if (!$issue && !$galley && !$article) {
                    return false;
                }

                if ($galley) {
                    if ($galley->isHTMLGalley()) {
                        $pubObject = $galley;
                        $assocType = ASSOC_TYPE_GALLEY;
                        $canonicalUrlParams = [(int) $article->getId(), $pubObject->getId($journal)];
                        $idParams = ['a' . (int) $article->getId(), 'g' . $pubObject->getId()];
                    } else {
                        // This is an access to an intermediary galley page which we do not count.
                        return false;
                    }
                } else {
                    if ($article) {
                        $pubObject = $article;
                        $assocType = ASSOC_TYPE_ARTICLE;
                        $canonicalUrlParams = [$pubObject->getId($journal)];
                        $idParams = ['a' . (int) $pubObject->getId()];
                    } else {
                        $pubObject = $issue;
                        $assocType = ASSOC_TYPE_ISSUE;
                        $canonicalUrlParams = [$pubObject->getId($journal)];
                        $idParams = ['i' . (int) $pubObject->getId()];
                    }
                }
                // The article, issue and HTML/remote galley pages do not download anything.
                $downloadSuccess = true;
                $canonicalUrlOp = 'view';
                break;

            case 'ArticleHandler::viewRemoteGalley':
                $article = $args[0] ?? null;
                $pubObject = $args[1] ?? null;
                if (!$article || !$pubObject) return false;
                
                $assocType = ASSOC_TYPE_GALLEY;
                $canonicalUrlParams = [(int) $article->getId(), $pubObject->getId($journal)];
                $idParams = ['a' . (int) $article->getId(), 'g' . $pubObject->getId()];
                $downloadSuccess = true;
                $canonicalUrlOp = 'view';
                break;
            
            // Article galley (except for HTML and remote galley).
            case 'ArticleHandler::viewFile':
            case 'ArticleHandler::downloadFile':
                $article = $args[0] ?? null;
                $pubObject = $args[1] ?? null;
                $fileId = (int) ($args[2] ?? 0);
                
                if (!$pubObject || $pubObject->getFileId() !== $fileId) {
                    return false;
                }
                $assocType = ASSOC_TYPE_GALLEY;
                $canonicalUrlOp = 'download';
                $canonicalUrlParams = [(int) $article->getId(), $pubObject->getId($journal)];
                $idParams = ['a' . (int) $article->getId(), 'g' . $pubObject->getId()];
                break;

            // Supplementary file.
            case 'ArticleHandler::downloadSuppFile':
                $article = $args[0] ?? null;
                $pubObject = $args[1] ?? null;
                if (!$article || !$pubObject) return false;
                
                $assocType = ASSOC_TYPE_SUPP_FILE;
                $canonicalUrlOp = 'downloadSuppFile';
                $canonicalUrlParams = [(int) $article->getId(), $pubObject->getId($journal)];
                $idParams = ['a' . (int) $article->getId(), 's' . $pubObject->getId()];
                break;

            // Issue galley.
            case 'IssueHandler::viewFile':
                $issue = $args[0] ?? null;
                $pubObject = $args[1] ?? null;
                if (!$issue || !$pubObject) return false;
                
                $assocType = ASSOC_TYPE_ISSUE_GALLEY;
                $canonicalUrlOp = 'download';
                $canonicalUrlParams = [(int) $issue->getId(), $pubObject->getId($journal)];
                $idParams = ['i' . (int) $issue->getId(), 'ig' . $pubObject->getId()];
                break;

            default:
                return false;
        }

        // Timestamp.
        $time = Core::getCurrentDate();

        // Actual document size, MIME type.
        $htmlPageAssocTypes = [ASSOC_TYPE_ARTICLE, ASSOC_TYPE_ISSUE, ASSOC_TYPE_JOURNAL];
        if (in_array($assocType, $htmlPageAssocTypes, true)) {
            // Article abstract or issue view page.
            $docSize = 0;
            $mimeType = 'text/html';
        } else {
            // Files.
            $docSize = (int) $pubObject->getFileSize();
            $mimeType = (string) $pubObject->getFileType();
        }

        // Canonical URL.
        $canonicalUrlPage = '';
        switch ($assocType) {
            case ASSOC_TYPE_ISSUE:
            case ASSOC_TYPE_ISSUE_GALLEY:
                $canonicalUrlPage = 'issue';
                break;
            case ASSOC_TYPE_ARTICLE:
            case ASSOC_TYPE_GALLEY:
            case ASSOC_TYPE_SUPP_FILE:
                $canonicalUrlPage = 'article';
                break;
            case ASSOC_TYPE_JOURNAL:
                $canonicalUrlPage = 'index';
                break;
        }

        $canonicalUrl = $router->url(
            $request, null, $canonicalUrlPage, $canonicalUrlOp, $canonicalUrlParams
        );

        // Make sure we log the server name and not aliases.
        $configBaseUrl = (string) Config::getVar('general', 'base_url');
        $requestBaseUrl = (string) $request->getBaseUrl();
        
        if ($requestBaseUrl !== $configBaseUrl) {
            $contextBaseUrls = Config::getContextBaseUrls();
            if (!in_array($requestBaseUrl, $contextBaseUrls, true) &&
                $requestBaseUrl !== (string) Config::getVar('general', 'base_url[index]')) {
                
                $baseUrlReplacement = (string) Config::getVar('general', 'base_url[' . $journal->getPath() . ']');
                if ($baseUrlReplacement === '') {
                    $baseUrlReplacement = $configBaseUrl;
                }
                $canonicalUrl = str_replace($requestBaseUrl, $baseUrlReplacement, $canonicalUrl);
            }
        }

        // Public identifiers.
        array_unshift($idParams, 'j' . (int) $journal->getId());
        $siteId = $this->getUniqueSiteId();
        
        if (empty($siteId)) {
            $siteId = uniqid();
            $this->updateSetting(0, 'uniqueSiteId', (string) $siteId);
        }
        array_unshift($idParams, (string) $siteId);
        $ojsId = 'ojs:' . implode('-', $idParams);
        $identifiers = ['other::ojs' => $ojsId];

        // Standardized public identifiers
        if (!($pubObject instanceof IssueGalley) && !($pubObject instanceof Journal)) {
            $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true, (int) $journal->getId());
            if (is_array($pubIdPlugins)) {
                foreach ($pubIdPlugins as $pubIdPlugin) {
                    if (!$pubIdPlugin->getEnabled()) {
                        continue;
                    }
                    $pubId = $pubIdPlugin->getPubId($pubObject);
                    if ($pubId) {
                        $identifiers[$pubIdPlugin->getPubIdType()] = (string) $pubId;
                    }
                }
            }
        }

        // Service URI.
        $serviceUri = $router->url($request, (string) $journal->getPath());

        // IP and Host.
        $ip = (string) $request->getRemoteAddr();
        $host = $_SERVER['REMOTE_HOST'] ?? null;

        // HTTP user agent.
        $userAgent = (string) $request->getUserAgent();

        // HTTP referrer.
        $referrer = $_SERVER['HTTP_REFERER'] ?? null;

        // User and roles.
        $user = $request->getUser();
        $roles = [];
        if ($user) {
            /** @var RoleDAO $roleDao */
            $roleDao = DAORegistry::getDAO('RoleDAO');
            $rolesByContext = $roleDao->getByUserIdGroupedByContext((int) $user->getId());
            foreach ([CONTEXT_SITE, (int) $journal->getId()] as $context) {
                if (isset($rolesByContext[$context])) {
                    foreach ($rolesByContext[$context] as $role) {
                        $roles[] = (int) $role->getRoleId();
                    }
                }
            }
        }

        // Try a simple classification of the request.
        $classification = null;
        if (!empty($roles)) {
            $internalRoles = array_diff($roles, [ROLE_ID_READER]);
            if (!empty($internalRoles)) {
                $classification = USAGE_EVENT_PLUGIN_CLASSIFICATION_ADMIN;
            }
        }
        if (method_exists($request, 'isBot') && $request->isBot()) {
            $classification = USAGE_EVENT_PLUGIN_CLASSIFICATION_BOT;
        }

        // Collect all information into an array.
        $usageEvent = compact(
            'time', 'pubObject', 'assocType', 'canonicalUrl', 'mimeType',
            'identifiers', 'docSize', 'downloadSuccess', 'serviceUri',
            'ip', 'host', 'user', 'roles', 'userAgent', 'referrer',
            'classification'
        );

        return $usageEvent;
    }

}
?>