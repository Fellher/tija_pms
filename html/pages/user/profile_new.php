<?php
/**
 * Comprehensive Employee Profile Management Page
 *
 * Features a modern tabbed interface with 9 major segments:
 * 1. Personal Details
 * 2. Employment & Job History
 * 3. Salary & Compensation
 * 4. Contact Details
 * 5. Next of Kin & Dependants
 * 6. Reporting Structure
 * 7. Qualifications
 * 8. Bank Details
 * 9. Benefits
 *
 * @package    Tija CRM
 * @subpackage Employee Management
 * @version    1.0
 * @created    2025-10-15
 */

if(!$isValidUser) {
    Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

// Get employee ID from URL or use current user
$employeeID = (isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) : $userDetails->ID;
$employeeDetails = Employee::employees(array('ID'=>$employeeID), true, $DBConn);

// Check if user has permission to view/edit this profile
$canEdit = ($isAdmin || $isValidAdmin || $employeeID == $userDetails->ID);
$canViewSalary = ($isAdmin || $isValidAdmin);

// Get current tab from URL or default to 'personal'
$currentTab = (isset($_GET['tab']) && !empty($_GET['tab'])) ? Utility::clean_string($_GET['tab']) : 'personal';

// Define profile tabs array (similar to reporting pages)
$profileTabsArray = [
    (object)[
        "title" => "Personal Details",
        "icon" => "ri-user-line",
        "slug" => "personal",
        "active" => $currentTab == "personal",
        "include" => "includes/employee_profile/personal_details_tab.php",
        "description" => "Personal information and identification documents",
        "permission" => true // Always visible
    ],
    (object)[
        "title" => "Employment",
        "icon" => "ri-briefcase-line",
        "slug" => "employment",
        "active" => $currentTab == "employment",
        "include" => "includes/employee_profile/employment_tab.php",
        "description" => "Current employment details and job history",
        "permission" => true
    ],
    (object)[
        "title" => "Compensation",
        "icon" => "ri-money-dollar-circle-line",
        "slug" => "compensation",
        "active" => $currentTab == "compensation",
        "include" => "includes/employee_profile/compensation_tab.php",
        "description" => "Salary, allowances, and compensation history",
        "permission" => $canViewSalary // Admin only
    ],
    (object)[
        "title" => "Contacts",
        "icon" => "ri-contacts-line",
        "slug" => "contacts",
        "active" => $currentTab == "contacts",
        "include" => "includes/employee_profile/contacts_tab.php",
        "description" => "Contact information and emergency contacts",
        "permission" => true
    ],
    (object)[
        "title" => "Family",
        "icon" => "ri-parent-line",
        "slug" => "family",
        "active" => $currentTab == "family",
        "include" => "includes/employee_profile/family_tab.php",
        "description" => "Next of kin and dependants",
        "permission" => true
    ],
    (object)[
        "title" => "Reporting",
        "icon" => "ri-organization-chart",
        "slug" => "reporting",
        "active" => $currentTab == "reporting",
        "include" => "includes/employee_profile/reporting_tab.php",
        "description" => "Supervisors and subordinates",
        "permission" => true
    ],
    (object)[
        "title" => "Qualifications",
        "icon" => "ri-award-line",
        "slug" => "qualifications",
        "active" => $currentTab == "qualifications",
        "include" => "includes/employee_profile/qualifications_tab.php",
        "description" => "Education, experience, skills, and certifications",
        "permission" => true
    ],
    (object)[
        "title" => "Bank Details",
        "icon" => "ri-bank-line",
        "slug" => "bank",
        "active" => $currentTab == "bank",
        "include" => "includes/employee_profile/bank_tab.php",
        "description" => "Bank account information for salary deposits",
        "permission" => $canEdit // Only editable users
    ],
    (object)[
        "title" => "Benefits",
        "icon" => "ri-shield-check-line",
        "slug" => "benefits",
        "active" => $currentTab == "benefits",
        "include" => "includes/employee_profile/benefits_tab.php",
        "description" => "Insurance and employee benefits",
        "permission" => true
    ]
];

// Filter tabs based on permissions
$visibleTabs = array_filter($profileTabsArray, function($tab) {
    return $tab->permission === true;
});

// Ensure current tab is valid and visible
$validTab = false;
foreach ($visibleTabs as $tab) {
    if ($tab->slug == $currentTab) {
        $validTab = true;
        break;
    }
}

// If current tab is not valid, default to first visible tab
if (!$validTab && !empty($visibleTabs)) {
    $currentTab = reset($visibleTabs)->slug;
}

?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <h1 class="page-title fw-medium fs-24 mb-0">Employee Profile</h1>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                <li class="breadcrumb-item"><a href="?s=core&ss=organisation&p=users">Employees</a></li>
                <li class="breadcrumb-item active" aria-current="page">Profile</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Main Profile Container -->
<div class="container-fluid">
    <div class="row">
        <!-- Left Sidebar - Profile Card -->
        <div class="col-xl-3 col-lg-4 col-md-12">
            <div class="card custom-card sticky-profile-card">

                <!-- VIEW MODE -->
                <div class="card-body text-center" id="profileViewMode">
                    <!-- Profile Image -->
                    <div class="profile-image-container mb-3">
                        <div class="position-relative d-inline-block">
                            <img src="<?= (!empty($employeeDetails->profile_image) && file_exists("{$config['DataDir']}{$employeeDetails->profile_image}"))
                                ? "{$config['DataDir']}{$employeeDetails->profile_image}"
                                : "{$base}assets/img/users/8.jpg" ?>"
                                alt="Profile"
                                class="rounded-circle profile-main-img"
                                id="mainProfileImg"
                                style="width: 150px; height: 150px; object-fit: cover; border: 5px solid #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                            <?php if ($canEdit): ?>
                            <span class="profile-edit-icon" id="profileImageEditIcon">
                                <i class="ri-camera-line"></i>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Employee Name -->
                    <h4 class="mb-1" id="displayName"><?= htmlspecialchars($employeeDetails->employeeName ?? 'Unknown') ?></h4>
                    <p class="text-muted mb-2" id="displayJobTitle"><?= htmlspecialchars($employeeDetails->jobTitle ?? 'N/A') ?></p>
                    <span class="badge bg-success-transparent" id="displayStatus"><?= htmlspecialchars($employeeDetails->employmentStatusTitle ?? 'Active') ?></span>

                    <hr class="my-3">

                    <!-- Quick Info -->
                    <div class="text-start">
                        <div class="d-flex align-items-center mb-2">
                            <i class="ri-user-line me-2 text-primary"></i>
                            <small class="text-muted">Payroll No:</small>
                            <span class="ms-auto fw-semibold" id="displayPayroll"><?= htmlspecialchars($employeeDetails->payrollNo ?? 'N/A') ?></span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="ri-mail-line me-2 text-primary"></i>
                            <small class="text-muted">Email:</small>
                            <span class="ms-auto fw-semibold text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($employeeDetails->Email ?? 'N/A') ?>" id="displayEmail">
                                <?= htmlspecialchars($employeeDetails->Email ?? 'N/A') ?>
                            </span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="ri-phone-line me-2 text-primary"></i>
                            <small class="text-muted">Phone:</small>
                            <span class="ms-auto fw-semibold" id="displayPhone"><?= htmlspecialchars($employeeDetails->phoneNo ?? 'N/A') ?></span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="ri-calendar-line me-2 text-primary"></i>
                            <small class="text-muted">Joined:</small>
                            <span class="ms-auto fw-semibold" id="displayJoined"><?= isset($employeeDetails->employmentStartDate) ? date('M Y', strtotime($employeeDetails->employmentStartDate)) : 'N/A' ?></span>
                        </div>
                    </div>

                    <?php if ($canEdit): ?>
                    <div class="mt-3 d-grid gap-2">
                        <button class="btn btn-primary btn-sm" id="editProfileBtn">
                            <i class="ri-edit-line me-1"></i> Edit Profile
                        </button>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- EDIT MODE (Hidden by default) -->
                <?php if ($canEdit): ?>
                <div class="card-body d-none" id="profileEditMode">
                    <form id="profileQuickEditForm" action="<?= $base ?>php/scripts/global/admin/manage_users.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="ID" value="<?= $employeeID ?>">
                        <input type="hidden" name="redirectUrl" value="<?= "?s={$s}&p={$p}&uid={$employeeID}&tab={$currentTab}" ?>">
                        <input type="hidden" name="organisationID" value="<?= $employeeDetails->orgDataID ?? '' ?>">
                        <input type="hidden" name="entityID" value="<?= $employeeDetails->entityID ?? '' ?>">

                        <!-- Profile Image Edit -->
                        <div class="profile-image-container mb-3 text-center">
                            <div class="position-relative d-inline-block">
                                <img src="<?= (!empty($employeeDetails->profile_image) && file_exists("{$config['DataDir']}{$employeeDetails->profile_image}"))
                                    ? "{$config['DataDir']}{$employeeDetails->profile_image}"
                                    : "{$base}assets/img/users/8.jpg" ?>"
                                    alt="Profile"
                                    class="rounded-circle"
                                    id="editProfileImg"
                                    style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #007bff;">
                                <span class="profile-edit-icon-active">
                                    <i class="ri-camera-line"></i>
                                    <input type="file" name="profile_image" id="profileImageInput" accept="image/*" style="display: none;">
                                </span>
                            </div>
                            <p class="text-muted small mt-2 mb-0">Click camera to change photo</p>
                        </div>

                        <!-- Editable Fields -->
                        <div class="mb-3">
                            <label class="form-label small text-primary">First Name</label>
                            <input type="text" class="form-control form-control-sm" name="FirstName" value="<?= htmlspecialchars($employeeDetails->FirstName ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-primary">Surname</label>
                            <input type="text" class="form-control form-control-sm" name="Surname" value="<?= htmlspecialchars($employeeDetails->Surname ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-primary">Other Names</label>
                            <input type="text" class="form-control form-control-sm" name="OtherNames" value="<?= htmlspecialchars($employeeDetails->OtherNames ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-primary">Email</label>
                            <input type="email" class="form-control form-control-sm" name="Email" value="<?= htmlspecialchars($employeeDetails->Email ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-primary">Phone Number</label>
                            <input type="text" class="form-control form-control-sm" name="phoneNumber" value="<?= htmlspecialchars($employeeDetails->phoneNo ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-primary">Payroll Number</label>
                            <input type="text" class="form-control form-control-sm" name="payrollNumber" value="<?= htmlspecialchars($employeeDetails->payrollNo ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-primary">Date of Birth</label>
                            <input type="text" class="form-control form-control-sm component-datepicker past-enabled" name="dateOfBirth" value="<?= $employeeDetails->dateOfBirth ?? '' ?>" placeholder="YYYY-MM-DD">
                        </div>




                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="ri-save-line me-1"></i> Save Changes
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" id="cancelQuickEditBtn">
                                <i class="ri-close-line me-1"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
            </div>

            <!-- Progress Card -->
            <div class="card custom-card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Profile Completion</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Profile Status</span>
                            <span class="fw-semibold" id="profileCompletionPercent">0%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                 id="profileCompletionBar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <small class="text-muted">Complete all sections to reach 100%</small>
                </div>
            </div>
        </div>

        <!-- Right Content - Tabbed Interface -->
        <div class="col-xl-9 col-lg-8 col-md-12">
            <div class="card custom-card">
                <div class="card-header border-bottom-0">
                    <!-- Tab Navigation - Generated from Array -->
                    <ul class="nav nav-tabs nav-tabs-header mb-0 flex-wrap" id="profileTabs" role="tablist">
                        <?php foreach ($visibleTabs as $tab):
                            $tabUrl = "?s={$s}&p={$p}&uid={$employeeID}&tab={$tab->slug}";
                        ?>
                        <li class="nav-item" role="presentation">
                            <a href="<?= $tabUrl ?>"
                               class="nav-link <?= $tab->active ? 'active' : '' ?>"
                               id="<?= $tab->slug ?>-tab"
                               data-tab-slug="<?= $tab->slug ?>"
                               title="<?= $tab->description ?>">
                                <i class="<?= $tab->icon ?> me-1"></i> <?= $tab->title ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="card-body">
                    <!-- Tab Content - State-based rendering -->
                    <div class="tab-content-wrapper">
                        <?php
                        // Find and display the active tab
                        foreach ($visibleTabs as $tab) {
                            if ($tab->active) {
                                echo '<div class="active-tab-content" data-tab="' . $tab->slug . '">';

                                // Include the tab content file (tab handles its own header and edit buttons)
                                if (file_exists($tab->include)) {
                                    include $tab->include;
                                } else {
                                    echo '<div class="alert alert-warning">';
                                    echo '<i class="ri-alert-line me-2"></i>';
                                    echo 'Tab content file not found: ' . $tab->include;
                                    echo '</div>';
                                }

                                echo '</div>';
                                break; // Only show one active tab
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Styles -->
<style>
.sticky-profile-card {
    position: sticky;
    top: 0px;
    z-index: 10;
}

.profile-edit-icon {
    position: absolute;
    bottom: 10px;
    right: 10px;
    background: #fff;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.15);
    cursor: pointer;
    transition: all 0.3s ease;
}

.profile-edit-icon:hover {
    background: #007bff;
    color: #fff;
    transform: scale(1.1);
}

.profile-edit-icon i {
    font-size: 1.2rem;
}

.profile-edit-icon-active {
    position: absolute;
    bottom: 5px;
    right: 5px;
    background: #007bff;
    color: #fff;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 3px 12px rgba(0,123,255,0.4);
    cursor: pointer;
    transition: all 0.3s ease;
}

.profile-edit-icon-active:hover {
    background: #0056b3;
    transform: scale(1.1);
}

.profile-edit-icon-active i {
    font-size: 1.1rem;
}

#profileEditMode .form-control {
    border: 1px solid #dee2e6;
    transition: all 0.2s ease;
}

#profileEditMode .form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

#profileEditMode .form-label {
    font-weight: 600;
    margin-bottom: 5px;
}

.nav-tabs-header {
    border-bottom: 2px solid #e9ecef;
    flex-wrap: wrap;
}

.nav-tabs-header .nav-link {
    border: none;
    color: #6c757d;
    padding: 12px 20px;
    font-weight: 500;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
    text-decoration: none;
}

.nav-tabs-header .nav-link:hover {
    color: #007bff;
    border-bottom-color: #007bff;
    background-color: #f8f9fa;
}

.nav-tabs-header .nav-link.active {
    color: #007bff;
    border-bottom-color: #007bff;
    background-color: transparent;
    font-weight: 600;
}

.active-tab-content {
    animation: fadeIn 0.4s ease-in;
}

.tab-header {
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 15px;
    margin-bottom: 20px;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.section-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.info-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-left: 4px solid #007bff;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 15px;
    transition: all 0.3s ease;
}

.info-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transform: translateY(-2px);
}

