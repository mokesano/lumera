<?php
declare(strict_types=1);

/**
 * @file pages/manager/SetupHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SetupHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for journal setup functions and Checkout Blueprint.
 */

import('pages.manager.ManagerHandler');
import('lib.pkp.classes.validation.ValidatorCSRF');

class SetupHandler extends ManagerHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SetupHandler() {
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
     * Display journal setup form for the selected step AND handle POST submission.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function setup(array $args = [], $request = null): void {
        $this->validate();
        $this->setupTemplate(true);

        // [LUMERA] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $step = isset($args[0]) ? (int) $args[0] : 0;

        if ($step >= 1 && $step <= 5) {
            $formClass = "JournalSetupStep{$step}Form";
            import("classes.manager.form.setup.$formClass");

            $setupForm = new $formClass();

            // =================================================================
            // TAHAP POST: Memproses Form Submission (Blueprint `billing()`)
            // =================================================================
            if ($request->isPost()) {
                $setupForm->readInputData();
                $formLocale = $setupForm->getFormLocale();
                $notificationMessageKey = null;

                // Check for any special cases before trying to save
                switch ($step) {
                    case 1:
                        if ((array) $request->getUserVar('addSponsor')) {
                            $editData = true;
                            $sponsors = $setupForm->getData('sponsors');
                            array_push($sponsors, []);
                            $setupForm->setData('sponsors', $sponsors);
                            $notificationMessageKey = 'manager.setup.notification.sponsorAdded';

                        } elseif (($delSponsor = (array) $request->getUserVar('delSponsor')) && count($delSponsor) == 1) {
                            $editData = true;
                            list($delSponsor) = array_keys($delSponsor);
                            $delSponsor = (int) $delSponsor;
                            $sponsors = $setupForm->getData('sponsors');
                            array_splice($sponsors, $delSponsor, 1);
                            $setupForm->setData('sponsors', $sponsors);
                            $notificationMessageKey = 'manager.setup.notification.sponsorRemoved';

                        } elseif ((array) $request->getUserVar('addContributor')) {
                            $editData = true;
                            $contributors = $setupForm->getData('contributors');
                            array_push($contributors, []);
                            $setupForm->setData('contributors', $contributors);
                            $notificationMessageKey = 'manager.setup.notification.contributorAdded';

                        } elseif (($delContributor = (array) $request->getUserVar('delContributor')) && count($delContributor) == 1) {
                            $editData = true;
                            list($delContributor) = array_keys($delContributor);
                            $delContributor = (int) $delContributor;
                            $contributors = $setupForm->getData('contributors');
                            array_splice($contributors, $delContributor, 1);
                            $setupForm->setData('contributors', $contributors);
                            $notificationMessageKey = 'manager.setup.notification.contributorRemoved';
                        }
                        break;

                    case 2:
                        if ((array) $request->getUserVar('addCustomAboutItem')) {
                            $editData = true;
                            $customAboutItems = $setupForm->getData('customAboutItems');
                            $customAboutItems[$formLocale][] = [];
                            $setupForm->setData('customAboutItems', $customAboutItems);
                            $notificationMessageKey = 'manager.setup.notification.itemAdded';

                        } elseif (($delCustomAboutItem = (array) $request->getUserVar('delCustomAboutItem')) && count($delCustomAboutItem) == 1) {
                            $editData = true;
                            list($delCustomAboutItem) = array_keys($delCustomAboutItem);
                            $delCustomAboutItem = (int) $delCustomAboutItem;
                            $customAboutItems = $setupForm->getData('customAboutItems');
                            if (!isset($customAboutItems[$formLocale])) $customAboutItems[$formLocale][] = [];
                            array_splice($customAboutItems[$formLocale], $delCustomAboutItem, 1);
                            $setupForm->setData('customAboutItems', $customAboutItems);
                            $notificationMessageKey = 'manager.setup.notification.itemRemoved';
                        }
                        if ((array) $request->getUserVar('addReviewerDatabaseLink')) {
                            $editData = true;
                            $reviewerDatabaseLinks = $setupForm->getData('reviewerDatabaseLinks');
                            array_push($reviewerDatabaseLinks, []);
                            $setupForm->setData('reviewerDatabaseLinks', $reviewerDatabaseLinks);
                            $notificationMessageKey = 'manager.setup.notification.itemAdded';

                        } elseif (($delReviewerDatabaseLink = (array) $request->getUserVar('delReviewerDatabaseLink')) && count($delReviewerDatabaseLink) == 1) {
                            $editData = true;
                            list($delReviewerDatabaseLink) = array_keys($delReviewerDatabaseLink);
                            $delReviewerDatabaseLink = (int) $delReviewerDatabaseLink;
                            $reviewerDatabaseLinks = $setupForm->getData('reviewerDatabaseLinks');
                            array_splice($reviewerDatabaseLinks, $delReviewerDatabaseLink, 1);
                            $setupForm->setData('reviewerDatabaseLinks', $reviewerDatabaseLinks);
                            $notificationMessageKey = 'manager.setup.notification.itemRemoved';
                        }
                        break;

                    case 3:
                        if ((array) $request->getUserVar('addChecklist')) {
                            $editData = true;
                            $checklist = $setupForm->getData('submissionChecklist');
                            if (!isset($checklist[$formLocale]) || !is_array($checklist[$formLocale])) {
                                $checklist[$formLocale] = [];
                                $lastOrder = 0;
                            } else {
                                $lastOrder = $checklist[$formLocale][count($checklist[$formLocale])-1]['order'];
                            }
                            array_push($checklist[$formLocale], ['order' => $lastOrder+1]);
                            $setupForm->setData('submissionChecklist', $checklist);
                            $notificationMessageKey = 'manager.setup.notification.itemAdded';

                        } elseif (($delChecklist = (array) $request->getUserVar('delChecklist')) && count($delChecklist) == 1) {
                            $editData = true;
                            list($delChecklist) = array_keys($delChecklist);
                            $delChecklist = (int) $delChecklist;
                            $checklist = $setupForm->getData('submissionChecklist');
                            if (!isset($checklist[$formLocale])) $checklist[$formLocale] = [];
                            array_splice($checklist[$formLocale], $delChecklist, 1);
                            $setupForm->setData('submissionChecklist', $checklist);
                            $notificationMessageKey = 'manager.setup.notification.itemRemoved';
                        }

                        if (!isset($editData)) {
                            $checklist = $setupForm->getData('submissionChecklist');
                            if (isset($checklist[$formLocale]) && is_array($checklist[$formLocale])) {
                                usort($checklist[$formLocale], function($a, $b) {
                                    return $a['order'] <=> $b['order'];
                                });
                            } elseif (!isset($checklist[$formLocale])) {
                                $checklist[$formLocale] = [];
                            }
                            $setupForm->setData('submissionChecklist', $checklist);
                        }
                        break;

                    case 4:
                        $router = $request->getRouter();
                        $journal = $router->getContext($request);
                        $templates = $journal->getSetting('templates');
                        import('classes.file.JournalFileManager');
                        $journalFileManager = new JournalFileManager($journal);
                        if ((array) $request->getUserVar('addTemplate')) {
                            $editData = true;
                            if (!is_array($templates)) $templates = [];
                            $templateId = count($templates);
                            $originalFilename = $_FILES['template-file']['name'];
                            $fileType = $journalFileManager->getUploadedFileType('template-file');
                            $filename = "template-$templateId." . $journalFileManager->parseFileExtension($originalFilename);
                            $journalFileManager->uploadFile('template-file', $filename);
                            $templates[$templateId] = [
                                'originalFilename' => $originalFilename,
                                'fileType' => $fileType,
                                'filename' => $filename,
                                'title' => htmlspecialchars(trim((string) $request->getUserVar('template-title')), ENT_QUOTES, 'UTF-8')
                            ];
                            $journal->updateSetting('templates', $templates);
                            $notificationMessageKey = 'manager.setup.notification.fileUploaded';
                            
                        } elseif (($delTemplate = (array) $request->getUserVar('delTemplate')) && count($delTemplate) == 1) {
                            $editData = true;
                            list($delTemplate) = array_keys($delTemplate);
                            $delTemplate = (int) $delTemplate;
                            $template = $templates[$delTemplate];
                            $filename = "template-$delTemplate." . $journalFileManager->parseFileExtension($template['originalFilename']);
                            $journalFileManager->deleteFile($filename);
                            array_splice($templates, $delTemplate, 1);
                            $journal->updateSetting('templates', $templates);
                            $notificationMessageKey = 'manager.setup.notification.fileRemoved';
                        }
                        $setupForm->setData('templates', $templates);
                        break;

                    case 5:
                        if ((array) $request->getUserVar('uploadHomeHeaderTitleImage')) {
                            if ($setupForm->uploadImage('homeHeaderTitleImage', $formLocale)) {
                                $editData = true;
                                $notificationMessageKey = 'manager.setup.notification.fileUploaded';
                            } else {
                                $setupForm->addError('homeHeaderTitleImage', __('manager.setup.homeTitleImageInvalid'));
                            }
                        } elseif ((array) $request->getUserVar('deleteHomeHeaderTitleImage')) {
                            $editData = true;
                            $setupForm->deleteImage('homeHeaderTitleImage', $formLocale);
                            $notificationMessageKey = 'manager.setup.notification.fileRemoved';
                        } elseif ((array) $request->getUserVar('uploadHomeHeaderLogoImage')) {
                            if ($setupForm->uploadImage('homeHeaderLogoImage', $formLocale)) {
                                $editData = true;
                                $notificationMessageKey = 'manager.setup.notification.fileUploaded';
                            } else {
                                $setupForm->addError('homeHeaderLogoImage', __('manager.setup.homeHeaderImageInvalid'));
                            }
                        } elseif ((array) $request->getUserVar('deleteHomeHeaderLogoImage')) {
                            $editData = true;
                            $setupForm->deleteImage('homeHeaderLogoImage', $formLocale);
                            $notificationMessageKey = 'manager.setup.notification.fileRemoved';
                        } elseif ((array) $request->getUserVar('uploadJournalThumbnail')) {
                            if ($setupForm->uploadImage('journalThumbnail', $formLocale)) {
                                $editData = true;
                                $notificationMessageKey = 'manager.setup.notification.fileUploaded';
                            } else {
                                $setupForm->addError('journalThumbnail', __('manager.setup.journalThumbnailInvalid'));
                            }
                        } elseif ((array) $request->getUserVar('deleteJournalThumbnail')) {
                            $editData = true;
                            $setupForm->deleteImage('journalThumbnail', $formLocale);
                            $notificationMessageKey = 'manager.setup.notification.fileRemoved';
                        } elseif ((array) $request->getUserVar('uploadJournalFavicon')) {
                            if ($setupForm->uploadImage('journalFavicon', $formLocale)) {
                                $editData = true;
                                $notificationMessageKey = 'manager.setup.notification.fileUploaded';
                            } else {
                                $setupForm->addError('journalFavicon', __('manager.setup.journalFaviconInvalid'));
                            }
                        } elseif ((array) $request->getUserVar('deleteJournalFavicon')) {
                            $editData = true;
                            $setupForm->deleteImage('journalFavicon', $formLocale);
                            $notificationMessageKey = 'manager.setup.notification.fileRemoved';
                        } elseif ((array) $request->getUserVar('uploadPageHeaderTitleImage')) {
                            if ($setupForm->uploadImage('pageHeaderTitleImage', $formLocale)) {
                                $editData = true;
                                $notificationMessageKey = 'manager.setup.notification.fileUploaded';
                            } else {
                                $setupForm->addError('pageHeaderTitleImage', __('manager.setup.pageHeaderTitleImageInvalid'));
                            }
                        } elseif ((array) $request->getUserVar('deletePageHeaderTitleImage')) {
                            $editData = true;
                            $setupForm->deleteImage('pageHeaderTitleImage', $formLocale);
                            $notificationMessageKey = 'manager.setup.notification.fileRemoved';
                        } elseif ((array) $request->getUserVar('uploadPageHeaderLogoImage')) {
                            if ($setupForm->uploadImage('pageHeaderLogoImage', $formLocale)) {
                                $editData = true;
                                $notificationMessageKey = 'manager.setup.notification.fileUploaded';
                            } else {
                                $setupForm->addError('pageHeaderLogoImage', __('manager.setup.pageHeaderLogoImageInvalid'));
                            }
                        } elseif ((array) $request->getUserVar('deletePageHeaderLogoImage')) {
                            $editData = true;
                            $setupForm->deleteImage('pageHeaderLogoImage', $formLocale);
                            $notificationMessageKey = 'manager.setup.notification.fileRemoved';
                        } elseif ((array) $request->getUserVar('uploadHomepageImage')) {
                            if ($setupForm->uploadImage('homepageImage', $formLocale)) {
                                $editData = true;
                                $notificationMessageKey = 'manager.setup.notification.fileUploaded';
                            } else {
                                $setupForm->addError('homepageImage', __('manager.setup.homepageImageInvalid'));
                            }
                        } elseif ((array) $request->getUserVar('deleteHomepageImage')) {
                            $editData = true;
                            $setupForm->deleteImage('homepageImage', $formLocale);
                            $notificationMessageKey = 'manager.setup.notification.fileRemoved';
                        } elseif ((array) $request->getUserVar('uploadJournalStyleSheet')) {
                            if ($setupForm->uploadStyleSheet('journalStyleSheet')) {
                                $editData = true;
                                $notificationMessageKey = 'manager.setup.notification.fileUploaded';
                            } else {
                                $setupForm->addError('journalStyleSheet', __('manager.setup.journalStyleSheetInvalid'));
                            }
                        } elseif ((array) $request->getUserVar('deleteJournalStyleSheet')) {
                            $editData = true;
                            $setupForm->deleteImage('journalStyleSheet');
                            $notificationMessageKey = 'manager.setup.notification.fileRemoved';
                        } elseif ((array) $request->getUserVar('addNavItem')) {
                            $editData = true;
                            $navItems = $setupForm->getData('navItems');
                            $navItems[$formLocale][] = [];
                            $setupForm->setData('navItems', $navItems);
                            $notificationMessageKey = 'manager.setup.notification.itemAdded';
                        } elseif (($delNavItem = (array) $request->getUserVar('delNavItem')) && count($delNavItem) == 1) {
                            $editData = true;
                            list($delNavItem) = array_keys($delNavItem);
                            $delNavItem = (int) $delNavItem;
                            $navItems = $setupForm->getData('navItems');
                            if (is_array($navItems) && is_array($navItems[$formLocale])) {
                                array_splice($navItems[$formLocale], $delNavItem, 1);
                                $setupForm->setData('navItems', $navItems);
                                $notificationMessageKey = 'manager.setup.notification.itemRemoved';
                            }
                        }
                        break;
                }

                // [LUMERA] NOTIF: flash message untuk aksi (add/delete/upload).
                if (!empty($editData) && $notificationMessageKey) {
                    $this->_notifyAction($request, $notificationMessageKey);
                }

                if (!isset($editData) && $setupForm->validate()) {
                    $setupForm->execute();
                    // [LUMERA] NOTIF: Pesan sukses default.
                    $this->_notifyAction($request);
                    $request->redirect(null, null, 'setupSaved', [$step]);
                    return;
                }

                // Fallthrough: Form render ulang jika editData atau error validasi
                $setupForm->display($request);
                return;
            }

            // =================================================================
            // TAHAP GET: Render Awal Form (Blueprint `billing()`)
            // =================================================================
            if ($setupForm->isLocaleResubmit()) {
                $setupForm->readInputData();
            } else {
                $setupForm->initData();
            }

            $setupForm->display($request);

        } else {
            $templateMgr = TemplateManager::getManager($request);
            $templateMgr->assign('helpTopicId', 'journal.managementPages.setup');
            $templateMgr->display('manager/setup/index.tpl');
        }
    }

