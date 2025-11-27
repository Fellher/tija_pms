<!-- Add/Edit License Modal -->
<div class="modal fade" id="licenseModal" tabindex="-1" aria-labelledby="licenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="licenseModalLabel">Add License</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="licenseForm" onsubmit="saveLicense(event)">
                <div class="modal-body">
                    <input type="hidden" id="licenseID" name="licenseID">
                    <input type="hidden" name="employeeID" value="<?= $employeeID ?>">

                    <div class="row">
                        <!-- License Details -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">License Details</h6>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label for="licenseName" class="form-label">License Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="licenseName" name="licenseName"
                                   placeholder="e.g., Driving License, Medical Practice License" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="licenseCategory" class="form-label">Category</label>
                            <select class="form-select" id="licenseCategory" name="licenseCategory">
                                <option value="">Select Category</option>
                                <option value="Driving">Driving</option>
                                <option value="Professional Practice">Professional Practice</option>
                                <option value="Business">Business</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="licenseNumber" class="form-label">License Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="licenseNumber" name="licenseNumber" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="issuingAuthority" class="form-label">Issuing Authority <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="issuingAuthority" name="issuingAuthority"
                                   placeholder="e.g., NTSA, Medical Board" required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="issuingCountry" class="form-label">Issuing Country</label>
                            <input type="text" class="form-control" id="issuingCountry" name="issuingCountry" value="Kenya">
                        </div>

                        <!-- Dates -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">Validity Period</h6>
                        </div>

                        <div class="col-md-5 mb-3">
                            <label for="licIssueDate" class="form-label">Issue Date</label>
                            <input type="text" class="form-control component-datepicker" id="licIssueDate" name="issueDate" placeholder="Select date">
                        </div>

                        <div class="col-md-5 mb-3">
                            <label for="licExpiryDate" class="form-label">Expiry Date</label>
                            <input type="text" class="form-control component-datepicker" id="licExpiryDate" name="expiryDate" placeholder="Select date">
                        </div>

                        <div class="col-md-2 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="licDoesNotExpire" name="doesNotExpire" value="Y">
                                <label class="form-check-label" for="licDoesNotExpire">
                                    No Expiry
                                </label>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">Status & Restrictions</h6>
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isActiveLicense" name="isActive" value="Y" checked>
                                <label class="form-check-label" for="isActiveLicense">
                                    <strong>License is Active</strong>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="restrictions" class="form-label">Restrictions</label>
                            <textarea class="form-control" id="restrictions" name="restrictions" rows="2"
                                      placeholder="Any restrictions or conditions on this license"></textarea>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="licNotes" class="form-label">Notes</label>
                            <textarea class="form-control" id="licNotes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Save License
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

