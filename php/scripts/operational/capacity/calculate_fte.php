<?php
/**
 * Calculate FTE API
 *
 * Calculate FTE for operational work
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
    $annualHours = $_GET['annualHours'] ?? $_POST['annualHours'] ?? null;
    $functionalArea = $_GET['functionalArea'] ?? $_POST['functionalArea'] ?? null;
    $dateFrom = $_GET['dateFrom'] ?? date('Y-01-01');
    $dateTo = $_GET['dateTo'] ?? date('Y-12-31');

    if ($annualHours !== null) {
        // Direct calculation
        $fte = CapacityPlanning::calculateFTE($annualHours);
        echo json_encode([
            'success' => true,
            'fte' => $fte,
            'annualHours' => $annualHours
        ]);
    } elseif ($functionalArea) {
        // Calculate FTE for functional area
        $bauHours = 0; // TODO: Sum BAU hours for functional area
        $fte = CapacityPlanning::calculateFTE($bauHours);
        echo json_encode([
            'success' => true,
            'fte' => $fte,
            'functionalArea' => $functionalArea,
            'annualHours' => $bauHours
        ]);
    } else {
        throw new Exception('Either annualHours or functionalArea must be provided');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

