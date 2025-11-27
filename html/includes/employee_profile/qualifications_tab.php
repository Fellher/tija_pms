<?php
/**
 * Qualifications Tab - Complete Implementation
 * Education, Experience, Skills, Certifications, Licenses with full CRUD
 */

// Get current sub-tab
$currentSubTab = (isset($_GET['subtab']) && !empty($_GET['subtab'])) ? Utility::clean_string($_GET['subtab']) : 'education';

// Define sub-tabs array
$qualificationSubTabsArray = [
    (object)["title" => "Education", "icon" => "ri-book-line", "slug" => "education", "active" => $currentSubTab == "education"],
    (object)["title" => "Work Experience", "icon" => "ri-briefcase-line", "slug" => "experience", "active" => $currentSubTab == "experience"],
    (object)["title" => "Skills", "icon" => "ri-tools-line", "slug" => "skills", "active" => $currentSubTab == "skills"],
    (object)["title" => "Certifications", "icon" => "ri-medal-line", "slug" => "certifications", "active" => $currentSubTab == "certifications"],
    (object)["title" => "Licenses", "icon" => "ri-shield-check-line", "slug" => "licenses", "active" => $currentSubTab == "licenses"]
];

// Get data for each section
$education = EmployeeProfileExtended::get_education_full(['employeeID' => $employeeID], false, $DBConn);
$experience = EmployeeProfileExtended::get_work_experience_full(['employeeID' => $employeeID], false, $DBConn);
$skills = EmployeeProfileExtended::get_skills(['employeeID' => $employeeID], false, $DBConn);

// Get certifications and licenses using Employee class methods
$certifications = Employee::employee_certifications(
    array('employeeID' => $employeeID, 'Suspended' => 'N'),
    false,
    $DBConn
);
// Convert to array if false
if (!$certifications) {
    $certifications = array();
}

$licenses = Employee::employee_licenses(
    array('employeeID' => $employeeID, 'Suspended' => 'N'),
    false,
    $DBConn
);
// Convert to array if false
if (!$licenses) {
    $licenses = array();
}
?>

<div class="section-header">
    <h5 class="mb-0"><i class="ri-award-line me-2"></i>Qualifications & Experience</h5>
</div>

<!-- Sub-Tabs Navigation -->
<ul class="nav nav-pills qualification-sub-tabs mb-3" role="tablist">
    <?php foreach ($qualificationSubTabsArray as $subTab):
        $subTabUrl = "?s={$s}&p={$p}&uid={$employeeID}&tab=qualifications&subtab={$subTab->slug}";
    ?>
    <li class="nav-item" role="presentation">
        <a href="<?= $subTabUrl ?>" class="nav-link <?= $subTab->active ? 'active' : '' ?>">
            <i class="<?= $subTab->icon ?> me-1"></i><?= $subTab->title ?>
        </a>
    </li>
    <?php endforeach; ?>
</ul>

