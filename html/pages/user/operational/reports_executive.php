<?php
/**
 * Executive Dashboard - User
 *
 * Executive-level operational work insights
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
$dateFrom = $_GET['dateFrom'] ?? date('Y-01-01');
$dateTo = $_GET['dateTo'] ?? date('Y-12-31');

// Get executive metrics
// Run the business (BAU hours) - sum all operational time logs
$runHours = 0;
$timeLogs = $DBConn->retrieve_db_table_rows('tija_tasks_time_logs',
    ['workHours', 'taskDuration'],
    ['taskType' => 'operational', 'Suspended' => 'N'],
    false,
    "AND taskDate >= '{$dateFrom}' AND taskDate <= '{$dateTo}'");
if ($timeLogs) {
    foreach ($timeLogs as $log) {
        $hours = !empty($log['workHours']) ? (float)$log['workHours'] : 0;
        if (!$hours && !empty($log['taskDuration'])) {
            // Parse duration string
            $parts = explode(':', $log['taskDuration']);
            $hours = (float)$parts[0] + ((float)($parts[1] ?? 0) / 60);
        }
        $runHours += $hours;
    }
}

// Grow the business (growth projects - would need to identify from project types)
$growHours = 0; // TODO: Sum hours from growth-type projects

// Transform the business (transformation projects - would need to identify from project types)
$transformHours = 0; // TODO: Sum hours from transformation-type projects

$pageTitle = "Executive Dashboard";
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
                        <li class="breadcrumb-item active">Executive Dashboard</li>
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

    <!-- Investment Mix -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2 text-primary">Run the Business</p>
                            <h4 class="mb-2 text-primary"><?php echo number_format($runHours); ?> hrs</h4>
                            <small class="text-muted">Operational/BAU work</small>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-primary-transparent rounded-3">
                                <i class="ri-repeat-line font-size-18 text-primary"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2 text-success">Grow the Business</p>
                            <h4 class="mb-2 text-success"><?php echo number_format($growHours); ?> hrs</h4>
                            <small class="text-muted">Growth projects</small>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-success-transparent rounded-3">
                                <i class="ri-line-chart-line font-size-18 text-success"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2 text-info">Transform the Business</p>
                            <h4 class="mb-2 text-info"><?php echo number_format($transformHours); ?> hrs</h4>
                            <small class="text-muted">Transformation projects</small>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-info-transparent rounded-3">
                                <i class="ri-lightbulb-line font-size-18 text-info"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Investment Mix Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Investment Mix</h4>
                </div>
                <div class="card-body">
                    <canvas id="investmentMixChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Capacity Waterline -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Capacity Waterline</h4>
                </div>
                <div class="card-body">
                    <canvas id="capacityWaterlineChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- FTE by Functional Area -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">FTE by Functional Area</h4>
                </div>
                <div class="card-body">
                    <canvas id="fteChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Operational Efficiency Trends</h4>
                </div>
                <div class="card-body">
                    <canvas id="efficiencyChart" height="200"></canvas>
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

