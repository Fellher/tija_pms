<?php
// License Management Tab Content
// $organisations = Admin::org_data(array(), false, $DBConn);

?>
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-primary" role="alert">
            <div class="d-flex align-items-start">
                <div class="me-3">
                    <i class="fas fa-info-circle fs-24"></i>
                </div>
                <div class="flex-fill">
                    <h6 class="alert-heading mb-2">License Management System</h6>
                    <p class="mb-0">
                        Manage software licenses for each tenant/organization. Assign licenses, set expiration dates,
                        control user limits, and monitor license usage across your system.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!$organisations): ?>
    <div class="text-center py-5">
        <div class="avatar avatar-xl bg-warning-transparent mx-auto mb-4">
            <i class="fas fa-certificate fs-32"></i>
        </div>
        <h5 class="mb-3">No Organizations to License</h5>
        <p class="text-muted mb-4">Create an organization first to assign licenses</p>
        <button class="btn btn-primary btn-wave" data-bs-toggle="modal" data-bs-target="#manageOrganisationModal">
            <i class="fas fa-building me-2"></i>Create Organization
        </button>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-warning">
                <tr>
                    <th scope="col" style="width: 5%;">#</th>
                    <th scope="col" style="width: 25%;">Organization</th>
                    <th scope="col" style="width: 15%;">License Type</th>
                    <th scope="col" style="width: 12%;">User Limit</th>
                    <th scope="col" style="width: 12%;">Users Active</th>
                    <th scope="col" style="width: 13%;">Issue Date</th>
                    <th scope="col" style="width: 13%;">Expiry Date</th>
                    <th scope="col" style="width: 10%;">Status</th>
                    <th scope="col" class="text-center" style="width: 10%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($organisations as $index => $organisation) {
                    // For demonstration - In production, fetch from tija_licenses table
                    $licenseType = 'Standard';
                    $userLimit = 100;
                    $activeUsers = rand(20, 90); // Replace with actual count
                    $issueDate = date('Y-m-d', strtotime('-6 months'));
                    $expiryDate = date('Y-m-d', strtotime('+6 months'));

                    // Calculate status
                    $daysUntilExpiry = (strtotime($expiryDate) - time()) / (60 * 60 * 24);
                    if ($daysUntilExpiry < 0) {
                        $status = 'Expired';
                        $statusClass = 'bg-danger';
                    } elseif ($daysUntilExpiry < 30) {
                        $status = 'Expiring Soon';
                        $statusClass = 'bg-warning';
                    } else {
                        $status = 'Active';
                        $statusClass = 'bg-success';
                    }

                    $usagePercentage = ($activeUsers / $userLimit) * 100;
                    ?>
                    <tr>
                        <td class="text-center"><?= $index + 1 ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm bg-primary-transparent me-2">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0"><?= htmlspecialchars($organisation->orgName) ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars($organisation->registrationNumber ?? 'N/A') ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info-transparent">
                                <i class="fas fa-certificate me-1"></i><?= $licenseType ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <strong><?= $userLimit ?></strong> users
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="flex-fill">
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar <?= $usagePercentage > 80 ? 'bg-danger' : ($usagePercentage > 60 ? 'bg-warning' : 'bg-success') ?>"
                                             role="progressbar"
                                             style="width: <?= $usagePercentage ?>%"
                                             aria-valuenow="<?= $usagePercentage ?>"
                                             aria-valuemin="0"
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                                <small class="ms-2 text-muted"><?= $activeUsers ?></small>
                            </div>
                        </td>
                        <td>
                            <small>
                                <i class="fas fa-calendar-plus me-1"></i>
                                <?= date('M d, Y', strtotime($issueDate)) ?>
                            </small>
                        </td>
                        <td>
                            <small>
                                <i class="fas fa-calendar-times me-1"></i>
                                <?= date('M d, Y', strtotime($expiryDate)) ?>
                            </small>
                            <?php if ($daysUntilExpiry > 0 && $daysUntilExpiry < 30): ?>
                                <br><small class="text-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i><?= round($daysUntilExpiry) ?> days left
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= $statusClass ?>">
                                <?= $status ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <button type="button"
                                    class="btn btn-sm btn-success-light btn-wave"
                                    data-bs-toggle="modal"
                                    data-bs-target="#manageLicenseModal"
                                    onclick="editLicense(<?= $organisation->orgDataID ?>, '<?= htmlspecialchars($organisation->orgName) ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button"
                                    class="btn btn-sm btn-info-light btn-wave"
                                    onclick="viewLicenseDetails(<?= $organisation->orgDataID ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button"
                                    class="btn btn-sm btn-primary-light btn-wave"
                                    onclick="renewLicense(<?= $organisation->orgDataID ?>)">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card custom-card border-success">
                <div class="card-body text-center">
                    <h3 class="mb-2 text-success">
                        <?php
                        $activeCount = 0;
                        foreach ($organisations as $org) {
                            if ($org->Suspended == 'N') $activeCount++;
                        }
                        echo $activeCount;
                        ?>
                    </h3>
                    <p class="mb-0 text-muted">Active Licenses</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card custom-card border-warning">
                <div class="card-body text-center">
                    <h3 class="mb-2 text-warning">0</h3>
                    <p class="mb-0 text-muted">Expiring Soon</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card custom-card border-danger">
                <div class="card-body text-center">
                    <h3 class="mb-2 text-danger">0</h3>
                    <p class="mb-0 text-muted">Expired</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card custom-card border-info">
                <div class="card-body text-center">
                    <h3 class="mb-2 text-info"><?= count($organisations) ?></h3>
                    <p class="mb-0 text-muted">Total Licenses</p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
