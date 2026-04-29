<?php
declare(strict_types=1);

/**
 * @file pages/manager/SectionHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SectionHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for section management functions.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.manager.ManagerHandler');

class SectionHandler extends ManagerHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SectionHandler() {
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
     * Display a list of the sections within the current journal.
     * @param array $args
     * @param PKPRequest $request
     */
    public function sections($args, $request) {
        $this->validate();
        $this->setupTemplate();

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journal = $request->getJournal();
        $rangeInfo = $this->getRangeInfo('sections');
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $sections = $sectionDao->getJournalSections($journal->getId(), $rangeInfo);
        $emptySectionIds = $sectionDao->getJournalEmptySectionIds($journal->getId());
        $templateMgr = TemplateManager::getManager();
        $templateMgr->addJavaScript('lib/pkp/js/lib/jquery/plugins/jquery.tablednd.js');
        $templateMgr->addJavaScript('lib/pkp/js/functions/tablednd.js');
        $templateMgr->assign('pageHierarchy', [[$request->url(null, 'manager'), 'manager.journalManagement']]);
        
        // [WIZDAM] Removed assign_by_ref
        $templateMgr->assign('sections', $sections);
        $templateMgr->assign('emptySectionIds', $emptySectionIds);
        $templateMgr->assign('helpTopicId', 'journal.managementPages.sections');
        $templateMgr->display('manager/sections/sections.tpl');
    }

    /**
     * Display form to create a new section.
     * @param array $args
     * @param PKPRequest $request
     */
    public function createSection($args, $request) {
        $this->editSection($args, $request);
    }

    /**
     * Display form to create/edit a section.
     * @param array $args if set the first parameter is the ID of the section to edit
     * @param PKPRequest $request
     */
    public function editSection($args, $request) {
        $this->validate();
        $this->setupTemplate(true);

        import('classes.manager.form.SectionForm');

        $sectionForm = new SectionForm(!isset($args) || empty($args) ? null : ((int) $args[0]));
        if ($sectionForm->isLocaleResubmit()) {
            $sectionForm->readInputData();
        } else {
            $sectionForm->initData();
        }
        $sectionForm->display();
    }

    /**
     * Save changes to a section.
     * @param array $args
     * @param PKPRequest $request
     */
    public function updateSection($args, $request) {
        $this->validate();
        $this->setupTemplate(true);

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        import('classes.manager.form.SectionForm');
        $sectionForm = new SectionForm(!isset($args) || empty($args) ? null : ((int) $args[0]));

        // [SECURITY FIX] Amankan 'editorAction' (string key) dengan trim()
        $editorAction = trim((string) $request->getUserVar('editorAction'));

        $canExecute = false;
        switch ($editorAction) {
            case 'addSectionEditor':
                // [SECURITY FIX] Amankan 'userId' (ID integer) dengan trim()
                $userId = (int) trim((string) $request->getUserVar('userId'));
                $sectionForm->includeSectionEditor($userId);
                $canExecute = false;
                break;
            case 'removeSectionEditor':
                // [SECURITY FIX] Amankan 'userId' (ID integer) dengan trim()
                $userId = (int) trim((string) $request->getUserVar('userId'));
                $sectionForm->omitSectionEditor($userId);
                $canExecute = false;
                break;
            default:
                $canExecute = true;
                break;
        }

        $sectionForm->readInputData();
        if ($canExecute && $sectionForm->validate()) {
            $sectionForm->execute();
            $request->redirect(null, null, 'sections');
        } else {
            $sectionForm->display();
        }
    }

    /**
     * Delete a section.
     * @param array $args first parameter is the ID of the section to delete
     * @param PKPRequest $request
     */
    public function deleteSection($args, $request) {
        $this->validate();

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        if (isset($args) && !empty($args)) {
            $journal = $request->getJournal();
            $sectionDao = DAORegistry::getDAO('SectionDAO');
            $sectionDao->deleteSectionById($args[0], $journal->getId());
        }
        $request->redirect(null, null, 'sections');
    }

    /**
     * Change the sequence of a section.
     * @param array $args
     * @param PKPRequest $request
     */
    public function moveSection($args, $request) {
        $this->validate();

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journal = $request->getJournal();
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        
        // [SECURITY FIX] Amankan 'id' (sectionId) dengan (int) trim()
        $sectionId = (int) trim((string) $request->getUserVar('id'));
        
        $section = $sectionDao->getSection($sectionId, $journal->getId()); 

        if ($section != null) {
            
            // [SECURITY FIX] Whitelist 'd' (direction)
            $direction = trim((string) $request->getUserVar('d'));

            if (!empty($direction)) {
                // moving with up or down arrow
                // Gunakan whitelisting yang ketat untuk arah yang valid
                if ($direction == 'u') {
                    $section->setSequence($section->getSequence() - 1.5);
                } elseif ($direction == 'd') {
                    $section->setSequence($section->getSequence() + 1.5);
                }

            } else {
                // Dragging and dropping
                
                // [SECURITY FIX] Amankan 'prevId' (ID integer) dengan (int) trim()
                $prevId = (int) trim((string) $request->getUserVar('prevId'));
                
                if ($prevId == 0) { // $prevId akan 0 jika null/kosong karena (int) casting
                    $prevSeq = 0;
                } else {
                    // Gunakan $prevId yang sudah diamankan
                    $prevJournal = $sectionDao->getSection($prevId);
                    $prevSeq = $prevJournal->getSequence();
                }

                $section->setSequence($prevSeq + .5);
            }

            $sectionDao->updateSection($section);
            $sectionDao->resequenceSections($journal->getId());
        }

        // Moving up or down with the arrows requires a page reload.
        if (isset($direction) && $direction != null) {
            $request->redirect(null, null, 'sections');
        }
    }

    /**
     * Configure the template.
     * @param bool $subclass True iff this page is a second level deep in the breadcrumb heirarchy.
     */
    public function setupTemplate($subclass = false) {
        AppLocale::requireComponents(
            LOCALE_COMPONENT_CORE_SUBMISSION, 
            LOCALE_COMPONENT_CORE_READER)
        ;
        parent::setupTemplate(true);
        if ($subclass) {
            $templateMgr = TemplateManager::getManager();
            $request = Application::get()->getRequest();
            $templateMgr->append('pageHierarchy', [$request->url(null, 'manager', 'sections'), 'section.sections']);
        }
    }
}
?>