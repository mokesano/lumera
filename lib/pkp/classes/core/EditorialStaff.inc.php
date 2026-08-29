<?php
declare(strict_types=1);

/**
 * @file lib/pkp/classes/core/EditorialStaff.inc.php
 * 
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Codecanau Team
 * Distributed under the GNU GPL v3.
 * 
 * @class EditorialStaff
 * @ingroup core
 *
 * @brief Caching system for journal editorial staff (Managers and Editors) 
 * to optimize homepage rendering performance.
 */

// Import DAO yang dibutuhkan
// import('classes.security.RoleDAO');
// import('classes.user.UserDAO');
// import('classes.user.UserSettingsDAO');
// import('classes.journal.JournalDAO');
// import('classes.user.User');
// import('classes.i18n.CountryDAO');

class EditorialStaff {

    const ROLE_JOURNAL_MANAGER = 16; // ROLE_ID_MANAGER
    const ROLE_EDITOR = 256;         // ROLE_ID_EDITOR

    /**
     * Main entry point to fetch and assign staff data to the template manager.
     * Utilizes a JSON file cache with hash-based invalidation to prevent 
     * redundant database queries on every homepage load.
     * @param Journal $journal
     * @param TemplateManager $templateMgr
     * @param int $maxDisplayCount
     * @return void
     */
    public static function displayHomepageStaff($journal, $templateMgr, $maxDisplayCount = 3) {
        if (!$journal) {
            return;
        }
        $journalId = (int) $journal->getId();

        $cacheEnabled = true;
        $cacheDir = self::getCacheDir();
        $cacheKey = 'journal_staff_' . $journalId . '_' . $maxDisplayCount;
        $cacheFile = $cacheDir . $cacheKey . '.json';

        $currentDataHash = self::getStaffDataHash($journalId, $maxDisplayCount);

        if ($cacheEnabled && !self::isStaffDataChanged($cacheFile, $currentDataHash)) {
            $cachedData = self::loadFromCache($cacheFile);
            if ($cachedData !== false) {
                $templateMgr->assign('journalManagers', $cachedData['managers']);
                $templateMgr->assign('journalEditors', $cachedData['editors']);
                return;
            }
        }

        $locale = $journal->getPrimaryLocale();
        if (empty($locale)) {
            $locale = AppLocale::getLocale();
        }

        $managers = [];
        $editors = [];
        $managerUserIds = []; 

        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        /** @var CountryDAO $countryDao */
        $countryDao = DAORegistry::getDAO('CountryDAO');

        $managersObj = $roleDao->getUsersByRoleId(self::ROLE_JOURNAL_MANAGER, $journalId);
        $managerCount = 0;
        
        if ($managersObj) {
            while ($manager = $managersObj->next()) {
                if ($managerCount >= $maxDisplayCount) break;

                $userId = (int) $manager->getId();
                $user = $userDao->getById($userId);
                if (!$user) continue;

                $managerUserIds[] = $userId;
                $managers[] = self::processUserData($user, $locale, $countryDao);
                $managerCount++;
            }
        }

        $editorsObj = $roleDao->getUsersByRoleId(self::ROLE_EDITOR, $journalId);
        $editorCount = 0;
        
        if ($editorsObj) {
            while ($editor = $editorsObj->next()) {
                if ($editorCount >= $maxDisplayCount) break;

                $userId = (int) $editor->getId();
                if (in_array($userId, $managerUserIds, true)) {
                    continue;
                }

                $user = $userDao->getById($userId);
                if (!$user) continue;

                $editors[] = self::processUserData($user, $locale, $countryDao);
                $editorCount++;
            }
        }

        if ($cacheEnabled) {
            $dataToCache = [
                'managers' => $managers,
                'editors' => $editors,
                'generated_at' => time(),
                'journal_id' => $journalId,
                'max_display_count' => $maxDisplayCount,
                'data_hash' => $currentDataHash
            ];
            self::saveToCache($cacheFile, $dataToCache);
        }

        $templateMgr->assign('journalManagers', $managers);
        $templateMgr->assign('journalEditors', $editors);
    }

