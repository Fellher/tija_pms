
<?php
session_start();
$base = '../../../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$categoryDetils=array();
$details= array();
$changes= array();
$success="";
if ($isValidAdmin || $isAdmin) {
  var_dump($_POST);
  $positionAssignmentID = (isset($_POST['positionAssignmentID']) && !empty($_POST['positionAssignmentID'])) ? Utility::clean_string($_POST['positionAssignmentID']) : null;
  $orgChartID = (isset($_POST['orgChartID']) && !empty($_POST['orgChartID'])) ? Utility::clean_string($_POST['orgChartID']) : null;
  $positionID = (isset($_POST['positionID']) && !empty($_POST['positionID'])) ? Utility::clean_string($_POST['positionID']) : null;
  $positionParentID = (isset($_POST['positionParentID']) && !empty($_POST['positionParentID'])) ? Utility::clean_string($_POST['positionParentID']) : '0';
  $orgDataID = (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ? Utility::clean_string($_POST['orgDataID']) : null;
  $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ? Utility::clean_string($_POST['entityID']) : null;

  if(!$positionAssignmentID) {
    $positionAssignmentDetails = array();
    $positionID ? $positionAssignmentDetails['positionID'] = $positionID : $errors[] = "Job Position is required";
    $positionParentID ? $positionAssignmentDetails['positionParentID'] = $positionParentID : $positionAssignmentDetails['positionParentID'] = 0;
    $orgChartID ? $positionAssignmentDetails['orgChartID'] = $orgChartID : $errors[] = "Organisation Chart is required";
    $orgDataID ? $positionAssignmentDetails['orgDataID'] = $orgDataID : $errors[] = "Organisation Data is required";
    $entityID ? $positionAssignmentDetails['entityID'] = $entityID : $errors[] = "Organisation Entity is required";
    $positionDetails = Admin::tija_job_titles(array('jobTitleID'=>$positionID), true, $DBConn);
    var_dump($positionDetails);
    if($positionDetails) {
      $positionAssignmentDetails['positionTitle'] = $positionDetails->jobTitle;
      $positionAssignmentDetails['positionDescription'] = $positionDetails->jobDescription;
    
    } else {
      $errors[] = "Job Position not found";
    }
    if(count($errors) === 0){
      $positionAssignmentDetails['LastUpdate'] = $config['currentDateTimeFormated'];
      $positionAssignmentDetails['LastUpdatedByID'] = $userDetails->ID;
      echo "<h4>Position Assignment Details</h4>";
      var_dump($positionAssignmentDetails);
      if($positionAssignmentDetails){
        if(!$DBConn->insert_data('tija_org_chart_position_assignments', $positionAssignmentDetails)){
          $errors[] = "Error adding Organisation Chart Position";
        } else {
          $positionAssignmentID = $DBConn->lastInsertId();
        }
      }
    }
  } else {
    $positionAssignmentDetails = Data::org_chart_position_assignments(array('positionAssignmentID'=>$positionAssignmentID), true, $DBConn);
    var_dump($positionAssignmentDetails);
    var_dump($positionParentID);
    $orgChartID  && $positionAssignmentDetails->orgChartID != $orgChartID ? $changes['orgChartID'] = $orgChartID : '';
    $positionID  && $positionAssignmentDetails->positionID != $positionID ? $changes['positionID'] = $positionID : '';
    if($positionParentID || $positionParentID === "0"){
      var_dump($positionAssignmentDetails->positionParentID);
      if($positionAssignmentDetails->positionParentID != $positionParentID){
        $changes['positionParentID'] = $positionParentID;
        var_dump($changes['positionParentID']);
      } 
    }

    $orgDataID  && $positionAssignmentDetails->orgDataID != $orgDataID ? $changes['orgDataID'] = $orgDataID : '';
    $entityID  && $positionAssignmentDetails->entityID != $entityID ? $changes['entityID'] = $entity : '';
    var_dump($changes);
    if(isset($changes['positionID']) && $changes['positionID'] !== null) {
      $positionDetails = Admin::tija_job_titles(array('jobTitleID'=>$positionID), true, $DBConn);
      var_dump($positionDetails);
      if($positionDetails) {
        $changes['positionTitle'] = $positionDetails->jobTitle;
        $changes['positionDescription'] = $positionDetails->jobDescription;
      } else {
        $errors[] = "Job Position not found";
      }
    }
    if($changes) {
      $changes['LastUpdate'] = $config['currentDateTimeFormated'];
      $changes['LastUpdatedByID'] = $userDetails->ID;
      if(count($changes) > 0){
        var_dump($changes);
        if(!$DBConn->update_table('tija_org_chart_position_assignments', $changes, array('positionAssignmentID'=>$positionAssignmentID))){
          $errors[] = "Error updating Organisation Chart Position";
        }
      }
    }
  }



  $returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
	var_dump($returnURL);
} else {
    $errors[] = "You are not authorized to perform this action. Please log in as a valid administrator.";
}

if (count($errors) == 0) {
	$DBConn->commit();
	$messages = array(array('Text'=>"{$success}", 'Type'=>'success'));
  $returnURL.="&orgChartID={$orgChartID}";
} else {
 	$DBConn->rollback();
 	$messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
    
}
var_dump($messages);
 $_SESSION['FlashMessages'] = serialize($messages);
 header("location:{$base}html/{$returnURL}");