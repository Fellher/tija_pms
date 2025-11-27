<?php
/**
 * Get Template Details
 * Returns template information for configuration
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

    if ($templates && count($templates) > 0) {
        $template = $templates[0];

        echo json_encode([
            'success' => true,
            'template' => [
                'templateID' => $template->templateID,
                'templateName' => $template->templateName,
                'templateDescription' => $template->templateDescription,
                'isSystemTemplate' => $template->isSystemTemplate
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Template not found']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

