                    <!--
                        Step 2: Date Selection
                        - Collects start/end dates and optional half-day preference.
                        - Summary tiles are updated by `apply_leave_modal_scripts.php` during
                          date validation (see `calculateLeaveDays`).
                        - Keep element IDs in sync with scripts; they are hard dependencies.
                    -->
                    <div class="form-step" id="step2">
                        <div class="p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Select Leave Dates</h6>
                                <small class="text-muted">Choose your leave period carefully</small>
                            </div>

                            <!-- Contextual guidance shown to end users inside the wizard card -->
                            <div class="alert alert-light border mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="ri-calendar-check-line text-info me-2 mt-1"></i>
                                    <div>
                                        <small class="text-muted">
                                            <strong>Guidance:</strong> Select your leave start and end dates. The system will automatically
                                            calculate working days, weekends, and holidays. Ensure you have sufficient leave balance
                                            and check maximum days per application limits.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="startDate" class="form-label">Start Date <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control date-picker" id="startDate" name="startDate" autocomplete="off" required>
                                    <div class="form-text">Select the first day of your leave</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="endDate" class="form-label">End Date <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control date-picker" id="endDate" name="endDate" autocomplete="off" required>
                                    <div class="form-text">Select the last day of your leave</div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="halfDayLeave" name="halfDayLeave">
                                        <label class="form-check-label" for="halfDayLeave">
                                            Half Day Leave
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12" id="halfDayOptions" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">Half Day Period</label>
                                            <select class="form-select" id="halfDayPeriod" name="halfDayPeriod">
                                                <option value="">Select Period</option>
                                                <option value="morning">Morning (8:00 AM - 12:00 PM)</option>
                                                <option value="afternoon">Afternoon (1:00 PM - 5:00 PM)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Calculated leave summary; values updated via JS when dates change -->
                            <div class="mt-4 p-3 bg-light rounded">
                                <h6 class="mb-2">Leave Summary</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <small class="text-muted">Total Days:</small>
                                        <div class="fw-bold" id="totalDays">0</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Working Days:</small>
                                        <div class="fw-bold" id="workingDays">0</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Weekends:</small>
                                        <div class="fw-bold" id="weekendDays">0</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Holidays:</small>
                                        <div class="fw-bold" id="holidayDays">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--
                        Step 3: Additional Details
                        - Captures narrative inputs and optional supplemental data.
                        - IDs are referenced downstream for review step population.
                    -->
                    <div class="form-step" id="step3">
                        <div class="p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Additional Information</h6>
                                <small class="text-muted">Provide details for your leave application</small>
                            </div>

                            <!-- User guidance for the additional detail capture -->
                            <div class="alert alert-light border mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="ri-file-text-line text-warning me-2 mt-1"></i>
                                    <div>
                                        <small class="text-muted">
                                            <strong>Guidance:</strong> Provide a clear reason for your leave and any additional information
                                            that will help your supervisor make an informed decision. Include emergency contact details
                                            and handover notes for your colleagues.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="leaveReason" class="form-label">Reason for Leave <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="leaveReason" name="leaveReason" rows="3"
                                              placeholder="Please provide a brief reason for your leave..." required></textarea>
                                </div>
                                <div class="col-12">
                                    <label for="emergencyContact" class="form-label">Emergency Contact</label>
                                    <input type="text" class="form-control" id="emergencyContact" name="emergencyContact"
                                           placeholder="Name and phone number of emergency contact">
                                </div>
                                <div class="col-12">
                                    <label for="handoverNotes" class="form-label">Handover Notes</label>
                                    <textarea class="form-control" id="handoverNotes" name="handoverNotes" rows="3"
                                              placeholder="Any important information for your colleagues during your absence..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label for="supportingDocuments" class="form-label">Supporting Documents</label>
                                    <input type="file" class="form-control" id="supportingDocuments" name="supportingDocuments[]"
                                           multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                    <div class="form-text">Upload any supporting documents (medical certificates, etc.)</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--
                        Step 4: Leave Handover Plan
                        - Captures structured handover requirements if policies demand it.
                        - Users can add items, assign colleagues, and share instructions.
                    -->
                    <div class="form-step" id="step4">
                        <div class="p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="mb-0">Leave Handover Plan</h6>
                                    <small class="text-muted">Document who will cover your responsibilities</small>
                                </div>
                                <span id="handoverRequirementBadge" class="badge bg-secondary">Pending selection</span>
                            </div>
                            <div id="handoverRequirementNotice" class="alert alert-info d-none">
                                <i class="ri-information-line me-2"></i>
                                <span>Select a leave type and dates to determine if a handover is required.</span>
                            </div>

                            <div class="card mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Add Handover Item</h6>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="resetHandoverFormBtn">
                                        <i class="ri-refresh-line me-1"></i>Reset
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Task / Responsibility <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="handoverItemTitle" placeholder="e.g. Weekly sales report">
                                        </div>
                                      </div>
                                    <div class="row g-3 mt-0">
                                        <div class="col-md-3">
                                            <label class="form-label">Type</label>
                                            <select class="form-select" id="handoverItemType">
                                                <option value="function">Function</option>
                                                <option value="project_task">Project Task</option>
                                                <option value="duty">Duty</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Priority</label>
                                            <select class="form-select" id="handoverItemPriority">
                                                <option value="medium">Medium</option>
                                                <option value="low">Low</option>
                                                <option value="high">High</option>
                                                <option value="critical">Critical</option>
                                            </select>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Description / Instructions</label>
                                            <textarea class="form-control" id="handoverItemDescription" rows="2" placeholder="Provide context, access instructions, deliverables, etc."></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Due / Check-in Date</label>
                                            <input type="date" class="form-control" id="handoverItemDueDate">
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label">Assign Colleagues <span class="text-danger">*</span></label>
                                            <select class="form-select" id="handoverAssignees" multiple>
                                                <option value="">Loading colleagues...</option>
                                            </select>
                                            <div class="form-text">Hold Ctrl (Windows) or Cmd (Mac) to select multiple colleagues.</div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mt-3">
                                        <button type="button" class="btn btn-outline-primary" id="addHandoverItemBtn">
                                            <i class="ri-add-line me-1"></i>Add Item
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Planned Handover Items</h6>
                                    <small class="text-muted" id="handoverItemsCounter">0 items</small>
                                </div>
                                <div class="card-body" id="handoverItemsList">
                                    <p class="text-muted mb-0">No handover items added yet. Use the form above to capture the responsibilities you need to transfer.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--
                        Step 5: Review and Submit
                        - Read-only summary populated before submission (see `updateReviewSummary`).
                        - Ensure any new fields added earlier are mirrored here.
                    -->
                    <div class="form-step" id="step5">
                        <div class="p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Review Your Application</h6>
                                <small class="text-muted">Verify all details before submitting</small>
                            </div>

                            <!-- Final check prompt for the applicant -->
                            <div class="alert alert-light border mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="ri-check-double-line text-success me-2 mt-1"></i>
                                    <div>
                                        <small class="text-muted">
                                            <strong>Final Check:</strong> Review all your leave details carefully. Once submitted,
                                            your application will be sent to your supervisor for approval. You can track the
                                            approval status in your leave dashboard.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">Leave Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <strong>Leave Type:</strong>
                                                <span id="reviewLeaveType">-</span>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Start Date:</strong>
                                                <span id="reviewStartDate">-</span>
                                            </div>
                                            <div class="mb-2">
                                                <strong>End Date:</strong>
                                                <span id="reviewEndDate">-</span>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Working Days:</strong>
                                                <span id="reviewTotalDays">-</span>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Half Day:</strong>
                                                <span id="reviewHalfDay">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">Additional Information</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <strong>Reason:</strong>
                                                <div id="reviewReason" class="text-muted small">-</div>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Emergency Contact:</strong>
                                                <span id="reviewEmergencyContact">-</span>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Handover Notes:</strong>
                                                <div id="reviewHandoverNotes" class="text-muted small">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Handover Overview</h6>
                                    <span class="badge bg-secondary" id="reviewHandoverStatus">Not evaluated</span>
                                </div>
                                <div class="card-body" id="reviewHandoverItems">
                                    <p class="text-muted mb-0">No handover items captured.</p>
                                </div>
                            </div>

                            <!-- Approval workflow preview - dynamically populated -->
                            <div class="mt-4">
                                <h6 class="mb-3">Approval Workflow</h6>
                                <div id="workflowPreviewContainer">
                                    <div class="text-center py-3">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                        <small class="text-muted d-block mt-2">Loading workflow...</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden Fields -->
                    <!-- <input type="hidden" name="leaveTypeId" id="leaveTypeId">
                    <input type="hidden" name="leaveEntitlementId" id="leaveEntitlementId">
                    <input type="hidden" name="employeeId" value="<?= $employeeDetails->ID ?>">
                    <input type="hidden" name="orgDataId" value="<?= $orgDataID ?>">
                    <input type="hidden" name="entityId" value="<?= $entityID ?>">
                    <input type="hidden" name="leavePeriodId" value="<?= $currentLeavePeriod->leavePeriodID ?? '' ?>"> -->

            <!-- <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-outline-primary" id="prevStepBtn" onclick="prevStep()" style="display: none;">
                    <i class="ri-arrow-left-line me-1"></i>Previous
                </button>
                <button type="button" class="btn btn-primary" id="nextStepBtn" onclick="nextStep()">
                    Next <i class="ri-arrow-right-line ms-1"></i>
                </button>
                <button type="button" class="btn btn-success" id="submitApplicationBtn" onclick="submitLeaveApplication()" style="display: none;">
                    <i class="ri-send-plane-line me-1"></i>Submit Application
                </button>
            </div> -->
