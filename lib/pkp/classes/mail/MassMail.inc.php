<?php
declare(strict_types=1);

/**
 * @file classes/mail/MassMail.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MassMail
 * @ingroup mail
 * @brief Helper class to send mass emails
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.mail.MailTemplate');

class MassMail extends MailTemplate {
    /** @var callable|null */
    public $callback = null;

    /** @var int Frequency of callback execution */
    public $frequency = 10;

    /**
     * Constructor
     * @param string|null $emailKey
     * @param string|null $locale
     * @param bool|null $enableAttachments
     */
    public function __construct($emailKey = null, $locale = null, $enableAttachments = null) {
        // [WIZDAM FIX] Gunakan parent::__construct() secara eksplisit
        parent::__construct($emailKey, $locale, $enableAttachments);
        
        $this->callback = null;
        $this->frequency = 10;
    }

    /**
     * [SHIM] Backward Compatibility
     * Standar Protokol #6
     */
    public function MassMail() {
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
     * Set the callback function (see PHP's callback pseudotype); this
     * function will be called for every n emails sent, according to the
     * frequency.
     * @param callable $callback
     */
    public function setCallback($callback) {
        $this->callback = $callback;
    }

    /**
     * Set the frequency at which the callback will be called (i.e. each
     * n emails).
     * @param int $frequency
     */
    public function setFrequency($frequency) {
        $this->frequency = (int) $frequency;
    }

    /**
     * Send the email.
     * @param bool $clearAttachments
     * @return bool
     */
    public function send($clearAttachments = true) {
        @set_time_limit(0);

        $realRecipients = $this->getRecipients();
        $realSubject = $this->getSubject();
        $realBody = $this->getBody();

        // Safety check for recipients array
        if (!is_array($realRecipients)) {
            $realRecipients = [];
        }

        $index = 0;
        $success = true;
        $max = count($realRecipients);
        
        foreach ($realRecipients as $recipient) {
            $this->clearAllRecipients();

            // Pastikan data recipient valid sebelum addRecipient
            $email = isset($recipient['email']) ? (string)$recipient['email'] : '';
            $name = isset($recipient['name']) ? (string)$recipient['name'] : '';

            if ($email !== '') {
                $this->addRecipient($email, $name);
                $this->setSubject($realSubject);
                $this->setBody($realBody);

                // [WIZDAM FIX] Changed static call MailTemplate::send() to parent::send()
                // Memastikan return boolean (casting)
                $result = parent::send(false);
                $success = $success && (bool)$result;
                
                $index++;
                
                // Callback execution check
                if ($this->callback && is_callable($this->callback) && ($index % $this->frequency) == 0) {
                    call_user_func($this->callback, $index, $max);
                }
            }
        }
        
        $this->setRecipients($realRecipients);
        return $success;
    }
}
?>