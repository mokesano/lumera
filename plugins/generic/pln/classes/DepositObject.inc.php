<?php
declare(strict_types=1);

/**
 * @file plugins/generic/pln/classes/DepositObject.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class DepositObject
 * @ingroup plugins_generic_pln
 *
 * @brief Basic class describing a deposit stored in the PLN
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

class DepositObject extends DataObject {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DepositObject() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::DepositObject(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Get the content object that's referenced by this deposit object
     * @return Issue|Article|null
     */
    public function getContent() {
        switch ($this->getObjectType()) {
            case PLN_PLUGIN_DEPOSIT_OBJECT_ISSUE:
                $issueDao = DAORegistry::getDAO('IssueDAO');
                return $issueDao->getIssueById($this->getObjectId(),$this->getJournalId());
            case PLN_PLUGIN_DEPOSIT_OBJECT_ARTICLE:
                $articleDao = DAORegistry::getDAO('ArticleDAO');
                return $articleDao->getArticle($this->getObjectId(),$this->getJournalId());
        }
        return null; // [PHP 8 FIX] Explicit return for fallback
    }

    /**
     * Set the content object that's referenced by this deposit object
     * @param Issue|Article $content
     */
    public function setContent($content) {
        switch (get_class($content)) {
            case PLN_PLUGIN_DEPOSIT_OBJECT_ISSUE:
            case PLN_PLUGIN_DEPOSIT_OBJECT_ARTICLE:
                // [PHP 8 FIX] get_class without arguments is deprecated/removed in PHP 8 inside methods if trying to get current class, but here it takes arg. 
                // However, directly calling get_class($content) is fine if $content is not null.
                if ($content) {
                    $objectType = get_class($content);
                    $objectId = $content->getId();
                    $this->setData('object_id', $objectId);
                    $this->setData('object_type', $objectType);
                }
                break;
            default:
                // Do nothing
        }
    }

    /**
     * Get type of the object being referenced by this deposit object
     * @return string
     */
    public function getObjectType() {
        return $this->getData('object_type');
    }

    /**
     * Set type of the object being referenced by this deposit object
     * @param string $objectType
     */
    public function setObjectType($objectType) {
        $this->setData('object_type', $objectType);
    }

    /**
     * Get the id of the object being referenced by this deposit object
     * @return int
     */
    public function getObjectId() {
        return $this->getData('object_id');
    }

    /**
     * Set the id of the object being referenced by this deposit object
     * @param int $objectId
     */
    public function setObjectId($objectId) {
        $this->setData('object_id', $objectId);
    }

    /**
     * Get the journal id of this deposit object
     * @return int
     */
    public function getJournalId() {
        return $this->getData('journal_id');
    }

    /**
     * Set the journal id of this deposit object
     * @param int $journalId
     */
    public function setJournalId($journalId) {
        $this->setData('journal_id', $journalId);
    }

    /**
     * Get the id of the deposit which includes this deposit object
     * @return int
     */
    public function getDepositId() {
        return $this->getData('deposit_id');
    }

    /**
     * Set the id of the deposit which includes this deposit object
     * @param int $depositId
     */
    public function setDepositId($depositId) {
        $this->setData('deposit_id', $depositId);
    }

    /**
     * Get the date of deposit object creation
     * @return string
     */
    public function getDateCreated() {
        return $this->getData('date_created');
    }

    /**
     * Set the date of deposit object creation
     * @param string $dateCreated
     */
    public function setDateCreated($dateCreated) {
        $this->setData('date_created', $dateCreated);
    }

    /**
     * Get the modification date of the deposit object
     * @return string
     */
    public function getDateModified() {
        return $this->getData('date_modified');
    }

    /**
     * Set the modification date of the deposit object
     * @param string $dateModified
     */
    public function setDateModified($dateModified) {
        $this->setData('date_modified', $dateModified);
    }
}
?>