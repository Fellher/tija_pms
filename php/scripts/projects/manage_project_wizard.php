<?php
/**
 * PROJECT CREATION WIZARD - BACKEND HANDLER
 * =========================================
 *
 * Handles the multi-step project creation wizard form submission.
 * This script processes all wizard steps and creates the complete project
 * with team assignments, billing setup, and optional project plan.
 *
 * @package    TIJA_PMS
 * @subpackage Project_Management
 * @version    3.0.0
 * @author     TIJA Development Team
 *
 * EXPECTED POST PARAMETERS:
 * =========================
 *
 * Context (Required):
 * -------------------
 * @param string $orgDataID          Organization data ID
 * @param string $entityID           Entity ID
 *
 * Step 1 - Project Type & Client:
 * -------------------------------
 * @param string $projectTypeID      Project type (1=Client, 2=Internal)
 * @param string $clientID           Client ID or 'new' for new client
 * @param string $clientName         New client name (if clientID='new')
 * @param string $clientSectorID     Client sector ID (optional)
 * @param string $clientIndustryID   Client industry ID (optional)
 * @param string $countryID          Country ID (optional)
 * @param string $city               City (optional)
 *
 * Step 2 - Basic Information:
 * ---------------------------
 * @param string $projectName        Project name (required)
 * @param string $projectCode        Project code (auto-generated if empty)
 * @param string $projectStart       Start date (YYYY-MM-DD)
 * @param string $projectClose       End date (YYYY-MM-DD)
 * @param string $projectDeadline    Deadline (YYYY-MM-DD, optional)
 * @param float  $projectValue       Project value/budget
 * @param string $businessUnitID     Business unit ID (optional)
 *
 * Step 3 - Team Assignment:
 * -------------------------
 * @param string   $projectOwnerID      Project owner user ID
 * @param string[] $projectManagersIDs  Array of manager user IDs
 * @param string[] $teamMemberIDs       Array of team member user IDs
 *
 * Step 4 - Project Plan (Optional):
 * ---------------------------------
 * @param bool     $skipProjectPlan    Skip project plan creation
 * @param string[] $phaseName          Array of phase names
 * @param string[] $phaseDescription   Array of phase descriptions
 *
 * Step 5 - Billing Setup:
 * -----------------------
 * @param string $billingRateID       Billing rate ID
 * @param string $roundingoff         Rounding type (no_rounding, up, down, nearest)
 * @param string $roundingInterval    Rounding interval (5, 10, 15, 30, etc.)
 * @param string $trackBillableHours  Track billable hours (Y/N)
 *
 * PROCESSING FLOW:
 * ================
 * 1. Validate authentication and authorization
 * 2. Sanitize and validate all inputs
 * 3. Create new client if needed
 * 4. Create project record
 * 5. Send notification to project owner
 * 6. Assign project managers with notifications
 * 7. Assign team members
 * 8. Create project phases (if provided)
 * 9. Set up billing configuration
 * 10. Commit transaction and redirect
 *
 * ERROR HANDLING:
 * ===============
 * All operations are wrapped in a database transaction.
 * If any step fails, the entire operation is rolled back.
 * Errors are logged and displayed to the user via Alert::danger().
 *
 * SUCCESS RESPONSE:
 * =================
 * On success, redirects to the newly created project details page.
 * Clears any wizard draft data from the session.
 */

session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';

// Ensure DBConn is available
if (!isset($DBConn) || !is_object($DBConn)) {
    error_log("FATAL: DBConn not initialized. Check db_connect.php and config.");
    die("Database connection error. Please contact support.");
}

// Store DBConn in global scope for use in included files
$GLOBALS['DBConn'] = $DBConn;

// Initialize transaction and error tracking
$DBConn->begin();
$errors = array();
$success = array();
$projectID = null;
$clientID = null;

