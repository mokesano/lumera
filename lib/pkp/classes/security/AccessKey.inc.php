<?php
declare(strict_types=1);

/**
 * @file classes/security/AccessKey.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AccessKey
 * @ingroup security
 * @see AccessKeyDAO
 *
 * @brief AccessKey class.
 */

class AccessKey extends DataObject {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AccessKey() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::AccessKey(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        $this->__construct();
    }

    //
    // Get/set methods
    //

    /**
     * Get the ID of the key.
     * @return int
     */
    public function getAccessKeyId() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        $id = $this->getId();
        return $id !== null ? (int) $id : 0;
    }

    /**
     * Set the ID of the access key.
     * @param mixed $accessKeyId
     */
    public function setAccessKeyId($accessKeyId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        $this->setId($accessKeyId !== null ? (int) $accessKeyId : 0);
    }

    /**
     * Get context.
     * @return string
     */
    public function getContext() {
        $data = $this->getData('context');
        return $data !== null ? (string) $data : '';
    }

    /**
     * Set context.
     * @param mixed $context
     */
    public function setContext($context) {
        $this->setData('context', $context !== null ? (string) $context : '');
    }

    /**
     * Get key hash.
     * @return string
     */
    public function getKeyHash() {
        $data = $this->getData('keyHash');
        return $data !== null ? (string) $data : '';
    }

    /**
     * Set key hash.
     * @param mixed $keyHash
     */
    public function setKeyHash($keyHash) {
        $this->setData('keyHash', $keyHash !== null ? (string) $keyHash : '');
    }

    /**
     * Get user ID.
     * @return int
     */
    public function getUserId() {
        $data = $this->getData('userId');
        return $data !== null ? (int) $data : 0;
    }

    /**
     * Set user ID.
     * @param mixed $userId
     */
    public function setUserId($userId) {
        $this->setData('userId', $userId !== null ? (int) $userId : 0);
    }

    /**
     * Get associated ID.
     * @return int
     */
    public function getAssocId() {
        $data = $this->getData('assocId');
        return $data !== null ? (int) $data : 0;
    }

    /**
     * Set associated ID.
     * @param mixed $assocId
     */
    public function setAssocId($assocId) {
        $this->setData('assocId', $assocId !== null ? (int) $assocId : 0);
    }

    /**
     * Get expiry date.
     * @return string
     */
    public function getExpiryDate() {
        $data = $this->getData('expiryDate');
        return $data !== null ? (string) $data : '';
    }

    /**
     * Set expiry date.
     * @param mixed $expiryDate
     */
    public function setExpiryDate($expiryDate) {
        $this->setData('expiryDate', $expiryDate !== null ? (string) $expiryDate : '');
    }
    
}
?>