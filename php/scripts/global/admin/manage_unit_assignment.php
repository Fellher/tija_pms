<?php
/**
 * Manage Unit Assignment Script
 * Handles adding and removing employee unit assignments
 *
 * @package Tija Practice Management System
 * @subpackage Admin
 * @version 1.0
 */

session_start();
$base = '../../../../';
set_include_path($base);

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

include 'php/includes.php';

// Log the start
error_log("=== Manage Unit Assignment Script Started ===");
error_log("POST Data: " . print_r($_POST, true));

$response = ['success' => false, 'message' => ''];

try {
    // Check admin access or user editing own profile
    $userID = isset($_POST['userID']) ? Utility::clean_string($_POST['userID']) : null;
    $action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : null;

    if (!$isValidAdmin && !$isAdmin && $userDetails->ID != $userID) {
        throw new Exception("You are not authorized to perform this action.");
    }

    $DBConn->begin();

    if ($action === 'add') {
        // Add new unit assignment
        $unitID = isset($_POST['unitID']) && !empty($_POST['unitID'])
            ? Utility::clean_string($_POST['unitID']) : null;
        $unitTypeID = isset($_POST['unitTypeID']) && !empty($_POST['unitTypeID'])
            ? Utility::clean_string($_POST['unitTypeID']) : null;
        $orgDataID = isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])
            ? Utility::clean_string($_POST['orgDataID']) : null;
        $entityID = isset($_POST['entityID']) && !empty($_POST['entityID'])
            ? Utility::clean_string($_POST['entityID']) : null;
        $assignmentStartDate = isset($_POST['assignmentStartDate']) && !empty($_POST['assignmentStartDate'])
            ? Utility::clean_string($_POST['assignmentStartDate']) : date('Y-m-d');

        // Validate required fields
        if (!$userID) {
            throw new Exception("User ID is required");
        }
        if (!$unitID) {
            throw new Exception("Unit ID is required");
        }

        // Check if assignment already exists
        $existingAssignment = Employee::user_unit_assignments([
            'userID' => $userID,
            'unitID' => $unitID,
            'Suspended' => 'N'
        ], true, $DBConn);

        if ($existingAssignment) {
            throw new Exception("User is already assigned to this unit");
        }

        // Prepare assignment data
        $assignmentData = [
            'userID' => $userID,
            'unitID' => $unitID,
            'unitTypeID' => $unitTypeID,
            'orgDataID' => $orgDataID,
            'entityID' => $entityID,
            'assignmentStartDate' => $assignmentStartDate,
            'DateAdded' => date('Y-m-d H:i:s'),
            'LastUpdate' => date('Y-m-d H:i:s'),
            'LastUpdatedByID' => $_SESSION['ID'] ?? $userDetails->ID ?? null,
            'Lapsed' => 'N',
            'Suspended' => 'N'
        ];

        error_log("Creating unit assignment: " . print_r($assignmentData, true));

        $insertResult = $DBConn->insert_data('tija_user_unit_assignments', $assignmentData);

        if ($insertResult) {
            $assignmentID = $DBConn->lastInsertId();
            error_log("Unit assignment created with ID: " . $assignmentID);

            $DBConn->commit();
            $response['success'] = true;
            $response['message'] = 'Unit assigned successfully';
            $response['assignmentID'] = $assignmentID;
        } else {
            throw new Exception("Failed to create unit assignment");
        }

    } elseif ($action === 'remove') {
        // Remove unit assignment (soft delete - set Suspended = 'Y')
        $unitAssignmentID = isset($_POST['unitAssignmentID']) && !empty($_POST['unitAssignmentID'])
            ? Utility::clean_string($_POST['unitAssignmentID']) : null;

        if (!$unitAssignmentID) {
            throw new Exception("Unit Assignment ID is required");
        }

        // Verify assignment exists and belongs to the user
        $assignment = Employee::user_unit_assignments([
            'unitAssignmentID' => $unitAssignmentID
        ], true, $DBConn);

        if (!$assignment) {
            throw new Exception("Unit assignment not found");
        }

        // Verify user has permission to delete this assignment
        if (!$isValidAdmin && !$isAdmin && $userDetails->ID != $assignment->userID) {
            throw new Exception("You are not authorized to remove this assignment");
        }

        // Soft delete
        $updateData = [
            'Suspended' => 'Y',
            'assignmentEndDate' => date('Y-m-d'),
            'LastUpdate' => date('Y-m-d H:i:s'),
            'LastUpdatedByID' => $userDetails->ID ?? null
        ];

        error_log("Removing unit assignment ID: " . $unitAssignmentID);

        $updateResult = $DBConn->update_table('tija_user_unit_assignments', $updateData, [
            'unitAssignmentID' => $unitAssignmentID
        ]);

        if ($updateResult) {
            $DBConn->commit();
            error_log("Unit assignment removed successfully");
            $response['success'] = true;
            $response['message'] = 'Unit assignment removed successfully';
        } else {
            throw new Exception("Failed to remove unit assignment");
        }

    } else {
        throw new Exception("Invalid action specified");
    }

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

