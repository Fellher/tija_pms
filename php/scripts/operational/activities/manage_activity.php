<?php
/**
 * Activity Management API
 *
 * Create, update, delete activities
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
                    'activityName' => $_POST['activityName'] ?? '',
                    'activityDescription' => $_POST['activityDescription'] ?? '',
                    'processID' => $_POST['processID'] ?? null,
                    'functionalArea' => $_POST['functionalArea'] ?? '',
                    'isActive' => $_POST['isActive'] ?? 'Y',
                    'createdByID' => $userID
                ];

                if (empty($data['activityName'])) {
                    throw new Exception('Activity name is required');
                }

                $activityID = BAUTaxonomy::createActivity($data, $DBConn);

                if ($activityID) {
                    echo json_encode(['success' => true, 'activityID' => $activityID, 'message' => 'Activity created successfully']);
                } else {
                    throw new Exception('Failed to create activity');
                }
            } elseif ($action === 'update') {
                $activityID = $_POST['activityID'] ?? null;
                if (!$activityID) {
                    throw new Exception('Activity ID is required');
                }

                $data = [];
                $allowedFields = ['activityName', 'activityDescription', 'processID', 'functionalArea', 'isActive'];
                foreach ($allowedFields as $field) {
                    if (isset($_POST[$field])) {
                        $data[$field] = $_POST[$field];
                    }
                }

                $success = BAUTaxonomy::updateActivity($activityID, $data, $DBConn);

                if ($success) {
                    echo json_encode(['success' => true, 'message' => 'Activity updated successfully']);
                } else {
                    throw new Exception('Failed to update activity');
                }
            } elseif ($action === 'delete') {
                $activityID = $_POST['activityID'] ?? null;
                if (!$activityID) {
                    throw new Exception('Activity ID is required');
                }

                $success = BAUTaxonomy::updateActivity($activityID, ['Suspended' => 'Y'], $DBConn);

                if ($success) {
                    echo json_encode(['success' => true, 'message' => 'Activity deleted successfully']);
                } else {
                    throw new Exception('Failed to delete activity');
                }
            } else {
                throw new Exception('Invalid action');
            }
            break;

        case 'GET':
            if ($action === 'get') {
                $activityID = $_GET['activityID'] ?? null;
                if (!$activityID) {
                    throw new Exception('Activity ID is required');
                }

                $activity = BAUTaxonomy::getActivityByID($activityID, $DBConn);
                if ($activity) {
                    echo json_encode(['success' => true, 'activity' => $activity]);
                } else {
                    throw new Exception('Activity not found');
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

