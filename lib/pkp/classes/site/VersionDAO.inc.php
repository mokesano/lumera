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
 * [WIZDAM EDITION] PHP 7.4+ Compatible & Custom Logic Preserved
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
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::VersionDAO(). Please refactor to parent::__construct().", 
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Retrieve the current version.
     * @param $productType string
     * @param $product string
     * @param $isPlugin boolean
     * @return Version
     */
    public function getCurrentVersion($productType = null, $product = null, $isPlugin = false) {
        if(!$productType || !$product) {
            $application = PKPApplication::getApplication();
            $productType = 'core';
            $product = $application->getName();
        }

        $returner = null;
        if (!$isPlugin) {
            $result = $this->retrieve(
                'SELECT * FROM versions WHERE current = 1'
            );
            
            if ($result->RecordCount() == 1) {
                $oldVersion = $this->_returnVersionFromRow($result->GetRowAssoc(false));
                if (isset($oldVersion)) $returner = $oldVersion;
            }
            $result->Close();
        }

        if (!$returner) {
            $result = $this->retrieve(
                'SELECT * FROM versions WHERE current = 1 AND product_type = ? AND product = ?',
                array($productType, $product)
            );
            $versionCount = $result->RecordCount();
            if ($versionCount == 1) {
                $returner = $this->_returnVersionFromRow($result->GetRowAssoc(false));
            } elseif ($versionCount > 1) {
                fatalError('More than one current version defined for the product type "'.$productType.'" and product "'.$product.'"!');
            }
            $result->Close();
        }

        return $returner;
    }

    /**
     * Retrieve the complete version history.
 	 * @param $productType string
	 * @param $product string
	 * @return array Versions
	 */
    public function getVersionHistory($productType = null, $product = null) {
        $versions = array();

        if(!$productType || !$product) {
            $application = PKPApplication::getApplication();
            $productType = 'core';
            $product = $application->getName();
        }

        $result = $this->retrieve(
            'SELECT * FROM versions WHERE product_type = ? AND product = ? ORDER BY date_installed DESC',
            array($productType, $product)
        );

        while (!$result->EOF) {
            $versions[] = $this->_returnVersionFromRow($result->GetRowAssoc(false));
            $result->MoveNext();
        }

        $result->Close();
        return $versions;
    }

    /**
     * Internal function to return a Version object from a row.
	 * @param $row array
	 * @return Version
	 */
    public function _returnVersionFromRow($row) {
        // [IMPORTANT] Memanggil Constructor baru dengan parameter lengkap
        // Sesuai dengan Version.inc.php yang baru kita perbaiki
        $version = new Version(
            $row['major'],
            $row['minor'],
            $row['revision'],
            $row['build'],
            $this->datetimeFromDB($row['date_installed']),
            $row['current'],
            (isset($row['product_type']) ? $row['product_type'] : null),
            (isset($row['product']) ? $row['product'] : null),
            (isset($row['product_class_name']) ? $row['product_class_name'] : ''),
            (isset($row['lazy_load']) ? $row['lazy_load'] : 0),
            (isset($row['sitewide']) ? $row['sitewide'] : 0)
        );

        HookRegistry::dispatch('VersionDAO::_returnVersionFromRow', array($version, $row));

        return $version;
    }

    /**
     * Insert a new version.
	 * @param $version Version
	 * @param $isPlugin boolean
	 */
    public function insertVersion($version, $isPlugin = false) {
        $isNewVersion = true;

        if ($version->getCurrent()) {
            $versionHistory = $this->getVersionHistory($version->getProductType(), $version->getProduct());
            $oldVersion = array_shift($versionHistory);
            
            if ($oldVersion) {
                if ($version->compare($oldVersion) == 0) {
                    $isNewVersion = false;
                } elseif ($version->compare($oldVersion) == 1) {
                    $this->update('UPDATE versions SET current = 0 WHERE current = 1 AND product = ?', array($version->getProduct()));
                } else {
                    fatalError('You are trying to downgrade the product "'.$version->getProduct().'" from version ['.$oldVersion->getVersionString().'] to version ['.$version->getVersionString().']. Downgrades are not supported.');
                }
            }
        }

        if ($isNewVersion) {
            if ($version->getDateInstalled() == null) {
                $version->setDateInstalled(Core::getCurrentDate());
            }

            return $this->update(
                sprintf('INSERT INTO versions
                    (major, minor, revision, build, date_installed, current, product_type, product, product_class_name, lazy_load, sitewide)
                    VALUES
                    (?, ?, ?, ?, %s, ?, ?, ?, ?, ?, ?)',
                    $this->datetimeToDB($version->getDateInstalled())),
                array(
                    (int) $version->getMajor(),
                    (int) $version->getMinor(),
                    (int) $version->getRevision(),
                    (int) $version->getBuild(),
                    (int) $version->getCurrent(),
                    $version->getProductType(),
                    $version->getProduct(),
                    $version->getProductClassName(),
                    ($version->getLazyLoad()?1:0),
                    ($version->getSitewide()?1:0)
                )
            );
        } else {
            return $this->update(
                'UPDATE versions SET current = ?, product_class_name = ?, lazy_load = ?, sitewide = ?
                    WHERE product_type = ? AND product = ? AND major = ? AND minor = ? AND revision = ? AND build = ?',
                array(
                    (int) $version->getCurrent(),
                    $version->getProductClassName(),
                    ($version->getLazyLoad()?1:0),
                    ($version->getSitewide()?1:0),
                    $version->getProductType(),
                    $version->getProduct(),
                    (int) $version->getMajor(),
                    (int) $version->getMinor(),
                    (int) $version->getRevision(),
                    (int) $version->getBuild()
                )
            );
        }
    }

    /**
     * Retrieve all currently enabled products.
	 *
	 * @param $context array the application context, only
	 *  products enabled in that context will be returned.
	 * @return array
	 */
    public function getCurrentProducts($context) {
        if (count($context)) {
            $contextNames = array_keys($context);
            foreach ($contextNames as $contextLevel => $contextName) {
                // Transform from camel case to ..._...
                PKPString::regexp_match_all('/[A-Z][a-z]*/', ucfirst($contextName), $words);
                $contextNames[$contextLevel] = strtolower_codesafe(implode('_', $words[0]));
            }
            // [NOTE] Logic kompleks query ini dipertahankan
            $contextWhereClause = 'AND (('.implode('_id = ? AND ', $contextNames).'_id = ?) OR v.sitewide = 1)';
        } else {
            $contextWhereClause = '';
        }

        // [MODERNISASI] Fix parameter retrieve, $context harus array untuk binding, tapi query logic Anda menggunakan string concat manual untuk binding placeholder.
        // ADODB execute expects params array.
        // Jika $context adalah array data (bukan array kosong), maka aman.
        
        $result = $this->retrieve(
                'SELECT v.*
                 FROM versions v LEFT JOIN plugin_settings ps ON
                    lower(v.product_class_name) = ps.plugin_name
                    AND ps.setting_name = \'enabled\' '.$contextWhereClause.'
                 WHERE v.current = 1 AND (ps.setting_value = \'1\' OR v.lazy_load <> 1)', 
                 $context, // Array of values for ? placeholders
                 false // callHooks
        );

        $productArray = array();
        while(!$result->EOF) {
            $row = $result->getRowAssoc(false);
            // [MODERNISASI] Factory call without &
            $productArray[$row['product_type']][$row['product']] = $this->_returnVersionFromRow($row);
            $result->MoveNext();
        }
        $result->Close();

        return $productArray;
    }

	/**
	 * Disable a product by setting its 'current' column to 0
	 * @param $productType string
	 * @param $product string
	 */
    public function disableVersion($productType, $product) {
        $this->update(
            'UPDATE versions SET current = 0 WHERE current = 1 AND product_type = ? AND product = ?',
            array($productType, $product)
        );
    }
}

?>