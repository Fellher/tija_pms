<?php
if(!$isValidUser) {
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}

$employeeID=(isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) : $userDetails->ID;
$employeeDetails = Data::users(array('ID'=>$employeeID), true, $DBConn);
$orgDataID= isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : $employeeDetails->orgDataID;
$entityID= isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : $employeeDetails->entityID;
?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div class="d-flex align-items-center gap-2">
        <h1 class="page-title fw-medium fs-24 mb-0">Sales Analytics Dashboard</h1>
        <button type="button" class="btn btn-light btn-sm d-inline-flex align-items-center"
                data-bs-toggle="modal" data-bs-target="#salesAnalyticsDocModal"
                title="View documentation">
            <i class="ri-information-line"></i>
        </button>
    </div>
    <div class="ms-md-1 ms-0">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="showWidget('overview')">
                <i class="ri-dashboard-line"></i> Overview
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="showWidget('stages')">
                <i class="ri-bar-chart-line"></i> Stage Analysis
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="showWidget('advanced')">
                <i class="ri-line-chart-line"></i> Advanced Analytics
            </button>
        </div>
    </div>
</div>
<div class="alert alert-info d-flex align-items-start gap-2">
    <i class="ri-lightbulb-line fs-18 text-primary mt-1"></i>
    <div class="small">
        Quick guide: <strong>Overview</strong> shows pipeline/value KPIs and activity tiles; <strong>Stage Analysis</strong> breaks down deals by stage and weighted value; <strong>Advanced</strong> surfaces deeper trend visuals and segmentation. Click the info button for full documentation.
    </div>
</div>

<!-- Analytics Tabs -->
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs nav-tabs-custom mb-3" id="analyticsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                                <i class="ri-dashboard-line me-2"></i>Overview <i class="ri-question-line text-muted ms-1" data-bs-toggle="tooltip" title="Headline KPIs, pipeline totals, quick activity insights"></i>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="stages-tab" data-bs-toggle="tab" data-bs-target="#stages" type="button" role="tab">
                                <i class="ri-bar-chart-line me-2"></i>Stage Analysis <i class="ri-question-line text-muted ms-1" data-bs-toggle="tooltip" title="Stage distribution, weighted values, conversion mix"></i>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="advanced-tab" data-bs-toggle="tab" data-bs-target="#advanced" type="button" role="tab">
                                <i class="ri-line-chart-line me-2"></i>Advanced Analytics <i class="ri-question-line text-muted ms-1" data-bs-toggle="tooltip" title="Trends, cohorts, segments, and deeper diagnostics"></i>
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="analyticsTabContent">

                        <!-- Overview Tab -->
                        <div class="tab-pane fade show active" id="overview" role="tabpanel">
                            <?php include "includes/scripts/sales/sales_overview_widgets.php"; ?>
                        </div>

                        <!-- Stage Analysis Tab -->
                        <div class="tab-pane fade" id="stages" role="tabpanel">
                            <?php include "includes/scripts/sales/sales_stage_analysis_widgets.php"; ?>
                        </div>

                        <!-- Advanced Analytics Tab -->
                        <div class="tab-pane fade" id="advanced" role="tabpanel">
                            <?php include "includes/scripts/sales/advanced_sales_analytics.php"; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Documentation Modal -->
