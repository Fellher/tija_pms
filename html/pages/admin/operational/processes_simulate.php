<?php
/**
 * Process Simulation - Admin
 *
 * Simulate and analyze process performance
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

if(!$isValidUser) {
    Alert::info("You need to be logged in to access this page", true);
    include "includes/core/log_in_script.php";
    return;
}

if (!$isAdmin && !$isValidAdmin) {
    Alert::error("Access denied. Administrator privileges required.", true);
    return;
}

global $DBConn, $userID;

// Get process models
$processModels = $DBConn->retrieve_db_table_rows('tija_process_models',
    ['modelID', 'modelName', 'processID', 'version', 'isActive', 'DateAdded'],
    ['Suspended' => 'N']);

$pageTitle = "Process Simulation";
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0"><?php echo htmlspecialchars($pageTitle); ?></h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="?s=admin">Admin</a></li>
                        <li class="breadcrumb-item"><a href="?s=admin&ss=operational">Operational Work</a></li>
                        <li class="breadcrumb-item active">Simulation</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Simulation Controls -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Simulation Parameters</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Select Process Model</label>
                            <select class="form-select" id="processModel">
                                <option value="">Select a process model...</option>
                                <?php if (is_array($processModels)): ?>
                                    <?php foreach ($processModels as $model): ?>
                                        <option value="<?php echo $model['modelID']; ?>">
                                            <?php echo htmlspecialchars($model['modelName']); ?> (v<?php echo $model['version']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Simulation Duration</label>
                            <select class="form-select" id="simDuration">
                                <option value="30">30 days</option>
                                <option value="90" selected>90 days</option>
                                <option value="180">180 days</option>
                                <option value="365">1 year</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Volume</label>
                            <input type="number" class="form-control" id="volume" value="100" min="1">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Resource Count</label>
                            <input type="number" class="form-control" id="resourceCount" value="5" min="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button class="btn btn-primary" data-action="run-simulation">
                                    <i class="ri-play-line me-1"></i>Run Simulation
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Simulation Results -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Simulation Results</h4>
                </div>
                <div class="card-body">
                    <div id="simulationResults" class="text-center py-5">
                        <i class="ri-play-circle-line fs-1 text-muted mb-3"></i>
                        <h5 class="text-muted">No Simulation Run Yet</h5>
                        <p class="text-muted">Configure parameters above and click "Run Simulation" to analyze process performance.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Simulation History -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Simulation History</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="simulationsTable">
                            <thead>
                                <tr>
                                    <th>Model</th>
                                    <th>Duration</th>
                                    <th>Volume</th>
                                    <th>Resources</th>
                                    <th>Avg Cycle Time</th>
                                    <th>Efficiency</th>
                                    <th>Run Date</th>
                                    <th width="100" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        No simulation history available
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function runSimulation() {
    const modelID = document.getElementById('processModel').value;
    if (!modelID) {
        alert('Please select a process model');
        return;
    }

    const duration = document.getElementById('simDuration').value;
    const volume = document.getElementById('volume').value;
    const resourceCount = document.getElementById('resourceCount').value;

    // Show loading
    document.getElementById('simulationResults').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Running simulation...</span>
            </div>
            <p>Running simulation...</p>
        </div>
    `;

    // TODO: Implement simulation API call
    setTimeout(() => {
        document.getElementById('simulationResults').innerHTML = `
            <div class="row">
                <div class="col-md-3">
                    <div class="card border">
                        <div class="card-body text-center">
                            <h3 class="text-primary">${(volume / duration * 30).toFixed(1)}</h3>
                            <p class="text-muted mb-0">Tasks/Month</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border">
                        <div class="card-body text-center">
                            <h3 class="text-success">${(duration / volume * 24).toFixed(1)}</h3>
                            <p class="text-muted mb-0">Avg Cycle Time (hours)</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border">
                        <div class="card-body text-center">
                            <h3 class="text-info">${((volume / duration / resourceCount) * 100).toFixed(1)}%</h3>
                            <p class="text-muted mb-0">Resource Utilization</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border">
                        <div class="card-body text-center">
                            <h3 class="text-warning">${(95 + Math.random() * 5).toFixed(1)}%</h3>
                            <p class="text-muted mb-0">Efficiency</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Performance Chart</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="performanceChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }, 2000);
}

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('simulationsTable')) {
        $('#simulationsTable').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[6, 'desc']],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ simulations per page"
            }
        });
    }
});
// Event delegation for process simulation
document.addEventListener('click', function(e) {
    const action = e.target.closest('[data-action]')?.getAttribute('data-action');
    if (!action) return;

    if (action === 'run-simulation') {
        runSimulation();
    }
});
</script>

