<?php
/**
 * Plan Instance Customization UI for Recurring Projects
 *
 * Allows editing project plans per billing cycle
 *
 * @package    TIJA_PMS
 * @subpackage Recurring Projects
 */

$billingCycleID = isset($_GET['cycleID']) ? intval($_GET['cycleID']) : 0;
$projectID = isset($_GET['pid']) ? intval($_GET['pid']) : 0;

if (!$billingCycleID || !$projectID) {
    echo '<div class="alert alert-danger">Invalid parameters</div>';
    exit;
}

// Get billing cycle
$billingCycle = Projects::get_billing_cycles(['billingCycleID' => $billingCycleID], true, $DBConn);
if (!$billingCycle || $billingCycle->projectID != $projectID) {
    echo '<div class="alert alert-danger">Invalid billing cycle</div>';
    exit;
}

// Get project
$project = Projects::projects_mini(['projectID' => $projectID], true, $DBConn);
if (!$project) {
    echo '<div class="alert alert-danger">Project not found</div>';
    exit;
}

// Get plan instance
$planInstance = $DBConn->retrieve_db_table_rows(
    'tija_recurring_project_plan_instances',
    ['planInstanceID', 'phaseJSON', 'isCustomized'],
    ['billingCycleID' => $billingCycleID, 'projectID' => $projectID]
);

$planData = null;
$isCustomized = false;

if ($planInstance && is_array($planInstance) && count($planInstance) > 0) {
    $instance = $planInstance[0];
    $planData = json_decode($instance->phaseJSON, true);
    $isCustomized = $instance->isCustomized == 'Y';
} else {
    // Load base plan
    $phases = Projects::project_phases(['projectID' => $projectID, 'Suspended' => 'N'], false, $DBConn);
    if ($phases && is_array($phases)) {
        $planData = [];
        foreach ($phases as $phase) {
            $tasks = Projects::project_tasks(['projectPhaseID' => $phase->projectPhaseID, 'Suspended' => 'N'], false, $DBConn);
            $planData[] = [
                'phaseID' => $phase->projectPhaseID,
                'phaseName' => $phase->projectPhaseName,
                'tasks' => $tasks ? array_map(function($t) {
                    return ['taskID' => $t->projectTaskID, 'taskName' => $t->projectTaskName];
                }, $tasks) : []
            ];
        }
    }
}
?>

<div class="card custom-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="ri-task-line me-2"></i>Plan Customization - Cycle #<?= $billingCycle->cycleNumber ?>
        </h5>
        <div>
            <?php if ($isCustomized): ?>
                <span class="badge bg-warning">Customized</span>
            <?php else: ?>
                <span class="badge bg-info">Base Plan</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="ri-information-line me-2"></i>
            <strong>Cycle Period:</strong> <?= date('d M Y', strtotime($billingCycle->cycleStartDate)) ?> -
            <?= date('d M Y', strtotime($billingCycle->cycleEndDate)) ?>
        </div>

        <div class="mb-3">
            <button type="button" class="btn btn-sm btn-primary" onclick="copyFromBasePlan()">
                <i class="ri-file-copy-line me-1"></i>Copy from Base Plan
            </button>
            <?php if ($isCustomized): ?>
                <button type="button" class="btn btn-sm btn-secondary" onclick="revertToBase()">
                    <i class="ri-arrow-go-back-line me-1"></i>Revert to Base Plan
                </button>
            <?php endif; ?>
        </div>

        <div id="planEditor">
            <?php if ($planData && is_array($planData)): ?>
                <?php foreach ($planData as $phase): ?>
                    <div class="card mb-3 phase-item" data-phase-id="<?= $phase['phaseID'] ?? '' ?>">
                        <div class="card-header">
                            <h6 class="mb-0"><?= htmlspecialchars($phase['phaseName'] ?? 'Unnamed Phase') ?></h6>
                        </div>
                        <div class="card-body">
                            <?php if (isset($phase['tasks']) && is_array($phase['tasks'])): ?>
                                <ul class="list-group">
                                    <?php foreach ($phase['tasks'] as $task): ?>
                                        <li class="list-group-item">
                                            <?= htmlspecialchars($task['taskName'] ?? 'Unnamed Task') ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-muted">No tasks in this phase</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning">No plan data available</div>
            <?php endif; ?>
        </div>

        <div class="mt-3">
            <button type="button" class="btn btn-success" onclick="saveCustomizedPlan()">
                <i class="ri-save-line me-1"></i>Save Customized Plan
            </button>
        </div>
    </div>
</div>

<script>
function copyFromBasePlan() {
    if (confirm('This will replace the current plan with the base project plan. Continue?')) {
        fetch('<?= $base ?>php/scripts/recurring_projects/manage_plan_instances.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                action: 'copy_base_plan',
                billingCycleID: <?= $billingCycleID ?>,
                projectID: <?= $projectID ?>
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Plan copied successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }
}

function revertToBase() {
    if (confirm('This will delete the customized plan and revert to the base plan. Continue?')) {
        fetch('<?= $base ?>php/scripts/recurring_projects/manage_plan_instances.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                action: 'revert_to_base',
                billingCycleID: <?= $billingCycleID ?>,
                projectID: <?= $projectID ?>
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Plan reverted successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }
}

function saveCustomizedPlan() {
    // TODO: Implement plan editing UI and save functionality
    alert('Plan customization editor - Coming soon');
}
</script>

