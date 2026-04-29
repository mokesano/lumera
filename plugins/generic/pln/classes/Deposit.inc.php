<?php
declare(strict_types=1);

/**
 * @file plugins/generic/pln/classes/Deposit.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Deposit
 * @ingroup plugins_generic_pln
 *
 * @brief Container for deposit objects that are submitted to a PLN
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

class Deposit extends DataObject {
    
    /**
     * Constructor
     * @param string $uuid
     */
    public function __construct($uuid) {
        parent::__construct();

        //Set up new deposits with a UUID
        $this->setUUID($uuid);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Deposit($uuid) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::Deposit(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }
    
    /**
     * Get the type of deposit objects in this deposit.
     * @return string One of PLN_PLUGIN_DEPOSIT_SUPPORTED_OBJECTS
     */
    public function getObjectType() {
        $depositObjects = $this->getDepositObjects();
        // PHP 8: Handle null or empty results gracefully
        if ($depositObjects && !$depositObjects->eof()) {
            $depositObject = $depositObjects->next();
            return ($depositObject ? $depositObject->getObjectType() : null);
        }
        return null;
    }
    
    /**
     * Get all deposit objects of this deposit.
     * @return DAOResultFactory
     */
    public function getDepositObjects() {
        $depositObjectDao = DAORegistry::getDAO('DepositObjectDAO');
        return $depositObjectDao->getByDepositId($this->getJournalId(), $this->getId());
    }
    
    /**
     * Get deposit uuid
     * @return string
     */
    public function getUUID() {
        return $this->getData('uuid');
    }
    
    /**
     * Set deposit uuid
     * @param string $uuid
     */
    public function setUUID($uuid) {
        $this->setData('uuid', $uuid);
    }
    
    /**
     * Get journal id
     * @return int
     */
    public function getJournalId() {
        return $this->getData('journal_id');
    }

    /**
     * Set journal id
     * @param int $journalId
     */
    public function setJournalId($journalId) {
        $this->setData('journal_id', $journalId);
    }

    /**
     * Get deposit status - this is the raw bit field, the other status
     * functions are more immediately useful.
     * @return int
     */
    public function getStatus() {
        return $this->getData('status');
    }

    /**
     * Set deposit status - this is the raw bit field, the other status
     * functions are more immediately useful.
     * @param int $status
     */
    public function setStatus($status) {
        $this->setData('status', $status);
    }

    /**
     * Return a string representation of the local status.
     * @return string
     */
    public function getLocalStatus() {
        if($this->getTransferredStatus()) {
            return 'plugins.generic.pln.status.transferred';
        }
        if($this->getPackagedStatus()) {
            return 'plugins.generic.pln.status.packaged';
        }
        if($this->getNewStatus()) {
            return 'plugins.generic.pln.status.new';
        }        
        return 'plugins.generic.pln.status.unknown';
    }
    
    /**
     * Return a string representation of the processing status.
     * @return string
     */
    public function getProcessingStatus() {
        if($this->getSentStatus()) {
            return 'plugins.generic.pln.status.sent';
        }
        if($this->getValidatedStatus()) {
            return 'plugins.generic.pln.status.validated';
        }
        if($this->getReceivedStatus()) {
            return 'plugins.generic.pln.status.received';
        }
        return 'plugins.generic.pln.status.unknown';
    }
    
    /**
     * Return a string representation of the LOCKSS status.
     * @return string
     */
    public function getLockssStatus() {
        if($this->getLockssAgreementStatus()) {
            return 'plugins.generic.pln.status.agreement';
        }
        if($this->getLockssSyncingStatus()) {
            return 'plugins.generic.pln.status.syncing';
        }
        if($this->getLockssReceivedStatus()) {
            return 'plugins.generic.pln.status.received';
        }
        return 'plugins.generic.pln.status.unknown';
    }
    
    /**
     * Return a string representation of wether or not the deposit processing
     * is complete ie. LOCKSS has acheived agreement.
     * @return string
     */
    public function getComplete() {
        if($this->getLockssAgreementStatus()) {
            return 'common.yes';
        }
        return 'common.no';
    }
    
    /**
     * Get new (blank) deposit status
     * @return boolean
     */
    public function getNewStatus() {
        return $this->getStatus() == PLN_PLUGIN_DEPOSIT_STATUS_NEW;
    }

    /**
     * Set new (blank) deposit status
     */
    public function setNewStatus() {
        $this->setStatus(PLN_PLUGIN_DEPOSIT_STATUS_NEW);
    }

    /**
     * Get a status from the bit field.
     * @param int $field one of the PLN_PLUGIN_DEPOSIT_STATUS_* constants.
     * @return int
     */
    public function _getStatusField($field) {
        return $this->getStatus() & $field;
    }
    
    /**
     * Set a status value.
     * @param boolean $value 
     * @param int $field one of the PLN_PLUGIN_DEPOSIT_STATUS_* constants.
     */
    public function _setStatusField($value, $field) {
        if($value) {
            $this->setStatus($this->getStatus() | $field);
        } else {
            $this->setStatus($this->getStatus() & ~$field);
        }
    }
    
    /**
     * Get whether the deposit has been packaged for the PLN
     * @return int
     */
    public function getPackagedStatus() {
        return $this->_getStatusField(PLN_PLUGIN_DEPOSIT_STATUS_PACKAGED);
    }

    /**
     * Set whether the deposit has been packaged for the PLN
     * @param boolean $status
     */
    public function setPackagedStatus($status = true) {
        $this->_setStatusField($status, PLN_PLUGIN_DEPOSIT_STATUS_PACKAGED);
    }

    /**
     * Get whether the PLN has been notified of the available deposit
     * @return int
     */
    public function getTransferredStatus() {
        return $this->_getStatusField(PLN_PLUGIN_DEPOSIT_STATUS_TRANSFERRED);
    }

    /**
     * Set whether the PLN has been notified of the available deposit
     * @param boolean $status
     */
    public function setTransferredStatus($status = true) {
        $this->_setStatusField($status, PLN_PLUGIN_DEPOSIT_STATUS_TRANSFERRED);
    }

    /**
     * Get whether the PLN has retrieved the deposit from the journal
     * @return int
     */
    public function getReceivedStatus() {
        return $this->_getStatusField(PLN_PLUGIN_DEPOSIT_STATUS_RECEIVED);
    }

    /**
     * Set whether the PLN has retrieved the deposit from the journal
     * @param boolean $status
     */
    public function setReceivedStatus($status = true) {
        $this->_setStatusField($status, PLN_PLUGIN_DEPOSIT_STATUS_RECEIVED);
    }

    /**
     * Get whether the PLN is syncing the deposit across its nodes
     * @return int
     */
    public function getValidatedStatus() {
        return $this->_getStatusField(PLN_PLUGIN_DEPOSIT_STATUS_VALIDATED);
    }

    /**
     * Set whether the PLN is syncing the deposit across its nodes
     * @param boolean $status
     */
    public function setValidatedStatus($status = true) {
        $this->_setStatusField($status, PLN_PLUGIN_DEPOSIT_STATUS_VALIDATED);
    }

    /**
     * Get whether the deposit has been synced across its nodes
     * @return int
     */
    public function getSentStatus() {
        return $this->_getStatusField(PLN_PLUGIN_DEPOSIT_STATUS_SENT);
    }

    /**
     * Set whether the deposit has been synced across its nodes
     * @param boolean $status
     */
    public function setSentStatus($status = true) {
        $this->_setStatusField($status, PLN_PLUGIN_DEPOSIT_STATUS_SENT);
    }

    /**
     * Get whether there's been an error from the staging server
     * @return int
     */
    public function getLockssReceivedStatus() {
        return $this->_getStatusField(PLN_PLUGIN_DEPOSIT_STATUS_LOCKSS_RECEIVED);
    }

    /**
     * Set whether there's been an error from the staging server
     * @param boolean $status
     */
    public function setLockssReceivedStatus($status = true) {
        $this->_setStatusField($status, PLN_PLUGIN_DEPOSIT_STATUS_LOCKSS_RECEIVED);
    }

    /**
     * Get whether there's been a local error in the deposit process
     * @return int
     */
    public function getLockssSyncingStatus() {
        return $this->_getStatusField(PLN_PLUGIN_DEPOSIT_STATUS_LOCKSS_SYNCING);
    }

    /**
     * Set whether there's been a local error in the deposit process
     * @param boolean $status
     */
    public function setLockssSyncingStatus($status = true) {
        $this->_setStatusField($status, PLN_PLUGIN_DEPOSIT_STATUS_LOCKSS_SYNCING);
    }

    /**
     * Get whether there's been an update to a deposit
     * @return int
     */
    public function getLockssAgreementStatus() {
        return $this->_getStatusField(PLN_PLUGIN_DEPOSIT_STATUS_LOCKSS_AGREEMENT);
    }

    /**
     * Set whether there's been an update to a deposit
     * @param boolean $status
     */
    public function setLockssAgreementStatus($status = true) {
        $this->_setStatusField($status, PLN_PLUGIN_DEPOSIT_STATUS_LOCKSS_AGREEMENT);
    }

    /**
     * Get the date of the last status change
     * @return string
     */
    public function getLastStatusDate() {
        return $this->getData('date_status');
    }

    /**
     * Set set the date of the last status change
     * @param string $dateLastStatus
     */
    public function setLastStatusDate($dateLastStatus) {
        $this->setData('date_status', $dateLastStatus);
    }

    /**
     * Get the date of deposit creation
     * @return string
     */
    public function getDateCreated() {
        return $this->getData('date_created');
    }

    /**
     * Set the date of deposit creation
     * @param string $dateCreated
     */
    public function setDateCreated($dateCreated) {
        $this->setData('date_created', $dateCreated);
    }

    /**
     * Get the modification date of the deposit
     * @return string
     */
    public function getDateModified() {
        return $this->getData('date_modified');
    }

    /**
     * Set the modification date of the deposit
     * @param string $dateModified
     */
    public function setDateModified($dateModified) {
        $this->setData('date_modified', $dateModified);
    }

}

?>