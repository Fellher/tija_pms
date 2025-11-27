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

    $financialStatementTypeName = (isset($_POST['financialStatementTypeName']) && !empty($_POST['financialStatementTypeName'])) ? Utility::clean_string($_POST['financialStatementTypeName']):"";
    $financialStatementTypeDescription = (isset($_POST['financialStatementTypeDescription']) && !empty($_POST['financialStatementTypeDescription'])) ? $_POST['financialStatementTypeDescription']:"";
    $financialStatementTypeID = (isset($_POST['financialStatementTypeID']) && !empty($_POST['financialStatementTypeID'])) ? Utility::clean_string($_POST['financialStatementTypeID']):"";
    $statementTypeNode = Tax::account_node_short($financialStatementTypeName);

    if($financialStatementTypeID) {
        $statementTypeDetails = Tax::financial_statements_types(array('financialStatementTypeID'=>$financialStatementTypeID), true, $DBConn);

        ($statementTypeDetails->financialStatementTypeName !== $financialStatementTypeName) ? $changes['financialStatementTypeName'] = $financialStatementTypeName : "";
        ($statementTypeDetails->financialStatementTypeDescription !== $financialStatementTypeDescription) ? $changes['financialStatementTypeDescription'] = $financialStatementTypeDescription : "";
        ($statementTypeDetails->statementTypeNode !== $statementTypeNode) ? $changes['statementTypeNode'] = $statementTypeNode : "";

        if(count($errors) === 0) {
            if($changes) {
                $changes['LastUpdate'] = $config['currentDateTimeFormated'];
                if(!$DBConn->update_table("sbsl_financial_statements_types", $changes, array('financialStatementTypeID'=> $financialStatementID))) {
                    $errors[] = "<span class't600'> ERROR!</span> Failed to update statement type to the database";
                } else {
                    $success = "Statement type updated successfully";
                }
            }
        }

    } else {

        $financialStatementTypeName ? $details['financialStatementTypeName'] = $financialStatementTypeName : $errors[] = "Please submit valid statement type";
        $financialStatementTypeDescription ? $details['financialStatementTypeDescription'] = $financialStatementTypeDescription : $errors[] = "Please submit valid statement type description";
        $statementTypeNode ? $details['statementTypeNode'] = $statementTypeNode : $errors[] = "Please submit valid statement type node";
        if(count($errors) === 0) {
            if($details) {
                $details['LastUpdate'] = $config['currentDateTimeFormated'];
                if(!$DBConn->insert_data("sbsl_financial_statements_types", $details)) {
                    $errors[] = "<span class't600'> ERROR!</span> Failed to update statement type to the database";
                } else {
                    $success = "Statement type added successfully";
                    $financialStatementID = $DBConn->lastInsertID();
                }
            }
        }

    }

    var_dump($errors);

} else { 
	$errors[] = 'You need to log in as a valid administrator to do that.';
}

$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=tax=home');
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