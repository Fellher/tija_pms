<?php
/**
 * Pay Grades Management API
 * Handles CRUD operations for pay grades and job title linking
 */

session_start();
$base = '../../../../';
set_include_path($base);

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'php/includes.php';

// Check admin access
if (!$isValidAdmin && !$isAdmin) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Ensure DBConn is available
if (!isset($DBConn) || !$DBConn) {
    echo json_encode(['success' => false, 'message' => 'Database connection not available']);
    exit;
}

// Get user ID from session
$userID = isset($userDetails->ID) ? $userDetails->ID : (isset($employeeDetails->ID) ? $employeeDetails->ID : null);

if (!$userID) {
    echo json_encode(['success' => false, 'message' => 'User ID not found']);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

// Get user details for orgDataID and entityID
$userDetails = Data::users(['ID' => $userID], true, $DBConn);

// Fallback to employeeDetails if available
if (!$userDetails && isset($employeeDetails)) {
    $userDetails = $employeeDetails;
}

try {
    switch ($action) {
        case 'get':
            // Get single pay grade
            $payGradeID = isset($_GET['id']) ? intval($_GET['id']) : 0;

            if (!$payGradeID) {
                echo json_encode(['success' => false, 'message' => 'Pay grade ID is required']);
                exit;
            }

            // Debug: Check if Data class exists
            if (!class_exists('Data')) {
                echo json_encode(['success' => false, 'message' => 'Data class not found', 'debug' => 'Class missing']);
                exit;
            }

            // Debug: Check if method exists
            if (!method_exists('Data', 'pay_grades')) {
                echo json_encode(['success' => false, 'message' => 'pay_grades method not found', 'debug' => 'Method missing']);
                exit;
            }

            $payGrade = Data::pay_grades(['payGradeID' => $payGradeID], true, $DBConn);

            if ($payGrade) {
                echo json_encode(['success' => true, 'data' => $payGrade]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Pay grade not found', 'debug' => ['payGradeID' => $payGradeID, 'result' => $payGrade]]);
            }
            break;

        case 'save':
            // Add or update pay grade
            $payGradeID = isset($_POST['payGradeID']) ? intval($_POST['payGradeID']) : 0;
            $payGradeCode = isset($_POST['payGradeCode']) ? trim($_POST['payGradeCode']) : '';
            $payGradeName = isset($_POST['payGradeName']) ? trim($_POST['payGradeName']) : '';
            $payGradeDescription = isset($_POST['payGradeDescription']) ? trim($_POST['payGradeDescription']) : '';
            $minSalary = isset($_POST['minSalary']) ? floatval($_POST['minSalary']) : 0;
            $midSalary = isset($_POST['midSalary']) ? floatval($_POST['midSalary']) : 0;
            $maxSalary = isset($_POST['maxSalary']) ? floatval($_POST['maxSalary']) : 0;
            $gradeLevel = isset($_POST['gradeLevel']) ? intval($_POST['gradeLevel']) : 1;
            $allowsOvertime = (isset($_POST['allowsOvertime']) && $_POST['allowsOvertime'] === 'Y') ? 'Y' : 'N';
            $bonusEligible = (isset($_POST['bonusEligible']) && $_POST['bonusEligible'] === 'Y') ? 'Y' : 'N';
            $commissionEligible = (isset($_POST['commissionEligible']) && $_POST['commissionEligible'] === 'Y') ? 'Y' : 'N';

            // Validation
            $errors = [];

            if (empty($payGradeCode)) $errors[] = 'Pay grade code is required';
            if (empty($payGradeName)) $errors[] = 'Pay grade name is required';
            if ($minSalary <= 0) $errors[] = 'Minimum salary must be greater than 0';
            if ($midSalary < $minSalary) $errors[] = 'Midpoint salary must be >= minimum salary';
            if ($maxSalary <= $midSalary) $errors[] = 'Maximum salary must be > midpoint salary';

            if (count($errors) > 0) {
                echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
                exit;
            }

            // Prepare data
            $data = [
                'orgDataID' => $userDetails->orgDataID,
                'entityID' => $userDetails->entityID,
                'payGradeCode' => $payGradeCode,
                'payGradeName' => $payGradeName,
                'payGradeDescription' => $payGradeDescription,
                'minSalary' => $minSalary,
                'midSalary' => $midSalary,
                'maxSalary' => $maxSalary,
                'gradeLevel' => $gradeLevel,
                'allowsOvertime' => $allowsOvertime,
                'bonusEligible' => $bonusEligible,
                'commissionEligible' => $commissionEligible,
                'updatedBy' => $userID
            ];

            if ($payGradeID) {
                // Update
                $result = $DBConn->update_table('tija_pay_grades', $data, ['payGradeID' => $payGradeID]);
                $message = 'Pay grade updated successfully';
            } else {
                // Insert
                $data['createdBy'] = $userID;
                $result = $DBConn->insert_data('tija_pay_grades', $data);
                $message = 'Pay grade created successfully';
            }

            if ($result !== false) {
                echo json_encode(['success' => true, 'message' => $message]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to save pay grade']);
            }
            break;

        case 'delete':
            // Soft delete pay grade
            $payGradeID = isset($_GET['id']) ? intval($_GET['id']) : 0;

            if (!$payGradeID) {
                echo json_encode(['success' => false, 'message' => 'Pay grade ID is required']);
                exit;
            }

            $result = $DBConn->update_table('tija_pay_grades', [
                'Suspended' => 'Y',
                'updatedBy' => $userID
            ], ['payGradeID' => $payGradeID]);

            if ($result !== false) {
                echo json_encode(['success' => true, 'message' => 'Pay grade deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete pay grade']);
            }
            break;

        case 'get_job_titles':
            // Get all job titles with link status for a pay grade (filtered by entity)
            $gradeID = isset($_GET['gradeID']) ? intval($_GET['gradeID']) : 0;

            // Get pay grade to check entity
            $payGrade = Data::pay_grades(['payGradeID' => $gradeID], true, $DBConn);

            if (!$payGrade) {
                echo json_encode(['success' => false, 'message' => 'Pay grade not found']);
                break;
            }

            $sql = "SELECT
                        jt.jobTitleID,
                        jt.jobTitle,
                        CASE WHEN jtpg.mappingID IS NOT NULL THEN 1 ELSE 0 END as isLinked
                    FROM tija_job_titles jt
                    LEFT JOIN tija_job_title_pay_grade jtpg ON jt.jobTitleID = jtpg.jobTitleID
                        AND jtpg.payGradeID = {$gradeID}
                        AND jtpg.isCurrent = 'Y'
                        AND jtpg.Suspended = 'N'
                    WHERE jt.Suspended = 'N'
                    ORDER BY jt.jobTitle";

            $DBConn->query($sql);
            $DBConn->execute();
            $jobTitles = $DBConn->resultSet();

            echo json_encode(['success' => true, 'jobTitles' => $jobTitles]);
            break;

        case 'link_job':
            // Link job title to pay grade
            $gradeID = isset($_GET['gradeID']) ? intval($_GET['gradeID']) : 0;
            $jobTitleID = isset($_GET['jobTitleID']) ? intval($_GET['jobTitleID']) : 0;

            if (!$gradeID || !$jobTitleID) {
                echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
                exit;
            }

            // Check if already linked
            $checkSql = "SELECT COUNT(*) as count FROM tija_job_title_pay_grade
                         WHERE jobTitleID = {$jobTitleID} AND isCurrent = 'Y' AND Suspended = 'N'";
            $DBConn->query($checkSql);
            $DBConn->execute();
            $existing = $DBConn->single();

            if ($existing && $existing->count > 0) {
                // Update existing link
                $result = $DBConn->update_table('tija_job_title_pay_grade', [
                    'payGradeID' => $gradeID,
                    'updatedBy' => $userID
                ], ['jobTitleID' => $jobTitleID, 'isCurrent' => 'Y']);
            } else {
                // Create new link
                $result = $DBConn->insert_data('tija_job_title_pay_grade', [
                    'jobTitleID' => $jobTitleID,
                    'payGradeID' => $gradeID,
                    'effectiveDate' => date('Y-m-d'),
                    'isCurrent' => 'Y',
                    'createdBy' => $userID,
                    'updatedBy' => $userID
                ]);
            }

            if ($result !== false) {
                echo json_encode(['success' => true, 'message' => 'Job title linked successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to link job title']);
            }
            break;

        case 'unlink_job':
            // Unlink job title from pay grade
            $jobTitleID = isset($_GET['jobTitleID']) ? intval($_GET['jobTitleID']) : 0;

            $result = $DBConn->update_table('tija_job_title_pay_grade', [
                'Suspended' => 'Y',
                'isCurrent' => 'N',
                'updatedBy' => $userID
            ], ['jobTitleID' => $jobTitleID, 'isCurrent' => 'Y']);

            if ($result !== false) {
                echo json_encode(['success' => true, 'message' => 'Job title unlinked successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to unlink job title']);
            }
            break;

        case 'get_linked_jobs':
            // Get jobs linked to a pay grade
            $gradeID = isset($_GET['gradeID']) ? intval($_GET['gradeID']) : 0;

            $sql = "SELECT jt.jobTitle, jtpg.effectiveDate
                    FROM tija_job_title_pay_grade jtpg
                    JOIN tija_job_titles jt ON jtpg.jobTitleID = jt.jobTitleID
                    WHERE jtpg.payGradeID = {$gradeID}
                    AND jtpg.isCurrent = 'Y'
                    AND jtpg.Suspended = 'N'
                    ORDER BY jt.jobTitle";

            $DBConn->query($sql);
            $DBConn->execute();
            $jobs = $DBConn->resultSet();

            echo json_encode(['success' => true, 'jobs' => $jobs ?: []]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
?>

