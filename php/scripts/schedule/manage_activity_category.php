<?php
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
$success = "";
if ($isValidUser) {
	var_dump($_POST);

   $activityCategoryID = (isset($_POST['activityCategoryID']) && !empty($_POST['activityCategoryID'])) ? Utility::clean_string($_POST['activityCategoryID']) : '';
   $activityCategoryName = (isset($_POST['activityCategoryName']) && !empty($_POST['activityCategoryName'])) ? Utility::clean_string($_POST['activityCategoryName']) : '';
   $activityCategoryDescription = (isset($_POST['activityCategoryDescription']) && !empty($_POST['activityCategoryDescription'])) ? Utility::clean_string($_POST['activityCategoryDescription']) : '';
   $iconlink = (isset($_POST['iconlink']) && !empty($_POST['iconlink'])) ? Utility::clean_string($_POST['iconlink']) : '';

   if(!$activityCategoryID) {

      $activityCategoryName && $activityCategoryName != '' ? $details['activityCategoryName'] = $activityCategoryName : $errors[] = "Activity Category Name is required";
      $activityCategoryDescription && $activityCategoryDescription != '' ? $details['activityCategoryDescription'] = $activityCategoryDescription : $errors[] = "Activity Category Description is required";
      $iconlink && $iconlink != '' ? $details['iconlink'] = $iconlink : $errors[] = "Icon Link is required";
      

      if(!$errors) {
         if($details){
            $details['LastUpdate'] = $config['currentDateTimeFormated'];
            $details['LastUpdatedByID'] = $userDetails->ID;
            var_dump($details);

            if(!$DBConn->insert_data('tija_activity_categories', $details)){
               $errors[] = "Failed to add activity category";
            } else {
               $success = "Activity Category added successfully";
            }
         }
         
      }

   } else {
      // Update existing activity category
      // Fetch existing details
      // Compare and update
      $activityCategoryDetails = Schedule::activity_categories(array('activityCategoryID'=>$activityCategoryID), true, $DBConn);
      var_dump($activityCategoryDetails);
      if($activityCategoryDetails) {
         $activityCategoryName && $activityCategoryName != $activityCategoryDetails->activityCategoryName ? $changes['activityCategoryName'] = $activityCategoryName : '';
         $activityCategoryDescription && $activityCategoryDescription != $activityCategoryDetails->activityCategoryDescription ? $changes['activityCategoryDescription'] = $activityCategoryDescription : '';
         $iconlink && $iconlink != $activityCategoryDetails->iconlink ? $changes['iconlink'] = $iconlink : '';

         if(!$errors) {
            if($changes){
               var_dump($changes);
               $changes['LastUpdate'] = $config['currentDateTimeFormated'];
               $changes['LastUpdatedByID'] = $userDetails->ID;
               if(!$DBConn->update_table('tija_activity_categories', $changes, array('activityCategoryID'=>$activityCategoryID))){
                  $errors[] = "Failed to update activity category";
               } else {
                  $success = "Activity Category updated successfully";
               }
            }
         }
      } else {
         $errors[] = "Activity Category not found";
      }
   }


      
} else {
	$errors[] = 'You need to log in as a valid user to add new sales case.';
}

var_dump($errors);
$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
var_dump($returnURL);
 if (count($errors) == 0) {
	 $DBConn->commit();
	 $messages = array(array('Text'=>$success, 'Type'=>'success'));
 } else {
	 $DBConn->rollback();
	 $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
 }
 $_SESSION['FlashMessages'] = serialize($messages);
 header("location:{$base}html/{$returnURL}");
?>