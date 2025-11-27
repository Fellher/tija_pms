<?php
/**
 * Modern Project Dashboard Component
 * Enterprise-grade project overview with cards-based layout
 *
 * @package Tija Practice Management System
 * @subpackage Projects
 * @version 2.0
 * @since November 2025
 */

// Safety check - ensure $projectDetails exists
if (!isset($projectDetails) || !$projectDetails) {
    echo '<div class="alert alert-warning">Project details not loaded. Please ensure $projectDetails is set before including this component.</div>';
    return;
}

// Calculate task metrics from actual task data
$taskTotal = 0;
$taskCompleted = 0;
$taskInProgress = 0;
$taskPending = 0;
$taskOverdue = 0;

if (isset($projectTasks) && is_array($projectTasks)) {
    $taskTotal = count($projectTasks);
    $now = time();

    foreach ($projectTasks as $task) {
        $status = strtolower($task->status ?? $task->taskStatus ?? 'pending');

        if (in_array($status, ['completed', 'done', 'closed'])) {
            $taskCompleted++;
        } elseif (in_array($status, ['in_progress', 'active', 'working'])) {
            $taskInProgress++;
        } else {
            $taskPending++;
        }

        // Check if overdue
        if (isset($task->taskDeadline) && $task->taskDeadline) {
            if (strtotime($task->taskDeadline) < $now && !in_array($status, ['completed', 'done', 'closed'])) {
                $taskOverdue++;
            }
        }
    }
}

// Calculate team metrics
$teamTotal = 0;
$teamActive = 0;

if (isset($teamMembers) && is_array($teamMembers)) {
    $teamTotal = count($teamMembers);
    foreach ($teamMembers as $member) {
        if (($member->isActive ?? 'Y') == 'Y') {
            $teamActive++;
        }
    }
}

// Calculate project metrics
$projectMetrics = [
    'budget' => [
        'total' => $projectDetails->projectValue ?? 0,
        'spent' => 0, // Will be calculated from expenses
        'remaining' => $projectDetails->projectValue ?? 0,
        'percentage' => 0
    ],
    'tasks' => [
        'total' => $taskTotal,
        'completed' => $taskCompleted,
        'inProgress' => $taskInProgress,
        'pending' => $taskPending,
        'overdue' => $taskOverdue,
        'percentage' => $taskTotal > 0 ? round(($taskCompleted / $taskTotal) * 100, 1) : 0
    ],
    'team' => [
        'total' => $teamTotal,
        'active' => $teamActive,
        'utilization' => $teamTotal > 0 ? round(($teamActive / $teamTotal) * 100, 1) : 0
    ],
    'timeline' => [
        'start' => $projectDetails->projectStart ?? null,
        'end' => $projectDetails->projectClose ?? $projectDetails->projectDeadline ?? null,
        'daysElapsed' => 0,
        'daysRemaining' => 0,
        'status' => 'on_track'
    ]
];

// Calculate budget spent (from expenses if available)
if (isset($projectExpenses) && is_array($projectExpenses)) {
    $totalExpenses = 0;
    foreach ($projectExpenses as $expense) {
        if (($expense->status ?? 'pending') == 'approved') {
            $totalExpenses += ($expense->amount ?? 0);
        }
    }
    $projectMetrics['budget']['spent'] = $totalExpenses;
    $projectMetrics['budget']['remaining'] = ($projectMetrics['budget']['total'] - $totalExpenses);

    if ($projectMetrics['budget']['total'] > 0) {
        $projectMetrics['budget']['percentage'] = round(($totalExpenses / $projectMetrics['budget']['total']) * 100, 1);
    }
}

// Calculate timeline metrics
if ($projectMetrics['timeline']['start'] && $projectMetrics['timeline']['end']) {
    $start = new DateTime($projectMetrics['timeline']['start']);
    $end = new DateTime($projectMetrics['timeline']['end']);
    $now = new DateTime();

    $totalDays = $start->diff($end)->days;
    $elapsedDays = $start->diff($now)->days;
    $remainingDays = $now->diff($end)->days;

    $projectMetrics['timeline']['daysElapsed'] = $elapsedDays;
    $projectMetrics['timeline']['daysRemaining'] = $remainingDays;
    $projectMetrics['timeline']['totalDays'] = $totalDays;
    $projectMetrics['timeline']['percentage'] = $totalDays > 0 ? round(($elapsedDays / $totalDays) * 100, 1) : 0;

    // Determine status
    if ($now > $end && $projectMetrics['tasks']['percentage'] < 100) {
        $projectMetrics['timeline']['status'] = 'delayed';
    } elseif ($projectMetrics['timeline']['percentage'] > $projectMetrics['tasks']['percentage'] + 10) {
        $projectMetrics['timeline']['status'] = 'at_risk';
    } else {
        $projectMetrics['timeline']['status'] = 'on_track';
    }
}

