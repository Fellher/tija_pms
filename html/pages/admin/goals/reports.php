<?php
/**
 * Goals Reports & Analytics
 * Global performance reporting and analytics
 *
 * @package    TIJA_PMS
 * @subpackage Goals
 * @version    1.0.0
 */

// Security check
if(!$isValidUser) {
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}

// Check admin permissions
if (!isset($isAdmin) || !$isAdmin) {
    if (!isset($isValidAdmin) || !$isValidAdmin) {
        Alert::error("Access denied. Administrator privileges required.", true);
        return;
    }
}

require_once 'php/classes/goalscoring.php';
require_once 'php/classes/goal.php';
require_once 'php/classes/data.php';

// Get filter parameters
$period = isset($_GET['period']) ? Utility::clean_string($_GET['period']) : 'quarterly';
$entityID = isset($_GET['entityID']) ? intval($_GET['entityID']) : null;
$rateType = isset($_GET['rateType']) ? Utility::clean_string($_GET['rateType']) : 'Budget';

// Calculate date range
$dateFrom = date('Y-m-d', strtotime('-3 months'));
$dateTo = date('Y-m-d');
if ($period === 'annual') {
    $dateFrom = date('Y-m-d', strtotime('-1 year'));
} elseif ($period === '5year') {
    $dateFrom = date('Y-m-d', strtotime('-5 years'));
}

// Get global statistics
$globalStats = $DBConn->retrieve_db_table_rows_custom(
    "SELECT
        COUNT(DISTINCT g.goalUUID) as totalGoals,
        COUNT(DISTINCT CASE WHEN g.status = 'Active' THEN g.goalUUID END) as activeGoals,
        COUNT(DISTINCT CASE WHEN g.status = 'Completed' THEN g.goalUUID END) as completedGoals,
        COUNT(DISTINCT CASE WHEN g.propriety = 'Critical' THEN g.goalUUID END) as criticalGoals,
        AVG(g.completionPercentage) as avgCompletion,
        AVG(gs.calculatedScore) as avgScore
     FROM tija_goals g
     LEFT JOIN tija_goal_scores gs ON g.goalUUID = gs.goalUUID
     WHERE g.sysEndTime IS NULL AND g.Lapsed = 'N'",
    array()
);

