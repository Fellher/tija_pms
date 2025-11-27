<?php
/**
 * Check Employee Holiday Applicability
 * Utility to determine which holidays apply to a specific employee
 * Used in leave calculations to exclude applicable holidays from leave days
 */

session_start();
$base = '../../../../';
require_once $base . 'php/includes.php';

/**
 * Get holidays applicable to an employee for a date range
 *
 * @param int $employeeID The employee ID
 * @param string $startDate Start date (Y-m-d)
 * @param string $endDate End date (Y-m-d)
 * @param object $DBConn Database connection
 * @return array List of applicable holidays
 */
function getEmployeeApplicableHolidays($employeeID, $startDate, $endDate, $DBConn) {
    // Get employee details with entity information
    $employee = Employee::employees(['ID' => $employeeID], true, $DBConn);

    if (!$employee) {
        return [];
    }

    // Get all holidays in the date range
    $params = [];
    $whereConditions = [
        "h.holidayDate >= ?",
        "h.holidayDate <= ?",
        "h.Lapsed = 'N'",
        "h.Suspended = 'N'"
    ];
    $params[] = [$startDate, 's'];
    $params[] = [$endDate, 's'];

    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

    $sql = "SELECT
        h.holidayID,
        h.holidayName,
        h.holidayDate,
        h.holidayType,
        h.countryID,
        h.jurisdictionLevel,
        h.regionID,
        h.cityID,
        h.entitySpecific,
        h.applyToEmploymentTypes,
        h.excludeBusinessUnits,
        h.affectsLeaveBalance,
        c.countryName
    FROM tija_holidays h
    LEFT JOIN african_countries c ON h.countryID = c.countryID
    $whereClause
    ORDER BY h.holidayDate ASC";

    $allHolidays = $DBConn->fetch_all_rows($sql, $params);

    if (!$allHolidays) {
        return [];
    }

    // Filter holidays based on employee's jurisdiction
    $applicableHolidays = [];

    foreach ($allHolidays as $holiday) {
        if (isHolidayApplicableToEmployee($holiday, $employee)) {
            $applicableHolidays[] = $holiday;
        }
    }

    return $applicableHolidays;
}

/**
 * Check if a specific holiday applies to an employee
 *
 * @param object $holiday Holiday object
 * @param object $employee Employee object
 * @return bool True if holiday applies to employee
 */
function isHolidayApplicableToEmployee($holiday, $employee) {
    // Check if holiday affects leave balance
    if (isset($holiday->affectsLeaveBalance) && $holiday->affectsLeaveBalance === 'N') {
        return false;
    }

    // Check jurisdiction level
    $jurisdictionLevel = $holiday->jurisdictionLevel ?? 'country';

    switch ($jurisdictionLevel) {
        case 'global':
            // Applies to all employees
            $jurisdictionMatch = true;
            break;

        case 'country':
            // Check if employee's entity is in the same country
            $jurisdictionMatch = ($employee->entityCountry == $holiday->countryID);
            break;

        case 'region':
            // Check country and region match
            $countryMatch = ($employee->entityCountry == $holiday->countryID);
            $regionMatch = false;

            if (!empty($holiday->regionID) && !empty($employee->entityCity)) {
                $regionMatch = (stripos($employee->entityCity, $holiday->regionID) !== false);
            }

            $jurisdictionMatch = $countryMatch && $regionMatch;
            break;

        case 'city':
            // Check city match
            $jurisdictionMatch = false;
            if (!empty($holiday->cityID) && !empty($employee->entityCity)) {
                $jurisdictionMatch = (stripos($employee->entityCity, $holiday->cityID) !== false);
            }
            break;

        case 'entity':
            // Check if employee's entity is in the list
            $jurisdictionMatch = false;
            if (!empty($holiday->entitySpecific)) {
                $entities = explode(',', $holiday->entitySpecific);
                $jurisdictionMatch = in_array($employee->entityID, $entities) || in_array('all', $entities);
            }
            break;

        default:
            // Legacy: check country only
            $jurisdictionMatch = (empty($holiday->countryID) ||
                                $holiday->countryID === 'all' ||
                                $employee->entityCountry == $holiday->countryID);
    }

    if (!$jurisdictionMatch) {
        return false;
    }

    // Check employment type filter
    if (!empty($holiday->applyToEmploymentTypes) && $holiday->applyToEmploymentTypes !== 'all') {
        $types = explode(',', $holiday->applyToEmploymentTypes);
        if (!empty($employee->employmentType) && !in_array($employee->employmentType, $types)) {
            return false;
        }
    }

    // Check excluded business units
    if (!empty($holiday->excludeBusinessUnits) && !empty($employee->businessUnitID)) {
        $excludedUnits = explode(',', $holiday->excludeBusinessUnits);
        if (in_array($employee->businessUnitID, $excludedUnits)) {
            return false;
        }
    }

    return true;
}

/**
 * Count working days excluding applicable holidays
 *
 * @param int $employeeID Employee ID
 * @param string $startDate Start date
 * @param string $endDate End date
 * @param object $DBConn Database connection
 * @return array ['totalDays' => int, 'workingDays' => int, 'holidays' => array]
 */
function calculateWorkingDaysWithHolidays($employeeID, $startDate, $endDate, $DBConn) {
    $applicableHolidays = getEmployeeApplicableHolidays($employeeID, $startDate, $endDate, $DBConn);

    // Calculate total calendar days
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $end->modify('+1 day'); // Include end date
    $interval = $start->diff($end);
    $totalDays = $interval->days;

    // Subtract holidays
    $holidayDates = array_map(function($h) { return $h->holidayDate; }, $applicableHolidays);
    $holidayCount = count($holidayDates);

    // Subtract weekends (assuming 5-day work week)
    $weekendDays = 0;
    $current = clone $start;
    while ($current < $end) {
        $dayOfWeek = $current->format('N'); // 1 = Monday, 7 = Sunday
        if ($dayOfWeek == 6 || $dayOfWeek == 7) { // Saturday or Sunday
            if (!in_array($current->format('Y-m-d'), $holidayDates)) {
                $weekendDays++;
            }
        }
        $current->modify('+1 day');
    }

    $workingDays = $totalDays - $holidayCount - $weekendDays;

    return [
        'totalDays' => $totalDays,
        'workingDays' => $workingDays,
        'holidays' => $applicableHolidays,
        'holidayCount' => $holidayCount,
        'weekendDays' => $weekendDays
    ];
}

// If called directly (AJAX endpoint)
if (basename($_SERVER['PHP_SELF']) === 'check_employee_holidays.php') {
    header('Content-Type: application/json');

    $employeeID = $_GET['employeeID'] ?? null;
    $startDate = $_GET['startDate'] ?? null;
    $endDate = $_GET['endDate'] ?? null;

    if (!$employeeID || !$startDate || !$endDate) {
        echo json_encode([
            'success' => false,
            'message' => 'Employee ID, start date, and end date are required'
        ]);
        exit;
    }

    try {
        $result = calculateWorkingDaysWithHolidays($employeeID, $startDate, $endDate, $DBConn);

        echo json_encode([
            'success' => true,
            'data' => $result
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?>

