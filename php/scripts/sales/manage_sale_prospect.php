
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
   var_dump($_FILES);
  
   $orgDataID = (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ? Utility::clean_string($_POST['orgDataID']) : "";
   $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ? Utility::clean_string($_POST['entityID']) : "";
   $salesCaseID = (isset($_POST['salesCaseID']) && !empty($_POST['salesCaseID'])) ? Utility::clean_string($_POST['salesCaseID']) : "";
   $salesCaseID = (isset($_POST['salesCaseID']) && !empty($_POST['salesCaseID'])) ? Utility::clean_string($_POST['salesCaseID']) : "";
   $salesCaseName = (isset($_POST['salesCaseName']) && !empty($_POST['salesCaseName'])) ? Utility::clean_string($_POST['salesCaseName']) : "";
   $clientID = (isset($_POST['clientID']) && !empty($_POST['clientID'])) ? Utility::clean_string($_POST['clientID']) : "";
   $businessUnitID = (isset($_POST['businessUnitID']) && !empty($_POST['businessUnitID'])) ? Utility::clean_string($_POST['businessUnitID']) : "";
   $newBusinessUnit = (isset($_POST['newBusinessUnit']) && !empty($_POST['newBusinessUnit'])) ? Utility::clean_string($_POST['newBusinessUnit']) : "";
   $countryID = (isset($_POST['countryID']) && !empty($_POST['countryID'])) ? Utility::clean_string($_POST['countryID']) : "";
   $city = (isset($_POST['city']) && !empty($_POST['city'])) ? Utility::clean_string($_POST['city']) : "";
   $salesPersonID = (isset($_POST['salesPersonID']) && !empty($_POST['salesPersonID'])) ? Utility::clean_string($_POST['salesPersonID']) : "";
   $newClientNote = (isset($_POST['newClientNote']) && !empty($_POST['newClientNote'])) ? Utility::clean_string($_POST['newClientNote']) : "";
   $countryID = (isset($_POST['countryID']) && !empty($_POST['countryID'])) ? Utility::clean_string($_POST['countryID']) : "";
   $city = (isset($_POST['city']) && !empty($_POST['city'])) ? Utility::clean_string($_POST['city']) : "";


   if($newClientNote && !empty($newClientNote)) {
      $clientArray = array(
         'orgDataID' => $orgDataID,
         'entityID' => $entityID,       
      );
      $clientID ?  $clientArray['clientName'] = $clientID : $errors[] = 'You need to select a client or add a new client.';
      var_dump($clientArray['clientName']);

      $clientArray['clientName'] ? $clientArray['clientCode'] =Utility::clientCode($clientArray['clientName'] ) : $errors[] = 'You need to select a client or add a new client.';
      $salesPersonID ? $clientArray['accountOwnerID'] = $salesPersonID : $errors[] = 'You need to select client owner person.';
      if(!$errors) {

         $clientArray['LastUpdateByID'] = $userDetails->ID;
         $clientArray['LastUpdate'] = $config['currentDateTimeFormated'];
         $clientArray['countryID'] = $countryID;
         $clientArray['city'] = $city;
         $clientArray['clientStatus'] = 'active';
         if($clientArray){
            if(!$DBConn->insert_data('tija_clients', $clientArray)) {
               $errors[] = 'Error while adding new client.';
            } else {
               $clientID = $DBConn->lastInsertId();
               $success = 'New Client added successfully.';
            }
         }        
      }
      echo  "<h5> Client Array Details</h5>";
      var_dump($clientArray);
   } 

   if(!$salesCaseID){ 
      $orgDataID ? $details['orgDataID'] = $orgDataID : $errors[] = 'Organization is required.';
      $entityID ? $details['entityID'] = $entityID : $errors[] = 'Entity is required.';
      $salesCaseName ? $details['salesCaseName'] = $salesCaseName : $errors[] = 'Sales Case Name is required.';
      $clientID ? $details['clientID'] = $clientID : $errors[] = 'Client is required.';
      $businessUnitID ? $details['businessUnitID'] = $businessUnitID : '';
      $salesPersonID ? $details['salesPersonID'] = $salesPersonID : $errors[] = 'Sales Person is required.';
     
      if(!$errors){
         if($details){
            $details['LastUpdatedByID'] = $userDetails->ID;
            $details['LastUpdate'] = $config['currentDateTimeFormatted'];      
            if(!$DBConn->insert_data('tija_sales_cases', $details)) {
               $errors[] = 'Error while adding new sales case.';
            } else {
               $salesCaseID = $DBConn->lastInsertId();
               $success = 'New Sales Case added successfully.';
            }      
         }
      }

   } else {
      $salesCaseDetails = Sales::sales_cases(array('salesCaseID' => $salesCaseID), true, $DBConn);
      var_dump($salesCaseDetails);

      if($salesCaseDetails) {
         $salesCaseName && $salesCaseName != $salesCaseDetails->salesCaseName ? $changes['salesCaseName'] = $salesCaseName : null;
         $clientID && $clientID != $salesCaseDetails->clientID ? $changes['clientID'] = $clientID : null;
         $businessUnitID && $businessUnitID != $salesCaseDetails->businessUnitID ? $changes['businessUnitID'] = $businessUnitID : null;
         $salesPersonID && $salesPersonID != $salesCaseDetails->salesPersonID ? $changes['salesPersonID'] = $salesPersonID : null;
         $orgDataID && $orgDataID != $salesCaseDetails->orgDataID ? $changes['orgDataID'] = $orgDataID : null;
         $entityID && $entityID != $salesCaseDetails->entityID ? $changes['entityID'] = $entityID : null;
     
         
         if(!$errors){
            if($changes) {
               $changes['LastUpdateByID'] = $userDetails->ID;
               $changes['LastUpdate'] = $config['currentDateTimeFormated'];
               if(!$DBConn->update_table('tija_sales_cases', $changes, array('salesCaseID'=>$salesCaseID))) {
                  $errors[] = 'Error while updating sales case details.';
               } else {
                  $success = 'Sales Case details updated successfully.';
               }
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