<!-- Role Levels Management Page -->
<?php
// Check admin access
if (!$isValidAdmin && !$isAdmin) {
    Alert::info("You need to be logged in as a valid administrator to access this page", true,
        array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    exit;
}

// Fetch role levels
$roleLevels = Data::role_levels(['Suspended' => 'N'], false, $DBConn);
if ($roleLevels) {
    // Sort by levelNumber (ascending - lower numbers = higher authority)
    usort($roleLevels, function($a, $b) {
        return ($a->levelNumber ?? 999) - ($b->levelNumber ?? 999);
    });
}
?>

<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <h1 class="page-title fw-semibold fs-24 mb-0">Role Levels Management</h1>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= $base ?>html/?s=core&ss=admin&p=home">Admin</a></li>
                <li class="breadcrumb-item active" aria-current="page">Role Levels</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-lg-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Total Role Levels</p>
                        <h3 class="mb-0 fw-semibold"><?= $roleLevels ? count($roleLevels) : 0 ?></h3>
                        <small class="text-muted fs-11">Active levels</small>
                    </div>
                    <div class="ms-2">
                        <span class="avatar avatar-md bg-primary-transparent">
                            <i class="fas fa-layer-group fs-20"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Default Levels</p>
                        <h3 class="mb-0 fw-semibold">
                            <?php
                            $defaultCount = 0;
                            if ($roleLevels) {
                                foreach ($roleLevels as $level) {
                                    if ($level->isDefault == 'Y') $defaultCount++;
                                }
                            }
                            echo $defaultCount;
                            ?>
                        </h3>
                        <small class="text-muted fs-11">System defaults</small>
                    </div>
                    <div class="ms-2">
                        <span class="avatar avatar-md bg-warning-transparent">
                            <i class="fas fa-star fs-20"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Active Levels</p>
                        <h3 class="mb-0 fw-semibold">
                            <?php
                            $activeCount = 0;
                            if ($roleLevels) {
                                foreach ($roleLevels as $level) {
                                    if ($level->isActive == 'Y') $activeCount++;
                                }
                            }
                            echo $activeCount;
                            ?>
                        </h3>
                        <small class="text-muted fs-11">Currently active</small>
                    </div>
                    <div class="ms-2">
                        <span class="avatar avatar-md bg-success-transparent">
                            <i class="fas fa-check-circle fs-20"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Custom Levels</p>
                        <h3 class="mb-0 fw-semibold">
                            <?php
                            $customCount = 0;
                            if ($roleLevels) {
                                foreach ($roleLevels as $level) {
                                    if ($level->isDefault == 'N') $customCount++;
                                }
                            }
                            echo $customCount;
                            ?>
                        </h3>
                        <small class="text-muted fs-11">User-created</small>
                    </div>
                    <div class="ms-2">
                        <span class="avatar avatar-md bg-info-transparent">
                            <i class="fas fa-plus-circle fs-20"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Role Levels Management -->
<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">
                    <h5 class="mb-0">Role Levels</h5>
                    <small class="text-muted">Lower numbers = Higher authority in hierarchy</small>
                </div>
                <button type="button" class="btn btn-primary btn-wave" data-bs-toggle="modal" data-bs-target="#manageRoleLevelModal" onclick="resetRoleLevelForm()">
                    <i class="fas fa-plus me-2"></i>Add New Role Level
                </button>
            </div>
            <div class="card-body">
                <?php if (!$roleLevels): ?>
                    <div class="text-center py-5">
                        <div class="avatar avatar-xl bg-primary-transparent mx-auto mb-4">
                            <i class="fas fa-layer-group fs-32"></i>
                        </div>
                        <h5 class="mb-3">No Role Levels Found</h5>
                        <p class="text-muted mb-4">Create your first role level to get started</p>
                        <button class="btn btn-primary btn-wave" data-bs-toggle="modal" data-bs-target="#manageRoleLevelModal" onclick="resetRoleLevelForm()">
                            <i class="fas fa-plus me-2"></i>Add Role Level
                        </button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-primary">
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 10%;">Level #</th>
                                    <th style="width: 20%;">Level Name</th>
                                    <th style="width: 25%;">Description</th>
                                    <th style="width: 10%;">Code</th>
                                    <th style="width: 10%;">Display Order</th>
                                    <th style="width: 10%;">Status</th>
                                    <th style="width: 20%;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="sortable-role-levels">
                                <?php foreach ($roleLevels as $index => $level): ?>
                                    <tr data-id="<?= $level->roleLevelID ?>" data-order="<?= $level->displayOrder ?>">
                                        <td class="text-center">
                                            <i class="fas fa-grip-vertical text-muted drag-handle" style="cursor: move;"></i>
                                            <?= $index + 1 ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= $level->levelNumber <= 2 ? 'danger' : ($level->levelNumber <= 4 ? 'warning' : 'info') ?>-transparent">
                                                Level <?= $level->levelNumber ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($level->levelName) ?></strong>
                                            <?php if ($level->isDefault == 'Y'): ?>
                                                <span class="badge bg-warning-transparent ms-2"><small>Default</small></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?= htmlspecialchars(substr($level->levelDescription ?? '', 0, 80)) ?><?= strlen($level->levelDescription ?? '') > 80 ? '...' : '' ?></small>
                                        </td>
                                        <td>
                                            <?php if ($level->levelCode): ?>
                                                <code><?= htmlspecialchars($level->levelCode) ?></code>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary-transparent">
                                                <?= $level->displayOrder ?? 0 ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($level->isActive == 'Y'): ?>
                                                <span class="badge bg-success-transparent">
                                                    <i class="fas fa-check me-1"></i>Active
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-transparent">
                                                    <i class="fas fa-times me-1"></i>Inactive
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <button type="button"
                                                    class="btn btn-sm btn-primary-light btn-wave"
                                                    onclick="editRoleLevel(<?= $level->roleLevelID ?>)"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#manageRoleLevelModal"
                                                    title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($level->isDefault != 'Y'): ?>
                                                    <button type="button"
                                                        class="btn btn-sm btn-danger-light btn-wave"
                                                        onclick="deleteRoleLevel(<?= $level->roleLevelID ?>, '<?= htmlspecialchars($level->levelName) ?>')"
                                                        title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button"
                                                        class="btn btn-sm btn-secondary-light btn-wave"
                                                        disabled
                                                        title="Cannot delete default role levels">
                                                        <i class="fas fa-lock"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Include the role level management modal
include "includes/core/admin/modals/manage_role_level.php";
?>

<style>
.drag-handle {
    cursor: move !important;
}

.sortable-role-levels tr {
    cursor: move;
}

.sortable-role-levels tr:hover {
    background-color: #f8f9fa;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
// Initialize sortable for drag and drop reordering
document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.querySelector('.sortable-role-levels');
    if (tbody) {
        new Sortable(tbody, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function(evt) {
                updateDisplayOrder();
            }
        });
    }
});

