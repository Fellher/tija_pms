<?php
/**
 * Get Unit Data Script
 * Fetches unit details for editing
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

    // Get unit ID
    $unitID = isset($_GET['unitID']) ? Utility::clean_string($_GET['unitID']) : null;

    if (!$unitID) {
        throw new Exception("Unit ID is required");
    }

    // Fetch unit data
    $unit = Data::units_full(['unitID' => $unitID], true, $DBConn);
    //eror log the $unit data as json string
    // error_log(json_encode($unit));


    if (!$unit  || count($unit) == 0) {
        throw new Exception("Unit not found");
    }

    // Get the first unit (since we're querying by ID)
    $unitData = is_array($unit) ? $unit[0] : $unit;

    // Return success with unit data
    echo json_encode(array(
        'success' => true,
        'unit' => $unitData,
        'message' => 'Unit data retrieved successfully'
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

