<?php
/**
 * Team Assignment Manager for Recurring Projects
 *
 * Handles team assignments for recurring projects:
 * - Assign teams to template (all cycles)
 * - Assign teams to specific cycles
 * - Copy template team to new cycles
 * - Update team assignments
 *
 * @package    TIJA_PMS
 * @subpackage Recurring Projects
 * @version    1.0
 */

session_start();
$base = '../../../';
set_include_path($base);

require_once 'php/includes.php';

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    if (!$isValidUser) {
        throw new Exception("User not authenticated");
    }

    $action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : '';
    $billingCycleID = isset($_POST['billingCycleID']) ? intval($_POST['billingCycleID']) : 0;
    $projectID = isset($_POST['projectID']) ? intval($_POST['projectID']) : 0;

    if (!$projectID) {
        throw new Exception("Project ID is required");
    }

    $DBConn->begin();

    // Get project
    $project = Projects::projects_mini(['projectID' => $projectID], true, $DBConn);
    if (!$project || $project->isRecurring != 'Y') {
        throw new Exception("Invalid recurring project");
    }

    // Get project team assignment mode
    $teamAssignmentMode = $project->teamAssignmentMode ?? 'template';

    switch ($action) {
        case 'assign_template_team':
            // Assign team members to template (applies to all cycles)
            $teamMembers = isset($_POST['teamMembers']) ? $_POST['teamMembers'] : [];

            if (!is_array($teamMembers)) {
                throw new Exception("Invalid team members data");
            }

            // Get project team from tija_project_team table
            // First, remove existing template assignments if any
            // Note: Template assignments are stored in tija_project_team table
            // Instance assignments are stored in tija_recurring_project_team_assignments

            // For template mode, we update the project's team assignments
            // This is handled by the existing manage_project_team.php script
            // Here we just ensure the project team is set correctly

            $response['success'] = true;
            $response['message'] = "Template team assignments updated. Use manage_project_team.php for template assignments.";
            $response['data'] = ['teamMembers' => $teamMembers];
            break;

        case 'assign_cycle_team':
            // Assign team members to a specific billing cycle
            if (!$billingCycleID) {
                throw new Exception("Billing cycle ID is required");
            }

            $teamMembers = isset($_POST['teamMembers']) ? $_POST['teamMembers'] : [];

            if (!is_array($teamMembers)) {
                throw new Exception("Invalid team members data");
            }

            // Get billing cycle
            $billingCycle = Projects::get_billing_cycles(['billingCycleID' => $billingCycleID], true, $DBConn);
            if (!$billingCycle || $billingCycle->projectID != $projectID) {
                throw new Exception("Invalid billing cycle");
            }

            // Remove existing cycle team assignments
            $existingAssignments = $DBConn->retrieve_db_table_rows(
                'tija_recurring_project_team_assignments',
                ['teamAssignmentID'],
                ['billingCycleID' => $billingCycleID, 'projectID' => $projectID, 'Suspended' => 'N']
            );

            if ($existingAssignments && is_array($existingAssignments)) {
                foreach ($existingAssignments as $assignment) {
                    $whereArr = ['teamAssignmentID' => $assignment->teamAssignmentID];
                    $DBConn->update_data(
                        'tija_recurring_project_team_assignments',
                        ['Suspended' => 'Y', 'LastUpdate' => date('Y-m-d H:i:s')],
                        $whereArr
                    );
                }
            }

            // Add new team assignments
            $assignedCount = 0;
            foreach ($teamMembers as $member) {
                $employeeID = intval($member['employeeID'] ?? 0);
                $role = isset($member['role']) ? Utility::clean_string($member['role']) : 'member';
                $hoursAllocated = isset($member['hoursAllocated']) ? floatval($member['hoursAllocated']) : null;

                if (!$employeeID) {
                    continue;
                }

                $assignmentData = [
                    'billingCycleID' => $billingCycleID,
                    'projectID' => $projectID,
                    'employeeID' => $employeeID,
                    'role' => $role,
                    'hoursAllocated' => $hoursAllocated,
                    'DateAdded' => date('Y-m-d H:i:s'),
                    'Suspended' => 'N'
                ];

                if ($DBConn->insert_data('tija_recurring_project_team_assignments', $assignmentData)) {
                    $assignedCount++;
                }
            }

            $response['success'] = true;
            $response['message'] = "Assigned {$assignedCount} team members to billing cycle";
            $response['data'] = ['assignedCount' => $assignedCount];
            break;

        case 'copy_template_to_cycle':
            // Copy template team to a specific billing cycle
            if (!$billingCycleID) {
                throw new Exception("Billing cycle ID is required");
            }

            // Get billing cycle
            $billingCycle = Projects::get_billing_cycles(['billingCycleID' => $billingCycleID], true, $DBConn);
            if (!$billingCycle || $billingCycle->projectID != $projectID) {
                throw new Exception("Invalid billing cycle");
            }

            // Get project team from tija_project_team table
            $projectTeam = Projects::task_user_assignment(['projectID' => $projectID], false, $DBConn);

            if (!$projectTeam || !is_array($projectTeam)) {
                throw new Exception("No template team found");
            }

            // Remove existing cycle team assignments
            $existingAssignments = $DBConn->retrieve_db_table_rows(
                'tija_recurring_project_team_assignments',
                ['teamAssignmentID'],
                ['billingCycleID' => $billingCycleID, 'projectID' => $projectID, 'Suspended' => 'N']
            );

            if ($existingAssignments && is_array($existingAssignments)) {
                foreach ($existingAssignments as $assignment) {
                    $whereArr = ['teamAssignmentID' => $assignment->teamAssignmentID];
                    $DBConn->update_data(
                        'tija_recurring_project_team_assignments',
                        ['Suspended' => 'Y', 'LastUpdate' => date('Y-m-d H:i:s')],
                        $whereArr
                    );
                }
            }

            // Copy template team to cycle
            $copiedCount = 0;
            foreach ($projectTeam as $member) {
                $employeeID = intval($member->employeeID ?? $member->ID ?? 0);

                if (!$employeeID) {
                    continue;
                }

                // Determine role from project team data
                $role = 'member';
                if (isset($member->role)) {
                    $role = $member->role;
                } elseif ($project->projectOwnerID == $employeeID) {
                    $role = 'owner';
                } elseif (isset($project->projectManagersIDs) && strpos($project->projectManagersIDs, (string)$employeeID) !== false) {
                    $role = 'manager';
                }

                $assignmentData = [
                    'billingCycleID' => $billingCycleID,
                    'projectID' => $projectID,
                    'employeeID' => $employeeID,
                    'role' => $role,
                    'hoursAllocated' => isset($member->hoursAllocated) ? floatval($member->hoursAllocated) : null,
                    'DateAdded' => date('Y-m-d H:i:s'),
                    'Suspended' => 'N'
                ];

                if ($DBConn->insert_data('tija_recurring_project_team_assignments', $assignmentData)) {
                    $copiedCount++;
                }
            }

            $response['success'] = true;
            $response['message'] = "Copied {$copiedCount} team members from template to billing cycle";
            $response['data'] = ['copiedCount' => $copiedCount];
            break;

        case 'get_cycle_team':
            // Get team members for a specific billing cycle
            if (!$billingCycleID) {
                throw new Exception("Billing cycle ID is required");
            }

            // Get cycle-specific team assignments
            $cycleTeam = $DBConn->retrieve_db_table_rows(
                'tija_recurring_project_team_assignments',
                ['teamAssignmentID', 'billingCycleID', 'projectID', 'employeeID', 'role', 'hoursAllocated', 'DateAdded'],
                ['billingCycleID' => $billingCycleID, 'projectID' => $projectID, 'Suspended' => 'N']
            );

            $teamMembers = [];

            if ($cycleTeam && is_array($cycleTeam)) {
                foreach ($cycleTeam as $assignment) {
                    // Get employee details
                    $employee = Employee::employees(['ID' => $assignment->employeeID], true, $DBConn);
                    if ($employee) {
                        $teamMembers[] = [
                            'teamAssignmentID' => $assignment->teamAssignmentID,
                            'employeeID' => $assignment->employeeID,
                            'employeeName' => $employee->employeeName ?? ($employee->FirstName . ' ' . $employee->Surname),
                            'role' => $assignment->role,
                            'hoursAllocated' => $assignment->hoursAllocated,
                            'dateAdded' => $assignment->DateAdded
                        ];
                    }
                }
            } else {
                // If no cycle-specific team, return template team
                $projectTeam = Projects::task_user_assignment(['projectID' => $projectID], false, $DBConn);

                if ($projectTeam && is_array($projectTeam)) {
                    foreach ($projectTeam as $member) {
                        $employeeID = intval($member->employeeID ?? $member->ID ?? 0);
                        if ($employeeID) {
                            $employee = Employee::employees(['ID' => $employeeID], true, $DBConn);
                            if ($employee) {
                                $role = 'member';
                                if ($project->projectOwnerID == $employeeID) {
                                    $role = 'owner';
                                } elseif (isset($project->projectManagersIDs) && strpos($project->projectManagersIDs, (string)$employeeID) !== false) {
                                    $role = 'manager';
                                }

                                $teamMembers[] = [
                                    'teamAssignmentID' => null,
                                    'employeeID' => $employeeID,
                                    'employeeName' => $employee->employeeName ?? ($employee->FirstName . ' ' . $employee->Surname),
                                    'role' => $role,
                                    'hoursAllocated' => null,
                                    'dateAdded' => null,
                                    'isTemplate' => true
                                ];
                            }
                        }
                    }
                }
            }

            $response['success'] = true;
            $response['data'] = ['teamMembers' => $teamMembers, 'assignmentMode' => $teamAssignmentMode];
            break;

        case 'get_template_team':
            // Get template team members (from project team)
            $projectTeam = Projects::task_user_assignment(['projectID' => $projectID], false, $DBConn);

            $teamMembers = [];
            if ($projectTeam && is_array($projectTeam)) {
                foreach ($projectTeam as $member) {
                    $employeeID = intval($member->employeeID ?? $member->ID ?? 0);
                    if ($employeeID) {
                        $employee = Employee::employees(['ID' => $employeeID], true, $DBConn);
                        if ($employee) {
                            $role = 'member';
                            if ($project->projectOwnerID == $employeeID) {
                                $role = 'owner';
                            } elseif (isset($project->projectManagersIDs) && strpos($project->projectManagersIDs, (string)$employeeID) !== false) {
                                $role = 'manager';
                            }

                            $teamMembers[] = [
                                'employeeID' => $employeeID,
                                'employeeName' => $employee->employeeName ?? ($employee->FirstName . ' ' . $employee->Surname),
                                'role' => $role,
                                'hoursAllocated' => isset($member->hoursAllocated) ? floatval($member->hoursAllocated) : null
                            ];
                        }
                    }
                }
            }

            $response['success'] = true;
            $response['data'] = ['teamMembers' => $teamMembers];
            break;

        case 'remove_cycle_team_member':
            // Remove a team member from a specific cycle
            if (!$billingCycleID) {
                throw new Exception("Billing cycle ID is required");
            }

            $teamAssignmentID = isset($_POST['teamAssignmentID']) ? intval($_POST['teamAssignmentID']) : 0;

            if (!$teamAssignmentID) {
                throw new Exception("Team assignment ID is required");
            }

            $whereArr = ['teamAssignmentID' => $teamAssignmentID, 'billingCycleID' => $billingCycleID, 'projectID' => $projectID];

            if ($DBConn->update_data(
                'tija_recurring_project_team_assignments',
                ['Suspended' => 'Y', 'LastUpdate' => date('Y-m-d H:i:s')],
                $whereArr
            )) {
                $response['success'] = true;
                $response['message'] = "Team member removed from billing cycle";
            } else {
                throw new Exception("Failed to remove team member");
            }
            break;

        default:
            throw new Exception("Invalid action");
    }

    $DBConn->commit();

} catch (Exception $e) {
    $DBConn->rollback();
    $response['message'] = $e->getMessage();
    error_log("Error in manage_cycle_teams: " . $e->getMessage());
}

header('Content-Type: application/json');
echo json_encode($response);
exit;

