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
        self::__construct();
    }

    /**
     * Retrieve an accessKey by ID.
     * @param $accessKeyId int
     * @return AccessKey|null
     */
    public function getAccessKey($accessKeyId) {
        $result = $this->retrieve(
            sprintf(
                'SELECT * FROM access_keys WHERE access_key_id = ? AND expiry_date > %s',
                $this->datetimeToDB(Core::getCurrentDate())
            ),
            array((int) $accessKeyId)
        );

        $accessKey = null;
        if ($result->RecordCount() != 0) {
            $accessKey = $this->_returnAccessKeyFromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        unset($result);
        return $accessKey;
    }

    /**
     * Retrieve a accessKey object user ID.
     * @param $context string
     * @param $userId int
     * @return AccessKey|null
     */
    public function getAccessKeyByUserId($context, $userId) {
        $result = $this->retrieve(
            sprintf(
                'SELECT * FROM access_keys WHERE context = ? AND user_id = ? AND expiry_date > %s',
                $this->datetimeToDB(Core::getCurrentDate())
            ),
            array($context, $userId)
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnAccessKeyFromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        unset($result);
        return $returner;
    }

    /**
     * Retrieve a accessKey object by key.
     * @param $context string
     * @param $userId int
     * @param $keyHash string
     * @param $assocId int
     * @return AccessKey|null
     */
    public function getAccessKeyByKeyHash($context, $userId, $keyHash, $assocId = null) {
        $paramArray = array($context, $keyHash, (int) $userId);
        if (isset($assocId)) $paramArray[] = (int) $assocId;
        $result = $this->retrieve(
            sprintf(
                'SELECT * FROM access_keys WHERE context = ? AND key_hash = ? AND user_id = ? AND expiry_date > %s' . (isset($assocId)?' AND assoc_id = ?':''),
                $this->datetimeToDB(Core::getCurrentDate())
            ),
            $paramArray
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnAccessKeyFromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        unset($result);
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
     * @param $row array
     * @return AccessKey
     */
    public function _returnAccessKeyFromRow($row) {
        $accessKey = $this->newDataObject();
        $accessKey->setId($row['access_key_id']);
        $accessKey->setKeyHash($row['key_hash']);
        $accessKey->setExpiryDate($this->datetimeFromDB($row['expiry_date']));
        $accessKey->setContext($row['context']);
        $accessKey->setAssocId($row['assoc_id']);
        $accessKey->setUserId($row['user_id']);

        HookRegistry::dispatch('AccessKeyDAO::_returnAccessKeyFromRow', array(&$accessKey, &$row));

        return $accessKey;
    }

    /**
     * Insert a new accessKey.
     * @param $accessKey AccessKey
     * @return int
     */
    public function insertAccessKey($accessKey) {
        $this->update(
            sprintf('INSERT INTO access_keys
                (key_hash, expiry_date, context, assoc_id, user_id)
                VALUES
                (?, %s, ?, ?, ?)',
                $this->datetimeToDB($accessKey->getExpiryDate())),
            array(
                $accessKey->getKeyHash(),
                $accessKey->getContext(),
                $accessKey->getAssocId()==''?null:(int) $accessKey->getAssocId(),
                (int) $accessKey->getUserId()
            )
        );

        $accessKey->setId($this->getInsertAccessKeyId());
        return $accessKey->getId();
    }

    /**
     * Update an existing accessKey.
     * @param $accessKey AccessKey
     */
    public function updateObject($accessKey) {
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
            array(
                $accessKey->getKeyHash(),
                $accessKey->getContext(),
                $accessKey->getAssocId()==''?null:(int) $accessKey->getAssocId(),
                (int) $accessKey->getUserId(),
                (int) $accessKey->getId()
            )
        );
    }

    public function updateAccessKey($accessKey) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->updateObject($accessKey);
    }

    /**
     * Delete an accessKey.
     * @param $accessKey AccessKey
     */
    public function deleteObject($accessKey) {
        return $this->deleteAccessKeyById($accessKey->getId());
    }

    public function deleteAccessKey($accessKey) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->deleteObject($accessKey);
    }

    /**
     * Delete an accessKey by ID.
     * @param $accessKeyId int
     */
    public function deleteAccessKeyById($accessKeyId) {
        return $this->update(
            'DELETE FROM access_keys WHERE access_key_id = ?',
            array((int) $accessKeyId)
        );
    }

    /**
     * Transfer access keys to another user ID.
     * @param $oldUserId int
     * @param $newUserId int
     */
    public function transferAccessKeys($oldUserId, $newUserId) {
        return $this->update(
            'UPDATE access_keys SET user_id = ? WHERE user_id = ?',
            array((int) $newUserId, (int) $oldUserId)
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
        return $this->getInsertId('access_keys', 'access_key_id');
    }
}

?>