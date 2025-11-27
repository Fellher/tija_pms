<?php
/**
 * Toggle Template Visibility (Public/Private)
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
    // Get current visibility
    $query = "SELECT isPublic, isSystemTemplate FROM tija_leave_workflow_templates
              WHERE templateID = ? AND Suspended = 'N'";
    $params = array(array($templateID, 'i'));
    $templates = $DBConn->fetch_all_rows($query, $params);

    if (!$templates || count($templates) === 0) {
        throw new Exception('Template not found');
    }

    $template = $templates[0];

    // Cannot change visibility of system templates
    if ($template->isSystemTemplate === 'Y') {
        throw new Exception('Cannot change visibility of system templates');
    }

    $currentVisibility = $template->isPublic;
    $newVisibility = ($currentVisibility === 'Y') ? 'N' : 'Y';

    // Update visibility
    $updateData = array(
        'isPublic' => $newVisibility,
        'updatedAt' => $config['currentDateTimeFormated']
    );

    $where = array('templateID' => $templateID);

    if ($DBConn->update_table('tija_leave_workflow_templates', $updateData, $where)) {
        echo json_encode([
            'success' => true,
            'message' => 'Template visibility updated',
            'isPublic' => $newVisibility
        ]);
    } else {
        throw new Exception('Failed to update template visibility');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

