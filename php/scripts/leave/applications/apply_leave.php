<?php
session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
$success = "";
if ($isValidUser) {
	var_dump($_POST);
   var_dump($_FILES);

   $leaveTypeID = (isset($_POST['leaveTypeID']) && !empty($_POST['leaveTypeID'])) ? Utility::clean_string($_POST['leaveTypeID']) : "";
   $leavePeriodID = (isset($_POST['leavePeriodID']) && !empty($_POST['leavePeriodID'])) ? Utility::clean_string($_POST['leavePeriodID']) : "";
   $startDate = (isset($_POST['startDate']) && !empty($_POST['startDate'])) ? Utility::clean_string($_POST['startDate']) : "";
   $endDate = (isset($_POST['endDate']) && !empty($_POST['endDate'])) ? Utility::clean_string($_POST['endDate']) : "";
   $leaveStatusID = (isset($_POST['leaveStatusID']) && !empty($_POST['leaveStatusID'])) ? Utility::clean_string($_POST['leaveStatusID']) : "1";
   $leaveComments = (isset($_POST['leaveComments']) && !empty($_POST['leaveComments'])) ? Utility::clean_string($_POST['leaveComments']) : "";
   $leaveApplicationID = (isset($_POST['leaveApplicationID']) && !empty($_POST['leaveApplicationID'])) ? Utility::clean_string($_POST['leaveApplicationID']) : "";
   $employeeID = (isset($_POST['employeeID']) && !empty($_POST['employeeID'])) ? Utility::clean_string($_POST['employeeID']) : $userDetails->ID;
   $leaveStatusID = (isset($_POST['leaveStatusID']) && !empty($_POST['leaveStatusID'])) ? Utility::clean_string($_POST['leaveStatusID']) : "1";
//   $leaveEntitlementID = (isset($_POST['leaveEntitlementID']) && !empty($_POST['leaveEntitlementID'])) ? Utility::clean_string($_POST['leaveEntitlementID']) : "";
  $entityID= (isset($_POST['entityID']) && !empty($_POST['entityID'])) ? Utility::clean_string($_POST['entityID']) : "";
  $orgDataID= (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ? Utility::clean_string($_POST['orgDataID']) : "";

   $leaveEntitlement = Leave::leave_entitlement(array("entityID"=> $entityID, "leaveTypeID"=>$leaveTypeID), true, $DBConn);
   echo "<h5> Leave entitlement</h5>";
   var_dump($leaveEntitlement);
   $leaveEntitlementID = "";
   if($leaveEntitlement) {
      $leaveEntitlementID = $leaveEntitlement->leaveEntitlementID;
   }

   // $numberOfDays = Leave::calculateLeaveDays($startDate, $endDate, $leaveEntitlement->entitlement, $DBConn);
   $noOfLeaveDays = Leave::countWeekdays($startDate, $endDate);
   $details['noOfDays'] = $noOfLeaveDays;




   
      // Split each file into individual variables
      $fileVariables = [];
      if($_FILES && count($_FILES)>0){
         foreach ($_FILES as $key => $file) {
            if (is_array($file['name'])) {
                foreach ($file['name'] as $index => $name) {
                    $fileVariables[$key][$index] = [
                        'name' => $file['name'][$index],
                        'type' => $file['type'][$index],
                        'tmp_name' => $file['tmp_name'][$index],
                        'error' => $file['error'][$index],
                        'size' => $file['size'][$index],
                    ];
                }
            } else {
                $fileVariables[$key] = $file;
            }
        }

      }
     echo "<h5> File Variables</h5>";
      var_dump($fileVariables); 
      $uploadedFilePaths = [];
      IF($fileVariables) {  
         foreach ($fileVariables as $key => $files) {
            if (is_array($files)) {
               var_dump($files);
               foreach ($files as $index => $file) {
                  var_dump($file);
                     // Check if the file is uploaded
                     if (!isset($file['name']) || empty($file['name'])) {                       
                        continue;
                     }
                     $uploadDir = $base . 'uploads/leave/' . $employeeID . '/';
                     if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                     }
                     $fileName = $file['name'];
                     $tempName = $file['tmp_name'];
                     $fileType = $file['type'];
                     $fileSize = $file['size'];
                     $fileError = $file['error'];

                     // Check if there's an error with the file
                     if ($fileError) {
                        $errors[] = "Error uploading file: " . $fileError;
                        continue;
                     }

                     // Check file size
                     if ($fileSize > 1024 * 1024 * 5) { // 5MB
                        $errors[] = "File size exceeds the maximum limit of 5MB.";
                        continue;
                     }

                     // Check file type
                     $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'];
                     if (!in_array($fileType, $allowedTypes)) {
                        $errors[] = "File type not allowed.";
                        continue;
                     }

                     // File upload logic

                     var_dump($fileName);
                     $fileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fileName); // Sanitize file name
                     $fileName = time() . '_' . $fileName; // Add timestamp to avoid overwriting
                     $newFileName = $uploadDir . $fileName;
                     if (move_uploaded_file($tempName, $newFileName)) {
                        $newFileName = str_replace($base . 'uploads/', '', $newFileName);

                        // var_dump($newFileName);
                        $uploadedFilePaths[] = $newFileName; // Store the file path in the array
                        $success = "File uploaded successfully.";
                     } else {
                        $errors[] = "Failed to upload file.";
                     }
               }
            }
         }
      }
      var_dump($uploadedFilePaths);
      $leaveFiles = implode(',', $uploadedFilePaths);

      $leaveFilesStr= base64_encode($leaveFiles);

   if(!$leaveApplicationID) {
      $leaveTypeID ? $details['leaveTypeID'] = Utility::clean_string($leaveTypeID) : $errors[] = "Please submit valid leave type";
      $leavePeriodID ? $details['leavePeriodID'] = Utility::clean_string($leavePeriodID) : $errors[] = "Please submit valid leave period";
      $startDate ? $details['startDate'] = Utility::clean_string($startDate) : $errors[] = "Please submit valid start date";
      $endDate ? $details['endDate'] = Utility::clean_string($endDate) : $errors[] = "Please submit valid end date";
      $leaveStatusID ? $details['leaveStatusID'] = Utility::clean_string($leaveStatusID) : $errors[] = "Please submit valid leave status";
      $employeeID ? $details['employeeID'] = Utility::clean_string($employeeID) : $errors[] = "Please submit valid employee ID";
      $leaveComments ? $details['leaveComments'] = Utility::clean_string($leaveComments) : $errors[] = "Please submit valid leave comments";
      $leaveFilesStr ? $details['leaveFiles'] = Utility::clean_string($leaveFilesStr) : "";
      $leaveEntitlementID ? $details['leaveEntitlementID'] = Utility::clean_string($leaveEntitlementID) : $errors[] = "Please submit valid leave entitlement ID";
      $entityID ? $details['entityID'] = Utility::clean_string($entityID) : $errors[] = "Please submit valid entity ID";
      $orgDataID ? $details['orgDataID'] = Utility::clean_string($orgDataID) : $errors[] = "Please submit valid org data ID";


      var_dump($details);
      if (count($errors) === 0) {
         $details['LastUpdate'] = $config['currentDateTimeFormated'];
         $details['LastUpdateByID'] = $userDetails->ID;
         $details['DateAdded'] = $config['currentDateTimeFormated'];
         if ($details) {
            if (!$DBConn->insert_data("tija_leave_applications", $details)) {
               $errors[]= "ERROR adding new leave application to the database";
            } else {
               $success = "Leave application added successfully";
            }
         }
      }

   } else {
      $leaveApplicationDetails = Leave::leave_applications(array("leaveApplicationID"=> $leaveApplicationID), true, $DBConn);

      var_dump($leaveApplicationDetails);
      $leaveTypeID && ($leaveTypeID !== $leaveApplicationDetails->leaveTypeID) ? $changes['leaveTypeID'] = $leaveTypeID : "";
      $leavePeriodID && ($leavePeriodID !== $leaveApplicationDetails->leavePeriodID) ? $changes['leavePeriodID'] = $leavePeriodID : "";
      $startDate && ($startDate !== $leaveApplicationDetails->startDate) ? $changes['startDate'] = $startDate : "";
      $endDate && ($endDate !== $leaveApplicationDetails->endDate) ? $changes['endDate'] = $endDate : "";
      $leaveStatusID && ($leaveStatusID !== $leaveApplicationDetails->leaveStatusID) ? $changes['leaveStatusID'] = $leaveStatusID : "";
      $leaveFiles && ($leaveFiles !== $leaveApplicationDetails->leaveFiles) ? $changes['leaveFiles'] = $leaveFiles : "";
      var_dump($changes);
      if (count($errors) === 0) {
         if ($changes) {
            $changes['LastUpdate'] = $config['currentDateTimeFormated'];
            $changes['LastUpdateByID'] = $userDetails->ID;
            if (!$DBConn->update_table("tija_leave_applications", $changes, array("leaveApplicationID"=>$leaveApplicationID))) {
               $errors[]= "ERROR updating leave application details in the database";
            } else {
               $success = "Leave application updated successfully";
            }
         }
      }
   }



   

} else {
	$errors[] = 'You need to log in as a valid user to add new sales case.';
}

var_dump($errors);
$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
var_dump($returnURL);
 if (count($errors) == 0) {
	 $DBConn->commit();
	 $messages = array(array('Text'=>$success, 'Type'=>'success'));
 } else {
	 $DBConn->rollback();
	 $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
 }
 $_SESSION['FlashMessages'] = serialize($messages);
 header("location:{$base}html/{$returnURL}");
?>