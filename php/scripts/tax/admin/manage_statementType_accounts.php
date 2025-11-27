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

    $financialStatementTypeID = (isset($_POST['financialStatementTypeID']) && !empty($_POST['financialStatementTypeID'])) ? Utility::clean_string($_POST['financialStatementTypeID']) : '';
    $statementTypeNode = (isset($_POST['statementTypeNode']) && !empty($_POST['statementTypeNode'])) ? Utility::clean_string($_POST['statementTypeNode']) : '';
    $accountName = (isset($_POST['accountName']) && !empty($_POST['accountName'])) ? Utility::clean_string($_POST['accountName']) : '';
    $accountCode = (isset($_POST['accountCode']) && !empty($_POST['accountCode'])) ? Utility::clean_string($_POST['accountCode']): Utility::clientCode($accountName);
    $accountName ? $accountNode = Tax::nodes($accountName): "";
    $financialStatementAccountID = (isset($_POST['financialStatementAccountID']) && !empty($_POST['financialStatementAccountID'])) ? Utility::clean_string($_POST['financialStatementAccountID']) : '';

    if($statementTypeNode === "StatementofInvestmentAllowance") {
        if(!$financialStatementAccountID){
            $financialStatementTypeID ? $details['financialStatementTypeID'] = $financialStatementTypeID : $errors[] = 'Financial Statement Type ID is required';
            $statementTypeNode ? $details['statementTypeNode'] = $statementTypeNode : $errors[] = 'Statement Type Node is required';
            $accountName ? $details['accountName'] = $accountName : $errors[] = 'Account Name is required';
            $accountCode ? $details['accountCode'] = $accountCode : $errors[] = 'Account Code is required';
            $accountNode ? $details['accountNode'] = $accountNode : $errors[] = 'Account Node is required';
            $details['accountCategory'] = "Investment Allowance";

            var_dump($details);
            if(count($errors) == 0){
                if($details){
                    if(!$DBConn->insert_data('sbsl_statement_of_investment_allowance_accounts', $details)){
                        $errors[] = "An error occurred while adding the account";
                    } else {
                        $success = "Account was added successfully";
                    }
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
header("location:{$base}html/?{$returnURL}");?>