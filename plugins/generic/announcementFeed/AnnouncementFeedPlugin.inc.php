<?php
declare(strict_types=1);

/**
 * @file plugins/generic/announcementFeed/AnnouncementFeedPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AnnouncementFeedPlugin
 * @ingroup plugins_generic_announcementFeed
 *
 * @brief Annoucement Feed plugin class
 * [WIZDAM EDITION] Modernized. PHP 8 Safe & Strict Standards.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class AnnouncementFeedPlugin extends GenericPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AnnouncementFeedPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::AnnouncementFeedPlugin(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Register plugin
     * @param string $category
     * @param string $path
     * @return bool
     */
    public function register(string $category, string $path): bool {
        if (parent::register($category, $path)) {
            if ($this->getEnabled()) {
                // [MODERNISASI] Callback tanpa &
                HookRegistry::register('TemplateManager::display', array($this, 'callbackAddLinks'));
                HookRegistry::register('PluginRegistry::loadCategory', array($this, 'callbackLoadCategory'));
            }
            return true;
        }
        return false;
    }

    /**
     * Get the display name of this plugin
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.announcementfeed.displayName');
    }

    /**
     * Get the description of this plugin
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.announcementfeed.description');
    }

    /**
     * Register as a block and gateway plugin
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function callbackLoadCategory($hookName, $args) {
        $category = $args[0];
        $plugins =& $args[1]; // [WIZDAM NOTE] Ini array pass-by-reference dari HookRegistry, biarkan & karena diperlukan untuk memodifikasi list plugin
        
        switch ($category) {
            case 'blocks':
                $this->import('AnnouncementFeedBlockPlugin');
                $blockPlugin = new AnnouncementFeedBlockPlugin($this->getName());
                $plugins[$blockPlugin->getSeq()][$blockPlugin->getPluginPath()] = $blockPlugin;
                break;
            case 'gateways':
                $this->import('AnnouncementFeedGatewayPlugin');
                $gatewayPlugin = new AnnouncementFeedGatewayPlugin($this->getName());
                $plugins[$gatewayPlugin->getSeq()][$gatewayPlugin->getPluginPath()] = $gatewayPlugin;
                break;
        }
        return false;
    }

    /**
     * Add feed links to page header.
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function callbackAddLinks($hookName, $args) {
        if ($this->getEnabled()) {
            // [MODERNISASI] Ambil request dengan cara yang benar
            $request = Application::getRequest();
            
            // Safety check for Router type
            $router = $request->getRouter();
            
            // [WIZDAM FIX] Replaced is_a with instanceof
            if (!($router instanceof PKPPageRouter)) return false;

            $templateManager = $args[0];
            $currentJournal = $templateManager->get_template_vars('currentJournal');
            
            $announcementsEnabled = $currentJournal ? $currentJournal->getSetting('enableAnnouncements') : false;
            $displayPage = $currentJournal ? $this->getSetting($currentJournal->getId(), 'displayPage') : null;
            $requestedPage = $request->getRequestedPage();

            // Logika untuk menentukan di halaman mana feed link muncul
            if ( $announcementsEnabled && (($displayPage == 'all') || ($displayPage == 'homepage' && (empty($requestedPage) || $requestedPage == 'index' || $requestedPage == 'announcement')) || ($displayPage == $requestedPage)) ) {

                $additionalHeadData = $templateManager->get_template_vars('additionalHeadData');

                $feedUrl1 = '<link rel="alternate" type="application/atom+xml" href="' . $request->url(null, 'gateway', 'plugin', array('AnnouncementFeedGatewayPlugin', 'atom')) . '" />';
                $feedUrl2 = '<link rel="alternate" type="application/rdf+xml" href="'.$currentJournal->getUrl().'/gateway/plugin/AnnouncementFeedGatewayPlugin/rss" />';
                $feedUrl3 = '<link rel="alternate" type="application/rss+xml" href="'.$currentJournal->getUrl().'/gateway/plugin/AnnouncementFeedGatewayPlugin/rss2" />';

                $templateManager->assign('additionalHeadData', $additionalHeadData."\n\t".$feedUrl1."\n\t".$feedUrl2."\n\t".$feedUrl3);
            }
        }

        return false;
    }

    /**
     * Display verbs for the management interface.
     * @param array $verbs
     * @param Request|null $request
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        $verbs = parent::getManagementVerbs($verbs, $request);

        if ($this->getEnabled($request)) {
            $verbs[] = array('settings', __('plugins.generic.announcementfeed.settings'));
        }
        
        return $verbs;
    }

    /**
     * Execute a management verb on this plugin.
     * [WIZDAM MODERNIZED] Used NotificationManager for user feedback.
     * @param string $verb
     * @param array $args
     * @param string $message
     * @param array $messageParams
     * @param Request|null $request
     * @return bool
     */
    public function manage(string $verb, array $args, string $message, array $messageParams, $request = null): bool {
        if (!parent::manage($verb, $args, $message, $messageParams, $request)) return false;
        
        switch ($verb) {
            case 'settings':
                $journal = $request->getJournal();

                $templateMgr = TemplateManager::getManager();
                $templateMgr->register_function('plugin_url', array($this, 'smartyPluginUrl'));

                $this->import('SettingsForm');
                $form = new SettingsForm($this, $journal->getId());

                if ($request->getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        
                        // [WIZDAM MODERNIZATION] Use NotificationManager
                        import('classes.notification.NotificationManager');
                        $notificationMgr = new NotificationManager();
                        $notificationMgr->createTrivialNotification(
                            $request->getUser()->getId(),
                            NOTIFICATION_TYPE_SUCCESS,
                            array('contents' => __('plugins.generic.announcementfeed.settings.saved'))
                        );
                        
                        return false;
                    } else {
                        $form->display();
                    }
                } else {
                    $form->initData();
                    $form->display();
                }
                return true;
            default:
                return false;
        }
    }
}
?>