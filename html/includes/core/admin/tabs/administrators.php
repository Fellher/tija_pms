<?php
// Administrators Tab Content
$admins = Core::organisation_admins(['Suspended' => "N"], false, $DBConn);
$adminTypes = Admin::admin_types(array(), false, $DBConn);
$users = Core::user([], false, $DBConn);
// $organisations = Admin::org_data(array(), false, $DBConn);
//filter for only admind for the current $organisations where organisation is an array of organisations
$admins = array_filter($admins, function($admin) use ($organisations){
    return in_array($admin->orgDataID, array_column($organisations, 'orgDataID'));
});
// var_dump($admins);
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-2">System Administrators</h5>
                <p class="text-muted mb-0">Manage administrator access and permissions across all tenants</p>
            </div>
            <button type="button"
                class="btn btn-primary btn-wave"
                data-bs-toggle="modal"
                data-bs-target="#manageAdmin">
                <i class="fas fa-user-plus me-2"></i>Add Administrator
            </button>
        </div>
    </div>
</div>

<?php if (!$admins): ?>
    <div class="text-center py-5">
        <div class="avatar avatar-xl bg-success-transparent mx-auto mb-4">
            <i class="fas fa-user-shield fs-32"></i>
        </div>
        <h5 class="mb-3">No Administrators Set Up</h5>
        <p class="text-muted mb-4">Create your first administrator to manage the system</p>
        <button class="btn btn-primary btn-wave" data-bs-toggle="modal" data-bs-target="#manageAdmin">
            <i class="fas fa-user-plus me-2"></i>Add First Administrator
        </button>
    </div>
