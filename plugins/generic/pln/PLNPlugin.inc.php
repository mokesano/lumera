<?php
declare(strict_types=1);

/**
 * @file plugins/generic/pln/PLNPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PLNPlugin
 * @ingroup plugins_generic_pln
 *
 * @brief PLN plugin class
 */

import('lib.pkp.classes.plugins.GenericPlugin');
import('lib.pkp.classes.config.Config');
import('classes.article.PublishedArticle');
import('classes.issue.Issue');

define('PLN_PLUGIN_NAME', 'plnplugin');

// Defined here in case an upgrade doesn't pick up the default value.
define('PLN_DEFAULT_NETWORK', 'http://pkp-pln.lib.sfu.ca');
define('PLN_DEFAULT_STATUS_SUFFIX', '/docs/status');

define('PLN_PLUGIN_HTTP_STATUS_OK', 200);
define('PLN_PLUGIN_HTTP_STATUS_CREATED', 201);

define('PLN_PLUGIN_XML_NAMESPACE', 'http://pkp.sfu.ca/SWORD');

// Base IRI for the SWORD server. IRIs are constructed by appending to this constant.
define('PLN_PLUGIN_BASE_IRI', '/api/sword/2.0');
// Used to retrieve the service document
define('PLN_PLUGIN_SD_IRI', PLN_PLUGIN_BASE_IRI . '/sd-iri');
// Used to submit a deposit
define('PLN_PLUGIN_COL_IRI', PLN_PLUGIN_BASE_IRI . '/col-iri');
// Used to edit and query the state of a deposit
define('PLN_PLUGIN_CONT_IRI', PLN_PLUGIN_BASE_IRI . '/cont-iri');

define('PLN_PLUGIN_ARCHIVE_FOLDER', 'pln');

// Local statuses
define('PLN_PLUGIN_DEPOSIT_STATUS_NEW',                 0x00);
define('PLN_PLUGIN_DEPOSIT_STATUS_PACKAGED',            0x01);
define('PLN_PLUGIN_DEPOSIT_STATUS_TRANSFERRED',         0x02);

// Status on the processing server
define('PLN_PLUGIN_DEPOSIT_STATUS_RECEIVED',            0x04);
define('PLN_PLUGIN_DEPOSIT_STATUS_VALIDATED',           0x08); // was SYNCING
define('PLN_PLUGIN_DEPOSIT_STATUS_SENT',                0x10); // was SYNCED

// Status in the LOCKSS PLN 
define('PLN_PLUGIN_DEPOSIT_STATUS_LOCKSS_RECEIVED',     0x20); // was REMOTE_FAILURE
define('PLN_PLUGIN_DEPOSIT_STATUS_LOCKSS_SYNCING',      0x40); // was LOCAL_FAILURE
define('PLN_PLUGIN_DEPOSIT_STATUS_LOCKSS_AGREEMENT',    0x80); // was UPDATE

define('PLN_PLUGIN_DEPOSIT_STATUS_UPDATE',              0x100);

define('PLN_PLUGIN_DEPOSIT_OBJECT_ARTICLE', 'PublishedArticle');
define('PLN_PLUGIN_DEPOSIT_OBJECT_ISSUE', 'Issue');

define('PLN_PLUGIN_NOTIFICATION_TYPE_PLUGIN_BASE',      NOTIFICATION_TYPE_PLUGIN_BASE + 0x10000000);
define('PLN_PLUGIN_NOTIFICATION_TYPE_TERMS_UPDATED',    PLN_PLUGIN_NOTIFICATION_TYPE_PLUGIN_BASE + 0x0000001);
define('PLN_PLUGIN_NOTIFICATION_TYPE_ISSN_MISSING',     PLN_PLUGIN_NOTIFICATION_TYPE_PLUGIN_BASE + 0x0000002);
define('PLN_PLUGIN_NOTIFICATION_TYPE_HTTP_ERROR',       PLN_PLUGIN_NOTIFICATION_TYPE_PLUGIN_BASE + 0x0000003);
define('PLN_PLUGIN_NOTIFICATION_TYPE_CURL_MISSING',     PLN_PLUGIN_NOTIFICATION_TYPE_PLUGIN_BASE + 0x0000004);
define('PLN_PLUGIN_NOTIFICATION_TYPE_ZIP_MISSING',      PLN_PLUGIN_NOTIFICATION_TYPE_PLUGIN_BASE + 0x0000005);
define('PLN_PLUGIN_NOTIFICATION_TYPE_TAR_MISSING',      PLN_PLUGIN_NOTIFICATION_TYPE_PLUGIN_BASE + 0x0000006);

