<?php
declare(strict_types=1);

/**
 * @file classes/user/form/ProfileForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProfileForm
 * @ingroup user_form
 *
 * @brief Form to edit user profile.
 *
 * WIZDAM MODERNIZATION:
 * - PHP 7.4/8.x Compatibility (Constructor, Ref removal, Visibility, Destructuring Guard)
 * - Session Safety Guard
 * - Image Processing Safety & UX Auto-Compression (GD Library)
 * - Secure Obfuscated Profile Image Naming
 */

import('lib.pkp.classes.form.Form');

class ProfileForm extends Form {

    /** @var User object */
    public $user;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct('user/profile.tpl');
        
        $user = Request::getUser();
        
        // Validasi: pastikan user sudah login (Guard Clause)
        if (!$user) {
            // Redirect ke halaman login jika user belum login
            Request::redirect(null, 'login');
            return;
        }
        
        $this->user = $user;
        $site = Request::getSite();
        
        // Validation checks for this form
        $this->addCheck(new FormValidator($this, 'firstName', 'required', 'user.profile.form.firstNameRequired'));
        $this->addCheck(new FormValidator($this, 'lastName', 'required', 'user.profile.form.lastNameRequired'));
        $this->addCheck(new FormValidatorUrl($this, 'userUrl', 'optional', 'user.profile.form.urlInvalid'));
        $this->addCheck(new FormValidatorEmail($this, 'email', 'required', 'user.profile.form.emailRequired'));
        $this->addCheck(new FormValidatorORCID($this, 'orcid', 'optional', 'user.profile.form.orcidInvalid'));
        
        // PHP 8: Use array callback properly
        $this->addCheck(new FormValidatorCustom($this, 'email', 'required', 'user.register.form.emailExists', array(DAORegistry::getDAO('UserDAO'), 'userExistsByEmail'), array($user->getId(), true), true));
        
