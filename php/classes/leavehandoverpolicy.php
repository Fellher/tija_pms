<?php
/**
 * LeaveHandoverPolicy
 *
 * Provides CRUD operations and policy management for leave handover policies
 * with support for flexible targeting (entity-wide, role-based, job group, job level, job title).
 */
class LeaveHandoverPolicy
{
    /**
     * Create a new handover policy.
     *
     * @param array $policyData Policy data array
     * @param object $DBConn Database connection
     * @return int|false Policy ID on success, false on failure
     */
    public static function create_policy($policyData, $DBConn)
    {
        if (!$DBConn || empty($policyData['entityID'])) {
            return false;
        }

        $data = array(
            'entityID' => (int)$policyData['entityID'],
            'orgDataID' => isset($policyData['orgDataID']) ? (int)$policyData['orgDataID'] : null,
            'leaveTypeID' => isset($policyData['leaveTypeID']) && !empty($policyData['leaveTypeID']) ? (int)$policyData['leaveTypeID'] : null,
            'policyScope' => isset($policyData['policyScope']) ? Utility::clean_string($policyData['policyScope']) : 'entity_wide',
            'targetRoleID' => isset($policyData['targetRoleID']) && !empty($policyData['targetRoleID']) ? (int)$policyData['targetRoleID'] : null,
            'targetJobCategoryID' => isset($policyData['targetJobCategoryID']) && !empty($policyData['targetJobCategoryID']) ? (int)$policyData['targetJobCategoryID'] : null,
            'targetJobBandID' => isset($policyData['targetJobBandID']) && !empty($policyData['targetJobBandID']) ? (int)$policyData['targetJobBandID'] : null,
            'targetJobLevelID' => isset($policyData['targetJobLevelID']) && !empty($policyData['targetJobLevelID']) ? (int)$policyData['targetJobLevelID'] : null,
            'targetJobTitleID' => isset($policyData['targetJobTitleID']) && !empty($policyData['targetJobTitleID']) ? (int)$policyData['targetJobTitleID'] : null,
            'isMandatory' => isset($policyData['isMandatory']) && $policyData['isMandatory'] === 'Y' ? 'Y' : 'N',
            'minHandoverDays' => isset($policyData['minHandoverDays']) ? (int)$policyData['minHandoverDays'] : 0,
            'requireConfirmation' => isset($policyData['requireConfirmation']) && $policyData['requireConfirmation'] === 'N' ? 'N' : 'Y',
            'requireTraining' => isset($policyData['requireTraining']) && $policyData['requireTraining'] === 'Y' ? 'Y' : 'N',
            'requireCredentials' => isset($policyData['requireCredentials']) && $policyData['requireCredentials'] === 'Y' ? 'Y' : 'N',
            'requireTools' => isset($policyData['requireTools']) && $policyData['requireTools'] === 'Y' ? 'Y' : 'N',
            'requireDocuments' => isset($policyData['requireDocuments']) && $policyData['requireDocuments'] === 'Y' ? 'Y' : 'N',
            'allowProjectIntegration' => isset($policyData['allowProjectIntegration']) && $policyData['allowProjectIntegration'] === 'Y' ? 'Y' : 'N',
            'requireNomineeAcceptance' => isset($policyData['requireNomineeAcceptance']) && $policyData['requireNomineeAcceptance'] === 'N' ? 'N' : 'Y',
            'nomineeResponseDeadlineHours' => isset($policyData['nomineeResponseDeadlineHours']) ? (int)$policyData['nomineeResponseDeadlineHours'] : 48,
            'allowPeerRevision' => isset($policyData['allowPeerRevision']) && $policyData['allowPeerRevision'] === 'N' ? 'N' : 'Y',
            'maxRevisionAttempts' => isset($policyData['maxRevisionAttempts']) ? (int)$policyData['maxRevisionAttempts'] : 3,
            'effectiveDate' => isset($policyData['effectiveDate']) ? Utility::clean_string($policyData['effectiveDate']) : date('Y-m-d'),
            'expiryDate' => isset($policyData['expiryDate']) && !empty($policyData['expiryDate']) ? Utility::clean_string($policyData['expiryDate']) : null,
            'policyName' => isset($policyData['policyName']) ? Utility::clean_string($policyData['policyName']) : null,
            'policyDescription' => isset($policyData['policyDescription']) ? Utility::clean_string($policyData['policyDescription']) : null,
            'DateAdded' => date('Y-m-d H:i:s'),
            'LastUpdate' => date('Y-m-d H:i:s'),
            'Lapsed' => 'N',
            'Suspended' => 'N'
        );

        if ($DBConn->insert_data('tija_leave_handover_policies', $data)) {
            return $DBConn->lastInsertId();
        }

        return false;
    }

