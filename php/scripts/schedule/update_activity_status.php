<?php
/**
 * Update Activity Status Script
 * Handles AJAX status updates for activities
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
    
    if (!$input || !isset($input['activityId']) || !isset($input['status'])) {
        throw new Exception('Invalid request data');
    }
    
    $activityId = intval($input['activityId']);
    $newStatus = intval($input['status']);
    
    // Update activity status
    $updateData = [
        'activityStatusID' => $newStatus,
        'LastUpdate' => date('Y-m-d H:i:s'),
        'LastUpdatedBy' => $userDetails->ID
    ];
    
    $result = $DBConn->update_data('tija_activities', $updateData, ['activityID' => $activityId]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Activity status updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update activity status');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
