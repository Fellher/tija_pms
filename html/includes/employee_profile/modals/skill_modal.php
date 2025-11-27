<!-- Add/Edit Skill Modal -->
<div class="modal fade" id="skillModal" tabindex="-1" aria-labelledby="skillModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="skillModalLabel">Add Skill</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="skillForm" onsubmit="saveSkill(event)">
                <div class="modal-body">
                    <input type="hidden" id="skillID" name="skillID">
                    <input type="hidden" name="employeeID" value="<?= $employeeID ?>">

                    <div class="row">
                        <!-- Skill Details -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Skill Details</h6>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label for="skillName" class="form-label">Skill Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="skillName" name="skillName"
                                   placeholder="e.g., Project Management, Python, Excel" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="skillCategory" class="form-label">Category</label>
                            <select class="form-select" id="skillCategory" name="skillCategory">
                                <option value="">Select Category</option>
                                <option value="Technical">Technical</option>
                                <option value="Soft Skills">Soft Skills</option>
                                <option value="Language">Language</option>
                                <option value="Management">Management</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <!-- Proficiency -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">Proficiency & Experience</h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="proficiencyLevel" class="form-label">Proficiency Level</label>
                            <select class="form-select" id="proficiencyLevel" name="proficiencyLevel">
                                <option value="beginner">Beginner</option>
                                <option value="intermediate" selected>Intermediate</option>
                                <option value="advanced">Advanced</option>
                                <option value="expert">Expert</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="yearsOfExperience" class="form-label">Years of Experience</label>
                            <input type="number" class="form-control" id="yearsOfExperience" name="yearsOfExperience"
                                   min="0" max="50" value="0">
                        </div>

                        <!-- Certification -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">Certification (Optional)</h6>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="isCertified" name="isCertified" value="Y">
                                <label class="form-check-label" for="isCertified">
                                    Certified
                                </label>
                            </div>
                        </div>

                        <div class="col-md-9 mb-3">
                            <label for="certificationName" class="form-label">Certification Name</label>
                            <input type="text" class="form-control" id="certificationName" name="certificationName"
                                   placeholder="If certified, name the certification">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="lastUsedDate" class="form-label">Last Used</label>
                            <input type="text" class="form-control qual-datepicker component-datepicker" id="lastUsedDate" name="lastUsedDate" placeholder="When did you last use this skill?">
                        </div>

                        <div class="col-12 mb-3">
                            <label for="skillNotes" class="form-label">Notes</label>
                            <textarea class="form-control" id="skillNotes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Save Skill
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

