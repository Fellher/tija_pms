<?php
/**
 * Employee Profile Management Class
 *
 * This class handles all operations related to the comprehensive employee profile
 * management system including personal details, employment info, compensation,
 * contacts, qualifications, and benefits.
 *
 * @package    Tija CRM
 * @subpackage Employee Management
 * @version    1.0
 * @created    2025-10-15
 */

class EmployeeProfile {

    // ===================================================================
    // 1. PERSONAL DETAILS METHODS
    // ===================================================================

    /**
     * Get employee personal details
     *
     * @param array $params Filter parameters
     * @param bool $single Return single record
     * @param mysqli $DBConn Database connection
     * @return mixed Employee personal details
     */
    public static function get_personal_details($params = [], $single = false, $DBConn = null) {
        global $config;

        if (!$DBConn) {
            return null;
        }

        $sql = "SELECT
                    pd.*,
                    p.prefixName,
                    CONCAT_WS(' ', pd.firstName, pd.middleName, pd.lastName) as fullName,
                    TIMESTAMPDIFF(YEAR, pd.dateOfBirth, CURDATE()) as age
                FROM tija_employee_personal_details pd
                LEFT JOIN tija_prefixes p ON pd.prefixID = p.prefixID
                WHERE 1=1";

        $whereClause = self::build_where_clause($params);
        $sql .= $whereClause;
        $sql .= " ORDER BY pd.lastName, pd.firstName";

        if ($single) {
            $sql .= " LIMIT 1";
        }

        $result = $DBConn->query($sql);

        if (!$result) {
            error_log("Error fetching personal details: " . $DBConn->error);
            return null;
        }

        if ($single) {
            return $result->fetch_object();
        }

        $details = [];
        while ($row = $result->fetch_object()) {
            $details[] = $row;
        }

        return empty($details) ? null : $details;
    }

    /**
     * Save or update employee personal details
     *
     * @param array $data Personal details data
     * @param mysqli $DBConn Database connection
     * @return array Result with success status and message
     */
    public static function save_personal_details($data, $DBConn = null) {
        if (!$DBConn) {
            return ['success' => false, 'message' => 'Database connection required'];
        }

        $employeeID = $data['employeeID'] ?? null;

        if (!$employeeID) {
            return ['success' => false, 'message' => 'Employee ID is required'];
        }

        // Check if record exists
        $existing = self::get_personal_details(['employeeID' => $employeeID], true, $DBConn);

        $fields = [
            'prefixID', 'firstName', 'middleName', 'lastName', 'maidenName',
            'dateOfBirth', 'gender', 'maritalStatus', 'nationality', 'nationalID',
            'passportNumber', 'passportIssueDate', 'passportExpiryDate', 'taxIDNumber',
            'nhifNumber', 'nssfNumber', 'bloodGroup', 'religion', 'ethnicity',
            'languagesSpoken', 'disabilities', 'profileImage', 'updatedBy', 'Suspended'
        ];

        if ($existing) {
            // Update existing record
            $updates = [];
            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $value = self::escape_value($data[$field], $DBConn);
                    $updates[] = "`$field` = $value";
                }
            }

            if (empty($updates)) {
                return ['success' => false, 'message' => 'No data to update'];
            }

            $sql = "UPDATE tija_employee_personal_details
                    SET " . implode(', ', $updates) . "
                    WHERE employeeID = " . (int)$employeeID;

