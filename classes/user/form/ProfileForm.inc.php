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
 * LUMERA MODERNIZATION:
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
            Request::redirect(null, 'login');
            return;
        }
        
        $this->user = $user;
        
        // Validation checks for this form
        $this->addCheck(new FormValidator($this, 'firstName', 'required', 'user.profile.form.firstNameRequired'));
        $this->addCheck(new FormValidator($this, 'lastName', 'required', 'user.profile.form.lastNameRequired'));
        $this->addCheck(new FormValidatorUrl($this, 'userUrl', 'optional', 'user.profile.form.urlInvalid'));
        $this->addCheck(new FormValidatorEmail($this, 'email', 'required', 'user.profile.form.emailRequired'));
        $this->addCheck(new FormValidatorORCID($this, 'orcid', 'optional', 'user.profile.form.orcidInvalid'));
        $this->addCheck(new FormValidatorCustom($this, 'email', 'required', 'user.register.form.emailExists', [DAORegistry::getDAO('UserDAO'), 'userExistsByEmail'], [$user->getId(), true], true));
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ProfileForm() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().", E_USER_DEPRECATED);
        }
        $this->__construct();
    }

    /**
     * Deletes a profile image.
     * @return boolean
     */
    public function deleteProfileImage() {
        $user = Request::getUser();
        if (!$user) return false;

        $profileImage = $user->getSetting('profileImage');
        if (!$profileImage || !isset($profileImage['uploadName'])) return false;

        import('classes.file.PublicFileManager');
        $fileManager = new PublicFileManager();
        if ($fileManager->removeSiteFile((string) $profileImage['uploadName'])) {
            return $user->updateSetting('profileImage', null);
        }
        return false;
    }

    /**
     * Uploads a profile image.
     * [LUMERA] Auto-Compression & Secure Unique Numeric Filename
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

        // 1. KEAMANAN: GENERATE NAMA FILE
        $lastName = (string) $user->getLastName();
        $cleanLastName = !empty($lastName) ? preg_replace('/[^a-zA-Z0-9]/', '', strtolower($lastName)) : 'usr';
        
        $userId = (int) $user->getId();
        $obfuscatedId = ($userId * 83) + 10024; 
        $dynamicNumbers = mt_rand(100, 999) . date('is'); 
        $numericHash = (string) $obfuscatedId . $dynamicNumbers;

        $uploadName = sprintf('%s-profile-%s%s', $cleanLastName, $numericHash, (string) $extension);

        // Hapus foto lama
        $oldProfileImage = $user->getSetting('profileImage');
        if ($oldProfileImage && isset($oldProfileImage['uploadName'])) {
            $fileManager->removeSiteFile((string) $oldProfileImage['uploadName']);
        }

        if (!$fileManager->uploadSiteFile('profileImage', $uploadName)) return false;

        $filePath = $fileManager->getSiteFilesPath();
        $fullFilePath = $filePath . '/' . $uploadName;
        
        if (!file_exists($fullFilePath)) return false;
        
        $imageSize = @getimagesize($fullFilePath);
        if ($imageSize === false) {
            $fileManager->removeSiteFile($uploadName);
            return false;
        }

        $width = (int) $imageSize[0];
        $height = (int) $imageSize[1];
        $mime = $imageSize['mime'] ?? 'application/octet-stream';

        // 2. UX: AUTO-KOMPRESI GAMBAR
        $maxFileSize = 1048576; // 1 MB
        $actualFileSize = (int) filesize($fullFilePath);

        if ($actualFileSize > $maxFileSize) {
            $gdInstalled = extension_loaded('gd') && function_exists('imagecreatetruecolor');

            if ($gdInstalled) {
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
                    
                    if ($mime === 'image/png' || $mime === 'image/gif') {
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
                    $fileManager->removeSiteFile($uploadName); 
                    return false;
                }
            } else {
                $user->updateSetting('profileImage', null);
                $fileManager->removeSiteFile($uploadName); 
                return false; 
            }
        }

        if ($width <= 0 || $height <= 0) {
            $user->updateSetting('profileImage', null);
            $fileManager->removeSiteFile($uploadName); 
            return false;
        }

        $userSetting = [
            'name' => (string) $fileManager->getUploadedFileName('profileImage'),
            'uploadName' => $uploadName,
            'width' => $width,
            'height' => $height,
            'dateUploaded' => Core::getCurrentDate()
        ];

        $user->updateSetting('profileImage', $userSetting);
        return true;
    }

    /**
     * Display the form.
     * @param mixed $request
     * @param mixed $template
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

        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');

        $journals = $journalDao->getJournals(true);
        $journalsArray = is_object($journals) ? $journals->toArray() : [];

        foreach ($journalsArray as $thisJournal) {
            if ($thisJournal->getSetting('publishingMode') == PUBLISHING_MODE_SUBSCRIPTION && $thisJournal->getSetting('enableOpenAccessNotification')) {
                $templateMgr->assign('displayOpenAccessNotification', true);
                $templateMgr->assign('user', $user);
                break;
            }
        }

        $templateMgr->assign('genderOptions', $userDao->getGenderOptions());

        /** @var CountryDAO $countryDao */
        $countryDao = DAORegistry::getDAO('CountryDAO');
        $countries = $countryDao->getCountries();

        $templateMgr->assign('journals', $journalsArray);
        $templateMgr->assign('countries', $countries);
        $templateMgr->assign('helpTopicId', 'user.registerAndProfile');

        $journal = Request::getJournal();
        if ($journal) {
            $roles = $roleDao->getRolesByUserId($user->getId(), $journal->getId());
            $roleNames = [];
            if (is_array($roles)) {
                foreach ($roles as $role) {
                    $roleNames[(string) $role->getRolePath()] = (string) $role->getRoleName();
                }
            }
            
            $templateMgr->assign('allowRegReviewer', $journal->getSetting('allowRegReviewer'));
            $templateMgr->assign('allowRegAuthor', $journal->getSetting('allowRegAuthor'));
            $templateMgr->assign('allowRegReader', $journal->getSetting('allowRegReader'));
            $templateMgr->assign('roles', $roleNames);
        }
        $templateMgr->assign('profileImage', $user->getSetting('profileImage'));

        parent::display($request, $template);
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
     * @param mixed $request
     */
    public function initData($args = null, $request = null) {
        $user = $request ? $request->getUser() : Request::getUser();
        if (!$user) return;

        import('lib.pkp.classes.user.InterestManager');
        $interestManager = new InterestManager();

        $this->_data = [
            'salutation' => $user->getSalutation(),
            'firstName' => $user->getFirstName(),
            'middleName' => $user->getMiddleName(),
            'initials' => $user->getInitials(),
            'lastName' => $user->getLastName(),
            'suffix' => $user->getSuffix(),
            'gender' => $user->getGender(),
            'affiliation' => $user->getAffiliation(null),
            'signature' => $user->getSignature(null),
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
            'biography' => $user->getBiography(null),
            'userLocales' => $user->getLocales(),
            'isAuthor' => Validation::isAuthor(),
            'isReader' => Validation::isReader(),
            'isReviewer' => Validation::isReviewer(),
            'interestsKeywords' => $interestManager->getInterestsForUser($user),
            'interestsTextOnly' => $interestManager->getInterestsString($user),
            'gossip' => is_array($user->getSetting('gossip')) ? $user->getSetting('gossip') : [],
        ];

        return parent::initData($args, $request);
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars([
            'salutation', 'firstName', 'middleName', 'lastName', 'suffix', 'gender',
            'initials', 'affiliation', 'signature', 'email', 'orcid', 'userUrl',
            'googleScholar', 'sintaId', 'scopusId', 'dimensionId', 'researcherId',
            'phone', 'fax', 'mailingAddress', 'country', 'biography', 'keywords',
            'interestsTextOnly', 'userLocales', 'readerRole', 'authorRole', 'reviewerRole',
            'gossip'
        ]);

        $userLocales = $this->getData('userLocales');
        if ($userLocales === null || !is_array($userLocales)) {
            $this->setData('userLocales', []);
        }

        $keywords = $this->getData('keywords');
        if ($keywords !== null && is_array($keywords) && isset($keywords['interests']) && is_array($keywords['interests'])) {
            $this->setData('interestsKeywords', array_map('urldecode', $keywords['interests']));
        }
    }

    /**
     * Save profile settings.
     * @param mixed $object
     */
    public function execute($object = null) {
        $user = Request::getUser();
        if (!$user) return;

        $user->setSalutation($this->getData('salutation'));
        $user->setFirstName($this->getData('firstName'));
        $user->setMiddleName($this->getData('middleName'));
        $user->setLastName($this->getData('lastName'));
        $user->setSuffix($this->getData('suffix'));
        $user->setGender($this->getData('gender'));
        $user->setInitials($this->getData('initials'));
        $user->setAffiliation($this->getData('affiliation'), null);
        $user->setSignature($this->getData('signature'), null);
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
        $user->setBiography($this->getData('biography'), null);

        $gossip = $this->getData('gossip');
        if (is_array($gossip)) {
            $user->updateSetting('gossip', $gossip, 'object', true);
        }

        $interests = $this->getData('interestsKeywords') ?: $this->getData('interestsTextOnly');
        import('lib.pkp.classes.user.InterestManager');
        $interestManager = new InterestManager();
        $interestManager->setInterestsForUser($user, $interests);

        $site = Request::getSite();
        $availableLocales = $site->getSupportedLocales();

        $locales = [];
        $userLocales = $this->getData('userLocales');
        if (is_array($userLocales)) {
            foreach ($userLocales as $locale) {
                if (AppLocale::isLocaleValid((string) $locale) && in_array($locale, $availableLocales)) {
                    $locales[] = (string) $locale;
                }
            }
        }
        $user->setLocales($locales);

        parent::execute($user);

        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $userDao->updateObject($user);

        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');

        $journal = Request::getJournal();
        if ($journal) {
            $role = new Role();
            $role->setUserId($user->getId());
            $role->setJournalId($journal->getId());
            
            $roleChecks = [
                'allowRegReviewer' => ['id' => ROLE_ID_REVIEWER, 'check' => Validation::isReviewer(), 'var' => 'reviewerRole'],
                'allowRegAuthor' => ['id' => ROLE_ID_AUTHOR, 'check' => Validation::isAuthor(), 'var' => 'authorRole'],
                'allowRegReader' => ['id' => ROLE_ID_READER, 'check' => Validation::isReader(), 'var' => 'readerRole'],
            ];

            foreach ($roleChecks as $setting => $data) {
                if ($journal->getSetting($setting)) {
                    $role->setRoleId($data['id']);
                    $hasRole = (bool) $data['check'];
                    $wantsRole = (bool) Request::getUserVar($data['var']);
                    
                    if ($hasRole && !$wantsRole) $roleDao->deleteRole($role);
                    if (!$hasRole && $wantsRole) $roleDao->insertRole($role);
                }
            }
        }

        $openAccessNotify = Request::getUserVar('openAccessNotify');

        /** @var UserSettingsDAO $userSettingsDao */
        $userSettingsDao = DAORegistry::getDAO('UserSettingsDAO');
        $journals = $journalDao->getJournals(true);
        $journalsArray = is_object($journals) ? $journals->toArray() : [];

        foreach ($journalsArray as $thisJournal) {
            if ($thisJournal->getSetting('publishingMode') == PUBLISHING_MODE_SUBSCRIPTION && $thisJournal->getSetting('enableOpenAccessNotification')) {
                $currentlyReceives = (bool) $user->getSetting('openAccessNotification', $thisJournal->getJournalId());
                $shouldReceive = is_array($openAccessNotify) && in_array($thisJournal->getJournalId(), $openAccessNotify);

                if ($currentlyReceives !== $shouldReceive) {
                    $userSettingsDao->updateSetting($user->getId(), 'openAccessNotification', $shouldReceive, 'bool', $thisJournal->getJournalId());
                }
            }
        }

        if ($user->getAuthId()) {
            /** @var AuthSourceDAO $authDao */
            $authDao = DAORegistry::getDAO('AuthSourceDAO');
            $auth = $authDao->getPlugin($user->getAuthId());
            if ($auth) {
                $auth->doSetUserInfo($user);
            }
        }
    }

}
?>