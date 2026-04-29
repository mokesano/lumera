<?php
declare(strict_types=1);

/**
 * @defgroup journal
 */

/**
 * @file classes/journal/Journal.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Journal
 * @ingroup journal
 * @see JournalDAO
 *
 * @brief Describes basic journal properties.
 * [WIZDAM EDITION] PHP 7.4+ Compatible & Cleaned References
 */


define('PUBLISHING_MODE_OPEN', 0);
define('PUBLISHING_MODE_SUBSCRIPTION', 1);
define('PUBLISHING_MODE_NONE', 2);

class Journal extends DataObject {
    
    /**
     * Constructor.
     * [MODERNISASI] Native Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Journal() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::Journal(). Please refactor to parent::__construct().", 
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Get the base URL to the journal.
     * @return string
     */
    public function getUrl() {
        return Request::url($this->getPath());
    }

    /**
     * Return the primary locale of this journal.
     * @return string
     */
    public function getPrimaryLocale() {
        return $this->getData('primaryLocale');
    }

    /**
     * Set the primary locale of this journal.
     * @param $primaryLocale string
     */
    public function setPrimaryLocale($primaryLocale) {
        return $this->setData('primaryLocale', $primaryLocale);
    }

    /**
     * Return associative array of all locales supported by the journal.
     * [MODERNISASI] Removed & reference
     * @return array
     */
    public function getSupportedLocaleNames() {
        $supportedLocales = $this->getData('supportedLocales');

        if (!isset($supportedLocales)) {
            $supportedLocales = array();
            $localeNames = AppLocale::getAllLocales();

            $locales = $this->getSetting('supportedLocales');
            if (!isset($locales) || !is_array($locales)) {
                $locales = array();
            }

            foreach ($locales as $localeKey) {
                if (!isset($localeNames[$localeKey])) continue;
                $supportedLocales[$localeKey] = $localeNames[$localeKey];
            }
        }

        return $supportedLocales;
    }

    /**
     * Return associative array of all locales supported by forms of the journal.
     * [MODERNISASI] Removed & reference
     * @return array
     */
    public function getSupportedFormLocaleNames() {
        $supportedLocales = $this->getData('supportedFormLocales');

        if (!isset($supportedLocales)) {
            $supportedLocales = array();
            $localeNames = AppLocale::getAllLocales();

            $locales = $this->getSetting('supportedFormLocales');
            if (!isset($locales) || !is_array($locales)) {
                $locales = array();
            }

            foreach ($locales as $localeKey) {
                $supportedLocales[$localeKey] = $localeNames[$localeKey];
            }
        }

        return $supportedLocales;
    }

    /**
     * Return associative array of all locales supported for the submissions.
     * [MODERNISASI] Removed & reference
     * @return array
     */
    public function getSupportedSubmissionLocaleNames() {
        $supportedLocales = $this->getData('supportedSubmissionLocales');

        if (!isset($supportedLocales)) {
            $supportedLocales = array();
            $localeNames = AppLocale::getAllLocales();

            $locales = $this->getSetting('supportedSubmissionLocales');
            if (empty($locales)) $locales = array($this->getPrimaryLocale());

            foreach ($locales as $localeKey) {
                $supportedLocales[$localeKey] = $localeNames[$localeKey];
            }
        }

        return $supportedLocales;
    }

    /**
     * Get "localized" journal page title (if applicable).
     * @return string
     */
    public function getLocalizedPageHeaderTitle($home = false) {
        $prefix = $home ? 'home' : 'page';
        $typeArray = $this->getSetting($prefix . 'HeaderTitleType');
        $imageArray = $this->getSetting($prefix . 'HeaderTitleImage');
        $titleArray = $this->getSetting($prefix . 'HeaderTitle');

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

    public function getJournalPageHeaderTitle($home = false) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedPageHeaderTitle($home);
    }

    /**
     * Get "localized" journal page logo (if applicable).
     * @return string
     */
    public function getLocalizedPageHeaderLogo($home = false) {
        $prefix = $home ? 'home' : 'page';
        $logoArray = $this->getSetting($prefix . 'HeaderLogoImage');
        foreach (array(AppLocale::getLocale(), AppLocale::getPrimaryLocale()) as $locale) {
            if (isset($logoArray[$locale])) return $logoArray[$locale];
        }
        return null;
    }

