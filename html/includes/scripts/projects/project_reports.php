<?php
/**
 * Comprehensive Project Report
 * Detailed project analysis including financials, assignees, progress, and insights
 * All data is retrieved from the database - no sample/placeholder data
 * @package    Tija CRM
 * @subpackage Project Reports
 * @version    3.0.0
 */

$projectID = isset($_GET['pid']) ? Utility::clean_string($_GET['pid']) : '';

if (!$projectID) {
    echo '<div class="alert alert-danger">Project ID is required.</div>';
    return;
}

// Get project details
$project = Projects::projects_full(array('projectID' => $projectID), true, $DBConn);

if (!$project) {
    echo '<div class="alert alert-danger">Project not found.</div>';
    return;
}

// Get client information
$client = Client::clients(array('clientID' => $project->clientID), true, $DBConn);

// Get project billing information (includes time log summaries)
$projectBilling = Projects::project_billings(array('projectID' => $projectID), true, $DBConn);

// Get project tasks with full details
$projectTasks = Projects::projects_tasks(array('projectID' => $projectID, 'Suspended' => 'N'), false, $DBConn);

// Get task assignments
$assignedTasks = Projects::task_user_assignment(array('projectID' => $projectID, 'Suspended' => 'N'), false, $DBConn);

// Get project phases
$projectPhases = Projects::project_phases(array('projectID' => $projectID, 'Suspended' => 'N'), false, $DBConn);

// Get all time logs for this project (from project start to today, or all time if no start date)
$dateFrom = $project->projectStart ? $project->projectStart : date('Y-01-01');
$dateTo = date('Y-m-d');

$timeLogs = TimeAttendance::project_tasks_time_logs_between_dates(
    array('projectID' => $projectID, 'Suspended' => 'N', 'Lapsed' => 'N'),
    $dateFrom,
    $dateTo,
    false,
    $DBConn
);

// Get invoices for this project
$projectInvoices = Invoice::invoices_full(array('projectID' => $projectID, 'Suspended' => 'N'), false, $DBConn);

// Collect all team members (from assignments and time logs)
$teamMemberIDs = array();
$employeeInfo = array();

// Get team members from task assignments
if ($assignedTasks && is_array($assignedTasks)) {
    foreach ($assignedTasks as $assignment) {
        if (!in_array($assignment->userID, $teamMemberIDs)) {
            $teamMemberIDs[] = $assignment->userID;
        }
    }
}

// Get team members from time logs
if ($timeLogs && is_array($timeLogs)) {
    foreach ($timeLogs as $log) {
        if (isset($log->employeeID) && $log->employeeID && !in_array($log->employeeID, $teamMemberIDs)) {
            $teamMemberIDs[] = $log->employeeID;
        }
    }
}

// Get employee information for all team members
foreach ($teamMemberIDs as $employeeID) {
    $employee = Data::users(array('ID' => $employeeID), true, $DBConn);
    if ($employee) {
        $employeeInfo[$employeeID] = $employee;
    }
}

// Initialize project metrics
$projectMetrics = [
    'total_tasks' => 0,
    'completed_tasks' => 0,
    'in_progress_tasks' => 0,
    'pending_tasks' => 0,
    'total_hours_logged' => 0,
    'total_billable_hours' => 0,
    'total_non_billable_hours' => 0,
    'total_cost' => 0,
    'total_value' => 0,
    'total_invoices' => 0,
    'total_billed' => 0,
    'total_paid' => 0,
    'outstanding_amount' => 0,
    'overdue_amount' => 0,
    'team_members' => count($teamMemberIDs),
    'project_duration_days' => 0,
    'days_remaining' => 0,
    'days_elapsed' => 0,
    'budget_utilization' => 0,
    'profit_margin' => 0,
    'billable_percentage' => 0
];

