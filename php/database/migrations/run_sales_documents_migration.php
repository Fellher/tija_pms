<?php
/**
 * Sales Documents Table Migration Runner
 * Run this script to create the tija_sales_documents table
 *
 * @package    Tija CRM
 * @subpackage Database Migrations
 */

$base = '../../../';
set_include_path($base);
include 'php/includes.php';

$sqlFile = __DIR__ . '/create_sales_documents_table.sql';
$success = false;
$message = '';

if (file_exists($sqlFile)) {
    $sql = file_get_contents($sqlFile);

    try {
        // Remove foreign key constraints temporarily if table exists
        $checkTable = $DBConn->fetch_all_rows("SHOW TABLES LIKE 'tija_sales_documents'");

        if (empty($checkTable)) {
            // Execute SQL
            $statements = explode(';', $sql);
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement) && !preg_match('/^--/', $statement)) {
                    $DBConn->query($statement);
                }
            }
            $success = true;
            $message = 'Sales documents table created successfully!';
        } else {
            $success = true;
            $message = 'Sales documents table already exists.';
        }
    } catch (Exception $e) {
        $success = false;
        $message = 'Error: ' . $e->getMessage();
    }
} else {
    $success = false;
    $message = 'Migration SQL file not found.';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sales Documents Migration</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Sales Documents Table Migration</h1>
    <p class="<?= $success ? 'success' : 'error' ?>">
        <?= htmlspecialchars($message) ?>
    </p>
    <p><a href="javascript:history.back()">Go Back</a></p>
</body>
</html>

