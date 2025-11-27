<?php
/**
 * Toggle Workflow Status (Active/Inactive)
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

if ($policyID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid policy ID']);
    exit;
}

try {
    // Get current status using Leave class method
    $policy = Leave::leave_approval_policies(
        array('policyID' => $policyID, 'Lapsed' => 'N'),
        true,
        $DBConn
    );

    if (!$policy) {
        throw new Exception('Workflow not found');
    }

    $currentStatus = $policy->isActive;
    $newStatus = ($currentStatus === 'Y') ? 'N' : 'Y';

    // Update status
    $updateData = array(
        'isActive' => $newStatus,
        'updatedBy' => $userDetails->ID,
        'updatedAt' => $config['currentDateTimeFormated']
    );

    $where = array('policyID' => $policyID);

    if ($DBConn->update_table('tija_leave_approval_policies', $updateData, $where)) {
        echo json_encode([
            'success' => true,
            'message' => 'Workflow status updated',
            'newStatus' => $newStatus
        ]);
    } else {
        throw new Exception('Failed to update workflow status');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

