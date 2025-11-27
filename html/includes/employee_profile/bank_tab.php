<?php
/**
 * Bank Details Tab - Complete Implementation
 * Manages employee bank accounts for payroll processing
 */

// Get bank accounts using Employee class method
$bankAccounts = Employee::employee_bank_accounts(
    array('employeeID' => $employeeID, 'Suspended' => 'N'),
    false,
    $DBConn
);
// Convert to array if false
if (!$bankAccounts) {
    $bankAccounts = array();
}

// Calculate total allocation
$totalAllocation = 0;
if ($bankAccounts) {
    foreach ($bankAccounts as $account) {
        $totalAllocation += floatval($account->allocationPercentage ?? 0);
    }
}
?>

<div class="section-header">
    <h5 class="mb-0"><i class="ri-bank-line me-2"></i>Bank Account Information</h5>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">Salary Deposit Accounts</h6>
            <?php if ($canEdit): ?>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#bankAccountModal" onclick="prepareAddBankAccount()">
                <i class="ri-add-line me-1"></i> Add Bank Account
            </button>
            <?php endif; ?>
        </div>

        <?php if ($bankAccounts && count($bankAccounts) > 0): ?>
        <div class="row">
            <?php foreach ($bankAccounts as $account): ?>
            <div class="col-md-6 mb-3">
                <div class="info-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <h6 class="fw-bold mb-0"><?= htmlspecialchars($account->bankName) ?></h6>
                                <?php if ($account->isPrimary == 'Y'): ?>
                                <span class="badge bg-primary ms-2">Primary</span>
                                <?php endif; ?>
                                <?php if ($account->isVerified == 'Y'): ?>
                                <span class="badge bg-success-transparent ms-1"><i class="ri-shield-check-line me-1"></i>Verified</span>
                                <?php endif; ?>
                                <?php if ($account->isActive != 'Y'): ?>
                                <span class="badge bg-warning-transparent ms-1">Inactive</span>
                                <?php endif; ?>
                            </div>

                            <div class="bank-details small">
                                <div class="mb-1">
                                    <i class="ri-bank-card-line me-2 text-muted"></i>
                                    <strong>Account:</strong> <?= htmlspecialchars($account->accountNumber) ?>
                                </div>
                                <div class="mb-1">
                                    <i class="ri-user-line me-2 text-muted"></i>
                                    <strong>Name:</strong> <?= htmlspecialchars($account->accountName) ?>
                                </div>
                                <?php if (!empty($account->branchName)): ?>
                                <div class="mb-1">
                                    <i class="ri-map-pin-line me-2 text-muted"></i>
                                    <strong>Branch:</strong> <?= htmlspecialchars($account->branchName) ?>
                                </div>
                                <?php endif; ?>
                                <div class="mb-1">
                                    <i class="ri-percent-line me-2 text-muted"></i>
                                    <strong>Allocation:</strong>
                                    <span class="badge bg-info-transparent"><?= number_format($account->allocationPercentage, 2) ?>%</span>
                                </div>
                            </div>
                        </div>

                        <?php if ($canEdit): ?>
                        <div class="btn-group btn-group-sm ms-2">
                            <button class="btn btn-sm btn-icon btn-primary-light"
                                    data-bs-toggle="modal"
                                    data-bs-target="#bankAccountModal"
                                    onclick="editBankAccount(<?= $account->bankAccountID ?>)"
                                    title="Edit">
                                <i class="ri-edit-line"></i>
                            </button>
                            <?php if ($account->isVerified != 'Y' && $isAdmin): ?>
                            <button class="btn btn-sm btn-icon btn-success-light"
                                    onclick="verifyBankAccount(<?= $account->bankAccountID ?>)"
                                    title="Verify">
                                <i class="ri-shield-check-line"></i>
                            </button>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-icon btn-danger-light"
                                    onclick="deleteBankAccount(<?= $account->bankAccountID ?>)"
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

        <!-- Allocation Summary -->
        <?php if (count($bankAccounts) > 1): ?>
        <div class="alert <?= $totalAllocation == 100 ? 'alert-success' : 'alert-warning' ?> mt-3">
            <i class="ri-information-line me-2"></i>
            <strong>Total Allocation:</strong> <?= number_format($totalAllocation, 2) ?>%
            <?php if ($totalAllocation != 100): ?>
            <span class="ms-2">(Should total 100%)</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="alert alert-info">
            <i class="ri-information-line me-2"></i>
            No bank accounts on record. Click "Add Bank Account" to add salary deposit account details.
        </div>
        <?php endif; ?>

        <div class="alert alert-warning mt-3">
            <i class="ri-shield-line me-2"></i>
            <strong>Security Note:</strong> Bank account information is sensitive and encrypted. Only authorized personnel can view and modify this information.
        </div>
    </div>
