<!-- Add/Edit Emergency Contact Modal -->
<div class="modal fade" id="emergencyContactModal" tabindex="-1" aria-labelledby="emergencyContactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="emergencyContactModalLabel">Add Emergency Contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="emergencyContactForm" onsubmit="saveEmergencyContact(event)">
                <div class="modal-body">
                    <input type="hidden" id="emergencyContactID" name="emergencyContactID">
                    <input type="hidden" name="employeeID" value="<?= $employeeID ?>">

                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Contact Information</h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="contactName" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="contactName" name="contactName"
                                   placeholder="Enter full name" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="relationship" class="form-label">Relationship <span class="text-danger">*</span></label>
                            <select class="form-select" id="relationship" name="relationship" required>
                                <option value="">Select Relationship</option>
                                <option value="Spouse">Spouse</option>
                                <option value="Parent">Parent</option>
                                <option value="Sibling">Sibling</option>
                                <option value="Child">Child</option>
                                <option value="Partner">Partner</option>
                                <option value="Friend">Friend</option>
                                <option value="Colleague">Colleague</option>
                                <option value="Guardian">Guardian</option>
                                <option value="Relative">Relative</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <!-- Contact Details -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Phone & Email</h6>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="primaryPhoneNumber" class="form-label">Primary Phone <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="primaryPhoneNumber" name="primaryPhoneNumber"
                                   placeholder="254 712 345 678" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="secondaryPhoneNumber" class="form-label">Secondary Phone</label>
                            <input type="tel" class="form-control" id="secondaryPhoneNumber" name="secondaryPhoneNumber"
                                   placeholder="254 723 456 789">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="workPhoneNumber" class="form-label">Work Phone</label>
                            <input type="tel" class="form-control" id="workPhoneNumber" name="workPhoneNumber"
                                   placeholder="254 20 1234567">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="emailAddress" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="emailAddress" name="emailAddress"
                                   placeholder="contact@example.com">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="nationalID" class="form-label">National ID/Passport</label>
                            <input type="text" class="form-control" id="nationalID" name="nationalID"
                                   placeholder="ID or Passport number">
                        </div>

                        <!-- Address -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Address</h6>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="ecAddress" class="form-label">Full Address</label>
                            <textarea class="form-control" id="ecAddress" name="address" rows="2"
                                      placeholder="Street address or P.O. Box"></textarea>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="ecCity" class="form-label">City</label>
                            <input type="text" class="form-control" id="ecCity" name="city" placeholder="City/Town">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="ecCounty" class="form-label">County</label>
                            <input type="text" class="form-control" id="ecCounty" name="county" placeholder="County/State">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="ecCountry" class="form-label">Country</label>
                            <input type="text" class="form-control" id="ecCountry" name="country" value="Kenya">
                        </div>

                        <!-- Employment Information -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Employment (Optional)</h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="occupation" class="form-label">Occupation</label>
                            <input type="text" class="form-control" id="occupation" name="occupation"
                                   placeholder="Job title or profession">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="employer" class="form-label">Employer</label>
                            <input type="text" class="form-control" id="employer" name="employer"
                                   placeholder="Company or organization name">
                        </div>

                        <!-- Priority and Authorization -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Priority & Authorization</h6>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="contactPriority" class="form-label">Priority Level <span class="text-danger">*</span></label>
                            <select class="form-select" id="contactPriority" name="contactPriority" required>
                                <option value="primary">Primary Contact</option>
                                <option value="secondary" selected>Secondary Contact</option>
                                <option value="tertiary">Tertiary Contact</option>
                            </select>
                            <small class="text-muted">Order in which to contact in case of emergency</small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="isPrimaryEC" name="isPrimaryEC" value="Y">
                                <label class="form-check-label" for="isPrimaryEC">
                                    <strong>Primary Emergency Contact</strong>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="sortOrder" class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="sortOrder" name="sortOrder"
                                   min="0" value="0" placeholder="0">
                        </div>

                        <!-- Authorization Checkboxes -->
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="authorizedToCollectSalary"
                                       name="authorizedToCollectSalary" value="Y">
                                <label class="form-check-label" for="authorizedToCollectSalary">
                                    Authorized to collect salary on employee's behalf
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="authorizedForMedicalDecisions"
                                       name="authorizedForMedicalDecisions" value="Y">
                                <label class="form-check-label" for="authorizedForMedicalDecisions">
                                    Authorized for medical decisions
                                </label>
                            </div>
                        </div>

                        <!-- Medical Information (Optional) -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Medical Information (Optional)</h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="bloodType" class="form-label">Blood Type</label>
                            <select class="form-select" id="bloodType" name="bloodType">
                                <option value="">Unknown</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="medicalConditions" class="form-label">Medical Conditions</label>
                            <textarea class="form-control" id="medicalConditions" name="medicalConditions" rows="2"
                                      placeholder="Any known medical conditions"></textarea>
                        </div>

                        <!-- Additional Notes -->
                        <div class="col-12 mb-3">
                            <label for="ecNotes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="ecNotes" name="notes" rows="2"
                                      placeholder="Any additional information about this emergency contact"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Save Emergency Contact
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

