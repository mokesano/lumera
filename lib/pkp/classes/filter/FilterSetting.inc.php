<?php
declare(strict_types=1);

/**
 * @file classes/filter/FilterSetting.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FilterSetting
 * @ingroup classes_filter
 *
 * @brief Class that describes a configurable filter setting.
 */

import('lib.pkp.classes.form.validation.FormValidator');

class FilterSetting {
    /** @var string the (internal) name of the setting */
    public $_name;

    /** @var string the supported transformation */
    public $_displayName;

    /** @var string */
    public $_validationMessage;

    /** @var string|boolean */
    public $_required;

    /** @var boolean */
    public $_isLocalized;

    /**
     * Constructor
     *
     * @param $name string
     * @param $displayName string
     * @param $validationMessage string
     * @param $required string
     * @param $isLocalized boolean
     */
    public function __construct($name, $displayName, $validationMessage, $required = FORM_VALIDATOR_REQUIRED_VALUE, $isLocalized = false) {
        $this->setName($name);
        $this->setDisplayName($displayName);
        $this->setValidationMessage($validationMessage);
        $this->setRequired($required);
        $this->setIsLocalized($isLocalized);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FilterSetting($name, $displayName, $validationMessage, $required = FORM_VALIDATOR_REQUIRED_VALUE, $isLocalized = false) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::FilterSetting(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct($name, $displayName, $validationMessage, $required, $isLocalized);
    }

    //
    // Setters and Getters
    //
    /**
     * Set the setting name
     * @param $name string
     */
    public function setName($name) {
        $this->_name = $name;
    }

    /**
     * Get the setting name
     * @return string
     */
    public function getName() {
        return $this->_name;
    }

    /**
     * Set the display name
     * @param $displayName string
     */
    public function setDisplayName($displayName) {
        $this->_displayName = $displayName;
    }

    /**
     * Get the display name
     * @return string
     */
    public function getDisplayName() {
        return $this->_displayName;
    }

    /**
     * Set the validation message
     * @param $validationMessage string
     */
    public function setValidationMessage($validationMessage) {
        $this->_validationMessage = $validationMessage;
    }

    /**
     * Get the validation message
     * @return string
     */
    public function getValidationMessage() {
        return $this->_validationMessage;
    }

    /**
     * Set the required flag
     * @param $required string
     */
    public function setRequired($required) {
        $this->_required = $required;
    }

    /**
     * Get the required flag
     * @return string
     */
    public function getRequired() {
        return $this->_required;
    }

    /**
     * Set the localization flag
     * @param $isLocalized boolean
     */
    public function setIsLocalized($isLocalized) {
        $this->_isLocalized = $isLocalized;
    }

    /**
     * Get the localization flag
     * @return boolean
     */
    public function getIsLocalized() {
        return $this->_isLocalized;
    }


    //
    // Protected Template Methods
    //
    /**
     * Get the form validation check
     * @param Form $form
     * @return FormValidator|null
     */
    public function getCheck($form) {
        // A validator is only required if this setting is mandatory.
        if ($this->getRequired() == FORM_VALIDATOR_OPTIONAL_VALUE) {
            return null;
        }

        // Instantiate a simple form validator.
        // Hapus '&' pada parameter form dan return
        $check = new FormValidator($form, $this->getName(), $this->getRequired(), $this->getValidationMessage());
        return $check;
    }
}
?>