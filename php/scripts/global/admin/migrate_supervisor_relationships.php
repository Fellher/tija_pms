<?php
session_start();
$base = '../../../../';
set_include_path($base);

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'php/includes.php';

$response = [
    'success' => false,
    'partialSuccess' => false,
    'message' => '',
    'results' => []
];

try {
    // Check admin access
    $hasMigrationPermission = $isValidAdmin || $isAdmin || $isSuperAdmin || $isTenantAdmin || $isHRManager;
    if (!$hasMigrationPermission) {
        throw new Exception("You are not authorized to perform this action.");
    }

    $DBConn->begin();

    // Get parameters
    $entityID = isset($_POST['entityID']) && $_POST['entityID'] !== ''
        ? intval($_POST['entityID'])
        : null;

    $employeeID = isset($_POST['employeeID']) && $_POST['employeeID'] !== ''
        ? intval($_POST['employeeID'])
        : null;

    $employeeIDs = isset($_POST['employeeIDs']) && is_array($_POST['employeeIDs'])
        ? array_unique(array_filter(array_map('intval', $_POST['employeeIDs'])))
        : [];

    // Helper closure to migrate a single employee
    $migrateSingleEmployee = function($employeeIdentifier) use ($DBConn) {
        $result = [
            'employeeID' => $employeeIdentifier,
            'status' => 'failed',
            'message' => ''
        ];

        $employee = Employee::employees(['ID' => $employeeIdentifier], true, $DBConn);
        if (!$employee) {
            $result['message'] = "Employee not found";
            return $result;
        }

        if (!isset($employee->supervisorID) || !$employee->supervisorID) {
            $result['message'] = "Employee has no supervisor to migrate";
            $result['status'] = 'skipped';
            return $result;
        }

        $existing = Data::reporting_relationships([
            'employeeID' => $employeeIdentifier,
            'isCurrent' => 'Y',
            'Suspended' => 'N'
        ], true, $DBConn);

        if ($existing) {
            $result['message'] = "Employee already has a current reporting relationship";
            $result['status'] = 'skipped';
            return $result;
        }

        $effectiveDate = $employee->employmentStartDate ?? date('Y-m-d');
        if (Reporting::syncSupervisorToReporting($employeeIdentifier, $employee->supervisorID, $DBConn, $effectiveDate)) {
            $result['status'] = 'success';
            $result['message'] = 'Reporting relationship migrated successfully';
        } else {
            $result['message'] = 'Failed to migrate reporting relationship';
        }

        return $result;
    };

    if (!empty($employeeIDs)) {
        $resultsSummary = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'details' => []
        ];

        foreach ($employeeIDs as $id) {
            $migrationResult = $migrateSingleEmployee($id);
            $resultsSummary['details'][] = $migrationResult;

            if ($migrationResult['status'] === 'success') {
                $resultsSummary['success']++;
            } elseif ($migrationResult['status'] === 'skipped') {
                $resultsSummary['skipped']++;
            } else {
                $resultsSummary['failed']++;
            }
        }

        $DBConn->commit();

        $response['results'] = $resultsSummary;
        $response['success'] = $resultsSummary['failed'] === 0 && $resultsSummary['success'] > 0;
        $response['partialSuccess'] = $resultsSummary['failed'] > 0 && $resultsSummary['success'] > 0;
        $response['message'] = sprintf(
            "Processed %d employees. Success: %d, Skipped: %d, Failed: %d",
            count($employeeIDs),
            $resultsSummary['success'],
            $resultsSummary['skipped'],
            $resultsSummary['failed']
        );
    } elseif ($employeeID) {
        $migrationResult = $migrateSingleEmployee($employeeID);

        if ($migrationResult['status'] === 'success') {
            $DBConn->commit();
            $response['success'] = true;
            $response['message'] = $migrationResult['message'];
        } else {
            throw new Exception($migrationResult['message'] ?: 'Failed to migrate reporting relationship');
        }
    } else {
        // Migrate all for entity or entire system
        $results = Reporting::migrateAllLegacyRelationships($entityID, $DBConn);

        $DBConn->commit();

        $response['success'] = ($results['failed'] ?? 0) === 0;
        $response['partialSuccess'] = ($results['failed'] ?? 0) > 0 && ($results['success'] ?? 0) > 0;
        $response['results'] = $results;
        $response['message'] = sprintf(
            "Migration completed. Success: %d, Failed: %d, Skipped: %d",
            $results['success'] ?? 0,
            $results['failed'] ?? 0,
            $results['skipped'] ?? 0
        );
    }

} catch (Exception $e) {
    if ($DBConn) {
        $DBConn->rollback();
    }
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
?>
