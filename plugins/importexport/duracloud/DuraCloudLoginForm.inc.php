<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/duracloud/DuraCloudLoginForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DuraCloudLoginForm
 * @ingroup plugins_importexport_duracloud
 *
 * @brief Form to allow login to an external DuraCloud service.
 */

import('lib.pkp.classes.form.Form');

class DuraCloudLoginForm extends Form {

    /** @var object The DuraCloud plugin instance */
    protected $_plugin;

    /**
     * Constructor.
     * @param object $plugin DuraCloudImportExportPlugin
     */
    public function __construct($plugin) {
        parent::__construct($plugin->getTemplatePath() . 'index.tpl');
        $this->_plugin = $plugin;

        // Validation checks for this form
        $this->addCheck(new FormValidatorUrl($this, 'duracloudUrl', 'required', 'plugins.importexport.duracloud.configuration.urlRequired'));
        $this->addCheck(new FormValidator($this, 'duracloudUsername', 'required', 'plugins.importexport.duracloud.configuration.usernameRequired'));
        $this->addCheck(new FormValidator($this, 'duracloudPassword', 'required', 'plugins.importexport.duracloud.configuration.passwordRequired'));
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DuraCloudLoginForm($plugin) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Display the form.
     * @param object|null $request
     * @param string|null $template
     */
    public function display($request = null, $template = null): void {
        $templateMgr = TemplateManager::getManager();
        $plugin = $this->_plugin;

        if ($plugin->isDuraCloudConfigured()) {
            // Provide configuration details
            $templateMgr->assign('isConfigured', true);
            $templateMgr->assign('duracloudUrl', $plugin->getDuraCloudUrl());
            $templateMgr->assign('duracloudUsername', $plugin->getDuraCloudUsername());

            // Get a list of spaces and the currently selected space.
            $dcc = $plugin->getDuraCloudConnection();
            $ds = new DuraStore($dcc);
            $templateMgr->assign('spaces', $ds->getSpaces());
            $templateMgr->assign('duracloudSpace', $plugin->getDuraCloudSpace());
        }
        parent::display($request, $template);
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData(): void {
        $this->readUserVars(['duracloudUrl', 'duracloudUsername', 'duracloudPassword']);
        parent::readInputData();
    }

    /**
     * Extend 
     * @see Form::validate()
     * @param bool $callHooks
     * @return bool
     */
    public function validate($callHooks = true): bool {
        // Check that all required fields are filled.
        if (!parent::validate($callHooks)) return false;

        // Verify that the credentials work.
        $dcc = new DuraCloudConnection(
            (string) $this->getData('duracloudUrl'),
            (string) $this->getData('duracloudUsername'),
            (string) $this->getData('duracloudPassword')
        );
        
        $ds = new DuraStore($dcc);
        // Note: $storeId variable was undefined in legacy code, likely null or intended to be passed by ref.
        // Assuming null for getSpaces() check based on context.
        $storeId = null;
        if ($ds->getSpaces($storeId) === false) {
            // Could not get a list of spaces.
            $this->addError('duracloudUrl', __('plugins.importexport.duracloud.configuration.credentialsInvalid'));
            return false;
        }

        // Success.
        return true;
    }

    /**
     * Perform a test login and store the details.
     * @param object|null $object
     */
    public function execute($object = null): void {
        parent::execute($object);
        $this->_plugin->storeDuraCloudConfiguration(
            (string) $this->getData('duracloudUrl'),
            (string) $this->getData('duracloudUsername'),
            (string) $this->getData('duracloudPassword')
        );
    }
}

?>