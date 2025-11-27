<?php
/**
 * Get All Templates by Type
 * Returns templates for template management
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

$type = isset($_GET['type']) ? Utility::clean_string($_GET['type']) : 'system';
$entityID = isset($_GET['entityID']) ? intval($_GET['entityID']) : 0;

try {
    $query = "SELECT t.*, COUNT(s.templateStepID) as stepCount
              FROM tija_leave_workflow_templates t
              LEFT JOIN tija_leave_workflow_template_steps s ON t.templateID = s.templateID
              WHERE t.Suspended = 'N' AND ";

    switch ($type) {
        case 'system':
            $query .= "t.isSystemTemplate = 'Y'";
            $params = array();
            break;

        case 'custom':
            $query .= "t.isSystemTemplate = 'N' AND t.createdForEntityID = ? AND t.isPublic = 'N'";
            $params = array(array($entityID, 'i'));
            break;

        case 'public':
            $query .= "t.isSystemTemplate = 'N' AND t.isPublic = 'Y'";
            $params = array();
            break;

        default:
            $query .= "1=1";
            $params = array();
    }

    $query .= " GROUP BY t.templateID ORDER BY t.usageCount DESC, t.templateName ASC";

    $templates = $DBConn->fetch_all_rows($query, $params);

    if ($templates && count($templates) > 0) {
        $templatesArray = array_map(function($template) {
            return [
                'templateID' => $template->templateID,
                'templateName' => $template->templateName,
                'templateDescription' => $template->templateDescription,
                'isSystemTemplate' => $template->isSystemTemplate,
                'isPublic' => $template->isPublic,
                'usageCount' => $template->usageCount,
                'stepCount' => $template->stepCount
            ];
        }, $templates);

        echo json_encode([
            'success' => true,
            'templates' => $templatesArray
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No templates found']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

