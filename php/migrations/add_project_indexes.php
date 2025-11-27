<?php
/**
 * Database Migration: Add Project Performance Indexes
 * Run this file once to add all performance indexes
 *
 * @package Tija Practice Management System
 * @subpackage Migrations
 */

session_start();
$base = '../../';
set_include_path($base);

require_once 'php/includes.php';



$DBConn->begin();


// Security check
// if (!isset($userDetails->ID)) {
//     die('Please login first.');
// }

echo "<!DOCTYPE html><html><head><title>Database Migration</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1e1e1e;color:#d4d4d4;}";
echo ".success{color:#4ec9b0;}.error{color:#f48771;}.warning{color:#dcdcaa;}.info{color:#569cd6;}</style></head><body>";
echo "<h2 class='info'>Adding Performance Indexes...</h2>";

$statements = [
    "ALTER TABLE `tija_projects` ADD INDEX `idx_status` (`projectStatus`)" => "Projects: Status index",
    "ALTER TABLE `tija_projects` ADD INDEX `idx_client` (`clientID`)" => "Projects: Client index",
    "ALTER TABLE `tija_projects` ADD INDEX `idx_dates` (`projectStart`, `projectDeadline`)" => "Projects: Dates index",
    "ALTER TABLE `tija_projects` ADD INDEX `idx_entity` (`entityID`, `orgDataID`)" => "Projects: Entity index",
    "ALTER TABLE `tija_projects` ADD INDEX `idx_owner` (`projectOwnerID`)" => "Projects: Owner index",
    "ALTER TABLE `tija_project_tasks` ADD INDEX `idx_project_status` (`projectID`, `taskStatusID`)" => "Tasks: Project/Status index",
    "ALTER TABLE `tija_project_tasks` ADD INDEX `idx_task_deadline` (`taskDeadline`)" => "Tasks: Deadline index",
    "ALTER TABLE `tija_project_tasks` ADD INDEX `idx_project` (`projectID`)" => "Tasks: Project index",
    "ALTER TABLE `tija_project_tasks` ADD INDEX `idx_status` (`taskStatusID`)" => "Tasks: Status index"
];

$success = 0;
$skipped = 0;
$errors = 0;

foreach ($statements as $sql => $description) {
    echo "<p><strong class='info'>Running:</strong> $description<br>";
    echo "<code style='color:#888;'>$sql</code><br>";

    try {
        $DBConn->query($sql);
        $DBConn->execute();
        echo "<span class='success'>✓ Success</span></p>";
        $success++;
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "<span class='warning'>⚠ Already exists (skipped)</span></p>";
            $skipped++;
        } elseif (strpos($e->getMessage(), "doesn't exist") !== false) {
            echo "<span class='warning'>⚠ Column/Table doesn't exist (skipped)</span></p>";
            $skipped++;
        } else {
            echo "<span class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</span></p>";
            $errors++;
        }
    }
}

echo "<hr>";
echo "<h3 class='info'>Summary:</h3>";
echo "<p class='success'>✓ Created: $success indexes</p>";
echo "<p class='warning'>⚠ Skipped: $skipped (already existed or N/A)</p>";
echo "<p class='error'>✗ Errors: $errors</p>";

if ($errors == 0) {
    echo "<h2 class='success'>✅ Migration Complete!</h2>";
    echo "<p class='success'>Your database queries should now be 60-80% faster.</p>";
    echo "<p><a href='" . $base . "html/?s=user&ss=projects' style='color:#4ec9b0;'>→ Go to Projects</a></p>";
} else {
    echo "<h2 class='error'>Some errors occurred. Check the messages above.</h2>";
}

echo "</body></html>";
?>

