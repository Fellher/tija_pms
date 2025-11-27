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
if ($isValidAdmin || $isAdmin) {
	var_dump($_POST);

    $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ? Utility::clean_string($_POST['entityID']) : '';
    $investmentFinancialStatementID = (isset($_POST['investmentFinancialStatementID']) && !empty($_POST['investmentFinancialStatementID'])) ? Utility::clean_string($_POST['investmentFinancialStatementID']) : '';
    $InvestmentAllowanceID = (isset($_POST['InvestmentAllowanceID']) && !empty($_POST['InvestmentAllowanceID']) ) ? Utility::clean_string($_POST['InvestmentAllowanceID']) : '';

    $investmentAllowanceAccountID = (isset($_POST['investmentAllowanceAccountID']) && !empty($_POST['investmentAllowanceAccountID'])) ? Utility::clean_string($_POST['investmentAllowanceAccountID']) : '';

    $entityID = (isset($entityID) && !empty($entityID)) ? $details['entityID'] = $entityID : $errors[] = 'Instance ID is required';
    $investmentFinancialStatementID = (isset($investmentFinancialStatementID) && !empty($investmentFinancialStatementID)) ? $details['investmentFinancialStatementID'] = $investmentFinancialStatementID : $errors[] = 'Investment Financial Statement ID is required';
    $InvestmentAllowanceID = (isset($InvestmentAllowanceID) && !empty($InvestmentAllowanceID)) ? $details['InvestmentAllowanceID'] = $InvestmentAllowanceID : $errors[] = 'Investment Allowance ID is required';
    $investmentAllowanceAccountDetails = Tax::financial_statement_accounts(array("financialStatementAccountID"=>$investmentAllowanceAccountID), true, $DBConn);
    var_dump($investmentAllowanceAccountDetails);
    $investmentAllowanceAccountID = (isset($investmentAllowanceAccountID) && !empty($investmentAllowanceAccountID)) ? $details['investmentAllowanceAccountID'] = $investmentAllowanceAccountID : $errors[] = 'Investment Allowance Account ID is required';


    var_dump($details);

    if(count($errors) == 0){
        if($details){
            if(!$DBConn->insert_data('tija_investment_mapped_accounts', $details)){
                $errors[] = "An error occurred while mappnmng the accounts";
            } else {
                $success = "mapping was successful";
            }
        }
    }

} else {
	$errors[] = 'You need to log in as a valid administrator to do that.';
}
var_dump($errors);
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