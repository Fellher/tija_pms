<?php
/**
 * Leave Approval Workflow Manager
 * Dynamic, entity-specific approval policy configuration interface
 *
 * @version 1.0
 * @date 2025-10-21
 */

// Get current entity and organization
$currentEntityID = isset($_GET['entityID']) ? intval(Utility::clean_string($_GET['entityID'])) : (isset($userDetails->entityID) ? intval($userDetails->entityID) : intval($_SESSION['entityID'] ?? 1));
$currentOrgDataID = isset($_GET['orgDataID']) ? intval(Utility::clean_string($_GET['orgDataID'])) : (isset($userDetails->orgDataID) ? intval($userDetails->orgDataID) : intval($_SESSION['orgDataID'] ?? 1));
$action = isset($_GET['workflow_action']) ? Utility::clean_string($_GET['workflow_action']) : 'list';
$policyID = isset($_GET['policyID']) ? Utility::clean_string($_GET['policyID']) : null;

// Initialize required data arrays
// Get all entities for the organization (scoped to orgDataID if HR manager)
try {
    if (isset($isHRManager) && $isHRManager && isset($userDetails) && isset($userDetails->orgDataID)) {
        $allEntities = Data::entities_full(array('orgDataID' => $userDetails->orgDataID, 'Suspended' => 'N'), false, $DBConn);
    } else {
        $allEntities = Data::entities_full(array('orgDataID' => $currentOrgDataID, 'Suspended' => 'N'), false, $DBConn);
    }
    if (!is_array($allEntities)) {
        $allEntities = array();
    }
} catch (Exception $e) {
    $allEntities = array();
}

// Get all employees for the current entity/organization
try {
    if (isset($isHRManager) && $isHRManager && isset($userDetails) && isset($userDetails->entityID) && isset($userDetails->orgDataID)) {
        // HR managers are scoped to their entity
        $allEmployees = Employee::get_all_employees($userDetails->orgDataID, $userDetails->entityID, $DBConn);
    } else {
        // For admins, get employees for current entity
        $allEmployees = Employee::get_all_employees($currentOrgDataID, $currentEntityID, $DBConn);
    }
    if (!is_array($allEmployees)) {
        $allEmployees = array();
    }
} catch (Exception $e) {
    $allEmployees = array();
}

// Get current tab (workflows or templates)
$currentWorkflowTab = (isset($_GET['workflow_tab']) && !empty($_GET['workflow_tab'])) ? Utility::clean_string($_GET['workflow_tab']) : 'workflows';

// Define tabs array for navigation
$workflowTabsArray = [
    (object)[
        "title" => "Workflows",
        "icon" => "ri-flow-chart",
        "slug" => "workflows",
        "active" => $currentWorkflowTab == "workflows"
    ],
    (object)[
        "title" => "Manage Templates",
        "icon" => "ri-file-list-3-line",
        "slug" => "templates",
        "active" => $currentWorkflowTab == "templates"
    ]
];

?>

<!-- Approval Workflow Manager Styles -->
<style>
.workflow-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    transition: all 0.3s ease;
}

.workflow-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.workflow-card.active {
    border-color: #28a745;
    background: #f0fff4;
}

.workflow-card.default {
    border-color: #6c5ce7;
    background: #f5f3ff;
}

.step-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #6c5ce7;
    color: white;
    font-weight: 600;
    margin-right: 10px;
}

.step-item {
    display: flex;
    align-items: flex-start;
    padding: 15px;
    background: #f9f9f9;
    border-left: 3px solid #6c5ce7;
    margin-bottom: 10px;
    border-radius: 4px;
}

.step-type-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    margin-left: 10px;
}

