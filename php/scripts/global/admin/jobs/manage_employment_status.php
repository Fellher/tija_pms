
<?php
session_start();
$base = '../../../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
$success="";
if ($isValidAdmin) {
	var_dump($_POST);
    $employmentStatusID = (isset($_POST['employmentStatusID']) && !empty($_POST['employmentStatusID'])) ? Utility::clean_string($_POST['employmentStatusID']) : null;
    $employmentStatusTitle = (isset($_POST['employmentStatusTitle']) && !empty($_POST['employmentStatusTitle'])) ? Utility::clean_string($_POST['employmentStatusTitle']) : null;
    $employmentStatusDescription = (isset($_POST['employmentStatusDescription']) && !empty($_POST['employmentStatusDescription'])) ? Utility::clean_string($_POST['employmentStatusDescription']) : null;

    if($employmentStatusID){
        $employmentStatus = Admin::tija_employment_status(array('employmentStatusID'=>$employmentStatusID), true, $DBConn);
        if($employmentStatus){
            (isset($employmentStatusTitle) && !empty(employmentStatusTitle) && $employmentStatus->employmentStatusTitle !== $employmentStatusTitle) ? $changes['employmentStatusTitle'] = $employmentStatusTitle : "";

            ($employmentStatusDescription && $employmentStatus->employmentStatusDescription !==  $employmentStatusDescription) ? $changes['employmentStatusDescription'] = $employmentStatusDescription : "";

            if(count($changes) > 0){
                if(!$DBConn->update_table('tija_employment_status', $changes, array('employmentStatusID'=>$employmentStatusID))){
                    $errors[] = "Error updating Employment Status";
                } else {
                    $success = "Employment Status updated successfully";
                }           
            } else {
                $errors[] = "No changes made";
            }
        } else {
            $errors[] = "Employment Status not found";
        }
    } else {

        (isset($employmentStatusTitle) && !empty($employmentStatusTitle)) ? $details['employmentStatusTitle'] = $employmentStatusTitle : $errors[] = "Employment Status Title is required";
        (isset($employmentStatusDescription) && !empty($employmentStatusDescription)) ? $details['employmentStatusDescription'] = $employmentStatusDescription : $errors[] = "Employment Status Description is required";
        if(count($errors) === 0){
            $details['LastUpdatedByID'] = $userDetails->ID;
            $details['LastUpdated'] = $config['currentDateTimeFormated'];
            if(!$DBConn->insert_data('tija_employment_status', $details)){
                $errors[] = "Error adding Employment Status";
            } else {
                $success = "Employment Status added successfully";
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
 header("location:{$base}html/{$returnURL}");