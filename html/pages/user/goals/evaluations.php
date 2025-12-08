<?php
/**
 * Evaluations Interface
 * Standalone page for managing evaluations
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
require_once 'php/classes/goalpermissions.php';

// Get pending evaluations
$pendingEvaluations = $DBConn->retrieve_db_table_rows_custom(
    "SELECT DISTINCT g.goalUUID, g.goalTitle, g.goalType, g.endDate, g.ownerUserID,
            ew.evaluatorRole, g.completionPercentage
     FROM tija_goals g
     INNER JOIN tija_goal_evaluation_weights ew ON g.goalUUID = ew.goalUUID
     LEFT JOIN tija_goal_evaluations e ON g.goalUUID = e.goalUUID
         AND e.evaluatorUserID = ?
         AND e.evaluatorRole = ew.evaluatorRole
         AND e.status = 'Submitted'
     WHERE g.status = 'Active'
     AND g.sysEndTime IS NULL
     AND g.Lapsed = 'N'
     AND e.evaluationID IS NULL
     AND (
         (ew.evaluatorRole = 'Manager' AND EXISTS (
             SELECT 1 FROM user_details ud WHERE ud.ID = g.ownerUserID AND ud.supervisorID = ?
         ))
         OR (ew.evaluatorRole = 'Self' AND g.ownerUserID = ?)
         OR (ew.evaluatorRole = 'Matrix' AND EXISTS (
             SELECT 1 FROM tija_goal_matrix_assignments ma
             WHERE ma.goalUUID = g.goalUUID AND ma.matrixManagerID = ? AND ma.status = 'Active'
         ))
     )
     ORDER BY g.endDate ASC",
    array(
        array('userID1', $userDetails->ID),
        array('userID2', $userDetails->ID),
        array('userID3', $userDetails->ID),
        array('userID4', $userDetails->ID)
    )
);

// Get submitted evaluations
$submittedEvaluations = $DBConn->retrieve_db_table_rows(
    'tija_goal_evaluations',
    array('evaluationID', 'goalUUID', 'evaluatorRole', 'score', 'evaluationDate', 'status'),
    array('evaluatorUserID' => $userDetails->ID, 'status' => 'Submitted'),
    false
);
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">My Evaluations</h4>
                    <p class="text-muted mb-0">Submit and manage goal evaluations</p>
                </div>
                <div>
                    <a href="<?= "{$base}html/?s=user&ss=goals&p=dashboard" ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Pending Evaluations</h6>
                    <h3 class="mb-0"><?php echo count($pendingEvaluations ? $pendingEvaluations : array()); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Submitted</h6>
                    <h3 class="mb-0"><?php echo count($submittedEvaluations ? $submittedEvaluations : array()); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Completion Rate</h6>
                    <h3 class="mb-0">
                        <?php
                        $total = count($pendingEvaluations ? $pendingEvaluations : array()) + count($submittedEvaluations ? $submittedEvaluations : array());
                        $completed = count($submittedEvaluations ? $submittedEvaluations : array());
                        echo $total > 0 ? number_format(($completed / $total) * 100, 1) : '0';
                        ?>%
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Evaluations -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Pending Evaluations</h5>
                </div>
                <div class="card-body">
                    <?php if ($pendingEvaluations && count($pendingEvaluations) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Goal</th>
                                        <th>Type</th>
                                        <th>My Role</th>
                                        <th>Progress</th>
                                        <th>Due Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingEvaluations as $pending): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars(is_object($pending) ? $pending->goalTitle : $pending['goalTitle']); ?></strong></td>
                                            <td><span class="badge bg-info"><?php echo htmlspecialchars(is_object($pending) ? $pending->goalType : $pending['goalType']); ?></span></td>
                                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars(is_object($pending) ? $pending->evaluatorRole : $pending['evaluatorRole']); ?></span></td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar"
                                                         style="width: <?php echo is_object($pending) ? ($pending->completionPercentage ?? 0) : ($pending['completionPercentage'] ?? 0); ?>%">
                                                        <?php echo number_format(is_object($pending) ? ($pending->completionPercentage ?? 0) : ($pending['completionPercentage'] ?? 0), 1); ?>%
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime(is_object($pending) ? $pending->endDate : $pending['endDate'])); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary"
                                                        onclick="openEvaluationModal('<?php echo is_object($pending) ? $pending->goalUUID : $pending['goalUUID']; ?>', '<?php echo is_object($pending) ? $pending->evaluatorRole : $pending['evaluatorRole']; ?>')">
                                                    <i class="bi bi-pencil me-1"></i>Evaluate
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-check-circle fs-1 text-success"></i>
                            <p class="text-muted mt-3">No pending evaluations. Great job!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Submitted Evaluations -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Evaluation History</h5>
                </div>
                <div class="card-body">
                    <?php if ($submittedEvaluations && count($submittedEvaluations) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Goal</th>
                                        <th>Role</th>
                                        <th>Score</th>
                                        <th>Date Submitted</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($submittedEvaluations as $eval): ?>
                                        <?php
                                        $goal = Goal::getGoal($eval->goalUUID, $DBConn);
                                        ?>
                                        <tr>
                                            <td><?php echo $goal ? htmlspecialchars($goal->goalTitle) : 'N/A'; ?></td>
                                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($eval->evaluatorRole); ?></span></td>
                                            <td><strong><?php echo number_format($eval->score, 1); ?></strong></td>
                                            <td><?php echo date('M d, Y', strtotime($eval->evaluationDate)); ?></td>
                                            <td><span class="badge bg-success"><?php echo htmlspecialchars($eval->status); ?></span></td>
                                            <td>
                                                <a href="<?= "{$base}html/?s=user&ss=goals&p=goal_detail&goalUUID=" . $eval->goalUUID ?>"
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> View Goal
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <p class="text-muted">No evaluation history yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Evaluation Modal -->
<div class="modal fade" id="evaluationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submit Evaluation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="evaluationForm">
                    <input type="hidden" name="action" value="submit">
                    <input type="hidden" name="goalUUID" id="evalGoalUUID">
                    <input type="hidden" name="evaluatorRole" id="evalRole">

                    <div class="mb-3">
                        <label class="form-label">Score (0-100) *</label>
                        <input type="number" class="form-control" name="score" id="evalScore"
                               min="0" max="100" step="0.1" required>
                        <small class="text-muted">Enter a score between 0 and 100</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Comments</label>
                        <textarea class="form-control" name="comments" id="evalComments" rows="4"
                                  placeholder="Provide feedback and comments..."></textarea>
                    </div>
                    <div class="alert alert-info">
                        <small>
                            <strong>Evaluation Guidelines:</strong><br>
                            - Be objective and fair in your assessment<br>
                            - Provide constructive feedback<br>
                            - Consider both quantitative and qualitative factors
                        </small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitEvaluation()">Submit Evaluation</button>
            </div>
        </div>
    </div>
</div>

<script>
let evaluationModal;

function openEvaluationModal(goalUUID, role) {
    document.getElementById('evalGoalUUID').value = goalUUID;
    document.getElementById('evalRole').value = role;
    document.getElementById('evalScore').value = '';
    document.getElementById('evalComments').value = '';

    evaluationModal = new bootstrap.Modal(document.getElementById('evaluationModal'));
    evaluationModal.show();
}

function submitEvaluation() {
    const form = document.getElementById('evaluationForm');
    const formData = new FormData(form);

    fetch('php/scripts/goals/submit_evaluation.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Evaluation submitted successfully!');
            evaluationModal.hide();
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

