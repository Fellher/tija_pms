document.addEventListener('DOMContentLoaded', () => {
    /**
     * Notification preferences form
     */
    const prefsForm = document.getElementById('notificationPrefsForm');
    if (prefsForm) {
        const statusEl = document.getElementById('notificationPrefsStatus');
        const actionUrl = prefsForm.dataset.action || '';
        const entityId = prefsForm.dataset.entityId || '';

        const toggleEnforceState = (enabledCheckbox, enforceCheckbox) => {
            if (!enabledCheckbox || !enforceCheckbox) {
                return;
                       }
            if (enabledCheckbox.checked) {
                enforceCheckbox.disabled = false;
            } else {
                enforceCheckbox.checked = false;
                enforceCheckbox.disabled = true;
            }
        };

        prefsForm.querySelectorAll('.channel-pref-row').forEach(row => {
            const enabled = row.querySelector('.pref-enabled');
            const enforce = row.querySelector('.pref-enforce');
            if (!enabled || !enforce) {
                return;
            }
            toggleEnforceState(enabled, enforce);
            enabled.addEventListener('change', () => toggleEnforceState(enabled, enforce));
        });

        prefsForm.addEventListener('submit', event => {
            event.preventDefault();
            if (!actionUrl) {
                return;
            }

            const submitBtn = prefsForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.dataset.originalText = submitBtn.dataset.originalText || submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...';
                submitBtn.disabled = true;
            }

            if (statusEl) {
                statusEl.classList.add('d-none');
            }

            const eventPreferences = {};
            prefsForm.querySelectorAll('.notification-pref-row').forEach(row => {
                const eventId = row.dataset.eventId;
                if (!eventId) {
                    return;
                }
                const enabledChannels = [];
                const enforceChannels = [];

                row.querySelectorAll('.channel-pref-row').forEach(channelRow => {
                    const channelSlug = channelRow.dataset.channelSlug;
                    if (!channelSlug) {
                        return;
                    }
                    const enabledCheckbox = channelRow.querySelector('.pref-enabled');
                    const enforceCheckbox = channelRow.querySelector('.pref-enforce');
                    if (enabledCheckbox && enabledCheckbox.checked) {
                        enabledChannels.push(channelSlug);
                    }
                    if (enforceCheckbox && enforceCheckbox.checked) {
                        enforceChannels.push(channelSlug);
                    }
                });

                eventPreferences[eventId] = {
                    enabledChannels,
                    enforceChannels
                };
            });

            const formData = new FormData();
            formData.append('entityID', entityId);
            formData.append('eventPreferences', JSON.stringify(eventPreferences));

            fetch(actionUrl, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (!statusEl) {
                        return;
                    }
                    statusEl.classList.remove('d-none', 'alert-success', 'alert-danger');
                    if (data.success) {
                        statusEl.classList.add('alert-success');
                        statusEl.textContent = data.message || 'Notification preferences updated successfully.';
                    } else {
                        statusEl.classList.add('alert-danger');
                        statusEl.textContent = data.message || 'Failed to update notification preferences.';
                    }
                })
                .catch(() => {
                    if (!statusEl) {
                        return;
                    }
                    statusEl.classList.remove('d-none');
                    statusEl.classList.add('alert-danger');
                    statusEl.textContent = 'An unexpected error occurred while saving preferences.';
                })
                .finally(() => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = submitBtn.dataset.originalText || 'Save Changes';
                    }
                });
        });
    }

    /**
     * Leave reset form
     */
    const leaveResetForm = document.getElementById('leaveResetForm');
    const leaveResetModalEl = document.getElementById('leaveResetModal');
    if (leaveResetForm) {
        const statusEl = document.getElementById('leaveResetStatus');
        const actionUrl = leaveResetForm.dataset.action || '';
        const confirmInput = document.getElementById('leaveResetConfirmInput');
        const dryRunCheckbox = document.getElementById('leaveResetDryRun');

        leaveResetForm.addEventListener('submit', event => {
            event.preventDefault();
            if (!actionUrl) {
                return;
            }

            const confirmValue = confirmInput ? confirmInput.value.trim() : '';
            if (confirmValue !== 'RESET_LEAVE_DATA') {
                if (statusEl) {
                    statusEl.classList.remove('d-none', 'alert-success');
                    statusEl.classList.add('alert-danger');
                    statusEl.textContent = 'Please type RESET_LEAVE_DATA exactly to confirm.';
                }
                return;
            }

            const submitBtn = leaveResetForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.dataset.originalText = submitBtn.dataset.originalText || submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';
                submitBtn.disabled = true;
            }

            if (statusEl) {
                statusEl.classList.add('d-none');
            }

            const formData = new FormData();
            formData.append('confirmReset', confirmValue);
            if (dryRunCheckbox && dryRunCheckbox.checked) {
                formData.append('dryRun', '1');
            } else {
                formData.append('dryRun', '0');
            }

            fetch(actionUrl, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (!statusEl) {
                        return;
                    }
                    statusEl.classList.remove('d-none', 'alert-success', 'alert-danger');
                    if (data.success) {
                        statusEl.classList.add('alert-success');
                        const messageParts = [];
                        if (data.dryRun) {
                            messageParts.push('Dry run completed. No data was deleted.');
                        } else {
                            messageParts.push('Leave data reset completed.');
                        }
                        if (Array.isArray(data.messages)) {
                            messageParts.push(data.messages.join(' '));
                        }
                        if (Array.isArray(data.notificationMessages)) {
                            messageParts.push(data.notificationMessages.join(' '));
                        }
                        statusEl.textContent = messageParts.join(' ');

                        if (!data.dryRun) {
                            if (window.bootstrap && leaveResetModalEl) {
                                const modalInstance = bootstrap.Modal.getInstance(leaveResetModalEl);
                                if (modalInstance) {
                                    modalInstance.hide();
                                }
                            }
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        }
                    } else {
                        statusEl.classList.add('alert-danger');
                        statusEl.textContent = data.message || 'Failed to reset leave data.';
                    }
                })
                .catch(() => {
                    if (!statusEl) {
                        return;
                    }
                    statusEl.classList.remove('d-none');
                    statusEl.classList.add('alert-danger');
                    statusEl.textContent = 'An unexpected error occurred while resetting leave data.';
                })
                .finally(() => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = submitBtn.dataset.originalText || 'Execute Reset';
                    }
                });
        });
    }
});

