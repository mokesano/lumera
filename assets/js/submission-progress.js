/**
 * js/simple-submission-progress.js
 * Simple Progress Bar JavaScript for OJS 2.4.8.2 - WITH COMPREHENSIVE LOGGING
 */

(function() {
    'use strict';
    
    // Logging system
    var Logger = {
        prefix: '[Wizdam Submit Progress]',
        
        log: function(level, message, data) {
            var timestamp = new Date().toISOString();
            var logMessage = this.prefix + ' [' + level.toUpperCase() + '] ' + timestamp + ' - ' + message;
            
            if (data) {
                console.log(logMessage, data);
            } else {
                console.log(logMessage);
            }
        },
        
        info: function(message, data) {
            this.log('info', message, data);
        },
        
        warn: function(message, data) {
            this.log('warn', message, data);
        },
        
        error: function(message, data) {
            this.log('error', message, data);
        },
        
        user: function(action, data) {
            this.log('user', 'User Action: ' + action, data);
        },
        
        input: function(element, action, data) {
            var elementInfo = {
                tagName: element.tagName,
                type: element.type || 'text',
                name: element.name || '',
                id: element.id || '',
                value: element.value || '',
                className: element.className || ''
            };
            
            this.log('input', 'Input ' + action + ': ' + this.getElementDescription(element), {
                element: elementInfo,
                data: data
            });
        },
        
        step: function(stepNumber, action, data) {
            this.log('step', 'Step ' + stepNumber + ' - ' + action, data);
        },
        
        progress: function(message, data) {
            this.log('progress', message, data);
        },
        
        getElementDescription: function(element) {
            var desc = element.tagName.toLowerCase();
            if (element.type) desc += '[type=' + element.type + ']';
            if (element.name) desc += '[name=' + element.name + ']';
            if (element.id) desc += '[id=' + element.id + ']';
            return desc;
        }
    };
    
    // Private SimpleProgress object - isolated from global scope
    var SimpleProgress = {
        
        currentStep: 1,
        submissionProgress: 0,
        inputTracking: {},
        userActivity: [],
        
        init: function(currentStep, submissionProgress) {
            try {
                Logger.info('Initializing SimpleProgress', {
                    currentStep: currentStep,
                    submissionProgress: submissionProgress,
                    url: window.location.href,
                    userAgent: navigator.userAgent
                });
                
                this.currentStep = currentStep || 1;
                this.submissionProgress = submissionProgress || 0;
                
                Logger.step(this.currentStep, 'Initialized as current step');
                
                this.initializeInputTracking();
                this.setupStickyBehavior();
                this.updateStepColors();
                this.updateProgressBar();
                this.bindEvents();
                
                // Initial positioning - pastikan progress bar tampil saat page load
                this.updateStickyPosition();
                
                Logger.info('SimpleProgress initialization completed successfully');
            } catch (e) {
                Logger.error('SimpleProgress init failed', { error: e.message, stack: e.stack });
            }
        },
        
        setupStickyBehavior: function() {
            try {
                Logger.info('Setting up proper sticky behavior within #body boundaries');
                
                var bodyElement = document.getElementById('body');
                var progressContainer = document.querySelector('.simple-progress-container');
                
                if (!bodyElement || !progressContainer) {
                    Logger.warn('Body element or progress container not found');
                    return;
                }
                
                // Setup scroll listener to control sticky behavior
                var self = this;
                var throttleTimeout = null;
                
                function handleScroll() {
                    if (throttleTimeout) return;
                    
                    throttleTimeout = setTimeout(function() {
                        self.updateStickyPosition();
                        throttleTimeout = null;
                    }, 16); // ~60fps
                }
                
                window.addEventListener('scroll', handleScroll);
                window.addEventListener('resize', function() {
                    self.updateStickyPosition();
                });
                
                // Initial position check
                this.updateStickyPosition();
                
                Logger.info('Sticky behavior setup completed');
            } catch (e) {
                Logger.error('Sticky behavior setup failed', { error: e.message });
            }
        },
        
        updateStickyPosition: function() {
            try {
                var bodyElement = document.getElementById('body');
                var progressContainer = document.querySelector('.simple-progress-container');
                
                if (!bodyElement || !progressContainer) return;
                
                var bodyRect = bodyElement.getBoundingClientRect();
                var windowHeight = window.innerHeight;
                
                // Get actual height of progress container
                var containerHeight = progressContainer.offsetHeight || 270; // fallback to 270px
                
                if (bodyRect.top <= 0 && bodyRect.bottom >= windowHeight) {
                    // Di dalam #body - sticky normal di tengah viewport
                    progressContainer.style.position = 'fixed';
                    progressContainer.style.top = '50vh';
                    progressContainer.style.right = '20px';
                    progressContainer.style.transform = 'translateY(-50%)';
                    
                } else if (bodyRect.top > 0) {
                    // Viewport di atas #body - progress bar di posisi sejajar top #body
                    progressContainer.style.position = 'fixed';
                    progressContainer.style.top = bodyRect.top + 50 + 'px';
                    progressContainer.style.right = '20px';
                    progressContainer.style.transform = 'none';
                    
                } else if (bodyRect.bottom < windowHeight) {
                    // Viewport di bawah #body - progress bar di posisi sejajar bottom #body
                    progressContainer.style.position = 'fixed';
                    progressContainer.style.top = (bodyRect.bottom - containerHeight) + 'px';
                    progressContainer.style.right = '20px';
                    progressContainer.style.transform = 'none';
                }
                
            } catch (e) {
                Logger.error('Sticky position update failed', { error: e.message });
            }
        },
        
        initializeInputTracking: function() {
            try {
                Logger.info('Initializing input tracking for step ' + this.currentStep);
                
                var inputs = document.querySelectorAll('input, textarea, select');
                var stepInputs = {
                    total: 0,
                    required: 0,
                    optional: 0,
                    filled: 0,
                    empty: 0,
                    types: {}
                };
                
                inputs.forEach(function(input) {
                    if (input.offsetParent === null) return; // Skip hidden
                    
                    stepInputs.total++;
                    
                    var inputType = input.type || input.tagName.toLowerCase();
                    stepInputs.types[inputType] = (stepInputs.types[inputType] || 0) + 1;
                    
                    if (input.required || input.classList.contains('required')) {
                        stepInputs.required++;
                    } else {
                        stepInputs.optional++;
                    }
                    
                    if (this.isInputFilled(input)) {
                        stepInputs.filled++;
                    } else {
                        stepInputs.empty++;
                    }
                    
                    Logger.input(input, 'detected', {
                        required: input.required || input.classList.contains('required'),
                        filled: this.isInputFilled(input),
                        initialValue: input.value
                    });
                }.bind(this));
                
                // Check for uploaded files
                var fileRows = document.querySelectorAll('table tr:not(:first-child), .file-row, .uploaded-file');
                if (fileRows.length > 0) {
                    stepInputs.uploadedFiles = fileRows.length;
                    Logger.info('Detected uploaded files', { count: fileRows.length });
                }
                
                this.inputTracking[this.currentStep] = stepInputs;
                
                Logger.step(this.currentStep, 'Input analysis completed', stepInputs);
            } catch (e) {
                Logger.error('Input tracking initialization failed', { error: e.message });
            }
        },
        
        isInputFilled: function(input) {
            if (input.type === 'file') {
                return input.files && input.files.length > 0;
            } else if (input.type === 'checkbox' || input.type === 'radio') {
                return input.checked;
            } else {
                return input.value && input.value.trim().length > 0;
            }
        },
        
        updateStepColors: function() {
            try {
                Logger.info('Updating step colors', {
                    currentStep: this.currentStep,
                    submissionProgress: this.submissionProgress
                });
                
                var indicators = document.querySelectorAll('.step-indicator');
                var stepStates = {};
                
                indicators.forEach(function(indicator) {
                    var stepNum = parseInt(indicator.getAttribute('data-step'));
                    var oldClass = indicator.className;
                    
                    // Remove all classes
                    indicator.classList.remove('completed', 'current', 'pending');
                    
                    var newState = '';
                    // Simple logic:
                    if (stepNum === this.currentStep) {
                        indicator.classList.add('current'); // Blue - current step
                        newState = 'current';
                    } else if (stepNum <= this.submissionProgress) {
                        indicator.classList.add('completed'); // Green - completed steps
                        newState = 'completed';
                    } else {
                        indicator.classList.add('pending'); // Gray - future steps
                        newState = 'pending';
                    }
                    
                    stepStates[stepNum] = newState;
                    
                    if (oldClass !== indicator.className) {
                        Logger.step(stepNum, 'Color changed', {
                            from: oldClass,
                            to: indicator.className,
                            state: newState
                        });
                    }
                }.bind(this));
                
                // Update complete indicator
                var completeIndicator = document.querySelector('.complete-indicator');
                if (completeIndicator) {
                    var wasCompleted = completeIndicator.classList.contains('completed');
                    
                    if (this.submissionProgress >= 5) {
                        completeIndicator.classList.remove('pending');
                        completeIndicator.classList.add('completed');
                        if (!wasCompleted) {
                            Logger.info('Complete indicator activated - all steps finished!');
                        }
                    } else {
                        completeIndicator.classList.remove('completed');
                        completeIndicator.classList.add('pending');
                    }
                }
                
                Logger.progress('Step colors updated', { stepStates: stepStates });
            } catch (e) {
                Logger.error('Step color update failed', { error: e.message });
            }
        },
        
        updateProgressBar: function() {
            try {
                var progressFill = document.getElementById('progressFill');
                var progressPercentage = document.getElementById('progressPercentage');
                
                if (!progressFill || !progressPercentage) {
                    Logger.warn('Progress bar elements not found');
                    return;
                }
                
                // Calculate progress: each step = 20%
                var baseProgress = this.submissionProgress * 20;
                
                // Add some progress for current step if user is actively in it
                var currentStepProgress = 0;
                var formProgressPercent = 0;
                
                if (this.currentStep > this.submissionProgress) {
                    formProgressPercent = this.getFormProgress();
                    currentStepProgress = formProgressPercent * 0.2; // Max 20% for current step
                }
                
                var totalProgress = Math.min(100, baseProgress + currentStepProgress);
                var oldProgress = parseFloat(progressFill.style.height) || 0;
                
                progressFill.style.height = totalProgress + '%';
                progressPercentage.textContent = Math.round(totalProgress) + '%';
                
                Logger.progress('Progress bar updated', {
                    oldProgress: oldProgress + '%',
                    newProgress: totalProgress + '%',
                    baseProgress: baseProgress + '%',
                    currentStepContribution: currentStepProgress + '%',
                    formCompletionPercent: formProgressPercent + '%',
                    step: this.currentStep,
                    submissionProgress: this.submissionProgress
                });
                
                // Log significant progress changes
                if (Math.abs(totalProgress - oldProgress) >= 5) {
                    Logger.user('significant_progress_change', {
                        change: (totalProgress - oldProgress).toFixed(1) + '%',
                        newTotal: totalProgress + '%'
                    });
                }
            } catch (e) {
                Logger.error('Progress bar update failed', { error: e.message });
            }
        },
        
        getFormProgress: function() {
            try {
                var inputs = document.querySelectorAll('input:not([type="hidden"]), textarea, select');
                var totalFields = 0;
                var filledFields = 0;
                var fieldAnalysis = {
                    byType: {},
                    filled: [],
                    empty: []
                };
                
                inputs.forEach(function(input) {
                    if (input.offsetParent === null) return; // Skip hidden
                    
                    totalFields++;
                    var inputType = input.type || input.tagName.toLowerCase();
                    
                    if (!fieldAnalysis.byType[inputType]) {
                        fieldAnalysis.byType[inputType] = { total: 0, filled: 0 };
                    }
                    fieldAnalysis.byType[inputType].total++;
                    
                    var isFilled = false;
                    if (input.type === 'file') {
                        isFilled = input.files && input.files.length > 0;
                    } else if (input.type === 'checkbox' || input.type === 'radio') {
                        isFilled = input.checked;
                    } else if (input.value && input.value.trim().length > 0) {
                        isFilled = true;
                    }
                    
                    if (isFilled) {
                        filledFields++;
                        fieldAnalysis.byType[inputType].filled++;
                        fieldAnalysis.filled.push({
                            name: input.name || input.id,
                            type: inputType,
                            value: input.type === 'password' ? '[HIDDEN]' : (input.value || '').substring(0, 50)
                        });
                    } else {
                        fieldAnalysis.empty.push({
                            name: input.name || input.id,
                            type: inputType,
                            required: input.required || input.classList.contains('required')
                        });
                    }
                });
                
                // Check for uploaded files in tables (for step 2)
                var fileRows = document.querySelectorAll('table tr:not(:first-child), .file-row');
                if (fileRows.length > 0) {
                    filledFields += fileRows.length;
                    totalFields += 1;
                    fieldAnalysis.uploadedFiles = fileRows.length;
                }
                
                var progressPercent = totalFields > 0 ? (filledFields / totalFields) * 100 : 0;
                
                Logger.progress('Form progress calculated', {
                    step: this.currentStep,
                    totalFields: totalFields,
                    filledFields: filledFields,
                    progressPercent: progressPercent.toFixed(1) + '%',
                    analysis: fieldAnalysis
                });
                
                return progressPercent;
            } catch (e) {
                Logger.error('Form progress calculation failed', { error: e.message });
                return 0;
            }
        },
        
        bindEvents: function() {
            try {
                Logger.info('Binding user interaction events');
                
                // Track input changes
                document.addEventListener('input', function(e) {
                    this.handleInputChange(e, 'input');
                }.bind(this));
                
                document.addEventListener('change', function(e) {
                    this.handleInputChange(e, 'change');
                }.bind(this));
                
                // Track focus events
                document.addEventListener('focus', function(e) {
                    if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) {
                        Logger.input(e.target, 'focused');
                        Logger.user('field_focused', {
                            field: Logger.getElementDescription(e.target),
                            step: this.currentStep
                        });
                    }
                }.bind(this), true);
                
                // Track blur events
                document.addEventListener('blur', function(e) {
                    if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) {
                        Logger.input(e.target, 'blurred', {
                            finalValue: e.target.value,
                            filled: this.isInputFilled(e.target)
                        });
                    }
                }.bind(this), true);
                
                // Handle step clicks
                var indicators = document.querySelectorAll('.step-indicator');
                indicators.forEach(function(indicator) {
                    indicator.addEventListener('click', function(e) {
                        var stepNum = parseInt(e.target.getAttribute('data-step'));
                        
                        Logger.user('step_clicked', {
                            targetStep: stepNum,
                            currentStep: this.currentStep,
                            submissionProgress: this.submissionProgress
                        });
                        
                        // Can click current step or completed steps
                        if (stepNum === this.currentStep || stepNum <= this.submissionProgress) {
                            Logger.user('step_navigation_allowed', {
                                from: this.currentStep,
                                to: stepNum
                            });
                            this.navigateToStep(stepNum);
                        } else {
                            Logger.user('step_navigation_blocked', {
                                targetStep: stepNum,
                                reason: 'step_not_accessible'
                            });
                        }
                    }.bind(this));
                }.bind(this));
                
                Logger.info('Event binding completed successfully');
            } catch (e) {
                Logger.error('Event binding failed', { error: e.message });
            }
        },
        
        handleInputChange: function(event, eventType) {
            try {
                var target = event.target;
                var inputTypes = ['INPUT', 'TEXTAREA', 'SELECT'];
                
                if (inputTypes.indexOf(target.tagName) !== -1) {
                    var wasFilledBefore = this.isInputFilled(target);
                    var currentValue = target.value;
                    var isFilledNow = this.isInputFilled(target);
                    
                    Logger.input(target, eventType, {
                        oldFilled: wasFilledBefore,
                        newFilled: isFilledNow,
                        valueLength: currentValue ? currentValue.length : 0,
                        valuePreview: target.type === 'password' ? '[HIDDEN]' : (currentValue || '').substring(0, 50),
                        step: this.currentStep
                    });
                    
                    // Log significant changes
                    if (wasFilledBefore !== isFilledNow) {
                        Logger.user(isFilledNow ? 'field_completed' : 'field_cleared', {
                            field: Logger.getElementDescription(target),
                            step: this.currentStep
                        });
                    }
                    
                    // Show auto-save indicator
                    this.showAutoSaveIndicator();
                    
                    // Update progress bar
                    this.updateProgressBar();
                }
            } catch (e) {
                Logger.error('Input change handling failed', { error: e.message });
            }
        },
        
        showAutoSaveIndicator: function() {
            try {
                var indicator = document.querySelector('.auto-save-indicator');
                if (indicator) {
                    indicator.classList.add('show');
                    
                    // Hide after 2 seconds
                    setTimeout(function() {
                        indicator.classList.remove('show');
                    }, 2000);
                    
                    Logger.info('Auto-save indicator shown');
                }
            } catch (e) {
                Logger.error('Auto-save indicator failed', { error: e.message });
            }
        },
        
        navigateToStep: function(stepNum) {
            try {
                Logger.user('attempting_navigation', {
                    from: this.currentStep,
                    to: stepNum,
                    url: window.location.href
                });
                
                var articleId = window.articleId || '';
                if (articleId) {
                    var url = window.location.pathname.replace(/\/\d+$/, '/' + stepNum) + '?articleId=' + encodeURIComponent(articleId);
                    
                    Logger.user('navigation_initiated', {
                        targetUrl: url,
                        method: 'window_location_change'
                    });
                    
                    window.location.href = url;
                } else {
                    Logger.warn('Navigation failed - no articleId found');
                }
            } catch (e) {
                Logger.error('Navigation failed', { error: e.message });
            }
        },
        
        // Public method to get current logging data
        getActivityLog: function() {
            return {
                currentStep: this.currentStep,
                submissionProgress: this.submissionProgress,
                inputTracking: this.inputTracking,
                userActivity: this.userActivity,
                timestamp: new Date().toISOString()
            };
        }
    };

    // Safe initialization function
    function initializeProgress() {
        try {
            var currentStep = window.submitStep || 1;
            var submissionProgress = window.submissionProgress || 0;
            
            Logger.info('Starting SimpleProgress initialization', {
                templateData: {
                    currentStep: currentStep,
                    submissionProgress: submissionProgress
                },
                pageInfo: {
                    url: window.location.href,
                    title: document.title,
                    readyState: document.readyState
                }
            });
            
            SimpleProgress.init(currentStep, submissionProgress);
        } catch (e) {
            Logger.error('SimpleProgress initialization failed', { error: e.message, stack: e.stack });
        }
    }

    // Initialize when DOM is ready with multiple fallbacks
    if (document.readyState === 'loading') {
        Logger.info('DOM still loading, waiting for DOMContentLoaded');
        document.addEventListener('DOMContentLoaded', initializeProgress);
    } else {
        Logger.info('DOM already ready, initializing immediately');
        initializeProgress();
    }

    // Only expose minimal API to global scope
    if (typeof window !== 'undefined') {
        window.SimpleProgress = {
            init: function(currentStep, submissionProgress) {
                return SimpleProgress.init(currentStep, submissionProgress);
            },
            getLog: function() {
                return SimpleProgress.getActivityLog();
            },
            enableVerboseLogging: function() {
                Logger.info('Verbose logging enabled');
                return true;
            }
        };
    }

})(); // <- IIFE closes here - all code above is private and isolated