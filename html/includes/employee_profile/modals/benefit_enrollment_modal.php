<!-- Add/Edit Benefit Enrollment Modal -->
<div class="modal fade" id="benefitModal" tabindex="-1" aria-labelledby="benefitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="benefitModalLabel">Enroll in Benefit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="benefitForm" onsubmit="saveBenefitEnrollment(event)">
                <div class="modal-body">
                    <input type="hidden" id="benefitID" name="benefitID">
                    <input type="hidden" name="employeeID" value="<?= $employeeID ?>">

                    <div class="row">
                        <!-- Benefit Selection -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Benefit Selection</h6>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="benefitTypeID" class="form-label">Select Benefit <span class="text-danger">*</span></label>
                            <select class="form-select" id="benefitTypeID" name="benefitTypeID" required onchange="loadBenefitDefaults(this.value)">
                                <option value="">-- Select Benefit --</option>
                                <?php
                                // Get benefit types using Employee class method
                                $benefitTypes = Employee::benefit_types(
                                    array('isActive' => 'Y', 'Suspended' => 'N'),
                                    false,
                                    $DBConn
                                );
                                if ($benefitTypes) {
                                    foreach ($benefitTypes as $bt) {
                                        echo "<option value='{$bt->benefitTypeID}' data-provider='{$bt->providerName}' data-category='{$bt->benefitCategory}'>";
                                        echo htmlspecialchars($bt->benefitName);
                                        if (!empty($bt->providerName)) {
                                            echo " - " . htmlspecialchars($bt->providerName);
                                        }
                                        echo "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Enrollment Dates -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">Enrollment & Coverage Period</h6>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="enrollmentDate" class="form-label">Enrollment Date <span class="text-danger">*</span></label>
                            <input type="text" class="form-control  component-datepicker" id="enrollmentDate" name="enrollmentDate" placeholder="Select date" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="effectiveDate" class="form-label">Effective Date <span class="text-danger">*</span></label>
                            <input type="text" class="form-control  component-datepicker" id="benefitEffectiveDate" name="effectiveDate" placeholder="Coverage starts" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="endDate" class="form-label">End Date</label>
                            <input type="text" class="form-control  component-datepicker" id="benefitEndDate" name="endDate" placeholder="Leave empty if ongoing">
                        </div>

                        <!-- Coverage Details -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">Coverage Details</h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="coverageLevel" class="form-label">Coverage Level</label>
                            <select class="form-select" id="coverageLevel" name="coverageLevel">
                                <option value="individual">Individual Only</option>
                                <option value="spouse">Individual + Spouse</option>
                                <option value="family">Family (Spouse + Children)</option>
                                <option value="children">Individual + Children</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="dependentsCovered" class="form-label">Number of Dependents Covered</label>
                            <input type="number" class="form-control" id="dependentsCovered" name="dependentsCovered" min="0" value="0">
                        </div>

                        <!-- Policy Details -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">Policy Information</h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="policyNumber" class="form-label">Policy Number</label>
                            <input type="text" class="form-control" id="policyNumber" name="policyNumber">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="memberNumber" class="form-label">Member Number</label>
                            <input type="text" class="form-control" id="memberNumber" name="memberNumber">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="providerName" class="form-label">Provider Name</label>
                            <input type="text" class="form-control" id="providerName" name="providerName">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="providerContact" class="form-label">Provider Contact</label>
                            <input type="text" class="form-control" id="providerContact" name="providerContact">
                        </div>

                        <!-- Cost & Contributions -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">Contributions</h6>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="employerContribution" class="form-label">Employer Contribution</label>
                            <div class="input-group">
                                <span class="input-group-text">KES</span>
                                <input type="number" class="form-control" id="employerContribution" name="employerContribution"
                                       min="0" step="0.01" value="0">
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="employeeContribution" class="form-label">Employee Contribution</label>
                            <div class="input-group">
                                <span class="input-group-text">KES</span>
                                <input type="number" class="form-control" id="employeeContribution" name="employeeContribution"
                                       min="0" step="0.01" value="0">
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="totalPremium" class="form-label">Total Premium</label>
                            <div class="input-group">
                                <span class="input-group-text">KES</span>
                                <input type="number" class="form-control" id="totalPremium" name="totalPremium"
                                       min="0" step="0.01" value="0" readonly>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="contributionFrequency" class="form-label">Contribution Frequency</label>
                            <select class="form-select" id="contributionFrequency" name="contributionFrequency">
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="annually">Annually</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="isActiveBenefit" name="isActive" value="Y" checked>
                                <label class="form-check-label" for="isActiveBenefit">
                                    <strong>Enrollment is Active</strong>
                                </label>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="benefitNotes" class="form-label">Notes</label>
                            <textarea class="form-control" id="benefitNotes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Save Enrollment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-calculate total premium
document.addEventListener('DOMContentLoaded', function() {
    const employerContr = document.getElementById('employerContribution');
    const employeeContr = document.getElementById('employeeContribution');
    const totalPremium = document.getElementById('totalPremium');

    function calculateTotal() {
        const employer = parseFloat(employerContr.value) || 0;
        const employee = parseFloat(employeeContr.value) || 0;
        totalPremium.value = (employer + employee).toFixed(2);
    }

    if (employerContr) employerContr.addEventListener('input', calculateTotal);
    if (employeeContr) employeeContr.addEventListener('input', calculateTotal);
});

function loadBenefitDefaults(benefitTypeID) {
    if (!benefitTypeID) return;

    // Get selected option
    const select = document.getElementById('benefitTypeID');
    const option = select.options[select.selectedIndex];

    // Set provider name if available
    const provider = option.getAttribute('data-provider');
    if (provider && provider !== 'null') {
        document.getElementById('providerName').value = provider;
    }
}
</script>

