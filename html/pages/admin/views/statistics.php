<?php
/**
 * Statistics View
 */

// Get statistics data
$startDate = $_GET['startDate'] ?? date('Y-01-01');
$endDate = $_GET['endDate'] ?? date('Y-12-31');

try {
    $statistics = AccumulationPolicy::get_accumulation_statistics($entityID, $startDate, $endDate, $DBConn);
    $policies = AccumulationPolicy::get_policies($entityID, false, $DBConn);
} catch (Exception $e) {
    $statistics = array();
    $policies = array();
    $errors[] = $e->getMessage();
}

$totalPolicies = count($policies);
$activePolicies = count(array_filter($policies, function($p) { return $p['isActive'] === 'Y'; }));
$totalAccrued = array_sum(array_column($statistics, 'totalAccrued'));
$avgAccrual = count($statistics) > 0 ? array_sum(array_column($statistics, 'avgAccrual')) / count($statistics) : 0;
?>

<div class="row">
    <!-- Date Range Filter -->
    <div class="col-12 mb-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="action" value="statistics">
                    
                    <div class="col-md-4">
                        <label for="startDate" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="startDate" name="startDate" value="<?= $startDate ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label for="endDate" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="endDate" name="endDate" value="<?= $endDate ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-search-line me-1"></i>Update Statistics
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Overview Statistics -->
    <div class="col-12 mb-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title mb-1">Total Policies</h6>
                                <h3 class="mb-0"><?= $totalPolicies ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="ri-file-list-3-line display-6"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title mb-1">Active Policies</h6>
                                <h3 class="mb-0"><?= $activePolicies ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="ri-check-circle-line display-6"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title mb-1">Total Accrued</h6>
                                <h3 class="mb-0"><?= number_format($totalAccrued, 1) ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="ri-calendar-check-line display-6"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title mb-1">Avg Accrual</h6>
                                <h3 class="mb-0"><?= number_format($avgAccrual, 2) ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="ri-bar-chart-line display-6"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Policy Performance Chart -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="ri-bar-chart-box-line me-2"></i>
                    Policy Performance
                </h5>
            </div>
            <div class="card-body">
                <canvas id="policyChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Accrual Types Distribution -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="ri-pie-chart-line me-2"></i>
                    Accrual Types
                </h5>
            </div>
            <div class="card-body">
                <canvas id="accrualTypeChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Detailed Statistics Table -->
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="ri-table-line me-2"></i>
                    Detailed Statistics
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($statistics)): ?>
                    <div class="text-center py-4">
                        <i class="ri-bar-chart-line display-4 text-muted"></i>
                        <h6 class="mt-2 text-muted">No Data Available</h6>
                        <p class="text-muted">No accumulation data found for the selected period.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Policy Name</th>
                                    <th>Leave Type</th>
                                    <th>Employees</th>
                                    <th>Total Accrued</th>
                                    <th>Avg Accrual</th>
                                    <th>Total Carryover</th>
                                    <th>Avg Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($statistics as $stat): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($stat['policyName']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?= htmlspecialchars($stat['leaveTypeName']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= $stat['employeeCount'] ?></span>
                                    </td>
                                    <td>
                                        <strong><?= number_format($stat['totalAccrued'], 2) ?></strong>
                                    </td>
                                    <td>
                                        <?= number_format($stat['avgAccrual'], 2) ?>
                                    </td>
                                    <td>
                                        <?= number_format($stat['totalCarryover'], 2) ?>
                                    </td>
                                    <td>
                                        <span class="text-success"><?= number_format($stat['avgBalance'], 2) ?></span>
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
    
    <!-- Export Options -->
    <div class="col-12 mt-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">
                    <i class="ri-download-line me-2"></i>
                    Export Data
                </h6>
                
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-success" onclick="exportData('csv')">
                        <i class="ri-file-excel-line me-1"></i>Export CSV
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="exportData('pdf')">
                        <i class="ri-file-pdf-line me-1"></i>Export PDF
                    </button>
                    <button type="button" class="btn btn-outline-info" onclick="printStatistics()">
                        <i class="ri-printer-line me-1"></i>Print
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Policy Performance Chart
const policyCtx = document.getElementById('policyChart').getContext('2d');
const policyChart = new Chart(policyCtx, {
    type: 'bar',
    data: {
        labels: [
            <?php foreach ($statistics as $stat): ?>
            '<?= addslashes($stat['policyName']) ?>',
            <?php endforeach; ?>
        ],
        datasets: [{
            label: 'Total Accrued',
            data: [
                <?php foreach ($statistics as $stat): ?>
                <?= $stat['totalAccrued'] ?>,
                <?php endforeach; ?>
            ],
            backgroundColor: 'rgba(54, 162, 235, 0.6)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }, {
            label: 'Average Accrual',
            data: [
                <?php foreach ($statistics as $stat): ?>
                <?= $stat['avgAccrual'] ?>,
                <?php endforeach; ?>
            ],
            backgroundColor: 'rgba(255, 99, 132, 0.6)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
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

// Accrual Types Distribution Chart
const accrualTypeCtx = document.getElementById('accrualTypeChart').getContext('2d');
const accrualTypeData = {
    <?php
    $accrualTypes = array();
    foreach ($policies as $policy) {
        $type = $policy['accrualType'];
        $accrualTypes[$type] = ($accrualTypes[$type] ?? 0) + 1;
    }
    ?>
    labels: [<?= implode(',', array_map(function($type) { return "'$type'"; }, array_keys($accrualTypes))) ?>],
    datasets: [{
        data: [<?= implode(',', array_values($accrualTypes)) ?>],
        backgroundColor: [
            'rgba(255, 99, 132, 0.6)',
            'rgba(54, 162, 235, 0.6)',
            'rgba(255, 205, 86, 0.6)',
            'rgba(75, 192, 192, 0.6)',
            'rgba(153, 102, 255, 0.6)'
        ],
        borderColor: [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 205, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)'
        ],
        borderWidth: 1
    }]
};

const accrualTypeChart = new Chart(accrualTypeCtx, {
    type: 'doughnut',
    data: accrualTypeData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Export functions
function exportData(format) {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    if (format === 'csv') {
        window.location.href = `php/scripts/admin/export_statistics.php?format=csv&startDate=${startDate}&endDate=${endDate}`;
    } else if (format === 'pdf') {
        window.location.href = `php/scripts/admin/export_statistics.php?format=pdf&startDate=${startDate}&endDate=${endDate}`;
    }
}

function printStatistics() {
    window.print();
}

// Auto-refresh every 5 minutes
setInterval(function() {
    // Optionally refresh the page or update data via AJAX
}, 300000);
</script>
