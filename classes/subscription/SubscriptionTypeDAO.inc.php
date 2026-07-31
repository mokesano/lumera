<?php
declare(strict_types=1);

/**
 * @file classes/subscription/SubscriptionTypeDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubscriptionTypeDAO
 * @ingroup subscription
 * @see SubscriptionType
 *
 * @brief Operations for retrieving and modifying SubscriptionType objects.
 */

import('classes.subscription.SubscriptionType');

class SubscriptionTypeDAO extends DAO {

    /**
     * Retrieve a subscription type by ID.
     * @param int $typeId
     * @return SubscriptionType|null
     */
    public function getSubscriptionType($typeId) {
        $result = $this->retrieve(
            'SELECT * FROM subscription_types WHERE type_id = ?',
            [(int) $typeId]
        );

        $returner = null;
        if ($result && !$result->EOF) {
            $returner = $this->_returnSubscriptionTypeFromRow($result->GetRowAssoc(false));
        }
        if ($result) {
            $result->Close();
        }

        return $returner;
    }

    /**
     * Retrieve subscription type journal ID by ID.
     * @param int $typeId
     * @return int|false
     */
    public function getSubscriptionTypeJournalId($typeId) {
        $result = $this->retrieve(
            'SELECT journal_id AS journal_id FROM subscription_types WHERE type_id = ?',
            [(int) $typeId]
        );

        $returner = false;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = (int) $row['journal_id'];
        }
        if ($result) {
            $result->Close();
        }

