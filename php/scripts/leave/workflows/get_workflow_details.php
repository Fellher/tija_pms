<?php
/**
 * Get Workflow Details for Editing
 * Returns workflow policy and steps data
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

$policyID = isset($_GET['policyID']) ? intval($_GET['policyID']) : 0;

if (!$policyID) {
    echo json_encode(['success' => false, 'message' => 'Policy ID is required']);
    exit;
}

try {
    // Get workflow policy
    $policy = Leave::leave_approval_policies(
        array('policyID' => $policyID, 'Lapsed' => 'N'),
        true,
        $DBConn
    );

    if (!$policy) {
        echo json_encode(['success' => false, 'message' => 'Workflow not found']);
        exit;
    }

    // Get workflow steps
    $steps = Leave::leave_approval_steps(
        array('policyID' => $policyID, 'Suspended' => 'N'),
        false,
        $DBConn
    );

    // Get approvers for each step
    $stepsWithApprovers = array();
    if ($steps && is_array($steps)) {
        foreach ($steps as $step) {
            $approvers = Leave::leave_approval_step_approvers(
                array('stepID' => $step->stepID, 'Suspended' => 'N'),
                false,
                $DBConn
            );

            $stepsWithApprovers[] = array(
                'stepID' => $step->stepID,
                'stepName' => $step->stepName,
                'stepType' => $step->stepType,
                'stepDescription' => $step->stepDescription ?? '',
                'stepOrder' => $step->stepOrder,
                'isRequired' => $step->isRequired,
                'isConditional' => $step->isConditional ?? 'N',
                'escalationDays' => $step->escalationDays ?? 3,
                'approvers' => $approvers ? array_map(function($a) {
                    return array(
                        'approverID' => $a->approverID,
                        'employeeID' => $a->approverUserID ?? null,
                        'approverUserID' => $a->approverUserID ?? null,
                        'approverType' => $a->approverType ?? 'specific'
                    );
                }, $approvers) : array()
            );
        }
    }

    // Sort steps by order
    usort($stepsWithApprovers, function($a, $b) {
        return $a['stepOrder'] <=> $b['stepOrder'];
    });

    echo json_encode([
        'success' => true,
        'workflow' => array(
            'policyID' => $policy->policyID,
            'policyName' => $policy->policyName,
            'policyDescription' => $policy->policyDescription ?? '',
            'entityID' => $policy->entityID,
            'requireAllApprovals' => $policy->requireAllApprovals ?? 'N',
            'allowDelegation' => $policy->allowDelegation ?? 'Y',
            'autoApproveThreshold' => $policy->autoApproveThreshold ?? null,
            'steps' => $stepsWithApprovers
        )
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

