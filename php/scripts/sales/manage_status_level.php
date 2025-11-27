
<?php
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
$success="";
if ($isValidAdmin || $isAdmin ) {
	var_dump($_POST);
   $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ?  Utility::clean_string($_POST['entityID']): "";
   $orgDataID = (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ?  Utility::clean_string($_POST['orgDataID']): "";
   $userID = (isset($_POST['userID']) && !empty($_POST['userID'])) ?  Utility::clean_string($_POST['userID']): "";
   $saleStatusLevelID= (isset($_POST['saleStatusLevelID']) && !empty($_POST['saleStatusLevelID'])) ?  Utility::clean_string($_POST['saleStatusLevelID']): "";
   $statusLevel = (isset($_POST['statusLevel']) && !empty($_POST['statusLevel'])) ?  Utility::clean_string($_POST['statusLevel']): "";
   $levelPercentage = (isset($_POST['levelPercentage']) && !empty($_POST['levelPercentage'])) ?  Utility::clean_string($_POST['levelPercentage']): "";
   $StatusLevelDescription = (isset($_POST['StatusLevelDescription']) && !empty($_POST['StatusLevelDescription'])) ?  Utility::clean_string($_POST['StatusLevelDescription']): "";
   // $statusColor = (isset($_POST['statusColor']) && !empty($_POST['statusColor'])) ?  Utility::clean_string($_POST['statusColor']): "";
   // $statusOrder = (isset($_POST['statusOrder']) && !empty($_POST['statusOrder'])) ?  Utility::clean_string($_POST['statusOrder']): "";
   $previousLevelID = (isset($_POST['previousLevel']) && !empty($_POST['previousLevel'])) ?  Utility::clean_string($_POST['previousLevel']): "";
   echo "<h4> Previous Level is {$previousLevelID}</h4>";
   if(empty($previousLevelID)){
      $statusOrder=1;
   } else {
      $statusOrder = Data::sales_status_levels(array('saleStatusLevelID'=>$previousLevelID, 'entityID'=>$entityID), true, $DBConn)->statusOrder + 1;
      var_dump($statusOrder);
   }

   var_dump($statusOrder);

   if(!$saleStatusLevelID){
      $entityID ? $details['entityID'] = $entityID : $errors[] = 'Entity ID is required';
      $orgDataID ? $details['orgDataID'] = $orgDataID : $errors[] = 'Organization ID is required';
      $statusLevel ? $details['statusLevel'] = $statusLevel : $errors[] = 'Status Level is required';
      $StatusLevelDescription ? $details['StatusLevelDescription'] = $StatusLevelDescription : $errors[] = 'Status Description is required';
      $statusOrder ? $details['statusOrder'] = $statusOrder : $errors[] = 'Status Order is required';
      $levelPercentage ? $details['levelPercentage'] = $levelPercentage : $errors[] = 'Level Percentage is required';
      $previousLevelID ? $details['previousLevelID'] = $previousLevelID : "";

      var_dump($changes);
      
      if(count($errors) == 0){
        if($details){
            $details['LastUpdate'] = $config['currentDateTimeFormated'];
            $details['LastUpdatedByID'] = $userID;

            var_dump($details);
           
            if(!$DBConn->insert_data('tija_sales_status_levels', $details)){
                $errors[] = 'There was an error adding the status level.';
            } else {
                $success = 'Status level added successfully.';
            }
        }
      }


   } else {
      $statusLevelDetails = Data::sales_status_levels(array('saleStatusLevelID'=>$saleStatusLevelID), true, $DBConn);
      var_dump($entityID);
      var_dump($statusLevel);
      
      $entityID && $entityID !== $statusLevelDetails->entityID ? $changes['entityID'] = $entityID: "";
      $orgDataID && $orgDataID !== $statusLevelDetails->orgDataID ? $changes['orgDataID'] = $orgDataID: "";
      $statusLevel && $statusLevel !== $statusLevelDetails->statusLevel ? $changes['statusLevel'] = $statusLevel: "";
      $StatusLevelDescription && $StatusLevelDescription !== $statusLevelDetails->StatusLevelDescription ? $changes['StatusLevelDescription'] = $StatusLevelDescription: "";
      $statusOrder && $statusOrder !== $statusLevelDetails->statusOrder ? $changes['statusOrder'] = $statusOrder: "";
      $levelPercentage && $levelPercentage !== $statusLevelDetails->levelPercentage ? $changes['levelPercentage'] = $levelPercentage: "";
      // $statusColor && $statusColor !== $statusLevelDetails->statusColor ? $changes['statusColor'] = $statusColor: "";
      $statusOrder && $statusOrder !== $statusLevelDetails->statusOrder ? $changes['statusOrder'] = $statusOrder: "";
      $previousLevelID && $previousLevelID !== $statusLevelDetails->previousLevelID ? $changes['previousLevelID'] = $previousLevelID: "";

      var_dump($changes);
      if(!$errors) {
         if($changes){
            $changes['LastUpdate'] = $config['currentDateTimeFormated'];
            $changes['LastUpdatedByID'] = $userID;
            if(!$DBConn->update_table('tija_sales_status_levels', $changes, array('saleStatusLevelID'=>$saleStatusLevelID))){
               $errors[] = 'There was an error updating the status level.';
            } else {
               $success = 'Status level updated successfully.';
            }
         }
      }

      var_dump($statusLevelDetails);
      $previousLevel = $statusLevelDetails->statusOrder;
   }
   var_dump($errors);





   

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