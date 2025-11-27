
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

    $jobCategoryID = (isset($_POST['jobCategoryID']) && !empty($_POST['jobCategoryID'])) ? Utility::clean_string($_POST['jobCategoryID']) : null;
    $jobCategoryTitle = (isset($_POST['jobCategoryTitle']) && !empty($_POST['jobCategoryTitle'])) ? Utility::clean_string($_POST['jobCategoryTitle']) : null;
    $jobCategoryDescription = (isset($_POST['jobCategoryDescription']) && !empty($_POST['jobCategoryDescription'])) ? Utility::clean_string($_POST['jobCategoryDescription']) : null;

    if($jobCategoryID){
        $jobCategory = Admin::tija_job_categories(array('jobCategoryID'=>$jobCategoryID), true, $DBConn);
        if($jobCategory){
            (isset($jobCategoryTitle) && !empty(jobCategoryTitle) && $jobCategory->jobCategoryTitle !== $jobCategoryTitle) ? $changes['jobCategoryTitle'] = $jobCategoryTitle : "";

            ($jobCategoryDescription && $jobCategory->jobCategoryDescription !==  $jobCategoryDescription) ? $changes['jobCategoryDescription'] = $jobCategoryDescription : "";

            if(count($changes) > 0){
                if(!$DBConn->update_table('tija_job_categories', $changes, array('jobCategoryID'=>$jobCategoryID))){
                    $errors[] = "Error updating Job Category";
                } else {
                    $success = "Job Category updated successfully";
                }           
            } else {
                $errors[] = "No changes made";
            }
        } else {
            $errors[] = "Job Category not found";
        }
    } else {

        (isset($jobCategoryTitle) && !empty($jobCategoryTitle)) ? $details['jobCategoryTitle'] = $jobCategoryTitle : $errors[] = "Job Category Title is required";
        (isset($jobCategoryDescription) && !empty($jobCategoryDescription)) ? $details['jobCategoryDescription'] = $jobCategoryDescription : $errors[] = "Job Category Description is required";
        if(count($errors) === 0){
            $details['LastUpdatedByID'] = $userDetails->ID;
            $details['LastUpdated'] = $config['currentDateTimeFormated'];
            if(!$DBConn->insert_data('tija_job_categories', $details)){
                $errors[] = "Error adding Job Category";
            } else {
                $success = "Job Category added successfully";
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