<?php
declare(strict_types=1);

/**
 * @defgroup FormBuilderVocabulary
 */

/**
 * @file classes/form/FormBuilderVocabulary.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION v3.4]
 * - Refactored for PHP 8.1+ Strict Mode
 * - Modern Array Syntax
 * - Strict Error Handling
 * - Security Enhancements
 *
 * @class FormBuilderVocabulary
 * @ingroup core
 * @see Form
 */


class FormBuilderVocabulary {
    /** @var mixed|null Form associated with this object */
    protected $_form = null;

    /** @var array Styles organized by parameter name */
    protected $_fbvStyles = [];

    /**
     * Constructor.
     * @param mixed|null $form
     */
    public function __construct($form = null) {
        $this->_fbvStyles = [
            'size' => ['SMALL' => 'SMALL', 'MEDIUM' => 'MEDIUM', 'LARGE' => 'LARGE'],
            'height' => ['SHORT' => 'SHORT', 'MEDIUM' => 'MEDIUM', 'TALL' => 'TALL']
        ];
        if ($form) {
            $this->setForm($form);
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FormBuilderVocabulary($form = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::FormBuilderVocabulary(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($form = null);
    }

    //
    // Setters and Getters
    //

    /**
     * Set the form
     * @param object $form
     */
    public function setForm($form): void {
        if (isset($form)) {
            assert($form instanceof Form);
        }
        // [WIZDAM] Removed reference assignment (&)
        $this->_form = $form;
    }

    /**
     * Get the form
     * @return object|null
     */
    public function getForm() {
        return $this->_form;
    }

    /**
     * Get the form style constants
     * @return array
     */
    public function getStyles(): array {
        return $this->_fbvStyles;
    }

    //
    // Public Methods
    //

    /**
     * A form area that contains form sections.
     * @param array $params
     * @param string|null $content
     * @param object $smarty
     * @param bool $repeat
     * @return string
     */
    public function smartyFBVFormArea(array $params, $content, &$smarty, &$repeat): string {
        if (!isset($params['id'])) {
            // [WIZDAM] Strict Error Handling
            header('HTTP/1.0 500 Internal Server Error');
            fatalError('FBV: form area \'id\' not set.', 500);
        }

        if (!$repeat) {
            $smarty->assign('FBV_class', $params['class'] ?? null);
            $smarty->assign('FBV_id', $params['id']);
            $smarty->assign('FBV_content', $content ?? null);
            $smarty->assign('FBV_translate', $params['translate'] ?? true);
            $smarty->assign('FBV_title', $params['title'] ?? null);
            return $smarty->fetch('form/formArea.tpl');
        }
        return '';
    }

    /**
     * A form section that contains controls in a variety of layout possibilities.
     * @param array $params
     * @param string|null $content
     * @param object $smarty
     * @param bool $repeat
     * @return string
     */
    public function smartyFBVFormSection(array $params, $content, &$smarty, &$repeat): string {
        $form = $this->getForm();
        
        if (!$repeat) {
            $smarty->assign('FBV_required', $params['required'] ?? false);
            $smarty->assign('FBV_id', $params['id'] ?? null);
            $smarty->assign('FBV_labelFor', !empty($params['for']) ? $params['for'] : null);
            $smarty->assign('FBV_title', $params['title'] ?? null);
            $smarty->assign('FBV_label', $params['label'] ?? null);
            $smarty->assign('FBV_layoutInfo', $this->_getLayoutInfo($params));
            $smarty->assign('FBV_description', $params['description'] ?? null);
            $smarty->assign('FBV_content', $content ?? null);
            // default is to perform translation:
            $smarty->assign('FBV_translate', $params['translate'] ?? true);

            $class = $params['class'] ?? '';

            // Check if we are using the Form class and if there are any errors
            $smarty->clear_assign(['FBV_sectionErrors']);
            if (isset($form) && !empty($form->formSectionErrors)) {
                $class = $class . (empty($class) ? '' : ' ') . 'error';
                $smarty->assign('FBV_sectionErrors', $form->formSectionErrors);
                $form->formSectionErrors = [];
            }

            // If we are displaying checkboxes or radio options, we'll need to use a
            // list to organize our elements -- Otherwise we use divs and spans
            if (isset($params['list']) && $params['list'] !== false) {
                $smarty->assign('FBV_listSection', true);
            } else {
                // Double check that we don't have lists in the content.
                if (substr(trim((string)$content), 0, 4) === "<li>") {
                    trigger_error('FBV: list attribute not set on form section containing lists', E_USER_WARNING);
                }

                $smarty->assign('FBV_listSection', false);
            }

            $smarty->assign('FBV_class', $class);
            $smarty->assign('FBV_layoutColumns', !empty($params['layout']));

            return $smarty->fetch('form/formSection.tpl');
        } else {
            if (isset($form)) {
                $form->formSectionErrors = [];
            }
        }
        return '';
    }

    /**
     * Submit and (optional) cancel button for a form.
     * @param array $params
     * @param object $smarty
     * @return string
     */
    public function smartyFBVFormButtons(array $params, &$smarty): string {
        // Submit button options.
        $smarty->assign('FBV_submitText', $params['submitText'] ?? 'common.ok');
        $smarty->assign('FBV_submitDisabled', isset($params['submitDisabled']) ? (bool)$params['submitDisabled'] : false);
        $smarty->assign('FBV_confirmSubmit', $params['confirmSubmit'] ?? null);

        // Cancel button options.
        $smarty->assign('FBV_cancelText', $params['cancelText'] ?? 'common.cancel');
        $smarty->assign('FBV_hideCancel', isset($params['hideCancel']) ? (bool)$params['hideCancel'] : false);
        $smarty->assign('FBV_confirmCancel', $params['confirmCancel'] ?? null);
        $smarty->assign('FBV_cancelAction', $params['cancelAction'] ?? null);
        $smarty->assign('FBV_cancelUrl', $params['cancelUrl'] ?? null);
        $smarty->assign('FBV_formReset', isset($params['formReset']) ? (bool)$params['formReset'] : false);

        $smarty->assign('FBV_translate', $params['translate'] ?? true);

        return $smarty->fetch('form/formButtons.tpl');
    }

    /**
     * Base form element.
     * @param array $params
     * @param object $smarty
     * @param string|null $content
     * @return string|null
     */
    public function smartyFBVElement(array $params, &$smarty, $content = null): ?string {
        if (!isset($params['type'])) {
            header('HTTP/1.0 500 Internal Server Error');
            fatalError('FBV: Element type not set', 500);
        }
        if (!isset($params['id'])) {
            header('HTTP/1.0 500 Internal Server Error');
            fatalError('FBV: Element ID not set', 500);
        }

        // Set up the element template
        $smarty->assign('FBV_id', $params['id']);
        $smarty->assign('FBV_class', $params['class'] ?? null);
        $smarty->assign('FBV_required', $params['required'] ?? false);
        $smarty->assign('FBV_layoutInfo', $this->_getLayoutInfo($params));
        $smarty->assign('FBV_label', $params['label'] ?? null);
        $smarty->assign('FBV_for', $params['for'] ?? null);
        $smarty->assign('FBV_tabIndex', $params['tabIndex'] ?? null);
        $smarty->assign('FBV_translate', $params['translate'] ?? true);
        $smarty->assign('FBV_keepLabelHtml', $params['keepLabelHtml'] ?? false);

        // Unset these parameters so they don't get assigned twice
        unset($params['class']);

        // Find fields that the form class has marked as required and add the 'required' class to them
        $params = $this->_addClientSideValidation($params);
        $smarty->assign('FBV_validation', $params['validation'] ?? null);

        // Set up the specific field's template
        // Using native strtolower instead of strtolower_codesafe if strictly safe, or wrapper if needed.
        // Assuming strict ASCII types for form elements.
        switch (strtolower($params['type'])) {
            case 'autocomplete':
                $content = $this->_smartyFBVAutocompleteInput($params, $smarty);
                break;
            case 'button':
            case 'submit':
                $content = $this->_smartyFBVButton($params, $smarty);
                break;
            case 'checkbox':
                $content = $this->_smartyFBVCheckbox($params, $smarty);
                unset($params['label']);
                break;
            case 'checkboxgroup':
                $content = $this->_smartyFBVCheckboxGroup($params, $smarty);
                unset($params['label']);
                break;
            case 'file':
                $content = $this->_smartyFBVFileInput($params, $smarty);
                break;
            case 'hidden':
                $content = $this->_smartyFBVHiddenInput($params, $smarty);
                break;
            case 'keyword':
                $content = $this->_smartyFBVKeywordInput($params, $smarty);
                break;
            case 'interests':
                $content = $this->_smartyFBVInterestsInput($params, $smarty);
                break;
            case 'link':
                $content = $this->_smartyFBVLink($params, $smarty);
                break;
            case 'radio':
                $content = $this->_smartyFBVRadioButton($params, $smarty);
                unset($params['label']);
                break;
            case 'rangeslider':
                $content = $this->_smartyFBVRangeSlider($params, $smarty);
                break;
            case 'select':
                $content = $this->_smartyFBVSelect($params, $smarty);
                break;
            case 'text':
                $content = $this->_smartyFBVTextInput($params, $smarty);
                break;
            case 'textarea':
                $content = $this->_smartyFBVTextArea($params, $smarty);
                break;
            default:
                header('HTTP/1.0 500 Internal Server Error');
                fatalError('FBV: Invalid element type "' . $params['type'] . '"', 500);
        }

        unset($params['type']);

        $parent = $smarty->_tag_stack[count($smarty->_tag_stack)-1] ?? null;
        
        if ($parent) {
            $form = $this->getForm();
            if (isset($form) && isset($form->errorFields[$params['id']])) {
                array_push($form->formSectionErrors, $form->errorsArray[$params['id']]);
            }
        }

        return $content;
    }

    //
    // Private/Protected methods
    //

    /**
     * Form button.
     * parameters: label (or value), disabled (optional), type (optional)
     * @param array $params
     * @param object $smarty
     * @return string
     */
    protected function _smartyFBVButton(array $params, &$smarty): string {
        // accept 'value' param, but the 'label' param is preferred
        if (isset($params['value'])) {
            $value = $params['value'];
            $params['label'] = $params['label'] ?? $value;
            unset($params['value']);
        }

        // the type of this button. the default value is 'button' (but could be 'submit')
        $params['type'] = isset($params['type']) ? strtolower($params['type']) : 'button';
        $params['disabled'] = $params['disabled'] ?? false;

        $buttonParams = '';
        $smarty->clear_assign(['FBV_label', 'FBV_type', 'FBV_disabled']);
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'label': $smarty->assign('FBV_label', $value); break;
                case 'type': $smarty->assign('FBV_type', $value); break;
                case 'disabled': $smarty->assign('FBV_disabled', $params['disabled']); break;
                default: 
                    $buttonParams .= htmlspecialchars((string)$key, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '" ';
            }
        }

        $smarty->assign('FBV_buttonParams', $buttonParams);

        return $smarty->fetch('form/button.tpl');
    }

    /**
     * Text link.
     * @param array $params
     * @param object $smarty
     * @return string
     */
    protected function _smartyFBVLink(array $params, &$smarty): string {
        if (!isset($params['id'])) {
            header('HTTP/1.0 500 Internal Server Error');
            fatalError('FBV: link form element \'id\' not set.', 500);
        }

        // accept 'value' param, but the 'label' param is preferred
        if (isset($params['value'])) {
            $value = $params['value'];
            $params['label'] = $params['label'] ?? $value;
            unset($params['value']);
        }

        // Set the URL if there is one (defaults to '#' e.g. when the link should activate javascript)
        $smarty->assign('FBV_href', $params['href'] ?? '#');

        $smarty->clear_assign(['FBV_label', 'FBV_type', 'FBV_disabled']);
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'type': break;
                case 'label': $smarty->assign('FBV_label', $value); break;
                // case 'type': duplicate in switch
                case 'disabled': $smarty->assign('FBV_disabled', $params['disabled']); break;
            }
        }

        return $smarty->fetch('form/link.tpl');
    }

