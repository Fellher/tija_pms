<?php
/**
 * Leave Accumulation Policy Management Class
 * Handles all operations related to leave accumulation policies, rules, and calculations
 */

class AccumulationPolicy {

    // ============================================================================
    // POLICY MANAGEMENT METHODS
    // ============================================================================

    /**
     * Get all accumulation policies for an entity
     *
     * @param int $entityID Entity ID
     * @param bool $activeOnly Whether to return only active policies
     * @param object $DBConn Database connection object
     * @param string $scope Policy scope filter (Global, Entity, Cadre, or null for all)
     * @return array Array of policy objects
     */
    public static function get_policies($entityID = null, $activeOnly = true, $DBConn, $scope = null) {
        $where = "";
        $params = array();

        if ($entityID) {
            $where .= " WHERE p.entityID = ?";
            $params[] = array($entityID, 'i');
        }

        if ($scope) {
            $where .= ($where ? " AND" : " WHERE") . " p.policyScope = ?";
            $params[] = array($scope, 's');
        }

        if ($activeOnly) {
            $where .= ($where ? " AND" : " WHERE") . " p.isActive = 'Y' AND p.Lapsed = 'N' AND p.Suspended = 'N'";
        }

        $sql = "SELECT p.*,
                lt.leaveTypeName, lt.leaveTypeDescription,
                e.entityName,
                jc.jobCategoryTitle as jobCategoryName,
                jb.jobBandTitle as jobBandName
                FROM tija_leave_accumulation_policies p
                LEFT JOIN tija_leave_types lt ON p.leaveTypeID = lt.leaveTypeID
                LEFT JOIN tija_entities e ON p.entityID = e.entityID
                LEFT JOIN tija_job_categories jc ON p.jobCategoryID = jc.jobCategoryID
                LEFT JOIN tija_job_bands jb ON p.jobBandID = jb.jobBandID
                $where
                ORDER BY p.policyScope, p.entityID, p.policyName, p.priority";

        return $DBConn->fetch_all_rows($sql, $params);
    }

    /**
     * Get policies filtered by scope
     *
     * @param int $entityID Entity ID (required for Entity and Cadre scope)
     * @param string $scope Policy scope (Global, Entity, Cadre)
     * @param int $jobCategoryID Job category ID (for Cadre scope)
     * @param int $jobBandID Job band ID (for Cadre scope)
     * @param bool $activeOnly Whether to return only active policies
     * @param object $DBConn Database connection object
     * @return array Array of policy objects
     */
    public static function get_policies_by_scope($entityID, $scope, $jobCategoryID = null, $jobBandID = null, $activeOnly = true, $DBConn) {
        $where = " WHERE p.policyScope = ?";
        $params = array(array($scope, 's'));

        if ($scope === 'Global') {
            // Global policies: parentEntityID = 0 or entityParentID = 0
            $where .= " AND (p.parentEntityID = 0 OR p.parentEntityID IS NULL)";
        } elseif ($scope === 'Entity') {
            // Entity policies: specific entityID
            if ($entityID) {
                $where .= " AND p.entityID = ?";
                $params[] = array($entityID, 'i');
            }
        } elseif ($scope === 'Cadre') {
            // Cadre policies: entityID + jobCategoryID or jobBandID
            if ($entityID) {
                $where .= " AND p.entityID = ?";
                $params[] = array($entityID, 'i');
            }
            if ($jobCategoryID) {
                $where .= " AND p.jobCategoryID = ?";
                $params[] = array($jobCategoryID, 'i');
            }
            if ($jobBandID) {
                $where .= " AND p.jobBandID = ?";
                $params[] = array($jobBandID, 'i');
            }
        }

        if ($activeOnly) {
            $where .= " AND p.isActive = 'Y' AND p.Lapsed = 'N' AND p.Suspended = 'N'";
        }

        $sql = "SELECT p.*, lt.leaveTypeName, lt.leaveTypeDescription
                FROM tija_leave_accumulation_policies p
                LEFT JOIN tija_leave_types lt ON p.leaveTypeID = lt.leaveTypeID
                $where
                ORDER BY p.priority, p.policyName";

        return $DBConn->fetch_all_rows($sql, $params);
    }