.step-type-supervisor { background: #e3f2fd; color: #1976d2; }
.step-type-project_manager { background: #f3e5f5; color: #7b1fa2; }
.step-type-hr_manager { background: #e8f5e9; color: #388e3c; }
.step-type-department_head { background: #fff3e0; color: #f57c00; }

.template-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.template-card {
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.template-card:hover {
    border-color: #6c5ce7;
    box-shadow: 0 4px 12px rgba(108,92,231,0.2);
}

.template-card.system {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.entity-selector {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}
</style>

<!-- Navigation Tabs for Workflows and Templates -->
<ul class="nav nav-tabs nav-tabs-header mb-0" role="tablist">
    <?php foreach ($workflowTabsArray as $tab):
        // Build URL with current parameters
        $tabParams = $_GET;
        $tabParams['workflow_tab'] = $tab->slug;
        unset($tabParams['workflow_action']); // Reset action when switching tabs
        unset($tabParams['policyID']); // Reset policyID when switching tabs
        $tabUrl = '?' . http_build_query($tabParams);
        $activeClass = $tab->active ? 'active' : '';
    ?>
    <li class="nav-item" role="presentation">
        <a href="<?= $tabUrl ?>" class="nav-link <?= $activeClass ?>">
            <i class="<?= $tab->icon ?> me-1"></i><?= $tab->title ?>
        </a>
    </li>
    <?php endforeach; ?>
</ul>

<!-- Tab Content -->
<?php if ($currentWorkflowTab == 'workflows'): ?>
    <!-- Workflows Tab -->
    <div id="workflowsContent">
        <!-- Main Workflow Manager Container -->
        <div class="card custom-card border-0 rounded-0 rounded-bottom">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    <i class="ri-flow-chart me-2"></i>
                    Leave Approval Workflow Manager
                    <button type="button" class="btn btn-sm btn-info-light ms-2" data-bs-toggle="modal" data-bs-target="#workflowGuideModal" title="User Guide - How to Configure Workflows">
                        <i class="ri-question-line"></i>
                    </button>
                </h4>
                <div class="btn-group">
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createWorkflowModal">
                        <i class="ri-add-line me-1"></i>
                        Create New Workflow
                    </button>
                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#templatesModal">
                        <i class="ri-file-copy-line me-1"></i>
                        Browse Templates
                    </button>
                </div>
            </div>

    <div class="card-body">
        <!-- Entity Selector -->
        <div class="entity-selector">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        <i class="ri-building-line me-1"></i>
                        Select Entity
                    </label>
                    <select class="form-select" id="entitySelector" onchange="loadEntityWorkflows(this.value)">
                        <?php
                        foreach ($allEntities as $entity) {
                            $selected = ($entity->entityID == $currentEntityID) ? 'selected' : '';
                            echo "<option value='{$entity->entityID}' {$selected}>{$entity->entityName}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        <i class="ri-information-line me-1"></i>
                        Current Status
                    </label>
                    <div id="entityStatusInfo" class="alert alert-info mb-0 py-2">
                        <small id="workflowStatusText">Loading...</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Workflows List -->
        <div id="workflowsList">
            <?php
            // Get workflows for current entity
            // Get approval policies using Leave class method
            $result = Leave::leave_approval_policies(
                array('entityID' => $currentEntityID, 'Lapsed' => 'N'),
                false,
                $DBConn
            );

            if ($result && count($result) > 0):
              ?>
                  <div class="row">
                     <?php foreach ($result as $policy): ?>
                     <div class="col-md-6">
                        <div class="workflow-card <?php echo $policy->isActive === 'Y' ? 'active' : ''; ?> <?php echo $policy->isDefault === 'Y' ? 'default' : ''; ?>">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1">
                                        <?php echo htmlspecialchars($policy->policyName); ?>
                                        <?php if ($policy->isDefault === 'Y'): ?>
                                            <span class="badge bg-purple">Default</span>
                                        <?php endif; ?>
                                        <?php if ($policy->isActive === 'Y'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </h5>
                                    <p class="text-muted small mb-0"><?php echo htmlspecialchars($policy->policyDescription); ?></p>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                        <i class="ri-more-2-fill"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="viewWorkflow(<?php echo $policy->policyID; ?>)">
                                            <i class="ri-eye-line me-2"></i>View Details
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="editWorkflow(<?php echo $policy->policyID; ?>)">
                                            <i class="ri-edit-line me-2"></i>Edit Workflow
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="cloneWorkflow(<?php echo $policy->policyID; ?>)">
                                            <i class="ri-file-copy-line me-2"></i>Clone Workflow
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="#" onclick="toggleWorkflowStatus(<?php echo $policy->policyID; ?>)">
                                            <i class="ri-toggle-line me-2"></i><?php echo $policy->isActive === 'Y' ? 'Deactivate' : 'Activate'; ?>
                                        </a></li>
                                        <?php if ($policy->isDefault === 'N'): ?>
                                        <li><a class="dropdown-item" href="#" onclick="setAsDefault(<?php echo $policy->policyID; ?>)">
                                            <i class="ri-star-line me-2"></i>Set as Default
                                        </a></li>
                                        <?php endif; ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteWorkflow(<?php echo $policy->policyID; ?>)">
                                            <i class="ri-delete-bin-line me-2"></i>Delete
                                        </a></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between text-muted small">
                                    <span><i class="ri-list-check me-1"></i> <?php echo $policy->totalSteps; ?> Steps (<?php echo $policy->requiredSteps; ?> required)</span>
                                    <span><i class="ri-user-line me-1"></i> By <?php echo htmlspecialchars($policy->createdByName); ?></span>
                                </div>
                            </div>

                            <?php
                            // Get workflow steps using Leave class method
                            $steps = Leave::leave_approval_steps(
                                array('policyID' => $policy->policyID, 'Suspended' => 'N'),
                                false,
                                $DBConn
                            );

                            if ($steps && count($steps) > 0):
                            ?>
                                <div class="workflow-steps-preview">
                                    <small class="text-muted d-block mb-2">Approval Flow:</small>
                                    <?php foreach ($steps as $step): ?>
                                        <div class="step-item-mini mb-2">
                                            <span class="step-badge-mini"><?php echo $step->stepOrder; ?></span>
                                            <span><?php echo htmlspecialchars($step->stepName); ?></span>
                                            <span class="step-type-badge step-type-<?php echo $step->stepType; ?>">
                                                <?php echo ucwords(str_replace('_', ' ', $step->stepType)); ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="mt-3 pt-3 border-top">
                                <button class="btn btn-sm btn-primary w-100" onclick="viewWorkflow(<?php echo $policy->policyID; ?>)">
                                    <i class="ri-eye-line me-1"></i>View Full Workflow
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="ri-flow-chart display-4 text-muted mb-3"></i>
                    <h5 class="text-muted">No Approval Workflows Yet</h5>
                    <p class="text-muted">Create your first workflow or copy from a template</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#templatesModal">
                        <i class="ri-file-copy-line me-1"></i>Browse Templates
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    </div>
<?php endif; ?>
<!-- End Workflows Tab -->

<!-- Templates Management Tab -->
<?php if ($currentWorkflowTab == 'templates'): ?>
    <div id="templatesContent">
        <div class="card custom-card border-0 rounded-0 rounded-bottom">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    <i class="ri-file-list-3-line me-2"></i>
                    Template Library Manager
                    <button type="button" class="btn btn-sm btn-info-light ms-2" data-bs-toggle="modal" data-bs-target="#templateManagementGuideModal" title="Template Management Guide">
                        <i class="ri-question-line"></i>
                    </button>
                </h4>
                <div class="btn-group">
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
                        <i class="ri-add-line me-1"></i>
                        Create New Template
                    </button>
                    <button class="btn btn-sm btn-success" onclick="saveWorkflowAsTemplate()">
                        <i class="ri-save-line me-1"></i>
                        Save Workflow as Template
                    </button>
                </div>
            </div>

            <div class="card-body">
                <!-- Template Categories -->
                <ul class="nav nav-pills mb-4" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#systemTemplatesTab">
                            <i class="ri-star-line me-1"></i>
                            System Templates
                            <span class="badge bg-purple ms-1">4</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#customTemplatesTab">
                            <i class="ri-file-edit-line me-1"></i>
                            Custom Templates
                            <span class="badge bg-info ms-1" id="customTemplateCount">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#publicTemplatesTab">
                            <i class="ri-global-line me-1"></i>
                            Public Templates
                            <span class="badge bg-success ms-1" id="publicTemplateCount">0</span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- System Templates -->
                    <div class="tab-pane fade show active" id="systemTemplatesTab">
                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>
                            <strong>System Templates</strong> are pre-built by the system and cannot be edited or deleted.
                            You can clone them to create custom variations.
                        </div>
                        <div id="systemTemplatesList" class="row">
                            <!-- System templates will be loaded here -->
                        </div>
                    </div>

                    <!-- Custom Templates -->
                    <div class="tab-pane fade" id="customTemplatesTab">
                        <div class="alert alert-success">
                            <i class="ri-information-line me-2"></i>
                            <strong>Custom Templates</strong> are created by you and visible only to your entity.
                            You can edit, delete, or make them public for other entities to use.
                        </div>
                        <div id="customTemplatesList" class="row">
                            <!-- Custom templates will be loaded here -->
                        </div>
                    </div>

                    <!-- Public Templates -->
                    <div class="tab-pane fade" id="publicTemplatesTab">
                        <div class="alert alert-warning">
                            <i class="ri-information-line me-2"></i>
                            <strong>Public Templates</strong> are shared templates created by administrators and available to all entities.
                        </div>
                        <div id="publicTemplatesList" class="row">
                            <!-- Public templates will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<!-- End Templates Tab -->

<!-- Create Workflow Modal -->
<div class="modal fade" id="createWorkflowModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="workflowModalTitle">
                    <i class="ri-add-line me-2"></i>
                    Create New Approval Workflow
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createWorkflowForm">
                    <input type="hidden" id="editPolicyID" name="policyID" value="">
                    <!-- Step 1: Basic Information -->
                    <div class="workflow-step" id="step1">
                        <h6 class="mb-3">Step 1: Basic Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Workflow Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="policyName" id="editPolicyName" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Entity</label>
                                    <select class="form-select" name="entityID" required>
                                        <?php foreach ($allEntities as $entity): ?>
                                            <option value="<?php echo $entity->entityID; ?>" <?php echo ($entity->entityID == $currentEntityID) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($entity->entityName); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="policyDescription" id="editPolicyDescription" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="requireAllApprovals" id="requireAllApprovals">
                                    <label class="form-check-label" for="requireAllApprovals">
                                        Require All Approvals
                                    </label>
                                </div>
                                <small class="text-muted">If checked, all steps must approve</small>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="allowDelegation" id="allowDelegation" checked onchange="toggleDelegationSettings()">
                                    <label class="form-check-label" for="allowDelegation">
                                        Allow Delegation
                                    </label>
                                </div>
                                <small class="text-muted">Approvers can delegate</small>
                            </div>
                        </div>

                        <!-- Enhanced Delegation Settings -->
                        <div id="delegationSettings" class="mt-4 p-3 bg-light rounded border">
                            <h6 class="mb-3">
                                <i class="ri-user-settings-line me-2"></i>
                                Delegation Management Settings
                            </h6>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="autoDelegationOnLeave" id="autoDelegationOnLeave" checked>
                                        <label class="form-check-label fw-semibold" for="autoDelegationOnLeave">
                                            Auto-Delegation When Approver on Leave
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mb-3">Automatically delegate approvals when approver has active leave</small>

                                    <div class="mb-3">
                                        <label class="form-label">Delegation Method</label>
                                        <select class="form-select" name="delegationMethod" id="delegationMethod">
                                            <option value="predefined">Use Predefined Delegates</option>
                                            <option value="skip_level">Skip to Manager's Manager</option>
                                            <option value="same_level">Same Level Colleague</option>
                                            <option value="hr_manager">HR Manager</option>
                                        </select>
                                        <small class="text-muted">How to assign delegate when approver unavailable</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="allowSkipLevel" id="allowSkipLevel" checked>
                                        <label class="form-check-label fw-semibold" for="allowSkipLevel">
                                            Enable Skip-Level Approval
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mb-3">Escalate to manager's manager if delegate unavailable</small>

                                    <div class="mb-3">
                                        <label class="form-label">Delegation Prompt</label>
                                        <select class="form-select" name="delegationPrompt" id="delegationPrompt">
                                            <option value="always">Always prompt manager to assign delegate when booking leave</option>
                                            <option value="optional">Optional - Manager can assign delegate anytime</option>
                                            <option value="auto">Automatic - System assigns delegate automatically</option>
                                        </select>
                                        <small class="text-muted">When should managers be prompted to assign delegates?</small>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info mb-0">
                                <i class="ri-information-line me-2"></i>
                                <strong>Note:</strong> Delegates can be assigned per approver step. Use the "Manage Delegates" button in each step to configure specific delegates.
                            </div>
                        </div>
                            <div class="col-md-4">
                                <label class="form-label">Auto-Approve Threshold (Days)</label>
                                <input type="number" class="form-control" name="autoApproveThreshold" min="0" max="5">
                                <small class="text-muted">Auto-approve if ≤ this value</small>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Approval Steps -->
                    <div class="workflow-step mt-4" id="step2">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Step 2: Define Approval Steps</h6>
                            <button type="button" class="btn btn-sm btn-primary" onclick="addApprovalStep()">
                                <i class="ri-add-line me-1"></i>Add Step
                            </button>
                        </div>

                        <div id="approvalStepsContainer">
                            <!-- Steps will be added here dynamically -->
                        </div>

                        <div class="alert alert-info mt-3">
                            <i class="ri-information-line me-2"></i>
                            <strong>Tip:</strong> Add approval steps in the order they should be executed.
                            Drag to reorder if needed.
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" onclick="resetWorkflowForm()">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveWorkflowBtn" onclick="saveWorkflow()">
                    <i class="ri-save-line me-1"></i>Create Workflow
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Template Configuration Modal -->
<div class="modal fade" id="templateConfigModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="ri-settings-3-line me-2"></i>
                    Configure Workflow: <span id="configTemplateName"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="configureWorkflowForm">
                    <input type="hidden" id="configTemplateID" name="templateID">
                    <input type="hidden" name="entityID" value="<?php echo $currentEntityID; ?>">
                    <input type="hidden" name="orgDataID" value="<?php echo $currentOrgDataID; ?>">

                    <!-- Workflow Name -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Workflow Name</label>
                        <input type="text" class="form-control" id="configWorkflowName" name="workflowName" required>
                        <small class="text-muted">You can customize the name for your entity</small>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description (Optional)</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="Describe when this workflow should be used"></textarea>
                    </div>

                    <hr>

                    <!-- Approver Selection -->
                    <h6 class="mb-3">
                        <i class="ri-user-follow-line me-2"></i>
                        Select Approvers
                    </h6>

                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        <strong>Note:</strong> Some approvers (like Direct Supervisor and Project Manager) are automatically
                        identified based on employee relationships. You only need to select specific users for HR roles.
                    </div>

                    <div id="approverSelectionContainer">
                        <!-- Approver selection fields will be loaded here -->
                    </div>

                    <hr>

                    <!-- Workflow Settings -->

                    <h6 class="mb-3">
                        <i class="ri-settings-4-line me-2"></i>
                        Workflow Settings
                    </h6>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="allowDelegation" id="configAllowDelegation" checked>
                                <label class="form-check-label" for="configAllowDelegation">
                                    Allow Delegation
                                </label>
                            </div>
                            <small class="text-muted d-block mb-3">Approvers can delegate their approval to others</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Auto-Approve Threshold (Days)</label>
                            <input type="number" class="form-control" name="autoApproveThreshold" min="0" max="5" placeholder="Leave blank to disable">
                            <small class="text-muted">Automatically approve leaves ≤ this many days</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="createWorkflowFromTemplate()">
                    <i class="ri-check-line me-1"></i>Create Workflow
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Templates Browser Modal -->
<div class="modal fade" id="templatesModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-gradient-purple text-white">
                <h5 class="modal-title">
                    <i class="ri-file-copy-line me-2"></i>
                    Workflow Templates Library
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <ul class="nav nav-pills" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#systemTemplates">
                                <i class="ri-star-line me-1"></i>System Templates
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#entityTemplates">
                                <i class="ri-building-line me-1"></i>Entity Workflows
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="tab-content">
                    <!-- System Templates -->
                    <div class="tab-pane fade show active" id="systemTemplates">
                        <div class="template-grid">
                            <?php
                            // Get system templates using Leave class method
                            $templates = Leave::leave_workflow_templates(
                                array('isSystemTemplate' => 'Y', 'Suspended' => 'N'),
                                false,
                                $DBConn
                            );

                            // Define template details for hover popups
                            $templateDetails = [
                                1 => [
                                    'title' => 'Standard 3-Level Approval',
                                    'levels' => '3 Approval Levels',
                                    'approvers' => [
                                        'Level 1: Direct Supervisor (Auto-identified)',
                                        'Level 2: Department Head (Auto-identified)',
                                        'Level 3: HR Manager (Select 1 or 2 HR managers)'
                                    ],
                                    'bestFor' => 'Medium to large organizations with established hierarchy',
                                    'estimatedTime' => '2-4 days average'
                                ],
                                2 => [
                                    'title' => 'Simple 2-Level Approval',
                                    'levels' => '2 Approval Levels',
                                    'approvers' => [
                                        'Level 1: Direct Supervisor (Auto-identified)',
                                        'Level 2: HR Manager (Select 1 or 2 HR managers)'
                                    ],
                                    'bestFor' => 'Small to medium organizations, faster approval process',
                                    'estimatedTime' => '1-2 days average'
                                ],
                                3 => [
                                    'title' => 'Project-Based Approval',
                                    'levels' => '3 Approval Levels',
                                    'approvers' => [
                                        'Level 1: Direct Supervisor (Auto-identified)',
                                        'Level 2: Project Manager (Auto-identified if assigned to projects)',
                                        'Level 3: HR Manager (Select 1 or 2 HR managers)'
                                    ],
                                    'bestFor' => 'Project-driven organizations where employees work on multiple projects',
                                    'estimatedTime' => '2-5 days average'
                                ],
                                4 => [
                                    'title' => 'HR-Only Approval',
                                    'levels' => '1 Approval Level',
                                    'approvers' => [
                                        'Level 1: HR Manager (Select 1 or 2 HR managers)'
                                    ],
                                    'bestFor' => 'HR department staff or very short leaves (≤2 days with auto-approve)',
                                    'estimatedTime' => '1 day average'
                                ]
                            ];

                            if ($templates):
                            foreach ($templates as $template):
                                $templateInfo = $templateDetails[$template->templateID] ?? null;
                                $tooltipContent = '';
                                if ($templateInfo) {
                                    $tooltipContent = htmlspecialchars(json_encode([
                                        'title' => $templateInfo['title'],
                                        'levels' => $templateInfo['levels'],
                                        'approvers' => $templateInfo['approvers'],
                                        'bestFor' => $templateInfo['bestFor'],
                                        'estimatedTime' => $templateInfo['estimatedTime']
                                    ]));
                                }
                            ?>
                                <div class="template-card system position-relative"
                                     onclick="useTemplate(<?php echo $template->templateID; ?>)"
                                     data-bs-toggle="popover"
                                     data-bs-trigger="hover"
                                     data-bs-html="true"
                                     data-bs-placement="top"
                                     data-template-info='<?php echo $tooltipContent; ?>'
                                     data-bs-content="">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0">
                                            <?php echo htmlspecialchars($template->templateName); ?>
                                            <i class="ri-information-line fs-14 ms-1 text-white-50" title="Hover for details"></i>
                                        </h6>
                                        <span class="badge bg-white text-dark"><?php echo $template->stepCount; ?> Steps</span>
                                    </div>
                                    <p class="small mb-3"><?php echo htmlspecialchars($template->templateDescription); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small><i class="ri-user-line me-1"></i><?php echo $template->usageCount; ?> uses</small>
                                        <button class="btn btn-sm btn-white" onclick="event.stopPropagation(); previewTemplate(<?php echo $template->templateID; ?>)">
                                            <i class="ri-eye-line"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach;
                            endif; ?>
                        </div>
                    </div>

                    <!-- Entity Workflows (for copying) -->
                    <div class="tab-pane fade" id="entityTemplates">
                        <p class="text-muted mb-3">Copy approval workflows from other entities in your organization</p>
                        <!-- Will be loaded dynamically -->
                        <div id="entityWorkflowsList"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.step-item-mini {
    display: flex;
    align-items: center;
    padding: 8px;
    background: white;
    border-radius: 4px;
}

.step-badge-mini {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #6c5ce7;
    color: white;
    font-size: 12px;
    font-weight: 600;
    margin-right: 8px;
}

.bg-gradient-purple {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.badge.bg-purple {
    background-color: #6c5ce7;
}

.workflow-guide-step {
    padding: 1.25rem;
    border-left: 4px solid #6c5ce7;
    background: linear-gradient(90deg, #f8f9fa 0%, #ffffff 100%);
    margin-bottom: 1rem;
    border-radius: 0 0.5rem 0.5rem 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.workflow-guide-step h6 {
    color: #6c5ce7;
    font-weight: 700;
    margin-bottom: 0.75rem;
    font-size: 1.1rem;
}

.workflow-guide-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 50%;
    font-weight: 700;
    font-size: 16px;
    margin-right: 0.75rem;
    box-shadow: 0 2px 8px rgba(108,92,231,0.3);
}

.workflow-tip-card {
    background: #f0f7ff;
    border-left: 3px solid #007bff;
    padding: 1rem;
    margin-bottom: 0.75rem;
    border-radius: 0.25rem;
}

.workflow-tip-card i {
    color: #007bff;
}

.modal-header.bg-workflow-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.modal-header.bg-workflow-gradient .btn-close {
    filter: brightness(0) invert(1);
}

.approver-type-box {
    background: #e8f5e9;
    border: 2px solid #4caf50;
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.approver-type-box.manual {
    background: #fff3e0;
    border-color: #ff9800;
}

.feature-highlight {
    background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin: 1rem 0;
}

.template-popover {
    max-width: 400px;
}

.template-popover .popover-body {
    padding: 1rem;
}

.template-popover-content h6 {
    font-size: 14px;
    font-weight: 600;
}

.template-popover-content .small {
    font-size: 12px;
    line-height: 1.4;
}

.template-card.system:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
    transition: all 0.3s ease;
}

.template-card.system {
    transition: all 0.3s ease;
    cursor: pointer;
}

.border-purple {
    border-color: #6c5ce7 !important;
}

.text-purple {
    color: #6c5ce7 !important;
}

.btn-close-white {
    filter: brightness(0) invert(1);
}

@media (max-width: 768px) {
    .workflow-guide-badge {
        width: 32px;
        height: 32px;
        font-size: 14px;
    }

    .workflow-guide-step {
        padding: 1rem;
    }
}
</style>

<!-- Create/Edit Template Modal -->
<div class="modal fade" id="createTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="ri-add-line me-2"></i>
                    <span id="templateModalTitle">Create New Template</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createTemplateForm">
                    <input type="hidden" id="editTemplateID" name="templateID">

                    <!-- Basic Information -->
                    <h6 class="mb-3">
                        <i class="ri-information-line me-2"></i>
                        Template Information
                    </h6>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Template Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="templateName" id="templateName" required>
                                <small class="text-muted">e.g., "Executive Approval Workflow" or "Fast Track 2-Level"</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Visibility</label>
                                <select class="form-select" name="isPublic" id="templateIsPublic">
                                    <option value="N">Private (My Entity Only)</option>
                                    <option value="Y">Public (All Entities)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" name="templateDescription" id="templateDescription" rows="2" required></textarea>
                        <small class="text-muted">Describe what this template is for and when it should be used</small>
                    </div>

                    <hr class="my-4">

                    <!-- Template Steps -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">
                            <i class="ri-list-ordered me-2"></i>
                            Define Approval Steps
                        </h6>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addTemplateStep()">
                            <i class="ri-add-line me-1"></i>
                            Add Step
                        </button>
                    </div>

                    <div id="templateStepsContainer">
                        <!-- Template steps will be added here -->
                    </div>

                    <div class="alert alert-info mt-3">
                        <i class="ri-lightbulb-line me-2"></i>
                        <strong>Tip:</strong> Add steps in the order they should execute. Use "Auto-Identified" types
                        (Supervisor, Project Manager, Department Head) for dynamic identification, or "Manual Selection" types
                        (HR Manager, HR Representative) for specific person selection during workflow creation.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>
                    Cancel
                </button>
                <button type="button" class="btn btn-success" onclick="saveTemplate()">
                    <i class="ri-save-line me-1"></i>
                    Save Template
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Workflow User Guide Modal -->
<div class="modal fade" id="workflowGuideModal" tabindex="-1" aria-labelledby="workflowGuideModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-workflow-gradient">
                <h5 class="modal-title" id="workflowGuideModalLabel">
                    <i class="ri-book-open-line me-2"></i>
                    Approval Workflow Manager - User Guide
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Introduction -->
                <div class="alert alert-info d-flex align-items-start mb-4">
                    <i class="ri-lightbulb-line fs-20 me-3 mt-1"></i>
                    <div>
                        <h6 class="alert-heading mb-2">Welcome to the Approval Workflow Manager!</h6>
                        <p class="mb-0">This powerful system allows you to configure dynamic leave approval workflows for your organization. Instead of manually assigning approvers to each employee, you define a workflow once, and the system automatically identifies the right approvers based on relationships.</p>
                    </div>
                </div>

                <!-- What is a Workflow -->
                <div class="feature-highlight">
                    <h5 class="mb-3">
                        <i class="ri-question-answer-line me-2"></i>
                        What is an Approval Workflow?
                    </h5>
                    <p class="mb-3">An approval workflow is a set of <strong>approval steps</strong> that a leave application must go through before being approved. For example:</p>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-white rounded">
                                <div class="workflow-guide-badge mx-auto mb-2">1</div>
                                <h6 class="mb-1">Direct Supervisor</h6>
                                <small class="text-muted">Employee's manager</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-white rounded">
                                <div class="workflow-guide-badge mx-auto mb-2">2</div>
                                <h6 class="mb-1">Department Head</h6>
                                <small class="text-muted">Head of department</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-white rounded">
                                <div class="workflow-guide-badge mx-auto mb-2">3</div>
                                <h6 class="mb-1">HR Manager</h6>
                                <small class="text-muted">Final approval</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Start Guide -->
                <h5 class="mt-4 mb-3">
                    <i class="ri-rocket-line me-2"></i>
                    Quick Start: Create Your First Workflow (2 Minutes)
                </h5>

                <!-- Step 1 -->
                <div class="workflow-guide-step">
                    <h6>
                        <span class="workflow-guide-badge">1</span>
                        Browse Templates
                    </h6>
                    <div class="ms-5">
                        <p class="mb-2">Click the <strong>"Browse Templates"</strong> button in the top-right corner.</p>
                        <p class="mb-2">You'll see 4 pre-built system templates:</p>
                        <ul class="small mb-0">
                            <li><strong>Standard 3-Level</strong> - Supervisor → Dept Head → HR (Most common)</li>
                            <li><strong>Simple 2-Level</strong> - Supervisor → HR (For smaller organizations)</li>
                            <li><strong>Project-Based</strong> - Supervisor → Project Manager → HR</li>
                            <li><strong>HR-Only</strong> - HR Manager only (For HR department)</li>
                        </ul>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="workflow-guide-step">
                    <h6>
                        <span class="workflow-guide-badge">2</span>
                        Select and Configure Template
                    </h6>
                    <div class="ms-5">
                        <p class="mb-2">Click on a template (e.g., "Standard 3-Level Approval").</p>
                        <p class="mb-2">A configuration modal will open where you can:</p>
                        <ul class="small">
                            <li><strong>Customize Name:</strong> Change the workflow name if desired</li>
                            <li><strong>Add Description:</strong> Describe when this workflow is used</li>
                            <li><strong>Select HR Manager:</strong> Choose from your entity's employee list ⭐</li>
                            <li><strong>Configure Settings:</strong>
                                <ul>
                                    <li>☑ Allow Delegation (recommended)</li>
                                    <li>Auto-Approve Threshold (e.g., 2 days for automatic approval)</li>
                                </ul>
                            </li>
                        </ul>
                        <div class="alert alert-warning small mb-0">
                            <i class="ri-information-line me-2"></i>
                            <strong>Important:</strong> Make sure to select your HR Manager from the dropdown before clicking "Create Workflow"!
                        </div>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="workflow-guide-step">
                    <h6>
                        <span class="workflow-guide-badge">3</span>
                        Create Workflow
                    </h6>
                    <div class="ms-5">
                        <p class="mb-2">Click the <strong>"Create Workflow"</strong> button.</p>
                        <p class="mb-2">You'll see:</p>
                        <ul class="small mb-0">
                            <li>🔵 Blue toast: "Creating Workflow... Please wait"</li>
                            <li>🟢 Green toast: "Success! Workflow created successfully"</li>
                            <li>Page automatically reloads with your new workflow</li>
                        </ul>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="workflow-guide-step">
                    <h6>
                        <span class="workflow-guide-badge">4</span>
                        Activate & Set as Default
                    </h6>
                    <div class="ms-5">
                        <p class="mb-2">Find your workflow card and click the <strong>⋮</strong> (three dots) menu:</p>
                        <ol class="small">
                            <li>Select <strong>"Activate"</strong> - Green toast confirms activation</li>
                            <li>Click ⋮ again, select <strong>"Set as Default"</strong> - This workflow will be used for all new leave applications</li>
                        </ol>
                        <div class="alert alert-success small mb-0">
                            <i class="ri-check-circle-line me-2"></i>
                            Your workflow is now active and ready to process leave applications!
                        </div>
                    </div>
                </div>

                <!-- Understanding Approver Types -->
                <h5 class="mt-4 mb-3">
                    <i class="ri-team-line me-2"></i>
                    Understanding Approver Types
                </h5>

                <div class="row">
                    <div class="col-md-6">
                        <div class="approver-type-box">
                            <h6 class="mb-2">
                                <i class="ri-ai-generate me-2"></i>
                                Auto-Identified Approvers
                            </h6>
                            <p class="small mb-2">These are <strong>automatically identified</strong> by the system based on employee relationships:</p>
                            <ul class="small mb-0">
                                <li><strong>Direct Supervisor</strong> - From employee's supervisor assignment</li>
                                <li><strong>Project Manager</strong> - From active project assignments</li>
                                <li><strong>Department Head</strong> - From department hierarchy</li>
                            </ul>
                            <div class="alert alert-light small mt-2 mb-0">
                                <i class="ri-information-line me-1"></i>
                                No manual selection needed!
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="approver-type-box manual">
                            <h6 class="mb-2">
                                <i class="ri-user-settings-line me-2"></i>
                                Manual Selection Required
                            </h6>
                            <p class="small mb-2">You <strong>must select</strong> specific people for these roles:</p>
                            <ul class="small mb-0">
                                <li><strong>HR Manager</strong> - Select from employee list ⭐</li>
                                <li><strong>HR Representative</strong> - Select specific HR staff</li>
                                <li><strong>Specific User</strong> - Choose any individual</li>
                            </ul>
                            <div class="alert alert-light small mt-2 mb-0">
                                <i class="ri-alert-line me-1"></i>
                                Selection required during configuration!
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Common Tasks -->
                <h5 class="mt-4 mb-3">
                    <i class="ri-task-line me-2"></i>
                    Common Tasks
                </h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="workflow-tip-card">
                            <h6 class="mb-2">
                                <i class="ri-file-copy-line me-2"></i>
                                Clone a Workflow
                            </h6>
                            <p class="small mb-2">To create a similar workflow:</p>
                            <ol class="small mb-0">
                                <li>Click ⋮ on existing workflow</li>
                                <li>Select "Clone Workflow"</li>
                                <li>Toast confirms cloning</li>
                                <li>Edit cloned workflow as needed</li>
                            </ol>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="workflow-tip-card">
                            <h6 class="mb-2">
                                <i class="ri-building-line me-2"></i>
                                Copy to Another Entity
                            </h6>
                            <p class="small mb-2">To use this workflow in another entity:</p>
                            <ol class="small mb-0">
                                <li>Click ⋮ on workflow</li>
                                <li>Select "Clone Workflow"</li>
                                <li>Switch entity selector</li>
                                <li>Activate in new entity</li>
                            </ol>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="workflow-tip-card">
                            <h6 class="mb-2">
                                <i class="ri-edit-line me-2"></i>
                                Edit a Workflow
                            </h6>
                            <p class="small mb-2">To modify an existing workflow:</p>
                            <ol class="small mb-0">
                                <li>Click ⋮ on workflow</li>
                                <li>Select "Edit Workflow"</li>
                                <li>Make your changes</li>
                                <li>Save and reactivate if needed</li>
                            </ol>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="workflow-tip-card">
                            <h6 class="mb-2">
                                <i class="ri-delete-bin-line me-2"></i>
                                Delete a Workflow
                            </h6>
                            <p class="small mb-2">To remove an unused workflow:</p>
                            <ol class="small mb-0">
                                <li>Click ⋮ on workflow</li>
                                <li>Select "Delete"</li>
                                <li>Confirm deletion</li>
                                <li>Can only delete if not in use</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Best Practices -->
                <h5 class="mt-4 mb-3">
                    <i class="ri-star-line me-2"></i>
                    Best Practices
                </h5>

                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="d-flex align-items-start p-2">
                            <i class="ri-check-line text-success me-2 mt-1 fs-18"></i>
                            <small><strong>Start with Templates:</strong> Use pre-built templates and customize rather than creating from scratch</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start p-2">
                            <i class="ri-check-line text-success me-2 mt-1 fs-18"></i>
                            <small><strong>One Default Per Entity:</strong> Each entity should have exactly one default workflow</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start p-2">
                            <i class="ri-check-line text-success me-2 mt-1 fs-18"></i>
                            <small><strong>Test First:</strong> Create workflow as inactive, test it, then activate</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start p-2">
                            <i class="ri-check-line text-success me-2 mt-1 fs-18"></i>
                            <small><strong>Use Clear Names:</strong> "Sales Dept - Fast Track" vs "Workflow 1"</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start p-2">
                            <i class="ri-check-line text-success me-2 mt-1 fs-18"></i>
                            <small><strong>Set Realistic Escalation:</strong> 2-3 days for most approval steps</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start p-2">
                            <i class="ri-check-line text-success me-2 mt-1 fs-18"></i>
                            <small><strong>Review Quarterly:</strong> Update workflows when organizational structure changes</small>
                        </div>
                    </div>
                </div>

                <!-- Key Features -->
                <h5 class="mt-4 mb-3">
                    <i class="ri-shield-star-line me-2"></i>
                    Key Features
                </h5>

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <i class="ri-team-line display-5 text-primary mb-2"></i>
                                <h6 class="mb-2">Dynamic Approvers</h6>
                                <p class="small text-muted mb-0">System automatically identifies supervisors and managers based on relationships</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <i class="ri-user-search-line display-5 text-success mb-2"></i>
                                <h6 class="mb-2">HR Manager Selection</h6>
                                <p class="small text-muted mb-0">Choose specific HR managers from your employee list for each workflow</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-info">
                            <div class="card-body text-center">
                                <i class="ri-file-copy-line display-5 text-info mb-2"></i>
                                <h6 class="mb-2">Template Library</h6>
                                <p class="small text-muted mb-0">Start quickly with 4 ready-to-use workflow templates</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-warning">
                            <div class="card-body text-center">
                                <i class="ri-building-line display-5 text-warning mb-2"></i>
                                <h6 class="mb-2">Entity-Specific</h6>
                                <p class="small text-muted mb-0">Different workflows for different entities and departments</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-purple">
                            <div class="card-body text-center">
                                <i class="ri-time-line display-5 text-purple mb-2"></i>
                                <h6 class="mb-2">Auto-Approve</h6>
                                <p class="small text-muted mb-0">Automatically approve short leaves (e.g., ≤2 days)</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-secondary">
                            <div class="card-body text-center">
                                <i class="ri-notification-line display-5 text-secondary mb-2"></i>
                                <h6 class="mb-2">Escalation Rules</h6>
                                <p class="small text-muted mb-0">Auto-escalate if no action within set timeframe</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Troubleshooting -->
                <h5 class="mt-4 mb-3">
                    <i class="ri-tools-line me-2"></i>
                    Troubleshooting
                </h5>

                <div class="accordion" id="troubleshootingAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#trouble1">
                                I don't see the configuration modal when selecting a template
                            </button>
                        </h2>
                        <div id="trouble1" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                            <div class="accordion-body">
                                <p class="small mb-2"><strong>Solution:</strong></p>
                                <ul class="small">
                                    <li>Clear your browser cache (Ctrl+F5 or Cmd+Shift+R)</li>
                                    <li>Verify JavaScript is enabled</li>
                                    <li>Check browser console for errors (F12)</li>
                                    <li>Try a different browser</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#trouble2">
                                HR Manager dropdown is empty
                            </button>
                        </h2>
                        <div id="trouble2" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                            <div class="accordion-body">
                                <p class="small mb-2"><strong>Possible causes:</strong></p>
                                <ul class="small">
                                    <li>Your entity has no employees configured</li>
                                    <li>Check that employees exist in the people/user_details tables</li>
                                    <li>Verify employees belong to your entity</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#trouble3">
                                Toast notifications don't appear
                            </button>
                        </h2>
                        <div id="trouble3" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                            <div class="accordion-body">
                                <p class="small mb-2"><strong>Solution:</strong></p>
                                <ul class="small">
                                    <li>Verify Bootstrap 5 CSS and JS are loaded</li>
                                    <li>Check browser console for JavaScript errors</li>
                                    <li>Clear cache and reload page</li>
                                    <li>Check if ad-blocker is interfering</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#trouble4">
                                Wrong approver is being identified
                            </button>
                        </h2>
                        <div id="trouble4" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                            <div class="accordion-body">
                                <p class="small mb-2"><strong>Solution:</strong></p>
                                <ul class="small">
                                    <li>Check employee's supervisor is set correctly in user_details</li>
                                    <li>Verify supervisor is an active user</li>
                                    <li>For project managers: Check project assignments are active</li>
                                    <li>For HR managers: Verify the person you selected is active</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Tips -->
                <h5 class="mt-4 mb-3">
                    <i class="ri-lightbulb-flash-line me-2"></i>
                    Quick Tips for Managers
                </h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="ri-time-line me-2 text-primary"></i>
                                    Estimated Setup Time
                                </h6>
                                <ul class="small mb-0">
                                    <li><strong>Using Template:</strong> 2-3 minutes</li>
                                    <li><strong>Custom Workflow:</strong> 10-15 minutes</li>
                                    <li><strong>Testing:</strong> 5 minutes</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="ri-checkbox-multiple-line me-2 text-success"></i>
                                    Recommended First Setup
                                </h6>
                                <ol class="small mb-0">
                                    <li>Use "Standard 3-Level" template</li>
                                    <li>Select your HR Manager</li>
                                    <li>Set auto-approve to 2 days</li>
                                    <li>Activate and set as default</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Workflow Benefits -->
                <div class="alert alert-success mt-4">
                    <h6 class="alert-heading">
                        <i class="ri-trophy-line me-2"></i>
                        Benefits of Using Approval Workflows
                    </h6>
                    <div class="row small">
                        <div class="col-md-6">
                            <ul class="mb-0">
                                <li>✅ <strong>Zero Manual Assignments</strong> - No more assigning approvers to each employee</li>
                                <li>✅ <strong>Automatic Routing</strong> - System identifies correct approvers</li>
                                <li>✅ <strong>Faster Updates</strong> - Change workflow once, affects everyone</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="mb-0">
                                <li>✅ <strong>Fewer Errors</strong> - Eliminates manual routing mistakes</li>
                                <li>✅ <strong>Entity Flexibility</strong> - Different workflows for different entities</li>
                                <li>✅ <strong>Complete Audit Trail</strong> - All actions logged</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Estimated Time -->
                <div class="alert alert-light border mt-4 mb-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="ri-time-line fs-20 me-3 text-primary"></i>
                            <div>
                                <strong>Total Setup Time:</strong>
                                <p class="mb-0 small text-muted">Quick Setup (Template): 2-5 minutes | Custom Setup: 15-20 minutes | Testing: 5 minutes</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>
                    Close
                </button>
                <a href="<?php echo $base; ?>LEAVE_APPROVAL_WORKFLOW_SYSTEM_GUIDE.md" target="_blank" class="btn btn-primary">
                    <i class="ri-book-open-line me-1"></i>
                    View Complete Documentation
                </a>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Workflow Manager -->
<script>
// Initialize approval steps counter
let stepCounter = 0;

// Toggle delegation settings visibility
function toggleDelegationSettings() {
    const allowDelegation = document.getElementById('allowDelegation');
    const delegationSettings = document.getElementById('delegationSettings');

    if (allowDelegation && delegationSettings) {
        if (allowDelegation.checked) {
            delegationSettings.style.display = 'block';
        } else {
            delegationSettings.style.display = 'none';
        }
    }
}

// Initialize delegation settings on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleDelegationSettings();
});

// Add new approval step
function addApprovalStep() {
    stepCounter++;
    const container = document.getElementById('approvalStepsContainer');
    const stepDiv = document.createElement('div');
    stepDiv.className = 'step-item mb-3';
    stepDiv.id = `approvalStep${stepCounter}`;

    stepDiv.innerHTML = `
        <span class="step-badge">${stepCounter}</span>
        <div class="flex-grow-1">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label small">Step Name</label>
                    <input type="text" class="form-control form-control-sm" name="steps[${stepCounter}][name]" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Approver Type</label>
                    <select class="form-select form-select-sm" name="steps[${stepCounter}][type]" required>
                        <option value="supervisor">Direct Supervisor</option>
                        <option value="project_manager">Project Manager</option>
                        <option value="hr_manager">HR Manager</option>
                        <option value="hr_representative">HR Representative</option>
                        <option value="department_head">Department Head</option>
                        <option value="custom_role">Custom Role</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Escalation (Days)</label>
                    <input type="number" class="form-control form-control-sm" name="steps[${stepCounter}][escalation]" min="0" max="30" value="3">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeApprovalStep(${stepCounter})">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-12">
                    <label class="form-label small">Description (optional)</label>
                    <input type="text" class="form-control form-control-sm" name="steps[${stepCounter}][description]">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-4">
                    <div class="form-check form-check-sm">
                        <input class="form-check-input" type="checkbox" name="steps[${stepCounter}][required]" checked>
                        <label class="form-check-label small">Required Step</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-check-sm">
                        <input class="form-check-input" type="checkbox" name="steps[${stepCounter}][conditional]">
                        <label class="form-check-label small">Conditional</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="manageStepDelegates(${stepCounter})" title="Manage Delegates">
                        <i class="ri-user-settings-line me-1"></i>Manage Delegates
                    </button>
                </div>
            </div>
            <div id="delegatesSection${stepCounter}" class="mt-2 p-2 bg-light rounded" style="display: none;">
                <small class="text-muted d-block mb-2">Delegates for this step:</small>
                <div id="delegatesList${stepCounter}"></div>
                <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addDelegate(${stepCounter})">
                    <i class="ri-add-line me-1"></i>Add Delegate
                </button>
            </div>
        </div>
    `;

    container.appendChild(stepDiv);
}

// Remove approval step
function removeApprovalStep(stepId) {
    const step = document.getElementById(`approvalStep${stepId}`);
    if (step) {
        step.remove();
        renumberSteps();
    }
}

// Renumber steps after deletion
function renumberSteps() {
    const steps = document.querySelectorAll('.step-item');
    steps.forEach((step, index) => {
        const badge = step.querySelector('.step-badge');
        if (badge) {
            badge.textContent = index + 1;
        }
    });
}

// Manage delegates for a specific step
function manageStepDelegates(stepId) {
    const delegatesSection = document.getElementById(`delegatesSection${stepId}`);
    if (delegatesSection) {
        delegatesSection.style.display = delegatesSection.style.display === 'none' ? 'block' : 'none';
    }
}

// Add delegate for a step
function addDelegate(stepId) {
    const delegatesList = document.getElementById(`delegatesList${stepId}`);
    if (!delegatesList) return;

    const delegateDiv = document.createElement('div');
    delegateDiv.className = 'mb-2 p-2 border rounded';
    delegateDiv.innerHTML = `
        <div class="row align-items-center">
            <div class="col-md-5">
                <select class="form-select form-select-sm" name="steps[${stepId}][delegates][]" required>
                    <option value="">Select Delegate</option>
                    <?php if (!empty($allEmployees)): ?>
                        <?php foreach ($allEmployees as $emp): ?>
                        <option value="<?= $emp->employeeID ?>"><?= htmlspecialchars($emp->firstName . ' ' . $emp->lastName) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-4">
                <select class="form-select form-select-sm" name="steps[${stepId}][delegatePriority][]">
                    <option value="primary">Primary Delegate</option>
                    <option value="secondary">Secondary Delegate</option>
                    <option value="tertiary">Tertiary Delegate</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.mb-2').remove()">
                    <i class="ri-delete-bin-line"></i> Remove
                </button>
            </div>
        </div>
    `;
    delegatesList.appendChild(delegateDiv);
}

// Save workflow
function saveWorkflow() {
    const form = document.getElementById('createWorkflowForm');
    const formData = new FormData(form);

    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // Determine if editing or creating
    const policyID = document.getElementById('editPolicyID').value;
    const isEditMode = policyID && policyID !== '';

    // Add additional data
    formData.append('action', isEditMode ? 'update_workflow' : 'create_workflow');
    formData.append('orgDataID', '<?php echo $currentOrgDataID; ?>');
    if (isEditMode) {
        formData.append('policyID', policyID);
    }

    // Show appropriate loading message
    showToast(isEditMode ? 'Updating Workflow' : 'Creating Workflow', 'Please wait...', 'info');

    // AJAX request to save
    fetch('<?php echo $base; ?>php/scripts/leave/workflows/save_approval_workflow.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', isEditMode ? 'Workflow updated successfully' : 'Workflow created successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('createWorkflowModal')).hide();
            resetWorkflowForm();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Error', data.message || (isEditMode ? 'Failed to update workflow' : 'Failed to create workflow'), 'error');
        }
    })
    .catch(error => {
        showToast('Error', isEditMode ? 'Failed to update workflow' : 'Failed to create workflow', 'error');
        console.error('Error:', error);
    });
}

// Use template - show configuration modal
function useTemplate(templateID) {
    // Close templates modal
    bootstrap.Modal.getInstance(document.getElementById('templatesModal')).hide();

    // Load template details and show configuration modal
    fetch(`<?php echo $base; ?>php/scripts/leave/workflows/get_template_details.php?templateID=${templateID}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showTemplateConfigModal(templateID, data.template);
            } else {
                showToast('Error loading template', data.message, 'error');
            }
        })
        .catch(error => {
            showToast('Error', 'Failed to load template details', 'error');
        });
}

// Show template configuration modal
function showTemplateConfigModal(templateID, template) {
    // Populate modal with template info
    document.getElementById('configTemplateID').value = templateID;
    document.getElementById('configTemplateName').textContent = template.templateName;
    document.getElementById('configWorkflowName').value = template.templateName;

    // Load template steps and show approver selection fields
    loadTemplateStepsForConfig(templateID);

    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('templateConfigModal'));
    modal.show();
}

// Load template steps and create approver selection fields
function loadTemplateStepsForConfig(templateID) {
    fetch(`<?php echo $base; ?>php/scripts/leave/workflows/get_template_steps.php?templateID=${templateID}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.steps) {
                const container = document.getElementById('approverSelectionContainer');
                container.innerHTML = '';

                data.steps.forEach((step, index) => {
                    // Only show selection for HR Manager, HR Representative, and Specific User types
                    if (step.stepType === 'hr_manager' || step.stepType === 'hr_representative' || step.stepType === 'specific_user') {
                        const stepDiv = document.createElement('div');
                        stepDiv.className = 'mb-3 p-3 border rounded bg-light';

                        const approverLabel = step.stepType === 'hr_manager' ? 'HR Manager' :
                                            (step.stepType === 'hr_representative' ? 'HR Representative' : 'Approver');

                        stepDiv.innerHTML = `
                            <label class="form-label fw-semibold">
                                <span class="badge bg-primary">${step.stepOrder}</span>
                                ${step.stepName}
                            </label>
                            <select class="form-select mb-2" name="stepApprover_${step.stepOrder}" id="approver_${step.stepOrder}" required>
                                <option value="">-- Select Primary ${approverLabel} --</option>
                                ${generateEmployeeOptions()}
                            </select>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="enableBackup_${step.stepOrder}" onchange="toggleBackupApprover(${step.stepOrder})">
                                <label class="form-check-label small" for="enableBackup_${step.stepOrder}">
                                    <i class="ri-add-circle-line me-1"></i>
                                    Add backup/alternative ${approverLabel} (either one can approve)
                                </label>
                            </div>
                            <div id="backupApprover_${step.stepOrder}" style="display: none;">
                                <select class="form-select" name="stepApproverBackup_${step.stepOrder}">
                                    <option value="">-- Select Backup ${approverLabel} --</option>
                                    ${generateEmployeeOptions()}
                                </select>
                                <small class="text-muted d-block mt-1">
                                    <i class="ri-information-line me-1"></i>
                                    Either the primary or backup approver can approve this step (parallel approval)
                                </small>
                            </div>
                            <small class="text-muted d-block mt-2">Select the person(s) who will approve at this step</small>
                        `;
                        container.appendChild(stepDiv);
                    }
                });

                // If no selectable steps, show info
                if (container.innerHTML === '') {
                    container.innerHTML = `
                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>
                            This workflow uses dynamic approvers (Supervisor, Project Manager, Department Head)
                            which are automatically identified from employee relationships. No manual selection needed!
                        </div>
                    `;
                }
            }
        });
}

// Toggle backup approver dropdown
function toggleBackupApprover(stepOrder) {
    const checkbox = document.getElementById(`enableBackup_${stepOrder}`);
    const backupDiv = document.getElementById(`backupApprover_${stepOrder}`);
    const backupSelect = document.querySelector(`[name="stepApproverBackup_${stepOrder}"]`);

    if (checkbox.checked) {
        backupDiv.style.display = 'block';
        backupSelect.required = true;
    } else {
        backupDiv.style.display = 'none';
        backupSelect.required = false;
        backupSelect.value = '';
    }
}

// Generate employee options for dropdown
function generateEmployeeOptions() {
    const employees = <?php echo json_encode(array_map(function($emp) {
        return [
            'ID' => $emp->ID,
            'name' => trim($emp->FirstName . ' ' . $emp->Surname)
        ];
    }, $allEmployees)); ?>;

    return employees.map(emp => `<option value="${emp.ID}">${emp.name}</option>`).join('');
}

// Create workflow from template with configured approvers
function createWorkflowFromTemplate() {
    const form = document.getElementById('configureWorkflowForm');

    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    formData.append('action', 'create_from_template');

    // Show loading toast
    showToast('Creating Workflow', 'Please wait...', 'info');

    // Submit to server
    fetch('<?php echo $base; ?>php/scripts/leave/workflows/create_workflow_from_template.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success!', 'Workflow created successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('templateConfigModal')).hide();

            // Reload page after short delay
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showToast('Error', data.message || 'Failed to create workflow', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'Failed to create workflow. Please try again.', 'error');
    });
}

// Load entity workflows
function loadEntityWorkflows(entityID) {
    window.location.href = `?s=user&ss=leave&p=config&state=leave_approvers&entityID=${entityID}`;
}

// View workflow details
function viewWorkflow(policyID) {
    window.location.href = `?s=user&ss=leave&p=config&state=leave_approvers&workflow_action=view&policyID=${policyID}`;
}

// Clone workflow
function cloneWorkflow(policyID) {
    // Show confirmation with Bootstrap modal or toast
    if (confirm('Clone this workflow? This will create a copy for the current entity.')) {
        showToast('Cloning Workflow', 'Please wait...', 'info');

        fetch(`<?php echo $base; ?>php/scripts/leave/clone_workflow.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                policyID: policyID,
                entityID: <?php echo $currentEntityID; ?>
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Success!', 'Workflow cloned successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Error', data.message || 'Failed to clone workflow', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error', 'Failed to clone workflow', 'error');
        });
    }
}

// Toggle workflow status
function toggleWorkflowStatus(policyID) {
    showToast('Updating Status', 'Please wait...', 'info');

    fetch(`<?php echo $base; ?>php/scripts/leave/toggle_workflow_status.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            policyID: policyID
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success!', 'Workflow status updated', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Error', data.message || 'Failed to update status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'Failed to update workflow status', 'error');
    });
}

// Set workflow as default
function setAsDefault(policyID) {
    showToast('Setting as Default', 'Please wait...', 'info');

    fetch(`<?php echo $base; ?>php/scripts/leave/set_default_workflow.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            policyID: policyID,
            entityID: <?php echo $currentEntityID; ?>
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success!', 'Default workflow updated', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Error', data.message || 'Failed to set as default', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'Failed to set as default', 'error');
    });
}

// Delete workflow
function deleteWorkflow(policyID) {
    if (confirm('Are you sure you want to delete this workflow? This action cannot be undone.')) {
        showToast('Deleting Workflow', 'Please wait...', 'info');

        fetch(`<?php echo $base; ?>php/scripts/leave/delete_workflow.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                policyID: policyID
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Success!', 'Workflow deleted successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Error', data.message || 'Failed to delete workflow', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error', 'Failed to delete workflow', 'error');
        });
    }
}

