<?php
/**
 * License Management Script
 * Handles CRUD operations for tenant licenses
 *
 * @package Tija Practice Management System
 * @subpackage Admin
 * @version 2.0
 */

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
$details = array();

// Verify admin access
if (!$isValidAdmin && !$isAdmin) {
    $errors[] = "Unauthorized access. Admin privileges required.";
}

// Get action type
$action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : 'save_license';

if (count($errors) == 0) {
    switch ($action) {
        case 'save_license':
        default:
            // Get form data
            $licenseID = isset($_POST['licenseID']) && $_POST['licenseID'] != '' ? Utility::clean_string($_POST['licenseID']) : null;
            $orgDataID = isset($_POST['orgDataID']) && $_POST['orgDataID'] != '' ? Utility::clean_string($_POST['orgDataID']) : $errors[] = "Organization is required";
            $licenseType = isset($_POST['licenseType']) && $_POST['licenseType'] != '' ? Utility::clean_string($_POST['licenseType']) : $errors[] = "License type is required";
            $userLimit = isset($_POST['userLimit']) && $_POST['userLimit'] != '' ? Utility::clean_string($_POST['userLimit']) : $errors[] = "User limit is required";
            $licenseKey = isset($_POST['licenseKey']) && $_POST['licenseKey'] != '' ? Utility::clean_string($_POST['licenseKey']) : null;
            $licenseIssueDate = isset($_POST['licenseIssueDate']) && $_POST['licenseIssueDate'] != '' ? Utility::clean_string($_POST['licenseIssueDate']) : $errors[] = "Issue date is required";
            $licenseExpiryDate = isset($_POST['licenseExpiryDate']) && $_POST['licenseExpiryDate'] != '' ? Utility::clean_string($_POST['licenseExpiryDate']) : $errors[] = "Expiry date is required";
            $licenseStatus = isset($_POST['licenseStatus']) && $_POST['licenseStatus'] != '' ? Utility::clean_string($_POST['licenseStatus']) : 'active';
            $licenseNotes = isset($_POST['licenseNotes']) ? Utility::clean_string($_POST['licenseNotes']) : '';

            // Handle features array
            $features = isset($_POST['features']) && is_array($_POST['features']) ? $_POST['features'] : array();
            $featuresJson = json_encode($features);

            // Generate license key if not provided
            if (!$licenseKey) {
                $licenseKey = generateLicenseKey();
            }

            if (count($errors) > 0) {
                throw new Exception(implode(", ", $errors));
            }

            // Validate dates
            if (strtotime($licenseExpiryDate) <= strtotime($licenseIssueDate)) {
                throw new Exception("Expiry date must be after issue date");
            }

            if ($licenseID) {
                // Update existing license
                $updateData = array(
                    'licenseType' => $licenseType,
                    'licenseKey' => $licenseKey,
                    'userLimit' => $userLimit,
                    'licenseIssueDate' => $licenseIssueDate,
                    'licenseExpiryDate' => $licenseExpiryDate,
                    'licenseStatus' => $licenseStatus,
                    'features' => $featuresJson,
                    'licenseNotes' => $licenseNotes,
                    'LastUpdateByID' => isset($userDetails->ID) ? $userDetails->ID : (isset($userDetails->ID) ? $userDetails->ID : 1)
                );

                $whereClause = array('licenseID' => $licenseID);

                if ($DBConn->update_table('tija_licenses', $updateData, $whereClause)) {
                    $success = "License updated successfully";
                } else {
                    $errors[] = "Failed to update license";
                }
            } else {
                // Check if license already exists for this organization
                $existingLicense = $DBConn->retrieve_db_table_rows(
                    'tija_licenses',
                    array('licenseID'),
                    array('orgDataID' => $orgDataID, 'Suspended' => 'N')
                );

                if ($existingLicense && count($existingLicense) > 0) {
                    $errors[] = "A license already exists for this organization. Please edit the existing license.";
                }

                if (count($errors) == 0) {
                    // Insert new license
                    $insertData = array(
                        'orgDataID' => $orgDataID,
                        'licenseType' => $licenseType,
                        'licenseKey' => $licenseKey,
                        'userLimit' => $userLimit,
                        'currentUsers' => 0,
                        'licenseIssueDate' => $licenseIssueDate,
                        'licenseExpiryDate' => $licenseExpiryDate,
                        'licenseStatus' => $licenseStatus,
                        'features' => $featuresJson,
                        'licenseNotes' => $licenseNotes,
                        'LastUpdateByID' => isset($userDetails->ID) ? $userDetails->ID : (isset($userDetails->ID) ? $userDetails->ID : 1)
                    );

                    if ($DBConn->insert_data('tija_licenses', $insertData)) {
                        $success = "License created successfully";
                    } else {
                        $errors[] = "Failed to create license";
                    }
                }
            }
            break;
    }
}

/**
 * Generate a unique license key
 * Format: XXXX-XXXX-XXXX-XXXX
 */
function generateLicenseKey() {
    global $DBConn;

    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $attempts = 0;
    $maxAttempts = 10;

    do {
        $key = '';
        for ($i = 0; $i < 4; $i++) {
            if ($i > 0) $key .= '-';
            for ($j = 0; $j < 4; $j++) {
                $key .= $chars[rand(0, strlen($chars) - 1)];
            }
        }

        // Check if key already exists
        $existing = $DBConn->retrieve_db_table_rows(
            'tija_licenses',
            array('licenseID'),
            array('licenseKey' => $key)
        );

        $attempts++;

    } while ($existing && count($existing) > 0 && $attempts < $maxAttempts);

    if ($attempts >= $maxAttempts) {
        $errors[] = "Failed to generate unique license key";
        return 'TEMP-' . time();
    }

    return $key;
}

// Prepare messages and redirect
if (count($errors) == 0) {
    $DBConn->commit();
    $messages = array(array('Text' => "{$success}", 'Type' => 'success'));
} else {
    $DBConn->rollback();
    $messages = array_map(function($error){
        return array('Text' => $error, 'Type' => 'danger');
    }, $errors);
}

$_SESSION['FlashMessages'] = serialize($messages);

// Determine return URL
$returnURL = Utility::returnURL($_SESSION['returnURL'], 's=core&ss=admin&p=home&tab=licenses');

// Redirect back
header("Location: {$base}html/{$returnURL}");
exit;
?>

