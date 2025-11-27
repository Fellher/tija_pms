<?php
/**
 * Leave Periods Management - Admin Page
 * Manage fiscal leave periods and years
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
$pageTitle = 'Leave Periods Management';
$title = $pageTitle . ' - Leave Management System';

// Build entity context
$allEntities = Data::entities_full([], false, $DBConn);
$entityLookup = [];
if ($allEntities) {
    foreach ($allEntities as $entity) {
        $entityLookup[$entity->entityID] = $entity->entityName;
    }
}

$selectedEntityID = isset($_GET['entity_filter']) && $_GET['entity_filter'] !== ''
    ? Utility::clean_string($_GET['entity_filter'])
    : $entityID;
$leavePeriodsFilter = ['Suspended' => 'N'];
if ($selectedEntityID) {
    $leavePeriodsFilter['entityID'] = $selectedEntityID;
}
$currentEntityID = $selectedEntityID;
?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-24 mb-0">
            <i class="ri-calendar-event-line me-2 text-primary"></i>
            <?= $pageTitle ?>
        </h1>
        <p class="text-muted mb-0 mt-2">Configure fiscal leave periods and manage leave years</p>
    </div>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= $base ?>html/?s=admin&ss=leave&p=dashboard">Leave Admin</a></li>
                <li class="breadcrumb-item active" aria-current="page">Leave Periods</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Include the existing leave periods component -->
<?php include 'includes/scripts/leave/leave_configurations/leave_periods.php'; ?>

<!-- Back Button -->
<div class="row mt-4">
    <div class="col-12">
        <a href="<?= $base ?>html/?s=admin&ss=leave&p=dashboard" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i>
            Back to Dashboard
        </a>
    </div>
</div>

