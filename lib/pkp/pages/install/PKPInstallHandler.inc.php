<?php
declare(strict_types=1);

/**
 * @file pages/install/PKPInstallHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPInstallHandler
 * @ingroup pages_install
 *
 * @brief Handle installation requests.
 *
 * [WIZDAM EDITION] REFACTOR: PHP 8.1+ Compatibility, Logic Fixes, Strict Types
 */

import('classes.install.form.InstallForm');
import('classes.install.form.UpgradeForm');
import('classes.handler.Handler');

class PKPInstallHandler extends Handler {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * If no context is selected, list all.
     * Otherwise, display the index page for the selected context.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function index($args = [], $request = null) {
        // [Wizdam] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        // Make sure errors are displayed to the browser during install.
        // [STRICT] ini_set expects string value
        @ini_set('display_errors', '1');

        // [LOGIC FIX] Pass null as first arg (requiredContexts) so $request goes to second arg
        $this->validate(null, $request);
        $this->setupTemplate();

        $setLocale = $request->getUserVar('setLocale');
        
        // [SECURITY FIX] Sanitasi string dari whitespace/karakter berbahaya
        if ($setLocale !== null) {
            $setLocale = trim((string) $setLocale);
            if (AppLocale::isLocaleValid($setLocale)) {
                $request->setCookieVar('currentLocale', $setLocale);
            }
        }

        $installForm = new InstallForm();
        $installForm->initData();
        $installForm->display();
    }

    /**
     * Redirect to index if system has already been installed.
     * @param mixed $requiredContexts (Ignored in this handler)
     * @param PKPRequest|null $request
     */
    public function validate($requiredContexts = null, $request = null) {
        // [Wizdam] Singleton Fallback inside validate
        if (!$request && class_exists('Application')) {
             $request = Application::get()->getRequest();
        }

        if (Config::getVar('general', 'installed')) {
            if ($request) {
                $request->redirect(null, 'index');
            }
        }
    }

    /**
     * Execute installer.
     * @param array $args
     * @param PKPRequest $request
     */
    public function install($args, $request) {
        $this->validate(null, $request);
        $this->setupTemplate();

        $installForm = new InstallForm();
        $installForm->readInputData();

        if ($installForm->validate()) {
            $installForm->execute();
        } else {
            $installForm->display();
        }
    }

    /**
     * Display upgrade form.
     * @param array $args
     * @param PKPRequest $request
     */
    public function upgrade($args, $request) {
        $this->validate(null, $request);
        $this->setupTemplate();

        $setLocale = $request->getUserVar('setLocale');
        
        // [SECURITY FIX] Sanitasi string dari whitespace/karakter berbahaya
        if ($setLocale !== null) {
            $setLocale = trim((string) $setLocale);
            if (AppLocale::isLocaleValid($setLocale)) {
                $request->setCookieVar('currentLocale', $setLocale);
            }
        }

        $installForm = new UpgradeForm();
        $installForm->initData();
        $installForm->display();
    }

    /**
     * Execute upgrade.
     * @param array $args
     * @param PKPRequest $request
     */
    public function installUpgrade($args, $request) {
        $this->validate(null, $request);
        $this->setupTemplate();

        $installForm = new UpgradeForm();
        $installForm->readInputData();

        if ($installForm->validate()) {
            $installForm->execute();
        } else {
            $installForm->display();
        }
    }

    /**
     * Set up the installer template.
     * @param PKPRequest|null $request
     * @param bool $subclass
     */
    public function setupTemplate($request = null, $subclass = false) {
        parent::setupTemplate();
        AppLocale::requireComponents(LOCALE_COMPONENT_CORE_INSTALLER);
    }
}
?>