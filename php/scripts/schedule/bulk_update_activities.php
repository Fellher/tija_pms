<?php
/**
 * Bulk Update Activities Script
 * Handles bulk operations on multiple activities
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
    
    if (!$input || !isset($input['activityIds']) || !isset($input['status'])) {
        throw new Exception('Invalid request data');
    }
    
    $activityIds = $input['activityIds'];
    $newStatus = intval($input['status']);
    $updated = 0;
    
    // Validate activity IDs
    if (!is_array($activityIds) || empty($activityIds)) {
        throw new Exception('No activities selected');
    }
    
    foreach ($activityIds as $activityId) {
        $activityId = intval($activityId);
        
        // Update each activity
        $updateData = [
            'activityStatusID' => $newStatus,
            'LastUpdate' => date('Y-m-d H:i:s'),
            'LastUpdatedBy' => $userDetails->ID
        ];
        
        $result = $DBConn->update_data('tija_activities', $updateData, ['activityID' => $activityId]);
        
        if ($result) {
            $updated++;
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Successfully updated {$updated} activities",
        'updated' => $updated
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
