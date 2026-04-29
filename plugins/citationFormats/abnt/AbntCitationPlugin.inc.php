<?php
declare(strict_types=1);

/**
 * @file plugins/citationFormats/abnt/AbntCitationPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * With contributions from by Lepidus Tecnologia
 *
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AbntCitationPlugin
 * @ingroup plugins_citationFormats_abnt
 *
 * @brief ABNT citation format plugin
 */

import('classes.plugins.CitationPlugin');

class AbntCitationPlugin extends CitationPlugin {
    
    /**
     * @see Plugin::register
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        $this->addLocaleData();
        return $success;
    }

    /**
     * Get the name of this plugin. The name must be unique within
     * its category.
     * @return string name of plugin
     */
    public function getName(): string {
        return 'AbntCitationPlugin';
    }

    /**
     * @see Plugin::getDisplayName
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.citationFormats.abnt.displayName');
    }

    /**
     * @see CitationFormatPlugin::getCitationFormatName
     * @return string
     */
    public function getCitationFormatName(): string {
        return __('plugins.citationFormats.abnt.citationFormatName');
    }

    /**
     * @see Plugin::getDescription
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.citationFormats.abnt.description');
    }

    /**
     * Get the localized location for citations in this journal
     * @param Journal $journal
     * @return string|null
     */
    public function getLocalizedLocation($journal) {
        $settings = $this->getSetting($journal->getId(), 'location');
        if ($settings === null) {
            return null;
        }
        $location = $settings[AppLocale::getLocale()] ?? null;
        if (empty($location)) {
            $location = $settings[AppLocale::getPrimaryLocale()] ?? null;
        }
        return $location;
    }

    /**
     * Display verbs for the management interface.
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        return [
            [
                'settings',
                __('plugins.citationFormats.abnt.manager.settings')
            ]
        ];
    }

    /**
     * Display an HTML-formatted citation. We register PKPString::strtoupper modifier
     * in order to convert author names to uppercase.
     * @param Article $article
     * @param Issue $issue
     * @param Journal $journal
     */
    public function displayCitation($article, $issue, $journal) {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->register_modifier('mb_upper', ['PKPString', 'strtoupper']);
        $templateMgr->register_modifier('abnt_date_format', [$this, 'abntDateFormat']);
        $templateMgr->register_modifier('abnt_date_format_with_day', [$this, 'abntDateFormatWithDay']);
        return parent::displayCitation($article, $issue, $journal);
    }

    /**
     * Execute a management verb on this plugin
     * @param string $verb
     * @param array $args
     * @param string|null $message If a message is returned from this by-ref
     * argument then it will be displayed as a notification if (and only
     * if) the method returns false.
     * @param array|null $messageParams
     * @param PKPRequest|null $request
     * @return bool will redirect to the plugin category page if false,
     * otherwise will remain on the same page
     */
    public function manage(string $verb, array $args, string $message = null, $messageParams = null, $request = null): bool {
        switch ($verb) {
            case 'settings':
                $templateMgr = TemplateManager::getManager();
                $templateMgr->register_function('plugin_url', [$this, 'smartyPluginUrl']);
                $journal = Request::getJournal();

                $this->import('AbntSettingsForm');
                $form = new AbntSettingsForm($this, (int) $journal->getId());
                if (Request::getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        Request::redirect(null, 'manager', 'plugin');
                        return false;
                    } else {
                        $this->setBreadCrumbs(true);
                        $form->display();
                    }
                } else {
                    $this->setBreadCrumbs(true);
                    if ($form->isLocaleResubmit()) {
                        $form->readInputData();
                    } else {
                        $form->initData();
                    }
                    $form->display();
                }
                return true;
            default:
                // Unknown management verb, delegate to parent
                return parent::manage($verb, $args, $message, $messageParams, $request);
        }
    }

    /**
     * Set the page's breadcrumbs, given the plugin's tree of items
     * to append.
     * @param bool $isSubclass
     */
    public function setBreadcrumbs($isSubclass = false) {
        $templateMgr = TemplateManager::getManager();
        $pageCrumbs = [
            [
                Request::url(null, 'manager'),
                'user.role.manager'
            ]
        ];
        if ($isSubclass) {
            $pageCrumbs[] = [
                Request::url(null, 'manager', 'plugins'),
                'manager.plugins'
            ];
        }

        $templateMgr->assign('pageHierarchy', $pageCrumbs);
    }

    /**
     * Extend the {url ...} smarty to support this plugin.
     * @return string
     */
    public function smartyPluginUrl($params, $smarty): string {
        $path = [$this->getCategory(), $this->getName()];
        if (is_array($params['path'])) {
            $params['path'] = array_merge($path, $params['path']);
        } elseif (!empty($params['path'])) {
            $params['path'] = array_merge($path, [$params['path']]);
        } else {
            $params['path'] = $path;
        }

        return $smarty->smartyUrl($params, $smarty);
    }

    /**
     * @function abntDateFormat Format date taking in consideration ABNT month abbreviations
     * @param string $string
     * @return string
     */
    public function abntDateFormat($string) {
        if (is_numeric($string)) {
            // it is a numeric string, we handle it as timestamp
            $timestamp = (int)$string;
        } else {
            $timestamp = strtotime($string);
        }
        
        // Modern replacement for strftime
        // Check length of full month name
        if (PKPString::strlen(date("F", $timestamp)) > 4) {
            $format = "M. Y"; // Short month with dot
        } else {
            $format = "F Y"; // Full month
        }

        return PKPString::strtolower(date($format, $timestamp));
    }

    /**
     * @function abntDateFormatWithDay Format date taking in consideration ABNT month abbreviations
     * @param string $string
     * @return string
     */
    public function abntDateFormatWithDay($string) {
        if (is_numeric($string)) {
            // it is a numeric string, we handle it as timestamp
            $timestamp = (int)$string;
        } else {
            $timestamp = strtotime($string);
        }
        
        // Modern replacement for strftime
        if (PKPString::strlen(date("F", $timestamp)) > 4) {
            $format = "d M. Y";
        } else {
            $format = "d F Y";
        }

        return PKPString::strtolower(date($format, $timestamp));
    }
}
?>