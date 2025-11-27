<?php
/**
 * Check Handover Timers
 *
 * Background job to check for expired peer response timers.
 * Should be run periodically (e.g., every hour via cron).
 */
$base = dirname(__DIR__, 2) . '/../';
set_include_path($base);

require_once __DIR__ . '/../../includes.php';

// This script can be run from command line or via web request
// For web requests, require admin authentication
if (php_sapi_name() !== 'cli') {
    session_start();
    if (!$isValidAdmin && !$isAdmin && !$isSuperAdmin) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    header('Content-Type: application/json');
}

try {
    if (!class_exists('LeaveHandoverTimer')) {
        throw new Exception('LeaveHandoverTimer class not available');
    }

    $expiredTimers = LeaveHandoverTimer::check_expired_timers($DBConn);
    $processed = 0;
    $errors = array();

    foreach ($expiredTimers as $expired) {
        try {
            $result = LeaveHandoverTimer::handle_timer_expiry($expired['handoverID'], $DBConn);
            if ($result) {
                $processed++;
            }
        } catch (Exception $e) {
            $errors[] = "Failed to handle expiry for handoverID {$expired['handoverID']}: " . $e->getMessage();
        }
    }

    $response = array(
        'success' => true,
        'expired_count' => count($expiredTimers),
        'processed' => $processed,
        'errors' => $errors,
        'timestamp' => date('Y-m-d H:i:s')
    );

    if (php_sapi_name() === 'cli') {
        echo "Handover Timer Check Results:\n";
        echo "Expired timers found: " . count($expiredTimers) . "\n";
        echo "Processed: {$processed}\n";
        if (!empty($errors)) {
            echo "Errors: " . count($errors) . "\n";
            foreach ($errors as $error) {
                echo "  - {$error}\n";
            }
        }
    } else {
        echo json_encode($response);
    }

} catch (Exception $e) {
    $errorResponse = array(
        'success' => false,
        'message' => 'Failed to check timers: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    );

    if (php_sapi_name() === 'cli') {
        echo "Error: " . $e->getMessage() . "\n";
    } else {
        echo json_encode($errorResponse);
    }
    exit(1);
}
?>

