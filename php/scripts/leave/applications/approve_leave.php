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
   $leaveApprovalID = (isset($_POST['leaveApprovalID']) && is_numeric($_POST['leaveApprovalID'])) ? Utility::clean_string($_POST['leaveApprovalID']) : null;
   $leaveApplicationID = (isset($_POST['leaveApplicationID']) && is_numeric($_POST['leaveApplicationID'])) ? Utility::clean_string($_POST['leaveApplicationID']) : null;
   $employeeID = (isset($_POST['employeeID']) && is_numeric($_POST['employeeID'])) ? Utility::clean_string($_POST['employeeID']) : null;
   $leaveTypeID = (isset($_POST['leaveTypeID']) && is_numeric($_POST['leaveTypeID'])) ? Utility::clean_string($_POST['leaveTypeID']) : null;
   $leavePeriodID = (isset($_POST['leavePeriodID']) && is_numeric($_POST['leavePeriodID'])) ? Utility::clean_string($_POST['leavePeriodID']) : null;
   $leaveApproverID = (isset($_POST['leaveApproverID']) && is_numeric($_POST['leaveApproverID'])) ? Utility::clean_string($_POST['leaveApproverID']) : null;
   $leaveStatusID = (isset($_POST['leaveStatusID']) && is_numeric($_POST['leaveStatusID'])) ? Utility::clean_string($_POST['leaveStatusID']) : null;
   $leaveDate = (isset($_POST['leaveDate']) && !empty($_POST['leaveDate'])) ? Utility::clean_string($_POST['leaveDate']) : null;
   $leaveStatus = (isset($_POST['leaveStatus']) && !empty($_POST['leaveStatus'])) ? Utility::clean_string($_POST['leaveStatus']) : null;
   $approversComments = (isset($_POST['approversComments']) && !empty($_POST['approversComments'])) ? Utility::clean_string($_POST['approversComments']) : null;

   if(!$leaveApprovalID){
      $leaveApplicationID  ? $details['leaveApplicationID'] = $leaveApplicationID : $errors[] = 'Leave Application ID is required.';
      $employeeID ? $details['employeeID'] = $employeeID : $errors[] = 'Employee ID is required.';
      $leaveTypeID ? $details['leaveTypeID'] = $leaveTypeID : $errors[] = 'Leave Type ID is required.';
      $leavePeriodID ? $details['leavePeriodID'] = $leavePeriodID : $errors[] = 'Leave Period ID is required.';
      $leaveApproverID ? $details['leaveApproverID'] = $leaveApproverID : $errors[] = 'Leave Approver ID is required.';
      $leaveDate ? $details['leaveDate'] = $leaveDate : $errors[] = 'Leave Date is required.';
      $leaveStatusID ? $details['leaveStatusID'] = $leaveStatusID : $errors[] = 'Leave Status ID is required.';
      $leaveStatus ? $details['leaveStatus'] = $leaveStatus : $errors[] = 'Leave Status is required.';
      $approversComments ? $details['approversComments'] = $approversComments : "";
      if(!$errors) {
         $details['LastUpdateByID'] = $userDetails->ID;
         $details['LastUpdate'] = date('Y-m-d H:i:s');
         if(!$DBConn->insert_data('tija_leave_approvals', $details)) {
            $errors[] = 'Error while adding new leave approval.';
         } else {
            $leaveApprovalID = $DBConn->lastInsertId();
            $success = 'Leave approval added successfully.';
         }
      }

   } else {
      $leaveApprovalDetails = Leave::leave_approvals(['leaveApprovalID' => $leaveApprovalID], true, $DBConn);
      var_dump($leaveApprovalDetails);

      $leaveApplicationID && $leaveApplicationID != $leaveApprovalDetails['leaveApplicationID'] ? $changes['leaveApplicationID'] = $leaveApplicationID : null;
      $employeeID && $employeeID != $leaveApprovalDetails['employeeID'] ? $changes['employeeID'] = $employeeID : null;
      $leaveTypeID && $leaveTypeID != $leaveApprovalDetails['leaveTypeID'] ? $changes['leaveTypeID'] = $leaveTypeID : null;
      $leavePeriodID && $leavePeriodID != $leaveApprovalDetails['leavePeriodID'] ? $changes['leavePeriodID'] = $leavePeriodID : null;
      $leaveApproverID && $leaveApproverID != $leaveApprovalDetails['leaveApproverID'] ? $changes['leaveApproverID'] = $leaveApproverID : null;
      $leaveDate && $leaveDate != $leaveApprovalDetails['leaveDate'] ? $changes['leaveDate'] = $leaveDate : null;
      $leaveStatusID && $leaveStatusID != $leaveApprovalDetails['leaveStatusID'] ? $changes['leaveStatusID'] = $leaveStatusID : null;
      $leaveStatus && $leaveStatus != $leaveApprovalDetails['leaveStatus'] ? $changes['leaveStatus'] = $leaveStatus : null;
      $approversComments && $approversComments != $leaveApprovalDetails['approversComments'] ? $changes['approversComments'] = $approversComments : null;
      if(!$errors && count($changes) > 0) {
         $changes['LastUpdateByID'] = $userDetails->ID;
         $changes['LastUpdate'] = date('Y-m-d H:i:s');
         if(!$DBConn->update_table('tija_leave_approvals', $changes, ['leaveApprovalID' => $leaveApprovalID])) {
            $errors[] = 'Error while updating leave approval.';
         } else {
            $success = 'Leave approval updated successfully.';
         }
      } else {
         $errors[] = 'No changes made to the leave approval.';
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