<?php
/**
 * Toggle Leave Type/Policy Status (Active/Suspended)
 * Handles activation and suspension of leave types and policies
 */

session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';

// Check admin permissions
if (!$isValidUser || (!$isAdmin && !$isValidAdmin && !$isHRManager)) {
    http_response_code(403);
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(array('success' => false, 'message' => 'Unauthorized access'));
    } else {
        Alert::error("Access denied. Admin privileges required.", true);
        header('Location: ' . $base . 'html/');
    }
    exit;
}

// Check if DBConn exists
if (!isset($DBConn) || !$DBConn) {
    http_response_code(500);
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(array('success' => false, 'message' => 'Database connection not available'));
    } else {
        Alert::error('Database connection not available', true);
        header('Location: ' . $base . 'html/');
    }
    exit;
}

$currentUserID = $userDetails->ID;
$response = array('success' => false, 'message' => '', 'data' => null);

// Handle response type - check if this is an AJAX request or normal form submission
$isAjaxRequest = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
$isAjaxRequest = $isAjaxRequest || (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

try {
    // Get leave type ID from POST or GET
    $leaveTypeID = isset($_POST['leaveTypeID']) ? (int)$_POST['leaveTypeID'] : (isset($_GET['leaveTypeID']) ? (int)$_GET['leaveTypeID'] : 0);
    $policyID = isset($_POST['policyID']) ? (int)$_POST['policyID'] : (isset($_GET['policyID']) ? (int)$_GET['policyID'] : 0);

    // Use policyID if leaveTypeID is not provided
    if (!$leaveTypeID && $policyID) {
        $leaveTypeID = $policyID;
    }

    if (!$leaveTypeID || $leaveTypeID <= 0) {
        throw new Exception('Leave type ID is required');
    }

    // Get current leave type status
    $leaveType = Leave::leave_types(array('leaveTypeID' => $leaveTypeID), true, $DBConn);
    if (!$leaveType) {
        throw new Exception('Leave type not found');
    }

    // Toggle status: Suspended='N' means active, Suspended='Y' means suspended
    $currentStatus = $leaveType->Suspended ?? 'N';
    $newStatus = ($currentStatus === 'Y') ? 'N' : 'Y';
    $statusText = ($newStatus === 'N') ? 'activated' : 'suspended';

    // Update status
    $updateData = array(
        'Suspended' => $newStatus,
        'LastUpdate' => (is_array($config) && isset($config['currentDateTimeFormated'])) ? $config['currentDateTimeFormated'] : ((is_object($config) && isset($config->currentDateTimeFormated)) ? $config->currentDateTimeFormated : date('Y-m-d H:i:s')),
        'LastUpdateByID' => $currentUserID
    );

    if ($DBConn->update_table('tija_leave_types', $updateData, array('leaveTypeID' => $leaveTypeID))) {
        $response['success'] = true;
        $response['message'] = 'Leave type ' . $statusText . ' successfully';
        $response['data'] = array(
            'leaveTypeID' => $leaveTypeID,
            'newStatus' => $newStatus,
            'statusText' => $statusText
        );
    } else {
        throw new Exception('Failed to update leave type status');
    }

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

// Handle response - check if this is an AJAX request or normal form submission
if ($isAjaxRequest) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} else {
    // Normal form submission - redirect based on result
    // Handle config as either array or object
    $siteURL = '';
    if (is_array($config) && isset($config['siteURL'])) {
        $siteURL = $config['siteURL'];
    } elseif (is_object($config) && isset($config->siteURL)) {
        $siteURL = $config->siteURL;
    }
    $baseUrl = !empty($siteURL) ? rtrim((string)$siteURL, '/') : '';
    $redirectBase = $baseUrl . '/html/';

    if ($response['success']) {
        $redirectUrl = $redirectBase . '?s=admin&ss=leave&p=leave_policy_management&action=list';
        if (!empty($response['message'])) {
            $_SESSION['success_message'] = $response['message'];
        }
        header("Location: " . $redirectUrl);
        exit;
    } else {
        $redirectUrl = $redirectBase . '?s=admin&ss=leave&p=leave_policy_management&action=list';
        if (!empty($response['message'])) {
            $_SESSION['error_message'] = $response['message'];
        }
        header("Location: " . $redirectUrl);
        exit;
    }
}
?>

