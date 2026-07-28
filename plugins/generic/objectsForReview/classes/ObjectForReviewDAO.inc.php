<?php
declare(strict_types=1);

/**
 * @file plugins/generic/objectsForReview/classes/ObjectForReviewDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ObjectForReviewDAO
 * @ingroup plugins_generic_objectsForReview
 * @see ObjectForReview
 *
 * @brief Operations for retrieving and modifying ObjectForReview objects.
 */

class ObjectForReviewDAO extends DAO {

    /** @var string Name of the parent plugin */
    protected $_parentPluginName;

    /** @var ObjectForReviewPersonDAO Object for review person DAO */
    protected $_objectForReviewPersonDao;

    /**
     * Constructor.
     * @param string $parentPluginName
     */
    public function __construct($parentPluginName) {
        parent::__construct();
        $this->_parentPluginName = (string) $parentPluginName;
        $this->_objectForReviewPersonDao = DAORegistry::getDAO('ObjectForReviewPersonDAO');
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param string $parentPluginName
     */
    public function ObjectForReviewDAO($parentPluginName) {
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
     * Retrieve object for review by object ID.
     * @param int $objectId
     * @param int|null $contextId
     * @return ObjectForReview|null
     */
    public function getById($objectId, $contextId = null) {
        $params = [(int) $objectId];
        if ($contextId !== null) {
            $params[] = (int) $contextId;
        }

        $result = $this->retrieve(
            'SELECT * FROM objects_for_review WHERE object_id = ?' . ($contextId !== null ? ' AND context_id = ?' : ''),
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
     * Construct a new data object corresponding to this DAO.
     * @return ObjectForReview
     */
    public function newDataObject() {
        $ofrPlugin = PluginRegistry::getPlugin('generic', $this->_parentPluginName);
        $ofrPlugin->import('classes.ObjectForReview');
        return new ObjectForReview();
    }

    /**
     * Internal function to return an ObjectForReview object from a row.
     * @param array $row
     * @return ObjectForReview
     */
    public function _fromRow($row) {
        $object = $this->newDataObject();
        $object->setId((int) $row['object_id']);
        $object->setReviewObjectTypeId((int) $row['review_object_type_id']);
        $object->setContextId((int) $row['context_id']);
        $object->setAvailable((int) $row['available']);
        $object->setDateCreated($this->datetimeFromDB($row['date_created']));
        $object->setEditorId(isset($row['editor_id']) ? (int) $row['editor_id'] : null);
        $object->setNotes((string) ($row['notes'] ?? ''));

        $tempObject = $object;
        $tempRow = $row;
        HookRegistry::dispatch('ObjectForReviewDAO::_fromRow', [&$tempObject, &$tempRow]);

        return $object;
    }

    /**
     * Insert a new ObjectForReview.
     * @param ObjectForReview $objectForReview
     * @return int
     */
    public function insertObject($objectForReview) {
        $this->update(
            sprintf('
                INSERT INTO objects_for_review
                    (review_object_type_id,
                    context_id,
                    available,
                    date_created,
                    editor_id,
                    notes)
                VALUES
                    (?, ?, ?, %s, ?, ?)',
                $this->datetimeToDB($objectForReview->getDateCreated())
            ),
            [
                (int) $objectForReview->getReviewObjectTypeId(),
                (int) $objectForReview->getContextId(),
                (int) $objectForReview->getAvailable(),
                $objectForReview->getEditorId() !== null ? (int) $objectForReview->getEditorId() : null,
                (string) $objectForReview->getNotes()
            ]
        );
        $objectForReview->setId((int) $this->getInsertId());

        return $objectForReview->getId();
    }

    /**
     * Update an existing object for review.
     * @param ObjectForReview $objectForReview
     * @return bool
     */
    public function updateObject($objectForReview) {
        return (bool) $this->update(
            sprintf('UPDATE objects_for_review
                SET
                    review_object_type_id = ?,
                    context_id = ?,
                    available = ?,
                    date_created = %s,
                    editor_id = ?,
                    notes = ?
                WHERE object_id = ?',
                $this->datetimeToDB($objectForReview->getDateCreated())
            ),
            [
                (int) $objectForReview->getReviewObjectTypeId(),
                (int) $objectForReview->getContextId(),
                (int) $objectForReview->getAvailable(),
                $objectForReview->getEditorId() !== null ? (int) $objectForReview->getEditorId() : null,
                (string) $objectForReview->getNotes(),
                (int) $objectForReview->getId()
            ]
        );
    }

    /**
     * Delete an object for review.
     * @param ObjectForReview $objectForReview
     * @return void
     */
    public function deleteObject($objectForReview) {
        $this->update(
            'DELETE FROM objects_for_review WHERE object_id = ? AND context_id = ?',
            [(int) $objectForReview->getId(), (int) $objectForReview->getContextId()]
        );
        
        if ($this->getAffectedRows()) {
            // Delete cover image files (for all locales) from the filesystem
            import('classes.file.PublicFileManager');
            $publicFileManager = new PublicFileManager();
            $coverPageSetting = $objectForReview->getCoverPage();
            
            // [WIZDAM FIX] Null-safety check for array access
            if (is_array($coverPageSetting) && isset($coverPageSetting['fileName'])) {
                $publicFileManager->removeJournalFile((int) $objectForReview->getContextId(), (string) $coverPageSetting['fileName']);
            }

            // Delete settings
            /** @var ObjectForReviewSettingsDAO $ofrSettingsDao */
            $ofrSettingsDao = DAORegistry::getDAO('ObjectForReviewSettingsDAO');
            $ofrSettingsDao->deleteSettings((int) $objectForReview->getId());
            
            // Delete persons
            $this->_objectForReviewPersonDao->deleteByObjectForReview((int) $objectForReview->getId());
            
            // Delete assignments
            /** @var ObjectForReviewAssignmentDAO $ofrAssignmentDao */
            $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
            $ofrAssignmentDao->deleteAllByObjectId((int) $objectForReview->getId());
        }
    }

    /**
     * Delete objects for review by context ID.
     * @param int $contextId
     * @return void
     */
    public function deleteByContextId($contextId) {
        $objectsForReview = $this->getAllByContextId((int) $contextId);
        while ($objectForReview = $objectsForReview->next()) {
            $this->deleteObject($objectForReview);
        }
    }

    /**
     * Delete objects for review by review object type ID
     * to be called only when deleting a review object type.
     * @param int $reviewObjectTypeId
     * @return void
     */
    public function deleteByReviewObjectTypeId($reviewObjectTypeId) {
        $objectsForReview = $this->getArrayByReviewObjectTypeId((int) $reviewObjectTypeId);
        foreach ($objectsForReview as $objectForReview) {
            $this->deleteObject($objectForReview);
        }
    }

    /**
     * Retrieve all objects for review in an array for a review object type.
     * @param int $reviewObjectTypeId
     * @return array
     */
    public function getArrayByReviewObjectTypeId($reviewObjectTypeId) {
        $result = $this->retrieve(
            'SELECT * FROM objects_for_review WHERE review_object_type_id = ? ORDER BY object_id',
            [(int) $reviewObjectTypeId]
        );

        $allObjectsForReview = [];
        if ($result) {
            while (!$result->EOF) {
                $objectForReview = $this->_fromRow($result->GetRowAssoc(false));
                $allObjectsForReview[(int) $objectForReview->getId()] = $objectForReview;
                $result->MoveNext();
            }
            $result->Close();
        }
        return $allObjectsForReview;
    }

    /**
     * Retrieve all objects for review matching a particular context ID.
     * @param int $contextId
     * @param int|null $searchType
     * @param string|null $search
     * @param string|null $searchMatch
     * @param int|null $available
     * @param int|null $editorId
     * @param int|null $filterType
     * @param mixed $rangeInfo DBResultRange
     * @param string|null $sortBy
     * @param int $sortDirection
     * @return DAOResultFactory
     */
    public function getAllByContextId($contextId, $searchType = null, $search = null, $searchMatch = null, $available = null, $editorId = null, $filterType = null, $rangeInfo = null, $sortBy = null, $sortDirection = SORT_DIRECTION_ASC) {
        $ofrPlugin = PluginRegistry::getPlugin('generic', $this->_parentPluginName);
        $ofrPlugin->import('classes.ReviewObjectMetadata');

        $params = [];
        $sortColumn = '';
        $sortSQL = '';
        
        switch ($sortBy) {
            case 'title':
                $sortColumn .= ', ofrs.setting_value AS ofr_title';
                $sortSQL .= ' LEFT JOIN object_for_review_settings ofrs ON (ofr.object_id = ofrs.object_id)
                    JOIN review_object_metadata rom ON (rom.metadata_id = ofrs.review_object_metadata_id AND rom.metadata_key = \'' . REVIEW_OBJECT_METADATA_KEY_TITLE . '\')';
                break;
            case 'type':
                $sortColumn .= ', COALESCE(rtl.setting_value, rtpl.setting_value) AS ofr_type_name';
                $sortSQL .= ' LEFT JOIN review_object_types rt ON (ofr.review_object_type_id = rt.type_id)
                    LEFT JOIN review_object_type_settings rtl ON (ofr.review_object_type_id = rtl.type_id AND rtl.setting_name = \'name\' AND rtl.locale = ?)
                    LEFT JOIN review_object_type_settings rtpl ON (ofr.review_object_type_id = rtpl.type_id AND rtpl.setting_name = \'name\' AND rtpl.locale = ?)';
                $params[] = AppLocale::getLocale();
                $params[] = AppLocale::getPrimaryLocale();
                // intentional fall-through
            case 'editor':
                $sortColumn .= ', COALESCE(ue.initials, CONCAT(SUBSTRING(ue.first_name FROM 1 FOR 1), SUBSTRING(ue.last_name FROM 1 FOR 1))) AS ed_initials';
                $sortSQL .= ' LEFT JOIN users ue ON (ofr.editor_id = ue.user_id)';
                break;
        }

        $sql = "SELECT DISTINCT ofr.*$sortColumn
            FROM objects_for_review ofr
            $sortSQL";

        switch ($searchType) {
            case OFR_FIELD_TITLE:
                if ($sortBy !== 'title') {
                    $sql .= ' LEFT JOIN object_for_review_settings ofrs ON (ofr.object_id = ofrs.object_id)
                        JOIN review_object_metadata rom ON (rom.metadata_id = ofrs.review_object_metadata_id AND rom.metadata_key = \'' . REVIEW_OBJECT_METADATA_KEY_TITLE . '\')';
                }
                $sql .= ' WHERE LOWER(ofrs.setting_value) ' . ($searchMatch === 'is' ? '=' : 'LIKE') . ' LOWER(?)';
                $params[] = $searchMatch === 'is' ? (string) $search : '%' . (string) $search . '%';
                break;
            case OFR_FIELD_ABSTRACT:
                $sql .= ' LEFT JOIN object_for_review_settings ofrsa ON (ofr.object_id = ofrsa.object_id)
                    LEFT JOIN review_object_metadata roma ON (roma.metadata_id = ofrsa.review_object_metadata_id)
                    WHERE roma.metadata_key = \'' . REVIEW_OBJECT_METADATA_KEY_ABSTRACT . '\' AND LOWER(ofrsa.setting_value) ' . ($searchMatch === 'is' ? '=' : 'LIKE') . ' LOWER(?)';
                $params[] = $searchMatch === 'is' ? (string) $search : '%' . (string) $search . '%';
                break;
            default:
                $searchType = null;
        }

        if (empty($searchType)) {
            $sql .= ' WHERE';
        } else {
            $sql .= ' AND';
        }

        if ($available !== null && $available !== '') {
            $sql .= ' ofr.available = 1 AND';
        }

        if ($editorId !== null && $editorId !== '') {
            $sql .= ' ofr.editor_id = ? AND';
            $params[] = (int) $editorId;
        }

        if ($filterType !== null && $filterType !== '') {
            $sql .= ' ofr.review_object_type_id = ? AND';
            $params[] = (int) $filterType;
        }

        $sql .= " ofr.context_id = ?";
        $params[] = (int) $contextId;

        if ($sortBy) {
            $sql .= ' ORDER BY ' . $this->getSortMapping($sortBy) . ' ' . $this->getDirectionMapping($sortDirection);
        }

        $result = $this->retrieveRange($sql, $params, $rangeInfo);
        return new DAOResultFactory($result, $this, '_fromRow');
    }

    /**
     * Check if the review object type exists with the specified ID.
     * @param int $objectId
     * @param int|null $contextId
     * @return bool
     */
    public function objectForReviewExists($objectId, $contextId = null) {
        $params = [(int) $objectId];
        if ($contextId !== null) {
            $params[] = (int) $contextId;
        }

        $result = $this->retrieve(
            'SELECT COUNT(*) AS count FROM objects_for_review WHERE object_id = ?' . ($contextId !== null ? ' AND context_id = ?' : ''),
            $params
        );

        $returner = false;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = ((int) ($row['count'] ?? 0)) > 0;
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Get the ID of the last inserted object for review.
     * @param string $table
     * @param string $id
     * @param bool $callHooks
     * @return int
     */
    public function getInsertId($table = '', $id = '', $callHooks = true) {
        return (int) parent::getInsertId('objects_for_review', 'object_id', $callHooks);
    }

    /**
     * Map a column heading value to a database value for sorting.
     * @param string $heading
     * @return string|null
     */
    public static function getSortMapping($heading) {
        switch ($heading) {
            case 'title': return 'ofr_title';
            case 'created': return 'ofr.date_created';
            case 'editor': return 'ed_initials';
            case 'status': return 'ofr.available';
            case 'type': return 'ofr_type_name';
            default: return null;
        }
    }

}
?>