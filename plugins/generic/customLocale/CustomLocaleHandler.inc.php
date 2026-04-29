<?php
declare(strict_types=1);

/**
 * @file plugins/generic/customLocale/CustomLocaleHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CustomLocaleHandler
 * @ingroup plugins_generic_customLocale
 *
 * @brief This handles requests for the customLocale plugin.
 */

require_once('CustomLocalePlugin.inc.php');
require_once('CustomLocaleAction.inc.php');
import('classes.handler.Handler');

class CustomLocaleHandler extends Handler {

    /** @var CustomLocalePlugin Plugin associated with the request */
    public $plugin;
    
    /**
     * Constructor
     */
    public function __construct($parentPluginName) {
        parent::__construct();

        $plugin = PluginRegistry::getPlugin('generic', $parentPluginName);
        $this->plugin = $plugin;        
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CustomLocaleHandler($parentPluginName) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::CustomLocaleHandler(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($parentPluginName);
    }

    /**
     * Display a list of locales with custom locale files.
     */
    public function index(array $args = [], $request = null) {
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate($plugin, false);

        $journal = Request::getJournal();
        // Removed & reference from Handler::getRangeInfo
        $rangeInfo = Handler::getRangeInfo('locales');

        $templateMgr = TemplateManager::getManager();
        import('lib.pkp.classes.core.ArrayItemIterator');
        $templateMgr->assign('locales', new ArrayItemIterator($journal->getSupportedLocaleNames(), $rangeInfo->getPage(), $rangeInfo->getCount()));
        $templateMgr->assign('masterLocale', MASTER_LOCALE);
        $templateMgr->display($plugin->getTemplatePath() . 'index.tpl');
    }

    /**
     * Display a list of locale files for a given locale.
     * @param $args array first parameter is the locale
     */
    public function edit($args) {
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate($plugin, true);

        $locale = array_shift($args);
        $file = array_shift($args);

        if (!AppLocale::isLocaleValid($locale)) {
            $path = array($plugin->getCategory(), $plugin->getName(), 'index');
            Request::redirect(null, null, null, $path);
        }
        $localeFiles = CustomLocaleAction::getLocaleFiles($locale);

        $templateMgr = TemplateManager::getManager();
        $localeFilesRangeInfo = Handler::getRangeInfo('localeFiles');

        import('lib.pkp.classes.core.ArrayItemIterator');
        $templateMgr->assign('localeFiles', new ArrayItemIterator($localeFiles, $localeFilesRangeInfo->getPage(), $localeFilesRangeInfo->getCount()));
        $templateMgr->assign('locale', $locale);
        $templateMgr->assign('masterLocale', MASTER_LOCALE);
        $templateMgr->display($plugin->getTemplatePath() . 'locale.tpl');
    }

    /**
     * Display the editor for a given locale file.
     * @param $args array first parameter is the locale, second parameter is the filename
     */
    public function editLocaleFile($args) {
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate($plugin, true);

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) {
            $path = array($plugin->getCategory(), $plugin->getName(), 'index');
            Request::redirect(null, null, null, $path);
        }

        $filename = urldecode(urldecode(array_shift($args)));
        if (!CustomLocaleAction::isLocaleFile($locale, $filename)) {
            $path = array($plugin->getCategory(), $plugin->getName(), 'edit', $locale);
            Request::redirect(null, null, null, $path);
        }

        $templateMgr = TemplateManager::getManager();

        import('lib.pkp.classes.file.FileManager');
        $fileManager = new FileManager();

        import('lib.pkp.classes.file.EditableLocaleFile');
        $journal = Request::getJournal();
        $journalId = $journal->getId();
        $publicFilesDir = Config::getVar('files', 'public_files_dir');
        $customLocaleDir = $publicFilesDir . DIRECTORY_SEPARATOR . 'journals' . DIRECTORY_SEPARATOR . $journalId . DIRECTORY_SEPARATOR . CUSTOM_LOCALE_DIR;
        $customLocalePath = $customLocaleDir . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $filename;
        if ($fileManager->fileExists($customLocalePath)) {
            $localeContents = EditableLocaleFile::load($customLocalePath);
        } else {
            $localeContents = null;
        }

        $referenceLocaleContents = EditableLocaleFile::load($filename);
        $referenceLocaleContentsRangeInfo = Handler::getRangeInfo('referenceLocaleContents');

        // Handle a search, if one was executed.
        // [SECURITY FIX] Amankan 'searchKey'
        $searchKey = trim(Request::getUserVar('searchKey'));

        $found = false;
        $index = 0;
        $pageIndex = 0;
        if (!empty($searchKey)) foreach ($referenceLocaleContents as $key => $value) {
            if ($index % $referenceLocaleContentsRangeInfo->getCount() == 0) $pageIndex++;
            if ($key == $searchKey) {
                $found = true;
                break;
            }
            $index++;
        }

        if ($found) {
            $referenceLocaleContentsRangeInfo->setPage($pageIndex);
            $templateMgr->assign('searchKey', $searchKey);
        }

        $templateMgr->assign('filename', $filename);
        $templateMgr->assign('locale', $locale);
        import('lib.pkp.classes.core.ArrayItemIterator');
        $templateMgr->assign('referenceLocaleContents', new ArrayItemIterator($referenceLocaleContents, $referenceLocaleContentsRangeInfo->getPage(), $referenceLocaleContentsRangeInfo->getCount()));
        $templateMgr->assign('localeContents', $localeContents);

        $templateMgr->display($plugin->getTemplatePath() . 'localeFile.tpl');
    }

