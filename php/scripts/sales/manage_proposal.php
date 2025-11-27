
<?php
session_start();
$base = '../../../';
set_include_path($base);

// Start output buffering
ob_start();

include 'php/includes.php';

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
$isAjax = $isAjax || (!empty($_POST['ajax']) || !empty($_GET['ajax']));

// Initialize JSON response for AJAX requests
$ajaxResponse = array(
    'success' => false,
    'message' => '',
    'data' => null
);

$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
$success="";
if ($isValidUser) {
	// Only dump if not AJAX
	if (!$isAjax) {
		var_dump($_POST);
		var_dump($_FILES);
	}

   $employeeID = (isset($_POST['employeID']) && !empty($_POST['employeID'])) ? Utility::clean_string($_POST['employeID']) : "";
   $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ? Utility::clean_string($_POST['entityID']) : "";
   $orgDataID = (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ? Utility::clean_string($_POST['orgDataID']) : "";
   $proposalID = (isset($_POST['proposalID']) && !empty($_POST['proposalID'])) ? Utility::clean_string($_POST['proposalID']) : "";
   $proposalTitle = (isset($_POST['proposalTitle']) && !empty($_POST['proposalTitle'])) ? Utility::clean_string($_POST['proposalTitle']) : "";
   $clientID = (isset($_POST['clientID']) && !empty($_POST['clientID'])) ? Utility::clean_string($_POST['clientID']) : "";
   $salesCaseID = (isset($_POST['salesCaseID']) && !empty($_POST['salesCaseID'])) ? Utility::clean_string($_POST['salesCaseID']) : "";
   $proposalCode = (isset($_POST['proposalCode']) && !empty($_POST['proposalCode'])) ? Utility::clean_string($_POST['proposalCode']) : Utility::genrateRandomInteger(4)."_".date('Y');

   $proposalDeadline = (isset($_POST['proposalDeadline']) && !empty($_POST['proposalDeadline'])) ? Utility::clean_string($_POST['proposalDeadline']) : "";
   // ISO date format check for proposalDeadline
   if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $proposalDeadline)) {
       $errors[] = "Invalid proposal deadline format. Please use YYYY-MM-DD.";
   }
   $proposalStatusID = (isset($_POST['proposalStatusID']) && !empty($_POST['proposalStatusID'])) ? Utility::clean_string($_POST['proposalStatusID']) : "";
   $proposalDescription = (isset($_POST['proposalDescription']) && !empty($_POST['proposalDescription'])) ? Utility::sanitize_rich_text_input($_POST['proposalDescription']) : "";
   $proposalComments = (isset($_POST['proposalComments']) && !empty($_POST['proposalComments'])) ? Utility::sanitize_rich_text_input($_POST['proposalComments']) : "";
   $proposalValue = (isset($_POST['proposalValue']) && !empty($_POST['proposalValue'])) ? Utility::clean_string($_POST['proposalValue']) : "";


  $proposalFile ="";

  if(isset($_FILES['proposalFile']) && isset($_FILES['proposalFile']['error']) && $_FILES['proposalFile']['error'] === 0 ){
      $proposalFile = $_FILES['proposalFile'];
      $fileName = $proposalFile['name'];
      $fileTmpName = $proposalFile['tmp_name'];
      $fileSize = $proposalFile['size'];
      $fileError = $proposalFile['error'];
      $fileType = $proposalFile['type'];
      $fileExt = explode('.', $fileName);
      $fileActualExt = strtolower(end($fileExt));
      $allowed = array('pdf', 'docx', 'doc', 'pptx', 'ppt', 'xlsx', 'xls');
      if(in_array($fileActualExt, $allowed)){
         if($fileError === 0){
            $uploadDir = 'uploads/proposals/';
            if(!is_dir($uploadDir)){
               mkdir($uploadDir, 0777, true);
            }


            // Check file size is less tha 10 mb
            if($fileSize > 10000000){
               $errors[] = "Your file is too big!";

            }


            $fileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fileName);
            $fileName = preg_replace('/_+/', '_', $fileName);

            $fileNameNew = uniqid('', true).".".$fileActualExt;
            $fileDestination = $uploadDir.$fileNameNew;
            if(move_uploaded_file($fileTmpName, $fileDestination)) {
               $proposalFile = $fileNameNew;
            } else {
               $errors[] = "There was an error uploading your file!";
            }
             // if($fileSize < 10000000){
            //    $fileNameNew = uniqid('', true).".".$fileActualExt;
            //    $fileDestination = 'uploads/proposals/'.$fileNameNew;
            //    move_uploaded_file($fileTmpName, $fileDestination);
            // } else {
            //    $errors[] = "Your file is too big!";
            // }
         } else {
            $errors[] = "There was an error uploading your file!";
         }
      } else {
         $errors[] = "You cannot upload files of this type!";
      }
   }


   if(!$proposalID) {
      $proposalTitle ? $details['proposalTitle'] = Utility::clean_string($proposalTitle) : $errors[] = "Please submit valid proposal title";
      $clientID ? $details['clientID'] = Utility::clean_string($clientID) : $errors[] = "Please submit valid client ID";
      $salesCaseID ? $details['salesCaseID'] = Utility::clean_string($salesCaseID) : $errors[] = "Please submit valid sales case ID";
      $proposalDeadline ? $details['proposalDeadline'] = Utility::clean_string($proposalDeadline) : $errors[] = "Please submit valid proposal deadline";
      $proposalStatusID ? $details['proposalStatusID'] = Utility::clean_string($proposalStatusID) : $errors[] = "Please submit valid proposal status ID";
      $proposalDescription ? $details['proposalDescription'] = Utility::clean_string($proposalDescription) : $errors[] = "Please submit valid proposal description";
      $proposalComments ? $details['proposalComments'] = Utility::clean_string($proposalComments) : "";
      $proposalValue ? $details['proposalValue'] = Utility::clean_string($proposalValue) : $errors[] = "Please submit valid proposal value";
      $employeeID ? $details['employeeID'] = Utility::clean_string($employeeID) : "";
      $entityID ? $details['entityID'] = Utility::clean_string($entityID) : "";
      $orgDataID ? $details['orgDataID'] = Utility::clean_string($orgDataID) : "";
      $proposalCode ? $details['proposalCode'] = Utility::clean_string($proposalCode) : $errors[] = "Please submit valid proposal code";

      if (count($errors) === 0) {
         if ($proposalFile) {
            $details['proposalFile'] = Utility::clean_string($proposalFile);
         }
      }

      if(!$errors){
         if($details){
            $details['LastUpdate'] = $config['currentDateTimeFormated'];
            $details['LastUpdateByID'] = $userDetails->ID;
            $details['DateAdded'] = $config['currentDateTimeFormated'];
            if (!$DBConn->insert_data("tija_proposals", $details)) {
               $errors[]= "ERROR adding new proposal to the database";
            } else {
               $success = "Proposal added successfully";
               $proposalID = $DBConn->lastInsertId();

            }
         }
      }

   } else {
      $proposalDetails = Sales::proposals(array("proposalID"=> $proposalID), true, $DBConn);
      var_dump($proposalDetails);
      $proposalTitle && ($proposalTitle !== $proposalDetails->proposalTitle) ? $changes['proposalTitle'] = $proposalTitle : "";
      $clientID && ($clientID !== $proposalDetails->clientID) ? $changes['clientID'] = $clientID : "";
      $salesCaseID && ($salesCaseID !== $proposalDetails->salesCaseID) ? $changes['salesCaseID'] = $salesCaseID : "";
      $proposalDeadline && ($proposalDeadline !== $proposalDetails->proposalDeadline) ? $changes['proposalDeadline'] = $proposalDeadline : "";
      $proposalStatusID && ($proposalStatusID !== $proposalDetails->proposalStatusID) ? $changes['proposalStatusID'] = $proposalStatusID : "";
      $proposalDescription && ($proposalDescription !== $proposalDetails->proposalDescription) ? $changes['proposalDescription'] = $proposalDescription : "";
      $proposalComments && ($proposalComments !== $proposalDetails->proposalComments) ? $changes['proposalComments'] = $proposalComments : "";
      $proposalValue && ($proposalValue !== $proposalDetails->proposalValue) ? $changes['proposalValue'] = $proposalValue : "";
      $employeeID && ($employeeID !== $proposalDetails->employeeID) ? $changes['employeeID'] = $employeeID : "";
      $entityID && ($entityID !== $proposalDetails->entityID) ? $changes['entityID'] = $entityID : "";
      $orgDataID && ($orgDataID !== $proposalDetails->orgDataID) ? $changes['orgDataID'] = $orgDataID : "";
      $proposalCode && ($proposalCode !== $proposalDetails->proposalCode) ? $changes['proposalCode'] = $proposalCode : "";
      if (count($errors) === 0) {
         if ($changes) {
            $changes['LastUpdate'] = $config['currentDateTimeFormated'];
            $changes['LastUpdateByID'] = $userDetails->ID;
            if (!$DBConn->update_table("tija_proposals", $changes, array("proposalID"=>$proposalID))) {
               $errors[]= "ERROR updating proposal details in the database";
            } else {
               $success = "Proposal updated successfully";
            }
         }
      }
   }

   var_dump($details);


} else {
	$errors[] = 'You need to log in as a valid user to add new sales case.';
}

