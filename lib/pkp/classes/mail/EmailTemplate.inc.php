<?php
declare(strict_types=1);

/**
 * @file classes/mail/EmailTemplate.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BaseEmailTemplate
 * @ingroup mail
 * @see EmailTemplateDAO
 *
 * @brief Describes basic email template properties.
 */

/**
 * Email template base class.
 */
class BaseEmailTemplate extends DataObject {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function BaseEmailTemplate() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::BaseEmailTemplate(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    //
    // Get/set methods
    //

    /**
     * Get association type.
     * @return int
     */
    public function getAssocType() {
        return $this->getData('assocType');
    }

    /**
     * Set association type.
     * @param $assocType int
     */
    public function setAssocType($assocType) {
        return $this->setData('assocType', $assocType);
    }

    /**
     * Get ID of journal/conference/...
     * @return int
     */
    public function getAssocId() {
        return $this->getData('assocId');
    }

    /**
     * Set ID of journal/conference/...
     * @param $assocId int
     */
    public function setAssocId($assocId) {
        return $this->setData('assocId', $assocId);
    }

    /**
     * Determine whether or not this is a custom email template
     * (ie one that was created by the journal/conference/... manager and
     * is not part of the system upon installation)
     */
    public function isCustomTemplate() {
        return false;
    }

    /**
     * Get sender role ID.
     */
    public function getFromRoleId() {
        return $this->getData('fromRoleId');
    }

    /**
     * Get sender role name.
     */
    public function getFromRoleName() {
        // Removed & reference
        $roleDao = DAORegistry::getDAO('RoleDAO');
        return $roleDao->getRoleName($this->getFromRoleId());
    }

    /**
     * Set sender role ID.
     * @param $fromRoleId int
     */
    public function setFromRoleId($fromRoleId) {
        $this->setData('fromRoleId', $fromRoleId);
    }

    /**
     * Get recipient role ID.
     */
    public function getToRoleId() {
        return $this->getData('toRoleId');
    }

    /**
     * Get recipient role name.
     */
    public function getToRoleName() {
        // Removed & reference
        $roleDao = DAORegistry::getDAO('RoleDAO');
        return $roleDao->getRoleName($this->getToRoleId());
    }

    /**
     * Set recipient role ID.
     * @param $toRoleId int
     */
    public function setToRoleId($toRoleId) {
        $this->setData('toRoleId', $toRoleId);
    }

    /**
     * Get ID of email template.
     * @return int
     */
    public function getEmailId() {
        return $this->getData('emailId');
    }

    /**
     * Set ID of email template.
     * @param $emailId int
     */
    public function setEmailId($emailId) {
        return $this->setData('emailId', $emailId);
    }

    /**
     * Get key of email template.
     * @return string
     */
    public function getEmailKey() {
        return $this->getData('emailKey');
    }

    /**
     * Set key of email template.
     * @param $emailKey string
     */
    public function setEmailKey($emailKey) {
        return $this->setData('emailKey', $emailKey);
    }

    /**
     * Get the enabled setting of email template.
     * @return boolean
     */
    public function getEnabled() {
        return $this->getData('enabled');
    }

    /**
     * Set the enabled setting of email template.
     * @param $enabled boolean
     */
    public function setEnabled($enabled) {
        return $this->setData('enabled', $enabled);
    }

    /**
     * Check if email template is allowed to be disabled.
     * @return boolean
     */
    public function getCanDisable() {
        return $this->getData('canDisable');
    }

    /**
     * Set whether or not email template is allowed to be disabled.
     * @param $canDisable boolean
     */
    public function setCanDisable($canDisable) {
        return $this->setData('canDisable', $canDisable);
    }

}


/**
 * Email template with data for all supported locales.
 */
class LocaleEmailTemplate extends BaseEmailTemplate {

    /** @var array of localized email template data */
    public $localeData;

    /** @var boolean */
    public $isCustomTemplate = false; // Added property declaration

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->localeData = array();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function LocaleEmailTemplate() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::LocaleEmailTemplate(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Set whether or not this is a custom template.
     */
    public function setCustomTemplate($isCustomTemplate) {
        $this->isCustomTemplate = $isCustomTemplate;
    }

    /**
     * Determine whether or not this is a custom email template
     * (ie one that was created by the journal/conference/... manager and
     * is not part of the system upon installation)
     */
    public function isCustomTemplate() {
        return $this->isCustomTemplate;
    }

    /**
     * Add a new locale to store data for.
     * @param $locale string
     */
    public function addLocale($locale) {
        $this->localeData[$locale] = array();
    }

    /**
     * Get set of supported locales for this template.
     * @return array
     */
    public function getLocales() {
        return array_keys($this->localeData);
    }

    //
    // Get/set methods
    //

    /**
     * Get description of email template.
     * @param $locale string
     * @return string
     */
    public function getDescription($locale) {
        return isset($this->localeData[$locale]['description']) ? $this->localeData[$locale]['description'] : '';
    }

    /**
     * Set description of email template.
     * @param $locale string
     * @param $description string
     */
    public function setDescription($locale, $description) {
        $this->localeData[$locale]['description'] = $description;
    }

    /**
     * Get subject of email template.
     * @param $locale string
     * @return string
     */
    public function getSubject($locale) {
        return isset($this->localeData[$locale]['subject']) ? $this->localeData[$locale]['subject'] : '';
    }

    /**
     * Set subject of email template.
     * @param $locale string
     * @param $subject string
     */
    public function setSubject($locale, $subject) {
        $this->localeData[$locale]['subject'] = $subject;
    }

    /**
     * Get body of email template.
     * @param $locale string
     * @return string
     */
    public function getBody($locale) {
        return isset($this->localeData[$locale]['body']) ? $this->localeData[$locale]['body'] : '';
    }

    /**
     * Set body of email template.
     * @param $locale string
     * @param $body string
     */
    public function setBody($locale, $body) {
        $this->localeData[$locale]['body'] = $body;
    }
}


/**
 * Email template for a specific locale.
 */
class EmailTemplate extends BaseEmailTemplate {

    /** @var boolean */
    public $isCustomTemplate = false; // Added property declaration

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function EmailTemplate() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::EmailTemplate(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Set whether or not this is a custom template.
     */
    public function setCustomTemplate($isCustomTemplate) {
        $this->isCustomTemplate = $isCustomTemplate;
    }

    /**
     * Determine whether or not this is a custom email template
     * (ie one that was created by the journal/conference/... manager and
     * is not part of the system upon installation)
     */
    public function isCustomTemplate() {
        return $this->isCustomTemplate;
    }

    //
    // Get/set methods
    //

    /**
     * Get locale of email template.
     * @return string
     */
    public function getLocale() {
        return $this->getData('locale');
    }

    /**
     * Set locale of email template.
     * @param $locale string
     */
    public function setLocale($locale) {
        return $this->setData('locale', $locale);
    }

    /**
     * Get subject of email template.
     * @return string
     */
    public function getSubject() {
        return $this->getData('subject');
    }

    /**
     * Set subject of email.
     * @param $subject string
     */
    public function setSubject($subject) {
        return $this->setData('subject', $subject);
    }

    /**
     * Get body of email template.
     * @return string
     */
    public function getBody() {
        return $this->getData('body');
    }

    /**
     * Set body of email template.
     * @param $body string
     */
    public function setBody($body) {
        return $this->setData('body', $body);
    }

}
?>