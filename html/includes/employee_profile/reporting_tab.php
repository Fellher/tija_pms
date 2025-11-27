<?php
/**
 * Reporting Structure Tab - Complete Implementation
 * Supervisors (Primary + Additional) & Subordinates
 */

// Get additional supervisor relationships (excluding primary in user_details)
$additionalSupervisors = Employee::get_additional_supervisors($employeeID, $DBConn);
//please filter supervisors with refference to job title and so that the list only contains supervisors with the same job title ranked higher  than the current user in the reporting structure
$entityID = $employeeDetails->entityID;
$jobTitle = $employeeDetails->jobTitle;
// $reportingStructure = Employee::get_reporting_structure($entityID, $DBConn);

/**
 * get the entity ID of the current user
 * get the job title of the current user
 * get the reporting structure of the entity
 * filter the supervisors with the same job title and those that are ranked higher than the current user in the reporting structure
 * return the filtered supervisors
 */

?>

<div class="section-header">
    <h5 class="mb-0"><i class="ri-organization-chart me-2"></i>Reporting Structure</h5>
</div>

<div class="row">
    <!-- Primary Supervisor (from user_details) -->
    <div class="col-md-6">
        <div class="info-card">
            <h6 class="fw-bold mb-3"><i class="ri-user-star-line me-2"></i>Primary Supervisor</h6>

            <?php if (!empty($employeeDetails->supervisorID)):
                $supervisor = Employee::employees(['ID' => $employeeDetails->supervisorID], true, $DBConn);
            ?>
            <div class="text-center py-3">
                <img src="<?= !empty($supervisor->profile_image)
                    ? "{$config['DataDir']}{$supervisor->profile_image}"
                    : "{$base}assets/img/users/8.jpg" ?>"
                    class="rounded-circle mb-2"
                    style="width: 80px; height: 80px; object-fit: cover;">
                <h6 class="mb-1"><?= htmlspecialchars($supervisor->employeeName ?? 'Unknown') ?></h6>
                <p class="text-muted mb-2"><?= htmlspecialchars($supervisor->jobTitle ?? 'N/A') ?></p>
                <p class="mb-0 small">
                    <i class="ri-mail-line me-1"></i><?= htmlspecialchars($supervisor->Email ?? '') ?><br>
                    <i class="ri-phone-line me-1"></i><?= htmlspecialchars($supervisor->phoneNo ?? '') ?>
                </p>
            </div>
            <?php else: ?>
            <div class="alert alert-info">
                <i class="ri-information-line me-2"></i>
                No primary supervisor assigned
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Additional Supervisors -->
    <div class="col-md-6">
        <div class="info-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0"><i class="ri-group-line me-2"></i>Additional Supervisors</h6>
                <?php if ($canEdit): ?>
                <button class="btn btn-sm btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#supervisorModal"
                        onclick="prepareAddSupervisor()">
                    <i class="ri-add-line me-1"></i> Add Supervisor
                </button>
                <?php endif; ?>
            </div>

            <?php if ($additionalSupervisors && count($additionalSupervisors) > 0): ?>
            <div class="supervisor-list">
                <?php foreach ($additionalSupervisors as $supRel): ?>
                <div class="supervisor-item mb-3 p-3 border rounded">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <img src="<?= !empty($supRel->supervisorImage)
                                    ? "{$config['DataDir']}{$supRel->supervisorImage}"
                                    : "{$base}assets/img/users/8.jpg" ?>"
                                    class="rounded-circle me-2"
                                    style="width: 40px; height: 40px; object-fit: cover;">
                                <div>
                                    <h6 class="mb-0">
                                        <?= htmlspecialchars($supRel->supervisorName) ?>
                                        <?php if ($supRel->isPrimary == 'Y'): ?>
                                        <span class="badge bg-primary ms-1">Primary</span>
                                        <?php endif; ?>
                                    </h6>
                                    <small class="text-muted"><?= htmlspecialchars($supRel->supervisorJobTitle ?? 'N/A') ?></small>
                                </div>
                            </div>

                            <div class="supervisor-details small">
                                <div class="mb-1">
                                    <i class="ri-link me-1 text-muted"></i>
                                    <span class="badge bg-info-transparent">
                                        <?= ucfirst(str_replace('-', ' ', $supRel->relationshipType)) ?>
                                    </span>
                                    <?php if ($supRel->percentage < 100): ?>
                                    <span class="badge bg-secondary-transparent ms-1">
                                        <?= number_format($supRel->percentage, 0) ?>% Time
                                    </span>
                                    <?php endif; ?>
                                    <?php if ($supRel->isActive != 'Y'): ?>
                                    <span class="badge bg-warning-transparent ms-1">Inactive</span>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($supRel->scope)): ?>
                                <div class="mb-1">
                                    <i class="ri-focus-line me-1 text-muted"></i>
                                    <strong>Scope:</strong> <?= htmlspecialchars($supRel->scope) ?>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($supRel->effectiveDate)): ?>
                                <div class="mb-1">
                                    <i class="ri-calendar-line me-1 text-muted"></i>
                                    <strong>Since:</strong> <?= date('M j, Y', strtotime($supRel->effectiveDate)) ?>
                                    <?php if (!empty($supRel->endDate)): ?>
                                    → <?= date('M j, Y', strtotime($supRel->endDate)) ?>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($canEdit): ?>
                        <div class="btn-group btn-group-sm ms-2">
                            <button class="btn btn-sm btn-icon btn-primary-light"
                                    data-bs-toggle="modal"
                                    data-bs-target="#supervisorModal"
                                    onclick="editSupervisorRelationship(<?= $supRel->relationshipID ?>)"
                                    title="Edit">
                                <i class="ri-edit-line"></i>
                            </button>
                            <button class="btn btn-sm btn-icon btn-danger-light"
                                    onclick="deleteSupervisorRelationship(<?= $supRel->relationshipID ?>)"
                                    title="Delete">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="alert alert-info">
                <i class="ri-information-line me-2"></i>
                No additional supervisor relationships. Click "Add Supervisor" to add dotted-line or functional reporting.
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Subordinates -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0"><i class="ri-team-line me-2"></i>Direct Reports</h6>
        </div>

        <?php
        // Get direct reports (employees who have this person as primary supervisor)
        $subordinates = Employee::employees(['supervisorID' => $employeeID], false, $DBConn);

        // Also get employees with this person as additional supervisor
        $additionalSubordinates = Employee::get_additional_subordinates($employeeID, $DBConn);
        ?>

        <?php if ($subordinates && count($subordinates) > 0): ?>
        <h6 class="text-muted mb-2">Primary Reports</h6>
        <div class="row">
            <?php foreach ($subordinates as $subordinate): ?>
            <div class="col-md-4 mb-3">
                <div class="info-card text-center">
                    <img src="<?= !empty($subordinate->profile_image)
                        ? "{$config['DataDir']}{$subordinate->profile_image}"
                        : "{$base}assets/img/users/8.jpg" ?>"
                        class="rounded-circle mb-2"
                        style="width: 60px; height: 60px; object-fit: cover;">
                    <h6 class="mb-1"><?= htmlspecialchars($subordinate->employeeName) ?></h6>
                    <p class="text-muted small mb-2"><?= htmlspecialchars($subordinate->jobTitle ?? 'N/A') ?></p>
                    <span class="badge bg-primary-transparent mb-2">Direct Report</span>
                    <br>
                    <a href="?s=user&p=profile&uid=<?= $subordinate->ID ?>" class="btn btn-sm btn-outline-primary mt-2">
                        <i class="ri-user-line me-1"></i>View Profile
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($additionalSubordinates && count($additionalSubordinates) > 0): ?>
        <h6 class="text-muted mb-2 mt-3">Additional Reports</h6>
        <div class="row">
            <?php foreach ($additionalSubordinates as $subRel): ?>
            <div class="col-md-4 mb-3">
                <div class="info-card text-center">
                    <img src="<?= !empty($subRel->employeeImage)
                        ? "{$config['DataDir']}{$subRel->employeeImage}"
                        : "{$base}assets/img/users/8.jpg" ?>"
                        class="rounded-circle mb-2"
                        style="width: 60px; height: 60px; object-fit: cover;">
                    <h6 class="mb-1"><?= htmlspecialchars($subRel->employeeName) ?></h6>
                    <p class="text-muted small mb-2"><?= htmlspecialchars($subRel->employeeJobTitle ?? 'N/A') ?></p>
                    <span class="badge bg-info-transparent mb-2">
                        <?= ucfirst(str_replace('-', ' ', $subRel->relationshipType)) ?>
                    </span>
                    <br>
                    <a href="?s=user&p=profile&uid=<?= $subRel->employeeID ?>" class="btn btn-sm btn-outline-primary mt-2">
                        <i class="ri-user-line me-1"></i>View Profile
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ((!$subordinates || count($subordinates) == 0) && (!$additionalSubordinates || count($additionalSubordinates) == 0)): ?>
        <div class="alert alert-info">
            <i class="ri-information-line me-2"></i>
            No direct reports assigned to this employee.
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Include Modal -->
<?php include __DIR__ . '/modals/supervisor_relationship_modal.php'; ?>

