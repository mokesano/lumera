<?php
declare(strict_types=1);

/**
 * @file classes/gift/PKPGiftDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPGiftDAO
 * @ingroup gift
 * @see Gift, PKPGift
 *
 * @brief Operations for retrieving and modifying Gift objects.
 */

import('lib.pkp.classes.gift.PKPGift');

define('GIFT_REDEEM_STATUS_SUCCESS', 0x01);
define('GIFT_REDEEM_STATUS_ERROR_GIFT_INVALID', 0x2);
define('GIFT_REDEEM_STATUS_ERROR_NO_GIFT_TO_REDEEM', 0x3);
define('GIFT_REDEEM_STATUS_ERROR_GIFT_ALREADY_REDEEMED', 0x4);

class PKPGiftDAO extends DAO {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPGiftDAO() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::PKPGiftDAO(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Retrieve a gift by gift ID.
     * @param $giftId int
     * @return Gift object
     */
    public function getGift($giftId) { // Menghapus reference (&)
        $result = $this->retrieve( // Menghapus reference (&)
            'SELECT * FROM gifts WHERE gift_id = ?', $giftId
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnGiftFromRow($result->GetRowAssoc(false)); // Menghapus reference (&)
        }
        $result->Close();
        return $returner;
    }

    /**
     * Retrieve gift assoc ID by gift ID.
     * @param $giftId int
     * @return int
     */
    public function getGiftAssocId($giftId) {
        $result = $this->retrieve( // Menghapus reference (&)
            'SELECT assoc_id FROM gifts WHERE gift_id = ?', $giftId
        );

        $returner = isset($result->fields[0]) ? $result->fields[0] : 0;
        $result->Close(); // Pastikan result ditutup
        unset($result);
        return $returner;
    }

    /**
     * Retrieve gift assoc type by gift ID.
     * @param $giftId int
     * @return int
     */
    public function getGiftAssocType($giftId) {
        $result = $this->retrieve( // Menghapus reference (&)
            'SELECT assoc_type FROM gifts WHERE gift_id = ?', $giftId
        );

        $returner = isset($result->fields[0]) ? $result->fields[0] : 0;
        $result->Close(); // Pastikan result ditutup
        unset($result);
        return $returner;
    }

    /**
     * Internal function to return a Gift object from a row.
     * @param $row array
     * @return Gift object
     */
    public function _returnGiftFromRow($row) { // Menghapus reference (&) pada return dan parameter
        $gift = $this->newDataObject();
        $gift->setId($row['gift_id']);
        $gift->setAssocType($row['assoc_type']);
        $gift->setAssocId($row['assoc_id']);
        $gift->setStatus($row['status']);
        $gift->setGiftType($row['gift_type']);
        $gift->setGiftAssocId($row['gift_assoc_id']);
        $gift->setBuyerFirstName($row['buyer_first_name']);
        $gift->setBuyerMiddleName($row['buyer_middle_name']);
        $gift->setBuyerLastName($row['buyer_last_name']);
        $gift->setBuyerEmail($row['buyer_email']);
        $gift->setBuyerUserId($row['buyer_user_id']);
        $gift->setRecipientFirstName($row['recipient_first_name']);
        $gift->setRecipientMiddleName($row['recipient_middle_name']);
        $gift->setRecipientLastName($row['recipient_last_name']);
        $gift->setRecipientEmail($row['recipient_email']);
        $gift->setRecipientUserId($row['recipient_user_id']);
        $gift->setDatetimeRedeemed($this->datetimeFromDB($row['date_redeemed']));
        $gift->setLocale($row['locale']);
        $gift->setGiftNoteTitle($row['gift_note_title']);
        $gift->setGiftNote($row['gift_note']);
        $gift->setNotes($row['notes']);

        HookRegistry::call('PKPNoteDAO::_returnGiftFromRow', array(&$gift, $row)); // $row tidak lagi reference

        return $gift;
    }

    /**
     * Insert a new Gift.
     * @param $gift Gift object
     * @return int
     */
    public function insertObject($gift) { // Menghapus reference (&)
        $this->update(
            sprintf('INSERT INTO gifts
                (assoc_type,
                assoc_id,
                status,
                gift_type,
                gift_assoc_id,
                buyer_first_name,
                buyer_middle_name,
                buyer_last_name,
                buyer_email,
                buyer_user_id,
                recipient_first_name,
                recipient_middle_name,
                recipient_last_name,
                recipient_email,
                recipient_user_id,
                locale,
                gift_note_title,
                gift_note,
                notes,
                date_redeemed)
                VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, %s)',
                $this->datetimeToDB($gift->getDatetimeRedeemed())),
            array(
                $gift->getAssocType(),
                $gift->getAssocId(),
                $gift->getStatus(),
                $gift->getGiftType(),
                $gift->getGiftAssocId(),
                $gift->getBuyerFirstName(),
                $gift->getBuyerMiddleName(),
                $gift->getBuyerLastName(),
                $gift->getBuyerEmail(),
                $gift->getBuyerUserId(),
                $gift->getRecipientFirstName(),
                $gift->getRecipientMiddleName(),
                $gift->getRecipientLastName(),
                $gift->getRecipientEmail(),
                $gift->getRecipientUserId(),
                $gift->getLocale(),
                $gift->getGiftNoteTitle(),
                $gift->getGiftNote(),
                $gift->getNotes()
            )
        );
        $gift->setId($this->getInsertGiftId());
        return $gift->getId();
    }