        return $returner;
    }

    /**
     * Retrieve subscription type name by ID.
     * @param int $typeId
     * @return string|false
     */
    public function getSubscriptionTypeName($typeId) {
        $result = $this->retrieve(
            'SELECT COALESCE(l.setting_value, p.setting_value) AS name 
             FROM subscription_type_settings l 
             LEFT JOIN subscription_type_settings p ON (p.type_id = ? AND p.setting_name = ? AND p.locale = ?) 
             WHERE l.type_id = ? AND l.setting_name = ? AND l.locale = ?', 
            [
                (int) $typeId, 'name', AppLocale::getLocale(),
                (int) $typeId, 'name', AppLocale::getPrimaryLocale()
            ]
        );

        $returner = false;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = (string) $row['name'];
        }
        if ($result) {
            $result->Close();
        }

        return $returner;
    }

    /**
     * Retrieve institutional flag by ID.
     * @param int $typeId
     * @return int|false
     */
    public function getSubscriptionTypeInstitutional($typeId) {
        $result = $this->retrieve(
            'SELECT institutional AS institutional FROM subscription_types WHERE type_id = ?',
            [(int) $typeId]
        );

        $returner = false;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = (int) $row['institutional'];
        }
        if ($result) {
            $result->Close();
        }

        return $returner;
    }

    /**
     * Retrieve membership flag by ID.
     * @param int $typeId
     * @return int|false
     */
    public function getSubscriptionTypeMembership($typeId) {
        $result = $this->retrieve(
            'SELECT membership AS membership FROM subscription_types WHERE type_id = ?',
            [(int) $typeId]
        );

        $returner = false;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = (int) $row['membership'];
        }
        if ($result) {
            $result->Close();
        }

        return $returner;
    }

    /**
     * Retrieve nonExpiring flag by ID.
     * @param int $typeId
     * @return int|false
     */
    public function getSubscriptionTypeNonExpiring($typeId) {
        $result = $this->retrieve(
            'SELECT non_expiring AS non_expiring FROM subscription_types WHERE type_id = ?',
            [(int) $typeId]
        );

        $returner = false;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = (int) $row['non_expiring'];
        }
        if ($result) {
            $result->Close();
        }

        return $returner;
    }

    /**
     * Retrieve public display flag by ID.
     * @param int $typeId
     * @return int|false
     */
    public function getSubscriptionTypeDisablePublicDisplay($typeId) {
        $result = $this->retrieve(
            'SELECT disable_public_display AS disable_public_display FROM subscription_types WHERE type_id = ?',
            [(int) $typeId]
        );

        $returner = false;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = (int) $row['disable_public_display'];
        }
        if ($result) {
            $result->Close();
        }

        return $returner;
    }

    /**
     * Check if a subscription type exists with the given type id for a journal.
     * @param int $typeId
     * @param int $journalId
     * @return bool
     */
    public function subscriptionTypeExistsByTypeId($typeId, $journalId) {
        $result = $this->retrieve(
            'SELECT COUNT(*) AS count FROM subscription_types WHERE type_id = ? AND journal_id = ?',
            [(int) $typeId, (int) $journalId]
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
     * Internal function to return a SubscriptionType object from a row.
     * @param array $row
     * @return SubscriptionType
     */
    public function _returnSubscriptionTypeFromRow($row) {
        $subscriptionType = new SubscriptionType();
        $subscriptionType->setTypeId((int) $row['type_id']);
        $subscriptionType->setJournalId((int) $row['journal_id']);
        $subscriptionType->setCost((float) $row['cost']);
        $subscriptionType->setCurrencyCodeAlpha((string) $row['currency_code_alpha']);
        $subscriptionType->setNonExpiring((int) $row['non_expiring']);
        $subscriptionType->setDuration((int) $row['duration']);
        $subscriptionType->setFormat((int) $row['format']);
        $subscriptionType->setInstitutional((int) $row['institutional']);
        $subscriptionType->setMembership((int) $row['membership']);
        $subscriptionType->setDisablePublicDisplay((int) $row['disable_public_display']);
        $subscriptionType->setSequence((float) $row['seq']);

        $this->getDataObjectSettings('subscription_type_settings', 'type_id', (int) $row['type_id'], $subscriptionType);

        $tempType = $subscriptionType;
        $tempRow = $row;
        HookRegistry::dispatch('SubscriptionTypeDAO::_returnSubscriptionTypeFromRow', [&$tempType, &$tempRow]);

        return $subscriptionType;
    }

    /**
     * Get the list of field names for which localized data is used.
     * @return array
     */
    public function getLocaleFieldNames() {
        return ['name', 'description'];
    }

    /**
     * Update the localized settings for this object.
     * @param SubscriptionType $subscriptionType
     * @return void
     */
    public function updateLocaleFields($subscriptionType) {
        $this->updateDataObjectSettings('subscription_type_settings', $subscriptionType, [
            'type_id' => (int) $subscriptionType->getTypeId()
        ]);
    }

    /**
     * Insert a new SubscriptionType.
     * @param SubscriptionType $subscriptionType
     * @return int
     */
    public function insertSubscriptionType($subscriptionType) {
        $this->update(
            'INSERT INTO subscription_types
                (journal_id, cost, currency_code_alpha, non_expiring, duration, format, institutional, membership, disable_public_display, seq)
                VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                (int) $subscriptionType->getJournalId(),
                (float) $subscriptionType->getCost(),
                (string) $subscriptionType->getCurrencyCodeAlpha(),
                (int) $subscriptionType->getNonExpiring(),
                (int) $subscriptionType->getDuration(),
                (int) $subscriptionType->getFormat(),
                (int) $subscriptionType->getInstitutional(),
                (int) $subscriptionType->getMembership(),
                (int) $subscriptionType->getDisablePublicDisplay(),
                (float) $subscriptionType->getSequence()
            ]
        );

        $subscriptionType->setTypeId($this->getInsertSubscriptionTypeId());
        $this->updateLocaleFields($subscriptionType);
        return (int) $subscriptionType->getTypeId();
    }

    /**
     * Update an existing subscription type.
     * @param SubscriptionType $subscriptionType
     * @return bool
     */
    public function updateSubscriptionType($subscriptionType) {
        $returner = $this->update(
            'UPDATE subscription_types
                SET
                    journal_id = ?,
                    cost = ?,
                    currency_code_alpha = ?,
                    non_expiring = ?,
                    duration = ?,
                    format = ?,
                    institutional = ?,
                    membership = ?,
                    disable_public_display = ?,
                    seq = ?
                WHERE type_id = ?',
            [
                (int) $subscriptionType->getJournalId(),
                (float) $subscriptionType->getCost(),
                (string) $subscriptionType->getCurrencyCodeAlpha(),
                (int) $subscriptionType->getNonExpiring(),
                (int) $subscriptionType->getDuration(),
                (int) $subscriptionType->getFormat(),
                (int) $subscriptionType->getInstitutional(),
                (int) $subscriptionType->getMembership(),
                (int) $subscriptionType->getDisablePublicDisplay(),
                (float) $subscriptionType->getSequence(),
                (int) $subscriptionType->getTypeId()
            ]
        );
        $this->updateLocaleFields($subscriptionType);
        return (bool) $returner;
    }

    /**
     * Delete a subscription type.
     * @param SubscriptionType $subscriptionType
     * @return bool
     */
    public function deleteSubscriptionType($subscriptionType) {
        return $this->deleteSubscriptionTypeById((int) $subscriptionType->getTypeId());
    }

    /**
     * Delete a subscription type by ID. Note that all subscriptions with this
     * type ID are also deleted.
     * @param int $typeId
     * @return bool
     */
    public function deleteSubscriptionTypeById($typeId) {
        $institutional = $this->getSubscriptionTypeInstitutional((int) $typeId);

        if ($institutional) {
            /** @var InstitutionalSubscriptionDAO $subscriptionDao */
            $subscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO');
        } else {
            /** @var IndividualSubscriptionDAO $subscriptionDao */
            $subscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
        }
        $returner = $subscriptionDao->deleteSubscriptionsByTypeId((int) $typeId);

        if ($returner) {
            $returner = $this->update('DELETE FROM subscription_types WHERE type_id = ?', [(int) $typeId]);
        }

        if ($returner) {
            $this->update('DELETE FROM subscription_type_settings WHERE type_id = ?', [(int) $typeId]);
        }

        return (bool) $returner;
    }

    /**
     * Delete subscription types by journal ID. Note that all subscriptions with
     * corresponding types are also deleted.
     * @param int $journalId
     * @return bool
     */
    public function deleteSubscriptionTypesByJournal($journalId) {
        $result = $this->retrieve(
            'SELECT type_id AS type_id FROM subscription_types WHERE journal_id = ?',
            [(int) $journalId]
        );

        $returner = false;

        if ($result && !$result->EOF) {
            $returner = true;
            while (!$result->EOF && $returner) {
                $row = $result->GetRowAssoc(false);
                $typeId = (int) $row['type_id'];
                $returner = $this->deleteSubscriptionTypeById($typeId);
                $result->MoveNext();
            }
        }

        if ($result) {
            $result->Close();
        }

        return $returner;
    }

    /**
     * Retrieve subscription types matching a particular journal ID.
     * @param int $journalId
     * @param mixed $rangeInfo
     * @return DAOResultFactory containing matching SubscriptionTypes
     */
    public function getSubscriptionTypesByJournalId($journalId, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM subscription_types WHERE journal_id = ? ORDER BY seq',
            [(int) $journalId],
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnSubscriptionTypeFromRow');
    }

    /**
     * Retrieve subscription types matching a particular journal ID and institutional flag.
     * @param int $journalId
     * @param bool $institutional
     * @param bool|null $disablePublicDisplay
     * @param mixed $rangeInfo
     * @return DAOResultFactory containing matching SubscriptionTypes
     */
    public function getSubscriptionTypesByInstitutional($journalId, $institutional = false, $disablePublicDisplay = null, $rangeInfo = null) {
        $institutionalInt = $institutional ? 1 : 0;

        if ($disablePublicDisplay === null) {
            $disablePublicDisplaySql = '';
        } elseif ($disablePublicDisplay) {
            $disablePublicDisplaySql = 'AND disable_public_display = 1';
        } else {
            $disablePublicDisplaySql = 'AND disable_public_display = 0';
        }

        $result = $this->retrieveRange(
            'SELECT * FROM subscription_types WHERE journal_id = ? AND institutional = ? ' . $disablePublicDisplaySql . ' ORDER BY seq',
            [(int) $journalId, $institutionalInt],
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnSubscriptionTypeFromRow');
    }

    /**
     * Check if at least one subscription type exists for a given journal by institutional flag.
     * @param int $journalId
     * @param bool $institutional
     * @return bool
     */
    public function subscriptionTypesExistByInstitutional($journalId, $institutional = false) {
        $institutionalInt = $institutional ? 1 : 0;

        $result = $this->retrieve(
            'SELECT COUNT(*) AS count FROM subscription_types st WHERE st.journal_id = ? AND st.institutional = ?',
            [(int) $journalId, $institutionalInt]
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
     * Get the ID of the last inserted subscription type.
     * @return int
     */
    public function getInsertSubscriptionTypeId() {
        return (int) $this->getInsertId('subscription_types', 'type_id');
    }

    /**
     * Sequentially renumber subscription types in their sequence order.
     * @param int $journalId
     * @return void
     */
    public function resequenceSubscriptionTypes($journalId) {
        $result = $this->retrieve(
            'SELECT type_id AS type_id FROM subscription_types WHERE journal_id = ? ORDER BY seq',
            [(int) $journalId]
        );

        if ($result) {
            for ($i = 1; !$result->EOF; $i++) {
                $row = $result->GetRowAssoc(false);
                $subscriptionTypeId = (int) $row['type_id'];
                $this->update(
                    'UPDATE subscription_types SET seq = ? WHERE type_id = ?',
                    [$i, $subscriptionTypeId]
                );
                $result->MoveNext();
            }
            $result->Close();
        }
    }
    
}
?>