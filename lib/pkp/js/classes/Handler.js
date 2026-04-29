/**
 * @file js/classes/Handler.js
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Handler
 * @ingroup js_classes
 *
 * @brief Base class for handlers bound to a DOM HTML element.
 * MODERNIZED for jQuery 3+ & Stability
 */
(function($) {

    /**
     * @constructor
     *
     * @extends $.pkp.classes.ObjectProxy
     *
     * @param {jQueryObject} $element A DOM element to which
     * this handler is bound.
     * @param {Object} options Handler options.
     */
    $.pkp.classes.Handler = function($element, options) {

        // MODERNIZATION: Prevent crash if selector selects multiple elements
        if ($element.length > 1) {
            console.warn('Handler: jQuery selector contained more than one element. Using the first one.');
            $element = $element.first();
        }

        // Save a pointer to the bound element in the handler.
        this.$htmlElement_ = $element;

        // MODERNIZATION: CRITICAL FIX
        // Instead of throwing an Error and crashing the script, we warn and exit gracefully.
        if (this.data('handler') !== undefined) {
             var handlerName = (typeof this.getObjectName === 'function') ? this.getObjectName() : 'UnknownHandler';
             console.warn('The handler "' + handlerName + '" has already been bound. Skipping initialization.');
             return;
        }

        // Initialize object properties.
        this.eventBindings_ = { };
        this.dataItems_ = { };
        this.publishedEvents_ = { };

        if (options.$eventBridge) {
            // Configure the event bridge.
            this.$eventBridge_ = options.$eventBridge;
        }

        // The "publishChangeEvents" option can be used to specify
        // a list of event names that will also be published upon
        // content change.
        if (options.publishChangeEvents) {
            this.publishChangeEvents_ = options.publishChangeEvents;
            var i;
            for (i = 0; i < this.publishChangeEvents_.length; i++) {
                this.publishEvent(this.publishChangeEvents_[i]);
            }
        } else {
            this.publishChangeEvents_ = [];
        }

        // Bind the handler to the DOM element.
        this.data('handler', this);
    };


    //
    // Private properties
    //
    /**
     * Optional list of publication events.
     * @private
     * @type {Array}
     */
    $.pkp.classes.Handler.prototype.publishChangeEvents_ = null;


    /**
     * The HTML element this handler is bound to.
     * @private
     * @type {jQueryObject}
     */
    $.pkp.classes.Handler.prototype.$htmlElement_ = null;


    /**
     * A list of event bindings for this handler.
     * @private
     * @type {Object.<string, Array>}
     */
    $.pkp.classes.Handler.prototype.eventBindings_ = null;


    /**
     * A list of data items bound to the DOM element
     * managed by this handler.
     * @private
     * @type {Object.<string, boolean>}
     */
    $.pkp.classes.Handler.prototype.dataItems_ = null;


    /**
     * A list of published events.
     * @private
     * @type {Object.<string, boolean>}
     */
    $.pkp.classes.Handler.prototype.publishedEvents_ = null;


    /**
     * An HTML element id to which we'll forward all handler events.
     * @private
     * @type {?string}
     */
    $.pkp.classes.Handler.prototype.$eventBridge_ = null;


    //
    // Public static methods
    //
    /**
     * Retrieve the bound handler from the jQuery element.
     * @param {jQueryObject} $element The element to which the
     * handler was attached.
     * @return {Object} The retrieved handler.
     */
    $.pkp.classes.Handler.getHandler = function($element) {
        // Retrieve the handler.
        var handler = $element.data('pkp.handler');

        // Check whether the handler exists.
        if (!(handler instanceof $.pkp.classes.Handler)) {
            // MODERNIZATION: Soft fail
            console.warn('There is no handler bound to this element!');
            return null;
        }

        return handler;
    };


    //
    // Public methods
    //
    /**
     * Returns the HTML element this handler is bound to.
     *
     * @return {jQueryObject} The element this handler is bound to.
     */
    $.pkp.classes.Handler.prototype.getHtmlElement = function() {
        $.pkp.classes.Handler.checkContext_(this);
        return this.$htmlElement_;
    };


    /**
     * Publish change events. (See options.publishChangeEvents.)
     */
    $.pkp.classes.Handler.prototype.publishChangeEvents = function() {
        var i;
        for (i = 0; i < this.publishChangeEvents_.length; i++) {
            this.trigger(this.publishChangeEvents_[i]);
        }
    };


    /**
     * A generic event dispatcher that will be bound to
     * all handler events.
     *
     * @this {HTMLElement}
     * @param {jQuery.Event} event The jQuery event object.
     * @return {boolean} Return value to be passed back to jQuery.
     */
    $.pkp.classes.Handler.prototype.handleEvent = function(event) {
        var $callingElement, handler, boundEvents, args, returnValue, i, l;

        $callingElement = $(this);
        handler = $.pkp.classes.Handler.getHandler($callingElement);
        
        // Safety check if handler retrieval failed
        if (!handler) return false;

        // Make sure that we really got the right element.
        if ($callingElement[0] !== handler.getHtmlElement()[0]) {
            console.error('An invalid handler is bound to the calling element of an event!');
            return false;
        }

        // Retrieve the event handlers for the given event type.
        boundEvents = handler.eventBindings_[event.type];
        if (boundEvents === undefined) {
            return false;
        }

        // Call all event handlers.
        args = $.makeArray(arguments);
        returnValue = true;
        args.unshift(this);
        for (i = 0, l = boundEvents.length; i < l; i++) {
            // Invoke the event handler in the context of the handler object.
            if (boundEvents[i].apply(handler, args) === false) {
                returnValue = false;
            }

            if (event.isImmediatePropagationStopped()) {
                break;
            }
        }

        event.stopPropagation();
        return returnValue;
    };


    /**
     * Create a closure that calls the callback in the context of the handler object.
     * Use $.proxy where possible, but this wrapper maintains backward compat logic.
     *
     * @param {Function} callback The callback to be wrapped.
     * @param {Object=} opt_context Specifies the object which
     * |this| should point to.
     * @return {Function} The wrapped callback.
     */
    $.pkp.classes.Handler.prototype.callbackWrapper =
            function(callback, opt_context) {

        $.pkp.classes.Handler.checkContext_(this);

        if (!opt_context) {
            opt_context = this;
        }
        
        // Use jQuery's proxy for better performance and standard compliance
        return $.proxy(callback, opt_context);
    };


    /**
     * This callback can be used to handle simple remote server requests.
     */
    $.pkp.classes.Handler.prototype.remoteResponse =
            function(ajaxOptions, jsonData) {
        return this.handleJson(jsonData);
    };


    /**
     * Completely remove all traces of the handler.
     */
    $.pkp.classes.Handler.prototype.remove = function() {
        $.pkp.classes.Handler.checkContext_(this);
        var $element, key;

        $element = this.getHtmlElement();
        // MODERNIZATION: .unbind() -> .off()
        $element.off('.pkpHandler');

        for (key in this.dataItems_) {
            if (key !== 'pkp.handler') {
                $element.removeData(key);
            }
        }

        $element.trigger('pkpRemoveHandler');
        // MODERNIZATION: .unbind() -> .off()
        $element.off('.pkpHandlerRemove');

        $element.removeData('pkp.handler');
    };


    //
    // Protected methods
    //
    /**
     * Bind an event to a handler operation.
     * MODERNIZED: Uses .on() instead of .bind() internally.
     *
     * @protected
     * @param {string} eventName The name of the event
     * @param {Function} handler The event handler
     */
    $.pkp.classes.Handler.prototype.bind = function(eventName, handler) {
        $.pkp.classes.Handler.checkContext_(this);

        if (!this.eventBindings_[eventName]) {
            this.eventBindings_[eventName] = [];

            var eventNamespace;
            eventNamespace = '.pkpHandler';
            if (eventName === 'pkpRemoveHandler') {
                eventNamespace = '.pkpHandlerRemove';
            }

            // MODERNIZATION: .bind() -> .on()
            this.getHtmlElement().on(eventName + eventNamespace, this.handleEvent);
        }

        this.eventBindings_[eventName].push(handler);
    };


    /**
     * Unbind an event from a handler operation.
     * MODERNIZED: Uses .off() instead of .unbind() internally.
     */
    $.pkp.classes.Handler.prototype.unbind = function(eventName, handler) {
        $.pkp.classes.Handler.checkContext_(this);

        if (!this.eventBindings_[eventName]) {
            return false;
        }

        var i, length;
        for (i = 0, length = this.eventBindings_[eventName].length; i < length; i++) {
            if (this.eventBindings_[eventName][i] === handler) {
                this.eventBindings_[eventName].splice([i], 1);
                break;
            }
        }

        if (this.eventBindings_[eventName].length === 0) {
            delete this.eventBindings_[eventName];
            // MODERNIZATION: .unbind() -> .off()
            this.getHtmlElement().off(eventName, this.handleEvent);
        }

        return true;
    };


    /**
     * Add or retrieve a data item to/from the DOM element.
     */
    $.pkp.classes.Handler.prototype.data = function(key, opt_value) {
        $.pkp.classes.Handler.checkContext_(this);

        key = 'pkp.' + key;

        if (opt_value !== undefined) {
            this.dataItems_[key] = true;
        }

        return this.getHtmlElement().data(key, opt_value);
    };


    /**
     * This function should be used to pre-process a JSON response.
     */
    $.pkp.classes.Handler.prototype.handleJson = function(jsonData) {
        if (!jsonData) {
            // Soft fail instead of crash
            console.error('Server error: Server returned no or invalid data!');
            return false;
        }

        if (jsonData.status === true) {
            if (jsonData.event) {
                if (jsonData.event.data) {
                    this.trigger(jsonData.event.name, jsonData.event.data);
                } else {
                    this.trigger(jsonData.event.name);
                }
            }
            return jsonData;
        }
        
        // If we got an error message then display it.
        if (jsonData.content) {
            alert(jsonData.content);
        }
        return false;
    };


    /**
     * Trigger events.
     */
    $.pkp.classes.Handler.prototype.trigger =
            function(eventName, opt_data) {

        if (opt_data === undefined) {
            opt_data = null;
        }

        var $handledElement = this.getHtmlElement();
        $handledElement.triggerHandler(eventName, opt_data);

        if (!this.publishedEvents_[eventName]) {
            this.triggerPublicEvent_(eventName, opt_data);
        }
    };


    /**
     * Publish an event triggered by a nested widget.
     */
    $.pkp.classes.Handler.prototype.publishEvent = function(eventName) {
        if (this.publishedEvents_[eventName]) {
            return;
        }

        this.publishedEvents_[eventName] = true;

        /*jslint unparam: true*/
        this.bind(eventName, function(context, privateEvent, var_args) {
            var eventData = null;
            if (arguments.length > 2) {
                eventData = Array.prototype.slice.call(arguments, 2);
            }
            this.triggerPublicEvent_(eventName, eventData);
        });
        /*jslint unparam: false*/
    };


    /**
     * Handle the "show more" and "show less" clicks.
     */
    $.pkp.classes.Handler.prototype.switchViz = function(event) {
        var eventElement = event.currentTarget;
        // Simple toggle is fine in jQuery 3 for visibility
        $(eventElement).parent().parent().find('span').toggle();
    };


    //
    // Private methods
    //
    /**
     * Trigger a public event.
     * @private
     */
    $.pkp.classes.Handler.prototype.triggerPublicEvent_ =
            function(eventName, opt_data) {

        var $handledElement = this.getHtmlElement();
        
        // Safety check
        if ($handledElement && $handledElement.parent()) {
             $handledElement.parent().trigger(eventName, opt_data);
        }

        if (this.$eventBridge_) {
            $('[id^="' + this.$eventBridge_ + '"]').trigger(eventName, opt_data);
        }
    };


    //
    // Private static methods
    //
    /**
     * Check the context of a method invocation.
     * @private
     */
    $.pkp.classes.Handler.checkContext_ = function(context) {
        if (!(context instanceof $.pkp.classes.Handler)) {
            console.error('Trying to call handler method in non-handler context!');
            // Don't throw, just log.
        }
    };


/** @param {jQuery} $ jQuery closure. */
}(jQuery));