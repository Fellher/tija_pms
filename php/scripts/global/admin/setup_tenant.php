<?php
/**
 * Tenant Setup Wizard - Complete Setup Handler
 * Handles creation of organization, entities, license, and administrators
 *
 * @package Tija Practice Management System
 * @subpackage Admin
 * @version 1.0
 * @author Tija Systems
 * @created October 25, 2025
 */

// Set JSON header for proper response
header('Content-Type: application/json');

// Error handling configuration
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Server Error: ' . $error['message'],
            'file' => $error['file'],
            'line' => $error['line']
        ]);
    }
});

session_start();
$base = '../../../../';
set_include_path($base);

// Include required files
include_once 'php/includes.php';
include_once 'php/class_autoload.php';
include_once 'php/config/config.inc.php';
include_once 'php/scripts/db_connect.php';

try {
    // Verify admin access
    if (!isset($isValidAdmin) || !$isValidAdmin) {
        throw new Exception("Unauthorized access. Admin privileges required.");
    }

    // Get current user ID
    $userID = isset($_SESSION['userID']) ? intval($_SESSION['userID']) : (isset($UserID) ? intval($UserID) : 1);

    if (empty($userID)) {
        throw new Exception("User ID not found in session");
    }

    // Start database transaction
    $DBConn->begin();

    $errors = array();
    $orgDataID = null;
    $licenseID = null;
    $createdEntities = array();
    $createdAdmins = array();

    // ============================================================================
    // STEP 1: CREATE ORGANIZATION
    // ============================================================================

    $orgName = isset($_POST['orgName']) ? Utility::clean_string($_POST['orgName']) : null;
    $registrationNumber = isset($_POST['registrationNumber']) ? Utility::clean_string($_POST['registrationNumber']) : null;
    $orgPIN = isset($_POST['orgPIN']) ? Utility::clean_string($_POST['orgPIN']) : null;
    $industrySectorID = isset($_POST['industrySectorID']) ? Utility::clean_string($_POST['industrySectorID']) : null;
    $countryID = isset($_POST['countryID']) ? Utility::clean_string($_POST['countryID']) : null;
    $orgCity = isset($_POST['orgCity']) ? Utility::clean_string($_POST['orgCity']) : null;
    $orgPostalCode = isset($_POST['orgPostalCode']) ? Utility::clean_string($_POST['orgPostalCode']) : '';
    $orgAddress = isset($_POST['orgAddress']) ? Utility::clean_string($_POST['orgAddress']) : '';
    $orgEmail = isset($_POST['orgEmail']) ? Utility::clean_string($_POST['orgEmail']) : null;
    $orgPhoneNumber1 = isset($_POST['orgPhoneNumber1']) ? Utility::clean_string($_POST['orgPhoneNumber1']) : null;
    $numberOfEmployees = isset($_POST['numberOfEmployees']) ? Utility::clean_string($_POST['numberOfEmployees']) : 0;
    $costCenterEnabled = isset($_POST['costCenterEnabled']) ? Utility::clean_string($_POST['costCenterEnabled']) : 'N';

    // Validate required organization fields
    if (empty($orgName)) {
        $errors[] = "Organization name is required";
    }
    if (empty($registrationNumber)) {
        $errors[] = "Registration number is required";
    }
    if (empty($orgPIN)) {
        $errors[] = "Tax PIN/Number is required";
    }
    if (empty($industrySectorID)) {
        $errors[] = "Industry sector is required";
    }
    if (empty($countryID)) {
        $errors[] = "Country is required";
    }
    if (empty($orgEmail)) {
        $errors[] = "Organization email is required";
    } elseif (!filter_var($orgEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    if (empty($orgPhoneNumber1)) {
        $errors[] = "Phone number is required";
    }

    // Check if organization already exists
    if (empty($errors)) {
        $existingOrg = $DBConn->retrieve_db_table_rows(
            'tija_organisation_data',
            array('orgDataID'),
            array('registrationNumber' => $registrationNumber),
            true
        );

        if ($existingOrg) {
            $errors[] = "An organization with registration number '{$registrationNumber}' already exists";
        }
    }

    // Create organization if no errors
    if (empty($errors)) {
        $orgData = array(
            'orgName' => $orgName,
            'registrationNumber' => $registrationNumber,
            'orgPIN' => $orgPIN,
            'industrySectorID' => $industrySectorID,
            'countryID' => $countryID,
            'orgCity' => $orgCity,
            'orgPostalCode' => $orgPostalCode,
            'orgAddress' => $orgAddress,
            'orgEmail' => $orgEmail,
            'orgPhoneNumber1' => $orgPhoneNumber1,
            'numberOfEmployees' => $numberOfEmployees,
            'costCenterEnabled' => $costCenterEnabled,
            'DateAdded' => date('Y-m-d H:i:s')
        );

        if (!$DBConn->insert_data('tija_organisation_data', $orgData)) {
            $errors[] = "Failed to create organization: " . $DBConn->get_error();
        } else {
            $orgDataID = $DBConn->lastInsertId();
        }
    }

    // ============================================================================
    // STEP 2: CREATE ENTITIES
    // ============================================================================

    if (empty($errors) && $orgDataID) {
        $entityNames = isset($_POST['entity_name']) ? $_POST['entity_name'] : array();
        $entityRegistrations = isset($_POST['entity_registration']) ? $_POST['entity_registration'] : array();
        $entityTypeIDs = isset($_POST['entityTypeID']) ? $_POST['entityTypeID'] : array();

        if (!empty($entityNames) && is_array($entityNames)) {
            foreach ($entityNames as $index => $entityName) {
                $entityName = Utility::clean_string($entityName);
                $entityRegistration = isset($entityRegistrations[$index]) ? Utility::clean_string($entityRegistrations[$index]) : '';
                $entityTypeID = isset($entityTypeIDs[$index]) ? intval($entityTypeIDs[$index]) : 1;

                if (!empty($entityName)) {
                    // Validate entity type
                    if (empty($entityTypeID) || $entityTypeID < 1) {
                        $errors[] = "Entity type is required for entity '{$entityName}'";
                        continue;
                    }

                    $entityData = array(
                        'entityName' => $entityName,
                        'entityParentID' => 0, // Root level entity
                        'orgDataID' => $orgDataID,
                        'registrationNumber' => $entityRegistration,
                        'entityEmail' => $orgEmail, // Default to org email
                        'entityPhoneNumber' => $orgPhoneNumber1, // Default to org phone
                        'entityCity' => $orgCity,
                        'entityCountry' => $countryID,
                        'entityTypeID' => $entityTypeID,
                        'DateAdded' => date('Y-m-d H:i:s')
                    );

                    if ($DBConn->insert_data('tija_entities', $entityData)) {
                        $createdEntities[] = array(
                            'entityID' => $DBConn->lastInsertId(),
                            'entityName' => $entityName
                        );
                    } else {
                        $errors[] = "Failed to create entity '{$entityName}': " . $DBConn->get_error();
                    }
                }
            }
        }
    }

    // ============================================================================
    // STEP 3: CREATE LICENSE
    // ============================================================================

    if (empty($errors) && $orgDataID) {
        $licenseTypeID = isset($_POST['licenseType']) ? intval($_POST['licenseType']) : null;
        $userLimit = isset($_POST['userLimit']) ? intval($_POST['userLimit']) : 50;
        $licenseIssueDate = isset($_POST['licenseIssueDate']) ? Utility::clean_string($_POST['licenseIssueDate']) : date('Y-m-d');
        $licenseExpiryDate = isset($_POST['licenseExpiryDate']) ? Utility::clean_string($_POST['licenseExpiryDate']) : date('Y-m-d', strtotime('+1 year'));
        $licenseNotes = isset($_POST['licenseNotes']) ? Utility::clean_string($_POST['licenseNotes']) : '';

        // Validate license type
        if (empty($licenseTypeID)) {
            $errors[] = "License type is required";
        }

        // Fetch license type details from database
        $licenseTypeDetails = null;
        if (!empty($licenseTypeID)) {
            $licenseTypeDetails = Admin::license_types(array('licenseTypeID' => $licenseTypeID, 'Suspended' => 'N'), true, $DBConn);

            if (!$licenseTypeDetails) {
                $errors[] = "Invalid license type selected";
            }
        }

        if (empty($errors) && $licenseTypeDetails) {
            // Generate unique license key
            $licenseKey = generateLicenseKey($orgName, $licenseTypeDetails->licenseTypeCode);

            // Determine license status and validate ENUM value
            $licenseTypeCode = strtolower($licenseTypeDetails->licenseTypeCode);

            // Map license type code to valid ENUM values in tija_licenses table
            $validEnumValues = array('trial', 'basic', 'standard', 'premium', 'enterprise');
            $licenseType = in_array($licenseTypeCode, $validEnumValues) ? $licenseTypeCode : 'standard';

            $licenseStatus = ($licenseTypeCode === 'trial' || $licenseTypeCode === 'tri') ? 'trial' : 'active';

            // Get features from license type (decode JSON if stored as JSON)
            $features = array();
            if (!empty($licenseTypeDetails->features)) {
                $featuresDecoded = json_decode($licenseTypeDetails->features, true);
                if (is_array($featuresDecoded)) {
                    $features = $featuresDecoded;
                } else {
                    // If not JSON, treat as comma-separated string
                    $features = array_map('trim', explode(',', $licenseTypeDetails->features));
                }
            } else {
                // Default features if not specified
                $features = array('payroll', 'leave', 'attendance', 'reports');
            }

            $licenseData = array(
                'orgDataID' => $orgDataID,
                'licenseType' => $licenseType, // Use validated ENUM value
                'licenseKey' => $licenseKey,
                'userLimit' => $userLimit,
                'currentUsers' => 0,
                'licenseIssueDate' => $licenseIssueDate,
                'licenseExpiryDate' => $licenseExpiryDate,
                'licenseStatus' => $licenseStatus,
                'features' => json_encode($features),
                'licenseNotes' => $licenseNotes,
                'DateAdded' => date('Y-m-d H:i:s')
            );

            if ($DBConn->insert_data('tija_licenses', $licenseData)) {
                $licenseID = $DBConn->lastInsertId();
            } else {
                $errors[] = "Failed to create license: " . $DBConn->get_error();
            }
        }
    }

    // ============================================================================
    // STEP 4: CREATE ADMINISTRATORS
    // ============================================================================

    if (empty($errors) && $orgDataID) {
        $adminFirstNames = isset($_POST['admin_first_name']) ? $_POST['admin_first_name'] : array();
        $adminLastNames = isset($_POST['admin_last_name']) ? $_POST['admin_last_name'] : array();
        $adminEmails = isset($_POST['admin_email']) ? $_POST['admin_email'] : array();
        $adminTypes = isset($_POST['admin_type']) ? $_POST['admin_type'] : array();
        $adminSendEmails = isset($_POST['admin_send_email']) ? $_POST['admin_send_email'] : array();

        if (!empty($adminFirstNames) && is_array($adminFirstNames)) {
            foreach ($adminFirstNames as $index => $firstName) {
                $firstName = Utility::clean_string($firstName);
                $surname = isset($adminLastNames[$index]) ? Utility::clean_string($adminLastNames[$index]) : '';
                $adminEmail = isset($adminEmails[$index]) ? Utility::clean_string($adminEmails[$index]) : '';
                $adminTypeID = isset($adminTypes[$index]) ? intval($adminTypes[$index]) : 2; // Default to System Admin
                $sendEmail = isset($adminSendEmails[$index]) ? true : false;

                if (!empty($firstName) && !empty($adminEmail)) {
                    // Validate email
                    if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = "Invalid email format for admin: {$adminEmail}";
                        continue;
                    }

                    // Check if user already exists
                    $existingUser = $DBConn->retrieve_db_table_rows(
                        'people',
                        array('ID'),
                        array('Email' => $adminEmail, ),
                        true
                    );

                    $peopleID = null;

                    if ($existingUser) {
                        $peopleID = $existingUser->ID;
                    } else {
                        // Create new user
                        $tempPassword = bin2hex(random_bytes(8)); // Generate random password
                        $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);

                        $userData = array(
                            'FirstName' => $firstName,
                            'Surname' => $surname,
                            'Email' => $adminEmail,
                            'password' => $hashedPassword,
                            'DateAdded' => date('Y-m-d H:i:s'),


                        );

                        if ($DBConn->insert_data('people', $userData)) {
                            $peopleID = $DBConn->lastInsertId();

                            // Store info for email notification if sendEmail is checked
                            if ($sendEmail) {
                                $createdAdmins[] = array(
                                    'firstName' => $firstName,
                                    'surname' => $surname,
                                    'email' => $adminEmail,
                                    'tempPassword' => $tempPassword
                                );
                            }
                        } else {
                            $errors[] = "Failed to create user '{$firstName} {$surname}': " . $DBConn->get_error();
                            continue;
                        }
                    }

                    // Create admin entry
                    if ($peopleID) {
                        // Check if already an admin
                        $existingAdmin = $DBConn->retrieve_db_table_rows(
                            'tija_administrators',
                            array('adminID'),
                            array('userID' => $peopleID, 'orgDataID' => $orgDataID),
                            true
                        );

                        if (!$existingAdmin) {
                            $adminData = array(
                                'userID' => $peopleID,
                                'orgDataID' => $orgDataID,
                                'adminTypeID' => $adminTypeID,
                                'entityID' => !empty($createdEntities) ? $createdEntities[0]['entityID'] : null, // Assign to first entity if exists
                                'DateAdded' => date('Y-m-d H:i:s')
                            );

                            if (!$DBConn->insert_data('tija_administrators', $adminData)) {
                                $errors[] = "Failed to create admin role for '{$firstName} {$surname}': " . $DBConn->get_error();
                            }
                        }

                        // Create user_details entry
                        $existingUserDetails = $DBConn->retrieve_db_table_rows(
                            'user_details',
                            array('ID'),
                            array('ID' => $peopleID, 'orgDataID' => $orgDataID),
                            true
                        );

                        if (!$existingUserDetails) {
                            $userDetailsData = array(
                                'ID' => $peopleID,
                                'orgDataID' => $orgDataID,
                                'entityID' => !empty($createdEntities) ? $createdEntities[0]['entityID'] : null,
                                'DateAdded' => date('Y-m-d H:i:s'),

                            );

                            $DBConn->insert_data('user_details', $userDetailsData);
                        }
                    }
                }
            }
        }
    }

    // ============================================================================
    // COMMIT OR ROLLBACK
    // ============================================================================

    if (empty($errors)) {
        $DBConn->commit();

        // Prepare success response
        $response = array(
            'success' => true,
            'message' => 'Tenant setup completed successfully!',
            'data' => array(
                'orgDataID' => $orgDataID,
                'orgName' => $orgName,
                'licenseID' => $licenseID,
                'licenseKey' => $licenseKey,
                'entitiesCreated' => count($createdEntities),
                'adminsCreated' => count($createdAdmins),
                'newAdmins' => $createdAdmins // For sending welcome emails
            )
        );

        // Set flash message
        $_SESSION['FlashMessages'] = serialize(array(
            array(
                'Text' => "Tenant '{$orgName}' created successfully! Organization ID: {$orgDataID}",
                'Type' => 'success'
            )
        ));

        echo json_encode($response);
    } else {
        $DBConn->rollback();

        echo json_encode(array(
            'success' => false,
            'message' => 'Failed to create tenant',
            'errors' => $errors
        ));
    }

} catch (Exception $e) {
    if (isset($DBConn) && $DBConn) {
        $DBConn->rollback();
    }

    echo json_encode(array(
        'success' => false,
        'message' => 'Exception occurred: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ));
}

/**
 * Generate a unique license key
 * Format: TIJA-TYPE-YEAR-XXXX
 */
function generateLicenseKey($orgName, $licenseType) {
    $prefix = 'TIJA';
    $typeCode = strtoupper(substr($licenseType, 0, 3));
    $year = date('Y');

    // Generate random alphanumeric string
    $randomPart = strtoupper(substr(md5($orgName . time() . rand()), 0, 8));

    return "{$prefix}-{$typeCode}-{$year}-{$randomPart}";
}
?>

