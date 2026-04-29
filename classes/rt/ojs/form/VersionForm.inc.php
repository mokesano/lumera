<?php
declare(strict_types=1);

/**
 * @file classes/rt/ojs/form/VersionForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class VersionForm
 * @ingroup rt_ojs_form
 * @see Version
 *
 * @brief Form to change metadata information for an RT version.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.form.Form');

class VersionForm extends Form {

    /** @var int|null the ID of the version */
    public $versionId;

    /** @var int the ID of the journal */
    public $journalId;

    /** @var RTVersion|null current version */
    public $version;

    /**
     * Constructor.
     * @param int|null $versionId
     * @param int $journalId
     */
    public function __construct($versionId, $journalId) {
        parent::__construct('rtadmin/version.tpl');
        $this->addCheck(new FormValidatorPost($this));

        $this->journalId = (int) $journalId;

        $rtDao = DAORegistry::getDAO('RTDAO');
        $this->version = $rtDao->getVersion($versionId, $journalId);

        if (isset($this->version)) {
            $this->versionId = (int) $versionId;
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function VersionForm($versionId, $journalId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::VersionForm(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct($versionId, $journalId);
    }

    /**
     * Initialize form data from current version.
     */
    public function initData() {
        if (isset($this->version)) {
            $version = $this->version;
            $this->_data = [
                'key' => $version->getKey(),
                'title' => $version->getTitle(),
                'locale' => $version->getLocale(),
                'description' => $version->getDescription()
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
        
        $templateMgr = TemplateManager::getManager();

        if (isset($this->version)) {
            // [WIZDAM] Removed assign_by_ref
            $templateMgr->assign('version', $this->version);
            $templateMgr->assign('versionId', $this->versionId);
        }

        $templateMgr->assign('helpTopicId', 'journal.managementPages.readingTools.versions');
        parent::display($request, $template);
    }


    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(
            [
                'key',
                'title',
                'locale',
                'description'
            ]
        );
    }

    /**
     * Save changes to version.
     * @param mixed $object
     * @return int the version ID
     */
    public function execute($object = null) {
        $rtDao = DAORegistry::getDAO('RTDAO');

        $version = $this->version;
        if (!isset($version)) {
            $version = new RTVersion();
        }

        $version->setTitle($this->getData('title'));
        $version->setKey($this->getData('key'));
        $version->setLocale($this->getData('locale'));
        $version->setDescription($this->getData('description'));

        if (isset($this->version)) {
            $rtDao->updateVersion($this->journalId, $version);
        } else {
            $rtDao->insertVersion($this->journalId, $version);
            $this->versionId = $version->getVersionId();
        }

        return $this->versionId;
    }

}
?>