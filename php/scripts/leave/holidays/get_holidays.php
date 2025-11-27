<?php
/**
 * Get Holidays Script
 *
 * Retrieves holidays for leave calculation
 */

// Include necessary files
session_start();
$base = '../../../../';
set_include_path($base);
include 'php/includes.php';

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Get entity ID or country ID from request
    $entityID = isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : '';
    $countryID = isset($_GET['countryID']) ? Utility::clean_string($_GET['countryID']) : '';

    // Build where clause
    $whereArr = array('Suspended' => 'N', 'Lapsed' => 'N');

    if (!empty($countryID)) {
        $whereArr['countryID'] = $countryID;
    }

    // Get holidays
    $holidays = Data::holidays($whereArr, false, $DBConn);

    // Format holidays for JavaScript
    $holidayDates = array();
    if ($holidays && is_array($holidays)) {
        foreach ($holidays as $holiday) {
            $holidayDates[] = array(
                'date' => $holiday->holidayDate,
                'name' => $holiday->holidayName,
                'type' => $holiday->holidayType
            );
        }
    }

    echo json_encode([
        'success' => true,
        'holidays' => $holidayDates
    ]);

} catch (Exception $e) {
    error_log('Get holidays error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while retrieving holidays']);
}
?>

