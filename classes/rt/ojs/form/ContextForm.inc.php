<?php
declare(strict_types=1);

/**
 * @file classes/rt/ojs/form/ContextForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ContextForm
 * @ingroup rt_ojs_form
 *
 * @brief Form to change metadata information for an RT context.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.form.Form');

class ContextForm extends Form {

    /** @var int|null the ID of the context */
    public $contextId;

    /** @var RTContext|null current context */
    public $context;

    /** @var int ID of the version */
    public $versionId;

    /**
     * Constructor.
     * @param int|null $contextId
     * @param int $versionId
     */
    public function __construct($contextId, $versionId) {
        parent::__construct('rtadmin/context.tpl');
        $this->addCheck(new FormValidatorPost($this));

        $rtDao = DAORegistry::getDAO('RTDAO');
        $this->context = $rtDao->getContext($contextId);

        $this->versionId = (int) $versionId;

        if (isset($this->context)) {
            $this->contextId = (int) $contextId;
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ContextForm($contextId, $versionId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::ContextForm(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct($contextId, $versionId);
    }

    /**
     * Initialize form data from current context.
     */
    public function initData() {
        if (isset($this->context)) {
            $context = $this->context;
            $this->_data = [
                'abbrev' => $context->getAbbrev(),
                'title' => $context->getTitle(),
                'order' => $context->getOrder(),
                'description' => $context->getDescription(),
                'authorTerms' => $context->getAuthorTerms(),
                'citedBy' => $context->getCitedBy(),
                'geoTerms' => $context->getGeoTerms(),
                'defineTerms' => $context->getDefineTerms()
            ];
        } else {
            $this->_data = [];
        }
    }

    /**
     * Display the form.
     * @param PKPRequest|null $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        
        $templateMgr = TemplateManager::getManager();

        $templateMgr->assign('versionId', $this->versionId);

        if (isset($this->context)) {
            // [WIZDAM] Removed assign_by_ref
            $templateMgr->assign('context', $this->context);
            $templateMgr->assign('contextId', $this->contextId);
        }

        $templateMgr->assign('helpTopicId', 'journal.managementPages.readingTools.contexts');
        parent::display($request, $template);
    }


    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(
            [
                'abbrev',
                'title',
                'order',
                'description',
                'authorTerms',
                'citedBy',
                'defineTerms'
            ]
        );
    }

    /**
     * Save changes to context.
     * @param mixed $object
     * @return int the context ID
     */
    public function execute($object = null) {
        $rtDao = DAORegistry::getDAO('RTDAO');

        $context = $this->context;
        if (!isset($context)) {
            $context = new RTContext();
            $context->setVersionId($this->versionId);
        }

        $context->setTitle($this->getData('title'));
        $context->setAbbrev($this->getData('abbrev'));
        $context->setCitedBy($this->getData('citedBy') == true);
        $context->setAuthorTerms($this->getData('authorTerms') == true);
        $context->setGeoTerms($this->getData('geoTerms') == true);
        $context->setDefineTerms($this->getData('defineTerms') == true);
        $context->setDescription($this->getData('description'));
        if (!isset($this->context)) $context->setOrder(-1);

        if (isset($this->context)) {
            $rtDao->updateContext($context);
        } else {
            $rtDao->insertContext($context);
            $this->contextId = $context->getContextId();
        }

        return $this->contextId;
    }

}
?>