    /**
     * Get a specific policy by ID
     *
     * @param int $policyID Policy ID
     * @param object $DBConn Database connection object
     * @return mixed Policy object or false if not found
     */
    public static function get_policy($policyID, $DBConn) {
        $sql = "SELECT p.*,
                lt.leaveTypeName, lt.leaveTypeDescription,
                e.entityName,
                jc.jobCategoryTitle as jobCategoryName,
                jb.jobBandTitle as jobBandName
                FROM tija_leave_accumulation_policies p
                LEFT JOIN tija_leave_types lt ON p.leaveTypeID = lt.leaveTypeID
                LEFT JOIN tija_entities e ON p.entityID = e.entityID
                LEFT JOIN tija_job_categories jc ON p.jobCategoryID = jc.jobCategoryID
                LEFT JOIN tija_job_bands jb ON p.jobBandID = jb.jobBandID
                WHERE p.policyID = ?";

        $params = array(array($policyID, 'i'));
        $rows = $DBConn->fetch_all_rows($sql, $params);

        return ($rows && count($rows) > 0) ? $rows[0] : false;
    }

    /**
     * Create a new policy
     *
     * @param array $policyData Policy data
     * @param object $DBConn Database connection object
     * @return mixed Policy ID on success, false on failure
     */
    public static function create_policy($policyData, $DBConn) {
        $policyScope = $policyData['policyScope'] ?? 'Entity';

        // Handle scope-specific fields
        if ($policyScope === 'Global') {
            // Global: entityID = NULL, parentEntityID = 0
            $entityID = null;
            $parentEntityID = 0;
            $jobCategoryID = null;
            $jobBandID = null;
        } elseif ($policyScope === 'Entity') {
            // Entity: entityID required, parentEntityID = NULL
            $entityID = $policyData['entityID'] ?? 1;
            $parentEntityID = null;
            $jobCategoryID = null;
            $jobBandID = null;
        } elseif ($policyScope === 'Cadre') {
            // Cadre: entityID required, jobCategoryID or jobBandID required
            $entityID = $policyData['entityID'] ?? 1;
            $parentEntityID = null;
            $jobCategoryID = isset($policyData['jobCategoryID']) && $policyData['jobCategoryID'] ? (int)$policyData['jobCategoryID'] : null;
            $jobBandID = isset($policyData['jobBandID']) && $policyData['jobBandID'] ? (int)$policyData['jobBandID'] : null;
        } else {
            // Default to Entity scope
            $entityID = $policyData['entityID'] ?? 1;
            $parentEntityID = null;
            $jobCategoryID = null;
            $jobBandID = null;
        }

        $insertData = array(
            'entityID' => $entityID,
            'policyScope' => $policyScope,
            'parentEntityID' => $parentEntityID,
            'policyName' => $policyData['policyName'] ?? '',
            'policyDescription' => $policyData['policyDescription'] ?? null,
            'leaveTypeID' => $policyData['leaveTypeID'] ?? '',
            'jobCategoryID' => $jobCategoryID,
            'jobBandID' => $jobBandID,
            'accrualType' => $policyData['accrualType'] ?? 'Monthly',
            'accrualRate' => $policyData['accrualRate'] ?? 0,
            'maxCarryover' => $policyData['maxCarryover'] ?? null,
            'carryoverExpiryMonths' => $policyData['carryoverExpiryMonths'] ?? null,
            'proRated' => $policyData['proRated'] ?? 'N',
            'isActive' => $policyData['isActive'] ?? 'Y',
            'priority' => $policyData['priority'] ?? 1,
            'Suspended' => $policyData['Suspended'] ?? 'N',
            'DateAdded' => date('Y-m-d H:i:s'),
            'LastUpdate' => date('Y-m-d H:i:s'),
            'LastUpdateByID' => $policyData['LastUpdateByID'] ?? null
        );

        $policyID = $DBConn->insert_data('tija_leave_accumulation_policies', $insertData);

        return $policyID ? $policyID : false;
    }

    /**
     * Update an existing policy
     *
     * @param int $policyID Policy ID
     * @param array $policyData Updated policy data
     * @param object $DBConn Database connection object
     * @return bool Success status
     */
    public static function update_policy($policyID, $policyData, $DBConn) {
        $policyData['LastUpdate'] = date('Y-m-d H:i:s');

        $where = array('policyID' => $policyID);
        // Try update_data first, fallback to update_table
        if (method_exists($DBConn, 'update_data')) {
            $result = $DBConn->update_data('tija_leave_accumulation_policies', $policyData, $where);
        } else {
            $result = $DBConn->update_table('tija_leave_accumulation_policies', $policyData, $where);
        }
        return $result;
    }

