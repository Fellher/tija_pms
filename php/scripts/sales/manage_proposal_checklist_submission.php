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
 * Send submission notification to checklist owner
 *
 * @param int $submissionID The submission ID
 * @param object $assignment The assignment details
 * @param object $DBConn Database connection
 */
function sendSubmissionNotification($submissionID, $assignment, $DBConn) {
    global $config, $userDetails;

    try {
        // Check if the notification class exists
        if (!class_exists('ProposalChecklistNotification')) {
            error_log("ProposalChecklistNotification class not found - submission notification not sent");
            return;
        }

        // Get proposal details
        $proposalDetails = Proposal::proposals(array('proposalID' => $assignment->proposalID), true, $DBConn);
        $proposalTitle = $proposalDetails ? $proposalDetails->proposalTitle : 'Proposal #' . $assignment->proposalID;

        // Get checklist details to find the owner
        $checklistDetails = Proposal::proposal_checklist(
            array('proposalChecklistID' => $assignment->proposalChecklistID),
            true,
            $DBConn
        );
        $checklistName = $checklistDetails ? $checklistDetails->proposalChecklistName : 'Checklist';
        $checklistOwnerID = $checklistDetails ? $checklistDetails->assignedEmployeeID : null;

        // Build action link
        $actionLink = "{$config['baseURL']}html/?s=sales&ss=proposals&sss=proposal_details&p=proposal_checklist_item_details&checkListItemAssignmentID={$assignment->proposalChecklistItemAssignmentID}";

        // Send notification to checklist owner if different from submitter
        if ($checklistOwnerID && $checklistOwnerID != $userDetails->ID) {
            $result = ProposalChecklistNotification::sendSubmissionNotification(array(
                'ownerUserID' => $checklistOwnerID,
                'assigneeUserID' => $userDetails->ID,
                'proposalID' => $assignment->proposalID,
                'proposalTitle' => $proposalTitle,
                'checklistName' => $checklistName,
                'requirementName' => $assignment->proposalChecklistItemAssignmentDescription ?? 'Checklist Item',
                'submissionDate' => date('M d, Y H:i'),
                'attachmentCount' => isset($_FILES['submissionFiles']) ? count($_FILES['submissionFiles']['name']) : 0,
                'actionLink' => $actionLink
            ), $DBConn);

            if ($result && isset($result['success']) && $result['success']) {
                error_log("Submission notification sent to checklist owner (ID: {$checklistOwnerID})");
            } else {
                error_log("Failed to send submission notification: " . ($result['message'] ?? 'Unknown error'));
            }
        }

        // Also notify the assignor if different from owner and submitter
        $assignorID = $assignment->proposalChecklistAssignorID ?? null;
        if ($assignorID && $assignorID != $userDetails->ID && $assignorID != $checklistOwnerID) {
            ProposalChecklistNotification::sendSubmissionNotification(array(
                'ownerUserID' => $assignorID,
                'assigneeUserID' => $userDetails->ID,
                'proposalID' => $assignment->proposalID,
                'proposalTitle' => $proposalTitle,
                'checklistName' => $checklistName,
                'requirementName' => $assignment->proposalChecklistItemAssignmentDescription ?? 'Checklist Item',
                'submissionDate' => date('M d, Y H:i'),
                'attachmentCount' => isset($_FILES['submissionFiles']) ? count($_FILES['submissionFiles']['name']) : 0,
                'actionLink' => $actionLink
            ), $DBConn);
        }

    } catch (Exception $e) {
        error_log("Error sending submission notification: " . $e->getMessage());
    }
}

/**
 * Send review notification to the submitter
 *
 * @param int $submissionID The submission ID
 * @param string $reviewStatus The review status (approved, rejected, revision_requested)
 * @param object $DBConn Database connection
 */