<!-- Sub-Tab Content -->
<div class="qualification-content">
    <?php if ($currentSubTab == 'education'): ?>
    <!-- EDUCATION SECTION -->
    <div class="qualification-section">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">Educational Background</h6>
            <?php if ($canEdit): ?>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#educationModal" onclick="prepareAddEducation()">
                <i class="ri-add-line me-1"></i> Add Education
            </button>
            <?php endif; ?>
        </div>
        <?php if ($education && count($education) > 0): ?>
        <div class="row">
            <?php foreach ($education as $edu): ?>
            <div class="col-md-6 mb-3">
                <div class="info-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($edu->qualificationTitle) ?></h6>
                            <p class="text-muted mb-1"><?= htmlspecialchars($edu->institutionName) ?></p>
                            <span class="badge bg-primary-transparent"><?= ucfirst(str_replace('_', ' ', $edu->qualificationLevel)) ?></span>
                            <?php if (!empty($edu->fieldOfStudy)): ?>
                            <p class="small mt-2 mb-1"><strong>Field:</strong> <?= htmlspecialchars($edu->fieldOfStudy) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($edu->completionDate)): ?>
                            <p class="small mb-0"><strong>Completed:</strong> <?= date('M Y', strtotime($edu->completionDate)) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if ($canEdit): ?>
                        <div class="btn-group btn-group-sm ms-2">
                            <button class="btn btn-sm btn-icon btn-primary-light" data-bs-toggle="modal" data-bs-target="#educationModal" onclick="editEducation(<?= $edu->educationID ?>)"><i class="ri-edit-line"></i></button>
                            <button class="btn btn-sm btn-icon btn-danger-light" onclick="deleteEducation(<?= $edu->educationID ?>)"><i class="ri-delete-bin-line"></i></button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-info"><i class="ri-information-line me-2"></i>No education records. Click "Add Education" to add qualifications.</div>
        <?php endif; ?>
    </div>

    <?php elseif ($currentSubTab == 'experience'): ?>
    <!-- EXPERIENCE SECTION -->
    <div class="qualification-section">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">Previous Employment</h6>
            <?php if ($canEdit): ?>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#experienceModal" onclick="prepareAddExperience()">
                <i class="ri-add-line me-1"></i> Add Experience
            </button>
            <?php endif; ?>
        </div>
        <?php if ($experience && count($experience) > 0): ?>
        <?php foreach ($experience as $exp): ?>
        <div class="info-card mb-3">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($exp->jobTitle) ?></h6>
                    <p class="text-primary mb-1"><?= htmlspecialchars($exp->companyName) ?></p>
                    <p class="small text-muted mb-2">
                        <?= date('M Y', strtotime($exp->startDate)) ?> -
                        <?= $exp->isCurrent == 'Y' ? 'Present' : date('M Y', strtotime($exp->endDate)) ?>
                        <?php if ($exp->isCurrent == 'Y'): ?><span class="badge bg-success ms-2">Current</span><?php endif; ?>
                    </p>
                    <?php if (!empty($exp->responsibilities)): ?>
                    <p class="small mb-0"><?= nl2br(htmlspecialchars(substr($exp->responsibilities, 0, 150))) ?><?= strlen($exp->responsibilities) > 150 ? '...' : '' ?></p>
                    <?php endif; ?>
                </div>
                <?php if ($canEdit): ?>
                <div class="btn-group btn-group-sm ms-2">
                    <button class="btn btn-sm btn-icon btn-primary-light" data-bs-toggle="modal" data-bs-target="#experienceModal" onclick="editExperience(<?= $exp->workExperienceID  ?>)"><i class="ri-edit-line"></i></button>
                    <button class="btn btn-sm btn-icon btn-danger-light" onclick="deleteExperience(<?= $exp->workExperienceID ?>)"><i class="ri-delete-bin-line"></i></button>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="alert alert-info"><i class="ri-information-line me-2"></i>No work experience records.</div>
        <?php endif; ?>
    </div>

    <?php elseif ($currentSubTab == 'skills'): ?>
    <!-- SKILLS SECTION -->
    <div class="qualification-section">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">Professional Skills</h6>
            <?php if ($canEdit): ?>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#skillModal" onclick="prepareAddSkill()">
                <i class="ri-add-line me-1"></i> Add Skill
            </button>
            <?php endif; ?>
        </div>
        <?php if ($skills && count($skills) > 0): ?>
        <div class="row">
            <?php foreach ($skills as $skill): ?>
            <div class="col-md-4 mb-3">
                <div class="info-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($skill->skillName) ?></h6>
                            <span class="badge bg-<?= $skill->proficiencyLevel == 'expert' ? 'success' : ($skill->proficiencyLevel == 'advanced' ? 'primary' : 'secondary') ?>-transparent">
                                <?= ucfirst($skill->proficiencyLevel) ?>
                            </span>
                            <?php if ($skill->yearsOfExperience > 0): ?>
                            <p class="small mt-2 mb-0"><?= $skill->yearsOfExperience ?> years experience</p>
                            <?php endif; ?>
                        </div>
                        <?php if ($canEdit): ?>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-sm btn-icon btn-primary-light" data-bs-toggle="modal" data-bs-target="#skillModal" onclick="editSkill(<?= $skill->skillID ?>)"><i class="ri-edit-line"></i></button>
                            <button class="btn btn-sm btn-icon btn-danger-light" onclick="deleteSkill(<?= $skill->skillID ?>)"><i class="ri-delete-bin-line"></i></button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-info"><i class="ri-information-line me-2"></i>No skills recorded.</div>
        <?php endif; ?>
    </div>

    <?php elseif ($currentSubTab == 'certifications'): ?>
    <!-- CERTIFICATIONS SECTION -->
    <div class="qualification-section">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">Professional Certifications</h6>
            <?php if ($canEdit): ?>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#certificationModal" onclick="prepareAddCertification()">
                <i class="ri-add-line me-1"></i> Add Certification
            </button>
            <?php endif; ?>
        </div>
        <?php if ($certifications && count($certifications) > 0): ?>
        <div class="row">
            <?php foreach ($certifications as $cert): ?>
            <div class="col-md-6 mb-3">
                <div class="info-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($cert->certificationName) ?></h6>
                            <p class="text-muted small mb-1"><?= htmlspecialchars($cert->issuingOrganization) ?></p>
                            <?php if (!empty($cert->expiryDate)): ?>
                            <p class="small mb-0"><strong>Expires:</strong> <?= date('M Y', strtotime($cert->expiryDate)) ?></p>
                            <?php elseif ($cert->doesNotExpire == 'Y'): ?>
                            <span class="badge bg-success">No Expiry</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($canEdit): ?>
                        <div class="btn-group btn-group-sm ms-2">
                            <button class="btn btn-sm btn-icon btn-primary-light" data-bs-toggle="modal" data-bs-target="#certificationModal" onclick="editCertification(<?= $cert->certificationID ?>)"><i class="ri-edit-line"></i></button>
                            <button class="btn btn-sm btn-icon btn-danger-light" onclick="deleteCertification(<?= $cert->certificationID ?>)"><i class="ri-delete-bin-line"></i></button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-info"><i class="ri-information-line me-2"></i>No certifications recorded.</div>
        <?php endif; ?>
    </div>

    <?php elseif ($currentSubTab == 'licenses'): ?>
    <!-- LICENSES SECTION -->
    <div class="qualification-section">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">Professional Licenses</h6>
            <?php if ($canEdit): ?>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#licenseModal" onclick="prepareAddLicense()">
                <i class="ri-add-line me-1"></i> Add License
            </button>
            <?php endif; ?>
        </div>
        <?php if ($licenses && count($licenses) > 0): ?>
        <div class="row">
            <?php foreach ($licenses as $lic): ?>
            <div class="col-md-6 mb-3">
                <div class="info-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($lic->licenseName) ?></h6>
                            <p class="small text-muted mb-1"><strong>Number:</strong> <?= htmlspecialchars($lic->licenseNumber) ?></p>
                            <p class="small text-muted mb-1"><strong>Authority:</strong> <?= htmlspecialchars($lic->issuingAuthority) ?></p>
                            <?php if (!empty($lic->expiryDate)): ?>
                            <p class="small mb-0"><strong>Expires:</strong> <?= date('M j, Y', strtotime($lic->expiryDate)) ?></p>
                            <?php endif; ?>
                            <?php if ($lic->isActive == 'Y'): ?>
                            <span class="badge bg-success mt-1">Active</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($canEdit): ?>
                        <div class="btn-group btn-group-sm ms-2">
                            <button class="btn btn-sm btn-icon btn-primary-light" data-bs-toggle="modal" data-bs-target="#licenseModal" onclick="editLicense(<?= $lic->licenseID ?>)"><i class="ri-edit-line"></i></button>
                            <button class="btn btn-sm btn-icon btn-danger-light" onclick="deleteLicense(<?= $lic->licenseID ?>)"><i class="ri-delete-bin-line"></i></button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-info"><i class="ri-information-line me-2"></i>No licenses recorded.</div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Include Modals -->
