<?php
/**
 * Advanced Prospect Management API
 * Handles CRUD operations for prospects with enhanced validation
 */
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$response = array('success' => false, 'message' => '', 'data' => null);

// Check if user is logged in
if (!isset($userDetails->ID) || empty($userDetails->ID)) {
    $response['message'] = 'Unauthorized access. Please log in.';
    echo json_encode($response);
    exit;
}

$userID = $userDetails->ID;
$userDetails = Data::users(array('ID' => $userID), true, $DBConn);

if (!$userDetails) {
    $response['message'] = 'Invalid user session.';
    echo json_encode($response);
    exit;
}

// Get action
$action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : (isset($_GET['action']) ? Utility::clean_string($_GET['action']) : '');

// Handle GET request for retrieving prospect data
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get') {
    if (!isset($_GET['salesProspectID']) || empty($_GET['salesProspectID'])) {
        $response['message'] = 'Prospect ID is required.';
        echo json_encode($response);
        exit;
    }

    $prospectID = (int)$_GET['salesProspectID'];
    $prospect = Sales::sales_prospect_full($prospectID, $DBConn);

    if ($prospect) {
        $response['success'] = true;
        $response['message'] = 'Prospect retrieved successfully.';
        $response['prospect'] = $prospect;
    } else {
        $response['message'] = 'Prospect not found.';
    }

    echo json_encode($response);
    exit;
}

try {
    switch ($action) {
        case 'create': // Alias for quick add modal
        case 'createProspect':
            createProspect($DBConn, $userDetails);
            break;

        case 'update': // Alias for edit modal
        case 'updateProspect':
            updateProspect($DBConn, $userDetails);
            break;

        case 'deleteProspect':
            deleteProspect($DBConn, $userDetails);
            break;

        case 'assignTeam':
            assignTeam($DBConn, $userDetails);
            break;

        case 'updateStatus':
            updateStatus($DBConn, $userDetails);
            break;

        case 'updateQualification':
            updateQualification($DBConn, $userDetails);
            break;

        case 'calculateScore':
            calculateScore($DBConn, $userDetails);
            break;

        case 'logInteraction':
            logInteraction($DBConn, $userDetails);
            break;

        default:
            $response['message'] = 'Invalid action specified.';
            echo json_encode($response);
            exit;
    }

    // Commit transaction only if everything succeeded
    $DBConn->commit();
    error_log("Transaction committed successfully for action: " . $action);

} catch (Exception $e) {
    // Rollback on any error
    $DBConn->rollBack();
    error_log("Transaction rolled back due to error: " . $e->getMessage());

    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
    echo json_encode($response);
    exit;
}

/**
 * Create a new prospect
 */
function createProspect($DBConn, $userDetails) {
    global $response;

    // Gather prospect data from POST
    $prospectData = array(
        'salesProspectName' => Utility::clean_string($_POST['salesProspectName'] ?? ''),
        'prospectEmail' => Utility::clean_string($_POST['prospectEmail'] ?? ''),
        'prospectCaseName' => Utility::clean_string($_POST['prospectCaseName'] ?? ''),
        'businessUnitID' => (int)($_POST['businessUnitID'] ?? 0),
        'leadSourceID' => (int)($_POST['leadSourceID'] ?? 0),
        'orgDataID' => isset($_POST['orgDataID']) ? (int)$_POST['orgDataID'] : $userDetails->orgDataID,
        'entityID' => isset($_POST['entityID']) ? (int)$_POST['entityID'] : $userDetails->entityID,
        'ownerID' => isset($_POST['ownerID']) ? (int)$_POST['ownerID'] : $userDetails->ID
    );

    // Optional fields
    $optionalFields = array(
        'isClient', 'clientID', 'address', 'prospectPhone', 'prospectWebsite',
        'estimatedValue', 'probability', 'salesProspectStatus', 'leadQualificationStatus',
        'assignedTeamID', 'territoryID', 'industryID', 'companySize',
        'expectedCloseDate', 'nextFollowUpDate', 'sourceDetails',
        'budgetConfirmed', 'decisionMakerIdentified', 'timelineDefined', 'needIdentified'
    );

    foreach ($optionalFields as $field) {
        if (isset($_POST[$field]) && !empty($_POST[$field])) {
            $prospectData[$field] = Utility::clean_string($_POST[$field]);
        }
    }

    // Handle tags
    if (isset($_POST['tags']) && !empty($_POST['tags'])) {
        $prospectData['tags'] = is_array($_POST['tags']) ? $_POST['tags'] : json_decode($_POST['tags'], true);
    }

    // Use Sales class method
    $result = Sales::create_prospect($prospectData, $userDetails->ID, $DBConn);

    $response = $result;
    echo json_encode($response);
}

