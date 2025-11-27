<?php
/**
 * Toggle HR Manager status for an employee.
 *
 * Updates the global isHRManager flag while ensuring the employee remains scoped
 * to their assigned entity/entities.
 */

session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';

function sanitize_identifier($name) {
    return preg_replace('/[^a-zA-Z0-9_]/', '', (string)$name);
}

function table_exists_local($tableName, $DBConn) {
    $tableName = sanitize_identifier($tableName);
    if (!$tableName) {
        return false;
    }
    $rows = $DBConn->fetch_all_rows("SHOW TABLES LIKE '{$tableName}'", array());
    return $rows && count($rows) > 0;
}

function user_has_hr_assignment($userID, $DBConn) {
    $rows = $DBConn->fetch_all_rows(
        "SELECT assignmentID FROM tija_entity_hr_assignments WHERE userID = ? AND Lapsed = 'N' AND Suspended = 'N' LIMIT 1",
        array(array($userID, 'i'))
    );
    return $rows && count($rows) > 0;
}

function set_user_hr_flag($userID, $flag, $DBConn, $updatedBy = null) {
    $data = array(
        'isHRManager' => $flag,
        'LastUpdate' => date('Y-m-d H:i:s')
    );
    if ($updatedBy) {
        $data['LastUpdatedByID'] = $updatedBy;
    }
    return $DBConn->update_table('user_details', $data, array('ID' => $userID));
}

$response = [
    'success' => false,
    'message' => ''
];

$transactionStarted = false;

try {
    if (!$isValidAdmin && !$isAdmin && !$isSuperAdmin && !$isTenantAdmin) {
        throw new Exception("You are not authorized to manage HR permissions.");
    }

    $userID = isset($_POST['userID']) ? Utility::clean_string($_POST['userID']) : null;
    $entityID = isset($_POST['entityID']) ? Utility::clean_string($_POST['entityID']) : null;
    $hrRoleType = isset($_POST['hrRoleType']) ? strtolower(Utility::clean_string($_POST['hrRoleType'])) : 'none';
    $validRoles = array('primary', 'substitute', 'none');
    if (!in_array($hrRoleType, $validRoles, true)) {
        $hrRoleType = 'none';
    }

    $returnURL = isset($_POST['returnURL']) ? trim($_POST['returnURL']) : '';
    if ($returnURL === '') {
        $returnURL = '?s=core&ss=admin&p=entity_details&entityID=' . ($entityID ?? '') . '&tab=employees';
    }

    if (!$userID) {
        throw new Exception("Employee identifier is required.");
    }
    if (!$entityID) {
        throw new Exception("Entity identifier is required.");
    }

    $employee = Employee::employees(['ID' => $userID], true, $DBConn);
    if (!$employee) {
        throw new Exception("Employee not found.");
    }

    if ((string)$employee->entityID !== (string)$entityID) {
        throw new Exception("Employee must belong to the entity to manage HR access.");
    }

    $assignmentsEnabled = table_exists_local('tija_entity_hr_assignments', $DBConn);
    if (($hrRoleType === 'primary' || $hrRoleType === 'substitute') && !$assignmentsEnabled) {
        throw new Exception("HR assignment table missing. Run create_entity_hr_assignments_table migration first.");
    }

    $DBConn->begin();
    $transactionStarted = true;

    $removedUsers = array();

    if ($assignmentsEnabled) {
        // Remove existing assignment for this user within the entity
        $DBConn->query("DELETE FROM tija_entity_hr_assignments WHERE entityID = ? AND userID = ?");
        $DBConn->bind(1, $entityID, PDO::PARAM_INT);
        $DBConn->bind(2, $userID, PDO::PARAM_INT);
        $DBConn->execute();
    }

    if ($assignmentsEnabled && $hrRoleType !== 'none') {
        // Capture previous role holder (if any) before replacement
        $previousRoleHolders = $DBConn->fetch_all_rows(
            "SELECT userID FROM tija_entity_hr_assignments WHERE entityID = ? AND roleType = ? AND Lapsed = 'N' AND Suspended = 'N'",
            array(
                array($entityID, 'i'),
                array($hrRoleType, 's')
            )
        );

        if ($previousRoleHolders) {
            foreach ($previousRoleHolders as $holder) {
                $removedUsers[] = (int)(is_object($holder) ? $holder->userID : $holder['userID']);
            }
        }

        // Remove any existing assignment for this role
        $DBConn->query("DELETE FROM tija_entity_hr_assignments WHERE entityID = ? AND roleType = ?");
        $DBConn->bind(1, $entityID, PDO::PARAM_INT);
        $DBConn->bind(2, $hrRoleType, PDO::PARAM_STR);
        $DBConn->execute();

        $assignmentData = array(
            'entityID' => $entityID,
            'userID' => $userID,
            'roleType' => $hrRoleType,
            'DateAdded' => date('Y-m-d H:i:s'),
            'LastUpdateByID' => isset($userDetails->ID) ? $userDetails->ID : null,
            'Lapsed' => 'N',
            'Suspended' => 'N'
        );
        $insertResult = $DBConn->insert_data('tija_entity_hr_assignments', $assignmentData);
        if (!$insertResult) {
            throw new Exception("Failed to save HR assignment.");
        }
    }

    // Update HR manager flag for current user
    if ($hrRoleType === 'none') {
        if ($assignmentsEnabled && user_has_hr_assignment($userID, $DBConn)) {
            set_user_hr_flag($userID, 'Y', $DBConn, $userDetails->ID ?? null);
        } else {
            set_user_hr_flag($userID, 'N', $DBConn, $userDetails->ID ?? null);
        }
    } else {
        set_user_hr_flag($userID, 'Y', $DBConn, $userDetails->ID ?? null);
    }

    // Update removed users (if they no longer hold assignments anywhere)
    if ($assignmentsEnabled && !empty($removedUsers)) {
        foreach ($removedUsers as $removedUserID) {
            if ($removedUserID === (int)$userID) {
                continue;
            }
            if (!user_has_hr_assignment($removedUserID, $DBConn)) {
                set_user_hr_flag($removedUserID, 'N', $DBConn, $userDetails->ID ?? null);
            }
        }
    }

    $DBConn->commit();
    $transactionStarted = false;

    $response['success'] = true;
    if ($hrRoleType === 'primary') {
        $response['message'] = 'Primary HR manager assigned successfully.';
    } elseif ($hrRoleType === 'substitute') {
        $response['message'] = 'Substitute HR manager assigned successfully.';
    } else {
        $response['message'] = 'Employee HR manager access revoked successfully.';
    }

} catch (Exception $exception) {
    if ($transactionStarted) {
        $DBConn->rollback();
    }
    $response['message'] = $exception->getMessage();
}

$_SESSION['flash_message'] = $response['message'];
$_SESSION['flash_type'] = $response['success'] ? 'success' : 'danger';

// Normalise redirect path
$redirectPath = $returnURL;
if (!preg_match('/^https?:/i', $returnURL)) {
    $trimmed = ltrim($returnURL, '/');
    if (stripos($trimmed, 'html/') === 0) {
        $redirectPath = $base . $trimmed;
    } else {
        $redirectPath = $base . 'html/' . $trimmed;
    }
}

header("Location: {$redirectPath}");
exit;

