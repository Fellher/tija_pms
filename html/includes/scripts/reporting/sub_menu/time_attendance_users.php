<?php
/**
 * Time Attendance Users Report - Enhanced User-Friendly Interface
 * Comprehensive analysis of employee attendance, work hours, and time tracking
 */

$entityUsers = Employee::employees(array('entityID'=>$entityID, 'Valid'=>'y'), false, $DBConn);

// Get the days from the start of the month to today
$daysInMonth = Utility::month_calendar_dates($month, $year, "weekDay");
$today = date('Y-m-d');
$filteredDays = array_filter($daysInMonth, function($date) use ($today) {
    return $date['date'] <= $today;
});

// Calculate total expected hours
$totalExpectedHours = 0;
foreach($filteredDays as $day) {
    $totalExpectedHours += 8; // assuming 8 hours per work day
}

// Initialize attendance metrics
$attendanceMetrics = [
    'overall_stats' => [
        'total_employees' => 0,
        'total_expected_hours' => 0,
        'total_work_hours' => 0,
        'total_absence_hours' => 0,
        'total_overtime_hours' => 0,
        'attendance_rate' => 0,
        'punctuality_rate' => 0
    ],
    'department_stats' => [],
    'employee_data' => [],
    'performance_tiers' => [
        'excellent' => [],
        'good' => [],
        'needs_improvement' => []
    ]
];

// Process employee data
if($entityUsers && is_array($entityUsers)) {
    foreach($entityUsers as $emp) {
        $attendanceMetrics['overall_stats']['total_employees']++;
        
        $hoursAbsenceCummSec = 0;
        $hoursAbsenceCummEmp = 0;
        $workHrsCummEmp = 0;
        $overtimeHrsCumm = 0;
        $totalWorkHourBalanceCumm = 0;
        
        $department = Data::unit_user_assignments_full(array('userID'=>$emp->ID, 'unitTypeID'=>'1'), true, $DBConn);
        $absence = TimeAttendance::absence_full(array('userID'=>$emp->ID), false, $DBConn);

        // Process absence records
        if($absence && is_array($absence)) {
            foreach($absence as $abs) {
                $absenceMonth = date('m', strtotime($abs->absenceDate));
                if ($absenceMonth !== $month) {
                    continue;
                }
                
                $start = DateTime::createFromFormat('H:i', $abs->startTime);
                $end = DateTime::createFromFormat('H:i', $abs->endTime);
                $interval = $start->diff($end);
                
                if(!$abs->absenceHrs) {
                    $hours = $interval->h;
                    $minutes = $interval->i;
                    if ($hours == 0 && $minutes == 0) {
                        $absenceTimeInSeconds = $end->getTimestamp() - $start->getTimestamp();
                        $hours = floor($absenceTimeInSeconds / 3600);
                        $minutes = floor(($absenceTimeInSeconds % 3600) / 60);
                        $seconds = $absenceTimeInSeconds % 60;
                        $absenceHrs = "{$hours}:{$minutes}:{$seconds}";
                    }
                } else {
                    $absenceHrs = $abs->absenceHrs;
                }
                
                if(preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $absenceHrs)) {
                    list($h, $m, $s) = explode(':', $absenceHrs);
                    $hoursAbsenceCummEmp += $h + ($m / 60) + ($s / 3600);
                    $absenceHrsInSeconds = ($h * 3600) + ($m * 60) + $s;
                    $hoursAbsenceCummSec += $absenceHrsInSeconds;
                } else {
                    $hoursAbsenceCummEmp += $absenceHrs;
                }
            }
        }

        // Process time logs
        $timeLogs = TimeAttendance::project_tasks_time_logs_full(array('employeeID'=>$emp->ID, 'Suspended'=>'N'), false, $DBConn);
        if($timeLogs && is_array($timeLogs)) {
            foreach($timeLogs as $log) {
                $logMonth = date('m', strtotime($log->taskDate));
                if ($logMonth !== $month) {
                    continue;
                }
                
                $workLogInSeconds = Utility::time_to_seconds($log->taskDuration);
                $workHrsCummEmp += $workLogInSeconds ?? 0;
            }
        }

        $totalUserWorkHours = $hoursAbsenceCummSec + $workHrsCummEmp;
        $totalWorkHourBalanceCumm = ($totalExpectedHours*60*60) - $totalUserWorkHours;
        
        // Calculate attendance rate
        $attendanceRate = $totalExpectedHours > 0 ? (($totalExpectedHours - ($hoursAbsenceCummSec/3600)) / $totalExpectedHours) * 100 : 0;
        
        // Department analysis
        $deptName = $emp->businessUnitName ? $emp->businessUnitName : ($department && $department->unitName ? $department->unitName : "Unknown");
        if (!isset($attendanceMetrics['department_stats'][$deptName])) {
            $attendanceMetrics['department_stats'][$deptName] = [
                'employee_count' => 0,
                'total_expected_hours' => 0,
                'total_work_hours' => 0,
                'total_absence_hours' => 0,
                'avg_attendance_rate' => 0
            ];
        }
        
        $attendanceMetrics['department_stats'][$deptName]['employee_count']++;
        $attendanceMetrics['department_stats'][$deptName]['total_expected_hours'] += $totalExpectedHours;
        $attendanceMetrics['department_stats'][$deptName]['total_work_hours'] += $workHrsCummEmp/3600;
        $attendanceMetrics['department_stats'][$deptName]['total_absence_hours'] += $hoursAbsenceCummSec/3600;
        
        // Store employee data
        $employeeData = [
            'employee' => $emp,
            'department' => $deptName,
            'expected_hours' => $totalExpectedHours,
            'work_hours' => $workHrsCummEmp/3600,
            'absence_hours' => $hoursAbsenceCummSec/3600,
            'overtime_hours' => $overtimeHrsCumm,
            'total_hours' => $totalUserWorkHours/3600,
            'balance_hours' => $totalWorkHourBalanceCumm/3600,
            'attendance_rate' => $attendanceRate,
            'work_hours_formatted' => Utility::format_time($workHrsCummEmp, ":", false),
            'absence_hours_formatted' => Utility::format_time($hoursAbsenceCummSec, ":", false),
            'total_hours_formatted' => Utility::format_time($totalUserWorkHours, ":", false),
            'balance_hours_formatted' => Utility::format_time($totalWorkHourBalanceCumm, ":", false)
        ];
        
        $attendanceMetrics['employee_data'][] = $employeeData;
        
        // Categorize performance
        if ($attendanceRate >= 95) {
            $attendanceMetrics['performance_tiers']['excellent'][] = $employeeData;
        } elseif ($attendanceRate >= 85) {
            $attendanceMetrics['performance_tiers']['good'][] = $employeeData;
        } else {
            $attendanceMetrics['performance_tiers']['needs_improvement'][] = $employeeData;
        }
        
        // Update overall stats
        $attendanceMetrics['overall_stats']['total_expected_hours'] += $totalExpectedHours;
        $attendanceMetrics['overall_stats']['total_work_hours'] += $workHrsCummEmp/3600;
        $attendanceMetrics['overall_stats']['total_absence_hours'] += $hoursAbsenceCummSec/3600;
        $attendanceMetrics['overall_stats']['total_overtime_hours'] += $overtimeHrsCumm;
    }
}

