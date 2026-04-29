<?php
declare(strict_types=1);

/**
 * @defgroup site
 */

/**
 * @file classes/site/Site.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Site
 * @ingroup site
 * @see SiteDAO
 *
 * @brief Describes system-wide site properties.
 */

class Site extends DataObject {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Legacy Constructor Shim.
     */
    public function Site() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::Site(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Return associative array of all locales supported by the site.
     * These locales are used to provide a language toggle on the main site pages.
     * @return array
     */
    public function getSupportedLocaleNames() {
        $supportedLocales = Registry::get('siteSupportedLocales', true, null);

        if ($supportedLocales === null) {
            $supportedLocales = array();
            $localeNames = AppLocale::getAllLocales();

            $locales = $this->getSupportedLocales();
            foreach ($locales as $localeKey) {
                if (isset($localeNames[$localeKey])) {
                    $supportedLocales[$localeKey] = $localeNames[$localeKey];
                }
            }

            asort($supportedLocales);
            Registry::set('siteSupportedLocales', $supportedLocales);
        }

        return $supportedLocales;
    }

    //
    // Get/set methods
    //

    /**
     * Get site title.
     * @param $locale string Locale code to return, if desired.
     * @return string
     */
    public function getTitle($locale = null) {
        return $this->getSetting('title', $locale);
    }

    /**
     * Get localized site title.
     * @return string
     */
    public function getLocalizedTitle() {
        return $this->getLocalizedSetting('title');
    }

    public function getSiteTitle() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedTitle();
    }

    /**
     * Get "localized" site page title (if applicable).
     * @return string
     */
    public function getLocalizedPageHeaderTitle() {
        $typeArray = $this->getSetting('pageHeaderTitleType');
        $imageArray = $this->getSetting('pageHeaderTitleImage');
        $titleArray = $this->getSetting('title');

        $title = null;

        foreach (array(AppLocale::getLocale(), AppLocale::getPrimaryLocale()) as $locale) {
            if (isset($typeArray[$locale]) && $typeArray[$locale]) {
                if (isset($imageArray[$locale])) $title = $imageArray[$locale];
            }
            if (empty($title) && isset($titleArray[$locale])) $title = $titleArray[$locale];
            if (!empty($title)) return $title;
        }
        return null;
    }

    public function getSitePageHeaderTitle() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedPageHeaderTitle();
    }

    /**
     * Get localized site logo type.
     * @return boolean
     */
    public function getLocalizedPageHeaderTitleType() {
        return $this->getLocalizedData('pageHeaderTitleType');
    }

    public function getSitePageHeaderTitleType() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedPageHeaderTitleType();
    }

    /**
     * Get original site stylesheet filename.
     * @return string
     */
    public function getOriginalStyleFilename() {
        return $this->getData('originalStyleFilename');
    }

    /**
     * Set original site stylesheet filename.
     * @param $originalStyleFilename string
     */
    public function setOriginalStyleFilename($originalStyleFilename) {
        return $this->setData('originalStyleFilename', $originalStyleFilename);
    }

    /**
     * Get localized site intro.
     * @return string
     */
    public function getLocalizedIntro() {
        return $this->getLocalizedSetting('intro');
    }

    public function getSiteIntro() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedIntro();
    }

    /**
     * Get redirect
     * @return int
     */
    public function getRedirect() {
        return $this->getData('redirect');
    }

    /**
     * Set redirect
     * @param $redirect int
     */
    public function setRedirect($redirect) {
        return $this->setData('redirect', (int)$redirect);
    }

    /**
     * Get localized site about statement.
     * @return string
     */
    public function getLocalizedAbout() {
        return $this->getLocalizedSetting('about');
    }

    public function getSiteAbout() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedAbout();
    }

    /**
     * Get localized site contact name.
     * @return string
     */
    public function getLocalizedContactName() {
        return $this->getLocalizedSetting('contactName');
    }

    public function getSiteContactName() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedContactName();
    }

    /**
     * Get localized site contact email.
     * @return string
     */
    public function getLocalizedContactEmail() {
        return $this->getLocalizedSetting('contactEmail');
    }

    public function getSiteContactEmail() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedContactEmail();
    }

    /**
     * Get minimum password length.
     * @return int
     */
    public function getMinPasswordLength() {
        return $this->getData('minPasswordLength');
    }

    /**
     * Set minimum password length.
     * @param $minPasswordLength int
     */
    public function setMinPasswordLength($minPasswordLength) {
        return $this->setData('minPasswordLength', $minPasswordLength);
    }

    /**
     * Get primary locale.
     * @return string
     */
    public function getPrimaryLocale() {
        return $this->getData('primaryLocale');
    }

    /**
     * Set primary locale.
     * @param $primaryLocale string
     */
    public function setPrimaryLocale($primaryLocale) {
        return $this->setData('primaryLocale', $primaryLocale);
    }

    /**
     * Get installed locales.
     * @return array
     */
    public function getInstalledLocales() {
        $locales = $this->getData('installedLocales');
        return isset($locales) ? $locales : array();
    }

    /**
     * Set installed locales.
     * @param $installedLocales array
     */
    public function setInstalledLocales($installedLocales) {
        return $this->setData('installedLocales', $installedLocales);
    }

    /**
     * Get array of all supported locales (for static text).
     * @return array
     */
    public function getSupportedLocales() {
        $locales = $this->getData('supportedLocales');
        return isset($locales) ? $locales : array();
    }

    /**
     * Set array of all supported locales (for static text).
     * @param $supportedLocales array
     */
    public function setSupportedLocales($supportedLocales) {
        return $this->setData('supportedLocales', $supportedLocales);
    }

    /**
     * Get the local name under which the site-wide locale file is stored.
     * @return string
     */
    public function getSiteStyleFilename() {
        return 'wizdamstyle.css';
    }

    /**
     * Retrieve a site setting value.
     * @param $name string
     * @param $locale string
     * @return mixed
     */
    public function getSetting($name, $locale = null) {
        $siteSettingsDao = DAORegistry::getDAO('SiteSettingsDAO');
        return $siteSettingsDao->getSetting($name, $locale);
    }

    /**
     * Get a localized setting using the current locale.
     * @param $name string Setting name
     * @return mixed
     */
    public function getLocalizedSetting($name) {
        $returner = $this->getSetting($name, AppLocale::getLocale());
        if ($returner === null) {
            unset($returner);
            $returner = $this->getSetting($name, AppLocale::getPrimaryLocale());
        }
        return $returner;
    }

    /**
     * Update a site setting value.
     * @param $name string
     * @param $value mixed
     * @param $type string optional
     * @param $isLocalized boolean optional
     */
    public function updateSetting($name, $value, $type = null, $isLocalized = false) {
        $siteSettingsDao = DAORegistry::getDAO('SiteSettingsDAO');
        return $siteSettingsDao->updateSetting($name, $value, $type, $isLocalized);
    }
}

?>