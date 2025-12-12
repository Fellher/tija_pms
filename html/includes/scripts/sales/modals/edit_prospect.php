<!-- Edit Prospect Modal -->
<div class="modal fade" id="editProspectModal" tabindex="-1" aria-labelledby="editProspectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editProspectModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Prospect
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editProspectForm">
                <input type="hidden" id="editProspectID" name="salesProspectID">
                <input type="hidden" name="action" value="update">

                <div class="modal-body">
                    <!-- Tabbed Interface for Organization -->
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#editBasicInfo">Basic Information</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#editClassification">Classification</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#editQualification">Qualification</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#editAssignment">Assignment</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#editAdditional">Additional Details</a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Basic Information Tab -->
                        <div class="tab-pane fade show active" id="editBasicInfo">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="editProspectName" class="form-label">Prospect Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="editProspectName" name="salesProspectName" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="editProspectEmail" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="editProspectEmail" name="prospectEmail" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="editProspectPhone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="editProspectPhone" name="prospectPhone">
                                </div>

                                <div class="col-md-6">
                                    <label for="editProspectWebsite" class="form-label">Website</label>
                                    <input type="text" class="form-control" id="editProspectWebsite" name="prospectWebsite" placeholder="https://" pattern="https?://.+">
                                </div>

                                <div class="col-12">
                                    <label for="editCaseName" class="form-label">Case/Opportunity Name</label>
                                    <input type="text" class="form-control" id="editCaseName" name="prospectCaseName">
                                </div>

                                <div class="col-12">
                                    <label for="editAddress" class="form-label">Address</label>
                                    <textarea class="form-control" id="editAddress" name="address" rows="2"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Classification Tab -->
                        <div class="tab-pane fade" id="editClassification">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="editBusinessUnit" class="form-label">Business Unit <span class="text-danger">*</span></label>
                                    <select class="form-select" id="editBusinessUnit" name="businessUnitID" required>
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
                                    <label for="editLeadSource" class="form-label">Lead Source <span class="text-danger">*</span></label>
                                    <select class="form-select" id="editLeadSource" name="leadSourceID" required>
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
                                    <label for="editIndustry" class="form-label">Industry</label>
                                    <select class="form-select" id="editIndustry" name="industryID">
                                        <option value="">Select Industry</option>
                                        <?php
                                        $allIndustries = Sales::prospect_industries(array('isActive' => 'Y'), false, $DBConn);
                                        if ($allIndustries) {
                                            foreach ($allIndustries as $industry) {
                                                echo "<option value='{$industry->industryID}'>{$industry->industryName}</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="editCompanySize" class="form-label">Company Size</label>
                                    <select class="form-select" id="editCompanySize" name="companySize">
                                        <option value="">Select Size</option>
                                        <option value="small">Small (1-50 employees)</option>
                                        <option value="medium">Medium (51-250 employees)</option>
                                        <option value="large">Large (251-1000 employees)</option>
                                        <option value="enterprise">Enterprise (1000+ employees)</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="editOrganization" class="form-label">Organization</label>
                                    <select class="form-select" id="editOrganization" name="orgDataID">
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
                                    <label for="editSourceDetails" class="form-label">Source Details</label>
                                    <input type="text" class="form-control" id="editSourceDetails" name="sourceDetails" placeholder="Additional source information">
                                </div>
                            </div>
                        </div>

                        <!-- Qualification Tab -->
                        <div class="tab-pane fade" id="editQualification">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="editStatus" class="form-label">Status</label>
                                    <select class="form-select" id="editStatus" name="salesProspectStatus">
                                        <option value="open">Open</option>
                                        <option value="closed">Closed</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="editQualificationStatus" class="form-label">Qualification Status</label>
                                    <select class="form-select" id="editQualificationStatus" name="leadQualificationStatus">
                                        <option value="unqualified">Unqualified</option>
                                        <option value="cold">Cold</option>
                                        <option value="warm">Warm</option>
                                        <option value="hot">Hot</option>
                                        <option value="qualified">Qualified</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="editEstimatedValue" class="form-label">Estimated Value (KES)</label>
                                    <input type="number" class="form-control" id="editEstimatedValue" name="estimatedValue" min="0" step="0.01">
                                </div>

                                <div class="col-md-6">
                                    <label for="editProbability" class="form-label">Probability (%)</label>
                                    <input type="number" class="form-control" id="editProbability" name="probability" min="0" max="100">
                                </div>

                                <div class="col-md-6">
                                    <label for="editExpectedCloseDate" class="form-label">Expected Close Date</label>
                                    <input type="date" class="form-control" id="editExpectedCloseDate" name="expectedCloseDate">
                                </div>

                                <div class="col-md-6">
                                    <label for="editNextFollowUpDate" class="form-label">Next Follow-up Date</label>
                                    <input type="date" class="form-control" id="editNextFollowUpDate" name="nextFollowUpDate">
                                </div>

                                <div class="col-12">
                                    <h6 class="fw-semibold mb-3">BANT Qualification</h6>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="editBudgetConfirmed" name="budgetConfirmed" value="Y">
                                        <label class="form-check-label" for="editBudgetConfirmed">
                                            Budget Confirmed
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="editDecisionMaker" name="decisionMakerIdentified" value="Y">
                                        <label class="form-check-label" for="editDecisionMaker">
                                            Decision Maker Identified
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="editNeedIdentified" name="needIdentified" value="Y">
                                        <label class="form-check-label" for="editNeedIdentified">
                                            Need Identified
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="editTimelineDefined" name="timelineDefined" value="Y">
                                        <label class="form-check-label" for="editTimelineDefined">
                                            Timeline Defined
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Assignment Tab -->
                        <div class="tab-pane fade" id="editAssignment">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="editOwner" class="form-label">Assign To</label>
                                    <select class="form-select" id="editOwner" name="entityID">
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

                                <div class="col-md-6">
                                    <label for="editTeam" class="form-label">Assigned Team</label>
                                    <select class="form-select" id="editTeam" name="assignedTeamID">
                                        <option value="">Select Team</option>
                                        <?php
                                        $allTeams = Sales::prospect_teams(array('isActive' => 'Y'), false, $DBConn);
                                        if ($allTeams) {
                                            foreach ($allTeams as $team) {
                                                echo "<option value='{$team->teamID}'>{$team->teamName}</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="editTerritory" class="form-label">Territory</label>
                                    <select class="form-select" id="editTerritory" name="territoryID">
                                        <option value="">Select Territory</option>
                                        <?php
                                        $allTerritories = Sales::prospect_territories(array('isActive' => 'Y'), false, $DBConn);
                                        if ($allTerritories) {
                                            foreach ($allTerritories as $territory) {
                                                echo "<option value='{$territory->territoryID}'>{$territory->territoryName}</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Details Tab -->
                        <div class="tab-pane fade" id="editAdditional">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="editTags" class="form-label">Tags</label>
                                    <input type="text" class="form-control" id="editTags" name="tags" placeholder="Comma-separated tags (e.g., VIP, Referral, High-Priority)">
                                    <small class="text-muted">Separate multiple tags with commas</small>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Client Status</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="editIsClient" name="isClient" value="Y">
                                        <label class="form-check-label" for="editIsClient">
                                            This prospect is already a client
                                        </label>
                                    </div>
                                </div>

                                <div class="col-12" id="editClientSelectContainer" style="display: none;">
                                    <label for="editClientID" class="form-label">Select Client</label>
                                    <select class="form-select" id="editClientID" name="clientID">
                                        <option value="">Select Client</option>
                                        <!-- Will be populated dynamically -->
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="editProspectSubmitBtn">
                        <i class="fas fa-save me-1"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editProspectModal');
    const editForm = document.getElementById('editProspectForm');
    const submitBtn = document.getElementById('editProspectSubmitBtn');
    const isClientCheckbox = document.getElementById('editIsClient');
    const clientSelectContainer = document.getElementById('editClientSelectContainer');

    // Toggle client select visibility
    if (isClientCheckbox) {
        isClientCheckbox.addEventListener('change', function() {
            clientSelectContainer.style.display = this.checked ? 'block' : 'none';
        });
    }

    // Function to load prospect data into form
    window.loadProspectForEdit = function(prospectID) {
        // Fetch prospect data
        fetch('<?= $base ?>php/scripts/sales/manage_prospect_advanced.php?action=get&salesProspectID=' + prospectID)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.prospect) {
                    const prospect = data.prospect;

                    // Populate form fields
                    document.getElementById('editProspectID').value = prospect.salesProspectID;
                    document.getElementById('editProspectName').value = prospect.salesProspectName || '';
                    document.getElementById('editProspectEmail').value = prospect.prospectEmail || '';
                    document.getElementById('editProspectPhone').value = prospect.prospectPhone || '';
                    document.getElementById('editProspectWebsite').value = prospect.prospectWebsite || '';
                    document.getElementById('editCaseName').value = prospect.prospectCaseName || '';
                    document.getElementById('editAddress').value = prospect.address || '';

                    // Classification
                    document.getElementById('editBusinessUnit').value = prospect.businessUnitID || '';
                    document.getElementById('editLeadSource').value = prospect.leadSourceID || '';
                    document.getElementById('editIndustry').value = prospect.industryID || '';
                    document.getElementById('editCompanySize').value = prospect.companySize || '';
                    document.getElementById('editOrganization').value = prospect.orgDataID || '';
                    document.getElementById('editSourceDetails').value = prospect.sourceDetails || '';

                    // Qualification
                    document.getElementById('editStatus').value = prospect.salesProspectStatus || 'open';
                    document.getElementById('editQualificationStatus').value = prospect.leadQualificationStatus || 'unqualified';
                    document.getElementById('editEstimatedValue').value = prospect.estimatedValue || '';
                    document.getElementById('editProbability').value = prospect.probability || '';
                    document.getElementById('editExpectedCloseDate').value = prospect.expectedCloseDate || '';
                    document.getElementById('editNextFollowUpDate').value = prospect.nextFollowUpDate || '';

                    // BANT
                    document.getElementById('editBudgetConfirmed').checked = prospect.budgetConfirmed === 'Y';
                    document.getElementById('editDecisionMaker').checked = prospect.decisionMakerIdentified === 'Y';
                    document.getElementById('editNeedIdentified').checked = prospect.needIdentified === 'Y';
                    document.getElementById('editTimelineDefined').checked = prospect.timelineDefined === 'Y';

                    // Assignment
                    document.getElementById('editOwner').value = prospect.entityID || '';
                    document.getElementById('editTeam').value = prospect.assignedTeamID || '';
                    document.getElementById('editTerritory').value = prospect.territoryID || '';

                    // Additional
                    if (prospect.tags) {
                        const tags = typeof prospect.tags === 'string' ? JSON.parse(prospect.tags) : prospect.tags;
                        document.getElementById('editTags').value = Array.isArray(tags) ? tags.join(', ') : '';
                    }
                    document.getElementById('editIsClient').checked = prospect.isClient === 'Y';
                    if (prospect.isClient === 'Y') {
                        clientSelectContainer.style.display = 'block';
                        document.getElementById('editClientID').value = prospect.clientID || '';
                    }

                    // Show modal
                    const modalInstance = new bootstrap.Modal(editModal);
                    modalInstance.show();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to load prospect data'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while loading prospect data'
                });
            });
    };

    // Form submission
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate required fields
            if (!editForm.checkValidity()) {
                editForm.classList.add('was-validated');
                return;
            }

            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';

            // Prepare form data
            const formData = new FormData(editForm);

            // Handle tags (convert to JSON array)
            const tagsInput = document.getElementById('editTags').value;
            if (tagsInput) {
                const tagsArray = tagsInput.split(',').map(tag => tag.trim()).filter(tag => tag);
                formData.set('tags', JSON.stringify(tagsArray));
            }

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
                        text: 'Prospect updated successfully',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        bootstrap.Modal.getInstance(editModal).hide();
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to update prospect'
                    });

                    // Re-enable submit button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save me-1"></i>Save Changes';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating the prospect'
                });

                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save me-1"></i>Save Changes';
            });
        });
    }

    // Initialize Flatpickr for date inputs
    if (typeof flatpickr !== 'undefined') {
        const expectedCloseDateInput = document.getElementById('editExpectedCloseDate');
        const nextFollowUpDateInput = document.getElementById('editNextFollowUpDate');

        if (expectedCloseDateInput) {
            flatpickr(expectedCloseDateInput, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'F j, Y',
                allowInput: true
            });
        }

        if (nextFollowUpDateInput) {
            flatpickr(nextFollowUpDateInput, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'F j, Y',
                allowInput: true,
                minDate: 'today'
            });
        }
    }

    // Reset form when modal is closed
    editModal.addEventListener('hidden.bs.modal', function() {
        editForm.reset();
        editForm.classList.remove('was-validated');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save me-1"></i>Save Changes';
        clientSelectContainer.style.display = 'none';
    });
});
</script>

<style>
#editProspectModal .nav-tabs .nav-link {
    color: #6c757d;
}

#editProspectModal .nav-tabs .nav-link.active {
    color: #0d6efd;
    font-weight: 500;
}

#editProspectModal .form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
}

#editProspectModal .text-danger {
    font-size: 0.875rem;
}
</style>
