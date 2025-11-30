<?php
/**
 * Process Management API
 *
 * Create, update, delete processes
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */



session_start();
$base = '../../../../';
set_include_path($base);
include 'php/includes.php';

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

// Debug logging (remove in production if needed)
error_log("Process API: method=$method, action=$action, processID=" . ($_GET['processID'] ?? $_POST['processID'] ?? 'N/A'));

try {
    switch ($method) {
        case 'POST':
            if ($action === 'create') {
                $data = [
                    'processID' => $_POST['processID'] ?? '',
                    'processName' => $_POST['processName'] ?? '',
                    'processDescription' => $_POST['processDescription'] ?? '',
                    'categoryID' => $_POST['categoryID'] ?? null,
                    'processGroupID' => $_POST['processGroupID'] ?? null,
                    'functionalArea' => $_POST['functionalArea'] ?? '',
                    'isCustom' => $_POST['isCustom'] ?? 'N',
                    'isActive' => $_POST['isActive'] ?? 'Y',
                    'createdByID' => $userID
                ];

                if (empty($data['processID']) || empty($data['processName'])) {
                    throw new Exception('Process ID and name are required');
                }

                // Use createCustomProcess if processID is provided, otherwise create new
                if (!empty($data['processID'])) {
                    // Check if process exists
                    $existing = BAUTaxonomy::getProcessByID($data['processID'], $DBConn);
                    if ($existing) {
                        throw new Exception('Process with this ID already exists');
                    }
                }
                $processID = BAUTaxonomy::createProcess($data, $DBConn);

                if ($processID) {
                    echo json_encode(['success' => true, 'processID' => $processID, 'message' => 'Process created successfully']);
                } else {
                    throw new Exception('Failed to create process');
                }
            } elseif ($action === 'update') {
                $processID = $_POST['processID'] ?? null;
                if (!$processID) {
                    throw new Exception('Process ID is required');
                }

                // Get current process data to compare
                $currentProcessData = BAUTaxonomy::getProcessByID($processID, $DBConn);
                if (!$currentProcessData || !isset($currentProcessData['process'])) {
                    throw new Exception('Process not found');
                }

                $currentProcess = $currentProcessData['process'];

                // Collect submitted data
                $submittedData = [];
                $allowedFields = ['processName', 'processDescription', 'categoryID', 'processGroupID', 'functionalArea', 'functionalAreaID', 'isActive', 'isCustom'];
                foreach ($allowedFields as $field) {
                    if (isset($_POST[$field])) {
                        $submittedData[$field] = $_POST[$field];
                    }
                }

                // Compare submitted data with current data to find actual changes
                $data = [];
                $hasChanges = false;

                foreach ($submittedData as $field => $newValue) {
                    // Get current value (handle both object and array access)
                    $currentValue = is_object($currentProcess)
                        ? ($currentProcess->$field ?? null)
                        : ($currentProcess[$field] ?? null);

                    // Normalize values for comparison
                    // Convert numeric strings to integers for IDs
                    if (in_array($field, ['categoryID', 'processGroupID', 'functionalAreaID']) && is_numeric($newValue)) {
                        $newValue = (int)$newValue;
                        if (is_numeric($currentValue)) {
                            $currentValue = (int)$currentValue;
                        }
                    }

                    // Compare values (handle null/empty cases)
                    $newValueNormalized = $newValue === '' ? null : $newValue;
                    $currentValueNormalized = $currentValue === '' ? null : $currentValue;

                    if ($newValueNormalized !== $currentValueNormalized) {
                        $data[$field] = $newValue;
                        $hasChanges = true;
                    }
                }

                // If no changes detected, return friendly message
                if (!$hasChanges || empty($data)) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'No changes were made to the process',
                        'noChanges' => true
                    ]);
                    exit;
                }

                // Map functionalAreaID to functionalArea if needed (for backward compatibility)
                // Note: The form might send functionalAreaID but the database might still need functionalArea
                // This should be handled based on which column exists or which is preferred

                $success = BAUTaxonomy::updateProcess($processID, $data, $DBConn);

                if ($success) {
                    echo json_encode(['success' => true, 'message' => 'Process updated successfully']);
                } else {
                    throw new Exception('Failed to update process');
                }
            } elseif ($action === 'delete') {
                $processID = $_POST['processID'] ?? null;
                if (!$processID) {
                    throw new Exception('Process ID is required');
                }

                $success = BAUTaxonomy::updateProcess($processID, ['Suspended' => 'Y'], $DBConn);

                if ($success) {
                    echo json_encode(['success' => true, 'message' => 'Process deleted successfully']);
                } else {
                    throw new Exception('Failed to delete process');
                }
            } else {
                throw new Exception('Invalid action');
            }
            break;

        case 'GET':
            if ($action === 'get') {
                $processID = $_GET['processID'] ?? null;
                if (!$processID) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Process ID is required']);
                    exit;
                }

                // Convert to integer if it's numeric (for processID lookup)
                // Otherwise keep as string (for processCode lookup)
                if (is_numeric($processID)) {
                    $processID = (int)$processID;
                }

                $process = BAUTaxonomy::getProcessByID($processID, $DBConn);
                if ($process) {
                    echo json_encode(['success' => true, 'process' => $process]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Process not found']);
                    exit;
                }
            } elseif ($action === 'get_next_process_code') {
                $processGroupID = $_GET['processGroupID'] ?? null;
                if (!$processGroupID) {
                    throw new Exception('Process Group ID is required');
                }

                $nextCode = BAUTaxonomy::getNextProcessCode($processGroupID, $DBConn);
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
    error_log("Process API Error: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'debug' => [
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]]);
} catch (Error $e) {
    http_response_code(500);
    error_log("Process API Fatal Error: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());
    echo json_encode(['success' => false, 'message' => 'Internal server error: ' . $e->getMessage()]);
}

