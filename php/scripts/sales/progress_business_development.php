<?php
/**
 * Progress Business Development to Opportunity
 * Handles converting a business development case to an opportunity
 *
 * @author System
 * @version 1.0
 * @date 2025-10-09
 */

session_start();
$base = '../../../';
set_include_path($base);

// Start output buffering to prevent any accidental output
ob_start();

include 'php/includes.php';

// Clean any output from includes
ob_clean();

header('Content-Type: application/json');

$DBConn->begin();
$errors = array();
$response = array('success' => false, 'message' => '', 'data' => null);

try {
    // Validate user authentication
    if (!$isValidUser) {
        throw new Exception('Unauthorized access');
    }

    // Get form data
    $salesCaseID = isset($_POST['salesCaseID']) ? Utility::clean_string($_POST['salesCaseID']) : null;
    $saleStatusLevelID = isset($_POST['saleStatusLevelID']) ? Utility::clean_string($_POST['saleStatusLevelID']) : null;
    $expectedCloseDate = isset($_POST['expectedCloseDate']) ? Utility::clean_string($_POST['expectedCloseDate']) : null;
    $probability = isset($_POST['probability']) ? Utility::clean_string($_POST['probability']) : null;
    $salesCaseEstimate = isset($_POST['salesCaseEstimate']) ? Utility::clean_string($_POST['salesCaseEstimate']) : null;
    $leadSourceID = isset($_POST['leadSourceID']) ? Utility::clean_string($_POST['leadSourceID']) : null;
    $salesCaseContactID = isset($_POST['salesCaseContactID']) ? Utility::clean_string($_POST['salesCaseContactID']) : null;
    $progressNotes = isset($_POST['progressNotes']) ? Utility::clean_string($_POST['progressNotes']) : null;
    $clientID = isset($_POST['clientID']) ? Utility::clean_string($_POST['clientID']) : null;
    $businessUnitID = isset($_POST['businessUnitID']) ? Utility::clean_string($_POST['businessUnitID']) : null;
    $salesPersonID = isset($_POST['salesPersonID']) ? Utility::clean_string($_POST['salesPersonID']) : $userDetails->ID;
    $entityID = isset($_POST['entityID']) ? Utility::clean_string($_POST['entityID']) : $userDetails->entityID;
    $orgDataID = isset($_POST['orgDataID']) ? Utility::clean_string($_POST['orgDataID']) : $userDetails->orgDataID;

    // Debug logging
    error_log("Progress Business Development - POST Data:");
    error_log("  salesCaseID: " . ($salesCaseID ?? 'NULL'));
    error_log("  expectedCloseDate: " . ($expectedCloseDate ?? 'NULL'));
    error_log("  probability: " . ($probability ?? 'NULL'));
    error_log("  saleStatusLevelID: " . ($saleStatusLevelID ?? 'NULL'));

    // Validate required fields
    if (empty($salesCaseID)) {
        throw new Exception('Sales Case ID is required');
    }

    if (empty($saleStatusLevelID)) {
        throw new Exception('Please select an opportunity status level');
    }

    // MANDATORY: Expected Close Date
    if (empty($expectedCloseDate)) {
        throw new Exception('Expected Close Date is required when progressing to opportunity stage');
    }

    // MANDATORY: Probability (auto-populated from status level)
    if (empty($probability)) {
        throw new Exception('Probability is required. Please select a status level.');
    }

    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $expectedCloseDate)) {
        throw new Exception('Invalid date format for Expected Close Date');
    }

    // Validate that expected close date is not in the past
    if (strtotime($expectedCloseDate) < strtotime(date('Y-m-d'))) {
        throw new Exception('Expected Close Date cannot be in the past');
    }

    // Handle new contact creation if needed
    if ($salesCaseContactID === 'addNew') {
        $contactFirstName = isset($_POST['contactFirstName']) ? Utility::clean_string($_POST['contactFirstName']) : null;
        $contactLastName = isset($_POST['contactLastName']) ? Utility::clean_string($_POST['contactLastName']) : null;
        $contactEmail = isset($_POST['contactEmail']) ? Utility::clean_string($_POST['contactEmail']) : null;
        $contactTitle = isset($_POST['contactTitle']) ? Utility::clean_string($_POST['contactTitle']) : null;
        $contactPhone = isset($_POST['contactTelephone']) ? Utility::clean_string($_POST['contactTelephone']) : null;
        $contactTypeID = isset($_POST['contactTypeID']) ? Utility::clean_string($_POST['contactTypeID']) : null;

        if (empty($contactFirstName) || empty($contactLastName) || empty($contactEmail)) {
            throw new Exception('Contact first name, last name, and email are required');
        }

        // Create new contact
        $contactName = $contactFirstName . ' ' . $contactLastName;
        $contactData = array(
            'contactName' => $contactName,
            'title' => $contactTitle,
            'contactEmail' => $contactEmail,
            'contactPhone' => $contactPhone,  // Correct column name
            'clientID' => $clientID,
            'contactTypeID' => $contactTypeID,
            'DateAdded' => date('Y-m-d H:i:s'),
            'LastUpdate' => date('Y-m-d H:i:s'),
            'LastUpdateByID' => $userDetails->ID,
            'Suspended' => 'N'
        );

        $newContactID = $DBConn->insert_data('tija_client_contacts', $contactData);
        if ($newContactID) {
            $salesCaseContactID = $newContactID;
        } else {
            throw new Exception('Failed to create new contact');
        }
    }

    // Update the sales case
    $updateData = array(
        'saleStage' => 'opportunities',
        'saleStatusLevelID' => $saleStatusLevelID,
        'expectedCloseDate' => $expectedCloseDate,  // MANDATORY
        'probability' => $probability,  // MANDATORY - auto-populated from status level
        'LastUpdate' => date('Y-m-d H:i:s'),
        'LastUpdatedByID' => $userDetails->ID
    );

    // Add optional fields if provided
    if (!empty($salesCaseEstimate)) {
        $updateData['salesCaseEstimate'] = $salesCaseEstimate;
    }

    if (!empty($leadSourceID)) {
        $updateData['leadSourceID'] = $leadSourceID;
    }

    if (!empty($salesCaseContactID) && $salesCaseContactID !== 'addNew') {
        $updateData['salesCaseContactID'] = $salesCaseContactID;
    }

    // Update the sales case
    $whereClause = array('salesCaseID' => $salesCaseID);
    $updated = $DBConn->update_table('tija_sales_cases', $updateData, $whereClause);

    if (!$updated) {
        error_log("Failed to update sales case - Update returned false");
        throw new Exception('Failed to update sales case');
    }

    // Log successful update
    error_log("Sales case updated successfully:");
    error_log("  Updated expectedCloseDate: " . $updateData['expectedCloseDate']);
    error_log("  Updated probability: " . $updateData['probability']);
    error_log("  Updated saleStatusLevelID: " . $updateData['saleStatusLevelID']);

    // Create progress record if notes provided
    if (!empty($progressNotes)) {
        $progressData = array(
            'salesCaseID' => $salesCaseID,
            'salesPersonID' => $salesPersonID,
            'saleStatusLevelID' => $saleStatusLevelID,
            'progressNotes' => $progressNotes,  // Correct column name
            'progressPercentage' => 0, // Can be calculated based on status level
            'clientID' => $clientID,
            'businessUnitID' => $businessUnitID,
            'orgDataID' => $orgDataID,
            'entityID' => $entityID,
            'DateAdded' => date('Y-m-d H:i:s'),
            'Suspended' => 'N'
        );

        $DBConn->insert_data('tija_sales_progress', $progressData);
    }

    $DBConn->commit();

    // Get the section and subsection for URL building
    $s = isset($_POST['s']) ? Utility::clean_string($_POST['s']) : 'user';
    $ss = isset($_POST['ss']) ? Utility::clean_string($_POST['ss']) : 'sales';

    // Build redirect URL to sale details page (use web root path, not relative path)
    // $base is '../../../' which is filesystem relative, we need web path
    $webBase = '/pms_skim.co.ke_rev/';  // Adjust if your site is at a different path
    $redirectUrl = "{$webBase}html/?s={$s}&ss={$ss}&p=sale_details&saleid={$salesCaseID}";

    // Log for debugging
    error_log("Progress Success - Redirect URL: " . $redirectUrl);
    error_log("Sales Case ID: " . $salesCaseID);
    error_log("Section: " . $s . ", Subsection: " . $ss);

    $response['success'] = true;
    $response['message'] = 'Successfully progressed to opportunity! Redirecting to sales details...';
    $response['data'] = array(
        'salesCaseID' => $salesCaseID,
        'newStage' => 'opportunities',
        'statusLevelID' => $saleStatusLevelID,
        'redirectUrl' => $redirectUrl
    );

} catch (Exception $e) {
    $DBConn->rollback();

    // Build return URL to sales home (use web root path)
    $s = isset($_POST['s']) ? Utility::clean_string($_POST['s']) : 'user';
    $ss = isset($_POST['ss']) ? Utility::clean_string($_POST['ss']) : 'sales';
    $webBase = '/pms_skim.co.ke_rev/';  // Adjust if your site is at a different path
    $returnUrl = "{$webBase}html/?s={$s}&ss={$ss}&p=home&state=business_development";

    $response['success'] = false;
    $response['message'] = $e->getMessage();
    $response['returnUrl'] = $returnUrl;

    error_log('Progress Business Development Error: ' . $e->getMessage());
}

// Clear any buffered output and send clean JSON
ob_end_clean();
echo json_encode($response);
exit;

