<?php
/**
 * Leave Audit Log - Admin Page
 * View all leave-related activities and changes
 */

if(!$isValidUser) {
    Alert::info("You need to be logged in to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

// Check admin permissions
if (!$isAdmin && !$isValidAdmin && !$isHRManager) {
    Alert::error("Access denied. Leave Administrator privileges required.", true, array('text-center'));
    return;
}

$entityID = $_SESSION['entityID'] ?? 1;
$pageTitle = 'Leave Audit Log';
$title = $pageTitle . ' - Leave Management System';
?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-24 mb-0">
            <i class="ri-history-line me-2 text-secondary"></i>
            <?= $pageTitle ?>
        </h1>
        <p class="text-muted mb-0 mt-2">Track all leave-related activities and system changes</p>
    </div>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= $base ?>html/?s=admin&ss=leave&p=dashboard">Leave Admin</a></li>
                <li class="breadcrumb-item active" aria-current="page">Audit Log</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Audit Log Content -->
<div class="row">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="ri-file-list-3-line me-2"></i>
                    Activity Log
                </div>
                <div class="card-options">
                    <button class="btn btn-outline-primary btn-sm" onclick="exportAuditLog()">
                        <i class="ri-download-line me-1"></i>
                        Export Log
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="ri-information-line me-2"></i>
                    <strong>Audit Log Feature</strong>
                    <p class="mb-0 mt-2">
                        The audit log functionality tracks all leave-related activities including:
                    </p>
                    <ul class="mb-0 mt-2">
                        <li>Leave applications submitted</li>
                        <li>Approval decisions made</li>
                        <li>Policy changes</li>
                        <li>Entitlement modifications</li>
                        <li>Leave type updates</li>
                    </ul>
                    <p class="mb-0 mt-2 text-muted">
                        <em>This feature is currently under development. Full audit logging will be available soon.</em>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Back Button -->
<div class="row mt-4">
    <div class="col-12">
        <a href="<?= $base ?>html/?s=admin&ss=leave&p=dashboard" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i>
            Back to Dashboard
        </a>
    </div>
</div>

<script>
function exportAuditLog() {
    if (typeof showToast === 'function') {
        showToast('Audit log export will be implemented. This will export activity logs in Excel/PDF format.', 'info');
    } else {
        alert('Audit log export will be implemented. This will export activity logs in Excel/PDF format.');
    }
    // TODO: Implement actual export functionality
}
</script>