            if ($DBConn->query($sql)) {
                return [
                    'success' => true,
                    'message' => 'Personal details updated successfully',
                    'id' => $existing->personalDetailID
                ];
            } else {
                error_log("Error updating personal details: " . $DBConn->error);
                return ['success' => false, 'message' => 'Failed to update personal details'];
            }
        } else {
            // Insert new record
            $data['employeeID'] = $employeeID;
            $insertFields = ['employeeID'];
            $insertValues = [(int)$employeeID];

            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $insertFields[] = "`$field`";
                    $insertValues[] = self::escape_value($data[$field], $DBConn);
                }
            }

            $sql = "INSERT INTO tija_employee_personal_details
                    (" . implode(', ', $insertFields) . ")
                    VALUES (" . implode(', ', $insertValues) . ")";

            if ($DBConn->query($sql)) {
                return [
                    'success' => true,
                    'message' => 'Personal details created successfully',
                    'id' => $DBConn->insert_id
                ];
            } else {
                error_log("Error inserting personal details: " . $DBConn->error);
                return ['success' => false, 'message' => 'Failed to create personal details'];
            }
        }
    }

    // ===================================================================
    // 2. EMPLOYMENT DETAILS METHODS
    // ===================================================================

    /**
     * Get employee employment details
     */
    public static function get_employment_details($params = [], $single = false, $DBConn = null) {
        if (!$DBConn) {
            return null;
        }

        $sql = "SELECT
                    ed.*,
                    jt.jobTitle,
                    es.employmentStatusTitle,
                    u.unitName as departmentName
                FROM tija_employee_employment_details ed
                LEFT JOIN tija_job_titles jt ON ed.jobTitleID = jt.jobTitleID
                LEFT JOIN tija_employment_status es ON ed.employmentStatusID = es.employmentStatusID
                LEFT JOIN tija_units u ON ed.departmentID = u.unitID
                WHERE 1=1";

        $whereClause = self::build_where_clause($params, 'ed');
        $sql .= $whereClause;
        $sql .= " ORDER BY ed.employmentStartDate DESC";

        if ($single) {
            $sql .= " LIMIT 1";
        }

        $result = $DBConn->query($sql);

        if (!$result) {
            error_log("Error fetching employment details: " . $DBConn->error);
            return null;
        }

        if ($single) {
            return $result->fetch_object();
        }

        $details = [];
        while ($row = $result->fetch_object()) {
            $details[] = $row;
        }

        return empty($details) ? null : $details;
    }

    /**
     * Save employment details
     */
    public static function save_employment_details($data, $DBConn = null) {
        if (!$DBConn) {
            return ['success' => false, 'message' => 'Database connection required'];
        }

        $employeeID = $data['employeeID'] ?? null;

        if (!$employeeID) {
            return ['success' => false, 'message' => 'Employee ID is required'];
        }

        $existing = self::get_employment_details(['employeeID' => $employeeID], true, $DBConn);

        $fields = [
            'employeeNumber', 'payrollNumber', 'employmentTypeID', 'employmentStatusID',
            'jobTitleID', 'departmentID', 'divisionID', 'officeLocation', 'workSchedule',
            'employmentStartDate', 'employmentEndDate', 'probationStartDate', 'probationEndDate',
            'confirmationDate', 'contractStartDate', 'contractEndDate', 'noticePeriodDays',
            'workEmailAddress', 'workPhoneNumber', 'workExtension', 'dailyWorkHours',
            'weeklyWorkHours', 'workHourRoundingID', 'isRemote', 'allowTimelogging',
            'allowExpenseClaims', 'allowLeaveApplication', 'terminationReason', 'terminationNotes',
            'rehireEligible', 'updatedBy', 'Suspended'
        ];

        if ($existing) {
            $updates = [];
            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $value = self::escape_value($data[$field], $DBConn);
                    $updates[] = "`$field` = $value";
                }
            }

            if (empty($updates)) {
                return ['success' => false, 'message' => 'No data to update'];
            }

            $sql = "UPDATE tija_employee_employment_details
                    SET " . implode(', ', $updates) . "
                    WHERE employeeID = " . (int)$employeeID;

            if ($DBConn->query($sql)) {
                return [
                    'success' => true,
                    'message' => 'Employment details updated successfully',
                    'id' => $existing->employmentDetailID
                ];
            } else {
                error_log("Error updating employment details: " . $DBConn->error);
                return ['success' => false, 'message' => 'Failed to update employment details'];
            }
        } else {
            $data['employeeID'] = $employeeID;
            $insertFields = ['employeeID'];
            $insertValues = [(int)$employeeID];

            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $insertFields[] = "`$field`";
                    $insertValues[] = self::escape_value($data[$field], $DBConn);
                }
            }

            $sql = "INSERT INTO tija_employee_employment_details
                    (" . implode(', ', $insertFields) . ")
                    VALUES (" . implode(', ', $insertValues) . ")";

            if ($DBConn->query($sql)) {
                return [
                    'success' => true,
                    'message' => 'Employment details created successfully',
                    'id' => $DBConn->insert_id
                ];
            } else {
                error_log("Error inserting employment details: " . $DBConn->error);
                return ['success' => false, 'message' => 'Failed to create employment details'];
            }
        }
    }

    // ===================================================================
    // 3. JOB HISTORY METHODS
    // ===================================================================

    /**
     * Get employee job history
     */
    public static function get_job_history($params = [], $single = false, $DBConn = null) {
        if (!$DBConn) {
            return null;
        }

        $sql = "SELECT
                    jh.*,
                    jt.jobTitle as jobTitleName,
                    u.unitName as departmentName
                FROM tija_employee_job_history jh
                LEFT JOIN tija_job_titles jt ON jh.jobTitleID = jt.jobTitleID
                LEFT JOIN tija_units u ON jh.departmentID = u.unitID
                WHERE jh.Suspended = 'N'";

        // Add WHERE conditions
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $sql .= " AND jh.`" . $key . "` = " . (is_numeric($value) ? $value : "'" . $value . "'");
            }
        }

        $sql .= " ORDER BY jh.startDate DESC";

        if ($single) {
            $sql .= " LIMIT 1";
        }

        // Use mysqlConnect methods correctly
        $DBConn->query($sql);
        $DBConn->execute();

        if ($single) {
            $result = $DBConn->single();
            return $result ? $result : null;
        }

        $results = $DBConn->resultSet();
        // Return array even if empty, not null
        return is_array($results) ? $results : [];
    }
    public static function job_history($whereArr = [], $single = false, $DBConn = null) {
        $params=[];
        $where= '';
        $rows=array();
        $jobHistory = array('jobHistoryID', 'employeeID', 'jobTitleID', 'departmentID', 'startDate', 'endDate', 'isCurrent', 'responsibilities', 'achievements', 'reasonForChange', 'notes', 'createdBy', 'updatedBy', 'Suspended');
        $jobTitle = array('jobTitle', 'jobDescription');
        $department = array('departmentName', 'departmentID');
        $employee = array('employeeName', 'employeeID');
        $createdBy = array('createdByName', 'createdByID');
        $updatedBy = array('updatedByName', 'updatedByID');

        if (count($whereArr) > 0) {
            $i = 0;
            foreach ($whereArr as $col => $val) {
                if ($where == '') {
                    $where = "WHERE ";
                } else {
                    $where .= " AND ";
                }
                //check if the column is in the job history table
                if (in_array($col, $jobHistory)) {
                    $where .= "jh.{$col} = ?";
                } elseif (in_array($col, $jobTitle)) {
                    $where .= "jt.{$col} = ?";
                } elseif (in_array($col, $department)) {
                    $where .= "u.{$col} = ?";
                } elseif (in_array($col, $employee)) {
                    $where .= "e.{$col} = ?";
                } elseif (in_array($col, $createdBy)) {
                    $where .= "createdBy.{$col} = ?";
                } elseif (in_array($col, $updatedBy)) {
                    $where .= "updatedBy.{$col} = ?";
                }
                $params[] = array($val, 's');
                $i++;
            }

        }
        $sql = "SELECT jh.*, jt.jobTitle, u.unitName as departmentName
                FROM tija_employee_job_history jh
                LEFT JOIN tija_job_titles jt ON jh.jobTitleID = jt.jobTitleID
                LEFT JOIN tija_units u ON jh.departmentID = u.unitID
                {$where}
                ORDER BY jh.startDate DESC";
        $rows = $DBConn->fetch_all_rows($sql,$params);

        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }


    /**
     * Save job history record
     */
    public static function save_job_history($data, $DBConn = null) {
        if (!$DBConn) {
            return ['success' => false, 'message' => 'Database connection required'];
        }

        $jobHistoryID = $data['jobHistoryID'] ?? null;

        $fields = [
            'employeeID', 'jobTitleID', 'jobTitle', 'departmentID', 'department',
            'divisionID', 'division', 'startDate', 'endDate', 'isCurrent',
            'responsibilities', 'achievements', 'reasonForChange', 'notes',
            'createdBy', 'updatedBy', 'Suspended'
        ];

        if ($jobHistoryID) {
            // Update existing record
            $updates = [];
            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $value = self::escape_value($data[$field], $DBConn);
                    $updates[] = "`$field` = $value";
                }
            }

            if (empty($updates)) {
                return ['success' => false, 'message' => 'No data to update'];
            }

            $sql = "UPDATE tija_employee_job_history
                    SET " . implode(', ', $updates) . "
                    WHERE jobHistoryID = " . (int)$jobHistoryID;

            if ($DBConn->query($sql)) {
                return [
                    'success' => true,
                    'message' => 'Job history updated successfully',
                    'id' => $jobHistoryID
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to update job history'];
            }
        } else {
            // Insert new record
            $insertFields = [];
            $insertValues = [];

            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $insertFields[] = "`$field`";
                    $insertValues[] = self::escape_value($data[$field], $DBConn);
                }
            }

            if (empty($insertFields)) {
                return ['success' => false, 'message' => 'No data provided'];
            }

            $sql = "INSERT INTO tija_employee_job_history
                    (" . implode(', ', $insertFields) . ")
                    VALUES (" . implode(', ', $insertValues) . ")";

            if ($DBConn->query($sql)) {
                return [
                    'success' => true,
                    'message' => 'Job history created successfully',
                    'id' => $DBConn->insert_id
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to create job history'];
            }
        }
    }

    /**
     * Delete job history record
     */
    public static function delete_job_history($jobHistoryID, $DBConn = null) {
        if (!$DBConn) {
            return ['success' => false, 'message' => 'Database connection required'];
        }

        $sql = "UPDATE tija_employee_job_history
                SET Suspended = 'Y'
                WHERE jobHistoryID = " . (int)$jobHistoryID;

        if ($DBConn->query($sql)) {
            return ['success' => true, 'message' => 'Job history deleted successfully'];
        }

        return ['success' => false, 'message' => 'Failed to delete job history'];
    }

    // ===================================================================
    // 4. COMPENSATION METHODS
    // ===================================================================

    /**
     * Get employee compensation details
     */
    public static function get_compensation($params = [], $single = false, $DBConn = null) {
        if (!$DBConn) {
            return null;
        }

        $sql = "SELECT * FROM tija_employee_compensation WHERE Suspended = 'N'";

        $whereClause = self::build_where_clause($params);
        $sql .= $whereClause;
        $sql .= " ORDER BY effectiveDate DESC";

        if ($single) {
            $sql .= " LIMIT 1";
        }

        $result = $DBConn->query($sql);

        if (!$result) {
            return null;
        }

        if ($single) {
            return $result->fetch_object();
        }

        $compensation = [];
        while ($row = $result->fetch_object()) {
            $compensation[] = $row;
        }

        return empty($compensation) ? null : $compensation;
    }

    /**
     * Save compensation details
     */
    public static function save_compensation($data, $DBConn = null) {
        if (!$DBConn) {
            return ['success' => false, 'message' => 'Database connection required'];
        }

        $compensationID = $data['compensationID'] ?? null;

        $fields = [
            'employeeID', 'basicSalary', 'currency', 'paymentFrequency',
            'housingAllowance', 'transportAllowance', 'medicalAllowance',
            'communicationAllowance', 'otherAllowances', 'bonusEligible',
            'overtimeEligible', 'overtimeRate', 'commissionEligible', 'commissionRate',
            'effectiveDate', 'endDate', 'isCurrent', 'salaryGrade', 'salaryStep',
            'payrollBankID', 'notes', 'createdBy', 'updatedBy', 'Suspended'
        ];

        if ($compensationID) {
            // Update existing record
            $updates = [];
            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $value = self::escape_value($data[$field], $DBConn);
                    $updates[] = "`$field` = $value";
                }
            }

            if (empty($updates)) {
                return ['success' => false, 'message' => 'No data to update'];
            }

            $sql = "UPDATE tija_employee_compensation
                    SET " . implode(', ', $updates) . "
                    WHERE compensationID = " . (int)$compensationID;

            if ($DBConn->query($sql)) {
                // Log salary history if salary changed
                if (isset($data['basicSalary'])) {
                    self::log_salary_history($data['employeeID'], $data, $DBConn);
                }

                return [
                    'success' => true,
                    'message' => 'Compensation updated successfully',
                    'id' => $compensationID
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to update compensation'];
            }
        } else {
            // Insert new record
            $insertFields = [];
            $insertValues = [];

            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $insertFields[] = "`$field`";
                    $insertValues[] = self::escape_value($data[$field], $DBConn);
                }
            }

            if (empty($insertFields)) {
                return ['success' => false, 'message' => 'No data provided'];
            }

            $sql = "INSERT INTO tija_employee_compensation
                    (" . implode(', ', $insertFields) . ")
                    VALUES (" . implode(', ', $insertValues) . ")";

            if ($DBConn->query($sql)) {
                return [
                    'success' => true,
                    'message' => 'Compensation created successfully',
                    'id' => $DBConn->insert_id
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to create compensation'];
            }
        }
    }

    /**
     * Get salary history
     */
    public static function get_salary_history($params = [], $single = false, $DBConn = null) {
        if (!$DBConn) {
            return null;
        }

        $sql = "SELECT * FROM tija_employee_salary_history";
        $whereClause = self::build_where_clause($params);
        $sql .= " WHERE 1=1" . $whereClause . " ORDER BY effectiveDate DESC";

        if ($single) {
            $sql .= " LIMIT 1";
        }

        $result = $DBConn->query($sql);
        if (!$result) return null;

        if ($single) {
            return $result->fetch_object();
        }

        $history = [];
        while ($row = $result->fetch_object()) {
            $history[] = $row;
        }

        return empty($history) ? null : $history;
    }

    /**
     * Log salary history
     */
    protected static function log_salary_history($employeeID, $data, $DBConn) {
        // Get previous salary
        $previous = self::get_compensation(['employeeID' => $employeeID, 'isCurrent' => 'Y'], true, $DBConn);

        if ($previous && $previous->basicSalary != $data['basicSalary']) {
            $change = (($data['basicSalary'] - $previous->basicSalary) / $previous->basicSalary) * 100;

            $sql = "INSERT INTO tija_employee_salary_history
                    (employeeID, oldBasicSalary, newBasicSalary, oldGrossSalary, newGrossSalary,
                     changePercentage, changeReason, effectiveDate, createdBy)
                    VALUES
                    (" . (int)$employeeID . ", " . (float)$previous->basicSalary . ", " .
                    (float)$data['basicSalary'] . ", " . (float)$previous->grossSalary . ", " .
                    (float)($data['basicSalary'] + $data['housingAllowance'] + $data['transportAllowance'] +
                    $data['medicalAllowance'] + $data['communicationAllowance'] + $data['otherAllowances']) . ", " .
                    (float)$change . ", " . self::escape_value($data['changeReason'] ?? 'Salary adjustment', $DBConn) . ", " .
                    self::escape_value($data['effectiveDate'], $DBConn) . ", " .
                    (int)($data['createdBy'] ?? 0) . ")";

            $DBConn->query($sql);
        }
    }

    // ===================================================================
    // CONTINUE IN NEXT PART...
    // (Methods for Contact Details, Emergency Contacts, Next of Kin, etc.)
    // ===================================================================

    /**
     * Build WHERE clause from parameters
     */
    protected static function build_where_clause($params, $tableAlias = '') {
        $where = '';
        $prefix = $tableAlias ? "$tableAlias." : '';

        foreach ($params as $key => $value) {
            if ($value === null) {
                $where .= " AND {$prefix}`$key` IS NULL";
            } else {
                if (is_numeric($value)) {
                    $where .= " AND {$prefix}`$key` = $value";
                } else {
                    $where .= " AND {$prefix}`$key` = '$value'";
                }
            }
        }

        return $where;
    }

    /**
     * Escape value for SQL query
     */
    protected static function escape_value($value, $DBConn) {
        if ($value === null || $value === '') {
            return 'NULL';
        }

        if (is_numeric($value)) {
            return $value;
        }

        return "'" . $DBConn->real_escape_string($value) . "'";
    }
}

// Continue in EmployeeProfileExtended.class.php for remaining methods...

