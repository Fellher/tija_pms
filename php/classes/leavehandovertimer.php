<?php
/**
 * LeaveHandoverTimer
 *
 * Manages timers and deadlines for peer response in the handover protocol.
 */
class LeaveHandoverTimer
{
    /**
     * Start a peer response timer.
     *
     * @param int $handoverID Handover ID
     * @param int $nomineeID Nominee ID
     * @param int $deadlineHours Hours until deadline
     * @param object $DBConn Database connection
     * @return bool Success status
     */
    public static function start_peer_response_timer($handoverID, $nomineeID, $deadlineHours, $DBConn)
    {
        if (!$handoverID || !$nomineeID || !$deadlineHours || !$DBConn) {
            return false;
        }

        // Get handover to find leave application
        $handover = $DBConn->fetch_all_rows(
            "SELECT * FROM tija_leave_handovers WHERE handoverID = ? LIMIT 1",
            array(array($handoverID, 'i'))
        );

        if (!$handover || count($handover) === 0) {
            return false;
        }

        $handover = is_object($handover[0]) ? $handover[0] : (object)$handover[0];
        $leaveApplicationID = $handover->leaveApplicationID;

        // Update FSM state with timer
        $now = date('Y-m-d H:i:s');
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$deadlineHours} hours"));

        $fsmState = LeaveHandoverFSM::get_current_state($leaveApplicationID, $DBConn);
        if ($fsmState) {
            return $DBConn->update_table('tija_leave_handover_fsm_states', array(
                'timerStartedAt' => $now,
                'timerExpiresAt' => $expiresAt,
                'nomineeID' => $nomineeID,
                'LastUpdate' => $now
            ), array('stateID' => $fsmState->stateID));
        }

