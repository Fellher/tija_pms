<?php
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
$addressDetails = array();
$success = "";

if ($isValidUser) {
   // Handle DELETE action (GET request)
   if (isset($_GET['action']) && $_GET['action'] === 'delete') {
      $clientDocumentID = isset($_GET['clientDocumentID']) ? (int)$_GET['clientDocumentID'] : 0;

      if ($clientDocumentID <= 0) {
         $errors[] = 'Invalid document ID for deletion.';
      } else {
         // Get document details before deleting
         $documentDetails = Client::client_documents(['clientDocumentID' => $clientDocumentID], true, $DBConn);

         if (!$documentDetails) {
            $errors[] = 'Document not found.';
         } else {
            // Delete the physical file if it exists
            if (isset($documentDetails->clientDocumentFile) && file_exists($documentDetails->clientDocumentFile)) {
               @unlink($documentDetails->clientDocumentFile);
            }

            // Delete from database
            if (!$DBConn->delete_row('tija_client_documents', ['clientDocumentID' => $clientDocumentID])) {
               $errors[] = 'Failed to delete document from database.';
            } else {
               $success = "Document '{$documentDetails->clientDocumentName}' deleted successfully.";
            }
         }
      }

      // Skip to the end
      goto end_processing;
   }

   var_dump($_POST);
   var_dump($_FILES);
   $fileUpload = null;

   $clientID = (isset($_POST['clientID']) && !empty($_POST['clientID'])) ? Utility::clean_string($_POST['clientID']) : null;
   $clientDocumentID = (isset($_POST['clientDocumentID']) && !empty($_POST['clientDocumentID'])) ? Utility::clean_string($_POST['clientDocumentID']) : null;
   $clientDocumentName = (isset($_POST['clientDocumentName']) && !empty($_POST['clientDocumentName'])) ? Utility::clean_string($_POST['clientDocumentName']) : null;
   $clientDocumentDescription = (isset($_POST['clientDocumentDescription']) && !empty($_POST['clientDocumentDescription'])) ? Utility::clean_string($_POST['clientDocumentDescription']) : null;
   $documentTypeID = (isset($_POST['documentTypeID']) && !empty($_POST['documentTypeID'])) ? Utility::clean_string($_POST['documentTypeID']) : null;
   $documentTypeName = (isset($_POST['documentTypeName']) && !empty($_POST['documentTypeName'])) ? Utility::clean_string($_POST['documentTypeName']) : null;
   $documentTypeDescription = (isset($_POST['documentTypeDescription']) && !empty($_POST['documentTypeDescription'])) ? Utility::clean_string($_POST['documentTypeDescription']) : null;
   $clientDocumentFile = (isset($_FILES['clientDocumentFile']) && !empty($_FILES['clientDocumentFile']['name'])) ? $_FILES['clientDocumentFile'] : null;

   if(!$clientDocumentID) {
      $clientID ? $details['clientID'] = $clientID : $errors[] = 'Client ID is required.';
      $clientDocumentName ? $details['clientDocumentName'] = $clientDocumentName : $errors[] = 'Document name is required.';
      $clientDocumentDescription ? $details['clientDocumentDescription'] = $clientDocumentDescription : $errors[] = 'Document description is required.';
      $documentTypeID ? $details['documentTypeID'] = $documentTypeID : $errors[] = 'Document type is required.';
      if($documentTypeID == 'other') {
         $documentTypeName ? $type_details['documentTypeName'] = $documentTypeName : $errors[] = 'Document type name is required.';
         $documentTypeDescription ? $type_details['DocumentTypeDescription'] = $documentTypeDescription : $errors[] = 'Document type description is required.';

         if(count($errors) == 0) {
            if($type_details){
               $type_details['LastUpdateByID'] = $userDetails->ID;
               $type_details['LastUpdate'] = $config['currentDateTimeFormated'];
               echo "<h5>Type Details</h5>";
               var_dump($type_details);
               if(!$DBConn->insert_data('tija_document_types', $type_details)) {
                  $errors[] = 'Failed to update document type details.';
               } else {
                  $success = "Document type updated successfully.";
                  $documentTypeID = $DBConn->lastInsertId();
                  $details['documentTypeID'] = $documentTypeID;
               }
            }
         }
      }

      if($clientDocumentFile) {
         $fileUpload = File::upload_file($clientDocumentFile, 'client_documents', "", $allowedFileSize= 1024 * 1024 * 20 , $config, $DBConn);

         var_dump($fileUpload);
         if($fileUpload['status'] == 'error') {
            $errors[] = $fileUpload['message'];
         } else {
            $details['clientDocumentFile'] = $fileUpload['uploadedFilePaths'];
            $details['documentFileName'] = $fileUpload['fileName'];
            $details['documentFileType'] = $fileUpload['fileType'];
            $details['documentFileSize'] = $fileUpload['fileSize'];
            $details['documentFilePath'] = $fileUpload['fileDestination'];


         }
      } else {
         $errors[] = 'Document file is required.';
      }

      if(!$errors){
         if($details){
            $details['LastUpdateByID'] = $userDetails->ID;
            $details['LastUpdate'] = $config['currentDateTimeFormated'];

            var_dump($details);
            if(!$DBConn->insert_data('tija_client_documents', $details)) {
               $errors[] = 'Failed to add client document details.';
            } else {
               $success = "Client Document added successfully.";
            }
         }

      }
   } else {
      $clientDocumentDetails = Client::client_documents(['clientDocumentID'=> $clientDocumentID], true, $DBConn);
      if(!$clientDocumentDetails) {
         $errors[] = 'Invalid client document Details.';
      } else {
         var_dump($clientDocumentDetails);
         $clientID && $clientID != $clientDocumentDetails->clientID ? $changes['clientID'] = $clientID : '';
         $clientDocumentName && $clientDocumentName != $clientDocumentDetails->clientDocumentName ? $changes['clientDocumentName'] = $clientDocumentName : '';
         $clientDocumentDescription && $clientDocumentDescription != $clientDocumentDetails->clientDocumentDescription ? $changes['clientDocumentDescription'] = $clientDocumentDescription : '';
         $documentTypeID && $documentTypeID != $clientDocumentDetails->documentTypeID ? $changes['documentTypeID'] = $documentTypeID : '';
         if($fileUpload && $fileUpload['status'] == 'success') {
            $changes['clientDocumentFile'] = $fileUpload['uploadedFilePaths'];
            $changes['documentFileName'] = $fileUpload['fileName'];
            $changes['documentFileType'] = $fileUpload['fileType'];
            $changes['documentFileSize'] = $fileUpload['fileSize'];
            $changes['documentFilePath'] = $fileUpload['fileDestination'];
         } elseif($fileUpload && $fileUpload['status'] == 'error') {
            $errors = array_merge($errors, $fileUpload['errors']);

         }

         if(!$errors){
            if($changes && count($changes) > 0) {
               $changes['LastUpdateByID'] = $userDetails->ID;
               $changes['LastUpdate'] = $config['currentDateTimeFormated'];
               var_dump($changes);
               if(!$DBConn->update_table('tija_client_documents', $changes, array('clientDocumentID'=>$clientDocumentID))) {
                  $errors[] = 'Failed to update client document details.';
               } else {
                  $success = "Client Document updated successfully.";
               }
            } else {
               $success = "No changes made to the client document.";
            }
         }


      }
   }

   var_dump($errors);
} else {
   $errors[] = 'You need to log in as a valid administrator to do that.';
}

end_processing:
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

