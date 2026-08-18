<?php
declare(strict_types=1);

/**
 * @file classes/mail/EmailTemplateDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EmailTemplateDAO
 * @ingroup mail
 * @see EmailTemplate
 *
 * @brief Operations for retrieving and modifying Email Template objects.
 */

import('lib.pkp.classes.mail.PKPEmailTemplateDAO');
import('lib.pkp.classes.mail.EmailTemplate');

class EmailTemplateDAO extends PKPEmailTemplateDAO {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function EmailTemplateDAO() {
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
     * Retrieve a base email template by key.
     * @param string $emailKey
     * @param int|string $assocType (or journalId in legacy)
     * @param int|null $assocId
     * @return BaseEmailTemplate|null
     */
    public function getBaseEmailTemplate($emailKey, $assocType, $assocId = null) {
        if ($assocId === null && is_numeric($assocType)) {
            $journalId = (int) $assocType;
        } else {
            $journalId = (int) $assocId;
        }
        return parent::getBaseEmailTemplate($emailKey, ASSOC_TYPE_JOURNAL, $journalId);
    }

    /**
     * Retrieve localized email template by key.
     * @param string $emailKey
     * @param int|string $assocType (or journalId in legacy)
     * @param int|null $assocId
     * @return LocaleEmailTemplate|null
     */
    public function getLocaleEmailTemplate($emailKey, $assocType, $assocId = null) {
        if ($assocId === null && is_numeric($assocType)) {
            $journalId = (int) $assocType;
        } else {
            $journalId = (int) $assocId;
        }
        return parent::getLocaleEmailTemplate($emailKey, ASSOC_TYPE_JOURNAL, $journalId);
    }

    /**
     * Retrieve an email template by key.
     * @param string $emailKey
     * @param string $locale
     * @param int|string $assocType (or journalId in legacy)
     * @param int|null $assocId
     * @return EmailTemplate|null
     */
    public function getEmailTemplate($emailKey, $locale, $assocType, $assocId = null) {
        if ($assocId === null && is_numeric($assocType)) {
            $journalId = (int) $assocType;
        } else {
            $journalId = (int) $assocId;
        }
        return parent::getEmailTemplate($emailKey, $locale, ASSOC_TYPE_JOURNAL, $journalId);
    }

    /**
     * Delete an email template by key.
     * @param string $emailKey
     * @param int|string $assocType (or journalId in legacy)
     * @param int|null $assocId
     */
    public function deleteEmailTemplateByKey($emailKey, $assocType, $assocId = null) {
        // If $assocId is null, then $assocType is actually $journalId (old way)
        if ($assocId === null) {
            $journalId = (int) $assocType;
        } else {
            $journalId = (int) $assocId;
        }
        
        return parent::deleteEmailTemplateByKey($emailKey, ASSOC_TYPE_JOURNAL, $journalId);
    }

    /**
     * Retrieve all email templates.
     * @param string $locale
     * @param int|string $assocType (or journalId in legacy)
     * @param int|null $assocId (or rangeInfo in legacy)
     * @param object|null $rangeInfo
     * @return array Email templates
     */
    public function getEmailTemplates($locale, $assocType, $assocId = null, $rangeInfo = null) {
        // Detect calling convention
        if (($assocId === null || is_object($assocId)) && is_numeric($assocType)) {
            // Old signature: getEmailTemplates($locale, $journalId, $rangeInfo)
            // where $assocType is actually $journalId (numeric)
            $journalId = (int) $assocType;
            // And $assocId might be $rangeInfo object
            if (is_object($assocId)) {
                $rangeInfo = $assocId;
            }
        } else {
            // New signature: getEmailTemplates($locale, $assocType, $assocId, $rangeInfo)
            $journalId = (int) $assocId;
        }
        
        return parent::getEmailTemplates($locale, ASSOC_TYPE_JOURNAL, $journalId, $rangeInfo);
    }

    /**
     * Delete all email templates for a specific journal.
     * @param int $journalId
     */
    public function deleteEmailTemplatesByJournal($journalId) {
        return parent::deleteEmailTemplatesByAssoc(ASSOC_TYPE_JOURNAL, (int) $journalId);
    }

    /**
     * Check if a template exists with the given email key for a journal.
     * @param string $emailKey
     * @param int|null $assocType
     * @param int|null $assocId
     * @return bool
     */
    public function templateExistsByKey($emailKey, $assocType = null, $assocId = null) {
        return parent::templateExistsByKey($emailKey, $assocType, $assocId);
    }

    /**
     * Check if a custom template exists with the given email key for a journal.
     * @param string $emailKey
     * @param int $assocType
     * @param int $assocId
     * @return bool
     */
    public function customTemplateExistsByKey($emailKey, $assocType, $assocId) {
        return parent::customTemplateExistsByKey($emailKey, ASSOC_TYPE_JOURNAL, (int) $assocId);
    }

    /**
     * [WIZDAM SELF-HEALING] Pastikan sebuah kunci template email SUDAH
     * terdaftar di database, untuk SEMUA locale terpasang di situs --
     * memasang otomatis dari registry/emailTemplates.xml +
     * locale/{locale}/emailTemplates.xml kalau BELUM ada.
     * @param string $emailKey
     * @return bool true kalau template (baru dipasang ATAU sudah ada)
     * siap dipakai untuk MINIMAL satu locale.
     */
    public function ensureEmailTemplateInstalled(string $emailKey): bool {
        if ($this->templateExistsByKey($emailKey)) {
            return true;
        }

        $this->installEmailTemplates($this->getMainEmailTemplatesFilename(), false, $emailKey, true);

        $request = Application::get()->getRequest();
        $site = $request->getSite();
        foreach ($site->getInstalledLocales() as $locale) {
            $dataFile = $this->getMainEmailTemplateDataFilename($locale);
            if ($dataFile && file_exists($dataFile)) {
                $this->installEmailTemplateData($dataFile, false, $emailKey);
            }
        }

        return $this->templateExistsByKey($emailKey);
    }

}
?>