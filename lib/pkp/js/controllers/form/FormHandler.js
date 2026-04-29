/**
 * @defgroup js_controllers_form
 */
/**
 * @file js/controllers/form/FormHandler.js
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FormHandler
 * @ingroup js_controllers_form
 *
 * @brief Abstract form handler.
 * MODERNIZED FIX for jQuery 3+ and Stability
 */
(function($) {

    /**
     * @constructor
     *
     * @extends $.pkp.classes.Handler
     *
     * @param {jQueryObject} $form the wrapped HTML form element.
     * @param {Object} options options to configure the form handler.
     */
    $.pkp.controllers.form.FormHandler = function($form, options) {
        this.parent($form, options);

        // Check whether we really got a form.
        if (!$form.is('form')) {
             console.error('FormHandler bound to non-form element:', $form);
             // Return early instead of throwing error to prevent crashing other scripts
             return; 
        }

        // Transform all form buttons with jQueryUI.
        if (options.transformButtons !== false) {
            $('.button', $form).button();
        }

        // Activate and configure the validation plug-in.
        if (options.submitHandler) {
            this.callerSubmitHandler_ = options.submitHandler;
        }

        // Set the redirect-to URL for the cancel button (if there is one).
        if (options.cancelRedirectUrl) {
            this.cancelRedirectUrl_ = options.cancelRedirectUrl;
        }

        // specific forms may override the form's default behavior
        // to warn about unsaved changes.
        if (typeof options.trackFormChanges !== 'undefined') {
            this.trackFormChanges_ = options.trackFormChanges;
        }

        // disable submission controls on certain forms.
        if (options.disableControlsOnSubmit) {
            this.disableControlsOnSubmit = options.disableControlsOnSubmit;
        }

        if (options.enableDisablePairs) {
            this.enableDisablePairs_ = options.enableDisablePairs;
            this.setupEnableDisablePairs();
        }

        // MODERNIZATION: Check if validate plugin exists
        if ($.fn.validate) {
            var validator = $form.validate({
                onfocusout: false,
                errorClass: 'error',
                highlight: function(element, errorClass) {
                    $(element).parent().parent().addClass(errorClass);
                },
                unhighlight: function(element, errorClass) {
                    $(element).parent().parent().removeClass(errorClass);
                },
                submitHandler: this.callbackWrapper(this.submitHandler_),
                showErrors: this.callbackWrapper(this.showErrors)
            });

            // Initial form validation.
            if (validator.checkForm()) {
                this.trigger('formValid');
            } else {
                this.trigger('formInvalid');
            }
        } else {
            console.warn('jQuery Validate plugin not loaded. Form validation disabled.');
        }

        // MODERNIZATION: Use .on() delegated events instead of .click() or .bind()
        // This ensures buttons work even if refreshed via AJAX
        
        // Activate the cancel button (if present).
        $form.on('click', '#cancelFormButton', this.callbackWrapper(this.cancelForm));

        // Activate the reset button (if present).
        $form.on('click', '#resetFormButton', this.callbackWrapper(this.resetForm));
        
        // Show More/Less
        $form.on('click', '.showMore, .showLess', this.switchViz);

        this.initializeTinyMCE_();

        // bind a handler to make sure tinyMCE fields are populated.
        // MODERNIZATION: Delegated event
        $form.on('click', '#submitFormButton', this.callbackWrapper(this.pushTinyMCEChanges_));
        $form.on('click', ':submit', this.callbackWrapper(this.pushTinyMCEChanges_));

        // bind a handler to handle change events on input fields.
        $form.on('change', ':input', this.callbackWrapper(this.formChange));
    };
    
    $.pkp.classes.Helper.inherits(
            $.pkp.controllers.form.FormHandler,
            $.pkp.classes.Handler);


    //
    // Private properties
    //
    $.pkp.controllers.form.FormHandler.prototype.callerSubmitHandler_ = null;
    $.pkp.controllers.form.FormHandler.prototype.cancelRedirectUrl_ = null;
    $.pkp.controllers.form.FormHandler.prototype.trackFormChanges_ = true;
    $.pkp.controllers.form.FormHandler.prototype.formChangesTracked = false;
    $.pkp.controllers.form.FormHandler.prototype.disableControlsOnSubmit = false;
    $.pkp.controllers.form.FormHandler.prototype.enableDisablePairs_ = null;


    //
    // Public methods
    //
	/**
	 * Internal callback called whenever the validator has to show form errors.
	 *
	 * @param {Object} validator The validator plug-in.
	 * @param {Object} errorMap An associative list that attributes
	 *  element names to error messages.
	 * @param {Array} errorList An array with objects that contains
	 *  error messages and the corresponding HTMLElements.
	 */
    /*jslint unparam: true*/
    $.pkp.controllers.form.FormHandler.prototype.showErrors =
            function(validator, errorMap, errorList) {

        // ensure that rich content elements have their
        // values stored before validation.
        if (typeof tinyMCE !== 'undefined') {
            try { tinyMCE.triggerSave(); } catch(e) {}
        }

        // Show errors generated by the form change.
        validator.defaultShowErrors();

        // Emit validation events.
        if (validator.checkForm()) {
            this.trigger('formValid');
        } else {
            this.trigger('formInvalid');
            this.enableFormControls();
        }
    };
    /*jslint unparam: false*/

	/**
	 * Internal callback called when a form element changes.
	 *
	 * @param {HTMLElement} formElement The form element that generated the event.
	 * @param {Event} event The formChange event.
	 */
    /*jslint unparam: true*/
    $.pkp.controllers.form.FormHandler.prototype.formChange =
            function(formElement, event) {

        if (this.trackFormChanges_ && !this.formChangesTracked) {
            this.trigger('formChanged');
            this.formChangesTracked = true;
        }
    };
    /*jslint unparam: false*/


    //
    // Protected methods
    //
	/**
	 * Protected method to disable a form's submit control if it is
	 * desired.
	 *
	 * @return {boolean} true.
	 * @protected
	 */
    $.pkp.controllers.form.FormHandler.prototype.disableFormControls =
            function() {

        if (this.disableControlsOnSubmit) {
            this.getHtmlElement().find(':submit').attr('disabled', 'disabled').
                    addClass('ui-state-disabled');
        }
        return true;
    };

	/**
	 * Protected method to reenable a form's submit control if it is
	 * desired.
	 *
	 * @return {boolean} true.
	 * @protected
	 */
    $.pkp.controllers.form.FormHandler.prototype.enableFormControls =
            function() {

        this.getHtmlElement().find(':submit').removeAttr('disabled').
                removeClass('ui-state-disabled');
        return true;
    };

	/**
	 * Internal callback called to cancel the form.
	 *
	 * @param {HTMLElement} cancelButton The cancel button.
	 * @param {Event} event The event that triggered the
	 *  cancel button.
	 * @return {boolean} false.
	 */
    /*jslint unparam: true*/
    $.pkp.controllers.form.FormHandler.prototype.cancelForm =
            function(cancelButton, event) {

        // Trigger the "form canceled" event and unregister the form.
        this.formChangesTracked = false;
        this.trigger('unregisterChangedForm');
        this.trigger('formCanceled');
        return false;
    };
    /*jslint unparam: false*/

	/**
	 * Internal callback called to reset the form.
	 *
	 * @param {HTMLElement} resetButton The reset button.
	 * @param {Event} event The event that triggered the
	 *  reset button.
	 * @return {boolean} false.
	 */
    /*jslint unparam: true*/
    $.pkp.controllers.form.FormHandler.prototype.resetForm =
            function(resetButton, event) {

        //unregister the form.
        this.formChangesTracked = false;
        this.trigger('unregisterChangedForm');

        var $form = this.getHtmlElement();
        $form.each(function() {
            this.reset();
        });

        return false;
    };
    /*jslint unparam: false*/

	/**
	 * Internal callback called to submit the form
	 * without further validation.
	 *
	 * @param {Object} validator The validator plug-in.
	 */
    $.pkp.controllers.form.FormHandler.prototype.submitFormWithoutValidation =
            function(validator) {

        // NB: When setting a submitHandler in jQuery's validator
        // plugin then the submit event will always be canceled
        if (validator && validator.settings) {
            validator.settings.submitHandler = null;
        }
        
        this.disableFormControls();
        this.getHtmlElement().submit();
        this.formChangesTracked = false;
    };


    //
    // Private Methods
    //
	/**
	 * Initialize TinyMCE instances.
	 *
	 * There are instances where TinyMCE is not initialized with the call to
	 * init(). These occur when content is loaded after the fact (via AJAX).
	 *
	 * In these cases, search for richContent fields and initialize them.
	 *
	 * @private
	 */
    $.pkp.controllers.form.FormHandler.prototype.initializeTinyMCE_ =
            function() {

        if (typeof tinyMCE !== 'undefined') {
            var $element, elementId, self = this;
            $element = this.getHtmlElement();
            elementId = $element.attr('id');
            
            setTimeout(function() {
                // Defensive check
                if ($('#' + elementId).length === 0) return;

                $('#' + elementId).find('.richContent').each(function() {
                    var textareaId = $(this).attr('id');
                    try {
                        // MODERNIZATION: Try/Catch to prevent crash if TinyMCE version mismatches
                        // Removing existing control first often helps
                        tinyMCE.execCommand('mceRemoveControl', false, textareaId);
                        tinyMCE.execCommand('mceAddControl', false, textareaId);
                    } catch(e) {
                        console.warn('TinyMCE init failed for ' + textareaId, e);
                    }
                });
            }, 500);
        }
    };

	/**
	 * Internal callback called after form validation to handle form
	 * submission.
	 *
	 * NB: Returning from this method without explicitly submitting
	 * the form will cancel form submission.
	 *
	 * @private
	 *
	 * @param {Object} validator The validator plug-in.
	 * @param {HTMLElement} formElement The wrapped HTML form.
	 */
    $.pkp.controllers.form.FormHandler.prototype.submitHandler_ =
            function(validator, formElement) {

        // Notify any nested formWidgets of the submit action.
        var formSubmitEvent = new $.Event('formSubmitRequested');
        $(formElement).find('.formWidget').trigger(formSubmitEvent);

        // If the default behavior was prevented for any reason, stop.
        if (formSubmitEvent.isDefaultPrevented()) {
            return;
        }

        $(formElement).find('.pkp_helpers_progressIndicator').show();

        this.trigger('unregisterChangedForm');

        if (this.callerSubmitHandler_ !== null) {
            this.formChangesTracked = false;
            // MODERNIZATION: Try/Catch block for external submit handlers
            try {
                this.callbackWrapper(this.callerSubmitHandler_).
                        call(validator, formElement);
            } catch (e) {
                console.error('External submit handler failed:', e);
                // Re-enable controls so user is not stuck
                this.enableFormControls(); 
                $(formElement).find('.pkp_helpers_progressIndicator').hide();
            }
        } else {
            // No form submission handler was provided. Use the usual method.
            this.submitFormWithoutValidation(validator);
        }
    };

	/**
	 * Internal callback called to push TinyMCE changes back to fields
	 * so they can be validated.
	 *
	 * @return {boolean} true.
	 * @private
	 */
    $.pkp.controllers.form.FormHandler.prototype.pushTinyMCEChanges_ =
            function() {
        // ensure that rich content elements have their
        // values stored before validation.
        if (typeof tinyMCE !== 'undefined') {
            try {
                tinyMCE.triggerSave();
            } catch (e) {
                // Ignore TinyMCE errors during save to allow form submit to proceed
            }
        }
        return true;
    };

	/**
	 * Configures the enable/disable pair bindings between a checkbox
	 * and some other form element.
	 *
	 * @return {boolean} true.
	 */
    $.pkp.controllers.form.FormHandler.prototype.setupEnableDisablePairs =
            function() {
        var formElement, key;

        formElement = this.getHtmlElement();
        for (key in this.enableDisablePairs_) {
            // MODERNIZATION: Use .on('click') instead of .bind('click')
            $(formElement).find("[id^='" + key + "']").on(
                    'click', this.callbackWrapper(this.toggleDependentElement_));
        }
        return true;
    };

	/**
	 * Enables or disables the item which depends on the state of source of the
	 * Event.
	 * @param {HTMLElement} sourceElement The element which generated the event.
	 * @return {boolean} true.
	 * @private
	 */
    $.pkp.controllers.form.FormHandler.prototype.toggleDependentElement_ =
            function(sourceElement) {
        var formElement, elementId, targetElement;

        formElement = this.getHtmlElement();
        elementId = $(sourceElement).attr('id');
        targetElement = $(formElement).find(
                "[id^='" + this.enableDisablePairs_[elementId] + "']");

        if ($(sourceElement).is(':checked')) {
            $(targetElement).removeAttr('disabled');
        } else {
            $(targetElement).attr('disabled', 'disabled');
        }

        return true;
    };
    
/** @param {jQuery} $ jQuery closure. */
}(jQuery));