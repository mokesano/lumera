<?php
declare(strict_types=1);

/**
 * @file plugins/generic/externalFeed/ExternalFeedPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ExternalFeedPlugin
 * @ingroup plugins_generic_externalFeed
 *
 * @brief ExternalFeed plugin class.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class ExternalFeedPlugin extends GenericPlugin {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function ExternalFeedPlugin() {
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
        if ($success) {
            $this->import('ExternalFeedDAO');
            
            // [LUMERA FIX] Standard DAO registration. The anonymous hook was fundamentally flawed.
            $externalFeedDao = new ExternalFeedDAO($this->getName());
            DAORegistry::registerDAO('ExternalFeedDAO', $externalFeedDao);

            HookRegistry::register('PluginRegistry::loadCategory', [$this, 'callbackLoadCategory']);
            HookRegistry::register('TemplateManager::display', [$this, 'displayHomepage']);
            HookRegistry::register('Templates::Manager::Index::ManagementPages', [$this, 'displayManagerLink']);
        }
        return $success;
    }

    /**
     * Get the display name of the plugin.
     * @return string
     * @see PKPPlugin::getDisplayName()
     */
    public function getDisplayName(): string {
        return __('plugins.generic.externalFeed.displayName');
    }

    /**
     * Get a description of the plugin.
     * @return string
     * @see PKPPlugin::getDescription()
     */
    public function getDescription(): string {
        return __('plugins.generic.externalFeed.description');
    }

    /**
     * Get the filename of the ADODB schema for this plugin.
     * @return string|null
     * @see PKPPlugin::getInstallSchemaFile()
     */
    public function getInstallSchemaFile(): ?string {
        return $this->getPluginPath() . '/schema.xml';
    }

    /**
     * Get the filename of the default CSS stylesheet for this plugin.
     * @return string|null
     * @see PKPPlugin::getDefaultStyleSheetFile()
     */
    public function getDefaultStyleSheetFile(): ?string {
        return $this->getPluginPath() . '/css/externalFeed.css';
    }

    /**
     * Get the filename of the CSS stylesheet for this plugin.
     * @return string|null
     * @see PKPPlugin::getStyleSheetFile()
     */
    public function getStyleSheetFile() {
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        $journalId = $journal !== null ? (int) $journal->getId() : 0;
        $styleSheet = $this->getSetting($journalId, 'externalFeedStyleSheet');

        if (empty($styleSheet)) {
            return $this->getDefaultStyleSheetFile();
        }
        
        import('classes.file.PublicFileManager');
        $fileManager = new PublicFileManager();
        return $fileManager->getJournalFilesPath($journalId) . '/' . (string) $styleSheet['uploadName'];
    }

    /**
     * Extend the {url ...} smarty to support externalFeed plugin.
     * @param array $params
     * @param object $smarty
     * @return string
     */
    public function smartyPluginUrl(array $params, $smarty): string {
        $path = [$this->getCategory(), $this->getName()];
        if (is_array($params['path'])) {
            $params['path'] = array_merge($path, $params['path']);
        } elseif (!empty($params['path'])) {
            $params['path'] = array_merge($path, [(string) $params['path']]);
        } else {
            $params['path'] = $path;
        }

        if (!empty($params['id'])) {
            $params['path'] = array_merge($params['path'], [(string) $params['id']]);
            unset($params['id']);
        }
        return $smarty->smartyUrl($params, $smarty);
    }

    /**
     * Set the page's breadcrumbs for the management interface.
     * @param bool $isSubclass
     * @return void
     */
    public function setBreadcrumbs($isSubclass = false) {
        $request = Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager();
        $pageCrumbs = [
            [
                $request->getRouter()->url($request, null, 'user'),
                'navigation.user'
            ],
            [
                $request->getRouter()->url($request, null, 'manager'),
                'user.role.manager'
            ]
        ];
        if ($isSubclass) {
            $pageCrumbs[] = [
                $request->getRouter()->url($request, null, 'manager', 'plugin', ['generic', $this->getName(), 'feeds']),
                $this->getDisplayName(),
                true
            ];
        }

        $templateMgr->assign('pageHierarchy', $pageCrumbs);
    }

    /**
     * Register as a block plugin.
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function callbackLoadCategory($hookName, $args) {
        $category = $args[0];
        $plugins =& $args[1]; // Reference required hook system
    
        if ($category === 'blocks') {
            $this->import('ExternalFeedBlockPlugin');
            $blockPlugin = new ExternalFeedBlockPlugin($this->getName());
            
            $blockPlugin->register('blocks', $this->getPluginPath());
            
            $seq = $blockPlugin->getSeq();
            $pluginPath = $blockPlugin->getPluginPath();
            
            if (!isset($plugins[$seq])) {
                $plugins[$seq] = [];
            }
            $plugins[$seq][$pluginPath] = $blockPlugin;
        }
        return false;
    }

    /**
     * Display verbs for the management interface.
     * @param array $verbs
     * @param object|null $request
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        $verbs = parent::getManagementVerbs($verbs, $request);
        if ($this->getEnabled($request)) {
            $verbs[] = ['feeds', __('plugins.generic.externalFeed.manager.feeds')];
            $verbs[] = ['settings', __('plugins.generic.externalFeed.manager.settings')];
        }
        return $verbs;
    }

    /**
     * Display external feed content on journal homepage.
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function displayHomepage($hookName, $args) {
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        
        if ($journal === null || !$this->getEnabled($request)) {
            return false;
        }

        if (!($request->getRouter() instanceof PKPPageRouter)) {
            return false;
        }
        
        $requestedPage = $request->getRequestedPage();

        if (empty($requestedPage) || $requestedPage === 'index') {
            /** @var ExternalFeedDAO $externalFeedDao */
            $externalFeedDao = DAORegistry::getDAO('ExternalFeedDAO');

            require_once Core::getBaseDir() . '/lib/wizdam/library/autoload.php';
            if (!class_exists('SimplePie') && class_exists('SimplePie\SimplePie')) {
                class_alias('SimplePie\SimplePie', 'SimplePie');
            }

            import('lib.pkp.classes.core.PKPString');
            $feeds = $externalFeedDao->getExternalFeedsByJournalId((int) $journal->getId());
            $processedFeeds = []; 
            $sectionIdSlug = ''; 

            while ($currentFeed = $feeds->next()) {
                if (!$currentFeed->getDisplayHomepage()) {
                    continue;
                }

                $feed = new SimplePie(); // @deprecated since SimplePie 1.7.0, use "SimplePie\SimplePie" instead
                $feedUrl = (string) $currentFeed->getUrl();
                $feedParts = parse_url($feedUrl);
                $feedHost = $feedParts['host'] ?? '';
                
                // [LUMERA FIX] Null-safety for $_SERVER variables
                $currentHost = $_SERVER['HTTP_HOST'] ?? ''; 
                $cleanFeedHost = str_replace('www.', '', $feedHost);
                $cleanCurrentHost = str_replace('www.', '', $currentHost);

                $curlOptions = [
                    CURLOPT_SSL_VERIFYHOST => 0, 
                    CURLOPT_SSL_VERIFYPEER => 0, 
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS      => 5,
                    CURLOPT_TIMEOUT        => 30,
                    CURLOPT_ENCODING       => '',
                ];

                if ($feedHost !== '' && stripos($cleanCurrentHost, $cleanFeedHost) !== false) {
                    $serverIP = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';
                    $curlOptions[CURLOPT_RESOLVE] = [
                        $feedHost . ":443:" . $serverIP,
                        $feedHost . ":80:" . $serverIP
                    ];
                    $feed->set_useragent('SangiaFeed');
                } else {
                    $feed->set_useragent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
                }

                $feed->set_curl_options($curlOptions);
                $feed->set_feed_url($feedUrl);
                $feed->set_cache_location(CacheManager::getFileCachePath());
                $feed->enable_order_by_date(false);
                $feed->init();

                if (empty($sectionIdSlug)) {
                    $rawTitle = (string) $currentFeed->getLocalizedTitle(); 
                    $slug = PKPString::strtolower($rawTitle); 
                    $slug = str_replace(['&', 'amp;'], '', $slug); 
                    $slug = PKPString::regexp_replace('/[^a-z0-9]+/', '-', $slug);
                    $slug = trim($slug, '-'); 
                    $sectionIdSlug = empty($slug) ? 'external-feed' : $slug;
                }

                $recentItemsLimit = $currentFeed->getLimitItems() ? (int) $currentFeed->getRecentItems() : 0;
                $hasItems = $feed->get_item_quantity() > 0;
                
                if ($hasItems) {
                    $processedFeeds[] = [
                        'title' => (string) $currentFeed->getLocalizedTitle(),
                        'feed' => $feed,
                        'featured_items' => $feed->get_items(0, 1),
                        'recent_items' => $feed->get_items(1, $recentItemsLimit),
                        'has_items' => true
                    ];
                }
            }

            if (!empty($processedFeeds)) {
                $templateManager = $args[0];
                $templateManager->addStyleSheet(
                    'externalFeedHomepageCss',
                    $request->getBaseUrl() . '/' . $this->getStyleSheetFile()
                );
                $templateManager->assign('processedExternalFeeds', $processedFeeds);
                $templateManager->assign('externalFeedSectionId', $sectionIdSlug);

                $output = $templateManager->fetch($this->getTemplatePath() . 'templates/homepageFeeds.tpl');
                $templateManager->assign('externalHomeContent', $output);
            }
        }
        return false;
    }

    /**
     * Display management link for JM.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function displayManagerLink($hookName, $params) {
        $request = Application::get()->getRequest();

        if ($this->getEnabled($request)) {
            $smarty = $params[1];
            $output =& $params[2]; // Reference required to append output

            $translatedText = __('plugins.generic.externalFeed.manager.feeds');
            $urlParams = ['op' => 'plugin', 'path' => 'feeds'];
            
            $output .= '<li><a href="' . $this->smartyPluginUrl($urlParams, $smarty) . '">' . htmlspecialchars($translatedText, ENT_QUOTES, 'UTF-8') . '</a></li>';
        }
        return false;
    }

    /**
     * Execute a management verb on this plugin.
     * @param string $verb
     * @param array $args
     * @param string|null $message
     * @param array|null $messageParams
     * @param object|null $request
     * @return bool
     */
    public function manage(string $verb, array $args, ?string &$message = null, ?array &$messageParams = null, $request = null): bool {
        $request = $request ?? Application::get()->getRequest();
        
        if ($verb !== 'enable' && $verb !== 'disable') {
            if (!$this->getEnabled($request)) {
                fatalError('Invalid management action on disabled plug-in!');
            }
        } else {
            return parent::manage($verb, $args, $message, $messageParams, $request);
        }
    
        AppLocale::requireComponents(
            LOCALE_COMPONENT_APPLICATION_COMMON,
            LOCALE_COMPONENT_CORE_MANAGER,
            LOCALE_COMPONENT_CORE_USER
        );
        
        $templateMgr = TemplateManager::getManager();
        $templateMgr->register_function('plugin_url', [$this, 'smartyPluginUrl']);
        
        $journal = $request->getJournal();
        if ($journal === null) {
            return false;
        }
        $journalId = (int) $journal->getId();
    
        switch ($verb) {
            case 'delete':
                if (!empty($args)) {
                    $externalFeedId = (int) $args[0];
                    /** @var ExternalFeedDAO $externalFeedDao */
                    $externalFeedDao = DAORegistry::getDAO('ExternalFeedDAO');
                    if ($externalFeedDao->getExternalFeedJournalId($externalFeedId) === $journalId) {
                        $externalFeedDao->deleteExternalFeedById($externalFeedId);
                    }
                }
                $request->redirect(null, 'manager', 'plugin', ['generic', $this->getName(), 'feeds']);
                return true;
                
            case 'move':
                $externalFeedId = !empty($args) ? (int) $args[0] : null;
                /** @var ExternalFeedDAO $externalFeedDao */
                $externalFeedDao = DAORegistry::getDAO('ExternalFeedDAO');
                
                if ($externalFeedId !== null && $externalFeedDao->getExternalFeedJournalId($externalFeedId) === $journalId) {
                    $feed = $externalFeedDao->getExternalFeed($externalFeedId);
                    $direction = $request->getUserVar('dir');
                    if ($direction !== null) {
                        $isDown = ($direction === 'd');
                        $feed->setSeq($feed->getSeq() + ($isDown ? 1.5 : -1.5));
                        $externalFeedDao->updateExternalFeed($feed);
                        $externalFeedDao->resequenceExternalFeeds($feed->getJournalId());
                    }
                }
                $request->redirect(null, 'manager', 'plugin', ['generic', $this->getName(), 'feeds']);
                return true;
                
            case 'create':
            case 'edit':
                $externalFeedId = !empty($args) ? (int) $args[0] : null;
                /** @var ExternalFeedDAO $externalFeedDao */
                $externalFeedDao = DAORegistry::getDAO('ExternalFeedDAO');

                if (($externalFeedId !== null && $externalFeedDao->getExternalFeedJournalId($externalFeedId) === $journalId) || $externalFeedId === null) {
                    $this->import('ExternalFeedForm');

                    $templateMgr->assign('externalFeedTitle', $externalFeedId === null ? 'plugins.generic.externalFeed.manager.createTitle' : 'plugins.generic.externalFeed.manager.editTitle');

                    /** @var JournalSettingsDAO $journalSettingsDao */
                    $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
                    $journalSettings = $journalSettingsDao->getJournalSettings($journalId);

                    $externalFeedForm = new ExternalFeedForm($this, $externalFeedId);
                    if ($externalFeedForm->isLocaleResubmit()) {
                        $externalFeedForm->readInputData();
                    } else {
                        $externalFeedForm->initData();
                    }
                    $this->setBreadcrumbs(true);
                    $templateMgr->assign('journalSettings', $journalSettings);
                    $externalFeedForm->display();
                } else {
                    $request->redirect(null, 'manager', 'plugin', ['generic', $this->getName(), 'feeds']);
                }
                return true;
                
            case 'update':
                $externalFeedId = $request->getUserVar('feedId') !== null ? (int) $request->getUserVar('feedId') : null;
                /** @var ExternalFeedDAO $externalFeedDao */
                $externalFeedDao = DAORegistry::getDAO('ExternalFeedDAO');

                if (($externalFeedId !== null && $externalFeedDao->getExternalFeedJournalId($externalFeedId) === $journalId) || $externalFeedId === null) {
                    $this->import('ExternalFeedForm');
                    $externalFeedForm = new ExternalFeedForm($this, $externalFeedId);
                    $externalFeedForm->readInputData();

                    if ($externalFeedForm->validate()) {
                        $externalFeedForm->execute();
                        
                        import('classes.notification.NotificationManager');
                        $notificationMgr = new NotificationManager();
                        $notificationMgr->createTrivialNotification(
                            $request->getUser()->getId(),
                            NOTIFICATION_TYPE_SUCCESS,
                            ['contents' => __('plugins.generic.externalFeed.manager.saved')]
                        );

                        if ($request->getUserVar('createAnother')) {
                            $request->redirect(null, 'manager', 'plugin', ['generic', $this->getName(), 'create']);
                        } else {
                            $request->redirect(null, 'manager', 'plugin', ['generic', $this->getName(), 'feeds']);
                        }
                    } else {
                        $templateMgr->assign('externalFeedTitle', $externalFeedId === null ? 'plugins.generic.externalFeed.manager.createTitle' : 'plugins.generic.externalFeed.manager.editTitle');
                        /** @var JournalSettingsDAO $journalSettingsDao */
                        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
                        $journalSettings = $journalSettingsDao->getJournalSettings($journalId);

                        $this->setBreadcrumbs(true);
                        $templateMgr->assign('journalSettings', $journalSettings);
                        $externalFeedForm->display();
                    }
                } else {
                    $request->redirect(null, 'manager', 'plugin', ['generic', $this->getName(), 'feeds']);
                }
                return true;
                
            case 'settings':
                $this->import('ExternalFeedSettingsForm');
                $form = new ExternalFeedSettingsForm($this, $journalId);
                
                if ($request->getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        import('classes.notification.NotificationManager');
                        $notificationMgr = new NotificationManager();
                        $notificationMgr->createTrivialNotification(
                            $request->getUser()->getId(),
                            NOTIFICATION_TYPE_SUCCESS,
                            ['contents' => __('plugins.generic.externalFeed.manager.settingsSaved')]
                        );
                        $request->redirect(null, 'manager', 'plugin', ['generic', $this->getName(), 'feeds']);
                    } else {
                        $this->setBreadcrumbs(true);
                        $form->display();
                    }
                } elseif ($request->getUserVar('uploadStyleSheet')) {
                    $form->uploadStyleSheet();
                } elseif ($request->getUserVar('deleteStyleSheet')) {
                    $form->deleteStyleSheet();
                } else {
                    $this->setBreadcrumbs(true);
                    $form->initData();
                    $form->display();
                }
                return true;
    
            case 'feeds':
            default:
                $this->import('ExternalFeed');
                $rangeInfo = Handler::getRangeInfo('feeds');
                /** @var ExternalFeedDAO $externalFeedDao */
                $externalFeedDao = DAORegistry::getDAO('ExternalFeedDAO');
                $feeds = $externalFeedDao->getExternalFeedsByJournalId($journalId, $rangeInfo);
                $templateMgr->assign('feeds', $feeds);
                $this->setBreadcrumbs();
                $templateMgr->display($this->getTemplatePath() . 'templates/externalFeeds.tpl');
                return true;
        }
    }

}
?>