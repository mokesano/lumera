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
     */
    public function __construct() {
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
        $this->__construct();
    }

    /**
     * Generate a key hash from a key.
     * @param mixed $key
     * @return string
     */
    public function generateKeyHash($key) {
        return md5((string) $key);
    }

    /**
     * Validate an access key based on the supplied credentials.
     * If $assocId is specified, it must match the associated ID of the
     * key exactly.
     * @param mixed $context
     * @param mixed $userId
     * @param mixed $keyHash
     * @param mixed $assocId
     * @return AccessKey|null
     */
    public function validateKey($context, $userId, $keyHash, $assocId = null) {
        $safeContext = (string) $context;
        $safeUserId = (int) $userId;
        $safeKeyHash = (string) $keyHash;
        $safeAssocId = $assocId !== null ? (int) $assocId : null;

        $accessKey = $this->accessKeyDao->getAccessKeyByKeyHash($safeContext, $safeUserId, $safeKeyHash, $safeAssocId);
        return $accessKey;
    }

    /**
     * Create an access key with the given information.
     * @param mixed $context
     * @param mixed $userId
     * @param mixed $assocId
     * @param mixed $expiryDays
     * @return string
     */
    public function createKey($context, $userId, $assocId, $expiryDays) {
        $accessKey = new AccessKey();
        $accessKey->setContext((string) $context);
        $accessKey->setUserId((int) $userId);
        $accessKey->setAssocId((int) $assocId);

        $expiryTimestamp = time() + (60 * 60 * 24 * (int) $expiryDays);
        $accessKey->setExpiryDate(Core::getCurrentDate($expiryTimestamp));

        $key = Validation::generatePassword();
        $accessKey->setKeyHash($this->generateKeyHash((string) $key));

        $this->accessKeyDao->insertAccessKey($accessKey);

        return (string) $key;
    }

    /**
     * Periodically clean up expired keys.
     */
    public function _performPeriodicCleanup() {
        if (time() % 100 === 0) {
            /** @var AccessKeyDAO $accessKeyDao */
            $accessKeyDao = DAORegistry::getDAO('AccessKeyDAO');
            $accessKeyDao->deleteExpiredKeys();
        }
    }
    
}
?>