<?php
/**
 * Leave Handover UI Helpers
 *
 * Utility functions for displaying handover status and FSM state information in the UI.
 */

if (!function_exists('get_fsm_state_badge')) {
    /**
     * Get FSM state badge HTML.
     *
     * @param string $stateCode FSM state code (e.g., 'ST_00')
     * @param string $stateName Optional state name override
     * @return string Badge HTML
     */
    function get_fsm_state_badge($stateCode, $stateName = null)
    {
        $stateNames = array(
            'ST_00' => 'Draft',
            'ST_01' => 'Composing Handover',
            'ST_02' => 'Awaiting Peer Response',
            'ST_03' => 'Revision Required',
            'ST_04' => 'Handover Accepted',
            'ST_05' => 'Manager Review',
            'ST_06' => 'Approved',
            'ST_07' => 'Rejected'
        );

        $stateClasses = array(
            'ST_00' => 'bg-secondary',
            'ST_01' => 'bg-info',
            'ST_02' => 'bg-warning',
            'ST_03' => 'bg-warning text-dark',
            'ST_04' => 'bg-success',
            'ST_05' => 'bg-primary',
            'ST_06' => 'bg-success',
            'ST_07' => 'bg-danger'
        );

        $displayName = $stateName ?: ($stateNames[$stateCode] ?? $stateCode);
        $badgeClass = $stateClasses[$stateCode] ?? 'bg-secondary';

        return '<span class="badge ' . htmlspecialchars($badgeClass) . ' text-uppercase">' . htmlspecialchars($displayName) . '</span>';
    }
}

if (!function_exists('get_handover_status_badge')) {
    /**
     * Get handover status badge HTML.
     *
     * @param string $status Handover status
     * @return string Badge HTML
     */
    function get_handover_status_badge($status)
    {
        $statusClasses = array(
            'not_required' => 'bg-secondary',
            'pending' => 'bg-warning',
            'in_progress' => 'bg-info',
            'completed' => 'bg-success',
            'partial' => 'bg-warning text-dark',
            'rejected' => 'bg-danger'
        );

        $badgeClass = $statusClasses[$status] ?? 'bg-secondary';
        return '<span class="badge ' . htmlspecialchars($badgeClass) . ' text-uppercase">' . htmlspecialchars($status) . '</span>';
    }
}

if (!function_exists('format_timer_remaining')) {
    /**
     * Format remaining time for display.
     *
     * @param float $remainingHours Remaining hours
     * @return string Formatted time string
     */
    function format_timer_remaining($remainingHours)
    {
        if ($remainingHours === null || $remainingHours < 0) {
            return 'Expired';
        }

        if ($remainingHours < 1) {
            $minutes = (int)($remainingHours * 60);
            return $minutes . ' minute' . ($minutes !== 1 ? 's' : '');
        } elseif ($remainingHours < 24) {
            $hours = (int)$remainingHours;
            $minutes = (int)(($remainingHours - $hours) * 60);
            if ($minutes > 0) {
                return $hours . 'h ' . $minutes . 'm';
            }
            return $hours . ' hour' . ($hours !== 1 ? 's' : '');
        } else {
            $days = (int)($remainingHours / 24);
            $hours = (int)($remainingHours % 24);
            if ($hours > 0) {
                return $days . 'd ' . $hours . 'h';
            }
            return $days . ' day' . ($days !== 1 ? 's' : '');
        }
    }
}
?>

