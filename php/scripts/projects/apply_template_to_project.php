<?php
/**
 * APPLY TEMPLATE TO EXISTING PROJECT
 * ===================================
 *
 * Applies a project plan template to an existing project
 * Creates phases based on template with automatic date distribution
 *
 * @package    TIJA_PMS
 * @subpackage Project_Management
 */

session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';

header('Content-Type: application/json');

$DBConn->begin();

try {
    if (!$isValidUser) {
        throw new Exception('Authentication required');
    }

    $action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : null;
    $projectID = isset($_POST['projectID']) ? Utility::clean_string($_POST['projectID']) : null;
    $templateID = isset($_POST['templateID']) ? Utility::clean_string($_POST['templateID']) : null;
    $applyMode = isset($_POST['applyMode']) ? Utility::clean_string($_POST['applyMode']) : 'replace';
    $projectStart = isset($_POST['projectStart']) ? Utility::clean_string($_POST['projectStart']) : null;
    $projectEnd = isset($_POST['projectEnd']) ? Utility::clean_string($_POST['projectEnd']) : null;
    $orgDataID = isset($_POST['orgDataID']) ? Utility::clean_string($_POST['orgDataID']) : null;
    $entityID = isset($_POST['entityID']) ? Utility::clean_string($_POST['entityID']) : null;

    if (!$projectID || !$templateID) {
        throw new Exception('Project ID and Template ID are required');
    }

    // Verify project exists and user has access
    $project = Projects::projects_full(array('projectID' => $projectID), true, $DBConn);
    if (!$project) {
        throw new Exception('Project not found');
    }

    // Get template with phases
    $template = $DBConn->retrieve_db_table_rows('tija_project_plan_templates',
        ['*'],
        array('templateID' => $templateID, 'isActive' => 'Y')
    );

    if (!$template || count($template) === 0) {
        throw new Exception('Template not found');
    }

    $templateData = $template[0];

    // Get template phases
    $query = "SELECT * FROM tija_project_plan_template_phases
              WHERE templateID = ?
              ORDER BY phaseOrder ASC";
    $DBConn->query($query);
    $DBConn->bind(1, $templateID);
    $phases = $DBConn->resultSet();

    if (!$phases || count($phases) === 0) {
        throw new Exception('Template has no phases defined');
    }

    // Handle existing phases based on apply mode
    if ($applyMode === 'replace') {
        // Delete existing project phases
        $query = "DELETE FROM tija_project_phases WHERE projectID = ?";
        $DBConn->query($query);
        $DBConn->bind(1, $projectID);
        $DBConn->execute();
    }

    // Calculate phase dates based on project timeline
    $phasesCreated = 0;
    $projectDuration = 0;

    if ($projectStart && $projectEnd) {
        $startDate = new DateTime($projectStart);
        $endDate = new DateTime($projectEnd);
        $projectDuration = $startDate->diff($endDate)->days;
    }

    foreach ($phases as $index => $phase) {
        // Calculate phase start and end dates
        $phaseStartDate = null;
        $phaseEndDate = null;

        if ($projectStart && $projectEnd && $projectDuration > 0) {
            $percent = $phase->durationPercent ? floatval($phase->durationPercent) / 100 : (1 / count($phases));
            $phaseDays = round($projectDuration * $percent);

            // Calculate cumulative days for this phase
            $cumulativeDays = 0;
            for ($i = 0; $i < $index; $i++) {
                $prevPercent = $phases[$i]->durationPercent ? floatval($phases[$i]->durationPercent) / 100 : (1 / count($phases));
                $cumulativeDays += round($projectDuration * $prevPercent);
            }

            $phaseStart = new DateTime($projectStart);
            $phaseStart->modify("+{$cumulativeDays} days");
            $phaseEnd = clone $phaseStart;
            $phaseEnd->modify("+{$phaseDays} days");

            $phaseStartDate = $phaseStart->format('Y-m-d');
            $phaseEndDate = $phaseEnd->format('Y-m-d');

            // Ensure last phase ends on project end date
            if ($index == count($phases) - 1) {
                $phaseEndDate = $projectEnd;
            }
        }

        // Create phase record
        $phaseData = array(
            'projectID' => $projectID,
            'projectPhaseName' => $phase->phaseName,
            'phaseStartDate' => $phaseStartDate,
            'phaseEndDate' => $phaseEndDate,
            'LastUpdate' => $config['currentDateTimeFormated'],
            'LastUpdatedByID' => $userDetails->ID
        );

        if ($DBConn->insert_data('tija_project_phases', $phaseData)) {
            $phasesCreated++;
        }
    }

    // Update template usage count
    $query = "UPDATE tija_project_plan_templates
              SET usageCount = usageCount + 1,
                  lastUsedDate = NOW()
              WHERE templateID = ?";
    $DBConn->query($query);
    $DBConn->bind(1, $templateID);
    $DBConn->execute();

    $DBConn->commit();

    echo json_encode([
        'success' => true,
        'phasesCreated' => $phasesCreated,
        'message' => "Template '{$templateData->templateName}' applied successfully with {$phasesCreated} phases"
    ]);

} catch (Exception $e) {
    $DBConn->rollBack();
    error_log("Apply template error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>

