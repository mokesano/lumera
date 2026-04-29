<?php
declare(strict_types=1);

/**
 * @file pages/manager/EmailHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EmailHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for email management functions. 
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.manager.ManagerHandler');

class EmailHandler extends ManagerHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function EmailHandler() {
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
     * Display a list of the emails within the current journal.
     * @param array $args
     * @param PKPRequest $request
     */
    public function emails($args = [], $request = null) {
        $this->validate();
        $this->setupTemplate(true);

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $rangeInfo = $this->getRangeInfo('emails');

        $journal = $request->getJournal();
        $emailTemplateDao = DAORegistry::getDAO('EmailTemplateDAO');
        $emailTemplates = $emailTemplateDao->getEmailTemplates(AppLocale::getLocale(), $journal->getId());

        import('lib.pkp.classes.core.ArrayItemIterator');
        $emailTemplates = ArrayItemIterator::fromRangeInfo($emailTemplates, $rangeInfo);

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('pageHierarchy', [[$request->url(null, 'manager'), 'manager.journalManagement']]);
        // [WIZDAM] Removed assign_by_ref
        $templateMgr->assign('emailTemplates', $emailTemplates);
        $templateMgr->assign('helpTopicId', 'journal.managementPages.emails');
        $templateMgr->display('manager/emails/emails.tpl');
    }

    /**
     * Create an empty email template.
     * @param array $args
     * @param PKPRequest $request
     */
    public function createEmail($args, $request) {
        $this->editEmail($args, $request);
    }

    /**
     * Display form to create/edit an email.
     * @param array $args if set the first parameter is the key of the email template to edit
     * @param PKPRequest $request
     */
    public function editEmail($args, $request) {
        $this->validate();
        $this->setupTemplate(true);

        $journal = $request->getJournal();
        $templateMgr = TemplateManager::getManager();
        $templateMgr->append('pageHierarchy', [$request->url(null, 'manager', 'emails'), 'manager.emails']);

        $emailKey = !isset($args) || empty($args) ? null : $args[0];

        import('classes.manager.form.EmailTemplateForm');

        $emailTemplateForm = new EmailTemplateForm($emailKey, $journal);
        $emailTemplateForm->initData();
        $emailTemplateForm->display();
    }

    /**
     * Save changes to an email.
     * @param array $args
     * @param PKPRequest $request
     */
    public function updateEmail($args = [], $request = null) {
        $this->validate();
        $this->setupTemplate(true);
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();
        $journal = $request->getJournal();

        import('classes.manager.form.EmailTemplateForm');

        // [SECURITY FIX] Terapkan trim() untuk sanitasi string
        $emailKey = trim((string) $request->getUserVar('emailKey'));

        $emailTemplateForm = new EmailTemplateForm($emailKey, $journal);
        $emailTemplateForm->readInputData();

        if ($emailTemplateForm->validate()) {
            $emailTemplateForm->execute();
            $request->redirect(null, null, 'emails');

        } else {
            $emailTemplateForm->display();
        }
    }

    /**
     * Delete a custom email.
     * @param array $args first parameter is the key of the email to delete
     * @param PKPRequest $request
     */
    public function deleteCustomEmail($args, $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        
        $emailKey = array_shift($args);

        $emailTemplateDao = DAORegistry::getDAO('EmailTemplateDAO');
        if ($emailTemplateDao->customTemplateExistsByKey($emailKey, $journal->getId())) {
            $emailTemplateDao->deleteEmailTemplateByKey($emailKey, $journal->getId());
        }

        $request->redirect(null, null, 'emails');
    }

    /**
     * Reset an email to default.
     * @param array $args first parameter is the key of the email to reset
     * @param PKPRequest $request
     */
    public function resetEmail($args, $request = null) {
        $this->validate();

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        if (isset($args) && !empty($args)) {
            $journal = $request->getJournal();

            $emailTemplateDao = DAORegistry::getDAO('EmailTemplateDAO');
            $emailTemplateDao->deleteEmailTemplateByKey($args[0], $journal->getId());
        }

        $request->redirect(null, null, 'emails');
    }

    /**
     * resets all email templates associated with the journal.
     * @param array $args
     * @param PKPRequest $request
     */
    public function resetAllEmails($args = [], $request = null) {
        $this->validate();

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journal = $request->getJournal();
        $emailTemplateDao = DAORegistry::getDAO('EmailTemplateDAO');
        $emailTemplateDao->deleteEmailTemplatesByJournal($journal->getId());

        $request->redirect(null, null, 'emails');
    }

    /**
     * disables an email template.
     * @param array $args first parameter is the key of the email to disable
     * @param PKPRequest $request
     */
    public function disableEmail($args, $request = null) {
        $this->validate();

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        if (isset($args) && !empty($args)) {
            $journal = $request->getJournal();

            $emailTemplateDao = DAORegistry::getDAO('EmailTemplateDAO');
            $emailTemplate = $emailTemplateDao->getBaseEmailTemplate($args[0], $journal->getId());

            if (isset($emailTemplate)) {
                if ($emailTemplate->getCanDisable()) {
                    $emailTemplate->setEnabled(0);

                    if ($emailTemplate->getAssocId() == null) {
                        $emailTemplate->setAssocId($journal->getId());
                        $emailTemplate->setAssocType(ASSOC_TYPE_JOURNAL);
                    }

                    if ($emailTemplate->getEmailId() != null) {
                        $emailTemplateDao->updateBaseEmailTemplate($emailTemplate);
                    } else {
                        $emailTemplateDao->insertBaseEmailTemplate($emailTemplate);
                    }
                }
            }
        }

        $request->redirect(null, null, 'emails');
    }

    /**
     * enables an email template.
     * @param array $args first parameter is the key of the email to enable
     * @param PKPRequest $request
     */
    public function enableEmail($args, $request = null) {
        $this->validate();

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        if (isset($args) && !empty($args)) {
            $journal = $request->getJournal();

            $emailTemplateDao = DAORegistry::getDAO('EmailTemplateDAO');
            $emailTemplate = $emailTemplateDao->getBaseEmailTemplate($args[0], $journal->getId());

            if (isset($emailTemplate)) {
                if ($emailTemplate->getCanDisable()) {
                    $emailTemplate->setEnabled(1);

                    if ($emailTemplate->getEmailId() != null) {
                        $emailTemplateDao->updateBaseEmailTemplate($emailTemplate);
                    } else {
                        $emailTemplateDao->insertBaseEmailTemplate($emailTemplate);
                    }
                }
            }
        }

        $request->redirect(null, null, 'emails');
    }
    
    /**
     * Export the selected email templates as XML
     * @param array $args
     * @param PKPRequest $request
     */
    public function exportEmails($args, $request) {
        $this->validate();
        import('lib.pkp.classes.xml.XMLCustomWriter');
        
        // [SECURITY FIX] Gunakan array_map untuk memaksa semua elemen menjadi integer
        $selectedEmailKeys = (array) $request->getUserVar('tplId');
        // Fix for intval mapping on strings (email keys are strings)
        // Only cast to string and trim to be safe, keys are not ints here
        // $selectedEmailKeys = array_map('intval', $selectedEmailKeys); 
        
        if (empty($selectedEmailKeys)) {
            $request->redirect(null, null, 'emails');
        }
        
        $journal = $request->getJournal();
        $doc = XMLCustomWriter::createDocument();
        $emailTexts = XMLCustomWriter::createElement($doc, 'email_texts');
        $emailTexts->setAttribute('locale', AppLocale::getLocale());
        $emailTexts->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        
        $emailTemplateDao = DAORegistry::getDAO('EmailTemplateDAO');
        $emailTemplates = $emailTemplateDao->getEmailTemplates(AppLocale::getLocale(), $journal->getId());
        
        foreach($emailTemplates as $emailTemplate) {
            $emailKey = $emailTemplate->getData('emailKey');
            if (!in_array($emailKey, $selectedEmailKeys)) continue;
            
            $subject = $emailTemplate->getData('subject');
            $body = $emailTemplate->getData('body');
            
            $emailTextNode = XMLCustomWriter::createElement($doc, 'email_text');
            XMLCustomWriter::setAttribute($emailTextNode, 'key', $emailKey);
            
            //append subject node
            $subjectNode = XMLCustomWriter::createChildWithText($doc, $emailTextNode, 'subject', $subject, false);
            XMLCustomWriter::appendChild($emailTextNode, $subjectNode);
            
            //append body node
            $bodyNode = XMLCustomWriter::createChildWithText($doc, $emailTextNode, 'body', $body, false);
            XMLCustomWriter::appendChild($emailTextNode, $bodyNode);
            
            //append email_text node
            XMLCustomWriter::appendChild($emailTexts, $emailTextNode);
        }
        
        XMLCustomWriter::appendChild($doc, $emailTexts);
        
        header("Content-Type: application/xml");
        header("Cache-Control: private");
        header("Content-Disposition: attachment; filename=\"email-templates-" . date('Y-m-d-H-i-s') . ".xml\"");
        
        XMLCustomWriter::printXML($doc);
    }
    
    /**
     * Upload a custom email template file
     * @param array $args
     * @param PKPRequest $request
     */
    public function uploadEmails($args, $request) {
        $this->validate();
        import('lib.pkp.classes.file.FileManager');
        $fileManager = new FileManager();

        $journal = $request->getJournal();
        $journalId = $journal->getId();
        
        $uploadName = 'email_file';
        $fileName = $fileManager->getUploadedFileName($uploadName);
        if (!$fileName) {
            $request->redirect(null, null, 'emails');
        }
        
        $filesDir = Config::getVar('files', 'files_dir');
        $filePath = $filesDir . '/journals/' . $journalId . '/' . $fileName;
        
        if (!$fileManager->uploadError($uploadName)) {
            if ($fileManager->uploadedFileExists($uploadName)) {
                $uploadedFilePath = $fileManager->getUploadedFilePath($uploadName);
                if ($this->_saveEmailTemplates($uploadedFilePath, $journal)) {
                    if ($fileManager->deleteFile($uploadedFilePath)) {
                        $this->_showMessage($request, true);
                        $request->redirect(null, null, 'emails');
                    }
                }
            }
        }
        
        $this->_showMessage($request, false);
        $request->redirect(null, null, 'emails');
    }
    
    /**
     * Save a custom email template file
     * @param string $filePath
     * @param Journal $journal
     * @return bool
     */
    protected function _saveEmailTemplates($filePath, $journal) {
        $this->validate();
        import('lib.pkp.classes.xml.XMLParser');
        $emailTemplateDao = DAORegistry::getDAO('EmailTemplateDAO');
        
        $xmlParser = new XMLParser();
        
        $struct = $xmlParser->parseStruct($filePath);
        if (!isset($struct['email_texts'][0]['attributes']['locale'])) {
            return false;
        }
        $locale = $struct['email_texts'][0]['attributes']['locale'];

        // [WIZDAM] Safe array access with coalescence
        $emailTexts = $struct['email_text'] ?? [];
        $subjects = $struct['subject'] ?? [];
        $bodies = $struct['body'] ?? [];
        
        // check if the parsed xml has the correct structure
        if (empty($emailTexts) || empty($subjects) || empty($bodies)) return false;

        $nodeSizes = [count($emailTexts), count($subjects), count($bodies)];
        if (count(array_unique($nodeSizes)) > 1) return false;

        $journalId = $journal->getId();
        $supportedLocales = $journal->getSupportedLocaleNames();

        foreach($emailTexts as $index => $emailText) {
            $emailKey = $emailText['attributes']['key'];
            $subject = $subjects[$index]['value'];
            $body = $bodies[$index]['value'];

            $emailTemplate = $emailTemplateDao->getLocaleEmailTemplate($emailKey, $journalId);
            
            // [WIZDAM] Check if template exists, if not create new dummy to prevent crash
            if (!$emailTemplate) {
                 continue; // or create new LocaleEmailTemplate()
            }
            
            $emailTemplateLocaleData = $emailTemplate->localeData;
            
            // just update supported locales
            if (is_array($emailTemplateLocaleData)) {
                foreach($emailTemplateLocaleData as $emailTemplateLocale => $data) {
                    if (!isset($supportedLocales[$emailTemplateLocale])) {
                        unset($emailTemplateLocaleData[$emailTemplateLocale]);
                    }
                }
                $emailTemplate->localeData = $emailTemplateLocaleData; 
            }
            
            $emailTemplate->setAssocType(ASSOC_TYPE_JOURNAL);
            $emailTemplate->setAssocId($journalId);
            
            if ($emailTemplate->getCanDisable()) {
                $emailTemplate->setEnabled($emailTemplate->getData('enabled'));
            }
            
            $emailTemplate->setSubject($locale, $subject);
            $emailTemplate->setBody($locale, $body);

            if ($emailTemplate->getEmailId() != null) {
                $emailTemplateDao->updateLocaleEmailTemplate($emailTemplate);
            } else {
                $emailTemplateDao->insertLocaleEmailTemplate($emailTemplate);
            }
        }
        return true;
    }
    
    /**
     * Show success or error message
     * @param PKPRequest $request
     * @param bool $success
     */
    protected function _showMessage($request, $success = true) {
        $this->validate();
        import('classes.notification.NotificationManager');
        $notificationManager = new NotificationManager();

        AppLocale::requireComponents(LOCALE_COMPONENT_CORE_MANAGER);
        
        if ($success == true) {
            $notificationType = NOTIFICATION_TYPE_SUCCESS;
            $message = 'manager.emails.uploadSuccess';
        } else {
            $notificationType = NOTIFICATION_TYPE_ERROR;
            $message = 'manager.emails.uploadError';
        }
        
        $user = $request->getUser();
        $notificationManager->createTrivialNotification(
            $user->getId(),
            $notificationType,
            ['contents' => __($message)]
        );
    }
}
?>