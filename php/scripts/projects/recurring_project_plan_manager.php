<?php
/**
 * RECURRING PROJECT PLAN MANAGER
 * =============================
 *
 * Handles plan replication for recurring projects across billing cycles.
 * This script manages:
 * - Storing plan templates (phases, tasks, subtasks) for recurring projects
 * - Replicating plans for each billing cycle
 * - Adjusting dates based on cycle dates
 * - Allowing configuration of cycle-specific plans
 *
 * @package    TIJA_PMS
 * @subpackage Project_Management
 * @version    1.0.0
 * @author     TIJA Development Team
 */

// This file can be included from multiple contexts
// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine base path based on where this file is included from
$base = '../../../';
if (strpos(__FILE__, 'php/scripts/projects/') !== false) {
    // File is in php/scripts/projects/, so base is 3 levels up
    $base = '../../../';
} elseif (strpos(__FILE__, 'php/classes/') !== false) {
    // File is being included from php/classes/, so base is 2 levels up
    $base = '../../';
}

// Check if DBConn is available from global scope (when called from Projects class)
if (isset($GLOBALS['DBConn']) && !isset($DBConn)) {
    $DBConn = $GLOBALS['DBConn'];
}

// Only include if not already included and if DBConn is not already available
// When called from Projects class, DBConn is already passed, so we don't need includes
if (!defined('TIJA_INCLUDES_LOADED') && !isset($DBConn)) {
    set_include_path($base);
    // Check if config exists before including
    if (!isset($config)) {
        // Try to load config first
        $configPath = $base . 'php/config/config.php';
        if (file_exists($configPath)) {
            include $configPath;
        }
    }
    // Only include if config is now available
    if (isset($config)) {
        include 'php/includes.php';
    }
}

/**
 * Store project plan as template for recurring project
 *
 * @param int $projectID - Project ID
 * @param object $DBConn - Database connection
 * @return bool - Success status
 */
