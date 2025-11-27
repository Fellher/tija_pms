<?php
/**
 * Get Organization Chart Data Script
 * Fetches org chart positions for visualization
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

    // Get org chart ID
    $orgChartID = isset($_GET['orgChartID']) ? Utility::clean_string($_GET['orgChartID']) : null;

    if (!$orgChartID) {
        throw new Exception("Org Chart ID is required");
    }

    // Fetch org chart data
    $orgChart = Data::org_charts(['orgChartID' => $orgChartID], true, $DBConn);

    if (!$orgChart) {
        throw new Exception("Organization chart not found");
    }

    // Fetch all positions for this chart
    $positions = Data::org_chart_position_assignments(['orgChartID' => $orgChartID, 'Suspended' => 'N'], false, $DBConn);

    // Return success with chart data
    echo json_encode(array(
        'success' => true,
        'chart' => $orgChart,
        'positions' => $positions ? $positions : array(),
        'message' => 'Organization chart data retrieved successfully'
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

