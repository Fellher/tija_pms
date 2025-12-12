<?php
/**
 * License Types Management Script
 * Handles CRUD operations for license types
 *
 * @package Tija Practice Management System
 * @subpackage Admin
 * @version 2.0
 */

// Set JSON header first
header('Content-Type: application/json');

// Enable error reporting but don't display errors (they would break JSON)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Catch any fatal errors and return JSON
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Server error: ' . $error['message'],
            'file' => $error['file'],
            'line' => $error['line']
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

$DBConn->begin();
$errors = array();
$success = "";

// Verify admin access
if (!$isValidAdmin && !$isAdmin) {
    $errors[] = "Unauthorized access. Admin privileges required.";
    echo json_encode(array('success' => false, 'errors' => $errors));
    exit;
}

// Get action type
$action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : (isset($_GET['action']) ? Utility::clean_string($_GET['action']) : '');

try {
    switch ($action) {
        case 'save':
            // Get form data
            $licenseTypeID = isset($_POST['licenseTypeID']) && $_POST['licenseTypeID'] != '' ? Utility::clean_string($_POST['licenseTypeID']) : null;
            $licenseTypeName = isset($_POST['licenseTypeName']) && $_POST['licenseTypeName'] != '' ? Utility::clean_string($_POST['licenseTypeName']) : $errors[] = "License type name is required";
            $licenseTypeCode = isset($_POST['licenseTypeCode']) && $_POST['licenseTypeCode'] != '' ? strtolower(Utility::clean_string($_POST['licenseTypeCode'])) : $errors[] = "License type code is required";
            $licenseTypeDescription = isset($_POST['licenseTypeDescription']) ? Utility::clean_string($_POST['licenseTypeDescription']) : '';
            $defaultUserLimit = isset($_POST['defaultUserLimit']) && $_POST['defaultUserLimit'] != '' ? Utility::clean_string($_POST['defaultUserLimit']) : $errors[] = "Default user limit is required";
            $monthlyPrice = isset($_POST['monthlyPrice']) && $_POST['monthlyPrice'] != '' ? Utility::clean_string($_POST['monthlyPrice']) : null;
            $yearlyPrice = isset($_POST['yearlyPrice']) && $_POST['yearlyPrice'] != '' ? Utility::clean_string($_POST['yearlyPrice']) : null;
            $defaultDuration = isset($_POST['defaultDuration']) && $_POST['defaultDuration'] != '' ? Utility::clean_string($_POST['defaultDuration']) : 365;
            $colorCode = isset($_POST['colorCode']) ? Utility::clean_string($_POST['colorCode']) : null;
            $iconClass = isset($_POST['iconClass']) ? Utility::clean_string($_POST['iconClass']) : null;
            $isPopular = isset($_POST['isPopular']) ? Utility::clean_string($_POST['isPopular']) : 'N';

            // Handle features array
            $features = isset($_POST['features']) && is_array($_POST['features']) ? $_POST['features'] : array();
            $featuresJson = json_encode($features);

            // Handle benefits (one per line)
            $benefitsText = isset($_POST['benefits']) ? $_POST['benefits'] : '';
            $benefitsArray = array_filter(array_map('trim', explode("\n", $benefitsText)));
            $benefitsJson = json_encode($benefitsArray);

            // Handle restrictions (one per line)
            $restrictionsText = isset($_POST['restrictions']) ? $_POST['restrictions'] : '';
            $restrictionsArray = array_filter(array_map('trim', explode("\n", $restrictionsText)));
            $restrictionsJson = json_encode($restrictionsArray);

            if (count($errors) > 0) {
                throw new Exception(implode(", ", $errors));
            }

            // Validate license type code format (alphanumeric and underscore only)
            if (!preg_match('/^[a-z0-9_]+$/', $licenseTypeCode)) {
                throw new Exception("License type code must contain only lowercase letters, numbers, and underscores");
            }

            if ($licenseTypeID) {
                // Update existing license type
                $updateData = array(
                    'licenseTypeName' => $licenseTypeName,
                    'licenseTypeCode' => $licenseTypeCode,
                    'licenseTypeDescription' => $licenseTypeDescription,
                    'defaultUserLimit' => $defaultUserLimit,
                    'monthlyPrice' => $monthlyPrice,
                    'yearlyPrice' => $yearlyPrice,
                    'defaultDuration' => $defaultDuration,
                    'features' => $featuresJson,
                    'restrictions' => $restrictionsJson,
                    'benefits' => $benefitsJson,
                    'colorCode' => $colorCode,
                    'iconClass' => $iconClass,
                    'isPopular' => $isPopular,
                    'LastUpdateByID' => $userDetails->ID
                );

                $whereClause = array('licenseTypeID' => $licenseTypeID);

                // Check if code is unique (excluding current record)
                $existing = $DBConn->retrieve_db_table_rows(
                    'tija_license_types',
                    array('licenseTypeID'),
                    array('licenseTypeCode' => $licenseTypeCode)
                );

                if ($existing && count($existing) > 0 && $existing[0]->licenseTypeID != $licenseTypeID) {
                    throw new Exception("License type code already exists. Please use a different code.");
                }

                if ($DBConn->update_table('tija_license_types', $updateData, $whereClause)) {
                    $success = "License type updated successfully";
                    $DBConn->commit();
                } else {
                    throw new Exception("Failed to update license type");
                }
            } else {
                // Check if code already exists
                $existing = $DBConn->retrieve_db_table_rows(
                    'tija_license_types',
                    array('licenseTypeID'),
                    array('licenseTypeCode' => $licenseTypeCode)
                );

                if ($existing && count($existing) > 0) {
                    throw new Exception("License type code already exists. Please use a different code.");
                }

                // Get max display order
                $query = "SELECT COALESCE(MAX(displayOrder), 0) + 1 as nextOrder FROM tija_license_types";
                $result = $DBConn->fetch_all_rows($query, array());
                $displayOrder = $result ? $result[0]->nextOrder : 1;

                // Insert new license type
                $insertData = array(
                    'licenseTypeName' => $licenseTypeName,
                    'licenseTypeCode' => $licenseTypeCode,
                    'licenseTypeDescription' => $licenseTypeDescription,
                    'defaultUserLimit' => $defaultUserLimit,
                    'monthlyPrice' => $monthlyPrice,
                    'yearlyPrice' => $yearlyPrice,
                    'defaultDuration' => $defaultDuration,
                    'features' => $featuresJson,
                    'restrictions' => $restrictionsJson,
                    'benefits' => $benefitsJson,
                    'colorCode' => $colorCode,
                    'iconClass' => $iconClass,
                    'isPopular' => $isPopular,
                    'displayOrder' => $displayOrder,
                    'LastUpdateByID' => $userDetails->ID
                );

                if ($DBConn->insert_into_db_table('tija_license_types', $insertData)) {
                    $success = "License type created successfully";
                    $DBConn->commit();
                } else {
                    throw new Exception("Failed to create license type");
                }
            }
            break;

        case 'get':
            // Get license type details
            $licenseTypeID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : $errors[] = "License type ID is required";

            if (count($errors) > 0) {
                throw new Exception(implode(", ", $errors));
            }

            $licenseType = $DBConn->retrieve_db_table_rows(
                'tija_license_types',
                array('*'),
                array('licenseTypeID' => $licenseTypeID)
            );

            if ($licenseType && count($licenseType) > 0) {
                echo json_encode(array('success' => true, 'licenseType' => $licenseType[0]));
            } else {
                echo json_encode(array('success' => false, 'message' => 'License type not found'));
            }
            exit;

        case 'delete':
            // Soft delete license type
            $licenseTypeID = isset($_POST['licenseTypeID']) ? Utility::clean_string($_POST['licenseTypeID']) : $errors[] = "License type ID is required";

            if (count($errors) > 0) {
                throw new Exception(implode(", ", $errors));
            }

            // Check if license type is in use
            $query = "SELECT COUNT(*) as count FROM tija_licenses WHERE licenseType = (SELECT licenseTypeCode FROM tija_license_types WHERE licenseTypeID = ?)";
            $params = array(array($licenseTypeID, 'i'));
            $result = $DBConn->fetch_all_rows($query, $params);

            if ($result && $result[0]->count > 0) {
                throw new Exception("Cannot delete license type because it is currently in use by " . $result[0]->count . " license(s). Please reassign those licenses first.");
            }

            $updateData = array(
                'Suspended' => 'Y',
                'LastUpdateByID' => $userDetails->ID
            );

            $whereClause = array('licenseTypeID' => $licenseTypeID);

            if ($DBConn->update_table('tija_license_types', $updateData, $whereClause)) {
                $success = "License type deleted successfully";
                $DBConn->commit();
            } else {
                throw new Exception("Failed to delete license type");
            }
            break;

        case 'update_order':
            // Update display order after drag and drop
            $orderData = isset($_POST['orderData']) ? json_decode($_POST['orderData'], true) : $errors[] = "Order data is required";

            if (count($errors) > 0) {
                throw new Exception(implode(", ", $errors));
            }

            foreach ($orderData as $item) {
                $updateData = array('displayOrder' => $item['order']);
                $whereClause = array('licenseTypeID' => $item['id']);
                if (!$DBConn->update_table('tija_license_types', $updateData, $whereClause)) {
                    $errors[] = "Failed to update display order";
                }
            }

            $success = "Display order updated successfully";
            $DBConn->commit();
            break;

        default:
            throw new Exception("Invalid action specified");
    }

    // Return success response
    echo json_encode(array(
        'success' => true,
        'message' => $success
    ));

} catch (Exception $e) {
    $DBConn->rollback();
    $errors[] = $e->getMessage();

    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage(),
        'errors' => $errors
    ));
}
?>

