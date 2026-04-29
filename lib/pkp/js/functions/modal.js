/**
 * modal.js
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Implementation of jQuery modals and other JS backend functions.
 * MODERNIZED FOR JQUERY 3.x COMPATIBILITY
 */

/**
 * modal
 * @param {string} url URL to load into the modal
 * @param {string} actType Type to define what callback should do (nothing|append|replace|remove)
 * @param {string} actOnId The ID on which to perform the action on callback
 * @param {Array} localizedButtons of translated 'Cancel/submit' strings
 * @param {string} callingElement Selector of the element that triggers the modal
 * @param {string} dialogTitle Set a custom title for the dialog
 */
function modal(url, actType, actOnId, localizedButtons, callingElement, dialogTitle) {
    $(function() {
        // Prepare variables
        var okButton = localizedButtons[0];
        var cancelButton = localizedButtons[1];
        var UID = Math.ceil(1000 * Math.random());

        // MODERNIZATION: Replace .die().live() with .off().on() attached to document
        // This handles dynamic elements correctly (formerly .live logic)
        $(document).off('click', callingElement).on('click', callingElement, function(e) {
            e.preventDefault(); // Prevent default link behavior

            // Dynamic title calculation based on clicked element
            var $this = $(this);
            var title = dialogTitle ? dialogTitle : $this.text();

            // Construct action to perform when OK and Cancel buttons are clicked
            var dialogOptions = {};
            if (actType == 'nothing') {
                dialogOptions[okButton] = function() {
                    $(this).dialog("close");
                };
            } else {
                dialogOptions[okButton] = function() {
                    submitJsonForm("#" + UID, actType, actOnId);
                };
                dialogOptions[cancelButton] = function() {
                    $(this).dialog("close");
                };
            }

            // Construct dialog
            $('<div id="' + UID + '"></div>').dialog({
                title: title,
                autoOpen: true,
                width: 700,
                modal: true,
                draggable: false,
                resizable: false,
                position: { my: "center", at: "center", of: window }, // jQuery UI 1.10+ position syntax
                buttons: dialogOptions,
                open: function(event, ui) {
                    $(this).css({ 'max-height': 600, 'overflow-y': 'auto', 'z-index': '10000' });
                    // Loading throbber
                    $(this).html("<div id='loading' class='deprecated_throbber'></div>");
                    $('#loading').show();

                    // Fetch Content
                    $.getJSON(url, function(jsonData) {
                        $('#loading').hide();
                        if (jsonData.status === true) {
                            $("#" + UID).html(jsonData.content);
                        } else {
                            alert(jsonData.content);
                        }
                    });
                },
                close: function() {
                    clearFormFields($("#" + UID).find('form'));
                    $(this).dialog('destroy').remove();
                }
            });

            // Handle custom dialog buttons inside the loaded content
            // Using delegation strictly on the dialog UID
            $(document).on('click', "#cancelModalButton", function() {
                $("#" + UID).dialog("close");
                return false;
            });

            $(document).on('click', "#submitModalButton", function() {
                submitJsonForm("#" + UID, actType, actOnId);
                return false;
            });

            return false;
        });
    });
}

/**
 * Opens a modal confirm box.
 * @param {string} url The URL to post to (null for simple alert)
 * @param {string} actType The action type (append|replace|remove|nothing|redirect)
 * @param {string} actOnId The ID on which to perform the action
 * @param {string} dialogText The text to display in the dialog
 * @param {Array} localizedButtons of translated 'Cancel/submit' strings
 * @param {string} callingElement The element that triggers the confirm dialog
 * @param {string} title The title of the dialog (optional)
 * @param {boolean} isForm Whether the action is a form submission (default: false)
 */
