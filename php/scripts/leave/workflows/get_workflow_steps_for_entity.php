<?php
/**
 * Get Workflow Steps for Entity
 *
 * Retrieves the active approval workflow steps and approvers for an entity
 * Falls back to legacy workflow if no active workflow is configured
 */

session_start();
$base = '../../../../';
set_include_path($base);
include 'php/includes.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!$isValidUser) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);
    $entityId = isset($input['entityId']) ? Utility::clean_string($input['entityId']) : '';
    $employeeId = isset($input['employeeId']) ? Utility::clean_string($input['employeeId']) : '';

    if (empty($entityId)) {
        echo json_encode(['success' => false, 'message' => 'Entity ID is required']);
        exit;
    }

    // Get active approval workflow for entity
    $activeWorkflow = Leave::get_active_approval_workflow($entityId, $DBConn);

    if ($activeWorkflow) {
        // Active workflow found - get steps and approvers
        $policyID = is_object($activeWorkflow) ? $activeWorkflow->policyID : (is_array($activeWorkflow) ? $activeWorkflow['policyID'] : null);
        $policyName = is_object($activeWorkflow) ? $activeWorkflow->policyName : (is_array($activeWorkflow) ? $activeWorkflow['policyName'] : 'Approval Workflow');

        if ($policyID) {
            // Get all approvers from workflow
            $approvers = Leave::get_workflow_approvers($policyID, $DBConn);

            // Get workflow steps
            $steps = Leave::leave_approval_steps(
                array('policyID' => $policyID, 'Suspended' => 'N'),
                false,
                $DBConn
            );

            // Group approvers by step order
            $stepsData = array();
            $stepMap = array();

            if ($steps) {
                foreach ($steps as $step) {
                    $step = is_object($step) ? (array)$step : $step;
                    $stepOrder = isset($step['stepOrder']) ? (int)$step['stepOrder'] : 0;
                    $stepID = isset($step['stepID']) ? (int)$step['stepID'] : null;

                    if (!isset($stepMap[$stepOrder])) {
                        $stepMap[$stepOrder] = array(
                            'stepOrder' => $stepOrder,
                            'stepID' => $stepID,
                            'stepName' => isset($step['stepName']) ? $step['stepName'] : 'Step ' . $stepOrder,
                            'stepDescription' => isset($step['stepDescription']) ? $step['stepDescription'] : '',
                            'approvers' => array()
                        );
                    }
                }
            }

            // Add approvers to their respective steps
            foreach ($approvers as $approver) {
                $stepOrder = isset($approver['stepOrder']) ? (int)$approver['stepOrder'] : 0;
                if (isset($stepMap[$stepOrder])) {
                    $isBackup = isset($approver['isBackup']) && strtoupper($approver['isBackup']) === 'Y';
                    $approverData = array(
                        'approverUserID' => isset($approver['approverUserID']) ? (int)$approver['approverUserID'] : null,
                        'name' => isset($approver['approverName']) ? $approver['approverName'] : 'Not assigned',
                        'email' => isset($approver['approverEmail']) ? $approver['approverEmail'] : '',
                        'isBackup' => $isBackup
                    );
                    $stepMap[$stepOrder]['approvers'][] = $approverData;
                }
            }

            // Convert map to array and sort by step order
            $stepsData = array_values($stepMap);
            usort($stepsData, function($a, $b) {
                return $a['stepOrder'] - $b['stepOrder'];
            });

            // Check if HR manager is in workflow
            $hasHrManagerInWorkflow = false;
            foreach ($stepsData as $step) {
                // Check step name for HR
                if (isset($step['stepName']) && stripos($step['stepName'], 'hr') !== false) {
                    $hasHrManagerInWorkflow = true;
                    break;
                }
                // Also check approvers
                foreach ($step['approvers'] as $approver) {
                    if (!empty($approver['approverUserID'])) {
                        $isHr = Employee::is_hr_manager($approver['approverUserID'], $DBConn, $entityId);
                        if ($isHr) {
                            $hasHrManagerInWorkflow = true;
                            break 2;
                        }
                    }
                }
            }

            // Get HR managers for entity (for notification purposes)
            $hrManagers = array();
            if (!$hasHrManagerInWorkflow) {
                $hrManagersList = Employee::get_hr_managers_for_entity($entityId, $DBConn);
                foreach ($hrManagersList as $hrManager) {
                    $hrManagerObj = is_object($hrManager) ? (array)$hrManager : $hrManager;
                    $hrManagers[] = array(
                        'approverUserID' => isset($hrManagerObj['ID']) ? (int)$hrManagerObj['ID'] : null,
                        'name' => isset($hrManagerObj['FirstName']) && isset($hrManagerObj['Surname'])
                            ? ($hrManagerObj['FirstName'] . ' ' . $hrManagerObj['Surname'])
                            : 'HR Manager',
                        'email' => isset($hrManagerObj['Email']) ? $hrManagerObj['Email'] : '',
                        'isBackup' => false
                    );
                }
            }

            echo json_encode([
                'success' => true,
                'hasWorkflow' => true,
                'workflow' => array(
                    'policyID' => $policyID,
                    'policyName' => $policyName,
                    'steps' => $stepsData,
                    'hrManagers' => $hrManagers // For notification purposes
                )
            ]);
            exit;
        }
    }

    // No active workflow found - return legacy workflow
    if (empty($employeeId)) {
        // Try to get employee ID from session
        $employeeId = isset($userDetails->ID) ? $userDetails->ID : null;
    }

    if ($employeeId) {
        $legacyWorkflow = Leave::get_approval_workflow($employeeId, $entityId, $DBConn);
        $legacySteps = array();

        foreach ($legacyWorkflow as $step) {
            $approver = Employee::employees(array('ID' => $step->approverID), true, $DBConn);
            $approverName = 'Not assigned';
            $approverEmail = '';

            if ($approver) {
                $approverName = $approver->FirstName . ' ' . $approver->Surname;
                $approverEmail = isset($approver->Email) ? $approver->Email : '';
            }

            $stepName = 'Manager Approval';
            if ($step->approvalType === 'direct_report') {
                $stepName = 'Direct Supervisor';
            } elseif ($step->approvalType === 'department_head') {
                $stepName = 'Department Head';
            } elseif ($step->approvalType === 'hr_manager') {
                $stepName = 'HR Manager';
            }

            $legacySteps[] = array(
                'stepOrder' => $step->approvalLevel,
                'stepID' => null,
                'stepName' => $stepName,
                'stepDescription' => '',
                'approvers' => array(array(
                    'approverUserID' => $step->approverID,
                    'name' => $approverName,
                    'email' => $approverEmail,
                    'isBackup' => false
                ))
            );
        }

        // Get HR managers if not in legacy workflow
        $hasHrInLegacy = false;
        foreach ($legacySteps as $step) {
            if ($step['stepName'] === 'HR Manager') {
                $hasHrInLegacy = true;
                break;
            }
        }

        $hrManagers = array();
        if (!$hasHrInLegacy) {
            $hrManagersList = Employee::get_hr_managers_for_entity($entityId, $DBConn);
            foreach ($hrManagersList as $hrManager) {
                $hrManagerObj = is_object($hrManager) ? (array)$hrManager : $hrManager;
                $hrManagers[] = array(
                    'approverUserID' => isset($hrManagerObj['ID']) ? (int)$hrManagerObj['ID'] : null,
                    'name' => isset($hrManagerObj['FirstName']) && isset($hrManagerObj['Surname'])
                        ? ($hrManagerObj['FirstName'] . ' ' . $hrManagerObj['Surname'])
                        : 'HR Manager',
                    'email' => isset($hrManagerObj['Email']) ? $hrManagerObj['Email'] : '',
                    'isBackup' => false
                );
            }
        }

        echo json_encode([
            'success' => true,
            'hasWorkflow' => false,
            'workflow' => array(
                'policyID' => null,
                'policyName' => 'Default Workflow',
                'steps' => $legacySteps,
                'hrManagers' => $hrManagers
            )
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Employee ID is required for legacy workflow'
        ]);
    }

} catch (Exception $e) {
    error_log('Get workflow steps error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while retrieving workflow steps',
        'error' => $e->getMessage()
    ]);
}
?>

