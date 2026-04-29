<?php
declare(strict_types=1);

/**
 * @file classes/session/SessionDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SessionDAO
 * @ingroup session
 * @see Session
 *
 * @brief Operations for retrieving and modifying Session objects.
 * [WIZDAM EDITION] PHP 7.4+ Compatible & Hardened
 */

import('lib.pkp.classes.session.Session');

class SessionDAO extends DAO {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SessionDAO() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::SessionDAO(). Please refactor to parent::__construct().", 
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Instantiate and return a new data object.
     */
    public function newDataObject() {
        return new Session();
    }

    /**
     * Retrieve a session by ID.
     * @param $sessionId string
     * @return Session
     */
    public function getSession($sessionId) {
        $result = $this->retrieve(
            'SELECT * FROM sessions WHERE session_id = ?',
            array($sessionId)
        );

        $session = null;
        if ($result->RecordCount() != 0) {
            $row = $result->GetRowAssoc(false);

            $session = $this->newDataObject();
            $session->setId($row['session_id']);
            $session->setUserId($row['user_id']);
            $session->setIpAddress($row['ip_address']);
            $session->setUserAgent($row['user_agent']);
            $session->setSecondsCreated($row['created']);
            $session->setSecondsLastUsed($row['last_used']);
            $session->setRemember($row['remember']);
            $session->setSessionData($row['data']);
            $session->setDomain($row['domain']);
        }

        $result->Close();
        return $session;
    }

    /**
     * Insert a new session.
     * @param $session Session
     */
    public function insertSession($session) {
        try {
            return $this->update(
                'INSERT INTO sessions
                    (session_id, ip_address, user_agent, created, last_used, remember, data, domain)
                    VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?)',
                array(
                    $session->getId(),
                    $session->getIpAddress(),
                    substr($session->getUserAgent(), 0, 255),
                    (int) $session->getSecondsCreated(),
                    (int) $session->getSecondsLastUsed(),
                    $session->getRemember() ? 1 : 0,
                    $session->getSessionData(),
                    $session->getDomain()
                )
            );
        } catch (\Exception $e) {
            // Menangkap error Duplicate Entry (race condition dari bot)
            // dan mengalihkannya menjadi operasi update agar terhindar dari Fatal Error.
            return $this->updateObject($session);
        }
    }

    /**
     * Update an existing session.
     * @param $session Session
     */
    public function updateObject($session) {
        $userId = $session->getUserId();

        // Normalisasi User ID untuk kompatibilitas Database Strict Mode
        // (Mencegah insert string kosong ke kolom integer)
        if (empty($userId)) {
            $userId = null; 
        } else {
            $userId = (int) $userId;
        }

        return $this->update(
            'UPDATE sessions
                SET
                    user_id = ?,
                    ip_address = ?,
                    user_agent = ?,
                    created = ?,
                    last_used = ?,
                    remember = ?,
                    data = ?,
                    domain = ?
                WHERE session_id = ?',
            array(
                $userId,
                $session->getIpAddress(),
                substr($session->getUserAgent(), 0, 255),
                (int) $session->getSecondsCreated(),
                (int) $session->getSecondsLastUsed(),
                $session->getRemember() ? 1 : 0,
                $session->getSessionData(),
                $session->getDomain(),
                $session->getId()
            )
        );
    }

    /**
     * Update an existing session.
     * @deprecated since OJS 2.x. Please use updateObject() instead.
     * @see SessionDAO::updateObject()
     * @param $session Session
     * @return boolean
     */
    public function updateSession($session) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Function '" . get_class($this) . "::" . __FUNCTION__ . "()' is deprecated. Please use 'updateObject()' instead.", E_USER_DEPRECATED);
        }
        return $this->updateObject($session);
    }

    /**
     * Delete a session object.
     * Standard DAO method for object deletion.
     * @param $session Session
     * @return boolean
     */
    public function deleteObject($session) {
        return $this->deleteSessionById($session->getId());
    }

    /**
     * Delete a session.
     * @deprecated since OJS 2.x. Please use deleteObject() instead.
     * @see SessionDAO::deleteObject()
     * @param $session Session
     * @return boolean
     */
    public function deleteSession($session) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Function '" . get_class($this) . "::" . __FUNCTION__ . "()' is deprecated. Please use 'deleteObject()' instead.", E_USER_DEPRECATED);
        }
        return $this->deleteObject($session);
    }

    /**
     * Delete a session by ID.
     * @param $sessionId string
     */
    public function deleteSessionById($sessionId) {
        return $this->update(
            'DELETE FROM sessions WHERE session_id = ?',
            array($sessionId)
        );
    }

    /**
     * Delete sessions by user ID.
     * @param $userId string
     */
    public function deleteSessionsByUserId($userId) {
        return $this->update(
            'DELETE FROM sessions WHERE user_id = ?',
            array((int) $userId)
        );
    }

    /**
     * Delete all sessions older than the specified time.
     */
    public function deleteSessionByLastUsed($lastUsed, $lastUsedRemember = 0) {
        if ($lastUsedRemember == 0) {
            return $this->update(
                'DELETE FROM sessions WHERE (last_used < ? AND remember = 0)',
                array((int) $lastUsed)
            );
        } else {
            return $this->update(
                'DELETE FROM sessions WHERE (last_used < ? AND remember = 0) OR (last_used < ? AND remember = 1)',
                array((int) $lastUsed, (int) $lastUsedRemember)
            );
        }
    }

    /**
     * Delete all sessions.
     */
    public function deleteAllSessions() {
        return $this->update('DELETE FROM sessions');
    }

    /**
     * Check if a session exists with the specified ID.
     * @param $sessionId string
     * @return boolean
     */
    public function sessionExistsById($sessionId) {
        $result = $this->retrieve(
            'SELECT COUNT(*) FROM sessions WHERE session_id = ?',
            array($sessionId)
        );
        $returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

        $result->Close();
        return $returner;
    }
}
?>