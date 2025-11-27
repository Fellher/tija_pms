<?php
/**
 * Family API - Next of Kin & Dependants CRUD Operations
 */

session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

try {
    if (!$isValidUser) {
        throw new Exception('You must be logged in to perform this action');
    }

    $action = isset($_GET['action']) ? Utility::clean_string($_GET['action']) : (isset($_POST['action']) ? Utility::clean_string($_POST['action']) : '');

    if (!$action) {
        throw new Exception('No action specified');
    }

    $DBConn->begin();

    switch ($action) {
        // ========================================
        // NEXT OF KIN OPERATIONS
        // ========================================

        case 'save_next_of_kin':
            $kinID = isset($_POST['nextOfKinID']) && !empty($_POST['nextOfKinID']) ?
                Utility::clean_string($_POST['nextOfKinID']) : null;
            $employeeID = Utility::clean_string($_POST['employeeID'] ?? '');

            if (empty($employeeID)) {
                throw new Exception('Employee ID is required');
            }

            //log to the php log file the value of the date being submited
            error_log('Date of birth: ' . $_POST['dateOfBirth']);
            // Process date of birth - validate format
            $dateOfBirth = null;
            if (isset($_POST['dateOfBirth']) && !empty($_POST['dateOfBirth'])) {
                $dob = trim($_POST['dateOfBirth']);
                // Validate date format (YYYY-MM-DD)
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
                    $dateOfBirth = $dob;
                }
            }

            $data = [
                'employeeID' => $employeeID,
                'fullName' => Utility::clean_string($_POST['fullName'] ?? ''),
                'relationship' => Utility::clean_string($_POST['relationship'] ?? ''),
                'dateOfBirth' => $dateOfBirth,
                'gender' => Utility::clean_string($_POST['gender'] ?? ''),
                'nationalID' => Utility::clean_string($_POST['nationalID'] ?? ''),
                'phoneNumber' => Utility::clean_string($_POST['phoneNumber'] ?? ''),
                'alternativePhone' => Utility::clean_string($_POST['alternativePhone'] ?? ''),
                'emailAddress' => Utility::clean_string($_POST['emailAddress'] ?? ''),
                'address' => Utility::clean_string($_POST['address'] ?? ''),
                'city' => Utility::clean_string($_POST['city'] ?? ''),
                'county' => Utility::clean_string($_POST['county'] ?? ''),
                'country' => Utility::clean_string($_POST['country'] ?? 'Kenya'),
                'allocationPercentage' => floatval($_POST['allocationPercentage'] ?? 0),
                'isPrimary' => isset($_POST['isPrimary']) && $_POST['isPrimary'] == 'Y' ? 'Y' : 'N',
                'occupation' => Utility::clean_string($_POST['occupation'] ?? ''),
                'employer' => Utility::clean_string($_POST['employer'] ?? ''),
                'notes' => Utility::clean_string($_POST['notes'] ?? ''),
                'updatedBy' => $userDetails->ID,
                'Suspended' => 'N',
                'Lapsed' => 'N'
            ];

            // Validate required fields
            if (empty($data['fullName']) || empty($data['relationship']) || empty($data['phoneNumber'])) {
                throw new Exception('Full name, relationship, and phone number are required');
            }

            // If setting as primary, unset other primary kin
            if ($data['isPrimary'] == 'Y') {
                $DBConn->update_table('tija_employee_next_of_kin',
                    ['isPrimary' => 'N'],
                    ['employeeID' => $employeeID]);
            }

            if ($kinID) {
                // Update existing
                $updateResult = $DBConn->update_table('tija_employee_next_of_kin', $data, ['nextOfKinID' => $kinID]);
                if ($updateResult === false) {
                    // Get last error for debugging
                    $error = $DBConn->error ?? 'Unknown database error';
                    throw new Exception('Failed to update next of kin: ' . $error);
                }
                $response['message'] = 'Next of kin updated successfully';
            } else {
                // Create new
                $data['createdBy'] = $userDetails->ID;

                $insertResult = $DBConn->insert_data('tija_employee_next_of_kin', $data);
                if ($insertResult === false) {
                    // Get last error for debugging
                    $error = $DBConn->error ?? 'Unknown database error';
                    throw new Exception('Failed to create next of kin: ' . $error);
                }
                $response['message'] = 'Next of kin created successfully';
            }

            $response['success'] = true;
            break;

        case 'get_next_of_kin':
            $kinID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$kinID) {
                throw new Exception('Next of kin ID is required');
            }

            $kin = EmployeeProfileExtended::get_next_of_kin(['nextOfKinID' => $kinID], true, $DBConn);
            if (!$kin) {
                throw new Exception('Next of kin not found');
            }

            $response['success'] = true;
            $response['data'] = $kin;
            break;

        case 'delete_next_of_kin':
            $kinID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$kinID) {
                throw new Exception('Next of kin ID is required');
            }

            // Soft delete
            if (!$DBConn->update_table('tija_employee_next_of_kin', ['Suspended' => 'Y'], ['nextOfKinID' => $kinID])) {
                throw new Exception('Failed to delete next of kin');
            }

            $response['success'] = true;
            $response['message'] = 'Next of kin deleted successfully';
            break;

        // ========================================
        // DEPENDANT OPERATIONS
        // ========================================

        case 'save_dependant':
            $dependantID = isset($_POST['dependantID']) && !empty($_POST['dependantID']) ?
                Utility::clean_string($_POST['dependantID']) : null;
            $employeeID = Utility::clean_string($_POST['employeeID'] ?? '');

            if (empty($employeeID)) {
                throw new Exception('Employee ID is required');
            }

            // Process date of birth - validate format (required for dependants)
            $dateOfBirth = null;
            if (isset($_POST['dateOfBirth']) && !empty($_POST['dateOfBirth'])) {
                $dob = trim($_POST['dateOfBirth']);
                // Validate date format (YYYY-MM-DD)
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
                    $dateOfBirth = $dob;
                }
            }

            $data = [
                'employeeID' => $employeeID,
                'fullName' => Utility::clean_string($_POST['fullName'] ?? ''),
                'relationship' => Utility::clean_string($_POST['relationship'] ?? ''),
                'dateOfBirth' => $dateOfBirth,
                'gender' => Utility::clean_string($_POST['gender'] ?? ''),
                'nationalID' => Utility::clean_string($_POST['nationalID'] ?? ''),
                'isBeneficiary' => isset($_POST['isBeneficiary']) && $_POST['isBeneficiary'] == 'Y' ? 'Y' : 'N',
                'isStudent' => isset($_POST['isStudent']) && $_POST['isStudent'] == 'Y' ? 'Y' : 'N',
                'isDisabled' => isset($_POST['isDisabled']) && $_POST['isDisabled'] == 'Y' ? 'Y' : 'N',
                'isDependentForTax' => isset($_POST['isDependentForTax']) && $_POST['isDependentForTax'] == 'Y' ? 'Y' : 'N',
                'schoolName' => Utility::clean_string($_POST['schoolName'] ?? ''),
                'grade' => Utility::clean_string($_POST['grade'] ?? ''),
                'studentID' => Utility::clean_string($_POST['studentID'] ?? ''),
                'bloodType' => Utility::clean_string($_POST['bloodType'] ?? ''),
                'medicalConditions' => Utility::clean_string($_POST['medicalConditions'] ?? ''),
                'insuranceMemberNumber' => Utility::clean_string($_POST['insuranceMemberNumber'] ?? ''),
                'phoneNumber' => Utility::clean_string($_POST['phoneNumber'] ?? ''),
                'emailAddress' => Utility::clean_string($_POST['emailAddress'] ?? ''),
                'notes' => Utility::clean_string($_POST['notes'] ?? ''),
                'updatedBy' => $userDetails->ID,
                'Suspended' => 'N',
                'Lapsed' => 'N'
            ];

            // Validate required fields with detailed error messages
            $missingFields = [];
            if (empty($data['fullName'])) $missingFields[] = 'fullName';
            if (empty($data['relationship'])) $missingFields[] = 'relationship';
            if (empty($data['dateOfBirth'])) $missingFields[] = 'dateOfBirth';
            if (empty($data['gender'])) $missingFields[] = 'gender';

            if (!empty($missingFields)) {
                error_log('Missing fields: ' . implode(', ', $missingFields));
                error_log('POST data received: ' . print_r($_POST, true));
                throw new Exception('Required fields missing: ' . implode(', ', $missingFields) . '. Please fill all required fields.');
            }

            // Validate date format
            if ($data['dateOfBirth'] && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['dateOfBirth'])) {
                throw new Exception('Invalid date format. Please use YYYY-MM-DD format');
            }

            if ($dependantID) {
                // Update existing
                $updateResult = $DBConn->update_table('tija_employee_dependants', $data, ['dependantID' => $dependantID]);
                if ($updateResult === false) {
                    // Get last error for debugging
                    $error = $DBConn->error ?? 'Unknown database error';
                    throw new Exception('Failed to update dependant: ' . $error);
                }
                $response['message'] = 'Dependant updated successfully';
            } else {
                // Create new
                $data['createdBy'] = $userDetails->ID;

                $insertResult = $DBConn->insert_data('tija_employee_dependants', $data);
                if ($insertResult === false) {
                    // Get last error for debugging
                    $error = $DBConn->error ?? 'Unknown database error';
                    throw new Exception('Failed to create dependant: ' . $error);
                }
                $response['message'] = 'Dependant created successfully';
            }

            $response['success'] = true;
            break;

        case 'get_dependant':
            $dependantID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$dependantID) {
                throw new Exception('Dependant ID is required');
            }

            $dependant = EmployeeProfileExtended::get_dependants(['dependantID' => $dependantID], true, $DBConn);
            if (!$dependant) {
                throw new Exception('Dependant not found');
            }

            $response['success'] = true;
            $response['data'] = $dependant;
            break;

        case 'delete_dependant':
            $dependantID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$dependantID) {
                throw new Exception('Dependant ID is required');
            }

            // Soft delete
            if (!$DBConn->update_table('tija_employee_dependants', ['Suspended' => 'Y'], ['dependantID' => $dependantID])) {
                throw new Exception('Failed to delete dependant');
            }

            $response['success'] = true;
            $response['message'] = 'Dependant deleted successfully';
            break;

        default:
            throw new Exception('Invalid action specified');
    }

    if ($response['success']) {
        $DBConn->commit();
    } else {
        $DBConn->rollback();
    }

} catch (Exception $e) {
    $DBConn->rollback();
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>

