<?php
/**
 * Goals Admin Dashboard
 * Global admin view with Strategy Map and performance overview
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

// Check admin permissions (would integrate with RBAC)
// if (!hasPermission('goals.view.global')) {
//     Alert::error("You do not have permission to access this page", true);
//     return;
// }

require_once 'php/classes/goal.php';
require_once 'php/classes/goalscoring.php';
require_once 'php/classes/goalhierarchy.php';

// Get global statistics
$totalGoals = $DBConn->retrieve_db_table_rows_custom(
    "SELECT COUNT(*) as count FROM tija_goals WHERE sysEndTime IS NULL AND Lapsed = 'N'",
    array()
);
$totalGoals = $totalGoals && count($totalGoals) > 0 ? (int)$totalGoals[0]->count : 0;

$activeGoals = $DBConn->retrieve_db_table_rows_custom(
    "SELECT COUNT(*) as count FROM tija_goals WHERE status = 'Active' AND sysEndTime IS NULL AND Lapsed = 'N'",
    array()
);
$activeGoals = $activeGoals && count($activeGoals) > 0 ? (int)$activeGoals[0]->count : 0;

$criticalGoals = $DBConn->retrieve_db_table_rows_custom(
    "SELECT COUNT(*) as count FROM tija_goals WHERE propriety = 'Critical' AND status = 'Active' AND sysEndTime IS NULL AND Lapsed = 'N'",
    array()
);
$criticalGoals = $criticalGoals && count($criticalGoals) > 0 ? (int)$criticalGoals[0]->count : 0;

// Get critical goal failures
$criticalFailures = $DBConn->retrieve_db_table_rows_custom(
    "SELECT g.goalUUID, g.goalTitle, g.ownerEntityID, g.ownerUserID, g.completionPercentage, g.endDate
     FROM tija_goals g
     WHERE g.propriety = 'Critical'
     AND g.status = 'Active'
     AND g.sysEndTime IS NULL
     AND g.Lapsed = 'N'
     AND (g.completionPercentage < 70 OR g.endDate < DATE_ADD(CURDATE(), INTERVAL 30 DAY))
     ORDER BY g.completionPercentage ASC, g.endDate ASC
     LIMIT 10",
    array()
);
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Goals Administration</h4>
                    <p class="text-muted mb-0">Global performance management and strategy alignment</p>
                </div>
                <div>
                    <a href="<?= "{$base}html/?s=admin&ss=goals&p=library" ?>" class="btn btn-outline-primary me-2">
                        <i class="bi bi-book me-2"></i>Goal Library
                    </a>
                    <a href="<?= "{$base}html/?s=admin&ss=goals&p=cascade" ?>" class="btn btn-outline-primary me-2">
                        <i class="bi bi-diagram-3 me-2"></i>Cascade Goals
                    </a>
                    <a href="<?= "{$base}html/?s=admin&ss=goals&p=reports" ?>" class="btn btn-primary">
                        <i class="bi bi-graph-up me-2"></i>Reports
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Goals</h6>
                            <h3 class="mb-0"><?php echo $totalGoals; ?></h3>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-bullseye fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Active Goals</h6>
                            <h3 class="mb-0"><?php echo $activeGoals; ?></h3>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-check-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Critical Goals</h6>
                            <h3 class="mb-0"><?php echo $criticalGoals; ?></h3>
                        </div>
                        <div class="text-danger">
                            <i class="bi bi-exclamation-triangle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">At Risk</h6>
                            <h3 class="mb-0"><?php echo count($criticalFailures ? $criticalFailures : array()); ?></h3>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-exclamation-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Critical Goal Failures Alert -->
    <?php if ($criticalFailures && count($criticalFailures) > 0): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Critical Goal Failures</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Goal</th>
                                    <th>Completion</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($criticalFailures as $failure): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars(is_object($failure) ? $failure->goalTitle : $failure['goalTitle']); ?></strong></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-danger"
                                                 style="width: <?php echo is_object($failure) ? $failure->completionPercentage : $failure['completionPercentage']; ?>%">
                                                <?php echo number_format(is_object($failure) ? $failure->completionPercentage : $failure['completionPercentage'], 1); ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime(is_object($failure) ? $failure->endDate : $failure['endDate'])); ?></td>
                                    <td>
                                        <?php
                                        $endDate = is_object($failure) ? $failure->endDate : $failure['endDate'];
                                        $daysRemaining = (strtotime($endDate) - time()) / 86400;
                                        if ($daysRemaining < 0) {
                                            echo '<span class="badge bg-danger">Overdue</span>';
                                        } elseif ($daysRemaining < 30) {
                                            echo '<span class="badge bg-warning">Due Soon</span>';
                                        } else {
                                            echo '<span class="badge bg-info">At Risk</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <a href="<?= "{$base}html/?s=admin&ss=goals&p=goal_detail&goalUUID=" . (is_object($failure) ? $failure->goalUUID : $failure['goalUUID']) ?>"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
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

    <!-- Strategy Map Placeholder -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Strategy Map</h5>
                    <small class="text-muted">Visual representation of goal cascading from global to local</small>
                </div>
                <div class="card-body">
                    <div id="strategyMap" style="min-height: 400px; background: #f8f9fa; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                        <div class="text-center text-muted">
                            <i class="bi bi-diagram-3 fs-1 d-block mb-3"></i>
                            <p>Strategy Map visualization will be displayed here</p>
                            <p class="small">Integration with D3.js or ApexCharts for interactive hierarchy visualization</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-book fs-1 text-primary mb-3"></i>
                    <h5>Goal Library</h5>
                    <p class="text-muted">Manage goal templates and taxonomy</p>
                    <a href="<?= "{$base}html/?s=admin&ss=goals&p=library" ?>" class="btn btn-primary">Manage Library</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-diagram-3 fs-1 text-success mb-3"></i>
                    <h5>Cascade Management</h5>
                    <p class="text-muted">Cascade goals across organization</p>
                    <a href="<?= "{$base}html/?s=admin&ss=goals&p=cascade" ?>" class="btn btn-success">Cascade Goals</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-graph-up fs-1 text-info mb-3"></i>
                    <h5>Reports & Analytics</h5>
                    <p class="text-muted">View global performance reports</p>
                    <a href="<?= "{$base}html/?s=admin&ss=goals&p=reports" ?>" class="btn btn-info">View Reports</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Strategy Map visualization would be implemented here
// Using D3.js or ApexCharts for interactive hierarchy display
// This is a placeholder for future implementation
</script>

