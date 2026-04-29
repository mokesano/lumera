<?php
declare(strict_types=1);

/**
 * @file plugins/generic/pln/pages/PLNHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PLNHandler
 * @ingroup plugins_generic_pln
 *
 * @brief Handle PLN requests
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

import('classes.handler.Handler');

class PLNHandler extends Handler {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PLNHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::PLNHandler(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Index handler: redirect to journal page.
     * @param array $args
     * @param PKPRequest $request
     */
    public function index($args, $request) {
        $request->redirect(null, 'index');
    }

    /**
     * Provide an endpoint for the PLN staging server to retrieve a deposit
     * @param array $args
     * @param PKPRequest $request
     */
    public function deposits($args, $request) {
        $journal = $request->getJournal();
        $depositDao = DAORegistry::getDAO('DepositDAO');
        $fileManager = new FileManager();
        $dispatcher = $request->getDispatcher();
        
        $depositUuid = (!isset($args[0]) || empty($args[0])) ? null : $args[0];

        // sanitize the input
        if (!preg_match('/^[[:xdigit:]]{8}-[[:xdigit:]]{4}-[[:xdigit:]]{4}-[[:xdigit:]]{4}-[[:xdigit:]]{12}$/',$depositUuid)) {
            error_log(__("plugins.generic.pln.error.handler.uuid.invalid"));
            $dispatcher->handle404();
            return false;
        }
        
        $deposit = $depositDao->getDepositByUUID($journal->getId(),$depositUuid);
        
        if (!$deposit) {
            error_log(__("plugins.generic.pln.error.handler.uuid.notfound"));
            $dispatcher->handle404();
            return false;
        }
        
        $depositPackage = new DepositPackage($deposit, null);
        $depositBag = $depositPackage->getPackageFilePath();
        
        if (!$fileManager->fileExists($depositBag)) {
            error_log("plugins.generic.pln.error.handler.file.notfound");
            $dispatcher->handle404();
            return false;
        }
                
        return $fileManager->downloadFile($depositBag, mime_content_type($depositBag), true);        
    }

    /**
     * Display status of deposit(s)
     * @param array $args
     * @param PKPRequest $request
     */
    public function status($args, $request) {
        // Fix: args parameter default value array() -> []
        if (empty($args)) $args = [];

        $journal = $request->getJournal();
        $plnPlugin = PluginRegistry::getPlugin('generic', PLN_PLUGIN_NAME);
        $templateMgr = TemplateManager::getManager();
        
        // [PHP 8 FIX] Handle potentially undefined $router if not explicitly requested
        $router = $request->getRouter();
        
        $templateMgr->assign('pageHierarchy', [[$router->url($request, null, 'about'), 'about.aboutTheJournal']]);
        $templateMgr->display($plnPlugin->getTemplatePath() . DIRECTORY_SEPARATOR . 'status.tpl');
    }
}

?>