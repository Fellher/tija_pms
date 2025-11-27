<?php
/**
 * Manage Pay Grade Script
 * Handles creation and updating of pay grades
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
ini_set('log_errors', 1);

include 'php/includes.php';

// Log the start
error_log("=== Manage Pay Grade Script Started ===");
error_log("POST Data: " . print_r($_POST, true));

$response = ['success' => false, 'message' => ''];

try {
    // Check admin access
    if (!$isValidAdmin && !$isAdmin) {
        throw new Exception("You are not authorized to perform this action.");
    }

    $DBConn->begin();

    // Get form data
    $payGradeID = isset($_POST['payGradeID']) && !empty($_POST['payGradeID'])
        ? Utility::clean_string($_POST['payGradeID']) : null;
    $payGradeCode = isset($_POST['payGradeCode']) && !empty($_POST['payGradeCode'])
        ? Utility::clean_string($_POST['payGradeCode']) : null;
    $payGradeName = isset($_POST['payGradeName']) && !empty($_POST['payGradeName'])
        ? Utility::clean_string($_POST['payGradeName']) : null;
    $payGradeDescription = isset($_POST['payGradeDescription']) && !empty($_POST['payGradeDescription'])
        ? Utility::clean_string($_POST['payGradeDescription']) : null;

    // Salary ranges
    $minSalary = isset($_POST['minSalary']) && !empty($_POST['minSalary'])
        ? Utility::clean_string($_POST['minSalary']) : 0;
    $midSalary = isset($_POST['midSalary']) && !empty($_POST['midSalary'])
        ? Utility::clean_string($_POST['midSalary']) : 0;
    $maxSalary = isset($_POST['maxSalary']) && !empty($_POST['maxSalary'])
        ? Utility::clean_string($_POST['maxSalary']) : 0;
    $currency = isset($_POST['currency']) && !empty($_POST['currency'])
        ? Utility::clean_string($_POST['currency']) : 'KES';

    // Other fields
    $gradeLevel = isset($_POST['gradeLevel']) && !empty($_POST['gradeLevel'])
        ? Utility::clean_string($_POST['gradeLevel']) : 5;
    $allowsOvertime = isset($_POST['allowsOvertime']) && $_POST['allowsOvertime'] == 'Y' ? 'Y' : 'N';
    $bonusEligible = isset($_POST['bonusEligible']) && $_POST['bonusEligible'] == 'Y' ? 'Y' : 'N';
    $commissionEligible = isset($_POST['commissionEligible']) && $_POST['commissionEligible'] == 'Y' ? 'Y' : 'N';
    $notes = isset($_POST['notes']) && !empty($_POST['notes'])
        ? Utility::clean_string($_POST['notes']) : null;

    $payGradeScope = isset($_POST['payGradeScope']) ? Utility::clean_string($_POST['payGradeScope']) : 'entity';

    // Get organization and entity IDs
    $orgDataID = isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])
        ? Utility::clean_string($_POST['orgDataID']) : null;
    $entityID = isset($_POST['entityID']) && !empty($_POST['entityID'])
        ? Utility::clean_string($_POST['entityID']) : null;

    // Validate required fields
    if (!$payGradeCode) {
        throw new Exception("Pay grade code is required");
    }
    if (!$payGradeName) {
        throw new Exception("Pay grade name is required");
    }
    if ($minSalary <= 0) {
        throw new Exception("Minimum salary must be greater than 0");
    }
    if ($maxSalary <= $midSalary) {
        throw new Exception("Maximum salary must be greater than mid-point salary");
    }
    if ($midSalary < $minSalary) {
        throw new Exception("Mid-point salary must be greater than or equal to minimum salary");
    }

    // Determine scope - if organization-wide, set entityID to null
    if ($payGradeScope === 'organization') {
        $entityID = null;
    }

    // Prepare pay grade data
    $data = [
        'payGradeCode' => $payGradeCode,
        'payGradeName' => $payGradeName,
        'payGradeDescription' => $payGradeDescription,
        'minSalary' => $minSalary,
        'midSalary' => $midSalary,
        'maxSalary' => $maxSalary,
        'currency' => $currency,
        'gradeLevel' => $gradeLevel,
        'allowsOvertime' => $allowsOvertime,
        'bonusEligible' => $bonusEligible,
        'commissionEligible' => $commissionEligible,
        'notes' => $notes,
        'orgDataID' => $orgDataID,
        'entityID' => $entityID,
        'Lapsed' => 'N',
        'Suspended' => 'N'
    ];

    error_log("Prepared pay grade data: " . print_r($data, true));

    if ($payGradeID) {
        // Update existing pay grade
        $data['updatedBy'] = $userDetails->ID ?? null;
        $data['LastUpdate'] = date('Y-m-d H:i:s');

        error_log("Updating pay grade ID: " . $payGradeID);

        $updateResult = $DBConn->update_table('tija_pay_grades', $data, ['payGradeID' => $payGradeID]);

        if ($updateResult) {
            $DBConn->commit();
            error_log("Pay grade updated successfully");
            $response['success'] = true;
            $response['message'] = 'Pay grade updated successfully';
            $response['payGradeID'] = $payGradeID;
        } else {
            throw new Exception("Failed to update pay grade");
        }
    } else {
        // Insert new pay grade
        $data['DateAdded'] = date('Y-m-d H:i:s');
        $data['createdBy'] = $userDetails->ID ?? null;
        $data['updatedBy'] = $userDetails->ID ?? null;

        error_log("Inserting new pay grade");

        $insertResult = $DBConn->insert_data('tija_pay_grades', $data);

        if ($insertResult) {
            $newPayGradeID = $DBConn->lastInsertId();
            error_log("Pay grade created with ID: " . $newPayGradeID);
            $DBConn->commit();
            $response['success'] = true;
            $response['message'] = 'Pay grade created successfully';
            $response['payGradeID'] = $newPayGradeID;
        } else {
            throw new Exception("Failed to create pay grade");
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
    $returnURL = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $base . 'html/?s=core&ss=admin&p=entity_details&entityID=' . $_POST['entityID'] . '&tab=pay_grades';

    // Add success/error message to session
    $_SESSION['flash_message'] = $response['message'];
    $_SESSION['flash_type'] = $response['success'] ? 'success' : 'danger';

    error_log("Flash message set: " . $response['message']);
    error_log("Redirecting to: " . $returnURL);

    // Redirect back
    header("Location: " . $returnURL);
    exit;
}

error_log("Returning JSON response");
echo json_encode($response);
exit;
?>

