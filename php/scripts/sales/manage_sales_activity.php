
<?php
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
$success="";
if ($isValidUser) {
	var_dump($_POST);


   $activityTypeID = (isset($_POST['activityTypeID']) && !empty($_POST['activityTypeID'])) ? Utility::clean_string($_POST['activityTypeID']) : '';
   $salesActivityDate = (isset($_POST['salesActivityDate']) && !empty($_POST['salesActivityDate'])) ? Utility::clean_string($_POST['salesActivityDate']) : '';
   $activityTime = (isset($_POST['activityTime']) && !empty($_POST['activityTime'])) ? Utility::clean_string($_POST['activityTime']) : '';
   $activityDescription = (isset($_POST['activityDescription']) && !empty($_POST['activityDescription'])) ? Utility::clean_string($_POST['activityDescription']) : '';
   $salesCaseID = (isset($_POST['salesCaseID']) && !empty($_POST['salesCaseID'])) ? Utility::clean_string($_POST['salesCaseID']) : '';
   $orgDataID = (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ? Utility::clean_string($_POST['orgDataID']) : '';
   $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ? Utility::clean_string($_POST['entityID']) : '';
   $activityName = (isset($_POST['activityName']) && !empty($_POST['activityName'])) ? Utility::clean_string($_POST['activityName']) : '';
   $clientID = (isset($_POST['clientID']) && !empty($_POST['clientID'])) ? Utility::clean_string($_POST['clientID']) : '';
   $activityOwnerID = (isset($_POST['activityOwner']) && !empty($_POST['activityOwner'])) ? Utility::clean_string($_POST['activityOwner']) : '';
   $salesCaseName = (isset($_POST['salesCaseName']) && !empty($_POST['salesCaseName'])) ? Utility::clean_string($_POST['salesCaseName']) : '';
   $salesPersonID = (isset($_POST['salesPersonID']) && !empty($_POST['salesPersonID'])) ? Utility::clean_string($_POST['salesPersonID']) : '';
   $salesActivityID = (isset($_POST['salesActivityID']) && !empty($_POST['salesActivityID'])) ? Utility::clean_string($_POST['salesActivityID']) : '';
   $activityCategory =(isset($_POST['activityCategory']) && !empty($_POST['activityCategory'])) ? Utility::clean_string($_POST['activityCategory']) : '';
   $activityStatus = (isset($_POST['activityStatus']) && !empty($_POST['activityStatus'])) ? Utility::clean_string($_POST['activityStatus']) : '';
   $activityDeadline = (isset($_POST['activityDeadline']) && !empty($_POST['activityDeadline'])) ? Utility::clean_string($_POST['activityDeadline']) : '';
   $activityStartDate = (isset($_POST['activityStartDate']) && !empty($_POST['activityStartDate'])) ? Utility::clean_string($_POST['activityStartDate']) : '';
   $activityCloseDate = (isset($_POST['activityCloseDate']) && !empty($_POST['activityCloseDate'])) ? Utility::clean_string($_POST['activityCloseDate']) : '';
   $activityCloseStatus = (isset($_POST['activityCloseStatus']) && !empty($_POST['activityCloseStatus'])) ? Utility::clean_string($_POST['activityCloseStatus']) : ($activityStatus ? $activityStatus : "");
   $activityNotes = (isset($_POST['activityNotes']) && !empty($_POST['activityNotes'])) ? Utility::clean_string($_POST['activityNotes']) : '';

   if(!$salesActivityID) {
     echo "<h4> sales case id is {$salesCaseID} </h4>";
      $salesCaseID ? $details['salesCaseID']= $salesCaseID : $errors[] = 'You need to select a valid sales case to add an activity to.';

      $orgDataID ? $details['orgDataID']=$orgDataID : $errors[] = 'please select a valid organization to add an activity to.';
      $entityID ? $details['entityID'] = $entityID : $errors[] = 'You need to select a valid entity to add an activity to.';
      $clientID ? $details['clientID'] = $clientID : $errors[] = 'You need to select a valid client to add an activity to.';
      $salesPersonID ? $details['salesPersonID'] = $salesPersonID : $errors[] = 'You need to select a valid sales person to add an activity to.';
      $activityName ? $details['activityName']= $activityName : $errors[] = 'You need to enter a valid activity name to add an activity to.';
      $activityTypeID ? $details['activityTypeID'] =$activityTypeID : $errors[] = 'You need to select a valid activity type to add an activity to.';
      $salesActivityDate ? $details['salesActivityDate'] = $salesActivityDate : $errors[] = 'You need to select a valid activity date to add an activity to.';
      $activityTime ? $details['activityTime'] = $activityTime : $errors[] = 'You need to select a valid activity time to add an activity to.';
      $activityDescription ? $details['activityDescription']= $activityDescription : $errors[] = 'You need to enter a valid activity description to add an activity to.';
      $activityOwnerID ? $details['activityOwnerID'] = $activityOwnerID : $errors[] = 'You need to select a valid activity owner to add an activity to.';
      $activityCategory ? $details['activityCategory'] = $activityCategory : '';
      $activityStatus ? $details['activityStatus'] = $activityStatus : "";
      $activityDeadline ? $details['activityDeadline'] = $activityDeadline :"";
      $activityStartDate ? $details['activityStartDate'] = $activityStartDate : "";
      $activityCloseDate ? $details['activityCloseDate'] = $activityCloseDate : "";
      $activityCloseStatus ? $details['activityCloseStatus'] = $activityCloseStatus : "";
      $activityNotes ? $details['activityNotes'] = $activityNotes : "";


      var_dump($details);
      if(count($errors) == 0) {
        $details['LastUpdatedByID'] = $userDetails->ID;
        $details['LastUpdate'] = $config['currentDateTimeFormated'];
         if(!$DBConn->insert_data('tija_sales_activities', $details)) {
            $errors[] = 'There was an error adding the activity to the sales case. Please try again.';
         } else {
            $success = 'The activity was successfully added to the sales case.';
            $salesActivityID = $DBConn->lastInsertId();
         }
      }
   } else {
      $salesActivityDetails = Sales::tija_sales_activities(array('salesActivityID'=>$salesActivityID), true, $DBConn);
      var_dump($salesActivityDetails);
      $salesCaseID  && $salesActivityDetails->salesCaseID != $salesCaseID ? $changes['salesCaseID'] = $salesCaseID : '';
      $orgDataID && $salesActivityDetails->orgDataID != $orgDataID ? $changes['orgDataID'] = $orgDataID : '';
      $entityID && $salesActivityDetails->entityID != $entityID ? $changes['entityID'] = $entityID : '';
      $salesPersonID && $salesActivityDetails->salesPersonID != $salesPersonID ? $changes['salesPersonID'] = $salesPersonID : '';
      $activityName && $salesActivityDetails->activityName != $activityName ? $changes['activityName'] = $activityName : '';
      $clientID && $salesActivityDetails->clientID != $clientID ? $changes['clientID'] = $clientID : '';
      $activityTypeID && $salesActivityDetails->activityTypeID != $activityTypeID ? $changes['activityTypeID'] = $activityTypeID : '';
      $salesActivityDate && $salesActivityDetails->salesActivityDate != $salesActivityDate ? $changes['salesActivityDate'] = $salesActivityDate : '';
      $activityTime && $salesActivityDetails->activityTime != $activityTime ? $changes['activityTime'] = $activityTime : '';
      $activityDescription && $salesActivityDetails->activityDescription != $activityDescription ? $changes['activityDescription'] = $activityDescription : '';
      $activityOwnerID && $salesActivityDetails->activityOwnerID != $activityOwnerID ? $changes['activityOwnerID'] = $activityOwnerID : '';
      $activityCategory && $salesActivityDetails->activityCategory != $activityCategory ? $changes['activityCategory'] = $activityCategory : '';
      $activityStatus && $salesActivityDetails->activityStatus != $activityStatus ? $changes['activityStatus'] = $activityStatus : '';
      $activityDeadline && $salesActivityDetails->activityDeadline != $activityDeadline ? $changes['activityDeadline'] = $activityDeadline : '';
      $activityStartDate && $salesActivityDetails->activityStartDate != $activityStartDate ? $changes['activityStartDate'] = $activityStartDate : '';
      $activityCloseDate && $salesActivityDetails->activityCloseDate != $activityCloseDate ? $changes['activityCloseDate'] = $activityCloseDate : '';
      $activityCloseStatus && $salesActivityDetails->activityCloseStatus != $activityCloseStatus ? $changes['activityCloseStatus'] = $activityCloseStatus : '';
      $activityNotes && $salesActivityDetails->activityNotes != $activityNotes ? $changes['activityNotes'] = $activityNotes : '';

      if(!$errors){
         if($changes){
            $changes['LastUpdatedByID'] = $userDetails->ID;
            $changes['LastUpdate'] = $config['currentDateTimeFormated'];
            if(!$DBConn->update_table('tija_sales_activities', $changes, ['salesActivityID'=>$salesActivityID])) {
               $errors[] = 'There was an error updating the activity. Please try again.';
            } else {
               $success = 'The activity was successfully updated.';
            }
         }
      }
   }

   $returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
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
//  header("location:{$base}html/{$returnURL}");