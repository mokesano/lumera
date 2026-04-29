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
 * MODERNIZED FOR WIZDAM FORK
 */

require_once('TranslatorAction.inc.php');
import('classes.handler.Handler');

class TranslatorHandler extends Handler {
    
    /** @var object */
    public $plugin;

    /**
     * Constructor
     **/
    public function __construct() {
        parent::__construct();
        $this->addCheck(new HandlerValidatorRoles($this, true, null, null, array(ROLE_ID_SITE_ADMIN)));

        $plugin = Registry::get('plugin');
        $this->plugin = $plugin;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function TranslatorHandler() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::TranslatorHandler(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Get the email template filename for a locale.
     * @param $locale string
     * @return string
     */
    public function getEmailTemplateFilename($locale) {
        return 'locale/' . $locale . '/emailTemplates.xml';
    }

    /**
     * Display the main translator page.
     */
    public function index(array $args = [], $request = null) {
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate(false);

        $rangeInfo = Handler::getRangeInfo('locales');

        $templateMgr = TemplateManager::getManager();
        import('lib.pkp.classes.core.ArrayItemIterator');
        $templateMgr->assign('locales', new ArrayItemIterator(AppLocale::getAllLocales(), $rangeInfo->getPage(), $rangeInfo->getCount()));
        $templateMgr->assign('masterLocale', MASTER_LOCALE);

        // Test whether the tar binary is available for the export to work
        $tarBinary = Config::getVar('cli', 'tar');
        $templateMgr->assign('tarAvailable', !empty($tarBinary) && file_exists($tarBinary));

        $templateMgr->display($plugin->getTemplatePath() . 'index.tpl');
    }

    /**
     * Setup common template variables.
     * @param $subclass boolean Whether this is called from a subclass.
     */
    public function setupTemplate($subclass = true) {
        parent::setupTemplate();
        $templateMgr = TemplateManager::getManager();
        AppLocale::requireComponents(LOCALE_COMPONENT_PKP_ADMIN, LOCALE_COMPONENT_PKP_MANAGER);
        $pageHierarchy = array(array(Request::url(null, 'user'), 'navigation.user'), array(Request::url(null, 'admin'), 'admin.siteAdmin'));
        if ($subclass) $pageHierarchy[] = array(Request::url(null, 'translate'), 'plugins.generic.translator.name');
        $templateMgr->assign('pageHierarchy', $pageHierarchy);
        $templateMgr->assign('helpTopicId', 'plugins.generic.TranslatorPlugin');
    }

    /**
     * Edit a locale.
     * @param $args array
     */
    public function edit($args) {
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate();

        $locale = array_shift($args);
        $file = array_shift($args);

        if (!AppLocale::isLocaleValid($locale)) Request::redirect(null, null, 'index');
        $localeFiles = TranslatorAction::getLocaleFiles($locale);
        $miscFiles = TranslatorAction::getMiscLocaleFiles($locale);
        $emails = TranslatorAction::getEmailTemplates($locale);

        $templateMgr = TemplateManager::getManager();

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
     * @param $args array
     */
    public function check($args) {
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate();

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) Request::redirect(null, null, 'index');

        $localeFiles = TranslatorAction::getLocaleFiles($locale);
        $unwriteableFiles = array();
        foreach ($localeFiles as $localeFile) {
            $filename = Core::getBaseDir() . DIRECTORY_SEPARATOR . $localeFile;
            if (file_exists($filename) && !is_writeable($filename)) {
                $unwriteableFiles[] = $localeFile;
            }
        }

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('locale', $locale);
        $templateMgr->assign('errors', TranslatorAction::testLocale($locale, MASTER_LOCALE));
        $templateMgr->assign('emailErrors', TranslatorAction::testEmails($locale, MASTER_LOCALE));
        $templateMgr->assign('localeFiles', TranslatorAction::getLocaleFiles($locale));
        if(!empty($unwriteableFiles)) {
            $templateMgr->assign('error', true);
            $templateMgr->assign('unwriteableFiles', $unwriteableFiles);
        }
        $templateMgr->display($plugin->getTemplatePath() . 'errors.tpl');
    }

    /**
     * Export the locale files to the browser as a tarball.
     * Requires tar (configured in config.inc.php) for operation.
     */
    public function export($args) {
        $this->validate();
        $plugin = $this->plugin;;
        $this->setupTemplate();

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) Request::redirect(null, null, 'index');

        TranslatorAction::export($locale);
    }

    /**
     * Save changes to a locale.
     * @param $args array
     */
    public function saveLocaleChanges($args) {
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate();

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) Request::redirect(null, null, 'index');

        $localeFiles = TranslatorAction::getLocaleFiles($locale);

        $changesByFile = array();

        // Arrange the list of changes to save into an array by file.
        // [SECURITY FIX] Save 'stack' (data array string) casting ke array
        $stack = (array) Request::getUserVar('stack');
        
        while (!empty($stack)) {
            // [SECURITY FIX] Amankan elemen array dengan trim() saat diekstrak
            // [PHP 8.1 FIX] Cast ke string sebelum trim
            $filename = trim((string) array_shift($stack));
            $key = trim((string) array_shift($stack));
            $value = trim((string) array_shift($stack));
            
            if (in_array($filename, $localeFiles)) {
                // $value sudah di-trim sebelum masuk ke correctCr
                $changesByFile[$filename][$key] = $this->correctCr($value);
            }
        }

        // Save the changes file by file.
        import('lib.pkp.classes.file.EditableLocaleFile');
        foreach ($changesByFile as $filename => $changes) {
            $file = new EditableLocaleFile($locale, $filename);
            foreach ($changes as $key => $value) {
                if (empty($value)) continue;
                if (!$file->update($key, $value)) {
                    $file->insert($key, $value);
                }
            }
            $file->write();

            unset($nodes);
            unset($dom);
            unset($file);
        }

        // Deal with key removals
        // [SECURITY FIX] Save 'deleteKey' Casting ke array.
        $deleteKeys = (array) Request::getUserVar('deleteKey');
        
        if (!empty($deleteKeys)) {
            // if (!is_array($deleteKeys)) $deleteKeys = array($deleteKeys); 
            
            foreach ($deleteKeys as $deleteKey) { // FIXME Optimize!
                // [SECURITY FIX] Save elemen array (string key) dengan trim()
                $safeDeleteKey = trim((string) $deleteKey);
                
                // Gunakan $safeDeleteKey yang sudah bersih
                list($filename, $key) = explode('/', $safeDeleteKey, 2); 
                
                $filename = urldecode(urldecode($filename));
                if (!in_array($filename, $localeFiles)) continue;
                $file = new EditableLocaleFile($locale, $filename);
                
                // Kunci $key yang diekstrak juga sebaiknya dibersihkan
                $safeKey = trim((string) $key);
                
                $file->delete($safeKey);
                $file->write();
                unset($file);
            }
        }

        // Deal with email removals
        import('lib.pkp.classes.file.EditableEmailFile');
        // [SECURITY FIX] Save 'deleteEmail' Casting ke array sudah ada.
        $deleteEmails = (array) Request::getUserVar('deleteEmail');
        
        if (!empty($deleteEmails)) {
            $file = new EditableEmailFile($locale, $this->getEmailTemplateFilename($locale));
            
            foreach ($deleteEmails as $key) {
                // [SECURITY FIX] Save elemen array (string key) dengan trim()
                $safeKey = trim((string) $key);
                $file->delete($safeKey);
            }
            $file->write();
            unset($file);
        }

        // [SECURITY FIX] Save 'redirectUrl' (string URL) dengan trim()
        $redirectUrl = trim((string) Request::getUserVar('redirectUrl'));
        Request::redirectUrl($redirectUrl);;
    }

