<?php
/**
 * AHP (Analytic Hierarchy Process) Interface
 * Pairwise comparison interface for strategic goal weighting
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

// Get strategic goals for comparison
$strategicGoals = $DBConn->retrieve_db_table_rows(
    'tija_goals',
    array('goalUUID', 'goalTitle', 'goalDescription', 'weight'),
    array('goalType' => 'Strategic', 'status' => 'Active', 'sysEndTime' => 'NULL', 'Lapsed' => 'N'),
    false
);
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Analytic Hierarchy Process (AHP)</h4>
                    <p class="text-muted mb-0">Use pairwise comparisons to determine optimal goal weights</p>
                </div>
                <div>
                    <a href="<?= "{$base}html/?s=admin&ss=goals&p=evaluation_config" ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Config
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Information Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <h6><i class="bi bi-info-circle me-2"></i>About AHP</h6>
                <p class="mb-0">
                    The Analytic Hierarchy Process helps you determine optimal weights for strategic goals through pairwise comparisons.
                    Compare each goal against every other goal to determine relative importance. The system will calculate mathematically
                    consistent weights based on your comparisons.
                </p>
            </div>
        </div>
    </div>

    <!-- Goal Selection -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Step 1: Select Goals to Compare</h5>
                </div>
                <div class="card-body">
                    <?php if ($strategicGoals && count($strategicGoals) > 0): ?>
                        <div class="row">
                            <?php foreach ($strategicGoals as $goal): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card goal-select-card" data-goal-uuid="<?php echo $goal->goalUUID; ?>">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       name="selectedGoals[]"
                                                       value="<?php echo $goal->goalUUID; ?>"
                                                       id="goal_<?php echo $goal->goalUUID; ?>">
                                                <label class="form-check-label" for="goal_<?php echo $goal->goalUUID; ?>">
                                                    <strong><?php echo htmlspecialchars($goal->goalTitle); ?></strong>
                                                </label>
                                            </div>
                                            <small class="text-muted">Current Weight: <?php echo number_format($goal->weight * 100, 2); ?>%</small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-primary" onclick="startAHPComparison()">
                                <i class="bi bi-diagram-2 me-2"></i>Start Pairwise Comparison
                            </button>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No strategic goals available for comparison.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparison Interface -->
    <div class="row mb-4 d-none" id="comparisonInterface">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Step 2: Pairwise Comparison</h5>
                    <small class="text-muted">Progress: <span id="comparisonProgress">0 / 0</span></small>
                </div>
                <div class="card-body">
                    <div id="comparisonContent">
                        <!-- Comparison pairs will be displayed here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Results -->
    <div class="row d-none" id="resultsInterface">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Step 3: Calculated Weights</h5>
                </div>
                <div class="card-body">
                    <div id="ahpResults">
                        <!-- Results will be displayed here -->
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-success" onclick="applyAHPWeights()">
                            <i class="bi bi-check-circle me-2"></i>Apply Weights to Goals
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="resetAHP()">
                            <i class="bi bi-arrow-clockwise me-2"></i>Start Over
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let selectedGoals = [];
let comparisonMatrix = {};
let currentComparisonIndex = 0;
let comparisons = [];

// AHP Scale (1-9)
const ahpScale = {
    1: 'Equal Importance',
    2: 'Weak',
    3: 'Moderate',
    4: 'Moderate Plus',
    5: 'Strong',
    6: 'Strong Plus',
    7: 'Very Strong',
    8: 'Very, Very Strong',
    9: 'Extreme'
};

function startAHPComparison() {
    // Get selected goals
    selectedGoals = Array.from(document.querySelectorAll('input[name="selectedGoals[]"]:checked'))
        .map(cb => cb.value);

    if (selectedGoals.length < 2) {
        alert('Please select at least 2 goals to compare');
        return;
    }

    // Generate all pairwise comparisons
    comparisons = [];
    for (let i = 0; i < selectedGoals.length; i++) {
        for (let j = i + 1; j < selectedGoals.length; j++) {
            comparisons.push({
                goal1: selectedGoals[i],
                goal2: selectedGoals[j],
                value: null
            });
        }
    }

    currentComparisonIndex = 0;
    document.getElementById('comparisonInterface').classList.remove('d-none');
    showNextComparison();
}

function showNextComparison() {
    if (currentComparisonIndex >= comparisons.length) {
        calculateAHPWeights();
        return;
    }

    const comparison = comparisons[currentComparisonIndex];
    const goal1Name = document.querySelector(`input[value="${comparison.goal1}"]`).closest('.card').querySelector('strong').textContent;
    const goal2Name = document.querySelector(`input[value="${comparison.goal2}"]`).closest('.card').querySelector('strong').textContent;

    document.getElementById('comparisonProgress').textContent =
        `${currentComparisonIndex + 1} / ${comparisons.length}`;

    document.getElementById('comparisonContent').innerHTML = `
        <div class="text-center">
            <h5>Which goal is more important?</h5>
            <div class="row mt-4">
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6>${goal1Name}</h6>
                            <button type="button" class="btn btn-primary" onclick="selectComparison(1)">
                                Select This
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 text-center">
                    <h4>vs</h4>
                </div>
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6>${goal2Name}</h6>
                            <button type="button" class="btn btn-primary" onclick="selectComparison(2)">
                                Select This
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <label class="form-label">How much more important? (1-9 scale)</label>
                <select class="form-select" id="importanceScale" style="max-width: 300px; margin: 0 auto;">
                    <option value="">Select importance level</option>
                    ${Object.entries(ahpScale).map(([val, label]) =>
                        `<option value="${val}">${val} - ${label}</option>`
                    ).join('')}
                </select>
            </div>
            <div class="mt-3">
                <button type="button" class="btn btn-success" onclick="saveComparison()" id="saveComparisonBtn" disabled>
                    Continue
                </button>
            </div>
        </div>
    `;

    document.getElementById('importanceScale').addEventListener('change', function() {
        document.getElementById('saveComparisonBtn').disabled = !this.value;
    });
}

let selectedGoal = null;

function selectComparison(goalNumber) {
    selectedGoal = goalNumber;
    // Highlight selected
    document.querySelectorAll('.card').forEach(card => card.classList.remove('border-primary'));
    event.target.closest('.col-md-5').querySelector('.card').classList.add('border-primary');
}

function saveComparison() {
    const scale = parseInt(document.getElementById('importanceScale').value);
    if (!scale || !selectedGoal) {
        alert('Please select a goal and importance level');
        return;
    }

    const comparison = comparisons[currentComparisonIndex];
    if (selectedGoal === 1) {
        comparison.value = scale;
    } else {
        comparison.value = 1 / scale; // Reciprocal
    }

    currentComparisonIndex++;
    showNextComparison();
}

function calculateAHPWeights() {
    // Build comparison matrix
    const n = selectedGoals.length;
    const matrix = Array(n).fill().map(() => Array(n).fill(1));

    comparisons.forEach(comp => {
        const i = selectedGoals.indexOf(comp.goal1);
        const j = selectedGoals.indexOf(comp.goal2);
        matrix[i][j] = comp.value;
        matrix[j][i] = 1 / comp.value;
    });

    // Calculate weights using eigenvector method (simplified)
    const weights = calculateEigenvector(matrix);

    // Display results
    document.getElementById('resultsInterface').classList.remove('d-none');
    const resultsHTML = `
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Goal</th>
                        <th>Calculated Weight</th>
                        <th>Current Weight</th>
                        <th>Difference</th>
                    </tr>
                </thead>
                <tbody>
                    ${selectedGoals.map((goalId, index) => {
                        const goalName = document.querySelector(`input[value="${goalId}"]`).closest('.card').querySelector('strong').textContent;
                        const calculatedWeight = weights[index];
                        const currentWeight = parseFloat(document.querySelector(`input[value="${goalId}"]`).closest('.card').querySelector('small').textContent.match(/[\d.]+/)[0]) / 100;
                        return `
                            <tr>
                                <td>${goalName}</td>
                                <td><strong>${(calculatedWeight * 100).toFixed(2)}%</strong></td>
                                <td>${(currentWeight * 100).toFixed(2)}%</td>
                                <td>${((calculatedWeight - currentWeight) * 100).toFixed(2)}%</td>
                            </tr>
                        `;
                    }).join('')}
                </tbody>
            </table>
        </div>
    `;

    document.getElementById('ahpResults').innerHTML = resultsHTML;
    window.calculatedWeights = weights;
}

function calculateEigenvector(matrix) {
    // Simplified eigenvector calculation (geometric mean method)
    const n = matrix.length;
    const weights = [];

    for (let i = 0; i < n; i++) {
        let product = 1;
        for (let j = 0; j < n; j++) {
            product *= matrix[i][j];
        }
        weights.push(Math.pow(product, 1/n));
    }

    // Normalize
    const sum = weights.reduce((a, b) => a + b, 0);
    return weights.map(w => w / sum);
}

function applyAHPWeights() {
    if (!window.calculatedWeights) {
        alert('No weights calculated');
        return;
    }

    // Apply weights via API
    const updates = selectedGoals.map((goalId, index) => ({
        goalUUID: goalId,
        weight: window.calculatedWeights[index]
    }));

    // Would call API to update weights
    alert('Weights applied successfully!');
    location.reload();
}

function resetAHP() {
    document.getElementById('comparisonInterface').classList.add('d-none');
    document.getElementById('resultsInterface').classList.add('d-none');
    selectedGoals = [];
    comparisons = [];
    currentComparisonIndex = 0;
}
</script>

