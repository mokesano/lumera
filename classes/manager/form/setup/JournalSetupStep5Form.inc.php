<?php
declare(strict_types=1);

/**
 * @file classes/manager/form/setup/JournalSetupStep5Form.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class JournalSetupStep5Form
 * @ingroup manager_form_setup
 *
 * @brief Form for Step 5 of journal setup.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.manager.form.setup.JournalSetupForm');

class JournalSetupStep5Form extends JournalSetupForm {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(
            5,
            [
                'homeHeaderTitleType' => 'int',
                'homeHeaderTitle' => 'string',
                'pageHeaderTitleType' => 'int',
                'pageHeaderTitle' => 'string',
                'readerInformation' => 'string',
                'authorInformation' => 'string',
                'librarianInformation' => 'string',
                'journalPageHeader' => 'string',
                'journalPageFooter' => 'string',
                'displayCurrentIssue' => 'bool',
                'additionalHomeContent' => 'string',
                'description' => 'string',
                'navItems' => 'object',
                'itemsPerPage' => 'int',
                'numPageLinks' => 'int',
                'journalTheme' => 'string',
                'journalThumbnailAltText' => 'string',
                'homeHeaderTitleImageAltText' => 'string',
                'homeHeaderLogoImageAltText' => 'string',
                'homepageImageAltText' => 'string',
                'pageHeaderTitleImageAltText' => 'string',
                'pageHeaderLogoImageAltText' => 'string'
            ]
        );
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function JournalSetupStep5Form() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Get the list of field names for which localized settings are used.
     * @return array
     */
    public function getLocaleFieldNames() {
        return [
            'homeHeaderTitleType', 'homeHeaderTitle', 'pageHeaderTitleType', 'pageHeaderTitle', 
            'readerInformation', 'authorInformation', 'librarianInformation', 'journalPageHeader', 
            'journalPageFooter', 'homepageImage', 'journalFavicon', 'additionalHomeContent', 
            'description', 'navItems', 'homeHeaderTitleImageAltText', 'homeHeaderLogoImageAltText', 
            'journalThumbnailAltText', 'homepageImageAltText', 'pageHeaderTitleImageAltText', 
            'pageHeaderLogoImageAltText'
        ];
    }

    /**
     * Display the form.
     * @param object|null $request
     * @param object|null $template
     */
    public function display($request = null, $template = null) {
        // [WIZDAM] Request Singleton
        $request = $request ?? Application::get()->getRequest();
        $journal = $request->getJournal();

        $allThemes = PluginRegistry::loadCategory('themes');
        $journalThemes = [];
        foreach ($allThemes as $key => $junk) {
            $plugin = $allThemes[$key];
            $journalThemes[basename($plugin->getPluginPath())] = $plugin;
        }

        $templateMgr = TemplateManager::getManager();

        $templateMgr->assign([
            'homeHeaderTitleImage' => $journal->getSetting('homeHeaderTitleImage'),
            'homeHeaderLogoImage'=> $journal->getSetting('homeHeaderLogoImage'),
            'journalThumbnail'=> $journal->getSetting('journalThumbnail'),
            'pageHeaderTitleImage' => $journal->getSetting('pageHeaderTitleImage'),
            'pageHeaderLogoImage' => $journal->getSetting('pageHeaderLogoImage'),
            'homepageImage' => $journal->getSetting('homepageImage'),
            'journalStyleSheet' => $journal->getSetting('journalStyleSheet'),
            'readerInformation' => $journal->getSetting('readerInformation'),
            'authorInformation' => $journal->getSetting('authorInformation'),
            'librarianInformation' => $journal->getSetting('librarianInformation'),
            'journalThemes' => $journalThemes,
            'journalFavicon' => $journal->getSetting('journalFavicon')
        ]);

        // Make lists of the sidebar blocks available.
        $leftBlockPlugins = $disabledBlockPlugins = $rightBlockPlugins = [];
        $plugins = PluginRegistry::loadCategory('blocks');
        foreach ($plugins as $key => $junk) {
            if (!$plugins[$key]->getEnabled() || $plugins[$key]->getBlockContext() == '') {
                if (count(array_intersect($plugins[$key]->getSupportedContexts(), [BLOCK_CONTEXT_LEFT_SIDEBAR, BLOCK_CONTEXT_RIGHT_SIDEBAR])) > 0) 
                    $disabledBlockPlugins[] = $plugins[$key];
            } else switch ($plugins[$key]->getBlockContext()) {
                case BLOCK_CONTEXT_LEFT_SIDEBAR:
                    $leftBlockPlugins[] = $plugins[$key];
                    break;
                case BLOCK_CONTEXT_RIGHT_SIDEBAR:
                    $rightBlockPlugins[] = $plugins[$key];
                    break;
            }
        }
        $templateMgr->assign([
            'disabledBlockPlugins' => $disabledBlockPlugins,
            'leftBlockPlugins' => $leftBlockPlugins,
            'rightBlockPlugins' => $rightBlockPlugins
        ]);

        $templateMgr->setCacheability(CACHEABILITY_MUST_REVALIDATE);
        parent::display($request, $template);
    }

    /**
     * Uploads a journal image.
     * @param string $settingName setting key associated with the file
     * @param string $locale
     * @return boolean
     */
    public function uploadImage($settingName, $locale) {
        // [WIZDAM] Request Singleton
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        
        $settingsDao = DAORegistry::getDAO('JournalSettingsDAO');
        $faviconTypes = ['.ico', '.png', '.gif'];

        import('classes.file.PublicFileManager');
        $fileManager = new PublicFileManager();
        if ($fileManager->uploadedFileExists($settingName)) {
            $type = $fileManager->getUploadedFileType($settingName);
            $extension = $fileManager->getImageExtension($type);
            if (!$extension) {
                return false;
            }
            if ($settingName == 'journalFavicon' && !in_array($extension, $faviconTypes)) {
                return false;
            }

            $uploadName = $settingName . '_' . $locale . $extension;
            if ($fileManager->uploadJournalFile($journal->getId(), $settingName, $uploadName)) {
                // Get image dimensions
                $filePath = $fileManager->getJournalFilesPath($journal->getId());
                list($width, $height) = getimagesize($filePath . '/' . $uploadName);

                $value = $journal->getSetting($settingName);
                $newImage = empty($value[$locale]);

                $value[$locale] = [
                    'name' => $fileManager->getUploadedFileName($settingName, $locale),
                    'uploadName' => $uploadName,
                    'width' => $width,
                    'height' => $height,
                    'mimeType' => $type,
                    'dateUploaded' => Core::getCurrentDate()
                ];

                $journal->updateSetting($settingName, $value, 'object', true);

                if ($newImage) {
                    $altText = $journal->getSetting($settingName.'AltText');
                    if (!empty($altText[$locale])) {
                        $this->setData($settingName.'AltText', $altText);
                    }
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Deletes a journal image.
     * @param string $settingName setting key associated with the file
     * @param string|null $locale
     * @return boolean
     */
    public function deleteImage($settingName, $locale = null) {
        // [WIZDAM] Request Singleton
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        
        $settingsDao = DAORegistry::getDAO('JournalSettingsDAO');
        $setting = $settingsDao->getSetting($journal->getId(), $settingName);

        import('classes.file.PublicFileManager');
        $fileManager = new PublicFileManager();
        if ($fileManager->removeJournalFile($journal->getId(), $locale !== null ? $setting[$locale]['uploadName'] : $setting['uploadName'] )) {
            $returner = $settingsDao->deleteSetting($journal->getId(), $settingName, $locale);
            // Ensure page header is refreshed
            if ($returner) {
                $templateMgr = TemplateManager::getManager();
                $templateMgr->assign([
                    'displayPageHeaderTitle' => $journal->getLocalizedPageHeaderTitle(),
                    'displayPageHeaderLogo' => $journal->getLocalizedPageHeaderLogo()
                ]);
            }
            return $returner;
        } else {
            return false;
        }
    }

    /**
     * Uploads journal custom stylesheet.
     * @param string $settingName setting key associated with the file
     * @return boolean
     */
    public function uploadStyleSheet($settingName) {
        // [WIZDAM] Request Singleton
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        
        $settingsDao = DAORegistry::getDAO('JournalSettingsDAO');

        import('classes.file.PublicFileManager');
        $fileManager = new PublicFileManager();
        if ($fileManager->uploadedFileExists($settingName)) {
            $type = $fileManager->getUploadedFileType($settingName);
            if ($type != 'text/css') {
                return false;
            }

            $uploadName = $settingName . '.css';
            if($fileManager->uploadJournalFile($journal->getId(), $settingName, $uploadName)) {
                $value = [
                    'name' => $fileManager->getUploadedFileName($settingName),
                    'uploadName' => $uploadName,
                    'dateUploaded' => Core::getCurrentDate()
                ];

                $settingsDao->updateSetting($journal->getId(), $settingName, $value, 'object');
                return true;
            }
        }

        return false;
    }

    /**
     * Execute the form
     * @param object|null $object
     */
    public function execute($object = null) {
        // [WIZDAM] Request Singleton
        $request = Application::get()->getRequest();

        // Save the block plugin layout settings.
        $blockVars = ['blockSelectLeft', 'blockUnselected', 'blockSelectRight'];
        foreach ($blockVars as $varName) {
            $$varName = array_map('urldecode', explode(' ', (string) $request->getUserVar($varName)));
        }

        $plugins = PluginRegistry::loadCategory('blocks');
        foreach ($plugins as $key => $junk) {
            $plugin = $plugins[$key];
            $plugin->setEnabled(!in_array($plugin->getName(), $blockUnselected));
            if (in_array($plugin->getName(), $blockSelectLeft)) {
                $plugin->setBlockContext(BLOCK_CONTEXT_LEFT_SIDEBAR);
                $plugin->setSeq(array_search($key, $blockSelectLeft));
            }
            else if (in_array($plugin->getName(), $blockSelectRight)) {
                $plugin->setBlockContext(BLOCK_CONTEXT_RIGHT_SIDEBAR);
                $plugin->setSeq(array_search($key, $blockSelectRight));
            }
            unset($plugin);
        }

        return parent::execute($object);
    }
}
?>