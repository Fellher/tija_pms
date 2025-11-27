
<?php
/**
 * Work Hour Analysis Report - Enhanced Version with Real Data
 * Replaces randomized data with actual database queries and comprehensive analysis
 */

$entityUsers = Employee::employees(array('entityID'=>$entityID, 'Valid'=>'y'), false, $DBConn);

// Get current month and year
$currentMonth = date('m');
$currentYear = date('Y');
$yearStart = $currentYear . '-01-01';
$monthStart = $currentYear . '-' . $currentMonth . '-01';
$monthEnd = date('Y-m-t');

// Initialize analysis data
$workHourAnalysis = [
    'overall_metrics' => [
        'total_employees' => 0,
        'total_current_hours' => 0,
        'total_ytd_hours' => 0,
        'total_current_billable_hours' => 0,
        'total_ytd_billable_hours' => 0,
        'total_current_value' => 0,
        'total_ytd_value' => 0,
        'total_current_cost' => 0,
        'total_ytd_cost' => 0,
        'total_current_profit' => 0,
        'total_ytd_profit' => 0,
        'avg_billable_percentage_current' => 0,
        'avg_billable_percentage_ytd' => 0,
        'avg_profit_margin_current' => 0,
        'avg_profit_margin_ytd' => 0
    ],
    'employee_data' => [],
    'performance_tiers' => [
        'high_performers' => [],
        'average_performers' => [],
        'low_performers' => []
    ]
];