/**
 * Update an existing prospect
 */
function updateProspect($DBConn, $userDetails) {
    global $response;

    if (!isset($_POST['salesProspectID']) || empty($_POST['salesProspectID'])) {
        $response['message'] = 'Prospect ID is required.';
        echo json_encode($response);
        exit;
    }

    $prospectID = (int)$_POST['salesProspectID'];

    // Build update data from allowed fields
    $updateData = array();
    $allowedFields = array(
        'salesProspectName', 'prospectEmail', 'prospectCaseName', 'address',
        'prospectPhone', 'prospectWebsite', 'estimatedValue', 'probability',
        'salesProspectStatus', 'leadQualificationStatus', 'isClient', 'clientID',
        'businessUnitID', 'leadSourceID', 'assignedTeamID', 'territoryID',
        'industryID', 'companySize', 'expectedCloseDate', 'lastContactDate',
        'nextFollowUpDate', 'sourceDetails', 'budgetConfirmed',
        'decisionMakerIdentified', 'timelineDefined', 'needIdentified'
    );

    foreach ($allowedFields as $field) {
        if (isset($_POST[$field])) {
            $updateData[$field] = Utility::clean_string($_POST[$field]);
        }
    }

    // Handle tags
    if (isset($_POST['tags'])) {
        $updateData['tags'] = is_array($_POST['tags']) ? $_POST['tags'] : json_decode($_POST['tags'], true);
    }

    if (empty($updateData)) {
        $response['message'] = 'No fields to update.';
        echo json_encode($response);
        exit;
    }

    // Use Sales class method
    $result = Sales::update_prospect($prospectID, $updateData, $userDetails->ID, $DBConn);
    $response = $result;
    echo json_encode($response);
}

/**
 * Delete a prospect (soft delete)
 */
function deleteProspect($DBConn, $userDetails) {
    global $response;

    if (!isset($_POST['salesProspectID']) || empty($_POST['salesProspectID'])) {
        $response['message'] = 'Prospect ID is required.';
        echo json_encode($response);
        exit;
    }

    $prospectID = (int)$_POST['salesProspectID'];

    // Use Sales class method
    $result = Sales::delete_prospect($prospectID, $userDetails->ID, $DBConn);
    $response = $result;
    echo json_encode($response);
}

/**
 * Assign prospect to team
 */
function assignTeam($DBConn, $userDetails) {
    global $response;

    if (!isset($_POST['salesProspectID']) || !isset($_POST['teamID'])) {
        $response['message'] = 'Prospect ID and Team ID are required.';
        echo json_encode($response);
        exit;
    }

    $prospectID = (int)$_POST['salesProspectID'];
    $teamID = (int)$_POST['teamID'];

    // Use Sales class method
    $result = Sales::assign_prospect_team($prospectID, $teamID, $userDetails->ID, $DBConn);
    $response = $result;
    echo json_encode($response);
}

/**
 * Update prospect status
 */
function updateStatus($DBConn, $userDetails) {
    global $response;

    if (!isset($_POST['salesProspectID']) || !isset($_POST['status'])) {
        $response['message'] = 'Prospect ID and status are required.';
        echo json_encode($response);
        exit;
    }

    $prospectID = (int)$_POST['salesProspectID'];
    $status = Utility::clean_string($_POST['status']);

    // Use Sales class method
    $result = Sales::update_prospect_status($prospectID, $status, $userDetails->ID, $DBConn);
    $response = $result;
    echo json_encode($response);
}

/**
 * Update prospect qualification
 */
function updateQualification($DBConn, $userDetails) {
    global $response;

    if (!isset($_POST['salesProspectID']) || !isset($_POST['qualification'])) {
        $response['message'] = 'Prospect ID and qualification are required.';
        echo json_encode($response);
        exit;
    }

    $prospectID = (int)$_POST['salesProspectID'];

    // Gather qualification data
    $qualificationData = array(
        'leadQualificationStatus' => Utility::clean_string($_POST['qualification'])
    );

    // Add BANT fields if provided
    $bantFields = array('budgetConfirmed', 'decisionMakerIdentified', 'timelineDefined', 'needIdentified');
    foreach ($bantFields as $field) {
        if (isset($_POST[$field])) {
            $qualificationData[$field] = $_POST[$field];
        }
    }

    // Use Sales class method
    $result = Sales::update_prospect_qualification($prospectID, $qualificationData, $userDetails->ID, $DBConn);
    $response = $result;
    echo json_encode($response);
}

