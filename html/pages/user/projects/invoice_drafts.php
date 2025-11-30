<?php
/**
 * Invoice Drafts Review Page for Recurring Projects
 *
 * Lists and manages invoice drafts for recurring project billing cycles
 *
 * @package    TIJA_PMS
 * @subpackage Recurring Projects
 */

session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';

// Get filters
$projectID = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
$status = isset($_GET['status']) ? Utility::clean_string($_GET['status']) : 'draft';

// Get invoice drafts (invoices with status 'draft' linked to billing cycles)
$whereArr = ['invoiceStatusID' => 1]; // Assuming 1 is draft status
if ($projectID) {
    $whereArr['projectID'] = $projectID;
}

$invoices = Invoice::invoices_full($whereArr, false, $DBConn);

// Filter for recurring project invoices
$draftInvoices = [];
if ($invoices && is_array($invoices)) {
    foreach ($invoices as $invoice) {
        if ($invoice->projectID) {
            $project = Projects::projects_mini(['projectID' => $invoice->projectID], true, $DBConn);
            if ($project && $project->isRecurring == 'Y') {
                // Get billing cycle for this invoice
                $cycle = Projects::get_billing_cycles(['invoiceDraftID' => $invoice->invoiceID], true, $DBConn);
                if ($cycle) {
                    $invoice->billingCycle = $cycle;
                    $draftInvoices[] = $invoice;
                }
            }
        }
    }
}

// Also get billing cycles that need invoice drafts created
$cyclesNeedingDrafts = [];
$cyclesWhereArr = ['status' => 'billing_due', 'Suspended' => 'N'];
if ($projectID) {
    $cyclesWhereArr['projectID'] = $projectID;
}

$billingCycles = Projects::get_billing_cycles($cyclesWhereArr, false, $DBConn);
if ($billingCycles && is_array($billingCycles)) {
    foreach ($billingCycles as $cycle) {
        // Only include cycles without invoice drafts
        if (!$cycle->invoiceDraftID && !$cycle->invoiceID) {
            $project = Projects::projects_mini(['projectID' => $cycle->projectID], true, $DBConn);
            if ($project) {
                $client = Clients::clients(['clientID' => $project->clientID], true, $DBConn);

                // Create a pseudo-invoice object for display
                $pseudoInvoice = (object)[
                    'invoiceID' => null,
                    'invoiceNumber' => 'Pending',
                    'projectID' => $cycle->projectID,
                    'projectName' => $project->projectName,
                    'clientID' => $project->clientID,
                    'clientName' => $client->clientName ?? 'N/A',
                    'totalAmount' => $cycle->amount,
                    'dueDate' => $cycle->dueDate,
                    'invoiceDate' => $cycle->billingDate,
                    'billingCycle' => $cycle,
                    'needsDraft' => true
                ];
                $cyclesNeedingDrafts[] = $pseudoInvoice;
            }
        }
    }
}

// Merge both lists
$allDraftItems = array_merge($draftInvoices, $cyclesNeedingDrafts);

$pageTitle = "Invoice Drafts - Recurring Projects";
// Header is automatically included in index.php
?>

