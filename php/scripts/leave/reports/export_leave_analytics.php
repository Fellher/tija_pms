<?php
/**
 * Export Leave Analytics
 * Generates Excel or PDF reports for board presentations
 */

session_start();
$base = '../../../../';
set_include_path($base);
include 'php/includes.php';

if (!$isValidUser) {
    die('Unauthorized access');
}

// Access control - HR Managers and Admins only
$isHrManager = Employee::is_hr_manager($userDetails->ID, $DBConn);
$isAdmin = isset($userDetails->isAdmin) && $userDetails->isAdmin;

if (!$isHrManager && !$isAdmin) {
    die('Access denied');
}

try {
    // Get parameters
    $format = isset($_GET['format']) ? Utility::clean_string($_GET['format']) : 'excel';
    $period = isset($_GET['period']) ? Utility::clean_string($_GET['period']) : 'month';
    $startDate = isset($_GET['start_date']) ? Utility::clean_string($_GET['start_date']) : date('Y-m-d', strtotime('-30 days'));
    $endDate = isset($_GET['end_date']) ? Utility::clean_string($_GET['end_date']) : date('Y-m-d');
    $orgDataID = isset($_GET['orgDataID']) ? (int)$_GET['orgDataID'] : ($userDetails->orgDataID ?? null);
    $entityID = isset($_GET['entityID']) ? (int)$_GET['entityID'] : ($userDetails->entityID ?? null);

    // Fetch all analytics data
    $orgAnalytics = Leave::get_organization_leave_analytics($orgDataID, $entityID, $startDate, $endDate, $DBConn);
    $departmentalBreakdown = Leave::get_departmental_leave_breakdown($orgDataID, $entityID, $startDate, $endDate, $DBConn);
    $leaveTypeDistribution = Leave::get_leave_type_distribution($orgDataID, $entityID, $startDate, $endDate, $DBConn);
    $workflowMetrics = Leave::get_approval_workflow_metrics($orgDataID, $entityID, $startDate, $endDate, $DBConn);
    $monthlyTrends = Leave::get_monthly_leave_trends($orgDataID, $entityID, $startDate, $endDate, $DBConn);

    if ($format === 'excel') {
        exportToExcel($orgAnalytics, $departmentalBreakdown, $leaveTypeDistribution, $workflowMetrics, $monthlyTrends, $period, $startDate, $endDate);
    } elseif ($format === 'pdf') {
        exportToPDF($orgAnalytics, $departmentalBreakdown, $leaveTypeDistribution, $workflowMetrics, $monthlyTrends, $period, $startDate, $endDate);
    } else {
        die('Invalid format');
    }

} catch (Exception $e) {
    error_log('Export error: ' . $e->getMessage());
    die('Error generating report: ' . $e->getMessage());
}

/**
 * Export to Excel format
 */
