<?php
/**
 * LeaveHandoverFSM
 *
 * Manages the Finite State Machine (FSM) workflow for leave handover protocol.
 * Handles state transitions, validation, and chain of custody logging.
 */
class LeaveHandoverFSM
{
    // State definitions
    const STATE_DRAFT = 'ST_00';
    const STATE_HANDOVER_COMPOSITION = 'ST_01';
    const STATE_PEER_NEGOTIATION = 'ST_02';
    const STATE_HANDOVER_REVISION = 'ST_03';
    const STATE_HANDOVER_ACCEPTED = 'ST_04';
    const STATE_MANAGER_REVIEW = 'ST_05';
    const STATE_APPROVED = 'ST_06';
    const STATE_REJECTED = 'ST_07';

    // Trigger definitions
    const TRIGGER_SUBMIT_DRAFT = 'Submit_Draft';
    const TRIGGER_SUBMIT_HANDOVER = 'Submit_Handover';
    const TRIGGER_PEER_REQUEST_CHANGE = 'Peer_Request_Change';
    const TRIGGER_PEER_ACCEPT = 'Peer_Accept';
    const TRIGGER_RESUBMIT_HANDOVER = 'Resubmit_Handover';
    const TRIGGER_SYSTEM_AUTO_ROUTE = 'System_Auto_Route';
    const TRIGGER_MANAGER_APPROVE = 'Manager_Approve';
    const TRIGGER_MANAGER_REJECT = 'Manager_Reject';

    /**
     * Valid state transitions map
     */
    private static $validTransitions = array(
        'ST_00' => array('Submit_Draft' => 'ST_01'),
        'ST_01' => array('Submit_Handover' => 'ST_02'),
        'ST_02' => array(
            'Peer_Request_Change' => 'ST_03',
            'Peer_Accept' => 'ST_04'
        ),
        'ST_03' => array('Resubmit_Handover' => 'ST_01'),
        'ST_04' => array('System_Auto_Route' => 'ST_05'),
        'ST_05' => array(
            'Manager_Approve' => 'ST_06',
            'Manager_Reject' => 'ST_07'
        )
    );

    /**
     * Initialize FSM for a leave application.
     *
     * @param int $leaveApplicationID Leave application ID
     * @param int $employeeID Employee ID (requester)
     * @param object $DBConn Database connection
     * @return int|false State ID on success, false on failure
     */
    public static function initialize_fsm($leaveApplicationID, $employeeID, $DBConn)
    {
        if (!$leaveApplicationID || !$employeeID || !$DBConn) {
            return false;
        }

        // Check if FSM state already exists
        $existing = self::get_current_state($leaveApplicationID, $DBConn);
        if ($existing) {
            return $existing->stateID;
        }

        $now = date('Y-m-d H:i:s');
        $data = array(
            'leaveApplicationID' => $leaveApplicationID,
            'handoverID' => null,
            'currentState' => self::STATE_DRAFT,
            'previousState' => null,
            'stateOwnerID' => $employeeID,
            'nomineeID' => null,
            'stateEnteredAt' => $now,
            'stateCompletedAt' => null,
            'timerStartedAt' => null,
            'timerExpiresAt' => null,
            'revisionCount' => 0,
            'chainOfCustodyLog' => json_encode(array(
                array(
                    'timestamp' => $now,
                    'from_state' => null,
                    'to_state' => self::STATE_DRAFT,
                    'trigger' => 'Creation',
                    'actor_id' => $employeeID,
                    'metadata' => array('action' => 'FSM initialized')
                )
            )),
            'DateAdded' => $now,
            'LastUpdate' => $now
        );

        if ($DBConn->insert_data('tija_leave_handover_fsm_states', $data)) {
            return $DBConn->lastInsertId();
        }

        return false;
    }

    /**
     * Transition to a new state.
     *
     * @param int $leaveApplicationID Leave application ID
     * @param string $trigger Trigger name
     * @param int $actorID Actor (user) ID
     * @param array $data Additional data for transition
     * @param object $DBConn Database connection
     * @return bool Success status
     */
    public static function transition_state($leaveApplicationID, $trigger, $actorID, $data, $DBConn)
    {
        if (!$leaveApplicationID || !$trigger || !$actorID || !$DBConn) {
            return false;
        }

        $currentState = self::get_current_state($leaveApplicationID, $DBConn);
        if (!$currentState) {
            // Initialize if doesn't exist
            $stateID = self::initialize_fsm($leaveApplicationID, $actorID, $DBConn);
            if (!$stateID) {
                return false;
            }
            $currentState = self::get_current_state($leaveApplicationID, $DBConn);
        }

        // Validate transition
        if (!self::can_transition($currentState->currentState, $trigger, $DBConn)) {
            return false;
        }

        $newState = self::$validTransitions[$currentState->currentState][$trigger];
        if (!$newState) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $previousState = $currentState->currentState;

        // Update chain of custody log
        $log = json_decode($currentState->chainOfCustodyLog, true);
        if (!is_array($log)) {
            $log = array();
        }

        $logEntry = array(
            'timestamp' => $now,
            'from_state' => $previousState,
            'to_state' => $newState,
            'trigger' => $trigger,
            'actor_id' => $actorID,
            'metadata' => $data
        );

        $log[] = $logEntry;

        // Determine state owner and nominee based on new state
        $stateOwnerID = $currentState->stateOwnerID;
        $nomineeID = $currentState->nomineeID;
        $timerStartedAt = $currentState->timerStartedAt;
        $timerExpiresAt = $currentState->timerExpiresAt;
        $revisionCount = (int)$currentState->revisionCount;

        if ($newState === self::STATE_PEER_NEGOTIATION) {
            // Set nominee if provided
            if (isset($data['nomineeID'])) {
                $nomineeID = (int)$data['nomineeID'];
            }
            // Start timer if policy requires it
            if (isset($data['deadlineHours'])) {
                $timerStartedAt = $now;
                $timerExpiresAt = date('Y-m-d H:i:s', strtotime("+{$data['deadlineHours']} hours"));
            }
        } elseif ($newState === self::STATE_HANDOVER_REVISION) {
            $revisionCount++;
        } elseif ($newState === self::STATE_HANDOVER_ACCEPTED) {
            // Clear timer on acceptance
            $timerStartedAt = null;
            $timerExpiresAt = null;
        }

        $updateData = array(
            'previousState' => $previousState,
            'currentState' => $newState,
            'stateOwnerID' => $stateOwnerID,
            'nomineeID' => $nomineeID,
            'stateEnteredAt' => $now,
            'stateCompletedAt' => in_array($newState, array(self::STATE_APPROVED, self::STATE_REJECTED)) ? $now : null,
            'timerStartedAt' => $timerStartedAt,
            'timerExpiresAt' => $timerExpiresAt,
            'revisionCount' => $revisionCount,
            'chainOfCustodyLog' => json_encode($log),
            'LastUpdate' => $now
        );

        $result = $DBConn->update_table('tija_leave_handover_fsm_states', $updateData, array(
            'stateID' => $currentState->stateID
        ));

        // Update handover record if exists
        if ($result && isset($data['handoverID'])) {
            $DBConn->update_table('tija_leave_handovers', array(
                'fsmStateID' => $currentState->stateID,
                'nomineeID' => $nomineeID,
                'revisionCount' => $revisionCount,
                'LastUpdate' => $now
            ), array('handoverID' => $data['handoverID']));
        }

        return $result;
    }

