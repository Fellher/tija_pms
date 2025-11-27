<?php
/**
 * Client Financials Analysis
 * Comprehensive financial analysis for a client including projects, sales, billings, and employee work value
 * @package    Tija CRM
 * @subpackage Client Financials
 */

$clientID = isset($_GET['client_id']) ? Utility::clean_string($_GET['client_id']) : '';

// Get client information
$client = Client::clients(array('clientID' => $clientID), true, $DBConn);

// Get current year and month for analysis
$currentYear = date('Y');
$currentMonth = date('Y-m');
$yearStart = $currentYear . '-01-01';
$monthStart = $currentMonth . '-01';
$monthEnd = date('Y-m-t');

// Initialize financial analysis data
$clientFinancials = [
    'overview' => [
        'total_projects' => 0,
        'total_sales_cases' => 0,
        'total_project_value' => 0,
        'total_sales_value' => 0,
        'total_billed' => 0,
        'total_paid' => 0,
        'outstanding_amount' => 0,
        'overdue_amount' => 0,
        'total_employee_hours' => 0,
        'total_employee_cost' => 0,
        'total_employee_value' => 0,
        'profit_margin' => 0
    ],
    'projects' => [],
    'sales_cases' => [],
    'invoices' => [],
    'employee_analysis' => [],
    'monthly_trends' => [],
    'performance_metrics' => []
];

if ($client) {
    // Get project billings for this client
    $projectBillings = Projects::project_billings(array(
        'clientID' => $clientID,
        'orgDataID' => $orgDataID,
        'entityID' => $entityID,
        'Suspended' => 'N'
    ), false, $DBConn);

    // Get sales cases for this client
    $salesCases = Sales::sales_case_mid(array(
        'clientID' => $clientID,
        'orgDataID' => $orgDataID,
        'entityID' => $entityID,
        'Suspended' => 'N'
    ), false, $DBConn);

    // Get invoices for this client
    $clientInvoices = Invoice::invoices_full(array(
        'clientID' => $clientID,
        'orgDataID' => $orgDataID,
        'entityID' => $entityID,
        'Suspended' => 'N'
    ), false, $DBConn);

    // Process project data
    if ($projectBillings && is_array($projectBillings)) {
        foreach ($projectBillings as $project) {
            $clientFinancials['overview']['total_projects']++;
            $clientFinancials['overview']['total_project_value'] += $project->projectValue ? $project->projectValue : 0;
            $clientFinancials['overview']['total_billed'] += $project->total_billed ? $project->total_billed : 0;
            $clientFinancials['overview']['total_paid'] += $project->paid_amount ? $project->paid_amount : 0;
            $clientFinancials['overview']['outstanding_amount'] += $project->outstanding_amount ? $project->outstanding_amount : 0;
            $clientFinancials['overview']['total_employee_hours'] += $project->total_hours_logged ? $project->total_hours_logged : 0;
            $clientFinancials['overview']['total_employee_value'] += $project->total_time_value ? $project->total_time_value : 0;
            
            $clientFinancials['projects'][] = $project;
        }
    }

    // Process sales cases data
    if ($salesCases && is_array($salesCases)) {
        foreach ($salesCases as $salesCase) {
            $clientFinancials['overview']['total_sales_cases']++;
            $clientFinancials['overview']['total_sales_value'] += $salesCase->salesCaseEstimate ? $salesCase->salesCaseEstimate : 0;
            
            $clientFinancials['sales_cases'][] = $salesCase;
        }
    }

    // Process invoices data
    if ($clientInvoices && is_array($clientInvoices)) {
        foreach ($clientInvoices as $invoice) {
            $clientFinancials['invoices'][] = $invoice;
        }
    }

    // Calculate employee analysis
    $employeeAnalysis = [];
    if ($projectBillings && is_array($projectBillings)) {
        foreach ($projectBillings as $project) {
            // Get time logs for this project
            $timeLogs = TimeAttendance::project_tasks_time_logs_between_dates(
                array('projectID' => $project->projectID, 'Suspended' => 'N'),
                $yearStart, date('Y-m-d'), false, $DBConn
            );

            if ($timeLogs && is_array($timeLogs)) {
                foreach ($timeLogs as $log) {
                    $employeeID = $log->employeeID;
                    $hours = Utility::time_to_seconds($log->taskDuration) / 3600;
                    
                    if (!isset($employeeAnalysis[$employeeID])) {
                        $employeeAnalysis[$employeeID] = [
                            'employee' => Employee::employees(array('ID' => $employeeID), true, $DBConn),
                            'total_hours' => 0,
                            'billable_hours' => 0,
                            'total_value' => 0,
                            'total_cost' => 0,
                            'projects_worked' => []
                        ];
                    }
                    
                    $employeeAnalysis[$employeeID]['total_hours'] += $hours;
                    $employeeAnalysis[$employeeID]['projects_worked'][] = $project->projectName;
                    
                    if ($log->billable == 'Y' || $log->billable == 'y') {
                        $employeeAnalysis[$employeeID]['billable_hours'] += $hours;
                        $billingRate = $log->billableRateValue ? $log->billableRateValue : 100;
                        $employeeAnalysis[$employeeID]['total_value'] += $hours * $billingRate;
                    }
                    
                    // Calculate employee cost
                    $employee = $employeeAnalysis[$employeeID]['employee'];
                    if ($employee && $employee->basicSalary > 0) {
                        $monthlyHours = Workutils::get_total_hours_in_month(date('m'), date('Y'), $DBConn);
                        $costPerHour = $employee->basicSalary / $monthlyHours;
                        $employeeAnalysis[$employeeID]['total_cost'] += $hours * $costPerHour;
                    }
                }
            }
        }
    }

    $clientFinancials['employee_analysis'] = $employeeAnalysis;

    // Calculate profit margin
    $totalRevenue = $clientFinancials['overview']['total_billed'] + $clientFinancials['overview']['total_sales_value'];
    $totalCost = $clientFinancials['overview']['total_employee_cost'];
    $clientFinancials['overview']['profit_margin'] = $totalRevenue > 0 ? (($totalRevenue - $totalCost) / $totalRevenue) * 100 : 0;

    // Calculate monthly trends (last 12 months)
    for ($i = 11; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $monthStart = $month . '-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));
        
        $monthlyBillings = Projects::project_billings(array(
            'clientID' => $clientID,
            'startDate' => $monthStart,
            'endDate' => $monthEnd,
            'orgDataID' => $orgDataID,
            'entityID' => $entityID,
            'Suspended' => 'N'
        ), false, $DBConn);
        
        $monthlyTotal = 0;
        if ($monthlyBillings && is_array($monthlyBillings)) {
            foreach ($monthlyBillings as $billing) {
                $monthlyTotal += $billing->total_billed ? $billing->total_billed : 0;
            }
        }
        
        $clientFinancials['monthly_trends'][] = [
            'month' => $month,
            'month_name' => date('M Y', strtotime($monthStart)),
            'amount' => $monthlyTotal
        ];
    }
}

