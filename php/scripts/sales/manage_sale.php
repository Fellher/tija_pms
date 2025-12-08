
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
   $salesCaseID = (isset($_POST['salesCaseID']) && !empty($_POST['salesCaseID'])) ?  Utility::clean_string($_POST['salesCaseID']): "";
   $orgDataID = (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ?  Utility::clean_string($_POST['orgDataID']): "";
   $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ?  Utility::clean_string($_POST['entityID']): "";
   $salesStage = (isset($_POST['salesStage']) && !empty($_POST['salesStage'])) ?  Utility::clean_string($_POST['salesStage']): "opportunities";
   $salesPersonID = (isset($_POST['salesPersonID']) && !empty($_POST['salesPersonID'])) ?  Utility::clean_string($_POST['salesPersonID']): "";
   $salesCaseContactID = (isset($_POST['salesCaseContactID']) && !empty($_POST['salesCaseContactID'])) ?  Utility::clean_string($_POST['salesCaseContactID']): "";
   $newClientNote = (isset($_POST['newClientNote']) && !empty($_POST['newClientNote'])) ?  Utility::clean_string($_POST['newClientNote']): "";
   if(!$newClientNote){
      $clientID = (isset($_POST['clientID']) && !empty($_POST['clientID'])) ?  Utility::clean_string($_POST['clientID']): "";
   } else {
      $clientName = (isset($_POST['clientName']) && !empty($_POST['clientName'])) ?  Utility::clean_string($_POST['clientName']): "";
      $clientSectorID = (isset($_POST['clientSectorID']) && !empty($_POST['clientSectorID'])) ?  Utility::clean_string($_POST['clientSectorID']): "";
      $clientIndustryID = (isset($_POST['clientIndustryID']) && !empty($_POST['clientIndustryID'])) ?  Utility::clean_string($_POST['clientIndustryID']): "";
      $clientCode = (isset($_POST['clientCode']) && !empty($_POST['clientCode'])) ?  Utility::clean_string($_POST['clientCode']):($clientName ?  Utility::clientCode($clientName) : null);
      $countryID = (isset($_POST['countryID']) && !empty($_POST['countryID'])) ?  Utility::clean_string($_POST['countryID']): "";
      $city = (isset($_POST['city']) && !empty($_POST['city'])) ?  Utility::clean_string($_POST['city']): "";
      $newClientArray = array(
         'orgDataID'=>$orgDataID,
         'entityID'=>$entityID,
         'LastUpdateByID'=>$userDetails->ID,
         'DateAdded'=>$config['currentDateTimeFormated'],
         'LastUpdate'=>$config['currentDateTimeFormated']
      );
      $clientName ? $newClientArray['clientName'] = $clientName : $errors[] = 'Client Name is required';
      $clientCode ? $newClientArray['clientCode'] = $clientCode : $errors[] = 'Client Code is required';
      $clientSectorID ? $newClientArray['clientSectorID'] = $clientSectorID : $errors[] = 'Client Sector is required';
      $clientIndustryID ? $newClientArray['clientIndustryID'] = $clientIndustryID : $errors[] = 'Client Industry is required';
      $countryID ? $newClientArray['countryID'] = $countryID : '';
      $city ? $newClientArray['city'] = $city : '';
      $newClientArray['accountOwnerID'] = $salesPersonID ?   $salesPersonID : $userDetails->ID;


      // Client Address Details (optional for Quick Add)
      $addressType = (isset($_POST['addressType']) && !empty($_POST['addressType'])) ?  Utility::clean_string($_POST['addressType']): "";
      $billingAddress = (isset($_POST['billingAddress']) && !empty($_POST['billingAddress'])) ?  Utility::clean_string($_POST['billingAddress']): "";
      $headquarters = (isset($_POST['headquarters']) && !empty($_POST['headquarters'])) ?  Utility::clean_string($_POST['headquarters']): "";
      $postalCode = (isset($_POST['postalCode']) && !empty($_POST['postalCode'])) ?  Utility::clean_string($_POST['postalCode']): "";
      $address = (isset($_POST['address']) && !empty($_POST['address'])) ?  Utility::clean_string($_POST['address']): "";

      if(!$errors) {
         if($newClientArray) {
            $newClientArray['LastUpdateByID'] = $userDetails->ID;
            if(!$DBConn->insert_data('tija_clients', $newClientArray)) {
               $errors[] = 'Error adding new Client';
            } else {
               $clientID = $DBConn->lastInsertID();
               $success = 'New Client added successfully';

               // Optionally add the client Address if any address-related fields were provided
               if ($addressType || $billingAddress || $headquarters || $postalCode || $countryID || $city || $address) {
                  $clientAddressArray = array();
                  $clientAddressArray['clientID'] = $clientID;
                  if ($addressType)     $clientAddressArray['addressType']   = $addressType;
                  if ($billingAddress)  $clientAddressArray['billingAddress'] = $billingAddress;
                  if ($headquarters)    $clientAddressArray['headquarters']   = $headquarters;
                  if ($postalCode)      $clientAddressArray['postalCode']     = $postalCode;
                  if ($countryID)       $clientAddressArray['countryID']      = $countryID;
                  if ($city)            $clientAddressArray['city']           = $city;
                  if ($address)         $clientAddressArray['address']        = $address;
                  if ($orgDataID)       $clientAddressArray['orgDataID']      = $orgDataID;
                  if ($entityID)        $clientAddressArray['entityID']       = $entityID;

                  if(!$errors && $clientAddressArray) {
                     $clientAddressArray['LastUpdateByID'] = $userDetails->ID;
                     $clientAddressArray['DateAdded'] = $config['currentDateTimeFormated'];
                     $clientAddressArray['LastUpdate'] = $config['currentDateTimeFormated'];
                     if(!$DBConn->insert_data('tija_client_addresses', $clientAddressArray)) {
                        $errors[] = 'Error adding new Client Address';
                     } else {
                        $clientAddressID = $DBConn->lastInsertID();
                        $success .= ' and Client Address added successfully';
                     }
                  }
               }

            }
         }
      }
   }

   $newSalesContactName = (isset($_POST['newSalesContactName']) && !empty($_POST['newSalesContactName'])) ?  Utility::clean_string($_POST['newSalesContactName']): "";
   if($newSalesContactName) {

      $contactTitle = (isset($_POST['contactTitle']) && !empty($_POST['contactTitle'])) ?  Utility::clean_string($_POST['contactTitle']): "";
      $contactEmail = (isset($_POST['contactEmail']) && !empty($_POST['contactEmail'])) ?  Utility::clean_string($_POST['contactEmail']): "";
      $contactPhone = (isset($_POST['contactPhone']) && !empty($_POST['contactPhone'])) ?  Utility::clean_string($_POST['contactPhone']): "";
      $contactTypeID = (isset($_POST['contactTypeID']) && !empty($_POST['contactTypeID'])) ?  Utility::clean_string($_POST['contactTypeID']): "";
      $salutationID = (isset($_POST['salutationID']) && !empty($_POST['salutationID'])) ?  Utility::clean_string($_POST['salutationID']): "1";
      $contactName = (isset($_POST['contactName']) && !empty($_POST['contactName'])) ?  Utility::clean_string($_POST['contactName']): $newSalesContactName;
      if(!$contactTitle) {
         $errors[] = 'Contact Title is required';
      }
      $salesContactArray = array(
         'contactName'=>$contactName,
         'title'=>$contactTitle,
         'salutationID'=>$salutationID? $salutationID : null,
         'contactEmail'=>$contactEmail,
         'contactPhone'=>$contactPhone,
         'contactTypeID'=>$contactTypeID ? $contactTypeID : null,
         'clientAddressID'=>isset($clientAddressID) && $clientAddressID ? $clientAddressID : null,
         'clientID'=>$clientID,
         'userID'=>$userDetails->ID,
         'LastUpdateByID'=>$userDetails->ID,
         'DateAdded'=>$config['currentDateTimeFormated'],
         'LastUpdate'=>$config['currentDateTimeFormated']
      );
      echo "<h5>New Sales Contact Name: {$salesContactArray['contactName']}</h5>";
      var_dump($salesContactArray);
      if(!$errors) {
         if($salesContactArray) {
            if(!$DBConn->insert_data('tija_client_contacts', $salesContactArray)) {
               $errors[] = 'Error adding new Sales Contact';
            } else {
               $salesCaseContactID = $DBConn->lastInsertID();
               echo "<h5>New Sales Contact ID: {$salesCaseContactID}</h5>";
               $success = 'New Sales Contact added successfully';
            }
         }
      }
   }

   $deleteSalesCase = (isset($_POST['deleteSalesCase']) && !empty($_POST['deleteSalesCase'])) ?  Utility::clean_string($_POST['deleteSalesCase']): "";
   $suspended =($deleteSalesCase && $deleteSalesCase == '1') ? 'Y' : 'N';
   $salesCaseName = (isset($_POST['salesCaseName']) && !empty($_POST['salesCaseName'])) ?  Utility::clean_string($_POST['salesCaseName']): "";
   $saleStage = (isset($_POST['saleStage']) && !empty($_POST['saleStage'])) ?  Utility::clean_string($_POST['saleStage']): "opportunity";
   $probability = (isset($_POST['probability']) && !empty($_POST['probability'])) ?  Utility::clean_string($_POST['probability']): "";
   $saleStatusLevelID = (isset($_POST['saleStatusLevelID']) && !empty($_POST['saleStatusLevelID'])) ?  Utility::clean_string($_POST['saleStatusLevelID']): "";
   $closeStatus = (isset($_POST['closeStatus']) && !empty($_POST['closeStatus'])) ?  Utility::clean_string($_POST['closeStatus']): "open";
   if($closeStatus === 'won') {
      $dateClosed = $config['currentDate'];
   } else if($closeStatus === 'lost') {
      $dateClosed = $config['currentDate'];
   } else {
      $dateClosed = '';
   }

   $businessUnitID = (isset($_POST['businessUnitID']) && !empty($_POST['businessUnitID'])) ?  Utility::clean_string($_POST['businessUnitID']): "";
   if($businessUnitID == 'newUnit') {
      $newBusinessUnit = (isset($_POST['newBusinessUnit']) && !empty($_POST['newBusinessUnit'])) ?  Utility::clean_string($_POST['newBusinessUnit']): "";
      $businessUnitArr = array('businessUnitName'=>$newBusinessUnit, 'orgDataID'=>$orgDataID, 'entityID'=>$entityID, 'LastUpdatedByID'=>$userDetails->ID);
      if(!$errors){
         if($businessUnitArr) {
            $businessUnitArr['LastUpdate']=$config['currentDateTimeFormated'];
            if(!$DBConn->insert_data('tija_business_units', $businessUnitArr)) {
               $errors[] = 'Error adding new Business Unit';
            } else {
               $businessUnitID = $DBConn->lastInsertID();
            }
         }
      }
      // $businessUnitID = Data::add_business_unit(array('businessUnitName'=>$newBusinessUnit, 'orgDataID'=>$orgDataID, 'entityID'=>$entityID), $DBConn);
   } else {
      $businessUnitID = $businessUnitID;
   }

   $saleStatusLevelID = (isset($_POST['saleStatusLevelID']) && !empty($_POST['saleStatusLevelID'])) ?  Utility::clean_string($_POST['saleStatusLevelID']): "";
   $salesCaseEstimate = (isset($_POST['salesCaseEstimate']) && !empty($_POST['salesCaseEstimate'])) ?  Utility::clean_string($_POST['salesCaseEstimate']): "";
   $probability = (isset($_POST['probability']) && !empty($_POST['probability'])) ?  Utility::clean_string($_POST['probability']): "";
   $expectedCloseDate = (isset($_POST['expectedCloseDate']) && !empty($_POST['expectedCloseDate']) &&  preg_match($config['ISODateFormat'], Utility::clean_string($_POST['expectedCloseDate']))) ?  Utility::clean_string($_POST['expectedCloseDate']): "";
   $leadSourceID = (isset($_POST['leadSourceID']) && !empty($_POST['leadSourceID'])) ?  Utility::clean_string($_POST['leadSourceID']): "";
   $salesCaseNotes = (isset($_POST['salesCaseNotes']) && !empty($_POST['salesCaseNotes'])) ?  Utility::clean_string($_POST['salesCaseNotes']): "";
   if($leadSourceID== 'newSource') {
      echo "New Lead Source";
      $newLeadSource = (isset($_POST['newLeadSource']) && !empty($_POST['newLeadSource'])) ?  Utility::clean_string($_POST['newLeadSource']): "";
      $leadSourceArr = array('leadSourceName'=>$newLeadSource, 'orgDataID'=>$orgDataID, 'entityID'=>$entityID, 'LastUpdatedByID'=>$userDetails->ID);
      if(!$errors){
         if($leadSourceArr) {
            $leadSourceArr['LastUpdate']=$config['currentDateTimeFormated'];
            if(!$DBConn->insert_data('tija_lead_sources', $leadSourceArr)) {
               $errors[] = 'Error adding new Lead Source';
            } else {
               $leadSourceID = $DBConn->lastInsertID();
            }
         }
      }
   } else {
      $leadSourceID = $leadSourceID;
   }

   if(!$salesCaseID) {
      $salesCaseName ? $details['salesCaseName'] = $salesCaseName : $errors[] = 'Case Name is required';
      $clientID ? $details['clientID'] = $clientID : $errors[] = 'Client is required';
      $salesCaseContactID ? $details['salesCaseContactID'] = $salesCaseContactID : "";
      $orgDataID ? $details['orgDataID'] = $orgDataID : $errors[] = 'Organization Data is required';
      $entityID ? $details['entityID'] = $entityID : $errors[] = 'Entity is required';
      $businessUnitID ? $details['businessUnitID'] = $businessUnitID : $errors[] = 'Business Unit is required';
      $salesPersonID ? $details['salesPersonID'] = $salesPersonID : $errors[] = 'salesPerson is required';
      $saleStatusLevelID ? $details['saleStatusLevelID'] = $saleStatusLevelID : $errors[] = 'Status Level is required';
      $salesStage ? $details['saleStage'] = $salesStage : $errors[] = 'Sales Stage is required';
      $salesCaseEstimate ? $details['salesCaseEstimate'] = $salesCaseEstimate : $errors[] = 'Sale Estimate is required';
      $probability ? $details['probability'] = $probability : $errors[] = 'Probability is required';
      $expectedCloseDate ? $details['expectedCloseDate'] = $expectedCloseDate : $errors[] = 'Expected Close Date is required';
      $leadSourceID ? $details['leadSourceID'] = $leadSourceID : $errors[] = 'Lead Source is required';
      $closeStatus ? $details['closeStatus'] = $closeStatus : "";
      $dateClosed ? $details['dateClosed'] = $dateClosed : "";
      if(!$errors) {
         if($details){
            $details['DateAdded'] = $config['currentDateTimeFormated'];
            $details['LastUpdate'] = $config['currentDateTimeFormated'];
            $details['LastUpdatedByID'] = $userDetails->ID;
            if(!$DBConn->insert_data('tija_sales_cases', $details)) {
               $errors[] = 'Error adding new Sales Case';
            } else {
               $salesCaseID = $DBConn->lastInsertID();
               $returnURL = "?s=user&ss=sales&p=sale_details&saleid={$salesCaseID}";
               $success = 'Sales Case added successfully';
               $employeeDetails = Employee::employees(array('ID'=>$salesPersonID), true, $DBConn);
               $notificationArr = array(
                  'employeeID' => $salesPersonID,
                  'approverID' => $userDetails->ID,
                  'segmentType'=> "sales",
                  'segmentID' => $salesCaseID,
                  'notificationNotes' => "<p>You have been assigned to the sales case <strong>{$salesCaseName}</strong> by {$employeeDetails->employeeNameWithInitials}</p>
                                          <p><a href='{$config['siteURL']}html/?s=user&ss=sales&p=sale_details&saleid={$salesCaseID}'>View Sales Case</a></p>
                                          <p> You have been assigned to this sales case.</p>",
                  'notificationType' => "sales_case_add",
                  'notificationStatus' => 'unread',
                  'originatorUserID' => $userDetails->ID,
                  'targetUserID' => $salesPersonID,

               );
               if(!$DBConn->insert_data('tija_notifications', $notificationArr)) {
                  $errors[] = 'Failed to add notification for sales case assignment';
               } else {
                  $success = 'Sales Case added successfully and notification created successfully';
               }
            }
         }
      }
   } else {
      $salesCaseDetails = Sales::sales_cases(array('salesCaseID'=>$salesCaseID), true, $DBConn);
      $orgDataID= $salesCaseDetails->orgDataID;
      $entityID= $salesCaseDetails->entityID;
      $orgDataID && $orgDataID != $salesCaseDetails->orgDataID ? $changes['orgDataID'] = $orgDataID : "";

      $entityID && $entityID != $salesCaseDetails->entityID ? $changes['entityID'] = $entityID : "";
      $salesPersonID && $salesPersonID != $salesCaseDetails->salesPersonID ? $changes['salesPersonID'] = $salesPersonID : "";
      $salesCaseName && $salesCaseName != $salesCaseDetails->salesCaseName ? $changes['salesCaseName'] = $salesCaseName : "";
      $clientID && $clientID != $salesCaseDetails->clientID ? $changes['clientID'] = $clientID : "";
      $saleStage && $saleStage != $salesCaseDetails->saleStage ? $changes['saleStage'] = $saleStage : "";

      $businessUnitID && $businessUnitID != $salesCaseDetails->businessUnitID ? $changes['businessUnitID'] = $businessUnitID : "";
      $saleStatusLevelID && $saleStatusLevelID != $salesCaseDetails->saleStatusLevelID ? $changes['saleStatusLevelID'] = $saleStatusLevelID : "";
      $salesCaseEstimate && $salesCaseEstimate != $salesCaseDetails->salesCaseEstimate ? $changes['salesCaseEstimate'] = $salesCaseEstimate : "";
      $probability && $probability != $salesCaseDetails->probability ? $changes['probability'] = $probability : "";
      $expectedCloseDate && $expectedCloseDate != $salesCaseDetails->expectedCloseDate ? $changes['expectedCloseDate'] = $expectedCloseDate : "";
      $leadSourceID && $leadSourceID != $salesCaseDetails->leadSourceID ? $changes['leadSourceID'] = $leadSourceID : "";
      ($suspended && $salesCaseDetails->Suspended != $suspended ) ? $changes['Suspended'] = $suspended : "";
      $closeStatus && $salesCaseDetails->closeStatus != $closeStatus ? $changes['closeStatus'] = $closeStatus : "";
      $dateClosed && $salesCaseDetails->dateClosed != $dateClosed ? $changes['dateClosed'] = $dateClosed : "";
      $salesCaseContactID && $salesCaseContactID != $salesCaseDetails->salesCaseContactID ? $changes['salesCaseContactID'] = $salesCaseContactID : "";
      $salesCaseNotes && $salesCaseNotes != $salesCaseDetails->salesCaseNotes ? $changes['salesCaseNotes'] = $salesCaseNotes : "";
      if(isset($changes['saleStatusLevelID']) && $changes['saleStatusLevelID'] ) {
         $salesLevelDetails = Sales::sales_status_levels(array('saleStatusLevelID'=>$changes['saleStatusLevelID']), true, $DBConn);
         $changes['probability'] = $salesLevelDetails->levelPercentage;
      }
      if(!$errors) {
         if($changes) {
            $changes['LastUpdate'] = $config['currentDateTimeFormated'];
            $changes['LastUpdatedByID'] = $userDetails->ID;
            if(!$DBConn->update_table('tija_sales_cases', $changes, array('salesCaseID'=>$salesCaseID))) {
               $errors[] = 'Error updating Sales Case';
            } else {
               $success = 'Sales Case updated successfully';
               if($saleStage == 'order' && $closeStatus == 'won') {
                  $success .= " and marked as won";
                  $returnURL = "?s=user&ss=projects&p=home&orderid={$salesCaseID}&state=order&orgDataID={$orgDataID}&entityID={$entityID}";
               } else if($saleStage == 'lost' && $closeStatus == 'lost') {
                  $success .= " and marked as lost";
               }
            }
         }
      }
   }

   // $returnURL= !$returnURL ?  Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home') : $returnURL;
   if(!$returnURL) {
      $returnURL = Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
   }
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
 header("location:{$base}html/{$returnURL}");