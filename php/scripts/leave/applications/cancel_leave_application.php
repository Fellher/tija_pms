<?php
/**
 * Cancel Leave Application Script
 *
 * Handles the cancellation of leave applications
 */

// Include necessary files
session_start();
$base = '../../../../';
set_include_path($base);
include 'php/includes.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!$isValidUser) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);
    $leaveId = isset($input['leaveId']) ? Utility::clean_string($input['leaveId']) : '';
    $cancelledBy = isset($input['cancelledBy']) ? Utility::clean_string($input['cancelledBy']) : '';
    $cancellationReason = isset($input['cancellationReason']) ? Utility::clean_string($input['cancellationReason']) : '';

    if (empty($leaveId)) {
        echo json_encode(['success' => false, 'message' => 'Leave ID is required']);
        exit;
    }

    // Get leave application details using Leave class method
    $leave = Leave::leave_applications_full(array('leaveApplicationID' => $leaveId), true, $DBConn);

    if (!$leave) {
        echo json_encode(['success' => false, 'message' => 'Leave application not found']);
        exit;
    }

    // Check permissions using Leave class method
    $currentUserId = $userDetails->ID ?? null;
    $permissions = Leave::check_leave_application_permissions($leave, $currentUserId);

    if (!$permissions['canCancel']) {
        echo json_encode(['success' => false, 'message' => 'You are not authorized to cancel this application or the application cannot be cancelled']);
        exit;
    }

    // Start transaction
    $DBConn->begin();

    try {
        $currentDate = date('Y-m-d H:i:s');
        $cancelledById = $cancelledBy ?: $currentUserId;

        // Update leave application status to cancelled using mysqlConnect update_table method
        $updateData = array(
            'leaveStatusID' => 5, // Cancelled status
            'cancellationDate' => $currentDate,
            'cancelledByID' => $cancelledById,
            'cancellationReason' => $cancellationReason,
            'LastUpdate' => $currentDate,
            'LastUpdateByID' => $cancelledById
        );

        $whereClause = array('leaveApplicationID' => $leaveId);

        $updateResult = $DBConn->update_table('tija_leave_applications', $updateData, $whereClause);

        if (!$updateResult) {
            throw new Exception('Failed to update leave application status');
        }

        // Add cancellation comment if table exists
        if (!empty($cancellationReason)) {
            // Check if comments table exists
            $tableCheck = "SHOW TABLES LIKE 'tija_leave_approval_comments'";
            $tableExists = $DBConn->fetch_all_rows($tableCheck, array());

            if ($tableExists && count($tableExists) > 0) {
                $commentData = array(
                    'leaveApplicationID' => $leaveId,
                    'approverID' => $cancelledById,
                    'approvalLevel' => 'cancellation',
                    'comment' => "Application cancelled: " . $cancellationReason,
                    'commentDate' => $currentDate,
                    'commentType' => 'cancellation',
                    'DateAdded' => $currentDate,
                    'Lapsed' => 'N',
                    'Suspended' => 'N'
                );

                $DBConn->insert_data('tija_leave_approval_comments', $commentData);
            } else {
                // Log comment to error log as fallback
                error_log("Leave cancellation comment (table not exists): Application {$leaveId} - {$cancellationReason}");
            }
        }

        // Log activity (simplified - just log for now)
        // TODO: Implement proper activity logging
        error_log("Leave activity: Application cancelled for ID {$leaveId}");

        // Send notifications (simplified - just log for now)
        // TODO: Implement proper notification system
        error_log("Leave notification: Application cancelled for ID {$leaveId}");

        // Commit transaction
        $DBConn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Leave application cancelled successfully',
            'leaveId' => $leaveId
        ]);

    } catch (Exception $e) {
        $DBConn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    error_log('Cancel leave application error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while cancelling the leave application']);
}
?>