<?php include __DIR__ . '/modals/education_modal.php'; ?>
<?php include __DIR__ . '/modals/experience_modal.php'; ?>
<?php include __DIR__ . '/modals/skill_modal.php'; ?>
<?php include __DIR__ . '/modals/certification_modal.php'; ?>
<?php include __DIR__ . '/modals/license_modal.php'; ?>

<script>
// Initialize date pickers
document.addEventListener('DOMContentLoaded', function() {
    initializeQualificationDatePickers();

    // Reinit when modals open
    ['educationModal', 'experienceModal', 'skillModal', 'certificationModal', 'licenseModal'].forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('shown.bs.modal', initializeQualificationDatePickers);
        }
    });
});

function initializeQualificationDatePickers() {
    if (typeof flatpickr !== 'undefined') {
        document.querySelectorAll('.qual-datepicker').forEach(input => {
            if (!input._flatpickr) {
                input.removeAttribute('readonly');
                flatpickr(input, {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'F j, Y',
                    allowInput: false,
                    onChange: function(selectedDates, dateStr, instance) {
                        if (dateStr) input.value = dateStr;
                    }
                });
            }
        });
    }
}

// Helper to extract date from Flatpickr
function extractDateValue(inputId) {
    const input = document.getElementById(inputId);
    if (input && input._flatpickr) {
        const selectedDate = input._flatpickr.selectedDates[0];
        if (selectedDate) {
            const y = selectedDate.getFullYear();
            const m = String(selectedDate.getMonth() + 1).padStart(2, '0');
            const d = String(selectedDate.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        }
    }
    return input ? input.value : '';
}

// EDUCATION FUNCTIONS
function prepareAddEducation() {
    document.getElementById('educationForm').reset();
    document.getElementById('educationID').value = '';
    document.getElementById('educationModalLabel').textContent = 'Add Education';
}

function editEducation(id) {
    fetch(`<?= $base ?>php/scripts/global/admin/qualifications_api.php?action=get_education&id=${id}`)
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                const edu = d.data;
                document.getElementById('educationID').value = edu.educationID;
                document.getElementById('institutionName').value = edu.institutionName || '';
                document.getElementById('institutionType').value = edu.institutionType || 'university';
                document.getElementById('institutionCountry').value = edu.institutionCountry || 'Kenya';
                document.getElementById('qualificationLevel').value = edu.qualificationLevel || '';
                document.getElementById('qualificationTitle').value = edu.qualificationTitle || '';
                document.getElementById('fieldOfStudy').value = edu.fieldOfStudy || '';
                document.getElementById('grade').value = edu.grade || '';
                document.getElementById('isCompleted').checked = (edu.isCompleted === 'Y');
                document.getElementById('certificateNumber').value = edu.certificateNumber || '';
                document.getElementById('eduNotes').value = edu.notes || '';

                if (edu.startDate && document.getElementById('eduStartDate')._flatpickr) {
                    document.getElementById('eduStartDate')._flatpickr.setDate(edu.startDate, true);
                }
                if (edu.completionDate && document.getElementById('eduCompletionDate')._flatpickr) {
                    document.getElementById('eduCompletionDate')._flatpickr.setDate(edu.completionDate, true);
                }
                document.getElementById('educationModalLabel').textContent = 'Edit Education';
            } else {
                showToast(d.message, 'danger');
            }
        });
}

