<?php
/**
 * Update Activity Date Script
 * Handles date updates for activities (calendar drag-and-drop)
 */

session_start();
$base = '../../../';
set_include_path($base);
header('Content-Type: application/json');

try {
    include 'php/includes.php';
    
    if (!isset($_SESSION['userDetails'])) {
        throw new Exception('User not logged in');
    }
    
    $userDetails = $_SESSION['userDetails'];
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['activityId']) || !isset($input['date'])) {
        throw new Exception('Invalid request data');
    }
    
    $activityId = intval($input['activityId']);
    $newDate = Utility::clean_string($input['date']);
    
    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $newDate)) {
        throw new Exception('Invalid date format');
    }
    
    // Update activity date
    $updateData = [
        'activityDate' => $newDate,
        'LastUpdate' => date('Y-m-d H:i:s'),
        'LastUpdatedBy' => $userDetails->ID
    ];
    
    $result = $DBConn->update_data('tija_activities', $updateData, ['activityID' => $activityId]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Activity date updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update activity date');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
