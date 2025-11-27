<?php
session_start();
$base = '../../../../';
set_include_path($base);

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'php/includes.php';

$response = ['success' => false, 'message' => ''];

try {
    // Check admin access
    if (!$isValidAdmin && !$isAdmin) {
        throw new Exception("You are not authorized to perform this action.");
    }

    $DBConn->begin();

    // Get form data
    $relationshipID = isset($_POST['relationshipID']) && !empty($_POST['relationshipID'])
        ? Utility::clean_string($_POST['relationshipID']) : null;
    $employeeID = isset($_POST['employeeID']) && !empty($_POST['employeeID'])
        ? Utility::clean_string($_POST['employeeID']) : null;
    $supervisorID = isset($_POST['supervisorID']) && !empty($_POST['supervisorID']) && $_POST['supervisorID'] != '0'
        ? Utility::clean_string($_POST['supervisorID']) : null;
    $relationshipType = isset($_POST['relationshipType']) && !empty($_POST['relationshipType'])
        ? Utility::clean_string($_POST['relationshipType']) : 'Direct';
    $relationshipStrength = isset($_POST['relationshipStrength']) && !empty($_POST['relationshipStrength'])
        ? Utility::clean_string($_POST['relationshipStrength']) : 'Primary';
    $effectiveDate = isset($_POST['effectiveDate']) && !empty($_POST['effectiveDate'])
        ? Utility::clean_string($_POST['effectiveDate']) : date('Y-m-d');
    $endDate = isset($_POST['endDate']) && !empty($_POST['endDate'])
        ? Utility::clean_string($_POST['endDate']) : null;
    $isCurrent = isset($_POST['isCurrent']) && !empty($_POST['isCurrent'])
        ? Utility::clean_string($_POST['isCurrent']) : 'Y';
    $reportingFrequency = isset($_POST['reportingFrequency']) && !empty($_POST['reportingFrequency'])
        ? Utility::clean_string($_POST['reportingFrequency']) : 'Weekly';
    $canDelegate = isset($_POST['canDelegate'])
        ? Utility::clean_string($_POST['canDelegate']) : 'N';
    $canSubstitute = isset($_POST['canSubstitute'])
        ? Utility::clean_string($_POST['canSubstitute']) : 'N';
    $notes = isset($_POST['notes']) && !empty($_POST['notes'])
        ? Utility::clean_string($_POST['notes']) : null;

    // Validate required fields
    if (!$employeeID) {
        throw new Exception("Employee is required");
    }

    // Get employee details for entity and org
    $employee = Employee::employees(['ID' => $employeeID], true, $DBConn);
    if (!$employee) {
        throw new Exception("Employee not found");
    }

    // Validate dates
    if ($endDate && strtotime($endDate) < strtotime($effectiveDate)) {
        throw new Exception("End date cannot be before effective date");
    }

    // Prepare relationship data
    $data = [
        'employeeID' => $employeeID,
        'supervisorID' => $supervisorID,
        'relationshipType' => $relationshipType,
        'relationshipStrength' => $relationshipStrength,
        'effectiveDate' => $effectiveDate,
        'endDate' => $endDate,
        'isCurrent' => $isCurrent,
        'reportingFrequency' => $reportingFrequency,
        'canDelegate' => $canDelegate,
        'canSubstitute' => $canSubstitute,
        'notes' => $notes,
        'relationshipID' => $relationshipID
    ];

    // Use Reporting class to update (handles both new table and legacy)
    if (Reporting::updateReportingRelationship($data, $DBConn)) {
        $DBConn->commit();
        $response['success'] = true;
        $response['message'] = $relationshipID
            ? 'Reporting relationship updated successfully'
            : 'Reporting relationship created successfully';
    } else {
        throw new Exception("Failed to save reporting relationship");
    }

} catch (Exception $e) {
    if ($DBConn) {
        $DBConn->rollback();
    }
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
?>