function exportToExcel($orgAnalytics, $departments, $leaveTypes, $workflow, $trends, $period, $startDate, $endDate) {
    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="Leave_Analytics_' . date('Y-m-d') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Generate HTML table format (Excel will import this)
    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head><meta charset="UTF-8"></head>';
    echo '<body>';

    // Title
    echo '<h1>Leave Analytics Report</h1>';
    echo '<p><strong>Period:</strong> ' . htmlspecialchars(ucfirst($period)) . ' (' . htmlspecialchars($startDate) . ' to ' . htmlspecialchars($endDate) . ')</p>';
    echo '<p><strong>Generated:</strong> ' . date('Y-m-d H:i:s') . '</p>';
    echo '<br>';

    // Executive Summary
    echo '<h2>Executive Summary</h2>';
    echo '<table border="1" cellpadding="5" cellspacing="0">';
    echo '<tr><th>Metric</th><th>Value</th></tr>';
    echo '<tr><td>Total Leave Days (Approved)</td><td>' . number_format($orgAnalytics['approvedLeaveDays'], 1) . '</td></tr>';
    echo '<tr><td>Total Applications</td><td>' . $orgAnalytics['totalApplications'] . '</td></tr>';
    echo '<tr><td>Approved Applications</td><td>' . $orgAnalytics['approvedApplications'] . '</td></tr>';
    echo '<tr><td>Rejected Applications</td><td>' . $orgAnalytics['rejectedApplications'] . '</td></tr>';
    echo '<tr><td>Pending Applications</td><td>' . $orgAnalytics['pendingApplications'] . '</td></tr>';
    echo '<tr><td>Utilization Rate</td><td>' . $orgAnalytics['utilizationRate'] . '%</td></tr>';
    echo '<tr><td>Employees on Leave (Current)</td><td>' . $orgAnalytics['employeesOnLeave'] . '</td></tr>';
    echo '<tr><td>Total Employees</td><td>' . $orgAnalytics['totalEmployees'] . '</td></tr>';
    echo '<tr><td>Average Application Days</td><td>' . $orgAnalytics['averageApplicationDays'] . '</td></tr>';
    echo '<tr><td>Top Leave Type</td><td>' . htmlspecialchars($orgAnalytics['topLeaveType']) . '</td></tr>';
    echo '<tr><td>Peak Absence Period</td><td>' . htmlspecialchars($orgAnalytics['peakAbsencePeriod']) . '</td></tr>';
    echo '</table>';
    echo '<br><br>';

    // Departmental Breakdown
    echo '<h2>Departmental Breakdown</h2>';
    echo '<table border="1" cellpadding="5" cellspacing="0">';
    echo '<tr>';
    echo '<th>Department</th>';
    echo '<th>Total Applications</th>';
    echo '<th>Approved</th>';
    echo '<th>Rejected</th>';
    echo '<th>Total Days</th>';
    echo '<th>Approved Days</th>';
    echo '<th>Employees</th>';
    echo '<th>Avg. Days</th>';
    echo '<th>Utilization %</th>';
    echo '</tr>';

    foreach ($departments as $dept) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($dept['departmentName']) . '</td>';
        echo '<td>' . $dept['totalApplications'] . '</td>';
        echo '<td>' . $dept['approvedApplications'] . '</td>';
        echo '<td>' . $dept['rejectedApplications'] . '</td>';
        echo '<td>' . number_format($dept['totalDays'], 1) . '</td>';
        echo '<td>' . number_format($dept['approvedDays'], 1) . '</td>';
        echo '<td>' . $dept['uniqueEmployees'] . '</td>';
        echo '<td>' . $dept['averageDays'] . '</td>';
        echo '<td>' . $dept['utilizationRate'] . '%</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '<br><br>';

    // Leave Type Distribution
    echo '<h2>Leave Type Distribution</h2>';
    echo '<table border="1" cellpadding="5" cellspacing="0">';
    echo '<tr>';
    echo '<th>Leave Type</th>';
    echo '<th>Applications</th>';
    echo '<th>Total Days</th>';
    echo '<th>Avg. Days</th>';
    echo '<th>Employees</th>';
    echo '<th>Percentage</th>';
    echo '</tr>';

    foreach ($leaveTypes as $type) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($type['leaveTypeName']) . '</td>';
        echo '<td>' . $type['applicationCount'] . '</td>';
        echo '<td>' . number_format($type['totalDays'], 1) . '</td>';
        echo '<td>' . $type['averageDays'] . '</td>';
        echo '<td>' . $type['uniqueEmployees'] . '</td>';
        echo '<td>' . $type['percentage'] . '%</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '<br><br>';

    // Workflow Metrics
    echo '<h2>Approval Workflow Metrics</h2>';
    echo '<table border="1" cellpadding="5" cellspacing="0">';
    echo '<tr><th>Metric</th><th>Value</th></tr>';
    echo '<tr><td>Average Approval Time</td><td>' . $workflow['averageApprovalTime'] . ' hours</td></tr>';
    echo '<tr><td>Median Approval Time</td><td>' . $workflow['medianApprovalTime'] . ' hours</td></tr>';
    echo '<tr><td>Approval Rate</td><td>' . $workflow['approvalRate'] . '%</td></tr>';
    echo '<tr><td>Rejection Rate</td><td>' . $workflow['rejectionRate'] . '%</td></tr>';
    echo '</table>';
    echo '<br><br>';

    // Monthly Trends
    echo '<h2>Monthly Trends</h2>';
    echo '<table border="1" cellpadding="5" cellspacing="0">';
    echo '<tr>';
    echo '<th>Month</th>';
    echo '<th>Applications</th>';
    echo '<th>Total Days</th>';
    echo '<th>Employees</th>';
    echo '<th>Avg. Days</th>';
    echo '</tr>';

    foreach ($trends as $trend) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($trend['monthLabel']) . '</td>';
        echo '<td>' . $trend['applicationCount'] . '</td>';
        echo '<td>' . number_format($trend['totalDays'], 1) . '</td>';
        echo '<td>' . $trend['uniqueEmployees'] . '</td>';
        echo '<td>' . $trend['averageDays'] . '</td>';
        echo '</tr>';
    }
    echo '</table>';

    echo '</body></html>';
}

/**
 * Export to PDF format
 */
