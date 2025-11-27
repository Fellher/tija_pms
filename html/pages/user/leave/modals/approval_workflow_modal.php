<?php
/**
 * Approval Workflow Modal
 *
 * Multi-level approval interface for leave applications with
 * approval history, comments, and workflow management
 */

// Get pending approvals for current user (will be loaded via AJAX)
$pendingApprovals = array(); // Will be populated by JavaScript
$approvalHistory = array();
?>

<!-- Approval Workflow Modal -->
<div class="modal fade" id="approvalWorkflowModal" tabindex="-1" aria-labelledby="approvalWorkflowModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="approvalWorkflowModalLabel">
                    <i class="ri-user-settings-line me-2"></i>
                    Leave Approval Workflow
                </h5>
                <div class="d-flex align-items-center gap-3">
                    <!-- Filter Tabs -->
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="approvalFilter" id="pendingApprovals" autocomplete="off" checked>
                        <label class="btn btn-outline-dark btn-sm" for="pendingApprovals">
                            Pending (<span id="pendingCount">0</span>)
                        </label>

                        <input type="radio" class="btn-check" name="approvalFilter" id="approvedApplications" autocomplete="off">
                        <label class="btn btn-outline-dark btn-sm" for="approvedApplications">
                            Approved
                        </label>

                        <input type="radio" class="btn-check" name="approvalFilter" id="rejectedApplications" autocomplete="off">
                        <label class="btn btn-outline-dark btn-sm" for="rejectedApplications">
                            Rejected
                        </label>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-0">
                <!-- Approval List -->
                <div class="approval-list" id="approvalList">
                    <!-- Approvals will be populated by JavaScript -->
                </div>

                <!-- Empty State -->
                <div class="empty-state text-center py-5" id="emptyState" style="display: none;">
                    <i class="ri-inbox-line text-muted" style="font-size: 4rem;"></i>
                    <h5 class="text-muted mt-3">No approvals found</h5>
                    <p class="text-muted">There are no leave applications requiring your approval at this time.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approval Details Modal -->
