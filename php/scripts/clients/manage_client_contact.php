<?php
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
$addressDetails = array();
$success = "";
if ($isValidUser) {
	var_dump($_POST);

   $clientContactID = (isset($_POST['clientContactID']) && !empty($_POST['clientContactID'])) ? $_POST['clientContactID'] : null;
   $userID = (isset($_POST['userID']) && !empty($_POST['userID'])) ? $_POST['userID'] : null;
   $clientID = (isset($_POST['clientID']) && !empty($_POST['clientID'])) ? $_POST['clientID'] : null;
   $firstName = (isset($_POST['firstName']) && !empty($_POST['firstName'])) ? $_POST['firstName'] : null;
   $lastName = (isset($_POST['lastName']) && !empty($_POST['lastName'])) ? $_POST['lastName'] : null;
   $title = (isset($_POST['title']) && !empty($_POST['title'])) ? $_POST['title'] : null;
   $salutationID = (isset($_POST['salutationID']) && !empty($_POST['salutationID'])) ? $_POST['salutationID'] : null;
   $email = (isset($_POST['email']) && !empty($_POST['email']) && Form::validate_email($_POST['email'])) ? $_POST['email'] : null;
   $telephone = (isset($_POST['telephone']) && !empty($_POST['telephone'])) ? $_POST['telephone'] : null;
   // $mobile = (isset($_POST['mobile']) && !empty($_POST['mobile'])) ? $_POST['mobile'] : null;
   $clientAddressID = (isset($_POST['clientAddressID']) && !empty($_POST['clientAddressID'])) ? $_POST['clientAddressID'] : null;
   $contactTypeID = (isset($_POST['contactTypeID']) && !empty($_POST['contactTypeID'])) ? $_POST['contactTypeID'] : null;

   IF($clientContactID) {
      $clientDetails = Client::client_contacts(array('clientContactID'=>$clientContactID), true, $DBConn);

      $userID && $userID != $clientDetails->userID ? $changes['userID'] = $userID : '';
      $clientID && $clientID != $clientDetails->clientID ? $changes['clientID'] = $clientID : '';
      $contactNameArr=explode(' ', $clientDetails->contactName);
      
      ($firstName && $lastName && ($firstName != $contactNameArr[0] || $lastName != $contactNameArr[1])) ? $changes['contactName'] = "{$firstName} {$lastName}" : '';
      $title && $title != $clientDetails->title ? $changes['title'] = $title : '';
      $salutationID && $salutationID != $clientDetails->salutationID ? $changes['salutationID'] = $salutationID : '';
      $email && $email != $clientDetails->contactEmail ? $changes['contactEmail'] = $email : '';
      $telephone && $telephone != $clientDetails->contactPhone ? $changes['contactPhone'] = $telephone : '';
      $clientAddressID && $clientAddressID != $clientDetails->clientAddressID ? $changes['clientAddressID'] = $clientAddressID : '';
      $contactTypeID && $contactTypeID != $clientDetails->contactTypeID ? $changes['contactTypeID'] = $contactTypeID : '';


      var_dump($changes);

      if(!$errors){
         if(count($changes) > 0) {
            $changes['LastUpdateByID'] = $userDetails->ID;
            $changes['LastUpdate'] = $config['currentDateTimeFormated'];
            if(!$DBConn->update_table('tija_client_contacts', $changes, array('clientContactID'=>$clientContactID))) {
               $errors[] = 'Failed to update client contact details';
            } else {
               $success = "Client Contact updated successfully";
            }
         }
      }


      var_dump($clientDetails);


   } else {
      $details['clientID'] = $clientID;
      $details['userID'] = $userID;
      $firstName && $lastName ? $details['contactName'] = "{$firstName} {$lastName}" : $errors[] = 'First and Last Name is required';
      // $lastName ? $details['contactName'] = $lastName : $errors[] = 'Last Name is required';
      $title ? $details['title'] = $title : '';
      $salutationID ? $details['salutationID'] = $salutationID : '';
      $email ? $details['contactEmail'] = $email : $errors[] = 'Email is required';
      $telephone ? $details['contactPhone'] = $telephone : '';
      $clientAddressID ? $details['clientAddressID'] = $clientAddressID : $errors[] = 'Address is required';
      $contactTypeID ? $details['contactTypeID'] = $contactTypeID : $errors[] = 'Contact Type is required';
      $clientAddressID ? $details['clientAddressID'] = $clientAddressID : $errors[] = 'Address is required';

      if(count($errors) == 0) {
         if($details) {
            $details['LastUpdateByID'] = $userDetails->ID;
            $details['LastUpdate'] = $config['currentDateTimeFormated'];
            if(!$DBConn->insert_data('tija_client_contacts', $details)) {
               $errors[] = 'Failed to add client contact to the database';
            } else {
               $clientContactID = $DBConn->lastInsertID();
               $success = "Client Contact added successfully";
            }
         }
      }
      var_dump($details);
   }
var_dump($errors);

} else { 
   $errors[] = 'You need to log in as a valid administrator to do that.';
}
$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
  var_dump($returnURL);
if (count($errors) == 0) {
  $DBConn->commit();
  $messages = array(array('Text'=>"{$success}", 'Type'=>'success'));
} else {
   $DBConn->rollback();
   $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
}
$_SESSION['FlashMessages'] = serialize($messages);
header("location:{$base}html/{$returnURL}");?>