    /**
     * Get current FSM state for a leave application.
     *
     * @param int $leaveApplicationID Leave application ID
     * @param object $DBConn Database connection
     * @return object|null State object or null
     */
    public static function get_current_state($leaveApplicationID, $DBConn)
    {
        if (!$leaveApplicationID || !$DBConn) {
            return null;
        }

        $rows = $DBConn->fetch_all_rows(
            "SELECT * FROM tija_leave_handover_fsm_states WHERE leaveApplicationID = ? ORDER BY stateID DESC LIMIT 1",
            array(array($leaveApplicationID, 'i'))
        );

        if (!$rows || count($rows) === 0) {
            return null;
        }

        return is_object($rows[0]) ? $rows[0] : (object)$rows[0];
    }

    /**
     * Check if a transition is valid.
     *
     * @param string $currentState Current state code
     * @param string $trigger Trigger name
     * @param object $DBConn Database connection (for future validation rules)
     * @return bool True if transition is valid
     */
    public static function can_transition($currentState, $trigger, $DBConn)
    {
        if (!isset(self::$validTransitions[$currentState])) {
            return false;
        }

        return isset(self::$validTransitions[$currentState][$trigger]);
    }

    /**
     * Log state transition to chain of custody.
     *
     * @param int $leaveApplicationID Leave application ID
     * @param string $fromState From state
     * @param string $toState To state
     * @param string $trigger Trigger name
     * @param int $actorID Actor ID
     * @param object $DBConn Database connection
     * @return bool Success status
     */
    public static function log_state_transition($leaveApplicationID, $fromState, $toState, $trigger, $actorID, $DBConn)
    {
        $currentState = self::get_current_state($leaveApplicationID, $DBConn);
        if (!$currentState) {
            return false;
        }

        $log = json_decode($currentState->chainOfCustodyLog, true);
        if (!is_array($log)) {
            $log = array();
        }

        $logEntry = array(
            'timestamp' => date('Y-m-d H:i:s'),
            'from_state' => $fromState,
            'to_state' => $toState,
            'trigger' => $trigger,
            'actor_id' => $actorID,
            'metadata' => array()
        );

        $log[] = $logEntry;

        return $DBConn->update_table('tija_leave_handover_fsm_states', array(
            'chainOfCustodyLog' => json_encode($log),
            'LastUpdate' => date('Y-m-d H:i:s')
        ), array('stateID' => $currentState->stateID));
    }

    /**
     * Check for expired peer response timers.
     *
     * @param int $leaveApplicationID Leave application ID
     * @param object $DBConn Database connection
     * @return array Timer status information
     */
    public static function check_timer_expiry($leaveApplicationID, $DBConn)
    {
        $currentState = self::get_current_state($leaveApplicationID, $DBConn);
        if (!$currentState || !$currentState->timerExpiresAt) {
            return array(
                'expired' => false,
                'remaining_hours' => null,
                'expires_at' => null
            );
        }

        $now = new DateTime();
        $expiresAt = new DateTime($currentState->timerExpiresAt);
        $expired = $now > $expiresAt;

        $diff = $now->diff($expiresAt);
        $remainingHours = $expired ? 0 : ($diff->days * 24 + $diff->h + ($diff->i / 60));

        return array(
            'expired' => $expired,
            'remaining_hours' => $remainingHours,
            'expires_at' => $currentState->timerExpiresAt
        );
    }

    /**
     * Get all state transitions for audit trail.
     *
     * @param int $leaveApplicationID Leave application ID
     * @param object $DBConn Database connection
     * @return array Array of transition log entries
     */
    public static function get_chain_of_custody($leaveApplicationID, $DBConn)
    {
        $currentState = self::get_current_state($leaveApplicationID, $DBConn);
        if (!$currentState || !$currentState->chainOfCustodyLog) {
            return array();
        }

        $log = json_decode($currentState->chainOfCustodyLog, true);
        return is_array($log) ? $log : array();
    }
}
?>

