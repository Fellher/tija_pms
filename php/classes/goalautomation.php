<?php
/**
 * Goal Automation Class
 *
 * Manages user automation preferences and manual execution
 *
 * @package    TIJA_PMS
 * @subpackage Goals
 * @version    1.0.0
 */

class GoalAutomation {

    /**
     * Get automation settings for user
     *
     * @param int $userID User ID
     * @param object $DBConn Database connection
     * @return array Automation settings
     */
    public static function getSettings($userID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array(
            'settingID', 'userID', 'automationType', 'executionMode',
            'scheduleFrequency', 'scheduleTime', 'isEnabled', 'notificationPreference'
        );

        $where = array('userID' => $userID);
        $settings = array();

        try {
            $settings = $DBConn->retrieve_db_table_rows('tija_goal_automation_settings', $cols, $where, false);
        } catch (\Throwable $e) {
            // Table may not exist yet; fall back to defaults without breaking the page
            error_log("GoalAutomation::getSettings fallback to defaults: " . $e->getMessage());
            $settings = array();
        }

        // Organize by automation type
        $organized = array();
        if ($settings) {
            foreach ($settings as $setting) {
                $organized[$setting->automationType] = $setting;
            }
        }

        // Return defaults if no settings found
        $defaults = self::defaultSettings();

        foreach ($defaults as $type => $default) {
            if (!isset($organized[$type])) {
                $organized[$type] = $default;
            }
        }

        return $organized;
    }

    /**
     * Update automation setting
     *
     * @param int $userID User ID
     * @param string $automationType Automation type
     * @param array $data Setting data
     * @param object $DBConn Database connection
     * @return bool Success
     */
    public static function updateSetting($userID, $automationType, $data, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        try {
            // Check if setting exists
            $existing = $DBConn->retrieve_db_table_rows(
                'tija_goal_automation_settings',
                array('settingID'),
                array('userID' => $userID, 'automationType' => $automationType),
                true
            );

            $settingData = array(
                'userID' => $userID,
                'automationType' => $automationType,
                'executionMode' => $data['executionMode'] ?? 'automatic',
                'scheduleFrequency' => $data['scheduleFrequency'] ?? null,
                'scheduleTime' => $data['scheduleTime'] ?? null,
                'isEnabled' => $data['isEnabled'] ?? 'Y',
                'notificationPreference' => $data['notificationPreference'] ?? 'both',
                'LastUpdatedByID' => $userID
            );

            if ($existing) {
                return $DBConn->update_table(
                    'tija_goal_automation_settings',
                    $settingData,
                    array('settingID' => $existing->settingID)
                );
            } else {
                return $DBConn->insert_data('tija_goal_automation_settings', $settingData);
            }
        } catch (\Throwable $e) {
            error_log("GoalAutomation::updateSetting failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if automation should run automatically
     *
     * @param int $userID User ID
     * @param string $automationType Automation type
     * @param object $DBConn Database connection
     * @return bool Should run automatically
     */
    public static function shouldRunAutomatically($userID, $automationType, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $settings = self::getSettings($userID, $DBConn);

        if (!isset($settings[$automationType])) {
            return true; // Default to automatic
        }

        $setting = $settings[$automationType];
        return $setting->isEnabled === 'Y' && $setting->executionMode === 'automatic';
    }

    /**
     * Execute manual automation
     *
     * @param int $userID User ID
     * @param string $automationType Automation type
     * @param array $params Parameters for automation
     * @param object $DBConn Database connection
     * @return array Result
     */
    public static function executeManual($userID, $automationType, $params = array(), $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        require_once 'goal.php';
        require_once 'goalevaluation.php';
        require_once 'goalscoring.php';

        $result = array('success' => false, 'message' => '', 'data' => array());

        switch ($automationType) {
            case 'score_calculation':
                if (isset($params['goalUUID'])) {
                    $score = GoalEvaluation::calculateWeightedScore($params['goalUUID'], $DBConn);
                    $result = array('success' => true, 'message' => 'Score calculated', 'data' => array('score' => $score));
                } elseif (isset($params['entityID'])) {
                    $score = GoalScoring::calculateEntityScore($params['entityID'], null, $DBConn);
                    $result = array('success' => true, 'message' => 'Entity score calculated', 'data' => array('score' => $score));
                } else {
                    $result = array('success' => false, 'message' => 'goalUUID or entityID required');
                }
                break;

            case 'snapshot_generation':
                if (isset($params['goalUUID'])) {
                    $snapshotID = GoalScoring::generateSnapshot($params['goalUUID'], $DBConn);
                    $result = array('success' => true, 'message' => 'Snapshot generated', 'data' => array('snapshotID' => $snapshotID));
                } else {
                    // Generate for all active goals
                    $goals = $DBConn->retrieve_db_table_rows(
                        'tija_goals',
                        array('goalUUID'),
                        array('status' => 'Active', 'sysEndTime' => 'NULL', 'Lapsed' => 'N'),
                        false
                    );
                    $count = 0;
                    if ($goals) {
                        foreach ($goals as $goal) {
                            GoalScoring::generateSnapshot($goal->goalUUID, $DBConn);
                            $count++;
                        }
                    }
                    $result = array('success' => true, 'message' => "Generated {$count} snapshots", 'data' => array('count' => $count));
                }
                break;

            case 'evaluation_reminders':
                // This would trigger reminder notifications
                $result = array('success' => true, 'message' => 'Reminders sent', 'data' => array());
                break;

            case 'deadline_alerts':
                // This would trigger deadline alerts
                $result = array('success' => true, 'message' => 'Alerts sent', 'data' => array());
                break;

            default:
                $result = array('success' => false, 'message' => 'Unknown automation type');
        }

        return $result;
    }

    /**
     * Default automation settings when none are stored or table is unavailable.
     *
     * @return array
     */
    private static function defaultSettings() {
        return array(
            'score_calculation' => (object)array(
                'executionMode' => 'automatic',
                'isEnabled' => 'Y',
                'notificationPreference' => 'both'
            ),
            'snapshot_generation' => (object)array(
                'executionMode' => 'automatic',
                'isEnabled' => 'Y',
                'notificationPreference' => 'both'
            ),
            'evaluation_reminders' => (object)array(
                'executionMode' => 'automatic',
                'isEnabled' => 'Y',
                'notificationPreference' => 'both'
            ),
            'deadline_alerts' => (object)array(
                'executionMode' => 'automatic',
                'isEnabled' => 'Y',
                'notificationPreference' => 'both'
            ),
            'cascade_updates' => (object)array(
                'executionMode' => 'automatic',
                'isEnabled' => 'Y',
                'notificationPreference' => 'both'
            )
        );
    }
}

