<?php
/**
 * Supervisor Relationships API
 * Handles CRUD operations for supervisor relationships
 */

session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

try {
    if (!$isValidUser) {
        throw new Exception('You must be logged in to perform this action');
    }

    $action = isset($_GET['action']) ? Utility::clean_string($_GET['action']) : (isset($_POST['action']) ? Utility::clean_string($_POST['action']) : '');

    if (!$action) {
        throw new Exception('No action specified');
    }

    $DBConn->begin();

    switch ($action) {
        // ========================================
        // SUPERVISOR RELATIONSHIP OPERATIONS
        // ========================================

        case 'save_supervisor_relationship':
            $relationshipID = isset($_POST['relationshipID']) && !empty($_POST['relationshipID']) ?
                Utility::clean_string($_POST['relationshipID']) : null;
            $employeeID = Utility::clean_string($_POST['employeeID'] ?? '');
            $supervisorID = Utility::clean_string($_POST['supervisorID'] ?? '');

            if (empty($employeeID)) {
                throw new Exception('Employee ID is required');
            }

            if (empty($supervisorID)) {
                throw new Exception('Supervisor ID is required');
            }

            // Prevent self-supervision
            if ($employeeID == $supervisorID) {
                throw new Exception('An employee cannot supervise themselves');
            }

            // Process dates
            $effectiveDate = null;
            if (isset($_POST['effectiveDate']) && !empty($_POST['effectiveDate'])) {
                $date = trim($_POST['effectiveDate']);
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    $effectiveDate = $date;
                }
            }

            $endDate = null;
            if (isset($_POST['endDate']) && !empty($_POST['endDate'])) {
                $date = trim($_POST['endDate']);
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    $endDate = $date;
                }
            }

            $data = [
                'employeeID' => $employeeID,
                'supervisorID' => $supervisorID,
                'relationshipType' => Utility::clean_string($_POST['relationshipType'] ?? 'direct'),
                'isPrimary' => isset($_POST['isPrimary']) && $_POST['isPrimary'] == 'Y' ? 'Y' : 'N',
                'percentage' => floatval($_POST['percentage'] ?? 100),
                'effectiveDate' => $effectiveDate,
                'endDate' => $endDate,
                'isActive' => isset($_POST['isActive']) && $_POST['isActive'] == 'Y' ? 'Y' : 'N',
                'scope' => Utility::clean_string($_POST['scope'] ?? ''),
                'department' => Utility::clean_string($_POST['department'] ?? ''),
                'notes' => Utility::clean_string($_POST['notes'] ?? ''),
                'updatedBy' => $userDetails->ID,
                'Suspended' => 'N',
                'Lapsed' => 'N'
            ];

            // If setting as primary, update the primary supervisor in user_details
            if ($data['isPrimary'] == 'Y') {
                // Unset other primary supervisors for this employee
                $DBConn->update_table('tija_employee_supervisor_relationships',
                    ['isPrimary' => 'N'],
                    ['employeeID' => $employeeID]);

                // Update primary supervisor in user_details table
                $DBConn->update_table('user_details',
                    ['supervisorID' => $supervisorID],
                    ['ID' => $employeeID]);
            }

            if ($relationshipID) {
                // Update existing relationship
                $updateResult = $DBConn->update_table('tija_employee_supervisor_relationships',
                    $data, ['relationshipID' => $relationshipID]);
                if ($updateResult === false) {
                    $error = $DBConn->error ?? 'Unknown database error';
                    throw new Exception('Failed to update supervisor relationship: ' . $error);
                }
                $response['message'] = 'Supervisor relationship updated successfully';
            } else {
                // Create new relationship
                $data['createdBy'] = $userDetails->ID;

                $insertResult = $DBConn->insert_data('tija_employee_supervisor_relationships', $data);
                if ($insertResult === false) {
                    $error = $DBConn->error ?? 'Unknown database error';
                    throw new Exception('Failed to create supervisor relationship: ' . $error);
                }
                $response['message'] = 'Supervisor relationship created successfully';
            }

            $response['success'] = true;
            break;

        case 'get_supervisor_relationship':
            $relationshipID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$relationshipID) {
                throw new Exception('Relationship ID is required');
            }

            $relationship = Employee::get_supervisor_relationship($relationshipID, $DBConn);

            if (!$relationship) {
                throw new Exception('Supervisor relationship not found');
            }

            $response['success'] = true;
            $response['data'] = $relationship;
            break;

        case 'delete_supervisor_relationship':
            $relationshipID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$relationshipID) {
                throw new Exception('Relationship ID is required');
            }

            $result = Employee::delete_supervisor_relationship($relationshipID, $DBConn);

            if (!$result['success']) {
                throw new Exception($result['message']);
            }

            $response['success'] = true;
            $response['message'] = $result['message'];
            break;

        case 'get_supervisors':
            // Get all supervisors for an employee
            $employeeID = isset($_GET['employeeID']) ? Utility::clean_string($_GET['employeeID']) : null;
            if (!$employeeID) {
                throw new Exception('Employee ID is required');
            }

            $supervisors = Employee::get_additional_supervisors($employeeID, $DBConn);

            $response['success'] = true;
            $response['data'] = $supervisors ?? [];
            break;

        default:
            throw new Exception('Invalid action specified');
    }

    if ($response['success']) {
        $DBConn->commit();
    } else {
        $DBConn->rollback();
    }

} catch (Exception $e) {
    $DBConn->rollback();
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>

