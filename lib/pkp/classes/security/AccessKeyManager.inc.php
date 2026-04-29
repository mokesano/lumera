<?php
declare(strict_types=1);

/**
 * @file classes/security/AccessKeyManager.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AccessKeyManager
 * @ingroup security
 * @see AccessKey
 *
 * @brief Class defining operations for AccessKey management.
 */

class AccessKeyManager {
    /** @var AccessKeyDAO */
    public $accessKeyDao;

    /**
     * Constructor.
     * Create a manager for access keys.
     */
    public function __construct() {
        // Removed & reference
        $this->accessKeyDao = DAORegistry::getDAO('AccessKeyDAO');
        $this->_performPeriodicCleanup();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AccessKeyManager() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::AccessKeyManager(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Generate a key hash from a key.
     * @param $key string
     * @return string
     */
    public function generateKeyHash($key) {
        return md5($key);
    }

    /**
     * Validate an access key based on the supplied credentials.
     * If $assocId is specified, it must match the associated ID of the
     * key exactly.
     * @param $context string The context of the access key
     * @param $userId int The user ID associated with the key
     * @param $keyHash string The hashed access key
     * @param $assocId string optional assoc ID to check against the keys in the database
     * @return AccessKey|null
     */
    public function validateKey($context, $userId, $keyHash, $assocId = null) {
        // Removed & reference from return
        $accessKey = $this->accessKeyDao->getAccessKeyByKeyHash($context, $userId, $keyHash, $assocId);
        return $accessKey;
    }

    /**
     * Create an access key with the given information.
     * @param $context string The context of the access key
     * @param $userId int The ID of the effective user for this access key
     * @param $assocId int The associated ID of the key
     * @param $expiryDays int The number of days before this key expires
     * @return string The generated passkey
     */
    public function createKey($context, $userId, $assocId, $expiryDays) {
        $accessKey = new AccessKey();
        $accessKey->setContext($context);
        $accessKey->setUserId($userId);
        $accessKey->setAssocId($assocId);
        $accessKey->setExpiryDate(Core::getCurrentDate(time() + (60 * 60 * 24 * $expiryDays)));

        $key = Validation::generatePassword();
        $accessKey->setKeyHash($this->generateKeyHash($key));

        $this->accessKeyDao->insertAccessKey($accessKey);

        return $key;
    }

    /**
     * Periodically clean up expired keys.
     */
    public function _performPeriodicCleanup() {
        if (time() % 100 == 0) {
            // Removed & reference
            $accessKeyDao = DAORegistry::getDAO('AccessKeyDAO');
            $accessKeyDao->deleteExpiredKeys();
        }
    }
}

?>