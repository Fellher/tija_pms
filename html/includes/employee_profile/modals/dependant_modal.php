<!-- Add/Edit Dependant Modal -->
<div class="modal fade" id="dependantModal" tabindex="-1" aria-labelledby="dependantModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dependantModalLabel">Add Dependant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="dependantForm" onsubmit="saveDependant(event)">
                <div class="modal-body">
                    <input type="hidden" id="dependantID" name="dependantID">
                    <input type="hidden" name="employeeID" value="<?= $employeeID ?>">

                    <div class="row">
                        <!-- Personal Information -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Personal Information</h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="depFullName" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="depFullName" name="fullName" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="depRelationship" class="form-label">Relationship <span class="text-danger">*</span></label>
                            <select class="form-select" id="depRelationship" name="relationship" required>
                                <option value="">Select Relationship</option>
                                <option value="Child">Child</option>
                                <option value="Spouse">Spouse</option>
                                <option value="Parent">Parent</option>
                                <option value="Sibling">Sibling</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="depDateOfBirth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                            <input type="text" class="form-control component-datepicker" id="depDateOfBirth" name="dateOfBirth" placeholder="Select date" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="depGender" class="form-label">Gender <span class="text-danger">*</span></label>
                            <select class="form-select" id="depGender" name="gender" required>
                                <option value="">Select</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="depNationalID" class="form-label">ID / Birth Cert No.</label>
                            <input type="text" class="form-control" id="depNationalID" name="nationalID">
                        </div>

                        <!-- Status Flags -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">Status & Benefits</h6>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="depIsBeneficiary" name="isBeneficiary" value="Y">
                                <label class="form-check-label" for="depIsBeneficiary">
                                    Eligible for Benefits
                                </label>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="depIsStudent" name="isStudent" value="Y">
                                <label class="form-check-label" for="depIsStudent">
                                    Currently a Student
                                </label>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="depIsDisabled" name="isDisabled" value="Y">
                                <label class="form-check-label" for="depIsDisabled">
                                    Has Disability
                                </label>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="depIsDependentForTax" name="isDependentForTax" value="Y">
                                <label class="form-check-label" for="depIsDependentForTax">
                                    Tax Dependent
                                </label>
                            </div>
                        </div>

                        <!-- Education (for students) -->
                        <div class="col-12 mt-3" id="educationSection" style="display: none;">
                            <h6 class="border-bottom pb-2 mb-3">Education Details</h6>
                        </div>

                        <div class="col-md-6 mb-3" id="schoolNameSection" style="display: none;">
                            <label for="depSchoolName" class="form-label">School/Institution Name</label>
                            <input type="text" class="form-control" id="depSchoolName" name="schoolName">
                        </div>

                        <div class="col-md-3 mb-3" id="gradeSection" style="display: none;">
                            <label for="depGrade" class="form-label">Grade/Level</label>
                            <input type="text" class="form-control" id="depGrade" name="grade" placeholder="e.g., Grade 10">
                        </div>

                        <div class="col-md-3 mb-3" id="studentIDSection" style="display: none;">
                            <label for="depStudentID" class="form-label">Student ID</label>
                            <input type="text" class="form-control" id="depStudentID" name="studentID">
                        </div>

                        <!-- Health Information -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">Health Information</h6>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="depBloodType" class="form-label">Blood Type</label>
                            <select class="form-select" id="depBloodType" name="bloodType">
                                <option value="">Select</option>
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

                        <div class="col-md-4 mb-3">
                            <label for="depInsuranceMemberNumber" class="form-label">Insurance Member Number</label>
                            <input type="text" class="form-control" id="depInsuranceMemberNumber" name="insuranceMemberNumber">
                        </div>

                        <div class="col-12 mb-3">
                            <label for="depMedicalConditions" class="form-label">Medical Conditions / Allergies</label>
                            <textarea class="form-control" id="depMedicalConditions" name="medicalConditions" rows="2" placeholder="Any known medical conditions or allergies"></textarea>
                        </div>

                        <!-- Contact (for older dependants) -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">Contact Information (Optional)</h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="depPhoneNumber" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="depPhoneNumber" name="phoneNumber">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="depEmailAddress" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="depEmailAddress" name="emailAddress">
                        </div>

                        <!-- Additional Notes -->
                        <div class="col-12 mb-3">
                            <label for="depNotes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="depNotes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Save Dependant
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Toggle education section based on student status
document.addEventListener('DOMContentLoaded', function() {
    const isStudentCheckbox = document.getElementById('depIsStudent');
    if (isStudentCheckbox) {
        isStudentCheckbox.addEventListener('change', function() {
            const educationSection = document.getElementById('educationSection');
            const schoolNameSection = document.getElementById('schoolNameSection');
            const gradeSection = document.getElementById('gradeSection');
            const studentIDSection = document.getElementById('studentIDSection');

            if (this.checked) {
                educationSection.style.display = 'block';
                schoolNameSection.style.display = 'block';
                gradeSection.style.display = 'block';
                studentIDSection.style.display = 'block';
            } else {
                educationSection.style.display = 'none';
                schoolNameSection.style.display = 'none';
                gradeSection.style.display = 'none';
                studentIDSection.style.display = 'none';
            }
        });
    }
});
</script>