    /**
     * Update an existing gift.
     * @param $gift Gift
     * @return boolean
     */
    public function updateObject($gift) { // Menghapus reference (&)
        $returner = $this->update(
            sprintf('UPDATE gifts
                SET
                    assoc_type = ?,
                    assoc_id = ?,
                    status = ?,
                    gift_type = ?,
                    gift_assoc_id = ?,
                    buyer_first_name = ?,
                    buyer_middle_name = ?,
                    buyer_last_name = ?,
                    buyer_email = ?,
                    buyer_user_id = ?,
                    recipient_first_name = ?,
                    recipient_middle_name = ?,
                    recipient_last_name = ?,
                    recipient_email = ?,
                    recipient_user_id = ?,
                    locale = ?,
                    gift_note_title = ?,
                    gift_note = ?,
                    notes = ?,
                    date_redeemed = %s
                WHERE gift_id = ?',
                $this->datetimeToDB($gift->getDatetimeRedeemed())),
            array(
                $gift->getAssocType(),
                $gift->getAssocId(),
                $gift->getStatus(),
                $gift->getGiftType(),
                $gift->getGiftAssocId(),
                $gift->getBuyerFirstName(),
                $gift->getBuyerMiddleName(),
                $gift->getBuyerLastName(),
                $gift->getBuyerEmail(),
                $gift->getBuyerUserId(),
                $gift->getRecipientFirstName(),
                $gift->getRecipientMiddleName(),
                $gift->getRecipientLastName(),
                $gift->getRecipientEmail(),
                $gift->getRecipientUserId(),
                $gift->getLocale(),
                $gift->getGiftNoteTitle(),
                $gift->getGiftNote(),
                $gift->getNotes(),
                $gift->getId()
            )
        );
        return $returner;
    }

    /**
     * Delete a gift.
     * @param $gift Gift
     * @return boolean
     */
    public function deleteObject($gift) {
        return $this->deleteGiftById($gift->getId());
    }

    /**
     * Delete a gift by gift ID.
     * @param $giftId int
     * @return boolean
     */
    public function deleteGiftById($giftId) {
        return $this->update('DELETE FROM gifts WHERE gift_id = ?', $giftId);
    }

    /**
     * Delete gifts by assoc ID
     * @param $assocType int
     * @param $assocId int
     */
    public function deleteGiftsByAssocId($assocType, $assocId) {
        $gifts = $this->getGiftsByAssocId($assocType, $assocId); // Menghapus reference (&)
        while (($gift = $gifts->next())) { // Menghapus reference (&)
            $this->deleteGiftById($gift->getId());
            unset($gift);
        }
        return true;
    }

    /**
     * Retrieve an array of gifts matching a particular assoc ID.
     * @param $assocType int
     * @param $assocId int
     * @return object DAOResultFactory containing matching Gifts
     */
    public function getGiftsByAssocId($assocType, $assocId, $rangeInfo = null) { // Menghapus reference (&)
        $result = $this->retrieveRange( // Menghapus reference (&)
            'SELECT *
            FROM gifts
            WHERE assoc_type = ? AND assoc_id = ?
            ORDER BY gift_id DESC',
            array($assocType, $assocId),
            $rangeInfo
        );

        $returner = new DAOResultFactory($result, $this, '_returnGiftFromRow');
        return $returner; // Menghapus reference (&)
    }