    /**
     * Form Autocomplete text input.
     * @param array $params
     * @param object $smarty
     * @return string
     */
    protected function _smartyFBVAutocompleteInput(array $params, &$smarty): string {
        if (!isset($params['autocompleteUrl'])) {
            header('HTTP/1.0 500 Internal Server Error');
            fatalError('FBV: url for autocompletion not specified.', 500);
        }

        // This id will be used for the hidden input that should be read by the Form.
        $autocompleteId = $params['id'];

        // We then override the id parameter to differentiate it from the hidden element
        $params['id'] = $autocompleteId . '_input';

        $smarty->clear_assign(['FBV_id', 'FBV_autocompleteUrl', 'FBV_autocompleteValue']);
        $smarty->assign('FBV_autocompleteUrl', $params['autocompleteUrl']);
        $smarty->assign('FBV_autocompleteValue', $params['autocompleteValue'] ?? null);

        unset($params['autocompleteUrl']);
        unset($params['autocompleteValue']);

        $smarty->assign('FBV_textInput', $this->_smartyFBVTextInput($params, $smarty));

        $smarty->assign('FBV_id', $autocompleteId);
        return $smarty->fetch('form/autocompleteInput.tpl');
    }

    /**
     * Range slider input.
     * @param array $params
     * @param object $smarty
     * @return string
     */
    protected function _smartyFBVRangeSlider(array $params, &$smarty): string {
        // Make sure our required fields are included
        if (!isset($params['min']) || !isset($params['max'])) {
            header('HTTP/1.0 500 Internal Server Error');
            fatalError('FBV: Min and/or max value for range slider not specified.', 500);
        }

        // Assign the min and max values to the handler
        $smarty->assign('FBV_min', $params['min']);
        $smarty->assign('FBV_max', $params['max']);

        $smarty->assign('FBV_label_content', isset($params['label']) ? $this->_smartyFBVSubLabel($params, $smarty) : null);

        return $smarty->fetch('form/rangeSlider.tpl');
    }