    /**
     * Delete a policy (soft delete)
     *
     * @param int $policyID Policy ID
     * @param int $deletedByID User ID of the person deleting
     * @param object $DBConn Database connection object
     * @return bool Success status
     */
    public static function delete_policy($policyID, $deletedByID, $DBConn) {
        $updateData = array(
            'Lapsed' => 'Y',
            'LastUpdate' => date('Y-m-d H:i:s'),
            'LastUpdateByID' => $deletedByID
        );

        $where = array('policyID' => $policyID);
        // Try update_data first, fallback to update_table
        if (method_exists($DBConn, 'update_data')) {
            $result = $DBConn->update_data('tija_leave_accumulation_policies', $updateData, $where);
        } else {
            $result = $DBConn->update_table('tija_leave_accumulation_policies', $updateData, $where);
        }
        return $result;
    }

    // ============================================================================
    // RULE MANAGEMENT METHODS
    // ============================================================================

    /**
     * Get rules for a specific policy
     *
     * @param int $policyID Policy ID
     * @param bool $activeOnly Whether to return only active rules
     * @param object $DBConn Database connection object
     * @return array Array of rule objects
     */
    public static function get_policy_rules($policyID, $activeOnly = true, $DBConn) {
        $where = " WHERE rule.policyID = ?";
        $params = array(array($policyID, 'i'));

        if ($activeOnly) {
            $where .= " AND rule.Lapsed = 'N' AND rule.Suspended = 'N'";
        }

        $sql = "SELECT rule.*
                FROM tija_leave_accumulation_rules rule
                $where
                ORDER BY rule.ruleID";

        return $DBConn->fetch_all_rows($sql, $params);
    }

    /**
     * Create a new rule
     *
     * @param array $ruleData Rule data
     * @param object $DBConn Database connection object
     * @return mixed Rule ID on success, false on failure
     */
    public static function create_rule($ruleData, $DBConn) {
        $required = ['policyID', 'ruleName', 'ruleType'];
        foreach ($required as $field) {
            if (!isset($ruleData[$field]) || empty($ruleData[$field])) {
                throw new Exception("Required field missing: $field");
            }
        }

        $insertData = array(
            'policyID' => $ruleData['policyID'],
            'ruleName' => $ruleData['ruleName'],
            'ruleType' => $ruleData['ruleType'],
            'conditionField' => $ruleData['conditionField'] ?? null,
            'conditionOperator' => $ruleData['conditionOperator'] ?? '>=',
            'conditionValue' => $ruleData['conditionValue'] ?? null,
            'accrualMultiplier' => $ruleData['accrualMultiplier'] ?? 1.00,
            'DateAdded' => date('Y-m-d H:i:s'),
            'LastUpdate' => date('Y-m-d H:i:s'),
            'LastUpdateByID' => $ruleData['LastUpdateByID'] ?? null
        );

        return $DBConn->insert_data('tija_leave_accumulation_rules', $insertData);
    }

    /**
     * Delete a rule (soft delete)
     *
     * @param int $ruleID Rule ID
     * @param int $deletedByID User ID of the person deleting
     * @param object $DBConn Database connection object
     * @return bool Success status
     */
    public static function delete_rule($ruleID, $deletedByID, $DBConn) {
        $updateData = array(
            'Lapsed' => 'Y',
            'LastUpdate' => date('Y-m-d H:i:s'),
            'LastUpdateByID' => $deletedByID
        );

        $where = array('ruleID' => $ruleID);
        return $DBConn->update_data('tija_leave_accumulation_rules', $updateData, $where);
    }

    // ============================================================================
    // CALCULATION METHODS
    // ============================================================================

    /**
     * Calculate accrual for a specific employee and policy
     *
     * @param int $employeeID Employee ID
     * @param int $policyID Policy ID
     * @param string $period Period to calculate (YYYY-MM format)
     * @param object $DBConn Database connection object
     * @return array Calculation result
     */
    public static function calculate_employee_accrual($employeeID, $policyID, $period, $DBConn) {
        // Get policy details
        $policy = self::get_policy($policyID, $DBConn);
        if (!$policy) {
            throw new Exception("Policy not found: $policyID");
        }

        // Get employee details for rule evaluation
        $employeeDetails = self::get_employee_details_for_calculation($employeeID, $DBConn);

        // Get applicable rules
        $rules = self::get_policy_rules($policyID, true, $DBConn);

        // Get accrual type and related fields
        $accrualType = isset($policy->accrualType) ? $policy->accrualType : 'Monthly';
        $baseAccrual = isset($policy->accrualRate) ? (float)$policy->accrualRate : 0;

        // Apply rules to get multiplier
        $multiplier = 1.0;
        $appliedRules = array();
        foreach ($rules as $rule) {
            if (self::evaluate_rule($rule, $employeeDetails)) {
                $multiplier *= (float)($rule->accrualMultiplier ?? 1.0);
                $appliedRules[] = $rule;
            }
        }

        // Calculate final accrual based on type
        $finalAccrual = $baseAccrual * $multiplier;

        // Get current balance
        $currentBalance = self::get_employee_leave_balance($employeeID, $policy->leaveTypeID, $DBConn);

        // Get carryover amount
        $carryoverAmount = self::calculate_carryover($employeeID, $policyID, $period, $DBConn);

        return array(
            'employeeID' => $employeeID,
            'policyID' => $policyID,
            'period' => $period,
            'accrualType' => $accrualType,
            'baseAccrual' => $baseAccrual,
            'multiplier' => $multiplier,
            'finalAccrual' => $finalAccrual,
            'currentBalance' => $currentBalance,
            'carryoverAmount' => $carryoverAmount,
            'totalBalance' => $currentBalance + $finalAccrual + $carryoverAmount,
            'appliedRules' => $appliedRules,
            'calculationDate' => date('Y-m-d')
        );
    }

