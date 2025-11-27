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

    $node = (isset($_POST['node']) && !empty($_POST['node'])) ? Utility::clean_string($_POST['node']):"";
    $instanceID = (isset($_POST['instanceID']) && !empty($_POST['instanceID'])) ? Utility::clean_string($_POST['instanceID']):"";
    $financialStatementAccountIDArr = (isset($_POST['financialStatementAccountID']) && !empty($_POST['financialStatementAccountID'])) ? $_POST['financialStatementAccountID']:"";
    $financialStatementTypeID = (isset($_POST['financialStatementTypeID']) && !empty($_POST['financialStatementTypeID'])) ? Utility::clean_string($_POST['financialStatementTypeID']):"";
    $adjustmentTypeID = (isset($_POST['adjustmentTypeID']) && !empty($_POST['adjustmentTypeID'])) ? Utility::clean_string($_POST['adjustmentTypeID']):"";

    // For investment 
    $accountID = (isset($_POST['accountID']) && (!empty($_POST['accountID']))) ? $_POST['accountID'] : "";
    $accountRate = (isset($_POST['accountRate']) && !empty($_POST['accountRate'])) ? Utility::clean_string($_POST['accountRate']):"";
    $adjustmentAccountsID = (isset($_POST['adjustmentAccountsID']) && !empty($_POST['adjustmentAccountsID'])) ? Utility::clean_string($_POST['adjustmentAccountsID']):"";


  
    $instanceID ? $details['instanceID'] = $instanceID : $errors[] = "Please submit valid instance ID";
    $financialStatementTypeID ? $details['financialStatementTypeID'] = $financialStatementTypeID : $errors[] = "Please submit valid financial statement type ID";
    $adjustmentTypeID ? $details['adjustmentTypeID'] = $adjustmentTypeID : $errors[] = "Please submit valid adjustment type ID";
    $accountRate ? $details['accountRate'] = ($accountRate/100) : $errors[] = "Please submit valid accountRate";

    if($adjustmentAccountsID) {


        $adjustmentAccountDetails = Tax::adjustment_accounts(array('adjustmentAccountsID'=>$adjustmentAccountsID, 'instanceID'=>$instanceID, 'financialStatementTypeID'=>$financialStatementTypeID, 'adjustmentTypeID'=>$adjustmentTypeID), true, $DBConn);
        var_dump($adjustmentAccountDetails);
        $financialStatementAccountID="";
        if(!is_array($financialStatementAccountIDArr)){
            $financialStatementAccountID = $financialStatementAccountIDArr;
        }

        if($adjustmentAccountDetails) {
            $adjustmentTypeID != $adjustmentAccountDetails->adjustmentTypeID ? $changes['adjustmentTypeID'] = $adjustmentTypeID : "";
            $financialStatementTypeID != $adjustmentAccountDetails->financialStatementTypeID ? $changes['financialStatementTypeID'] = $financialStatementTypeID : "";
            ($accountRate/100) != $adjustmentAccountDetails->accountRate ? $changes['accountRate'] = ($accountRate/100) : "";
            $financialStatementAccountID != $adjustmentAccountDetails->financialStatementAccountID ? $changes['financialStatementAccountID'] = $financialStatementAccountID : "";
            var_dump($changes);
            if(count($errors) ===0) {
                if(count($changes) > 0) {
                    if(!$DBConn->update_table('sbsl_tax_adjustments_accounts', $changes, array('adjustmentAccountsID'=>$adjustmentAccountsID))) {
                        $errors[] = "<span class't600'> ERROR!</span> Failed to update adjustment type to the database";
                    } else {
                        $success = "Adjustment type updated successfully";
                    }
                } else {
                    $errors[] = "No changes were made";
                }

            }



        }


    } else {
        if($financialStatementTypeID == 5) {
            if(is_array($accountID)) {
                foreach ($accountID as $key => $account) {
                    $details['financialStatementAccountID'] = Utility::clean_string($account);
                    var_dump($details);
                    if($details){
                        if(!$DBConn->insert_data("sbsl_tax_adjustments_accounts", $details)) {
                            $errors[] = "<span class't600'> ERROR!</span> Failed to update adjustment type to the database";
                        } else {
                            $success = "Adjustment type added successfully";
                            $adjustmentAccountsID = $DBConn->lastInsertID();
                        }
                    }
                }    
            }
        } else {
            if(is_array($financialStatementAccountIDArr)) {
                foreach ($financialStatementAccountIDArr as $key => $financialStatementAccountID) {
                    $details['financialStatementAccountID'] = Utility::clean_string($financialStatementAccountID);
                   var_dump($details);
                    if($details){
                        if(!$DBConn->insert_data("sbsl_tax_adjustments_accounts", $details)) {
                            $errors[] = "<span class't600'> ERROR!</span> Failed to update adjustment type to the database";
                        } else {
                            $success = "Adjustment type added successfully";
                            $adjustmentAccountsID = $DBConn->lastInsertID();
                        }
                    }
                }
            }            
        }
    }
    


   

    var_dump($errors);


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