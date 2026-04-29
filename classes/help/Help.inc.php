<?php
declare(strict_types=1);

/**
 * @file classes/help/Help.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Help
 * @ingroup help
 *
 * @brief Provides methods for translating help topic keys to their respected topic
 * help ids.
 * * MODERNIZED FOR WIZDAM FORK
 */

import('lib.pkp.classes.help.PKPHelp');

class Help extends PKPHelp {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        import('classes.help.OJSHelpMappingFile');
        $mainMappingFile = new OJSHelpMappingFile();
        $this->addMappingFile($mainMappingFile);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Help() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::Help(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }
}

?>