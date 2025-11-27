<?php
/**
 * Plan Instance Manager for Recurring Projects
 *
 * Handles copying and customizing project plans for specific billing cycles
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

    if (!$billingCycleID || !$projectID) {
        throw new Exception("Missing required parameters");
    }

    $DBConn->begin();

    // Get billing cycle
    $billingCycle = Projects::get_billing_cycles(['billingCycleID' => $billingCycleID], true, $DBConn);
    if (!$billingCycle || $billingCycle->projectID != $projectID) {
        throw new Exception("Invalid billing cycle");
    }

    // Get project
    $project = Projects::projects_mini(['projectID' => $projectID], true, $DBConn);
    if (!$project || $project->isRecurring != 'Y') {
        throw new Exception("Invalid recurring project");
    }

    switch ($action) {
        case 'copy_base_plan':
            // Copy base project plan to cycle instance
            $phases = Projects::project_phases(['projectID' => $projectID, 'Suspended' => 'N'], false, $DBConn);

            if (!$phases || !is_array($phases)) {
                throw new Exception("No project plan found");
            }

            // Build plan structure
            $planData = [];
            foreach ($phases as $phase) {
                $phaseData = [
                    'phaseID' => $phase->projectPhaseID,
                    'phaseName' => $phase->projectPhaseName,
                    'phaseDescription' => $phase->phaseDescription ?? '',
                    'phaseStartDate' => $phase->phaseStartDate ?? null,
                    'phaseEndDate' => $phase->phaseEndDate ?? null,
                    'phaseWorkHrs' => $phase->phaseWorkHrs ?? 0,
                    'phaseWeighting' => $phase->phaseWeighting ?? 0,
                    'tasks' => []
                ];

                // Get tasks for this phase
                $tasks = Projects::project_tasks(['projectPhaseID' => $phase->projectPhaseID, 'Suspended' => 'N'], false, $DBConn);

                if ($tasks && is_array($tasks)) {
                    foreach ($tasks as $task) {
                        $taskData = [
                            'taskID' => $task->projectTaskID,
                            'taskName' => $task->projectTaskName,
                            'taskDescription' => $task->taskDescription ?? '',
                            'taskStart' => $task->taskStart ?? null,
                            'taskDeadline' => $task->taskDeadline ?? null,
                            'hoursAllocated' => $task->hoursAllocated ?? 0,
                            'progress' => $task->progress ?? 0,
                            'status' => $task->status ?? 'pending',
                            'subtasks' => []
                        ];

                        // Get subtasks
                        $subtasks = Projects::project_subtasks(['projectTaskID' => $task->projectTaskID, 'Suspended' => 'N'], false, $DBConn);
                        if ($subtasks && is_array($subtasks)) {
                            foreach ($subtasks as $subtask) {
                                $taskData['subtasks'][] = [
                                    'subtaskID' => $subtask->subtaskID,
                                    'subtaskName' => $subtask->subtaskName,
                                    'dueDate' => $subtask->dueDate ?? null,
                                    'progress' => $subtask->progress ?? 0,
                                    'status' => $subtask->status ?? 'pending'
                                ];
                            }
                        }

                        $phaseData['tasks'][] = $taskData;
                    }
                }

                $planData[] = $phaseData;
            }

            // Check if plan instance already exists
            $existingInstance = $DBConn->retrieve_db_table_rows(
                'tija_recurring_project_plan_instances',
                ['planInstanceID', 'billingCycleID', 'projectID', 'phaseJSON', 'isCustomized'],
                ['billingCycleID' => $billingCycleID, 'projectID' => $projectID]
            );

            $planJSON = json_encode($planData);

            if ($existingInstance && is_array($existingInstance) && count($existingInstance) > 0) {
                // Update existing instance
                $updateData = [
                    'phaseJSON' => $planJSON,
                    'isCustomized' => 'N',
                    'LastUpdate' => date('Y-m-d H:i:s')
                ];
                $whereArr = ['planInstanceID' => $existingInstance[0]->planInstanceID];

                if (!$DBConn->update_data('tija_recurring_project_plan_instances', $updateData, $whereArr)) {
                    throw new Exception("Failed to update plan instance");
                }

                $response['message'] = "Plan instance updated from base plan";
            } else {
                // Create new instance
                $insertData = [
                    'billingCycleID' => $billingCycleID,
                    'projectID' => $projectID,
                    'phaseJSON' => $planJSON,
                    'isCustomized' => 'N',
                    'DateAdded' => date('Y-m-d H:i:s'),
                    'LastUpdate' => date('Y-m-d H:i:s')
                ];

                if (!$DBConn->insert_data('tija_recurring_project_plan_instances', $insertData)) {
                    throw new Exception("Failed to create plan instance");
                }

                $response['message'] = "Plan instance created from base plan";
            }

            $response['success'] = true;
            $response['data'] = ['planInstanceID' => $existingInstance ? $existingInstance[0]->planInstanceID : $DBConn->lastInsertId()];
            break;

        case 'save_customized_plan':
            // Save customized plan for this cycle
            $planJSON = isset($_POST['planJSON']) ? $_POST['planJSON'] : '';

            if (empty($planJSON)) {
                throw new Exception("Plan data is required");
            }

            // Validate JSON
            $planData = json_decode($planJSON, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON format: " . json_last_error_msg());
            }

            // Check if plan instance exists
            $existingInstance = $DBConn->retrieve_db_table_rows(
                'tija_recurring_project_plan_instances',
                ['planInstanceID'],
                ['billingCycleID' => $billingCycleID, 'projectID' => $projectID]
            );

            if ($existingInstance && is_array($existingInstance) && count($existingInstance) > 0) {
                // Update existing instance
                $updateData = [
                    'phaseJSON' => $planJSON,
                    'isCustomized' => 'Y',
                    'LastUpdate' => date('Y-m-d H:i:s')
                ];
                $whereArr = ['planInstanceID' => $existingInstance[0]->planInstanceID];

                if (!$DBConn->update_data('tija_recurring_project_plan_instances', $updateData, $whereArr)) {
                    throw new Exception("Failed to update customized plan");
                }

                $response['message'] = "Customized plan saved successfully";
            } else {
                // Create new instance
                $insertData = [
                    'billingCycleID' => $billingCycleID,
                    'projectID' => $projectID,
                    'phaseJSON' => $planJSON,
                    'isCustomized' => 'Y',
                    'DateAdded' => date('Y-m-d H:i:s'),
                    'LastUpdate' => date('Y-m-d H:i:s')
                ];

                if (!$DBConn->insert_data('tija_recurring_project_plan_instances', $insertData)) {
                    throw new Exception("Failed to save customized plan");
                }

                $response['message'] = "Customized plan created successfully";
            }

            $response['success'] = true;
            break;

        case 'get_plan_instance':
            // Get plan instance for this cycle
            $planInstance = $DBConn->retrieve_db_table_rows(
                'tija_recurring_project_plan_instances',
                ['planInstanceID', 'billingCycleID', 'projectID', 'phaseJSON', 'isCustomized', 'DateAdded', 'LastUpdate'],
                ['billingCycleID' => $billingCycleID, 'projectID' => $projectID]
            );

            if ($planInstance && is_array($planInstance) && count($planInstance) > 0) {
                $instance = $planInstance[0];
                $planData = json_decode($instance->phaseJSON, true);

                $response['success'] = true;
                $response['data'] = [
                    'planInstanceID' => $instance->planInstanceID,
                    'isCustomized' => $instance->isCustomized,
                    'planData' => $planData,
                    'dateAdded' => $instance->DateAdded,
                    'lastUpdate' => $instance->LastUpdate
                ];
            } else {
                // Return base plan if no instance exists
                $phases = Projects::project_phases(['projectID' => $projectID, 'Suspended' => 'N'], false, $DBConn);

                if ($phases && is_array($phases)) {
                    $planData = [];
                    foreach ($phases as $phase) {
                        $phaseData = [
                            'phaseID' => $phase->projectPhaseID,
                            'phaseName' => $phase->projectPhaseName,
                            'tasks' => []
                        ];

                        $tasks = Projects::project_tasks(['projectPhaseID' => $phase->projectPhaseID, 'Suspended' => 'N'], false, $DBConn);
                        if ($tasks && is_array($tasks)) {
                            foreach ($tasks as $task) {
                                $phaseData['tasks'][] = [
                                    'taskID' => $task->projectTaskID,
                                    'taskName' => $task->projectTaskName
                                ];
                            }
                        }

                        $planData[] = $phaseData;
                    }

                    $response['success'] = true;
                    $response['data'] = [
                        'planInstanceID' => null,
                        'isCustomized' => 'N',
                        'planData' => $planData
                    ];
                } else {
                    throw new Exception("No plan found");
                }
            }
            break;

        case 'revert_to_base':
            // Revert customized plan to base plan
            $existingInstance = $DBConn->retrieve_db_table_rows(
                'tija_recurring_project_plan_instances',
                ['planInstanceID'],
                ['billingCycleID' => $billingCycleID, 'projectID' => $projectID]
            );

            if ($existingInstance && is_array($existingInstance) && count($existingInstance) > 0) {
                // Delete the instance to revert to base plan
                $whereArr = ['planInstanceID' => $existingInstance[0]->planInstanceID];
                if (!$DBConn->delete_data('tija_recurring_project_plan_instances', $whereArr)) {
                    throw new Exception("Failed to revert plan");
                }

                $response['success'] = true;
                $response['message'] = "Plan reverted to base plan";
            } else {
                $response['success'] = true;
                $response['message'] = "Plan is already using base plan";
            }
            break;

        default:
            throw new Exception("Invalid action");
    }

    $DBConn->commit();

} catch (Exception $e) {
    $DBConn->rollback();
    $response['message'] = $e->getMessage();
    error_log("Error in manage_plan_instances: " . $e->getMessage());
}

header('Content-Type: application/json');
echo json_encode($response);
exit;

