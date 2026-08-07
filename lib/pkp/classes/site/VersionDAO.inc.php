<?php
declare(strict_types=1);

/**
 * @file classes/site/VersionDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class VersionDAO
 * @ingroup site
 * @see Version
 *
 * @brief Operations for retrieving and modifying Version objects.
 */

import('lib.pkp.classes.site.Version');

class VersionDAO extends DAO {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function VersionDAO() {
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
     * Retrieve the current version.
     * @param string|null $productType
     * @param string|null $product
     * @param bool $isPlugin
     * @return Version|null
     */
    public function getCurrentVersion(?string $productType = null, ?string $product = null, bool $isPlugin = false): ?Version {
        if (!$productType || !$product) {
            $application = PKPApplication::getApplication();
            $productType = 'core';
            $product = $application->getName();
        }

        $returner = null;

        // Legacy fallback: if not a plugin, check if there's exactly ONE current version in the entire table
        if (!$isPlugin) {
            $result = $this->retrieve('SELECT * FROM versions WHERE current = 1');
            if (!$result->EOF) {
                $oldVersion = $this->_returnVersionFromRow($result->GetRowAssoc(false));
                $result->MoveNext();
                
                // Pastikan hanya ada 1 baris (RecordCount == 1 di kode asli)
                if ($result->EOF && $oldVersion) {
                    $returner = $oldVersion;
                }
            }
            $result->Close();
        }

        if (!$returner) {
            $result = $this->retrieve(
                'SELECT * FROM versions WHERE current = 1 AND product_type = ? AND product = ?',
                [$productType, $product]
            );
            
            if (!$result->EOF) {
                $returner = $this->_returnVersionFromRow($result->GetRowAssoc(false));
                $result->MoveNext();
                
                if (!$result->EOF) {
                    fatalError('More than one current version defined for the product type "' . $productType . '" and product "' . $product . '"!');
                }
            }
            $result->Close();
        }

        return $returner;
    }

    /**
     * Retrieve the complete version history.
     * @param string|null $productType
     * @param string|null $product
     * @return array Versions
     */
    public function getVersionHistory(?string $productType = null, ?string $product = null): array {
        if (!$productType || !$product) {
            $application = PKPApplication::getApplication();
            $productType = 'core';
            $product = $application->getName();
        }

        $result = $this->retrieve(
            'SELECT * FROM versions WHERE product_type = ? AND product = ? ORDER BY date_installed DESC',
            [$productType, $product]
        );

        $versions = [];
        while (!$result->EOF) {
            $versions[] = $this->_returnVersionFromRow($result->GetRowAssoc(false));
            $result->MoveNext();
        }

        $result->Close();
        return $versions;
    }

    /**
     * Internal function to return a Version object from a row.
     * @param array $row
     * @return Version
     */
    public function _returnVersionFromRow(array $row): Version {
        $version = new Version(
            (int) $row['major'],
            (int) $row['minor'],
            (int) $row['revision'],
            (int) $row['build'],
            $this->datetimeFromDB($row['date_installed']),
            (int) $row['current'],
            $row['product_type'] ?? null,
            $row['product'] ?? null,
            $row['product_class_name'] ?? '',
            (int) ($row['lazy_load'] ?? 0),
            (int) ($row['sitewide'] ?? 0)
        );

        HookRegistry::dispatch('VersionDAO::_returnVersionFromRow', [$version, &$row]);

        return $version;
    }

