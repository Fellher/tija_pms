<?php
/**
 * Leave Policy Management - Legacy Compatibility Layer
 * This file redirects to the new streamlined structure:
 * - Leave Types: leave_types.php
 * - Leave Policies: leave_policies.php
 *
 * @deprecated This file is maintained for backward compatibility only.
 *             New code should use leave_types.php and leave_policies.php directly.
 */
var_dump($isValidUser);
var_dump($isAdmin);
var_dump($isValidAdmin);
var_dump($isHRManager);

if(!$isValidUser) {
    Alert::info("You need to be logged in as a valid user to access this page - {$isValidUser} - {$isAdmin} - {$isValidAdmin} - {$isHRManager} ", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    // return;
}

// Check admin permissions
if (!$isAdmin && !$isValidAdmin && !$isHRManager) {
    Alert::error("Access denied. Admin privileges required.", true);
    return;
}

$entityID = $_SESSION['entityID'] ?? 1;
$currentUserID = $userDetails->ID;

// Get current page parameters
$action = $_GET['action'] ?? 'list';
$policyID = $_GET['policyID'] ?? null;
$leaveTypeID = $_GET['leaveTypeID'] ?? null;

// Redirect to new structure based on action
if ($action === 'leave_types' || $action === 'create_leave_type') {
    // Redirect to leave_types.php
    $redirectParams = array('s' => 'admin', 'ss' => 'leave', 'p' => 'leave_types');
    if ($action === 'create_leave_type') {
        $redirectParams['action'] = 'create';
    } elseif ($leaveTypeID) {
        $redirectParams['action'] = $action === 'view' ? 'view' : 'edit';
        $redirectParams['leaveTypeID'] = $leaveTypeID;
    }
    header('Location: ' . $base . 'html/?' . http_build_query($redirectParams));
    exit;
} elseif ($action === 'list' || $action === 'create' || $action === 'view' || $action === 'edit' || $action === 'configure_policy') {
    // Redirect to leave_policies.php
    $redirectParams = array('s' => 'admin', 'ss' => 'leave', 'p' => 'leave_policies');
    if ($action !== 'list') {
        $redirectParams['action'] = $action === 'configure_policy' ? 'create' : $action;
    }
    if ($policyID) {
        $redirectParams['policyID'] = $policyID;
    }
    if ($leaveTypeID) {
        $redirectParams['leaveTypeID'] = $leaveTypeID;
    }
    header('Location: ' . $base . 'html/?' . http_build_query($redirectParams));
    exit;
}

// If we reach here, it means no redirect was triggered (shouldn't happen, but fallback)
// Show a message and redirect to dashboard
?>
<div class="container-fluid">
    <div class="alert alert-info">
        <i class="ri-information-line me-2"></i>
        <strong>Notice:</strong> This page has been restructured. You are being redirected to the new location.
    </div>
</div>
<script>
    setTimeout(function() {
        window.location.href = '<?= $base ?>html/?s=admin&ss=leave&p=dashboard';
    }, 2000);
</script>


<!-- Help Guide Modal -->
<div class="modal fade help-modal" id="helpGuideModal" tabindex="-1" aria-labelledby="helpGuideModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="helpGuideModalLabel">
                    <i class="ri-book-open-line"></i>
                    Leave Policy Configuration - User Guide
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Introduction -->
                <div class="help-category">
                    <h3 class="help-category-title">
                        <span class="feature-icon">
                            <i class="ri-information-line"></i>
                        </span>
                        Welcome to Leave Policy Configuration
                    </h3>
                    <p class="mb-0">This comprehensive system helps you create and manage complete leave policies with full configuration including entitlements, accrual rules, eligibility criteria, carry-over policies, and approval workflows. Each policy is a complete package that defines how a specific type of leave works in your organization.</p>
                </div>

                <!-- What's New -->
                <div class="help-category">
                    <h3 class="help-category-title">
                        <span class="feature-icon">
                            <i class="ri-star-line"></i>
                        </span>
                        Key Features
                    </h3>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="feature-box p-3 bg-primary bg-opacity-10 rounded">
                                <h6 class="fw-bold text-primary mb-2">
                                    <i class="ri-route-line me-2"></i>6-Step Configuration Wizard
                                </h6>
                                <p class="small mb-0">
                                    Guided wizard walks you through complete policy setup from basic info to workflows
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-box p-3 bg-success bg-opacity-10 rounded">
                                <h6 class="fw-bold text-success mb-2">
                                    <i class="ri-progress-3-line me-2"></i>Configuration Progress Tracking
                                </h6>
                                <p class="small mb-0">
                                    Visual indicators show completion status and what still needs configuration
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-box p-3 bg-info bg-opacity-10 rounded">
                                <h6 class="fw-bold text-info mb-2">
                                    <i class="ri-links-line me-2"></i>Integrated Components
                                </h6>
                                <p class="small mb-0">
                                    Links to accumulation policies, entitlements, and approval workflows
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-box p-3 bg-warning bg-opacity-10 rounded">
                                <h6 class="fw-bold text-warning mb-2">
                                    <i class="ri-layout-grid-line me-2"></i>Tabbed Detail View
                                </h6>
                                <p class="small mb-0">
                                    View complete policy configuration across 7 organized tabs
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Understanding the Interface -->
                <div class="help-category">
                    <h3 class="help-category-title">
                        <span class="feature-icon">
                            <i class="ri-layout-line"></i>
                        </span>
                        Understanding the Policy List
                    </h3>

                    <p class="mb-3">The policy list shows all your leave policies with key information:</p>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <h6 class="text-primary mb-2">
                                        <i class="ri-progress-line me-2"></i>Configuration Progress Bar
                                    </h6>
                                    <p class="small mb-0">Shows percentage of policy configuration completed. Aim for 100% for fully functional policies.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-body">
                                    <h6 class="text-success mb-2">
                                        <i class="ri-checkbox-circle-line me-2"></i>Configuration Checklist
                                    </h6>
                                    <p class="small mb-0">Green checkmarks show completed components. Gray icons indicate pending configuration.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Card Element</th>
                                    <th>What It Shows</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Progress Bar</strong></td>
                                    <td>Overall configuration completion (green = 100%, yellow = 50-99%, red = below 50%)</td>
                                </tr>
                                <tr>
                                    <td><strong>Configuration Checklist</strong></td>
                                    <td>4 key components: Basic Info, Entitlements, Accumulation, Application Rules</td>
                                </tr>
                                <tr>
                                    <td><strong>Quick Stats</strong></td>
                                    <td>Days/Year entitlement and number of tiers configured</td>
                                </tr>
                                <tr>
                                    <td><strong>Action Buttons</strong></td>
                                    <td>View (eye), Configure (gear), Suspend/Activate (play/pause)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 6-Step Wizard Guide -->
                <div class="help-category">
                    <h3 class="help-category-title">
                        <span class="feature-icon">
                            <i class="ri-guide-line"></i>
                        </span>
                        6-Step Configuration Wizard
                    </h3>

                    <p class="mb-3">Creating a new policy guides you through 6 comprehensive steps:</p>

                    <div class="help-step">
                        <div class="d-flex align-items-start">
                            <span class="help-step-number">1</span>
                            <div>
                                <h6 class="fw-bold mb-2">Basic Information</h6>
                                <ul class="mb-0">
                                    <li><strong>Policy Name:</strong> E.g., "Annual Leave", "Sick Leave", "Maternity Leave"</li>
                                    <li><strong>Policy Code:</strong> Short uppercase code (e.g., "ANNUAL", "SICK")</li>
                                    <li><strong>Description:</strong> Explain the purpose of this leave type</li>
                                    <li><strong>Settings:</strong> Paid/Unpaid, Requires Approval, Status</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="help-step">
                        <div class="d-flex align-items-start">
                            <span class="help-step-number">2</span>
                            <div>
                                <h6 class="fw-bold mb-2">Entitlement & Accrual Rules</h6>
                                <ul class="mb-0">
                                    <li><strong>Annual Entitlement:</strong> Total days per year (e.g., 21 days)</li>
                                    <li><strong>Accrual Method:</strong> Upfront, Monthly, Quarterly, Anniversary</li>
                                    <li><strong>Accrual Rate:</strong> Auto-calculated based on method</li>
                                    <li><strong>Proration:</strong> Pro-rate for mid-year joiners</li>
                                    <li><strong>Negative Balance:</strong> Allow leave advances</li>
                                    <li><strong>Maximum Accrual:</strong> Cap on total balance</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="help-step">
                        <div class="d-flex align-items-start">
                            <span class="help-step-number">3</span>
                            <div>
                                <h6 class="fw-bold mb-2">Carry-Over Policies</h6>
                                <ul class="mb-0">
                                    <li><strong>Allow Carry-Over:</strong> Can unused days roll to next period?</li>
                                    <li><strong>Maximum Carry-Over:</strong> Limit on carried days</li>
                                    <li><strong>Expiry Period:</strong> When carried days expire (3, 6, 12 months)</li>
                                    <li><strong>Use It or Lose It:</strong> Forfeit unused days</li>
                                    <li><strong>Cash-Out:</strong> Convert unused days to payment</li>
                                    <li><strong>Usage Priority:</strong> Use carried days first</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="help-step">
                        <div class="d-flex align-items-start">
                            <span class="help-step-number">4</span>
                            <div>
                                <h6 class="fw-bold mb-2">Eligibility Criteria</h6>
                                <ul class="mb-0">
                                    <li><strong>Minimum Service:</strong> Months required before eligibility</li>
                                    <li><strong>Probation Rule:</strong> Available during probation or not</li>
                                    <li><strong>Gender Restriction:</strong> All, Female only, Male only</li>
                                    <li><strong>Employment Type:</strong> Permanent, Contract, Temporary, Part-time</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="help-step">
                        <div class="d-flex align-items-start">
                            <span class="help-step-number">5</span>
                            <div>
                                <h6 class="fw-bold mb-2">Application Rules & Constraints</h6>
                                <ul class="mb-0">
                                    <li><strong>Minimum Notice:</strong> Days before leave starts (0 = same day)</li>
                                    <li><strong>Max Advance Booking:</strong> How far ahead can be booked</li>
                                    <li><strong>Backdated Applications:</strong> Not allowed / Allowed / With approval</li>
                                    <li><strong>Days Range:</strong> Min and max days per application</li>
                                    <li><strong>Half-Day:</strong> Allow half-day applications</li>
                                    <li><strong>Documentation:</strong> Require supporting documents</li>
                                    <li><strong>Blackout Periods:</strong> Dates when leave cannot be taken</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="help-step">
                        <div class="d-flex align-items-start">
                            <span class="help-step-number">6</span>
                            <div>
                                <h6 class="fw-bold mb-2">Workflows & Policy Links</h6>
                                <ul class="mb-0">
                                    <li><strong>Accumulation Policy:</strong> Link to automatic accrual policy</li>
                                    <li><strong>Approval Workflow:</strong> Assign specific approval chain</li>
                                    <li><strong>Quick Access:</strong> Direct links to create/manage related components</li>
                                    <li><strong>Save Options:</strong> Save as draft or create complete policy</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Viewing & Managing Policies -->
                <div class="help-category">
                    <h3 class="help-category-title">
                        <span class="feature-icon">
                            <i class="ri-eye-line"></i>
                        </span>
                        Viewing & Managing Policies
                    </h3>

                    <div class="help-step">
                        <h6 class="fw-bold text-primary mb-2">Policy Detail View (7 Tabs)</h6>
                        <p class="mb-2">Click "View" on any policy to see complete configuration across 7 tabs:</p>
                        <ol class="small">
                            <li><strong>Basic Info</strong> - Core policy details and metadata</li>
                            <li><strong>Entitlements</strong> - All tiers with linked accumulation policies</li>
                            <li><strong>Carry-Over</strong> - Complete carry-over rules and limits</li>
                            <li><strong>Eligibility</strong> - Who can use this policy</li>
                            <li><strong>Application Rules</strong> - How employees can apply</li>
                            <li><strong>Workflows</strong> - Linked policies and approval workflows</li>
                            <li><strong>Usage Stats</strong> - Application metrics and reports</li>
                        </ol>
                    </div>

                    <div class="help-step">
                        <h6 class="fw-bold text-success mb-2">Configuration Status Indicators</h6>
                        <ul class="mb-0">
                            <li><strong>Green Progress Bar (100%):</strong> Policy fully configured and ready</li>
                            <li><strong>Yellow Progress Bar (50-99%):</strong> Partial configuration, needs attention</li>
                            <li><strong>Red Progress Bar (&lt;50%):</strong> Minimal configuration, requires setup</li>
                            <li><strong>Green Checkmarks:</strong> Component configured</li>
                            <li><strong>Gray Circles:</strong> Component needs configuration</li>
                        </ul>
                    </div>
                </div>

                <!-- Dashboard Statistics -->
                <div class="help-category">
                    <h3 class="help-category-title">
                        <span class="feature-icon">
                            <i class="ri-dashboard-line"></i>
                        </span>
                        Dashboard Statistics
                    </h3>

                    <p class="mb-3">The top dashboard shows 4 key metrics:</p>

                    <div class="row g-2">
                        <div class="col-6">
                            <div class="p-2 bg-primary bg-opacity-10 rounded text-center">
                                <i class="ri-file-list-line text-primary"></i>
                                <strong class="d-block">Total Policies</strong>
                                <small class="text-muted">All policies in system</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-success bg-opacity-10 rounded text-center">
                                <i class="ri-checkbox-circle-line text-success"></i>
                                <strong class="d-block">Active Policies</strong>
                                <small class="text-muted">Currently available</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-warning bg-opacity-10 rounded text-center">
                                <i class="ri-settings-3-line text-warning"></i>
                                <strong class="d-block">Need Configuration</strong>
                                <small class="text-muted">Incomplete policies</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-info bg-opacity-10 rounded text-center">
                                <i class="ri-pause-circle-line text-info"></i>
                                <strong class="d-block">Suspended</strong>
                                <small class="text-muted">Temporarily disabled</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Common Actions -->
                <div class="help-category">
                    <h3 class="help-category-title">
                        <span class="feature-icon">
                            <i class="ri-functions-line"></i>
                        </span>
                        Common Actions
                    </h3>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Action</th>
                                    <th>How to Perform</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><i class="ri-add-line text-primary me-2"></i><strong>Create Policy</strong></td>
                                    <td>Click "Create Policy" button</td>
                                    <td>Launch 6-step wizard to create comprehensive policy</td>
                                </tr>
                                <tr>
                                    <td><i class="ri-eye-line text-info me-2"></i><strong>View Policy</strong></td>
                                    <td>Click eye icon on policy card</td>
                                    <td>View complete configuration across 7 tabs</td>
                                </tr>
                                <tr>
                                    <td><i class="ri-settings-3-line text-success me-2"></i><strong>Configure</strong></td>
                                    <td>Click gear icon on policy card</td>
                                    <td>Edit policy using the wizard or add missing components</td>
                                </tr>
                                <tr>
                                    <td><i class="ri-pause-line text-warning me-2"></i><strong>Suspend/Activate</strong></td>
                                    <td>Click play/pause icon</td>
                                    <td>Temporarily disable or re-enable a policy</td>
                                </tr>
                                <tr>
                                    <td><i class="ri-search-line text-secondary me-2"></i><strong>Search</strong></td>
                                    <td>Use search box at top</td>
                                    <td>Find policies by name, code, or description</td>
                                </tr>
                                <tr>
                                    <td><i class="ri-filter-line text-secondary me-2"></i><strong>Filter</strong></td>
                                    <td>Use dropdown filters</td>
                                    <td>Filter by status or configuration completion</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Policy Components Explained -->
                <div class="help-category">
                    <h3 class="help-category-title">
                        <span class="feature-icon">
                            <i class="ri-puzzle-line"></i>
                        </span>
                        Policy Components Explained
                    </h3>

                    <div class="accordion" id="componentsAccordion">
                        <!-- Basic Information -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#component1">
                                    <i class="ri-file-list-line me-2 text-primary"></i>
                                    <strong>1. Basic Information (Always Required)</strong>
                                </button>
                            </h2>
                            <div id="component1" class="accordion-collapse collapse show" data-bs-parent="#componentsAccordion">
                                <div class="accordion-body">
                                    <p><strong>What it is:</strong> Core identification and basic settings for the policy.</p>
                                    <p><strong>Fields:</strong></p>
                                    <ul class="small">
                                        <li>Policy Name - Clear, descriptive name</li>
                                        <li>Policy Code - Unique identifier (auto-generated available)</li>
                                        <li>Description - Detailed explanation</li>
                                        <li>Paid/Unpaid - Whether leave is paid</li>
                                        <li>Requires Approval - Auto-approve or manual approval</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Entitlements -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#component2">
                                    <i class="ri-calendar-check-line me-2 text-success"></i>
                                    <strong>2. Entitlements & Accrual (Recommended)</strong>
                                </button>
                            </h2>
                            <div id="component2" class="accordion-collapse collapse" data-bs-parent="#componentsAccordion">
                                <div class="accordion-body">
                                    <p><strong>What it is:</strong> Defines how much leave employees get and how it accrues.</p>
                                    <p><strong>Fields:</strong></p>
                                    <ul class="small">
                                        <li>Annual Entitlement - Days per year (e.g., 21)</li>
                                        <li>Accrual Method - When leave is granted (upfront vs periodic)</li>
                                        <li>Proration - Fair calculation for partial years</li>
                                        <li>Negative Balance - Borrow against future entitlement</li>
                                        <li>Maximum Accrual - Prevent unlimited accumulation</li>
                                    </ul>
                                    <p class="mb-0"><strong>Example:</strong> 21 days/year with monthly accrual = 1.75 days per month</p>
                                </div>
                            </div>
                        </div>

                        <!-- Carry-Over -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#component3">
                                    <i class="ri-refresh-line me-2 text-info"></i>
                                    <strong>3. Carry-Over Policies (Optional)</strong>
                                </button>
                            </h2>
                            <div id="component3" class="accordion-collapse collapse" data-bs-parent="#componentsAccordion">
                                <div class="accordion-body">
                                    <p><strong>What it is:</strong> Rules for unused leave at year-end.</p>
                                    <p><strong>Common Scenarios:</strong></p>
                                    <ul class="small">
                                        <li><strong>Full Carry-Over:</strong> Allow + No limit + No expiry</li>
                                        <li><strong>Limited Carry-Over:</strong> Allow + Max 5 days + Expires in 3 months</li>
                                        <li><strong>Use It or Lose It:</strong> No carry-over allowed</li>
                                        <li><strong>Cash-Out Option:</strong> Convert to payment instead</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Eligibility -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#component4">
                                    <i class="ri-shield-check-line me-2 text-warning"></i>
                                    <strong>4. Eligibility Criteria (Optional)</strong>
                                </button>
                            </h2>
                            <div id="component4" class="accordion-collapse collapse" data-bs-parent="#componentsAccordion">
                                <div class="accordion-body">
                                    <p><strong>What it is:</strong> Who can use this leave policy.</p>
                                    <p><strong>Examples:</strong></p>
                                    <ul class="small">
                                        <li><strong>Maternity Leave:</strong> Female only, 6 months service</li>
                                        <li><strong>Study Leave:</strong> Permanent employees, 12 months service</li>
                                        <li><strong>Sick Leave:</strong> All employees, immediate eligibility</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Application Rules -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#component5">
                                    <i class="ri-calendar-event-line me-2 text-danger"></i>
                                    <strong>5. Application Rules (Recommended)</strong>
                                </button>
                            </h2>
                            <div id="component5" class="accordion-collapse collapse" data-bs-parent="#componentsAccordion">
                                <div class="accordion-body">
                                    <p><strong>What it is:</strong> Constraints on how leave can be applied for.</p>
                                    <p><strong>Typical Configurations:</strong></p>
                                    <ul class="small">
                                        <li><strong>Annual Leave:</strong> 14 days notice, max 21 days, half-day allowed</li>
                                        <li><strong>Sick Leave:</strong> 0 days notice (emergency), requires medical cert</li>
                                        <li><strong>Maternity:</strong> 30 days notice, requires documentation</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Workflows -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#component6">
                                    <i class="ri-flow-chart me-2 text-purple"></i>
                                    <strong>6. Workflows & Links (Optional)</strong>
                                </button>
                            </h2>
                            <div id="component6" class="accordion-collapse collapse" data-bs-parent="#componentsAccordion">
                                <div class="accordion-body">
                                    <p><strong>What it is:</strong> Integration with other leave management components.</p>
                                    <ul class="small">
                                        <li><strong>Accumulation Policy:</strong> Automates leave accrual calculations</li>
                                        <li><strong>Approval Workflow:</strong> Custom approval chains for this policy</li>
                                        <li><strong>Quick Links:</strong> Jump to related management pages</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Best Practices -->
                <div class="help-category">
                    <h3 class="help-category-title">
                        <span class="feature-icon">
                            <i class="ri-lightbulb-line"></i>
                        </span>
                        Best Practices & Tips
                    </h3>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-body">
                                    <h6 class="text-success mb-3">
                                        <i class="ri-checkbox-circle-line me-2"></i>
                                        Do's
                                    </h6>
                                    <ul class="small mb-0">
                                        <li>Complete all 6 wizard steps for full functionality</li>
                                        <li>Link accumulation policies for automatic accrual</li>
                                        <li>Set realistic notice periods and limits</li>
                                        <li>Test policies with a small group first</li>
                                        <li>Use "Save as Draft" if not ready to activate</li>
                                        <li>Review configuration progress regularly</li>
                                        <li>Document special rules in blackout periods</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-danger">
                                <div class="card-body">
                                    <h6 class="text-danger mb-3">
                                        <i class="ri-close-circle-line me-2"></i>
                                        Don'ts
                                    </h6>
                                    <ul class="small mb-0">
                                        <li>Don't activate incomplete policies (check progress bar)</li>
                                        <li>Don't change entitlements mid-year without communication</li>
                                        <li>Don't set unrealistic maximum accrual limits</li>
                                        <li>Don't forget to configure eligibility criteria</li>
                                        <li>Don't suspend policies with pending applications</li>
                                        <li>Don't skip linking accumulation policies</li>
                                        <li>Don't ignore the "Needs Configuration" warning</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tip-box mt-3">
                        <i class="ri-lightbulb-flash-line"></i>
                        <strong>Pro Tip:</strong> Use the configuration progress bar to identify policies that need attention. Policies below 50% completion may not function correctly for employees.
                    </div>
                </div>

                <!-- Sidebar Navigation Guide -->
                <div class="help-category">
                    <h3 class="help-category-title">
                        <span class="feature-icon">
                            <i class="ri-menu-line"></i>
                        </span>
                        Sidebar Navigation
                    </h3>

                    <p class="mb-3">The left sidebar provides quick access to:</p>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <h6 class="mb-2"><i class="ri-list-check me-2"></i>All Leave Policies</h6>
                                <p class="small mb-0">Main list with progress tracking</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <h6 class="mb-2"><i class="ri-add-circle-line me-2"></i>Create New Policy</h6>
                                <p class="small mb-0">Launch configuration wizard</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <h6 class="mb-2"><i class="ri-bar-chart-line me-2"></i>Policy Analytics</h6>
                                <p class="small mb-0">View usage statistics</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <h6 class="mb-2"><i class="ri-list-settings-line me-2"></i>Leave Types</h6>
                                <p class="small mb-0">Basic leave type management</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <h6 class="mb-2"><i class="ri-refresh-line me-2"></i>Accumulation Policies</h6>
                                <p class="small mb-0">Manage automatic accrual</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <h6 class="mb-2"><i class="ri-award-line me-2"></i>Entitlements</h6>
                                <p class="small mb-0">Configure entitlement tiers</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <h6 class="mb-2"><i class="ri-flow-chart me-2"></i>Approval Workflows</h6>
                                <p class="small mb-0">Set up approval chains</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <h6 class="mb-2"><i class="ri-dashboard-2-line me-2"></i>Admin Dashboard</h6>
                                <p class="small mb-0">Return to main dashboard</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Troubleshooting -->
                <div class="help-category">
                    <h3 class="help-category-title">
                        <span class="feature-icon">
                            <i class="ri-tools-line"></i>
                        </span>
                        Troubleshooting & FAQs
                    </h3>

                    <div class="help-step">
                        <h6 class="fw-bold text-primary mb-2">Q: What does the configuration progress bar mean?</h6>
                        <p class="mb-0"><strong>A:</strong> It shows how many of the 4 key components are configured: Basic Info (always 100%), Entitlements, Accumulation Policy, and Application Rules. Aim for 100% for fully functional policies.</p>
                    </div>

                    <div class="help-step">
                        <h6 class="fw-bold text-primary mb-2">Q: Can I skip steps in the wizard?</h6>
                        <p class="mb-0"><strong>A:</strong> The wizard validates each step before proceeding. However, you can use "Save as Draft" to save partial configurations and complete them later.</p>
                    </div>

                    <div class="help-step">
                        <h6 class="fw-bold text-primary mb-2">Q: What's the difference between Leave Types and Leave Policies?</h6>
                        <p class="mb-0"><strong>A:</strong> <strong>Leave Types</strong> are basic categories. <strong>Leave Policies</strong> are complete configurations including entitlements, rules, eligibility, and workflows. Use this page for comprehensive policy management.</p>
                    </div>

                    <div class="help-step">
                        <h6 class="fw-bold text-primary mb-2">Q: Why link to Accumulation Policies?</h6>
                        <p class="mb-0"><strong>A:</strong> Accumulation policies automate leave accrual based on rules (tenure, performance, etc.). Without a link, entitlements must be allocated manually.</p>
                    </div>

                    <div class="help-step">
                        <h6 class="fw-bold text-primary mb-2">Q: Can I edit policies that are in use?</h6>
                        <p class="mb-0"><strong>A:</strong> Yes, but changes will affect future leave applications. Consider creating a new version instead of modifying active policies with pending applications.</p>
                    </div>

                    <div class="help-step">
                        <h6 class="fw-bold text-primary mb-2">Q: What happens if I suspend a policy?</h6>
                        <p class="mb-0"><strong>A:</strong> Suspended policies are hidden from employees and cannot be used for new applications. Existing applications remain valid.</p>
                    </div>
                </div>

                <!-- Quick Reference -->
                <div class="help-category">
                    <h3 class="help-category-title">
                        <span class="feature-icon">
                            <i class="ri-bookmark-line"></i>
                        </span>
                        Quick Reference Icons
                    </h3>

                    <div class="row g-2">
                        <div class="col-md-4 col-6">
                            <div class="text-center p-2 bg-light rounded">
                                <i class="ri-checkbox-circle-fill text-success fs-4"></i>
                                <p class="small mb-0 mt-1">Configured</p>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="text-center p-2 bg-light rounded">
                                <i class="ri-checkbox-blank-circle-line text-muted fs-4"></i>
                                <p class="small mb-0 mt-1">Not Configured</p>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="text-center p-2 bg-light rounded">
                                <i class="ri-eye-line text-info fs-4"></i>
                                <p class="small mb-0 mt-1">View</p>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="text-center p-2 bg-light rounded">
                                <i class="ri-settings-3-line text-success fs-4"></i>
                                <p class="small mb-0 mt-1">Configure</p>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="text-center p-2 bg-light rounded">
                                <i class="ri-play-line text-success fs-4"></i>
                                <p class="small mb-0 mt-1">Activate</p>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="text-center p-2 bg-light rounded">
                                <i class="ri-pause-line text-warning fs-4"></i>
                                <p class="small mb-0 mt-1">Suspend</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Support -->
                <div class="alert alert-info">
                    <i class="ri-customer-service-line me-2"></i>
                    <strong>Need More Help?</strong> Contact your system administrator or HR department for additional assistance with leave policy configuration.
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Close
                </button>
                <button type="button" class="btn btn-primary" onclick="printHelpGuide()">
                    <i class="ri-printer-line me-1"></i>Print Guide
                </button>
            </div>
        </div>
    </div>
</div>
<?php
// var_dump($p);?>
<script>
// Show help guide modal
function showHelpGuide() {
    const helpModal = new bootstrap.Modal(document.getElementById('helpGuideModal'));
    helpModal.show();
}

// Print help guide
function printHelpGuide() {
    window.print();
}

// Export policies function
function exportPolicies() {
    window.location.href = '<?= $base ?>php/scripts/leave/export_policies.php?format=excel';
}

// Toggle policy status
function togglePolicyStatus(policyID, currentStatus) {
    if (confirm('Are you sure you want to ' + (currentStatus === 'Y' ? 'activate' : 'suspend') + ' this policy?')) {
        window.location.href = '?s=admin&ss=leave&p=leave_policy_management&action=toggle_status&policyID=' + policyID;
    }
}
</script>
<?php echo $config['siteURL'];
// var_dump($p);
?>

