<?php
declare(strict_types=1);

/**
 * @file plugins/generic/translator/TranslatorAction.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TranslatorAction
 * @ingroup plugins_generic_translator
 *
 * @brief Perform various tasks related to translation.
 */

class TranslatorAction {
    
    /**
     * Export the locale files to the browser as a tarball.
     * Requires tar for operation (configured in config.inc.php).
     * @param string $locale
     */
    public static function export($locale) {
        // Construct the tar command
        $tarBinary = Config::getVar('cli', 'tar');
        if (empty($tarBinary) || !file_exists($tarBinary)) {
            // We can use fatalError() here as we already have a user
            // friendly way of dealing with the missing tar on the index page.
            fatalError('The tar binary must be configured in config.inc.php\'s cli section to use the export function of this plugin!');
        }
        $command = $tarBinary . ' cz';

        $localeFilesList = self::getLocaleFiles($locale);
        $miscFiles = self::getMiscLocaleFiles($locale);
        if (is_array($miscFiles)) {
            $localeFilesList = array_merge($localeFilesList, $miscFiles);
        }
        
        /** @var EmailTemplateDAO $emailTemplateDao */
        $emailTemplateDao = DAORegistry::getDAO('EmailTemplateDAO');
        $localeFilesList[] = $emailTemplateDao->getMainEmailTemplateDataFilename($locale);
        
        // Include email files
        foreach (array_values(self::getEmailFileMap($locale)) as $emailFile) {
            $localeFilesList[] = $emailFile;
        }

        // Include locale files (main file and plugin files)
        foreach ($localeFilesList as $file) {
            if (file_exists($file)) {
                $command .= ' ' . escapeshellarg($file);
            }
        }

        header('Content-Type: application/x-gtar');
        header('Content-Disposition: attachment; filename="' . $locale . '.tar.gz"');
        header('Cache-Control: private'); // Workarounds for IE weirdness
        passthru($command);
    }

    /**
     * Get a list of locale files for the given locale.
     * @param string $locale
     * @return array
     */
    public static function getLocaleFiles($locale) {
        if (!AppLocale::isLocaleValid($locale)) {
            return [];
        }

        $localeFiles = AppLocale::getFilenameComponentMap($locale);
        $plugins = PluginRegistry::loadAllPlugins();
        
        foreach ($plugins as $plugin) {
            $localeFile = $plugin->getLocaleFilename($locale);
            if (!empty($localeFile)) {
                if (is_array($localeFile)) {
                    $localeFiles = array_merge($localeFiles, $localeFile);
                } elseif (is_scalar($localeFile)) {
                    $localeFiles[] = $localeFile;
                }
            }
        }
        return $localeFiles;
    }

    /**
     * Get a list of miscellaneous locale files for the given locale.
     * @param string $locale
     * @return array
     */
    public static function getMiscLocaleFiles($locale) {
        /** @var CountryDAO $countryDao */
        $countryDao = DAORegistry::getDAO('CountryDAO');
        /** @var CurrencyDAO $currencyDao */
        $currencyDao = DAORegistry::getDAO('CurrencyDAO');
        /** @var LanguageDAO $languageDao */
        $languageDao = DAORegistry::getDAO('LanguageDAO');
        
        return [
            $countryDao->getFilename($locale),
            $currencyDao->getCurrencyFilename($locale),
            $languageDao->getLanguageFilename($locale)
        ];
    }

    /**
     * Get a map of email template files to email data files for the given locale.
     * @param string $locale
     * @return array
     */
    public static function getEmailFileMap($locale) {
        /** @var EmailTemplateDAO $emailTemplateDao */
        $emailTemplateDao = DAORegistry::getDAO('EmailTemplateDAO');
        $files = [
            $emailTemplateDao->getMainEmailTemplatesFilename() => $emailTemplateDao->getMainEmailTemplateDataFilename($locale)
        ];
        
        $categories = PluginRegistry::getCategories();
        foreach ($categories as $category) {
            $plugins = PluginRegistry::loadCategory($category);
            if (is_array($plugins)) {
                foreach ($plugins as $plugin) {
                    $templatesFile = $plugin->getInstallEmailTemplatesFile();
                    if ($templatesFile) {
                        $files[$templatesFile] = str_replace('{$installedLocale}', $locale, $plugin->getInstallEmailTemplateDataFile());
                    }
                }
            }
        }
        return $files;
    }

    /**
     * Get all email templates for the given locale.
     * @param string $locale
     * @return array
     */
    public static function getEmailTemplates($locale) {
        $files = self::getEmailFileMap($locale);
        $returner = [];
        
        foreach ($files as $templateFile => $templateDataFile) {
            $xmlParser = new PKPXMLParser();
            $data = null;
            
            if (file_exists($templateDataFile)) {
                $data = $xmlParser->parse($templateDataFile);
            }
            
            if ($data) {
                foreach ($data->getChildren() as $emailNode) {
                    $returner[$emailNode->getAttribute('key')] = [
                        'subject' => $emailNode->getChildValue('subject'),
                        'body' => $emailNode->getChildValue('body'),
                        'description' => $emailNode->getChildValue('description'),
                        'templateFile' => $templateFile,
                        'templateDataFile' => $templateDataFile
                    ];
                }
            }
        }
        return $returner;
    }