function editLicense(orgId, orgName) {
    // Fetch and populate license management modal with organization data
    const modal = document.querySelector('#manageLicenseModal');

    if (!modal) {
        console.error('License modal not found');
        return;
    }

    // Set organization ID and update title
    modal.querySelector('input[name="orgDataID"]').value = orgId;
    modal.querySelector('.modal-title').textContent = 'Manage License for ' + orgName;

    // Fetch existing license data via AJAX
    fetch('<?= $base ?>php/scripts/global/admin/get_license.php?orgDataID=' + orgId)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch license data');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.hasLicense && data.license) {
                const license = data.license;

                // Populate form fields
                const fields = {
                    'licenseID': license.licenseID,
                    'licenseType': license.licenseType,
                    'userLimit': license.userLimit,
                    'licenseKey': license.licenseKey,
                    'licenseStatus': license.licenseStatus,
                    'licenseNotes': license.licenseNotes
                };

                // Fill in each field
                for (const [fieldName, value] of Object.entries(fields)) {
                    const input = modal.querySelector(`[name="${fieldName}"], #${fieldName}`);
                    if (input) {
                        input.value = value || '';
                    }
                }

                // Set dates using flatpickr
                const issueDatePicker = modal.querySelector('#licenseIssueDate')._flatpickr;
                if (issueDatePicker && license.licenseIssueDate) {
                    issueDatePicker.setDate(license.licenseIssueDate);
                }

                const expiryDatePicker = modal.querySelector('#licenseExpiryDate')._flatpickr;
                if (expiryDatePicker && license.licenseExpiryDate) {
                    expiryDatePicker.setDate(license.licenseExpiryDate);
                }

                // Handle features checkboxes
                if (license.features && Array.isArray(license.features)) {
                    modal.querySelectorAll('input[name="features[]"]').forEach(checkbox => {
                        checkbox.checked = license.features.includes(checkbox.value);
                    });
                }

                console.log('License data loaded:', license);
            } else {
                // No existing license - reset form for new license
                console.log('No existing license found. Ready for new license.');
                const form = modal.querySelector('form');
                if (form) {
                    form.reset();
                    // Keep orgDataID
                    modal.querySelector('input[name="orgDataID"]').value = orgId;
                }

                // Clear licenseID
                const licenseIDInput = modal.querySelector('#licenseID');
                if (licenseIDInput) {
                    licenseIDInput.value = '';
                }
            }
        })
        .catch(error => {
            console.error('Error loading license:', error);
            // Reset form on error - prepare for new license
            const form = modal.querySelector('form');
            if (form) {
                form.reset();
                modal.querySelector('input[name="orgDataID"]').value = orgId;
            }
        });
}

function viewLicenseDetails(orgId) {
    // Show detailed license information
    alert('View license details for organization ID: ' + orgId);
    // Implement detailed view modal
}

function renewLicense(orgId) {
    if (confirm('Are you sure you want to renew the license for this organization?')) {
        // Implement license renewal
        alert('Renewing license for organization ID: ' + orgId);
    }
}
</script>

