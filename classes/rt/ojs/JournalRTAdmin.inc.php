<?php
declare(strict_types=1);

/**
 * @file classes/rt/ojs/JournalRTAdmin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class JournalRTAdmin
 * @ingroup rt_ojs
 *
 * @brief OJS-specific Reading Tools administration interface.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.rt.RTAdmin');
import('classes.rt.ojs.RTDAO');

define('RT_DIRECTORY', 'rt');
define('DEFAULT_RT_LOCALE', 'en_US');

class JournalRTAdmin extends RTAdmin {

    /** @var int */
    public $journalId;

    /** @var RTDAO */
    public $dao;

    /**
     * Constructor
     * @param int $journalId
     */
    public function __construct($journalId) {
        $this->journalId = (int) $journalId;
        $this->dao = DAORegistry::getDAO('RTDAO');
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function JournalRTAdmin($journalId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::JournalRTAdmin(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct($journalId);
    }

    /**
     * Restore versions from XML
     * @param bool $deleteBeforeLoad
     */
    public function restoreVersions($deleteBeforeLoad = true) {
        import('lib.pkp.classes.rt.RTXMLParser');
        $parser = new RTXMLParser();

        if ($deleteBeforeLoad) {
            $this->dao->deleteVersionsByJournalId($this->journalId);
        }

        $localeFilesLocation = RT_DIRECTORY . DIRECTORY_SEPARATOR . AppLocale::getLocale();
        if (!file_exists($localeFilesLocation)) {
            // If no reading tools exist for the given locale, use the default set
            $localeFilesLocation = RT_DIRECTORY . DIRECTORY_SEPARATOR . DEFAULT_RT_LOCALE;
            $overrideLocale = true;
        } else {
            $overrideLocale = false;
        }

        $versions = $parser->parseAll($localeFilesLocation);
        foreach ($versions as $version) {
            if ($overrideLocale) {
                $version->setLocale(AppLocale::getLocale());
            }
            $this->dao->insertVersion($this->journalId, $version);
        }
    }

    /**
     * Import version from file
     * @param string $filename
     */
    public function importVersion($filename) {
        import('lib.pkp.classes.rt.RTXMLParser');
        $parser = new RTXMLParser();

        $version = $parser->parse($filename);
        $this->dao->insertVersion($this->journalId, $version);
    }
}
?>