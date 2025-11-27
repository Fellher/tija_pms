<?php
/**
 * Get Business Unit Data Script
 * Fetches business unit details for editing
 *
 * @package Tija Practice Management System
 * @subpackage Admin
 * @version 1.0
 */

// Set JSON header
header('Content-Type: application/json');

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Server error: ' . $error['message']
        ]);
    }
});

session_start();
$base = '../../../../';
set_include_path($base);

include_once 'php/includes.php';
include_once 'php/class_autoload.php';
include_once 'php/config/config.inc.php';
include_once 'php/scripts/db_connect.php';

$errors = array();

try {
    // Verify admin access
    if (!$isValidAdmin && !$isAdmin) {
        throw new Exception("Unauthorized access. Admin privileges required.");
    }

    // Get business unit ID
    $businessUnitID = isset($_GET['businessUnitID']) ? Utility::clean_string($_GET['businessUnitID']) : null;

    if (!$businessUnitID) {
        throw new Exception("Business Unit ID is required");
    }

    // Fetch business unit data
    $businessUnit = Data::business_units(['businessUnitID' => $businessUnitID], true, $DBConn);

    if (!$businessUnit) {
        throw new Exception("Business unit not found");
    }

    // Return success with business unit data
    echo json_encode(array(
        'success' => true,
        'businessUnit' => $businessUnit,
        'message' => 'Business unit data retrieved successfully'
    ));

} catch (Exception $e) {
    // Return error
    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage(),
        'errors' => array($e->getMessage())
    ));
}
?>