function modalConfirm(url, actType, actOnId, dialogText, localizedButtons, callingElement, title, isForm) {
    $(function() {
        // MODERNIZATION: Replace .live() with delegated .on()
        $(document).off('click', callingElement).on("click", callingElement, function(e) {
            e.preventDefault();
            var $caller = $(this);

            // Determine Title
            var currentTitle = title;
            if (!currentTitle) {
                currentTitle = $caller.text();
                if (currentTitle === '') {
                    currentTitle = $caller.attr('title');
                }
            }

            var okButton = localizedButtons[0];
            var cancelButton = localizedButtons[1];
            var dialogOptions = {};

            if (url == null) {
                // Simple alert
                dialogOptions[okButton] = function() {
                    $(this).dialog("close");
                };
            } else {
                // Action Dialog
                dialogOptions[okButton] = function() {
                    if (isForm) {
                        submitJsonForm(actOnId, actType, actOnId, url);
                    } else {
                        // Trigger start event
                        $(actOnId).triggerHandler('actionStart');

                        // Post to server
                        $.post(url, '', function(returnString) {
                            $(actOnId).triggerHandler('actionStop');
                            if (returnString.status) {
                                updateItem(actType, actOnId, returnString.content);
                            } else {
                                alert(returnString.content);
                            }
                        }, 'json');
                    }
                    $('#modalConfirm').dialog("close");
                };
                dialogOptions[cancelButton] = function() {
                    $(actOnId).triggerHandler('actionStop');
                    $(this).dialog("close");
                };
            }

            // Construct dialog
            // Remove existing if any to prevent duplicates
            $('#modalConfirm').remove();
            
            var $dialog = $('<div id="modalConfirm">' + dialogText + '</div>').dialog({
                title: currentTitle,
                autoOpen: true,
                modal: true,
                draggable: false,
                buttons: dialogOptions,
                close: function() {
                    $(this).dialog('destroy').remove();
                }
            });

            $dialog.dialog('open');
            return false;
        });
    });
}

/**
 * Submit a form that returns JSON data.
 * @param {string} formContainer The container of the form
 * @param {string} actType The action type (append|replace|remove|nothing|redirect)
 * @param {string} actOnId The ID on which to perform the action
 * @param {string} url The URL to submit the form to (optional)
 */
function submitJsonForm(formContainer, actType, actOnId, url) {
    var $formContainer = $(formContainer);
    var $form = $formContainer.find('form');
    
    // Check if validate plugin exists
    var validator = null;
    if ($.isFunction($form.validate)) {
        validator = $form.validate();
    }

    if (!url) {
        url = $form.attr('action');
    }

    // Modern jQuery valid check or fallback
    var isValid = true;
    if ($.isFunction($form.valid)) {
        isValid = $form.valid();
    }

    if (isValid) {
        var data = $form.serialize();
        $(actOnId).triggerHandler('actionStart');

        $.post(url, data, function(jsonData) {
            $(actOnId).triggerHandler('actionStop');
            
            if (jsonData.status == true) {
                var $updatedElement = updateItem(actType, actOnId, jsonData.content);
                
                // Check if the container is a dialog widget
                if ($formContainer.hasClass('ui-dialog-content')) {
                    $formContainer.dialog('close');
                } else if (typeof($formContainer.dialog) == 'function') {
                    // Fallback for older jQuery UI checking
                    try { $formContainer.dialog('close'); } catch(e) {}
                }

                $formContainer.triggerHandler('submitSuccessful', [$updatedElement]);
            } else {
                // Redisplay form on error
                $formContainer.html(jsonData.content);
                $formContainer.triggerHandler('submitFailed');
            }
        }, "json");
        validator = null;
    }
}

/**
 * Display a simple alert dialog
 * @param {string} dialogText The text to display
 * @param {string} localizedButtons Comma-separated list of localized button text (OK, Title)
 * @return {boolean} Always false
 */
function modalAlert(dialogText, localizedButtons) {
    var localizedText = localizedButtons.split(',');
    var okButton = localizedText[0];
    var title = localizedText[1] ? localizedText[1] : "Alert";

    var dialogOptions = {};
    dialogOptions[okButton] = function() {
        $(this).dialog("close");
    };

    $('#modalAlert').remove(); // Clean up previous
    var $dialog = $('<div id="modalAlert">' + dialogText + '</div>').dialog({
        title: title,
        autoOpen: false,
        modal: true,
        draggable: false,
        buttons: dialogOptions,
        close: function() {
            $(this).dialog('destroy').remove();
        }
    });

    $dialog.dialog('open');
    return false;
}

/**
 * Clear all fields of a form.
 * @param {jQuery} form The form to clear
 */
function clearFormFields(form) {
    $(':input', form).each(function() {
        var $input = $(this);
        if (!$input.is('.static')) {
            switch (this.type) {
                case 'password':
                case 'select-multiple':
                case 'select-one':
                case 'text':
                case 'textarea':
                    $input.val('');
                    break;
                case 'checkbox':
                case 'radio':
                    this.checked = false;
            }
        }
    });
}

