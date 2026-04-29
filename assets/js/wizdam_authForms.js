/**
 * Modern Auth Forms JavaScript untuk OJS v2.4.8.2
 * Production-ready authentication forms with modern UI components
 * 
 * @fileoverview Modern auth forms with floating labels, password validation,
 * and prof checkbox designs for OJS registration system.
 * @version 1.3.6
 * @author Rochmady and Wizdam OJS Theme Developer
 * @requires Kopi plus Rokok
 */

(function() {
    'use strict';

    /**
     * AuthForms namespace for form utilities
     * @namespace AuthForms
     * @description Global namespace for authentication form utilities
     */
    window.AuthForms = window.AuthForms || {};

    /**
     * Configuration object for AuthForms
     * @type {Object}
     * @memberof AuthForms
     */
    AuthForms.config = {
        siteKey: '',
        proxyUrl: '',
        theme: 'auto',
        size: 'normal'
    };

    /**
     * Set configuration for AuthForms
     * @function
     * @memberof AuthForms
     * @param {Object} config - Configuration object
     * @returns {void}
     */
    AuthForms.setConfig = function(config) {
        Object.assign(this.config, config);
    };

    /**
     * Password field configurations
     * @type {Array}
     * @memberof AuthForms
     */
    AuthForms.passwordFields = [
        {
            id: 'password',
            strengthIndicatorId: 'passwordStrengthIndicator',
            strengthLabelId: 'strengthLabel',
            isMainPassword: true,
            showRequirements: true
        },
        {
            id: 'loginPassword', 
            strengthIndicatorId: 'loginPasswordStrengthIndicator',
            strengthLabelId: 'loginStrengthLabel',
            isMainPassword: true,
            showRequirements: false
        },
        {
            id: 'oldPassword',
            strengthIndicatorId: 'oldPasswordStrengthIndicator', 
            strengthLabelId: 'oldStrengthLabel',
            isMainPassword: true,
            showRequirements: false
        }
    ];

    /**
     * ENHANCED: Persistent Early Capture System for Messages
     * Captures error/success messages before they are removed by the system
     */
    (function() {
        // Multiple storage mechanisms for redundancy
        window.earlyCapturedMessages = new Map();
        window.backupCapturedMessages = {};
        
        function captureMessagesImmediately() {
            console.log('[Wizdam Auth] === PERSISTENT EARLY CAPTURE ===');
            
            let attempts = 0;
            const maxAttempts = 50;
            
            // All known password field IDs across all OJS auth pages
            const targetFieldIds = ['loginPassword', 'oldPassword', 'password', 'password2'];
            
            function tryCapture() {
                attempts++;
                console.log(`[Wizdam Auth] Persistent capture attempt ${attempts}`);
                
                targetFieldIds.forEach(function(fieldId) {
                    // Skip if already captured
                    if (window.earlyCapturedMessages.has(fieldId)) return;
                    
                    const field = document.getElementById(fieldId);
                    if (!field) return;
                    
                    const fieldContainer = field.parentElement;
                    const errorMessage   = fieldContainer.querySelector('.error-message');
                    const successMessage = fieldContainer.querySelector('.success-message');
                    
                    console.log(`[Wizdam Auth]   ${fieldId}: error=${!!errorMessage}, success=${!!successMessage}`);
                    
                    if (errorMessage || successMessage) {
                        const capturedData = {
                            errorElement:   errorMessage   ? errorMessage.cloneNode(true)   : null,
                            successElement: successMessage ? successMessage.cloneNode(true) : null,
                            capturedAt:     Date.now(),
                            errorText:      errorMessage   ? errorMessage.textContent   : null,
                            successText:    successMessage ? successMessage.textContent : null
                        };
                        
                        window.earlyCapturedMessages.set(fieldId, capturedData);
                        window.backupCapturedMessages[fieldId] = capturedData;
                        
                        console.log(`[Wizdam Auth]   ✅ Persistent captured ${fieldId}`);
                    }
                });
                
                // Count how many target fields actually exist on this page
                const existingFields = targetFieldIds.filter(function(id) {
                    return !!document.getElementById(id);
                });
                
                // Keep trying if we haven't captured all existing fields yet
                const allCaptured = existingFields.every(function(id) {
                    return window.earlyCapturedMessages.has(id);
                });
                
                if (!allCaptured && attempts < maxAttempts) {
                    setTimeout(tryCapture, 100);
                } else {
                    console.log('[Wizdam Auth] === PERSISTENT CAPTURE COMPLETE ===');
                    console.log('[Wizdam Auth] Captured in Map:', window.earlyCapturedMessages.size);
                    console.log('[Wizdam Auth] Captured in backup:', Object.keys(window.backupCapturedMessages).length);
                }
            }
            
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', tryCapture);
            } else {
                tryCapture();
            }
        }
        
        captureMessagesImmediately();
    })();

    /**
     * ENHANCED: Transfer Early Captured Messages
     * Transfers captured messages to originalMessages with fallback systems
     */
    function transferEarlyCapturedMessages() {
        console.log('[Wizdam Auth] === ENHANCED TRANSFER ===');
        
        // Try Map storage first
        if (window.earlyCapturedMessages && window.earlyCapturedMessages.size > 0) {
            console.log('[Wizdam Auth] Using Map storage');
            window.earlyCapturedMessages.forEach(function(capturedData, fieldId) {
                if (!originalMessages.has(fieldId)) {
                    originalMessages.set(fieldId, {
                        errorElement: capturedData.errorElement,
                        successElement: capturedData.successElement
                    });
                    console.log(`[Wizdam Auth] ✅ Transferred ${fieldId} from Map`);
                }
            });
            return;
        }
        
        // Fallback to backup object
        if (window.backupCapturedMessages && Object.keys(window.backupCapturedMessages).length > 0) {
            console.log('[Wizdam Auth] Using backup object storage');
            Object.keys(window.backupCapturedMessages).forEach(function(fieldId) {
                if (!originalMessages.has(fieldId)) {
                    const capturedData = window.backupCapturedMessages[fieldId];
                    originalMessages.set(fieldId, {
                        errorElement: capturedData.errorElement,
                        successElement: capturedData.successElement
                    });
                    console.log(`[Wizdam Auth] ✅ Transferred ${fieldId} from backup`);
                }
            });
            return;
        }
        
        // No messages found
        console.log('[Wizdam Auth] ❌ No captured messages found');
    }

    /**
     * Initialize all form components when DOM is ready
     * @function
     * @returns {void}
     */
    function initializeForm() {
        setupFloatingLabels();
        setupPasswordToggle();
        setupModernCheckboxes();
        setupReviewerInterests();
        setupPrivacyDialog();
        setupFormValidation();
        initializePasswordRequirements();
        // CRITICAL ORDER: initializeFieldMessages (which calls storeOriginalMessages) MUST
        // run before initializePasswordStrengthIndicator (which calls updateFieldMessages
        // and removes error/success elements from the DOM).
        initializeFieldMessages();
        initializePasswordStrengthIndicator();
        initializeTurnstile();
        setupTurnstileResize();
        setupEnhancedRememberMe();
        setupChangePasswordValidation();
    }

    /**
     * ENHANCED: Setup change password specific validation
     * @function
     * @returns {void}
     */
    function setupChangePasswordValidation() {
        const oldPasswordField = document.getElementById('oldPassword');
        const newPasswordField = document.getElementById('password');
        
        // Only setup if both fields exist (change password page)
        if (!oldPasswordField || !newPasswordField) return;
        
        // Add event listeners for change password validation
        newPasswordField.addEventListener('input', function() {
            validateNewPasswordAgainstOld(this, oldPasswordField);
        });
        
        newPasswordField.addEventListener('blur', function() {
            validateNewPasswordAgainstOld(this, oldPasswordField);
        });
        
        oldPasswordField.addEventListener('input', function() {
            // Re-validate new password when old password changes
            if (newPasswordField.value.trim()) {
                validateNewPasswordAgainstOld(newPasswordField, this);
            }
        });
        
        oldPasswordField.addEventListener('blur', function() {
            // Re-validate new password when old password loses focus
            if (newPasswordField.value.trim()) {
                validateNewPasswordAgainstOld(newPasswordField, this);
            }
        });
    }

    /**
     * ENHANCED: Validate new password against old password
     * @function
     * @param {HTMLElement} newPasswordField - New password input element
     * @param {HTMLElement} oldPasswordField - Old password input element
     * @returns {void}
     */
    function validateNewPasswordAgainstOld(newPasswordField, oldPasswordField) {
        const newPassword = newPasswordField.value.trim();
        const oldPassword = oldPasswordField.value.trim();
        
        // Don't validate if either field is empty
        if (!newPassword || !oldPassword) {
            clearPasswordSameAsOldError(newPasswordField);
            return;
        }
        
        // Check if new password is same as old password
        if (newPassword === oldPassword) {
            showPasswordSameAsOldError(newPasswordField);
        } else {
            clearPasswordSameAsOldError(newPasswordField);
        }
    }

    /**
     * ENHANCED: Show error when new password is same as old password
     * @function
     * @param {HTMLElement} newPasswordField - New password input element
     * @returns {void}
     */
    function showPasswordSameAsOldError(newPasswordField) {
        console.log('[Wizdam Auth] === showPasswordSameAsOldError called ===');
        
        // Ensure captured messages are transferred
        transferEarlyCapturedMessages();
        
        // Set flag and state
        newPasswordField.dataset.samePasswordError = 'true';
        newPasswordField.classList.add('error');
        newPasswordField.classList.remove('success');
        newPasswordField.classList.add('touched');
        
        console.log('[Wizdam Auth] Flag set, checking originalMessages now:');
        const fieldKey = newPasswordField.id || newPasswordField.name;
        const storedMessages = originalMessages.get(fieldKey);
        console.log(`[Wizdam Auth] storedMessages for ${fieldKey}:`, storedMessages);
        
        // Call updateFieldMessages
        console.log('[Wizdam Auth] Calling updateFieldMessages...');
        updateFieldMessages(newPasswordField);
        console.log('[Wizdam Auth] updateFieldMessages completed');
    }

    /**
     * ENHANCED: Clear error when new password is different from old password
     * @function
     * @param {HTMLElement} newPasswordField - New password input element
     * @returns {void}
     */
    function clearPasswordSameAsOldError(newPasswordField) {
        // Clear same-password error flag
        delete newPasswordField.dataset.samePasswordError;
        
        // Re-evaluate field state (let other validation take over)
        updateFieldMessages(newPasswordField);
    }

    /**
     * Initialize password strength indicator for all password fields
     * @function
     * @returns {void}
     */
    function initializePasswordStrengthIndicator() {
        AuthForms.passwordFields.forEach(function(fieldConfig) {
            const passwordInput = document.getElementById(fieldConfig.id);
            const strengthIndicator = document.getElementById(fieldConfig.strengthIndicatorId);
            
            if (passwordInput) {
                const currentPassword = passwordInput.value || '';
                
                // Update password requirements if this field shows them
                if (fieldConfig.showRequirements) {
                    updatePasswordRequirements(currentPassword);
                }
                
                // Update input state and field messages
                updateInputState(passwordInput);
                updateFieldMessages(passwordInput);
                
                // Add event listeners
                passwordInput.addEventListener('input', function() {
                    if (fieldConfig.showRequirements) {
                        updatePasswordRequirements(this.value);
                    }
                    updatePasswordStrengthForField(this, fieldConfig);
                    updateFieldMessages(this);
                });
                
                passwordInput.addEventListener('blur', function() {
                    updateFieldMessages(this);
                });
                
                // Initialize strength indicator for this field
                updatePasswordStrengthForField(passwordInput, fieldConfig);
            }
        });
    }

    /**
     * Get username info from form.
     * Tries multiple selectors to handle different OJS form templates.
     * Returns an object so callers can distinguish:
     *   { exists: false }            → no username field on this page
     *   { exists: true, value: '' }  → field exists but is empty
     *   { exists: true, value: 'x' } → field exists and has a value
     * @function
     * @returns {{ exists: boolean, value: string }}
     */
    function getUsernameInfo() {
        // Try common OJS username field selectors in priority order
        const usernameField =
            document.getElementById('username') ||
            document.querySelector('input[name="username"]') ||
            document.querySelector('input[id$="username" i]') ||
            document.querySelector('input[id*="Username"]') ||
            document.querySelector('input[name*="username" i]');

        if (!usernameField) {
            return { exists: false, value: '' };
        }
        return { exists: true, value: usernameField.value.trim().toLowerCase() };
    }

    /**
     * Get username value from form (legacy helper, returns '' when field absent)
     * @function
     * @returns {string} username in lowercase, empty string if not found
     */
    function getUsernameValue() {
        return getUsernameInfo().value;
    }

    /**
     * Update password strength for specific field.
     * Password has 6 requirements:
     *   - 5 mandatory: length, uppercase, lowercase, number, special char
     *   - 1 optional:  different from username
     *
     * Strength levels:
     *   0–2 / 5 mandatory       → Very Weak  (1 segment)
     *   3 / 5                   → Weak        (2 segments)
     *   4 / 5                   → Fair        (3 segments)
     *   5 / 5, same as username → Good        (4 segments)
     *   5 / 5 + diff username   → Strong      (5 segments)
     *   5 / 5 + diff username
     *        + length > 12
     *        + no predictable patterns → Very Strong (6 segments)
     *
     * @function
     * @param {HTMLElement} passwordInput - Password input element
     * @param {Object} fieldConfig - Field configuration
     * @returns {void}
     */
    function updatePasswordStrengthForField(passwordInput, fieldConfig) {
        const password = passwordInput.value;
        const strengthIndicator = document.getElementById(fieldConfig.strengthIndicatorId);
        const strengthLabel = document.getElementById(fieldConfig.strengthLabelId);
        
        if (!strengthIndicator) return;
        
        const strengthSegments = strengthIndicator.querySelectorAll('.strength-segment');
        
        // Clear all segments
        strengthSegments.forEach(function(segment) {
            segment.classList.remove('active');
        });
        
        if (!password || password.length === 0) {
            if (strengthLabel) {
                strengthLabel.textContent = '';
            }
            strengthIndicator.setAttribute('data-strength', 'empty');
            return;
        }
        
        // --- 5 Mandatory requirements ---
        const mandatoryReqs = [
            password.length >= 8,           // length
            /[A-Z]/.test(password),          // uppercase
            /[a-z]/.test(password),          // lowercase
            /[0-9]/.test(password),          // number
            /[^A-Za-z0-9]/.test(password)    // special character
        ];
        
        // --- 1 Optional requirement: different from username ---
        // Rules:
        //   - No username field on page       → N/A, treat as met
        //   - Username field exists but empty → NOT met (wait for user to type)
        //   - Username >= 3 chars: password must not CONTAIN username (case-insensitive)
        //     and username must not contain password (catches "Password1234!" ⊃ "password")
        //   - Username < 3 chars: exact match only (avoid false positives on short names)
        const usernameInfo = getUsernameInfo();
        let diffFromUsername;
        if (!usernameInfo.exists) {
            diffFromUsername = true;
        } else if (usernameInfo.value.length === 0) {
            diffFromUsername = false;
        } else {
            const pwLower = password.toLowerCase();
            const unLower = usernameInfo.value; // already lowercased in getUsernameInfo
            if (unLower.length >= 3) {
                diffFromUsername = !pwLower.includes(unLower) && !unLower.includes(pwLower);
            } else {
                diffFromUsername = pwLower !== unLower;
            }
        }
        
        const mandatoryMet    = mandatoryReqs.filter(Boolean).length;
        const allMandatoryMet = mandatoryMet === 5;

        // --- "Unique" bonus: length > 12 AND no predictable patterns ---
        // Checks performed:
        //   1. Length > 12
        //   2. No sequential keyboard/alphabet runs of 3+ chars (abc, 123, qwerty…)
        //   3. No repeated character blocks of 3+ (aaa, 111, !!!)
        //   4. No run of 4+ consecutive chars of the same type (e.g. all-letters run)
        var isUnique = (function(pw) {
            if (pw.length <= 12) return false;

            var lower = pw.toLowerCase();

            // Sequential alphabet/numeric runs (forward and reverse)
            var seqAlpha = 'abcdefghijklmnopqrstuvwxyz';
            var seqNum   = '0123456789';
            var seqKeys  = ['qwertyuiop', 'asdfghjkl', 'zxcvbnm',  // keyboard rows
                            'qazwsxedcrfvtgbyhnujmikolp'];           // keyboard columns
            var seqSets  = [seqAlpha, seqNum].concat(seqKeys);

            for (var s = 0; s < seqSets.length; s++) {
                var seq    = seqSets[s];
                var revSeq = seq.split('').reverse().join('');
                for (var i = 0; i <= lower.length - 3; i++) {
                    var chunk = lower.substring(i, i + 3);
                    if (seq.indexOf(chunk) !== -1 || revSeq.indexOf(chunk) !== -1) {
                        return false; // sequential pattern found
                    }
                }
            }

            // Repeated characters (e.g. "aaa", "111", "!!!")
            if (/(.)\1{2,}/.test(pw)) return false;

            // Long run (4+) of same character type without interruption
            if (/[a-zA-Z]{5,}/.test(pw) && !/[0-9!@#$%^&*()\-_=+\[\]{};':"\\|,.<>\/?`~]/.test(pw)) {
                return false;
            }

            return true;
        }(password));

        let strengthLevel  = '';
        let activeSegments = 0;

        if (mandatoryMet <= 2) {
            // 0–2 / 5 mandatory met
            strengthLevel  = 'Very Weak';
            activeSegments = 1;
        } else if (mandatoryMet === 3) {
            strengthLevel  = 'Weak';
            activeSegments = 2;
        } else if (mandatoryMet === 4) {
            strengthLevel  = 'Fair';
            activeSegments = 3;
        } else if (allMandatoryMet && !diffFromUsername) {
            // All 5 mandatory met but password is too similar to username
            strengthLevel  = 'Good';
            activeSegments = 4;
        } else if (allMandatoryMet && diffFromUsername && !isUnique) {
            // All 5 mandatory + different from username, but not "unique"
            strengthLevel  = 'Strong';
            activeSegments = 5;
        } else if (allMandatoryMet && diffFromUsername && isUnique) {
            // All 5 mandatory + different from username + long & unpredictable
            strengthLevel  = 'Very Strong';
            activeSegments = 6;
        }
        
        // Activate segments
        strengthSegments.forEach(function(segment, index) {
            if (index < activeSegments) {
                segment.classList.add('active');
            }
        });
        
        // Update label
        if (strengthLabel) {
            strengthLabel.textContent = strengthLevel;
            strengthLabel.setAttribute('data-level', strengthLevel.toLowerCase().replace(' ', '-'));
        }
        
        strengthIndicator.setAttribute('data-strength', strengthLevel.toLowerCase().replace(' ', '-'));
    }

    /**
     * Store original message elements on initialization
     * @type {Map}
     */
    const originalMessages = new Map();

    /**
     * Store original message elements for reuse.
     * Scans the DOM first, then falls back to early-captured messages for any
     * password fields whose DOM elements may already have been removed.
     * @function
     * @returns {void}
     */
    function storeOriginalMessages() {
        // 1. DOM scan — catches all regular form fields
        const formFields = document.querySelectorAll('.form-control');
        
        formFields.forEach(function(field) {
            const fieldContainer = field.parentElement;
            const errorMessage   = fieldContainer.querySelector('.error-message');
            const successMessage = fieldContainer.querySelector('.success-message');
            
            const fieldKey = field.id || field.name || field.className;
            
            if ((errorMessage || successMessage) && !originalMessages.has(fieldKey)) {
                originalMessages.set(fieldKey, {
                    errorElement:   errorMessage   ? errorMessage.cloneNode(true)   : null,
                    successElement: successMessage ? successMessage.cloneNode(true) : null
                });
            }
        });

        // 2. Fallback: pull from early-capture for any field not yet stored
        //    (handles cases where messages were removed before the DOM scan ran)
        if (window.earlyCapturedMessages && window.earlyCapturedMessages.size > 0) {
            window.earlyCapturedMessages.forEach(function(capturedData, fieldId) {
                if (!originalMessages.has(fieldId)) {
                    originalMessages.set(fieldId, {
                        errorElement:   capturedData.errorElement,
                        successElement: capturedData.successElement
                    });
                    console.log('[Wizdam Auth] storeOriginalMessages: pulled ' + fieldId + ' from early capture');
                }
            });
        }
    }

    /**
     * Insert message element in correct position
     * @function
     * @param {HTMLElement} container - Field container
     * @param {HTMLElement} messageElement - Message element to insert
     * @returns {void}
     */
    function insertMessageElement(container, messageElement) {
        const helpText = container.querySelector('.form-help-text');
        if (helpText) {
            container.insertBefore(messageElement, helpText);
        } else {
            container.appendChild(messageElement);
        }
    }

    /**
     * ENHANCED: Update field messages based on field state
     * @function
     * @param {HTMLElement} field - The form field
     * @returns {void}
     */
    function updateFieldMessages(field) {
        const fieldContainer = field.parentElement;
        const currentErrorMessage = fieldContainer.querySelector('.error-message:not(.same-password-error)');
        const currentSuccessMessage = fieldContainer.querySelector('.success-message');
        
        // Remove existing messages from DOM (but preserve same-password-error)
        if (currentErrorMessage && currentErrorMessage.parentNode) {
            currentErrorMessage.parentNode.removeChild(currentErrorMessage);
        }
        if (currentSuccessMessage && currentSuccessMessage.parentNode) {
            currentSuccessMessage.parentNode.removeChild(currentSuccessMessage);
        }
        
        const value = field.value.trim();
        const isRequired = field.hasAttribute('required');
        const isError = field.classList.contains('error');
        const isSuccess = field.classList.contains('success');
        const isTouched = field.classList.contains('touched');
        const isFocused = field.classList.contains('focused');
        
        // Check if field has same-password error (for change password)
        const hasSamePasswordError = field.dataset.samePasswordError === 'true';
        
        // Get stored messages
        const fieldKey = field.id || field.name || field.className;
        const storedMessages = originalMessages.get(fieldKey);
        
        // Special handling for all password fields
        // NOTE: check by id/name, NOT field.type, because the toggle may have changed type to 'text'
        const isLoginPassword   = field.id === 'loginPassword';
        const isRegisterPassword = field.id === 'password'    || field.name === 'password';
        const isChangePassword   = field.id === 'oldPassword' || field.name === 'oldPassword';
        const isPasswordField    = isLoginPassword || isRegisterPassword || isChangePassword;

        if (isPasswordField) {

            // --- Change-password: same-as-old error ---
            if (hasSamePasswordError) {
                if (typeof transferEarlyCapturedMessages === 'function') {
                    transferEarlyCapturedMessages();
                }
                if (storedMessages && storedMessages.errorElement) {
                    const errorClone = storedMessages.errorElement.cloneNode(true);
                    insertMessageElement(fieldContainer, errorClone);
                }
                field.classList.add('error');
                field.classList.remove('success');
                return;
            }

            // --- LOGIN password: no strength requirements, just check not empty ---
            if (isLoginPassword) {
                if (isRequired && !value && isTouched && !isFocused && storedMessages && storedMessages.errorElement) {
                    const errorClone = storedMessages.errorElement.cloneNode(true);
                    insertMessageElement(fieldContainer, errorClone);
                    field.classList.add('error');
                    field.classList.remove('success');
                } else if (value && isTouched) {
                    // Any non-empty password is acceptable on the login page
                    if (storedMessages && storedMessages.successElement) {
                        const successClone = storedMessages.successElement.cloneNode(true);
                        insertMessageElement(fieldContainer, successClone);
                    }
                    field.classList.add('success');
                    field.classList.remove('error');
                } else {
                    field.classList.remove('error', 'success');
                }
                return;
            }

            // --- REGISTER / CHANGE password: validate 5 mandatory requirements ---
            if (isRequired && !value && isTouched && !isFocused && storedMessages && storedMessages.errorElement) {
                const errorClone = storedMessages.errorElement.cloneNode(true);
                insertMessageElement(fieldContainer, errorClone);
                field.classList.add('error');
                field.classList.remove('success');
                return;
            } else if (value && isTouched) {
                const mandatoryReqs = [
                    value.length >= 8,
                    /[A-Z]/.test(value),
                    /[a-z]/.test(value),
                    /[0-9]/.test(value),
                    /[^A-Za-z0-9]/.test(value)
                ];
                const allMet = mandatoryReqs.every(Boolean);

                if (allMet && storedMessages && storedMessages.successElement) {
                    const successClone = storedMessages.successElement.cloneNode(true);
                    insertMessageElement(fieldContainer, successClone);
                    field.classList.add('success');
                    field.classList.remove('error');
                    return;
                } else if (!allMet && storedMessages && storedMessages.errorElement) {
                    const errorClone = storedMessages.errorElement.cloneNode(true);
                    insertMessageElement(fieldContainer, errorClone);
                    field.classList.add('error');
                    field.classList.remove('success');
                    return;
                }
            } else {
                field.classList.remove('error', 'success');
                return;
            }
        }
        
        // Special handling for password confirmation
        if (field.name === 'password2' || field.name === 'confirmPassword' || field.id === 'password2') {
            const passwordField = document.getElementById('password');
            const passwordValue = passwordField ? passwordField.value.trim() : '';
            
            if (isRequired && !value && isTouched && !isFocused && storedMessages && storedMessages.errorElement) {
                const errorClone = storedMessages.errorElement.cloneNode(true);
                insertMessageElement(fieldContainer, errorClone);
                field.classList.add('error');
                field.classList.remove('success');
                return;
            } else if (value && isTouched) {
                if (passwordValue && value === passwordValue && storedMessages && storedMessages.successElement) {
                    const successClone = storedMessages.successElement.cloneNode(true);
                    insertMessageElement(fieldContainer, successClone);
                    field.classList.add('success');
                    field.classList.remove('error');
                    return;
                } else if (passwordValue && value !== passwordValue && storedMessages && storedMessages.errorElement) {
                    const errorClone = storedMessages.errorElement.cloneNode(true);
                    insertMessageElement(fieldContainer, errorClone);
                    field.classList.add('error');
                    field.classList.remove('success');
                    return;
                } else if (!passwordValue) {
                    field.classList.remove('error', 'success');
                    return;
                }
            } else {
                field.classList.remove('error', 'success');
                return;
            }
        }
        
        // Regular field handling for other input types
        if (isRequired && !value && isTouched && !isFocused && storedMessages && storedMessages.errorElement) {
            const errorClone = storedMessages.errorElement.cloneNode(true);
            insertMessageElement(fieldContainer, errorClone);
            field.classList.add('error');
            field.classList.remove('success');
        }
        else if (value && !isError && storedMessages && storedMessages.successElement) {
            const successClone = storedMessages.successElement.cloneNode(true);
            insertMessageElement(fieldContainer, successClone);
            field.classList.add('success');
            field.classList.remove('error');
        }
        else if (value || !isTouched) {
            field.classList.remove('error');
            if (value && !isRequired) {
                field.classList.add('success');
            } else if (value && isRequired) {
                field.classList.add('success');
            } else {
                field.classList.remove('success');
            }
        }
    }

    /**
     * Initialize field messages for all form fields
     * @function
     * @returns {void}
     */
    function initializeFieldMessages() {
        // Store original messages first
        storeOriginalMessages();
        
        const formFields = document.querySelectorAll('.form-control');
        
        formFields.forEach(function(field) {
            updateFieldMessages(field);
            
            field.addEventListener('blur', function() {
                updateFieldMessages(this);
            });
            
            field.addEventListener('input', function() {
                updateFieldMessages(this);
            });
        });
    }

    /**
     * Setup floating label functionality for form inputs
     * @function
     * @returns {void}
     */
    function setupFloatingLabels() {
        const inputs = document.querySelectorAll('.form-control');
        
        inputs.forEach(function(input) {
            updateInputState(input);
            
            input.addEventListener('input', function() {
                updateInputState(this);
                validateField(this);
                
                // Handle password strength for all password fields
                const passwordFieldConfig = AuthForms.passwordFields.find(function(config) {
                    return config.id === this.id;
                }.bind(this));
                
                if (passwordFieldConfig) {
                    if (passwordFieldConfig.showRequirements) {
                        updatePasswordRequirements(this.value);
                    }
                    updatePasswordStrengthForField(this, passwordFieldConfig);
                }
                
                updateFieldMessages(this);
                
                // If username changes, refresh password strength AND requirements
                if (this.name === 'username' || this.id === 'username' ||
                    (this.id && this.id.toLowerCase().indexOf('username') !== -1) ||
                    (this.name && this.name.toLowerCase().indexOf('username') !== -1)) {
                    const passwordInput = document.getElementById('password');
                    if (passwordInput && passwordInput.value) {
                        const pwConfig = AuthForms.passwordFields.find(function(c) { return c.id === 'password'; });
                        if (pwConfig) {
                            updatePasswordStrengthForField(passwordInput, pwConfig);
                            if (pwConfig.showRequirements) {
                                updatePasswordRequirements(passwordInput.value);
                            }
                        }
                    }
                }
                
                // If this is main password field, also update confirmation field
                if (this.name === 'password' || this.id === 'password') {
                    const confirmPasswordField = document.getElementById('password2') || document.querySelector('input[name="password2"]') || document.querySelector('input[name="confirmPassword"]');
                    if (confirmPasswordField) {
                        updateFieldMessages(confirmPasswordField);
                    }
                }
            });
            
            input.addEventListener('focus', function() {
                this.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.classList.remove('focused');
                this.classList.add('touched');
                validateField(this);
            });
            
            if (input.tagName.toLowerCase() === 'select') {
                input.addEventListener('change', function() {
                    updateInputState(this);
                    validateField(this);
                });
            }
        });
    }

    /**
     * Update input state for floating labels
     * @function
     * @param {HTMLElement} input - The input element
     * @returns {void}
     */
    function updateInputState(input) {
        const value = input.value.trim();
        
        if (input.tagName.toLowerCase() === 'select') {
            const selectedOption = input.options[input.selectedIndex];
            const hasValue = selectedOption && selectedOption.value !== '' && !selectedOption.disabled;
            input.classList.toggle('has-value', hasValue);
        } else {
            input.classList.toggle('has-value', value !== '');
        }
    }

    /**
     * Sync toggle icons for a password input.
     *
     * UX convention:
     *   type="password" (text hidden)  -> show icon-eye-OFF (struck eye = click to REVEAL)
     *   type="text"     (text visible) -> show icon-eye     (open eye   = click to HIDE)
     *
     * The unused SVG is REMOVED from the DOM entirely (not hidden via CSS).
     * Both references are kept on the toggle element (_iconEye / _iconEyeOff)
     * so they survive removal and can be re-inserted.
     *
     * @param {HTMLElement} toggle - .password-toggle wrapper
     * @param {HTMLElement} input  - the associated password/text input
     */
    function syncToggleIcons(toggle, input) {
        var iconEye    = toggle._iconEye;
        var iconEyeOff = toggle._iconEyeOff;
        if (!iconEye || !iconEyeOff) return;

        if (input.type === 'password') {
            // Text hidden → show eye-off (struck-through eye = "click to reveal")
            if (iconEye.parentNode)     { iconEye.parentNode.removeChild(iconEye); }
            if (!iconEyeOff.parentNode) { toggle.appendChild(iconEyeOff); }
        } else {
            // Text visible → show eye (open eye = "click to hide")
            if (iconEyeOff.parentNode)  { iconEyeOff.parentNode.removeChild(iconEyeOff); }
            if (!iconEye.parentNode)    { toggle.appendChild(iconEye); }
        }
    }

    /**
     * Setup password visibility toggle functionality.
     * SVG references are cached on the toggle element BEFORE any DOM manipulation
     * so they survive removal and can be re-inserted correctly.
     * @function
     * @returns {void}
     */
    function setupPasswordToggle() {
        var passwordToggles = document.querySelectorAll('.password-toggle');

        passwordToggles.forEach(function(toggle) {
            var targetId = toggle.getAttribute('data-target');
            var input    = document.getElementById(targetId);
            if (!input) return;

            // Cache both SVG elements NOW, before any are removed from DOM
            toggle._iconEye    = toggle.querySelector('.icon-eye');
            toggle._iconEyeOff = toggle.querySelector('.icon-eye-off');

            // Strip any inline style="display:..." set in the HTML template
            if (toggle._iconEye)    { toggle._iconEye.removeAttribute('style'); }
            if (toggle._iconEyeOff) { toggle._iconEyeOff.removeAttribute('style'); }

            // Apply correct initial icon state
            syncToggleIcons(toggle, input);

            toggle.addEventListener('click', function() {
                var t = document.getElementById(this.getAttribute('data-target'));
                if (!t) return;

                t.type = (t.type === 'password') ? 'text' : 'password';
                syncToggleIcons(this, t);

                // Re-run field messages (isPasswordField checks id/name, not type)
                updateFieldMessages(t);
            });
        });
    }

    /**
     * Setup unified checkbox system (modern + regular checkboxes)
     * @function
     * @returns {void}
     */
    function setupModernCheckboxes() {
        const modernCheckboxItems = document.querySelectorAll('.modern-checkbox-item');
        modernCheckboxItems.forEach(function(item) {
            const checkbox = item.querySelector('.modern-checkbox-input');
            if (checkbox) {
                setupCheckboxItem(item, checkbox, 'modern');
            }
        });
        
        const checkboxContainers = document.querySelectorAll('.checkbox-container');
        checkboxContainers.forEach(function(container) {
            const checkbox = container.querySelector('.checkbox-input');
            if (checkbox) {
                setupCheckboxItem(container, checkbox, 'unified');
            }
        });
    }

    /**
     * IDs of checkboxes whose checked state is controlled by the PHP backend.
     * If the backend renders them as checked (defaultChecked = true), the user
     * should not be able to uncheck them — JS must respect and lock that state.
     * @type {Array<string>}
     */
    var BACKEND_LOCKED_CHECKBOX_IDS = [
        'registerAsAuthor',
        'registerAsReader',
        'sendPassword'
    ];

    /**
     * Setup individual checkbox item.
     *
     * Behaviour:
     *  - If the checkbox has `checked` set by PHP (defaultChecked === true) AND its ID
     *    is in BACKEND_LOCKED_CHECKBOX_IDS, the checkbox is treated as locked-checked:
     *      • It is forced to checked on init and cannot be toggled by the user.
     *      • A `data-locked="true"` attribute is added for CSS targeting if needed.
     *  - All other checkboxes (including registerAsReviewer and privacyAgreement) behave
     *    normally — they are togglable and their initial visual state is synced from the
     *    actual `checkbox.checked` property (set by PHP if applicable).
     *
     * @function
     * @param {HTMLElement} container - The checkbox container
     * @param {HTMLElement} checkbox  - The checkbox input
     * @param {string}      type     - 'modern' or 'unified'
     * @returns {void}
     */
    function setupCheckboxItem(container, checkbox, type) {
        var isBackendLocked = BACKEND_LOCKED_CHECKBOX_IDS.indexOf(checkbox.id) !== -1 &&
                              checkbox.defaultChecked === true;

        if (isBackendLocked) {
            // Enforce checked state set by PHP — do not allow user to uncheck
            checkbox.checked = true;
            container.setAttribute('data-locked', 'true');
        }

        // Sync visual state to actual checked state (respects PHP-rendered checked attr)
        updateCheckboxState(container, checkbox, type);

        if (isBackendLocked) {
            // Prevent all interaction that could uncheck this box
            container.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                // Keep visual state in sync just in case
                checkbox.checked = true;
                updateCheckboxState(container, checkbox, type);
            });
            checkbox.addEventListener('click', function(e) {
                e.preventDefault();
                checkbox.checked = true;
            });
            return; // No further event wiring needed
        }

        // Normal (non-locked) checkbox behaviour
        container.addEventListener('click', function(e) {
            if (e.target.type !== 'checkbox' && !e.target.closest('a')) {
                e.preventDefault();
                checkbox.checked = !checkbox.checked;
                checkbox.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });

        checkbox.addEventListener('change', function() {
            updateCheckboxState(container, checkbox, type);
            handleSpecialCheckboxLogic(checkbox);
        });

        checkbox.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    /**
     * Update checkbox visual state
     * @function
     * @param {HTMLElement} container - The checkbox container
     * @param {HTMLElement} checkbox - The checkbox input
     * @param {string} type - Type of checkbox ('modern' or 'unified')
     * @returns {void}
     */
    function updateCheckboxState(container, checkbox, type) {
        container.classList.toggle('checked', checkbox.checked);
        
        container.classList.add('checkbox-animate');
        setTimeout(function() {
            container.classList.remove('checkbox-animate');
        }, 200);
    }

    /**
     * Handle special checkbox logic (like reviewer interests)
     * @function
     * @param {HTMLElement} checkbox - The checkbox input
     * @returns {void}
     */
    function handleSpecialCheckboxLogic(checkbox) {
        if (checkbox.id === 'registerAsReviewer') {
            const interestsContainer = document.getElementById('reviewerInterestsContainer');
            if (interestsContainer) {
                if (checkbox.checked) {
                    if (!interestsContainer.parentNode) {
                        checkbox.closest('.form-group').parentNode.appendChild(interestsContainer);
                    }
                } else {
                    if (interestsContainer.parentNode) {
                        interestsContainer.parentNode.removeChild(interestsContainer);
                    }
                }
                
                const interestsInput = document.getElementById('interests');
                if (interestsInput) {
                    if (checkbox.checked) {
                        interestsInput.setAttribute('required', 'required');
                    } else {
                        interestsInput.removeAttribute('required');
                        interestsInput.value = '';
                        updateInputState(interestsInput);
                    }
                }
            }
        }
    }

    /**
     * Setup reviewer interests field visibility
     * @function
     * @returns {void}
     */
    function setupReviewerInterests() {
        const reviewerCheckbox = document.getElementById('registerAsReviewer');
        const interestsContainer = document.getElementById('reviewerInterestsContainer');
        
        if (reviewerCheckbox && interestsContainer) {
            if (reviewerCheckbox.checked) {
                if (!interestsContainer.parentNode) {
                    reviewerCheckbox.closest('.form-group').parentNode.appendChild(interestsContainer);
                }
            } else {
                if (interestsContainer.parentNode) {
                    interestsContainer.parentNode.removeChild(interestsContainer);
                }
            }
            
            reviewerCheckbox.addEventListener('change', function() {
                const isVisible = this.checked;
                
                if (isVisible) {
                    if (!interestsContainer.parentNode) {
                        this.closest('.form-group').parentNode.appendChild(interestsContainer);
                    }
                    interestsContainer.style.opacity = '0';
                    interestsContainer.style.transform = 'translateY(-10px)';
                    setTimeout(function() {
                        interestsContainer.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        interestsContainer.style.opacity = '1';
                        interestsContainer.style.transform = 'translateY(0)';
                    }, 10);
                } else {
                    if (interestsContainer.parentNode) {
                        interestsContainer.parentNode.removeChild(interestsContainer);
                    }
                }
                
                const interestsInput = document.getElementById('interests');
                if (interestsInput) {
                    if (isVisible) {
                        interestsInput.setAttribute('required', 'required');
                    } else {
                        interestsInput.removeAttribute('required');
                        interestsInput.value = '';
                        interestsInput.classList.remove('has-value', 'error', 'success');
                    }
                }
            });
        }
    }

    /**
     * Setup privacy statement dialog functionality
     * @function
     * @returns {void}
     */
    function setupPrivacyDialog() {
        const privacyLinks = document.querySelectorAll('a[href="#privacyStatement"]');
        const privacyContent = document.getElementById('privacyStatement');
        
        if (!privacyContent) return;
        
        privacyLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                showPrivacyDialog();
            });
        });
        
        createPrivacyDialogElements();
    }

    /**
     * Create privacy dialog elements
     * @function
     * @returns {void}
     */
    function createPrivacyDialogElements() {
        const overlay = document.createElement('div');
        overlay.id = 'privacy-dialog-overlay';
        overlay.className = 'privacy-dialog-overlay';
        
        const dialog = document.createElement('div');
        dialog.id = 'privacy-dialog';
        dialog.className = 'privacy-dialog';
        
        const header = document.createElement('div');
        header.className = 'privacy-dialog-header';
        header.innerHTML = '<h3 class="privacy-dialog-title">Privacy Statement</h3><button type="button" class="privacy-dialog-close" aria-label="Close dialog"><svg viewBox="0 0 24 24" width="24" height="24"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button>';
        
        const content = document.createElement('div');
        content.className = 'privacy-dialog-content';
        content.id = 'privacy-dialog-content';
        
        dialog.appendChild(header);
        dialog.appendChild(content);
        overlay.appendChild(dialog);
        
        document.body.appendChild(overlay);
        
        const closeBtn = header.querySelector('.privacy-dialog-close');
        closeBtn.addEventListener('click', hidePrivacyDialog);
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                hidePrivacyDialog();
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && overlay.classList.contains('active')) {
                hidePrivacyDialog();
            }
        });
    }

    /**
     * Show privacy dialog
     * @function
     * @returns {void}
     */
    function showPrivacyDialog() {
        const overlay = document.getElementById('privacy-dialog-overlay');
        const content = document.getElementById('privacy-dialog-content');
        const privacyContent = document.getElementById('privacyStatement');
        
        if (overlay && content && privacyContent) {
            content.innerHTML = privacyContent.innerHTML;
            overlay.classList.add('active');
            document.body.classList.add('dialog-open');
            
            const dialog = document.getElementById('privacy-dialog');
            if (dialog) {
                dialog.focus();
            }
        }
    }

    /**
     * Hide privacy dialog
     * @function
     * @returns {void}
     */
    function hidePrivacyDialog() {
        const overlay = document.getElementById('privacy-dialog-overlay');
        if (overlay) {
            overlay.classList.remove('active');
            document.body.classList.remove('dialog-open');
        }
    }

    /**
     * Setup Turnstile widget responsiveness
     * @function
     * @returns {void}
     */
    function setupTurnstileResize() {
        const container = document.getElementById('turnstile-container');
        if (!container) return;
        
        function adjustTurnstileSize() {
            const containerWidth = container.parentElement.offsetWidth;
            const maxWidth = Math.min(containerWidth, 700);
        
            container.style.maxWidth = maxWidth + 'px';
            container.style.width = '100%';
        }
        
        adjustTurnstileSize();
        
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(adjustTurnstileSize, 250);
        });
    }

    /**
     * Setup form validation and submission
     * @function
     * @returns {void}
     */
    function setupFormValidation() {
        const form = document.getElementById('registerForm');
        if (!form) return;
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateForm(this)) {
                submitForm(this);
            }
        });
    }

    /**
     * Submit form with loading state
     * @function
     * @param {HTMLFormElement} form - The form element
     * @returns {void}
     */
    function submitForm(form) {
        const submitButton = document.getElementById('registerButton');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner"></span>Processing...';
            
            setTimeout(function() {
                form.submit();
            }, 100);
        } else {
            form.submit();
        }
    }

    /**
     * Validate individual form field
     * @function
     * @param {HTMLElement} field - The form field
     * @returns {boolean} Field validity
     */
    function validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        
        field.classList.remove('error', 'success');
        
        if (field.hasAttribute('required') && !value) {
            isValid = false;
        }
        
        // Password validation uses 5 mandatory requirements
        if (field.type === 'password' && value) {
            const mandatoryReqs = [
                value.length >= 8,
                /[A-Z]/.test(value),
                /[a-z]/.test(value),
                /[0-9]/.test(value),
                /[^A-Za-z0-9]/.test(value)
            ];
            isValid = mandatoryReqs.every(Boolean);
        }
        
        if (field.name === 'password2' && value) {
            const passwordField = document.getElementById('password');
            isValid = passwordField && value === passwordField.value;
        }
        
        if (!isValid) {
            field.classList.add('error');
        } else if (value && field.hasAttribute('required')) {
            field.classList.add('success');
        }
        
        return isValid;
    }

    /**
     * Validate entire form
     * @function
     * @param {HTMLFormElement} form - The form element
     * @returns {boolean} Form validity
     */
    function validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('.form-control[required]');
        
        inputs.forEach(function(input) {
            input.classList.add('touched');
            if (!validateField(input)) {
                isValid = false;
            }
        });
        
        const privacyCheckbox = document.getElementById('privacyAgreement');
        if (privacyCheckbox && !privacyCheckbox.checked) {
            const privacyContainer = privacyCheckbox.closest('.checkbox-container');
            if (privacyContainer) {
                privacyContainer.classList.add('error');
            }
            showMessage('Please agree to the Terms of Use and Privacy Policy to continue.', 'error');
            isValid = false;
        }
        
        const reviewerCheckbox = document.getElementById('registerAsReviewer');
        const interestsInput = document.getElementById('interests');
        if (reviewerCheckbox && reviewerCheckbox.checked && interestsInput && !interestsInput.value.trim()) {
            interestsInput.classList.add('error');
            showMessage('Please provide your reviewer interests.', 'error');
            isValid = false;
        }
        
        const password = form.querySelector('input[name="password"]');
        const password2 = form.querySelector('input[name="password2"]');
        if (password && password2 && password.value !== password2.value) {
            password2.classList.add('error');
            isValid = false;
        }
        
        return isValid;
    }

    /**
     * Show message to user
     * @function
     * @param {string} message - The message
     * @param {string} type - Message type (error, success, info)
     * @returns {void}
     */
    function showMessage(message, type) {
        if (typeof showNotification === 'function') {
            showNotification(message, type);
        } else {
            alert(message);
        }
    }

    /**
     * Initialize password requirements visual indicators
     * Requirements (6 total):
     *   Mandatory (5): req-length, req-uppercase, req-lowercase, req-number, req-special
     *   Optional  (1): req-username (Different from username)
     * @function
     * @returns {void}
     */
    function initializePasswordRequirements() {
        const allRequirements = ['req-length', 'req-uppercase', 'req-lowercase', 'req-number', 'req-special', 'req-username'];
        allRequirements.forEach(function(id) {
            const element = document.getElementById(id);
            if (element) {
                element.classList.add('not-met');
            }
        });
    }

    /**
     * Update password requirements visual indicators
     * Mandatory requirements (5): length, uppercase, lowercase, number, special
     * Optional requirement  (1): req-username — Different from username
     *
     * "Good" = all 5 mandatory requirements met
     * "Very Strong" = all 5 mandatory + optional username requirement met
     *
     * @function
     * @param {string} password - The password value
     * @returns {void}
     */
    function updatePasswordRequirements(password) {
        const usernameInfo = getUsernameInfo();
        let usernameReqMet;
        if (!usernameInfo.exists) {
            usernameReqMet = true;                                    // N/A on this page
        } else if (usernameInfo.value.length === 0 || password.length === 0) {
            usernameReqMet = false;                                   // either empty → not met
        } else {
            const pwLower = password.toLowerCase();
            const unLower = usernameInfo.value; // already lowercased
            if (unLower.length >= 3) {
                // Password must not CONTAIN username and username must not contain password
                usernameReqMet = !pwLower.includes(unLower) && !unLower.includes(pwLower);
            } else {
                usernameReqMet = pwLower !== unLower;
            }
        }
        
        const requirements = [
            // id,             test
            { id: 'req-length',    test: password.length >= 8 },
            { id: 'req-uppercase', test: /[A-Z]/.test(password) },
            { id: 'req-lowercase', test: /[a-z]/.test(password) },
            { id: 'req-number',    test: /[0-9]/.test(password) },
            { id: 'req-special',   test: /[^A-Za-z0-9]/.test(password) },
            { id: 'req-username',  test: usernameReqMet }
        ];
        
        requirements.forEach(function(req) {
            const element = document.getElementById(req.id);
            if (element) {
                const icon = element.querySelector('.requirement-icon');
                
                element.classList.toggle('met', req.test);
                element.classList.toggle('not-met', !req.test);
                
                if (icon) {
                    icon.textContent = req.test ? '✓' : '✗';
                }
            }
        });
        
        // Delegate to strength indicator
        updatePasswordStrengthIndicator(password);
    }

    /**
     * Update password strength indicator bar
     * @function
     * @param {string} password - The password value
     * @returns {void}
     */
    function updatePasswordStrengthIndicator(password) {
        const passwordInput = document.getElementById('password');
        if (passwordInput) {
            const fieldConfig = AuthForms.passwordFields.find(function(config) {
                return config.id === 'password';
            });
            if (fieldConfig) {
                updatePasswordStrengthForField(passwordInput, fieldConfig);
            }
        }
    }

    /**
     * Initialize Turnstile CAPTCHA
     * @function
     * @memberof AuthForms
     * @returns {void}
     */
    function initializeTurnstile() {
        if (typeof turnstile === 'undefined' || !AuthForms.config.siteKey) {
            return;
        }
        
        const container = document.getElementById('turnstile-container');
        const loading = document.getElementById('turnstile-loading');
        const error = document.getElementById('turnstile-error');
        
        if (!container || !loading) return;
        
        if (!loading.parentNode) {
            container.parentNode.appendChild(loading);
        }
        
        try {
            turnstile.render(container, {
                sitekey: AuthForms.config.siteKey,
                theme: AuthForms.config.theme,
                size: AuthForms.config.size,
                callback: function(token) {
                    if (loading && loading.parentNode) {
                        loading.parentNode.removeChild(loading);
                    }
                    if (error && error.parentNode) {
                        error.parentNode.removeChild(error);
                    }
                    
                    const form = document.getElementById('registerForm');
                    if (form) {
                        let hiddenInput = form.querySelector('input[name="cf-turnstile-response"]');
                        if (!hiddenInput) {
                            hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'cf-turnstile-response';
                            form.appendChild(hiddenInput);
                        }
                        hiddenInput.value = token;
                    }
                },
                'error-callback': function() {
                    if (loading && loading.parentNode) {
                        loading.parentNode.removeChild(loading);
                    }
                    if (error) {
                        if (!error.parentNode) {
                            container.parentNode.appendChild(error);
                        }
                        error.innerHTML = '<div class="alert alert-error">Security verification failed. Please refresh the page and try again.</div>';
                    }
                }
            });
        } catch (e) {
            if (loading && loading.parentNode) {
                loading.parentNode.removeChild(loading);
            }
            if (error) {
                if (!error.parentNode) {
                    container.parentNode.appendChild(error);
                }
                error.innerHTML = '<div class="alert alert-error">Security verification unavailable. Please try again later.</div>';
            }
        }
    }

    /**
     * Enhanced Remember Me functionality untuk horizontal layout
     * @function
     * @returns {void}
     */
    function setupEnhancedRememberMe() {
        const rememberCheckbox = document.getElementById('loginRemember');
        
        if (!rememberCheckbox) return;
        
        initializeRememberMeState(rememberCheckbox);
        
        rememberCheckbox.addEventListener('change', function() {
            handleEnhancedRememberMeChange(this);
        });
        
        updateRememberMeVisualState(rememberCheckbox);
    }

    /**
     * Initialize remember me state dari localStorage
     * @function
     * @param {HTMLElement} checkbox - Checkbox element
     * @returns {void}
     */
    function initializeRememberMeState(checkbox) {
        try {
            const savedState = localStorage.getItem('ojsRememberMe');
            if (savedState === 'true') {
                checkbox.checked = true;
            }
            
            updateRememberMeVisualState(checkbox);
        } catch (e) {
            console.warn('[Wizdam Auth] Could not access localStorage for remember me state');
        }
    }

    /**
     * Handle remember me change dengan enhanced functionality
     * @function
     * @param {HTMLElement} checkbox - Checkbox element
     * @returns {void}
     */
    function handleEnhancedRememberMeChange(checkbox) {
        try {
            localStorage.setItem('ojsRememberMe', checkbox.checked.toString());
        } catch (e) {
            console.warn('[Wizdam Auth] Could not save remember me state');
        }
        
        updateRememberMeVisualState(checkbox);
        
        const customEvent = new CustomEvent('ojsRememberMeChanged', {
            detail: { 
                checked: checkbox.checked,
                timestamp: new Date().toISOString() 
            }
        });
        document.dispatchEvent(customEvent);
        
        if (typeof gtag !== 'undefined') {
            gtag('event', 'remember_me_toggle', {
                'value': checkbox.checked ? 1 : 0
            });
        }
    }

    /**
     * Update visual state untuk remember me checkbox
     * @function
     * @param {HTMLElement} checkbox - Checkbox element
     * @returns {void}
     */
    function updateRememberMeVisualState(checkbox) {
        const container = checkbox.closest('.checkbox-container');
        if (!container) return;
        
        container.classList.toggle('checked', checkbox.checked);
        checkbox.setAttribute('aria-checked', checkbox.checked.toString());
        container.style.transition = 'all 0.2s ease';
    }

    // Public API for AuthForms
    AuthForms.initTurnstile = initializeTurnstile;

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeForm);
    } else {
        initializeForm();
    }

    // Initialize password strength on window load
    window.addEventListener('load', function() {
        const strengthIndicator = document.getElementById('passwordStrengthIndicator') || 
                                document.getElementById('loginPasswordStrengthIndicator') ||
                                document.getElementById('oldPasswordStrengthIndicator');
        if (strengthIndicator) {
            initializePasswordStrengthIndicator();
        }
    });

    // Handle visibility change to refresh password requirements
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            AuthForms.passwordFields.forEach(function(fieldConfig) {
                const passwordInput = document.getElementById(fieldConfig.id);
                if (passwordInput) {
                    const currentPassword = passwordInput.value || '';
                    if (fieldConfig.showRequirements) {
                        updatePasswordRequirements(currentPassword);
                    }
                    updatePasswordStrengthForField(passwordInput, fieldConfig);
                }
            });
        }
    });

    // Initialize Turnstile when available
    if (typeof turnstile !== 'undefined') {
        initializeTurnstile();
    } else {
        window.addEventListener('load', function() {
            setTimeout(initializeTurnstile, 1000);
        });
    }

    // Enhanced API for external access
    if (typeof AuthForms !== 'undefined') {
        AuthForms.enhanced = {
            updateFieldMessages: updateFieldMessages,
            updatePasswordRequirements: updatePasswordRequirements,
            initializePasswordStrengthIndicator: initializePasswordStrengthIndicator,
            setupEnhancedRememberMe: setupEnhancedRememberMe,
            updatePasswordStrengthForField: updatePasswordStrengthForField,
            showPasswordSameAsOldError: showPasswordSameAsOldError,
            clearPasswordSameAsOldError: clearPasswordSameAsOldError,
            validateNewPasswordAgainstOld: validateNewPasswordAgainstOld,
            transferEarlyCapturedMessages: transferEarlyCapturedMessages
        };
    }

    // Global utility functions
    window.ojsEnhancedUtils = {
        updateFieldMessages: updateFieldMessages,
        updatePasswordRequirements: updatePasswordRequirements,
        initializePasswordStrengthIndicator: initializePasswordStrengthIndicator,
        setupEnhancedRememberMe: setupEnhancedRememberMe,
        updatePasswordStrengthForField: updatePasswordStrengthForField,
        clearRememberMeState: function() {
            try {
                localStorage.removeItem('ojsRememberMe');
                const checkbox = document.getElementById('loginRemember');
                if (checkbox) {
                    checkbox.checked = false;
                    updateRememberMeVisualState(checkbox);
                }
            } catch (e) {
                console.warn('[Wizdam Auth] Could not clear remember me state');
            }
        },
        getRememberMeState: function() {
            try {
                return localStorage.getItem('ojsRememberMe') === 'true';
            } catch (e) {
                return false;
            }
        },
        testSamePasswordNow: function() {
            console.log('[Wizdam Auth] === TESTING SAME PASSWORD ERROR ===');
            
            const oldPasswordField = document.getElementById('oldPassword');
            const newPasswordField = document.getElementById('password');
            
            if (!oldPasswordField || !newPasswordField) {
                console.log('[Wizdam Auth] ❌ Password fields not found');
                return;
            }
            
            const testPassword = 'samepassword123';
            oldPasswordField.value = testPassword;
            newPasswordField.value = testPassword;
            
            validateNewPasswordAgainstOld(newPasswordField, oldPasswordField);
            
            if (newPasswordField.dataset.samePasswordError === 'true') {
                console.log('[Wizdam Auth] ✅ Same password error detected successfully!');
            } else {
                console.log('[Wizdam Auth] ❌ Same password error not detected');
            }
        },
        testDifferentPassword: function() {
            console.log('[Wizdam Auth] === TESTING DIFFERENT PASSWORD ===');
            
            const oldPasswordField = document.getElementById('oldPassword');
            const newPasswordField = document.getElementById('password');
            
            if (!oldPasswordField || !newPasswordField) {
                console.log('[Wizdam Auth] ❌ Password fields not found');
                return;
            }
            
            oldPasswordField.value = 'oldpassword123';
            newPasswordField.value = 'newpassword456';
            
            validateNewPasswordAgainstOld(newPasswordField, oldPasswordField);
            
            if (newPasswordField.dataset.samePasswordError !== 'true') {
                console.log('[Wizdam Auth] ✅ Same password error cleared successfully!');
            } else {
                console.log('[Wizdam Auth] ❌ Same password error not cleared');
            }
        },
        checkAllStorages: function() {
            console.log('[Wizdam Auth] === ALL STORAGE CHECK ===');
            console.log('[Wizdam Auth] earlyCapturedMessages Map:', window.earlyCapturedMessages);
            console.log('[Wizdam Auth] backupCapturedMessages Object:', window.backupCapturedMessages);
            console.log('[Wizdam Auth] originalMessages Map:');
            originalMessages.forEach(function(value, key) {
                console.log(`[Wizdam Auth]   ${key}:`, value);
            });
        }
    };

})();