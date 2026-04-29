<?php
declare(strict_types=1);

/**
 * @defgroup install_form
 */

/**
 * @file classes/install/form/InstallForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InstallForm
 * @ingroup install_form
 * @see Install
 *
 * @brief Form for system installation.
 * [WIZDAM EDITION] Refactored for PHP 8.x and Modern Prudent Standards
 */

import('classes.install.Install');
import('lib.pkp.classes.site.VersionCheck');
import('lib.pkp.classes.form.Form');

class InstallForm extends Form {

    /** @var array locales supported by this system */
    public $supportedLocales = [];

    /** @var array locale completeness booleans */
    protected $localesComplete = [];

    /** @var array client character sets supported by this system */
    protected $supportedClientCharsets = [];

    /** @var array connection character sets supported by this system */
    protected $supportedConnectionCharsets = [];

    /** @var array database character sets supported by this system */
    protected $supportedDatabaseCharsets = [];

    /** @var array database drivers supported by this system */
    protected $supportedDatabaseDrivers = [];

    /** @var array encryption algorithms supported */
    protected $supportedEncryptionAlgorithms = [];

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct('install/install.tpl');

        // FIXME Move the below options to an external configuration file?
        $this->supportedLocales = AppLocale::getAllLocales();
        $this->localesComplete = [];
        foreach ($this->supportedLocales as $key => $name) {
            $this->localesComplete[$key] = AppLocale::isLocaleComplete($key);
        }

        $this->supportedClientCharsets = [
            'utf-8' => 'Unicode (UTF-8)',
            'iso-8859-1' => 'Western (ISO-8859-1)'
        ];

        $this->supportedConnectionCharsets = [
            '' => __('common.notApplicable'),
            'utf8' => 'Unicode (UTF-8)'
        ];

        $this->supportedDatabaseCharsets = [
            '' => __('common.notApplicable'),
            'utf8' => 'Unicode (UTF-8)'
        ];

        // [WIZDAM MODERNIZATION] Exclusive use of bcrypt
        $this->supportedEncryptionAlgorithms = [
            'bcrypt' => 'Bcrypt'
        ];

        // [WIZDAM PRUDENT ARCHITECTURE] Limit to verified stable drivers
        $this->supportedDatabaseDrivers = [
            // <adodb-driver> => array(<php-module>, <name>)
            'mysqli' => ['mysqli', 'MySQLi / MariaDB'],
            'postgres' => ['pgsql', 'PostgreSQL']
        ];

        // Validation checks for this form
        $this->addCheck(new FormValidatorInSet($this, 'locale', 'required', 'installer.form.localeRequired', array_keys($this->supportedLocales)));
        $this->addCheck(new FormValidatorCustom($this, 'locale', 'required', 'installer.form.localeRequired', ['AppLocale', 'isLocaleValid']));
        $this->addCheck(new FormValidatorInSet($this, 'clientCharset', 'required', 'installer.form.clientCharsetRequired', array_keys($this->supportedClientCharsets)));
        $this->addCheck(new FormValidator($this, 'filesDir', 'required', 'installer.form.filesDirRequired'));
        $this->addCheck(new FormValidatorInSet($this, 'encryption', 'required', 'installer.form.encryptionRequired', array_keys($this->supportedEncryptionAlgorithms)));
        $this->addCheck(new FormValidator($this, 'adminUsername', 'required', 'installer.form.usernameRequired'));
        $this->addCheck(new FormValidatorAlphaNum($this, 'adminUsername', 'required', 'installer.form.usernameAlphaNumeric'));
        $this->addCheck(new FormValidatorLength($this, 'adminPassword', 'required', 'user.register.form.passwordLengthTooShort', '>=', INSTALLER_DEFAULT_MIN_PASSWORD_LENGTH));
        $this->addCheck(new FormValidator($this, 'adminPassword', 'required', 'installer.form.passwordRequired'));
        
        // [WIZDAM] Replaced create_function with closure
        $this->addCheck(new FormValidatorCustom(
            $this, 
            'adminPassword', 
            'required', 
            'installer.form.passwordsDoNotMatch', 
            function($password, $form) {
                return $password == $form->getData('adminPassword2');
            }, 
            [$this]
        ));
        
