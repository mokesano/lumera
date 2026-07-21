<?php
declare(strict_types=1);

/**
 * @file classes/security/AccessKeyDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AccessKeyDAO
 * @ingroup security
 * @see AccessKey
 *
 * @brief Operations for retrieving and modifying AccessKey objects.
 */

import('lib.pkp.classes.security.AccessKey');

class AccessKeyDAO extends DAO {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AccessKeyDAO() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::AccessKeyDAO(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        $this->__construct();
    }

    /**
     * Retrieve an accessKey by ID.
     * @param mixed $accessKeyId
     * @return AccessKey|null
     */
    public function getAccessKey($accessKeyId) {
        $result = $this->retrieve(
            sprintf(
                'SELECT * FROM access_keys WHERE access_key_id = ? AND expiry_date > %s',
                $this->datetimeToDB(Core::getCurrentDate())
            ),
            [(int) $accessKeyId]
        );

        $accessKey = null;
        
        // [LUMERA FIX] Modern ADODB check: safer than RecordCount()
        if ($result && !$result->EOF) {
            $accessKey = $this->_returnAccessKeyFromRow($result->GetRowAssoc(false));
        }
        
        if ($result) {
            $result->Close();
        }
        
        return $accessKey;
    }

    /**
     * Retrieve a accessKey object user ID.
     * @param mixed $context
     * @param mixed $userId
     * @return AccessKey|null
     */
    public function getAccessKeyByUserId($context, $userId) {
        $result = $this->retrieve(
            sprintf(
                'SELECT * FROM access_keys WHERE context = ? AND user_id = ? AND expiry_date > %s',
                $this->datetimeToDB(Core::getCurrentDate())
            ),
            [(string) $context, (int) $userId]
        );

        $returner = null;
        
        // [LUMERA FIX] Modern ADODB check
        if ($result && !$result->EOF) {
            $returner = $this->_returnAccessKeyFromRow($result->GetRowAssoc(false));
        }
        
        if ($result) {
            $result->Close();
        }
        
        return $returner;
    }

    /**
     * Retrieve a accessKey object by key.
     * @param mixed $context
     * @param mixed $userId
     * @param mixed $keyHash
     * @param mixed $assocId
     * @return AccessKey|null
     */
    public function getAccessKeyByKeyHash($context, $userId, $keyHash, $assocId = null) {
        $paramArray = [(string) $context, (string) $keyHash, (int) $userId];
        if ($assocId !== null) {
            $paramArray[] = (int) $assocId;
        }
        
        $result = $this->retrieve(
            sprintf(
                'SELECT * FROM access_keys WHERE context = ? AND key_hash = ? AND user_id = ? AND expiry_date > %s' . ($assocId !== null ? ' AND assoc_id = ?' : ''),
                $this->datetimeToDB(Core::getCurrentDate())
            ),
            $paramArray
        );

        $returner = null;
        
        // [LUMERA FIX] Modern ADODB check
        if ($result && !$result->EOF) {
            $returner = $this->_returnAccessKeyFromRow($result->GetRowAssoc(false));
        }
        
        if ($result) {
            $result->Close();
        }
        
        return $returner;
    }

    /**
     * Instantiate and return a new data object.
     * @return AccessKey
     */
    public function newDataObject() {
        return new AccessKey();
    }

    /**
     * Internal function to return an AccessKey object from a row.
     * @param mixed $row
     * @return AccessKey
     */
    public function _returnAccessKeyFromRow($row) {
        $accessKey = $this->newDataObject();

        $accessKey->setId((int) ($row['access_key_id'] ?? 0));
        $accessKey->setKeyHash((string) ($row['key_hash'] ?? ''));
        $accessKey->setExpiryDate($this->datetimeFromDB($row['expiry_date'] ?? null));
        $accessKey->setContext((string) ($row['context'] ?? ''));
        $accessKey->setAssocId(isset($row['assoc_id']) && $row['assoc_id'] !== '' ? (int) $row['assoc_id'] : null);
        $accessKey->setUserId((int) ($row['user_id'] ?? 0));

        HookRegistry::dispatch('AccessKeyDAO::_returnAccessKeyFromRow', [$accessKey, &$row]);

        return $accessKey;
    }

    /**
     * Insert a new accessKey.
     * @param mixed $accessKey AccessKey
     * @return int
     */
    public function insertAccessKey($accessKey) {
        $assocId = $accessKey->getAssocId();
        $safeAssocId = ($assocId === '' || $assocId === null) ? null : (int) $assocId;

        $this->update(
            sprintf('INSERT INTO access_keys
                (key_hash, expiry_date, context, assoc_id, user_id)
                VALUES
                (?, %s, ?, ?, ?)',
                $this->datetimeToDB($accessKey->getExpiryDate())),
            [
                (string) $accessKey->getKeyHash(),
                (string) $accessKey->getContext(),
                $safeAssocId,
                (int) $accessKey->getUserId()
            ]
        );

        $accessKey->setId((int) $this->getInsertAccessKeyId());
        return $accessKey->getId();
    }

    /**
     * Update an existing accessKey.
     * @param mixed $accessKey AccessKey
     */
    public function updateObject($accessKey) {
        $assocId = $accessKey->getAssocId();
        $safeAssocId = ($assocId === '' || $assocId === null) ? null : (int) $assocId;

        return $this->update(
            sprintf('UPDATE access_keys
                SET
                    key_hash = ?,
                    expiry_date = %s,
                    context = ?,
                    assoc_id = ?,
                    user_id = ?
                WHERE access_key_id = ?',
                $this->datetimeToDB($accessKey->getExpiryDate())),
            [
                (string) $accessKey->getKeyHash(),
                (string) $accessKey->getContext(),
                $safeAssocId,
                (int) $accessKey->getUserId(),
                (int) $accessKey->getId()
            ]
        );
    }

    /**
     * [DEPRECATED] Update an existing accessKey.
     * @param mixed $accessKey AccessKey
     */
    public function updateAccessKey($accessKey) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->updateObject($accessKey);
    }

    /**
     * Delete an accessKey.
     * @param mixed $accessKey AccessKey
     */
    public function deleteObject($accessKey) {
        $id = is_object($accessKey) ? (int) $accessKey->getId() : (int) $accessKey;
        return $this->deleteAccessKeyById($id);
    }

    /**
     * [DEPRECATED] Delete an accessKey.
     * @param mixed $accessKey AccessKey
     */
    public function deleteAccessKey($accessKey) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->deleteObject($accessKey);
    }

    /**
     * Delete an accessKey by ID.
     * @param mixed $accessKeyId
     */
    public function deleteAccessKeyById($accessKeyId) {
        return $this->update(
            'DELETE FROM access_keys WHERE access_key_id = ?',
            [(int) $accessKeyId]
        );
    }

    /**
     * Transfer access keys to another user ID.
     * @param mixed $oldUserId
     * @param mixed $newUserId
     */
    public function transferAccessKeys($oldUserId, $newUserId) {
        return $this->update(
            'UPDATE access_keys SET user_id = ? WHERE user_id = ?',
            [(int) $newUserId, (int) $oldUserId]
        );
    }

    /**
     * Delete expired access keys.
     */
    public function deleteExpiredKeys() {
        return $this->update(
            sprintf(
                'DELETE FROM access_keys WHERE expiry_date <= %s',
                $this->datetimeToDB(Core::getCurrentDate())
            )
        );
    }

    /**
     * Get the ID of the last inserted accessKey.
     * @return int
     */
    public function getInsertAccessKeyId() {
        return (int) $this->getInsertId('access_keys', 'access_key_id');
    }

}
?>