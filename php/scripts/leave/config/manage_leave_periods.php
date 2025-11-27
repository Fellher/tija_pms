<?php
session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';


function leavePeriodsOverlap($startA, $endA, $startB, $endB) {
   return (strtotime($startA) <= strtotime($endB)) && (strtotime($startB) <= strtotime($endA));
}

// Check admin permissions
if (!$isValidUser || (!$isAdmin && !$isValidAdmin && !$isHRManager)) {
    http_response_code(403);
    Alert::error("Access denied. Admin or HR Manager privileges required.", true);
    header('Location: ' . $base . 'html/');
    exit;
}

$DBConn->begin();
$errors = array();
$details = array();
$changes = array();
$success = "";
if ($isValidUser) {

   $leavePeriodID = (isset($_POST['leavePeriodID']) && !empty($_POST['leavePeriodID'])) ? Utility::clean_string($_POST['leavePeriodID']) : "";
   $leavePeriodName = (isset($_POST['leavePeriodName']) && !empty($_POST['leavePeriodName'])) ? Utility::clean_string($_POST['leavePeriodName']) : "";
   $leavePeriodStartDate = (isset($_POST['leavePeriodStartDate']) && !empty($_POST['leavePeriodStartDate']) && (preg_match($config['ISODateFormat'], Utility::clean_string($_POST['leavePeriodStartDate'])))) ? Utility::clean_string($_POST['leavePeriodStartDate']) : "";
   $leavePeriodEndDate = (isset($_POST['leavePeriodEndDate']) && !empty($_POST['leavePeriodEndDate']) && (preg_match($config['ISODateFormat'], Utility::clean_string($_POST['leavePeriodEndDate'])))) ? Utility::clean_string($_POST['leavePeriodEndDate']) : "";
   $entityID = (isset($_POST['entityID']) && $_POST['entityID'] !== '') ? (int) Utility::clean_string($_POST['entityID']) : 0;

   if (!$entityID) {
      $errors[] = "Please select a valid organisation / entity.";
   }

   if (!empty($leavePeriodStartDate) && !empty($leavePeriodEndDate)) {
      if (strtotime($leavePeriodStartDate) > strtotime($leavePeriodEndDate)) {
         $errors[] = "Leave Period Start Date cannot be greater than Leave Period End Date";
      } else {
         // Validate 12-month period
         $start = new DateTime($leavePeriodStartDate);
         $end = new DateTime($leavePeriodEndDate);
         $interval = $start->diff($end);
         $daysDiff = $interval->days;

         // Check if period is approximately 12 months (364-366 days to account for leap years)
         if ($daysDiff < 364 || $daysDiff > 366) {
            $months = round($daysDiff / 30.44, 1);
            $errors[] = "Leave period must be exactly 12 months (365-366 days). Current period is {$daysDiff} days ({$months} months).";
         }
      }
   }

   if (count($errors) === 0 && $entityID && $leavePeriodStartDate && $leavePeriodEndDate) {
      $existingPeriods = Leave::leave_Periods(array('entityID' => $entityID, 'Suspended' => 'N'), false, $DBConn);
      if ($existingPeriods) {
         foreach ($existingPeriods as $existing) {
            if ($leavePeriodID && $existing->leavePeriodID == $leavePeriodID) {
               continue;
            }
            if (leavePeriodsOverlap($leavePeriodStartDate, $leavePeriodEndDate, $existing->leavePeriodStartDate, $existing->leavePeriodEndDate)) {
               $errors[] = "The selected dates overlap with an existing leave period ({$existing->leavePeriodName}).";
               break;
            }
         }
      }
   }

   if (!$leavePeriodID) {
      $leavePeriodStartDate ? $details['leavePeriodStartDate'] = $leavePeriodStartDate : $errors[] = "Please submit valid leave period Start Date";
      $leavePeriodEndDate ? $details['leavePeriodEndDate'] = $leavePeriodEndDate : $errors[] = "Please submit valid leave period End Date";
      $leavePeriodName ? $details['leavePeriodName'] = $leavePeriodName : $errors[] = "Please submit valid leave period Name";
      if ($entityID) {
         $details['entityID'] = $entityID;
      }
      if (isset($userDetails->orgDataID)) {
         $details['orgDataID'] = $userDetails->orgDataID;
      }
      $details['Suspended'] = 'N';
      $details['Lapsed'] = 'N';
      $details['DateAdded'] = $config['currentDateTimeFormated'];
      $details['LastUpdate'] = $config['currentDateTimeFormated'];
      if (isset($userDetails->ID)) {
         $details['LastUpdateByID'] = $userDetails->ID;
      }

      if (count($errors) === 0) {
         if ($details) {
            if (!$DBConn->insert_data("tija_leave_periods", $details)) {
               $errors[] = "ERROR adding new leave period to the database";
            } else {
               $success = "Leave Period added successfully";
            }
         }
      }
   } else {
      $leavePeriodDetails = Leave::leave_Periods(array("leavePeriodID" => $leavePeriodID), true, $DBConn);
      if (!$leavePeriodDetails) {
         $errors[] = "Unable to locate the leave period you are trying to update.";
      } else {
         if ($leavePeriodStartDate && ($leavePeriodStartDate !== $leavePeriodDetails->leavePeriodStartDate)) {
            $changes['leavePeriodStartDate'] = $leavePeriodStartDate;
         }
         if ($leavePeriodEndDate && ($leavePeriodEndDate !== $leavePeriodDetails->leavePeriodEndDate)) {
            $changes['leavePeriodEndDate'] = $leavePeriodEndDate;
         }
         if ($leavePeriodName && ($leavePeriodName !== $leavePeriodDetails->leavePeriodName)) {
            $changes['leavePeriodName'] = $leavePeriodName;
         }
         if ($entityID && ($entityID !== (int) $leavePeriodDetails->entityID)) {
            $changes['entityID'] = $entityID;
         }
      }

      if (count($errors) === 0) {
         if ($changes) {
            $changes['LastUpdate'] = $config['currentDateTimeFormated'];
            if (isset($userDetails->ID)) {
               $changes['LastUpdateByID'] = $userDetails->ID;
            }
            if (!$DBConn->update_table("tija_leave_periods", $changes, array("leavePeriodID" => $leavePeriodID))) {
               $errors[] = "ERROR updating leave period details in the database";
            } else {
               $success = "Leave Period updated successfully";
            }
         }
      }
   }
} else {
   $errors[] = 'You need to log in as a valid user to manage leave periods.';
}

$returnURL = Utility::returnURL($_SESSION['returnURL'] ?? '', 's=admin&ss=leave&p=leave_periods');
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