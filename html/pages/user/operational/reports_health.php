<?php
/**
 * Operational Health Report - User
 *
 * View operational health metrics and KPIs
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

global $DBConn, $userID;

// Get date range
$dateFrom = $_GET['dateFrom'] ?? date('Y-m-01');
$dateTo = $_GET['dateTo'] ?? date('Y-m-t');

// Get metrics
$completedTasks = $DBConn->retrieve_db_table_rows('tija_operational_tasks',
    ['operationalTaskID', 'completedDate', 'startDate', 'dueDate'],
    ['status' => 'completed', 'Suspended' => 'N'],
    false,
    "AND completedDate >= '{$dateFrom}' AND completedDate <= '{$dateTo}'");

$taskVolume = is_array($completedTasks) ? count($completedTasks) : 0;

// Calculate average cycle time
$avgCycleTime = 0;
if ($completedTasks) {
    $totalCycleTime = 0;
    $count = 0;
    foreach ($completedTasks as $task) {
        if (!empty($task['startDate']) && !empty($task['completedDate'])) {
            $start = strtotime($task['startDate']);
            $end = strtotime($task['completedDate']);
            $hours = ($end - $start) / 3600;
            $totalCycleTime += $hours;
            $count++;
        }
    }
    $avgCycleTime = $count > 0 ? $totalCycleTime / $count : 0;
}

// Get backlog (overdue tasks)
$overdueTasks = OperationalTask::getOverdueTasks([], $DBConn);
$backlog = is_array($overdueTasks) ? count($overdueTasks) : 0;

// Calculate SLA compliance
$allTasks = $DBConn->retrieve_db_table_rows('tija_operational_tasks',
    ['operationalTaskID', 'completedDate', 'dueDate', 'status'],
    ['Suspended' => 'N'],
    false,
    "AND completedDate >= '{$dateFrom}' AND completedDate <= '{$dateTo}'");

$onTimeCount = 0;
$totalCompleted = 0;
if ($allTasks) {
    foreach ($allTasks as $task) {
        if (($task['status'] ?? '') === 'completed' && !empty($task['completedDate']) && !empty($task['dueDate'])) {
            $totalCompleted++;
            if (strtotime($task['completedDate']) <= strtotime($task['dueDate'])) {
                $onTimeCount++;
            }
        }
    }
}
$slaCompliance = $totalCompleted > 0 ? ($onTimeCount / $totalCompleted) * 100 : 0;

// Error rate (simplified - would need task reopening tracking)
$errorRate = 0; // Would calculate from reopened tasks

$pageTitle = "Operational Health Report";
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
                        <li class="breadcrumb-item active">Health Report</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <input type="date" class="form-control" id="dateFrom" value="<?php echo htmlspecialchars($dateFrom); ?>" style="width: 200px;">
                        <input type="date" class="form-control" id="dateTo" value="<?php echo htmlspecialchars($dateTo); ?>" style="width: 200px;">
                        <button class="btn btn-primary" onclick="applyDateRange()">
                            <i class="ri-search-line me-1"></i>Apply
                        </button>
                        <button class="btn btn-success" onclick="exportReport()">
                            <i class="ri-download-line me-1"></i>Export
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Health Metrics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Task Volume</p>
                            <h4 class="mb-2"><?php echo number_format($taskVolume); ?></h4>
                            <small class="text-muted">Tasks completed this period</small>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-primary rounded-3">
                                <i class="ri-task-line font-size-18"></i>
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
                            <p class="text-truncate font-size-14 mb-2">Avg Cycle Time</p>
                            <h4 class="mb-2 text-info"><?php echo number_format($avgCycleTime, 1); ?> hrs</h4>
                            <small class="text-muted">Average time to complete</small>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-info rounded-3">
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
                            <p class="text-truncate font-size-14 mb-2">Backlog</p>
                            <h4 class="mb-2 text-warning"><?php echo number_format($backlog); ?></h4>
                            <small class="text-muted">Overdue tasks</small>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-warning rounded-3">
                                <i class="ri-stack-line font-size-18"></i>
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
                            <p class="text-truncate font-size-14 mb-2">SLA Compliance</p>
                            <h4 class="mb-2 text-success"><?php echo number_format($slaCompliance, 1); ?>%</h4>
                            <small class="text-muted">Completed on time</small>
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

    <!-- Charts -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Task Volume Trend</h4>
                </div>
                <div class="card-body">
                    <canvas id="volumeChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Cycle Time Trend</h4>
                </div>
                <div class="card-body">
                    <canvas id="cycleTimeChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quality Metrics -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Quality Metrics</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-4">
                                <h3 class="text-danger"><?php echo number_format($errorRate, 1); ?>%</h3>
                                <p class="text-muted mb-0">Error/Reopened Rate</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-4">
                                <h3 class="text-success"><?php echo number_format(100 - $errorRate, 1); ?>%</h3>
                                <p class="text-muted mb-0">First-Time Right</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-4">
                                <h3 class="text-info"><?php echo number_format($slaCompliance, 1); ?>%</h3>
                                <p class="text-muted mb-0">SLA Compliance</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function applyDateRange() {
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    const url = new URL(window.location);
    url.searchParams.set('dateFrom', dateFrom);
    url.searchParams.set('dateTo', dateTo);
    window.location.href = url.toString();
}

function exportReport() {
    // Build export URL with current filters
    const url = new URL(window.location);
    url.searchParams.set('export', 'pdf');
    window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    // TODO: Initialize charts with Chart.js
});
</script>