/**
 * Calculate/recalculate lead score
 */
function calculateScore($DBConn, $userDetails) {
    global $response;

    if (!isset($_POST['salesProspectID'])) {
        $response['message'] = 'Prospect ID is required.';
        echo json_encode($response);
        exit;
    }

    $prospectID = (int)$_POST['salesProspectID'];

    $score = Sales::calculate_lead_score($prospectID, $DBConn);

    $response['success'] = true;
    $response['message'] = 'Lead score calculated successfully.';
    $response['data'] = array('leadScore' => $score);

    echo json_encode($response);
}

/**
 * Log a prospect interaction
 */
function logInteraction($DBConn, $userDetails) {
    global $response;

    // DEBUG: Log all POST data
    error_log("=== LOG INTERACTION DEBUG ===");
    error_log("POST data: " . print_r($_POST, true));
    error_log("User ID: " . $userDetails->ID);

    if (!isset($_POST['salesProspectID']) || !isset($_POST['interactionType'])) {
        error_log("ERROR: Missing required fields - salesProspectID or interactionType");
        $response['message'] = 'Prospect ID and interaction type are required.';
        echo json_encode($response);
        exit;
    }

    $data = array(
        'salesProspectID' => (int)$_POST['salesProspectID'],
        'interactionType' => Utility::clean_string($_POST['interactionType']),
        'interactionDate' => isset($_POST['interactionDate']) ? Utility::clean_string($_POST['interactionDate']) : date('Y-m-d H:i:s'),
        'userID' => $userDetails->ID,
        'LastUpdatedByID' => $userDetails->ID
    );

    // Optional fields
    $optionalFields = array('interactionSubject', 'interactionDescription', 'interactionOutcome', 'nextSteps', 'duration', 'attachmentURL', 'relatedActivityID');
    foreach ($optionalFields as $field) {
        if (isset($_POST[$field]) && !empty($_POST[$field])) {
            $data[$field] = Utility::clean_string($_POST[$field]);
        }
    }

    error_log("Data to insert: " . print_r($data, true));

    // Use insert_data method
    $result = $DBConn->insert_data('tija_prospect_interactions', $data);
    error_log("Insert result: " . ($result ? $result : 'FAILED'));

    error_log("Insert result State: " . ($result ? 'SUCCESS' : 'FAILED'));

    if ($result) {
        error_log("Insert result : " . $result);
        $interactionID = $DBConn->lastInsertId();
        error_log("Interaction logged successfully with ID: " . $interactionID);

        // Update lastContactDate on prospect using update_table
        $updateData = array('lastContactDate' => date('Y-m-d'));

        // Update qualification status if provided
        if (isset($_POST['leadQualificationStatus']) && !empty($_POST['leadQualificationStatus'])) {
            $updateData['leadQualificationStatus'] = Utility::clean_string($_POST['leadQualificationStatus']);
            error_log("Updating qualification status to: " . $updateData['leadQualificationStatus']);
        }

        // ===== BANT QUALIFICATION PROCESSING =====

        // 1. Budget Confirmation
        if (isset($_POST['budgetConfirmed']) && $_POST['budgetConfirmed'] === 'Y') {
            if (isset($_POST['confirmedBudget']) && !empty($_POST['confirmedBudget'])) {
                $budget = floatval($_POST['confirmedBudget']);
                if ($budget > 0) {
                    $updateData['confirmedBudget'] = $budget;
                    $updateData['budgetConfirmedDate'] = date('Y-m-d');
                    $updateData['budgetConfirmed'] = 'Y';
                    error_log("Budget confirmed: KES " . number_format($budget, 2));
                }
            }
        }

        // 2. Decision Maker (Authority)
        if (isset($_POST['decisionMakerConfirmed']) && $_POST['decisionMakerConfirmed'] === 'Y') {
            if (isset($_POST['decisionMakerContactID']) && !empty($_POST['decisionMakerContactID'])) {
                $contactID = $_POST['decisionMakerContactID'];

                // Check if we need to create a new contact
                if ($contactID === 'new') {
                    // Validate new contact fields
                    if (empty($_POST['newContactName']) || empty($_POST['newContactEmail']) || empty($_POST['newContactPhone'])) {
                        error_log("ERROR: Missing required new contact fields");
                        $response['message'] = 'Contact name, email, and phone are required to create a new contact.';
                        echo json_encode($response);
                        exit;
                    }

                    // Get prospect details to get clientID
                    $prospect = Sales::sales_prospect_full($data['salesProspectID'], $DBConn);
                    if (!$prospect || !$prospect->clientID) {
                        error_log("ERROR: Prospect has no associated client");
                        $response['message'] = 'Cannot create contact: Prospect has no associated client.';
                        echo json_encode($response);
                        exit;
                    }

                    // Create new contact
                    $newContactData = array(
                        'clientID' => $prospect->clientID,
                        'contactName' => Utility::clean_string($_POST['newContactName']),
                        'contactEmail' => Utility::clean_string($_POST['newContactEmail']),
                        'contactPhone' => Utility::clean_string($_POST['newContactPhone']),
                        'contactTypeID' => isset($_POST['newContactType']) ? (int)$_POST['newContactType'] : null,
                        'isDecisionMaker' => 'Y',
                        'DateAdded' => date('Y-m-d H:i:s'),
                        'LastUpdateByID' => $userDetails->ID
                    );

                    // Create address if provided
                    if (isset($_POST['newContactAddress']) && !empty($_POST['newContactAddress'])) {
                        $addressData = array(
                            'clientID' => $prospect->clientID,
                            'orgDataID' => $prospect->orgDataID,
                            'entityID' => $prospect->entityID,
                            'address' => Utility::clean_string($_POST['newContactAddress']),
                            'addressType' => 'Contact',
                            'billingAddress' => 'N',
                            'headquarters' => 'N',
                            'Suspended' => 'N',
                            'LastUpdateByID' => $userDetails->ID
                        );

                        // Insert address
                        $addressID = $DBConn->insert_data('tija_client_addresses', $addressData);

                        if ($addressID) {
                            $newContactData['clientAddressID'] = $addressID;
                            error_log("Address created with ID: " . $addressID);
                        }
                    }

                    // Insert new contact
                    $contactID = $DBConn->insert_data('tija_client_contacts', $newContactData);

                    if ($contactID) {
                        error_log("New contact created with ID: " . $contactID);
                        $updateData['decisionMakerIdentified'] = 'Y';
                    } else {
                        error_log("ERROR: Failed to create new contact");
                        $response['message'] = 'Failed to create new contact.';
                        echo json_encode($response);
                        exit;
                    }
                } else {
                    // Update existing client contact to mark as decision maker
                    $contactID = (int)$contactID;
                    $contactUpdate = array('isDecisionMaker' => 'Y');
                    $contactWhere = array('clientContactID' => $contactID);
                    $DBConn->update_table('tija_client_contacts', $contactUpdate, $contactWhere);

                    $updateData['decisionMakerIdentified'] = 'Y';
                    error_log("Decision maker confirmed: Contact ID " . $contactID);
                }
            }
        }

        // 3. Need Identification
        if (isset($_POST['needIdentified']) && $_POST['needIdentified'] === 'Y') {
            if (isset($_POST['identifiedNeed']) && !empty($_POST['identifiedNeed'])) {
                $need = Utility::clean_string($_POST['identifiedNeed']);
                if (strlen($need) > 0) {
                    $updateData['identifiedNeed'] = $need;
                    $updateData['needIdentifiedDate'] = date('Y-m-d');
                    $updateData['needIdentified'] = 'Y';
                    error_log("Need identified: " . substr($need, 0, 50) . "...");
                }
            }
        }

        // 4. Timeline Definition
        if (isset($_POST['timelineDefined']) && $_POST['timelineDefined'] === 'Y') {
            if (isset($_POST['expectedTimeline']) && !empty($_POST['expectedTimeline'])) {
                $timeline = Utility::clean_string($_POST['expectedTimeline']);
                if (strtotime($timeline) >= strtotime(date('Y-m-d'))) {
                    $updateData['expectedTimeline'] = $timeline;
                    $updateData['timelineDefinedDate'] = date('Y-m-d');
                    $updateData['timelineDefined'] = 'Y';
                    error_log("Timeline defined: " . $timeline);
                }
            }
        }

        $where = array('salesProspectID' => $data['salesProspectID']);
        $DBConn->update_table('tija_sales_prospects', $updateData, $where);

        error_log("Interaction logged successfully with ID: " . $interactionID);

        $response['success'] = true;
        $response['message'] = 'Interaction logged successfully.';
        $response['data'] = array('interactionID' => $interactionID);
    } else {
        error_log("ERROR: Failed to insert interaction");
        $response['message'] = 'Failed to log interaction.';
    }

    error_log("=========================");
    echo json_encode($response);
}
?>
