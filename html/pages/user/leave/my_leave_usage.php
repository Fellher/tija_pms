<?php
/**
 * My Leave Usage - Enhanced Analytics Dashboard
 *
 * Comprehensive leave usage analytics with:
 * - Period navigation
 * - Visual analytics (charts)
 * - Detailed breakdowns
 * - Historical comparisons
 * - Export capabilities
 */

if(!$isValidUser) {
    Alert::info("You need to be logged in to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

// Get employee context
$employeeID = (isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) : $userDetails->ID;
$employeeDetails = Employee::employees(array('ID'=>$employeeID), true, $DBConn);
$orgDataID = isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : $employeeDetails->orgDataID;
$entityID = isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : $employeeDetails->entityID;

// Get selected period (default to current)
$selectedPeriodID = isset($_GET['periodID']) ? Utility::clean_string($_GET['periodID']) : null;

// Get all leave periods for dropdown
$allPeriods = Leave::leave_periods(array('Lapsed'=>'N', 'entityID'=>$entityID), false, $DBConn);

// Determine current period
$today = date('Y-m-d');
$currentPeriod = null;
if ($allPeriods && is_array($allPeriods)) {
    foreach ($allPeriods as $period) {
        if ($today >= $period->leavePeriodStartDate && $today <= $period->leavePeriodEndDate) {
            $currentPeriod = $period;
            break;
        }
    }
}

// If no period selected, use current period
if (!$selectedPeriodID && $currentPeriod) {
    $selectedPeriodID = $currentPeriod->leavePeriodID;
}

// Get selected period details
$selectedPeriod = null;
if ($selectedPeriodID) {
    $selectedPeriod = Leave::leave_periods(array('leavePeriodID'=>$selectedPeriodID), true, $DBConn);
}

// Get leave types
$leaveTypes = Leave::leave_types(array('Lapsed'=>'N'), false, $DBConn);

// Calculate comprehensive usage data
$usageData = array();
$totalEntitlement = 0;
$totalUsed = 0;
$totalPending = 0;
$totalScheduled = 0;
$totalAvailable = 0;

if ($leaveTypes && is_array($leaveTypes)) {
    foreach ($leaveTypes as $leaveType) {
        $leaveEntitlement = Leave::leave_entitlement(
            array('entityID'=>$entityID, 'leaveTypeID'=>$leaveType->leaveTypeID),
            true,
            $DBConn
        );

        if (!$leaveEntitlement) continue;

        // Get applications for this period and leave type
        $whereArr = array(
            'employeeID' => $employeeID,
            'leaveTypeID' => $leaveType->leaveTypeID,
            'Lapsed' => 'N'
        );

        if ($selectedPeriod) {
            $whereArr['leavePeriodID'] = $selectedPeriod->leavePeriodID;
        }

        $applications = Leave::leave_applications($whereArr, false, $DBConn);

        // Calculate usage statistics
        $pending = 0;
        $scheduled = 0;
        $taken = 0;
        $rejected = 0;
        $cancelled = 0;
        $applicationCount = 0;

        if ($applications && is_array($applications)) {
            $applicationCount = count($applications);
            foreach ($applications as $app) {
                $days = $app->noOfDays ?: Leave::countWeekdays($app->startDate, $app->endDate);

                switch ($app->leaveStatusID) {
                    case 1: // Draft/Scheduled
                        $scheduled += $days;
                        break;
                    case 2: // Submitted/Pending
                    case 3: // Under Review
                        $pending += $days;
                        break;
                    case 4: // Approved/Taken
                        $taken += $days;
                        break;
                    case 5: // Rejected
                        $rejected += $days;
                        break;
                    case 6: // Cancelled
                        $cancelled += $days;
                        break;
                }
            }
        }

        $entitlement = $leaveEntitlement->entitlement ?? 0;
        $used = $taken;
        $available = max(0, $entitlement - ($pending + $scheduled + $taken));
        $utilization = $entitlement > 0 ? round(($used / $entitlement) * 100, 1) : 0;

        $usageData[] = array(
            'leaveType' => $leaveType,
            'entitlement' => $entitlement,
            'pending' => $pending,
            'scheduled' => $scheduled,
            'taken' => $taken,
            'rejected' => $rejected,
            'cancelled' => $cancelled,
            'available' => $available,
            'utilization' => $utilization,
            'applicationCount' => $applicationCount,
            'leaveEntitlementID' => $leaveEntitlement->leaveEntitlementID ?? null
        );

        $totalEntitlement += $entitlement;
        $totalUsed += $used;
        $totalPending += $pending;
        $totalScheduled += $scheduled;
        $totalAvailable += $available;
    }
}

// Calculate overall utilization
$overallUtilization = $totalEntitlement > 0 ? round(($totalUsed / $totalEntitlement) * 100, 1) : 0;

// Get historical comparison (if not viewing current period)
$showComparison = ($selectedPeriod && $currentPeriod && $selectedPeriod->leavePeriodID != $currentPeriod->leavePeriodID);
?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-medium fs-24 mb-0">
            <i class="ri-pie-chart-line me-2 text-primary"></i>
            My Leave Analytics
        </h1>
        <p class="text-muted mb-0 mt-1">Comprehensive view of your leave usage and patterns</p>
    </div>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $s ?></a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $ss ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">My Leave Analytics</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Period Selector & Quick Actions -->
<div class="row mb-4">
    <div class="col-xl-8 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="ri-calendar-2-line fs-3 text-primary me-3"></i>
                        <div>
                            <h6 class="mb-1">Viewing Period</h6>
                            <form method="GET" id="periodSelectorForm" class="mb-0">
                                <input type="hidden" name="s" value="<?= $s ?>">
                                <input type="hidden" name="ss" value="<?= $ss ?>">
                                <input type="hidden" name="p" value="<?= $p ?>">
                                <input type="hidden" name="uid" value="<?= $employeeID ?>">

                                <select name="periodID" class="form-select form-select-sm d-inline-block w-auto"
                                        data-action="usage-period-select">
                                    <?php if ($allPeriods && is_array($allPeriods)): ?>
                                        <?php foreach ($allPeriods as $period): ?>
                                            <option value="<?= $period->leavePeriodID ?>"
                                                    <?= ($selectedPeriodID == $period->leavePeriodID) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($period->leavePeriodName) ?>
                                                (<?= date('M Y', strtotime($period->leavePeriodStartDate)) ?> -
                                                 <?= date('M Y', strtotime($period->leavePeriodEndDate)) ?>)
                                                <?= ($currentPeriod && $period->leavePeriodID == $currentPeriod->leavePeriodID) ? ' - Current' : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </form>
                        </div>
                    </div>

                    <div class="btn-group" role="group">
                        <a href="?s=<?= $s ?>&ss=<?= $ss ?>&p=apply_leave_workflow" class="btn btn-sm btn-primary">
                            <i class="ri-add-line me-1"></i>Apply Leave
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-action="export-usage-data">
                            <i class="ri-download-line me-1"></i>Export
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-5">
        <div class="card border-0 shadow-sm bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 opacity-75">Overall Utilization</p>
                        <h2 class="mb-0 fw-bold"><?= $overallUtilization ?>%</h2>
                        <small class="opacity-75"><?= $totalUsed ?> of <?= $totalEntitlement ?> days used</small>
                    </div>
                    <div class="text-end">
                        <div class="usage-circle" data-percentage="<?= $overallUtilization ?>">
                            <svg width="80" height="80" viewBox="0 0 80 80">
                                <circle cx="40" cy="40" r="35" stroke="rgba(255,255,255,0.3)" stroke-width="6" fill="none"/>
                                <circle cx="40" cy="40" r="35" stroke="white" stroke-width="6" fill="none"
                                        stroke-dasharray="<?= 2 * 3.14159 * 35 ?>"
                                        stroke-dashoffset="<?= 2 * 3.14159 * 35 * (1 - $overallUtilization/100) ?>"
                                        transform="rotate(-90 40 40)"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Summary Statistics -->
<div class="row mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card stats-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Total Entitlement</p>
                        <h3 class="mb-0 fw-bold text-primary"><?= $totalEntitlement ?></h3>
                        <small class="text-muted">days per year</small>
                    </div>
                    <div class="stats-icon bg-primary-transparent">
                        <i class="ri-calendar-check-line text-primary fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card stats-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Days Used</p>
                        <h3 class="mb-0 fw-bold text-success"><?= $totalUsed ?></h3>
                        <small class="text-muted"><?= $overallUtilization ?>% utilized</small>
                    </div>
                    <div class="stats-icon bg-success-transparent">
                        <i class="ri-checkbox-circle-line text-success fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card stats-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Pending Approval</p>
                        <h3 class="mb-0 fw-bold text-warning"><?= $totalPending ?></h3>
                        <small class="text-muted">awaiting decision</small>
                    </div>
                    <div class="stats-icon bg-warning-transparent">
                        <i class="ri-time-line text-warning fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card stats-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Days Available</p>
                        <h3 class="mb-0 fw-bold text-info"><?= $totalAvailable ?></h3>
                        <small class="text-muted">ready to use</small>
                    </div>
                    <div class="stats-icon bg-info-transparent">
                        <i class="ri-gift-line text-info fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Analytics Section -->
<div class="row">
    <!-- Leave Type Breakdown -->
    <div class="col-xl-8 col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                <h5 class="mb-0">
                    <i class="ri-list-check-2 me-2 text-primary"></i>
                    Leave Type Breakdown
                </h5>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#detailsTable">
                    <i class="ri-eye-line me-1"></i>Toggle Details
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive collapse show" id="detailsTable">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0">Leave Type</th>
                                <th class="border-0 text-center">Entitlement</th>
                                <th class="border-0 text-center">Used</th>
                                <th class="border-0 text-center">Pending</th>
                                <th class="border-0 text-center">Available</th>
                                <th class="border-0 text-center">Utilization</th>
                                <th class="border-0 text-center">Applications</th>
                                <th class="border-0 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($usageData)): ?>
                                <?php foreach ($usageData as $data): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="leave-type-indicator me-2"
                                                     style="background-color: <?= $data['leaveType']->leaveColor ?? '#3498db' ?>"></div>
                                                <div>
                                                    <strong><?= htmlspecialchars($data['leaveType']->leaveTypeName) ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?= htmlspecialchars($data['leaveType']->leaveTypeCode ?? '') ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark"><?= $data['entitlement'] ?> days</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success-transparent text-success"><?= $data['taken'] ?> days</span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($data['pending'] > 0): ?>
                                                <span class="badge bg-warning-transparent text-warning"><?= $data['pending'] ?> days</span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info-transparent text-info fw-bold"><?= $data['available'] ?> days</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="progress-stack">
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-success"
                                                         style="width: <?= $data['utilization'] ?>%"
                                                         data-bs-toggle="tooltip"
                                                         title="<?= $data['utilization'] ?>% used">
                                                        <?= $data['utilization'] ?>%
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary-transparent"><?= $data['applicationCount'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-primary-ghost apply-leave-btn"
                                                    data-leave-type-id="<?= $data['leaveType']->leaveTypeID ?>"
                                                    data-leave-entitlement-id="<?= $data['leaveEntitlementID'] ?>"
                                                    data-leave-period-id="<?= $selectedPeriod ? $selectedPeriod->leavePeriodID : '' ?>"
                                                    data-employee-id="<?= $employeeID ?>"
                                                    data-available="<?= $data['available'] ?>">
                                                <i class="ri-add-circle-line me-1"></i>Apply
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <!-- Total Row -->
                                <tr class="table-active fw-bold">
                                    <td>TOTAL</td>
                                    <td class="text-center"><?= $totalEntitlement ?></td>
                                    <td class="text-center"><?= $totalUsed ?></td>
                                    <td class="text-center"><?= $totalPending ?></td>
                                    <td class="text-center"><?= $totalAvailable ?></td>
                                    <td class="text-center"><?= $overallUtilization ?>%</td>
                                    <td class="text-center">-</td>
                                    <td class="text-center">-</td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="ri-inbox-line fs-2 text-muted"></i>
                                        <p class="text-muted mb-0">No leave entitlements found for this period</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Usage Timeline -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="ri-timeline-view me-2 text-primary"></i>
                    Usage Timeline
                </h5>
            </div>
            <div class="card-body">
                <canvas id="usageTimelineChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <!-- Charts & Insights -->
    <div class="col-xl-4 col-lg-5">
        <!-- Leave Type Distribution -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="ri-pie-chart-2-line me-2 text-primary"></i>
                    Leave Distribution
                </h6>
            </div>
            <div class="card-body">
                <canvas id="leaveDistributionChart" height="250"></canvas>
            </div>
        </div>

        <!-- Key Insights -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="ri-lightbulb-line me-2 text-primary"></i>
                    Key Insights
                </h6>
            </div>
            <div class="card-body">
                <?php
                // Calculate insights
                $mostUsedType = null;
                $leastUsedType = null;
                $highestUtilization = 0;
                $lowestUtilization = 100;

                foreach ($usageData as $data) {
                    if ($data['taken'] > 0) {
                        if (!$mostUsedType || $data['taken'] > $mostUsedType['taken']) {
                            $mostUsedType = $data;
                        }
                        if ($data['utilization'] > $highestUtilization) {
                            $highestUtilization = $data['utilization'];
                        }
                    }
                    if ($data['available'] > 0) {
                        if (!$leastUsedType || $data['taken'] < $leastUsedType['taken']) {
                            $leastUsedType = $data;
                        }
                        if ($data['utilization'] < $lowestUtilization) {
                            $lowestUtilization = $data['utilization'];
                        }
                    }
                }
                ?>

                <div class="insight-item">
                    <div class="d-flex align-items-start mb-3">
                        <div class="insight-icon bg-success-transparent text-success me-3">
                            <i class="ri-arrow-up-line"></i>
                        </div>
                        <div>
                            <strong class="d-block">Most Used Leave Type</strong>
                            <span class="text-muted">
                                <?= $mostUsedType ? htmlspecialchars($mostUsedType['leaveType']->leaveTypeName) . ' (' . $mostUsedType['taken'] . ' days)' : 'N/A' ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="insight-item">
                    <div class="d-flex align-items-start mb-3">
                        <div class="insight-icon bg-info-transparent text-info me-3">
                            <i class="ri-gift-line"></i>
                        </div>
                        <div>
                            <strong class="d-block">Available to Use</strong>
                            <span class="text-muted">
                                You have <?= $totalAvailable ?> days available across all leave types
                            </span>
                        </div>
                    </div>
                </div>

                <?php if ($totalPending > 0): ?>
                <div class="insight-item">
                    <div class="d-flex align-items-start mb-3">
                        <div class="insight-icon bg-warning-transparent text-warning me-3">
                            <i class="ri-time-line"></i>
                        </div>
                        <div>
                            <strong class="d-block">Pending Approval</strong>
                            <span class="text-muted">
                                <?= $totalPending ?> days awaiting approval
                            </span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($overallUtilization < 50): ?>
                <div class="insight-item">
                    <div class="d-flex align-items-start mb-3">
                        <div class="insight-icon bg-danger-transparent text-danger me-3">
                            <i class="ri-alert-line"></i>
                        </div>
                        <div>
                            <strong class="d-block">Low Utilization Alert</strong>
                            <span class="text-muted">
                                You've only used <?= $overallUtilization ?>% of your leave. Consider planning time off for work-life balance.
                            </span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($selectedPeriod && strtotime($selectedPeriod->leavePeriodEndDate) < strtotime('+3 months')): ?>
                <div class="insight-item">
                    <div class="d-flex align-items-start">
                        <div class="insight-icon bg-purple-transparent text-purple me-3">
                            <i class="ri-calendar-event-line"></i>
                        </div>
                        <div>
                            <strong class="d-block">Period Ending Soon</strong>
                            <span class="text-muted">
                                This leave period ends on <?= date('M d, Y', strtotime($selectedPeriod->leavePeriodEndDate)) ?>.
                                Plan to use your remaining <?= $totalAvailable ?> days.
                            </span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="ri-bar-chart-2-line me-2 text-primary"></i>
                    Quick Stats
                </h6>
            </div>
            <div class="card-body">
                <div class="stat-row">
                    <span class="text-muted">Total Applications</span>
                    <strong><?= array_sum(array_column($usageData, 'applicationCount')) ?></strong>
                </div>
                <div class="stat-row">
                    <span class="text-muted">Scheduled Leave</span>
                    <strong><?= $totalScheduled ?> days</strong>
                </div>
                <div class="stat-row">
                    <span class="text-muted">Average per Application</span>
                    <strong>
                        <?php
                        $totalApps = array_sum(array_column($usageData, 'applicationCount'));
                        echo $totalApps > 0 ? round($totalUsed / $totalApps, 1) : 0;
                        ?> days
                    </strong>
                </div>
                <div class="stat-row border-0">
                    <span class="text-muted">Period Progress</span>
                    <strong>
                        <?php
                        if ($selectedPeriod) {
                            $start = new DateTime($selectedPeriod->leavePeriodStartDate);
                            $end = new DateTime($selectedPeriod->leavePeriodEndDate);
                            $now = new DateTime();
                            $total = $start->diff($end)->days;
                            $elapsed = $start->diff($now)->days;
                            $progress = min(100, round(($elapsed / $total) * 100));
                            echo $progress . '%';
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Historical Comparison (if viewing past period) -->
<?php if ($showComparison && $currentPeriod): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0">
                    <i class="ri-git-compare-line me-2 text-primary"></i>
                    Period Comparison
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="comparison-box">
                            <h6 class="text-muted mb-3">
                                <i class="ri-history-line me-2"></i>
                                <?= htmlspecialchars($selectedPeriod->leavePeriodName) ?>
                                <span class="badge bg-secondary ms-2">Viewing</span>
                            </h6>
                            <div class="comparison-stats">
                                <div class="stat-item">
                                    <span>Total Used:</span>
                                    <strong><?= $totalUsed ?> days</strong>
                                </div>
                                <div class="stat-item">
                                    <span>Utilization:</span>
                                    <strong><?= $overallUtilization ?>%</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="comparison-box">
                            <h6 class="text-muted mb-3">
                                <i class="ri-time-line me-2"></i>
                                <?= htmlspecialchars($currentPeriod->leavePeriodName) ?>
                                <span class="badge bg-success ms-2">Current</span>
                            </h6>
                            <div class="comparison-stats">
                                <div class="stat-item">
                                    <span>Total Used:</span>
                                    <strong>
                                        <a href="?s=<?= $s ?>&ss=<?= $ss ?>&p=<?= $p ?>&uid=<?= $employeeID ?>&periodID=<?= $currentPeriod->leavePeriodID ?>"
                                           class="text-decoration-none">
                                            View Current Period →
                                        </a>
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Apply Leave Modal -->
<div class="modal fade" id="quickApplyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Apply Leave</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">
                    <i class="ri-information-line me-1"></i>
                    You'll be redirected to the full application form with this leave type pre-selected.
                </p>
                <div id="quickApplyInfo" class="alert alert-info">
                    <!-- Populated by JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="proceedToApply" class="btn btn-primary">
                    <i class="ri-arrow-right-line me-1"></i>Proceed to Apply
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Charts JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    const periodSelect = document.querySelector('[data-action="usage-period-select"]');
    if (periodSelect) {
        periodSelect.addEventListener('change', () => {
            periodSelect.form?.submit();
        });
    }

    // ============================================================================
    // USAGE TIMELINE CHART
    // ============================================================================

    const timelineCtx = document.getElementById('usageTimelineChart');
    if (timelineCtx) {
        const usageData = <?= json_encode(array_map(function($data) {
            return array(
                'label' => $data['leaveType']->leaveTypeName,
                'taken' => $data['taken'],
                'pending' => $data['pending'],
                'available' => $data['available'],
                'color' => $data['leaveType']->leaveColor ?? '#3498db'
            );
        }, $usageData)) ?>;

        new Chart(timelineCtx, {
            type: 'bar',
            data: {
                labels: usageData.map(d => d.label),
                datasets: [
                    {
                        label: 'Used',
                        data: usageData.map(d => d.taken),
                        backgroundColor: 'rgba(40, 167, 69, 0.7)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Pending',
                        data: usageData.map(d => d.pending),
                        backgroundColor: 'rgba(255, 193, 7, 0.7)',
                        borderColor: 'rgba(255, 193, 7, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Available',
                        data: usageData.map(d => d.available),
                        backgroundColor: 'rgba(23, 162, 184, 0.7)',
                        borderColor: 'rgba(23, 162, 184, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Leave Balance Breakdown by Type'
                    }
                },
                scales: {
                    x: {
                        stacked: false,
                    },
                    y: {
                        stacked: false,
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Days'
                        }
                    }
                }
            }
        });
    }

    // ============================================================================
    // LEAVE DISTRIBUTION PIE CHART
    // ============================================================================

    const distributionCtx = document.getElementById('leaveDistributionChart');
    if (distributionCtx) {
        const usageData = <?= json_encode(array_values(array_filter(array_map(function($data) {
            return $data['taken'] > 0 ? array(
                'label' => $data['leaveType']->leaveTypeName,
                'value' => $data['taken'],
                'color' => $data['leaveType']->leaveColor ?? '#3498db'
            ) : null;
        }, $usageData)))) ?>;

        if (usageData.length > 0) {
            new Chart(distributionCtx, {
                type: 'doughnut',
                data: {
                    labels: usageData.map(d => d.label),
                    datasets: [{
                        data: usageData.map(d => d.value),
                        backgroundColor: usageData.map(d => d.color),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return `${label}: ${value} days (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        } else {
            distributionCtx.parentElement.innerHTML = '<div class="text-center py-5 text-muted"><i class="ri-pie-chart-line fs-2"></i><p class="mb-0 mt-2">No leave usage data yet</p></div>';
        }
    }

    // ============================================================================
    // APPLY LEAVE BUTTON HANDLERS
    // ============================================================================

    document.querySelectorAll('.apply-leave-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            const leaveTypeId = this.dataset.leaveTypeId;
            const leaveEntitlementId = this.dataset.leaveEntitlementId;
            const leavePeriodId = this.dataset.leavePeriodId;
            const employeeId = this.dataset.employeeId;
            const available = this.dataset.available;

            // Check if leave is available
            if (parseInt(available) <= 0) {
                showToast('warning', 'No Leave Available', 'You have no available leave days for this leave type.');
                return;
            }

            // Build URL with pre-filled parameters
            const url = `?s=<?= $s ?>&ss=<?= $ss ?>&p=apply_leave_workflow` +
                       `&leaveTypeId=${leaveTypeId}` +
                       `&leaveEntitlementId=${leaveEntitlementId}` +
                       `&leavePeriodId=${leavePeriodId}` +
                       `&employeeId=${employeeId}`;

            window.location.href = url;
        });
    });

    document.querySelectorAll('[data-action="export-usage-data"]').forEach(button => {
        button.addEventListener('click', () => {
            if (typeof exportUsageData === 'function') {
                exportUsageData();
            }
        });
    });
});

// ============================================================================
// EXPORT FUNCTIONALITY
// ============================================================================

function exportUsageData() {
    const format = 'excel'; // or 'pdf'
    const periodId = '<?= $selectedPeriodID ?>';
    const employeeId = '<?= $employeeID ?>';

    window.location.href = `<?= $base ?>php/scripts/leave/utilities/export_statistics.php?` +
                          `format=${format}&periodID=${periodId}&employeeID=${employeeId}&type=personal`;
}

// ============================================================================
// TOAST NOTIFICATIONS
// ============================================================================

function showToast(type, title, message) {
    const toastContainer = document.querySelector('.toast-container') || createToastContainer();

    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <strong>${title}</strong><br>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

    toastContainer.appendChild(toast);

    const bsToast = new bootstrap.Toast(toast, {
        autohide: true,
        delay: 5000
    });

    bsToast.show();

    toast.addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '1055';
    document.body.appendChild(container);
    return container;
}
</script>
