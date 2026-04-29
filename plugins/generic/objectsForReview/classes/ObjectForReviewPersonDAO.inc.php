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
 * * MODERNIZED FOR WIZDAM FORK
 */

class ObjectForReviewPersonDAO extends DAO {

    /** @var string Name of parent plugin */
    public $parentPluginName;

    /**
     * Constructor
     */
    public function __construct($parentPluginName){
        parent::__construct();
        $this->parentPluginName = $parentPluginName;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ObjectForReviewPersonDAO($parentPluginName) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::ObjectForReviewPersonDAO(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($parentPluginName);
    }

    /**
     * Retrieve person by ID.
     * @param $personId int
     * @return ObjectForReviewPerson
     */
    public function getById($personId, $objectId = null) {
        $params = array((int) $personId);
        if ($objectId) $params[] = (int) $objectId;

        $result = $this->retrieve(
            'SELECT * FROM object_for_review_persons WHERE person_id = ?'. ($objectId ? ' AND object_id = ?' : ''),
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
     * Retrieve all persons for the object for review.
     * @param $objectId int
     * @return array ObjectForReviewPersons ordered by sequence
     */
    public function getByObjectForReview($objectId) {
        $result = $this->retrieve(
            'SELECT * FROM object_for_review_persons WHERE object_id = ? ORDER BY seq',
            (int) $objectId
        );

        $persons = array();
        while (!$result->EOF) {
            $persons[] = $this->_fromRow($result->GetRowAssoc(false));
            $result->MoveNext();
        }
        $result->Close();
        return $persons;
    }

    /**
     * Construct a new data object corresponding to this DAO.
     * @return ObjectForReviewPerson
     */
    public function newDataObject() {
        $ofrPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
        $ofrPlugin->import('classes.ObjectForReviewPerson');
        return new ObjectForReviewPerson();
    }

    /**
     * Internal function to return an ObjectForReviewPerson object from a row.
     * @param $row array
     * @return ObjectForReviewPerson
     */
    public function _fromRow($row) {
        $person = $this->newDataObject();
        $person->setId($row['person_id']);
        $person->setObjectId($row['object_id']);
        $person->setSequence($row['seq']);
        $person->setRole($row['role']);
        $person->setFirstName($row['first_name']);
        $person->setMiddleName($row['middle_name']);
        $person->setLastName($row['last_name']);

        HookRegistry::dispatch('ObjectForReviewPersonDAO::_fromRow', array(&$person, &$row));

        return $person;
    }

    /**
     * Insert a new ObjectForReviewPerson.
     * @param $person ObjectForReviewPerson
     * @return int
     */
    public function insertObject($person) {
        $this->update(
            'INSERT INTO object_for_review_persons
                (object_id, seq, role, first_name, middle_name, last_name)
                VALUES
                (?, ?, ?, ?, ?, ?)',
            array(
                (int) $person->getObjectId(),
                (float) $person->getSequence(),
                $person->getRole(),
                $person->getFirstName(),
                $person->getMiddleName() . '', // make non-null
                $person->getLastName()
            )
        );
        $person->setId($this->getInsertId());
        return $person->getId();
    }

    /**
     * Update an existing ObjectForReviewPerson.
     * @param $person ObjectForReviewPerson
     * @return boolean
     */
    public function updateObject($person) {
        $returner = $this->update(
            'UPDATE object_for_review_persons
                SET
                    seq = ?,
                    role = ?,
                    first_name = ?,
                    middle_name = ?,
                    last_name = ?
                WHERE person_id = ?',
            array(
                (float) $person->getSequence(),
                $person->getRole(),
                $person->getFirstName(),
                $person->getMiddleName() . '', // make non-null
                $person->getLastName(),
                (int) $person->getId()
            )
        );
        return $returner;
    }

    /**
     * Delete a person.
     * @param $person ObjectForReviewPerson
     */
    public function deleteObject($person) {
        return $this->deleteById($person->getId());
    }

    /**
     * Delete a person by ID.
     * @param $personId int
     * @param $objectId int (optional)
     */
    public function deleteById($personId, $objectId = null) {
        $params = array((int) $personId);
        if ($objectId) $params[] = (int) $objectId;
        $returner = $this->update(
            'DELETE FROM object_for_review_persons WHERE person_id = ?' . ($objectId ? ' AND object_id = ?' : ''),
            $params
        );
    }

    /**
     * Delete object for review persons.
     * @param $objectId int
     */
    public function deleteByObjectForReview($objectId) {
        $persons = $this->getByObjectForReview($objectId);
        foreach ($persons as $person) {
            $this->deleteObject($person);
        }
    }

    /**
     * Sequentially renumber object for review's persons in their sequence order.
     * @param $objectId int
     */
    public function resequence($objectId) {
        $result = $this->retrieve(
            'SELECT person_id FROM object_for_review_persons WHERE object_id = ? ORDER BY seq', (int) $objectId
        );

        for ($i=1; !$result->EOF; $i++) {
            list($personId) = $result->fields;
            $this->update(
                'UPDATE object_for_review_persons SET seq = ? WHERE person_id = ?',
                array(
                    $i,
                    $personId
                )
            );

            $result->MoveNext();
        }
        $result->Close();
    }

    /**
     * Get the ID of the last inserted person.
     * @return int
     */
    public function getInsertId($table = '', $id = '', $callHooks = true) {
        return parent::getInsertId('object_for_review_persons', 'person_id', $callHooks);
    }

}

?>