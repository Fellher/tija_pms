<?php
/**
 * Delete Holiday Handler
 * Soft delete a holiday from the system
 */

session_start();
$base = '../../../../';
require_once $base . 'php/includes.php';

// Check authentication
if (!isset($userDetails->ID) || !$isValidUser) {
    http_response_code(403);
    Alert::error("Access denied", true);
    header('Location: ' . $base . 'html/');
    exit;
}

// Check admin permissions
if (!$isAdmin && !$isValidAdmin && !$isHRManager) {
    Alert::error("Access denied. Admin privileges required.", true);
    header('Location: ' . $base . 'html/');
    exit;
}

$holidayID = $_GET['holidayID'] ?? $_POST['holidayID'] ?? null;

if (!$holidayID) {
    Alert::error("Holiday ID is required", true);
    header('Location: ' . $base . 'html/?s=admin&ss=leave&p=holidays');
    exit;
}

try {
    // Soft delete
    $updateData = [
        'Lapsed' => 'Y',
        'LastUpdate' => $config['currentDateTimeFormated'],
        'LastUpdateByID' => $userDetails->ID
    ];

    $result = $DBConn->update_table('tija_holidays', $updateData, ['holidayID' => $holidayID]);

    if ($result) {
        Alert::success("Holiday deleted successfully", true);
    } else {
        Alert::error("Failed to delete holiday", true);
    }

} catch (Exception $e) {
    Alert::error("Error: " . $e->getMessage(), true);
}

header('Location: ' . $base . 'html/?s=admin&ss=leave&p=holidays');
exit;
?>