.data-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.data-row:last-child {
    border-bottom: none;
}

.data-label {
    font-weight: 600;
    color: #495057;
    min-width: 180px;
}

.data-value {
    color: #6c757d;
    text-align: right;
    flex: 1;
}

.badge-custom {
    padding: 6px 12px;
    font-size: 0.875rem;
    font-weight: 500;
}

/* Page transition */
.tab-content-wrapper {
    min-height: 400px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .sticky-profile-card {
        position: relative;
        top: 0;
    }

    .nav-tabs-header .nav-link {
        padding: 10px 12px;
        font-size: 0.875rem;
    }

    .nav-tabs-header .nav-link i {
        display: none;
    }

    .data-row {
        flex-direction: column;
    }

    .data-label {
        min-width: auto;
        margin-bottom: 5px;
    }

    .data-value {
        text-align: left;
    }
}

/* Loading states */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.spinner-border-custom {
    width: 3rem;
    height: 3rem;
    border-width: 0.3em;
}
</style>

<!-- JavaScript - Simplified for URL-based state management -->
<script>
// Global variables
const EMPLOYEE_ID = <?= $employeeID ?>;
const CAN_EDIT = <?= $canEdit ? 'true' : 'false' ?>;
const CAN_VIEW_SALARY = <?= $canViewSalary ? 'true' : 'false' ?>;
const BASE_URL = '<?= $base ?>';
const CURRENT_TAB = '<?= $currentTab ?>';

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Employee Profile - Current Tab:', CURRENT_TAB);

    initializeProfileFeatures();
});

