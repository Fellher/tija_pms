<?php
/**
 * Manage Business Development
 * Handles creating and editing business development cases (prospects)
 *
 * @author System
 * @version 2.0
 * @date 2025-10-09
 */

session_start();
$base = '../../../';
set_include_path($base);

// Start output buffering
ob_start();

include 'php/includes.php';

// Clean any output from includes
ob_clean();

header('Content-Type: application/json');

$DBConn->begin();
$response = array('success' => false, 'message' => '', 'data' => null);

try {
    // Validate user authentication
    if (!$isValidUser) {
        throw new Exception('Unauthorized access');
    }

    // Get form data
    $salesCaseID = isset($_POST['salesCaseID']) ? Utility::clean_string($_POST['salesCaseID']) : null;
    $salesCaseName = isset($_POST['salesCaseName']) ? Utility::clean_string($_POST['salesCaseName']) : null;
    $clientID = isset($_POST['clientID']) ? Utility::clean_string($_POST['clientID']) : null;
    $clientContactID = isset($_POST['clientContactID']) ? Utility::clean_string($_POST['clientContactID']) : null;
    $businessUnitID = isset($_POST['businessUnitID']) ? Utility::clean_string($_POST['businessUnitID']) : null;
    $salesPersonID = isset($_POST['salesPersonID']) ? Utility::clean_string($_POST['salesPersonID']) : $userDetails->ID;
    $expectedRevenue = isset($_POST['expectedRevenue']) ? Utility::clean_string($_POST['expectedRevenue']) : null;
    $orgDataID = isset($_POST['orgDataID']) ? Utility::clean_string($_POST['orgDataID']) : $userDetails->orgDataID;
    $entityID = isset($_POST['entityID']) ? Utility::clean_string($_POST['entityID']) : $userDetails->entityID;

    // Validate required fields
    if (empty($salesCaseName)) {
        throw new Exception('Prospect/Opportunity name is required');
    }

    // Handle new client creation
    if ($clientID === 'newClient') {
        $newClientName = isset($_POST['newClientName']) ? Utility::clean_string($_POST['newClientName']) : null;
        $countryID = isset($_POST['countryID']) ? Utility::clean_string($_POST['countryID']) : null;
        $city = isset($_POST['city']) ? Utility::clean_string($_POST['city']) : null;

        if (empty($newClientName)) {
            throw new Exception('New client name is required');
        }

        // Create new client
        $clientData = array(
            'clientName' => $newClientName,
            'clientCode' => Utility::clientCode($newClientName),
            'accountOwnerID' => $salesPersonID,
            'orgDataID' => $orgDataID,
            'entityID' => $entityID,
            'countryID' => $countryID,
            'city' => $city,
            'clientStatus' => 'active',
            'DateAdded' => date('Y-m-d H:i:s'),
            'LastUpdate' => date('Y-m-d H:i:s'),
            'LastUpdateByID' => $userDetails->ID,
            'Suspended' => 'N'
        );

        $newClientID = $DBConn->insert_data('tija_clients', $clientData);
        if ($newClientID) {
            $clientID = $newClientID;
        } else {
            throw new Exception('Failed to create new client');
        }
    }

    // Validate client
    if (empty($clientID) || $clientID === 'newClient') {
        throw new Exception('Client is required');
    }

    // Handle new contact creation
    if ($clientContactID === 'newContact') {
        $contactName = isset($_POST['contactName']) ? Utility::clean_string($_POST['contactName']) : null;
        $contactEmail = isset($_POST['contactEmail']) ? Utility::clean_string($_POST['contactEmail']) : null;
        $contactPhone = isset($_POST['contactPhone']) ? Utility::clean_string($_POST['contactPhone']) : null;
        $contactPersonTitle = isset($_POST['contactPersonTitle']) ? Utility::clean_string($_POST['contactPersonTitle']) : null;

        if (empty($contactName) || empty($contactEmail)) {
            throw new Exception('Contact name and email are required');
        }

        // Create new contact
        $contactData = array(
            'contactName' => $contactName,
            'contactEmail' => $contactEmail,
            'contactPhone' => $contactPhone,
            'title' => $contactPersonTitle,
            'clientID' => $clientID,
            'DateAdded' => date('Y-m-d H:i:s'),
            'LastUpdate' => date('Y-m-d H:i:s'),
            'LastUpdateByID' => $userDetails->ID,
            'Suspended' => 'N'
        );

        $newContactID = $DBConn->insert_data('tija_client_contacts', $contactData);
        if ($newContactID) {
            $clientContactID = $newContactID;
        } else {
            throw new Exception('Failed to create new contact');
        }
    }

    // Handle new business unit creation
    if ($businessUnitID === 'newUnit') {
        $newBusinessUnit = isset($_POST['newBusinessUnit']) ? Utility::clean_string($_POST['newBusinessUnit']) : null;

        if (empty($newBusinessUnit)) {
            throw new Exception('New business unit name is required');
        }

        // Create new business unit
        $unitData = array(
            'businessUnitName' => $newBusinessUnit,
            'orgDataID' => $orgDataID,
            'entityID' => $entityID,
            'DateAdded' => date('Y-m-d H:i:s'),
            'LastUpdate' => date('Y-m-d H:i:s'),
            'LastUpdateByID' => $userDetails->ID,
            'Suspended' => 'N'
        );

        $newUnitID = $DBConn->insert_data('tija_business_units', $unitData);
        if ($newUnitID) {
            $businessUnitID = $newUnitID;
        } else {
            throw new Exception('Failed to create new business unit');
        }
    }

    // Validate business unit
    if (empty($businessUnitID) || $businessUnitID === 'newUnit') {
        throw new Exception('Business unit is required');
    }

    // Create or update sales case
    if (empty($salesCaseID)) {
        // Create new business development case
        $caseData = array(
            'salesCaseName' => $salesCaseName,
            'clientID' => $clientID,
            'salesCaseContactID' => ($clientContactID && $clientContactID !== 'newContact') ? $clientContactID : null,
            'businessUnitID' => $businessUnitID,
            'salesPersonID' => $salesPersonID,
            'salesCaseEstimate' => $expectedRevenue,
            'saleStage' => 'business_development',
            'orgDataID' => $orgDataID,
            'entityID' => $entityID,
            'DateAdded' => date('Y-m-d H:i:s'),
            'LastUpdate' => date('Y-m-d H:i:s'),
            'LastUpdatedByID' => $userDetails->ID,
            'Suspended' => 'N'
        );

        $newCaseID = $DBConn->insert_data('tija_sales_cases', $caseData);
        if ($newCaseID) {
            $salesCaseID = $newCaseID;
            $message = 'Business development prospect added successfully';
        } else {
            throw new Exception('Failed to create business development case');
        }
    } else {
        // Update existing case
        $updateData = array(
            'salesCaseName' => $salesCaseName,
            'clientID' => $clientID,
            'businessUnitID' => $businessUnitID,
            'salesPersonID' => $salesPersonID,
            'LastUpdate' => date('Y-m-d H:i:s'),
            'LastUpdatedByID' => $userDetails->ID
        );

        if ($expectedRevenue) {
            $updateData['salesCaseEstimate'] = $expectedRevenue;
        }

        if ($clientContactID && $clientContactID !== 'newContact') {
            $updateData['salesCaseContactID'] = $clientContactID;
        }

        $updated = $DBConn->update_table('tija_sales_cases', $updateData, array('salesCaseID' => $salesCaseID));
        if (!$updated) {
            throw new Exception('Failed to update business development case');
        }
        $message = 'Business development prospect updated successfully';
    }

    $DBConn->commit();

    // Build redirect URL (relative path - JavaScript will prepend base)
    $redirectUrl = "?s=user&ss=sales&p=home&state=business_development";

    $response['success'] = true;
    $response['message'] = $message;
    $response['data'] = array(
        'salesCaseID' => $salesCaseID,
        'redirectUrl' => $redirectUrl
    );

} catch (Exception $e) {
    $DBConn->rollback();

    $response['success'] = false;
    $response['message'] = $e->getMessage();

    error_log('Manage Business Development Error: ' . $e->getMessage());
}

// Clear buffer and send clean JSON
ob_end_clean();
echo json_encode($response);
exit;

