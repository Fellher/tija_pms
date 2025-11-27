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
	$saleID = (isset($_POST['saleID']) && !empty($_POST['saleID'])) ? Utility::clean_string($_POST['saleID']) : "";

	(isset($_POST['saleStatus']) && !empty($_POST['saleStatus'])) ? $details['saleStatus'] = Utility::clean_string($_POST['saleStatus']) : '';
	(isset($_POST['salesPersonID']) && !empty($_POST['salesPersonID'])) ? $details['salesPersonID'] = Utility::clean_string($_POST['salesPersonID']) : '';
	(isset($_POST['saleEstimate']) && !empty($_POST['saleEstimate'])) ? $details['saleEstimate'] = Utility::clean_string($_POST['saleEstimate']) : '';
	(isset($_POST['instanceID']) && !empty($_POST['instanceID'])) ? $details['instanceID'] = Utility::clean_string($_POST['instanceID']) : '';
	
	(isset($_POST['probability']) && !empty($_POST['probability'])) ? $details['probability'] = Utility::clean_string($_POST['probability']) : '';
	(isset($_POST['caseID']) && !empty($_POST['caseID'])) ? $details['caseID'] = Utility::clean_string($_POST['caseID']) : '';


	(isset($_POST['clientID']) && !empty($_POST['clientID'])) ? $details['clientID'] = Utility::clean_string($_POST['clientID']) : '';

	$closeStatus = (isset($_POST['orderType']) && !empty($_POST['orderType'])) ? Utility::clean_string($_POST['orderType']) : "";

	if (isset($details['clientID']) && $details['clientID'] ===  'newClient') {
		$clientArr= array();
		(isset($_POST['clientName']) && !empty($_POST['clientName'])) ? $clientArr['clientName'] = Utility::clean_string($_POST['clientName']) : $errors[] = "Please submit valid client name";
		$clientArr['clientCode'] =(isset($_POST['clientCode']) && !empty($_POST['clientCode'])) ? Utility::clean_string($_POST['clientCode']) : "SBSL-".Utility::generateRandomString(5);
		$clientArr['accountOwnerID'] = (isset($_POST['accountOwnerID']) && !empty($_POST['accountOwnerID'])) ?  Utility::clean_string($_POST['accountOwnerID']) : $userDetails->ID; 
		(isset($_POST['contactEmail']) && !empty($_POST['contactEmail'])) ? $clientArr['contactEmail'] : $errors[] = "Please submit valid client Email";
		(isset($_POST['contactName']) && !empty($_POST['contactName'])) ? $clientArr['contactName'] : $errors[] = "Please submit valid client Contact Name";

		if (count($errors) === 0) {
			if ($clientArr) {
				if (!$DBConn->insert_data("tija_clients", $clientArr)) {
					$errors[] = "<span class't600'> ERROR! </span> Unable to update client details to the database";					
				} else {
					$details['clientID'] = $DBConn->lastInsertID();
				}				
			}			
		}
	}

	(isset($_POST['businessUnitID']) && !empty($_POST['businessUnitID'])) ? $details['businessUnitID'] = Utility::clean_string($_POST['businessUnitID']) : '';

	if (isset($details['businessUnitID']) && ($details['businessUnitID'] === "newUnit")){
		$unitArr = array();
		(isset($_POST['newBusinessUnit']) && !empty($_POST['newBusinessUnit'])) ? $unitArr['businessUnitName'] =  Utility::clean_string($_POST['newBusinessUnit']) : $errors[]= "Please submit valid name for new unit";

		$unitArr['instanceID'] = $details['instanceID'];

		if (count($errors) ==- 0) {
			if ($unitArr) {
				if (!$DBConn->insert_data("tija_business_units", $unitArr)) {
					$errors[] = "<span class't600'> ERROR! </span> Unable to save new business unit   to the database";	
				} else {
					$details['businessUnitID'] = $DBConn->lastInsertID();
				}
				
			}
			
		}

		
	}


	(isset($_POST['expectedCloseDate']) && !empty($_POST['expectedCloseDate']) && preg_match($config['ISODateFormat'], Utility::clean_string($_POST['expectedCloseDate']))) ? $details['expectedCloseDate'] = Utility::clean_string($_POST['expectedCloseDate']) : '';
	(isset($_POST['dateClosed']) && !empty($_POST['dateClosed']) && preg_match($config['ISODateFormat'], Utility::clean_string($_POST['dateClosed']))) ? $details['dateClosed'] = Utility::clean_string($_POST['dateClosed']) : '';
	(isset($_POST['leadSourceID']) && !empty($_POST['leadSourceID'])) ? $details['leadSourceID'] = Utility::clean_string($_POST['leadSourceID']) : '';

	if (isset($details['leadSourceID']) && ($details['leadSourceID'] === 'newSource') ) {
		(isset($_POST['newLeadSource']) && !empty($_POST['newLeadSource']) ) ? $newLeadSource = Utility::clean_string($_POST['newLeadSource']) : $errors[] = "Please submit valid Name for new Lead source ";
		if (isset($newLeadSource) && !empty($newLeadSource)) {
		 	if (count($errors) === "") {
		 		if ($newLeadSource) {
		 			if (!$DBConn->insert_data("sbsl_lead_source" , array("leadSourceName"=>$newLeadSource, "instanceID"=> $details['instanceID']))) {
		 				$errors[]= "<span class't600'> ERROR! </span> Unable to save new Lead source details to the database";
		 			} else {
		 				$details['leadSourceID'] = $DBConn->lastInsertID();
		 			}
		 			
		 		}
		 	}
		 } 

		
	}
	(isset($_POST['closeStatus']) && !empty($_POST['closeStatus'])) ? $details['closeStatus'] = Utility::clean_string($_POST['closeStatus']) : '';
	(isset($_POST['projectID']) && !empty($_POST['projectID'])) ? $details['projectID'] = Utility::clean_string($_POST['projectID']) : '';

	var_dump($details);
	if ($saleID) {
		$salesDetails = Work::sales_cases(array("saleID"=> $saleID), true, $DBConn);

		(isset($details['saleStatus']) &&  ($salesDetails->saleStatus !== $details['saleStatus'])) ? $changes['saleStatus'] = $details['saleStatus'] : "";
		(isset($details['salesPersonID']) &&  ($salesDetails->salesPersonID !== (int)$details['salesPersonID'])) ? $changes['salesPersonID'] = $details['salesPersonID'] : "";
		(isset($details['saleEstimate']) &&  ($salesDetails->saleEstimate !== floatval($details['saleEstimate'])) ) ? $changes['saleEstimate'] = $details['saleEstimate'] : "";
		(isset($details['probability']) &&  ($salesDetails->probability !== (int)$details['probability'])) ? $changes['probability'] = $details['probability'] : "";
		(isset($details['caseID']) &&  ($salesDetails->caseID !== (int)$details['caseID'])) ? $changes['caseID'] = $details['caseID'] : "";
		(isset($details['clientID']) &&  ($salesDetails->clientID !== (int)$details['clientID'])) ? $changes['clientID'] = $details['clientID'] : "";
		(isset($details['businessUnitID']) &&  ($salesDetails->businessUnitID !== (int)$details['businessUnitID'])) ? $changes['businessUnitID'] = $details['businessUnitID'] : "";
		(isset($details['expectedCloseDate']) &&  ($salesDetails->expectedCloseDate !== $details['expectedCloseDate'])) ? $changes['expectedCloseDate'] = $details['expectedCloseDate'] : "";
		(isset($details['dateClosed']) &&  ($salesDetails->dateClosed !== $details['dateClosed'])) ? $changes['dateClosed'] = $details['dateClosed'] : "";
		(isset($details['leadSourceID']) &&  ($salesDetails->leadSourceID !== (int)$details['leadSourceID'])) ? $changes['leadSourceID'] = $details['leadSourceID'] : "";
		(isset($details['closeStatus']) &&  ($salesDetails->closeStatus !== $details['closeStatus'])) ? $changes['closeStatus'] = $details['closeStatus'] : "";
		(isset($details['projectID']) &&  ($salesDetails->projectID !== $details['projectID'])) ? $changes['projectID'] = $details['projectID'] : "";

		($closeStatus && $closeStatus !== $salesDetails->closeStatus) ? $changes['closeStatus'] = $closeStatus : "";

		var_dump($changes);

		if (count($errors) === 0) {
			if ($changes) {
				$changes['LastUpdate'] = $config['currentDateTimeFormated'];
				if (!$DBConn->update_table("sbsl_sales", $changes, array("saleID"=> $saleID))) {
					$errors[] = "<span class't600'> ERROR! </span> Unable to update sale details to the database";
				}				
			}			
		}

		var_dump($salesDetails);
		
	} else {



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
  
} else {
   $DBConn->rollback();
   $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
   $_SESSION['posts'] = serialize($_POST);  
   $returnURL= Utility::returnURL($_SESSION['returnURL'], "s={$s}&ss={$ss}&p={$p}");
	
}

var_dump($returnURL);
$_SESSION['FlashMessages'] = serialize($messages);
header("location:{$base}html/?{$returnURL}");
var_dump($errors); ?>