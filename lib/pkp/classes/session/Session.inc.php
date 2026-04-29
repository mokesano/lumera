<?php
declare(strict_types=1);

/**
 * @file classes/session/Session.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Session
 * @ingroup session
 * @see SessionDAO
 *
 * @brief Maintains user state information from one request to the next.
 * [WIZDAM EDITION] PHP 7.4+ Compatible
 */

class Session extends DataObject {

    /** @var User The User object associated with this session */
    protected $user;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Session() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::Session(). Please refactor to parent::__construct().", 
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Get a session variable's value.
     * [MODERNISASI] Use Null Coalescing Operator
     * @param $key string
     * @return mixed
     */
    public function getSessionVar($key) {
        return $_SESSION[$key] ?? null;
    }

    /**
     * Get a session variable's value.
     * @param $key string
     * @param $value mixed
     * @return mixed
     */
    public function setSessionVar($key, $value) {
        $_SESSION[$key] = $value;
        return $value;
    }

    /**
     * Unset (delete) a session variable.
     * @param $key string
     */
    public function unsetSessionVar($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    //
    // Get/set methods
    //

    /**
     * Get user ID (0 if anonymous user).
     * @return int
     */
    public function getUserId() {
        return $this->getData('userId');
    }

    /**
     * Set user ID.
     * [MODERNISASI] Hapus '&' saat mengambil User object
     * @param $userId int
     */
    public function setUserId($userId) {
        if (!isset($userId) || empty($userId)) {
            $this->user = null;
            $userId = null;

        } else if ($userId != $this->getData('userId')) {
            $userDao = DAORegistry::getDAO('UserDAO');
            // [MODERNISASI] Removed & reference assignment
            $this->user = $userDao->getById($userId);
            if (!isset($this->user)) {
                $userId = null;
            }
        }
        return $this->setData('userId', $userId);
    }

    /**
     * Get IP address.
     * @return string
     */
    public function getIpAddress() {
        return $this->getData('ipAddress');
    }

    /**
     * Set IP address.
     * @param $ipAddress string
     */
    public function setIpAddress($ipAddress) {
        return $this->setData('ipAddress', $ipAddress);
    }

    /**
     * Get user agent.
     * @return string
     */
    public function getUserAgent() {
        return $this->getData('userAgent');
    }

    /**
     * Set user agent.
     * @param $userAgent string
     */
    public function setUserAgent($userAgent) {
        return $this->setData('userAgent', $userAgent);
    }

    /**
     * Get time (in seconds) since session was created.
     * @return int
     */
    public function getSecondsCreated() {
        return $this->getData('created');
    }

    /**
     * Set time (in seconds) since session was created.
     * @param $created int
     */
    public function setSecondsCreated($created) {
        return $this->setData('created', $created);
    }

    /**
     * Get time (in seconds) since session was last used.
     * @return int
     */
    public function getSecondsLastUsed() {
        return $this->getData('lastUsed');
    }

    /**
     * Set time (in seconds) since session was last used.
     * @param $lastUsed int
     */
    public function setSecondsLastUsed($lastUsed) {
        return $this->setData('lastUsed', $lastUsed);
    }

    /**
     * Check if session is to be saved across browser sessions.
     * @return boolean
     */
    public function getRemember() {
        return $this->getData('remember');
    }

    /**
     * Set whether session is to be saved across browser sessions.
     * @param $remember boolean
     */
    public function setRemember($remember) {
        return $this->setData('remember', $remember);
    }

    /**
     * Get all session parameters.
     * @return array
     */
    public function getSessionData() {
        return $this->getData('data');
    }

    /**
     * Set session parameters.
     * @param $data array
     */
    public function setSessionData($data) {
        return $this->setData('data', $data);
    }

    /**
     * Get the domain with which the session is registered
     * @return array
     */
    public function getDomain() {
        return $this->getData('domain');
    }

    /**
     * Set the domain with which the session is registered
     * @param $data array
     */
    public function setDomain($data) {
        return $this->setData('domain', $data);
    }

    /**
     * Get user associated with this session (null if anonymous user).
     * [MODERNISASI] Removed & reference
     * @return User
     */
    public function getUser() {
        return $this->user;
    }
}

?>