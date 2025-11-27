<?php
/**
 * Proposal Checklist Item Submission Management Script
 * Handles submission, review, and approval of checklist item assignments
 *
 * @package    Tija CRM
 * @subpackage Sales Management
 */

session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';

// Initialize response
$response = array(
    'success' => false,
    'message' => '',
    'data' => null
);

try {
    // Check if user is logged in
    if (!$isValidUser) {
        throw new Exception('User not authenticated. Please log in to continue.');
    }

    $userID = $userDetails->ID;
    $action = $_POST['action'] ?? 'submit';

    switch ($action) {
        case 'submit':
            handleSubmission($userID, $DBConn);
            break;
        case 'review':
            handleReview($userID, $DBConn);
            break;
        case 'get':
            handleGetSubmission($userID, $DBConn);
            break;
        default:
            throw new Exception('Invalid action specified.');
    }

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    error_log("Proposal Submission Error [User: {$userID}]: " . $e->getMessage());
    echo json_encode($response);
    exit;
}

/**
 * Handle submission
 */
function handleSubmission($userID, $DBConn) {
    global $response, $config;

    try {
        // Validate required fields
        if (!isset($_POST['proposalChecklistItemAssignmentID']) || empty($_POST['proposalChecklistItemAssignmentID'])) {
            throw new Exception('Assignment ID is required.');
        }

        $assignmentID = intval($_POST['proposalChecklistItemAssignmentID']);

        // Get assignment details
        $assignment = Proposal::proposal_checklist_item_assignment_full(
            array('proposalChecklistItemAssignmentID' => $assignmentID),
            true,
            $DBConn
        );

        if (!$assignment) {
            throw new Exception('Assignment not found.');
        }

        // Check if user is assigned to this task
        if ($assignment->checklistItemAssignedEmployeeID != $userID) {
            throw new Exception('You are not assigned to this task.');
        }

        $submissionNotes = isset($_POST['submissionNotes']) ? Utility::clean_string($_POST['submissionNotes']) : null;
        $submissionStatus = isset($_POST['submissionStatus']) ? Utility::clean_string($_POST['submissionStatus']) : 'submitted';
        $orgDataID = isset($_POST['orgDataID']) ? intval($_POST['orgDataID']) : null;
        $entityID = isset($_POST['entityID']) ? intval($_POST['entityID']) : null;

        // Handle file uploads if any
        $submissionFiles = array();
        if (isset($_FILES['submissionFiles']) && !empty($_FILES['submissionFiles']['name'][0])) {
            $files = $_FILES['submissionFiles'];
            $allowedTypes = array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png', 'gif');
            $maxSize = 50 * 1024 * 1024; // 50MB

            // Handle multiple files
            if (is_array($files['name'])) {
                for ($i = 0; $i < count($files['name']); $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        $file = array(
                            'name' => $files['name'][$i],
                            'type' => $files['type'][$i],
                            'tmp_name' => $files['tmp_name'][$i],
                            'error' => $files['error'][$i],
                            'size' => $files['size'][$i]
                        );

                        if ($file['size'] > $maxSize) {
                            throw new Exception("File '{$file['name']}' exceeds maximum size of 50MB.");
                        }

                        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        if (!in_array($fileExtension, $allowedTypes)) {
                            throw new Exception("File type '{$fileExtension}' is not allowed for file '{$file['name']}'.");
                        }

                        $uploadResult = File::upload_file($file, 'proposal_submissions', $allowedTypes, $maxSize, $config, $DBConn);
                        if ($uploadResult && isset($uploadResult['uploadedFilePaths'])) {
                            $submissionFiles[] = $uploadResult['uploadedFilePaths'];
                        }
                    }
                }
            }
        }

        // Prepare submission data
        $submissionData = array(
            'proposalChecklistItemAssignmentID' => $assignmentID,
            'submittedBy' => $userID,
            'submissionDate' => 'NOW()',
            'submissionStatus' => $submissionStatus,
            'submissionNotes' => $submissionNotes,
            'submissionFiles' => !empty($submissionFiles) ? json_encode($submissionFiles) : null,
            'orgDataID' => $orgDataID,
            'entityID' => $entityID,
            'DateAdded' => 'NOW()',
            'Suspended' => 'N'
        );

        // Insert submission
        $result = $DBConn->insert_data('tija_proposal_checklist_item_submissions', $submissionData);

        if ($result) {
            $submissionID = $DBConn->lastInsertId();

            // Update assignment status if needed
            if ($submissionStatus === 'submitted') {
                // Update assignment to show it's been submitted
                // You may want to update the assignment status here
            }

            // Update proposal completion
            Proposal::update_proposal_completion($assignment->proposalID, $DBConn);

            // Send notification to manager/proposal owner
            sendSubmissionNotification($submissionID, $assignment, $DBConn);

            $response['success'] = true;
            $response['message'] = 'Submission completed successfully';
            $response['data'] = array('submissionID' => $submissionID);
        } else {
            throw new Exception('Failed to save submission. Please try again.');
        }

    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
        error_log("Proposal Submission Error [User: {$userID}]: " . $e->getMessage());
    }

    echo json_encode($response);
    exit;
}