    /**
     * [LUMERA] Save changes to journal settings.
     * [SHIM] Backward Compatibility.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function saveSetup(array $args = [], $request = null): void {
        $this->setup($args, $request);
    }

    /**
     * Display a "Settings Saved" message
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function setupSaved(array $args = [], $request = null): void {
        $this->validate();

        // [LUMERA] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $step = isset($args[0]) ? (int) $args[0] : 0;

        if ($step >= 1 && $step <= 5) {
            $this->setupTemplate(true);

            $templateMgr = TemplateManager::getManager($request);
            $templateMgr->assign('setupStep', $step);
            $templateMgr->assign('helpTopicId', 'journal.managementPages.setup');
            $templateMgr->display('manager/setup/settingsSaved.tpl');
        } else {
            $request->redirect(null, 'index');
        }
    }

    /**
     * Download a layout template.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function downloadLayoutTemplate(array $args = [], $request = null): void {
        $this->validate();

        // [LUMERA] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $router = $request->getRouter();
        $journal = $router->getContext($request);
        $templates = $journal->getSetting('templates');
        import('classes.file.JournalFileManager');
        $journalFileManager = new JournalFileManager($journal);
        $templateId = (int) array_shift($args);
        if ($templateId >= count($templates) || $templateId < 0) $request->redirect(null, null, 'setup');
        $template = $templates[$templateId];

        $filename = "template-$templateId." . $journalFileManager->parseFileExtension($template['originalFilename']);
        $journalFileManager->downloadFile($filename, $template['fileType']);
    }

    /**
     * Reset the license attached to article content.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function resetPermissions(array $args = [], $request = null): void {
        $this->validate();

        // [LUMERA] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $router = $request->getRouter();
        $journal = $router->getContext($request);

        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $articleDao->resetPermissions($journal->getId());

        $request->redirect(null, null, 'setup', ['3']);
    }

    /**
     * [LUMERA] Kirim notifikasi trivial (flash message) ke user setelah aksi.
     * @param PKPRequest $request
     * @param string|null $localeKey
     */
    private function _notifyAction($request, $localeKey = null): void {
        import('classes.notification.NotificationManager');
        $notificationManager = new NotificationManager();
        $user = $request->getUser();
        if (!$user) return;

        if ($localeKey) {
            $notificationManager->createTrivialNotification(
                $user->getId(),
                NOTIFICATION_TYPE_SUCCESS,
                ['contents' => __($localeKey)]
            );
        } else {
            $notificationManager->createTrivialNotification($user->getId());
        }
    }
    
}
?>