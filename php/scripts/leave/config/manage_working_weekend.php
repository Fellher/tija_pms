<?php
/**
 * Working Weekend Management Handler
 * Handles CRUD operations for working weekends
 */

// Start session and include necessary files
session_start();
$base = '../../../../';
require_once $base . 'php/includes.php';

// Check authentication
if (!isset($userDetails->ID) || !$isValidUser) {
    http_response_code(403);
    header('Location: ' . $base . 'html/?s=core&p=signin');
    exit;
}

$currentUserID = $userDetails->ID;
$entityID = $_SESSION['entityID'] ?? 1;

$response = ['success' => false, 'message' => ''];

try {
    $action = $_POST['action'] ?? 'save';

    switch ($action) {
        case 'save':
        case 'create':
        case 'update':
            // Get form data
            $workingWeekendID = $_POST['workingWeekendID'] ?? null;
            $workingWeekendName = $_POST['workingWeekendName'] ?? '';
            $workingWeekendDate = $_POST['workingWeekendDate'] ?? '';
            $workType = $_POST['workType'] ?? 'FullDay';
            $description = $_POST['description'] ?? '';

            // Validate required fields
            if (empty($workingWeekendName) || empty($workingWeekendDate)) {
                throw new Exception('Weekend name and date are required');
            }

            $data = [
                'entityID' => $entityID,
                'workingWeekendName' => $workingWeekendName,
                'workingWeekendDate' => $workingWeekendDate,
                'workType' => $workType,
                'description' => $description,
                'LastUpdateByID' => $currentUserID,
                'LastUpdate' => $config['currentDateTimeFormated']
            ];

            if ($workingWeekendID) {
                // Update existing working weekend
                $result = $DBConn->update_table('tija_working_weekends', $data, ['workingWeekendID' => $workingWeekendID]);
                $message = 'Working weekend updated successfully';
            } else {
                // Create new working weekend
                $data['CreateDate'] = $config['currentDateTimeFormated'];
                $data['CreatedByID'] = $currentUserID;
                $result = $DBConn->insert_data('tija_working_weekends', $data);
                $message = 'Working weekend created successfully';
            }

            if ($result) {
                Alert::success($message, true);
                header('Location: ' . $base . 'html/?s=admin&ss=leave&p=working_weekends');
            } else {
                throw new Exception('Failed to save working weekend');
            }
            break;

        case 'delete':
            $workingWeekendID = $_POST['workingWeekendID'] ?? null;

            if (!$workingWeekendID) {
                throw new Exception('Working weekend ID is required');
            }

            // Soft delete by setting Lapsed = 'Y'
            $data = [
                'Lapsed' => 'Y',
                'LastUpdateByID' => $currentUserID,
                'LastUpdate' => $config['currentDateTimeFormated']
            ];

            $result = $DBConn->update_table('tija_working_weekends', $data, ['workingWeekendID' => $workingWeekendID]);

            if ($result) {
                Alert::success('Working weekend deleted successfully', true);
                header('Location: ' . $base . 'html/?s=admin&ss=leave&p=working_weekends');
            } else {
                throw new Exception('Failed to delete working weekend');
            }
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    Alert::error('Error: ' . $e->getMessage(), true);
    header('Location: ' . $base . 'html/?s=admin&ss=leave&p=working_weekends');
}
exit;
?>

