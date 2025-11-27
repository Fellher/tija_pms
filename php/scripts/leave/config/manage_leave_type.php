<?php
/**
 * Leave Type Management Script
 * Consolidated script for all leave type CRUD operations
 * Replaces: manage_leave_types.php and manage_leave_policy_types.php
 */

session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';

// Set response headers - will be set conditionally based on request type
// Don't set JSON header yet - will be set only for AJAX requests

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Check if DBConn exists
if (!isset($DBConn) || !$DBConn) {
    die(json_encode(array('success' => false, 'message' => 'Database connection not available')));
}

$DBConn->begin();

$errors = array();
$details = array();
$changes = array();
$success = "";
$response = array('success' => false, 'message' => '', 'data' => null);

if ($isValidUser && ($isAdmin || $isValidAdmin || $isHRManager)) {

    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $leaveTypeID = $_POST['leaveTypeID'] ?? $_GET['leaveTypeID'] ?? '';

    // Only get basic leave type fields - policy fields will be handled in policy configuration
    $leaveTypeName = $_POST['leaveTypeName'] ?? '';
    $leaveTypeDescription = $_POST['leaveTypeDescription'] ?? '';
    $leaveTypeCode = $_POST['leaveTypeCode'] ?? '';
    $isActive = $_POST['isActive'] ?? 'Y';
    $sortOrder = $_POST['sortOrder'] ?? 0;
    $leaveColor = $_POST['leaveColor'] ?? '#3498db';
    $leaveIcon = $_POST['leaveIcon'] ?? 'ri-calendar-line';

    // For other actions (update, etc.), get additional fields as needed
    if ($action !== 'create') {
        $requiresApproval = $_POST['requiresApproval'] ?? null;
        $isPaidLeave = $_POST['isPaidLeave'] ?? null;
        $maxDaysPerApplication = $_POST['maxDaysPerApplication'] ?? null;
        $minDaysPerApplication = $_POST['minDaysPerApplication'] ?? null;
        $allowHalfDay = $_POST['allowHalfDay'] ?? null;
        $carryForwardAllowed = $_POST['carryForwardAllowed'] ?? null;
        $maxCarryForwardDays = $_POST['maxCarryForwardDays'] ?? null;
        $requiresDocumentation = $_POST['requiresDocumentation'] ?? null;
        $minAdvanceNoticeDays = $_POST['minAdvanceNoticeDays'] ?? null;
        $maxAdvanceBookingDays = $_POST['maxAdvanceBookingDays'] ?? null;
    }

    switch ($action) {
        case 'create':
            // Validate required fields
            if (empty($leaveTypeName)) {
                $errors[] = "Leave type name is required";
            }
            if (empty($leaveTypeCode)) {
                $leaveTypeCode = Utility::generate_account_code($leaveTypeName);
            }

            // Check for duplicate code
            if (!empty($leaveTypeCode)) {
                $existingType = Leave::leave_types(array('leaveTypeCode' => strtoupper($leaveTypeCode)), true, $DBConn);
                if ($existingType) {
                    $errors[] = "Leave type code already exists";
                }
            }

            if (count($errors) === 0) {
                // Only save basic leave type fields - policy details will be configured separately
                $details = array(
                    'leaveTypeName' => Utility::clean_string($leaveTypeName),
                    'leaveTypeDescription' => Utility::clean_string($leaveTypeDescription),
                    'leaveTypeCode' => strtoupper(Utility::clean_string($leaveTypeCode)),
                    'Suspended' => $isActive === 'Y' ? 'N' : 'Y', // Note: Suspended='N' means active
                    'Lapsed' => 'N',
                    'sortOrder' => (int)$sortOrder,
                    'leaveColor' => $leaveColor,
                    'leaveIcon' => $leaveIcon,
                    'LastUpdate' => $config['currentDateTimeFormated'],
                    'LastUpdateByID' => $userDetails->ID,
                    'DateAdded' => $config['currentDateTimeFormated']
                );

                $insertResult = $DBConn->insert_data("tija_leave_types", $details);

                if ($insertResult) {
                    $newTypeID = $DBConn->lastInsertId();
                    $success = "Leave type created successfully";
                    $response['success'] = true;
                    $response['message'] = $success;
                    $response['data'] = array('leaveTypeID' => $newTypeID);
                } else {
                    $errors[] = "Failed to create leave type. Please check database connection and table structure.";
                }
            }
            break;

        case 'update':
            if (empty($leaveTypeID)) {
                $errors[] = "Leave type ID is required for update";
            }

            if (count($errors) === 0) {
                $leaveTypeDetails = Leave::leave_types(array("leaveTypeID" => $leaveTypeID), true, $DBConn);

                if ($leaveTypeDetails) {
                    // Check for changes
                    if (!empty($leaveTypeName) && $leaveTypeName !== $leaveTypeDetails->leaveTypeName) {
                        $changes['leaveTypeName'] = Utility::clean_string($leaveTypeName);
                    }
                    if ($leaveTypeDescription !== $leaveTypeDetails->leaveTypeDescription) {
                        $changes['leaveTypeDescription'] = Utility::clean_string($leaveTypeDescription);
                    }
                    if (!empty($leaveTypeCode) && $leaveTypeCode !== $leaveTypeDetails->leaveTypeCode) {
                        // Check for duplicate code
                        $existingType = Leave::leave_types(array('leaveTypeCode' => strtoupper($leaveTypeCode)), true, $DBConn);
                        if ($existingType && $existingType->leaveTypeID != $leaveTypeID) {
                            $errors[] = "Leave type code already exists";
                        } else {
                            $changes['leaveTypeCode'] = strtoupper(Utility::clean_string($leaveTypeCode));
                        }
                    }

                    // Check all other fields for changes
                    $fields = array(
                        'sortOrder', 'requiresApproval', 'isPaidLeave', 'maxDaysPerApplication',
                        'minDaysPerApplication', 'allowHalfDay', 'carryForwardAllowed',
                        'maxCarryForwardDays', 'requiresDocumentation', 'minAdvanceNoticeDays',
                        'maxAdvanceBookingDays', 'leaveColor', 'leaveIcon'
                    );

                    foreach ($fields as $field) {
                        $value = $_POST[$field] ?? null;
                        if ($value !== null && isset($leaveTypeDetails->$field) && $value != $leaveTypeDetails->$field) {
                            $changes[$field] = $value;
                        }
                    }

                    // Handle active status
                    $expectedSuspended = $isActive === 'Y' ? 'N' : 'Y';
                    if (isset($leaveTypeDetails->Suspended) && $expectedSuspended !== $leaveTypeDetails->Suspended) {
                        $changes['Suspended'] = $expectedSuspended;
                    }

                    if (count($errors) === 0 && count($changes) > 0) {
                        $changes['LastUpdate'] = $config['currentDateTimeFormated'];
                        $changes['LastUpdateByID'] = $userDetails->ID;

                        if ($DBConn->update_table("tija_leave_types", $changes, array("leaveTypeID" => $leaveTypeID))) {
                            $success = "Leave type updated successfully";
                            $response['success'] = true;
                            $response['message'] = $success;
                        } else {
                            $errors[] = "Failed to update leave type";
                        }
                    } elseif (count($changes) === 0) {
                        $response['success'] = true;
                        $response['message'] = "No changes detected";
                    }
                } else {
                    $errors[] = "Leave type not found";
                }
            }
            break;

        case 'delete':
            if (empty($leaveTypeID)) {
                $errors[] = "Leave type ID is required for deletion";
            }

            if (count($errors) === 0) {
                // Check if leave type is in use
                $applications = Leave::leave_applications(array('leaveTypeID' => $leaveTypeID, 'Lapsed' => 'N'), false, $DBConn);
                $entitlements = Leave::leave_entitlements(array('leaveTypeID' => $leaveTypeID, 'Lapsed' => 'N'), false, $DBConn);

                if (($applications && count($applications) > 0) || ($entitlements && count($entitlements) > 0)) {
                    $errors[] = "Cannot delete leave type. It is currently being used in leave applications or entitlements.";
                } else {
                    $updateData = array(
                        'Lapsed' => 'Y',
                        'LastUpdate' => $config['currentDateTimeFormated'],
                        'LastUpdateByID' => $userDetails->ID
                    );

                    if ($DBConn->update_table('tija_leave_types', $updateData, array('leaveTypeID' => $leaveTypeID))) {
                        $success = "Leave type deleted successfully";
                        $response['success'] = true;
                        $response['message'] = $success;
                    } else {
                        $errors[] = "Failed to delete leave type";
                    }
                }
            }
            break;

        case 'toggle_status':
            if (empty($leaveTypeID)) {
                $errors[] = "Leave type ID is required";
            }

            if (count($errors) === 0) {
                $leaveType = Leave::leave_types(array('leaveTypeID' => $leaveTypeID), true, $DBConn);
                if ($leaveType) {
                    $newStatus = $leaveType->Suspended === 'Y' ? 'N' : 'Y';
                    $updateData = array(
                        'Suspended' => $newStatus,
                        'LastUpdate' => $config['currentDateTimeFormated'],
                        'LastUpdateByID' => $userDetails->ID
                    );

                    if ($DBConn->update_table('tija_leave_types', $updateData, array('leaveTypeID' => $leaveTypeID))) {
                        $statusText = $newStatus === 'N' ? 'activated' : 'suspended';
                        $success = "Leave type {$statusText} successfully";
                        $response['success'] = true;
                        $response['message'] = $success;
                        $response['data'] = array('newStatus' => $newStatus);
                    } else {
                        $errors[] = "Failed to update leave type status";
                    }
                } else {
                    $errors[] = "Leave type not found";
                }
            }
            break;

        case 'get_all':
            $filter = $_GET['filter'] ?? 'active';
            $whereArr = array();

            switch ($filter) {
                case 'active':
                    $whereArr['Suspended'] = 'N';
                    $whereArr['Lapsed'] = 'N';
                    break;
                case 'suspended':
                    $whereArr['Suspended'] = 'Y';
                    $whereArr['Lapsed'] = 'N';
                    break;
                case 'deleted':
                    $whereArr['Lapsed'] = 'Y';
                    break;
                case 'all':
                    // No filter
                    break;
            }

            $leaveTypes = Leave::leave_types($whereArr, false, $DBConn);
            $response['success'] = true;
            $response['data'] = $leaveTypes;
            break;

        case 'get_one':
            if (empty($leaveTypeID)) {
                $errors[] = "Leave type ID is required";
            }

            if (count($errors) === 0) {
                $leaveType = Leave::leave_types(array('leaveTypeID' => $leaveTypeID), true, $DBConn);
                if ($leaveType) {
                    $response['success'] = true;
                    $response['data'] = $leaveType;
                } else {
                    $errors[] = "Leave type not found";
                }
            }
            break;

        case 'validate_code':
            $code = $_POST['code'] ?? $_GET['code'] ?? '';
            $excludeID = $_POST['excludeID'] ?? $_GET['excludeID'] ?? '';

            if (empty($code)) {
                $errors[] = "Code is required";
            }

            if (count($errors) === 0) {
                $whereArr = array('leaveTypeCode' => strtoupper($code));
                $existingType = Leave::leave_types($whereArr, true, $DBConn);

                $isAvailable = !$existingType || ($excludeID && $existingType->leaveTypeID == $excludeID);

                $response['success'] = true;
                $response['data'] = array('available' => $isAvailable);
            }
            break;

        case 'bulk_update_order':
            $orderData = $_POST['orderData'] ?? array();

            if (empty($orderData) || !is_array($orderData)) {
                $errors[] = "Order data is required";
            }

            if (count($errors) === 0) {
                $updateCount = 0;
                foreach ($orderData as $item) {
                    if (isset($item['leaveTypeID']) && isset($item['sortOrder'])) {
                        $updateData = array(
                            'sortOrder' => (int)$item['sortOrder'],
                            'LastUpdate' => $config['currentDateTimeFormated'],
                            'LastUpdateByID' => $userDetails->ID
                        );

                        if ($DBConn->update_table('tija_leave_types', $updateData, array('leaveTypeID' => $item['leaveTypeID']))) {
                            $updateCount++;
                        }
                    }
                }

                $response['success'] = true;
                $response['message'] = "{$updateCount} leave types reordered successfully";
                $response['data'] = array('updatedCount' => $updateCount);
            }
            break;

        case 'export':
            $format = $_GET['format'] ?? 'csv';
            exportLeaveTypes($format, $DBConn);
            exit;
            break;

        default:
            $errors[] = "Invalid action specified";
            break;
    }

} else {
    $errors[] = 'Access denied. Admin privileges required.';
}