        // [WIZDAM SECURITY] Gunakan Validator CSRF yang kita buat sebelumnya!
        import('lib.pkp.classes.form.validation.FormValidatorCSRF');
        $this->addCheck(new FormValidatorCSRF($this));
        
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ProfileForm() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class " . get_class($this) . " uses deprecated constructor parent::ProfileForm(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Deletes a profile image.
     * @return boolean
     */
    public function deleteProfileImage() {
        $user = Request::getUser();
        if (!$user) return false;

        $profileImage = $user->getSetting('profileImage');
        if (!$profileImage) return false;

        import('classes.file.PublicFileManager');
        $fileManager = new PublicFileManager();
        if ($fileManager->removeSiteFile($profileImage['uploadName'])) {
            return $user->updateSetting('profileImage', null);
        } else {
            return false;
        }
    }

    /**
     * Uploads a profile image.
     * [WIZDAM EDITION] Auto-Compression & Secure Unique Numeric Filename
     * @return boolean
     */
    public function uploadProfileImage() {
        import('classes.file.PublicFileManager');
        $fileManager = new PublicFileManager();

        $user = $this->user;
        if (!$user) return false;

        $type = $fileManager->getUploadedFileType('profileImage');
        $extension = $fileManager->getImageExtension($type);
        if (!$extension) return false;

        // =================================================================
        // 1. KEAMANAN: GENERATE NAMA FILE (MURNI ANGKA & LAST NAME)
        // =================================================================
        // Ambil lastName, bersihkan dari karakter aneh, ubah ke huruf kecil.
        $lastName = $user->getLastName();
        $cleanLastName = !empty($lastName) ? preg_replace('/[^a-zA-Z0-9]/', '', strtolower($lastName)) : 'usr';
        
        $userId = (int) $user->getId();
        
        // [WIZDAM MAGIC] Mengaburkan User ID menjadi angka murni
        // Rumus: (ID * 83) + 10024. Jika ID = 42, hasilnya = 13510
        $obfuscatedId = ($userId * 83) + 10024; 
        
        // Tambahkan angka acak dan detik saat ini agar file selalu unik (mencegah cache browser)
        $dynamicNumbers = mt_rand(100, 999) . date('is'); 
        
        // Gabungkan ID samar dengan angka dinamis
        $numericHash = $obfuscatedId . $dynamicNumbers;

        // Susun format akhir: doe-profile-135104821530.jpg
        $uploadName = sprintf('%s-profile-%s%s', $cleanLastName, $numericHash, $extension);

        // [PENTING] Hapus foto lama di server agar tidak menumpuk menjadi file sampah
        $oldProfileImage = $user->getSetting('profileImage');
        if ($oldProfileImage && isset($oldProfileImage['uploadName'])) {
            $fileManager->removeSiteFile($oldProfileImage['uploadName']);
        }

        // Upload file asli secara sementara dengan nama baru
        if (!$fileManager->uploadSiteFile('profileImage', $uploadName)) return false;

        $filePath = $fileManager->getSiteFilesPath();
        $fullFilePath = $filePath . '/' . $uploadName;
        
        // PHP 7.4/8.x Strict Safety: Pastikan file benar-benar ada sebelum diproses
        if (!file_exists($fullFilePath)) return false;
        
        $imageSize = @getimagesize($fullFilePath);
        
        // Jika file bukan gambar valid (misal file .txt di-rename), getimagesize return false
        if ($imageSize === false) {
            $fileManager->removeSiteFile($uploadName);
            return false;
        }

        list($width, $height) = $imageSize;
        $mime = $imageSize['mime'];

        // =================================================================
        // 2. UX: AUTO-KOMPRESI GAMBAR (DENGAN SAFE GUARD GD LIBRARY)
        // =================================================================
        $maxFileSize = 1048576; // 1 MB (dalam bytes)
        $actualFileSize = filesize($fullFilePath);

        if ($actualFileSize > $maxFileSize) {
            
            // [WIZDAM SAFETY CHECK] Periksa apakah GD Library tersedia di server
            $gdInstalled = extension_loaded('gd') && function_exists('imagecreatetruecolor');

            if ($gdInstalled) {
                // RENCANA A: Lakukan Kompresi Cerdas dengan GD Library
                $image = null;
                
                switch ($mime) {
                    case 'image/jpeg':
                    case 'image/pjpeg':
                        $image = @imagecreatefromjpeg($fullFilePath);
                        break;
                    case 'image/png':
                    case 'image/x-png':
                        $image = @imagecreatefrompng($fullFilePath);
                        break;
                    case 'image/gif':
                        $image = @imagecreatefromgif($fullFilePath);
                        break;
                }

                if ($image) {
                    $maxWidth = 800;
                    $maxHeight = 800;
                    $newWidth = $width;
                    $newHeight = $height;

                    if ($width > $maxWidth || $height > $maxHeight) {
                        $ratio = min($maxWidth / $width, $maxHeight / $height);
                        $newWidth = (int)($width * $ratio);
                        $newHeight = (int)($height * $ratio);
                    }

                    $newImage = imagecreatetruecolor($newWidth, $newHeight);
                    
                    if ($mime == 'image/png' || $mime == 'image/gif') {
                        imagecolortransparent($newImage, imagecolorallocatealpha($newImage, 0, 0, 0, 127));
                        imagealphablending($newImage, false);
                        imagesavealpha($newImage, true);
                    }

                    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

                    switch ($mime) {
                        case 'image/jpeg':
                        case 'image/pjpeg':
                            imagejpeg($newImage, $fullFilePath, 75); 
                            break;
                        case 'image/png':
                        case 'image/x-png':
                            imagepng($newImage, $fullFilePath, 8); 
                            break;
                        case 'image/gif':
                            imagegif($newImage, $fullFilePath);
                            break;
                    }

                    imagedestroy($image);
                    imagedestroy($newImage);
                    
                    $width = $newWidth;
                    $height = $newHeight;
                } else {
                    // Fallback jika file corrupt dan gagal dibaca GD
                    $fileManager->removeSiteFile($uploadName); 
                    return false;
                }
                
            } else {
                // RENCANA B: Fallback jika server tidak memiliki GD Library
                $user->updateSetting('profileImage', null);
                $fileManager->removeSiteFile($uploadName); 
                return false; 
            }
        }

        // Keamanan akhir: pastikan dimensi masuk akal
        if ($width <= 0 || $height <= 0) {
            $user->updateSetting('profileImage', null);
            $fileManager->removeSiteFile($uploadName); 
            return false;
        }

        $userSetting = array(
            'name' => $fileManager->getUploadedFileName('profileImage'),
            'uploadName' => $uploadName,
            'width' => $width,
            'height' => $height,
            'dateUploaded' => Core::getCurrentDate()
        );

        $user->updateSetting('profileImage', $userSetting);
        return true;
    }

    /**
     * Display the form.
     * @param PKPRequest|null $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        $user = Request::getUser();
        if (!$user) {
            Request::redirect(null, 'login');
            return;
        }

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('username', $user->getUsername());

        $site = Request::getSite();
        $templateMgr->assign('availableLocales', $site->getSupportedLocaleNames());

        $roleDao = DAORegistry::getDAO('RoleDAO');
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $userSettingsDao = DAORegistry::getDAO('UserSettingsDAO');
        $userDao = DAORegistry::getDAO('UserDAO');

        $journals = $journalDao->getJournals(true);
        $journals = $journals->toArray();

        foreach ($journals as $thisJournal) {
            if ($thisJournal->getSetting('publishingMode') == PUBLISHING_MODE_SUBSCRIPTION && $thisJournal->getSetting('enableOpenAccessNotification')) {
                $templateMgr->assign('displayOpenAccessNotification', true);
                $templateMgr->assign('user', $user);
                break;
            }
        }

        $templateMgr->assign('genderOptions', $userDao->getGenderOptions());

        $countryDao = DAORegistry::getDAO('CountryDAO');
        $countries = $countryDao->getCountries();

        $templateMgr->assign('journals', $journals);
        $templateMgr->assign('countries', $countries);
        $templateMgr->assign('helpTopicId', 'user.registerAndProfile');

        $journal = Request::getJournal();
        if ($journal) {
            $roles = $roleDao->getRolesByUserId($user->getId(), $journal->getId());
            $roleNames = array();
            foreach ($roles as $role) $roleNames[$role->getRolePath()] = $role->getRoleName();
            
            $templateMgr->assign('allowRegReviewer', $journal->getSetting('allowRegReviewer'));
            $templateMgr->assign('allowRegAuthor', $journal->getSetting('allowRegAuthor'));
            $templateMgr->assign('allowRegReader', $journal->getSetting('allowRegReader'));
            $templateMgr->assign('roles', $roleNames);
        }

        $templateMgr->assign('profileImage', $user->getSetting('profileImage'));

        parent::display();
    }

    /**
     * Get locale field names.
     * @return array
     */
    public function getLocaleFieldNames() {
        $userDao = DAORegistry::getDAO('UserDAO');
        return $userDao->getLocaleFieldNames();
    }

    /**
     * Initialize form data from current settings.
     * @param mixed $args
     * @param PKPRequest $request
     */
    public function initData($args = null, $request = null) {
        $user = $request->getUser();
        if (!$user) return; // Safety

        import('lib.pkp.classes.user.InterestManager');
        $interestManager = new InterestManager();

        $this->_data = array(
            'salutation' => $user->getSalutation(),
            'firstName' => $user->getFirstName(),
            'middleName' => $user->getMiddleName(),
            'initials' => $user->getInitials(),
            'lastName' => $user->getLastName(),
            'suffix' => $user->getSuffix(),
            'gender' => $user->getGender(),
            'affiliation' => $user->getAffiliation(null), // Localized
            'signature' => $user->getSignature(null), // Localized
            'email' => $user->getEmail(),
            'orcid' => $user->getData('orcid'),
            'userUrl' => $user->getUrl(),
            'googleScholar' => $user->getGoogleScholar(),
            'sintaId' => $user->getSintaId(),
            'scopusId' => $user->getScopusId(),
            'dimensionId' => $user->getDimensionId(),
            'researcherId' => $user->getResearcherId(),
            'phone' => $user->getPhone(),
            'fax' => $user->getFax(),
            'mailingAddress' => $user->getMailingAddress(),
            'country' => $user->getCountry(),
            'biography' => $user->getBiography(null), // Localized
            'userLocales' => $user->getLocales(),
            'isAuthor' => Validation::isAuthor(),
            'isReader' => Validation::isReader(),
            'isReviewer' => Validation::isReviewer(),
            'interestsKeywords' => $interestManager->getInterestsForUser($user),
            'interestsTextOnly' => $interestManager->getInterestsString($user),
        );

        return parent::initData();
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(array(
            'salutation',
            'firstName',
            'middleName',
            'lastName',
            'suffix',
            'gender',
            'initials',
            'affiliation',
            'signature',
            'email',
            'orcid',
            'userUrl',
            'googleScholar',
            'sintaId',
            'scopusId',
            'dimensionId',
            'researcherId',
            'phone',
            'fax',
            'mailingAddress',
            'country',
            'biography',
            'keywords',
            'interestsTextOnly',
            'userLocales',
            'readerRole',
            'authorRole',
            'reviewerRole'
        ));

        if ($this->getData('userLocales') == null || !is_array($this->getData('userLocales'))) {
            $this->setData('userLocales', array());
        }

        $keywords = $this->getData('keywords');
        if ($keywords != null && is_array($keywords) && isset($keywords['interests']) && is_array($keywords['interests'])) {
            // The interests are coming in encoded -- Decode them for DB storage
            $this->setData('interestsKeywords', array_map('urldecode', $keywords['interests']));
        }
    }

    /**
     * Save profile settings.
     * @param object|null $object
     */
    public function execute($object = null) {
        $user = Request::getUser();
        if (!$user) return; // Safety

        $user->setSalutation($this->getData('salutation'));
        $user->setFirstName($this->getData('firstName'));
        $user->setMiddleName($this->getData('middleName'));
        $user->setLastName($this->getData('lastName'));
        $user->setSuffix($this->getData('suffix'));
        $user->setGender($this->getData('gender'));
        $user->setInitials($this->getData('initials'));
        $user->setAffiliation($this->getData('affiliation'), null); // Localized
        $user->setSignature($this->getData('signature'), null); // Localized
        $user->setEmail($this->getData('email'));
        $user->setData('orcid', $this->getData('orcid'));
        $user->setUrl($this->getData('userUrl'));
        $user->setGoogleScholar($this->getData('googleScholar'));
        $user->setSintaId($this->getData('sintaId'));
        $user->setScopusId($this->getData('scopusId'));
        $user->setDimensionId($this->getData('dimensionId'));
        $user->setResearcherId($this->getData('researcherId'));
        $user->setPhone($this->getData('phone'));
        $user->setFax($this->getData('fax'));
        $user->setMailingAddress($this->getData('mailingAddress'));
        $user->setCountry($this->getData('country'));
        $user->setBiography($this->getData('biography'), null); // Localized

        // Insert the user interests
        $interests = $this->getData('interestsKeywords') ? $this->getData('interestsKeywords') : $this->getData('interestsTextOnly');
        import('lib.pkp.classes.user.InterestManager');
        $interestManager = new InterestManager();
        $interestManager->setInterestsForUser($user, $interests);

        $site = Request::getSite();
        $availableLocales = $site->getSupportedLocales();

        $locales = array();
        // PHP 8 Safety: Ensure iterable
        $userLocales = $this->getData('userLocales');
        if (is_array($userLocales)) {
            foreach ($userLocales as $locale) {
                if (AppLocale::isLocaleValid($locale) && in_array($locale, $availableLocales)) {
                    array_push($locales, $locale);
                }
            }
        }
        $user->setLocales($locales);

        parent::execute($user);

        $userDao = DAORegistry::getDAO('UserDAO');
        $userDao->updateObject($user);

        $roleDao = DAORegistry::getDAO('RoleDAO');
        $journalDao = DAORegistry::getDAO('JournalDAO');

        // Roles
        $journal = Request::getJournal();
        if ($journal) {
            $role = new Role();
            $role->setUserId($user->getId());
            $role->setJournalId($journal->getId());
            
            // Simplified logic for readability
            $roleChecks = [
                'allowRegReviewer' => ['id' => ROLE_ID_REVIEWER, 'check' => Validation::isReviewer(), 'var' => 'reviewerRole'],
                'allowRegAuthor' => ['id' => ROLE_ID_AUTHOR, 'check' => Validation::isAuthor(), 'var' => 'authorRole'],
                'allowRegReader' => ['id' => ROLE_ID_READER, 'check' => Validation::isReader(), 'var' => 'readerRole'],
            ];

            foreach ($roleChecks as $setting => $data) {
                if ($journal->getSetting($setting)) {
                    $role->setRoleId($data['id']);
                    $hasRole = $data['check'];
                    $wantsRole = Request::getUserVar($data['var']);
                    
                    if ($hasRole && !$wantsRole) $roleDao->deleteRole($role);
                    if (!$hasRole && $wantsRole) $roleDao->insertRole($role);
                }
            }
        }

        $openAccessNotify = Request::getUserVar('openAccessNotify');

        $userSettingsDao = DAORegistry::getDAO('UserSettingsDAO');
        $journals = $journalDao->getJournals(true);
        $journals = $journals->toArray();

        foreach ($journals as $thisJournal) {
            if ($thisJournal->getSetting('publishingMode') == PUBLISHING_MODE_SUBSCRIPTION && $thisJournal->getSetting('enableOpenAccessNotification')) {
                $currentlyReceives = $user->getSetting('openAccessNotification', $thisJournal->getJournalId());
                $shouldReceive = !empty($openAccessNotify) && is_array($openAccessNotify) && in_array($thisJournal->getJournalId(), $openAccessNotify);
                
                if ($currentlyReceives != $shouldReceive) {
                    $userSettingsDao->updateSetting($user->getId(), 'openAccessNotification', $shouldReceive, 'bool', $thisJournal->getJournalId());
                }
            }
        }

        $auth = null;
        if ($user->getAuthId()) {
            $authDao = DAORegistry::getDAO('AuthSourceDAO');
            $auth = $authDao->getPlugin($user->getAuthId());
        }

        if (isset($auth)) {
            $auth->doSetUserInfo($user);
        }
    }
}
?>