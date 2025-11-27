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
if ($isValidAdmin || $isAdmin) {
	var_dump($_POST);

   $entityName = (isset($_POST['entityName']) && !empty($_POST['entityName'])) ?  Utility::clean_string($_POST['entityName']): "";
   $entityTypeID = (isset($_POST['entityTypeID']) && !empty($_POST['entityTypeID'])) ?  Utility::clean_string($_POST['entityTypeID']): "";
   // Handle entityParentID - 0 is valid (no parent entity)
   $entityParentID = isset($_POST['entityParentID']) ? Utility::clean_string($_POST['entityParentID']) : "";
   $entityDescription = (isset($_POST['entityDescription']) && !empty($_POST['entityDescription'])) ?  Utility::clean_string($_POST['entityDescription']): "";
   $orgDataID = (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ?  Utility::clean_string($_POST['orgDataID']): "";
   $industrySectorID = (isset($_POST['industrySectorID']) && !empty($_POST['industrySectorID'])) ?  Utility::clean_string($_POST['industrySectorID']): "";
   $registrationNumber = (isset($_POST['registrationNumber']) && !empty($_POST['registrationNumber'])) ?  Utility::clean_string($_POST['registrationNumber']): "";
   $entityPIN = (isset($_POST['entityPIN']) && !empty($_POST['entityPIN'])) ?  Utility::clean_string($_POST['entityPIN']): "";
   $entityCity = (isset($_POST['entityCity']) && !empty($_POST['entityCity'])) ?  Utility::clean_string($_POST['entityCity']): "";
   $entityCountry = (isset($_POST['entityCountry']) && !empty($_POST['entityCountry'])) ?  Utility::clean_string($_POST['entityCountry']): "";
   $entityPhoneNumber = (isset($_POST['entityPhoneNumber']) && !empty($_POST['entityPhoneNumber'])) ?  Utility::clean_string($_POST['entityPhoneNumber']): "";
   $entityEmail = (isset($_POST['entityEmail']) && !empty($_POST['entityEmail'])) ?  Utility::clean_string($_POST['entityEmail']): "";
   $entityID= (isset($_POST['entityID']) && !empty($_POST['entityID'])) ?  Utility::clean_string($_POST['entityID']): "";
   $action = (isset($_POST['action']) && !empty($_POST['action'])) ?  Utility::clean_string($_POST['action']): "";


    if($entityID) {
        $entityDetails = Data::entities(array("entityID"=>$entityID), true, $DBConn);
        var_dump($entityDetails);
        if($entityDetails){
            (isset($entityTypeID) && !empty($entityTypeID) && $entityTypeID != $entityDetails->entityTypeID) ? $changes['entityTypeID'] = $entityTypeID : '';

            // Handle entityParentID - allow 0 (no parent)
            if (isset($_POST['entityParentID'])) {
                $newParentID = ($entityParentID === '' || $entityParentID === null) ? null : (int)$entityParentID;
                $currentParentID = isset($entityDetails->entityParentID) ? (int)$entityDetails->entityParentID : null;
                if ($newParentID !== $currentParentID) {
                    $changes['entityParentID'] = $newParentID;
                }
            }

            (isset($entityDescription) && !empty($entityDescription) && $entityDescription != $entityDetails->entityDescription) ? $changes['entityDescription'] = $entityDescription : '';
            (isset($orgDataID) && !empty($orgDataID) && $orgDataID != $entityDetails->orgDataID) ? $changes['orgDataID'] = $orgDataID : '';
            (isset($industrySectorID) && !empty($industrySectorID) && $industrySectorID != $entityDetails->industrySectorID) ? $changes['industrySectorID'] = $industrySectorID : '';
            (isset($registrationNumber) && !empty($registrationNumber) && $registrationNumber != $entityDetails->registrationNumber) ? $changes['registrationNumber'] = $registrationNumber : '';
            (isset($entityPIN) && !empty($entityPIN) && $entityPIN != $entityDetails->entityPIN) ? $changes['entityPIN'] = $entityPIN : '';
            (isset($entityCity) && !empty($entityCity) && $entityCity != $entityDetails->entityCity) ? $changes['entityCity'] = $entityCity : '';
            (isset($entityCountry) && !empty($entityCountry) && $entityCountry != $entityDetails->entityCountry) ? $changes['entityCountry'] = $entityCountry : '';
            (isset($entityPhoneNumber) && !empty($entityPhoneNumber) && $entityPhoneNumber != $entityDetails->entityPhoneNumber) ? $changes['entityPhoneNumber'] = $entityPhoneNumber : '';
            (isset($entityEmail) && !empty($entityEmail) && $entityEmail != $entityDetails->entityEmail) ? $changes['entityEmail'] = $entityEmail : '';
            (isset($entityName) && !empty($entityName) && $entityName != $entityDetails->entityName) ? $changes['entityName'] = $entityName : '';
            if($action == 'delete') {
                echo "<h4>Deleting</h4>";
                if(!$DBConn->delete_row('tija_entities', array("entityID"=>$entityID))) {
                    $errors[] = "There was an error deleting the entity.";
                } else {
                    $success = "The entity has been deleted successfully.";
                }
            } else {

               if(count($changes) > 0){
                  var_dump($changes);

                  if(!$DBConn->update_table('tija_entities', $changes, array("entityID"=>$entityID))) {
                        $errors[] = "There was an error updating the entity.";
                     } else {
                        $success = "The entity has been updated successfully.";
                        $entityID = $DBConn->lastInsertId();
                  }
               }
            }
         } else {
            $errors[] = "The entity could not be found.";
         }
      } else {

         $entityDetails = array();
         (isset($entityName) && !empty($entityName)) ? $entityDetails['entityName'] = $entityName: $errors[]= 'The entity name is required.';
         (isset($entityTypeID) && !empty($entityTypeID)) ? $entityDetails['entityTypeID'] = $entityTypeID: $errors[]= 'The entity type is required.';

         // Handle entityParentID - allow 0 (no parent entity), default to 0 if not set
         if (isset($entityParentID) && $entityParentID !== '') {
             $entityDetails['entityParentID'] = ($entityParentID === '0' || $entityParentID === 0) ? 0 : $entityParentID;
         } else {
             $entityDetails['entityParentID'] = 0;
         }

         (isset($entityDescription) && !empty($entityDescription)) ? $entityDetails['entityDescription'] = $entityDescription: "";
         (isset($orgDataID) && !empty($orgDataID)) ? $entityDetails['orgDataID'] = $orgDataID: $errors[]= 'The organization data is required.';
         (isset($industrySectorID) && !empty($industrySectorID)) ? $entityDetails['industrySectorID'] = $industrySectorID: $errors[]= 'The industry sector is required.';
         (isset($registrationNumber) && !empty($registrationNumber)) ? $entityDetails['registrationNumber'] = $registrationNumber: $errors[]= 'The registration number is required.';
         (isset($entityPIN) && !empty($entityPIN)) ? $entityDetails['entityPIN'] = $entityPIN: $errors[]= 'The entity PIN is required.';
         (isset($entityCity) && !empty($entityCity)) ? $entityDetails['entityCity'] = $entityCity: $errors[]= 'The entity city is required.';
         (isset($entityCountry) && !empty($entityCountry)) ? $entityDetails['entityCountry'] = $entityCountry: $errors[]= 'The entity country is required.';
         (isset($entityPhoneNumber) && !empty($entityPhoneNumber)) ? $entityDetails['entityPhoneNumber'] = $entityPhoneNumber: $errors[]= 'The entity phone number is required.';
         (isset($entityEmail) && !empty($entityEmail)) ? $entityDetails['entityEmail'] = $entityEmail: $errors[]= 'The entity email is required.';
         if(count($errors) === 0) {
            if($entityDetails) {
               $entityDetails['LastUpdateByID'] = $userDetails->ID;
               $entityDetails['LastUpdate'] = $config['currentDateTimeFormated'];
               if(!$DBConn->insert_data('tija_entities', $entityDetails)){
                  $errors[] = "There was an error adding the entity.";
               } else {
                  $entityID = $DBConn->lastInsertId();
                  // Create a default cost center for the entity/and client with a flag of inhouse
                  isset($entityName) && $entityName ? $clientDetails['clientName'] = $entityName : $errors[] = 'The client name is required.';
                  $clientDetails['clientCode'] =  Utility::clientCode($clientDetails['clientName']) ;
                  (isset($orgDataID) && !empty($orgDataID)) ? $clientDetails['orgDataID'] = $orgDataID: $errors[]= 'The organization data is required.';
                  (isset($entityID) && !empty($entityID)) ? $clientDetails['entityID'] = $entityID: $errors[]= 'The entity is required.';
                  (isset($industrySectorID) && !empty($industrySectorID)) ? $clientDetails['clientIndustryID'] = $industrySectorID: $errors[]= 'The industry sector is required.';
                  (isset($entityPIN) && !empty($entityPIN)) ? $clientDetails['clientPin'] = $entityPIN: "";
                  $clientDetails['inhouse'] = 'Y';
                  $clientDetails['accountOwnerID'] = $userDetails->ID;

                  if(!$errors){
                     if($clientDetails) {
                        $clientDetails['LastUpdateByID'] = $userDetails->ID;
                        $clientDetails['LastUpdate'] = $config['currentDateTimeFormated'];
                        if(!$DBConn->insert_data('tija_clients', $clientDetails)) {
                           $errors[] = "There was an error adding the client.";

                        } else {
                           $clientID = $DBConn->lastInsertId();
                        }
                     }
                  }

               }
            }
         }
      }


   $returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
	var_dump($returnURL);

} else {
    $errors[] = "You are not authorized to perform this action.";
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