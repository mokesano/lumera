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
     * [SHIM] Backward Compatibility.
     */
    public function PKPAuthor() {
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
     * Get the author's complete name.
     * Includes first name, middle name (if applicable), and last name.
     * @param bool $lastFirst
     * @return string
     */
    public function getFullName($lastFirst = false) {
        $firstName = (string) $this->getData('firstName');
        $middleName = (string) $this->getData('middleName');
        $lastName = (string) $this->getData('lastName');
        $suffix = (string) $this->getData('suffix');

        if ($lastFirst) {
            return $lastName . ', ' . $firstName . ($middleName !== '' ? ' ' . $middleName : '');
        }
        return $firstName . ' ' . ($middleName !== '' ? $middleName . ' ' : '') . $lastName . ($suffix !== '' ? ', ' . $suffix : '');
    }

    //
    // Get/set methods
    //

    /**
     * Get ID of author.
     * DEPRECATED in favour of getId.
     * @return int
     */
    public function getAuthorId() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return (int) $this->getId();
    }

    /**
     * Set ID of author.
     * DEPRECATED in favour of setId.
     * @param int $authorId
     */
    public function setAuthorId($authorId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        $this->setId((int) $authorId);
    }

    /**
     * Get ID of submission.
     * @return int|null
     */
    public function getSubmissionId() {
        $id = $this->getData('submissionId');
        return $id !== null ? (int) $id : null;
    }

    /**
     * Set ID of submission.
     * @param int|null $submissionId
     */
    public function setSubmissionId($submissionId) {
        $this->setData('submissionId', $submissionId !== null ? (int) $submissionId : null);
    }

    /**
     * Set the user group id.
     * @param int|null $userGroupId
     */
    public function setUserGroupId($userGroupId) {
        $this->setData('userGroupId', $userGroupId !== null ? (int) $userGroupId : null);
    }

    /**
     * Get the user group id.
     * @return int|null
     */
    public function getUserGroupId() {
        $id = $this->getData('userGroupId');
        return $id !== null ? (int) $id : null;
    }

    /**
     * Get first name.
     * @return string
     */
    public function getFirstName() {
        return (string) $this->getData('firstName');
    }

    /**
     * Set first name.
     * @param string $firstName
     */
    public function setFirstName($firstName) {
        $this->setData('firstName', (string) $firstName);
    }

    /**
     * Get middle name.
     * @return string
     */
    public function getMiddleName() {
        return (string) $this->getData('middleName');
    }

    /**
     * Set middle name.
     * @param string $middleName
     */
    public function setMiddleName($middleName) {
        $this->setData('middleName', (string) $middleName);
    }

    /**
     * Get initials.
     * @return string
     */
    public function getInitials() {
        return (string) $this->getData('initials');
    }

    /**
     * Set initials.
     * @param string $initials
     */
    public function setInitials($initials) {
        $this->setData('initials', (string) $initials);
    }

    /**
     * Get last name.
     * @return string
     */
    public function getLastName() {
        return (string) $this->getData('lastName');
    }

    /**
     * Set last name.
     * @param string $lastName
     */
    public function setLastName($lastName) {
        $this->setData('lastName', (string) $lastName);
    }

    /**
     * Get suffix.
     * @return string
     */
    public function getSuffix() {
        return (string) $this->getData('suffix');
    }

    /**
     * Set suffix.
     * @param string $suffix
     */
    public function setSuffix($suffix) {
        $this->setData('suffix', (string) $suffix);
    }

    /**
     * Get user salutation.
     * @return string
     */
    public function getSalutation() {
        return (string) $this->getData('salutation');
    }

    /**
     * Set user salutation.
     * @param string $salutation
     */
    public function setSalutation($salutation) {
        $this->setData('salutation', (string) $salutation);
    }

    /**
     * Get affiliation (position, institution, etc.).
     * @param string|null $locale
     * @return string
     */
    public function getAffiliation($locale) {
        return $this->getData('affiliation', $locale);
    }

    /**
     * Set affiliation.
     * @param string $affiliation
     * @param string|null $locale
     */
    public function setAffiliation($affiliation, $locale) {
        $this->setData('affiliation', (string) $affiliation, $locale);
    }

    /**
     * Get the localized affiliation for this author.
     * @return string
     */
    public function getLocalizedAffiliation() {
        return (string) $this->getLocalizedData('affiliation');
    }

    /**
     * Get country code.
     * @return string
     */
    public function getCountry() {
        return (string) $this->getData('country');
    }

    /**
     * Get localized country name.
     * @return string|null
     */
    public function getCountryLocalized() {
        $country = $this->getCountry();
        if ($country !== null && $country !== '') {
            /** @var CountryDAO $countryDao */
            $countryDao = DAORegistry::getDAO('CountryDAO');
            return $countryDao->getCountry($country);
        }
        return null;
    }

    /**
     * Set country code.
     * @param string $country
     */
    public function setCountry($country) {
        $this->setData('country', (string) $country);
    }

    /**
     * Get email address.
     * @return string
     */
    public function getEmail() {
        return (string) $this->getData('email');
    }

    /**
     * Set email address.
     * @param string $email
     */
    public function setEmail($email) {
        $this->setData('email', (string) $email);
    }

    /**
     * Get URL.
     * @return string
     */
    public function getUrl() {
        return (string) $this->getData('url');
    }

    /**
     * Set URL.
     * @param string $url
     */
    public function setUrl($url) {
        $this->setData('url', (string) $url);
    }

    /**
     * Get the localized biography for this author.
     * @return string
     */
    public function getLocalizedBiography() {
        return (string) $this->getLocalizedData('biography');
    }

    /**
     * [DEPRECATED] Get Author Biography.
     * @return string
     */
    public function getAuthorBiography() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->getLocalizedBiography();
    }

    /**
     * Get author biography.
     * @param string|null $locale
     * @return string
     */
    public function getBiography($locale) {
        if (empty($locale)) {
            $locale = AppLocale::getPrimaryLocale();
        }

        $biography = $this->getData('biography', $locale);
        if (is_array($biography)) {
            $biography = reset($biography);
            if (!is_string($biography)) {
                $biography = '';
            }
        }

        return (string) $biography;
    }

    /**
     * Set author biography.
     * @param string $biography
     * @param string|null $locale
     */
    public function setBiography($biography, $locale) {
        $this->setData('biography', (string) $biography, $locale);
    }

    /**
     * Get primary contact.
     * @return bool
     */
    public function getPrimaryContact() {
        return (bool) $this->getData('primaryContact');
    }

    /**
     * Set primary contact.
     * @param bool $primaryContact
     */
    public function setPrimaryContact($primaryContact) {
        $this->setData('primaryContact', (bool) $primaryContact);
    }

    /**
     * Get sequence of author in article's author list.
     * @return float|null
     */
    public function getSequence() {
        $seq = $this->getData('sequence');
        return $seq !== null ? (float) $seq : null;
    }

    /**
     * Set sequence of author in article's author list.
     * @param float|null $sequence
     */
    public function setSequence($sequence) {
        $this->setData('sequence', $sequence !== null ? (float) $sequence : null);
    }
    
}
?>