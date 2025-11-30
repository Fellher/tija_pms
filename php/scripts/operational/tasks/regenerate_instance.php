<?php
/**
 * Regenerate Task Instance API
 *
 * Manually trigger next instance creation for a completed task
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

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    $operationalTaskID = $_POST['operationalTaskID'] ?? null;

    if (!$operationalTaskID) {
        throw new Exception('Operational Task ID is required');
    }

    $success = OperationalTask::regenerateNextInstance($operationalTaskID, $DBConn);

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Next instance created successfully']);
    } else {
        throw new Exception('Failed to regenerate instance. Task may not be completed or template may be inactive.');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

