<!-- Leave Type Detail View -->
<?php if ($leaveType): ?>
<div class="leave-type-detail-wrapper">
    <div class="row">
        <div class="col-lg-10 col-xl-9 mx-auto">
            <!-- Header Card -->
            <div class="card custom-card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="flex-fill">
                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar avatar-lg avatar-rounded bg-primary-transparent me-3">
                                    <i class="ri-calendar-2-line text-primary fs-24"></i>
                                </div>
                                <div>
                                    <h3 class="mb-1 fw-semibold"><?= htmlspecialchars($leaveType->leaveTypeName) ?></h3>
                                    <?php if (!empty($leaveType->leaveTypeCode)): ?>
                                    <span class="badge bg-primary-transparent text-primary fs-12 fw-semibold px-3 py-1">
                                        <i class="ri-code-line me-1"></i><?= htmlspecialchars($leaveType->leaveTypeCode) ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!empty($leaveType->leaveTypeDescription)): ?>
                            <p class="text-muted mb-0 mt-2"><?= htmlspecialchars($leaveType->leaveTypeDescription) ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <span class="badge bg-<?= $leaveType->Suspended === 'Y' ? 'warning' : 'success' ?>-transparent text-<?= $leaveType->Suspended === 'Y' ? 'warning' : 'success' ?> fs-12 fw-semibold px-3 py-2">
                                <i class="ri-<?= $leaveType->Suspended === 'Y' ? 'pause' : 'check' ?>-circle-line me-1"></i>
                                <?= $leaveType->Suspended === 'Y' ? 'Suspended' : 'Active' ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="card custom-card mb-4">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <a href="<?= $base ?>html/?s=admin&ss=leave&p=leave_types&action=list" class="btn btn-outline-secondary">
                            <i class="ri-arrow-left-line me-1"></i>
                            Back to List
                        </a>

                        <div class="btn-group">
                            <?php if ($action === 'view'): ?>
                            <a href="<?= $base ?>html/?s=admin&ss=leave&p=leave_types&action=edit&leaveTypeID=<?= $leaveType->leaveTypeID ?>" class="btn btn-primary">
                                <i class="ri-edit-line me-1"></i>
                                Edit Leave Type
                            </a>
                            <?php else: ?>
                            <a href="<?= $base ?>html/?s=admin&ss=leave&p=leave_types&action=view&leaveTypeID=<?= $leaveType->leaveTypeID ?>" class="btn btn-outline-primary">
                                <i class="ri-eye-line me-1"></i>
                                View Details
                            </a>
                            <?php endif; ?>

                            <a href="<?= $base ?>html/?s=admin&ss=leave&p=leave_policies&action=create&leaveTypeID=<?= $leaveType->leaveTypeID ?>" class="btn btn-info">
                                <i class="ri-settings-3-line me-1"></i>
                                Configure Policy
                            </a>

                            <button type="button"
                                    class="btn btn-outline-<?= $leaveType->Suspended === 'Y' ? 'success' : 'warning' ?>"
                                    onclick="toggleLeaveTypeStatus(<?= $leaveType->leaveTypeID ?>, '<?= $leaveType->Suspended ?>')">
                                <i class="ri-<?= $leaveType->Suspended === 'Y' ? 'play' : 'pause' ?>-line me-1"></i>
                                <?= $leaveType->Suspended === 'Y' ? 'Activate' : 'Suspend' ?>
                            </button>

                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="ri-more-line"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)" onclick="copyLeaveTypeToClipboard(<?= $leaveType->leaveTypeID ?>)">
                                            <i class="ri-copy-line me-2"></i>Copy Details
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)" onclick="printLeaveType(<?= $leaveType->leaveTypeID ?>)">
                                            <i class="ri-printer-line me-2"></i>Print
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($action === 'edit'): ?>
            <!-- Edit Form -->
            <div class="card custom-card mb-4">
                <div class="card-header bg-primary-transparent">
                    <h5 class="mb-0">
                        <i class="ri-edit-line me-2"></i>
                        Edit Leave Type
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                    $backendScript = '/php/scripts/leave/config/manage_leave_type.php';
                    if (isset($config['siteURL']) && !empty($config['siteURL'])) {
                        $backendScript = rtrim($config['siteURL'], '/') . '/php/scripts/leave/config/manage_leave_type.php';
                    }
                    ?>
                    <form id="leaveTypeForm" method="POST" action="<?= $backendScript ?>" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="leaveTypeID" value="<?= $leaveType->leaveTypeID ?>">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="leaveTypeName" class="form-label">
                                        Leave Type Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control"
                                           id="leaveTypeName"
                                           name="leaveTypeName"
                                           value="<?= htmlspecialchars($leaveType->leaveTypeName) ?>"
                                           required>
                                    <div class="invalid-feedback">Please provide a leave type name.</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="leaveTypeCode" class="form-label">
                                        Leave Type Code <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="text"
                                               class="form-control"
                                               id="leaveTypeCode"
                                               name="leaveTypeCode"
                                               value="<?= htmlspecialchars($leaveType->leaveTypeCode) ?>"
                                               pattern="[A-Z0-9_]+"
                                               maxlength="10"
                                               required>
                                        <button type="button"
                                                class="btn btn-outline-secondary"
                                                onclick="generateLeaveTypeCode()"
                                                title="Auto-generate code">
                                            <i class="ri-refresh-line"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback">Please provide a valid code (uppercase letters, numbers, underscores only).</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="leaveTypeDescription" class="form-label">Description</label>
                            <textarea class="form-control"
                                      id="leaveTypeDescription"
                                      name="leaveTypeDescription"
                                      rows="4"><?= htmlspecialchars($leaveType->leaveTypeDescription) ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="isActive" class="form-label">Status</label>
                                    <select class="form-select" id="isActive" name="isActive">
                                        <option value="N" <?= $leaveType->Suspended === 'N' ? 'selected' : '' ?>>Active</option>
                                        <option value="Y" <?= $leaveType->Suspended === 'Y' ? 'selected' : '' ?>>Suspended</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                            <a href="<?= $base ?>html/?s=admin&ss=leave&p=leave_types&action=view&leaveTypeID=<?= $leaveType->leaveTypeID ?>" class="btn btn-outline-secondary">
                                <i class="ri-close-line me-1"></i>
                                Cancel
                            </a>

                            <div>
                                <button type="button" class="btn btn-outline-primary me-2" onclick="resetForm()">
                                    <i class="ri-refresh-line me-1"></i>
                                    Reset
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line me-1"></i>
                                    Update Leave Type
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <!-- View Mode - Information Cards -->
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card custom-card h-100">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="ri-information-line me-2 text-primary"></i>
                                Leave Type Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="text-muted small mb-1 d-block">Name</label>
                                <div class="fw-semibold"><?= htmlspecialchars($leaveType->leaveTypeName) ?></div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small mb-1 d-block">Code</label>
                                <div class="fw-semibold">
                                    <span class="badge bg-primary-transparent text-primary"><?= htmlspecialchars($leaveType->leaveTypeCode) ?></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small mb-1 d-block">Status</label>
                                <div>
                                    <span class="badge bg-<?= $leaveType->Suspended === 'Y' ? 'warning' : 'success' ?>-transparent text-<?= $leaveType->Suspended === 'Y' ? 'warning' : 'success' ?>">
                                        <i class="ri-<?= $leaveType->Suspended === 'Y' ? 'pause' : 'check' ?>-circle-line me-1"></i>
                                        <?= $leaveType->Suspended === 'Y' ? 'Suspended' : 'Active' ?>
                                    </span>
                                </div>
                            </div>
                            <div>
                                <label class="text-muted small mb-1 d-block">Last Updated</label>
                                <div class="fw-semibold"><?= date('M d, Y H:i', strtotime($leaveType->LastUpdate)) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card custom-card h-100">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="ri-bar-chart-line me-2 text-info"></i>
                                Usage Statistics
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php
                            $applications = Leave::leave_applications(array('leaveTypeID' => $leaveType->leaveTypeID), false, $DBConn);
                            $totalApplications = $applications ? count($applications) : 0;
                            $activeApplications = $applications ? count(array_filter($applications, function($app) { return $app->leaveStatusID != 3; })) : 0;
                            $approvedApplications = $applications ? count(array_filter($applications, function($app) { return $app->leaveStatusID == 2; })) : 0;
                            ?>
                            <div class="mb-3">
                                <label class="text-muted small mb-1 d-block">Total Applications</label>
                                <div class="fw-semibold fs-18"><?= $totalApplications ?></div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small mb-1 d-block">Active Applications</label>
                                <div class="fw-semibold fs-18 text-warning"><?= $activeApplications ?></div>
                            </div>
                            <div>
                                <label class="text-muted small mb-1 d-block">Approved Applications</label>
                                <div class="fw-semibold fs-18 text-success"><?= $approvedApplications ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.leave-type-detail-wrapper {
    animation: fadeIn 0.3s ease-in;
}

