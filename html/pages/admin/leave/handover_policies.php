<?php
/**
 * Handover Policies Management
 *
 * Provides an admin UI to configure enterprise handover policies with role/job-level targeting.
 */

if (!$isValidAdmin && !$isAdmin && !$isSuperAdmin && !$isHRManager) {
    Alert::error("You do not have permission to manage handover policies.", true, array('text-center'));
    echo "<div class='container py-5'><a class='btn btn-outline-primary' href='{$base}html/?s=admin&ss=leave&p=dashboard'>Back to Leave Admin</a></div>";
    return;
}

include_once 'php/functions/leave_handover_ui_helpers.php';

$selectedEntityID = isset($_GET['entityID'])
    ? (int)Utility::clean_string($_GET['entityID'])
    : ($userDetails->entityID ?? null);

if (!$selectedEntityID) {
    $entities = Data::entities(array('Lapsed' => 'N'), false, $DBConn);
    $selectedEntityID = $entities && isset($entities[0]) ? ($entities[0]->entityID ?? null) : null;
}

$policies = LeaveHandoverPolicy::get_policies(array('entityID' => $selectedEntityID), $DBConn);
$leaveTypes = Leave::leave_types(array('Lapsed' => 'N'), false, $DBConn);

$policyScopes = array(
    'entity_wide' => 'Entity Wide',
    'role_based' => 'Role Based',
    'job_group' => 'Job Group',
    'job_level' => 'Job Level',
    'job_title' => 'Job Title'
);
?>

