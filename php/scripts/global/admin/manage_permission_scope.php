
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
    $permissionScopeID = (isset($_POST['permissionScopeID']) && !empty($_POST['permissionScopeID'])) ?  Utility::clean_string($_POST['permissionScopeID']): "";
    $permissionScopeTitle = (isset($_POST['permissionScopeTitle']) && !empty($_POST['permissionScopeTitle'])) ?  Utility::clean_string($_POST['permissionScopeTitle']): "";
    $permissionScopeDescription = (isset($_POST['permissionScopeDescription']) && !empty($_POST['permissionScopeDescription'])) ?  Utility::clean_string($_POST['permissionScopeDescription']): "";

    if($permissionScopeID) {
        $permissionScopeDetails = Admin::permission_scope(array("permissionScopeID"=>$permissionScopeID), true, $DBConn);
        if($permissionScopeDetails){
            (isset($permissionScopeTitle) && !empty($permissionScopeTitle) && $permissionScopeTitle != $permissionScopeDetails->permissionScopeTitle) ? $changes['permissionScopeTitle'] = $permissionScopeTitle : '';
            (isset($permissionScopeDescription) && !empty($permissionScopeDescription) && $permissionScopeDescription != $permissionScopeDetails->permissionScopeDescription) ? $changes['permissionScopeDescription'] = $permissionScopeDescription : '';

            if(count($changes) > 0){
                $update = $DBConn->update_table('tija_permission_scopes', $changes, array("permissionScopeID"=>$permissionScopeID));
                if($update){
                    $success = "The permission scope has been updated successfully.";
                    $permissionScopeID = $DBConn->lastInsertId();
                } else {
                    $errors[] = "There was an error updating the permission scope.";
                }
            } else {
                $errors[] = "No changes were made to the permission scope.";
            }
        } else {
            $errors[] = "The permission scope you are trying to update does not exist.";
        }

    } else{
        $permissionScopeTitle ? $details['permissionScopeTitle'] = $permissionScopeTitle : $errors[] = 'The permission scope title is required.';
        $permissionScopeDescription ? $details['permissionScopeDescription'] = $permissionScopeDescription : $errors[] = 'The permission scope description is required.';
        if(count($errors) == 0){
            if($details){   
                $details['LastUpdatedByID'] = $userDetails->ID;  
                $lastUpdate = $config['currentDateTimeFormated'];
            // $insert = $DBConn->insert_table('tija_permission_types', $details);
                if(!$DBConn->insert_data('tija_permission_scopes', $details)){
                    $errors[] = "There was an error adding the permission scope.";
                } else {
                    $success = "The permission scope has been added successfully.";
                    $permissionScopeID = $DBConn->lastInsertId();               
                }
            }
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