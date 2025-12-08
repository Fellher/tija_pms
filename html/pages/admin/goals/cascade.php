<?php
/**
 * Cascade Management Interface
 * Admin interface for cascading goals across organization
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
require_once 'php/classes/goalhierarchy.php';
require_once 'php/classes/data.php';

// Get available goals for cascading
$cascadeableGoals = Goal::getCascadeableGoals($DBConn);

// Get cascade log
$cascadeLog = GoalHierarchy::getCascadeLog(50, $DBConn);

// Get entities for target selection
$entities = Data::entities(array('Lapsed' => 'N', 'Suspended' => 'N'), false, $DBConn);
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Cascade Management</h4>
                    <p class="text-muted mb-0">Cascade goals across organizational hierarchy</p>
                </div>
                <div>
                    <a href="<?= "{$base}html/?s=admin&ss=goals&p=dashboard" ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Cascade Form -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Cascade Goal</h5>
                </div>
                <div class="card-body">
                    <form id="cascadeForm">
                        <input type="hidden" name="action" value="cascade">

                        <!-- Step 1: Select Parent Goal -->
                        <div class="mb-4">
                            <h6>Step 1: Select Parent Goal</h6>
                            <select class="form-select" name="parentGoalUUID" id="parentGoalSelect" required>
                                <option value="">-- Select Goal to Cascade --</option>
                                <?php if ($cascadeableGoals): ?>
                                    <?php foreach ($cascadeableGoals as $goal): ?>
                                        <option value="<?php echo $goal->goalUUID; ?>"
                                                data-mode="<?php echo $goal->cascadeMode; ?>">
                                            <?php echo htmlspecialchars($goal->goalTitle); ?>
                                            (<?php echo htmlspecialchars($goal->goalType); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Step 2: Select Cascade Mode -->
                        <div class="mb-4">
                            <h6>Step 2: Select Cascade Mode</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="cascadeMode"
                                                       id="modeStrict" value="Strict" checked>
                                                <label class="form-check-label" for="modeStrict">
                                                    <strong>Strict Cascade</strong>
                                                </label>
                                            </div>
                                            <small class="text-muted">Mandatory adoption - exact copy created automatically</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="cascadeMode"
                                                       id="modeAligned" value="Aligned">
                                                <label class="form-check-label" for="modeAligned">
                                                    <strong>Aligned Cascade</strong>
                                                </label>
                                            </div>
                                            <small class="text-muted">Interpretive adoption - targets create their own aligned goals</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="cascadeMode"
                                                       id="modeHybrid" value="Hybrid">
                                                <label class="form-check-label" for="modeHybrid">
                                                    <strong>Hybrid Cascade</strong>
                                                </label>
                                            </div>
                                            <small class="text-muted">Matrix cascade - based on functional criteria</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Select Targets -->
                        <div class="mb-4" id="targetSelection">
                            <h6>Step 3: Select Targets</h6>

                            <!-- Entity Selection (for Strict/Aligned) -->
                            <div id="entityTargets" class="target-section">
                                <label class="form-label">Select Entities</label>
                                <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                    <?php if ($entities): ?>
                                        <?php foreach ($entities as $entity): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       name="targets[]"
                                                       value='{"type":"Entity","id":<?php echo $entity->entityID; ?>}'
                                                       id="entity_<?php echo $entity->entityID; ?>">
                                                <label class="form-check-label" for="entity_<?php echo $entity->entityID; ?>">
                                                    <?php echo htmlspecialchars($entity->entityName); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Functional Criteria (for Hybrid) -->
                            <div id="functionalTargets" class="target-section d-none">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Job Title</label>
                                        <select class="form-select" name="functionalFilter[jobTitleID]" id="jobTitleFilter">
                                            <option value="">All Job Titles</option>
                                            <?php
                                            $jobTitles = Data::job_titles(array('Lapsed' => 'N', 'Suspended' => 'N'), false, $DBConn);
                                            if ($jobTitles) {
                                                foreach ($jobTitles as $title) {
                                                    echo "<option value=\"{$title->jobTitleID}\">" . htmlspecialchars($title->jobTitle) . "</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Department</label>
                                        <select class="form-select" name="functionalFilter[departmentID]" id="departmentFilter">
                                            <option value="">All Departments</option>
                                            <?php
                                            $departments = Data::departments(array('Lapsed' => 'N', 'Suspended' => 'N'), false, $DBConn);
                                            if ($departments) {
                                                foreach ($departments as $dept) {
                                                    echo "<option value=\"{$dept->departmentID}\">" . htmlspecialchars($dept->departmentName) . "</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Job Category</label>
                                        <select class="form-select" name="functionalFilter[jobCategoryID]" id="jobCategoryFilter">
                                            <option value="">All Categories</option>
                                            <?php
                                            $categories = Data::job_categories(array('Lapsed' => 'N', 'Suspended' => 'N'), false, $DBConn);
                                            if ($categories) {
                                                foreach ($categories as $cat) {
                                                    echo "<option value=\"{$cat->jobCategoryID}\">" . htmlspecialchars($cat->jobCategoryName) . "</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Button -->
                        <div class="mb-3">
                            <button type="button" class="btn btn-outline-info" onclick="previewCascade()">
                                <i class="bi bi-eye me-2"></i>Preview Cascade
                            </button>
                        </div>

                        <!-- Execute Button -->
                        <div>
                            <button type="button" class="btn btn-primary" onclick="executeCascade()">
                                <i class="bi bi-diagram-3 me-2"></i>Execute Cascade
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Cascade Log -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Cascade History</h5>
                </div>
                <div class="card-body">
                    <?php if ($cascadeLog && count($cascadeLog) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Parent Goal</th>
                                        <th>Child Goal</th>
                                        <th>Mode</th>
                                        <th>Cascaded By</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cascadeLog as $log): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y H:i', strtotime(is_object($log) ? $log->cascadeDate : $log['cascadeDate'])); ?></td>
                                            <td><?php echo htmlspecialchars((is_object($log) ? ($log->parentTitle ?? 'N/A') : ($log['parentTitle'] ?? 'N/A'))); ?></td>
                                            <td><?php echo htmlspecialchars((is_object($log) ? ($log->childTitle ?? 'Pending') : ($log['childTitle'] ?? 'Pending'))); ?></td>
                                            <td><span class="badge bg-info"><?php echo htmlspecialchars(is_object($log) ? $log->cascadeMode : $log['cascadeMode']); ?></span></td>
                                            <td><?php echo htmlspecialchars((is_object($log) ? ($log->cascadedByName ?? 'N/A') : ($log['cascadedByName'] ?? 'N/A'))); ?></td>
                                            <td>
                                                <?php
                                                $status = is_object($log) ? $log->status : $log['status'];
                                                $statusClass = 'secondary';
                                                if ($status === 'Accepted') $statusClass = 'success';
                                                elseif ($status === 'Rejected') $statusClass = 'danger';
                                                elseif ($status === 'Pending') $statusClass = 'warning';
                                                ?>
                                                <span class="badge bg-<?php echo $statusClass; ?>">
                                                    <?php echo htmlspecialchars($status); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $childGoalUUID = is_object($log) ? ($log->childGoalUUID ?? null) : ($log['childGoalUUID'] ?? null);
                                                if ($childGoalUUID): ?>
                                                    <a href="<?= "{$base}html/?s=admin&ss=goals&p=goal_detail&goalUUID=" . $childGoalUUID ?>"
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i> View
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No cascade history yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewCascade() {
    const form = document.getElementById('cascadeForm');
    const formData = new FormData(form);
    const parentUUID = formData.get('parentGoalUUID');
    const mode = formData.get('cascadeMode');

    if (!parentUUID) {
        alert('Please select a parent goal');
        return;
    }

    // Collect targets similarly to executeCascade, but we won't actually execute
    let previewPayload = new FormData();
    previewPayload.append('action', 'cascade');
    previewPayload.append('parentGoalUUID', parentUUID);
    previewPayload.append('cascadeMode', mode);

    if (mode === 'Hybrid') {
        const filter = {
            jobTitleID: document.getElementById('jobTitleFilter').value || null,
            departmentID: document.getElementById('departmentFilter').value || null,
            jobCategoryID: document.getElementById('jobCategoryFilter').value || null
        };
        previewPayload.append('targets', JSON.stringify(filter));
    } else {
        const targets = [];
        const checkboxes = document.querySelectorAll('#entityTargets input[type="checkbox"]:checked');
        checkboxes.forEach(cb => {
            targets.push(JSON.parse(cb.value));
        });

        if (targets.length === 0) {
            alert('Please select at least one target to preview');
            return;
        }

        previewPayload.append('targets', JSON.stringify(targets));
    }

    // Call cascade endpoint but DO NOT reload; just use the results for preview
    fetch('<?= "{$base}php/scripts/goals/cascade_goal.php" ?>', {
        method: 'POST',
        body: previewPayload
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert('Preview error: ' + (data.message || data.error || 'Unknown error'));
            return;
        }

        const results = data.results || [];
        const count = results.length;

        let details = '';
        if (mode === 'Hybrid') {
            details = results.slice(0, 10).map(r =>
                '- Employee ID: ' + (r.employeeID || 'N/A') + ', Status: ' + (r.status || 'Created')
            ).join('\n');
        } else {
            details = results.slice(0, 10).map(r =>
                '- Target ' + (r.targetType || 'Entity') + ' ID: ' + (r.targetID || 'N/A') +
                ', Status: ' + (r.status || 'Pending')
            ).join('\n');
        }

        const extra = count > 10 ? '\n\n... and ' + (count - 10) + ' more.' : '';

        alert(
            'Cascade preview\n\n' +
            'Mode: ' + mode + '\n' +
            'Parent goal: ' + parentUUID + '\n' +
            'Estimated affected targets: ' + count + '\n\n' +
            (details ? 'Sample details:\n' + details : 'No individual results returned.') +
            extra
        );
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while generating preview');
    });
}

function executeCascade() {
    const form = document.getElementById('cascadeForm');
    const formData = new FormData(form);
    const parentUUID = formData.get('parentGoalUUID');
    const mode = formData.get('cascadeMode');

    if (!parentUUID) {
        alert('Please select a parent goal');
        return;
    }

    // Collect targets
    const targets = [];
    if (mode === 'Hybrid') {
        // Collect functional filter
        const filter = {
            jobTitleID: document.getElementById('jobTitleFilter').value || null,
            departmentID: document.getElementById('departmentFilter').value || null,
            jobCategoryID: document.getElementById('jobCategoryFilter').value || null
        };
        formData.append('targets', JSON.stringify(filter));
    } else {
        // Collect entity checkboxes
        const checkboxes = document.querySelectorAll('#entityTargets input[type="checkbox"]:checked');
        checkboxes.forEach(cb => {
            targets.push(JSON.parse(cb.value));
        });
        if (targets.length === 0) {
            alert('Please select at least one target');
            return;
        }
        formData.append('targets', JSON.stringify(targets));
    }

    if (!confirm('Execute cascade? This will create goals for selected targets.')) {
        return;
    }

    fetch('<?= "{$base}php/scripts/goals/cascade_goal.php" ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Cascade executed successfully! Created ' + data.results.length + ' goals.');
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

// Show/hide target sections based on mode
document.querySelectorAll('input[name="cascadeMode"]').forEach(radio => {
    radio.addEventListener('change', function() {
        if (this.value === 'Hybrid') {
            document.getElementById('entityTargets').classList.add('d-none');
            document.getElementById('functionalTargets').classList.remove('d-none');
        } else {
            document.getElementById('entityTargets').classList.remove('d-none');
            document.getElementById('functionalTargets').classList.add('d-none');
        }
    });
});
</script>

