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

	$clientName = (isset($_POST['clientName']) && !empty($_POST['clientName'])) ? $_POST['clientName'] : null;
	$accountOwnerID = (isset($_POST['accountOwnerID']) && !empty($_POST['accountOwnerID'])) ? $_POST['accountOwnerID'] : null;
	$clientLevelID = (isset($_POST['clientLevelID']) && !empty($_POST['clientLevelID'])) ? $_POST['clientLevelID'] : null;
	$clientIndustryID = (isset($_POST['clientIndustryID']) && !empty($_POST['clientIndustryID'])) ? $_POST['clientIndustryID'] : null;
	$address = (isset($_POST['address']) && !empty($_POST['address'])) ? $_POST['address'] : null;
	$postalCode = (isset($_POST['postalCode']) && !empty($_POST['postalCode'])) ? $_POST['postalCode'] : null;
	$city = (isset($_POST['city']) && !empty($_POST['city'])) ? $_POST['city'] : null;
	$country = (isset($_POST['country']) && !empty($_POST['country'])) ? $_POST['country'] : null;
	$clientID = (isset($_POST['clientID']) && !empty($_POST['clientID'])) ? $_POST['clientID'] : null;
	$addressType = (isset($_POST['addressType']) && !empty($_POST['addressType'])) ? $_POST['addressType'] : null;
	$billingAddress = (isset($_POST['BillingAddress']) && !empty($_POST['BillingAddress'])) ? $_POST['BillingAddress'] : null;
	$headquarters = (isset($_POST['Headquarters']) && !empty($_POST['Headquarters'])) ? $_POST['Headquarters'] : null;

	$clientCode= (isset($_POST['clientCode']) && !empty($_POST['clientCode'])) ? Utility::clean_string($_POST['clientCode']) : ($clientName ?  Utility::clientCode($clientName) : null);
	$orgDataID = (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ? $_POST['orgDataID'] : null;
	$entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ? $_POST['entityID'] : null;
	$vatNumber = (isset($_POST['vatNumber']) && !empty($_POST['vatNumber'])) ? $_POST['vatNumber'] : null;
	$clientDescription = (isset($_POST['clientDescription']) && !empty($_POST['clientDescription'])) ? $_POST['clientDescription'] : null;
	if($clientIndustryID){
		$clientIndustryDetails = Data::tija_industry (array('industryID'=>$clientIndustryID), true, $DBConn);
		var_dump($clientIndustryDetails);
		if($clientIndustryDetails) {
			$clientSectorID = $clientIndustryDetails->sectorID;
		} else {
			$errors[] = 'Invalid Client Industry';
		}
	}


	if(!$clientID) {
		$clientName ? $details['clientName'] = $clientName : $errors[] = 'Client Name is required';
		$accountOwnerID ? $details['accountOwnerID'] = $accountOwnerID : $errors[] = 'Account Owner is required';
		
		$clientCode ? $details['clientCode'] = $clientCode : $errors[] = 'Client Code is required';
		$orgDataID ? $details['orgDataID'] = $orgDataID : $errors[] = 'Organization is required';
		$entityID ? $details['entityID'] = $entityID : $errors[] = 'Entity is required';
		$vatNumber ? $details['vatNumber'] = $vatNumber : '';
		$clientDescription ? $details['clientDescription'] = $clientDescription : '';
		var_dump($details);
		if(count($errors) == 0) {
			if($details) {
				$details['LastUpdateByID'] = $userDetails->ID;
				$details['LastUpdate'] = $config['currentDateTimeFormated'];
				if(!$DBConn->insert_data('tija_clients', $details)) {
					$errors[] = 'Failed to add client to the database';
				} else {				
					$clientID = $DBConn->lastInsertID();
					$returnURL = "?s=user&ss=clients&p=client_details&client_id={$clientID}";
					$addressDetails['clientID'] = $clientID;
					if($address) {
						$address ? $addressDetails['address'] = $address : $errors[] = 'Address is required';
						$postalCode ? $addressDetails['postalCode'] = $postalCode : '';
						$city ? $addressDetails['city'] = $city : $errors[] = 'City is required. Please input your nearest city or town';
						$country ? $addressDetails['countryID'] = $country : $errors[] = 'Country is required';
						$addressType ? $addressDetails['addressType'] = $addressType : $errors[] = 'Address Type is required';
						$headquarters && $headquarters == 'Headquarters' ? $addressDetails['headquarters'] = "Y" : 'N';
						$billingAddress && $billingAddress == 'BillingAddress' ? $addressDetails['billingAddress'] = "Y" : 'N';
						$orgDataID ? $addressDetails['orgDataID'] = $orgDataID : $errors[] = 'Organization is required';
						$entityID ? $addressDetails['entityID'] = $entityID : $errors[] = 'Entity is required';
					
						$clientLevelID ? $addressDetails['clientLevelID'] = $clientLevelID : '';
						$clientIndustryID ? $addressDetails['clientIndustryID'] = $clientIndustryID : '';
					
						
						if(count($errors) == 0) {
							$addressDetails['LastUpdateByID'] = $userDetails->ID;
							$addressDetails['LastUpdate'] = $config['currentDateTimeFormated'];
							var_dump($addressDetails);
							if(!$DBConn->insert_data('tija_client_addresses', $addressDetails)) {
								$errors[] = 'Failed to add client address to the database';
							} else {
								$addressID = $DBConn->lastInsertID();
								$changes[] = "Client {$clientName} added successfully";
								$success = "Client {$clientName} added successfully";
							}
						}
					}

					if($accountOwnerID) {
						$clientRelationshipDetails = array();
						$clientRelationshipDetails['clientID'] = $clientID;
						$clientRelationshipDetails['employeeID'] = $accountOwnerID;
						$clientRelationshipDetails['clientRelationshipType'] = 'engagementPartner';
						$clientRelationshipDetails['LastUpdateByID'] = $userDetails->ID;
						$clientRelationshipDetails['LastUpdate'] = $config['currentDateTimeFormated'];

						if(!$DBConn->insert_data('client_relationship_assignments', $clientRelationshipDetails)) {
							$errors[] = 'Failed to add client relationship to the database';
						} else {							
							$success = "Client {$clientName} relationship added successfully";
							$employeeDetails = Employee::employees(array('ID'=>$accountOwnerID), true, $DBConn);
							$notificationArr = array(
								'employeeID' => $accountOwnerID,
								'approverID' => $userDetails->ID,
								'segmentType'=> "clients",
								'segmentID' => $clientID,
								'notificationNotes' => "<p>You have been added as an engagement partner for client {$clientName} by {$employeeDetails->employeeNameWithInitials}</p>
												<p><a href='{$config['siteURL']}html/?s=user&ss=clients&p=client_details&client_id{$clientID}'>View Client</a></p>
												<p> You have been assigned to this client as an engagement partner.</p>",
								'notificationType' => "clients_engagement_partner_add",
								'notificationStatus' => 'unread',
								'originatorUserID' => $userDetails->ID,
								'targetUserID' => $accountOwnerID,
								
							);
							if(!$DBConn->insert_data('tija_notifications', $notificationArr)) {
								$errors[] = 'Failed to add notification for client relationship';
							} else {
								$success = "Client {$clientName} relationship added successfully and notification created successfully";
							}
							$returnURL = "?s=user&ss=clients&p=client_details&client_id={$clientID}";
						}

					}
					// $primaryContactDetails = array();
					// $primaryContactDetails['clientID'] = $clientID;
					// $primaryContactDetails['userID'] = $accountOwnerID;
					// $primaryContactDetails['contactName'] = $clientContactName;
					// $primaryContactDetails['contactEmail'] = $clientContactEmail;
					// $primaryContactDetails['LastUpdateByID'] = $userDetails->ID;
					// $primaryContactDetails['LastUpdate'] = $config['currentDateTimeFormated'];
					// if(!$DBConn->insert_data('tija_client_contacts', $primaryContactDetails)) {
					// 	$errors[] = 'Failed to add client primary contact to the database';
					// } else {
					// 	$primaryContactID = $DBConn->lastInsertID();						
					// 	$success = "Client {$clientName} primary contact added successfully";
					// }
				}
			}
		}
	} else {
    	$clientDetails = Client::clients(['clientID'=>$clientID], true, $DBConn);
		$clientChanges = array();
		var_dump($clientDetails);
		$clientName && $clientName != $clientDetails->clientName ? $clientChanges['clientName'] = $clientName : null;
		$accountOwnerID && $accountOwnerID != $clientDetails->accountOwnerID ? $clientChanges['accountOwnerID'] = $accountOwnerID : null;
		
		$orgDataID && $orgDataID != $clientDetails->orgDataID ? $clientChanges['orgDataID'] = $orgDataID : null;
		$entityID && $entityID != $clientDetails->entityID ? $clientChanges['entityID'] = $entityID : null;
		$vatNumber && $vatNumber != $clientDetails->vatNumber ? $clientChanges['vatNumber'] = $vatNumber : null;
		$clientCode && $clientCode != $clientDetails->clientCode ? $clientChanges['clientCode'] = $clientCode : null;
		$clientDescription && $clientDescription != $clientDetails->clientDescription ? $clientChanges['clientDescription'] = $clientDescription : null;
		$clientLevelID && $clientLevelID != $clientDetails->clientLevelID ? $clientChanges['clientLevelID'] = $clientLevelID : null;
		$clientIndustryID && $clientIndustryID != $clientDetails->clientIndustryID ? $clientChanges['clientIndustryID'] = $clientIndustryID : null;
		$clientSectorID && $clientSectorID != $clientDetails->clientSectorID ? $clientChanges['clientSectorID'] = $clientSectorID : null;
		var_dump($clientChanges);
		if($clientChanges) {
			$clientChanges['LastUpdateByID'] = $userDetails->ID;
			$clientChanges['LastUpdate'] = $config['currentDateTimeFormated'];
			if(!$DBConn->update_table('tija_clients', $clientChanges, array('clientID'=>$clientID))) {
				$errors[] = 'Failed to update client details';
			} else {
				// $changes[] = "Client {$clientName} details updated successfully";
				$success = "Client {$clientName} details updated successfully";
				$returnURL = "?s=user&ss=clients&p=client_details&client_id={$clientID}";
				if($accountOwnerID && $accountOwnerID != $clientDetails->accountOwnerID) {
					$employeeDetails = Employee::employees(array('ID'=>$accountOwnerID), true, $DBConn);
					$notificationArr = array(
						'employeeID' => $accountOwnerID,
						'approverID' => $userDetails->ID,
						'segmentType'=> "clients",
						'segmentID' => $clientID,
						'notificationNotes' => "<p>You have been added as an engagement partner for client {$clientName} by {$employeeDetails->employeeNameWithInitials}</p>
												<p><a href='{$config['siteURL']}html/?s=user&ss=clients&p=client_details&client_id={$clientID}'>View Client</a></p>
												<p> You have been assigned to this client as an engagement partner.</p>",
						'notificationType' => "clients_engagement_partner_add",
						'notificationStatus' => 'unread',
						'originatorUserID' => $userDetails->ID,
						'targetUserID' => $accountOwnerID,
						
					);
					if(!$DBConn->insert_data('tija_notifications', $notificationArr)) {
						$errors[] = 'Failed to add notification for client relationship';
					} else {
						$success = "Client {$clientName} relationship added successfully and notification created successfully";
					}
				}
					
			}
		}
	
  }

  var_dump($errors);

} else { 
   $errors[] = 'You need to log in as a valid administrator to do that.';
}
if(!$returnURL) {
$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
}
  var_dump($returnURL);
if (count($errors) == 0) {
  $DBConn->commit();
  $messages = array(array('Text'=>"{$success}", 'Type'=>'success'));
} else {
   $DBConn->rollback();
   $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
}
$_SESSION['FlashMessages'] = serialize($messages);
header("location:{$base}html/{$returnURL}");
?>