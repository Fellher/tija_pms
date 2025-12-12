<?php
/**
 * AJAX Handler for Leave Accumulation Policy Management
 * Handles CRUD operations for policies and rules
 */

// Set JSON response header
header('Content-Type: application/json');

// Start session and include necessary files
session_start();
$base = '../../../../';
require_once $base . 'php/includes.php';

// Check admin permissions - use the same check as other leave admin pages
if (!$isAdmin && !$isValidAdmin && !$isHRManager) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
    exit;
}

$currentUserID = $userDetails->ID ?? $userDetails->ID ?? 0;
$entityID = $_SESSION['entityID'] ?? 1;

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

$response = ['success' => false, 'message' => ''];

try {
    switch ($action) {
        case 'create_policy':
            if ($method !== 'POST') {
                throw new Exception('Invalid request method');
            }

            // Get policy scope
            $policyScope = $_POST['policyScope'] ?? 'Entity';

            // Handle scope-specific entityID logic
            $postEntityID = null;
            $parentEntityID = null;

            if ($policyScope === 'Global') {
                // Global: entityID = NULL, parentEntityID = 0
                $postEntityID = null;
                $parentEntityID = 0;
            } elseif ($policyScope === 'Entity') {
                // Entity: entityID required
                $postEntityID = isset($_POST['entityID']) ? (int)$_POST['entityID'] : $entityID;
                // Ensure HR managers can only create policies for their entity
                if ($isHRManager && !$isAdmin && !$isValidAdmin) {
                    $postEntityID = $entityID;
                }
                $parentEntityID = null;
            } elseif ($policyScope === 'Cadre') {
                // Cadre: entityID required
                $postEntityID = isset($_POST['entityID']) ? (int)$_POST['entityID'] : $entityID;
                // Ensure HR managers can only create policies for their entity
                if ($isHRManager && !$isAdmin && !$isValidAdmin) {
                    $postEntityID = $entityID;
                }
                $parentEntityID = null;
            }

            $policyData = [
                'entityID' => $postEntityID,
                'policyScope' => $policyScope,
                'parentEntityID' => $parentEntityID,
                'policyName' => $_POST['policyName'] ?? '',
                'policyDescription' => (isset($_POST['policyDescription']) && trim($_POST['policyDescription']) !== '') ? trim($_POST['policyDescription']) : null,
                'leaveTypeID' => $_POST['leaveTypeID'] ?? '',
                'jobCategoryID' => (isset($_POST['jobCategoryID']) && $_POST['jobCategoryID'] !== '') ? (int)$_POST['jobCategoryID'] : null,
                'jobBandID' => (isset($_POST['jobBandID']) && $_POST['jobBandID'] !== '') ? (int)$_POST['jobBandID'] : null,
                'accrualType' => $_POST['accrualType'] ?? 'Monthly',
                'accrualRate' => isset($_POST['accrualRate']) ? (float)$_POST['accrualRate'] : null,
                'maxCarryover' => (isset($_POST['maxCarryover']) && $_POST['maxCarryover'] !== '') ? (int)$_POST['maxCarryover'] : null,
                'carryoverExpiryMonths' => (isset($_POST['carryoverExpiryMonths']) && $_POST['carryoverExpiryMonths'] !== '') ? (int)$_POST['carryoverExpiryMonths'] : null,
                'proRated' => (isset($_POST['proRated']) && $_POST['proRated'] === 'Y') ? 'Y' : 'N',
                'isActive' => (isset($_POST['isActive']) && $_POST['isActive'] === 'Y') ? 'Y' : 'N',
                'priority' => isset($_POST['priority']) ? (int)$_POST['priority'] : 1,
                'Suspended' => (isset($_POST['Suspended']) && $_POST['Suspended'] === 'Y') ? 'Y' : 'N',
                'LastUpdateByID' => $currentUserID
            ];

            // Validate policy data - check if validate_policy exists
            $errors = array();
            if (method_exists('AccumulationPolicy', 'validate_policy')) {
                $errors = AccumulationPolicy::validate_policy($policyData);
            } else {
                // Basic validation
                if (empty($policyData['policyName']) || empty($policyData['leaveTypeID']) || empty($policyData['accrualType']) || empty($policyData['accrualRate'])) {
                    $errors[] = 'Required fields missing: policyName, leaveTypeID, accrualType, accrualRate';
                }
            }
            if (!empty($errors)) {
                throw new Exception('Validation errors: ' . (is_array($errors) ? implode(', ', $errors) : $errors));
            }

            // Create policy
            $policyID = AccumulationPolicy::create_policy($policyData, $DBConn);

            if ($policyID) {
                // Handle rules if provided
                if (isset($_POST['ruleName']) && is_array($_POST['ruleName'])) {
                    for ($i = 0; $i < count($_POST['ruleName']); $i++) {
                        if (!empty($_POST['ruleName'][$i])) {
                            $ruleData = [
                                'policyID' => $policyID,
                                'ruleName' => $_POST['ruleName'][$i],
                                'ruleType' => $_POST['ruleType'][$i] ?? 'Custom',
                                'conditionField' => $_POST['conditionField'][$i] ?? null,
                                'conditionOperator' => $_POST['conditionOperator'][$i] ?? '>=',
                                'conditionValue' => $_POST['conditionValue'][$i] ?? null,
                                'accrualMultiplier' => $_POST['accrualMultiplier'][$i] ?? 1.00,
                                'LastUpdateByID' => $currentUserID
                            ];

                            // Validate rule data - check if validate_rule exists
                            $ruleErrors = array();
                            if (method_exists('AccumulationPolicy', 'validate_rule')) {
                                $ruleErrors = AccumulationPolicy::validate_rule($ruleData);
                            }
                            if (empty($ruleErrors)) {
                                AccumulationPolicy::create_rule($ruleData, $DBConn);
                            }
                        }
                    }
                }

                $response = [
                    'success' => true,
                    'message' => 'Policy created successfully',
                    'policyID' => $policyID
                ];
            } else {
                throw new Exception('Failed to create policy');
            }
            break;

        case 'update_policy':
            if ($method !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $policyID = $_POST['policyID'] ?? '';
            if (!$policyID) {
                throw new Exception('Policy ID is required');
            }

            // Check if HR manager is trying to edit policy from different entity
            if ($isHRManager && !$isAdmin && !$isValidAdmin) {
                $existingPolicy = AccumulationPolicy::get_policy($policyID, $DBConn);
                if ($existingPolicy && $existingPolicy->entityID != $entityID) {
                    throw new Exception('Access denied. You can only edit policies for your entity.');
                }
            }

            // Get policy scope
            $policyScope = $_POST['policyScope'] ?? null;

            // Handle scope-specific entityID logic for updates
            $updateEntityID = null;
            $updateParentEntityID = null;

            if ($policyScope === 'Global') {
                // Global: entityID = NULL, parentEntityID = 0
                $updateEntityID = null;
                $updateParentEntityID = 0;
            } elseif ($policyScope === 'Entity') {
                // Entity: entityID required
                $updateEntityID = isset($_POST['entityID']) ? (int)$_POST['entityID'] : null;
                $updateParentEntityID = null;
            } elseif ($policyScope === 'Cadre') {
                // Cadre: entityID required
                $updateEntityID = isset($_POST['entityID']) ? (int)$_POST['entityID'] : null;
                $updateParentEntityID = null;
            }

            $policyData = [
                'policyName' => $_POST['policyName'] ?? '',
                'policyDescription' => (isset($_POST['policyDescription']) && trim($_POST['policyDescription']) !== '') ? trim($_POST['policyDescription']) : null,
                'leaveTypeID' => $_POST['leaveTypeID'] ?? '',
                'accrualType' => $_POST['accrualType'] ?? 'Monthly',
                'accrualRate' => isset($_POST['accrualRate']) ? (float)$_POST['accrualRate'] : null,
                'maxCarryover' => (isset($_POST['maxCarryover']) && $_POST['maxCarryover'] !== '') ? (int)$_POST['maxCarryover'] : null,
                'carryoverExpiryMonths' => (isset($_POST['carryoverExpiryMonths']) && $_POST['carryoverExpiryMonths'] !== '') ? (int)$_POST['carryoverExpiryMonths'] : null,
                'proRated' => (isset($_POST['proRated']) && $_POST['proRated'] === 'Y') ? 'Y' : 'N',
                'isActive' => (isset($_POST['isActive']) && $_POST['isActive'] === 'Y') ? 'Y' : 'N',
                'priority' => isset($_POST['priority']) ? (int)$_POST['priority'] : 1,
                'Suspended' => (isset($_POST['Suspended']) && $_POST['Suspended'] === 'Y') ? 'Y' : 'N',
                'LastUpdateByID' => $currentUserID
            ];

            // Add scope-related fields if provided
            if ($policyScope !== null) {
                $policyData['policyScope'] = $policyScope;
            }
            if ($updateEntityID !== null) {
                $policyData['entityID'] = $updateEntityID;
            }
            if ($updateParentEntityID !== null) {
                $policyData['parentEntityID'] = $updateParentEntityID;
            }
            if (isset($_POST['jobCategoryID'])) {
                $policyData['jobCategoryID'] = ($_POST['jobCategoryID'] !== '') ? (int)$_POST['jobCategoryID'] : null;
            }
            if (isset($_POST['jobBandID'])) {
                $policyData['jobBandID'] = ($_POST['jobBandID'] !== '') ? (int)$_POST['jobBandID'] : null;
            }

            // Validate policy data - check if validate_policy exists
            $errors = array();
            if (method_exists('AccumulationPolicy', 'validate_policy')) {
                $errors = AccumulationPolicy::validate_policy($policyData);
            } else {
                // Basic validation
                if (empty($policyData['policyName']) || empty($policyData['leaveTypeID']) || empty($policyData['accrualType']) || empty($policyData['accrualRate'])) {
                    $errors[] = 'Required fields missing: policyName, leaveTypeID, accrualType, accrualRate';
                }
            }
            if (!empty($errors)) {
                throw new Exception('Validation errors: ' . (is_array($errors) ? implode(', ', $errors) : $errors));
            }

            // Update policy
            if (AccumulationPolicy::update_policy($policyID, $policyData, $DBConn)) {
                // Handle rules update (delete existing and recreate)
                if (isset($_POST['ruleName']) && is_array($_POST['ruleName'])) {
                    // Get existing rules and mark them as deleted
                    $existingRules = AccumulationPolicy::get_policy_rules($policyID, false, $DBConn);
                    if ($existingRules && is_array($existingRules)) {
                        foreach ($existingRules as $rule) {
                            $ruleID = is_object($rule) ? $rule->ruleID : (is_array($rule) ? $rule['ruleID'] : $rule);
                            if ($ruleID) {
                                AccumulationPolicy::delete_rule($ruleID, $currentUserID, $DBConn);
                            }
                        }
                    }

                    // Create new rules
                    for ($i = 0; $i < count($_POST['ruleName']); $i++) {
                        if (!empty($_POST['ruleName'][$i])) {
                            $ruleData = [
                                'policyID' => $policyID,
                                'ruleName' => $_POST['ruleName'][$i],
                                'ruleType' => $_POST['ruleType'][$i] ?? 'Custom',
                                'conditionField' => $_POST['conditionField'][$i] ?? null,
                                'conditionOperator' => $_POST['conditionOperator'][$i] ?? '>=',
                                'conditionValue' => $_POST['conditionValue'][$i] ?? null,
                                'accrualMultiplier' => $_POST['accrualMultiplier'][$i] ?? 1.00,
                                'LastUpdateByID' => $currentUserID
                            ];

                            // Validate rule data - check if validate_rule exists
                            $ruleErrors = array();
                            if (method_exists('AccumulationPolicy', 'validate_rule')) {
                                $ruleErrors = AccumulationPolicy::validate_rule($ruleData);
                            }
                            if (empty($ruleErrors)) {
                                AccumulationPolicy::create_rule($ruleData, $DBConn);
                            }
                        }
                    }
                }

                $response = [
                    'success' => true,
                    'message' => 'Policy updated successfully'
                ];
            } else {
                throw new Exception('Failed to update policy');
            }
            break;

        case 'delete_policy':
            if ($method !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $policyID = $_POST['policyID'] ?? '';
            if (!$policyID) {
                throw new Exception('Policy ID is required');
            }

            // Verify policy exists before deletion
            $existingPolicy = AccumulationPolicy::get_policy($policyID, $DBConn);
            if (!$existingPolicy) {
                throw new Exception('Policy not found');
            }

            // Check if HR manager is trying to delete policy from different entity
            if ($isHRManager && !$isAdmin && !$isValidAdmin) {
                if ($existingPolicy->entityID != $entityID) {
                    throw new Exception('Access denied. You can only delete policies for your entity.');
                }
            }

            // Perform the delete (soft delete - sets Lapsed = 'Y')
            $deleteResult = AccumulationPolicy::delete_policy($policyID, $currentUserID, $DBConn);

            if ($deleteResult) {
                // Verify the delete was successful by checking the policy again
                $deletedPolicy = AccumulationPolicy::get_policy($policyID, $DBConn);
                if ($deletedPolicy && ($deletedPolicy->Lapsed ?? 'N') === 'Y') {
                    $response = [
                        'success' => true,
                        'message' => 'Policy deleted successfully'
                    ];
                } else {
                    throw new Exception('Delete operation completed but verification failed. Policy may still be active.');
                }
            } else {
                throw new Exception('Failed to delete policy. Database update returned false.');
            }
            break;

        case 'get_policy':
            if ($method !== 'GET') {
                throw new Exception('Invalid request method');
            }

            $policyID = $_GET['policyID'] ?? '';
            if (!$policyID) {
                throw new Exception('Policy ID is required');
            }

            $policy = AccumulationPolicy::get_policy($policyID, $DBConn);

            // Check if HR manager is trying to access policy from different entity
            if ($policy && $isHRManager && !$isAdmin && !$isValidAdmin && $policy->entityID != $entityID) {
                throw new Exception('Access denied. You can only view policies for your entity.');
            }
            if ($policy) {
                $rules = AccumulationPolicy::get_policy_rules($policyID, false, $DBConn);

                // Convert policy object to array for JSON
                $policyArray = is_object($policy) ? (array)$policy : $policy;
                $rulesArray = is_array($rules) ? $rules : array();

                $response = [
                    'success' => true,
                    'policy' => $policyArray,
                    'rules' => $rulesArray
                ];
            } else {
                throw new Exception('Policy not found');
            }
            break;

        case 'get_policies':
            if ($method !== 'GET') {
                throw new Exception('Invalid request method');
            }

            $activeOnly = $_GET['activeOnly'] ?? 'true';
            $policies = AccumulationPolicy::get_policies($entityID, $activeOnly === 'true', $DBConn);

            $response = [
                'success' => true,
                'policies' => $policies
            ];
            break;

        case 'calculate_accrual':
            if ($method !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $employeeID = $_POST['employeeID'] ?? '';
            $policyID = $_POST['policyID'] ?? '';
            $period = $_POST['period'] ?? date('Y-m');

            if (!$employeeID || !$policyID) {
                throw new Exception('Employee ID and Policy ID are required');
            }

            $calculation = AccumulationPolicy::calculate_employee_accrual($employeeID, $policyID, $period, $DBConn);

            $response = [
                'success' => true,
                'calculation' => $calculation
            ];
            break;

        case 'get_employee_history':
            if ($method !== 'GET') {
                throw new Exception('Invalid request method');
            }

            $employeeID = $_GET['employeeID'] ?? '';
            $leaveTypeID = $_GET['leaveTypeID'] ?? null;
            $startDate = $_GET['startDate'] ?? null;
            $endDate = $_GET['endDate'] ?? null;

            if (!$employeeID) {
                throw new Exception('Employee ID is required');
            }

            $history = AccumulationPolicy::get_employee_history($employeeID, $leaveTypeID, $startDate, $endDate, $DBConn);

            $response = [
                'success' => true,
                'history' => $history
            ];
            break;

        case 'get_statistics':
            if ($method !== 'GET') {
                throw new Exception('Invalid request method');
            }

            $startDate = $_GET['startDate'] ?? date('Y-01-01');
            $endDate = $_GET['endDate'] ?? date('Y-12-31');

            $statistics = AccumulationPolicy::get_accumulation_statistics($entityID, $startDate, $endDate, $DBConn);

            $response = [
                'success' => true,
                'statistics' => $statistics
            ];
            break;

        case 'toggle_policy_status':
            if ($method !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $policyID = $_POST['policyID'] ?? '';
            $newStatus = $_POST['status'] ?? '';

            if (!$policyID || !in_array($newStatus, ['Y', 'N'])) {
                throw new Exception('Invalid parameters');
            }

            // Check if HR manager is trying to toggle policy from different entity
            if ($isHRManager && !$isAdmin && !$isValidAdmin) {
                $existingPolicy = AccumulationPolicy::get_policy($policyID, $DBConn);
                if ($existingPolicy && $existingPolicy->entityID != $entityID) {
                    throw new Exception('Access denied. You can only modify policies for your entity.');
                }
            }

            $policyData = [
                'isActive' => $newStatus,
                'LastUpdateByID' => $currentUserID
            ];

            if (AccumulationPolicy::update_policy($policyID, $policyData, $DBConn)) {
                $statusText = $newStatus === 'Y' ? 'activated' : 'deactivated';
                $response = [
                    'success' => true,
                    'message' => "Policy $statusText successfully"
                ];
            } else {
                throw new Exception('Failed to update policy status');
            }
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];

    // Log error for debugging
    error_log("Accumulation Policy Error: " . $e->getMessage());
}

// Return JSON response
echo json_encode($response);
exit;
?>

