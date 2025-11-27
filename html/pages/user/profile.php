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
$canEdit = ($isAdmin || $isValidAdmin || $isHRManager || $employeeID == $userDetails->ID);
$canViewSalary = ($isAdmin || $isValidAdmin || $isHRManager);


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
        "permission" => $canViewSalary || $isHRManager
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

/**
 * Calculate profile completion based on actual data in each tab
 * Returns completion status for each tab
 */
function calculateTabCompletion($tabSlug, $employeeDetails, $employeeID, $DBConn) {
    $completion = false;

    switch ($tabSlug) {
        case 'personal':
            // Check if essential personal details are filled
            $hasName = !empty($employeeDetails->FirstName) && !empty($employeeDetails->Surname);
            $hasEmail = !empty($employeeDetails->Email);
            $hasPhone = !empty($employeeDetails->phoneNo);
            $hasDOB = !empty($employeeDetails->dateOfBirth) && $employeeDetails->dateOfBirth != '0000-00-00';
            $hasProfileImage = !empty($employeeDetails->profile_image);
            // Consider complete if at least 4 out of 5 key fields are filled
            $completion = ($hasName && $hasEmail && $hasPhone && ($hasDOB || $hasProfileImage));
            break;

        case 'employment':
            // Check employment details
            $hasPayrollNo = !empty($employeeDetails->payrollNo);
            $hasJobTitle = !empty($employeeDetails->jobTitleID) || !empty($employeeDetails->jobTitle);
            $hasStartDate = !empty($employeeDetails->employmentStartDate) && $employeeDetails->employmentStartDate != '0000-00-00';
            $hasStatus = !empty($employeeDetails->employmentStatusID);
            // Consider complete if payroll number, job title, and start date are present
            $completion = ($hasPayrollNo && $hasJobTitle && $hasStartDate);
            break;

        case 'compensation':
            // Check if salary information exists
            $hasBasicSalary = isset($employeeDetails->basicSalary) && $employeeDetails->basicSalary > 0;
            $hasPayGrade = !empty($employeeDetails->payGradeID);
            // Consider complete if basic salary is set
            $completion = $hasBasicSalary;
            break;

        case 'contacts':
            // Check contact information
            $hasEmail = !empty($employeeDetails->Email);
            $hasPhone = !empty($employeeDetails->phoneNo);
            // Check for addresses
            $hasAddress = false;
            try {
                $addresses = EmployeeProfileExtended::get_addresses_full(['employeeID' => $employeeID], false, $DBConn);
                $hasAddress = !empty($addresses) && is_array($addresses) && count($addresses) > 0;
            } catch (Exception $e) {
                $hasAddress = false;
            }
            // Check for emergency contacts
            $hasEmergencyContact = false;
            try {
                $emergencyContacts = EmployeeProfileExtended::get_emergency_contacts_full(['employeeID' => $employeeID], false, $DBConn);
                $hasEmergencyContact = !empty($emergencyContacts) && is_array($emergencyContacts) && count($emergencyContacts) > 0;
            } catch (Exception $e) {
                $hasEmergencyContact = false;
            }
            // Consider complete if email/phone and at least one address or emergency contact
            $completion = ($hasEmail || $hasPhone) && ($hasAddress || $hasEmergencyContact);
            break;

        case 'family':
            // Check for next of kin or dependants
            try {
                $nextOfKin = EmployeeProfileExtended::get_next_of_kin(['employeeID' => $employeeID], false, $DBConn);
                $hasNextOfKin = !empty($nextOfKin) && is_array($nextOfKin) && count($nextOfKin) > 0;
            } catch (Exception $e) {
                $hasNextOfKin = false;
            }
            try {
                $dependants = EmployeeProfileExtended::get_dependants(['employeeID' => $employeeID], false, $DBConn);
                $hasDependants = !empty($dependants) && is_array($dependants) && count($dependants) > 0;
            } catch (Exception $e) {
                $hasDependants = false;
            }
            // Consider complete if at least next of kin is provided
            $completion = $hasNextOfKin;
            break;

        case 'reporting':
            // Check if supervisor is assigned (optional, so always true if employee exists)
            $hasSupervisor = isset($employeeDetails->supervisorID) && $employeeDetails->supervisorID > 0;
            // Reporting structure is optional, so consider complete if employee exists
            $completion = true; // Always true as it's optional
            break;

        case 'qualifications':
            // Check for qualifications, education, work experience, or skills
            $hasEducation = false;
            $hasWorkExperience = false;
            $hasSkills = false;

            try {
                $education = EmployeeProfileExtended::get_education(['employeeID' => $employeeID], false, $DBConn);
                $hasEducation = !empty($education) && is_array($education) && count($education) > 0;
            } catch (Exception $e) {
                $hasEducation = false;
            }

            try {
                $workExperience = EmployeeProfileExtended::get_work_experience(['employeeID' => $employeeID], false, $DBConn);
                $hasWorkExperience = !empty($workExperience) && is_array($workExperience) && count($workExperience) > 0;
            } catch (Exception $e) {
                $hasWorkExperience = false;
            }

            try {
                $skills = EmployeeProfileExtended::get_skills(['employeeID' => $employeeID], false, $DBConn);
                $hasSkills = !empty($skills) && is_array($skills) && count($skills) > 0;
            } catch (Exception $e) {
                $hasSkills = false;
            }

            // Consider complete if at least one qualification, education, work experience, or skill record exists
            $completion = ($hasEducation || $hasWorkExperience || $hasSkills);
            break;

        case 'bank':
            // Check for bank accounts
            $hasBankAccount = false;
            try {
                $bankAccounts = Employee::employee_bank_accounts(
                    array('employeeID' => $employeeID, 'Suspended' => 'N'),
                    false,
                    $DBConn
                );
                $hasBankAccount = !empty($bankAccounts) && (is_array($bankAccounts) || is_object($bankAccounts)) && count((array)$bankAccounts) > 0;
            } catch (Exception $e) {
                $hasBankAccount = false;
            }
            $completion = $hasBankAccount;
            break;

        case 'benefits':
            // Check for benefits (NHIF, NSSF, or other benefits)
            $hasNHIF = !empty($employeeDetails->nhifNumber);
            $hasNSSF = !empty($employeeDetails->nssfNumber);
            $hasOtherBenefits = false;
            try {
                $benefits = EmployeeProfileExtended::get_benefits_full(['employeeID' => $employeeID], false, $DBConn);
                $hasOtherBenefits = !empty($benefits) && is_array($benefits) && count($benefits) > 0;
            } catch (Exception $e) {
                $hasOtherBenefits = false;
            }
            // Consider complete if at least NHIF or NSSF is provided
            $completion = ($hasNHIF || $hasNSSF || $hasOtherBenefits);
            break;

        default:
            $completion = false;
            break;
    }

    return $completion;
}