        $this->addCheck(new FormValidatorEmail($this, 'adminEmail', 'required', 'installer.form.emailRequired'));
        $this->addCheck(new FormValidatorInSet($this, 'databaseDriver', 'required', 'installer.form.databaseDriverRequired', array_keys($this->supportedDatabaseDrivers)));
        $this->addCheck(new FormValidator($this, 'databaseName', 'required', 'installer.form.databaseNameRequired'));
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function InstallForm() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Display the form.
     * @param PKPRequest|null $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('localeOptions', $this->supportedLocales);
        $templateMgr->assign('localesComplete', $this->localesComplete);
        $templateMgr->assign('clientCharsetOptions', $this->supportedClientCharsets);
        $templateMgr->assign('connectionCharsetOptions', $this->supportedConnectionCharsets);
        $templateMgr->assign('databaseCharsetOptions', $this->supportedDatabaseCharsets);
        $templateMgr->assign('encryptionOptions', $this->supportedEncryptionAlgorithms);
        $templateMgr->assign('allowFileUploads', get_cfg_var('file_uploads') ? __('common.yes') : __('common.no'));
        $templateMgr->assign('maxFileUploadSize', get_cfg_var('upload_max_filesize'));
        $templateMgr->assign('databaseDriverOptions', $this->checkDBDrivers());
        $templateMgr->assign('supportsMBString', PKPString::hasMBString() ? __('common.yes') : __('common.no'));
        $templateMgr->assign('phpIsSupportedVersion', version_compare(PHP_REQUIRED_VERSION, PHP_VERSION) != 1);
        $templateMgr->assign('phpRequiredVersion', PHP_REQUIRED_VERSION);
        $templateMgr->assign('phpVersion', PHP_VERSION);
        $templateMgr->assign('version', VersionCheck::getCurrentCodeVersion());
        $templateMgr->assign('passwordLength', INSTALLER_DEFAULT_MIN_PASSWORD_LENGTH);

        parent::display($request, $template);
    }

    /**
     * Initialize form data.
     */
    public function initData() {
        $docRoot = dirname($_SERVER['DOCUMENT_ROOT']);
        if (Core::isWindows()) {
            // Replace backslashes with slashes for the default files directory.
            $docRoot = str_replace('\\', '/', $docRoot);
        }

        // Add a trailing slash for paths that aren't filesystem root
        if ($docRoot !== '/') $docRoot .= '/';

        // [WIZDAM] Singleton Fallback for Request
        $request = Application::get()->getRequest();

        $this->_data = [
            'locale' => AppLocale::getLocale(),
            'additionalLocales' => [],
            'clientCharset' => 'utf-8',
            'connectionCharset' => '',
            'databaseCharset' => '',
            'encryption' => 'bcrypt', // [WIZDAM] Set default to bcrypt
            'filesDir' =>  $docRoot . 'files',
            'databaseDriver' => 'mysqli', // [WIZDAM] Set default to modern mysqli instead of mysql
            'databaseHost' => 'localhost',
            'databaseUsername' => 'wizdam',
            'databasePassword' => '',
            'databaseName' => 'wizdam',
            'createDatabase' => 1,
            'oaiRepositoryId' => 'wizdam.' . $request->getServerHost(),
            'enableBeacon'=> true
        ];
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars([
            'locale',
            'additionalLocales',
            'clientCharset',
            'connectionCharset',
            'databaseCharset',
            'filesDir',
            'encryption',
            'adminUsername',
            'adminPassword',
            'adminPassword2',
            'adminEmail',
            'databaseDriver',
            'databaseHost',
            'databaseUsername',
            'databasePassword',
            'databaseName',
            'createDatabase',
            'oaiRepositoryId',
            'enableBeacon'
        ]);

        if ($this->getData('additionalLocales') == null || !is_array($this->getData('additionalLocales'))) {
            $this->setData('additionalLocales', []);
        }
    }

    /**
     * Perform installation.
     * @param object|null $object
     */
    public function execute($object = null) {
        $templateMgr = TemplateManager::getManager();
        $installer = new Install($this->_data);

        if ($installer->execute()) {
            if (!$installer->wroteConfig()) {
                // Display config file contents for manual replacement
                $templateMgr->assign(['writeConfigFailed' => true, 'configFileContents' => $installer->getConfigContents()]);
            }

            $templateMgr->display('install/installComplete.tpl');

        } else {
            switch ($installer->getErrorType()) {
                case INSTALLER_ERROR_DB:
                    $this->dbInstallError($installer->getErrorMsg());
                    break;
                default:
                    $this->installError($installer->getErrorMsg());
                    break;
            }
        }

        $installer->destroy();
    }

    /**
     * Check if database drivers have the required PHP module loaded.
     * The names of drivers that appear to be unavailable are bracketed.
     * @return array
     */
    public function checkDBDrivers(): array {
        $dbDrivers = [];
        foreach ($this->supportedDatabaseDrivers as $driver => $info) {
            list($module, $name) = $info;
            if (!extension_loaded($module)) {
                $name = '[ ' . $name . ' ]';
            }
            $dbDrivers[$driver] = $name;
        }
        return $dbDrivers;
    }

    /**
     * Fail with a generic installation error.
     * @param string $errorMsg
     */
    public function installError($errorMsg) {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign(['isInstallError' => true, 'errorMsg' => $errorMsg]);
        error_log($errorMsg);
        $this->display();
    }

    /**
     * Fail with a database installation error.
     * @param string|null $errorMsg
     */
    public function dbInstallError($errorMsg) {
        $templateMgr = TemplateManager::getManager();
        if (empty($errorMsg)) $errorMsg = __('common.error.databaseErrorUnknown');
        $templateMgr->assign(['isInstallError' => true, 'dbErrorMsg' => $errorMsg]);
        error_log((string)$errorMsg);
        $this->display();
    }

}

?>