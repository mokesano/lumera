<?php
declare(strict_types=1);

/**
 * @file pages/manager/ImportExportHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ImportExportHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for import/export functions.
 */

define('IMPORTEXPORT_PLUGIN_CATEGORY', 'importexport');

import('pages.manager.ManagerHandler');

class ImportExportHandler extends ManagerHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ImportExportHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Import or export data.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function importexport($args = [], $request = null) {
        $this->validate();
        $this->setupTemplate(true);

        PluginRegistry::loadCategory(IMPORTEXPORT_PLUGIN_CATEGORY);
        
        // [WIZDAM] Modernization: Pass $request to getManager for better context handling
        $templateMgr = TemplateManager::getManager($request);

        if (array_shift($args) === 'plugin') {
            $pluginName = (string) array_shift($args);
            /** @var ImportExportPlugin $plugin */
            $plugin = PluginRegistry::getPlugin(IMPORTEXPORT_PLUGIN_CATEGORY, $pluginName);
            
            if ($plugin) {
                return $plugin->display($args, $request);
            }
        }
        
        $templateMgr->assign('plugins', PluginRegistry::getPlugins(IMPORTEXPORT_PLUGIN_CATEGORY));
        $templateMgr->assign('helpTopicId', 'journal.managementPages.importExport');
        
        $templateMgr->display('manager/importexport/plugins.tpl');
    }

}
?>