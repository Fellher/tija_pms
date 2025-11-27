<?php
/**
 * Pay Grade Distribution Report
 * Shows analytics on pay grade distribution across the organization
 */

// Get all pay grades for current entity
$payGrades = Data::pay_grades(['entityID' => $userDetails->entityID, 'Suspended' => 'N'], false, $DBConn);

// Get distribution data
$sql = "SELECT
            pg.payGradeID,
            pg.payGradeCode,
            pg.payGradeName,
            pg.minSalary,
            pg.midSalary,
            pg.maxSalary,
            pg.gradeLevel,
            COUNT(DISTINCT ud.ID) as employeeCount,
            AVG(ud.basicSalary) as avgSalary,
            MIN(ud.basicSalary) as lowestSalary,
            MAX(ud.basicSalary) as highestSalary,
            COUNT(DISTINCT jtpg.jobTitleID) as linkedJobsCount
        FROM tija_pay_grades pg
        LEFT JOIN user_details ud ON pg.payGradeID = ud.payGradeID AND ud.Suspended = 'N'
        LEFT JOIN tija_job_title_pay_grade jtpg ON pg.payGradeID = jtpg.payGradeID
            AND jtpg.isCurrent = 'Y' AND jtpg.Suspended = 'N'
        WHERE pg.entityID = {$userDetails->entityID}
        AND pg.Suspended = 'N'
        GROUP BY pg.payGradeID
        ORDER BY pg.gradeLevel ASC";

$DBConn->query($sql);
$DBConn->execute();
$distribution = $DBConn->resultSet();