// Process each employee
if($entityUsers && is_array($entityUsers)) {
    foreach($entityUsers as $emp) {
        $workHourAnalysis['overall_metrics']['total_employees']++;
        
        // Get employee department
        $department = Data::unit_user_assignments_full(array('userID'=>$emp->ID, 'unitTypeID'=>'1'), true, $DBConn);
        $deptName = $emp->businessUnitName ? $emp->businessUnitName : ($department && $department->unitName ? $department->unitName : "Unknown");
        
        // Initialize employee metrics
        $employeeMetrics = [
            'employee' => $emp,
            'department' => $deptName,
            'current_month' => [
                'hours' => 0, 'billable_hours' => 0, 'value' => 0, 'cost' => 0, 'profit' => 0,
                'billable_percentage' => 0, 'profit_margin' => 0
            ],
            'year_to_date' => [
                'hours' => 0, 'billable_hours' => 0, 'value' => 0, 'cost' => 0, 'profit' => 0,
                'billable_percentage' => 0, 'profit_margin' => 0
            ],
            'performance_score' => 0,
            'efficiency_rating' => 'Average'
        ];
        
        // Calculate employee cost per hour
        $monthlyHours = Workutils::get_total_hours_in_month($currentMonth, $currentYear, $DBConn);
        $costPerHour = $emp->basicSalary > 0 ? $emp->basicSalary / $monthlyHours : 0;
        
        // Get current month time logs
        $currentMonthLogs = TimeAttendance::project_tasks_time_logs_between_dates(
            array('employeeID' => $emp->ID, 'Suspended' => 'N'), 
            $monthStart, $monthEnd, false, $DBConn
        );
        
        // Get year-to-date time logs
        $ytdLogs = TimeAttendance::project_tasks_time_logs_between_dates(
            array('employeeID' => $emp->ID, 'Suspended' => 'N'), 
            $yearStart, date('Y-m-d'), false, $DBConn
        );
        
        // Process current month data
        if($currentMonthLogs && is_array($currentMonthLogs)) {
            foreach($currentMonthLogs as $log) {
                $hours = Utility::time_to_seconds($log->taskDuration) / 3600;
                $employeeMetrics['current_month']['hours'] += $hours;
                
                if($log->billable == 'Y' || $log->billable == 'y') {
                    $employeeMetrics['current_month']['billable_hours'] += $hours;
                    $billingRate = $log->billableRateValue ? $log->billableRateValue : 100;
                    $employeeMetrics['current_month']['value'] += $hours * $billingRate;
                }
                $employeeMetrics['current_month']['cost'] += $hours * $costPerHour;
            }
        }
        
        // Process year-to-date data
        if($ytdLogs && is_array($ytdLogs)) {
            foreach($ytdLogs as $log) {
                $hours = Utility::time_to_seconds($log->taskDuration) / 3600;
                $employeeMetrics['year_to_date']['hours'] += $hours;
                
                if($log->billable == 'Y' || $log->billable == 'y') {
                    $employeeMetrics['year_to_date']['billable_hours'] += $hours;
                    $billingRate = $log->billableRateValue ? $log->billableRateValue : 100;
                    $employeeMetrics['year_to_date']['value'] += $hours * $billingRate;
                }
                $employeeMetrics['year_to_date']['cost'] += $hours * $costPerHour;
            }
        }
        
        // Calculate derived metrics
        $employeeMetrics['current_month']['profit'] = $employeeMetrics['current_month']['value'] - $employeeMetrics['current_month']['cost'];
        $employeeMetrics['year_to_date']['profit'] = $employeeMetrics['year_to_date']['value'] - $employeeMetrics['year_to_date']['cost'];
        
        $employeeMetrics['current_month']['billable_percentage'] = $employeeMetrics['current_month']['hours'] > 0 ? 
            ($employeeMetrics['current_month']['billable_hours'] / $employeeMetrics['current_month']['hours']) * 100 : 0;
        
        $employeeMetrics['year_to_date']['billable_percentage'] = $employeeMetrics['year_to_date']['hours'] > 0 ? 
            ($employeeMetrics['year_to_date']['billable_hours'] / $employeeMetrics['year_to_date']['hours']) * 100 : 0;
        
        $employeeMetrics['current_month']['profit_margin'] = $employeeMetrics['current_month']['value'] > 0 ? 
            ($employeeMetrics['current_month']['profit'] / $employeeMetrics['current_month']['value']) * 100 : 0;
        
        $employeeMetrics['year_to_date']['profit_margin'] = $employeeMetrics['year_to_date']['value'] > 0 ? 
            ($employeeMetrics['year_to_date']['profit'] / $employeeMetrics['year_to_date']['value']) * 100 : 0;
        
        // Calculate performance score
        $employeeMetrics['performance_score'] = ($employeeMetrics['current_month']['billable_percentage'] * 0.6) + 
                                               ($employeeMetrics['current_month']['profit_margin'] * 0.4);
        
        // Categorize performance
        if($employeeMetrics['performance_score'] >= 80) {
            $employeeMetrics['efficiency_rating'] = 'High';
            $workHourAnalysis['performance_tiers']['high_performers'][] = $employeeMetrics;
        } elseif($employeeMetrics['performance_score'] >= 60) {
            $employeeMetrics['efficiency_rating'] = 'Average';
            $workHourAnalysis['performance_tiers']['average_performers'][] = $employeeMetrics;
        } else {
            $employeeMetrics['efficiency_rating'] = 'Low';
            $workHourAnalysis['performance_tiers']['low_performers'][] = $employeeMetrics;
        }
        
        // Update overall metrics
        $workHourAnalysis['overall_metrics']['total_current_hours'] += $employeeMetrics['current_month']['hours'];
        $workHourAnalysis['overall_metrics']['total_ytd_hours'] += $employeeMetrics['year_to_date']['hours'];
        $workHourAnalysis['overall_metrics']['total_current_billable_hours'] += $employeeMetrics['current_month']['billable_hours'];
        $workHourAnalysis['overall_metrics']['total_ytd_billable_hours'] += $employeeMetrics['year_to_date']['billable_hours'];
        $workHourAnalysis['overall_metrics']['total_current_value'] += $employeeMetrics['current_month']['value'];
        $workHourAnalysis['overall_metrics']['total_ytd_value'] += $employeeMetrics['year_to_date']['value'];
        $workHourAnalysis['overall_metrics']['total_current_cost'] += $employeeMetrics['current_month']['cost'];
        $workHourAnalysis['overall_metrics']['total_ytd_cost'] += $employeeMetrics['year_to_date']['cost'];
        $workHourAnalysis['overall_metrics']['total_current_profit'] += $employeeMetrics['current_month']['profit'];
        $workHourAnalysis['overall_metrics']['total_ytd_profit'] += $employeeMetrics['year_to_date']['profit'];
        
        $workHourAnalysis['employee_data'][] = $employeeMetrics;
    }
}

// Calculate overall averages
$workHourAnalysis['overall_metrics']['avg_billable_percentage_current'] = $workHourAnalysis['overall_metrics']['total_current_hours'] > 0 ? 
    ($workHourAnalysis['overall_metrics']['total_current_billable_hours'] / $workHourAnalysis['overall_metrics']['total_current_hours']) * 100 : 0;

$workHourAnalysis['overall_metrics']['avg_billable_percentage_ytd'] = $workHourAnalysis['overall_metrics']['total_ytd_hours'] > 0 ? 
    ($workHourAnalysis['overall_metrics']['total_ytd_billable_hours'] / $workHourAnalysis['overall_metrics']['total_ytd_hours']) * 100 : 0;

