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
     * [SHIM] Legacy Constructor.
     */
    public function Site() {
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
     * Return associative array of all locales supported by the site.
     * @return array
     */
    public function getSupportedLocaleNames() {
        $supportedLocales = Registry::get('siteSupportedLocales', true, null);

        if ($supportedLocales === null) {
            $supportedLocales = [];
            $localeNames = AppLocale::getAllLocales();
            $locales = $this->getSupportedLocales();

            if (is_array($locales) && is_array($localeNames)) {
                foreach ($locales as $localeKey) {
                    if (isset($localeNames[$localeKey])) {
                        $supportedLocales[$localeKey] = $localeNames[$localeKey];
                    }
                }
            }

            asort($supportedLocales);
            Registry::set('siteSupportedLocales', $supportedLocales);
        }

        return $supportedLocales;
    }

    /**
     * Get site title.
     * @param string|null $locale
     * @return mixed
     */
    public function getTitle($locale = null) {
        return $this->getSetting('title', $locale);
    }

    /**
     * Get localized site title.
     * @return mixed
     */
    public function getLocalizedTitle() {
        return $this->getLocalizedSetting('title');
    }

    /**
     * [DEPRECATED] Legacy Get Site Title Shim.
     * @return mixed
     */
    public function getSiteTitle() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->getLocalizedTitle();
    }

    /**
     * Get localized site page title (if applicable).
     * @return mixed
     */
    public function getLocalizedPageHeaderTitle() {
        $typeArray = $this->getSetting('pageHeaderTitleType');
        $imageArray = $this->getSetting('pageHeaderTitleImage');
        $titleArray = $this->getSetting('title');

        foreach ([AppLocale::getLocale(), AppLocale::getPrimaryLocale()] as $locale) {
            if (is_array($typeArray) && isset($typeArray[$locale]) && $typeArray[$locale]) {
                if (is_array($imageArray) && isset($imageArray[$locale])) {
                    return $imageArray[$locale];
                }
            }
            if (is_array($titleArray) && isset($titleArray[$locale]) && !empty($titleArray[$locale])) {
                return $titleArray[$locale];
            }
        }
        return null;
    }

    /**
     * [DEPRECATED] Legacy Get Site Page Header Title Shim.
     * @return mixed
     */
    public function getSitePageHeaderTitle() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->getLocalizedPageHeaderTitle();
    }

    /**
     * Get localized site logo type.
     * @return mixed
     */
    public function getLocalizedPageHeaderTitleType() {
        return $this->getLocalizedData('pageHeaderTitleType');
    }

    /**
     * [DEPRECATED] Legacy Get Site Page Header Title Type Shim.
     * @return mixed
     */
    public function getSitePageHeaderTitleType() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->getLocalizedPageHeaderTitleType();
    }

    /**
     * Get original site stylesheet filename.
     * @return mixed
     */
    public function getOriginalStyleFilename() {
        return $this->getData('originalStyleFilename');
    }

    /**
     * Set original site stylesheet filename.
     * @param mixed $originalStyleFilename
     * @return void
     */
    public function setOriginalStyleFilename($originalStyleFilename) {
        $this->setData('originalStyleFilename', $originalStyleFilename);
    }

    /**
     * Get localized site intro.
     * @return mixed
     */
    public function getLocalizedIntro() {
        return $this->getLocalizedSetting('intro');
    }

    /**
     * [DEPRECATED] Legacy Get Site Intro Shim.
     * @return mixed
     */
    public function getSiteIntro() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->getLocalizedIntro();
    }

    /**
     * Get redirect.
     * @return int
     */
    public function getRedirect() {
        $redirect = $this->getData('redirect');
        return $redirect !== null ? (int) $redirect : 0;
    }

    /**
     * Set redirect.
     * @param mixed $redirect
     * @return void
     */
    public function setRedirect($redirect) {
        $this->setData('redirect', $redirect !== null ? (int) $redirect : 0);
    }

    /**
     * Get localized site about statement.
     * @return mixed
     */
    public function getLocalizedAbout() {
        return $this->getLocalizedSetting('about');
    }

    /**
     * [DEPRECATED] Legacy Get Site About Shim.
     * @return mixed
     */
    public function getSiteAbout() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->getLocalizedAbout();
    }

    /**
     * Get localized site contact name.
     * @return mixed
     */
    public function getLocalizedContactName() {
        return $this->getLocalizedSetting('contactName');
    }

    /**
     * [DEPRECATED] Legacy Get Site Contact Name Shim.
     * @return mixed
     */
    public function getSiteContactName() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->getLocalizedContactName();
    }

    /**
     * Get localized site contact email.
     * @return mixed
     */
    public function getLocalizedContactEmail() {
        return $this->getLocalizedSetting('contactEmail');
    }

    /**
     * [DEPRECATED] Legacy Get Site Contact Email Shim.
     * @return mixed
     */
    public function getSiteContactEmail() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->getLocalizedContactEmail();
    }

    /**
     * Get minimum password length.
     * @return int
     */
    public function getMinPasswordLength() {
        $length = $this->getData('minPasswordLength');
        return $length !== null ? (int) $length : 0;
    }

    /**
     * Set minimum password length.
     * @param mixed $minPasswordLength
     * @return void
     */
    public function setMinPasswordLength($minPasswordLength) {
        $this->setData('minPasswordLength', $minPasswordLength !== null ? (int) $minPasswordLength : 0);
    }

    /**
     * Get primary locale.
     * @return mixed
     */
    public function getPrimaryLocale() {
        return $this->getData('primaryLocale');
    }

    /**
     * Set primary locale.
     * @param mixed $primaryLocale
     * @return void
     */
    public function setPrimaryLocale($primaryLocale) {
        $this->setData('primaryLocale', $primaryLocale);
    }

    /**
     * Get installed locales.
     * @return array
     */
    public function getInstalledLocales() {
        $locales = $this->getData('installedLocales');
        return is_array($locales) ? $locales : [];
    }

    /**
     * Set installed locales.
     * @param array $installedLocales
     * @return void
     */
    public function setInstalledLocales($installedLocales) {
        $this->setData('installedLocales', is_array($installedLocales) ? $installedLocales : []);
    }

    /**
     * Get array of all supported locales (for static text).
     * @return array
     */
    public function getSupportedLocales() {
        $locales = $this->getData('supportedLocales');
        return is_array($locales) ? $locales : [];
    }

    /**
     * Set array of all supported locales (for static text).
     * @param array $supportedLocales
     * @return void
     */
    public function setSupportedLocales($supportedLocales) {
        $this->setData('supportedLocales', is_array($supportedLocales) ? $supportedLocales : []);
    }

    /**
     * Get the local name under which the site-wide locale file is stored.
     * @return string
     */
    public function getSiteStyleFilename() {
        return 'lumerapublisher.css';
    }

    /**
     * Retrieve a site setting value.
     * @param string $name
     * @param string|null $locale
     * @return mixed
     */
    public function getSetting($name, $locale = null) {
        /** @var SiteSettingsDAO $siteSettingsDao */
        $siteSettingsDao = DAORegistry::getDAO('SiteSettingsDAO');
        return $siteSettingsDao->getSetting($name, $locale);
    }

    /**
     * Get a localized setting using the current locale.
     * @param string $name
     * @return mixed
     */
    public function getLocalizedSetting($name) {
        $returner = $this->getSetting($name, AppLocale::getLocale());
        if ($returner === null) {
            $returner = $this->getSetting($name, AppLocale::getPrimaryLocale());
        }
        return $returner;
    }

    /**
     * Update a site setting value.
     * @param string $name
     * @param mixed $value
     * @param string|null $type
     * @param bool $isLocalized
     * @return void
     */
    public function updateSetting($name, $value, $type = null, $isLocalized = false) {
        /** @var SiteSettingsDAO $siteSettingsDao */
        $siteSettingsDao = DAORegistry::getDAO('SiteSettingsDAO');
        $siteSettingsDao->updateSetting($name, $value, $type, $isLocalized);
    }
    
}
?>