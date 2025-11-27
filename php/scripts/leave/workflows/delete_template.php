<?php
/**
 * Delete Template (Soft Delete)
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

if ($templateID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid template ID']);
    exit;
}

try {
    // Check if template is a system template
    $checkQuery = "SELECT isSystemTemplate, usageCount FROM tija_leave_workflow_templates
                   WHERE templateID = ?";
    $checkParams = array(array($templateID, 'i'));
    $templates = $DBConn->fetch_all_rows($checkQuery, $checkParams);

    if (!$templates || count($templates) === 0) {
        throw new Exception('Template not found');
    }

    $template = $templates[0];

    // Cannot delete system templates
    if ($template->isSystemTemplate === 'Y') {
        throw new Exception('Cannot delete system templates. You can clone them instead.');
    }

    // Warn if template is in use
    if ($template->usageCount > 0) {
        // Soft delete only
        $updateData = array(
            'Suspended' => 'Y',
            'updatedAt' => $config['currentDateTimeFormated']
        );
    } else {
        // Can fully soft delete
        $updateData = array(
            'Suspended' => 'Y',
            'updatedAt' => $config['currentDateTimeFormated']
        );
    }

    $where = array('templateID' => $templateID);

    if ($DBConn->update_table('tija_leave_workflow_templates', $updateData, $where)) {
        echo json_encode([
            'success' => true,
            'message' => 'Template deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete template');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

