<?php
/**
 * Leave Reports & Analytics - Admin Page
 * View comprehensive leave reports and analytics
 */

if(!$isValidUser) {
    Alert::info("You need to be logged in to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

// Check admin permissions
if (!$isAdmin && !$isValidAdmin && !$isHRManager) {
    Alert::error("Access denied. Leave Administrator privileges required.", true, array('text-center'));
    return;
}

$entityID = $_SESSION['entityID'] ?? 1;
$orgDataID = $_SESSION['orgDataID'] ?? 1;
$pageTitle = 'Leave Reports & Analytics';
$title = $pageTitle . ' - Leave Management System';

// Get current year data
$currentYear = date('Y');

// Get leave applications for reporting
$allApplications = Leave::leave_applications(array('entityID' => $entityID), false, $DBConn);
$leaveTypes = Leave::leave_types(array('Lapsed' => 'N'), false, $DBConn);
$leaveStatuses = Leave::leave_status(array('Suspended' => 'N'), false, $DBConn);

if (!function_exists('sanitizeReportDate')) {
    function sanitizeReportDate($value, $fallback) {
        $value = trim((string) $value);
        if ($value === '' || !strtotime($value)) {
            return $fallback;
        }
        return date('Y-m-d', strtotime($value));
    }
}

if (!function_exists('buildLeaveReportFilters')) {
    function buildLeaveReportFilters($entityID, $orgDataID, $fromDate, $toDate, $leaveTypeId, $statusId) {
        $whereParts = array('la.entityID = ?');
        $params = array(array((int)$entityID, 'i'));

        if (!empty($orgDataID)) {
            $whereParts[] = 'la.orgDataID = ?';
            $params[] = array((int)$orgDataID, 'i');
        }

        if ($fromDate) {
            $whereParts[] = 'la.startDate >= ?';
            $params[] = array($fromDate, 's');
        }

        if ($toDate) {
            $whereParts[] = 'la.endDate <= ?';
            $params[] = array($toDate, 's');
        }

        if (!empty($leaveTypeId)) {
            $whereParts[] = 'la.leaveTypeID = ?';
            $params[] = array((int)$leaveTypeId, 'i');
        }

        if ($statusId !== null && $statusId !== '') {
            $whereParts[] = 'la.leaveStatusID = ?';
            $params[] = array((int)$statusId, 'i');
        }

        return array(
            'where' => 'WHERE ' . implode(' AND ', $whereParts),
            'params' => $params
        );
    }
}

$defaultFromDate = date('Y-01-01');
$defaultToDate = date('Y-m-d');

$fromDate = sanitizeReportDate($_GET['fromDate'] ?? $defaultFromDate, $defaultFromDate);
$toDate = sanitizeReportDate($_GET['toDate'] ?? $defaultToDate, $defaultToDate);

if (strtotime($toDate) < strtotime($fromDate)) {
    $tmp = $fromDate;
    $fromDate = $toDate;
    $toDate = $tmp;
}

$leaveTypeFilter = isset($_GET['leaveType']) && $_GET['leaveType'] !== '' ? (int)$_GET['leaveType'] : null;
$statusFilter = isset($_GET['status']) && $_GET['status'] !== '' ? (int)$_GET['status'] : null;

$filters = buildLeaveReportFilters($entityID, $orgDataID, $fromDate, $toDate, $leaveTypeFilter, $statusFilter);
$whereClause = $filters['where'];
$queryParams = $filters['params'];

$applicationsSql = "
    SELECT la.*, lt.leaveTypeName, ls.leaveStatusName,
           CONCAT(p.FirstName, ' ', p.Surname) AS employeeName
    FROM tija_leave_applications la
    LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
    LEFT JOIN tija_leave_status ls ON la.leaveStatusID = ls.leaveStatusID
    LEFT JOIN people p ON la.employeeID = p.ID
    {$whereClause}
    ORDER BY la.startDate DESC
";
$filteredApplications = $DBConn->fetch_all_rows($applicationsSql, $queryParams);

$totalApplications = $filteredApplications ? count($filteredApplications) : 0;
$approvedApplications = 0;
$pendingApplications = 0;
$rejectedApplications = 0;

if ($filteredApplications) {
    foreach ($filteredApplications as $application) {
        $statusId = (int)($application->leaveStatusID ?? 0);
        if ($statusId === 6) {
            $approvedApplications++;
        } elseif ($statusId === 3) {
            $pendingApplications++;
        } elseif ($statusId === 4) {
            $rejectedApplications++;
        }
    }
}

$leaveUsageSql = "
    SELECT
        lt.leaveTypeID,
        lt.leaveTypeName,
        COUNT(la.leaveApplicationID) AS totalApplications,
        COUNT(DISTINCT la.employeeID) AS uniqueEmployees,
        SUM(la.noOfDays) AS totalDays,
        SUM(CASE WHEN la.leaveStatusID = 6 THEN la.noOfDays ELSE 0 END) AS approvedDays,
        SUM(CASE WHEN la.leaveStatusID = 3 THEN la.noOfDays ELSE 0 END) AS pendingDays,
        SUM(CASE WHEN la.leaveStatusID = 4 THEN la.noOfDays ELSE 0 END) AS rejectedDays
    FROM tija_leave_applications la
    INNER JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
    {$whereClause}
    GROUP BY lt.leaveTypeID, lt.leaveTypeName
    ORDER BY totalDays DESC
";
$leaveUsageReport = $DBConn->fetch_all_rows($leaveUsageSql, $queryParams);

$departmentSummarySql = "
    SELECT
        COALESCE(bu.unitName, 'Unassigned') AS departmentName,
        COUNT(la.leaveApplicationID) AS totalApplications,
        COUNT(DISTINCT la.employeeID) AS uniqueEmployees,
        SUM(la.noOfDays) AS totalDays,
        SUM(CASE WHEN la.leaveStatusID = 6 THEN la.noOfDays ELSE 0 END) AS approvedDays,
        SUM(CASE WHEN la.leaveStatusID = 3 THEN la.noOfDays ELSE 0 END) AS pendingDays,
        SUM(CASE WHEN la.leaveStatusID = 4 THEN la.noOfDays ELSE 0 END) AS rejectedDays
    FROM tija_leave_applications la
    LEFT JOIN user_details ud ON la.employeeID = ud.ID
    LEFT JOIN tija_units bu ON ud.businessUnitID = bu.unitID
    {$whereClause}
    GROUP BY departmentName
    ORDER BY totalDays DESC
";
$departmentSummary = $DBConn->fetch_all_rows($departmentSummarySql, $queryParams);

$employeeFilters = array('entityID' => $entityID, 'Suspended' => 'N');
$entityEmployees = Employee::employees($employeeFilters, false, $DBConn);
$leaveBalanceRows = array();

if ($entityEmployees) {
    foreach ($entityEmployees as $employee) {
        $balances = Leave::calculate_leave_balances($employee->ID, $entityID, $DBConn);
        if (!$balances) {
            continue;
        }

        $employeeName = trim(($employee->FirstName ?? '') . ' ' . ($employee->Surname ?? ''));
        $jobTitle = $employee->jobTitle ?? '';

        foreach ($balances as $leaveKey => $balance) {
            $leaveBalanceRows[] = array(
                'employeeId' => $employee->ID,
                'employeeName' => $employeeName !== '' ? $employeeName : ($employee->Email ?? 'Employee #' . $employee->ID),
                'jobTitle' => $jobTitle,
                'leaveType' => ucwords(str_replace('_', ' ', $leaveKey)),
                'total' => $balance['total'] ?? 0,
                'used' => $balance['used'] ?? 0,
                'available' => $balance['available'] ?? 0,
                'percentage' => round($balance['percentage'] ?? 0, 1)
            );
        }
    }
}

$auditWhereParts = array("log.entityType IN ('application','approval','entitlement','policy')");
$auditParams = array();

$auditWhereParts[] = "(log.entityType <> 'application' OR la.entityID = ?)";
$auditParams[] = array((int)$entityID, 'i');

if ($fromDate) {
    $auditWhereParts[] = 'log.performedDate >= ?';
    $auditParams[] = array($fromDate . ' 00:00:00', 's');
}

if ($toDate) {
    $auditWhereParts[] = 'log.performedDate <= ?';
    $auditParams[] = array($toDate . ' 23:59:59', 's');
}

$auditWhere = 'WHERE ' . implode(' AND ', $auditWhereParts);

$leaveAuditTrailSql = "
    SELECT
        log.auditID,
        log.entityType,
        log.entityID,
        log.action,
        log.oldValues,
        log.newValues,
        log.performedDate,
        log.reason,
        CONCAT(perf.FirstName, ' ', perf.Surname) AS performedBy,
        lt.leaveTypeName,
        la.startDate,
        la.endDate,
        la.noOfDays
    FROM tija_leave_audit_log log
    LEFT JOIN people perf ON log.performedByID = perf.ID
    LEFT JOIN tija_leave_applications la
        ON log.entityType = 'application' AND log.entityID = la.leaveApplicationID
    LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
    {$auditWhere}
    ORDER BY log.performedDate DESC
    LIMIT 100
";
$leaveAuditTrail = $DBConn->fetch_all_rows($leaveAuditTrailSql, $auditParams);

function formatAuditValue($value) {
    if ($value === null || $value === '') {
        return '<span class="text-muted">-</span>';
    }

    $decoded = json_decode($value, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        return '<pre class="small mb-0">' . htmlspecialchars(json_encode($decoded, JSON_PRETTY_PRINT)) . '</pre>';
    }

    return '<span class="small">' . htmlspecialchars($value) . '</span>';
}
?>

<style>
.report-card {
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.report-card:hover {
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    transform: translateY(-4px);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: #0052CC;
}

.chart-container {
    position: relative;
    height: 400px;
}
</style>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-24 mb-0">
            <i class="ri-bar-chart-line me-2 text-success"></i>
            <?= $pageTitle ?>
        </h1>
        <p class="text-muted mb-0 mt-2">Comprehensive leave analytics and reporting</p>
    </div>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= $base ?>html/?s=admin&ss=leave&p=dashboard">Leave Admin</a></li>
                <li class="breadcrumb-item active" aria-current="page">Reports</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Filters -->
<form method="get" class="card custom-card mb-4">
    <input type="hidden" name="s" value="admin">
    <input type="hidden" name="ss" value="leave">
    <input type="hidden" name="p" value="reports">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="card-title mb-0">
            <i class="ri-filter-3-line me-2"></i>
            Report Filters
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= $base ?>html/?s=admin&ss=leave&p=reports" class="btn btn-sm btn-outline-secondary">
                <i class="ri-refresh-line me-1"></i>
                Reset
            </a>
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="ri-filter-line me-1"></i>
                Apply Filters
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label for="fromDate" class="form-label small text-uppercase text-muted fw-bold">From Date</label>
                <input type="date" id="fromDate" name="fromDate" class="form-control"
                       value="<?= htmlspecialchars($fromDate) ?>">
            </div>
            <div class="col-md-3">
                <label for="toDate" class="form-label small text-uppercase text-muted fw-bold">To Date</label>
                <input type="date" id="toDate" name="toDate" class="form-control"
                       value="<?= htmlspecialchars($toDate) ?>">
            </div>
            <div class="col-md-3">
                <label for="leaveType" class="form-label small text-uppercase text-muted fw-bold">Leave Type</label>
                <select id="leaveType" name="leaveType" class="form-select">
                    <option value="">All Leave Types</option>
                    <?php if ($leaveTypes): ?>
                        <?php foreach ($leaveTypes as $type): ?>
                            <option value="<?= $type->leaveTypeID ?>" <?= $leaveTypeFilter === (int)$type->leaveTypeID ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type->leaveTypeName) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label small text-uppercase text-muted fw-bold">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <?php if ($leaveStatuses): ?>
                        <?php foreach ($leaveStatuses as $status): ?>
                            <option value="<?= $status->leaveStatusID ?>" <?= $statusFilter === (int)$status->leaveStatusID ? 'selected' : '' ?>>
                                <?= htmlspecialchars($status->leaveStatusName) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>
    </div>
</form>

<!-- Summary Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="report-card card">
            <div class="card-body text-center">
                <i class="ri-file-list-3-line text-primary" style="font-size: 3rem;"></i>
                <div class="stat-number mt-2"><?= $totalApplications ?></div>
                <p class="text-muted mb-0">Total Applications</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="report-card card">
            <div class="card-body text-center">
                <i class="ri-check-line text-success" style="font-size: 3rem;"></i>
                <div class="stat-number mt-2 text-success">
                    <?= $approvedApplications ?>
                </div>
                <p class="text-muted mb-0">Approved</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="report-card card">
            <div class="card-body text-center">
                <i class="ri-time-line text-warning" style="font-size: 3rem;"></i>
                <div class="stat-number mt-2 text-warning">
                    <?= $pendingApplications ?>
                </div>
                <p class="text-muted mb-0">Pending</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="report-card card">
            <div class="card-body text-center">
                <i class="ri-close-line text-danger" style="font-size: 3rem;"></i>
                <div class="stat-number mt-2 text-danger">
                    <?= $rejectedApplications ?>
                </div>
                <p class="text-muted mb-0">Rejected</p>
            </div>
        </div>
    </div>
</div>

<!-- Report Options -->
<div class="row">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="ri-file-chart-line me-2"></i>
                    Available Reports
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card border">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="ri-calendar-2-line me-2 text-primary"></i>
                                    Leave Usage Report
                                </h5>
                                <p class="card-text text-muted">
                                    Detailed breakdown of leave usage by type, department, and employee
                                </p>
                                <button class="btn btn-primary" onclick="generateLeaveUsageReport()">
                                    <i class="ri-file-download-line me-2"></i>
                                    Generate Report
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="ri-team-line me-2 text-success"></i>
                                    Department Leave Summary
                                </h5>
                                <p class="card-text text-muted">
                                    Summary of leave taken by department and business unit
                                </p>
                                <button class="btn btn-success" onclick="generateDepartmentReport()">
                                    <i class="ri-file-download-line me-2"></i>
                                    Generate Report
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="ri-bar-chart-grouped-line me-2 text-info"></i>
                                    Leave Balance Report
                                </h5>
                                <p class="card-text text-muted">
                                    Current leave balances for all employees across leave types
                                </p>
                                <button class="btn btn-info" onclick="generateBalanceReport()">
                                    <i class="ri-file-download-line me-2"></i>
                                    Generate Report
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="ri-history-line me-2 text-warning"></i>
                                    Leave Audit Trail
                                </h5>
                                <p class="card-text text-muted">
                                    Complete audit log of all leave-related activities and approvals
                                </p>
                                <button class="btn btn-warning" onclick="generateAuditReport()">
                                    <i class="ri-file-download-line me-2"></i>
                                    Generate Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mt-4">
                    <i class="ri-information-line me-2"></i>
                    <strong>Note:</strong> Reports can be generated in PDF or Excel format. Select your preferred date range and filters before generating.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Reports -->
<div class="card custom-card mt-4">
    <div class="card-header">
        <div class="card-title d-flex align-items-center">
            <i class="ri-bar-chart-2-line me-2"></i>
            Detailed Leave Reports
        </div>
    </div>
    <div class="card-body">
        <ul class="nav nav-pills justify-content-start gap-2" id="reportTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="usage-tab" data-bs-toggle="pill" data-bs-target="#leaveUsageReport"
                        type="button" role="tab" aria-controls="leaveUsageReport" aria-selected="true">
                    Leave Usage
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="department-tab" data-bs-toggle="pill" data-bs-target="#departmentSummary"
                        type="button" role="tab" aria-controls="departmentSummary" aria-selected="false">
                    Department Summary
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="balance-tab" data-bs-toggle="pill" data-bs-target="#leaveBalanceReport"
                        type="button" role="tab" aria-controls="leaveBalanceReport" aria-selected="false">
                    Leave Balances
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="audit-tab" data-bs-toggle="pill" data-bs-target="#leaveAuditTrail"
                        type="button" role="tab" aria-controls="leaveAuditTrail" aria-selected="false">
                    Audit Trail
                </button>
            </li>
        </ul>

        <div class="tab-content mt-4" id="reportTabsContent">
            <!-- Leave Usage -->
            <div class="tab-pane fade show active" id="leaveUsageReport" role="tabpanel" aria-labelledby="usage-tab">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Leave Type</th>
                                <th class="text-center">Applications</th>
                                <th class="text-center">Unique Employees</th>
                                <th class="text-end">Total Days</th>
                                <th class="text-end">Approved Days</th>
                                <th class="text-end">Pending Days</th>
                                <th class="text-end">Rejected Days</th>
                                <th class="text-end">Avg Days / Application</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($leaveUsageReport): ?>
                                <?php foreach ($leaveUsageReport as $usage): ?>
                                    <?php
                                        $applicationsCount = (int)($usage->totalApplications ?? 0);
                                        $totalDays = (float)($usage->totalDays ?? 0);
                                        $approvedDays = (float)($usage->approvedDays ?? 0);
                                        $pendingDays = (float)($usage->pendingDays ?? 0);
                                        $rejectedDays = (float)($usage->rejectedDays ?? 0);
                                        $avgDays = $applicationsCount > 0 ? $totalDays / $applicationsCount : 0;
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($usage->leaveTypeName ?? 'Unknown') ?></td>
                                        <td class="text-center"><?= number_format($applicationsCount) ?></td>
                                        <td class="text-center"><?= number_format((int)($usage->uniqueEmployees ?? 0)) ?></td>
                                        <td class="text-end"><?= number_format($totalDays, 1) ?></td>
                                        <td class="text-end text-success"><?= number_format($approvedDays, 1) ?></td>
                                        <td class="text-end text-warning"><?= number_format($pendingDays, 1) ?></td>
                                        <td class="text-end text-danger"><?= number_format($rejectedDays, 1) ?></td>
                                        <td class="text-end"><?= number_format($avgDays, 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No leave usage data available for the selected filters.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Department Summary -->
            <div class="tab-pane fade" id="departmentSummary" role="tabpanel" aria-labelledby="department-tab">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Department</th>
                                <th class="text-center">Applications</th>
                                <th class="text-center">Unique Employees</th>
                                <th class="text-end">Approved Days</th>
                                <th class="text-end">Pending Days</th>
                                <th class="text-end">Rejected Days</th>
                                <th class="text-end">Total Days</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($departmentSummary): ?>
                                <?php foreach ($departmentSummary as $department): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($department->departmentName ?? 'Unassigned') ?></td>
                                        <td class="text-center"><?= number_format((int)($department->totalApplications ?? 0)) ?></td>
                                        <td class="text-center"><?= number_format((int)($department->uniqueEmployees ?? 0)) ?></td>
                                        <td class="text-end text-success"><?= number_format((float)($department->approvedDays ?? 0), 1) ?></td>
                                        <td class="text-end text-warning"><?= number_format((float)($department->pendingDays ?? 0), 1) ?></td>
                                        <td class="text-end text-danger"><?= number_format((float)($department->rejectedDays ?? 0), 1) ?></td>
                                        <td class="text-end fw-bold"><?= number_format((float)($department->totalDays ?? 0), 1) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No department summary data available for the selected filters.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Leave Balance Report -->
            <div class="tab-pane fade" id="leaveBalanceReport" role="tabpanel" aria-labelledby="balance-tab">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Employee</th>
                                <th>Job Title</th>
                                <th>Leave Type</th>
                                <th class="text-end">Allocated Days</th>
                                <th class="text-end">Used Days</th>
                                <th class="text-end">Available Days</th>
                                <th class="text-end">Usage %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($leaveBalanceRows)): ?>
                                <?php foreach ($leaveBalanceRows as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['employeeName']) ?></td>
                                        <td><?= htmlspecialchars($row['jobTitle'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($row['leaveType']) ?></td>
                                        <td class="text-end"><?= number_format((float)$row['total'], 1) ?></td>
                                        <td class="text-end text-danger"><?= number_format((float)$row['used'], 1) ?></td>
                                        <td class="text-end text-success"><?= number_format((float)$row['available'], 1) ?></td>
                                        <td class="text-end"><?= number_format((float)$row['percentage'], 1) ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No leave balance data available to display.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Leave Audit Trail -->
            <div class="tab-pane fade" id="leaveAuditTrail" role="tabpanel" aria-labelledby="audit-tab">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>User</th>
                                <th>Entity</th>
                                <th>Action</th>
                                <th>Leave Type</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($leaveAuditTrail): ?>
                                <?php foreach ($leaveAuditTrail as $audit): ?>
                                    <tr>
                                        <td><?= $audit->performedDate ? date('M d, Y H:i', strtotime($audit->performedDate)) : '-' ?></td>
                                        <td><?= htmlspecialchars($audit->performedBy ?? 'System') ?></td>
                                        <td><?= htmlspecialchars(ucfirst($audit->entityType ?? 'unknown')) ?> #<?= htmlspecialchars($audit->entityID ?? '-') ?></td>
                                        <td><?= htmlspecialchars(ucfirst($audit->action ?? 'update')) ?></td>
                                        <td><?= htmlspecialchars($audit->leaveTypeName ?? '-') ?></td>
                                        <td>
                                            <div class="d-flex flex-column gap-2">
                                                <div>
                                                    <span class="badge bg-light text-dark">Old</span>
                                                    <?= formatAuditValue($audit->oldValues ?? '') ?>
                                                </div>
                                                <div>
                                                    <span class="badge bg-primary text-light">New</span>
                                                    <?= formatAuditValue($audit->newValues ?? '') ?>
                                                </div>
                                                <?php if (!empty($audit->reason)): ?>
                                                    <div class="small text-muted"><strong>Reason:</strong> <?= htmlspecialchars($audit->reason) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No audit records available for the selected filters.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Back Button -->
<div class="row mt-4">
    <div class="col-12">
        <a href="<?= $base ?>html/?s=admin&ss=leave&p=dashboard" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i>
            Back to Dashboard
        </a>
    </div>
</div>

<script>
function showReportTab(tabId) {
    const trigger = document.querySelector(`[data-bs-target="#${tabId}"]`);
    if (trigger) {
        const tabInstance = bootstrap.Tab.getOrCreateInstance(trigger);
        tabInstance.show();

        const target = document.getElementById(tabId);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
}

function generateLeaveUsageReport() {
    showReportTab('leaveUsageReport');
}

function generateDepartmentReport() {
    showReportTab('departmentSummary');
}

function generateBalanceReport() {
    showReportTab('leaveBalanceReport');
}

function generateAuditReport() {
    showReportTab('leaveAuditTrail');
}
</script>

