<?php
declare(strict_types=1);

/**
 * @file lib/pkp/classes/core/PKPWizdamEditorStaff.inc.php
 *
 * @brief Sistem cache data staff jurnal (Manager dan Editor) untuk homepage.
 * Versi core dari skrip tema kustom.
 *
 * @author Rochmady and Wizdam Team
 * @version v1.22.5 (Core Refactor)
 */

// Import DAO yang dibutuhkan
// import('classes.security.RoleDAO');
// import('classes.user.UserDAO');
// import('classes.user.UserSettingsDAO');
// import('classes.journal.JournalDAO');
// import('classes.user.User');
// import('classes.i18n.CountryDAO'); // Path yang benar

class PKPWizdamEditorStaff {

    // Role ID - konstanta OJS
    const ROLE_JOURNAL_MANAGER = 16;
    const ROLE_EDITOR = 256;

    /**
     * @brief Metode publik utama untuk dipanggil dari handler lain (spt IndexHandler).
     * @param $journal Journal Objek jurnal saat ini
     * @param $templateMgr TemplateManager Objek template manager
     * @param $maxDisplayCount int Jumlah maksimum staff yang ditampilkan
     */
    public static function displayHomepageStaff($journal, $templateMgr, $maxDisplayCount = 3) {

        if (!$journal) return;

        $journalId = $journal->getId();

        // Konfigurasi cache
        $cacheEnabled = true; // Anda bisa membuat ini setting jurnal jika mau
        $cacheDir = self::getCacheDir();
        $cacheKey = 'journal_staff_' . $journalId . '_' . $maxDisplayCount;
        $cacheFile = $cacheDir . $cacheKey . '.json';

        // Generate hash untuk deteksi perubahan
        $currentDataHash = self::getStaffDataHash($journalId, $maxDisplayCount);

        // Cek apakah data staff berubah
        if ($cacheEnabled && !self::isStaffDataChanged($cacheFile, $currentDataHash)) {
            $cachedData = self::loadFromCache($cacheFile);
            if ($cachedData !== false) {
                // Load dari cache
                $templateMgr->assign('journalManagers', $cachedData['managers']);
                $templateMgr->assign('journalEditors', $cachedData['editors']);
                return;
            }
        }

        // --- Cache tidak ada atau usang, generate data baru ---

        $locale = $journal->getPrimaryLocale();
        if (empty($locale)) {
            $locale = AppLocale::getLocale();
        }

        $managers = array();
        $editors = array();
        $managerUserIds = array(); 

        $roleDao = &DAORegistry::getDAO('RoleDAO');
        $userDao = &DAORegistry::getDAO('UserDAO');
        $countryDao = &DAORegistry::getDAO('CountryDAO');

        // Mendapatkan daftar manager
        $managersObj = $roleDao->getUsersByRoleId(self::ROLE_JOURNAL_MANAGER, $journalId);
        $managerCount = 0;
        while ($manager = $managersObj->next()) {
            if ($managerCount >= $maxDisplayCount) break;

            $userId = $manager->getId();
            $user = $userDao->getById($userId); // Menggunakan getById
            if (!$user) continue;

            $managerUserIds[] = $userId;
            $managers[] = self::processUserData($user, $locale, $countryDao);
            $managerCount++;
        }

        // Mendapatkan daftar editor
        $editorsObj = $roleDao->getUsersByRoleId(self::ROLE_EDITOR, $journalId);
        $editorCount = 0;
        while ($editor = $editorsObj->next()) {
            if ($editorCount >= $maxDisplayCount) break;

            $userId = $editor->getId();
            // Skip jika user ini juga Journal Manager
            if (in_array($userId, $managerUserIds)) {
                continue;
            }

            $user = $userDao->getById($userId); // Menggunakan getById (bukan getUser)
            if (!$user) continue;

            $editors[] = self::processUserData($user, $locale, $countryDao);
            $editorCount++;
        }

        // Simpan ke cache dengan hash
        if ($cacheEnabled) {
            $dataToCache = array(
                'managers' => $managers,
                'editors' => $editors,
                'generated_at' => time(),
                'journal_id' => $journalId,
                'max_display_count' => $maxDisplayCount,
                'data_hash' => $currentDataHash
            );
            self::saveToCache($cacheFile, $dataToCache);
        }

        // Menetapkan variabel ke Smarty
        $templateMgr->assign('journalManagers', $managers);
        $templateMgr->assign('journalEditors', $editors);
    }

    /**
     * @brief Memproses objek User menjadi array data yang siap ditampilkan.
     * @param $user User Objek user
     * @param $locale string Locale
     * @param $countryDao CountryDAO Objek CountryDAO
     * @return array
     */
    private static function processUserData($user, $locale, $countryDao) {
        $userId = $user->getId();

        // Mendapatkan prefix
        $prefix = self::getUserSetting($userId, 'prefix', $locale);
        if (empty($prefix)) {
            $prefix = self::getUserSetting($userId, 'prefix', 'en_US'); // Fallback
        }

        // Afiliasi
        $originalAffiliation = $user->getAffiliation($locale);
        $affiliation = self::processAffiliation($originalAffiliation);
        $affiliationWasProcessed = ($originalAffiliation != $affiliation && !empty($originalAffiliation));

        // Negara
        $countryCode = $user->getCountry();
        $countryName = '';
        if (!$affiliationWasProcessed && !empty($countryCode)) {
            $countryName = $countryDao->getCountry($countryCode, $locale);
            if (empty($countryName)) {
                $countryName = $countryDao->getCountry($countryCode, 'en_US'); // Fallback
            }
        }

        // Email & Gambar
        $userEmail = $user->getEmail();
        $hasProfileImage = self::profileImageExists($userId) ? true : false;
        $profileImageUrl = self::getProfileImageUrl($userId);

        if (!$hasProfileImage && !empty($userEmail)) {
            $gravatarInfo = self::getGravatarInfo($userEmail);
            $profileImageUrl = $gravatarInfo['imageUrl'];
            $hasProfileImage = $gravatarInfo['hasProfileImage'];
        }

        return array(
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
        );
    }

