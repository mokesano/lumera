<?php
declare(strict_types=1);

/**
 * @file classes/announcement/PKPAnnouncementType.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPAnnouncementType
 * @ingroup announcement
 * @see AnnouncementTypeDAO, AnnouncementTypeForm, PKPAnnouncementTypeDAO, PKPAnnouncementTypeForm
 *
 * @brief Basic class describing an announcement type.
 *
 * WIZDAM MODERNIZATION:
 * - PHP 8.x Compatibility (Constructor)
 * - Strict SHIM
 * - Visibility explicit
 */

class PKPAnnouncementType extends DataObject {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPAnnouncementType() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class PKPAnnouncementType uses deprecated constructor parent::PKPAnnouncementType(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    //
    // Get/set methods
    //

    /**
     * Get the ID of the announcement type.
     * @return int
     */
    public function getTypeId() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->getId();
    }

    /**
     * Set the ID of the announcement type.
     * @param int $typeId
     */
    public function setTypeId($typeId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->setId($typeId);
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
     * Set assoc Type for this annoucement.
     * @param int $assocType
     */
    public function setAssocType($assocType) {
        return $this->setData('assocType', $assocType);
    }

    /**
     * Get the type of the announcement type.
     * @return string
     */
    public function getLocalizedTypeName() {
        return $this->getLocalizedData('name');
    }

    /**
     * @deprecated
     */
    public function getAnnouncementTypeName() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->getLocalizedTypeName();
    }

    /**
     * Get the type of the announcement type.
     * @param string $locale
     * @return string
     */
    public function getName($locale) {
        return $this->getData('name', $locale);
    }

    /**
     * Set the type of the announcement type.
     * @param string $name
     * @param string $locale
     */
    public function setName($name, $locale) {
        return $this->setData('name', $name, $locale);
    }
}

?>