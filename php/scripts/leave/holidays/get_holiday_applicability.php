<?php
/**
 * Get Holiday Applicability
 * Returns list of employees who should observe a specific holiday
 */

session_start();
$base = '../../../../';
require_once $base . 'php/includes.php';

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['userID']) || !$isValidUser) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$holidayID = $_GET['holidayID'] ?? null;

if (!$holidayID) {
    echo json_encode(['success' => false, 'message' => 'Holiday ID is required']);
    exit;
}

try {
    // Get holiday details
    $holiday = Data::holidays(['holidayID' => $holidayID], true, $DBConn);

    if (!$holiday) {
        throw new Exception('Holiday not found');
    }

    // Build employee query based on jurisdiction
    $whereConditions = ["d.Suspended = 'N'", "u.Valid = 'Y'"];
    $params = [];

    // Apply jurisdiction filters
    if (!empty($holiday->jurisdictionLevel)) {
        switch ($holiday->jurisdictionLevel) {
            case 'global':
                // All employees - no additional filter
                break;

            case 'country':
                if (!empty($holiday->countryID) && $holiday->countryID !== 'all') {
                    $whereConditions[] = "e.entityCountry = ?";
                    $params[] = [$holiday->countryID, 's'];
                }
                break;

            case 'region':
                if (!empty($holiday->countryID)) {
                    $whereConditions[] = "e.entityCountry = ?";
                    $params[] = [$holiday->countryID, 's'];
                }
                if (!empty($holiday->regionID)) {
                    $whereConditions[] = "(e.entityCity LIKE ? OR e.entityRegion LIKE ?)";
                    $params[] = ['%' . $holiday->regionID . '%', 's'];
                    $params[] = ['%' . $holiday->regionID . '%', 's'];
                }
                break;

            case 'city':
                if (!empty($holiday->cityID)) {
                    $whereConditions[] = "e.entityCity LIKE ?";
                    $params[] = ['%' . $holiday->cityID . '%', 's'];
                }
                break;

            case 'entity':
                if (!empty($holiday->entitySpecific)) {
                    $entities = explode(',', $holiday->entitySpecific);
                    $entities = array_filter($entities, function($e) { return $e !== 'all'; });

                    if (!empty($entities)) {
                        $placeholders = implode(',', array_fill(0, count($entities), '?'));
                        $whereConditions[] = "d.entityID IN ($placeholders)";
                        foreach ($entities as $eid) {
                            $params[] = [$eid, 's'];
                        }
                    }
                }
                break;
        }
    } else {
        // Legacy: just use country
        if (!empty($holiday->countryID) && $holiday->countryID !== 'all') {
            $whereConditions[] = "e.entityCountry = ?";
            $params[] = [$holiday->countryID, 's'];
        }
    }

    // Apply employment type filters
    if (!empty($holiday->applyToEmploymentTypes) && $holiday->applyToEmploymentTypes !== 'all') {
        $types = explode(',', $holiday->applyToEmploymentTypes);
        $placeholders = implode(',', array_fill(0, count($types), '?'));
        $whereConditions[] = "d.employmentType IN ($placeholders)";
        foreach ($types as $type) {
            $params[] = [$type, 's'];
        }
    }

    // Exclude business units
    if (!empty($holiday->excludeBusinessUnits)) {
        $units = explode(',', $holiday->excludeBusinessUnits);
        $placeholders = implode(',', array_fill(0, count($units), '?'));
        $whereConditions[] = "(d.businessUnitID IS NULL OR d.businessUnitID NOT IN ($placeholders))";
        foreach ($units as $unit) {
            $params[] = [$unit, 's'];
        }
    }

    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

    // Query applicable employees
    $sql = "SELECT
        u.ID,
        CONCAT(u.FirstName, ' ', u.Surname) as employeeName,
        e.entityName,
        e.entityCity,
        e.entityCountry,
        c.countryName,
        d.businessUnitID,
        d.employmentType
    FROM people u
    JOIN user_details d ON u.ID = d.ID
    LEFT JOIN tija_entities e ON d.entityID = e.entityID
    LEFT JOIN african_countries c ON e.entityCountry = c.countryID
    $whereClause
    ORDER BY employeeName ASC
    LIMIT 500";

    $employees = $DBConn->fetch_all_rows($sql, $params);

    // Format response
    $formattedEmployees = [];
    if ($employees) {
        foreach ($employees as $emp) {
            $formattedEmployees[] = [
                'id' => $emp->ID,
                'name' => $emp->employeeName,
                'entity' => $emp->entityName ?? 'N/A',
                'location' => ($emp->entityCity ?? 'N/A') . ', ' . ($emp->countryName ?? 'N/A'),
                'employmentType' => $emp->employmentType ?? 'N/A'
            ];
        }
    }

    $response = [
        'success' => true,
        'holidayName' => $holiday->holidayName,
        'holidayDate' => date('l, F j, Y', strtotime($holiday->holidayDate)),
        'jurisdictionLevel' => ucfirst($holiday->jurisdictionLevel ?? 'country'),
        'applicableCount' => count($formattedEmployees),
        'employees' => $formattedEmployees,
        'totalEmployees' => count(Employee::employees(['Suspended' => 'N'], false, $DBConn) ?? [])
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