// Calculate task metrics from actual database data
if ($projectTasks && is_array($projectTasks)) {
    $projectMetrics['total_tasks'] = count($projectTasks);

    foreach ($projectTasks as $task) {
        $progress = isset($task->progress) ? floatval($task->progress) : 0;

        if ($progress >= 100) {
            $projectMetrics['completed_tasks']++;
        } elseif ($progress > 0) {
            $projectMetrics['in_progress_tasks']++;
        } else {
            $projectMetrics['pending_tasks']++;
        }
    }
}

// Calculate time metrics from actual time logs
if ($timeLogs && is_array($timeLogs)) {
    foreach ($timeLogs as $log) {
        // Calculate hours - check multiple possible fields
        $hours = 0;

        // Method 1: Check if workHours field exists (decimal hours)
        if (isset($log->workHours) && $log->workHours > 0) {
            $hours = floatval($log->workHours);
        }
        // Method 2: Parse taskDuration (HH:MM or HH:MM:SS format)
        elseif (isset($log->taskDuration) && !empty($log->taskDuration)) {
            $durationSeconds = Utility::time_to_seconds($log->taskDuration);
            if ($durationSeconds !== false) {
                $hours = $durationSeconds / 3600;
            }
        }
        // Method 3: Calculate from startTime and endTime
        elseif (isset($log->startTime) && isset($log->endTime) && !empty($log->startTime) && !empty($log->endTime)) {
            $start = strtotime($log->startTime);
            $end = strtotime($log->endTime);
            if ($start && $end && $end > $start) {
                $hours = ($end - $start) / 3600;
            }
        }

        if ($hours <= 0) continue;

        // Determine if billable - check project billable status first, then log
        $isBillable = false;
        if (isset($project->billable) && ($project->billable == 'Y' || $project->billable == 'y')) {
            $isBillable = true;
        } elseif (isset($log->billable) && ($log->billable == 'Y' || $log->billable == 'y')) {
            $isBillable = true;
        }

        // Update totals
        $projectMetrics['total_hours_logged'] += $hours;

        if ($isBillable) {
            $projectMetrics['total_billable_hours'] += $hours;

            // Calculate value using billing rate
            $billingRate = 0;
            if (isset($log->billableRateValue) && $log->billableRateValue > 0) {
                $billingRate = floatval($log->billableRateValue);
            } elseif (isset($project->billableRateValue) && $project->billableRateValue > 0) {
                $billingRate = floatval($project->billableRateValue);
            } elseif (isset($projectBilling->effective_billing_rate) && $projectBilling->effective_billing_rate > 0) {
                $billingRate = floatval($projectBilling->effective_billing_rate);
            }

            if ($billingRate > 0) {
                $projectMetrics['total_value'] += $hours * $billingRate;
            }
        } else {
            $projectMetrics['total_non_billable_hours'] += $hours;
        }

        // Calculate cost (if employee info available)
        if (isset($log->employeeID) && isset($employeeInfo[$log->employeeID])) {
            $employee = $employeeInfo[$log->employeeID];
            if (isset($employee->basicSalary) && $employee->basicSalary > 0) {
                // Assume 160 working hours per month (standard)
                $monthlyHours = 160;
                $costPerHour = floatval($employee->basicSalary) / $monthlyHours;
                $projectMetrics['total_cost'] += $hours * $costPerHour;
            }
        }
    }
}

// Calculate billable percentage
if ($projectMetrics['total_hours_logged'] > 0) {
    $projectMetrics['billable_percentage'] = ($projectMetrics['total_billable_hours'] / $projectMetrics['total_hours_logged']) * 100;
}

