<?php
/**
 * Leave Policies Management - Admin Page
 * Comprehensive policy configuration hub for leave types
 * Handles entity-scoped policy configuration with all components integrated
 */

if(!$isValidUser) {
    Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

// Check admin permissions
if (!$isAdmin && !$isValidAdmin && !$isHRManager) {
    Alert::error("Access denied. Admin privileges required.", true);
    return;
}

$entityID = $_SESSION['entityID'] ?? 1;
$currentUserID = $userDetails->ID;

// Get current page parameters
$action = $_GET['action'] ?? 'list';
$policyID = $_GET['policyID'] ?? null;
$leaveTypeID = $_GET['leaveTypeID'] ?? null;

// Initialize data arrays
$policies = array();
$policy = null;
$leaveTypes = array();
$accumulationPolicies = array();
$workflows = array();
$errors = array();
$success = '';

try {
    // Get supporting data
    $leaveTypes = Leave::leave_types(array('Lapsed' => 'N'), false, $DBConn);
    $accumulationPolicies = AccumulationPolicy::get_policies($entityID, true, $DBConn) ?? array();

    // Handle different actions
    switch ($action) {
        case 'list':
            // Get all leave policies (leave types) with their configuration status
            $policies = Leave::leave_types(array('Lapsed' => 'N'), false, $DBConn);
            break;

        case 'view':
        case 'edit':
            if ($policyID || $leaveTypeID) {
                $id = $policyID ?? $leaveTypeID;
                $policy = Leave::leave_types(array('leaveTypeID' => $id), true, $DBConn);
                // var_dump($policy);

                if ($policy) {
                    // Fetch configurationData and other fields that might not be in the standard leave_types query
                    try {
                        $sql = "SELECT * FROM tija_leave_types WHERE leaveTypeID = ?";
                        $params = array(array($id, 'i'));
                        $fullPolicyData = $DBConn->fetch_all_rows($sql, $params);

                        // var_dump($fullPolicyData);

                        if (!empty($fullPolicyData) && is_array($fullPolicyData) && count($fullPolicyData) > 0) {
                            $fullPolicy = $fullPolicyData[0];

                            // Merge additional fields into policy object
                            foreach ($fullPolicy as $key => $value) {
                                if (!isset($policy->$key)) {
                                    $policy->$key = $value;
                                }
                            }

                            // Parse configurationData JSON if it exists
                            if (!empty($policy->configurationData)) {
                                $configData = json_decode($policy->configurationData, true);
                                if ($configData && is_array($configData)) {
                                    // Attach parsed configuration data to policy object
                                    $policy->config = $configData;

                                    // Flatten for easier access in form
                                    if (isset($configData['entitlements']) && is_array($configData['entitlements'])) {
                                        foreach ($configData['entitlements'] as $key => $value) {
                                            $policy->$key = $value;
                                        }
                                    }
                                    if (isset($configData['carryOver']) && is_array($configData['carryOver'])) {
                                        foreach ($configData['carryOver'] as $key => $value) {
                                            $policy->$key = $value;
                                        }
                                    }
                                    if (isset($configData['eligibility']) && is_array($configData['eligibility'])) {
                                        foreach ($configData['eligibility'] as $key => $value) {
                                            $policy->$key = $value;
                                        }
                                    }
                                    if (isset($configData['applicationRules']) && is_array($configData['applicationRules'])) {
                                        foreach ($configData['applicationRules'] as $key => $value) {
                                            $policy->$key = $value;
                                        }
                                    }
                                    if (isset($configData['workflows']) && is_array($configData['workflows'])) {
                                        foreach ($configData['workflows'] as $key => $value) {
                                            $policy->$key = $value;
                                        }
                                    }
                                }
                            }
                        }
                    } catch (Exception $e) {
                        // If configurationData column doesn't exist or query fails, continue without it
                        // This allows the form to still work for policies without the new fields
                        error_log('Error loading policy configuration data: ' . $e->getMessage());
                    }

                    // Get related policy data - fetch all entitlements (not just one)
                    $entitlements = Leave::leave_entitlements(
                        array('leaveTypeID' => $id, 'entityID' => $entityID, 'Suspended' => 'N'),
                        false,
                        $DBConn
                    );
                    // Ensure entitlements is always an array
                    $policy->entitlements = is_array($entitlements) ? $entitlements : (is_object($entitlements) ? array($entitlements) : array());

                    // Get accumulation policy and its rules
                    $policy->accumulationPolicy = null;
                    $policy->accumulationRules = array();
                    if (!empty($accumulationPolicies)) {
                        foreach ($accumulationPolicies as $ap) {
                            if ($ap->leaveTypeID == $id) {
                                $policy->accumulationPolicy = $ap;
                                // Fetch accumulation rules for this policy
                                if (!empty($ap->policyID)) {
                                    $policy->accumulationRules = AccumulationPolicy::get_policy_rules($ap->policyID, true, $DBConn) ?? array();
                                }
                                break;
                            }
                        }
                    }
                } else {
                    $errors[] = 'Policy not found';
                }
            } else {
                $errors[] = 'Policy ID or Leave Type ID is required';
            }
            break;

        case 'create':
            // If leaveTypeID is provided, pre-select that leave type
            if ($leaveTypeID) {
                $selectedLeaveType = Leave::leave_types(array('leaveTypeID' => $leaveTypeID), true, $DBConn);
                if (!$selectedLeaveType) {
                    $errors[] = 'Leave type not found';
                }
            }
            break;

        case 'delete':
            // Redirect to backend script for deletion
            if ($policyID || $leaveTypeID) {
                $id = (int)($policyID ?? $leaveTypeID);
                $siteURL = '';
                if (is_array($config) && isset($config['siteURL'])) {
                    $siteURL = $config['siteURL'];
                } elseif (is_object($config) && isset($config->siteURL)) {
                    $siteURL = $config->siteURL;
                }
                $backendScript = !empty($siteURL) ? rtrim((string)$siteURL, '/') . '/php/scripts/leave/config/delete_leave_type.php' : '/php/scripts/leave/config/delete_leave_type.php';
                header("Location: " . $backendScript . "?leaveTypeID=" . $id);
                exit;
            } else {
                $errors[] = 'Policy ID is required';
            }
            break;

        case 'toggle_status':
            // Redirect to backend script for status toggle
            if ($policyID || $leaveTypeID) {
                $id = (int)($policyID ?? $leaveTypeID);
                $siteURL = '';
                if (is_array($config) && isset($config['siteURL'])) {
                    $siteURL = $config['siteURL'];
                } elseif (is_object($config) && isset($config->siteURL)) {
                    $siteURL = $config->siteURL;
                }
                $backendScript = !empty($siteURL) ? rtrim((string)$siteURL, '/') . '/php/scripts/leave/config/toggle_leave_type_status.php' : '/php/scripts/leave/config/toggle_leave_type_status.php';
                header("Location: " . $backendScript . "?leaveTypeID=" . $id);
                exit;
            } else {
                $errors[] = 'Policy ID is required';
            }
            break;
    }

} catch (Exception $e) {
    $errors[] = $e->getMessage();
}

// Get page title
$pageTitle = 'Leave Policies';
if ($action === 'view' || $action === 'edit') {
    $pageTitle = ($action === 'edit' ? 'Configure' : 'View') . ' Leave Policy';
} elseif ($action === 'create') {
    $pageTitle = 'Create Leave Policy';
}

// Set page variables for header
$title = $pageTitle . ' - Leave Management System';
$keywords = array('leave management', 'leave policies', 'admin', 'policy configuration');
?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-24 mb-0">
            <i class="ri-file-list-3-line me-2 text-primary"></i>
            <?= $pageTitle ?>
        </h1>
        <p class="text-muted mb-0 mt-2">Configure comprehensive leave policies with entitlements, accrual rules, eligibility, application rules, and workflows</p>
    </div>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= $base ?>html/?s=admin&ss=leave&p=dashboard">Leave Admin</a></li>
                <li class="breadcrumb-item"><a href="<?= $base ?>html/?s=admin&ss=leave&p=leave_types">Leave Types</a></li>
                <li class="breadcrumb-item active" aria-current="page">Leave Policies</li>
            </ol>
        </nav>
    </div>
</div>

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
<?php if ($action === 'list'): ?>
    <?php include 'leave_policy_management/views/policy_list.php'; ?>
<?php elseif ($action === 'create'): ?>
    <?php
    // Set variables for the form
    $isEdit = false;
    $policy = isset($selectedLeaveType) ? $selectedLeaveType : null;
    include 'leave_policy_management/views/policy_form.php';
    ?>
<?php elseif ($action === 'view' || $action === 'edit'): ?>
    <?php
    // Set variables for the detail view
    $isEdit = $action === 'edit';
    include 'leave_policy_management/views/policy_detail.php';
    ?>
<?php endif; ?>