    /**
     * Determine if the given filename is a locale file for the given locale.
     * @param string $locale
     * @param string $filename
     * @return bool
     */
    public static function isLocaleFile($locale, $filename) {
        if (in_array($filename, self::getLocaleFiles($locale), true)) {
            return true;
        }
        if (in_array($filename, self::getMiscLocaleFiles($locale), true)) {
            return true;
        }
        
        /** @var EmailTemplateDAO $emailTemplateDao */
        $emailTemplateDao = DAORegistry::getDAO('EmailTemplateDAO');
        if ($filename === $emailTemplateDao->getMainEmailTemplateDataFilename($locale)) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine the reference filename for the given locale and filename.
     * @param string $locale
     * @param string $filename
     * @return string
     */
    public static function determineReferenceFilename($locale, $filename) {
        // FIXME: This is ugly.
        return str_replace($locale, MASTER_LOCALE, $filename);
    }

    /**
     * Test all locale files for the supplied locale against the supplied
     * reference locale, returning an array of errors.
     * @param string $locale
     * @param string $referenceLocale
     * @return array
     */
    public static function testLocale($locale, $referenceLocale) {
        $localeFileNames = AppLocale::getFilenameComponentMap($locale);
        $errors = [];
        
        foreach ($localeFileNames as $localeFileName) {
            $referenceLocaleFileName = str_replace($locale, $referenceLocale, $localeFileName);
            $localeFile = new LocaleFile($locale, $localeFileName);
            $referenceLocaleFile = new LocaleFile($referenceLocale, $referenceLocaleFileName);
            
            $errors = array_merge_recursive($errors, $localeFile->testLocale($referenceLocaleFile));
        }

        $pluginsReferenceLocaleFilenamesList = [];
        $plugins = PluginRegistry::loadAllPlugins();
        
        foreach ($plugins as $plugin) {
            $referenceLocaleFilenames = $plugin->getLocaleFilename($referenceLocale);
            
            if ($referenceLocaleFilenames && !in_array($referenceLocaleFilenames, $pluginsReferenceLocaleFilenamesList, true)) {
                $pluginsReferenceLocaleFilenamesList[] = $referenceLocaleFilenames;
                
                $refFiles = is_scalar($referenceLocaleFilenames) ? [$referenceLocaleFilenames] : (array) $referenceLocaleFilenames;
                $locFiles = is_scalar($plugin->getLocaleFilename($locale)) ? [$plugin->getLocaleFilename($locale)] : (array) $plugin->getLocaleFilename($locale);
                
                // Safety check to ensure counts match before iterating
                if (count($locFiles) === count($refFiles)) {
                    foreach ($refFiles as $index => $referenceLocaleFilename) {
                        if (isset($locFiles[$index])) {
                            $localeFile = new LocaleFile($locale, $locFiles[$index]);
                            $referenceLocaleFile = new LocaleFile($referenceLocale, $referenceLocaleFilename);
                            $errors = array_merge_recursive($errors, $localeFile->testLocale($referenceLocaleFile));
                        }
                    }
                }
            }
        }
        return $errors;
    }

    /**
     * Test the emails in the supplied locale against those in the supplied
     * reference locale.
     * @param string $locale
     * @param string $referenceLocale
     * @return array List of errors
     */
    public static function testEmails($locale, $referenceLocale) {
        $errors = [];
        $matchedReferenceEmails = [];

        $emails = self::getEmailTemplates($locale);
        $referenceEmails = self::getEmailTemplates($referenceLocale);

        // Pass 1: For all translated emails, check that they match against reference translations.
        foreach ($emails as $emailKey => $email) {
            // Check if a matching reference email was found.
            if (!isset($referenceEmails[$emailKey])) {
                $errors[EMAIL_ERROR_EXTRA_EMAIL][] = ['key' => $emailKey];
                continue;
            }

            // We've successfully found a matching reference email. Compare it against the translation.
            $bodyParams = AppLocale::getParameterNames($email['body']);
            $referenceBodyParams = AppLocale::getParameterNames($referenceEmails[$emailKey]['body']);
            $diff = array_diff($bodyParams, $referenceBodyParams);
            
            if (!empty($diff)) {
                $errors[EMAIL_ERROR_DIFFERING_PARAMS][] = [
                    'key' => $emailKey,
                    'mismatch' => $diff
                ];
            }

            $subjectParams = AppLocale::getParameterNames($email['subject']);
            $referenceSubjectParams = AppLocale::getParameterNames($referenceEmails[$emailKey]['subject']);
            $diffSubject = array_diff($subjectParams, $referenceSubjectParams);
            
            if (!empty($diffSubject)) {
                $errors[EMAIL_ERROR_DIFFERING_PARAMS][] = [
                    'key' => $emailKey,
                    'mismatch' => $diffSubject
                ];
            }

            $matchedReferenceEmails[] = $emailKey;
        }

        // Pass 2: Make sure that there are no missing translations.
        foreach ($referenceEmails as $emailKey => $email) {
            if (!isset($emails[$emailKey])) {
                $errors[EMAIL_ERROR_MISSING_EMAIL][] = ['key' => $emailKey];
            }
        }

        return $errors;
    }
    
}
?>