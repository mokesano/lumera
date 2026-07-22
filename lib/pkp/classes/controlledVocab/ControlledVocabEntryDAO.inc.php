<?php
declare(strict_types=1);

/**
 * @file classes/controlledVocab/ControlledVocabEntryDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ControlledVocabEntryDAO
 * @ingroup controlled_vocab
 * @see ControlledVocabEntry
 *
 * @brief Operations for retrieving and modifying ControlledVocabEntry objects
 */

import('lib.pkp.classes.controlledVocab.ControlledVocabEntry');

class ControlledVocabEntryDAO extends DAO {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ControlledVocabEntryDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::ControlledVocabEntryDAO(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Retrieve a controlled vocab entry by controlled vocab entry ID.
     * @param int $controlledVocabEntryId int
     * @param int $controlledVocabId int
     * @return ControlledVocabEntry
     */
    public function getById($controlledVocabEntryId, $controlledVocabId = null) {
        $params = [(int) $controlledVocabEntryId];
        if (!empty($controlledVocabId)) $params[] = (int) $controlledVocabId;

        $result = $this->retrieve(
            'SELECT * FROM controlled_vocab_entries WHERE controlled_vocab_entry_id = ?' .
            (!empty($controlledVocabId)?' AND controlled_vocab_id = ?':''),
            $params
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_fromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        return $returner;
    }

    /**
     * Retrieve a controlled vocab entry by resolving one of its settings
     * to the corresponding entry id.
     * @param string $settingValue string
     * @param string $symbolic string
     * @param int $assocType integer
     * @param int $assocId integer
     * @param string $settingName string
     * @param string $locale string
     * @return ControlledVocabEntry
     */
    public function getBySetting($settingValue, $symbolic, $assocType = 0, $assocId = 0, $settingName = 'name', $locale = '') {
        $result = $this->retrieve(
            'SELECT cve.*
             FROM controlled_vocabs cv
             INNER JOIN controlled_vocab_entries cve ON cv.controlled_vocab_id = cve.controlled_vocab_id
             INNER JOIN controlled_vocab_entry_settings cves ON cve.controlled_vocab_entry_id = cves.controlled_vocab_entry_id
             WHERE    cves.setting_name = ? AND
                cves.locale = ? AND
                cves.setting_value = ? AND
                cv.symbolic = ? AND
                cv.assoc_type = ? AND
                cv.assoc_id = ?',
            [$settingName, $locale, $settingValue, $symbolic, (int) $assocType, (int) $assocId]
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_fromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        return $returner;
    }

    /**
     * Construct a new data object corresponding to this DAO.
     * @return ControlledVocabEntry
     */
    public function newDataObject() {
        return new ControlledVocabEntry();
    }

    /**
     * Internal function to return an ControlledVocabEntry object from a row.
     * @param array $row array
     * @return ControlledVocabEntry
     */
    public function _fromRow($row) {
        $controlledVocabEntry = $this->newDataObject();
        $controlledVocabEntry->setControlledVocabId($row['controlled_vocab_id']);
        $controlledVocabEntry->setId($row['controlled_vocab_entry_id']);
        $controlledVocabEntry->setSequence($row['seq']);

        $this->getDataObjectSettings('controlled_vocab_entry_settings', 'controlled_vocab_entry_id', $row['controlled_vocab_entry_id'], $controlledVocabEntry);

        return $controlledVocabEntry;
    }

    /**
     * Get the list of fields for which data can be localized.
     * @return array
     */
    public function getLocaleFieldNames() {
        return array_merge(parent::getLocaleFieldNames(), ['name']);
    }

    /**
     * Update the localized fields for this table.
     * @param object $controlledVocabEntry object
     */
    public function updateLocaleFields($controlledVocabEntry) {
        $this->updateDataObjectSettings(
            'controlled_vocab_entry_settings', 
            $controlledVocabEntry, 
            ['controlled_vocab_entry_id' => $controlledVocabEntry->getId()]
        );
    }

    /**
     * Insert a new ControlledVocabEntry.
     * @param ControlledVocabEntry $controlledVocabEntry
     * @return int
     */
    public function insertObject($controlledVocabEntry) {
        $this->update(
            sprintf('INSERT INTO controlled_vocab_entries
                (controlled_vocab_id, seq)
                VALUES
                (?, ?)'),
            [
                (int) $controlledVocabEntry->getControlledVocabId(),
                (float) $controlledVocabEntry->getSequence()
            ]
        );
        $controlledVocabEntry->setId($this->getInsertId());
        $this->updateLocaleFields($controlledVocabEntry);
        return (int) $controlledVocabEntry->getId();
    }

    /**
     * Delete a controlled vocab entry.
     * @param ControlledVocabEntry $controlledVocabEntry
     * @return boolean
     */
    public function deleteObject($controlledVocabEntry) {
        return $this->deleteObjectById($controlledVocabEntry->getId());
    }

    /**
     * Delete a controlled vocab entry by controlled vocab entry ID.
     * @param int $controlledVocabEntryId int
     * @return boolean
     */
    public function deleteObjectById($controlledVocabEntryId) {
        $params = array((int) $controlledVocabEntryId);
        $this->update('DELETE FROM controlled_vocab_entry_settings WHERE controlled_vocab_entry_id = ?', $params);
        return $this->update('DELETE FROM controlled_vocab_entries WHERE controlled_vocab_entry_id = ?', $params);
    }

    /**
     * Retrieve an iterator of controlled vocabulary entries matching a
     * particular controlled vocabulary ID.
     * @param int $controlledVocabId int
     * @return object
     */
    public function getByControlledVocabId($controlledVocabId, $rangeInfo = null, $filter = null) {
        $params = [(int) $controlledVocabId];
        if (!empty($filter)) $params[] = "%$filter%";

        $result = $this->retrieveRange(
            'SELECT *
             FROM controlled_vocab_entries cve '.
             (!empty($filter) ? 'INNER JOIN controlled_vocab_entry_settings cves ON cve.controlled_vocab_entry_id = cves.controlled_vocab_entry_id ' : '') .
            'WHERE controlled_vocab_id = ? ' .
             (!empty($filter) ? 'AND cves.setting_value LIKE ? ' : '') .
            'ORDER BY seq',
            $params,
            $rangeInfo
        );

        $returner = new DAOResultFactory($result, $this, '_fromRow');
        return $returner;
    }

    /**
     * Update an existing review form element.
     * @param ControlledVocabEntry $controlledVocabEntry
     */
    public function updateObject($controlledVocabEntry) {
        $returner = $this->update(
            'UPDATE    controlled_vocab_entries
            SET    controlled_vocab_id = ?,
                seq = ?
            WHERE    controlled_vocab_entry_id = ?',
            [
                (int) $controlledVocabEntry->getControlledVocabId(),
                (float) $controlledVocabEntry->getSequence(),
                (int) $controlledVocabEntry->getId()
            ]
        );
        $this->updateLocaleFields($controlledVocabEntry);
    }

    /**
     * Sequentially renumber entries in their sequence order.
     * @param int $controlledVocabId int
     */
    public function resequence($controlledVocabId) {
        $result = $this->retrieve(
            'SELECT controlled_vocab_entry_id FROM controlled_vocab_entries WHERE controlled_vocab_id = ? ORDER BY seq',
            [(int) $controlledVocabId]
        );

        for ($i=1; !$result->EOF; $i++) {
            list($controlledVocabEntryId) = $result->fields;
            $this->update(
                'UPDATE controlled_vocab_entries SET seq = ? WHERE controlled_vocab_entry_id = ?',
                [
                    (int) $i,
                    (int) $controlledVocabEntryId
                ]
            );

            $result->MoveNext();
        }

        $result->Close();
        unset($result);
    }

    /**
     * Get the ID of the last inserted controlled vocab.
     * @return int
     */
    public function getInsertId($table = '', $id = '', $callHooks = true) {
        return parent::getInsertId('controlled_vocab_entries', 'controlled_vocab_entry_id');
    }
    
}
?>