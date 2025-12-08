<?php
/**
 * Goal Detail Page
 * Individual goal view with progress tracking and evaluation
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

require_once 'php/classes/goal.php';
require_once 'php/classes/goalevaluation.php';
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

// Check access permissions
if ($goal->ownerUserID != $userDetails->ID && $goal->ownerEntityID != $userDetails->entityID) {
    // Check if user is evaluator or manager
    $hasAccess = false;
    // Add permission check logic here
    if (!$hasAccess) {
        Alert::error("You do not have permission to view this goal", true);
        return;
    }
}

// Get cascade path
$cascadePath = GoalHierarchy::getCascadePath($goalUUID, $DBConn);

// Get evaluations
$evaluations = GoalEvaluation::getEvaluations($goalUUID, false, $DBConn);

// Get 360 feedback
$feedback360 = GoalEvaluation::get360Feedback($goalUUID, $DBConn);

// Get latest score
require_once 'php/classes/goalscoring.php';
$latestScore = GoalScoring::getLatestScore($goalUUID, $DBConn);
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0"><?php echo htmlspecialchars($goal->goalTitle); ?></h4>
                    <p class="text-muted mb-0">
                        <span class="badge bg-info"><?php echo htmlspecialchars($goal->goalType); ?></span>
                        <?php if ($goal->propriety === 'Critical'): ?>
                            <span class="badge bg-danger ms-2">Critical</span>
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <a href="<?= "{$base}html/?s=user&ss=goals&p=dashboard" ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
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
                                echo $goal->status === 'Active' ? 'success' :
                                    ($goal->status === 'Completed' ? 'primary' : 'secondary');
                            ?>"><?php echo htmlspecialchars($goal->status); ?></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Weight:</strong>
                            <?php echo number_format($goal->weight * 100, 2); ?>%
                        </div>
                        <div class="col-md-6 mt-2">
                            <strong>Start Date:</strong>
                            <?php echo date('M d, Y', strtotime($goal->startDate)); ?>
                        </div>
                        <div class="col-md-6 mt-2">
                            <strong>End Date:</strong>
                            <?php echo date('M d, Y', strtotime($goal->endDate)); ?>
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
                    <?php if ($goal->goalType === 'OKR' && isset($goal->okrData)): ?>
                        <div class="mt-4">
                            <h6>Objective</h6>
                            <p><?php echo htmlspecialchars($goal->okrData->objective); ?></p>
                            <h6>Key Results</h6>
                            <ul>
                                <?php if (is_array($goal->okrData->keyResults)): ?>
                                    <?php foreach ($goal->okrData->keyResults as $kr): ?>
                                        <li><?php echo htmlspecialchars($kr['kr'] ?? ''); ?> -
                                            Target: <?php echo $kr['target'] ?? ''; ?>
                                            <?php echo $kr['unit'] ?? ''; ?>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    <?php elseif ($goal->goalType === 'KPI' && isset($goal->kpiData)): ?>
                        <div class="mt-4">
                            <h6>KPI Details</h6>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($goal->kpiData->kpiName); ?></p>
                            <p><strong>Target:</strong> <?php echo number_format($goal->kpiData->targetValue, 2); ?>
                                <?php echo htmlspecialchars($goal->kpiData->unit ?? ''); ?></p>
                            <p><strong>Current:</strong> <?php echo number_format($goal->kpiData->currentValue ?? 0, 2); ?>
                                <?php echo htmlspecialchars($goal->kpiData->unit ?? ''); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Score Card -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Performance Score</h5>
                </div>
                <div class="card-body text-center">
                    <h1 class="display-4"><?php echo $latestScore ? number_format($latestScore, 1) : 'N/A'; ?></h1>
                    <p class="text-muted">Weighted Score</p>
                </div>
            </div>

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

    <!-- 360 Feedback -->
    <?php if ($feedback360): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">360-Degree Feedback</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($feedback360 as $role => $data): ?>
                            <?php if ($role !== 'overallScore' && isset($data['averageScore'])): ?>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h6><?php echo htmlspecialchars($role); ?></h6>
                                    <h3><?php echo number_format($data['averageScore'], 1); ?></h3>
                                    <small class="text-muted"><?php echo $data['count']; ?> evaluation(s)</small>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Evaluation Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Evaluations</h5>
                </div>
                <div class="card-body">
                    <?php if ($evaluations && count($evaluations) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Evaluator</th>
                                        <th>Role</th>
                                        <th>Score</th>
                                        <th>Comments</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($evaluations as $eval): ?>
                                        <tr>
                                            <td>
                                                <?php if ($eval->isAnonymous === 'Y'): ?>
                                                    <em>Anonymous</em>
                                                <?php else: ?>
                                                    <?php echo htmlspecialchars($eval->evaluatorUserID); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($eval->evaluatorRole); ?></span></td>
                                            <td><?php echo number_format($eval->score, 1); ?></td>
                                            <td><?php echo htmlspecialchars($eval->comments ?? ''); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($eval->evaluationDate)); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No evaluations submitted yet.</p>
                    <?php endif; ?>

                    <!-- Submit Evaluation Form -->
                    <div class="mt-4">
                        <h6>Submit Evaluation</h6>
                        <form id="evaluationForm" onsubmit="submitEvaluation(event)">
                            <input type="hidden" name="action" value="submit">
                            <input type="hidden" name="goalUUID" value="<?php echo htmlspecialchars($goalUUID); ?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Score (0-100)</label>
                                    <input type="number" class="form-control" name="score" min="0" max="100" step="0.1" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Comments</label>
                                    <textarea class="form-control" name="comments" rows="3"></textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Submit Evaluation</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function submitEvaluation(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    fetch('php/scripts/goals/submit_evaluation.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Evaluation submitted successfully!');
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
</script>