    /**
     * Download a locale file.
     * @param $args array
     */
    public function downloadLocaleFile($args) {
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate();

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) Request::redirect(null, null, 'index');

        $filename = urldecode(urldecode(array_shift($args)));
        if (!TranslatorAction::isLocaleFile($locale, $filename)) {
            Request::redirect(null, null, 'edit', $locale);
        }

        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Cache-Control: private');
        readfile($filename);
    }

    /**
     * Edit a locale file.
     * @param $args array
     */
    public function editLocaleFile($args) {
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate();

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) Request::redirect(null, null, 'index');

        $filename = urldecode(urldecode(array_shift($args)));
        if (!TranslatorAction::isLocaleFile($locale, $filename)) {
            Request::redirect(null, null, 'edit', $locale);
        }

        $templateMgr = TemplateManager::getManager();
        if(!is_writeable(Core::getBaseDir() . DIRECTORY_SEPARATOR . $filename)) {
            $templateMgr->assign('error', true);
        }


        import('lib.pkp.classes.file.EditableLocaleFile');
        $localeContentsRangeInfo = Handler::getRangeInfo('localeContents');
        $localeContents = EditableLocaleFile::load($filename);

        // Handle a search, if one was executed.
        // [SECURITY FIX] Save 'searchKey' (string teks pencarian) dengan trim()
        $searchKey = trim((string) Request::getUserVar('searchKey')); 
        
        $found = false;
        $index = 0;
        $pageIndex = 0;
        
        // Menggunakan variabel $searchKey yang sudah bersih
        if (!empty($searchKey)) foreach ($localeContents as $key => $value) {
            if ($index % $localeContentsRangeInfo->getCount() == 0) $pageIndex++;
            if ($key == $searchKey) {
                $found = true;
                break;
            }
            $index++;
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
     * @param $args array
     */
    public function editMiscFile($args) {
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate();

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) Request::redirect(null, null, 'index');

        $filename = urldecode(urldecode(array_shift($args)));
        if (!TranslatorAction::isLocaleFile($locale, $filename)) {
            Request::redirect(null, null, 'edit', $locale);
        }
        $referenceFilename = TranslatorAction::determineReferenceFilename($locale, $filename);
        $templateMgr = TemplateManager::getManager();

        $templateMgr->assign('locale', $locale);
        $templateMgr->assign('filename', $filename);
        $templateMgr->assign('referenceContents', file_get_contents($referenceFilename));
        $templateMgr->assign('translationContents', file_exists($filename)?file_get_contents($filename):'');
        $templateMgr->display($plugin->getTemplatePath() . 'editMiscFile.tpl');
    }

    /**
     * Save a locale file.
     * @param $args array
     */
    public function saveLocaleFile($args) {
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate();

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) Request::redirect(null, null, 'index');

        $filename = urldecode(urldecode(array_shift($args)));
        if (!TranslatorAction::isLocaleFile($locale, $filename)) {
            Request::redirect(null, null, 'edit', $locale);
        }

        import('lib.pkp.classes.file.EditableLocaleFile');
        
        // [SECURITY FIX] Save 'changes' (data array string)
        $changes = (array) Request::getUserVar('changes');
        
        $file = new EditableLocaleFile($locale, $filename);

        while (!empty($changes)) {
            // [SECURITY FIX] Save array $key $value with trim() saat diekstrak
            $key = trim((string) array_shift($changes)); 
            $value = $this->correctCr(trim((string) array_shift($changes)));
            
            if (!$file->update($key, $value)) {
                $file->insert($key, $value);
            }
        }
        $file->write();
        
        // [SECURITY FIX] Amankan 'redirectUrl' (string URL) dengan trim()
        $redirectUrl = trim((string) Request::getUserVar('redirectUrl'));
        Request::redirectUrl($redirectUrl);
    }

    /**
     * Delete a locale key.
     * @param $args array
     */
    public function deleteLocaleKey($args) {
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate();

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) Request::redirect(null, null, 'index');

        $filename = urldecode(urldecode(array_shift($args)));
        if (!TranslatorAction::isLocaleFile($locale, $filename)) {
            Request::redirect(null, null, 'edit', $locale);
        }

        // [SECURITY FIX] Save 'changes' (data array string) with array
        $changes = (array) Request::getUserVar('changes');
        $file = new EditableLocaleFile($locale, $filename);

        if ($file->delete(array_shift($args))) $file->write();
        Request::redirect(null, null, 'editLocaleFile', array($locale, urlencode(urlencode($filename))));
    }

    /**
     * Save a miscellaneous locale file.
     * @param $args array
     */
    public function saveMiscFile($args) {
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate();

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) Request::redirect(null, null, 'index');

        $filename = urldecode(urldecode(array_shift($args)));
        if (!TranslatorAction::isLocaleFile($locale, $filename)) {
            Request::redirect(null, null, 'edit', $locale);
        }

        $fp = fopen($filename, 'w+'); // FIXME error handling
        if ($fp) {
            // [SECURITY FIX] 'translationContents' (data string) with trim()
            $rawContents = (string) trim((string) Request::getUserVar('translationContents'));
            
            $contents = $this->correctCr($rawContents);
            
            fwrite ($fp, $contents);
            fclose($fp);
        }
        Request::redirect(null, null, 'edit', $locale);
    }

    /**
     * Edit an email template.
     * @param $args array
     */
    public function editEmail($args) {
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate();

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) Request::redirect(null, null, 'index');

        $emails = TranslatorAction::getEmailTemplates($locale);
        $referenceEmails = TranslatorAction::getEmailTemplates(MASTER_LOCALE);
        $emailKey = array_shift($args);

        if (!in_array($emailKey, array_keys($referenceEmails)) && !in_array($emailKey, array_keys($emails))) Request::redirect(null, null, 'index');

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('emailKey', $emailKey);
        $templateMgr->assign('locale', $locale);
        $templateMgr->assign('email', isset($emails[$emailKey])?$emails[$emailKey]:'');

        // [SECURITY FIX] Save 'returnToCheck' (flag boolean) with (int) trim()
        $returnToCheckFlag = (int) trim((string) Request::getUserVar('returnToCheck'));
        // Assign nilai yang sudah diamankan
        $templateMgr->assign('returnToCheck', $returnToCheckFlag);

        $templateMgr->assign('referenceEmail', isset($referenceEmails[$emailKey])?$referenceEmails[$emailKey]:'');
        $templateMgr->display($plugin->getTemplatePath() . 'editEmail.tpl');
    }

    /**
     * Create a locale file.
     * @param $args array
     */
    public function createFile($args) {
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate();

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) Request::redirect(null, null, 'index');

        $filename = urldecode(urldecode(array_shift($args)));
        if (!TranslatorAction::isLocaleFile($locale, $filename)) {
            Request::redirect(null, null, 'edit', $locale);
        }

        import('lib.pkp.classes.file.FileManager');
        $fileManager = new FileManager();
        $fileManager->copyFile(TranslatorAction::determineReferenceFilename($locale, $filename), $filename);
        $localeKeys = LocaleFile::load($filename);
        import('lib.pkp.classes.file.EditableLocaleFile');
        $file = new EditableLocaleFile($locale, $filename);
        // remove default translations from keys
        foreach (array_keys($localeKeys) as $key) {
            $file->update($key, '');
        }
        $file->write();
        
        // [SECURITY FIX] Amankan 'redirectUrl' (string URL) dengan trim()
        $redirectUrl = trim((string) Request::getUserVar('redirectUrl'));
        Request::redirectUrl($redirectUrl);
    }

    /**
     * Delete an email template.
     * @param $args array
     */
    public function deleteEmail($args) {
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate();

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) Request::redirect(null, null, 'index');

        $emails = TranslatorAction::getEmailTemplates($locale);
        $referenceEmails = TranslatorAction::getEmailTemplates(MASTER_LOCALE);
        $emailKey = array_shift($args);

        if (!in_array($emailKey, array_keys($emails))) Request::redirect(null, null, 'index');

        import('lib.pkp.classes.file.EditableEmailFile');
        $file = new EditableEmailFile($locale, $this->getEmailTemplateFilename($locale));

        // [SECURITY FIX] Amankan 'subject' (string teks) dengan trim()
        $subject = trim((string) Request::getUserVar('subject'));
        
        // [SECURITY FIX] Amankan 'body' (string teks) dengan trim()
        $body = trim((string) Request::getUserVar('body'));
        
        // [SECURITY FIX] Amankan 'description' (string teks) dengan trim()
        $description = trim((string) Request::getUserVar('description'));
        
        if ($file->delete($emailKey)) $file->write();
        Request::redirect(null, null, 'edit', $locale, null, 'emails');
    }

    /**
     * Save an email template.
     * @param $args array
     */
    public function saveEmail($args) {
        $this->validate();
        $plugin = $this->plugin;
        $this->setupTemplate();

        $locale = array_shift($args);
        if (!AppLocale::isLocaleValid($locale)) Request::redirect(null, null, 'index');

        $emails = TranslatorAction::getEmailTemplates($locale);
        $referenceEmails = TranslatorAction::getEmailTemplates(MASTER_LOCALE);
        $emailKey = array_shift($args);
        $targetFilename = str_replace(MASTER_LOCALE, $locale, $referenceEmails[$emailKey]['templateDataFile']); // FIXME: Ugly.

        if (!in_array($emailKey, array_keys($emails))) {
            // If it's not a reference or translation email, bail.
            if (!in_array($emailKey, array_keys($referenceEmails))) Request::redirect(null, null, 'index');

            // If it's a reference email but not a translated one,
            // create a blank file. FIXME: This is ugly.
            if (!file_exists($targetFilename)) {
                $dir = dirname($targetFilename);
                if (!file_exists($dir)) mkdir($dir);
                file_put_contents($targetFilename, '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE email_texts SYSTEM "../../../../../lib/pkp/dtd/emailTemplateData.dtd">
<email_texts locale="' . $locale . '">
</email_texts>');
            }
        }

        import('lib.pkp.classes.file.EditableEmailFile');
        $file = new EditableEmailFile($locale, $targetFilename);

        // [SECURITY FIX] Amankan 'subject' (string teks) dengan trim()
        $subject = $this->correctCr(trim((string) Request::getUserVar('subject'))); 
        
        // [SECURITY FIX] Amankan 'body' (string teks) dengan trim()
        $body = $this->correctCr(trim((string) Request::getUserVar('body')));
        
        // [SECURITY FIX] Amankan 'description' (string teks) dengan trim()
        $description = $this->correctCr(trim((string) Request::getUserVar('description')));

        if (!$file->update($emailKey, $subject, $body, $description))
            $file->insert($emailKey, $subject, $body, $description);

        $file->write();
        // [SECURITY FIX] Save 'returnToCheck' (flag boolean) with (int) trim()
        if ((int) trim((string) Request::getUserVar('returnToCheck')) == 1) {
            Request::redirect(null, null, 'check', $locale);
        } else {
            Request::redirect(null, null, 'edit', $locale);
        }
    }

    /**
     * Correct CRLF to LF in a string.
     * @param $value string
     * @return string
     */
    public function correctCr($value) {
        return str_replace("\r\n", "\n", $value);
    }
}

?>