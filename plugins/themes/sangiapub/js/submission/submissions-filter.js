/**
 * submissions-filter.js
 * SIMPLE implementation for Clear All functionality
 */

// SIMPLE Clear All function
function clearAllFilters() {
    console.log('[Wizdam Filter Feature] Simple clear all triggered');
    
    try {
        // Method 1: Simple URL redirect without parameters
        var cleanUrl = window.location.protocol + '//' + window.location.host + window.location.pathname;
        window.location.href = cleanUrl;
        
    } catch (error) {
        // Method 2: Page reload
        window.location.reload();
    }
}

// SIMPLE date picker setup
function setupSimpleDatePickers() {
    if (typeof flatpickr !== 'undefined') {
        var fromInput = document.getElementById('dateFrom');
        var toInput = document.getElementById('dateTo');
        
        if (fromInput) {
            flatpickr(fromInput, {
                dateFormat: "Y-m-d",
                allowInput: true,
                onChange: function(selectedDates, dateStr) {
                    updateSimpleDateFields('dateFrom', dateStr);
                }
            });
        }
        
        if (toInput) {
            flatpickr(toInput, {
                dateFormat: "Y-m-d",
                allowInput: true,
                onChange: function(selectedDates, dateStr) {
                    updateSimpleDateFields('dateTo', dateStr);
                }
            });
        }
    }
}

// SIMPLE date field update
function updateSimpleDateFields(fieldType, dateStr) {
    var prefix = fieldType === 'dateFrom' ? 'dateFrom' : 'dateTo';
    
    if (!dateStr) {
        var dayField = document.getElementById(prefix + 'Day');
        var monthField = document.getElementById(prefix + 'Month');
        var yearField = document.getElementById(prefix + 'Year');
        
        if (dayField) dayField.value = '';
        if (monthField) monthField.value = '';
        if (yearField) yearField.value = '';
        return;
    }
    
    var date = new Date(dateStr);
    if (!isNaN(date.getTime())) {
        var dayField = document.getElementById(prefix + 'Day');
        var monthField = document.getElementById(prefix + 'Month');
        var yearField = document.getElementById(prefix + 'Year');
        
        if (dayField) dayField.value = date.getDate();
        if (monthField) monthField.value = date.getMonth() + 1;
        if (yearField) yearField.value = date.getFullYear();
    }
}

// SIMPLE form validation
function validateSimpleForm() {
    var dateFrom = document.getElementById('dateFrom');
    var dateTo = document.getElementById('dateTo');
    var dateSearchField = document.getElementById('dateSearchField');
    
    if ((dateFrom && dateFrom.value) || (dateTo && dateTo.value)) {
        if (!dateSearchField || !dateSearchField.value) {
            alert('Please select a date field when using date range filter.');
            return false;
        }
    }
    
    return true;
}

// SIMPLE initialization
function initSimpleFilter() {
    console.log('[Wizdam Filter Feature] Simple initialization');
    
    var form = document.getElementById('filterForm');
    if (form) {
        // Setup date pickers
        setupSimpleDatePickers();
        
        // Setup form validation
        form.addEventListener('submit', function(e) {
            if (!validateSimpleForm()) {
                e.preventDefault();
                return false;
            }
        });
        
        console.log('[Wizdam Filter Feature] Simple setup completed');
    }
}

// AUTO-INITIALIZE
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(initSimpleFilter, 100);
    });
} else {
    setTimeout(initSimpleFilter, 100);
}

console.log('[Wizdam Filter Feature] Simple script loaded');