<?php
/**
 * Project Team View - Enterprise Level
 * Comprehensive team management and individual member reporting
 *
 * @package Tija Practice Management System
 * @subpackage Projects - Team View
 * @version 3.0.0
 */

// Get project details
$projectDetails = Projects::projects_mini(array('projectID' => $projectID), true, $DBConn);

// Get all team members for the project
$teamMembers = Projects::project_team_full(
    array('projectID' => $projectID, 'Suspended' => 'N'),
    false,
    $DBConn
);

// Get project tasks
$projectTasks = Projects::projects_tasks(array('projectID' => $projectID, 'Suspended' => 'N'), false, $DBConn);

// Get task assignments
$assignedTasks = Projects::task_user_assignment(array('projectID' => $projectID, 'Suspended' => 'N'), false, $DBConn);

// Get time logs for the project
$dateFrom = $projectDetails->projectStart ? $projectDetails->projectStart : date('Y-01-01');
$dateTo = date('Y-m-d');

$timeLogs = TimeAttendance::project_tasks_time_logs_between_dates(
    array('projectID' => $projectID, 'Suspended' => 'N', 'Lapsed' => 'N'),
    $dateFrom,
    $dateTo,
    false,
    $DBConn
);

// Get project team roles
$projectTeamRoles = Projects::project_team_roles(array('Suspended' => 'N'), false, $DBConn);

// Get employees list for adding team members
$employees = Employee::employees(array('Suspended' => 'N'), false, $DBConn);

// Calculate individual team member statistics
$memberStats = array();
$selectedMemberID = isset($_GET['memberID']) ? intval($_GET['memberID']) : null;

if ($teamMembers && is_array($teamMembers)) {
    foreach ($teamMembers as $member) {
        $memberID = $member->userID ?? null;
        if (!$memberID) continue;

        $stats = array(
            'member' => $member,
            'totalHours' => 0,
            'billableHours' => 0,
            'nonBillableHours' => 0,
            'totalValue' => 0,
            'tasksAssigned' => 0,
            'tasksCompleted' => 0,
            'tasksInProgress' => 0,
            'tasksPending' => 0,
            'timeLogs' => array(),
            'dailyHours' => array(),
            'phaseBreakdown' => array(),
            'workTypeBreakdown' => array(),
            'recentActivity' => array()
        );

        // Calculate hours from time logs
        if ($timeLogs && is_array($timeLogs)) {
            foreach ($timeLogs as $log) {
                if (!isset($log->employeeID) || $log->employeeID != $memberID) continue;

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

                $stats['totalHours'] += $hours;
                $stats['timeLogs'][] = $log;

                // Check if billable
                $isBillable = false;
                if (isset($projectDetails->billable) && ($projectDetails->billable == 'Y' || $projectDetails->billable == 'y')) {
                    $isBillable = true;
                } elseif (isset($log->billable) && ($log->billable == 'Y' || $log->billable == 'y')) {
                    $isBillable = true;
                }

                if ($isBillable) {
                    $stats['billableHours'] += $hours;

                    // Calculate value
                    $billingRate = 0;
                    if (isset($log->billableRateValue) && $log->billableRateValue > 0) {
                        $billingRate = floatval($log->billableRateValue);
                    } elseif (isset($projectDetails->billableRateValue) && $projectDetails->billableRateValue > 0) {
                        $billingRate = floatval($projectDetails->billableRateValue);
                    }

                    if ($billingRate > 0) {
                        $stats['totalValue'] += $hours * $billingRate;
                    }
                } else {
                    $stats['nonBillableHours'] += $hours;
                }

                // Daily hours breakdown
                $logDate = isset($log->taskDate) ? $log->taskDate : date('Y-m-d');
                if (!isset($stats['dailyHours'][$logDate])) {
                    $stats['dailyHours'][$logDate] = 0;
                }
                $stats['dailyHours'][$logDate] += $hours;

                // Phase breakdown
                if (isset($log->projectPhaseID) && $log->projectPhaseID) {
                    $phaseName = $log->projectPhaseName ?? 'Unknown Phase';
                    if (!isset($stats['phaseBreakdown'][$phaseName])) {
                        $stats['phaseBreakdown'][$phaseName] = 0;
                    }
                    $stats['phaseBreakdown'][$phaseName] += $hours;
                }

                // Work type breakdown
                if (isset($log->workTypeID) && $log->workTypeID) {
                    $workTypeName = $log->workTypeName ?? 'Unknown Work Type';
                    if (!isset($stats['workTypeBreakdown'][$workTypeName])) {
                        $stats['workTypeBreakdown'][$workTypeName] = 0;
                    }
                    $stats['workTypeBreakdown'][$workTypeName] += $hours;
                }
            }
        }

        // Calculate task statistics
        if ($assignedTasks && is_array($assignedTasks)) {
            foreach ($assignedTasks as $assignment) {
                if ($assignment->userID == $memberID) {
                    $stats['tasksAssigned']++;

                    // Find task details
                    if ($projectTasks && is_array($projectTasks)) {
                        foreach ($projectTasks as $task) {
                            if ($task->projectTaskID == $assignment->projectTaskID) {
                                $progress = isset($task->progress) ? floatval($task->progress) : 0;
                                if ($progress >= 100) {
                                    $stats['tasksCompleted']++;
                                } elseif ($progress > 0) {
                                    $stats['tasksInProgress']++;
                                } else {
                                    $stats['tasksPending']++;
                                }
                                break;
                            }
                        }
                    }
                }
            }
        }

        // Calculate efficiency metrics
        $stats['efficiency'] = $stats['totalHours'] > 0 ? ($stats['totalValue'] / $stats['totalHours']) : 0;
        $stats['billablePercentage'] = $stats['totalHours'] > 0 ? (($stats['billableHours'] / $stats['totalHours']) * 100) : 0;
        $stats['completionRate'] = $stats['tasksAssigned'] > 0 ? (($stats['tasksCompleted'] / $stats['tasksAssigned']) * 100) : 0;

        // Get recent activity (last 10 time logs)
        $stats['recentActivity'] = array_slice($stats['timeLogs'], -10);

        $memberStats[$memberID] = $stats;
    }
}