    /**
     * @brief Mendapatkan direktori cache yang standar OJS.
     * @return string Path ke direktori cache
     */
    private static function getCacheDir() {
        // Menggunakan direktori cache standar OJS
        return 'cache/t_wizdam/staff/';
    }

    /**
     * @brief Memastikan direktori cache ada.
     * @param $dir string Path
     */
    private static function ensureCacheDir($dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * @brief Mendapatkan hash dari data staff untuk deteksi perubahan.
     * @param int $journalId
     * @param int $maxDisplayCount
     * @return string MD5 hash
     */
    private static function getStaffDataHash($journalId, $maxDisplayCount) {
        $roleDao = &DAORegistry::getDAO('RoleDAO');
        $userDao = &DAORegistry::getDAO('UserDAO');
        $hashData = array();
        $managerIds = array();

        // Get managers
        $managersObj = $roleDao->getUsersByRoleId(self::ROLE_JOURNAL_MANAGER, $journalId);
        $managerCount = 0;
        while ($manager = $managersObj->next()) {
            if ($managerCount >= $maxDisplayCount) break;
            $userId = $manager->getId();
            $user = $userDao->getById($userId);
            if (!$user) continue;
            $managerIds[] = $userId;
            $hashData[] = array(
                'id' => $userId, 'role' => 'manager', 'name' => $user->getFullName(),
                'email' => $user->getEmail(), 'affiliation' => $user->getLocalizedAffiliation(),
                'country' => $user->getCountry()
            );
            $managerCount++;
        }

        // Get editors
        $editorsObj = $roleDao->getUsersByRoleId(self::ROLE_EDITOR, $journalId);
        $editorCount = 0;
        while ($editor = $editorsObj->next()) {
            if ($editorCount >= $maxDisplayCount) break;
            $userId = $editor->getId();
            $user = $userDao->getById($userId);
            if (!$user) continue;
            if (in_array($userId, $managerIds)) continue;
            $hashData[] = array(
                'id' => $userId, 'role' => 'editor', 'name' => $user->getFullName(),
                'email' => $user->getEmail(), 'affiliation' => $user->getLocalizedAffiliation(),
                'country' => $user->getCountry()
            );
            $editorCount++;
        }

        $hashData['daily_refresh'] = date('Y-m-d');
        return md5(serialize($hashData));
    }

    /**
     * @brief Cek apakah data staff berubah.
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
     * @brief Load data dari cache.
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
     * @brief Simpan data ke cache.
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
     * @brief Helper untuk mendapatkan setting user.
     * @param int $userId
     * @param string $settingName
     * @param string $locale
     * @return string
     */
    private static function getUserSetting($userId, $settingName, $locale) {
        $userSettingsDao = &DAORegistry::getDAO('UserSettingsDAO');
        return $userSettingsDao->getSetting($userId, $settingName, $locale);
    }

    /**
     * @brief Cek apakah gambar profil ada.
     * @param int $userId
     * @return string|false
     */
    private static function profileImageExists($userId) {
        $baseDir = Config::getVar('files', 'public_files_dir') . '/site/';
        $formats = array('jpg', 'gif', 'png');
        foreach ($formats as $format) {
            $filename = 'profileImage-' . $userId . '.' . $format;
            if (file_exists($baseDir . $filename)) {
                return $format;
            }
        }
        return false;
    }

    /**
     * @brief Mendapatkan URL gambar profil.
     * @param int $userId
     * @return string|null
     */
    private static function getProfileImageUrl($userId) {
        $format = self::profileImageExists($userId);
        if ($format) {
            $baseUrl = Request::getBaseUrl();
            return $baseUrl . '/public/site/profileImage-' . $userId . '.' . $format;
        }
        return null;
    }

    /**
     * @brief Proses afiliasi.
     * @param string $affiliation
     * @return string
     */
    private static function processAffiliation($affiliation) {
        if (empty($affiliation)) return '';
        $parts = explode("\n", $affiliation);
        return trim($parts[0]);
    }

    /**
     * @brief Cek dan generate Gravatar URL.
     * @param string $email
     * @return array
     */
    private static function getGravatarInfo($email) {
        if (empty($email)) {
            return array('imageUrl' => null, 'hasProfileImage' => false);
        }
        
        $gravatarUrl = "https://www.gravatar.com/avatar/" . md5(strtolower(trim($email))) . "?s=150&d=404";

        $context = stream_context_create(array(
            'http' => array('timeout' => 2, 'method' => 'HEAD')
        ));

        // [WIZDAM FIX] PHP 8.0+: parameter $associative harus bool, bukan int
        $headers = @get_headers($gravatarUrl, false, $context);

        // Pastikan $headers tidak false sebelum mengakses array index 0
        if ($headers && isset($headers[0]) && strpos($headers[0], '200') !== false) {
            return array('imageUrl' => $gravatarUrl, 'hasProfileImage' => true);
        }
        
        return array('imageUrl' => null, 'hasProfileImage' => false);
    }
}

?>