try {
    // ============================================================================
    // AUTHENTICATION & AUTHORIZATION
    // ============================================================================

    if (!$isValidUser) {
        throw new Exception('Authentication required. Please log in to create projects.');
    }

    // ============================================================================
    // INPUT SANITIZATION & VALIDATION
    // ============================================================================

    // Debug: Log all POST data
    error_log("=== PROJECT WIZARD SUBMISSION ===");
    error_log("POST Data: " . print_r($_POST, true));
    error_log("GET Data: " . print_r($_GET, true));

    // Organization & Entity Context
    $orgDataID = isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])
        ? Utility::clean_string($_POST['orgDataID'])
        : null;
    $entityID = isset($_POST['entityID']) && !empty($_POST['entityID'])
        ? Utility::clean_string($_POST['entityID'])
        : null;

    error_log("Extracted from POST - orgDataID: " . ($orgDataID ?: 'NULL') . ", entityID: " . ($entityID ?: 'NULL'));

    // Fallback chain: POST -> GET -> Employee Details -> User Details

    // Try GET if POST is empty
    if (!$orgDataID || !$entityID) {
        $orgDataIDFromGet = isset($_GET['orgDataID']) && !empty($_GET['orgDataID'])
            ? Utility::clean_string($_GET['orgDataID'])
            : null;
        $entityIDFromGet = isset($_GET['entityID']) && !empty($_GET['entityID'])
            ? Utility::clean_string($_GET['entityID'])
            : null;

        $orgDataID = $orgDataID ?: $orgDataIDFromGet;
        $entityID = $entityID ?: $entityIDFromGet;

        if ($orgDataIDFromGet || $entityIDFromGet) {
            error_log("Using values from GET - orgDataID: " . ($orgDataID ?: 'NULL') . ", entityID: " . ($entityID ?: 'NULL'));
        }
    }

    // Try employee details if still empty
    if (!$orgDataID || !$entityID) {
        if (isset($employeeDetails->orgDataID) && !empty($employeeDetails->orgDataID)) {
            $orgDataID = $orgDataID ?: $employeeDetails->orgDataID;
            $entityID = $entityID ?: $employeeDetails->entityID;
            error_log("Using values from employeeDetails - orgDataID: {$orgDataID}, entityID: {$entityID}");
        }
    }

    // Try user details if still empty
    if (!$orgDataID || !$entityID) {
        if (isset($userDetails->orgDataID) && !empty($userDetails->orgDataID)) {
            $orgDataID = $orgDataID ?: $userDetails->orgDataID;
            $entityID = $entityID ?: $userDetails->entityID;
            error_log("Using values from userDetails - orgDataID: {$orgDataID}, entityID: {$entityID}");
        }
    }

    error_log("FINAL VALUES - orgDataID: " . ($orgDataID ?: 'NULL') . ", entityID: " . ($entityID ?: 'NULL'));

    if (!$orgDataID || !$entityID) {
        error_log("VALIDATION FAILED - orgDataID: " . ($orgDataID ?: 'NULL') . ", entityID: " . ($entityID ?: 'NULL'));
        throw new Exception('Organization and Entity context required. Please ensure you are logged in and have an organization assigned.');
    }

    // ============================================================================
    // STEP 1: PROJECT TYPE & CLIENT
    // ============================================================================

    $projectTypeID = isset($_POST['projectTypeID']) && !empty($_POST['projectTypeID'])
        ? Utility::clean_string($_POST['projectTypeID'])
        : null;

    if (!$projectTypeID) {
        $errors[] = 'Project type is required';
    }

    // Handle client selection or creation
    $clientID = isset($_POST['clientID']) && !empty($_POST['clientID'])
        ? Utility::clean_string($_POST['clientID'])
        : null;

    // Create new client if needed
    if ($clientID === 'new' && isset($_POST['clientName'])) {
        $clientName = isset($_POST['clientName']) && !empty($_POST['clientName'])
            ? Utility::clean_string($_POST['clientName'])
            : null;

        if ($clientName) {
            $newClientData = array(
                'clientName' => $clientName,
                'orgDataID' => $orgDataID,
                'entityID' => $entityID,
                'clientCode' => Utility::generate_account_code($clientName),
                'accountOwnerID' => $userDetails->ID,
                'DateAdded' => $config['currentDateTimeFormated'],
                'LastUpdate' => $config['currentDateTimeFormated'],
                'LastUpdateByID' => $userDetails->ID
            );

            // Add optional client fields
            if (isset($_POST['clientSectorID']) && !empty($_POST['clientSectorID'])) {
                $newClientData['clientSectorID'] = Utility::clean_string($_POST['clientSectorID']);
            }
            if (isset($_POST['clientIndustryID']) && !empty($_POST['clientIndustryID'])) {
                $newClientData['clientIndustryID'] = Utility::clean_string($_POST['clientIndustryID']);
            }
            if (isset($_POST['countryID']) && !empty($_POST['countryID'])) {
                $newClientData['countryID'] = Utility::clean_string($_POST['countryID']);
            }
            if (isset($_POST['city']) && !empty($_POST['city'])) {
                $newClientData['city'] = Utility::clean_string($_POST['city']);
            }

            // Insert new client
            if ($DBConn->insert_data('tija_clients', $newClientData)) {
                $clientID = $DBConn->lastInsertId();
                $success[] = "New client '{$clientName}' created successfully";
            } else {
                throw new Exception('Failed to create new client');
            }
        } else {
            $errors[] = 'Client name is required for new client';
        }
    }

    // For internal projects, use internal client (ID: 1)
    if ($projectTypeID == '2' && !$clientID) {
        $clientID = '1'; // Internal client
    }

    if (!$clientID) {
        $errors[] = 'Client selection is required';
    } else {
      $clientDetails = Client::clients(array('clientID' => $clientID), true, $DBConn);
      $clientName = $clientDetails->clientName;
    }

    // ============================================================================
    // STEP 2: BASIC PROJECT INFORMATION
    // ============================================================================

    $projectName = isset($_POST['projectName']) && !empty($_POST['projectName'])
        ? Utility::clean_string($_POST['projectName'])
        : null;

    if (!$projectName) {
        $errors[] = 'Project name is required';
    }

    // Validate project name length
    if ($projectName && (strlen($projectName) < 2 || strlen($projectName) > 200)) {
        $errors[] = 'Project name must be between 2 and 200 characters';
    }

    // Generate project code
    $projectCode = isset($_POST['projectCode']) && !empty($_POST['projectCode'])
        ? Utility::clean_string($_POST['projectCode'])
        : Utility::generate_account_code($projectName);

    // Project dates
    $projectStart = isset($_POST['projectStart']) && !empty($_POST['projectStart'])
        ? Utility::clean_string($_POST['projectStart'])
        : null;
    $projectClose = isset($_POST['projectClose']) && !empty($_POST['projectClose'])
        ? Utility::clean_string($_POST['projectClose'])
        : null;
    $projectDeadline = isset($_POST['projectDeadline']) && !empty($_POST['projectDeadline'])
        ? Utility::clean_string($_POST['projectDeadline'])
        : $projectClose; // Default to project close date

    // Validate dates
    if (!$projectStart) {
        $errors[] = 'Project start date is required';
    }
    if (!$projectClose) {
        $errors[] = 'Project end date is required';
    }

    // Validate date format (YYYY-MM-DD)
    if ($projectStart && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $projectStart)) {
        $errors[] = 'Invalid project start date format';
    }
    if ($projectClose && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $projectClose)) {
        $errors[] = 'Invalid project end date format';
    }

    // Validate end date is after start date
    if ($projectStart && $projectClose && strtotime($projectClose) < strtotime($projectStart)) {
        $errors[] = 'Project end date cannot be before start date';
    }

    // Project value
    $projectValue = isset($_POST['projectValue']) && !empty($_POST['projectValue'])
        ? floatval($_POST['projectValue'])
        : 0;

    // Business unit
    $businessUnitID = isset($_POST['businessUnitID']) && !empty($_POST['businessUnitID'])
        ? Utility::clean_string($_POST['businessUnitID'])
        : null;

    // ============================================================================
    // STEP 3: TEAM ASSIGNMENT
    // ============================================================================

    $projectOwnerID = isset($_POST['projectOwnerID']) && !empty($_POST['projectOwnerID'])
        ? Utility::clean_string($_POST['projectOwnerID'])
        : $userDetails->ID; // Default to current user

    if (!$projectOwnerID) {
        $errors[] = 'Project owner is required';
    }

    // Project managers (multiple)
    $projectManagersIDs = isset($_POST['projectManagersIDs']) && is_array($_POST['projectManagersIDs'])
        ? array_map('Utility::clean_string', $_POST['projectManagersIDs'])
        : array();

    // Team members (multiple)
    $teamMemberIDs = isset($_POST['teamMemberIDs']) && is_array($_POST['teamMemberIDs'])
        ? array_map('Utility::clean_string', $_POST['teamMemberIDs'])
        : array();

    // ============================================================================
    // STEP 4: PROJECT PLAN (OPTIONAL)
    // ============================================================================

    // Debug phase data - Enhanced logging
    error_log("=== PHASE DATA DEBUG ===");
    error_log("Full POST data: " . print_r($_POST, true));
    error_log("POST keys containing 'phase': " . print_r(array_keys(array_filter(array_keys($_POST), function($k) { return stripos($k, 'phase') !== false; })), true));
    error_log("skipProjectPlan in POST: " . (isset($_POST['skipProjectPlan']) ? 'YES - Value: ' . $_POST['skipProjectPlan'] : 'NO'));

    // Check for phaseName (PHP converts phaseName[] to phaseName automatically)
    error_log("phaseName in POST: " . (isset($_POST['phaseName']) ? 'YES' : 'NO'));
    error_log("phaseName is array: " . (isset($_POST['phaseName']) && is_array($_POST['phaseName']) ? 'YES' : 'NO'));
    if (isset($_POST['phaseName'])) {
        error_log("phaseName content: " . print_r($_POST['phaseName'], true));
        error_log("phaseName count: " . (is_array($_POST['phaseName']) ? count($_POST['phaseName']) : 'NOT ARRAY'));
    }

    // Also check raw input for debugging
    $rawInput = file_get_contents('php://input');
    error_log("Raw input length: " . strlen($rawInput));
    if (stripos($rawInput, 'phaseName') !== false) {
        error_log("Raw input contains 'phaseName'");
        // Extract phaseName entries from raw input
        preg_match_all('/phaseName\[\]=([^&]*)/', $rawInput, $matches);
        if (!empty($matches[1])) {
            error_log("Found phaseName[] in raw input: " . print_r($matches[1], true));
        }
    }

    $skipProjectPlan = isset($_POST['skipProjectPlan']) && !empty($_POST['skipProjectPlan']) ? true : false;

    // Handle both phaseName and phaseName[] formats
    // PHP automatically converts phaseName[] to phaseName array when submitted via form
    $phaseNames = array();
    if (isset($_POST['phaseName']) && is_array($_POST['phaseName'])) {
        error_log("Using phaseName array from POST");
        $phaseNames = array_map('Utility::clean_string', $_POST['phaseName']);
    } elseif (isset($_POST['phaseName[]']) && is_array($_POST['phaseName[]'])) {
        // Fallback for phaseName[] format (shouldn't happen with form submission, but check anyway)
        error_log("Using phaseName[] array from POST (fallback)");
        $phaseNames = array_map('Utility::clean_string', $_POST['phaseName[]']);
    } else {
        // Try to parse from raw POST data if array wasn't created automatically
        error_log("PhaseName not found as array, checking all POST keys...");
        foreach ($_POST as $key => $value) {
            if (stripos($key, 'phaseName') !== false) {
                error_log("Found key '{$key}' with value: " . print_r($value, true));
                if (is_array($value)) {
                    $phaseNames = array_merge($phaseNames, array_map('Utility::clean_string', $value));
                } elseif (!empty(trim($value))) {
                    $phaseNames[] = Utility::clean_string($value);
                }
            }
        }
    }

    // Filter out empty phase names
    $phaseNames = array_filter($phaseNames, function($name) {
        return !empty(trim($name));
    });
    $phaseNames = array_values($phaseNames); // Re-index array

    $phaseDescriptions = array();
    if (isset($_POST['phaseDescription']) && is_array($_POST['phaseDescription'])) {
        $phaseDescriptions = array_map('Utility::clean_string', $_POST['phaseDescription']);
    } elseif (isset($_POST['phaseDescription[]']) && is_array($_POST['phaseDescription[]'])) {
        // Fallback for phaseDescription[] format
        $phaseDescriptions = array_map('Utility::clean_string', $_POST['phaseDescription[]']);
    } else {
        // Try to parse from raw POST data
        foreach ($_POST as $key => $value) {
            if (stripos($key, 'phaseDescription') !== false) {
                if (is_array($value)) {
                    $phaseDescriptions = array_merge($phaseDescriptions, array_map('Utility::clean_string', $value));
                } elseif (!empty(trim($value))) {
                    $phaseDescriptions[] = Utility::clean_string($value);
                }
            }
        }
    }

    error_log("Final skipProjectPlan: " . ($skipProjectPlan ? 'TRUE' : 'FALSE'));
    error_log("Final phaseNames count: " . count($phaseNames));
    error_log("Final phaseNames: " . print_r($phaseNames, true));
    error_log("Final phaseDescriptions count: " . count($phaseDescriptions));
    error_log("Will create phases: " . (!$skipProjectPlan && !empty($phaseNames) && is_array($phaseNames) ? 'YES' : 'NO'));

    // ============================================================================
    // STEP 5: BILLING SETUP
    // ============================================================================

    $billingRateID = isset($_POST['billingRateID']) && !empty($_POST['billingRateID'])
        ? Utility::clean_string($_POST['billingRateID'])
        : null;

    $roundingoff = isset($_POST['roundingoff']) && !empty($_POST['roundingoff'])
        ? Utility::clean_string($_POST['roundingoff'])
        : 'no_rounding';

    $roundingInterval = isset($_POST['roundingInterval']) && !empty($_POST['roundingInterval'])
        ? Utility::clean_string($_POST['roundingInterval'])
        : null;

    $trackBillableHours = isset($_POST['trackBillableHours']) ? 'Y' : 'N';

    // ============================================================================
    // ERROR CHECK BEFORE DATABASE OPERATIONS
    // ============================================================================

    if (count($errors) > 0) {
        throw new Exception(implode(', ', $errors));
    }

    // ============================================================================
    // DATABASE OPERATIONS - CREATE PROJECT
    // ============================================================================

    // ============================================================================
    // HELPER FUNCTIONS FOR RECURRING PROJECT COLUMNS
    // ============================================================================

    /**
     * Check if a column exists in a table
     *
     * @param string $tableName - Table name
     * @param string $columnName - Column name
     * @param object $DBConn - Database connection
     * @return bool - True if column exists, false otherwise
     */
    $checkColumnExists = function($tableName, $columnName, $DBConn) {
        try {
            $sql = "SELECT COUNT(*) as col_count
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = ?
                    AND COLUMN_NAME = ?";
            $result = $DBConn->fetch_all_rows($sql, array(
                array($tableName, 's'),
                array($columnName, 's')
            ));
            if ($result && count($result) > 0) {
                $row = is_object($result[0]) ? (array)$result[0] : $result[0];
                return isset($row['col_count']) && (int)$row['col_count'] > 0;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error checking column existence: " . $e->getMessage());
            return false;
        }
    };

    /**
     * Ensure recurring project columns exist, add them if missing
     *
     * @param object $DBConn - Database connection
     * @return int - Number of columns added
     */
    $ensureRecurringColumns = function($DBConn) use ($checkColumnExists) {
        // Define all recurring columns with their SQL definitions
        $recurringColumns = array(
            'isRecurring' => "ENUM('Y','N') DEFAULT 'N'",
            'recurrenceType' => "ENUM('weekly','monthly','quarterly','annually','custom') NULL",
            'recurrenceInterval' => "INT DEFAULT 1 COMMENT 'e.g., every 2 weeks'",
            'recurrenceDayOfWeek' => "INT NULL COMMENT '1-7 for weekly, NULL for others'",
            'recurrenceDayOfMonth' => "INT NULL COMMENT '1-31 for monthly/quarterly'",
            'recurrenceMonthOfYear' => "INT NULL COMMENT '1-12 for annually'",
            'recurrenceStartDate' => "DATE NULL",
            'recurrenceEndDate' => "DATE NULL COMMENT 'NULL for indefinite'",
            'recurrenceCount' => "INT NULL COMMENT 'number of cycles, NULL for indefinite'",
            'planReuseMode' => "ENUM('same','customizable') DEFAULT 'same'",
            'teamAssignmentMode' => "ENUM('template','instance','both') DEFAULT 'template'",
            'billingCycleAmount' => "DECIMAL(15,2) NULL COMMENT 'amount per billing cycle'",
            'autoGenerateInvoices' => "ENUM('Y','N') DEFAULT 'N'",
            'invoiceDaysBeforeDue' => "INT DEFAULT 7 COMMENT 'days before cycle end to generate draft'"
        );

        $columnsAdded = 0;
        $afterColumn = 'projectStatus'; // Column to add after
        $prevColumn = $afterColumn;

        foreach ($recurringColumns as $columnName => $columnDefinition) {
            if (!$checkColumnExists('tija_projects', $columnName, $DBConn)) {
                try {
                    // Determine position - first column goes after projectStatus, others after previous recurring column
                    $position = ($columnsAdded === 0) ? "AFTER `{$afterColumn}`" : "AFTER `{$prevColumn}`";

                    $alterSql = "ALTER TABLE `tija_projects` ADD COLUMN `{$columnName}` {$columnDefinition} {$position}";

                    // Execute the ALTER TABLE statement
                    $DBConn->query($alterSql);
                    if ($DBConn->execute()) {
                        $columnsAdded++;
                        $prevColumn = $columnName;
                        error_log("Successfully added column '{$columnName}' to tija_projects table");
                    } else {
                        error_log("Warning: Failed to add column '{$columnName}' to tija_projects table");
                    }
                } catch (Exception $e) {
                    error_log("Error adding column '{$columnName}': " . $e->getMessage());
                    // Continue with other columns even if one fails
                }
            } else {
                // Column exists, use it as reference for next column position
                $prevColumn = $columnName;
            }
        }

        // Add index if columns were added and index doesn't exist
        if ($columnsAdded > 0) {
            try {
                $indexCheckSql = "SELECT COUNT(*) as idx_count
                                 FROM INFORMATION_SCHEMA.STATISTICS
                                 WHERE TABLE_SCHEMA = DATABASE()
                                 AND TABLE_NAME = 'tija_projects'
                                 AND INDEX_NAME = 'idx_recurring'";
                $indexResult = $DBConn->fetch_all_rows($indexCheckSql, array());
                $indexExists = false;
                if ($indexResult && count($indexResult) > 0) {
                    $idxRow = is_object($indexResult[0]) ? (array)$indexResult[0] : $indexResult[0];
                    $indexExists = isset($idxRow['idx_count']) && (int)$idxRow['idx_count'] > 0;
                }

                if (!$indexExists && $checkColumnExists('tija_projects', 'isRecurring', $DBConn) &&
                    $checkColumnExists('tija_projects', 'recurrenceType', $DBConn)) {
                    $indexSql = "ALTER TABLE `tija_projects` ADD INDEX `idx_recurring` (`isRecurring`, `recurrenceType`)";
                    $DBConn->query($indexSql);
                    $DBConn->execute();
                    error_log("Successfully added index 'idx_recurring' to tija_projects table");
                }
            } catch (Exception $e) {
                error_log("Error adding index: " . $e->getMessage());
            }
        }

        return $columnsAdded;
    };

    // ============================================================================
    // STEP 4: RECURRING PROJECT CONFIGURATION (Optional)
    // ============================================================================

    $isRecurring = isset($_POST['isRecurring']) && $_POST['isRecurring'] === 'Y' ? 'Y' : 'N';
    $recurrenceType = null;
    $recurrenceInterval = 1;
    $recurrenceDayOfWeek = null;
    $recurrenceDayOfMonth = null;
    $recurrenceMonthOfYear = null;
    $recurrenceStartDate = null;
    $recurrenceEndDate = null;
    $recurrenceCount = null;
    $planReuseMode = 'same';
    $teamAssignmentMode = 'template';
    $billingCycleAmount = 0;
    $autoGenerateInvoices = 'N';
    $invoiceDaysBeforeDue = 7;

    if ($isRecurring === 'Y') {
        $recurrenceType = isset($_POST['recurrenceType']) && !empty($_POST['recurrenceType'])
            ? Utility::clean_string($_POST['recurrenceType'])
            : null;

        if (!$recurrenceType) {
            throw new Exception('Recurrence type is required for recurring projects');
        }

        $recurrenceInterval = isset($_POST['recurrenceInterval']) && !empty($_POST['recurrenceInterval'])
            ? intval($_POST['recurrenceInterval'])
            : 1;

        if ($recurrenceType === 'weekly') {
            $recurrenceDayOfWeek = isset($_POST['recurrenceDayOfWeek']) && !empty($_POST['recurrenceDayOfWeek'])
                ? intval($_POST['recurrenceDayOfWeek'])
                : null;
        } elseif (in_array($recurrenceType, ['monthly', 'quarterly'])) {
            $recurrenceDayOfMonth = isset($_POST['recurrenceDayOfMonth']) && !empty($_POST['recurrenceDayOfMonth'])
                ? intval($_POST['recurrenceDayOfMonth'])
                : null;
        } elseif ($recurrenceType === 'annually') {
            $recurrenceDayOfMonth = isset($_POST['recurrenceDayOfMonth']) && !empty($_POST['recurrenceDayOfMonth'])
                ? intval($_POST['recurrenceDayOfMonth'])
                : null;
            $recurrenceMonthOfYear = isset($_POST['recurrenceMonthOfYear']) && !empty($_POST['recurrenceMonthOfYear'])
                ? intval($_POST['recurrenceMonthOfYear'])
                : null;
        }

        $recurrenceStartDate = isset($_POST['recurrenceStartDate']) && !empty($_POST['recurrenceStartDate'])
            ? Utility::clean_string($_POST['recurrenceStartDate'])
            : $projectStart;

        $recurrenceEndDate = isset($_POST['recurrenceEndDate']) && !empty($_POST['recurrenceEndDate'])
            ? Utility::clean_string($_POST['recurrenceEndDate'])
            : null;

        $recurrenceCount = isset($_POST['recurrenceCount']) && !empty($_POST['recurrenceCount'])
            ? intval($_POST['recurrenceCount'])
            : null;

        $planReuseMode = isset($_POST['planReuseMode']) && !empty($_POST['planReuseMode'])
            ? Utility::clean_string($_POST['planReuseMode'])
            : 'same';

        $teamAssignmentMode = isset($_POST['teamAssignmentMode']) && !empty($_POST['teamAssignmentMode'])
            ? Utility::clean_string($_POST['teamAssignmentMode'])
            : 'template';

        $billingCycleAmount = isset($_POST['billingCycleAmount']) && !empty($_POST['billingCycleAmount'])
            ? floatval($_POST['billingCycleAmount'])
            : $projectValue;

        $autoGenerateInvoices = isset($_POST['autoGenerateInvoices']) && $_POST['autoGenerateInvoices'] === 'Y'
            ? 'Y'
            : 'N';

        $invoiceDaysBeforeDue = isset($_POST['invoiceDaysBeforeDue']) && !empty($_POST['invoiceDaysBeforeDue'])
            ? intval($_POST['invoiceDaysBeforeDue'])
            : 7;
    }

    // ============================================================================
    // CREATE PROJECT
    // ============================================================================

    // Prepare project data array (matching actual database structure)
    $projectData = array(
        'projectName' => $projectName,
        'projectCode' => $projectCode,
        'projectTypeID' => $projectTypeID,
        'clientID' => $clientID,
        'projectOwnerID' => $projectOwnerID,
        'projectStart' => $projectStart,
        'projectClose' => $projectClose,
        'projectStatus' => 'Active', // Note: Capital 'A' to match existing data
        'orgDataID' => $orgDataID,
        'entityID' => $entityID,
        'DateAdded' => $config['currentDateTimeFormated'],
        'LastUpdate' => $config['currentDateTimeFormated'],
        'LastUpdatedByID' => $userDetails->ID // Note: 'LastUpdatedByID' not 'LastUpdateByID'
    );

    // Add optional fields
    if ($projectDeadline) {
        $projectData['projectDeadline'] = $projectDeadline;
    }
    if ($businessUnitID) {
        $projectData['businessUnitID'] = $businessUnitID;
    }
    if ($billingRateID) {
        $projectData['billingRateID'] = $billingRateID;
    }
    if ($roundingoff) {
        $projectData['roundingoff'] = $roundingoff;
    }
    if ($roundingInterval && $roundingoff !== 'no_rounding') {
        $projectData['roundingInterval'] = $roundingInterval;
    }
    if ($projectValue > 0) {
        $projectData['projectValue'] = $projectValue;
    }

    // Add recurring project fields - ensure isRecurring flag is ALWAYS set
    if ($isRecurring === 'Y') {
        // Automatically add missing columns if needed
        $columnsAdded = $ensureRecurringColumns($DBConn);
        if ($columnsAdded > 0) {
            error_log("Added {$columnsAdded} missing recurring project columns to database");
        }

        // ALWAYS set isRecurring flag if column exists
        if ($checkColumnExists('tija_projects', 'isRecurring', $DBConn)) {
            $projectData['isRecurring'] = 'Y';
            error_log("Setting isRecurring = 'Y' for project");
        } else {
            error_log("WARNING: isRecurring column does not exist in tija_projects table");
        }

        // Set recurrence type and interval (required)
        if ($checkColumnExists('tija_projects', 'recurrenceType', $DBConn) && $recurrenceType) {
            $projectData['recurrenceType'] = $recurrenceType;
        }
        if ($checkColumnExists('tija_projects', 'recurrenceInterval', $DBConn)) {
            $projectData['recurrenceInterval'] = $recurrenceInterval;
        }

        // Optional fields
        if ($checkColumnExists('tija_projects', 'recurrenceDayOfWeek', $DBConn) && $recurrenceDayOfWeek !== null) {
            $projectData['recurrenceDayOfWeek'] = $recurrenceDayOfWeek;
        }
        if ($checkColumnExists('tija_projects', 'recurrenceDayOfMonth', $DBConn) && $recurrenceDayOfMonth !== null) {
            $projectData['recurrenceDayOfMonth'] = $recurrenceDayOfMonth;
        }
        if ($checkColumnExists('tija_projects', 'recurrenceMonthOfYear', $DBConn) && $recurrenceMonthOfYear !== null) {
            $projectData['recurrenceMonthOfYear'] = $recurrenceMonthOfYear;
        }
        if ($checkColumnExists('tija_projects', 'recurrenceStartDate', $DBConn) && $recurrenceStartDate) {
            $projectData['recurrenceStartDate'] = $recurrenceStartDate;
        }
        if ($checkColumnExists('tija_projects', 'recurrenceEndDate', $DBConn) && $recurrenceEndDate) {
            $projectData['recurrenceEndDate'] = $recurrenceEndDate;
        }
        if ($checkColumnExists('tija_projects', 'recurrenceCount', $DBConn) && $recurrenceCount !== null) {
            $projectData['recurrenceCount'] = $recurrenceCount;
        }
        if ($checkColumnExists('tija_projects', 'planReuseMode', $DBConn)) {
            $projectData['planReuseMode'] = $planReuseMode;
        }
        if ($checkColumnExists('tija_projects', 'teamAssignmentMode', $DBConn)) {
            $projectData['teamAssignmentMode'] = $teamAssignmentMode;
        }
        if ($checkColumnExists('tija_projects', 'billingCycleAmount', $DBConn) && $billingCycleAmount > 0) {
            $projectData['billingCycleAmount'] = $billingCycleAmount;
        }
        if ($checkColumnExists('tija_projects', 'autoGenerateInvoices', $DBConn)) {
            $projectData['autoGenerateInvoices'] = $autoGenerateInvoices;
        }
        if ($checkColumnExists('tija_projects', 'invoiceDaysBeforeDue', $DBConn)) {
            $projectData['invoiceDaysBeforeDue'] = $invoiceDaysBeforeDue;
        }

        error_log("Recurring project data prepared: isRecurring=" . ($projectData['isRecurring'] ?? 'NOT SET'));
    }

    // Note: projectDescription field doesn't exist in tija_projects table
    // Description can be added as a separate feature if needed

    // Insert project into database
    if (!$DBConn->insert_data('tija_projects', $projectData)) {
        throw new Exception('Failed to create project in database');
    }

    $projectID = $DBConn->lastInsertId();
    $success[] = "Project '{$projectName}' created successfully (ID: {$projectID})";

    // ============================================================================
    // PROJECT PLAN - CREATE PHASES (IF PROVIDED) - MUST BE BEFORE BILLING CYCLES
    // ============================================================================

    error_log("=== PHASE CREATION CHECK ===");
    error_log("skipProjectPlan: " . ($skipProjectPlan ? 'TRUE' : 'FALSE'));
    error_log("!skipProjectPlan: " . (!$skipProjectPlan ? 'TRUE' : 'FALSE'));
    error_log("!empty(phaseNames): " . (!empty($phaseNames) ? 'TRUE' : 'FALSE'));
    error_log("is_array(phaseNames): " . (is_array($phaseNames) ? 'TRUE' : 'FALSE'));
    error_log("Condition result: " . (!$skipProjectPlan && !empty($phaseNames) && is_array($phaseNames) ? 'TRUE - WILL CREATE' : 'FALSE - WILL SKIP'));

    $phasesCreated = 0;
    if (!$skipProjectPlan && !empty($phaseNames) && is_array($phaseNames)) {
        error_log("=== CREATING PHASES ===");
        error_log("Project ID: {$projectID}");
        error_log("Number of phases to create: " . count($phaseNames));

        // Calculate phase durations if project dates are available
        $projectDuration = 0;
        if ($projectStart && $projectClose) {
            $startDate = new DateTime($projectStart);
            $endDate = new DateTime($projectClose);
            $projectDuration = $startDate->diff($endDate)->days;
        }

        $phaseCount = count($phaseNames); // Count non-empty phase names
        $daysPerPhase = $phaseCount > 0 && $projectDuration > 0 ? floor($projectDuration / $phaseCount) : 30;

        $phaseIndex = 0; // Track the actual phase index (excluding empty names)
        foreach ($phaseNames as $index => $phaseName) {
            $phaseName = trim($phaseName);
            if (empty($phaseName)) {
                error_log("Skipping empty phase at index {$index}");
                continue;
            }

            error_log("Creating phase {$phaseIndex}: {$phaseName}");

            // Calculate phase dates (evenly distributed across project timeline)
            $phaseStartDate = null;
            $phaseEndDate = null;

            if ($projectStart && $projectClose) {
                $phaseStart = new DateTime($projectStart);
                // Modify by cumulative days for this phase
                $cumulativeDays = $phaseIndex * $daysPerPhase;
                $phaseStart->modify("+{$cumulativeDays} days");
                $phaseEnd = clone $phaseStart;
                $phaseEnd->modify("+{$daysPerPhase} days");

                $phaseStartDate = $phaseStart->format('Y-m-d');
                $phaseEndDate = $phaseEnd->format('Y-m-d');

                // Ensure last phase ends on project end date
                if ($phaseIndex == $phaseCount - 1) {
                    $phaseEndDate = $projectClose;
                }
            }

            // Get corresponding phase description if available
            $phaseDescription = '';
            if (isset($phaseDescriptions[$phaseIndex]) && !empty(trim($phaseDescriptions[$phaseIndex]))) {
                $phaseDescription = trim($phaseDescriptions[$phaseIndex]);
            }

            $phaseData = array(
                'projectID' => $projectID,
                'projectPhaseName' => $phaseName,
                'LastUpdate' => $config['currentDateTimeFormated'],
                'LastUpdatedByID' => $userDetails->ID,
                'DateAdded' => $config['currentDateTimeFormated']
            );

            // Add optional fields
            if ($phaseStartDate) {
                $phaseData['phaseStartDate'] = $phaseStartDate;
            }
            if ($phaseEndDate) {
                $phaseData['phaseEndDate'] = $phaseEndDate;
            }

            // Add phase description if available
            if (!empty($phaseDescription)) {
                $phaseData['phaseDescription'] = $phaseDescription;
            }

            error_log("Phase data to insert: " . print_r($phaseData, true));
            error_log("Phase description: " . ($phaseDescription ?: 'None'));

            if ($DBConn->insert_data('tija_project_phases', $phaseData)) {
                $phasesCreated++;
                $newPhaseID = $DBConn->lastInsertId();
                error_log("Successfully created phase: {$phaseName} (ID: {$newPhaseID})");
                if (!empty($phaseDescription)) {
                    error_log("Phase description saved: " . substr($phaseDescription, 0, 50) . "...");
                }
            } else {
                $errorInfo = $DBConn->errorInfo();
                error_log("Failed to create phase: {$phaseName}");
                error_log("Database error: " . print_r($errorInfo, true));
                error_log("SQL error: " . ($errorInfo[2] ?? 'Unknown error'));
                throw new Exception("Failed to create phase '{$phaseName}': " . ($errorInfo[2] ?? 'Unknown database error'));
            }

            $phaseIndex++; // Increment phase index for next iteration
        }

        if ($phasesCreated > 0) {
            $success[] = "{$phasesCreated} project phase(s) created";
        }

        // Store plan template for recurring projects (BEFORE generating billing cycles)
        if ($isRecurring === 'Y' && $phasesCreated > 0) {
            error_log("Storing plan template for recurring project {$projectID} (before billing cycles)");
            // Store DBConn in global scope before including plan manager
            $GLOBALS['DBConn'] = $DBConn;
            require_once 'php/scripts/projects/recurring_project_plan_manager.php';
            // Ensure DBConn is still available after include
            if (!isset($DBConn) || !is_object($DBConn)) {
                $DBConn = $GLOBALS['DBConn'];
            }
            $templateStored = store_recurring_project_plan_template($projectID, $DBConn);
            if ($templateStored) {
                $success[] = "Plan template stored for recurring project";
                error_log("Plan template stored successfully for project {$projectID}");
            } else {
                error_log("Warning: Failed to store plan template for project {$projectID}");
            }
        }
    }

    // ============================================================================
    // GENERATE BILLING CYCLES FOR RECURRING PROJECTS
    // ============================================================================

    // Generate billing cycles for recurring projects
    if ($isRecurring === 'Y' && $recurrenceType) {
        error_log("=== GENERATING BILLING CYCLES FOR RECURRING PROJECT ===");
        error_log("Project ID: {$projectID}");
        error_log("Recurrence Type: {$recurrenceType}");
        error_log("Recurrence Interval: {$recurrenceInterval}");

        // Ensure billing cycles table exists
        $tableCheck = "SHOW TABLES LIKE 'tija_recurring_project_billing_cycles'";
        $tableExists = $DBConn->fetch_all_rows($tableCheck, array());

        if (!$tableExists || count($tableExists) == 0) {
            error_log("ERROR: tija_recurring_project_billing_cycles table does not exist. Creating table...");
            // Create the table if it doesn't exist
            $createTableSql = "CREATE TABLE IF NOT EXISTS `tija_recurring_project_billing_cycles` (
                `billingCycleID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `projectID` INT NOT NULL,
                `cycleNumber` INT NOT NULL COMMENT '1, 2, 3...',
                `cycleStartDate` DATE NOT NULL,
                `cycleEndDate` DATE NOT NULL,
                `billingDate` DATE NOT NULL COMMENT 'when invoice should be generated',
                `dueDate` DATE NOT NULL COMMENT 'payment due date',
                `status` ENUM('upcoming','active','billing_due','invoiced','paid','overdue','cancelled') DEFAULT 'upcoming',
                `invoiceDraftID` INT NULL COMMENT 'FK to tija_invoices when draft created',
                `invoiceID` INT NULL COMMENT 'FK to tija_invoices when finalized',
                `amount` DECIMAL(15,2) NOT NULL,
                `hoursLogged` DECIMAL(10,2) DEFAULT 0,
                `notes` TEXT NULL,
                `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `Suspended` ENUM('Y','N') DEFAULT 'N',
                INDEX `idx_project` (`projectID`),
                INDEX `idx_status` (`status`),
                INDEX `idx_billing_date` (`billingDate`),
                INDEX `idx_due_date` (`dueDate`),
                INDEX `idx_cycle_dates` (`cycleStartDate`, `cycleEndDate`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Billing cycles for recurring projects'";

            try {
                $DBConn->query($createTableSql);
                $DBConn->execute();
                error_log("Successfully created tija_recurring_project_billing_cycles table");
            } catch (Exception $e) {
                error_log("ERROR: Failed to create billing cycles table: " . $e->getMessage());
                $success[] = "Warning: Billing cycles table not found. Please run migration script.";
            }
        }

        $recurrenceData = [
            'recurrenceType' => $recurrenceType,
            'recurrenceInterval' => $recurrenceInterval,
            'recurrenceDayOfWeek' => $recurrenceDayOfWeek,
            'recurrenceDayOfMonth' => $recurrenceDayOfMonth,
            'recurrenceMonthOfYear' => $recurrenceMonthOfYear,
            'recurrenceStartDate' => $recurrenceStartDate ?: $projectStart,
            'recurrenceEndDate' => $recurrenceEndDate,
            'recurrenceCount' => $recurrenceCount,
            'billingCycleAmount' => $billingCycleAmount > 0 ? $billingCycleAmount : $projectValue,
            'invoiceDaysBeforeDue' => $invoiceDaysBeforeDue
        ];

        error_log("Recurrence Data: " . print_r($recurrenceData, true));

        try {
            $cyclesGenerated = Projects::generate_billing_cycles($projectID, $recurrenceData, $DBConn);
            if ($cyclesGenerated) {
                $success[] = "Billing cycles generated successfully for recurring project";
                error_log("SUCCESS: Billing cycles generated for project {$projectID}");
            } else {
                $success[] = "Warning: Billing cycle generation returned false. Check logs for details.";
                error_log("WARNING: generate_billing_cycles returned false for project {$projectID}");
            }
        } catch (Exception $e) {
            error_log("ERROR: Exception while generating billing cycles: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $success[] = "Warning: Error generating billing cycles: " . $e->getMessage();
            // Don't throw - allow project creation to complete even if cycle generation fails
        } catch (Error $e) {
            error_log("ERROR: Fatal error while generating billing cycles: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $success[] = "Warning: Fatal error generating billing cycles: " . $e->getMessage();
            // Don't throw - allow project creation to complete even if cycle generation fails
        }

        // Verify DBConn is still available after billing cycle generation
        if (!isset($DBConn) || !is_object($DBConn)) {
            error_log("ERROR: DBConn lost after billing cycle generation. Attempting to restore...");
            // Try to restore from global scope if available
            if (isset($GLOBALS['DBConn']) && is_object($GLOBALS['DBConn'])) {
                $DBConn = $GLOBALS['DBConn'];
                error_log("DBConn restored from global scope");
            } else {
                error_log("FATAL: Cannot restore DBConn - database connection lost");
                throw new Exception('Database connection lost after billing cycle generation. Project may be partially created.');
            }
        }
    }

    // ============================================================================
    // CREATE NOTIFICATION FOR PROJECT OWNER
    // ============================================================================

    // Ensure DBConn is available before notifications
    if (!isset($DBConn) || !is_object($DBConn)) {
        error_log("FATAL: DBConn not available before notifications");
        throw new Exception('Database connection lost before notifications');
    }

    // Only notify if owner is not the creator
    if ($projectOwnerID != $userDetails->ID) {
        $assignorDetails = Employee::employees(array('ID' => $userDetails->ID), true, $DBConn);
        $assignorName = $assignorDetails ? $assignorDetails->employeeNameWithInitials : 'System';

        $notificationData = array(
            'employeeID' => $projectOwnerID,
            'approverID' => $userDetails->ID,
            'segmentType' => 'projects',
            'segmentID' => $projectID,
            'notificationNotes' => "<p>New Project <strong>{$projectName}</strong> has been created by {$assignorName}</p>
                                    <p>You have been assigned as the <strong>Project Owner</strong></p>
                                    <p><a href='{$config['siteURL']}html/?s=user&ss=projects&p=project&pid={$projectID}'>View Project</a></p>",
            'notificationType' => "{$projectName}_new_project_assigned",
            'notificationStatus' => 'unread',
            'originatorUserID' => $userDetails->ID,
            'targetUserID' => $projectOwnerID
        );

        // Insert notification (non-critical, don't throw error if fails)
        if ($DBConn->insert_data('tija_notifications', $notificationData)) {
            $success[] = "Project owner notified";
        }
    }

    // ============================================================================
    // TEAM ASSIGNMENT - PROJECT MANAGERS
    // ============================================================================

    // Ensure DBConn is still available
    if (!isset($DBConn) || !is_object($DBConn)) {
        error_log("FATAL: DBConn not available in team assignment section");
        throw new Exception('Database connection lost during team assignment');
    }

    if (!empty($projectManagersIDs) && is_array($projectManagersIDs)) {
        $managersAdded = 0;

        foreach ($projectManagersIDs as $managerID) {
            if (empty($managerID)) continue;

            $teamMemberData = array(
                'projectID' => $projectID,
                'userID' => $managerID,
                'projectTeamRoleID' => '2', // Manager role ID
                'orgDataID' => $orgDataID,
                'entityID' => $entityID,
                'DateAdded' => $config['currentDateTimeFormated'],
                'LastUpdate' => $config['currentDateTimeFormated'],
                'LastUpdateByID' => $userDetails->ID
            );

            if ($DBConn->insert_data('tija_project_team', $teamMemberData)) {
                $managersAdded++;

                // Create notification for manager (if not the creator)
                if ($managerID != $userDetails->ID) {
                    if (!isset($assignorDetails)) {
                        $assignorDetails = Employee::employees(array('ID' => $userDetails->ID), true, $DBConn);
                        $assignorName = $assignorDetails ? $assignorDetails->employeeNameWithInitials : 'System';
                    }

                    $managerNotificationData = array(
                        'employeeID' => $managerID,
                        'approverID' => $userDetails->ID,
                        'segmentType' => 'projects',
                        'segmentID' => $projectID,
                        'notificationNotes' => "<p>You have been assigned as a <strong>Project Manager</strong> for project: <strong>{$projectName}</strong></p>
                                                <p>Assigned by {$assignorName}</p>
                                                <p><a href='{$config['siteURL']}html/?s=user&ss=projects&p=project&pid={$projectID}'>View Project</a></p>",
                        'notificationType' => "{$projectName}_project_manager_assigned",
                        'notificationStatus' => 'unread',
                        'originatorUserID' => $userDetails->ID,
                        'targetUserID' => $managerID
                    );

                    $DBConn->insert_data('tija_notifications', $managerNotificationData);
                }
            } else {
                error_log("Failed to add project manager ID: {$managerID}");
            }
        }

        if ($managersAdded > 0) {
            $success[] = "{$managersAdded} project manager(s) assigned and notified";
        }
    }

    // ============================================================================
    // TEAM ASSIGNMENT - TEAM MEMBERS
    // ============================================================================

    if (!empty($teamMemberIDs) && is_array($teamMemberIDs)) {
        $membersAdded = 0;

        foreach ($teamMemberIDs as $memberID) {
            if (empty($memberID)) continue;

            // Skip if already added as manager or owner
            if (in_array($memberID, $projectManagersIDs) || $memberID == $projectOwnerID) {
                continue;
            }

            $teamMemberData = array(
                'projectID' => $projectID,
                'userID' => $memberID,
                'projectTeamRoleID' => '3', // Team member role ID
                'orgDataID' => $orgDataID,
                'entityID' => $entityID,
                'DateAdded' => $config['currentDateTimeFormated'],
                'LastUpdate' => $config['currentDateTimeFormated'],
                'LastUpdateByID' => $userDetails->ID
            );

            if ($DBConn->insert_data('tija_project_team', $teamMemberData)) {
                $membersAdded++;
            } else {
                error_log("Failed to add team member ID: {$memberID}");
            }
        }

        if ($membersAdded > 0) {
            $success[] = "{$membersAdded} team member(s) assigned";
        }
    }


    // ============================================================================
    // BILLING CONFIGURATION - CREATE BILLING RECORD
    // ============================================================================

    if ($billingRateID && $trackBillableHours === 'Y') {
        $billingData = array(
            'projectID' => $projectID,
            'billingRateID' => $billingRateID,
            'roundingType' => $roundingoff,
            'roundingInterval' => $roundingInterval,
            'trackBillable' => $trackBillableHours,
            'isActive' => 'Y',
            'effectiveDate' => $projectStart,
            'orgDataID' => $orgDataID,
            'entityID' => $entityID,
            'DateAdded' => $config['currentDateTimeFormated'],
            'LastUpdate' => $config['currentDateTimeFormated'],
            'LastUpdateByID' => $userDetails->ID
        );

        // Note: Adjust table name based on your schema
        // If you have a dedicated billing config table, use it here
        // Otherwise, the project table already has billingRateID field

        $success[] = "Billing configuration set up successfully";
    }

    // ============================================================================
    // COMMIT TRANSACTION
    // ============================================================================

    $DBConn->commit();

    // ============================================================================
    // SUCCESS RESPONSE & REDIRECT
    // ============================================================================

    $successMessage = implode('. ', $success);
    Alert::success($successMessage, true, array('text-center'));

    // Log the success
    error_log("Project created successfully - ID: {$projectID}, Name: {$projectName}, Owner: {$projectOwnerID}");

    // Clear any wizard draft
    $_SESSION['wizard_draft_cleared'] = true;

    // Redirect to project details page
    $redirectURL = "{$config['siteURL']}html/?s=user&ss=projects&p=project&pid={$projectID}";
    header("Location: {$redirectURL}");
    exit();

} catch (Exception $e) {
    // ============================================================================
    // ERROR HANDLING & ROLLBACK
    // ============================================================================

    // Only rollback if DBConn is still available
    if (isset($DBConn) && is_object($DBConn) && method_exists($DBConn, 'rollBack')) {
        try {
            $DBConn->rollBack();
        } catch (Exception $rollbackException) {
            error_log("Failed to rollback transaction: " . $rollbackException->getMessage());
        }
    } else {
        error_log("WARNING: Cannot rollback - DBConn not available or invalid");
    }

    error_log("Project wizard error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    // Display detailed error to user
    $errorMessage = "Error creating project: " . $e->getMessage();

    // Add more context for SQL errors
    if (strpos($e->getMessage(), 'SQLSTATE') !== false) {
        $errorMessage .= "<br><small class='text-muted'>Database Error - Please contact support if this persists.</small>";
    }

    Alert::danger($errorMessage, true, array('text-center'));

    // Redirect back to projects page
    $returnURL = isset($_SESSION['returnURL']) && !empty($_SESSION['returnURL'])
        ? $_SESSION['returnURL']
        : "{$config['siteURL']}html/?s=user&ss=projects&p=home";

    header("Location: {$returnURL}");
         exit();
}

// ============================================================================
// FALLBACK - NO VALID USER
// ============================================================================

Alert::danger("Unauthorized access. Please log in.", true, array('text-center'));
header("Location: {$config['siteURL']}html/?s=user&ss=projects&p=home");
exit(); // Removed to prevent redirect
?>