// Only dump if not AJAX
if (!$isAjax) {
	var_dump($errors);
}

$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');

// Only dump if not AJAX
if (!$isAjax) {
	var_dump($returnURL);
}

if (count($errors) == 0) {
	$DBConn->commit();
	$messages = array(array('Text'=>$success, 'Type'=>'success'));

	// If AJAX request, return JSON
	if ($isAjax) {
		ob_clean();
		header('Content-Type: application/json');
		$ajaxResponse['success'] = true;
		$ajaxResponse['message'] = $success;
		$ajaxResponse['data'] = array(
			'proposalID' => $proposalID ?? null,
			'redirectURL' => $base . 'html/' . $returnURL
		);
		echo json_encode($ajaxResponse);
		exit;
	}
} else {
	$DBConn->rollback();
	$messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);

	// If AJAX request, return JSON error
	if ($isAjax) {
		ob_clean();
		header('Content-Type: application/json');
		$ajaxResponse['success'] = false;
		$ajaxResponse['message'] = implode('; ', $errors);
		$ajaxResponse['errors'] = $errors;
		echo json_encode($ajaxResponse);
		exit;
	}
}

// For non-AJAX requests, use session flash messages and redirect
$_SESSION['FlashMessages'] = serialize($messages);
header("location:{$base}html/{$returnURL}");
exit;
?>