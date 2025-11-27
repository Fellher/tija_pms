<?php
/**
 * Employee Expense Submission Interface
 * User-friendly interface for employees to submit expenses
 * @package    Tija CRM
 * @subpackage Expense Management
 */

// Get expense categories, types, and statuses
$expenseCategories = Expense::expense_categories(array('orgDataID' => $orgDataID, 'entityID' => $entityID, 'isActive' => 'Y'), false, $DBConn);
$expenseTypes = Expense::expense_types(array('orgDataID' => $orgDataID, 'entityID' => $entityID, 'isActive' => 'Y'), false, $DBConn);
$expenseStatuses = Expense::expense_status(array('orgDataID' => $orgDataID, 'entityID' => $entityID, 'isActive' => 'Y'), false, $DBConn);

// Get employee's projects and clients
$employeeProjects = Projects::projects(array('projectOwnerID' => $userDetails->ID, 'orgDataID' => $orgDataID, 'entityID' => $entityID, 'Suspended' => 'N'), false, $DBConn);
$employeeClients = Client::clients(array('orgDataID' => $orgDataID, 'entityID' => $entityID, 'Suspended' => 'N'), false, $DBConn);

// Get employee's recent expenses
$recentExpenses = Expense::expenses_full(array('employeeID' => $userDetails->ID, 'orgDataID' => $orgDataID, 'entityID' => $entityID, 'Suspended' => 'N'), false, $DBConn);
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">Expense Submission</h4>
                            <p class="text-muted mb-0">Submit and track your business expenses</p>
                        </div>
                        <div class="text-end">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#submitExpenseModal">
                                <i class="ri-add-line me-2"></i>Submit New Expense
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <div class="avatar-sm bg-primary-subtle rounded mx-auto mb-3">
                        <span class="avatar-title bg-primary text-white rounded">
                            <i class="ri-file-list-3-line"></i>
                        </span>
                    </div>
                    <h5 class="mb-1"><?= $recentExpenses ? count($recentExpenses) : 0 ?></h5>
                    <p class="text-muted mb-0">Total Expenses</p>
                    <small class="text-primary">This Year</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <div class="avatar-sm bg-success-subtle rounded mx-auto mb-3">
                        <span class="avatar-title bg-success text-white rounded">
                            <i class="ri-check-line"></i>
                        </span>
                    </div>
                    <h5 class="mb-1">
                        <?php 
                        $approvedCount = 0;
                        if ($recentExpenses && is_array($recentExpenses)) {
                            foreach ($recentExpenses as $expense) {
                                if ($expense->expenseStatusID == 4) $approvedCount++;
                            }
                        }
                        echo $approvedCount;
                        ?>
                    </h5>
                    <p class="text-muted mb-0">Approved</p>
                    <small class="text-success">This Year</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <div class="avatar-sm bg-info-subtle rounded mx-auto mb-3">
                        <span class="avatar-title bg-info text-white rounded">
                            <i class="ri-money-dollar-circle-line"></i>
                        </span>
                    </div>
                    <h5 class="mb-1">
                        <?php 
                        $totalAmount = 0;
                        if ($recentExpenses && is_array($recentExpenses)) {
                            foreach ($recentExpenses as $expense) {
                                $totalAmount += $expense->amount;
                            }
                        }
                        echo 'KES ' . number_format($totalAmount, 2);
                        ?>
                    </h5>
                    <p class="text-muted mb-0">Total Amount</p>
                    <small class="text-info">This Year</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <div class="avatar-sm bg-warning-subtle rounded mx-auto mb-3">
                        <span class="avatar-title bg-warning text-white rounded">
                            <i class="ri-time-line"></i>
                        </span>
                    </div>
                    <h5 class="mb-1">
                        <?php 
                        $pendingCount = 0;
                        if ($recentExpenses && is_array($recentExpenses)) {
                            foreach ($recentExpenses as $expense) {
                                if (in_array($expense->expenseStatusID, [2, 3])) $pendingCount++;
                            }
                        }
                        echo $pendingCount;
                        ?>
                    </h5>
                    <p class="text-muted mb-0">Pending</p>
                    <small class="text-warning">Awaiting Approval</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Expenses -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-time-line me-2"></i>Your Recent Expenses</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Expense #</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recentExpenses && is_array($recentExpenses)): ?>
                                    <?php foreach (array_slice($recentExpenses, 0, 10) as $expense): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($expense->expenseNumber) ?></td>
                                            <td><?= htmlspecialchars($expense->expenseTypeName) ?></td>
                                            <td><?= htmlspecialchars($expense->expenseCategoryName) ?></td>
                                            <td class="text-end">KES <?= number_format($expense->amount, 2) ?></td>
                                            <td><?= date('M d, Y', strtotime($expense->expenseDate)) ?></td>
                                            <td>
                                                <span class="badge" style="background-color: <?= $expense->expenseStatusColor ?>">
                                                    <?= htmlspecialchars($expense->expenseStatusName) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="viewExpense(<?= $expense->expenseID ?>)">
                                                    <i class="ri-eye-line"></i>
                                                </button>
                                                <?php if ($expense->expenseStatusID == 1): ?>
                                                    <button class="btn btn-sm btn-outline-warning" onclick="editExpense(<?= $expense->expenseID ?>)">
                                                        <i class="ri-edit-line"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No expenses submitted yet</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Submit Expense Modal -->
