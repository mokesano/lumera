<?php
declare(strict_types=1);

/**
 * @file classes/mail/MailTemplate.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MailTemplate
 * @ingroup mail
 *
 * @brief Subclass of PKPMailTemplate for mailing a template email.
 *
 * [WIZDAM EDITION] Refactored for PHP 7.4 and ready for 8.1+ Strict Compliance
 * [FIXED] str_contains replaced with strpos for universal compatibility
 */

import('lib.pkp.classes.mail.PKPMailTemplate');

class MailTemplate extends PKPMailTemplate {
    /** @var object|null The journal this message relates to */
    public $journal = null;

    /**
     * Constructor.
     * @param string|null $emailKey unique identifier for the template
     * @param string|null $locale locale of the template
     * @param bool|null $enableAttachments optional Whether or not to enable article attachments in the template
     * @param object|null $journal optional The journal this message relates to
     * @param bool $includeSignature optional
     * @param bool $ignorePostedData optional
     */
    public function __construct($emailKey = null, $locale = null, $enableAttachments = null, $journal = null, $includeSignature = true, $ignorePostedData = false) {
        // [WIZDAM FIX] Explicit parent constructor call to prevent Legacy SHIM loops
        parent::__construct($emailKey, $locale, $enableAttachments, $includeSignature);

        // [WIZDAM] Request Singleton
        $request = Application::get()->getRequest();

        // If a journal wasn't specified, use the current request.
        if ($journal === null) {
            $journal = $request->getJournal();
        }

        // Initialize Property
        $this->journal = $journal;

        if (isset($this->emailKey)) {
            $emailTemplateDao = DAORegistry::getDAO('EmailTemplateDAO');
            // [WIZDAM] Strict ID checking
            $journalId = ($journal instanceof Journal) ? (int) $journal->getId() : 0;
            
            // ASSOC_TYPE_JOURNAL typically equals 0x0000100 (256 in decimal)
            $emailTemplate = $emailTemplateDao->getEmailTemplate(
                (string) $this->emailKey, 
                (string) $this->locale, 
                ASSOC_TYPE_JOURNAL, 
                $journalId
            );
        }

        $userSig = '';
        $user = $request->getUser();
        if ($user && $includeSignature) {
            $userSig = $user->getLocalizedSignature();
            if (!empty($userSig)) $userSig = "\n" . $userSig;
        }

        if (isset($emailTemplate) && ($ignorePostedData || ($request->getUserVar('subject') == null && $request->getUserVar('body') == null))) {
            $this->setSubject($emailTemplate->getSubject());
            $this->setBody($emailTemplate->getBody() . $userSig);
            $this->enabled = $emailTemplate->getEnabled();

            if ($request->getUserVar('usePostedAddresses')) {
                $to = $request->getUserVar('to');
                if (is_array($to)) {
                    $this->setRecipients($this->processAddresses($this->getRecipients(), $to));
                }
                $cc = $request->getUserVar('cc');
                if (is_array($cc)) {
                    $this->setCcs($this->processAddresses($this->getCcs(), $cc));
                }
                $bcc = $request->getUserVar('bcc');
                if (is_array($bcc)) {
                    $this->setBccs($this->processAddresses($this->getBccs(), $bcc));
                }
            }
        } else {
            $this->setSubject($request->getUserVar('subject'));
            $body = $request->getUserVar('body');
            if (empty($body)) $this->setBody($userSig);
            else $this->setBody($body);
            
            $tmp = $request->getUserVar('send');
            $this->skip = ($tmp && is_array($tmp) && isset($tmp['skip']));
            $this->enabled = true;

            $toEmails = $request->getUserVar('to');
            if (is_array($toEmails)) {
                $this->setRecipients($this->processAddresses($this->getRecipients(), $toEmails));
            }
            
            $ccEmails = $request->getUserVar('cc');
            if (is_array($ccEmails)) {
                $this->setCcs($this->processAddresses($this->getCcs(), $ccEmails));
            }
            
            $bccEmails = $request->getUserVar('bcc');
            if (is_array($bccEmails)) {
                $this->setBccs($this->processAddresses($this->getBccs(), $bccEmails));
            }
        }

        // Default "From" to user if available, otherwise site/journal principal contact
        if ($user) {
            $this->setFrom($user->getEmail(), $user->getFullName());
        } elseif (is_null($this->journal) || is_null($this->journal->getSetting('contactEmail'))) {
            $site = $request->getSite();
            $this->setFrom($site->getLocalizedContactEmail(), $site->getLocalizedContactName());
        } else {
            $this->setFrom($this->journal->getSetting('contactEmail'), $this->journal->getSetting('contactName'));
        }

        if ($this->journal && !$request->getUserVar('continued')) {
            $initials = $this->journal->getLocalizedSetting('initials');
            // Ensure subject isn't null before concatenating
            $currentSubject = $this->getSubject() ?? ''; 
            $this->setSubject('[' . $initials . '] ' . $currentSubject);
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function MailTemplate() {
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
     * Assigns values to e-mail parameters.
     * @param array $paramArray
     * @return void
     */
    public function assignParams($paramArray = []) {
        // [WIZDAM] Request Singleton
        $request = Application::get()->getRequest();

        // Add commonly-used variables to the list
        if (isset($this->journal) && $this->journal instanceof Journal) {
            // FIXME Include affiliation, title, etc. in signature?
            $paramArray['journalName'] = $this->journal->getLocalizedTitle();
            $paramArray['principalContactSignature'] = $this->journal->getSetting('contactName');
        } else {
            $site = $request->getSite();
            $paramArray['principalContactSignature'] = $site->getLocalizedContactName();
        }
        
        if (!isset($paramArray['journalUrl'])) {
            $paramArray['journalUrl'] = $request->url($request->getRequestedJournalPath());
        }

        parent::assignParams($paramArray);
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
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('helpTopicId', 'journal.managementPages.emails');

        parent::displayEditForm($formActionUrl, $hiddenFormParams, $alternateTemplate, $additionalParameters);
    }

    /**
     * Send the email.
     * Aside from calling the parent method, this actually attaches
     * the persistent attachments if they are used.
     * @param bool $clearAttachments Whether to delete attachments after
     * @return bool
     */
    public function send($clearAttachments = true) {
        if (isset($this->journal) && $this->journal instanceof Journal) {
            // If {$templateSignature} exists in the body of the
            // message, replace it with the journal signature;
            // otherwise just append it. This is here to
            // accomodate MIME-encoded messages or other cases
            // where the signature cannot just be appended.
            $searchString = '{$templateSignature}';
            
            // Casting to string ensures PHP 8.1+ doesn't complain if body is null
            $body = (string) $this->getBody();
            $signature = (string) $this->journal->getSetting('emailSignature'); // Casting to string for safety

            // [FIXED] Changed str_contains (PHP 8.0+) to strpos for PHP 7.x compatibility
            // strpos returns false if not found, or an integer (position) if found.
            // Using strict comparison (=== false) is mandatory here.
            if (strpos($body, $searchString) === false) {
                $this->setBody($body . "\n" . $signature);
            } else {
                $this->setBody(str_replace($searchString, $signature, $body));
            }

            $envelopeSender = $this->journal->getSetting('envelopeSender');
            if (!empty($envelopeSender) && Config::getVar('email', 'allow_envelope_sender')) {
                $this->setEnvelopeSender($envelopeSender);
            }
        }

        return parent::send($clearAttachments);
    }
}
?>