<?php
declare(strict_types=1);

/**
 * @file classes/install/form/UpgradeForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UpgradeForm
 * @ingroup install_form
 *
 * @brief Form for system upgrades.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('classes.install.Upgrade');
import('lib.pkp.classes.form.Form');

class UpgradeForm extends Form {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct('install/upgrade.tpl');
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function UpgradeForm() {
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
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $templateMgr = TemplateManager::getManager($request);
        // [WIZDAM] Use assign instead of assign_by_ref
        $templateMgr->assign('version', VersionCheck::getCurrentCodeVersion());

        parent::display($request, $template);
    }

    /**
     * Perform installation.
     * @param object|null $object
     */
    public function execute($object = null) {
        $templateMgr = TemplateManager::getManager();
        $installer = new Upgrade($this->_data);

        // FIXME Use logger?

        // FIXME Mostly common with InstallForm

        if ($installer->execute()) {
            if (!$installer->wroteConfig()) {
                // Display config file contents for manual replacement
                $templateMgr->assign(['writeConfigFailed' => true, 'configFileContents' => $installer->getConfigContents()]);
            }

            $templateMgr->assign('notes', $installer->getNotes());
            // [WIZDAM] Use assign instead of assign_by_ref
            $templateMgr->assign('newVersion', $installer->getNewVersion());
            $templateMgr->display('install/upgradeComplete.tpl');

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

    // FIXME Common with InstallForm

    /**
     * Fail with a generic installation error.
     * @param string $errorMsg
     */
    public function installError($errorMsg) {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign(['isInstallError' => true, 'errorMsg' => $errorMsg]);
        $this->display();
    }

    /**
     * Fail with a database installation error.
     * @param string $errorMsg
     */
    public function dbInstallError($errorMsg) {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign(['isInstallError' => true, 'dbErrorMsg' => empty($errorMsg) ? __('common.error.databaseErrorUnknown') : $errorMsg]);
        $this->display();
    }

}

?>