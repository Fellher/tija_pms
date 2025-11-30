<?php
/**
 * Capacity Planning - User
 *
 * View capacity utilization and planning
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

if(!$isValidUser) {
    Alert::info("You need to be logged in to access this page", true);
    include "includes/core/log_in_script.php";
    return;
}

// Include help component
include __DIR__ . '/../../../includes/components/operational_help.php';

global $DBConn, $userID;

// Get capacity data
$dateFrom = $_GET['dateFrom'] ?? date('Y-01-01');
$dateTo = $_GET['dateTo'] ?? date('Y-12-31');

// Get user's capacity
$bauHours = CapacityPlanning::calculateOperationalTax($userID, $dateFrom, $dateTo, $DBConn);
$availableCapacity = CapacityPlanning::getAvailableCapacity($userID, $dateFrom, $dateTo, $DBConn);
$capacityWaterline = CapacityPlanning::getCapacityWaterline($userID, $dateFrom, $dateTo, $DBConn);

$pageTitle = "Capacity Planning";
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0"><?php echo htmlspecialchars($pageTitle); ?></h4>
                    <p class="text-muted mb-0 help-text">
                        View your capacity utilization and planning.
                        <?php echo renderHelpPopover('Capacity Planning', 'A visual representation of how your time is allocated: Layer 1 (Non-working: PTO, holidays), Layer 2 (BAU: operational tasks), Layer 3 (Projects), and Available capacity. This helps you understand your workload and plan accordingly.', 'right'); ?>
                    </p>
                </div>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="?s=user">User</a></li>
                        <li class="breadcrumb-item"><a href="?s=user&ss=operational">Operational Work</a></li>
                        <li class="breadcrumb-item active">Capacity</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <input type="date" class="form-control" id="dateFrom" value="<?php echo htmlspecialchars($dateFrom); ?>" style="width: 200px;">
                        <input type="date" class="form-control" id="dateTo" value="<?php echo htmlspecialchars($dateTo); ?>" style="width: 200px;">
                        <button class="btn btn-primary" onclick="applyDateRange()">
                            <i class="ri-search-line me-1"></i>Apply
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Capacity Overview -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Total Capacity</p>
                            <h4 class="mb-2">2,080 hrs</h4>
                            <small class="text-muted">Annual (FTE)</small>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-primary rounded-3">
                                <i class="ri-bar-chart-box-line font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">
                                BAU Hours
                                <?php echo renderHelpIcon('The time spent on Business-As-Usual (BAU) tasks. This "operational tax" on your capacity is necessary operational work that must be done to keep the business running.', 'top'); ?>
                            </p>
                            <h4 class="mb-2 text-warning"><?php echo number_format($bauHours ?? 0, 0); ?> hrs</h4>
                            <small class="text-muted">Operational Tax</small>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-warning rounded-3">
                                <i class="ri-repeat-line font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Available Capacity</p>
                            <h4 class="mb-2 text-success"><?php echo number_format($availableCapacity['availableHours'] ?? 0, 0); ?> hrs</h4>
                            <small class="text-muted">For Projects</small>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-success rounded-3">
                                <i class="ri-checkbox-circle-line font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Utilization</p>
                            <h4 class="mb-2 text-info"><?php
                                $utilization = 2080 > 0 ? (($bauHours ?? 0) / 2080) * 100 : 0;
                                echo number_format($utilization, 1);
                            ?>%</h4>
                            <small class="text-muted">BAU Utilization</small>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-info rounded-3">
                                <i class="ri-pie-chart-line font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Capacity Waterline Visualization -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Capacity Waterline</h4>
                </div>
                <div class="card-body">
                    <div class="capacity-waterline" style="height: 400px;">
                        <canvas id="capacityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Capacity Breakdown -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Capacity Breakdown</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Hours</th>
                                    <th>Percentage</th>
                                    <th>Visual</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Non-Working Time</strong><br><small class="text-muted">PTO, Holidays</small></td>
                                    <td><?php echo number_format($capacityWaterline['layer1_nonWorking'] ?? 0, 0); ?> hrs</td>
                                    <td><?php echo number_format((($capacityWaterline['layer1_nonWorking'] ?? 0) / ($capacityWaterline['total'] ?? 2080)) * 100, 1); ?>%</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-secondary" style="width: <?php echo (($capacityWaterline['layer1_nonWorking'] ?? 0) / ($capacityWaterline['total'] ?? 2080)) * 100; ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>BAU (Operational Tasks)</strong><br><small class="text-muted">Recurring operational work</small></td>
                                    <td><?php echo number_format($capacityWaterline['layer2_bau'] ?? 0, 0); ?> hrs</td>
                                    <td><?php echo number_format((($capacityWaterline['layer2_bau'] ?? 0) / ($capacityWaterline['total'] ?? 2080)) * 100, 1); ?>%</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-warning" style="width: <?php echo (($capacityWaterline['layer2_bau'] ?? 0) / ($capacityWaterline['total'] ?? 2080)) * 100; ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Projects</strong><br><small class="text-muted">Project work</small></td>
                                    <td><?php echo number_format($capacityWaterline['layer3_projects'] ?? 0, 0); ?> hrs</td>
                                    <td><?php echo number_format((($capacityWaterline['layer3_projects'] ?? 0) / ($capacityWaterline['total'] ?? 2080)) * 100, 1); ?>%</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-info" style="width: <?php echo (($capacityWaterline['layer3_projects'] ?? 0) / ($capacityWaterline['total'] ?? 2080)) * 100; ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="table-success">
                                    <td><strong>Available Capacity</strong><br><small class="text-muted">Remaining capacity</small></td>
                                    <td><strong><?php echo number_format($capacityWaterline['available'] ?? 0, 0); ?> hrs</strong></td>
                                    <td><strong><?php echo number_format((($capacityWaterline['available'] ?? 0) / ($capacityWaterline['total'] ?? 2080)) * 100, 1); ?>%</strong></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" style="width: <?php echo (($capacityWaterline['available'] ?? 0) / ($capacityWaterline['total'] ?? 2080)) * 100; ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function applyDateRange() {
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    const url = new URL(window.location);
    url.searchParams.set('dateFrom', dateFrom);
    url.searchParams.set('dateTo', dateTo);
    window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    // TODO: Initialize capacity chart with Chart.js
    const ctx = document.getElementById('capacityChart');
    if (ctx) {
        // Chart initialization code will be implemented
    }
});
</script>

