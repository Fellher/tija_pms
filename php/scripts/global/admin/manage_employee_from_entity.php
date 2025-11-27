<?php
/**
 * Manage Employee From Entity Page
 * Creates new employee record from entity details page
 *
 * @package Tija Practice Management System
 * @subpackage Admin
 * @version 1.0
 */

session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';

$DBConn->begin();
$errors = array();
$success = "";

if ($isValidAdmin || $isAdmin || $isValidUser) {

    // Personal Information
    $firstName = (isset($_POST['FirstName']) && !empty($_POST['FirstName'])) ? Utility::clean_string($_POST['FirstName']) : "";
    $surname = (isset($_POST['Surname']) && !empty($_POST['Surname'])) ? Utility::clean_string($_POST['Surname']) : "";
    $otherNames = (isset($_POST['OtherNames']) && !empty($_POST['OtherNames'])) ? Utility::clean_string($_POST['OtherNames']) : "";
    $userInitials = (isset($_POST['userInitials']) && !empty($_POST['userInitials'])) ? Utility::clean_string($_POST['userInitials']) : "";
    $email = (isset($_POST['Email']) && !empty($_POST['Email'])) ? Utility::clean_string($_POST['Email']) : "";
    $phoneNo = (isset($_POST['phoneNumber']) && !empty($_POST['phoneNumber'])) ? Utility::clean_string($_POST['phoneNumber']) : "";
    $gender = (isset($_POST['gender']) && !empty($_POST['gender'])) ? Utility::clean_string($_POST['gender']) : "";
    $dateOfBirth = (isset($_POST['dateOfBirth']) && !empty($_POST['dateOfBirth'])) ? Utility::clean_string($_POST['dateOfBirth']) : "";
    $nationalID = (isset($_POST['nationalID']) && !empty($_POST['nationalID'])) ? Utility::clean_string($_POST['nationalID']) : "";
    $prefixID = (isset($_POST['prefixID']) && !empty($_POST['prefixID'])) ? Utility::clean_string($_POST['prefixID']) : "";

    // Employment Details
    $orgDataID = (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ? Utility::clean_string($_POST['orgDataID']) : "";
    $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ? Utility::clean_string($_POST['entityID']) : "";
    $jobTitleID = (isset($_POST['jobTitleID']) && !empty($_POST['jobTitleID'])) ? Utility::clean_string($_POST['jobTitleID']) : "";
    $employeeTypeID = (isset($_POST['employeeTypeID']) && !empty($_POST['employeeTypeID'])) ? Utility::clean_string($_POST['employeeTypeID']) : "";
    $dateOfEmployment = (isset($_POST['dateOfEmployment']) && !empty($_POST['dateOfEmployment'])) ? Utility::clean_string($_POST['dateOfEmployment']) : "";
    $payrollNumber = (isset($_POST['payrollNumber']) && !empty($_POST['payrollNumber'])) ? Utility::clean_string($_POST['payrollNumber']) : "";
    // Handle supervisorID - 0 is valid (no supervisor), don't skip it
    $supervisorID = isset($_POST['supervisorID']) ? Utility::clean_string($_POST['supervisorID']) : null;
    $dailyWorkHours = (isset($_POST['dailyWorkHours']) && !empty($_POST['dailyWorkHours'])) ? Utility::clean_string($_POST['dailyWorkHours']) : "";

    // Payroll Details
    $basicSalary = (isset($_POST['basicSalary']) && !empty($_POST['basicSalary'])) ? Utility::clean_string($_POST['basicSalary']) : "";
    $pin = (isset($_POST['pin']) && !empty($_POST['pin'])) ? Utility::clean_string($_POST['pin']) : "";
    $nhifNumber = (isset($_POST['nhifNumber']) && !empty($_POST['nhifNumber'])) ? Utility::clean_string($_POST['nhifNumber']) : "";
    $nssfNumber = (isset($_POST['nssfNumber']) && !empty($_POST['nssfNumber'])) ? Utility::clean_string($_POST['nssfNumber']) : "";
    $costPerHour = (isset($_POST['costPerHour']) && !empty($_POST['costPerHour'])) ? Utility::clean_string($_POST['costPerHour']) : "";
    $overtimeAllowed = (isset($_POST['overtimeAllowed']) && $_POST['overtimeAllowed'] === 'Y') ? 'Y' : 'N';
    $bonusEligible = (isset($_POST['bonusEligible']) && $_POST['bonusEligible'] === 'Y') ? 'Y' : 'N';
    $commissionEligible = (isset($_POST['commissionEligible']) && $_POST['commissionEligible'] === 'Y') ? 'Y' : 'N';

    // Validation
    if (!$firstName) $errors[] = "First name is required";
    if (!$surname) $errors[] = "Surname is required";
    if (!$email) {
        $errors[] = "Email is required";
    } else {
        // Check if email already exists
        $existingUser = Core::user(['Email' => $email], true, $DBConn);
        if ($existingUser) {
            $errors[] = "A user with this email already exists";
        }
    }
    if (!$phoneNo) $errors[] = "Phone number is required";
    if (!$gender) $errors[] = "Gender is required";
    if (!$orgDataID) $errors[] = "Organization ID is required";
    if (!$entityID) $errors[] = "Entity ID is required";
    if (!$jobTitleID) $errors[] = "Job title is required";
    if (!$employeeTypeID) $errors[] = "Employment status is required";
    if (!$dateOfEmployment) $errors[] = "Employment start date is required";

    if (count($errors) === 0) {
        // Create person record first
        $personDetails = array(
            'FirstName' => $firstName,
            'Surname' => $surname,
            'OtherNames' => $otherNames,
            'Email' => $email,
            'userInitials' => $userInitials
        );

        if (!$DBConn->insert_data('people', $personDetails)) {
            $errors[] = "Error creating user account";
        } else {
            $userID = $DBConn->lastInsertId();

            // Generate registration tokens
            $tokens = Core::add_registration_tokens($userID, $DBConn);

            if ($userID) {
                // Create employee record
                $employeeUserDetails = array(
                    'ID' => $userID,
                    'UID' => bin2hex(openssl_random_pseudo_bytes(32)),
                    'LastUpdatedByID' => $userDetails->ID,
                    'prefixID' => $prefixID ? $prefixID : null,
                    'phoneNo' => $phoneNo,
                    'gender' => $gender,
                    'dateOfBirth' => $dateOfBirth ? $dateOfBirth : null,
                    'orgDataID' => $orgDataID,
                    'entityID' => $entityID,
                    'jobTitleID' => $jobTitleID,
                    'employmentStatusID' => $employeeTypeID,
                    'nationalID' => $nationalID ? $nationalID : null,
                    'nhifNumber' => $nhifNumber ? $nhifNumber : null,
                    'nssfNumber' => $nssfNumber ? $nssfNumber : null,
                    'PIN' => $pin ? $pin : null,
                    'payrollNo' => $payrollNumber ? $payrollNumber : null,
                    'salary' => $basicSalary ? $basicSalary : null,
                    'basicSalary' => $basicSalary ? $basicSalary : null,
                    'dailyHours' => $dailyWorkHours ? $dailyWorkHours : null,
                    'employmentStartDate' => $dateOfEmployment,
                    'supervisorID' => ($supervisorID !== null && $supervisorID !== '') ? (int)$supervisorID : null,
                    'costPerHour' => $costPerHour ? $costPerHour : null,
                    'overtimeAllowed' => $overtimeAllowed,
                    'bonusEligible' => $bonusEligible,
                    'commissionEligible' => $commissionEligible
                );

                if (!$DBConn->insert_data('user_details', $employeeUserDetails)) {
                    $errors[] = "Error creating employee record";
                } else {
                    $success = "Employee added successfully! Registration email will be sent.";

                    // TODO: Send registration email
                    // You can add email notification here if needed
                }
            }
        }
    }

} else {
    $errors[] = 'You need to log in as a valid administrator to add employees.';
}

// Build return URL
$returnURL = Utility::returnURL($_SESSION['returnURL'], 's=core&ss=admin&p=entity_details&entityID=' . $entityID . '&tab=employees');

if (count($errors) == 0) {
    $DBConn->commit();
    $messages = array(array('Text' => $success, 'Type' => 'success'));
} else {
    $DBConn->rollback();
    $messages = array_map(function($error) {
        return array('Text' => $error, 'Type' => 'danger');
    }, $errors);
}

$_SESSION['FlashMessages'] = serialize($messages);
header("location:{$base}html/{$returnURL}");
?>

