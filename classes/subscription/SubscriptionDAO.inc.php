<?php
declare(strict_types=1);

/**
 * @file classes/subscription/SubscriptionDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubscriptionDAO
 * @ingroup subscription
 * @see Subscription
 *
 * @brief Abstract class for retrieving and modifying subscriptions.
 */

import('classes.subscription.Subscription');
import('classes.subscription.SubscriptionType');

define('SUBSCRIPTION_USER', 0x01);
define('SUBSCRIPTION_MEMBERSHIP', 0x02);
define('SUBSCRIPTION_REFERENCE_NUMBER', 0x03);
define('SUBSCRIPTION_NOTES', 0x04);

define('SUBSCRIPTION_REMINDER_FIELD_BEFORE_EXPIRY', 1);
define('SUBSCRIPTION_REMINDER_FIELD_AFTER_EXPIRY', 2);

class SubscriptionDAO extends DAO {

    /**
     * Retrieve subscription by subscription ID.
     * @param int $subscriptionId
     * @return Subscription|null
     */
    public function getSubscription($subscriptionId) {
        // Must be implemented by sub-classes.
        assert(false);
    }

    /**
     * Retrieve subscription journal ID by subscription ID.
     * @param int $subscriptionId
     * @return int|false
     */
    public function getSubscriptionJournalId($subscriptionId) {
        $result = $this->retrieve(
            'SELECT journal_id AS journal_id FROM subscriptions WHERE subscription_id = ?',
            [(int) $subscriptionId]
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
     * Flag a subscription reminder as having been sent.
     * @param int $subscriptionId
     * @param int $reminderType SUBSCRIPTION_REMINDER_FIELD_..._EXPIRY
     * @return void
     */
    public function flagReminded($subscriptionId, $reminderType) {
        $fieldName = ((int) $reminderType === SUBSCRIPTION_REMINDER_FIELD_BEFORE_EXPIRY) ? 'date_reminded_before' : 'date_reminded_after';
        
        $this->update(
            sprintf('UPDATE subscriptions SET %s = %s WHERE subscription_id = ?', $fieldName, $this->datetimeToDB(Core::getCurrentDate())),
            [(int) $subscriptionId]
        );
    }

    /**
     * Retrieve subscription status options as an associative array.
     * @return array
     */
    public function getStatusOptions() {
        return [
            SUBSCRIPTION_STATUS_ACTIVE => 'subscriptions.status.active',
            SUBSCRIPTION_STATUS_NEEDS_INFORMATION => 'subscriptions.status.needsInformation',
            SUBSCRIPTION_STATUS_NEEDS_APPROVAL => 'subscriptions.status.needsApproval',
            SUBSCRIPTION_STATUS_AWAITING_MANUAL_PAYMENT => 'subscriptions.status.awaitingManualPayment',
            SUBSCRIPTION_STATUS_AWAITING_ONLINE_PAYMENT => 'subscriptions.status.awaitingOnlinePayment',
            SUBSCRIPTION_STATUS_OTHER => 'subscriptions.status.other'
        ];
    }

    /**
     * Return number of subscriptions with a given status.
     * @param int $status
     * @return int
     */
    public function getStatusCount($status) {
        // Must be implemented by sub-classes.
        assert(false);
    }

    /**
     * Check if a subscription exists for a given subscription ID.
     * @param int $subscriptionId
     * @return bool
     */
    public function subscriptionExists($subscriptionId) {
        // Must be implemented by sub-classes.
        assert(false);
    }

    /**
     * Check if a subscription exists given a user.
     * @param int $subscriptionId
     * @param int $userId
     * @return bool
     */
    public function subscriptionExistsByUser($subscriptionId, $userId) {
        // Must be implemented by sub-classes.
        assert(false);
    }

    /**
     * Check if a subscription exists given a user and journal.
     * @param int $userId
     * @param int $journalId
     * @return bool
     */
    public function subscriptionExistsByUserForJournal($userId, $journalId) {
        // Must be implemented by sub-classes.
        assert(false);
    }

    /**
     * Insert a new subscription.
     * @param Subscription $subscription
     * @return int
     */
    public function insertSubscription($subscription) {
        // Must be implemented by sub-classes.
        assert(false);
    }

    /**
     * Function to get the ID of the last inserted subscription.
     * @return int
     */
    public function getInsertSubscriptionId() {
        return (int) $this->getInsertId('subscriptions', 'subscription_id');
    }

    /**
     * Update an existing subscription.
     * @param Subscription $subscription
     * @return bool
     */
    public function updateSubscription($subscription) {
        // Must be implemented by sub-classes.
        assert(false);
    }

    /**
     * Delete a subscription by subscription ID.
     * @param int $subscriptionId
     * @return bool
     */
    public function deleteSubscriptionById($subscriptionId) {
        // Must be implemented by sub-classes.
        assert(false);
    }

    /**
     * Delete subscriptions by journal ID.
     * @param int $journalId
     * @return bool
     */
    public function deleteSubscriptionsByJournal($journalId) {
        // Must be implemented by sub-classes.
        assert(false);
    }

    /**
     * Delete subscriptions by user ID.
     * @param int $userId
     * @return bool
     */
    public function deleteSubscriptionsByUserId($userId) {
        // Must be implemented by sub-classes.
        assert(false);
    }

    /**
     * Delete all subscriptions by subscription type ID.
     * @param int $subscriptionTypeId
     * @return bool
     */
    public function deleteSubscriptionsByTypeId($subscriptionTypeId) {
        // Must be implemented by sub-classes.
        assert(false);
    }

    /**
     * Retrieve all subscriptions.
     * @param mixed $rangeInfo
     * @return DAOResultFactory containing Subscriptions
     */
    public function getSubscriptions($rangeInfo = null) {
        // Must be implemented by sub-classes.
        assert(false);
    }

    /**
     * Retrieve subscriptions matching a particular journal ID.
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
        // Must be implemented by sub-classes.
        assert(false);
    }

    /**
     * Retrieve subscriptions matching a particular end date and journal ID.
     * @param string $dateEnd Date (YYYY-MM-DD)
     * @param int $journalId
     * @param int $reminderType SUBSCRIPTION_REMINDER_FIELD_..._EXPIRY
     * @param mixed $rangeInfo
     * @return DAOResultFactory containing matching Subscriptions
     */
    public function getSubscriptionsToRemind($dateEnd, $journalId, $reminderType, $rangeInfo = null) {
        // Must be implemented by sub-classes.
        assert(false);
    }

    /**
     * Function to renew a subscription by dateEnd + duration of subscription type.
     * If the subscription is expired, renew to current date + duration.
     * @param Subscription $subscription
     * @return bool
     */
    public function renewSubscription($subscription) {
        // Must be implemented by sub-classes.
        assert(false);
    }

    /**
     * Internal function to generate user-based search query.
     * @param string $search
     * @param string $searchMatch
     * @param string $prefix
     * @param array $params
     * @return string
     */
    public function _generateUserNameSearchSQL($search, $searchMatch, $prefix, &$params) {
        $first_last = $this->concat($prefix . 'first_name', '\' \'', $prefix . 'last_name');
        $first_middle_last = $this->concat($prefix . 'first_name', '\' \'', $prefix . 'middle_name', '\' \'', $prefix . 'last_name');
        $last_comma_first = $this->concat($prefix . 'last_name', '\', \'', $prefix . 'first_name');
        $last_comma_first_middle = $this->concat($prefix . 'last_name', '\', \'', $prefix . 'first_name', '\' \'', $prefix . 'middle_name');
        
        if ($searchMatch === 'is') {
            $searchSql = " AND (LOWER({$prefix}last_name) = LOWER(?) OR LOWER($first_last) = LOWER(?) OR LOWER($first_middle_last) = LOWER(?) OR LOWER($last_comma_first) = LOWER(?) OR LOWER($last_comma_first_middle) = LOWER(?))";
        } elseif ($searchMatch === 'contains') {
            $searchSql = " AND (LOWER({$prefix}last_name) LIKE LOWER(?) OR LOWER($first_last) LIKE LOWER(?) OR LOWER($first_middle_last) LIKE LOWER(?) OR LOWER($last_comma_first) LIKE LOWER(?) OR LOWER($last_comma_first_middle) LIKE LOWER(?))";
            $search = '%' . (string) $search . '%';
        } else { // $searchMatch === 'startsWith'
            $searchSql = " AND (LOWER({$prefix}last_name) LIKE LOWER(?) OR LOWER($first_last) LIKE LOWER(?) OR LOWER($first_middle_last) LIKE LOWER(?) OR LOWER($last_comma_first) LIKE LOWER(?) OR LOWER($last_comma_first_middle) LIKE LOWER(?))";
            $search = (string) $search . '%';
        }
        
        // [WIZDAM FIX] Replaced chained assignment with clean sequential pushes for readability
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        
        return $searchSql;
    }

    /**
     * Internal function to generate subscription-based search query.
     * @param int|null $status
     * @param int|null $searchField
     * @param string|null $searchMatch
     * @param string|null $search
     * @param int|null $dateField
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @param array $params
     * @return string
     */
    public function _generateSearchSQL($status = null, $searchField = null, $searchMatch = null, $search = null, $dateField = null, $dateFrom = null, $dateTo = null, &$params) {
        $searchSql = '';

        if ($search !== null && $search !== '') {
            switch ($searchField) {
                case SUBSCRIPTION_USER:
                    $searchSql = $this->_generateUserNameSearchSQL((string) $search, (string) $searchMatch, 'u.', $params);
                    break;
                case SUBSCRIPTION_MEMBERSHIP:
                    if ($searchMatch === 'is') {
                        $searchSql = ' AND LOWER(s.membership) = LOWER(?)';
                    } elseif ($searchMatch === 'contains') {
                        $searchSql = ' AND LOWER(s.membership) LIKE LOWER(?)';
                        $search = '%' . (string) $search . '%';
                    } else { // $searchMatch === 'startsWith'
                        $searchSql = ' AND LOWER(s.membership) LIKE LOWER(?)';
                        $search = (string) $search . '%';
                    }
                    $params[] = $search;
                    break;
                case SUBSCRIPTION_REFERENCE_NUMBER:
                    if ($searchMatch === 'is') {
                        $searchSql = ' AND LOWER(s.reference_number) = LOWER(?)';
                    } elseif ($searchMatch === 'contains') {
                        $searchSql = ' AND LOWER(s.reference_number) LIKE LOWER(?)';
                        $search = '%' . (string) $search . '%';
                    } else { // $searchMatch === 'startsWith'
                        $searchSql = ' AND LOWER(s.reference_number) LIKE LOWER(?)';
                        $search = (string) $search . '%';
                    }
                    $params[] = $search;
                    break;
                case SUBSCRIPTION_NOTES:
                    if ($searchMatch === 'is') {
                        $searchSql = ' AND LOWER(s.notes) = LOWER(?)';
                    } elseif ($searchMatch === 'contains') {
                        $searchSql = ' AND LOWER(s.notes) LIKE LOWER(?)';
                        $search = '%' . (string) $search . '%';
                    } else { // $searchMatch === 'startsWith'
                        $searchSql = ' AND LOWER(s.notes) LIKE LOWER(?)';
                        $search = (string) $search . '%';
                    }
                    $params[] = $search;
                    break;
            }
        }

        if (($dateFrom !== null && $dateFrom !== '') || ($dateTo !== null && $dateTo !== '')) {
            switch ($dateField) {
                case SUBSCRIPTION_DATE_START:
                    if ($dateFrom !== null && $dateFrom !== '') {
                        $searchSql .= ' AND s.date_start >= ' . $this->datetimeToDB($dateFrom);
                    }
                    if ($dateTo !== null && $dateTo !== '') {
                        $searchSql .= ' AND s.date_start <= ' . $this->datetimeToDB($dateTo);
                    }
                    break;
                case SUBSCRIPTION_DATE_END:
                    if ($dateFrom !== null && $dateFrom !== '') {
                        $searchSql .= ' AND s.date_end >= ' . $this->datetimeToDB($dateFrom);
                    }
                    if ($dateTo !== null && $dateTo !== '') {
                        $searchSql .= ' AND s.date_end <= ' . $this->datetimeToDB($dateTo);
                    }
                    break;
            }
        }

        if ($status !== null && $status !== '') {
            $searchSql .= ' AND s.status = ' . (int) $status;
        }

        return $searchSql;
    }

    /**
     * Generator function to create object.
     * @return Subscription
     */
    public function createObject() {
        // Must be implemented by sub-classes.
        assert(false);
    }

    /**
     * Internal function to return a Subscription object from a row.
     * @param array $row
     * @return Subscription
     */
    public function _returnSubscriptionFromRow($row) {
        $subscription = $this->createObject();
        $subscription->setId((int) $row['subscription_id']);
        $subscription->setJournalId((int) $row['journal_id']);
        $subscription->setUserId((int) $row['user_id']);
        $subscription->setTypeId((int) $row['type_id']);
        $subscription->setDateStart($this->dateFromDB($row['date_start']));
        $subscription->setDateEnd($this->dateFromDB($row['date_end']));
        $subscription->setDateRemindedBefore($this->datetimeFromDB($row['date_reminded_before']));
        $subscription->setDateRemindedAfter($this->datetimeFromDB($row['date_reminded_after']));
        $subscription->setStatus((int) $row['status']);
        $subscription->setMembership((string) $row['membership']);
        $subscription->setReferenceNumber((string) $row['reference_number']);
        $subscription->setNotes((string) $row['notes']);

        $tempSubscription = $subscription;
        $tempRow = $row;
        HookRegistry::dispatch('SubscriptionDAO::_returnSubscriptionFromRow', [&$tempSubscription, &$tempRow]);

        return $subscription;
    }

    /**
     * Internal function to insert a new Subscription.
     * @param Subscription $subscription
     * @return int
     */
    public function _insertSubscription($subscription) {
        $this->update(
            sprintf('INSERT INTO subscriptions
                (journal_id, user_id, type_id, date_start, date_end, date_reminded_before, date_reminded_after, status, membership, reference_number, notes)
                VALUES
                (?, ?, ?, %s, %s, %s, %s, ?, ?, ?, ?)',
                $this->dateToDB($subscription->getDateStart()),
                $this->datetimeToDB($subscription->getDateEnd()),
                $this->datetimeToDB($subscription->getDateRemindedBefore()),
                $this->datetimeToDB($subscription->getDateRemindedAfter())
            ),
            [
                (int) $subscription->getJournalId(),
                (int) $subscription->getUserId(),
                (int) $subscription->getTypeId(),
                (int) $subscription->getStatus(),
                (string) $subscription->getMembership(),
                (string) $subscription->getReferenceNumber(),
                (string) $subscription->getNotes()
            ]
        );

        $subscriptionId = $this->getInsertSubscriptionId();
        $subscription->setId($subscriptionId);

        return $subscriptionId;
    }

    /**
     * Internal function to update a Subscription.
     * @param Subscription $subscription
     * @return bool
     */
    public function _updateSubscription($subscription) {
        $returner = $this->update(
            sprintf('UPDATE subscriptions
                SET
                    journal_id = ?,
                    user_id = ?,
                    type_id = ?,
                    date_start = %s,
                    date_end = %s,
                    date_reminded_before = %s,
                    date_reminded_after = %s,
                    status = ?,
                    membership = ?,
                    reference_number = ?,
                    notes = ?
                WHERE subscription_id = ?',
                $this->dateToDB($subscription->getDateStart()),
                $this->datetimeToDB($subscription->getDateEnd()),
                $this->datetimeToDB($subscription->getDateRemindedBefore()),
                $this->datetimeToDB($subscription->getDateRemindedAfter())
            ),
            [
                (int) $subscription->getJournalId(),
                (int) $subscription->getUserId(),
                (int) $subscription->getTypeId(),
                (int) $subscription->getStatus(),
                (string) $subscription->getMembership(),
                (string) $subscription->getReferenceNumber(),
                (string) $subscription->getNotes(),
                (int) $subscription->getId()
            ]
        );

        return (bool) $returner;
    }

    /**
     * Internal function to renew a subscription by dateEnd + duration of subscription type.
     * If the subscription is expired, renew to current date + duration.
     * @param Subscription $subscription
     * @return void
     */
    public function _renewSubscription($subscription) {
        if ($subscription->isNonExpiring()) {
            return;
        }

        /** @var SubscriptionTypeDAO $subscriptionTypeDao */
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        $subscriptionType = $subscriptionTypeDao->getSubscriptionType((int) $subscription->getTypeId());

        $duration = (int) $subscriptionType->getDuration();
        $dateEnd = strtotime((string) $subscription->getDateEnd());

        // If the subscription is expired, extend it to today + duration of subscription
        $time = time();
        if ($dateEnd < $time) {
            $dateEnd = $time;
        }

        $subscription->setDateEnd(mktime(23, 59, 59, (int) date("m", $dateEnd) + $duration, (int) date("d", $dateEnd), (int) date("Y", $dateEnd)));

        // Reset reminder dates
        $subscription->setDateRemindedBefore(null);
        $subscription->setDateRemindedAfter(null);

        $this->updateSubscription($subscription);
    }

}
?>