function store_recurring_project_plan_template($projectID, $DBConn) {
    if (!$projectID || !$DBConn) {
        error_log("store_recurring_project_plan_template: Missing projectID or DBConn");
        return false;
    }

    // Get project details
    $project = Projects::projects_mini(['projectID' => $projectID], true, $DBConn);
    if (!$project) {
        error_log("store_recurring_project_plan_template: Project {$projectID} not found");
        return false;
    }

    // Check if project is recurring
    $isRecurring = false;
    if (isset($project->isRecurring) && $project->isRecurring === 'Y') {
        $isRecurring = true;
        error_log("store_recurring_project_plan_template: Project {$projectID} is recurring (isRecurring=Y)");
    } elseif (isset($project->projectType) && $project->projectType === 'recurrent') {
        $isRecurring = true;
        error_log("store_recurring_project_plan_template: Project {$projectID} is recurring (projectType=recurrent)");
    } else {
        error_log("store_recurring_project_plan_template: Project {$projectID} is NOT recurring. isRecurring=" . (isset($project->isRecurring) ? $project->isRecurring : 'NOT SET') . ", projectType=" . (isset($project->projectType) ? $project->projectType : 'NOT SET'));
    }

    if (!$isRecurring) {
        return false; // Not a recurring project
    }

    // Get all phases for this project
    $phases = Projects::project_phases_mini(['projectID' => $projectID, 'Suspended' => 'N'], false, $DBConn);
    error_log("store_recurring_project_plan_template: Found " . ($phases ? count($phases) : 0) . " phases for project {$projectID}");

    if (!$phases || !is_array($phases) || count($phases) === 0) {
        error_log("store_recurring_project_plan_template: No phases found for project {$projectID}");
        return false; // No phases to store
    }

    // Store each phase as template
    $templatesStored = 0;
    foreach ($phases as $phase) {
        // Check if template already exists for this phase
        $existingTemplate = $DBConn->fetch_all_rows(
            "SELECT templatePhaseID FROM tija_recurring_project_plan_templates
             WHERE projectID = ? AND originalPhaseID = ? AND Suspended = 'N'",
            array(
                array($projectID, 'i'),
                array($phase->projectPhaseID, 'i')
            )
        );

        if ($existingTemplate && count($existingTemplate) > 0) {
            continue; // Template already exists
        }

        // Get tasks for this phase
        $tasks = Projects::project_tasks(
            ['projectPhaseID' => $phase->projectPhaseID, 'Suspended' => 'N'],
            false,
            $DBConn
        );

        // Calculate phase duration in days
        $phaseDuration = 0;
        if ($phase->phaseStartDate && $phase->phaseEndDate) {
            $start = new DateTime($phase->phaseStartDate);
            $end = new DateTime($phase->phaseEndDate);
            $phaseDuration = $start->diff($end)->days + 1;
        }

        // Store phase template
        $templateData = array(
            'projectID' => $projectID,
            'originalPhaseID' => $phase->projectPhaseID,
            'phaseName' => $phase->projectPhaseName,
            'phaseDescription' => isset($phase->phaseDescription) ? $phase->phaseDescription : null,
            'phaseOrder' => isset($phase->phaseOrder) ? $phase->phaseOrder : 0,
            'phaseDuration' => $phaseDuration,
            'phaseWorkHrs' => isset($phase->phaseWorkHrs) ? $phase->phaseWorkHrs : null,
            'phaseWeighting' => isset($phase->phaseWeighting) ? $phase->phaseWeighting : null,
            'billingMilestone' => isset($phase->billingMilestone) ? $phase->billingMilestone : 'N',
            'relativeStartDay' => 0, // Days from cycle start (0 = start of cycle)
            'relativeEndDay' => $phaseDuration > 0 ? $phaseDuration - 1 : 0,
            'applyToAllCycles' => 'Y', // Default: apply to all cycles
            'DateAdded' => date('Y-m-d H:i:s'),
            'Suspended' => 'N'
        );

        if ($DBConn->insert_data('tija_recurring_project_plan_templates', $templateData)) {
            $templatePhaseID = $DBConn->lastInsertId();
            $templatesStored++;

            // Store tasks for this phase template
            if ($tasks && is_array($tasks)) {
                foreach ($tasks as $task) {
                    // Calculate task relative dates within phase
                    $taskRelativeStart = 0;
                    $taskRelativeEnd = 0;

                    if ($phase->phaseStartDate && $task->taskStart) {
                        $phaseStart = new DateTime($phase->phaseStartDate);
                        $taskStart = new DateTime($task->taskStart);
                        $taskRelativeStart = $phaseStart->diff($taskStart)->days;
                    }

                    if ($phase->phaseStartDate && $task->taskDeadline) {
                        $phaseStart = new DateTime($phase->phaseStartDate);
                        $taskEnd = new DateTime($task->taskDeadline);
                        $taskRelativeEnd = $phaseStart->diff($taskEnd)->days;
                    }

                    $taskTemplateData = array(
                        'templatePhaseID' => $templatePhaseID,
                        'originalTaskID' => $task->projectTaskID,
                        'taskName' => $task->projectTaskName,
                        'taskCode' => $task->projectTaskCode,
                        'taskDescription' => isset($task->taskDescription) ? $task->taskDescription : null,
                        'relativeStartDay' => $taskRelativeStart,
                        'relativeEndDay' => $taskRelativeEnd,
                        'hoursAllocated' => isset($task->hoursAllocated) ? $task->hoursAllocated : null,
                        'taskWeighting' => isset($task->taskWeighting) ? $task->taskWeighting : null,
                        'assigneeID' => isset($task->assigneeID) ? $task->assigneeID : null,
                        'applyToAllCycles' => 'Y',
                        'DateAdded' => date('Y-m-d H:i:s'),
                        'Suspended' => 'N'
                    );

                    $DBConn->insert_data('tija_recurring_project_plan_task_templates', $taskTemplateData);
                }
            }
        }
    }

    return $templatesStored > 0;
}

/**
 * Replicate project plan for a specific billing cycle
 *
 * @param int $projectID - Project ID
 * @param int $billingCycleID - Billing cycle ID
 * @param object $DBConn - Database connection
 * @return bool - Success status
 */
