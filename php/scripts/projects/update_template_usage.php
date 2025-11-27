<?php
/**
 * UPDATE TEMPLATE USAGE STATISTICS
 * =================================
 *
 * Updates template usage count and last used date
 *
 * @package    TIJA_PMS
 * @subpackage Project_Management
 */

session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';

header('Content-Type: application/json');

if (!$isValidUser) {
    echo json_encode(['success' => false]);
    exit();
}

$templateID = isset($_POST['templateID']) ? Utility::clean_string($_POST['templateID']) : null;

if ($templateID) {
    try {
        // Increment usage count and update last used date
        $query = "UPDATE tija_project_plan_templates
                  SET usageCount = usageCount + 1,
                      lastUsedDate = NOW()
                  WHERE templateID = ?";

        $DBConn->query($query);
        $DBConn->bind(1, $templateID);
        $DBConn->execute();

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        error_log("Template usage update error: " . $e->getMessage());
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
}
?>

