<?php
/**
 * Proposal Checklist Notification Helper
 *
 * This class provides helper methods to send notifications for proposal checklist events.
 * It integrates with the main Notification system to send in-app and email notifications.
 *
 * Usage:
 *   // When assigning a checklist item
 *   ProposalChecklistNotification::sendAssignmentNotification($assignmentData, $DBConn);
 *
 *   // When submitting a checklist item
 *   ProposalChecklistNotification::sendSubmissionNotification($submissionData, $DBConn);
 *
 * @version 1.0
 * @date 2025-12-12
 */

class ProposalChecklistNotification {

    /**
     * Send notification when a checklist item is assigned to a team member
     *
     * @param array $params Assignment parameters:
     *   - assigneeUserID (required): User ID of the person being assigned
     *   - assignorUserID (required): User ID of the person making the assignment
     *   - proposalID (required): Proposal ID
     *   - proposalTitle (required): Proposal title
     *   - checklistID (required): Checklist category ID
     *   - checklistName (required): Checklist category name
     *   - requirementName (required): The requirement/item description
     *   - dueDate (required): Due date for the assignment
     *   - instructions (optional): Special instructions for the assignee
     *   - actionLink (optional): Link to the assignment details page
     * @param object $DBConn Database connection
     * @return array Success status and notification details
     */
    public static function sendAssignmentNotification($params, $DBConn) {
        // Validate required parameters
        $required = array('assigneeUserID', 'assignorUserID', 'proposalID', 'proposalTitle',
                          'checklistName', 'requirementName', 'dueDate');
        foreach ($required as $field) {
            if (!isset($params[$field]) || empty($params[$field])) {
                return array('success' => false, 'message' => "Missing required parameter: {$field}");
            }
        }

        // Get assignor name
        $assignorName = self::getUserName($params['assignorUserID'], $DBConn);

        // Build notification data
        $notificationData = array(
            'eventSlug' => 'checklist_item_assigned',
            'userId' => $params['assigneeUserID'],
            'data' => array(
                'proposal_id' => $params['proposalID'],
                'proposal_title' => $params['proposalTitle'],
                'checklist_id' => $params['checklistID'] ?? '',
                'checklist_name' => $params['checklistName'],
                'requirement_name' => $params['requirementName'],
                'due_date' => self::formatDate($params['dueDate']),
                'assignor_name' => $assignorName,
                'assignee_name' => self::getUserName($params['assigneeUserID'], $DBConn),
                'instructions' => $params['instructions'] ?? 'No specific instructions provided.',
                'action_link' => $params['actionLink'] ?? '',
                'action_link_full' => $params['actionLink'] ?? ''
            ),
            'priority' => 'high'
        );

        return self::createNotification($notificationData, $DBConn);
    }

    /**
     * Send notification when a checklist item is submitted
     *
     * @param array $params Submission parameters:
     *   - ownerUserID (required): User ID of the checklist/proposal owner to notify
     *   - assigneeUserID (required): User ID of the person who submitted
     *   - proposalID (required): Proposal ID
     *   - proposalTitle (required): Proposal title
     *   - checklistName (required): Checklist category name
     *   - requirementName (required): The requirement/item description
     *   - submissionDate (optional): Date of submission (defaults to now)
     *   - attachmentCount (optional): Number of files attached
     *   - actionLink (optional): Link to review the submission
     * @param object $DBConn Database connection
     * @return array Success status and notification details
     */
    public static function sendSubmissionNotification($params, $DBConn) {
        // Validate required parameters
        $required = array('ownerUserID', 'assigneeUserID', 'proposalID', 'proposalTitle',
                          'checklistName', 'requirementName');
        foreach ($required as $field) {
            if (!isset($params[$field]) || empty($params[$field])) {
                return array('success' => false, 'message' => "Missing required parameter: {$field}");
            }
        }

        $assigneeName = self::getUserName($params['assigneeUserID'], $DBConn);

        $notificationData = array(
            'eventSlug' => 'checklist_item_submitted',
            'userId' => $params['ownerUserID'],
            'data' => array(
                'proposal_id' => $params['proposalID'],
                'proposal_title' => $params['proposalTitle'],
                'checklist_name' => $params['checklistName'],
                'requirement_name' => $params['requirementName'],
                'assignee_name' => $assigneeName,
                'submission_date' => $params['submissionDate'] ?? date('M d, Y H:i'),
                'attachment_count' => $params['attachmentCount'] ?? 0,
                'action_link' => $params['actionLink'] ?? '',
                'action_link_full' => $params['actionLink'] ?? ''
            ),
            'priority' => 'medium'
        );

        return self::createNotification($notificationData, $DBConn);
    }

