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
 * @brief ExternalFeed plugin class
 * [WIZDAM] MODERNIZED FOR PHP 8.x
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class ExternalFeedPlugin extends GenericPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ExternalFeedPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::ExternalFeedPlugin(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Called as a plugin is registered to the registry
     * @param $category String Name of category plugin was registered to
     * @return boolean True iff plugin initialized successfully
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        if ($success) {
            $this->import('ExternalFeedDAO');
    
            // [WIZDAM FIX] Daftarkan DAO hanya setelah aplikasi siap
            // bukan langsung instansiasi saat plugin loading
            HookRegistry::register('PluginRegistry::loadCategory', array($this, 'callbackLoadCategory'));
            HookRegistry::register('TemplateManager::display', array($this, 'displayHomepage'));
            HookRegistry::register('Templates::Manager::Index::ManagementPages', array($this, 'displayManagerLink'));
    
            // Registrasi DAO dilakukan via hook saat DB sudah siap
            HookRegistry::register('DAORegistry::registerDAO', function() {
                $externalFeedDao = new ExternalFeedDAO($this->getName());
                DAORegistry::registerDAO('ExternalFeedDAO', $externalFeedDao);
            });
        }
        return $success;
    }

    /**
     * Get the display name of the plugin.
     * @see PKPPlugin::getDisplayName()
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.externalFeed.displayName');
    }

    /**
     * Get a description of the plugin.
     * @see PKPPlugin::getDescription()
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.externalFeed.description');
    }

    /**
     * Get the filename of the ADODB schema for this plugin.
     * @see PKPPlugin::getInstallSchemaFile()
     * @return string|null
     */
    public function getInstallSchemaFile(): ?string {
        return $this->getPluginPath() . '/' . 'schema.xml';
    }

    /**
     * Get the filename of the default CSS stylesheet for this plugin.
     * @see PKPPlugin::getDefaultStyleSheetFile()
     * @return string|null
     */
    public function getDefaultStyleSheetFile(): ?string {
        return $this->getPluginPath() . '/' . 'css/externalFeed.css';
    }

    /**
     * Get the filename of the CSS stylesheet for this plugin.
     * @see PKPPlugin::getStyleSheetFile()
     * @return string|null
     */
    public function getStyleSheetFile() {
        $journal = Request::getJournal();
        $journalId = $journal ? $journal->getId() : 0;
        $styleSheet = $this->getSetting($journalId, 'externalFeedStyleSheet');

        if (empty($styleSheet)) {
            return $this->getDefaultStyleSheetFile();
        } else {
            import('classes.file.PublicFileManager');
            $fileManager = new PublicFileManager();
            return $fileManager->getJournalFilesPath($journalId) . '/' . $styleSheet['uploadName'];
        }
    }

    /**
     * Extend the {url ...} smarty to support externalFeed plugin.
     * @param array $params
     * @param Smarty $smarty
     * @return string
     */
    public function smartyPluginUrl(array $params, $smarty): string {
        $path = array($this->getCategory(), $this->getName());
        if (is_array($params['path'])) {
            $params['path'] = array_merge($path, $params['path']);
        } elseif (!empty($params['path'])) {
            $params['path'] = array_merge($path, array($params['path']));
        } else {
            $params['path'] = $path;
        }

        if (!empty($params['id'])) {
            $params['path'] = array_merge($params['path'], array($params['id']));
            unset($params['id']);
        }
        return $smarty->smartyUrl($params, $smarty);
    }

    /**
     * Set the page's breadcrumbs for the management interface.
     * @param bool $isSubclass
     */
    public function setBreadcrumbs($isSubclass = false) {
        $templateMgr = TemplateManager::getManager();
        $pageCrumbs = array(
            array(
                Request::url(null, 'user'),
                'navigation.user'
            ),
            array(
                Request::url(null, 'manager'),
                'user.role.manager'
            )
        );
        if ($isSubclass) $pageCrumbs[] = array(
            Request::url(null, 'manager', 'plugin', array('generic', $this->getName(), 'feeds')),
            $this->getDisplayName(),
            true
        );

        $templateMgr->assign('pageHierarchy', $pageCrumbs);
    }

    /**
     * Register as a block plugin
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function callbackLoadCategory($hookName, $args) {
        $category = $args[0];
        $plugins =& $args[1];
    
        if ($category === 'blocks') {
            $this->import('ExternalFeedBlockPlugin');
            $blockPlugin = new ExternalFeedBlockPlugin($this->getName());
            
            // FIX: Panggil register() dulu agar pluginPath dan context terisi
            $blockPlugin->register('blocks', $this->getPluginPath());
            
            // Sekarang getSeq() dan getPluginPath() mengembalikan nilai yang benar
            $seq = $blockPlugin->getSeq();
            $pluginPath = $blockPlugin->getPluginPath();
            
            // Gunakan nama unik sebagai key untuk hindari overwrite
            if (!isset($plugins[$seq])) {
                $plugins[$seq] = array();
            }
            $plugins[$seq][$pluginPath] = $blockPlugin;
        }
        return false;
    }

    /**
     * Display verbs for the management interface.
     * @see PKPPlugin::getManagementVerbs()
     * @param array $verbs
     * @param null $request
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        $verbs = parent::getManagementVerbs($verbs, $request);
        if ($this->getEnabled($request)) {
            $verbs[] = array('feeds', __('plugins.generic.externalFeed.manager.feeds'));
            $verbs[] = array('settings', __('plugins.generic.externalFeed.manager.settings'));
        }
        return $verbs;
    }

    /**
     * Display external feed content on journal homepage.
     * Uses Smarty template for rendering instead of string concatenation.
     * [WIZDAM LOGIC PRESERVED] Firewall Bypass logic maintained.
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function displayHomepage($hookName, $args) {
        $journal = Request::getJournal();
        $journalId = $journal ? $journal->getId() : 0;

        $request = Registry::get('request');
        if ($this->getEnabled($request)) {
            // [WIZDAM FIX] Replaced is_a with instanceof
            if (!($request->getRouter() instanceof PKPPageRouter)) return false;
            
            $requestedPage = $request->getRequestedPage();

            if (empty($requestedPage) || $requestedPage == 'index') {
                $externalFeedDao = DAORegistry::getDAO('ExternalFeedDAO');
                $this->import('simplepie.SimplePie');
                
                // PENTING: Import PKPString agar tidak error Class Not Found
                import('lib.pkp.classes.core.PKPString');

                $feeds = $externalFeedDao->getExternalFeedsByJournalId($journal->getId());
                $processedFeeds = array(); 
                $sectionIdSlug = ''; 

                while ($currentFeed = $feeds->next()) {
                    if (!$currentFeed->getDisplayHomepage()) continue;

                    // --- INISIALISASI SIMPLEPIE ---
                    $feed = new SimplePie();
                    $feedUrl = $currentFeed->getUrl();
                    
                    // Logic Bypass Firewall & Koneksi
                    $feedParts = parse_url($feedUrl);
                    $feedHost = isset($feedParts['host']) ? $feedParts['host'] : '';
                    $currentHost = $_SERVER['HTTP_HOST']; 
                    $cleanFeedHost = str_replace('www.', '', $feedHost);
                    $cleanCurrentHost = str_replace('www.', '', $currentHost);

                    $curlOptions = array(
                        CURLOPT_SSL_VERIFYHOST => 0, 
                        CURLOPT_SSL_VERIFYPEER => 0, 
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_MAXREDIRS      => 5,
                        CURLOPT_TIMEOUT        => 30,
                        CURLOPT_ENCODING       => '',
                    );

                    if ($feedHost && stripos($cleanCurrentHost, $cleanFeedHost) !== false) {
                        $serverIP = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1';
                        
                        $curlOptions[CURLOPT_RESOLVE] = array(
                            $feedHost . ":443:" . $serverIP,
                            $feedHost . ":80:" . $serverIP
                        );
                        $feed->set_useragent('SangiaFeedSystem_Internal_Verif');
                    } else {
                        $feed->set_useragent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
                    }

                    $feed->set_curl_options($curlOptions);
                    $feed->set_feed_url($feedUrl);
                    $feed->set_cache_location(CacheManager::getFileCachePath());
                    $feed->enable_order_by_date(false);
                    $feed->init();

                    // --- LOGIC SLUGIFY MENGGUNAKAN PKPString (FIX ERROR) ---
                    if (empty($sectionIdSlug)) {
                        $rawTitle = $currentFeed->getLocalizedTitle(); 
                        
                        $slug = PKPString::strtolower($rawTitle); 
                        $slug = str_replace(array('&', 'amp;'), '', $slug); 
                        $slug = PKPString::regexp_replace('/[^a-z0-9]+/', '-', $slug);
                        $slug = trim($slug, '-'); 
                        
                        if (empty($slug)) { $slug = 'external-feed'; }
                        
                        $sectionIdSlug = $slug;
                    }

                    // --- DATA PREPARATION FOR SMARTY ---
                    if ($currentFeed->getLimitItems()) {
                        $recentItemsLimit = $currentFeed->getRecentItems();
                    } else {
                        $recentItemsLimit = 0;
                    }

                    $hasItems = ($feed->get_item_quantity() > 0);
                    
                    if ($hasItems) {
                        $processedFeeds[] = array(
                            'title' => $currentFeed->getLocalizedTitle(),
                            'feed' => $feed,
                            'featured_items' => $feed->get_items(0, 1),
                            'recent_items' => $feed->get_items(1, $recentItemsLimit),
                            'has_items' => true
                        );
                    }
                }

                $templateManager = $args[0];
                $templateManager->addStyleSheet(Request::getBaseUrl() . '/' . $this->getStyleSheetFile());
                $templateManager->assign('processedExternalFeeds', $processedFeeds);
                
                // Kirim ID Slug ke template
                $templateManager->assign('externalFeedSectionId', $sectionIdSlug);

                // FIX PATH: Pastikan path ini benar (di dalam folder templates)
                $output = $templateManager->fetch($this->getTemplatePath() . 'templates/homepageFeeds.tpl');

                $templateManager->assign('externalHomeContent', $output);
            }
        }
    }

    /**
     * Display management link for JM.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function displayManagerLink($hookName, $params) {
        $request = Registry::get('request'); // FIX: Ambil $request
        if ($this->getEnabled($request)) { // FIX: Teruskan $request
            $smarty = $params[1];
            $output =& $params[2]; // Reference required to append output
            
            // [WIZDAM FIX] Gunakan fungsi global __() alih-alih memanggil 
            // fungsi Smarty non-static secara statis yang memicu Fatal Error di PHP 8.4.
            $translatedText = __('plugins.generic.externalFeed.manager.feeds');
            
            $output .= '<li><a href="' . $this->smartyPluginUrl(array('op'=>'plugin', 'path'=>'feeds'), $smarty) . '">' . $translatedText . '</a></li>';
        }
        return false;
    }

    /**
     * Execute a management verb on this plugin
     * [WIZDAM PROTOCOL] Used NotificationManager for user feedback.
     * @param string $verb
     * @param array $args
     * @param string $message
     * @param array $messageParams
     * @param null|Request $request
     * @return bool
     */
    public function manage(string $verb, array $args, string $message, array $messageParams, $request = NULL): bool {
        // FIX 4: Perbaiki logika parent::manage() — teruskan $request
        // dan jangan blokir semua verb
        if ($verb !== 'enable' && $verb !== 'disable') {
            if (!$this->getEnabled($request)) {
                fatalError('Invalid management action on disabled plug-in!');
            }
        } else {
            return parent::manage($verb, $args, $message, $messageParams, $request);
        }
    
        AppLocale::requireComponents(
            LOCALE_COMPONENT_APPLICATION_COMMON,
            LOCALE_COMPONENT_PKP_MANAGER,
            LOCALE_COMPONENT_PKP_USER
        );
        $templateMgr = TemplateManager::getManager();
        $templateMgr->register_function('plugin_url', array($this, 'smartyPluginUrl'));
        $journal = Request::getJournal();
        $journalId = $journal->getId();
    
        switch ($verb) {
            case 'delete':
                if (!empty($args)) {
                    $externalFeedId = !isset($args) || empty($args) ? null : (int) $args[0];
                    $externalFeedDao = DAORegistry::getDAO('ExternalFeedDAO');
                    if ($externalFeedDao->getExternalFeedJournalId($externalFeedId) == $journalId) {
                        $externalFeedDao->deleteExternalFeedById($externalFeedId);
                    }
                }
                Request::redirect(null, 'manager', 'plugin', array('generic', $this->getName(), 'feeds'));
                return true;
            case 'move':
                $externalFeedId = !isset($args) || empty($args) ? null : (int) $args[0];
                $externalFeedDao = DAORegistry::getDAO('ExternalFeedDAO');
                if (($externalFeedId != null && $externalFeedDao->getExternalFeedJournalId($externalFeedId) == $journalId)) {
                    $feed = $externalFeedDao->getExternalFeed($externalFeedId);
                    $direction = Request::getUserVar('dir');
                    if ($direction != null) {
                        $isDown = ($direction=='d');
                        $feed->setSeq($feed->getSeq()+($isDown?1.5:-1.5));
                        $externalFeedDao->updateExternalFeed($feed);
                        $externalFeedDao->resequenceExternalFeeds($feed->getJournalId());
                    }
                }
                Request::redirect(null, 'manager', 'plugin', array('generic', $this->getName(), 'feeds'));
                return true;
            case 'create':
            case 'edit':
                $externalFeedId = !isset($args) || empty($args) ? null : (int) $args[0];
                $externalFeedDao = DAORegistry::getDAO('ExternalFeedDAO');

                if (($externalFeedId != null && $externalFeedDao->getExternalFeedJournalId($externalFeedId) == $journalId) || ($externalFeedId == null)) {
                    $this->import('ExternalFeedForm');

                    if ($externalFeedId == null) {
                        $templateMgr->assign('externalFeedTitle', 'plugins.generic.externalFeed.manager.createTitle');
                    } else {
                        $templateMgr->assign('externalFeedTitle', 'plugins.generic.externalFeed.manager.editTitle');
                    }

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
                    Request::redirect(null, 'manager', 'plugin', array('generic', $this->getName(), 'feeds'));
                }
                return true;
            case 'update':
                $externalFeedId = Request::getUserVar('feedId') == null ? null : (int) Request::getUserVar('feedId');
                $externalFeedDao = DAORegistry::getDAO('ExternalFeedDAO');

                if (($externalFeedId != null && $externalFeedDao->getExternalFeedJournalId($externalFeedId) == $journalId) || $externalFeedId == null) {
                    $this->import('ExternalFeedForm');
                    $externalFeedForm = new ExternalFeedForm($this, $externalFeedId);
                    $externalFeedForm->readInputData();

                    if ($externalFeedForm->validate()) {
                        $externalFeedForm->execute();
                        
                        // [WIZDAM] Use NotificationManager
                        import('classes.notification.NotificationManager');
                        $notificationMgr = new NotificationManager();
                        $notificationMgr->createTrivialNotification(
                            $request->getUser()->getId(),
                            NOTIFICATION_TYPE_SUCCESS,
                            array('contents' => __('plugins.generic.externalFeed.manager.saved'))
                        );

                        if (Request::getUserVar('createAnother')) {
                            Request::redirect(null, 'manager', 'plugin', array('generic', $this->getName(), 'create'));
                        } else {
                            Request::redirect(null, 'manager', 'plugin', array('generic', $this->getName(), 'feeds'));
                        }
                    } else {
                        if ($externalFeedId == null) {
                            $templateMgr->assign('externalFeedTitle', 'plugins.generic.externalFeed.manager.createTitle');
                        } else {
                            $templateMgr->assign('externalFeedTitle', 'plugins.generic.externalFeed.manager.editTitle');
                        }
                        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
                        $journalSettings = $journalSettingsDao->getJournalSettings($journalId);

                        $this->setBreadcrumbs(true);
                        $templateMgr->assign('journalSettings', $journalSettings);
                        $externalFeedForm->display();
                    }
                } else {
                    Request::redirect(null, 'manager', 'plugin', array('generic', $this->getName(), 'feeds'));
                }
                return true;
            case 'settings':
                $this->import('ExternalFeedSettingsForm');
                $form = new ExternalFeedSettingsForm($this, $journal->getId());
                if (Request::getUserVar('save')) {
                    // FIX 5: Proses form sebelum redirect
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        import('classes.notification.NotificationManager');
                        $notificationMgr = new NotificationManager();
                        $notificationMgr->createTrivialNotification(
                            $request->getUser()->getId(),
                            NOTIFICATION_TYPE_SUCCESS,
                            array('contents' => __('plugins.generic.externalFeed.manager.settingsSaved'))
                        );
                        Request::redirect(null, 'manager', 'plugin', array('generic', $this->getName(), 'feeds'));
                    } else {
                        $this->setBreadcrumbs(true); // FIX 3: lowercase 'c'
                        $form->display();
                    }
                } elseif (Request::getUserVar('uploadStyleSheet')) {
                    $form->uploadStyleSheet();
                } elseif (Request::getUserVar('deleteStyleSheet')) {
                    $form->deleteStyleSheet();
                } else {
                    $this->setBreadcrumbs(true); // FIX 3: lowercase 'c'
                    $form->initData();
                    $form->display();
                }
                return true;
    
            case 'feeds':
            default:
                $this->import('ExternalFeed');
                $rangeInfo = Handler::getRangeInfo('feeds');
                $externalFeedDao = DAORegistry::getDAO('ExternalFeedDAO');
                $feeds = $externalFeedDao->getExternalFeedsByJournalId($journalId, $rangeInfo);
                $templateMgr->assign('feeds', $feeds);
                $this->setBreadcrumbs(); // FIX 3: lowercase 'c'
                $templateMgr->display($this->getTemplatePath() . 'templates/externalFeeds.tpl');
                return true;
        }
    }
}
?>