// Edit workflow
function editWorkflow(policyID) {
    showToast('Loading Workflow', 'Please wait...', 'info');

    fetch(`<?php echo $base; ?>php/scripts/leave/workflows/get_workflow_details.php?policyID=${policyID}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.workflow) {
                loadWorkflowIntoModal(data.workflow);
                const modal = new bootstrap.Modal(document.getElementById('createWorkflowModal'));
                modal.show();
            } else {
                showToast('Error', data.message || 'Failed to load workflow', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error', 'Failed to load workflow details', 'error');
        });
}

// Load workflow data into edit modal
function loadWorkflowIntoModal(workflow) {
    // Set edit mode
    document.getElementById('editPolicyID').value = workflow.policyID;
    document.getElementById('editPolicyName').value = workflow.policyName || '';
    document.getElementById('editPolicyDescription').value = workflow.policyDescription || '';

    // Set entity
    const entitySelect = document.querySelector('select[name="entityID"]');
    if (entitySelect) {
        entitySelect.value = workflow.entityID;
    }

    // Set checkboxes
    document.getElementById('requireAllApprovals').checked = workflow.requireAllApprovals === 'Y';
    document.getElementById('allowDelegation').checked = workflow.allowDelegation === 'Y';
    toggleDelegationSettings(); // Show/hide delegation settings based on checkbox

    // Set delegation settings
    if (workflow.autoDelegationOnLeave !== undefined) {
        document.getElementById('autoDelegationOnLeave').checked = workflow.autoDelegationOnLeave === 'Y';
    }
    if (workflow.allowSkipLevel !== undefined) {
        document.getElementById('allowSkipLevel').checked = workflow.allowSkipLevel === 'Y';
    }
    if (workflow.delegationMethod) {
        document.getElementById('delegationMethod').value = workflow.delegationMethod;
    }
    if (workflow.delegationPrompt) {
        document.getElementById('delegationPrompt').value = workflow.delegationPrompt;
    }

    // Set auto-approve threshold
    const autoApproveInput = document.querySelector('input[name="autoApproveThreshold"]');
    if (autoApproveInput) {
        autoApproveInput.value = workflow.autoApproveThreshold || '';
    }

    // Clear existing steps
    const container = document.getElementById('approvalStepsContainer');
    container.innerHTML = '';
    stepCounter = 0;

    // Load steps
    if (workflow.steps && workflow.steps.length > 0) {
        workflow.steps.forEach((step, index) => {
            addApprovalStep();
            const currentIndex = index + 1; // stepCounter is already incremented by addApprovalStep

            // Set step values
            const stepNameInput = document.querySelector(`[name="steps[${currentIndex}][name]"]`);
            const stepTypeSelect = document.querySelector(`[name="steps[${currentIndex}][type]"]`);
            const stepEscalationInput = document.querySelector(`[name="steps[${currentIndex}][escalation]"]`);
            const stepDescriptionInput = document.querySelector(`[name="steps[${currentIndex}][description]"]`);
            const stepRequiredCheckbox = document.querySelector(`[name="steps[${currentIndex}][required]"]`);
            const stepConditionalCheckbox = document.querySelector(`[name="steps[${currentIndex}][conditional]"]`);

            if (stepNameInput) stepNameInput.value = step.stepName || '';
            if (stepTypeSelect) stepTypeSelect.value = step.stepType || 'supervisor';
            if (stepEscalationInput) stepEscalationInput.value = step.escalationDays || 3;
            if (stepDescriptionInput) stepDescriptionInput.value = step.stepDescription || '';
            if (stepRequiredCheckbox) stepRequiredCheckbox.checked = step.isRequired === 'Y';
            if (stepConditionalCheckbox) stepConditionalCheckbox.checked = step.isConditional === 'Y';

            // Store stepID for editing (add hidden input)
            const stepDiv = document.getElementById(`approvalStep${currentIndex}`);
            if (stepDiv && step.stepID) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = `steps[${currentIndex}][stepID]`;
                hiddenInput.value = step.stepID;
                stepDiv.appendChild(hiddenInput);
            }
        });
    }

    // Update modal title and button
    const modalTitle = document.getElementById('workflowModalTitle');
    const saveBtn = document.getElementById('saveWorkflowBtn');

    if (modalTitle) {
        modalTitle.innerHTML = '<i class="ri-edit-line me-2"></i>Edit Approval Workflow';
    }
    if (saveBtn) {
        saveBtn.innerHTML = '<i class="ri-save-line me-1"></i>Update Workflow';
    }
}

// Reset workflow form to create mode
function resetWorkflowForm() {
    document.getElementById('createWorkflowForm').reset();
    document.getElementById('editPolicyID').value = '';
    document.getElementById('approvalStepsContainer').innerHTML = '';
    stepCounter = 0;

    // Reset modal title and button
    const modalTitle = document.getElementById('workflowModalTitle');
    const saveBtn = document.getElementById('saveWorkflowBtn');

    if (modalTitle) {
        modalTitle.innerHTML = '<i class="ri-add-line me-2"></i>Create New Approval Workflow';
    }
    if (saveBtn) {
        saveBtn.innerHTML = '<i class="ri-save-line me-1"></i>Create Workflow';
    }

    // Reset checkboxes to defaults
    document.getElementById('allowDelegation').checked = true;
    document.getElementById('requireAllApprovals').checked = false;
}

// Initialize template popovers
function initializeTemplatePopovers() {
    const templateCards = document.querySelectorAll('[data-template-info]');

    templateCards.forEach(card => {
        const templateInfo = JSON.parse(card.getAttribute('data-template-info'));

        // Build popover content
        const popoverContent = `
            <div class="template-popover-content">
                <h6 class="mb-2 text-primary">${templateInfo.title}</h6>
                <p class="mb-2 small"><strong>${templateInfo.levels}</strong></p>
                <div class="mb-2">
                    <strong class="small d-block mb-1">Approval Steps:</strong>
                    <ol class="small mb-0 ps-3">
                        ${templateInfo.approvers.map(a => `<li>${a}</li>`).join('')}
                    </ol>
                </div>
                <p class="mb-1 small"><strong>Best For:</strong> ${templateInfo.bestFor}</p>
                <p class="mb-0 small text-muted"><i class="ri-time-line me-1"></i>${templateInfo.estimatedTime}</p>
            </div>
        `;

        // Initialize Bootstrap popover
        new bootstrap.Popover(card, {
            content: popoverContent,
            html: true,
            trigger: 'hover focus',
            placement: 'top',
            container: 'body',
            customClass: 'template-popover'
        });
    });
}

// ============================================================================
// TEMPLATE MANAGEMENT FUNCTIONS
// ============================================================================

let templateStepCounter = 0;

// Load all templates
function loadTemplates() {
    // Load system templates
    loadSystemTemplates();
    // Load custom templates
    loadCustomTemplates();
    // Load public templates
    loadPublicTemplates();
}

// Load system templates
function loadSystemTemplates() {
    fetch('<?php echo $base; ?>php/scripts/leave/workflows/get_all_templates.php?type=system')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.templates) {
                // console.log(data.templates);
                renderTemplateCards(data.templates, 'systemTemplatesList', 'system');
            }
        })
        .catch(error => {
            console.error('Error loading system templates:', error);
            const listElement = document.getElementById('systemTemplatesList');
            if (listElement) {
                listElement.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="ri-error-warning-line display-4 text-danger mb-3"></i>
                        <h6 class="text-danger">Error Loading Templates</h6>
                        <p class="text-muted">Please refresh the page to try again</p>
                    </div>
                `;
            }
        });
}

