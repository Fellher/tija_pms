<!-- Add/Edit Work Experience Modal -->
<div class="modal fade" id="experienceModal" tabindex="-1" aria-labelledby="experienceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="experienceModalLabel">Add Work Experience</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="experienceForm" onsubmit="saveExperience(event)">
                <div class="modal-body">
                    <input type="hidden" id="experienceID" name="experienceID">
                    <input type="hidden" name="employeeID" value="<?= $employeeID ?>">

                    <div class="row">
                        <!-- Company Details -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Company Details</h6>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label for="companyName" class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="companyName" name="companyName" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="companyIndustry" class="form-label">Industry</label>
                            <input type="text" class="form-control" id="companyIndustry" name="companyIndustry"
                                   placeholder="e.g., Technology, Finance">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="companyLocation" class="form-label">Company Location</label>
                            <input type="text" class="form-control" id="companyLocation" name="companyLocation"
                                   placeholder="City, Country">
                        </div>

                        <!-- Position Details -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">Position Details</h6>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label for="expJobTitle" class="form-label">Job Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="expJobTitle" name="jobTitle" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="expDepartment" class="form-label">Department</label>
                            <input type="text" class="form-control" id="expDepartment" name="department">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="employmentType" class="form-label">Employment Type</label>
                            <select class="form-select" id="employmentType" name="employmentType">
                                <option value="full_time">Full Time</option>
                                <option value="part_time">Part Time</option>
                                <option value="contract">Contract</option>
                                <option value="internship">Internship</option>
                                <option value="freelance">Freelance</option>
                            </select>
                        </div>

                        <!-- Dates -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">Employment Period</h6>
                        </div>

                        <div class="col-md-5 mb-3">
                            <label for="expStartDate" class="form-label">Start Date</label>
                            <input type="text" class="form-control component-datepicker" id="expStartDate" name="startDate" placeholder="Select date">
                        </div>

                        <div class="col-md-5 mb-3">
                            <label for="expEndDate" class="form-label">End Date</label>
                            <input type="text" class="form-control component-datepicker" id="expEndDate" name="endDate" placeholder="Leave empty if current">
                        </div>

                        <div class="col-md-2 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="isCurrent" name="isCurrent" value="Y">
                                <label class="form-check-label" for="isCurrent">
                                    Current
                                </label>
                            </div>
                        </div>

                        <!-- Responsibilities & Achievements -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">Responsibilities & Achievements</h6>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="responsibilities" class="form-label">Key Responsibilities</label>
                            <textarea class="form-control" id="responsibilities" name="responsibilities" rows="3"
                                      placeholder="Describe your main responsibilities"></textarea>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="achievements" class="form-label">Key Achievements</label>
                            <textarea class="form-control" id="achievements" name="achievements" rows="2"
                                      placeholder="Notable accomplishments and results"></textarea>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="reasonForLeaving" class="form-label">Reason for Leaving</label>
                            <input type="text" class="form-control" id="reasonForLeaving" name="reasonForLeaving">
                        </div>

                        <!-- Reference -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">Supervisor/Reference (Optional)</h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="supervisorName" class="form-label">Supervisor Name</label>
                            <input type="text" class="form-control" id="supervisorName" name="supervisorName">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="supervisorContact" class="form-label">Supervisor Contact</label>
                            <input type="text" class="form-control" id="supervisorContact" name="supervisorContact"
                                   placeholder="Phone or email">
                        </div>

                        <div class="col-12 mb-3">
                            <label for="expNotes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="expNotes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Save Experience
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

