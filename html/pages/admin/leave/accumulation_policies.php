<?php
/**
 * Leave Accumulation Policy Management Interface
 * Admin interface for managing leave accumulation policies and rules
 */

 if(!$isValidUser) {
    Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
  }

// Check admin permissions
if (!$isAdmin && !$isValidAdmin && !$isHRManager) {
    Alert::error("Access denied. Leave Administrator privileges required.", true, array('text-center'));
    return;
}



$entityID = $_SESSION['entityID'] ?? 1;
$currentUserID = $userDetails->ID;

// Get current page parameters
$action = $_GET['action'] ?? 'list';
$policyID = $_GET['policyID'] ?? null;
$ruleID = $_GET['ruleID'] ?? null;

// Initialize data arrays
$policies = array();
$policy = null;
$rules = array();
$leaveTypes = array();
$errors = array();
$success = '';

try {
    // Get leave types for dropdowns
    $leaveTypes = Leave::leave_types(array(), false, $DBConn);

    // Handle different actions
    switch ($action) {
        case 'list':
            // Get policies scoped to entity (respects HR manager entity scoping)
            // Exclude lapsed (deleted) policies from the list
            $policies = AccumulationPolicy::get_policies($entityID, false, $DBConn);
            // Filter out lapsed policies
            $policies = array_filter($policies, function($policy) {
                return ($policy->Lapsed ?? 'N') !== 'Y';
            });
            break;

        case 'view':
        case 'edit':
            if ($policyID) {
                $policy = AccumulationPolicy::get_policy($policyID, $DBConn);
                if ($policy) {
                    $rules = AccumulationPolicy::get_policy_rules($policyID, false, $DBConn);
                }
            }
            break;

        case 'delete':
            if ($policyID) {
                if (AccumulationPolicy::delete_policy($policyID, $currentUserID, $DBConn)) {
                    $success = 'Policy deleted successfully';
                } else {
                    $errors[] = 'Failed to delete policy';
                }
                $policies = AccumulationPolicy::get_policies($entityID, false, $DBConn);
            }
            break;

        case 'delete_rule':
            if ($ruleID) {
                if (AccumulationPolicy::delete_rule($ruleID, $currentUserID, $DBConn)) {
                    $success = 'Rule deleted successfully';
                } else {
                    $errors[] = 'Failed to delete rule';
                }
                if ($policyID) {
                    $policy = AccumulationPolicy::get_policy($policyID, $DBConn);
                    if ($policy) {
                        $rules = AccumulationPolicy::get_policy_rules($policyID, false, $DBConn);
                    }
                }
            }
            break;
    }

} catch (Exception $e) {
    $errors[] = $e->getMessage();
}

// Get page title based on action
$pageTitle = 'Leave Accumulation Policies';
if ($action === 'view' || $action === 'edit') {
    $pageTitle = ($action === 'edit' ? 'Edit' : 'View') . ' Accumulation Policy';
} elseif ($action === 'create') {
    $pageTitle = 'Create New Accumulation Policy';
}

// Set page variables for header
$title = $pageTitle . ' - Leave Management System';
$keywords = array('leave management', 'accumulation policies', 'admin', 'policies');
?>

<div class="container-fluid">
        <div class="row">
            <!-- Main Content -->
            <div class="col-12 p-3">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-1">
                            <i class="ri-calendar-check-line me-2 text-primary"></i>
                            <?= $pageTitle ?>
                        </h2>
                        <p class="text-muted mb-0">Manage leave accumulation policies and rules</p>
                    </div>

                    <div class="d-flex">
                        <a href="<?= $base ?>html/?s=admin&ss=leave&p=accumulation_policies&action=list" class="btn btn-outline-secondary me-2 <?= $action === 'list' ? 'active' : '' ?>">
                            <i class="ri-list-check me-1"></i>
                            Policy List
                        </a>
                        <a href="<?= $base ?>html/?s=admin&ss=leave&p=accumulation_policies&action=statistics" class="btn btn-outline-info me-2 <?= $action === 'statistics' ? 'active' : '' ?>">
                            <i class="ri-bar-chart-line me-1"></i>
                            Statistics
                        </a>
                        <a href="<?= $base ?>html/?s=admin&ss=leave&p=dashboard" class="btn btn-outline-secondary me-2">
                            <i class="ri-dashboard-2-line me-1"></i>
                            Admin Dashboard
                        </a>
                        <button type="button" class="btn btn-primary" onclick="typeof editAccumulationPolicy === 'function' ? editAccumulationPolicy(0) : window.location.href='<?= $base ?>html/?s=admin&ss=leave&p=accumulation_policies&action=create'">
                            <i class="ri-add-line me-1"></i>
                            Create Policy
                        </button>
                    </div>
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
                    $viewsBasePath = __DIR__ . '/leave_policy_management/views/';
                ?>
                <?php if ($action === 'list'): ?>
                    <?php include $viewsBasePath . 'accumulation_policy_list.php'; ?>
                <?php elseif ($action === 'create'): ?>
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        Click the "Create Policy" button above to open the policy wizard.
                    </div>
                    <script>
                        // Auto-open modal on create action
                        document.addEventListener('DOMContentLoaded', function() {
                            if (typeof editAccumulationPolicy === 'function') {
                                editAccumulationPolicy(0);
                            }
                        });
                    </script>
                <?php elseif ($action === 'view' || $action === 'edit'): ?>
                    <?php include $viewsBasePath . 'accumulation_policy_detail.php'; ?>
                    <?php if ($action === 'edit' && isset($policy) && $policy): ?>
                        <script>
                            // Auto-open modal on edit action
                            document.addEventListener('DOMContentLoaded', function() {
                                if (typeof editAccumulationPolicy === 'function' && <?= $policy->policyID ?? 0 ?>) {
                                    editAccumulationPolicy(<?= $policy->policyID ?? 0 ?>);
                                }
                            });
                        </script>
                    <?php endif; ?>
                <?php elseif ($action === 'statistics'): ?>
                    <?php include $viewsBasePath . 'statistics.php'; ?>
                <?php endif; ?>

                <!-- Include modal for all actions -->
                <?php include $viewsBasePath . 'accumulation_policy_modal.php'; ?>
            </div>
        </div>
    </div>

<!-- Rule Template for Dynamic Addition -->
<template id="ruleTemplate">
    <div class="rule-item">
        <div class="row">
            <div class="col-md-3">
                <label class="form-label">Leave Type</label>
                <select name="ruleLeaveTypeID[]" class="form-select" required>
                    <option value="">Select Leave Type</option>
                    <?php if ($leaveTypes): ?>
                        <?php foreach ($leaveTypes as $type): ?>
                            <option value="<?= $type->leaveTypeID ?>">
                                <?= htmlspecialchars($type->leaveTypeName) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Accrual Rate</label>
                <input type="number" name="ruleAccrualRate[]" class="form-control"
                       step="0.01" min="0" max="365" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Max Days</label>
                <input type="number" name="ruleMaxDays[]" class="form-control"
                       min="0" max="365">
            </div>
            <div class="col-md-2">
                <label class="form-label">Priority</label>
                <input type="number" name="rulePriority[]" class="form-control"
                       min="1" max="10" value="1">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="ruleStatus[]" class="form-select">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-outline-danger remove-rule-btn"
                        title="Remove Rule">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
        </div>
    </div>
</template>