class PLNPlugin extends GenericPlugin {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PLNPlugin() {
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
     * Register the plugin with the application.
     * @copydoc LazyLoadPlugin::register()
     * @param string $category String
     * @param string $path String
     * @return bool
     */
    public function register(string $category, string $path): bool {
        if (!$this->php5Installed()) {
            return false;
        }
    
        $success = parent::register($category, $path);
        if ($success) {
            HookRegistry::register('TemplateManager::display', [$this, 'callbackTemplateDisplay']);
            HookRegistry::register('Templates::Manager::Setup::JournalArchiving', [$this, 'callbackJournalArchivingSetup']);
            HookRegistry::register('AcronPlugin::parseCronTab', [$this, 'callbackParseCronTab']);
        
            if ($this->getEnabled()) {
                $this->registerDAOs();
                $this->import('classes.Deposit');
                $this->import('classes.DepositObject');
                $this->import('classes.DepositPackage');
            
                HookRegistry::register('PluginRegistry::loadCategory', [$this, 'callbackLoadCategory']);            
                HookRegistry::register('JournalDAO::deleteJournalById', [$this, 'callbackDeleteJournalById']);
                HookRegistry::register('LoadHandler', [$this, 'callbackLoadHandler']);
                HookRegistry::register('NotificationManager::getNotificationContents', [$this, 'callbackNotificationContents']);
            }
        }

        return $success;
    }

    /**
     * Register this plugin's DAOs with the application
     */    
    public function registerDAOs() {
        $this->import('classes.DepositDAO');
        $this->import('classes.DepositObjectDAO');
        
        $depositDao = new DepositDAO();
        DAORegistry::registerDAO('DepositDAO', $depositDao);
        
        $depositObjectDao = new DepositObjectDAO();
        DAORegistry::registerDAO('DepositObjectDAO', $depositObjectDao);
    }
    
    /**
     * Get the display name of the plugin.
     * @see PKPPlugin::getDisplayName()
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.pln');
    }
    
    /**
     * Get a description of the plugin.
     * @see PKPPlugin::getDescription()
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.pln.description');
    }
    
    /**
     * Get the path to the plugin's installation schema file.
     * @see PKPPlugin::getInstallSchemaFile()
     * @return string|null
     */
    public function getInstallSchemaFile(): ?string {
        return $this->getPluginPath() . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . 'schema.xml';
    }
    
    /**
     * Get the path to the plugin's handler directory.
     * @see PKPPlugin::getHandlerPath()
     * @return string
     */
    public function getHandlerPath(): string {
        return $this->getPluginPath() . DIRECTORY_SEPARATOR . 'pages';
    }
    
    /**
     * Get the path to the plugin's template directory.
     * @see PKPPlugin::getTemplatePath()
     * @return string
     */
    public function getTemplatePath(): string {
        return parent::getTemplatePath() . DIRECTORY_SEPARATOR . 'templates';
    }
    
    /**
     * Get the path to the plugin's context-specific settings file.
     * @see PKPPlugin::getContextSpecificPluginSettingsFile()
     * @return string|null
     */
    public function getContextSpecificPluginSettingsFile(): ?string {
        return $this->getPluginPath() . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . 'settings.xml';
    }
    
