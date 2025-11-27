<?php
/**
 * HR Leave Policy Management Dashboard
 * Central hub for HR administrators to manage leave policies and configurations
 */

if(!$isValidUser) {
    Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

// Check HR admin permissions
if ($userDetails->userRole !== 'admin' && $userDetails->userRole !== 'hr_admin') {
    Alert::error("Access denied. HR Admin privileges required.", true);
    return;
}

$entityID = $_SESSION['entityID'] ?? 1;
$currentUserID = $_SESSION['userID'];

// Get current page parameters
$action = $_GET['action'] ?? 'dashboard';
$leaveTypeID = $_GET['leaveTypeID'] ?? null;
$policyID = $_GET['policyID'] ?? null;

// Initialize data arrays
$leaveTypes = array();
$leaveType = null;
$policies = array();
$policy = null;
$errors = array();
$success = '';

try {
    // Get leave types for dashboard
    $leaveTypes = Leave::leave_types(array('Lapsed' => 'N'), false, $DBConn);

    // Get accumulation policies for dashboard
    $policies = AccumulationPolicy::get_policies($entityID, false, $DBConn);

    // Handle different actions
    switch ($action) {
        case 'dashboard':
            // Dashboard data already loaded above
            break;

        case 'leave_types':
            $leaveTypes = Leave::leave_types(array('Lapsed' => 'N'), false, $DBConn);
            break;

        case 'view_leave_type':
        case 'edit_leave_type':
            if ($leaveTypeID) {
                $leaveType = Leave::leave_types(array('leaveTypeID' => $leaveTypeID), true, $DBConn);
            }
            break;

        case 'delete_leave_type':
            if ($leaveTypeID) {
                // Check if leave type is in use
                $applications = Leave::leave_applications(array('leaveTypeID' => $leaveTypeID), false, $DBConn);
                if ($applications && count($applications) > 0) {
                    $errors[] = 'Cannot delete leave type. It is currently being used in leave applications.';
                } else {
                    $updateData = array(
                        'Lapsed' => 'Y',
                        'LastUpdate' => $config['currentDateTimeFormated'],
                        'LastUpdateByID' => $currentUserID
                    );
                    if ($DBConn->update_table('tija_leave_types', $updateData, array('leaveTypeID' => $leaveTypeID))) {
                        $success = 'Leave type deleted successfully';
                    } else {
                        $errors[] = 'Failed to delete leave type';
                    }
                }
                $leaveTypes = Leave::leave_types(array('Lapsed' => 'N'), false, $DBConn);
            }
            break;

        case 'toggle_leave_type_status':
            if ($leaveTypeID) {
                $leaveType = Leave::leave_types(array('leaveTypeID' => $leaveTypeID), true, $DBConn);
                if ($leaveType) {
                    $newStatus = $leaveType->Suspended === 'Y' ? 'N' : 'Y';
                    $updateData = array(
                        'Suspended' => $newStatus,
                        'LastUpdate' => $config['currentDateTimeFormated'],
                        'LastUpdateByID' => $currentUserID
                    );
                    if ($DBConn->update_table('tija_leave_types', $updateData, array('leaveTypeID' => $leaveTypeID))) {
                        $success = 'Leave type status updated successfully';
                    } else {
                        $errors[] = 'Failed to update leave type status';
                    }
                }
                $leaveTypes = Leave::leave_types(array('Lapsed' => 'N'), false, $DBConn);
            }
            break;

        case 'accumulation_policies':
            $policies = AccumulationPolicy::get_policies($entityID, false, $DBConn);
            break;

        case 'view_policy':
        case 'edit_policy':
            if ($policyID) {
                $policy = AccumulationPolicy::get_policy($policyID, $DBConn);
            }
            break;

        case 'delete_policy':
            if ($policyID) {
                if (AccumulationPolicy::delete_policy($policyID, $currentUserID, $DBConn)) {
                    $success = 'Policy deleted successfully';
                } else {
                    $errors[] = 'Failed to delete policy';
                }
                $policies = AccumulationPolicy::get_policies($entityID, false, $DBConn);
            }
            break;
    }

} catch (Exception $e) {
    $errors[] = $e->getMessage();
}

// Get page title
$pageTitle = 'HR Leave Policy Management';
switch ($action) {
    case 'dashboard':
        $pageTitle = 'HR Leave Dashboard';
        break;
    case 'leave_types':
        $pageTitle = 'Leave Types Management';
        break;
    case 'view_leave_type':
        $pageTitle = 'View Leave Type';
        break;
    case 'edit_leave_type':
        $pageTitle = 'Edit Leave Type';
        break;
    case 'create_leave_type':
        $pageTitle = 'Create Leave Type';
        break;
    case 'accumulation_policies':
        $pageTitle = 'Accumulation Policies';
        break;
    case 'view_policy':
        $pageTitle = 'View Policy';
        break;
    case 'edit_policy':
        $pageTitle = 'Edit Policy';
        break;
    case 'create_policy':
        $pageTitle = 'Create Policy';
        break;
    case 'statistics':
        $pageTitle = 'Leave Statistics';
        break;
}

// Set page variables for header
$title = $pageTitle . ' - HR Leave Management';
$keywords = array('leave management', 'hr admin', 'leave policies', 'accumulation policies');
?>

<!-- Include page-specific CSS -->
<?php include $base . '/assets/css/src/pages/user/leave/hr_admin/dashboard.php'; ?>

<div class="container-fluid">
    <div class="row">
        <!-- HR Admin Sidebar -->
        <div class="col-md-3 col-lg-2 p-3">
            <div class="sidebar-nav">
                <h5 class="mb-3">
                    <i class="ri-admin-line me-2"></i>
                    HR Admin
                </h5>

                <nav class="nav flex-column">
                    <a href="?action=dashboard" class="nav-link <?= $action === 'dashboard' ? 'active' : '' ?>">
                        <i class="ri-dashboard-line me-2"></i>
                        Dashboard
                    </a>

                    <div class="nav-section">
                        <h6 class="nav-section-title">Leave Types</h6>
                        <a href="?action=leave_types" class="nav-link <?= $action === 'leave_types' ? 'active' : '' ?>">
                            <i class="ri-list-check me-2"></i>
                            All Leave Types
                        </a>
                        <a href="?action=create_leave_type" class="nav-link <?= $action === 'create_leave_type' ? 'active' : '' ?>">
                            <i class="ri-add-circle-line me-2"></i>
                            Create Leave Type
                        </a>
                    </div>

                    <div class="nav-section">
                        <h6 class="nav-section-title">Policies</h6>
                        <a href="?action=accumulation_policies" class="nav-link <?= $action === 'accumulation_policies' ? 'active' : '' ?>">
                            <i class="ri-settings-3-line me-2"></i>
                            Accumulation Policies
                        </a>
                        <a href="?action=create_policy" class="nav-link <?= $action === 'create_policy' ? 'active' : '' ?>">
                            <i class="ri-add-circle-line me-2"></i>
                            Create Policy
                        </a>
                    </div>

                    <div class="nav-section">
                        <h6 class="nav-section-title">Reports</h6>
                        <a href="?action=statistics" class="nav-link <?= $action === 'statistics' ? 'active' : '' ?>">
                            <i class="ri-bar-chart-line me-2"></i>
                            Statistics
                        </a>
                    </div>

                    <hr class="my-3">

                    <a href="<?= $base ?>/html/pages/admin/dashboard.php" class="nav-link">
                        <i class="ri-arrow-left-line me-2"></i>
                        Main Admin
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 p-3">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="ri-admin-line me-2 text-primary"></i>
                        <?= $pageTitle ?>
                    </h2>
                    <p class="text-muted mb-0">HR administration for leave policies and configurations</p>
                </div>

                <!-- Action buttons based on current view -->
                <?php if ($action === 'dashboard'): ?>
                <div class="d-flex gap-2">
                    <a href="?action=create_leave_type" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i>
                        Create Leave Type
                    </a>
                    <a href="?action=create_policy" class="btn btn-outline-primary">
                        <i class="ri-settings-3-line me-1"></i>
                        Create Policy
                    </a>
                </div>
                <?php elseif ($action === 'leave_types'): ?>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary" data-action="export-leave-types">
                        <i class="ri-download-line me-1"></i>
                        Export
                    </button>
                    <a href="?action=create_leave_type" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i>
                        Create Leave Type
                    </a>
                </div>
                <?php elseif ($action === 'accumulation_policies'): ?>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary" data-action="export-policies">
                        <i class="ri-download-line me-1"></i>
                        Export
                    </button>
                    <a href="?action=create_policy" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i>
                        Create Policy
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Alerts Container -->
            <div id="alertContainer"></div>

            <!-- Alerts -->
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line me-2"></i>
                <strong>Error!</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-check-line me-2"></i>
                <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Content based on action -->
            <?php
            switch ($action) {
                case 'dashboard':
                    include 'views/hr_dashboard.php';
                    break;
                case 'leave_types':
                    include 'views/leave_types_list.php';
                    break;
                case 'view_leave_type':
                case 'edit_leave_type':
                    include 'views/leave_type_detail.php';
                    break;
                case 'create_leave_type':
                    include 'views/leave_type_form.php';
                    break;
                case 'accumulation_policies':
                    include 'views/policies_list.php';
                    break;
                case 'view_policy':
                case 'edit_policy':
                    include 'views/policy_detail.php';
                    break;
                case 'create_policy':
                    include 'views/policy_form.php';
                    break;
                case 'statistics':
                    include 'views/statistics.php';
                    break;
                default:
                    include 'views/hr_dashboard.php';
                    break;
            }
            ?>
        </div>
    </div>
</div>

<!-- Include page-specific JavaScript -->
<?php include $base . '/assets/php/src/pages/user/leave/hr_admin/dashboard.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-action="export-leave-types"]').forEach(button => {
        button.addEventListener('click', () => {
            if (typeof exportLeaveTypes === 'function') {
                exportLeaveTypes();
            }
        });
    });

    document.querySelectorAll('[data-action="export-policies"]').forEach(button => {
        button.addEventListener('click', () => {
            if (typeof exportPolicies === 'function') {
                exportPolicies();
            }
        });
    });
});
</script>