    /**
     * Send notification when a checklist item submission is approved
     *
     * @param array $params Approval parameters
     * @param object $DBConn Database connection
     * @return array Success status
     */
    public static function sendApprovalNotification($params, $DBConn) {
        $required = array('assigneeUserID', 'reviewerUserID', 'proposalID', 'proposalTitle',
                          'checklistName', 'requirementName');
        foreach ($required as $field) {
            if (!isset($params[$field]) || empty($params[$field])) {
                return array('success' => false, 'message' => "Missing required parameter: {$field}");
            }
        }

        $reviewerName = self::getUserName($params['reviewerUserID'], $DBConn);

        $notificationData = array(
            'eventSlug' => 'checklist_item_approved',
            'userId' => $params['assigneeUserID'],
            'data' => array(
                'proposal_id' => $params['proposalID'],
                'proposal_title' => $params['proposalTitle'],
                'checklist_name' => $params['checklistName'],
                'requirement_name' => $params['requirementName'],
                'reviewer_name' => $reviewerName,
                'reviewer_comments' => $params['comments'] ?? 'Looks good!',
                'action_link' => $params['actionLink'] ?? '',
                'action_link_full' => $params['actionLink'] ?? ''
            ),
            'priority' => 'medium'
        );

        return self::createNotification($notificationData, $DBConn);
    }

    /**
     * Send notification when a checklist item submission requires revision
     *
     * @param array $params Revision parameters
     * @param object $DBConn Database connection
     * @return array Success status
     */
    public static function sendRevisionRequiredNotification($params, $DBConn) {
        $required = array('assigneeUserID', 'reviewerUserID', 'proposalID', 'proposalTitle',
                          'checklistName', 'requirementName', 'feedback');
        foreach ($required as $field) {
            if (!isset($params[$field]) || empty($params[$field])) {
                return array('success' => false, 'message' => "Missing required parameter: {$field}");
            }
        }

        $reviewerName = self::getUserName($params['reviewerUserID'], $DBConn);

        $notificationData = array(
            'eventSlug' => 'checklist_item_revision_required',
            'userId' => $params['assigneeUserID'],
            'data' => array(
                'proposal_id' => $params['proposalID'],
                'proposal_title' => $params['proposalTitle'],
                'checklist_name' => $params['checklistName'],
                'requirement_name' => $params['requirementName'],
                'reviewer_name' => $reviewerName,
                'feedback' => $params['feedback'],
                'due_date' => isset($params['dueDate']) ? self::formatDate($params['dueDate']) : '',
                'action_link' => $params['actionLink'] ?? '',
                'action_link_full' => $params['actionLink'] ?? ''
            ),
            'priority' => 'high'
        );

        return self::createNotification($notificationData, $DBConn);
    }

    /**
     * Send deadline reminder notification
     *
     * @param array $params Reminder parameters
     * @param object $DBConn Database connection
     * @return array Success status
     */
    public static function sendDeadlineReminderNotification($params, $DBConn) {
        $required = array('assigneeUserID', 'proposalID', 'proposalTitle',
                          'checklistName', 'requirementName', 'dueDate', 'daysRemaining');
        foreach ($required as $field) {
            if (!isset($params[$field])) {
                return array('success' => false, 'message' => "Missing required parameter: {$field}");
            }
        }

        $notificationData = array(
            'eventSlug' => 'checklist_item_deadline_reminder',
            'userId' => $params['assigneeUserID'],
            'data' => array(
                'proposal_id' => $params['proposalID'],
                'proposal_title' => $params['proposalTitle'],
                'checklist_name' => $params['checklistName'],
                'requirement_name' => $params['requirementName'],
                'due_date' => self::formatDate($params['dueDate']),
                'days_remaining' => $params['daysRemaining'],
                'assignee_name' => self::getUserName($params['assigneeUserID'], $DBConn),
                'action_link' => $params['actionLink'] ?? '',
                'action_link_full' => $params['actionLink'] ?? ''
            ),
            'priority' => 'high'
        );

        return self::createNotification($notificationData, $DBConn);
    }

