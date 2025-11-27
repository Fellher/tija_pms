<?php
// This is a new sub-menu reporting file for 'view_leave_application.php'
// Add your content here.
?>

<script>
(function() {
    const leaveApplicationID = <?php echo $leaveApplicationID; ?>;
    const baseUrl = '<?php echo $base; ?>';
    let refreshInterval = null;
    let isRefreshing = false;
    let currentLeaveStatusID = <?php echo $leave['leaveStatusID'] ?? 3; ?>;

    function loadApprovalStatus() {
        if (isRefreshing) return;
        isRefreshing = true;

        const container = document.getElementById('workflowStatusContainer');
        if (!container) return;

        fetch(baseUrl + 'php/scripts/leave/workflows/get_approval_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                leaveApplicationID: leaveApplicationID
            })
        })
        .then(response => response.json())
        .then(data => {
            isRefreshing = false;
            if (data.success) {
                // Update current status for auto-refresh logic
                if (data.leaveStatusID !== undefined) {
                    currentLeaveStatusID = data.leaveStatusID;
                }
                renderWorkflowStatus(data);
                // Update auto-refresh based on new status
                updateAutoRefresh();
            } else {
                container.innerHTML = '<div class="alert alert-warning mb-0">' +
                    (data.message || 'Unable to load approval status') + '</div>';
            }
        })
        .catch(error => {
            isRefreshing = false;
            console.error('Error loading approval status:', error);
            const container = document.getElementById('workflowStatusContainer');
            if (container) {
                container.innerHTML = '<div class="alert alert-danger mb-0">Error loading approval status. Please refresh the page.</div>';
            }
        });
    }

    function renderWorkflowStatus(data) {
        const container = document.getElementById('workflowStatusContainer');
        if (!container) return;

        if (!data.hasWorkflow || !data.workflow || !data.workflow.steps || data.workflow.steps.length === 0) {
            // Fallback to legacy approval history
            const approvalHistory = <?php echo json_encode($approvalHistory); ?>;
            if (approvalHistory && approvalHistory.length > 0) {
                let html = '<div class="timeline">';
                approvalHistory.forEach(approval => {
                    const approverName = escapeHtml(approval.approverName || 'Approver');
                    const actionStatus = renderStatusBadge(approval.leaveStatusID || 3);
                    const actionDate = approval.approvalDateAdded || approval.DateAdded || null;
                    const formattedDate = actionDate ? formatDateTime(actionDate) : '-';
                    const comments = approval.approversComments || '';

                    html += `
                        <div class="timeline-item mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1">${approverName}</h6>
                                <span>${actionStatus}</span>
                            </div>
                            <p class="text-muted small mb-1"><i class="ri-time-line me-1"></i>${escapeHtml(formattedDate)}</p>
                            ${comments ? '<p class="mb-0">' + escapeHtmlMultiline(comments) + '</p>' : ''}
                        </div>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<p class="text-muted mb-0">No approval actions recorded yet.</p>';
            }
            return;
        }

        const workflow = data.workflow;
        const steps = workflow.steps || [];
        const comments = data.comments || [];

        // Calculate progress
        const totalSteps = steps.length;
        const completedSteps = steps.filter(s => s.stepStatus === 'approved').length;
        const progressPercent = totalSteps > 0 ? (completedSteps / totalSteps) * 100 : 0;

        let html = '';

        // Progress bar
        html += `
            <div class="workflow-progress mb-3">
                <div class="workflow-progress-bar" style="width: ${progressPercent}%"></div>
            </div>
        `;

        // Overall status
        if (workflow.hasRejection) {
            html += '<div class="alert alert-danger mb-3"><i class="ri-close-circle-line me-2"></i>Application has been rejected.</div>';
        } else if (workflow.allRequiredApproved && workflow.isFinalStepComplete) {
            html += '<div class="alert alert-success mb-3"><i class="ri-checkbox-circle-line me-2"></i>All approvals completed.</div>';
        } else {
            html += '<div class="alert alert-info mb-3"><i class="ri-time-line me-2"></i>Waiting for approvals...</div>';
        }

        // Workflow steps
        if (steps.length === 0) {
            html += '<p class="text-muted mb-0">No workflow steps configured.</p>';
        } else {
            steps.forEach((step, index) => {
                const stepStatus = step.stepStatus || 'pending';
                const stepClass = stepStatus === 'approved' ? 'completed' :
                                 stepStatus === 'rejected' ? 'rejected' :
                                 stepStatus === 'partial' ? 'partial' : 'pending';

                html += `
                    <div class="workflow-step ${stepClass}">
                        <div class="workflow-step-header">
                            <h6 class="workflow-step-title">
                                ${escapeHtml(step.stepName || 'Step ' + step.stepOrder)}
                            </h6>
                            <span class="workflow-step-status badge ${getStatusBadgeClass(stepStatus)}">
                                ${getStatusLabel(stepStatus)}
                            </span>
                        </div>
                        ${step.stepDescription ? '<p class="text-muted small mb-2">' + escapeHtml(step.stepDescription) + '</p>' : ''}
                        <div class="approvers-list">
                `;

                if (step.approvers && step.approvers.length > 0) {
                    step.approvers.forEach(approver => {
                        const hasActed = approver.hasActed || false;
                        const action = approver.action || null;
                        const approverComments = approver.comments || '';
                        const decisionDate = approver.decisionDate || null;

                        // Find matching comment from comments array
                        const matchingComment = comments.find(c =>
                            c.approverUserID == approver.approverUserID ||
                            c.approverID == approver.approverUserID
                        );

                        const displayComment = approverComments || (matchingComment ? matchingComment.comment : '');
                        const displayDate = decisionDate || (matchingComment ? matchingComment.commentDate : null);

                        html += `
                            <div class="approver-item">
                                <div class="approver-info">
                                    <div class="approver-name">
                                        ${escapeHtml(approver.approverName || 'Unknown Approver')}
                                        ${approver.isBackup ? '<span class="badge bg-secondary ms-2" style="font-size: 0.65rem;">Backup</span>' : ''}
                                    </div>
                                    ${approver.approverEmail ? '<div class="approver-email">' + escapeHtml(approver.approverEmail) + '</div>' : ''}
                                </div>
                                <div class="approver-action">
                                    ${hasActed ?
                                        `<span class="badge approver-action-badge ${action === 'approved' ? 'bg-success' : 'bg-danger'}">
                                            ${action === 'approved' ? '<i class="ri-check-line"></i> Approved' : '<i class="ri-close-line"></i> Rejected'}
                                        </span>` :
                                        `<span class="badge approver-action-badge bg-warning text-dark">
                                            <i class="ri-time-line"></i> Pending
                                        </span>`
                                    }
                                </div>
                            </div>
                        `;

                        if (hasActed && displayComment) {
                            html += `
                                <div class="approver-comment">
                                    <strong>Comment:</strong> ${escapeHtmlMultiline(displayComment)}
                                    ${displayDate ? '<div class="approver-comment-date"><i class="ri-time-line me-1"></i>' + formatDateTime(displayDate) + '</div>' : ''}
                                </div>
                            `;
                        }
                    });
                } else {
                    html += '<p class="text-muted small mb-0">No approvers assigned to this step.</p>';
                }

                html += `
                        </div>
                    </div>
                `;
            });
        }

        container.innerHTML = html;
    }

    function getStatusBadgeClass(status) {
        switch(status) {
            case 'approved': return 'bg-success';
            case 'rejected': return 'bg-danger';
            case 'partial': return 'bg-info';
            case 'pending': return 'bg-warning text-dark';
            default: return 'bg-secondary';
        }
    }

    function getStatusLabel(status) {
        switch(status) {
            case 'approved': return 'Approved';
            case 'rejected': return 'Rejected';
            case 'partial': return 'Partially Approved';
            case 'pending': return 'Pending';
            default: return 'Unknown';
        }
    }

    function renderStatusBadge(statusID) {
        switch(parseInt(statusID)) {
            case 6: return '<span class="badge bg-success">Approved</span>';
            case 4: return '<span class="badge bg-danger">Rejected</span>';
            case 3:
            case 2: return '<span class="badge bg-warning text-dark">Pending</span>';
            default: return '<span class="badge bg-secondary">Draft</span>';
        }
    }

    function formatDateTime(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit'
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function escapeHtmlMultiline(text) {
        if (!text) return '';
        return escapeHtml(text).replace(/\n/g, '<br>');
    }

    // Refresh button handler
    const refreshBtn = document.getElementById('refreshWorkflowBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            this.classList.add('refreshing');
            loadApprovalStatus();
            setTimeout(() => {
                this.classList.remove('refreshing');
            }, 1000);
        });
    }

    // Initial load
    loadApprovalStatus();

    // Auto-refresh every 30 seconds if status is pending
    function updateAutoRefresh() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
            refreshInterval = null;
        }

        // Only auto-refresh if status is pending (3)
        if (currentLeaveStatusID === 3) {
            refreshInterval = setInterval(() => {
                loadApprovalStatus();
            }, 30000); // 30 seconds
        }
    }

    // Start auto-refresh
    updateAutoRefresh();

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    });
})();
</script>