    /**
     * Form text input.
     * @param array $params
     * @param object $smarty
     * @return string
     */
    protected function _smartyFBVTextInput(array $params, &$smarty): string {
        $params['name'] = $params['name'] ?? $params['id'];
        $params['disabled'] = $params['disabled'] ?? false;
        $params['multilingual'] = $params['multilingual'] ?? false;
        $params['value'] = $params['value'] ?? '';
        // Unused in body but kept for consistency
        //$params['subLabelTranslate'] = isset($params['subLabelTranslate']) ? (bool) $params['subLabelTranslate'] : true;
        
        $smarty->assign('FBV_isPassword', isset($params['password']));

        $textInputParams = '';
        $smarty->clear_assign(['FBV_disabled', 'FBV_multilingual', 'FBV_name', 'FBV_value', 'FBV_label_content']);
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'label': $smarty->assign('FBV_label_content', $this->_smartyFBVSubLabel($params, $smarty)); break;
                case 'type': break;
                case 'size': break;
                case 'inline': break;
                case 'subLabelTranslate': break;
                case 'disabled': $smarty->assign('FBV_disabled', $params['disabled']); break;
                case 'multilingual': $smarty->assign('FBV_multilingual', $params['multilingual']); break;
                case 'name': $smarty->assign('FBV_name', $params['name']); break;
                case 'id': $smarty->assign('FBV_id', $params['id']); break;
                case 'value': $smarty->assign('FBV_value', $params['value']); break;
                case 'required': 
                    if ($value != 'true') {
                        $textInputParams .= 'required="' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '"';
                    }
                    break;
                default: 
                    $textInputParams .= htmlspecialchars((string)$key, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'). '" ';
            }
        }

