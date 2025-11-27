<?php
/**
 * Get Entities for Organization
 * Fetches all entities belonging to an organization
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

    // Get organization ID
    $orgDataID = isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : null;

    if (!$orgDataID) {
        throw new Exception("Organization ID is required");
    }

    // Fetch entities
    $entities = Data::entities_full(['orgDataID' => $orgDataID, 'Suspended' => 'N'], false, $DBConn);

    if ($entities) {
        // Sort entities - parents first
        usort($entities, function($a, $b) {
            return $a->entityParentID <=> $b->entityParentID;
        });

        echo json_encode([
            'success' => true,
            'entities' => $entities,
            'count' => count($entities)
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'entities' => [],
            'count' => 0,
            'message' => 'No entities found'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