<div class="modal fade" id="approvalDetailsModal" tabindex="-1" aria-labelledby="approvalDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approvalDetailsModalLabel">
                    <i class="ri-file-text-line me-2"></i>
                    Leave Application Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Application Details -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Employee Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar me-3">
                                        <img src="<?= $base ?>assets/img/avatar-placeholder.png" alt="Avatar" class="rounded-circle" width="50" height="50">
                                    </div>
                                    <div>
                                        <h6 class="mb-0" id="employeeName">-</h6>
                                        <small class="text-muted" id="employeePosition">-</small>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <strong>Department:</strong>
                                    <span id="employeeDepartment">-</span>
                                </div>
                                <div class="mb-2">
                                    <strong>Manager:</strong>
                                    <span id="employeeManager">-</span>
                                </div>
                                <div class="mb-2">
                                    <strong>Employment Date:</strong>
                                    <span id="employmentDate">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Leave Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <strong>Leave Type:</strong>
                                    <span id="leaveType">-</span>
                                </div>
                                <div class="mb-2">
                                    <strong>Start Date:</strong>
                                    <span id="startDate">-</span>
                                </div>
                                <div class="mb-2">
                                    <strong>End Date:</strong>
                                    <span id="endDate">-</span>
                                </div>
                                <div class="mb-2">
                                    <strong>Total Days:</strong>
                                    <span id="totalDays">-</span>
                                </div>
                                <div class="mb-2">
                                    <strong>Half Day:</strong>
                                    <span id="halfDay">-</span>
                                </div>
                                <div class="mb-2">
                                    <strong>Applied On:</strong>
                                    <span id="appliedDate">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leave Reason -->
                <div class="mt-4">
                    <h6>Reason for Leave</h6>
                    <div class="card">
                        <div class="card-body">
                            <p id="leaveReason" class="mb-0">-</p>
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact & Handover -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h6>Emergency Contact</h6>
                        <div class="card">
                            <div class="card-body">
                                <p id="emergencyContact" class="mb-0">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Handover Notes</h6>
                        <div class="card">
                            <div class="card-body">
                                <p id="handoverNotes" class="mb-0">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Supporting Documents -->
                <div class="mt-4" id="supportingDocumentsSection" style="display: none;">
                    <h6>Supporting Documents</h6>
                    <div class="card">
                        <div class="card-body">
                            <div id="supportingDocuments" class="document-list">
                                <!-- Documents will be populated here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Approval Workflow -->
                <div class="mt-4">
                    <h6>Approval Workflow</h6>
                    <div class="approval-workflow">
                        <div class="workflow-step" id="supervisorStep">
                            <div class="step-icon">
                                <i class="ri-user-line"></i>
                            </div>
                            <div class="step-content">
                                <div class="step-title">Direct Supervisor</div>
                                <div class="step-name" id="supervisorName">-</div>
                                <div class="step-status" id="supervisorStatus">Pending</div>
                                <div class="step-date" id="supervisorDate">-</div>
                            </div>
                        </div>

                        <div class="workflow-arrow">
                            <i class="ri-arrow-right-line"></i>
                        </div>

                        <div class="workflow-step" id="departmentHeadStep">
                            <div class="step-icon">
                                <i class="ri-building-line"></i>
                            </div>
                            <div class="step-content">
                                <div class="step-title">Department Head</div>
                                <div class="step-name" id="departmentHeadName">-</div>
                                <div class="step-status" id="departmentHeadStatus">Pending</div>
                                <div class="step-date" id="departmentHeadDate">-</div>
                            </div>
                        </div>

                        <div class="workflow-arrow">
                            <i class="ri-arrow-right-line"></i>
                        </div>

                        <div class="workflow-step" id="hrStep">
                            <div class="step-icon">
                                <i class="ri-user-settings-line"></i>
                            </div>
                            <div class="step-content">
                                <div class="step-title">HR Manager</div>
                                <div class="step-name" id="hrManagerName">-</div>
                                <div class="step-status" id="hrStatus">Pending</div>
                                <div class="step-date" id="hrDate">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Approvers -->
                <div class="mt-3" id="pendingApproversSection" style="display: none;">
                    <h6>Pending Approvers</h6>
                    <div class="card">
                        <div class="card-body">
                            <div id="pendingApproversList" class="pending-approvers-list text-muted">
                                <!-- Pending approvers populated via JS -->
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <small class="text-muted">Send a reminder to outstanding approvers.</small>
                                <button type="button" class="btn btn-outline-primary btn-sm" data-action="approval-remind">
                                    <i class="ri-notification-3-line me-1"></i>Remind Approvers
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Approval Comments -->
                <div class="mt-4" id="approvalCommentsSection">
                    <h6>Approval Comments</h6>
                    <div class="card">
                        <div class="card-body">
                            <div id="approvalComments" class="comments-list">
                                <!-- Comments will be populated here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Approval Actions -->
                <div class="mt-4" id="approvalActionsSection">
                    <h6>Your Decision</h6>
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="approvalComment" class="form-label">Comments (Optional)</label>
                                <textarea class="form-control" id="approvalComment" rows="3"
                                          placeholder="Add any comments about your decision..."></textarea>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success" data-action="approval-approve">
                                    <i class="ri-check-line me-1"></i>Approve
                                </button>
                                <button type="button" class="btn btn-danger" data-action="approval-reject">
                                    <i class="ri-close-line me-1"></i>Reject
                                </button>
                                <button type="button" class="btn btn-outline-secondary" data-action="approval-request-info">
                                    <i class="ri-question-line me-1"></i>Request More Info
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" data-action="approval-download">
                    <i class="ri-download-line me-1"></i>Download PDF
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .approval-list {
        max-height: 600px;
        overflow-y: auto;
    }

    .approval-item {
        border-bottom: 1px solid #dee2e6;
        padding: 1rem;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .approval-item:hover {
        background-color: #f8f9fa;
    }

    .approval-item:last-child {
        border-bottom: none;
    }

    .approval-header {
        display: flex;
        justify-content-between;
        align-items: flex-start;
        margin-bottom: 0.5rem;
    }

    .approval-employee {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .approval-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: #495057;
    }

    .approval-info h6 {
        margin: 0;
        font-size: 1rem;
    }

    .approval-info small {
        color: #6c757d;
    }

    .approval-meta {
        text-align: right;
    }

    .approval-type {
        font-size: 0.875rem;
        font-weight: 600;
        color: #495057;
    }

    .approval-dates {
        font-size: 0.75rem;
        color: #6c757d;
    }

    .approval-priority {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-weight: 600;
    }

    .priority-high {
        background: #f8d7da;
        color: #721c24;
    }

    .priority-medium {
        background: #fff3cd;
        color: #856404;
    }

    .priority-low {
        background: #d1ecf1;
        color: #0c5460;
    }

    .approval-workflow {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 0.5rem;
    }

    .workflow-step {
        display: flex;
        align-items: center;
        flex: 1;
    }

    .step-icon {
        width: 40px;
        height: 40px;
        background: #6c757d;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 0.75rem;
    }

    .step-icon.approved {
        background: #28a745;
    }

    .step-icon.rejected {
        background: #dc3545;
    }

    .step-icon.current {
        background: #ffc107;
        color: #212529;
    }

    .step-content {
        flex: 1;
    }

    .step-title {
        font-weight: 600;
        font-size: 0.875rem;
    }

    .step-name {
        color: #6c757d;
        font-size: 0.75rem;
    }

    .step-status {
        font-size: 0.75rem;
        font-weight: 600;
    }

    .step-status.approved {
        color: #28a745;
    }

    .step-status.rejected {
        color: #dc3545;
    }

    .step-status.pending {
        color: #ffc107;
    }

    .step-date {
        color: #6c757d;
        font-size: 0.75rem;
    }

    .workflow-arrow {
        color: #6c757d;
        margin: 0 0.5rem;
    }

    .comments-list {
        max-height: 200px;
        overflow-y: auto;
    }

    .comment-item {
        padding: 0.75rem;
        border-bottom: 1px solid #dee2e6;
    }

    .comment-item:last-child {
        border-bottom: none;
    }

    .comment-header {
        display: flex;
        justify-content-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .comment-author {
        font-weight: 600;
        font-size: 0.875rem;
    }

    .comment-date {
        font-size: 0.75rem;
        color: #6c757d;
    }

    .comment-content {
        font-size: 0.875rem;
        color: #495057;
    }

    .document-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .document-item {
        padding: 0.5rem;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        font-size: 0.875rem;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .document-item:hover {
        background: #e9ecef;
    }

    .empty-state {
        padding: 3rem 1rem;
    }

    .avatar img {
        object-fit: cover;
    }

    .pending-approvers-list {
        font-size: 0.875rem;
        color: #495057;
    }

    .pending-approvers-list strong {
        color: #343a40;
    }
</style>

<script>
// Approval Workflow Modal JavaScript
let currentApprovals = [];
let currentFilter = 'pending';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-action="approval-approve"]').forEach(button => {
        button.addEventListener('click', () => {
            if (typeof approveLeave === 'function') {
                approveLeave();
            }
        });
    });

    document.querySelectorAll('[data-action="approval-reject"]').forEach(button => {
        button.addEventListener('click', () => {
            if (typeof rejectLeave === 'function') {
                rejectLeave();
            }
        });
    });

    document.querySelectorAll('[data-action="approval-request-info"]').forEach(button => {
        button.addEventListener('click', () => {
            if (typeof requestMoreInfo === 'function') {
                requestMoreInfo();
            }
        });
    });

    document.querySelectorAll('[data-action="approval-download"]').forEach(button => {
        button.addEventListener('click', () => {
            if (typeof downloadApplication === 'function') {
                downloadApplication();
            }
        });
    });

    document.querySelectorAll('[data-action="approval-remind"]').forEach(button => {
        button.addEventListener('click', () => {
            remindPendingApprovers();
        });
    });
});

