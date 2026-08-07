<?php
declare(strict_types=1);

/**
 * @file classes/site/VersionCheck.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class VersionCheck
 * @ingroup site
 * @see Version
 *
 * @brief Provides methods to check for the latest version of App.
 */

define('VERSION_CODE_PATH', 'dbscripts/xml/version.xml');

import('lib.pkp.classes.db.XMLDAO');
import('lib.pkp.classes.site.Version');

class VersionCheck {

    /**
     * Return information about the latest available version.
     * @return array|false
     */
    public static function getLatestVersion() {
        $application = Application::getApplication();

        $includeId = Config::getVar('general', 'installed') &&
            !defined('RUNNING_UPGRADE') &&
            Config::getVar('general', 'enable_beacon', true);

        $uniqueSiteId = null;
        if ($includeId) {
            /** @var PluginSettingsDAO $pluginSettingsDao */
            $pluginSettingsDao = DAORegistry::getDAO('PluginSettingsDAO');
            if ($pluginSettingsDao) {
                $uniqueSiteId = $pluginSettingsDao->getSetting(CONTEXT_SITE, 'UsageEventPlugin', 'uniqueSiteId');
            }
        }

        $request = $application->getRequest();
        $router = $request->getRouter();
        $oaiUrl = $router ? $router->url($request, 'index', 'oai') : '';

        $url = $application->getVersionDescriptorUrl();
        if ($includeId) {
            $url .= '?id=' . urlencode((string) $uniqueSiteId) . '&oai=' . urlencode($oaiUrl);
        }

        return VersionCheck::parseVersionXML($url);
    }

    /**
     * Return the currently installed database version.
     * @return Version|null
     */
    public static function getCurrentDBVersion(): ?Version {
        /** @var VersionDAO $versionDao */
        $versionDao = DAORegistry::getDAO('VersionDAO');
        if (!$versionDao) return null;
        
        return $versionDao->getCurrentVersion();
    }

    /**
     * Return the current code version.
     * @return Version|false
     */
    public static function getCurrentCodeVersion() {
        $versionInfo = VersionCheck::parseVersionXML(VERSION_CODE_PATH);
        if (is_array($versionInfo) && isset($versionInfo['version'])) {
            return $versionInfo['version'];
        }
        return false;
    }

    /**
     * Parse information from a version XML file.
     * @param string $url
     * @return array|false
     */
    public static function parseVersionXML($url) {
        $xmlDao = new XMLDAO();
        $data = $xmlDao->parseStruct($url, []);
        
        if (!$data) {
            return false;
        }

        $versionInfo = [
            'application' => $data['application'][0]['value'] ?? null,
            'type'        => $data['type'][0]['value'] ?? null,
            'release'     => $data['release'][0]['value'] ?? null,
            'tag'         => $data['tag'][0]['value'] ?? null,
            'date'        => $data['date'][0]['value'] ?? null,
            'info'        => $data['info'][0]['value'] ?? null,
            'package'     => $data['package'][0]['value'] ?? null,
            'class'       => (string) ($data['class'][0]['value'] ?? ''),
            'lazy-load'   => (int) ($data['lazy-load'][0]['value'] ?? 0),
            'sitewide'    => (int) ($data['sitewide'][0]['value'] ?? 0)
        ];

        if (isset($data['patch'])) {
            $versionInfo['patch'] = [];
            foreach ($data['patch'] as $patch) {
                if (isset($patch['attributes']['from']) && isset($patch['value'])) {
                    $versionInfo['patch'][$patch['attributes']['from']] = $patch['value'];
                }
            }
        }

        if (isset($versionInfo['release']) && isset($versionInfo['application'])) {
            $version = Version::fromString(
                $versionInfo['release'],
                $versionInfo['type'] ?? null,
                $versionInfo['application'],
                $versionInfo['class'],
                $versionInfo['lazy-load'],
                $versionInfo['sitewide']
            );
            $versionInfo['version'] = $version;
        }

        return $versionInfo;
    }

    /**
     * Find the applicable patch for the current code version (if available).
     * @param array|false $versionInfo as returned by parseVersionXML()
     * @param Version|null $codeVersion as returned by getCurrentCodeVersion()
     * @return string|null
     */
    public static function getPatch($versionInfo, $codeVersion = null): ?string {
        if (!isset($codeVersion)) {
            $codeVersion = VersionCheck::getCurrentCodeVersion();
        }
        
        if (!$codeVersion instanceof Version || !is_array($versionInfo) || !isset($versionInfo['patch'])) {
            return null;
        }

        $versionString = $codeVersion->getVersionString();
        return $versionInfo['patch'][$versionString] ?? null;
    }