function replicate_plan_for_cycle($projectID, $billingCycleID, $DBConn) {
    if (!$projectID || !$billingCycleID || !$DBConn) {
        error_log("replicate_plan_for_cycle: Missing required parameters");
        return false;
    }

    // Ensure DBConn is valid
    if (!is_object($DBConn) || !method_exists($DBConn, 'fetch_all_rows')) {
        error_log("replicate_plan_for_cycle: Invalid DBConn object");
        return false;
    }

    // Get billing cycle details
    $cycle = Projects::get_billing_cycles(['billingCycleID' => $billingCycleID], true, $DBConn);
    if (!$cycle) {
        return false;
    }

    // Verify cycle belongs to project
    if ($cycle->projectID != $projectID) {
        return false;
    }

    // Check if plan already replicated for this cycle
    $existingPhases = $DBConn->fetch_all_rows(
        "SELECT projectPhaseID FROM tija_project_phases
         WHERE projectID = ? AND billingCycleID = ? AND Suspended = 'N'",
        array(
            array($projectID, 'i'),
            array($billingCycleID, 'i')
        )
    );

    if ($existingPhases && count($existingPhases) > 0) {
        return true; // Plan already replicated
    }

    // Get plan templates for this project
    $templates = $DBConn->fetch_all_rows(
        "SELECT * FROM tija_recurring_project_plan_templates
         WHERE projectID = ? AND Suspended = 'N'
         ORDER BY phaseOrder ASC, templatePhaseID ASC",
        array(array($projectID, 'i'))
    );

    if (!$templates || count($templates) === 0) {
        return false; // No templates found
    }

    $cycleStartDate = new DateTime($cycle->cycleStartDate);
    $cycleEndDate = new DateTime($cycle->cycleEndDate);
    $cycleDuration = $cycleStartDate->diff($cycleEndDate)->days + 1;

    $phasesCreated = 0;
    $tasksCreated = 0;

    foreach ($templates as $template) {
        // Check if template applies to this cycle
        if (isset($template->applyToAllCycles) && $template->applyToAllCycles === 'N') {
            // Check cycle-specific configuration
            $cycleConfig = $DBConn->fetch_all_rows(
                "SELECT * FROM tija_recurring_project_plan_cycle_config
                 WHERE templatePhaseID = ? AND billingCycleID = ? AND Suspended = 'N'",
                array(
                    array($template->templatePhaseID, 'i'),
                    array($billingCycleID, 'i')
                )
            );

            if (!$cycleConfig || count($cycleConfig) === 0) {
                continue; // Skip this phase for this cycle
            }
        }

        // Calculate phase dates for this cycle
        $phaseStartDate = clone $cycleStartDate;
        $phaseEndDate = clone $cycleStartDate;

        if (isset($template->relativeStartDay)) {
            $phaseStartDate->modify("+{$template->relativeStartDay} days");
        }

        if (isset($template->relativeEndDay)) {
            $phaseEndDate->modify("+{$template->relativeEndDay} days");
        } elseif (isset($template->phaseDuration) && $template->phaseDuration > 0) {
            $phaseEndDate->modify("+" . ($template->phaseDuration - 1) . " days");
        } else {
            // Default to end of cycle
            $phaseEndDate = clone $cycleEndDate;
        }

        // Ensure dates don't exceed cycle boundaries
        if ($phaseStartDate < $cycleStartDate) {
            $phaseStartDate = clone $cycleStartDate;
        }
        if ($phaseEndDate > $cycleEndDate) {
            $phaseEndDate = clone $cycleEndDate;
        }

        // Create phase for this cycle
        $phaseData = array(
            'projectID' => $projectID,
            'billingCycleID' => $billingCycleID,
            'projectPhaseName' => $template->phaseName,
            'phaseStartDate' => $phaseStartDate->format('Y-m-d'),
            'phaseEndDate' => $phaseEndDate->format('Y-m-d'),
            'phaseWorkHrs' => isset($template->phaseWorkHrs) ? $template->phaseWorkHrs : null,
            'phaseWeighting' => isset($template->phaseWeighting) ? $template->phaseWeighting : null,
            'billingMilestone' => isset($template->billingMilestone) ? $template->billingMilestone : 'N',
            'DateAdded' => date('Y-m-d H:i:s'),
            'LastUpdate' => date('Y-m-d H:i:s'),
            'LastUpdatedByID' => isset($_SESSION['userID']) ? $_SESSION['userID'] : 0,
            'Lapsed' => 'N',
            'Suspended' => 'N'
        );

        if ($DBConn->insert_data('tija_project_phases', $phaseData)) {
            $newPhaseID = $DBConn->lastInsertId();
            $phasesCreated++;

            // Get task templates for this phase
            $taskTemplates = $DBConn->fetch_all_rows(
                "SELECT * FROM tija_recurring_project_plan_task_templates
                 WHERE templatePhaseID = ? AND Suspended = 'N'
                 ORDER BY relativeStartDay ASC",
                array(array($template->templatePhaseID, 'i'))
            );

            if ($taskTemplates && count($taskTemplates) > 0) {
                foreach ($taskTemplates as $taskTemplate) {
                    // Check if task applies to this cycle
                    if (isset($taskTemplate->applyToAllCycles) && $taskTemplate->applyToAllCycles === 'N') {
                        $taskCycleConfig = $DBConn->fetch_all_rows(
                            "SELECT * FROM tija_recurring_project_plan_cycle_config
                             WHERE templateTaskID = ? AND billingCycleID = ? AND Suspended = 'N'",
                            array(
                                array($taskTemplate->templateTaskID, 'i'),
                                array($billingCycleID, 'i')
                            )
                        );

                        if (!$taskCycleConfig || count($taskCycleConfig) === 0) {
                            continue; // Skip this task for this cycle
                        }
                    }

                    // Calculate task dates relative to phase start
                    $taskStartDate = clone $phaseStartDate;
                    $taskEndDate = clone $phaseStartDate;

                    if (isset($taskTemplate->relativeStartDay)) {
                        $taskStartDate->modify("+{$taskTemplate->relativeStartDay} days");
                    }

                    if (isset($taskTemplate->relativeEndDay)) {
                        $taskEndDate->modify("+{$taskTemplate->relativeEndDay} days");
                    } else {
                        // Default to phase end date
                        $taskEndDate = clone $phaseEndDate;
                    }

                    // Ensure dates don't exceed phase boundaries
                    if ($taskStartDate < $phaseStartDate) {
                        $taskStartDate = clone $phaseStartDate;
                    }
                    if ($taskEndDate > $phaseEndDate) {
                        $taskEndDate = clone $phaseEndDate;
                    }

                    // Create task for this cycle
                    $taskData = array(
                        'projectID' => $projectID,
                        'projectPhaseID' => $newPhaseID,
                        'billingCycleID' => $billingCycleID,
                        'projectTaskCode' => $taskTemplate->taskCode . '-' . $cycle->cycleNumber,
                        'projectTaskName' => $taskTemplate->taskName,
                        'taskStart' => $taskStartDate->format('Y-m-d'),
                        'taskDeadline' => $taskEndDate->format('Y-m-d'),
                        'taskDescription' => isset($taskTemplate->taskDescription) ? $taskTemplate->taskDescription : null,
                        'hoursAllocated' => isset($taskTemplate->hoursAllocated) ? $taskTemplate->hoursAllocated : null,
                        'taskWeighting' => isset($taskTemplate->taskWeighting) ? $taskTemplate->taskWeighting : null,
                        'assigneeID' => isset($taskTemplate->assigneeID) ? $taskTemplate->assigneeID : null,
                        'taskStatusID' => 1, // Default: Not Started
                        'status' => 'active',
                        'progress' => 0,
                        'DateAdded' => date('Y-m-d H:i:s'),
                        'DateLastUpdated' => date('Y-m-d H:i:s'),
                        'Lapsed' => 'N',
                        'Suspended' => 'N'
                    );

                    if ($DBConn->insert_data('tija_project_tasks', $taskData)) {
                        $tasksCreated++;
                    }
                }
            }
        }
    }

    return $phasesCreated > 0;
}