function initializeApprovalWorkflow() {
    // Set up event listeners
    document.getElementById('pendingApprovals').addEventListener('change', () => switchFilter('pending'));
    document.getElementById('approvedApplications').addEventListener('change', () => switchFilter('approved'));
    document.getElementById('rejectedApplications').addEventListener('change', () => switchFilter('rejected'));

    // Load initial data
    loadApprovals();
}

function switchFilter(filter) {
    currentFilter = filter;
    loadApprovals();
}

function loadApprovals() {
    const loadingHtml = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading approvals...</p>
        </div>
    `;

    document.getElementById('approvalList').innerHTML = loadingHtml;
    document.getElementById('emptyState').style.display = 'none';

    fetch('<?= $base ?>php/scripts/leave/workflows/get_approvals.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            filter: currentFilter,
            userId: <?= $userDetails->ID ?>
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentApprovals = data.approvals;
            renderApprovals();
            updatePendingCount();
        } else {
            showEmptyState();
        }
    })
    .catch(error => {
        console.error('Error loading approvals:', error);
        showEmptyState();
    });
}

function renderApprovals() {
    const approvalList = document.getElementById('approvalList');

    if (currentApprovals.length === 0) {
        showEmptyState();
        return;
    }

    approvalList.innerHTML = '';

    currentApprovals.forEach(approval => {
        const approvalItem = document.createElement('div');
        approvalItem.className = 'approval-item';
        approvalItem.addEventListener('click', () => showApprovalDetails(approval.leaveApplicationID));

        const priorityClass = getPriorityClass(approval.priority);
        const statusClass = getStatusClass(approval.leaveStatusName);

        approvalItem.innerHTML = `
            <div class="approval-header">
                <div class="approval-employee">
                    <div class="approval-avatar">
                        ${approval.employeeName.charAt(0)}
                    </div>
                    <div class="approval-info">
                        <h6>${approval.employeeName}</h6>
                        <small>${approval.jobTitle} - ${approval.departmentName}</small>
                    </div>
                </div>
                <div class="approval-meta">
                    <div class="approval-type">${approval.leaveTypeName}</div>
                    <div class="approval-dates">${approval.startDate} to ${approval.endDate} (${approval.noOfDays} days)</div>
                    <div class="approval-priority ${priorityClass}">${approval.priority}</div>
                </div>
            </div>
            <div class="approval-summary">
                <p class="mb-1 text-muted">${approval.leaveReason}</p>
                <small class="text-muted">Applied: ${approval.dateApplied}</small>
            </div>
        `;

        approvalList.appendChild(approvalItem);
    });
}

function showEmptyState() {
    document.getElementById('approvalList').innerHTML = '';
    document.getElementById('emptyState').style.display = 'block';
}

function updatePendingCount() {
    const pendingCount = currentApprovals.filter(a => a.leaveStatusName === 'Pending').length;
    document.getElementById('pendingCount').textContent = pendingCount;
}

function getPriorityClass(priority) {
    switch (priority.toLowerCase()) {
        case 'high': return 'priority-high';
        case 'medium': return 'priority-medium';
        case 'low': return 'priority-low';
        default: return 'priority-medium';
    }
}

function getStatusClass(status) {
    switch (status.toLowerCase()) {
        case 'approved': return 'approved';
        case 'rejected': return 'rejected';
        case 'pending': return 'pending';
        default: return 'pending';
    }
}

function showApprovalDetails(leaveId) {
    window.currentLeaveId = leaveId;

    fetch('<?= $base ?>php/scripts/leave/workflows/get_approval_details.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ leaveId: leaveId })
    })
    .then(response => response.json())
    .then(data => {
        if (window.console && console.debug) {
            console.debug('[Approval Details]', data);
        }

        if (data.success) {
            populateApprovalDetails(data.leave);
            populateWorkflow(data.workflow || {});
            populateComments(data.comments);
            populateDocuments(data.documents);
            renderPendingApprovers(data.pendingApprovers || [], { canRemind: !!data.isHRManager });

            // Show/hide approval actions based on user permissions
            const actionsSection = document.getElementById('approvalActionsSection');
            const canApprove = !!(data.canApprove || (data.permissions && data.permissions.canApprove));
            actionsSection.style.display = canApprove ? 'block' : 'none';
            document.getElementById('approvalComment').value = '';

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('approvalDetailsModal'));
            modal.show();

            const workflowModal = document.getElementById('approvalWorkflowModal');
            if (workflowModal && workflowModal.dataset.pendingApprovalAction) {
                handlePendingApprovalAction(workflowModal.dataset.pendingApprovalAction);
                delete workflowModal.dataset.pendingApprovalAction;
            }
        } else {
            if (window.console && console.warn) {
                console.warn('[Approval Details] Failed', data.message);
            }
            showToast('warning', 'Error', data.message || 'Unable to load approval details.');
        }
    })
    .catch(error => {
        if (window.console && console.error) {
            console.error('Error loading approval details:', error);
        }
        showToast('error', 'Error', 'Failed to load approval details');
    });
}

function populateApprovalDetails(leave) {
    document.getElementById('employeeName').textContent = leave.employeeName;
    document.getElementById('employeePosition').textContent = leave.jobTitle;
    document.getElementById('employeeDepartment').textContent = leave.departmentName;
    document.getElementById('employeeManager').textContent = leave.supervisorName || 'Not assigned';
    document.getElementById('employmentDate').textContent = leave.employmentStartDate;

    document.getElementById('leaveType').textContent = leave.leaveTypeName;
    document.getElementById('startDate').textContent = leave.startDate;
    document.getElementById('endDate').textContent = leave.endDate;
    document.getElementById('totalDays').textContent = leave.noOfDays;
    document.getElementById('halfDay').textContent = leave.halfDayLeave ? leave.halfDayPeriod : 'No';
    document.getElementById('appliedDate').textContent = leave.dateApplied;

    document.getElementById('leaveReason').textContent = leave.leaveReason;
    document.getElementById('emergencyContact').textContent = leave.emergencyContact || 'Not provided';
    document.getElementById('handoverNotes').textContent = leave.handoverNotes || 'None';
}

function populateWorkflow(workflow) {
    const supervisor = workflow.supervisor || {};
    document.getElementById('supervisorName').textContent = supervisor.name || 'Not assigned';
    document.getElementById('supervisorStatus').textContent = supervisor.status || 'Pending';
    document.getElementById('supervisorDate').textContent = supervisor.date || '-';

    const departmentHead = workflow.departmentHead || {};
    document.getElementById('departmentHeadName').textContent = departmentHead.name || 'Not assigned';
    document.getElementById('departmentHeadStatus').textContent = departmentHead.status || 'Pending';
    document.getElementById('departmentHeadDate').textContent = departmentHead.date || '-';

    const hrManager = workflow.hrManager || {};
    document.getElementById('hrManagerName').textContent = hrManager.name || 'Not assigned';
    document.getElementById('hrStatus').textContent = hrManager.status || 'Pending';
    document.getElementById('hrDate').textContent = hrManager.date || '-';

    updateStepIcon('supervisorStep', supervisor.rawStatus || supervisor.status);
    updateStepIcon('departmentHeadStep', departmentHead.rawStatus || departmentHead.status);
    updateStepIcon('hrStep', hrManager.rawStatus || hrManager.status);
}

function updateStepIcon(stepId, status) {
    const step = document.getElementById(stepId);
    const icon = step.querySelector('.step-icon');

    icon.className = 'step-icon';
    const normalized = (status || '').toString().toLowerCase();
    if (normalized === 'approved' || normalized === 'approve') {
        icon.classList.add('approved');
    } else if (normalized === 'rejected' || normalized === 'reject') {
        icon.classList.add('rejected');
    } else if (normalized === 'info_requested' || normalized === 'request_info') {
        icon.classList.add('info');
    } else {
        icon.classList.add('current');
    }
}

function populateComments(comments) {
    const commentsContainer = document.getElementById('approvalComments');
    commentsContainer.innerHTML = '';

    if (comments.length === 0) {
        commentsContainer.innerHTML = '<p class="text-muted text-center">No comments yet</p>';
        return;
    }

    comments.forEach(comment => {
        const commentElement = document.createElement('div');
        commentElement.className = 'comment-item';
        commentElement.innerHTML = `
            <div class="comment-header">
                <span class="comment-author">${comment.approverName}</span>
                <span class="comment-date">${comment.commentDate}</span>
            </div>
            <div class="comment-content">${comment.comment}</div>
        `;
        commentsContainer.appendChild(commentElement);
    });
}

function populateDocuments(documents) {
    const documentsSection = document.getElementById('supportingDocumentsSection');
    const documentsContainer = document.getElementById('supportingDocuments');

    if (documents.length === 0) {
        documentsSection.style.display = 'none';
        return;
    }

    documentsSection.style.display = 'block';
    documentsContainer.innerHTML = '';

    documents.forEach(doc => {
        const docElement = document.createElement('div');
        docElement.className = 'document-item';
        docElement.innerHTML = `
            <i class="ri-file-line me-1"></i>
            ${doc.fileName}
        `;
        docElement.onclick = () => downloadDocument(doc.fileId);
        documentsContainer.appendChild(docElement);
    });
}

function approveLeave() {
    const comment = document.getElementById('approvalComment').value.trim();

    if (confirm('Are you sure you want to approve this leave application?')) {
        submitApprovalDecision('approve', comment);
    }
}

function rejectLeave() {
    const comment = document.getElementById('approvalComment').value.trim();

    if (!comment) {
        if (typeof showToast === 'function') {
            showToast('Please provide a reason for rejection.', 'warning');
        } else {
            alert('Please provide a reason for rejection.');
        }
        return;
    }

    if (confirm('Are you sure you want to reject this leave application?')) {
        submitApprovalDecision('reject', comment);
    }
}

function requestMoreInfo() {
    const comment = document.getElementById('approvalComment').value.trim();

    if (!comment) {
        if (typeof showToast === 'function') {
            showToast('Please specify what additional information is needed.', 'warning');
        } else {
            alert('Please specify what additional information is needed.');
        }
        return;
    }

    submitApprovalDecision('request_info', comment);
}

function submitApprovalDecision(decision, comment) {
    const leaveId = getCurrentLeaveId(); // You'll need to implement this

    fetch('<?= $base ?>php/scripts/leave/workflows/submit_approval_decision.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            leaveId: leaveId,
            decision: decision,
            comment: comment,
            approverId: <?= $userDetails->ID ?>
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Decision Submitted',
                     `Leave application has been ${decision === 'approve' ? 'approved' : decision === 'reject' ? 'rejected' : 'marked for more information'}.`);

            // Close modal and refresh list
            const modal = bootstrap.Modal.getInstance(document.getElementById('approvalDetailsModal'));
            modal.hide();

            loadApprovals();
        } else {
            showToast('error', 'Error', data.message || 'Failed to submit decision');
        }
    })
    .catch(error => {
        console.error('Error submitting decision:', error);
        showToast('error', 'Error', 'Failed to submit decision');
    });
}

function downloadApplication() {
    const leaveId = getCurrentLeaveId();
    window.open(`<?= $base ?>php/scripts/leave/applications/download_leave_application.php?id=${leaveId}`, '_blank');
}

function downloadDocument(fileId) {
    window.open(`<?= $base ?>php/scripts/leave/applications/download_document.php?id=${fileId}`, '_blank');
}

function getCurrentLeaveId() {
    // This should be set when showing approval details
    return window.currentLeaveId || null;
}

function renderPendingApprovers(pendingApprovers, options = {}) {
    const section = document.getElementById('pendingApproversSection');
    const listContainer = document.getElementById('pendingApproversList');
    const remindButton = document.querySelector('[data-action="approval-remind"]');

    if (!pendingApprovers || pendingApprovers.length === 0) {
        section.style.display = 'none';
        if (remindButton) {
            remindButton.setAttribute('disabled', 'disabled');
        }
        return;
    }

    const canRemind = !!options.canRemind;

    const items = pendingApprovers.map((approver) => {
        const role = approver.role || 'Approver';
        const name = approver.name || 'Pending approver';
        const userID = approver.userID || '';

        const remindAction = canRemind && userID
            ? `<button type="button" class="btn btn-outline-primary btn-sm ms-2" data-approver-id="${userID}" data-action="remind-specific">
                    <i class="ri-notification-3-line"></i>
               </button>`
            : '';

        return `<div class="d-flex align-items-center justify-content-between py-1">
                    <div>
                        <strong>${role}</strong>: ${name}
                    </div>
                    ${remindAction}
                </div>`;
    }).join('');

    listContainer.innerHTML = items;
    section.style.display = 'block';

    if (remindButton) {
        if (canRemind) {
            remindButton.removeAttribute('disabled');
        } else {
            remindButton.setAttribute('disabled', 'disabled');
        }
    }

    listContainer.querySelectorAll('[data-action="remind-specific"]').forEach(button => {
        button.addEventListener('click', () => {
            const approverId = button.dataset.approverId;
            remindPendingApprovers(approverId);
        });
    });
}

function remindPendingApprovers(approverId = null) {
    const leaveId = getCurrentLeaveId();
    if (!leaveId) {
        showToast('warning', 'No Request', 'Select a leave application first.');
        return;
    }

    const button = document.querySelector('[data-action="approval-remind"]');
    if (button) {
        button.setAttribute('disabled', 'disabled');
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Sending...';
    }

    const payload = { leaveId: leaveId };
    if (approverId) {
        payload.approverId = approverId;
    }

    fetch('<?= $base ?>php/scripts/leave/workflows/send_approval_reminder.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload)
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const msg = data.message || (approverId ? 'Approver has been reminded.' : 'Pending approvers have been reminded.');
                showToast('success', 'Reminder Sent', msg);
            } else {
                showToast('warning', 'Reminder Not Sent', data.message || 'Unable to send reminder.');
            }
        })
        .catch(error => {
            console.error('Reminder error:', error);
            showToast('error', 'Error', 'Failed to send reminder.');
        })
        .finally(() => {
            if (button) {
                button.innerHTML = button.dataset.originalText || '<i class="ri-notification-3-line me-1"></i>Remind Approvers';
                button.removeAttribute('disabled');
            }
        });
}

function handlePendingApprovalAction(action) {
    if (!action) {
        return;
    }

    const normalized = action.toString().toLowerCase();
    const actionMap = {
        approve: '[data-action="approval-approve"]',
        reject: '[data-action="approval-reject"]',
        'request_info': '[data-action="approval-request-info"]',
        'request-info': '[data-action="approval-request-info"]'
    };

    const selector = actionMap[normalized];
    if (!selector) {
        return;
    }

    const target = document.querySelector(`#approvalActionsSection ${selector}`);
    if (target) {
        target.focus();
    }
}

// Initialize when modal is shown
document.getElementById('approvalWorkflowModal').addEventListener('shown.bs.modal', function() {
    initializeApprovalWorkflow();
});
</script>
