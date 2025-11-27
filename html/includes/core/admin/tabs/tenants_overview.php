<?php
// Tenants Overview Tab Content
// $organisations = Admin::org_data(array(), false, $DBConn);
$countries = Data::countries(array(), false, $DBConn);
$entityTypes = Data::entity_types(array(), false, $DBConn);
$licenseTypes = Admin::license_types(array('Suspended' => 'N'), false, $DBConn);
// var_dump($licenseTypes);

  // Initialize permission variables with default values
  $canEdit = false;
  $canManageLicense = false;
  $canToggleSuspension = false;

if (!$organisations) {
    Alert::info("No Tenants/Organisations set up in the system yet", true, array('fst-italic', 'text-center', 'font-18'));
    ?>
    <div class="text-center py-5">
        <div class="avatar avatar-xl bg-primary-transparent mx-auto mb-4">
            <i class="fas fa-building fs-32"></i>
        </div>
        <h5 class="mb-3">Get Started with Your First Tenant</h5>
        <p class="text-muted mb-4">Use the Setup Wizard to create a complete tenant with organizations and entities</p>
        <button class="btn btn-primary btn-wave" data-bs-toggle="modal" data-bs-target="#tenantSetupWizard">
            <i class="fas fa-rocket me-2"></i>Start Setup Wizard
        </button>
    </div>
    <?php
} else {
    ?>
    <div class="table-responsive">
        <table class="table table-hover text-nowrap table-bordered">
            <thead class="table-primary">
                <tr>
                    <th scope="col" class="text-center" style="width: 60px;">#</th>
                    <th scope="col">Organization/Tenant Name</th>
                    <th scope="col">Industry</th>
                    <th scope="col">Location</th>
                    <th scope="col">Entities</th>
                    <th scope="col">Admins</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($organisations as $index => $organisation) {
                    // Get entity count for this organization
                    $orgEntities = Data::entities_full(['orgDataID' => $organisation->orgDataID, 'Suspended' => 'N'], false, $DBConn);
                    $entityCount = $orgEntities ? count($orgEntities) : 0;

                    // Get admin count for this organization
                    $orgAdmins = Core::organisation_admins(['orgDataID' => $organisation->orgDataID, 'Suspended' => 'N'], false, $DBConn);
                    $adminCount = $orgAdmins ? count($orgAdmins) : 0;

                    $status = $organisation->Suspended == 'N' ? 'Active' : 'Suspended';
                    $statusClass = $organisation->Suspended == 'N' ? 'status-active' : 'status-inactive';
                    ?>
                    <tr>
                        <td class="text-center fw-semibold"><?= $index + 1 ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm bg-primary-transparent me-2">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div>
                                    <a href="<?= $base ?>html/?s=core&ss=admin&p=tenant_details&id=<?= $organisation->orgDataID ?>"
                                       class="text-decoration-none">
                                        <h6 class="mb-0 text-primary"><?= htmlspecialchars($organisation->orgName) ?></h6>
                                    </a>
                                    <small class="text-muted"><?= htmlspecialchars($organisation->registrationNumber ?? '') ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($organisation->industryTitle ?? 'N/A') ?></td>
                        <td>
                            <?= htmlspecialchars($organisation->orgCity ?? '') ?>
                            <?php if ($organisation->countryID): ?>
                                <?php
                                $country = Data::countries(['countryID' => $organisation->countryID], true, $DBConn);
                                echo $country ? ', ' . htmlspecialchars($country->countryName) : '';
                                ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-secondary-transparent">
                                <i class="fas fa-sitemap me-1"></i><?= $entityCount ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-success-transparent">
                                <i class="fas fa-users me-1"></i><?= $adminCount ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge <?= $statusClass ?>"><?= $status ?></span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <button type="button"
                                    class="btn btn-sm btn-primary-light btn-wave"
                                    onclick="viewTenantDetails(<?= $organisation->orgDataID ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php
                                if($isSuperAdmin || $isValidAdmin){

                                    // echo " OrgData ID is: " . $organisation->orgDataID;
                                    //check admin status for each organisation
                                    $adminStatus = Core::app_administrators(['orgDataID' => $organisation->orgDataID, 'userID' => $userDetails->ID, 'Suspended' => 'N'], true, $DBConn);




                                    // var_dump($adminStatus);
                                    if($adminStatus){
                                        $canEdit = $adminStatus->adminCode == 'SUPER' || $adminStatus->adminCode == 'TENANT';
                                        $canManageLicense = $adminStatus->adminCode == 'SUPER' ? true : false;
                                        $canToggleSuspension = $adminStatus->adminCode == 'SUPER' ? true : false;
                                    }

                                    if($isSuperAdmin || $isValidAdmin){
                                        $canEdit = true;
                                        $canManageLicense = true;
                                        $canToggleSuspension = true;
                                    }
                                    if($canEdit){  ?>
                                        <button type="button"
                                            class="btn btn-sm btn-info-light btn-wave"
                                            data-bs-toggle="modal"
                                            data-bs-target="#manageOrganisationModal"
                                            onclick="editOrganisation(<?= $organisation->orgDataID ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    <?php
                                    }
                                    if($canManageLicense){ ?>
                                        <button type="button"
                                            class="btn btn-sm btn-success-light btn-wave"
                                            data-bs-toggle="modal"
                                            data-bs-target="#manageLicenseModal"
                                            onclick="manageLicense(<?= $organisation->orgDataID ?>, '<?= htmlspecialchars($organisation->orgName) ?>')">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <?php
                                    }
                                    if($canToggleSuspension){ ?>
                                        <button type="button"
                                            class="btn btn-sm btn-warning-light btn-wave"
                                            onclick="toggleSuspension(<?= $organisation->orgDataID ?>, '<?= $organisation->Suspended ?>')">
                                            <i class="fas fa-<?= $organisation->Suspended == 'N' ? 'ban' : 'check' ?>"></i>
                                        </button>
                                        <?php
                                    }
                                }?>
                            </div>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Tip:</strong> Click the organization name or eye icon to view tenant details, edit icon to modify, key icon to manage licenses, or ban/check icon to toggle status.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php
}
?>

<style>
/* Clickable organization name styling */
td a h6.text-primary {
    transition: all 0.2s ease;
}

td a h6.text-primary:hover {
    text-decoration: underline !important;
    color: #0056b3 !important;
}

td a:hover {
    cursor: pointer;
}
</style>

<script>
function viewTenantDetails(orgId) {
    // Navigate to tenant details page
    window.location.href = '<?= $base ?>html/?s=core&ss=admin&p=tenant_details&id=' + orgId;
}

function editOrganisation(orgId) {
    // Fetch organization data and populate edit modal
    const modal = document.querySelector('#manageOrganisationModal');

    if (!modal) {
        console.error('Organization modal not found');
        return;
    }

    // Update modal title
    modal.querySelector('.modal-title').textContent = 'Edit Organization';

    // Fetch organization data via AJAX
    fetch('<?= $base ?>php/scripts/global/admin/get_organisation.php?orgDataID=' + orgId)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch organization data');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.organisation) {
                const org = data.organisation;

                // Populate form fields
                const fields = {
                    'orgDataID': org.orgDataID,
                    'orgName': org.orgName,
                    'numberOfEmployees': org.numberOfEmployees,
                    'registrationNumber': org.registrationNumber,
                    'orgPIN': org.orgPIN,
                    'orgAddress': org.orgAddress,
                    'orgPostalCode': org.orgPostalCode,
                    'orgCity': org.orgCity,
                    'countryID': org.countryID,
                    'orgPhoneNumber1': org.orgPhoneNumber1,
                    'orgPhoneNUmber2': org.orgPhoneNUmber2,
                    'orgEmail': org.orgEmail,
                    'industrySectorID': org.industrySectorID
                };

                // Fill in each field
                for (const [fieldName, value] of Object.entries(fields)) {
                    const input = modal.querySelector(`[name="${fieldName}"], #${fieldName}`);
                    if (input) {
                        if (input.tagName === 'SELECT') {
                            input.value = value || '';
                        } else if (input.type === 'checkbox') {
                            input.checked = value === 'Y';
                        } else if (input.type === 'textarea' || input.tagName === 'TEXTAREA') {
                            input.value = value || '';
                        } else {
                            input.value = value || '';
                        }
                    }
                }

                // Handle cost center checkbox
                const costCenterCheckbox = modal.querySelector('#costCenterEnabled');
                if (costCenterCheckbox) {
                    costCenterCheckbox.checked = org.costCenterEnabled === 'Y';
                }

            } else {
                alert('Error: ' + (data.message || 'Failed to load organization data'));
            }
        })
        .catch(error => {
            console.error('Error loading organization:', error);
            alert('Error loading organization data. Please try again.');
        });
}

function manageLicense(orgId, orgName) {
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

function resetOrganisationModal() {
    // Reset the organization modal for adding new organization
    const modal = document.querySelector('#manageOrganisationModal');

    if (modal) {
        // Update modal title
        modal.querySelector('.modal-title').textContent = 'Add Organisation';

        // Reset form
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
        }

        // Clear orgDataID
        const orgDataIDInput = modal.querySelector('#orgDataID');
        if (orgDataIDInput) {
            orgDataIDInput.value = '';
        }
    }
}

function toggleSuspension(orgId, currentStatus) {
    const action = currentStatus === 'N' ? 'suspend' : 'activate';
    if (confirm('Are you sure you want to ' + action + ' this organization?')) {
        // Implement suspension toggle
        // This would make an AJAX call to update the status
        alert(action + ' organization ID: ' + orgId);
    }
}
</script>

