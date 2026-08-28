<?php
declare(strict_types=1);

/**
 * @file classes/manager/form/ArticleTypeCustomForm.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Lumera Team
 * Distributed under the GNU GPL v3.
 *
 * @class ArticleTypeCustomForm
 * @ingroup manager_form
 *
 * @brief [WIZDAM] Form untuk Journal Manager menambah/mengedit tipe
 * artikel KUSTOM milik jurnalnya. Pola dasar mengikuti
 * SectionForm.inc.php, jauh lebih sederhana -- cuma satu field (nama,
 * localized).
 */

import('lib.pkp.classes.form.Form');
import('classes.article.ArticleTypeCustom');

class ArticleTypeCustomForm extends Form {

    /** @var ArticleTypeCustom|null */
    public $customType = null;

    /**
     * Constructor.
     * @param int|null $customTypeId
     */
    public function __construct($customTypeId = null) {
        parent::__construct('manager/articleTypes/articleTypeForm.tpl');

        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        $journalId = $journal->getId();

        $customType = null;
        if ($customTypeId !== null && is_numeric($customTypeId)) {
            /** @var ArticleTypeCustomDAO $articleTypeCustomDao */
            $articleTypeCustomDao = DAORegistry::getDAO('ArticleTypeCustomDAO');
            $customType = $articleTypeCustomDao->getById((int) $customTypeId, $journalId);
        }
        $this->customType = $customType;

        $this->addCheck(new FormValidatorLocale($this, 'name', 'required', 'article.type.form.nameRequired'));
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     * @param int|null $customTypeId
     */
    public function ArticleTypeCustomForm($customTypeId = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct($customTypeId);
    }

    /**
     * Display the form.
     * @param PKPRequest|null $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();
        $customTypeId = ($this->customType instanceof ArticleTypeCustom ? $this->customType->getId() : null);
        $templateMgr->assign('customTypeId', $customTypeId);
        $templateMgr->assign('helpTopicId', 'journal.managementPages.articleTypes');
        parent::display($request, $template);
    }

    /**
     * Get the names of fields for which data should be localized.
     * @return array
     */
    public function getLocaleFieldNames() {
        return ['name'];
    }

    /**
     * Initialize form data from the current custom type (edit mode).
     */
    public function initData() {
        if (isset($this->customType)) {
            $this->_data = [
                'name' => $this->customType->getName(null),
            ];
        }
        parent::initData();
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(['name']);
    }

    /**
     * Save changes to the custom article type.
     * @param object|null $object
     * @return int the custom_type_id
     */
    public function execute($object = null) {
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();

        /** @var ArticleTypeCustomDAO $articleTypeCustomDao */
        $articleTypeCustomDao = DAORegistry::getDAO('ArticleTypeCustomDAO');

        if (isset($this->customType)) {
            $customType = $this->customType;
        } else {
            $customType = $articleTypeCustomDao->newDataObject();
            $customType->setJournalId($journal->getId());
            $customType->setSequence(defined('REALLY_BIG_NUMBER') ? REALLY_BIG_NUMBER : 99999);
        }

        $customType->setName($this->getData('name'), null);

        if (isset($this->customType)) {
            $articleTypeCustomDao->updateCustomType($customType);
        } else {
            $articleTypeCustomDao->insertCustomType($customType);
            $this->customType = $customType;
        }

        parent::execute();

        return $customType->getId();
    }

}
?>