    /**
     * Display the editor for a given locale file.
     * @param $args array first parameter is the locale, second parameter is the filename
     */
    public function saveLocaleFile($args) {
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate($plugin, true);

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) {
            $path = array($plugin->getCategory(), $plugin->getName(), 'index');
            Request::redirect(null, null, null, $path);
        }

        $filename = urldecode(urldecode(array_shift($args)));
        if (!CustomLocaleAction::isLocaleFile($locale, $filename)) {
            $path = array($plugin->getCategory(), $plugin->getName(), 'edit', $locale);
            Request::redirect(null, null, null, $path);
        }

        $journal = Request::getJournal();
        $journalId = $journal->getId();
        
        // [SECURITY FIX] Amankan 'changes'
        $changes = (array) Request::getUserVar('changes');

        $customFilesDir = Config::getVar('files', 'public_files_dir') . DIRECTORY_SEPARATOR . 'journals' . DIRECTORY_SEPARATOR . $journalId . DIRECTORY_SEPARATOR . CUSTOM_LOCALE_DIR . DIRECTORY_SEPARATOR . $locale;
        $customFilePath = $customFilesDir . DIRECTORY_SEPARATOR . $filename;

        // Create empty custom locale file if it doesn't exist
        import('lib.pkp.classes.file.FileManager');
        $fileManager = new FileManager();

        import('lib.pkp.classes.file.EditableLocaleFile');
        if (!$fileManager->fileExists($customFilePath)) {
            $numParentDirs = substr_count($customFilePath, DIRECTORY_SEPARATOR); 
            $parentDirs = '';
            for ($i=0; $i<$numParentDirs; $i++) {
                $parentDirs .= '..' . DIRECTORY_SEPARATOR;
            }

            $newFileContents = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $newFileContents .= '<!DOCTYPE locale SYSTEM "' . $parentDirs . 'lib' . DIRECTORY_SEPARATOR . 'pkp' . DIRECTORY_SEPARATOR . 'dtd' . DIRECTORY_SEPARATOR . 'locale.dtd' . '">' . "\n";
            $newFileContents .= '<locale name="' . $locale . '">' . "\n";
            $newFileContents .= '</locale>';
            $fileManager->writeFile($customFilePath, $newFileContents);
        }

        $file = new EditableLocaleFile($locale, $customFilePath);

        while (!empty($changes)) {
            $key = array_shift($changes);
            $value = $this->correctCr(array_shift($changes));
            if (!empty($value)) {
                if (!$file->update($key, $value)) {
                    $file->insert($key, $value);
                }
            } else {
                $file->delete($key);
            }
        }
        $file->write();

        // [SECURITY FIX] Amankan 'redirectUrl'
        $redirectUrl = trim(Request::getUserVar('redirectUrl'));
        
        // Integrasi dalam Logika:
        Request::redirectUrl($redirectUrl);
    }

    /**
     * Replace carriage returns with newlines.
     * @param $value string
     * @return string
     */
    public function correctCr($value) {
        return str_replace("\r\n", "\n", $value);
    }

    /**
     * Setup common template variables.
     * @param $request PKPRequest
     * @param $plugin CustomLocalePlugin
     * @param $subclass boolean set to false if the handler is being used by a plugin that doesn't subclass CustomLocalePlugin
     */
    public function setupTemplate($request = null, $plugin = null, $subclass = true) {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->register_function('plugin_url', array($plugin, 'smartyPluginUrl'));
        $pageHierarchy = array(array(Request::url(null, 'user'), 'navigation.user'), array(Request::url(null, 'manager'), 'user.role.manager'));
        if ($subclass) {
            $path = array($this->plugin->getCategory(), $this->plugin->getName(), 'index');
            $pageHierarchy[] = array(Request::url(null, null, null, $path), 'plugins.generic.customLocale.name');
        }
        $templateMgr->assign('pageHierarchy', $pageHierarchy);
        $templateMgr->assign('helpTopicId', 'plugins.generic.CustomLocalePlugin');
    }
}
?>