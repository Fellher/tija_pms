<?php
/**
 * Billing Cycles Display Component
 * Displays billing cycles for recurring projects with filtering and actions
 *
 * @package    TIJA_PMS
 * @subpackage Recurring Projects
 */

// Ensure $base is defined
if (!isset($base)) {
    $base = '../../../';
}

// Get billing cycles if not already loaded
if (!isset($billingCycles)) {
    if (isset($projectID)) {
        $billingCycles = Projects::get_billing_cycles(['projectID' => $projectID, 'Suspended' => 'N'], false, $DBConn);
    } else {
        $billingCycles = [];
    }
}

// Get project details if not already loaded
if (!isset($projectDetails)) {
    if (isset($projectID)) {
        $projectDetails = Projects::projects_mini(['projectID' => $projectID], true, $DBConn);
    }
}

// Check if project is recurring
$isRecurring = false;
if (isset($projectDetails)) {
    if (isset($projectDetails->isRecurring) && $projectDetails->isRecurring === 'Y') {
        $isRecurring = true;
    } elseif (isset($projectDetails->projectType) && $projectDetails->projectType === 'recurrent') {
        $isRecurring = true;
    }
}

// Only show if recurring project
if (!$isRecurring) {
    return;
}

// Group cycles by status
$cyclesByStatus = [
    'upcoming' => [],
    'active' => [],
    'billing_due' => [],
    'invoiced' => [],
    'paid' => [],
    'overdue' => [],
    'cancelled' => []
];

if ($billingCycles && is_array($billingCycles)) {
    foreach ($billingCycles as $cycle) {
        $status = $cycle->status ?? 'upcoming';
        if (isset($cyclesByStatus[$status])) {
            $cyclesByStatus[$status][] = $cycle;
        }
    }
}

// Status colors and icons
$statusConfig = [
    'upcoming' => ['color' => 'secondary', 'icon' => 'ri-time-line', 'label' => 'Upcoming'],
    'active' => ['color' => 'primary', 'icon' => 'ri-play-circle-line', 'label' => 'Active'],
    'billing_due' => ['color' => 'warning', 'icon' => 'ri-alarm-warning-line', 'label' => 'Billing Due'],
    'invoiced' => ['color' => 'info', 'icon' => 'ri-file-list-3-line', 'label' => 'Invoiced'],
    'paid' => ['color' => 'success', 'icon' => 'ri-checkbox-circle-line', 'label' => 'Paid'],
    'overdue' => ['color' => 'danger', 'icon' => 'ri-error-warning-line', 'label' => 'Overdue'],
    'cancelled' => ['color' => 'dark', 'icon' => 'ri-close-circle-line', 'label' => 'Cancelled']
];

// Calculate totals
$totalCycles = count($billingCycles ?? []);
$totalAmount = 0;
$totalHoursLogged = 0;
$activeCycles = 0;
$upcomingCycles = 0;

if ($billingCycles && is_array($billingCycles)) {
    foreach ($billingCycles as $cycle) {
        $totalAmount += floatval($cycle->amount ?? 0);
        $totalHoursLogged += floatval($cycle->hoursLogged ?? 0);
        if ($cycle->status === 'active') {
            $activeCycles++;
        } elseif ($cycle->status === 'upcoming') {
            $upcomingCycles++;
        }
    }
}
?>

