<?php
declare(strict_types=1);

/**
 * @defgroup gift
 */

/**
 * @file classes/gift/PKPGift.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPGift
 * @ingroup gift
 * @see GiftDAO, PKPGiftDAO
 *
 * @brief Basic class describing a gift.
 */

define('GIFT_STATUS_AWAITING_MANUAL_PAYMENT', 0x01);
define('GIFT_STATUS_AWAITING_ONLINE_PAYMENT', 0x02);
define('GIFT_STATUS_NOT_REDEEMED', 0x03);
define('GIFT_STATUS_REDEEMED', 0x04);
define('GIFT_STATUS_OTHER', 0x10);

class PKPGift extends DataObject {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPGift() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::PKPGift(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    //
    // Get/set methods
    //

    /**
     * Get assoc type for this gift.
     * @return int
     */
    public function getAssocType() {
        return $this->getData('assocType');
    }

    /**
     * Set assoc type for this gift.
     * @param $assocType int
     */
    public function setAssocType($assocType) {
        return $this->setData('assocType', $assocType);
    }

    /**
     * Get assoc ID for this gift.
     * @return int
     */
    public function getAssocId() {
        return $this->getData('assocId');
    }

    /**
     * Set assoc ID for this gift.
     * @param $assocId int
     */
    public function setAssocId($assocId) {
        return $this->setData('assocId', $assocId);
    }

    /**
     * Get the gift status of the gift.
     * @return int
     */
    public function getStatus() {
        return $this->getData('status');
    }

    /**
     * Set the gift status of the gift.
     * @param $status int
     */
    public function setStatus($status) {
        return $this->setData('status', $status);
    }

    /**
     * Get the gift type of the gift.
     * @return int
     */
    public function getGiftType() {
        return $this->getData('giftType');
    }

    /**
     * Set the gift type of the gift.
     * @param $type int
     */
    public function setGiftType($type) {
        return $this->setData('giftType', $type);
    }

    /**
     * Get the name of the gift based on gift type.
     * @param $locale string
     * @return string
     */
    public function getGiftName($locale = null) {
        // Must be implemented by sub-classes
        assert(false);
    }

    /**
     * Get the gift assoc id.
     * @return in
     */
    public function getGiftAssocId() {
        return $this->getData('giftAssocId');
    }

    /**
     * Set the gift assoc id.
     * @param $giftAssocId int
     */
    public function setGiftAssocId($giftAssocId) {
        return $this->setData('giftAssocId', $giftAssocId);
    }

    /**
     * Get the gift buyer first name.
     * @return string
     */
    public function getBuyerFirstName() {
        return $this->getData('buyerFirstName');
    }

    /**
     * Set the gift buyer first name.
     * @param $buyerFirstName string
     */
    public function setBuyerFirstName($buyerFirstName) {
        return $this->setData('buyerFirstName', $buyerFirstName);
    }

    /**
     * Get the gift buyer middle name.
     * @return string
     */
    public function getBuyerMiddleName() {
        return $this->getData('buyerMiddleName');
    }

    /**
     * Set the gift buyer middle name.
     * @param $buyerMiddleName string
     */
    public function setBuyerMiddleName($buyerMiddleName) {
        return $this->setData('buyerMiddleName', $buyerMiddleName);
    }

    /**
     * Get the gift buyer last name.
     * @return string
     */
    public function getBuyerLastName() {
        return $this->getData('buyerLastName');
    }

    /**
     * Set the gift buyer last name.
     * @param $buyerLastName string
     */
    public function setBuyerLastName($buyerLastName) {
        return $this->setData('buyerLastName', $buyerLastName);
    }

    /**
     * Get the buyer's complete name.
     * Includes first name, middle name (if applicable), and last name.
     * @param $lastFirst boolean return in "LastName, FirstName" format
     * @return string
     */
    public function getBuyerFullName($lastFirst = false) {
        $firstName = $this->getData('buyerFirstName');
        $middleName = $this->getData('buyerMiddleName');
        $lastName = $this->getData('buyerLastName');
        if ($lastFirst) {
            return "$lastName, " . "$firstName" . ($middleName != ''?" $middleName":'');
        } else {
            return "$firstName " . ($middleName != ''?"$middleName ":'') . $lastName;
        }
    }

    /**
     * Get the gift buyer email.
     * @return string
     */
    public function getBuyerEmail() {
        return $this->getData('buyerEmail');
    }

    /**
     * Set the gift buyer email.
     * @param $buyerEmail string
     */
    public function setBuyerEmail($buyerEmail) {
        return $this->setData('buyerEmail', $buyerEmail);
    }

    /**
     * Get the gift buyer user id .
     * @return int
     */
    public function getBuyerUserId() {
        return $this->getData('buyerUserId');
    }

    /**
     * Set the gift buyer user id.
     * @param $userId int
     */
    public function setBuyerUserId($userId) {
        return $this->setData('buyerUserId', $userId);
    }

    /**
     * Get the gift recipient first name.
     * @return string
     */
    public function getRecipientFirstName() {
        return $this->getData('recipientFirstName');
    }

    /**
     * Set the gift recipient first name.
     * @param $recipientFirstName string
     */
    public function setRecipientFirstName($recipientFirstName) {
        return $this->setData('recipientFirstName', $recipientFirstName);
    }

    /**
     * Get the gift recipient middle name.
     * @return string
     */
    public function getRecipientMiddleName() {
        return $this->getData('recipientMiddleName');
    }

    /**
     * Set the gift recipient middle name.
     * @param $recipientMiddleName string
     */
    public function setRecipientMiddleName($recipientMiddleName) {
        return $this->setData('recipientMiddleName', $recipientMiddleName);
    }

    /**
     * Get the gift recipient last name.
     * @return string
     */
    public function getRecipientLastName() {
        return $this->getData('recipientLastName');
    }

    /**
     * Set the gift recipient last name.
     * @param $recipientLastName string
     */
    public function setRecipientLastName($recipientLastName) {
        return $this->setData('recipientLastName', $recipientLastName);
    }

    /**
     * Get the recipient's complete name.
     * Includes first name, middle name (if applicable), and last name.
     * @param $lastFirst boolean return in "LastName, FirstName" format
     * @return string
     */
    public function getRecipientFullName($lastFirst = false) {
        $firstName = $this->getData('recipientFirstName');
        $middleName = $this->getData('recipientMiddleName');
        $lastName = $this->getData('recipientLastName');
        if ($lastFirst) {
            return "$lastName, " . "$firstName" . ($middleName != ''?" $middleName":'');
        } else {
            return "$firstName " . ($middleName != ''?"$middleName ":'') . $lastName;
        }
    }

    /**
     * Get the gift recipient email.
     * @return string
     */
    public function getRecipientEmail() {
        return $this->getData('recipientEmail');
    }

    /**
     * Set the gift recipient email.
     * @param $recipientEmail string
     */
    public function setRecipientEmail($recipientEmail) {
        return $this->setData('recipientEmail', $recipientEmail);
    }

    /**
     * Get the gift recipient user id .
     * @return int
     */
    public function getRecipientUserId() {
        return $this->getData('recipientUserId');
    }

    /**
     * Set the gift recipient user id.
     * @param $userId int
     */
    public function setRecipientUserId($userId) {
        return $this->setData('recipientUserId', $userId);
    }

    /**
     * Get locale.
     * @return string
     */
    public function getLocale() {
        return $this->getData('locale');
    }

    /**
     * Set locale.
     * @param $locale string
     */
    public function setLocale($locale) {
        return $this->setData('locale', $locale);
    }

    /**
     * Get the gift note title from buyer.
     * @return string
     */
    public function getGiftNoteTitle() {
        return $this->getData('giftNoteTitle');
    }

    /**
     * Set the gift note title from buyer.
     * @param $giftNote string
     */
    public function setGiftNoteTitle($giftNoteTitle) {
        return $this->setData('giftNoteTitle', $giftNoteTitle);
    }

    /**
     * Get the gift note from buyer.
     * @return string
     */
    public function getGiftNote() {
        return $this->getData('giftNote');
    }

    /**
     * Set the gift note from buyer.
     * @param $giftNote string
     */
    public function setGiftNote($giftNote) {
        return $this->setData('giftNote', $giftNote);
    }

    /**
     * Get the gift admin notes.
     * @return string
     */
    public function getNotes() {
        return $this->getData('notes');
    }

    /**
     * Set the gift admin notes.
     * @param $notes string
     */
    public function setNotes($notes) {
        return $this->setData('notes', $notes);
    }

    /**
     * Get gift redeemed datetime.
     * @return datetime (YYYY-MM-DD HH:MM:SS)
     */
    public function getDatetimeRedeemed() {
        return $this->getData('dateRedeemed');
    }

    /**
     * Set gift redeemed datetime.
     * @param $datetimeRedeemed datetime (YYYY-MM-DD HH:MM:SS)
     */
    public function setDatetimeRedeemed($datetimeRedeemed) {
        return $this->setData('dateRedeemed', $datetimeRedeemed);
    }
}

?>