function saveEducation(event) {
    event.preventDefault();
    document.getElementById('eduStartDate').value = extractDateValue('eduStartDate');
    document.getElementById('eduCompletionDate').value = extractDateValue('eduCompletionDate');

    const formData = new FormData(event.target);
    formData.append('action', 'save_education');

    fetch('<?= $base ?>php/scripts/global/admin/qualifications_api.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                showToast(d.message, 'success');
                setTimeout(() => window.location.href = '?s=<?= $s ?>&p=<?= $p ?>&uid=<?= $employeeID ?>&tab=qualifications&subtab=education', 1500);
            } else {
                showToast(d.message, 'danger');
            }
        });
}

function deleteEducation(id) {
    if (!confirm('Delete this education record?')) return;
    fetch(`<?= $base ?>php/scripts/global/admin/qualifications_api.php?action=delete_education&id=${id}`, { method: 'POST' })
        .then(r => r.json())
        .then(d => {
            showToast(d.message, d.success ? 'success' : 'danger');
            if (d.success) setTimeout(() => location.reload(), 1500);
        });
}

// EXPERIENCE FUNCTIONS
function prepareAddExperience() {
    document.getElementById('experienceForm').reset();
    document.getElementById('experienceID').value = '';
    document.getElementById('experienceModalLabel').textContent = 'Add Work Experience';
}