<!-- Billing Cycles Widget -->
<div class="card custom-card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="ri-repeat-line me-2"></i>Billing Cycles
        </h5>
        <div>
            <a href="?s=user&ss=projects&p=recurring_billing_cycles&pid=<?= $projectID ?>" class="btn btn-sm btn-outline-primary">
                <i class="ri-external-link-line me-1"></i>View All
            </a>
            <?php if ($isRecurring): ?>
                <button type="button" class="btn btn-sm btn-primary" onclick="activateNextCycle(<?= $projectID ?>)">
                    <i class="ri-play-line me-1"></i>Activate Next Cycle
                </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <h3 class="text-primary mb-1"><?= $totalCycles ?></h3>
                        <p class="text-muted mb-0 small">Total Cycles</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <h3 class="text-success mb-1"><?= $activeCycles ?></h3>
                        <p class="text-muted mb-0 small">Active Cycles</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <h3 class="text-info mb-1">KES <?= number_format($totalAmount, 2) ?></h3>
                        <p class="text-muted mb-0 small">Total Amount</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <h3 class="text-warning mb-1"><?= number_format($totalHoursLogged, 2) ?></h3>
                        <p class="text-muted mb-0 small">Hours Logged</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cycles by Status Tabs -->
        <ul class="nav nav-tabs mb-3" role="tablist">
            <?php
            $firstTab = true;
            foreach ($statusConfig as $status => $config):
                $count = count($cyclesByStatus[$status]);
                if ($count > 0 || $status === 'active' || $status === 'upcoming'):
            ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $firstTab ? 'active' : '' ?>"
                            id="<?= $status ?>-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#<?= $status ?>-cycles"
                            type="button"
                            role="tab">
                        <i class="<?= $config['icon'] ?> me-1"></i>
                        <?= $config['label'] ?>
                        <?php if ($count > 0): ?>
                            <span class="badge bg-<?= $config['color'] ?> ms-1"><?= $count ?></span>
                        <?php endif; ?>
                    </button>
                </li>
            <?php
                    $firstTab = false;
                endif;
            endforeach;
            ?>
        </ul>

        <!-- Cycles Content -->
        <div class="tab-content">
            <?php
            $firstContent = true;
            foreach ($statusConfig as $status => $config):
                $cycles = $cyclesByStatus[$status];
                if (count($cycles) > 0 || $status === 'active' || $status === 'upcoming'):
            ?>
                <div class="tab-pane fade <?= $firstContent ? 'show active' : '' ?>"
                     id="<?= $status ?>-cycles"
                     role="tabpanel">
                    <?php if (count($cycles) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Cycle #</th>
                                        <th>Period</th>
                                        <th>Billing Date</th>
                                        <th>Due Date</th>
                                        <th>Amount</th>
                                        <th>Hours</th>
                                        <th>Invoice</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cycles as $cycle): ?>
                                        <tr>
                                            <td>
                                                <strong>#<?= $cycle->cycleNumber ?></strong>
                                            </td>
                                            <td>
                                                <?= date('d M', strtotime($cycle->cycleStartDate)) ?> -
                                                <?= date('d M Y', strtotime($cycle->cycleEndDate)) ?>
                                            </td>
                                            <td>
                                                <?= date('d M Y', strtotime($cycle->billingDate)) ?>
                                            </td>
                                            <td>
                                                <?= date('d M Y', strtotime($cycle->dueDate)) ?>
                                            </td>
                                            <td>
                                                <strong>KES <?= number_format($cycle->amount, 2) ?></strong>
                                            </td>
                                            <td>
                                                <?= number_format($cycle->hoursLogged, 2) ?> hrs
                                            </td>
                                            <td>
                                                <?php if ($cycle->invoiceID): ?>
                                                    <a href="?s=user&ss=invoices&p=invoice&iid=<?= $cycle->invoiceID ?>"
                                                       class="btn btn-sm btn-info">
                                                        <i class="ri-file-list-3-line"></i> View
                                                    </a>
                                                <?php elseif ($cycle->invoiceDraftID): ?>
                                                    <a href="?s=user&ss=projects&p=invoice_drafts&draftid=<?= $cycle->invoiceDraftID ?>"
                                                       class="btn btn-sm btn-warning">
                                                        <i class="ri-draft-line"></i> Draft
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button"
                                                            class="btn btn-outline-primary"
                                                            onclick="viewCycleDetails(<?= $cycle->billingCycleID ?>, <?= $projectID ?>)"
                                                            title="View Details">
                                                        <i class="ri-eye-line"></i>
                                                    </button>
                                                    <?php if (in_array($cycle->status, ['upcoming', 'active'])): ?>
                                                        <button type="button"
                                                                class="btn btn-outline-success"
                                                                onclick="activateCycle(<?= $cycle->billingCycleID ?>, <?= $projectID ?>)"
                                                                title="Activate Cycle">
                                                            <i class="ri-play-line"></i>
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
                        <div class="text-center py-4">
                            <i class="<?= $config['icon'] ?> fs-48 text-muted"></i>
                            <p class="text-muted mt-2">No <?= strtolower($config['label']) ?> cycles</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php
                    $firstContent = false;
                endif;
            endforeach;
            ?>
        </div>
    </div>
</div>

<!-- Cycle Details Modal -->
<div class="modal fade" id="cycleDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Billing Cycle Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="cycleDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * View billing cycle details
 */
function viewCycleDetails(billingCycleID, projectID) {
    const modal = new bootstrap.Modal(document.getElementById('cycleDetailsModal'));
    const content = document.getElementById('cycleDetailsContent');

    content.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    modal.show();

    fetch('../../../php/scripts/projects/get_billing_cycle_details.php?billingCycleID=' + billingCycleID + '&projectID=' + projectID)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                content.innerHTML = data.html;
            } else {
                content.innerHTML = '<div class="alert alert-danger">' + (data.message || 'Failed to load cycle details') + '</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = '<div class="alert alert-danger">An error occurred while loading cycle details.</div>';
        });
}

/**
 * Activate a billing cycle
 */
function activateCycle(billingCycleID, projectID) {
    if (!confirm('Activate this billing cycle? This will replicate the project plan for this cycle.')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'activate_cycle');
    formData.append('billingCycleID', billingCycleID);

    fetch('../../../php/scripts/projects/recurring_project_plan_manager.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof showToast === 'function') {
                showToast('Billing cycle activated successfully!', 'success');
            } else {
                alert('Billing cycle activated successfully!');
            }
            setTimeout(() => location.reload(), 1500);
        } else {
            if (typeof showToast === 'function') {
                showToast('Error: ' + (data.message || 'Failed to activate cycle'), 'error');
            } else {
                alert('Error: ' + (data.message || 'Failed to activate cycle'));
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof showToast === 'function') {
            showToast('An error occurred while activating the cycle', 'error');
        } else {
            alert('An error occurred while activating the cycle');
        }
    });
}

/**
 * Activate next upcoming cycle
 */
function activateNextCycle(projectID) {
    if (!confirm('Activate the next upcoming billing cycle?')) {
        return;
    }

    // Find next upcoming cycle
    fetch('../../../php/scripts/projects/get_billing_cycle_details.php?action=get_next_upcoming&projectID=' + projectID)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.billingCycleID) {
                activateCycle(data.billingCycleID, projectID);
            } else {
                if (typeof showToast === 'function') {
                    showToast('No upcoming cycles found', 'info');
                } else {
                    alert('No upcoming cycles found');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof showToast === 'function') {
                showToast('An error occurred', 'error');
            } else {
                alert('An error occurred');
            }
        });
}
</script>