    /**
     * Get employee details for calculation
     */
    private static function get_employee_details_for_calculation($employeeID, $DBConn) {
        // This would fetch employee data needed for rule evaluation
        // Placeholder implementation
        return array('employeeID' => $employeeID);
    }

    /**
     * Evaluate a rule against employee details
     */
    private static function evaluate_rule($rule, $employeeDetails) {
        // Placeholder - implement rule evaluation logic
        return true;
    }

    /**
     * Get employee leave balance
     */
    private static function get_employee_leave_balance($employeeID, $leaveTypeID, $DBConn) {
        // Placeholder - implement balance calculation
        return 0;
    }

    /**
     * Calculate carryover amount
     */
    private static function calculate_carryover($employeeID, $policyID, $period, $DBConn) {
        $policy = self::get_policy($policyID, $DBConn);
        if (!$policy->maxCarryover) {
            return 0; // No carryover limit
        }

        // Get previous period balance
        $prevPeriod = date('Y-m', strtotime($period . '-01 -1 month'));

        $sql = "SELECT totalBalance
                FROM tija_leave_accumulation_history
                WHERE employeeID = ?
                AND policyID = ?
                AND accrualPeriod = ?
                ORDER BY accrualDate DESC
                LIMIT 1";

        $params = array(
            array($employeeID, 'i'),
            array($policyID, 'i'),
            array($prevPeriod, 's')
        );

        $rows = $DBConn->fetch_all_rows($sql, $params);
        $prevBalance = ($rows && count($rows) > 0) ? $rows[0]['totalBalance'] : 0;

        return min($prevBalance, $policy->maxCarryover);
    }

    // ============================================================================
    // HISTORY AND REPORTING METHODS
    // ============================================================================

    /**
     * Record an accrual in the history
     *
     * @param array $accrualData Accrual data
     * @param object $DBConn Database connection object
     * @return mixed History ID on success, false on failure
     */
    public static function record_accrual($accrualData, $DBConn) {
        $insertData = array(
            'employeeID' => $accrualData['employeeID'],
            'policyID' => $accrualData['policyID'],
            'ruleID' => $accrualData['ruleID'] ?? null,
            'leaveTypeID' => $accrualData['leaveTypeID'],
            'accrualPeriod' => $accrualData['period'],
            'accrualDate' => $accrualData['calculationDate'],
            'baseAccrualRate' => $accrualData['baseAccrual'],
            'appliedMultiplier' => $accrualData['multiplier'],
            'finalAccrualAmount' => $accrualData['finalAccrual'],
            'carryoverAmount' => $accrualData['carryoverAmount'],
            'totalBalance' => $accrualData['totalBalance'],
            'calculationNotes' => $accrualData['calculationNotes'] ?? null,
            'DateAdded' => date('Y-m-d H:i:s'),
            'LastUpdate' => date('Y-m-d H:i:s'),
            'LastUpdateByID' => $accrualData['LastUpdateByID'] ?? null
        );

        return $DBConn->insert_data('tija_leave_accumulation_history', $insertData);
    }

