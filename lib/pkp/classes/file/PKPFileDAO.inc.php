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
 * objects and their decendents.
 */

define('INLINEABLE_TYPES_FILE', Core::getBaseDir() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'pkp' . DIRECTORY_SEPARATOR . 'registry' . DIRECTORY_SEPARATOR . 'inlineTypes.txt');

class PKPFileDAO extends DAO {

    /**
     * @var array|null a private list of MIME types that can be shown inline in the browser
     */
    protected ?array $_inlineableTypes = null;

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
        $this->__construct();
    }

    //
    // Public methods
    //
    
    /**
     * Check whether a file may be displayed inline.
     * @param PKPFile $file
     * @return boolean
     */
    public function isInlineable($file) {
        if ($this->_inlineableTypes === null) {
            $this->_inlineableTypes = [];

            $lines = @file(INLINEABLE_TYPES_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (is_array($lines)) {
                foreach ($lines as $line) {
                    $line = trim((string) $line);

                    if ($line !== '' && !str_starts_with($line, '#')) {
                        $this->_inlineableTypes[] = $line;
                    }
                }
            }
        }

        return in_array((string) $file->getFileType(), $this->_inlineableTypes, true);
    }

}
?>