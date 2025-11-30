<?php
/**
 * Get Capacity API
 *
 * Get employee/team capacity data
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

require_once __DIR__ . '/../../../includes.php';

header('Content-Type: application/json');

global $DBConn, $isValidUser, $userID;

// Check authentication
if (!$isValidUser) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $employeeID = $_GET['employeeID'] ?? $userID;
    $dateFrom = $_GET['dateFrom'] ?? date('Y-01-01');
    $dateTo = $_GET['dateTo'] ?? date('Y-12-31');
    $teamID = $_GET['teamID'] ?? null;

    if ($teamID) {
        // Get team capacity
        // TODO: Implement team capacity calculation
        $capacity = [
            'totalCapacity' => 2080,
            'bauHours' => 0,
            'projectHours' => 0,
            'availableCapacity' => 2080,
            'utilization' => 0
        ];
    } else {
        // Get individual capacity
        $bauHours = CapacityPlanning::calculateOperationalTax($employeeID, $dateFrom, $dateTo, $DBConn);
        $availableCapacityData = CapacityPlanning::getAvailableCapacity($employeeID, $dateFrom, $dateTo, $DBConn);
        $capacityWaterline = CapacityPlanning::getCapacityWaterline($employeeID, $dateFrom, $dateTo, $DBConn);

        $capacity = [
            'totalCapacity' => $availableCapacityData['totalHours'] ?? 2080,
            'bauHours' => $bauHours ?? 0,
            'projectHours' => $capacityWaterline['layer3_projects'] ?? 0,
            'nonWorkingTime' => $capacityWaterline['layer1_nonWorking'] ?? 0,
            'availableCapacity' => $availableCapacityData['availableHours'] ?? 0,
            'utilization' => ($availableCapacityData['utilization'] ?? 0),
            'waterline' => $capacityWaterline
        ];
    }

    echo json_encode([
        'success' => true,
        'capacity' => $capacity,
        'employeeID' => $employeeID,
        'dateRange' => ['from' => $dateFrom, 'to' => $dateTo]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

