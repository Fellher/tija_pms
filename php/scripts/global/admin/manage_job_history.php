<?php
/**
 * Job History Management API
 * Handles CRUD operations for employee job history
 */

// Check if session is not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set up paths if not already set (check if mysqlConnect class exists)
if (!class_exists('mysqlConnect')) {
    $base = '../../../../';
    set_include_path($base);
    include 'php/includes.php';
}

header('Content-Type: application/json');

// Check if user is logged in
if (!$isValidUser) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Ensure DBConn is available
global $DBConn;
if (!isset($DBConn) || !$DBConn) {
    echo json_encode(['success' => false, 'message' => 'Database connection not available']);
    exit;
}

$userID = $userDetails->ID;
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

// Use the global database connection (already available from includes.php)

try {
    switch ($action) {
        case 'get':
            // Get single job history record
            $jobHistoryID = isset($_GET['id']) ? intval($_GET['id']) : 0;

            if (!$jobHistoryID) {
                echo json_encode(['success' => false, 'message' => 'Job history ID is required']);
                exit;
            }

            $jobHistory = EmployeeProfile::get_job_history(['jobHistoryID' => $jobHistoryID], true, $DBConn);

            if ($jobHistory) {
                echo json_encode([
                    'success' => true,
                    'data' => $jobHistory
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Job history not found']);
            }
            break;

        case 'save':
            // Add or update job history
            $jobHistoryID = isset($_POST['jobHistoryID']) ? intval($_POST['jobHistoryID']) : 0;
            $employeeID = isset($_POST['employeeID']) ? intval($_POST['employeeID']) : 0;
            $jobTitleID = isset($_POST['jobTitleID']) ? intval($_POST['jobTitleID']) : null;
            $departmentID = isset($_POST['departmentID']) ? intval($_POST['departmentID']) : null;
            $startDate = isset($_POST['startDate']) ? $_POST['startDate'] : '';
            $endDate = isset($_POST['endDate']) && !empty($_POST['endDate']) ? $_POST['endDate'] : null;
            $isCurrent = isset($_POST['isCurrent']) && $_POST['isCurrent'] === 'Y' ? 'Y' : 'N';
            $responsibilities = isset($_POST['responsibilities']) ? trim($_POST['responsibilities']) : null;
            $achievements = isset($_POST['achievements']) ? trim($_POST['achievements']) : null;
            $changeReason = isset($_POST['changeReason']) ? trim($_POST['changeReason']) : null;

            // Validation
            $errors = [];

            if (!$employeeID) {
                $errors[] = 'Employee ID is required';
            }

            if (!$jobTitleID) {
                $errors[] = 'Job title is required';
            }

            if (!$departmentID) {
                $errors[] = 'Department is required';
            }

            if (!$startDate) {
                $errors[] = 'Start date is required';
            }

            // Validate dates
            if ($startDate && $endDate) {
                $start = strtotime($startDate);
                $end = strtotime($endDate);

                if ($end <= $start) {
                    $errors[] = 'End date must be after start date';
                }
            }

            if (count($errors) > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => implode(', ', $errors)
                ]);
                exit;
            }

            // Prepare data
            $data = [
                'employeeID' => $employeeID,
                'jobTitleID' => $jobTitleID,
                'departmentID' => $departmentID,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'isCurrent' => $isCurrent,
                'responsibilities' => $responsibilities,
                'achievements' => $achievements,
                'changeReason' => $changeReason,
                'updatedBy' => $userID
            ];

            if ($jobHistoryID) {
                // Update existing record
                $result = $DBConn->update_table('tija_employee_job_history', $data, ['jobHistoryID' => $jobHistoryID]);

                if ($result !== false) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Job history updated successfully'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to update job history'
                    ]);
                }
            } else {
                // Insert new record
                $data['createdBy'] = $userID;

                $result = $DBConn->insert_data('tija_employee_job_history', $data);

                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Job history added successfully'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to add job history'
                    ]);
                }
            }
            break;

        case 'delete':
            // Delete job history (soft delete)
            // Check both GET and POST for ID (frontend sends POST with query string)
            $jobHistoryID = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);
            //provide error logg output for the jobHistoryID
            error_log('Job History ID: ' . $jobHistoryID);
            if (!$jobHistoryID) {
                echo json_encode(['success' => false, 'message' => 'Job history ID is required']);
                exit;
            }

            // First verify the record exists and belongs to the correct employee/entity
            $existingRecord = EmployeeProfile::get_job_history(['jobHistoryID' => $jobHistoryID], true, $DBConn);
            //provide error logg output for the existingRecord
            error_log('Existing Record: ' . json_encode($existingRecord));
            if (!$existingRecord) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Job history record not found'
                ]);
                exit;
            }

            // Begin transaction for safety
            $DBConn->begin();

            try {
                // Soft delete by setting Suspended = 'Y'
                $updateData = [
                    'Suspended' => 'Y',
                    'updatedBy' => $userID
                ];

                // Add LastUpdate if the column exists
                if (method_exists($DBConn, 'get_table_columns')) {
                    // Try to add LastUpdate timestamp
                    $updateData['LastUpdate'] = date('Y-m-d H:i:s');
                }

                $result = $DBConn->update_table('tija_employee_job_history', $updateData, ['jobHistoryID' => $jobHistoryID]);

                // Check if update was successful
                // update_table returns true if rowCount() > 0, false otherwise
                if ($result === false) {
                    // Check if record was already suspended
                    $checkSQL = "SELECT Suspended FROM tija_employee_job_history WHERE jobHistoryID = ?";
                    $DBConn->query($checkSQL);
                    $DBConn->bind(1, $jobHistoryID);
                    $DBConn->execute();
                    $checkRecord = $DBConn->single();

                    if ($checkRecord && isset($checkRecord->Suspended) && $checkRecord->Suspended === 'Y') {
                        // Already deleted, consider it success
                        $DBConn->commit();
                        echo json_encode([
                            'success' => true,
                            'message' => 'Job history already deleted'
                        ]);
                    } else {
                        $DBConn->rollback();
                        echo json_encode([
                            'success' => false,
                            'message' => 'Failed to delete job history. Database update returned false.'
                        ]);
                    }
                    exit;
                }

                // Verify the update was successful by querying directly (bypassing the Suspended filter)
                $verifySQL = "SELECT Suspended FROM tija_employee_job_history WHERE jobHistoryID = ?";
                $DBConn->query($verifySQL);
                $DBConn->bind(1, $jobHistoryID);
                $DBConn->execute();
                $updatedRecord = $DBConn->single();

                if ($updatedRecord && isset($updatedRecord->Suspended) && $updatedRecord->Suspended === 'Y') {
                    $DBConn->commit();
                    echo json_encode([
                        'success' => true,
                        'message' => 'Job history deleted successfully'
                    ]);
                } else {
                    $DBConn->rollback();
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to delete job history. Update did not take effect. Record still active.'
                    ]);
                }
            } catch (Exception $e) {
                $DBConn->rollback();
                error_log('Job History Delete Error: ' . $e->getMessage());
                error_log('Stack Trace: ' . $e->getTraceAsString());
                echo json_encode([
                    'success' => false,
                    'message' => 'An error occurred while deleting job history: ' . $e->getMessage()
                ]);
            }
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
            break;
    }
} catch (Exception $e) {
    // Log the error for debugging
    error_log('Job History Error: ' . $e->getMessage());
    error_log('Trace: ' . $e->getTraceAsString());

    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>