    /**
     * Send overdue notification
     *
     * @param array $params Overdue parameters
     * @param object $DBConn Database connection
     * @return array Success status
     */
    public static function sendOverdueNotification($params, $DBConn) {
        $required = array('assigneeUserID', 'proposalID', 'proposalTitle',
                          'checklistName', 'requirementName', 'dueDate', 'daysOverdue');
        foreach ($required as $field) {
            if (!isset($params[$field])) {
                return array('success' => false, 'message' => "Missing required parameter: {$field}");
            }
        }

        $notificationData = array(
            'eventSlug' => 'checklist_item_overdue',
            'userId' => $params['assigneeUserID'],
            'data' => array(
                'proposal_id' => $params['proposalID'],
                'proposal_title' => $params['proposalTitle'],
                'checklist_name' => $params['checklistName'],
                'requirement_name' => $params['requirementName'],
                'due_date' => self::formatDate($params['dueDate']),
                'days_overdue' => $params['daysOverdue'],
                'assignee_name' => self::getUserName($params['assigneeUserID'], $DBConn),
                'action_link' => $params['actionLink'] ?? '',
                'action_link_full' => $params['actionLink'] ?? ''
            ),
            'priority' => 'critical'
        );

        // Also notify the checklist owner if provided
        $results = array();
        $results['assignee'] = self::createNotification($notificationData, $DBConn);

        if (isset($params['ownerUserID']) && $params['ownerUserID'] != $params['assigneeUserID']) {
            $ownerNotificationData = $notificationData;
            $ownerNotificationData['userId'] = $params['ownerUserID'];
            $results['owner'] = self::createNotification($ownerNotificationData, $DBConn);
        }

        return array('success' => true, 'results' => $results);
    }

    /**
     * Send notification when all checklist items are completed
     *
     * @param array $params Completion parameters
     * @param object $DBConn Database connection
     * @return array Success status
     */
    public static function sendCompletionNotification($params, $DBConn) {
        $required = array('ownerUserID', 'proposalID', 'proposalTitle', 'totalItems');
        foreach ($required as $field) {
            if (!isset($params[$field])) {
                return array('success' => false, 'message' => "Missing required parameter: {$field}");
            }
        }

        $notificationData = array(
            'eventSlug' => 'proposal_checklist_completed',
            'userId' => $params['ownerUserID'],
            'data' => array(
                'proposal_id' => $params['proposalID'],
                'proposal_title' => $params['proposalTitle'],
                'total_items' => $params['totalItems'],
                'completion_date' => date('M d, Y'),
                'action_link' => $params['actionLink'] ?? '',
                'action_link_full' => $params['actionLink'] ?? ''
            ),
            'priority' => 'medium'
        );

        return self::createNotification($notificationData, $DBConn);
    }