// Calculate financial metrics from actual invoices
if ($projectInvoices && is_array($projectInvoices)) {
    $projectMetrics['total_invoices'] = count($projectInvoices);

    foreach ($projectInvoices as $invoice) {
        $amount = isset($invoice->totalAmount) ? floatval($invoice->totalAmount) : 0;
        if ($amount <= 0) continue;

        $projectMetrics['total_billed'] += $amount;

        $statusID = isset($invoice->invoiceStatusID) ? intval($invoice->invoiceStatusID) : 0;

        // Status 3 = Paid, Status 5 = Overdue
        if ($statusID == 3) {
            $projectMetrics['total_paid'] += $amount;
        } elseif ($statusID == 5) {
            $projectMetrics['overdue_amount'] += $amount;
        } else {
            // Check if invoice is overdue by due date
            if (isset($invoice->dueDate)) {
                $dueDate = new DateTime($invoice->dueDate);
                $now = new DateTime();
                if ($dueDate < $now) {
                    $projectMetrics['overdue_amount'] += $amount;
                } else {
                    $projectMetrics['outstanding_amount'] += $amount;
                }
            } else {
                $projectMetrics['outstanding_amount'] += $amount;
            }
        }
    }
}

// Use project billing data if available (more accurate)
if ($projectBilling) {
    if (isset($projectBilling->total_hours_logged) && $projectBilling->total_hours_logged > 0) {
        $projectMetrics['total_hours_logged'] = floatval($projectBilling->total_hours_logged);
    }
    if (isset($projectBilling->billable_hours_logged) && $projectBilling->billable_hours_logged > 0) {
        $projectMetrics['total_billable_hours'] = floatval($projectBilling->billable_hours_logged);
    }
    if (isset($projectBilling->total_billed) && $projectBilling->total_billed > 0) {
        $projectMetrics['total_billed'] = floatval($projectBilling->total_billed);
    }
    if (isset($projectBilling->paid_amount) && $projectBilling->paid_amount > 0) {
        $projectMetrics['total_paid'] = floatval($projectBilling->paid_amount);
    }
    if (isset($projectBilling->outstanding_amount) && $projectBilling->outstanding_amount > 0) {
        $projectMetrics['outstanding_amount'] = floatval($projectBilling->outstanding_amount);
    }
}

// Calculate project duration
if ($project->projectStart) {
    $startDate = new DateTime($project->projectStart);
    $now = new DateTime();
    $projectMetrics['days_elapsed'] = $startDate->diff($now)->days;

    if ($project->projectClose) {
        $endDate = new DateTime($project->projectClose);
        $projectMetrics['project_duration_days'] = $startDate->diff($endDate)->days;

        if ($endDate > $now) {
            $projectMetrics['days_remaining'] = $now->diff($endDate)->days;
        }
    } elseif ($project->projectDeadline) {
        $deadline = new DateTime($project->projectDeadline);
        $projectMetrics['project_duration_days'] = $startDate->diff($deadline)->days;

        if ($deadline > $now) {
            $projectMetrics['days_remaining'] = $now->diff($deadline)->days;
        }
    }
}

// Calculate budget utilization
if (isset($project->projectValue) && $project->projectValue > 0) {
    $projectMetrics['budget_utilization'] = ($projectMetrics['total_billed'] / floatval($project->projectValue)) * 100;
}

// Calculate profit margin
if ($projectMetrics['total_value'] > 0) {
    $projectMetrics['profit_margin'] = (($projectMetrics['total_value'] - $projectMetrics['total_cost']) / $projectMetrics['total_value']) * 100;
}

// Get project owner
$projectOwner = Data::users(array('ID' => $project->projectOwnerID), true, $DBConn);

// Calculate team performance from actual data
$teamPerformance = array();

