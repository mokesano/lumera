<?php
declare(strict_types=1);

/**
 * @file pages/manager/ReviewFormHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewFormHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for review form management functions.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.manager.ManagerHandler');

class ReviewFormHandler extends ManagerHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ReviewFormHandler() {
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
     * Display a list of review forms within the current journal.
     * @param array $args
     * @param PKPRequest $request
     */
    public function reviewForms($args, $request) {
        $this->validate();
        $this->setupTemplate();

        // [WIZDAM] Singleton Fallback (jika method ini dipanggil tanpa request di legacy chain)
        if (!$request) $request = Application::get()->getRequest();

        $journal = $request->getJournal();
        $rangeInfo = $this->getRangeInfo('reviewForms');
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        $reviewForms = $reviewFormDao->getByAssocId(ASSOC_TYPE_JOURNAL, $journal->getId(), $rangeInfo);
        
        // ReviewAssignmentDAO unused variable removed

        $templateMgr = TemplateManager::getManager();
        $templateMgr->addJavaScript('lib/pkp/js/lib/jquery/plugins/jquery.tablednd.js');
        $templateMgr->addJavaScript('lib/pkp/js/functions/tablednd.js');
        // [WIZDAM] Removed assign_by_ref
        $templateMgr->assign('reviewForms', $reviewForms);
        $templateMgr->assign('completeCounts', $reviewFormDao->getUseCounts(ASSOC_TYPE_JOURNAL, $journal->getId(), true));
        $templateMgr->assign('incompleteCounts', $reviewFormDao->getUseCounts(ASSOC_TYPE_JOURNAL, $journal->getId(), false));
        $templateMgr->assign('helpTopicId', 'journal.managementPages.reviewForms');
        $templateMgr->display('manager/reviewForms/reviewForms.tpl');
    }

    /**
     * Display form to create a new review form.
     */
    public function createReviewForm() {
        $this->editReviewForm();
    }

    /**
     * Display form to create/edit a review form.
     * @param array $args optional, if set the first parameter is the ID of the review form to edit
     */
    public function editReviewForm($args = []) {
        $this->validate();

        $reviewFormId = isset($args[0]) ? (int)$args[0] : null;

        $journal = Application::get()->getRequest()->getJournal();
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        $reviewForm = $reviewFormDao->getReviewForm($reviewFormId, ASSOC_TYPE_JOURNAL, $journal->getId());
        $completeCounts = $reviewFormDao->getUseCounts(ASSOC_TYPE_JOURNAL, $journal->getId(), true);
        $incompleteCounts = $reviewFormDao->getUseCounts(ASSOC_TYPE_JOURNAL, $journal->getId(), false);

        if ($reviewFormId != null && (!isset($reviewForm) || $completeCounts[$reviewFormId] != 0 || $incompleteCounts[$reviewFormId] != 0)) {
            Application::get()->getRequest()->redirect(null, null, 'reviewForms');
        } else {
            $this->setupTemplate(true, $reviewForm);
            $templateMgr = TemplateManager::getManager();

            if ($reviewFormId == null) {
                $templateMgr->assign('pageTitle', 'manager.reviewForms.create');
            } else {
                $templateMgr->assign('pageTitle', 'manager.reviewForms.edit');
            }

            import('classes.manager.form.ReviewFormForm');
            $reviewFormForm = new ReviewFormForm($reviewFormId);

            if ($reviewFormForm->isLocaleResubmit()) {
                $reviewFormForm->readInputData();
            } else {
                $reviewFormForm->initData();
            }
            $reviewFormForm->display();
        }
    }

    /**
     * Save changes to a review form.
     */
    public function updateReviewForm() {
        $this->validate();
        $request = Application::get()->getRequest();

        // [SECURITY FIX] Amankan 'reviewFormId' dengan trim() dan (int)
        $reviewFormIdInput = $request->getUserVar('reviewFormId');
        $reviewFormId = $reviewFormIdInput === null ? null : (int) trim((string) $reviewFormIdInput);

        $journal = $request->getJournal();
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        $reviewForm = $reviewFormDao->getReviewForm($reviewFormId, ASSOC_TYPE_JOURNAL, $journal->getId());
        $completeCounts = $reviewFormDao->getUseCounts(ASSOC_TYPE_JOURNAL, $journal->getId(), true);
        $incompleteCounts = $reviewFormDao->getUseCounts(ASSOC_TYPE_JOURNAL, $journal->getId(), false);
        
        if ($reviewFormId != null && (!isset($reviewForm) || $completeCounts[$reviewFormId] != 0 || $incompleteCounts[$reviewFormId] != 0)) {
            $request->redirect(null, null, 'reviewForms');
        }
        $this->setupTemplate(true, $reviewForm);

        import('classes.manager.form.ReviewFormForm');
        $reviewFormForm = new ReviewFormForm($reviewFormId);
        $reviewFormForm->readInputData();

        if ($reviewFormForm->validate()) {
            $reviewFormForm->execute();
            $request->redirect(null, null, 'reviewForms');
        } else {
            $templateMgr = TemplateManager::getManager();

            if ($reviewFormId == null) {
                $templateMgr->assign('pageTitle', 'manager.reviewForms.create');
            } else {
                $templateMgr->assign('pageTitle', 'manager.reviewForms.edit');
            }

            $reviewFormForm->display();
        }
    }

    /**
     * Preview a review form.
     * @param array $args first parameter is the ID of the review form to preview
     */
    public function previewReviewForm($args) {
        $this->validate();

        $reviewFormId = isset($args[0]) ? (int)$args[0] : null;

        $journal = Application::get()->getRequest()->getJournal();
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        $reviewForm = $reviewFormDao->getReviewForm($reviewFormId, ASSOC_TYPE_JOURNAL, $journal->getId());
        $reviewFormElementDao = DAORegistry::getDAO('ReviewFormElementDAO');
        $reviewFormElements = $reviewFormElementDao->getReviewFormElements($reviewFormId);

        if (!isset($reviewForm)) {
            Application::get()->getRequest()->redirect(null, null, 'reviewForms');
        }

        $completeCounts = $reviewFormDao->getUseCounts(ASSOC_TYPE_JOURNAL, $journal->getId(), true);
        $incompleteCounts = $reviewFormDao->getUseCounts(ASSOC_TYPE_JOURNAL, $journal->getId(), false);
        if ($completeCounts[$reviewFormId] != 0 || $incompleteCounts[$reviewFormId] != 0) {
            $this->setupTemplate(true);
        } else {
            $this->setupTemplate(true, $reviewForm);
        }

        $templateMgr = TemplateManager::getManager();

        $templateMgr->assign('pageTitle', 'manager.reviewForms.preview');
        // [WIZDAM] Removed assign_by_ref
        $templateMgr->assign('reviewForm', $reviewForm);
        $templateMgr->assign('reviewFormElements', $reviewFormElements);
        $templateMgr->assign('completeCounts', $completeCounts);
        $templateMgr->assign('incompleteCounts', $incompleteCounts);
        
        // Note: register_function is technically deprecated in Smarty 3/4 but usually shimmed in OJS wrappers.
        // If 'ReviewFormHandler' static methods are accessible, this works.
        $templateMgr->register_function('form_language_chooser', ['ReviewFormHandler', 'smartyFormLanguageChooser']);
        $templateMgr->assign('helpTopicId', 'journal.managementPages.reviewForms');
        $templateMgr->display('manager/reviewForms/previewReviewForm.tpl');
    }

    /**
     * Delete a review form.
     * @param array $args first parameter is the ID of the review form to delete
     */
    public function deleteReviewForm($args) {
        $this->validate();

        $reviewFormId = isset($args[0]) ? (int)$args[0] : null;

        $journal = Application::get()->getRequest()->getJournal();
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        $reviewForm = $reviewFormDao->getReviewForm($reviewFormId, ASSOC_TYPE_JOURNAL, $journal->getId());

        $completeCounts = $reviewFormDao->getUseCounts(ASSOC_TYPE_JOURNAL, $journal->getId(), true);
        $incompleteCounts = $reviewFormDao->getUseCounts(ASSOC_TYPE_JOURNAL, $journal->getId(), false);
        if (isset($reviewForm) && $completeCounts[$reviewFormId] == 0 && $incompleteCounts[$reviewFormId] == 0) {
            $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
            $reviewAssignments = $reviewAssignmentDao->getByReviewFormId($reviewFormId);

            foreach ($reviewAssignments as $reviewAssignment) {
                $reviewAssignment->setReviewFormId('');
                $reviewAssignmentDao->updateReviewAssignment($reviewAssignment);
            }

            $reviewFormDao->deleteById($reviewFormId, $journal->getId());
        }

        Application::get()->getRequest()->redirect(null, null, 'reviewForms');
    }

    /**
     * Activate a published review form.
     * @param array $args first parameter is the ID of the review form to activate
     */
    public function activateReviewForm($args) {
        $this->validate();

        $reviewFormId = isset($args[0]) ? (int)$args[0] : null;

        $journal = Application::get()->getRequest()->getJournal();
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        $reviewForm = $reviewFormDao->getReviewForm($reviewFormId, ASSOC_TYPE_JOURNAL, $journal->getId());

        if (isset($reviewForm) && !$reviewForm->getActive()) {
            $reviewForm->setActive(1);
            $reviewFormDao->updateObject($reviewForm);
        }

        Application::get()->getRequest()->redirect(null, null, 'reviewForms');
    }

    /**
     * Deactivate a published review form.
     * @param array $args first parameter is the ID of the review form to deactivate
     */
    public function deactivateReviewForm($args) {
        $this->validate();

        $reviewFormId = isset($args[0]) ? (int)$args[0] : null;

        $journal = Application::get()->getRequest()->getJournal();
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        $reviewForm = $reviewFormDao->getReviewForm($reviewFormId, ASSOC_TYPE_JOURNAL, $journal->getId());

        if (isset($reviewForm) && $reviewForm->getActive()) {
            $reviewForm->setActive(0);
            $reviewFormDao->updateObject($reviewForm);
        }

        Application::get()->getRequest()->redirect(null, null, 'reviewForms');
    }

    /**
     * Copy a published review form.
     * @param array $args
     */
    public function copyReviewForm($args) {
        $this->validate();

        $reviewFormId = isset($args[0]) ? (int)$args[0] : null;

        $journal = Application::get()->getRequest()->getJournal();
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        $reviewForm = $reviewFormDao->getReviewForm($reviewFormId, ASSOC_TYPE_JOURNAL, $journal->getId());

        if (isset($reviewForm)) {
            $reviewForm->setActive(0);
            $reviewForm->setSequence(defined('REALLY_BIG_NUMBER') ? REALLY_BIG_NUMBER : 99999);
            $newReviewFormId = $reviewFormDao->insertObject($reviewForm);
            $reviewFormDao->resequenceReviewForms(ASSOC_TYPE_JOURNAL, $journal->getId());

            $reviewFormElementDao = DAORegistry::getDAO('ReviewFormElementDAO');
            $reviewFormElements = $reviewFormElementDao->getReviewFormElements($reviewFormId);
            foreach ($reviewFormElements as $reviewFormElement) {
                $reviewFormElement->setReviewFormId($newReviewFormId);
                $reviewFormElement->setSequence(defined('REALLY_BIG_NUMBER') ? REALLY_BIG_NUMBER : 99999);
                $reviewFormElementDao->insertObject($reviewFormElement);
                $reviewFormElementDao->resequenceReviewFormElements($newReviewFormId);
            }
        }

        Application::get()->getRequest()->redirect(null, null, 'reviewForms');
    }

    /**
     * Change the sequence of a review form.
     */
    public function moveReviewForm() {
        $this->validate();
        $request = Application::get()->getRequest();

        $journal = $request->getJournal();
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        
        // [SECURITY FIX] Amankan 'id' (reviewFormId) dengan (int) trim()
        $reviewFormId = (int) trim((string) $request->getUserVar('id'));
        $reviewForm = $reviewFormDao->getReviewForm($reviewFormId, ASSOC_TYPE_JOURNAL, $journal->getId());

        if (isset($reviewForm)) {
            // [SECURITY FIX] Whitelist 'd' (direction)
            $direction = trim((string) $request->getUserVar('d'));

            if (!empty($direction)) {
                // moving with up or down arrow
                if ($direction == 'u') {
                    $reviewForm->setSequence($reviewForm->getSequence() - 1.5);
                } elseif ($direction == 'd') {
                    $reviewForm->setSequence($reviewForm->getSequence() + 1.5);
                }
            } else {
                // Dragging and dropping
                // [SECURITY FIX] Amankan 'prevId' (ID formulir) dengan (int) trim()
                $prevId = (int) trim((string) $request->getUserVar('prevId'));
                
                if ($prevId == 0) { // Jika $prevId tidak disetel atau 0
                    $prevSeq = 0;
                } else {
                    // Gunakan $prevId yang sudah diamankan
                    $prevJournal = $reviewFormDao->getReviewForm($prevId);
                    $prevSeq = $prevJournal->getSequence();
                }

                $reviewForm->setSequence($prevSeq + .5);
            }

            $reviewFormDao->updateObject($reviewForm);
            $reviewFormDao->resequenceReviewForms(ASSOC_TYPE_JOURNAL, $journal->getId());
        }

        // Moving up or down with the arrows requires a page reload.
        if (isset($direction) && $direction != null) {
            $request->redirect(null, null, 'reviewForms');
        }
    }

    /**
     * Display a list of the review form elements within a review form.
     * @param array $args
     */
    public function reviewFormElements($args) {
        $this->validate();

        $reviewFormId = isset($args[0]) ? (int) $args[0] : null;

        $journal = Application::get()->getRequest()->getJournal();
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        $reviewForm = $reviewFormDao->getReviewForm($reviewFormId, ASSOC_TYPE_JOURNAL, $journal->getId());
        $completeCounts = $reviewFormDao->getUseCounts(ASSOC_TYPE_JOURNAL, $journal->getId(), true);
        $incompleteCounts = $reviewFormDao->getUseCounts(ASSOC_TYPE_JOURNAL, $journal->getId(), false);

        if (!isset($reviewForm) || $completeCounts[$reviewFormId] != 0 || $incompleteCounts[$reviewFormId] != 0) {
            Application::get()->getRequest()->redirect(null, null, 'reviewForms');
        }

        $rangeInfo = $this->getRangeInfo('reviewFormElements');
        $reviewFormElementDao = DAORegistry::getDAO('ReviewFormElementDAO');
        $reviewFormElements = $reviewFormElementDao->getReviewFormElementsByReviewForm($reviewFormId, $rangeInfo);

        $unusedReviewFormTitles = $reviewFormDao->getTitlesByAssocId(ASSOC_TYPE_JOURNAL, $journal->getId(), 0);

        $this->setupTemplate(true, $reviewForm);
        $templateMgr = TemplateManager::getManager();

        $templateMgr->addJavaScript('lib/pkp/js/lib/jquery/plugins/jquery.tablednd.js');
        $templateMgr->addJavaScript('lib/pkp/js/functions/tablednd.js');

        // [WIZDAM] Removed assign_by_ref
        $templateMgr->assign('unusedReviewFormTitles', $unusedReviewFormTitles);
        $templateMgr->assign('reviewFormElements', $reviewFormElements);
        $templateMgr->assign('reviewFormId', $reviewFormId);
        import('lib.pkp.classes.reviewForm.ReviewFormElement');
        $templateMgr->assign('reviewFormElementTypeOptions', ReviewFormElement::getReviewFormElementTypeOptions());
        $templateMgr->assign('helpTopicId', 'journal.managementPages.reviewForms');
        $templateMgr->display('manager/reviewForms/reviewFormElements.tpl');
    }

    /**
     * Display form to create a new review form element.
     * @param array $args
     */
    public function createReviewFormElement($args) {
        $this->editReviewFormElement($args);
    }

    /**
     * Display form to create/edit a review form element.
     * @param array $args ($reviewFormId, $reviewFormElementId)
     */
    public function editReviewFormElement($args) {
        $this->validate();

        $reviewFormId = isset($args[0]) ? (int)$args[0] : null;
        $reviewFormElementId = isset($args[1]) ? (int) $args[1] : null;

        $journal = Application::get()->getRequest()->getJournal();
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        $reviewForm = $reviewFormDao->getReviewForm($reviewFormId, ASSOC_TYPE_JOURNAL, $journal->getId());
        $completeCounts = $reviewFormDao->getUseCounts(ASSOC_TYPE_JOURNAL, $journal->getId(), true);
        $incompleteCounts = $reviewFormDao->getUseCounts(ASSOC_TYPE_JOURNAL, $journal->getId(), false);
        $reviewFormElementDao = DAORegistry::getDAO('ReviewFormElementDAO');

        if (!isset($reviewForm) || $completeCounts[$reviewFormId] != 0 || $incompleteCounts[$reviewFormId] != 0 || ($reviewFormElementId != null && !$reviewFormElementDao->reviewFormElementExists($reviewFormElementId, $reviewFormId))) {
            Application::get()->getRequest()->redirect(null, null, 'reviewFormElements', [$reviewFormId]);
        }

        $this->setupTemplate(true, $reviewForm);
        $templateMgr = TemplateManager::getManager();

        if ($reviewFormElementId == null) {
            $templateMgr->assign('pageTitle', 'manager.reviewFormElements.create');
        } else {
            $templateMgr->assign('pageTitle', 'manager.reviewFormElements.edit');
        }

        import('classes.manager.form.ReviewFormElementForm');
        $reviewFormElementForm = new ReviewFormElementForm($reviewFormId, $reviewFormElementId);
        if ($reviewFormElementForm->isLocaleResubmit()) {
            $reviewFormElementForm->readInputData();
        } else {
            $reviewFormElementForm->initData();
        }

        $reviewFormElementForm->display();
    }

    /**
     * Save changes to a review form element.
     */
    public function updateReviewFormElement() {
        $this->validate();
        $request = Application::get()->getRequest();

        // [SECURITY FIX] Amankan 'reviewFormId' dengan trim() dan (int)
        $reviewFormIdInput = $request->getUserVar('reviewFormId');
        $reviewFormId = $reviewFormIdInput === null ? null : (int) trim((string) $reviewFormIdInput);
        
        // [SECURITY FIX] Amankan 'reviewFormElementId' dengan trim() dan (int)
        $reviewFormElementIdInput = $request->getUserVar('reviewFormElementId');
        $reviewFormElementId = $reviewFormElementIdInput === null ? null : (int) trim((string) $reviewFormElementIdInput);

        $journal = $request->getJournal();
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        $reviewFormElementDao = DAORegistry::getDAO('ReviewFormElementDAO');

        $reviewForm = $reviewFormDao->getReviewForm($reviewFormId, ASSOC_TYPE_JOURNAL, $journal->getId());
        $this->setupTemplate(true, $reviewForm);

        if (!$reviewFormDao->unusedReviewFormExists($reviewFormId, ASSOC_TYPE_JOURNAL, $journal->getId()) || ($reviewFormElementId != null && !$reviewFormElementDao->reviewFormElementExists($reviewFormElementId, $reviewFormId))) {
            $request->redirect(null, null, 'reviewFormElements', [$reviewFormId]);
        }

        import('classes.manager.form.ReviewFormElementForm');
        $reviewFormElementForm = new ReviewFormElementForm($reviewFormId, $reviewFormElementId);
        $reviewFormElementForm->readInputData();
        $formLocale = $reviewFormElementForm->getFormLocale();

        // Reorder response items
        $response = $reviewFormElementForm->getData('possibleResponses');
        if (isset($response[$formLocale]) && is_array($response[$formLocale])) {
            // [WIZDAM FIX] Replaced deprecated create_function with anonymous Closure
            usort($response[$formLocale], function($a, $b) {
                return $a['order'] <=> $b['order'];
            });
        }
        $reviewFormElementForm->setData('possibleResponses', $response);

        // [SECURITY FIX] Amankan flag boolean 'addResponse' dengan (int) trim()
        if ((int) trim((string) $request->getUserVar('addResponse'))) {
            // Add a response item
            $editData = true;
            $response = $reviewFormElementForm->getData('possibleResponses');
            if (!isset($response[$formLocale]) || !is_array($response[$formLocale])) {
                $response[$formLocale] = [];
                $lastOrder = 0;
            } else {
                $lastOrder = $response[$formLocale][count($response[$formLocale])-1]['order'];
            }
            array_push($response[$formLocale], ['order' => $lastOrder+1]);
            $reviewFormElementForm->setData('possibleResponses', $response);

        } else {
            $delResponseInput = $request->getUserVar('delResponse');
            
            // [SECURITY FIX] Validasi dan amankan 'delResponse' key/index
            if (is_array($delResponseInput) && count($delResponseInput) == 1) {
                // Delete a response item
                $editData = true;
                
                // Ambil key (indeks) dan bersihkan dengan trim() sebelum (int)
                $delResponseIndex = key($delResponseInput);
                $delResponse = (int) trim((string) $delResponseIndex);
                
                // Jika $delResponse adalah array of possibleResponses, $delResponse harus >= 0
                if ($delResponse >= 0) { 
                    $response = $reviewFormElementForm->getData('possibleResponses');
                    if (!isset($response[$formLocale])) $response[$formLocale] = [];
                    
                    // Gunakan index yang sudah diamankan
                    array_splice($response[$formLocale], $delResponse, 1);
                    $reviewFormElementForm->setData('possibleResponses', $response);
                }
            }
        }

        if (!isset($editData) && $reviewFormElementForm->validate()) {
            $reviewFormElementForm->execute();
            $request->redirect(null, null, 'reviewFormElements', [$reviewFormId]);
        } else {
            $templateMgr = TemplateManager::getManager();
            if ($reviewFormElementId == null) {
                $templateMgr->assign('pageTitle', 'manager.reviewFormElements.create');
            } else {
                $templateMgr->assign('pageTitle', 'manager.reviewFormElements.edit');
            }

            $reviewFormElementForm->display();
        }
    }

    /**
     * Delete a review form element.
     * @param array $args ($reviewFormId, $reviewFormElementId)
     */
    public function deleteReviewFormElement($args) {
        $this->validate();

        $reviewFormId = isset($args[0]) ? (int)$args[0] : null;
        $reviewFormElementId = isset($args[1]) ? (int) $args[1] : null;

        $journal = Application::get()->getRequest()->getJournal();
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');

        if ($reviewFormDao->unusedReviewFormExists($reviewFormId, ASSOC_TYPE_JOURNAL, $journal->getId())) {
            $reviewFormElementDao = DAORegistry::getDAO('ReviewFormElementDAO');
            $reviewFormElementDao->deleteById($reviewFormElementId);
        }
        Application::get()->getRequest()->redirect(null, null, 'reviewFormElements', [$reviewFormId]);
    }

    /**
     * Change the sequence of a review form element.
     * @param array $args
     * @param PKPRequest $request
     */
    public function moveReviewFormElement($args, $request) {
        $this->validate();

        $journal = $request->getJournal();
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        $reviewFormElementDao = DAORegistry::getDAO('ReviewFormElementDAO');
        // [SECURITY FIX] Amankan 'id' (reviewFormElementId) dengan (int) trim()
        $reviewFormElementId = (int) trim((string) $request->getUserVar('id'));
        $reviewFormElement = $reviewFormElementDao->getReviewFormElement($reviewFormElementId);

        if (!isset($reviewFormElement) || !$reviewFormDao->unusedReviewFormExists($reviewFormElement->getReviewFormId(), ASSOC_TYPE_JOURNAL, $journal->getId())) {
            $request->redirect(null, null, 'reviewForms');
        }

        // [SECURITY FIX] Whitelist 'd' (direction)
        $direction = trim((string) $request->getUserVar('d'));

        if (!empty($direction)) {
            // moving with up or down arrow
            if ($direction == 'u') {
                $reviewFormElement->setSequence($reviewFormElement->getSequence() - 1.5);
            } elseif ($direction == 'd') {
                $reviewFormElement->setSequence($reviewFormElement->getSequence() + 1.5);
            }
        } else {
            // drag and drop
            // [SECURITY FIX] Amankan 'prevId' (ID elemen) dengan (int) trim()
            $prevId = (int) trim((string) $request->getUserVar('prevId'));
            
            if ($prevId == 0) { // $prevId akan 0 jika null/kosong karena (int) casting
                $prevSeq = 0;
            } else {
                // Gunakan $prevId yang sudah diamankan
                $prevReviewFormElement = $reviewFormElementDao->getReviewFormElement($prevId);
                $prevSeq = $prevReviewFormElement->getSequence();
            }

            $reviewFormElement->setSequence($prevSeq + .5);
        }

        $reviewFormElementDao->updateObject($reviewFormElement);
        $reviewFormElementDao->resequenceReviewFormElements($reviewFormElement->getReviewFormId());

        // Moving up or down with the arrows requires a page reload.
        if (isset($direction) && $direction != null) {
            $request->redirect(null, null, 'reviewFormElements', [$reviewFormElement->getReviewFormId()]);
        }
    }

    /**
     * Copy review form elemnts to another review form.
     */
    public function copyReviewFormElement() {
        $this->validate();
        $request = Application::get()->getRequest();

        // [SECURITY FIX] Amankan 'copy' sebagai flag boolean/integer dengan (int) trim()
        // Note: The original code expected 'copy' to be an array of IDs from checkboxes?
        // Let's re-examine original: "if (is_array($copy)...)"
        // If it's checkboxes, it comes as array.
        $copy = $request->getUserVar('copy');
        
        // [SECURITY FIX] Amankan 'targetReviewFormId' (ID integer) dengan (int) trim()
        $targetReviewFormId = (int) trim((string) $request->getUserVar('targetReviewForm'));

        $journal = $request->getJournal();
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');

        if (is_array($copy) && $reviewFormDao->unusedReviewFormExists($targetReviewFormId, ASSOC_TYPE_JOURNAL, $journal->getId())) {
            $reviewFormElementDao = DAORegistry::getDAO('ReviewFormElementDAO');
            foreach ($copy as $reviewFormElementId) {
                // Sanitize ID
                $reviewFormElementId = (int) $reviewFormElementId;
                $reviewFormElement = $reviewFormElementDao->getReviewFormElement($reviewFormElementId);
                if (isset($reviewFormElement) && $reviewFormDao->unusedReviewFormExists($reviewFormElement->getReviewFormId(), ASSOC_TYPE_JOURNAL, $journal->getId())) {
                    $reviewFormElement->setReviewFormId($targetReviewFormId);
                    $reviewFormElement->setSequence(defined('REALLY_BIG_NUMBER') ? REALLY_BIG_NUMBER : 99999);
                    $reviewFormElementDao->insertObject($reviewFormElement);
                    $reviewFormElementDao->resequenceReviewFormElements($targetReviewFormId);
                }
                unset($reviewFormElement);
            }
        }

        $request->redirect(null, null, 'reviewFormElements', [$targetReviewFormId]);
    }

    /**
     * @param bool $subclass
     * @param ReviewForm|null $reviewForm
     */
    public function setupTemplate($subclass = false, $reviewForm = null) {
        parent::setupTemplate(true);
        $templateMgr = TemplateManager::getManager();
        $request = Application::get()->getRequest();
        
        if ($subclass) {
            $templateMgr->append('pageHierarchy', [$request->url(null, 'manager', 'reviewForms'), 'manager.reviewForms']);
        }
        if ($reviewForm) {
            $templateMgr->append('pageHierarchy', [$request->url(null, 'manager', 'editReviewForm', $reviewForm->getId()), $reviewForm->getLocalizedTitle(), true]);
        }
    }
}
?>