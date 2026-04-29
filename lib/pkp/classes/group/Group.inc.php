<?php
declare(strict_types=1);

/**
 * @defgroup group
 */

/**
 * @file classes/group/Group.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Group
 * @ingroup group
 * @see GroupDAO
 *
 * @brief Describes user groups.
 */

define('GROUP_CONTEXT_EDITORIAL_TEAM',    0x000001);
define('GROUP_CONTEXT_PEOPLE',        0x000002);

class Group extends DataObject {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Legacy Constructor Shim.
     */
    public function Group() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::Group(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Get localized title of group.
     */
    public function getLocalizedTitle() {
        return $this->getLocalizedData('title');
    }

    public function getGroupTitle() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedTitle();
    }


    //
    // Get/set methods
    //
    /**
     * Get title of group (primary locale)
     * @param $locale string
     * @return string
     */
    public function getTitle($locale) {
        return $this->getData('title', $locale);
    }

    /**
     * Set title of group
     * @param $title string
     * @param $locale string
     */
    public function setTitle($title, $locale) {
        return $this->setData('title', $title, $locale);
    }

    /**
     * Get context of group
     * @return int
     */
    public function getContext() {
        return $this->getData('context');
    }

    /**
     * Set context of group
     * @param $context int
     */
    public function setContext($context) {
        return $this->setData('context',$context);
    }

    /**
     * Get publish email flag
     * @return int
     */
    public function getPublishEmail() {
        return $this->getData('publishEmail');
    }

    /**
     * Set publish email flag
     * @param $publishEmail int
     */
    public function setPublishEmail($publishEmail) {
        return $this->setData('publishEmail',$publishEmail);
    }

    /**
     * Get flag indicating whether or not the group is displayed in "About"
     * @return boolean
     */
    public function getAboutDisplayed() {
        return $this->getData('aboutDisplayed');
    }

    /**
     * Set flag indicating whether or not the group is displayed in "About"
     * @param $aboutDisplayed boolean
     */
    public function setAboutDisplayed($aboutDisplayed) {
        return $this->setData('aboutDisplayed',$aboutDisplayed);
    }

    /**
     * Get ID of group. Deprecated in favour of getId.
     * @return int
     */
    public function getGroupId() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getId();
    }

    /**
     * Set ID of group. DEPRECATED in favour of setId.
     * @param $groupId int
     */
    public function setGroupId($groupId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->setId($groupId);
    }

    /**
     * Get assoc ID for this group.
     * @return int
     */
    public function getAssocId() {
        return $this->getData('assocId');
    }

    /**
     * Set assoc ID for this group.
     * @param $assocId int
     */
    public function setAssocId($assocId) {
        return $this->setData('assocId', $assocId);
    }

    /**
     * Get assoc type for this group.
     * @return int
     */
    public function getAssocType() {
        return $this->getData('assocType');
    }

    /**
     * Set assoc type for this group.
     * @param $assocType int
     */
    public function setAssocType($assocType) {
        return $this->setData('assocType', $assocType);
    }

    /**
     * Get sequence of group.
     * @return float
     */
    public function getSequence() {
        return $this->getData('sequence');
    }

    /**
     * Set sequence of group.
     * @param $sequence float
     */
    public function setSequence($sequence) {
        return $this->setData('sequence', $sequence);
    }
}

?>