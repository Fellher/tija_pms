<!-- Add/Edit Certification Modal -->
<div class="modal fade" id="certificationModal" tabindex="-1" aria-labelledby="certificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="certificationModalLabel">Add Certification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="certificationForm" onsubmit="saveCertification(event)">
                <div class="modal-body">
                    <input type="hidden" id="certificationID" name="certificationID">
                    <input type="hidden" name="employeeID" value="<?= $employeeID ?>">

                    <div class="row">
                        <!-- Certification Details -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Certification Details</h6>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="certificationName" class="form-label">Certification Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="certName" name="certificationName"
                                   placeholder="e.g., PMP, AWS Certified, CPA" required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="issuingOrganization" class="form-label">Issuing Organization <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="issuingOrganization" name="issuingOrganization"
                                   placeholder="e.g., PMI, Amazon Web Services, ICPAK" required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="certNumber" class="form-label">Certification Number</label>
                            <input type="text" class="form-control" id="certNumber" name="certificationNumber"
                                   placeholder="Certificate or credential number">
                        </div>

                        <!-- Dates -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">Validity Period</h6>
                        </div>

                        <div class="col-md-5 mb-3">
                            <label for="certIssueDate" class="form-label">Issue Date</label>
                            <input type="text" class="form-control component-datepicker" id="certIssueDate" name="issueDate" placeholder="Select date">
                        </div>

                        <div class="col-md-5 mb-3">
                            <label for="certExpiryDate" class="form-label">Expiry Date</label>
                            <input type="text" class="form-control component-datepicker" id="certExpiryDate" name="expiryDate" placeholder="Select date">
                        </div>

                        <div class="col-md-2 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="certDoesNotExpire" name="doesNotExpire" value="Y">
                                <label class="form-check-label" for="certDoesNotExpire">
                                    No Expiry
                                </label>
                            </div>
                        </div>

                        <!-- Verification -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">Verification (Optional)</h6>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="verificationURL" class="form-label">Verification URL</label>
                            <input type="url" class="form-control" id="verificationURL" name="verificationURL"
                                   placeholder="https://verify.example.com/...">
                            <small class="text-muted">Link to verify the certification online</small>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="credentialID" class="form-label">Credential ID</label>
                            <input type="text" class="form-control" id="credentialID" name="credentialID"
                                   placeholder="Public credential identifier">
                        </div>

                        <div class="col-12 mb-3">
                            <label for="certNotes" class="form-label">Notes</label>
                            <textarea class="form-control" id="certNotes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Save Certification
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