    /**
     * Insert a new version.
     * @param Version $version
     * @param bool $isPlugin
     * @return bool
     */
    public function insertVersion(Version $version, bool $isPlugin = false): bool {
        $isNewVersion = true;

        if ($version->getCurrent()) {
            $versionHistory = $this->getVersionHistory($version->getProductType(), $version->getProduct());
            $oldVersion = array_shift($versionHistory);
            
            if ($oldVersion) {
                $compareResult = $version->compare($oldVersion);
                
                if ($compareResult === 0) {
                    $isNewVersion = false;
                } elseif ($compareResult === 1) {
                    $this->update(
                        'UPDATE versions SET current = 0 WHERE current = 1 AND product = ?',
                        [$version->getProduct()]
                    );
                } else {
                    fatalError('You are trying to downgrade the product "' . $version->getProduct() . '" from version [' . $oldVersion->getVersionString() . '] to version [' . $version->getVersionString() . ']. Downgrades are not supported.');
                }
            }
        }

        if ($isNewVersion) {
            if ($version->getDateInstalled() === null) {
                $version->setDateInstalled(Core::getCurrentDate());
            }

            return $this->update(
                sprintf(
                    'INSERT INTO versions
                    (major, minor, revision, build, date_installed, current, product_type, product, product_class_name, lazy_load, sitewide)
                    VALUES (?, ?, ?, ?, %s, ?, ?, ?, ?, ?, ?)',
                    $this->datetimeToDB($version->getDateInstalled())
                ),
                [
                    (int) $version->getMajor(),
                    (int) $version->getMinor(),
                    (int) $version->getRevision(),
                    (int) $version->getBuild(),
                    (int) $version->getCurrent(),
                    $version->getProductType(),
                    $version->getProduct(),
                    $version->getProductClassName(),
                    (int) $version->getLazyLoad(),
                    (int) $version->getSitewide()
                ]
            );
        } else {
            return $this->update(
                'UPDATE versions SET current = ?, product_class_name = ?, lazy_load = ?, sitewide = ?
                    WHERE product_type = ? AND product = ? AND major = ? AND minor = ? AND revision = ? AND build = ?',
                [
                    (int) $version->getCurrent(),
                    $version->getProductClassName(),
                    (int) $version->getLazyLoad(),
                    (int) $version->getSitewide(),
                    $version->getProductType(),
                    $version->getProduct(),
                    (int) $version->getMajor(),
                    (int) $version->getMinor(),
                    (int) $version->getRevision(),
                    (int) $version->getBuild()
                ]
            );
        }
    }

    /**
     * Retrieve all currently enabled products.
     * @param array $context the application context (e.g., ['journal' => 1])
     * @return array
     */
    public function getCurrentProducts(array $context): array {
        $contextWhereClause = '';
        $params = [];

        if (!empty($context)) {
            $contextNames = array_keys($context);
            $params = array_values($context);

            foreach ($contextNames as $contextLevel => $contextName) {
                // Transform from camel case to ..._...
                $words = []; 
                PKPString::regexp_match_all('/[A-Z][a-z]*/', ucfirst($contextName), $words);
                $matchedWords = isset($words[0]) && is_array($words[0]) ? $words[0] : [];
                $contextNames[$contextLevel] = strtolower_codesafe(implode('_', $matchedWords));
            }
            
            $contextWhereClause = ' AND ( (' . implode('_id = ? AND ', $contextNames) . '_id = ?) OR v.sitewide = 1 )';
        }

        $result = $this->retrieve(
            'SELECT v.*
             FROM versions v 
             LEFT JOIN plugin_settings ps ON lower(v.product_class_name) = ps.plugin_name AND ps.setting_name = \'enabled\' ' . $contextWhereClause . '
             WHERE v.current = 1 AND (ps.setting_value = \'1\' OR v.lazy_load <> 1)',
            $params,
            false
        );

        $productArray = [];
        while (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $productArray[$row['product_type']][$row['product']] = $this->_returnVersionFromRow($row);
            $result->MoveNext();
        }
        $result->Close();

        return $productArray;
    }

    /**
     * Disable a product by setting its 'current' column to 0.
     * @param string $productType
     * @param string $product
     * @return bool
     */
    public function disableVersion(string $productType, string $product): bool {
        return $this->update(
            'UPDATE versions SET current = 0 WHERE current = 1 AND product_type = ? AND product = ?',
            [$productType, $product]
        );
    }
    
}
?>