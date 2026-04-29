<?php
declare(strict_types=1);

/**
 * @file classes/manager/form/EmailTemplateForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EmailTemplateForm
 * @ingroup manager_form
 * @see EmailTemplateDAO
 *
 * @brief Form for creating and modifying email templates.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.form.Form');

class EmailTemplateForm extends Form {

    /** @var string|null The key of the email template being edited */
    public $emailKey = null;

    /** @var Journal|null The journal of the email template being edited */
    public $journal = null;

    /**
     * Constructor.
     * @param string $emailKey
     * @param Journal $journal
     */
    public function __construct($emailKey, $journal) {
        // [WIZDAM FIX] Explicit parent constructor
        parent::__construct('manager/emails/emailTemplateForm.tpl');

        $this->journal = $journal;
        $this->emailKey = $emailKey;

        // Validation checks for this form
        $this->addCheck(new FormValidatorArray($this, 'subject', 'required', 'manager.emails.form.subjectRequired'));
        $this->addCheck(new FormValidatorArray($this, 'body', 'required', 'manager.emails.form.bodyRequired'));
        $this->addCheck(new FormValidator($this, 'emailKey', 'required', 'manager.emails.form.emailKeyRequired'));
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function EmailTemplateForm($emailKey, $journal) {
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
     * Display the form.
     * @param mixed $request
     * @param mixed $template
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();
        $journal = Application::get()->getRequest()->getJournal();
        
        $emailTemplateDao = DAORegistry::getDAO('EmailTemplateDAO');
        $emailTemplate = $emailTemplateDao->getBaseEmailTemplate($this->emailKey, $journal->getId());
        
        $templateMgr->assign('canDisable', $emailTemplate ? $emailTemplate->getCanDisable() : false);
        $templateMgr->assign('supportedLocales', $journal->getSupportedLocaleNames());
        $templateMgr->assign('helpTopicId', 'journal.managementPages.emails');
        
        parent::display($request, $template);
    }

    /**
     * Initialize form data from current settings.
     */
    public function initData() {
        $journal = Application::get()->getRequest()->getJournal();
        $emailTemplateDao = DAORegistry::getDAO('EmailTemplateDAO');
        $emailTemplate = $emailTemplateDao->getLocaleEmailTemplate($this->emailKey, $journal->getId());
        $thisLocale = AppLocale::getLocale();

        if ($emailTemplate) {
            $subject = [];
            $body = [];
            $description = [];
            foreach ($emailTemplate->getLocales() as $locale) {
                $subject[$locale] = $emailTemplate->getSubject($locale);
                $body[$locale] = $emailTemplate->getBody($locale);
                $description[$locale] = $emailTemplate->getDescription($locale);
            }

            $this->_data = [
                'emailId' => $emailTemplate->getEmailId(),
                'emailKey' => $emailTemplate->getEmailKey(),
                'subject' => $subject,
                'body' => $body,
                'description' => isset($description[$thisLocale]) ? $description[$thisLocale] : null,
                'enabled' => $emailTemplate->getEnabled()
            ];
        } else {
            $this->_data = ['isNewTemplate' => true];
        }
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(['emailId', 'subject', 'body', 'enabled', 'journalId', 'emailKey']);

        $journalId = $this->journal->getId(); // [WIZDAM] Correct getter for journal ID
        $emailTemplateDao = DAORegistry::getDAO('EmailTemplateDAO');
        $emailTemplate = $emailTemplateDao->getLocaleEmailTemplate($this->emailKey, $journalId);
        
        if (!$emailTemplate) {
            $this->_data['isNewTemplate'] = true;
        }
    }

    /**
     * Save email template.
     * @param mixed $object
     */
    public function execute($object = null) {
        $journal = Application::get()->getRequest()->getJournal();

        $emailTemplateDao = DAORegistry::getDAO('EmailTemplateDAO');
        $emailTemplate = $emailTemplateDao->getLocaleEmailTemplate($this->emailKey, $journal->getId());

        if (!$emailTemplate) {
            $emailTemplate = new LocaleEmailTemplate();
            $emailTemplate->setCustomTemplate(true);
            $emailTemplate->setCanDisable(false);
            $emailTemplate->setEnabled(true);
            $emailTemplate->setEmailKey($this->getData('emailKey'));
        } else {
            $emailTemplate->setEmailId($this->getData('emailId'));
            if ($emailTemplate->getCanDisable()) {
                $emailTemplate->setEnabled($this->getData('enabled'));
            }
        }

        $emailTemplate->setAssocType(ASSOC_TYPE_JOURNAL);
        $emailTemplate->setAssocId($journal->getId());

        $supportedLocales = $journal->getSupportedLocaleNames();
        if (!empty($supportedLocales)) {
            foreach ($journal->getSupportedLocaleNames() as $localeKey => $localeName) {
                $emailTemplate->setSubject($localeKey, $this->_data['subject'][$localeKey]);
                $emailTemplate->setBody($localeKey, $this->_data['body'][$localeKey]);
            }
        } else {
            $localeKey = AppLocale::getLocale();
            $emailTemplate->setSubject($localeKey, $this->_data['subject'][$localeKey]);
            $emailTemplate->setBody($localeKey, $this->_data['body'][$localeKey]);
        }

        if ($emailTemplate->getEmailId() != null) {
            $emailTemplateDao->updateLocaleEmailTemplate($emailTemplate);
        } else {
            $emailTemplateDao->insertLocaleEmailTemplate($emailTemplate);
        }
    }
}
?>