/**
 * Implements a generic ajax action.
 * @param {string} actType The action type (post|get)
 * @param {string} actOnId The ID of the element to act on
 * @param {string} callingElement The element that triggers the ajax action
 * @param {string} url The URL to call
 * @param {string} data The data to send (for post)
 * @param {string} eventName The event name to bind to (default: click)
 * @param {string} form Optional form selector to serialize data from (for post)
 */
function ajaxAction(actType, actOnId, callingElement, url, data, eventName, form) {
    var eventHandler;
    
    if (actType == 'post') {
        eventHandler = function(e) {
            if(e) e.preventDefault();
            
            var $form = form ? $(form) : $(actOnId).find('form');

            if (!url) url = $form.attr("action");
            if (!data) data = $form.serialize();

            var isValid = true;
            if ($.isFunction($form.validate)) $form.validate();
            if ($.isFunction($form.valid)) isValid = $form.valid();

            if (isValid) {
                var $actOnId = $(actOnId);
                $actOnId.triggerHandler('actionStart');
                $.post(url, data, function(jsonData) {
                    $actOnId.triggerHandler('actionStop');
                    if (jsonData !== null) {
                        if (jsonData.status === true) {
                            $actOnId.replaceWith(jsonData.content);
                        } else {
                            alert(jsonData.content);
                        }
                    }
                }, 'json');
            }
            return false;
        };
    } else {
        eventHandler = function(e) {
            if(e) e.preventDefault();
            var $actOnId = $(actOnId);
            $actOnId.triggerHandler('actionStart');
            $.getJSON(url, function(jsonData) {
                $actOnId.triggerHandler('actionStop');
                if (jsonData !== null) {
                    if (jsonData.status === true) {
                        $actOnId.replaceWith(jsonData.content);
                    } else {
                        alert(jsonData.content);
                    }
                }
            });
            return false;
        };
    }

    if (!eventName) eventName = 'click';
    
    // MODERNIZATION: Replace .bind with .off().on()
    $(callingElement).each(function() {
        $(this).off(eventName).on(eventName, eventHandler);
    });
}

/**
 * Binds to the "actionStart" event.
 * @param {string} actOnId The ID of the element to act on
 */
function actionThrobber(actOnId) {
    // MODERNIZATION: Replace bind/unbind with on/off
    $(actOnId)
        .on('actionStart', function() {
            $(this).off('actionStart').html('<div id="actionThrobber" class="deprecated_throbber"></div>');
            $('#actionThrobber').show();
        })
        .on('actionStop', function() {
            $(this).off('actionStart').off('actionStop');
        });
}

/**
 * Update the DOM of a grid or list.
 * @param {string} actType The action to perform (append|replace|remove|nothing|redirect)
 * @param {string} actOnId The ID on which to perform the action
 * @param {string} content The content to use for the action
 * @return {jQuery} The updated element
 */
function updateItem(actType, actOnId, content) {
    var updatedItem;
    var $actElement = $(actOnId);
    
    switch (actType) {
        case 'append':
        case 'replace':
            var $empty = $actElement.closest('table').children('.empty');
            if (actType === 'append') {
                updatedItem = $actElement.append(content).children().last();
                $empty.hide();
            } else {
                updatedItem = $actElement.replaceWith(content);
                if ($(actOnId).children().length === 0) {
                    $empty.show();
                } else {
                    $empty.hide();
                }
            }
            break;

        case 'remove':
            if ($actElement.siblings().length == 0) {
                updatedItem = deleteElementById(actOnId, true);
            } else {
                updatedItem = deleteElementById(actOnId);
            }
            break;

        case 'nothing':
        case 'redirect':
            if (actType === 'redirect') {
                $(window.location).attr('href', content);
            }
            updatedItem = null;
            break;
    }

	// Trigger custom event so that clients can take
	// additional action.
    $(actOnId).triggerHandler('updatedItem', [actType]);
    return updatedItem;
}

/**
 * Deletes the given grid or list element from the DOM.
 * @param {string} element The element to delete
 * @param {boolean} showEmpty Whether to show the "empty" placeholder
 * @return {jQuery} The deleted element
 */
function deleteElementById(element, showEmpty) {
    var $deletedElement = $(element);
    var $emptyPlaceholder;
    if (showEmpty) {
        $emptyPlaceholder = $deletedElement.closest('table').children('.empty');
    }
    $deletedElement.fadeOut(500, function() {
        $(this).remove();
        if (showEmpty && $emptyPlaceholder) {
            $emptyPlaceholder.fadeIn(500);
        }
    });
    return $deletedElement;
}

