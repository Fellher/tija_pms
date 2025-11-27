<!-- Universal Date Picker Initialization -->
<script>
/**
 * Universal Flatpickr Initialization
 * Automatically initializes all date inputs on the page
 *
 * Usage: Include this file in any page that has date inputs
 */

(function() {
    'use strict';

    // Configuration for different date picker types
    const datePickerConfigs = {
        // Standard date picker
        default: {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'F j, Y',
            allowInput: true
        },

        // Date picker with today as default
        withToday: {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'F j, Y',
            defaultDate: new Date(),
            allowInput: true
        },

        // Date picker with minimum date (today)
        futureOnly: {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'F j, Y',
            minDate: 'today',
            allowInput: true
        },

        // Date picker for past dates only
        pastOnly: {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'F j, Y',
            maxDate: 'today',
            allowInput: true
        },

        // Date and time picker
        dateTime: {
            dateFormat: 'Y-m-d H:i',
            altInput: true,
            altFormat: 'F j, Y at h:i K',
            enableTime: true,
            time_24hr: false,
            allowInput: true
        }
    };

    /**
     * Initialize flatpickr on elements
     */
    function initializeDatePickers() {
        // Standard date inputs (type="date")
        document.querySelectorAll('input[type="date"]:not(.flatpickr-input)').forEach(input => {
            const config = {...datePickerConfigs.default};

            // Check for data attributes to customize
            if (input.hasAttribute('data-min-today')) {
                config.minDate = 'today';
            }
            if (input.hasAttribute('data-max-today')) {
                config.maxDate = 'today';
            }
            if (input.hasAttribute('data-enable-time')) {
                config.enableTime = true;
                config.dateFormat = 'Y-m-d H:i';
            }

            // Preserve existing value
            if (input.value) {
                config.defaultDate = input.value;
            }

            // Change input type to text for better flatpickr display
            input.type = 'text';
            input.setAttribute('readonly', 'readonly');

            flatpickr(input, config);
        });

        // Issue date specific
        document.querySelectorAll('input[name*="IssueDate"]:not(.flatpickr-input), input[id*="IssueDate"]:not(.flatpickr-input)').forEach(input => {
            if (input.type === 'text' && !input.classList.contains('flatpickr-input')) {
                flatpickr(input, {
                    ...datePickerConfigs.withToday,
                    onChange: function(selectedDates) {
                        // Try to find corresponding expiry date field
                        const expiryInput = findCorrespondingExpiryDate(input);
                        if (expiryInput && expiryInput._flatpickr) {
                            // Auto-set expiry to 1 year from issue
                            const expiryDate = new Date(selectedDates[0]);
                            expiryDate.setFullYear(expiryDate.getFullYear() + 1);
                            expiryInput._flatpickr.setDate(expiryDate);
                        }
                    }
                });
            }
        });

        // Expiry date specific
        document.querySelectorAll('input[name*="ExpiryDate"]:not(.flatpickr-input), input[id*="ExpiryDate"]:not(.flatpickr-input)').forEach(input => {
            if (input.type === 'text' && !input.classList.contains('flatpickr-input')) {
                const config = {
                    ...datePickerConfigs.futureOnly
                };

                // Set default to 1 year from now if no value
                if (!input.value) {
                    const defaultExpiry = new Date();
                    defaultExpiry.setFullYear(defaultExpiry.getFullYear() + 1);
                    config.defaultDate = defaultExpiry;
                }

                flatpickr(input, config);
            }
        });

        // Start date specific
        document.querySelectorAll('input[name*="StartDate"]:not(.flatpickr-input), input[id*="StartDate"]:not(.flatpickr-input)').forEach(input => {
            if (input.type === 'text' && !input.classList.contains('flatpickr-input')) {
                flatpickr(input, datePickerConfigs.default);
            }
        });

        // End date specific
        document.querySelectorAll('input[name*="EndDate"]:not(.flatpickr-input), input[id*="EndDate"]:not(.flatpickr-input)').forEach(input => {
            if (input.type === 'text' && !input.classList.contains('flatpickr-input')) {
                flatpickr(input, datePickerConfigs.default);
            }
        });

        // Birth date / DOB specific (past only)
        document.querySelectorAll('input[name*="BirthDate"]:not(.flatpickr-input), input[name*="DOB"]:not(.flatpickr-input), input[id*="BirthDate"]:not(.flatpickr-input)').forEach(input => {
            if (input.type === 'text' && !input.classList.contains('flatpickr-input')) {
                flatpickr(input, datePickerConfigs.pastOnly);
            }
        });

        // Generic date class
        document.querySelectorAll('.date-picker:not(.flatpickr-input)').forEach(input => {
            flatpickr(input, datePickerConfigs.default);
        });

        // Date range picker (coming soon/future only)
        document.querySelectorAll('.date-range-picker:not(.flatpickr-input)').forEach(input => {
            flatpickr(input, {
                ...datePickerConfigs.default,
                mode: 'range'
            });
        });
    }

    /**
     * Find corresponding expiry date field for an issue date field
     */
    function findCorrespondingExpiryDate(issueInput) {
        const issueName = issueInput.name || issueInput.id;
        const baseNameMatch = issueName.match(/(.*?)IssueDate/i);

        if (baseNameMatch) {
            const baseName = baseNameMatch[1];
            // Try to find expiry field with same base name
            const expiryInput = document.querySelector(
                `input[name="${baseName}ExpiryDate"], input[name="${baseName}expiryDate"], ` +
                `input[id="${baseName}ExpiryDate"], input[id="${baseName}expiryDate"]`
            );
            return expiryInput;
        }

        // Try generic expiry field in same form
        const form = issueInput.closest('form');
        if (form) {
            return form.querySelector('input[name*="ExpiryDate"], input[name*="expiryDate"]');
        }

        return null;
    }

    /**
     * Re-initialize date pickers (useful for dynamically added content)
     */
    window.reinitializeDatePickers = function() {
        initializeDatePickers();
    };

    /**
     * Initialize on DOM ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeDatePickers);
    } else {
        initializeDatePickers();
    }

    /**
     * Watch for dynamically added content
     */
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        // Check if the added node or its children have date inputs
                        const dateInputs = node.querySelectorAll ?
                            node.querySelectorAll('input[type="date"]:not(.flatpickr-input), input[type="text"][name*="Date"]:not(.flatpickr-input)') :
                            [];

                        if (dateInputs.length > 0 || (node.type === 'date' || (node.name && node.name.includes('Date')))) {
                            // Wait a bit for DOM to settle, then initialize
                            setTimeout(initializeDatePickers, 100);
                        }
                    }
                });
            }
        });
    });

    // Observe the entire document for changes
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

})();

console.log('✅ Flatpickr date pickers initialized');
</script>

<style>
/* Flatpickr Custom Styling */
.flatpickr-input[readonly] {
    background-color: #ffffff !important;
    cursor: pointer;
}

.flatpickr-input[readonly]:hover {
    background-color: #f8f9fa !important;
}

.flatpickr-calendar {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    border-radius: 8px !important;
}

.flatpickr-day.selected {
    background: var(--primary-color, #5b6fe3) !important;
    border-color: var(--primary-color, #5b6fe3) !important;
}

.flatpickr-day.today {
    border-color: var(--primary-color, #5b6fe3) !important;
}

.flatpickr-day.today:hover {
    background: var(--primary-color, #5b6fe3) !important;
    color: white !important;
}

.flatpickr-months .flatpickr-month {
    background: var(--primary-color, #5b6fe3) !important;
}

.flatpickr-current-month .flatpickr-monthDropdown-months {
    background: var(--primary-color, #5b6fe3) !important;
}

.flatpickr-weekday {
    color: var(--primary-color, #5b6fe3) !important;
    font-weight: 600;
}
</style>

