<?php
/**
 * Function Head Assignment API
 *
 * Manage function head assignments
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

require_once __DIR__ . '/../../../includes.php';

header('Content-Type: application/json');

global $DBConn, $isValidUser, $userID;

// Check authentication
if (!$isValidUser) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check admin permissions
if (!$isAdmin && !$isValidAdmin) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Administrator privileges required']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($method) {
        case 'POST':
            if ($action === 'create') {
                $data = [
                    'employeeID' => $_POST['employeeID'] ?? null,
                    'functionalArea' => $_POST['functionalArea'] ?? '',
                    'isActive' => $_POST['isActive'] ?? 'Y',
                    'assignedByID' => $userID
                ];

                if (!$data['employeeID'] || empty($data['functionalArea'])) {
                    throw new Exception('Employee ID and functional area are required');
                }

                // Check if assignment already exists
                $existing = $DBConn->retrieve_db_table_rows('tija_function_head_assignments',
                    ['assignmentID'],
                    ['employeeID' => $data['employeeID'], 'functionalArea' => $data['functionalArea'], 'Suspended' => 'N']);

                if ($existing && count($existing) > 0) {
                    throw new Exception('Function head assignment already exists for this employee and functional area');
                }

                $cols = ['employeeID', 'functionalArea', 'isActive', 'assignedByID'];
                $assignmentID = $DBConn->insert_db_table_row('tija_function_head_assignments', $cols, $data);

                if ($assignmentID) {
                    echo json_encode(['success' => true, 'assignmentID' => $assignmentID, 'message' => 'Function head assigned successfully']);
                } else {
                    throw new Exception('Failed to create assignment');
                }
            } elseif ($action === 'update') {
                $assignmentID = $_POST['assignmentID'] ?? null;
                if (!$assignmentID) {
                    throw new Exception('Assignment ID is required');
                }

                $data = [];
                $allowedFields = ['employeeID', 'functionalArea', 'isActive'];
                foreach ($allowedFields as $field) {
                    if (isset($_POST[$field])) {
                        $data[$field] = $_POST[$field];
                    }
                }

                if (empty($data)) {
                    throw new Exception('No fields to update');
                }

                $success = $DBConn->update_db_table_row('tija_function_head_assignments',
                    ['assignmentID' => $assignmentID],
                    $data);

                if ($success) {
                    echo json_encode(['success' => true, 'message' => 'Assignment updated successfully']);
                } else {
                    throw new Exception('Failed to update assignment');
                }
            } elseif ($action === 'delete') {
                $assignmentID = $_POST['assignmentID'] ?? null;
                if (!$assignmentID) {
                    throw new Exception('Assignment ID is required');
                }

                $success = $DBConn->update_db_table_row('tija_function_head_assignments',
                    ['assignmentID' => $assignmentID],
                    ['Suspended' => 'Y']);

                if ($success) {
                    echo json_encode(['success' => true, 'message' => 'Assignment removed successfully']);
                } else {
                    throw new Exception('Failed to remove assignment');
                }
            } elseif ($action === 'toggle') {
                $assignmentID = $_POST['assignmentID'] ?? null;
                $isActive = $_POST['isActive'] ?? 'Y';

                if (!$assignmentID) {
                    throw new Exception('Assignment ID is required');
                }

                $success = $DBConn->update_db_table_row('tija_function_head_assignments',
                    ['assignmentID' => $assignmentID],
                    ['isActive' => $isActive]);

                if ($success) {
                    $status = $isActive === 'Y' ? 'activated' : 'deactivated';
                    echo json_encode(['success' => true, 'message' => "Assignment {$status} successfully"]);
                } else {
                    throw new Exception('Failed to update assignment status');
                }
            } else {
                throw new Exception('Invalid action');
            }
            break;

        case 'GET':
            if ($action === 'get') {
                $assignmentID = $_GET['assignmentID'] ?? null;
                if (!$assignmentID) {
                    throw new Exception('Assignment ID is required');
                }

                $assignment = $DBConn->retrieve_db_table_rows('tija_function_head_assignments',
                    ['assignmentID', 'employeeID', 'functionalArea', 'isActive', 'DateAdded'],
                    ['assignmentID' => $assignmentID, 'Suspended' => 'N'],
                    true);

                if ($assignment) {
                    echo json_encode(['success' => true, 'assignment' => $assignment]);
                } else {
                    throw new Exception('Assignment not found');
                }
            } else {
                throw new Exception('Invalid action');
            }
            break;

        default:
            throw new Exception('Method not allowed');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