    /**
     * Get accumulation history for an employee
     *
     * @param int $employeeID Employee ID
     * @param int $leaveTypeID Leave type ID (optional)
     * @param string $startDate Start date (optional)
     * @param string $endDate End date (optional)
     * @param object $DBConn Database connection object
     * @return array Array of history records
     */
    public static function get_employee_history($employeeID, $leaveTypeID = null, $startDate = null, $endDate = null, $DBConn) {
        $where = " WHERE h.employeeID = ?";
        $params = array(array($employeeID, 'i'));

        if ($leaveTypeID) {
            $where .= " AND h.leaveTypeID = ?";
            $params[] = array($leaveTypeID, 'i');
        }

        if ($startDate) {
            $where .= " AND h.accrualDate >= ?";
            $params[] = array($startDate, 's');
        }

        if ($endDate) {
            $where .= " AND h.accrualDate <= ?";
            $params[] = array($endDate, 's');
        }

        $sql = "SELECT h.*, p.policyName, lt.leaveTypeName
                FROM tija_leave_accumulation_history h
                LEFT JOIN tija_leave_accumulation_policies p ON h.policyID = p.policyID
                LEFT JOIN tija_leave_types lt ON h.leaveTypeID = lt.leaveTypeID
                $where
                ORDER BY h.accrualDate DESC, h.accrualPeriod DESC";

        return $DBConn->fetch_all_rows($sql, $params);
    }

    /**
     * Get accumulation statistics for reporting
     *
     * @param int $entityID Entity ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @param object $DBConn Database connection object
     * @return array Statistics data
     */
    public static function get_accumulation_statistics($entityID, $startDate, $endDate, $DBConn) {
        $sql = "SELECT
                    p.policyName,
                    lt.leaveTypeName,
                    COUNT(DISTINCT h.employeeID) as employeeCount,
                    SUM(h.finalAccrualAmount) as totalAccrued,
                    AVG(h.finalAccrualAmount) as avgAccrual,
                    SUM(h.carryoverAmount) as totalCarryover,
                    AVG(h.totalBalance) as avgBalance
                FROM tija_leave_accumulation_history h
                LEFT JOIN tija_leave_accumulation_policies p ON h.policyID = p.policyID
                LEFT JOIN tija_leave_types lt ON h.leaveTypeID = lt.leaveTypeID
                WHERE p.entityID = ?
                AND h.accrualDate >= ?
                AND h.accrualDate <= ?
                GROUP BY p.policyID, lt.leaveTypeID
                ORDER BY p.policyName, lt.leaveTypeName";

        $params = array(
            array($entityID, 'i'),
            array($startDate, 's'),
            array($endDate, 's')
        );

        return $DBConn->fetch_all_rows($sql, $params);
    }

    // ============================================================================
    // VALIDATION METHODS
    // ============================================================================

    /**
     * Validate policy data
     *
     * @param array $policyData Policy data
     * @return array Validation errors (empty if valid)
     */
    public static function validate_policy($policyData) {
        $errors = array();

        // Required fields
        if (empty($policyData['policyName'])) {
            $errors[] = 'Policy name is required';
        }
        if (empty($policyData['leaveTypeID'])) {
            $errors[] = 'Leave type is required';
        }
        if (empty($policyData['accrualType'])) {
            $errors[] = 'Accrual type is required';
        }
        if (!isset($policyData['accrualRate']) || $policyData['accrualRate'] <= 0) {
            $errors[] = 'Accrual rate must be greater than 0';
        }

        // Scope-specific validation
        $policyScope = $policyData['policyScope'] ?? 'Entity';

        if ($policyScope === 'Global') {
            // Global: entityID should be NULL, parentEntityID should be 0
            if (isset($policyData['entityID']) && $policyData['entityID'] !== null) {
                $errors[] = 'Global policies must have entityID set to NULL';
            }
        } elseif ($policyScope === 'Entity') {
            // Entity: entityID required
            if (empty($policyData['entityID'])) {
                $errors[] = 'Entity ID is required for Entity scope policies';
            }
            if (!empty($policyData['jobCategoryID']) || !empty($policyData['jobBandID'])) {
                $errors[] = 'Entity scope policies cannot have jobCategoryID or jobBandID';
            }
        } elseif ($policyScope === 'Cadre') {
            // Cadre: entityID required, jobCategoryID OR jobBandID required
            if (empty($policyData['entityID'])) {
                $errors[] = 'Entity ID is required for Cadre scope policies';
            }
            if (empty($policyData['jobCategoryID']) && empty($policyData['jobBandID'])) {
                $errors[] = 'Cadre scope policies require either jobCategoryID or jobBandID';
            }
        }

        return $errors;
    }

    /**
     * Validate rule data
     *
     * @param array $ruleData Rule data
     * @return array Validation errors (empty if valid)
     */
    public static function validate_rule($ruleData) {
        $errors = array();

        if (empty($ruleData['ruleName'])) {
            $errors[] = 'Rule name is required';
        }
        if (empty($ruleData['ruleType'])) {
            $errors[] = 'Rule type is required';
        }
        if (empty($ruleData['policyID'])) {
            $errors[] = 'Policy ID is required';
        }

        return $errors;
    }

