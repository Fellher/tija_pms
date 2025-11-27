<?php
/**
 * Clone Workflow to Same or Different Entity
 */

session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';

header('Content-Type: application/json');

// Check if user is admin
if (!$isAdmin) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$policyID = isset($input['policyID']) ? intval($input['policyID']) : 0;
$targetEntityID = isset($input['entityID']) ? intval($input['entityID']) : 0;

if ($policyID <= 0 || $targetEntityID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    $DBConn->begin();

    // Get source policy using Leave class method
    $sourcePolicy = Leave::leave_approval_policies(
        array('policyID' => $policyID, 'Lapsed' => 'N'),
        true,
        $DBConn
    );

    if (!$sourcePolicy) {
        throw new Exception('Source workflow not found');
    }

    // Create new policy (clone)
    $newPolicyData = array(
        'entityID' => $targetEntityID,
        'orgDataID' => $sourcePolicy->orgDataID,
        'policyName' => $sourcePolicy->policyName . ' (Cloned)',
        'policyDescription' => $sourcePolicy->policyDescription,
        'isActive' => 'N', // Clone as inactive
        'isDefault' => 'N',
        'requireAllApprovals' => $sourcePolicy->requireAllApprovals,
        'allowDelegation' => $sourcePolicy->allowDelegation,
        'autoApproveThreshold' => $sourcePolicy->autoApproveThreshold,
        'createdBy' => $userDetails->ID,
        'createdAt' => $config['currentDateTimeFormated'],
        'Suspended' => 'N',
        'Lapsed' => 'N'
    );

    if (!$DBConn->insert_data('tija_leave_approval_policies', $newPolicyData)) {
        throw new Exception('Failed to clone policy');
    }

    $newPolicyID = $DBConn->lastInsertId();

    // Clone steps
    $stepsQuery = "SELECT * FROM tija_leave_approval_steps
                   WHERE policyID = ? AND Suspended = 'N'
                   ORDER BY stepOrder";
    $stepsParams = array(array($policyID, 'i'));
    $steps = $DBConn->fetch_all_rows($stepsQuery, $stepsParams);

    if ($steps && count($steps) > 0) {
        foreach ($steps as $step) {
            $newStepData = array(
                'policyID' => $newPolicyID,
                'stepOrder' => $step->stepOrder,
                'stepName' => $step->stepName,
                'stepType' => $step->stepType,
                'stepDescription' => $step->stepDescription,
                'isRequired' => $step->isRequired,
                'isConditional' => $step->isConditional,
                'conditionType' => $step->conditionType,
                'conditionValue' => $step->conditionValue,
                'escalationDays' => $step->escalationDays,
                'notifyOnPending' => $step->notifyOnPending,
                'notifyOnApprove' => $step->notifyOnApprove,
                'notifyOnReject' => $step->notifyOnReject,
                'createdAt' => $config['currentDateTimeFormated'],
                'Suspended' => 'N'
            );

            if (!$DBConn->insert_data('tija_leave_approval_steps', $newStepData)) {
                throw new Exception('Failed to clone workflow steps');
            }

            $newStepID = $DBConn->lastInsertId();

            // Clone custom approvers if any
            $approversQuery = "SELECT * FROM tija_leave_approval_step_approvers
                              WHERE stepID = ? AND Suspended = 'N'";
            $approversParams = array(array($step->stepID, 'i'));
            $approvers = $DBConn->fetch_all_rows($approversQuery, $approversParams);

            if ($approvers && count($approvers) > 0) {
                foreach ($approvers as $approver) {
                    $newApproverData = array(
                        'stepID' => $newStepID,
                        'approverType' => $approver->approverType,
                        'approverUserID' => $approver->approverUserID,
                        'approverRole' => $approver->approverRole,
                        'approverDepartment' => $approver->approverDepartment,
                        'isBackup' => $approver->isBackup,
                        'notificationOrder' => $approver->notificationOrder,
                        'createdAt' => $config['currentDateTimeFormated'],
                        'Suspended' => 'N'
                    );

                    $DBConn->insert_data('tija_leave_approval_step_approvers', $newApproverData);
                }
            }
        }
    }

    $DBConn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Workflow cloned successfully',
        'newPolicyID' => $newPolicyID
    ]);

} catch (Exception $e) {
    $DBConn->rollBack();

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

