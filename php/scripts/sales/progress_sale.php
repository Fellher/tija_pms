<?php
session_start();
$base = "../../../";
set_include_path($base);

include 'php/includes.php';



$errors = array();
$DBConn->begin();
$details=array();
$changes= array();

if ($isValidUser) {
	var_dump($_POST);
   $salesProgressID  = (isset($_POST['salesProgressID']) && !empty($_POST['salesProgressID'])) ? Utility::clean_string($_POST['salesProgressID']) : "";
   $salesCaseID = (isset($_POST['salesCaseID']) && !empty($_POST['salesCaseID'])) ? Utility::clean_string($_POST['salesCaseID']) : "";
   $clientID = (isset($_POST['clientID']) && !empty($_POST['clientID'])) ? Utility::clean_string($_POST['clientID']) : "";
   $businessUnitID = (isset($_POST['businessUnitID']) && !empty($_POST['businessUnitID'])) ? Utility::clean_string($_POST['businessUnitID']) : "";
   $salesPersonID = (isset($_POST['salesPersonID']) && !empty($_POST['salesPersonID'])) ? Utility::clean_string($_POST['salesPersonID']) : $userDetails->ID; // Default to current user if not set
 
   $saleStatusLevelID = (isset($_POST['saleStatusLevelID']) && !empty($_POST['saleStatusLevelID'])) ? Utility::clean_string($_POST['saleStatusLevelID']) : "";
   $progressPercentage = (isset($_POST['progressPercentage']) && !empty($_POST['progressPercentage'])) ? Utility::clean_string($_POST['progressPercentage']) : "";
   $progressNotes = (isset($_POST['progressNotes']) && !empty($_POST['progressNotes'])) ? Utility::clean_string($_POST['progressNotes']) : "";
   $orgDataID = (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ? Utility::clean_string($_POST['orgDataID']) : "";
   $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ? Utility::clean_string($_POST['entityID']) : "";
   $salesCaseEstimate = (isset($_POST['salesCaseEstimate']) && !empty($_POST['salesCaseEstimate'])) ? Utility::clean_string($_POST['salesCaseEstimate']) : "";
   $leadSourceID = (isset($_POST['leadSourceID']) && !empty($_POST['leadSourceID'])) ? Utility::clean_string($_POST['leadSourceID']) : "";
   $salesCaseContactID = (isset($_POST['salesCaseContactID']) && !empty($_POST['salesCaseContactID'])) ? Utility::clean_string($_POST['salesCaseContactID']) : "";
   if ($salesCaseContactID && !is_numeric($salesCaseContactID) && $salesCaseContactID == 'addNew') {
      $firstName = (isset($_POST['firstName']) && !empty($_POST['firstName'])) ? Utility::clean_string($_POST['firstName']) : "";
      $lastName = (isset($_POST['lastName']) && !empty($_POST['lastName'])) ? Utility::clean_string($_POST['lastName']) : "";
      $title = (isset($_POST['title']) && !empty($_POST['title'])) ? Utility::clean_string($_POST['title']) : "";
      $salutationID = (isset($_POST['salutationID']) && !empty($_POST['salutationID'])) ? Utility::clean_string($_POST['salutationID']) : "";
      $email = (isset($_POST['email']) && !empty($_POST['email'])) ? Utility::clean_string($_POST['email']) : "";
      $telephone = (isset($_POST['telephone']) && !empty($_POST['telephone'])) ? Utility::clean_string($_POST['telephone']) : "";
      $contactTypeID = (isset($_POST['contactTypeID']) && !empty($_POST['contactTypeID'])) ? Utility::clean_string($_POST['contactTypeID']) : "";
      $clientAddressID = (isset($_POST['clientAddressID']) && !empty($_POST['clientAddressID'])) ? Utility::clean_string($_POST['clientAddressID']) : "";
      if($clientAddressID && $clientAddressID =="addNew") {
         $address = (isset($_POST['address']) && !empty($_POST['address'])) ? Utility::clean_string($_POST['address']) : "";
         $postalCode = (isset($_POST['postalCode']) && !empty($_POST['postalCode'])) ? Utility::clean_string($_POST['postalCode']) : "";
         $city = (isset($_POST['city']) && !empty($_POST['city'])) ? Utility::clean_string($_POST['city']) : "";
         $countryID = (isset($_POST['countryID']) && !empty($_POST['countryID'])) ? Utility::clean_string($_POST['countryID']) : "";
         $addressType = (isset($_POST['addressType']) && !empty($_POST['addressType'])) ? Utility::clean_string($_POST['addressType']) : "";
         $addressDetails=array();
         $address ?  $addressDetails['address'] = $address : $errors[] = "Please submit valid address";
         $postalCode ? $addressDetails['postalCode'] = $postalCode : $errors[] = "Please submit valid postal code";
         $city ? $addressDetails['city'] = $city : $errors[] = "Please submit valid city";
         $countryID ? $addressDetails['countryID'] = $countryID : $errors[] = "Please submit valid country ID";
         $addressType ? $addressDetails['addressType'] = $addressType : $errors[] = "Please submit valid address type";
         if (count($errors) == 0) {
            if (!$DBConn->insert_data("tija_client_addresses", $addressDetails)) {
               $errors[] = "<span class't600'> ERROR! </span> Unable to update client address details to the database";					
            } else {
               $clientAddressID = $DBConn->lastInsertID();
            }
         }

      } 
      $contactDetails = array();
      $firstName ? $contact['firstName'] = $firstName : $errors[] = "Please submit valid first name";
      $lastName ? $contact['lastName'] = $lastName : $errors[] = "Please submit valid last name";
      if($firstName && $lastName) {
         $contactDetails['contactName'] = "{$firstName} {$lastName}";
      } else {
         $errors[] = "Please submit valid first and last name";
      }
      $title ? $contactDetails['title'] = $title : $errors[] = "Please submit valid title";
    
      $email ? $contactDetails['contactEmail'] = $email : "";
      $telephone ? $contactDetails['contactPhone'] = $telephone : "";
      $contactTypeID ? $contactDetails['contactTypeID'] = $contactTypeID : $errors[] = "Please submit valid contact type";
      $clientAddressID ? $contactDetails['clientAddressID'] = $clientAddressID : $errors[] = "Please submit valid client address ";
      if (count($errors) == 0) {
         if (!$DBConn->insert_data("tija_client_contacts", $contactDetails)) {
            $errors[] = "<span class't600'> ERROR! </span> Unable to update client contact details to the database";					
         } else {
            $salesCaseContactID = $DBConn->lastInsertID();
         }
      }

   }

   if(!$salesProgressID){
      $salesCaseID  ? $details['salesCaseID'] = $salesCaseID : $errors[] = "Please submit valid sales case ID";
      $clientID ? $details['clientID'] = $clientID : $errors[] = "Please submit valid client ID";
      $businessUnitID ? $details['businessUnitID'] = $businessUnitID : $errors[] = "Please submit valid business unit ID";
      $salesPersonID ? $details['salesPersonID'] = $salesPersonID : $errors[] = "Please submit valid sales person ID";
      $saleStatusLevelID ? $details['saleStatusLevelID'] = $saleStatusLevelID : $errors[] = "Please submit valid sales status level ID";
      $salesStatusLevelDetails = Sales::sales_status_levels(['saleStatusLevelID'=>$saleStatusLevelID], true, $DBConn);
      $progressPercentage ? $details['progressPercentage'] = $progressPercentage : $details['progressPercentage'] = $salesStatusLevelDetails->levelPercentage;
      $progressNotes ? $details['progressNotes'] = $progressNotes : "";
      $orgDataID ? $details['orgDataID'] = $orgDataID : $errors[] = "Please submit valid organization data ID";
      $entityID ? $details['entityID'] = $entityID : $errors[] = "Please submit valid entity ID";
      $salesCaseEstimate ? $details['salesCaseEstimate'] = $salesCaseEstimate : "";
      $leadSourceID ? $details['leadSourceID'] = $leadSourceID : "";
      $salesCaseContactID ? $details['salesCaseContactID'] = $salesCaseContactID : $errors[] = "Please submit valid sales case contact ID";

      if (count($errors) == 0) {
         if($details){
            if (!$DBConn->insert_data("tija_sales_progress", $details)) {
               $errors[] = "<span class't600'> ERROR! </span> Unable to update sales progress details to the database";					
            } else {
               $salesProgressID = $DBConn->lastInsertID();
               $salesCaseDetails = Sales::sales_case_mid(['salesCaseID'=>$salesCaseID], true, $DBConn);
               if($salesCaseDetails){
                  var_dump($salesCaseDetails);
                  var_dump($salesStatusLevelDetails);
                  echo "<br>Sales Case Details: progress percentage {$progressPercentage} <br>";
                  $saleStatusLevelID && $saleStatusLevelID !== $salesCaseDetails->saleStatusLevelID ? $salesChanges['saleStatusLevelID'] = $saleStatusLevelID : '';
                  $progressPercentage && $progressPercentage !== $salesCaseDetails->probability ? $salesChanges['probability'] = $progressPercentage : $salesChanges['probability'] = $salesStatusLevelDetails->levelPercentage;
                  $salesCaseContactID && $salesCaseContactID !== $salesCaseDetails->salesCaseContactID ? $salesChanges['salesCaseContactID'] = $salesCaseContactID : '';
                  $salesPersonID && $salesPersonID !== $salesCaseDetails->salesPersonID ? $salesChanges['salesPersonID'] = $salesPersonID : '';
                  $salesCaseEstimate && $salesCaseEstimate !== $salesCaseDetails->salesCaseEstimate ? $salesChanges['salesCaseEstimate'] = $salesCaseEstimate : '';
                  $leadSourceID && $leadSourceID !== $salesCaseDetails->leadSourceID ? $salesChanges['leadSourceID'] = $leadSourceID : '';
                  
                  $salesChanges['salesProgressID'] = $salesProgressID;
                  $salesChanges['saleStage'] = 'opportunities';

                  var_dump($salesChanges);
                  if($salesChanges){
                     if (!$DBConn->update_table("tija_sales_cases", $salesChanges, array('salesCaseID'=>$salesCaseID))) {
                        $errors[] = "<span class't600'> ERROR! </span> Unable to update sales case details to the database";					
                     } else {
                        $success = "Sales case details updated successfully.";
                     }
                  } else {
                     $errors[] = "No changes were made to the sales case details";

                  }
                  // if (!$DBConn->update_table("tija_sales_cases", $salesChanges, array('salesCaseID'=>$salesCaseID))) {
                  //    $errors[] = "<span class't600'> ERROR! </span> Unable to update sales case details to the database";					
                  // } else {
                  //    $DBConn->commit();
                  // }
               } else {
                  $errors[] = "Unable to find sales case details for the provided sales case ID";
               }
            }				
         }
      }



   } else{
      $salesProgressDetails = Sales::sales_progress(['salesProgressID'=>$salesProgressID], true,  $DBConn);
      var_dump($salesProgressDetails);
      $salesCaseID && $salesCaseID !== $salesProgressDetails->salesCaseID ? $changes['salesCaseID'] = $salesCaseID : '';
      $clientID && $clientID !== $salesProgressDetails->clientID ? $changes['clientID'] = $clientID : '';
      $businessUnitID && $businessUnitID !== $salesProgressDetails->businessUnitID ? $changes['businessUnitID'] = $businessUnitID : '';
      $salesPersonID && $salesPersonID !== $salesProgressDetails->salesPersonID ? $changes['salesPersonID'] = $salesPersonID : '';
      $saleStatusLevelID && $saleStatusLevelID !== $salesProgressDetails->saleStatusLevelID ? $changes['saleStatusLevelID'] = $saleStatusLevelID : '';
      $salesStatusLevelDetails = Sales::sales_status_levels(['saleStatusLevelID'=>$saleStatusLevelID], true, $DBConn);
      $progressPercentage && $progressPercentage !== $salesProgressDetails->progressPercentage ? $changes['progressPercentage'] = $progressPercentage : $changes['progressPercentage'] = $salesStatusLevelDetails->levelPercentage;
      $progressNotes && $progressNotes !== $salesProgressDetails->progressNotes ? $changes['progressNotes'] = $progressNotes : '';
      $orgDataID && $orgDataID !== $salesProgressDetails->orgDataID ? $changes['orgDataID'] = $orgDataID : '';
      $entityID && $entityID !== $salesProgressDetails->entityID ? $changes['entityID'] = $entityID : '';
      $salesCaseEstimate && $salesCaseEstimate !== $salesProgressDetails->salesCaseEstimate ? $changes['salesCaseEstimate'] = $salesCaseEstimate : '';
      $leadSourceID && $leadSourceID !== $salesProgressDetails->leadSourceID ? $changes['leadSourceID'] = $leadSourceID : '';
      $salesCaseContactID && $salesCaseContactID !== $salesProgressDetails->salesCaseContactID ? $changes['salesCaseContactID'] = $salesCaseContactID : '';


      if (count($changes) > 0) {
         if (!$DBConn->update_data("tija_sales_progress", $changes, array('salesProgressID'=>$salesProgressID))) {
            $errors[] = "<span class't600'> ERROR! </span> Unable to update sales progress details to the database";					
         } 
      } else {
         $errors[] = "No changes were made to the sales progress details";
      }
   }
   

} else {
	Alert::danger('You need to be logged in as a valid user to edit the User personal infomation');
}

if (count($errors) == 0) {
   $DBConn->commit();
   $messages = array(array('Text'=>'The updates were successfully Saved.', 'Type'=>'success'));
   if (isset($s) || isset($p) || isset($ss) ) {
   	 $returnURL= "s={$s}&ss={$ss}&p=client_details&id={$clientID}";
   } else {
   	 $returnURL= Utility::returnURL($_SESSION['returnURL'], "s=user&ss=work&p=sales");
   }
   $returnURL = "?s=user&ss=sales&p=sale_details&saleid={$salesCaseID}&salesProgressID={$salesProgressID}&clientID={$clientID}";
  
} else {
   $DBConn->rollback();
   $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
   $_SESSION['posts'] = serialize($_POST);  
   $returnURL= Utility::returnURL($_SESSION['returnURL'], "s={$s}&ss={$ss}&p={$p}");
	
}

var_dump($returnURL);
$_SESSION['FlashMessages'] = serialize($messages);
header("location:{$base}html/{$returnURL}");
// var_dump($errors); ?>