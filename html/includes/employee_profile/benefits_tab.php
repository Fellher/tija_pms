<?php
/**
 * Benefits Tab - Complete Implementation
 * Manages employee benefit enrollments
 */

// Get all benefit enrollments for this employee
$allBenefits = EmployeeProfileExtended::get_benefits_full(['employeeID' => $employeeID], false, $DBConn);

// Group benefits by category
$benefitsByCategory = [
    'insurance' => [],
    'pension' => [],
    'allowance' => [],
    'wellness' => [],
    'other' => []
];

if ($allBenefits) {
    foreach ($allBenefits as $benefit) {
        $category = $benefit->benefitCategory ?? 'other';
        $benefitsByCategory[$category][] = $benefit;
    }
}

// Category icons and titles
$categoryIcons = [
    'insurance' => ['icon' => 'ri-shield-cross-line', 'color' => 'primary', 'title' => 'Insurance Benefits'],
    'pension' => ['icon' => 'ri-funds-line', 'color' => 'success', 'title' => 'Pension & Retirement'],
    'allowance' => ['icon' => 'ri-money-dollar-circle-line', 'color' => 'info', 'title' => 'Allowances'],
    'wellness' => ['icon' => 'ri-heart-pulse-line', 'color' => 'danger', 'title' => 'Wellness Benefits'],
    'other' => ['icon' => 'ri-gift-line', 'color' => 'secondary', 'title' => 'Other Benefits']
];
?>

<div class="section-header">
    <h5 class="mb-0"><i class="ri-shield-check-line me-2"></i>Employee Benefits</h5>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">Benefits Enrollment</h6>
            <?php if ($canViewSalary || $isHRManager): ?>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#benefitModal" onclick="prepareAddBenefit()">
                <i class="ri-add-line me-1"></i> Enroll in Benefit
            </button>
            <?php endif; ?>
        </div>

        <?php if ($allBenefits && count($allBenefits) > 0): ?>
        <!-- Benefits by Category -->
        <?php foreach ($benefitsByCategory as $catKey => $benefits): ?>
            <?php if (!empty($benefits)):
                $catInfo = $categoryIcons[$catKey];
            ?>
            <div class="mb-4">
                <h6 class="fw-bold mb-3">
                    <i class="<?= $catInfo['icon'] ?> me-2 text-<?= $catInfo['color'] ?>"></i>
                    <?= $catInfo['title'] ?>
                </h6>

                <div class="row">
                    <?php foreach ($benefits as $benefit): ?>
                    <div class="col-md-6 mb-3">
                        <div class="info-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <h6 class="fw-bold mb-0"><?= htmlspecialchars($benefit->benefitName) ?></h6>
                                        <?php if ($benefit->isActive == 'Y'): ?>
                                        <span class="badge bg-success-transparent ms-2">Active</span>
                                        <?php else: ?>
                                        <span class="badge bg-warning-transparent ms-2">Inactive</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="benefit-details small">
                                        <?php if (!empty($benefit->providerName)): ?>
                                        <div class="mb-1">
                                            <i class="ri-hospital-line me-2 text-muted"></i>
                                            <strong>Provider:</strong> <?= htmlspecialchars($benefit->providerName) ?>
                                        </div>
                                        <?php endif; ?>

                                        <?php if (!empty($benefit->memberNumber)): ?>
                                        <div class="mb-1">
                                            <i class="ri-bank-card-line me-2 text-muted"></i>
                                            <strong>Member #:</strong> <?= htmlspecialchars($benefit->memberNumber) ?>
                                        </div>
                                        <?php endif; ?>

                                        <div class="mb-1">
                                            <i class="ri-user-line me-2 text-muted"></i>
                                            <strong>Coverage:</strong>
                                            <?php
                                            $coverageLabels = [
                                                'individual' => 'Individual Only',
                                                'spouse' => 'Individual + Spouse',
                                                'family' => 'Family',
                                                'children' => 'Individual + Children'
                                            ];
                                            echo $coverageLabels[$benefit->coverageLevel] ?? $benefit->coverageLevel;
                                            ?>
                                        </div>

                                        <div class="mb-1">
                                            <i class="ri-calendar-line me-2 text-muted"></i>
                                            <strong>Period:</strong>
                                            <?= date('M j, Y', strtotime($benefit->effectiveDate)) ?>
                                            <?php if ($benefit->endDate): ?>
                                            - <?= date('M j, Y', strtotime($benefit->endDate)) ?>
                                            <?php else: ?>
                                            - Ongoing
                                            <?php endif; ?>
                                        </div>

                                        <?php if ($benefit->totalPremium > 0): ?>
                                        <div class="mb-1">
                                            <i class="ri-money-dollar-circle-line me-2 text-muted"></i>
                                            <strong>Premium:</strong> KES <?= number_format($benefit->totalPremium, 2) ?>
                                            <small class="text-muted">(<?= $benefit->contributionFrequency ?>)</small>
                                        </div>
                                        <div class="ms-4 small text-muted">
                                            Employer: KES <?= number_format($benefit->employerContribution, 2) ?> |
                                            Employee: KES <?= number_format($benefit->employeeContribution, 2) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if ($canEdit): ?>
                                <div class="btn-group btn-group-sm ms-2">
                                    <button class="btn btn-sm btn-icon btn-primary-light"
                                            data-bs-toggle="modal"
                                            data-bs-target="#benefitModal"
                                            onclick="editBenefit(<?= $benefit->benefitID ?>)"
                                            title="Edit">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    <button class="btn btn-sm btn-icon btn-danger-light"
                                            onclick="deleteBenefit(<?= $benefit->benefitID ?>)"
                                            title="Delete">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php else: ?>
        <div class="alert alert-info">
            <i class="ri-information-line me-2"></i>
            No benefit enrollments on record. Click "Enroll in Benefit" to add employee benefits.
        </div>
        <?php endif; ?>

        <!-- Statutory Benefits Info -->
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="info-card">
                    <h6 class="fw-bold mb-3">
                        <i class="ri-hospital-line me-2 text-info"></i>
                        NHIF
                    </h6>
                    <div class="data-row">
                        <span class="data-label">NHIF Number:</span>
                        <span class="data-value"><?= htmlspecialchars($employeeDetails->nhifNumber ?? 'Not registered') ?></span>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="info-card">
                    <h6 class="fw-bold mb-3">
                        <i class="ri-funds-line me-2 text-success"></i>
                        NSSF
                    </h6>
                    <div class="data-row">
                        <span class="data-label">NSSF Number:</span>
                        <span class="data-value"><?= htmlspecialchars($employeeDetails->nssfNumber ?? 'Not registered') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Modal -->
