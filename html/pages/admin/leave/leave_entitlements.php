<?php
/**
 * Leave Entitlements Management - Admin Page
 * Assign and manage employee leave entitlements
 */

if(!$isValidUser) {
    Alert::info("You need to be logged in to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

// Check admin permissions
if (!$isAdmin && !$isValidAdmin && !$isHRManager) {
    Alert::error("Access denied. Leave Administrator privileges required.", true, array('text-center'));
    return;
}

$entityID = $_SESSION['entityID'] ?? 1;
$orgDataID = $_SESSION['orgDataID'] ?? 1;
$pageTitle = 'Leave Entitlements Management';
$title = $pageTitle . ' - Leave Management System';

// Get leave entitlements using Leave class method
$leaveEntitlements = Leave::leave_entitlements(array('entityID' => $entityID, 'Suspended' => 'N'), false, $DBConn);
// var_dump($leaveEntitlements);
$leaveTypes = Leave::leave_types(array('Lapsed' => 'N'), false, $DBConn);
$entities= Data::entities_full(array("orgDataID" => $orgDataID, 'Suspended' => 'N'), false, $DBConn);
?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-24 mb-0">
            <i class="ri-user-settings-line me-2 text-primary"></i>
            <?= $pageTitle ?>
        </h1>
        <p class="text-muted mb-0 mt-2">Assign leave entitlements to employees based on leave types</p>
    </div>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= $base ?>html/?s=admin&ss=leave&p=dashboard">Leave Admin</a></li>
                <li class="breadcrumb-item active" aria-current="page">Entitlements</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Entitlements Content -->
<div class="row">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">
                    <h4 class="mb-0">
                        <i class="ri-list-check-2 me-2"></i>
                        Leave Entitlements
                    </h4>
                </div>
                <div class="card-options">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manage_Leave_entitlement">
                        <i class="ri-add-line me-2"></i>
                        Add Entitlement
                    </button>
                </div>
            </div>

            <?php if ($leaveEntitlements && is_array($leaveEntitlements) && count($leaveEntitlements) > 0): ?>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 60px;">#</th>
                                <th>Leave Type</th>
                                <th class="text-center">Entitlement (Days)</th>
                                <th class="text-center">Max Per Application</th>
                                <th class="text-center">Min Notice (Days)</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            foreach($leaveEntitlements as $entitlement):
                            ?>
                            <tr>
                                <td class="text-center"><?= $i ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($entitlement->leaveTypeName ?? 'N/A') ?></strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary"><?= $entitlement->entitlement ?> days</span>
                                </td>
                                <td class="text-center">
                                    <?= $entitlement->maxDaysPerApplication ?? 'N/A' ?>
                                </td>
                                <td class="text-center">
                                    <?= $entitlement->minNoticeDays ?? 'N/A' ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info editEntitlement"
                                        data-bs-toggle="modal"
                                        data-bs-target="#manage_Leave_entitlement"
                                        data-entitlementid="<?= $entitlement->leaveEntitlementID ?>"
                                        data-leavetypeid="<?= $entitlement->leaveTypeID ?>"
                                        data-entitlement="<?= $entitlement->entitlement ?>"
                                        data-maxdays="<?= $entitlement->maxDaysPerApplication ?? '' ?>"
                    data-minnotice="<?= $entitlement->minNoticeDays ?? '' ?>"
                    data-entityid="<?= $entitlement->entityID ?? $entityID ?>">
                                        <i class="ri-edit-line me-1"></i>
                                        Edit
                                    </button>
                                </td>
                            </tr>
                            <?php
                            $i++;
                            endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php else: ?>
            <div class="card-body text-center py-5">
                <i class="ri-user-settings-line display-1 text-muted"></i>
                <h4 class="mt-3">No Entitlements Configured</h4>
                <p class="text-muted">Start by creating leave entitlements for your employees</p>
                <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#manage_Leave_entitlement">
                    <i class="ri-add-line me-2"></i>
                    Create First Entitlement
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Back Button -->
<div class="row mt-4">
    <div class="col-12">
        <a href="<?= $base ?>html/?s=admin&ss=leave&p=dashboard" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i>
            Back to Dashboard
        </a>
    </div>
</div>

<!-- Manage Leave Entitlement Modal -->
<?php
echo Utility::form_modal_header(
    'manage_Leave_entitlement',
    'leave/config/manage_Leave_entitlement.php',
    'Manage Leave Entitlement',
    array('modal-dialog-centered', 'modal-lg'),
    $base
);

include 'includes/scripts/leave/leave_configurations/modals/manage_Leave_entitlement.php';

echo Utility::form_modal_footer('Save Entitlement');
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit entitlement
    document.querySelectorAll('.editEntitlement').forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = document.querySelector('#manage_Leave_entitlement');
            modal.querySelector('.modal-title').textContent = 'Edit Leave Entitlement';

            // Populate form fields
            const entitlementID = this.dataset.entitlementid;
            const leaveTypeID = this.dataset.leavetypeid;
            const entitlement = this.dataset.entitlement;
            const maxDays = this.dataset.maxdays;
            const minNotice = this.dataset.minnotice;

            // Set form values
            modal.querySelector('[name="leaveEntitlementID"]').value = entitlementID || '';
            modal.querySelector('[name="leaveTypeID"]').value = leaveTypeID || '';
            modal.querySelector('[name="entitlement"]').value = entitlement || '';
            modal.querySelector('[name="maxDaysPerApplication"]').value = maxDays || '';
            modal.querySelector('[name="minNoticeDays"]').value = minNotice || '';
            modal.querySelector('[name="entityID"]').value = this.dataset.entityid || '';
        });
    });

    // Reset form when opening for new entitlement
    document.querySelectorAll('[data-bs-target="#manage_Leave_entitlement"]').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!this.classList.contains('editEntitlement')) {
                const modal = document.querySelector('#manage_Leave_entitlement');
                modal.querySelector('.modal-title').textContent = 'Add Leave Entitlement';
                modal.querySelector('[name="leaveEntitlementID"]').value = '';
                modal.querySelector('[name="leaveTypeID"]').value = '';
                modal.querySelector('[name="entitlement"]').value = '';
                modal.querySelector('[name="maxDaysPerApplication"]').value = '';
                modal.querySelector('[name="minNoticeDays"]').value = '';
                modal.querySelector('[name="entityID"]').value = '<?= $entityID ?>';
            }
        });
    });
});
</script>

