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
if ($isValidAdmin || $isAdmin) {
  
	var_dump($_POST);
    var_dump($_FILES);

    $instanceID = (isset($_POST['instanceID']) && !empty($_POST['instanceID'])) ? Utility::clean_string($_POST['instanceID']) : '';
    $financialStatementID = (isset($_POST['financialStatementID']) && !empty($_POST['financialStatementID'])) ? Utility::clean_string($_POST['financialStatementID']) : '';
    $accountName = (isset($_POST['accountName']) && !empty($_POST['accountName'])) ? Utility::clean_string($_POST['accountName']) : '';
    $accountCode = (isset($_POST['accountCode']) && !empty($_POST['accountCode'])) ? Utility::clean_string($_POST['accountCode']) : Utility::clientCode($accountName) ;
    $accountNode =(isset($_POST['accountNode']) && !empty($_POST['accountNode'])) ? Utility::clean_string($_POST['accountNode']) :  Tax::account_node_short($accountName); 
    $accountCategory = (isset($_POST['accountCategory']) && !empty($_POST['accountCategory'])) ? Utility::clean_string($_POST['accountCategory']) : '';
    $accountType = (isset($_POST['accountType']) && !empty($_POST['accountType'])) ? Utility::clean_string($_POST['accountType']) : '';
    $accountValue = (isset($_POST['accountValue']) && !empty($_POST['accountValue'])) ? Utility::clean_string($_POST['accountValue']) : '';
    $accountDescription = (isset($_POST['accountDescription']) && !empty($_POST['accountDescription'])) ? $_POST['accountDescription']: '';
    $financialStatementDataID = (isset($_POST['financialStatementDataID']) && !empty($_POST['financialStatementDataID'])) ? Utility::clean_string($_POST['financialStatementDataID']) : '';

    if($financialStatementDataID) {
        $returnURL .="&fsdID={$financialStatementDataID}";
        $financialStatementData = Tax::financial_statementData(array('financialStatementDataID'=>$financialStatementDataID), true, $DBConn);
        var_dump($financialStatementData);
      
        $financialStatementData->accountName !== $accountName ? $changes['accountName'] = $accountName : '';

        if(isset($changes['accountName']) && $changes['accountName']) {
            $accountCode = Utility::clientCode($accountName);
            $accountNode = Tax::account_node_short($accountName);
        }

        $financialStatementData->accountCode !== $accountCode ? $changes['accountCode'] = $accountCode : '';
        $financialStatementData->accountNode !== $accountNode ? $changes['accountNode'] = $accountNode : '';
        $financialStatementData->accountCategory !== $accountCategory ? $changes['accountCategory'] = $accountCategory : '';
        $financialStatementData->accountType !== $accountType ? $changes['accountType'] = $accountType : '';
        $financialStatementData->accountDescription !== $accountDescription ? $changes['accountDescription'] = $accountDescription : '';

        if(isset($changes['accountType']) && $changes['accountType']) {
            if($changes['accountType'] == "debit") {
                $changes['debitValue'] = $accountValue;
                $changes['creditValue'] = '';
            } else {
                $changes['creditValue'] = $accountValue;
                $changes['debitValue'] = '';
            }           
        } 
        
        var_dump($accountValue);
        
        if($accountType == "debit") {
           $accountValue !== $financialStatementData->debitValue ? $changes['debitValue'] = $accountValue : '';
            $changes['creditValue'] = '';
        } else {
            $accountValue !== $financialStatementData->creditValue ? $changes['creditValue'] = $accountValue : '';
           
            $changes['debitValue'] = '';
        }
        var_dump($changes);   
        if(count($errors) ===0 ) {
            if($changes) {
                if(!$DBConn->update_table('sbsl_financial_statement_data', $changes, array('financialStatementDataID'=>$financialStatementDataID))) {
                    $errors[] = "Failed to update account details";
                } else {
                    $success = "Account details updated successfully";
                }
            }
        } 

    } else {
        $instanceID ? $details['instanceID'] = $instanceID : $errors[] = "Instance ID is required";
        $financialStatementID ? $details['financialStatementID'] = $financialStatementID : $errors[] = "Financial Statement ID is required";
        $accountName ? $details['accountName'] = $accountName : $errors[] = "Account Name is required";
        $accountCode ? $details['accountCode'] = $accountCode : $errors[] = "Account Code is required";
        $accountNode ? $details['accountNode'] = $accountNode : $errors[] = "Account Node is required";
        $accountCategory ? $details['accountCategory'] = $accountCategory : $errors[] = "Account Category is required";
        $accountType ? $details['accountType'] = $accountType : $errors[] = "Account Type is required";
        $accountDescription ? $details['accountDescription'] = $accountDescription : '';
        ($accountValue && is_numeric($accountValue) && $accountType== "debit") ? $details['debitValue'] = $accountValue : $details['creditValue'] = $accountValue;

        var_dump($details);


        $whereArr = array( 'accountName'=>$accountName, 'accountNode'=>$accountNode, 'accountCategory'=>$accountCategory, 'accountType'=>$accountType, 'financialStatementID'=>$financialStatementID, 'instanceID'=>$instanceID, 'Lapsed'=>'N');
        $financialStatementData = Tax::financial_statementData($whereArr, true, $DBConn);
        if($financialStatementData) {
            $errors[] = "Account already exists in the financial statement";
        } 
        if(count($errors) == 0) {
            if($details){
                if(!$DBConn->insert_data('sbsl_financial_statement_data', $details)) {
                    $errors[] = "Failed to add account to financial statement";
                } else {
                    $success = "Account added to financial statement successfully";
                    $returnURL .="&finstmtID={$financialStatementID}";
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