    /**
     * Internal method to create and dispatch a notification
     *
     * @param array $notificationData Notification parameters
     * @param object $DBConn Database connection
     * @return array Result of notification creation
     */
    private static function createNotification($notificationData, $DBConn) {
        try {
            // Check if Notification class exists
            if (!class_exists('Notification')) {
                error_log("ProposalChecklistNotification: Notification class not found");
                return array('success' => false, 'message' => 'Notification class not available');
            }

            // Create the notification using the main Notification system
            $result = Notification::create($notificationData, $DBConn);

            if ($result && isset($result['success']) && $result['success']) {
                // Optionally process queue immediately for important notifications
                if (isset($notificationData['priority']) &&
                    in_array($notificationData['priority'], array('high', 'critical'))) {
                    Notification::processQueueImmediately(5, $DBConn);
                }
            }

            return $result;
        } catch (Exception $e) {
            error_log("ProposalChecklistNotification error: " . $e->getMessage());
            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    /**
     * Get user's full name by ID
     *
     * @param int $userID User ID
     * @param object $DBConn Database connection
     * @return string User's full name or 'Unknown User'
     */
    private static function getUserName($userID, $DBConn) {
        if (empty($userID)) {
            return 'Unknown User';
        }

        $user = $DBConn->fetch_all_rows(
            "SELECT FirstName, OtherNames, Surname FROM people WHERE ID = ?",
            array(array($userID, 'i'))
        );

        if ($user && count($user) > 0) {
            $u = is_object($user[0]) ? $user[0] : (object)$user[0];
            $name = trim(($u->FirstName ?? '') . ' ' . ($u->OtherNames ?? '') . ' ' . ($u->Surname ?? ''));
            return $name ?: 'Unknown User';
        }

        return 'Unknown User';
    }

    /**
     * Format date for display
     *
     * @param string $date Date string
     * @return string Formatted date
     */
    private static function formatDate($date) {
        if (empty($date)) {
            return 'Not specified';
        }

        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return $date;
        }

        return date('M d, Y', $timestamp);
    }

    /**
     * Check for overdue items and send notifications
     * This method can be called from a cron job
     *
     * @param object $DBConn Database connection
     * @return array Results of notifications sent
     */
    public static function processOverdueNotifications($DBConn) {
        $results = array('checked' => 0, 'notifications_sent' => 0, 'errors' => array());

        // Get all overdue, non-completed checklist items
        $overdueItems = $DBConn->fetch_all_rows(
            "SELECT
                pcia.proposalChecklistItemAssignmentID,
                pcia.proposalChecklistItemAssignmentDescription as requirementName,
                pcia.proposalChecklistItemAssignmentDueDate as dueDate,
                pcia.checklistItemAssignedEmployeeID as assigneeUserID,
                pc.proposalChecklistName as checklistName,
                pc.assignedEmployeeID as ownerUserID,
                p.proposalID,
                p.proposalTitle,
                DATEDIFF(CURDATE(), pcia.proposalChecklistItemAssignmentDueDate) as daysOverdue
            FROM tija_proposal_checklist_item_assignment pcia
            JOIN tija_proposal_checklist pc ON pcia.proposalChecklistID = pc.proposalChecklistID
            JOIN tija_proposals p ON pcia.proposalID = p.proposalID
            WHERE pcia.proposalChecklistItemAssignmentDueDate < CURDATE()
            AND pcia.proposalChecklistItemAssignmentStatusID != 3
            AND (pcia.Suspended IS NULL OR pcia.Suspended = 'N')",
            array()
        );

        if ($overdueItems && count($overdueItems) > 0) {
            foreach ($overdueItems as $item) {
                $results['checked']++;
                $item = is_object($item) ? $item : (object)$item;

                $notifResult = self::sendOverdueNotification(array(
                    'assigneeUserID' => $item->assigneeUserID,
                    'ownerUserID' => $item->ownerUserID,
                    'proposalID' => $item->proposalID,
                    'proposalTitle' => $item->proposalTitle,
                    'checklistName' => $item->checklistName,
                    'requirementName' => $item->requirementName,
                    'dueDate' => $item->dueDate,
                    'daysOverdue' => $item->daysOverdue
                ), $DBConn);

                if ($notifResult && isset($notifResult['success']) && $notifResult['success']) {
                    $results['notifications_sent']++;
                } else {
                    $results['errors'][] = $notifResult['message'] ?? 'Unknown error';
                }
            }
        }

        return $results;
    }

    /**
     * Check for upcoming deadlines and send reminder notifications
     * This method can be called from a cron job
     *
     * @param int $daysAhead Number of days ahead to check (default: 3)
     * @param object $DBConn Database connection
     * @return array Results of notifications sent
     */
    public static function processDeadlineReminders($daysAhead = 3, $DBConn) {
        $results = array('checked' => 0, 'notifications_sent' => 0, 'errors' => array());

        // Get items with upcoming deadlines
        $upcomingItems = $DBConn->fetch_all_rows(
            "SELECT
                pcia.proposalChecklistItemAssignmentID,
                pcia.proposalChecklistItemAssignmentDescription as requirementName,
                pcia.proposalChecklistItemAssignmentDueDate as dueDate,
                pcia.checklistItemAssignedEmployeeID as assigneeUserID,
                pc.proposalChecklistName as checklistName,
                p.proposalID,
                p.proposalTitle,
                DATEDIFF(pcia.proposalChecklistItemAssignmentDueDate, CURDATE()) as daysRemaining
            FROM tija_proposal_checklist_item_assignment pcia
            JOIN tija_proposal_checklist pc ON pcia.proposalChecklistID = pc.proposalChecklistID
            JOIN tija_proposals p ON pcia.proposalID = p.proposalID
            WHERE pcia.proposalChecklistItemAssignmentDueDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
            AND pcia.proposalChecklistItemAssignmentStatusID != 3
            AND (pcia.Suspended IS NULL OR pcia.Suspended = 'N')",
            array(array($daysAhead, 'i'))
        );

        if ($upcomingItems && count($upcomingItems) > 0) {
            foreach ($upcomingItems as $item) {
                $results['checked']++;
                $item = is_object($item) ? $item : (object)$item;

                $notifResult = self::sendDeadlineReminderNotification(array(
                    'assigneeUserID' => $item->assigneeUserID,
                    'proposalID' => $item->proposalID,
                    'proposalTitle' => $item->proposalTitle,
                    'checklistName' => $item->checklistName,
                    'requirementName' => $item->requirementName,
                    'dueDate' => $item->dueDate,
                    'daysRemaining' => $item->daysRemaining
                ), $DBConn);

                if ($notifResult && isset($notifResult['success']) && $notifResult['success']) {
                    $results['notifications_sent']++;
                } else {
                    $results['errors'][] = $notifResult['message'] ?? 'Unknown error';
                }
            }
        }

        return $results;
    }
}