<div class="modal fade" id="salesAnalyticsDocModal" tabindex="-1" aria-labelledby="salesAnalyticsDocModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="salesAnalyticsDocModalLabel">
          <i class="ri-book-open-line me-2"></i>Sales Analytics Guide
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-4">
          <h6 class="text-primary d-flex align-items-center gap-2">
            <i class="ri-dashboard-line"></i> Overview
          </h6>
          <ul class="small mb-0">
            <li><strong>Pipeline / Value KPIs:</strong> Total pipeline value and split by stage (Won/Lost/Opportunities/Business Development).</li>
            <li><strong>Activity snapshots:</strong> Recent activities and counts to show current engagement intensity.</li>
            <li><strong>Quick filters/tabs:</strong> Use the top buttons or tabs to jump between views without reloading.</li>
          </ul>
        </div>
        <div class="mb-4">
          <h6 class="text-primary d-flex align-items-center gap-2">
            <i class="ri-bar-chart-line"></i> Stage Analysis
          </h6>
          <ul class="small mb-0">
            <li><strong>Stage distribution:</strong> Doughnut/stacked visuals showing how deals are spread by stage.</li>
            <li><strong>Weighted values:</strong> Values weighted by probability to show risk-adjusted pipeline.</li>
            <li><strong>Conversion mix:</strong> Quickly spot where deals accumulate or stall.</li>
          </ul>
        </div>
        <div class="mb-4">
          <h6 class="text-primary d-flex align-items-center gap-2">
            <i class="ri-line-chart-line"></i> Advanced Analytics
          </h6>
          <ul class="small mb-0">
            <li><strong>Trends:</strong> Time-based charts (value and count) to see movement over weeks/months.</li>
            <li><strong>Segments & cohorts:</strong> Breakdowns by owner, industry, source, or other dimensions.</li>
            <li><strong>Diagnostics:</strong> Identify leakage points, cycle times, and improvement opportunities.</li>
          </ul>
        </div>
        <div class="mb-0">
          <h6 class="text-primary d-flex align-items-center gap-2">
            <i class="ri-information-line"></i> Tips
          </h6>
          <ul class="small mb-0">
            <li>Hover icons (<i class="ri-question-line"></i>) for quick inline hints on each tab.</li>
            <li>Use the Stage Analysis tab to prioritize effort on high-value stalled stages.</li>
            <li>Check Advanced Analytics for trend confirmation before reallocating resources.</li>
          </ul>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
    function showWidget(widgetType) {
        // Remove active class from all tabs
        document.querySelectorAll('.nav-link').forEach(tab => {
            tab.classList.remove('active');
        });

        // Remove active class from all tab panes
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.classList.remove('show', 'active');
        });

        // Activate the selected tab
        const selectedTab = document.getElementById(widgetType + '-tab');
        const selectedPane = document.getElementById(widgetType);

        if (selectedTab && selectedPane) {
            selectedTab.classList.add('active');
            selectedPane.classList.add('show', 'active');

            // Trigger chart initialization when stages tab is activated
            if (widgetType === 'stages') {
                setTimeout(function() {
                    initializeSalesStageChart();
                }, 100);
            }
        }
    }

    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Initialize charts on page load
        setTimeout(function() {
            initializeSalesStageChart();
        }, 500);
    });

    // Function to initialize sales stage chart
    function initializeSalesStageChart() {
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded');
            return;
        }

        const canvas = document.getElementById('salesStageChart');
        if (!canvas) {
            console.error('Canvas element not found');
            return;
        }

        // Destroy existing chart if it exists
        if (window.salesStageChartInstance) {
            window.salesStageChartInstance.destroy();
        }

        const ctx = canvas.getContext('2d');
        window.salesStageChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Business Development', 'Opportunities', 'Won', 'Lost'],
                datasets: [{
                    data: [
                        <?= $stageData['business_development']['value'] ?? 0 ?>,
                        <?= $stageData['opportunities']['value'] ?? 0 ?>,
                        <?= $stageData['won']['value'] ?? 0 ?>,
                        <?= $stageData['lost']['value'] ?? 0 ?>
                    ],
                    backgroundColor: [
                        '#17a2b8', // Business Development - Info
                        '#ffc107', // Opportunities - Warning
                        '#28a745', // Won - Success
                        '#dc3545'  // Lost - Danger
                    ],
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
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';
                                return context.label + ': KES ' + value.toLocaleString() + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });

        console.log('Sales Stage Chart initialized successfully');
    }
</script>
