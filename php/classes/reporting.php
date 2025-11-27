<?php
/**
 * Reporting Class - Handles reporting relationships and synchronization
 *
 * This class provides methods to manage reporting relationships between the
 * legacy supervisorID system and the new tija_reporting_relationships table.
 */

class Reporting {

    /**
     * Sync supervisor relationship from user_details to tija_reporting_relationships
     *
     * @param int $employeeID - The employee ID
     * @param int|null $supervisorID - The supervisor ID (0 or null for no supervisor)
     * @param object $DBConn - Database connection
     * @param string $effectiveDate - When the relationship starts (default: today)
     * @return bool - Success or failure
     */
    public static function syncSupervisorToReporting($employeeID, $supervisorID, $DBConn, $effectiveDate = null) {
        if (!$effectiveDate) {
            $effectiveDate = date('Y-m-d');
        }

        // Get employee details
        $employee = Employee::employees(['ID' => $employeeID], true, $DBConn);
        if (!$employee) {
            return false;
        }

        // End current relationships for this employee
        $currentRelationships = Data::reporting_relationships([
            'employeeID' => $employeeID,
            'isCurrent' => 'Y',
            'Suspended' => 'N'
        ], false, $DBConn);

        if ($currentRelationships) {
            foreach ($currentRelationships as $rel) {
                $DBConn->update_table('tija_reporting_relationships',
                    [
                        'isCurrent' => 'N',
                        'endDate' => date('Y-m-d'),
                        'LastUpdate' => date('Y-m-d H:i:s')
                    ],
                    ['relationshipID' => $rel->relationshipID]
                );
            }
        }

        // If supervisorID is 0 or null, we're removing the supervisor - done
        if ($supervisorID == 0 || $supervisorID == null || $supervisorID == '') {
            return true;
        }

        // Create new reporting relationship
        $relationshipData = [
            'employeeID' => $employeeID,
            'supervisorID' => $supervisorID,
            'entityID' => $employee->entityID,
            'orgDataID' => $employee->orgDataID,
            'relationshipType' => 'Direct',
            'relationshipStrength' => 'Primary',
            'effectiveDate' => $effectiveDate,
            'isCurrent' => 'Y',
            'reportingFrequency' => 'Weekly',
            'LastUpdate' => date('Y-m-d H:i:s')
        ];

        return $DBConn->insert_data('tija_reporting_relationships', $relationshipData);
    }

    /**
     * Get effective supervisor for an employee
     * Checks new reporting table first, falls back to supervisorID
     *
     * @param int $employeeID - The employee ID
     * @param object $DBConn - Database connection
     * @return object|null - Supervisor details or null
     */
    public static function getEffectiveSupervisor($employeeID, $DBConn) {
        // Try new reporting relationships first
        $relationship = Data::reporting_relationships([
            'employeeID' => $employeeID,
            'isCurrent' => 'Y',
            'Suspended' => 'N',
            'relationshipType' => 'Direct'
        ], true, $DBConn);

        if ($relationship && isset($relationship->supervisorID)) {
            return Employee::employees(['ID' => $relationship->supervisorID], true, $DBConn);
        }

        // Fall back to legacy supervisorID
        $employee = Employee::employees(['ID' => $employeeID], true, $DBConn);
        if ($employee && isset($employee->supervisorID) && $employee->supervisorID > 0) {
            return Employee::employees(['ID' => $employee->supervisorID], true, $DBConn);
        }

        return null;
    }

    /**
     * Get all direct reports for a supervisor
     * Combines both new and legacy systems
     *
     * @param int $supervisorID - The supervisor ID
     * @param object $DBConn - Database connection
     * @return array - Array of employees
     */
    public static function getDirectReports($supervisorID, $DBConn) {
        $directReports = [];
        $employeeIDs = [];

        // Get from new reporting relationships
        $newRelationships = Data::reporting_relationships([
            'supervisorID' => $supervisorID,
            'isCurrent' => 'Y',
            'Suspended' => 'N',
            'relationshipType' => 'Direct'
        ], false, $DBConn);

        if ($newRelationships) {
            foreach ($newRelationships as $rel) {
                if (!in_array($rel->employeeID, $employeeIDs)) {
                    $emp = Employee::employees(['ID' => $rel->employeeID], true, $DBConn);
                    if ($emp) {
                        $emp->reportingSource = 'new';
                        $directReports[] = $emp;
                        $employeeIDs[] = $rel->employeeID;
                    }
                }
            }
        }

        // Get from legacy supervisorID (only if not already in new system)
        $legacyReports = Employee::employees(['supervisorID' => $supervisorID, 'Suspended' => 'N'], false, $DBConn);

        if ($legacyReports) {
            foreach ($legacyReports as $emp) {
                if (!in_array($emp->ID, $employeeIDs)) {
                    $emp->reportingSource = 'legacy';
                    $directReports[] = $emp;
                }
            }
        }

        return $directReports;
    }

