<?php
declare(strict_types=1);

/**
 * @file classes/site/Version.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Version
 * @ingroup site
 * @see VersionDAO
 *
 * @brief Describes system version history.
 * [WIZDAM EDITION] PHP 7.4+ Compatible
 */

class Version extends DataObject {
    
    /**
     * Constructor.
     */
    public function __construct($major = 0, $minor = 0, $revision = 0, $build = 0, $dateInstalled = null, $current = 1, $productType = null, $product = null, $productClassName = '', $lazyLoad = 0, $sitewide = 1) {
        parent::__construct();

        // Initialize object
        $this->setMajor($major);
        $this->setMinor($minor);
        $this->setRevision($revision);
        $this->setBuild($build);
        $this->setDateInstalled($dateInstalled);
        $this->setCurrent($current);
        $this->setProductType($productType);
        $this->setProduct($product);
        $this->setProductClassName($productClassName);
        $this->setLazyLoad($lazyLoad);
        $this->setSitewide($sitewide);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Version($major = 0, $minor = 0, $revision = 0, $build = 0, $dateInstalled = null, $current = 1, $productType = null, $product = null, $productClassName = '', $lazyLoad = 0, $sitewide = 1) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::Version(). Please refactor to use parent::__construct().", 
            E_USER_DEPRECATED
        );
        self::__construct($major, $minor, $revision, $build, $dateInstalled, $current, $productType, $product, $productClassName, $lazyLoad, $sitewide);
    }

    /**
     * Compare this version with another version.
     * Returns:
     * < 0 if this version is lower
     * 0 if they are equal
     * > 0 if this version is higher
     * @param $version string/Version the version to compare against
     * @return int
     */
    public function compare($version) {
        if (is_object($version)) {
            return $this->compare($version->getVersionString());
        }
        return version_compare($this->getVersionString(), $version);
    }

    /**
     * Static method to return a new version from a version string of the form "W.X.Y.Z".
     * @param $versionString string
     * @param $productType string
     * @param $product string
     * @param $productClass string
     * @param $lazyLoad integer
     * @param $sitewide integer
     * @return Version
     */
    public static function fromString($versionString, $productType = null, $product = null, $productClass = '', $lazyLoad = 0, $sitewide = 1) {
        $versionArray = explode('.', $versionString);

        if(!$product && !$productType) {
            $application = PKPApplication::getApplication();
            $product = $application->getName();
            $productType = 'core';
        }

        $version = new Version(
            (isset($versionArray[0]) ? (int) $versionArray[0] : 0),
            (isset($versionArray[1]) ? (int) $versionArray[1] : 0),
            (isset($versionArray[2]) ? (int) $versionArray[2] : 0),
            (isset($versionArray[3]) ? (int) $versionArray[3] : 0),
            Core::getCurrentDate(),
            1,
            $productType,
            $product,
            $productClass,
            $lazyLoad,
            $sitewide
        );

        return $version;
    }

    //
    // Get/set methods
    //

    /**
     * Get major version.
     * @return int
     */
    public function getMajor() {
        return $this->getData('major');
    }

    /**
     * Set major version.
     * @param $major int
     */
    public function setMajor($major) {
        return $this->setData('major', $major);
    }

    /**
     * Get minor version.
     * @return int
     */
    public function getMinor() {
        return $this->getData('minor');
    }

    /**
     * Set minor version.
     * @param $minor int
     */
    public function setMinor($minor) {
        return $this->setData('minor', $minor);
    }

    /**
     * Get revision version.
     * @return int
     */
    public function getRevision() {
        return $this->getData('revision');
    }

    /**
     * Set revision version.
     * @param $revision int
     */
    public function setRevision($revision) {
        return $this->setData('revision', $revision);
    }

    /**
     * Get build version.
     * @return int
     */
    public function getBuild() {
        return $this->getData('build');
    }

    /**
     * Set build version.
     * @param $build int
     */
    public function setBuild($build) {
        return $this->setData('build', $build);
    }

    /**
     * Get date installed.
     * @return date
     */
    public function getDateInstalled() {
        return $this->getData('dateInstalled');
    }

    /**
     * Set date installed.
     * @param $dateInstalled date
     */
    public function setDateInstalled($dateInstalled) {
        return $this->setData('dateInstalled', $dateInstalled);
    }

    /**
     * Check if current version.
     * @return int
     */
    public function getCurrent() {
        return $this->getData('current');
    }

    /**
     * Set if current version.
     * @param $current int
     */
    public function setCurrent($current) {
        return $this->setData('current', $current);
    }

    /**
     * Get product type.
     * @return string
     */
    public function getProductType() {
        return $this->getData('productType');
    }

    /**
     * Set product type.
     * @param $productType string
     */
    public function setProductType($productType) {
        return $this->setData('productType', $productType);
    }

    /**
     * Get product name.
     * @return string
     */
    public function getProduct() {
        return $this->getData('product');
    }

    /**
     * Set product name.
     * @param $product string
     */
    public function setProduct($product) {
        return $this->setData('product', $product);
    }

    /**
     * Get the product's class name
     * @return string
     */
    public function getProductClassName() {
        return $this->getData('productClassName');
    }

    /**
     * Set the product's class name
     * @param $productClassName string
     */
    public function setProductClassName($productClassName) {
        $this->setData('productClassName', $productClassName);
    }

    /**
     * Get the lazy load flag for this product
     * @return boolean
     */
    public function getLazyLoad() {
        return $this->getData('lazyLoad');
    }

    /**
     * Set the lazy load flag for this product
     * @param $lazyLoad boolean
     */
    public function setLazyLoad($lazyLoad) {
        return $this->setData('lazyLoad', $lazyLoad);
    }

    /**
     * Get the sitewide flag for this product
     * @return boolean
     */
    public function getSitewide() {
        return $this->getData('sitewide');
    }

    /**
     * Set the sitewide flag for this product
     * @param $sitewide boolean
     */
    public function setSitewide($sitewide) {
        return $this->setData('sitewide', $sitewide);
    }

    /**
     * Return complete version string.
     * @return string
     */
    public function getVersionString() {
        return sprintf('%d.%d.%d.%d', $this->getMajor(), $this->getMinor(), $this->getRevision(), $this->getBuild());
    }
}

?>