// Process each team member
foreach ($teamMemberIDs as $employeeID) {
    if (!isset($employeeInfo[$employeeID])) continue;

    $employee = $employeeInfo[$employeeID];
    $employeeHours = 0;
    $employeeBillableHours = 0;
    $employeeValue = 0;
    $employeeTasks = 0;
    $employeeTaskIDs = array();

    // Calculate hours and value from time logs
    if ($timeLogs && is_array($timeLogs)) {
        foreach ($timeLogs as $log) {
            if (!isset($log->employeeID) || $log->employeeID != $employeeID) continue;

            // Calculate hours
            $hours = 0;
            if (isset($log->workHours) && $log->workHours > 0) {
                $hours = floatval($log->workHours);
            } elseif (isset($log->taskDuration) && !empty($log->taskDuration)) {
                $durationSeconds = Utility::time_to_seconds($log->taskDuration);
                if ($durationSeconds !== false) {
                    $hours = $durationSeconds / 3600;
                }
            } elseif (isset($log->startTime) && isset($log->endTime)) {
                $start = strtotime($log->startTime);
                $end = strtotime($log->endTime);
                if ($start && $end && $end > $start) {
                    $hours = ($end - $start) / 3600;
                }
            }

            if ($hours <= 0) continue;

            $employeeHours += $hours;

            // Check if billable
            $isBillable = false;
            if (isset($project->billable) && ($project->billable == 'Y' || $project->billable == 'y')) {
                $isBillable = true;
            } elseif (isset($log->billable) && ($log->billable == 'Y' || $log->billable == 'y')) {
                $isBillable = true;
            }

            if ($isBillable) {
                $employeeBillableHours += $hours;

                // Calculate value
                $billingRate = 0;
                if (isset($log->billableRateValue) && $log->billableRateValue > 0) {
                    $billingRate = floatval($log->billableRateValue);
                } elseif (isset($project->billableRateValue) && $project->billableRateValue > 0) {
                    $billingRate = floatval($project->billableRateValue);
                }

                if ($billingRate > 0) {
                    $employeeValue += $hours * $billingRate;
                }
            }

            // Track unique tasks
            if (isset($log->projectTaskID) && !in_array($log->projectTaskID, $employeeTaskIDs)) {
                $employeeTaskIDs[] = $log->projectTaskID;
            }
        }
    }

    // Count assigned tasks
    if ($assignedTasks && is_array($assignedTasks)) {
        foreach ($assignedTasks as $assignment) {
            if ($assignment->userID == $employeeID) {
                if (!in_array($assignment->projectTaskID, $employeeTaskIDs)) {
                    $employeeTaskIDs[] = $assignment->projectTaskID;
                }
            }
        }
    }

    $employeeTasks = count($employeeTaskIDs);

    // Calculate efficiency (value per hour)
    $efficiency = $employeeHours > 0 ? ($employeeValue / $employeeHours) : 0;

    $teamPerformance[$employeeID] = [
        'employee' => $employee,
        'hours' => round($employeeHours, 2),
        'billableHours' => round($employeeBillableHours, 2),
        'value' => round($employeeValue, 2),
        'tasks' => $employeeTasks,
        'efficiency' => round($efficiency, 2),
        'billablePercentage' => $employeeHours > 0 ? round(($employeeBillableHours / $employeeHours) * 100, 1) : 0
    ];
}

// Sort team performance by hours (descending)
uasort($teamPerformance, function($a, $b) {
    return $b['hours'] <=> $a['hours'];
});