function sendReviewNotification($submissionID, $reviewStatus, $DBConn) {
    global $config, $userDetails;

    try {
        if (!class_exists('ProposalChecklistNotification')) {
            error_log("ProposalChecklistNotification class not found - review notification not sent");
            return;
        }

        // Get submission details
        $submission = Proposal::proposal_checklist_submissions(
            array('submissionID' => $submissionID),
            true,
            $DBConn
        );

        if (!$submission) {
            error_log("Could not find submission {$submissionID} for review notification");
            return;
        }

        // Get assignment details
        $assignment = Proposal::proposal_checklist_item_assignment_full(
            array('proposalChecklistItemAssignmentID' => $submission->proposalChecklistItemAssignmentID),
            true,
            $DBConn
        );

        if (!$assignment) {
            error_log("Could not find assignment for review notification");
            return;
        }

        // Get proposal details
        $proposalDetails = Proposal::proposals(array('proposalID' => $assignment->proposalID), true, $DBConn);
        $proposalTitle = $proposalDetails ? $proposalDetails->proposalTitle : 'Proposal #' . $assignment->proposalID;

        // Get checklist details
        $checklistDetails = Proposal::proposal_checklist(
            array('proposalChecklistID' => $assignment->proposalChecklistID),
            true,
            $DBConn
        );
        $checklistName = $checklistDetails ? $checklistDetails->proposalChecklistName : 'Checklist';

        $requirementName = $assignment->proposalChecklistItemAssignmentDescription ?? 'Checklist Item';
        $submitterID = $submission->submittedBy ?? $assignment->checklistItemAssignedEmployeeID;

        $actionLink = "{$config['baseURL']}html/?s=sales&ss=proposals&sss=proposal_details&p=proposal_checklist_item_details&checkListItemAssignmentID={$assignment->proposalChecklistItemAssignmentID}";

        $reviewNotes = isset($_POST['reviewNotes']) ? Utility::clean_string($_POST['reviewNotes']) : '';

        switch ($reviewStatus) {
            case 'approved':
                ProposalChecklistNotification::sendApprovalNotification(array(
                    'assigneeUserID' => $submitterID,
                    'reviewerUserID' => $userDetails->ID,
                    'proposalID' => $assignment->proposalID,
                    'proposalTitle' => $proposalTitle,
                    'checklistName' => $checklistName,
                    'requirementName' => $requirementName,
                    'comments' => $reviewNotes ?: 'Your submission has been approved.',
                    'actionLink' => $actionLink
                ), $DBConn);

                // Check if all items are completed for this proposal
                $completion = Proposal::calculate_proposal_completion($assignment->proposalID, $DBConn);
                if ($completion && isset($completion['total']) && $completion['total'] == 100) {
                    // All items completed - notify proposal owner
                    ProposalChecklistNotification::sendCompletionNotification(array(
                        'ownerUserID' => $proposalDetails->employeeID ?? $userDetails->ID,
                        'proposalID' => $assignment->proposalID,
                        'proposalTitle' => $proposalTitle,
                        'totalItems' => $completion['totalItems'] ?? 0,
                        'actionLink' => "{$config['baseURL']}html/?s=sales&ss=proposals&sss=proposal_details&proposalID={$assignment->proposalID}"
                    ), $DBConn);
                }
                break;

            case 'rejected':
            case 'revision_requested':
                ProposalChecklistNotification::sendRevisionRequiredNotification(array(
                    'assigneeUserID' => $submitterID,
                    'reviewerUserID' => $userDetails->ID,
                    'proposalID' => $assignment->proposalID,
                    'proposalTitle' => $proposalTitle,
                    'checklistName' => $checklistName,
                    'requirementName' => $requirementName,
                    'feedback' => $reviewNotes ?: 'Please review the comments and revise your submission.',
                    'dueDate' => $assignment->proposalChecklistItemAssignmentDueDate ?? date('Y-m-d', strtotime('+3 days')),
                    'actionLink' => $actionLink
                ), $DBConn);
                break;
        }

        error_log("Review notification sent for submission {$submissionID} with status: {$reviewStatus}");

    } catch (Exception $e) {
        error_log("Error sending review notification: " . $e->getMessage());
    }
}
