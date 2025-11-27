<?php
/**
 * Get Billable Data Script
 * Returns billable hours, expenses, or fee expenses for a project
 *
 * @package    TIJA_PMS
 * @subpackage Invoicing
 */

session_start();
$base = '../../../';
set_include_path($base);
include 'php/includes.php';

header('Content-Type: application/json');

// Check authentication
if (!$isValidUser) {
    echo json_encode(array('success' => false, 'message' => 'Unauthorized access'));
    exit;
}

$type = isset($_GET['type']) ? Utility::clean_string($_GET['type']) : '';
$projectID = isset($_GET['projectID']) ? intval($_GET['projectID']) : 0;
$dateFrom = isset($_GET['dateFrom']) ? Utility::clean_string($_GET['dateFrom']) : null;
$dateTo = isset($_GET['dateTo']) ? Utility::clean_string($_GET['dateTo']) : null;

if (!$projectID) {
    echo json_encode(array('success' => false, 'message' => 'Project ID is required'));
    exit;
}

try {
    switch ($type) {
        case 'hours':
            $data = Invoice::get_billable_hours($projectID, $dateFrom, $dateTo, $DBConn);
            break;

        case 'expenses':
            $data = Invoice::get_billable_expenses($projectID, $dateFrom, $dateTo, $DBConn);
            break;

        case 'fee_expenses':
            $data = Invoice::get_billable_fee_expenses($projectID, $dateFrom, $dateTo, $DBConn);
            break;

        default:
            echo json_encode(array('success' => false, 'message' => 'Invalid type'));
            exit;
    }

    echo json_encode(array('success' => true, 'data' => $data));
} catch (Exception $e) {
    echo json_encode(array('success' => false, 'message' => 'Error: ' . $e->getMessage()));
}
?>

