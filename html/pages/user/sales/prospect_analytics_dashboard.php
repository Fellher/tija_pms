<?php
if(!$isValidUser) {
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}

// Get user context
$orgDataID = isset($_GET['orgDataID']) ? (int)$_GET['orgDataID'] : $employeeDetails->orgDataID;
$entityID = isset($_GET['entityID']) ? (int)$_GET['entityID'] : $employeeDetails->entityID;

// Date range for analytics
$dateRange = array(
    'start' => isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-01'),
    'end' => isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d')
);

// Get analytics data
$sourceAnalytics = Sales::prospect_analytics_by_source($orgDataID, $entityID, $dateRange, $DBConn);

// Conversion funnel - filter by organization and entity
// Using new dynamic whereArr pattern (like employees method)
$funnelFilters = array(
    'orgDataID' => $orgDataID,
    'entityID' => $entityID
    // Note: Suspended is not filtered, so converted prospects are included
);
$conversionFunnel = Sales::prospect_conversion_funnel($funnelFilters, $dateRange, $DBConn);

// Get team performance if teams exist
$teams = Sales::prospect_teams(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID, 'isActive'=>'Y'), false, $DBConn);
$teamPerformance = array();
if ($teams && count($teams) > 0) {
    foreach ($teams as $team) {
        $teamPerformance[] = Sales::team_performance_metrics($team->teamID, $dateRange, $DBConn);
    }
}

// Calculate KPIs
$totalProspects = 0;
$qualifiedLeads = 0;
$avgScore = 0;
$pipelineValue = 0;

if (is_array($conversionFunnel) && count($conversionFunnel) > 0) {
    foreach ($conversionFunnel as $stage) {

        // var_dump($stage);
        $status = $stage->stage ?? '';
        $count = (int)($stage->count ?? 0);
        $score = (float)($stage->avgScore ?? 0);
        $value = (float)($stage->totalValue ?? 0);

        // Add to total
        $totalProspects += $count;

        if ($status == 'qualified') {
            $qualifiedLeads = $count;
        }
        $avgScore += $score * $count;
        $pipelineValue += $value;
    }
}
$avgScore = $totalProspects > 0 ? round($avgScore / $totalProspects, 1) : 0;

?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-medium fs-24 mb-0">Prospect Analytics Dashboard</h1>
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= "{$base}html/?s=user&ss=sales&p=home" ?>">Sales</a></li>
                <li class="breadcrumb-item"><a href="<?= "{$base}html/?s=user&ss=sales&p=prospects" ?>">Prospects</a></li>
                <li class="breadcrumb-item active" aria-current="page">Analytics</li>
            </ol>
        </nav>
    </div>
    <div class="ms-md-1 ms-0 mt-md-0 mt-2">
        <button type="button" class="btn btn-outline-primary btn-wave me-2" data-bs-toggle="modal" data-bs-target="#prospectHelpModal" title="Help & Documentation">
            <i class="ri-question-line me-1"></i> Help
        </button>
        <form method="GET" class="d-inline-flex gap-2">
            <input type="hidden" name="s" value="<?= $s ?>">
            <input type="hidden" name="ss" value="<?= $ss ?>">
            <input type="hidden" name="p" value="<?= $p ?>">
            <input type="date" class="form-control form-control-sm" name="startDate" value="<?= $dateRange['start'] ?>">
            <input type="date" class="form-control form-control-sm" name="endDate" value="<?= $dateRange['end'] ?>">
            <button type="submit" class="btn btn-sm btn-primary">Apply</button>
        </form>
    </div>
</div>

<!-- KPI Cards -->
<div class="row">
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top">
                    <div class="me-3">
                        <span class="avatar avatar-md avatar-rounded bg-primary">
                            <i class="ri-user-line fs-18"></i>
                        </span>
                    </div>
                    <div class="flex-fill">
                        <span class="d-block text-muted mb-1">Total Prospects</span>
                        <h4 class="fw-semibold mb-0"><?= number_format($totalProspects) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top">
                    <div class="me-3">
                        <span class="avatar avatar-md avatar-rounded bg-success">
                            <i class="ri-checkbox-circle-line fs-18"></i>
                        </span>
                    </div>
                    <div class="flex-fill">
                        <span class="d-block text-muted mb-1">Qualified Leads</span>
                        <h4 class="fw-semibold mb-0"><?= number_format($qualifiedLeads) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top">
                    <div class="me-3">
                        <span class="avatar avatar-md avatar-rounded bg-warning">
                            <i class="ri-line-chart-line fs-18"></i>
                        </span>
                    </div>
                    <div class="flex-fill">
                        <span class="d-block text-muted mb-1">Avg Lead Score</span>
                        <h4 class="fw-semibold mb-0"><?= $avgScore ?>/100</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top">
                    <div class="me-3">
                        <span class="avatar avatar-md avatar-rounded bg-info">
                            <i class="ri-money-dollar-circle-line fs-18"></i>
                        </span>
                    </div>
                    <div class="flex-fill">
                        <span class="d-block text-muted mb-1">Pipeline Value</span>
                        <h4 class="fw-semibold mb-0"><?= number_format($pipelineValue, 2) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 1 -->
