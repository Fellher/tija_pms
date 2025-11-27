<?php
/**
 * Get Entity Users
 * Fetches all users belonging to a specific entity
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

    // Get parameters
    $entityID = isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : null;
    $orgDataID = isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : null;

    if (!$entityID && !$orgDataID) {
        throw new Exception("Entity ID or Organization ID is required");
    }

    // Build query to get users from entity
    // This gets users assigned to the entity who are not already admins
    $query = "SELECT
                p.ID,
                p.FirstName,
                p.Surname,
                p.OtherNames,
                p.Email,
                p.profile_image,
                ud.orgDataID,
                ud.entityID,
                ud.prefixID,
                ud.Suspended,

                a.adminID,
                CONCAT(p.FirstName, ' ', p.Surname) AS userName,
                CONCAT(p.FirstName, ' ', p.Surname, ' (', p.userInitials, ')') AS userNameWithInitials,
                CONCAT(SUBSTRING(p.FirstName, 1, 1), SUBSTRING(p.Surname, 1, 1)) AS userInitials
              FROM people p
              LEFT JOIN user_details ud ON p.ID = ud.ID
              LEFT JOIN tija_administrators a ON p.ID = a.userID AND a.Suspended = 'N'
              WHERE ud.Suspended = 'N'";

    $params = array();

    if ($entityID) {
        $query .= " AND ud.entityID = ?";
        $params[] = array($entityID, 'i');
    }

    if ($orgDataID) {
        $query .= " AND ud.orgDataID = ?";
        $params[] = array($orgDataID, 'i');
    }

    $query .= " ORDER BY p.FirstName, p.Surname";

    $users = $DBConn->fetch_all_rows($query, $params);

    if ($users) {
        echo json_encode([
            'success' => true,
            'users' => $users,
            'count' => count($users)
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'users' => [],
            'count' => 0,
            'message' => 'No users found in this entity'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