function editExperience(id) {
    fetch(`<?= $base ?>php/scripts/global/admin/qualifications_api.php?action=get_experience&id=${id}`)
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                const exp = d.data;
                document.getElementById('experienceID').value = exp.experienceID;
                document.getElementById('companyName').value = exp.companyName || '';
                document.getElementById('companyIndustry').value = exp.companyIndustry || '';
                document.getElementById('companyLocation').value = exp.companyLocation || '';
                document.getElementById('expJobTitle').value = exp.jobTitle || '';
                document.getElementById('expDepartment').value = exp.department || '';
                document.getElementById('employmentType').value = exp.employmentType || 'full_time';
                document.getElementById('isCurrent').checked = (exp.isCurrent === 'Y');
                document.getElementById('responsibilities').value = exp.responsibilities || '';
                document.getElementById('achievements').value = exp.achievements || '';
                document.getElementById('reasonForLeaving').value = exp.reasonForLeaving || '';
                document.getElementById('supervisorName').value = exp.supervisorName || '';
                document.getElementById('supervisorContact').value = exp.supervisorContact || '';
                document.getElementById('expNotes').value = exp.notes || '';

                if (exp.startDate && document.getElementById('expStartDate')._flatpickr) {
                    document.getElementById('expStartDate')._flatpickr.setDate(exp.startDate, true);
                }
                if (exp.endDate && document.getElementById('expEndDate')._flatpickr) {
                    document.getElementById('expEndDate')._flatpickr.setDate(exp.endDate, true);
                }
                document.getElementById('experienceModalLabel').textContent = 'Edit Work Experience';
            } else {
                showToast(d.message, 'danger');
            }
        });
}

function saveExperience(event) {
    event.preventDefault();

    // Extract and set dates from Flatpickr
    const startDateValue = extractDateValue('expStartDate');
    const endDateValue = extractDateValue('expEndDate');

    document.getElementById('expStartDate').value = startDateValue;
    document.getElementById('expEndDate').value = endDateValue;

    console.log('Start Date extracted:', startDateValue);
    console.log('End Date extracted:', endDateValue);

    // Validate required startDate
    if (!startDateValue) {
        showToast('Start Date is required for work experience', 'danger');
        return;
    }

    const formData = new FormData(event.target);
    formData.append('action', 'save_experience');

    // Debug: log what we're sending
    console.log('Form data being submitted:');
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }

    const submitBtn = event.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i> Saving...';

    fetch('<?= $base ?>php/scripts/global/admin/qualifications_api.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                showToast(d.message, 'success');
                setTimeout(() => window.location.href = '?s=<?= $s ?>&p=<?= $p ?>&uid=<?= $employeeID ?>&tab=qualifications&subtab=experience', 1500);
            } else {
                showToast(d.message, 'danger');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="ri-save-line me-1"></i> Save Experience';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while saving', 'danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="ri-save-line me-1"></i> Save Experience';
        });
}

function deleteExperience(id) {
    if (!confirm('Delete this experience record?')) return;
    fetch(`<?= $base ?>php/scripts/global/admin/qualifications_api.php?action=delete_experience&id=${id}`, { method: 'POST' })
        .then(r => r.json())
        .then(d => {
            showToast(d.message, d.success ? 'success' : 'danger');
            if (d.success) setTimeout(() => location.reload(), 1500);
        });
}

// SKILL FUNCTIONS
function prepareAddSkill() {
    document.getElementById('skillForm').reset();
    document.getElementById('skillID').value = '';
    document.getElementById('skillModalLabel').textContent = 'Add Skill';
}

