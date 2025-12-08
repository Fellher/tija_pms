<?php
/**
 * Leave Analytics Dashboard
 * Comprehensive leave analytics for HR Managers and Admins
 * Board-ready reports with drill-down capabilities
 */

if (!$isValidUser) {
    Alert::info("You need to be logged in to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

// Access control - HR Managers and Admins only
$isHrManager = Employee::is_hr_manager($userDetails->ID, $DBConn);
$isAdmin = isset($userDetails->isAdmin) && $userDetails->isAdmin;

if (!$isHrManager && !$isAdmin) {
    Alert::error("You need HR Manager or Admin privileges to access this page", true, array('text-center'));
    echo "<div class='container py-5'><a class='btn btn-outline-primary' href='{$base}html/?s=user&ss=leave&p=my_applications'>Back to Leave Management</a></div>";
    return;
}

// Get HR Manager scope
$hrManagerScope = Employee::get_hr_manager_scope($userDetails->ID, $DBConn);
$isGlobalHR = !empty($hrManagerScope['isHRManager']) && !empty($hrManagerScope['scopes']) &&
              count(array_filter($hrManagerScope['scopes'], function($s) { return !empty($s['global']); })) > 0;

// Default to user's org/entity unless they have global scope
$orgDataID = $userDetails->orgDataID ?? null;
$entityID = $userDetails->entityID ?? null;

// Allow filtering by entity if global HR
$selectedEntityID = isset($_GET['entityID']) && $isGlobalHR ? (int)$_GET['entityID'] : $entityID;
$selectedOrgDataID = isset($_GET['orgDataID']) && $isGlobalHR ? (int)$_GET['orgDataID'] : $orgDataID;

// Time period filter
$period = isset($_GET['period']) ? Utility::clean_string($_GET['period']) : 'month';
$validPeriods = array('week', 'month', 'quarter', 'semi-annual', 'annual');
if (!in_array($period, $validPeriods)) {
    $period = 'month';
}

// Custom date range
$customStartDate = isset($_GET['start_date']) ? Utility::clean_string($_GET['start_date']) : null;
$customEndDate = isset($_GET['end_date']) ? Utility::clean_string($_GET['end_date']) : null;

// Calculate date range based on period
if ($customStartDate && $customEndDate) {
    $startDate = $customStartDate;
    $endDate = $customEndDate;
} else {
    $endDate = date('Y-m-d');
    switch ($period) {
        case 'week':
            $startDate = date('Y-m-d', strtotime('-7 days'));
            break;
        case 'month':
            $startDate = date('Y-m-d', strtotime('-30 days'));
            break;
        case 'quarter':
            $startDate = date('Y-m-d', strtotime('-3 months'));
            break;
        case 'semi-annual':
            $startDate = date('Y-m-d', strtotime('-6 months'));
            break;
        case 'annual':
            $startDate = date('Y-m-01', strtotime('January ' . date('Y')));
            break;
        default:
            $startDate = date('Y-m-d', strtotime('-30 days'));
    }
}

// Active tab
$activeTab = isset($_GET['tab']) ? Utility::clean_string($_GET['tab']) : 'overview';

// Get entities for filter dropdown (if global HR)
$entities = array();
if ($isGlobalHR) {
    $entities = Data::entities_full(array('orgDataID' => $orgDataID, 'Suspended' => 'N'), false, $DBConn);
}
?>

<div class="container-fluid p-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <h2 class="mb-2">
                <i class="ri-bar-chart-box-line text-primary me-2"></i>
                Leave Analytics Dashboard
            </h2>
            <p class="text-muted mb-0">
                Comprehensive leave analysis and reporting for strategic workforce planning
            </p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <div class="btn-group">
                <button type="button" class="btn btn-outline-success" id="exportExcelBtn">
                    <i class="ri-file-excel-2-line me-1"></i> Export Excel
                </button>
                <button type="button" class="btn btn-outline-danger" id="exportPdfBtn">
                    <i class="ri-file-pdf-line me-1"></i> Export PDF
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="ri-printer-line me-1"></i> Print
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" id="analyticsFilters" class="row g-3 align-items-end">
                <input type="hidden" name="s" value="user">
                <input type="hidden" name="ss" value="leave">
                <input type="hidden" name="p" value="leave_analytics">
                <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab); ?>">

                <?php if ($isGlobalHR && $entities): ?>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Entity/Organization</label>
                    <select name="entityID" class="form-select">
                        <option value="">All Entities</option>
                        <?php foreach ($entities as $entity): ?>
                            <option value="<?php echo $entity->entityID; ?>" <?php echo $selectedEntityID == $entity->entityID ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($entity->entityName); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Time Period</label>
                    <select name="period" class="form-select" id="periodSelect">
                        <option value="week" <?php echo $period === 'week' ? 'selected' : ''; ?>>Last 7 Days</option>
                        <option value="month" <?php echo $period === 'month' ? 'selected' : ''; ?>>Last 30 Days</option>
                        <option value="quarter" <?php echo $period === 'quarter' ? 'selected' : ''; ?>>Last Quarter</option>
                        <option value="semi-annual" <?php echo $period === 'semi-annual' ? 'selected' : ''; ?>>Last 6 Months</option>
                        <option value="annual" <?php echo $period === 'annual' ? 'selected' : ''; ?>>Current Year</option>
                        <option value="custom" <?php echo $customStartDate ? 'selected' : ''; ?>>Custom Range</option>
                    </select>
                </div>

                <div class="col-md-2" id="customDateRange" style="display: <?php echo $customStartDate ? 'block' : 'none'; ?>;">
                    <label class="form-label fw-semibold">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>">
                </div>

                <div class="col-md-2" id="customDateRange2" style="display: <?php echo $customStartDate ? 'block' : 'none'; ?>;">
                    <label class="form-label fw-semibold">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($endDate); ?>">
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ri-filter-3-line me-1"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading indicator -->
    <div id="loadingIndicator" class="text-center py-5" style="display: none;">
        <div class="spinner-border text-primary mb-3" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="text-muted">Loading analytics data...</p>
    </div>

    <!-- Analytics content will be loaded here -->
    <div id="analyticsContent">
        <!-- Executive Summary Cards -->
        <div class="row g-3 mb-4" id="executiveSummary">
            <!-- Will be populated by JavaScript -->
        </div>

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs nav-tabs-custom mb-4" role="tablist">
            <li class="nav-item">
                <a class="nav-link <?php echo $activeTab === 'overview' ? 'active' : ''; ?>"
                   href="?s=user&ss=leave&p=leave_analytics&tab=overview&period=<?php echo $period; ?>">
                    <i class="ri-dashboard-line me-1"></i> Overview
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $activeTab === 'workforce' ? 'active' : ''; ?>"
                   href="?s=user&ss=leave&p=leave_analytics&tab=workforce&period=<?php echo $period; ?>">
                    <i class="ri-team-line me-1"></i> Workforce Impact
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $activeTab === 'workflow' ? 'active' : ''; ?>"
                   href="?s=user&ss=leave&p=leave_analytics&tab=workflow&period=<?php echo $period; ?>">
                    <i class="ri-git-branch-line me-1"></i> Approval Workflow
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $activeTab === 'employees' ? 'active' : ''; ?>"
                   href="?s=user&ss=leave&p=leave_analytics&tab=employees&period=<?php echo $period; ?>">
                    <i class="ri-user-search-line me-1"></i> Employee Drill-Down
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $activeTab === 'departments' ? 'active' : ''; ?>"
                   href="?s=user&ss=leave&p=leave_analytics&tab=departments&period=<?php echo $period; ?>">
                    <i class="ri-building-2-line me-1"></i> Departmental Analysis
                </a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Overview Tab -->
            <div class="tab-pane <?php echo $activeTab === 'overview' ? 'active show' : ''; ?>" id="overviewTab">
                <?php include 'includes/scripts/leave/analytics/overview_tab.php'; ?>
            </div>

            <!-- Workforce Impact Tab -->
            <div class="tab-pane <?php echo $activeTab === 'workforce' ? 'active show' : ''; ?>" id="workforceTab">
                <?php include 'includes/scripts/leave/analytics/workforce_tab.php'; ?>
            </div>

            <!-- Approval Workflow Tab -->
            <div class="tab-pane <?php echo $activeTab === 'workflow' ? 'active show' : ''; ?>" id="workflowTab">
                <?php include 'includes/scripts/leave/analytics/workflow_tab.php'; ?>
            </div>

            <!-- Employee Drill-Down Tab -->
            <div class="tab-pane <?php echo $activeTab === 'employees' ? 'active show' : ''; ?>" id="employeesTab">
                <?php include 'includes/scripts/leave/analytics/employees_tab.php'; ?>
            </div>

            <!-- Departmental Analysis Tab -->
            <div class="tab-pane <?php echo $activeTab === 'departments' ? 'active show' : ''; ?>" id="departmentsTab">
                <?php include 'includes/scripts/leave/analytics/departments_tab.php'; ?>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn-group, .nav-tabs, .card-body form, #exportExcelBtn, #exportPdfBtn {
        display: none !important;
    }

    .container-fluid {
        padding: 0 !important;
    }

    .card {
        break-inside: avoid;
        page-break-inside: avoid;
    }
}

