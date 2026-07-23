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
     * @param string $parentPluginName
     */
    public function __construct($parentPluginName) {
        parent::__construct();

        $plugin = PluginRegistry::getPlugin('generic', $parentPluginName);
        $this->plugin = $plugin;        
    }

    /**
     * [SHIM] Backward Compatibility
     * @param string $parentPluginName
     */
    public function CustomLocaleHandler($parentPluginName) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::CustomLocaleHandler(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct($parentPluginName);
    }

    /**
     * Display a list of locales with custom locale files.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function index(array $args = [], $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate($request, $plugin, false);

        $journal = $request->getJournal();
        if (!$journal) {
            return;
        }

        $rangeInfo = Handler::getRangeInfo('locales');

        $templateMgr = TemplateManager::getManager($request);
        import('lib.pkp.classes.core.ArrayItemIterator');
        
        $templateMgr->assign('locales', new ArrayItemIterator(
            $journal->getSupportedLocaleNames(), 
            $rangeInfo->getPage(), 
            $rangeInfo->getCount()
        ));
        $templateMgr->assign('masterLocale', MASTER_LOCALE);
        $templateMgr->display($plugin->getTemplatePath() . 'index.tpl');
    }

    /**
     * Display a list of locale files for a given locale.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function edit($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate($request, $plugin, true);

        $locale = array_shift($args);
        
        if (!AppLocale::isLocaleValid($locale)) {
            $path = [$plugin->getCategory(), $plugin->getName(), 'index'];
            $request->redirect(null, null, null, $path);
        }
        
        $localeFiles = CustomLocaleAction::getLocaleFiles($locale);

        $templateMgr = TemplateManager::getManager($request);
        $localeFilesRangeInfo = Handler::getRangeInfo('localeFiles');

        import('lib.pkp.classes.core.ArrayItemIterator');
        $templateMgr->assign('localeFiles', new ArrayItemIterator(
            $localeFiles, 
            $localeFilesRangeInfo->getPage(), 
            $localeFilesRangeInfo->getCount()
        ));
        $templateMgr->assign('locale', $locale);
        $templateMgr->assign('masterLocale', MASTER_LOCALE);
        $templateMgr->display($plugin->getTemplatePath() . 'locale.tpl');
    }

    /**
     * Display the editor for a given locale file.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function editLocaleFile($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate($request, $plugin, true);

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) {
            $path = [$plugin->getCategory(), $plugin->getName(), 'index'];
            $request->redirect(null, null, null, $path);
        }

        $filename = urldecode(urldecode((string) array_shift($args)));
        if (!CustomLocaleAction::isLocaleFile($locale, $filename)) {
            $path = [$plugin->getCategory(), $plugin->getName(), 'edit', $locale];
            $request->redirect(null, null, null, $path);
        }

        $templateMgr = TemplateManager::getManager($request);

        import('lib.pkp.classes.file.FileManager');
        $fileManager = new FileManager();

        import('lib.pkp.classes.file.EditableLocaleFile');
        $journal = $request->getJournal();
        $journalId = $journal ? (int) $journal->getId() : 0;
        $publicFilesDir = Config::getVar('files', 'public_files_dir');
        $customLocaleDir = $publicFilesDir . DIRECTORY_SEPARATOR . 'journals' . DIRECTORY_SEPARATOR . $journalId . DIRECTORY_SEPARATOR . CUSTOM_LOCALE_DIR;
        $customLocalePath = $customLocaleDir . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $filename;
        
        $localeContents = null;
        if ($fileManager->fileExists($customLocalePath)) {
            $localeContents = EditableLocaleFile::load($customLocalePath);
        }

        $referenceLocaleContents = EditableLocaleFile::load($filename);
        $referenceLocaleContentsRangeInfo = Handler::getRangeInfo('referenceLocaleContents');

        // Handle a search, if one was executed.
        $searchKey = trim((string) $request->getUserVar('searchKey'));

        $found = false;
        $index = 0;
        $pageIndex = 0;
        
        // [WIZDAM] FIX: Separate if and foreach for proper PSR-12 formatting
        if (!empty($searchKey)) {
            foreach ($referenceLocaleContents as $key => $value) {
                if ($index % $referenceLocaleContentsRangeInfo->getCount() === 0) {
                    $pageIndex++;
                }
                if ($key === $searchKey) {
                    $found = true;
                    break;
                }
                $index++;
            }
        }

        if ($found) {
            $referenceLocaleContentsRangeInfo->setPage($pageIndex);
            $templateMgr->assign('searchKey', $searchKey);
        }

        $templateMgr->assign('filename', $filename);
        $templateMgr->assign('locale', $locale);
        
        import('lib.pkp.classes.core.ArrayItemIterator');
        $templateMgr->assign('referenceLocaleContents', new ArrayItemIterator(
            $referenceLocaleContents, 
            $referenceLocaleContentsRangeInfo->getPage(), 
            $referenceLocaleContentsRangeInfo->getCount()
        ));
        $templateMgr->assign('localeContents', $localeContents);

        $templateMgr->display($plugin->getTemplatePath() . 'localeFile.tpl');
    }

    /**
     * Save changes to a locale.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function saveLocaleChanges($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate($request, $plugin, false);

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) {
            $request->redirect(null, null, 'index');
        }

        $localeFiles = CustomLocaleAction::getLocaleFiles($locale);
        $changesByFile = [];

        // Arrange the list of changes to save into an array by file.
        $stack = (array) $request->getUserVar('stack');
        
        while (!empty($stack)) {
            $filename = trim((string) array_shift($stack));
            $key = trim((string) array_shift($stack));
            $value = trim((string) array_shift($stack));
            
            if (in_array($filename, $localeFiles, true)) {
                $changesByFile[$filename][$key] = $this->correctCr($value);
            }
        }

        // Save the changes file by file.
        import('lib.pkp.classes.file.EditableLocaleFile');
        foreach ($changesByFile as $filename => $changes) {
            $file = new EditableLocaleFile($locale, $filename);
            foreach ($changes as $key => $value) {
                if (empty($value)) {
                    continue;
                }
                if (!$file->update($key, $value)) {
                    $file->insert($key, $value);
                }
            }
            $file->write();
        }

        // Deal with key removals
        $deleteKeys = (array) $request->getUserVar('deleteKey');
        
        if (!empty($deleteKeys)) {
            foreach ($deleteKeys as $deleteKey) {
                $safeDeleteKey = trim((string) $deleteKey);
                list($filename, $key) = explode('/', $safeDeleteKey, 2); 
                
                $filename = urldecode(urldecode($filename));
                if (!in_array($filename, $localeFiles, true)) {
                    continue;
                }
                
                $file = new EditableLocaleFile($locale, $filename);
                $safeKey = trim((string) $key);
                
                $file->delete($safeKey);
                $file->write();
            }
        }

        // Deal with email removals
        import('lib.pkp.classes.file.EditableEmailFile');
        $deleteEmails = (array) $request->getUserVar('deleteEmail');
        
        if (!empty($deleteEmails)) {
            $file = new EditableEmailFile($locale, $this->getEmailTemplateFilename($locale));
            
            foreach ($deleteEmails as $key) {
                $safeKey = trim((string) $key);
                $file->delete($safeKey);
            }
            $file->write();
        }

        $redirectUrl = trim((string) $request->getUserVar('redirectUrl'));
        if (!empty($redirectUrl)) {
            $request->redirectUrl($redirectUrl);
        } else {
            $request->redirect(null, null, 'index');
        }
    }
    
    /**
     * Save changes to a specific locale file.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function saveLocaleFile($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate($request, $plugin, true);

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) {
            $path = [$plugin->getCategory(), $plugin->getName(), 'index'];
            $request->redirect(null, null, null, $path);
        }

        $filename = urldecode(urldecode((string) array_shift($args)));
        if (!CustomLocaleAction::isLocaleFile($locale, $filename)) {
            $path = [$plugin->getCategory(), $plugin->getName(), 'edit', $locale];
            $request->redirect(null, null, null, $path);
        }

        $journal = $request->getJournal();
        $journalId = $journal ? (int) $journal->getId() : 0;
        
        $changes = (array) $request->getUserVar('changes');

        $customFilesDir = Config::getVar('files', 'public_files_dir') . DIRECTORY_SEPARATOR . 'journals' . DIRECTORY_SEPARATOR . $journalId . DIRECTORY_SEPARATOR . CUSTOM_LOCALE_DIR . DIRECTORY_SEPARATOR . $locale;
        $customFilePath = $customFilesDir . DIRECTORY_SEPARATOR . $filename;

        import('lib.pkp.classes.file.FileManager');
        $fileManager = new FileManager();

        import('lib.pkp.classes.file.EditableLocaleFile');
        if (!$fileManager->fileExists($customFilePath)) {
            $numParentDirs = substr_count($customFilePath, DIRECTORY_SEPARATOR); 
            $parentDirs = '';
            for ($i = 0; $i < $numParentDirs; $i++) {
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
            $key = (string) array_shift($changes);
            $value = $this->correctCr((string) array_shift($changes));
            
            if (!empty($value)) {
                if (!$file->update($key, $value)) {
                    $file->insert($key, $value);
                }
            } else {
                $file->delete($key);
            }
        }
        $file->write();

        $redirectUrl = trim((string) $request->getUserVar('redirectUrl'));
        if (!empty($redirectUrl)) {
            $request->redirectUrl($redirectUrl);
        } else {
            $path = [$plugin->getCategory(), $plugin->getName(), 'edit', $locale];
            $request->redirect(null, null, null, $path);
        }
    }

    /**
     * Replace carriage returns with newlines.
     * @param mixed $value
     * @return string
     */
    public function correctCr($value) {
        return str_replace("\r\n", "\n", (string) $value);
    }

    /**
     * Get the email template filename for a locale.
     * @param string $locale
     * @return string
     */
    public function getEmailTemplateFilename($locale) {
        return 'locale/' . $locale . '/emailTemplates.xml';
    }
    
    /**
     * Setup common template variables.
     * @param PKPRequest|null $request
     * @param CustomLocalePlugin|null $plugin
     * @param bool $subclass
     */
    public function setupTemplate($request = null, $plugin = null, $subclass = true) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        
        $templateMgr = TemplateManager::getManager($request);
        
        if ($plugin) {
            $templateMgr->register_function('plugin_url', [$plugin, 'smartyPluginUrl']);
        }
        
        $pageHierarchy = [
            [$request->url(null, 'user'), 'navigation.user'], 
            [$request->url(null, 'manager'), 'user.role.manager']
        ];
        
        if ($subclass && $this->plugin) {
            $path = [$this->plugin->getCategory(), $this->plugin->getName(), 'index'];
            $pageHierarchy[] = [$request->url(null, null, null, $path), 'plugins.generic.customLocale.name'];
        }
        
        $templateMgr->assign('pageHierarchy', $pageHierarchy);
        $templateMgr->assign('helpTopicId', 'plugins.generic.CustomLocalePlugin');
    }
    
}
?>