function editSkill(id) {
    fetch(`<?= $base ?>php/scripts/global/admin/qualifications_api.php?action=get_skill&id=${id}`)
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                const skill = d.data;
                document.getElementById('skillID').value = skill.skillID;
                document.getElementById('skillName').value = skill.skillName || '';
                document.getElementById('skillCategory').value = skill.skillCategory || '';
                document.getElementById('proficiencyLevel').value = skill.proficiencyLevel || 'intermediate';
                document.getElementById('yearsOfExperience').value = skill.yearsOfExperience || 0;
                document.getElementById('isCertified').checked = (skill.isCertified === 'Y');
                document.getElementById('certificationName').value = skill.certificationName || '';
                document.getElementById('skillNotes').value = skill.notes || '';

                if (skill.lastUsedDate && document.getElementById('lastUsedDate')._flatpickr) {
                    document.getElementById('lastUsedDate')._flatpickr.setDate(skill.lastUsedDate, true);
                }
                document.getElementById('skillModalLabel').textContent = 'Edit Skill';
            } else {
                showToast(d.message, 'danger');
            }
        });
}

function saveSkill(event) {
    event.preventDefault();
    document.getElementById('lastUsedDate').value = extractDateValue('lastUsedDate');

    const formData = new FormData(event.target);
    formData.append('action', 'save_skill');

    fetch('<?= $base ?>php/scripts/global/admin/qualifications_api.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                showToast(d.message, 'success');
                setTimeout(() => window.location.href = '?s=<?= $s ?>&p=<?= $p ?>&uid=<?= $employeeID ?>&tab=qualifications&subtab=skills', 1500);
            } else {
                showToast(d.message, 'danger');
            }
        });
}

function deleteSkill(id) {
    if (!confirm('Delete this skill?')) return;
    fetch(`<?= $base ?>php/scripts/global/admin/qualifications_api.php?action=delete_skill&id=${id}`, { method: 'POST' })
        .then(r => r.json())
        .then(d => {
            showToast(d.message, d.success ? 'success' : 'danger');
            if (d.success) setTimeout(() => location.reload(), 1500);
        });
}

// CERTIFICATION FUNCTIONS
function prepareAddCertification() {
    document.getElementById('certificationForm').reset();
    document.getElementById('certificationID').value = '';
    document.getElementById('certificationModalLabel').textContent = 'Add Certification';
}

function editCertification(id) {
    fetch(`<?= $base ?>php/scripts/global/admin/qualifications_api.php?action=get_certification&id=${id}`)
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                const cert = d.data;
                document.getElementById('certificationID').value = cert.certificationID;
                document.getElementById('certName').value = cert.certificationName || '';
                document.getElementById('issuingOrganization').value = cert.issuingOrganization || '';
                document.getElementById('certNumber').value = cert.certificationNumber || '';
                document.getElementById('certDoesNotExpire').checked = (cert.doesNotExpire === 'Y');
                document.getElementById('verificationURL').value = cert.verificationURL || '';
                document.getElementById('credentialID').value = cert.credentialID || '';
                document.getElementById('certNotes').value = cert.notes || '';

                if (cert.issueDate && document.getElementById('certIssueDate')._flatpickr) {
                    document.getElementById('certIssueDate')._flatpickr.setDate(cert.issueDate, true);
                }
                if (cert.expiryDate && document.getElementById('certExpiryDate')._flatpickr) {
                    document.getElementById('certExpiryDate')._flatpickr.setDate(cert.expiryDate, true);
                }
                document.getElementById('certificationModalLabel').textContent = 'Edit Certification';
            } else {
                showToast(d.message, 'danger');
            }
        });
}

function saveCertification(event) {
    event.preventDefault();
    document.getElementById('certIssueDate').value = extractDateValue('certIssueDate');
    document.getElementById('certExpiryDate').value = extractDateValue('certExpiryDate');

    const formData = new FormData(event.target);
    formData.append('action', 'save_certification');

    fetch('<?= $base ?>php/scripts/global/admin/qualifications_api.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                showToast(d.message, 'success');
                setTimeout(() => window.location.href = '?s=<?= $s ?>&p=<?= $p ?>&uid=<?= $employeeID ?>&tab=qualifications&subtab=certifications', 1500);
            } else {
                showToast(d.message, 'danger');
            }
        });
}

