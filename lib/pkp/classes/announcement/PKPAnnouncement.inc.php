<?php
declare(strict_types=1);

/**
 * @defgroup announcement
 */

/**
 * @file classes/announcement/PKPAnnouncement.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPAnnouncement
 * @ingroup announcement
 * @see AnnouncementDAO, PKPAnnouncementDAO
 *
 * @brief Basic class describing a announcement.
 *
 * WIZDAM MODERNIZATION:
 * - PHP 8.x Compatibility (Constructor)
 * - Strict SHIM
 * - Visibility explicit
 * - Safe Date Handling
 */

define('ANNOUNCEMENT_EXPIRE_YEAR_OFFSET_FUTURE',    '+10');

class PKPAnnouncement extends DataObject {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPAnnouncement() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class PKPAnnouncement uses deprecated constructor parent::PKPAnnouncement(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    //
    // Get/set methods
    //

    /**
     * Get the ID of the announcement.
     * @return int
     */
    public function getAnnouncementId() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->getId();
    }

    /**
     * Set the ID of the announcement.
     * @param int $announcementId
     */
    public function setAnnouncementId($announcementId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->setId($announcementId);
    }

    /**
     * Get assoc ID for this annoucement.
     * @return int
     */
    public function getAssocId() {
        return $this->getData('assocId');
    }

    /**
     * Set assoc ID for this annoucement.
     * @param int $assocId
     */
    public function setAssocId($assocId) {
        return $this->setData('assocId', $assocId);
    }

    /**
     * Get assoc type for this annoucement.
     * @return int
     */
    public function getAssocType() {
        return $this->getData('assocType');
    }

    /**
     * Set assoc type for this annoucement.
     * @param int $assocType
     */
    public function setAssocType($assocType) {
        return $this->setData('assocType', $assocType);
    }

    /**
     * Get the announcement type of the announcement.
     * @return int
     */
    public function getTypeId() {
        return $this->getData('typeId');
    }

    /**
     * Set the announcement type of the announcement.
     * @param int $typeId
     */
    public function setTypeId($typeId) {
        return $this->setData('typeId', $typeId);
    }

    /**
     * Get the announcement type name of the announcement.
     * @return string
     */
    public function getAnnouncementTypeName() {
        $announcementTypeDao = DAORegistry::getDAO('AnnouncementTypeDAO');
        return $announcementTypeDao->getAnnouncementTypeName($this->getData('typeId'));
    }

    /**
     * Get localized announcement title
     * @return string
     */
    public function getLocalizedTitle() {
        return $this->getLocalizedData('title');
    }

    /**
     * @deprecated
     */
    public function getAnnouncementTitle() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->getLocalizedTitle();
    }

    /**
     * Get full localized announcement title including type name
     * @return string
     */
    public function getLocalizedTitleFull() {
        $typeName = $this->getAnnouncementTypeName();
        $title = $this->getLocalizedTitle();

        if (!empty($typeName)) {
            return $typeName . ': ' . $title;
        } else {
            return $title;
        }
    }

    /**
     * @deprecated
     */
    public function getAnnouncementTitleFull() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->getLocalizedTitleFull();
    }

    /**
     * Get announcement title.
     * @param string $locale
     * @return string
     */
    public function getTitle($locale) {
        return $this->getData('title', $locale);
    }

    /**
     * Set announcement title.
     * @param string $title
     * @param string $locale
     */
    public function setTitle($title, $locale) {
        return $this->setData('title', $title, $locale);
    }

    /**
     * Get localized short description
     * @return string
     */
    public function getLocalizedDescriptionShort() {
        return $this->getLocalizedData('descriptionShort');
    }

    /**
     * @deprecated
     */
    public function getAnnouncementDescriptionShort() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->getLocalizedDescriptionShort();
    }

    /**
     * Get announcement brief description.
     * @param string $locale
     * @return string
     */
    public function getDescriptionShort($locale) {
        return $this->getData('descriptionShort', $locale);
    }

    /**
     * Set announcement brief description.
     * @param string $descriptionShort
     * @param string $locale
     */
    public function setDescriptionShort($descriptionShort, $locale) {
        return $this->setData('descriptionShort', $descriptionShort, $locale);
    }

    /**
     * Get localized full description
     * @return string
     */
    public function getLocalizedDescription() {
        return $this->getLocalizedData('description');
    }

    /**
     * @deprecated
     */
    public function getAnnouncementDescription() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->getLocalizedDescription();
    }

    /**
     * Get announcement description.
     * @param string $locale
     * @return string
     */
    public function getDescription($locale) {
        return $this->getData('description', $locale);
    }

    /**
     * Set announcement description.
     * @param string $description
     * @param string $locale
     */
    public function setDescription($description, $locale) {
        return $this->setData('description', $description, $locale);
    }

    /**
     * Get announcement expiration date.
     * @return string (YYYY-MM-DD)
     */
    public function getDateExpire() {
        return $this->getData('dateExpire');
    }

    /**
     * Set announcement expiration date.
     * @param string $dateExpire (YYYY-MM-DD)
     */
    public function setDateExpire($dateExpire) {
        return $this->setData('dateExpire', $dateExpire);
    }

    /**
     * Get announcement posted date.
     * @return string (YYYY-MM-DD)
     */
    public function getDatePosted() {
        // PHP 8 Safety: Handle null datePosted
        $datePosted = $this->getData('datePosted');
        if (!$datePosted) return null;
        return date('Y-m-d', strtotime($datePosted));
    }

    /**
     * Get announcement posted datetime.
     * @return string (YYYY-MM-DD HH:MM:SS)
     */
    public function getDatetimePosted() {
        return $this->getData('datePosted');
    }

    /**
     * Set announcement posted date.
     * @param string $datePosted (YYYY-MM-DD)
     */
    public function setDatePosted($datePosted) {
        return $this->setData('datePosted', $datePosted);
    }

    /**
     * Set announcement posted datetime.
     * @param string $datetimePosted (YYYY-MM-DD HH:MM:SS)
     */
    public function setDatetimePosted($datetimePosted) {
        return $this->setData('datePosted', $datetimePosted);
    }
}

?>