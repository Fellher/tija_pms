<?php
/**
 * Recurring Projects Notifications Widget
 *
 * Displays notifications for recurring projects that need attention
 * Should be included in the header or dashboard
 *
 * @package    TIJA_PMS
 * @subpackage Recurring Projects
 */

if (!$isValidUser) {
    return;
}

// Check for recurring project notifications via AJAX
?>

<div id="recurringProjectsNotifications" class="recurring-notifications-widget" style="display: none;">
    <div class="card custom-card border-warning mb-3">
        <div class="card-header bg-warning-transparent">
            <h6 class="mb-0">
                <i class="ri-repeat-line me-2"></i>Recurring Projects - Action Required
            </h6>
        </div>
        <div class="card-body" id="recurringNotificationsContent">
            <div class="text-center py-3">
                <i class="ri-loader-4-line spinner-border spinner-border-sm"></i>
                <p class="text-muted mb-0 mt-2">Loading notifications...</p>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    // Check for recurring project notifications
    function checkRecurringNotifications() {
        fetch('<?= $base ?>php/scripts/recurring_projects/check_recurring_notifications.php', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.notifications && data.notifications.length > 0) {
                displayNotifications(data.notifications);
            } else {
                // Hide widget if no notifications
                const widget = document.getElementById('recurringProjectsNotifications');
                if (widget) {
                    widget.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('Error checking recurring notifications:', error);
            // Hide widget on error
            const widget = document.getElementById('recurringProjectsNotifications');
            if (widget) {
                widget.style.display = 'none';
            }
        });
    }

    function displayNotifications(notifications) {
        const container = document.getElementById('recurringNotificationsContent');
        const widget = document.getElementById('recurringProjectsNotifications');

        if (!container || !widget) return;

        let html = '<div class="list-group list-group-flush">';

        notifications.forEach((notif, index) => {
            const priorityClass = notif.priority === 'high' ? 'list-group-item-danger' : 'list-group-item-warning';
            html += `
                <div class="list-group-item ${priorityClass}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">${escapeHtml(notif.title)}</h6>
                            <p class="mb-2">${escapeHtml(notif.message)}</p>
                            ${notif.action === 'generate_cycles' ? `
                                <button class="btn btn-sm btn-primary" onclick="generateNextCycles(${notif.projectID}, '${escapeHtml(notif.projectName)}')">
                                    <i class="ri-play-line me-1"></i>Generate Next Cycles
                                </button>
                            ` : ''}
                            ${notif.action === 'create_invoice_draft' ? `
                                <button class="btn btn-sm btn-success" onclick="createInvoiceDraft(${notif.billingCycleID}, ${notif.projectID}, '${escapeHtml(notif.projectName)}', ${notif.cycleNumber})">
                                    <i class="ri-file-add-line me-1"></i>Create Invoice Draft
                                </button>
                            ` : ''}
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-muted" onclick="dismissNotification(${index})">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        container.innerHTML = html;
        widget.style.display = 'block';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function generateNextCycles(projectID, projectName) {
        if (!confirm(`Generate next billing cycles for "${projectName}"?`)) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'generate_next_cycles');
        formData.append('projectID', projectID);

        // Show loading on button if it exists
        const btn = (typeof event !== 'undefined' && event?.target) ? event.target.closest('button') : null;
        let originalHTML = '';
        if (btn) {
            originalHTML = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i>Generating...';
        }

        fetch('<?= $base ?>php/scripts/recurring_projects/manage_billing_cycle.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (btn && originalHTML) {
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }

            if (data.success) {
                alert('Billing cycles generated successfully!');
                // Refresh notifications
                setTimeout(checkRecurringNotifications, 1000);
                // Optionally reload page or refresh billing cycles list
                if (window.location.href.includes('recurring_billing_cycles')) {
                    setTimeout(() => location.reload(), 1500);
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (btn && originalHTML) {
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
            alert('An error occurred while generating cycles');
        });
    }

    function createInvoiceDraft(billingCycleID, projectID, projectName, cycleNumber) {
        if (!confirm(`Create invoice draft for "${projectName}" - Cycle #${cycleNumber}?`)) {
            return;
        }

        const formData = new FormData();
        formData.append('billingCycleID', billingCycleID);

        // Show loading on button if it exists
        const btn = (typeof event !== 'undefined' && event?.target) ? event.target.closest('button') : null;
        let originalHTML = '';
        if (btn) {
            originalHTML = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i>Creating...';
        }

        fetch('<?= $base ?>php/scripts/recurring_projects/create_invoice_draft_manual.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (btn && originalHTML) {
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }

            if (data.success) {
                alert(`Invoice draft created successfully!\nInvoice Number: ${data.data.invoiceNumber}\nAmount: KES ${data.data.amount.toLocaleString()}`);
                // Refresh notifications
                setTimeout(checkRecurringNotifications, 1000);
                // Optionally redirect to invoice drafts page
                if (confirm('View invoice draft?')) {
                    window.location.href = '<?= $base ?>html/?s=user&ss=projects&p=invoice_drafts&draftid=' + data.data.invoiceID;
                } else if (window.location.href.includes('invoice_drafts')) {
                    setTimeout(() => location.reload(), 1500);
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (btn && originalHTML) {
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
            alert('An error occurred while creating invoice draft');
        });
    }

    function dismissNotification(index) {
        // Remove notification from display (could also mark as dismissed in database)
        const widget = document.getElementById('recurringProjectsNotifications');
        if (widget) {
            const notifications = widget.querySelectorAll('.list-group-item');
            if (notifications[index]) {
                notifications[index].remove();
                // Hide widget if no more notifications
                if (widget.querySelectorAll('.list-group-item').length === 0) {
                    widget.style.display = 'none';
                }
            }
        }
    }

    // Expose functions globally
    window.generateNextCycles = generateNextCycles;
    window.createInvoiceDraft = createInvoiceDraft;
    window.dismissNotification = dismissNotification;

    // Check notifications when page loads
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkRecurringNotifications);
    } else {
        checkRecurringNotifications();
    }

    // Refresh notifications every 5 minutes
    setInterval(checkRecurringNotifications, 5 * 60 * 1000);
})();
</script>

<style>
.recurring-notifications-widget {
    position: sticky;
    top: 20px;
    z-index: 1000;
}

.recurring-notifications-widget .list-group-item {
    border-left: 3px solid;
}

.recurring-notifications-widget .list-group-item-danger {
    border-left-color: #dc3545;
}

.recurring-notifications-widget .list-group-item-warning {
    border-left-color: #ffc107;
}
</style>

