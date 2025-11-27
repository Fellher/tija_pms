<?php
/**
 * Get Organization Data Script
 * Fetches organization details for editing
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

    // Get organization ID
    $orgDataID = isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : null;

    if (!$orgDataID) {
        throw new Exception("Organization ID is required");
    }

    // Fetch organization data
    $organisation = Admin::org_data(array('orgDataID' => $orgDataID), true, $DBConn);

    if (!$organisation) {
        throw new Exception("Organization not found");
    }

    // Return success with organization data
    echo json_encode(array(
        'success' => true,
        'organisation' => $organisation,
        'message' => 'Organization data retrieved successfully'
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