    /**
     * Update an existing handover policy.
     *
     * @param int $policyID Policy ID
     * @param array $policyData Policy data array
     * @param object $DBConn Database connection
     * @return bool Success status
     */
    public static function update_policy($policyID, $policyData, $DBConn)
    {
        if (!$DBConn || !$policyID) {
            return false;
        }

        $updateData = array();
        $allowedFields = array(
            'orgDataID', 'leaveTypeID', 'policyScope', 'targetRoleID', 'targetJobCategoryID',
            'targetJobBandID', 'targetJobLevelID', 'targetJobTitleID', 'isMandatory', 'minHandoverDays',
            'requireConfirmation', 'requireTraining', 'requireCredentials', 'requireTools', 'requireDocuments',
            'allowProjectIntegration', 'requireNomineeAcceptance', 'nomineeResponseDeadlineHours',
            'allowPeerRevision', 'maxRevisionAttempts', 'effectiveDate', 'expiryDate',
            'policyName', 'policyDescription', 'Lapsed', 'Suspended'
        );

        foreach ($allowedFields as $field) {
            if (isset($policyData[$field])) {
                if (in_array($field, array('orgDataID', 'leaveTypeID', 'targetRoleID', 'targetJobCategoryID',
                    'targetJobBandID', 'targetJobLevelID', 'targetJobTitleID', 'minHandoverDays',
                    'nomineeResponseDeadlineHours', 'maxRevisionAttempts'))) {
                    $updateData[$field] = !empty($policyData[$field]) ? (int)$policyData[$field] : null;
                } elseif (in_array($field, array('effectiveDate', 'expiryDate'))) {
                    $updateData[$field] = !empty($policyData[$field]) ? Utility::clean_string($policyData[$field]) : null;
                } elseif (in_array($field, array('policyName', 'policyDescription'))) {
                    $updateData[$field] = Utility::clean_string($policyData[$field]);
                } elseif (in_array($field, array('policyScope'))) {
                    $updateData[$field] = Utility::clean_string($policyData[$field]);
                } else {
                    $updateData[$field] = Utility::clean_string($policyData[$field]);
                }
            }
        }

        $updateData['LastUpdate'] = date('Y-m-d H:i:s');

        return $DBConn->update_table('tija_leave_handover_policies', $updateData, array('policyID' => $policyID));
    }

