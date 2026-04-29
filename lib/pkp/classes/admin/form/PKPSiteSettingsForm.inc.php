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
 * @class SiteSettingsForm
 * @ingroup admin_form
 *
 * @brief Form to edit site settings.
 */

define('SITE_MIN_PASSWORD_LENGTH', 12);
import('lib.pkp.classes.form.Form');

class PKPSiteSettingsForm extends Form {
    
    /** @var object Site settings DAO */
    public $siteSettingsDao;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct('admin/settings.tpl');
        $this->siteSettingsDao = DAORegistry::getDAO('SiteSettingsDAO');

        // Validation checks for this form
        $this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'admin.settings.form.titleRequired'));
        $this->addCheck(new FormValidatorLocale($this, 'contactName', 'required', 'admin.settings.form.contactNameRequired'));
        $this->addCheck(new FormValidatorLocaleEmail($this, 'contactEmail', 'required', 'admin.settings.form.contactEmailRequired'));
        
        // [WIZDAM] Replaced deprecated create_function with Closure
        $this->addCheck(new FormValidatorCustom(
            $this, 
            'minPasswordLength', 
            'required', 
            'admin.settings.form.minPasswordLengthRequired', 
            function($l) {
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
     * @param mixed $request
     * @param mixed $template
     */
    public function display($request = null, $template = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $site = $request->getSite();
        $publicFileManager = new PublicFileManager();
        $siteStyleFilename = $publicFileManager->getSiteFilesPath() . '/' . $site->getSiteStyleFilename();
        
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('showThumbnail', $site->getSetting('showThumbnail'));
        $templateMgr->assign('showTitle', $site->getSetting('showTitle'));
        $templateMgr->assign('showDescription', $site->getSetting('showDescription'));
        $templateMgr->assign('originalStyleFilename', $site->getOriginalStyleFilename());
        $templateMgr->assign('pageHeaderTitleImage', $site->getSetting('pageHeaderTitleImage'));
        $templateMgr->assign('styleFilename', $site->getSiteStyleFilename());
        $templateMgr->assign('publicFilesDir', $request->getBasePath() . '/' . $publicFileManager->getSiteFilesPath());
        $templateMgr->assign('dateStyleFileUploaded', file_exists($siteStyleFilename) ? filemtime($siteStyleFilename) : null);
        $templateMgr->assign('siteStyleFileExists', file_exists($siteStyleFilename));
        $templateMgr->assign('helpTopicId', 'site.siteManagement');
        
        return parent::display($request, $template);
    }

    /**
     * Initialize form data from current settings.
     */
    public function initData() {
        $siteDao = DAORegistry::getDAO('SiteDAO');
        $site = $siteDao->getSite();

        $data = [
            'title' => $site->getSetting('title'), // Localized
            'intro' => $site->getSetting('intro'), // Localized
            'redirect' => $site->getRedirect(),
            'showThumbnail' => $site->getSetting('showThumbnail'),
            'showTitle' => $site->getSetting('showTitle'),
            'showDescription' => $site->getSetting('showDescription'),
            'about' => $site->getSetting('about'), // Localized
            'contactName' => $site->getSetting('contactName'), // Localized
            'contactEmail' => $site->getSetting('contactEmail'), // Localized
            'minPasswordLength' => $site->getMinPasswordLength(),
            'pageHeaderTitleType' => $site->getSetting('pageHeaderTitleType'), // Localized
            'siteTheme' => $site->getSetting('siteTheme'),
            'oneStepReset' => $site->getSetting('oneStepReset') ? true : false,
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
        $this->readUserVars(
            ['pageHeaderTitleType', 'title', 'intro', 'about', 'redirect', 'contactName', 'contactEmail', 'minPasswordLength', 'oneStepReset', 'pageHeaderTitleImageAltText', 'showThumbnail', 'showTitle', 'showDescription', 'siteTheme']
        );
    }

    /**
     * Save site settings.
     * @param mixed $object
     */
    public function execute($object = null) {
        $siteDao = DAORegistry::getDAO('SiteDAO');
        $site = $siteDao->getSite();

        $site->setRedirect($this->getData('redirect'));
        $site->setMinPasswordLength((int) $this->getData('minPasswordLength'));

        $siteSettingsDao = $this->siteSettingsDao;
        foreach ($this->getLocaleFieldNames() as $setting) {
            $siteSettingsDao->updateSetting($setting, $this->getData($setting), null, true);
        }

        $site->updateSetting('siteTheme', $this->getData('siteTheme'), 'string', false);

        $setting = $site->getSetting('pageHeaderTitleImage');
        if (!empty($setting)) {
            $imageAltText = $this->getData('pageHeaderTitleImageAltText');
            $locale = $this->getFormLocale();
            $setting[$locale]['altText'] = $imageAltText[$locale];
            $site->updateSetting('pageHeaderTitleImage', $setting, 'object', true);
        }

        $site->updateSetting('showThumbnail', (bool) $this->getData('showThumbnail'), 'bool');
        $site->updateSetting('showTitle', (bool) $this->getData('showTitle'), 'bool');
        $site->updateSetting('showDescription', (bool) $this->getData('showDescription'), 'bool');
        $site->updateSetting('oneStepReset', (bool) $this->getData('oneStepReset'), 'bool');

        $siteDao->updateObject($site);
        return true;
    }

    /**
     * Uploads custom site stylesheet.
     */
    public function uploadSiteStyleSheet() {
        import('classes.file.PublicFileManager');
        $publicFileManager = new PublicFileManager();
        
        // [WIZDAM] Fetch site via singleton for file operations
        $site = Application::get()->getRequest()->getSite();
        
        if ($publicFileManager->uploadedFileExists('siteStyleSheet')) {
            $type = $publicFileManager->getUploadedFileType('siteStyleSheet');
            if ($type != 'text/plain' && $type != 'text/css') {
                return false;
            }

            $uploadName = $site->getSiteStyleFilename();
            if ($publicFileManager->uploadSiteFile('siteStyleSheet', $uploadName)) {
                $siteDao = DAORegistry::getDAO('SiteDAO');
                $site->setOriginalStyleFilename($publicFileManager->getUploadedFileName('siteStyleSheet'));
                $siteDao->updateObject($site);
            }
        }

        return true;
    }

    /**
     * Uploads custom site logo.
     * @param string $locale
     */
    public function uploadPageHeaderTitleImage($locale) {
        import('classes.file.PublicFileManager');
        $publicFileManager = new PublicFileManager();
        
        // [WIZDAM] Fetch site via singleton
        $site = Application::get()->getRequest()->getSite();
        
        if ($publicFileManager->uploadedFileExists('pageHeaderTitleImage')) {
            $type = $publicFileManager->getUploadedFileType('pageHeaderTitleImage');
            $extension = $publicFileManager->getImageExtension($type);
            if (!$extension) return false;

            $uploadName = 'pageHeaderTitleImage_' . $locale . $extension;
            if ($publicFileManager->uploadSiteFile('pageHeaderTitleImage', $uploadName)) {
                $siteDao = DAORegistry::getDAO('SiteDAO');
                $setting = $site->getSetting('pageHeaderTitleImage');
                list($width, $height) = getimagesize($publicFileManager->getSiteFilesPath() . '/' . $uploadName);
                
                $setting[$locale] = [
                    'originalFilename' => $publicFileManager->getUploadedFileName('pageHeaderTitleImage'),
                    'width' => $width,
                    'height' => $height,
                    'uploadName' => $uploadName,
                    'dateUploaded' => Core::getCurrentDate()
                ];
                $site->updateSetting('pageHeaderTitleImage', $setting, 'object', true);
            }
        }

        return true;
    }
}

?>