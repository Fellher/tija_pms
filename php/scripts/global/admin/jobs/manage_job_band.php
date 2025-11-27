
<?php
session_start();
$base = '../../../../../';
set_include_path($base);

include 'php/includes.php';

$DBConn->begin();
$errors = array();
$categoryDetils=array();
$details= array();
$changes= array();
$success="";
if ($isValidAdmin) {
	var_dump($_POST);

    $jobBandID = (isset($_POST['jobBandID']) && !empty($_POST['jobBandID'])) ? Utility::clean_string($_POST['jobBandID']) : null;
    $jobBandTitle = (isset($_POST['jobBandTitle']) && !empty($_POST['jobBandTitle'])) ? Utility::clean_string($_POST['jobBandTitle']) : null;
    $jobBandDescription = (isset($_POST['jobBandDescription']) && !empty($_POST['jobBandDescription'])) ? Utility::clean_string($_POST['jobBandDescription']) : null;


    if($jobBandID) {
        $jobBand = Admin::tija_job_bands(array('jobBandID'=>$jobBandID), true, $DBConn);
        if($jobBand){
            (isset($jobBandTitle) && !empty($jobBandTitle) && $jobBand->jobBandTitle !== $jobBandTitle) ? $changes['jobBandTitle'] = $jobBandTitle : "";

            ($jobBandDescription && $jobBand->jobBandDescription !==  $jobBandDescription) ? $changes['jobBandDescription'] = $jobBandDescription : "";

            if(count($changes) > 0){
                if(!$DBConn->update_table('tija_job_bands', $changes, array('jobBandID'=>$jobBandID))){
                    $errors[] = "Error updating Job Band";
                } else {
                    $success = "Job Band updated successfully";
                }           
            } else {
                $errors[] = "No changes made";
            }
        } else {
            $errors[] = "Job Band not found";
        }

    } else {
        (isset($jobBandTitle) && !empty($jobBandTitle)) ? $details['jobBandTitle'] = $jobBandTitle : $errors[] = "Job Band Title is required";
        (isset($jobBandDescription) && !empty($jobBandDescription)) ? $details['jobBandDescription'] = $jobBandDescription : $errors[] = "Job Band Description is required";
        if(count($errors) === 0){
            $details['LastUpdatedByID'] = $userDetails->ID;
            $details['LastUpdated'] = $config['currentDateTimeFormated'];
            if(!$DBConn->insert_data('tija_job_bands', $details)){
                $errors[] = "Error adding Job Band";
            } else {
                $success = "Job Band added successfully";
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