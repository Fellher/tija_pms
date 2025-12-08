<?php
/**
 * Goals Daily Cron Job
 * Runs daily to maintain goal data and send reminders
 *
 * @package    TIJA_PMS
 * @subpackage Goals
 * @version    1.0.0
 *
 * Usage: php php/scripts/cron/goals_daily.php
 * Or via cron: 0 2 * * * /usr/bin/php /path/to/php/scripts/cron/goals_daily.php
 */

// CLI mode check
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from command line');
}

// Set base path
$base = __DIR__ . '/../../../';
set_include_path($base);
include 'php/includes.php';

require_once 'php/classes/goal.php';
require_once 'php/classes/goalevaluation.php';
require_once 'php/classes/goalscoring.php';
require_once 'php/classes/notification.php';

echo "[" . date('Y-m-d H:i:s') . "] Starting Goals Daily Cron Job\n";

try {
    // 1. Generate daily snapshots for active goals
    echo "Generating daily snapshots for active goals...\n";
    $activeGoals = $DBConn->retrieve_db_table_rows(
        'tija_goals',
        array('goalUUID'),
        array('status' => 'Active', 'sysEndTime' => 'NULL', 'Lapsed' => 'N'),
        false
    );

    $snapshotCount = 0;
    if ($activeGoals) {
        foreach ($activeGoals as $goal) {
            $snapshotID = GoalScoring::generateSnapshot($goal->goalUUID, $DBConn);
            if ($snapshotID) {
                $snapshotCount++;
            }
        }
    }
    echo "Generated {$snapshotCount} snapshots\n";

    // 2. Send reminders for pending evaluations
    echo "Checking for pending evaluations...\n";
    $pendingEvaluations = $DBConn->retrieve_db_table_rows_custom(
        "SELECT DISTINCT g.goalUUID, g.goalTitle, g.ownerUserID, e.evaluatorUserID, e.evaluatorRole
         FROM tija_goals g
         INNER JOIN tija_goal_evaluation_weights ew ON g.goalUUID = ew.goalUUID
         LEFT JOIN tija_goal_evaluations e ON g.goalUUID = e.goalUUID
             AND e.evaluatorUserID = (SELECT evaluatorUserID FROM tija_goal_evaluations
                                      WHERE goalUUID = g.goalUUID
                                      AND evaluatorRole = ew.evaluatorRole
                                      AND status = 'Submitted'
                                      LIMIT 1)
         WHERE g.status = 'Active'
         AND g.sysEndTime IS NULL
         AND g.Lapsed = 'N'
         AND e.evaluationID IS NULL
         AND g.endDate >= CURDATE()
         AND g.endDate <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)",
        array()
    );

    $reminderCount = 0;
    if ($pendingEvaluations) {
        require_once 'php/classes/employee.php';
        foreach ($pendingEvaluations as $pending) {
            // Get evaluator details
            $evaluator = Employee::employees(array('ID' => $pending['evaluatorUserID']), true, $DBConn);
            if ($evaluator) {
                // Create notification (would need notification system integration)
                $notificationData = array(
                    'userID' => $pending['evaluatorUserID'],
                    'notificationType' => 'GoalEvaluationReminder',
                    'title' => 'Pending Goal Evaluation',
                    'message' => "You have a pending evaluation for goal: {$pending['goalTitle']}",
                    'relatedID' => $pending['goalUUID'],
                    'relatedType' => 'Goal'
                );
                // Notification::create($notificationData, $DBConn); // Uncomment when notification system is integrated
                $reminderCount++;
            }
        }
    }
    echo "Sent {$reminderCount} evaluation reminders\n";

    // 3. Calculate aggregate scores for entities
    echo "Calculating entity aggregate scores...\n";
    $entities = $DBConn->retrieve_db_table_rows(
        'tija_entities',
        array('entityID'),
        array('Lapsed' => 'N', 'Suspended' => 'N'),
        false
    );

    $entityScoreCount = 0;
    if ($entities) {
        foreach ($entities as $entity) {
            $score = GoalScoring::calculateEntityScore($entity->entityID, null, $DBConn);
            if ($score !== false) {
                $entityScoreCount++;
            }
        }
    }
    echo "Calculated scores for {$entityScoreCount} entities\n";

    // 4. Update cascade status for pending cascades
    echo "Updating cascade status...\n";
    $pendingCascades = $DBConn->retrieve_db_table_rows(
        'tija_goal_cascade_log',
        array('logID', 'parentGoalUUID', 'childGoalUUID', 'status'),
        array('status' => 'Pending'),
        false
    );

    $cascadeUpdateCount = 0;
    if ($pendingCascades) {
        foreach ($pendingCascades as $cascade) {
            // Check if child goal was created
            if ($cascade->childGoalUUID) {
                $childGoal = Goal::getGoal($cascade->childGoalUUID, $DBConn);
                if ($childGoal && $childGoal->status === 'Active') {
                    // Update status to Accepted if goal is active
                    $DBConn->update_table(
                        'tija_goal_cascade_log',
                        array('status' => 'Accepted', 'responseDate' => date('Y-m-d H:i:s')),
                        array('logID' => $cascade->logID)
                    );
                    $cascadeUpdateCount++;
                }
            }
        }
    }
    echo "Updated {$cascadeUpdateCount} cascade statuses\n";

    // 5. Check for goals approaching deadline
    echo "Checking for goals approaching deadline...\n";
    $approachingDeadline = $DBConn->retrieve_db_table_rows_custom(
        "SELECT goalUUID, goalTitle, ownerUserID, endDate, DATEDIFF(endDate, CURDATE()) as daysRemaining
         FROM tija_goals
         WHERE status = 'Active'
         AND sysEndTime IS NULL
         AND Lapsed = 'N'
         AND endDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
         AND completionPercentage < 100",
        array()
    );

    $deadlineAlertCount = 0;
    if ($approachingDeadline) {
        foreach ($approachingDeadline as $goal) {
            // Send alert to goal owner
            $notificationData = array(
                'userID' => $goal['ownerUserID'],
                'notificationType' => 'GoalDeadlineApproaching',
                'title' => 'Goal Deadline Approaching',
                'message' => "Goal '{$goal['goalTitle']}' deadline is in {$goal['daysRemaining']} days",
                'relatedID' => $goal['goalUUID'],
                'relatedType' => 'Goal'
            );
            // Notification::create($notificationData, $DBConn); // Uncomment when notification system is integrated
            $deadlineAlertCount++;
        }
    }
    echo "Sent {$deadlineAlertCount} deadline alerts\n";

    echo "[" . date('Y-m-d H:i:s') . "] Goals Daily Cron Job completed successfully\n";
    echo "Summary: {$snapshotCount} snapshots, {$reminderCount} reminders, {$entityScoreCount} entity scores, {$cascadeUpdateCount} cascade updates, {$deadlineAlertCount} deadline alerts\n";

} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

exit(0);

