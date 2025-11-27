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

   var_dump($errors);
   $address = (isset($_POST['address']) && !empty($_POST['address'])) ? $_POST['address'] : null;
   $postalCode = (isset($_POST['postalCode']) && !empty($_POST['postalCode'])) ? $_POST['postalCode'] : null;
   $city = (isset($_POST['city']) && !empty($_POST['city'])) ? $_POST['city'] : null;
   $countryID = (isset($_POST['countryID']) && !empty($_POST['countryID'])) ? $_POST['countryID'] : null;
   $addressType = (isset($_POST['addressType']) && !empty($_POST['addressType'])) ? $_POST['addressType'] : null;
   $billingAddress = (isset($_POST['billingAddress']) && !empty($_POST['billingAddress']) && Utility::clean_string($_POST['billingAddress']) ==="BillingAddress" ) ? "Y" : null;
   $headquarters = (isset($_POST['headquarters']) && !empty($_POST['headquarters']) && Utility::clean_string($_POST['headquarters']) === 'HeadQuaters' ) ? "Y" : null;
   $clientID = (isset($_POST['clientID']) && !empty($_POST['clientID'])) ? $_POST['clientID'] : null;
   $orgDataID = (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ? $_POST['orgDataID'] : null;
   $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ? $_POST['entityID'] : null;
   $clientAddressID = (isset($_POST['clientAddressID']) && !empty($_POST['clientAddressID'])) ? $_POST['clientAddressID'] : null;
   
   if(!$clientAddressID) {
      $address ? $addressDetails['address'] = $address : $errors[] = 'Address is required';
      $postalCode ? $addressDetails['postalCode'] = $postalCode : '';
      $city ? $addressDetails['city'] = $city : $errors[] = 'City is required. Please input your nearest city or town';
      $countryID ? $addressDetails['countryID'] = $countryID : $errors[] = 'Country is required';
      $addressType ? $addressDetails['addressType'] = $addressType : $errors[] = 'Address Type is required';
      $headquarters && $headquarters == 'Headquarters' ? $addressDetails['headquarters'] = "Y" : 'N';
      $billingAddress && $billingAddress == 'BillingAddress' ? $addressDetails['billingAddress'] = "Y" : 'N';
      $clientID ? $addressDetails['clientID'] = $clientID : $errors[] = 'Client ID is required';
      $orgDataID ? $addressDetails['orgDataID'] = $orgDataID : $errors[] = 'Organization is required';
      $entityID ? $addressDetails['entityID'] = $entityID : $errors[] = 'Entity is required';
      var_dump($addressDetails);
      if(!$errors){
         if(count($addressDetails) > 0) {
            $addressDetails['DateAdded'] = $config['currentDateTimeFormated'];
            $addressDetails['LastUpdateByID'] = $userDetails->ID;
            $addressDetails['LastUpdate'] = $config['currentDateTimeFormated'];
            if(!$DBConn->insert_data('tija_client_addresses', $addressDetails)) {
               $errors[] = 'Failed to add client address to the database';
            } else {
               $success = "Client Address added successfully";
            }
         }
      }


   } else {
      $clientAddressDetails = Client::client_address(array('clientAddressID'=>$clientAddressID), true, $DBConn);
      var_dump($clientAddressDetails);
      $clientID && $clientID != $clientAddressDetails->clientID ? $addressDetails['clientID'] = $clientID : '';
      $address && $address != $clientAddressDetails->address ? $addressDetails['address'] = $address : '';
      $postalCode && $postalCode != $clientAddressDetails->postalCode ? $addressDetails['postalCode'] = $postalCode : '';
      $city && $city != $clientAddressDetails->city ? $addressDetails['city'] = $city : '';
      $countryID && $countryID != $clientAddressDetails->countryID ? $addressDetails['countryID'] = $countryID : '';
      $addressType && $addressType != $clientAddressDetails->addressType ? $addressDetails['addressType'] = $addressType : '';
      $billingAddress && $billingAddress != $clientAddressDetails->billingAddress ? $addressDetails['billingAddress'] = $billingAddress : '';
      $headquarters && $headquarters != $clientAddressDetails->headquarters ? $addressDetails['headquarters'] = $headquarters : '';
      var_dump($addressDetails);
      if(!$errors){
         if(count($addressDetails) > 0) {
            $addressDetails['LastUpdateByID'] = $userDetails->ID;
            $addressDetails['LastUpdate'] = $config['currentDateTimeFormated'];
            if(!$DBConn->update_table('tija_client_addresses', $addressDetails, array('clientAddressID'=>$clientAddressID))) {
               $errors[] = 'Failed to update client address details';
            } else {
               $success = "Client Address updated successfully";
            }
         }
      }
   }

   

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