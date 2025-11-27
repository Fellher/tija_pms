<?php
/**
 * Role Types Management Script
 * Handles CRUD operations for role types
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
            $roleTypeID = isset($_POST['roleTypeID']) ? intval($_POST['roleTypeID']) : 0;
            $roleTypeName = isset($_POST['roleTypeName']) ? Utility::clean_string($_POST['roleTypeName']) : '';
            $roleTypeCode = isset($_POST['roleTypeCode']) ? strtoupper(Utility::clean_string($_POST['roleTypeCode'])) : '';
            $roleTypeDescription = isset($_POST['roleTypeDescription']) ? Utility::clean_string($_POST['roleTypeDescription']) : '';
            $displayOrder = isset($_POST['displayOrder']) ? intval($_POST['displayOrder']) : 0;
            $colorCode = isset($_POST['colorCode']) ? Utility::clean_string($_POST['colorCode']) : '#667eea';
            $iconClass = isset($_POST['iconClass']) ? Utility::clean_string($_POST['iconClass']) : 'fa-user-tie';
            $isActive = isset($_POST['isActive']) && $_POST['isActive'] == 'Y' ? 'Y' : 'N';

            // Validation
            if (empty($roleTypeName)) {
                echo json_encode(['success' => false, 'message' => 'Role type name is required']);
                exit;
            }

            if (empty($roleTypeCode)) {
                echo json_encode(['success' => false, 'message' => 'Role type code is required']);
                exit;
            }

            // Check if code already exists (for new records or if code changed)
            $existingCheck = Data::role_types(['roleTypeCode' => $roleTypeCode, 'Suspended' => 'N'], true, $DBConn);
            if ($existingCheck && ($action === 'create' || ($action === 'update' && $existingCheck->roleTypeID != $roleTypeID))) {
                echo json_encode(['success' => false, 'message' => 'Role type code already exists']);
                exit;
            }

            $data = [
                'roleTypeName' => $roleTypeName,
                'roleTypeCode' => $roleTypeCode,
                'roleTypeDescription' => $roleTypeDescription,
                'displayOrder' => $displayOrder,
                'colorCode' => $colorCode,
                'iconClass' => $iconClass,
                'isActive' => $isActive,
                'LastUpdatedByID' => $userID
            ];

            if ($action === 'create') {
                $data['DateAdded'] = date('Y-m-d H:i:s');
                $data['Lapsed'] = 'N';
                $data['Suspended'] = 'N';
                $result = $DBConn->insert_data('tija_org_role_types', $data);
                $message = 'Role type created successfully';
            } else {
                $result = $DBConn->update_table('tija_org_role_types', $data, ['roleTypeID' => $roleTypeID]);
                $message = 'Role type updated successfully';
            }

            if ($result !== false) {
                echo json_encode(['success' => true, 'message' => $message]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to save role type']);
            }
            break;

        case 'get':
            $roleTypeID = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if (!$roleTypeID) {
                echo json_encode(['success' => false, 'message' => 'Role type ID is required']);
                exit;
            }

            $roleType = Data::role_types(['roleTypeID' => $roleTypeID], true, $DBConn);
            if ($roleType) {
                echo json_encode(['success' => true, 'roleType' => $roleType]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Role type not found']);
            }
            break;

        case 'delete':
            $roleTypeID = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if (!$roleTypeID) {
                echo json_encode(['success' => false, 'message' => 'Role type ID is required']);
                exit;
            }

            // Check if role type is default (cannot delete defaults)
            $roleType = Data::role_types(['roleTypeID' => $roleTypeID], true, $DBConn);
            if ($roleType && $roleType->isDefault === 'Y') {
                echo json_encode(['success' => false, 'message' => 'Cannot delete default role types']);
                exit;
            }

            // Check if role type is in use
            $rolesUsingType = Data::roles(['roleType' => $roleType->roleTypeCode, 'Suspended' => 'N'], false, $DBConn);
            if ($rolesUsingType && count($rolesUsingType) > 0) {
                echo json_encode(['success' => false, 'message' => 'Cannot delete role type that is in use by ' . count($rolesUsingType) . ' role(s)']);
                exit;
            }

            // Soft delete
            $result = $DBConn->update_table('tija_org_role_types', [
                'Suspended' => 'Y',
                'LastUpdatedByID' => $userID
            ], ['roleTypeID' => $roleTypeID]);

            if ($result !== false) {
                echo json_encode(['success' => true, 'message' => 'Role type deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete role type']);
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
                    $DBConn->update_table('tija_org_role_types', [
                        'displayOrder' => intval($item['order']),
                        'LastUpdatedByID' => $userID
                    ], ['roleTypeID' => intval($item['id'])]);
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

