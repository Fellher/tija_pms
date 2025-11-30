<?php
/**
 * SOP Management API
 *
 * Create, update, delete, approve SOPs
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
                    'sopCode' => $_POST['sopCode'] ?? '',
                    'sopTitle' => $_POST['sopTitle'] ?? '',
                    'sopDescription' => $_POST['sopDescription'] ?? '',
                    'functionalArea' => $_POST['functionalArea'] ?? '',
                    'sopVersion' => $_POST['sopVersion'] ?? '1.0',
                    'sopDocumentURL' => $_POST['sopDocumentURL'] ?? null,
                    'isActive' => $_POST['isActive'] ?? 'Y',
                    'approvalStatus' => $_POST['approvalStatus'] ?? 'draft',
                    'createdByID' => $userID
                ];

                if (empty($data['sopCode']) || empty($data['sopTitle'])) {
                    throw new Exception('SOP code and title are required');
                }

                $sopID = SOPManagement::createSOP($data, $DBConn);

                if ($sopID) {
                    echo json_encode(['success' => true, 'sopID' => $sopID, 'message' => 'SOP created successfully']);
                } else {
                    throw new Exception('Failed to create SOP');
                }
            } elseif ($action === 'update') {
                $sopID = $_POST['sopID'] ?? null;
                if (!$sopID) {
                    throw new Exception('SOP ID is required');
                }

                $data = [];
                $allowedFields = ['sopCode', 'sopTitle', 'sopDescription', 'functionalArea', 'sopVersion', 'sopDocumentURL', 'isActive', 'approvalStatus'];
                foreach ($allowedFields as $field) {
                    if (isset($_POST[$field])) {
                        $data[$field] = $_POST[$field];
                    }
                }

                $success = SOPManagement::updateSOP($sopID, $data, $DBConn);

                if ($success) {
                    echo json_encode(['success' => true, 'message' => 'SOP updated successfully']);
                } else {
                    throw new Exception('Failed to update SOP');
                }
            } elseif ($action === 'delete') {
                $sopID = $_POST['sopID'] ?? null;
                if (!$sopID) {
                    throw new Exception('SOP ID is required');
                }

                $success = SOPManagement::updateSOP($sopID, ['Suspended' => 'Y'], $DBConn);

                if ($success) {
                    echo json_encode(['success' => true, 'message' => 'SOP deleted successfully']);
                } else {
                    throw new Exception('Failed to delete SOP');
                }
            } elseif ($action === 'approve') {
                $sopID = $_POST['sopID'] ?? null;
                if (!$sopID) {
                    throw new Exception('SOP ID is required');
                }

                $success = SOPManagement::approveSOP($sopID, $userID, $DBConn);

                if ($success) {
                    echo json_encode(['success' => true, 'message' => 'SOP approved successfully']);
                } else {
                    throw new Exception('Failed to approve SOP');
                }
            } else {
                throw new Exception('Invalid action');
            }
            break;

        case 'GET':
            if ($action === 'get') {
                $sopID = $_GET['sopID'] ?? null;
                if (!$sopID) {
                    throw new Exception('SOP ID is required');
                }

                $sop = SOPManagement::getSOP($sopID, $DBConn);
                if ($sop) {
                    echo json_encode(['success' => true, 'sop' => $sop]);
                } else {
                    throw new Exception('SOP not found');
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

