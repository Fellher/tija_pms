<?php
/**
 * Goal Library Management
 * Admin interface for managing goal templates
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
        if (!isset($isHRManager) || !$isHRManager) {
            Alert::error("Access denied. Administrator privileges required.", true);
            return;
        }
    }
}



// Get all templates
$templates = GoalLibrary::getTemplates(array('isActive' => 'Y'), $DBConn);

// Handle filters
$filters = array('isActive' => 'Y');
if (isset($_GET['goalType']) && !empty($_GET['goalType'])) {
    $filters['goalType'] = Utility::clean_string($_GET['goalType']);
}
if (isset($_GET['functionalDomain']) && !empty($_GET['functionalDomain'])) {
    $filters['functionalDomain'] = Utility::clean_string($_GET['functionalDomain']);
}
if (isset($_GET['competencyLevel']) && !empty($_GET['competencyLevel'])) {
    $filters['competencyLevel'] = Utility::clean_string($_GET['competencyLevel']);
}

$filteredTemplates = GoalLibrary::getTemplates($filters, $DBConn);
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Goal Library</h4>
                    <p class="text-muted mb-0">Manage goal templates and taxonomy</p>
                </div>
                <div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
                        <i class="bi bi-plus-circle me-2"></i>Create Template
                    </button>
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
                        <input type="hidden" name="s" value="admin">
                        <input type="hidden" name="ss" value="goals">
                        <input type="hidden" name="p" value="library">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">Goal Type</label>
                                <select class="form-select" name="goalType">
                                    <option value="">All Types</option>
                                    <option value="Strategic" <?php echo (isset($_GET['goalType']) && $_GET['goalType'] === 'Strategic') ? 'selected' : ''; ?>>Strategic</option>
                                    <option value="OKR" <?php echo (isset($_GET['goalType']) && $_GET['goalType'] === 'OKR') ? 'selected' : ''; ?>>OKR</option>
                                    <option value="KPI" <?php echo (isset($_GET['goalType']) && $_GET['goalType'] === 'KPI') ? 'selected' : ''; ?>>KPI</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Functional Domain</label>
                                <select class="form-select" name="functionalDomain">
                                    <option value="">All Domains</option>
                                    <option value="Sales" <?php echo (isset($_GET['functionalDomain']) && $_GET['functionalDomain'] === 'Sales') ? 'selected' : ''; ?>>Sales</option>
                                    <option value="IT" <?php echo (isset($_GET['functionalDomain']) && $_GET['functionalDomain'] === 'IT') ? 'selected' : ''; ?>>IT</option>
                                    <option value="HR" <?php echo (isset($_GET['functionalDomain']) && $_GET['functionalDomain'] === 'HR') ? 'selected' : ''; ?>>HR</option>
                                    <option value="Executive" <?php echo (isset($_GET['functionalDomain']) && $_GET['functionalDomain'] === 'Executive') ? 'selected' : ''; ?>>Executive</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Competency Level</label>
                                <select class="form-select" name="competencyLevel">
                                    <option value="">All Levels</option>
                                    <option value="Junior" <?php echo (isset($_GET['competencyLevel']) && $_GET['competencyLevel'] === 'Junior') ? 'selected' : ''; ?>>Junior</option>
                                    <option value="Senior" <?php echo (isset($_GET['competencyLevel']) && $_GET['competencyLevel'] === 'Senior') ? 'selected' : ''; ?>>Senior</option>
                                    <option value="Principal" <?php echo (isset($_GET['competencyLevel']) && $_GET['competencyLevel'] === 'Principal') ? 'selected' : ''; ?>>Principal</option>
                                    <option value="Executive" <?php echo (isset($_GET['competencyLevel']) && $_GET['competencyLevel'] === 'Executive') ? 'selected' : ''; ?>>Executive</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Templates (<?php echo count($filteredTemplates ? $filteredTemplates : array()); ?>)</h5>
                    <small class="text-muted">Click View to inspect, Edit to modify in-place</small>
                </div>
                <div class="card-body">
                    <?php if ($filteredTemplates && count($filteredTemplates) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Domain</th>
                                        <th>Level</th>
                                        <th>Usage</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($filteredTemplates as $template): ?>
                                        <tr>
                                            <td><code><?php echo htmlspecialchars($template->templateCode); ?></code></td>
                                            <td><strong><?php echo htmlspecialchars($template->templateName); ?></strong></td>
                                            <td><span class="badge bg-info">
                                                <?php echo htmlspecialchars($template->goalType); ?></span></td>
                                            <td><?php echo htmlspecialchars($template->functionalDomain ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($template->competencyLevel ?? 'All'); ?></td>
                                            <td><?php echo $template->usageCount ?? 0; ?> times</td>
                                            <td>
                                                <span class="badge bg-<?php echo $template->isActive === 'Y' ? 'success' : 'secondary'; ?>">
                                                    <?php echo $template->isActive === 'Y' ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                        onclick="viewTemplate(<?php echo $template->libraryID; ?>)">
                                                    <i class="bi bi-eye"></i> View
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                                        onclick="editTemplate(<?php echo $template->libraryID; ?>)">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-3">No templates found. Create your first template!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Template Modal -->
<div class="modal fade" id="createTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Goal Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="templateForm">
                    <div class="mb-3">
                        <label class="form-label">Template Code *</label>
                        <input type="text" class="form-control" name="templateCode" required
                               placeholder="e.g., SALE-001">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Template Name *</label>
                        <input type="text" class="form-control" name="templateName" required
                               placeholder="e.g., Achieve [Target]% Growth in [Product] Sales">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="templateDescription" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Goal Type *</label>
                            <select class="form-select" name="goalType" required>
                                <option value="Strategic">Strategic</option>
                                <option value="OKR">OKR</option>
                                <option value="KPI">KPI</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Functional Domain</label>
                            <select class="form-select" name="functionalDomain">
                                <option value="">Select Domain</option>
                                <option value="Sales">Sales</option>
                                <option value="IT">IT</option>
                                <option value="HR">HR</option>
                                <option value="Executive">Executive</option>
                                <option value="Legal">Legal</option>
                                <option value="Operations">Operations</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label">Competency Level</label>
                            <select class="form-select" name="competencyLevel">
                                <option value="All">All</option>
                                <option value="Junior">Junior</option>
                                <option value="Senior">Senior</option>
                                <option value="Principal">Principal</option>
                                <option value="Executive">Executive</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Time Horizon</label>
                            <select class="form-select" name="timeHorizon">
                                <option value="Annual">Annual</option>
                                <option value="Quarterly">Quarterly</option>
                                <option value="Monthly">Monthly</option>
                                <option value="Sprint">Sprint</option>
                                <option value="5-Year">5-Year</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label">Variables (JSON array)</label>
                        <input type="text" class="form-control" name="variables"
                               placeholder='["Target", "Product"]'>
                        <small class="text-muted">Array of variable names used in template (e.g., [Target], [Product])</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Suggested Weight</label>
                        <input type="number" class="form-control" name="suggestedWeight"
                               step="0.0001" min="0" max="1" value="0.25">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createTemplate()">Create Template</button>
            </div>
        </div>
    </div>
</div>

<!-- View Template Modal -->
<div class="modal fade" id="viewTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View Goal Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <dl class="row">
                    <dt class="col-sm-3">Code</dt>
                    <dd class="col-sm-9" id="viewTemplateCode"></dd>

                    <dt class="col-sm-3">Name</dt>
                    <dd class="col-sm-9" id="viewTemplateName"></dd>

                    <dt class="col-sm-3">Description</dt>
                    <dd class="col-sm-9" id="viewTemplateDescription"></dd>

                    <dt class="col-sm-3">Goal Type</dt>
                    <dd class="col-sm-9" id="viewTemplateType"></dd>

                    <dt class="col-sm-3">Functional Domain</dt>
                    <dd class="col-sm-9" id="viewTemplateDomain"></dd>

                    <dt class="col-sm-3">Competency Level</dt>
                    <dd class="col-sm-9" id="viewTemplateLevel"></dd>

                    <dt class="col-sm-3">Time Horizon</dt>
                    <dd class="col-sm-9" id="viewTemplateHorizon"></dd>

                    <dt class="col-sm-3">Suggested Weight</dt>
                    <dd class="col-sm-9" id="viewTemplateWeight"></dd>

                    <dt class="col-sm-3">Variables</dt>
                    <dd class="col-sm-9" id="viewTemplateVariables"></dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Template Modal -->
<div class="modal fade" id="editTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Goal Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editTemplateForm">
                    <input type="hidden" name="libraryID" id="editLibraryID">
                    <div class="mb-3">
                        <label class="form-label">Template Code *</label>
                        <input type="text" class="form-control" name="templateCode" id="editTemplateCode" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Template Name *</label>
                        <input type="text" class="form-control" name="templateName" id="editTemplateName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="templateDescription" id="editTemplateDescription" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Goal Type *</label>
                            <select class="form-select" name="goalType" id="editGoalType" required>
                                <option value="Strategic">Strategic</option>
                                <option value="OKR">OKR</option>
                                <option value="KPI">KPI</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Functional Domain</label>
                            <select class="form-select" name="functionalDomain" id="editFunctionalDomain">
                                <option value="">Select Domain</option>
                                <option value="Sales">Sales</option>
                                <option value="IT">IT</option>
                                <option value="HR">HR</option>
                                <option value="Executive">Executive</option>
                                <option value="Legal">Legal</option>
                                <option value="Operations">Operations</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label">Competency Level</label>
                            <select class="form-select" name="competencyLevel" id="editCompetencyLevel">
                                <option value="All">All</option>
                                <option value="Junior">Junior</option>
                                <option value="Senior">Senior</option>
                                <option value="Principal">Principal</option>
                                <option value="Executive">Executive</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Time Horizon</label>
                            <select class="form-select" name="timeHorizon" id="editTimeHorizon">
                                <option value="Annual">Annual</option>
                                <option value="Quarterly">Quarterly</option>
                                <option value="Monthly">Monthly</option>
                                <option value="Sprint">Sprint</option>
                                <option value="5-Year">5-Year</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label">Variables (JSON array)</label>
                        <input type="text" class="form-control" name="variables" id="editVariables"
                               placeholder='["Target", "Product"]'>
                        <small class="text-muted">Array of variable names used in template (e.g., [Target], [Product])</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Default KPIs (JSON)</label>
                        <textarea class="form-control" name="defaultKPIs" id="editDefaultKPIs"
                                  rows="2" placeholder='[{"name":"Sales","target":100}]'></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jurisdiction Deny (JSON array of country codes)</label>
                        <input type="text" class="form-control" name="jurisdictionDeny" id="editJurisdictionDeny"
                               placeholder='["DE","FR"]'>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Suggested Weight</label>
                        <input type="number" class="form-control" name="suggestedWeight" id="editSuggestedWeight"
                               step="0.0001" min="0" max="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Strategic Pillar</label>
                        <input type="text" class="form-control" name="strategicPillar" id="editStrategicPillar">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jurisdiction Scope</label>
                        <input type="text" class="form-control" name="jurisdictionScope" id="editJurisdictionScope"
                               placeholder="e.g. Global, Region, Country">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Broader Concept ID</label>
                        <input type="number" class="form-control" name="broaderConceptID" id="editBroaderConceptID">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Narrower Concept IDs (JSON array)</label>
                        <input type="text" class="form-control" name="narrowerConceptIDs" id="editNarrowerConceptIDs"
                               placeholder='[2,3,4]'>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Related Concept IDs (JSON array)</label>
                        <input type="text" class="form-control" name="relatedConceptIDs" id="editRelatedConceptIDs"
                               placeholder='[5,6]'>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="isActive" id="editIsActive">
                            <option value="Y">Active</option>
                            <option value="N">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateTemplate()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
function createTemplate() {
    const form = document.getElementById('templateForm');
    const formData = new FormData(form);

    // Parse variables JSON
    const variables = formData.get('variables');
    if (variables) {
        try {
            JSON.parse(variables);
        } catch (e) {
            alert('Invalid JSON format for variables');
            return;
        }
    }

    fetch('<?= "{$base}php/scripts/goals/manage_library.php" ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Template created successfully!');
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

function viewTemplate(libraryID) {
    const formData = new FormData();
    formData.append('action', 'get');
    formData.append('libraryID', libraryID);

    fetch('<?= "{$base}php/scripts/goals/manage_library.php" ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert('Error: ' + data.message);
            return;
        }

        const t = data.template || {};
        document.getElementById('viewTemplateCode').textContent = t.templateCode || '';
        document.getElementById('viewTemplateName').textContent = t.templateName || '';
        document.getElementById('viewTemplateDescription').textContent = t.templateDescription || '';
        document.getElementById('viewTemplateType').textContent = t.goalType || '';
        document.getElementById('viewTemplateDomain').textContent = t.functionalDomain || 'N/A';
        document.getElementById('viewTemplateLevel').textContent = t.competencyLevel || 'All';
        document.getElementById('viewTemplateHorizon').textContent = t.timeHorizon || 'Annual';
        document.getElementById('viewTemplateWeight').textContent = (t.suggestedWeight !== undefined && t.suggestedWeight !== null)
            ? t.suggestedWeight
            : '';

        let varsText = '';
        if (Array.isArray(t.variables)) {
            varsText = t.variables.join(', ');
        } else if (typeof t.variables === 'string') {
            varsText = t.variables;
        }
        document.getElementById('viewTemplateVariables').textContent = varsText || 'None';

        const modal = new bootstrap.Modal(document.getElementById('viewTemplateModal'));
        modal.show();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

function editTemplate(libraryID) {
    const formData = new FormData();
    formData.append('action', 'get');
    formData.append('libraryID', libraryID);

    fetch('<?= "{$base}php/scripts/goals/manage_library.php" ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert('Error: ' + data.message);
            return;
        }

        const t = data.template || {};
        document.getElementById('editLibraryID').value = t.libraryID || libraryID;
        document.getElementById('editTemplateCode').value = t.templateCode || '';
        document.getElementById('editTemplateName').value = t.templateName || '';
        document.getElementById('editTemplateDescription').value = t.templateDescription || '';
        document.getElementById('editGoalType').value = t.goalType || 'Strategic';
        document.getElementById('editFunctionalDomain').value = t.functionalDomain || '';
        document.getElementById('editCompetencyLevel').value = t.competencyLevel || 'All';
        document.getElementById('editTimeHorizon').value = t.timeHorizon || 'Annual';

        if (Array.isArray(t.variables)) {
            document.getElementById('editVariables').value = JSON.stringify(t.variables);
        } else if (typeof t.variables === 'string' && t.variables.trim() !== '') {
            document.getElementById('editVariables').value = t.variables;
        } else {
            document.getElementById('editVariables').value = '';
        }

        document.getElementById('editSuggestedWeight').value = (t.suggestedWeight !== undefined && t.suggestedWeight !== null)
            ? t.suggestedWeight
            : 0.25;

        // Default KPIs
        if (Array.isArray(t.defaultKPIs)) {
            document.getElementById('editDefaultKPIs').value = JSON.stringify(t.defaultKPIs);
        } else if (typeof t.defaultKPIs === 'string' && t.defaultKPIs.trim() !== '') {
            document.getElementById('editDefaultKPIs').value = t.defaultKPIs;
        } else {
            document.getElementById('editDefaultKPIs').value = '';
        }

        // Jurisdiction deny
        if (Array.isArray(t.jurisdictionDeny)) {
            document.getElementById('editJurisdictionDeny').value = JSON.stringify(t.jurisdictionDeny);
        } else if (typeof t.jurisdictionDeny === 'string' && t.jurisdictionDeny.trim() !== '') {
            document.getElementById('editJurisdictionDeny').value = t.jurisdictionDeny;
        } else {
            document.getElementById('editJurisdictionDeny').value = '';
        }

        document.getElementById('editStrategicPillar').value = t.strategicPillar || '';
        document.getElementById('editJurisdictionScope').value = t.jurisdictionScope || '';
        document.getElementById('editBroaderConceptID').value = t.broaderConceptID || '';

        // Narrower / related concepts
        if (Array.isArray(t.narrowerConceptIDs)) {
            document.getElementById('editNarrowerConceptIDs').value = JSON.stringify(t.narrowerConceptIDs);
        } else if (typeof t.narrowerConceptIDs === 'string' && t.narrowerConceptIDs.trim() !== '') {
            document.getElementById('editNarrowerConceptIDs').value = t.narrowerConceptIDs;
        } else {
            document.getElementById('editNarrowerConceptIDs').value = '';
        }

        if (Array.isArray(t.relatedConceptIDs)) {
            document.getElementById('editRelatedConceptIDs').value = JSON.stringify(t.relatedConceptIDs);
        } else if (typeof t.relatedConceptIDs === 'string' && t.relatedConceptIDs.trim() !== '') {
            document.getElementById('editRelatedConceptIDs').value = t.relatedConceptIDs;
        } else {
            document.getElementById('editRelatedConceptIDs').value = '';
        }

        document.getElementById('editIsActive').value = t.isActive || 'Y';

        const modal = new bootstrap.Modal(document.getElementById('editTemplateModal'));
        modal.show();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

function updateTemplate() {
    const form = document.getElementById('editTemplateForm');
    const formData = new FormData(form);
    formData.append('action', 'update');

    // Validate JSON fields
    const jsonFields = ['variables', 'defaultKPIs', 'jurisdictionDeny', 'narrowerConceptIDs', 'relatedConceptIDs'];
    for (const field of jsonFields) {
        const val = formData.get(field);
        if (val) {
            try {
                JSON.parse(val);
            } catch (e) {
                alert('Invalid JSON format for ' + field);
                return;
            }
        }
    }

    fetch('<?= "{$base}php/scripts/goals/manage_library.php" ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Template updated successfully!');
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

