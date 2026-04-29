<?php
declare(strict_types=1);

/**
 * @file plugins/generic/xmlGalley/XMLGalleySettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class XMLGalleySettingsForm
 * @ingroup plugins_generic_xmlGalley
 *
 * @brief Form for journal managers to modify Article XML Galley plugin settings
 * MODERNIZED FOR SCHOLARWIZDAM FORK
 */

import('lib.pkp.classes.form.Form');

class XMLGalleySettingsForm extends Form {
    
    /** @var int */
    public $journalId;

    /** @var object */
    public $plugin;

    /**
     * Constructor
     * @param $plugin object
     * @param $journalId int
     */
    public function __construct($plugin, $journalId) {
        $this->journalId = (int) $journalId;
        $this->plugin = $plugin;

        parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function XMLGalleySettingsForm($plugin, $journalId) {
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
     * Initialize form data.
     * @return void
     */
    public function initData() {
        $journalId = $this->journalId;
        $plugin = $this->plugin;

        $templateMgr = TemplateManager::getManager();

        // set form variables for available XSLT renderers
        $xsltNative = ( extension_loaded('xsl') && extension_loaded('dom') );

        // populate form variables with saved plugin settings
        $this->setData('xsltNative', $xsltNative);
        
        // Ensure default fallback if setting is empty or pointing to obsolete PHP4/PHP5 options
        $currentRenderer = $plugin->getSetting($journalId, 'XSLTrenderer');
        if (empty($currentRenderer) || $currentRenderer == 'PHP4' || $currentRenderer == 'PHP5') {
            $currentRenderer = $xsltNative ? 'Native' : 'external';
        }

        if ( !Request::getUserVar('save') ) {
            $this->setData('XSLTrenderer', $currentRenderer);
            $this->setData('externalXSLT', $plugin->getSetting($journalId, 'externalXSLT'));
            $this->setData('XSLstylesheet', $plugin->getSetting($journalId, 'XSLstylesheet'));
            $this->setData('nlmPDF', $plugin->getSetting($journalId, 'nlmPDF'));
            $this->setData('externalFOP', $plugin->getSetting($journalId, 'externalFOP'));
        }
        $this->setData('customXSL', $plugin->getSetting($journalId, 'customXSL'));
    }

    /**
     * Assign form data to user-submitted data.
     * @return void
     */
    public function readInputData() {
        $this->readUserVars(['XSLTrenderer', 'XSLstylesheet', 'externalXSLT', 'customXSL', 'nlmPDF', 'externalFOP']);

        // ensure that external XSLT or XSL are not blank
        if ($this->getData('XSLTrenderer') == "external") {
            $this->addCheck(new FormValidator($this, 'externalXSLT', 'required', 'plugins.generic.xmlGalley.settings.externalXSLTRequired'));
        }

        // if PDF rendering is enabled, then check that an external FO processor is set
        if ($this->getData('nlmPDF') == "1") {
            $this->addCheck(new FormValidator($this, 'externalFOP', 'required', 'plugins.generic.xmlGalley.settings.xslFOPRequired'));
        }

        // if the custom stylesheet button is enabled, then check that an XSL is uploaded
        if ($this->getData('XSLstylesheet') == "custom") {
            $this->addCheck(new FormValidator($this, 'customXSL', 'required', 'plugins.generic.xmlGalley.settings.customXSLRequired'));
        }
    }

    /**
     * Save settings.
     * @return void
     */
    public function execute($object = NULL) {
        $plugin = $this->plugin;
        $journalId = $this->journalId;

        // get existing settings to see if any are changing that will affect the cache
        $flushCache = false;
        foreach ($this->_data as $setting => $value) {
            if ($plugin->getSetting($journalId, $setting) != $value) $flushCache = true;
        }

        // if there are changes, flush the XSLT cache
        if ($flushCache == true) {
            $cacheManager = CacheManager::getManager();
            $cacheManager->flush('xsltGalley', CACHE_TYPE_FILE);
        }

        // Checkbox value handling: if unchecked, it won't be sent in POST data.
        $nlmPdfValue = $this->getData('nlmPDF') ? '1' : '0';

        $plugin->updateSetting($journalId, 'nlmPDF', $nlmPdfValue);
        $plugin->updateSetting($journalId, 'externalFOP', $this->getData('externalFOP'));
        $plugin->updateSetting($journalId, 'XSLTrenderer', $this->getData('XSLTrenderer'));
        $plugin->updateSetting($journalId, 'XSLstylesheet', $this->getData('XSLstylesheet'));
        $plugin->updateSetting($journalId, 'externalXSLT', $this->getData('externalXSLT'));
        $plugin->updateSetting($journalId, 'customXSL', $this->getData('customXSL'));
    }
}
?>