<?php
/**
 * Process Group Management API
 *
 * Create, update, delete process groups
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
                    'categoryID' => $_POST['categoryID'] ?? null,
                    'processGroupCode' => $_POST['processGroupCode'] ?? '',
                    'processGroupName' => $_POST['processGroupName'] ?? '',
                    'processGroupDescription' => $_POST['processGroupDescription'] ?? '',
                    'displayOrder' => $_POST['displayOrder'] ?? 0,
                    'isActive' => $_POST['isActive'] ?? 'Y'
                ];

                if (empty($data['categoryID']) || empty($data['processGroupCode']) || empty($data['processGroupName'])) {
                    throw new Exception('Category, Process Group Code and Name are required');
                }

                $processGroupID = BAUTaxonomy::createProcessGroup($data, $DBConn);

                if ($processGroupID) {
                    echo json_encode(['success' => true, 'processGroupID' => $processGroupID, 'message' => 'Process Group created successfully']);
                } else {
                    throw new Exception('Failed to create process group');
                }
            } elseif ($action === 'update') {
                $processGroupID = $_POST['processGroupID'] ?? null;
                if (!$processGroupID) {
                    throw new Exception('Process Group ID is required');
                }

                $data = [];
                $allowedFields = ['categoryID', 'processGroupCode', 'processGroupName', 'processGroupDescription', 'displayOrder', 'isActive'];
                foreach ($allowedFields as $field) {
                    if (isset($_POST[$field])) {
                        $data[$field] = $_POST[$field];
                    }
                }

                $success = BAUTaxonomy::updateProcessGroup($processGroupID, $data, $DBConn);

                if ($success) {
                    echo json_encode(['success' => true, 'message' => 'Process Group updated successfully']);
                } else {
                    throw new Exception('Failed to update process group');
                }
            } else {
                throw new Exception('Invalid action');
            }
            break;

        case 'GET':
            if ($action === 'get') {
                $processGroupID = $_GET['processGroupID'] ?? null;
                if (!$processGroupID) {
                    throw new Exception('Process Group ID is required');
                }

                // Get process group by ID
                $cols = array(
                    'processGroupID', 'categoryID', 'processGroupCode', 'processGroupName',
                    'processGroupDescription', 'displayOrder', 'isActive', 'DateAdded',
                    'LastUpdate', 'LastUpdatedByID', 'Lapsed', 'Suspended'
                );
                $processGroups = $DBConn->retrieve_db_table_rows('tija_bau_process_groups', $cols, ['processGroupID' => $processGroupID]);
                $processGroup = (is_array($processGroups) && count($processGroups) > 0) ? $processGroups[0] : false;

                if ($processGroup) {
                    echo json_encode(['success' => true, 'processGroup' => $processGroup]);
                } else {
                    throw new Exception('Process Group not found');
                }
            } elseif ($action === 'get_next_code') {
                // Get next available process code for a category
                $categoryID = $_GET['categoryID'] ?? null;
                if (!$categoryID) {
                    throw new Exception('Category ID is required');
                }

                $nextCode = BAUTaxonomy::getNextProcessGroupCode($categoryID, $DBConn);
                echo json_encode(['success' => true, 'nextCode' => $nextCode]);
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