    // ============================================================================
    // HISTORICAL REPORTING METHODS
    // ============================================================================

    /**
     * Get policy snapshot data for historical reporting
     * Returns the policy state as it was at a specific date
     *
     * @param int $policyID Policy ID
     * @param string $asOfDate Date to get policy state (Y-m-d format)
     * @param object $DBConn Database connection object
     * @return array Policy snapshot data with historical context
     */
    public static function get_policy_snapshot($policyID, $asOfDate, $DBConn) {
        // Get current policy
        $currentPolicy = self::get_policy($policyID, $DBConn);
        if (!$currentPolicy) {
            return false;
        }

        // Get all accrual history records for this policy up to the date
        $sql = "SELECT
                    MIN(accrualDate) as firstAccrualDate,
                    MAX(accrualDate) as lastAccrualDate,
                    MIN(baseAccrualRate) as minRate,
                    MAX(baseAccrualRate) as maxRate,
                    AVG(baseAccrualRate) as avgRate,
                    COUNT(DISTINCT accrualPeriod) as periodCount
                FROM tija_leave_accumulation_history
                WHERE policyID = ?
                AND accrualDate <= ?
                GROUP BY policyID";

        $params = array(
            array($policyID, 'i'),
            array($asOfDate, 's')
        );

        $historyStats = $DBConn->fetch_all_rows($sql, $params);

        // Get the most recent history record before or on the date to see what rate was actually used
        $sql = "SELECT
                    baseAccrualRate,
                    appliedMultiplier,
                    finalAccrualAmount,
                    accrualDate,
                    accrualPeriod
                FROM tija_leave_accumulation_history
                WHERE policyID = ?
                AND accrualDate <= ?
                ORDER BY accrualDate DESC, historyID DESC
                LIMIT 1";

        $lastAccrual = $DBConn->fetch_all_rows($sql, $params);

        return array(
            'policyID' => $policyID,
            'policyName' => $currentPolicy->policyName,
            'asOfDate' => $asOfDate,
            'currentPolicy' => $currentPolicy,
            'historicalStats' => $historyStats[0] ?? null,
            'lastAccrualUsed' => $lastAccrual[0] ?? null,
            'note' => 'Policy may have changed since this date. Historical accruals used the rates stored in history table.'
        );
    }

    // ============================================================================
    // POLICY HIERARCHY AND RESOLUTION METHODS
    // ============================================================================

    /**
     * Merge policies with precedence (cadre > entity > global)
     *
     * @param mixed $globalPolicy Global policy object or null
     * @param mixed $entityPolicy Entity policy object or null
     * @param mixed $cadrePolicy Cadre policy object or null
     * @return mixed Merged policy object
     */
    public static function merge_policies($globalPolicy, $entityPolicy, $cadrePolicy) {
        // Start with global policy as base
        $merged = $globalPolicy ? (array)$globalPolicy : array();

        // Apply entity overrides
        if ($entityPolicy) {
            $entityArray = (array)$entityPolicy;
            foreach ($entityArray as $key => $value) {
                // Skip null values and scope-related fields
                if ($value !== null && !in_array($key, ['policyScope', 'parentEntityID', 'jobCategoryID', 'jobBandID'])) {
                    $merged[$key] = $value;
                }
            }
        }

        // Apply cadre overrides (highest precedence)
        if ($cadrePolicy) {
            $cadreArray = (array)$cadrePolicy;
            foreach ($cadreArray as $key => $value) {
                // Skip null values and scope-related fields
                if ($value !== null && !in_array($key, ['policyScope', 'parentEntityID', 'jobCategoryID', 'jobBandID'])) {
                    $merged[$key] = $value;
                }
            }
        }

        // Convert back to object
        return (object)$merged;
    }

