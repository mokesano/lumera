<?php
declare(strict_types=1);

/**
 * @file classes/search/SearchHelperParser.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SearchHelperParser
 * @ingroup search
 *
 * @brief Class to extract text from a file using an external helper program.
 */

import('lib.pkp.classes.search.SearchFileParser');

class SearchHelperParser extends SearchFileParser {

    /** @var string Type should match an index[$type] setting in the "search" section of config.inc.php */
    public $type;

    /**
     * Constructor.
     * @param $type string
     * @param $filePath string
     */
    public function __construct($type, $filePath) {
        parent::__construct($filePath);
        $this->type = $type;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SearchHelperParser($type, $filePath) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::SearchHelperParser(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct($type, $filePath);
    }

    /**
     * Open the file using external program.
     * @return boolean
     */
    public function open() {
        // Mengambil command dari config.inc.php
        $prog = Config::getVar('search', 'index[' . $this->type . ']');

        if (!empty($prog)) {
            // SECURITY UPDATE:
            // Menggunakan escapeshellarg() lebih aman daripada escapeshellcmd() untuk nama file.
            // Ini membungkus path dengan 'single quotes' dan meng-escape quote di dalamnya.
            // Pastikan config.inc.php menggunakan %s (misal: "/usr/bin/pdftotext %s -")
            $exec = sprintf($prog, escapeshellarg($this->getFilePath()));
            
            $this->fp = @popen($exec, 'r');
            return $this->fp ? true : false;
        }

        return false;
    }

    /**
     * Close the file.
     */
    public function close() {
        if (is_resource($this->fp)) {
            pclose($this->fp);
            $this->fp = null;
        }
    }
}

?>