.nav-tabs-custom {
    border-bottom: 2px solid #e5e7eb;
}

.nav-tabs-custom .nav-link {
    border: none;
    color: #6b7280;
    padding: 12px 20px;
    font-weight: 500;
    transition: all 0.3s;
}

.nav-tabs-custom .nav-link:hover {
    color: #4f46e5;
    background: #f3f4f6;
}

.nav-tabs-custom .nav-link.active {
    color: #4f46e5;
    background: transparent;
    border-bottom: 3px solid #4f46e5;
}

.metric-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.metric-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

.chart-container {
    position: relative;
    height: 350px;
    margin-bottom: 20px;
}

.table-responsive {
    max-height: 500px;
    overflow-y: auto;
}
</style>

<script>
// Global variables for filters
const analyticsConfig = {
    orgDataID: <?php echo json_encode($selectedOrgDataID); ?>,
    entityID: <?php echo json_encode($selectedEntityID); ?>,
    startDate: '<?php echo $startDate; ?>',
    endDate: '<?php echo $endDate; ?>',
    period: '<?php echo $period; ?>',
    baseUrl: '<?php echo $base; ?>'
};

// Show/hide custom date range inputs
document.getElementById('periodSelect')?.addEventListener('change', function() {
    const customFields = document.querySelectorAll('#customDateRange, #customDateRange2');
    customFields.forEach(field => {
        field.style.display = this.value === 'custom' ? 'block' : 'none';
    });
});

// Export functions
document.getElementById('exportExcelBtn')?.addEventListener('click', function() {
    const url = `${analyticsConfig.baseUrl}php/scripts/leave/reports/export_leave_analytics.php?` +
                `format=excel&period=${analyticsConfig.period}&` +
                `start_date=${analyticsConfig.startDate}&end_date=${analyticsConfig.endDate}&` +
                `entityID=${analyticsConfig.entityID || ''}&orgDataID=${analyticsConfig.orgDataID || ''}`;
    window.location.href = url;
});

document.getElementById('exportPdfBtn')?.addEventListener('click', function() {
    const url = `${analyticsConfig.baseUrl}php/scripts/leave/reports/export_leave_analytics.php?` +
                `format=pdf&period=${analyticsConfig.period}&` +
                `start_date=${analyticsConfig.startDate}&end_date=${analyticsConfig.endDate}&` +
                `entityID=${analyticsConfig.entityID || ''}&orgDataID=${analyticsConfig.orgDataID || ''}`;
    window.location.href = url;
});
</script>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<!-- Custom Chart Configurations -->
<script src="<?php echo $base; ?>html/includes/scripts/leave/analytics/charts_config.js"></script>


