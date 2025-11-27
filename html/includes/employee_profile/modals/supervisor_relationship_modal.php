<!-- Add/Edit Supervisor Relationship Modal -->
<div class="modal fade" id="supervisorModal" tabindex="-1" aria-labelledby="supervisorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="supervisorModalLabel">Add Supervisor Relationship</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="supervisorForm" onsubmit="saveSupervisorRelationship(event)">
                <div class="modal-body">
                    <input type="hidden" id="relationshipID" name="relationshipID">
                    <input type="hidden" name="employeeID" value="<?= $employeeID ?>">

                    <div class="row">
                        <!-- Supervisor Selection -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Supervisor Details</h6>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="supervisorID" class="form-label">Select Supervisor <span class="text-danger">*</span></label>
                            <select class="form-select" id="supervisorID" name="supervisorID" required>
                                <option value="">-- Select Supervisor --</option>
                                <?php
                                // Get all active employees except the current one
                                $orgDataID =  $employeeDetails->orgDataID;
                                $entityID = $employeeDetails->entityID;

                                $allEmployees = Employee::employees(['orgDataID' => $orgDataID, 'entityID' => $entityID], false, $DBConn);
                                if ($allEmployees) {
                                    foreach ($allEmployees as $emp) {
                                        if ($emp->ID != $employeeID) {
                                            echo "<option value='{$emp->ID}'>";
                                            echo htmlspecialchars($emp->employeeName ?? ($emp->FirstName . ' ' . $emp->Surname));
                                            if (!empty($emp->jobTitle)) {
                                                echo " - " . htmlspecialchars($emp->jobTitle);
                                            }
                                            echo "</option>";
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Relationship Type -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">Relationship Type</h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="relationshipType" class="form-label">Reporting Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="relationshipType" name="relationshipType" required>
                                <option value="">-- Select Type --</option>
                                <option value="direct">Direct Reporting (Solid Line)</option>
                                <option value="indirect">Indirect Reporting</option>
                                <option value="dotted-line">Dotted-Line Reporting</option>
                                <option value="functional">Functional Reporting</option>
                                <option value="matrix">Matrix Reporting</option>
                            </select>
                            <small class="text-muted">Direct = Primary hierarchy, Dotted-line = Secondary/Project-based</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="percentage" class="form-label">Time Allocation</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="percentage" name="percentage"
                                       min="0" max="100" step="0.01" value="100">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">Percentage of time reporting to this supervisor</small>
                        </div>

                        <!-- Additional Details -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">Additional Details</h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="scope" class="form-label">Scope of Supervision</label>
                            <input type="text" class="form-control" id="scope" name="scope"
                                   placeholder="e.g., Technical, Administrative, Project-based">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="department" class="form-label">Department Context</label>
                            <input type="text" class="form-control" id="department" name="department"
                                   placeholder="e.g., Engineering, Sales">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="effectiveDate" class="form-label">Effective From</label>
                            <input type="text" class="form-control supervisor-datepicker" id="effectiveDate"
                                   name="effectiveDate" placeholder="Select date">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="endDate" class="form-label">End Date</label>
                            <input type="text" class="form-control supervisor-datepicker" id="endDate"
                                   name="endDate" placeholder="Leave empty if ongoing">
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="isPrimarySupervisor"
                                       name="isPrimary" value="Y">
                                <label class="form-check-label" for="isPrimarySupervisor">
                                    <strong>Set as Primary Supervisor</strong>
                                </label>
                                <small class="d-block text-muted">This will update the primary supervisor in user details</small>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="isActiveRelationship"
                                       name="isActive" value="Y" checked>
                                <label class="form-check-label" for="isActiveRelationship">
                                    <strong>Active Relationship</strong>
                                </label>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="supervisorNotes" class="form-label">Notes</label>
                            <textarea class="form-control" id="supervisorNotes" name="notes" rows="2"
                                      placeholder="Any additional information about this reporting relationship"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Save Relationship
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