// Handle errors
if (count($errors) > 0) {
    $response['success'] = false;
    $response['message'] = implode(', ', $errors);
    $response['errors'] = $errors;
}

// Commit or rollback transaction
try {
    if ($response['success']) {
        $DBConn->commit();
    } else {
        $DBConn->rollback();
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = "Database transaction error: " . $e->getMessage();
}

// Handle response - check if this is an AJAX request or normal form submission
$isAjaxRequest = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
$isAjaxRequest = $isAjaxRequest || (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

if ($isAjaxRequest) {
    // Return JSON response for AJAX requests
    header('Content-Type: application/json');
    try {
        $jsonResponse = json_encode($response);
        if ($jsonResponse === false) {
            $response = array(
                'success' => false,
                'message' => 'Error encoding response: ' . json_last_error_msg(),
                'errors' => array('JSON encoding failed')
            );
            $jsonResponse = json_encode($response);
        }
        echo $jsonResponse;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array(
            'success' => false,
            'message' => 'Server error occurred',
            'errors' => array('Failed to generate response')
        ));
    }
} else {
    // Normal form submission - redirect based on result
    // Construct redirect URL using siteURL from config or relative path
    $baseUrl = isset($config['siteURL']) ? rtrim($config['siteURL'], '/') : '';
    $redirectBase = $baseUrl . '/html/';

    if ($response['success']) {
        // Success - redirect to policy configuration page for the new leave type
        $leaveTypeID = $response['data']['leaveTypeID'] ?? '';
        if ($leaveTypeID) {
            // Redirect to policy configuration page (edit action will load the policy form)
            $redirectUrl = $redirectBase . '?s=admin&ss=leave&p=leave_policy_management&action=edit&leaveTypeID=' . $leaveTypeID;
            if (!empty($response['message'])) {
                $_SESSION['success_message'] = $response['message'] . ' Now configure the policy details.';
            }
        } else {
            // Fallback to leave types list if no ID
            $redirectUrl = $redirectBase . '?s=admin&ss=leave&p=leave_policy_management&action=leave_types';
            if (!empty($response['message'])) {
                $_SESSION['success_message'] = $response['message'];
            }
        }
        header("Location: " . $redirectUrl);
        exit;
    } else {
        // Error - redirect back to form with error message
        $redirectUrl = $redirectBase . '?s=admin&ss=leave&p=leave_policy_management&action=create_leave_type';
        if (!empty($response['message'])) {
            $_SESSION['error_message'] = $response['message'];
        }
        if (!empty($response['errors']) && is_array($response['errors'])) {
            $_SESSION['error_details'] = $response['errors'];
        }
        header("Location: " . $redirectUrl);
        exit;
    }
}

/**
 * Export leave types to CSV or JSON format
 */
function exportLeaveTypes($format, $DBConn) {
    $leaveTypes = Leave::leave_types(array('Lapsed' => 'N'), false, $DBConn);

    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="leave_types_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // CSV headers
        fputcsv($output, array(
            'ID',
            'Name',
            'Code',
            'Description',
            'Status',
            'Paid Leave',
            'Requires Approval',
            'Min Days',
            'Max Days',
            'Allow Half Day',
            'Carry Forward',
            'Created Date',
            'Last Updated'
        ));

        // CSV data
        if ($leaveTypes && is_array($leaveTypes)) {
            foreach ($leaveTypes as $type) {
                fputcsv($output, array(
                    $type->leaveTypeID ?? '',
                    $type->leaveTypeName ?? '',
                    $type->leaveTypeCode ?? '',
                    $type->leaveTypeDescription ?? '',
                    ($type->Suspended ?? 'Y') === 'N' ? 'Active' : 'Suspended',
                    ($type->isPaidLeave ?? 'Y') === 'Y' ? 'Yes' : 'No',
                    ($type->requiresApproval ?? 'Y') === 'Y' ? 'Yes' : 'No',
                    $type->minDaysPerApplication ?? '1',
                    $type->maxDaysPerApplication ?? 'Unlimited',
                    ($type->allowHalfDay ?? 'N') === 'Y' ? 'Yes' : 'No',
                    ($type->carryForwardAllowed ?? 'N') === 'Y' ? 'Yes' : 'No',
                    isset($type->DateAdded) ? date('Y-m-d', strtotime($type->DateAdded)) : '',
                    isset($type->LastUpdate) ? date('Y-m-d', strtotime($type->LastUpdate)) : ''
                ));
            }
        }

        fclose($output);
    } else {
        // Export as JSON
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="leave_types_' . date('Y-m-d') . '.json"');

        echo json_encode($leaveTypes, JSON_PRETTY_PRINT);
    }
}
?>