<script>
// ========================================
// DATE PICKERS INITIALIZATION
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    initializeSupervisorDatePickers();

    const supModal = document.getElementById('supervisorModal');
    if (supModal) {
        supModal.addEventListener('shown.bs.modal', initializeSupervisorDatePickers);
    }
});

function initializeSupervisorDatePickers() {
    if (typeof flatpickr !== 'undefined') {
        const dateInputs = document.querySelectorAll('.supervisor-datepicker');
        dateInputs.forEach(input => {
            if (!input._flatpickr) {
                input.removeAttribute('readonly');
                flatpickr(input, {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'F j, Y',
                    allowInput: false,
                    onChange: function(selectedDates, dateStr, instance) {
                        if (dateStr) {
                            input.value = dateStr;
                        }
                    }
                });
            }
        });
    }
}

// ========================================
// SUPERVISOR RELATIONSHIP FUNCTIONS
// ========================================

function prepareAddSupervisor() {
    const form = document.getElementById('supervisorForm');
    if (form) {
        form.reset();
    }

    document.getElementById('relationshipID').value = '';
    document.getElementById('supervisorModalLabel').textContent = 'Add Supervisor Relationship';
    document.getElementById('isActiveRelationship').checked = true;
    document.getElementById('percentage').value = 100;
}