    /**
     * Checks whether the given version file exists and whether it
     * contains valid data. Returns a Version object if everything
     * is ok, otherwise null. If $returnErrorMsg is true, returns the
     * error message.
     * @param string $versionFile
     * @param bool $returnErrorMsg
     * @return Version|string|null
     */
    public static function getValidPluginVersionInfo($versionFile, $returnErrorMsg = false) {
        $errorMsg = null;
        $versionInfo = [];
        $productType = [];
        
        $fileManager = new FileManager();
        if ($fileManager->fileExists($versionFile)) {
            $parsedInfo = VersionCheck::parseVersionXML($versionFile);
            if (is_array($parsedInfo)) {
                $versionInfo = $parsedInfo;
            } else {
                $errorMsg = 'manager.plugins.versionFileInvalid';
            }
        } else {
            $errorMsg = 'manager.plugins.versionFileNotFound';
        }

        // Validate plugin name and type to avoid abuse
        if ($errorMsg === null) {
            $typeString = $versionInfo['type'] ?? '';
            $productType = explode('.', (string) $typeString);
            
            if (count($productType) !== 2 || $productType[0] !== 'plugins') {
                $errorMsg = 'manager.plugins.versionFileInvalid';
            }
        }

        $pluginVersion = null;
        if ($errorMsg === null) {
            $pluginVersion = $versionInfo['version'] ?? null;
            
            if (!$pluginVersion instanceof Version) {
                $errorMsg = 'manager.plugins.versionFileInvalid';
            } else {
                $namesToValidate = [$pluginVersion->getProduct(), $productType[1] ?? ''];
                foreach ($namesToValidate as $nameToValidate) {
                    if (!PKPString::regexp_match('/[a-z][a-zA-Z0-9]+/', (string) $nameToValidate)) {
                        $errorMsg = 'manager.plugins.versionFileInvalid';
                        break;
                    }
                }
            }
        }

        if ($errorMsg !== null) {
            if ($returnErrorMsg) {
                return $errorMsg;
            } else {
                $request = Application::get()->getRequest();
                $templateMgr = TemplateManager::getManager($request);
                $templateMgr->assign('message', $errorMsg);
                return null;
            }
        } else {
            return $pluginVersion;
        }
    }

    /**
     * Checks the application's version against the latest version 
     * on the PKP servers.
     * @return string|false Returns the latest version string or false if no newer version
     */
    public static function checkIfNewVersionExists() {
        $versionInfo = VersionCheck::getLatestVersion();
        
        if (!is_array($versionInfo) || !isset($versionInfo['release'])) {
            return false;
        }
        
        $latestVersion = $versionInfo['release'];

        $currentVersion = VersionCheck::getCurrentDBVersion();
        if ($currentVersion instanceof Version && $currentVersion->compare($latestVersion) < 0) {
            return $latestVersion;
        }
        
        return false;
    }

    /**
     * Mendapatkan status versi dengan fallback backend yang robust.
     * Metode ini menjamin template selalu menerima array yang terstruktur, 
     * terlepas dari apakah pengecekan remote berhasil atau gagal.
     * @return array{
     *   status: 'up_to_date'|'update_available'|'check_failed',
     *   current_version: string,
     *   latest_version: ?string,
     *   message: string,
     *   info_url: ?string,
     *   package_url: ?string
     * }
     */
    public static function getVersionStatus(): array {
        // 1. Ambil versi saat ini dari DB
        $currentVersion = self::getCurrentDBVersion();
        $currentVersionString = $currentVersion instanceof Version ? $currentVersion->getVersionString() : '0.0.0.0';

        // 2. Ambil versi terbaru dari remote
        $latestVersionInfo = self::getLatestVersion();

        // 3. FALLBACK 1: Jika data remote gagal total
        if (!is_array($latestVersionInfo) || !isset($latestVersionInfo['version'])) {
            return [
                'status'          => 'check_failed',
                'current_version' => $currentVersionString,
                'latest_version'  => null,
                'message'         => 'Tidak dapat mengambil informasi versi terbaru. Pastikan file XML valid dan dapat diakses.',
                'info_url'        => null,
                'package_url'     => null
            ];
        }

        /** @var Version $latestVersion */
        $latestVersion = $latestVersionInfo['version'];
        $latestVersionString = $latestVersion->getVersionString();

        // 4. Bandingkan versi (method compare yang sudah aman dari null)
        $compareResult = $currentVersion instanceof Version ? $currentVersion->compare($latestVersion) : -1;

        // 5. FALLBACK: Tentukan status berdasarkan hasil perbandingan
        if ($compareResult >= 0) {
            return [
                'status'          => 'up_to_date',
                'current_version' => $currentVersionString,
                'latest_version'  => $latestVersionString,
                'message'         => 'Sistem Anda sudah menggunakan versi terbaru (' . $currentVersionString . ').',
                'info_url'        => $latestVersionInfo['info'] ?? null,
                'package_url'     => null
            ];
        } else {
            return [
                'status'          => 'update_available',
                'current_version' => $currentVersionString,
                'latest_version'  => $latestVersionString,
                'message'         => 'Pembaruan tersedia! Versi terbaru: ' . $latestVersionString . '.',
                'info_url'        => $latestVersionInfo['info'] ?? null,
                'package_url'     => $latestVersionInfo['package'] ?? null
            ];
        }
    }
    
}
?>