<?php
/**
 * Get Activity Details Script
 * Returns detailed information for a specific activity
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
    
    // Get activity ID
    $activityId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if (!$activityId) {
        throw new Exception('Activity ID is required');
    }
    
    // Get activity details with related information
    $activity = $DBConn->select_data('tija_activities', ['activityID' => $activityId], true);
    
    if (!$activity) {
        throw new Exception('Activity not found');
    }
    
    // Get related information
    $category = null;
    $type = null;
    $owner = null;
    $project = null;
    $client = null;
    
    if ($activity->activityCategoryID) {
        $category = $DBConn->select_data('activity_categories', ['activityCategoryID' => $activity->activityCategoryID], true);
    }
    
    if ($activity->activityTypeID) {
        $type = $DBConn->select_data('tija_activity_types', ['activityTypeID' => $activity->activityTypeID], true);
    }
    
    if ($activity->activityOwnerID) {
        $owner = $DBConn->select_data('users', ['ID' => $activity->activityOwnerID], true);
    }
    
    if ($activity->projectID) {
        $project = $DBConn->select_data('projects', ['projectID' => $activity->projectID], true);
    }
    
    if ($activity->clientID) {
        $client = $DBConn->select_data('clients', ['clientID' => $activity->clientID], true);
    }
    
    // Format response
    $response = [
        'success' => true,
        'activity' => [
            'activityID' => $activity->activityID,
            'activityName' => $activity->activityName,
            'activityDescription' => $activity->activityDescription,
            'activityDate' => $activity->activityDate,
            'activityStartTime' => $activity->activityStartTime,
            'activityDuration' => $activity->activityDuration,
            'priority' => $activity->priority,
            'activityStatusID' => $activity->activityStatusID,
            'activityNotes' => $activity->activityNotes,
            'activityCategoryName' => $category ? $category->activityCategoryName : null,
            'activityTypeName' => $type ? $type->activityTypeName : null,
            'activityOwnerName' => $owner ? $owner->firstName . ' ' . $owner->lastName : null,
            'projectName' => $project ? $project->projectName : null,
            'clientName' => $client ? $client->clientName : null,
            'CreatedDate' => $activity->CreatedDate,
            'LastUpdate' => $activity->LastUpdate
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
