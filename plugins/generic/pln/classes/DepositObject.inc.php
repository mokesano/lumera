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
 * @brief Basic class describing a deposit stored in the PLN.
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
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::DepositObject(). Please refactor to parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Get the content object that's referenced by this deposit object.
     * @return Issue|Article|null
     */
    public function getContent() {
        $objectType = $this->getObjectType();
        $objectId = (int) $this->getObjectId();
        $journalId = (int) $this->getJournalId();

        if ($objectType === PLN_PLUGIN_DEPOSIT_OBJECT_ISSUE) {
            /** @var IssueDAO $issueDao */
            $issueDao = DAORegistry::getDAO('IssueDAO');
            return $issueDao->getIssueById($objectId, $journalId);
        }

        if ($objectType === PLN_PLUGIN_DEPOSIT_OBJECT_ARTICLE) {
            /** @var ArticleDAO $articleDao */
            $articleDao = DAORegistry::getDAO('ArticleDAO');
            return $articleDao->getArticle($objectId, $journalId);
        }

        return null;
    }

    /**
     * Set the content object that's referenced by this deposit object.
     * @param Issue|Article $content
     */
    public function setContent($content) {
        if ($content instanceof Issue) {
            $this->setData('object_type', PLN_PLUGIN_DEPOSIT_OBJECT_ISSUE);
            $this->setData('object_id', (int) $content->getId());
        } elseif ($content instanceof Article) {
            $this->setData('object_type', PLN_PLUGIN_DEPOSIT_OBJECT_ARTICLE);
            $this->setData('object_id', (int) $content->getId());
        }
    }

    /**
     * Get type of the object being referenced by this deposit object.
     * @return string|null
     */
    public function getObjectType() {
        return $this->getData('object_type');
    }

    /**
     * Set type of the object being referenced by this deposit object.
     * @param string $objectType
     */
    public function setObjectType($objectType) {
        $this->setData('object_type', (string) $objectType);
    }

    /**
     * Get the id of the object being referenced by this deposit object.
     * @return int|null
     */
    public function getObjectId() {
        $id = $this->getData('object_id');
        return $id !== null ? (int) $id : null;
    }

    /**
     * Set the id of the object being referenced by this deposit object.
     * @param int $objectId
     */
    public function setObjectId($objectId) {
        $this->setData('object_id', (int) $objectId);
    }

    /**
     * Get the journal id of this deposit object.
     * @return int|null
     */
    public function getJournalId() {
        $id = $this->getData('journal_id');
        return $id !== null ? (int) $id : null;
    }

    /**
     * Set the journal id of this deposit object.
     * @param int $journalId
     */
    public function setJournalId($journalId) {
        $this->setData('journal_id', (int) $journalId);
    }

    /**
     * Get the id of the deposit which includes this deposit object.
     * @return int|null
     */
    public function getDepositId() {
        $id = $this->getData('deposit_id');
        return $id !== null ? (int) $id : null;
    }

    /**
     * Set the id of the deposit which includes this deposit object.
     * @param int $depositId
     */
    public function setDepositId($depositId) {
        $this->setData('deposit_id', (int) $depositId);
    }

    /**
     * Get the date of deposit object creation.
     * @return string|null
     */
    public function getDateCreated() {
        return $this->getData('date_created');
    }

    /**
     * Set the date of deposit object creation.
     * @param string $dateCreated
     */
    public function setDateCreated($dateCreated) {
        $this->setData('date_created', (string) $dateCreated);
    }

    /**
     * Get the modification date of the deposit object.
     * @return string|null
     */
    public function getDateModified() {
        return $this->getData('date_modified');
    }

    /**
     * Set the modification date of the deposit object.
     * @param string $dateModified
     */
    public function setDateModified($dateModified) {
        $this->setData('date_modified', (string) $dateModified);
    }

}
?>