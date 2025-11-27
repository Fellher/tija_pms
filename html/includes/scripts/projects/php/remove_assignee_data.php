<?php
/**
 * Remove Assignee Data Handler
 *
 * Handles the removal of assignees from tasks via form submission.
 * This script processes the form data and updates the database.
 *
 * @package    PMS
 * @subpackage Projects
 * @author     SKM Development Team
 * @version    3.0.0
 * @since      1.0.0
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once '../../../../php/config/config.inc.php';
require_once '../../../../php/includes.php';

// Set content type to JSON for AJAX responses
header('Content-Type: application/json');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

// Get form data
$assignmentId = isset($_POST['assignmentId']) ? trim($_POST['assignmentId']) : '';
$assigneeId = isset($_POST['assigneeId']) ? trim($_POST['assigneeId']) : '';
$taskId = isset($_POST['taskId']) ? trim($_POST['taskId']) : '';
$projectId = isset($_POST['projectId']) ? trim($_POST['projectId']) : '';

// Validate required fields
if (empty($assignmentId) || empty($assigneeId) || empty($taskId) || empty($projectId)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit;
}

try {
    // Get database connection
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Check if assignment exists
    $checkQuery = "SELECT * FROM tija_assigned_project_tasks 
                   WHERE assignmentId = ? AND assigneeId = ? AND taskId = ? AND projectId = ? 
                   AND Suspended = 'N'";
    
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ssss", $assignmentId, $assigneeId, $taskId, $projectId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Assignment not found or already removed');
    }
    
    // Update assignment to suspended
    $updateQuery = "UPDATE tija_assigned_project_tasks 
                    SET Suspended = 'Y', 
                        assignmentStatus = 'suspended',
                        updatedAt = NOW()
                    WHERE assignmentId = ? AND assigneeId = ? AND taskId = ? AND projectId = ?";
    
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ssss", $assignmentId, $assigneeId, $taskId, $projectId);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Success - redirect back to project page with success message
            $redirectUrl = "../../../pages/user/projects/project.php?pid=" . urlencode($projectId) . "&state=plan&msg=assignee_removed";
            header("Location: " . $redirectUrl);
            exit;
        } else {
            throw new Exception('No rows were updated');
        }
    } else {
        throw new Exception('Database update failed: ' . $stmt->error);
    }
    
} catch (Exception $e) {
    // Log error
    error_log("Error removing assignee: " . $e->getMessage());
    
    // Redirect back with error message
    $redirectUrl = "../../../pages/user/projects/project.php?pid=" . urlencode($projectId) . "&state=plan&error=assignee_removal_failed";
    header("Location: " . $redirectUrl);
    exit;
}
?>