// Calculate overall attendance rate
$attendanceMetrics['overall_stats']['attendance_rate'] = $attendanceMetrics['overall_stats']['total_expected_hours'] > 0 ? 
    (($attendanceMetrics['overall_stats']['total_expected_hours'] - $attendanceMetrics['overall_stats']['total_absence_hours']) / $attendanceMetrics['overall_stats']['total_expected_hours']) * 100 : 0;

// Calculate department averages
foreach ($attendanceMetrics['department_stats'] as $dept => &$data) {
    $data['avg_attendance_rate'] = $data['employee_count'] > 0 ? 
        (($data['total_expected_hours'] - $data['total_absence_hours']) / $data['total_expected_hours']) * 100 : 0;
}

?>

<div class="container-fluid">
    <!-- Attendance Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-white shadow">
                <div class="card-header bg-primary-subtle">
                    <h6 class="mb-0 text-primary">
                        <i class="ri-team-line me-2"></i>Total Employees
                    </h6>
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
                            <h4 class="mb-0"><?= $attendanceMetrics['overall_stats']['total_employees'] ?></h4>
                            <small class="text-muted">Active employees</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-white shadow">
                <div class="card-header bg-success-subtle">
                    <h6 class="mb-0 text-success">
                        <i class="ri-time-line me-2"></i>Total Work Hours
                    </h6>
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
                            <h4 class="mb-0"><?= number_format($attendanceMetrics['overall_stats']['total_work_hours'], 1) ?></h4>
                            <small class="text-muted">Hours logged this month</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-white shadow">
                <div class="card-header bg-warning-subtle">
                    <h6 class="mb-0 text-warning">
                        <i class="ri-user-unfollow-line me-2"></i>Absence Hours
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-warning-subtle rounded">
                                <span class="avatar-title bg-warning-subtle text-warning rounded">
                                    <i class="ri-user-unfollow-line fs-20"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0"><?= number_format($attendanceMetrics['overall_stats']['total_absence_hours'], 1) ?></h4>
                            <small class="text-muted">Hours absent</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-white shadow">
                <div class="card-header bg-info-subtle">
                    <h6 class="mb-0 text-info">
                        <i class="ri-percent-line me-2"></i>Attendance Rate
                    </h6>
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
                            <h4 class="mb-0"><?= number_format($attendanceMetrics['overall_stats']['attendance_rate'], 1) ?>%</h4>
                            <small class="text-muted">Overall attendance</small>
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
                    <h5 class="card-title mb-0">
                        <i class="ri-trophy-line me-2"></i>Excellent Attendance
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar-sm bg-success-subtle rounded mx-auto mb-3">
                            <span class="avatar-title bg-success-subtle text-success rounded">
                                <i class="ri-trophy-line fs-20"></i>
                            </span>
                        </div>
                        <h4 class="text-success"><?= count($attendanceMetrics['performance_tiers']['excellent']) ?></h4>
                        <p class="text-muted mb-0">≥ 95% Attendance</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Rate</th>
                                    <th>Hours</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($attendanceMetrics['performance_tiers']['excellent'], 0, 5) as $emp): ?>
                                <tr>
                                    <td>
                                        <small><?= htmlspecialchars($emp['employee']->employeeName) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success"><?= number_format($emp['attendance_rate'], 0) ?>%</span>
                                    </td>
                                    <td>
                                        <small><?= $emp['work_hours_formatted'] ?></small>
                                    </td>
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
                    <h5 class="card-title mb-0">
                        <i class="ri-user-line me-2"></i>Good Attendance
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar-sm bg-warning-subtle rounded mx-auto mb-3">
                            <span class="avatar-title bg-warning-subtle text-warning rounded">
                                <i class="ri-user-line fs-20"></i>
                            </span>
                        </div>
                        <h4 class="text-warning"><?= count($attendanceMetrics['performance_tiers']['good']) ?></h4>
                        <p class="text-muted mb-0">85-94% Attendance</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Rate</th>
                                    <th>Hours</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($attendanceMetrics['performance_tiers']['good'], 0, 5) as $emp): ?>
                                <tr>
                                    <td>
                                        <small><?= htmlspecialchars($emp['employee']->employeeName) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning-subtle text-warning"><?= number_format($emp['attendance_rate'], 0) ?>%</span>
                                    </td>
                                    <td>
                                        <small><?= $emp['work_hours_formatted'] ?></small>
                                    </td>
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
                    <h5 class="card-title mb-0">
                        <i class="ri-alert-line me-2"></i>Needs Improvement
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar-sm bg-danger-subtle rounded mx-auto mb-3">
                            <span class="avatar-title bg-danger-subtle text-danger rounded">
                                <i class="ri-alert-line fs-20"></i>
                            </span>
                        </div>
                        <h4 class="text-danger"><?= count($attendanceMetrics['performance_tiers']['needs_improvement']) ?></h4>
                        <p class="text-muted mb-0">< 85% Attendance</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Rate</th>
                                    <th>Hours</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($attendanceMetrics['performance_tiers']['needs_improvement'], 0, 5) as $emp): ?>
                                <tr>
                                    <td>
                                        <small><?= htmlspecialchars($emp['employee']->employeeName) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger-subtle text-danger"><?= number_format($emp['attendance_rate'], 0) ?>%</span>
                                    </td>
                                    <td>
                                        <small><?= $emp['work_hours_formatted'] ?></small>
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

    <!-- Department Analysis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-building-line me-2"></i>Department Attendance Analysis
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Employees</th>
                                    <th>Expected Hours</th>
                                    <th>Work Hours</th>
                                    <th>Absence Hours</th>
                                    <th>Attendance Rate</th>
                                    <th>Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendanceMetrics['department_stats'] as $dept => $data): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-light rounded me-2">
                                                <span class="avatar-title bg-light text-dark rounded">
                                                    <i class="ri-building-line"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($dept) ?></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary"><?= $data['employee_count'] ?></span>
                                    </td>
                                    <td><?= number_format($data['total_expected_hours'], 1) ?> hrs</td>
                                    <td><?= number_format($data['total_work_hours'], 1) ?> hrs</td>
                                    <td><?= number_format($data['total_absence_hours'], 1) ?> hrs</td>
                                    <td>
                                        <?php 
                                        $rateClass = $data['avg_attendance_rate'] >= 95 ? 'bg-success-subtle text-success' : 
                                                   ($data['avg_attendance_rate'] >= 85 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger');
                                        ?>
                                        <span class="badge <?= $rateClass ?>"><?= number_format($data['avg_attendance_rate'], 1) ?>%</span>
                                    </td>
                                    <td>
                                        <?php 
                                        $performance = $data['avg_attendance_rate'] >= 95 ? 'Excellent' : 
                                                     ($data['avg_attendance_rate'] >= 85 ? 'Good' : 'Needs Improvement');
                                        $performanceClass = $data['avg_attendance_rate'] >= 95 ? 'bg-success-subtle text-success' : 
                                                          ($data['avg_attendance_rate'] >= 85 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger');
                                        ?>
                                        <span class="badge <?= $performanceClass ?>"><?= $performance ?></span>
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

    <!-- Detailed Employee Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-table-line me-2"></i>Detailed Employee Attendance Report
                    </h5>
                    <div class="card-options">
                        <button class="btn btn-sm btn-outline-primary" onclick="exportTable()">
                            <i class="ri-download-line me-1"></i>Export
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="attendanceTable">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Job Title</th>
                                    <th>Department</th>
                                    <th>Expected Hours</th>
                                    <th>Work Hours</th>
                                    <th>Absence Hours</th>
                                    <th>Overtime Hours</th>
                                    <th>Total Hours</th>
                                    <th>Balance Hours</th>
                                    <th>Attendance Rate</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendanceMetrics['employee_data'] as $emp): ?>
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
                                                <small class="text-muted"><?= htmlspecialchars($emp['employee']->payrollNo) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($emp['employee']->jobTitle) ?></td>
                                    <td><?= htmlspecialchars($emp['department']) ?></td>
                                    <td class="text-center"><?= $emp['expected_hours'] ?> hrs</td>
                                    <td><?= $emp['work_hours_formatted'] ?></td>
                                    <td><?= $emp['absence_hours_formatted'] ?></td>
                                    <td><?= $emp['overtime_hours'] ?: '0:00' ?></td>
                                    <td><?= $emp['total_hours_formatted'] ?></td>
                                    <td class="text-center">
                                        <span class="<?= $emp['balance_hours'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= $emp['balance_hours_formatted'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $rateClass = $emp['attendance_rate'] >= 95 ? 'bg-success-subtle text-success' : 
                                                   ($emp['attendance_rate'] >= 85 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger');
                                        ?>
                                        <span class="badge <?= $rateClass ?>"><?= number_format($emp['attendance_rate'], 1) ?>%</span>
                                    </td>
                                    <td>
                                        <?php 
                                        $status = $emp['attendance_rate'] >= 95 ? 'Excellent' : 
                                                ($emp['attendance_rate'] >= 85 ? 'Good' : 'Needs Attention');
                                        $statusClass = $emp['attendance_rate'] >= 95 ? 'bg-success-subtle text-success' : 
                                                     ($emp['attendance_rate'] >= 85 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger');
                                        ?>
                                        <span class="badge <?= $statusClass ?>"><?= $status ?></span>
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

    <!-- Charts -->
    <div class="row mb-4">
        <div class="col-xl-6 col-md-12 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-pie-chart-line me-2"></i>Attendance Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="attendanceDistributionChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-md-12 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-bar-chart-line me-2"></i>Department Attendance Rates
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="departmentAttendanceChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Attendance Distribution Chart
    const attendanceDistributionCtx = document.getElementById('attendanceDistributionChart');
    if (attendanceDistributionCtx) {
        const excellent = <?= count($attendanceMetrics['performance_tiers']['excellent']) ?>;
        const good = <?= count($attendanceMetrics['performance_tiers']['good']) ?>;
        const needsImprovement = <?= count($attendanceMetrics['performance_tiers']['needs_improvement']) ?>;
        
        new Chart(attendanceDistributionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Excellent (≥95%)', 'Good (85-94%)', 'Needs Improvement (<85%)'],
                datasets: [{
                    data: [excellent, good, needsImprovement],
                    backgroundColor: [
                        '#28a745', // Excellent - Green
                        '#ffc107', // Good - Yellow
                        '#dc3545'  // Needs Improvement - Red
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';
                                return context.label + ': ' + value + ' employees (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Department Attendance Chart
    const departmentAttendanceCtx = document.getElementById('departmentAttendanceChart');
    if (departmentAttendanceCtx) {
        const departmentData = <?= json_encode($attendanceMetrics['department_stats']) ?>;
        const deptLabels = Object.keys(departmentData);
        const attendanceRates = deptLabels.map(label => departmentData[label]['avg_attendance_rate']);
        
        new Chart(departmentAttendanceCtx, {
            type: 'bar',
            data: {
                labels: deptLabels,
                datasets: [{
                    label: 'Attendance Rate (%)',
                    data: attendanceRates,
                    backgroundColor: attendanceRates.map(rate => {
                        if (rate >= 95) return '#28a745';
                        if (rate >= 85) return '#ffc107';
                        return '#dc3545';
                    }),
                    borderColor: attendanceRates.map(rate => {
                        if (rate >= 95) return '#28a745';
                        if (rate >= 85) return '#ffc107';
                        return '#dc3545';
                    }),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Attendance Rate: ' + context.parsed.y + '%';
                            }
                        }
                    }
                }
            }
        });
    }
});

// Export function
function exportTable() {
    const table = document.getElementById('attendanceTable');
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
    a.download = 'attendance_report_<?= date('Y-m') ?>.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}
</script>
