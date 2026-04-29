<?php
declare(strict_types=1);

/**
 * @file plugins/pubIds/urn/URNSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class URNSettingsForm
 * @ingroup plugins_pubIds_urn
 *
 * @brief Form for journal managers to setup URN plugin
 */

import('lib.pkp.classes.form.Form');

class URNSettingsForm extends Form {

    //
    // Private properties
    //
    /** @var int */
    private int $_journalId;

    /** @var URNPubIdPlugin */
    private URNPubIdPlugin $_plugin;

    //
    // Constructor
    //
    /**
     * Constructor
     * @param URNPubIdPlugin $plugin
     * @param int $journalId
     */
    public function __construct(URNPubIdPlugin $plugin, int $journalId) {
        $this->_journalId = $journalId;
        $this->_plugin = $plugin;

        parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');

        // Validator: At least one content type enabled
        $this->addCheck(new FormValidatorCustom(
            $this,
            'enableIssueURN',
            'required',
            'plugins.pubIds.urn.manager.settings.form.journalContentRequired',
            function($enableIssueURN, $form) {
                return $form->getData('enableIssueURN') || 
                       $form->getData('enableArticleURN') || 
                       $form->getData('enableGalleyURN') || 
                       $form->getData('enableSuppFileURN');
            },
            [$this]
        ));

        // Validator: URN Prefix
        $this->addCheck(new FormValidator($this, 'urnPrefix', 'required', 'plugins.pubIds.urn.manager.settings.form.urnPrefixRequired'));
        $this->addCheck(new FormValidatorRegExp($this, 'urnPrefix', 'optional', 'plugins.pubIds.urn.manager.settings.form.urnPrefixPattern', '/^urn:[a-zA-Z0-9-]*:.*/'));

        // Validator: Issue Suffix Pattern
        $this->addCheck(new FormValidatorCustom(
            $this,
            'urnIssueSuffixPattern',
            'required',
            'plugins.pubIds.urn.manager.settings.form.urnIssueSuffixPatternRequired',
            function($urnIssueSuffixPattern, $form) {
                if ($form->getData('urnSuffix') == 'pattern' && $form->getData('enableIssueURN')) {
                    return $urnIssueSuffixPattern != '';
                }
                return true;
            },
            [$this]
        ));

        // Validator: Article Suffix Pattern
        $this->addCheck(new FormValidatorCustom(
            $this,
            'urnArticleSuffixPattern',
            'required',
            'plugins.pubIds.urn.manager.settings.form.urnArticleSuffixPatternRequired',
            function($urnArticleSuffixPattern, $form) {
                if ($form->getData('urnSuffix') == 'pattern' && $form->getData('enableArticleURN')) {
                    return $urnArticleSuffixPattern != '';
                }
                return true;
            },
            [$this]
        ));

        // Validator: Galley Suffix Pattern
        $this->addCheck(new FormValidatorCustom(
            $this,
            'urnGalleySuffixPattern',
            'required',
            'plugins.pubIds.urn.manager.settings.form.urnGalleySuffixPatternRequired',
            function($urnGalleySuffixPattern, $form) {
                if ($form->getData('urnSuffix') == 'pattern' && $form->getData('enableGalleyURN')) {
                    return $urnGalleySuffixPattern != '';
                }
                return true;
            },
            [$this]
        ));

        // Validator: SuppFile Suffix Pattern
        $this->addCheck(new FormValidatorCustom(
            $this,
            'urnSuppFileSuffixPattern',
            'required',
            'plugins.pubIds.urn.manager.settings.form.urnSuppFileSuffixPatternRequired',
            function($urnSuppFileSuffixPattern, $form) {
                if ($form->getData('urnSuffix') == 'pattern' && $form->getData('enableSuppFileURN')) {
                    return $urnSuppFileSuffixPattern != '';
                }
                return true;
            },
            [$this]
        ));

        $this->addCheck(new FormValidator(
            $this, 
            'namespace', 
            'required', 
            'plugins.pubIds.urn.manager.settings.form.namespaceRequired'
        ));
        
        $this->addCheck(new FormValidatorUrl(
            $this, 
            'urnResolver', 
            'required', 
            'plugins.pubIds.urn.manager.settings.form.urnResolverRequired'
        ));
        
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function URNSettingsForm() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    //
    // Implement template methods from Form
    //
    /**
     * Get the form display template
     * @see Form::display()
     * @param null|Request $request
     * @param null|string $template
     * @return void
     */
    public function display($request = null, $template = null) {
        $namespaces = [
            'urn:nbn:de' => 'urn:nbn:de',
            'urn:nbn:at' => 'urn:nbn:at',
            'urn:nbn:ch' => 'urn:nbn:ch',
            'urn:nbn' => 'urn:nbn',
            'urn' => 'urn'
        ];
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('namespaces', $namespaces);
        parent::display($request, $template);
    }

    /**
     * Initialize form data.
     * @see Form::initData()
     * @return void
     */
    public function initData() {
        $journalId = $this->_journalId;
        $plugin = $this->_plugin;

        foreach($this->_getFormFields() as $fieldName => $fieldType) {
            $this->setData($fieldName, $plugin->getSetting($journalId, $fieldName));
        }
    }

    /**
     * Read user input data.
     * @see Form::readInputData()
     * @return void
     */
    public function readInputData() {
        $this->readUserVars(array_keys($this->_getFormFields()));
    }

    /**
     * Save settings.
     * @see Form::execute()
     * @return void
     */
    public function execute() {
        $plugin = $this->_plugin;
        $journalId = $this->_journalId;

        foreach($this->_getFormFields() as $fieldName => $fieldType) {
            $plugin->updateSetting($journalId, $fieldName, $this->getData($fieldName), $fieldType);
        }
    }

    //
    // Private helper methods
    //
    /**
     * Get all form fields and their types
     * @return array
     */
    private function _getFormFields(): array {
        return [
            'enableIssueURN' => 'bool',
            'enableArticleURN' => 'bool',
            'enableGalleyURN' => 'bool',
            'enableSuppFileURN' => 'bool',
            'urnPrefix' => 'string',
            'urnSuffix' => 'string',
            'urnIssueSuffixPattern' => 'string',
            'urnArticleSuffixPattern' => 'string',
            'urnGalleySuffixPattern' => 'string',
            'urnSuppFileSuffixPattern' => 'string',
            'checkNo' => 'bool',
            'namespace' => 'string',
            'urnResolver' => 'string'
        ];
    }
}

?>