<?php
/**
 * Activity Wizard Backend Processing
 * Handles comprehensive activity creation and updates with all wizard fields
 *
 * @package    Tija CRM
 * @subpackage Sales Management
 * @version    2.0.0
 */

session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';

$DBConn->begin();
$errors = array();
$details = array();
$changes = array();
$success = "";

if ($isValidUser) {

   // ============================================================================
   // STEP 1: BASIC INFORMATION
   // ============================================================================
   $activityID = (isset($_POST['activityID']) && !empty($_POST['activityID'])) ? Utility::clean_string($_POST['activityID']) : '';
   $activityName = (isset($_POST['activityName']) && !empty($_POST['activityName'])) ? Utility::clean_string($_POST['activityName']) : '';
   $activityDescription = (isset($_POST['activityDescription']) && !empty($_POST['activityDescription'])) ? Utility::clean_string($_POST['activityDescription']) : '';
   $activityCategoryID = (isset($_POST['activityCategoryID']) && !empty($_POST['activityCategoryID'])) ? Utility::clean_string($_POST['activityCategoryID']) : '';
   $activityTypeID = (isset($_POST['activityTypeID']) && !empty($_POST['activityTypeID'])) ? Utility::clean_string($_POST['activityTypeID']) : '';
   $clientID = (isset($_POST['clientID']) && !empty($_POST['clientID'])) ? Utility::clean_string($_POST['clientID']) : '';
   $activityPriority = (isset($_POST['activityPriority']) && !empty($_POST['activityPriority'])) ? Utility::clean_string($_POST['activityPriority']) : 'Medium';
   $activityStatus = (isset($_POST['activityStatus']) && !empty($_POST['activityStatus'])) ? Utility::clean_string($_POST['activityStatus']) : 'notStarted';

   // ============================================================================
   // STEP 2: SCHEDULE & TIMELINE
   // ============================================================================
   $activityDate = (isset($_POST['activityDate']) && !empty($_POST['activityDate'])) ? Utility::clean_string($_POST['activityDate']) : '';
   $durationType = (isset($_POST['durationType']) && !empty($_POST['durationType'])) ? Utility::clean_string($_POST['durationType']) : 'oneOff';
   $activityStartTime = (isset($_POST['activityStartTime']) && !empty($_POST['activityStartTime'])) ? Utility::clean_string($_POST['activityStartTime']) : null;
   $activityEndTime = (isset($_POST['activityEndTime']) && !empty($_POST['activityEndTime'])) ? Utility::clean_string($_POST['activityEndTime']) : null;
   $activityDurationEndDate = (isset($_POST['activityDurationEndDate']) && !empty($_POST['activityDurationEndDate'])) ? Utility::clean_string($_POST['activityDurationEndDate']) : null;
   $activityDurationEndTime = (isset($_POST['activityDurationEndTime']) && !empty($_POST['activityDurationEndTime'])) ? Utility::clean_string($_POST['activityDurationEndTime']) : null;
   $allDayEvent = (isset($_POST['allDayEvent']) && $_POST['allDayEvent'] === 'on') ? 'Y' : 'N';

   // Calculate duration in minutes
   $duration = null;
   if ($activityStartTime && $activityEndTime) {
      list($startH, $startM) = explode(':', $activityStartTime);
      list($endH, $endM) = explode(':', $activityEndTime);
      $duration = (($endH * 60) + $endM) - (($startH * 60) + $startM);
   }

   // Recurrence fields
   $recurring = ($durationType === 'recurring') ? 'Y' : 'N';
   $recurrenceType = (isset($_POST['recurrenceType']) && !empty($_POST['recurrenceType'])) ? Utility::clean_string($_POST['recurrenceType']) : null;
   $recurringInterval = (isset($_POST['recurringInterval']) && !empty($_POST['recurringInterval'])) ? Utility::clean_string($_POST['recurringInterval']) : null;
   $recurringIntervalUnit = (isset($_POST['recurringIntervalUnit']) && !empty($_POST['recurringIntervalUnit'])) ? Utility::clean_string($_POST['recurringIntervalUnit']) : null;
   $recurrenceEndType = (isset($_POST['recurrenceEndType']) && !empty($_POST['recurrenceEndType'])) ? Utility::clean_string($_POST['recurrenceEndType']) : null;
   $numberOfOccurrencesToEnd = (isset($_POST['numberOfOccurrencesToEnd']) && !empty($_POST['numberOfOccurrencesToEnd'])) ? Utility::clean_string($_POST['numberOfOccurrencesToEnd']) : null;
   $recurringEndDate = (isset($_POST['recurringEndDate']) && !empty($_POST['recurringEndDate'])) ? Utility::clean_string($_POST['recurringEndDate']) : null;

   // ============================================================================
   // STEP 3: ADDITIONAL DETAILS
   // ============================================================================
   $activityOwnerID = (isset($_POST['activityOwnerID']) && !empty($_POST['activityOwnerID'])) ? Utility::clean_string($_POST['activityOwnerID']) : '';
   $activityParticipants = (isset($_POST['activityParticipants']) && is_array($_POST['activityParticipants'])) ? json_encode($_POST['activityParticipants']) : null;
   $activityLocation = (isset($_POST['activityLocation']) && !empty($_POST['activityLocation'])) ? Utility::clean_string($_POST['activityLocation']) : '';
   $meetingLink = (isset($_POST['meetingLink']) && !empty($_POST['meetingLink'])) ? Utility::clean_string($_POST['meetingLink']) : '';
   $activityNotes = (isset($_POST['activityNotes']) && !empty($_POST['activityNotes'])) ? Utility::clean_string($_POST['activityNotes']) : '';
   $sendReminder = (isset($_POST['sendReminder']) && $_POST['sendReminder'] === 'on') ? 'Y' : 'N';
   $reminderTime = (isset($_POST['reminderTime']) && !empty($_POST['reminderTime'])) ? Utility::clean_string($_POST['reminderTime']) : null;

   // ============================================================================
   // STEP 4: OUTCOMES & EXPENSES (Multi-Expense Support)
   // ============================================================================
   $activityOutcome = (isset($_POST['activityOutcome']) && !empty($_POST['activityOutcome'])) ? Utility::clean_string($_POST['activityOutcome']) : '';
   $activityResult = (isset($_POST['activityResult']) && !empty($_POST['activityResult'])) ? Utility::clean_string($_POST['activityResult']) : '';
   $requiresFollowUp = (isset($_POST['requiresFollowUp']) && $_POST['requiresFollowUp'] === 'on') ? 'Y' : 'N';
   $followUpNotes = (isset($_POST['followUpNotes']) && !empty($_POST['followUpNotes'])) ? Utility::clean_string($_POST['followUpNotes']) : '';

   // Process Multiple Expenses
   $expenses = array();
   if (isset($_POST['expenses']) && is_array($_POST['expenses'])) {
      foreach ($_POST['expenses'] as $expenseData) {
         if (isset($expenseData['category']) && isset($expenseData['amount']) && floatval($expenseData['amount']) > 0) {
            $expenses[] = array(
               'category' => Utility::clean_string($expenseData['category']),
               'amount' => floatval($expenseData['amount']),
               'description' => isset($expenseData['description']) ? Utility::clean_string($expenseData['description']) : '',
               'paymentMethod' => isset($expenseData['paymentMethod']) ? Utility::clean_string($expenseData['paymentMethod']) : '',
               'receiptNumber' => isset($expenseData['receiptNumber']) ? Utility::clean_string($expenseData['receiptNumber']) : '',
               'reimbursable' => isset($expenseData['reimbursable']) ? Utility::clean_string($expenseData['reimbursable']) : 'Y'
            );
         }
      }
   }

   // ============================================================================
   // CONTEXT FIELDS
   // ============================================================================
   $salesCaseID = (isset($_POST['salesCaseID']) && !empty($_POST['salesCaseID'])) ? Utility::clean_string($_POST['salesCaseID']) : '';
   $orgDataID = (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ? Utility::clean_string($_POST['orgDataID']) : '';
   $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ? Utility::clean_string($_POST['entityID']) : '';
   $activitySegment = (isset($_POST['activitySegment']) && !empty($_POST['activitySegment'])) ? Utility::clean_string($_POST['activitySegment']) : 'sales';

   // ============================================================================
   // CREATE OR UPDATE ACTIVITY
   // ============================================================================
   if (!$activityID) {
      // CREATE NEW ACTIVITY
      // Required field validations
      $activityName ? $details['activityName'] = $activityName : $errors[] = 'Activity name is required.';
      $activityCategoryID ? $details['activityCategoryID'] = $activityCategoryID : $errors[] = 'Activity category is required.';
      $activityTypeID ? $details['activityTypeID'] = $activityTypeID : $errors[] = 'Activity type is required.';
      $activityDate ? $details['activityDate'] = $activityDate : $errors[] = 'Activity date is required.';
      $activityOwnerID ? $details['activityOwnerID'] = $activityOwnerID : $errors[] = 'Activity owner is required.';
      $orgDataID ? $details['orgDataID'] = $orgDataID : $errors[] = 'Organization is required.';
      $entityID ? $details['entityID'] = $entityID : $errors[] = 'Entity is required.';
      $clientID ? $details['clientID'] = $clientID : $errors[] = 'Client is required.';

      // Optional fields
      if ($activityDescription) $details['activityDescription'] = $activityDescription;
      if ($activityPriority) $details['activityPriority'] = $activityPriority;
      if ($activityStatus) $details['activityStatus'] = $activityStatus;
      if ($durationType) $details['durationType'] = $durationType;
      if ($activityStartTime) $details['activityStartTime'] = $activityStartTime;
      if ($activityEndTime) $details['activityDurationEndTime'] = $activityEndTime;
      if ($activityDurationEndDate) $details['activityDurationEndDate'] = $activityDurationEndDate;
      if ($activityDurationEndTime) $details['activityDurationEndTime'] = $activityDurationEndTime;
      if ($allDayEvent) $details['allDayEvent'] = $allDayEvent;
      if ($duration) $details['duration'] = $duration;

      // Recurrence
      if ($recurring) $details['recurring'] = $recurring;
      if ($recurrenceType) $details['recurrenceType'] = $recurrenceType;
      if ($recurringInterval) $details['recurringInterval'] = $recurringInterval;
      if ($recurringIntervalUnit) $details['recurringIntervalUnit'] = $recurringIntervalUnit;
      if ($recurrenceEndType) $details['recurrenceEndType'] = $recurrenceEndType;
      if ($numberOfOccurrencesToEnd) $details['numberOfOccurrencesToEnd'] = $numberOfOccurrencesToEnd;
      if ($recurringEndDate) $details['recurringEndDate'] = $recurringEndDate;

      // Additional details
      if ($activityParticipants) $details['activityParticipants'] = $activityParticipants;
      if ($activityLocation) $details['activityLocation'] = $activityLocation;
      if ($meetingLink) $details['meetingLink'] = $meetingLink;
      if ($activityNotes) $details['activityNotes'] = $activityNotes;
      if ($sendReminder) $details['sendReminder'] = $sendReminder;
      if ($reminderTime) $details['reminderTime'] = $reminderTime;

      // Outcomes & Follow-up
      if ($activityOutcome) $details['activityOutcome'] = $activityOutcome;
      if ($activityResult) $details['activityResult'] = $activityResult;
      if ($requiresFollowUp) $details['requiresFollowUp'] = $requiresFollowUp;
      if ($followUpNotes) $details['followUpNotes'] = $followUpNotes;

      // Context
      if ($salesCaseID) $details['salesCaseID'] = $salesCaseID;
      if ($activitySegment) $details['activitySegment'] = $activitySegment;

      // System fields
      $details['assignedByID'] = $userDetails->ID;
      $details['LastUpdateByID'] = $userDetails->ID;
      $details['LastUpdate'] = $config['currentDateTimeFormated'];

      if (count($errors) == 0) {
         if (!$DBConn->insert_data('tija_activities', $details)) {
            $errors[] = 'There was an error creating the activity. Please try again.';
         } else {
            $success = 'Activity created successfully!';
            $activityID = $DBConn->lastInsertId();

            // Create reminder if needed
            if ($sendReminder === 'Y' && $reminderTime && $activityDate && $activityStartTime) {
               createActivityReminder($activityID, $activityDate, $activityStartTime, $reminderTime, $activityOwnerID, $DBConn, $config);
            }

            // Save multiple expenses
            if (!empty($expenses)) {
               saveActivityExpenses($activityID, $expenses, $activityDate, $userDetails->ID, $DBConn);
            }
         }
      }

   } else {
      // UPDATE EXISTING ACTIVITY
      $activityDetails = Schedule::tija_activities(array('activityID' => $activityID), true, $DBConn);

      if (!$activityDetails) {
         $errors[] = 'Activity not found.';
      } else {
         // Check and add changes
         if ($activityName && $activityDetails->activityName != $activityName) $changes['activityName'] = $activityName;
         if ($activityDescription && $activityDetails->activityDescription != $activityDescription) $changes['activityDescription'] = $activityDescription;
         if ($activityCategoryID && $activityDetails->activityCategoryID != $activityCategoryID) $changes['activityCategoryID'] = $activityCategoryID;
         if ($activityTypeID && $activityDetails->activityTypeID != $activityTypeID) $changes['activityTypeID'] = $activityTypeID;
         if ($activityPriority && $activityDetails->activityPriority != $activityPriority) $changes['activityPriority'] = $activityPriority;
         if ($activityStatus && $activityDetails->activityStatus != $activityStatus) $changes['activityStatus'] = $activityStatus;
         if ($activityDate && $activityDetails->activityDate != $activityDate) $changes['activityDate'] = $activityDate;
         if ($durationType && $activityDetails->durationType != $durationType) $changes['durationType'] = $durationType;
         if ($activityStartTime !== null && $activityDetails->activityStartTime != $activityStartTime) $changes['activityStartTime'] = $activityStartTime;
         if ($activityEndTime !== null) $changes['activityDurationEndTime'] = $activityEndTime;
         if ($duration !== null) $changes['duration'] = $duration;
         if ($activityOwnerID && $activityDetails->activityOwnerID != $activityOwnerID) $changes['activityOwnerID'] = $activityOwnerID;
         if ($activityParticipants !== null) $changes['activityParticipants'] = $activityParticipants;
         if ($activityLocation && $activityDetails->activityLocation != $activityLocation) $changes['activityLocation'] = $activityLocation;
         if ($meetingLink && $activityDetails->meetingLink != $meetingLink) $changes['meetingLink'] = $meetingLink;
         if ($activityNotes && $activityDetails->activityNotes != $activityNotes) $changes['activityNotes'] = $activityNotes;
         if ($activityOutcome && $activityDetails->activityOutcome != $activityOutcome) $changes['activityOutcome'] = $activityOutcome;
         if ($activityResult && $activityDetails->activityResult != $activityResult) $changes['activityResult'] = $activityResult;
         if ($requiresFollowUp) $changes['requiresFollowUp'] = $requiresFollowUp;
         if ($followUpNotes) $changes['followUpNotes'] = $followUpNotes;
         if ($allDayEvent) $changes['allDayEvent'] = $allDayEvent;

         if (count($changes) > 0) {
            $changes['LastUpdateByID'] = $userDetails->ID;
            $changes['LastUpdate'] = $config['currentDateTimeFormated'];

            if (!$DBConn->update_table('tija_activities', $changes, ['activityID' => $activityID])) {
               $errors[] = 'There was an error updating the activity. Please try again.';
            } else {
               $success = 'Activity updated successfully!';

               // Update reminder if needed
               if ($sendReminder === 'Y' && $reminderTime && $activityDate && $activityStartTime) {
                  createActivityReminder($activityID, $activityDate, $activityStartTime, $reminderTime, $activityOwnerID, $DBConn, $config);
               }

               // Update expenses (delete old, insert new)
               if (isset($_POST['expenses'])) {
                  // Delete existing expenses
                  $DBConn->query("DELETE FROM tija_activity_expenses WHERE activityID = ?");
                  $DBConn->bind(1, $activityID);
                  $DBConn->execute();

                  // Insert new expenses
                  if (!empty($expenses)) {
                     saveActivityExpenses($activityID, $expenses, $activityDate, $userDetails->ID, $DBConn);
                  }
               }
            }
         } else {
            // No changes to activity, but check for expense changes
            if (isset($_POST['expenses'])) {
               // Delete existing expenses
               $DBConn->query("DELETE FROM tija_activity_expenses WHERE activityID = ?");
               $DBConn->bind(1, $activityID);
               $DBConn->execute();

               // Insert new expenses
               if (!empty($expenses)) {
                  saveActivityExpenses($activityID, $expenses, $activityDate, $userDetails->ID, $DBConn);
               }
               $success = 'Activity expenses updated successfully!';
            } else {
               $success = 'No changes detected.';
            }
         }
      }
   }

   $returnURL = Utility::returnURL($_SESSION['returnURL'], 's=sales&ss=activities&p=sale_details&saleid=' . $salesCaseID);

} else {
   $errors[] = 'You need to log in as a valid user to perform this action.';
}

// ============================================================================
// FINALIZE TRANSACTION
// ============================================================================
if (count($errors) == 0) {
   $DBConn->commit();
   $messages = array(array('Text' => $success, 'Type' => 'success'));
} else {
   $DBConn->rollback();
   $messages = array_map(function($error) {
      return array('Text' => $error, 'Type' => 'danger');
   }, $errors);
}

$_SESSION['FlashMessages'] = serialize($messages);
header("location:{$base}html/{$returnURL}");
exit;

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================
function createActivityReminder($activityID, $activityDate, $activityTime, $reminderMinutes, $recipientID, $DBConn, $config) {
   try {
      // Calculate reminder datetime
      $activityDateTime = strtotime("$activityDate $activityTime");
      $reminderDateTime = date('Y-m-d H:i:s', strtotime("-$reminderMinutes minutes", $activityDateTime));

      $reminderData = array(
         'activityID' => $activityID,
         'reminderTime' => $reminderDateTime,
         'reminderType' => 'notification',
         'recipientID' => $recipientID,
         'reminderSent' => 'N',
         'reminderNote' => "Reminder: Activity starting in $reminderMinutes minutes"
      );

      // Check if reminder already exists
      $existingReminder = $DBConn->retrieve_db_table_rows('tija_activity_reminders', ['reminderID'], ['activityID' => $activityID, 'recipientID' => $recipientID]);

      if ($existingReminder && count($existingReminder) > 0) {
         // Update existing reminder
         $DBConn->update_table('tija_activity_reminders', ['reminderTime' => $reminderDateTime], ['activityID' => $activityID, 'recipientID' => $recipientID]);
      } else {
         // Create new reminder
         $DBConn->insert_data('tija_activity_reminders', $reminderData);
      }
   } catch (Exception $e) {
      // Log error but don't fail the whole transaction
      error_log("Failed to create activity reminder: " . $e->getMessage());
   }
}

function saveActivityExpenses($activityID, $expenses, $activityDate, $userID, $DBConn) {
   try {
      // Check if table exists before attempting to save
      $tableExists = false;
      try {
         $checkTable = $DBConn->retrieve_db_table_rows('tija_activity_expenses', ['expenseID'], ['1' => '0'], 1);
         $tableExists = true;
      } catch (Exception $e) {
         // Table doesn't exist yet
         error_log("tija_activity_expenses table does not exist. Please run the migration: add_activity_multi_expenses.sql");
         return false;
      }

      if (!$tableExists) {
         return false;
      }

      foreach ($expenses as $expense) {
         $expenseData = array(
            'activityID' => $activityID,
            'expenseDate' => $activityDate,
            'expenseCategory' => $expense['category'],
            'expenseAmount' => $expense['amount'],
            'expenseDescription' => $expense['description'],
            'expenseCurrency' => 'KES',
            'paymentMethod' => $expense['paymentMethod'],
            'receiptNumber' => $expense['receiptNumber'],
            'reimbursable' => $expense['reimbursable'],
            'reimbursementStatus' => 'pending',
            'addedBy' => $userID,
            'LastUpdatedByID' => $userID
         );

         if (!$DBConn->insert_data('tija_activity_expenses', $expenseData)) {
            error_log("Failed to save expense for activity $activityID: " . json_encode($expense));
         }
      }
      return true;
   } catch (Exception $e) {
      error_log("Failed to save activity expenses: " . $e->getMessage());
      return false;
   }
}