<div class="modal fade" id="submitExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submit New Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="expenseSubmissionForm" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Expense Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="expenseTypeID" required>
                                <option value="">Select Type</option>
                                <?php if ($expenseTypes && is_array($expenseTypes)): ?>
                                    <?php foreach ($expenseTypes as $type): ?>
                                        <option value="<?= $type->expenseTypeID ?>" 
                                                data-requires-approval="<?= $type->requiresApproval ?>"
                                                data-approval-limit="<?= $type->approvalLimit ?>"
                                                data-requires-receipt="<?= $type->requiresReceipt ?>"
                                                data-approval-level="<?= $type->approvalLevel ?>"
                                                data-max-amount="<?= $type->maxAmount ?>"
                                                data-min-amount="<?= $type->minAmount ?>"
                                                data-auto-approve-limit="<?= $type->autoApproveLimit ?>"
                                                data-requires-justification="<?= $type->requiresJustification ?>"
                                                data-requires-project-link="<?= $type->requiresProjectLink ?>"
                                                data-requires-client-link="<?= $type->requiresClientLink ?>"
                                                data-requires-sales-case-link="<?= $type->requiresSalesCaseLink ?>"
                                                data-is-reimbursable="<?= $type->isReimbursable ?>"
                                                data-is-petty-cash="<?= $type->isPettyCash ?>"
                                                data-is-taxable="<?= $type->isTaxable ?>"
                                                data-tax-rate="<?= $type->taxRate ?>"
                                                data-reimbursement-rate="<?= $type->reimbursementRate ?>"
                                                data-default-currency="<?= $type->defaultCurrency ?>">
                                            <?= htmlspecialchars($type->typeName) ?> (<?= $type->typeCode ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" name="expenseCategoryID" required>
                                <option value="">Select Category</option>
                                <?php if ($expenseCategories && is_array($expenseCategories)): ?>
                                    <?php foreach ($expenseCategories as $category): ?>
                                        <option value="<?= $category->expenseCategoryID ?>" 
                                                data-requires-receipt="<?= $category->requiresReceipt ?>"
                                                data-max-amount="<?= $category->maxAmount ?>">
                                            <?= htmlspecialchars($category->categoryName) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Expense Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="expenseDate" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Amount (KES) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="amount" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Project (Optional)</label>
                            <select class="form-select" name="projectID">
                                <option value="">No Project</option>
                                <?php if ($employeeProjects && is_array($employeeProjects)): ?>
                                    <?php foreach ($employeeProjects as $project): ?>
                                        <option value="<?= $project->projectID ?>"><?= htmlspecialchars($project->projectName) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Client (Optional)</label>
                            <select class="form-select" name="clientID">
                                <option value="">No Client</option>
                                <?php if ($employeeClients && is_array($employeeClients)): ?>
                                    <?php foreach ($employeeClients as $client): ?>
                                        <option value="<?= $client->clientID ?>"><?= htmlspecialchars($client->clientName) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="description" rows="3" required placeholder="Describe the expense in detail..."></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Receipt/Attachment</label>
                            <input type="file" class="form-control" name="receipt" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            <small class="form-text text-muted">Upload receipt or supporting document (PDF, JPG, PNG, DOC)</small>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="urgent" id="urgentCheck">
                                <label class="form-check-label" for="urgentCheck">
                                    Mark as Urgent
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitExpense()">Submit Expense</button>
            </div>
        </div>
    </div>
</div>

<!-- Expense Details Modal -->
<div class="modal fade" id="expenseDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Expense Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="expenseDetailsContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set default expense date to today
    document.querySelector('input[name="expenseDate"]').value = new Date().toISOString().split('T')[0];
    
    // Handle expense type change
    document.querySelector('select[name="expenseTypeID"]').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const requiresApproval = selectedOption.getAttribute('data-requires-approval');
        const approvalLimit = selectedOption.getAttribute('data-approval-limit');
        
        // Show approval information
        if (requiresApproval === 'Y') {
            console.log('This expense type requires approval');
            if (approvalLimit) {
                console.log('Approval limit: KES ' + approvalLimit);
            }
        }
    });
    
    // Handle category change
    document.querySelector('select[name="expenseCategoryID"]').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const requiresReceipt = selectedOption.getAttribute('data-requires-receipt');
        const maxAmount = selectedOption.getAttribute('data-max-amount');
        
        // Show receipt requirement
        if (requiresReceipt === 'Y') {
            console.log('Receipt required for this category');
        }
        
        // Show max amount limit
        if (maxAmount) {
            console.log('Maximum amount: KES ' + maxAmount);
        }
    });
});

function submitExpense() {
    const form = document.getElementById('expenseSubmissionForm');
    const formData = new FormData(form);
    
    // Add additional data
    formData.append('employeeID', '<?= $userDetails->ID ?>');
    formData.append('orgDataID', '<?= $orgDataID ?>');
    formData.append('entityID', '<?= $entityID ?>');
    formData.append('action', 'submit_expense');
    
    // Show loading
    const submitBtn = document.querySelector('#submitExpenseModal .btn-primary');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-2"></i>Submitting...';
    submitBtn.disabled = true;
    
    fetch('includes/scripts/expenses/expense_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Expense submitted successfully!', 'success');
            document.getElementById('expenseSubmissionForm').reset();
            bootstrap.Modal.getInstance(document.getElementById('submitExpenseModal')).hide();
            // Reload page to show updated data
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error submitting expense', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function viewExpense(expenseID) {
    fetch('includes/scripts/expenses/expense_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'get_expense_details',
            expenseID: expenseID
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('expenseDetailsContent').innerHTML = data.html;
            new bootstrap.Modal(document.getElementById('expenseDetailsModal')).show();
        } else {
            showNotification('Error loading expense details', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error loading expense details', 'error');
    });
}

function editExpense(expenseID) {
    // Implementation for editing expense
    console.log('Edit expense:', expenseID);
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}
</script>
