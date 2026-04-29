<?php
declare(strict_types=1);

/**
 * @file classes/manager/form/SectionForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SectionForm
 * @ingroup manager_form
 *
 * @brief Form for creating and modifying journal sections.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.form.Form');
import('classes.journal.Section');

class SectionForm extends Form {

    /** @var Section|null The section being edited */
    public $section = null;

    /** @var object|null Additional section editor to include in assigned list for this section */
    public $includeSectionEditor = null;

    /** @var object|null Assigned section editor to omit from assigned list for this section */
    public $omitSectionEditor = null;

    /** @var array List of user objects representing the available section editors for this journal. */
    public $sectionEditors = [];

    /**
     * Constructor.
     * @param int|null $sectionId omit for a new journal
     */
    public function __construct($sectionId = null) {
        parent::__construct('manager/sections/sectionForm.tpl');

        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        $journalId = $journal->getId();

        // Retrieve/instantiate section.
        $section = null;
        if ($sectionId !== null && is_numeric($sectionId)) {
            $sectionDao = DAORegistry::getDAO('SectionDAO');
            $section = $sectionDao->getSection((int) $sectionId, $journalId);
        }
        $this->section = $section;

        // Validation checks for this form
        $this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'manager.sections.form.titleRequired'));
        $this->addCheck(new FormValidatorLocale($this, 'abbrev', 'required', 'manager.sections.form.abbrevRequired'));
        $this->addCheck(new FormValidatorPost($this));
        
        $this->addCheck(new FormValidatorCustom(
            $this, 
            'reviewFormId', 
            'optional', 
            'manager.sections.form.reviewFormId', 
            [DAORegistry::getDAO('ReviewFormDAO'), 'reviewFormExists'], 
            [ASSOC_TYPE_JOURNAL, $journal->getId()]
        ));
        
        $this->includeSectionEditor = null;
        $this->omitSectionEditor = null;

        // Get a list of section editors for this journal.
        $roleDao = DAORegistry::getDAO('RoleDAO');
        $this->sectionEditors = $roleDao->getUsersByRoleId(ROLE_ID_SECTION_EDITOR, $journal->getId());
        $this->sectionEditors = $this->sectionEditors->toArray();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SectionForm($sectionId = null) {
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
     * When displaying the form, include the specified section editor
     * in the assigned list for this section.
     * @param int $sectionEditorId
     */
    public function includeSectionEditor($sectionEditorId) {
        foreach ($this->sectionEditors as $key => $junk) {
            if ($this->sectionEditors[$key]->getId() == $sectionEditorId) {
                $this->includeSectionEditor = $this->sectionEditors[$key];
            }
        }
    }

    /**
     * When displaying the form, omit the specified section editor from
     * the assigned list for this section.
     * @param int $sectionEditorId
     */
    public function omitSectionEditor($sectionEditorId) {
        foreach ($this->sectionEditors as $key => $junk) {
            if ($this->sectionEditors[$key]->getId() == $sectionEditorId) {
                $this->omitSectionEditor = $this->sectionEditors[$key];
            }
        }
    }

    /**
     * Get the names of fields for which localized data is allowed.
     * @return array
     */
    public function getLocaleFieldNames() {
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        return $sectionDao->getLocaleFieldNames();
    }

    /**
     * Display the form.
     * @param mixed $request
     * @param mixed $template
     */
    public function display($request = null, $template = null) {
        $journal = Application::get()->getRequest()->getJournal();
        $templateMgr = TemplateManager::getManager();

        $section = $this->section;
        $sectionId = ($section instanceof Section ? $section->getId() : null);
        
        $templateMgr->assign('sectionId', $sectionId);
        $templateMgr->assign('commentsEnabled', $journal->getSetting('enableComments'));
        $templateMgr->assign('helpTopicId', 'journal.managementPages.sections');

        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        $reviewForms = $reviewFormDao->getActiveByAssocId(ASSOC_TYPE_JOURNAL, $journal->getId());
        $reviewFormOptions = [];
        while ($reviewForm = $reviewForms->next()) {
            $reviewFormOptions[$reviewForm->getId()] = $reviewForm->getLocalizedTitle();
        }
        // [WIZDAM FIX] Removed assign_by_ref
        $templateMgr->assign('reviewFormOptions', $reviewFormOptions);

        parent::display($request, $template);
    }

    /**
     * Initialize form data from current settings.
     */
    public function initData() {
        $journal = Application::get()->getRequest()->getJournal();
        $sectionEditorsDao = DAORegistry::getDAO('SectionEditorsDAO');
        $section = $this->section;

        if ($section instanceof Section) {
            $this->_data = [
                'title' => $section->getTitle(null), // Localized
                'abbrev' => $section->getAbbrev(null), // Localized
                'reviewFormId' => $section->getReviewFormId(),
                'metaIndexed' => !$section->getMetaIndexed(), // #2066: Inverted
                'metaReviewed' => !$section->getMetaReviewed(), // #2066: Inverted
                'abstractsNotRequired' => $section->getAbstractsNotRequired(),
                'identifyType' => $section->getIdentifyType(null), // Localized
                'editorRestriction' => $section->getEditorRestricted(),
                'hideTitle' => $section->getHideTitle(),
                'hideAuthor' => $section->getHideAuthor(),
                'hideAbout' => $section->getHideAbout(),
                'disableComments' => $section->getDisableComments(),
                'policy' => $section->getPolicy(null), // Localized
                'assignedEditors' => $sectionEditorsDao->getEditorsBySectionId($journal->getId(), $section->getId()),
                'unassignedEditors' => $sectionEditorsDao->getEditorsNotInSection($journal->getId(), $section->getId()),
                'wordCount' => $section->getAbstractWordCount()
            ];
        } else {
            $this->_data = [
                'unassignedEditors' => $sectionEditorsDao->getEditorsNotInSection($journal->getId(), null)
            ];
        }
        parent::initData();
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(['title', 'abbrev', 'policy', 'reviewFormId', 'identifyType', 'metaIndexed', 'metaReviewed', 'abstractsNotRequired', 'editorRestriction', 'hideTitle', 'hideAuthor', 'hideAbout', 'disableComments', 'wordCount']);

        $request = Application::get()->getRequest();
        $assignedEditorIds = $request->getUserVar('assignedEditorIds');
        if (empty($assignedEditorIds)) {
            $assignedEditorIds = [];
        } elseif (!is_array($assignedEditorIds)) {
            $assignedEditorIds = [$assignedEditorIds];
        }

        $assignedEditors = $unassignedEditors = [];

        foreach ($this->sectionEditors as $key => $junk) {
            $sectionEditor = $this->sectionEditors[$key]; 
            $userId = $sectionEditor->getId();

            $isIncludeEditor = $this->includeSectionEditor && $this->includeSectionEditor->getId() == $userId;
            $isOmitEditor = $this->omitSectionEditor && $this->omitSectionEditor->getId() == $userId;
            
            if ((in_array($userId, $assignedEditorIds) || $isIncludeEditor) && !$isOmitEditor) {
                $assignedEditors[] = [
                    'user' => $sectionEditor, // [WIZDAM] Removed & ref
                    'canReview' => ($request->getUserVar('canReview' . $userId) ? 1 : 0),
                    'canEdit' => ($request->getUserVar('canEdit' . $userId) ? 1 : 0)
                ];
            } else {
                $unassignedEditors[] = $sectionEditor;
            }

            // unset($sectionEditor); // Not needed in foreach scope in PHP 7+ unless iterating by ref
        }

        $this->setData('assignedEditors', $assignedEditors);
        $this->setData('unassignedEditors', $unassignedEditors);
    }

    /**
     * Save section.
     * @param mixed $object
     */
    public function execute($object = null) {
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        $journalId = $journal->getId();

        // We get the section DAO early on so that
        // the section class will be imported.
        $sectionDao = DAORegistry::getDAO('SectionDAO');

        $section = $this->section;
        if (!($section instanceof Section)) {
            $section = new Section();
            $section->setJournalId($journalId);
            // [WIZDAM] Ensure REALLY_BIG_NUMBER is defined
            $section->setSequence(defined('REALLY_BIG_NUMBER') ? REALLY_BIG_NUMBER : 99999);
        }

        $section->setTitle($this->getData('title'), null); // Localized
        $section->setAbbrev($this->getData('abbrev'), null); // Localized
        
        $reviewFormId = $this->getData('reviewFormId');
        if ($reviewFormId === '') $reviewFormId = null;
        
        $section->setReviewFormId($reviewFormId);
        $section->setMetaIndexed($this->getData('metaIndexed') ? 0 : 1); // #2066: Inverted
        $section->setMetaReviewed($this->getData('metaReviewed') ? 0 : 1); // #2066: Inverted
        $section->setAbstractsNotRequired($this->getData('abstractsNotRequired') ? 1 : 0);
        $section->setIdentifyType($this->getData('identifyType'), null); // Localized
        $section->setEditorRestricted($this->getData('editorRestriction') ? 1 : 0);
        $section->setHideTitle($this->getData('hideTitle') ? 1 : 0);
        $section->setHideAuthor($this->getData('hideAuthor') ? 1 : 0);
        $section->setHideAbout($this->getData('hideAbout') ? 1 : 0);
        $section->setDisableComments($this->getData('disableComments') ? 1 : 0);
        $section->setPolicy($this->getData('policy'), null); // Localized
        $section->setAbstractWordCount($this->getData('wordCount'));

        // [WIZDAM] explicit parent call
        $section = parent::execute($section);

        if ($section->getId() != null) {
            $sectionDao->updateSection($section);
            $sectionId = $section->getId();
        } else {
            $sectionId = $sectionDao->insertSection($section);
            $sectionDao->resequenceSections($journalId);
        }

        // Save assigned editors
        $assignedEditorIds = $request->getUserVar('assignedEditorIds');
        if (empty($assignedEditorIds)) $assignedEditorIds = [];
        elseif (!is_array($assignedEditorIds)) $assignedEditorIds = [$assignedEditorIds];
        
        $sectionEditorsDao = DAORegistry::getDAO('SectionEditorsDAO');
        $sectionEditorsDao->deleteEditorsBySectionId($sectionId, $journalId);
        
        foreach ($this->sectionEditors as $key => $junk) {
            $sectionEditor = $this->sectionEditors[$key];
            $userId = $sectionEditor->getId();
            
            // We don't have to worry about omit- and include-
            // section editors because this function is only called
            // when the Save button is pressed and those are only
            // used in other cases.
            if (in_array($userId, $assignedEditorIds)) {
                $sectionEditorsDao->insertEditor(
                    $journalId,
                    $sectionId,
                    $userId,
                    $request->getUserVar('canReview' . $userId),
                    $request->getUserVar('canEdit' . $userId)
                );
            }
        }
    }
}

?>