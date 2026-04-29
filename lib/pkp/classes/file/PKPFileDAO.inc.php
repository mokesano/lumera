<?php
declare(strict_types=1);

/**
 * @file classes/file/PKPFileDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPFileDAO
 * @ingroup file
 * @see PKPFile
 *
 * @brief Abstract base class for retrieving and modifying PKPFile
 * objects and their decendents
 */

define('INLINEABLE_TYPES_FILE', Core::getBaseDir() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'pkp' . DIRECTORY_SEPARATOR . 'registry' . DIRECTORY_SEPARATOR . 'inlineTypes.txt');

class PKPFileDAO extends DAO {
    /**
     * @var array a private list of MIME types that can be shown inline
     * in the browser
     */
    protected $_inlineableTypes;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPFileDAO() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::PKPFileDAO(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    //
    // Public methods
    //
    
    /**
     * Check whether a file may be displayed inline.
     * @param $file PKPFile
     * @return boolean
     */
    public function isInlineable($file) {
        // Retrieve MIME types.
        if (!isset($this->_inlineableTypes)) {
            $this->_inlineableTypes = array();
            
            // SECURITY FIX: Replaced deprecated create_function with safe logic
            // Load file into array, ignore new lines
            $lines = file(INLINEABLE_TYPES_FILE, FILE_IGNORE_NEW_LINES);
            
            if ($lines !== false) {
                foreach ($lines as $line) {
                    $line = trim($line);
                    // Filter empty lines and comments (starting with #)
                    if (!empty($line) && $line[0] != '#') {
                        $this->_inlineableTypes[] = $line;
                    }
                }
            }
        }

        // Check the MIME type of the file.
        return in_array($file->getFileType(), $this->_inlineableTypes);
    }
}

?>