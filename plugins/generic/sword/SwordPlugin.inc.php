<?php
declare(strict_types=1);

/**
 * @file plugins/generic/sword/SwordPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SwordPlugin
 * @ingroup plugins_generic_sword
 *
 * @brief SWORD deposit plugin class
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

define('SWORD_DEPOSIT_TYPE_AUTOMATIC',          1);
define('SWORD_DEPOSIT_TYPE_OPTIONAL_SELECTION', 2);
define('SWORD_DEPOSIT_TYPE_OPTIONAL_FIXED',     3);
define('SWORD_DEPOSIT_TYPE_MANAGER',            4);

define('NOTIFICATION_TYPE_SWORD_ENABLED',       NOTIFICATION_TYPE_PLUGIN_BASE + 0x0000001);
define('NOTIFICATION_TYPE_SWORD_DEPOSIT_COMPLETE',      NOTIFICATION_TYPE_PLUGIN_BASE + 0x0000003);
define('NOTIFICATION_TYPE_SWORD_AUTO_DEPOSIT_COMPLETE',     NOTIFICATION_TYPE_PLUGIN_BASE + 0x0000004);

import('lib.pkp.classes.plugins.GenericPlugin');

class SwordPlugin extends GenericPlugin {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SwordPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::SwordPlugin(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Get the display name of this plugin
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.sword.displayName');
    }

    /**
     * Determine whether or not this plugin is supported.
     * @return boolean
     */
    public function getSupported() {
        return class_exists('ZipArchive');
    }

    /**
     * Get the description of this plugin
     * @return string
     */
    public function getDescription(): string {
        if ($this->getSupported()) return __('plugins.generic.sword.description');
        return __('plugins.generic.sword.descriptionUnsupported');
    }

    /**
     * Register the plugin
     * @param string $category
     * @param string $path
     * @return boolean
     */
    public function register(string $category, string $path): bool {
        if (parent::register($category, $path)) {
            HookRegistry::register('PluginRegistry::loadCategory', [$this, 'callbackLoadCategory']);
            if ($this->getEnabled()) {
                HookRegistry::register('LoadHandler', [$this, 'callbackLoadHandler']);
                HookRegistry::register('SectionEditorAction::emailEditorDecisionComment', [$this, 'callbackAuthorDeposits']);
                HookRegistry::register('NotificationManager::getNotificationContents', [$this, 'callbackNotificationContents']);
            }
            return true;
        }
        return false;
    }

    /**
     * Check whether or not this plugin is enabled
     * @param PKPRequest $request
     * @return boolean
     */
    public function getEnabled($request = null): bool {
        $journal = Request::getJournal();
        $journalId = $journal ? $journal->getId() : 0;
        return (bool) $this->getSetting($journalId, 'enabled');
    }

    /**
     * Register as a block plugin, even though this is a generic plugin.
     * This will allow the plugin to behave as a block plugin, i.e. to
     * have layout tasks performed on it.
     * @param string $hookName
     * @param array $args
     */
    public function callbackLoadCategory($hookName, $args) {
        $category = $args[0];
        $plugins = $args[1];

        switch ($category) {
            case 'importexport':
                $this->import('SwordImportExportPlugin');
                $plugin = new SwordImportExportPlugin();
                
                // [FIX] Set nama parent TERLEBIH DAHULU sebelum memanggil getPluginPath()
                $plugin->parentPluginName = $this->getName(); 
                
                // [FIX] Baru kemudian panggil getPluginPath()
                $args[1][$plugin->getSeq()][$plugin->getPluginPath()] = $plugin;
                
                return true;
        }
        return false;
    }

    /**
     * Hook registry function that is called to display the sword deposit page for authors.
     * @param string $hookName
     * @param array $args
     */
    public function callbackLoadHandler($hookName, $args) {
        $page = $args[0];
        if ($page === 'sword') {
            define('HANDLER_CLASS', 'SwordHandler');
            define('SWORD_PLUGIN_NAME', $this->getName());
            // $args[2] is a reference to the handler file variable
            $handlerFile =& $args[2];
            $handlerFile = $this->getPluginPath() . '/' . 'SwordHandler.inc.php';
        }
    }

    /**
     * Hook registry function that is called when it's time to perform all automatic
     * deposits and notify the author of optional deposits.
     * @param string $hookName
     * @param array $args
     */
    public function callbackAuthorDeposits($hookName, $args) {
        $sectionEditorSubmission = $args[0];
        $request = $args[2];

        // Determine if the most recent decision was an "Accept"
        $decisions = $sectionEditorSubmission->getDecisions();
        $decisions = array_pop($decisions); // Rounds
        $decision = array_pop($decisions);
        $decisionConst = $decision ? $decision['decision'] : null;
        if ($decisionConst != SUBMISSION_EDITOR_DECISION_ACCEPT) return false;

        // The most recent decision was an "Accept"; perform auto deposits.
        $journal = Request::getJournal();
        $depositPoints = $this->getSetting($journal->getId(), 'depositPoints');
        import('classes.sword.OJSSwordDeposit');

        import('classes.notification.NotificationManager');
        $notificationManager = new NotificationManager();

        $sendDepositNotification = $this->getSetting($journal->getId(), 'allowAuthorSpecify') ? true : false;
        
        if (is_array($depositPoints)) {
            foreach ($depositPoints as $depositPoint) {
                $depositType = $depositPoint['type'];

                if ($depositType == SWORD_DEPOSIT_TYPE_OPTIONAL_SELECTION || $depositType == SWORD_DEPOSIT_TYPE_OPTIONAL_FIXED) $sendDepositNotification = true;
                if ($depositType != SWORD_DEPOSIT_TYPE_AUTOMATIC) continue;

                // For each automatic deposit point, perform a deposit.
                $deposit = new OJSSwordDeposit($sectionEditorSubmission);
                $deposit->setMetadata();
                $deposit->addEditorial();
                $deposit->createPackage();
                $deposit->deposit(
                    $depositPoint['url'],
                    $depositPoint['username'],
                    $depositPoint['password']
                );
                $deposit->cleanup();
                unset($deposit);

                $user = $request->getUser();
                $params = ['itemTitle' => $sectionEditorSubmission->getLocalizedTitle(), 'repositoryName' => $depositPoint['name']];
                $notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SWORD_AUTO_DEPOSIT_COMPLETE, $params);
            }
        }

        if ($sendDepositNotification) {
            $submittingUser = $sectionEditorSubmission->getUser();

            import('classes.mail.ArticleMailTemplate');
            $contactName = $journal->getSetting('contactName');
            $contactEmail = $journal->getSetting('contactEmail');
            $mail = new ArticleMailTemplate($sectionEditorSubmission, 'SWORD_DEPOSIT_NOTIFICATION', null, null, $journal, true, true);
            $mail->setFrom($contactEmail, $contactName);
            $mail->addRecipient($submittingUser->getEmail(), $submittingUser->getFullName());

            $mail->assignParams([
                'journalName' => $journal->getLocalizedTitle(),
                'articleTitle' => $sectionEditorSubmission->getLocalizedTitle(),
                'swordDepositUrl' => Request::url(
                    null, 'sword', 'index', $sectionEditorSubmission->getId()
                )
            ]);

            $mail->send($request);
        }

        return false;
    }

    /**
     * Hook registry function to provide notification messages for SWORD notifications
     * @param string $hookName
     * @param array $args
     */
    public function callbackNotificationContents($hookName, $args) {
        $notification = $args[0];
        $message =& $args[1]; // Message is passed by reference to be modified

        $type = $notification->getType();
        assert(isset($type));

        import('classes.notification.NotificationManager');
        $notificationManager = new NotificationManager();

        switch ($type) {
            case NOTIFICATION_TYPE_SWORD_DEPOSIT_COMPLETE:
                $notificationSettingsDao = DAORegistry::getDAO('NotificationSettingsDAO');
                $params = $notificationSettingsDao->getNotificationSettings($notification->getId());
                $message = __('plugins.generic.sword.depositComplete', $notificationManager->getParamsForCurrentLocale($params));
                break;
            case NOTIFICATION_TYPE_SWORD_AUTO_DEPOSIT_COMPLETE:
                $notificationSettingsDao = DAORegistry::getDAO('NotificationSettingsDAO');
                $params = $notificationSettingsDao->getNotificationSettings($notification->getId());
                $message = __('plugins.generic.sword.automaticDepositComplete', $notificationManager->getParamsForCurrentLocale($params));
                break;
            case NOTIFICATION_TYPE_SWORD_ENABLED:
                $message = __('plugins.generic.sword.enabled');
                break;
        }
    }

    /**
     * Display verbs for the management interface.
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        $verbs = parent::getManagementVerbs($verbs, $request);

        if ($this->getEnabled($request)) {
            $verbs[] = [
                'settings',
                __('plugins.generic.sword.settings')
            ];
        }
        
        return $verbs;
    }

    /**
     * Execute a management verb on this plugin
     * @param string $verb
     * @param array $args
     * @param string $message Result status message
     * @param array $messageParams Parameters for status message
     * @return boolean
     */
    public function manage(string $verb, array $args, string $message, array $messageParams, $request = NULL): bool {
        if (!parent::manage($verb, $args, $message, $messageParams)) return false;

        if (!$request) $request = Registry::get('request');

        switch ($verb) {
            case 'settings':
                AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON,  LOCALE_COMPONENT_PKP_MANAGER);
                $templateMgr = TemplateManager::getManager();
                $templateMgr->register_function('plugin_url', [$this, 'smartyPluginUrl']);
                $journal = $request->getJournal();

                $this->import('SettingsForm');
                $form = new SettingsForm($this, $journal->getId());

                // [WIZDAM FIX] Tangkap aksi Cancel secara eksplisit
                if ($request->getUserVar('cancel')) {
                    $request->redirect(null, 'manager', 'plugins', $this->getCategory());
                } elseif ($request->getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        $request->redirect(null, 'manager', 'plugins', $this->getCategory());
                    } else {
                        $form->display();
                    }
                } else {
                    $form->initData();
                    $form->display();
                }
                break;

            case 'enable':
                // [WIZDAM FIX] Ganti pemanggilan statis Request::
                $journal = $request->getJournal();
                $this->updateSetting($journal->getId(), 'enabled', true);
                $message = NOTIFICATION_TYPE_SWORD_ENABLED;
                return false;

            case 'disable':
                // [WIZDAM FIX] Ganti pemanggilan statis Request::
                $journal = $request->getJournal();
                $this->updateSetting($journal->getId(), 'enabled', false);
                $message = NOTIFICATION_TYPE_PLUGIN_DISABLED;
                $messageParams = ['pluginName' => $this->getDisplayName()];
                return false;

            case 'createDepositPoint':
            case 'editDepositPoint':
                // [WIZDAM FIX] Ganti pemanggilan statis Request::
                $journal = $request->getJournal();
                $templateMgr = TemplateManager::getManager();
                $templateMgr->register_function('plugin_url', [$this, 'smartyPluginUrl']);

                $depositPointId = array_shift($args);
                if ($depositPointId == '') $depositPointId = null;
                else $depositPointId = (int) $depositPointId;
                
                $this->import('DepositPointForm');
                $form = new DepositPointForm($this, $journal->getId(), $depositPointId);

                // [WIZDAM FIX] Tangkap aksi Cancel dan kembalikan ke halaman Settings SWORD
                if ($request->getUserVar('cancel')) {
                    $request->redirect(null, 'manager', 'plugin', [$this->getCategory(), $this->getName(), 'settings']);
                } elseif ($request->getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        // [WIZDAM FIX] Hapus magic string 'generic' & 'null, null, null'
                        $request->redirect(null, 'manager', 'plugin', [$this->getCategory(), $this->getName(), 'settings']);
                    } else {
                        $form->display();
                    }
                } else {
                    $form->initData();
                    $form->display();
                }
                break;

            case 'deleteDepositPoint':
                // [WIZDAM FIX] Ganti pemanggilan statis Request::
                $journal = $request->getJournal();
                $journalId = $journal->getId();
                $depositPointId = (int) array_shift($args);
                $depositPoints = $this->getSetting($journalId, 'depositPoints');
                
                if (isset($depositPoints[$depositPointId])) {
                    unset($depositPoints[$depositPointId]);
                    $this->updateSetting($journalId, 'depositPoints', $depositPoints);
                }
                // [WIZDAM FIX] Hapus magic string 'generic' dan 'SwordPlugin'
                $request->redirect(null, 'manager', 'plugin', [$this->getCategory(), $this->getName(), 'settings']);
                break;
        }

        return true;
    }

    /**
     * Get Type Map
     * @return array
     */
    public function getTypeMap() {
        return [
            SWORD_DEPOSIT_TYPE_AUTOMATIC => 'plugins.generic.sword.depositPoints.type.automatic',
            SWORD_DEPOSIT_TYPE_OPTIONAL_SELECTION => 'plugins.generic.sword.depositPoints.type.optionalSelection',
            SWORD_DEPOSIT_TYPE_OPTIONAL_FIXED => 'plugins.generic.sword.depositPoints.type.optionalFixed',
            SWORD_DEPOSIT_TYPE_MANAGER => 'plugins.generic.sword.depositPoints.type.manager'
        ];
    }

    /**
     * Get install email templates file
     * @return string
     */
    public function getInstallEmailTemplatesFile(): ?string {
        return ($this->getPluginPath() . DIRECTORY_SEPARATOR . 'emailTemplates.xml');
    }

    /**
     * Get install email template data file
     * @return string
     */
    public function getInstallEmailTemplateDataFile(): ?string {
        return ($this->getPluginPath() . '/locale/{$installedLocale}/emailTemplates.xml');
    }
}

?>