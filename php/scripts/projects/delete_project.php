<?php
/**
 * DELETE PROJECT SCRIPT
 * ====================
 *
 * This script handles the deletion of a project and all its related data.
 * It performs a cascading delete operation to ensure data integrity.
 *
 * DELETION ORDER:
 * 1. Subtasks (depends on tasks)
 * 2. Task assignments (depends on tasks)
 * 3. Tasks (depends on phases and project)
 * 4. Phases (depends on project)
 * 5. Team members (depends on project)
 * 6. Expenses (depends on project)
 * 7. Project itself
 *
 * @package    TIJA_PMS
 * @subpackage Project_Management
 * @author     TIJA Development Team
 * @version    1.0.0
 * @since      2024
 */

session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';

// Initialize response arrays
$errors = array();
$success = "";
$deletedCounts = array(
    'subtasks' => 0,
    'taskAssignments' => 0,
    'tasks' => 0,
    'phases' => 0,
    'teamMembers' => 0,
    'expenses' => 0,
    'feeExpenses' => 0
);

// Check if user is valid
if (!$isValidUser) {
    $errors[] = "You need to be logged in as a valid user to perform this action";
    echo json_encode(array('success' => false, 'errors' => $errors));
    exit;
}

// Check if user is admin (only admins can delete projects)
if (!$isAdmin) {
    $errors[] = "You do not have permission to delete projects. Only administrators can perform this action.";
    echo json_encode(array('success' => false, 'errors' => $errors));
    exit;
}

// Get project ID from POST data
$projectID = (isset($_POST['projectID']) && !empty($_POST['projectID']))
    ? Utility::clean_string($_POST['projectID'])
    : "";

// Validate project ID
if (empty($projectID) || !is_numeric($projectID)) {
    $errors[] = "Invalid project ID provided";
    echo json_encode(array('success' => false, 'errors' => $errors));
    exit;
}

// Verify project exists and get project details
$projectDetails = Projects::projects_full(array('projectID' => $projectID), true, $DBConn);

if (!$projectDetails) {
    $errors[] = "Project not found or you do not have access to this project";
    echo json_encode(array('success' => false, 'errors' => $errors));
    exit;
}

// Start database transaction
$DBConn->begin();

try {
    // ========================================================================
    // STEP 1: DELETE SUBTASKS
    // ========================================================================
    // First, get all tasks for this project to find their subtasks
    $projectTasks = Projects::project_tasks(array('projectID' => $projectID), false, $DBConn);

    if ($projectTasks && is_array($projectTasks)) {
        foreach ($projectTasks as $task) {
            // Delete subtasks for each task
            $subtasks = Projects::project_subtasks(array('projectTaskID' => $task->projectTaskID), false, $DBConn);

            if ($subtasks && is_array($subtasks)) {
                foreach ($subtasks as $subtask) {
                    if ($DBConn->delete_row('tija_subtasks', array('subtaskID' => $subtask->subtaskID))) {
                        $deletedCounts['subtasks']++;
                    }
                }
            }
        }
    }

    // ========================================================================
    // STEP 2: DELETE TASK ASSIGNMENTS
    // ========================================================================
    // Use the assigned_task method which returns assignments with projectID
    $taskAssignments = Projects::assigned_task(array('projectID' => $projectID), false, $DBConn);

    if ($taskAssignments && is_array($taskAssignments)) {
        foreach ($taskAssignments as $assignment) {
            if ($DBConn->delete_row('tija_assigned_project_tasks', array('assignmentTaskID' => $assignment->assignmentTaskID))) {
                $deletedCounts['taskAssignments']++;
            }
        }
    }

    // ========================================================================
    // STEP 3: DELETE PROJECT TASKS
    // ========================================================================
    if ($projectTasks && is_array($projectTasks)) {
        foreach ($projectTasks as $task) {
            if ($DBConn->delete_row('tija_project_tasks', array('projectTaskID' => $task->projectTaskID))) {
                $deletedCounts['tasks']++;
            }
        }
    }

    // ========================================================================
    // STEP 4: DELETE PROJECT PHASES
    // ========================================================================
    $projectPhases = Projects::project_phases(array('projectID' => $projectID), false, $DBConn);

    if ($projectPhases && is_array($projectPhases)) {
        foreach ($projectPhases as $phase) {
            if ($DBConn->delete_row('tija_project_phases', array('projectPhaseID' => $phase->projectPhaseID))) {
                $deletedCounts['phases']++;
            }
        }
    }

    // ========================================================================
    // STEP 5: DELETE PROJECT TEAM MEMBERS
    // ========================================================================
    $projectTeam = Projects::project_team(array('projectID' => $projectID), false, $DBConn);

    if ($projectTeam && is_array($projectTeam)) {
        foreach ($projectTeam as $member) {
            if ($DBConn->delete_row('tija_project_team', array('projectTeamMemberID' => $member->projectTeamMemberID))) {
                $deletedCounts['teamMembers']++;
            }
        }
    }

    // ========================================================================
    // STEP 6: DELETE PROJECT EXPENSES
    // ========================================================================
    $projectExpenses = Projects::project_expenses(array('projectID' => $projectID), false, $DBConn);

    if ($projectExpenses && is_array($projectExpenses)) {
        foreach ($projectExpenses as $expense) {
            if ($DBConn->delete_row('tija_project_expenses', array('expenseID' => $expense->expenseID))) {
                $deletedCounts['expenses']++;
            }
        }
    }

    // ========================================================================
    // STEP 7: DELETE PROJECT FEE EXPENSES
    // ========================================================================
    $projectFeeExpenses = Projects::project_fee_expenses(array('projectID' => $projectID), false, $DBConn);

    if ($projectFeeExpenses && is_array($projectFeeExpenses)) {
        foreach ($projectFeeExpenses as $feeExpense) {
            if ($DBConn->delete_row('tija_project_fee_expenses', array('projectFeeExpenseID' => $feeExpense->projectFeeExpenseID))) {
                $deletedCounts['feeExpenses']++;
            }
        }
    }

    // ========================================================================
    // STEP 8: DELETE THE PROJECT ITSELF
    // ========================================================================
    if (!$DBConn->delete_row('tija_projects', array('projectID' => $projectID))) {
        throw new Exception("Failed to delete the project");
    }

    // Commit transaction if all deletions succeeded
    $DBConn->commit();

    // Prepare success message
    $success = "Project '{$projectDetails->projectName}' and all related data have been successfully deleted.";

    // Build detailed deletion summary
    $deletionSummary = array(
        'project' => $projectDetails->projectName,
        'subtasks' => $deletedCounts['subtasks'],
        'taskAssignments' => $deletedCounts['taskAssignments'],
        'tasks' => $deletedCounts['tasks'],
        'phases' => $deletedCounts['phases'],
        'teamMembers' => $deletedCounts['teamMembers'],
        'expenses' => $deletedCounts['expenses'],
        'feeExpenses' => $deletedCounts['feeExpenses']
    );

    // Return success response
    echo json_encode(array(
        'success' => true,
        'message' => $success,
        'deletionSummary' => $deletionSummary
    ));

} catch (Exception $e) {
    // Rollback transaction on error
    $DBConn->rollback();

    $errors[] = "Error deleting project: " . $e->getMessage();
    error_log("Delete Project Error: " . $e->getMessage() . " | Project ID: " . $projectID);

    echo json_encode(array(
        'success' => false,
        'errors' => $errors
    ));
}

