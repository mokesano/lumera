<?php
declare(strict_types=1);

/**
 * @file classes/log/EmailLogEntry.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EmailLogEntry
 * @ingroup log
 * @see EmailLogDAO
 *
 * @brief Describes an entry in the email log.
 */

class EmailLogEntry extends DataObject {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function EmailLogEntry() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    //
    // Get/set methods
    //

    /**
     * Get user ID of sender.
     * @return int
     */
    public function getSenderId() {
        return (int) $this->getData('senderId');
    }

    /**
     * Set user ID of sender.
     * @param int $senderId
     */
    public function setSenderId($senderId) {
        $this->setData('senderId', $senderId);
    }

    /**
     * Get date email was sent.
     * @return string|null
     */
    public function getDateSent() {
        return $this->getData('dateSent');
    }

    /**
     * Set date email was sent.
     * @param string $dateSent
     */
    public function setDateSent($dateSent) {
        $this->setData('dateSent', $dateSent);
    }

    /**
     * Get IP address of sender.
     * @return string
     */
    public function getIPAddress() {
        return (string) $this->getData('ipAddress');
    }

    /**
     * Set IP address of sender.
     * @param string $ipAddress
     */
    public function setIPAddress($ipAddress) {
        $this->setData('ipAddress', $ipAddress);
    }

    /**
     * Get event type.
     * @return int
     */
    public function getEventType() {
        return (int) $this->getData('eventType');
    }

    /**
     * Set event type.
     * @param int $eventType
     */
    public function setEventType($eventType) {
        $this->setData('eventType', $eventType);
    }

    /**
     * Get associated type.
     * @return int
     */
    public function getAssocType() {
        return (int) $this->getData('assocType');
    }

    /**
     * Set associated type.
     * @param int $assocType
     */
    public function setAssocType($assocType) {
        $this->setData('assocType', $assocType);
    }

    /**
     * Get associated ID.
     * @return int
     */
    public function getAssocId() {
        return (int) $this->getData('assocId');
    }

    /**
     * Set associated ID.
     * @param int $assocId
     */
    public function setAssocId($assocId) {
        $this->setData('assocId', $assocId);
    }

    /**
     * Return the full name of the sender (not necessarily the same as the from address).
     * @return string
     */
    public function getSenderFullName() {
        $senderFullName = $this->getData('senderFullName');

        if(!isset($senderFullName)) {
            $userDao = DAORegistry::getDAO('UserDAO'); /* @var $userDao UserDAO */
            $senderFullName = $userDao->getUserFullName($this->getSenderId(), true);
        }

        return ($senderFullName ? $senderFullName : '');
    }

    /**
     * Return the email address of sender.
     * @return string
     */
    public function getSenderEmail() {
        $senderEmail = $this->getData('senderEmail');

        if(!isset($senderEmail)) {
            $userDao = DAORegistry::getDAO('UserDAO'); /* @var $userDao UserDAO */
            $senderEmail = $userDao->getUserEmail($this->getSenderId(), true);
        }

        return ($senderEmail ? $senderEmail : '');
    }


    //
    // Email data
    //

    /**
     * @return string
     */
    public function getFrom() {
        return (string) $this->getData('from');
    }

    /**
     * @param string $from
     */
    public function setFrom($from) {
        $this->setData('from', $from);
    }

    /**
     * @return string
     */
    public function getRecipients() {
        return (string) $this->getData('recipients');
    }

    /**
     * @param string $recipients
     */
    public function setRecipients($recipients) {
        $this->setData('recipients', $recipients);
    }

    /**
     * @return string
     */
    public function getCcs() {
        return (string) $this->getData('ccs');
    }

    /**
     * @param string $ccs
     */
    public function setCcs($ccs) {
        $this->setData('ccs', $ccs);
    }

    /**
     * @return string
     */
    public function getBccs() {
        return (string) $this->getData('bccs');
    }

    /**
     * @param string $bccs
     */
    public function setBccs($bccs) {
        $this->setData('bccs', $bccs);
    }

    /**
     * @return string
     */
    public function getSubject() {
        return (string) $this->getData('subject');
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject) {
        $this->setData('subject', $subject);
    }

    /**
     * @return string
     */
    public function getBody() {
        return (string) $this->getData('body');
    }

    /**
     * @param string $body
     */
    public function setBody($body) {
        $this->setData('body', $body);
    }
}
?>