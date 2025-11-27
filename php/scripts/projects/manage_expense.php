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


	$userID = (isset($_POST['userID']) && !empty($_POST['userID'])) ? Utility::clean_string($_POST['userID']) : "";
	$projectID = (isset($_POST['projectID']) && !empty($_POST['projectID'])) ? Utility::clean_string($_POST['projectID']) : "";

	$expenseTypeID = (isset($_POST['expenseTypeID']) && !empty($_POST['expenseTypeID'])) ? Utility::clean_string($_POST['expenseTypeID']) : "";
	$expenseDate = (isset($_POST['expenseDate']) && !empty($_POST['expenseDate']) && preg_match($config['ISODateFormat'], Utility::clean_string($_POST['expenseDate']))) ? Utility::clean_string($_POST['expenseDate']) : "";
	$expenseAmount = (isset($_POST['expenseAmount']) && !empty($_POST['expenseAmount'])) ? Utility::clean_string($_POST['expenseAmount']) : "";
	$expenseDescription = (isset($_POST['expenseDescription']) && !empty($_POST['expenseDescription'])) ? $_POST['expenseDescription']: "";
	$expenseID = (isset($_POST['expenseID']) && !empty($_POST['expenseID'])) ? Utility::clean_string($_POST['expenseID']) : "";
	$expenseDocuments = (isset($_FILES['expenseDocuments']) && !empty($_FILES['expenseDocuments'])) ? $_FILES['expenseDocuments'] : null;

	
	// upload expense documents multiple format using FileUpload class
	if ($expenseDocuments && is_array($expenseDocuments) && count($expenseDocuments) > 0) {
		var_dump($expenseDocuments);
		$fileUpload = File::multiple_file_upload($_FILES, "expense_files", $config['ValidFileTypes'], $config['maxExpenseDocumentSize'], $config, $DBConn);
		if (count($fileUpload['success']) > 0 && !empty($fileUpload['filePaths'])) {
			$expenseDocuments = $fileUpload['filePaths'];
		} else {
			$errors = array_merge($errors, $fileUpload['errors']);
		}
	} else {
		$expenseDocuments = null;
	}



	if ($expenseID) {
		$expenseDetails = Work::project_expenses(array("expenseID"=>$expenseID), true, $DBConn);
		($expenseTypeID && $expenseTypeID !== $expenseDetails->expenseTypeID) ? $changes['expenseTypeID'] = $expenseTypeID : "";
		($expenseDate && $expenseDate !== $expenseDetails->expenseDate) ? $changes['expenseDate'] = $expenseDate : "";
		($expenseAmount && $expenseAmount !== $expenseDetails->expenseAmount) ? $changes['expenseAmount'] = $expenseAmount : "";
		($expenseDescription && $expenseDescription !== $expenseDetails->expenseDescription) ? $changes['expenseDescription'] = $expenseDescription : "";
		($userID && $userID !== $expenseDetails->userID) ? $changes['userID'] = $userID : "";
		$expenseDocuments && $expenseDocuments !== $expenseDetails->expenseDocuments ? $changes['expenseDocuments'] = $expenseDocuments : "";


		

		if (count($errors) === 0 ) {
			if ($changes) {
				$changes['LastUpdate'] = $config['currentDateTimeFormated'];
				if (!$DBConn->update_table('sbsl_project_expenses', $changes, array("expenseID"=>$expenseID))) {
					$errors[] = "Error saving the expense updates to the database";
				} else {
					$success = "Successfully updates expense to the database";
				}
			}
		}		
	} else {
		$projectID ? $details['projectID'] = $projectID : $errors[] = "Please submit valid project for the expense";
		$expenseTypeID ? $details['expenseTypeID'] = $expenseTypeID : $errors[] = "Please submit valid expense type for the expense";
		$expenseAmount ? $details['expenseAmount'] = $expenseAmount : $errors[] = "Please submit valid expense amount";
		$expenseDescription ? $details['expenseDescription'] = $expenseDescription : $errors[] = "Please submit valid expense Description /notes";
		$expenseDate ? $details['expenseDate'] = $expenseDate : $errors[] = "Please submit valid date the expense  /notes";
		$userID ? $details['userID'] = $userID : $errors[] = "Please submit valid user the expense  /notes";
		$expenseDocuments ? $details['expenseDocuments'] = $expenseDocuments : "";

		var_dump($details);

		if (count($errors) === 0) {
			if ($details) {
				if (!$DBConn->insert_data("sbsl_project_expenses", $details)) {
					$errors[]=" error saving the expense details to the database";					
				} else {
					$expenseID= $DBConn->lastInsertID();
					$success= "Successfully saved expense to the database";
				}			
			}			
		}		
	}

} else {
	$errors[] = 'You need to log in as a valid user to add new sales case.';
} 
	
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
//  header("location:{$base}html/?{$returnURL}");
?>