<?php
/**
 * Matrix Manager View
 * View and manage goals for matrix-assigned team members
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

require_once 'php/classes/goalmatrix.php';
require_once 'php/classes/goal.php';
require_once 'php/classes/employee.php';

// Get matrix team
$projectID = isset($_GET['projectID']) ? intval($_GET['projectID']) : null;
$matrixTeam = GoalMatrix::getMatrixTeam($userDetails->ID, $projectID, $DBConn);

// Get goals for team members
$teamGoals = array();
if ($matrixTeam) {
    foreach ($matrixTeam as $member) {
        $employeeID = is_object($member) ? $member->employeeID : $member['employeeID'];
        $goals = GoalMatrix::getMatrixGoals($employeeID, $DBConn);
        if ($goals) {
            $teamGoals[$employeeID] = array(
                'employee' => $member,
                'goals' => $goals
            );
        }
    }
}
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Matrix Team Goals</h4>
                    <p class="text-muted mb-0">Manage goals for your matrix-assigned team members</p>
                </div>
                <div>
                    <a href="<?= "{$base}html/?s=user&ss=goals&p=dashboard" ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="">
                        <input type="hidden" name="page" value="user/goals/matrix_team">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Filter by Project</label>
                                <select class="form-select" name="projectID" onchange="this.form.submit()">
                                    <option value="">All Projects</option>
                                    <?php
                                    // Get projects where user is matrix manager
                                    $projects = $DBConn->retrieve_db_table_rows_custom(
                                        "SELECT DISTINCT p.projectID, p.projectName
                                         FROM tija_projects p
                                         INNER JOIN tija_goal_matrix_assignments ma ON ma.projectID = p.projectID
                                         WHERE ma.matrixManagerID = ? AND ma.status = 'Active'
                                         ORDER BY p.projectName",
                                        array(array('managerID', $userDetails->ID))
                                    );
                                    if ($projects) {
                                        foreach ($projects as $project) {
                                            $selected = ($projectID == $project['projectID']) ? 'selected' : '';
                                            echo "<option value=\"{$project['projectID']}\" {$selected}>" . htmlspecialchars($project['projectName']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Team Members</label>
                                <div class="form-control-plaintext">
                                    <strong><?php echo count($matrixTeam ? $matrixTeam : array()); ?></strong> team members
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Goals Overview -->
    <?php if ($matrixTeam && count($matrixTeam) > 0): ?>
        <?php foreach ($teamGoals as $employeeID => $data): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-person me-2"></i>
                                <?php
                                $employee = $data['employee'];
                                $employeeName = is_object($employee) ? ($employee->employeeName ?? 'N/A') : ($employee['employeeName'] ?? 'N/A');
                                echo htmlspecialchars($employeeName);
                                ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (isset($data['goals']) && count($data['goals']) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Goal</th>
                                                <th>Type</th>
                                                <th>Progress</th>
                                                <th>Due Date</th>
                                                <th>Score</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($data['goals'] as $goal): ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($goal->goalTitle); ?></strong></td>
                                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($goal->goalType); ?></span></td>
                                                    <td>
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar"
                                                                 style="width: <?php echo $goal->completionPercentage ?? 0; ?>%">
                                                                <?php echo number_format($goal->completionPercentage ?? 0, 1); ?>%
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><?php echo date('M d, Y', strtotime($goal->endDate)); ?></td>
                                                    <td>
                                                        <?php
                                                        require_once 'php/classes/goalscoring.php';
                                                        $score = GoalScoring::getLatestScore($goal->goalUUID, $DBConn);
                                                        echo $score ? number_format($score, 1) : 'N/A';
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <a href="<?= "{$base}html/?s=user&ss=goals&p=goal_detail&goalUUID=" . $goal->goalUUID ?>"
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-eye"></i> View
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-success"
                                                                onclick="openEvaluationModal('<?php echo $goal->goalUUID; ?>', 'Matrix')">
                                                            <i class="bi bi-pencil"></i> Evaluate
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No matrix-assigned goals for this team member.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-3">No matrix team members assigned to you.</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Evaluation Modal (reuse from evaluations.php) -->
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
                    <input type="hidden" name="evaluatorRole" id="evalRole" value="Matrix">

                    <div class="mb-3">
                        <label class="form-label">Score (0-100) *</label>
                        <input type="number" class="form-control" name="score" id="evalScore"
                               min="0" max="100" step="0.1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Comments</label>
                        <textarea class="form-control" name="comments" id="evalComments" rows="4"></textarea>
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