        $smarty->assign('FBV_textInputParams', $textInputParams);

        return $smarty->fetch('form/textInput.tpl');
    }

    /**
     * Form text area.
     * @param array $params
     * @param object $smarty
     * @return string
     */
    protected function _smartyFBVTextArea(array $params, &$smarty): string {
        $params['name'] = $params['name'] ?? $params['id'];
        $params['disabled'] = $params['disabled'] ?? false;
        $params['rich'] = $params['rich'] ?? false;
        $params['multilingual'] = $params['multilingual'] ?? false;
        $params['value'] = $params['value'] ?? '';
        
        $textAreaParams = '';
        $smarty->clear_assign(['FBV_label_content', 'FBV_disabled', 'FBV_multilingual', 'FBV_name', 'FBV_value', 'FBV_height']);
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'name': $smarty->assign('FBV_name', $params['name']); break;
                case 'value': $smarty->assign('FBV_value', $value); break;
                case 'label': $smarty->assign('FBV_label_content', $this->_smartyFBVSubLabel($params, $smarty)); break;
                case 'type': break;
                case 'size': break;
                case 'inline': break;
                case 'subLabelTranslate': break;
                case 'height':
                    $styles = $this->getStyles();
                    switch($params['height']) {
                        case $styles['height']['SHORT']: $smarty->assign('FBV_height', 'short'); break;
                        case $styles['height']['MEDIUM']: $smarty->assign('FBV_height', 'medium'); break;
                        case $styles['height']['TALL']: $smarty->assign('FBV_height', 'tall'); break;
                        default:
                            header('HTTP/1.0 500 Internal Server Error');
                            fatalError('FBV: invalid height specified for textarea.', 500);
                    }
                    break;
                case 'rich': $smarty->assign('FBV_rich', $params['rich']); break;
                case 'disabled': $smarty->assign('FBV_disabled', $params['disabled']); break;
                case 'multilingual': $smarty->assign('FBV_multilingual', $params['multilingual']); break;
                case 'id': break; // if we don't do this, the textarea ends up with two id attributes because FBV_id is also set.
                default: 
                    $textAreaParams .= htmlspecialchars((string)$key, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '" ';
            }
        }

        $smarty->assign('FBV_textAreaParams', $textAreaParams);

        return $smarty->fetch('form/textarea.tpl');
    }

    /**
     * Hidden input element.
     * @param array $params
     * @param object $smarty
     * @return string
     */
    protected function _smartyFBVHiddenInput(array $params, &$smarty): string {
        $params['name'] = $params['name'] ?? $params['id'];
        $params['value'] = $params['value'] ?? '';

        $hiddenInputParams = '';
        $smarty->clear_assign(['FBV_id']);
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'name': $smarty->assign('FBV_name', $value); break;
                case 'id': $smarty->assign('FBV_id', $value); break;
                case 'value': $smarty->assign('FBV_value', $value); break;
                case 'label': break;
                case 'type': break;
                default: 
                    $hiddenInputParams .= htmlspecialchars((string)$key, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '" ';
            }
        }

        $smarty->assign('FBV_hiddenInputParams', $hiddenInputParams);

        return $smarty->fetch('form/hiddenInput.tpl');
    }

    /**
     * Form select control.
     * @param array $params
     * @param object $smarty
     * @return string
     */
    protected function _smartyFBVSelect(array $params, &$smarty): string {
        $params['name'] = $params['name'] ?? $params['id'];
        $params['translate'] = $params['translate'] ?? true;
        $params['disabled'] = $params['disabled'] ?? false;
        // $params['subLabelTranslate'] = isset($params['subLabelTranslate']) ? (bool) $params['subLabelTranslate'] : true;

        $selectParams = '';

        if (!isset($params['defaultValue']) || !isset($params['defaultLabel'])) {
            if (isset($params['defaultValue'])) unset($params['defaultValue']);
            if (isset($params['defaultLabel'])) unset($params['defaultLabel']);
            $smarty->assign('FBV_defaultValue', null);
            $smarty->assign('FBV_defaultLabel', null);
        }

        $smarty->clear_assign(['FBV_from', 'FBV_selected', 'FBV_label_content', 'FBV_defaultValue', 'FBV_defaultLabel']);
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'from': $smarty->assign('FBV_from', $value); break;
                case 'selected': $smarty->assign('FBV_selected', $value); break;
                case 'translate': $smarty->assign('FBV_translate', $value); break;
                case 'defaultValue': $smarty->assign('FBV_defaultValue', $value); break;
                case 'defaultLabel': $smarty->assign('FBV_defaultLabel', $value); break;
                case 'type': break;
                case 'inline': break;
                case 'subLabelTranslate': break;
                case 'label': $smarty->assign('FBV_label_content', $this->_smartyFBVSubLabel($params, $smarty)); break;
                case 'disabled': $smarty->assign('FBV_disabled', $params['disabled']); break;
                default: 
                    $selectParams .= htmlspecialchars((string)$key, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '" ';
            }
        }

        $smarty->assign('FBV_selectParams', $selectParams);

        return $smarty->fetch('form/select.tpl');
    }

    /**
     * Form checkbox group control.
     * @param array $params
     * @param object $smarty
     * @return string
     */
    protected function _smartyFBVCheckboxGroup(array $params, &$smarty): string {
        $params['name'] = $params['name'] ?? $params['id'];
        $params['translate'] = isset($params['translate']) ? (bool)$params['translate'] : true;
        $params['validation'] = $params['validation'] ?? false;
        $params['disabled'] = $params['disabled'] ?? false;
        // $params['subLabelTranslate'] = isset($params['subLabelTranslate']) ? (bool) $params['subLabelTranslate'] : true;
        
        $checkboxParams = '';
        if (empty($params['defaultValue']) || empty($params['defaultLabel'])) {
            if (isset($params['defaultValue'])) unset($params['defaultValue']);
            if (isset($params['defaultLabel'])) unset($params['defaultLabel']);
            $smarty->assign('FBV_defaultValue', null);
            $smarty->assign('FBV_defaultLabel', null);
        }

        $smarty->clear_assign(['FBV_from', 'FBV_selected', 'FBV_label_content', 'FBV_defaultValue', 'FBV_defaultLabel', 'FBV_name']);
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'from': $smarty->assign('FBV_from', $value); break;
                case 'selected': $smarty->assign('FBV_selected', $value); break;
                case 'translate': $smarty->assign('FBV_translate', $params['translate']); break;
                case 'defaultValue': $smarty->assign('FBV_defaultValue', $value); break;
                case 'defaultLabel': $smarty->assign('FBV_defaultLabel', $value); break;
                case 'name': $smarty->assign('FBV_name', $params['name']); break;
                case 'validation': $smarty->assign('FBV_validation', $params['validation']); break;
                case 'type': break;
                case 'inline': break;
                case 'subLabelTranslate': break;
                case 'disabled': $smarty->assign('FBV_disabled', $params['disabled']); break;
                default: 
                    $checkboxParams .= htmlspecialchars((string)$key, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '" ';
            }
        }

        $smarty->assign('FBV_checkboxParams', $checkboxParams);

        return $smarty->fetch('form/checkboxGroup.tpl');
    }

    /**
     * Checkbox input control.
     * @param array $params
     * @param object $smarty
     * @return string
     */
    protected function _smartyFBVCheckbox(array $params, &$smarty): string {
        $params['name'] = $params['name'] ?? $params['id'];
        $params['translate'] = isset($params['translate']) ? (bool)$params['translate'] : true;
        $params['checked'] = $params['checked'] ?? false;
        $params['disabled'] = $params['disabled'] ?? false;

        $checkboxParams = '';
        $smarty->clear_assign(['FBV_id', 'FBV_label']);
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'type': break;
                case 'id': $smarty->assign('FBV_id', $params['id']); break;
                case 'label': $smarty->assign('FBV_label', $params['label']); break;
                case 'translate': $smarty->assign('FBV_translate', $params['translate']); break;
                case 'checked': $smarty->assign('FBV_checked', $params['checked']); break;
                case 'disabled': $smarty->assign('FBV_disabled', $params['disabled']); break;
                default: 
                    $checkboxParams .= htmlspecialchars((string)$key, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '" ';
            }
        }

        $smarty->assign('FBV_checkboxParams', $checkboxParams);

        return $smarty->fetch('form/checkbox.tpl');
    }

    /**
     * Radio input control.
     * @param array $params
     * @param object $smarty
     * @return string
     */
    protected function _smartyFBVRadioButton(array $params, &$smarty): string {
        $params['name'] = $params['name'] ?? $params['id'];
        $params['translate'] = $params['translate'] ?? true;
        $params['checked'] = $params['checked'] ?? false;
        $params['disabled'] = $params['disabled'] ?? false;

        if (isset($params['label']) && isset($params['content'])) {
            header('HTTP/1.0 500 Internal Server Error');
            fatalError('FBV: radio button cannot have both a content and a label parameter. Label has precedence.', 500);
        }

        $radioParams = '';
        $smarty->clear_assign(['FBV_id', 'FBV_label', 'FBV_content']);
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'type': break;
                case 'id': $smarty->assign('FBV_id', $params['id']); break;
                case 'label': $smarty->assign('FBV_label', $params['label']); break;
                case 'translate': $smarty->assign('FBV_translate', $params['translate']); break;
                case 'checked': $smarty->assign('FBV_checked', $params['checked']); break;
                case 'disabled': $smarty->assign('FBV_disabled', $params['disabled']); break;
                case 'content': $smarty->assign('FBV_content', $params['content']); break;
                default: 
                    $radioParams .= htmlspecialchars((string)$key, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '" ';
            }
        }

        $smarty->assign('FBV_radioParams', $radioParams);

        return $smarty->fetch('form/radioButton.tpl');
    }

    /**
     * File upload input.
     * @param array $params
     * @param object $smarty
     * @return string
     */
    protected function _smartyFBVFileInput(array $params, &$smarty): string {
        $params['name'] = $params['name'] ?? $params['id'];
        $params['translate'] = $params['translate'] ?? true;
        $params['checked'] = $params['checked'] ?? false;
        $params['disabled'] = $params['disabled'] ?? false;
        $params['submit'] = $params['submit'] ?? false;

        $smarty->clear_assign(['FBV_id', 'FBV_label_content']);
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'type': break;
                case 'id': $smarty->assign('FBV_id', $params['id']); break;
                case 'submit': $smarty->assign('FBV_submit', $params['submit']); break;
                case 'name': $smarty->assign('FBV_name', $params['name']); break;
                case 'label': $smarty->assign('FBV_label_content', $this->_smartyFBVSubLabel($params, $smarty)); break;
                case 'disabled': $smarty->assign('FBV_disabled', $params['disabled']); break;
            }
        }

        return $smarty->fetch('form/fileInput.tpl');
    }

    /**
     * Keyword input.
     * @param array $params
     * @param object $smarty
     * @return string
     */
    protected function _smartyFBVKeywordInput(array $params, &$smarty): string {
        $params['multilingual'] = $params['multilingual'] ?? false;
        $params['disabled'] = $params['disabled'] ?? false;
        $params['available'] = $params['available'] ?? false;
        $params['current'] = $params['current'] ?? false;

        $smarty->clear_assign(['FBV_id', 'FBV_label', 'FBV_availableKeywords', 'FBV_currentKeywords', 'FBV_multilingual', 'FBV_sourceUrl']);
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'type': break;
                case 'id': $smarty->assign('FBV_id', $params['id']); break;
                case 'label': $smarty->assign('FBV_label_content', $this->_smartyFBVSubLabel($params, $smarty)); break;
                case 'available': $smarty->assign('FBV_availableKeywords', $params['available']); break;
                case 'current': $smarty->assign('FBV_currentKeywords', $params['current']); break;
                case 'disabled': $smarty->assign('FBV_disabled', $params['disabled']); break;
                case 'multilingual': $smarty->assign('FBV_multilingual', $params['multilingual']); break;
                case 'source': $smarty->assign('FBV_sourceUrl', $params['source']); break;
            }
        }

        return $smarty->fetch('form/keywordInput.tpl');
    }

    /**
     * Reviewing interests input.
     * @param array $params
     * @param object $smarty
     * @return string
     */
    protected function _smartyFBVInterestsInput(array $params, &$smarty): string {
        $params['interestsKeywords'] = $params['interestsKeywords'] ?? false;
        $params['interestsTextOnly'] = $params['interestsTextOnly'] ?? false;

        $smarty->clear_assign(['FBV_id', 'FBV_label', 'FBV_availableKeywords', 'FBV_currentKeywords', 'FBV_multilingual']);
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'type': break;
                case 'id': $smarty->assign('FBV_id', $params['id']); break;
                case 'interestsKeywords': $smarty->assign('FBV_interestsKeywords', $params['interestsKeywords']); break;
                case 'interestsTextOnly': $smarty->assign('FBV_interestsTextOnly', $params['interestsTextOnly']); break;
                case 'label': $smarty->assign('FBV_label_content', $this->_smartyFBVSubLabel($params, $smarty)); break;
            }
        }

        return $smarty->fetch('form/interestsInput.tpl');
    }

    /**
     * Custom Smarty function for labelling/highlighting of form fields.
     * @param array $params
     * @param object $smarty
     * @return string
     */
    protected function _smartyFBVSubLabel(array $params, &$smarty): string {
        $returner = '';
        if (!isset($params['label'])) {
             header('HTTP/1.0 500 Internal Server Error');
             fatalError('FBV: label for SubLabel not specified.', 500);
        }

        $form = $this->getForm();
        if (isset($form) && isset($form->errorFields[$params['name']])) {
            $smarty->assign('FBV_error', true);
        } else {
            $smarty->assign('FBV_error', false);
        }

        $smarty->clear_assign(['FBV_suppressId', 'FBV_label', 'FBV_required']);
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'subLabelTranslate': $smarty->assign('FBV_subLabelTranslate', $value); break;
                case 'label': $smarty->assign('FBV_label', $value); break;
                case 'suppressId': $smarty->assign('FBV_suppressId', $value); break;
                case 'required': $smarty->assign('FBV_required', $value); break;
            }
        }

        $returner = $smarty->fetch('form/subLabel.tpl');

        return $returner;
    }

    /**
     * Assign the appropriate class name to the element for client-side validation
     * @param array $params
     * @return array
     */
    protected function _addClientSideValidation(array $params): array {
        $form = $this->getForm();
        if (isset($form)) {
            // Assign the appropriate class name to the element for client-side validation
            $fieldId = $params['id'];
            if (isset($form->cssValidation[$fieldId])) {
                $params['validation'] = implode(' ', $form->cssValidation[$fieldId]);
            }
        }
        return $params;
    }

    /**
     * Cycle through layout parameters to add the appropriate classes to the element's parent container
     * @param array $params
     * @return string|null
     */
    protected function _getLayoutInfo(array $params): ?string {
        $classes = [];
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'size':
                    switch($value) {
                        case 'SMALL': $classes[] = 'pkp_helpers_quarter'; break;
                        case 'MEDIUM': $classes[] = 'pkp_helpers_half'; break;
                        case 'LARGE': $classes[] = 'pkp_helpers_threeQuarter'; break;
                    }
                    break;
                case 'inline':
                    if($value) $classes[] = 'inline'; break;
            }
        }
        if(!empty($classes)) {
            return implode(' ', $classes);
        } else return null;
    }


    /**
     * Custom Smarty function for labelling/highlighting of form fields.
     * @param array $params
     * @param object $smarty
     * @return string
     */
    public function smartyFieldLabel(array $params, &$smarty): string {
        $returner = '';
        if (!empty($params)) {
            if (isset($params['key'])) {
                $params['label'] = __($params['key'], $params);
            }

            $form = $this->getForm();
            if (isset($form) && isset($form->errorFields[$params['name']])) {
                $smarty->assign('FBV_class', 'error ' . ($params['class'] ?? ''));
            } else {
                $smarty->assign('FBV_class', $params['class']);
            }

            $smarty->clear_assign(['FBV_suppressId', 'FBV_label', 'FBV_required', 'FBV_disabled', 'FBV_name']);
            $smarty->assign('FBV_suppressId', false);
            $smarty->assign('FBV_required', false);
            foreach ($params as $key => $value) {
                switch ($key) {
                    case 'label': $smarty->assign('FBV_label', $value); break;
                    case 'required': $smarty->assign('FBV_required', $value); break;
                    case 'suppressId': $smarty->assign('FBV_suppressId', true); break;
                    case 'disabled': $smarty->assign('FBV_disabled', $value); break;
                    case 'name': $smarty->assign('FBV_name', $value); break;
                }
            }

            $returner = $smarty->fetch('form/fieldLabel.tpl');
        }
        return $returner;
    }
}

?>