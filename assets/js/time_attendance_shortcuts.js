/**
 * Time Attendance Keyboard Shortcuts & Accessibility
 * ===================================================
 * Provides keyboard shortcuts and accessibility enhancements for the time attendance module
 */

(function() {
    'use strict';

    // ========================================
    // CONFIGURATION
    // ========================================
    const SHORTCUTS = {
        // Quick Actions
        'ctrl+n': { action: 'newEntry', description: 'New time entry' },
        'ctrl+shift+n': { action: 'newAbsence', description: 'New absence' },
        'ctrl+shift+e': { action: 'newExpense', description: 'New expense' },

        // Navigation
        'ctrl+left': { action: 'previousDay', description: 'Previous day' },
        'ctrl+right': { action: 'nextDay', description: 'Next day' },
        'ctrl+t': { action: 'today', description: 'Go to today' },

        // Templates & Quick Entry
        'ctrl+shift+c': { action: 'copyPrevious', description: 'Copy from previous day' },
        'ctrl+shift+t': { action: 'useTemplate', description: 'Use template' },
        'ctrl+s': { action: 'saveTemplate', description: 'Save as template' },

        // Utility
        'esc': { action: 'closeModals', description: 'Close modals/dialogs' },
        'ctrl+shift+?': { action: 'showHelp', description: 'Show keyboard shortcuts' },
        'f2': { action: 'focusSearch', description: 'Focus on search' }
    };

    // ========================================
    // INITIALIZATION
    // ========================================
    let isInitialized = false;

    function init() {
        if (isInitialized) return;

        document.addEventListener('DOMContentLoaded', function() {
            setupKeyboardShortcuts();
            setupAccessibilityEnhancements();
            setupQuickActions();
            createShortcutsHelp();
            announceShortcutsAvailability();

            isInitialized = true;
            console.log('Time Attendance Shortcuts initialized');
        });
    }

    // ========================================
    // KEYBOARD SHORTCUTS
    // ========================================
    function setupKeyboardShortcuts() {
        document.addEventListener('keydown', function(e) {
            // Don't trigger shortcuts when typing in inputs
            if (isTyping(e.target)) {
                // Exception for Ctrl+S to save template
                if (!(e.ctrlKey && e.key === 's')) {
                    return;
                }
            }

            const shortcut = getShortcutKey(e);

            if (SHORTCUTS[shortcut]) {
                e.preventDefault();
                executeShortcut(SHORTCUTS[shortcut].action, e);
            }
        });
    }

    function getShortcutKey(e) {
        let key = e.key.toLowerCase();
        let shortcut = '';

        if (e.ctrlKey) shortcut += 'ctrl+';
        if (e.shiftKey) shortcut += 'shift+';
        if (e.altKey) shortcut += 'alt+';

        shortcut += key;

        return shortcut;
    }

    function isTyping(element) {
        const typingElements = ['INPUT', 'TEXTAREA', 'SELECT'];
        return typingElements.includes(element.tagName) ||
               element.isContentEditable;
    }

    function executeShortcut(action, event) {
        console.log('Executing shortcut:', action);

        switch (action) {
            case 'newEntry':
                openNewEntryModal();
                break;
            case 'newAbsence':
                openModal('manageAbsence');
                break;
            case 'newExpense':
                openModal('addExpense');
                break;
            case 'previousDay':
                navigateDay(-1);
                break;
            case 'nextDay':
                navigateDay(1);
                break;
            case 'today':
                navigateToToday();
                break;
            case 'copyPrevious':
                copyFromPreviousDay();
                break;
            case 'useTemplate':
                openTemplateSelector();
                break;
            case 'saveTemplate':
                event.preventDefault();
                saveCurrentAsTemplate();
                break;
            case 'closeModals':
                closeAllModals();
                break;
            case 'showHelp':
                showShortcutsHelp();
                break;
            case 'focusSearch':
                focusSearchInput();
                break;
        }
    }

    // ========================================
    // SHORTCUT ACTIONS
    // ========================================
    function openNewEntryModal() {
        const addWorkBtn = document.querySelector('.addWorkHourBtn');
        if (addWorkBtn) {
            addWorkBtn.click();
        } else {
            const collapseBtn = document.querySelector('[data-bs-target="#add_work_hours"]');
            if (collapseBtn) collapseBtn.click();
        }
    }

    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }
    }

    function navigateDay(offset) {
        // Get current date from URL or page
        const urlParams = new URLSearchParams(window.location.search);
        let currentDate = urlParams.get('d');

        if (!currentDate) {
            currentDate = new Date().toISOString().split('T')[0];
        }

        const date = new Date(currentDate);
        date.setDate(date.getDate() + offset);

        const newDate = date.toISOString().split('T')[0];
        urlParams.set('d', newDate);

        window.location.search = urlParams.toString();
    }

    function navigateToToday() {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.delete('d');
        urlParams.delete('week');
        urlParams.delete('year');
        window.location.search = urlParams.toString();
    }

    function copyFromPreviousDay() {
        const copyBtn = document.getElementById('copyPreviousDayBtn');
        if (copyBtn) {
            copyBtn.click();
        } else {
            showNotification('Copy from previous day feature not available on this page', 'info');
        }
    }

    function openTemplateSelector() {
        const templateBtn = document.getElementById('useTemplateBtn');
        if (templateBtn) {
            templateBtn.click();
        } else {
            showNotification('Template feature not available on this page', 'info');
        }
    }

    function saveCurrentAsTemplate() {
        const saveBtn = document.getElementById('saveAsTemplateBtn');
        if (saveBtn) {
            saveBtn.click();
        } else {
            showNotification('Cannot save template from this view', 'warning');
        }
    }

    function closeAllModals() {
        // Close Bootstrap modals
        const modals = document.querySelectorAll('.modal.show');
        modals.forEach(modal => {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        });

        // Close collapse elements
        const collapses = document.querySelectorAll('.collapse.show');
        collapses.forEach(collapse => {
            const bsCollapse = bootstrap.Collapse.getInstance(collapse);
            if (bsCollapse) {
                bsCollapse.hide();
            }
        });
    }

    function focusSearchInput() {
        const searchInput = document.getElementById('projectTaskSearch');
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
    }

    // ========================================
    // ACCESSIBILITY ENHANCEMENTS
    // ========================================
    function setupAccessibilityEnhancements() {
        // Add ARIA labels
        addAriaLabels();

        // Enhance focus indicators
        enhanceFocusIndicators();

        // Add skip links
        addSkipLinks();

        // Improve form accessibility
        improveFormAccessibility();

        // Add keyboard navigation for custom elements
        enhanceCustomElementNavigation();
    }

    function addAriaLabels() {
        // Add labels to buttons without text
        const iconButtons = document.querySelectorAll('button:not([aria-label]) i.fa-solid');
        iconButtons.forEach(icon => {
            const button = icon.closest('button');
            if (button && !button.getAttribute('aria-label')) {
                const title = button.getAttribute('title');
                if (title) {
                    button.setAttribute('aria-label', title);
                }
            }
        });

        // Add role="status" to dynamic content areas
        const statusAreas = document.querySelectorAll('.alert, .toast, .notification');
        statusAreas.forEach(area => {
            area.setAttribute('role', 'status');
            area.setAttribute('aria-live', 'polite');
        });
    }

    function enhanceFocusIndicators() {
        // Add custom focus class for better visibility
        const focusableElements = document.querySelectorAll(
            'a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );

        focusableElements.forEach(element => {
            element.addEventListener('focus', function() {
                this.classList.add('keyboard-focus');
            });

            element.addEventListener('blur', function() {
                this.classList.remove('keyboard-focus');
            });
        });
    }

    function addSkipLinks() {
        // Add skip to main content link
        const skipLink = document.createElement('a');
        skipLink.href = '#main-content';
        skipLink.className = 'skip-link sr-only sr-only-focusable';
        skipLink.textContent = 'Skip to main content';
        skipLink.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            background: var(--primary-color);
            color: white;
            padding: 1rem;
            z-index: 9999;
            text-decoration: none;
        `;

        document.body.insertBefore(skipLink, document.body.firstChild);

        // Ensure main content has ID
        const mainContent = document.querySelector('.main-content, main, [role="main"]');
        if (mainContent && !mainContent.id) {
            mainContent.id = 'main-content';
        }
    }

    function improveFormAccessibility() {
        // Associate labels with inputs
        const inputs = document.querySelectorAll('input:not([id]), select:not([id]), textarea:not([id])');
        inputs.forEach((input, index) => {
            if (!input.id) {
                input.id = `input-${index}`;
            }

            const label = input.closest('.form-group')?.querySelector('label');
            if (label && !label.getAttribute('for')) {
                label.setAttribute('for', input.id);
            }
        });

        // Add required indicators
        const requiredInputs = document.querySelectorAll('[required]');
        requiredInputs.forEach(input => {
            const label = document.querySelector(`label[for="${input.id}"]`);
            if (label && !label.querySelector('.required-indicator')) {
                const indicator = document.createElement('span');
                indicator.className = 'required-indicator';
                indicator.textContent = ' *';
                indicator.setAttribute('aria-label', 'required');
                indicator.style.color = 'var(--danger-color)';
                label.appendChild(indicator);
            }
        });
    }

    function enhanceCustomElementNavigation() {
        // Make div/span buttons keyboard accessible
        const customButtons = document.querySelectorAll('[onclick]:not(button):not(a)');
        customButtons.forEach(element => {
            if (!element.getAttribute('role')) {
                element.setAttribute('role', 'button');
            }
            if (!element.getAttribute('tabindex')) {
                element.setAttribute('tabindex', '0');
            }

            // Add keyboard support
            element.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.click();
                }
            });
        });
    }

    // ========================================
    // QUICK ACTIONS INTEGRATION
    // ========================================
    function setupQuickActions() {
        // Listen for quick action events
        document.addEventListener('click', function(e) {
            // Copy previous day
            if (e.target.id === 'copyPreviousDayBtn' || e.target.closest('#copyPreviousDayBtn')) {
                handleCopyPreviousDay();
            }

            // Use template
            if (e.target.id === 'useTemplateBtn' || e.target.closest('#useTemplateBtn')) {
                handleUseTemplate();
            }

            // Save template
            if (e.target.id === 'saveAsTemplateBtn' || e.target.closest('#saveAsTemplateBtn')) {
                handleSaveTemplate();
            }
        });
    }

    function handleCopyPreviousDay() {
        // Show loading
        showNotification('Loading previous day entries...', 'info');

        fetch(siteUrl + 'php/scripts/time_attendance/quick_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=copy_previous_day&targetDate=' + getCurrentDate()
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showCopyPreviousDialog(data.data);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            showNotification('Error loading previous entries', 'error');
            console.error('Error:', error);
        });
    }

    function handleUseTemplate() {
        showNotification('Loading templates...', 'info');

        fetch(siteUrl + 'php/scripts/time_attendance/quick_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_templates'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showTemplateSelector(data.data.templates);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            showNotification('Error loading templates', 'error');
            console.error('Error:', error);
        });
    }

    function handleSaveTemplate() {
        // Show template name input dialog
        const templateName = prompt('Enter template name:');
        if (!templateName) return;

        // Collect current form data
        const formData = collectFormData();

        fetch(siteUrl + 'php/scripts/time_attendance/quick_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=save_template&templateName=${encodeURIComponent(templateName)}&templateData=${encodeURIComponent(JSON.stringify(formData))}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Template saved successfully', 'success');
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            showNotification('Error saving template', 'error');
            console.error('Error:', error);
        });
    }

    // ========================================
    // HELPER FUNCTIONS
    // ========================================
    function showCopyPreviousDialog(data) {
        const message = `Found ${data.count} entries from ${data.sourceDate}. Copy them to today?`;
        if (confirm(message)) {
            // Populate form with entries
            data.entries.forEach(entry => {
                console.log('Copying entry:', entry);
                // Implementation depends on form structure
            });
            showNotification('Entries copied successfully', 'success');
        }
    }

    function showTemplateSelector(templates) {
        if (templates.length === 0) {
            showNotification('No templates found', 'info');
            return;
        }

        // Create simple selector (can be enhanced with modal)
        let message = 'Select a template:\n';
        templates.forEach((template, index) => {
            message += `${index + 1}. ${template.templateName}\n`;
        });

        const selection = prompt(message + '\nEnter template number:');
        if (selection) {
            const index = parseInt(selection) - 1;
            if (index >= 0 && index < templates.length) {
                loadTemplate(templates[index].templateID);
            }
        }
    }

    function loadTemplate(templateID) {
        fetch(siteUrl + 'php/scripts/time_attendance/quick_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=load_template&templateID=${templateID}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateFormWithTemplate(data.data.templateData);
                showNotification('Template loaded', 'success');
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            showNotification('Error loading template', 'error');
            console.error('Error:', error);
        });
    }

    function collectFormData() {
        // Collect form data from current entry
        return {
            projectID: document.getElementById('hiddenProjectID')?.value || '',
            projectPhaseID: document.getElementById('hiddenPhaseID')?.value || '',
            projectTaskID: document.getElementById('hiddenTaskID')?.value || '',
            workTypeID: document.getElementById('workTypeID')?.value || '',
            taskDuration: document.getElementById('taskDuration')?.value || '',
            taskStatusID: document.getElementById('taskStatusID')?.value || ''
        };
    }

    function populateFormWithTemplate(data) {
        // Populate form with template data
        if (data.projectID) document.getElementById('hiddenProjectID').value = data.projectID;
        if (data.workTypeID) document.getElementById('workTypeID').value = data.workTypeID;
        if (data.taskDuration) document.getElementById('taskDuration').value = data.taskDuration;
        if (data.taskStatusID) document.getElementById('taskStatusID').value = data.taskStatusID;
    }

    function getCurrentDate() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('d') || new Date().toISOString().split('T')[0];
    }

    function showNotification(message, type = 'info') {
        // Simple notification (can be enhanced with toast library)
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} fixed-top mx-auto mt-3`;
        notification.style.cssText = 'max-width: 500px; z-index: 9999; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
        notification.textContent = message;
        notification.setAttribute('role', 'alert');

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // ========================================
    // SHORTCUTS HELP
    // ========================================
    function createShortcutsHelp() {
        const helpModal = document.createElement('div');
        helpModal.id = 'shortcutsHelpModal';
        helpModal.className = 'modal fade';
        helpModal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-keyboard me-2"></i>
                            Keyboard Shortcuts
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            ${generateShortcutsHTML()}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(helpModal);
    }

    function generateShortcutsHTML() {
        let html = '';
        const categories = {
            'Quick Actions': ['newEntry', 'newAbsence', 'newExpense'],
            'Navigation': ['previousDay', 'nextDay', 'today'],
            'Templates': ['copyPrevious', 'useTemplate', 'saveTemplate'],
            'Utility': ['closeModals', 'showHelp', 'focusSearch']
        };

        for (const [category, actions] of Object.entries(categories)) {
            html += `<div class="col-md-6 mb-3">
                        <h6 class="text-primary border-bottom pb-2">${category}</h6>
                        <ul class="list-unstyled">`;

            actions.forEach(action => {
                const shortcut = Object.entries(SHORTCUTS).find(([key, val]) => val.action === action);
                if (shortcut) {
                    const [key, data] = shortcut;
                    html += `<li class="mb-2">
                                <kbd class="bg-light px-2 py-1 rounded">${key.toUpperCase()}</kbd>
                                <span class="ms-2 text-muted">${data.description}</span>
                             </li>`;
                }
            });

            html += `</ul></div>`;
        }

        return html;
    }

    function showShortcutsHelp() {
        const modal = new bootstrap.Modal(document.getElementById('shortcutsHelpModal'));
        modal.show();
    }

    function announceShortcutsAvailability() {
        // Announce to screen readers that shortcuts are available
        const announcement = document.createElement('div');
        announcement.className = 'sr-only';
        announcement.setAttribute('role', 'status');
        announcement.setAttribute('aria-live', 'polite');
        announcement.textContent = 'Keyboard shortcuts available. Press Ctrl+Shift+? for help';
        document.body.appendChild(announcement);
    }

    // ========================================
    // EXPORT & INITIALIZE
    // ========================================
    window.TimeAttendanceShortcuts = {
        init: init,
        showHelp: showShortcutsHelp,
        executeShortcut: executeShortcut
    };

    // Auto-initialize
    init();
})();