// Load custom templates
function loadCustomTemplates() {
    fetch('<?php echo $base; ?>php/scripts/leave/workflows/get_all_templates.php?type=custom&entityID=<?php echo $currentEntityID; ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.templates) {
                renderTemplateCards(data.templates, 'customTemplatesList', 'custom');
                const countElement = document.getElementById('customTemplateCount');
                if (countElement) countElement.textContent = data.templates.length;
            } else {
                const listElement = document.getElementById('customTemplatesList');
                if (listElement) {
                    listElement.innerHTML = `
                        <div class="col-12 text-center py-5">
                            <i class="ri-file-list-3-line display-4 text-muted mb-3"></i>
                            <h6 class="text-muted">No Custom Templates Yet</h6>
                            <p class="text-muted">Create your first template or save a workflow as template</p>
                        </div>
                    `;
                }
            }
        })
        .catch(error => {
            console.error('Error loading custom templates:', error);
            const listElement = document.getElementById('customTemplatesList');
            if (listElement) {
                listElement.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="ri-error-warning-line display-4 text-danger mb-3"></i>
                        <h6 class="text-danger">Error Loading Templates</h6>
                        <p class="text-muted">Please refresh the page to try again</p>
                    </div>
                `;
            }
        });
}

// Load public templates
function loadPublicTemplates() {
    fetch('<?php echo $base; ?>php/scripts/leave/workflows/get_all_templates.php?type=public')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.templates) {
                renderTemplateCards(data.templates, 'publicTemplatesList', 'public');
                const countElement = document.getElementById('publicTemplateCount');
                if (countElement) countElement.textContent = data.templates.length;
            } else {
                const listElement = document.getElementById('publicTemplatesList');
                if (listElement) {
                    listElement.innerHTML = `
                        <div class="col-12 text-center py-5">
                            <i class="ri-global-line display-4 text-muted mb-3"></i>
                            <h6 class="text-muted">No Public Templates Yet</h6>
                            <p class="text-muted">Admins can make templates public to share with all entities</p>
                        </div>
                    `;
                }
            }
        })
        .catch(error => {
            console.error('Error loading public templates:', error);
            const listElement = document.getElementById('publicTemplatesList');
            if (listElement) {
                listElement.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="ri-error-warning-line display-4 text-danger mb-3"></i>
                        <h6 class="text-danger">Error Loading Templates</h6>
                        <p class="text-muted">Please refresh the page to try again</p>
                    </div>
                `;
            }
        });
}

