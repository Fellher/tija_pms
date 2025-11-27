
<?php
session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
$employeeChanges = array();
$success="";
if ( $isValidUser) {

  var_dump($userDetails);
	var_dump($_POST);
  var_dump($_FILES);
    // check if userID is in POST and is not empty
    $userID = (isset($_POST['ID']) && !empty($_POST['ID'])) ? Utility::clean_string($_POST['ID']) : "";

    $prefixID = (isset($_POST['prefixID']) && !empty($_POST['prefixID'])) ? Utility::clean_string($_POST['prefixID']) : "";



    // Get user profile details from POST
    $firstName = (isset($_POST['FirstName']) && !empty($_POST['FirstName'])) ? Utility::clean_string($_POST['FirstName']) : "";
    $surname = (isset($_POST['Surname']) && !empty($_POST['Surname'])) ? Utility::clean_string($_POST['Surname']) : "";
    $userInitials = (isset($_POST['userInitials']) && !empty($_POST['userInitials'])) ? Utility::clean_string($_POST['userInitials']) : "";
    $email = (isset($_POST['Email']) && !empty($_POST['Email'])) ? Utility::clean_string($_POST['Email']) : "";
    $phoneNo = (isset($_POST['phoneNumber']) && !empty($_POST['phoneNumber'])) ? Utility::clean_string($_POST['phoneNumber']) : "";
    $gender = (isset($_POST['gender']) && !empty($_POST['gender'])) ? Utility::clean_string($_POST['gender']) : "";
    $dateOfBirth = (isset($_POST['dateOfBirth']) && !empty($_POST['dateOfBirth'])) ? Utility::clean_string($_POST['dateOfBirth']) : "";
    $otherNames = (isset($_POST['OtherNames']) && !empty($_POST['OtherNames'])) ? Utility::clean_string($_POST['OtherNames']) : "";
    $profileImage = (isset($_FILES['profile_image']) && !empty($_FILES['profile_image']['name'])) ? $_FILES['profile_image'] : null;

    var_dump($profileImage);

    // Validate profile image if provided
    if($profileImage && $profileImage['error'] == 0) {
      $fileTypeArrayExtensions = ['jpeg', 'png', 'jpg', 'gif', 'webp'];
      $profileImage = File::upload_file(
        $profileImage,
        'employee_profile',
        $fileTypeArrayExtensions,
        $allowedFileSize = 10 * 1024 * 1024, // 10MB
        $config,
        $DBConn);
        ECHO "<h4> Profile Image </h4>";
        var_dump($profileImage);

        if($profileImage['status'] == 'error') {
          $errors[] = $profileImage['message'];
        } else {
          $profile_image = $profileImage['uploadedFilePaths'];
          $profileImagePath = $profileImage['uploadedFilePaths']; // Store the file path for later use
        }
    } else {
      $profile_image = null; // No image uploaded
        $profileImagePath = null; // No image uploaded
    }
    echo "<h4> Profile Image Path </h4>";
    var_dump($profile_image);

    // Get employment details (check both possible field names)
    $organisationID = (isset($_POST['organisationID']) && !empty($_POST['organisationID'])) ? Utility::clean_string($_POST['organisationID']) :
                     ((isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ? Utility::clean_string($_POST['orgDataID']) : "");
    $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ? Utility::clean_string($_POST['entityID']) : "";
    $jobTitleID = (isset($_POST['jobTitleID']) && !empty($_POST['jobTitleID'])) ? Utility::clean_string($_POST['jobTitleID']) : "";
    $employeeTypeID = (isset($_POST['employeeTypeID']) && !empty($_POST['employeeTypeID'])) ? Utility::clean_string($_POST['employeeTypeID']) : "";
    $nationalID = (isset($_POST['nationalID']) && !empty($_POST['nationalID'])) ? Utility::clean_string($_POST['nationalID']) : "";
    $nhifNumber = (isset($_POST['nhifNumber']) && !empty($_POST['nhifNumber'])) ? Utility::clean_string($_POST['nhifNumber']) : "";
    $nssfNumber = (isset($_POST['nssfNumber']) && !empty($_POST['nssfNumber'])) ? Utility::clean_string($_POST['nssfNumber']) : "";
    $pin = (isset($_POST['pin']) && !empty($_POST['pin'])) ? Utility::clean_string($_POST['pin']) : "";
    $basicSalary = (isset($_POST['basicSalary']) && !empty($_POST['basicSalary'])) ? Utility::clean_string($_POST['basicSalary']) : "";
    $payGradeID = (isset($_POST['payGradeID']) && !empty($_POST['payGradeID'])) ? Utility::clean_string($_POST['payGradeID']) : "";
    $costPerHour = (isset($_POST['costPerHour']) && !empty($_POST['costPerHour'])) ? Utility::clean_string($_POST['costPerHour']) : "";
    $overtimeAllowed = (isset($_POST['overtimeAllowed']) && $_POST['overtimeAllowed'] === 'Y') ? 'Y' : 'N';

    // Allowances
    $housingAllowance = (isset($_POST['housingAllowance']) && !empty($_POST['housingAllowance'])) ? Utility::clean_string($_POST['housingAllowance']) : 0;
    $transportAllowance = (isset($_POST['transportAllowance']) && !empty($_POST['transportAllowance'])) ? Utility::clean_string($_POST['transportAllowance']) : 0;
    $medicalAllowance = (isset($_POST['medicalAllowance']) && !empty($_POST['medicalAllowance'])) ? Utility::clean_string($_POST['medicalAllowance']) : 0;
    $communicationAllowance = (isset($_POST['communicationAllowance']) && !empty($_POST['communicationAllowance'])) ? Utility::clean_string($_POST['communicationAllowance']) : 0;
    $otherAllowances = (isset($_POST['otherAllowances']) && !empty($_POST['otherAllowances'])) ? Utility::clean_string($_POST['otherAllowances']) : 0;
    $bonusEligible = (isset($_POST['bonusEligible']) && $_POST['bonusEligible'] === 'Y') ? 'Y' : 'N';
    $commissionEligible = (isset($_POST['commissionEligible']) && $_POST['commissionEligible'] === 'Y') ? 'Y' : 'N';
    $commissionRate = (isset($_POST['commissionRate']) && !empty($_POST['commissionRate'])) ? Utility::clean_string($_POST['commissionRate']) : 0;

    $dailyWorkHours = (isset($_POST['dailyWorkHours']) && !empty($_POST['dailyWorkHours'])) ? Utility::clean_string($_POST['dailyWorkHours']) : "";
    $weekWorkDays = (isset($_POST['weekWorkDays']) && !empty($_POST['weekWorkDays'])) ? Utility::clean_string($_POST['weekWorkDays']) : "";
    $dateOfEmployment = (isset($_POST['dateOfEmployment']) && !empty($_POST['dateOfEmployment'])) ? Utility::clean_string($_POST['dateOfEmployment']) : "";
    $dateOfTermination = (isset($_POST['dateOfTermination']) && !empty($_POST['dateOfTermination'])) ? Utility::clean_string($_POST['dateOfTermination']) : "";
    $contractStartDate = (isset($_POST['contractStartDate']) && !empty($_POST['contractStartDate'])) ? Utility::clean_string($_POST['contractStartDate']) : "";
    $contractEndDate = (isset($_POST['contractEndDate']) && !empty($_POST['contractEndDate'])) ? Utility::clean_string($_POST['contractEndDate']) : "";
    $workHourRounding = (isset($_POST['workHourRounding']) && !empty($_POST['workHourRounding'])) ? Utility::clean_string($_POST['workHourRounding']) : "";
    $payrollNumber = (isset($_POST['payrollNumber']) && !empty($_POST['payrollNumber'])) ? Utility::clean_string($_POST['payrollNumber']) : "";
    // Handle supervisorID - 0 is valid (no supervisor), don't skip it
    $supervisorID = isset($_POST['supervisorID']) ? Utility::clean_string($_POST['supervisorID']) : "";

    // Get extended personal details
    $middleName = (isset($_POST['middleName']) && !empty($_POST['middleName'])) ? Utility::clean_string($_POST['middleName']) : "";
    $maidenName = (isset($_POST['maidenName']) && !empty($_POST['maidenName'])) ? Utility::clean_string($_POST['maidenName']) : "";
    $maritalStatus = (isset($_POST['maritalStatus']) && !empty($_POST['maritalStatus'])) ? Utility::clean_string($_POST['maritalStatus']) : "";
    $nationality = (isset($_POST['nationality']) && !empty($_POST['nationality'])) ? Utility::clean_string($_POST['nationality']) : "";
    $passportNumber = (isset($_POST['passportNumber']) && !empty($_POST['passportNumber'])) ? Utility::clean_string($_POST['passportNumber']) : "";
    $passportIssueDate = (isset($_POST['passportIssueDate']) && !empty($_POST['passportIssueDate'])) ? Utility::clean_string($_POST['passportIssueDate']) : "";
    $passportExpiryDate = (isset($_POST['passportExpiryDate']) && !empty($_POST['passportExpiryDate'])) ? Utility::clean_string($_POST['passportExpiryDate']) : "";
    $bloodGroup = (isset($_POST['bloodGroup']) && !empty($_POST['bloodGroup'])) ? Utility::clean_string($_POST['bloodGroup']) : "";
    $religion = (isset($_POST['religion']) && !empty($_POST['religion'])) ? Utility::clean_string($_POST['religion']) : "";
    $ethnicity = (isset($_POST['ethnicity']) && !empty($_POST['ethnicity'])) ? Utility::clean_string($_POST['ethnicity']) : "";
    $languagesSpoken = (isset($_POST['languagesSpoken']) && !empty($_POST['languagesSpoken'])) ? Utility::clean_string($_POST['languagesSpoken']) : "";
    $disabilities = (isset($_POST['disabilities']) && !empty($_POST['disabilities'])) ? Utility::clean_string($_POST['disabilities']) : "";


    // Get unit type assignments if any
    $unitTypes = array();
    if(isset($_POST['unitType']) && is_array($_POST['unitType'])) {
        foreach($_POST['unitType'] as $typeID => $unitID) {
            if(!empty($unitID)) {
                $unitTypes[$typeID] = Utility::clean_string($unitID);
            }
        }
    }


    var_dump($prefixID);

    // Debug: Show received values
    echo "<h4>Received Organisation/Entity IDs:</h4>";
    echo "organisationID: "; var_dump($organisationID);
    echo "entityID: "; var_dump($entityID);

    // Validate required fields (only for basic info - org/entity not required for inline quick edit)
    // if(!$prefixID) $errors[] = "Prefix is required";
    if(!$userID && !$firstName) $errors[] = "First name is required";
    if(!$userID && !$surname) $errors[] = "Surname is required";
    if(!$userID && !$email) $errors[] = "Email is required";

    // Organisation and Entity are only required when adding NEW employees, not for quick edits
    if(!$userID) {
        // New employee - require org and entity
    if(!$organisationID) $errors[] = "Organisation is required";
    if(!$entityID) $errors[] = "Entity is required";
    } else {
        // Existing employee - use current values if not provided
        if(!$organisationID && isset($employeeDetails->orgDataID)) {
            $organisationID = $employeeDetails->orgDataID;
        }
        if(!$entityID && isset($employeeDetails->entityID)) {
            $entityID = $employeeDetails->entityID;
        }
    }
    // if(!$jobTitleID) $errors[] = "Job title is required";
    // if(!$employeeTypeID) $errors[] = "Employee type is required";
    // if(!$nationalID) $errors[] = "National ID is required";



    if(!$userID) {
      // Check if email is unique
      $user = Core::user(array("Email"=>$email), true, $DBConn);
      if($user) $errors[] = "The email address provided is already in use";

      // If validation passes, prepare data for insertion/update
      if(count($errors) == 0) {
        // $personDetails= array(
        //     'FirstName' => $firstName,
        //     'Surname' => $surname,
        //     'OtherNames' => $otherNames ? $otherNames : "",
        //     'Email' => $email
        // );
        $firstName  ? $personDetails['FirstName'] = $firstName : $errors[] = "First name is required";
        $surname  ? $personDetails['Surname'] = $surname : $errors[] = "Surname is required";
        $otherNames  ? $personDetails['OtherNames'] = $otherNames : "";
        $email  ? $personDetails['Email'] = $email : $errors[] = "Email is required";
        $userInitials  ? $personDetails['userInitials'] = $userInitials :  $personDetails['userInitials'] = Core::user_name_initials((object)['FirstName'=>$firstName, 'Surname'=>$surname]);
        $profile_image ? $personDetails['profile_image'] = $profile_image : "";

        if(count($errors)=== 0) {

          if($personDetails) {
            if(!$DBConn->insert_data('people', $personDetails)) {
              $errors[] = "There was an error adding the user.";
            } else {
              $userID = $DBConn->lastInsertId();
              $tokens = Core::add_registration_tokens($userID, $DBConn);
              if($userID) {

                $employeeUserDetails = array(
                  'ID' => $userID,
                  'UID' => bin2hex(openssl_random_pseudo_bytes(32)),
                  'LastUpdatedByID' => $userDetails->ID,
                  'prefixID' => $prefixID ? $prefixID : "",
                  'phoneNo' => $phoneNo,
                  'gender' => $gender,
                  'dateOfBirth' => $dateOfBirth,
                  'orgDataID' => $organisationID,
                  'entityID' => $entityID,
                  'jobTitleID' => $jobTitleID,
                  'employmentStatusID' => $employeeTypeID,
                  'nationalID' => $nationalID,
                  'nhifNumber' => $nhifNumber,
                  'nssfNumber' => $nssfNumber,
                  'PIN' => $pin,
                  'payrollNo'=> $payrollNumber,
                  'basicSalary' => $basicSalary,
                  'dailyHours' => $dailyWorkHours,
                  'employmentStartDate' => $dateOfEmployment,
                  'employmentEndDate' => $dateOfTermination,
                  'workHourRoundingID' => $workHourRounding,
                  'supervisorID' => $supervisorID,
                  'profileImageFile' => $profile_image ? $profile_image : null,
                );
                if(!$DBConn->insert_data('user_details', $employeeUserDetails)) {
                  $errors[] = "There was an error adding the user.";
                } else {
                  $newUserID = $DBConn->lastInsertId();
                  $success .= "User details have been saved successfully";

                  // Sync supervisor to new reporting relationships table
                  if ($supervisorID && $supervisorID != '0' && $supervisorID != 0) {
                      Reporting::syncSupervisorToReporting($newUserID, $supervisorID, $DBConn, $dateOfEmployment);
                  }

                  // add the unit type assignments
                  if(count($unitTypes) > 0) {
                    foreach($unitTypes as $typeID => $unitID) {
                      $unitAssignment = array(
                        'userID' => $userID,
                        'unitTypeID' => $typeID,
                        'unitID' => $unitID,
                        'LastUpdatedByID' => $userDetails->ID,
                        'orgDataID' => $organisationID,
                        'entityID' => $entityID,
                        'assignmentStartDate' => $dateOfEmployment,
                      );
                      if($dateOfTermination) $unitAssignment['assignmentEndDate'] = $dateOfTermination;
                      if($unitAssignment){
                        if(!$DBConn->insert_data('tija_user_unit_assignments', $unitAssignment)) {
                          $errors[] = "There was an error adding the unit type assignments for {$unitID} of type {$typeID} for user {$userID}";
                        } else {
                          $success .= "User assignment details have been saved successfully";
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    } else {


      $employeeDetails = Employee::employees(['ID'=>$userID], true, $DBConn);
      var_dump($employeeDetails);
      echo "<h4> OtherNames is {$otherNames} </h4>";
      var_dump($otherNames);

      // Only proceed if no validation errors
      if($employeeDetails && count($errors) == 0) {
      // check and populate changes
        if($employeeDetails->FirstName != $firstName) $changes['FirstName'] = $firstName;
        if($employeeDetails->Surname != $surname) $changes['Surname'] = $surname;
        if($employeeDetails->Email != $email) $changes['Email'] = $email;
        $otherNames && $employeeDetails->OtherNames != $otherNames ? $changes['OtherNames'] = $otherNames : "";
        $userInitials && $employeeDetails->userInitials != $userInitials ? $changes['userInitials'] = $userInitials : "";
        $profile_image && $employeeDetails->profile_image != $profile_image ? $changes['profile_image'] = $profile_image : "";

        if($changes) {
          var_dump($changes);

          $update = $DBConn->update_table('people', $changes, array('ID'=>$userID));
          if(!$update) {
            $errors[] = "There was an error updating the user details.";
          } else {
            $success .= "User details have been updated successfully";
          }
        }

        // check and update the users employment details
        if($prefixID != $employeeDetails->prefixID)  $employeeChanges['prefixID'] = $prefixID;
        if($employeeDetails->orgDataID != $organisationID) $employeeChanges['orgDataID'] = $organisationID;
        if($employeeDetails->entityID != $entityID) $employeeChanges['entityID'] = $entityID;
        if($employeeDetails->jobTitleID != $jobTitleID) $employeeChanges['jobTitleID'] = $jobTitleID;
        if($employeeDetails->employmentStatusID != $employeeTypeID) $employeeChanges['employmentStatusID'] = $employeeTypeID;
        if($employeeDetails->nationalID != (int)$nationalID) $employeeChanges['nationalID'] = (int)$nationalID;

        $phoneNo && $employeeDetails->phoneNo != $phoneNo ? $employeeChanges['phoneNo'] = $phoneNo : "";
        $gender && $employeeDetails->gender != $gender ? $employeeChanges['gender'] = $gender : "";
        $payrollNumber && $employeeDetails->payrollNo != $payrollNumber ? $employeeChanges['payrollNo'] = $payrollNumber : "";

        // Handle supervisorID - allow 0 (no supervisor) or actual ID
        if (isset($_POST['supervisorID'])) {
            $supervisorID = Utility::clean_string($_POST['supervisorID']);
            // Convert to integer, 0 is valid (no supervisor), empty string becomes null
            $newSupervisorID = ($supervisorID === '' || $supervisorID === null) ? null : (int)$supervisorID;
            $currentSupervisorID = isset($employeeDetails->supervisorID) ? (int)$employeeDetails->supervisorID : null;

            if ($newSupervisorID !== $currentSupervisorID) {
                $employeeChanges['supervisorID'] = $newSupervisorID;

                // Sync to new reporting relationships table
                Reporting::syncSupervisorToReporting($userID, $newSupervisorID, $DBConn);
            }
        }


        var_dump($employeeDetails->nationalID);
        var_dump($nationalID);
        if($employeeDetails->nhifNumber != $nhifNumber) $employeeChanges['nhifNumber'] = $nhifNumber;
        if($employeeDetails->nssfNumber != $nssfNumber) $employeeChanges['nssfNumber'] = $nssfNumber;
        if($employeeDetails->pin != $pin) $employeeChanges['pin'] = $pin;

        if($employeeDetails->basicSalary != (float)$basicSalary) $employeeChanges['basicSalary'] = (float)$basicSalary;
        if($payGradeID && $employeeDetails->payGradeID != $payGradeID) $employeeChanges['payGradeID'] = $payGradeID;
        if($costPerHour && $employeeDetails->costPerHour != $costPerHour) $employeeChanges['costPerHour'] = (float)$costPerHour;
        if($employeeDetails->overtimeAllowed != $overtimeAllowed) $employeeChanges['overtimeAllowed'] = $overtimeAllowed;

        // Bonus & Commission (now in user_details table)
        if(isset($employeeDetails->bonusEligible) && $employeeDetails->bonusEligible != $bonusEligible) $employeeChanges['bonusEligible'] = $bonusEligible;
        if(!isset($employeeDetails->bonusEligible)) $employeeChanges['bonusEligible'] = $bonusEligible; // Add if column exists but no value

        if(isset($employeeDetails->commissionEligible) && $employeeDetails->commissionEligible != $commissionEligible) $employeeChanges['commissionEligible'] = $commissionEligible;
        if(!isset($employeeDetails->commissionEligible)) $employeeChanges['commissionEligible'] = $commissionEligible;

        if(isset($employeeDetails->commissionRate) && $employeeDetails->commissionRate != (float)$commissionRate) $employeeChanges['commissionRate'] = (float)$commissionRate;
        if(!isset($employeeDetails->commissionRate)) $employeeChanges['commissionRate'] = (float)$commissionRate;
        if($employeeDetails->dailyHours != $dailyWorkHours) $employeeChanges['dailyHours'] = $dailyWorkHours;
        if($weekWorkDays && $employeeDetails->weekWorkDays != $weekWorkDays) $employeeChanges['weekWorkDays'] = $weekWorkDays;
        if($employeeDetails->employmentStartDate != $dateOfEmployment) $employeeChanges['employmentStartDate'] = $dateOfEmployment;
        if($employeeDetails->employmentEndDate != $dateOfTermination) $employeeChanges['employmentEndDate'] = $dateOfTermination;
        if($contractStartDate && $employeeDetails->contractStartDate != $contractStartDate) $employeeChanges['contractStartDate'] = $contractStartDate;
        if($contractEndDate && $employeeDetails->contractEndDate != $contractEndDate) $employeeChanges['contractEndDate'] = $contractEndDate;
        if($employeeDetails->workHourRoundingID != $workHourRounding) $employeeChanges['workHourRoundingID'] = $workHourRounding;
        ($dateOfBirth && $employeeDetails->dateOfBirth != $dateOfBirth) ? $employeeChanges['dateOfBirth'] = $dateOfBirth : "";
        ($profile_image && $employeeDetails->profileImageFile != $profile_image) ? $employeeChanges['profileImageFile'] = $profile_image : "";
        echo  "<h5> Employee Changes </h5>";
        var_dump($employeeChanges);
        if($employeeChanges) {
          $employeeChanges['LastUpdatedByID'] = $userDetails->ID;
          var_dump($employeeChanges);
          // $update = $DBConn->update_table('user_details', $employeeChanges, array('ID'=>$userID));
          if(!$DBConn->update_table('user_details', $employeeChanges, array('ID'=>$userID))) {
            $errors[] = "There was an error updating the user employment details.";
            var_dump($errors);
          } else {
            $success .= "User employment details have been updated successfully";
          }
        }

        // Update extended personal details (if any extended fields provided)
        if($middleName || $maidenName || $maritalStatus || $nationality || $passportNumber || $bloodGroup || $religion || $ethnicity || $languagesSpoken || $disabilities || $passportIssueDate || $passportExpiryDate) {

          // Check if extended personal record exists - use direct query for reliability
          $checkSql = "SELECT COUNT(*) as count FROM tija_employee_extended_personal WHERE employeeID = " . intval($userID) . " AND Suspended = 'N'";
          $DBConn->query($checkSql);
          $DBConn->execute();
          $checkResult = $DBConn->single();
          $recordExists = ($checkResult && $checkResult->count > 0);

          echo "<h4>Extended Personal Check</h4>";
          echo "Record exists: " . ($recordExists ? 'YES' : 'NO') . "<br>";
          echo "User ID: " . $userID . "<br>";

          // Prepare data (without employeeID for update)
          $extendedData = [];

          if($middleName) $extendedData['middleName'] = $middleName;
          if($maidenName) $extendedData['maidenName'] = $maidenName;
          if($maritalStatus) $extendedData['maritalStatus'] = $maritalStatus;
          if($nationality) $extendedData['nationality'] = $nationality;
          if($passportNumber) $extendedData['passportNumber'] = $passportNumber;
          if($passportIssueDate) $extendedData['passportIssueDate'] = $passportIssueDate;
          if($passportExpiryDate) $extendedData['passportExpiryDate'] = $passportExpiryDate;
          if($bloodGroup) $extendedData['bloodGroup'] = $bloodGroup;
          if($religion) $extendedData['religion'] = $religion;
          if($ethnicity) $extendedData['ethnicity'] = $ethnicity;
          if($languagesSpoken) $extendedData['languagesSpoken'] = $languagesSpoken;
          if($disabilities) $extendedData['disabilities'] = $disabilities;

          $extendedData['updatedBy'] = $userDetails->ID;

          if($recordExists) {
            // Record exists - UPDATE
            echo "<p>Performing UPDATE...</p>";
            var_dump($extendedData);
            $updateResult = $DBConn->update_table('tija_employee_extended_personal', $extendedData, array('employeeID' => $userID));
            if($updateResult !== false) {
              $success = "Personal details updated successfully";
              echo "<p style='color:green'>✓ UPDATE successful</p>";
            } else {
              $errors[] = "Failed to update extended personal details";
              echo "<p style='color:red'>✗ UPDATE failed</p>";
            }
          } else {
            // Record doesn't exist - INSERT
            echo "<p>Performing INSERT...</p>";
            $extendedData['employeeID'] = $userID;  // Add employeeID only for insert
            $extendedData['createdBy'] = $userDetails->ID;
            var_dump($extendedData);
            $insertResult = $DBConn->insert_data('tija_employee_extended_personal', $extendedData);
            if($insertResult) {
              $success = "Extended personal details added successfully";
              echo "<p style='color:green'>✓ INSERT successful</p>";
            } else {
              $errors[] = "Failed to insert extended personal details";
              echo "<p style='color:red'>✗ INSERT failed</p>";
            }
          }
        }

        // Update allowances (if any allowance fields provided)
        // NOTE: Bonus/Commission are now in user_details, not here
        if($housingAllowance || $transportAllowance || $medicalAllowance || $communicationAllowance || $otherAllowances) {

          // Check if allowance record exists
          $checkSql = "SELECT COUNT(*) as count FROM tija_employee_allowances WHERE employeeID = " . intval($userID) . " AND isCurrent = 'Y' AND Suspended = 'N'";
          $DBConn->query($checkSql);
          $DBConn->execute();
          $checkResult = $DBConn->single();
          $allowanceExists = ($checkResult && $checkResult->count > 0);

          // Prepare allowance data (ONLY allowances, NOT bonus/commission)
          $allowanceData = [];
          $allowanceData['housingAllowance'] = (float)$housingAllowance;
          $allowanceData['transportAllowance'] = (float)$transportAllowance;
          $allowanceData['medicalAllowance'] = (float)$medicalAllowance;
          $allowanceData['communicationAllowance'] = (float)$communicationAllowance;
          $allowanceData['otherAllowances'] = (float)$otherAllowances;
          $allowanceData['updatedBy'] = $userDetails->ID;

          if($allowanceExists) {
            // Record exists - UPDATE
            $updateResult = $DBConn->update_table('tija_employee_allowances', $allowanceData, array('employeeID' => $userID, 'isCurrent' => 'Y'));
            if($updateResult !== false) {
              $success = "Compensation updated successfully";
            } else {
              $errors[] = "Failed to update allowances";
            }
          } else {
            // Record doesn't exist - INSERT
            $allowanceData['employeeID'] = $userID;
            $allowanceData['isCurrent'] = 'Y';
            $allowanceData['effectiveDate'] = date('Y-m-d');
            $allowanceData['createdBy'] = $userDetails->ID;
            $insertResult = $DBConn->insert_data('tija_employee_allowances', $allowanceData);
            if($insertResult) {
              $success = "Allowances added successfully";
            } else {
              $errors[] = "Failed to insert allowances";
            }
          }
        }

        // check and update the users unit type assignments
        if(count($unitTypes) > 0) {
          foreach($unitTypes as $typeID => $unitID) {
            $unitAssignment = array(
              'userID' => $userID,
              'unitTypeID' => $typeID,
              'unitID' => $unitID,
              'LastUpdatedByID' => $userDetails->ID,
              'orgDataID' => $organisationID,
              'entityID' => $entityID,
            );
            // check if the assignment to the unitType for the user exists
            $assignment = Data::unit_user_assignments(array("userID"=>$userID, "unitTypeID"=>$typeID), true, $DBConn);
            var_dump($assignment);
            if($assignment) {
              // Check if the unitID assignment has changed
              if($assignment->unitID != $unitID) {
                $unitAssignment['assignmentStartDate'] = $dateOfEmployment;
                $unitAssignment['assignmentEndDate'] = $dateOfTermination;
                $update = $DBConn->update_table('tija_user_unit_assignments', $unitAssignment, array('userID'=>$userID, 'unitTypeID'=>$typeID));
                if(!$update) {
                  $errors[] = "There was an error updating the unit type assignments for {$unitID} of type {$typeID} for user {$userID}";
                } else {
                  $success .= "User assignment details have been updated successfully";
                }
              }

            } else {
              // add the ubit type assignment to the database
              $unitAssignment['assignmentStartDate'] = $dateOfEmployment;
              $unitAssignment['assignmentEndDate'] = $dateOfTermination;
              var_dump($unitAssignment);
              $insert = $DBConn->insert_data('tija_user_unit_assignments', $unitAssignment);
              if(!$insert) {
                $errors[] = "There was an error adding the unit type assignments for {$unitID} of type {$typeID} for user {$userID}";
              } else {
                $success.= "User assignment details have been saved successfully";
              }
            }
          }
        }
      } // End of if($employeeDetails && count($errors) == 0)
        // check and update the users details based on user_details table
        // $userDetails = Core::user_details(array("userID"=>$userID), true, $DBConn);
    }

    var_dump($errors);

    echo "<h4> Success </h4>";
    var_dump($success);

  // Check if redirectUrl is provided (for inline edit), otherwise use session returnURL
  if (isset($_POST['redirectUrl']) && !empty($_POST['redirectUrl'])) {
      $returnURL = $_POST['redirectUrl'];
  } else {
      $returnURL = Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
  }
	var_dump($returnURL);


} else {
	$errors[] = 'You need to log in as a valid administrator to do that.';
}

if (count($errors) == 0) {
	$DBConn->commit();
	$messages = array(array('Text'=>"{$success}", 'Type'=>'success'));
} else {
 	$DBConn->rollback();
 	$messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
}
 $_SESSION['FlashMessages'] = serialize($messages);
 header("location:{$base}html/{$returnURL}");