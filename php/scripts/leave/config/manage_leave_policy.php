<?php
/**
 * Comprehensive Leave Policy Management Handler
 * Handles creation and updates of complete leave policies
 */

// Start session and include necessary files
session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';


// Check admin permissions
if (!$isAdmin && !$isValidAdmin && !$isHRManager) {
    http_response_code(403);
    Alert::error("Access denied. Admin privileges required.", true);
    header('Location: ' . $base . 'html/');
    exit;
}

$currentUserID = $userDetails->ID;
$entityID = $_SESSION['entityID'] ?? 1;


try {
    $action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : 'create';
    $isDraft = isset($_POST['isDraft']) && ($_POST['isDraft'] === 'Y' || $_POST['isDraft'] === '1');
    $policyID = isset($_POST['policyID']) ? (int) Utility::clean_string($_POST['policyID']) : 0;

    // Collect all form data
    $basicData = [
        'entityID' => $entityID,
        'leaveTypeName' => isset($_POST['leaveTypeName']) ? Utility::clean_string($_POST['leaveTypeName']) : '',
        'leaveTypeCode' => strtoupper(isset($_POST['leaveTypeCode']) ? Utility::clean_string($_POST['leaveTypeCode']) : ''),
        'leaveTypeDescription' => isset($_POST['leaveTypeDescription']) ? Utility::clean_string($_POST['leaveTypeDescription']) : null,
        'isPaidLeave' => isset($_POST['isPaidLeave']) ? Utility::clean_string($_POST['isPaidLeave']) : 'Y',
        'requiresApproval' => isset($_POST['requiresApproval']) ? Utility::clean_string($_POST['requiresApproval']) : 'Y',
        'Suspended' => isset($_POST['status']) ? Utility::clean_string($_POST['status']) : 'N',
        'LastUpdate' => $config['currentDateTimeFormated'],
        'LastUpdateByID' => $currentUserID
    ];

    // Entitlement data
    $entitlementData = [
        'annualEntitlement' => isset($_POST['annualEntitlement']) ? (float) $_POST['annualEntitlement'] : null,
        'accrualMethod' => isset($_POST['accrualMethod']) ? Utility::clean_string($_POST['accrualMethod']) : 'upfront',
        'accrualRate' => isset($_POST['accrualRate']) ? (float) $_POST['accrualRate'] : null,
        'allowProration' => isset($_POST['allowProration']) ? Utility::clean_string($_POST['allowProration']) : 'N',
        'allowNegativeBalance' => isset($_POST['allowNegativeBalance']) ? Utility::clean_string($_POST['allowNegativeBalance']) : 'N',
        'maxAccrual' => isset($_POST['maxAccrual']) ? (float) $_POST['maxAccrual'] : null,
        'minBalance' => isset($_POST['minBalance']) ? (float) $_POST['minBalance'] : 0.5
    ];

    // Carry-over data
    $carryOverData = [
        'allowCarryOver' => $_POST['allowCarryOver'] ?? 'N',
        'maxCarryOver' => isset($_POST['maxCarryOver']) ? (float) $_POST['maxCarryOver'] : null,
        'carryOverExpiry' => isset($_POST['carryOverExpiry']) ? (float) $_POST['carryOverExpiry'] : null,
        'useItOrLoseIt' => isset($_POST['useItOrLoseIt']) ? Utility::clean_string($_POST['useItOrLoseIt']) : 'N',
        'allowCashout' => $_POST['allowCashout'] ?? 'N',
        'carryOverPriority' => $_POST['carryOverPriority'] ?? 'N'
    ];

    // Eligibility data
    $eligibilityData = [
        'minServicePeriod' => $_POST['minServicePeriod'] ?? 0,
        'excludeProbation' => $_POST['excludeProbation'] ?? 'N',
        'genderRestriction' => $_POST['genderRestriction'] ?? 'all',
        'employmentType' => is_array($_POST['employmentType']) ? implode(',', $_POST['employmentType']) : ($_POST['employmentType'] ?? 'all')
    ];

    // Application rules data
    $applicationRulesData = [
        'minNoticeDays' => $_POST['minNoticeDays'] ?? 0,
        'maxAdvanceBooking' => $_POST['maxAdvanceBooking'] ?? null,
        'allowBackdated' => $_POST['allowBackdated'] ?? 'N',
        'minDaysPerApplication' => $_POST['minDaysPerApplication'] ?? 0.5,
        'maxDaysPerApplication' => $_POST['maxDaysPerApplication'] ?? null,
        'allowHalfDay' => $_POST['allowHalfDay'] ?? 'Y',
        'requireDocumentation' => $_POST['requireDocumentation'] ?? 'N',
        'blackoutPeriods' => $_POST['blackoutPeriods'] ?? null
    ];

    // Workflow links
    $workflowData = [
        'accumulationPolicyID' => $_POST['accumulationPolicyID'] ?? null,
        'approvalWorkflowID' => $_POST['approvalWorkflowID'] ?? null
    ];

    // Validate required fields
    if (empty($basicData['leaveTypeName']) || empty($basicData['leaveTypeCode'])) {
        throw new Exception('Policy name and code are required');
    }

    // Merge all data for the leave type table
    $policyData = array_merge(
        $basicData,
        $entitlementData,
        $carryOverData,
        $eligibilityData,
        $applicationRulesData,
        $workflowData
    );

    // Store as JSON for fields not in standard table structure
    $policyData['configurationData'] = json_encode([
        'entitlements' => $entitlementData,
        'carryOver' => $carryOverData,
        'eligibility' => $eligibilityData,
        'applicationRules' => $applicationRulesData,
        'workflows' => $workflowData,
        'isDraft' => $isDraft
    ]);

    if ($action === 'create') {
        // Create new policy
        $policyData['CreateDate'] = $config['currentDateTimeFormated'];
        $policyData['CreatedByID'] = $currentUserID;

        $result = $DBConn->insert_data('tija_leave_types', $policyData);

        if ($result) {
            $newPolicyID = $DBConn->lastInsertId();

            // Create default entitlement if annual entitlement is provided
            if (!empty($entitlementData['annualEntitlement'])) {
                $entData = [
                    'entityID' => $entityID,
                    'leaveTypeID' => $newPolicyID,
                    'entitlement' => $entitlementData['annualEntitlement'],
                    'maxDaysPerApplication' => $applicationRulesData['maxDaysPerApplication'],
                    'minNoticeDays' => $applicationRulesData['minNoticeDays'],
                    'Suspended' => 'N',
                    'CreateDate' => $config['currentDateTimeFormated'],
                    'CreatedByID' => $currentUserID,
                    'LastUpdate' => $config['currentDateTimeFormated'],
                    'LastUpdateByID' => $currentUserID
                ];

                $DBConn->insert_data('tija_leave_entitlements', $entData);
            }

            Alert::success('Leave policy created successfully' . ($isDraft ? ' as draft' : ''), true);
            header('Location: ' . $base . 'html/?s=admin&ss=leave&p=leave_policies&action=view&policyID=' . $newPolicyID);
            exit;
        } else {
            throw new Exception('Failed to create leave policy');
        }

    } elseif ($action === 'update') {
        // Update existing policy
        if (!$policyID) {
            throw new Exception('Policy ID is required for updates');
        }

        $result = $DBConn->update_table('tija_leave_types', $policyData, ['leaveTypeID' => $policyID]);

        if ($result) {
            // Update or create entitlement
            if (!empty($entitlementData['annualEntitlement'])) {
                $existingEnt = Leave::leave_entitlements(['leaveTypeID' => $policyID, 'Suspended' => 'N'], true, $DBConn);

                $entData = [
                    'entitlement' => $entitlementData['annualEntitlement'],
                    'maxDaysPerApplication' => $applicationRulesData['maxDaysPerApplication'],
                    'minNoticeDays' => $applicationRulesData['minNoticeDays'],
                    'LastUpdate' => $config['currentDateTimeFormated'],
                    'LastUpdateByID' => $currentUserID
                ];

                if ($existingEnt) {
                    $DBConn->update_table('tija_leave_entitlements', $entData, ['leaveEntitlementID' => $existingEnt->leaveEntitlementID]);
                } else {
                    $entData['entityID'] = $entityID;
                    $entData['leaveTypeID'] = $policyID;
                    $entData['Suspended'] = 'N';
                    $entData['CreateDate'] = $config['currentDateTimeFormated'];
                    $entData['CreatedByID'] = $currentUserID;
                    $DBConn->insert_data('tija_leave_entitlements', $entData);
                }
            }

            Alert::success('Leave policy updated successfully', true);
            header('Location: ' . $base . 'html/?s=admin&ss=leave&p=leave_policies&action=view&policyID=' . $policyID);
            exit;
        } else {
            throw new Exception('Failed to update leave policy');
        }
    }

} catch (Exception $e) {
    Alert::error('Error: ' . $e->getMessage(), true);
    // Build redirect URL with proper parameters
    $redirectParams = [
        's' => 'admin',
        'ss' => 'leave',
        'p' => 'leave_policies',
        'action' => $policyID ? 'edit' : 'create'
    ];
    if ($policyID) {
        $redirectParams['policyID'] = $policyID;
    }
    header('Location: ' . $base . 'html/?' . http_build_query($redirectParams));
    exit;
}
?>

