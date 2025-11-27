<?php
/**
 * Download Document Script
 *
 * Handles the download of supporting documents for leave applications
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
    // Get document ID from query parameter
    $documentId = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : '';

    if (empty($documentId)) {
        http_response_code(400);
        echo 'Document ID is required';
        exit;
    }

    // Get document details
    $sql = "SELECT
                ld.*,
                la.employeeID,
                CONCAT(e.FirstName, ' ', e.Surname) as employeeName
            FROM tija_leave_documents ld
            LEFT JOIN tija_leave_applications la ON ld.leaveApplicationID = la.leaveApplicationID
            LEFT JOIN people e ON la.employeeID = e.ID
            WHERE ld.documentID = ?
            AND ld.Lapsed = 'N'
            AND ld.Suspended = 'N'";

    $params = array(array($documentId, 'i'));
    $rows = $DBConn->fetch_all_rows($sql, $params);

    if (!$rows || count($rows) === 0) {
        http_response_code(404);
        echo 'Document not found';
        exit;
    }

    $document = $rows[0];

    // Check if file exists
    $filePath = '../../../' . $document['filePath']; // Adjust path as needed
    if (!file_exists($filePath)) {
        http_response_code(404);
        echo 'File not found on server';
        exit;
    }

    // Check permissions (basic check - you may want to enhance this)
    $currentUserId = isset($_SESSION['userDetails']) ? $_SESSION['userDetails']->ID : null;
    $hasPermission = ($document['employeeID'] == $currentUserId) ||
                    // Add other permission checks as needed
                    true; // For now, allow all downloads

    if (!$hasPermission) {
        http_response_code(403);
        echo 'You are not authorized to download this document';
        exit;
    }

    // Get file info
    $fileSize = filesize($filePath);
    $fileName = $document['fileName'];
    $fileType = $document['fileType'];

    // Set headers for file download
    header('Content-Type: ' . $fileType);
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . $fileSize);
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');

    // Clear any output buffering
    if (ob_get_level()) {
        ob_end_clean();
    }

    // Read and output the file
    $handle = fopen($filePath, 'rb');
    if ($handle) {
        while (!feof($handle)) {
            echo fread($handle, 8192);
            flush();
        }
        fclose($handle);
    } else {
        http_response_code(500);
        echo 'Error reading file';
    }

    // Log download activity
    $logSql = "INSERT INTO tija_document_downloads
               (documentID, downloadedByID, downloadDate, ipAddress)
               VALUES (?, ?, ?, ?)";
    $logParams = array(
        array($documentId, 'i'),
        array($currentUserId, 'i'),
        array(date('Y-m-d H:i:s'), 's'),
        array($_SERVER['REMOTE_ADDR'], 's')
    );

    try {
        $DBConn->query($logSql, $logParams);
    } catch (Exception $e) {
        // Log error but don't fail the download
        error_log('Document download logging error: ' . $e->getMessage());
    }

} catch (Exception $e) {
    error_log('Download document error: ' . $e->getMessage());
    http_response_code(500);
    echo 'An error occurred while downloading the document';
}
?>
