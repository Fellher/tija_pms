<?php
/**
 * Quick Migration Runner for tija_project_files table
 *
 * Usage: Run this file directly in browser or via command line
 * URL: http://localhost/demo-pms.tija.ke/php/database/migrations/run_project_files_migration.php
 */

// Include database connection
require_once('../../../php/config/database.php');

try {
    $DBConn = new Database();

    // Read SQL file
    $sqlFile = __DIR__ . '/create_project_files_table.sql';

    if (!file_exists($sqlFile)) {
        die("Error: SQL file not found at: $sqlFile");
    }

    $sql = file_get_contents($sqlFile);

    // Remove comments and split by semicolon
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );

    echo "<h2>Running Project Files Table Migration</h2>";
    echo "<pre>";

    $successCount = 0;
    $errorCount = 0;

    foreach ($statements as $statement) {
        if (empty(trim($statement))) {
            continue;
        }

        try {
            // Execute statement
            $result = $DBConn->execute_query($statement);

            if ($result !== false) {
                echo "✓ Successfully executed statement\n";
                $successCount++;
            } else {
                echo "⚠ Statement executed but returned false\n";
                $successCount++;
            }
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();

            // Ignore "table already exists" errors
            if (strpos($errorMsg, 'already exists') !== false ||
                strpos($errorMsg, 'Duplicate table') !== false) {
                echo "ℹ Table already exists (this is OK)\n";
                $successCount++;
            } else {
                echo "✗ Error: " . $errorMsg . "\n";
                $errorCount++;
            }
        }
    }

    echo "\n";
    echo "========================================\n";
    echo "Migration Summary:\n";
    echo "✓ Successful: $successCount\n";
    echo "✗ Errors: $errorCount\n";
    echo "========================================\n";

    if ($errorCount === 0) {
        echo "\n✅ Migration completed successfully!\n";
        echo "\nYou can now use the project files feature.\n";
    } else {
        echo "\n⚠ Some errors occurred. Please review the output above.\n";
    }

    echo "</pre>";

    // Verify table exists
    echo "<h3>Verification</h3>";
    echo "<pre>";
    try {
        $verifySQL = "SHOW TABLES LIKE 'tija_project_files'";
        $result = $DBConn->fetch_all_rows($verifySQL);

        if ($result && count($result) > 0) {
            echo "✅ Table 'tija_project_files' exists!\n\n";

            // Show table structure
            $structureSQL = "DESCRIBE tija_project_files";
            $columns = $DBConn->fetch_all_rows($structureSQL);

            if ($columns) {
                echo "Table Structure:\n";
                echo str_repeat("-", 60) . "\n";
                foreach ($columns as $col) {
                    echo sprintf("%-25s %-20s %s\n",
                        $col->Field,
                        $col->Type,
                        $col->Null === 'YES' ? 'NULL' : 'NOT NULL'
                    );
                }
            }
        } else {
            echo "❌ Table 'tija_project_files' was not created.\n";
        }
    } catch (Exception $e) {
        echo "Error verifying table: " . $e->getMessage() . "\n";
    }

    echo "</pre>";

} catch (Exception $e) {
    echo "<pre>";
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "</pre>";
}