// Calculate totals
$totalEmployees = 0;
$totalPayroll = 0;
foreach ($distribution as $row) {
    $totalEmployees += $row->employeeCount;
    $totalPayroll += ($row->avgSalary * $row->employeeCount);
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="ri-bar-chart-box-line me-2"></i>Pay Grade Distribution Report</h5>
                    <a href="?s=core&p=admin&sp=jobs&state=payGrades" class="btn btn-light btn-sm">
                        <i class="ri-arrow-left-line me-1"></i> Back to Pay Grades
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-0"><?= count($payGrades ?? []) ?></h3>
                                <p class="mb-0">Pay Grades</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-0"><?= $totalEmployees ?></h3>
                                <p class="mb-0">Total Employees</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h5 class="mb-0">KES <?= number_format($totalPayroll, 0) ?></h5>
                                <p class="mb-0">Total Payroll</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h5 class="mb-0">KES <?= $totalEmployees > 0 ? number_format($totalPayroll / $totalEmployees, 0) : 0 ?></h5>
                                <p class="mb-0">Avg Salary</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Distribution Table -->
                <h5 class="mb-3">Employee Distribution by Pay Grade</h5>
                <?php if ($distribution && count($distribution) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Level</th>
                                <th>Pay Grade</th>
                                <th>Salary Range (KES)</th>
                                <th>Employees</th>
                                <th>% of Total</th>
                                <th>Avg Salary</th>
                                <th>Salary Distribution</th>
                                <th>Linked Jobs</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($distribution as $row):
                                $percentage = $totalEmployees > 0 ? ($row->employeeCount / $totalEmployees) * 100 : 0;
                                $rangeWidth = $row->maxSalary - $row->minSalary;
                                $avgPosition = $rangeWidth > 0 && $row->avgSalary > 0
                                    ? (($row->avgSalary - $row->minSalary) / $rangeWidth) * 100
                                    : 0;
                            ?>
                            <tr>
                                <td class="text-center">
                                    <span class="badge bg-info">Level <?= $row->gradeLevel ?></span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($row->payGradeCode) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($row->payGradeName) ?></small>
                                </td>
                                <td>
                                    <small>
                                        Min: <?= number_format($row->minSalary, 0) ?><br>
                                        Mid: <?= number_format($row->midSalary, 0) ?><br>
                                        Max: <?= number_format($row->maxSalary, 0) ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <h4 class="mb-0 text-primary"><?= $row->employeeCount ?></h4>
                                </td>
                                <td class="text-center">
                                    <?php if ($row->employeeCount > 0): ?>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-success" role="progressbar"
                                             style="width: <?= $percentage ?>%">
                                            <?= number_format($percentage, 1) ?>%
                                        </div>
                                    </div>
                                    <?php else: ?>
                                    <span class="text-muted">0%</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row->employeeCount > 0): ?>
                                    <strong>KES <?= number_format($row->avgSalary, 0) ?></strong>
                                    <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row->employeeCount > 0 && $row->lowestSalary > 0): ?>
                                    <div class="position-relative" style="height: 30px; background: #e9ecef; border-radius: 4px;">
                                        <!-- Min marker -->
                                        <div style="position: absolute; left: 0; top: 0; height: 100%; width: 2px; background: red;"></div>
                                        <!-- Max marker -->
                                        <div style="position: absolute; right: 0; top: 0; height: 100%; width: 2px; background: red;"></div>
                                        <!-- Mid marker -->
                                        <div style="position: absolute; left: 50%; top: 0; height: 100%; width: 2px; background: orange;"></div>
                                        <!-- Average position -->
                                        <div class="bg-primary" style="position: absolute; left: <?= max(0, min(100, $avgPosition)) ?>%; top: 5px; height: 20px; width: 4px; border-radius: 2px;"></div>
                                        <small style="position: absolute; bottom: -18px; left: 0;">Min</small>
                                        <small style="position: absolute; bottom: -18px; right: 0;">Max</small>
                                    </div>
                                    <small class="text-muted d-block mt-3">
                                        Range: <?= number_format($row->lowestSalary, 0) ?> - <?= number_format($row->highestSalary, 0) ?>
                                    </small>
                                    <?php else: ?>
                                    <span class="text-muted">No employees</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($row->linkedJobsCount > 0): ?>
                                    <span class="badge bg-primary"><?= $row->linkedJobsCount ?> Job(s)</span>
                                    <?php else: ?>
                                    <span class="text-muted">None</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="3" class="text-end">TOTALS:</th>
                                <th class="text-center"><?= $totalEmployees ?></th>
                                <th class="text-center">100%</th>
                                <th>KES <?= $totalEmployees > 0 ? number_format($totalPayroll / $totalEmployees, 0) : 0 ?></th>
                                <th colspan="2">Total Payroll: KES <?= number_format($totalPayroll, 0) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Charts -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Employee Distribution by Grade Level</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="distributionChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Payroll Distribution by Grade</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="payrollChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Insights -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="ri-lightbulb-line me-2"></i>Insights & Recommendations</h6>
                            </div>
                            <div class="card-body">
                                <?php
                                $insights = [];

                                // Check for grades with no employees
                                $emptyGrades = array_filter($distribution, function($g) { return $g->employeeCount == 0; });
                                if (count($emptyGrades) > 0) {
                                    $insights[] = "<div class='alert alert-warning'><i class='ri-alert-line me-2'></i><strong>Empty Grades:</strong> " . count($emptyGrades) . " pay grade(s) have no employees assigned. Consider removing or reviewing these grades.</div>";
                                }

                                // Check for grades with salaries outside range
                                foreach ($distribution as $row) {
                                    if ($row->employeeCount > 0) {
                                        if ($row->lowestSalary < $row->minSalary) {
                                            $insights[] = "<div class='alert alert-danger'><i class='ri-error-warning-line me-2'></i><strong>{$row->payGradeCode}:</strong> Some employees are paid below the minimum (KES " . number_format($row->lowestSalary, 0) . " < " . number_format($row->minSalary, 0) . ")</div>";
                                        }
                                        if ($row->highestSalary > $row->maxSalary) {
                                            $insights[] = "<div class='alert alert-warning'><i class='ri-alert-line me-2'></i><strong>{$row->payGradeCode}:</strong> Some employees are paid above the maximum (KES " . number_format($row->highestSalary, 0) . " > " . number_format($row->maxSalary, 0) . ")</div>";
                                        }
                                    }
                                }

                                // Check for imbalanced distribution
                                foreach ($distribution as $row) {
                                    $pct = $totalEmployees > 0 ? ($row->employeeCount / $totalEmployees) * 100 : 0;
                                    if ($pct > 40) {
                                        $insights[] = "<div class='alert alert-info'><i class='ri-information-line me-2'></i><strong>High Concentration:</strong> {$row->payGradeCode} has " . number_format($pct, 1) . "% of all employees. Consider if this is appropriate for your organization structure.</div>";
                                    }
                                }

                                if (count($insights) > 0) {
                                    foreach ($insights as $insight) {
                                        echo $insight;
                                    }
                                } else {
                                    echo "<div class='alert alert-success'><i class='ri-checkbox-circle-line me-2'></i><strong>Excellent!</strong> Your pay grade structure looks well-balanced with no obvious issues.</div>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php else: ?>
                <div class="alert alert-info">
                    <i class="ri-information-line me-2"></i>
                    No pay grade data available yet. Add pay grades and assign employees to see distribution analytics.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js for visualization -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
<?php if ($distribution && count($distribution) > 0): ?>
// Employee Distribution Chart
const distributionCtx = document.getElementById('distributionChart').getContext('2d');
new Chart(distributionCtx, {
    type: 'bar',
    data: {
        labels: [<?php foreach($distribution as $r) echo "'" . $r->payGradeCode . "',"; ?>],
        datasets: [{
            label: 'Number of Employees',
            data: [<?php foreach($distribution as $r) echo $r->employeeCount . ','; ?>],
            backgroundColor: [
                'rgba(54, 162, 235, 0.7)',
                'rgba(75, 192, 192, 0.7)',
                'rgba(255, 206, 86, 0.7)',
                'rgba(255, 159, 64, 0.7)',
                'rgba(153, 102, 255, 0.7)',
                'rgba(255, 99, 132, 0.7)'
            ],
            borderColor: [
                'rgba(54, 162, 235, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(255, 159, 64, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 99, 132, 1)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            title: {
                display: true,
                text: 'Employees per Pay Grade'
            }
        }
    }
});

// Payroll Distribution Chart
const payrollCtx = document.getElementById('payrollChart').getContext('2d');
new Chart(payrollCtx, {
    type: 'doughnut',
    data: {
        labels: [<?php foreach($distribution as $r) echo "'" . $r->payGradeCode . " (" . $r->employeeCount . ")',"; ?>],
        datasets: [{
            label: 'Payroll Amount',
            data: [<?php foreach($distribution as $r) echo ($r->avgSalary * $r->employeeCount) . ','; ?>],
            backgroundColor: [
                'rgba(54, 162, 235, 0.7)',
                'rgba(75, 192, 192, 0.7)',
                'rgba(255, 206, 86, 0.7)',
                'rgba(255, 159, 64, 0.7)',
                'rgba(153, 102, 255, 0.7)',
                'rgba(255, 99, 132, 0.7)'
            ],
            borderColor: '#fff',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right'
            },
            title: {
                display: true,
                text: 'Payroll Cost by Grade'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        if (label) {
                            label += ': ';
                        }
                        label += 'KES ' + context.parsed.toLocaleString();
                        return label;
                    }
                }
            }
        }
    }
});
<?php endif; ?>
</script>

<style>
.progress {
    box-shadow: inset 0 1px 2px rgba(0,0,0,.1);
}
.card {
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}
</style>


