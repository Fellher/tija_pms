<?php
/**
 * Get Activity Details
 * Fetches activity data for editing in the activity wizard
 *
 * @package    Tija CRM
 * @subpackage Sales Management
 * @version    1.0.0
 */

// Set JSON header
header('Content-Type: application/json');

// Include necessary files
$base = "../../../";
require_once($base . "php/core/core.php");

// Initialize response
$response = array(
    'success' => false,
    'message' => '',
    'activity' => null
);

try {
    // Check if user is authenticated
    if (!$isValidUser) {
        $response['message'] = 'Unauthorized access';
        echo json_encode($response);
        exit;
    }

    // Get activity ID from request
    $activityID = isset($_GET['activityID']) ? Utility::clean_string($_GET['activityID']) : '';

    if (empty($activityID)) {
        $response['message'] = 'Activity ID is required';
        echo json_encode($response);
        exit;
    }

    // Fetch activity details
    $activity = Schedule::tija_activities(array('activityID' => $activityID), true, $DBConn);

    if (!$activity) {
        $response['message'] = 'Activity not found';
        echo json_encode($response);
        exit;
    }

    // Fetch activity expenses (check if table exists first)
    $expenses = array();
    try {
        $expenseRecords = $DBConn->retrieve_db_table_rows(
            'tija_activity_expenses',
            ['expenseID', 'expenseCategory', 'expenseAmount', 'expenseDescription', 'paymentMethod', 'receiptNumber', 'reimbursable', 'reimbursementStatus'],
            ['activityID' => $activityID, 'Suspended' => 'N']
        );

        if ($expenseRecords && is_array($expenseRecords)) {
            foreach ($expenseRecords as $expense) {
                $expenses[] = array(
                    'expenseID' => $expense->expenseID,
                    'category' => $expense->expenseCategory,
                    'amount' => $expense->expenseAmount,
                    'description' => $expense->expenseDescription ?? '',
                    'paymentMethod' => $expense->paymentMethod ?? '',
                    'receiptNumber' => $expense->receiptNumber ?? '',
                    'reimbursable' => $expense->reimbursable ?? 'Y',
                    'reimbursementStatus' => $expense->reimbursementStatus ?? 'pending'
                );
            }
        }
    } catch (Exception $e) {
        // Table doesn't exist yet - skip expenses
        error_log("Could not fetch activity expenses: " . $e->getMessage());
    }

    // Prepare activity data for response
    $activityData = array(
        'activityID' => $activity->activityID,
        'activityName' => $activity->activityName ?? '',
        'activityDescription' => $activity->activityDescription ?? '',
        'activityCategoryID' => $activity->activityCategoryID ?? '',
        'activityTypeID' => $activity->activityTypeID ?? '',
        'activityDate' => $activity->activityDate ?? '',
        'activityStartTime' => $activity->activityStartTime ?? '',
        'activityDurationEndTime' => $activity->activityDurationEndTime ?? '',
        'activityDurationEndDate' => $activity->activityDurationEndDate ?? '',
        'activityPriority' => $activity->activityPriority ?? 'Medium',
        'activityStatus' => $activity->activityStatus ?? 'notStarted',
        'activityOwnerID' => $activity->activityOwnerID ?? '',
        'activityLocation' => $activity->activityLocation ?? '',
        'durationType' => $activity->durationType ?? 'oneOff',
        'clientID' => $activity->clientID ?? '',
        'salesCaseID' => $activity->salesCaseID ?? '',
        'activityParticipants' => $activity->activityParticipants ?? '',
        'activityNotes' => $activity->activityNotes ?? '',
        'meetingLink' => $activity->meetingLink ?? '',
        'activityOutcome' => $activity->activityOutcome ?? '',
        'activityResult' => $activity->activityResult ?? '',
        'expenses' => $expenses, // Multi-expense array
        'followUpNotes' => $activity->followUpNotes ?? '',

        // Recurrence fields
        'recurring' => $activity->recurring ?? 'N',
        'recurrenceType' => $activity->recurrenceType ?? '',
        'recurringInterval' => $activity->recurringInterval ?? 1,
        'recurringIntervalUnit' => $activity->recurringIntervalUnit ?? 'day',
        'weekRecurringDays' => $activity->weekRecurringDays ?? '',
        'monthRepeatOnDays' => $activity->monthRepeatOnDays ?? '',
        'monthlyRepeatingDay' => $activity->monthlyRepeatingDay ?? '',
        'recurrenceEndType' => $activity->recurrenceEndType ?? 'never',
        'numberOfOccurrencesToEnd' => $activity->numberOfOccurrencesToEnd ?? '',
        'recurringEndDate' => $activity->recurringEndDate ?? '',

        // Additional metadata
        'activityOwnerName' => $activity->activityOwnerName ?? '',
        'activityTypeName' => $activity->activityTypeName ?? '',
        'activityCategoryName' => $activity->activityCategoryName ?? '',
        'clientName' => $activity->clientName ?? '',
        'salesCaseName' => $activity->salesCaseName ?? ''
    );

    $response['success'] = true;
    $response['message'] = 'Activity loaded successfully';
    $response['activity'] = $activityData;

} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Output JSON response
echo json_encode($response);
exit;