    /**
     * Processes a User object into an array of display-ready data.
     * Handles fallback locales for names and affiliations, and resolves 
     * profile images via local storage or Gravatar.
     * @param User $user
     * @param Locale $locale
     * @param CountryDAO $countryDao
     * @return array
     */
    private static function processUserData($user, $locale, $countryDao) {
        $userId = (int) $user->getId();

        $prefix = self::getUserSetting($userId, 'prefix', $locale);
        if (empty($prefix)) {
            $prefix = self::getUserSetting($userId, 'prefix', 'en_US');
        }

        $originalAffiliation = $user->getAffiliation($locale);
        $affiliation = self::processAffiliation($originalAffiliation);
        $affiliationWasProcessed = ($originalAffiliation !== $affiliation && !empty($originalAffiliation));

        $countryCode = $user->getCountry();
        $countryName = '';
        if (!$affiliationWasProcessed && !empty($countryCode)) {
            $countryName = $countryDao->getCountry($countryCode, $locale);
            if (empty($countryName)) {
                $countryName = $countryDao->getCountry($countryCode, 'en_US');
            }
        }

        $userEmail = $user->getEmail();
        $hasProfileImage = self::profileImageExists($userId) !== false;
        $profileImageUrl = self::getProfileImageUrl($userId);

        if (!$hasProfileImage && !empty($userEmail)) {
            $gravatarInfo = self::getGravatarInfo($userEmail);
            $profileImageUrl = $gravatarInfo['imageUrl'];
            $hasProfileImage = $gravatarInfo['hasProfileImage'];
        }

        return [
            'userId' => $userId,
            'salutation' => $user->getSalutation(),
            'firstName' => $user->getFirstName(),
            'middleName' => $user->getMiddleName(),
            'lastName' => $user->getLastName(),
            'suffix' => $user->getSuffix(),
            'fullName' => $user->getFullName(),
            'affiliation' => $affiliation,
            'country' => $countryName,
            'email' => $userEmail,
            'imageUrl' => $profileImageUrl,
            'hasProfileImage' => $hasProfileImage
        ];
    }

    /**
     * Get the standard cache directory path.
     * @return string
     */
    private static function getCacheDir() {
        return 'cache/t_wizdam/staff/';
    }

