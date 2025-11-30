<?php
/**
 * Workflows Management - Admin
 *
 * Manage operational workflows
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

// Include help component
include __DIR__ . '/../../../includes/components/operational_help.php';

global $DBConn, $userID;

// Get filters
$functionalArea = $_GET['functionalArea'] ?? '';
$status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Get workflows
$whereArr = ['Suspended' => 'N'];
if ($status !== 'all') {
    if ($status === 'active') {
        $whereArr['isActive'] = 'Y';
    } else {
        $whereArr['isActive'] = 'N';
    }
}

$workflows = $DBConn->retrieve_db_table_rows('tija_workflows',
    ['workflowID', 'workflowName', 'workflowDescription', 'functionalArea', 'isActive', 'DateAdded'],
    $whereArr);

// Filter by functional area if specified
if ($functionalArea && $workflows) {
    $workflows = array_filter($workflows, function($w) use ($functionalArea) {
        $fa = is_object($w) ? ($w->functionalArea ?? '') : ($w['functionalArea'] ?? '');
        return $fa === $functionalArea;
    });
}

$pageTitle = "Workflows Management";
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0"><?php echo htmlspecialchars($pageTitle); ?></h4>
                    <p class="text-muted mb-0 help-text">
                        Define workflows that specify step-by-step processes for completing operational tasks.
                        <?php echo renderHelpPopover('Workflows', 'Workflows define the sequential steps required to complete a task. Each step can have assigned roles, required approvals, and specific actions. Workflows ensure consistency and compliance.', 'right'); ?>
                    </p>
                </div>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="?s=admin">Admin</a></li>
                        <li class="breadcrumb-item"><a href="?s=admin&ss=operational">Operational Work</a></li>
                        <li class="breadcrumb-item active">Workflows</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Total Workflows</p>
                            <h4 class="mb-2"><?php echo is_array($workflows) ? count($workflows) : 0; ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-primary rounded-3">
                                <i class="ri-flow-chart font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Active Workflows</p>
                            <h4 class="mb-2 text-success"><?php
                                $active = is_array($workflows) ? array_filter($workflows, function($w) {
                                    $isActive = is_object($w) ? ($w->isActive ?? 'N') : ($w['isActive'] ?? 'N');
                                    return $isActive === 'Y';
                                }) : [];
                                echo count($active);
                            ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-success rounded-3">
                                <i class="ri-checkbox-circle-line font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Inactive</p>
                            <h4 class="mb-2 text-secondary"><?php
                                $inactive = is_array($workflows) ? array_filter($workflows, function($w) {
                                    $isActive = is_object($w) ? ($w->isActive ?? 'N') : ($w['isActive'] ?? 'N');
                                    return $isActive === 'N';
                                }) : [];
                                echo count($inactive);
                            ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-secondary rounded-3">
                                <i class="ri-pause-circle-line font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">This Month</p>
                            <h4 class="mb-2 text-info"><?php
                                $thisMonth = is_array($workflows) ? array_filter($workflows, function($w) {
                                    $dateAdded = is_object($w) ? ($w->DateAdded ?? '') : ($w['DateAdded'] ?? '');
                                    return !empty($dateAdded) && date('Y-m', strtotime($dateAdded)) === date('Y-m');
                                }) : [];
                                echo count($thisMonth);
                            ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-info rounded-3">
                                <i class="ri-calendar-line font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Actions -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex gap-2 flex-wrap">
                            <div class="input-group" style="width: 300px;">
                                <span class="input-group-text"><i class="ri-search-line"></i></span>
                                <input type="text" class="form-control" id="searchInput" placeholder="Search workflows..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <select class="form-select" id="statusFilter" style="width: 150px;">
                                <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                            <select class="form-select" id="functionalAreaFilter" style="width: 200px;">
                                <option value="">All Functional Areas</option>
                                <option value="Finance" <?php echo $functionalArea == 'Finance' ? 'selected' : ''; ?>>Finance</option>
                                <option value="HR" <?php echo $functionalArea == 'HR' ? 'selected' : ''; ?>>HR</option>
                                <option value="IT" <?php echo $functionalArea == 'IT' ? 'selected' : ''; ?>>IT</option>
                                <option value="Sales" <?php echo $functionalArea == 'Sales' ? 'selected' : ''; ?>>Sales</option>
                                <option value="Marketing" <?php echo $functionalArea == 'Marketing' ? 'selected' : ''; ?>>Marketing</option>
                                <option value="Legal" <?php echo $functionalArea == 'Legal' ? 'selected' : ''; ?>>Legal</option>
                                <option value="Facilities" <?php echo $functionalArea == 'Facilities' ? 'selected' : ''; ?>>Facilities</option>
                            </select>
                        </div>
                        <div>
                            <button type="button" class="btn btn-primary" data-action="open-workflow-modal" data-workflow-action="create">
                                <i class="ri-add-line me-1"></i>Create Workflow
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Workflows Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Workflows</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($workflows)): ?>
                        <div class="text-center py-5">
                            <i class="ri-flow-chart fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">No Workflows Found</h5>
                            <p class="text-muted">Get started by creating your first workflow.</p>
                            <button type="button" class="btn btn-primary mt-3" data-action="open-workflow-modal" data-workflow-action="create">
                                <i class="ri-add-line me-1"></i>Create Workflow
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="workflowsTable">
                                <thead>
                                    <tr>
                                        <th>Workflow Name</th>
                                        <th>Functional Area</th>
                                        <th>Status</th>
                                        <th>Steps</th>
                                        <th>Created</th>
                                        <th width="200" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($workflows as $workflow):
                                        // Handle both object and array access
                                        $workflowID = is_object($workflow) ? ($workflow->workflowID ?? null) : ($workflow['workflowID'] ?? null);
                                        $workflowName = is_object($workflow) ? ($workflow->workflowName ?? 'Unknown') : ($workflow['workflowName'] ?? 'Unknown');
                                        $workflowDescription = is_object($workflow) ? ($workflow->workflowDescription ?? '') : ($workflow['workflowDescription'] ?? '');
                                        $functionalArea = is_object($workflow) ? ($workflow->functionalArea ?? '') : ($workflow['functionalArea'] ?? '');
                                        $isActive = is_object($workflow) ? ($workflow->isActive ?? 'N') : ($workflow['isActive'] ?? 'N');
                                        $dateAdded = is_object($workflow) ? ($workflow->DateAdded ?? '') : ($workflow['DateAdded'] ?? '');

                                        // Get step count
                                        $steps = $DBConn->retrieve_db_table_rows('tija_workflow_steps',
                                            ['workflowStepID'],
                                            ['workflowID' => $workflowID]);
                                        $stepCount = is_array($steps) ? count($steps) : 0;
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($workflowName); ?></div>
                                                <?php if (!empty($workflowDescription)): ?>
                                                    <small class="text-muted"><?php echo htmlspecialchars(substr($workflowDescription, 0, 100)); ?>...</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($functionalArea ?: 'N/A'); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $isActive === 'Y' ? 'success' : 'secondary'; ?>">
                                                    <?php echo $isActive === 'Y' ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo $stepCount; ?> steps</span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo !empty($dateAdded) ? date('M d, Y', strtotime($dateAdded)) : 'N/A'; ?>
                                                </small>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-info"
                                                            data-action="view-workflow" data-workflow-id="<?php echo $workflowID; ?>" title="View">
                                                        <i class="ri-eye-line"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-primary"
                                                            data-action="open-workflow-modal" data-workflow-action="edit" data-workflow-id="<?php echo $workflowID; ?>" title="Edit">
                                                        <i class="ri-edit-line"></i>
                                                    </button>
                                                    <a href="?s=admin&ss=operational&p=workflows&action=design&id=<?php echo $workflowID; ?>"
                                                       class="btn btn-sm btn-success" title="Design">
                                                        <i class="ri-node-tree"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                            data-action="delete-workflow" data-workflow-id="<?php echo $workflowID; ?>" title="Delete">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Workflow Wizard Modal -->
<div class="modal fade" id="workflowModal" tabindex="-1" aria-labelledby="workflowModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="workflowModalLabel">Create Workflow</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Wizard Steps Indicator -->
                <ul class="nav nav-pills nav-justified mb-4" id="workflowWizardSteps" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="step1-tab" data-bs-toggle="tab" data-bs-target="#step1" type="button" role="tab" aria-controls="step1" aria-selected="true">
                            <span class="badge bg-primary rounded-pill me-2">1</span> Basic Info
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="step2-tab" data-bs-toggle="tab" data-bs-target="#step2" type="button" role="tab" aria-controls="step2" aria-selected="false" style="pointer-events: none; opacity: 0.6;">
                            <span class="badge bg-secondary rounded-pill me-2">2</span> Add Steps
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="step3-tab" data-bs-toggle="tab" data-bs-target="#step3" type="button" role="tab" aria-controls="step3" aria-selected="false" style="pointer-events: none; opacity: 0.6;">
                            <span class="badge bg-secondary rounded-pill me-2">3</span> Transitions
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="step4-tab" data-bs-toggle="tab" data-bs-target="#step4" type="button" role="tab" aria-controls="step4" aria-selected="false" style="pointer-events: none; opacity: 0.6;">
                            <span class="badge bg-secondary rounded-pill me-2">4</span> Review
                        </button>
                    </li>
                </ul>

                <div id="workflowFormError" class="alert alert-danger d-none" role="alert"></div>

                <div class="tab-content" id="workflowWizardContent">
                    <!-- Step 1: Basic Information -->
                    <div class="tab-pane fade show active" id="step1" role="tabpanel" aria-labelledby="step1-tab">
                        <h6 class="mb-3">Workflow Basic Information</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="workflowName" class="form-label">
                                    Workflow Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="workflowName" name="workflowName" required
                                       placeholder="e.g., Invoice Processing Workflow">
                            </div>

                            <div class="col-md-6">
                                <label for="workflowFunctionalArea" class="form-label">
                                    Functional Area <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="workflowFunctionalArea" name="functionalArea" required>
                                    <option value="">Select Functional Area</option>
                                    <option value="Finance">Finance</option>
                                    <option value="HR">HR</option>
                                    <option value="IT">IT</option>
                                    <option value="Sales">Sales</option>
                                    <option value="Marketing">Marketing</option>
                                    <option value="Legal">Legal</option>
                                    <option value="Facilities">Facilities</option>
                                    <option value="Custom">Custom</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="workflowType" class="form-label">
                                    Workflow Type
                                </label>
                                <select class="form-select" id="workflowType" name="workflowType">
                                    <option value="sequential">Sequential</option>
                                    <option value="parallel">Parallel</option>
                                    <option value="conditional">Conditional</option>
                                    <option value="state_machine">State Machine</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="workflowIsActive" class="form-label">
                                    Status
                                </label>
                                <select class="form-select" id="workflowIsActive" name="isActive">
                                    <option value="Y">Active</option>
                                    <option value="N">Inactive</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="workflowDescription" class="form-label">
                                    Description
                                </label>
                                <textarea class="form-control" id="workflowDescription" name="workflowDescription" rows="4"
                                          placeholder="Describe the workflow, its purpose, and key steps..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Add Steps -->
                    <div class="tab-pane fade" id="step2" role="tabpanel" aria-labelledby="step2-tab">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Workflow Steps</h6>
                            <button type="button" class="btn btn-sm btn-primary" id="addStepBtn">
                                <i class="ri-add-line"></i> Add Step
                            </button>
                        </div>
                        <div id="workflowStepsContainer">
                            <!-- Steps will be added here dynamically -->
                        </div>
                        <div id="noStepsMessage" class="alert alert-info">
                            <i class="ri-information-line"></i> Click "Add Step" to create workflow steps.
                        </div>
                    </div>

                    <!-- Step 3: Configure Transitions -->
                    <div class="tab-pane fade" id="step3" role="tabpanel" aria-labelledby="step3-tab">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Workflow Transitions</h6>
                            <button type="button" class="btn btn-sm btn-primary" id="addTransitionBtn">
                                <i class="ri-add-line"></i> Add Transition
                            </button>
                        </div>
                        <div id="workflowTransitionsContainer">
                            <!-- Transitions will be added here dynamically -->
                        </div>
                        <div id="noTransitionsMessage" class="alert alert-info">
                            <i class="ri-information-line"></i> Add transitions to connect workflow steps.
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="ri-information-line"></i> Transitions define how the workflow moves from one step to another.
                                Sequential workflows typically have transitions from Step N to Step N+1.
                            </small>
                        </div>
                    </div>

                    <!-- Step 4: Review and Save -->
                    <div class="tab-pane fade" id="step4" role="tabpanel" aria-labelledby="step4-tab">
                        <h6 class="mb-3">Review Workflow</h6>
                        <div id="workflowReviewContent">
                            <!-- Review content will be populated here -->
                        </div>
                    </div>
                </div>

                <input type="hidden" id="workflowFormAction" name="action" value="create">
                <input type="hidden" id="workflowFormWorkflowID" name="workflowID" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-secondary" id="workflowWizardPrevBtn" style="display: none;">
                    <i class="ri-arrow-left-line"></i> Previous
                </button>
                <button type="button" class="btn btn-primary" id="workflowWizardNextBtn">
                    Next <i class="ri-arrow-right-line"></i>
                </button>
                <button type="button" class="btn btn-success" id="workflowFormSubmit" style="display: none;">
                    <span class="spinner-border spinner-border-sm d-none" id="workflowFormSpinner" role="status" aria-hidden="true"></span>
                    <span id="workflowFormSubmitText">Save Workflow</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Workflow View Modal -->
<div class="modal fade" id="workflowViewModal" tabindex="-1" aria-labelledby="workflowViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="workflowViewModalLabel">Workflow Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="workflowViewContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading workflow details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="editFromViewBtn" style="display: none;">
                    <i class="ri-edit-line"></i> Edit Workflow
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Native JavaScript table search functionality
    const searchInput = document.getElementById('searchInput');
    const workflowsTable = document.getElementById('workflowsTable');

    if (searchInput && workflowsTable) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = workflowsTable.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                const textContent = row.textContent.toLowerCase();
                if (textContent.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    document.getElementById('statusFilter')?.addEventListener('change', function() {
        const status = this.value;
        const url = new URL(window.location);
        if (status && status !== 'all') {
            url.searchParams.set('status', status);
        } else {
            url.searchParams.delete('status');
        }
        window.location.href = url.toString();
    });

    document.getElementById('functionalAreaFilter')?.addEventListener('change', function() {
        const functionalArea = this.value;
        const url = new URL(window.location);
        if (functionalArea) {
            url.searchParams.set('functionalArea', functionalArea);
        } else {
            url.searchParams.delete('functionalArea');
        }
        window.location.href = url.toString();
    });
});

function viewWorkflow(workflowID) {
    const modalElement = document.getElementById('workflowViewModal');
    const modal = new bootstrap.Modal(modalElement);
    const content = document.getElementById('workflowViewContent');
    const editBtn = document.getElementById('editFromViewBtn');

    // Show loading state
    content.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading workflow details...</p>
        </div>
    `;

    editBtn.style.display = 'none';
    modal.show();

    // Load workflow data
    fetch(`<?php echo $base; ?>php/scripts/operational/workflows/manage_workflow.php?action=get&workflowID=${workflowID}`, {
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.workflow) {
                renderWorkflowView(data.workflow);
                editBtn.style.display = 'inline-block';
                editBtn.onclick = function() {
                    modal.hide();
                    setTimeout(() => {
                        openWorkflowModal('edit', workflowID);
                    }, 300);
                };
            } else {
                content.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="ri-error-warning-line"></i> ${data.message || 'Failed to load workflow details'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading workflow:', error);
            content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line"></i> An error occurred while loading workflow details: ${error.message}
                </div>
            `;
        });
}

function renderWorkflowView(workflow) {
    const content = document.getElementById('workflowViewContent');
    const w = workflow;

    // Helper to check if object
    const isObject = (obj) => {
        return typeof obj === 'object' && obj !== null && !Array.isArray(obj);
    };

    // Get steps and transitions
    const steps = w.steps || [];
    const transitions = w.transitions || [];

    // Helper to get step name by ID
    const getStepName = (stepID) => {
        const step = steps.find(s => {
            const stepIDNum = isObject(s) ? (s.workflowStepID ?? null) : (s['workflowStepID'] ?? null);
            return stepIDNum == stepID;
        });
        if (!step) return 'Unknown Step';
        const stepName = isObject(step) ? (step.stepName ?? 'Unnamed') : (step['stepName'] ?? 'Unnamed');
        const stepOrder = isObject(step) ? (step.stepOrder ?? 0) : (step['stepOrder'] ?? 0);
        return `Step ${stepOrder}: ${stepName}`;
    };

    // Build steps list
    let stepsHTML = '';
    if (steps.length > 0) {
        stepsHTML = '<ol class="mb-0">';
        steps.forEach((step, index) => {
            const stepName = isObject(step) ? (step.stepName ?? 'Unnamed Step') : (step['stepName'] ?? 'Unnamed Step');
            const stepType = isObject(step) ? (step.stepType ?? 'task') : (step['stepType'] ?? 'task');
            const stepOrder = isObject(step) ? (step.stepOrder ?? index + 1) : (step['stepOrder'] ?? index + 1);
            const stepDescription = isObject(step) ? (step.stepDescription ?? '') : (step['stepDescription'] ?? '');
            const estimatedDuration = isObject(step) ? (step.estimatedDuration ?? null) : (step['estimatedDuration'] ?? null);
            const isMandatory = isObject(step) ? (step.isMandatory ?? 'Y') : (step['isMandatory'] ?? 'Y');
            const assigneeType = isObject(step) ? (step.assigneeType ?? 'auto') : (step['assigneeType'] ?? 'auto');

            stepsHTML += `
                <li class="mb-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <strong>${stepName}</strong>
                            <span class="badge bg-info ms-2">${stepType}</span>
                            ${isMandatory === 'Y' ? '<span class="badge bg-warning ms-1">Mandatory</span>' : ''}
                            ${estimatedDuration ? `<span class="badge bg-secondary ms-1">${estimatedDuration}h</span>` : ''}
                            <br>
                            <small class="text-muted">
                                Order: ${stepOrder} | Assignee: ${assigneeType}
                                ${stepDescription ? ` | ${stepDescription}` : ''}
                            </small>
                        </div>
                    </div>
                </li>
            `;
        });
        stepsHTML += '</ol>';
    } else {
        stepsHTML = '<p class="text-muted mb-0"><em>No steps defined</em></p>';
    }

    // Build transitions list
    let transitionsHTML = '';
    if (transitions.length > 0) {
        transitionsHTML = '<ul class="mb-0">';
        transitions.forEach(trans => {
            const fromStepID = isObject(trans) ? (trans.fromStepID ?? null) : (trans['fromStepID'] ?? null);
            const toStepID = isObject(trans) ? (trans.toStepID ?? null) : (trans['toStepID'] ?? null);
            const conditionType = isObject(trans) ? (trans.conditionType ?? 'always') : (trans['conditionType'] ?? 'always');
            const transitionLabel = isObject(trans) ? (trans.transitionLabel ?? '') : (trans['transitionLabel'] ?? '');

            transitionsHTML += `
                <li class="mb-2">
                    <i class="ri-arrow-right-line text-primary"></i>
                    <strong>${getStepName(fromStepID)}</strong>
                    →
                    <strong>${getStepName(toStepID)}</strong>
                    ${transitionLabel ? `<span class="badge bg-secondary ms-2">${transitionLabel}</span>` : ''}
                    <span class="badge bg-info ms-1">${conditionType}</span>
                </li>
            `;
        });
        transitionsHTML += '</ul>';
    } else {
        transitionsHTML = '<p class="text-muted mb-0"><em>No transitions defined</em></p>';
    }

    const workflowName = isObject(w) ? (w.workflowName ?? 'Unknown') : (w['workflowName'] ?? 'Unknown');
    const workflowDescription = isObject(w) ? (w.workflowDescription ?? '') : (w['workflowDescription'] ?? '');
    const functionalArea = isObject(w) ? (w.functionalArea ?? '') : (w['functionalArea'] ?? '');
    const workflowType = isObject(w) ? (w.workflowType ?? 'sequential') : (w['workflowType'] ?? 'sequential');
    const workflowCode = isObject(w) ? (w.workflowCode ?? '') : (w['workflowCode'] ?? '');
    const isActive = isObject(w) ? (w.isActive ?? 'N') : (w['isActive'] ?? 'N');
    const dateAdded = isObject(w) ? (w.DateAdded ?? '') : (w['DateAdded'] ?? '');
    const lastUpdate = isObject(w) ? (w.LastUpdate ?? '') : (w['LastUpdate'] ?? '');

    content.innerHTML = `
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="ri-flow-chart-line me-2"></i>${workflowName}</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Workflow Code:</dt>
                    <dd class="col-sm-9"><code>${workflowCode || 'N/A'}</code></dd>
                    <dt class="col-sm-3">Functional Area:</dt>
                    <dd class="col-sm-9"><span class="badge bg-info">${functionalArea || 'N/A'}</span></dd>
                    <dt class="col-sm-3">Workflow Type:</dt>
                    <dd class="col-sm-9"><span class="badge bg-secondary">${workflowType}</span></dd>
                    <dt class="col-sm-3">Status:</dt>
                    <dd class="col-sm-9">
                        <span class="badge bg-${isActive === 'Y' ? 'success' : 'secondary'}">
                            ${isActive === 'Y' ? 'Active' : 'Inactive'}
                        </span>
                    </dd>
                    ${dateAdded ? `
                    <dt class="col-sm-3">Created:</dt>
                    <dd class="col-sm-9">${new Date(dateAdded).toLocaleString()}</dd>
                    ` : ''}
                    ${lastUpdate ? `
                    <dt class="col-sm-3">Last Updated:</dt>
                    <dd class="col-sm-9">${new Date(lastUpdate).toLocaleString()}</dd>
                    ` : ''}
                    ${workflowDescription ? `
                    <dt class="col-sm-3">Description:</dt>
                    <dd class="col-sm-9">${workflowDescription.replace(/\n/g, '<br>')}</dd>
                    ` : ''}
                </dl>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="ri-list-check me-2"></i>Workflow Steps (${steps.length})
                </h6>
            </div>
            <div class="card-body">
                ${stepsHTML}
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="ri-arrow-right-circle-line me-2"></i>Transitions (${transitions.length})
                </h6>
            </div>
            <div class="card-body">
                ${transitionsHTML}
            </div>
        </div>

        ${steps.length > 0 ? `
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="ri-node-tree me-2"></i>Workflow Flow Diagram
                </h6>
            </div>
            <div class="card-body">
                <div class="workflow-flow-diagram">
                    ${renderWorkflowFlowDiagram(steps, transitions)}
                </div>
            </div>
        </div>
        ` : ''}
    `;
}

function renderWorkflowFlowDiagram(steps, transitions) {
    if (steps.length === 0) {
        return '<p class="text-muted"><em>No steps to display</em></p>';
    }

    // Helper to check if object
    const isObject = (obj) => typeof obj === 'object' && obj !== null && !Array.isArray(obj);

    // Sort steps by order
    const sortedSteps = [...steps].sort((a, b) => {
        const orderA = isObject(a) ? (a.stepOrder ?? 0) : (a['stepOrder'] ?? 0);
        const orderB = isObject(b) ? (b.stepOrder ?? 0) : (b['stepOrder'] ?? 0);
        return orderA - orderB;
    });

    let diagram = '<div class="d-flex flex-wrap align-items-center gap-3">';

    sortedSteps.forEach((step, index) => {
        const stepName = isObject(step) ? (step.stepName ?? 'Unnamed') : (step['stepName'] ?? 'Unnamed');
        const stepType = isObject(step) ? (step.stepType ?? 'task') : (step['stepType'] ?? 'task');
        const stepOrder = isObject(step) ? (step.stepOrder ?? index + 1) : (step['stepOrder'] ?? index + 1);

        if (index > 0) {
            diagram += '<div class="text-center"><i class="ri-arrow-right-line fs-4 text-primary"></i></div>';
        }

        diagram += `
            <div class="card" style="min-width: 200px;">
                <div class="card-body text-center p-3">
                    <div class="fw-bold">${stepName}</div>
                    <small class="text-muted d-block mt-1">Step ${stepOrder}</small>
                    <span class="badge bg-info mt-2">${stepType}</span>
                </div>
            </div>
        `;
    });

    diagram += '</div>';

    return diagram;
}

function deleteWorkflow(workflowID) {
    if (confirm('Are you sure you want to delete this workflow? This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('workflowID', workflowID);

        fetch('<?php echo $base; ?>php/scripts/operational/workflows/manage_workflow.php?action=delete', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (typeof showToast === 'function') {
                    showToast('Workflow deleted successfully', 'success');
                } else {
                    alert('Workflow deleted successfully');
                }
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the workflow');
        });
    }
}

// Event delegation for workflows
document.addEventListener('click', function(e) {
    const action = e.target.closest('[data-action]')?.getAttribute('data-action');
    if (!action) return;

    const element = e.target.closest('[data-action]');

    switch(action) {
        case 'open-workflow-modal':
            const workflowAction = element.getAttribute('data-workflow-action');
            const workflowID = element.getAttribute('data-workflow-id');
            openWorkflowModal(workflowAction, workflowID ? parseInt(workflowID) : null);
            break;

        case 'view-workflow':
            const viewWorkflowID = element.getAttribute('data-workflow-id');
            if (viewWorkflowID) {
                viewWorkflow(parseInt(viewWorkflowID));
            }
            break;

        case 'delete-workflow':
            const deleteWorkflowID = element.getAttribute('data-workflow-id');
            if (deleteWorkflowID) {
                deleteWorkflow(parseInt(deleteWorkflowID));
            }
            break;
    }
});

// Wizard state
let currentWizardStep = 1;
let workflowSteps = [];
let workflowTransitions = [];
let currentWorkflowID = null;
let isEditMode = false;

function openWorkflowModal(action, workflowID = null) {
    const modalElement = document.getElementById('workflowModal');
    const modal = new bootstrap.Modal(modalElement);
    const modalTitle = document.getElementById('workflowModalLabel');
    const formAction = document.getElementById('workflowFormAction');
    const formWorkflowID = document.getElementById('workflowFormWorkflowID');
    const errorDiv = document.getElementById('workflowFormError');

    // Reset wizard state
    currentWizardStep = 1;
    workflowSteps = [];
    workflowTransitions = [];
    currentWorkflowID = workflowID;
    isEditMode = action === 'edit';

    // Reset form and UI
    errorDiv.classList.add('d-none');
    errorDiv.textContent = '';
    formAction.value = action;
    formWorkflowID.value = workflowID || '';

    // Reset all wizard steps
    resetWizardSteps();
    showWizardStep(1);

    if (action === 'create') {
        modalTitle.textContent = 'Create Workflow - Wizard';
        document.getElementById('workflowIsActive').value = 'Y';
        document.getElementById('workflowType').value = 'sequential';

        modal.show();

        // Ensure first step is visible after modal is shown
        modalElement.addEventListener('shown.bs.modal', function ensureFirstStep() {
            const step1Pane = document.getElementById('step1');
            const step1Tab = document.getElementById('step1-tab');

            if (step1Pane) {
                step1Pane.classList.add('show', 'active');
                step1Pane.style.display = 'block';
            }

            if (step1Tab) {
                step1Tab.classList.add('active');
                step1Tab.setAttribute('aria-selected', 'true');
            }

            // Hide other steps
            ['step2', 'step3', 'step4'].forEach(stepId => {
                const pane = document.getElementById(stepId);
                if (pane) {
                    pane.classList.remove('show', 'active');
                    pane.style.display = 'none';
                }
            });

            // Remove listener after first use
            modalElement.removeEventListener('shown.bs.modal', ensureFirstStep);
        }, { once: true });
    } else if (action === 'edit' && workflowID) {
        modalTitle.textContent = 'Edit Workflow - Wizard';
        modal.show();

        // Load workflow data
        loadWorkflowForEdit(workflowID);
    }
}

function resetWizardSteps() {
    // Reset step 1
    document.getElementById('workflowName').value = '';
    document.getElementById('workflowDescription').value = '';
    document.getElementById('workflowFunctionalArea').value = '';
    document.getElementById('workflowIsActive').value = 'Y';
    document.getElementById('workflowType').value = 'sequential';

    // Clear steps
    document.getElementById('workflowStepsContainer').innerHTML = '';
    document.getElementById('noStepsMessage').style.display = '';

    // Clear transitions
    document.getElementById('workflowTransitionsContainer').innerHTML = '';
    document.getElementById('noTransitionsMessage').style.display = '';

    // Reset wizard navigation
    const stepTabs = document.querySelectorAll('#workflowWizardSteps .nav-link');
    stepTabs.forEach((tab, index) => {
        if (index === 0) {
            tab.classList.add('active');
            tab.setAttribute('aria-selected', 'true');
            tab.disabled = false;
            const badge = tab.querySelector('.badge');
            if (badge) {
                badge.classList.remove('bg-secondary', 'bg-success');
                badge.classList.add('bg-primary');
            }
        } else {
            tab.classList.remove('active');
            tab.setAttribute('aria-selected', 'false');
            tab.style.pointerEvents = 'none';
            tab.style.opacity = '0.6';
            const badge = tab.querySelector('.badge');
            if (badge) {
                badge.classList.remove('bg-primary', 'bg-success');
                badge.classList.add('bg-secondary');
            }
        }
    });

    // Show first step pane, hide others
    const stepPanes = document.querySelectorAll('#workflowWizardContent .tab-pane');
    stepPanes.forEach((pane, index) => {
        if (index === 0) {
            pane.classList.add('show', 'active');
        } else {
            pane.classList.remove('show', 'active');
        }
    });

    // Reset navigation buttons
    document.getElementById('workflowWizardPrevBtn').style.display = 'none';
    document.getElementById('workflowWizardNextBtn').style.display = 'inline-block';
    document.getElementById('workflowFormSubmit').style.display = 'none';
}

function loadWorkflowForEdit(workflowID) {
    fetch(`<?php echo $base; ?>php/scripts/operational/workflows/manage_workflow.php?action=get&workflowID=${workflowID}`, {
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.workflow) {
                const w = data.workflow;

                // Populate step 1
                document.getElementById('workflowName').value = w.workflowName || '';
                document.getElementById('workflowDescription').value = w.workflowDescription || '';
                document.getElementById('workflowFunctionalArea').value = w.functionalArea || '';
                document.getElementById('workflowIsActive').value = w.isActive || 'Y';
                document.getElementById('workflowType').value = w.workflowType || 'sequential';

                // Load steps
                if (w.steps && Array.isArray(w.steps)) {
                    workflowSteps = w.steps.map((step, index) => ({
                        ...step,
                        tempID: 'step_' + (index + 1)
                    }));
                    renderWorkflowSteps();
                }

                // Load transitions
                if (w.transitions && Array.isArray(w.transitions)) {
                    workflowTransitions = w.transitions.map((trans, index) => ({
                        ...trans,
                        tempID: 'trans_' + (index + 1)
                    }));
                    renderWorkflowTransitions();
                }
            } else {
                showWorkflowError(data.message || 'Failed to load workflow data');
            }
        })
        .catch(error => {
            console.error('Error loading workflow data:', error);
            showWorkflowError('Failed to load workflow data: ' + error.message);
        });
}

function showWizardStep(step) {
    currentWizardStep = step;

    // Update step tabs
    const stepTabs = document.querySelectorAll('#workflowWizardSteps .nav-link');
    stepTabs.forEach((tab, index) => {
        const stepNum = index + 1;
        if (stepNum === step) {
            tab.classList.add('active');
            tab.setAttribute('aria-selected', 'true');
            tab.style.pointerEvents = '';
            tab.style.opacity = '';
            const badge = tab.querySelector('.badge');
            if (badge) {
                badge.classList.remove('bg-secondary', 'bg-success');
                badge.classList.add('bg-primary');
            }
        } else if (stepNum < step) {
            tab.classList.remove('active');
            tab.setAttribute('aria-selected', 'false');
            tab.style.pointerEvents = '';
            tab.style.opacity = '';
            const badge = tab.querySelector('.badge');
            if (badge) {
                badge.classList.remove('bg-primary', 'bg-secondary');
                badge.classList.add('bg-success');
            }
        } else {
            tab.classList.remove('active');
            tab.setAttribute('aria-selected', 'false');
            if (stepNum > step) {
                tab.style.pointerEvents = 'none';
                tab.style.opacity = '0.6';
            } else {
                tab.style.pointerEvents = '';
                tab.style.opacity = '';
            }
            const badge = tab.querySelector('.badge');
            if (badge) {
                badge.classList.remove('bg-primary', 'bg-success');
                badge.classList.add('bg-secondary');
            }
        }
    });

    // Show/hide navigation buttons
    document.getElementById('workflowWizardPrevBtn').style.display = step > 1 ? 'inline-block' : 'none';
    document.getElementById('workflowWizardNextBtn').style.display = step < 4 ? 'inline-block' : 'none';
    document.getElementById('workflowFormSubmit').style.display = step === 4 ? 'inline-block' : 'none';

    // Show the correct tab pane
    const stepPanes = document.querySelectorAll('#workflowWizardContent .tab-pane');
    stepPanes.forEach((pane, index) => {
        const stepNum = index + 1;
        if (stepNum === step) {
            pane.classList.add('show', 'active');
        } else {
            pane.classList.remove('show', 'active');
        }
    });

    // Trigger Bootstrap tab show programmatically
    const tabElement = document.querySelector(`#step${step}-tab`);
    if (tabElement && tabElement.style.pointerEvents !== 'none') {
        try {
            const tab = new bootstrap.Tab(tabElement);
            tab.show();
        } catch (e) {
            // Fallback: manually show the tab pane
            const pane = document.getElementById(`step${step}`);
            if (pane) {
                pane.classList.add('show', 'active');
                pane.style.display = 'block';
            }
        }
    } else {
        // Fallback: manually show the tab pane
        const pane = document.getElementById(`step${step}`);
        if (pane) {
            pane.classList.add('show', 'active');
            pane.style.display = 'block';
        }
    }

    // Special handling for review step
    if (step === 4) {
        renderWorkflowReview();
    }
}

function validateStep1() {
    const name = document.getElementById('workflowName').value.trim();
    const functionalArea = document.getElementById('workflowFunctionalArea').value;

    if (!name) {
        showWorkflowError('Workflow name is required');
        return false;
    }
    if (!functionalArea) {
        showWorkflowError('Functional area is required');
        return false;
    }
    return true;
}

function validateStep2() {
    if (workflowSteps.length === 0) {
        showWorkflowError('Please add at least one workflow step');
        return false;
    }

    // Validate each step
    for (let i = 0; i < workflowSteps.length; i++) {
        const step = workflowSteps[i];
        if (!step.stepName || !step.stepName.trim()) {
            showWorkflowError(`Step ${i + 1} must have a name`);
            return false;
        }
    }
    return true;
}

function showWorkflowError(message) {
    const errorDiv = document.getElementById('workflowFormError');
    errorDiv.textContent = message;
    errorDiv.classList.remove('d-none');
    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// Step Management
function addWorkflowStep() {
    const stepNum = workflowSteps.length + 1;
    const newStep = {
        tempID: 'step_' + Date.now(),
        stepOrder: stepNum,
        stepName: '',
        stepDescription: '',
        stepType: 'task',
        assigneeType: 'auto',
        assigneeRoleID: null,
        assigneeEmployeeID: null,
        estimatedDuration: null,
        isMandatory: 'Y',
        stepConfig: null
    };
    workflowSteps.push(newStep);
    renderWorkflowSteps();
}

function removeWorkflowStep(tempID) {
    workflowSteps = workflowSteps.filter(s => s.tempID !== tempID);
    // Reorder steps
    workflowSteps.forEach((step, index) => {
        step.stepOrder = index + 1;
    });
    renderWorkflowSteps();
    renderWorkflowTransitions(); // Re-render transitions as step references may have changed
}

function updateWorkflowStep(tempID, field, value) {
    const step = workflowSteps.find(s => s.tempID === tempID);
    if (step) {
        step[field] = value;
        if (field === 'stepOrder') {
            step.stepOrder = parseInt(value) || 1;
            // Sort steps by order
            workflowSteps.sort((a, b) => a.stepOrder - b.stepOrder);
            renderWorkflowSteps();
        }
    }
}

function renderWorkflowSteps() {
    const container = document.getElementById('workflowStepsContainer');
    const noStepsMsg = document.getElementById('noStepsMessage');

    if (workflowSteps.length === 0) {
        container.innerHTML = '';
        noStepsMsg.style.display = '';
        return;
    }

    noStepsMsg.style.display = 'none';
    container.innerHTML = workflowSteps.map((step, index) => `
        <div class="card mb-3 step-item" data-temp-id="${step.tempID}">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Step ${step.stepOrder}</h6>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeWorkflowStep('${step.tempID}')">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Order</label>
                        <input type="number" class="form-control form-control-sm"
                               value="${step.stepOrder}" min="1"
                               onchange="updateWorkflowStep('${step.tempID}', 'stepOrder', this.value)">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Step Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm"
                               value="${step.stepName || ''}" required
                               onchange="updateWorkflowStep('${step.tempID}', 'stepName', this.value)"
                               placeholder="e.g., Submit Invoice">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Step Type</label>
                        <select class="form-select form-select-sm"
                                onchange="updateWorkflowStep('${step.tempID}', 'stepType', this.value)">
                            <option value="task" ${step.stepType === 'task' ? 'selected' : ''}>Task</option>
                            <option value="approval" ${step.stepType === 'approval' ? 'selected' : ''}>Approval</option>
                            <option value="decision" ${step.stepType === 'decision' ? 'selected' : ''}>Decision</option>
                            <option value="notification" ${step.stepType === 'notification' ? 'selected' : ''}>Notification</option>
                            <option value="automation" ${step.stepType === 'automation' ? 'selected' : ''}>Automation</option>
                            <option value="subprocess" ${step.stepType === 'subprocess' ? 'selected' : ''}>Subprocess</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Assignee Type</label>
                        <select class="form-select form-select-sm"
                                onchange="updateWorkflowStep('${step.tempID}', 'assigneeType', this.value)">
                            <option value="auto" ${step.assigneeType === 'auto' ? 'selected' : ''}>Auto</option>
                            <option value="role" ${step.assigneeType === 'role' ? 'selected' : ''}>Role</option>
                            <option value="employee" ${step.assigneeType === 'employee' ? 'selected' : ''}>Employee</option>
                            <option value="function_head" ${step.assigneeType === 'function_head' ? 'selected' : ''}>Function Head</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Description</label>
                        <textarea class="form-control form-control-sm" rows="2"
                                  onchange="updateWorkflowStep('${step.tempID}', 'stepDescription', this.value)"
                                  placeholder="Step description...">${step.stepDescription || ''}</textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estimated Duration (hours)</label>
                        <input type="number" class="form-control form-control-sm" step="0.5"
                               value="${step.estimatedDuration || ''}"
                               onchange="updateWorkflowStep('${step.tempID}', 'estimatedDuration', this.value)">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Mandatory</label>
                        <select class="form-select form-select-sm"
                                onchange="updateWorkflowStep('${step.tempID}', 'isMandatory', this.value)">
                            <option value="Y" ${step.isMandatory === 'Y' ? 'selected' : ''}>Yes</option>
                            <option value="N" ${step.isMandatory === 'N' ? 'selected' : ''}>No</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

// Transition Management
function addWorkflowTransition() {
    if (workflowSteps.length < 2) {
        showWorkflowError('Add at least 2 steps before creating transitions');
        return;
    }

    const newTransition = {
        tempID: 'trans_' + Date.now(),
        fromStepID: workflowSteps[0].workflowStepID || workflowSteps[0].tempID,
        toStepID: workflowSteps[1]?.workflowStepID || workflowSteps[1]?.tempID,
        conditionType: 'always',
        conditionExpression: null,
        transitionLabel: ''
    };
    workflowTransitions.push(newTransition);
    renderWorkflowTransitions();
}

function removeWorkflowTransition(tempID) {
    workflowTransitions = workflowTransitions.filter(t => t.tempID !== tempID);
    renderWorkflowTransitions();
}

function updateWorkflowTransition(tempID, field, value) {
    const transition = workflowTransitions.find(t => t.tempID === tempID);
    if (transition) {
        transition[field] = value;
    }
}

function renderWorkflowTransitions() {
    const container = document.getElementById('workflowTransitionsContainer');
    const noTransMsg = document.getElementById('noTransitionsMessage');

    if (workflowSteps.length < 2) {
        container.innerHTML = '';
        noTransMsg.style.display = '';
        noTransMsg.innerHTML = '<i class="ri-information-line"></i> Add at least 2 steps before creating transitions.';
        return;
    }

    if (workflowTransitions.length === 0) {
        container.innerHTML = '';
        noTransMsg.style.display = '';
        noTransMsg.innerHTML = '<i class="ri-information-line"></i> Add transitions to connect workflow steps.';
        return;
    }

    noTransMsg.style.display = 'none';

    // Helper function to get step name
    const getStepName = (stepID) => {
        const step = workflowSteps.find(s =>
            (s.workflowStepID && s.workflowStepID == stepID) ||
            (s.tempID === stepID)
        );
        return step ? `Step ${step.stepOrder}: ${step.stepName}` : 'Unknown Step';
    };

    container.innerHTML = workflowTransitions.map((trans, index) => `
        <div class="card mb-3 transition-item" data-temp-id="${trans.tempID}">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Transition ${index + 1}</h6>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeWorkflowTransition('${trans.tempID}')">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">From Step <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" required
                                onchange="updateWorkflowTransition('${trans.tempID}', 'fromStepID', this.value)">
                            <option value="">Select Step</option>
                            ${workflowSteps.map(s => {
                                const stepID = s.workflowStepID || s.tempID;
                                return `<option value="${stepID}" ${(trans.fromStepID == stepID || trans.fromStepID === s.tempID) ? 'selected' : ''}>
                                    Step ${s.stepOrder}: ${s.stepName || 'Unnamed'}
                                </option>`;
                            }).join('')}
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">To Step <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" required
                                onchange="updateWorkflowTransition('${trans.tempID}', 'toStepID', this.value)">
                            <option value="">Select Step</option>
                            ${workflowSteps.map(s => {
                                const stepID = s.workflowStepID || s.tempID;
                                return `<option value="${stepID}" ${(trans.toStepID == stepID || trans.toStepID === s.tempID) ? 'selected' : ''}>
                                    Step ${s.stepOrder}: ${s.stepName || 'Unnamed'}
                                </option>`;
                            }).join('')}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Condition</label>
                        <select class="form-select form-select-sm"
                                onchange="updateWorkflowTransition('${trans.tempID}', 'conditionType', this.value)">
                            <option value="always" ${trans.conditionType === 'always' ? 'selected' : ''}>Always</option>
                            <option value="conditional" ${trans.conditionType === 'conditional' ? 'selected' : ''}>Conditional</option>
                            <option value="time_based" ${trans.conditionType === 'time_based' ? 'selected' : ''}>Time Based</option>
                            <option value="event_based" ${trans.conditionType === 'event_based' ? 'selected' : ''}>Event Based</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Transition Label (optional)</label>
                        <input type="text" class="form-control form-control-sm"
                               value="${trans.transitionLabel || ''}"
                               onchange="updateWorkflowTransition('${trans.tempID}', 'transitionLabel', this.value)"
                               placeholder="e.g., Approve, Reject, Continue">
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function renderWorkflowReview() {
    const container = document.getElementById('workflowReviewContent');

    const workflowName = document.getElementById('workflowName').value;
    const workflowDescription = document.getElementById('workflowDescription').value;
    const functionalArea = document.getElementById('workflowFunctionalArea').value;
    const workflowType = document.getElementById('workflowType').value;
    const isActive = document.getElementById('workflowIsActive').value;

    container.innerHTML = `
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">Workflow Information</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Name:</dt>
                    <dd class="col-sm-9">${workflowName || 'Not set'}</dd>
                    <dt class="col-sm-3">Functional Area:</dt>
                    <dd class="col-sm-9">${functionalArea || 'Not set'}</dd>
                    <dt class="col-sm-3">Type:</dt>
                    <dd class="col-sm-9">${workflowType}</dd>
                    <dt class="col-sm-3">Status:</dt>
                    <dd class="col-sm-9"><span class="badge bg-${isActive === 'Y' ? 'success' : 'secondary'}">${isActive === 'Y' ? 'Active' : 'Inactive'}</span></dd>
                    ${workflowDescription ? `
                    <dt class="col-sm-3">Description:</dt>
                    <dd class="col-sm-9">${workflowDescription}</dd>
                    ` : ''}
                </dl>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">Workflow Steps (${workflowSteps.length})</h6>
            </div>
            <div class="card-body">
                ${workflowSteps.length > 0 ? `
                    <ol class="mb-0">
                        ${workflowSteps.map(step => `
                            <li>
                                <strong>${step.stepName || 'Unnamed Step'}</strong>
                                <span class="badge bg-info ms-2">${step.stepType}</span>
                                ${step.isMandatory === 'Y' ? '<span class="badge bg-warning ms-1">Mandatory</span>' : ''}
                                ${step.estimatedDuration ? `<span class="badge bg-secondary ms-1">${step.estimatedDuration}h</span>` : ''}
                                ${step.stepDescription ? `<br><small class="text-muted">${step.stepDescription}</small>` : ''}
                            </li>
                        `).join('')}
                    </ol>
                ` : '<p class="text-muted mb-0">No steps defined</p>'}
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">Transitions (${workflowTransitions.length})</h6>
            </div>
            <div class="card-body">
                ${workflowTransitions.length > 0 ? `
                    <ul class="mb-0">
                        ${workflowTransitions.map(trans => {
                            const fromStep = workflowSteps.find(s =>
                                (s.workflowStepID && s.workflowStepID == trans.fromStepID) ||
                                (s.tempID === trans.fromStepID)
                            );
                            const toStep = workflowSteps.find(s =>
                                (s.workflowStepID && s.workflowStepID == trans.toStepID) ||
                                (s.tempID === trans.toStepID)
                            );
                            return `
                                <li>
                                    <i class="ri-arrow-right-line"></i>
                                    ${fromStep ? `Step ${fromStep.stepOrder}: ${fromStep.stepName}` : 'Unknown'}
                                    →
                                    ${toStep ? `Step ${toStep.stepOrder}: ${toStep.stepName}` : 'Unknown'}
                                    ${trans.transitionLabel ? `<span class="badge bg-secondary ms-2">${trans.transitionLabel}</span>` : ''}
                                    <span class="badge bg-info ms-1">${trans.conditionType}</span>
                                </li>
                            `;
                        }).join('')}
                    </ul>
                ` : '<p class="text-muted mb-0">No transitions defined</p>'}
            </div>
        </div>
    `;
}

// Wizard Navigation
document.getElementById('workflowWizardNextBtn').addEventListener('click', function() {
    let valid = true;

    if (currentWizardStep === 1) {
        valid = validateStep1();
    } else if (currentWizardStep === 2) {
        valid = validateStep2();
    } else if (currentWizardStep === 3) {
        // Step 3 (transitions) is optional, but we can validate if steps exist
        if (workflowSteps.length < 2) {
            showWorkflowError('Add at least 2 steps before proceeding');
            valid = false;
        }
    }

    if (valid) {
        document.getElementById('workflowFormError').classList.add('d-none');
        if (currentWizardStep < 4) {
            // Save workflow first if creating new
            if (currentWizardStep === 1 && !currentWorkflowID && document.getElementById('workflowFormAction').value === 'create') {
                saveWorkflowBasic().then(workflowID => {
                    if (workflowID) {
                        currentWorkflowID = workflowID;
                        document.getElementById('workflowFormWorkflowID').value = workflowID;
                        showWizardStep(2);
                    }
                });
            } else {
                showWizardStep(currentWizardStep + 1);
            }
        }
    }
});

document.getElementById('workflowWizardPrevBtn').addEventListener('click', function() {
    if (currentWizardStep > 1) {
        showWizardStep(currentWizardStep - 1);
    }
});

// Add step button
document.getElementById('addStepBtn').addEventListener('click', function() {
    addWorkflowStep();
});

// Add transition button
document.getElementById('addTransitionBtn').addEventListener('click', function() {
    addWorkflowTransition();
});

// Save workflow basic info (step 1)
function saveWorkflowBasic() {
    return new Promise((resolve, reject) => {
        const formData = new FormData();
        formData.append('action', 'create');
        formData.append('workflowName', document.getElementById('workflowName').value);
        formData.append('workflowDescription', document.getElementById('workflowDescription').value);
        formData.append('functionalArea', document.getElementById('workflowFunctionalArea').value);
        formData.append('workflowType', document.getElementById('workflowType').value);
        formData.append('isActive', document.getElementById('workflowIsActive').value);

        fetch(`<?php echo $base; ?>php/scripts/operational/workflows/manage_workflow.php?action=create`, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.workflowID) {
                    resolve(data.workflowID);
                } else {
                    showWorkflowError(data.message || 'Failed to create workflow');
                    reject(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showWorkflowError('An error occurred while saving workflow');
                reject(error);
            });
    });
}

// Final save function
async function saveCompleteWorkflow() {
    const submitBtn = document.getElementById('workflowFormSubmit');
    const spinner = document.getElementById('workflowFormSpinner');
    const submitText = document.getElementById('workflowFormSubmitText');
    const errorDiv = document.getElementById('workflowFormError');

    const action = document.getElementById('workflowFormAction').value;
    let workflowID = currentWorkflowID || document.getElementById('workflowFormWorkflowID').value;

    // Show loading state
    submitBtn.disabled = true;
    spinner.classList.remove('d-none');
    errorDiv.classList.add('d-none');

    try {
        // Step 1: Save/Update workflow basic info if needed
        if (action === 'create' && !workflowID) {
            workflowID = await saveWorkflowBasic();
            currentWorkflowID = workflowID;
        } else if (action === 'update' && workflowID) {
            // Update workflow info
            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('workflowID', workflowID);
            formData.append('workflowName', document.getElementById('workflowName').value);
            formData.append('workflowDescription', document.getElementById('workflowDescription').value);
            formData.append('functionalArea', document.getElementById('workflowFunctionalArea').value);
            formData.append('workflowType', document.getElementById('workflowType').value);
            formData.append('isActive', document.getElementById('workflowIsActive').value);

            const updateResponse = await fetch(`<?php echo $base; ?>php/scripts/operational/workflows/manage_workflow.php?action=update`, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            const updateData = await updateResponse.json();
            if (!updateData.success) {
                throw new Error(updateData.message || 'Failed to update workflow');
            }
        }

        // Step 2: Save steps
        for (const step of workflowSteps) {
            const stepData = new FormData();
            if (step.workflowStepID && isEditMode) {
                // Update existing step
                stepData.append('action', 'update_step');
                stepData.append('stepID', step.workflowStepID);
            } else {
                // Add new step
                stepData.append('action', 'add_step');
                stepData.append('workflowID', workflowID);
            }

            stepData.append('stepOrder', step.stepOrder);
            stepData.append('stepName', step.stepName);
            stepData.append('stepDescription', step.stepDescription || '');
            stepData.append('stepType', step.stepType);
            stepData.append('assigneeType', step.assigneeType);
            if (step.assigneeRoleID) stepData.append('assigneeRoleID', step.assigneeRoleID);
            if (step.assigneeEmployeeID) stepData.append('assigneeEmployeeID', step.assigneeEmployeeID);
            if (step.estimatedDuration) stepData.append('estimatedDuration', step.estimatedDuration);
            stepData.append('isMandatory', step.isMandatory);
            if (step.stepConfig) stepData.append('stepConfig', JSON.stringify(step.stepConfig));

            const stepResponse = await fetch(`<?php echo $base; ?>php/scripts/operational/workflows/manage_workflow.php?action=${step.workflowStepID && isEditMode ? 'update_step' : 'add_step'}`, {
                method: 'POST',
                body: stepData,
                credentials: 'same-origin'
            });
            const stepResult = await stepResponse.json();
            if (!stepResult.success) {
                throw new Error(stepResult.message || 'Failed to save step');
            }

            // Store the returned stepID for transitions
            if (stepResult.stepID) {
                step.workflowStepID = stepResult.stepID;
            }
        }

        // Step 3: Save transitions (need actual stepIDs from database)
        // First, reload steps to get actual IDs
        const workflowResponse = await fetch(`<?php echo $base; ?>php/scripts/operational/workflows/manage_workflow.php?action=get&workflowID=${workflowID}`, {
            credentials: 'same-origin'
        });
        const workflowData = await workflowResponse.json();

        if (workflowData.success && workflowData.workflow.steps) {
            // Map temp step IDs to actual step IDs
            const stepMap = new Map();
            workflowSteps.forEach((tempStep, index) => {
                const actualStep = workflowData.workflow.steps[index];
                if (actualStep) {
                    stepMap.set(tempStep.tempID, actualStep.workflowStepID);
                }
            });

            // Save transitions
            for (const trans of workflowTransitions) {
                const fromStepID = stepMap.get(trans.fromStepID) || trans.fromStepID;
                const toStepID = stepMap.get(trans.toStepID) || trans.toStepID;

                if (!trans.transitionID || !isEditMode) {
                    const transData = new FormData();
                    transData.append('action', 'add_transition');
                    transData.append('workflowID', workflowID);
                    transData.append('fromStepID', fromStepID);
                    transData.append('toStepID', toStepID);
                    transData.append('conditionType', trans.conditionType);
                    transData.append('transitionLabel', trans.transitionLabel || '');
                    if (trans.conditionExpression) {
                        transData.append('conditionExpression', JSON.stringify(trans.conditionExpression));
                    }

                    const transResponse = await fetch(`<?php echo $base; ?>php/scripts/operational/workflows/manage_workflow.php?action=add_transition`, {
                        method: 'POST',
                        body: transData,
                        credentials: 'same-origin'
                    });
                    const transResult = await transResponse.json();
                    if (!transResult.success) {
                        throw new Error(transResult.message || 'Failed to save transition');
                    }
                }
            }
        }

        // Success!
        if (typeof showToast === 'function') {
            showToast('Workflow saved successfully', 'success');
        } else {
            alert('Workflow saved successfully');
        }

        closeModalAndRemoveBackdrop('workflowModal');
        setTimeout(() => {
            window.location.reload();
        }, 500);

    } catch (error) {
        console.error('Error saving workflow:', error);
        showWorkflowError(error.message || 'An error occurred while saving the workflow');
        submitBtn.disabled = false;
        spinner.classList.add('d-none');
    }
}

// Handle final form submission
document.getElementById('workflowFormSubmit').addEventListener('click', function() {
    if (!validateStep1() || !validateStep2()) {
        return;
    }
    saveCompleteWorkflow();
});

// Properly close modal and remove backdrop
function closeModalAndRemoveBackdrop(modalElementOrId) {
    const modalElement = typeof modalElementOrId === 'string'
        ? document.getElementById(modalElementOrId)
        : modalElementOrId;

    if (!modalElement) {
        return;
    }

    const modal = bootstrap.Modal.getInstance(modalElement);

    if (modal) {
        // Hide the modal
        modal.hide();

        // Wait for modal to fully hide, then ensure backdrop is removed
        modalElement.addEventListener('hidden.bs.modal', function cleanupModal() {
            // Remove backdrop if it exists
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }

            // Remove modal-open class from body
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';

            // Dispose of modal instance to prevent memory leaks
            modal.dispose();

            // Remove event listener
            modalElement.removeEventListener('hidden.bs.modal', cleanupModal);
        }, { once: true });
    } else {
        // If no modal instance exists, manually clean up
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    }
}

// Global listener to ensure backdrop is removed when any modal closes
document.addEventListener('hidden.bs.modal', function(e) {
    // Wait a bit to ensure Bootstrap has finished its cleanup
    setTimeout(function() {
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }

        // Ensure body classes are cleaned up
        if (document.querySelectorAll('.modal.show').length === 0) {
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }
    }, 50);
});
</script>

