<?php
declare(strict_types=1);

/**
 * @file classes/controlledVocab/ControlledVocabDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ControlledVocabDAO
 * @ingroup controlled_vocab
 * @see ControlledVocab
 *
 * @brief Operations for retrieving and modifying ControlledVocab objects.
 */

import('lib.pkp.classes.controlledVocab.ControlledVocab');

class ControlledVocabDAO extends DAO {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ControlledVocabDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::ControlledVocabDAO(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Return the Controlled Vocab Entry DAO for this Controlled Vocab.
     * Can be subclassed to provide extended DAOs.
     * @return ControlledVocabEntryDAO
     */
    public function getEntryDAO() {
        return DAORegistry::getDAO('ControlledVocabEntryDAO');
    }

    /**
     * Retrieve a controlled vocab by controlled vocab ID.
     * @param mixed $controlledVocabId
     * @return ControlledVocab|null
     */
    public function getById($controlledVocabId) {
        $result = $this->retrieve(
            'SELECT * FROM controlled_vocabs WHERE controlled_vocab_id = ?', 
            [(int) $controlledVocabId]
        );

        $returner = null;
        if (!$result->EOF) {
            $returner = $this->_fromRow($result->getRowAssoc(false));
        }
        $result->Close();
        return $returner;
    }

    /**
     * Fetch a controlled vocab by symbolic info, building it if needed.
     * @param string $symbolic
     * @param mixed $assocType
     * @param mixed $assocId
     * @return ControlledVocab
     */
    public function build($symbolic, $assocType = 0, $assocId = 0) {
        $controlledVocab = $this->getBySymbolic($symbolic, $assocType, $assocId);
        if ($controlledVocab) {
            return $controlledVocab;
        }

        $controlledVocab = $this->newDataObject();
        $controlledVocab->setSymbolic($symbolic);
        $controlledVocab->setAssocType((int) $assocType);
        $controlledVocab->setAssocId((int) $assocId);
        $this->insertObject($controlledVocab);
        
        return $controlledVocab;
    }

    /**
     * Construct a new data object corresponding to this DAO.
     * @return ControlledVocab
     */
    public function newDataObject() {
        return new ControlledVocab();
    }

    /**
     * Internal function to return a ControlledVocab object from a row.
     * @param array $row
     * @return ControlledVocab
     */
    public function _fromRow($row) {
        $controlledVocab = $this->newDataObject();
        $controlledVocab->setId((int) $row['controlled_vocab_id']);
        $controlledVocab->setAssocType((int) $row['assoc_type']);
        $controlledVocab->setAssocId((int) $row['assoc_id']);
        $controlledVocab->setSymbolic($row['symbolic']);

        return $controlledVocab;
    }

    /**
     * Insert a new ControlledVocab.
     * @param ControlledVocab $controlledVocab
     * @return int
     */
    public function insertObject($controlledVocab) {
        $this->update(
            'INSERT INTO controlled_vocabs (symbolic, assoc_type, assoc_id) VALUES (?, ?, ?)',
            [
                $controlledVocab->getSymbolic(),
                (int) $controlledVocab->getAssocType(),
                (int) $controlledVocab->getAssocId()
            ]
        );
        $controlledVocab->setId($this->getInsertId());
        return $controlledVocab->getId();
    }

    /**
     * Update an existing controlled vocab.
     * @param ControlledVocab $controlledVocab
     * @return bool
     */
    public function updateObject($controlledVocab) {
        return $this->update(
            'UPDATE controlled_vocabs SET symbolic = ?, assoc_type = ?, assoc_id = ? WHERE controlled_vocab_id = ?',
            [
                $controlledVocab->getSymbolic(),
                (int) $controlledVocab->getAssocType(),
                (int) $controlledVocab->getAssocId(),
                (int) $controlledVocab->getId()
            ]
        );
    }

    /**
     * Delete a controlled vocab.
     * @param ControlledVocab $controlledVocab
     * @return bool
     */
    public function deleteObject($controlledVocab) {
        return $this->deleteObjectById((int) $controlledVocab->getId());
    }

    /**
     * Delete a controlled vocab by controlled vocab ID.
     * @param mixed $controlledVocabId
     * @return bool
     */
    public function deleteObjectById($controlledVocabId) {
        $controlledVocabId = (int) $controlledVocabId;

        $this->update('DELETE FROM controlled_vocab_entries WHERE controlled_vocab_id = ?', $controlledVocabId);
        
        return $this->update('DELETE FROM controlled_vocabs WHERE controlled_vocab_id = ?', $controlledVocabId);
    }

    /**
     * Retrieve a controlled vocab matching the specified symbolic name and assoc info.
     * @param string $symbolic
     * @param mixed $assocType
     * @param mixed $assocId
     * @return ControlledVocab|null
     */
    public function getBySymbolic($symbolic, $assocType, $assocId) {
        $result = $this->retrieve(
            'SELECT * FROM controlled_vocabs WHERE symbolic = ? AND assoc_type = ? AND assoc_id = ?',
            [$symbolic, (int) $assocType, (int) $assocId]
        );

        $returner = null;
        if (!$result->EOF) {
            $returner = $this->_fromRow($result->getRowAssoc(false));
        }
        $result->Close();
        return $returner;
    }

    /**
     * Get a list of controlled vocabulary options by symbolic name.
     * @param string $symbolic
     * @param mixed $assocType
     * @param mixed $assocId
     * @param string $settingName
     * @return array
     */
    public function enumerateBySymbolic($symbolic, $assocType, $assocId, $settingName = 'name') {
        $controlledVocab = $this->getBySymbolic($symbolic, $assocType, $assocId);
        if (!$controlledVocab) {
            return [];
        }
        return $controlledVocab->enumerate($settingName);
    }

    /**
     * Get a list of controlled vocabulary options by vocab ID.
     * @param mixed $controlledVocabId
     * @param string $settingName
     * @return array
     */
    public function enumerate($controlledVocabId, $settingName = 'name') {
        $result = $this->retrieve(
            'SELECT e.controlled_vocab_entry_id,
                COALESCE(l.setting_value, p.setting_value, n.setting_value) AS setting_value,
                COALESCE(l.setting_type, p.setting_type, n.setting_type) AS setting_type
            FROM controlled_vocab_entries e
                LEFT JOIN controlled_vocab_entry_settings l ON (l.controlled_vocab_entry_id = e.controlled_vocab_entry_id AND l.setting_name = ? AND l.locale = ?)
                LEFT JOIN controlled_vocab_entry_settings p ON (p.controlled_vocab_entry_id = e.controlled_vocab_entry_id AND p.setting_name = ? AND p.locale = ?)
                LEFT JOIN controlled_vocab_entry_settings n ON (n.controlled_vocab_entry_id = e.controlled_vocab_entry_id AND n.setting_name = ? AND n.locale = ?)
            WHERE e.controlled_vocab_id = ?
            ORDER BY e.seq',
            [
                $settingName, AppLocale::getLocale(),           
                $settingName, AppLocale::getPrimaryLocale(),    
                $settingName, '',                               
                (int) $controlledVocabId
            ]
        );

        $returner = [];
        while (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $returner[(int) $row['controlled_vocab_entry_id']] = $this->convertFromDB(
                $row['setting_value'],
                $row['setting_type']
            );
            $result->MoveNext();
        }
        $result->Close();

        return $returner;
    }

    /**
     * Get the ID of the last inserted controlled vocab.
     * @param string $table
     * @param string $id
     * @param bool $callHooks
     * @return int
     */
    public function getInsertId($table = '', $id = '', $callHooks = true) {
        return parent::getInsertId('controlled_vocabs', 'controlled_vocab_id');
    }
    
}
?>