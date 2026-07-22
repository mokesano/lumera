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

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journal = $request->getJournal();
        $rangeInfo = $this->getRangeInfo('reviewForms');

        /** @var ReviewFormDAO $reviewFormDao */
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        $reviewForms = $reviewFormDao->getByAssocId(ASSOC_TYPE_JOURNAL, $journal->getId(), $rangeInfo);
        
        $templateMgr = TemplateManager::getManager();
        $templateMgr->addJavaScript('lib/pkp/js/lib/jquery/plugins/jquery.tablednd.js');
        $templateMgr->addJavaScript('lib/pkp/js/functions/tablednd.js');
        
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
     * @param array $args
     */
    public function editReviewForm($args = []) {
        $this->validate();

        $reviewFormId = isset($args[0]) ? (int)$args[0] : null;

        $journal = Application::get()->getRequest()->getJournal();

        /** @var ReviewFormDAO $reviewFormDao */
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

        $reviewFormIdInput = $request->getUserVar('reviewFormId');
        $reviewFormId = $reviewFormIdInput === null ? null : (int) trim((string) $reviewFormIdInput);

        $journal = $request->getJournal();
        /** @var ReviewFormDAO $reviewFormDao */
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
     * @param array $args
     */
    public function previewReviewForm($args) {
        $this->validate();

        $reviewFormId = isset($args[0]) ? (int)$args[0] : null;
        $journal = Application::get()->getRequest()->getJournal();

        /** @var ReviewFormDAO $reviewFormDao */
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        $reviewForm = $reviewFormDao->getReviewForm($reviewFormId, ASSOC_TYPE_JOURNAL, $journal->getId());

        /** @var ReviewFormElementDAO $reviewFormElementDao */
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
     * @param array $args
     */
    public function deleteReviewForm($args) {
        $this->validate();

        $reviewFormId = isset($args[0]) ? (int)$args[0] : null;
        $journal = Application::get()->getRequest()->getJournal();

        /** @var ReviewFormDAO $reviewFormDao */
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        $reviewForm = $reviewFormDao->getReviewForm($reviewFormId, ASSOC_TYPE_JOURNAL, $journal->getId());
        $completeCounts = $reviewFormDao->getUseCounts(ASSOC_TYPE_JOURNAL, $journal->getId(), true);
        $incompleteCounts = $reviewFormDao->getUseCounts(ASSOC_TYPE_JOURNAL, $journal->getId(), false);

        if (isset($reviewForm) && $completeCounts[$reviewFormId] == 0 && $incompleteCounts[$reviewFormId] == 0) {
            /** @var ReviewAssignmentDAO $reviewAssignmentDao */
            $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
            $reviewAssignments = $reviewAssignmentDao->getByReviewFormId($reviewFormId);

            foreach ($reviewAssignments as $reviewAssignment) {
                $reviewAssignment->setReviewFormId('');
                $reviewAssignmentDao->updateReviewAssignment($reviewAssignment);
            }

            $reviewFormDao->deleteById($reviewFormId);
        }

        Application::get()->getRequest()->redirect(null, null, 'reviewForms');
    }

    /**
     * Activate a published review form.
     * @param array $args
     */
    public function activateReviewForm($args) {
        $this->validate();

        $reviewFormId = isset($args[0]) ? (int)$args[0] : null;
        $journal = Application::get()->getRequest()->getJournal();

        /** @var ReviewFormDAO $reviewFormDao */
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
     * @param array $args
     */
    public function deactivateReviewForm($args) {
        $this->validate();

        $reviewFormId = isset($args[0]) ? (int)$args[0] : null;
        $journal = Application::get()->getRequest()->getJournal();

        /** @var ReviewFormDAO $reviewFormDao */
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

        /** @var ReviewFormDAO $reviewFormDao */
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        $reviewForm = $reviewFormDao->getReviewForm($reviewFormId, ASSOC_TYPE_JOURNAL, $journal->getId());

        if (isset($reviewForm)) {
            $reviewForm->setActive(0);
            $reviewForm->setSequence(defined('REALLY_BIG_NUMBER') ? REALLY_BIG_NUMBER : 999999);
            $newReviewFormId = $reviewFormDao->insertObject($reviewForm);
            $reviewFormDao->resequenceReviewForms(ASSOC_TYPE_JOURNAL, $journal->getId());

            /** @var ReviewFormElementDAO $reviewFormElementDao */
            $reviewFormElementDao = DAORegistry::getDAO('ReviewFormElementDAO');
            $reviewFormElements = $reviewFormElementDao->getReviewFormElements($reviewFormId);
            foreach ($reviewFormElements as $reviewFormElement) {
                $reviewFormElement->setReviewFormId($newReviewFormId);
                $reviewFormElement->setSequence(defined('REALLY_BIG_NUMBER') ? REALLY_BIG_NUMBER : 999999);
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

        /** @var ReviewFormDAO $reviewFormDao */
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        $reviewFormId = (int) trim((string) $request->getUserVar('id'));
        $reviewForm = $reviewFormDao->getReviewForm($reviewFormId, ASSOC_TYPE_JOURNAL, $journal->getId());

        if (isset($reviewForm)) {
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
                $prevId = (int) trim((string) $request->getUserVar('prevId'));
                
                if ($prevId == 0) {
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

        /** @var ReviewFormDAO $reviewFormDao */
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        $reviewForm = $reviewFormDao->getReviewForm($reviewFormId, ASSOC_TYPE_JOURNAL, $journal->getId());
        $completeCounts = $reviewFormDao->getUseCounts(ASSOC_TYPE_JOURNAL, $journal->getId(), true);
        $incompleteCounts = $reviewFormDao->getUseCounts(ASSOC_TYPE_JOURNAL, $journal->getId(), false);

        if (!isset($reviewForm) || $completeCounts[$reviewFormId] != 0 || $incompleteCounts[$reviewFormId] != 0) {
            Application::get()->getRequest()->redirect(null, null, 'reviewForms');
        }

        $rangeInfo = $this->getRangeInfo('reviewFormElements');
        /** @var ReviewFormElementDAO $reviewFormElementDao */
        $reviewFormElementDao = DAORegistry::getDAO('ReviewFormElementDAO');
        $reviewFormElements = $reviewFormElementDao->getReviewFormElementsByReviewForm($reviewFormId, $rangeInfo);

        $unusedReviewFormTitles = $reviewFormDao->getTitlesByAssocId(ASSOC_TYPE_JOURNAL, $journal->getId(), 0);

        $this->setupTemplate(true, $reviewForm);
        $templateMgr = TemplateManager::getManager();

        $templateMgr->addJavaScript('lib/pkp/js/lib/jquery/plugins/jquery.tablednd.js');
        $templateMgr->addJavaScript('lib/pkp/js/functions/tablednd.js');

        $templateMgr->assign('unusedReviewFormTitles', $unusedReviewFormTitles);
        $templateMgr->assign('reviewFormElements', $reviewFormElements);
        $templateMgr->assign('reviewFormId', $reviewFormId);

        import('lib.pkp.classes.reviewForm.ReviewFormElement');
        $reviewFormElement = new ReviewFormElement();
        $templateMgr->assign('reviewFormElementTypeOptions', $reviewFormElement->getReviewFormElementTypeOptions());
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

        /** @var ReviewFormDAO $reviewFormDao */
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        $reviewForm = $reviewFormDao->getReviewForm($reviewFormId, ASSOC_TYPE_JOURNAL, $journal->getId());
        $completeCounts = $reviewFormDao->getUseCounts(ASSOC_TYPE_JOURNAL, $journal->getId(), true);
        $incompleteCounts = $reviewFormDao->getUseCounts(ASSOC_TYPE_JOURNAL, $journal->getId(), false);

        /** @var ReviewFormElementDAO $reviewFormElementDao */
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

        $reviewFormIdInput = $request->getUserVar('reviewFormId');
        $reviewFormId = $reviewFormIdInput === null ? null : (int) trim((string) $reviewFormIdInput);
        $reviewFormElementIdInput = $request->getUserVar('reviewFormElementId');
        $reviewFormElementId = $reviewFormElementIdInput === null ? null : (int) trim((string) $reviewFormElementIdInput);

        $journal = $request->getJournal();
        /** @var ReviewFormDAO $reviewFormDao */
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        /** @var ReviewFormElementDAO $reviewFormElementDao */
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
            usort($response[$formLocale], function($a, $b) {
                return $a['order'] <=> $b['order'];
            });
        }
        $reviewFormElementForm->setData('possibleResponses', $response);

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

            if (is_array($delResponseInput) && count($delResponseInput) == 1) {
                $editData = true;
                $delResponseIndex = key($delResponseInput);
                $delResponse = (int) trim((string) $delResponseIndex);

                if ($delResponse >= 0) { 
                    $response = $reviewFormElementForm->getData('possibleResponses');
                    if (!isset($response[$formLocale])) $response[$formLocale] = [];

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

        /** @var ReviewFormDAO $reviewFormDao */
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        if ($reviewFormDao->unusedReviewFormExists($reviewFormId, ASSOC_TYPE_JOURNAL, $journal->getId())) {
            /** @var ReviewFormElementDAO $reviewFormElementDao */
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

        /** @var ReviewFormDAO $reviewFormDao */
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        /** @var ReviewFormElementDAO $reviewFormElementDao */
        $reviewFormElementDao = DAORegistry::getDAO('ReviewFormElementDAO');
        $reviewFormElementId = (int) trim((string) $request->getUserVar('id'));
        $reviewFormElement = $reviewFormElementDao->getReviewFormElement($reviewFormElementId);

        if (!isset($reviewFormElement) || !$reviewFormDao->unusedReviewFormExists($reviewFormElement->getReviewFormId(), ASSOC_TYPE_JOURNAL, $journal->getId())) {
            $request->redirect(null, null, 'reviewForms');
        }

        $direction = trim((string) $request->getUserVar('d'));
        if (!empty($direction)) {
            if ($direction == 'u') {
                $reviewFormElement->setSequence($reviewFormElement->getSequence() - 1.5);
            } elseif ($direction == 'd') {
                $reviewFormElement->setSequence($reviewFormElement->getSequence() + 1.5);
            }
        } else {
            $prevId = (int) trim((string) $request->getUserVar('prevId'));
            
            if ($prevId == 0) {
                $prevSeq = 0;
            } else {
                $prevReviewFormElement = $reviewFormElementDao->getReviewFormElement($prevId);
                $prevSeq = $prevReviewFormElement->getSequence();
            }

            $reviewFormElement->setSequence($prevSeq + .5);
        }

        $reviewFormElementDao->updateObject($reviewFormElement);
        $reviewFormElementDao->resequenceReviewFormElements($reviewFormElement->getReviewFormId());

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

        $copy = $request->getUserVar('copy');
        $targetReviewFormId = (int) trim((string) $request->getUserVar('targetReviewForm'));
        $journal = $request->getJournal();

        /** @var ReviewFormDAO $reviewFormDao */
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        if (is_array($copy) && $reviewFormDao->unusedReviewFormExists($targetReviewFormId, ASSOC_TYPE_JOURNAL, $journal->getId())) {
            /** @var ReviewFormElementDAO $reviewFormElementDao */
            $reviewFormElementDao = DAORegistry::getDAO('ReviewFormElementDAO');

            foreach ($copy as $reviewFormElementId) {
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