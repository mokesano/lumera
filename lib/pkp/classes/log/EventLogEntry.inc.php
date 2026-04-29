<?php
declare(strict_types=1);

/**
 * @file classes/log/EventLogEntry.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EventLogEntry
 * @ingroup log
 * @see EventLogDAO
 *
 * @brief Describes an entry in the event log.
 */

class EventLogEntry extends DataObject {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function EventLogEntry() {
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
     * Get user ID of user that initiated the event.
     * @return int
     */
    public function getUserId() {
        return (int) $this->getData('userId');
    }

    /**
     * Set user ID of user that initiated the event.
     * @param int $userId
     */
    public function setUserId($userId) {
        $this->setData('userId', $userId);
    }

    /**
     * Get date entry was logged.
     * @return string|null
     */
    public function getDateLogged() {
        return $this->getData('dateLogged');
    }

    /**
     * Set date entry was logged.
     * @param string $dateLogged
     */
    public function setDateLogged($dateLogged) {
        $this->setData('dateLogged', $dateLogged);
    }

    /**
     * Get IP address of user that initiated the event.
     * @return string
     */
    public function getIPAddress() {
        return (string) $this->getData('ipAddress');
    }

    /**
     * Set IP address of user that initiated the event.
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
     * Get custom log message (either locale key or literal string).
     * @return string
     */
    public function getMessage() {
        return (string) $this->getData('message');
    }

    /**
     * Set custom log message (either locale key or literal string).
     * @param string $message
     */
    public function setMessage($message) {
        $this->setData('message', $message);
    }

    /**
     * Get flag indicating whether or not message is translated.
     * @return bool
     */
    public function getIsTranslated() {
        return (bool) $this->getData('isTranslated');
    }

    /**
     * Set flag indicating whether or not message is translated.
     * @param bool|int $isTranslated
     */
    public function setIsTranslated($isTranslated) {
        $this->setData('isTranslated', $isTranslated);
    }

    /**
     * Get translated message, translating it if necessary.
     * @param string|null $locale optional
     * @return string
     */
    public function getTranslatedMessage($locale = null) {
        $message = $this->getMessage();
        // If it's already translated, just return the message.
        if ($this->getIsTranslated()) return $message;

        // Otherwise, translate it and include parameters.
        if ($locale === null) $locale = AppLocale::getLocale();
        
        // BONGKAR ARRAY KOTOR: Gabungkan parameter dan musnahkan key 'params'
        $translationParams = array_merge($this->_data, $this->getParams());
        unset($translationParams['params']);
        
        return __($message, $translationParams, $locale);
    }

    /**
     * Get custom log message parameters.
     * @return array
     */
    public function getParams() {
        $params = $this->getData('params');
        return is_array($params) ? $params : [];
    }

    /**
     * Set custom log message parameters.
     * @param array $params
     */
    public function setParams($params) {
        $this->setData('params', $params);
    }

    /**
     * Return the full name of the user.
     * @return string
     */
    public function getUserFullName() {
        $userFullName = $this->getData('userFullName');
        if(!isset($userFullName)) {
            $userDao = DAORegistry::getDAO('UserDAO'); /* @var $userDao UserDAO */
            $userFullName = $userDao->getUserFullName($this->getUserId(), true);
        }

        return ($userFullName ? $userFullName : '');
    }

    /**
     * Return the email address of the user.
     * @return string
     */
    public function getUserEmail() {
        $userEmail = $this->getData('userEmail');

        if(!isset($userEmail)) {
            $userDao = DAORegistry::getDAO('UserDAO'); /* @var $userDao UserDAO */
            $userEmail = $userDao->getUserEmail($this->getUserId(), true);
        }

        return ($userEmail ? $userEmail : '');
    }
}
?>