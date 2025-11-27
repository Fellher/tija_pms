<?php
/**
 * Manage Role Script
 * Handles creation and updating of organizational roles
 *
 * @package Tija Practice Management System
 * @subpackage Admin
 * @version 1.0
 */

session_start();
$base = '../../../../';
set_include_path($base);

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', $base . 'error_log.txt');

include 'php/includes.php';

// Log the start
error_log("=== Manage Role Script Started ===");
error_log("POST Data: " . print_r($_POST, true));

$response = ['success' => false, 'message' => ''];

try {
    // Check admin access
    if (!$isValidAdmin && !$isAdmin) {
        throw new Exception("You are not authorized to perform this action.");
    }

    $DBConn->begin();

    // Get form data
    $roleID = isset($_POST['roleID']) && !empty($_POST['roleID'])
        ? Utility::clean_string($_POST['roleID']) : null;

    // Handle job title - either existing or new
    $jobTitleID = isset($_POST['jobTitleID']) && !empty($_POST['jobTitleID'])
        ? Utility::clean_string($_POST['jobTitleID']) : null;
    $newJobTitle = isset($_POST['newJobTitle']) && !empty($_POST['newJobTitle'])
        ? Utility::clean_string($_POST['newJobTitle']) : null;
    $newJobDescription = isset($_POST['newJobDescription']) && !empty($_POST['newJobDescription'])
        ? Utility::clean_string($_POST['newJobDescription']) : null;

    // Role name from form (used if job title was selected)
    $roleName = isset($_POST['roleName']) && !empty($_POST['roleName'])
        ? Utility::clean_string($_POST['roleName']) : null;

    $roleCode = isset($_POST['roleCode']) && !empty($_POST['roleCode'])
        ? Utility::clean_string($_POST['roleCode']) : null;
    // Get roleTypeID and roleLevelID from form (should be IDs, not codes/numbers)
    $roleTypeID = isset($_POST['roleType']) && !empty($_POST['roleType'])
        ? intval($_POST['roleType']) : null;
    $roleLevelID = isset($_POST['roleLevel']) && !empty($_POST['roleLevel'])
        ? intval($_POST['roleLevel']) : null;

    // If IDs are not provided, try to find by code/number (backward compatibility)
    if (!$roleTypeID && isset($_POST['roleType']) && !empty($_POST['roleType'])) {
        $roleTypeCode = Utility::clean_string($_POST['roleType']);
        $roleTypeObj = Data::role_types(['roleTypeCode' => $roleTypeCode], true, $DBConn);
        if ($roleTypeObj) {
            $roleTypeID = $roleTypeObj->roleTypeID;
        }
    }

    if (!$roleLevelID && isset($_POST['roleLevel']) && !empty($_POST['roleLevel'])) {
        $levelNumber = intval($_POST['roleLevel']);
        $roleLevelObj = Data::role_levels(['levelNumber' => $levelNumber], true, $DBConn);
        if ($roleLevelObj) {
            $roleLevelID = $roleLevelObj->roleLevelID;
        }
    }

    // Set defaults if still null
    if (!$roleTypeID) {
        $defaultRoleType = Data::role_types(['roleTypeCode' => 'OPR'], true, $DBConn);
        $roleTypeID = $defaultRoleType ? $defaultRoleType->roleTypeID : null;
    }

    if (!$roleLevelID) {
        $defaultRoleLevel = Data::role_levels(['levelNumber' => 5], true, $DBConn);
        $roleLevelID = $defaultRoleLevel ? $defaultRoleLevel->roleLevelID : null;
    }
    $roleDescription = isset($_POST['roleDescription']) && !empty($_POST['roleDescription'])
        ? Utility::clean_string($_POST['roleDescription']) : null;

    // Approval settings
    $requiresApproval = isset($_POST['requiresApproval']) && $_POST['requiresApproval'] == 'Y' ? 'Y' : 'N';
    $canApprove = isset($_POST['canApprove']) && $_POST['canApprove'] == 'Y' ? 'Y' : 'N';
    $approvalLimit = isset($_POST['approvalLimit']) && !empty($_POST['approvalLimit'])
        ? Utility::clean_string($_POST['approvalLimit']) : null;

    // Organizational structure
    $departmentID = isset($_POST['departmentID']) && !empty($_POST['departmentID'])
        ? Utility::clean_string($_POST['departmentID']) : null;
    $unitID = isset($_POST['unitID']) && !empty($_POST['unitID'])
        ? Utility::clean_string($_POST['unitID']) : null;
    $parentRoleID = isset($_POST['parentRoleID']) && !empty($_POST['parentRoleID'])
        ? Utility::clean_string($_POST['parentRoleID']) : null;

    // Visual customization
    $iconClass = isset($_POST['iconClass']) && !empty($_POST['iconClass'])
        ? Utility::clean_string($_POST['iconClass']) : null;
    $colorCode = isset($_POST['colorCode']) && !empty($_POST['colorCode'])
        ? Utility::clean_string($_POST['colorCode']) : null;

    // Metrics
    $reportsCount = isset($_POST['reportsCount']) && !empty($_POST['reportsCount'])
        ? Utility::clean_string($_POST['reportsCount']) : 0;

    $isActive = isset($_POST['isActive']) && $_POST['isActive'] == 'Y' ? 'Y' : 'N';
    $roleScope = isset($_POST['roleScope']) ? Utility::clean_string($_POST['roleScope']) : 'entity';

    // Get organization and entity IDs
    $orgDataID = isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])
        ? Utility::clean_string($_POST['orgDataID']) : null;
    $entityID = isset($_POST['entityID']) && !empty($_POST['entityID'])
        ? Utility::clean_string($_POST['entityID']) : null;

    // Handle new job title creation
    if ($newJobTitle) {
        // Create new job title first
        $jobTitleData = [
            'jobTitle' => $newJobTitle,
            'jobDescription' => $newJobDescription,
            'DateAdded' => date('Y-m-d H:i:s'),
            'Suspended' => 'N'
        ];

        $jobTitleInsertResult = $DBConn->insert_data('tija_job_titles', $jobTitleData);

        if ($jobTitleInsertResult) {
            $jobTitleID = $DBConn->lastInsertId();
            $roleName = $newJobTitle;
        } else {
            throw new Exception("Failed to create new job title");
        }
    } elseif ($jobTitleID) {
        // Use existing job title - fetch the name if not provided
        if (!$roleName) {
            $existingJobTitle = Data::job_titles(['jobTitleID' => $jobTitleID], true, $DBConn);
            if ($existingJobTitle) {
                $roleName = $existingJobTitle->jobTitle;
            }
        }
    }

    // Validate required fields
    if (!$roleName) {
        error_log("ERROR: Role name is required");
        throw new Exception("Role name is required");
    }

    error_log("Role Name: " . $roleName);
    error_log("Role Type ID: " . $roleTypeID);
    error_log("Role Level ID: " . $roleLevelID);
    error_log("Entity ID: " . $entityID);

    // Determine scope - if organization-wide, set entityID to null
    if ($roleScope === 'organization') {
        $entityID = null;
        error_log("Role scope set to organization-wide, entityID set to NULL");
    }

    // Prepare role data - ALL fields from tija_roles table
    $data = [
        'roleName' => $roleName,
        'roleCode' => $roleCode,
        'roleTypeID' => $roleTypeID,
        'roleLevelID' => $roleLevelID,
        'roleDescription' => $roleDescription,
        'orgDataID' => $orgDataID,
        'entityID' => $entityID,
        'departmentID' => $departmentID,
        'unitID' => $unitID,
        'parentRoleID' => $parentRoleID,
        'requiresApproval' => $requiresApproval,
        'canApprove' => $canApprove,
        'approvalLimit' => $approvalLimit,
        'reportsCount' => $reportsCount,
        'iconClass' => $iconClass,
        'colorCode' => $colorCode,
        'isActive' => $isActive,
        'Lapsed' => 'N',
        'Suspended' => 'N'
    ];

    // Add jobTitleID if provided (for future reference/linking)
    if ($jobTitleID) {
        $data['jobTitleID'] = $jobTitleID;
    }

    error_log("Prepared data: " . print_r($data, true));

    if ($roleID) {
        // Update existing role
        error_log("Updating existing role ID: " . $roleID);
        $data['roleID'] = $roleID;
        $data['LastUpdate'] = date('Y-m-d H:i:s');
        $data['LastUpdatedByID'] = $userDetails->ID ?? null;

        try {
            $updateResult = $DBConn->update_table('tija_roles', $data, ['roleID' => $roleID]);
            error_log("Update result: " . ($updateResult ? 'SUCCESS' : 'FAILED'));
        } catch (Exception $dbError) {
            error_log("Database update error: " . $dbError->getMessage());
            throw new Exception("Database update error: " . $dbError->getMessage());
        }

        if ($updateResult) {
            $DBConn->commit();
            error_log("Transaction committed successfully");
            $response['success'] = true;
            $response['message'] = 'Role updated successfully';
            $response['roleID'] = $roleID;
        } else {
            error_log("Update failed - no result from update_table");
            throw new Exception("Failed to update role");
        }
    } else {
        // Insert new role
        error_log("Inserting new role");
        $data['DateAdded'] = date('Y-m-d H:i:s');
        $data['LastUpdatedByID'] = $userDetails->ID ?? null;

        error_log("Final insert data: " . print_r($data, true));

        try {
            $insertResult = $DBConn->insert_data('tija_roles', $data);
            error_log("Insert result: " . ($insertResult ? 'SUCCESS' : 'FAILED'));
        } catch (Exception $dbError) {
            error_log("Database insert error: " . $dbError->getMessage());
            throw new Exception("Database insert error: " . $dbError->getMessage());
        }

        if ($insertResult) {
            $newRoleID = $DBConn->lastInsertId();
            error_log("New role ID: " . $newRoleID);
            $DBConn->commit();
            error_log("Transaction committed successfully");
            $response['success'] = true;
            $response['message'] = 'Role created successfully';
            $response['roleID'] = $newRoleID;
        } else {
            error_log("Insert failed - no result from insert_data");
            throw new Exception("Failed to create role");
        }
    }

} catch (Exception $e) {
    error_log("=== EXCEPTION CAUGHT ===");
    error_log("Exception: " . $e->getMessage());
    error_log("Trace: " . $e->getTraceAsString());

    if ($DBConn) {
        try {
            $DBConn->rollback();
            error_log("Transaction rolled back");
        } catch (Exception $rollbackError) {
            error_log("Rollback error: " . $rollbackError->getMessage());
        }
    }
    $response['message'] = $e->getMessage();
    $response['success'] = false;
}

error_log("Final response: " . print_r($response, true));

// Redirect if this is not an AJAX request
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    error_log("Redirecting to referer page");

    // Get the return URL from the referer or use default
    $returnURL = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $base . 'html/pages/core/admin/entity_details.php';

    // Add success/error message to session
    $_SESSION['flash_message'] = $response['message'];
    $_SESSION['flash_type'] = $response['success'] ? 'success' : 'danger';

    error_log("Flash message set: " . $response['message']);
    error_log("Flash type: " . ($_SESSION['flash_type'] ?? 'not set'));
    error_log("Redirecting to: " . $returnURL);

    // Redirect back
    header("Location: " . $returnURL);
    exit;
}

error_log("Returning JSON response");
echo json_encode($response);
exit;
?>

