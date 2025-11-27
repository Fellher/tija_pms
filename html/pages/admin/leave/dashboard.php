<?php
/**
 * Leave Administration Dashboard
 * Central hub for all leave-related administrative functions
 *
 * Access: Admin and HR personnel only
 */

// Check if user is logged in
if(!$isValidUser) {
    $_SESSION['returnURL'] = $getString;
    Alert::info("You need to be logged in to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

// Check admin permissions
if (!$isAdmin && !$isValidAdmin && !$isHRManager) {
    Alert::error("Access denied. Leave Administrator privileges required.", true, array('text-center'));
    echo '<div class="text-center mt-5">
            <div class="avatar avatar-xl bg-danger-transparent mx-auto mb-4">
                <i class="ri-lock-line fs-32"></i>
            </div>
            <h4>Access Denied</h4>
            <p class="text-muted">You do not have permission to access Leave Administration.</p>
            <a href="'.$base.'html/?s=user&ss=leave&p=leave_management_enhanced" class="btn btn-primary mt-3">
                <i class="ri-arrow-left-line me-2"></i>Back to Leave Management
            </a>
        </div>';
    return;
}

// Get statistics for dashboard
$entityID = $userDetails->entityID ?? 1;
$orgDataID = $userDetails->orgDataID ?? 1;

// Leave Types
$leaveTypes = Leave::leave_types(array('Lapsed' => 'N'), false, $DBConn);
$totalLeaveTypes = is_array($leaveTypes) || is_countable($leaveTypes) ? count($leaveTypes) : 0;

// Leave Applications Statistics
$pendingApplications = Leave::leave_applications(array('leaveStatusID' => 1, 'entityID' => $entityID), false, $DBConn);
$totalPending = is_array($pendingApplications) || is_countable($pendingApplications) ? count($pendingApplications) : 0;

// All applications for current year
$currentYear = date('Y');
$allApplicationsSql = "SELECT COUNT(*) as total FROM tija_leave_applications
                       WHERE entityID = ?
                       AND YEAR(startDate) = ?
                       AND Suspended = 'N'
                       AND Lapsed = 'N'";
$allAppsResult = $DBConn->fetch_all_rows($allApplicationsSql, array(array($entityID, 'i'), array($currentYear, 's')));
$totalApplications = $allAppsResult ? $allAppsResult[0]->total : 0;

// Active Employees - Use Employee class method
$activeEmployees = Employee::get_all_employees($orgDataID, $entityID, $DBConn);
$totalEmployees = is_array($activeEmployees) || is_countable($activeEmployees) ? count($activeEmployees) : 0;

// Accumulation Policies
$policies = AccumulationPolicy::get_policies($entityID, false, $DBConn);
$totalPolicies = is_array($policies) || is_countable($policies) ? count($policies) : 0;

// Holidays
$holidays = Data::holidays(array('Suspended' => 'N', 'Lapsed' => 'N'), false, $DBConn);
$totalHolidays = is_array($holidays) || is_countable($holidays) ? count($holidays) : 0;

// Notification preferences data
$defaultNotificationChannels = array('in_app', 'email');
$leaveNotificationModule = Notification::getModules(array('moduleSlug' => 'leave'), true, $DBConn);
if ($leaveNotificationModule && is_array($leaveNotificationModule)) {
    $leaveNotificationModule = (object) $leaveNotificationModule;
}
$leaveNotificationEvents = ($leaveNotificationModule)
    ? (Notification::getEvents(array('moduleID' => $leaveNotificationModule->moduleID), false, $DBConn) ?: array())
    : array();
$notificationChannels = Notification::getChannels(array('isActive' => 'Y', 'Suspended' => 'N'), false, $DBConn) ?: array();
$notificationPrefTableExists = false;
$entityNotificationPreferences = array();

$handoverPolicies = array();
$handoverPolicyCount = 0;
$mandatoryHandoverPolicies = 0;
$handoverNomineePolicies = 0;
$recentHandoverPolicies = array();

if (!class_exists('LeaveHandoverPolicy') && file_exists($base . 'php/classes/leavehandoverpolicy.php')) {
    include_once $base . 'php/classes/leavehandoverpolicy.php';
}

if (class_exists('LeaveHandoverPolicy')) {
    $policyRows = LeaveHandoverPolicy::get_policies(array('entityID' => $entityID), $DBConn);
    if (is_array($policyRows) && count($policyRows) > 0) {
        $handoverPolicies = array_map(function($policy) {
            return is_object($policy) ? $policy : (object)$policy;
        }, $policyRows);
        $handoverPolicyCount = count($handoverPolicies);
        foreach ($handoverPolicies as $policy) {
            if (($policy->isMandatory ?? 'N') === 'Y') {
                $mandatoryHandoverPolicies++;
            }
            if (($policy->requireNomineeAcceptance ?? 'Y') === 'Y') {
                $handoverNomineePolicies++;
            }
        }
        $recentHandoverPolicies = $handoverPolicies;
        usort($recentHandoverPolicies, function($a, $b) {
            return strtotime($b->DateAdded ?? '1970-01-01') <=> strtotime($a->DateAdded ?? '1970-01-01');
        });
        $recentHandoverPolicies = array_slice($recentHandoverPolicies, 0, 3);
    }
}

$prefTableCheck = $DBConn->fetch_all_rows("SHOW TABLES LIKE 'tija_notification_entity_preferences'", array());
if ($prefTableCheck && count($prefTableCheck) > 0) {
    $notificationPrefTableExists = true;
    $channelSlugMap = array();
    foreach ($notificationChannels as $channelRow) {
        $channelObj = is_object($channelRow) ? $channelRow : (object)$channelRow;
        $channelSlugMap[$channelObj->channelID] = strtolower($channelObj->channelSlug);
    }

    $prefRows = $DBConn->fetch_all_rows(
        "SELECT eventID, channelID, isEnabled, enforceForAllUsers
         FROM tija_notification_entity_preferences
         WHERE entityID = ?",
        array(array($entityID, 'i'))
    );

    if ($prefRows) {
        foreach ($prefRows as $prefRow) {
            $pref = is_object($prefRow) ? (array)$prefRow : $prefRow;
            $eventIDKey = isset($pref['eventID']) ? (int)$pref['eventID'] : 0;
            $channelIDKey = isset($pref['channelID']) ? (int)$pref['channelID'] : 0;
            if ($eventIDKey === 0 || $channelIDKey === 0) {
                continue;
            }
            $slug = $channelSlugMap[$channelIDKey] ?? null;
            if (!$slug) {
                continue;
            }

            if (!isset($entityNotificationPreferences[$eventIDKey])) {
                $entityNotificationPreferences[$eventIDKey] = array(
                    'enabled' => array(),
                    'enforced' => array()
                );
            }

            $entityNotificationPreferences[$eventIDKey]['enabled'][$slug] = ($pref['isEnabled'] ?? 'N') === 'Y';
            $entityNotificationPreferences[$eventIDKey]['enforced'][$slug] = ($pref['enforceForAllUsers'] ?? 'N') === 'Y';
        }
    }
}

$canManageNotifications = !empty($leaveNotificationEvents) && !empty($notificationChannels);
$canResetLeaveData = ($isSuperAdmin ?? false) || ($isAdmin ?? false) || ($isValidAdmin ?? false) || ($isHRManager ?? false);
?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-24 mb-0">
            <i class="ri-admin-line me-2 text-primary"></i>
            Leave Administration Dashboard
        </h1>
        <p class="text-muted mb-0 mt-2">Manage leave policies, configurations, and monitor system-wide leave data</p>
    </div>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0);">Administration</a></li>
                <li class="breadcrumb-item active" aria-current="page">Leave Admin</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Setup Wizard Statistics and Steps -->
<?php
// Check if setup is complete
$setupComplete = true;
$setupSteps = [];

// Check Leave Types
if ($totalLeaveTypes == 0) {
    $setupComplete = false;
    $setupSteps[] = [
        'title' => 'Create Leave Types',
        'description' => 'Define the types of leave available in your organization',
        'link' => $base . 'html/?s=admin&ss=leave&p=leave_types&action=create',
        'icon' => 'ri-calendar-2-line',
        'status' => 'pending',
        'order' => 1
    ];
} else {
    $setupSteps[] = [
        'title' => 'Leave Types',
        'description' => $totalLeaveTypes . ' leave type(s) configured',
        'link' => $base . 'html/?s=admin&ss=leave&p=leave_types',
        'icon' => 'ri-calendar-2-line',
        'status' => 'complete',
        'order' => 1
    ];
}

// Check Leave Policies
$policies = Leave::leave_types(array('Lapsed' => 'N'), false, $DBConn);
$policiesWithConfig = 0;
if ($policies) {
    foreach ($policies as $policy) {
        $entitlements = Leave::leave_entitlements(['leaveTypeID' => $policy->leaveTypeID, 'entityID' => $entityID], false, $DBConn);
        if ($entitlements && count($entitlements) > 0) {
            $policiesWithConfig++;
        }
    }
}

if ($policiesWithConfig == 0 && $totalLeaveTypes > 0) {
    $setupComplete = false;
    $setupSteps[] = [
        'title' => 'Configure Leave Policies',
        'description' => 'Set up entitlements, accrual rules, and workflows for your leave types',
        'link' => $base . 'html/?s=admin&ss=leave&p=leave_policies&action=create',
        'icon' => 'ri-file-list-3-line',
        'status' => 'pending',
        'order' => 2
    ];
} else {
    $setupSteps[] = [
        'title' => 'Leave Policies',
        'description' => $policiesWithConfig . ' policy/policies configured',
        'link' => $base . 'html/?s=admin&ss=leave&p=leave_policies',
        'icon' => 'ri-file-list-3-line',
        'status' => 'complete',
        'order' => 2
    ];
}

// Check Approval Workflows
$workflows = Leave::leave_approval_policies(['entityID' => $entityID, 'Lapsed' => 'N', 'isActive' => 'Y'], false, $DBConn);
$workflowCount = $workflows ? count($workflows) : 0;

if ($workflowCount == 0) {
    $setupComplete = false;
    $setupSteps[] = [
        'title' => 'Create Approval Workflows',
        'description' => 'Define how leave applications are approved',
        'link' => $base . 'html/?s=admin&ss=leave&p=approval_workflows',
        'icon' => 'ri-flow-chart',
        'status' => 'pending',
        'order' => 3
    ];
} else {
    $setupSteps[] = [
        'title' => 'Approval Workflows',
        'description' => $workflowCount . ' workflow(s) configured',
        'link' => $base . 'html/?s=admin&ss=leave&p=approval_workflows',
        'icon' => 'ri-flow-chart',
        'status' => 'complete',
        'order' => 3
    ];
}

// Check Holidays
if ($totalHolidays == 0) {
    $setupComplete = false;
    $setupSteps[] = [
        'title' => 'Configure Holidays',
        'description' => 'Add public holidays and special days',
        'link' => $base . 'html/?s=admin&ss=leave&p=holidays',
        'icon' => 'ri-sun-line',
        'status' => 'pending',
        'order' => 4
    ];
} else {
    $setupSteps[] = [
        'title' => 'Holidays',
        'description' => $totalHolidays . ' holiday(s) configured',
        'link' => $base . 'html/?s=admin&ss=leave&p=holidays',
        'icon' => 'ri-sun-line',
        'status' => 'complete',
        'order' => 4
    ];
}

// Check Leave Periods
$periods = Leave::leave_Periods(['entityID' => $entityID, 'Suspended' => 'N'], false, $DBConn);
$periodCount = $periods ? count($periods) : 0;

if ($periodCount == 0) {
    $setupComplete = false;
    $setupSteps[] = [
        'title' => 'Set Leave Periods',
        'description' => 'Configure your 12-month leave periods',
        'link' => $base . 'html/?s=admin&ss=leave&p=leave_periods',
        'icon' => 'ri-calendar-event-line',
        'status' => 'pending',
        'order' => 5
    ];
} else {
    $setupSteps[] = [
        'title' => 'Leave Periods',
        'description' => $periodCount . ' period(s) configured',
        'link' => $base . 'html/?s=admin&ss=leave&p=leave_periods',
        'icon' => 'ri-calendar-event-line',
        'status' => 'complete',
        'order' => 5
    ];
}

// Check Handover Policies
if ($handoverPolicyCount == 0) {
    $setupComplete = false;
    $setupSteps[] = [
        'title' => 'Configure Leave Handover Policies',
        'description' => 'Define when handovers are required and who must approve them',
        'link' => $base . 'html/?s=admin&ss=leave&p=handover_policies',
        'icon' => 'ri-briefcase-4-line',
        'status' => 'pending',
        'order' => 6
    ];
} else {
    $setupSteps[] = [
        'title' => 'Leave Handover Policies',
        'description' => $handoverPolicyCount . ' policy/policies configured',
        'link' => $base . 'html/?s=admin&ss=leave&p=handover_policies',
        'icon' => 'ri-briefcase-4-line',
        'status' => 'complete',
        'order' => 6
    ];
}

// Count completed and pending steps
$completedSteps = count(array_filter($setupSteps, function($step) { return $step['status'] === 'complete'; }));
$pendingSteps = count(array_filter($setupSteps, function($step) { return $step['status'] === 'pending'; }));
$totalSteps = count($setupSteps);
$completionPercentage = $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0;
?>

<!-- Quick Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div>
                        <span class="avatar avatar-md avatar-rounded bg-primary">
                            <i class="ri-calendar-check-line fs-18"></i>
                        </span>
                    </div>
                    <div class="flex-fill ms-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <div>
                                <p class="text-muted mb-0">Leave Types</p>
                                <h4 class="fw-semibold mt-1"><?= $totalLeaveTypes ?></h4>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-1">
                            <div>
                                <a href="<?= $base ?>html/?s=admin&ss=leave&p=leave_types" class="text-primary fs-12">Manage <i class="ri-arrow-right-s-line ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div>
                        <span class="avatar avatar-md avatar-rounded bg-warning">
                            <i class="ri-time-line fs-18"></i>
                        </span>
                    </div>
                    <div class="flex-fill ms-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <div>
                                <p class="text-muted mb-0">Pending Approvals</p>
                                <h4 class="fw-semibold mt-1"><?= $totalPending ?></h4>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-1">
                            <div>
                                <span class="text-muted fs-12">Requires attention</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div>
                        <span class="avatar avatar-md avatar-rounded bg-success">
                            <i class="ri-file-list-3-line fs-18"></i>
                        </span>
                    </div>
                    <div class="flex-fill ms-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <div>
                                <p class="text-muted mb-0">Applications (<?= $currentYear ?>)</p>
                                <h4 class="fw-semibold mt-1"><?= $totalApplications ?></h4>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-1">
                            <div>
                                <a href="<?= $base ?>html/?s=admin&ss=leave&p=reports" class="text-success fs-12">View Reports <i class="ri-arrow-right-s-line ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div>
                        <span class="avatar avatar-md avatar-rounded bg-secondary">
                            <i class="ri-team-line fs-18"></i>
                        </span>
                    </div>
                    <div class="flex-fill ms-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <div>
                                <p class="text-muted mb-0">Active Employees</p>
                                <h4 class="fw-semibold mt-1"><?= $totalEmployees ?></h4>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-1">
                            <div>
                                <span class="text-muted fs-12">In entity</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $handoverCoverageRate = $handoverPolicyCount > 0 ? round(($mandatoryHandoverPolicies / max(1, $handoverPolicyCount)) * 100) : 0; ?>
<div class="row mb-4">
    <div class="col-xl-6 col-lg-12">
        <div class="card custom-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title mb-0">
                    <i class="ri-briefcase-4-line me-2 text-primary"></i>
                    Leave Handover Administration
                </div>
                <span class="badge bg-primary-subtle text-primary">
                    <?= $handoverPolicyCount ?> policy<?= $handoverPolicyCount === 1 ? '' : 'ies' ?>
                </span>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Define structured handover requirements, nominee SLAs, and escalation rules before employees go on leave.
                </p>
                <div class="row text-center g-3 mb-3">
                    <div class="col-sm-4">
                        <h4 class="fw-semibold mb-0"><?= $mandatoryHandoverPolicies ?></h4>
                        <small class="text-muted d-block">Mandatory</small>
                    </div>
                    <div class="col-sm-4">
                        <h4 class="fw-semibold mb-0"><?= $handoverNomineePolicies ?></h4>
                        <small class="text-muted d-block">Require nominee</small>
                    </div>
                    <div class="col-sm-4">
                        <h4 class="fw-semibold mb-0"><?= $handoverCoverageRate ?>%</h4>
                        <small class="text-muted d-block">Coverage rate</small>
                    </div>
                </div>
                <?php if (!empty($recentHandoverPolicies)): ?>
                    <div class="list-group list-group-flush mb-3">
                        <?php foreach ($recentHandoverPolicies as $policy): ?>
                            <?php
                            $scopeLabel = $policy->policyScope ?? 'entity_wide';
                            $scopeLabel = ucwords(str_replace('_', ' ', $scopeLabel));
                            $isMandatory = ($policy->isMandatory ?? 'N') === 'Y';
                            ?>
                            <div class="list-group-item px-0 d-flex justify-content-between align-items-start">
                                <div class="me-3">
                                    <strong><?= htmlspecialchars($policy->policyName ?? 'Untitled Policy') ?></strong>
                                    <div class="text-muted small"><?= htmlspecialchars($scopeLabel) ?></div>
                                </div>
                                <span class="badge <?= $isMandatory ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary' ?>">
                                    <?= $isMandatory ? 'Mandatory' : 'Optional' ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light border mb-3">
                        No handover policies configured yet. Get started to enforce structured leave coverage.
                    </div>
                <?php endif; ?>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-primary" data-action="open-handover-admin">
                        <i class="ri-settings-4-line me-1"></i>Manage Handover Policies
                    </button>
                    <a href="<?= $base ?>html/?s=admin&ss=leave&p=handover_policies" class="btn btn-outline-secondary">
                        <i class="ri-external-link-line me-1"></i>Open in New Tab
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions with Setup Wizard -->
<div class="row mb-4">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div class="card-title mb-0">
                        <i class="ri-flashlight-line me-2"></i>Quick Actions
                    </div>
                    <!-- Setup Wizard Toggle -->
                    <div class="d-flex align-items-center gap-3">
                        <!-- Setup Progress Indicator -->
                        <div class="d-flex align-items-center gap-2">
                            <div class="text-end">
                                <small class="text-muted d-block">Setup Progress</small>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress" style="width: 100px; height: 8px;">
                                        <div class="progress-bar <?= $setupComplete ? 'bg-success' : 'bg-warning' ?>"
                                             role="progressbar"
                                             style="width: <?= $completionPercentage ?>%"
                                             aria-valuenow="<?= $completionPercentage ?>"
                                             aria-valuemin="0"
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="fw-semibold"><?= $completionPercentage ?>%</small>
                                </div>
                            </div>
                        </div>
                        <!-- Setup Wizard Toggle Button -->
                        <button type="button"
                                class="btn btn-sm btn-<?= $setupComplete ? 'outline-success' : 'warning' ?>"
                                onclick="toggleSetupWizard()"
                                id="setupWizardToggle">
                            <i class="ri-magic-line me-1"></i>
                            <span id="setupWizardToggleText"><?= $setupComplete ? 'View Setup' : 'Setup Wizard' ?></span>
                            <i class="ri-arrow-down-s-line ms-1" id="setupWizardToggleIcon"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Setup Wizard (Collapsed by default) -->
                <div class="card border-<?= $setupComplete ? 'success' : 'warning' ?> shadow-sm mb-4 d-none" id="setupWizardCard">
                    <div class="card-header bg-<?= $setupComplete ? 'success' : 'warning' ?> bg-opacity-10">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">
                                    <i class="ri-magic-line me-2 text-<?= $setupComplete ? 'success' : 'warning' ?>"></i>
                                    Setup Wizard - Leave System Configuration
                                </h5>
                                <p class="text-muted mb-0 mt-1 small">
                                    <?= $completedSteps ?> of <?= $totalSteps ?> steps completed
                                    <?php if (!$setupComplete): ?>
                                    - Follow these steps to complete your setup
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body" id="setupWizardContent">
                        <div class="row g-3">
                            <?php
                            // Sort steps by order
                            usort($setupSteps, function($a, $b) {
                                return $a['order'] <=> $b['order'];
                            });

                            foreach ($setupSteps as $index => $step):
                            ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 <?= $step['status'] === 'complete' ? 'border-success' : 'border-warning' ?>">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start mb-2">
                                            <div class="flex-shrink-0">
                                                <?php if ($step['status'] === 'complete'): ?>
                                                <span class="badge bg-success rounded-circle p-2">
                                                    <i class="ri-check-line"></i>
                                                </span>
                                                <?php else: ?>
                                                <span class="badge bg-warning rounded-circle p-2">
                                                    <?= $step['order'] ?>
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-grow-1 ms-2">
                                                <h6 class="mb-1">
                                                    <i class="<?= $step['icon'] ?> me-1"></i>
                                                    <?= htmlspecialchars($step['title']) ?>
                                                </h6>
                                                <p class="text-muted small mb-2"><?= htmlspecialchars($step['description']) ?></p>
                                                <a href="<?= $step['link'] ?>" class="btn btn-sm btn-<?= $step['status'] === 'complete' ? 'outline-success' : 'warning' ?>">
                                                    <?= $step['status'] === 'complete' ? 'View' : 'Setup' ?>
                                                    <i class="ri-arrow-right-line ms-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (!$setupComplete): ?>
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="ri-information-line me-2"></i>
                            <strong>Tip:</strong> Complete all setup steps to ensure your leave management system is fully functional. Follow the steps in order for the best experience.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions Grid -->
                <div class="row g-3">
                    <?php
                    // Sort steps by order for quick actions display
                    usort($setupSteps, function($a, $b) {
                        return $a['order'] <=> $b['order'];
                    });

                    // Display quick actions in setup wizard order
                    foreach ($setupSteps as $step):
                        // Map step titles to quick action cards
                        $quickActionMap = [
                            'Leave Types' => [
                                'href' => $base . 'html/?s=admin&ss=leave&p=leave_types',
                                'icon' => 'ri-calendar-2-line',
                                'bg' => 'info-transparent',
                                'title' => 'Leave Types',
                                'desc' => 'Configure leave types'
                            ],
                            'Create Leave Types' => [
                                'href' => $base . 'html/?s=admin&ss=leave&p=leave_types&action=create',
                                'icon' => 'ri-calendar-2-line',
                                'bg' => 'info-transparent',
                                'title' => 'Leave Types',
                                'desc' => 'Create leave types'
                            ],
                            'Leave Policies' => [
                                'href' => $base . 'html/?s=admin&ss=leave&p=leave_policies',
                                'icon' => 'ri-file-list-3-line',
                                'bg' => 'primary-transparent',
                                'title' => 'Leave Policies',
                                'desc' => 'Manage policy types'
                            ],
                            'Configure Leave Policies' => [
                                'href' => $base . 'html/?s=admin&ss=leave&p=leave_policies&action=create',
                                'icon' => 'ri-file-list-3-line',
                                'bg' => 'primary-transparent',
                                'title' => 'Leave Policies',
                                'desc' => 'Configure policies'
                            ],
                            'Approval Workflows' => [
                                'href' => $base . 'html/?s=admin&ss=leave&p=approval_workflows',
                                'icon' => 'ri-flow-chart',
                                'bg' => 'teal-transparent',
                                'title' => 'Workflows',
                                'desc' => 'Approval processes'
                            ],
                            'Create Approval Workflows' => [
                                'href' => $base . 'html/?s=admin&ss=leave&p=approval_workflows',
                                'icon' => 'ri-flow-chart',
                                'bg' => 'teal-transparent',
                                'title' => 'Workflows',
                                'desc' => 'Create workflows'
                            ],
                            'Holidays' => [
                                'href' => $base . 'html/?s=admin&ss=leave&p=holidays',
                                'icon' => 'ri-sun-line',
                                'bg' => 'warning-transparent',
                                'title' => 'Holidays',
                                'desc' => 'Manage public holidays'
                            ],
                            'Configure Holidays' => [
                                'href' => $base . 'html/?s=admin&ss=leave&p=holidays',
                                'icon' => 'ri-sun-line',
                                'bg' => 'warning-transparent',
                                'title' => 'Holidays',
                                'desc' => 'Configure holidays'
                            ],
                            'Leave Periods' => [
                                'href' => $base . 'html/?s=admin&ss=leave&p=leave_periods',
                                'icon' => 'ri-calendar-event-line',
                                'bg' => 'danger-transparent',
                                'title' => 'Leave Periods',
                                'desc' => 'Fiscal year periods'
                            ],
                            'Set Leave Periods' => [
                                'href' => $base . 'html/?s=admin&ss=leave&p=leave_periods',
                                'icon' => 'ri-calendar-event-line',
                                'bg' => 'danger-transparent',
                                'title' => 'Leave Periods',
                                'desc' => 'Set periods'
                            ],
                            'Leave Handover Policies' => [
                                'href' => $base . 'html/?s=admin&ss=leave&p=handover_policies',
                                'icon' => 'ri-briefcase-4-line',
                                'bg' => 'secondary',
                                'title' => 'Handover',
                                'desc' => 'Review handover rules'
                            ],
                            'Configure Leave Handover Policies' => [
                                'href' => $base . 'html/?s=admin&ss=leave&p=handover_policies',
                                'icon' => 'ri-briefcase-4-line',
                                'bg' => 'secondary',
                                'title' => 'Handover',
                                'desc' => 'Create handover rules'
                            ]
                        ];

                        $action = $quickActionMap[$step['title']] ?? null;
                        if ($action):
                    ?>
                    <div class="col-md-3">
                        <a href="<?= $action['href'] ?>" class="card custom-card mb-0 text-decoration-none quick-action-card position-relative">
                            <?php if ($step['status'] === 'pending'): ?>
                            <span class="badge bg-warning position-absolute top-0 end-0 m-2" style="z-index: 1;">
                                Step <?= $step['order'] ?>
                            </span>
                            <?php elseif ($step['status'] === 'complete'): ?>
                            <span class="badge bg-success position-absolute top-0 end-0 m-2" style="z-index: 1;">
                                <i class="ri-check-line"></i>
                            </span>
                            <?php endif; ?>
                            <div class="card-body text-center">
                                <div class="avatar avatar-lg bg-<?= $action['bg'] ?> mx-auto mb-3">
                                    <i class="<?= $action['icon'] ?> fs-24"></i>
                                </div>
                                <h6 class="mb-1"><?= htmlspecialchars($action['title']) ?></h6>
                                <small class="text-muted"><?= htmlspecialchars($action['desc']) ?></small>
                            </div>
                        </a>
                    </div>
                    <?php
                        endif;
                    endforeach;

                    // Add additional quick actions that aren't in setup wizard
                    ?>
                    <div class="col-md-3">
                        <a href="<?= $base ?>html/?s=admin&ss=leave&p=accumulation_policies" class="card custom-card mb-0 text-decoration-none quick-action-card">
                            <div class="card-body text-center">
                                <div class="avatar avatar-lg bg-success-transparent mx-auto mb-3">
                                    <i class="ri-refresh-line fs-24"></i>
                                </div>
                                <h6 class="mb-1">Accumulation</h6>
                                <small class="text-muted">Configure accrual rules</small>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-3">
                        <a href="<?= $base ?>html/?s=admin&ss=leave&p=reports" class="card custom-card mb-0 text-decoration-none quick-action-card">
                            <div class="card-body text-center">
                                <div class="avatar avatar-lg bg-purple-transparent mx-auto mb-3">
                                    <i class="ri-bar-chart-line fs-24"></i>
                                </div>
                                <h6 class="mb-1">Reports</h6>
                                <small class="text-muted">Analytics & insights</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Information -->
<div class="row">
    <div class="col-xl-6">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="ri-settings-3-line me-2"></i>System Configuration
                </div>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <div class="list-group-item">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1">Leave Types</h6>
                                <small class="text-muted"><?= $totalLeaveTypes ?> active types configured</small>
                            </div>
                            <a href="<?= $base ?>html/?s=admin&ss=leave&p=leave_types" class="btn btn-sm btn-primary-light">
                                <i class="ri-settings-3-line"></i>
                            </a>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1">Accumulation Policies</h6>
                                <small class="text-muted"><?= $totalPolicies ?> policies configured</small>
                            </div>
                            <a href="<?= $base ?>html/?s=admin&ss=leave&p=accumulation_policies" class="btn btn-sm btn-primary-light">
                                <i class="ri-settings-3-line"></i>
                            </a>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1">Handover Policies</h6>
                                <small class="text-muted">
                                    <?= $handoverPolicyCount ?> configured,
                                    <?= $mandatoryHandoverPolicies ?> mandatory
                                </small>
                            </div>
                            <button type="button"
                                    class="btn btn-sm btn-primary-light"
                                    data-action="open-handover-admin">
                                <i class="ri-briefcase-4-line"></i>
                            </button>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1">Holidays</h6>
                                <small class="text-muted"><?= $totalHolidays ?> holidays configured</small>
                            </div>
                            <a href="<?= $base ?>html/?s=admin&ss=leave&p=holidays" class="btn btn-sm btn-primary-light">
                                <i class="ri-settings-3-line"></i>
                            </a>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <h6 class="mb-1">Notification Channels</h6>
                                <small class="text-muted">
                                    Configure leave event alerts per channel
                                    <?php if (!$notificationPrefTableExists): ?>
                                        <span class="text-warning ms-1">(run migration to persist)</span>
                                    <?php endif; ?>
                                </small>
                            </div>
                            <button type="button"
                                    class="btn btn-sm btn-primary"
                                    id="manageNotificationPrefsBtn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#notificationPrefsModal"
                                    <?= !$canManageNotifications ? 'disabled' : '' ?>>
                                <i class="ri-notification-3-line me-1"></i>Manage
                            </button>
                        </div>
                    </div>
                    <?php if ($canResetLeaveData): ?>
                    <div class="list-group-item bg-light border-danger-subtle">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div>
                                <h6 class="mb-1 text-danger">Reset Leave Data</h6>
                                <small class="text-muted d-block">
                                    Permanently clears all leave applications, approvals, documents, and related notifications.
                                </small>
                            </div>
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    id="openLeaveResetModalBtn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#leaveResetModal">
                                <i class="ri-delete-bin-line me-1"></i>Reset Leave System
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="ri-information-line me-2"></i>Recent Activity
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-0">
                    <i class="ri-information-line me-2"></i>
                    <strong>Welcome to Leave Administration!</strong>
                    <p class="mb-0 mt-2">Use this dashboard to manage all leave-related configurations, policies, and system settings. All changes made here will affect the entire organization.</p>
                </div>
                <div class="mt-3">
                    <a href="<?= $base ?>html/?s=user&ss=leave&p=config" class="btn btn-primary w-100">
                        <i class="ri-settings-3-line me-2"></i>System Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notification Preferences Modal -->
<div class="modal fade" id="notificationPrefsModal" tabindex="-1" aria-labelledby="notificationPrefsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="notificationPrefsModalLabel">
                        <i class="ri-notification-3-line me-1"></i>Leave Notification Channels
                    </h5>
                    <p class="text-muted mb-0 small">Enable or enforce channels per leave event for this entity.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (!$canManageNotifications): ?>
                    <div class="alert alert-warning mb-0">
                        <i class="ri-alert-line me-1"></i>
                        Leave notification events or channels are not available. Ensure the notification module is set up and try again.
                    </div>
                <?php else: ?>
                    <form id="notificationPrefsForm"
                          data-entity-id="<?= (int)$entityID ?>"
                          data-action="<?= $base ?>php/scripts/notifications/apply_entity_notification_preferences.php">
                        <div id="notificationPrefsStatus" class="alert d-none mb-3"></div>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 30%;">Leave Event</th>
                                        <th>Channel Preferences</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($leaveNotificationEvents as $eventRow): ?>
                                        <?php
                                            $eventObj = is_object($eventRow) ? $eventRow : (object)$eventRow;
                                            $eventIDValue = (int) ($eventObj->eventID ?? 0);
                                            $hasStoredPrefsForEvent = isset($entityNotificationPreferences[$eventIDValue]);
                                            $eventPrefs = $hasStoredPrefsForEvent
                                                ? $entityNotificationPreferences[$eventIDValue]
                                                : array('enabled' => array(), 'enforced' => array());
                                        ?>
                                        <tr class="notification-pref-row" data-event-id="<?= $eventIDValue ?>">
                                            <td>
                                                <strong><?= htmlspecialchars($eventObj->eventName ?? 'Leave Event') ?></strong>
                                                <div class="text-muted small">
                                                    <?= htmlspecialchars($eventObj->eventDescription ?? '') ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php foreach ($notificationChannels as $channelRow): ?>
                                                    <?php
                                                        $channelObj = is_object($channelRow) ? $channelRow : (object)$channelRow;
                                                        $channelSlug = strtolower($channelObj->channelSlug ?? '');
                                                        $enabledDefault = in_array($channelSlug, $defaultNotificationChannels, true);
                                                        $isEnabled = $hasStoredPrefsForEvent
                                                            ? ($eventPrefs['enabled'][$channelSlug] ?? false)
                                                            : $enabledDefault;
                                                        $isEnforced = $hasStoredPrefsForEvent
                                                            ? ($eventPrefs['enforced'][$channelSlug] ?? false)
                                                            : false;
                                                        $uniqueId = 'event-' . $eventIDValue . '-' . $channelSlug;
                                                    ?>
                                                    <div class="border rounded p-3 mb-3 channel-pref-row" data-channel-slug="<?= htmlspecialchars($channelSlug) ?>">
                                                        <div class="d-flex justify-content-between flex-wrap gap-3">
                                                            <div>
                                                                <div class="fw-semibold"><?= htmlspecialchars($channelObj->channelName ?? 'Channel') ?></div>
                                                                <?php if (!empty($channelObj->channelDescription)): ?>
                                                                    <small class="text-muted"><?= htmlspecialchars($channelObj->channelDescription) ?></small>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="d-flex align-items-center gap-4">
                                                                <div class="form-check form-switch mb-0">
                                                                    <input class="form-check-input pref-enabled"
                                                                           type="checkbox"
                                                                           id="enable-<?= $uniqueId ?>"
                                                                           <?= $isEnabled ? 'checked' : '' ?>>
                                                                    <label class="form-check-label" for="enable-<?= $uniqueId ?>">Enabled</label>
                                                                </div>
                                                                <div class="form-check form-switch mb-0">
                                                                    <input class="form-check-input pref-enforce"
                                                                           type="checkbox"
                                                                           id="enforce-<?= $uniqueId ?>"
                                                                           <?= $isEnforced ? 'checked' : '' ?>>
                                                                    <label class="form-check-label" for="enforce-<?= $uniqueId ?>">Enforce</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-3-line me-1"></i>Save Changes
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($canResetLeaveData): ?>
<!-- Leave Reset Modal -->
<div class="modal fade" id="leaveResetModal" tabindex="-1" aria-labelledby="leaveResetModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="leaveResetModalLabel">
                    <i class="ri-delete-bin-2-line me-1"></i>Reset Leave Applications
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <strong>Irreversible Action:</strong> This will delete <em>all</em> leave applications, approval history,
                    documents, audit logs, and leave notifications for every employee. Make sure you have completed backups
                    before proceeding.
                </div>
                <form id="leaveResetForm" data-action="<?= $base ?>php/scripts/leave/admin/reset_leave_system.php">
                    <div id="leaveResetStatus" class="alert d-none mb-3"></div>
                    <div class="mb-3">
                        <label for="leaveResetConfirmInput" class="form-label">
                            Type <span class="fw-semibold">RESET_LEAVE_DATA</span> to confirm:
                        </label>
                        <input type="text" class="form-control" id="leaveResetConfirmInput" name="confirmReset" placeholder="RESET_LEAVE_DATA" required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="1" id="leaveResetDryRun" checked>
                        <label class="form-check-label" for="leaveResetDryRun">
                            Run as dry run first (shows the operations without deleting data)
                        </label>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="ri-delete-bin-line me-1"></i>Execute Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function toggleSetupWizard() {
    const wizardCard = document.getElementById('setupWizardCard');
    const toggleIcon = document.getElementById('setupWizardToggleIcon');
    const toggleText = document.getElementById('setupWizardToggleText');

    if (wizardCard.classList.contains('d-none')) {
        wizardCard.classList.remove('d-none');
        toggleIcon.classList.remove('ri-arrow-down-s-line');
        toggleIcon.classList.add('ri-arrow-up-s-line');
        toggleText.textContent = '<?= $setupComplete ? 'Hide Setup' : 'Hide Wizard' ?>';
    } else {
        wizardCard.classList.add('d-none');
        toggleIcon.classList.remove('ri-arrow-up-s-line');
        toggleIcon.classList.add('ri-arrow-down-s-line');
        toggleText.textContent = '<?= $setupComplete ? 'View Setup' : 'Setup Wizard' ?>';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-action="open-handover-admin"]').forEach(button => {
        button.addEventListener('click', () => {
            window.location.href = '<?= $base ?>html/?s=admin&ss=leave&p=handover_policies';
        });
    });
});
</script>

<style>
.quick-action-card {
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.1);
}

.quick-action-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
    border-color: var(--bs-primary);
}

.list-group-item {
    border-left: none;
    border-right: none;
}

.list-group-item:first-child {
    border-top: none;
}

.list-group-item:last-child {
    border-bottom: none;
}

.progress {
    background-color: rgba(0,0,0,0.1);
}

#setupWizardCard {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

