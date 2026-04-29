<?php
declare(strict_types=1);

/**
 * @file classes/mail/PKPMailTemplate.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPMailTemplate
 * @ingroup mail
 *
 * @brief Subclass of Mail for mailing a template email.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.mail.Mail');

define('MAIL_ERROR_INVALID_EMAIL', 0x000001);

class PKPMailTemplate extends Mail {

    /** @var string|null Key of the email template we are using */
    public $emailKey = null;

    /** @var string|null locale of this template */
    public $locale = null;

    /** @var bool email template is enabled */
    public $enabled = true;

    /** @var array List of errors to display to the user */
    public $errorMessages = [];

    /** @var array List of temporary files belonging to email */
    public $persistAttachments = [];
    
    /** @var bool */
    public $attachmentsEnabled = false;

    /** @var bool If set to true, this message has been skipped */
    public $skip = false;

    /** @var bool whether or not to bcc the sender */
    public $bccSender = false;

    /** @var bool Whether or not email fields are disabled */
    public $addressFieldsEnabled = true;

    /**
     * Constructor.
     * @param string|null $emailKey unique identifier for the template
     * @param string|null $locale locale of the template
     * @param bool|null $enableAttachments optional Whether or not to enable article attachments
     * @param bool $includeSignature optional
     */
    public function __construct($emailKey = null, $locale = null, $enableAttachments = null, $includeSignature = true) {
        parent::__construct();
        
        $this->emailKey = $emailKey;
        $this->locale = $locale ?? AppLocale::getLocale();

        // [WIZDAM] Request Singleton Priority
        $request = Application::get()->getRequest();

        $this->bccSender = (bool) $request->getUserVar('bccSender');

        if ($enableAttachments === null) {
            $enableAttachments = Config::getVar('email', 'enable_attachments') ? true : false;
        }

        $user = $request->getUser();
        if ($enableAttachments && $user) {
            $this->_handleAttachments((int) $user->getId());
        } else {
            $this->attachmentsEnabled = false;
        }

        $this->addressFieldsEnabled = true;
    }
    
    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPMailTemplate() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::PublishedArticle(). Please refactor to parent::__construct().", 
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Disable or enable the address fields on the email form.
     * @param bool $addressFieldsEnabled
     */
    public function setAddressFieldsEnabled($addressFieldsEnabled) {
        $this->addressFieldsEnabled = (bool) $addressFieldsEnabled;
    }

    /**
     * Get the enabled/disabled state of address fields on the email form.
     * @return bool
     */
    public function getAddressFieldsEnabled() {
        return $this->addressFieldsEnabled;
    }

    /**
     * Check whether or not there were errors in the user input for this form.
     * @return bool true iff one or more error messages are stored.
     */
    public function hasErrors() {
        return !empty($this->errorMessages);
    }

    /**
     * Assigns values to e-mail parameters.
     * @param array $paramArray
     * @return void
     */
    public function assignParams($paramArray = []) {
        $subject = $this->getSubject();
        $body = $this->getBody();

        // Replace variables in message with values
        foreach ($paramArray as $key => $value) {
            if (!is_object($value) && !is_array($value)) {
                $subject = str_replace('{$' . $key . '}', (string)$value, $subject);
                $body = str_replace('{$' . $key . '}', (string)$value, $body);
            }
        }

        $this->setSubject($subject);
        $this->setBody($body);
    }

    /**
     * Returns true if the email template is enabled; false otherwise.
     * @return bool
     */
    public function isEnabled() {
        return $this->enabled;
    }

    /**
     * Processes form-submitted addresses for inclusion in
     * the recipient list
     * @param array $currentList Current recipient/cc/bcc list
     * @param array $newAddresses "Raw" form parameter for additional addresses
     * @return array
     */
    public function processAddresses($currentList, $newAddresses) {
        // Safety check if inputs are not arrays
        if (!is_array($currentList)) $currentList = [];
        if (!is_array($newAddresses)) return $currentList;

        foreach ($newAddresses as $newAddress) {
            $regs = [];
            // Match the form "My Name <my_email@my.domain.com>"
            if (PKPString::regexp_match_get('/^([^<>' . "\n" . ']*[^<> ' . "\n" . '])[ ]*<(?P<email>' . PCRE_EMAIL_ADDRESS . ')>$/i', $newAddress, $regs)) {
                $currentList[] = ['name' => $regs[1], 'email' => $regs['email']];

            } elseif (PKPString::regexp_match_get('/^<?(?P<email>' . PCRE_EMAIL_ADDRESS . ')>?$/i', $newAddress, $regs)) {
                $currentList[] = ['name' => '', 'email' => $regs['email']];

            } elseif ($newAddress != '') {
                $this->errorMessages[] = ['type' => MAIL_ERROR_INVALID_EMAIL, 'address' => $newAddress];
            }
        }
        return $currentList;
    }

    /**
     * Displays an edit form to customize the email.
     * @param string $formActionUrl
     * @param array|null $hiddenFormParams
     * @param string|null $alternateTemplate
     * @param array $additionalParameters
     * @return void
     */
    public function displayEditForm($formActionUrl, $hiddenFormParams = null, $alternateTemplate = null, $additionalParameters = []) {
        import('lib.pkp.classes.form.Form');
        $form = new Form($alternateTemplate != null ? $alternateTemplate : 'email/email.tpl');
        
        // [WIZDAM] Request Singleton
        $request = Application::get()->getRequest();
        $user = $request->getUser();

        $form->setData('formActionUrl', $formActionUrl);
        $form->setData('subject', $this->getSubject());
        $form->setData('body', $this->getBody());

        $form->setData('to', $this->getRecipients());
        $form->setData('cc', $this->getCcs());
        $form->setData('bcc', $this->getBccs());
        $form->setData('blankTo', $request->getUserVar('blankTo'));
        $form->setData('blankCc', $request->getUserVar('blankCc'));
        $form->setData('blankBcc', $request->getUserVar('blankBcc'));
        $form->setData('from', $this->getFromString(false));

        $form->setData('addressFieldsEnabled', $this->getAddressFieldsEnabled());

        if ($user) {
            $form->setData('senderEmail', $user->getEmail());
            $form->setData('bccSender', $this->bccSender);
        }

        if ($this->attachmentsEnabled) {
            $form->setData('attachmentsEnabled', true);
            $form->setData('persistAttachments', $this->persistAttachments);
        }

        $form->setData('errorMessages', $this->errorMessages);

        if ($hiddenFormParams != null) {
            $form->setData('hiddenFormParams', $hiddenFormParams);
        }

        foreach ($additionalParameters as $key => $value) {
            $form->setData($key, $value);
        }

        $form->display();
    }

    /**
     * Send the email.
     * Aside from calling the parent method, this actually attaches
     * the persistent attachments if they are used.
     * @param bool $clearAttachments Whether to delete attachments after
     * @return bool
     */
    public function send($clearAttachments = true) {
        if ($this->attachmentsEnabled && !empty($this->persistAttachments)) {
            foreach ($this->persistAttachments as $persistentAttachment) {
                $this->addAttachment(
                    $persistentAttachment->getFilePath(),
                    $persistentAttachment->getOriginalFileName(),
                    $persistentAttachment->getFileType()
                );
            }
        }

        // [WIZDAM] Request Singleton
        $request = Application::get()->getRequest();
        $user = $request->getUser();

        if ($user && $this->bccSender) {
            $this->addBcc($user->getEmail(), $user->getFullName());
        }

        if (isset($this->skip) && $this->skip) {
            $result = true;
        } else {
            // [WIZDAM] Explicit parent call
            $result = parent::send();
        }

        if ($clearAttachments && $this->attachmentsEnabled && $user) {
            $this->_clearAttachments((int) $user->getId());
        }

        return $result;
    }

    /**
     * Assigns user-specific values to email parameters, sends
     * the email, then clears those values.
     * @param array $paramArray
     * @return bool
     */
    public function sendWithParams($paramArray) {
        $savedHeaders = $this->getHeaders();
        $savedSubject = $this->getSubject();
        $savedBody = $this->getBody();

        $this->assignParams($paramArray);

        $ret = $this->send();

        $this->setHeaders($savedHeaders);
        $this->setSubject($savedSubject);
        $this->setBody($savedBody);

        return $ret;
    }

    /**
     * Clears the recipient, cc, and bcc lists.
     * @param bool $clearHeaders if true, also clear headers
     * @return void
     */
    public function clearRecipients($clearHeaders = true) {
        $this->setData('recipients', null);
        $this->setData('ccs', null);
        $this->setData('bccs', null);
        if ($clearHeaders) {
            $this->setData('headers', null);
        }
    }

    /**
     * Adds a persistent attachment to the current list.
     * Persistent attachments MUST be previously initialized
     * with handleAttachments.
     * @param object $temporaryFile
     */
    public function addPersistAttachment($temporaryFile) {
        $this->persistAttachments[] = $temporaryFile;
    }

    /**
     * Handles attachments in a generalized manner in situations where
     * an email message must span several requests. Called from the
     * constructor when attachments are enabled.
     * @param int $userId
     */
    public function _handleAttachments($userId) {
        import('classes.file.TemporaryFileManager');
        $temporaryFileManager = new TemporaryFileManager();

        $this->attachmentsEnabled = true;
        $this->persistAttachments = [];

        // [WIZDAM] Request Singleton
        $request = Application::get()->getRequest();

        $deleteAttachment = $request->getUserVar('deleteAttachment');
        $persistAttachmentsVar = $request->getUserVar('persistAttachments');

        if ($persistAttachmentsVar != null && is_array($persistAttachmentsVar)) {
            foreach ($persistAttachmentsVar as $fileId) {
                $temporaryFile = $temporaryFileManager->getFile($fileId, $userId);
                if (!empty($temporaryFile)) {
                    if ($deleteAttachment != $temporaryFile->getId()) {
                        $this->persistAttachments[] = $temporaryFile;
                    } else {
                        // This file is being deleted.
                        $temporaryFileManager->deleteFile($temporaryFile->getId(), $userId);
                    }
                }
            }
        }

        if ($request->getUserVar('addAttachment') && $temporaryFileManager->uploadedFileExists('newAttachment')) {
            $user = $request->getUser();
            if ($user) {
                $this->persistAttachments[] = $temporaryFileManager->handleUpload('newAttachment', $user->getId());
            }
        }
    }

    /**
     * Get attachment files.
     * @return array
     */
    public function getAttachmentFiles() {
        if ($this->attachmentsEnabled) return $this->persistAttachments;
        return [];
    }

    /**
     * Delete all attachments associated with this message.
     * Called from send().
     * @param int $userId
     */
    public function _clearAttachments($userId) {
        import('classes.file.TemporaryFileManager');
        $temporaryFileManager = new TemporaryFileManager();

        // [WIZDAM] Request Singleton
        $request = Application::get()->getRequest();
        $persistAttachments = $request->getUserVar('persistAttachments');

        if (is_array($persistAttachments)) {
            foreach ($persistAttachments as $fileId) {
                $temporaryFile = $temporaryFileManager->getFile($fileId, $userId);
                if (!empty($temporaryFile)) {
                    $temporaryFileManager->deleteFile($temporaryFile->getId(), $userId);
                }
            }
        }
    }
}
?>