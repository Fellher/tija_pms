
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
    $permissionRoleID = (isset($_POST['permissionRoleID']) && !empty($_POST['permissionRoleID'])) ?  Utility::clean_string($_POST['permissionRoleID']): "";
    $permRoleTitle = (isset($_POST['permRoleTitle']) && !empty($_POST['permRoleTitle'])) ?  Utility::clean_string($_POST['permRoleTitle']): "";
    $permRoleDescription = (isset($_POST['permRoleDescription']) && !empty($_POST['permRoleDescription'])) ?  Utility::clean_string($_POST['permRoleDescription']): "";
    $roleTypeID = (isset($_POST['roleTypeID']) && !empty($_POST['roleTypeID'])) ?  Utility::clean_string($_POST['roleTypeID']): "";
    $roleTypeTitle = (isset($_POST['roleTypeTitle']) && !empty($_POST['roleTypeTitle'])) ?  Utility::clean_string($_POST['roleTypeTitle']): "";
    $roleTypeDescription = (isset($_POST['roleTypeDescription']) && !empty($_POST['roleTypeDescription'])) ?  Utility::clean_string($_POST['roleTypeDescription']): "";
    $roleTypeDescription = (isset($_POST['roleTypeDescription']) && !empty($_POST['roleTypeDescription'])) ?  Utility::clean_string($_POST['roleTypeDescription']): "";
    $permissionScopeID = (isset($_POST['permissionScopeID']) && !empty($_POST['permissionScopeID'])) ?  Utility::clean_string($_POST['permissionScopeID']): "";
    $permissionProfileID = (isset($_POST['permissionProfileID']) && !empty($_POST['permissionProfileID'])) ?  Utility::clean_string($_POST['permissionProfileID']): "";

    if($permissionRoleID) {
        $permissionRoleDetails = Admin::tija_permission_roles(array("permissionRoleID"=>$permissionRoleID), true, $DBConn);
        if($permissionRoleDetails){
            (isset($permRoleTitle) && !empty($permRoleTitle) && $permRoleTitle != $permissionRoleDetails->permRoleTitle) ? $changes['permRoleTitle'] = $permRoleTitle : '';
            (isset($permRoleDescription) && !empty($permRoleDescription) && $permRoleDescription != $permissionRoleDetails->permRoleDescription) ? $changes['permRoleDescription'] = $permRoleDescription : '';
            (isset($permissionProfileID) && !empty(permissionProfileID) && $permissionProfileID != $permissionRoleDetails->permissionProfileID) ? $changes['permissionProfileID'] = $permissionProfileID : '';
            if($roleTypeID) {
                (isset($roleTypeID) && !empty($roleTypeID) && $roleTypeID != $permissionRoleDetails->roleTypeID) ? $changes['roleTypeID'] = $roleTypeID : '';
            } else {
                $roletypeDetails = array();           
                (isset($roleTypeTitle) && !empty($roleTypeTitle)) ? $roletypeDetails['roleTypeTitle'] = $roleTypeTitle: $errors[]= 'The role type title is required.';
                (isset($roleTypeDescription) && !empty(roleTypeDescription)) ? $roletypeDetails['roleTypeDescription'] = $roleTypeDescription : $errors[]= 'The role type description is required.';
                if(count($errors) === 0) {
                    if($roletypeDetails) {
                        $roletypeDetails['LastUpdatedByID'] = $userDetails->ID;
                        $roletypeDetails['LastUpdated'] = $config['currentDateTimeFormated'];
                        if(!$DBConn->insert_data('tija_role_types', $roletypeDetails)){
                            $errors[] = "There was an error adding the role type.";
                        } else {
                            $changes['roleTypeID'] = $DBConn->lastInsertId();
                        }
                    }
                }
            }
            (isset($roleTypeID) && !empty($roleTypeID) && $roleTypeID != $permissionRoleDetails->roleTypeID) ? $changes['roleTypeID'] = $roleTypeID : '';
            (isset($permissionScopeID) && !empty($permissionScopeID) && $permissionScopeID != $permissionRoleDetails->permissionScopeID) ? $changes['permissionScopeID'] = $permissionScopeID : '';

            if(count($changes) > 0){
                $update = $DBConn->update_table('tija_permission_roles', $changes, array("permissionRoleID"=>$permissionRoleID));
                var_dump($update);
                if($update){
                    $success = "The permission role has been updated successfully.";
                    $permissionRoleID = $DBConn->lastInsertId();
                } else {
                    $errors[] = "There was an error updating the permission role.";
                }
            } else {
                $errors[] = "No changes were made to the permission role.";
            }
        } else {
            $errors[] = "The permission role you are trying to update does not exist.";
        }

    } else{
        $permRoleTitle ? $details['permRoleTitle'] = $permRoleTitle : $errors[] = 'The permission role title is required.';
       
        $permissionScopeID ? $details['permissionScopeID'] = $permissionScopeID : $errors[] = 'The permission scope is required.';
        $permRoleDescription ? $details['permRoleDescription'] = $permRoleDescription : $errors[] = 'The permission role description is required.';
        $permissionProfileID ? $details['permissionProfileID'] = $permissionProfileID : $errors[] = 'The permission profile is required.';
       
        if($roleTypeID) {
            
            $roleTypeID ? $details['roleTypeID'] = $roleTypeID : $errors[] = 'The role type is required.';
        } else {
            $roletypeDetails = array();  
            echo "<h3> Here </h3>  ";          
            (isset($roleTypeTitle) && !empty($roleTypeTitle)) ? $roletypeDetails['roleTypeTitle'] = $roleTypeTitle: $errors[]= 'The role type title is required.';
            (isset($roleTypeDescription) && !empty($roleTypeDescription)) ? $roletypeDetails['roleTypeDescription'] = $roleTypeDescription : $errors[]= 'The role type description is required.';
            var_dump($errors);
            if(count($errors) === 0) {
                
                if($roletypeDetails) {
                    var_dump($roletypeDetails);
                    $roletypeDetails['LastUpdatedByID'] = $userDetails->ID;
                    $roletypeDetails['LastUpdate'] = $config['currentDateTimeFormated'];
                    var_dump($roletypeDetails);
                    if(!$DBConn->insert_data('tija_role_types', $roletypeDetails)){
                        $errors[] = "There was an error adding the role type.";
                    } else {
                        $details['roleTypeID'] = $DBConn->lastInsertId();
                    }
                }
            }
        }

        var_dump($details);
        if(count($errors) == 0){
            if($details){   
                $details['LastUpdate'] = $config['currentDateTimeFormated'];
                $details['LastUpdatedByID'] = $userDetails->ID;
                if(!$DBConn->insert_data('tija_permission_roles', $details)){
                    $errors[] = "There was an error adding the permission role.";
                } else {
                    $success = "The permission role has been added successfully.";
                    $permissionRoleID = $DBConn->lastInsertId();               
                }
            }
        }
    }
    $returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
	var_dump($returnURL);

} else {
    $errors[] = "You are not authorized to perform this action.";
}

if (count($errors) == 0) {
	$DBConn->commit();
	$messages = array(array('Text'=>"{$success}", 'Type'=>'success'));
} else {
 	$DBConn->rollback();
 	$messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
}
 $_SESSION['FlashMessages'] = serialize($messages);
 header("location:{$base}html/{$returnURL}");?>