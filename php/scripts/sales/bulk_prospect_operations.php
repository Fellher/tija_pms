<?php
/**
 * Bulk Prospect Operations API
 * Handles bulk actions on multiple prospects
 */

session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();

$response = array('success' => false, 'message' => '', 'data' => null);

// Check authentication
if (!isset($userDetails->ID) || empty($userDetails->ID)) {
    $response['message'] = 'Unauthorized access.';
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

$action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : '';
$prospectIDs = isset($_POST['prospectIDs']) ? json_decode($_POST['prospectIDs'], true) : array();

if (empty($prospectIDs) || !is_array($prospectIDs)) {
    $response['message'] = 'No prospects selected.';
    echo json_encode($response);
    exit;
}

try {
    switch ($action) {
        case 'bulkAssignTeam':
            bulkAssignTeam($DBConn, $userDetails, $prospectIDs);
            break;

        case 'bulkUpdateStatus':
            bulkUpdateStatus($DBConn, $userDetails, $prospectIDs);
            break;

        case 'bulkUpdateQualification':
            bulkUpdateQualification($DBConn, $userDetails, $prospectIDs);
            break;

        case 'bulkDelete':
            bulkDelete($DBConn, $userDetails, $prospectIDs);
            break;

        case 'bulkCalculateScores':
            bulkCalculateScores($DBConn, $userDetails, $prospectIDs);
            break;

        default:
            $response['message'] = 'Invalid bulk action.';
            echo json_encode($response);
            exit;
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
    echo json_encode($response);
    exit;
}

function bulkAssignTeam($DBConn, $userDetails, $prospectIDs) {
    global $response;

    if (!isset($_POST['teamID'])) {
        $response['message'] = 'Team ID is required.';
        echo json_encode($response);
        exit;
    }

    $teamID = (int)$_POST['teamID'];
    $placeholders = implode(',', array_fill(0, count($prospectIDs), '?'));

    $sql = "UPDATE tija_sales_prospects
            SET assignedTeamID = ?, LastUpdateByID = ?
            WHERE salesProspectID IN ({$placeholders})";

    $params = array(
        array($teamID, 'i'),
        array($userDetails->ID, 'i')
    );

    foreach ($prospectIDs as $id) {
        $params[] = array((int)$id, 'i');
    }

    $result = $DBConn->execute_query($sql, $params);

    if ($result) {
        $response['success'] = true;
        $response['message'] = count($prospectIDs) . ' prospect(s) assigned to team successfully.';
    } else {
        $response['message'] = 'Failed to assign prospects to team.';
    }

    echo json_encode($response);
}

function bulkUpdateStatus($DBConn, $userDetails, $prospectIDs) {
    global $response;

    if (!isset($_POST['status'])) {
        $response['message'] = 'Status is required.';
        echo json_encode($response);
        exit;
    }

    $status = Utility::clean_string($_POST['status']);
    $placeholders = implode(',', array_fill(0, count($prospectIDs), '?'));

    $sql = "UPDATE tija_sales_prospects
            SET salesProspectStatus = ?, LastUpdateByID = ?
            WHERE salesProspectID IN ({$placeholders})";

    $params = array(
        array($status, 's'),
        array($userDetails->ID, 'i')
    );

    foreach ($prospectIDs as $id) {
        $params[] = array((int)$id, 'i');
    }

    $result = $DBConn->execute_query($sql, $params);

    if ($result) {
        $response['success'] = true;
        $response['message'] = count($prospectIDs) . ' prospect(s) status updated successfully.';
    } else {
        $response['message'] = 'Failed to update prospect status.';
    }

    echo json_encode($response);
}

function bulkUpdateQualification($DBConn, $userDetails, $prospectIDs) {
    global $response;

    if (!isset($_POST['qualification'])) {
        $response['message'] = 'Qualification is required.';
        echo json_encode($response);
        exit;
    }

    $qualification = Utility::clean_string($_POST['qualification']);
    $placeholders = implode(',', array_fill(0, count($prospectIDs), '?'));

    $sql = "UPDATE tija_sales_prospects
            SET leadQualificationStatus = ?, LastUpdateByID = ?
            WHERE salesProspectID IN ({$placeholders})";

    $params = array(
        array($qualification, 's'),
        array($userDetails->ID, 'i')
    );

    foreach ($prospectIDs as $id) {
        $params[] = array((int)$id, 'i');
    }

    $result = $DBConn->execute_query($sql, $params);

    if ($result) {
        // Recalculate scores for all updated prospects
        foreach ($prospectIDs as $id) {
            Sales::calculate_lead_score((int)$id, $DBConn);
        }

        $response['success'] = true;
        $response['message'] = count($prospectIDs) . ' prospect(s) qualification updated successfully.';
    } else {
        $response['message'] = 'Failed to update prospect qualification.';
    }

    echo json_encode($response);
}

function bulkDelete($DBConn, $userDetails, $prospectIDs) {
    global $response;

    $placeholders = implode(',', array_fill(0, count($prospectIDs), '?'));

    $sql = "UPDATE tija_sales_prospects
            SET Suspended = 'Y', LastUpdateByID = ?
            WHERE salesProspectID IN ({$placeholders})";

    $params = array(array($userDetails->ID, 'i'));

    foreach ($prospectIDs as $id) {
        $params[] = array((int)$id, 'i');
    }

    $result = $DBConn->execute_query($sql, $params);

    if ($result) {
        $response['success'] = true;
        $response['message'] = count($prospectIDs) . ' prospect(s) deleted successfully.';
    } else {
        $response['message'] = 'Failed to delete prospects.';
    }

    echo json_encode($response);
}

function bulkCalculateScores($DBConn, $userDetails, $prospectIDs) {
    global $response;

    $successCount = 0;
    foreach ($prospectIDs as $id) {
        $score = Sales::calculate_lead_score((int)$id, $DBConn);
        if ($score !== false) {
            $successCount++;
        }
    }

    $response['success'] = true;
    $response['message'] = "Lead scores calculated for {$successCount} of " . count($prospectIDs) . " prospect(s).";

    echo json_encode($response);
}
?>