// Render template cards
function renderTemplateCards(templates, containerId, type) {
    const container = document.getElementById(containerId);

    // Check if container exists
    if (!container) {
        console.warn(`Container element '${containerId}' not found`);
        return;
    }

    container.innerHTML = '';

    templates.forEach(template => {
        const card = document.createElement('div');
        card.className = 'col-md-6 col-lg-4 mb-3';

        const canEdit = (type == 'system');
        const badge = type === 'system' ? '<span class="badge bg-purple">System</span>' :
                     (template.isPublic === 'Y' ? '<span class="badge bg-success">Public</span>' : '<span class="badge bg-info">Private</span>');

        card.innerHTML = `
            <div class="card h-100 border">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0">${template.templateName}</h6>
                        ${badge}
                    </div>
                    <p class="small text-muted mb-3">${template.templateDescription}</p>
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="ri-list-check me-1"></i>${template.stepCount || 0} Steps
                            <span class="ms-2"><i class="ri-user-line me-1"></i>${template.usageCount} uses</span>
                        </small>
                    </div>
                    <div class="d-grid gap-2">
                        <button class="btn btn-sm btn-primary" onclick="useTemplate(${template.templateID})">
                            <i class="ri-download-line me-1"></i>Use Template
                        </button>
                        ${canEdit ? `
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary" onclick="editTemplate(${template.templateID})">
                                    <i class="ri-edit-line me-1"></i>Edit
                                </button>
                                <button class="btn btn-sm btn-outline-info" onclick="cloneTemplate(${template.templateID})">
                                    <i class="ri-file-copy-line me-1"></i>Clone
                                </button>
                                ${type === 'custom' ? `
                                    <button class="btn btn-sm btn-outline-success" onclick="toggleTemplateVisibility(${template.templateID})">
                                        <i class="ri-${template.isPublic === 'Y' ? 'lock' : 'global'}-line me-1"></i>
                                        ${template.isPublic === 'Y' ? 'Make Private' : 'Make Public'}
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteTemplate(${template.templateID})">
                                        <i class="ri-delete-bin-line me-1"></i>Delete
                                    </button>
                                ` : ''}
                            </div>
                        ` : `
                            <button class="btn btn-sm btn-outline-secondary" onclick="cloneTemplate(${template.templateID})">
                                <i class="ri-file-copy-line me-1"></i>Clone to Custom
                            </button>
                        `}
                    </div>
                </div>
            </div>
        `;

        container.appendChild(card);
    });
}