/**
 * Implement the "extras on demand" design pattern.
 * @param {string} actOnId The ID of the element to act on
 */
function extrasOnDemand(actOnId) {
    // Modern Browser Detection for IE
    var ua = navigator.userAgent.toLowerCase();
    var isOldIE = /msie [1-7]\./.test(ua); // Checks IE 7 or lower

	/**
	 * Shows the extra options.
	 */
    function activateExtraOptions() {
        $(actOnId + ' .options-head .option-block-inactive').hide();
        $(actOnId + ' .options-head .option-block-active, ' + actOnId + ' .option-block').show();
        $(actOnId + ' .options-head').removeClass('inactive').addClass('active');
        $(actOnId + ' .ui-icon').removeClass('ui-icon-triangle-1-e').addClass('ui-icon-triangle-1-s');
        
        if (isOldIE) {
            setTimeout(function(){scrollToMakeVisible(actOnId)}, 500);
        } else {
            scrollToMakeVisible(actOnId);
        }
    }

	/**
	 * Hides the extra options.
	 */
    function deactivateExtraOptions() {
        $(actOnId + ' .options-head .option-block-active, ' + actOnId + ' .option-block').hide();
        $(actOnId + ' .options-head .option-block-inactive').show();
        $(actOnId + ' .options-head').removeClass('active').addClass('inactive');
        $(actOnId + ' .ui-icon').removeClass('ui-icon-triangle-1-s').addClass('ui-icon-triangle-1-e');
    }

	// De-activate the extra options on startup.
    deactivateExtraOptions();

	// Toggle the options when clicking on the header.
    $(actOnId + ' .options-head').click(function() {
        if ($(this).hasClass('active')) {
            deactivateExtraOptions();
        } else {
            activateExtraOptions();
        }
    });
}

/**
 * Scroll a scrollable element.
 */
function scrollToMakeVisible(actOnId) {
    var $contentBlock = $(actOnId);
    var $scrollable = $contentBlock.closest('.scrollable');
    
    if ($scrollable.length === 0) return; // Guard clause

    var contentBlockTop = $contentBlock.position().top;
    var scrollingBlockTop = $scrollable.position().top;
    var currentScrollingTop = $scrollable.scrollTop();

    if (contentBlockTop > scrollingBlockTop) {
        var hiddenPixels = Math.ceil(contentBlockTop + $contentBlock.height() - $scrollable.height());
        if (hiddenPixels > 0) {
            $scrollable.scrollTop(currentScrollingTop + hiddenPixels);
        }
    } else {
        var newScrollingTop = Math.max(Math.floor(currentScrollingTop + contentBlockTop - scrollingBlockTop), 0);
        $scrollable.scrollTop(newScrollingTop);
    }
}

/**
 * Custom jQuery plug-in: selectRange
 */
