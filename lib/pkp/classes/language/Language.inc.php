<?php
declare(strict_types=1);

/**
 * @defgroup language
 */

/**
 * @file classes/language/Language.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Language
 * @ingroup language
 * @see LanguageDAO
 *
 * @brief Basic class describing a language.
 *
 */

class Language extends DataObject {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Language() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    //
    // Get/set methods
    //

    /**
     * Get the name of the language.
     * @return string
     */
    public function getName() {
        return (string) $this->getData('name');
    }

    /**
     * Set the name of the language.
     * @param string $name
     */
    public function setName($name) {
        $this->setData('name', $name);
    }

    /**
     * Get language code.
     * @return string
     */
    public function getCode() {
        return (string) $this->getData('code');
    }

    /**
     * Set language code.
     * @param string $code
     */
    public function setCode($code) {
        $this->setData('code', $code);
    }

}

?>