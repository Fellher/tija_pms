<?php
session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
$success="";
if ($isValidUser) {
	var_dump($_POST);
   $salesProspectID = (isset($_POST['salesProspectID']) && $_POST['salesProspectID']) ? Utility::clean_string($_POST['salesProspectID']) : null;
   $salesProspectName = (isset($_POST['salesProspectName']) && $_POST['salesProspectName']) ? Utility::clean_string($_POST['salesProspectName']) : null;
   $orgDataID = (isset($_POST['orgDataID']) && $_POST['orgDataID']) ? Utility::clean_string($_POST['orgDataID']) : null;
   $entityID = (isset($_POST['entityID']) && $_POST['entityID']) ? Utility::clean_string($_POST['entityID']) : null;
   $isClient = (isset($_POST['isClient']) && $_POST['isClient']) ? Utility::clean_string($_POST['isClient']) : null;
   $clientID = (isset($_POST['clientID']) && $_POST['clientID']) ? Utility::clean_string($_POST['clientID']) : null;
   $address = (isset($_POST['address']) && $_POST['address']) ? Utility::clean_string($_POST['address']) : null;
   $prospectEmail = (isset($_POST['prospectEmail']) && $_POST['prospectEmail']) ? Utility::clean_string($_POST['prospectEmail']) : null;
   $prospectCaseName = (isset($_POST['prospectCaseName']) && $_POST['prospectCaseName']) ? Utility::clean_string($_POST['prospectCaseName']) : null;
   $estimatedValue = (isset($_POST['estimatedValue']) && $_POST['estimatedValue']) ? Utility::clean_string($_POST['estimatedValue']) : null;
   $probability = (isset($_POST['probability']) && $_POST['probability']) ? Utility::clean_string($_POST['probability']) : null;
   $leadSourceID = (isset($_POST['leadSourceID']) && $_POST['leadSourceID']) ? Utility::clean_string($_POST['leadSourceID']) : null;
   $businessUnitID = (isset($_POST['businessUnitID']) && $_POST['businessUnitID']) ? Utility::clean_string($_POST['businessUnitID']) : null;
   $salesProspectStatus = (isset($_POST['salesProspectStatus']) && $_POST['salesProspectStatus']) ? Utility::clean_string($_POST['salesProspectStatus']) : null;
   $countryID = (isset($_POST['countryID']) && $_POST['countryID']) ? Utility::clean_string($_POST['countryID']) : null;
   $city = (isset($_POST['city']) && $_POST['city']) ? Utility::clean_string($_POST['city']) : null;

   if(!$salesProspectID){
      echo "<h4>adding Sales Prospect</h4>";
     
      $orgDataID ? $details['orgDataID'] = $orgDataID : $errors[] = 'You need to select an organization.';
      $entityID ? $details['entityID'] = $entityID : $errors[] = 'You need to select an entity.';
      $isClient ? $details['isClient'] = $isClient : null;  
      if($isClient == 'Y'){
         $clientID ? $details['clientID'] = $clientID : null;
         $clientDetails = Client::client_full(array('clientID'=>$clientID), true, $DBConn);

         var_dump($clientDetails);
         $salesProspectName = $clientDetails->clientName;
         $address = isset($clientDetails->clientAddresses) && is_array($clientDetails->clientAddresses) ?  $clientDetails->clientAddresses[0]->address : "";
        
        
         
      } else {
         $details['clientID'] = null;
         $address ? $details['address'] = $address : null;
         $prospectEmail ? $details['prospectEmail'] = $prospectEmail : null;
         $salesProspectName ? $details['salesProspectName'] = $salesProspectName : null;

      }
      $address ? $details['address'] = $address : "";
      $salesProspectName ? $details['salesProspectName'] = $salesProspectName : $errors[] = 'Please input a sales prospect name.';
      $prospectEmail ? $details['prospectEmail'] = $prospectEmail : $errors[] = 'Please input an email address.';
      $prospectCaseName ? $details['prospectCaseName'] = $prospectCaseName : $errors[] = 'Please input a case name.';
      $estimatedValue ? $details['estimatedValue'] = $estimatedValue : null;
      $probability ? $details['probability'] = $probability : null;
      $leadSourceID ? $details['leadSourceID'] = $leadSourceID : null;
      $businessUnitID ? $details['businessUnitID'] = $businessUnitID : $errors[] = 'You need to input a business unit.';
      $salesProspectStatus ? $details['salesProspectStatus'] = $salesProspectStatus : null;
      $countryID ? $details['countryID'] = $countryID : null;
      $city ? $details['city'] = $city : null;
      $details['LastUpdate'] = date('Y-m-d H:i:s');
      $details['LastUpdateByID'] = $userDetails->ID;

      var_dump($details);
      var_dump($errors);

      if(!$errors){
         if($details){
            if(!$DBConn->insert_data('tija_sales_prospects', $details)){
               $errors[] = 'There was a problem updating the sales prospect.';
            } else {
               $success = 'Sales prospect updated successfully.';
            }
         }
      }

   } else {
      $prospectDetails = Sales::sales_prospects(array('salesProspectID'=>$salesProspectID), true, $DBConn);
      var_dump($prospectDetails);
      $salesProspectName  && $prospectDetails->salesProspectName != $salesProspectName ? $changes['salesProspectName'] = $salesProspectName : null;
      $orgDataID && $prospectDetails->orgDataID != $orgDataID ?$changes['orgDataID'] = $orgDataID : null;
      $entityID && $prospectDetails->entityID != $entityID ?$changes['entityID'] = $entityID : null;
      $isClient && $prospectDetails->isClient != $isClient ?$changes['isClient'] = $isClient : null;
      $prospectEmail && $prospectDetails->prospectEmail != $prospectEmail ?$changes['prospectEmail'] = $prospectEmail : null;
      $address && $prospectDetails->address != $address ?$changes['address'] = $address : null;
      $prospectCaseName && $prospectDetails->prospectCaseName != $prospectCaseName ?$changes['prospectCaseName'] = $prospectCaseName : null;
      $estimatedValue && $prospectDetails->estimatedValue != $estimatedValue ?$changes['estimatedValue'] = $estimatedValue : null;
      $probability && $prospectDetails->probability != $probability ?$changes['probability'] = $probability : null;
      $leadSourceID && $prospectDetails->leadSourceID != $leadSourceID ?$changes['leadSourceID'] = $leadSourceID : null;
      $businessUnitID && $prospectDetails->businessUnitID != $businessUnitID ?$changes['businessUnitID'] = $businessUnitID : null;
      $salesProspectStatus && $prospectDetails->salesProspectStatus != $salesProspectStatus ?$changes['salesProspectStatus'] = $salesProspectStatus : null;
      if($changes){
         $changes['LastUpdate'] = date('Y-m-d H:i:s');
         $changes['LastUpdateByID'] = $userDetails->ID;
         var_dump($changes);
         if(!$DBConn->update_table('tija_sales_prospects', $changes, array('salesProspectID'=>$salesProspectID))){
            $errors[] = 'There was a problem updating the sales prospect.';
         } else {
            $success = 'Sales prospect updated successfully.';
         }
      } else {
         $errors[] = 'No changes were made to the sales prospect.';
      }
      
      
   }

   
   var_dump($errors);

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