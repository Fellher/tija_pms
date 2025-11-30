<?php
/**
 * Template Management API
 *
 * Create, update, delete, activate/deactivate operational task templates
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
                // Create template
                $data = [
                    'templateCode' => $_POST['templateCode'] ?? '',
                    'templateName' => $_POST['templateName'] ?? '',
                    'templateDescription' => $_POST['templateDescription'] ?? '',
                    'processID' => $_POST['processID'] ?? null,
                    'workflowID' => $_POST['workflowID'] ?? null,
                    'sopID' => $_POST['sopID'] ?? null,
                    'functionalArea' => $_POST['functionalArea'] ?? '',
                    'frequencyType' => $_POST['frequencyType'] ?? 'monthly',
                    'frequencyInterval' => $_POST['frequencyInterval'] ?? 1,
                    'frequencyDayOfWeek' => $_POST['frequencyDayOfWeek'] ?? null,
                    'frequencyDayOfMonth' => $_POST['frequencyDayOfMonth'] ?? null,
                    'frequencyMonthOfYear' => $_POST['frequencyMonthOfYear'] ?? null,
                    'estimatedDuration' => $_POST['estimatedDuration'] ?? 0,
                    'sopDocumentURL' => $_POST['sopDocumentURL'] ?? null,
                    'assignmentRule' => $_POST['assignmentRule'] ?? '{}',
                    'requiresApproval' => $_POST['requiresApproval'] ?? 'N',
                    'approverRoleID' => $_POST['approverRoleID'] ?? null,
                    'processingMode' => $_POST['processingMode'] ?? 'cron',
                    'isActive' => $_POST['isActive'] ?? 'Y'
                ];

                if (empty($data['templateCode']) || empty($data['templateName'])) {
                    throw new Exception('Template code and name are required');
                }

                $templateID = OperationalTaskTemplate::createTemplate($data, $DBConn);

                if ($templateID) {
                    echo json_encode(['success' => true, 'templateID' => $templateID, 'message' => 'Template created successfully']);
                } else {
                    throw new Exception('Failed to create template');
                }
            } elseif ($action === 'update') {
                // Update template
                $templateID = $_POST['templateID'] ?? null;
                if (!$templateID) {
                    throw new Exception('Template ID is required');
                }

                $data = [];
                $allowedFields = [
                    'templateCode', 'templateName', 'templateDescription', 'processID', 'workflowID', 'sopID',
                    'functionalArea', 'frequencyType', 'frequencyInterval', 'frequencyDayOfWeek',
                    'frequencyDayOfMonth', 'frequencyMonthOfYear', 'estimatedDuration', 'sopDocumentURL',
                    'assignmentRule', 'requiresApproval', 'approverRoleID', 'processingMode', 'isActive'
                ];

                foreach ($allowedFields as $field) {
                    if (isset($_POST[$field])) {
                        $data[$field] = $_POST[$field];
                    }
                }

                if (empty($data)) {
                    throw new Exception('No fields to update');
                }

                $success = OperationalTaskTemplate::updateTemplate($templateID, $data, $DBConn);

                if ($success) {
                    echo json_encode(['success' => true, 'message' => 'Template updated successfully']);
                } else {
                    throw new Exception('Failed to update template');
                }
            } elseif ($action === 'delete') {
                // Delete template (soft delete)
                $templateID = $_POST['templateID'] ?? null;
                if (!$templateID) {
                    throw new Exception('Template ID is required');
                }

                $success = OperationalTaskTemplate::updateTemplate($templateID, ['Suspended' => 'Y'], $DBConn);

                if ($success) {
                    echo json_encode(['success' => true, 'message' => 'Template deleted successfully']);
                } else {
                    throw new Exception('Failed to delete template');
                }
            } elseif ($action === 'toggle') {
                // Activate/deactivate template
                $templateID = $_POST['templateID'] ?? null;
                $isActive = $_POST['isActive'] ?? 'Y';

                if (!$templateID) {
                    throw new Exception('Template ID is required');
                }

                $success = OperationalTaskTemplate::updateTemplate($templateID, ['isActive' => $isActive], $DBConn);

                if ($success) {
                    $status = $isActive === 'Y' ? 'activated' : 'deactivated';
                    echo json_encode(['success' => true, 'message' => "Template {$status} successfully"]);
                } else {
                    throw new Exception('Failed to update template status');
                }
            } else {
                throw new Exception('Invalid action');
            }
            break;

        case 'GET':
            if ($action === 'get') {
                // Get single template
                $templateID = $_GET['templateID'] ?? null;
                if (!$templateID) {
                    throw new Exception('Template ID is required');
                }

                $template = OperationalTaskTemplate::getTemplate($templateID, $DBConn);
                if ($template) {
                    echo json_encode(['success' => true, 'template' => $template]);
                } else {
                    throw new Exception('Template not found');
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

