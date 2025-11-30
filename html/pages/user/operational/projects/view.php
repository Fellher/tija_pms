<?php
/**
 * View Operational Project - User
 *
 * View operational project (BAU bucket) details
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

if(!$isValidUser) {
    Alert::info("You need to be logged in to access this page", true);
    include "includes/core/log_in_script.php";
    return;
}

// Include help component
include __DIR__ . '/../../../includes/components/operational_help.php';

global $DBConn, $userID;

$projectID = $_GET['id'] ?? null;

if (!$projectID) {
    Alert::error("Project ID is required", true);
    header("Location: ?s=user&ss=operational&p=projects");
    exit;
}

// Get project
$project = $DBConn->retrieve_db_table_rows('tija_operational_projects',
    ['operationalProjectID', 'projectCode', 'projectName', 'functionalArea', 'fiscalYear',
     'allocatedHours', 'actualHours', 'fteRequirement', 'DateAdded'],
    ['operationalProjectID' => $projectID, 'Suspended' => 'N'],
    true);

if (!$project) {
    Alert::error("Project not found", true);
    header("Location: ?s=user&ss=operational&p=projects");
    exit;
}

// Get time logs for this project
$timeLogs = $DBConn->retrieve_db_table_rows('tija_tasks_time_logs',
    ['timelogID', 'employeeID', 'taskDate', 'workHours', 'taskNarrative', 'operationalTaskID'],
    ['operationalProjectID' => $projectID, 'taskType' => 'operational', 'Suspended' => 'N'],
    false,
    'ORDER BY taskDate DESC, timelogID DESC');

// Calculate utilization
$utilization = $project['allocatedHours'] > 0
    ? ($project['actualHours'] / $project['allocatedHours']) * 100
    : 0;

// Get resource allocations (employees who have logged time)
$resourceAllocations = [];
if ($timeLogs) {
    $employeeIDs = array_unique(array_column($timeLogs, 'employeeID'));
    foreach ($employeeIDs as $empID) {
        $employee = Data::users(['ID' => $empID], true, $DBConn);
        $empHours = 0;
        foreach ($timeLogs as $log) {
            if ($log['employeeID'] == $empID) {
                $empHours += (float)($log['workHours'] ?? 0);
            }
        }
        if ($employee) {
            $resourceAllocations[] = [
                'employeeID' => $empID,
                'name' => $employee->FirstName . ' ' . $employee->Surname,
                'hours' => $empHours
            ];
        }
    }
}

$pageTitle = "Project: " . htmlspecialchars($project['projectName'] ?? 'Unknown');
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0"><?php echo htmlspecialchars($pageTitle); ?></h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="?s=user">User</a></li>
                        <li class="breadcrumb-item"><a href="?s=user&ss=operational">Operational Work</a></li>
                        <li class="breadcrumb-item"><a href="?s=user&ss=operational&p=projects">Projects</a></li>
                        <li class="breadcrumb-item active">View</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Project Overview -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Allocated Hours</p>
                            <h4 class="mb-2"><?php echo number_format($project['allocatedHours'] ?? 0, 0); ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-primary rounded-3">
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
                            <p class="text-truncate font-size-14 mb-2">Actual Hours</p>
                            <h4 class="mb-2 text-info"><?php echo number_format($project['actualHours'] ?? 0, 0); ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-info rounded-3">
                                <i class="ri-check-line font-size-18"></i>
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
                            <p class="text-truncate font-size-14 mb-2">Utilization</p>
                            <h4 class="mb-2 text-<?php echo $utilization > 100 ? 'danger' : ($utilization > 80 ? 'warning' : 'success'); ?>">
                                <?php echo number_format($utilization, 1); ?>%
                            </h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-warning rounded-3">
                                <i class="ri-pie-chart-line font-size-18"></i>
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
                            <p class="text-truncate font-size-14 mb-2">FTE Requirement</p>
                            <h4 class="mb-2 text-success"><?php echo number_format($project['fteRequirement'] ?? 0, 2); ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-success rounded-3">
                                <i class="ri-user-line font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Project Details -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Project Information</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h5><?php echo htmlspecialchars($project['projectName'] ?? 'Unknown'); ?></h5>
                        <span class="badge bg-primary"><?php echo htmlspecialchars($project['projectCode'] ?? ''); ?></span>
                        <span class="badge bg-info"><?php echo htmlspecialchars($project['functionalArea'] ?? 'N/A'); ?></span>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Fiscal Year:</strong><br>
                            <?php echo htmlspecialchars($project['fiscalYear'] ?? 'N/A'); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Created:</strong><br>
                            <?php echo !empty($project['DateAdded']) ? date('M d, Y', strtotime($project['DateAdded'])) : 'N/A'; ?>
                        </div>
                    </div>

                    <!-- Utilization Chart -->
                    <div class="mb-4">
                        <h6>Utilization Progress</h6>
                        <div class="progress" style="height: 30px;">
                            <div class="progress-bar bg-<?php echo $utilization > 100 ? 'danger' : ($utilization > 80 ? 'warning' : 'success'); ?>"
                                 role="progressbar"
                                 style="width: <?php echo min($utilization, 100); ?>%">
                                <?php echo number_format($utilization, 1); ?>%
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <small class="text-muted">Allocated: <?php echo number_format($project['allocatedHours'] ?? 0, 0); ?> hrs</small>
                            <small class="text-muted">Actual: <?php echo number_format($project['actualHours'] ?? 0, 0); ?> hrs</small>
                        </div>
                    </div>

                    <!-- Resource Allocations -->
                    <?php if (!empty($resourceAllocations)): ?>
                        <div class="mb-3">
                            <h6>Resource Allocations</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Hours Logged</th>
                                            <th>% of Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($resourceAllocations as $allocation):
                                            $percent = $project['actualHours'] > 0
                                                ? ($allocation['hours'] / $project['actualHours']) * 100
                                                : 0;
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($allocation['name']); ?></td>
                                                <td><?php echo number_format($allocation['hours'], 1); ?> hrs</td>
                                                <td><?php echo number_format($percent, 1); ?>%</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Time Log Summary -->
            <div class="card mt-3">
                <div class="card-header">
                    <h4 class="card-title mb-0">Time Log Summary</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($timeLogs)): ?>
                        <p class="text-muted">No time logs recorded for this project yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="timeLogsTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Employee</th>
                                        <th>Hours</th>
                                        <th>Task</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($timeLogs as $log):
                                        $employee = Data::users(['ID' => $log['employeeID']], true, $DBConn);
                                        $task = null;
                                        if ($log['operationalTaskID']) {
                                            $task = OperationalTask::getInstance($log['operationalTaskID'], $DBConn);
                                        }
                                    ?>
                                        <tr>
                                            <td><?php echo date('M d, Y', strtotime($log['taskDate'])); ?></td>
                                            <td><?php echo $employee ? htmlspecialchars($employee->FirstName . ' ' . $employee->Surname) : 'Unknown'; ?></td>
                                            <td><?php echo number_format((float)($log['workHours'] ?? 0), 2); ?> hrs</td>
                                            <td>
                                                <?php if ($task): ?>
                                                    <small><?php echo htmlspecialchars($task['templateName'] ?? 'Task #' . $log['operationalTaskID']); ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?php echo htmlspecialchars(substr($log['taskNarrative'] ?? '', 0, 50)); ?></small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Project Summary</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Remaining Hours:</strong><br>
                        <h4 class="text-<?php echo ($project['allocatedHours'] - $project['actualHours']) < 0 ? 'danger' : 'success'; ?>">
                            <?php echo number_format(max(0, ($project['allocatedHours'] ?? 0) - ($project['actualHours'] ?? 0)), 0); ?>
                        </h4>
                    </div>
                    <div class="mb-3">
                        <strong>Over/Under:</strong><br>
                        <?php
                            $variance = ($project['actualHours'] ?? 0) - ($project['allocatedHours'] ?? 0);
                            $varianceClass = $variance > 0 ? 'danger' : ($variance < 0 ? 'success' : 'info');
                        ?>
                        <span class="text-<?php echo $varianceClass; ?>">
                            <?php echo $variance > 0 ? '+' : ''; ?><?php echo number_format($variance, 0); ?> hrs
                        </span>
                    </div>
                    <div class="mb-3">
                        <strong>Resources:</strong><br>
                        <?php echo count($resourceAllocations); ?> employee(s)
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Actions</h6>
                </div>
                <div class="card-body">
                    <a href="?s=user&ss=operational&p=projects" class="btn btn-secondary w-100">
                        <i class="ri-arrow-left-line me-1"></i>Back to Projects
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('timeLogsTable')) {
        $('#timeLogsTable').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[0, 'desc']],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ logs per page"
            }
        });
    }
});
</script>