$stats = $globalStats && count($globalStats) > 0 ? $globalStats[0] : null;
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Goals Reports & Analytics</h4>
                    <p class="text-muted mb-0">Global performance reporting and analytics</p>
                </div>
                <div>
                    <a href="<?= "{$base}html/?s=admin&ss=goals&p=dashboard" ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="" class="row">
                        <input type="hidden" name="s" value="admin">
                        <input type="hidden" name="ss" value="goals">
                        <input type="hidden" name="p" value="reports">
                        <div class="col-md-3">
                            <label class="form-label">Time Period</label>
                            <select class="form-select" name="period" onchange="this.form.submit()">
                                <option value="quarterly" <?php echo $period === 'quarterly' ? 'selected' : ''; ?>>Quarterly</option>
                                <option value="annual" <?php echo $period === 'annual' ? 'selected' : ''; ?>>Annual</option>
                                <option value="5year" <?php echo $period === '5year' ? 'selected' : ''; ?>>5 Years</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Entity</label>
                            <select class="form-select" name="entityID" onchange="this.form.submit()">
                                <option value="">All Entities</option>
                                <?php
                                $entities = Data::entities(array('Lapsed' => 'N', 'Suspended' => 'N'), false, $DBConn);
                                if ($entities) {
                                    foreach ($entities as $entity) {
                                        $selected = ($entityID == $entity->entityID) ? 'selected' : '';
                                        echo "<option value=\"{$entity->entityID}\" {$selected}>" . htmlspecialchars($entity->entityName) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Currency Rate</label>
                            <select class="form-select" name="rateType" onchange="this.form.submit()">
                                <option value="Budget" <?php echo $rateType === 'Budget' ? 'selected' : ''; ?>>Budget Rate</option>
                                <option value="Spot" <?php echo $rateType === 'Spot' ? 'selected' : ''; ?>>Spot Rate</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="button" class="btn btn-outline-primary w-100" onclick="exportReport()">
                                    <i class="bi bi-download me-2"></i>Export CSV
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Total Goals</h6>
                    <h2 class="mb-0"><?php echo is_object($stats) ? ($stats->totalGoals ?? 0) : ($stats['totalGoals'] ?? 0); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Active Goals</h6>
                    <h2 class="mb-0 text-success"><?php echo is_object($stats) ? ($stats->activeGoals ?? 0) : ($stats['activeGoals'] ?? 0); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Avg. Completion</h6>
                    <h2 class="mb-0"><?php echo number_format(is_object($stats) ? ($stats->avgCompletion ?? 0) : ($stats['avgCompletion'] ?? 0), 1); ?>%</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Avg. Score</h6>
                    <h2 class="mb-0"><?php echo number_format(is_object($stats) ? ($stats->avgScore ?? 0) : ($stats['avgScore'] ?? 0), 1); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Performance Trend</h5>
                </div>
                <div class="card-body">
                    <div id="performanceChart" style="min-height: 300px;">
                        <!-- Chart will be rendered here using ApexCharts -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Entity Performance Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Entity Performance</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="entityPerformanceTable">
                            <thead>
                                <tr>
                                    <th>Entity</th>
                                    <th>Total Goals</th>
                                    <th>Active Goals</th>
                                    <th>Avg. Completion</th>
                                    <th>Entity Score</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $entities = Data::entities(array('Lapsed' => 'N', 'Suspended' => 'N'), false, $DBConn);
                                if ($entities) {
                                    foreach ($entities as $entity) {
                                        $entityScore = GoalScoring::calculateEntityScore($entity->entityID, null, $DBConn);
                                        $entityGoals = Goal::getGoalsByOwner($entity->entityID, 'Entity', array('status' => 'Active'), $DBConn);
                                        $totalGoals = count($entityGoals ? $entityGoals : array());
                                        $avgCompletion = 0;
                                        if ($entityGoals) {
                                            $totalCompletion = 0;
                                            foreach ($entityGoals as $goal) {
                                                $totalCompletion += (float)($goal->completionPercentage ?? 0);
                                            }
                                            $avgCompletion = $totalGoals > 0 ? $totalCompletion / $totalGoals : 0;
                                        }

                                        $statusClass = 'success';
                                        if ($entityScore < 70) $statusClass = 'danger';
                                        elseif ($entityScore < 85) $statusClass = 'warning';
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($entity->entityName); ?></strong></td>
                                            <td><?php echo $totalGoals; ?></td>
                                            <td><?php echo $totalGoals; ?></td>
                                            <td><?php echo number_format($avgCompletion, 1); ?>%</td>
                                            <td><strong><?php echo $entityScore ? number_format($entityScore, 1) : 'N/A'; ?></strong></td>
                                            <td>
                                                <span class="badge bg-<?php echo $statusClass; ?>">
                                                    <?php
                                                    if ($entityScore >= 85) echo 'On Track';
                                                    elseif ($entityScore >= 70) echo 'At Risk';
                                                    else echo 'Behind';
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= "{$base}html/?s=admin&ss=goals&p=reports_entity&entityID=" . $entity->entityID ?>"
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-graph-up"></i> Details
                                                </a>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Goal Type Breakdown -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Goals by Type</h5>
                </div>
                <div class="card-body">
                    <div id="goalTypeChart" style="min-height: 250px;">
                        <!-- Pie chart will be rendered here -->
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Goals by Status</h5>
                </div>
                <div class="card-body">
                    <div id="goalStatusChart" style="min-height: 250px;">
                        <!-- Pie chart will be rendered here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
// Performance Trend Chart
const performanceData = {
    series: [{
        name: 'Average Score',
        data: [85, 87, 86, 88, 89, 90, 88] // Would be fetched from API
    }],
    chart: {
        type: 'line',
        height: 300
    },
    xaxis: {
        categories: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6', 'Week 7']
    }
};

const performanceChart = new ApexCharts(document.querySelector("#performanceChart"), performanceData);
performanceChart.render();

// Goal Type Chart
const goalTypeData = {
    series: [<?php
        $typeStats = $DBConn->retrieve_db_table_rows_custom(
            "SELECT goalType, COUNT(*) as count FROM tija_goals WHERE sysEndTime IS NULL AND Lapsed = 'N' GROUP BY goalType",
            array()
        );
        $typeCounts = array();
        if ($typeStats) {
            foreach ($typeStats as $stat) {
                $typeCounts[] = $stat['count'];
            }
        }
        echo implode(',', $typeCounts ?: array(0, 0, 0));
    ?>],
    chart: {
        type: 'pie',
        height: 250
    },
    labels: ['Strategic', 'OKR', 'KPI']
};

const goalTypeChart = new ApexCharts(document.querySelector("#goalTypeChart"), goalTypeData);
goalTypeChart.render();

// Goal Status Chart
const goalStatusData = {
    series: [<?php
        $statusStats = $DBConn->retrieve_db_table_rows_custom(
            "SELECT status, COUNT(*) as count FROM tija_goals WHERE sysEndTime IS NULL AND Lapsed = 'N' GROUP BY status",
            array()
        );
        $statusCounts = array();
        if ($statusStats) {
            foreach ($statusStats as $stat) {
                $statusCounts[] = $stat['count'];
            }
        }
        echo implode(',', $statusCounts ?: array(0, 0, 0));
    ?>],
    chart: {
        type: 'pie',
        height: 250
    },
    labels: ['Active', 'Completed', 'Draft']
};

const goalStatusChart = new ApexCharts(document.querySelector("#goalStatusChart"), goalStatusData);
goalStatusChart.render();

function exportReport() {
    // Export functionality
    alert('Export functionality - would generate CSV report');
}
</script>

