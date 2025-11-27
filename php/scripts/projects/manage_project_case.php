
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

   $projectID = (isset($_POST['projectID']) && !empty($_POST['projectID'])) ?  Utility::clean_string($_POST['projectID']): "";
   $clientID = (isset($_POST['clientID']) && !empty($_POST['clientID'])) ?  Utility::clean_string($_POST['clientID']): "";
   $ProjectName = (isset($_POST['ProjectName']) && !empty($_POST['ProjectName'])) ?  Utility::clean_string($_POST['ProjectName']): "";
   $orgDataID = (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ?  Utility::clean_string($_POST['orgDataID']): "";
   $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ?  Utility::clean_string($_POST['entityID']): "";
   $caseID = (isset($_POST['caseID']) && !empty($_POST['caseID'])) ?  Utility::clean_string($_POST['caseID']): "";
   $projectCode = (isset($_POST['projectCode']) && !empty($_POST['projectCode'])) ?  Utility::clean_string($_POST['projectCode']): Utility::generate_account_code($ProjectName);
   $projectName = (isset($_POST['projectName']) && !empty($_POST['projectName'])) ?  Utility::clean_string($_POST['projectName']): "";
   $projectOwnerID = (isset($_POST['projectOwnerID']) && !empty($_POST['projectOwnerID'])) ?  Utility::clean_string($_POST['projectOwnerID']): "";
   $projectTimeline = (isset($_POST['projectTimeline']) && !empty($_POST['projectTimeline'])) ?  Utility::clean_string($_POST['projectTimeline']): "";
   $projectStart = (isset($_POST['projectStart']) && !empty($_POST['projectStart'])) ?  Utility::clean_string($_POST['projectStart']): "";
   $projectClose = (isset($_POST['projectClose']) && !empty($_POST['projectClose'])) ?  Utility::clean_string($_POST['projectClose']): "";
   
   $projectTypeID = (isset($_POST['projectTypeID']) && !empty($_POST['projectTypeID'])) ?  Utility::clean_string($_POST['projectTypeID']): "";
   $action = (isset($_POST['action']) && !empty($_POST['action'])) ?  Utility::clean_string($_POST['action']): "";

   var_dump($projectStart);
   var_dump($projectClose);

  
   if($projectTimeline) {
      $projectTimeline = explode(" to ", $projectTimeline);
      $projectStart = utility::clean_string($projectTimeline[0]);
      $projectClose =  utility::clean_string($projectTimeline[1]);
   } 
   $projectStart =(preg_match($config['ISODateFormat'], Utility::clean_string($projectStart))) ?  Utility::clean_string($projectStart): "";
   $projectClose = ( preg_match($config['ISODateFormat'], Utility::clean_string($projectClose))) ?  Utility::clean_string($projectClose): "";

   if(!$projectStart && !$projectClose ) {
      $errors[] = 'Project Start Date or Project End Date is required';
   }

   $billingRateID = (isset($_POST['billingRateID']) && !empty($_POST['billingRateID'])) ?  Utility::clean_string($_POST['billingRateID']): "";
   $roundingoff = (isset($_POST['roundingoff']) && !empty($_POST['roundingoff'])) ?  Utility::clean_string($_POST['roundingoff']): "";
   $roundingInterval = (isset($_POST['roundingInterval']) && !empty($_POST['roundingInterval'])) ?  Utility::clean_string($_POST['roundingInterval']): "";
   $businessUnitID = (isset($_POST['businessUnitID']) && !empty($_POST['businessUnitID'])) ?  Utility::clean_string($_POST['businessUnitID']): "";
   $newbusinessUnit = (isset($_POST['newbusinessUnit']) && !empty($_POST['newbusinessUnit'])) ?  Utility::clean_string($_POST['newbusinessUnit']): "";
   if ($businessUnitID=== "newbusinessUnit") {
		if (isset($_POST['newbusinessUnit']) && !empty($_POST['newbusinessUnit'])) {
			$newbusinessUnit= Utility::clean_string($_POST['newbusinessUnit']);
			if (count($errors) === 0) {
				if ($newbusinessUnit) {
					if (!$DBConn->insert_data("tija_business_units", array("businessUnitName"=> $newbusinessUnit, "orgDataID"=> $orgDataID, "entityID"=> $entityID,'businessUnitDescription'=>$newbusinessUnit ))) {
						$errors[]="<span class't600'> ERROR!</span> Failed to update business Unit to the database"; 
						
					} else {
						$details['businessUnitID'] = $DBConn->lastInsertID();
					}
				}
			}
		}		
	} else {
		$details['businessUnitID'] = $businessUnitID;
	} 

   $projectValue = (isset($_POST['projectValue']) && !empty($_POST['projectValue'])) ?  Utility::clean_string($_POST['projectValue']): "";
   $saleStatus = (isset($_POST['SaleStatus']) && !empty($_POST['SaleStatus'])) ?  Utility::clean_string($_POST['SaleStatus']): "";
   $orderDate = (isset($_POST['orderDate']) && !empty($_POST['orderDate'])) ?  Utility::clean_string($_POST['orderDate']): "";
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
      $newClientArray['accountOwnerID'] = $projectOwnerID ?   $projectOwnerID : $userDetails->ID;


      // Client Address Details
      $addressType = (isset($_POST['addressType']) && !empty($_POST['addressType'])) ?  Utility::clean_string($_POST['addressType']): "";
      $billingAddress = (isset($_POST['billingAddress']) && !empty($_POST['billingAddress'])) ?  Utility::clean_string($_POST['billingAddress']): "";

      $headquarters = (isset($_POST['headquarters']) && !empty($_POST['headquarters'])) ?  Utility::clean_string($_POST['headquarters']): "";
      $postalCode = (isset($_POST['postalCode']) && !empty($_POST['postalCode'])) ?  Utility::clean_string($_POST['postalCode']): "";
      $address = (isset($_POST['address']) && !empty($_POST['address'])) ?  Utility::clean_string($_POST['address']): "";
      

      if(!$errors) {
         if($newClientArray) {
            var_dump($newClientArray);
            $newClientArray['LastUpdateByID'] = $userDetails->ID;
            if(!$DBConn->insert_data('tija_clients', $newClientArray)) {
               $errors[] = 'Error adding new Client';
            } else {
               $clientID = $DBConn->lastInsertID();
               $success = 'New Client added successfully';
               // Add the client Address 
               $clientAddressArray=array();
               $clientID ? $clientAddressArray['clientID'] = $clientID : $errors[] = 'Client ID is required';
               $addressType ? $clientAddressArray['addressType'] = $addressType : $errors[] = 'Address Type is required';
               $billingAddress ? $clientAddressArray['billingAddress'] = $billingAddress : "";
               $headquarters ? $clientAddressArray['headquarters'] = $headquarters : "";
               $postalCode ? $clientAddressArray['postalCode'] = $postalCode : "";
               $countryID ? $clientAddressArray['countryID'] = $countryID : "";
               $city ? $clientAddressArray['city'] = $city : "";
               $address ? $clientAddressArray['address'] = $address : $errors[] = 'Address is required';
               $orgDataID ? $clientAddressArray['orgDataID'] = $orgDataID : $errors[] = 'Organization Data is required';
               $entityID ? $clientAddressArray['entityID'] = $entityID : $errors[] = 'Entity is required';
               if(!$errors) {
                  if($clientAddressArray) {
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

   if(!$projectID) {
      $clientID ? $details['clientID'] = $clientID : $errors[] = 'Client ID is required';
      $projectName ? $details['projectName'] = $projectName : $errors[] = 'Project Name is required';
      $projectOwnerID ? $details['projectOwnerID'] = $projectOwnerID : $errors[] = 'Project Owner is required';
      $projectCode ? $details['projectCode'] = $projectCode : $errors[] = 'Project Code is required';
      $projectStart ? $details['projectStart'] = $projectStart : $errors[] = 'Project Start Date is required';
      $projectClose ? $details['projectClose'] = $projectClose : $errors[] = 'Project Close Date is required';
      $billingRateID ? $details['billingRateID'] = $billingRateID : $errors[] = 'Billing Rate is required';
      $roundingoff ? $details['roundingoff'] = $roundingoff : $errors[] = 'Rounding Off is required';
      $roundingInterval ? $details['roundingInterval'] = $roundingInterval : '';
      $businessUnitID ? $details['businessUnitID'] = $businessUnitID : $errors[] = 'Business Unit is required';
      $projectValue ? $details['projectValue'] = $projectValue : $errors[] = 'Project Value is required';
      //$saleStatus ? $details['saleStatus'] =$saleStatus : '';
      $orderDate ? $details['orderDate'] = $orderDate : '';
      $orgDataID ? $details['orgDataID'] = $orgDataID : $errors[] = 'Organization Data is required';
      $entityID ? $details['entityID'] = $entityID : $errors[] = 'Entity is required';
      $caseID ? $details['caseID'] = $caseID : '';
      
     
      $details['projectStatus'] = 'Active';
      $projectTypeID ? $details['projectTypeID'] = $projectTypeID:"" ;

    
      

      var_dump($details);

      if(!$errors) {
         if($details) {
            $details['DateAdded'] = $config['currentDateTimeFormated'];
            $details['LastUpdate'] = $config['currentDateTimeFormated'];
            $details['LastUpdatedByID'] = $userDetails->ID;
            if(!$DBConn->insert_data('tija_projects', $details)) {
               $errors[] = 'Error adding new Project';
            } else {
               $projectID = $DBConn->lastInsertID();
               $success = "New Project Added";
               $projectDetails = Projects::projects_full(array('projectID'=>$projectID), true, $DBConn);
               echo "<h4>New Project Details: </h4>";
               var_dump($projectDetails);
               $projec = $action == 'createFromSale' ? "sale_" : "";
               $returnURL = "?s=user&ss=projects&p=project&pid={$projectID}&action=createFromSale";
               $projectOwnerDetails = Employee::employees(array('ID'=>$projectOwnerID), true, $DBConn);
               $assignorDetails = Employee::employees(array('ID'=>$userDetails->ID), true, $DBConn);
               $notificationArr = array(
                  'employeeID' => $projectOwnerID,
                  'approverID' => $userDetails->ID,
                  'segmentType'=> "projects",
                  "segmentID" => $projectID,
                  "notificationNotes" => "<p>New Project {$details['projectName']} has been created by {$assignorDetails->employeeNameWithInitials}</p>
                                          <p> you have been added as project Owner</p>
                                          <p><a href='{$base}html/?s=user&ss=projects&p=project&pid={$projectID}'>View Project</a></p>
                                          <p> You have been assigned as the Project Owner.</p>",
                  'notificationType' => "{$details['projectName']}_new_project_assigned",
                  'notificationStatus' => 'unread',
                  'originatorUserID' => $userDetails->ID,
                  'targetUserID' => $projectOwnerID,
                  
               );
               if($notificationArr) {
                  if(!$DBConn->insert_data('tija_notifications', $notificationArr)) {
                     $errors[] = 'Failed to create notification for project assignment';
                  } else {
                     $success .= ' and notification created successfully';
                  }
               }
            }
         }
      }

   } else {
      $projectDetails = Projects::projects_mini(array('projectID'=>$projectID), true, $DBConn);
      var_dump($projectDetails);
      $projectCode && !$projectDetails->projectCode && $projectCode != $projectDetails->projectCode ? $changes['projectCode'] = $projectCode : '';
      $projectName && $projectName != $projectDetails->projectName ? $changes['projectName'] = $projectName : '';
      $projectOwnerID && $projectOwnerID != $projectDetails->projectOwnerID ? $changes['projectOwnerID'] = $projectOwnerID : '';
      $projectStart && $projectStart != $projectDetails->projectStart ? $changes['projectStart'] = $projectStart : '';
      $projectClose && $projectClose != $projectDetails->projectClose ? $changes['projectClose'] = $projectClose : '';
      $billingRateID && $billingRateID != $projectDetails->billingRateID ? $changes['billingRateID'] = $billingRate : '';
      $roundingoff && $roundingoff != $projectDetails->roundingoff ? $changes['roundingoff'] = $roundingoff : '';
      $roundingInterval && $roundingInterval != $projectDetails->roundingInterval ? $changes['roundingInterval'] = $roundingInterval : '';
      $businessUnitID && $businessUnitID != $projectDetails->businessUnitID ? $changes['businessUnitID'] = $businessUnitID : '';
      $projectValue && $projectValue != $projectDetails->projectValue ? $changes['projectValue'] = $projectValue : '';
      (isset($SalesStatus) && $saleStatus &&$saleStatus != $projectDetails->saleStatus) ? $changes['saleStatus'] =$saleStatus : ''; 
      $orderDate && $orderDate != $projectDetails->orderDate ? $changes['orderDate'] = $orderDate : '';
      $orgDataID && $orgDataID != $projectDetails->orgDataID ? $changes['orgDataID'] = $orgDataID : '';
      $entityID && $entityID != $projectDetails->entityID ? $changes['entityID'] = $entityID : '';
      $caseID && $caseID != $projectDetails->caseID ? $changes['caseID'] = $caseID : '';
      $clientID && $clientID != $projectDetails->clientID ? $changes['clientID'] = $clientID : '';
      $projectTypeID && $projectTypeID != $projectDetails->projectTypeID ? $changes['projectTypeID'] = $projectTypeID : '';
    
      echo "<h4>Changes: </h4>";
      var_dump($changes);
      if($changes) {
         $changes['LastUpdate'] = $config['currentDateTimeFormated'];
         $changes['LastUpdatedByID'] = $userDetails->ID;
         if(!$DBConn->update_table('tija_projects', $changes, array('projectID'=>$projectID))) {
            $errors[] = 'Error updating Project';
         } else {
            $success = "Project Updated";
         }
      }
   }

   var_dump($errors);
   if(!$returnURL) { 
      $returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=projects&p=project&pid={$projectID}');
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