<?php include __DIR__ . '/modals/benefit_enrollment_modal.php'; ?>

<script>
// Initialize date pickers
document.addEventListener('DOMContentLoaded', function() {
    initializeBenefitDatePickers();

    const benefitModal = document.getElementById('benefitModal');
    if (benefitModal) {
        benefitModal.addEventListener('shown.bs.modal', initializeBenefitDatePickers);
    }
});

function initializeBenefitDatePickers() {
    if (typeof flatpickr !== 'undefined') {
        document.querySelectorAll('.benefit-datepicker').forEach(input => {
            if (!input._flatpickr) {
                input.removeAttribute('readonly');
                flatpickr(input, {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'F j, Y',
                    allowInput: false,
                    onChange: function(selectedDates, dateStr, instance) {
                        if (dateStr) input.value = dateStr;
                    }
                });
            }
        });
    }
}

// Helper to extract date from Flatpickr
function extractBenefitDateValue(inputId) {
    const input = document.getElementById(inputId);
    if (input && input._flatpickr) {
        const selectedDate = input._flatpickr.selectedDates[0];
        if (selectedDate) {
            const y = selectedDate.getFullYear();
            const m = String(selectedDate.getMonth() + 1).padStart(2, '0');
            const d = String(selectedDate.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        }
    }
    return input ? input.value : '';
}

// BENEFIT FUNCTIONS
function prepareAddBenefit() {
    document.getElementById('benefitForm').reset();
    document.getElementById('benefitID').value = '';
    document.getElementById('benefitModalLabel').textContent = 'Enroll in Benefit';
    document.getElementById('isActiveBenefit').checked = true;
    document.getElementById('coverageLevel').value = 'individual';
    document.getElementById('contributionFrequency').value = 'monthly';
}

function editBenefit(id) {
    fetch(`<?= $base ?>php/scripts/global/admin/benefits_api.php?action=get_benefit_enrollment&id=${id}`)
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                const ben = d.data;
                console.log('Benefit Data:', ben);
                document.getElementById('benefitID').value = ben.benefitID;
                document.getElementById('benefitTypeID').value = ben.benefitTypeID || '';
                document.getElementById('coverageLevel').value = ben.coverageLevel || 'individual';
                document.getElementById('policyNumber').value = ben.policyNumber || '';
                document.getElementById('memberNumber').value = ben.memberNumber || '';
                document.getElementById('employerContribution').value = ben.employerContribution || 0;
                document.getElementById('employeeContribution').value = ben.employeeContribution || 0;
                document.getElementById('totalPremium').value = ben.totalPremium || 0;
                document.getElementById('contributionFrequency').value = ben.contributionFrequency || 'monthly';
                document.getElementById('dependentsCovered').value = ben.dependentsCovered || 0;
                document.getElementById('providerName').value = ben.providerName || '';
                document.getElementById('providerContact').value = ben.providerContact || '';
                document.getElementById('isActiveBenefit').checked = (ben.isActive === 'Y');
                document.getElementById('benefitNotes').value = ben.notes || '';

                // Set dates
                if (ben.enrollmentDate && document.getElementById('enrollmentDate')._flatpickr) {
                    document.getElementById('enrollmentDate')._flatpickr.setDate(ben.enrollmentDate, true);
                }
                if (ben.effectiveDate && document.getElementById('benefitEffectiveDate')._flatpickr) {
                    document.getElementById('benefitEffectiveDate')._flatpickr.setDate(ben.effectiveDate, true);
                }
                if (ben.endDate && document.getElementById('benefitEndDate')._flatpickr) {
                    document.getElementById('benefitEndDate')._flatpickr.setDate(ben.endDate, true);
                }

                document.getElementById('benefitModalLabel').textContent = 'Edit Benefit Enrollment';
            } else {
                showToast(d.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while loading benefit', 'danger');
        });
}

