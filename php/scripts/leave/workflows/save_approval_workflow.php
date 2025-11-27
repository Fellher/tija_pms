<?php
/**
 * Save Approval Workflow (Create or Update)
 * Handles workflow CRUD operations
 */

session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';

header('Content-Type: application/json');

// Check if user is admin
if (!$isAdmin && !$isValidAdmin && !$isHRManager) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get POST data
$action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : '';
$policyID = isset($_POST['policyID']) && !empty($_POST['policyID']) ? intval($_POST['policyID']) : 0;
$policyName = isset($_POST['policyName']) ? Utility::clean_string($_POST['policyName']) : '';
$policyDescription = isset($_POST['policyDescription']) ? Utility::clean_string($_POST['policyDescription']) : '';
$entityID = isset($_POST['entityID']) ? intval($_POST['entityID']) : 0;
$orgDataID = isset($_POST['orgDataID']) ? intval($_POST['orgDataID']) : 0;
$requireAllApprovals = isset($_POST['requireAllApprovals']) ? 'Y' : 'N';
$allowDelegation = isset($_POST['allowDelegation']) ? 'Y' : 'N';
$autoApproveThreshold = isset($_POST['autoApproveThreshold']) && !empty($_POST['autoApproveThreshold']) ? intval($_POST['autoApproveThreshold']) : null;

// Delegation settings
$autoDelegationOnLeave = isset($_POST['autoDelegationOnLeave']) ? 'Y' : 'N';
$delegationMethod = isset($_POST['delegationMethod']) ? Utility::clean_string($_POST['delegationMethod']) : 'predefined';
$delegationPrompt = isset($_POST['delegationPrompt']) ? 'Y' : 'N';
$allowSkipLevel = isset($_POST['allowSkipLevel']) ? 'Y' : 'N';

$steps = isset($_POST['steps']) && is_array($_POST['steps']) ? $_POST['steps'] : array();

// Debug: Log all POST data related to approvers
error_log("=== SAVE WORKFLOW DEBUG ===");
error_log("Action: {$action}, PolicyID: {$policyID}");
error_log("Steps count: " . count($steps));
error_log("All POST keys: " . implode(', ', array_keys($_POST)));
foreach ($_POST as $key => $value) {
    if (strpos($key, 'stepApprover') !== false || strpos($key, 'approver') !== false) {
        error_log("Approver field: {$key} = " . (is_array($value) ? json_encode($value) : $value));
    }
}

if (empty($policyName)) {
    echo json_encode(['success' => false, 'message' => 'Workflow name is required']);
    exit;
}

if ($entityID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Entity ID is required']);
    exit;
}

if (empty($steps) || !is_array($steps)) {
    echo json_encode(['success' => false, 'message' => 'At least one approval step is required']);
    exit;
}