    /**
     * Resolve policy hierarchy for a specific employee
     *
     * @param int $employeeID Employee ID
     * @param int $leaveTypeID Leave type ID
     * @param int $entityID Entity ID
     * @param object $DBConn Database connection object
     * @return mixed Resolved policy object or false if not found
     */
    public static function resolve_policy_for_employee($employeeID, $leaveTypeID, $entityID, $DBConn) {
        // Get employee details
        $employee = Employee::employees(array('ID' => $employeeID), true, $DBConn);
        if (!$employee) {
            return false;
        }

        $employeeEntityID = $employee->entityID ?? $entityID;
        $jobCategoryID = $employee->jobCategoryID ?? null;
        $jobBandID = $employee->jobBandID ?? null;

        // Get parent entity ID for global policies
        $parentEntityID = 0;
        if ($employeeEntityID) {
            $entitySql = "SELECT entityParentID FROM tija_entities WHERE entityID = ?";
            $entityParams = array(array($employeeEntityID, 'i'));
            $entityRows = $DBConn->fetch_all_rows($entitySql, $entityParams);
            if ($entityRows && count($entityRows) > 0) {
                $parentEntityID = $entityRows[0]['entityParentID'] ?? 0;
            }
        }

        // Fetch policies in hierarchy order
        $globalPolicy = null;
        $entityPolicy = null;
        $cadrePolicy = null;

        // 1. Get global policy (parentEntityID = 0)
        $globalSql = "SELECT p.*, lt.leaveTypeName, lt.leaveTypeDescription
                     FROM tija_leave_accumulation_policies p
                     LEFT JOIN tija_leave_types lt ON p.leaveTypeID = lt.leaveTypeID
                     WHERE p.policyScope = 'Global'
                     AND p.leaveTypeID = ?
                     AND (p.parentEntityID = 0 OR p.parentEntityID IS NULL)
                     AND p.isActive = 'Y' AND p.Lapsed = 'N' AND p.Suspended = 'N'
                     ORDER BY p.priority
                     LIMIT 1";
        $globalParams = array(array($leaveTypeID, 'i'));
        $globalRows = $DBConn->fetch_all_rows($globalSql, $globalParams);
        if ($globalRows && count($globalRows) > 0) {
            $globalPolicy = $globalRows[0];
        }

        // 2. Get entity policy
        if ($employeeEntityID) {
            $entitySql = "SELECT p.*, lt.leaveTypeName, lt.leaveTypeDescription
                         FROM tija_leave_accumulation_policies p
                         LEFT JOIN tija_leave_types lt ON p.leaveTypeID = lt.leaveTypeID
                         WHERE p.policyScope = 'Entity'
                         AND p.entityID = ?
                         AND p.leaveTypeID = ?
                         AND p.isActive = 'Y' AND p.Lapsed = 'N' AND p.Suspended = 'N'
                         ORDER BY p.priority
                         LIMIT 1";
            $entityParams = array(
                array($employeeEntityID, 'i'),
                array($leaveTypeID, 'i')
            );
            $entityRows = $DBConn->fetch_all_rows($entitySql, $entityParams);
            if ($entityRows && count($entityRows) > 0) {
                $entityPolicy = $entityRows[0];
            }
        }

        // 3. Get cadre policy (jobCategoryID or jobBandID match)
        if ($employeeEntityID && ($jobCategoryID || $jobBandID)) {
            $cadreWhere = "p.policyScope = 'Cadre' AND p.entityID = ? AND p.leaveTypeID = ?";
            $cadreParams = array(
                array($employeeEntityID, 'i'),
                array($leaveTypeID, 'i')
            );

            if ($jobCategoryID) {
                $cadreWhere .= " AND p.jobCategoryID = ?";
                $cadreParams[] = array($jobCategoryID, 'i');
            }
            if ($jobBandID) {
                $cadreWhere .= " AND p.jobBandID = ?";
                $cadreParams[] = array($jobBandID, 'i');
            }

            $cadreSql = "SELECT p.*, lt.leaveTypeName, lt.leaveTypeDescription
                        FROM tija_leave_accumulation_policies p
                        LEFT JOIN tija_leave_types lt ON p.leaveTypeID = lt.leaveTypeID
                        WHERE $cadreWhere
                        AND p.isActive = 'Y' AND p.Lapsed = 'N' AND p.Suspended = 'N'
                        ORDER BY p.priority
                        LIMIT 1";
            $cadreRows = $DBConn->fetch_all_rows($cadreSql, $cadreParams);
            if ($cadreRows && count($cadreRows) > 0) {
                $cadrePolicy = $cadreRows[0];
            }
        }

        // Merge policies with precedence
        $mergedPolicy = self::merge_policies($globalPolicy, $entityPolicy, $cadrePolicy);

        // If no policy found at any level, return false
        if (empty($mergedPolicy) || (is_object($mergedPolicy) && !isset($mergedPolicy->policyID))) {
            return false;
        }

        return $mergedPolicy;
    }

    /**
     * Get effective policy for an employee (alias for resolve_policy_for_employee)
     *
     * @param int $employeeID Employee ID
     * @param int $leaveTypeID Leave type ID
     * @param int $entityID Entity ID
     * @param object $DBConn Database connection object
     * @return mixed Effective policy object or false if not found
     */
    public static function get_effective_policy($employeeID, $leaveTypeID, $entityID, $DBConn) {
        return self::resolve_policy_for_employee($employeeID, $leaveTypeID, $entityID, $DBConn);
    }

