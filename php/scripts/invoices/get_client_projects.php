<?php
/**
 * Get Projects by Client ID
 *
 * Returns active projects for a specific client
 *
 * @package    TIJA_PMS
 * @subpackage Invoicing
 */

session_start();
$base = '../../../';
set_include_path($base);
include 'php/includes.php';

header('Content-Type: application/json');

// Check authentication
if (!$isValidUser) {
    echo json_encode(array('success' => false, 'message' => 'Unauthorized access'));
    exit;
}

$clientID = isset($_GET['clientID']) ? intval($_GET['clientID']) : 0;

if (!$clientID) {
    echo json_encode(array('success' => false, 'message' => 'Client ID is required'));
    exit;
}

try {
    // Get employee details for organization context
    $employeeDetails = Employee::employees(array('ID' => $userDetails->ID), true, $DBConn);

    // Resolve organization and entity IDs
    $orgDataID = isset($_GET['orgDataID'])
        ? Utility::clean_string($_GET['orgDataID'])
        : (isset($employeeDetails->orgDataID) && !empty($employeeDetails->orgDataID)
            ? $employeeDetails->orgDataID
            : (isset($_SESSION['orgDataID']) && !empty($_SESSION['orgDataID'])
                ? $_SESSION['orgDataID']
                : ""));

    $entityID = isset($_GET['entityID'])
        ? Utility::clean_string($_GET['entityID'])
        : (isset($employeeDetails->entityID) && !empty($employeeDetails->entityID)
            ? $employeeDetails->entityID
            : (isset($_SESSION['entityID'])
                ? $_SESSION['entityID']
                : ''));

    // Build where clause
    $whereArr = array(
        'clientID' => $clientID,
        'Suspended' => 'N'
    );

    if ($orgDataID) {
        $whereArr['orgDataID'] = $orgDataID;
    }
    if ($entityID) {
        $whereArr['entityID'] = $entityID;
    }

    // Get projects
    $allProjects = Projects::projects_mini($whereArr, false, $DBConn);

    // Filter to only active projects (not closed, inactive, or cancelled)
    $activeProjects = array();
    if ($allProjects && is_array($allProjects)) {
        foreach ($allProjects as $project) {
            $status = strtolower($project->projectStatus ?? '');
            // Include projects that are not closed, inactive, or cancelled
            if (!in_array($status, array('closed', 'inactive', 'cancelled'))) {
                $activeProjects[] = array(
                    'projectID' => $project->projectID,
                    'projectName' => $project->projectName,
                    'projectCode' => $project->projectCode ?? '',
                    'clientID' => $project->clientID
                );
            }
        }
    }

    echo json_encode(array(
        'success' => true,
        'projects' => $activeProjects
    ));

} catch (Exception $e) {
    echo json_encode(array(
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ));
}

?>

