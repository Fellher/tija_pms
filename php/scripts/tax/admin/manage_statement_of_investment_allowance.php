<?php
session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
$success = "";
$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');

var_dump($returnURL);
if ($isValidAdmin || $isAdmin) {
  
	var_dump($_POST);

	$investmentName = (isset($_POST['investmentName']) && !empty($_POST['investmentName'])) ? Utility::clean_string($_POST['investmentName']) : '';
	$rate = (isset($_POST['rate']) && !empty($_POST['rate'])) ? Utility::clean_string($_POST['rate']) : '';
	$initialWriteDownValue = (isset($_POST['initialWriteDownValue']) && !empty($_POST['initialWriteDownValue'])) ? Utility::clean_string($_POST['initialWriteDownValue']) : '';
	$beginDate = (isset($_POST['beginDate']) && !empty($_POST['beginDate']) && (preg_match($config['ISODateFormat'], $_POST['beginDate'])) ) ? Utility::clean_string($_POST['beginDate']) : '';
	$additions = (isset($_POST['additions']) && !empty($_POST['additions'])) ? Utility::clean_string($_POST['additions']) : '';
	$disposals = (isset($_POST['disposals']) && !empty($_POST['disposals'])) ? Utility::clean_string($_POST['disposals']) : '';
	$wearAndTearAllowance = (isset($_POST['wearAndTearAllowance']) && !empty($_POST['wearAndTearAllowance'])) ? Utility::clean_string($_POST['wearAndTearAllowance']) : '';
	$endWriteDownValue = (isset($_POST['endWriteDownValue']) && !empty($_POST['endWriteDownValue'])) ? Utility::clean_string($_POST['endWriteDownValue']) : '';
	$endDate = (isset($_POST['endDate']) && !empty($_POST['endDate']) &&  (preg_match($config['ISODateFormat'], $_POST['endDate']))) ? Utility::clean_string($_POST['endDate']) : '';
	$investmentAllowanceID = (isset($_POST['investmentAllowanceID']) && !empty($_POST['investmentAllowanceID'])) ? Utility::clean_string($_POST['investmentAllowanceID']) : '';
	$instanceID = (isset($_POST['instanceID']) && !empty($_POST['instanceID'])) ? Utility::clean_string($_POST['instanceID']) : '';
	$financialStatementID = (isset($_POST['financialStatementID']) && !empty($_POST['financialStatementID'])) ? Utility::clean_string($_POST['financialStatementID']) : '';
	$Suspend = (isset($_POST['Suspend']) && !empty($_POST['Suspend'])) ? 'Y' : 'N';
	$allowInTotal = (isset($_POST['allowInTotal']) && !empty($_POST['allowInTotal'])) ? 'Y' : 'N';
	


	if($investmentAllowanceID) {
		
		$investmentAllowanceData = Tax::statement_of_investment_data(array('investmentAllowanceID'=>$investmentAllowanceID), true, $DBConn);
		var_dump($investmentAllowanceData);
	  
		$investmentAllowanceData->investmentName !== $investmentName ? $changes['investmentName'] = $investmentName : '';

		if(isset($changes['investmentName']) && $changes['investmentName']) {
			$accountCode = Utility::clientCode($investmentName);
			$accountNode = Tax::account_node_short($investmentName);
		}

		$investmentAllowanceData->rate !== (floatval($rate)/100) ? $changes['rate'] = floatval($rate) : '';
		$investmentAllowanceData->initialWriteDownValue !== floatval($initialWriteDownValue) ? $changes['initialWriteDownValue'] = floatval($initialWriteDownValue) : '';
		$investmentAllowanceData->beginDate !== $beginDate ? $changes['beginDate'] = $beginDate : '';
		$investmentAllowanceData->additions !== floatval($additions) ? $changes['additions'] = floatval($additions) : '';
		$investmentAllowanceData->disposals !== floatval($disposals) ? $changes['disposals'] = floatval($disposals) : '';
		$investmentAllowanceData->wearAndTearAllowance !== floatval($wearAndTearAllowance) ? $changes['wearAndTearAllowance'] = floatval($wearAndTearAllowance) : '';
		$investmentAllowanceData->endWriteDownValue !== floatval($endWriteDownValue) ? $changes['endWriteDownValue'] = $endWriteDownValue : '';
		$investmentAllowanceData->endDate !== $endDate ? $changes['endDate'] = $endDate : '';
		$Suspend !== $investmentAllowanceData->Suspended ? $changes['Suspended'] = $Suspend : '';
		$allowInTotal !== $investmentAllowanceData->allowInTotal ? $changes['allowInTotal'] = $allowInTotal : '';

		var_dump($changes);
		if(count($errors) == 0){
			if($changes) {
				$changes['LastUpdate'] = $config['currentDateTimeFormated'];
				if(!$DBConn->update_table('sbsl_statement_of_investment_allowance_data', $changes, array('investmentAllowanceID'=>$investmentAllowanceID))) {
					$errors[] = "Failed to update account details";
				}
			}
		}
	} else {
		$investmentName ? $details['investmentName'] = $investmentName : $errors[] = "Investment Name is required";
		$rate ? $details['rate'] = floatval($rate) : $errors[] = "Rate is required";
		$initialWriteDownValue ? $details['initialWriteDownValue']=floatval($initialWriteDownValue) : $errors[] = "Initial Write Down Value is required";
		$beginDate ? $details['beginDate'] = $beginDate : $errors[] = "Begin Date is required";
		$additions ? $details['additions'] = floatval($additions) : "";
		$disposals ? $details['disposals'] = floatval($disposals) : "";
		$wearAndTearAllowance ? $details['wearAndTearAllowance']= floatval($wearAndTearAllowance) : "";
		$endWriteDownValue ? $details['endWriteDownValue'] = flpatval($endWriteDownValue) : "";
		$endDate ? $details['endDate'] = $endDate : $errors[] = "End Date is required";
		$financialStatementID ? $details['financialStatementID'] = $financialStatementID : $errors[] = "Financial Statement ID is required";
		$instanceID ? $details['instanceID'] =  $instanceID : $errors[] = "Instance ID is required";
		$details ['DateAdded'] = $config['currentDateTimeFormated'];
		
		

		if(count($errors) == 0) {
			$details['LastUpdate'] = $config['currentDateTimeFormated']; 			
			if($details) {
				var_dump($details);
				if(!$DBConn->insert_data('sbsl_statement_of_investment_allowance_data', $details)) {
					$errors[] = "Failed to insert account details";
				}
			}			
		}
	}
    
} else {
	$errors[] = 'You need to log in as a valid administrator to do that.';
}


	var_dump($returnURL);
if (count($errors) == 0) {
	$success = "Statement uploaded successfully";
	$DBConn->commit();
	$messages = array(array('Text'=>"{$success}", 'Type'=>'success'));
	
	var_dump($returnURL);
} else {
 	$DBConn->rollback();
 	$messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
}
$_SESSION['FlashMessages'] = serialize($messages);
header("location:{$base}html/?{$returnURL}");?>

