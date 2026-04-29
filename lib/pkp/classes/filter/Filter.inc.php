<?php
declare(strict_types=1);

/**
 * @file classes/filter/Filter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Filter
 * @ingroup filter
 *
 * @brief Class that provides the basic template for a filter. Filters are
 * generic data processors that take in a well-specified data type
 * and return another well-specified data type.
 */

import('lib.pkp.classes.core.DataObject');
import('lib.pkp.classes.filter.TypeDescription');
import('lib.pkp.classes.filter.TypeDescriptionFactory');

class Filter extends DataObject {
    /** @var TypeDescription */
    public $_inputType;

    /** @var TypeDescription */
    public $_outputType;

    /** @var mixed */
    public $_input;

    /** @var mixed */
    public $_output;

    /** @var array a list of errors occurred while filtering */
    public $_errors = array();

    /**
     * @var RuntimeEnvironment the installation requirements required to
     * run this filter instance, false on initialization.
     */
    public $_runtimeEnvironment = false;

    /**
     * Constructor
     *
     * Receives input and output type that define the transformation.
     * @see TypeDescription
     *
     * @param $inputType string a string representation of a TypeDescription
     * @param $outputType string a string representation of a TypeDescription
     */
    public function __construct($inputType, $outputType) {
        // Initialize the filter.
        parent::__construct();
        $this->setTransformationType($inputType, $outputType);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Filter($inputType, $outputType) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::Filter(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    //
    // Setters and Getters
    //
    /**
     * Set the display name
     * @param $displayName string
     */
    public function setDisplayName($displayName) {
        $this->setData('displayName', $displayName);
    }

    /**
     * Get the display name
     *
     * NB: The standard implementation of this
     * method will initialize the display name
     * with the filter class name. Subclasses can of
     * course override this behavior by explicitly
     * setting a display name.
     *
     * @return string
     */
    public function getDisplayName() {
        if (!$this->hasData('displayName')) {
            $this->setData('displayName', get_class($this));
        }

        return $this->getData('displayName');
    }

    /**
     * Set the sequence id
     * @param $seq integer
     */
    public function setSeq($seq) {
        $this->setData('seq', $seq);
    }

    /**
     * Get the sequence id
     * @return integer
     */
    public function getSeq() {
        return $this->getData('seq');
    }

    /**
     * Set the input/output type of this filter group.
     *
     * @param $inputType TypeDescription|string
     * @param $outputType TypeDescription|string
     *
     * @see TypeDescriptionFactory::instantiateTypeDescription() for more details
     */
    public function setTransformationType($inputType, $outputType) {
        // MODERNIZATION: Removed & from parameters and assignment
        $typeDescriptionFactory = TypeDescriptionFactory::getInstance();

        // Instantiate the type descriptions if we got string input.
        // MODERNIZATION: Replaced is_a() with instanceof
        // Pastikan class TypeDescription sudah di-load atau dikenali autoloader
        if (!($inputType instanceof TypeDescription)) {
            assert(is_string($inputType));
            $inputType = $typeDescriptionFactory->instantiateTypeDescription($inputType);
        }
        
        if (!($outputType instanceof TypeDescription)) {
            assert(is_string($outputType));
            $outputType = $typeDescriptionFactory->instantiateTypeDescription($outputType);
        }

        $this->_inputType = $inputType;
        $this->_outputType = $outputType;
    }

    /**
     * Get the input type
     * @return TypeDescription
     */
    public function getInputType() {
        return $this->_inputType;
    }

    /**
     * Get the output type
     * @return TypeDescription
     */
    public function getOutputType() {
        return $this->_outputType;
    }

    /**
     * Get the last valid output produced by
     * this filter.
     *
     * This can be used for debugging internal
     * filter state or for access to intermediate
     * results when working with larger filter
     * grids.
     *
     * NB: The output will be set only after
     * output validation so that you can be
     * sure that you'll always find valid
     * data here.
     *
     * @return mixed
     */
    public function getLastOutput() {
        return $this->_output;
    }

    /**
     * Get the last valid input processed by
     * this filter.
     *
     * This can be used for debugging internal
     * filter state or for access to intermediate
     * results when working with larger filter
     * grids.
     *
     * NB: The input will be set only after
     * input validation so that you can be
     * sure that you'll always find valid
     * data here.
     *
     * @return mixed
     */
    public function getLastInput() {
        return $this->_input;
    }

    /**
     * Add a filter error
     * @param $message string
     */
    public function addError($message) {
        $this->_errors[] = $message;
    }

    /**
     * Get all filter errors
     * @return array
     */
    public function getErrors() {
        return $this->_errors;
    }

    /**
     * Whether this filter has produced errors.
     * @return boolean
     */
    public function hasErrors() {
        return (!empty($this->_errors));
    }

    /**
     * Clear all processing errors.
     */
    public function clearErrors() {
        $this->_errors = array();
    }

    /**
     * Get the required runtime environment
     * @return RuntimeEnvironment
     */
    public function getRuntimeEnvironment() {
        return $this->_runtimeEnvironment;
    }


    //
    // Abstract template methods to be implemented by subclasses
    //
    /**
     * This method performs the actual data processing.
     * NB: sub-classes must implement this method.
     * @param $input mixed validated filter input data
     * @return mixed non-validated filter output or null
     * if processing was not successful.
     */
    public function process($input) {
        // MODERNIZATION: Removed & from definition and param
        assert(false);
    }

    //
    // Public methods
    //
    /**
     * Returns true if the given input and output
     * objects represent a valid transformation
     * for this filter.
     *
     * This check must be type based. It can
     * optionally include an additional stateful
     * inspection of the given object instances.
     *
     * If the output type is null then only
     * check whether the given input type is
     * one of the input types accepted by this
     * filter.
     *
     * The standard implementation provides full
     * type based checking. Subclasses must
     * implement any required stateful inspection
     * of the provided objects.
     *
     * @param $input mixed
     * @param $output mixed
     * @return boolean
     */
    public function supports($input, $output) {
        // MODERNIZATION: Removed & from parameters
        
        // Validate input
        $inputType = $this->getInputType();
        $validInput = $inputType->isCompatible($input);

        // If output is null then we're done
        if (is_null($output)) return $validInput;

        // Validate output
        $outputType = $this->getOutputType();
        $validOutput = $outputType->isCompatible($output);

        return $validInput && $validOutput;
    }

    /**
     * Returns true if the given input is supported
     * by this filter. Otherwise returns false.
     *
     * NB: sub-classes will not normally override
     * this method.
     *
     * @param $input mixed
     * @return boolean
     */
    public function supportsAsInput($input) {
        // MODERNIZATION: Can pass null directly now that reference constraint is gone
        return $this->supports($input, null);
    }

    /**
     * Check whether the filter is compatible with
     * the required runtime environment.
     * @return boolean
     */
    public function isCompatibleWithRuntimeEnvironment() {
        if ($this->_runtimeEnvironment === false) {
            // The runtime environment has never been
            // queried before.
            $runtimeSettings = self::supportedRuntimeEnvironmentSettings();

            // Find out whether we have any runtime restrictions set.
            $hasRuntimeSettings = false;
            
            // Define variables to hold settings
            $phpVersionMin = $phpVersionMax = null;
            $phpExtensions = $externalPrograms = array();

            foreach($runtimeSettings as $runtimeSetting => $defaultValue) {
                if ($this->hasData($runtimeSetting)) {
                    $$runtimeSetting = $this->getData($runtimeSetting);
                    $hasRuntimeSettings = true;
                } else {
                    $$runtimeSetting = $defaultValue;
                }
            }

            // If we found any runtime restrictions then construct a
            // runtime environment from the settings.
            if ($hasRuntimeSettings) {
                import('lib.pkp.classes.core.RuntimeEnvironment');
                $this->_runtimeEnvironment = new RuntimeEnvironment($phpVersionMin, $phpVersionMax, $phpExtensions, $externalPrograms);
            } else {
                // Set null so that we don't try to construct
                // a runtime environment object again.
                $this->_runtimeEnvironment = null;
            }
        }

        if (is_null($this->_runtimeEnvironment) || $this->_runtimeEnvironment->isCompatible()) return true;

        return false;
    }

    /**
     * Filters the given input.
     *
     * Input and output of this method will
     * be tested for compliance with the filter
     * definition.
     *
     * NB: sub-classes will not normally override
     * this method.
     *
     * @param mixed an input value that is supported
     * by this filter
     * @return mixed a valid return value or null
     * if an error occurred during processing
     */
    public function execute($input) {
        // Make sure that we don't destroy referenced
        // data somewhere out there.
        unset($this->_input, $this->_output);

        // Check the runtime environment
        if (!$this->isCompatibleWithRuntimeEnvironment()) {
            // Missing installation requirements.
            fatalError('Trying to run a transformation that is not supported in your installation environment.');
        }

        // Validate the filter input
        if (!$this->supportsAsInput($input)) {
            // We have no valid input so return
            // an empty output (see unset statement
            // above).
            return $this->_output;
        }

        // Save a reference to the last valid input
        // MODERNIZATION: Removed &
        $this->_input = $input;

        // Process the filter
        // MODERNIZATION: Removed &
        $preliminaryOutput = $this->process($input);

        // Validate the filter output
        if (!is_null($preliminaryOutput) && $this->supports($input, $preliminaryOutput)) {
             // MODERNIZATION: Removed &
            $this->_output = $preliminaryOutput;
        }

        // Return processed data
        return $this->_output;
    }

    //
    // Public helper methods
    //
    /**
     * Returns a static array with supported runtime
     * environment settings and their default values.
     *
     * @return array
     */
    public static function supportedRuntimeEnvironmentSettings() {
        static $runtimeEnvironmentSettings = array(
            'phpVersionMin' => PHP_REQUIRED_VERSION,
            'phpVersionMax' => null,
            'phpExtensions' => array(),
            'externalPrograms' => array()
        );

        return $runtimeEnvironmentSettings;
    }
}
?>