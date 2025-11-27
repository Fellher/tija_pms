<?php
/**
 * Project Creation Wizard Backend Handler
 * Processes 4-step wizard form submission
 *
 * @package Tija Practice Management System
 * @subpackage Projects
 * @version 2.0
 */

session_start();
$base = '../../../';
set_include_path($base);

// Include dependencies
require_once 'php/includes.php';
require_once 'php/middleware/SecurityMiddleware.php';

// Enable error logging
error_reporting(E_ALL);
ini_set('log_errors', 1);
error_log("=== Project Creation Wizard Started ===");
error_log("POST Data: " . print_r($_POST, true));

$response = ['success' => false, 'message' => '', 'projectID' => null];

try {
    // Validate session
    if (!SecurityMiddleware::validateSession()) {
        throw new Exception("Session validation failed");
    }

    // Validate CSRF token
    SecurityMiddleware::validatePOSTRequest();

    // Check rate limiting (max 5 projects per minute)
    if (!SecurityMiddleware::checkRateLimit('create_project', 5, 60)) {
        throw new Exception("Rate limit exceeded. Please wait before creating another project.");
    }

    $DBConn->begin();

    // ========================================================================
    // STEP 1: Basic Information
    // ========================================================================

    $projectName = isset($_POST['projectName']) && !empty($_POST['projectName'])
        ? Utility::clean_string($_POST['projectName']) : null;
    $projectCode = isset($_POST['projectCode']) && !empty($_POST['projectCode'])
        ? Utility::clean_string($_POST['projectCode']) : null;
    $clientID = isset($_POST['clientID']) && !empty($_POST['clientID'])
        ? Utility::clean_string($_POST['clientID']) : null;
    $projectType = isset($_POST['projectType']) && !empty($_POST['projectType'])
        ? Utility::clean_string($_POST['projectType']) : 'fixed_price';
    $projectStart = isset($_POST['projectStart']) && !empty($_POST['projectStart'])
        ? Utility::clean_string($_POST['projectStart']) : date('Y-m-d');
    $projectDeadline = isset($_POST['projectDeadline']) && !empty($_POST['projectDeadline'])
        ? Utility::clean_string($_POST['projectDeadline']) : null;
    $projectClose = isset($_POST['projectClose']) && !empty($_POST['projectClose'])
        ? Utility::clean_string($_POST['projectClose']) : null;
    $projectDescription = isset($_POST['projectDescription']) && !empty($_POST['projectDescription'])
        ? Utility::clean_string($_POST['projectDescription']) : null;
    $priority = isset($_POST['priority']) ? Utility::clean_string($_POST['priority']) : 'medium';
    $projectStatus = isset($_POST['projectStatus']) ? Utility::clean_string($_POST['projectStatus']) : 'planning';

    // Organization and entity
    $orgDataID = isset($_POST['orgDataID']) ? Utility::clean_string($_POST['orgDataID']) : null;
    $entityID = isset($_POST['entityID']) ? Utility::clean_string($_POST['entityID']) : null;
    $createdBy = isset($_POST['createdBy']) ? Utility::clean_string($_POST['createdBy']) : $userDetails->ID;

    // Validate required fields
    if (!$projectName) {
        throw new Exception("Project name is required");
    }
    if (!$clientID) {
        throw new Exception("Client is required");
    }
    if (!$projectDeadline) {
        throw new Exception("Project deadline is required");
    }

    // Generate project code if not provided
    if (!$projectCode) {
        $projectCode = 'PRJ-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    // ========================================================================
    // STEP 2: Team Selection
    // ========================================================================

    $projectOwnerID = isset($_POST['projectOwnerID']) && !empty($_POST['projectOwnerID'])
        ? Utility::clean_string($_POST['projectOwnerID']) : $createdBy;
    $teamMembers = isset($_POST['teamMembers']) && is_array($_POST['teamMembers'])
        ? array_map(function($id) { return Utility::clean_string($id); }, $_POST['teamMembers']) : [];

    // ========================================================================
    // STEP 3: Budget & Billing
    // ========================================================================

    $totalBudget = isset($_POST['totalBudget']) && !empty($_POST['totalBudget'])
        ? Utility::clean_string($_POST['totalBudget']) : 0;
    $billingModel = isset($_POST['billingModel']) ? Utility::clean_string($_POST['billingModel']) : 'fixed';
    $hourlyRate = isset($_POST['hourlyRate']) ? Utility::clean_string($_POST['hourlyRate']) : null;
    $paymentTerms = isset($_POST['paymentTerms']) ? Utility::clean_string($_POST['paymentTerms']) : 'net_30';
    $milestones = isset($_POST['milestones']) && is_array($_POST['milestones']) ? $_POST['milestones'] : [];

    // Validate budget
    if ($totalBudget <= 0) {
        throw new Exception("Project budget must be greater than 0");
    }

    // ========================================================================
    // CREATE PROJECT
    // ========================================================================

    $projectData = [
        'projectName' => $projectName,
        'projectCode' => $projectCode,
        'clientID' => $clientID,
        'projectTypeID' => $projectType,
        'projectStart' => $projectStart,
        'projectDeadline' => $projectDeadline,
        'projectClose' => $projectClose,
        'projectStatus' => $projectStatus,
        'projectOwnerID' => $projectOwnerID,
        'projectValue' => $totalBudget,
        'billable' => 'Y',
        'billingRateID' => null, // Can be set later
        'billableRateValue' => $hourlyRate,
        'businessUnitID' => null, // Can be set later
        'approval' => 'N',
        'orgDataID' => $orgDataID,
        'entityID' => $entityID,
        'DateAdded' => date('Y-m-d H:i:s'),
        'LastUpdate' => date('Y-m-d H:i:s'),
        'Lapsed' => 'N',
        'Suspended' => 'N'
    ];

    error_log("Creating project: " . print_r($projectData, true));

    // Insert project
    $insertResult = $DBConn->insert_data('tija_projects', $projectData);

    if (!$insertResult) {
        throw new Exception("Failed to create project");
    }

    $projectID = $DBConn->lastInsertId();
    error_log("Project created with ID: " . $projectID);

    // ========================================================================
    // ADD TEAM MEMBERS
    // ========================================================================

    // Add project owner as team member
    if ($projectOwnerID) {
        $ownerData = [
            'projectID' => $projectID,
            'userID' => $projectOwnerID,
            'role' => 'manager',
            'assignmentDate' => date('Y-m-d'),
            'isActive' => 'Y',
            'DateAdded' => date('Y-m-d H:i:s'),
            'Suspended' => 'N'
        ];
        try {
            $DBConn->insert_data('tija_project_team', $ownerData);
        } catch (Exception $e) {
            error_log("Failed to add project owner to team: " . $e->getMessage());
        }
    }

    // Add team members
    foreach ($teamMembers as $memberID) {
        if ($memberID != $projectOwnerID) {
            $memberData = [
                'projectID' => $projectID,
                'userID' => $memberID,
                'role' => 'member',
                'assignmentDate' => date('Y-m-d'),
                'isActive' => 'Y',
                'DateAdded' => date('Y-m-d H:i:s'),
                'Suspended' => 'N'
            ];
            try {
                $DBConn->insert_data('tija_project_team', $memberData);
            } catch (Exception $e) {
                error_log("Failed to add team member {$memberID}: " . $e->getMessage());
            }
        }
    }

    // ========================================================================
    // ADD MILESTONES
    // ========================================================================

    if (!empty($milestones)) {
        $orderIndex = 0;
        foreach ($milestones as $milestone) {
            if (!empty($milestone['name'])) {
                $milestoneData = [
                    'projectID' => $projectID,
                    'milestoneName' => Utility::clean_string($milestone['name']),
                    'paymentPercentage' => isset($milestone['percentage']) ? floatval($milestone['percentage']) : 0,
                    'dueDate' => !empty($milestone['dueDate']) ? Utility::clean_string($milestone['dueDate']) : null,
                    'status' => 'pending',
                    'orderIndex' => $orderIndex++,
                    'createdBy' => $createdBy,
                    'DateAdded' => date('Y-m-d H:i:s'),
                    'Suspended' => 'N'
                ];

                $DBConn->insert_data('tija_project_milestones', $milestoneData);
            }
        }
    }

    // ========================================================================
    // LOG AUDIT TRAIL
    // ========================================================================

    $auditData = [
        'projectID' => $projectID,
        'userID' => $createdBy,
        'action' => 'create_project',
        'tableName' => 'tija_projects',
        'recordID' => $projectID,
        'newValue' => json_encode($projectData),
        'ipAddress' => $_SERVER['REMOTE_ADDR'] ?? null,
        'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    try {
        $DBConn->insert_data('tija_project_audit_log', $auditData);
    } catch (Exception $e) {
        error_log("Failed to log audit trail: " . $e->getMessage());
    }

    // ========================================================================
    // COMMIT TRANSACTION
    // ========================================================================

    $DBConn->commit();
    error_log("Project creation completed successfully");

    $response['success'] = true;
    $response['message'] = 'Project created successfully!';
    $response['projectID'] = $projectID;

    // Log security event
    SecurityMiddleware::logSecurityEvent('project_created',
        "Project '{$projectName}' (ID: {$projectID}) created", 'info');

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

// Handle response
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    // Form submission (not AJAX)
    if ($response['success']) {
        $_SESSION['flash_message'] = $response['message'];
        $_SESSION['flash_type'] = 'success';

        // Redirect to project page
        $returnURL = $base . 'html/?s=user&ss=projects&p=project&projectID=' . $response['projectID'];
    } else {
        $_SESSION['flash_message'] = $response['message'];
        $_SESSION['flash_type'] = 'danger';

        // Redirect back to wizard
        $returnURL = $base . 'html/?s=user&ss=projects&p=create_project_wizard';
    }

    header("Location: " . $returnURL);
    exit;
}

// AJAX response
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>