function updateDisplayOrder() {
    const rows = document.querySelectorAll('.sortable-role-levels tr');
    const orderData = [];

    rows.forEach((row, index) => {
        const id = row.getAttribute('data-id');
        orderData.push({
            id: id,
            order: index + 1
        });
    });

    // Send AJAX request to update order
    fetch('<?= $base ?>php/scripts/global/admin/manage_role_levels.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=update_order&orderData=' + encodeURIComponent(JSON.stringify(orderData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Order updated successfully');
        } else {
            console.error('Failed to update order:', data.message || 'Unknown error');
        }
    })
    .catch(error => {
        console.error('Error updating display order:', error);
    });
}

function resetRoleLevelForm() {
    document.getElementById('manageRoleLevelForm').reset();
    document.getElementById('roleLevelID').value = '';
    document.getElementById('isActive').checked = true;
    document.querySelector('#manageRoleLevelModal .modal-title').textContent = 'Add New Role Level';
}

function editRoleLevel(id) {
    // Fetch role level data and populate form
    fetch('<?= $base ?>php/scripts/global/admin/manage_role_levels.php?action=get&id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const level = data.roleLevel;

                // Populate form fields
                document.getElementById('roleLevelID').value = level.roleLevelID;
                document.getElementById('levelNumber').value = level.levelNumber;
                document.getElementById('levelName').value = level.levelName;
                document.getElementById('levelCode').value = level.levelCode || '';
                document.getElementById('levelDescription').value = level.levelDescription || '';
                document.getElementById('displayOrder').value = level.displayOrder || 0;
                document.getElementById('isActive').checked = level.isActive === 'Y';

                document.querySelector('#manageRoleLevelModal .modal-title').textContent = 'Edit Role Level';
            }
        });
}

function deleteRoleLevel(id, name) {
    if (confirm('Are you sure you want to delete the role level "' + name + '"?\n\nWarning: This may affect existing roles using this level.')) {
        fetch('<?= $base ?>php/scripts/global/admin/manage_role_levels.php?action=delete&id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Role level deleted successfully');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to delete role level'));
                }
            });
    }
}
</script>

