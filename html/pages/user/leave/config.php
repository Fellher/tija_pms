<?php
/**
 * Leave Configuration Management
 * Unified interface for managing all leave-related configurations
 *
 * This module provides:
 * - Leave periods, types, and entitlements configuration
 * - Holidays and working weekends management
 * - Leave approvers and workflow settings
 * - Bradford factor threshold configuration
 * - Integration with Leave Admin modules
 *
 * @version 2.0
 * @date 2025-10-21
 */

// Security check
if(!$isAdmin && !$isValidAdmin && !$isHRManager) {
    Alert::info("You need to be logged in as a valid administrator to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

// Get current state
$state = isset($_GET['state']) ? Utility::clean_string($_GET['state']) : 'home';

// Get employee details
$employeeID = (isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) : $userDetails->ID;
$employeeDetails = Employee::employees(array('ID' => $employeeID), true, $DBConn);

// Get organization data - Get all employees using Employee class
   $allEmployees = Employee::get_all_employees($employeeDetails->orgDataID, $employeeDetails->entityID, $DBConn);
   $allOrgs = Admin::organisation_data_mini([], false, $DBConn);
   $allEntities = Data::entities_full([], false, $DBConn);

// Get entity and organization IDs
$orgDataID = isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : $employeeDetails->orgDataID;
$entityID = isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : $employeeDetails->entityID;

// Define configuration sections with categories
$configSections = [
    'basic' => [
        'title' => 'Basic Configuration',
        'icon' => 'ri-settings-3-line',
        'items' => [
            'home' => [
                'label' => 'Leave Periods',
                'icon' => 'ri-calendar-line',
                'script' => 'leave_periods.php',
                'description' => 'Manage leave periods and fiscal years'
            ],
            'leave_types' => [
                'label' => 'Leave Types',
                'icon' => 'ri-file-list-3-line',
                'script' => 'leave_types.php',
                'description' => 'Configure leave types and categories'
            ],
            'entitled_leave' => [
                'label' => 'Leave Entitlement',
                'icon' => 'ri-user-settings-line',
                'script' => 'entitled_leave.php',
                'description' => 'Assign leave entitlements to employees'
            ],
        ]
    ],
    'calendar' => [
        'title' => 'Calendar & Schedule',
        'icon' => 'ri-calendar-event-line',
        'items' => [
            'holidays' => [
                'label' => 'Holidays',
                'icon' => 'ri-calendar-event-fill',
                'script' => 'holidays.php',
                'description' => 'Manage public holidays and special days'
            ],
            'working_weekends' => [
                'label' => 'Working Weekends',
                'icon' => 'ri-calendar-check-line',
                'script' => 'working_weekends.php',
                'description' => 'Configure working weekends and special workdays'
            ],
            'leave_calendar' => [
                'label' => 'Leave Calendar',
                'icon' => 'ri-calendar-2-line',
                'script' => 'leave_calendar.php',
                'description' => 'View and manage leave calendar'
            ],
        ]
    ],
    'workflow' => [
        'title' => 'Workflow & Approvals',
        'icon' => 'ri-flow-chart',
        'items' => [
            'leave_approvers' => [
                'label' => 'Leave Approvers',
                'icon' => 'ri-user-follow-line',
                'script' => 'leave_approvers.php',
                'description' => 'Configure leave approval hierarchy'
            ],
            'bradford_factor_threshold' => [
                'label' => 'Bradford Factor',
                'icon' => 'ri-bar-chart-box-line',
                'script' => 'bradford_factor_threshold.php',
                'description' => 'Set Bradford factor thresholds and alerts'
            ],
        ]
    ]
];

// Get all config items for quick access
$allConfigItems = [];
foreach ($configSections as $sectionKey => $section) {
    foreach ($section['items'] as $itemKey => $item) {
        $allConfigItems[$itemKey] = $item;
    }
}

?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb border-bottom pb-3">
    <div>
        <h1 class="page-title fw-medium fs-24 mb-1">
            <i class="ri-settings-3-line me-2 text-primary"></i>
            Leave Configurations
            <button type="button" class="btn btn-sm btn-info-light ms-2" data-bs-toggle="modal" data-bs-target="#quickStartModal" title="Quick Start Guide">
                <i class="ri-question-line"></i>
            </button>
        </h1>
        <p class="text-muted mb-0 fs-13">Manage leave settings, policies, and configurations</p>
    </div>
   <div class="ms-md-1 ms-0">
      <nav>
         <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $s ?></a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $ss ?></a></li>
            <li class="breadcrumb-item active d-inline-flex" aria-current="page">Config</li>
         </ol>
      </nav>
   </div>
</div>

<!-- Admin Modules Quick Access -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card custom-card shadow-sm">
            <div class="card-header bg-primary-transparent">
                <h6 class="card-title mb-0">
                    <i class="ri-admin-line me-2"></i>
                    Advanced Leave Administration
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center p-3 bg-light rounded-3">
                            <div class="me-3">
                                <div class="avatar avatar-lg bg-primary-transparent rounded-circle">
                                    <i class="ri-file-list-3-line fs-20"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-semibold">Leave Policy Types</h6>
                                <p class="mb-2 text-muted fs-12">Advanced leave type management with accumulation rules</p>
                                <a href="<?php echo "{$base}html/?s=user&ss=leave&sss=admin&p=leave_policy_management" ?>" class="btn btn-sm btn-primary">
                                    <i class="ri-arrow-right-line me-1"></i> Manage Policies
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center p-3 bg-light rounded-3">
                            <div class="me-3">
                                <div class="avatar avatar-lg bg-success-transparent rounded-circle">
                                    <i class="ri-settings-4-line fs-20"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-semibold">Accumulation Policies</h6>
                                <p class="mb-2 text-muted fs-12">Configure leave accumulation and calculation rules</p>
                                <a href="<?php echo "{$base}html/?s=user&ss=leave&sss=admin&p=accumulation_policies" ?>" class="btn btn-sm btn-success">
                                    <i class="ri-arrow-right-line me-1"></i> Manage Rules
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center p-3 bg-light rounded-3">
                            <div class="me-3">
                                <div class="avatar avatar-lg bg-info-transparent rounded-circle">
                                    <i class="ri-dashboard-line fs-20"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-semibold">Leave Admin Dashboard</h6>
                                <p class="mb-2 text-muted fs-12">Comprehensive leave administration and analytics</p>
                                <a href="<?php echo "{$base}html/?s=user&ss=leave&sss=admin&p=home" ?>" class="btn btn-sm btn-info">
                                    <i class="ri-arrow-right-line me-1"></i> View Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Configuration Sections -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="ri-list-settings-line me-2"></i>
                    Configuration Options
                </h6>
            </div>
            <div class="card-body">
                <!-- Categorized Navigation Tabs -->
                <div class="row g-2 mb-3">
                    <?php foreach ($configSections as $sectionKey => $section): ?>
                        <div class="col-md-4">
                            <div class="card custom-card mb-0 border">
                                <div class="card-header bg-<?php echo $sectionKey === 'basic' ? 'primary' : ($sectionKey === 'calendar' ? 'success' : 'warning'); ?>-transparent py-2">
                                    <h6 class="card-title mb-0 fs-14">
                                        <i class="<?php echo $section['icon'] ?> me-2"></i>
                                        <?php echo $section['title'] ?>
                                    </h6>
                                </div>
                                <div class="card-body p-2">
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($section['items'] as $itemKey => $item):
                                            $isActive = ($state === $itemKey);
                                        ?>
                                            <a href="<?php echo "{$base}html/{$getString}&state={$itemKey}" ?>"
                                               class="list-group-item list-group-item-action d-flex align-items-center <?php echo $isActive ? 'active' : '' ?>"
                                               title="<?php echo $item['description'] ?>">
                                                <i class="<?php echo $item['icon'] ?> me-2"></i>
                                                <span class="flex-grow-1"><?php echo $item['label'] ?></span>
                                                <?php if ($isActive): ?>
                                                    <i class="ri-check-line ms-auto"></i>
                                                <?php endif; ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Configuration Content -->
<div class="row">
    <div class="col-12">
  <?php
        // Update query string for state changes
     $getString = str_replace("&state={$state}", "", $getString);
     $getString .= "&state={$state}";

        // Get script to include
        $scriptToInclude = isset($allConfigItems[$state]) ? $allConfigItems[$state]['script'] : 'leave_periods.php';
        $currentConfig = isset($allConfigItems[$state]) ? $allConfigItems[$state] : $allConfigItems['home'];
        ?>

        <!-- Content Header -->
        <div class="mb-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-1">
                        <i class="<?php echo $currentConfig['icon'] ?> me-2 text-primary"></i>
                        <?php echo $currentConfig['label'] ?>
                    </h5>
                    <p class="text-muted mb-0 fs-13"><?php echo $currentConfig['description'] ?></p>
                </div>
            </div>
        </div>

        <!-- Include configuration script -->
        <?php include "includes/scripts/leave/leave_configurations/{$scriptToInclude}"; ?>
    </div>
</div>

<style>
/* Custom styling for config page */
.bg-light-blue {
    background-color: #f0f7ff;
}

.list-group-item.active {
    background-color: #6c5ce7;
    border-color: #6c5ce7;
    color: white;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}

.card-header.bg-primary-transparent {
    background-color: rgba(108, 92, 231, 0.1);
}

.card-header.bg-success-transparent {
    background-color: rgba(40, 167, 69, 0.1);
}

.card-header.bg-warning-transparent {
    background-color: rgba(255, 193, 7, 0.1);
}

.avatar-lg {
    width: 3.5rem;
    height: 3.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-primary-transparent {
    background-color: rgba(108, 92, 231, 0.1);
    color: #6c5ce7;
}

.bg-success-transparent {
    background-color: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

.bg-info-transparent {
    background-color: rgba(23, 162, 184, 0.1);
    color: #17a2b8;
}

.rounded-3 {
    border-radius: 0.5rem;
}

@media (max-width: 768px) {
    .card-body .row.g-3 > div {
        margin-bottom: 1rem;
    }
}

.btn-info-light {
    background-color: rgba(23, 162, 184, 0.1);
    color: #17a2b8;
    border: none;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.btn-info-light:hover {
    background-color: #17a2b8;
    color: white;
    transform: scale(1.1);
}

.quickstart-step {
    padding: 1rem;
    border-left: 3px solid #6c5ce7;
    background: #f8f9fa;
    margin-bottom: 1rem;
    border-radius: 0.25rem;
}

.quickstart-step h6 {
    color: #6c5ce7;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.quickstart-badge {
    display: inline-block;
    width: 28px;
    height: 28px;
    background: #6c5ce7;
    color: white;
    border-radius: 50%;
    text-align: center;
    line-height: 28px;
    font-weight: 600;
    font-size: 14px;
    margin-right: 0.5rem;
}

.modal-header.bg-info-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.modal-header.bg-info-gradient .btn-close {
    filter: brightness(0) invert(1);
}
</style>

<!-- Quick Start Guide Modal -->
<div class="modal fade" id="quickStartModal" tabindex="-1" aria-labelledby="quickStartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info-gradient">
                <h5 class="modal-title" id="quickStartModalLabel">
                    <i class="ri-guide-line me-2"></i>
                    Quick Start Guide - Leave Configuration
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Introduction -->
                <div class="alert alert-info d-flex align-items-start mb-4">
                    <i class="ri-lightbulb-line fs-20 me-3 mt-1"></i>
                    <div>
                        <h6 class="alert-heading mb-2">Welcome to Leave Configuration!</h6>
                        <p class="mb-0">Follow these steps to set up your organization's leave management system. Complete each step in order for the best results.</p>
                    </div>
                </div>

                <!-- Step 1: Basic Configuration -->
                <div class="quickstart-step">
                    <h6>
                        <span class="quickstart-badge">1</span>
                        Basic Configuration
                    </h6>
                    <div class="ms-5">
                        <p class="mb-2"><strong>Setup Leave Periods</strong></p>
                        <ul class="small">
                            <li>Navigate to: <strong>Basic Configuration → Leave Periods</strong></li>
                            <li>Create your first leave period (e.g., "2025 Fiscal Year")</li>
                            <li>Set start date (e.g., January 1, 2025) and end date (e.g., December 31, 2025)</li>
                        </ul>

                        <p class="mb-2 mt-3"><strong>Configure Leave Types</strong></p>
                        <ul class="small">
                            <li>Navigate to: <strong>Basic Configuration → Leave Types</strong></li>
                            <li>Add standard types: Annual Leave (AL), Sick Leave (SL), Maternity Leave (ML)</li>
                            <li>Set clear descriptions for each type</li>
                        </ul>

                        <p class="mb-2 mt-3"><strong>Assign Leave Entitlements</strong></p>
                        <ul class="small">
                            <li>Navigate to: <strong>Basic Configuration → Leave Entitlement</strong></li>
                            <li>Assign leave days to employees based on role/seniority</li>
                            <li>Define carry-over rules</li>
                        </ul>
                    </div>
                </div>

                <!-- Step 2: Calendar & Schedule -->
                <div class="quickstart-step">
                    <h6>
                        <span class="quickstart-badge">2</span>
                        Calendar & Schedule
                    </h6>
                    <div class="ms-5">
                        <p class="mb-2"><strong>Add Public Holidays</strong></p>
                        <ul class="small">
                            <li>Navigate to: <strong>Calendar & Schedule → Holidays</strong></li>
                            <li>Add national and organizational holidays</li>
                            <li>Define if they count as leave days</li>
                        </ul>

                        <p class="mb-2 mt-3"><strong>Configure Working Weekends (if applicable)</strong></p>
                        <ul class="small">
                            <li>Navigate to: <strong>Calendar & Schedule → Working Weekends</strong></li>
                            <li>Mark any weekends that are working days</li>
                            <li>Set compensation rules</li>
                        </ul>
                    </div>
                </div>

                <!-- Step 3: Workflow & Approvals -->
                <div class="quickstart-step">
                    <h6>
                        <span class="quickstart-badge">3</span>
                        Workflow & Approvals
                    </h6>
                    <div class="ms-5">
                        <p class="mb-2"><strong>Setup Leave Approvers</strong></p>
                        <ul class="small">
                            <li>Navigate to: <strong>Workflow & Approvals → Leave Approvers</strong></li>
                            <li>Define approval hierarchy: Supervisor → Dept Head → HR Manager</li>
                            <li>Assign specific approvers to employees</li>
                        </ul>

                        <p class="mb-2 mt-3"><strong>Configure Bradford Factor (optional)</strong></p>
                        <ul class="small">
                            <li>Navigate to: <strong>Workflow & Approvals → Bradford Factor</strong></li>
                            <li>Set threshold levels for absence monitoring</li>
                            <li>Configure alert notifications</li>
                        </ul>
                    </div>
                </div>

                <!-- Advanced Features -->
                <div class="card border-primary mt-4">
                    <div class="card-header bg-primary-transparent">
                        <h6 class="mb-0">
                            <i class="ri-star-line me-2"></i>
                            Advanced Features
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>For complex leave policies:</strong></p>
                        <ul class="small mb-3">
                            <li>Use <strong>Leave Policy Types</strong> for advanced leave management with accumulation rules</li>
                            <li>Use <strong>Accumulation Policies</strong> for automated leave accrual and calculation</li>
                            <li>Access <strong>Leave Admin Dashboard</strong> for comprehensive analytics and reporting</li>
                        </ul>
                        <p class="text-muted small mb-0">
                            <i class="ri-information-line me-1"></i>
                            These advanced features are accessible via the quick access cards at the top of this page.
                        </p>
                    </div>
                </div>

                <!-- Quick Tips -->
                <div class="mt-4">
                    <h6 class="text-primary mb-3">
                        <i class="ri-lightbulb-flash-line me-2"></i>
                        Quick Tips
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <i class="ri-checkbox-circle-line text-success me-2 mt-1"></i>
                                <small><strong>Start Simple:</strong> Configure basic settings first, add advanced features later</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <i class="ri-checkbox-circle-line text-success me-2 mt-1"></i>
                                <small><strong>Test First:</strong> Test with a small group before rolling out organization-wide</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <i class="ri-checkbox-circle-line text-success me-2 mt-1"></i>
                                <small><strong>Use Clear Names:</strong> Use descriptive names for leave types and periods</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <i class="ri-checkbox-circle-line text-success me-2 mt-1"></i>
                                <small><strong>Regular Review:</strong> Review configurations quarterly to ensure accuracy</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estimated Time -->
                <div class="alert alert-light border mt-4 mb-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="ri-time-line fs-20 me-3 text-primary"></i>
                            <div>
                                <strong>Estimated Setup Time:</strong>
                                <p class="mb-0 small text-muted">Basic setup: 25-30 minutes | Full setup with advanced features: 45-60 minutes</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>
                    Close
                </button>
                <a href="<?php echo $base ?>LEAVE_CONFIG_ADMIN_QUICK_START.md" target="_blank" class="btn btn-primary">
                    <i class="ri-book-open-line me-1"></i>
                    View Full Documentation
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-show modal on first visit (optional - can be removed)
document.addEventListener('DOMContentLoaded', function() {
    // Check if user has seen the guide before
    const hasSeenGuide = localStorage.getItem('leaveConfigGuideShown');

    // Uncomment the lines below to auto-show on first visit
    // if (!hasSeenGuide) {
    //     const modal = new bootstrap.Modal(document.getElementById('quickStartModal'));
    //     modal.show();
    //     localStorage.setItem('leaveConfigGuideShown', 'true');
    // }

    // Add tooltip to help button
    const helpBtn = document.querySelector('[data-bs-target="#quickStartModal"]');
    if (helpBtn) {
        helpBtn.addEventListener('mouseenter', function() {
            this.setAttribute('title', 'Click for Quick Start Guide');
        });
    }
});
</script>


