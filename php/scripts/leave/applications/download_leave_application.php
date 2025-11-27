<?php
/**
 * Download Leave Application PDF Script
 *
 * Generates and downloads a PDF of the leave application
 */

// Include necessary files
session_start();
$base = '../../../../';
set_include_path($base);
include 'php/includes.php';

// Check if user is logged in
if (!$isValidUser) {
    http_response_code(401);
    echo 'Unauthorized access';
    exit;
}

// Check if request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

try {
    // Get leave ID from query parameter
    $leaveId = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : '';

    if (empty($leaveId)) {
        http_response_code(400);
        echo 'Leave ID is required';
        exit;
    }

    // Get leave application details
    $sql = "SELECT
                la.*,
                lt.leaveTypeName,
                ls.leaveStatusName,
                e.FirstName,
                e.Surname,
                e.Email,
                CONCAT(e.FirstName, ' ', e.Surname) as employeeName,
                ud.jobTitleID,
                jt.jobTitle,
                ud.employmentStartDate,
                ud.supervisorID,
                bu.businessUnitName as departmentName,
                supervisor.FirstName as supervisorFirstName,
                supervisor.Surname as supervisorSurname,
                CONCAT(supervisor.FirstName, ' ', supervisor.Surname) as supervisorName,
                org.orgName,
                ent.entityName
            FROM tija_leave_applications la
            LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
            LEFT JOIN tija_leave_status ls ON la.leaveStatusID = ls.leaveStatusID
            LEFT JOIN people e ON la.employeeID = e.ID
            LEFT JOIN user_details ud ON e.ID = ud.ID
            LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
            LEFT JOIN tija_business_units bu ON ud.businessUnitID = bu.businessUnitID
            LEFT JOIN people supervisor ON ud.supervisorID = supervisor.ID
            LEFT JOIN tija_organisation_data org ON ud.orgDataID = org.orgDataID
            LEFT JOIN tija_entities ent ON ud.entityID = ent.entityID
            WHERE la.leaveApplicationID = ?
            AND la.Lapsed = 'N'
            AND la.Suspended = 'N'";

    $params = array(array($leaveId, 'i'));
    $rows = $DBConn->fetch_all_rows($sql, $params);

    if (!$rows || count($rows) === 0) {
        http_response_code(404);
        echo 'Leave application not found';
        exit;
    }

    $leave = $rows[0];

    // Get approval comments
    $commentsSql = "SELECT
                        lac.comment,
                        lac.commentDate,
                        CONCAT(p.FirstName, ' ', p.Surname) as approverName,
                        lac.approvalLevel
                    FROM tija_leave_approval_comments lac
                    LEFT JOIN people p ON lac.approverID = p.ID
                    WHERE lac.leaveApplicationID = ?
                    ORDER BY lac.commentDate ASC";

    $commentsRows = $DBConn->fetch_all_rows($commentsSql, $params);
    $comments = $commentsRows ? $commentsRows : [];

    // Generate PDF content (simplified HTML version for now)
    $html = generateLeaveApplicationHTML($leave, $comments);

    // Set headers for PDF download
    $filename = 'Leave_Application_' . $leaveId . '_' . date('Y-m-d') . '.html';

    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');

    echo $html;

} catch (Exception $e) {
    error_log('Download leave application error: ' . $e->getMessage());
    http_response_code(500);
    echo 'An error occurred while generating the document';
}

/**
 * Generate HTML content for leave application
 */
function generateLeaveApplicationHTML($leave, $comments) {
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Leave Application - ' . $leave['leaveApplicationID'] . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .section { margin-bottom: 20px; }
        .section h3 { background: #f0f0f0; padding: 10px; margin: 0 0 10px 0; }
        .field { margin-bottom: 10px; }
        .field label { font-weight: bold; display: inline-block; width: 150px; }
        .comments { margin-top: 20px; }
        .comment { border-left: 3px solid #007bff; padding-left: 10px; margin-bottom: 10px; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Leave Application</h1>
        <h2>' . $leave['orgName'] . '</h2>
        <p>Application ID: ' . $leave['leaveApplicationID'] . '</p>
    </div>

    <div class="section">
        <h3>Employee Information</h3>
        <div class="field"><label>Name:</label> ' . $leave['employeeName'] . '</div>
        <div class="field"><label>Position:</label> ' . $leave['jobTitle'] . '</div>
        <div class="field"><label>Department:</label> ' . $leave['departmentName'] . '</div>
        <div class="field"><label>Supervisor:</label> ' . $leave['supervisorName'] . '</div>
        <div class="field"><label>Employment Date:</label> ' . date('F j, Y', strtotime($leave['employmentStartDate'])) . '</div>
    </div>

    <div class="section">
        <h3>Leave Details</h3>
        <div class="field"><label>Leave Type:</label> ' . $leave['leaveTypeName'] . '</div>
        <div class="field"><label>Start Date:</label> ' . date('F j, Y', strtotime($leave['startDate'])) . '</div>
        <div class="field"><label>End Date:</label> ' . date('F j, Y', strtotime($leave['endDate'])) . '</div>
        <div class="field"><label>Number of Days:</label> ' . $leave['noOfDays'] . '</div>';

    if ($leave['halfDayLeave'] === 'Y') {
        $html .= '<div class="field"><label>Half Day Period:</label> ' . ucfirst($leave['halfDayPeriod']) . '</div>';
    }

    $html .= '<div class="field"><label>Status:</label> ' . $leave['leaveStatusName'] . '</div>
        <div class="field"><label>Applied Date:</label> ' . date('F j, Y g:i A', strtotime($leave['dateApplied'])) . '</div>
    </div>

    <div class="section">
        <h3>Reason for Leave</h3>
        <p>' . nl2br(htmlspecialchars($leave['leaveReason'])) . '</p>
    </div>';

    if (!empty($leave['emergencyContact'])) {
        $html .= '<div class="section">
            <h3>Emergency Contact</h3>
            <p>' . htmlspecialchars($leave['emergencyContact']) . '</p>
        </div>';
    }

    if (!empty($leave['handoverNotes'])) {
        $html .= '<div class="section">
            <h3>Handover Notes</h3>
            <p>' . nl2br(htmlspecialchars($leave['handoverNotes'])) . '</p>
        </div>';
    }

    if (!empty($comments)) {
        $html .= '<div class="section">
            <h3>Approval Comments</h3>
            <div class="comments">';

        foreach ($comments as $comment) {
            $html .= '<div class="comment">
                <strong>' . $comment['approverName'] . '</strong> (' . ucfirst($comment['approvalLevel']) . ')
                <br><small>' . date('F j, Y g:i A', strtotime($comment['commentDate'])) . '</small>
                <p>' . nl2br(htmlspecialchars($comment['comment'])) . '</p>
            </div>';
        }

        $html .= '</div></div>';
    }

    $html .= '<div class="footer">
        <p>Generated on ' . date('F j, Y g:i A') . '</p>
        <p>This document was automatically generated by the Leave Management System</p>
    </div>
</body>
</html>';

    return $html;
}
?>
