<?php
/**
 * Get Full Template with Steps
 * Returns complete template data for editing
 */

session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';

header('Content-Type: application/json');

// Check if user is admin
if (!$isAdmin) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$templateID = isset($_GET['templateID']) ? intval($_GET['templateID']) : 0;

if ($templateID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid template ID']);
    exit;
}

try {
    // Get template details
    $query = "SELECT * FROM tija_leave_workflow_templates WHERE templateID = ?";
    $params = array(array($templateID, 'i'));
    $templates = $DBConn->fetch_all_rows($query, $params);

    if (!$templates || count($templates) === 0) {
        echo json_encode(['success' => false, 'message' => 'Template not found']);
        exit;
    }

    $template = $templates[0];

    // Get template steps
    $stepsQuery = "SELECT * FROM tija_leave_workflow_template_steps
                   WHERE templateID = ?
                   ORDER BY stepOrder";
    $stepsParams = array(array($templateID, 'i'));
    $steps = $DBConn->fetch_all_rows($stepsQuery, $stepsParams);

    $stepsArray = [];
    if ($steps && count($steps) > 0) {
        $stepsArray = array_map(function($step) {
            return [
                'templateStepID' => $step->templateStepID,
                'stepOrder' => $step->stepOrder,
                'stepName' => $step->stepName,
                'stepType' => $step->stepType,
                'stepDescription' => $step->stepDescription,
                'isRequired' => $step->isRequired,
                'isConditional' => $step->isConditional,
                'escalationDays' => $step->escalationDays
            ];
        }, $steps);
    }

    echo json_encode([
        'success' => true,
        'template' => [
            'templateID' => $template->templateID,
            'templateName' => $template->templateName,
            'templateDescription' => $template->templateDescription,
            'isSystemTemplate' => $template->isSystemTemplate,
            'isPublic' => $template->isPublic
        ],
        'steps' => $stepsArray
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