    /**
     * Get policies with optional filters.
     *
     * @param array $filters Filter conditions
     * @param object $DBConn Database connection
     * @return array Array of policy objects
     */
    public static function get_policies($filters, $DBConn)
    {
        if (!$DBConn) {
            return array();
        }

        $where = array();
        $params = array();

        if (isset($filters['entityID'])) {
            $where[] = "p.entityID = ?";
            $params[] = array((int)$filters['entityID'], 'i');
        }

        if (isset($filters['policyScope'])) {
            $where[] = "p.policyScope = ?";
            $params[] = array(Utility::clean_string($filters['policyScope']), 's');
        }

        if (isset($filters['Lapsed'])) {
            $where[] = "p.Lapsed = ?";
            $params[] = array(Utility::clean_string($filters['Lapsed']), 's');
        } else {
            $where[] = "p.Lapsed = 'N'";
        }

        if (isset($filters['Suspended'])) {
            $where[] = "p.Suspended = ?";
            $params[] = array(Utility::clean_string($filters['Suspended']), 's');
        } else {
            $where[] = "p.Suspended = 'N'";
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT p.* FROM tija_leave_handover_policies p {$whereClause} ORDER BY p.policyScope, p.effectiveDate DESC";

        $rows = $DBConn->fetch_all_rows($sql, $params);
        return $rows ? $rows : array();
    }

    /**
     * Delete (soft delete) a policy.
     *
     * @param int $policyID Policy ID
     * @param object $DBConn Database connection
     * @return bool Success status
     */
    public static function delete_policy($policyID, $DBConn)
    {
        if (!$DBConn || !$policyID) {
            return false;
        }

        return $DBConn->update_table('tija_leave_handover_policies', array(
            'Lapsed' => 'Y',
            'LastUpdate' => date('Y-m-d H:i:s')
        ), array('policyID' => $policyID));
    }

    /**
     * Get policy by employee context (with priority matching).
     *
     * @param int $employeeID Employee ID
     * @param int $entityID Entity ID
     * @param int $leaveTypeID Leave type ID
     * @param int $noOfDays Number of days
     * @param object $DBConn Database connection
     * @return array Policy match result
     */
    public static function get_policy_by_employee_context($employeeID, $entityID, $leaveTypeID, $noOfDays, $DBConn)
    {
        if (!$DBConn || !$employeeID || !$entityID) {
            return array('required' => false, 'policy' => null);
        }

        // Get employee details
        $employee = Employee::employees(array('ID' => $employeeID), true, $DBConn);
        if (!$employee) {
            return array('required' => false, 'policy' => null);
        }

        $jobTitleID = isset($employee->jobTitleID) ? (int)$employee->jobTitleID : null;
        $jobCategoryID = isset($employee->jobCategoryID) ? (int)$employee->jobCategoryID : null;
        $jobBandID = isset($employee->jobBandID) ? (int)$employee->jobBandID : null;
        $jobLevelID = null; // Would need to be retrieved from user_details or org roles

        $jobLevelID = isset($employee->roleLevelID) ? (int)$employee->roleLevelID : null;

        $params = array(
            array($entityID, 'i')
        );

        $where = "p.entityID = ? AND p.Lapsed = 'N' AND p.Suspended = 'N'
                  AND p.effectiveDate <= CURRENT_DATE()
                  AND (p.expiryDate IS NULL OR p.expiryDate >= CURRENT_DATE())";

        if (!empty($leaveTypeID)) {
            $where .= " AND (p.leaveTypeID = ? OR p.leaveTypeID IS NULL)";
            $params[] = array($leaveTypeID, 'i');
        }

        // Build scope matching conditions with priority
        // Priority: job_title > job_level > job_group > role > entity_wide
        $scopeConditions = array();

        // Job title match (highest priority)
        if ($jobTitleID) {
            $scopeConditions[] = "(p.policyScope = 'job_title' AND p.targetJobTitleID = ?)";
            $params[] = array($jobTitleID, 'i');
        }

        // Job level match
        if ($jobLevelID) {
            $scopeConditions[] = "(p.policyScope = 'job_level' AND p.targetJobLevelID = ?)";
            $params[] = array($jobLevelID, 'i');
        }

        // Job group match (category or band)
        if ($jobCategoryID || $jobBandID) {
            $jobGroupConditions = array();
            if ($jobCategoryID) {
                $jobGroupConditions[] = "p.targetJobCategoryID = ?";
                $params[] = array($jobCategoryID, 'i');
            }
            if ($jobBandID) {
                $jobGroupConditions[] = "p.targetJobBandID = ?";
                $params[] = array($jobBandID, 'i');
            }
            if (!empty($jobGroupConditions)) {
                $scopeConditions[] = "(p.policyScope = 'job_group' AND (" . implode(' OR ', $jobGroupConditions) . "))";
            }
        }

        // Role-based match (would need roleID from employee/user_details)
        // For now, skip role-based matching as it requires additional context

        // Entity-wide (lowest priority, always available)
        $scopeConditions[] = "p.policyScope = 'entity_wide'";

        if (!empty($scopeConditions)) {
            $where .= " AND (" . implode(' OR ', $scopeConditions) . ")";
        }

        // Order by priority: job_title first, then job_level, then job_group, then entity_wide
        // Also prioritize leave-type-specific over generic
        $sql = "SELECT p.* FROM tija_leave_handover_policies p
                WHERE {$where}
                ORDER BY
                    CASE
                        WHEN p.policyScope = 'job_title' THEN 1
                        WHEN p.policyScope = 'job_level' THEN 2
                        WHEN p.policyScope = 'job_group' THEN 3
                        WHEN p.policyScope = 'role_based' THEN 4
                        WHEN p.policyScope = 'entity_wide' THEN 5
                        ELSE 6
                    END ASC,
                    CASE WHEN p.leaveTypeID IS NULL THEN 1 ELSE 0 END ASC,
                    p.minHandoverDays DESC
                LIMIT 1";

        $policyRows = $DBConn->fetch_all_rows($sql, $params);
        if (!$policyRows || count($policyRows) === 0) {
            return array('required' => false, 'policy' => null);
        }

        $policy = is_object($policyRows[0]) ? $policyRows[0] : (object)$policyRows[0];
        $required = ($policy->isMandatory === 'Y') && ((int)$noOfDays >= (int)$policy->minHandoverDays);

        return array(
            'required' => $required,
            'policy' => $policy
        );
    }
}
?>

