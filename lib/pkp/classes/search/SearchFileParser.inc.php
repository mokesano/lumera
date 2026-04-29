<?php
declare(strict_types=1);

/**
 * @defgroup search
 */

/**
 * @file classes/search/SearchFileParser.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SearchFileParser
 * @ingroup search
 *
 * @brief Abstract class to extract search text from a given file.
 */

class SearchFileParser {

    /** @var $filePath string the complete path to the file */
    public $filePath;

    /** @var $fp resource file handle */
    public $fp;

    /**
     * Constructor.
     * @param $filePath string
     */
    public function __construct($filePath) {
        $this->filePath = $filePath;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SearchFileParser($filePath) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::SearchFileParser(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct($filePath);
    }

    /**
     * Return the path to the file.
     * @return string
     */
    public function getFilePath() {
        return $this->filePath;
    }

    /**
     * Change the file path.
     * @param $filePath string
     */
    public function setFilePath($filePath) {
        $this->filePath = $filePath;
    }

    /**
     * Open the file.
     * @return boolean
     */
    public function open() {
        if (!$this->filePath) return false;
        $this->fp = @fopen($this->filePath, 'rb');
        return $this->fp ? true : false;
    }

    /**
     * Close the file.
     */
    public function close() {
        if ($this->fp) {
            fclose($this->fp);
            $this->fp = null;
        }
    }

    /**
     * Read and return the next block/line of text.
     * @return string|false (false on EOF)
     */
    public function read() {
        if (!$this->fp || feof($this->fp)) {
            return false;
        }
        return $this->doRead();
    }

    /**
     * Read from the file pointer.
     * @return string
     */
    public function doRead() {
        return fgets($this->fp, 4096);
    }


    //
    // Static methods
    //

    /**
     * Create a text parser for a file.
     * (Modernisasi: Menghapus & return dan & parameter)
     * @param $file ArticleFile|PaperFile
     * @return SearchFileParser
     */
    public static function fromFile($file) {
        $returner = SearchFileParser::fromFileType($file->getFileType(), $file->getFilePath());
        return $returner;
    }

    /**
     * Create a text parser for a file based on type.
     * (Modernisasi: Menghapus & return)
     * @param $type string
     * @param $path string
     * @return SearchFileParser
     */
    public static function fromFileType($type, $path) {
        switch ($type) {
            case 'text/plain':
                $returner = new SearchFileParser($path);
                break;
            case 'text/html':
            case 'text/xml':
            case 'application/xhtml':
            case 'application/xml':
                import('classes.search.SearchHTMLParser');
                $returner = new SearchHTMLParser($path);
                break;
            default:
                import('classes.search.SearchHelperParser');
                $returner = new SearchHelperParser($type, $path);
        }
        return $returner;
    }
}

?>