function editSupervisorRelationship(relationshipID) {
    fetch(`<?= $base ?>php/scripts/global/admin/supervisor_relationships_api.php?action=get_supervisor_relationship&id=${relationshipID}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateSupervisorForm(data.data);
                document.getElementById('supervisorModalLabel').textContent = 'Edit Supervisor Relationship';
            } else {
                showToast(data.message || 'Failed to load relationship', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while loading relationship', 'danger');
        });
}

function populateSupervisorForm(rel) {
    document.getElementById('relationshipID').value = rel.relationshipID;
    document.getElementById('supervisorID').value = rel.supervisorID || '';
    document.getElementById('relationshipType').value = rel.relationshipType || 'direct';
    document.getElementById('percentage').value = rel.percentage || 100;
    document.getElementById('scope').value = rel.scope || '';
    document.getElementById('department').value = rel.department || '';
    document.getElementById('isPrimarySupervisor').checked = (rel.isPrimary === 'Y');
    document.getElementById('isActiveRelationship').checked = (rel.isActive === 'Y');
    document.getElementById('supervisorNotes').value = rel.notes || '';

    // Set dates via flatpickr
    const effectiveInput = document.getElementById('effectiveDate');
    if (effectiveInput && rel.effectiveDate) {
        if (effectiveInput._flatpickr) {
            effectiveInput._flatpickr.clear();
            setTimeout(() => {
                effectiveInput._flatpickr.setDate(rel.effectiveDate, true);
            }, 100);
        }
    }

    const endInput = document.getElementById('endDate');
    if (endInput && rel.endDate) {
        if (endInput._flatpickr) {
            endInput._flatpickr.clear();
            setTimeout(() => {
                endInput._flatpickr.setDate(rel.endDate, true);
            }, 100);
        }
    }
}

function saveSupervisorRelationship(event) {
    event.preventDefault();

    // Extract dates from Flatpickr
    const effectiveInput = document.getElementById('effectiveDate');
    if (effectiveInput && effectiveInput._flatpickr) {
        const selectedDate = effectiveInput._flatpickr.selectedDates[0];
        if (selectedDate) {
            const year = selectedDate.getFullYear();
            const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
            const day = String(selectedDate.getDate()).padStart(2, '0');
            effectiveInput.value = `${year}-${month}-${day}`;
        }
    }

    const endInput = document.getElementById('endDate');
    if (endInput && endInput._flatpickr) {
        const selectedDate = endInput._flatpickr.selectedDates[0];
        if (selectedDate) {
            const year = selectedDate.getFullYear();
            const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
            const day = String(selectedDate.getDate()).padStart(2, '0');
            endInput.value = `${year}-${month}-${day}`;
        }
    }

    const formData = new FormData(event.target);
    formData.append('action', 'save_supervisor_relationship');

    const submitBtn = event.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i> Saving...';

    fetch('<?= $base ?>php/scripts/global/admin/supervisor_relationships_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Supervisor relationship saved successfully', 'success');
            setTimeout(() => {
                window.location.href = '?s=<?= $s ?>&p=<?= $p ?>&uid=<?= $employeeID ?>&tab=reporting';
            }, 1500);
        } else {
            showToast(data.message || 'Failed to save relationship', 'danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="ri-save-line me-1"></i> Save Relationship';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while saving relationship', 'danger');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="ri-save-line me-1"></i> Save Relationship';
    });
}

function deleteSupervisorRelationship(relationshipID) {
    if (!confirm('Are you sure you want to delete this supervisor relationship?')) {
        return;
    }

    fetch(`<?= $base ?>php/scripts/global/admin/supervisor_relationships_api.php?action=delete_supervisor_relationship&id=${relationshipID}`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Relationship deleted successfully', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast(data.message || 'Failed to delete relationship', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while deleting relationship', 'danger');
    });
}

// ========================================
// TOAST NOTIFICATION
// ========================================
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();

    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' :
                   type === 'danger' ? 'bg-danger' :
                   type === 'warning' ? 'bg-warning' : 'bg-info';

    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    toastContainer.insertAdjacentHTML('beforeend', toastHTML);

    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
    toast.show();

    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}
</script>

<style>
.supervisor-item {
    transition: all 0.3s ease;
}

.supervisor-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.supervisor-details {
    color: #6c757d;
}
</style>

