<!-- Quick Add Prospect Modal -->
<div class="modal fade" id="quickAddProspectModal" tabindex="-1" aria-labelledby="quickAddProspectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="quickAddProspectModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Quick Add Prospect
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickAddProspectForm">
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Quick Entry:</strong> Fill in the essential details below. You can add more information later from the prospect detail page.
                    </div>

                    <div class="row g-3">
                        <!-- Essential Information -->
                        <div class="col-md-6">
                            <label for="quickProspectName" class="form-label">Prospect Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="quickProspectName" name="salesProspectName" required>
                        </div>

                        <div class="col-md-6">
                            <label for="quickProspectEmail" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="quickProspectEmail" name="prospectEmail" required>
                        </div>

                        <div class="col-md-6">
                            <label for="quickProspectPhone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="quickProspectPhone" name="prospectPhone">
                        </div>

                        <div class="col-md-6">
                            <label for="quickBusinessUnit" class="form-label">Business Unit <span class="text-danger">*</span></label>
                            <select class="form-select" id="quickBusinessUnit" name="businessUnitID" required>
                                <option value="">Select Business Unit</option>
                                <?php
                                $allBusinessUnits = Data::business_units(array(), false, $DBConn);
                                if ($allBusinessUnits) {
                                    foreach ($allBusinessUnits as $bu) {
                                        echo "<option value='{$bu->businessUnitID}'>{$bu->businessUnitName}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="quickLeadSource" class="form-label">Lead Source <span class="text-danger">*</span></label>
                            <select class="form-select" id="quickLeadSource" name="leadSourceID" required>
                                <option value="">Select Lead Source</option>
                                <?php
                                $allLeadSources = Sales::lead_sources(array(), false, $DBConn);
                                if ($allLeadSources) {
                                    foreach ($allLeadSources as $source) {
                                        echo "<option value='{$source->leadSourceID}'>{$source->leadSourceName}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="quickEstimatedValue" class="form-label">Estimated Value (KES)</label>
                            <input type="number" class="form-control" id="quickEstimatedValue" name="estimatedValue" min="0" step="0.01">
                        </div>

                        <div class="col-md-6">
                            <label for="quickOrganization" class="form-label">Organization</label>
                            <select class="form-select" id="quickOrganization" name="orgDataID">
                                <option value="">Select Organization</option>
                                <?php
                                $allOrgs = Admin::org_data(array(), false, $DBConn);
                                if ($allOrgs) {
                                    foreach ($allOrgs as $org) {
                                        echo "<option value='{$org->orgDataID}'>{$org->orgName}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="quickOwner" class="form-label">Assign To</label>
                            <select class="form-select" id="quickOwner" name="ownerID">
                                <option value="">Select Owner</option>
                                <?php
                                $allEmployees = Employee::employees(array(), false, $DBConn);
                                if ($allEmployees) {
                                    foreach ($allEmployees as $emp) {
                                        echo "<option value='{$emp->ID}'>{$emp->employeeName}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="quickCaseName" class="form-label">Case/Opportunity Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="quickCaseName" name="prospectCaseName" placeholder="Brief description of the opportunity" required>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="quickIsClient" name="isClient" value="Y">
                                <label class="form-check-label" for="quickIsClient">
                                    This prospect is an existing client
                                </label>
                            </div>
                        </div>

                        <div class="col-12 d-none" id="quickClientSelectContainer">
                            <label for="quickClientSelect" class="form-label">Select Existing Client</label>
                            <select class="form-select" id="quickClientSelect" name="clientID">
                                <option value="">Select Client</option>
                                <?php
                                // Fetch clients using Client class
                                $clients = Client::clients(array(), false, $DBConn);
                                if ($clients) {
                                    foreach ($clients as $client) {
                                        echo "<option value='{$client->clientID}'>{$client->clientName}</option>";
                                    }
                                }
                                ?>
                            </select>
                            <small class="text-muted">Link this prospect to an existing client to avoid duplication</small>
                        </div>

                        <div class="col-12">
                            <label for="quickNotes" class="form-label">Initial Notes</label>
                            <textarea class="form-control" id="quickNotes" name="initialNotes" rows="3" placeholder="Any additional information about this prospect..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="quickAddSubmitBtn">
                        <i class="fas fa-save me-1"></i>Add Prospect
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const quickAddForm = document.getElementById('quickAddProspectForm');
    const quickAddModal = new bootstrap.Modal(document.getElementById('quickAddProspectModal'));
    const submitBtn = document.getElementById('quickAddSubmitBtn');

    if (quickAddForm) {
        quickAddForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate required fields
            if (!quickAddForm.checkValidity()) {
                quickAddForm.classList.add('was-validated');
                return;
            }

            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Adding...';

            // Prepare form data
            const formData = new FormData(quickAddForm);
            formData.append('action', 'create');

            // Submit via AJAX
            fetch('<?= $base ?>php/scripts/sales/manage_prospect_advanced.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Prospect added successfully',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        quickAddModal.hide();
                        window.location.reload();
                    });
                } else {
                    // Hide any page loaders
                    const loaders = document.querySelectorAll('.page-loader, #page-loader, .loader-overlay, .spinner-border');
                    loaders.forEach(loader => {
                        loader.style.display = 'none';
                        loader.remove();
                    });

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to add prospect'
                    }).then(() => {
                        // Re-enable submit button after user closes the alert
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="ri-add-line me-1"></i>Add Prospect';
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);

                // Hide any page loaders
                const loaders = document.querySelectorAll('.page-loader, #page-loader, .loader-overlay, .spinner-border');
                loaders.forEach(loader => {
                    loader.style.display = 'none';
                    loader.remove();
                });

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while adding the prospect'
                }).then(() => {
                    // Re-enable submit button after user closes the alert
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="ri-add-line me-1"></i>Add Prospect';
                });
            });
        });
    }

    // Reset form when modal is closed
    document.getElementById('quickAddProspectModal').addEventListener('hidden.bs.modal', function() {
        quickAddForm.reset();
        quickAddForm.classList.remove('was-validated');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save me-1"></i>Add Prospect';
    });

    // Toggle client selection dropdown
    const isClientCheckbox = document.getElementById('quickIsClient');
    const clientSelectContainer = document.getElementById('quickClientSelectContainer');

    if (isClientCheckbox && clientSelectContainer) {
        isClientCheckbox.addEventListener('change', function() {
            if (this.checked) {
                clientSelectContainer.classList.remove('d-none');
            } else {
                clientSelectContainer.classList.add('d-none');
                document.getElementById('quickClientSelect').value = '';
            }
        });
    }

    // Reset form when modal is closed
    const quickAddModalElement = document.getElementById('quickAddProspectModal');
    quickAddModalElement.addEventListener('hidden.bs.modal', function() {
        quickAddForm.reset();
        quickAddForm.classList.remove('was-validated');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="ri-add-line me-1"></i>Add Prospect';
        if (clientSelectContainer) {
            clientSelectContainer.classList.add('d-none');
        }
    });
});
</script>

<style>
#quickAddProspectModal .form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
}

#quickAddProspectModal .text-danger {
    font-size: 0.875rem;
}

#quickAddProspectModal .alert-info {
    border-left: 4px solid #0dcaf0;
}
</style>
