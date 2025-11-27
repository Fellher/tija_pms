<!-- Add/Edit Next of Kin Modal -->
<div class="modal fade" id="nextOfKinModal" tabindex="-1" aria-labelledby="nextOfKinModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nextOfKinModalLabel">Add Next of Kin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="nextOfKinForm" onsubmit="saveNextOfKin(event)">
                <div class="modal-body">
                    <input type="hidden" id="nextOfKinID" name="nextOfKinID">
                    <input type="hidden" name="employeeID" value="<?= $employeeID ?>">

                    <div class="row">
                        <!-- Personal Information -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Personal Information</h6>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label for="kinFullName" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="kinFullName" name="fullName" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="kinGender" class="form-label">Gender</label>
                            <select class="form-select" id="kinGender" name="gender">
                                <option value="">Select</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="kinRelationship" class="form-label">Relationship <span class="text-danger">*</span></label>
                            <select class="form-select" id="kinRelationship" name="relationship" required>
                                <option value="">Select Relationship</option>
                                <option value="Spouse">Spouse</option>
                                <option value="Parent">Parent</option>
                                <option value="Child">Child</option>
                                <option value="Sibling">Sibling</option>
                                <option value="Guardian">Guardian</option>
                                <option value="Relative">Relative</option>
                                <option value="Friend">Friend</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="kinDateOfBirth" class="form-label">Date of Birth</label>
                            <input type="text" class="form-control component-datepicker" id="kinDateOfBirth" name="dateOfBirth" placeholder="Select date">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="kinNationalID" class="form-label">National ID / Passport</label>
                            <input type="text" class="form-control" id="kinNationalID" name="nationalID">
                        </div>

                        <!-- Contact Information -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">Contact Information</h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="kinPhoneNumber" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="kinPhoneNumber" name="phoneNumber" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="kinAlternativePhone" class="form-label">Alternative Phone</label>
                            <input type="tel" class="form-control" id="kinAlternativePhone" name="alternativePhone">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="kinEmailAddress" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="kinEmailAddress" name="emailAddress">
                        </div>

                        <div class="col-12 mb-3">
                            <label for="kinAddress" class="form-label">Address</label>
                            <textarea class="form-control" id="kinAddress" name="address" rows="2"></textarea>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="kinCity" class="form-label">City/Town</label>
                            <input type="text" class="form-control" id="kinCity" name="city">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="kinCounty" class="form-label">County/State</label>
                            <input type="text" class="form-control" id="kinCounty" name="county">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="kinCountry" class="form-label">Country</label>
                            <input type="text" class="form-control" id="kinCountry" name="country" value="Kenya">
                        </div>

                        <!-- Employment & Allocation -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">Additional Details</h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="kinOccupation" class="form-label">Occupation</label>
                            <input type="text" class="form-control" id="kinOccupation" name="occupation">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="kinEmployer" class="form-label">Employer</label>
                            <input type="text" class="form-control" id="kinEmployer" name="employer">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="kinAllocationPercentage" class="form-label">Allocation Percentage</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="kinAllocationPercentage" name="allocationPercentage"
                                       min="0" max="100" step="0.01" value="0">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">For insurance and benefits distribution</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="kinIsPrimary" name="isPrimary" value="Y">
                                <label class="form-check-label" for="kinIsPrimary">
                                    <strong>Set as Primary Next of Kin</strong>
                                </label>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="kinNotes" class="form-label">Notes</label>
                            <textarea class="form-control" id="kinNotes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Save Next of Kin
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

