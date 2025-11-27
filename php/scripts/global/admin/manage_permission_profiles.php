
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

	$permissionProfileID = (isset($_POST['permissionProfileID']) && !empty($_POST['permissionProfileID'])) ?  Utility::clean_string($_POST['permissionProfileID']): "";
	$permissionProfileTitle = (isset($_POST['permissionProfileTitle']) && !empty($_POST['permissionProfileTitle'])) ?  Utility::clean_string($_POST['permissionProfileTitle']): "";
	$permissionProfileScopeID = (isset($_POST['permissionProfileScopeID']) && !empty($_POST['permissionProfileScopeID'])) ?  Utility::clean_string($_POST['permissionProfileScopeID']): "";
	$permissionProfileDescription = (isset($_POST['permissionProfileDescription']) && !empty($_POST['permissionProfileDescription'])) ?  Utility::clean_string($_POST['permissionProfileDescription']): "";

	if($permissionProfileID) {
		$permissionProfiledetails = Admin::permission_profiles(array("permissionProfileID"=>$permissionProfileID), true, $DBConn);

		if($permissionProfiledetails){
			(isset($permissionProfileTitle) && !empty($permissionTypeTitle) && $permissionTypeTitle != $permissionProfiledetails->permissionTypeTitle) ? $changes['permissionTypeTitle'] = $permissionTypeTitle : '';
			(isset($permissionProfileScopeID) && !empty($permissionProfileScopeID) && $permissionProfileScopeID != $permissionProfiledetails->permissionProfileScopeID) ? $changes['permissionProfileScopeID'] = $permissionProfileScopeID : '';
			(isset($permissionProfileDescription) && !empty($permissionProfileDescription) && $permissionProfileDescription != $permissionProfiledetails->permissionProfileDescription) ? $changes['permissionProfileDescription'] = $permissionProfileDescription : '';

			if(count($changes) > 0){
				$update = $DBConn->update_table('tija_permission_profiles', $changes, array("permissionProfileID"=>$permissionProfileID));
				if($update){
					$success = "The permission profile has been updated successfully.";
					$permissionProfileID = $DBConn->lastInsertId();
				} else {
					$errors[] = "There was an error updating the permission profile.";
				}
			} else {
				$errors[] = "No changes were made to the permission profile.";
			}
		} else {
			$errors[] = "The permission profile you are trying to update does not exist.";
		}


	} else {


		$permissionProfileTitle ? $details['permissionProfileTitle'] = $permissionProfileTitle : $errors[] = 'The permission profile title is required.';
		$permissionProfileScopeID ? $details['permissionProfileScopeID'] = $permissionProfileScopeID : $errors[] = 'The permission profile scope is required.';
		$permissionProfileDescription ? $details['permissionProfileDescription'] = $permissionProfileDescription : $errors[] = 'The permission type description is required.';
		var_dump($details);
		if(count($errors) == 0){
			if($details){   
				$details['LastUpdatedByID'] = $userDetails->ID;  
				$lastUpdate = $config['currentDateTimeFormated'];
				// $insert = $DBConn->insert_table('tija_permission_types', $details);
				if(!$DBConn->insert_data('tija_permission_profiles', $details)){
					$errors[] = "There was an error adding the permission profile.";
				} else {
					$success = "The permission profile has been added successfully.";
					$permissionTypeID = $DBConn->lastInsertId();               
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