    public function getJournalPageHeaderLogo($home = false) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedPageHeaderLogo($home);
    }

    /**
     * Get localized favicon
     * @return string
     */
    public function getLocalizedFavicon() {
        $faviconArray = $this->getSetting('journalFavicon');
        foreach (array(AppLocale::getLocale(), AppLocale::getPrimaryLocale()) as $locale) {
            if (isset($faviconArray[$locale])) return $faviconArray[$locale];
        }
        return null;
    }

    //
    // Get/set methods
    //

    /**
     * Get the localized title of the journal.
     * @param $preferredLocale string
     * @return string
     */
    public function getLocalizedTitle($preferredLocale = null) {
        return $this->getLocalizedSetting('title', $preferredLocale);
    }

    public function getJournalTitle() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedTitle();
    }

    /**
     * Get title of journal
     * @param $locale string
     * @return string
     */
    public function getTitle($locale) {
        return $this->getSetting('title', $locale);
    }

    /**
     * Get localized initials of journal
     * @return string
     */
    public function getLocalizedInitials() {
        return $this->getLocalizedSetting('initials');
    }

    public function getJournalInitials() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedInitials();
    }

    /**
     * Get the initials of the journal.
     * @param $locale string
     * @return string
     */
    public function getInitials($locale) {
        return $this->getSetting('initials', $locale);
    }

    /**
     * Get enabled flag of journal
     * @return int
     */
    public function getEnabled() {
        return $this->getData('enabled');
    }

    /**
     * Set enabled flag of journal
     * @param $enabled int
     */
    public function setEnabled($enabled) {
        return $this->setData('enabled',$enabled);
    }

    /**
     * Get ID of journal.
     * @return int
     */
    public function getJournalId() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getId();
    }

    /**
     * Set ID of journal.
     * @param $journalId int
     */
    public function setJournalId($journalId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->setId($journalId);
    }

    /**
     * Get the localized description of the journal.
     * @return string
     */
    public function getLocalizedDescription() {
        return $this->getLocalizedSetting('description');
    }

    public function getJournalDescription() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedDescription();
    }
    
    /**
     * Get description of journal.
     * @param $locale string
     * @return string
     */
    public function getDescription($locale) {
        return $this->getSetting('description', $locale);
    }

    /**
     * Get path to journal (in URL).
     * @return string
     */
    public function getPath() {
        return $this->getData('path');
    }

    /**
     * Set path to journal (in URL).
     * @param $path string
     */
    public function setPath($path) {
        return $this->setData('path', $path);
    }

    /**
     * Get sequence of journal in site table of contents.
     * @return float
     */
    public function getSequence() {
        return $this->getData('sequence');
    }

    /**
     * Set sequence of journal in site table of contents.
     * @param $sequence float
     */
    public function setSequence($sequence) {
        return $this->setData('sequence', $sequence);
    }

    /**
     * Retrieve array of journal settings.
     * [MODERNISASI] Removed & reference
     * @return array
     */
    public function getSettings() {
        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
        $settings = $journalSettingsDao->getJournalSettings($this->getId());
        return $settings;
    }

    /**
     * Retrieve a localized setting.
     * [MODERNISASI] Removed & reference
     * @param $name string
     * @param $preferredLocale string
     * @return mixed
     */
    public function getLocalizedSetting($name, $preferredLocale = null) {
        if (is_null($preferredLocale)) $preferredLocale = AppLocale::getLocale();
        $returner = $this->getSetting($name, $preferredLocale);
        if ($returner === null) {
            unset($returner);
            $returner = $this->getSetting($name, AppLocale::getPrimaryLocale());
        }
        return $returner;
    }

    /**
     * Retrieve a journal setting value.
     * [MODERNISASI] Removed & reference
     * @param $name string
     * @param $locale string
     * @return mixed
     */
    public function getSetting($name, $locale = null) {
        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
        $setting = $journalSettingsDao->getSetting($this->getId(), $name, $locale);
        return $setting;
    }

    /**
     * Update a journal setting value.
     * @param $name string
     * @param $value mixed
     * @param $type string optional
     * @param $isLocalized boolean optional
     */
    public function updateSetting($name, $value, $type = null, $isLocalized = false) {
        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
        return $journalSettingsDao->updateSetting($this->getId(), $name, $value, $type, $isLocalized);
    }


    //
    // Statistics API
    //
    
    /**
     * Return all metric types supported by this journal.
	 *
	 * @return array An array of strings of supported metric type identifiers.
	 */
    public function getMetricTypes($withDisplayNames = false) {
        $reportPlugins = PluginRegistry::loadCategory('reports', true, $this->getId());
        if (!is_array($reportPlugins)) return array();

        $metricTypes = array();
        foreach ($reportPlugins as $reportPlugin) {
            $pluginMetricTypes = $reportPlugin->getMetricTypes();
            if ($withDisplayNames) {
                foreach ($pluginMetricTypes as $metricType) {
                    $metricTypes[$metricType] = $reportPlugin->getMetricDisplayType($metricType);
                }
            } else {
                $metricTypes = array_merge($metricTypes, $pluginMetricTypes);
            }
        }

        return $metricTypes;
    }

    /**
     * Returns the currently configured default metric type for this journal.
     * 
	 * @return null|string A metric type identifier or null if no default metric
	 *   type could be identified.
	 */
    public function getDefaultMetricType() {
        $defaultMetricType = $this->getSetting('defaultMetricType');

        $availableMetrics = $this->getMetricTypes();
        if (empty($defaultMetricType)) {
            if (count($availableMetrics) === 1) {
                $defaultMetricType = $availableMetrics[0];
            } else {
                $application = PKPApplication::getApplication();
                $defaultMetricType = $application->getDefaultMetricType();
            }
        } else {
            if (!in_array($defaultMetricType, $availableMetrics)) return null;
        }
        return $defaultMetricType;
    }

    /**
     * Retrieve a statistics report pre-filtered on this journal.
     * [MODERNISASI] Strict Type Hinting (array) for Consistency with Application.inc.php
	 *
	 * @param $metricType null|integer|array metrics selection
	 * @param $columns integer|array column (aggregation level) selection
	 * @param $filter array report-level filter selection
	 * @param $orderBy array order criteria
	 * @param $range null|DBResultRange paging specification
	 *
	 * @return null|array The selected data as a simple tabular
	 *  result set or null if metrics are not supported by this journal.
	 */
    public function getMetrics($metricType = null, array $columns = [], array $filter = [], array $orderBy = [], $range = null) {
        // Add a journal filter and run the report.
        $filter[STATISTICS_DIMENSION_CONTEXT_ID] = $this->getId();
        $application = PKPApplication::getApplication();
        return $application->getMetrics($metricType, $columns, $filter, $orderBy, $range);
    }
}

?>