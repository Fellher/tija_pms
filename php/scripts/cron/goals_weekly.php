<?php
/**
 * Goals Weekly Cron Job
 * Runs weekly to create performance snapshots and update currency rates
 *
 * @package    TIJA_PMS
 * @subpackage Goals
 * @version    1.0.0
 *
 * Usage: php php/scripts/cron/goals_weekly.php
 * Or via cron: 0 3 * * 0 /usr/bin/php /path/to/php/scripts/cron/goals_weekly.php
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
require_once 'php/classes/goalscoring.php';

echo "[" . date('Y-m-d H:i:s') . "] Starting Goals Weekly Cron Job\n";

try {
    // 1. Create performance snapshots for warehouse
    echo "Creating weekly performance snapshots...\n";
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
    echo "Created {$snapshotCount} weekly snapshots\n";

    // 2. Update currency rates (would integrate with external API in production)
    echo "Updating currency rates...\n";
    // Note: In production, this would fetch rates from an external API
    // For now, we'll just log that it should be done
    $currencyPairs = $DBConn->retrieve_db_table_rows(
        'tija_goal_currency_rates',
        array('rateID', 'fromCurrency', 'toCurrency'),
        array('expiryDate' => 'NULL'),
        false
    );

    $rateUpdateCount = 0;
    if ($currencyPairs) {
        // In production, fetch from external API:
        // $rates = fetchCurrencyRatesFromAPI();
        // For now, just mark as needing update
        foreach ($currencyPairs as $pair) {
            // Example: Update spot rate (would come from API)
            // $newSpotRate = fetchRate($pair->fromCurrency, $pair->toCurrency);
            // $DBConn->update_table(
            //     'tija_goal_currency_rates',
            //     array('spotRate' => $newSpotRate, 'LastUpdate' => date('Y-m-d H:i:s')),
            //     array('rateID' => $pair->rateID)
            // );
            $rateUpdateCount++;
        }
    }
    echo "Checked {$rateUpdateCount} currency pairs (manual update required)\n";

    // 3. Compliance checks (jurisdiction rules)
    echo "Running compliance checks...\n";
    require_once 'php/classes/goalcompliance.php';

    $jurisdictions = $DBConn->retrieve_db_table_rows(
        'tija_entities',
        array('entityID', 'entityCountry'),
        array('Lapsed' => 'N', 'Suspended' => 'N'),
        false
    );

    $complianceCheckCount = 0;
    if ($jurisdictions) {
        foreach ($jurisdictions as $jurisdiction) {
            // Check if data retention needs to be enforced
            $deletedCount = GoalCompliance::enforceDataRetention($jurisdiction->entityID, $DBConn);
            if ($deletedCount > 0) {
                $complianceCheckCount++;
                echo "  Enforced data retention for jurisdiction {$jurisdiction->entityID} ({$jurisdiction->entityCountry})\n";
            }
        }
    }
    echo "Completed compliance checks for {$complianceCheckCount} jurisdictions\n";

    // 4. Generate weekly performance report summary
    echo "Generating weekly performance summary...\n";
    $weeklySummary = $DBConn->retrieve_db_table_rows_custom(
        "SELECT
            COUNT(DISTINCT g.goalUUID) as totalGoals,
            COUNT(DISTINCT CASE WHEN g.status = 'Active' THEN g.goalUUID END) as activeGoals,
            COUNT(DISTINCT CASE WHEN g.status = 'Completed' THEN g.goalUUID END) as completedGoals,
            AVG(g.completionPercentage) as avgCompletion,
            COUNT(DISTINCT g.ownerEntityID) as entitiesWithGoals,
            COUNT(DISTINCT g.ownerUserID) as usersWithGoals
         FROM tija_goals g
         WHERE g.sysEndTime IS NULL
         AND g.Lapsed = 'N'",
        array()
    );

    if ($weeklySummary && count($weeklySummary) > 0) {
        $summary = $weeklySummary[0];
        echo "Weekly Summary:\n";
        echo "  Total Goals: {$summary['totalGoals']}\n";
        echo "  Active Goals: {$summary['activeGoals']}\n";
        echo "  Completed Goals: {$summary['completedGoals']}\n";
        echo "  Average Completion: " . number_format($summary['avgCompletion'], 2) . "%\n";
        echo "  Entities with Goals: {$summary['entitiesWithGoals']}\n";
        echo "  Users with Goals: {$summary['usersWithGoals']}\n";
    }

    // 5. Archive old snapshots (older than 2 years)
    echo "Archiving old snapshots...\n";
    $archiveDate = date('Y-m-d', strtotime('-2 years'));
    $archivedCount = $DBConn->execute_custom_query(
        "DELETE FROM tija_goal_performance_snapshots
         WHERE snapshotDate < ?",
        array(array('archiveDate', $archiveDate))
    );
    echo "Archived {$archivedCount} old snapshots\n";

    echo "[" . date('Y-m-d H:i:s') . "] Goals Weekly Cron Job completed successfully\n";

} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

exit(0);