$statusBadgeClass = [
    'on_track' => 'success',
    'at_risk' => 'warning',
    'delayed' => 'danger'
];
?>

<!-- Project Header -->
<div class="card custom-card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1">
                <div class="d-flex align-items-center mb-2">
                    <h3 class="mb-0 me-3"><?= htmlspecialchars($projectDetails->projectName ?? 'Untitled Project') ?></h3>
                    <span class="badge bg-<?= $statusBadgeClass[$projectMetrics['timeline']['status']] ?>-transparent">
                        <?= ucwords(str_replace('_', ' ', $projectMetrics['timeline']['status'])) ?>
                    </span>
                </div>
                <p class="text-muted mb-3"><?= htmlspecialchars($projectDetails->projectDescription ?? 'No description') ?></p>

                <!-- Project Info Row -->
                <div class="d-flex flex-wrap gap-3">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user-tie text-primary me-2"></i>
                        <small><strong>Client:</strong> <?= htmlspecialchars($projectDetails->clientName ?? 'N/A') ?></small>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user-circle text-success me-2"></i>
                        <small><strong>Manager:</strong> <?= htmlspecialchars($projectDetails->projectManagerName ?? 'N/A') ?></small>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar text-info me-2"></i>
                        <small><strong>Duration:</strong>
                            <?= $projectMetrics['timeline']['start'] ? date('M d, Y', strtotime($projectMetrics['timeline']['start'])) : 'N/A' ?> -
                            <?= $projectMetrics['timeline']['end'] ? date('M d, Y', strtotime($projectMetrics['timeline']['end'])) : 'N/A' ?>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary btn-sm" id="toggleDashboardSections" title="Show/Hide Dashboard Sections">
                    <i class="fas fa-chevron-down" id="toggleDashboardIcon"></i>
                </button>
                <button type="button" class="btn btn-primary btn-sm" title="Edit Project">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-info btn-sm" title="Export">
                    <i class="fas fa-download"></i>
                </button>
                <button type="button" class="btn btn-secondary btn-sm" title="Settings">
                    <i class="fas fa-cog"></i>
                </button>
                <div class="dropdown">
                    <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-archive me-2"></i>Archive Project</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-copy me-2"></i>Duplicate Project</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-trash me-2"></i>Delete Project</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Overall Progress Bar -->
        <div class="mt-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <small class="text-muted">Overall Progress</small>
                <small class="text-muted"><strong><?= $projectMetrics['tasks']['percentage'] ?>%</strong> Complete</small>
            </div>
            <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-success" role="progressbar"
                    style="width: <?= $projectMetrics['tasks']['percentage'] ?>%"
                    aria-valuenow="<?= $projectMetrics['tasks']['percentage'] ?>"
                    aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-collapsible-section" id="keyMetricsSection" style="display: none;">
