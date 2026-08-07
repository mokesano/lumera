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
     * Compare this version with another version.
     * Returns:
     * < 0 if this version is lower
     * 0 if they are equal
     * > 0 if this version is higher
     * @param string $version string/Version the version to compare against
     * @return int
     */
    /**
     * Compare this version to another version.
     * @param mixed $version Version object or version string
     * @return int -1 if older, 0 if equal, 1 if newer
     */
    public function compare($version): int {
        if ($version instanceof Version) {
            $versionString = $version->getVersionString();
        } elseif (is_string($version)) {
            $versionString = $version;
        } else {
            $versionString = '0.0.0.0'; 
        }

        return version_compare($this->getVersionString(), (string) $versionString);
    }

    /**
     * Static method to return a new version from a version string of the form "W.X.Y.Z".
     * @param string $versionString string
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
     * @param int $major int
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
     * @param int $minor int
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
     * @param int $revision int
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
     * @param int $build int
     */
    public function setBuild($build) {
        return $this->setData('build', $build);
    }

    /**
     * Get date installed.
     * @return string|null
     */
    public function getDateInstalled() {
        return $this->getData('dateInstalled');
    }

    /**
     * Set date installed.
     * @param string $dateInstalled date
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
     * @param int $current int
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
     * @param string $productType string
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
     * @param string $product string
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
     * @param string $productClassName string
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
     * @param bool $lazyLoad boolean
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
     * @param bool $sitewide boolean
     */
    public function setSitewide($sitewide) {
        return $this->setData('sitewide', $sitewide);
    }

    /**
     * Return complete version string.
     * @return string
     */
    /**
     * Get the version string.
     * @return string
     */
    public function getVersionString(): string {
        return sprintf(
            '%d.%d.%d.%d', 
            (int) $this->getMajor(), 
            (int) $this->getMinor(), 
            (int) $this->getRevision(), 
            (int) $this->getBuild()
        );
    }
    
}
?>