// Calculate completion for each visible tab
$tabCompletion = [];
foreach ($visibleTabs as $tab) {
    $tabCompletion[$tab->slug] = calculateTabCompletion($tab->slug, $employeeDetails, $employeeID, $DBConn);
}

// Calculate overall completion percentage
$totalTabs = count($visibleTabs);
$completedTabs = array_sum($tabCompletion);
$completionPercentage = $totalTabs > 0 ? round(($completedTabs / $totalTabs) * 100) : 0;

// Determine breadcrumb destination based on role
$employeeBreadcrumbUrl = '?s=core&ss=organisation&p=users';
$isAdminLevelUser = (!empty($isSuperAdmin) || !empty($isTenantAdmin) || !empty($isAdmin) || !empty($isEntityAdmin));
if ($isAdminLevelUser) {
    $targetEntityId = !empty($employeeDetails->entityID) ? $employeeDetails->entityID : (!empty($entityID) ? $entityID : 1);
    $employeeBreadcrumbUrl = "{$base}html/?s=core&ss=admin&p=entity_details&entityID={$targetEntityId}&tab=employees";
} elseif (!empty($isHRManager)) {
    $employeeBreadcrumbUrl = "{$base}html/?s=core&ss=admin&p=users";
}

?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <h1 class="page-title fw-medium fs-24 mb-0">Employee Profile</h1>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= htmlspecialchars($employeeBreadcrumbUrl) ?>">Employees</a></li>
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
                        <div class="position-relative d-inline-block" id="profileImageContainer">
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
                        <?php if ($canEdit): ?>
                        <input type="file" id="profileImageInputView" accept="image/*" style="display: none;">
                        <?php endif; ?>
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
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Profile Status</span>
                            <span class="fw-semibold" id="profileCompletionPercent"><?= $completionPercentage ?>%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                 id="profileCompletionBar"
                                 style="width: <?= $completionPercentage ?>%"
                                 aria-valuenow="<?= $completionPercentage ?>"
                                 aria-valuemin="0"
                                 aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block mb-2">Completed: <strong id="completedTabsCount"><?= $completedTabs ?></strong> of <strong><?= $totalTabs ?></strong> sections</small>
                        <div class="tab-completion-list" style="max-height: 200px; overflow-y: auto;">
                            <?php foreach ($visibleTabs as $tab):
                                $isComplete = isset($tabCompletion[$tab->slug]) && $tabCompletion[$tab->slug];
                            ?>
                            <div class="d-flex align-items-center mb-1 small">
                                <i class="ri-<?= $isComplete ? 'check' : 'close' ?>-circle-fill me-2 text-<?= $isComplete ? 'success' : 'muted' ?>" style="font-size: 0.875rem;"></i>
                                <span class="text-<?= $isComplete ? 'success' : 'muted' ?>"><?= htmlspecialchars($tab->title) ?></span>
                            </div>
                            <?php endforeach; ?>
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

