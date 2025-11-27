<?php
/**
 * Approval Workflows Management - Admin Page
 * Configure leave approval workflows
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
$pageTitle = 'Approval Workflows Management';
$title = $pageTitle . ' - Leave Management System';
?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-24 mb-0">
            <i class="ri-flow-chart me-2 text-info"></i>
            <?= $pageTitle ?>
        </h1>
        <p class="text-muted mb-0 mt-2">Configure approval workflows and define approval hierarchies</p>
    </div>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= $base ?>html/?s=admin&ss=leave&p=dashboard">Leave Admin</a></li>
                <li class="breadcrumb-item active" aria-current="page">Approval Workflows</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Include the existing approval workflow manager component -->
<?php include 'includes/scripts/leave/leave_configurations/approval_workflow_manager.php'; ?>

<!-- Back Button -->
<div class="row mt-4">
    <div class="col-12">
        <a href="<?= $base ?>html/?s=admin&ss=leave&p=dashboard" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i>
            Back to Dashboard
        </a>
    </div>
</div>

