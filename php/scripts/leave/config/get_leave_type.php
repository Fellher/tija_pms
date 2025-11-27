<?php
/**
 * Get Leave Type Details - AJAX Endpoint
 * Returns leave type details as JSON for use in forms
 */

session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';

header('Content-Type: application/json');

// Check admin permissions
if (!$isAdmin && !$isValidAdmin && !$isHRManager) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$leaveTypeID = isset($_GET['leaveTypeID']) ? intval($_GET['leaveTypeID']) : 0;

if (!$leaveTypeID) {
    echo json_encode(['success' => false, 'message' => 'Leave type ID is required']);
    exit;
}

try {
    $leaveType = Leave::leave_types(array('leaveTypeID' => $leaveTypeID), true, $DBConn);

    if ($leaveType) {
        echo json_encode([
            'success' => true,
            'leaveType' => [
                'leaveTypeID' => $leaveType->leaveTypeID,
                'leaveTypeName' => $leaveType->leaveTypeName ?? '',
                'leaveTypeCode' => $leaveType->leaveTypeCode ?? '',
                'leaveTypeDescription' => $leaveType->leaveTypeDescription ?? ''
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Leave type not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