(function($) {

	/**
	 * Custom jQuery plug-in that marks the matched elements
	 * Code adapted from phpBB, thanks to the phpBB group.
	 * @return {jQuery}
	 */
    $.fn.selectRange = function() {
        return this.each(function() {
            if (window.getSelection) {
                var s = window.getSelection();
                if (s.setBaseAndExtent) {
                    s.setBaseAndExtent(this, 0, this, this.innerText.length - 1);
                } else {
                    var r = document.createRange();
                    r.selectNodeContents(this);
                    s.removeAllRanges();
                    s.addRange(r);
                }
            } else if (document.getSelection) {
                var s = document.getSelection();
                var r = document.createRange();
                r.selectNodeContents(this);
                s.removeAllRanges();
                s.addRange(r);
            } else if (document.selection) {
                var r = document.body.createTextRange();
                r.moveToElementText(this);
                r.select();
            }
        });
    };

    /**
     * BROWSER DETECTION & CSS INJECTION - MODERNIZED
     * Replaces $.browser with navigator.userAgent regex
     * Adds version detection for major browsers
     * 
	 * Add a class to the <body> tag that identifies the browser
	 * to facilitate browser-specific CSS.
	 * Thanks to author Jon Hobbs-Smith who put this
	 * code in the public domain.
	 */
    $(function() {
        var ua = navigator.userAgent.toLowerCase();
        
        // Detection Logic
        var isChrome = /chrome/.test(ua) && /google inc/.test(navigator.vendor.toLowerCase());
        var isSafari = /safari/.test(ua) && !isChrome;
        var isFirefox = /firefox/.test(ua);
        var isIE = /msie|trident/.test(ua);
        var isOpera = /opera/.test(ua) || /opr/.test(ua);

        var $body = $('body');

        if (isIE) {
            $body.addClass('browserIE');
            // Try to extract version roughly for old CSS hooks
            var ieVersion = (ua.match(/(?:msie |rv:)(\d+)/) || [])[1];
            if (ieVersion) $body.addClass('browserIE' + ieVersion.substring(0,1));
        }

        if (isChrome) {
            $body.addClass('browserChrome');
            // Extract version
            var chromeVer = (ua.match(/chrome\/(\d+)/) || [])[1];
            if (chromeVer) $body.addClass('browserChrome' + chromeVer.substring(0,1));
        }

        if (isSafari) {
            $body.addClass('browserSafari');
            var safVer = (ua.match(/version\/(\d+)/) || [])[1];
            if (safVer) $body.addClass('browserSafari' + safVer.substring(0,1));
        }

        if (isFirefox) {
            $body.addClass('browserFirefox');
            var ffVer = (ua.match(/firefox\/(\d+)/) || [])[1];
            if (ffVer) $body.addClass('browserFirefox' + ffVer.substring(0,1));
        } else if (/mozilla/.test(ua) && !isChrome && !isSafari && !isIE) {
             $body.addClass('browserMozilla');
        }

        if (isOpera) {
            $body.addClass('browserOpera');
        }
    });

    /**
     * jQuery Hotkeys Plugin
     * Left largely intact as it generally works, 
     * but ensure basic event compatibility.
     */
    $.hotkeys = {
        version: "0.8",
        specialKeys: {
            8: "backspace", 9: "tab", 13: "return", 16: "shift", 17: "ctrl", 18: "alt", 19: "pause",
            20: "capslock", 27: "esc", 32: "space", 33: "pageup", 34: "pagedown", 35: "end", 36: "home",
            37: "left", 38: "up", 39: "right", 40: "down", 45: "insert", 46: "del",
            96: "0", 97: "1", 98: "2", 99: "3", 100: "4", 101: "5", 102: "6", 103: "7",
            104: "8", 105: "9", 106: "*", 107: "+", 109: "-", 110: ".", 111 : "/",
            112: "f1", 113: "f2", 114: "f3", 115: "f4", 116: "f5", 117: "f6", 118: "f7", 119: "f8",
            120: "f9", 121: "f10", 122: "f11", 123: "f12", 144: "numlock", 145: "scroll", 191: "/", 224: "meta"
        },
        shiftNums: {
            "`": "~", "1": "!", "2": "@", "3": "#", "4": "$", "5": "%", "6": "^", "7": "&",
            "8": "*", "9": "(", "0": ")", "-": "_", "=": "+", ";": ": ", "'": "\"", ",": "<",
            ".": ">",  "/": "?",  "\\": "|"
        }
    };

   	/**
	 * @param {Object} handleObj
     * Key event handler
     * @return {undefined}
	 */
    function keyHandler( handleObj ) {
        if ( typeof handleObj.data !== "string" ) return;

        var origHandler = handleObj.handler,
            keys = handleObj.data.toLowerCase().split(" ");

        handleObj.handler = function( event ) {
            if ( this !== event.target && (/textarea|select/i.test( event.target.nodeName ) ||
                 event.target.type === "text") ) {
                return;
            }

            var special = event.type !== "keypress" && $.hotkeys.specialKeys[ event.which ],
                character = String.fromCharCode( event.which ).toLowerCase(),
                key, modif = "", possible = {};

            if ( event.altKey && special !== "alt" ) modif += "alt+";
            if ( event.ctrlKey && special !== "ctrl" ) modif += "ctrl+";
            if ( event.metaKey && !event.ctrlKey && special !== "meta" ) modif += "meta+";
            if ( event.shiftKey && special !== "shift" ) modif += "shift+";

            if ( special ) {
                possible[ modif + special ] = true;
            } else {
                possible[ modif + character ] = true;
                possible[ modif + $.hotkeys.shiftNums[ character ] ] = true;
                if ( modif === "shift+" ) {
                    possible[ $.hotkeys.shiftNums[ character ] ] = true;
                }
            }

            for ( var i = 0, l = keys.length; i < l; i++ ) {
                if ( possible[ keys[i] ] ) {
                    return origHandler.apply( this, arguments );
                }
            }
        };
    }

    $.each([ "keydown", "keyup", "keypress" ], function() {
        $.event.special[ this ] = { add: keyHandler };
    });

})(jQuery);