/**
 * Activate billing cycle and replicate plan
 *
 * @param int $billingCycleID - Billing cycle ID
 * @param object $DBConn - Database connection
 * @return array - Result with success status and details
 */
function activate_billing_cycle($billingCycleID, $DBConn) {
    if (!$billingCycleID || !$DBConn) {
        return array('success' => false, 'message' => 'Invalid parameters');
    }

    // Get cycle details
    $cycle = Projects::get_billing_cycles(['billingCycleID' => $billingCycleID], true, $DBConn);
    if (!$cycle) {
        return array('success' => false, 'message' => 'Billing cycle not found');
    }

    // Update cycle status to 'active'
    Projects::update_billing_cycle_status(
        $billingCycleID,
        'active',
        array(),
        $DBConn
    );

    // Replicate plan for this cycle
    $replicated = replicate_plan_for_cycle($cycle->projectID, $billingCycleID, $DBConn);

    if ($replicated) {
        return array(
            'success' => true,
            'message' => 'Billing cycle activated and plan replicated successfully',
            'billingCycleID' => $billingCycleID,
            'projectID' => $cycle->projectID
        );
    } else {
        return array(
            'success' => false,
            'message' => 'Failed to replicate plan for billing cycle'
        );
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    $action = $_POST['action'];
    $response = array('success' => false, 'message' => 'Unknown action');

    try {
        switch ($action) {
            case 'store_template':
                $projectID = isset($_POST['projectID']) ? intval($_POST['projectID']) : 0;
                if ($projectID > 0) {
                    $result = store_recurring_project_plan_template($projectID, $DBConn);
                    $response = array(
                        'success' => $result,
                        'message' => $result ? 'Plan template stored successfully' : 'Failed to store plan template'
                    );
                }
                break;

            case 'replicate_for_cycle':
                $projectID = isset($_POST['projectID']) ? intval($_POST['projectID']) : 0;
                $billingCycleID = isset($_POST['billingCycleID']) ? intval($_POST['billingCycleID']) : 0;
                if ($projectID > 0 && $billingCycleID > 0) {
                    $result = replicate_plan_for_cycle($projectID, $billingCycleID, $DBConn);
                    $response = array(
                        'success' => $result,
                        'message' => $result ? 'Plan replicated successfully' : 'Failed to replicate plan'
                    );
                }
                break;

            case 'activate_cycle':
                $billingCycleID = isset($_POST['billingCycleID']) ? intval($_POST['billingCycleID']) : 0;
                if ($billingCycleID > 0) {
                    $response = activate_billing_cycle($billingCycleID, $DBConn);
                }
                break;
        }
    } catch (Exception $e) {
        $response = array(
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        );
    }

    echo json_encode($response);
    exit;
}

