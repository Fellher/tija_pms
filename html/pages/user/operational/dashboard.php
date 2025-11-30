<?php
/**
 * Operational Work Dashboard - User View
 *
 * Dashboard for users to view and manage their operational tasks
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

// Check authentication
if(!$isValidUser) {
    Alert::info("You need to be logged in to access this page", true);
    include "includes/core/log_in_script.php";
    return;
}

// Include help component
include __DIR__ . '/../../../includes/components/operational_help.php';

require_once __DIR__ . '/../../../../php/classes/operationaltask.php';
require_once __DIR__ . '/../../../../php/classes/capacityplanning.php';

global $DBConn, $userID;

// Get user's tasks
$upcomingTasks = OperationalTask::getUpcomingTasks(7, ['assigneeID' => $userID], $DBConn);
$overdueTasks = OperationalTask::getOverdueTasks(['assigneeID' => $userID], $DBConn);
$inProgressTasks = $DBConn->retrieve_db_table_rows('tija_operational_tasks',
    ['operationalTaskID', 'templateID', 'dueDate', 'status', 'assigneeID'],
    ['assigneeID' => $userID, 'status' => 'in_progress', 'Suspended' => 'N']);

// Get capacity
$startDate = date('Y-m-d', strtotime('-30 days'));
$endDate = date('Y-m-d', strtotime('+30 days'));
$capacity = CapacityPlanning::getCapacityWaterline($userID, $startDate, $endDate, $DBConn);

$pageTitle = "Operational Work Dashboard";
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0"><?php echo htmlspecialchars($pageTitle); ?></h4>
                    <p class="text-muted mb-0 help-text">
                        View your assigned operational tasks and capacity.
                        <?php echo renderHelpPopover('My Operational Work Dashboard', 'This dashboard shows your assigned operational tasks and capacity. Monitor upcoming tasks, track your progress, and view your available capacity for taking on additional work.', 'right'); ?>
                    </p>
                </div>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="?s=user">User</a></li>
                        <li class="breadcrumb-item active">Operational Work</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert for overdue tasks -->
    <?php if (is_array($overdueTasks) && count($overdueTasks) > 0): ?>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-alarm-warning-line me-2"></i>
                You have <strong><?php echo count($overdueTasks); ?></strong> overdue operational task(s).
                <a href="?s=user&ss=operational&p=tasks&filter=overdue" class="alert-link">View them now</a>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Upcoming Tasks</p>
                            <h4 class="mb-2"><?php echo is_array($upcomingTasks) ? count($upcomingTasks) : 0; ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-primary rounded-3">
                                <i class="ri-calendar-todo-line font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">In Progress</p>
                            <h4 class="mb-2"><?php echo count($inProgressTasks); ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-info rounded-3">
                                <i class="ri-loader-4-line font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">
                                BAU Hours (30 days)
                                <?php echo renderHelpIcon('Time spent on Business-As-Usual (operational) tasks. This "operational tax" is necessary work to keep the business running.', 'top'); ?>
                            </p>
                            <h4 class="mb-2"><?php echo number_format($capacity['layer2_bau'] ?? 0, 1); ?>h</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-warning rounded-3">
                                <i class="ri-time-line font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">
                                Available Capacity
                                <?php echo renderHelpIcon('Hours available for additional work after accounting for non-working time, BAU tasks, and projects. This helps you understand your workload.', 'top'); ?>
                            </p>
                            <h4 class="mb-2"><?php echo number_format($capacity['available'] ?? 0, 1); ?>h</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-success rounded-3">
                                <i class="ri-checkbox-circle-line font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Tasks -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        Upcoming Tasks (Next 7 Days)
                        <?php echo renderHelpIcon('Tasks due in the next 7 days. Click "Execute" to start working on a task and complete the required checklist items.', 'right'); ?>
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (is_array($upcomingTasks) && count($upcomingTasks) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($upcomingTasks as $task): ?>
                                <tr>
                                    <td>Task #<?php echo htmlspecialchars($task['operationalTaskID']); ?></td>
                                    <td><?php echo htmlspecialchars($task['dueDate']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php
                                            echo $task['status'] === 'pending' ? 'warning' :
                                                ($task['status'] === 'in_progress' ? 'info' : 'success');
                                        ?>">
                                            <?php echo ucfirst($task['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?s=user&ss=operational&p=tasks&action=execute&taskID=<?php echo $task['operationalTaskID']; ?>"
                                           class="btn btn-sm btn-primary">
                                            <i class="ri-play-line"></i> Execute
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-muted">No upcoming tasks.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
