<?php
declare(strict_types=1);

/**
 * @file plugins/generic/pln/PLNSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PLNSettingsForm
 * @ingroup plugins_generic_pln
 *
 * @brief Form for journal managers to modify PLN plugin settings
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

import('lib.pkp.classes.form.Form');

class PLNSettingsForm extends Form {

    /**
     * @var int
     */
    protected $_journalId;

    /**
     * @var object
     */
    protected $_plugin;

    /**
     * Constructor
     * @param object $plugin
     * @param int $journalId
     */
    public function __construct($plugin, $journalId) {
        $this->_journalId = $journalId;
        $this->_plugin = $plugin;
        parent::__construct($plugin->getTemplatePath() . DIRECTORY_SEPARATOR . 'settings.tpl');
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PLNSettingsForm($plugin, $journalId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::PLNSettingsForm(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Validate the form. Called by the parent when the form is submitted.
     * @see Form::validate()
     */
    public function validate($callHooks = true) {
        return parent::validate($callHooks);
    }

    /**
     * Initialize form data.
     */
    public function initData() {
        $journalId = $this->_journalId;
        if (!$this->_plugin->getSetting($journalId, 'terms_of_use')) {
            $this->_plugin->getServiceDocument($journalId);
        }
        $this->setData('terms_of_use', unserialize($this->_plugin->getSetting($journalId, 'terms_of_use')));
        $this->setData('terms_of_use_agreement', unserialize($this->_plugin->getSetting($journalId, 'terms_of_use_agreement')));
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $terms_agreed = $this->getData('terms_of_use_agreement');
        // PHP 8: Null handling for user vars
        $userTermsAgreed = Request::getUserVar('terms_agreed');
        
        if ($userTermsAgreed && is_array($userTermsAgreed)) {
            foreach (array_keys($userTermsAgreed) as $term_agreed) {
                // PHP 8: gmdate() is standard, no change needed
                $terms_agreed[$term_agreed] = gmdate('c');
            }
            $this->setData('terms_of_use_agreement', $terms_agreed);
        }
    }
    
    /**
     * Check for the prerequisites for the plugin, and return a translated 
     * message for each missing requirement.
     * @return array
     */
    public function _checkPrerequisites() {
        $messages = [];
        
        if( ! $this->_plugin->php5Installed()) {
            // If php5 isn't available, then the other checks are not 
            // useful.
            $messages[] =  __('plugins.generic.pln.notifications.php5_missing');
            return $messages;
        }
        if( ! @include_once('Archive/Tar.php')) {
            $messages[] = __('plugins.generic.pln.notifications.archive_tar_missing');
        }
        if( ! $this->_plugin->curlInstalled()) {
            $messages[] = __('plugins.generic.pln.notifications.curl_missing');
        }
        if( ! $this->_plugin->zipInstalled()) {
            $messages[] = __('plugins.generic.pln.notifications.zip_missing');
        }
        if( ! $this->_plugin->cronEnabled()) {
            $messages[] = __('plugins.generic.pln.settings.acron_required');
        }
        return $messages;
    }

    /**
     * Display the form. Called by the parent to display the form.
     * @see Form::display()
     * @param PKPRequest $request
     * @param string $template
     */
    public function display($request = null, $template = null) {
        $journal = Request::getJournal();
        $issn = '';
        if ($journal->getSetting('onlineIssn')) {
            $issn = $journal->getSetting('onlineIssn');
        } else if ($journal->getSetting('printIssn')) {
            $issn = $journal->getSetting('printIssn');
        }
        $hasIssn = false;
        if ($issn != '') {
            $hasIssn = true;
        }
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('hasIssn', $hasIssn);
        $templateMgr->assign('prerequisitesMissing', $this->_checkPrerequisites());
        $templateMgr->assign('journal_uuid', $this->_plugin->getSetting($this->_journalId, 'journal_uuid'));
        $templateMgr->assign('terms_of_use', unserialize($this->_plugin->getSetting($this->_journalId, 'terms_of_use')));
        $templateMgr->assign('terms_of_use_agreement', $this->getData('terms_of_use_agreement'));
        parent::display($request, $template);
    }

    /**
     * Save settings. Called by the parent when the form is submitted.
     * @see Form::execute()
     */
    public function execute($object = null) {
        $this->_plugin->updateSetting($this->_journalId, 'terms_of_use_agreement', serialize($this->getData('terms_of_use_agreement')), 'object');

        $pluginSettingsDao = DAORegistry::getDAO('PluginSettingsDAO');
        $pluginSettingsDao->installSettings($this->_journalId, $this->_plugin->getName(), $this->_plugin->getContextSpecificPluginSettingsFile());
    }

}
?>