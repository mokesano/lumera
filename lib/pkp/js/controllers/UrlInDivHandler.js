/**
 * @file js/controllers/UrlInDivHandler.js
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UrlInDivHandler
 * @ingroup js_controllers
 *
 * @brief "URL in div" handler - HTTPS AUTO-FIX VERSION
 */
(function($) {

    /**
     * @constructor
     *
     * @extends $.pkp.classes.Handler
     *
     * @param {jQueryObject} $divElement the wrapped div element.
     * @param {Object} options options to be passed.
     */
    $.pkp.controllers.UrlInDivHandler = function($divElement, options) {
        this.parent($divElement, options);

        // Store the URL (e.g. for reloads)
        this.sourceUrl_ = options.sourceUrl;

        // MODERNIZATION: Bind the callback explicitly to 'this' scope
        this.handleLoadedContent_ = $.proxy(this.handleLoadedContent_, this);

        // Load the contents.
        this.reload();
    };
    
    // Inherit from Handler
    $.pkp.classes.Helper.inherits(
        $.pkp.controllers.UrlInDivHandler, $.pkp.classes.Handler);

    //
    // Private properties
    //
    /**
     * The URL to be used for data loaded into this div
     * @private
     * @type {?string}
     */
    $.pkp.controllers.UrlInDivHandler.prototype.sourceUrl_ = null;

    //
    // Public Methods
    //
    /**
     * Reload the div contents.
     */
    $.pkp.controllers.UrlInDivHandler.prototype.reload = function() {
        if (!this.sourceUrl_) {
            console.warn('UrlInDivHandler: No sourceUrl defined for reloading.');
            return;
        }

        var requestUrl = this.sourceUrl_;

        // --- HTTPS AUTO-FIX START ---
        // Jika halaman utama adalah HTTPS tapi request URL adalah HTTP,
        // paksa ubah request URL menjadi HTTPS agar tidak diblokir browser.
        if (location.protocol === 'https:' && requestUrl.indexOf('http:') === 0) {
            // Ganti http:// menjadi https://
            requestUrl = requestUrl.replace('http:', 'https:');
            console.warn('UrlInDivHandler: Auto-upgrading insecure URL to:', requestUrl);
        }
        // --- HTTPS AUTO-FIX END ---

        // Debugging log untuk melihat URL apa yang sebenarnya dipanggil
        console.log('UrlInDivHandler: Fetching content from:', requestUrl);

        $.get(requestUrl, this.callbackWrapper(this.handleLoadedContent_), 'json')
            .fail(function(jqXHR, textStatus, errorThrown) {
                // Log detail status untuk diagnosa
                console.error('UrlInDivHandler Failed:', 
                    '\nURL:', requestUrl,
                    '\nStatus:', jqXHR.status, 
                    '\nText:', textStatus, 
                    '\nError:', errorThrown
                );
            });
    };

    //
    // Private Methods
    //
    /**
     * Handle a callback after a load operation returns.
     *
     * @param {Object} ajaxContext The AJAX request context.
     * @param {Object} jsonData A parsed JSON response object.
     * @return {boolean} Message handling result.
     * @private
     */
    $.pkp.controllers.UrlInDivHandler.prototype.handleLoadedContent_ = 
            function(ajaxContext, jsonData) {
        
        // Safety check if 'this' context is lost
        if (!this.handleJson) {
             console.error("UrlInDivHandler context lost. 'this' is not the Handler instance.");
             return false;
        }

        var handledJsonData = this.handleJson(jsonData);
        
        if (handledJsonData.status === true) {
            // Smooth transition
            var $el = this.getHtmlElement();
            $el.hide().html(handledJsonData.content).fadeIn(400);
            $el.trigger('urlInDivLoaded'); 
        } else {
            // Alert that loading failed.
            var msg = handledJsonData.content ? handledJsonData.content : 'Failed to load content.';
            // console.warn('UrlInDivHandler content load reported failure:', msg);
            // alert(msg); // Optional: uncomment if you want popup alerts
        }

        return false;
    };

/** @param {jQuery} $ jQuery closure. */
}(jQuery));