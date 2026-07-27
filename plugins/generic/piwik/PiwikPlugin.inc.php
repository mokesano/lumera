<?php
declare(strict_types=1);

/**
 * @file plugins/generic/piwik/PiwikPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PiwikPlugin
 * @ingroup plugins_generic_piwik
 *
 * @brief Piwik plugin class.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class PiwikPlugin extends GenericPlugin {

    /**
     * Called as a plugin is registered to the registry.
     * @param string $category
     * @param string $path
     * @return bool
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) {
            return true;
        }
        if ($success && $this->getEnabled()) {
            // Insert Piwik page tag to common footer
            HookRegistry::register('Templates::Common::Footer::PageFooter', [$this, 'insertFooter']);

            // Insert Piwik page tag to article footer
            HookRegistry::register('Templates::Article::Footer::PageFooter', [$this, 'insertFooter']);

            // Insert Piwik page tag to article interstitial footer
            HookRegistry::register('Templates::Article::Interstitial::PageFooter', [$this, 'insertFooter']);

            // Insert Piwik page tag to article pdf interstitial footer
            HookRegistry::register('Templates::Article::PdfInterstitial::PageFooter', [$this, 'insertFooter']);

            // Insert Piwik page tag to reading tools footer
            HookRegistry::register('Templates::Rt::Footer::PageFooter', [$this, 'insertFooter']);

            // Insert Piwik page tag to help footer
            HookRegistry::register('Templates::Help::Footer::PageFooter', [$this, 'insertFooter']);
        }
        return $success;
    }

    /**
     * Get the name of this plugin.
     * @return string
     */
    public function getName(): string {
        return 'PiwikPlugin';
    }

    /**
     * Get display name.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.piwik.displayName');
    }

    /**
     * Get description.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.piwik.description');
    }

    /**
     * Extend the {url ...} smarty to support this plugin.
     * @param array $params
     * @param object $smarty
     * @return string
     */
    public function smartyPluginUrl(array $params, $smarty): string {
        $path = [$this->getCategory(), $this->getName()];
        if (is_array($params['path'])) {
            $params['path'] = array_merge($path, $params['path']);
        } elseif (!empty($params['path'])) {
            $params['path'] = array_merge($path, [$params['path']]);
        } else {
            $params['path'] = $path;
        }

        if (!empty($params['id'])) {
            $params['path'] = array_merge($params['path'], [$params['id']]);
            unset($params['id']);
        }
        return $smarty->smartyUrl($params, $smarty);
    }

    /**
     * Set the page's breadcrumbs, given the plugin's tree of items to append.
     * @param bool $isSubclass
     */
    public function setBreadcrumbs($isSubclass = false) {
        // Lumera Singleton Fallback
        $request = Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);
        
        $pageCrumbs = [
            [
                $request->url(null, 'user'),
                'navigation.user'
            ],
            [
                $request->url(null, 'manager'),
                'user.role.manager'
            ]
        ];
        if ($isSubclass) {
            $pageCrumbs[] = [
                $request->url(null, 'manager', 'plugins'),
                'manager.plugins'
            ];
        }

        $templateMgr->assign('pageHierarchy', $pageCrumbs);
    }

    /**
     * Display verbs for the management interface.
     * @param array $verbs
     * @param mixed $request
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        $verbs = []; 
        
        if ($this->getEnabled($request)) { 
            $verbs[] = [
                'disable',
                __('manager.plugins.disable')
            ];
            $verbs[] = [
                'settings',
                __('plugins.generic.piwik.manager.settings')
            ];
        } else {
            $verbs[] = [
                'enable',
                __('manager.plugins.enable')
            ];
        }
        
        return $verbs;
    }

    /**
     * Determine whether or not this plugin is enabled.
     * @param mixed $request
     * @return bool
     */
    public function getEnabled($request = null): bool {
        // Lumera Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        
        $journal = $request->getJournal();
        if (!$journal) {
            return false;
        }
        return (bool) $this->getSetting((int) $journal->getId(), 'enabled');
    }

    /**
     * Set the enabled/disabled state of this plugin.
     * @param bool $enabled
     * @param mixed $request
     * @return bool
     */
    public function setEnabled(bool $enabled, $request = null): bool { 
        return parent::setEnabled($enabled, $request); 
    }

    /**
     * Insert Piwik page tag to footer.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function insertFooter($hookName, $params) {
        if ($this->getEnabled()) {
            // Lumera Singleton Fallback
            $request = Application::get()->getRequest();
            $journal = $request->getJournal();
            
            if (!$journal) {
                return false;
            }

            $smarty = $params[1];
            $output = $params[2];
            $journalId = (int) $journal->getId();
            $journalPath = (string) $journal->getPath();
            $piwikSiteId = (string) $this->getSetting($journalId, 'piwikSiteId');
            $piwikUrl = (string) $this->getSetting($journalId, 'piwikUrl');
            
            if ($piwikSiteId !== '' && $piwikUrl !== '') {
                $output .= '<!-- Piwik -->' .
                    '<script type="text/javascript">' .
                    'var pkBaseURL = "' . $piwikUrl . '/";' .
                    'document.write(unescape("%3Cscript src=\'" + pkBaseURL + "piwik.js\' type=\'text/javascript\'%3E%3C/script%3E"));' .
                    '</script><script type="text/javascript">' .
                    'try {' .
                    'var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", ' . $piwikSiteId . ');' .
                    'piwikTracker.setDocumentTitle("' . $journalPath . '");' .
                    'piwikTracker.trackPageView();' .
                    'piwikTracker.enableLinkTracking();' .
                    '} catch( err ) {}' .
                    '</script><noscript><p><img src="' . $piwikUrl . '/piwik.php?idsite=' . $piwikSiteId . '" style="border:0" alt="" /></p></noscript>' .
                    '<!-- End Piwik Tag -->';
            }
        }
        return false;
    }

    /**
     * Perform management functions.
     * @param string $verb
     * @param array $args
     * @param string|null $message
     * @param array|null $messageParams
     * @param mixed $request
     * @return bool
     */
    public function manage(string $verb, array $args, ?string &$message = null, ?array &$messageParams = null, $request = null): bool {
        // Lumera Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->register_function('plugin_url', [$this, 'smartyPluginUrl']);
        $journal = $request->getJournal();
        
        if (!$journal) {
            return false;
        }

        $returner = true;

        switch ($verb) {
            case 'enable':
                $this->setEnabled(true, $request);
                $returner = false;
                break;
            case 'disable':
                $this->setEnabled(false, $request);
                $returner = false;
                break;
            case 'settings':
                if ($this->getEnabled($request)) {
                    $this->import('PiwikSettingsForm');
                    $form = new PiwikSettingsForm($this, (int) $journal->getId());
                    if ($request->getUserVar('save')) {
                        $form->readInputData();
                        if ($form->validate()) {
                            $form->execute();
                            $request->redirect(null, 'manager', 'plugins', [$this->getCategory()]);
                            return false;
                        } else {
                            $this->setBreadcrumbs(true);
                            $form->display($request);
                        }
                    } else {
                        $this->setBreadcrumbs(true);
                        $form->initData();
                        $form->display($request);
                    }
                } else {
                    $request->redirect(null, 'manager');
                }
                break;
            default:
                $request->redirect(null, 'manager');
        }
        return $returner;
    }

}
?>