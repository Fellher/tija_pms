<?php
/**
 * Generate Annual Recurring Holidays
 * Automatically creates holiday instances for recurring holidays
 */

session_start();
$base = '../../../../';
require_once $base . 'php/includes.php';

header('Content-Type: application/json');

// Check authentication and admin permissions
if (!isset($userDetails->ID) || !$isValidUser || (!$isAdmin && !$isValidAdmin && !$isHRManager)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$currentUserID = $userDetails->ID;
$entityID = $_SESSION['entityID'] ?? 1;

try {
    $action = $_POST['action'] ?? $_GET['action'] ?? 'preview';
    $targetYear = $_POST['year'] ?? $_GET['year'] ?? date('Y');

    if ($action === 'preview') {
        // Preview what holidays will be generated
        $recurringHolidays = Leave::get_recurring_holidays($DBConn);

        if (!$recurringHolidays) {
            echo json_encode([
                'success' => true,
                'message' => 'No recurring holidays found',
                'holidays' => []
            ]);
            exit;
        }

        $preview = [];
        foreach ($recurringHolidays as $holiday) {
            // Get original date parts
            $originalDate = new DateTime($holiday->holidayDate);
            $month = $originalDate->format('m');
            $day = $originalDate->format('d');

            // Create date for target year
            $newDate = sprintf('%04d-%02d-%02d', $targetYear, $month, $day);

            // Check if holiday already exists for this year
            $existing = Leave::holidays([
                'holidayName' => $holiday->holidayName,
                'holidayDate' => $newDate
            ], true, $DBConn);

            $preview[] = [
                'originalID' => $holiday->holidayID,
                'name' => $holiday->holidayName,
                'originalDate' => $holiday->holidayDate,
                'newDate' => $newDate,
                'type' => $holiday->holidayType,
                'country' => $holiday->countryName ?? 'Not set',
                'exists' => $existing ? true : false,
                'jurisdiction' => $holiday->jurisdictionLevel ?? 'country'
            ];
        }

        echo json_encode([
            'success' => true,
            'year' => $targetYear,
            'count' => count($preview),
            'holidays' => $preview
        ]);

    } elseif ($action === 'generate') {
        // Use Leave class method for generation
        $result = Leave::generate_annual_holidays($targetYear, $currentUserID, $DBConn);

        if (count($result['errors']) > 0) {
            throw new Exception('Failed to generate some holidays: ' . implode(', ', $result['errors']));
        }

        echo json_encode([
            'success' => true,
            'message' => "Successfully generated {$result['created']} holidays for year {$targetYear}",
            'created' => $result['created'],
            'skipped' => $result['skipped'],
            'year' => $targetYear
        ]);

    } else {
        throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    if ($DBConn->inTransaction()) {
        $DBConn->rollback();
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

