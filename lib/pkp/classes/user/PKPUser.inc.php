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
 * [WIZDAM EDITION] PHP 7.4+ Compatible
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
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::PKPUser(). Please refactor to parent::__construct().", 
            E_USER_DEPRECATED
        );
        self::__construct();
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
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getId();
    }

    /**
     * Set the ID of the user. 
     * DEPRECATED in favour of setId.
     * @param $userId int
     */
    public function setUserId($userId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->setId($userId);
    }

    /**
     * Get username.
     * @return string
     */
    public function getUsername() {
        return $this->getData('username');
    }

    /**
     * Set username.
     * @param $username string
     */
    public function setUsername($username) {
        return $this->setData('username', $username);
    }

    /**
     * Get implicit auth ID string.
     * @return String
     */
    public function getAuthStr() {
        return $this->getData('authStr');
    }

    /**
     * Set Shib ID string for this user.
     * @param $authStr string
     */
    public function setAuthStr($authStr) {
        return $this->setData('authStr', $authStr);
    }

    /**
     * Get localized user signature.
     */
    public function getLocalizedSignature() {
        return $this->getLocalizedData('signature');
    }

    /**
     * Get user signature. 
     * DEPRECATED in favour of getLocalizedSignature.
     * @return string
     */
    public function getUserSignature() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedSignature();
    }

    /**
     * Get email signature.
     * @param $locale string
     * @return string
     */
    public function getSignature($locale) {
        return $this->getData('signature', $locale);
    }

    /**
     * Set signature.
     * @param $signature string
     * @param $locale string
     */
    public function setSignature($signature, $locale) {
        return $this->setData('signature', $signature, $locale);
    }

    /**
     * Get password (encrypted).
     * @return string
     */
    public function getPassword() {
        return $this->getData('password');
    }

    /**
     * Set password (assumed to be already encrypted).
     * @param $password string
     */
    public function setPassword($password) {
        return $this->setData('password', $password);
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
     * Get name suffix.
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
     * Get user gender.
     * @return string
     */
    public function getGender() {
        return $this->getData('gender');
    }

    /**
     * Set user gender.
     * @param $gender string
     */
    public function setGender($gender) {
        return $this->setData('gender', $gender);
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
     * Get localized user affiliation.
     * @return string
     */
    public function getLocalizedAffiliation() {
        return $this->getLocalizedData('affiliation');
    }

    /**
     * Mendapatkan afiliasi utama (hanya baris pertama)
     * @param $locale string
     * @return string
     */
    function getPrimaryAffiliation($locale = null) {
        $affiliation = $this->getAffiliation($locale);
        if (empty($affiliation)) return '';
        $parts = explode("\n", $affiliation);
        return trim($parts[0]);
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
     * Get Google Scholar ID user.
     * @return string
     */
    public function getGoogleScholar() {
        return $this->getData('googleScholar');
    }

    /**
     * Set Google Scholar ID user.
     * @param $googleScholarId string
     */
    public function setGoogleScholar($googleScholarId) {
        return $this->setData('googleScholar', $googleScholarId);
    }

    /**
     * Get SINTA ID user.
     * @return string
     */
    public function getSintaId() {
        return $this->getData('sintaId');
    }

    /**
     * Set SINTA ID user.
     * @param $sintaId string
     */
    public function setSintaId($sintaId) {
        return $this->setData('sintaId', $sintaId);
    }

    /**
     * Get Scopus ID user.
     * @return string
     */
    public function getScopusId() {
        return $this->getData('scopusId');
    }

    /**
     * Set Scopus ID user.
     * @param $scopusId string
     */
    public function setScopusId($scopusId) {
        return $this->setData('scopusId', $scopusId);
    }

    /**
     * Get Dimension ID user.
     * @return string
     */
    public function getDimensionId() {
        return $this->getData('dimensionId');
    }

    /**
     * Set Dimension ID user.
     * @param $dimensionId string
     */
    public function setDimensionId($dimensionId) {
        return $this->setData('dimensionId', $dimensionId);
    }

    /**
     * Get Researcher ID user.
     * @return string
     */
    public function getResearcherId() {
        return $this->getData('researcherId');
    }

    /**
     * Set Researcher ID user.
     * @param $researcherId string
     */
    public function setResearcherId($researcherId) {
        return $this->setData('researcherId', $researcherId);
    }

    /**
     * Get phone number.
     * @return string
     */
    public function getPhone() {
        return $this->getData('phone');
    }

    /**
     * Set phone number.
     * @param $phone string
     */
    public function setPhone($phone) {
        return $this->setData('phone', $phone);
    }

    /**
     * Get fax number.
     * @return string
     */
    public function getFax() {
        return $this->getData('fax');
    }

    /**
     * Set fax number.
     * @param $fax string
     */
    public function setFax($fax) {
        return $this->setData('fax', $fax);
    }

    /**
     * Get mailing address.
     * @return string
     */
    public function getMailingAddress() {
        return $this->getData('mailingAddress');
    }

    /**
     * Set mailing address.
     * @param $mailingAddress string
     */
    public function setMailingAddress($mailingAddress) {
        return $this->setData('mailingAddress', $mailingAddress);
    }

    /**
     * Get billing address.
     * @return string
     */
    public function getBillingAddress() {
        return $this->getData('billingAddress');
    }

    /**
     * Set billing address.
     * @param $billingAddress string
     */
    public function setBillingAddress($billingAddress) {
        return $this->setData('billingAddress', $billingAddress);
    }

    /**
     * Get country.
     * @return string
     */
    public function getCountry() {
        return $this->getData('country');
    }
    
    /**
     * Get the localized name of the country.
     * @return string|null
     */
    public function getCountryLocalized() {
        $countryDao = DAORegistry::getDAO('CountryDAO');

        $code = $this->getCountry();
        if (empty($code)) {
            return null;
        }
        return $countryDao->getCountry($code);
    }

    /**
     * [WIZDAM] Mendapatkan nama negara lengkap berdasarkan locale aktif
     * @return string|null
     */
    public function getCountryName() {
        $countryCode = $this->getCountry();
        if (!$countryCode) return null;
        
        $countryDao = DAORegistry::getDAO('CountryDAO');
        // Mengambil locale saat ini secara global
        $locale = AppLocale::getLocale(); 
        return $countryDao->getCountry($countryCode, $locale);
    }

    /**
     * Set country.
     * @param $country string
     */
    public function setCountry($country) {
        return $this->setData('country', $country);
    }

    /**
     * [WIZDAM] Mendapatkan nama file foto profil
     * @return string|null
     */
    public function getProfilePictureName() {
        $profileImage = $this->getData('profileImage');
        if ($profileImage && !empty($profileImage['uploadName'])) {
            return $profileImage['uploadName'];
        }
        return null;
    }

    /**
     * [WIZDAM REFACTOR] Mendapatkan Full URL Foto Profil secara Dinamis.
     * Core yang melacak path, bukan Smarty. 
     * Tahan banting terhadap perubahan config.
     */
    public function getProfileImageUrl() {
        // 1. Cek apakah user punya data gambar di database
        $profileImage = $this->getData('profileImage');
        
        if ($profileImage && !empty($profileImage['uploadName'])) {
            // Ambil Base URL jurnal
            $baseUrl = Request::getBaseUrl();
            
            // BACA DARI CONFIG: Ambil nama direktori publik yang aktif saat ini
            $publicDir = Config::getVar('files', 'public_files_dir'); 
            
            // Rakit URL secara dinamis (Contoh hasil: http://domain.com/public/site/namafile.jpg)
            return $baseUrl . '/' . $publicDir . '/site/' . $profileImage['uploadName'];
        }
        
        // 2. Fallback Gravatar Otomatis (Jika tidak ada foto lokal)
        $email = $this->getEmail();
        if (!empty($email)) {
            return "https://www.gravatar.com/avatar/" . md5(strtolower(trim($email))) . "?s=150&d=mp";
        }
        
        return ''; // Kosong jika tidak ada sama sekali
    }

    /**
     * Get localized user biography.
     */
    public function getLocalizedBiography() {
        return $this->getLocalizedData('biography');
    }

    /**
     * Get user biography. 
     * DEPRECATED in favour of getLocalizedBiography.
     * @return string
     */
    public function getUserBiography() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedBiography();
    }

    /**
     * Get user biography.
     * @param $locale string
     * @return string
     */
    public function getBiography($locale) {
        return $this->getData('biography', $locale);
    }

    /**
     * Set user biography.
     * @param $biography string
     * @param $locale string
     */
    public function setBiography($biography, $locale) {
        return $this->setData('biography', $biography, $locale);
    }

    /**
     * Get the user's reviewing interests as an array. 
     * DEPRECATED in favour of direct interaction with the InterestManager.
     * @return array
     */
    public function getUserInterests() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        import('lib.pkp.classes.user.InterestManager');
        $interestManager = new InterestManager();
        return $interestManager->getInterestsForUser($this);
    }

    /**
     * Get the user's interests displayed as a comma-separated string
     * @return string
     */
    public function getInterestString() {
        import('lib.pkp.classes.user.InterestManager');
        $interestManager = new InterestManager();
        return $interestManager->getInterestsString($this);
    }

    /**
     * Get localized user gossip.
     */
    public function getLocalizedGossip() {
        return $this->getLocalizedData('gossip');
    }

    /**
     * Get user gossip.
     * @param $locale string
     * @return string
     */
    public function getGossip($locale) {
        $gossip = $this->getData('gossip', $locale);
        return ($gossip !== null) ? (string) $gossip : '';
    }

    /**
     * Set user gossip.
     * @param $gossip string
     * @param $locale string
     */
    public function setGossip($gossip, $locale) {
        return $this->setData('gossip', $gossip, $locale);
    }

    /**
     * Get user's working languages.
     * @return array
     */
    public function getLocales() {
        $locales = $this->getData('locales');
        return isset($locales) ? $locales : array();
    }

    /**
     * Set user's working languages.
     * @param $locales array
     */
    public function setLocales($locales) {
        return $this->setData('locales', $locales);
    }

    /**
     * Get date user last sent an email.
     * @return datestamp (YYYY-MM-DD HH:MM:SS)
     */
    public function getDateLastEmail() {
        return $this->getData('dateLastEmail');
    }

    /**
     * Set date user last sent an email.
     * @param $dateLastEmail datestamp (YYYY-MM-DD HH:MM:SS)
     */
    public function setDateLastEmail($dateLastEmail) {
        return $this->setData('dateLastEmail', $dateLastEmail);
    }

    /**
     * Get date user registered with the site.
     * @return datestamp (YYYY-MM-DD HH:MM:SS)
     */
    public function getDateRegistered() {
        return $this->getData('dateRegistered');
    }

    /**
     * Set date user registered with the site.
     * @param $dateRegistered datestamp (YYYY-MM-DD HH:MM:SS)
     */
    public function setDateRegistered($dateRegistered) {
        return $this->setData('dateRegistered', $dateRegistered);
    }

    /**
     * Get date user email was validated with the site.
     * @return datestamp (YYYY-MM-DD HH:MM:SS)
     */
    public function getDateValidated() {
        return $this->getData('dateValidated');
    }

    /**
     * Set date user email was validated with the site.
     * @param $dateValidated datestamp (YYYY-MM-DD HH:MM:SS)
     */
    public function setDateValidated($dateValidated) {
        return $this->setData('dateValidated', $dateValidated);
    }

    /**
     * Get date user last logged in to the site.
     * @return datestamp
     */
    public function getDateLastLogin() {
        return $this->getData('dateLastLogin');
    }

    /**
     * Set date user last logged in to the site.
     * @param $dateLastLogin datestamp
     */
    public function setDateLastLogin($dateLastLogin) {
        return $this->setData('dateLastLogin', $dateLastLogin);
    }

    /**
     * Check if user must change their password on their next login.
     * @return boolean
     */
    public function getMustChangePassword() {
        return $this->getData('mustChangePassword');
    }

    /**
     * Set whether or not user must change their password on their next login.
     * @param $mustChangePassword boolean
     */
    public function setMustChangePassword($mustChangePassword) {
        return $this->setData('mustChangePassword', $mustChangePassword);
    }

    /**
     * Check if user is disabled.
     * @return boolean
     */
    public function getDisabled() {
        return $this->getData('disabled');
    }

    /**
     * Set whether or not user is disabled.
     * @param $disabled boolean
     */
    public function setDisabled($disabled) {
        return $this->setData('disabled', $disabled);
    }

    /**
     * Get the reason the user was disabled.
     * @return string
     */
    public function getDisabledReason() {
        return $this->getData('disabled_reason');
    }

    /**
     * Set the reason the user is disabled.
     * @param $reasonDisabled string
     */
    public function setDisabledReason($reasonDisabled) {
        return $this->setData('disabled_reason', $reasonDisabled);
    }

    /**
     * Get ID of authentication source for this user.
     * @return int
     */
    public function getAuthId() {
        return $this->getData('authId');
    }

    /**
     * Set ID of authentication source for this user.
     * @param $authId int
     */
    public function setAuthId($authId) {
        return $this->setData('authId', $authId);
    }

    /**
     * Get the inline help display status for this user.
     * @return int
     */
    public function getInlineHelp() {
        return $this->getData('inlineHelp');
    }

    /**
     * Set the inline help display status for this user.
     * @param $inlineHelp int
     */
    public function setInlineHelp($inlineHelp) {
        return $this->setData('inlineHelp', $inlineHelp);
    }

    /**
     * Mendapatkan Given Name untuk UI Aplikasi.
     * Menggabungkan First Name dan Last Name. Jika pengguna adalah mononim
     * (First Name dan Last Name diinput sama persis sesuai aturan form),
     * maka hanya kembalikan satu nama saja agar tidak tercetak ganda.
     * @return string
     */
    function getGivenName() {
        $firstName = trim($this->getFirstName());
        $lastName = trim($this->getLastName());

        // Cek apakah ini kasus mononim (input kembar)
        if (strtolower($firstName) === strtolower($lastName)) {
            return $firstName;
        }

        // Jika nama normal (berbeda), gabungkan
        return $firstName . ' ' . $lastName;
    }

    /**
     * Mendapatkan Surname untuk UI Aplikasi.
     * Mengembalikan Last Name apa adanya karena database dijamin bersih.
     * @return string
     */
    function getSurname() {
        // Jika nama pengguna Soekarno (LN=Soekarno), fungsi ini akan 
        // mengembalikan "Soekarno".
        return trim($this->getLastName());
    }
    
    /**
     * Get the user's complete name.
     * Includes first name, middle name (if applicable), and last name.
     * The suffix is only included when the name is not reversed with $lastFirst
     * @param $lastFirst boolean return in "LastName, FirstName" format
     * @return string
     */
    public function getFullName($lastFirst = false) {
        // Ambil data dan bersihkan spasi berlebih di awal/akhir trim()
        $salutation = trim((string) $this->getData('salutation'));
        $firstName  = trim((string) $this->getData('firstName'));
        $middleName = trim((string) $this->getData('middleName'));
        $lastName   = trim((string) $this->getData('lastName'));
        $suffix     = trim((string) $this->getData('suffix'));

        // [WIZDAM]: Deteksi nama tunggal Indonesia, Jika FirstName dan LastName sama persis (abaikan huruf besar/kecil), maka kosongkan FirstName agar tidak dicetak ganda.
        if ($firstName !== '' && $lastName !== '' && strcasecmp($firstName, $lastName) === 0) {
            $firstName = ''; 
        }

        if ($lastFirst) {
            // Format: "LastName, Salutation FirstName MiddleName"
            $firstPart = $lastName;
            
            // Kumpulkan bagian setelah koma, abaikan yang kosong
            $secondPartArray = array_filter([$salutation, $firstName, $middleName], fn($val) => $val !== '');
            $secondPart = implode(' ', $secondPartArray);

            // Jika ada bagian kedua, gabungkan dengan koma. 
            // Jika tidak, tampilkan nama akhirnya saja.
            return !empty($secondPart) ? "$firstPart, $secondPart" : $firstPart;

        } else {
            // Format: "Salutation FirstName MiddleName LastName, Suffix"
            
            // Kumpulkan semua bagian nama utama, abaikan yang kosong
            $mainPartArray = array_filter([$salutation, $firstName, $middleName, $lastName], fn($val) => $val !== '');
            $mainPart = implode(' ', $mainPartArray);

            // Tambahkan suffix (gelar di belakang) jika ada
            return !empty($suffix) ? "$mainPart, $suffix" : $mainPart;
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
        if ($a = $this->getLocalizedAffiliation()) $signature .= "\n" . $a;
        if ($p = $this->getPhone()) $signature .= "\n" . __('user.phone') . ' ' . $p;
        if ($f = $this->getFax()) $signature .= "\n" . __('user.fax') . ' ' . $f;
        $signature .= "\n" . $this->getEmail();
        return $signature;
    }
}
?>