<div class="container-fluid py-4 handover-policies-page">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1">Leave Handover Policies</h2>
            <p class="text-muted mb-0">Configure policy-driven handover rules by entity, role, job group, or job level.</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#handoverPolicyModal" data-mode="create">
                <i class="ri-add-line me-1"></i>Create Policy
            </button>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form class="row g-3 align-items-end" method="get">
                <input type="hidden" name="s" value="admin">
                <input type="hidden" name="ss" value="leave">
                <input type="hidden" name="p" value="handover_policies">
                <div class="col-md-4">
                    <label for="entityID" class="form-label">Entity</label>
                    <select class="form-select" id="entityID" name="entityID">
                        <?php
                        $entities = Data::entities(array('Lapsed' => 'N'), false, $DBConn);
                        if ($entities) :
                            foreach ($entities as $entity) :
                                $entityID = $entity->entityID ?? null;
                                $entityName = $entity->entityName ?? "Entity {$entityID}";
                                ?>
                                <option value="<?= (int)$entityID ?>" <?= (int)$entityID === (int)$selectedEntityID ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($entityName) ?>
                                </option>
                            <?php
                            endforeach;
                        endif;
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="policyScopeFilter" class="form-label">Scope</label>
                    <select class="form-select" id="policyScopeFilter" name="policyScope">
                        <option value="">All scopes</option>
                        <?php foreach ($policyScopes as $scopeKey => $scopeLabel): ?>
                            <option value="<?= $scopeKey ?>" <?= (isset($_GET['policyScope']) && $_GET['policyScope'] === $scopeKey) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($scopeLabel) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="ri-filter-3-line me-1"></i>Apply Filters
                    </button>
                    <a href="<?= "{$base}html/?s=admin&ss=leave&p=handover_policies" ?>" class="btn btn-link">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Configured Policies</h5>
            <small class="text-muted">Scope priority: Job Title → Job Level → Job Group → Role → Entity-wide</small>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Policy</th>
                        <th>Scope</th>
                        <th>Leave Type</th>
                        <th>Mandatory</th>
                        <th>Min Days</th>
                        <th>Nominee Response</th>
                        <th>Revisions</th>
                        <th>Effective</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($policies && count($policies) > 0): ?>
                        <?php foreach ($policies as $policy):
                            $policy = is_object($policy) ? $policy : (object)$policy;
                            $leaveTypeObj = null;
                            if (!empty($policy->leaveTypeID)) {
                                $leaveTypeObj = Leave::leave_types(array('leaveTypeID' => $policy->leaveTypeID), true, $DBConn);
                            }
                            $scopeLabel = $policyScopes[$policy->policyScope] ?? ucfirst(str_replace('_', ' ', $policy->policyScope));
                            ?>
                            <tr data-policy-id="<?= (int)$policy->policyID ?>">
                                <td>
                                    <strong><?= htmlspecialchars($policy->policyName ?? 'Untitled Policy') ?></strong>
                                    <div class="text-muted small"><?= htmlspecialchars($policy->policyDescription ?? 'No description provided.') ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark"><?= htmlspecialchars($scopeLabel) ?></span>
                                </td>
                                <td><?= htmlspecialchars($leaveTypeObj->leaveTypeName ?? 'All leave types') ?></td>
                                <td><?= $policy->isMandatory === 'Y' ? '<span class="badge bg-danger">Yes</span>' : '<span class="badge bg-secondary">Optional</span>' ?></td>
                                <td><?= (int)$policy->minHandoverDays ?> days</td>
                                <td>
                                    <div>Required: <strong><?= $policy->requireNomineeAcceptance === 'Y' ? 'Yes' : 'No' ?></strong></div>
                                    <div class="text-muted small">Deadline: <?= (int)$policy->nomineeResponseDeadlineHours ?>h</div>
                                </td>
                                <td>
                                    <?= $policy->allowPeerRevision === 'Y' ? (int)$policy->maxRevisionAttempts . ' attempts' : 'Disabled' ?>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($policy->effectiveDate) ?></div>
                                    <div class="text-muted small">
                                        <?= $policy->expiryDate ? 'Ends ' . htmlspecialchars($policy->expiryDate) : 'No expiry' ?>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-secondary me-1" data-action="edit-policy" data-policy='<?= json_encode($policy) ?>'>
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" data-action="delete-policy" data-policy-id="<?= (int)$policy->policyID ?>">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="text-muted">No policies configured for this entity yet.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Policy Modal -->
<div class="modal fade" id="handoverPolicyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="handoverPolicyForm">
                <div class="modal-header">
                    <h5 class="modal-title">Create Policy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="policyID" id="policyID">
                    <input type="hidden" name="entityID" value="<?= (int)$selectedEntityID ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Policy Name</label>
                            <input type="text" class="form-control" name="policyName" id="policyName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Leave Type</label>
                            <select class="form-select" name="leaveTypeID" id="leaveTypeID">
                                <option value="">All leave types</option>
                                <?php if ($leaveTypes): ?>
                                    <?php foreach ($leaveTypes as $leaveType): ?>
                                        <option value="<?= (int)$leaveType->leaveTypeID ?>">
                                            <?= htmlspecialchars($leaveType->leaveTypeName) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Scope</label>
                            <select class="form-select" name="policyScope" id="policyScope" required>
                                <?php foreach ($policyScopes as $key => $label): ?>
                                    <option value="<?= $key ?>"><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Min Handover Days</label>
                            <input type="number" min="0" class="form-control" name="minHandoverDays" id="minHandoverDays" value="0">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="policyDescription" id="policyDescription" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mandatory Handover</label>
                            <select class="form-select" name="isMandatory" id="isMandatory">
                                <option value="Y">Yes - block submission without handover</option>
                                <option value="N">No - optional</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nominee Response Deadline (hours)</label>
                            <input type="number" min="1" class="form-control" name="nomineeResponseDeadlineHours" id="nomineeResponseDeadlineHours" value="48">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Allow Peer Revisions</label>
                            <select class="form-select" name="allowPeerRevision" id="allowPeerRevision">
                                <option value="Y">Yes</option>
                                <option value="N">No</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Max Revision Attempts</label>
                            <input type="number" min="0" class="form-control" name="maxRevisionAttempts" id="maxRevisionAttempts" value="3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Effective Date</label>
                            <input type="date" class="form-control" name="effectiveDate" id="effectiveDate" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Expiry Date</label>
                            <input type="date" class="form-control" name="expiryDate" id="expiryDate">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Advanced Requirements</label>
                            <div class="row">
                                <?php
                                $requirementFields = array(
                                    'requireConfirmation' => 'Peer Confirmation',
                                    'requireTraining' => 'Training / Knowledge Transfer',
                                    'requireCredentials' => 'Credentials / Access',
                                    'requireTools' => 'Tools / Equipment',
                                    'requireDocuments' => 'Documents / SOPs'
                                );
                                foreach ($requirementFields as $field => $label) : ?>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label small"><?= htmlspecialchars($label) ?></label>
                                        <select class="form-select form-select-sm" name="<?= $field ?>" id="<?= $field ?>">
                                            <option value="Y">Required</option>
                                            <option value="N">Optional</option>
                                        </select>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="default-label">Save Policy</span>
                        <span class="loading-label d-none"><span class="spinner-border spinner-border-sm me-1"></span>Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    const policyModal = document.getElementById('handoverPolicyModal');
    const policyForm = document.getElementById('handoverPolicyForm');
    const policyTable = document.querySelector('.handover-policies-page table tbody');

    function setFormLoading(isLoading) {
        const defaultLabel = policyForm.querySelector('.default-label');
        const loadingLabel = policyForm.querySelector('.loading-label');
        if (isLoading) {
            defaultLabel.classList.add('d-none');
            loadingLabel.classList.remove('d-none');
        } else {
            defaultLabel.classList.remove('d-none');
            loadingLabel.classList.add('d-none');
        }
    }

    policyModal?.addEventListener('show.bs.modal', function(event) {
        const triggerButton = event.relatedTarget;
        const mode = triggerButton?.dataset?.mode || 'create';
        policyForm.reset();
        policyForm.querySelector('#policyID').value = '';
        policyModal.querySelector('.modal-title').textContent = mode === 'edit' ? 'Edit Policy' : 'Create Policy';
        if (mode === 'create') {
            policyForm.querySelector('[name=\"action\"]').value = 'create';
            return;
        }

        const policyData = triggerButton?.dataset?.policy ? JSON.parse(triggerButton.dataset.policy) : null;
        if (policyData) {
            Object.keys(policyData).forEach(key => {
                const input = policyForm.querySelector(`[name=\"${key}\"]`);
                if (input) {
                    input.value = policyData[key];
                }
            });
            policyForm.querySelector('#policyID').value = policyData.policyID;
        }
    });

    policyForm?.addEventListener('submit', async function(event) {
        event.preventDefault();
        const formData = new FormData(policyForm);
        const isEdit = !!formData.get('policyID');
        formData.append('action', isEdit ? 'update' : 'create');
        setFormLoading(true);

        try {
            const response = await fetch('<?= $base ?>php/scripts/leave/policies/manage_handover_policy.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.success) {
                location.reload();
            } else {
                alert(result.message || 'Failed to save policy.');
            }
        } catch (error) {
            console.error(error);
            alert('An error occurred while saving the policy.');
        } finally {
            setFormLoading(false);
        }
    });

    policyTable?.addEventListener('click', async function(event) {
        const actionButton = event.target.closest('[data-action]');
        if (!actionButton) {
            return;
        }
        const action = actionButton.dataset.action;
        const policyID = actionButton.dataset.policyId;

        if (action === 'delete-policy' && policyID) {
            if (!confirm('Are you sure you want to delete this policy?')) {
                return;
            }
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('policyID', policyID);

            try {
                const response = await fetch('<?= $base ?>php/scripts/leave/policies/manage_handover_policy.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    location.reload();
                } else {
                    alert(result.message || 'Failed to delete policy.');
                }
            } catch (error) {
                console.error(error);
                alert('An error occurred while deleting the policy.');
            }
        }
    });
})();
</script>

