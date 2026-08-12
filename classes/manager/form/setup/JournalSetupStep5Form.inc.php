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
                'articleHeroMode' => 'int',
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
     * [SHIM] Backward Compatibility.
     */
    public function JournalSetupStep5Form() {
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
     * @return void
     */
    public function display($request = null, $template = null) {
        $request = $request ?? Application::get()->getRequest();
        $journal = $request->getJournal();

        $allThemes = PluginRegistry::loadCategory('themes');
        $journalThemes = [];
        if (is_array($allThemes)) {
            foreach ($allThemes as $plugin) {
                $journalThemes[basename($plugin->getPluginPath())] = $plugin;
            }
        }

        $templateMgr = TemplateManager::getManager();

        $templateMgr->assign([
            'homeHeaderTitleImage' => $journal->getSetting('homeHeaderTitleImage') ?? [],
            'homeHeaderLogoImage'=> $journal->getSetting('homeHeaderLogoImage') ?? [],
            'journalThumbnail'=> $journal->getSetting('journalThumbnail') ?? [],
            'pageHeaderTitleImage' => $journal->getSetting('pageHeaderTitleImage') ?? [],
            'pageHeaderLogoImage' => $journal->getSetting('pageHeaderLogoImage') ?? [],
            'homepageImage' => $journal->getSetting('homepageImage') ?? [],
            'journalStyleSheet' => $journal->getSetting('journalStyleSheet'),
            'readerInformation' => $journal->getSetting('readerInformation'),
            'authorInformation' => $journal->getSetting('authorInformation'),
            'librarianInformation' => $journal->getSetting('librarianInformation'),
            'journalThemes' => $journalThemes,
            'journalFavicon' => $journal->getSetting('journalFavicon')
        ]);

        $leftBlockPlugins = [];
        $disabledBlockPlugins = [];
        $rightBlockPlugins = [];
        
        $plugins = PluginRegistry::loadCategory('blocks');
        if (is_array($plugins)) {
            foreach ($plugins as $plugin) {
                if (!$plugin->getEnabled() || $plugin->getBlockContext() === '') {
                    if (count(array_intersect($plugin->getSupportedContexts(), [BLOCK_CONTEXT_LEFT_SIDEBAR, BLOCK_CONTEXT_RIGHT_SIDEBAR])) > 0) {
                        $disabledBlockPlugins[] = $plugin;
                    }
                } else {
                    $context = $plugin->getBlockContext();
                    if ($context === BLOCK_CONTEXT_LEFT_SIDEBAR) {
                        $leftBlockPlugins[] = $plugin;
                    } elseif ($context === BLOCK_CONTEXT_RIGHT_SIDEBAR) {
                        $rightBlockPlugins[] = $plugin;
                    }
                }
            }
        }
        
        $templateMgr->assign([
            'disabledBlockPlugins' => $disabledBlockPlugins,
            'leftBlockPlugins' => $leftBlockPlugins,
            'rightBlockPlugins' => $rightBlockPlugins
        ]);

        $templateMgr->setCacheability(CACHEABILITY_MUST_REVALIDATE);

        foreach ($this->getLocaleFieldNames() as $localeField) {
            if (!isset($this->_data[$localeField]) || !is_array($this->_data[$localeField])) {
                $this->_data[$localeField] = [];
            }
        }

        parent::display($request, $template);
    }

    /**
     * Uploads a journal image.
     * @param string $settingName Setting key associated with the file
     * @param string $locale
     * @return bool
     */
    public function uploadImage($settingName, $locale) {
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        
        $faviconTypes = ['.ico', '.png', '.gif'];

        import('classes.file.PublicFileManager');
        $fileManager = new PublicFileManager();
        
        if ($fileManager->uploadedFileExists($settingName)) {
            $type = $fileManager->getUploadedFileType($settingName);
            $extension = $fileManager->getImageExtension($type);
            if (!$extension) {
                return false;
            }
            if ($settingName === 'journalFavicon' && !in_array($extension, $faviconTypes, true)) {
                return false;
            }

            $uploadName = $settingName . '_' . $locale . $extension;
            if ($fileManager->uploadJournalFile($journal->getId(), $settingName, $uploadName)) {
                $filePath = $fileManager->getJournalFilesPath($journal->getId());
                $imageSize = getimagesize($filePath . '/' . $uploadName);
                $width = $imageSize ? (int) $imageSize[0] : 0;
                $height = $imageSize ? (int) $imageSize[1] : 0;

                $value = $journal->getSetting($settingName);
                if (!is_array($value)) {
                    $value = [];
                }
                $newImage = empty($value[$locale]);

                $value[$locale] = [
                    'name' => (string) $fileManager->getUploadedFileName($settingName),
                    'uploadName' => $uploadName,
                    'width' => $width,
                    'height' => $height,
                    'mimeType' => (string) $type,
                    'dateUploaded' => Core::getCurrentDate()
                ];

                $journal->updateSetting($settingName, $value, 'object', true);

                if ($newImage) {
                    $altText = $journal->getSetting($settingName . 'AltText');
                    if (is_array($altText) && !empty($altText[$locale])) {
                        $this->setData($settingName . 'AltText', $altText);
                    }
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Deletes a journal image.
     * @param string $settingName Setting key associated with the file
     * @param string|null $locale
     * @return bool
     */
    public function deleteImage($settingName, $locale = null) {
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        
        /** @var JournalSettingsDAO $settingsDao */
        $settingsDao = DAORegistry::getDAO('JournalSettingsDAO');
        $setting = $settingsDao->getSetting($journal->getId(), $settingName);

        import('classes.file.PublicFileManager');
        $fileManager = new PublicFileManager();
        
        $uploadName = '';
        if (is_array($setting)) {
            if ($locale !== null && isset($setting[$locale]['uploadName'])) {
                $uploadName = (string) $setting[$locale]['uploadName'];
            } elseif (isset($setting['uploadName'])) {
                $uploadName = (string) $setting['uploadName'];
            }
        }

        if ($uploadName !== '' && $fileManager->removeJournalFile($journal->getId(), $uploadName)) {
            $returner = $settingsDao->deleteSetting($journal->getId(), $settingName, $locale);
            if ($returner) {
                $templateMgr = TemplateManager::getManager();
                $templateMgr->assign([
                    'displayPageHeaderTitle' => $journal->getLocalizedPageHeaderTitle(),
                    'displayPageHeaderLogo' => $journal->getLocalizedPageHeaderLogo()
                ]);
            }
            return (bool) $returner;
        }
        
        return false;
    }

    /**
     * Uploads journal custom stylesheet.
     * @param string $settingName Setting key associated with the file
     * @return bool
     */
    public function uploadStyleSheet($settingName) {
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        
        /** @var JournalSettingsDAO $settingsDao */
        $settingsDao = DAORegistry::getDAO('JournalSettingsDAO');

        import('classes.file.PublicFileManager');
        $fileManager = new PublicFileManager();
        
        if ($fileManager->uploadedFileExists($settingName)) {
            $type = $fileManager->getUploadedFileType($settingName);
            if ($type !== 'text/css') {
                return false;
            }

            $uploadName = $settingName . '.css';
            if ($fileManager->uploadJournalFile($journal->getId(), $settingName, $uploadName)) {
                $value = [
                    'name' => (string) $fileManager->getUploadedFileName($settingName),
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
     * Execute the form.
     * @param object|null $object
     * @return mixed
     */
    public function execute($object = null) {
        $request = Application::get()->getRequest();

        $blockVars = ['blockSelectLeft', 'blockUnselected', 'blockSelectRight'];
        $blockData = [];
        foreach ($blockVars as $varName) {
            $blockData[$varName] = array_map('urldecode', explode(' ', (string) $request->getUserVar($varName)));
        }

        $plugins = PluginRegistry::loadCategory('blocks');
        if (is_array($plugins)) {
            foreach ($plugins as $plugin) {
                $plugin->setEnabled(!in_array($plugin->getName(), $blockData['blockUnselected'], true));

                if (in_array($plugin->getName(), $blockData['blockSelectLeft'], true)) {
                    $plugin->setBlockContext(BLOCK_CONTEXT_LEFT_SIDEBAR);
                    $plugin->setSeq(array_search($plugin->getName(), $blockData['blockSelectLeft'], true));
                } elseif (in_array($plugin->getName(), $blockData['blockSelectRight'], true)) {
                    $plugin->setBlockContext(BLOCK_CONTEXT_RIGHT_SIDEBAR);
                    $plugin->setSeq(array_search($plugin->getName(), $blockData['blockSelectRight'], true));
                }
            }
        }

        return parent::execute($object);
    }

}
?>