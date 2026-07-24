<?php
declare(strict_types=1);

/**
 * @file plugins/generic/translator/TranslatorHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TranslatorHandler
 * @ingroup plugins_generic_translator
 *
 * @brief This handles requests for the translator plugin.
 */

require_once('TranslatorAction.inc.php');
import('classes.handler.Handler');

class TranslatorHandler extends Handler {
    
    /** @var object|null */
    public $plugin;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->addCheck(new HandlerValidatorRoles($this, true, null, null, [ROLE_ID_SITE_ADMIN]));

        $plugin = Registry::get('plugin');
        $this->plugin = $plugin;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function TranslatorHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::TranslatorHandler(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct();
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
     * Display the main translator page.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function index(array $args = [], $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate(false);

        $rangeInfo = Handler::getRangeInfo('locales');

        $templateMgr = TemplateManager::getManager($request);
        import('lib.pkp.classes.core.ArrayItemIterator');
        $templateMgr->assign('locales', new ArrayItemIterator(
            AppLocale::getAllLocales(), 
            $rangeInfo->getPage(), 
            $rangeInfo->getCount()
        ));
        $templateMgr->assign('masterLocale', MASTER_LOCALE);

        $tarBinary = Config::getVar('cli', 'tar');
        $templateMgr->assign('tarAvailable', !empty($tarBinary) && file_exists($tarBinary));

        $templateMgr->display($plugin->getTemplatePath() . 'index.tpl');
    }

    /**
     * Setup common template variables.
     * @param bool $subclass Whether this is called from a subclass.
     */
    public function setupTemplate($subclass = true) {
        parent::setupTemplate();
        $request = Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);
        AppLocale::requireComponents(LOCALE_COMPONENT_CORE_ADMIN, LOCALE_COMPONENT_CORE_MANAGER);
        
        $pageHierarchy = [
            [$request->url(null, 'user'), 'navigation.user'], 
            [$request->url(null, 'admin'), 'admin.siteAdmin']
        ];
        if ($subclass) {
            $pageHierarchy[] = [$request->url(null, 'translate'), 'plugins.generic.translator.name'];
        }
        
        $templateMgr->assign('pageHierarchy', $pageHierarchy);
        $templateMgr->assign('helpTopicId', 'plugins.generic.TranslatorPlugin');
    }

