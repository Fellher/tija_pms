<?php
/**
 * Get Templates API
 *
 * List templates with filters
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

require_once __DIR__ . '/../../../includes.php';

header('Content-Type: application/json');

global $DBConn, $isValidUser;

// Check authentication
if (!$isValidUser) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $filters = [];

    // Apply filters from query parameters
    if (isset($_GET['functionalArea']) && !empty($_GET['functionalArea'])) {
        $filters['functionalArea'] = $_GET['functionalArea'];
    }

    if (isset($_GET['isActive']) && !empty($_GET['isActive'])) {
        $filters['isActive'] = $_GET['isActive'];
    }

    if (isset($_GET['processingMode']) && !empty($_GET['processingMode'])) {
        $filters['processingMode'] = ['IN', [$_GET['processingMode'], 'both']];
    }

    if (isset($_GET['frequencyType']) && !empty($_GET['frequencyType'])) {
        $filters['frequencyType'] = $_GET['frequencyType'];
    }

    if (isset($_GET['search']) && !empty($_GET['search'])) {
        // Search will be handled client-side or via SQL LIKE
        // For now, return all and filter client-side
    }

    $templates = OperationalTaskTemplate::listTemplates($filters, $DBConn);

    echo json_encode([
        'success' => true,
        'templates' => $templates ?: [],
        'count' => is_array($templates) ? count($templates) : 0
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

