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
   $financialStatementID = (isset($_POST['financialStatementID']) && !empty($_POST['financialStatementID'])) ? Utility::clean_string($_POST['financialStatementID']) : '';
    $orgDataID = (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ? Utility::clean_string($_POST['orgDataID']) : '';
    $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ? Utility::clean_string($_POST['entityID']) : '';
    $statementTypeNode = (isset($_POST['statementTypeNode']) && !empty($_POST['statementTypeNode'])) ? Utility::clean_string($_POST['statementTypeNode']) : '';
    $trial_balance_financialStatementDataIDs = (isset($_POST['trial_balance_financialStatementDataIDs']) && !empty($_POST['trial_balance_financialStatementDataIDs'])) && is_array($_POST['trial_balance_financialStatementDataIDs']) ? $_POST['trial_balance_financialStatementDataIDs'] : '';
    $financialStatementAccountID = (isset($_POST['financialStatementAccountID']) && !empty($_POST['financialStatementAccountID'])) ? Utility::clean_string($_POST['financialStatementAccountID']) : '';

    (isset($financialStatementTypeID) && !empty($financialStatementTypeID)) ? $details['financialStatementTypeID'] = $financialStatementTypeID : $errors[] = 'Financial Statement Type ID is required';
    (isset($financialStatementID) && !empty($financialStatementID)) ? $details['financialStatementID'] = $financialStatementID : $errors[] = 'Financial Statement ID is required';
    (isset($entityID) && !empty($entityID)) ? $details['entityID'] = $entityID : $errors[] = 'entity ID is required';
   
    (isset($statementTypeNode) && !empty($statementTypeNode)) ? $details['statementTypeNode'] = $statementTypeNode : $errors[] = 'Statement Type Node is required';
    (isset($trial_balance_financialStatementDataIDs) && !empty($trial_balance_financialStatementDataIDs)) ? $trial_balance_financialStatementDataIDs : $errors[] = 'Trial Balance Financial Statement Data IDs are required';
    (isset($financialStatementAccountID) && !empty($financialStatementAccountID)) ? $details['financialStatementAccountID'] = $financialStatementAccountID : $errors[] = 'Income Statement Account ID is required';
    $incomeStatementAccountDetails = Tax::financial_statement_accounts(array("financialStatementAccountID"=>$financialStatementAccountID), true, $DBConn);

    var_dump($incomeStatementAccountDetails);

    if(is_array($trial_balance_financialStatementDataIDs)){
        foreach ($trial_balance_financialStatementDataIDs as $key => $trial_balance_financialStatementDataID) {
            $trial_balance_financialStatementDataID = Utility::clean_string($trial_balance_financialStatementDataID);
            $trial_balance_financialStatementData =Tax::financial_statementData(array("financialStatementDataID"=>$trial_balance_financialStatementDataID), true, $DBConn); 
            var_dump($trial_balance_financialStatementData);
            $details['financialStatementDataID']=$trial_balance_financialStatementDataID;
            $details['accountType']=$trial_balance_financialStatementData->accountType;
            $trial_balance_financialStatementData->accountType === "debit" ? $details['debitValue'] = $trial_balance_financialStatementData->debitValue : $details['debitValue'] = 0;
            $trial_balance_financialStatementData->accountType === "credit" ? $details['creditValue'] = $trial_balance_financialStatementData->creditValue : $details['creditValue'] = 0;
            $details['accountCode'] = $trial_balance_financialStatementData->accountCode;
            $details['categoryAccountCode'] = $incomeStatementAccountDetails->accountCode;
            $details['accountName'] = $trial_balance_financialStatementData->accountName;
            $details['accountCategory'] = $trial_balance_financialStatementData->accountCategory;       

            var_dump($details);

            if(count($errors) == 0){
                if($details){
                    if(!$DBConn->insert_data('tija_trial_balance_mapped_accounts', $details)){
                        $errors[] = "An error occurred while mappnmng the accounts";
                    } else {
                        $success = "mapping was successful";
                    }
                }
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