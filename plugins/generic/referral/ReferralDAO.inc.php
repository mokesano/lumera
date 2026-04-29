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
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

class ReferralDAO extends DAO {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ReferralDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::ReferralDAO(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Retrieve an referral by referral ID.
     * @param int $referralId
     * @return Referral|null
     */
    public function getReferral($referralId) {
        $result = $this->retrieve(
            'SELECT * FROM referrals WHERE referral_id = ?', 
            (int) $referralId
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnReferralFromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        return $returner;
    }

    /**
     * Get a list of localized field names
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
        $referral->setId($row['referral_id']);
        $referral->setArticleId($row['article_id']);
        $referral->setStatus($row['status']);
        $referral->setURL($row['url']);
        $referral->setDateAdded($this->datetimeFromDB($row['date_added']));
        $referral->setLinkCount($row['link_count']);

        $this->getDataObjectSettings('referral_settings', 'referral_id', $row['referral_id'], $referral);

        return $referral;
    }

    /**
     * Check if a referrer exists with the given article and URL.
     * @param int $articleId
     * @param string $url
     * @return boolean
     */
    public function referralExistsByUrl($articleId, $url) {
        $result = $this->retrieve(
            'SELECT COUNT(*) FROM referrals WHERE article_id = ? AND url = ?',
            [
                (int) $articleId,
                $url
            ]
        );
        $returner = isset($result->fields[0]) && $result->fields[0] != 0 ? true : false;

        $result->Close();
        return $returner;
    }

    /**
     * Increment the referral count.
     * @param int $articleId
     * @param string $url
     * @return int 1 iff the referral exists
     */
    public function incrementReferralCount($articleId, $url) {
        return $this->update(
            'UPDATE referrals SET link_count = link_count + 1 WHERE article_id = ? AND url = ?',
            [(int) $articleId, $url]
        );
    }

    /**
     * Update the localized settings for this object
     * @param Referral $referral
     */
    public function updateLocaleFields(&$referral) {
        $this->updateDataObjectSettings('referral_settings', $referral, [
            'referral_id' => $referral->getId()
        ]);
    }

    /**
     * Insert a new Referral or replace the Referral if it already exists
     * @param Referral $referral
     * @return int
     */
    public function replaceReferral(&$referral) {
        $date = trim($this->datetimeToDB($referral->getDateAdded()), "'");
        $result = $this->replace(
            'referrals',
            [
                'status' => (int) $referral->getStatus(),
                'article_id' => (int) $referral->getArticleId(),
                'url' => $referral->getURL(),
                'date_added' => $date,
                'link_count' => (int) $referral->getLinkCount(),
            ],
            ['article_id', 'url']
        );

        if ($result == 2) { // ADODB magic number: 2 means successful new insert
            $referral->setId($this->getInsertObjectId());
        }

        $this->updateLocaleFields($referral);
        return $referral->getId();
    }

    /**
     * Update an existing referral.
     * @param Referral $referral
     * @return boolean
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
                $referral->getURL(),
                (int) $referral->getLinkCount(),
                (int) $referral->getId()
            ]
        );
        $this->updateLocaleFields($referral);
        return $returner;
    }

    /**
     * Delete a referral.
     * @param Referral $referral
     * @return boolean
     */
    public function deleteReferral($referral) {
        return $this->deleteReferralById($referral->getId());
    }

    /**
     * Delete a referral by referral ID.
     * @param int $referralId
     * @return boolean
     */
    public function deleteReferralById($referralId) {
        $this->update('DELETE FROM referral_settings WHERE referral_id = ?', (int) $referralId);
        return $this->update('DELETE FROM referrals WHERE referral_id = ?', (int) $referralId);
    }

    /**
     * Retrieve an iterator of referrals for a particular user ID,
     * optionally filtering by status.
     * @param int $userId
     * @param int $journalId
     * @param int $status
     * @return DAOResultFactory containing matching Referrals
     */
    public function getByUserId($userId, $journalId, $status = null, $rangeInfo = null) {
        $params = [(int) $userId, (int) $journalId];
        if ($status !== null) $params[] = (int) $status;
        
        $result = $this->retrieveRange(
            'SELECT r.*
            FROM referrals r,
                articles a
            WHERE r.article_id = a.article_id AND
                a.user_id = ? AND
                a.journal_id = ?' .
                ($status !== null?' AND r.status = ?':'') . '
            ORDER BY r.date_added',
            $params,
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnReferralFromRow');
    }

    /**
     * Retrieve an iterator of published referrals for a particular user article
     * @param int $articleId
     * @param object $rangeInfo
     * @return DAOResultFactory containing matching Referrals
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