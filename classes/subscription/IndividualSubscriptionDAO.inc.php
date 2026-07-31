<?php
declare(strict_types=1);

/**
 * @file classes/subscription/IndividualSubscriptionDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IndividualSubscriptionDAO
 * @ingroup subscription
 * @see IndividualSubscription
 *
 * @brief Operations for retrieving and modifying IndividualSubscription objects.
 */

import('classes.subscription.SubscriptionDAO');
import('classes.subscription.IndividualSubscription');

class IndividualSubscriptionDAO extends SubscriptionDAO {

    /**
     * Retrieve an individual subscription by subscription ID.
     * @param int $subscriptionId
     * @return IndividualSubscription|null
     */
    public function getSubscription($subscriptionId) {
        $result = $this->retrieve(
            'SELECT s.*
            FROM subscriptions s, subscription_types st
            WHERE s.type_id = st.type_id
            AND st.institutional = 0
            AND s.subscription_id = ?',
            [(int) $subscriptionId]
        );

        $returner = null;
        if ($result && !$result->EOF) {
            $returner = $this->_returnSubscriptionFromRow($result->GetRowAssoc(false));
        }
        if ($result) {
            $result->Close();
        }

        return $returner;
    }

    /**
     * Retrieve individual subscription by user ID for journal.
     * @param int $userId
     * @param int $journalId
     * @return IndividualSubscription|null
     */
    public function getSubscriptionByUserForJournal($userId, $journalId) {
        $result = $this->retrieve(
            'SELECT s.*
            FROM subscriptions s, subscription_types st
            WHERE s.type_id = st.type_id
            AND st.institutional = 0
            AND s.user_id = ?
            AND s.journal_id = ?',
            [(int) $userId, (int) $journalId]
        );

        $returner = null;
        if ($result && !$result->EOF) {
            $returner = $this->_returnSubscriptionFromRow($result->GetRowAssoc(false));
        }
        if ($result) {
            $result->Close();
        }

        return $returner;
    }

    /**
     * Retrieve individual subscriptions by user ID.
     * @param int $userId
     * @param mixed $rangeInfo
     * @return DAOResultFactory containing IndividualSubscriptions
     */
    public function getSubscriptionsByUser($userId, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT s.*
            FROM subscriptions s, subscription_types st
            WHERE s.type_id = st.type_id
            AND st.institutional = 0
            AND s.user_id = ?',
            [(int) $userId],
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnSubscriptionFromRow');
    }

    /**
     * Retrieve individual subscription ID by user ID.
     * @param int $userId
     * @param int $journalId
     * @return int
     */
    public function getSubscriptionIdByUser($userId, $journalId) {
        $result = $this->retrieve(
            'SELECT s.subscription_id AS subscription_id
            FROM subscriptions s, subscription_types st
            WHERE s.type_id = st.type_id
            AND st.institutional = 0
            AND s.user_id = ?
            AND s.journal_id = ?',
            [(int) $userId, (int) $journalId]
        );

        $returner = 0;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = (int) $row['subscription_id'];
        }
        if ($result) {
            $result->Close();
        }

