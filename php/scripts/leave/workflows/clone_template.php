<?php
/**
 * Clone Template
 * Creates a copy of an existing template
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

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$templateID = isset($input['templateID']) ? intval($input['templateID']) : 0;
$entityID = isset($input['entityID']) ? intval($input['entityID']) : 0;

if ($templateID <= 0 || $entityID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    $DBConn->begin();

    // Get source template
    $query = "SELECT * FROM tija_leave_workflow_templates WHERE templateID = ?";
    $params = array(array($templateID, 'i'));
    $templates = $DBConn->fetch_all_rows($query, $params);

    if (!$templates || count($templates) === 0) {
        throw new Exception('Source template not found');
    }

    $sourceTemplate = $templates[0];

    // Create cloned template
    $clonedData = array(
        'templateName' => $sourceTemplate->templateName . ' (Copy)',
        'templateDescription' => $sourceTemplate->templateDescription,
        'isSystemTemplate' => 'N',
        'isPublic' => 'N', // Clone as private
        'createdBy' => $userDetails->ID,
        'createdForEntityID' => $entityID,
        'usageCount' => 0,
        'createdAt' => $config['currentDateTimeFormated'],
        'Suspended' => 'N'
    );

    if (!$DBConn->insert_data('tija_leave_workflow_templates', $clonedData)) {
        throw new Exception('Failed to clone template');
    }

    $newTemplateID = $DBConn->lastInsertId();

    // Clone template steps
    $stepsQuery = "SELECT * FROM tija_leave_workflow_template_steps
                   WHERE templateID = ?
                   ORDER BY stepOrder";
    $stepsParams = array(array($templateID, 'i'));
    $steps = $DBConn->fetch_all_rows($stepsQuery, $stepsParams);

    if ($steps && count($steps) > 0) {
        foreach ($steps as $step) {
            $stepData = array(
                'templateID' => $newTemplateID,
                'stepOrder' => $step->stepOrder,
                'stepName' => $step->stepName,
                'stepType' => $step->stepType,
                'stepDescription' => $step->stepDescription,
                'isRequired' => $step->isRequired,
                'isConditional' => $step->isConditional,
                'conditionType' => $step->conditionType,
                'conditionValue' => $step->conditionValue,
                'escalationDays' => $step->escalationDays,
                'notifySettings' => $step->notifySettings
            );

            if (!$DBConn->insert_data('tija_leave_workflow_template_steps', $stepData)) {
                throw new Exception('Failed to clone template steps');
            }
        }
    }

    $DBConn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Template cloned successfully',
        'newTemplateID' => $newTemplateID
    ]);

} catch (Exception $e) {
    $DBConn->rollBack();

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

