/**
 * Simple Markdown Editor - SINGLE TEXTAREA ONLY
 * 
 * NO DUAL EDITOR. NO OVERLAY. NO BULLSHIT.
 * JUST KEYBOARD SHORTCUTS + SMART TOGGLE.
 */

(function() {
    'use strict';
    
    function SimpleMarkdownEditor(textarea) {
        this.textarea = textarea;
        this.init();
    }
    
    SimpleMarkdownEditor.prototype.init = function() {
        this.setupKeyboardShortcuts();
        this.addBasicStyling();
        console.log('SimpleMarkdownEditor initialized for:', this.textarea.id);
    };
    
    SimpleMarkdownEditor.prototype.addBasicStyling = function() {
        // Add basic styling to make markdown more readable
        this.textarea.style.cssText += `
            padding: 16px;
            border: 1px solid #ddd;
        `;
    };
    
    SimpleMarkdownEditor.prototype.setupKeyboardShortcuts = function() {
        this.textarea.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key.toLowerCase()) {
                    case 'b':
                        e.preventDefault();
                        this.toggleFormatting('bold');
                        break;
                    case 'i':
                        e.preventDefault();
                        this.toggleFormatting('italic');
                        break;
                    case 'u':
                        e.preventDefault();
                        this.toggleFormatting('underline');
                        break;
                }
            }
        });
    };
    
    SimpleMarkdownEditor.prototype.toggleFormatting = function(type) {
        const textarea = this.textarea;
        const startPos = textarea.selectionStart;
        const endPos = textarea.selectionEnd;
        const selectedText = textarea.value.substring(startPos, endPos);
        
        console.log('toggleFormatting:', { type, startPos, endPos, selectedText });
        
        let replacement;
        let newCursorStart, newCursorEnd;
        
        if (selectedText && selectedText.length > 0) {
            replacement = this.smartToggle(selectedText, type);
            newCursorStart = startPos;
            newCursorEnd = startPos + replacement.length;
            console.log('Smart toggle result:', replacement);
        } else {
            // No selection - insert placeholder
            const markers = {
                'bold': ['**', '**'],
                'italic': ['*', '*'],
                'underline': ['__', '__']
            };
            
            const [start, end] = markers[type];
            replacement = start + 'text' + end;
            newCursorStart = startPos + start.length;
            newCursorEnd = startPos + start.length + 4; // Select 'text'
            console.log('Inserting placeholder:', replacement);
        }
        
        // Replace text
        const beforeText = textarea.value.substring(0, startPos);
        const afterText = textarea.value.substring(endPos);
        textarea.value = beforeText + replacement + afterText;
        
        // Set selection
        textarea.setSelectionRange(newCursorStart, newCursorEnd);
        
        // Focus back
        textarea.focus();
        
        console.log('toggleFormatting completed:', replacement);
    };
    
    SimpleMarkdownEditor.prototype.smartToggle = function(text, type) {
        // Detect current formatting state
        const state = this.detectFormatting(text);
        console.log('Current state:', state);
        
        // Extract core text (without any formatting)
        const coreText = this.extractCoreText(text);
        console.log('Core text:', coreText);
        
        // Toggle the specific formatting
        if (type === 'bold') {
            state.bold = !state.bold;
        } else if (type === 'italic') {
            state.italic = !state.italic;
        } else if (type === 'underline') {
            state.underline = !state.underline;
        }
        
        // Build new formatted text
        const result = this.buildFormattedText(coreText, state);
        console.log('New state:', state, 'Result:', result);
        return result;
    };
    
    SimpleMarkdownEditor.prototype.detectFormatting = function(text) {
        const state = { bold: false, italic: false, underline: false };
        
        // Check for various combinations
        if (text.match(/^\*\*\*__(.+)__\*\*\*$/)) {
            // Bold + Italic + Underline
            state.bold = true;
            state.italic = true;
            state.underline = true;
        } else if (text.match(/^\*\*__(.+)__\*\*$/)) {
            // Bold + Underline
            state.bold = true;
            state.underline = true;
        } else if (text.match(/^\*__(.+)__\*$/)) {
            // Italic + Underline
            state.italic = true;
            state.underline = true;
        } else if (text.match(/^\*\*\*(.+)\*\*\*$/)) {
            // Bold + Italic
            state.bold = true;
            state.italic = true;
        } else if (text.match(/^\*\*(.+)\*\*$/)) {
            // Bold only
            state.bold = true;
        } else if (text.match(/^\*(.+)\*$/)) {
            // Italic only
            state.italic = true;
        } else if (text.match(/^__(.+)__$/)) {
            // Underline only
            state.underline = true;
        }
        
        return state;
    };
    
    SimpleMarkdownEditor.prototype.extractCoreText = function(text) {
        // Remove all formatting to get core text
        return text
            .replace(/^\*\*\*__(.+)__\*\*\*$/, '$1')
            .replace(/^\*\*__(.+)__\*\*$/, '$1')
            .replace(/^\*__(.+)__\*$/, '$1')
            .replace(/^\*\*\*(.+)\*\*\*$/, '$1')
            .replace(/^\*\*(.+)\*\*$/, '$1')
            .replace(/^\*(.+)\*$/, '$1')
            .replace(/^__(.+)__$/, '$1');
    };
    
    SimpleMarkdownEditor.prototype.buildFormattedText = function(coreText, state) {
        let result = coreText;
        
        // Apply formatting in correct order
        if (state.underline) {
            result = '__' + result + '__';
        }
        
        if (state.italic) {
            result = '*' + result + '*';
        }
        
        if (state.bold) {
            result = '**' + result + '**';
        }
        
        return result;
    };
    
    // Auto-initialize
    function initSimpleEditors() {
        // Find textareas with markdown-editor class
        const textareas = document.querySelectorAll('textarea.markdown-editor');
        textareas.forEach(textarea => {
            if (!textarea.simpleMarkdownEditor) {
                textarea.simpleMarkdownEditor = new SimpleMarkdownEditor(textarea);
            }
        });
        
        // Also find textareas with data-markdown-editor attribute
        const dataTextareas = document.querySelectorAll('textarea[data-markdown-editor]');
        dataTextareas.forEach(textarea => {
            if (!textarea.simpleMarkdownEditor) {
                textarea.simpleMarkdownEditor = new SimpleMarkdownEditor(textarea);
            }
        });
    }
    
    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSimpleEditors);
    } else {
        initSimpleEditors();
    }
    
    // Export to global
    window.SimpleMarkdownEditor = SimpleMarkdownEditor;
    window.initSimpleEditors = initSimpleEditors;
    
})();