// Add template step
function addTemplateStep() {
    templateStepCounter++;
    const container = document.getElementById('templateStepsContainer');
    const stepDiv = document.createElement('div');
    stepDiv.className = 'template-step-item mb-3 p-3 border rounded bg-light';
    stepDiv.id = `templateStep${templateStepCounter}`;

    stepDiv.innerHTML = `
        <div class="d-flex justify-content-between align-items-start mb-3">
            <h6 class="mb-0">
                <span class="badge bg-primary">${templateStepCounter}</span>
                Step ${templateStepCounter}
            </h6>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeTemplateStep(${templateStepCounter})">
                <i class="ri-delete-bin-line"></i>
            </button>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label class="form-label small">Step Name</label>
                <input type="text" class="form-control form-control-sm" name="templateSteps[${templateStepCounter}][name]" required placeholder="e.g., Direct Supervisor Approval">
            </div>
            <div class="col-md-6">
                <label class="form-label small">Approver Type</label>
                <select class="form-select form-select-sm" name="templateSteps[${templateStepCounter}][type]" required>
                    <optgroup label="Auto-Identified (Dynamic)">
                        <option value="supervisor">Direct Supervisor</option>
                        <option value="project_manager">Project Manager</option>
                        <option value="department_head">Department Head</option>
                    </optgroup>
                    <optgroup label="Manual Selection Required">
                        <option value="hr_manager">HR Manager</option>
                        <option value="hr_representative">HR Representative</option>
                        <option value="custom_role">Custom Role</option>
                        <option value="specific_user">Specific User</option>
                    </optgroup>
                </select>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-8">
                <label class="form-label small">Description</label>
                <input type="text" class="form-control form-control-sm" name="templateSteps[${templateStepCounter}][description]" placeholder="Describe this approval step">
            </div>
            <div class="col-md-4">
                <label class="form-label small">Escalation (Days)</label>
                <input type="number" class="form-control form-control-sm" name="templateSteps[${templateStepCounter}][escalation]" min="0" max="30" value="3">
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="templateSteps[${templateStepCounter}][required]" checked>
                    <label class="form-check-label small">Required Step</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="templateSteps[${templateStepCounter}][conditional]">
                    <label class="form-check-label small">Conditional</label>
                </div>
            </div>
        </div>
    `;

    container.appendChild(stepDiv);
}

