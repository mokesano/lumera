<?php
declare(strict_types=1);

/**
 * @file plugins/generic/objectsForReview/classes/ObjectForReviewPersonDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ObjectForReviewPersonDAO
 * @ingroup plugins_generic_objectsForReview
 * @see ObjectForReviewPerson
 *
 * @brief Operations for retrieving and modifying ObjectForReviewPerson objects.
 */

class ObjectForReviewPersonDAO extends DAO {

    /** @var string Name of parent plugin */
    protected $_parentPluginName;

    /**
     * Constructor.
     * @param string $parentPluginName
     */
    public function __construct($parentPluginName) {
        parent::__construct();
        $this->_parentPluginName = (string) $parentPluginName;
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param string $parentPluginName
     */
    public function ObjectForReviewPersonDAO($parentPluginName) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Retrieve person by ID.
     * @param int $personId
     * @param int|null $objectId
     * @return ObjectForReviewPerson|null
     */
    public function getById($personId, $objectId = null) {
        $params = [(int) $personId];
        if ($objectId !== null) {
            $params[] = (int) $objectId;
        }

        $result = $this->retrieve(
            'SELECT * FROM object_for_review_persons WHERE person_id = ?' . ($objectId !== null ? ' AND object_id = ?' : ''),
            $params
        );

        $returner = null;
        if ($result && !$result->EOF) {
            $returner = $this->_fromRow($result->GetRowAssoc(false));
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Retrieve all persons for the object for review.
     * @param int $objectId
     * @return array
     */
    public function getByObjectForReview($objectId) {
        $result = $this->retrieve(
            'SELECT * FROM object_for_review_persons WHERE object_id = ? ORDER BY seq',
            [(int) $objectId]
        );

        $persons = [];
        if ($result) {
            while (!$result->EOF) {
                $persons[] = $this->_fromRow($result->GetRowAssoc(false));
                $result->MoveNext();
            }
            $result->Close();
        }
        return $persons;
    }

    /**
     * Construct a new data object corresponding to this DAO.
     * @return ObjectForReviewPerson
     */
    public function newDataObject() {
        $ofrPlugin = PluginRegistry::getPlugin('generic', $this->_parentPluginName);
        $ofrPlugin->import('classes.ObjectForReviewPerson');
        return new ObjectForReviewPerson();
    }

    /**
     * Internal function to return an ObjectForReviewPerson object from a row.
     * @param array $row
     * @return ObjectForReviewPerson
     */
    public function _fromRow($row) {
        $person = $this->newDataObject();
        $person->setId((int) $row['person_id']);
        $person->setObjectId((int) $row['object_id']);
        $person->setSequence((float) $row['seq']);
        $person->setRole((string) $row['role']);
        $person->setFirstName((string) $row['first_name']);
        $person->setMiddleName((string) ($row['middle_name'] ?? ''));
        $person->setLastName((string) $row['last_name']);

        // Note: HookRegistry dispatch with references is legacy but preserved for signature compatibility
        $tempPerson = $person;
        $tempRow = $row;
        HookRegistry::dispatch('ObjectForReviewPersonDAO::_fromRow', [&$tempPerson, &$tempRow]);

        return $person;
    }

    /**
     * Insert a new ObjectForReviewPerson.
     * @param ObjectForReviewPerson $person
     * @return int
     */
    public function insertObject($person) {
        $this->update(
            'INSERT INTO object_for_review_persons
                (object_id, seq, role, first_name, middle_name, last_name)
                VALUES
                (?, ?, ?, ?, ?, ?)',
            [
                (int) $person->getObjectId(),
                (float) $person->getSequence(),
                (string) $person->getRole(),
                (string) $person->getFirstName(),
                (string) $person->getMiddleName(),
                (string) $person->getLastName()
            ]
        );
        $person->setId((int) $this->getInsertId());
        return $person->getId();
    }

    /**
     * Update an existing ObjectForReviewPerson.
     * @param ObjectForReviewPerson $person
     * @return bool
     */
    public function updateObject($person) {
        return (bool) $this->update(
            'UPDATE object_for_review_persons
                SET
                    seq = ?,
                    role = ?,
                    first_name = ?,
                    middle_name = ?,
                    last_name = ?
                WHERE person_id = ?',
            [
                (float) $person->getSequence(),
                (string) $person->getRole(),
                (string) $person->getFirstName(),
                (string) $person->getMiddleName(),
                (string) $person->getLastName(),
                (int) $person->getId()
            ]
        );
    }

    /**
     * Delete a person.
     * @param ObjectForReviewPerson $person
     * @return bool
     */
    public function deleteObject($person) {
        return $this->deleteById((int) $person->getId());
    }

    /**
     * Delete a person by ID.
     * @param int $personId
     * @param int|null $objectId
     * @return bool
     */
    public function deleteById($personId, $objectId = null) {
        $params = [(int) $personId];
        if ($objectId !== null) {
            $params[] = (int) $objectId;
        }
        return (bool) $this->update(
            'DELETE FROM object_for_review_persons WHERE person_id = ?' . ($objectId !== null ? ' AND object_id = ?' : ''),
            $params
        );
    }

    /**
     * Delete object for review persons.
     * @param int $objectId
     * @return void
     */
    public function deleteByObjectForReview($objectId) {
        $persons = $this->getByObjectForReview((int) $objectId);
        foreach ($persons as $person) {
            $this->deleteObject($person);
        }
    }

    /**
     * Sequentially renumber object for review's persons in their sequence order.
     * @param int $objectId
     * @return void
     */
    public function resequence($objectId) {
        $result = $this->retrieve(
            'SELECT person_id FROM object_for_review_persons WHERE object_id = ? ORDER BY seq', 
            [(int) $objectId]
        );

        if ($result) {
            for ($i = 1; !$result->EOF; $i++) {
                $row = $result->GetRowAssoc(false);
                $personId = (int) $row['person_id'];
                $this->update(
                    'UPDATE object_for_review_persons SET seq = ? WHERE person_id = ?',
                    [$i, $personId]
                );
                $result->MoveNext();
            }
            $result->Close();
        }
    }

    /**
     * Get the ID of the last inserted person.
     * @param string $table
     * @param string $id
     * @param bool $callHooks
     * @return int
     */
    public function getInsertId($table = '', $id = '', $callHooks = true) {
        return (int) parent::getInsertId('object_for_review_persons', 'person_id', $callHooks);
    }

}
?>