// Sort members by total hours (descending)
uasort($memberStats, function($a, $b) {
    return $b['totalHours'] <=> $a['totalHours'];
});

// Get selected member details
$selectedMemberStats = null;
if ($selectedMemberID && isset($memberStats[$selectedMemberID])) {
    $selectedMemberStats = $memberStats[$selectedMemberID];
}
?>

<div class="container-fluid my-3" id="projectTeamViewContainer">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0">
                <i class="ri-team-line me-2 text-primary"></i>Project Team View
            </h3>
            <p class="text-muted mb-0">Manage team members and view detailed performance reports</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button"
                    class="btn btn-outline-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#manage_project_team"
                    data-user-id="<?= htmlspecialchars($userDetails->ID ?? '') ?>"
                    data-project-id="<?= htmlspecialchars($projectID) ?>">
                <i class="ri-user-add-line me-1"></i>Add Team Member
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='?s=user&ss=projects&p=project&pid=<?= $projectID ?>&state=team'">
                <i class="ri-settings-3-line me-1"></i>Team Settings
            </button>
        </div>
    </div>

    <!-- Team Overview Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Team Members</h6>
                            <h3 class="mb-0"><?= count($memberStats) ?></h3>
                            <small class="text-muted">Active members</small>
                        </div>
                        <div class="ms-3">
                            <i class="ri-team-line text-primary" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Hours Logged</h6>
                            <h3 class="mb-0">
                                <?php
                                $totalTeamHours = 0;
                                foreach ($memberStats as $stats) {
                                    $totalTeamHours += $stats['totalHours'];
                                }
                                echo number_format($totalTeamHours, 1);
                                ?>
                            </h3>
                            <small class="text-success">
                                <?php
                                $totalBillableHours = 0;
                                foreach ($memberStats as $stats) {
                                    $totalBillableHours += $stats['billableHours'];
                                }
                                echo number_format($totalBillableHours, 1) . ' billable';
                                ?>
                            </small>
                        </div>
                        <div class="ms-3">
                            <i class="ri-time-line text-success" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Value Generated</h6>
                            <h3 class="mb-0">
                                <?php
                                $totalTeamValue = 0;
                                foreach ($memberStats as $stats) {
                                    $totalTeamValue += $stats['totalValue'];
                                }
                                echo Utility::formatToCurrency($totalTeamValue);
                                ?>
                            </h3>
                            <small class="text-info">Billable work value</small>
                        </div>
                        <div class="ms-3">
                            <i class="ri-money-dollar-circle-line text-info" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-warning h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Average Efficiency</h6>
                            <h3 class="mb-0">
                                <?php
                                $totalEfficiency = 0;
                                $memberCount = 0;
                                foreach ($memberStats as $stats) {
                                    if ($stats['totalHours'] > 0) {
                                        $totalEfficiency += $stats['efficiency'];
                                        $memberCount++;
                                    }
                                }
                                $avgEfficiency = $memberCount > 0 ? ($totalEfficiency / $memberCount) : 0;
                                echo Utility::formatToCurrency($avgEfficiency) . '/hr';
                                ?>
                            </h3>
                            <small class="text-warning">Per hour rate</small>
                        </div>
                        <div class="ms-3">
                            <i class="ri-bar-chart-line text-warning" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Members Grid -->
    <div class="row g-3 mb-4">
        <?php if (!empty($memberStats)): ?>
            <?php foreach ($memberStats as $memberID => $stats):
                $member = $stats['member'];
                $isSelected = $selectedMemberID == $memberID;
            ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 team-member-card <?= $isSelected ? 'border-primary shadow-sm' : '' ?>"
                         data-member-id="<?= $memberID ?>"
                         onclick="viewMemberReport(<?= $memberID ?>)"
                         style="cursor: pointer; transition: all 0.3s ease;">
                        <div class="card-body">
                            <div class="d-flex align-items-start mb-3">
                                <div class="avatar avatar-lg bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center">
                                    <?= strtoupper(substr($member->teamMemberName ?? '?', 0, 1)) ?>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1"><?= htmlspecialchars($member->teamMemberName ?? 'Unknown Member') ?></h5>
                                    <?php if (isset($member->projectTeamRoleName) && $member->projectTeamRoleName): ?>
                                        <span class="badge bg-primary-transparent mb-2">
                                            <i class="ri-user-star-line me-1"></i>
                                            <?= htmlspecialchars($member->projectTeamRoleName) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (isset($member->jobTitle) && $member->jobTitle): ?>
                                        <p class="text-muted small mb-0"><?= htmlspecialchars($member->jobTitle) ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php if ($isSelected): ?>
                                    <i class="ri-checkbox-circle-fill text-primary" style="font-size: 1.5rem;"></i>
                                <?php endif; ?>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="text-center p-2 bg-light rounded">
                                        <h6 class="mb-0 text-primary"><?= number_format($stats['totalHours'], 1) ?></h6>
                                        <small class="text-muted">Hours</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-2 bg-light rounded">
                                        <h6 class="mb-0 text-success"><?= $stats['tasksAssigned'] ?></h6>
                                        <small class="text-muted">Tasks</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">Billable Ratio</small>
                                    <small class="fw-bold"><?= number_format($stats['billablePercentage'], 1) ?>%</small>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar <?= $stats['billablePercentage'] >= 80 ? 'bg-success' : ($stats['billablePercentage'] >= 50 ? 'bg-warning' : 'bg-danger') ?>"
                                         role="progressbar"
                                         style="width: <?= $stats['billablePercentage'] ?>%"></div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block">Value Generated</small>
                                    <strong class="text-success"><?= Utility::formatToCurrency($stats['totalValue']) ?></strong>
                                </div>
                                <button class="btn btn-sm btn-outline-primary"
                                        onclick="event.stopPropagation(); viewMemberReport(<?= $memberID ?>)">
                                    <i class="ri-file-chart-line me-1"></i>View Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="ri-team-line text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
                        <h5 class="text-muted mt-3 mb-2">No Team Members Found</h5>
                        <p class="text-muted mb-4">Start building your project team by adding team members.</p>
                        <button type="button"
                                class="btn btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#manage_project_team"
                                data-user-id="<?= htmlspecialchars($userDetails->ID ?? '') ?>"
                                data-project-id="<?= htmlspecialchars($projectID) ?>">
                            <i class="ri-user-add-line me-1"></i>Add Team Member
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Individual Member Report (shown when member is selected) -->
    <?php if ($selectedMemberStats): ?>
        <div class="card custom-card mb-4" id="memberReportSection">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">
                        <i class="ri-file-chart-line me-2"></i>
                        <?= htmlspecialchars($selectedMemberStats['member']->teamMemberName ?? 'Unknown Member') ?> - Performance Report
                    </h5>
                    <small class="opacity-75">
                        <?= htmlspecialchars($selectedMemberStats['member']->projectTeamRoleName ?? 'Team Member') ?>
                        <?php if (isset($selectedMemberStats['member']->jobTitle) && $selectedMemberStats['member']->jobTitle): ?>
                            | <?= htmlspecialchars($selectedMemberStats['member']->jobTitle) ?>
                        <?php endif; ?>
                    </small>
                </div>
                <button type="button" class="btn btn-sm btn-light" onclick="closeMemberReport()">
                    <i class="ri-close-line"></i>
                </button>
            </div>
            <div class="card-body">
                <!-- Member Statistics Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-sm-6">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Total Hours</h6>
                                <h3 class="mb-0 text-primary"><?= number_format($selectedMemberStats['totalHours'], 1) ?></h3>
                                <small class="text-muted">
                                    <?= number_format($selectedMemberStats['billableHours'], 1) ?> billable
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Tasks Assigned</h6>
                                <h3 class="mb-0 text-success"><?= $selectedMemberStats['tasksAssigned'] ?></h3>
                                <small class="text-muted">
                                    <?= $selectedMemberStats['tasksCompleted'] ?> completed
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card border-info">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Value Generated</h6>
                                <h3 class="mb-0 text-info"><?= Utility::formatToCurrency($selectedMemberStats['totalValue']) ?></h3>
                                <small class="text-muted">
                                    KES <?= number_format($selectedMemberStats['efficiency'], 2) ?>/hr
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card border-warning">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Completion Rate</h6>
                                <h3 class="mb-0 text-warning"><?= number_format($selectedMemberStats['completionRate'], 1) ?>%</h3>
                                <small class="text-muted">
                                    <?= $selectedMemberStats['tasksInProgress'] ?> in progress
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row g-3 mb-4">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="ri-line-chart-line me-2"></i>Daily Hours Trend
                                </h6>
                            </div>
                            <div class="card-body">
                                <canvas id="memberDailyHoursChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="ri-pie-chart-line me-2"></i>Hours by Phase
                                </h6>
                            </div>
                            <div class="card-body">
                                <canvas id="memberPhaseChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Breakdown Tables -->
                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="ri-folder-chart-line me-2"></i>Phase Breakdown
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($selectedMemberStats['phaseBreakdown'])): ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Phase</th>
                                                    <th class="text-end">Hours</th>
                                                    <th class="text-end">Percentage</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $phaseTotal = array_sum($selectedMemberStats['phaseBreakdown']);
                                                foreach ($selectedMemberStats['phaseBreakdown'] as $phaseName => $hours):
                                                    $percentage = $phaseTotal > 0 ? (($hours / $phaseTotal) * 100) : 0;
                                                ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($phaseName) ?></td>
                                                        <td class="text-end"><?= number_format($hours, 1) ?></td>
                                                        <td class="text-end">
                                                            <div class="d-flex align-items-center justify-content-end">
                                                                <div class="progress me-2" style="width: 60px; height: 8px;">
                                                                    <div class="progress-bar bg-primary" style="width: <?= $percentage ?>%"></div>
                                                                </div>
                                                                <span><?= number_format($percentage, 1) ?>%</span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted text-center py-3">No phase data available</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="ri-briefcase-line me-2"></i>Work Type Breakdown
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($selectedMemberStats['workTypeBreakdown'])): ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Work Type</th>
                                                    <th class="text-end">Hours</th>
                                                    <th class="text-end">Percentage</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $workTypeTotal = array_sum($selectedMemberStats['workTypeBreakdown']);
                                                foreach ($selectedMemberStats['workTypeBreakdown'] as $workTypeName => $hours):
                                                    $percentage = $workTypeTotal > 0 ? (($hours / $workTypeTotal) * 100) : 0;
                                                ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($workTypeName) ?></td>
                                                        <td class="text-end"><?= number_format($hours, 1) ?></td>
                                                        <td class="text-end">
                                                            <div class="d-flex align-items-center justify-content-end">
                                                                <div class="progress me-2" style="width: 60px; height: 8px;">
                                                                    <div class="progress-bar bg-success" style="width: <?= $percentage ?>%"></div>
                                                                </div>
                                                                <span><?= number_format($percentage, 1) ?>%</span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted text-center py-3">No work type data available</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <?php if (!empty($selectedMemberStats['recentActivity'])): ?>
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="ri-history-line me-2"></i>Recent Activity
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Task</th>
                                        <th>Phase</th>
                                        <th class="text-end">Hours</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_reverse($selectedMemberStats['recentActivity']) as $log):
                                        $hours = 0;
                                        if (isset($log->workHours) && $log->workHours > 0) {
                                            $hours = floatval($log->workHours);
                                        } elseif (isset($log->taskDuration) && !empty($log->taskDuration)) {
                                            $durationSeconds = Utility::time_to_seconds($log->taskDuration);
                                            if ($durationSeconds !== false) {
                                                $hours = $durationSeconds / 3600;
                                            }
                                        }
                                    ?>
                                        <tr>
                                            <td>
                                                <?= isset($log->taskDate) ? date('M d, Y', strtotime($log->taskDate)) : 'N/A' ?>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($log->projectTaskName ?? 'Unknown Task') ?>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($log->projectPhaseName ?? 'No Phase') ?>
                                            </td>
                                            <td class="text-end">
                                                <strong><?= number_format($hours, 1) ?></strong>
                                            </td>
                                            <td>
                                                <?php
                                                $isBillable = false;
                                                if (isset($projectDetails->billable) && ($projectDetails->billable == 'Y' || $projectDetails->billable == 'y')) {
                                                    $isBillable = true;
                                                } elseif (isset($log->billable) && ($log->billable == 'Y' || $log->billable == 'y')) {
                                                    $isBillable = true;
                                                }
                                                ?>
                                                <span class="badge <?= $isBillable ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= $isBillable ? 'Billable' : 'Non-Billable' ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Team Management Modal -->