try {
    $DBConn->begin();

    if ($action === 'update_workflow' && $policyID > 0) {
        // Update existing workflow
        $updateData = array(
            'policyName' => $policyName,
            'policyDescription' => $policyDescription,
            'entityID' => $entityID,
            'orgDataID' => $orgDataID,
            'requireAllApprovals' => $requireAllApprovals,
            'allowDelegation' => $allowDelegation,
            'autoApproveThreshold' => $autoApproveThreshold,
            'autoDelegationOnLeave' => $autoDelegationOnLeave,
            'delegationMethod' => $delegationMethod,
            'delegationPrompt' => $delegationPrompt,
            'allowSkipLevel' => $allowSkipLevel,
            'updatedBy' => $userDetails->ID,
            'updatedAt' => $config['currentDateTimeFormated']
        );

        $where = array('policyID' => $policyID);

        if (!$DBConn->update_table('tija_leave_approval_policies', $updateData, $where)) {
            throw new Exception('Failed to update workflow policy');
        }

        // Get step IDs before deleting steps
        $stepIDsQuery = "SELECT stepID FROM tija_leave_approval_steps WHERE policyID = ?";
        $stepIDsParams = array(array($policyID, 'i'));
        $stepIDs = $DBConn->fetch_all_rows($stepIDsQuery, $stepIDsParams);

        // Delete existing step approvers first (before deleting steps)
        if ($stepIDs && count($stepIDs) > 0) {
            $stepIDList = array();
            foreach ($stepIDs as $step) {
                $stepIDList[] = is_object($step) ? $step->stepID : $step['stepID'];
            }
            if (!empty($stepIDList)) {
                $placeholders = implode(',', array_fill(0, count($stepIDList), '?'));
                $deleteApproversQuery = "DELETE FROM tija_leave_approval_step_approvers WHERE stepID IN ($placeholders)";
                $deleteParams = array();
                foreach ($stepIDList as $sid) {
                    $deleteParams[] = array($sid, 'i');
                }
                $DBConn->query($deleteApproversQuery);
                foreach ($deleteParams as $idx => $param) {
                    $DBConn->bind(($idx + 1), $param[0]);
                }
                $DBConn->execute();
            }
        }

        // Delete existing steps (we'll recreate them)
        $deleteStepsQuery = "DELETE FROM tija_leave_approval_steps WHERE policyID = ?";
        $DBConn->query($deleteStepsQuery);
        $DBConn->bind('1', $policyID);
        $DBConn->execute();

        $newPolicyID = $policyID;

    } else {
        // Create new workflow
        $policyData = array(
            'policyName' => $policyName,
            'policyDescription' => $policyDescription,
            'entityID' => $entityID,
            'orgDataID' => $orgDataID,
            'isDefault' => 'N',
            'isActive' => 'Y',
            'approvalType' => 'sequential',
            'requireAllApprovals' => $requireAllApprovals,
            'allowDelegation' => $allowDelegation,
            'autoApproveThreshold' => $autoApproveThreshold,
            'autoDelegationOnLeave' => $autoDelegationOnLeave,
            'delegationMethod' => $delegationMethod,
            'delegationPrompt' => $delegationPrompt,
            'allowSkipLevel' => $allowSkipLevel,
            'createdBy' => $userDetails->ID,
            'createdAt' => $config['currentDateTimeFormated'],
            'Lapsed' => 'N',
            'Suspended' => 'N'
        );

        if (!$DBConn->insert_data('tija_leave_approval_policies', $policyData)) {
            throw new Exception('Failed to create workflow policy');
        }

        $newPolicyID = $DBConn->lastInsertId();
    }

    // Insert workflow steps and approvers
    $stepOrder = 1;
    foreach ($steps as $stepIndex => $stepData) {
        // Skip if stepID exists but we're creating (shouldn't happen, but safety check)
        if (isset($stepData['stepID']) && $action !== 'update_workflow') {
            continue;
        }

        $stepName = isset($stepData['name']) ? Utility::clean_string($stepData['name']) : '';
        $stepType = isset($stepData['type']) ? Utility::clean_string($stepData['type']) : 'supervisor';
        $stepDescription = isset($stepData['description']) ? Utility::clean_string($stepData['description']) : null;
        $isRequired = isset($stepData['required']) && $stepData['required'] ? 'Y' : 'N';
        $isConditional = isset($stepData['conditional']) && $stepData['conditional'] ? 'Y' : 'N';
        $escalationDays = isset($stepData['escalation']) ? intval($stepData['escalation']) : 3;

        // Delegation settings for step
        $delegateApproverID = isset($stepData['delegateApproverID']) && !empty($stepData['delegateApproverID']) ? intval($stepData['delegateApproverID']) : null;
        $delegationPriority = isset($stepData['delegationPriority']) ? intval($stepData['delegationPriority']) : 1;
        $stepAllowDelegation = isset($stepData['allowDelegation']) ? 'Y' : 'Y'; // Default to Y

        if (empty($stepName)) {
            continue; // Skip empty steps
        }

        $stepInsertData = array(
            'policyID' => $newPolicyID,
            'stepOrder' => $stepOrder,
            'stepName' => $stepName,
            'stepType' => $stepType,
            'stepDescription' => $stepDescription,
            'isRequired' => $isRequired,
            'isConditional' => $isConditional,
            'escalationDays' => $escalationDays,
            'delegateApproverID' => $delegateApproverID,
            'delegationPriority' => $delegationPriority,
            'allowDelegation' => $stepAllowDelegation,
            'createdAt' => $config['currentDateTimeFormated'],
            'Suspended' => 'N'
        );

        if (!$DBConn->insert_data('tija_leave_approval_steps', $stepInsertData)) {
            throw new Exception('Failed to create workflow step');
        }

        $stepID = $DBConn->lastInsertId();

        // Save approvers for this step
        // Check for approvers in POST data (format: stepApprover_{stepOrder} or steps[{stepIndex}][approvers])
        $stepApproverKey = 'stepApprover_' . $stepOrder;
        $stepBackupApproverKey = 'stepApproverBackup_' . $stepOrder;

        // Also check if approvers are in stepData array
        $approvers = isset($stepData['approvers']) ? $stepData['approvers'] : array();

        // Check POST for approver fields
        if (isset($_POST[$stepApproverKey]) && !empty($_POST[$stepApproverKey])) {
            $approverUserID = intval($_POST[$stepApproverKey]);

            // Insert primary approver
            $approverData = array(
                'stepID' => $stepID,
                'approverUserID' => $approverUserID,
                'approverType' => 'user',
                'isBackup' => 'N',
                'notificationOrder' => 1,
                'createdAt' => $config['currentDateTimeFormated'],
                'Suspended' => 'N'
            );

            // Check if approverType column exists
            $columnsCheck = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_step_approvers LIKE 'approverType'", array());
            if (!$columnsCheck || count($columnsCheck) === 0) {
                unset($approverData['approverType']);
            }

            if (!$DBConn->insert_data('tija_leave_approval_step_approvers', $approverData)) {
                error_log("WARNING: Failed to save approver for step {$stepID}, user {$approverUserID}");
            } else {
                error_log("Saved approver for step {$stepID}: User ID {$approverUserID}");
            }

            // Check for backup approver
            if (isset($_POST[$stepBackupApproverKey]) && !empty($_POST[$stepBackupApproverKey])) {
                $backupApproverUserID = intval($_POST[$stepBackupApproverKey]);

                if ($backupApproverUserID !== $approverUserID) {
                    $backupApproverData = array(
                        'stepID' => $stepID,
                        'approverUserID' => $backupApproverUserID,
                        'approverType' => 'user',
                        'isBackup' => 'Y',
                        'notificationOrder' => 1,
                        'createdAt' => $config['currentDateTimeFormated'],
                        'Suspended' => 'N'
                    );

                    if (isset($approverData['approverType'])) {
                        unset($backupApproverData['approverType']);
                    }

                    if (!$DBConn->insert_data('tija_leave_approval_step_approvers', $backupApproverData)) {
                        error_log("WARNING: Failed to save backup approver for step {$stepID}, user {$backupApproverUserID}");
                    } else {
                        error_log("Saved backup approver for step {$stepID}: User ID {$backupApproverUserID}");
                    }
                }
            }
        } elseif (!empty($approvers) && is_array($approvers)) {
            // Handle approvers from stepData array
            foreach ($approvers as $approverIdx => $approver) {
                $approverUserID = isset($approver['userID']) ? intval($approver['userID']) : (isset($approver['approverUserID']) ? intval($approver['approverUserID']) : 0);
                $isBackup = isset($approver['isBackup']) && $approver['isBackup'] ? 'Y' : 'N';

                if ($approverUserID > 0) {
                    $approverData = array(
                        'stepID' => $stepID,
                        'approverUserID' => $approverUserID,
                        'isBackup' => $isBackup,
                        'notificationOrder' => $approverIdx + 1,
                        'createdAt' => $config['currentDateTimeFormated'],
                        'Suspended' => 'N'
                    );

                    // Check if approverType column exists
                    $columnsCheck = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_step_approvers LIKE 'approverType'", array());
                    if ($columnsCheck && count($columnsCheck) > 0) {
                        $approverData['approverType'] = isset($approver['approverType']) ? $approver['approverType'] : 'user';
                    }

                    if (!$DBConn->insert_data('tija_leave_approval_step_approvers', $approverData)) {
                        error_log("WARNING: Failed to save approver for step {$stepID}, user {$approverUserID}");
                    } else {
                        error_log("Saved approver for step {$stepID}: User ID {$approverUserID}, Backup: {$isBackup}");
                    }
                }
            }
        } else {
            // For dynamic approver types (supervisor, project_manager, etc.), we don't save specific users
            // They will be resolved dynamically when the leave application is submitted
            error_log("Step {$stepID} ({$stepType}) uses dynamic approvers - no specific users to save");
        }

        $stepOrder++;
    }

    // Commit transaction
    $DBConn->commit();

    echo json_encode([
        'success' => true,
        'message' => $action === 'update_workflow' ? 'Workflow updated successfully' : 'Workflow created successfully',
        'policyID' => $newPolicyID
    ]);

} catch (Exception $e) {
    // Rollback on error
    $DBConn->rollBack();

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