    /**
     * Edit a locale.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function edit($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate(true);

        $locale = array_shift($args);
        $file = array_shift($args);

        if (!AppLocale::isLocaleValid($locale)) {
            $request->redirect(null, null, 'index');
        }
        
        $localeFiles = TranslatorAction::getLocaleFiles($locale);
        $miscFiles = TranslatorAction::getMiscLocaleFiles($locale);
        $emails = TranslatorAction::getEmailTemplates($locale);

        $templateMgr = TemplateManager::getManager($request);

        $localeFilesRangeInfo = Handler::getRangeInfo('localeFiles');
        $miscFilesRangeInfo = Handler::getRangeInfo('miscFiles');
        $emailsRangeInfo = Handler::getRangeInfo('emails');

        import('lib.pkp.classes.core.ArrayItemIterator');
        $templateMgr->assign('localeFiles', new ArrayItemIterator($localeFiles, $localeFilesRangeInfo->getPage(), $localeFilesRangeInfo->getCount()));
        $templateMgr->assign('miscFiles', new ArrayItemIterator($miscFiles, $miscFilesRangeInfo->getPage(), $miscFilesRangeInfo->getCount()));
        $templateMgr->assign('emails', new ArrayItemIterator($emails, $emailsRangeInfo->getPage(), $emailsRangeInfo->getCount()));

        $templateMgr->assign('locale', $locale);
        $templateMgr->assign('masterLocale', MASTER_LOCALE);

        $templateMgr->display($plugin->getTemplatePath() . 'locale.tpl');
    }

    /**
     * Check a locale for errors.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function check($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate(true);

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) {
            $request->redirect(null, null, 'index');
        }

        $localeFiles = TranslatorAction::getLocaleFiles($locale);
        $unwriteableFiles = [];
        foreach ($localeFiles as $localeFile) {
            $filename = Core::getBaseDir() . DIRECTORY_SEPARATOR . $localeFile;
            if (file_exists($filename) && !is_writable($filename)) {
                $unwriteableFiles[] = $localeFile;
            }
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('locale', $locale);
        $templateMgr->assign('errors', TranslatorAction::testLocale($locale, MASTER_LOCALE));
        $templateMgr->assign('emailErrors', TranslatorAction::testEmails($locale, MASTER_LOCALE));
        $templateMgr->assign('localeFiles', TranslatorAction::getLocaleFiles($locale));
        
        if (!empty($unwriteableFiles)) {
            $templateMgr->assign('error', true);
            $templateMgr->assign('unwriteableFiles', $unwriteableFiles);
        }
        $templateMgr->display($plugin->getTemplatePath() . 'errors.tpl');
    }

    /**
     * Export the locale files to the browser as a tarball.
     * Requires tar (configured in config.inc.php) for operation.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function export($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate(true);

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) {
            $request->redirect(null, null, 'index');
        }

        TranslatorAction::export($locale);
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
        $this->setupTemplate(true);

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) {
            $request->redirect(null, null, 'index');
        }

        $localeFiles = TranslatorAction::getLocaleFiles($locale);
        $changesByFile = [];

        $stack = (array) $request->getUserVar('stack');
        
        while (!empty($stack)) {
            $filename = trim((string) array_shift($stack));
            $key = trim((string) array_shift($stack));
            $value = trim((string) array_shift($stack));
            
            if (in_array($filename, $localeFiles, true)) {
                $changesByFile[$filename][$key] = $this->correctCr($value);
            }
        }

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

        $deleteKeys = (array) $request->getUserVar('deleteKey');
        if (!empty($deleteKeys)) {
            foreach ($deleteKeys as $deleteKey) {
                $safeDeleteKey = trim((string) $deleteKey);
                [$filename, $key] = explode('/', $safeDeleteKey, 2); 
                
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
     * Download a locale file.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function downloadLocaleFile($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate(true);

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) {
            $request->redirect(null, null, 'index');
        }

        $filename = urldecode(urldecode((string) array_shift($args)));
        if (!TranslatorAction::isLocaleFile($locale, $filename)) {
            $request->redirect(null, null, 'edit', $locale);
        }

        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Cache-Control: private');
        readfile($filename);
    }

    /**
     * Edit a locale file.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function editLocaleFile($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate(true);

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) {
            $request->redirect(null, null, 'index');
        }

        $filename = urldecode(urldecode((string) array_shift($args)));
        if (!TranslatorAction::isLocaleFile($locale, $filename)) {
            $request->redirect(null, null, 'edit', $locale);
        }

        $templateMgr = TemplateManager::getManager($request);
        if (!is_writable(Core::getBaseDir() . DIRECTORY_SEPARATOR . $filename)) {
            $templateMgr->assign('error', true);
        }

        import('lib.pkp.classes.file.EditableLocaleFile');
        $localeContentsRangeInfo = Handler::getRangeInfo('localeContents');
        $localeContents = EditableLocaleFile::load($filename);

        $searchKey = trim((string) $request->getUserVar('searchKey')); 
        
        $found = false;
        $index = 0;
        $pageIndex = 0;
        
        if (!empty($searchKey)) {
            foreach ($localeContents as $key => $value) {
                if ($index % $localeContentsRangeInfo->getCount() === 0) {
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
            $localeContentsRangeInfo->setPage($pageIndex);
            $templateMgr->assign('searchKey', $searchKey);
        }

        $templateMgr->assign('filename', $filename);
        $templateMgr->assign('locale', $locale);
        import('lib.pkp.classes.core.ArrayItemIterator');
        $templateMgr->assign('localeContents', new ArrayItemIterator($localeContents, $localeContentsRangeInfo->getPage(), $localeContentsRangeInfo->getCount()));
        $templateMgr->assign('referenceLocaleContents', EditableLocaleFile::load(TranslatorAction::determineReferenceFilename($locale, $filename)));

        $templateMgr->display($plugin->getTemplatePath() . 'localeFile.tpl');
    }

    /**
     * Edit a miscellaneous locale file.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function editMiscFile($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate(true);

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) {
            $request->redirect(null, null, 'index');
        }

        $filename = urldecode(urldecode((string) array_shift($args)));
        if (!TranslatorAction::isLocaleFile($locale, $filename)) {
            $request->redirect(null, null, 'edit', $locale);
        }
        
        $referenceFilename = TranslatorAction::determineReferenceFilename($locale, $filename);
        $templateMgr = TemplateManager::getManager($request);

        $templateMgr->assign('locale', $locale);
        $templateMgr->assign('filename', $filename);
        $templateMgr->assign('referenceContents', file_get_contents($referenceFilename));
        $templateMgr->assign('translationContents', file_exists($filename) ? file_get_contents($filename) : '');

        $templateMgr->display($plugin->getTemplatePath() . 'editMiscFile.tpl');
    }

    /**
     * Save a locale file.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function saveLocaleFile($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate(true);

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) {
            $request->redirect(null, null, 'index');
        }

        $filename = urldecode(urldecode((string) array_shift($args)));
        if (!TranslatorAction::isLocaleFile($locale, $filename)) {
            $request->redirect(null, null, 'edit', $locale);
        }

        import('lib.pkp.classes.file.EditableLocaleFile');
        $changes = (array) $request->getUserVar('changes');
        $file = new EditableLocaleFile($locale, $filename);

        while (!empty($changes)) {
            $key = trim((string) array_shift($changes)); 
            $value = $this->correctCr(trim((string) array_shift($changes)));
            
            if (!$file->update($key, $value)) {
                $file->insert($key, $value);
            }
        }
        $file->write();
        
        $redirectUrl = trim((string) $request->getUserVar('redirectUrl'));
        if (!empty($redirectUrl)) {
            $request->redirectUrl($redirectUrl);
        } else {
            $request->redirect(null, null, 'edit', $locale);
        }
    }

    /**
     * Delete a locale key.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function deleteLocaleKey($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate(true);

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) {
            $request->redirect(null, null, 'index');
        }

        $filename = urldecode(urldecode((string) array_shift($args)));
        if (!TranslatorAction::isLocaleFile($locale, $filename)) {
            $request->redirect(null, null, 'edit', $locale);
        }

        $changes = (array) $request->getUserVar('changes');
        $file = new EditableLocaleFile($locale, $filename);

        $keyToDelete = array_shift($args);
        if ($keyToDelete !== null && $file->delete((string) $keyToDelete)) {
            $file->write();
        }
        $request->redirect(null, null, 'editLocaleFile', [$locale, urlencode(urlencode($filename))]);
    }

    /**
     * Save a miscellaneous locale file.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function saveMiscFile($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate(true);

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) {
            $request->redirect(null, null, 'index');
        }

        $filename = urldecode(urldecode((string) array_shift($args)));
        if (!TranslatorAction::isLocaleFile($locale, $filename)) {
            $request->redirect(null, null, 'edit', $locale);
        }

        $fp = fopen($filename, 'w+');
        if ($fp) {
            $rawContents = (string) trim((string) $request->getUserVar('translationContents'));
            $contents = $this->correctCr($rawContents);
            fwrite($fp, $contents);
            fclose($fp);
        }
        $request->redirect(null, null, 'edit', $locale);
    }

    /**
     * Edit an email template.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function editEmail($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate(true);

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) {
            $request->redirect(null, null, 'index');
        }

        $emails = TranslatorAction::getEmailTemplates($locale);
        $referenceEmails = TranslatorAction::getEmailTemplates(MASTER_LOCALE);
        $emailKey = array_shift($args);

        if (!in_array($emailKey, array_keys($referenceEmails), true) && !in_array($emailKey, array_keys($emails), true)) {
            $request->redirect(null, null, 'index');
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('emailKey', $emailKey);
        $templateMgr->assign('locale', $locale);
        $templateMgr->assign('email', $emails[$emailKey] ?? '');

        $returnToCheckFlag = (int) trim((string) $request->getUserVar('returnToCheck'));
        $templateMgr->assign('returnToCheck', $returnToCheckFlag);
        
        $templateMgr->assign('referenceEmail', $referenceEmails[$emailKey] ?? '');

        $templateMgr->display($plugin->getTemplatePath() . 'editEmail.tpl');
    }

    /**
     * Create a locale file.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function createFile($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate(true);

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) {
            $request->redirect(null, null, 'index');
        }

        $filename = urldecode(urldecode((string) array_shift($args)));
        if (!TranslatorAction::isLocaleFile($locale, $filename)) {
            $request->redirect(null, null, 'edit', $locale);
        }

        import('lib.pkp.classes.file.FileManager');
        $fileManager = new FileManager();
        $fileManager->copyFile(TranslatorAction::determineReferenceFilename($locale, $filename), $filename);
        
        import('lib.pkp.classes.file.EditableLocaleFile');
        $file = new EditableLocaleFile($locale, $filename);
        
        $localeKeys = LocaleFile::load($filename);
        foreach (array_keys($localeKeys) as $key) {
            $file->update($key, '');
        }
        $file->write();
        
        $redirectUrl = trim((string) $request->getUserVar('redirectUrl'));
        if (!empty($redirectUrl)) {
            $request->redirectUrl($redirectUrl);
        } else {
            $request->redirect(null, null, 'edit', $locale);
        }
    }

    /**
     * Delete an email template.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function deleteEmail($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate(true);

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) {
            $request->redirect(null, null, 'index');
        }

        $emails = TranslatorAction::getEmailTemplates($locale);
        $referenceEmails = TranslatorAction::getEmailTemplates(MASTER_LOCALE);
        $emailKey = array_shift($args);

        if (!in_array($emailKey, array_keys($emails), true)) {
            $request->redirect(null, null, 'index');
        }

        import('lib.pkp.classes.file.EditableEmailFile');
        $file = new EditableEmailFile($locale, $this->getEmailTemplateFilename($locale));

        if ($file->delete($emailKey)) {
            $file->write();
        }
        $request->redirect(null, null, 'edit', $locale, null, 'emails');
    }

    /**
     * Save an email template.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function saveEmail($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate(true);

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) {
            $request->redirect(null, null, 'index');
        }

        $emails = TranslatorAction::getEmailTemplates($locale);
        $referenceEmails = TranslatorAction::getEmailTemplates(MASTER_LOCALE);
        $emailKey = array_shift($args);
        $targetFilename = str_replace(MASTER_LOCALE, $locale, $referenceEmails[$emailKey]['templateDataFile']);

        if (!in_array($emailKey, array_keys($emails), true)) {
            if (!in_array($emailKey, array_keys($referenceEmails), true)) {
                $request->redirect(null, null, 'index');
            }

            if (!file_exists($targetFilename)) {
                $dir = dirname($targetFilename);
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                file_put_contents($targetFilename, '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE email_texts SYSTEM "../../../../../lib/pkp/dtd/emailTemplateData.dtd">
<email_texts locale="' . $locale . '">
</email_texts>');
            }
        }

        import('lib.pkp.classes.file.EditableEmailFile');
        $file = new EditableEmailFile($locale, $targetFilename);

        $subject = $this->correctCr(trim((string) $request->getUserVar('subject'))); 
        $body = $this->correctCr(trim((string) $request->getUserVar('body')));
        $description = $this->correctCr(trim((string) $request->getUserVar('description')));

        if (!$file->update($emailKey, $subject, $body, $description)) {
            $file->insert($emailKey, $subject, $body, $description);
        }

        $file->write();
        
        if ((int) trim((string) $request->getUserVar('returnToCheck')) === 1) {
            $request->redirect(null, null, 'check', $locale);
        } else {
            $request->redirect(null, null, 'edit', $locale);
        }
    }

    /**
     * Correct CRLF to LF in a string.
     * @param mixed $value
     * @return string
     */
    public function correctCr($value) {
        return str_replace("\r\n", "\n", (string) $value);
    }
    
}
?>