function initializeProfileFeatures() {
    // Edit profile button - Toggle to edit mode
    const editBtn = document.getElementById('editProfileBtn');
    if (editBtn) {
        editBtn.addEventListener('click', function(e) {
            e.preventDefault();
            toggleProfileEditMode(true);
        });
    }

    // Cancel quick edit button - Toggle back to view mode
    const cancelQuickEditBtn = document.getElementById('cancelQuickEditBtn');
    if (cancelQuickEditBtn) {
        cancelQuickEditBtn.addEventListener('click', function(e) {
            e.preventDefault();
            toggleProfileEditMode(false);
        });
    }

    // Profile image upload in edit mode
    const profileEditIconActive = document.querySelector('.profile-edit-icon-active');
    if (profileEditIconActive) {
        profileEditIconActive.addEventListener('click', function(e) {
            e.preventDefault();
            const uploadInput = document.getElementById('profileImageInput');
            if (uploadInput) {
                uploadInput.click();
            }
        });
    }

    // Profile image preview in edit mode
    const profileImageInput = document.getElementById('profileImageInput');
    if (profileImageInput) {
        profileImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const editImg = document.getElementById('editProfileImg');
                    if (editImg) {
                        editImg.src = e.target.result;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Profile image edit icon in view mode
    const profileImageEditIcon = document.getElementById('profileImageEditIcon');
    if (profileImageEditIcon) {
        profileImageEditIcon.addEventListener('click', function(e) {
            e.preventDefault();
            // Switch to edit mode when clicking camera icon
            toggleProfileEditMode(true);
        });
    }

    // Handle form submission
    const quickEditForm = document.getElementById('profileQuickEditForm');
    if (quickEditForm) {
        quickEditForm.addEventListener('submit', function(e) {
            // Form will submit normally, add loading indicator
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ri-loader-4-line me-1 spinner-border spinner-border-sm"></i> Saving...';
            }
        });
    }

    // Initialize datepicker for date of birth
    initializeDatePickers();

    // Calculate profile completion
    calculateProfileCompletion();

    console.log('✓ Profile features initialized');
}

function toggleProfileEditMode(enableEdit) {
    const viewMode = document.getElementById('profileViewMode');
    const editMode = document.getElementById('profileEditMode');

    if (!viewMode || !editMode) {
        console.error('View or Edit mode elements not found');
        return;
    }

    if (enableEdit) {
        // Switch to edit mode
        viewMode.classList.add('d-none');
        editMode.classList.remove('d-none');

        // Add smooth transition
        editMode.style.opacity = '0';
        setTimeout(function() {
            editMode.style.transition = 'opacity 0.3s ease-in';
            editMode.style.opacity = '1';
        }, 10);

        console.log('✓ Edit mode activated');
    } else {
        // Switch back to view mode
        editMode.classList.add('d-none');
        viewMode.classList.remove('d-none');

        // Add smooth transition
        viewMode.style.opacity = '0';
        setTimeout(function() {
            viewMode.style.transition = 'opacity 0.3s ease-in';
            viewMode.style.opacity = '1';
        }, 10);

        console.log('✓ View mode activated');
    }
}

function initializeDatePickers() {
    // Initialize flatpickr for date fields
    if (typeof flatpickr !== 'undefined') {
        const dateInputs = document.querySelectorAll('.component-datepicker');
        dateInputs.forEach(function(input) {
            if (input.classList.contains('past-enabled')) {
                flatpickr(input, {
                    maxDate: new Date(),
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'F j, Y'
                });
            } else {
                flatpickr(input, {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'F j, Y'
                });
            }
        });
        console.log('✓ Date pickers initialized');
    }
}

function calculateProfileCompletion() {
    // Calculate based on data presence
    let totalSections = <?= count($visibleTabs) ?>;
    let completedSections = 5; // Default based on existing data in people/user_details

    const percentage = Math.round((completedSections / totalSections) * 100);

    const percentEl = document.getElementById('profileCompletionPercent');
    const barEl = document.getElementById('profileCompletionBar');

    if (percentEl) percentEl.textContent = percentage + '%';
    if (barEl) {
        barEl.style.width = percentage + '%';
        barEl.setAttribute('aria-valuenow', percentage);
    }
}
</script>

