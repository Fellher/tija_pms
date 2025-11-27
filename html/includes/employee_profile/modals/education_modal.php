<!-- Add/Edit Education Modal -->
<div class="modal fade" id="educationModal" tabindex="-1" aria-labelledby="educationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="educationModalLabel">Add Education</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="educationForm" onsubmit="saveEducation(event)">
                <div class="modal-body">
                    <input type="hidden" id="educationID" name="educationID">
                    <input type="hidden" name="employeeID" value="<?= $employeeID ?>">

                    <div class="row">
                        <!-- Institution Details -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Institution Details</h6>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label for="institutionName" class="form-label">Institution Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="institutionName" name="institutionName"
                                   placeholder="e.g., University of Nairobi" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="institutionType" class="form-label">Institution Type</label>
                            <select class="form-select" id="institutionType" name="institutionType">
                                <option value="university">University</option>
                                <option value="college">College</option>
                                <option value="technical">Technical Institute</option>
                                <option value="high_school">High School</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="institutionCountry" class="form-label">Country</label>
                            <input type="text" class="form-control" id="institutionCountry" name="institutionCountry" value="Kenya">
                        </div>

                        <!-- Qualification Details -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">Qualification Details</h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="qualificationLevel" class="form-label">Qualification Level <span class="text-danger">*</span></label>
                            <select class="form-select" id="qualificationLevel" name="qualificationLevel" required>
                                <option value="">Select Level</option>
                                <option value="high_school">High School</option>
                                <option value="certificate">Certificate</option>
                                <option value="diploma">Diploma</option>
                                <option value="degree">Bachelor's Degree</option>
                                <option value="masters">Master's Degree</option>
                                <option value="phd">PhD/Doctorate</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="qualificationTitle" class="form-label">Qualification Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="qualificationTitle" name="qualificationTitle"
                                   placeholder="e.g., Bachelor of Science" required>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label for="fieldOfStudy" class="form-label">Field of Study</label>
                            <input type="text" class="form-control" id="fieldOfStudy" name="fieldOfStudy"
                                   placeholder="e.g., Computer Science, Business Administration">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="grade" class="form-label">Grade/GPA</label>
                            <input type="text" class="form-control" id="grade" name="grade"
                                   placeholder="e.g., 3.8, First Class">
                        </div>

                        <!-- Dates -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">Duration</h6>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="eduStartDate" class="form-label">Start Date</label>
                            <input type="text" class="form-control component-datepicker" id="eduStartDate" name="startDate" placeholder="Select date">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="eduCompletionDate" class="form-label">Completion Date</label>
                            <input type="text" class="form-control component-datepicker" id="eduCompletionDate" name="completionDate" placeholder="Select date">
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="isCompleted" name="isCompleted" value="Y" checked>
                                <label class="form-check-label" for="isCompleted">
                                    Completed
                                </label>
                            </div>
                        </div>

                        <!-- Additional -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">Additional Information</h6>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="certificateNumber" class="form-label">Certificate Number</label>
                            <input type="text" class="form-control" id="certificateNumber" name="certificateNumber">
                        </div>

                        <div class="col-12 mb-3">
                            <label for="eduNotes" class="form-label">Notes</label>
                            <textarea class="form-control" id="eduNotes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Save Education
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