.profile-image-container {
    position: relative;
}

#profileImageContainer {
    cursor: pointer;
    transition: all 0.3s ease;
}

#profileImageContainer:hover {
    opacity: 0.9;
}

#profileImageContainer.drag-over {
    opacity: 0.7;
    transform: scale(1.05);
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
    z-index: 10;
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
const ORG_ID = '<?= $employeeDetails->orgDataID ?? '' ?>';
const ENTITY_ID = '<?= $employeeDetails->entityID ?? '' ?>';
const REDIRECT_URL = '<?= "?s={$s}&p={$p}&uid={$employeeID}&tab={$currentTab}" ?>';
const DEFAULT_PROFILE_IMAGE = '<?= (!empty($employeeDetails->profile_image) && file_exists("{$config['DataDir']}{$employeeDetails->profile_image}"))
    ? "{$config['DataDir']}{$employeeDetails->profile_image}"
    : "{$base}assets/img/users/8.jpg" ?>';
const TAB_COMPLETION = <?= json_encode($tabCompletion) ?>;
const COMPLETION_PERCENTAGE = <?= $completionPercentage ?>;
const TOTAL_TABS = <?= $totalTabs ?>;
const COMPLETED_TABS = <?= $completedTabs ?>;

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

    // Profile image upload functionality - Click, drag & drop
    const profileImageEditIcon = document.getElementById('profileImageEditIcon');
    const profileImageInputView = document.getElementById('profileImageInputView');
    const profileImageContainer = document.getElementById('profileImageContainer');

    console.log('Profile image elements:', {
        icon: !!profileImageEditIcon,
        input: !!profileImageInputView,
        container: !!profileImageContainer,
        canEdit: CAN_EDIT
    });

    // Function to handle file processing
    function processImageFile(file) {
        if (!file) {
            console.error('No file provided');
            return false;
        }

        console.log('Processing file:', file.name, file.type, file.size);

        // Validate file type
        if (!file.type.match('image.*')) {
            if (typeof showToast === 'function') {
                showToast('Please select a valid image file', 'warning');
            } else {
                alert('Please select a valid image file');
            }
            return false;
        }

        // Validate file size (10MB max)
        if (file.size > 10 * 1024 * 1024) {
            if (typeof showToast === 'function') {
                showToast('Image size must be less than 10MB', 'warning');
            } else {
                alert('Image size must be less than 10MB');
            }
            return false;
        }

        // Show preview immediately
        const reader = new FileReader();
        reader.onload = function(e) {
            const mainImg = document.getElementById('mainProfileImg');
            if (mainImg) {
                mainImg.src = e.target.result;
                console.log('Preview updated');
            }
        };
        reader.onerror = function(error) {
            console.error('Error reading file:', error);
            if (typeof showToast === 'function') {
                showToast('Error reading image file', 'error');
            } else {
                alert('Error reading image file');
            }
        };
        reader.readAsDataURL(file);

        // Upload the image via AJAX
        uploadProfileImage(file);
        return true;
    }

    // Click icon to trigger file input
    if (profileImageEditIcon && profileImageInputView) {
        profileImageEditIcon.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Icon clicked, triggering file input');
            profileImageInputView.click();
        });
    } else {
        console.warn('Profile image icon or input not found');
    }

    // Click on image container to trigger file input
    if (profileImageContainer && profileImageInputView) {
        profileImageContainer.addEventListener('click', function(e) {
            // Don't trigger if clicking the icon (it has its own handler)
            if (e.target.closest('.profile-edit-icon')) {
                return;
            }
            console.log('Image container clicked, triggering file input');
            profileImageInputView.click();
        });
    }

    // Handle file input change
    if (profileImageInputView) {
        profileImageInputView.addEventListener('change', function(e) {
            console.log('File input changed');
            const file = e.target.files[0];
            if (file) {
                processImageFile(file);
            } else {
                console.warn('No file selected');
            }
        });
    } else {
        console.warn('Profile image input not found');
    }

    // Drag and drop functionality
    if (profileImageContainer && profileImageInputView) {
        // Prevent default drag behaviors
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            profileImageContainer.addEventListener(eventName, preventDefaults, false);
        });

        // Highlight drop zone when item is dragged over it
        ['dragenter', 'dragover'].forEach(eventName => {
            profileImageContainer.addEventListener(eventName, function(e) {
                console.log('Drag over profile image');
                profileImageContainer.classList.add('drag-over');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            profileImageContainer.addEventListener(eventName, function(e) {
                profileImageContainer.classList.remove('drag-over');
            }, false);
        });

        // Handle dropped files
        profileImageContainer.addEventListener('drop', function(e) {
            console.log('File dropped');
            const dt = e.dataTransfer;
            const files = dt.files;

            if (files.length > 0) {
                const file = files[0];
                console.log('Dropped file:', file.name);
                if (processImageFile(file)) {
                    // Update the file input to reflect the dropped file
                    try {
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        profileImageInputView.files = dataTransfer.files;
                    } catch (err) {
                        console.warn('Could not update file input:', err);
                        // This is okay, the file is already being processed
                    }
                }
            }
        }, false);

        console.log('Drag and drop handlers attached');
    } else {
        console.warn('Cannot attach drag and drop - container or input missing');
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

function uploadProfileImage(file) {
    // Store original image source for error recovery
    const mainImg = document.getElementById('mainProfileImg');
    const originalImageSrc = mainImg ? mainImg.src : DEFAULT_PROFILE_IMAGE;

    // Create FormData for file upload
    const formData = new FormData();
    formData.append('profile_image', file);
    formData.append('ID', EMPLOYEE_ID);
    formData.append('organisationID', ORG_ID);
    formData.append('entityID', ENTITY_ID);
    formData.append('redirectUrl', REDIRECT_URL);

    // Add other required fields (to prevent validation errors)
    formData.append('FirstName', '<?= htmlspecialchars($employeeDetails->FirstName ?? '', ENT_QUOTES) ?>');
    formData.append('Surname', '<?= htmlspecialchars($employeeDetails->Surname ?? '', ENT_QUOTES) ?>');
    formData.append('Email', '<?= htmlspecialchars($employeeDetails->Email ?? '', ENT_QUOTES) ?>');
    formData.append('phoneNumber', '<?= htmlspecialchars($employeeDetails->phoneNo ?? '', ENT_QUOTES) ?>');
    formData.append('payrollNumber', '<?= htmlspecialchars($employeeDetails->payrollNo ?? '', ENT_QUOTES) ?>');

    // Show loading indicator
    if (mainImg) {
        mainImg.style.opacity = '0.5';
        mainImg.style.transition = 'opacity 0.3s';
    }

    // Upload via AJAX
    fetch(BASE_URL + 'php/scripts/global/admin/manage_users.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => {
        if (response.ok) {
            // Reload page to show updated image
            window.location.reload();
        } else {
            throw new Error('Upload failed');
        }
    })
    .catch(error => {
        console.error('Error uploading profile image:', error);
        if (typeof showToast === 'function') {
            showToast('Failed to upload profile image. Please try again.', 'error');
        } else {
            alert('Failed to upload profile image. Please try again.');
        }

        // Restore original image on error
        if (mainImg) {
            mainImg.style.opacity = '1';
            mainImg.src = originalImageSrc;
        }

        // Reset file input
        const fileInput = document.getElementById('profileImageInputView');
        if (fileInput) {
            fileInput.value = '';
        }
    });
}

function calculateProfileCompletion() {
    // Use the completion data calculated server-side
    const percentage = COMPLETION_PERCENTAGE;
    const completedCount = COMPLETED_TABS;
    const totalCount = TOTAL_TABS;

    const percentEl = document.getElementById('profileCompletionPercent');
    const barEl = document.getElementById('profileCompletionBar');

    if (percentEl) {
        percentEl.textContent = percentage + '%';
    }
    if (barEl) {
        barEl.style.width = percentage + '%';
        barEl.setAttribute('aria-valuenow', percentage);

        // Update progress bar color based on completion
        barEl.classList.remove('bg-success', 'bg-warning', 'bg-danger');
        if (percentage >= 80) {
            barEl.classList.add('bg-success');
        } else if (percentage >= 50) {
            barEl.classList.add('bg-warning');
        } else {
            barEl.classList.add('bg-danger');
        }
    }

    // Log completion details for debugging
    console.log('Profile Completion:', {
        percentage: percentage + '%',
        completed: completedCount,
        total: totalCount,
        details: TAB_COMPLETION
    });
}
</script>