    /**
     * Ensure the cache directory exists.
     * @param string $dir
     * @return void
     */
    private static function ensureCacheDir($dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * Generate an MD5 hash of current staff data for cache invalidation.
     * Includes a daily refresh trigger to prevent stale gravatar/affiliation data.
     * @param int $journalId
     * @param int $maxDisplayCount
     * @return string
     */
    private static function getStaffDataHash($journalId, $maxDisplayCount) {
        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        
        $hashData = [];
        $managerIds = [];

        $managersObj = $roleDao->getUsersByRoleId(self::ROLE_JOURNAL_MANAGER, $journalId);
        $managerCount = 0;
        
        if ($managersObj) {
            while ($manager = $managersObj->next()) {
                if ($managerCount >= $maxDisplayCount) break;
                $userId = (int) $manager->getId();
                $user = $userDao->getById($userId);
                if (!$user) continue;
                
                $managerIds[] = $userId;
                $hashData[] = [
                    'id' => $userId, 'role' => 'manager', 'name' => $user->getFullName(),
                    'email' => $user->getEmail(), 'affiliation' => $user->getLocalizedAffiliation(),
                    'country' => $user->getCountry()
                ];
                $managerCount++;
            }
        }

        $editorsObj = $roleDao->getUsersByRoleId(self::ROLE_EDITOR, $journalId);
        $editorCount = 0;
        
        if ($editorsObj) {
            while ($editor = $editorsObj->next()) {
                if ($editorCount >= $maxDisplayCount) break;
                $userId = (int) $editor->getId();
                $user = $userDao->getById($userId);
                if (!$user) continue;
                if (in_array($userId, $managerIds, true)) continue;
                
                $hashData[] = [
                    'id' => $userId, 'role' => 'editor', 'name' => $user->getFullName(),
                    'email' => $user->getEmail(), 'affiliation' => $user->getLocalizedAffiliation(),
                    'country' => $user->getCountry()
                ];
                $editorCount++;
            }
        }

        $hashData['daily_refresh'] = date('Y-m-d');
        return md5(serialize($hashData));
    }

    /**
     * Check if staff data has changed compared to the cached hash.
     * @param string $cacheFile
     * @param string $currentHash
     * @return bool
     */
    private static function isStaffDataChanged($cacheFile, $currentHash) {
        if (!file_exists($cacheFile)) return true;
        $cachedData = self::loadFromCache($cacheFile);
        if ($cachedData === false || !isset($cachedData['data_hash'])) return true;
        return $cachedData['data_hash'] !== $currentHash;
    }

    /**
     * Load data from the JSON cache file.
     * @param string $cacheFile
     * @return array|false
     */
    private static function loadFromCache($cacheFile) {
        if (!file_exists($cacheFile)) return false;
        $content = @file_get_contents($cacheFile);
        if ($content === false) return false;
        $data = json_decode($content, true);
        return $data !== null ? $data : false;
    }

    /**
     * Save data to the JSON cache file.
     * @param string $cacheFile
     * @param array $data
     * @return bool
     */
    private static function saveToCache($cacheFile, $data) {
        $dir = dirname($cacheFile);
        self::ensureCacheDir($dir);
        $content = json_encode($data, JSON_PRETTY_PRINT);
        return file_put_contents($cacheFile, $content) !== false;
    }

    /**
     * Helper to retrieve a specific user setting.
     * @param int $userId
     * @param string $settingName
     * @param string $locale
     * @return string|null
     */
    private static function getUserSetting($userId, $settingName, $locale) {
        /** @var UserSettingsDAO $userSettingsDao */
        $userSettingsDao = DAORegistry::getDAO('UserSettingsDAO');
        return $userSettingsDao->getSetting($userId, $settingName, $locale);
    }

    /**
     * Check if a local profile image exists for the user.
     * @param int $userId
     * @return string|false Image extension if found, false otherwise.
     */
    private static function profileImageExists($userId) {
        $baseDir = Config::getVar('files', 'public_files_dir') . '/site/';
        $formats = ['jpg', 'gif', 'png'];
        foreach ($formats as $format) {
            $filename = 'profileImage-' . $userId . '.' . $format;
            if (file_exists($baseDir . $filename)) {
                return $format;
            }
        }
        return false;
    }

    /**
     * Get the URL for the user's local profile image.
     * @param int $userId
     * @return string|null
     */
    private static function getProfileImageUrl($userId) {
        $format = self::profileImageExists($userId);
        if ($format) {
            $request = Application::get()->getRequest();
            $baseUrl = $request->getBaseUrl();
            $publicFilesDir = Config::getVar('files', 'public_files_dir');
            return $baseUrl . '/' . $publicFilesDir . '/site/profileImage-' . $userId . '.' . $format;
        }
        return null;
    }

    /**
     * Process affiliation string to extract the primary institution.
     * @param string|null $affiliation
     * @return string
     */
    private static function processAffiliation($affiliation) {
        if (empty($affiliation)) return '';
        $parts = explode("\n", $affiliation);
        return trim($parts[0]);
    }

    /**
     * Check Gravatar availability and generate the image URL.
     * Uses a HEAD request with a short timeout to prevent blocking the page render.
     * @param string $email
     * @return array
     */
    private static function getGravatarInfo($email) {
        if (empty($email)) {
            return ['imageUrl' => null, 'hasProfileImage' => false];
        }
        
        $gravatarUrl = "https://www.gravatar.com/avatar/" . md5(strtolower(trim($email))) . "?s=150&d=404";

        $context = stream_context_create([
            'http' => ['timeout' => 2, 'method' => 'HEAD']
        ]);

        $headers = @get_headers($gravatarUrl, false, $context);

        if ($headers && isset($headers[0]) && strpos($headers[0], '200') !== false) {
            return ['imageUrl' => $gravatarUrl, 'hasProfileImage' => true];
        }
        
        return ['imageUrl' => null, 'hasProfileImage' => false];
    }

}
?>