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

   // Determine action from POST (form submit) or GET (delete via link)
   $action = null;
   if (isset($_POST['action']) && $_POST['action'] !== '') {
      $action = $_POST['action'];
   } elseif (isset($_GET['action']) && $_GET['action'] !== '') {
      $action = $_GET['action'];
   }

   // ----------------------------------------------------------------------
   // DELETE RELATIONSHIP (GET)
   // ----------------------------------------------------------------------
   if ($action === 'delete') {
      $clientRelationshipID = isset($_GET['clientRelationshipID']) ? (int)$_GET['clientRelationshipID'] : 0;

      if ($clientRelationshipID <= 0) {
         $errors[] = 'Invalid client relationship specified for deletion.';
      } else {
         // Optional: fetch details for messaging/auditing
         $clientRelationshipDetails = Client::client_relationships(
            ['clientRelationshipID' => $clientRelationshipID],
            true,
            $DBConn
         );

         if (!$DBConn->delete_row('client_relationship_assignments', ['clientRelationshipID' => $clientRelationshipID])) {
            $errors[] = 'Failed to delete client relationship. Please try again.';
         } else {
            $clientName = ($clientRelationshipDetails && isset($clientRelationshipDetails->clientName))
               ? $clientRelationshipDetails->clientName
               : 'client';
            $success = "Client relationship for {$clientName} deleted successfully.";
         }
      }

   // ----------------------------------------------------------------------
   // ADD / EDIT RELATIONSHIP (POST)
   // ----------------------------------------------------------------------
   } else {
      if ($action != 'add' && $action != 'edit') {
         $errors[] = 'Invalid action specified';
      }

      $clientRelationshipID = (isset($_POST['clientRelationshipID']) && $_POST['clientRelationshipID'] != '') ? $_POST['clientRelationshipID'] : null;
      $clientID = (isset($_POST['clientID']) && $_POST['clientID'] != '') ? $_POST['clientID'] : null;
      $employeeID = (isset($_POST['employeeID']) && $_POST['employeeID'] != '') ? $_POST['employeeID'] : null;
      $clientRelationshipType = (isset($_POST['clientRelationshipType']) && $_POST['clientRelationshipType'] != '') ? $_POST['clientRelationshipType'] : null;
      $relationshipStatus = (isset($_POST['relationshipStatus']) && $_POST['relationshipStatus'] != '') ? $_POST['relationshipStatus'] : null;
      $startDate = (isset($_POST['startDate']) && $_POST['startDate'] != '') ? $_POST['startDate'] : date('Y-m-d');
      $endDate = (isset($_POST['endDate']) && $_POST['endDate'] != '') ? $_POST['endDate'] : null;
      $notes = (isset($_POST['notes']) && $_POST['notes'] != '') ? $_POST['notes'] : null;

      if($clientRelationshipID) {
         $clientRelationshipDetails = Client::client_relationships(['clientRelationshipID'=>$clientRelationshipID], true, $DBConn);

         if($clientRelationshipDetails) {
         $clientID && $clientID != $clientRelationshipDetails->clientID ? $changes['clientID'] = $clientID : '';
         $employeeID && $employeeID != $clientRelationshipDetails->employeeID ? $changes['employeeID'] = $employeeID : '';
         $clientRelationshipType && $clientRelationshipType != $clientRelationshipDetails->clientRelationshipType ? $changes['clientRelationshipType'] = $clientRelationshipType : '';
         $relationshipStatus && $relationshipStatus != $clientRelationshipDetails->relationshipStatus ? $changes['relationshipStatus'] = $relationshipStatus : '';
         $startDate && $startDate != $clientRelationshipDetails->startDate ? $changes['startDate'] = $startDate : '';
         $endDate && $endDate != $clientRelationshipDetails->endDate ? $changes['endDate'] = $endDate : '';
         $notes && $notes != $clientRelationshipDetails->notes ? $changes['notes'] = $notes : '';

         if(!$errors) {
            $changes['LastUpdateByID'] = $userDetails->ID;
            $changes['LastUpdate'] = date('Y-m-d H:i:s');
            if(!$DBConn->update_table('client_relationship_assignments', $changes, array('clientRelationshipID'=>$clientRelationshipID))) {
               $errors[] = 'Failed to update client relationship';
            } else {
               $success = 'Client Relationship updated successfully';
               $employeeDetails = Employee::employees(array('ID'=>$employeeID), true, $DBConn);

               $notificationArr = array(
                  'employeeID' => $clientRelationshipDetails->employeeID,
                  'approverID' => $userDetails->ID,
                  'segmentType'=> "client_relationships",
                  "segmentID" => $clientRelationshipDetails->clientRelationshipID,
                  "notificationNotes" => "<p>Client Relationship for {$clientRelationshipDetails->clientName} has been updated by {$employeeDetails->employeeNameWithInitials}</p>
                                          <p><a href='{$base}html/?s=user&ss=clients&p=client_details&clientID={$clientRelationshipDetails->clientID}'>View Client Relationship</a></p>
                                          <p> You have been assigned to this client relationship.</p>",
                  'notificationType' => "client_relationships_{$action}",
                  'notificationText' => "Client Relationship for {$clientRelationshipDetails->clientName} has been updated by {$employeeDetails->employeeNameWithInitials}
                                          <p> You have been assigned to this client relationship as a {$clientRelationshipType}</p>

                                          <a href='{$base}html/?s=user&ss=clients&p=client_details&clientID={$clientRelationshipDetails->clientID}'>View Client Relationship</a>",
                  'notificationStatus' => 'unread',
                  'originatorUserID' => $userDetails->ID,
                  'targetUserID' => $clientRelationshipDetails->employeeID,

               );

               if($notificationArr) {
                  if(!$DBConn->insert_data('tija_notifications', $notificationArr)) {
                     $errors[] = 'Failed to create notification for client relationship update';
                  } else {
                     $success .= ' and notification created successfully';
                  }
               }
            }
         }

      } else {
         $errors[] = 'Client Relationship not found';
      }

      } else {
         $clientID ? $details['clientID'] = $clientID : $errors[] = 'Client is required';
         $employeeID ? $details['employeeID'] = $employeeID : $errors[] = 'Employee is required';
         $clientRelationshipType ? $details['clientRelationshipType'] = $clientRelationshipType : $errors[] = 'Relationship Type is required';
         $relationshipStatus ? $details['relationshipStatus'] = $relationshipStatus : "";
         $startDate ? $details['startDate'] = $startDate : date('Y-m-d');
         $endDate ? $details['endDate'] = $endDate : "";
         $notes ? $details['notes'] = $notes : "";

         if(!$errors) {
            if($details){
               $details['LastUpdateByID'] = $userDetails->ID;
               $details['LastUpdate'] = date('Y-m-d H:i:s');
               if(!$DBConn->insert_data('client_relationship_assignments', $details)) {
                  $errors[] = 'Failed to create client relationship';
               } else {
                  $clientRelationshipID = $DBConn->lastInsertID();
                  $success = 'Client Relationship created successfully';

                  // Optional notification for new relationship
                  $employeeDetails = Employee::employees(array('ID'=>$employeeID), true, $DBConn);
                  if ($employeeDetails) {
                     $notificationArr = array(
                        'employeeID' => $employeeID,
                        'approverID' => $userDetails->ID,
                        'segmentType'=> "client_relationships",
                        "segmentID" => $clientRelationshipID,
                        "notificationNotes" => "<p>You have been assigned to a client relationship.</p>",
                        'notificationType' => "client_relationships_{$action}",
                        'notificationStatus' => 'unread',
                     );

                     if($notificationArr) {
                        if(!$DBConn->insert_data('tija_notifications', $notificationArr)) {
                           $errors[] = 'Failed to create notification for client relationship.';
                        } else {
                           $success .= ' and notification created successfully';
                        }
                     }
                  }
               }
            }
         }
      }
   } // end add/edit block

} else {
   $errors[] = 'You need to log in as a valid administrator to do that.';
}
$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performance&p=home');
if (count($errors) == 0) {
  $DBConn->commit();
  $messages = array(array('Text'=>"{$success}", 'Type'=>'success'));
} else {
   $DBConn->rollback();
   $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
}
$_SESSION['FlashMessages'] = serialize($messages);
header("location:{$base}html/{$returnURL}");?>