function saveBenefitEnrollment(event) {
    event.preventDefault();

    // Extract dates
    document.getElementById('enrollmentDate').value = extractBenefitDateValue('enrollmentDate');
    document.getElementById('benefitEffectiveDate').value = extractBenefitDateValue('benefitEffectiveDate');
    document.getElementById('benefitEndDate').value = extractBenefitDateValue('benefitEndDate');

    const formData = new FormData(event.target);
    formData.append('action', 'save_benefit_enrollment');

    // Rename effectiveDate field
    formData.set('effectiveDate', formData.get('effectiveDate') || '');

    const submitBtn = event.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i> Saving...';

    fetch('<?= $base ?>php/scripts/global/admin/benefits_api.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(d => {
        console.log('API Response:', d);
        if (d.success) {
            showToast(d.message, 'success');
            setTimeout(() => {
                window.location.href = '?s=<?= $s ?>&p=<?= $p ?>&uid=<?= $employeeID ?>&tab=benefits';
            }, 1500);
        } else {
            console.error('API Error:', d);
            if (d.error_details) {
                console.error('Error Details:', d.error_details);
            }
            showToast(d.message || 'An error occurred while saving', 'danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="ri-save-line me-1"></i> Save Enrollment';
        }
    })
    .catch(error => {
        console.error('Catch Error:', error);
        showToast('An error occurred while saving', 'danger');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="ri-save-line me-1"></i> Save Enrollment';
    });
}

function deleteBenefit(id) {
    if (!confirm('Are you sure you want to delete this benefit enrollment?')) {
        return;
    }

    fetch(`<?= $base ?>php/scripts/global/admin/benefits_api.php?action=delete_benefit_enrollment&id=${id}`, {
        method: 'POST'
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            showToast(d.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(d.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while deleting', 'danger');
    });
}

// TOAST FUNCTION
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' : type === 'danger' ? 'bg-danger' : 'bg-info';

    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
    toast.show();
    toastElement.addEventListener('hidden.bs.toast', function() { toastElement.remove(); });
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