// Calculate monthly progress (last 12 months) from actual data
$monthlyProgress = array();
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $monthStart = $month . '-01';
    $monthEnd = date('Y-m-t', strtotime($monthStart));

    $monthlyLogs = TimeAttendance::project_tasks_time_logs_between_dates(
        array('projectID' => $projectID, 'Suspended' => 'N', 'Lapsed' => 'N'),
        $monthStart,
        $monthEnd,
        false,
        $DBConn
    );

    $monthlyHours = 0;
    $monthlyBillableHours = 0;
    $monthlyValue = 0;

    if ($monthlyLogs && is_array($monthlyLogs)) {
        foreach ($monthlyLogs as $log) {
            // Calculate hours
            $hours = 0;
            if (isset($log->workHours) && $log->workHours > 0) {
                $hours = floatval($log->workHours);
            } elseif (isset($log->taskDuration) && !empty($log->taskDuration)) {
                $durationSeconds = Utility::time_to_seconds($log->taskDuration);
                if ($durationSeconds !== false) {
                    $hours = $durationSeconds / 3600;
                }
            } elseif (isset($log->startTime) && isset($log->endTime)) {
                $start = strtotime($log->startTime);
                $end = strtotime($log->endTime);
                if ($start && $end && $end > $start) {
                    $hours = ($end - $start) / 3600;
                }
            }

            if ($hours <= 0) continue;

            $monthlyHours += $hours;

            // Check if billable
            $isBillable = false;
            if (isset($project->billable) && ($project->billable == 'Y' || $project->billable == 'y')) {
                $isBillable = true;
            } elseif (isset($log->billable) && ($log->billable == 'Y' || $log->billable == 'y')) {
                $isBillable = true;
            }

            if ($isBillable) {
                $monthlyBillableHours += $hours;

                // Calculate value
                $billingRate = 0;
                if (isset($log->billableRateValue) && $log->billableRateValue > 0) {
                    $billingRate = floatval($log->billableRateValue);
                } elseif (isset($project->billableRateValue) && $project->billableRateValue > 0) {
                    $billingRate = floatval($project->billableRateValue);
                }

                if ($billingRate > 0) {
                    $monthlyValue += $hours * $billingRate;
                }
            }
        }
    }

    $monthlyProgress[] = [
        'month' => $month,
        'month_name' => date('M Y', strtotime($monthStart)),
        'hours' => round($monthlyHours, 2),
        'billableHours' => round($monthlyBillableHours, 2),
        'value' => round($monthlyValue, 2)
    ];
}

// Get user name helper function
function getUserName($userID, $DBConn) {
    $user = Data::users(array('ID' => $userID), true, $DBConn);
    if ($user) {
        if (isset($user->FirstName) && isset($user->Surname)) {
            return trim($user->FirstName . ' ' . $user->Surname);
        } elseif (isset($user->employeeName)) {
            return $user->employeeName;
        } elseif (isset($user->userName)) {
            return $user->userName;
        }
    }
    return 'Unknown User';
}
?>

