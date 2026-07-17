<?php
declare(strict_types=1);

/**
 * @defgroup admin_form
 */

/**
 * @file classes/admin/form/PKPSiteSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPSiteSettingsForm
 * @ingroup admin_form
 *
 * @brief Form to edit site settings.
 */

define('SITE_MIN_PASSWORD_LENGTH', 12);

import('lib.pkp.classes.form.Form');
import('classes.file.PublicFileManager');

class PKPSiteSettingsForm extends Form {
    
    /** @var SiteSettingsDAO|null Site settings DAO */
    public $siteSettingsDao;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct('admin/settings.tpl');
        
        /** @var SiteSettingsDAO $siteSettingsDao */
        $this->siteSettingsDao = DAORegistry::getDAO('SiteSettingsDAO');

        // Validation checks for this form
        $this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'admin.settings.form.titleRequired'));
        $this->addCheck(new FormValidatorLocale($this, 'contactName', 'required', 'admin.settings.form.contactNameRequired'));
        $this->addCheck(new FormValidatorLocaleEmail($this, 'contactEmail', 'required', 'admin.settings.form.contactEmailRequired'));

        $this->addCheck(new FormValidatorCustom(
            $this, 
            'minPasswordLength', 
            'required', 
            'admin.settings.form.minPasswordLengthRequired', 
            function(int $l): bool {
                return $l >= SITE_MIN_PASSWORD_LENGTH;
            }
        ));
        
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPSiteSettingsForm() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Display the form.
     * 
     * @param PKPRequest|null $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        // [WIZDAM] Strict type check fallback
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();
        $site = $request->getSite();
        
        $styleFilename = (string) ($site ? $site->getSiteStyleFilename() : '');
        $publicFileManager = new PublicFileManager();
        $siteStyleFullPath = $publicFileManager->getSiteFilesPath() . '/' . $styleFilename;
        
        $templateMgr = TemplateManager::getManager($request);
        
        // [WIZDAM] Micro-Payload
        $templateMgr->assign([
            'showThumbnail' => $site ? (bool) $site->getSetting('showThumbnail') : false,
            'showTitle' => $site ? (bool) $site->getSetting('showTitle') : false,
            'showDescription' => $site ? (bool) $site->getSetting('showDescription') : false,
            'originalStyleFilename' => $site ? $site->getOriginalStyleFilename() : '',
            'pageHeaderTitleImage' => $site ? $site->getSetting('pageHeaderTitleImage') : null,
            'styleFilename' => $styleFilename,
            'publicFilesDir' => $request->getBasePath() . '/' . $publicFileManager->getSiteFilesPath(),
            'dateStyleFileUploaded' => ($styleFilename !== '' && file_exists($siteStyleFullPath)) ? filemtime($siteStyleFullPath) : null,
            'siteStyleFileExists' => ($styleFilename !== '' && file_exists($siteStyleFullPath)),
            'helpTopicId' => 'site.siteManagement'
        ]);
        
        parent::display($request, $template);
    }

    /**
     * Initialize form data from current settings.
     */
    public function initData() {
        /** @var SiteDAO $siteDao */
        $siteDao = DAORegistry::getDAO('SiteDAO');
        $site = $siteDao ? $siteDao->getSite() : null;

        $data = [
            'title' => $site ? $site->getSetting('title') : [],
            'intro' => $site ? $site->getSetting('intro') : [],
            'redirect' => $site ? (int) $site->getRedirect() : 0,
            'showThumbnail' => $site ? (bool) $site->getSetting('showThumbnail') : false,
            'showTitle' => $site ? (bool) $site->getSetting('showTitle') : false,
            'showDescription' => $site ? (bool) $site->getSetting('showDescription') : false,
            'about' => $site ? $site->getSetting('about') : [],
            'contactName' => $site ? $site->getSetting('contactName') : [],
            'contactEmail' => $site ? $site->getSetting('contactEmail') : [],
            'minPasswordLength' => $site ? (int) $site->getMinPasswordLength() : SITE_MIN_PASSWORD_LENGTH,
            'pageHeaderTitleType' => $site ? $site->getSetting('pageHeaderTitleType') : [],
            'siteTheme' => $site ? (string) $site->getSetting('siteTheme') : '',
            'oneStepReset' => $site ? (bool) $site->getSetting('oneStepReset') : false,
        ];

        foreach ($data as $key => $value) {
            $this->setData($key, $value);
        }
    }

    /**
     * Get locale field names.
     * @return array
     */
    public function getLocaleFieldNames() {
        return array_merge(parent::getLocaleFieldNames(), ['title', 'pageHeaderTitleType', 'intro', 'about', 'contactName', 'contactEmail']);
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars([
            'pageHeaderTitleType', 'title', 'intro', 'about', 'redirect', 
            'contactName', 'contactEmail', 'minPasswordLength', 'oneStepReset', 
            'pageHeaderTitleImageAltText', 'showThumbnail', 'showTitle', 
            'showDescription', 'siteTheme'
        ]);
    }

    /**
     * Save site settings.
     * 
     * @param mixed $object
     * @return bool
     */
    public function execute($object = null) {
        /** @var SiteDAO $siteDao */
        $siteDao = DAORegistry::getDAO('SiteDAO');
        $site = $siteDao ? $siteDao->getSite() : null;
        
        if (!$site || !$this->siteSettingsDao) {
            return false;
        }

        $site->setRedirect((int) $this->getData('redirect'));
        $site->setMinPasswordLength((int) $this->getData('minPasswordLength'));

        foreach ($this->getLocaleFieldNames() as $setting) {
            $this->siteSettingsDao->updateSetting($setting, $this->getData($setting), null, true);
        }

        $this->siteSettingsDao->updateSetting('siteTheme', (string) $this->getData('siteTheme'), 'string', false);
        
        $setting = $site->getSetting('pageHeaderTitleImage');
        if (!is_array($setting)) {
            $setting = [];
        }
        
        $imageAltText = $this->getData('pageHeaderTitleImageAltText');
        $locale = $this->getFormLocale();
        
        if (is_array($imageAltText) && isset($imageAltText[$locale])) {
            $setting[$locale]['altText'] = $imageAltText[$locale];
        }
        
        $this->siteSettingsDao->updateSetting('pageHeaderTitleImage', $setting, 'object', true);
        $this->siteSettingsDao->updateSetting('showThumbnail', (bool) $this->getData('showThumbnail'), 'bool', false);
        $this->siteSettingsDao->updateSetting('showTitle', (bool) $this->getData('showTitle'), 'bool', false);
        $this->siteSettingsDao->updateSetting('showDescription', (bool) $this->getData('showDescription'), 'bool', false);
        $this->siteSettingsDao->updateSetting('oneStepReset', (bool) $this->getData('oneStepReset'), 'bool', false);

        $siteDao->updateObject($site);
        return true;
    }

    /**
     * Uploads custom site stylesheet.
     * @return bool
     */
    public function uploadSiteStyleSheet() {
        $publicFileManager = new PublicFileManager();
        $site = Application::get()->getRequest()->getSite();
        
        if (!$site || !$publicFileManager->uploadedFileExists('siteStyleSheet')) {
            return false;
        }

        $type = $publicFileManager->getUploadedFileType('siteStyleSheet');
        // [WIZDAM] Strict comparison
        if ($type !== 'text/plain' && $type !== 'text/css') {
            return false;
        }

        // [WIZDAM] Fallback jika filename null
        $uploadName = (string) $site->getSiteStyleFilename();
        if ($uploadName === '') {
            $uploadName = 'siteStyleSheet.css';
        }

        if ($publicFileManager->uploadSiteFile('siteStyleSheet', $uploadName)) {
            /** @var SiteDAO $siteDao */
            $siteDao = DAORegistry::getDAO('SiteDAO');
            $site->setOriginalStyleFilename($publicFileManager->getUploadedFileName('siteStyleSheet'));
            $siteDao->updateObject($site);
        }

        return true;
    }

    /**
     * Uploads custom site logo.
     * 
     * @param string $locale
     * @return bool
     */
    public function uploadPageHeaderTitleImage($locale) {
        $publicFileManager = new PublicFileManager();
        $site = Application::get()->getRequest()->getSite();
        
        if (!$site || !$publicFileManager->uploadedFileExists('pageHeaderTitleImage')) {
            return false;
        }

        $type = $publicFileManager->getUploadedFileType('pageHeaderTitleImage');
        $extension = $publicFileManager->getImageExtension($type);
        if (!$extension) {
            return false;
        }

        $uploadName = 'pageHeaderTitleImage_' . $locale . $extension;
        if ($publicFileManager->uploadSiteFile('pageHeaderTitleImage', $uploadName)) {
            /** @var SiteDAO $siteDao */
            $siteDao = DAORegistry::getDAO('SiteDAO');
            
            $imagePath = $publicFileManager->getSiteFilesPath() . '/' . $uploadName;
            $size = getimagesize($imagePath);
            $width = $size[0] ?? 0;
            $height = $size[1] ?? 0;
            
            $setting = $site->getSetting('pageHeaderTitleImage');
            if (!is_array($setting)) {
                $setting = [];
            }
            
            $setting[$locale] = [
                'originalFilename' => $publicFileManager->getUploadedFileName('pageHeaderTitleImage'),
                'width' => $width,
                'height' => $height,
                'uploadName' => $uploadName,
                'dateUploaded' => Core::getCurrentDate()
            ];
            
            $this->siteSettingsDao->updateSetting('pageHeaderTitleImage', $setting, 'object', true);
        }

        return true;
    }

}
?>