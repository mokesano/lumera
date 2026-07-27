<?php
declare(strict_types=1);

/**
 * @file plugins/generic/referral/ReferralDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReferralDAO
 * @ingroup plugins_generic_referral
 * @see Referral
 *
 * @brief Operations for retrieving and modifying Referral objects.
 */

class ReferralDAO extends DAO {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function ReferralDAO() {
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
     * Retrieve a referral by referral ID.
     * @param int $referralId
     * @return Referral|null
     */
    public function getReferral($referralId) {
        $result = $this->retrieve(
            'SELECT * FROM referrals WHERE referral_id = ?', 
            [(int) $referralId]
        );

        $returner = null;
        // [SCHOLARWIZDAM LUMERA STANDARD] Using $result && !$result->EOF
        if ($result && !$result->EOF) {
            $returner = $this->_returnReferralFromRow($result->GetRowAssoc(false));
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Get a list of localized field names.
     * @return array
     */
    public function getLocaleFieldNames() {
        return ['name'];
    }

    /**
     * Internal function to return a Referral object from a row.
     * @param array $row
     * @return Referral
     */
    public function _returnReferralFromRow($row) {
        $referral = new Referral();
        $referral->setId((int) $row['referral_id']);
        $referral->setArticleId((int) $row['article_id']);
        $referral->setStatus((int) $row['status']);
        $referral->setURL((string) $row['url']);
        $referral->setDateAdded($this->datetimeFromDB($row['date_added']));
        $referral->setLinkCount((int) $row['link_count']);

        $this->getDataObjectSettings('referral_settings', 'referral_id', (int) $row['referral_id'], $referral);

        return $referral;
    }

    /**
     * Check if a referrer exists with the given article and URL.
     * @param int $articleId
     * @param string $url
     * @return bool
     */
    public function referralExistsByUrl($articleId, $url) {
        $result = $this->retrieve(
            'SELECT COUNT(*) AS count FROM referrals WHERE article_id = ? AND url = ?',
            [
                (int) $articleId,
                (string) $url
            ]
        );
        
        $returner = false;
        // [SCHOLARWIZDAM LUMERA STANDARD] Using $result && !$result->EOF instead of $result->fields[0]
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
     * Increment the referral count.
     * @param int $articleId
     * @param string $url
     * @return bool
     */
    public function incrementReferralCount($articleId, $url) {
        return $this->update(
            'UPDATE referrals SET link_count = link_count + 1 WHERE article_id = ? AND url = ?',
            [(int) $articleId, (string) $url]
        );
    }

    /**
     * Update the localized settings for this object.
     * @param Referral $referral
     * @return void
     */
    public function updateLocaleFields(&$referral) {
        $this->updateDataObjectSettings('referral_settings', $referral, [
            'referral_id' => (int) $referral->getId()
        ]);
    }

    /**
     * Insert a new Referral or replace the Referral if it already exists.
     * @param Referral $referral
     * @return int
     */
    public function replaceReferral(&$referral) {
        $date = trim((string) $this->datetimeToDB($referral->getDateAdded()), "'");
        $isNew = ($referral->getId() === null || $referral->getId() === 0);
        $this->replace(
            'referrals',
            [
                'status' => (int) $referral->getStatus(),
                'article_id' => (int) $referral->getArticleId(),
                'url' => (string) $referral->getURL(),
                'date_added' => $date,
                'link_count' => (int) $referral->getLinkCount(),
            ],
            ['article_id', 'url']
        );

        if ($isNew) {
            $referral->setId((int) $this->getInsertObjectId());
        }

        $this->updateLocaleFields($referral);
        return (int) $referral->getId();
    }

    /**
     * Update an existing referral.
     * @param Referral $referral
     * @return bool
     */
    public function updateReferral(&$referral) {
        $returner = $this->update(
            sprintf('UPDATE referrals
                SET status = ?,
                    article_id = ?,
                    url = ?,
                    date_added = %s,
                    link_count = ?
                WHERE referral_id = ?',
                $this->datetimeToDB($referral->getDateAdded())
            ),
            [
                (int) $referral->getStatus(),
                (int) $referral->getArticleId(),
                (string) $referral->getURL(),
                (int) $referral->getLinkCount(),
                (int) $referral->getId()
            ]
        );
        $this->updateLocaleFields($referral);
        return (bool) $returner;
    }

    /**
     * Delete a referral.
     * @param Referral $referral
     * @return bool
     */
    public function deleteReferral($referral) {
        return $this->deleteReferralById((int) $referral->getId());
    }

    /**
     * Delete a referral by referral ID.
     * @param int $referralId
     * @return bool
     */
    public function deleteReferralById($referralId) {
        $referralId = (int) $referralId;
        $this->update('DELETE FROM referral_settings WHERE referral_id = ?', [$referralId]);
        return $this->update('DELETE FROM referrals WHERE referral_id = ?', [$referralId]);
    }

    /**
     * Retrieve an iterator of referrals for a particular user ID,
     * optionally filtering by status.
     * @param int $userId
     * @param int $journalId
     * @param int|null $status
     * @param DBResultRange|null $rangeInfo
     * @return DAOResultFactory
     */
    public function getByUserId($userId, $journalId, $status = null, $rangeInfo = null) {
        $params = [(int) $userId, (int) $journalId];
        if ($status !== null) {
            $params[] = (int) $status;
        }
        
        $result = $this->retrieveRange(
            'SELECT r.*
            FROM referrals r,
                articles a
            WHERE r.article_id = a.article_id AND
                a.user_id = ? AND
                a.journal_id = ?' .
                ($status !== null ? ' AND r.status = ?' : '') . '
            ORDER BY r.date_added',
            $params,
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnReferralFromRow');
    }

    /**
     * Retrieve an iterator of published referrals for a particular user article.
     * @param int $articleId
     * @param DBResultRange|null $rangeInfo
     * @return DAOResultFactory
     */
    public function getPublishedReferralsForArticle($articleId, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT r.*
            FROM referrals r
            WHERE r.article_id = ? AND
                r.status = ?',
            [(int) $articleId, REFERRAL_STATUS_ACCEPT],
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnReferralFromRow');
    }

    /**
     * Get the ID of the last inserted referral.
     * @return int
     */
    public function getInsertObjectId() {
        return $this->getInsertId('referrals', 'referral_id');
    }
    
}
?>