<div class="row">
    <!-- Lead Source Distribution -->
    <div class="col-xl-6">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Lead Source Distribution</div>
            </div>
            <div class="card-body">
                <canvas id="leadSourceChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Conversion Funnel -->
    <div class="col-xl-6">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Conversion Funnel</div>
            </div>
            <div class="card-body">
                <canvas id="conversionFunnelChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 2 -->
<div class="row">
    <!-- Lead Score Distribution -->
    <div class="col-xl-6">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Lead Score Distribution</div>
            </div>
            <div class="card-body">
                <canvas id="leadScoreChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Team Performance -->
    <?php if (count($teamPerformance) > 0): ?>
    <div class="col-xl-6">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Team Performance</div>
            </div>
            <div class="card-body">
                <canvas id="teamPerformanceChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Top Performing Sources Table -->
<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Top Performing Lead Sources</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Lead Source</th>
                                <th>Prospects</th>
                                <th>Total Value</th>
                                <th>Avg Value</th>
                                <th>Conversion Rate</th>
                                <th>Avg Lead Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($sourceAnalytics && count($sourceAnalytics) > 0): ?>
                                <?php foreach ($sourceAnalytics as $source): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($source->leadSourceName) ?></td>
                                        <td><?= number_format($source->prospectCount) ?></td>
                                        <td><?= number_format($source->totalValue, 2) ?></td>
                                        <td><?= number_format($source->avgValue, 2) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $source->conversionRate >= 50 ? 'success' : ($source->conversionRate >= 25 ? 'warning' : 'danger') ?>-transparent">
                                                <?= number_format($source->conversionRate, 1) ?>%
                                            </span>
                                        </td>
                                        <td><?= number_format($source->avgLeadScore, 1) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No data available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lead Source Pie Chart
    const sourceData = <?= json_encode($sourceAnalytics) ?>;
    if (sourceData && sourceData.length > 0) {
        new Chart(document.getElementById('leadSourceChart'), {
            type: 'pie',
            data: {
                labels: sourceData.map(s => s.leadSourceName),
                datasets: [{
                    data: sourceData.map(s => s.prospectCount),
                    backgroundColor: [
                        '#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545',
                        '#fd7e14', '#ffc107', '#198754', '#20c997', '#0dcaf0'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    }

    // Conversion Funnel Bar Chart
    const funnelData = <?= json_encode($conversionFunnel) ?>;
    if (funnelData && Array.isArray(funnelData) && funnelData.length > 0) {
        new Chart(document.getElementById('conversionFunnelChart'), {
            type: 'bar',
            data: {
                labels: funnelData.map(f => {
                    const status = f.stage || f.leadQualificationStatus || 'Unknown';
                    return status.charAt(0).toUpperCase() + status.slice(1);
                }),
                datasets: [{
                    label: 'Prospects',
                    data: funnelData.map(f => f.count || f.prospectCount || 0),
                    backgroundColor: '#0d6efd'
                }, {
                    label: 'Total Value',
                    data: funnelData.map(f => f.totalValue || 0),
                    backgroundColor: '#198754'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Lead Score Distribution Histogram
    new Chart(document.getElementById('leadScoreChart'), {
        type: 'bar',
        data: {
            labels: ['0-20', '21-40', '41-60', '61-80', '81-100'],
            datasets: [{
                label: 'Number of Prospects',
                data: [0, 0, 0, 0, 0], // Would need to calculate from data
                backgroundColor: ['#dc3545', '#fd7e14', '#ffc107', '#20c997', '#198754']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Team Performance Chart
    <?php if (count($teamPerformance) > 0): ?>
    const teamData = <?= json_encode($teamPerformance) ?>;
    if (teamData && teamData.length > 0) {
        new Chart(document.getElementById('teamPerformanceChart'), {
            type: 'bar',
            data: {
                labels: teamData.map(t => t.teamName),
                datasets: [{
                    label: 'Total Prospects',
                    data: teamData.map(t => t.totalProspects),
                    backgroundColor: '#0d6efd'
                }, {
                    label: 'Conversion Rate (%)',
                    data: teamData.map(t => t.conversionRate),
                    backgroundColor: '#198754'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    <?php endif; ?>
});
</script>

<?php
// Include help modal
include "includes/scripts/sales/modals/prospect_help.php";
?>

<script>
// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize Flatpickr for date range inputs
    if (typeof flatpickr !== 'undefined') {
        const startDateInput = document.querySelector('input[name="startDate"]');
        const endDateInput = document.querySelector('input[name="endDate"]');

        if (startDateInput) {
            flatpickr(startDateInput, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'F j, Y',
                allowInput: true,
                maxDate: endDateInput ? endDateInput.value : 'today'
            });
        }

        if (endDateInput) {
            flatpickr(endDateInput, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'F j, Y',
                allowInput: true,
                maxDate: 'today'
            });
        }
    }
});
</script>
