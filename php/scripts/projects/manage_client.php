<?php
session_start();
$base = "../../../";
set_include_path($base);

include 'php/includes.php';

var_dump($_POST);

$errors = array();
$DBConn->begin();
$details=array();

if ($isValidUser) {
	$s =(isset($_POST['s']) && !empty($_POST['s'])) ? Utility::clean_string($_POST['s']) : null;
	$ss =(isset($_POST['ss']) && !empty($_POST['ss'])) ? Utility::clean_string($_POST['ss']) : null;
	$p =(isset($_POST['p']) && !empty($_POST['p'])) ? Utility::clean_string($_POST['p']) : null;


	(isset($_POST['clientCode'])  && !empty($_POST['clientCode'])) ? $details['clientCode'] = Utility::clean_string($_POST['clientCode']): $details['clientCode'] = "SBSL-".Utility::generateRandomString(5);
	(isset($_POST['clientName'])  && !empty($_POST['clientName'])) ? $details['clientName'] = Utility::clean_string($_POST['clientName']) : $errors[]= "Please submit valid client Name";
	(isset($_POST['accountOwnerID'])  && !empty($_POST['accountOwnerID'])) ? $details['accountOwnerID'] = Utility::clean_string($_POST['accountOwnerID']) : 	"";

	if (isset($_POST['clientContactName'])  && !empty($_POST['clientContactName'])) {
		$contactName= explode(' ', Utility::clean_string($_POST['clientContactName']));
		$details['contactName'] = Utility::clean_string($_POST['clientContactName']);
	} 

	(isset($_POST['clientContactemail'])  && !empty($_POST['clientContactemail']) && Form::validate_email($_POST['clientContactemail'])) ?	$details['contactemail'] = Form::validate_email($_POST['clientContactemail']) : "";

	if (isset($_POST['clientID']) && !empty($_POST['clientID'])) {
		$clientID= Utility::clean_string($_POST['clientID']);

		$clientDetails = Work::sbsl_clients(array("clientID"=> $clientID), true, $DBConn);
		var_dump($clientDetails);
		(isset($details['clientName']) && ($details['clientName'] !== $clientDetails->clientName)) ? $changes['clientName'] = $details['clientName'] : "";
		(isset($details['accountOwnerID']) && ($details['accountOwnerID'] !== $clientDetails->accountOwnerID)) ? $changes['accountOwnerID'] = $details['accountOwnerID'] : "";
		(isset($details['contactEmail']) && ($details['contactEmail'] !== $clientDetails->contactEmail)) ? $changes['contactEmail'] = $details['contactEmail'] : "";
		(isset($details['contactName']) && ($details['contactName'] !== $clientDetails->contactName)) ? $changes['contactName'] = $details['contactName'] : "";
	
		if (count($errors)=== 0) {
			if ($changes) {
				$changes['LastUpdate'] = $config['currentDateTimeFormated'];
				if (!$DBConn->update_table("sbsl_clients", $changes, array("clientID"=> $clientID))) {
					$errors[]="<span class't600'> ERROR!</span> Unable to save cliets upodates to the database";
				}			
			}		
		}
	} else {
		if (count($errors) === 0) {
			if ($details) {
				if (!$DBConn->insert_data('sbsl_clients' , $details)) {
					$errors[]= "We were unable to save client Details. Please try again. if the problem persists consult the administrator";
				} else {
					$clientID= $DBConn->lastInsertID();
				}
			}
		}
	}
	var_dump($details);
} else {
	Alert::danger('You need to be logged in as a valid user to edit the User personal infomation');
}

if (count($errors) == 0) {
   $DBConn->commit();
   $messages = array(array('Text'=>'The updates were successfully Saved.', 'Type'=>'success'));
   if ($s || $p || $ss) {
   	 $returnURL= "s={$s}&ss={$ss}&p=client_details&id={$clientID}";
   } else {
   	 $returnURL= Utility::returnURL($_SESSION['returnURL'], "s={$s}&ss={$ss}&p={$p}");
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