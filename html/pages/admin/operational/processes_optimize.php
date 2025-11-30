<?php
/**
 * Process Optimization - Admin
 *
 * Analyze and optimize operational processes
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

if (!$isAdmin && !$isValidAdmin) {
    Alert::error("Access denied. Administrator privileges required.", true);
    return;
}

global $DBConn, $userID;

$pageTitle = "Process Optimization";
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0"><?php echo htmlspecialchars($pageTitle); ?></h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="?s=admin">Admin</a></li>
                        <li class="breadcrumb-item"><a href="?s=admin&ss=operational">Operational Work</a></li>
                        <li class="breadcrumb-item active">Optimization</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Optimization Insights -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2 text-primary">Automation Opportunities</p>
                            <h4 class="mb-2 text-primary">12</h4>
                            <small class="text-muted">Processes identified for automation</small>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-primary-transparent rounded-3">
                                <i class="ri-robot-line font-size-18 text-primary"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2 text-warning">Bottlenecks</p>
                            <h4 class="mb-2 text-warning">5</h4>
                            <small class="text-muted">Process steps causing delays</small>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-warning-transparent rounded-3">
                                <i class="ri-time-line font-size-18 text-warning"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2 text-success">Potential Savings</p>
                            <h4 class="mb-2 text-success">45 hrs/week</h4>
                            <small class="text-muted">Estimated time savings</small>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-success-transparent rounded-3">
                                <i class="ri-money-dollar-circle-line font-size-18 text-success"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Optimization Recommendations -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Optimization Recommendations</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="recommendationsTable">
                            <thead>
                                <tr>
                                    <th>Process</th>
                                    <th>Issue</th>
                                    <th>Impact</th>
                                    <th>Recommendation</th>
                                    <th>Potential Savings</th>
                                    <th width="150" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <span class="badge bg-primary">8.6.1</span>
                                        <div class="fw-semibold mt-1">Cash Reconciliation</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">High Cycle Time</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">High</span>
                                    </td>
                                    <td>Automate data extraction from bank statements</td>
                                    <td>8 hrs/month</td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-primary" data-action="apply-recommendation" data-recommendation-id="1">
                                            <i class="ri-check-line me-1"></i>Apply
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge bg-primary">7.3.1</span>
                                        <div class="fw-semibold mt-1">Payroll Processing</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">Manual Steps</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">Medium</span>
                                    </td>
                                    <td>Implement automated validation checks</td>
                                    <td>4 hrs/month</td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-primary" data-action="apply-recommendation" data-recommendation-id="2">
                                            <i class="ri-check-line me-1"></i>Apply
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Process Efficiency Analysis -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Process Efficiency Analysis</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Top Processes by Time Spent</h6>
                            <canvas id="timeSpentChart" height="200"></canvas>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Efficiency Trends</h6>
                            <canvas id="efficiencyChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function applyRecommendation(recommendationID) {
    if (confirm('Apply this optimization recommendation?')) {
        // TODO: Implement apply recommendation API call
        alert('Apply functionality will be implemented');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('recommendationsTable')) {
        $('#recommendationsTable').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[2, 'desc']],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ recommendations per page"
            }
        });
    }

    // TODO: Initialize charts with Chart.js or similar library

    // Event delegation for process optimization
    document.addEventListener('click', function(e) {
        const action = e.target.closest('[data-action]')?.getAttribute('data-action');
        if (!action) return;

        const element = e.target.closest('[data-action]');

        if (action === 'apply-recommendation') {
            const recommendationID = element.getAttribute('data-recommendation-id');
            if (recommendationID) {
                applyRecommendation(parseInt(recommendationID));
            }
        }
    });
});
</script>