<div class="main-content">
    <div class="page-header">
        <div class="page-header-content">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?>html/?s=user&ss=projects&p=home">Projects</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Invoice Drafts</li>
                </ol>
            </nav>
            <div class="page-header-title">
                <h2><i class="ri-draft-line me-2"></i>Invoice Drafts</h2>
                <p class="text-muted">Review and approve invoice drafts for recurring projects</p>
            </div>
        </div>
    </div>

    <div class="page-content">
        <!-- Recurring Projects Notifications Widget -->
        <?php include 'html/includes/projects/recurring_notifications_widget.php'; ?>
        <!-- Filters -->
        <div class="card custom-card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Project</label>
                        <select name="pid" class="form-select">
                            <option value="">All Projects</option>
                            <?php
                            $recurringProjects = Projects::projects_mini(['isRecurring' => 'Y', 'Suspended' => 'N'], false, $DBConn);
                            if ($recurringProjects && is_array($recurringProjects)):
                                foreach ($recurringProjects as $proj):
                            ?>
                                <option value="<?= $proj->projectID ?>" <?= $projectID == $proj->projectID ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($proj->projectName) ?>
                                </option>
                            <?php
                                endforeach;
                            endif;
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft" <?= $status == 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="all">All</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-search-line me-1"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Invoice Drafts Table -->
        <div class="card custom-card">
            <div class="card-header">
                <h5 class="mb-0">Invoice Drafts</h5>
            </div>
            <div class="card-body">
                <?php if ($allDraftItems && count($allDraftItems) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Project</th>
                                    <th>Client</th>
                                    <th>Billing Cycle</th>
                                    <th>Cycle Period</th>
                                    <th>Amount</th>
                                    <th>Due Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allDraftItems as $invoice): ?>
                                    <tr>
                                        <td>
                                            <?php if (isset($invoice->needsDraft) && $invoice->needsDraft): ?>
                                                <span class="badge bg-warning">Needs Draft</span>
                                            <?php else: ?>
                                                <?= htmlspecialchars($invoice->invoiceNumber) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (isset($invoice->projectID)): ?>
                                                <a href="<?= $base ?>html/?s=user&ss=projects&p=project&pid=<?= $invoice->projectID ?>">
                                                    <?= htmlspecialchars($invoice->projectName ?? 'N/A') ?>
                                                </a>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($invoice->clientName ?? 'N/A') ?></td>
                                        <td>
                                            <?php if (isset($invoice->billingCycle) && $invoice->billingCycle): ?>
                                                Cycle #<?= $invoice->billingCycle->cycleNumber ?>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (isset($invoice->billingCycle) && $invoice->billingCycle): ?>
                                                <?= date('d M Y', strtotime($invoice->billingCycle->cycleStartDate)) ?> -
                                                <?= date('d M Y', strtotime($invoice->billingCycle->cycleEndDate)) ?>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td>KES <?= number_format($invoice->totalAmount ?? 0, 2) ?></td>
                                        <td>
                                            <?php if (isset($invoice->dueDate)): ?>
                                                <?= date('d M Y', strtotime($invoice->dueDate)) ?>
                                            <?php else: ?>
                                                <?= date('d M Y', strtotime($invoice->billingCycle->dueDate ?? 'now')) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <?php if (isset($invoice->needsDraft) && $invoice->needsDraft && isset($invoice->billingCycle)): ?>
                                                    <!-- Cycle needs draft created -->
                                                    <button type="button" class="btn btn-outline-success" onclick="createInvoiceDraft(<?= $invoice->billingCycle->billingCycleID ?>, <?= $invoice->projectID ?>, '<?= htmlspecialchars($invoice->projectName ?? '', ENT_QUOTES) ?>', <?= $invoice->billingCycle->cycleNumber ?>)">
                                                        <i class="ri-file-add-line"></i> Create Draft
                                                    </button>
                                                <?php elseif ($invoice->invoiceID): ?>
                                                    <!-- Draft exists -->
                                                    <button type="button" class="btn btn-outline-primary" onclick="previewInvoice(<?= $invoice->invoiceID ?>)">
                                                        <i class="ri-eye-line"></i> Preview
                                                    </button>
                                                    <button type="button" class="btn btn-outline-success" onclick="approveInvoice(<?= $invoice->invoiceID ?>)">
                                                        <i class="ri-check-line"></i> Approve
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger" onclick="rejectInvoice(<?= $invoice->invoiceID ?>)">
                                                        <i class="ri-close-line"></i> Reject
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="ri-inbox-line fs-48 text-muted"></i>
                        <p class="text-muted mt-3">No invoice drafts found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function previewInvoice(invoiceID) {
    window.open('<?= $base ?>html/?s=user&ss=invoices&p=invoice&iid=' + invoiceID, '_blank');
}

function createInvoiceDraft(billingCycleID, projectID, projectName, cycleNumber) {
    if (!confirm(`Create invoice draft for "${projectName}" - Cycle #${cycleNumber}?`)) {
        return;
    }

    const formData = new FormData();
    formData.append('billingCycleID', billingCycleID);

    // Show loading
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i>Creating...';

    fetch('<?= $base ?>php/scripts/recurring_projects/create_invoice_draft_manual.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalHTML;

        if (data.success) {
            if (typeof showToast === 'function') {
                showToast(`Invoice draft created successfully! Invoice Number: ${data.data.invoiceNumber}, Amount: KES ${data.data.amount.toLocaleString()}`, 'success');
            } else {
                alert(`Invoice draft created successfully!\nInvoice Number: ${data.data.invoiceNumber}\nAmount: KES ${data.data.amount.toLocaleString()}`);
            }
            setTimeout(() => location.reload(), 1500);
        } else {
            if (typeof showToast === 'function') {
                showToast('Error: ' + data.message, 'error');
            } else {
                alert('Error: ' + data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        btn.disabled = false;
        btn.innerHTML = originalHTML;
        if (typeof showToast === 'function') {
            showToast('An error occurred while creating invoice draft', 'error');
        } else {
            alert('An error occurred while creating invoice draft');
        }
    });
}

function approveInvoice(invoiceID) {
    if (confirm('Are you sure you want to approve and finalize this invoice draft?')) {
        // TODO: Implement approve action
        if (typeof showToast === 'function') {
            showToast('Invoice approval - Coming soon', 'info');
        } else {
            alert('Invoice approval - Coming soon');
        }
    }
}

function rejectInvoice(invoiceID) {
    if (confirm('Are you sure you want to reject this invoice draft? This will delete the draft.')) {
        // TODO: Implement reject action
        if (typeof showToast === 'function') {
            showToast('Invoice rejection - Coming soon', 'info');
        } else {
            alert('Invoice rejection - Coming soon');
        }
    }
}
</script>

<?php // Footer is automatically included in index.php ?>

