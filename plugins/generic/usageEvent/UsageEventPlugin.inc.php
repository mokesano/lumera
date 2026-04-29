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
 * * REFACTORED: Wizdam Edition (HookRegistry::dispatch + instanceof modernization)
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
     * @return bool True if plugin initialized successfully.
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);

        if ($success) {
            // Register callbacks.
            HookRegistry::register('TemplateManager::display', array($this, 'getUsageEvent'));
            HookRegistry::register('ArticleHandler::viewFile', array($this, 'getUsageEvent'));
            HookRegistry::register('ArticleHandler::viewRemoteGalley', array($this, 'getUsageEvent'));
            HookRegistry::register('ArticleHandler::downloadFile', array($this, 'getUsageEvent'));
            HookRegistry::register('ArticleHandler::downloadSuppFile', array($this, 'getUsageEvent'));
            HookRegistry::register('IssueHandler::viewFile', array($this, 'getUsageEvent'));
            HookRegistry::register('FileManager::downloadFileFinished', array($this, 'getUsageEvent'));
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
     * @return mixed string or null
     */
    public function getUniqueSiteId() {
        return $this->getSetting(0, 'uniqueSiteId');
    }


    //
    // Hook implementations.
    //
    /**
     * Get usage event and pass it to the registered plugins, if any.
     * @param $hookName string
     * @param $args array
     * @return bool false
     */
    public function getUsageEvent($hookName, $args) {
        // Check if we have a registration to receive the usage event.
        $hooks = HookRegistry::getHooks();
        
        if (array_key_exists('UsageEventPlugin::getUsageEvent', $hooks)) {
            $usageEvent = $this->_buildUsageEvent($hookName, $args);
            
            // [WIZDAM PROTOCOL] Dispatch Logic
            $dispatchArgs = array_merge(array($hookName, $usageEvent), $args);
            HookRegistry::dispatch('UsageEventPlugin::getUsageEvent', $dispatchArgs);
        }
        return false;
    }


    //
    // Private helper methods.
    //
    /**
     * Build an usage event.
     * @param $hookName string
     * @param $args array
     * @return array
     */
    private function _buildUsageEvent($hookName, $args) {
        // Finished downloading a file?
        if ($hookName == 'FileManager::downloadFileFinished') {
            return null;
        }

        $application = Application::getApplication();
        $request = $application->getRequest();
        $router = $request->getRouter(); /* @var $router PageRouter */
        $templateMgr = $args[0]; /* @var $templateMgr TemplateManager */

        // We are just interested in page requests.
        // [WIZDAM FIX] Replaced is_a() with instanceof
        if (!($router instanceof PageRouter)) return false;

        // Check whether we are in journal context.
        $journal = $router->getContext($request);
        if (!$journal) return false;

        // Prepare request information.
        $downloadSuccess = false;
        $idParams = array();
        $canonicalUrlParams = array();
        
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
                if (($page == 'index' || empty($page)) && $op == 'index') {
                    $pubObject = $templateMgr->get_template_vars('currentJournal');
                    
                    // [WIZDAM FIX] Replaced is_a() with instanceof
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
                $wantedPages = array('article', 'issue');
                $wantedOps = array('view', 'articleView');

                if (!in_array($page, $wantedPages) || !in_array($op, $wantedOps)) return false;

                $issue = $templateMgr->get_template_vars('issue');
                $galley = $templateMgr->get_template_vars('galley'); /* @var $galley ArticleGalley */
                $article = $templateMgr->get_template_vars('article');

                // If there is no published object, there is no usage event.
                if (!$issue && !$galley && !$article) return false;

                if ($galley) {
                    if ($galley->isHTMLGalley()) {
                        $pubObject = $galley;
                        $assocType = ASSOC_TYPE_GALLEY;
                        $canonicalUrlParams = array($article->getId(), $pubObject->getId($journal));
                        $idParams = array('a' . $article->getId(), 'g' . $pubObject->getId());
                    } else {
                        // This is an access to an intermediary galley page which we do not count.
                        return false;
                    }
                } else {
                    if ($article) {
                        $pubObject = $article;
                        $assocType = ASSOC_TYPE_ARTICLE;
                        $canonicalUrlParams = array($pubObject->getId($journal));
                        $idParams = array('a' . $pubObject->getId());
                    } else {
                        $pubObject = $issue;
                        $assocType = ASSOC_TYPE_ISSUE;
                        $canonicalUrlParams = array($pubObject->getId($journal));
                        $idParams = array('i' . $pubObject->getId());
                    }
                }
                // The article, issue and HTML/remote galley pages do not download anything.
                $downloadSuccess = true;
                $canonicalUrlOp = 'view';
                break;

            case 'ArticleHandler::viewRemoteGalley':
                $article = $args[0];
                $pubObject = $args[1];
                $assocType = ASSOC_TYPE_GALLEY;
                $canonicalUrlParams = array($article->getId(), $pubObject->getId($journal));
                $idParams = array('a' . $article->getId(), 'g' . $pubObject->getId());
                $downloadSuccess = true;
                $canonicalUrlOp = 'view';
                break;
            
            // Article galley (except for HTML and remote galley).
            case 'ArticleHandler::viewFile':
            case 'ArticleHandler::downloadFile':
                $article = $args[0];
                $pubObject = $args[1];
                $fileId = $args[2];
                // if file is not a gallay file (e.g. CSS or images), there is no usage event.
                if ($pubObject->getFileId() != $fileId) return false;
                $assocType = ASSOC_TYPE_GALLEY;
                $canonicalUrlOp = 'download';
                $canonicalUrlParams = array($article->getId(), $pubObject->getId($journal));
                $idParams = array('a' . $article->getId(), 'g' . $pubObject->getId());
                break;

            // Supplementary file.
            case 'ArticleHandler::downloadSuppFile':
                $pubObject = $args[1];
                $assocType = ASSOC_TYPE_SUPP_FILE;
                $canonicalUrlOp = 'downloadSuppFile';
                $article = $args[0];
                $canonicalUrlParams = array($article->getId(), $pubObject->getId($journal));
                $idParams = array('a' . $article->getId(), 's' . $pubObject->getId());
                break;

            // Issue galley.
            case 'IssueHandler::viewFile':
                $pubObject = $args[1];
                $assocType = ASSOC_TYPE_ISSUE_GALLEY;
                $canonicalUrlOp = 'download';
                $issue = $args[0];
                $canonicalUrlParams = array($issue->getId(), $pubObject->getId($journal));
                $idParams = array('i' . $issue->getId(), 'ig' . $pubObject->getId());
                break;

            default:
                return false;
        }

        // Timestamp.
        $time = Core::getCurrentDate();

        // Actual document size, MIME type.
        $htmlPageAssocTypes = array(ASSOC_TYPE_ARTICLE, ASSOC_TYPE_ISSUE, ASSOC_TYPE_JOURNAL);
        if (in_array($assocType, $htmlPageAssocTypes)) {
            // Article abstract or issue view page.
            $docSize = 0;
            $mimeType = 'text/html';
        } else {
            // Files.
            $docSize = (int)$pubObject->getFileSize();
            $mimeType = $pubObject->getFileType();
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
        $configBaseUrl = Config::getVar('general', 'base_url');
        $requestBaseUrl = $request->getBaseUrl();
        if ($requestBaseUrl !== $configBaseUrl) {
            if (!in_array($requestBaseUrl, Config::getContextBaseUrls()) &&
                    $requestBaseUrl !== Config::getVar('general', 'base_url[index]')) {
                $baseUrlReplacement = Config::getVar('general', 'base_url['.$journal->getPath().']');
                if (!$baseUrlReplacement) $baseUrlReplacement = $configBaseUrl;
                $canonicalUrl = str_replace($requestBaseUrl, $baseUrlReplacement, $canonicalUrl);
            }
        }

        // Public identifiers.
        array_unshift($idParams, 'j' . $journal->getId());
        $siteId = $this->getUniqueSiteId();
        if (empty($siteId)) {
            $siteId = uniqid();
            $this->updateSetting(0, 'uniqueSiteId', $siteId);
        }
        array_unshift($idParams, $siteId);
        $ojsId = 'ojs:' . implode('-', $idParams);
        $identifiers = array('other::ojs' => $ojsId);

        // Standardized public identifiers
        // [WIZDAM FIX] Replaced is_a() with instanceof
        if (!($pubObject instanceof IssueGalley) && !($pubObject instanceof Journal)) {
            $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true, $journal->getId());
            if (is_array($pubIdPlugins)) {
                foreach ($pubIdPlugins as $pubIdPlugin) {
                    if (!$pubIdPlugin->getEnabled()) continue;
                    $pubId = $pubIdPlugin->getPubId($pubObject);
                    if ($pubId) {
                        $identifiers[$pubIdPlugin->getPubIdType()] = $pubId;
                    }
                }
            }
        }

        // Service URI.
        $serviceUri = $router->url($request, $journal->getPath());

        // IP and Host.
        $ip = $request->getRemoteAddr();
        $host = null;
        if (isset($_SERVER['REMOTE_HOST'])) {
            $host = $_SERVER['REMOTE_HOST'];
        }

        // HTTP user agent.
        $userAgent = $request->getUserAgent();

        // HTTP referrer.
        $referrer = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null);

        // User and roles.
        $user = $request->getUser();
        $roles = array();
        if ($user) {
            $roleDao = DAORegistry::getDAO('RoleDAO');
            $rolesByContext = $roleDao->getByUserIdGroupedByContext($user->getId());
            foreach (array(CONTEXT_SITE, $journal->getId()) as $context) {
                if(isset($rolesByContext[$context])) {
                    foreach ($rolesByContext[$context] as $role) {
                        $roles[] = $role->getRoleId();
                    }
                }
            }
        }

        // Try a simple classification of the request.
        $classification = null;
        if (!empty($roles)) {
            $internalRoles = array_diff($roles, array(ROLE_ID_READER));
            if (!empty($internalRoles)) {
                $classification = USAGE_EVENT_PLUGIN_CLASSIFICATION_ADMIN;
            }
        }
        if ($request->isBot()) {
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