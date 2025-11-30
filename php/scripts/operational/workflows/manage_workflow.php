<?php
/**
 * Workflow Management API
 *
 * Create, update, delete workflows
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
                $workflowName = $_POST['workflowName'] ?? '';
                $data = [
                    'workflowCode' => $_POST['workflowCode'] ?? 'WF-' . strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $workflowName), 0, 20)) . '-' . time(),
                    'workflowName' => $workflowName,
                    'workflowDescription' => $_POST['workflowDescription'] ?? '',
                    'functionalArea' => $_POST['functionalArea'] ?? '',
                    'workflowType' => $_POST['workflowType'] ?? 'sequential',
                    'isActive' => $_POST['isActive'] ?? 'Y',
                    'createdByID' => $userID
                ];

                if (empty($data['workflowName'])) {
                    throw new Exception('Workflow name is required');
                }

                $workflowID = WorkflowDefinition::createWorkflow($data, $DBConn);

                if ($workflowID) {
                    echo json_encode(['success' => true, 'workflowID' => $workflowID, 'message' => 'Workflow created successfully']);
                } else {
                    throw new Exception('Failed to create workflow');
                }
            } elseif ($action === 'update') {
                $workflowID = $_POST['workflowID'] ?? null;
                if (!$workflowID) {
                    throw new Exception('Workflow ID is required');
                }

                $data = [];
                $allowedFields = ['workflowName', 'workflowDescription', 'functionalArea', 'isActive'];
                foreach ($allowedFields as $field) {
                    if (isset($_POST[$field])) {
                        $data[$field] = $_POST[$field];
                    }
                }

                $success = WorkflowDefinition::updateWorkflow($workflowID, $data, $DBConn);

                if ($success) {
                    echo json_encode(['success' => true, 'message' => 'Workflow updated successfully']);
                } else {
                    throw new Exception('Failed to update workflow');
                }
            } elseif ($action === 'add_step') {
                $workflowID = $_POST['workflowID'] ?? null;
                if (!$workflowID) {
                    throw new Exception('Workflow ID is required');
                }

                $stepData = [
                    'stepOrder' => (int)($_POST['stepOrder'] ?? 1),
                    'stepName' => $_POST['stepName'] ?? '',
                    'stepDescription' => $_POST['stepDescription'] ?? '',
                    'stepType' => $_POST['stepType'] ?? 'task',
                    'assigneeType' => $_POST['assigneeType'] ?? 'auto',
                    'assigneeRoleID' => !empty($_POST['assigneeRoleID']) ? (int)$_POST['assigneeRoleID'] : null,
                    'assigneeEmployeeID' => !empty($_POST['assigneeEmployeeID']) ? (int)$_POST['assigneeEmployeeID'] : null,
                    'estimatedDuration' => !empty($_POST['estimatedDuration']) ? (float)$_POST['estimatedDuration'] : null,
                    'isMandatory' => $_POST['isMandatory'] ?? 'Y',
                    'stepConfig' => !empty($_POST['stepConfig']) ? json_decode($_POST['stepConfig'], true) : null
                ];

                if (empty($stepData['stepName'])) {
                    throw new Exception('Step name is required');
                }

                $stepID = WorkflowDefinition::addWorkflowStep($workflowID, $stepData, $DBConn);

                if ($stepID) {
                    echo json_encode(['success' => true, 'stepID' => $stepID, 'message' => 'Step added successfully']);
                } else {
                    throw new Exception('Failed to add step');
                }
            } elseif ($action === 'update_step') {
                $stepID = $_POST['stepID'] ?? null;
                if (!$stepID) {
                    throw new Exception('Step ID is required');
                }

                $stepData = [];
                $allowedFields = ['stepOrder', 'stepName', 'stepDescription', 'stepType', 'assigneeType', 'assigneeRoleID', 'assigneeEmployeeID', 'estimatedDuration', 'isMandatory', 'stepConfig'];
                foreach ($allowedFields as $field) {
                    if (isset($_POST[$field])) {
                        if ($field === 'assigneeRoleID' || $field === 'assigneeEmployeeID' || $field === 'stepOrder') {
                            $stepData[$field] = !empty($_POST[$field]) ? (int)$_POST[$field] : null;
                        } elseif ($field === 'estimatedDuration') {
                            $stepData[$field] = !empty($_POST[$field]) ? (float)$_POST[$field] : null;
                        } elseif ($field === 'stepConfig') {
                            $stepData[$field] = json_decode($_POST[$field], true);
                        } else {
                            $stepData[$field] = $_POST[$field];
                        }
                    }
                }

                $success = WorkflowDefinition::updateWorkflowStep($stepID, $stepData, $DBConn);

                if ($success) {
                    echo json_encode(['success' => true, 'message' => 'Step updated successfully']);
                } else {
                    throw new Exception('Failed to update step');
                }
            } elseif ($action === 'delete_step') {
                $stepID = $_POST['stepID'] ?? null;
                if (!$stepID) {
                    throw new Exception('Step ID is required');
                }

                $success = WorkflowDefinition::updateWorkflowStep($stepID, ['Suspended' => 'Y'], $DBConn);

                if ($success) {
                    echo json_encode(['success' => true, 'message' => 'Step deleted successfully']);
                } else {
                    throw new Exception('Failed to delete step');
                }
            } elseif ($action === 'add_transition') {
                $workflowID = $_POST['workflowID'] ?? null;
                if (!$workflowID) {
                    throw new Exception('Workflow ID is required');
                }

                $transitionData = [
                    'fromStepID' => (int)($_POST['fromStepID'] ?? 0),
                    'toStepID' => (int)($_POST['toStepID'] ?? 0),
                    'conditionType' => $_POST['conditionType'] ?? 'always',
                    'conditionExpression' => !empty($_POST['conditionExpression']) ? json_decode($_POST['conditionExpression'], true) : null,
                    'transitionLabel' => $_POST['transitionLabel'] ?? ''
                ];

                if (!$transitionData['fromStepID'] || !$transitionData['toStepID']) {
                    throw new Exception('From and To step IDs are required');
                }

                $transitionID = WorkflowDefinition::addWorkflowTransition($workflowID, $transitionData, $DBConn);

                if ($transitionID) {
                    echo json_encode(['success' => true, 'transitionID' => $transitionID, 'message' => 'Transition added successfully']);
                } else {
                    throw new Exception('Failed to add transition');
                }
            } elseif ($action === 'delete_transition') {
                $transitionID = $_POST['transitionID'] ?? null;
                if (!$transitionID) {
                    throw new Exception('Transition ID is required');
                }

                // Note: We'll need to add a delete method for transitions
                // For now, we'll use update_table directly
                $success = $DBConn->update_table('tija_workflow_transitions', ['Suspended' => 'Y'], ['transitionID' => $transitionID]);

                if ($success) {
                    echo json_encode(['success' => true, 'message' => 'Transition deleted successfully']);
                } else {
                    throw new Exception('Failed to delete transition');
                }
            } elseif ($action === 'delete') {
                $workflowID = $_POST['workflowID'] ?? null;
                if (!$workflowID) {
                    throw new Exception('Workflow ID is required');
                }

                $success = WorkflowDefinition::updateWorkflow($workflowID, ['Suspended' => 'Y'], $DBConn);

                if ($success) {
                    echo json_encode(['success' => true, 'message' => 'Workflow deleted successfully']);
                } else {
                    throw new Exception('Failed to delete workflow');
                }
            } else {
                throw new Exception('Invalid action');
            }
            break;

        case 'GET':
            if ($action === 'get') {
                $workflowID = $_GET['workflowID'] ?? null;
                if (!$workflowID) {
                    throw new Exception('Workflow ID is required');
                }

                $workflow = WorkflowDefinition::getWorkflow($workflowID, $DBConn);
                if ($workflow) {
                    echo json_encode(['success' => true, 'workflow' => $workflow]);
                } else {
                    throw new Exception('Workflow not found');
                }
            } elseif ($action === 'validate') {
                $workflowID = $_GET['workflowID'] ?? null;
                if (!$workflowID) {
                    throw new Exception('Workflow ID is required');
                }

                $validation = WorkflowDefinition::validateWorkflow($workflowID, $DBConn);
                echo json_encode(['success' => true, 'validation' => $validation]);
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