        return $returner;
    }

    /**
     * Return number of individual subscriptions with given status for journal.
     * @param int $journalId
     * @param int|null $status
     * @return int
     */
    public function getStatusCount($journalId, $status = null) {
        $params = [(int) $journalId];
        if ($status !== null) {
            $params[] = (int) $status;
        }

        $result = $this->retrieve(
            'SELECT COUNT(*) AS count
            FROM subscriptions s, subscription_types st
            WHERE s.type_id = st.type_id AND
                st.institutional = 0 AND
                s.journal_id = ?' . ($status !== null ? ' AND s.status = ?' : ''),
            $params
        );

        $returner = 0;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = (int) ($row['count'] ?? 0);
        }
        if ($result) {
            $result->Close();
        }

        return $returner;
    }

    /**
     * Get the number of individual subscriptions for a particular journal.
     * @param int $journalId
     * @return int
     */
    public function getSubscribedUserCount($journalId) {
        return $this->getStatusCount($journalId);
    }

    /**
     * Check if an individual subscription exists for a given subscription ID.
     * @param int $subscriptionId
     * @return bool
     */
    public function subscriptionExists($subscriptionId) {
        $result = $this->retrieve(
            'SELECT COUNT(*) AS count
            FROM subscriptions s, subscription_types st
            WHERE s.type_id = st.type_id
            AND st.institutional = 0
            AND s.subscription_id = ?',
            [(int) $subscriptionId]
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
     * Check if an individual subscription exists for a given user.
     * @param int $subscriptionId
     * @param int $userId
     * @return bool
     */
    public function subscriptionExistsByUser($subscriptionId, $userId) {
        $result = $this->retrieve(
            'SELECT COUNT(*) AS count
            FROM subscriptions s, subscription_types st
            WHERE s.type_id = st.type_id
            AND st.institutional = 0
            AND s.subscription_id = ?
            AND s.user_id = ?',
            [(int) $subscriptionId, (int) $userId]
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
     * Check if an individual subscription exists for a given user and journal.
     * @param int $userId
     * @param int $journalId
     * @return bool
     */
    public function subscriptionExistsByUserForJournal($userId, $journalId) {
        $result = $this->retrieve(
            'SELECT COUNT(*) AS count
            FROM subscriptions s, subscription_types st
            WHERE s.type_id = st.type_id
            AND st.institutional = 0
            AND s.user_id = ?
            AND s.journal_id = ?',
            [(int) $userId, (int) $journalId]
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
     * Generator function to create object.
     * @return IndividualSubscription
     */
    public function createObject() {
        return new IndividualSubscription();
    }

    /**
     * Internal function to return an IndividualSubscription object from a row.
     * @param array $row
     * @return IndividualSubscription
     */
    public function _returnSubscriptionFromRow($row) {
        $individualSubscription = parent::_returnSubscriptionFromRow($row);
        
        $tempSubscription = $individualSubscription;
        $tempRow = $row;
        HookRegistry::dispatch('IndividualSubscriptionDAO::_returnSubscriptionFromRow', [&$tempSubscription, &$tempRow]);

        return $individualSubscription;
    }

    /**
     * Insert a new individual subscription.
     * @param IndividualSubscription $individualSubscription
     * @return int
     */
    public function insertSubscription($individualSubscription) {
        return $this->_insertSubscription($individualSubscription);
    }

    /**
     * Update an existing individual subscription.
     * @param IndividualSubscription $individualSubscription
     * @return bool
     */
    public function updateSubscription($individualSubscription) {
        return (bool) $this->_updateSubscription($individualSubscription);
    }

    /**
     * Delete an individual subscription by subscription ID.
     * @param int $subscriptionId
     * @return bool
     */
    public function deleteSubscriptionById($subscriptionId) {
        if ($this->subscriptionExists((int) $subscriptionId)) {
            return (bool) $this->update(
                'DELETE FROM subscriptions WHERE subscription_id = ?',
                [(int) $subscriptionId]
            );
        }
        return false;
    }

    /**
     * Delete individual subscriptions by journal ID.
     * @param int $journalId
     * @return bool
     */
    public function deleteSubscriptionsByJournal($journalId) {
        $result = $this->retrieve(
            'SELECT subscription_id AS subscription_id FROM subscriptions WHERE journal_id = ?',
            [(int) $journalId]
        );

        $returner = true;
        if ($result && !$result->EOF) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $subscriptionId = (int) $row['subscription_id'];
                $returner = $this->deleteSubscriptionById($subscriptionId);
                if (!$returner) { 
                    break;
                }
                $result->MoveNext();
            }
        }
        if ($result) {
            $result->Close();
        }

        return $returner;
    }

    /**
     * Delete individual subscriptions by user ID.
     * @param int $userId
     * @return bool
     */
    public function deleteSubscriptionsByUserId($userId) {
        $result = $this->retrieve(
            'SELECT subscription_id AS subscription_id FROM subscriptions WHERE user_id = ?',
            [(int) $userId]
        );

        $returner = true;
        if ($result && !$result->EOF) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $subscriptionId = (int) $row['subscription_id'];
                $returner = $this->deleteSubscriptionById($subscriptionId);
                if (!$returner) { 
                    break;
                }
                $result->MoveNext();
            }
        }
        if ($result) {
            $result->Close();
        }

        return $returner;
    }

    /**
     * Delete individual subscriptions by user ID and journal ID.
     * @param int $userId
     * @param int $journalId
     * @return bool
     */
    public function deleteSubscriptionsByUserIdForJournal($userId, $journalId) {
        $result = $this->retrieve(
            'SELECT subscription_id AS subscription_id FROM subscriptions WHERE user_id = ? AND journal_id = ?',
            [(int) $userId, (int) $journalId]
        );

        $returner = true;
        if ($result && !$result->EOF) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $subscriptionId = (int) $row['subscription_id'];
                $returner = $this->deleteSubscriptionById($subscriptionId);
                if (!$returner) { 
                    break;
                }
                $result->MoveNext();
            }
        }
        if ($result) {
            $result->Close();
        }

        return $returner;
    }

    /**
     * Delete all individual subscriptions by subscription type ID.
     * @param int $subscriptionTypeId
     * @return bool
     */
    public function deleteSubscriptionsByTypeId($subscriptionTypeId) {
        $result = $this->retrieve(
            'SELECT subscription_id AS subscription_id FROM subscriptions WHERE type_id = ?',
            [(int) $subscriptionTypeId]
        );

        $returner = true;
        if ($result && !$result->EOF) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $subscriptionId = (int) $row['subscription_id'];
                $returner = $this->deleteSubscriptionById($subscriptionId);
                if (!$returner) { 
                    break;
                }
                $result->MoveNext();
            }
        }
        if ($result) {
            $result->Close();
        }

        return $returner;
    }

    /**
     * Retrieve all individual subscriptions.
     * @param mixed $rangeInfo
     * @return DAOResultFactory containing IndividualSubscriptions
     */
    public function getSubscriptions($rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT s.*
            FROM subscriptions s, subscription_types st, users u
            WHERE s.type_id = st.type_id
            AND st.institutional = 0
            AND s.user_id = u.user_id
            ORDER BY u.last_name ASC, s.subscription_id',
            false,
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnSubscriptionFromRow');
    }

    /**
     * Retrieve all individual subscribed users.
     * @param int $journalId
     * @param mixed $rangeInfo
     * @return DAOResultFactory containing Users
     */
    public function getSubscribedUsers($journalId, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT u.*
            FROM subscriptions s, subscription_types st, users u
            WHERE s.type_id = st.type_id AND
                st.institutional = 0 AND
                s.user_id = u.user_id AND
                s.journal_id = ?
            ORDER BY u.last_name ASC, s.subscription_id',
            [(int) $journalId],
            $rangeInfo
        );

        $userDao = DAORegistry::getDAO('UserDAO');
        return new DAOResultFactory($result, $userDao, '_returnUserFromRow');
    }

    /**
     * Retrieve individual subscriptions matching a particular journal ID.
     * @param int $journalId
     * @param int|null $status
     * @param int|null $searchField
     * @param string|null $searchMatch "is", "contains", or "startsWith"
     * @param string|null $search String to look in $searchField for
     * @param int|null $dateField
     * @param string|null $dateFrom Date to search from
     * @param string|null $dateTo Date to search to
     * @param mixed $rangeInfo
     * @return DAOResultFactory containing matching IndividualSubscriptions
     */
    public function getSubscriptionsByJournalId($journalId, $status = null, $searchField = null, $searchMatch = null, $search = null, $dateField = null, $dateFrom = null, $dateTo = null, $rangeInfo = null) {
        $params = [(int) $journalId];
        $searchSql = $this->_generateSearchSQL($status, $searchField, $searchMatch, $search, $dateField, $dateFrom, $dateTo, $params);

        $sql = 'SELECT s.*
                FROM subscriptions s, subscription_types st, users u
                WHERE s.type_id = st.type_id
                AND st.institutional = 0
                AND s.user_id = u.user_id
                AND s.journal_id = ?';
 
        $result = $this->retrieveRange(
            $sql . ' ' . $searchSql . ' ORDER BY u.last_name ASC, s.subscription_id',
            $params,
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnSubscriptionFromRow');
    }

    /**
     * Check whether user with ID has a valid individual subscription for a given journal.
     * @param int $userId
     * @param int $journalId
     * @param int $check Check using either start date, end date, or both (default)
     * @param string|null $checkDate Use this date instead of current date
     * @return int|false
     */
    public function isValidIndividualSubscription($userId, $journalId, $check = SUBSCRIPTION_DATE_BOTH, $checkDate = null) {
        if (empty($userId) || empty($journalId)) {
            return false;
        }
        $returner = false;

        $today = $this->dateToDB(Core::getCurrentDate()); 

        if ($checkDate === null) {
            $checkDate = $today;
        } else {
            $checkDate = $this->dateToDB($checkDate);
        }

        switch ($check) {
            case SUBSCRIPTION_DATE_START:
                $dateSql = sprintf('%s >= s.date_start AND %s >= s.date_start', $checkDate, $today);
                break;
            case SUBSCRIPTION_DATE_END:
                $dateSql = sprintf('%s <= s.date_end AND %s >= s.date_start', $checkDate, $today);
                break;
            default:
                $dateSql = sprintf('%s >= s.date_start AND %s <= s.date_end', $checkDate, $checkDate);
        }

        $nonExpiringSql = "AND ((st.non_expiring = 1) OR (st.non_expiring = 0 AND ($dateSql)))";

        $result = $this->retrieve(
            'SELECT s.subscription_id AS subscription_id
            FROM subscriptions s, subscription_types st
            WHERE s.user_id = ?
            AND s.journal_id = ? 
            AND s.status = ' . SUBSCRIPTION_STATUS_ACTIVE . '
            AND s.type_id = st.type_id
            AND st.institutional = 0 '
            . $nonExpiringSql .
            ' AND (st.format = ' . SUBSCRIPTION_TYPE_FORMAT_ONLINE . ' 
                OR st.format = ' . SUBSCRIPTION_TYPE_FORMAT_PRINT_ONLINE . ')',
            [(int) $userId, (int) $journalId]
        );

        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = (int) $row['subscription_id'];
        }
        if ($result) {
            $result->Close();
        }

        return $returner;
    }

    /**
     * Retrieve active individual subscriptions matching a particular end date and journal ID.
     * @param string $dateEnd
     * @param int $journalId
     * @param int $reminderType SUBSCRIPTION_REMINDER_FIELD_..._EXPIRY
     * @param mixed $rangeInfo
     * @return DAOResultFactory containing matching IndividualSubscriptions
     */
    public function getSubscriptionsToRemind($dateEnd, $journalId, $reminderType, $rangeInfo = null) {
        $fieldName = ((int) $reminderType === SUBSCRIPTION_REMINDER_FIELD_BEFORE_EXPIRY) ? 'date_reminded_before' : 'date_reminded_after';
        
        $result = $this->retrieveRange(
            sprintf(
                'SELECT s.*
                FROM subscriptions s, subscription_types st, users u
                WHERE s.type_id = st.type_id
                    AND s.status = ?
                    AND st.institutional = 0
                    AND u.user_id = s.user_id
                    AND s.date_end <= %s
                    AND s.' . $fieldName . ' IS NULL
                    AND s.journal_id = ?
                ORDER BY u.last_name ASC, s.subscription_id',
                $this->datetimeToDB($dateEnd)
            ),
            [SUBSCRIPTION_STATUS_ACTIVE, (int) $journalId],
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnSubscriptionFromRow');
    }

    /**
     * Renew an individual subscription by dateEnd + duration of subscription type.
     * If the individual subscription is expired, renew to current date + duration.
     * @param IndividualSubscription $individualSubscription
     * @return void
     */    
    public function renewSubscription($individualSubscription) {
        $this->_renewSubscription($individualSubscription);
    }
    
}
?>