// Remove template step
function removeTemplateStep(stepId) {
    const step = document.getElementById(`templateStep${stepId}`);
    if (step) {
        step.remove();
        renumberTemplateSteps();
    }
}

// Renumber template steps
function renumberTemplateSteps() {
    const steps = document.querySelectorAll('.template-step-item');
    steps.forEach((step, index) => {
        const badge = step.querySelector('.badge');
        const heading = step.querySelector('h6');
        if (badge) badge.textContent = index + 1;
        if (heading) {
            const currentText = heading.innerHTML;
            heading.innerHTML = currentText.replace(/Step \d+/, `Step ${index + 1}`);
        }
    });
}

// Save template
function saveTemplate() {
    const form = document.getElementById('createTemplateForm');

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    const templateID = document.getElementById('editTemplateID').value;
    formData.append('action', templateID ? 'update_template' : 'create_template');
    formData.append('createdForEntityID', '<?php echo $currentEntityID; ?>');

    showToast('Saving Template', 'Please wait...', 'info');

    fetch('<?php echo $base; ?>php/scripts/leave/workflows/save_template.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success!', templateID ? 'Template updated successfully' : 'Template created successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('createTemplateModal')).hide();
            loadTemplates();
            form.reset();
            document.getElementById('templateStepsContainer').innerHTML = '';
            templateStepCounter = 0;
        } else {
            showToast('Error', data.message || 'Failed to save template', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'Failed to save template', 'error');
    });
}

