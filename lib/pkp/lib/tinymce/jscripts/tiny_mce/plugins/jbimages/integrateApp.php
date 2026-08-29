<?php
declare(strict_types=1);

/**
 * @file tiny_mce/plugins/jbimages/integrateApp.php
 * 
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Lumera Team
 * Distributed under the GNU GPL v3.
 *
 * @class IntegrateApp
 * @ingroup plugins_generic_tinymce
 *
 * @brief Integrates Lumera applications with the jbimages image upload utility for TinyMCE.
 */

class IntegrateApp {
	
    /** @var string Path to the base application directory */
    protected $baseDir = '';

    /** @var string URL to the public uploads directory */
    protected $baseUrl = '';

    /** @var string|null Path to the user's image upload directory */
    protected $imageDir = null;

    /**
     * Constructor.
     * Bootstraps the application environment, resolves the base URL, 
     * and ensures the user's image upload directory exists.
     */
    public function __construct() {
        // Resolve base directory path
        $this->baseDir = $_SERVER['SCRIPT_FILENAME'] ?? '';
        for ($i = 0; $i < 10; $i++) {
            $this->baseDir = dirname($this->baseDir);
        }

        chdir($this->baseDir);
		// Dynamic public files from config to determine location
        $publicFilesDir = Config::getVar('files', 'public_files_dir');
		// Check if index.php exists in public/ directory (new structure)
        if (file_exists($this->baseDir . '/public/index.php')) {
            define('INDEX_FILE_LOCATION', $this->baseDir . '/public/index.php');
        } else {
            // Fallback to root index.php (legacy structure)
            define('INDEX_FILE_LOCATION', $this->baseDir . '/index.php');
        }
        require($this->baseDir . '/lib/pkp/includes/bootstrap.inc.php');

        $publicDir = Config::getVar('files', 'public_files_dir');
        $config = Config::getData();
        
        // Rank and match base URLs to find the most accurate one for the current request
        $baseUrls = [];
        foreach ($config['general'] as $k => $v) {
            if (strpos($k, 'base_url') === 0) {
                $ranking = strlen($v);
                $key = substr($k, 9, -1); // Extracts journal path from base_url[path]
                
                if ($key === 'index') $ranking = 0;
                elseif ($key === '') $ranking = -1;
                
                $baseUrls[$v] = sprintf('%08d', $ranking) . $key;
            }
        }
        
        arsort($baseUrls);
        $this->baseUrl = Config::getVar('general', 'base_url');
        
        $requestUri = ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '');
        foreach ($baseUrls as $k => $v) {
            if (stripos($requestUri, preg_replace('#^https?://#i', '', $k)) !== false) {
                $this->baseUrl = $k;
                break;
            }
        }

        if (!defined('SESSION_DISABLE_INIT')) {
            define('SESSION_DISABLE_INIT', 1);
        }

        // Register locale files
        $locale = LOCALE_DEFAULT;
        $localeFile = new LocaleFile($locale, $this->baseDir . "/lib/pkp/locale/$locale/installer.xml");
        Registry::get('localeFiles', true, [$locale => [$localeFile]]);

        // Initialize session and user context
        $sessionManager = SessionManager::getManager();
        $userSession = $sessionManager->getUserSession();
        $user = $userSession->getUser();

        if ($user) {
            $siteDir = $this->baseDir . '/' . $publicDir . '/site';
            $imagesDir = $siteDir . '/images';
            $userImageDir = $imagesDir . '/' . $user->getUsername();
            
            import('lib.pkp.classes.file.FileManager');
            $fileManager = new FileManager();

            // Ensure base site and images directories exist
            if (!file_exists($imagesDir)) {
                if (!file_exists($siteDir) || !is_writeable($siteDir)) {
                    die(__('installer.installFilesDirError'));
                }
                if (!$fileManager->mkdir($imagesDir)) {
                    die(__('installer.installFilesDirError'));
                }
            }

            // Ensure user-specific image directory exists
            if (Validation::isLoggedIn() && !file_exists($userImageDir)) {
                if (!is_writeable($imagesDir)) {
                    die(__('installer.installFilesDirError'));
                }
                if (!$fileManager->mkdir($userImageDir)) {
                    die(__('installer.installFilesDirError'));
                }
            }
            
            if (Validation::isLoggedIn()) {
                $this->imageDir = $publicDir . '/site/images/' . $user->getUsername();
            }
        }

        // Restore original working directory
        chdir(dirname($_SERVER['SCRIPT_FILENAME']));
    }

    /**
     * Get the absolute path to the user's image upload directory.
     * @return string
     */
    public function getAppImageUploadPath() {
        // [LUMERA] Explicit null check prevents PHP 8.1+ string concatenation warnings
        if ($this->baseDir !== '' && $this->imageDir !== null) {
            return $this->baseDir . '/' . $this->imageDir;
        }

        die(__('installer.installFilesDirError'));
    }

    /**
     * Get the URL (minus domain name) for the user's image upload directory.
     * @return string
     */
    public function getAppImageUrl() {
        // [LUMERA] Explicit null check prevents PHP 8.1+ string concatenation warnings
        if ($this->baseUrl !== '' && $this->imageDir !== null) {
            $url = $this->baseUrl . '/' . $this->imageDir;
            $urlParts = parse_url($url);
            return $urlParts['path'] ?? '';
        }

        die(__('installer.installFilesDirError'));
    }
	
}
?>