<!-- Key Metrics Cards -->
    <div class="row mb-4">
        <!-- Budget Card -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card custom-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small">Budget</p>
                            <h4 class="mb-0"><?= number_format($projectMetrics['budget']['total'], 0) ?></h4>
                            <small class="text-muted"><?= $projectDetails->currency ?? 'KES' ?></small>
                        </div>
                        <div class="avatar avatar-md bg-primary-transparent">
                            <i class="fas fa-coins fs-20"></i>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-success">Spent: <?= number_format($projectMetrics['budget']['spent'], 0) ?></small>
                        <small class="text-info">Remaining: <?= number_format($projectMetrics['budget']['remaining'], 0) ?></small>
                    </div>

                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-<?= $projectMetrics['budget']['percentage'] > 90 ? 'danger' : ($projectMetrics['budget']['percentage'] > 75 ? 'warning' : 'success') ?>"
                            style="width: <?= min($projectMetrics['budget']['percentage'], 100) ?>%"></div>
                    </div>
                    <small class="text-muted mt-1 d-block"><?= $projectMetrics['budget']['percentage'] ?>% utilized</small>
                </div>
            </div>
        </div>

        <!-- Tasks Card -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card custom-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small">Tasks</p>
                            <h4 class="mb-0"><?= $projectMetrics['tasks']['completed'] ?>/<?= $projectMetrics['tasks']['total'] ?></h4>
                            <small class="text-muted">Completed</small>
                        </div>
                        <div class="avatar avatar-md bg-success-transparent">
                            <i class="fas fa-tasks fs-20"></i>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-warning-transparent"><?= $projectMetrics['tasks']['inProgress'] ?> In Progress</span>
                        <?php if ($projectMetrics['tasks']['overdue'] > 0): ?>
                            <span class="badge bg-danger-transparent"><?= $projectMetrics['tasks']['overdue'] ?> Overdue</span>
                        <?php endif; ?>
                    </div>

                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: <?= $projectMetrics['tasks']['percentage'] ?>%"></div>
                    </div>
                    <small class="text-muted mt-1 d-block"><?= $projectMetrics['tasks']['percentage'] ?>% complete</small>
                </div>
            </div>
        </div>

        <!-- Team Card -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card custom-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small">Team</p>
                            <h4 class="mb-0"><?= $projectMetrics['team']['active'] ?>/<?= $projectMetrics['team']['total'] ?></h4>
                            <small class="text-muted">Active Members</small>
                        </div>
                        <div class="avatar avatar-md bg-info-transparent">
                            <i class="fas fa-users fs-20"></i>
                        </div>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted">Team Utilization</small>
                    </div>

                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-info" style="width: <?= $projectMetrics['team']['utilization'] ?>%"></div>
                    </div>
                    <small class="text-muted mt-1 d-block"><?= $projectMetrics['team']['utilization'] ?>% capacity</small>
                </div>
            </div>
        </div>

        <!-- Timeline Card -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card custom-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small">Timeline</p>
                            <h4 class="mb-0"><?= $projectMetrics['timeline']['daysRemaining'] ?></h4>
                            <small class="text-muted">Days Remaining</small>
                        </div>
                        <div class="avatar avatar-md bg-warning-transparent">
                            <i class="fas fa-calendar-alt fs-20"></i>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted"><?= $projectMetrics['timeline']['daysElapsed'] ?> days elapsed</small>
                        <small class="text-muted"><?= $projectMetrics['timeline']['totalDays'] ?? 0 ?> total</small>
                    </div>

                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-<?= $statusBadgeClass[$projectMetrics['timeline']['status']] ?>"
                            style="width: <?= $projectMetrics['timeline']['percentage'] ?>%"></div>
                    </div>
                    <small class="text-muted mt-1 d-block"><?= $projectMetrics['timeline']['percentage'] ?>% time elapsed</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Insights Row -->
    <div class="row mb-4 ">
        <!-- Recent Activity -->
        <div class="col-xl-6 mb-3">
            <div class="card custom-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Activity</h6>
                    <a href="#" class="btn btn-sm btn-light">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php
                        // Sample activities - replace with real data
                        $recentActivities = [
                            ['icon' => 'check-circle', 'color' => 'success', 'text' => 'Task "Design Homepage" completed', 'user' => 'John Doe', 'time' => '2 hours ago'],
                            ['icon' => 'plus-circle', 'color' => 'primary', 'text' => 'New task "Code Review" added', 'user' => 'Jane Smith', 'time' => '4 hours ago'],
                            ['icon' => 'user-plus', 'color' => 'info', 'text' => 'Bob Wilson joined the project', 'user' => 'System', 'time' => '1 day ago'],
                        ];

                        if (!empty($recentActivities)):
                            foreach ($recentActivities as $activity):
                        ?>
                            <div class="list-group-item border-0">
                                <div class="d-flex align-items-start">
                                    <div class="avatar avatar-sm bg-<?= $activity['color'] ?>-transparent me-3">
                                        <i class="fas fa-<?= $activity['icon'] ?>"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-1"><?= $activity['text'] ?></p>
                                        <small class="text-muted">by <?= $activity['user'] ?> • <?= $activity['time'] ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php
                            endforeach;
                        else:
                        ?>
                            <div class="list-group-item text-center py-4">
                                <p class="text-muted mb-0">No recent activity</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Milestones -->
        <div class="col-xl-6 mb-3">
            <div class="card custom-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-flag me-2"></i>Upcoming Milestones</h6>
                    <a href="#" class="btn btn-sm btn-light">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php
                        // Sample milestones - replace with real data
                        $upcomingMilestones = [
                            ['name' => 'Design Phase Complete', 'date' => '2025-12-10', 'status' => 'in_progress'],
                            ['name' => 'Development Phase 1', 'date' => '2025-12-25', 'status' => 'pending'],
                            ['name' => 'Testing & QA', 'date' => '2026-01-15', 'status' => 'pending'],
                        ];

                        if (!empty($upcomingMilestones)):
                            foreach ($upcomingMilestones as $milestone):
                                $daysUntil = ceil((strtotime($milestone['date']) - time()) / 86400);
                                $statusColor = $milestone['status'] == 'completed' ? 'success' :
                                            ($milestone['status'] == 'in_progress' ? 'primary' : 'secondary');
                        ?>
                            <div class="list-group-item border-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?= $milestone['name'] ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= date('M d, Y', strtotime($milestone['date'])) ?>
                                            <span class="ms-2 badge bg-<?= $statusColor ?>-transparent">
                                                <?= $daysUntil > 0 ? $daysUntil . ' days' : 'Today' ?>
                                            </span>
                                        </small>
                                    </div>
                                    <span class="badge bg-<?= $statusColor ?>">
                                        <?= ucfirst($milestone['status']) ?>
                                    </span>
                                </div>
                            </div>
                        <?php
                            endforeach;
                        else:
                        ?>
                            <div class="list-group-item text-center py-4">
                                <p class="text-muted mb-0">No milestones defined</p>
                                <button class="btn btn-sm btn-primary mt-2">
                                    <i class="fas fa-plus me-1"></i>Add Milestone
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Task Summary by Status -->
    <div class="row mb-4 ">
        <div class="col-12">
            <div class="card custom-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Task Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <div class="mb-2">
                                <div class="avatar avatar-xl bg-secondary-transparent mx-auto">
                                    <h3 class="mb-0"><?= $projectMetrics['tasks']['pending'] ?></h3>
                                </div>
                            </div>
                            <h6 class="mb-1">To Do</h6>
                            <small class="text-muted">Pending tasks</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="mb-2">
                                <div class="avatar avatar-xl bg-warning-transparent mx-auto">
                                    <h3 class="mb-0"><?= $projectMetrics['tasks']['inProgress'] ?></h3>
                                </div>
                            </div>
                            <h6 class="mb-1">In Progress</h6>
                            <small class="text-muted">Active tasks</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="mb-2">
                                <div class="avatar avatar-xl bg-success-transparent mx-auto">
                                    <h3 class="mb-0"><?= $projectMetrics['tasks']['completed'] ?></h3>
                                </div>
                            </div>
                            <h6 class="mb-1">Done</h6>
                            <small class="text-muted">Completed tasks</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="mb-2">
                                <div class="avatar avatar-xl bg-danger-transparent mx-auto">
                                    <h3 class="mb-0"><?= $projectMetrics['tasks']['overdue'] ?></h3>
                                </div>
                            </div>
                            <h6 class="mb-1">Overdue</h6>
                            <small class="text-muted">Past due date</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.avatar-xl {
    width: 72px;
    height: 72px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
}