        return false;
    }

    /**
     * Check for expired timers across all active handovers.
     *
     * @param object $DBConn Database connection
     * @return array Array of expired timer information
     */
    public static function check_expired_timers($DBConn)
    {
        if (!$DBConn) {
            return array();
        }

        $now = date('Y-m-d H:i:s');
        $expiredStates = $DBConn->fetch_all_rows(
            "SELECT s.*, la.employeeID, la.entityID, la.orgDataID,
                    CONCAT(emp.FirstName, ' ', emp.Surname) as employeeName
             FROM tija_leave_handover_fsm_states s
             LEFT JOIN tija_leave_applications la ON s.leaveApplicationID = la.leaveApplicationID
             LEFT JOIN people emp ON la.employeeID = emp.ID
             WHERE s.currentState = 'ST_02'
             AND s.timerExpiresAt IS NOT NULL
             AND s.timerExpiresAt < ?
             AND s.stateCompletedAt IS NULL",
            array(array($now, 's'))
        );

        $expired = array();
        if ($expiredStates) {
            foreach ($expiredStates as $state) {
                $state = is_object($state) ? $state : (object)$state;
                $expired[] = array(
                    'stateID' => $state->stateID,
                    'leaveApplicationID' => $state->leaveApplicationID,
                    'handoverID' => $state->handoverID,
                    'nomineeID' => $state->nomineeID,
                    'employeeID' => $state->employeeID,
                    'employeeName' => $state->employeeName ?? 'Employee',
                    'entityID' => $state->entityID,
                    'orgDataID' => $state->orgDataID,
                    'expiredAt' => $state->timerExpiresAt
                );
            }
        }

        return $expired;
    }

    /**
     * Get remaining time for a handover response.
     *
     * @param int $handoverID Handover ID
     * @param object $DBConn Database connection
     * @return array Timer information
     */
    public static function get_remaining_time($handoverID, $DBConn)
    {
        if (!$handoverID || !$DBConn) {
            return array(
                'expired' => false,
                'remaining_hours' => null,
                'expires_at' => null
            );
        }

        // Get handover to find leave application
        $handover = $DBConn->fetch_all_rows(
            "SELECT * FROM tija_leave_handovers WHERE handoverID = ? LIMIT 1",
            array(array($handoverID, 'i'))
        );

        if (!$handover || count($handover) === 0) {
            return array(
                'expired' => false,
                'remaining_hours' => null,
                'expires_at' => null
            );
        }

        $handover = is_object($handover[0]) ? $handover[0] : (object)$handover[0];
        $leaveApplicationID = $handover->leaveApplicationID;

        return LeaveHandoverFSM::check_timer_expiry($leaveApplicationID, $DBConn);
    }

    /**
     * Handle timer expiry - send notifications and escalate if needed.
     *
     * @param int $handoverID Handover ID
     * @param object $DBConn Database connection
     * @return bool Success status
     */
    public static function handle_timer_expiry($handoverID, $DBConn)
    {
        if (!$handoverID || !$DBConn) {
            return false;
        }

        // Get handover details
        $handover = $DBConn->fetch_all_rows(
            "SELECT h.*, la.employeeID, la.entityID, la.orgDataID, la.startDate, la.endDate,
                    CONCAT(emp.FirstName, ' ', emp.Surname) as employeeName,
                    CONCAT(nom.FirstName, ' ', nom.Surname) as nomineeName,
                    lt.leaveTypeName
             FROM tija_leave_handovers h
             LEFT JOIN tija_leave_applications la ON h.leaveApplicationID = la.leaveApplicationID
             LEFT JOIN people emp ON la.employeeID = emp.ID
             LEFT JOIN people nom ON h.nomineeID = nom.ID
             LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
             WHERE h.handoverID = ?
             LIMIT 1",
            array(array($handoverID, 'i'))
        );

        if (!$handover || count($handover) === 0) {
            return false;
        }

        $handover = is_object($handover[0]) ? $handover[0] : (object)$handover[0];
        $fsmState = LeaveHandoverFSM::get_current_state($handover->leaveApplicationID, $DBConn);

        if (!$fsmState || $fsmState->currentState !== LeaveHandoverFSM::STATE_PEER_NEGOTIATION) {
            return false;
        }

        // Send escalation notifications
        if (class_exists('Notification')) {
            // Notify requester
            Notification::create(array(
                'eventSlug' => 'leave_handover_timer_expired',
                'userId' => $handover->employeeID,
                'originatorId' => $handover->nomineeID ?? $handover->employeeID,
                'data' => array(
                    'employee_name' => $handover->employeeName ?? 'Employee',
                    'leave_type' => $handover->leaveTypeName ?? 'Leave',
                    'start_date' => isset($handover->startDate) ? date('M j, Y', strtotime($handover->startDate)) : '',
                    'end_date' => isset($handover->endDate) ? date('M j, Y', strtotime($handover->endDate)) : '',
                    'handover_id' => $handoverID,
                    'application_id' => $handover->leaveApplicationID,
                    'nominee_name' => $handover->nomineeName ?? 'Nominated Employee'
                ),
                'link' => '?s=user&ss=leave&p=view_leave_application&id=' . $handover->leaveApplicationID,
                'entityID' => $handover->entityID,
                'orgDataID' => $handover->orgDataID,
                'segmentType' => 'leave_application',
                'segmentID' => $handover->leaveApplicationID,
                'priority' => 'high'
            ), $DBConn);

            // Notify nominee
            if ($handover->nomineeID) {
                Notification::create(array(
                    'eventSlug' => 'leave_handover_timer_expired',
                    'userId' => $handover->nomineeID,
                    'originatorId' => $handover->employeeID,
                    'data' => array(
                        'employee_name' => $handover->employeeName ?? 'Employee',
                        'leave_type' => $handover->leaveTypeName ?? 'Leave',
                        'start_date' => isset($handover->startDate) ? date('M j, Y', strtotime($handover->startDate)) : '',
                        'end_date' => isset($handover->endDate) ? date('M j, Y', strtotime($handover->endDate)) : '',
                        'handover_id' => $handoverID,
                        'application_id' => $handover->leaveApplicationID,
                        'nominee_name' => $handover->nomineeName ?? 'Nominated Employee'
                    ),
                    'link' => '?s=user&ss=leave&p=peer_handover_response&handoverID=' . $handoverID,
                    'entityID' => $handover->entityID,
                    'orgDataID' => $handover->orgDataID,
                    'segmentType' => 'leave_application',
                    'segmentID' => $handover->leaveApplicationID,
                    'priority' => 'high'
                ), $DBConn);
            }
        }

        return true;
    }
}
?>