    /**
     * Return the location of the plugin's CSS file
     * @return string
     */
    public function getStyleSheet() {
        return $this->getPluginPath() . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'pln.css';
    }
    
    /**
     * Get a plugin setting, with some special handling for certain settings.
     * @see PKPPlugin::getSetting()
     * @param int $journalId
     * @param string $settingName
     * @return mixed
     */
    public function getSetting($journalId, $settingName) {
        // If there isn't a journal_uuid, make one
        switch ($settingName) {
            case 'journal_uuid':
                $uuid = parent::getSetting($journalId, $settingName);
                if ($uuid !== null && $uuid !== '') {
                    return $uuid;
                }
                $this->updateSetting($journalId, $settingName, $this->newUUID());
                break;
            case 'object_type':
                $type = parent::getSetting($journalId, $settingName);
                if ($type !== null) {
                    return $type;
                }
                $this->updateSetting($journalId, $settingName, PLN_PLUGIN_DEPOSIT_OBJECT_ISSUE);
                break;
            case 'pln_network':
                return Config::getVar('lockss', 'pln_url', PLN_DEFAULT_NETWORK);
            case 'pln_status_docs':
                return Config::getVar('lockss', 'pln_status_docs', Config::getVar('lockss', 'pln_url', PLN_DEFAULT_NETWORK) . PLN_DEFAULT_STATUS_SUFFIX);
            default:
                break;
        }
        return parent::getSetting($journalId, $settingName);
    }

    /**
     * Register as a gateway plugin.
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function callbackLoadCategory($hookName, $args) {
        $category = $args[0];
        $plugins =& $args[1];
        
        switch ($category) {
            case 'gateways':
                $this->import('PLNGatewayPlugin');
                $gatewayPlugin = new PLNGatewayPlugin($this->getName());
                $plugins[$gatewayPlugin->getSeq()][$gatewayPlugin->getPluginPath()] = $gatewayPlugin;
                break;
        }
        return false;
    }

    /**
     * Delete all plug-in data for a journal when the journal is deleted
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function callbackDeleteJournalById($hookName, $params) {
        // [WIZDAM FIX] Fallback to $params[0] as it's the standard first parameter for this hook in OJS
        $journalId = (int) ($params[0] ?? $params[1] ?? 0);
        
        /** @var DepositDAO $depositDao */
        $depositDao = DAORegistry::getDAO('DepositDAO');
        $depositDao->deleteDepositsByJournalId($journalId);
        
        /** @var DepositObjectDAO $depositObjectDao */
        $depositObjectDao = DAORegistry::getDAO('DepositObjectDAO');
        $depositObjectDao->deleteDepositObjectsByJournalId($journalId);
        
        return false;
    }
    