<?php
echo Utility::form_modal_header(
    "manage_project_team",
    "projects/manage_project_team.php",
    "Manage Project Team Member",
    array('modal-lg', 'modal-dialog-centered'),
    $base
);
include 'includes/scripts/projects/modals/manage_project_team.php';
echo Utility::form_modal_footer("Save Team Member", "manage_project_team_btn", 'btn btn-primary btn-sm');
?>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
(function() {
    'use strict';

    // View member report function
    window.viewMemberReport = function(memberID) {
        const url = new URL(window.location);
        url.searchParams.set('memberID', memberID);
        window.location.href = url.toString();
    };

    // Close member report function
    window.closeMemberReport = function() {
        const url = new URL(window.location);
        url.searchParams.delete('memberID');
        window.location.href = url.toString();
    };

    // Initialize member report charts
    <?php if ($selectedMemberStats): ?>
    const memberStats = <?= json_encode($selectedMemberStats) ?>;

    // Daily Hours Chart
    const dailyHoursData = memberStats.dailyHours || {};
    const dailyLabels = Object.keys(dailyHoursData).sort();
    const dailyHours = dailyLabels.map(date => dailyHoursData[date] || 0);

    if (dailyLabels.length > 0) {
        const dailyHoursCtx = document.getElementById('memberDailyHoursChart');
        if (dailyHoursCtx) {
            new Chart(dailyHoursCtx, {
                type: 'line',
                data: {
                    labels: dailyLabels,
                    datasets: [{
                        label: 'Hours Logged',
                        data: dailyHours,
                        borderColor: 'rgb(13, 110, 253)',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y.toFixed(1) + ' hours';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value + 'h';
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    // Phase Breakdown Chart
    const phaseData = memberStats.phaseBreakdown || {};
    const phaseLabels = Object.keys(phaseData);
    const phaseHours = Object.values(phaseData);

    if (phaseLabels.length > 0) {
        const phaseCtx = document.getElementById('memberPhaseChart');
        if (phaseCtx) {
            new Chart(phaseCtx, {
                type: 'doughnut',
                data: {
                    labels: phaseLabels,
                    datasets: [{
                        data: phaseHours,
                        backgroundColor: [
                            'rgba(13, 110, 253, 0.8)',
                            'rgba(25, 135, 84, 0.8)',
                            'rgba(255, 193, 7, 0.8)',
                            'rgba(220, 53, 69, 0.8)',
                            'rgba(13, 202, 240, 0.8)',
                            'rgba(108, 117, 125, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return `${label}: ${value.toFixed(1)}h (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
    }
    <?php endif; ?>

    // Initialize modal data population
    document.querySelectorAll('.addProjectTeam, [data-bs-target="#manage_project_team"]').forEach(button => {
        button.addEventListener('click', function() {
            const form = document.getElementById('manage_project_team_form');
            if (!form) return;

            const data = this.dataset;
            const fieldMappings = {
                'projectTeamMemberID': 'projectTeamMemberId',
                'projectID': 'projectId',
                'userID': 'userId',
                'projectTeamRoleID': 'projectTeamRoleId'
            };

            for (const [fieldName, dataAttribute] of Object.entries(fieldMappings)) {
                const input = form.querySelector(`[name="${fieldName}"]`);
                if (input && data[dataAttribute]) {
                    input.value = data[dataAttribute];
                }
            }

            const selectElements = form.querySelectorAll('select');
            selectElements.forEach(select => {
                const dataValue = data[select.name.toLowerCase().replace('id', 'Id')] ||
                                data[select.name] ||
                                data[select.id];

                if (dataValue) {
                    const option = select.querySelector(`option[value="${dataValue}"]`);
                    if (option) {
                        option.selected = true;
                        select.dispatchEvent(new Event('change'));
                    }
                }
            });

            if (!data.projectTeamMemberId) {
                form.reset();
                const projectIDInput = form.querySelector('[name="projectID"]');
                if (projectIDInput) {
                    projectIDInput.value = data.projectId || '<?= $projectID ?>';
                }
            }
        });
    });

    // Card hover effects
    document.querySelectorAll('.team-member-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
            this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });
})();
</script>

<style>
#projectTeamViewContainer .card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
    transition: all 0.3s ease;
}

#projectTeamViewContainer .card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

#projectTeamViewContainer .team-member-card {
    border: 2px solid transparent;
}

#projectTeamViewContainer .team-member-card:hover {
    border-color: #0d6efd;
}

#projectTeamViewContainer .avatar {
    width: 60px;
    height: 60px;
    font-size: 1.5rem;
    font-weight: 600;
}

#projectTeamViewContainer .avatar-lg {
    width: 80px;
    height: 80px;
    font-size: 2rem;
}

#projectTeamViewContainer .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

#projectTeamViewContainer .table th {
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    color: #6c757d;
    border-bottom: 2px solid #dee2e6;
}

#projectTeamViewContainer .progress {
    border-radius: 0.5rem;
}

@media (max-width: 768px) {
    #projectTeamViewContainer .avatar-lg {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
}
</style>

