<?php
session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';


// Check admin permissions
if (!$isValidUser || (!$isAdmin && !$isValidAdmin && !$isHRManager)) {
    http_response_code(403);
    Alert::error("Access denied. Admin or HR Manager privileges required.", true);
    header('Location: ' . $base . 'html/');
    exit;
}

$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
$success = "";
if ($isValidUser) {

   var_dump($_POST);
   $leaveTypeID = isset($_POST['leaveTypeID']) ? (int) $_POST['leaveTypeID'] : 0;
   $entitlement = isset($_POST['entitlement']) ? (float) $_POST['entitlement'] : null;
   $maxDaysPerApplication = (isset($_POST['maxDaysPerApplication']) && $_POST['maxDaysPerApplication'] !== '')
      ? (int) $_POST['maxDaysPerApplication']
      : null;
   $minNoticeDays = (isset($_POST['minNoticeDays']) && $_POST['minNoticeDays'] !== '')
      ? (int) $_POST['minNoticeDays']
      : null;
   $entityID = isset($_POST['entityID']) ? (int) $_POST['entityID'] : 0;
   $leaveEntitlementID = isset($_POST['leaveEntitlementID']) ? (int) $_POST['leaveEntitlementID'] : 0;

   if(!$leaveEntitlementID) {
      $leaveTypeID > 0 ? $details['leaveTypeID'] = $leaveTypeID : $errors[] = "Please submit valid leave type ID";
      $entitlement !== null ? $details['entitlement'] = $entitlement : $errors[] = "Please submit valid leave entitlement";
      $maxDaysPerApplication !== null ? $details['maxDaysPerApplication'] = $maxDaysPerApplication : null;
      $minNoticeDays !== null ? $details['minNoticeDays'] = $minNoticeDays : null;
      $entityID > 0 ? $details['entityID'] = $entityID : $errors[] = "Please submit valid entity ID";
      if (count($errors) === 0) {
         if ($details) {
            $details['LastUpdate'] = $config['currentDateTimeFormated'];
            $details['LastUpdateByID'] = $userDetails->ID;
            if (!$DBConn->insert_data("tija_leave_entitlement", $details)) {
               $errors[]= "ERROR adding new leave entitlement to the database";
            } else {
               $success = "Leave Entitlement added successfully";
            }
         }
      }
   } else {
      $leaveEntitlementDetails = Leave::leave_entitlement(array("leaveEntitlementID"=> $leaveEntitlementID), true, $DBConn);
      if (!$leaveEntitlementDetails) {
         $errors[] = 'Leave entitlement record not found.';
      } else {
         $leaveTypeID > 0 && ($leaveTypeID !== (int)$leaveEntitlementDetails->leaveTypeID) ? $changes['leaveTypeID'] = $leaveTypeID : null;
         $entitlement !== null && ($entitlement != $leaveEntitlementDetails->entitlement) ? $changes['entitlement'] = $entitlement : null;
         $maxDaysPerApplication !== null && ($maxDaysPerApplication != ($leaveEntitlementDetails->maxDaysPerApplication ?? null))
            ? $changes['maxDaysPerApplication'] = $maxDaysPerApplication
            : null;
         $minNoticeDays !== null && ($minNoticeDays != ($leaveEntitlementDetails->minNoticeDays ?? null))
            ? $changes['minNoticeDays'] = $minNoticeDays
            : null;
         $entityID > 0 && ($entityID !== (int)$leaveEntitlementDetails->entityID) ? $changes['entityID'] = $entityID : null;
      }
      if (count($errors) === 0) {
         if ($changes) {
            $changes['LastUpdate'] = $config['currentDateTimeFormated'];
            $changes['LastUpdateByID'] = $userDetails->ID;
            var_dump($changes);
            if (!$DBConn->update_table("tija_leave_entitlement", $changes, array("leaveEntitlementID"=>$leaveEntitlementID))) {
               $errors[]= "ERROR updating leave entitlement details in the database";
            } else {
               $success = "Leave Entitlement updated successfully";
            }
         } else {
            $success = "Leave Entitlement is already up to date";
         }
      }
   }
} else {
	$errors[] = 'You need to log in as a valid user to manage leave entitlements.';
}

$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=leave&p=leave_entitlements');
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