    /**
     * Callback for template display
     * @copydoc TemplateManager::display()
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function callbackTemplateDisplay($hookName, $params) {
        $request = Application::get()->getRequest();
        $journal = $request->getContext();
        
        $templateMgr = $params[0];
        $templateMgr->addStylesheet($request->getBaseUrl() . '/' . $this->getStyleSheet());
        
        return false;
    }
    
    /**
     * A callback to add this plugin's cron tasks to the list of scheduled tasks
     * @copydoc AcronPlugin::parseCronTab()
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function callbackParseCronTab($hookName, $args) {
        $taskFilesPath =& $args[0];
        $taskFilesPath[] = $this->getPluginPath() . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . 'scheduledTasks.xml';
        return false;
    }
    
    /**
     * A callback used to populate journal setup step 2.6 with PLN preservation info
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function callbackJournalArchivingSetup($hookName, $args) {
        $smarty = $args[1];
        $output =& $args[2];
        $templateMgr = TemplateManager::getManager();
        $templateMgr->register_function('plugin_url', [$this, 'smartyPluginUrl']);
        $output .= $templateMgr->fetch($this->getTemplatePath() . DIRECTORY_SEPARATOR . 'setup.tpl');
        return false;
    }
    
    /**
     * Hook registry function to provide notification messages
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function callbackNotificationContents($hookName, $args) {
        $notification = $args[0];
        $message =& $args[1];

        $type = $notification->getType();
        
        // [WIZDAM FIX] Replaced assert with safe check
        if (!isset($type)) {
            return false;
        }
        
        switch ($type) {
            case PLN_PLUGIN_NOTIFICATION_TYPE_TERMS_UPDATED:
                $message = __('plugins.generic.pln.notifications.terms_updated');
                break;
            case PLN_PLUGIN_NOTIFICATION_TYPE_ISSN_MISSING:
                $message = __('plugins.generic.pln.notifications.issn_missing');
                break;
            case PLN_PLUGIN_NOTIFICATION_TYPE_HTTP_ERROR:
                $message = __('plugins.generic.pln.notifications.http_error');
                break;
        }
        return false;
    }
    
    /**
     * A callback to load this plugin's page handler
     * @copydoc PKPPageRouter::route()
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function callbackLoadHandler($hookName, $args) {
        $page = $args[0];
        if ($page === 'pln') {
            $op = $args[1] ?? null;
            if ($op) {
                if (in_array($op, ['deposits'], true)) {
                    define('HANDLER_CLASS', 'PLNHandler');
                    AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON);
                    $handlerFile =& $args[2];
                    $handlerFile = $this->getHandlerPath() . DIRECTORY_SEPARATOR . 'PLNHandler.inc.php';
                }
            }
        }
        return false;
    }
    
    /**
     * A callback to manage this plugin's settings and actions
     * @copydoc PKPPlugin::manage()
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
        
        $journal = $request->getJournal();
        if (!$journal) {
            return false;
        }

        switch ($verb) {
            case 'enable':
                if (!@include_once('Archive/Tar.php')) {
                    $message = (string) NOTIFICATION_TYPE_ERROR;
                    $messageParams = ['contents' => __('plugins.generic.pln.notifications.archive_tar_missing')];
                    break;
                }
                if (!$this->php5Installed()) {
                    $message = (string) NOTIFICATION_TYPE_ERROR;
                    $messageParams = ['contents' => __('plugins.generic.pln.notifications.php_missing')];
                    break;
                }
                if (!$this->curlInstalled()) {
                    $message = (string) NOTIFICATION_TYPE_ERROR;
                    $messageParams = ['contents' => __('plugins.generic.pln.notifications.curl_missing')];
                    break;
                }
                if (!$this->zipInstalled()) {
                    $message = (string) NOTIFICATION_TYPE_ERROR;
                    $messageParams = ['contents' => __('plugins.generic.pln.notifications.zip_missing')];
                    break;
                }
                if (!$this->tarInstalled()) {
                    $message = (string) NOTIFICATION_TYPE_ERROR;
                    $messageParams = ['contents' => __('plugins.generic.pln.notifications.tar_missing')];
                    break;
                }
                $message = (string) NOTIFICATION_TYPE_SUCCESS;
                $messageParams = ['contents' => __('plugins.generic.pln.enabled')];
                $this->updateSetting((int) $journal->getId(), 'enabled', true);
                break;
            case 'disable':
                $message = (string) NOTIFICATION_TYPE_SUCCESS;
                $messageParams = ['contents' => __('plugins.generic.pln.disabled')];
                $this->updateSetting((int) $journal->getId(), 'enabled', false);
                break;
            case 'settings':
                $templateMgr = TemplateManager::getManager($request);
                $templateMgr->register_function('plugin_url', [$this, 'smartyPluginUrl']);
                $this->import('classes.form.PLNSettingsForm');
                $form = new PLNSettingsForm($this, (int) $journal->getId());

                if ($request->getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        $message = (string) NOTIFICATION_TYPE_SUCCESS;
                        $messageParams = ['contents' => __('plugins.generic.pln.settings.saved')];
                        return false;
                    } else {
                        $this->setBreadcrumbs('settings');
                        $form->display($request);
                    }
                } else {
                    if ($request->getUserVar('refresh')) {
                        $this->getServiceDocument((int) $journal->getId());
                    } 
                    $this->setBreadcrumbs('settings');
                    $form->initData();
                    $form->display($request);
                }
                return true;
            case 'status':
                $templateMgr = TemplateManager::getManager($request);
                $templateMgr->register_function('plugin_url', [$this, 'smartyPluginUrl']);
                $this->import('classes.form.PLNStatusForm');
                $form = new PLNStatusForm($this, (int) $journal->getId());
                
                $resetIds = $request->getUserVar('reset');
                if (is_array($resetIds)) {
                    $depositIds = array_keys($resetIds);
                    /** @var DepositDAO $depositDao */
                    $depositDao = DAORegistry::getDAO('DepositDAO');
                    foreach ($depositIds as $depositId) {
                        $deposit = $depositDao->getDepositById((int) $journal->getId(), (int) $depositId);
                        if ($deposit) {
                            $deposit->setStatus(PLN_PLUGIN_DEPOSIT_STATUS_NEW);
                            $depositDao->updateDeposit($deposit);
                        }
                    }
                }
                
                $this->setBreadcrumbs('status'); // [WIZDAM FIX] Corrected typo from setBreadCrumbs
                $form->display($request);
                return true;
            default:
                return parent::manage($verb, $args, $message, $messageParams, $request);
        }

        return false;
    }
    
    /**
     * A callback to manage this plugin's settings and actions
     * @copydoc GenericPlugin::getManagementVerbs()
     * @param array $verbs
     * @param mixed $request
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array { 
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        
        $verbs = parent::getManagementVerbs($verbs, $request); 
        if ($this->getEnabled($request)) { 
            $verbs[] = ['settings', __('plugins.generic.pln.settings')];
            $verbs[] = ['status', __('plugins.generic.pln.status')];
        }
        return $verbs;
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
     * @param string $page
     */
    public function setBreadcrumbs($page) {
        $request = Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);
        $pageCrumbs = [
            [
                $request->url(null, 'user'),
                'navigation.user'
            ],
            [
                $request->url(null, 'manager'),
                'manager.journalManagement'
            ],
            [
                $request->url(null, 'manager', 'plugins'),
                'manager.plugins.pluginManagement'
            ],
            [
                $request->url(null, 'manager', 'plugins', 'generic'),
                'plugins.categories.generic'
            ]
        ];
        $templateMgr->assign('pageHierarchy', $pageCrumbs);
    }
    
    /**
     * Check to see whether the PLN's terms have been agreed to.
     * @param int $journalId
     * @return bool
     */
    public function termsAgreed($journalId) {
        $terms = unserialize((string) $this->getSetting($journalId, 'terms_of_use'));
        $termsAgreed = unserialize((string) $this->getSetting($journalId, 'terms_of_use_agreement'));

        if (!is_array($terms) || !is_array($termsAgreed)) {
            return false;
        }

        foreach (array_keys($terms) as $term) {
            if (!isset($termsAgreed[$term]) || !$termsAgreed[$term]) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Request service document at specified URL
     * @param int $journalId
     * @return int|bool
     */
    public function getServiceDocument($journalId) {
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journal = $journalDao->getById((int) $journalId);
        if (!$journal) {
            return false;
        }

        $locale = $journal->getPrimaryLocale();
        $language = strtolower(str_replace('_', '-', $locale));
        $network = $this->getSetting((int) $journalId, 'pln_network');
        
        // Retrieve the service document
        $result = $this->_curlGet(
            $network . PLN_PLUGIN_SD_IRI,
            [
                'On-Behalf-Of: ' . $this->getSetting((int) $journalId, 'journal_uuid'),
                'Journal-URL: ' . $journal->getUrl(),
                'Accept-language: ' . $language,
            ]
        );
        
        // Stop here if we didn't get an OK
        if ($result['status'] !== PLN_PLUGIN_HTTP_STATUS_OK) {
            if ($result['status'] === false) {
                error_log(__('plugins.generic.pln.error.network.servicedocument', ['error' => $result['error']]));
            } else {
                error_log(__('plugins.generic.pln.error.http.servicedocument', ['error' => $result['status']]));
            }
            return $result['status'];
        }

        $serviceDocument = new DOMDocument();
        $serviceDocument->preserveWhiteSpace = false;
        $serviceDocument->loadXML($result['result']);
        
        // Update the max upload size
        $element = $serviceDocument->getElementsByTagName('maxUploadSize')->item(0);
        if ($element) {
            $this->updateSetting((int) $journalId, 'max_upload_size', $element->nodeValue);
        }
        
        // Update the checksum type
        $element = $serviceDocument->getElementsByTagName('uploadChecksumType')->item(0);
        if ($element) {
            $this->updateSetting((int) $journalId, 'checksum_type', $element->nodeValue);
        }
        
        // Update the network status
        $element = $serviceDocument->getElementsByTagName('pln_accepting')->item(0);
        if ($element) {
            $this->updateSetting((int) $journalId, 'pln_accepting', ($element->getAttribute('is_accepting') === 'Yes'));
            $this->updateSetting((int) $journalId, 'pln_accepting_message', $element->nodeValue);
        }
        
        // Update the terms of use
        $termElements = $serviceDocument->getElementsByTagName('terms_of_use')->item(0)->childNodes;
        $terms = [];
        foreach ($termElements as $termElement) {
            if (!$termElement instanceof DOMElement) {
                continue;
            }
            $terms[$termElement->tagName] = [
                'updated' => $termElement->getAttribute('updated'), 
                'term' => $termElement->nodeValue
            ];
        }
        
        $newTerms = serialize($terms);
        $oldTerms = $this->getSetting((int) $journalId, 'terms_of_use');
        
        // If the new terms don't match the existing ones we need to reset agreement
        if ($newTerms !== $oldTerms) {
            $termAgreements = [];
            foreach ($terms as $termName => $termText) {
                $termAgreements[$termName] = null;
            }
        
            $this->updateSetting((int) $journalId, 'terms_of_use', $newTerms, 'object');
            $this->updateSetting((int) $journalId, 'terms_of_use_agreement', serialize($termAgreements), 'object');
            $this->createJournalManagerNotification((int) $journalId, PLN_PLUGIN_NOTIFICATION_TYPE_TERMS_UPDATED);
        }
        
        return $result['status'];
    }
    
    /**
     * Create notification for all journal managers
     * @param int $journalId
     * @param int $notificationType
     */
    public function createJournalManagerNotification($journalId, $notificationType) {
        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');
        $journalManagers = $roleDao->getUsersByRoleId(ROLE_ID_JOURNAL_MANAGER, (int) $journalId);
        import('classes.notification.NotificationManager');
        $notificationManager = new NotificationManager();
        
        while ($journalManager = $journalManagers->next()) {
            $notificationManager->createTrivialNotification((int) $journalManager->getId(), $notificationType);
        }
    }

    /**
     * Get whether we're running php 7+
     * @return bool
     */
    public function php5Installed() {
        return version_compare(PHP_VERSION, '7.4.0', '>=');
    }
    
    /**
     * Get whether curl is available
     * @return bool
     */
    public function curlInstalled() {
        return function_exists('curl_version');
    }
    
    /**
     * Get whether zip archive support is present
     * @return bool
     */
    public function zipInstalled() {
        return class_exists('ZipArchive');
    }
        
    /**
     * Check if the Archive_Tar extension is installed and available. BagIt requires it.
     * @return bool
     */
    public function tarInstalled() {
        @include_once('Archive/Tar.php');
        return class_exists('Archive_Tar');
    }

    /**
     * Check if acron is enabled, or if the scheduled_tasks config var is set.
     * @return bool
     */
    public function cronEnabled() {
        $application = PKPApplication::getApplication();
        $products = $application->getEnabledProducts('plugins.generic');
        return isset($products['acron']) || Config::getVar('general', 'scheduled_tasks', false);
    }
        
    /**
     * Get resource using CURL
     * [WIZDAM FIX] Changed from protected to public to allow access from DepositPackage
     * @param string $url
     * @param array $headers
     * @return array
     */
    public function _curlGet($url, $headers = []) {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_URL => $url
        ]);
        
        $httpProxyHost = Config::getVar('proxy', 'http_host');
        if ($httpProxyHost) {
            curl_setopt($curl, CURLOPT_PROXY, $httpProxyHost);
            curl_setopt($curl, CURLOPT_PROXYPORT, Config::getVar('proxy', 'http_port', '80'));
            $username = Config::getVar('proxy', 'username');
            if ($username) {
                curl_setopt($curl, CURLOPT_PROXYUSERPWD, $username . ':' . Config::getVar('proxy', 'password'));
            }
        }
        
        $httpResult = curl_exec($curl);
        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $httpError = curl_error($curl);
        curl_close($curl);
                
        return [
            'status' => $httpStatus,
            'result' => $httpResult,
            'error'  => $httpError
        ];
    }
    
    /**
     * Post a file to a resource using CURL
     * [WIZDAM FIX] Changed from protected to public to allow access from DepositPackage
     * @param string $url
     * @param string $filename
     * @return array
     */
    public function _curlPostFile($url, $filename) {
        $curl = curl_init(); 
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ["Content-Length: " . filesize($filename)],
            CURLOPT_INFILE => fopen($filename, "r"),
            CURLOPT_INFILESIZE => filesize($filename),
            CURLOPT_URL => $url
        ]);
        
        $httpProxyHost = Config::getVar('proxy', 'http_host');
        if ($httpProxyHost) {
            curl_setopt($curl, CURLOPT_PROXY, $httpProxyHost);
            curl_setopt($curl, CURLOPT_PROXYPORT, Config::getVar('proxy', 'http_port', '80'));
            $username = Config::getVar('proxy', 'username');
            if ($username) {
                curl_setopt($curl, CURLOPT_PROXYUSERPWD, $username . ':' . Config::getVar('proxy', 'password'));
            }
        }
        
        $httpResult = curl_exec($curl);
        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $httpError = curl_error($curl);
        curl_close($curl);
        
        return [
            'status' => $httpStatus,
            'result' => $httpResult,
            'error'  => $httpError
        ];
    }
    
    /**
     * Put a file to a resource using CURL
     * [WIZDAM FIX] Changed from protected to public to allow access from DepositPackage
     * @param string $url
     * @param string $filename
     * @return array
     */
    public function _curlPutFile($url, $filename) {
        $headers = [
            "Content-Type: " . mime_content_type($filename),
            "Content-Length: " . filesize($filename)
        ];
        
        $curl = curl_init(); 
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_PUT => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_INFILE => fopen($filename, "r"),
            CURLOPT_INFILESIZE => filesize($filename),
            CURLOPT_URL => $url
        ]);
        
        $httpProxyHost = Config::getVar('proxy', 'http_host');
        if ($httpProxyHost) {
            curl_setopt($curl, CURLOPT_PROXY, $httpProxyHost);
            curl_setopt($curl, CURLOPT_PROXYPORT, Config::getVar('proxy', 'http_port', '80'));
            $username = Config::getVar('proxy', 'username');
            if ($username) {
                curl_setopt($curl, CURLOPT_PROXYUSERPWD, $username . ':' . Config::getVar('proxy', 'password'));
            }
        }
        
        $httpResult = curl_exec($curl);
        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $httpError = curl_error($curl);
        curl_close($curl);
        
        return [
            'status' => $httpStatus,
            'result' => $httpResult,
            'error'  => $httpError
        ];
    }
    
    /**
     * Create a new UUID
     * @return string
     */
    public function newUUID() {
        return PKPString::generateUUID();
    }

}
?>