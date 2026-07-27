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
 * @brief Handle PLN requests.
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
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::PLNHandler(). Please refactor to parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Index handler: redirect to journal page.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function index(array $args = [], $request = null) {
        // Lumera Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $request->redirect(null, 'index');
    }

    /**
     * Provide an endpoint for the PLN staging server to retrieve a deposit
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function deposits($args, $request = null) {
        // Lumera Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $journal = $request->getJournal();
        if (!$journal) {
            $request->redirect(null, 'index');
            return;
        }

        /** @var DepositDAO $depositDao */
        $depositDao = DAORegistry::getDAO('DepositDAO');
        $fileManager = new FileManager();
        $dispatcher = $request->getDispatcher();
        
        $depositUuid = isset($args[0]) && !empty($args[0]) ? (string) $args[0] : null;

        // Sanitize the input
        if (!preg_match('/^[[:xdigit:]]{8}-[[:xdigit:]]{4}-[[:xdigit:]]{4}-[[:xdigit:]]{4}-[[:xdigit:]]{12}$/', (string) $depositUuid)) {
            error_log(__('plugins.generic.pln.error.handler.uuid.invalid'));
            $dispatcher->handle404($request);
            return false;
        }
        
        $deposit = $depositDao->getDepositByUUID((int) $journal->getId(), (string) $depositUuid);
        
        if (!$deposit) {
            error_log(__('plugins.generic.pln.error.handler.uuid.notfound'));
            $dispatcher->handle404($request);
            return false;
        }
        
        $depositPackage = new DepositPackage($deposit, null);
        $depositBag = $depositPackage->getPackageFilePath();
        
        if (!$fileManager->fileExists($depositBag)) {
            error_log(__('plugins.generic.pln.error.handler.file.notfound'));
            $dispatcher->handle404($request);
            return false;
        }
                
        return $fileManager->downloadFile($depositBag, mime_content_type($depositBag), true);        
    }

    /**
     * Display status of deposit(s)
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function status($args = [], $request = null) {
        // Lumera Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $journal = $request->getJournal();
        if (!$journal) {
            $request->redirect(null, 'index');
            return;
        }

        $plnPlugin = PluginRegistry::getPlugin('generic', PLN_PLUGIN_NAME);
        $templateMgr = TemplateManager::getManager($request);
        
        $router = $request->getRouter();
        
        $templateMgr->assign('pageHierarchy', [[$router->url($request, null, 'about'), 'about.aboutTheJournal']]);
        $templateMgr->display($plnPlugin->getTemplatePath() . DIRECTORY_SEPARATOR . 'status.tpl');
    }

}
?>