<?php
/**
 * Get Entity Workflow Status
 * Returns workflow statistics for an entity
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

$entityID = isset($_GET['entityID']) ? intval($_GET['entityID']) : 0;

if ($entityID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid entity ID']);
    exit;
}

try {
    // Get all workflows for entity using Leave class method
    $allPolicies = Leave::leave_approval_policies(
        array('entityID' => $entityID, 'Lapsed' => 'N'),
        false,
        $DBConn
    );

    // Calculate statistics from retrieved policies
    $totalPolicies = $allPolicies ? count($allPolicies) : 0;
    $activeWorkflows = 0;
    $defaultWorkflow = 'None';

    if ($allPolicies) {
        foreach ($allPolicies as $policy) {
            if ($policy->isActive === 'Y') {
                $activeWorkflows++;
            }
            if ($policy->isDefault === 'Y') {
                $defaultWorkflow = $policy->policyName;
            }
        }
    }

    echo json_encode([
        'success' => true,
        'totalPolicies' => $totalPolicies,
        'activeWorkflows' => $activeWorkflows,
        'defaultWorkflow' => $defaultWorkflow
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

