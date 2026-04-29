<?php
declare(strict_types=1);

/**
 * @file plugins/generic/dataverse/pages/DataverseHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DataverseHandler
 * @ingroup plugins_generic_dataverse
 *
 * @brief Handle Dataverse page requests.
 * [WIZDAM EDITION] Modernized for PHP 8.4, Native REST API, and LSP Compliance.
 */

import('classes.handler.Handler');

class DataverseHandler extends Handler {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Index handler: redirect to journal page.
     * [WIZDAM LSP RULE] No type hints on signature to maintain compatibility with base Handler.
     * @param array $args
     * @param Request $request
     */
    public function index(array $args = [], $request = null) {
        $request->redirect(null, 'index');
    }
    
    /**
     * Display data availability policy.
     * @param array $args
     * @param Request $request
     */
    public function dataAvailabilityPolicy($args, $request) {
        $router = $request->getRouter();
        $journal = $router->getContext($request);
        
        $pluginName = defined('DATAVERSE_PLUGIN_NAME') ? DATAVERSE_PLUGIN_NAME : 'dataverseplugin';
        $dataversePlugin = PluginRegistry::getPlugin('generic', $pluginName);

        if (!$dataversePlugin) {
            $request->redirect(null, 'index');
        }

        // [WIZDAM FIX] Menambahkan $request ke TemplateManager untuk mencegah error Null Context
        $templateMgr = TemplateManager::getManager($request);
        
        // [WIZDAM FIX] Modern Array Syntax & String Casting
        $templateMgr->assign('pageHierarchy', [[$router->url($request, null, 'about'), 'about.aboutTheJournal']]);
        $templateMgr->assign('dataAvailabilityPolicy', (string) $dataversePlugin->getSetting($journal->getId(), 'dataAvailability'));
        
        $templateMgr->display($dataversePlugin->getTemplatePath() . '/dataAvailabilityPolicy.tpl');
    }

    /**
     * Display terms of use for Dataverse configured for journal.
     * @param array $args
     * @param Request $request
     */
    public function termsOfUse($args, $request) {
        $router = $request->getRouter();
        $journal = $router->getContext($request);
        
        $pluginName = defined('DATAVERSE_PLUGIN_NAME') ? DATAVERSE_PLUGIN_NAME : 'dataverseplugin';
        $dataversePlugin = PluginRegistry::getPlugin('generic', $pluginName);

        if (!$dataversePlugin) {
            $request->redirect(null, 'index');
        }

        $templateMgr = TemplateManager::getManager($request);
        
        // [WIZDAM REST API FIX] Mengarahkan pengambilan Terms of Use ke API Client yang baru
        if ((bool) $dataversePlugin->getSetting($journal->getId(), 'fetchTermsOfUse')) {
            $dataversePlugin->import('classes.api.DataverseApiClient');
            $apiClient = new DataverseApiClient($dataversePlugin);
            
            // Ekstrak alias Dataverse dari URL tujuan
            $dvUri = (string) $dataversePlugin->getSetting($journal->getId(), 'dvUri');
            $dataverseAlias = '';
            if (preg_match("/.+\/(\w+)$/", $dvUri, $matches)) {
                $dataverseAlias = $matches[1];
            }

            $termsOfUse = '';
            if (!empty($dataverseAlias)) {
                $termsOfUse = $apiClient->getTermsOfUse((int) $journal->getId(), $dataverseAlias);
            }
            
            // Fallback jika API gagal
            $fallbackTerms = (string) $dataversePlugin->getSetting($journal->getId(), 'dvTermsOfUse');
            $templateMgr->assign('termsOfUse', $termsOfUse ?: $fallbackTerms);
        } else {
            // Gunakan term yang diatur manual oleh Journal Manager
            $templateMgr->assign('termsOfUse', (string) $dataversePlugin->getSetting($journal->getId(), 'termsOfUse'));
        }
        
        $templateMgr->display($dataversePlugin->getTemplatePath() . '/termsOfUse.tpl');
    }
}
?>