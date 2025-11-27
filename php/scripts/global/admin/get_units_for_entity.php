<?php
/**
 * Get Units for Entity
 * Fetches all units belonging to an entity
 *
 * @package Tija Practice Management System
 * @subpackage Admin
 * @version 1.0
 */

// Set JSON header
header('Content-Type: application/json');

session_start();
$base = '../../../../';
set_include_path($base);

include_once 'php/includes.php';
include_once 'php/class_autoload.php';
include_once 'php/config/config.inc.php';
include_once 'php/scripts/db_connect.php';

try {
    // Verify admin access
    if (!$isValidAdmin && !$isAdmin) {
        throw new Exception("Unauthorized access");
    }

    // Get entity ID
    $entityID = isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : null;

    if (!$entityID) {
        throw new Exception("Entity ID is required");
    }

    // Fetch units for this entity
    $units = Data::units_full(['entityID' => $entityID, 'Suspended' => 'N'], false, $DBConn);

    if ($units) {
        // Sort units by name
        usort($units, function($a, $b) {
            return strcmp($a->unitName, $b->unitName);
        });

        echo json_encode([
            'success' => true,
            'units' => $units,
            'count' => count($units)
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'units' => [],
            'count' => 0,
            'message' => 'No units found for this entity'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