    /**
     * Check if recipient user has a gift.
     * @param $assocType int
     * @param $assocId int
     * @param $userId int
     * @param $giftId int
     * @return boolean
     */
    public function recipientHasGift($assocType, $assocId, $userId, $giftId) {
        $result = $this->retrieve( // Menghapus reference (&)
            'SELECT COUNT(*)
            FROM gifts
            WHERE gift_id = ?
            AND assoc_type = ? AND assoc_id = ?
            AND recipient_user_id = ?',
            array(
                $giftId,
                $assocType,
                $assocId,
                $userId
            )
        );

        $returner = $result->fields[0] ? true : false;

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Check if recipient user has a gift that is unreedemed.
     * @param $assocType int
     * @param $assocId int
     * @param $userId int
     * @param $giftId int
     * @return boolean
     */
    public function recipientHasNotRedeemedGift($assocType, $assocId, $userId, $giftId) {
        $result = $this->retrieve( // Menghapus reference (&)
            'SELECT COUNT(*)
            FROM gifts
            WHERE gift_id = ?
            AND assoc_type = ? AND assoc_id = ?
            AND recipient_user_id = ?
            AND status = ?',
            array(
                $giftId,
                $assocType,
                $assocId,
                $userId,
                GIFT_STATUS_NOT_REDEEMED
            )
        );

        $returner = $result->fields[0] ? true : false;

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Redeem a gift for a recipient user.
     * @param $assocType int
     * @param $assocId int
     * @param $userId int
     * @param $giftId int
     * @return int Status code indicating whether gift could be redeemed
     */
    public function redeemGift($assocType, $assocId, $userId, $giftId) {
        // Must be implemented by sub-classes
        assert(false);
    }

    /**
     * Retrieve an array of all gifts for a recipient user.
     * @param $assocType int
     * @param $userId int
     * @return object DAOResultFactory containing matching Gifts
     */
    public function getAllGiftsByRecipient($assocType, $userId, $rangeInfo = null) { // Menghapus reference (&)
        $result = $this->retrieveRange( // Menghapus reference (&)
            'SELECT *
            FROM gifts
            WHERE assoc_type = ?
            AND recipient_user_id = ?
            ORDER BY gift_id DESC',
            array(
                $assocType,
                $userId
            ),
            $rangeInfo
        );

        $returner = new DAOResultFactory($result, $this, '_returnGiftFromRow');
        return $returner; // Menghapus reference (&)
    }

    /**
     * Retrieve an array of redeemed and unredeemed gifts for a recipient user.
     * @param $assocType int
     * @param $assocId int
     * @param $userId int
     * @return object DAOResultFactory containing matching Gifts
     */
    public function getGiftsByRecipient($assocType, $assocId, $userId, $rangeInfo = null) { // Menghapus reference (&)
        $result = $this->retrieveRange( // Menghapus reference (&)
            'SELECT *
            FROM gifts
            WHERE assoc_type = ? AND assoc_id = ?
            AND recipient_user_id = ?
            AND (status = ? OR status = ?)
            ORDER BY gift_id DESC',
            array(
                $assocType,
                $assocId,
                $userId,
                GIFT_STATUS_NOT_REDEEMED,
                GIFT_STATUS_REDEEMED
            ),
            $rangeInfo
        );

        $returner = new DAOResultFactory($result, $this, '_returnGiftFromRow');
        return $returner; // Menghapus reference (&)
    }

    /**
     * Retrieve an array of redeemed and unredeemed gifts of a certain type for a recipient user.
     * @param $assocType int
     * @param $assocId int
     * @param $giftType int
     * @param $userId int
     * @return object DAOResultFactory containing matching Gifts
     */
    public function getGiftsByTypeAndRecipient($assocType, $assocId, $giftType, $userId, $rangeInfo = null) { // Menghapus reference (&)
        $result = $this->retrieveRange( // Menghapus reference (&)
            'SELECT *
            FROM gifts
            WHERE assoc_type = ? AND assoc_id = ?
            AND recipient_user_id = ? AND gift_type = ?
            AND (status = ? OR status = ?)
            ORDER BY gift_id DESC',
            array(
                $assocType,
                $assocId,
                $userId,
                $giftType,
                GIFT_STATUS_NOT_REDEEMED,
                GIFT_STATUS_REDEEMED
            ),
            $rangeInfo
        );

        $returner = new DAOResultFactory($result, $this, '_returnGiftFromRow');
        return $returner; // Menghapus reference (&)
    }

    /**
     * Retrieve an array of unredeemed gifts for a recipient user.
     * @param $assocType int
     * @param $assocId int
     * @param $userId int
     * @return object DAOResultFactory containing matching Gifts
     */
    public function getNotRedeemedGiftsByRecipient($assocType, $assocId, $userId, $rangeInfo = null) { // Menghapus reference (&)
        $result = $this->retrieveRange( // Menghapus reference (&)
            'SELECT *
            FROM gifts
            WHERE assoc_type = ? AND assoc_id = ?
            AND recipient_user_id = ?
            AND status = ?
            ORDER BY gift_id DESC',
            array(
                $assocType,
                $assocId,
                $userId,
                GIFT_STATUS_NOT_REDEEMED
            ),
            $rangeInfo
        );

        $returner = new DAOResultFactory($result, $this, '_returnGiftFromRow');
        return $returner; // Menghapus reference (&)
    }

    /**
     * Retrieve an array of unredeemed gifts of a certain type for a recipient user.
     * @param $assocType int
     * @param $assocId int
     * @param $giftType int
     * @param $userId int
     * @return object DAOResultFactory containing matching Gifts
     */
    public function getNotRedeemedGiftsByTypeAndRecipient($assocType, $assocId, $giftType, $userId, $rangeInfo = null) { // Menghapus reference (&)
        $result = $this->retrieveRange( // Menghapus reference (&)
            'SELECT *
            FROM gifts
            WHERE assoc_type = ? AND assoc_id = ?
            AND recipient_user_id = ? AND gift_type = ?
            AND status = ?
            ORDER BY gift_id DESC',
            array(
                $assocType,
                $assocId,
                $userId,
                $giftType,
                GIFT_STATUS_NOT_REDEEMED
            ),
            $rangeInfo
        );

        $returner = new DAOResultFactory($result, $this, '_returnGiftFromRow');
        return $returner; // Menghapus reference (&)
    }

    /**
     * Get the ID of the last inserted gift.
     * @return int
     */
    public function getInsertGiftId() {
        return $this->getInsertId('gifts', 'gift_id');
    }
}

?>