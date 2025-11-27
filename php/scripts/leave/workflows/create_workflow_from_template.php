<?php
/**
 * Create Workflow from Template with Approver Configuration
 * Creates a new workflow from template and assigns specific approvers
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
$templateID = isset($_POST['templateID']) ? intval($_POST['templateID']) : 0;
$entityID = isset($_POST['entityID']) ? intval($_POST['entityID']) : 0;
$orgDataID = isset($_POST['orgDataID']) ? intval($_POST['orgDataID']) : 0;
$workflowName = isset($_POST['workflowName']) ? Utility::clean_string($_POST['workflowName']) : '';
$description = isset($_POST['description']) ? Utility::clean_string($_POST['description']) : '';
$allowDelegation = isset($_POST['allowDelegation']) ? 'Y' : 'N';
$autoApproveThreshold = isset($_POST['autoApproveThreshold']) && !empty($_POST['autoApproveThreshold']) ? intval($_POST['autoApproveThreshold']) : null;

if ($templateID <= 0 || $entityID <= 0 || empty($workflowName)) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    // Start transaction
    $DBConn->begin();

    // Get template details
    $templateQuery = "SELECT * FROM tija_leave_workflow_templates WHERE templateID = ?";
    $templateParams = array(array($templateID, 'i'));
    $templates = $DBConn->fetch_all_rows($templateQuery, $templateParams);

    if (!$templates || count($templates) === 0) {
        throw new Exception('Template not found');
    }

    $template = $templates[0];

    // Create new policy
    $policyData = array(
        'entityID' => $entityID,
        'orgDataID' => $orgDataID,
        'policyName' => $workflowName,
        'policyDescription' => !empty($description) ? $description : $template->templateDescription,
        'isActive' => 'N', // Create as inactive initially
        'isDefault' => 'N',
        'requireAllApprovals' => 'N',
        'allowDelegation' => $allowDelegation,
        'autoApproveThreshold' => $autoApproveThreshold,
        'createdBy' => $userDetails->ID,
        'createdAt' => $config['currentDateTimeFormated'],
        'Suspended' => 'N',
        'Lapsed' => 'N'
    );

    if (!$DBConn->insert_data('tija_leave_approval_policies', $policyData)) {
        throw new Exception('Failed to create policy');
    }

    $newPolicyID = $DBConn->lastInsertId();

    // Get template steps
    $stepsQuery = "SELECT * FROM tija_leave_workflow_template_steps
                   WHERE templateID = ?
                   ORDER BY stepOrder";
    $stepsParams = array(array($templateID, 'i'));
    $templateSteps = $DBConn->fetch_all_rows($stepsQuery, $stepsParams);

    if ($templateSteps && count($templateSteps) > 0) {
        // Insert each step
        foreach ($templateSteps as $templateStep) {
            $stepData = array(
                'policyID' => $newPolicyID,
                'stepOrder' => $templateStep->stepOrder,
                'stepName' => $templateStep->stepName,
                'stepType' => $templateStep->stepType,
                'stepDescription' => $templateStep->stepDescription,
                'isRequired' => $templateStep->isRequired,
                'isConditional' => $templateStep->isConditional,
                'conditionType' => $templateStep->conditionType,
                'conditionValue' => $templateStep->conditionValue,
                'escalationDays' => $templateStep->escalationDays,
                'notifyOnPending' => 'Y',
                'notifyOnApprove' => 'Y',
                'notifyOnReject' => 'Y',
                'createdAt' => $config['currentDateTimeFormated'],
                'Suspended' => 'N'
            );

            if (!$DBConn->insert_data('tija_leave_approval_steps', $stepData)) {
                throw new Exception('Failed to create workflow step');
            }

            $newStepID = $DBConn->lastInsertId();

            // Check if specific approver was selected for this step (Primary)
            $stepApproverKey = 'stepApprover_' . $templateStep->stepOrder;
            if (isset($_POST[$stepApproverKey]) && !empty($_POST[$stepApproverKey])) {
                $approverUserID = intval($_POST[$stepApproverKey]);

                // Insert primary approver
                $approverData = array(
                    'stepID' => $newStepID,
                    'approverType' => 'user',
                    'approverUserID' => $approverUserID,
                    'isBackup' => 'N',
                    'notificationOrder' => 1,
                    'createdAt' => $config['currentDateTimeFormated'],
                    'Suspended' => 'N'
                );

                if (!$DBConn->insert_data('tija_leave_approval_step_approvers', $approverData)) {
                    throw new Exception('Failed to assign primary approver');
                }

                // Check if backup approver was also selected
                $stepBackupApproverKey = 'stepApproverBackup_' . $templateStep->stepOrder;
                if (isset($_POST[$stepBackupApproverKey]) && !empty($_POST[$stepBackupApproverKey])) {
                    $backupApproverUserID = intval($_POST[$stepBackupApproverKey]);

                    // Ensure backup is different from primary
                    if ($backupApproverUserID !== $approverUserID) {
                        // Insert backup approver (parallel approval - either can approve)
                        $backupApproverData = array(
                            'stepID' => $newStepID,
                            'approverType' => 'user',
                            'approverUserID' => $backupApproverUserID,
                            'isBackup' => 'Y',
                            'notificationOrder' => 1, // Same order = parallel approval
                            'createdAt' => $config['currentDateTimeFormated'],
                            'Suspended' => 'N'
                        );

                        if (!$DBConn->insert_data('tija_leave_approval_step_approvers', $backupApproverData)) {
                            throw new Exception('Failed to assign backup approver');
                        }
                    }
                }
            }
        }
    }

    // Update template usage count
    $updateQuery = "UPDATE tija_leave_workflow_templates
                    SET usageCount = usageCount + 1
                    WHERE templateID = ?";
    $DBConn->query($updateQuery);
    $DBConn->bind('1', $templateID);
    $DBConn->execute();

    // Commit transaction
    $DBConn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Workflow created successfully',
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

