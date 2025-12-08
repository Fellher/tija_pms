<?php
/**
 * Admin Goal Detail Page
 * Read-only view of a cascaded/any goal for administrators
 *
 * @package    TIJA_PMS
 * @subpackage Goals
 * @version    1.0.0
 */

// Security check
if(!$isValidUser) {
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}

// Admin / privileged access
if (!isset($isAdmin) || !$isAdmin) {
    if (!isset($isValidAdmin) || !$isValidAdmin) {
        Alert::error("Access denied. Administrator privileges required.", true);
        return;
    }
}

require_once 'php/classes/goal.php';
require_once 'php/classes/goalhierarchy.php';

$goalUUID = isset($_GET['goalUUID']) ? Utility::clean_string($_GET['goalUUID']) : '';

if (empty($goalUUID)) {
    Alert::error("Goal UUID is required", true);
    return;
}

$goal = Goal::getGoal($goalUUID, $DBConn);
if (!$goal) {
    Alert::error("Goal not found", true);
    return;
}
var_dump($goal);
// For admin view we allow reading any goal; no additional owner check

// Get cascade path
$cascadePath = GoalHierarchy::getCascadePath($goalUUID, $DBConn);
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0"><?php echo htmlspecialchars($goal->goalTitle ?? 'Goal Detail'); ?></h4>
                    <p class="text-muted mb-0">
                        <span class="badge bg-info"><?php echo htmlspecialchars($goal->goalType ?? ''); ?></span>
                        <?php if (($goal->propriety ?? '') === 'Critical'): ?>
                            <span class="badge bg-danger ms-2">Critical</span>
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <a href="<?= "{$base}html/?s=admin&ss=goals&p=cascade" ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Cascade
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Goal Information -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Goal Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Description:</strong>
                        <p><?php echo htmlspecialchars($goal->goalDescription ?? 'No description provided'); ?></p>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Status:</strong>
                            <span class="badge bg-<?php
                                $status = $goal->status ?? 'Draft';
                                echo $status === 'Active' ? 'success' :
                                    ($status === 'Completed' ? 'primary' : 'secondary');
                            ?>"><?php echo htmlspecialchars($status); ?></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Weight:</strong>
                            <?php echo number_format((float)($goal->weight ?? 0) * 100, 2); ?>%
                        </div>
                        <div class="col-md-6 mt-2">
                            <strong>Start Date:</strong>
                            <?php echo !empty($goal->startDate) ? date('M d, Y', strtotime($goal->startDate)) : 'N/A'; ?>
                        </div>
                        <div class="col-md-6 mt-2">
                            <strong>End Date:</strong>
                            <?php echo !empty($goal->endDate) ? date('M d, Y', strtotime($goal->endDate)) : 'N/A'; ?>
                        </div>
                    </div>

                    <!-- Progress -->
                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-2">
                            <strong>Progress</strong>
                            <span><?php echo number_format($goal->completionPercentage ?? 0, 1); ?>%</span>
                        </div>
                        <div class="progress" style="height: 30px;">
                            <div class="progress-bar"
                                 style="width: <?php echo $goal->completionPercentage ?? 0; ?>%"
                                 role="progressbar">
                                <?php echo number_format($goal->completionPercentage ?? 0, 1); ?>%
                            </div>
                        </div>
                    </div>

                    <!-- Type-specific data -->
                    <?php if (($goal->goalType ?? '') === 'OKR' && isset($goal->okrData)): ?>
                        <div class="mt-4">
                            <h6>Objective</h6>
                            <p><?php echo htmlspecialchars($goal->okrData->objective ?? ''); ?></p>
                            <h6>Key Results</h6>
                            <ul>
                                <?php if (isset($goal->okrData->keyResults) && is_array($goal->okrData->keyResults)): ?>
                                    <?php foreach ($goal->okrData->keyResults as $kr): ?>
                                        <li><?php echo htmlspecialchars($kr['kr'] ?? ''); ?> -
                                            Target: <?php echo $kr['target'] ?? ''; ?>
                                            <?php echo $kr['unit'] ?? ''; ?>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    <?php elseif (($goal->goalType ?? '') === 'KPI' && isset($goal->kpiData)): ?>
                        <div class="mt-4">
                            <h6>KPI Details</h6>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($goal->kpiData->kpiName ?? ''); ?></p>
                            <p><strong>Target:</strong> <?php echo number_format($goal->kpiData->targetValue ?? 0, 2); ?>
                                <?php echo htmlspecialchars($goal->kpiData->unit ?? ''); ?></p>
                            <p><strong>Current:</strong> <?php echo number_format($goal->kpiData->currentValue ?? 0, 2); ?>
                                <?php echo htmlspecialchars($goal->kpiData->unit ?? ''); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Cascade Path -->
            <?php if ($cascadePath && count($cascadePath) > 1): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Alignment Path</h5>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <?php foreach ($cascadePath as $pathGoal): ?>
                            <li class="<?php echo $pathGoal['goalUUID'] === $goalUUID ? 'fw-bold' : ''; ?>">
                                <?php echo htmlspecialchars($pathGoal['goalTitle']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>


