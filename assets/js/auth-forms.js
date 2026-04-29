/**
 * Modern Auth Forms JavaScript untuk OJS v2.4.8.2
 * Production-ready authentication forms with modern UI components
 * 
 * @fileoverview Modern auth forms with floating labels, password validation,
 * email verification, and prof checkbox designs for OJS registration system.
 * @version 1.2.4
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
        size: 'normal',
        emailVerification: {
            enabled: true,
            apiKey: '',
            timeout: 8000,
            retries: 2,
            strictMode: false,
            delayAfterTyping: 2000,
            verifyOnConfirm: true
        }
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
     * Email verification cache
     * @type {Map}
     * @memberof AuthForms
     */
    AuthForms.emailCache = new Map();

    /**
     * Email verification timeouts
     * @type {Map}
     * @memberof AuthForms
     */
    AuthForms.verificationTimeouts = new Map();

    /**
     * Disposable email domains list
     * @type {Array}
     * @memberof AuthForms
     */
    AuthForms.disposableDomains = [
        '10minutemail.com', '10minutemail.net', 'guerrillamail.com', 
        'mailinator.com', 'tempmail.org', 'temp-mail.org',
        'yopmail.com', 'throwaway.email', 'getnada.com',
        'maildrop.cc', 'mailnesia.com', 'sharklasers.com',
        'spam4.me', 'tempail.com', 'trashmail.com',
        'wegwerfmail.de', 'zehnminutenmail.de', 'emailondeck.com',
        'mintemail.com', 'mytrashmail.com', 'sogetthis.com',
        'spamgourmet.com', 'spamhole.com', 'tempemail.net',
        'temporaryemail.net', 'neverbox.com', 'mailnull.com',
        'fakeinbox.com', 'dispostable.com', 'jetable.org'
    ];

    /**
     * Suspicious email patterns
     * @type {Array}
     * @memberof AuthForms
     */
    AuthForms.suspiciousPatterns = [
        /^(test|fake|spam|trash|temp|dummy|invalid|noreply|no-reply)[\d]*@/i,
        /^(a|aa|aaa|abc|123|test123)@/i,
        /^(.)\1{4,}@/i,
        /@(test|fake|invalid|example)\./i
    ];

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
            
            function tryCapture() {
                attempts++;
                console.log(`[Wizdam Auth] Persistent capture attempt ${attempts}`);
                
                const passwordFields = ['oldPassword', 'password', 'password2'];
                
                passwordFields.forEach(function(fieldId) {
                    const field = document.getElementById(fieldId);
                    if (!field) return;
                    
                    const fieldContainer = field.parentElement;
                    const errorMessage = fieldContainer.querySelector('.error-message');
                    const successMessage = fieldContainer.querySelector('.success-message');
                    
                    console.log(`[Wizdam Auth]   ${fieldId}: error=${!!errorMessage}, success=${!!successMessage}`);
                    
                    if ((errorMessage || successMessage) && !window.earlyCapturedMessages.has(fieldId)) {
                        const capturedData = {
                            errorElement: errorMessage ? errorMessage.cloneNode(true) : null,
                            successElement: successMessage ? successMessage.cloneNode(true) : null,
                            capturedAt: Date.now(),
                            errorText: errorMessage ? errorMessage.textContent : null,
                            successText: successMessage ? successMessage.textContent : null
                        };
                        
                        // Store in Map
                        window.earlyCapturedMessages.set(fieldId, capturedData);
                        
                        // Store in backup object
                        window.backupCapturedMessages[fieldId] = capturedData;
                        
                        console.log(`[Wizdam Auth]   ✅ Persistent captured ${fieldId}`);
                    }
                });
                
                if (window.earlyCapturedMessages.size < 3 && attempts < maxAttempts) {
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
        setupEmailVerification();
        setupFormValidation();
        initializePasswordRequirements();
        initializePasswordStrengthIndicator();
        initializeFieldMessages();
        initializeTurnstile();
        setupTurnstileResize();
        setupEnhancedRememberMe();
        // ENHANCED: Initialize change password validation
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
                
                // Special handling for login password (show/hide indicator)
                if (fieldConfig.id === 'loginPassword') {
                    passwordInput.addEventListener('focus', function() {
                        if (strengthIndicator && !strengthIndicator.parentNode) {
                            passwordInput.parentElement.parentElement.appendChild(strengthIndicator);
                        }
                    });
                    
                    passwordInput.addEventListener('blur', function() {
                        if (strengthIndicator && !this.value.trim() && strengthIndicator.parentNode) {
                            strengthIndicator.parentNode.removeChild(strengthIndicator);
                        }
                    });
                }
                
                // Initialize strength indicator for this field
                updatePasswordStrengthForField(passwordInput, fieldConfig);
            }
        });
    }

    /**
     * Update password strength for specific field
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
        
        // Calculate strength
        const requirements = [
            password.length >= 8,
            /[A-Z]/.test(password),
            /[0-9]/.test(password),
            /[^A-Za-z0-9]/.test(password)
        ];
        
        let strength = requirements.filter(Boolean).length;
        
        // Bonus points for length and complexity
        if (password.length >= 10) strength += 0.25;
        if (password.length >= 12) strength += 0.25;
        if (password.length >= 16) strength += 0.5;
        if (password.length >= 20) strength += 0.5;
        if (/[a-z]/.test(password)) strength += 0.25;
        if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~`]/.test(password)) strength += 0.25;
        
        let strengthLevel = '';
        let activeSegments = 0;
        
        if (strength < 1) {
            strengthLevel = 'Very Weak';
            activeSegments = 1;
        } else if (strength < 2) {
            strengthLevel = 'Weak';
            activeSegments = 2;
        } else if (strength < 3) {
            strengthLevel = 'Fair';
            activeSegments = 3;
        } else if (strength < 4) {
            strengthLevel = 'Good';
            activeSegments = 4;
        } else if (strength < 5) {
            strengthLevel = 'Strong';
            activeSegments = 5;
        } else {
            strengthLevel = 'Very Strong';
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
     * Store original message elements for reuse
     * @function
     * @returns {void}
     */
    function storeOriginalMessages() {
        const formFields = document.querySelectorAll('.form-control');
        
        formFields.forEach(function(field) {
            const fieldContainer = field.parentElement;
            const errorMessage = fieldContainer.querySelector('.error-message');
            const successMessage = fieldContainer.querySelector('.success-message');
            
            const fieldKey = field.id || field.name || field.className;
            
            if (errorMessage || successMessage) {
                originalMessages.set(fieldKey, {
                    errorElement: errorMessage ? errorMessage.cloneNode(true) : null,
                    successElement: successMessage ? successMessage.cloneNode(true) : null
                });
            }
        });
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
        if (field.type === 'password' && (field.name === 'password' || field.id === 'password' || 
            field.id === 'loginPassword' || field.id === 'oldPassword' || field.name === 'oldPassword')) {
            
            // ENHANCED: If field has same-password error, show error message
            if (hasSamePasswordError) {
                // Transfer early captured messages if needed
                if (typeof transferEarlyCapturedMessages === 'function') {
                    transferEarlyCapturedMessages();
                }
                
                // Show error message like other fields
                if (storedMessages && storedMessages.errorElement) {
                    const errorClone = storedMessages.errorElement.cloneNode(true);
                    insertMessageElement(fieldContainer, errorClone);
                }
                field.classList.add('error');
                field.classList.remove('success');
                return;
            }
            
            if (isRequired && !value && isTouched && !isFocused && storedMessages && storedMessages.errorElement) {
                const errorClone = storedMessages.errorElement.cloneNode(true);
                insertMessageElement(fieldContainer, errorClone);
                field.classList.add('error');
                field.classList.remove('success');
                return;
            } else if (value && isTouched) {
                // Validate password requirements
                const requirements = [
                    value.length >= 8,
                    /[A-Z]/.test(value),
                    /[0-9]/.test(value),
                    /[^A-Za-z0-9]/.test(value)
                ];
                const allMet = requirements.every(Boolean);
                
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
                // Clear states for password if not touched or no value
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
                    // If main password is empty, don't show error/success for confirmation
                    field.classList.remove('error', 'success');
                    return;
                }
            } else {
                // Clear states for password confirmation if not touched
                field.classList.remove('error', 'success');
                return;
            }
        }
        
        // **IMPROVED: Special handling for main email field**
        if (field.type === 'email' && field.name !== 'confirmEmail' && field.id !== 'confirmEmail') {
            if (isRequired && !value && isTouched && !isFocused && storedMessages && storedMessages.errorElement) {
                const errorClone = storedMessages.errorElement.cloneNode(true);
                insertMessageElement(fieldContainer, errorClone);
                field.classList.add('error');
                field.classList.remove('success');
                return;
            } else if (value && isTouched) {
                const isValidFormat = isValidEmailFormat(value);
                if (isValidFormat && storedMessages && storedMessages.successElement) {
                    const successClone = storedMessages.successElement.cloneNode(true);
                    insertMessageElement(fieldContainer, successClone);
                    field.classList.add('success');
                    field.classList.remove('error');
                    return;
                } else if (!isValidFormat && storedMessages && storedMessages.errorElement) {
                    const errorClone = storedMessages.errorElement.cloneNode(true);
                    insertMessageElement(fieldContainer, errorClone);
                    field.classList.add('error');
                    field.classList.remove('success');
                    return;
                }
            } else {
                // Clear states for email if not touched
                field.classList.remove('error', 'success');
                return;
            }
        }
        
        // **IMPROVED: Special handling for confirm email field**
        if (field.name === 'confirmEmail' || field.id === 'confirmEmail') {
            const emailField = document.getElementById('email');
            const emailValue = emailField ? emailField.value.trim() : '';
            
            // Case 1: Required field is empty and touched (not focused)
            if (isRequired && !value && isTouched && !isFocused && storedMessages && storedMessages.errorElement) {
                const errorClone = storedMessages.errorElement.cloneNode(true);
                insertMessageElement(fieldContainer, errorClone);
                field.classList.add('error');
                field.classList.remove('success');
                return;
            } 
            // Case 2: Confirm email has value and is touched
            else if (value && isTouched) {
                const isValidFormat = isValidEmailFormat(value);
                const isEmailValidFormat = emailValue ? isValidEmailFormat(emailValue) : false;
                
                // Case 2a: Main email is empty - show error on confirm email
                if (!emailValue) {
                    if (storedMessages && storedMessages.errorElement) {
                        const errorClone = storedMessages.errorElement.cloneNode(true);
                        insertMessageElement(fieldContainer, errorClone);
                        field.classList.add('error');
                        field.classList.remove('success');
                    }
                    return;
                }
                
                // Case 2b: Both emails have values - check if they match exactly
                if (emailValue) {
                    // CRITICAL: Check exact match first, then format validation
                    if (value === emailValue) {
                        // Emails match - now check if both have valid format
                        if (isValidFormat && isEmailValidFormat) {
                            // Show success only if emails match AND both are valid format
                            if (storedMessages && storedMessages.successElement) {
                                const successClone = storedMessages.successElement.cloneNode(true);
                                insertMessageElement(fieldContainer, successClone);
                                field.classList.add('success');
                                field.classList.remove('error');
                            }
                        } else {
                            // Emails match but invalid format - show error
                            if (storedMessages && storedMessages.errorElement) {
                                const errorClone = storedMessages.errorElement.cloneNode(true);
                                insertMessageElement(fieldContainer, errorClone);
                                field.classList.add('error');
                                field.classList.remove('success');
                            }
                        }
                    } else {
                        // Emails DON'T match - always show error regardless of format
                        if (storedMessages && storedMessages.errorElement) {
                            const errorClone = storedMessages.errorElement.cloneNode(true);
                            insertMessageElement(fieldContainer, errorClone);
                            field.classList.add('error');
                            field.classList.remove('success');
                        }
                    }
                    return;
                }
            } 
            // **NEW: Special case - both fields have values but confirm email not yet touched**
            else if (value && emailValue && value !== emailValue) {
                // Show error immediately when emails don't match, even if not touched
                if (storedMessages && storedMessages.errorElement) {
                    const errorClone = storedMessages.errorElement.cloneNode(true);
                    insertMessageElement(fieldContainer, errorClone);
                    field.classList.add('error');
                    field.classList.remove('success');
                }
                return;
            }
            // Case 3: Not touched or no value - clear states
            else {
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
                // For non-required fields, add success class only if they have value and are valid
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
                
                // Trigger validation update for field messages
                updateFieldMessages(this);
                
                // **IMPROVED: If this is main email field, also update confirm email field**
                if (this.name === 'email' || this.id === 'email') {
                    const confirmEmailField = document.getElementById('confirmEmail') || document.querySelector('input[name="confirmEmail"]');
                    if (confirmEmailField) {
                        updateFieldMessages(confirmEmailField);
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
     * Setup password visibility toggle functionality
     * @function
     * @returns {void}
     */
    function setupPasswordToggle() {
        const passwordToggles = document.querySelectorAll('.password-toggle');
        
        passwordToggles.forEach(function(toggle) {
            // Initialize toggle icons properly
            const targetId = toggle.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const iconEye = toggle.querySelector('.icon-eye');
            const iconEyeOff = toggle.querySelector('.icon-eye-off');
            
            if (input && iconEye && iconEyeOff) {
                // Set initial state based on input type
                if (input.type === 'password') {
                    if (iconEye.parentNode) iconEye.style.display = 'block';
                    if (iconEyeOff.parentNode) iconEyeOff.style.display = 'none';
                } else {
                    if (iconEye.parentNode) iconEye.style.display = 'none';
                    if (iconEyeOff.parentNode) iconEyeOff.style.display = 'block';
                }
            }
            
            toggle.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const iconEye = this.querySelector('.icon-eye');
                const iconEyeOff = this.querySelector('.icon-eye-off');
                
                if (input && iconEye && iconEyeOff) {
                    const isPassword = input.type === 'password';
                    input.type = isPassword ? 'text' : 'password';
                    
                    // Toggle icon visibility using style instead of DOM manipulation for better reliability
                    if (isPassword) {
                        iconEye.style.display = 'none';
                        iconEyeOff.style.display = 'block';
                    } else {
                        iconEye.style.display = 'block';
                        iconEyeOff.style.display = 'none';
                    }
                }
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
     * Setup individual checkbox item
     * @function
     * @param {HTMLElement} container - The checkbox container
     * @param {HTMLElement} checkbox - The checkbox input
     * @param {string} type - Type of checkbox ('modern' or 'unified')
     * @returns {void}
     */
    function setupCheckboxItem(container, checkbox, type) {
        updateCheckboxState(container, checkbox, type);
        
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
     * Setup email verification functionality
     * @function
     * @returns {void}
     */
    function setupEmailVerification() {
        const emailInput = document.getElementById('email');
        const confirmEmailInput = document.getElementById('confirmEmail');
        
        if (!emailInput || !AuthForms.config.emailVerification.enabled) return;
        
        emailInput.addEventListener('input', function() {
            const email = this.value.trim();
            const confirmEmail = confirmEmailInput ? confirmEmailInput.value.trim() : '';
            
            clearExistingVerification(email);
            clearAllEmailVerificationUI();
            
            if (email && isValidEmailFormat(email)) {
                const timeoutId = setTimeout(function() {
                    performQuietVerification(email, emailInput);
                    
                    if (confirmEmail && email === confirmEmail) {
                        setTimeout(function() {
                            showEmailVerificationResult(email, emailInput);
                        }, 100);
                    }
                }, AuthForms.config.emailVerification.delayAfterTyping);
                
                AuthForms.verificationTimeouts.set(email, timeoutId);
            }
            
            if (email && confirmEmail && email === confirmEmail && isValidEmailFormat(email)) {
                setTimeout(function() {
                    showEmailVerificationResult(email, emailInput);
                }, AuthForms.config.emailVerification.delayAfterTyping + 100);
            }
        });
        
        if (confirmEmailInput) {
            confirmEmailInput.addEventListener('input', function() {
                const email = emailInput.value.trim();
                const confirmEmail = this.value.trim();
                
                clearEmailVerificationUI(confirmEmailInput);
                
                // Update field messages for confirm email validation
                updateFieldMessages(this);
                
                if (email && confirmEmail && email === confirmEmail && isValidEmailFormat(email)) {
                    if (AuthForms.emailCache.has(email)) {
                        showEmailVerificationResult(email, emailInput);
                    } else {
                        clearExistingVerification(email);
                        const timeoutId = setTimeout(function() {
                            performQuietVerification(email, emailInput);
                            setTimeout(function() {
                                showEmailVerificationResult(email, emailInput);
                            }, 100);
                        }, 500);
                        
                        AuthForms.verificationTimeouts.set(email, timeoutId);
                    }
                } else if (confirmEmail && email !== confirmEmail) {
                    showEmailMismatchError(confirmEmailInput);
                }
            });
            
            confirmEmailInput.addEventListener('blur', function() {
                const email = emailInput.value.trim();
                const confirmEmail = this.value.trim();
                
                // Update field messages on blur
                updateFieldMessages(this);
                
                if (email && confirmEmail) {
                    if (email === confirmEmail && isValidEmailFormat(email)) {
                        if (AuthForms.emailCache.has(email)) {
                            showEmailVerificationResult(email, emailInput);
                        } else {
                            verifyEmailAddress(email, emailInput, true);
                        }
                    } else if (email !== confirmEmail) {
                        showEmailMismatchError(confirmEmailInput);
                    }
                }
            });
        }
    }

    /**
     * Clear all email verification UI from both fields
     * @function
     * @returns {void}
     */
    function clearAllEmailVerificationUI() {
        const emailInput = document.getElementById('email');
        const confirmEmailInput = document.getElementById('confirmEmail');
        
        if (emailInput) {
            clearEmailVerificationUI(emailInput);
        }
        if (confirmEmailInput) {
            clearEmailVerificationUI(confirmEmailInput);
        }
    }

    /**
     * Show email mismatch error
     * @function
     * @param {HTMLElement} confirmEmailInput - Confirm email input element
     * @returns {void}
     */
    function showEmailMismatchError(confirmEmailInput) {
        confirmEmailInput.classList.remove('verifying', 'success');
        confirmEmailInput.classList.add('error');
        
        const messageElement = confirmEmailInput.parentElement.querySelector('.verification-message');
        if (messageElement && messageElement.parentNode) {
            messageElement.parentNode.removeChild(messageElement);
        }
    }

    /**
     * Clear existing verification timeout
     * @function
     * @param {string} email - Email address
     * @returns {void}
     */
    function clearExistingVerification(email) {
        if (AuthForms.verificationTimeouts.has(email)) {
            clearTimeout(AuthForms.verificationTimeouts.get(email));
            AuthForms.verificationTimeouts.delete(email);
        }
        
        const showKey = email + '_show';
        if (AuthForms.verificationTimeouts.has(showKey)) {
            clearTimeout(AuthForms.verificationTimeouts.get(showKey));
            AuthForms.verificationTimeouts.delete(showKey);
        }
    }

    /**
     * Perform quiet verification (background check)
     * @function
     * @param {string} email - Email address to verify
     * @param {HTMLElement} inputElement - The email input element
     * @returns {void}
     */
    function performQuietVerification(email, inputElement) {
        const domain = email.split('@')[1];
        if (!domain) return;
        
        if (AuthForms.emailCache.has(email)) {
            return;
        }
        
        if (isHighConfidenceFakeEmail(domain, email)) {
            const result = {
                isValid: false,
                reason: 'obvious_fake',
                confidence: 'high',
                message: 'This appears to be a temporary or fake email address'
            };
            AuthForms.emailCache.set(email, result);
            return;
        }
        
        checkEmailDomainQuiet(domain)
            .then(function(domainValid) {
                let result;
                if (!domainValid) {
                    result = {
                        isValid: false,
                        reason: 'invalid_domain',
                        confidence: 'high',
                        message: 'This email domain does not exist or cannot receive emails'
                    };
                } else {
                    result = {
                        isValid: true,
                        reason: 'format_and_domain_valid',
                        confidence: 'medium',
                        message: 'Email format and domain appear valid'
                    };
                }
                AuthForms.emailCache.set(email, result);
            })
            .catch(function(error) {
                const result = {
                    isValid: true,
                    reason: 'verification_unavailable',
                    confidence: 'low',
                    message: 'Email format is valid (domain verification unavailable)'
                };
                AuthForms.emailCache.set(email, result);
            });
    }

    /**
     * Show email verification result when appropriate
     * @function
     * @param {string} email - Email address
     * @param {HTMLElement} inputElement - Input element
     * @returns {void}
     */
    function showEmailVerificationResult(email, inputElement) {
        const confirmEmailInput = document.getElementById('confirmEmail');
        const targetElement = confirmEmailInput || inputElement;
        
        if (!AuthForms.emailCache.has(email)) {
            showEmailVerificationLoading(targetElement);
            
            const timeoutId = setTimeout(function() {
                verifyEmailAddress(email, inputElement, true);
            }, 200);
            
            AuthForms.verificationTimeouts.set(email + '_show', timeoutId);
            return;
        }
        
        const result = AuthForms.emailCache.get(email);
        updateEmailValidationUI(targetElement, result, true);
    }

    /**
     * Verify if email address is real and active
     * @function
     * @param {string} email - Email address to verify
     * @param {HTMLElement} inputElement - The email input element
     * @param {boolean} isConfirmField - Whether this is triggered from confirm field
     * @returns {void}
     */
    function verifyEmailAddress(email, inputElement, isConfirmField) {
        const domain = email.split('@')[1];
        if (!domain) return;
        
        const confirmEmailInput = document.getElementById('confirmEmail');
        const targetElement = (isConfirmField && confirmEmailInput) ? confirmEmailInput : inputElement;
        
        if (AuthForms.emailCache.has(email)) {
            const cachedResult = AuthForms.emailCache.get(email);
            updateEmailValidationUI(targetElement, cachedResult, isConfirmField);
            return;
        }
        
        showEmailVerificationLoading(targetElement);
        
        if (isHighConfidenceFakeEmail(domain, email)) {
            const result = {
                isValid: false,
                reason: 'disposable',
                message: 'This email appears to be temporary or fake. Please use a valid email address.'
            };
            AuthForms.emailCache.set(email, result);
            updateEmailValidationUI(targetElement, result, isConfirmField);
            return;
        }
        
        checkEmailDomain(domain)
            .then(function(domainValid) {
                if (!domainValid) {
                    const result = {
                        isValid: false,
                        reason: 'invalid_domain',
                        message: 'Email domain does not exist or cannot receive emails'
                    };
                    AuthForms.emailCache.set(email, result);
                    updateEmailValidationUI(targetElement, result, isConfirmField);
                    return;
                }
                
                const result = {
                    isValid: true,
                    reason: 'basic_valid',
                    message: 'Email appears to be valid'
                };
                AuthForms.emailCache.set(email, result);
                updateEmailValidationUI(targetElement, result, isConfirmField);
            })
            .catch(function(error) {
                const result = {
                    isValid: true,
                    reason: 'verification_failed',
                    message: 'Email format is valid (verification unavailable)'
                };
                updateEmailValidationUI(targetElement, result, isConfirmField);
            });
    }

    /**
     * Check if email is high-confidence fake (strict patterns only)
     * @function
     * @param {string} domain - Email domain
     * @param {string} email - Full email address
     * @returns {boolean} True if obviously fake
     */
    function isHighConfidenceFakeEmail(domain, email) {
        const lowerDomain = domain.toLowerCase();
        const lowerEmail = email.toLowerCase();
        
        if (AuthForms.disposableDomains.includes(lowerDomain)) {
            return true;
        }
        
        for (let i = 0; i < AuthForms.suspiciousPatterns.length; i++) {
            if (AuthForms.suspiciousPatterns[i].test(lowerEmail)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if email domain has valid MX records
     * @function
     * @param {string} domain - Domain to check
     * @returns {Promise<boolean>} Promise resolving to domain validity
     */
    function checkEmailDomain(domain) {
        return new Promise(function(resolve, reject) {
            const dnsUrls = [
                'https://dns.google/resolve?name=' + domain + '&type=MX',
                'https://cloudflare-dns.com/dns-query?name=' + domain + '&type=MX'
            ];
            
            let attempts = 0;
            
            function tryNextDNS() {
                if (attempts >= dnsUrls.length) {
                    checkARecord(domain).then(resolve).catch(reject);
                    return;
                }
                
                fetch(dnsUrls[attempts], {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/dns-json'
                    }
                })
                .then(function(response) {
                    if (!response.ok) throw new Error('DNS query failed');
                    return response.json();
                })
                .then(function(data) {
                    const hasMX = data.Answer && data.Answer.some(function(record) {
                        return record.type === 15;
                    });
                    resolve(hasMX);
                })
                .catch(function(error) {
                    attempts++;
                    tryNextDNS();
                });
            }
            
            tryNextDNS();
        });
    }

    /**
     * Check A record as fallback
     * @function
     * @param {string} domain - Domain to check
     * @returns {Promise<boolean>} Promise resolving to domain validity
     */
    function checkARecord(domain) {
        return fetch('https://dns.google/resolve?name=' + domain + '&type=A', {
            method: 'GET',
            headers: {
                'Accept': 'application/dns-json'
            }
        })
        .then(function(response) {
            if (!response.ok) throw new Error('A record query failed');
            return response.json();
        })
        .then(function(data) {
            return data.Answer && data.Answer.length > 0;
        });
    }

    /**
     * Quiet DNS check (no UI updates)
     * @function
     * @param {string} domain - Domain to check
     * @returns {Promise<boolean>} Promise resolving to domain validity
     */
    function checkEmailDomainQuiet(domain) {
        return new Promise(function(resolve, reject) {
            const timeoutId = setTimeout(function() {
                reject(new Error('Timeout'));
            }, 3000);
            
            fetch('https://dns.google/resolve?name=' + domain + '&type=MX', {
                method: 'GET',
                headers: { 'Accept': 'application/dns-json' }
            })
            .then(function(response) {
                clearTimeout(timeoutId);
                if (!response.ok) throw new Error('DNS query failed');
                return response.json();
            })
            .then(function(data) {
                const hasMX = data.Answer && data.Answer.some(function(record) {
                    return record.type === 15;
                });
                resolve(hasMX);
            })
            .catch(function(error) {
                clearTimeout(timeoutId);
                fetch('https://dns.google/resolve?name=' + domain + '&type=A')
                .then(function(response) { return response.json(); })
                .then(function(data) { resolve(data.Answer && data.Answer.length > 0); })
                .catch(function() { resolve(true); });
            });
        });
    }

    /**
     * Show email verification loading state
     * @function
     * @param {HTMLElement} inputElement - Input element
     * @returns {void}
     */
    function showEmailVerificationLoading(inputElement) {
        inputElement.classList.remove('error', 'success');
        inputElement.classList.add('verifying');
        
        let messageElement = inputElement.parentElement.querySelector('.verification-message');
        if (!messageElement) {
            messageElement = document.createElement('div');
            messageElement.className = 'verification-message';
            inputElement.parentElement.appendChild(messageElement);
        }
        
        messageElement.className = 'verification-message verifying';
        messageElement.textContent = 'Verifying email address...';
    }

    /**
     * Update email validation UI based on verification result
     * @function
     * @param {HTMLElement} inputElement - Input element
     * @param {Object} result - Verification result
     * @param {boolean} showResult - Whether to show the result immediately
     * @returns {void}
     */
    function updateEmailValidationUI(inputElement, result, showResult) {
        const emailInput = document.getElementById('email');
        if (emailInput) {
            emailInput.classList.remove('verifying', 'error', 'success');
        }
        
        inputElement.classList.remove('verifying', 'error', 'success');
        
        let messageElement = inputElement.parentElement.querySelector('.verification-message');
        if (!messageElement) {
            messageElement = document.createElement('div');
            messageElement.className = 'verification-message';
            inputElement.parentElement.appendChild(messageElement);
        }
        
        if (!showResult) {
            if (messageElement && messageElement.parentNode) {
                messageElement.parentNode.removeChild(messageElement);
            }
            if (emailInput) {
                emailInput.dataset.emailVerified = result.isValid.toString();
                emailInput.dataset.verificationReason = result.reason;
                emailInput.dataset.verificationConfidence = result.confidence || 'medium';
            }
            inputElement.dataset.emailVerified = result.isValid.toString();
            inputElement.dataset.verificationReason = result.reason;
            inputElement.dataset.verificationConfidence = result.confidence || 'medium';
            return;
        }
        
        if (result.isValid) {
            if (emailInput) emailInput.classList.add('success');
            inputElement.classList.add('success');
            
            messageElement.className = 'verification-message success';
            
            let message = result.message;
            if (result.confidence === 'low') {
                message = 'Email format is valid';
            } else if (result.confidence === 'medium') {
                message = 'Email appears to be valid';
            } else {
                message = 'Email verified as valid';
            }
            messageElement.textContent = message;
        } else {
            if (emailInput) emailInput.classList.add('error');
            inputElement.classList.add('error');
            
            messageElement.className = 'verification-message error';
            
            let message = result.message;
            if (result.reason === 'obvious_fake') {
                message = 'Please use a valid email address';
            } else if (result.reason === 'invalid_domain') {
                message = 'This email domain does not exist';
            } else {
                message = 'This email appears to be invalid';
            }
            messageElement.textContent = message;
        }
        
        if (emailInput) {
            emailInput.dataset.emailVerified = result.isValid.toString();
            emailInput.dataset.verificationReason = result.reason;
            emailInput.dataset.verificationConfidence = result.confidence || 'medium';
        }
        inputElement.dataset.emailVerified = result.isValid.toString();
        inputElement.dataset.verificationReason = result.reason;
        inputElement.dataset.verificationConfidence = result.confidence || 'medium';
    }

    /**
     * Clear email verification UI
     * @function
     * @param {HTMLElement} inputElement - Input element
     * @returns {void}
     */
    function clearEmailVerificationUI(inputElement) {
        inputElement.classList.remove('verifying', 'error', 'success');
        
        const messageElement = inputElement.parentElement.querySelector('.verification-message');
        if (messageElement && messageElement.parentNode) {
            messageElement.parentNode.removeChild(messageElement);
        }
        
        delete inputElement.dataset.emailVerified;
        delete inputElement.dataset.verificationReason;
    }

    /**
     * Check if email format is valid
     * @function
     * @param {string} email - Email to check
     * @returns {boolean} True if format is valid
     */
    function isValidEmailFormat(email) {
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        
        if (!emailRegex.test(email)) {
            return false;
        }
        
        const parts = email.split('@');
        if (parts.length !== 2) return false;
        
        const localPart = parts[0];
        const domain = parts[1];
        
        if (localPart.length === 0 || localPart.length > 64) return false;
        if (localPart.startsWith('.') || localPart.endsWith('.')) return false;
        if (localPart.includes('..')) return false;
        
        if (domain.length === 0 || domain.length > 253) return false;
        if (domain.startsWith('.') || domain.endsWith('.')) return false;
        if (domain.includes('..')) return false;
        
        return true;
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
            const originalText = submitButton.textContent;
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
        
        if (field.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
            } else {
                const emailInput = document.getElementById('email');
                const checkField = (field.name === 'confirmEmail' && emailInput) ? emailInput : field;
                const emailVerified = checkField.dataset.emailVerified;
                if (emailVerified === 'false') {
                    isValid = false;
                }
            }
        }
        
        if (field.type === 'password' && value) {
            const requirements = [
                value.length >= 8,
                /[A-Z]/.test(value),
                /[0-9]/.test(value),
                /[^A-Za-z0-9]/.test(value)
            ];
            isValid = requirements.every(Boolean);
        }
        
        if (field.name === 'password2' && value) {
            const passwordField = document.getElementById('password');
            isValid = passwordField && value === passwordField.value;
        }
        
        if (field.name === 'confirmEmail' && value) {
            const emailField = document.getElementById('email');
            if (emailField && value === emailField.value) {
                const emailVerified = emailField.dataset.emailVerified;
                isValid = emailVerified !== 'false';
            } else {
                isValid = false;
            }
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
        const inputs = form.querySelectorAll('.form-control[required], .form-control[type="email"]');
        
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
        
        const email = form.querySelector('input[name="email"]');
        const confirmEmail = form.querySelector('input[name="confirmEmail"]');
        if (email && confirmEmail && email.value !== confirmEmail.value) {
            confirmEmail.classList.add('error');
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
     * Initialize password requirements
     * @function
     * @returns {void}
     */
    function initializePasswordRequirements() {
        const requirements = ['req-length', 'req-uppercase', 'req-number', 'req-special'];
        requirements.forEach(function(id) {
            const element = document.getElementById(id);
            if (element) {
                element.classList.add('not-met');
            }
        });
    }

    /**
     * Update password requirements visual indicators
     * @function
     * @param {string} password - The password value
     * @returns {void}
     */
    function updatePasswordRequirements(password) {
        const requirements = [
            { id: 'req-length', test: password.length >= 8 },
            { id: 'req-uppercase', test: /[A-Z]/.test(password) },
            { id: 'req-number', test: /[0-9]/.test(password) },
            { id: 'req-special', test: /[^A-Za-z0-9]/.test(password) }
        ];
        
        let metCount = 0;
        
        requirements.forEach(function(req) {
            if (req.test) metCount++;
        });
        
        requirements.forEach(function(req) {
            const element = document.getElementById(req.id);
            if (element) {
                const icon = element.querySelector('.requirement-icon');
                const isMet = req.test;
                
                element.classList.toggle('met', isMet);
                element.classList.toggle('not-met', !isMet);
                
                if (icon) {
                    icon.textContent = isMet ? '✓' : '✗';
                }
            }
        });
        
        updatePasswordStrengthIndicator(password, metCount, requirements);
    }

    /**
     * Update password strength indicator bar (legacy function for compatibility)
     * @function
     * @param {string} password - The password value
     * @param {number} metCount - Number of basic requirements met
     * @param {Array} requirements - Array of requirement objects
     * @returns {void}
     */
    function updatePasswordStrengthIndicator(password, metCount, requirements) {
        // Find the primary password field that triggered this
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
            // Refresh all password fields
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
            // ENHANCED: Expose same password validation functions
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
        // ENHANCED: Test functions for same password validation
        testSamePasswordNow: function() {
            console.log('[Wizdam Auth] === TESTING SAME PASSWORD ERROR ===');
            
            const oldPasswordField = document.getElementById('oldPassword');
            const newPasswordField = document.getElementById('password');
            
            if (!oldPasswordField || !newPasswordField) {
                console.log('[Wizdam Auth] ❌ Password fields not found');
                return;
            }
            
            // Set passwords to same value
            const testPassword = 'samepassword123';
            oldPasswordField.value = testPassword;
            newPasswordField.value = testPassword;
            
            console.log('[Wizdam Auth] 1. Set both passwords to:', testPassword);
            console.log('[Wizdam Auth] 2. Calling validateNewPasswordAgainstOld...');
            validateNewPasswordAgainstOld(newPasswordField, oldPasswordField);
            
            console.log('[Wizdam Auth] 3. Same password error flag:', newPasswordField.dataset.samePasswordError);
            
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
            
            // Set passwords to different values
            oldPasswordField.value = 'oldpassword123';
            newPasswordField.value = 'newpassword456';
            
            console.log('[Wizdam Auth] 1. Set different passwords');
            console.log('[Wizdam Auth] 2. Calling validateNewPasswordAgainstOld...');
            validateNewPasswordAgainstOld(newPasswordField, oldPasswordField);
            
            console.log('[Wizdam Auth] 3. Same password error flag:', newPasswordField.dataset.samePasswordError);
            
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