function deleteCertification(id) {
    if (!confirm('Delete this certification?')) return;
    fetch(`<?= $base ?>php/scripts/global/admin/qualifications_api.php?action=delete_certification&id=${id}`, { method: 'POST' })
        .then(r => r.json())
        .then(d => {
            showToast(d.message, d.success ? 'success' : 'danger');
            if (d.success) setTimeout(() => location.reload(), 1500);
        });
}

// LICENSE FUNCTIONS
function prepareAddLicense() {
    document.getElementById('licenseForm').reset();
    document.getElementById('licenseID').value = '';
    document.getElementById('licenseModalLabel').textContent = 'Add License';
    document.getElementById('isActiveLicense').checked = true;
}

function editLicense(id) {
    fetch(`<?= $base ?>php/scripts/global/admin/qualifications_api.php?action=get_license&id=${id}`)
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                const lic = d.data;
                document.getElementById('licenseID').value = lic.licenseID;
                document.getElementById('licenseName').value = lic.licenseName || '';
                document.getElementById('licenseNumber').value = lic.licenseNumber || '';
                document.getElementById('licenseCategory').value = lic.licenseCategory || '';
                document.getElementById('issuingAuthority').value = lic.issuingAuthority || '';
                document.getElementById('issuingCountry').value = lic.issuingCountry || 'Kenya';
                document.getElementById('licDoesNotExpire').checked = (lic.doesNotExpire === 'Y');
                document.getElementById('isActiveLicense').checked = (lic.isActive === 'Y');
                document.getElementById('restrictions').value = lic.restrictions || '';
                document.getElementById('licNotes').value = lic.notes || '';

                if (lic.issueDate && document.getElementById('licIssueDate')._flatpickr) {
                    document.getElementById('licIssueDate')._flatpickr.setDate(lic.issueDate, true);
                }
                if (lic.expiryDate && document.getElementById('licExpiryDate')._flatpickr) {
                    document.getElementById('licExpiryDate')._flatpickr.setDate(lic.expiryDate, true);
                }
                document.getElementById('licenseModalLabel').textContent = 'Edit License';
            } else {
                showToast(d.message, 'danger');
            }
        });
}

function saveLicense(event) {
    event.preventDefault();
    document.getElementById('licIssueDate').value = extractDateValue('licIssueDate');
    document.getElementById('licExpiryDate').value = extractDateValue('licExpiryDate');

    const formData = new FormData(event.target);
    formData.append('action', 'save_license');

    fetch('<?= $base ?>php/scripts/global/admin/qualifications_api.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                showToast(d.message, 'success');
                setTimeout(() => window.location.href = '?s=<?= $s ?>&p=<?= $p ?>&uid=<?= $employeeID ?>&tab=qualifications&subtab=licenses', 1500);
            } else {
                showToast(d.message, 'danger');
            }
        });
}

function deleteLicense(id) {
    if (!confirm('Delete this license?')) return;
    fetch(`<?= $base ?>php/scripts/global/admin/qualifications_api.php?action=delete_license&id=${id}`, { method: 'POST' })
        .then(r => r.json())
        .then(d => {
            showToast(d.message, d.success ? 'success' : 'danger');
            if (d.success) setTimeout(() => location.reload(), 1500);
        });
}

// TOAST FUNCTION
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' : type === 'danger' ? 'bg-danger' : 'bg-info';

    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
    toast.show();
    toastElement.addEventListener('hidden.bs.toast', function() { toastElement.remove(); });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}
</script>

<style>
.qualification-sub-tabs {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 8px;
}

.qualification-sub-tabs .nav-link {
    color: #6c757d;
    font-weight: 500;
    border-radius: 6px;
    padding: 8px 16px;
    margin: 0 4px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.qualification-sub-tabs .nav-link:hover {
    background-color: rgba(0, 123, 255, 0.1);
    color: #007bff;
}

.qualification-sub-tabs .nav-link.active {
    background-color: #007bff;
    color: #fff;
    font-weight: 600;
}

.qualification-section {
    animation: fadeInSubTab 0.3s ease-in;
}

@keyframes fadeInSubTab {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
    .qualification-sub-tabs .nav-link {
        padding: 6px 12px;
        font-size: 0.875rem;
        margin: 2px;
    }
    .qualification-sub-tabs .nav-link i { display: none; }
}
</style>

