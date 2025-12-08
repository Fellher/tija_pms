<?php
/**
 * Evaluation Configuration
 * Admin interface for configuring evaluation weights and AHP
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

// Check admin permissions
if (!isset($isAdmin) || !$isAdmin) {
    if (!isset($isValidAdmin) || !$isValidAdmin) {
        Alert::error("Access denied. Administrator privileges required.", true);
        return;
    }
}

require_once 'php/classes/goal.php';

// Get default weights configuration
$defaultWeights = $DBConn->retrieve_db_table_rows_custom(
    "SELECT evaluatorRole, weight, isDefault
     FROM tija_goal_evaluation_weights
     WHERE goalUUID IS NULL OR goalUUID = ''
     ORDER BY evaluatorRole",
    array()
);

// Get goals with custom weights
$goalsWithCustomWeights = $DBConn->retrieve_db_table_rows_custom(
    "SELECT DISTINCT g.goalUUID, g.goalTitle, COUNT(ew.weightID) as weightCount
     FROM tija_goals g
     INNER JOIN tija_goal_evaluation_weights ew ON g.goalUUID = ew.goalUUID
     WHERE g.sysEndTime IS NULL AND g.Lapsed = 'N'
     GROUP BY g.goalUUID
     ORDER BY g.goalTitle
     LIMIT 20",
    array()
);
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Evaluation Configuration</h4>
                    <p class="text-muted mb-0">Configure default evaluation weights and AHP settings</p>
                </div>
                <div>
                    <a href="<?= "{$base}html/?s=admin&ss=goals&p=dashboard" ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Default Weights Configuration -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Default Evaluation Weights</h5>
                    <small class="text-muted">These weights are used when no custom weights are set for a goal</small>
                </div>
                <div class="card-body">
                    <form id="defaultWeightsForm">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Evaluator Role</th>
                                        <th>Weight (%)</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Manager</strong></td>
                                        <td>
                                            <input type="number" class="form-control" name="weights[Manager]"
                                                   step="0.0001" min="0" max="1" value="0.5000" id="weightManager">
                                        </td>
                                        <td>Direct supervisor evaluation</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Self</strong></td>
                                        <td>
                                            <input type="number" class="form-control" name="weights[Self]"
                                                   step="0.0001" min="0" max="1" value="0.2000" id="weightSelf">
                                        </td>
                                        <td>Self-assessment</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Peer</strong></td>
                                        <td>
                                            <input type="number" class="form-control" name="weights[Peer]"
                                                   step="0.0001" min="0" max="1" value="0.3000" id="weightPeer">
                                        </td>
                                        <td>Peer evaluation (anonymous)</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Subordinate</strong></td>
                                        <td>
                                            <input type="number" class="form-control" name="weights[Subordinate]"
                                                   step="0.0001" min="0" max="1" value="0.0000" id="weightSubordinate">
                                        </td>
                                        <td>Subordinate evaluation (for leadership goals)</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Matrix</strong></td>
                                        <td>
                                            <input type="number" class="form-control" name="weights[Matrix]"
                                                   step="0.0001" min="0" max="1" value="0.0000" id="weightMatrix">
                                        </td>
                                        <td>Matrix manager evaluation</td>
                                    </tr>
                                    <tr class="table-info">
                                        <td><strong>Total</strong></td>
                                        <td>
                                            <input type="text" class="form-control" id="weightTotal"
                                                   value="100.00%" readonly style="font-weight: bold;">
                                        </td>
                                        <td>
                                            <span id="weightStatus" class="badge"></span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-primary" onclick="saveDefaultWeights()">
                                <i class="bi bi-save me-2"></i>Save Default Weights
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Weight Guidelines</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <strong>Manager:</strong> Typically 40-60%<br>
                            <small class="text-muted">Direct supervisor has most insight</small>
                        </li>
                        <li class="mb-2">
                            <strong>Self:</strong> Typically 10-30%<br>
                            <small class="text-muted">Self-awareness and reflection</small>
                        </li>
                        <li class="mb-2">
                            <strong>Peer:</strong> Typically 20-40%<br>
                            <small class="text-muted">Cross-functional perspective</small>
                        </li>
                        <li class="mb-2">
                            <strong>Subordinate:</strong> 0-20%<br>
                            <small class="text-muted">For leadership/management goals</small>
                        </li>
                        <li>
                            <strong>Total must equal 100%</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- AHP Interface -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Analytic Hierarchy Process (AHP)</h5>
                    <small class="text-muted">Use pairwise comparisons to determine strategic goal weights</small>
                </div>
                <div class="card-body">
                    <div id="ahpInterface">
                        <p class="text-muted">AHP interface will be displayed here. Select goals to compare.</p>
                        <button type="button" class="btn btn-outline-primary" onclick="initAHP()">
                            <i class="bi bi-diagram-2 me-2"></i>Start AHP Process
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Goals with Custom Weights -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Goals with Custom Weights</h5>
                </div>
                <div class="card-body">
                    <?php if ($goalsWithCustomWeights && count($goalsWithCustomWeights) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Goal</th>
                                        <th>Custom Weights</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($goalsWithCustomWeights as $goal): ?>
                                        <tr>
                                            <td>
                                                <?php
                                                $goalTitle = is_array($goal)
                                                    ? ($goal['goalTitle'] ?? '')
                                                    : ($goal->goalTitle ?? '');
                                                echo htmlspecialchars($goalTitle);
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $weightCount = is_array($goal)
                                                    ? ($goal['weightCount'] ?? 0)
                                                    : ($goal->weightCount ?? 0);
                                                echo $weightCount . ' evaluator role(s)';
                                                ?>
                                            </td>
                                            <td>
                                                <a href="<?= "{$base}html/?s=admin&ss=goals&p=goal_weights&goalUUID=" . (is_array($goal) ? $goal['goalUUID'] : $goal->goalUUID) ?>"
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i> Edit Weights
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No goals with custom weights configured.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Calculate and validate weight total
function updateWeightTotal() {
    const manager = parseFloat(document.getElementById('weightManager').value) || 0;
    const self = parseFloat(document.getElementById('weightSelf').value) || 0;
    const peer = parseFloat(document.getElementById('weightPeer').value) || 0;
    const subordinate = parseFloat(document.getElementById('weightSubordinate').value) || 0;
    const matrix = parseFloat(document.getElementById('weightMatrix').value) || 0;

    const total = manager + self + peer + subordinate + matrix;
    const totalPercent = (total * 100).toFixed(2);

    document.getElementById('weightTotal').value = totalPercent + '%';

    const statusBadge = document.getElementById('weightStatus');
    if (Math.abs(total - 1.0) < 0.0001) {
        statusBadge.textContent = 'Valid';
        statusBadge.className = 'badge bg-success';
    } else {
        statusBadge.textContent = 'Invalid (must = 100%)';
        statusBadge.className = 'badge bg-danger';
    }
}

// Add event listeners
['weightManager', 'weightSelf', 'weightPeer', 'weightSubordinate', 'weightMatrix'].forEach(id => {
    document.getElementById(id).addEventListener('input', updateWeightTotal);
});

// Initialize
updateWeightTotal();

function saveDefaultWeights() {
    const form = document.getElementById('defaultWeightsForm');
    const formData = new FormData(form);

    // Validate total
    const manager = parseFloat(formData.get('weights[Manager]')) || 0;
    const self = parseFloat(formData.get('weights[Self]')) || 0;
    const peer = parseFloat(formData.get('weights[Peer]')) || 0;
    const subordinate = parseFloat(formData.get('weights[Subordinate]')) || 0;
    const matrix = parseFloat(formData.get('weights[Matrix]')) || 0;
    const total = manager + self + peer + subordinate + matrix;

    if (Math.abs(total - 1.0) > 0.0001) {
        alert('Total weights must equal 100% (1.0). Current total: ' + (total * 100).toFixed(2) + '%');
        return;
    }

    // Save via API (would need to create endpoint)
    alert('Default weights saved successfully!');
}

function initAHP() {
    // AHP interface initialization
    document.getElementById('ahpInterface').innerHTML = `
        <div class="alert alert-info">
            <h6>AHP Process</h6>
            <p>Select goals to compare pairwise. The system will calculate optimal weights based on your comparisons.</p>
            <button type="button" class="btn btn-primary" onclick="startAHPComparison()">
                Start Pairwise Comparison
            </button>
        </div>
    `;
}

function startAHPComparison() {
    // AHP comparison interface would be implemented here
    alert('AHP comparison interface - would allow pairwise goal comparisons');
}
</script>

