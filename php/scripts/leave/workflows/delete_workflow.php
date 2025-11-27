<?php
/**
 * Delete Workflow (Soft Delete)
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
    // Check if workflow is in use
    $checkQuery = "SELECT COUNT(*) as count FROM tija_leave_approval_instances
                   WHERE policyID = ?";
    $checkParams = array(array($policyID, 'i'));
    $results = $DBConn->fetch_all_rows($checkQuery, $checkParams);

    if ($results && $results[0]->count > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Cannot delete workflow. It is currently being used in ' . $results[0]->count . ' leave application(s)'
        ]);
        exit;
    }

    // Soft delete the workflow
    $updateData = array(
        'Lapsed' => 'Y',
        'isActive' => 'N',
        'isDefault' => 'N',
        'updatedBy' => $userDetails->ID,
        'updatedAt' => $config['currentDateTimeFormated']
    );

    $where = array('policyID' => $policyID);

    if ($DBConn->update_table('tija_leave_approval_policies', $updateData, $where)) {
        echo json_encode([
            'success' => true,
            'message' => 'Workflow deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete workflow');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