function exportToPDF($orgAnalytics, $departments, $leaveTypes, $workflow, $trends, $period, $startDate, $endDate) {
    // Check if TCPDF or similar library is available
    // For now, use HTML to PDF conversion

    // Set headers for PDF download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="Leave_Analytics_' . date('Y-m-d') . '.pdf"');

    // If TCPDF or mPDF is not available, fall back to HTML print
    // This will generate a simple PDF via browser print
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Leave Analytics Report</title>
        <style>
            @page { margin: 2cm; }
            body { font-family: Arial, sans-serif; font-size: 12px; }
            h1 { color: #1f2937; font-size: 24px; margin-bottom: 10px; }
            h2 { color: #4f46e5; font-size: 18px; margin-top: 20px; margin-bottom: 10px; border-bottom: 2px solid #4f46e5; padding-bottom: 5px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
            th { background-color: #f3f4f6; font-weight: bold; }
            .metric-box { background: #eff6ff; padding: 10px; margin: 5px 0; border-left: 4px solid #4f46e5; }
        </style>
    </head>
    <body>
        <h1>Leave Analytics Report</h1>
        <p><strong>Period:</strong> <?php echo htmlspecialchars(ucfirst($period)); ?> (<?php echo htmlspecialchars($startDate); ?> to <?php echo htmlspecialchars($endDate); ?>)</p>
        <p><strong>Generated:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>

        <h2>Executive Summary</h2>
        <div class="metric-box">
            <strong>Total Leave Days (Approved):</strong> <?php echo number_format($orgAnalytics['approvedLeaveDays'], 1); ?> days<br>
            <strong>Utilization Rate:</strong> <?php echo $orgAnalytics['utilizationRate']; ?>%<br>
            <strong>Total Applications:</strong> <?php echo $orgAnalytics['totalApplications']; ?>
            (Approved: <?php echo $orgAnalytics['approvedApplications']; ?>,
            Rejected: <?php echo $orgAnalytics['rejectedApplications']; ?>,
            Pending: <?php echo $orgAnalytics['pendingApplications']; ?>)<br>
            <strong>Employees on Leave:</strong> <?php echo $orgAnalytics['employeesOnLeave']; ?> of <?php echo $orgAnalytics['totalEmployees']; ?><br>
            <strong>Top Leave Type:</strong> <?php echo htmlspecialchars($orgAnalytics['topLeaveType']); ?><br>
            <strong>Peak Absence Period:</strong> <?php echo htmlspecialchars($orgAnalytics['peakAbsencePeriod']); ?>
        </div>

        <h2>Departmental Breakdown</h2>
        <table>
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Applications</th>
                    <th>Approved Days</th>
                    <th>Employees</th>
                    <th>Utilization %</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($departments as $dept): ?>
                <tr>
                    <td><?php echo htmlspecialchars($dept['departmentName']); ?></td>
                    <td><?php echo $dept['totalApplications']; ?></td>
                    <td><?php echo number_format($dept['approvedDays'], 1); ?></td>
                    <td><?php echo $dept['uniqueEmployees']; ?></td>
                    <td><?php echo $dept['utilizationRate']; ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Leave Type Distribution</h2>
        <table>
            <thead>
                <tr>
                    <th>Leave Type</th>
                    <th>Applications</th>
                    <th>Total Days</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leaveTypes as $type): ?>
                <tr>
                    <td><?php echo htmlspecialchars($type['leaveTypeName']); ?></td>
                    <td><?php echo $type['applicationCount']; ?></td>
                    <td><?php echo number_format($type['totalDays'], 1); ?></td>
                    <td><?php echo $type['percentage']; ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Approval Workflow Performance</h2>
        <div class="metric-box">
            <strong>Average Approval Time:</strong> <?php echo $workflow['averageApprovalTime']; ?> hours<br>
            <strong>Approval Rate:</strong> <?php echo $workflow['approvalRate']; ?>%<br>
            <strong>Rejection Rate:</strong> <?php echo $workflow['rejectionRate']; ?>%
        </div>

        <?php if (!empty($workflow['stepMetrics'])): ?>
        <h3>Workflow Step Performance</h3>
        <table>
            <thead>
                <tr>
                    <th>Step</th>
                    <th>Total Actions</th>
                    <th>Approved</th>
                    <th>Rejected</th>
                    <th>Avg. Response Time (hours)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($workflow['stepMetrics'] as $step): ?>
                <tr>
                    <td><?php echo htmlspecialchars($step['stepName']); ?></td>
                    <td><?php echo $step['actionCount']; ?></td>
                    <td><?php echo $step['approvedCount']; ?></td>
                    <td><?php echo $step['rejectedCount']; ?></td>
                    <td><?php echo $step['avgResponseTime']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <h2>Monthly Trends</h2>
        <table>
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Applications</th>
                    <th>Total Days</th>
                    <th>Employees</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($trends as $trend): ?>
                <tr>
                    <td><?php echo htmlspecialchars($trend['monthLabel']); ?></td>
                    <td><?php echo $trend['applicationCount']; ?></td>
                    <td><?php echo number_format($trend['totalDays'], 1); ?></td>
                    <td><?php echo $trend['uniqueEmployees']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <br>
        <p style="text-align: center; color: #6b7280; font-size: 10px; margin-top: 40px;">
            This report was generated automatically by the Tija Leave Management System
        </p>
    </body>
    </html>
    <?php
}
?>

