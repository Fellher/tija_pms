<?php
/**
 * Goals Dashboard - User View
 * Individual goal management dashboard
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
require_once 'php/classes/goallibrary.php';

// Get user goals
$userGoals = Goal::getGoalsByOwner($userDetails->ID, 'User', array('status' => 'Active'), $DBConn);
$completedGoals = Goal::getGoalsByOwner($userDetails->ID, 'User', array('status' => 'Completed'), $DBConn);
$draftGoals = Goal::getGoalsByOwner($userDetails->ID, 'User', array('status' => 'Draft'), $DBConn);

// Get suggested templates
$suggestedTemplates = GoalLibrary::suggestTemplates($userDetails->ID, array(), $DBConn);
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">My Goals</h4>
                    <p class="text-muted mb-0">Manage your performance goals and track progress</p>
                </div>
                <div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#goalWizardModal">
                        <i class="bi bi-plus-circle me-2"></i>Create New Goal
                    </button>
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
                            <h6 class="text-muted mb-1">Active Goals</h6>
                            <h3 class="mb-0"><?php echo count($userGoals ? $userGoals : array()); ?></h3>
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
                            <h6 class="text-muted mb-1">Completed</h6>
                            <h3 class="mb-0"><?php echo count($completedGoals ? $completedGoals : array()); ?></h3>
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
                            <h6 class="text-muted mb-1">Draft Goals</h6>
                            <h3 class="mb-0"><?php echo count($draftGoals ? $draftGoals : array()); ?></h3>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-file-earmark-text fs-1"></i>
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
                            <h6 class="text-muted mb-1">Avg. Completion</h6>
                            <h3 class="mb-0">
                                <?php
                                $totalCompletion = 0;
                                $goalCount = 0;
                                if ($userGoals) {
                                    foreach ($userGoals as $goal) {
                                        $totalCompletion += (float)($goal->completionPercentage ?? 0);
                                        $goalCount++;
                                    }
                                }
                                echo $goalCount > 0 ? number_format($totalCompletion / $goalCount, 1) : '0';
                                ?>%
                            </h3>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-graph-up fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Goals Tabs -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#active-goals">Active Goals</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#completed-goals">Completed</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#draft-goals">Drafts</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Active Goals Tab -->
                        <div class="tab-pane fade show active" id="active-goals">
                            <?php if ($userGoals && count($userGoals) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Goal</th>
                                                <th>Type</th>
                                                <th>Progress</th>
                                                <th>Due Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($userGoals as $goal): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($goal->goalTitle); ?></strong>
                                                        <?php if ($goal->propriety === 'Critical'): ?>
                                                            <span class="badge bg-danger ms-2">Critical</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo htmlspecialchars($goal->goalType); ?></span>
                                                    </td>
                                                    <td>
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar" role="progressbar"
                                                                 style="width: <?php echo $goal->completionPercentage ?? 0; ?>%"
                                                                 aria-valuenow="<?php echo $goal->completionPercentage ?? 0; ?>"
                                                                 aria-valuemin="0" aria-valuemax="100">
                                                                <?php echo number_format($goal->completionPercentage ?? 0, 1); ?>%
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><?php echo date('M d, Y', strtotime($goal->endDate)); ?></td>
                                                    <td>
                                                        <span class="badge bg-success"><?php echo htmlspecialchars($goal->status); ?></span>
                                                    </td>
                                                    <td>
                                                        <a href="<?= "{$base}html/?s=user&ss=goals&p=goal_detail&goalUUID=" . $goal->goalUUID ?>"
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-eye"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox fs-1 text-muted"></i>
                                    <p class="text-muted mt-3">No active goals. Create your first goal to get started!</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Completed Goals Tab -->
                        <div class="tab-pane fade" id="completed-goals">
                            <?php if ($completedGoals && count($completedGoals) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Goal</th>
                                                <th>Type</th>
                                                <th>Completion</th>
                                                <th>Completed Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($completedGoals as $goal): ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($goal->goalTitle); ?></strong></td>
                                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($goal->goalType); ?></span></td>
                                                    <td><?php echo number_format($goal->completionPercentage ?? 100, 1); ?>%</td>
                                                    <td><?php echo date('M d, Y', strtotime($goal->endDate)); ?></td>
                                                    <td>
                                                        <a href="<?= "{$base}html/?s=user&ss=goals&p=goal_detail&goalUUID=" . $goal->goalUUID ?>"
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-eye"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <p class="text-muted">No completed goals yet.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Draft Goals Tab -->
                        <div class="tab-pane fade" id="draft-goals">
                            <?php if ($draftGoals && count($draftGoals) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Goal</th>
                                                <th>Type</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($draftGoals as $goal): ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($goal->goalTitle); ?></strong></td>
                                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($goal->goalType); ?></span></td>
                                                    <td><?php echo date('M d, Y', strtotime($goal->DateAdded)); ?></td>
                                                    <td>
                                                        <a href="<?= "{$base}html/?s=user&ss=goals&p=goal_detail&goalUUID=" . $goal->goalUUID ?>"
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-pencil"></i> Edit
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <p class="text-muted">No draft goals.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Goal Wizard Modal -->
<div class="modal fade" id="goalWizardModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Goal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="goalWizardSteps">
                    <!-- Step 1: Template Selection -->
                    <div class="wizard-step" data-step="1">
                        <h6>Select Template</h6>
                        <div class="row">
                            <?php if ($suggestedTemplates): ?>
                                <?php foreach (array_slice($suggestedTemplates, 0, 6) as $template): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card template-card" data-template-id="<?php echo $template->libraryID; ?>">
                                            <div class="card-body">
                                                <h6><?php echo htmlspecialchars($template->templateName); ?></h6>
                                                <p class="text-muted small mb-0"><?php echo htmlspecialchars($template->templateDescription ?? ''); ?></p>
                                                <span class="badge bg-secondary mt-2"><?php echo htmlspecialchars($template->goalType); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-outline-secondary" onclick="showCustomGoal()">Create Custom Goal</button>
                    </div>

                    <!-- Step 2: Goal Details (will be populated dynamically) -->
                    <div class="wizard-step d-none" data-step="2">
                        <h6>Goal Details</h6>
                        <form id="goalForm">
                            <input type="hidden" name="action" value="create">
                            <div class="mb-3">
                                <label class="form-label">Goal Title</label>
                                <input type="text" class="form-control" name="goalTitle" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Goal Type</label>
                                <select class="form-select" name="goalType" required>
                                    <option value="Strategic">Strategic Goal</option>
                                    <option value="OKR">OKR</option>
                                    <option value="KPI">KPI</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="goalDescription" rows="3"></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" class="form-control" name="startDate" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">End Date</label>
                                    <input type="date" class="form-control" name="endDate" required>
                                </div>
                            </div>
                            <div class="mb-3 mt-3">
                                <label class="form-label">Weight</label>
                                <input type="number" class="form-control" name="weight" step="0.0001" min="0" max="1" value="0.25">
                                <small class="text-muted">Weight percentage (0.0 - 1.0)</small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="nextStepBtn" onclick="nextWizardStep()">Next</button>
                <button type="button" class="btn btn-primary d-none" id="submitGoalBtn" onclick="submitGoal()">Create Goal</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentStep = 1;
let selectedTemplateId = null;

function nextWizardStep() {
    if (currentStep === 1) {
        // Move to step 2
        document.querySelector('[data-step="1"]').classList.add('d-none');
        document.querySelector('[data-step="2"]').classList.remove('d-none');
        document.getElementById('nextStepBtn').classList.add('d-none');
        document.getElementById('submitGoalBtn').classList.remove('d-none');
        currentStep = 2;
    }
}

function showCustomGoal() {
    selectedTemplateId = null;
    nextWizardStep();
}

// Template selection
document.querySelectorAll('.template-card').forEach(card => {
    card.addEventListener('click', function() {
        document.querySelectorAll('.template-card').forEach(c => c.classList.remove('border-primary'));
        this.classList.add('border-primary');
        selectedTemplateId = this.dataset.templateId;
    });
});

function submitGoal() {
    const form = document.getElementById('goalForm');
    const formData = new FormData(form);

    if (selectedTemplateId) {
        formData.append('libraryRefID', selectedTemplateId);
    }

    fetch('php/scripts/goals/manage_goal.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Goal created successfully!');
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

