/**
 * @defgroup js_classes
 */

/**
 * @file js/classes/Helper.js
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Helper
 * @ingroup js_controllers
 *
 * @brief PKP helper methods - MODERNIZED
 */
(function($) {

    // Create PKP namespaces.
    $.pkp = $.pkp || { };
    $.pkp.classes = $.pkp.classes || { };
    $.pkp.controllers = $.pkp.controllers || { };
    $.pkp.controllers.form = $.pkp.controllers.form || {};
    $.pkp.plugins = $.pkp.plugins || {};
    $.pkp.plugins.blocks = $.pkp.plugins.blocks || {};
    $.pkp.plugins.generic = $.pkp.plugins.generic || {};

    /**
     * Helper singleton
     * @constructor
     *
     * @extends $.pkp.classes.ObjectProxy
     */
    $.pkp.classes.Helper = function() {
        throw new Error('Trying to instantiate the Helper singleton!');
    };


    //
    // Private class constants
    //
    /**
     * Characters available for UUID generation.
     * @const
     * @private
     * @type {Array}
     */
    $.pkp.classes.Helper.CHARS_ = ['0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'abcdefghijklmnopqrstuvwxyz'].join('').split('');


    //
    // Public static helper methods
    //
    /**
     * Generate a random UUID.
     *
     * @return {string} an RFC4122v4 compliant UUID.
     */
    $.pkp.classes.Helper.uuid = function() {
        // MODERNIZATION: Use native crypto API if available (much better randomness)
        if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
            return crypto.randomUUID();
        }

        // Fallback for older browsers
        var chars = $.pkp.classes.Helper.CHARS_, uuid = new Array(36), rnd = 0, r, i;
        for (i = 0; i < 36; i++) {
            if (i == 8 || i == 13 || i == 18 || i == 23) {
                uuid[i] = '-';
            } else if (i == 14) {
                uuid[i] = '4';
            } else {
                /*jslint bitwise: true*/
                if (rnd <= 0x02) {
                    rnd = 0x2000000 + (Math.random() * 0x1000000) | 0;
                }
                r = rnd & 0xf;
                rnd = rnd >> 4;
                uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r];
                /*jslint bitwise: false*/
            }
        }
        return uuid.join('');
    };


    /**
     * Let one object inherit from another.
     *
     * @param {Function} Child Constructor of the child object.
     * @param {Function} Parent Constructor of the parent object.
     */
    $.pkp.classes.Helper.inherits = function(Child, Parent) {
        // Safety check
        if (!Parent || !Parent.prototype) {
             console.error('Helper.inherits: Parent constructor is invalid or undefined.');
             return;
        }

        // Use an empty temporary object to avoid calling a potentially costly constructor
        /** @constructor */ var Temp = function() {
            return this;
        };
        Temp.prototype = Parent.prototype;

        // Provide a way to reach the parent's method implementations
        Child.parent_ = Parent.prototype;

        // Let the child object inherit from the parent object.
        Child.prototype = new Temp();

        // Fix the child constructor
        Child.prototype.constructor = Child;

        // Parent constructor fix
        if (Parent.prototype.constructor == Object.prototype.constructor) {
            Parent.prototype.constructor = Parent;
        }
    };


    /**
     * Introduce a central object factory
     *
     * @param {string} objectName The name of an object.
     * @param {Array} args The arguments to be passed
     * into the object's constructor.
     * @return {$.pkp.classes.ObjectProxy} the instantiated object.
     */
    $.pkp.classes.Helper.objectFactory = function(objectName, args) {
        var ObjectConstructor, ObjectProxyInstance, objectInstance;

        // Resolve the object name.
        ObjectConstructor = $.pkp.classes.Helper.resolveObjectName(objectName);

        if (!ObjectConstructor) {
            // MODERNIZATION: Fail gracefully instead of crashing
            console.warn('ObjectFactory: Could not create object "' + objectName + '".');
            return null;
        }

        // Create a new proxy constructor instance.
        ObjectProxyInstance = $.pkp.classes.Helper.getObjectProxyInstance();

        // Copy static members over from the object proxy.
        $.extend(true, ObjectProxyInstance, $.pkp.classes.ObjectProxy);

        // Let the proxy inherit from the proxied object.
        $.pkp.classes.Helper.inherits(ObjectProxyInstance, ObjectConstructor);

        // Enrich the new proxy constructor prototype
        $.extend(true, ObjectProxyInstance.prototype,
                $.pkp.classes.ObjectProxy.prototype);

        // Instantiate the proxy with the proxied object.
        objectInstance = new ObjectProxyInstance(objectName, args);
        return objectInstance;
    };


    /**
     * Resolves the given object name to an object implementation
     * (or better to it's constructor).
     * @param {string} objectName The object name to resolve.
     * @return {Function} The constructor of the object.
     */
    $.pkp.classes.Helper.resolveObjectName = function(objectName) {
        var objectNameParts, i, functionName, ObjectConstructor;

        if (!objectName) return null;

        // Currently only objects in the $ namespace are supported.
        objectNameParts = objectName.split('.');
        if (objectNameParts.shift() != '$') {
            console.error('Namespace "' + objectNameParts[0] + '" for object "' + objectName + '" is currently not supported!');
            return null;
        }

        // Make sure that we actually have a constructor name
        functionName = objectNameParts[objectNameParts.length - 1];
        if (functionName.charAt(0).toUpperCase() !== functionName.charAt(0)) {
            console.error('The name "' + objectName + '" does not point to a constructor (must start with Uppercase)!');
            return null;
        }

        // Run through the namespace and identify the constructor.
        ObjectConstructor = $;
        for (i in objectNameParts) {
            ObjectConstructor = ObjectConstructor[objectNameParts[i]];
            if (ObjectConstructor === undefined) {
                // MODERNIZATION: Return null instead of Throwing Error
                console.error('Constructor for object "' + objectName + '" not found! Check if file is loaded.');
                return null;
            }
        }

        // Check that the constructor actually is a function.
        if (!$.isFunction(ObjectConstructor)) {
            console.error('The name "' + objectName + '" is found but is not a function!');
            return null;
        }

        return ObjectConstructor;
    };


    /**
     * Create a new instance of a proxy constructor.
     * @return {Function} a new proxy instance.
     */
    $.pkp.classes.Helper.getObjectProxyInstance = function() {
        /**
         * @constructor
         * @param {string} objectName The name of the proxied object.
         * @param {Array} args The arguments to be passed
         */
        var proxyConstructor = function(objectName, args) {
            this.objectName_ = objectName;
            // Call the constructor of the proxied object.
            // Safety check for parent
            if (this.parent && typeof this.parent.apply === 'function') {
                this.parent.apply(this, args);
            }
        };

        proxyConstructor.objectName_ = '';

        /*jslint unparam: true*/
        proxyConstructor.prototype.parent = function(opt_methodName, var_args) {
            return this;
        };
        /*jslint unparam: false*/

        return proxyConstructor;
    };


    /**
     * Inject (mix in) an interface into an object.
     * @param {Function} Constructor The target object's constructor.
     * @param {string} mixinObjectName The object name of interface
     */
    $.pkp.classes.Helper.injectMixin = function(Constructor, mixinObjectName) {
        // Retrieve an instance of the mix-in interface implementation.
        var mixin = $.pkp.classes.Helper.objectFactory(mixinObjectName, []);
        
        if (mixin) {
            // Inject the mix-in into the target constructor.
            $.extend(true, Constructor, mixin);
        }
    };


    /**
     * A function currying implementation.
     * MODERNIZED: Uses native .bind() if available for performance.
     * * @param {Function} fn A function to partially apply.
     * @param {Object} context Specifies the object which |this| should point to.
     * @return {!Function} A partially-applied form of the function.
     */
    $.pkp.classes.Helper.curry = function(fn, context) {
        // MODERNIZATION: Use native bind (ECMAScript 5+)
        if (fn.bind && typeof fn.bind === 'function') {
             // Create an array of arguments, starting from the 3rd argument (index 2)
             var args = Array.prototype.slice.call(arguments, 2);
             // Prepend the context to the arguments array for .bind logic
             args.unshift(context);
             return fn.bind.apply(fn, args);
        }

        // Fallback for extremely old browsers
        if (arguments.length > 2) {
            var boundArgs, newArgs;
            boundArgs = Array.prototype.slice.call(arguments, 2);
            return function() {
                newArgs = Array.prototype.slice.call(arguments);
                Array.prototype.unshift.apply(newArgs, boundArgs);
                return fn.apply(context, newArgs);
            };
        }
        return function() {
            return fn.apply(context, arguments);
        };
    };

/** @param {jQuery} $ jQuery closure. */
}(jQuery));