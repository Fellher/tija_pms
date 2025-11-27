<?php
/**
 * Proposal Creation Wizard Handler
 * Handles multi-step proposal creation with checklist and assignments
 *
 * @package    Tija CRM
 * @subpackage Sales Management
 */

session_start();
$base = '../../../';
set_include_path($base);

// Start output buffering
ob_start();

include 'php/includes.php';

// Clear any output
ob_clean();

// Set content type to JSON
header('Content-Type: application/json');

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
    $action = $_POST['action'] ?? 'create_with_checklist';

    if ($action === 'create_with_checklist') {
        handleWizardCreation($userID, $DBConn);
    } else {
        throw new Exception('Invalid action specified.');
    }

} catch (Exception $e) {
    ob_clean();
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    error_log("Proposal Wizard Error [User: " . ($userID ?? 'unknown') . "]: " . $e->getMessage());
    echo json_encode($response);
    exit;
} catch (Error $e) {
    ob_clean();
    $response['success'] = false;
    $response['message'] = 'A system error occurred: ' . $e->getMessage();
    error_log("Proposal Wizard Fatal Error [User: " . ($userID ?? 'unknown') . "]: " . $e->getMessage());
    echo json_encode($response);
    exit;
}

/**
 * Handle wizard creation
 */
function handleWizardCreation($userID, $DBConn) {
    global $response, $config;

    $DBConn->beginTransaction();

    try {
        // Step 1: Create Proposal
        $proposalData = array(
            'proposalTitle' => Utility::clean_string($_POST['proposalTitle'] ?? ''),
            'clientID' => intval($_POST['clientID'] ?? 0),
            'salesCaseID' => intval($_POST['salesCaseID'] ?? 0),
            'proposalDeadline' => Utility::clean_string($_POST['proposalDeadline'] ?? ''),
            'proposalValue' => floatval($_POST['proposalValue'] ?? 0),
            'proposalStatusID' => intval($_POST['proposalStatusID'] ?? 0),
            'proposalDescription' => isset($_POST['proposalDescription']) ? Utility::sanitize_rich_text_input($_POST['proposalDescription']) : '',
            'employeeID' => $userID,
            'orgDataID' => intval($_POST['orgDataID'] ?? 0),
            'entityID' => intval($_POST['entityID'] ?? 0),
            'proposalCode' => Utility::genrateRandomInteger(4) . "_" . date('Y'),
            'statusStage' => 'draft',
            'statusStageOrder' => 1,
            'completionPercentage' => 0,
            'mandatoryCompletionPercentage' => 0,
            'DateAdded' => $config['currentDateTimeFormated'],
            'LastUpdate' => $config['currentDateTimeFormated'],
            'LastUpdateByID' => $userID,
            'Suspended' => 'N'
        );

        // Validate required fields
        if (empty($proposalData['proposalTitle'])) {
            throw new Exception('Proposal title is required.');
        }
        if (empty($proposalData['clientID'])) {
            throw new Exception('Client selection is required.');
        }
        if (empty($proposalData['salesCaseID'])) {
            throw new Exception('Sales case selection is required.');
        }
        if (empty($proposalData['proposalDeadline'])) {
            throw new Exception('Proposal deadline is required.');
        }

        // Insert proposal
        if (!$DBConn->insert_data('tija_proposals', $proposalData)) {
            throw new Exception('Failed to create proposal.');
        }

        $proposalID = $DBConn->lastInsertId();
        if (!$proposalID) {
            throw new Exception('Failed to retrieve proposal ID.');
        }

        // Step 2: Create Checklist
        $checklistName = Utility::clean_string($_POST['checklist_checklistName'] ?? '');
        $checklistDeadline = Utility::clean_string($_POST['checklist_checklistDeadline'] ?? '');

        if (!empty($checklistName) && !empty($checklistDeadline)) {
            $checklistData = array(
                'proposalID' => $proposalID,
                'proposalChecklistName' => $checklistName,
                'proposalChecklistDeadlineDate' => $checklistDeadline,
                'proposalChecklistStatusID' => !empty($_POST['checklist_checklistStatusID']) ? intval($_POST['checklist_checklistStatusID']) : null,
                'assignedEmployeeID' => !empty($_POST['checklist_checklistAssignedTo']) ? intval($_POST['checklist_checklistAssignedTo']) : null,
                'proposalChecklistDescription' => !empty($_POST['checklist_checklistDescription']) ? Utility::sanitize_rich_text_input($_POST['checklist_checklistDescription']) : null,
                'orgDataID' => intval($_POST['orgDataID'] ?? 0),
                'entityID' => intval($_POST['entityID'] ?? 0),
                'DateAdded' => $config['currentDateTimeFormated'],
                'LastUpdate' => $config['currentDateTimeFormated'],
                'LastUpdateByID' => $userID,
                'Suspended' => 'N'
            );

            if (!$DBConn->insert_data('tija_proposal_checklists', $checklistData)) {
                throw new Exception('Failed to create checklist.');
            }

            $checklistID = $DBConn->lastInsertId();

            // Step 3 & 4: Create Assignments
            $selectedItems = json_decode($_POST['selectedItems'] ?? '[]', true);
            $assignments = json_decode($_POST['assignments'] ?? '[]', true);

            if (!empty($selectedItems) && !empty($assignments)) {
                foreach ($assignments as $assignment) {
                    if (empty($assignment['assignedTo']) || empty($assignment['dueDate'])) {
                        continue; // Skip incomplete assignments
                    }

                    $assignmentData = array(
                        'proposalID' => $proposalID,
                        'proposalChecklistID' => $checklistID,
                        'proposalChecklistItemID' => intval($assignment['itemID']),
                        'assignedTo' => intval($assignment['assignedTo']),
                        'assignedBy' => $userID,
                        'proposalChecklistItemAssignmentDueDate' => Utility::clean_string($assignment['dueDate']),
                        'proposalChecklistItemAssignmentDescription' => !empty($assignment['description']) ? Utility::clean_string($assignment['description']) : null,
                        'isMandatory' => !empty($assignment['isMandatory']) ? 'Y' : 'N',
                        'completionPercentage' => 0,
                        'notificationSent' => 'N',
                        'orgDataID' => intval($_POST['orgDataID'] ?? 0),
                        'entityID' => intval($_POST['entityID'] ?? 0),
                        'DateAdded' => $config['currentDateTimeFormated'],
                        'LastUpdate' => $config['currentDateTimeFormated'],
                        'LastUpdateByID' => $userID,
                        'Suspended' => 'N'
                    );

                    if (!$DBConn->insert_data('tija_proposal_checklist_item_assignment', $assignmentData)) {
                        error_log("Failed to create assignment for item: " . $assignment['itemID']);
                        // Continue with other assignments
                    } else {
                        // Send notification to assigned user
                        sendAssignmentNotification($DBConn->lastInsertId(), intval($assignment['assignedTo']), $DBConn);
                    }
                }
            }

            // Update proposal completion percentages
            Proposal::update_proposal_completion($proposalID, $DBConn);
        }

        $DBConn->commit();

        $response['success'] = true;
        $response['message'] = 'Proposal created successfully with checklist and assignments';
        $response['data'] = array(
            'proposalID' => $proposalID,
            'checklistID' => $checklistID ?? null
        );

    } catch (Exception $e) {
        $DBConn->rollBack();
        throw $e;
    }

    ob_clean();
    echo json_encode($response);
    exit;
}

/**
 * Send assignment notification
 */
function sendAssignmentNotification($assignmentID, $assignedTo, $DBConn) {
    global $config;

    try {
        // Get assignment details
        $assignment = Proposal::proposal_checklist_item_assignment_full(
            array('proposalChecklistItemAssignmentID' => $assignmentID),
            true,
            $DBConn
        );

        if (!$assignment) {
            return;
        }

        // Create notification
        $notificationData = array(
            'userID' => $assignedTo,
            'notificationType' => 'proposal_checklist_assignment',
            'notificationTitle' => 'New Proposal Checklist Assignment',
            'notificationMessage' => "You have been assigned: {$assignment->proposalChecklistItemName}",
            'notificationLink' => "html/?s=user&ss=sales&p=proposal_details&prID={$assignment->proposalID}",
            'isRead' => 'N',
            'DateAdded' => $config['currentDateTimeFormated'],
            'Suspended' => 'N'
        );

        $DBConn->insert_data('tija_notifications', $notificationData);
    } catch (Exception $e) {
        error_log("Failed to send assignment notification: " . $e->getMessage());
        // Don't fail the whole process if notification fails
    }
}

