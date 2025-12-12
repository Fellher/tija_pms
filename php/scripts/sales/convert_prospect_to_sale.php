<?php
/**
 * Convert Prospect to Sale API
 * Handles the conversion of qualified prospects to sales opportunities
 */


session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();

// Check authentication
if (!isset($userDetails->ID) || empty($userDetails->ID)) {
    die('Unauthorized access.');
}

$userID = $userDetails->ID;



// Get action
$action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : '';

// DEBUG: Log request
error_log("=== CONVERT PROSPECT TO SALE ===");
error_log("POST data: " . print_r($_POST, true));
error_log("User ID: " . $userDetails->ID);

try {
    switch ($action) {
        case 'convertToSale':
            convertProspectToSale($DBConn, $userDetails);
            break;

        case 'getSalesStages':
            getSalesStages($DBConn, $userDetails);
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
 * Get available sales stages from tija_sales_status_levels
 */
function getSalesStages($DBConn, $userDetails) {
    global $response;

    $entityID = isset($_POST['entityID']) ? (int)$_POST['entityID'] : null;

    // Fetch sales stages
    $where = array();
    if ($entityID) {
        $where['entityID'] = $entityID;
    }

    $stages = $DBConn->retrieve_db_table_rows(
        'tija_sales_status_levels',
        array('saleStatusLevelID', 'statusLevel', 'StatusLevelDescription', 'levelPercentage'),
        $where
    );

    if ($stages) {
        $response['success'] = true;
        $response['data'] = $stages;
    } else {
        $response['message'] = 'No sales stages found.';
    }

    echo json_encode($response);
    exit;
}

/**
 * Convert a qualified prospect to a sale
 */
function convertProspectToSale($DBConn, $userDetails) {
    global $response;

    // Validate required fields
    if (!isset($_POST['salesProspectID']) || !isset($_POST['saleStatusLevelID'])) {
        $response['message'] = 'Prospect ID and sales stage are required.';
        echo json_encode($response);
        exit;
    }

    $prospectID = (int)$_POST['salesProspectID'];
    $salesStageID = (int)$_POST['saleStatusLevelID'];

    error_log("Converting prospect ID: $prospectID to sale with stage: $salesStageID");

    // Fetch prospect details
    $prospect = Sales::sales_prospect_full($prospectID, $DBConn);

    if (!$prospect) {
        $response['message'] = 'Prospect not found.';
        echo json_encode($response);
        exit;
    }

    // Validate prospect is qualified
    if ($prospect->leadQualificationStatus !== 'qualified') {
        $response['message'] = 'Only qualified prospects can be converted to sales.';
        echo json_encode($response);
        exit;
    }

    // Check if already converted
    if ($prospect->convertedToSale === 'Y') {
        $response['message'] = 'This prospect has already been converted to a sale.';
        echo json_encode($response);
        exit;
    }

    // Prepare sales data
    $saleData = array(
        'salesCaseName' => Utility::clean_string($_POST['saleCaseName'] ?? $prospect->salesProspectName),
        'saleCaseDescription' => Utility::clean_string($_POST['saleDescription'] ?? $prospect->prospectCaseName),
        'salesCaseEstimate' => isset($_POST['saleValue']) ? (float)$_POST['saleValue'] : (float)$prospect->estimatedValue,
        'probability' => isset($_POST['probability']) ? (int)$_POST['probability'] : (int)$prospect->probability,
        'saleStatusLevelID' => $salesStageID,
        'salesPersonID' => isset($_POST['saleOwnerID']) ? (int)$_POST['saleOwnerID'] : (int)$prospect->ownerID,
        'businessUnitID' => $prospect->businessUnitID,
        'expectedCloseDate' => isset($_POST['expectedCloseDate']) ? Utility::clean_string($_POST['expectedCloseDate']) : $prospect->expectedCloseDate,
        'clientID' => $prospect->clientID,
        'orgDataID' => $prospect->orgDataID,
        'entityID' => $prospect->entityID,
        'leadSourceID' => $prospect->leadSourceID ?? "",
        'assignedTeamID' => isset($_POST['assignedTeamID']) ? (int)$_POST['assignedTeamID'] : $prospect->assignedTeamID,

        'LastUpdatedByID' => $userDetails->ID
    );

    error_log("Sale data to insert: " . print_r($saleData, true));

    // Create sale record
    $saleResult = $DBConn->insert_data('tija_sales_cases', $saleData);

    if (!$saleResult) {
        error_log("ERROR: Failed to create sale record");
        $response['message'] = 'Failed to create sale record.';
        echo json_encode($response);
        exit;
    }

    $salesCaseID = $DBConn->lastInsertId();
    error_log("Sale created successfully with ID: $salesCaseID");

    // Update prospect - deactivate and link to sale
    $prospectUpdate = array(
        'salesProspectStatus' => 'closed',
        'Suspended' => 'Y',
        'convertedToSale' => 'Y',
        'salesCaseID' => $salesCaseID,
        'conversionDate' => date('Y-m-d H:i:s'),
        'convertedByID' => $userDetails->ID,
        'LastUpdateByID' => $userDetails->ID
    );

    $updateResult = $DBConn->update_table(
        'tija_sales_prospects',
        $prospectUpdate,
        array('salesProspectID' => $prospectID)
    );

    if (!$updateResult) {
        error_log("ERROR: Failed to update prospect");
        $response['message'] = 'Sale created but failed to update prospect.';
        echo json_encode($response);
        exit;
    }

    error_log("Prospect updated successfully - deactivated and linked to sale");

    $response['success'] = true;
    $response['message'] = 'Prospect successfully converted to sale!';
    $response['data'] = array(
        'salesCaseID' => $salesCaseID,
        'prospectID' => $prospectID
    );

    error_log("=========================");
    echo json_encode($response);
}
?>
