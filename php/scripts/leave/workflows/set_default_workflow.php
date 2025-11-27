<?php
/**
 * Set Workflow as Default for Entity
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
$entityID = isset($input['entityID']) ? intval($input['entityID']) : 0;

if ($policyID <= 0 || $entityID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    $DBConn->begin();

    // Verify policy belongs to entity using Leave class method
    $policy = Leave::leave_approval_policies(
        array('policyID' => $policyID, 'entityID' => $entityID, 'Lapsed' => 'N'),
        true,
        $DBConn
    );

    if (!$policy) {
        throw new Exception('Workflow not found or does not belong to this entity');
    }

    // Remove default flag from all other workflows in this entity
    $removeDefaultQuery = "UPDATE tija_leave_approval_policies
                           SET isDefault = 'N',
                               updatedBy = ?,
                               updatedAt = ?
                           WHERE entityID = ? AND isDefault = 'Y'";
    $DBConn->query($removeDefaultQuery);
    $DBConn->bind('1', $userDetails->ID);
    $DBConn->bind('2', $config['currentDateTimeFormated']);
    $DBConn->bind('3', $entityID);
    $DBConn->execute();

    // Set this workflow as default
    $updateData = array(
        'isDefault' => 'Y',
        'updatedBy' => $userDetails->ID,
        'updatedAt' => $config['currentDateTimeFormated']
    );

    $where = array('policyID' => $policyID);

    if (!$DBConn->update_table('tija_leave_approval_policies', $updateData, $where)) {
        throw new Exception('Failed to set workflow as default');
    }

    $DBConn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Workflow set as default successfully'
    ]);

} catch (Exception $e) {
    $DBConn->rollBack();

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

