<?php
/**
 * Get Template Steps
 * Returns template steps for configuration
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
    // Get template steps
    $query = "SELECT * FROM tija_leave_workflow_template_steps
              WHERE templateID = ?
              ORDER BY stepOrder";
    $params = array(array($templateID, 'i'));
    $steps = $DBConn->fetch_all_rows($query, $params);

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

        echo json_encode([
            'success' => true,
            'steps' => $stepsArray
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No steps found for this template']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

