<?php
/**
 * Get License Data Script
 * Fetches license details for editing
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

    // Get parameters
    $licenseID = isset($_GET['licenseID']) ? Utility::clean_string($_GET['licenseID']) : null;
    $orgDataID = isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : null;

    if (!$licenseID && !$orgDataID) {
        throw new Exception("License ID or Organization ID is required");
    }

    // Build query
    $query = "SELECT * FROM tija_licenses WHERE Suspended = 'N'";
    $params = array();

    if ($licenseID) {
        $query .= " AND licenseID = ?";
        $params[] = array($licenseID, 'i');
    } elseif ($orgDataID) {
        $query .= " AND orgDataID = ?";
        $params[] = array($orgDataID, 'i');
    }

    $query .= " LIMIT 1";

    // Fetch license data
    $licenses = $DBConn->fetch_all_rows($query, $params);

    if (!$licenses || count($licenses) == 0) {
        // No license found - return empty state
        echo json_encode(array(
            'success' => true,
            'hasLicense' => false,
            'license' => null,
            'message' => 'No license assigned to this organization'
        ));
        exit;
    }

    $license = $licenses[0];

    // Decode features if JSON
    if ($license->features) {
        $license->features = json_decode($license->features);
    }

    // Get license type details
    if ($license->licenseType) {
        $licenseType = Admin::license_types(array('licenseTypeCode' => $license->licenseType), true, $DBConn);
        if ($licenseType) {
            $license->licenseTypeName = $licenseType->licenseTypeName;
            $license->licenseTypeIcon = $licenseType->iconClass;
            $license->licenseTypeColor = $licenseType->colorCode;
        }
    }

    // Return success with license data
    echo json_encode(array(
        'success' => true,
        'hasLicense' => true,
        'license' => $license,
        'message' => 'License data retrieved successfully'
    ));

} catch (Exception $e) {
    // Return error
    echo json_encode(array(
        'success' => false,
        'hasLicense' => false,
        'message' => $e->getMessage(),
        'errors' => array($e->getMessage())
    ));
}
?>