</div>

<!-- Include Modal -->
<?php include __DIR__ . '/modals/bank_account_modal.php'; ?>

<script>
// Initialize date pickers
document.addEventListener('DOMContentLoaded', function() {
    initializeBankDatePickers();

    const bankModal = document.getElementById('bankAccountModal');
    if (bankModal) {
        bankModal.addEventListener('shown.bs.modal', initializeBankDatePickers);
    }
});

function initializeBankDatePickers() {
    if (typeof flatpickr !== 'undefined') {
        document.querySelectorAll('.bank-datepicker').forEach(input => {
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
function extractBankDateValue(inputId) {
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

// BANK ACCOUNT FUNCTIONS
function prepareAddBankAccount() {
    document.getElementById('bankAccountForm').reset();
    document.getElementById('bankAccountID').value = '';
    document.getElementById('bankAccountModalLabel').textContent = 'Add Bank Account';
    document.getElementById('currency').value = 'KES';
    document.getElementById('allocationPercentage').value = 100;
    document.getElementById('isActiveAccount').checked = true;
}

function editBankAccount(id) {
    fetch(`<?= $base ?>php/scripts/global/admin/bank_accounts_api.php?action=get_bank_account&id=${id}`)
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                const acc = d.data;
                document.getElementById('bankAccountID').value = acc.bankAccountID;
                document.getElementById('bankName').value = acc.bankName || '';
                document.getElementById('bankCode').value = acc.bankCode || '';
                document.getElementById('branchName').value = acc.branchName || '';
                document.getElementById('branchCode').value = acc.branchCode || '';
                document.getElementById('accountNumber').value = acc.accountNumber || '';
                document.getElementById('accountName').value = acc.accountName || '';
                document.getElementById('accountType').value = acc.accountType || 'salary';
                document.getElementById('currency').value = acc.currency || 'KES';
                document.getElementById('allocationPercentage').value = acc.allocationPercentage || 100;
                document.getElementById('isPrimaryAccount').checked = (acc.isPrimary === 'Y');
                document.getElementById('swiftCode').value = acc.swiftCode || '';
                document.getElementById('iban').value = acc.iban || '';
                document.getElementById('isActiveAccount').checked = (acc.isActive === 'Y');
                document.getElementById('bankNotes').value = acc.notes || '';

                if (acc.effectiveDate && document.getElementById('effectiveDate')._flatpickr) {
                    document.getElementById('effectiveDate')._flatpickr.setDate(acc.effectiveDate, true);
                }

                document.getElementById('bankAccountModalLabel').textContent = 'Edit Bank Account';
            } else {
                showToast(d.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while loading bank account', 'danger');
        });
}

function saveBankAccount(event) {
    event.preventDefault();

    // Extract dates
    document.getElementById('effectiveDate').value = extractBankDateValue('effectiveDate');

    const formData = new FormData(event.target);
    formData.append('action', 'save_bank_account');

    const submitBtn = event.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i> Saving...';

    fetch('<?= $base ?>php/scripts/global/admin/bank_accounts_api.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            showToast(d.message, 'success');
            setTimeout(() => {
                window.location.href = '?s=<?= $s ?>&p=<?= $p ?>&uid=<?= $employeeID ?>&tab=bank';
            }, 1500);
        } else {
            showToast(d.message, 'danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="ri-save-line me-1"></i> Save Bank Account';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while saving', 'danger');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="ri-save-line me-1"></i> Save Bank Account';
    });
}

function deleteBankAccount(id) {
    if (!confirm('Are you sure you want to delete this bank account?')) {
        return;
    }

    fetch(`<?= $base ?>php/scripts/global/admin/bank_accounts_api.php?action=delete_bank_account&id=${id}`, {
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

function verifyBankAccount(id) {
    if (!confirm('Mark this bank account as verified?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'verify_bank_account');
    formData.append('bankAccountID', id);

    fetch('<?= $base ?>php/scripts/global/admin/bank_accounts_api.php', {
        method: 'POST',
        body: formData
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
        showToast('An error occurred', 'danger');
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

