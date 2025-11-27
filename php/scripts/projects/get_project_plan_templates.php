<?php
/**
 * GET PROJECT PLAN TEMPLATES
 * ===========================
 *
 * API endpoint to retrieve project plan templates with their phases
 * Returns JSON for AJAX consumption
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
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit();
}

// Get employee details if not already set
if (!isset($employeeDetails) || !$employeeDetails) {
    $employeeID = isset($userDetails->employeeID) ? $userDetails->employeeID : $userDetails->ID;
    $employeeDetails = Employee::employees(['ID' => $employeeID], true, $DBConn);
}

// Get parameters with proper fallback chain
$orgDataID = null;
$entityID = null;

// Try GET parameters first
if (isset($_GET['orgDataID']) && !empty($_GET['orgDataID'])) {
    $orgDataID = Utility::clean_string($_GET['orgDataID']);
}
if (isset($_GET['entityID']) && !empty($_GET['entityID'])) {
    $entityID = Utility::clean_string($_GET['entityID']);
}

// Fallback to employee details
if (!$orgDataID && isset($employeeDetails) && is_object($employeeDetails) && isset($employeeDetails->orgDataID)) {
    $orgDataID = $employeeDetails->orgDataID;
}
if (!$entityID && isset($employeeDetails) && is_object($employeeDetails) && isset($employeeDetails->entityID)) {
    $entityID = $employeeDetails->entityID;
}

// Fallback to user details
if (!$orgDataID && isset($userDetails) && is_object($userDetails) && isset($userDetails->orgDataID)) {
    $orgDataID = $userDetails->orgDataID;
}
if (!$entityID && isset($userDetails) && is_object($userDetails) && isset($userDetails->entityID)) {
    $entityID = $userDetails->entityID;
}

// Validate required parameters
if (!$orgDataID) {
    echo json_encode(['success' => false, 'error' => 'Organization ID required']);
    exit();
}

$templateID = isset($_GET['templateID']) ? Utility::clean_string($_GET['templateID']) : null;

try {
    if ($templateID) {
        // Get specific template with its phases
        $template = $DBConn->retrieve_db_table_rows('tija_project_plan_templates',
            ['*'],
            array('templateID' => $templateID, 'isActive' => 'Y')
        );

        if ($template && count($template) > 0) {
            $templateData = $template[0];

            // Get phases
            $query = "SELECT * FROM tija_project_plan_template_phases
                      WHERE templateID = ?
                      ORDER BY phaseOrder ASC";
            $DBConn->query($query);
            $DBConn->bind(1, $templateID);
            $phases = $DBConn->resultSet();

            $templateData->phases = $phases ?: array();

            echo json_encode(['success' => true, 'template' => $templateData]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Template not found']);
        }
    } else {
        // Get all templates for organization
        $query = "SELECT t.*,
                         CONCAT(u.FirstName, ' ', u.Surname) as createdByName,
                         COUNT(p.templatePhaseID) as phaseCount
                  FROM tija_project_plan_templates t
                  LEFT JOIN people u ON t.createdByID = u.ID
                  LEFT JOIN tija_project_plan_template_phases p ON t.templateID = p.templateID
                  WHERE t.orgDataID = ?
                  AND t.isActive = 'Y'
                  AND (t.isPublic = 'Y' OR t.createdByID = ?)
                  GROUP BY t.templateID
                  ORDER BY t.isSystemTemplate DESC, t.usageCount DESC, t.templateName ASC";

        $DBConn->query($query);
        $DBConn->bind(1, $orgDataID);
        $DBConn->bind(2, $userDetails->ID);
        $templates = $DBConn->resultSet();

        echo json_encode(['success' => true, 'templates' => $templates ?: array()]);
    }
} catch (Exception $e) {
    error_log("Get templates error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to retrieve templates']);
}
?>