    /**
     * Migrate all legacy supervisor relationships to new system
     *
     * @param int $entityID - Entity ID to migrate (optional, migrates all if not provided)
     * @param object $DBConn - Database connection
     * @return array - Migration results
     */
    public static function migrateAllLegacyRelationships($entityID = null, $DBConn) {
        $results = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        // Get all employees with supervisors
        $where = ['Suspended' => 'N'];
        if ($entityID) {
            $where['entityID'] = $entityID;
        }

        $employees = Employee::employees($where, false, $DBConn);

        if (!$employees) {
            return $results;
        }

        foreach ($employees as $emp) {
            // Skip if no supervisor
            if (!isset($emp->supervisorID) || $emp->supervisorID == 0 || $emp->supervisorID == null) {
                $results['skipped']++;
                continue;
            }

            // Check if already in new system
            $existing = Data::reporting_relationships([
                'employeeID' => $emp->ID,
                'isCurrent' => 'Y',
                'Suspended' => 'N'
            ], true, $DBConn);

            if ($existing) {
                $results['skipped']++;
                continue;
            }

            // Migrate
            $effectiveDate = $emp->employmentStartDate ?? date('Y-m-d');
            if (self::syncSupervisorToReporting($emp->ID, $emp->supervisorID, $DBConn, $effectiveDate)) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = "Failed to migrate employee ID: {$emp->ID}";
            }
        }

        return $results;
    }

    /**
     * Update or create reporting relationship
     * Updates both new table and legacy supervisorID for backward compatibility
     *
     * @param array $data - Relationship data
     * @param object $DBConn - Database connection
     * @return bool - Success or failure
     */
    public static function updateReportingRelationship($data, $DBConn) {
        $employeeID = $data['employeeID'] ?? null;
        $supervisorID = $data['supervisorID'] ?? null;
        $relationshipID = $data['relationshipID'] ?? null;

        if (!$employeeID) {
            return false;
        }

        // Update user_details.supervisorID for backward compatibility
        $legacyUpdate = [
            'supervisorID' => ($supervisorID == 0 || $supervisorID == null) ? null : $supervisorID,
            'LastUpdate' => date('Y-m-d H:i:s')
        ];
        $DBConn->update_table('user_details', $legacyUpdate, ['ID' => $employeeID]);

        // If updating existing relationship
        if ($relationshipID) {
            $updateData = [
                'supervisorID' => $supervisorID,
                'relationshipType' => $data['relationshipType'] ?? 'Direct',
                'relationshipStrength' => $data['relationshipStrength'] ?? 'Primary',
                'effectiveDate' => $data['effectiveDate'] ?? date('Y-m-d'),
                'endDate' => $data['endDate'] ?? null,
                'isCurrent' => $data['isCurrent'] ?? 'Y',
                'reportingFrequency' => $data['reportingFrequency'] ?? 'Weekly',
                'canDelegate' => $data['canDelegate'] ?? 'N',
                'canSubstitute' => $data['canSubstitute'] ?? 'N',
                'notes' => $data['notes'] ?? null,
                'LastUpdate' => date('Y-m-d H:i:s')
            ];

            return $DBConn->update_table('tija_reporting_relationships', $updateData, ['relationshipID' => $relationshipID]);
        } else {
            // Create new relationship
            return self::syncSupervisorToReporting($employeeID, $supervisorID, $DBConn, $data['effectiveDate'] ?? date('Y-m-d'));
        }
    }

    /**
     * Get reporting hierarchy for an employee (full chain)
     *
     * @param int $employeeID - The employee ID
     * @param object $DBConn - Database connection
     * @param int $maxLevels - Maximum levels to traverse (prevent infinite loops)
     * @return array - Array of supervisors in hierarchy
     */
    public static function getReportingHierarchy($employeeID, $DBConn, $maxLevels = 10) {
        $hierarchy = [];
        $currentEmployeeID = $employeeID;
        $level = 0;

        while ($level < $maxLevels) {
            $supervisor = self::getEffectiveSupervisor($currentEmployeeID, $DBConn);

            if (!$supervisor) {
                break; // No more supervisors
            }

            // Prevent circular references
            if (in_array($supervisor->ID, array_column($hierarchy, 'ID'))) {
                break;
            }

            $supervisor->level = $level + 1;
            $hierarchy[] = $supervisor;

            $currentEmployeeID = $supervisor->ID;
            $level++;
        }

        return $hierarchy;
    }
}
?>

