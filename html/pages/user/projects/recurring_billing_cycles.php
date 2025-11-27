<?php
/**
 * Recurring Project Billing Cycles Management Page
 *
 * Displays and manages billing cycles for recurring projects
 *
 * @package    TIJA_PMS
 * @subpackage Recurring Projects
 */

session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';

// Get project ID from URL
$projectID = isset($_GET['pid']) ? intval($_GET['pid']) : 0;

if (!$projectID) {
    Alert::danger("Project ID is required");
    header("Location: {$base}html/?s=user&ss=projects&p=home");
    exit;
}

// Get project details
$project = Projects::projects_mini(['projectID' => $projectID], true, $DBConn);

if (!$project || $project->isRecurring != 'Y') {
    Alert::danger("This is not a recurring project");
    header("Location: {$base}html/?s=user&ss=projects&p=project&pid={$projectID}");
    exit;
}

// Get billing cycles
$billingCycles = Projects::get_billing_cycles(['projectID' => $projectID, 'Suspended' => 'N'], false, $DBConn);

// Get project client
$client = Clients::clients(['clientID' => $project->clientID], true, $DBConn);

$pageTitle = "Billing Cycles - {$project->projectName}";
include 'html/includes/header.php';
?>

<div class="main-content">
    <div class="page-header">
        <div class="page-header-content">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?>html/?s=user&ss=projects&p=home">Projects</a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?>html/?s=user&ss=projects&p=project&pid=<?= $projectID ?>"><?= htmlspecialchars($project->projectName) ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Billing Cycles</li>
                </ol>
            </nav>
            <div class="page-header-title">
                <h2><i class="ri-repeat-line me-2"></i>Billing Cycles</h2>
                <p class="text-muted">Manage recurring billing cycles for <?= htmlspecialchars($project->projectName) ?></p>
            </div>
        </div>
    </div>

    <div class="page-content">
        <!-- Recurring Projects Notifications Widget -->
        <?php include 'html/includes/projects/recurring_notifications_widget.php'; ?>
        <!-- Project Info Card -->
        <div class="card custom-card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5><?= htmlspecialchars($project->projectName) ?></h5>
                        <p class="text-muted mb-2">
                            <strong>Client:</strong> <?= htmlspecialchars($client->clientName ?? 'N/A') ?><br>
                            <strong>Recurrence:</strong> <?= ucfirst($project->recurrenceType ?? 'N/A') ?><br>
                            <strong>Billing Amount:</strong> KES <?= number_format($project->billingCycleAmount ?? 0, 2) ?>
                        </p>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="button" class="btn btn-success me-2" onclick="generateNextCycles(<?= $project->projectID ?>, '<?= htmlspecialchars($project->projectName, ENT_QUOTES) ?>')">
                            <i class="ri-play-line me-1"></i>Generate Next Cycles
                        </button>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCycleModal">
                            <i class="ri-add-line me-1"></i>Create New Cycle
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Billing Cycles Table -->
        <div class="card custom-card">
            <div class="card-header">
                <h5 class="mb-0">Billing Cycles</h5>
            </div>
            <div class="card-body">
                <?php if ($billingCycles && is_array($billingCycles) && count($billingCycles) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Cycle #</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Billing Date</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Hours Logged</th>
                                    <th>Invoice</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($billingCycles as $cycle): ?>
                                    <tr>
                                        <td><?= $cycle->cycleNumber ?></td>
                                        <td><?= date('d M Y', strtotime($cycle->cycleStartDate)) ?></td>
                                        <td><?= date('d M Y', strtotime($cycle->cycleEndDate)) ?></td>
                                        <td><?= date('d M Y', strtotime($cycle->billingDate)) ?></td>
                                        <td><?= date('d M Y', strtotime($cycle->dueDate)) ?></td>
                                        <td>
                                            <?php
                                            $statusColors = [
                                                'upcoming' => 'secondary',
                                                'active' => 'primary',
                                                'billing_due' => 'warning',
                                                'invoiced' => 'info',
                                                'paid' => 'success',
                                                'overdue' => 'danger',
                                                'cancelled' => 'dark'
                                            ];
                                            $statusColor = $statusColors[$cycle->status] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?= $statusColor ?>"><?= ucfirst(str_replace('_', ' ', $cycle->status)) ?></span>
                                        </td>
                                        <td>KES <?= number_format($cycle->amount, 2) ?></td>
                                        <td><?= number_format($cycle->hoursLogged, 2) ?> hrs</td>
                                        <td>
                                            <?php if ($cycle->invoiceID): ?>
                                                <a href="<?= $base ?>html/?s=user&ss=invoices&p=invoice&iid=<?= $cycle->invoiceID ?>" class="btn btn-sm btn-info">
                                                    <i class="ri-file-list-3-line"></i> View Invoice
                                                </a>
                                            <?php elseif ($cycle->invoiceDraftID): ?>
                                                <a href="<?= $base ?>html/?s=user&ss=projects&p=invoice_drafts&draftid=<?= $cycle->invoiceDraftID ?>" class="btn btn-sm btn-warning">
                                                    <i class="ri-draft-line"></i> Draft
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" onclick="viewCycleDetails(<?= $cycle->billingCycleID ?>)">
                                                    <i class="ri-eye-line"></i>
                                                </button>
                                                <?php if ($cycle->status == 'upcoming' || $cycle->status == 'active'): ?>
                                                    <button type="button" class="btn btn-outline-secondary" onclick="editCycle(<?= $cycle->billingCycleID ?>)">
                                                        <i class="ri-edit-line"></i>
                                                    </button>
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
                        <p class="text-muted mt-3">No billing cycles found. Create your first cycle to get started.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Create Cycle Modal -->
<div class="modal fade" id="createCycleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Billing Cycle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createCycleForm" method="POST" action="<?= $base ?>php/scripts/recurring_projects/manage_billing_cycle.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="projectID" value="<?= $projectID ?>">

                    <div class="mb-3">
                        <label class="form-label">Cycle Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="cycleStartDate" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cycle End Date <span class="text-danger">*</span></label>
                        <input type="date" name="cycleEndDate" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Billing Date <span class="text-danger">*</span></label>
                        <input type="date" name="billingDate" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Due Date <span class="text-danger">*</span></label>
                        <input type="date" name="dueDate" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">KES</span>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0" value="<?= $project->billingCycleAmount ?? 0 ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Cycle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewCycleDetails(cycleID) {
    // TODO: Implement cycle details view
    if (typeof showToast === 'function') {
        showToast('Cycle details view - Coming soon', 'info');
    } else {
        alert('Cycle details view - Coming soon');
    }
}

function editCycle(cycleID) {
    // TODO: Implement cycle edit
    if (typeof showToast === 'function') {
        showToast('Cycle edit - Coming soon', 'info');
    } else {
        alert('Cycle edit - Coming soon');
    }
}

function generateNextCycles(projectID, projectName) {
    if (!confirm(`Generate next billing cycles for "${projectName}"?`)) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'generate_next_cycles');
    formData.append('projectID', projectID);

    // Show loading
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i>Generating...';

    fetch('<?= $base ?>php/scripts/recurring_projects/manage_billing_cycle.php', {
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
                showToast('Billing cycles generated successfully!', 'success');
            } else {
                alert('Billing cycles generated successfully!');
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
            showToast('An error occurred while generating cycles', 'error');
        } else {
            alert('An error occurred while generating cycles');
        }
    });
}
</script>

<?php include 'html/includes/footer.php'; ?>

