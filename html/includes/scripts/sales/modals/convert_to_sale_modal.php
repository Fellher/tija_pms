<?php
/**
 * Convert Prospect to Sale Modal
 * Modal interface for converting qualified prospects to sales opportunities
 */
?>

<!-- Convert to Sale Modal -->
<div class="modal fade" id="convertToSaleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt me-2"></i>Convert Prospect to Sale
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="convertToSaleForm">
                    <input type="hidden" name="action" value="convertToSale">
                    <input type="hidden" name="salesProspectID" id="convertProspectID">
                    <input type="hidden" name="entityID" id="convertEntityID">

                    <!-- Prospect Summary -->
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Prospect Summary</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Prospect:</strong> <span id="summaryProspectName"></span>
                            </div>
                            <div class="col-md-6">
                                <strong>Case:</strong> <span id="summaryCaseName"></span>
                            </div>
                            <div class="col-md-6 mt-2">
                                <strong>Estimated Value:</strong> <span id="summaryValue"></span>
                            </div>
                            <div class="col-md-6 mt-2">
                                <strong>Status:</strong> <span class="badge bg-success" id="summaryStatus"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Sales Stage Selection -->
                    <div class="mb-3">
                        <label class="form-label">
                            Sales Stage <span class="text-danger">*</span>
                        </label>
                        <div class="row" id="salesStageOptions">
                            <!-- Populated dynamically with radio button cards -->
                            <div class="col-12 text-center py-3">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading stages...</span>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted">Select the initial stage for this sales opportunity</small>
                    </div>

                    <!-- Sales Details -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="saleCaseName" class="form-label">
                                Sale Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="saleCaseName" name="saleCaseName" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="saleValue" class="form-label">
                                Deal Value (KES) <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="saleValue" name="saleValue" step="0.01" min="0" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="probability" class="form-label">
                                Probability (%)
                            </label>
                            <input type="number" class="form-control" id="probability" name="probability" min="0" max="100" value="50">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="expectedCloseDate" class="form-label">
                                Expected Close Date
                            </label>
                            <input type="date" class="form-control" id="expectedCloseDate" name="expectedCloseDate">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="saleOwnerID" class="form-label">
                                Sales Owner
                            </label>
                            <select class="form-select" id="saleOwnerID" name="saleOwnerID">
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

                        <div class="col-md-4 mb-3">
                            <label for="assignedTeamID" class="form-label">
                                Assigned Team
                            </label>
                            <select class="form-select" id="assignedTeamID" name="assignedTeamID">
                                <option value="">Select Team</option>
                                <?php
                                $teams = $DBConn->retrieve_db_table_rows('tija_prospect_teams', array('prospectTeamID', 'prospectTeamName'), array());
                                if ($teams) {
                                    foreach ($teams as $team) {
                                        echo "<option value='{$team->prospectTeamID}'>{$team->prospectTeamName}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="mb-3">
                        <label for="saleDescription" class="form-label">
                            Sale Description
                        </label>
                        <textarea class="form-control" id="saleDescription" name="saleDescription" rows="3"></textarea>
                    </div>

                    <!-- Confirmation -->
                    <div class="alert alert-warning">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirmConversion" required>
                            <label class="form-check-label" for="confirmConversion">
                                <strong>I understand this action will deactivate the prospect and create a new sales opportunity</strong>
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-success" id="confirmConvertBtn">
                    <i class="fas fa-check me-1"></i>Convert to Sale
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const convertModal = document.getElementById('convertToSaleModal');
    const convertForm = document.getElementById('convertToSaleForm');
    const confirmBtn = document.getElementById('confirmConvertBtn');
    const expectedCloseDateInput = document.getElementById('expectedCloseDate');

    // Initialize Flatpickr for expected close date
    if (typeof flatpickr !== 'undefined' && expectedCloseDateInput) {
        flatpickr(expectedCloseDateInput, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'F j, Y',
            allowInput: true,
            minDate: 'today'
        });
    }

    // Function to load prospect data into modal
    window.loadProspectForConversion = function(prospectData) {
        // Set hidden fields
        document.getElementById('convertProspectID').value = prospectData.salesProspectID;
        document.getElementById('convertEntityID').value = prospectData.entityID || '';

        // Set summary fields
        document.getElementById('summaryProspectName').textContent = prospectData.salesProspectName || '-';
        document.getElementById('summaryCaseName').textContent = prospectData.prospectCaseName || '-';
        document.getElementById('summaryValue').textContent = 'KES ' + (parseFloat(prospectData.estimatedValue || 0).toLocaleString());
        document.getElementById('summaryStatus').textContent = prospectData.leadQualificationStatus || 'Qualified';

        // Pre-fill form fields
        document.getElementById('saleCaseName').value = prospectData.salesProspectName || '';
        document.getElementById('saleValue').value = prospectData.estimatedValue || '';
        document.getElementById('probability').value = prospectData.probability || 50;
        document.getElementById('expectedCloseDate').value = prospectData.expectedCloseDate || '';
        document.getElementById('saleDescription').value = prospectData.prospectCaseName || '';

        if (prospectData.ownerID) {
            document.getElementById('saleOwnerID').value = prospectData.ownerID;
        }
        if (prospectData.assignedTeamID) {
            document.getElementById('assignedTeamID').value = prospectData.assignedTeamID;
        }

        // Load sales stages
        loadSalesStages(prospectData.entityID);

        // Show modal
        const modal = new bootstrap.Modal(convertModal);
        modal.show();
    };

    // Load sales stages from API
  /*  function loadSalesStages(entityID) {
        const formData = new FormData();
        formData.append('action', 'getSalesStages');
        if (entityID) {
            formData.append('entityID', entityID);
        })

        const salesStageContainer = document.getElementById('salesStageOptions');

        fetch('<?= "{$base}php/scripts/sales/convert_prospect_to_sale.php" ?>', {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data && data.data.length > 0) {
                let stagesHTML = '';
                data.data.forEach(stage => {
                    const option = document.createElement('option');
                    option.value = stage.saleStatusLevelID;

                    // Display format: "Stage Name - Description"
                    const stageName = stage.statusLevel || 'Unknown Stage';
                    const stageDesc = stage.StatusLevelDescription || '';

                    if (stageDesc) {
                        option.textContent = `${stageName} - ${stageDesc}`;
                    } else {
                        option.textContent = stageName;
                    }

                    option.dataset.probability = stage.levelPercentage || 50;
                    salesStageSelect.appendChild(option);
                });
            } else {
                salesStageContainer.innerHTML = '<div class="col-12"><div class="alert alert-warning">No sales stages available. Please contact your administrator.</div></div>';
            }
        })
        .catch(error => {
            console.error('Error loading sales stages:', error);
            salesStageContainer.innerHTML = '<div class="col-12"><div class="alert alert-danger">Error loading sales stages. Please try again.</div></div>';
        });
    }*/
   // Load sales stages from API
    function loadSalesStages(entityID) {
        const formData = new FormData();
        formData.append('action', 'getSalesStages');
        if (entityID) {
            formData.append('entityID', entityID);
        }

        const salesStageContainer = document.getElementById('salesStageOptions');

        fetch('<?= "{$base}php/scripts/sales/convert_prospect_to_sale.php" ?>', {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data && data.data.length > 0) {
                let stagesHTML = '';
                let displayedCount = 0;

                data.data.forEach((stage, index) => {
                    displayedCount++;
                    const stageName = stage.statusLevel || 'Unknown Stage';
                    const stageDesc = stage.StatusLevelDescription || 'No description available';
                    const probability = stage.levelPercentage || 50;
                    const stageID = stage.saleStatusLevelID;

                    stagesHTML += `
                        <div class="col-sm-12 col-lg-3 mb-2">
                            <input class="btn-check" type="radio" name="saleStatusLevelID"
                                id="convertStatusLevel${index}"
                                value="${stageID}"
                                data-probability="${probability}"
                                ${displayedCount === 1 ? 'required' : ''}>
                            <label class="form-check-label btn btn-outline-primary w-100 text-start"
                                for="convertStatusLevel${index}">
                                <h6 class="mb-1">${stageName}</h6>
                                <small class="text-muted">${stageDesc}</small>
                                <div class="mt-1">
                                    <span class="badge bg-primary-transparent">${probability}% probability</span>
                                </div>
                            </label>
                        </div>
                    `;
                });

                salesStageContainer.innerHTML = stagesHTML;

                // Add event listeners to update probability when stage is selected
                document.querySelectorAll('input[name="saleStatusLevelID"]').forEach(radio => {
                    radio.addEventListener('change', function() {
                        if (this.checked && this.dataset.probability) {
                            document.getElementById('probability').value = this.dataset.probability;
                        }
                    });
                });
            } else {
                salesStageContainer.innerHTML = '<div class="col-12"><div class="alert alert-warning">No sales stages available. Please contact your administrator.</div></div>';
            }
        })
        .catch(error => {
            console.error('Error loading sales stages:', error);
            salesStageContainer.innerHTML = '<div class="col-12"><div class="alert alert-danger">Error loading sales stages. Please try again.</div></div>';
        });
    }




    // Handle conversion
    confirmBtn.addEventListener('click', function() {
        if (!convertForm.checkValidity()) {
            convertForm.classList.add('was-validated');
            return;
        }

        const confirmCheckbox = document.getElementById('confirmConversion');
        if (!confirmCheckbox.checked) {
            Swal.fire({
                icon: 'warning',
                title: 'Confirmation Required',
                text: 'Please confirm that you understand this action will deactivate the prospect.'
            });
            return;
        }

        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Converting...';

        const formData = new FormData(convertForm);

        fetch('<?= "{$base}php/scripts/sales/convert_prospect_to_sale.php" ?>', {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = '<?= "{$base}html/?s=user&ss=sales&p=sale_details&salesCaseID=" ?>' + data.data.salesCaseID;
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message
                });
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fas fa-check me-1"></i>Convert to Sale';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while converting the prospect.'
            });
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="fas fa-check me-1"></i>Convert to Sale';
        });
    });

    // Reset form when modal is closed
    convertModal.addEventListener('hidden.bs.modal', function() {
        convertForm.reset();
        convertForm.classList.remove('was-validated');
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = '<i class="fas fa-check me-1"></i>Convert to Sale';
    });
});
</script>

<style>
#convertToSaleModal .alert-info {
    background-color: #e7f3ff;
    border-left: 4px solid #0d6efd;
}

#convertToSaleModal .alert-warning {
    background-color: #fff3cd;
    border-left: 4px solid #ffc107;
}

#convertToSaleModal .form-label {
    font-weight: 500;
}

/* Sales stage radio button cards - selected state styling */
#convertToSaleModal .btn-check:checked + .btn-outline-primary {
    color: #fff !important;
    background-color: #0d6efd;
    border-color: #0d6efd;
}

#convertToSaleModal .btn-check:checked + .btn-outline-primary h6,
#convertToSaleModal .btn-check:checked + .btn-outline-primary small,
#convertToSaleModal .btn-check:checked + .btn-outline-primary .text-muted {
    color: #fff !important;
}

#convertToSaleModal .btn-check:checked + .btn-outline-primary .badge {
    background-color: rgba(255, 255, 255, 0.2) !important;
    color: #fff !important;
}
</style>
