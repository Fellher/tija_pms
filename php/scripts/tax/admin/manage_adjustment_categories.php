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

   $action = (isset($_POST['action']) && !empty($_POST['action'])) ? Utility::clean_string($_POST['action']):"";


   $adjustmentCategoryName = (isset($_POST['adjustmentCategoryName']) && !empty($_POST['adjustmentCategoryName'])) ? Utility::clean_string($_POST['adjustmentCategoryName']):"";
   $categoryDescription = (isset($_POST['categoryDescription']) && !empty($_POST['categoryDescription'])) ? Utility::clean_string($_POST['categoryDescription']):"";
   $adjustmentCategoryID = (isset($_POST['adjustmentCategoryID']) && !empty($_POST['adjustmentCategoryID'])) ? Utility::clean_string($_POST['adjustmentCategoryID']):"";
   $adjustmentTypeID = (isset($_POST['adjustmentTypeID']) && !empty($_POST['adjustmentTypeID'])) ? Utility::clean_string($_POST['adjustmentTypeID']):"";

   if($adjustmentCategoryID) {

      $adjustmentCategoryDetails = Tax::tax_adjustment_categories(array('adjustmentCategoryID'=>$adjustmentCategoryID), true, $DBConn);
      var_dump($adjustmentCategoryDetails);
      ($adjustmentCategoryDetails && $adjustmentCategoryName != $adjustmentCategoryDetails->adjustmentCategoryName) ? $changes['adjustmentCategoryName'] = $adjustmentCategoryName : "";
      ($adjustmentCategoryDetails && $categoryDescription != $adjustmentCategoryDetails->adjustmentCategoryDescription )? $changes['adjustmentCategoryDescription'] = $categoryDescription : "";
      ($adjustmentTypeID != $adjustmentCategoryDetails->adjustmentTypeID) ? $changes['adjustmentTypeID'] = $adjustmentTypeID : "";
      if($action && $action == 'delete') {
         echo "<h4> Deleting</h4>";
         if(!$DBConn->delete_row('tija_tax_adjustment_categories', array('adjustmentCategoryID'=>$adjustmentCategoryID))) {
            $errors[] = "<span class='t600'>ERROR!</span> Failed to delete adjustment category from the database";
         
         } else {
            $success = "Adjustment category deleted successfully";
            echo "<h4> Deleted</h4>";
         }
      } else {
         var_dump($changes);
         if(count($errors) ===0) {
            if(count($changes) > 0) {

               $changes['LastUpdate'] = $config['currentDateTimeFormated'];
               if(!$DBConn->update_table('tija_tax_adjustment_categories', $changes, array('adjustmentCategoryID'=>$adjustmentCategoryID))) {
                  $errors[] = "<span class't600'> ERROR!</span> Failed to update adjustment category to the database";
               } else {
                  $success = "Adjustment category updated successfully";
               }
            } else {
               $errors[] = "No changes were made";
            }
         }
      }
      

   } else {
      $adjustmentCategoryName ? $details['adjustmentCategoryName'] = $adjustmentCategoryName : $errors[] = "Please submit valid adjustment category name";
      $categoryDescription ? $details['adjustmentCategoryDescription'] = $categoryDescription : $errors[] = "Please submit valid category description";
      $adjustmentTypeID ? $details['adjustmentTypeID'] = $adjustmentTypeID : $errors[] = "Please submit valid adjustment type ID";

      if(count($errors) === 0) {
         if(!$DBConn->insert_data('tija_tax_adjustment_categories', $details)) {
            $errors[] = "<span class='t600'>ERROR!</span> Failed to add adjustment category to the database";
         } else {
            $success = "Adjustment category added successfully";
         }
      }
   }

   

} else { 
   $errors[] = 'You need to log in as a valid administrator to do that.';
}
$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
  var_dump($returnURL);
if (count($errors) == 0) {
  $DBConn->commit();
  $messages = array(array('Text'=>"{$success}", 'Type'=>'success'));
} else {
   $DBConn->rollback();
   $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
}
$_SESSION['FlashMessages'] = serialize($messages);
header("location:{$base}html/{$returnURL}");?>