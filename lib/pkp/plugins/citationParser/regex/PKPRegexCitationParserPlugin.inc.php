<?php
declare(strict_types=1);

/**
 * @defgroup plugins_citationParser_regex
 */

/**
 * @file plugins/citationParser/regex/PKPRegexCitationParserPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPRegexCitationParserPlugin
 * @ingroup plugins_citationParser_regex
 *
 * @brief Cross-application regular expression based citation parser
 *
 * [WIZDAM REFACTOR]
 * - PHP 8.1+ Strict Compliance
 * - Explicit Visibility & Return Types
 */

import('classes.plugins.Plugin');

class PKPRegexCitationParserPlugin extends Plugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    //
    // Override protected template methods from PKPPlugin
    //
    
    /**
     * @see PKPPlugin::register()
     * @param string $category
     * @param string $path
     */
    public function register(string $category, string $path, $mainContextId = null): bool {
        if (!parent::register($category, $path, $mainContextId)) {
            return false;
        }
        $this->addLocaleData();
        return true;
    }

    /**
     * @see PKPPlugin::getName()
     * @return string
     */
    public function getName(): string {
        return 'RegexCitationParserPlugin';
    }

    /**
     * @see PKPPlugin::getDisplayName()
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.citationParser.regex.displayName');
    }

    /**
     * @see PKPPlugin::getDescription()
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.citationParser.regex.description');
    }
}
?>