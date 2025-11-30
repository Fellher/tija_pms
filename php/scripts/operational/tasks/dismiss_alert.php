<?php
/**
 * Dismiss Operational Tasks Alert
 *
 * Stores dismissal in session to prevent alert from showing for 24 hours
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

session_start();
$base = '../../../';
set_include_path($base);

require_once 'php/includes.php';

header('Content-Type: application/json');

if (!$isValidUser) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Store dismissal timestamp in session (valid for 24 hours)
$_SESSION['operational_tasks_alert_dismissed'] = time();

echo json_encode(['success' => true]);

