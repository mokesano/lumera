<?php
declare(strict_types=1);

/**
 * @file plugins/generic/objectsForReview/classes/ReviewObjectTypeDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewObjectTypeDAO
 * @ingroup plugins_generic_objectsForReview
 * @see ReviewObjectType
 *
 * @brief Operations for retrieving and modifying ReviewObjectType objects.
 */

class ReviewObjectTypeDAO extends DAO {

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
    public function ReviewObjectTypeDAO($parentPluginName) {
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
     * Retrieve a review object type by ID.
     * @param int $typeId
     * @param int|null $contextId
     * @return ReviewObjectType|null
     */
    public function getById($typeId, $contextId = null) {
        $params = [(int) $typeId];
        if ($contextId !== null) {
            $params[] = (int) $contextId;
        }

        $result = $this->retrieve(
            'SELECT * FROM review_object_types WHERE type_id = ?' . ($contextId !== null ? ' AND context_id = ?' : ''),
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
     * Retrieve a review object type by key.
     * @param string $typeKey
     * @param int|null $contextId
     * @return ReviewObjectType|null
     */
    public function getByKey($typeKey, $contextId = null) {
        $params = [(string) $typeKey];
        if ($contextId !== null) {
            $params[] = (int) $contextId;
        }

        $result = $this->retrieve(
            'SELECT * FROM review_object_types WHERE type_key = ?' . ($contextId !== null ? ' AND context_id = ?' : ''),
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
     * @return ReviewObjectType
     */
    public function newDataObject() {
        $ofrPlugin = PluginRegistry::getPlugin('generic', $this->_parentPluginName);
        $ofrPlugin->import('classes.ReviewObjectType');
        return new ReviewObjectType();
    }

    /**
     * Internal function to return a ReviewObjectType object from a row.
     * @param array $row
     * @return ReviewObjectType
     */
    public function _fromRow($row) {
        $reviewObjectType = $this->newDataObject();
        $reviewObjectType->setId((int) $row['type_id']);
        $reviewObjectType->setContextId((int) $row['context_id']);
        $reviewObjectType->setActive((int) $row['is_active']);
        $reviewObjectType->setKey((string) $row['type_key']);

        $this->getDataObjectSettings('review_object_type_settings', 'type_id', (int) $row['type_id'], $reviewObjectType);

        // Note: HookRegistry dispatch with references is legacy but preserved for signature compatibility
        $tempObjectType = $reviewObjectType;
        $tempRow = $row;
        HookRegistry::dispatch('ReviewObjectTypeDAO::_fromRow', [&$tempObjectType, &$tempRow]);

        return $reviewObjectType;
    }

    /**
     * Get the list of fields for which data can be localized.
     * @return array
     */
    public function getLocaleFieldNames() {
        return ['name', 'description'];
    }

    /**
     * Update the localized fields for this table.
     * @param ReviewObjectType $reviewObjectType
     * @return void
     */
    public function updateLocaleFields($reviewObjectType) {
        $this->updateDataObjectSettings('review_object_type_settings', $reviewObjectType, [
            'type_id' => (int) $reviewObjectType->getId()
        ]);
    }

    /**
     * Insert a new review object type.
     * @param ReviewObjectType $reviewObjectType
     * @return int
     */
    public function insertObject($reviewObjectType) {
        $this->update(
            'INSERT INTO review_object_types
                (context_id, is_active, type_key)
                VALUES
                (?, ?, ?)',
            [
                (int) $reviewObjectType->getContextId(),
                (int) $reviewObjectType->getActive() ? 1 : 0,
                (string) $reviewObjectType->getKey()
            ]
        );
        $reviewObjectType->setId((int) $this->getInsertId());
        $this->updateLocaleFields($reviewObjectType);
        return $reviewObjectType->getId();
    }

    /**
     * Update an existing review object type.
     * @param ReviewObjectType $reviewObjectType
     * @return bool
     */
    public function updateObject($reviewObjectType) {
        $returner = $this->update(
            'UPDATE review_object_types
                SET
                    context_id = ?,
                    is_active = ?,
                    type_key = ?
                WHERE type_id = ?',
            [
                (int) $reviewObjectType->getContextId(),
                (int) $reviewObjectType->getActive() ? 1 : 0,
                (string) $reviewObjectType->getKey(),
                (int) $reviewObjectType->getId()
            ]
        );
        $this->updateLocaleFields($reviewObjectType);
        return (bool) $returner;
    }

    /**
     * Delete a review object type.
     * @param ReviewObjectType $reviewObjectType
     * @return bool
     */
    public function deleteObject($reviewObjectType) {
        return $this->deleteById((int) $reviewObjectType->getId());
    }

    /**
     * Delete a review object type by ID.
     * @param int $typeId
     * @param int|null $contextId
     * @return void
     */
    public function deleteById($typeId, $contextId = null) {
        $params = [(int) $typeId];
        if ($contextId !== null) {
            $params[] = (int) $contextId;
        }

        $this->update(
            'DELETE FROM review_object_types WHERE type_id = ?' . ($contextId !== null ? ' AND context_id = ?' : ''),
            $params
        );
        
        if ($this->getAffectedRows()) {
            $this->update(
                'DELETE FROM review_object_type_settings WHERE type_id = ?',
                [(int) $typeId]
            );
            
            /** @var ReviewObjectMetadataDAO $reviewObjectMetadataDao */
            $reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
            $reviewObjectMetadataDao->deleteByReviewObjectTypeId((int) $typeId);
            
            /** @var ObjectForReviewDAO $ofrDao */
            $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
            $ofrDao->deleteByReviewObjectTypeId((int) $typeId);
        }
    }

    /**
     * Delete all review object types by context ID.
     * @param int $contextId
     * @return void
     */
    public function deleteByContextId($contextId) {
        $reviewObjectTypes = $this->getByContextId((int) $contextId);
        while ($reviewObjectType = $reviewObjectTypes->next()) {
            $this->deleteById((int) $reviewObjectType->getId());
        }
    }

    /**
     * Get all review object types by journal ID.
     * @param int $contextId
     * @param mixed $rangeInfo DBResultRange (optional)
     * @return DAOResultFactory containing matching ReviewObjectTypes
     */
    public function getByContextId($contextId, $rangeInfo = null) {
        $params = [(int) $contextId];

        $result = $this->retrieveRange(
            'SELECT * FROM review_object_types WHERE context_id = ?',
            $params, 
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_fromRow');
    }

    /**
     * Retrieve review object types IDs for a context, sorted alphabetically.
     * @param int $contextId
     * @return array
     */
    public function getTypeIdsAlphabetizedByContext($contextId) {
        $params = [
            'name', AppLocale::getLocale(),
            'name', AppLocale::getPrimaryLocale(),
            (int) $contextId
        ];

        $result = $this->retrieve(
            'SELECT t.type_id, t.is_active, t.type_key,
                COALESCE(nl.setting_value, npl.setting_value) AS type_name
            FROM review_object_types t
                LEFT JOIN review_object_type_settings nl ON (nl.type_id = t.type_id AND nl.setting_name = ? AND nl.locale = ?)
                LEFT JOIN review_object_type_settings npl ON (npl.type_id = t.type_id AND npl.setting_name = ? AND npl.locale = ?)
            WHERE t.context_id = ? ORDER BY type_name',
            $params
        );

        $types = [];
        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $types[] = [
                    'typeId' => (int) $row['type_id'], 
                    'typeKey' => (string) $row['type_key'], 
                    'typeName' => (string) $row['type_name'], 
                    'typeActive' => (int) $row['is_active']
                ];
                $result->MoveNext();
            }
            $result->Close();
        }
        return $types;
    }

    /**
     * Check if review object type exists with the specified ID.
     * @param int $typeId
     * @param int|null $contextId
     * @return bool
     */
    public function reviewObjectTypeExists($typeId, $contextId = null) {
        $params = [(int) $typeId];
        if ($contextId !== null) {
            $params[] = (int) $contextId;
        }

        $result = $this->retrieve(
            'SELECT COUNT(*) AS count FROM review_object_types WHERE type_id = ?' . ($contextId !== null ? ' AND context_id = ?' : ''),
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
     * Get all installed default types i.e. their keys.
     * @param int $contextId
     * @return array
     */
    public function getTypeKeys($contextId) {
        $params = [(int) $contextId];
        $result = $this->retrieve(
            'SELECT type_key FROM review_object_types WHERE context_id = ?',
            $params
        );

        $typeKeys = [];
        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $typeKeys[] = (string) $row['type_key'];
                $result->MoveNext();
            }
            $result->Close();
        }
        return $typeKeys;
    }

    /**
     * Get the ID of the last inserted review object type.
     * @param string $table
     * @param string $id
     * @param bool $callHooks
     * @return int
     */
    public function getInsertId($table = '', $id = '', $callHooks = true) {
        return (int) parent::getInsertId('review_object_types', 'type_id', $callHooks);
    }

}
?>