/**
 * Handle review/approval
 */
function handleReview($userID, $DBConn) {
    global $response;

    try {
        if (!isset($_POST['submissionID']) || empty($_POST['submissionID'])) {
            throw new Exception('Submission ID is required.');
        }

        if (!isset($_POST['reviewStatus']) || !in_array($_POST['reviewStatus'], array('approved', 'rejected', 'revision_requested'))) {
            throw new Exception('Invalid review status.');
        }

        $submissionID = intval($_POST['submissionID']);
        $reviewStatus = Utility::clean_string($_POST['reviewStatus']);
        $reviewNotes = isset($_POST['reviewNotes']) ? Utility::clean_string($_POST['reviewNotes']) : null;

        // Get submission
        $submission = Proposal::proposal_checklist_submissions(
            array('submissionID' => $submissionID),
            true,
            $DBConn
        );

        if (!$submission) {
            throw new Exception('Submission not found.');
        }

        // Check permissions (manager, proposal owner, or line manager)
        // This should be enhanced with proper role checking
        $canReview = true; // Placeholder - add proper permission check

        if (!$canReview) {
            throw new Exception('You do not have permission to review this submission.');
        }

        $changes = array(
            'submissionStatus' => $reviewStatus,
            'reviewedBy' => $userID,
            'reviewedDate' => 'NOW()',
            'reviewNotes' => $reviewNotes,
            'LastUpdatedByID' => $userID
        );

        $result = $DBConn->update_table('tija_proposal_checklist_item_submissions', $changes, array('submissionID' => $submissionID));

        if ($result) {
            // Update assignment status if approved
            if ($reviewStatus === 'approved') {
                // Update the assignment status to completed/approved
                // This depends on your status system
            }

            // Update proposal completion
            Proposal::update_proposal_completion($submission->proposalID, $DBConn);

            // Send notification to submitter
            sendReviewNotification($submissionID, $reviewStatus, $DBConn);

            $response['success'] = true;
            $response['message'] = "Submission {$reviewStatus} successfully";
        } else {
            throw new Exception('Failed to update review status. Please try again.');
        }

    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
        error_log("Proposal Review Error [User: {$userID}]: " . $e->getMessage());
    }

    echo json_encode($response);
    exit;
}

/**
 * Handle get submission
 */
function handleGetSubmission($userID, $DBConn) {
    global $response;

    try {
        if (!isset($_POST['submissionID']) || empty($_POST['submissionID'])) {
            throw new Exception('Submission ID is required.');
        }

        $submissionID = intval($_POST['submissionID']);
        $submission = Proposal::proposal_checklist_submissions(
            array('submissionID' => $submissionID),
            true,
            $DBConn
        );

        if (!$submission) {
            throw new Exception('Submission not found.');
        }

        $response['success'] = true;
        $response['data'] = $submission;

    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
    }

    echo json_encode($response);
    exit;
}

/**
 * Send submission notification
 */
function sendSubmissionNotification($submissionID, $assignment, $DBConn) {
    // TODO: Integrate with notification system
    error_log("Submission notification should be sent for submission ID: {$submissionID}");
}

/**
 * Send review notification
 */
function sendReviewNotification($submissionID, $reviewStatus, $DBConn) {
    // TODO: Integrate with notification system
    error_log("Review notification should be sent for submission ID: {$submissionID}, Status: {$reviewStatus}");
}