// Edit template
function editTemplate(templateID) {
    showToast('Loading Template', 'Please wait...', 'info');

    fetch(`<?php echo $base; ?>php/scripts/leave/workflows/get_template_full.php?templateID=${templateID}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.template) {
                // Populate form
                document.getElementById('editTemplateID').value = data.template.templateID;
                document.getElementById('templateName').value = data.template.templateName;
                document.getElementById('templateDescription').value = data.template.templateDescription;
                document.getElementById('templateIsPublic').value = data.template.isPublic;
                document.getElementById('templateModalTitle').textContent = 'Edit Template';

                // Clear and populate steps
                document.getElementById('templateStepsContainer').innerHTML = '';
                templateStepCounter = 0;

                if (data.steps && data.steps.length > 0) {
                    data.steps.forEach(step => {
                        addTemplateStep();
                        const currentIndex = templateStepCounter;
                        document.querySelector(`[name="templateSteps[${currentIndex}][name]"]`).value = step.stepName;
                        document.querySelector(`[name="templateSteps[${currentIndex}][type]"]`).value = step.stepType;
                        document.querySelector(`[name="templateSteps[${currentIndex}][description]"]`).value = step.stepDescription || '';
                        document.querySelector(`[name="templateSteps[${currentIndex}][escalation]"]`).value = step.escalationDays || 3;
                        document.querySelector(`[name="templateSteps[${currentIndex}][required]"]`).checked = step.isRequired === 'Y';
                        document.querySelector(`[name="templateSteps[${currentIndex}][conditional]"]`).checked = step.isConditional === 'Y';
                    });
                }

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('createTemplateModal'));
                modal.show();
            } else {
                showToast('Error', data.message || 'Failed to load template', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error', 'Failed to load template', 'error');
        });
}

// Clone template
function cloneTemplate(templateID) {
    showToast('Cloning Template', 'Please wait...', 'info');

    fetch('<?php echo $base; ?>php/scripts/leave/workflows/clone_template.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            templateID: templateID,
            entityID: <?php echo $currentEntityID; ?>
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success!', 'Template cloned successfully', 'success');
            loadTemplates();
        } else {
            showToast('Error', data.message || 'Failed to clone template', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'Failed to clone template', 'error');
    });
}

// Toggle template visibility
function toggleTemplateVisibility(templateID) {
    showToast('Updating Visibility', 'Please wait...', 'info');

    fetch('<?php echo $base; ?>php/scripts/leave/workflows/toggle_template_visibility.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            templateID: templateID
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success!', `Template is now ${data.isPublic === 'Y' ? 'public' : 'private'}`, 'success');
            loadTemplates();
        } else {
            showToast('Error', data.message || 'Failed to update visibility', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'Failed to update template visibility', 'error');
    });
}

// Delete template
function deleteTemplate(templateID) {
    if (confirm('Are you sure you want to delete this template? This action cannot be undone.')) {
        showToast('Deleting Template', 'Please wait...', 'info');

        fetch('<?php echo $base; ?>php/scripts/leave/workflows/delete_template.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                templateID: templateID
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Success!', 'Template deleted successfully', 'success');
                loadTemplates();
            } else {
                showToast('Error', data.message || 'Failed to delete template', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error', 'Failed to delete template', 'error');
        });
    }
}

// Save workflow as template
function saveWorkflowAsTemplate() {
    // Show modal to select which workflow to save
    showToast('Feature Coming', 'Select a workflow card, click menu ⋮, then "Save as Template"', 'info');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add first approval step by default
    addApprovalStep();

    // Load entity workflow status
    updateEntityStatus();

    // Load templates if on templates tab (URL-based)
    <?php if ($currentWorkflowTab == 'templates'): ?>
    loadTemplates();
    <?php endif; ?>

    // Reset workflow form when create modal is opened (not when editing)
    const createWorkflowModal = document.getElementById('createWorkflowModal');
    if (createWorkflowModal) {
        createWorkflowModal.addEventListener('show.bs.modal', function(event) {
            // Only reset if not editing (no policyID in hidden field)
            const policyID = document.getElementById('editPolicyID').value;
            if (!policyID || policyID === '') {
                resetWorkflowForm();
                addApprovalStep(); // Add first step by default for new workflows
            }
        });
    }

    // Initialize template popovers when templates modal is shown
    const templatesModal = document.getElementById('templatesModal');
    if (templatesModal) {
        templatesModal.addEventListener('shown.bs.modal', function() {
            // Small delay to ensure templates are rendered
            setTimeout(initializeTemplatePopovers, 100);
        });
    }

    // Reset template form when modal is hidden
    document.getElementById('createTemplateModal')?.addEventListener('hidden.bs.modal', function() {
        document.getElementById('createTemplateForm').reset();
        document.getElementById('templateStepsContainer').innerHTML = '';
        document.getElementById('editTemplateID').value = '';
        document.getElementById('templateModalTitle').textContent = 'Create New Template';
        templateStepCounter = 0;
    });
});

function updateEntityStatus() {
    fetch(`<?php echo $base; ?>php/scripts/leave/utilities/get_entity_workflow_status.php?entityID=<?php echo $currentEntityID; ?>`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('workflowStatusText').innerHTML = `
                    <i class="ri-check-circle-line me-1"></i>
                    ${data.activeWorkflows} active workflow(s), ${data.totalPolicies} total
                `;
            }
        });
}

// Toast notification system
function showToast(title, message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }

    // Determine icon and color based on type
    let icon, bgClass, textClass;
    switch(type) {
        case 'success':
            icon = 'ri-checkbox-circle-line';
            bgClass = 'bg-success';
            textClass = 'text-white';
            break;
        case 'error':
            icon = 'ri-error-warning-line';
            bgClass = 'bg-danger';
            textClass = 'text-white';
            break;
        case 'warning':
            icon = 'ri-alert-line';
            bgClass = 'bg-warning';
            textClass = 'text-dark';
            break;
        default:
            icon = 'ri-information-line';
            bgClass = 'bg-info';
            textClass = 'text-white';
    }

    // Create toast element
    const toastId = 'toast_' + Date.now();
    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center ${bgClass} ${textClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="${icon} me-2"></i>
                    <strong>${title}</strong> ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    toastContainer.insertAdjacentHTML('beforeend', toastHTML);

    // Initialize and show toast
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: type === 'error' ? 5000 : 3000
    });
    toast.show();

    // Remove from DOM after hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}
</script>

