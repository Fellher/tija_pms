<?php
/**
 * Get Billing Cycle Details
 * Returns detailed information about a billing cycle including tasks, time logs, etc.
 *
 * @package    TIJA_PMS
 * @subpackage Recurring Projects
 */

session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';

header('Content-Type: application/json');

$billingCycleID = isset($_GET['billingCycleID']) ? intval($_GET['billingCycleID']) : 0;
$projectID = isset($_GET['projectID']) ? intval($_GET['projectID']) : 0;
$action = isset($_GET['action']) ? $_GET['action'] : 'details';

try {
    if ($action === 'get_next_upcoming') {
        // Get next upcoming cycle
        if (!$projectID) {
            throw new Exception('Project ID is required');
        }

        $cycles = Projects::get_billing_cycles(
            ['projectID' => $projectID, 'status' => 'upcoming', 'Suspended' => 'N'],
            false,
            $DBConn
        );

        if ($cycles && is_array($cycles) && count($cycles) > 0) {
            // Sort by cycle number and get first
            usort($cycles, function($a, $b) {
                return ($a->cycleNumber ?? 0) - ($b->cycleNumber ?? 0);
            });

            echo json_encode([
                'success' => true,
                'billingCycleID' => $cycles[0]->billingCycleID,
                'cycleNumber' => $cycles[0]->cycleNumber
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No upcoming cycles found'
            ]);
        }
        exit;
    }

    if (!$billingCycleID) {
        throw new Exception('Billing cycle ID is required');
    }

    // Get cycle details
    $cycle = Projects::get_billing_cycles(['billingCycleID' => $billingCycleID], true, $DBConn);

    if (!$cycle) {
        throw new Exception('Billing cycle not found');
    }

    // Get project details
    if (!$projectID) {
        $projectID = $cycle->projectID;
    }
    $project = Projects::projects_mini(['projectID' => $projectID], true, $DBConn);

    // Get phases for this cycle
    $phases = Projects::project_phases_mini(
        ['projectID' => $projectID, 'billingCycleID' => $billingCycleID, 'Suspended' => 'N'],
        false,
        $DBConn
    );

    // Get tasks for this cycle
    $tasks = Projects::project_tasks(
        ['projectID' => $projectID, 'billingCycleID' => $billingCycleID, 'Suspended' => 'N'],
        false,
        $DBConn
    );

    // Get time logs for this cycle
    $timeLogs = Projects::get_cycle_time_logs($billingCycleID, $DBConn);

    // Calculate billing summary
    $billingSummary = Projects::calculate_cycle_billing($billingCycleID, $DBConn);

    // Build HTML response
    ob_start();
    ?>
    <div class="billing-cycle-details">
        <!-- Cycle Information -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h6 class="text-muted mb-2">Cycle Information</h6>
                <table class="table table-sm table-borderless">
                    <tr>
                        <td><strong>Cycle Number:</strong></td>
                        <td>#<?= $cycle->cycleNumber ?></td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            <?php
                            $statusColors = [
                                'upcoming' => 'secondary',
                                'active' => 'primary',
                                'billing_due' => 'warning',
                                'invoiced' => 'info',
                                'paid' => 'success',
                                'overdue' => 'danger',
                                'cancelled' => 'dark'
                            ];
                            $statusColor = $statusColors[$cycle->status] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $statusColor ?>">
                                <?= ucfirst(str_replace('_', ' ', $cycle->status)) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Start Date:</strong></td>
                        <td><?= date('d M Y', strtotime($cycle->cycleStartDate)) ?></td>
                    </tr>
                    <tr>
                        <td><strong>End Date:</strong></td>
                        <td><?= date('d M Y', strtotime($cycle->cycleEndDate)) ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted mb-2">Billing Information</h6>
                <table class="table table-sm table-borderless">
                    <tr>
                        <td><strong>Billing Date:</strong></td>
                        <td><?= date('d M Y', strtotime($cycle->billingDate)) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Due Date:</strong></td>
                        <td><?= date('d M Y', strtotime($cycle->dueDate)) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Amount:</strong></td>
                        <td><strong>KES <?= number_format($cycle->amount, 2) ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong>Hours Logged:</strong></td>
                        <td><?= number_format($cycle->hoursLogged, 2) ?> hrs</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Billing Summary -->
        <?php if ($billingSummary): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Billing Summary</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-primary"><?= number_format($billingSummary['totalHours'], 2) ?></h4>
                            <p class="text-muted mb-0 small">Total Hours</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-success"><?= number_format($billingSummary['billableHours'], 2) ?></h4>
                            <p class="text-muted mb-0 small">Billable Hours</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-info">KES <?= number_format($billingSummary['calculatedAmount'], 2) ?></h4>
                            <p class="text-muted mb-0 small">Calculated Amount</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-warning">KES <?= number_format($billingSummary['cycleAmount'], 2) ?></h4>
                            <p class="text-muted mb-0 small">Cycle Amount</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Phases -->
        <?php if ($phases && is_array($phases) && count($phases) > 0): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Phases (<?= count($phases) ?>)</h6>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <?php foreach ($phases as $phase): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars($phase->projectPhaseName) ?></h6>
                                    <small class="text-muted">
                                        <?= date('d M', strtotime($phase->phaseStartDate)) ?> -
                                        <?= date('d M Y', strtotime($phase->phaseEndDate)) ?>
                                    </small>
                                </div>
                                <?php if (isset($phase->phaseWorkHrs) && $phase->phaseWorkHrs > 0): ?>
                                    <span class="badge bg-info"><?= number_format($phase->phaseWorkHrs, 2) ?> hrs</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tasks -->
        <?php if ($tasks && is_array($tasks) && count($tasks) > 0): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Tasks (<?= count($tasks) ?>)</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Start Date</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $task): ?>
                                <tr>
                                    <td><?= htmlspecialchars($task->projectTaskName) ?></td>
                                    <td><?= date('d M Y', strtotime($task->taskStart)) ?></td>
                                    <td><?= date('d M Y', strtotime($task->taskDeadline)) ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($task->status ?? 'active') ?></span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar" role="progressbar"
                                                 style="width: <?= $task->progress ?? 0 ?>%"
                                                 aria-valuenow="<?= $task->progress ?? 0 ?>"
                                                 aria-valuemin="0"
                                                 aria-valuemax="100">
                                                <?= $task->progress ?? 0 ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Time Logs -->
        <?php if ($timeLogs && is_array($timeLogs) && count($timeLogs) > 0): ?>
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Time Logs (<?= count($timeLogs) ?>)</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Employee</th>
                                <th>Task</th>
                                <th>Duration</th>
                                <th>Hours</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($timeLogs as $log): ?>
                                <tr>
                                    <td><?= date('d M Y', strtotime($log->taskDate ?? $log->DateAdded)) ?></td>
                                    <td>
                                        <?php
                                        if (isset($log->employeeID)) {
                                            $employee = Employee::employees(['ID' => $log->employeeID], true, $DBConn);
                                            echo htmlspecialchars($employee ? ($employee->FirstName . ' ' . $employee->Surname) : 'N/A');
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td><?= htmlspecialchars($log->projectTaskName ?? $log->taskNarrative ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($log->taskDuration ?? 'N/A') ?></td>
                                    <td>
                                        <?php
                                        if (isset($log->workHours)) {
                                            echo number_format($log->workHours, 2);
                                        } elseif (isset($log->taskDuration)) {
                                            // Parse duration if needed
                                            echo htmlspecialchars($log->taskDuration);
                                        } else {
                                            echo 'N/A';
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
        <?php endif; ?>
    </div>
    <?php
    $html = ob_get_clean();

    echo json_encode([
        'success' => true,
        'html' => $html,
        'cycle' => [
            'billingCycleID' => $cycle->billingCycleID,
            'cycleNumber' => $cycle->cycleNumber,
            'status' => $cycle->status,
            'amount' => $cycle->amount,
            'hoursLogged' => $cycle->hoursLogged
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

