<?php
/**
 * Save Template (Create or Update)
 * Handles template CRUD operations
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
$action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : '';
$templateID = isset($_POST['templateID']) && !empty($_POST['templateID']) ? intval($_POST['templateID']) : 0;
$templateName = isset($_POST['templateName']) ? Utility::clean_string($_POST['templateName']) : '';
$templateDescription = isset($_POST['templateDescription']) ? Utility::clean_string($_POST['templateDescription']) : '';
$isPublic = isset($_POST['isPublic']) ? Utility::clean_string($_POST['isPublic']) : 'N';
$createdForEntityID = isset($_POST['createdForEntityID']) ? intval($_POST['createdForEntityID']) : 0;
$templateSteps = isset($_POST['templateSteps']) ? $_POST['templateSteps'] : array();

if (empty($templateName) || empty($templateDescription)) {
    echo json_encode(['success' => false, 'message' => 'Template name and description are required']);
    exit;
}

if (empty($templateSteps) || !is_array($templateSteps)) {
    echo json_encode(['success' => false, 'message' => 'At least one approval step is required']);
    exit;
}

try {
    $DBConn->begin();

    if ($action === 'update_template' && $templateID > 0) {
        // Update existing template
        $updateData = array(
            'templateName' => $templateName,
            'templateDescription' => $templateDescription,
            'isPublic' => $isPublic,
            'updatedAt' => $config['currentDateTimeFormated']
        );

        $where = array('templateID' => $templateID);

        if (!$DBConn->update_table('tija_leave_workflow_templates', $updateData, $where)) {
            throw new Exception('Failed to update template');
        }

        // Delete existing steps
        $deleteQuery = "DELETE FROM tija_leave_workflow_template_steps WHERE templateID = ?";
        $DBConn->query($deleteQuery);
        $DBConn->bind('1', $templateID);
        $DBConn->execute();

        $newTemplateID = $templateID;

    } else {
        // Create new template
        $templateData = array(
            'templateName' => $templateName,
            'templateDescription' => $templateDescription,
            'isSystemTemplate' => 'N',
            'isPublic' => $isPublic,
            'createdBy' => $userDetails->ID,
            'createdForEntityID' => $createdForEntityID,
            'usageCount' => 0,
            'createdAt' => $config['currentDateTimeFormated'],
            'Suspended' => 'N'
        );

        if (!$DBConn->insert_data('tija_leave_workflow_templates', $templateData)) {
            throw new Exception('Failed to create template');
        }

        $newTemplateID = $DBConn->lastInsertId();
    }

    // Insert template steps
    $stepOrder = 1;
    foreach ($templateSteps as $stepData) {
        $stepInsertData = array(
            'templateID' => $newTemplateID,
            'stepOrder' => $stepOrder,
            'stepName' => Utility::clean_string($stepData['name']),
            'stepType' => Utility::clean_string($stepData['type']),
            'stepDescription' => isset($stepData['description']) ? Utility::clean_string($stepData['description']) : null,
            'isRequired' => isset($stepData['required']) ? 'Y' : 'N',
            'isConditional' => isset($stepData['conditional']) ? 'Y' : 'N',
            'escalationDays' => isset($stepData['escalation']) ? intval($stepData['escalation']) : 3,
            'notifySettings' => null
        );

        if (!$DBConn->insert_data('tija_leave_workflow_template_steps', $stepInsertData)) {
            throw new Exception('Failed to create template step');
        }

        $stepOrder++;
    }

    $DBConn->commit();

    echo json_encode([
        'success' => true,
        'message' => $action === 'update_template' ? 'Template updated successfully' : 'Template created successfully',
        'templateID' => $newTemplateID
    ]);

} catch (Exception $e) {
    $DBConn->rollBack();

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

