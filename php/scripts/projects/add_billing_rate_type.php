<?php
/**
 * Add Billing Rate Type (AJAX)
 * Creates a new billing rate type inline from the billing rate modal
 *
 * @package Tija Practice Management System
 * @subpackage Projects - Billing
 * @version 2.0
 */

session_start();
$base = '../../../';
set_include_path($base);

header('Content-Type: application/json');

// Include dependencies
require_once 'php/includes.php';

error_log("=== Add Billing Rate Type Started ===");
error_log("POST Data: " . print_r($_POST, true));

$response = ['success' => false, 'message' => '', 'billingRateTypeID' => null];

try {
    // Validate user is logged in
    if (!isset($_SESSION['ID']) && !isset($userDetails->ID)) {
        throw new Exception("You must be logged in to perform this action");
    }

    // Get current user ID
    $currentUserID = $_SESSION['ID'] ?? $userDetails->ID ?? null;
    if (!$currentUserID) {
        throw new Exception("Unable to identify current user");
    }

    // Get parameters
    $billingRateTypeName = isset($_POST['billingRateTypeName']) && !empty($_POST['billingRateTypeName'])
        ? Utility::clean_string($_POST['billingRateTypeName']) : null;
    $billingRateTypeDescription = isset($_POST['billingRateTypeDescription']) && !empty($_POST['billingRateTypeDescription'])
        ? Utility::clean_string($_POST['billingRateTypeDescription']) : null;

    // Validate required fields
    if (!$billingRateTypeName) {
        throw new Exception("Rate type name is required");
    }

    // Check if rate type with same name already exists
    $existingRateType = Projects::billing_rate_type(['billingRateTypeName' => $billingRateTypeName], true, $DBConn);
    if ($existingRateType) {
        throw new Exception("A rate type with this name already exists");
    }

    $DBConn->begin();

    // Prepare data for insertion
    $details = [
        'billingRateTypeName' => $billingRateTypeName,
        'billingRateTypeDescription' => $billingRateTypeDescription ? $billingRateTypeDescription : '',
        'LastUpdate' => date('Y-m-d H:i:s'),
        'LastUpdateByID' => $currentUserID,
        'Suspended' => 'N',
        'Lapsed' => 'N'
    ];

    // Insert new rate type
    $insertResult = $DBConn->insert_data('tija_billing_rate_types', $details);

    if (!$insertResult) {
        $errorInfo = $DBConn->errorInfo();
        throw new Exception("Failed to add rate type: " . ($errorInfo[2] ?? 'Unknown database error'));
    }

    $billingRateTypeID = $DBConn->lastInsertId();

    $DBConn->commit();

    $response['success'] = true;
    $response['message'] = "Rate type added successfully";
    $response['billingRateTypeID'] = $billingRateTypeID;
    $response['billingRateTypeName'] = $billingRateTypeName;

    error_log("Billing rate type added successfully: ID = {$billingRateTypeID}");

} catch (Exception $e) {
    error_log("=== EXCEPTION CAUGHT ===");
    error_log("Exception: " . $e->getMessage());
    error_log("Trace: " . $e->getTraceAsString());

    if ($DBConn) {
        try {
            $DBConn->rollback();
            error_log("Transaction rolled back");
        } catch (Exception $rollbackError) {
            error_log("Rollback error: " . $rollbackError->getMessage());
        }
    }

    $response['message'] = $e->getMessage();
    $response['success'] = false;
}

error_log("Final response: " . print_r($response, true));

echo json_encode($response);
exit;
?>