$workHourAnalysis['overall_metrics']['avg_profit_margin_current'] = $workHourAnalysis['overall_metrics']['total_current_value'] > 0 ? 
    ($workHourAnalysis['overall_metrics']['total_current_profit'] / $workHourAnalysis['overall_metrics']['total_current_value']) * 100 : 0;

$workHourAnalysis['overall_metrics']['avg_profit_margin_ytd'] = $workHourAnalysis['overall_metrics']['total_ytd_value'] > 0 ? 
    ($workHourAnalysis['overall_metrics']['total_ytd_profit'] / $workHourAnalysis['overall_metrics']['total_ytd_value']) * 100 : 0;
?>

<div class="container-fluid">
    <!-- Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-white shadow">
                <div class="card-header bg-primary-subtle">
                    <h6 class="mb-0 text-primary"><i class="ri-team-line me-2"></i>Total Employees</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-primary-subtle rounded">
                                <span class="avatar-title bg-primary-subtle text-primary rounded">
                                    <i class="ri-team-line fs-20"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0"><?= $workHourAnalysis['overall_metrics']['total_employees'] ?></h4>
                            <small class="text-muted">Active employees</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-white shadow">
                <div class="card-header bg-success-subtle">
                    <h6 class="mb-0 text-success"><i class="ri-time-line me-2"></i>Current Month Hours</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-success-subtle rounded">
                                <span class="avatar-title bg-success-subtle text-success rounded">
                                    <i class="ri-time-line fs-20"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0"><?= number_format($workHourAnalysis['overall_metrics']['total_current_hours'], 1) ?></h4>
                            <small class="text-muted">Hours worked</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-white shadow">
                <div class="card-header bg-info-subtle">
                    <h6 class="mb-0 text-info"><i class="ri-percent-line me-2"></i>Billable Rate</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-info-subtle rounded">
                                <span class="avatar-title bg-info-subtle text-info rounded">
                                    <i class="ri-percent-line fs-20"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0"><?= number_format($workHourAnalysis['overall_metrics']['avg_billable_percentage_current'], 1) ?>%</h4>
                            <small class="text-muted">Current month</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-white shadow">
                <div class="card-header bg-warning-subtle">
                    <h6 class="mb-0 text-warning"><i class="ri-money-dollar-circle-line me-2"></i>Profit Margin</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-warning-subtle rounded">
                                <span class="avatar-title bg-warning-subtle text-warning rounded">
                                    <i class="ri-money-dollar-circle-line fs-20"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0"><?= number_format($workHourAnalysis['overall_metrics']['avg_profit_margin_current'], 1) ?>%</h4>
                            <small class="text-muted">Current month</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Tiers -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-trophy-line me-2"></i>High Performers</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar-sm bg-success-subtle rounded mx-auto mb-3">
                            <span class="avatar-title bg-success-subtle text-success rounded">
                                <i class="ri-trophy-line fs-20"></i>
                            </span>
                        </div>
                        <h4 class="text-success"><?= count($workHourAnalysis['performance_tiers']['high_performers']) ?></h4>
                        <p class="text-muted mb-0">Score ≥ 80</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>Employee</th><th>Score</th><th>Billable %</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($workHourAnalysis['performance_tiers']['high_performers'], 0, 5) as $emp): ?>
                                <tr>
                                    <td><small><?= htmlspecialchars($emp['employee']->employeeName) ?></small></td>
                                    <td><span class="badge bg-success-subtle text-success"><?= number_format($emp['performance_score'], 0) ?></span></td>
                                    <td><small><?= number_format($emp['current_month']['billable_percentage'], 1) ?>%</small></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-user-line me-2"></i>Average Performers</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar-sm bg-warning-subtle rounded mx-auto mb-3">
                            <span class="avatar-title bg-warning-subtle text-warning rounded">
                                <i class="ri-user-line fs-20"></i>
                            </span>
                        </div>
                        <h4 class="text-warning"><?= count($workHourAnalysis['performance_tiers']['average_performers']) ?></h4>
                        <p class="text-muted mb-0">Score 60-79</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>Employee</th><th>Score</th><th>Billable %</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($workHourAnalysis['performance_tiers']['average_performers'], 0, 5) as $emp): ?>
                                <tr>
                                    <td><small><?= htmlspecialchars($emp['employee']->employeeName) ?></small></td>
                                    <td><span class="badge bg-warning-subtle text-warning"><?= number_format($emp['performance_score'], 0) ?></span></td>
                                    <td><small><?= number_format($emp['current_month']['billable_percentage'], 1) ?>%</small></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-alert-line me-2"></i>Low Performers</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar-sm bg-danger-subtle rounded mx-auto mb-3">
                            <span class="avatar-title bg-danger-subtle text-danger rounded">
                                <i class="ri-alert-line fs-20"></i>
                            </span>
                        </div>
                        <h4 class="text-danger"><?= count($workHourAnalysis['performance_tiers']['low_performers']) ?></h4>
                        <p class="text-muted mb-0">Score < 60</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>Employee</th><th>Score</th><th>Billable %</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($workHourAnalysis['performance_tiers']['low_performers'], 0, 5) as $emp): ?>
                                <tr>
                                    <td><small><?= htmlspecialchars($emp['employee']->employeeName) ?></small></td>
                                    <td><span class="badge bg-danger-subtle text-danger"><?= number_format($emp['performance_score'], 0) ?></span></td>
                                    <td><small><?= number_format($emp['current_month']['billable_percentage'], 1) ?>%</small></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Employee Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-table-line me-2"></i>Detailed Work Hour Analysis Report</h5>
                    <div class="card-options">
                        <button class="btn btn-sm btn-outline-primary" onclick="exportTable()">
                            <i class="ri-download-line me-1"></i>Export
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="workHourTable">
                            <thead>
                                <tr>
                                    <th>&nbsp;</th>
                                    <th scope="col" colspan="5" class="ps-2 text-center">Current Month</th>
                                    <th scope="col" colspan="5" class="text-center">Year to Date</th>
                                </tr>
                                <tr>
                                    <th>Employee</th>
                                    <th>Hours</th><th>Billable %</th><th>Value</th><th>Cost</th><th>Profit</th>
                                    <th>Hours</th><th>Billable %</th><th>Value</th><th>Cost</th><th>Profit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($workHourAnalysis['employee_data'] as $emp): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-light rounded me-2">
                                                <span class="avatar-title bg-light text-dark rounded">
                                                    <i class="ri-user-line"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($emp['employee']->employeeName) ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars($emp['department']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= number_format($emp['current_month']['hours'], 1) ?> hrs</td>
                                    <td class="text-center">
                                        <?php 
                                        $billableClass = $emp['current_month']['billable_percentage'] >= 80 ? 'bg-success-subtle text-success' : 
                                                       ($emp['current_month']['billable_percentage'] >= 60 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger');
                                        ?>
                                        <span class="badge <?= $billableClass ?>"><?= number_format($emp['current_month']['billable_percentage'], 1) ?>%</span>
                                    </td>
                                    <td class="text-end">KES <?= number_format($emp['current_month']['value'], 2) ?></td>
                                    <td class="text-end">KES <?= number_format($emp['current_month']['cost'], 2) ?></td>
                                    <td class="text-end">
                                        <span class="<?= $emp['current_month']['profit'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                            KES <?= number_format($emp['current_month']['profit'], 2) ?>
                                        </span>
                                    </td>
                                    <td><?= number_format($emp['year_to_date']['hours'], 1) ?> hrs</td>
                                    <td class="text-center">
                                        <?php 
                                        $billableClassYtd = $emp['year_to_date']['billable_percentage'] >= 80 ? 'bg-success-subtle text-success' : 
                                                          ($emp['year_to_date']['billable_percentage'] >= 60 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger');
                                        ?>
                                        <span class="badge <?= $billableClassYtd ?>"><?= number_format($emp['year_to_date']['billable_percentage'], 1) ?>%</span>
                                    </td>
                                    <td class="text-end">KES <?= number_format($emp['year_to_date']['value'], 2) ?></td>
                                    <td class="text-end">KES <?= number_format($emp['year_to_date']['cost'], 2) ?></td>
                                    <td class="text-end">
                                        <span class="<?= $emp['year_to_date']['profit'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                            KES <?= number_format($emp['year_to_date']['profit'], 2) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportTable() {
    const table = document.getElementById('workHourTable');
    const rows = Array.from(table.querySelectorAll('tr'));
    let csv = '';
    
    rows.forEach(row => {
        const cells = Array.from(row.querySelectorAll('th, td'));
        const rowData = cells.map(cell => `"${cell.textContent.trim()}"`).join(',');
        csv += rowData + '\n';
    });
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'work_hour_analysis_<?= date('Y-m') ?>.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}
</script>
