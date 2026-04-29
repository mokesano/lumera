<?php
declare(strict_types=1);

/**
 * @file plugins/generic/referral/Referral.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Referral
 * @ingroup plugins_generic_referral
 * @see ReferralDAO
 *
 * @brief Basic class describing a referral.
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

define('REFERRAL_STATUS_NEW',        0x00000001);
define('REFERRAL_STATUS_ACCEPT',     0x00000002);
define('REFERRAL_STATUS_DECLINE',    0x00000003);

class Referral extends DataObject {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Referral() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::Referral(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    //
    // Get/set methods
    //

    /**
     * Get the article ID of the referral.
     * @return int
     */
    public function getArticleId() {
        return $this->getData('articleId');
    }

    /**
     * Set the article ID of the referral.
     * @param int $articleId
     */
    public function setArticleId($articleId) {
        return $this->setData('articleId', $articleId);
    }

    /**
     * Get the URL of the referral.
     * @return string
     */
    public function getURL() {
        return $this->getData('url');
    }

    /**
     * Set the URL of the referral.
     * @param string $url
     */
    public function setURL($url) {
        return $this->setData('url', $url);
    }

    /**
     * Get the status flag of the referral (REFERRAL_STATUS_...).
     * @return int
     */
    public function getStatus() {
        return $this->getData('status');
    }

    /**
     * Get the locale key corresponding to this referral's status
     * @return string
     */
    public function getStatusKey() {
        switch ($this->getStatus()) {
            case REFERRAL_STATUS_NEW: return 'plugins.generic.referral.status.new';
            case REFERRAL_STATUS_ACCEPT: return 'plugins.generic.referral.status.accept';
            case REFERRAL_STATUS_DECLINE: return 'plugins.generic.referral.status.decline';
        }
        return '';
    }

    /**
     * Set the status flag of the referral.
     * @param int $status REFERRAL_STATUS_...
     */
    public function setStatus($status) {
        return $this->setData('status', $status);
    }

    /**
     * Get the date added of the referral.
     * @return string
     */
    public function getDateAdded() {
        return $this->getData('dateAdded');
    }

    /**
     * Set the date added of the referral.
     * @param string $dateAdded
     */
    public function setDateAdded($dateAdded) {
        return $this->setData('dateAdded', $dateAdded);
    }

    /**
     * Get the name of the referral.
     * @return string
     */
    public function getReferralName() {
        return $this->getLocalizedData('name');
    }

    /**
     * Get the name of the referral.
     * @param string $locale
     * @return string
     */
    public function getName($locale) {
        return $this->getData('name', $locale);
    }

    /**
     * Set the name of the referral.
     * @param string $name
     * @param string $locale
     */
    public function setName($name, $locale) {
        return $this->setData('name', $name, $locale);
    }

    /**
     * Get the link count of the referral.
     * @return int
     */
    public function getLinkCount() {
        return $this->getData('linkCount');
    }

    /**
     * Set the link count of the referral.
     * @param int $linkCount
     */
    public function setLinkCount($linkCount) {
        return $this->setData('linkCount', $linkCount);
    }
}

?>