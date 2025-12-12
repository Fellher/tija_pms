<?php
/**
 * Leave Statistics Export Handler
 * Export leave statistics in CSV or PDF format
 */

// Start session and include necessary files
session_start();
$base = '../../../../';
require_once $base . 'php/includes.php';

// Check admin permissions
if (!isset($userDetails->ID) || !$isAdmin) {
    http_response_code(403);
    die('Access denied');
}

$entityID = $_SESSION['entityID'] ?? 1;
$format = $_GET['format'] ?? 'csv';
$startDate = $_GET['startDate'] ?? date('Y-01-01');
$endDate = $_GET['endDate'] ?? date('Y-12-31');

try {
    // Get statistics data
    $statistics = Leave::get_leave_statistics($entityID, $startDate, $endDate, $DBConn);

    if ($format === 'csv') {
        // Export as CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=leave_statistics_' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');

        // Add CSV headers
        fputcsv($output, array('Metric', 'Value'));

        // Add data rows
        if (is_array($statistics)) {
            foreach ($statistics as $key => $value) {
                fputcsv($output, array($key, $value));
            }
        }

        fclose($output);

    } elseif ($format === 'pdf') {
        // For PDF export, you would need a PDF library like TCPDF or FPDF
        // This is a placeholder for PDF implementation
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename=leave_statistics_' . date('Y-m-d') . '.pdf');

        // TODO: Implement PDF generation
        echo "PDF export is not yet implemented. Please use CSV format.";

    } else {
        throw new Exception('Invalid export format');
    }

} catch (Exception $e) {
    http_response_code(500);
    die('Error exporting statistics: ' . $e->getMessage());
}
?>

