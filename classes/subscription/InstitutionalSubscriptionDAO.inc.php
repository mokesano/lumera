<?php
declare(strict_types=1);

/**
 * @file classes/subscription/InstitutionalSubscriptionDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InstitutionalSubscriptionDAO
 * @ingroup subscription
 * @see InstitutionalSubscription
 *
 * @brief Operations for retrieving and modifying InstitutionalSubscription objects.
 */

import('classes.subscription.SubscriptionDAO');
import('classes.subscription.InstitutionalSubscription');

define('SUBSCRIPTION_INSTITUTION_NAME', 0x20);
define('SUBSCRIPTION_DOMAIN', 0x21);
define('SUBSCRIPTION_IP_RANGE', 0x22);

class InstitutionalSubscriptionDAO extends SubscriptionDAO {

    /**
     * Retrieve an institutional subscription by subscription ID.
     * @param int $subscriptionId
     * @return InstitutionalSubscription|null
     */
    public function getSubscription($subscriptionId) {
        $result = $this->retrieve(
            'SELECT s.*, iss.*
            FROM subscriptions s, subscription_types st, institutional_subscriptions iss
            WHERE s.type_id = st.type_id
            AND st.institutional = 1
            AND s.subscription_id = iss.subscription_id
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
     * Retrieve institutional subscriptions by user ID.
     * @param int $userId
     * @param mixed $rangeInfo
     * @return DAOResultFactory containing matching InstitutionalSubscriptions
     */
    public function getSubscriptionsByUser($userId, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT s.*, iss.*
            FROM subscriptions s, subscription_types st, institutional_subscriptions iss
            WHERE s.type_id = st.type_id
            AND st.institutional = 1
            AND s.subscription_id = iss.subscription_id
            AND s.user_id = ?',
            [(int) $userId],
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnSubscriptionFromRow');
    }

    /**
     * Retrieve institutional subscriptions by user ID and journal ID.
     * @param int $userId
     * @param int $journalId
     * @param mixed $rangeInfo
     * @return DAOResultFactory containing matching InstitutionalSubscriptions
     */
    public function getSubscriptionsByUserForJournal($userId, $journalId, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT s.*, iss.*
            FROM subscriptions s, subscription_types st, institutional_subscriptions iss
            WHERE s.type_id = st.type_id
            AND st.institutional = 1
            AND s.subscription_id = iss.subscription_id
            AND s.user_id = ?
            AND s.journal_id = ?',
            [(int) $userId, (int) $journalId],
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnSubscriptionFromRow');
    }

    /**
     * Retrieve institutional subscriptions by institution name.
     * @param string $institutionName
     * @param int $journalId
     * @param bool $exactMatch
     * @param mixed $rangeInfo
     * @return DAOResultFactory containing matching InstitutionalSubscriptions
     */
    public function getSubscriptionsByInstitutionName($institutionName, $journalId, $exactMatch = true, $rangeInfo = null) {
        $sql = 'SELECT s.*, iss.*
                FROM subscriptions s, subscription_types st, institutional_subscriptions iss
                WHERE s.type_id = st.type_id
                AND st.institutional = 1
                AND s.subscription_id = iss.subscription_id';
        
        if ($exactMatch) {
            $sql .= ' AND LOWER(iss.institution_name) = LOWER(?)';
        } else {
            $sql .= ' AND LOWER(iss.institution_name) LIKE LOWER(?)';
        }
        
        $sql .= ' AND s.journal_id = ?';

        $result = $this->retrieveRange(
            $sql,
            [(string) $institutionName, (int) $journalId],
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnSubscriptionFromRow');
    }

    /**
     * Return number of institutional subscriptions with given status for a journal.
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
                st.institutional = 1 AND
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
     * Get the number of institutional subscriptions for a particular journal.
     * @param int $journalId
     * @return int
     */
    public function getSubscribedUserCount($journalId) {
        return $this->getStatusCount($journalId);
    }

    /**
     * Check if an institutional subscription exists for a given subscription ID.
     * @param int $subscriptionId
     * @return bool
     */
    public function subscriptionExists($subscriptionId) {
        $result = $this->retrieve(
            'SELECT COUNT(*) AS count
            FROM subscriptions s, subscription_types st
            WHERE s.type_id = st.type_id
            AND st.institutional = 1
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
     * Check if an institutional subscription exists for a given user.
     * @param int $subscriptionId
     * @param int $userId
     * @return bool
     */
    public function subscriptionExistsByUser($subscriptionId, $userId) { 
        $result = $this->retrieve(
            'SELECT COUNT(*) AS count
            FROM subscriptions s, subscription_types st
            WHERE s.type_id = st.type_id
            AND st.institutional = 1
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
     * Check if an institutional subscription exists for a given user and journal.
     * @param int $userId
     * @param int $journalId
     * @return bool
     */
    public function subscriptionExistsByUserForJournal($userId, $journalId) {
        $result = $this->retrieve(
            'SELECT COUNT(*) AS count
            FROM subscriptions s, subscription_types st
            WHERE s.type_id = st.type_id
            AND st.institutional = 1
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
     * Check if an institutional subscription exists for a given institution name and journal.
     * @param string $institutionName
     * @param int $journalId
     * @param bool $exactMatch
     * @return bool
     */
    public function subscriptionExistsByInstitutionName($institutionName, $journalId, $exactMatch = true) {
        $sql = 'SELECT COUNT(*) AS count
                FROM subscriptions s, subscription_types st, institutional_subscriptions iss
                WHERE s.type_id = st.type_id
                AND st.institutional = 1';
                
        if ($exactMatch) {
            $sql .= ' AND LOWER(iss.institution_name) = LOWER(?)';
        } else {
            $sql .= ' AND LOWER(iss.institution_name) LIKE LOWER(?)';
        }

        $sql .= ' AND s.journal_id = ?';

        $result = $this->retrieve(
            $sql,
            [(string) $institutionName, (int) $journalId]
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
     * Insert a new institutional subscription.
     * @param InstitutionalSubscription $institutionalSubscription
     * @return int
     */
    public function insertSubscription($institutionalSubscription) {
        $subscriptionId = null;
        if ($this->_insertSubscription($institutionalSubscription)) {
            $subscriptionId = (int) $institutionalSubscription->getId();

            $this->update(
                'INSERT INTO institutional_subscriptions (subscription_id, institution_name, mailing_address, domain) VALUES (?, ?, ?, ?)',
                [
                    $subscriptionId,
                    (string) $institutionalSubscription->getInstitutionName(),
                    (string) $institutionalSubscription->getInstitutionMailingAddress(),
                    (string) $institutionalSubscription->getDomain()
                ]
            );

            $this->_insertSubscriptionIPRanges($subscriptionId, $institutionalSubscription->getIPRanges());
        }

        return $subscriptionId;
    }

    /**
     * Update an existing institutional subscription.
     * @param InstitutionalSubscription $institutionalSubscription
     * @return bool
     */
    public function updateSubscription($institutionalSubscription) {
        $returner = false;
        if ($this->_updateSubscription($institutionalSubscription)) {
            $returner = (bool) $this->update(
                'UPDATE institutional_subscriptions SET institution_name = ?, mailing_address = ?, domain = ? WHERE subscription_id = ?',
                [
                    (string) $institutionalSubscription->getInstitutionName(),
                    (string) $institutionalSubscription->getInstitutionMailingAddress(),
                    (string) $institutionalSubscription->getDomain(),
                    (int) $institutionalSubscription->getId()
                ]
            );

            $this->_deleteSubscriptionIPRanges((int) $institutionalSubscription->getId());
            $this->_insertSubscriptionIPRanges((int) $institutionalSubscription->getId(), $institutionalSubscription->getIPRanges());
        }

        return $returner;
    }

    /**
     * Delete an institutional subscription by subscription ID.
     * @param int $subscriptionId
     * @return bool
     */
    public function deleteSubscriptionById($subscriptionId) {
        $returner = false;
        if ($this->subscriptionExists((int) $subscriptionId)) {
            $this->update('DELETE FROM subscriptions WHERE subscription_id = ?', [(int) $subscriptionId]);
            $returner = (bool) $this->update('DELETE FROM institutional_subscriptions WHERE subscription_id = ?', [(int) $subscriptionId]);
            $this->_deleteSubscriptionIPRanges((int) $subscriptionId);
        } 
        return $returner;
    }

    /**
     * Delete institutional subscriptions by journal ID.
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
     * Delete institutional subscriptions by user ID.
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
     * Delete institutional subscriptions by user ID and journal ID.
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
     * Delete all institutional subscriptions by subscription type ID.
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
     * Retrieve all institutional subscriptions.
     * @param mixed $rangeInfo
     * @return DAOResultFactory containing InstitutionalSubscriptions
     */
    public function getSubscriptions($rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT s.*, iss.*
            FROM subscriptions s, subscription_types st, institutional_subscriptions iss
            WHERE s.type_id = st.type_id
            AND st.institutional = 1
            AND s.subscription_id = iss.subscription_id
            ORDER BY iss.institution_name ASC, s.subscription_id',
            false,
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnSubscriptionFromRow');
    }

    /**
     * Retrieve all institutional subscription contacts.
     * @param int $journalId
     * @param mixed $rangeInfo
     * @return DAOResultFactory containing Users
     */
    public function getSubscribedUsers($journalId, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT u.*
            FROM subscriptions s, subscription_types st, users u
            WHERE s.type_id = st.type_id AND
                st.institutional = 1 AND
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
     * Retrieve institutional subscriptions matching a particular journal ID.
     * @param int $journalId
     * @param int|null $status
     * @param int|null $searchField
     * @param string|null $searchMatch "is", "contains", or "startsWith"
     * @param string|null $search String to look in $searchField for
     * @param int|null $dateField
     * @param string|null $dateFrom Date to search from
     * @param string|null $dateTo Date to search to
     * @param mixed $rangeInfo
     * @return DAOResultFactory containing matching Subscriptions
     */
    public function getSubscriptionsByJournalId($journalId, $status = null, $searchField = null, $searchMatch = null, $search = null, $dateField = null, $dateFrom = null, $dateTo = null, $rangeInfo = null) {
        $params = [(int) $journalId];
        $ipRangeSql1 = '';
        $ipRangeSql2 = '';
        
        $searchSql = $this->_generateSearchSQL($status, $searchField, $searchMatch, $search, $dateField, $dateFrom, $dateTo, $params);

        if ($search !== null && $search !== '') {
            switch ($searchField) {
                case SUBSCRIPTION_INSTITUTION_NAME:
                    if ($searchMatch === 'is') {
                        $searchSql = ' AND LOWER(iss.institution_name) = LOWER(?)';
                    } elseif ($searchMatch === 'contains') {
                        $searchSql = ' AND LOWER(iss.institution_name) LIKE LOWER(?)';
                        $search = '%' . (string) $search . '%';
                    } else {
                        $searchSql = ' AND LOWER(iss.institution_name) LIKE LOWER(?)';
                        $search = (string) $search . '%';
                    }
                    $params[] = $search;
                    break;
                case SUBSCRIPTION_DOMAIN:
                    if ($searchMatch === 'is') {
                        $searchSql = ' AND LOWER(iss.domain) = LOWER(?)';
                    } elseif ($searchMatch === 'contains') {
                        $searchSql = ' AND LOWER(iss.domain) LIKE LOWER(?)';
                        $search = '%' . (string) $search . '%';
                    } else {
                        $searchSql = ' AND LOWER(iss.domain) LIKE LOWER(?)';
                        $search = (string) $search . '%';
                    }
                    $params[] = $search;
                    break;
                case SUBSCRIPTION_IP_RANGE:
                    if ($searchMatch === 'is') {
                        $searchSql = ' AND LOWER(isip.ip_string) = LOWER(?)';
                    } elseif ($searchMatch === 'contains') {
                        $searchSql = ' AND LOWER(isip.ip_string) LIKE LOWER(?)';
                        $search = '%' . (string) $search . '%';
                    } else {
                        $searchSql = ' AND LOWER(isip.ip_string) LIKE LOWER(?)';
                        $search = (string) $search . '%';
                    }
                    $params[] = $search;
                    $ipRangeSql1 = ', institutional_subscription_ip isip';
                    $ipRangeSql2 = ' AND s.subscription_id = isip.subscription_id';
                    break;
            }
        }

        $sql = 'SELECT DISTINCT s.*, iss.*
                FROM subscriptions s, subscription_types st, users u, institutional_subscriptions iss'
                . $ipRangeSql1 .
                ' WHERE s.type_id = st.type_id
                AND s.user_id = u.user_id
                AND st.institutional = 1
                AND s.subscription_id = iss.subscription_id'
                . $ipRangeSql2 .
                ' AND s.journal_id = ?';

        $result = $this->retrieveRange(
            $sql . ' ' . $searchSql . ' ORDER BY iss.institution_name ASC, s.subscription_id',
            $params,
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnSubscriptionFromRow');
    }

    /**
     * Check whether there is a valid institutional subscription for a given journal.
     * @param string $domain
     * @param string $IP
     * @param int $journalId
     * @param int $check Test using either start date, end date, or both (default)
     * @param string|null $checkDate Use this date instead of current date
     * @return int|false
     */
    public function isValidInstitutionalSubscription($domain, $IP, $journalId, $check = SUBSCRIPTION_DATE_BOTH, $checkDate = null) {
        if (empty($journalId) || (empty($domain) && empty($IP))) {
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

        if (!empty($domain)) {
            $result = $this->retrieve(
                'SELECT iss.subscription_id 
                FROM institutional_subscriptions iss, subscriptions s, subscription_types st
                WHERE POSITION(UPPER(LPAD(iss.domain, LENGTH(iss.domain)+1, \'.\')) IN UPPER(LPAD(?, LENGTH(?)+1, \'.\'))) != 0
                AND iss.domain != \'\'
                AND iss.subscription_id = s.subscription_id
                AND s.journal_id = ?
                AND s.status = ' . SUBSCRIPTION_STATUS_ACTIVE . '
                AND s.type_id = st.type_id
                AND st.institutional = 1 '
                . $nonExpiringSql .
                ' AND (st.format = ' . SUBSCRIPTION_TYPE_FORMAT_ONLINE . '
                    OR st.format = ' . SUBSCRIPTION_TYPE_FORMAT_PRINT_ONLINE . ')',
                [(string) $domain, (string) $domain, (int) $journalId]
            );

            if ($result && !$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $returner = (int) $row['subscription_id'];
            }
            if ($result) {
                $result->Close();
            }

            if ($returner) {
                return $returner;
            }
        }

        if (!empty($IP)) {
            $ipLong = sprintf('%u', ip2long((string) $IP));

            $result = $this->retrieve(
                'SELECT isip.subscription_id
                FROM institutional_subscription_ip isip, subscriptions s, subscription_types st
                WHERE ((isip.ip_end IS NOT NULL
                AND ? >= isip.ip_start AND ? <= isip.ip_end
                AND isip.subscription_id = s.subscription_id   
                AND s.journal_id = ?
                AND s.status = ' . SUBSCRIPTION_STATUS_ACTIVE . '
                AND s.type_id = st.type_id
                AND st.institutional = 1 '
                . $nonExpiringSql .
                ' AND (st.format = ' . SUBSCRIPTION_TYPE_FORMAT_ONLINE . '
                    OR st.format = ' . SUBSCRIPTION_TYPE_FORMAT_PRINT_ONLINE . '))
                OR  (isip.ip_end IS NULL
                AND ? = isip.ip_start
                AND isip.subscription_id = s.subscription_id   
                AND s.journal_id = ?
                AND s.status = ' . SUBSCRIPTION_STATUS_ACTIVE . '
                AND s.type_id = st.type_id
                AND st.institutional = 1 '
                . $nonExpiringSql .
                ' AND (st.format = ' . SUBSCRIPTION_TYPE_FORMAT_ONLINE . '
                    OR st.format = ' . SUBSCRIPTION_TYPE_FORMAT_PRINT_ONLINE . ')))',
                [$ipLong, $ipLong, (int) $journalId, $ipLong, (int) $journalId]
            );

            if ($result && !$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $returner = (int) $row['subscription_id'];
            }
            if ($result) {
                $result->Close();
            }
        }

        return $returner;
    }

    /**
     * Retrieve active institutional subscriptions matching a particular end date and journal ID.
     * @param string $dateEnd
     * @param int $journalId
     * @param int $reminderType SUBSCRIPTION_REMINDER_FIELD_..._EXPIRY
     * @param mixed $rangeInfo
     * @return DAOResultFactory containing matching InstitutionalSubscriptions
     */
    public function getSubscriptionsToRemind($dateEnd, $journalId, $reminderType, $rangeInfo = null) {
        $fieldName = ((int) $reminderType === SUBSCRIPTION_REMINDER_FIELD_BEFORE_EXPIRY) ? 'date_reminded_before' : 'date_reminded_after';
        
        $result = $this->retrieveRange(
            sprintf(
                'SELECT s.*, iss.*
                FROM subscriptions s, subscription_types st, institutional_subscriptions iss
                WHERE s.type_id = st.type_id
                    AND s.status = ?
                    AND st.institutional = 1
                    AND s.subscription_id = iss.subscription_id
                    AND s.date_end <= %s
                    AND s.' . $fieldName . ' IS NULL
                    AND s.journal_id = ?
                ORDER BY iss.institution_name ASC, s.subscription_id',
                $this->datetimeToDB($dateEnd)
            ),
            [SUBSCRIPTION_STATUS_ACTIVE, (int) $journalId],
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnSubscriptionFromRow');
    }

    /**
     * Renew an institutional subscription by dateEnd + duration of subscription type.
     * If the institutional subscription is expired, renew to current date + duration.
     * @param InstitutionalSubscription $institutionalSubscription
     * @return void
     */	
    public function renewSubscription($institutionalSubscription) {
        $this->_renewSubscription($institutionalSubscription);
    }

    /**
     * Generator function to create object.
     * @return InstitutionalSubscription
     */
    public function createObject() {
        return new InstitutionalSubscription();
    }

    /**
     * Internal function to return an InstitutionalSubscription object from a row.
     * @param array $row
     * @return InstitutionalSubscription
     */
    public function _returnSubscriptionFromRow($row) {
        // [WIZDAM] Type narrowing annotation to satisfy linter for undefined methods
        /** @var InstitutionalSubscription $institutionalSubscription */
        $institutionalSubscription = parent::_returnSubscriptionFromRow($row);

        $institutionalSubscription->setInstitutionName((string) $row['institution_name']);
        $institutionalSubscription->setInstitutionMailingAddress((string) $row['mailing_address']);
        $institutionalSubscription->setDomain((string) $row['domain']);

        $ipResult = $this->retrieve(
            'SELECT ip_string FROM institutional_subscription_ip WHERE subscription_id = ? ORDER BY institutional_subscription_ip_id ASC',
            [(int) $institutionalSubscription->getId()]
        );

        $ipRanges = [];
        if ($ipResult) {
            while (!$ipResult->EOF) {
                $ipRow = $ipResult->GetRowAssoc(false);
                $ipRanges[] = (string) $ipRow['ip_string'];
                $ipResult->MoveNext(); // [FIX] Corrected capitalization from moveNext() to MoveNext()
            }
            $ipResult->Close();
        }

        $institutionalSubscription->setIPRanges($ipRanges);

        $tempSubscription = $institutionalSubscription;
        $tempRow = $row;
        HookRegistry::dispatch('InstitutionalSubscriptionDAO::_returnSubscriptionFromRow', [&$tempSubscription, &$tempRow]);

        return $institutionalSubscription;
    }

    /**
     * Internal function to insert institutional subscription IP ranges.
     * @param int $subscriptionId
     * @param array $ipRanges
     * @return bool
     */
    public function _insertSubscriptionIPRanges($subscriptionId, $ipRanges) {
        if (empty($ipRanges) || empty($subscriptionId)) {
            return false;
        }

        $returner = true;
        
        foreach ($ipRanges as $curIPString) {
            $ipStart = null;
            $ipEnd = null;
            $curIPString = (string) $curIPString;

            if (strpos($curIPString, SUBSCRIPTION_IP_RANGE_RANGE) === false) {
                if (strpos($curIPString, SUBSCRIPTION_IP_RANGE_WILDCARD) === false) {
                    if (strpos($curIPString, '/') === false) {
                        $ipStart = sprintf("%u", ip2long(trim($curIPString)));
                    } else {
                        list($cidrIPString, $cidrBits) = explode('/', trim($curIPString));
                        $cidrBits = (int) $cidrBits;
                        if ($cidrBits === 0) {
                            $cidrMask = 0;
                        } else {
                            $cidrMask = (0xffffffff << (32 - $cidrBits));
                        }
                        $ipStart = sprintf('%u', ip2long($cidrIPString) & $cidrMask);
                        if ($cidrBits !== 32) {
                            $ipEnd = sprintf('%u', ip2long($cidrIPString) | (~$cidrMask & 0xffffffff));
                        }
                    }
                } else {
                    $ipStart = sprintf('%u', ip2long(str_replace(SUBSCRIPTION_IP_RANGE_WILDCARD, '0', trim($curIPString))));
                    $ipEnd = sprintf('%u', ip2long(str_replace(SUBSCRIPTION_IP_RANGE_WILDCARD, '255', trim($curIPString)))); 
                }
            } else {
                list($ipStartStr, $ipEndStr) = explode(SUBSCRIPTION_IP_RANGE_RANGE, $curIPString);
                $ipStart = sprintf('%u', ip2long(str_replace(SUBSCRIPTION_IP_RANGE_WILDCARD, '0', trim($ipStartStr))));
                $ipEnd = sprintf('%u', ip2long(str_replace(SUBSCRIPTION_IP_RANGE_WILDCARD, '255', trim($ipEndStr))));
            }

            if ($ipStart !== null && $returner) {
                $returner = (bool) $this->update(
                    'INSERT INTO institutional_subscription_ip (subscription_id, ip_string, ip_start, ip_end) VALUES (?, ?, ?, ?)',
                    [
                        (int) $subscriptionId,
                        $curIPString,
                        (int) $ipStart,
                        $ipEnd !== null ? (int) $ipEnd : null
                    ] 
                );
            } else {
                $returner = false;
                break;
            }
        }

        return $returner;
    }

    /**
     * Internal function to delete subscription IP ranges by subscription ID.
     * @param int $subscriptionId
     * @return bool
     */
    public function _deleteSubscriptionIPRanges($subscriptionId) {
        return (bool) $this->update(
            'DELETE FROM institutional_subscription_ip WHERE subscription_id = ?', 
            [(int) $subscriptionId]
        );
    }
    
}
?>