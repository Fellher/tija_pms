<?php
if(!$isValidUser) {
    Alert::info("You need to be logged in to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

$applicationID = isset($_GET['applicationID']) ? (int)Utility::clean_string($_GET['applicationID']) : 0;
$handoverReport = $applicationID ? LeaveHandover::get_handover_report($applicationID, $DBConn) : null;

if (!$handoverReport) {
    Alert::error("Unable to load handover report for the selected application.", true);
    return;
}

$handover = is_object($handoverReport['handover']) ? $handoverReport['handover'] : (object)$handoverReport['handover'];
$items = $handoverReport['items'] ?? array();
$statusBadgeClass = 'bg-secondary';
switch ($handover->handoverStatus) {
    case 'completed':
        $statusBadgeClass = 'bg-success';
        break;
    case 'in_progress':
        $statusBadgeClass = 'bg-info';
        break;
    case 'partial':
        $statusBadgeClass = 'bg-warning text-dark';
        break;
    case 'pending':
    default:
        $statusBadgeClass = 'bg-secondary';
        break;
}
?>

<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-medium fs-24 mb-0">
            <i class="ri-file-list-line me-2 text-primary"></i>
            Handover Report
        </h1>
        <p class="text-muted mb-0 mt-1">Detailed handover summary for leave application #<?= $handover->leaveApplicationID ?></p>
    </div>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $s ?></a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $ss ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Handover Report</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-1">Handover Status</p>
                <span class="badge <?= $statusBadgeClass ?> text-uppercase"><?= htmlspecialchars($handover->handoverStatus) ?></span>
                <?php if (!empty($handover->completionDate)): ?>
                    <div class="text-muted small mt-2">Completed on <?= date('M j, Y H:i', strtotime($handover->completionDate)) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-1">Handover Created</p>
                <div><?= date('M j, Y H:i', strtotime($handover->handoverDate)) ?></div>
                <small class="text-muted">Leave application #<?= $handover->leaveApplicationID ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-1">Total Items</p>
                <div class="fw-bold fs-4"><?= count($items) ?></div>
                <small class="text-muted">Including assigned colleagues</small>
            </div>
        </div>
    </div>
</div>

<?php if (empty($items)): ?>
    <div class="alert alert-warning">
        <i class="ri-information-line me-2"></i>
        No structured handover items were captured for this leave application.
    </div>
<?php else: ?>
    <?php foreach ($items as $index => $itemRow): ?>
        <?php $item = is_object($itemRow) ? $itemRow : (object)$itemRow; ?>
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge bg-light text-dark me-2">#<?= $index + 1 ?></span>
                    <strong><?= htmlspecialchars($item->itemTitle ?? 'Handover Item') ?></strong>
                </div>
                <div>
                    <?php if (!empty($item->priority)): ?>
                        <span class="badge bg-primary text-uppercase me-2"><?= htmlspecialchars($item->priority) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($item->dueDate)): ?>
                        <span class="badge bg-light text-dark">Due <?= date('M j, Y', strtotime($item->dueDate)) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3"><?= nl2br(htmlspecialchars($item->itemDescription ?? 'No instructions provided.')) ?></p>
                <div class="row">
                    <div class="col-md-6">
                        <p class="text-muted mb-1 small">Project / Task</p>
                        <div>
                            <?= htmlspecialchars($item->projectName ?? 'General responsibility') ?>
                            <?php if (!empty($item->projectTaskName)): ?>
                                <small class="text-muted d-block"><?= htmlspecialchars($item->projectTaskName) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-1 small">Assignment Status</p>
                        <div>
                            <span class="badge <?= ($item->confirmationStatus === 'confirmed') ? 'bg-success' : 'bg-warning text-dark' ?>">
                                <?= htmlspecialchars($item->confirmationStatus ?? 'pending') ?>
                            </span>
                            <?php if (!empty($item->confirmedDate)): ?>
                                <small class="text-muted ms-2">on <?= date('M j, Y H:i', strtotime($item->confirmedDate)) ?></small>
                            <?php endif; ?>
                        </div>
                        <small class="text-muted d-block">Assigned to: <?= htmlspecialchars($item->assigneeName ?? 'Colleague') ?></small>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