<div class="container-fluid">
    <!-- Project Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1"><?= htmlspecialchars($project->projectName ?? 'Unnamed Project') ?></h4>
                            <p class="text-muted mb-0">
                                <?= htmlspecialchars($project->projectCode ?? 'N/A') ?> - Comprehensive Project Report
                            </p>
                            <small class="text-muted">
                                Client: <?= $client ? htmlspecialchars($client->clientName ?? 'Unknown') : 'Unknown' ?> |
                                Owner: <?= $projectOwner ? htmlspecialchars(getUserName($project->projectOwnerID, $DBConn)) : 'Unknown' ?>
                            </small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-primary fs-6">Project Report</span>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <?= $project->projectStart ? date('M d, Y', strtotime($project->projectStart)) : 'No start date' ?> -
                                    <?= $project->projectClose ? date('M d, Y', strtotime($project->projectClose)) : ($project->projectDeadline ? date('M d, Y', strtotime($project->projectDeadline)) : 'Ongoing') ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Overview -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <div class="avatar-sm bg-primary-subtle rounded mx-auto mb-3">
                        <span class="avatar-title bg-primary text-white rounded">
                            <i class="ri-money-dollar-circle-line"></i>
                        </span>
                    </div>
                    <h5 class="mb-1">KES <?= number_format($project->projectValue ?? 0, 2) ?></h5>
                    <p class="text-muted mb-0">Project Value</p>
                    <small class="text-success"><?= number_format($projectMetrics['budget_utilization'], 1) ?>% Utilized</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <div class="avatar-sm bg-info-subtle rounded mx-auto mb-3">
                        <span class="avatar-title bg-info text-white rounded">
                            <i class="ri-task-line"></i>
                        </span>
                    </div>
                    <h5 class="mb-1"><?= $projectMetrics['total_tasks'] ?></h5>
                    <p class="text-muted mb-0">Total Tasks</p>
                    <small class="text-info">
                        <?= $projectMetrics['completed_tasks'] ?> Completed |
                        <?= $projectMetrics['in_progress_tasks'] ?> In Progress
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <div class="avatar-sm bg-success-subtle rounded mx-auto mb-3">
                        <span class="avatar-title bg-success text-white rounded">
                            <i class="ri-time-line"></i>
                        </span>
                    </div>
                    <h5 class="mb-1"><?= number_format($projectMetrics['total_hours_logged'], 1) ?></h5>
                    <p class="text-muted mb-0">Hours Logged</p>
                    <small class="text-success">
                        <?= number_format($projectMetrics['total_billable_hours'], 1) ?> Billable
                        (<?= number_format($projectMetrics['billable_percentage'], 1) ?>%)
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <div class="avatar-sm bg-warning-subtle rounded mx-auto mb-3">
                        <span class="avatar-title bg-warning text-white rounded">
                            <i class="ri-team-line"></i>
                        </span>
                    </div>
                    <h5 class="mb-1"><?= $projectMetrics['team_members'] ?></h5>
                    <p class="text-muted mb-0">Team Members</p>
                    <small class="text-warning">
                        <?= $projectMetrics['days_remaining'] > 0 ? $projectMetrics['days_remaining'] . ' Days Left' : ($projectMetrics['days_elapsed'] . ' Days Elapsed') ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Overview -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-pie-chart-line me-2"></i>Financial Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-success">KES <?= number_format($projectMetrics['total_billed'], 2) ?></h4>
                                <p class="text-muted mb-0">Total Billed</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-info">KES <?= number_format($projectMetrics['total_paid'], 2) ?></h4>
                                <p class="text-muted mb-0">Total Paid</p>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-warning">KES <?= number_format($projectMetrics['outstanding_amount'], 2) ?></h4>
                                <p class="text-muted mb-0">Outstanding</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-danger">KES <?= number_format($projectMetrics['overdue_amount'], 2) ?></h4>
                                <p class="text-muted mb-0">Overdue</p>
                            </div>
                        </div>
                    </div>
                    <?php if ($projectMetrics['total_value'] > 0): ?>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <div class="text-center">
                                <h5 class="text-primary">KES <?= number_format($projectMetrics['total_value'], 2) ?></h5>
                                <p class="text-muted mb-0 small">Total Billable Value Generated</p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-bar-chart-line me-2"></i>Monthly Progress</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyProgressChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Performance -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-team-line me-2"></i>Team Performance</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Team Member</th>
                                    <th class="text-center">Tasks</th>
                                    <th class="text-end">Total Hours</th>
                                    <th class="text-end">Billable Hours</th>
                                    <th class="text-end">Value Generated</th>
                                    <th class="text-center">Efficiency Rate</th>
                                    <th class="text-center">Billable %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($teamPerformance)): ?>
                                    <?php foreach ($teamPerformance as $employeeID => $performance): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-light rounded me-2">
                                                <span class="avatar-title bg-primary text-white rounded">
                                                    <?= strtoupper(substr(getUserName($employeeID, $DBConn), 0, 1)) ?>
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars(getUserName($employeeID, $DBConn)) ?></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info"><?= $performance['tasks'] ?></span>
                                    </td>
                                    <td class="text-end"><?= number_format($performance['hours'], 1) ?> hrs</td>
                                    <td class="text-end">
                                        <span class="badge bg-success"><?= number_format($performance['billableHours'], 1) ?> hrs</span>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success">KES <?= number_format($performance['value'], 2) ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $performance['efficiency'] >= 100 ? 'bg-success' : ($performance['efficiency'] >= 50 ? 'bg-warning' : 'bg-danger') ?>">
                                            KES <?= number_format($performance['efficiency'], 0) ?>/hr
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="progress" style="width: 80px; height: 20px; margin: 0 auto;">
                                            <div class="progress-bar <?= $performance['billablePercentage'] >= 80 ? 'bg-success' : ($performance['billablePercentage'] >= 50 ? 'bg-warning' : 'bg-danger') ?>"
                                                 role="progressbar"
                                                 style="width: <?= $performance['billablePercentage'] ?>%">
                                                <?= $performance['billablePercentage'] ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="ri-user-line fs-3 d-block mb-2"></i>
                                            No team members found for this project
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Project Tasks -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-task-line me-2"></i>Project Tasks</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Phase</th>
                                    <th>Progress</th>
                                    <th>Assigned To</th>
                                    <th class="text-end">Hours Logged</th>
                                    <th>Deadline</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($projectTasks && is_array($projectTasks) && count($projectTasks) > 0): ?>
                                    <?php foreach ($projectTasks as $task): ?>
                                <tr>
                                    <td>
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($task->projectTaskName ?? 'Unnamed Task') ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($task->projectTaskCode ?? 'N/A') ?></small>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($task->projectPhaseName ?? 'No Phase') ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress me-2" style="width: 60px; height: 8px;">
                                                <div class="progress-bar" role="progressbar" style="width: <?= $task->progress ?? 0 ?>%" aria-valuenow="<?= $task->progress ?? 0 ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <small><?= number_format($task->progress ?? 0, 0) ?>%</small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $taskAssignments = array();
                                        if ($assignedTasks && is_array($assignedTasks)) {
                                            $taskAssignments = array_filter($assignedTasks, function($assignment) use ($task) {
                                                return isset($assignment->projectTaskID) && $assignment->projectTaskID == $task->projectTaskID;
                                            });
                                        }
                                        ?>
                                        <?php if (!empty($taskAssignments)): ?>
                                            <?php foreach ($taskAssignments as $assignment): ?>
                                                <span class="badge bg-light text-dark me-1">
                                                    <?= htmlspecialchars(getUserName($assignment->userID, $DBConn)) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php
                                        $taskHours = 0;
                                        if ($timeLogs && is_array($timeLogs)) {
                                            foreach ($timeLogs as $log) {
                                                if (isset($log->projectTaskID) && $log->projectTaskID == $task->projectTaskID) {
                                                    $hours = 0;
                                                    if (isset($log->workHours) && $log->workHours > 0) {
                                                        $hours = floatval($log->workHours);
                                                    } elseif (isset($log->taskDuration) && !empty($log->taskDuration)) {
                                                        $durationSeconds = Utility::time_to_seconds($log->taskDuration);
                                                        if ($durationSeconds !== false) {
                                                            $hours = $durationSeconds / 3600;
                                                        }
                                                    }
                                                    $taskHours += $hours;
                                                }
                                            }
                                        }
                                        ?>
                                        <?= number_format($taskHours, 1) ?> hrs
                                    </td>
                                    <td>
                                        <?php if (isset($task->taskDeadline) && $task->taskDeadline): ?>
                                            <?php
                                            $deadline = new DateTime($task->taskDeadline);
                                            $now = new DateTime();
                                            $isOverdue = $deadline < $now && ($task->progress ?? 0) < 100;
                                            $isDueSoon = $deadline <= (clone $now)->modify('+3 days') && ($task->progress ?? 0) < 100;
                                            ?>
                                            <small class="<?= $isOverdue ? 'text-danger' : ($isDueSoon ? 'text-warning' : 'text-muted') ?>">
                                                <?= $deadline->format('M d, Y') ?>
                                                <?php if ($isOverdue): ?>
                                                    <i class="ri-alert-line ms-1"></i>
                                                <?php elseif ($isDueSoon): ?>
                                                    <i class="ri-time-line ms-1"></i>
                                                <?php endif; ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">No deadline</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $progress = $task->progress ?? 0;
                                        $statusClass = $progress >= 100 ? 'bg-success' : ($progress > 0 ? 'bg-warning' : 'bg-secondary');
                                        $statusText = $progress >= 100 ? 'Completed' : ($progress > 0 ? 'In Progress' : 'Not Started');
                                        ?>
                                        <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                                    </td>
                                </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="ri-task-line fs-3 d-block mb-2"></i>
                                            No tasks found for this project
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Project Phases -->
    <?php if ($projectPhases && is_array($projectPhases) && count($projectPhases) > 0): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-layout-line me-2"></i>Project Phases</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($projectPhases as $phase): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card border-left-info">
                                <div class="card-body">
                                    <h6 class="card-title"><?= htmlspecialchars($phase->projectPhaseName ?? 'Unnamed Phase') ?></h6>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-info"><?= number_format($phase->phaseWorkHrs ?? 0, 1) ?> hrs</small>
                                        <small class="text-muted"><?= $phase->phaseWeighting ?? 0 ?>% Weight</small>
                                    </div>
                                    <?php if (isset($phase->billingMilestone) && $phase->billingMilestone): ?>
                                        <div class="mt-2">
                                            <span class="badge bg-success">Billing Milestone</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Project Invoices -->
    <?php if ($projectInvoices && is_array($projectInvoices) && count($projectInvoices) > 0): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-file-list-3-line me-2"></i>Project Invoices</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Date</th>
                                    <th>Due Date</th>
                                    <th class="text-end">Amount</th>
                                    <th>Status</th>
                                    <th class="text-center">Days Overdue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($projectInvoices as $invoice): ?>
                                <tr>
                                    <td><?= htmlspecialchars($invoice->invoiceNumber ?? 'N/A') ?></td>
                                    <td><?= isset($invoice->invoiceDate) ? date('M d, Y', strtotime($invoice->invoiceDate)) : 'N/A' ?></td>
                                    <td><?= isset($invoice->dueDate) ? date('M d, Y', strtotime($invoice->dueDate)) : 'N/A' ?></td>
                                    <td class="text-end">KES <?= number_format($invoice->totalAmount ?? 0, 2) ?></td>
                                    <td>
                                        <?php
                                        $statusColor = isset($invoice->invoiceStatusColor) ? $invoice->invoiceStatusColor : '#6c757d';
                                        $statusName = isset($invoice->invoiceStatusName) ? $invoice->invoiceStatusName : 'Unknown';
                                        ?>
                                        <span class="badge" style="background-color: <?= htmlspecialchars($statusColor) ?>">
                                            <?= htmlspecialchars($statusName) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        if (isset($invoice->dueDate)) {
                                            $dueDate = new DateTime($invoice->dueDate);
                                            $now = new DateTime();
                                            $statusID = isset($invoice->invoiceStatusID) ? intval($invoice->invoiceStatusID) : 0;

                                            if ($dueDate < $now && $statusID != 3) {
                                                $daysOverdue = $now->diff($dueDate)->days;
                                                echo '<span class="text-danger">' . $daysOverdue . '</span>';
                                            } else {
                                                echo '<span class="text-muted">-</span>';
                                            }
                                        } else {
                                            echo '<span class="text-muted">-</span>';
                                        }
                                        ?>
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
    <?php endif; ?>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Progress Chart
    const monthlyProgressCtx = document.getElementById('monthlyProgressChart');
    if (monthlyProgressCtx) {
        const monthlyData = <?= json_encode($monthlyProgress) ?>;

        new Chart(monthlyProgressCtx, {
            type: 'bar',
            data: {
                labels: monthlyData.map(item => item.month_name),
                datasets: [{
                    label: 'Total Hours',
                    data: monthlyData.map(item => item.hours),
                    backgroundColor: 'rgba(13, 110, 253, 0.8)',
                    borderColor: '#0d6efd',
                    borderWidth: 1
                }, {
                    label: 'Billable Hours',
                    data: monthlyData.map(item => item.billableHours),
                    backgroundColor: 'rgba(25, 135, 84, 0.8)',
                    borderColor: '#198754',
                    borderWidth: 1
                }, {
                    label: 'Value Generated (KES)',
                    data: monthlyData.map(item => item.value),
                    backgroundColor: 'rgba(255, 193, 7, 0.8)',
                    borderColor: '#ffc107',
                    borderWidth: 1,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Hours'
                        },
                        beginAtZero: true
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Value (KES)'
                        },
                        beginAtZero: true,
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
    }
});
</script>
