<?php
declare(strict_types=1);

/**
 * @file classes/search/SearchHTMLParser.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SearchHTMLParser
 * @ingroup search
 *
 * @brief Class to extract text from an HTML file.
 */

import('lib.pkp.classes.search.SearchFileParser');
import('lib.pkp.classes.core.PKPString');

class SearchHTMLParser extends SearchFileParser {

    /**
     * Constructor.
     * @param $filePath string
     */
    public function __construct($filePath) {
        parent::__construct($filePath);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SearchHTMLParser($filePath) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::SearchHTMLParser(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct($filePath);
    }

    /**
     * Read and return the next block/line of text.
     * @return string|false
     */
    public function doRead() {
        // CRITICAL FIX PHP 8: fgetss() dihapus di PHP 8.0.
        // Kita ganti dengan fgets() biasa, lalu strip_tags() manual.
        
        $line = fgets($this->fp, 4096);

        // Handle EOF atau error baca
        if ($line === false) {
            return false;
        }

        // strip HTML tags from the read line
        // (Setara dengan fgetss tanpa parameter allowed_tags)
        $line = strip_tags($line);

        // convert HTML entities to valid UTF-8 characters
        $line = PKPString::html2utf($line);

        return $line;
    }
}

?>