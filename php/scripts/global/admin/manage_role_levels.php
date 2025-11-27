<?php
/**
 * Role Levels Management Script
 * Handles CRUD operations for role levels
 */

session_start();
$base = '../../../../';
set_include_path($base);

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'php/includes.php';

// Check admin access
if (!$isValidAdmin && !$isAdmin) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Ensure DBConn is available
if (!isset($DBConn) || !$DBConn) {
    echo json_encode(['success' => false, 'message' => 'Database connection not available']);
    exit;
}

// Get user ID from session
$userID = isset($userDetails->ID) ? $userDetails->ID : (isset($employeeDetails->ID) ? $employeeDetails->ID : null);

if (!$userID) {
    echo json_encode(['success' => false, 'message' => 'User ID not found']);
    exit;
}

// Get action
$action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : (isset($_GET['action']) ? Utility::clean_string($_GET['action']) : '');

try {
    switch ($action) {
        case 'create':
        case 'update':
            $roleLevelID = isset($_POST['roleLevelID']) ? intval($_POST['roleLevelID']) : 0;
            $levelNumber = isset($_POST['levelNumber']) ? intval($_POST['levelNumber']) : 0;
            $levelName = isset($_POST['levelName']) ? Utility::clean_string($_POST['levelName']) : '';
            $levelCode = isset($_POST['levelCode']) ? strtoupper(Utility::clean_string($_POST['levelCode'])) : '';
            $levelDescription = isset($_POST['levelDescription']) ? Utility::clean_string($_POST['levelDescription']) : '';
            $displayOrder = isset($_POST['displayOrder']) ? intval($_POST['displayOrder']) : 0;
            $isActive = isset($_POST['isActive']) && $_POST['isActive'] == 'Y' ? 'Y' : 'N';

            // Validation
            if (empty($levelName)) {
                echo json_encode(['success' => false, 'message' => 'Level name is required']);
                exit;
            }

            if ($levelNumber < 0 || $levelNumber > 99) {
                echo json_encode(['success' => false, 'message' => 'Level number must be between 0 and 99']);
                exit;
            }

            // Check if level number already exists (for new records or if number changed)
            $existingCheck = Data::role_levels(['levelNumber' => $levelNumber, 'Suspended' => 'N'], true, $DBConn);
            if ($existingCheck && ($action === 'create' || ($action === 'update' && $existingCheck->roleLevelID != $roleLevelID))) {
                echo json_encode(['success' => false, 'message' => 'Level number already exists']);
                exit;
            }

            // Check level code if provided
            if (!empty($levelCode)) {
                $existingCodeCheck = Data::role_levels(['levelCode' => $levelCode, 'Suspended' => 'N'], true, $DBConn);
                if ($existingCodeCheck && ($action === 'create' || ($action === 'update' && $existingCodeCheck->roleLevelID != $roleLevelID))) {
                    echo json_encode(['success' => false, 'message' => 'Level code already exists']);
                    exit;
                }
            }

            $data = [
                'levelNumber' => $levelNumber,
                'levelName' => $levelName,
                'levelCode' => $levelCode ?: null,
                'levelDescription' => $levelDescription,
                'displayOrder' => $displayOrder,
                'isActive' => $isActive,
                'LastUpdatedByID' => $userID
            ];

            if ($action === 'create') {
                $data['DateAdded'] = date('Y-m-d H:i:s');
                $data['Lapsed'] = 'N';
                $data['Suspended'] = 'N';
                $result = $DBConn->insert_data('tija_role_levels', $data);
                $message = 'Role level created successfully';
            } else {
                $result = $DBConn->update_table('tija_role_levels', $data, ['roleLevelID' => $roleLevelID]);
                $message = 'Role level updated successfully';
            }

            if ($result !== false) {
                echo json_encode(['success' => true, 'message' => $message]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to save role level']);
            }
            break;

        case 'get':
            $roleLevelID = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if (!$roleLevelID) {
                echo json_encode(['success' => false, 'message' => 'Role level ID is required']);
                exit;
            }

            $roleLevel = Data::role_levels(['roleLevelID' => $roleLevelID], true, $DBConn);
            if ($roleLevel) {
                echo json_encode(['success' => true, 'roleLevel' => $roleLevel]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Role level not found']);
            }
            break;

        case 'delete':
            $roleLevelID = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if (!$roleLevelID) {
                echo json_encode(['success' => false, 'message' => 'Role level ID is required']);
                exit;
            }

            // Check if role level is default (cannot delete defaults)
            $roleLevel = Data::role_levels(['roleLevelID' => $roleLevelID], true, $DBConn);
            if ($roleLevel && $roleLevel->isDefault === 'Y') {
                echo json_encode(['success' => false, 'message' => 'Cannot delete default role levels']);
                exit;
            }

            // Check if role level is in use
            $rolesUsingLevel = Data::roles(['roleLevel' => $roleLevel->levelNumber, 'Suspended' => 'N'], false, $DBConn);
            if ($rolesUsingLevel && count($rolesUsingLevel) > 0) {
                echo json_encode(['success' => false, 'message' => 'Cannot delete role level that is in use by ' . count($rolesUsingLevel) . ' role(s)']);
                exit;
            }

            // Soft delete
            $result = $DBConn->update_table('tija_role_levels', [
                'Suspended' => 'Y',
                'LastUpdatedByID' => $userID
            ], ['roleLevelID' => $roleLevelID]);

            if ($result !== false) {
                echo json_encode(['success' => true, 'message' => 'Role level deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete role level']);
            }
            break;

        case 'update_order':
            $orderData = isset($_POST['orderData']) ? json_decode($_POST['orderData'], true) : [];
            if (empty($orderData)) {
                echo json_encode(['success' => false, 'message' => 'Order data is required']);
                exit;
            }

            $DBConn->begin_transaction();
            try {
                foreach ($orderData as $item) {
                    $DBConn->update_table('tija_role_levels', [
                        'displayOrder' => intval($item['order']),
                        'LastUpdatedByID' => $userID
                    ], ['roleLevelID' => intval($item['id'])]);
                }
                $DBConn->commit();
                echo json_encode(['success' => true, 'message' => 'Display order updated successfully']);
            } catch (Exception $e) {
                $DBConn->rollback();
                echo json_encode(['success' => false, 'message' => 'Failed to update order: ' . $e->getMessage()]);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