    /**
     * Get accrual history with policy context for reporting
     * Shows what policy rules were in effect at the time of each accrual
     *
     * @param int $employeeID Employee ID
     * @param int $policyID Policy ID (optional)
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @param object $DBConn Database connection object
     * @return array History records with policy context
     */
    public static function get_history_with_policy_context($employeeID, $policyID = null, $startDate = null, $endDate = null, $DBConn) {
        $where = " WHERE h.employeeID = ?";
        $params = array(array($employeeID, 'i'));

        if ($policyID) {
            $where .= " AND h.policyID = ?";
            $params[] = array($policyID, 'i');
        }

        if ($startDate) {
            $where .= " AND h.accrualDate >= ?";
            $params[] = array($startDate, 's');
        }

        if ($endDate) {
            $where .= " AND h.accrualDate <= ?";
            $params[] = array($endDate, 's');
        }

        // Get history with policy details - note that policy details shown are CURRENT, not historical
        // The actual rates used are stored in baseAccrualRate, appliedMultiplier, finalAccrualAmount
        $sql = "SELECT
                    h.*,
                    p.policyName as currentPolicyName,
                    p.accrualType as currentAccrualType,
                    p.accrualRate as currentAccrualRate,
                    p.maxCarryover as currentMaxCarryover,
                    lt.leaveTypeName,
                    -- Historical values (what was actually used)
                    h.baseAccrualRate as historicalRate,
                    h.appliedMultiplier as historicalMultiplier,
                    h.finalAccrualAmount as historicalFinalAmount,
                    -- Calculate if policy has changed since this accrual
                    CASE
                        WHEN ABS(h.baseAccrualRate - p.accrualRate) > 0.01 THEN 'Y'
                        ELSE 'N'
                    END as policyChanged
                FROM tija_leave_accumulation_history h
                LEFT JOIN tija_leave_accumulation_policies p ON h.policyID = p.policyID
                LEFT JOIN tija_leave_types lt ON h.leaveTypeID = lt.leaveTypeID
                $where
                ORDER BY h.accrualDate DESC, h.accrualPeriod DESC";

        return $DBConn->fetch_all_rows($sql, $params);
    }

    /**
     * Generate policy change report
     * Shows when policy changed and impact on accruals
     *
     * @param int $policyID Policy ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @param object $DBConn Database connection object
     * @return array Report data
     */
    public static function get_policy_change_report($policyID, $startDate, $endDate, $DBConn) {
        // Get policy change history (using LastUpdate timestamps)
        $policy = self::get_policy($policyID, $DBConn);

        // Get accrual history grouped by date ranges to detect rate changes
        $sql = "SELECT
                    DATE_FORMAT(accrualDate, '%Y-%m') as accrualMonth,
                    MIN(baseAccrualRate) as minRate,
                    MAX(baseAccrualRate) as maxRate,
                    AVG(baseAccrualRate) as avgRate,
                    COUNT(*) as accrualCount,
                    SUM(finalAccrualAmount) as totalAccrued
                FROM tija_leave_accumulation_history
                WHERE policyID = ?
                AND accrualDate >= ?
                AND accrualDate <= ?
                GROUP BY DATE_FORMAT(accrualDate, '%Y-%m')
                ORDER BY accrualMonth";

        $params = array(
            array($policyID, 'i'),
            array($startDate, 's'),
            array($endDate, 's')
        );

        $monthlyStats = $DBConn->fetch_all_rows($sql, $params);

        // Detect rate changes between months
        $rateChanges = array();
        $prevRate = null;
        foreach ($monthlyStats as $month) {
            if ($prevRate !== null && abs($month['avgRate'] - $prevRate) > 0.01) {
                $rateChanges[] = array(
                    'month' => $month['accrualMonth'],
                    'oldRate' => $prevRate,
                    'newRate' => $month['avgRate'],
                    'change' => $month['avgRate'] - $prevRate
                );
            }
            $prevRate = $month['avgRate'];
        }

        return array(
            'policyID' => $policyID,
            'policyName' => $policy->policyName ?? 'Unknown',
            'currentRate' => $policy->accrualRate ?? 0,
            'reportPeriod' => array('start' => $startDate, 'end' => $endDate),
            'monthlyStats' => $monthlyStats,
            'detectedRateChanges' => $rateChanges,
            'policyLastUpdated' => $policy->LastUpdate ?? null,
            'note' => 'Rate changes detected by comparing historical accrual rates. Actual policy changes may have occurred on different dates.'
        );
    }
}
