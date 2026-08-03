<?php
declare(strict_types=1);

/**
 * @defgroup user
 */

/**
 * @file classes/user/PKPUser.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPUser
 * @ingroup user
 * @see UserDAO
 *
 * @brief Basic class describing users existing in the system.
 */

class PKPUser extends DataObject {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPUser() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().", 
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    //
    // Get/set methods
    //

    /**
     * Get the ID of the user. 
     * DEPRECATED in favour of getId.
     * @return int
     */
    public function getUserId() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return (int) $this->getId();
    }

    /**
     * Set the ID of the user. 
     * DEPRECATED in favour of setId.
     * @param int $userId
     */
    public function setUserId($userId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        $this->setId((int) $userId);
    }

    /**
     * Get username.
     * @return string
     */
    public function getUsername() {
        return (string) $this->getData('username');
    }

    /**
     * Set username.
     * @param string $username
     */
    public function setUsername($username) {
        $this->setData('username', (string) $username);
    }

    /**
     * Get implicit auth ID string.
     * @return string
     */
    public function getAuthStr() {
        return (string) $this->getData('authStr');
    }

    /**
     * Set Shib ID string for this user.
     * @param string $authStr
     */
    public function setAuthStr($authStr) {
        $this->setData('authStr', (string) $authStr);
    }

    /**
     * Get localized user signature.
     * @return string
     */
    public function getLocalizedSignature() {
        return (string) $this->getLocalizedData('signature');
    }

    /**
     * Get user signature. 
     * DEPRECATED in favour of getLocalizedSignature.
     * @return string
     */
    public function getUserSignature() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->getLocalizedSignature();
    }

    /**
     * Get email signature.
     * @param string $locale
     * @return string
     */
    public function getSignature($locale) {
        return $this->getData('signature', $locale);
    }

    /**
     * Set signature.
     * @param string $signature
     * @param string $locale
     */
    public function setSignature($signature, $locale) {
        $this->setData('signature', (string) $signature, $locale);
    }

    /**
     * Get password (encrypted).
     * @return string
     */
    public function getPassword() {
        return (string) $this->getData('password');
    }

    /**
     * Set password (assumed to be already encrypted).
     * @param string $password
     */
    public function setPassword($password) {
        $this->setData('password', (string) $password);
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
     * Get name suffix.
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
     * Get user gender.
     * @return string
     */
    public function getGender() {
        return (string) $this->getData('gender');
    }

    /**
     * Set user gender.
     * @param string $gender
     */
    public function setGender($gender) {
        $this->setData('gender', (string) $gender);
    }

    /**
     * Get affiliation (position, institution, etc.).
     * @param string $locale
     * @return string
     */
    public function getAffiliation($locale) {
        return $this->getData('affiliation', $locale);
    }

    /**
     * Set affiliation.
     * @param string $affiliation
     * @param string $locale
     */
    public function setAffiliation($affiliation, $locale) {
        $this->setData('affiliation', (string) $affiliation, $locale);
    }

    /**
     * Get localized user affiliation.
     * @return string
     */
    public function getLocalizedAffiliation() {
        return (string) $this->getLocalizedData('affiliation');
    }

    /**
     * Get primary affiliation (first line only).
     * @param string $locale
     * @return string
     */
    public function getPrimaryAffiliation($locale = null) {
        $affiliation = (string) $this->getAffiliation($locale);
        if ($affiliation === '') {
            return '';
        }
        $parts = explode("\n", $affiliation);
        return trim($parts[0]);
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
     * Get Google Scholar ID user.
     * @return string
     */
    public function getGoogleScholar() {
        return (string) $this->getData('googleScholar');
    }

    /**
     * Set Google Scholar ID user.
     * @param string $googleScholarId
     */
    public function setGoogleScholar($googleScholarId) {
        $this->setData('googleScholar', (string) $googleScholarId);
    }

    /**
     * Get SINTA ID user.
     * @return string
     */
    public function getSintaId() {
        return (string) $this->getData('sintaId');
    }

    /**
     * Set SINTA ID user.
     * @param string $sintaId
     */
    public function setSintaId($sintaId) {
        $this->setData('sintaId', (string) $sintaId);
    }

    /**
     * Get Scopus ID user.
     * @return string
     */
    public function getScopusId() {
        return (string) $this->getData('scopusId');
    }

    /**
     * Set Scopus ID user.
     * @param string $scopusId
     */
    public function setScopusId($scopusId) {
        $this->setData('scopusId', (string) $scopusId);
    }

    /**
     * Get Dimension ID user.
     * @return string
     */
    public function getDimensionId() {
        return (string) $this->getData('dimensionId');
    }

    /**
     * Set Dimension ID user.
     * @param string $dimensionId
     */
    public function setDimensionId($dimensionId) {
        $this->setData('dimensionId', (string) $dimensionId);
    }

    /**
     * Get Researcher ID user.
     * @return string
     */
    public function getResearcherId() {
        return (string) $this->getData('researcherId');
    }

    /**
     * Set Researcher ID user.
     * @param string $researcherId
     */
    public function setResearcherId($researcherId) {
        $this->setData('researcherId', (string) $researcherId);
    }

    /**
     * Get phone number.
     * @return string
     */
    public function getPhone() {
        return (string) $this->getData('phone');
    }

    /**
     * Set phone number.
     * @param string $phone
     */
    public function setPhone($phone) {
        $this->setData('phone', (string) $phone);
    }

    /**
     * Get fax number.
     * @return string
     */
    public function getFax() {
        return (string) $this->getData('fax');
    }

    /**
     * Set fax number.
     * @param string $fax
     */
    public function setFax($fax) {
        $this->setData('fax', (string) $fax);
    }

    /**
     * Get mailing address.
     * @return string
     */
    public function getMailingAddress() {
        return (string) $this->getData('mailingAddress');
    }

    /**
     * Set mailing address.
     * @param string $mailingAddress
     */
    public function setMailingAddress($mailingAddress) {
        $this->setData('mailingAddress', (string) $mailingAddress);
    }

    /**
     * Get billing address.
     * @return string
     */
    public function getBillingAddress() {
        return (string) $this->getData('billingAddress');
    }

    /**
     * Set billing address.
     * @param string $billingAddress
     */
    public function setBillingAddress($billingAddress) {
        $this->setData('billingAddress', (string) $billingAddress);
    }

    /**
     * Get country.
     * @return string
     */
    public function getCountry() {
        return (string) $this->getData('country');
    }
    
    /**
     * Get the localized name of the country.
     * @return string|null
     */
    public function getCountryLocalized() {
        $code = $this->getCountry();
        if ($code === '') {
            return null;
        }
        /** @var CountryDAO $countryDao */
        $countryDao = DAORegistry::getDAO('CountryDAO');
        $country = $countryDao->getCountry($code);
        return $country !== null ? (string) $country : null;
    }

    /**
     * Get the full name of the country based on the active locale.
     * @return string|null
     */
    public function getCountryName() {
        $countryCode = $this->getCountry();
        if ($countryCode === '') {
            return null;
        }
        /** @var CountryDAO $countryDao */
        $countryDao = DAORegistry::getDAO('CountryDAO');
        $locale = AppLocale::getLocale(); 
        $country = $countryDao->getCountry($countryCode, $locale);
        return $country !== null ? (string) $country : null;
    }

    /**
     * Set country.
     * @param string $country
     */
    public function setCountry($country) {
        $this->setData('country', (string) $country);
    }

    /**
     * Get the profile picture file name.
     * @return string|null
     */
    public function getProfilePictureName() {
        $profileImage = $this->getData('profileImage');
        if (is_array($profileImage) && !empty($profileImage['uploadName'])) {
            return (string) $profileImage['uploadName'];
        }
        return null;
    }

    /**
     * Get the dynamic full URL of the profile picture.
     * Resilient to config changes and falls back to Gravatar.
     * @return string
     */
    public function getProfileImageUrl() {
        $profileImage = $this->getData('profileImage');
        
        if (is_array($profileImage) && !empty($profileImage['uploadName'])) {
            // Lumera Singleton Fallback
            $request = Application::get()->getRequest();
            $baseUrl = $request->getBaseUrl();
            $publicDir = (string) Config::getVar('files', 'public_files_dir'); 
            
            return $baseUrl . '/' . $publicDir . '/site/' . (string) $profileImage['uploadName'];
        }
        
        $email = $this->getEmail();
        if ($email !== '') {
            return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . '?s=150&d=mp';
        }
        
        return '';
    }

    /**
     * Get localized user biography.
     * @return string
     */
    public function getLocalizedBiography() {
        return (string) $this->getLocalizedData('biography');
    }

    /**
     * Get user biography. 
     * DEPRECATED in favour of getLocalizedBiography.
     * @return string
     */
    public function getUserBiography() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->getLocalizedBiography();
    }

    /**
     * Get user biography.
     * @param string $locale
     * @return string
     */
    public function getBiography($locale) {
        $biography = $this->getData('biography', $locale);
        return is_array($biography) ? $biography : (string) $biography;
    }

    /**
     * Set user biography.
     * @param string $biography
     * @param string $locale
     */
    public function setBiography($biography, $locale) {
        $this->setData('biography', (string) $biography, $locale);
    }

    /**
     * Get the user's reviewing interests as an array. 
     * DEPRECATED in favour of direct interaction with the InterestManager.
     * @return array
     */
    public function getUserInterests() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        import('lib.pkp.classes.user.InterestManager');
        $interestManager = new InterestManager();
        return $interestManager->getInterestsForUser($this);
    }

    /**
     * Get the user's interests displayed as a comma-separated string.
     * @return string
     */
    public function getInterestString() {
        import('lib.pkp.classes.user.InterestManager');
        $interestManager = new InterestManager();
        return (string) $interestManager->getInterestsString($this);
    }

    /**
     * Get localized user gossip.
     * @return string
     */
    public function getLocalizedGossip() {
        return (string) $this->getLocalizedData('gossip');
    }

    /**
     * Get user gossip.
     * @param string $locale
     * @return string
     */
    public function getGossip($locale) {
        $gossip = $this->getData('gossip', $locale);
        return $gossip !== null ? (string) $gossip : '';
    }

    /**
     * Set user gossip.
     * @param string $gossip
     * @param string $locale
     */
    public function setGossip($gossip, $locale) {
        $this->setData('gossip', (string) $gossip, $locale);
    }

    /**
     * Get user's working languages.
     * @return array
     */
    public function getLocales() {
        $locales = $this->getData('locales');
        return is_array($locales) ? $locales : [];
    }

    /**
     * Set user's working languages.
     * @param array $locales
     */
    public function setLocales($locales) {
        $this->setData('locales', is_array($locales) ? $locales : []);
    }

    /**
     * Get date user last sent an email.
     * @return string
     */
    public function getDateLastEmail() {
        return (string) $this->getData('dateLastEmail');
    }

    /**
     * Set date user last sent an email.
     * @param string $dateLastEmail
     */
    public function setDateLastEmail($dateLastEmail) {
        $this->setData('dateLastEmail', (string) $dateLastEmail);
    }

    /**
     * Get date user registered with the site.
     * @return string
     */
    public function getDateRegistered() {
        return (string) $this->getData('dateRegistered');
    }

    /**
     * Set date user registered with the site.
     * @param string $dateRegistered
     */
    public function setDateRegistered($dateRegistered) {
        $this->setData('dateRegistered', (string) $dateRegistered);
    }

    /**
     * Get date user email was validated with the site.
     * @return string
     */
    public function getDateValidated() {
        return (string) $this->getData('dateValidated');
    }

    /**
     * Set date user email was validated with the site.
     * @param string $dateValidated
     */
    public function setDateValidated($dateValidated) {
        $this->setData('dateValidated', (string) $dateValidated);
    }

    /**
     * Get date user last logged in to the site.
     * @return string
     */
    public function getDateLastLogin() {
        return (string) $this->getData('dateLastLogin');
    }

    /**
     * Set date user last logged in to the site.
     * @param string $dateLastLogin
     */
    public function setDateLastLogin($dateLastLogin) {
        $this->setData('dateLastLogin', (string) $dateLastLogin);
    }

    /**
     * Check if user must change their password on their next login.
     * @return bool
     */
    public function getMustChangePassword() {
        return (bool) $this->getData('mustChangePassword');
    }

    /**
     * Set whether or not user must change their password on their next login.
     * @param bool $mustChangePassword
     */
    public function setMustChangePassword($mustChangePassword) {
        $this->setData('mustChangePassword', (bool) $mustChangePassword);
    }

    /**
     * Check if user is disabled.
     * @return bool
     */
    public function getDisabled() {
        return (bool) $this->getData('disabled');
    }

    /**
     * Set whether or not user is disabled.
     * @param bool $disabled
     */
    public function setDisabled($disabled) {
        $this->setData('disabled', (bool) $disabled);
    }

    /**
     * Get the reason the user was disabled.
     * @return string
     */
    public function getDisabledReason() {
        return (string) $this->getData('disabled_reason');
    }

    /**
     * Set the reason the user is disabled.
     * @param string $reasonDisabled
     */
    public function setDisabledReason($reasonDisabled) {
        $this->setData('disabled_reason', (string) $reasonDisabled);
    }

    /**
     * Get ID of authentication source for this user.
     * @return int|null
     */
    public function getAuthId() {
        $authId = $this->getData('authId');
        return $authId !== null ? (int) $authId : null;
    }

    /**
     * Set ID of authentication source for this user.
     * @param int $authId
     */
    public function setAuthId($authId) {
        $this->setData('authId', $authId !== null ? (int) $authId : null);
    }

    /**
     * Get the inline help display status for this user.
     * @return int
     */
    public function getInlineHelp() {
        return (int) $this->getData('inlineHelp');
    }

    /**
     * Set the inline help display status for this user.
     * @param int $inlineHelp
     */
    public function setInlineHelp($inlineHelp) {
        $this->setData('inlineHelp', (int) $inlineHelp);
    }

    /**
     * Get the Given Name for the application UI.
     * Combines First Name and Last Name. If the user is mononymous 
     * (First Name and Last Name are identical), returns only one name.
     * @return string
     */
    public function getGivenName() {
        $firstName = trim((string) $this->getFirstName());
        $lastName = trim((string) $this->getLastName());

        if ($firstName !== '' && $lastName !== '' && strcasecmp($firstName, $lastName) === 0) {
            return $firstName;
        }

        return $firstName !== '' ? $firstName . ' ' . $lastName : $lastName;
    }

    /**
     * Get the Surname for the application UI.
     * Returns Last Name as is, assuming database integrity.
     * @return string
     */
    public function getSurname() {
        return trim((string) $this->getLastName());
    }
    
    /**
     * Get the user's complete name.
     * Includes first name, middle name (if applicable), and last name.
     * The suffix is only included when the name is not reversed with $lastFirst.
     * @param bool $lastFirst Return in "LastName, FirstName" format
     * @return string
     */
    public function getFullName($lastFirst = false) {
        $salutation = trim((string) $this->getData('salutation'));
        $firstName  = trim((string) $this->getData('firstName'));
        $middleName = trim((string) $this->getData('middleName'));
        $lastName   = trim((string) $this->getData('lastName'));
        $suffix     = trim((string) $this->getData('suffix'));

        // Detect Indonesian mononymous names: if First and Last are identical, clear First to avoid duplication.
        if ($firstName !== '' && $lastName !== '' && strcasecmp($firstName, $lastName) === 0) {
            $firstName = ''; 
        }

        if ($lastFirst) {
            $firstPart = $lastName;
            $secondPartArray = array_filter([$salutation, $firstName, $middleName], function($val) { return $val !== ''; });
            $secondPart = implode(' ', $secondPartArray);
            return $secondPart !== '' ? "$firstPart, $secondPart" : $firstPart;
        } else {
            $mainPartArray = array_filter([$salutation, $firstName, $middleName, $lastName], function($val) { return $val !== ''; });
            $mainPart = implode(' ', $mainPartArray);
            return $suffix !== '' ? "$mainPart, $suffix" : $mainPart;
        }
    }

    /**
     * Get a contact signature for this user, including name, 
     * affiliation, phone, fax, and email.
     * @return string
     */
    public function getContactSignature() {
        $signature = $this->getFullName();
        AppLocale::requireComponents(LOCALE_COMPONENT_CORE_USER);
        
        $affiliation = $this->getLocalizedAffiliation();
        if ($affiliation !== '') {
            $signature .= "\n" . $affiliation;
        }
        
        $phone = $this->getPhone();
        if ($phone !== '') {
            $signature .= "\n" . __('user.phone') . ' ' . $phone;
        }
        
        $fax = $this->getFax();
        if ($fax !== '') {
            $signature .= "\n" . __('user.fax') . ' ' . $fax;
        }
        
        $email = $this->getEmail();
        if ($email !== '') {
            $signature .= "\n" . $email;
        }
        
        return $signature;
    }
    
}
?>