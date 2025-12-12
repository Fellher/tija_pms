<?php
/**
 * Employee Profile Management API Endpoint
 *
 * Handles all CRUD operations for the comprehensive employee profile system
 *
 * @package    Tija CRM
 * @subpackage Employee Management
 * @version    1.0
 * @created    2025-10-15
 */

// Security headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base = '../../../../';
set_include_path($base);

include 'php/includes.php';

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'data' => null,
    'errors' => []
];

try {
    // Get database connection (use mysqli for compatibility with profile classes)
    $DBConn = new mysqli(
        $config['DBHost'],
        $config['DBUser'],
        $config['DBPassword'],
        $config['DB']
    );

    if ($DBConn->connect_errno) {
        throw new Exception('Database connection failed: ' . $DBConn->connect_error);
    }

    $DBConn->set_charset('utf8mb4');

    // Check if user is logged in
    if (!isset($userDetails->ID) || empty($userDetails->ID)) {
        throw new Exception('Unauthorized access. Please log in.');
    }

    $userID = $userDetails->ID;

    // Get request method
    $requestMethod = $_SERVER['REQUEST_METHOD'];

    // Get action from POST or GET
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    if (empty($action)) {
        throw new Exception('Action is required');
    }

    // ===================================================================
    // ROUTE ACTIONS
    // ===================================================================

    switch ($action) {

        // -----------------------------------------------------------------
        // 1. PERSONAL DETAILS
        // -----------------------------------------------------------------
        case 'get_personal_details':
            $employeeID = $_GET['employeeID'] ?? null;
            if (!$employeeID) {
                throw new Exception('Employee ID is required');
            }

            $data = EmployeeProfile::get_personal_details(['employeeID' => $employeeID], true, $DBConn);
            $response['success'] = true;
            $response['data'] = $data;
            $response['message'] = 'Personal details retrieved successfully';
            break;

        case 'save_personal_details':
            $data = $_POST;
            $data['updatedBy'] = $userID;

            $result = EmployeeProfile::save_personal_details($data, $DBConn);
            $response = array_merge($response, $result);
            break;

        // -----------------------------------------------------------------
        // 2. EMPLOYMENT DETAILS
        // -----------------------------------------------------------------
        case 'get_employment_details':
            $employeeID = $_GET['employeeID'] ?? null;
            if (!$employeeID) {
                throw new Exception('Employee ID is required');
            }

            $data = EmployeeProfile::get_employment_details(['employeeID' => $employeeID], true, $DBConn);
            $response['success'] = true;
            $response['data'] = $data;
            $response['message'] = 'Employment details retrieved successfully';
            break;

        case 'save_employment_details':
            $data = $_POST;
            $data['updatedBy'] = $userID;

            $result = EmployeeProfile::save_employment_details($data, $DBConn);
            $response = array_merge($response, $result);
            break;

        // -----------------------------------------------------------------
        // 3. JOB HISTORY
        // -----------------------------------------------------------------
        case 'get_job_history':
            $employeeID = $_GET['employeeID'] ?? null;
            if (!$employeeID) {
                throw new Exception('Employee ID is required');
            }

            $data = EmployeeProfile::get_job_history(['employeeID' => $employeeID], false, $DBConn);
            $response['success'] = true;
            $response['data'] = $data;
            $response['message'] = 'Job history retrieved successfully';
            break;

        case 'save_job_history':
            $data = $_POST;
            $data['createdBy'] = $userID;
            $data['updatedBy'] = $userID;

            $result = EmployeeProfile::save_job_history($data, $DBConn);
            $response = array_merge($response, $result);
            break;

        case 'delete_job_history':
            $jobHistoryID = $_POST['jobHistoryID'] ?? null;
            if (!$jobHistoryID) {
                throw new Exception('Job History ID is required');
            }

            $result = EmployeeProfile::delete_job_history($jobHistoryID, $DBConn);
            $response = array_merge($response, $result);
            break;

        // -----------------------------------------------------------------
        // 4. COMPENSATION
        // -----------------------------------------------------------------
        case 'get_compensation':
            $employeeID = $_GET['employeeID'] ?? null;
            if (!$employeeID) {
                throw new Exception('Employee ID is required');
            }

            $data = EmployeeProfile::get_compensation(['employeeID' => $employeeID, 'isCurrent' => 'Y'], true, $DBConn);
            $response['success'] = true;
            $response['data'] = $data;
            $response['message'] = 'Compensation details retrieved successfully';
            break;

        case 'save_compensation':
            $data = $_POST;
            $data['createdBy'] = $userID;
            $data['updatedBy'] = $userID;

            $result = EmployeeProfile::save_compensation($data, $DBConn);
            $response = array_merge($response, $result);
            break;

        // -----------------------------------------------------------------
        // 5. CONTACT DETAILS
        // -----------------------------------------------------------------
        case 'get_contact_details':
            $employeeID = $_GET['employeeID'] ?? null;
            if (!$employeeID) {
                throw new Exception('Employee ID is required');
            }

            $data = EmployeeProfileExtended::get_contact_details(['employeeID' => $employeeID], true, $DBConn);
            $response['success'] = true;
            $response['data'] = $data;
            $response['message'] = 'Contact details retrieved successfully';
            break;

        case 'save_contact_details':
            $data = $_POST;
            $data['updatedBy'] = $userID;

            $result = EmployeeProfileExtended::save_contact_details($data, $DBConn);
            $response = array_merge($response, $result);
            break;

        // -----------------------------------------------------------------
        // 6. EMERGENCY CONTACTS
        // -----------------------------------------------------------------
        case 'get_emergency_contacts':
            $employeeID = $_GET['employeeID'] ?? null;
            if (!$employeeID) {
                throw new Exception('Employee ID is required');
            }

            $data = EmployeeProfileExtended::get_emergency_contacts(['employeeID' => $employeeID], false, $DBConn);
            $response['success'] = true;
            $response['data'] = $data;
            $response['message'] = 'Emergency contacts retrieved successfully';
            break;

        case 'save_emergency_contact':
            $data = $_POST;
            $data['createdBy'] = $userID;
            $data['updatedBy'] = $userID;

            $result = EmployeeProfileExtended::save_emergency_contact($data, $DBConn);
            $response = array_merge($response, $result);
            break;

        case 'delete_emergency_contact':
            $emergencyContactID = $_POST['emergencyContactID'] ?? null;
            if (!$emergencyContactID) {
                throw new Exception('Emergency Contact ID is required');
            }

            $result = EmployeeProfileExtended::delete_emergency_contact($emergencyContactID, $DBConn);
            $response = array_merge($response, $result);
            break;

        // -----------------------------------------------------------------
        // 7. NEXT OF KIN
        // -----------------------------------------------------------------
        case 'get_next_of_kin':
            $employeeID = $_GET['employeeID'] ?? null;
            if (!$employeeID) {
                throw new Exception('Employee ID is required');
            }

            $data = EmployeeProfileExtended::get_next_of_kin(['employeeID' => $employeeID], false, $DBConn);
            $response['success'] = true;
            $response['data'] = $data;
            $response['message'] = 'Next of kin retrieved successfully';
            break;

        case 'save_next_of_kin':
            $data = $_POST;
            $data['createdBy'] = $userID;
            $data['updatedBy'] = $userID;

            $result = EmployeeProfileExtended::save_next_of_kin($data, $DBConn);
            $response = array_merge($response, $result);
            break;

        case 'delete_next_of_kin':
            $nextOfKinID = $_POST['nextOfKinID'] ?? null;
            if (!$nextOfKinID) {
                throw new Exception('Next of Kin ID is required');
            }

            $result = EmployeeProfileExtended::delete_next_of_kin($nextOfKinID, $DBConn);
            $response = array_merge($response, $result);
            break;

        // -----------------------------------------------------------------
        // 8. DEPENDANTS
        // -----------------------------------------------------------------
        case 'get_dependants':
            $employeeID = $_GET['employeeID'] ?? null;
            if (!$employeeID) {
                throw new Exception('Employee ID is required');
            }

            $data = EmployeeProfileExtended::get_dependants(['employeeID' => $employeeID], false, $DBConn);
            $response['success'] = true;
            $response['data'] = $data;
            $response['message'] = 'Dependants retrieved successfully';
            break;

        case 'save_dependant':
            $data = $_POST;
            $data['createdBy'] = $userID;
            $data['updatedBy'] = $userID;

            $result = EmployeeProfileExtended::save_dependant($data, $DBConn);
            $response = array_merge($response, $result);
            break;

        case 'delete_dependant':
            $dependantID = $_POST['dependantID'] ?? null;
            if (!$dependantID) {
                throw new Exception('Dependant ID is required');
            }

            $result = EmployeeProfileExtended::delete_dependant($dependantID, $DBConn);
            $response = array_merge($response, $result);
            break;

        // -----------------------------------------------------------------
        // 9. WORK EXPERIENCE
        // -----------------------------------------------------------------
        case 'get_work_experience':
            $employeeID = $_GET['employeeID'] ?? null;
            if (!$employeeID) {
                throw new Exception('Employee ID is required');
            }

            $data = EmployeeProfileExtended::get_work_experience(['employeeID' => $employeeID], false, $DBConn);
            $response['success'] = true;
            $response['data'] = $data;
            $response['message'] = 'Work experience retrieved successfully';
            break;

        case 'save_work_experience':
            $data = $_POST;
            $data['createdBy'] = $userID;
            $data['updatedBy'] = $userID;

            $result = EmployeeProfileExtended::save_work_experience($data, $DBConn);
            $response = array_merge($response, $result);
            break;

        case 'delete_work_experience':
            $workExperienceID = $_POST['workExperienceID'] ?? null;
            if (!$workExperienceID) {
                throw new Exception('Work Experience ID is required');
            }

            $result = EmployeeProfileExtended::delete_work_experience($workExperienceID, $DBConn);
            $response = array_merge($response, $result);
            break;

        // -----------------------------------------------------------------
        // 10. EDUCATION
        // -----------------------------------------------------------------
        case 'get_education':
            $employeeID = $_GET['employeeID'] ?? null;
            if (!$employeeID) {
                throw new Exception('Employee ID is required');
            }

            $data = EmployeeProfileExtended::get_education(['employeeID' => $employeeID], false, $DBConn);
            $response['success'] = true;
            $response['data'] = $data;
            $response['message'] = 'Education records retrieved successfully';
            break;

        case 'save_education':
            $data = $_POST;
            $data['createdBy'] = $userID;
            $data['updatedBy'] = $userID;

            $result = EmployeeProfileExtended::save_education($data, $DBConn);
            $response = array_merge($response, $result);
            break;

        case 'delete_education':
            $educationID = $_POST['educationID'] ?? null;
            if (!$educationID) {
                throw new Exception('Education ID is required');
            }

            $result = EmployeeProfileExtended::delete_education($educationID, $DBConn);
            $response = array_merge($response, $result);
            break;

        // -----------------------------------------------------------------
        // 11. SKILLS
        // -----------------------------------------------------------------
        case 'get_skills':
            $employeeID = $_GET['employeeID'] ?? null;
            if (!$employeeID) {
                throw new Exception('Employee ID is required');
            }

            $data = EmployeeProfileExtended::get_skills(['employeeID' => $employeeID], false, $DBConn);
            $response['success'] = true;
            $response['data'] = $data;
            $response['message'] = 'Skills retrieved successfully';
            break;

        // -----------------------------------------------------------------
        // 12. BANK DETAILS
        // -----------------------------------------------------------------
        case 'get_bank_details':
            $employeeID = $_GET['employeeID'] ?? null;
            if (!$employeeID) {
                throw new Exception('Employee ID is required');
            }

            $data = EmployeeProfileExtended::get_bank_details(['employeeID' => $employeeID], false, $DBConn);
            $response['success'] = true;
            $response['data'] = $data;
            $response['message'] = 'Bank details retrieved successfully';
            break;

        case 'save_bank_details':
            $data = $_POST;
            $data['createdBy'] = $userID;
            $data['updatedBy'] = $userID;

            $result = EmployeeProfileExtended::save_bank_details($data, $DBConn);
            $response = array_merge($response, $result);
            break;

        // -----------------------------------------------------------------
        // 13. BENEFITS
        // -----------------------------------------------------------------
        case 'get_benefits':
            $employeeID = $_GET['employeeID'] ?? null;
            if (!$employeeID) {
                throw new Exception('Employee ID is required');
            }

            $data = EmployeeProfileExtended::get_benefits(['employeeID' => $employeeID], false, $DBConn);
            $response['success'] = true;
            $response['data'] = $data;
            $response['message'] = 'Benefits retrieved successfully';
            break;

        case 'save_benefit':
            $data = $_POST;
            $data['createdBy'] = $userID;
            $data['updatedBy'] = $userID;

            $result = EmployeeProfileExtended::save_benefit($data, $DBConn);
            $response = array_merge($response, $result);
            break;

        // -----------------------------------------------------------------
        // 14. COMPREHENSIVE PROFILE
        // -----------------------------------------------------------------
        case 'get_comprehensive_profile':
            $employeeID = $_GET['employeeID'] ?? null;
            if (!$employeeID) {
                throw new Exception('Employee ID is required');
            }

            $data = EmployeeProfileExtended::get_comprehensive_profile($employeeID, $DBConn);
            $response['success'] = true;
            $response['data'] = $data;
            $response['message'] = 'Comprehensive profile retrieved successfully';
            break;

        // -----------------------------------------------------------------
        // DEFAULT
        // -----------------------------------------------------------------
        default:
            throw new Exception('Invalid action specified');
    }

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    $response['errors'][] = [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ];

    // Log error
    error_log("Employee Profile API Error: " . $e->getMessage());
}

// Output JSON response
echo json_encode($response);
exit;

