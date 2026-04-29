<?php
/**
 * File: plugins/themes/[theme-name]/php/journal_staff/getJournalStaff.php
 * Sistem cache untuk data staff jurnal (Manager dan Editor) dengan optimisasi performa
 * @author Rochmady and Wizdam Team
 * @version v1.22.5
 */

// Batasi jumlah staff yang ditampilkan (cuplikan)
$maxDisplayCount = 3;

// Cache configuration
$cacheEnabled = true;
$cacheDir = 'plugins/themes/' . basename(dirname(dirname(__DIR__))) . '/php/journal_staff/cache/';

// Role ID - konstanta OJS
$roleJournalManager = 16; // ROLE_ID_JOURNAL_MANAGER
$roleEditor = 256;        // ROLE_ID_EDITOR

// Mendapatkan ID jurnal dari konteks Smarty
$journal = $this->get_template_vars('currentJournal');
$journalId = $journal->getId();

// Generate cache key berdasarkan journal ID dan max display count
$cacheKey = 'journal_staff_' . $journalId . '_' . $maxDisplayCount;
$cacheFile = $cacheDir . $cacheKey . '.json';

/**
 * Fungsi untuk membuat direktori cache jika belum ada
 */
function ensureCacheDir($dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

/**
 * Fungsi untuk mendapatkan hash dari data staff untuk deteksi perubahan
 * @param int $journalId ID jurnal
 * @param int $maxDisplayCount Jumlah maksimal staff yang ditampilkan
 * @return string Hash untuk deteksi perubahan
 */
function getStaffDataHash($journalId, $maxDisplayCount) {
    $roleJournalManager = 16;
    $roleEditor = 256;
    
    $roleDao = &DAORegistry::getDAO('RoleDAO');
    $userDao = &DAORegistry::getDAO('UserDAO');
    
    $hashData = array();
    
    // Get managers untuk hash
    $managersObj = $roleDao->getUsersByRoleId($roleJournalManager, $journalId);
    $managerCount = 0;
    $managerIds = array();
    
    while ($manager = $managersObj->next()) {
        if ($managerCount >= $maxDisplayCount) break;
        
        $userId = $manager->getId();
        // PERBAIKAN: Gunakan getById() atau retrieve()
        $user = $userDao->getById($userId);
        if (!$user) continue;
        
        $managerIds[] = $userId;
        
        // Data yang bisa berubah untuk hash
        $hashData[] = array(
            'id' => $userId,
            'role' => 'manager',
            'name' => $user->getFullName(),
            'email' => $user->getEmail(),
            'affiliation' => $user->getLocalizedAffiliation(),
            'country' => $user->getCountry()
        );
        
        $managerCount++;
    }
    
    // Get editors untuk hash
    $editorsObj = $roleDao->getUsersByRoleId($roleEditor, $journalId);
    $editorCount = 0;
    
    while ($editor = $editorsObj->next()) {
        if ($editorCount >= $maxDisplayCount) break;
        
        $userId = $editor->getId();
        // PERBAIKAN: Gunakan getById() atau retrieve()
        $user = $userDao->getById($userId);
        if (!$user) continue;
        
        // Skip jika user ini juga manager
        if (in_array($userId, $managerIds)) {
            continue;
        }
        
        // Data yang bisa berubah untuk hash
        $hashData[] = array(
            'id' => $userId,
            'role' => 'editor',
            'name' => $user->getFullName(),
            'email' => $user->getEmail(),
            'affiliation' => $user->getLocalizedAffiliation(),
            'country' => $user->getCountry()
        );
        
        $editorCount++;
    }
    
    // Tambahkan timestamp untuk memaksa refresh cache setiap 24 jam sebagai fallback
    $hashData['daily_refresh'] = date('Y-m-d');
    
    return md5(serialize($hashData));
}

/**
 * Fungsi untuk cek apakah data staff berubah
 * @param string $cacheFile Path ke file cache
 * @param string $currentHash Hash data saat ini
 * @return bool True jika data berubah atau cache tidak ada
 */
function isStaffDataChanged($cacheFile, $currentHash) {
    if (!file_exists($cacheFile)) {
        return true; // Cache tidak ada, perlu generate
    }
    
    $cachedData = loadFromCache($cacheFile);
    if ($cachedData === false) {
        return true; // Cache rusak, perlu generate
    }
    
    if (!isset($cachedData['data_hash'])) {
        return true; // Cache lama tanpa hash, perlu generate
    }
    
    return $cachedData['data_hash'] !== $currentHash;
}

/**
 * Fungsi untuk load data dari cache
 * @param string $cacheFile Path ke file cache
 * @return array|false Array data atau false jika gagal
 */
function loadFromCache($cacheFile) {
    if (!file_exists($cacheFile)) {
        return false;
    }
    
    $content = file_get_contents($cacheFile);
    if ($content === false) {
        return false;
    }
    
    $data = json_decode($content, true);
    return $data !== null ? $data : false;
}

/**
 * Fungsi untuk save data ke cache
 * @param string $cacheFile Path ke file cache
 * @param array $data Data yang akan disimpan
 * @return bool True jika berhasil
 */
function saveToCache($cacheFile, $data) {
    $dir = dirname($cacheFile);
    ensureCacheDir($dir);
    
    $content = json_encode($data, JSON_PRETTY_PRINT);
    return file_put_contents($cacheFile, $content) !== false;
}

// Generate hash untuk deteksi perubahan data staff
$currentDataHash = getStaffDataHash($journalId, $maxDisplayCount);

// Cek apakah data staff berubah
if ($cacheEnabled && !isStaffDataChanged($cacheFile, $currentDataHash)) {
    $cachedData = loadFromCache($cacheFile);
    if ($cachedData !== false) {
        // Load dari cache karena data tidak berubah
        $this->assign('journalManagers', $cachedData['managers']);
        $this->assign('journalEditors', $cachedData['editors']);
        return;
    }
}

// Jika cache tidak tersedia atau expired, generate data baru
$locale = $journal->getPrimaryLocale();
if (empty($locale)) {
    $locale = AppLocale::getLocale();
}

// Array untuk data staff
$managers = array();
$editors = array();
$managerUserIds = array(); // Untuk melacak user ID manager

// Mendapatkan staff untuk jurnal
$roleDao = &DAORegistry::getDAO('RoleDAO');
$userDao = &DAORegistry::getDAO('UserDAO');
$countryDao = &DAORegistry::getDAO('CountryDAO');

/**
 * Fungsi helper untuk mendapatkan setting user (prefix)
 * @param int $userId ID user
 * @param string $settingName Nama setting
 * @param string $locale Locale yang digunakan
 * @return string Nilai setting
 */
function getUserSetting($userId, $settingName, $locale) {
    $userSettingsDao = &DAORegistry::getDAO('UserSettingsDAO');
    return $userSettingsDao->getSetting($userId, $settingName, $locale);
}

/**
 * Fungsi untuk mengecek apakah gambar profil ada
 * @param int $userId ID user
 * @return string|false Format file jika ada, false jika tidak
 */
function profileImageExists($userId) {
    $baseDir = Config::getVar('files', 'public_files_dir') . '/site/';
    
    // Cek untuk format jpg, gif, dan png
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
 * Fungsi untuk mendapatkan URL gambar profil
 * @param int $userId ID user
 * @return string|null URL gambar atau null jika tidak ada
 */
function getProfileImageUrl($userId) {
    $format = profileImageExists($userId);
    
    if ($format) {
        $baseUrl = Request::getBaseUrl();
        return $baseUrl . '/public/site/profileImage-' . $userId . '.' . $format;
    }
    
    return null;
}

/**
 * Fungsi untuk memproses afiliasi (ambil hanya bagian pertama jika dipisahkan dengan \n)
 * @param string $affiliation Afiliasi asli
 * @return string Afiliasi yang telah diproses
 */
function processAffiliation($affiliation) {
    if (empty($affiliation)) return '';
    
    // Split berdasarkan newline dan ambil baris pertama saja
    $parts = explode("\n", $affiliation);
    return trim($parts[0]);
}

/**
 * Fungsi untuk cek dan generate Gravatar URL
 * @param string $email Email user
 * @return array Array dengan imageUrl dan hasProfileImage
 */
function getGravatarInfo($email) {
    if (empty($email)) {
        return array('imageUrl' => null, 'hasProfileImage' => false);
    }
    
    $gravatarUrl = "https://www.gravatar.com/avatar/" . 
                   md5(strtolower(trim($email))) . 
                   "?s=150&d=404";
    
    // Cek apakah gravatar tersedia dengan timeout yang pendek
    $context = stream_context_create(array(
        'http' => array(
            'timeout' => 2, // 2 detik timeout
            'method' => 'HEAD'
        )
    ));
    
    $headers = @get_headers($gravatarUrl, false, $context);
    if ($headers && strpos($headers[0], '200') !== false) {
        return array('imageUrl' => $gravatarUrl, 'hasProfileImage' => true);
    }
    
    return array('imageUrl' => null, 'hasProfileImage' => false);
}

// Mendapatkan daftar manager
$managersObj = $roleDao->getUsersByRoleId($roleJournalManager, $journalId);
$managerCount = 0;
while ($manager = $managersObj->next()) {
    if ($managerCount >= $maxDisplayCount) break;
    
    $userId = $manager->getId();
    $user = $userDao->getById($userId);
    if (!$user) continue;
    
    // Simpan ID user manager untuk pengecekan nanti
    $managerUserIds[] = $userId;
    
    // Mendapatkan prefix menggunakan UserSettingsDAO
    $prefix = getUserSetting($userId, 'prefix', $locale);
    if (empty($prefix)) {
        // Fallback ke en_US
        $prefix = getUserSetting($userId, 'prefix', 'en_US');
    }
    
    // Mendapatkan dan proses afiliasi
    $originalAffiliation = $user->getAffiliation($locale);
    $affiliation = processAffiliation($originalAffiliation);
    $affiliationWasProcessed = ($originalAffiliation != $affiliation && !empty($originalAffiliation));
    
    // Mendapatkan data country - hanya tampilkan jika afiliasi tidak diproses (tidak ada \n)
    $countryCode = $user->getCountry();
    $countryName = '';
    if (!$affiliationWasProcessed && !empty($countryCode)) {
        $countryName = $countryDao->getCountry($countryCode, $locale);
        if (empty($countryName)) {
            // Fallback ke en_US
            $countryName = $countryDao->getCountry($countryCode, 'en_US');
        }
    }
    
    // Mendapatkan email untuk Gravatar fallback
    $userEmail = $user->getEmail();
    
    // Cek profile image dan setup Gravatar fallback
    $hasProfileImage = profileImageExists($userId) ? true : false;
    $profileImageUrl = getProfileImageUrl($userId);
    
    // Fallback to Gravatar jika tidak ada foto profil
    if (!$hasProfileImage && !empty($userEmail)) {
        $gravatarInfo = getGravatarInfo($userEmail);
        $profileImageUrl = $gravatarInfo['imageUrl'];
        $hasProfileImage = $gravatarInfo['hasProfileImage'];
    }
    
    $managers[] = array(
        'userId' => $userId,
        'salutation' => $user->getSalutation(),
        'firstName' => $user->getFirstName(),
        'middleName' => $user->getMiddleName(),
        'lastName' => $user->getLastName(),
        'prefix' => $prefix,
        'fullName' => $user->getFullName(),
        'affiliation' => $affiliation,
        'country' => $countryName,
        'email' => $userEmail,
        'imageUrl' => $profileImageUrl,
        'hasProfileImage' => $hasProfileImage
    );
    
    $managerCount++;
}

// Mendapatkan daftar editor
$editorsObj = $roleDao->getUsersByRoleId($roleEditor, $journalId);
$editorCount = 0;
while ($editor = $editorsObj->next()) {
    if ($editorCount >= $maxDisplayCount) break;
    
    $userId = $editor->getId();
    $user = $userDao->getUser($userId);
    if (!$user) continue;
    
    // Skip jika user ini juga menjadi Journal Manager
    if (in_array($userId, $managerUserIds)) {
        continue;
    }
    
    // Mendapatkan prefix menggunakan UserSettingsDAO
    $prefix = getUserSetting($userId, 'prefix', $locale);
    if (empty($prefix)) {
        // Fallback ke en_US
        $prefix = getUserSetting($userId, 'prefix', 'en_US');
    }
    
    // Mendapatkan dan proses afiliasi
    $originalAffiliation = $user->getAffiliation($locale);
    $affiliation = processAffiliation($originalAffiliation);
    $affiliationWasProcessed = ($originalAffiliation != $affiliation && !empty($originalAffiliation));
    
    // Mendapatkan data country - hanya tampilkan jika afiliasi tidak diproses (tidak ada \n)
    $countryCode = $user->getCountry();
    $countryName = '';
    if (!$affiliationWasProcessed && !empty($countryCode)) {
        $countryName = $countryDao->getCountry($countryCode, $locale);
        if (empty($countryName)) {
            // Fallback ke en_US
            $countryName = $countryDao->getCountry($countryCode, 'en_US');
        }
    }
    
    // Mendapatkan email untuk Gravatar fallback
    $userEmail = $user->getEmail();
    
    // Cek profile image dan setup Gravatar fallback
    $hasProfileImage = profileImageExists($userId) ? true : false;
    $profileImageUrl = getProfileImageUrl($userId);
    
    // Fallback to Gravatar jika tidak ada foto profil
    if (!$hasProfileImage && !empty($userEmail)) {
        $gravatarInfo = getGravatarInfo($userEmail);
        $profileImageUrl = $gravatarInfo['imageUrl'];
        $hasProfileImage = $gravatarInfo['hasProfileImage'];
    }
    
    $editors[] = array(
        'userId' => $userId,
        'salutation' => $user->getSalutation(),
        'firstName' => $user->getFirstName(),
        'middleName' => $user->getMiddleName(),
        'lastName' => $user->getLastName(),
        'prefix' => $prefix,
        'fullName' => $user->getFullName(),
        'affiliation' => $affiliation,
        'country' => $countryName,
        'email' => $userEmail,
        'imageUrl' => $profileImageUrl,
        'hasProfileImage' => $hasProfileImage
    );
    
    $editorCount++;
}

// Simpan ke cache dengan hash untuk deteksi perubahan
if ($cacheEnabled) {
    $dataToCache = array(
        'managers' => $managers,
        'editors' => $editors,
        'generated_at' => time(),
        'journal_id' => $journalId,
        'max_display_count' => $maxDisplayCount,
        'data_hash' => $currentDataHash
    );
    saveToCache($cacheFile, $dataToCache);
}

// Menetapkan variabel yang dapat diakses di template
$this->assign('journalManagers', $managers);
$this->assign('journalEditors', $editors);
?>