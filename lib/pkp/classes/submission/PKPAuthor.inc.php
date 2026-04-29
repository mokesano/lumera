<?php
declare(strict_types=1);

/**
 * @file classes/submission/PKPAuthor.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPAuthor
 * @ingroup submission
 * @see PKPAuthorDAO
 *
 * @brief Author metadata class.
 */

class PKPAuthor extends DataObject {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPAuthor() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::PKPAuthor(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Get the author's complete name.
     * Includes first name, middle name (if applicable), and last name.
     * @param $lastFirst boolean False / default: Firstname Middle Lastname
     * If true: Lastname, Firstname Middlename
     * @return string
     */
    public function getFullName($lastFirst = false) {
        if ($lastFirst) return $this->getData('lastName') . ', ' . $this->getData('firstName') . ($this->getData('middleName') != '' ? ' ' . $this->getData('middleName') : '');
        else return $this->getData('firstName') . ' ' . ($this->getData('middleName') != '' ? $this->getData('middleName') . ' ' : '') . $this->getData('lastName') . ($this->getData('suffix') != '' ? ', ' . $this->getData('suffix') : '');
    }

    //
    // Get/set methods
    //

    /**
     * Get ID of author.
     * @return int
     */
    public function getAuthorId() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->getId();
    }

    /**
     * Set ID of author.
     * @param $authorId int
     */
    public function setAuthorId($authorId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->setId($authorId);
    }

    /**
     * Get ID of submission.
     * @return int
     */
    public function getSubmissionId() {
        return $this->getData('submissionId');
    }

    /**
     * Set ID of submission.
     * @param $submissionId int
     */
    public function setSubmissionId($submissionId) {
        return $this->setData('submissionId', $submissionId);
    }

    /**
     * Set the user group id
     * @param $userGroupId int
     */
    public function setUserGroupId($userGroupId) {
        $this->setData('userGroupId', $userGroupId);
    }

    /**
     * Get the user group id
     * @return int
     */
    public function getUserGroupId() {
        return $this->getData('userGroupId');
    }

    /**
     * Get first name.
     * @return string
     */
    public function getFirstName() {
        return $this->getData('firstName');
    }

    /**
     * Set first name.
     * @param $firstName string
     */
    public function setFirstName($firstName) {
        return $this->setData('firstName', $firstName);
    }

    /**
     * Get middle name.
     * @return string
     */
    public function getMiddleName() {
        return $this->getData('middleName');
    }

    /**
     * Set middle name.
     * @param $middleName string
     */
    public function setMiddleName($middleName) {
        return $this->setData('middleName', $middleName);
    }

    /**
     * Get initials.
     * @return string
     */
    public function getInitials() {
        return $this->getData('initials');
    }

    /**
     * Set initials.
     * @param $initials string
     */
    public function setInitials($initials) {
        return $this->setData('initials', $initials);
    }

    /**
     * Get last name.
     * @return string
     */
    public function getLastName() {
        return $this->getData('lastName');
    }

    /**
     * Set last name.
     * @param $lastName string
     */
    public function setLastName($lastName) {
        return $this->setData('lastName', $lastName);
    }

    /**
     * Get suffix.
     * @return string
     */
    public function getSuffix() {
        return $this->getData('suffix');
    }

    /**
     * Set suffix.
     * @param $suffix string
     */
    public function setSuffix($suffix) {
        return $this->setData('suffix', $suffix);
    }

    /**
     * Get user salutation.
     * @return string
     */
    public function getSalutation() {
        return $this->getData('salutation');
    }

    /**
     * Set user salutation.
     * @param $salutation string
     */
    public function setSalutation($salutation) {
        return $this->setData('salutation', $salutation);
    }

    /**
     * Get affiliation (position, institution, etc.).
     * @param $locale string
     * @return string
     */
    public function getAffiliation($locale) {
        return $this->getData('affiliation', $locale);
    }

    /**
     * Set affiliation.
     * @param $affiliation string
     * @param $locale string
     */
    public function setAffiliation($affiliation, $locale) {
        return $this->setData('affiliation', $affiliation, $locale);
    }

    /**
     * Get the localized affiliation for this author
     */
    public function getLocalizedAffiliation() {
        return $this->getLocalizedData('affiliation');
    }

    /**
     * Get country code
     * @return string
     */
    public function getCountry() {
        return $this->getData('country');
    }

    /**
     * Get localized country
     * @return string
     */
    public function getCountryLocalized() {
        // Hapus '&'
        $countryDao = DAORegistry::getDAO('CountryDAO');
        $country = $this->getCountry();
        if ($country) {
            return $countryDao->getCountry($country);
        }
        return null;
    }

    /**
     * Set country code.
     * @param $country string
     */
    public function setCountry($country) {
        return $this->setData('country', $country);
    }

    /**
     * Get email address.
     * @return string
     */
    public function getEmail() {
        return $this->getData('email');
    }

    /**
     * Set email address.
     * @param $email string
     */
    public function setEmail($email) {
        return $this->setData('email', $email);
    }

    /**
     * Get URL.
     * @return string
     */
    public function getUrl() {
        return $this->getData('url');
    }

    /**
     * Set URL.
     * @param $url string
     */
    public function setUrl($url) {
        return $this->setData('url', $url);
    }

    /**
     * Get the localized biography for this author
     */
    public function getLocalizedBiography() {
        return $this->getLocalizedData('biography');
    }

    public function getAuthorBiography() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->getLocalizedBiography();
    }

    /**
     * Get author biography.
     * @param $locale string
     * @return string
     */
    public function getBiography($locale) {
        return $this->getData('biography', $locale);
    }

    /**
     * Set author biography.
     * @param $biography string
     * @param $locale string
     */
    public function setBiography($biography, $locale) {
        return $this->setData('biography', $biography, $locale);
    }

    /**
     * Get primary contact.
     * @return boolean
     */
    public function getPrimaryContact() {
        return $this->getData('primaryContact');
    }

    /**
     * Set primary contact.
     * @param $primaryContact boolean
     */
    public function setPrimaryContact($primaryContact) {
        return $this->setData('primaryContact', $primaryContact);
    }

    /**
     * Get sequence of author in article's author list.
     * @return float
     */
    public function getSequence() {
        return $this->getData('sequence');
    }

    /**
     * Set sequence of author in article's author list.
     * @param $sequence float
     */
    public function setSequence($sequence) {
        return $this->setData('sequence', $sequence);
    }
}

?>