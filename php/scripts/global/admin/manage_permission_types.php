
<?php
session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
$success="";
if ($isValidAdmin) {
	var_dump($_POST);

    $permissionTypeTitle = (isset($_POST['permissionTypeTitle']) && !empty($_POST['permissionTypeTitle'])) ?  Utility::clean_string($_POST['permissionTypeTitle']): "";
    $permissionTypeID = (isset($_POST['permissionTypeID']) && !empty($_POST['permissionTypeID'])) ?  Utility::clean_string($_POST['permissionTypeID']): "";
    $permissionTypeDescription = (isset($_POST['permissionTypeDescription']) && !empty($_POST['permissionTypeDescription'])) ?  Utility::clean_string($_POST['permissionTypeDescription']): "";

    if(!$permissionTypeID){
        $permissionTypeTitle ? $details['permissionTypeTitle'] = $permissionTypeTitle : $errors[] = 'The permission type title is required.';
        $permissionTypeDescription ? $details['permissionTypeDescription'] = $permissionTypeDescription : $errors[] = 'The permission type description is required.';
        if(count($errors) == 0){
            if($details){   
                $details['LastUpdatedByID'] = $userDetails->ID;  
            // $insert = $DBConn->insert_table('tija_permission_types', $details);
                if(!$DBConn->insert_data('tija_permission_types', $details)){
                    $errors[] = "There was an error adding the permission type.";
                } else {
                    $success = "The permission type has been added successfully.";
                    $permissionTypeID = $DBConn->lastInsertId();               
                }
            }
        }

    } else {
        $permissionType = Admin::tija_permission_types(array("permissionTypeID"=>$permissionTypeID), true, $DBConn);
        if($permissionType){
           (isset($permissionTypeTitle) && !empty($permissionTypeTitle) && $permissionTypeTitle != $permissionType->permissionTypeTitle) ? $changes['permissionTypeTitle'] = $permissionTypeTitle : '';
            (isset($permissionTypeDescription) && !empty($permissionTypeDescription) && $permissionTypeDescription != $permissionType->permissionTypeDescription) ? $changes['permissionTypeDescription'] = $permissionTypeDescription : '';

            if(count($changes) > 0){
                $update = $DBConn->update_table('tija_permission_types', $changes, array("permissionTypeID"=>$permissionTypeID));
                if($update){
                    $success = "The permission type has been updated successfully.";
                } else {
                    $errors[] = "There was an error updating the permission type.";
                }
            } else {
                $errors[] = "No changes were made to the permission type.";
            }
        } else {
            $errors[] = "The permission type you are trying to update does not exist.";
        }
    }

    $returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
	var_dump($returnURL);

} else {
	$errors[] = 'You need to log in as a valid administrator to do that.';
}

if (count($errors) == 0) {
	$DBConn->commit();
	$messages = array(array('Text'=>"{$success}", 'Type'=>'success'));
} else {
 	$DBConn->rollback();
 	$messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
}
 $_SESSION['FlashMessages'] = serialize($messages);
 header("location:{$base}html/{$returnURL}");