.avatar-md {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}

.avatar-sm {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
}

/* Dashboard Collapsible Sections */
.dashboard-collapsible-section {
    transition: all 0.3s ease-in-out;
}

.dashboard-collapsible-section.show {
    display: block !important;
    animation: slideDown 0.3s ease-in-out;
}

.dashboard-collapsible-section.hide {
    display: none !important;
    animation: slideUp 0.3s ease-in-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideUp {
    from {
        opacity: 1;
        transform: translateY(0);
    }
    to {
        opacity: 0;
        transform: translateY(-10px);
    }
}

#toggleDashboardSections {
    transition: all 0.2s ease;
}

#toggleDashboardSections.active {
    background-color: var(--bs-primary);
    color: white;
    border-color: var(--bs-primary);
}

#toggleDashboardIcon {
    transition: all 0.3s ease;
}
</style>

<script>
(function() {
    'use strict';

    // Initialize dashboard toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButton = document.getElementById('toggleDashboardSections');
        const toggleIcon = document.getElementById('toggleDashboardIcon');
        const sections = document.querySelectorAll('.dashboard-collapsible-section');

        if (!toggleButton || sections.length === 0) {
            return;
        }

        // Check localStorage for saved state
        const savedState = localStorage.getItem('projectDashboardSectionsVisible');
        const isVisible = savedState === 'true';

        // Function to update icon based on visibility state
        function updateIcon(isVisible) {
            if (isVisible) {
                toggleIcon.classList.remove('fa-chevron-down');
                toggleIcon.classList.add('fa-chevron-up');
            } else {
                toggleIcon.classList.remove('fa-chevron-up');
                toggleIcon.classList.add('fa-chevron-down');
            }
        }

        // Set initial state
        if (isVisible) {
            sections.forEach(section => {
                section.style.display = 'block';
                section.classList.add('show');
            });
            toggleButton.classList.add('active');
            updateIcon(true);
        } else {
            sections.forEach(section => {
                section.style.display = 'none';
                section.classList.remove('show');
            });
            toggleButton.classList.remove('active');
            updateIcon(false);
        }

        // Toggle button click handler
        toggleButton.addEventListener('click', function() {
            const isCurrentlyVisible = sections[0].style.display !== 'none';

            if (isCurrentlyVisible) {
                // Hide sections
                sections.forEach(section => {
                    section.classList.remove('show');
                    section.classList.add('hide');
                    setTimeout(() => {
                        section.style.display = 'none';
                        section.classList.remove('hide');
                    }, 300);
                });
                toggleButton.classList.remove('active');
                updateIcon(false);
                localStorage.setItem('projectDashboardSectionsVisible', 'false');
            } else {
                // Show sections
                sections.forEach(section => {
                    section.style.display = 'block';
                    section.classList.add('show');
                });
                toggleButton.classList.add('active');
                updateIcon(true);
                localStorage.setItem('projectDashboardSectionsVisible', 'true');
            }
        });
    });
})();
</script>

