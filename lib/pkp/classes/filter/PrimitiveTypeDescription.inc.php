<?php
declare(strict_types=1);

/**
 * @file classes/filter/PrimitiveTypeDescription.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PrimitiveTypeDescription
 * @ingroup filter
 *
 * @brief Class that describes a primitive input/output type.
 */

import('lib.pkp.classes.filter.TypeDescription');
import('lib.pkp.classes.filter.TypeDescriptionFactory');

class PrimitiveTypeDescription extends TypeDescription {
    /** @var string a PHP primitive type, e.g. 'string' */
    protected $_primitiveType;

    /**
     * Constructor
     *
     * @param $typeName string Allowed primitive types are
     * 'integer', 'string', 'float' and 'boolean'.
     */
    public function __construct($typeName) {
        parent::__construct($typeName);
    }

    /**
     * [SHIM] Backward Compatibility
     * @param $typeName string
     */
    public function PrimitiveTypeDescription($typeName) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::PrimitiveTypeDescription(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($typeName);
    }


    //
    // Setters and Getters
    //
    /**
     * @see TypeDescription::getNamespace()
     */
    public function getNamespace() {
        return TYPE_DESCRIPTION_NAMESPACE_PRIMITIVE;
    }


    //
    // Implement abstract template methods from TypeDescription
    //
    /**
     * @see TypeDescription::parseTypeName()
     */
    public function parseTypeName($typeName) {
        // This should be a primitive type
        if (!in_array($typeName, $this->_supportedPrimitiveTypes())) return false;

        $this->_primitiveType = $typeName;
        return true;
    }

    /**
     * @see TypeDescription::checkType()
     */
    public function checkType($object) { // Menghilangkan reference (&) pada parameter
        // We expect a primitive type
        if (!is_scalar($object)) return false;

        // Check the type
        if ($this->_getPrimitiveTypeName($object) != $this->_primitiveType) return false;

        return true;
    }


    //
    // Private helper methods
    //
    /**
     * Return a string representation of a primitive type.
     * @param $variable mixed
     */
    protected function _getPrimitiveTypeName($variable) { // Menghilangkan reference (&) dan menggunakan protected
        assert(!(is_object($variable) || is_array($variable) || is_null($variable)));

        // FIXME: When gettype's implementation changes as mentioned
        // in <http://www.php.net/manual/en/function.gettype.php> then
        // we have to manually re-implement this method.
        return str_replace('double', 'float', gettype($variable));
    }

    /**
     * Returns a (static) array with supported
     * primitive type names.
     *
     * NB: Workaround for missing static class
     * members in PHP4.
     */
    protected function _supportedPrimitiveTypes() { // Menggunakan protected
        static $supportedPrimitiveTypes = array(
            'string', 'integer', 'float', 'boolean'
        );
        return $supportedPrimitiveTypes;
    }
}
?>