?>

<div class="container-fluid">
    <!-- Client Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1"><?= $client ? htmlspecialchars($client->clientName) : 'Client Financial Analysis' ?></h4>
                            <p class="text-muted mb-0"><?= $client ? htmlspecialchars($client->clientCode) : '' ?></p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-primary fs-6">Financial Analysis</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <div class="avatar-sm bg-primary-subtle rounded mx-auto mb-3">
                        <span class="avatar-title bg-primary text-white rounded">
                            <i class="ri-project-line"></i>
                        </span>
                    </div>
                    <h5 class="mb-1"><?= $clientFinancials['overview']['total_projects'] ?></h5>
                    <p class="text-muted mb-0">Total Projects</p>
                    <small class="text-success">KES <?= number_format($clientFinancials['overview']['total_project_value'], 2) ?></small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <div class="avatar-sm bg-info-subtle rounded mx-auto mb-3">
                        <span class="avatar-title bg-info text-white rounded">
                            <i class="ri-money-dollar-circle-line"></i>
                        </span>
                    </div>
                    <h5 class="mb-1"><?= $clientFinancials['overview']['total_sales_cases'] ?></h5>
                    <p class="text-muted mb-0">Sales Cases</p>
                    <small class="text-info">KES <?= number_format($clientFinancials['overview']['total_sales_value'], 2) ?></small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <div class="avatar-sm bg-success-subtle rounded mx-auto mb-3">
                        <span class="avatar-title bg-success text-white rounded">
                            <i class="ri-file-list-3-line"></i>
                        </span>
                    </div>
                    <h5 class="mb-1">KES <?= number_format($clientFinancials['overview']['total_billed'], 2) ?></h5>
                    <p class="text-muted mb-0">Total Billed</p>
                    <small class="text-success">KES <?= number_format($clientFinancials['overview']['total_paid'], 2) ?> Paid</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <div class="avatar-sm bg-warning-subtle rounded mx-auto mb-3">
                        <span class="avatar-title bg-warning text-white rounded">
                            <i class="ri-user-line"></i>
                        </span>
                    </div>
                    <h5 class="mb-1"><?= number_format($clientFinancials['overview']['total_employee_hours'], 1) ?></h5>
                    <p class="text-muted mb-0">Employee Hours</p>
                    <small class="text-warning">KES <?= number_format($clientFinancials['overview']['total_employee_value'], 2) ?> Value</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Health -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-pie-chart-line me-2"></i>Financial Health</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-success"><?= number_format($clientFinancials['overview']['profit_margin'], 1) ?>%</h4>
                                <p class="text-muted mb-0">Profit Margin</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-info"><?= number_format($clientFinancials['overview']['outstanding_amount'], 2) ?></h4>
                                <p class="text-muted mb-0">Outstanding</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-bar-chart-line me-2"></i>Monthly Trends</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyTrendsChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Analysis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-project-line me-2"></i>Projects Analysis</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Project</th>
                                    <th>Value</th>
                                    <th>Billed</th>
                                    <th>Billing %</th>
                                    <th>Status</th>
                                    <th>Hours</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clientFinancials['projects'] as $project): ?>
                                <tr>
                                    <td>
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($project->projectName) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($project->projectCode) ?></small>
                                        </div>
                                    </td>
                                    <td class="text-end">KES <?= number_format($project->projectValue, 2) ?></td>
                                    <td class="text-end">KES <?= number_format($project->total_billed, 2) ?></td>
                                    <td class="text-center">
                                        <span class="badge <?= $project->billing_percentage >= 80 ? 'bg-success' : ($project->billing_percentage >= 50 ? 'bg-warning' : 'bg-danger') ?>">
                                            <?= number_format($project->billing_percentage, 1) ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $project->billing_status == 'Fully Billed' ? 'bg-success' : ($project->billing_status == 'Well Billed' ? 'bg-info' : ($project->billing_status == 'Partially Billed' ? 'bg-warning' : 'bg-danger')) ?>">
                                            <?= htmlspecialchars($project->billing_status) ?>
                                        </span>
                                    </td>
                                    <td class="text-end"><?= number_format($project->total_hours_logged, 1) ?> hrs</td>
                                    <td class="text-end">KES <?= number_format($project->total_time_value, 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Analysis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-team-line me-2"></i>Employee Work Analysis</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Total Hours</th>
                                    <th>Billable Hours</th>
                                    <th>Billable %</th>
                                    <th>Value Generated</th>
                                    <th>Cost</th>
                                    <th>ROI</th>
                                    <th>Projects</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clientFinancials['employee_analysis'] as $employeeID => $empData): ?>
                                <?php if ($empData['employee']): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-light rounded me-2">
                                                <span class="avatar-title bg-light text-dark rounded">
                                                    <i class="ri-user-line"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($empData['employee']->employeeName) ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars($empData['employee']->employeeCode) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end"><?= number_format($empData['total_hours'], 1) ?> hrs</td>
                                    <td class="text-end"><?= number_format($empData['billable_hours'], 1) ?> hrs</td>
                                    <td class="text-center">
                                        <?php 
                                        $billablePercentage = $empData['total_hours'] > 0 ? ($empData['billable_hours'] / $empData['total_hours']) * 100 : 0;
                                        $billableClass = $billablePercentage >= 80 ? 'bg-success' : ($billablePercentage >= 60 ? 'bg-warning' : 'bg-danger');
                                        ?>
                                        <span class="badge <?= $billableClass ?>"><?= number_format($billablePercentage, 1) ?>%</span>
                                    </td>
                                    <td class="text-end">KES <?= number_format($empData['total_value'], 2) ?></td>
                                    <td class="text-end">KES <?= number_format($empData['total_cost'], 2) ?></td>
                                    <td class="text-center">
                                        <?php 
                                        $roi = $empData['total_cost'] > 0 ? (($empData['total_value'] - $empData['total_cost']) / $empData['total_cost']) * 100 : 0;
                                        $roiClass = $roi >= 0 ? 'text-success' : 'text-danger';
                                        ?>
                                        <span class="<?= $roiClass ?>"><?= number_format($roi, 1) ?>%</span>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= count(array_unique($empData['projects_worked'])) ?> projects</small>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Cases Analysis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-money-dollar-circle-line me-2"></i>Sales Cases Analysis</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Sales Case</th>
                                    <th>Value</th>
                                    <th>Probability</th>
                                    <th>Stage</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clientFinancials['sales_cases'] as $salesCase):
                                    // var_dump($salesCase);
                                    ?>
                                <tr>
                                    <td>
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($salesCase->salesCaseName) ?></h6>
                                           
                                        </div>
                                    </td>
                                    <td class="text-end">KES <?= number_format($salesCase->salesCaseEstimate, 2) ?></td>
                                    <td class="text-center">
                                        <span class="badge <?= $salesCase->probability >= 80 ? 'bg-success' : ($salesCase->probability >= 50 ? 'bg-warning' : 'bg-danger') ?>">
                                            <?= number_format($salesCase->probability, 0) ?>%
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($salesCase->saleStage) ?></td>
                                    <td>
                                        <span class="badge <?= $salesCase->closeStatus == 'won' ? 'bg-success' : ($salesCase->closeStatus == 'lost' ? 'bg-danger' : 'bg-info') ?>">
                                            <?= htmlspecialchars($salesCase->closeStatus) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($salesCase->DateAdded)) ?></td>
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

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Trends Chart
    const monthlyTrendsCtx = document.getElementById('monthlyTrendsChart');
    if (monthlyTrendsCtx) {
        const monthlyData = <?= json_encode($clientFinancials['monthly_trends']) ?>;
        
        new Chart(monthlyTrendsCtx, {
            type: 'line',
            data: {
                labels: monthlyData.map(item => item.month_name),
                datasets: [{
                    label: 'Monthly Billing',
                    data: monthlyData.map(item => item.amount),
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'KES ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