<?php else: ?>
    <!-- Admin Type Filter -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-filter"></i></span>
                <select class="form-select" id="adminTypeFilter" onchange="filterAdmins()">
                    <option value="">All Admin Types</option>
                    <?php if ($adminTypes): ?>
                        <?php foreach ($adminTypes as $type): ?>
                            <option value="<?= $type->adminTypeID ?>">
                                <?= htmlspecialchars($type->adminTypeName) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-building"></i></span>
                <select class="form-select" id="orgFilter" onchange="filterAdmins()">
                    <option value="">All Organizations</option>
                    <?php if ($organisations): ?>
                        <?php foreach ($organisations as $org): ?>
                            <option value="<?= $org->orgDataID ?>">
                                <?= htmlspecialchars($org->orgName) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="searchAdmin" placeholder="Search by name or email..." onkeyup="filterAdmins()">
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover" id="adminsTable">
            <thead class="table-success">
                <tr>
                    <th scope="col" style="width: 5%;">#</th>
                    <th scope="col" style="width: 20%;">Admin Name</th>
                    <th scope="col" style="width: 15%;">Admin Type</th>
                    <th scope="col" style="width: 20%;">Organisation</th>
                    <th scope="col" style="width: 15%;">Entity</th>
                    <th scope="col" style="width: 13%;">Unit</th>
                    <th scope="col" style="width: 7%;">Status</th>
                    <th class="text-center" scope="col" style="width: 10%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $index => $admin): ?>
                    <tr class="admin-row"
                        data-admin-type="<?= $admin->adminTypeID ?>"
                        data-org-id="<?= $admin->orgDataID ?>"
                        data-search-text="<?= strtolower($admin->AdminName . ' ' . $admin->Email) ?>">
                        <td class="text-center"><?= $index + 1 ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <?php if ($admin->profile_image): ?>
                                    <img src="<?= $admin->profile_image ?>"
                                         alt="Profile"
                                         class="avatar avatar-sm rounded-circle me-2">
                                <?php else: ?>
                                    <div class="avatar avatar-sm bg-primary-transparent rounded-circle me-2">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h6 class="mb-0"><?= htmlspecialchars($admin->AdminName) ?></h6>
                                    <small class="text-muted">
                                        <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($admin->Email) ?>
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php
                            $adminTypeClass = '';
                            switch ($admin->adminTypeName) {
                                case 'Super Admin':
                                    $adminTypeClass = 'bg-danger';
                                    break;
                                case 'System Admin':
                                    $adminTypeClass = 'bg-primary';
                                    break;
                                case 'Entity Admin':
                                    $adminTypeClass = 'bg-info';
                                    break;
                                default:
                                    $adminTypeClass = 'bg-secondary';
                            }
                            ?>
                            <span class="badge <?= $adminTypeClass ?>">
                                <?= htmlspecialchars($admin->adminTypeName ?? 'N/A') ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($admin->orgName ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($admin->entityName ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($admin->unitName ?? 'N/A') ?></td>
                        <td>
                            <span class="badge bg-success-transparent">
                                <i class="fas fa-check-circle me-1"></i>Active
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <button
                                    class="btn btn-sm btn-primary-light btn-wave edit_admin_restricted"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editAdminRestricted"
                                    data-admin-id="<?= $admin->adminID ?>"
                                    data-user-id="<?= $admin->userID ?>"
                                    data-admin-name="<?= htmlspecialchars($admin->AdminName) ?>"
                                    data-admin-email="<?= htmlspecialchars($admin->Email) ?>"
                                    data-admin-type-id="<?= $admin->adminTypeID ?>"
                                    data-org-data-id="<?= $admin->orgDataID ?>"
                                    data-org-name="<?= htmlspecialchars($admin->orgName ?? 'N/A') ?>"
                                    data-entity-id="<?= $admin->entityID ?>"
                                    data-unit-id="<?= $admin->unitID ?>"
                                    data-unit-type-id="<?= $admin->unitTypeID ?>"
                                    data-is-employee="<?= $admin->isEmployee ?>"
                                    title="Edit Role & Assignment">
                                    <i class="fas fa-user-cog"></i>
                                </button>
                                <button
                                    class="btn btn-sm btn-info-light btn-wave edit_admin"
                                    data-bs-toggle="modal"
                                    data-bs-target="#manageAdmin"
                                    data-admin-id="<?= $admin->adminID ?>"
                                    data-user-id="<?= $admin->userID ?>"
                                    data-admin-type-id="<?= $admin->adminTypeID ?>"
                                    data-org-data-id="<?= $admin->orgDataID ?>"
                                    data-entity-id="<?= $admin->entityID ?>"
                                    data-unit-id="<?= $admin->unitID ?>"
                                    data-unit-type-id="<?= $admin->unitTypeID ?>"
                                    data-is-employee="<?= $admin->isEmployee ?>"
                                    title="Full Edit (Change Person)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button
                                    class="btn btn-sm btn-warning-light btn-wave"
                                    onclick="toggleAdminStatus(<?= $admin->adminID ?>)"
                                    title="Suspend Administrator">
                                    <i class="fas fa-ban"></i>
                                </button>
                                <button
                                    class="btn btn-sm btn-danger-light btn-wave"
                                    onclick="deleteAdmin(<?= $admin->adminID ?>, '<?= htmlspecialchars($admin->AdminName) ?>')"
                                    title="Delete Administrator">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Statistics Cards -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card custom-card">
                <div class="card-header">
                    <h6 class="mb-0">Administrator Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php
                        // Count admins by type
                        $adminTypeCounts = [];
                        foreach ($admins as $admin) {
                            $typeName = $admin->adminTypeName ?? 'Unknown';
                            if (!isset($adminTypeCounts[$typeName])) {
                                $adminTypeCounts[$typeName] = 0;
                            }
                            $adminTypeCounts[$typeName]++;
                        }

                        $colors = [
                            'Super Admin' => 'danger',
                            'System Admin' => 'primary',
                            'Entity Admin' => 'info',
                            'Unit Admin' => 'success',
                            'Team Admin' => 'warning'
                        ];

                        foreach ($adminTypeCounts as $typeName => $count):
                            $color = $colors[$typeName] ?? 'secondary';
                        ?>
                            <div class="col-md">
                                <div class="text-center p-3 border rounded">
                                    <h4 class="mb-1 text-<?= $color ?>"><?= $count ?></h4>
                                    <p class="mb-0 text-muted fs-12"><?= htmlspecialchars($typeName) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
function filterAdmins() {
    const adminTypeFilter = document.getElementById('adminTypeFilter').value;
    const orgFilter = document.getElementById('orgFilter').value;
    const searchText = document.getElementById('searchAdmin').value.toLowerCase();

    const rows = document.querySelectorAll('.admin-row');

    rows.forEach(row => {
        let show = true;

        // Filter by admin type
        if (adminTypeFilter && row.getAttribute('data-admin-type') !== adminTypeFilter) {
            show = false;
        }

        // Filter by organization
        if (orgFilter && row.getAttribute('data-org-id') !== orgFilter) {
            show = false;
        }

        // Filter by search text
        if (searchText && !row.getAttribute('data-search-text').includes(searchText)) {
            show = false;
        }

        row.style.display = show ? '' : 'none';
    });
}

function toggleAdminStatus(adminId) {
    if (confirm('Are you sure you want to suspend this administrator?')) {
        // Implement status toggle via AJAX
        alert('Toggle status for admin ID: ' + adminId);
    }
}

function deleteAdmin(adminId, adminName) {
    if (confirm('Are you sure you want to delete administrator "' + adminName + '"? This action cannot be undone.')) {
        // Implement delete via AJAX
        alert('Delete admin ID: ' + adminId);
    }
}

// Edit admin button handler (kept from original code)
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.edit_admin');

    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = document.querySelector('#manageAdmin');
            if (modal) {
                const data = this.dataset;

                // Clear any previous error messages
                const errorMessage = modal.querySelector('.error-message');
                if (errorMessage) {
                    errorMessage.textContent = '';
                }

                // Map form fields to their corresponding data attributes
                const fieldMappings = {
                    'adminID': 'adminId',
                    'userID': 'userId',
                    'adminTypeID': 'adminTypeId',
                    'orgDataID': 'orgDataId',
                    'entityID': 'entityId',
                    'unitID': 'unitId',
                    'unitTypeID': 'unitTypeId',
                    'isEmployee': 'isEmployee'
                };

                // Fill in the form fields using the data attributes
                for (const [field, dataAttr] of Object.entries(fieldMappings)) {
                    const input = modal.querySelector(`input[name="${field}"], select[name="${field}"]`);
                    if (input && data[dataAttr]) {
                        input.value = data[dataAttr];
                    }
                }
            }
        });
    });
});
</script>

