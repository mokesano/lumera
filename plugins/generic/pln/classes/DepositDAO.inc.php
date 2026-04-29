<?php
declare(strict_types=1);

/**
 * @file plugins/generic/pln/classes/DepositDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class DepositDAO
 * @ingroup plugins_generic_pln
 *
 * @brief Operations for adding a PLN deposit
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

class DepositDAO extends DAO {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DepositDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::DepositDAO(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Retrieve a deposit by deposit id.
     * @param int $journalId
     * @param int $depositId
     * @return Deposit|null
     */
    public function getDepositById($journalId, $depositId) {
        $result = $this->retrieve(
            'SELECT * FROM pln_deposits WHERE journal_id = ? AND deposit_id = ?',
            [
                (int) $journalId,
                (int) $depositId
            ]
        );
        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnDepositFromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        return $returner;
    }

    /**
     * Retrieve a deposit by deposit uuid.
     * @param int $journalId
     * @param string $depositUuid
     * @return Deposit|null
     */
    public function getDepositByUUID($journalId, $depositUuid) {
        $result = $this->retrieve(
            'SELECT * FROM pln_deposits WHERE journal_id = ? AND uuid = ?',
            [
                (int) $journalId,
                $depositUuid
            ]
        );
        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnDepositFromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        return $returner;
    }

    /**
     * Retrieve all deposits.
     * @param int $journalId
     * @return DAOResultFactory
     */
    public function getDepositsByJournalId($journalId, $dbResultRange = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM pln_deposits WHERE journal_id = ? ORDER BY deposit_id',
            (int) $journalId,
            $dbResultRange
        );
        $returner = new DAOResultFactory($result, $this, '_returnDepositFromRow');
        return $returner;
    }

    /**
     * Retrieve all newly-created deposits (ones with new status)
     * @param int $journalId
     * @return DAOResultFactory
     */
    public function getNew($journalId) {
        $result = $this->retrieve(
            'SELECT * FROM pln_deposits WHERE journal_id = ? AND status = ?',
            [
                (int) $journalId,
                (int) PLN_PLUGIN_DEPOSIT_STATUS_NEW
            ]
        );
        $returner = new DAOResultFactory($result, $this, '_returnDepositFromRow');
        return $returner;
    }

    /**
     * Retrieve all deposits that need transferring
     * @param int $journalId
     * @return DAOResultFactory
     */
    public function getNeedTransferring($journalId) {
        $result = $this->retrieve(
            'SELECT * FROM pln_deposits AS d WHERE d.journal_id = ? AND (d.status & ?) = 0 AND (d.status & ?) = 0',
            [
                (int) $journalId,
                (int) PLN_PLUGIN_DEPOSIT_STATUS_TRANSFERRED,
                (int) PLN_PLUGIN_DEPOSIT_STATUS_LOCKSS_AGREEMENT
            ]
        );
        $returner = new DAOResultFactory($result, $this, '_returnDepositFromRow');
        return $returner;
    }

    /**
     * Retrieve all deposits that need packaging
     * @param int $journalId
     * @return DAOResultFactory
     */
    public function getNeedPackaging($journalId) {
        $result = $this->retrieve(
            'SELECT * FROM pln_deposits AS d WHERE d.journal_id = ? AND (d.status & ?) = 0 AND (d.status & ?) = 0',
            [
                (int) $journalId,
                (int) PLN_PLUGIN_DEPOSIT_STATUS_PACKAGED,
                (int) PLN_PLUGIN_DEPOSIT_STATUS_LOCKSS_AGREEMENT
            ]
        );
        $returner = new DAOResultFactory($result, $this, '_returnDepositFromRow');
        return $returner;
    }

    /**
     * Retrieve all deposits that need a status update
     * @param int $journalId
     * @return DAOResultFactory
     */
    public function getNeedStagingStatusUpdate($journalId) {
        $result = $this->retrieve(
            'SELECT * FROM pln_deposits AS d WHERE d.journal_id = ? AND (d.status & ?) <> 0 AND (d.status & ?) = 0',
            [
                (int) $journalId,
                (int) PLN_PLUGIN_DEPOSIT_STATUS_TRANSFERRED,
                (int) PLN_PLUGIN_DEPOSIT_STATUS_LOCKSS_AGREEMENT
            ]
        );
        $returner = new DAOResultFactory($result, $this, '_returnDepositFromRow');
        return $returner;
    }

    /**
     * Insert deposit object
     * @param Deposit $deposit
     * @return int inserted Deposit id
     */
    public function insertDeposit($deposit) {
        $ret = $this->update(
            sprintf('
                INSERT INTO pln_deposits
                    (journal_id,
                    uuid,
                    status,
                    date_status,
                    date_created,
                    date_modified)
                VALUES
                    (?, ?, ?, %s, NOW(), %s)',
                $this->datetimeToDB($deposit->getLastStatusDate()),
                $this->datetimeToDB($deposit->getDateModified())
            ),
            [
                (int) $deposit->getJournalId(),
                $deposit->getUUID(),
                (int) $deposit->getStatus()
            ]
        );
        $deposit->setId($this->getInsertDepositId());
        return $deposit->getId();
    }

    /**
     * Update deposit
     * @param Deposit $deposit
     * @return boolean
     */
    public function updateDeposit($deposit) {
        $ret = $this->update(
            sprintf('
                UPDATE pln_deposits SET
                    journal_id = ?,
                    uuid = ?,
                    status = ?,
                    date_status = %s,
                    date_created = %s,
                    date_modified = NOW()
                WHERE deposit_id = ?',
                $this->datetimeToDB($deposit->getLastStatusDate()),
                $this->datetimeToDB($deposit->getDateCreated())
            ),
            [
                (int) $deposit->getJournalId(),
                $deposit->getUUID(),
                (int) $deposit->getStatus(),
                (int) $deposit->getId()
            ]
        );
        return $ret;
    }

    /**
     * Delete deposit
     * @param Deposit $deposit
     * @return boolean
     */
    public function deleteDeposit($deposit) {
        $deposit_object_dao = DAORegistry::getDAO('DepositObjectDAO');
        
        $depositObjects = $deposit->getDepositObjects();
        // PHP 8 safe iteration for DAOResultFactory
        while ($deposit_object = $depositObjects->next()) {
            $deposit_object_dao->deleteDepositObject($deposit_object);
        }
        
        $ret = $this->update(
            'DELETE from pln_deposits WHERE deposit_id = ?',
            (int) $deposit->getId()
        );
        return $ret;
    }

    /**
     * Delete deposits by journal id
     * @param int $journalId
     */
    public function deleteDepositsByJournalId($journalId) {
        $deposits = $this->getDepositsByJournalId($journalId);
        while ($deposit = $deposits->next()) {
            $this->deleteDeposit($deposit);
        }
    }

    /**
     * Get the ID of the last inserted deposit.
     * @return int
     */
    public function getInsertDepositId() {
        return $this->getInsertId('pln_deposits', 'deposit_id');
    }

    /**
     * Construct a new data object corresponding to this DAO.
     * @return Deposit
     */
    public function _newDataObject() {
        $plnPlugin = PluginRegistry::getPlugin('generic', PLN_PLUGIN_NAME);
        return new Deposit(null);
    }

    /**
     * Internal function to return a deposit from a row.
     * @param array $row
     * @return Deposit
     */
    public function _returnDepositFromRow($row) {
        $deposit = $this->_newDataObject();
        $deposit->setId($row['deposit_id']);
        $deposit->setJournalId($row['journal_id']);
        $deposit->setUUID($row['uuid']);
        $deposit->setStatus($row['status']);
        $deposit->setLastStatusDate($this->datetimeFromDB($row['date_status']));
        $deposit->setDateCreated($this->datetimeFromDB($row['date_created']));
        $deposit->setDateModified($this->datetimeFromDB($row['date_modified']));

        HookRegistry::dispatch('DepositDAO::_returnDepositFromRow', [&$deposit, &$row]);

        return $deposit;
    }
}
?>