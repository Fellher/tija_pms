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

    $adjustmentType = (isset($_POST['adjustmentType']) && !empty($_POST['adjustmentType'])) ? Utility::clean_string($_POST['adjustmentType']):"";
    $adjustmentTypeDescription = (isset($_POST['adjustmentTypeDescription']) && !empty($_POST['adjustmentTypeDescription'])) ? $_POST['adjustmentTypeDescription']:"";
    $adjustmentTypeID = (isset($_POST['adjustmentTypeID']) && !empty($_POST['adjustmentTypeID'])) ? Utility::clean_string($_POST['adjustmentTypeID']):"";

    if($adjustmentTypeID) {
        $adjustmentTypeDetails = Tax::adjustment_types(array('adjustmentTypeID'=>$adjustmentTypeID), true, $DBConn);

        ($adjustmentTypeDetails->adjustmentType !== $adjustmentType) ? $changes['adjustmentType'] = $adjustmentType : "";
        ($adjustmentTypeDetails->adjustmentTypeDescription !== $adjustmentTypeDescription) ? $changes['adjustmentTypeDescription'] = $adjustmentTypeDescription : "";

        if(count($errors) === 0) {
            if($changes) {
                $changes['LastUpdate'] = $config['currentDateTimeFormated'];
                if(!$DBConn->update_table("sbsl_tax_adjustment_types", $changes, array('adjustmentTypeID'=> $adjustmentTypeID))) {
                    $errors[] = "<span class't600'> ERROR!</span> Failed to update adjustment type to the database";
                } else {
                    $success = "Adjustment type updated successfully";
                }
            }
        }

    } else {
        $adjustmentType ? $details['adjustmentType'] = $adjustmentType : $errors[] = "Please submit valid adjustment type";
        $adjustmentTypeDescription ? $details['adjustmentTypeDescription'] = $adjustmentTypeDescription : $errors[] = "Please submit valid adjustment type description";
        if(count($errors) === 0) {
            if($details) {
                $details['LastUpdate'] = $config['currentDateTimeFormated'];
                if(!$DBConn->insert_data("sbsl_tax_adjustment_types", $details)) {
                    $errors[] = "<span class't600'> ERROR!</span> Failed to update adjustment type to the database";
                } else {
                    $success = "Adjustment type added successfully";
                    $adjustmentTypeID = $DBConn->lastInsertID();
                }
            }
        }
    }


    
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