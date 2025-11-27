<!-- Add/Edit Address Modal -->
<div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addressModalLabel">Add Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addressForm" onsubmit="saveAddress(event)">
                <div class="modal-body">
                    <input type="hidden" id="addressID" name="addressID">
                    <input type="hidden" name="employeeID" value="<?= $employeeID ?>">

                    <div class="row">
                        <!-- Address Type and Priority -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Address Type</h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="addressType" class="form-label">Address Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="addressType" name="addressType" required>
                                <option value="">Select Type</option>
                                <option value="home">Home Address</option>
                                <option value="work">Work Address</option>
                                <option value="postal">Postal Address</option>
                                <option value="permanent">Permanent Address</option>
                                <option value="temporary">Temporary Address</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="isPrimary" name="isPrimary" value="Y">
                                <label class="form-check-label" for="isPrimary">
                                    <strong>Set as Primary Address</strong>
                                </label>
                                <small class="d-block text-muted">This will be the default address for this employee</small>
                            </div>
                        </div>

                        <!-- Address Details -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Address Details</h6>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="addressLine1" class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="addressLine1" name="addressLine1"
                                   placeholder="Street address, P.O. Box, etc." required>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="addressLine2" class="form-label">Address Line 2</label>
                            <textarea class="form-control" id="addressLine2" name="addressLine2" rows="2"
                                      placeholder="Apartment, suite, unit, building, floor, etc. (additional address details)"></textarea>
                        </div>

                        <!-- Location Details -->
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">City/Town <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="city" name="city" placeholder="e.g., Nairobi" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="county" class="form-label">County/State</label>
                            <input type="text" class="form-control" id="county" name="county" placeholder="e.g., Nairobi">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="postalCode" class="form-label">Postal Code</label>
                            <input type="text" class="form-control" id="postalCode" name="postalCode" placeholder="e.g., 00100">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="country" name="country" value="Kenya" required>
                        </div>

                        <!-- Additional Information -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Additional Information</h6>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="landmark" class="form-label">Nearby Landmark</label>
                            <input type="text" class="form-control" id="landmark" name="landmark"
                                   placeholder="Helpful landmark for directions">
                            <small class="text-muted">E.g., "Next to City Mall" or "Opposite Police Station"</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="validFrom" class="form-label">Valid From</label>
                            <input type="text" class="form-control component-datepicker" id="validFrom" name="validFrom"
                                   placeholder="YYYY-MM-DD" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="validTo" class="form-label">Valid To</label>
                            <input type="text" class="form-control component-datepicker" id="validTo" name="validTo"
                                   placeholder="YYYY-MM-DD" readonly>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="addressNotes" class="form-label">Notes</label>
                            <textarea class="form-control" id="addressNotes" name="notes" rows="2"
                                      placeholder="Any additional notes about this address"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Save Address
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

