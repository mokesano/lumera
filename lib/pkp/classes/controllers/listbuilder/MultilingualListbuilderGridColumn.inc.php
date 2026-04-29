<?php
declare(strict_types=1);

/**
 * @file classes/controllers/listbuilder/MultilingualListbuilderGridColumn.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MultilingualListbuilderGridColumn
 * @ingroup controllers_listbuilder
 *
 * @brief Represents a multilingual text column within a listbuilder.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.controllers.listbuilder.ListbuilderGridColumn');

class MultilingualListbuilderGridColumn extends ListbuilderGridColumn {
    
    /**
     * Constructor
     * @param ListbuilderHandler $listbuilder
     * @param string $id
     * @param string|null $title
     * @param string|null $titleTranslated
     * @param string|null $template
     * @param object|null $cellProvider
     * @param array|null $availableLocales
     * @param array $flags
     */
    public function __construct($listbuilder, $id = '', $title = null,
            $titleTranslated = null, $template = null, $cellProvider = null,
            $availableLocales = null, $flags = []) {

        // [WIZDAM] Strict Object Validation & Logic Check (Replacing assert)
        if (!is_object($listbuilder) || !method_exists($listbuilder, 'getSourceType')) {
            fatalError('Invalid Listbuilder object passed to MultilingualListbuilderGridColumn.');
        }

        // Make sure this is a text input
        if ($listbuilder->getSourceType() !== LISTBUILDER_SOURCE_TYPE_TEXT) {
            fatalError('MultilingualListbuilderGridColumn can only be used with LISTBUILDER_SOURCE_TYPE_TEXT.');
        }

        // Provide a default set of available locales if not specified
        if ($availableLocales === null) {
            $availableLocales = AppLocale::getSupportedFormLocales();
        }

        // Set some flags for multilingual support
        $flags['multilingual'] = true; // This is a multilingual column.
        $flags['availableLocales'] = $availableLocales; // Provide available locales

        parent::__construct($listbuilder, $id, $title, $titleTranslated, $template, $cellProvider, $flags);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function MultilingualListbuilderGridColumn($listbuilder, $id = '', $title = null,
            $titleTranslated = null, $template = null, $cellProvider = null,
            $availableLocales = null, $flags = []) {
        
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        
        self::__construct($listbuilder, $id, $title, $titleTranslated, $template, $cellProvider, $availableLocales, $flags);
    }
}

?>