.leave-type-detail-wrapper .card {
    border: 1px solid rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.leave-type-detail-wrapper .card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
</style>

<script>
function resetForm() {
    if (confirm('Are you sure you want to reset the form? All changes will be lost.')) {
        location.reload();
    }
}

function generateLeaveTypeCode() {
    const nameInput = document.getElementById('leaveTypeName');
    const codeInput = document.getElementById('leaveTypeCode');
    if (nameInput && codeInput && nameInput.value) {
        const name = nameInput.value.toUpperCase();
        let code = name.replace(/[^A-Z0-9]/g, '_').replace(/_+/g, '_').replace(/^_|_$/g, '').substring(0, 10);
        codeInput.value = code;
    }
}

function copyLeaveTypeToClipboard(leaveTypeID) {
    const text = `Leave Type ID: ${leaveTypeID}\nName: <?= htmlspecialchars($leaveType->leaveTypeName) ?>\nCode: <?= htmlspecialchars($leaveType->leaveTypeCode) ?>`;
    navigator.clipboard.writeText(text).then(() => {
        alert('Leave type details copied to clipboard!');
    }).catch(() => {
        alert('Failed to copy to clipboard');
    });
}

function printLeaveType(leaveTypeID) {
    window.print();
}

// Form validation
(function() {
    'use strict';
    const form = document.getElementById('leaveTypeForm');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    }
})();
</script>

<?php else: ?>
<!-- Leave Type Not Found -->
<div class="row">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-body text-center py-5">
                <div class="avatar avatar-xl bg-danger-transparent mx-auto mb-4">
                    <i class="ri-error-warning-line text-danger fs-48"></i>
                </div>
                <h4 class="mb-2">Leave Type Not Found</h4>
                <p class="text-muted mb-4">
                    The requested leave type could not be found or may have been deleted.
                </p>
                <a href="<?= $base ?>html/?s=admin&ss=leave&p=leave_types&action=list" class="btn btn-primary">
                    <i class="ri-arrow-left-line me-1"></i>
                    Back to List
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
