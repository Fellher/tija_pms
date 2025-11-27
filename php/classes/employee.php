<?php
class Employee {

    private static $hrAssignmentTableChecked = false;
    private static $hrAssignmentTableExists = false;

    private static function hrAssignmentsEnabled($DBConn) {
        if (!self::$hrAssignmentTableChecked) {
            $rows = $DBConn->fetch_all_rows("SHOW TABLES LIKE 'tija_entity_hr_assignments'", array());
            self::$hrAssignmentTableExists = ($rows && count($rows) > 0);
            self::$hrAssignmentTableChecked = true;
        }
        return self::$hrAssignmentTableExists;
    }

    public static function employees($whereArr, $single,$DBConn) {
        $params= array();
        $where= '';
        $rows=array();
        $people = array('ID', 'DateAdded', 'FirstName', 'Surname', 'OtherNames', 'userInitials', 'Email', 'profile_image', 'Valid');
        $userDetails = array('UID', 'DateAdded', 'orgDataID', 'entityID', 'prefixID', 'phoneNo', 'payrollNo', 'pin', 'dateOfBirth', 'gender', 'workTypeID', 'jobTitleID', 'jobCategoryID', 'jobBandID', 'employmentStatusID', 'payGradeID', 'employmentStartDate', 'employmentEndDate', 'costPerHour', 'dailyHours', 'overtimeAllowed', 'supervisorID', 'weekWorkDays', 'workHourRoundingID', 'setUpProfile', 'LastUpdatedByID', 'nationalID', 'nhifNumber', 'nssfNumber', 'basicSalary', 'LastUpdate', 'Lapsed', 'Suspended');
        $entity = array('entityName', 'entityTypeID', 'entityParentID', 'industrySectorID', 'registrationNumber', 'entityPIN', 'entityCity', 'entityCountry', 'entityPhoneNumber', 'entityEmail');
        $organisation = array('orgName');
        $prefix = array('prefixName');
        $jobTitle = array('jobTitle', 'jobDescription');
        $businessUnit = array('businessUnitName', 'businessUnitID');


        if (count($whereArr) > 0) {
        $i = 0;
        foreach ($whereArr as $col => $val) {
            if ($where == '') {
                $where = "WHERE ";
            } else {
                $where .= " AND ";
            }
                // Check if the column is in the people table
                if (in_array($col, $people)) {
                    $where .= "u.{$col} = ?";
                } elseif (in_array($col, $userDetails)) {
                    $where .= "d.{$col} = ?";
                } elseif (in_array($col, $entity)) {
                    $where .= "e.{$col} = ?";
                } elseif (in_array($col, $organisation)) {
                    $where .= "o.{$col} = ?";
                } elseif (in_array($col, $prefix)) {
                    $where .= "p.{$col} = ?";
                } elseif (in_array($col, $jobTitle)) {
                    $where .= "jt.{$col} = ?";
                } elseif (in_array($col, $businessUnit)) {
                    $where .= "d.{$col} = ?";
                }
                else {
                    // If the column is not found in any of the tables, you can choose to skip it or handle it differently
                    continue;
                }
            // $where .= "u.{$col} = ?";
            $params[] = array($val, 's');
            $i++;
        }
        }

        // var_dump($whereArr);
        // var_dump($where);

        $sql = "SELECT
            u.ID,   u.DateAdded, u.FirstName, u.Surname, u.OtherNames, u.userInitials, u.Email, u.profile_image, u.Valid,
            d.ID AS userID, d.UID, d.DateAdded, d.orgDataID, d.entityID, d.prefixID, d.phoneNo, d.payrollNo, d.pin, d.dateOfBirth, d.gender,  d.businessUnitID, d.supervisorID,
            d.supervisingJobTitleID,  d.workTypeID,  d.jobTitleID, d.jobCategoryID, d.jobBandID, d.employmentStatusID, d.payGradeID, d.employmentStartDate,  d.employmentEndDate,
            d.costPerHour, d.dailyHours, d.overtimeAllowed, d.weekWorkDays, d.workHourRoundingID, d.setUpProfile, d.nationalID, d.nhifNumber, d.nssfNumber,
            d.basicSalary, d.bonusEligible, d.commissionEligible, d.commissionRate, d.profileImageFile, d.LastUpdatedByID, d.LastUpdate, d.Lapsed, d.Suspended, d.contractStartDate, d.contractEndDate, d.isHRManager,
            e.entityName, e.entityTypeID, e.entityParentID, e.industrySectorID, e.registrationNumber, e.entityPIN, e.entityCity,  e.entityCountry,
            e.entityPhoneNumber,
            e.entityEmail,
            o.orgName,
            p.prefixName,
            jt.jobTitle,
            jt.jobDescription,
            bu.businessUnitName,
            bu.businessUnitID,
            GROUP_CONCAT(DISTINCT CONCAT(un.unitName, ' (', ut.unitTypeName, ')') SEPARATOR ', ') AS unitAssignments,
            GROUP_CONCAT(DISTINCT un.unitID) AS unitIDs,
            GROUP_CONCAT(DISTINCT ut.unitTypeName) AS unitTypes,
            CONCAT(supervisor_u.FirstName, ' ', supervisor_u.Surname) AS supervisorName,
            CONCAT(u.FirstName, IF(u.OtherNames IS NOT NULL, CONCAT(' ', u.OtherNames), ''), ' ', u.Surname) AS employeeName,
            CONCAT(u.FirstName, IF(u.OtherNames IS NOT NULL, CONCAT(' ', u.OtherNames), ''), ' ', u.Surname,  ' (', u.userInitials, ')') AS employeeNameWithInitials,
        CONCAT(SUBSTRING(u.FirstName, 1, 1), SUBSTRING(u.Surname, 1, 1)) AS employeeInitials
        FROM user_details d
        LEFT JOIN people u ON d.ID = u.ID
        LEFT JOIN tija_entities e ON d.entityID = e.entityID
        LEFT JOIN tija_organisation_data o ON d.orgDataID = o.orgDataID
        LEFT JOIN tija_name_prefixes p ON d.prefixID = p.prefixID
        LEFT JOIN tija_job_titles jt ON d.jobTitleID = jt.jobTitleID
        LEFT JOIN tija_business_units bu ON d.businessUnitID = bu.businessUnitID
        LEFT JOIN tija_user_unit_assignments ua ON d.ID = ua.userID
        LEFT JOIN tija_units un ON ua.unitID = un.unitID
        LEFT JOIN tija_unit_types ut ON un.unitTypeID = ut.unitTypeID
        LEFT JOIN people supervisor_u ON d.supervisorID = supervisor_u.ID
        {$where}
        GROUP BY d.ID
        ORDER BY employeeName ASC";
        $rows = $DBConn->fetch_all_rows($sql,$params);

        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    public static function organisation_roles($whereArr, $single,$DBConn) {
        $params= array();
        $where= '';
        $rows=array();
        $jobTitles = array('jobTitle', 'jobDescription', 'jobCategoryID', 'jobSpesification', 'jobBandID', 'employmentStatusID', 'payGradeID', 'jobTitleID');
        $organisation = array('orgName' );
        $entity = array('entityName', 'entityTypeID', 'entityParentID', 'industrySectorID', 'registrationNumber', 'entityPIN', 'entityCity', 'entityCountry', 'entityPhoneNumber', 'entityEmail');

        if (count($whereArr) > 0) {
            $i = 0;
            foreach ($whereArr as $col => $val) {
            if ($where == '') {
                $where = "WHERE ";
            } else {
                $where .= " AND ";
            }

            }
        }

        $sql = "SELECT r.orgRoleID, r.DateAdded, r.orgDataID, r.entityID, r.jobTitleID, r.LastUpdateByID, r.LastUpdate, r.Lapsed, r.Suspended,
        jt.jobTitle, jt.jobDescription, jt.jobCategoryID, jt.jobSpesification, jt.jobGradeID, jt.jobDescriptionDoc,
        o.orgName, e.entityName, e.entityTypeID, e.entityParentID, e.industrySectorID, e.registrationNumber, e.entityPIN, e.entityCity, e.entityCountry, e.entityPhoneNumber, e.entityEmail
        FROM tija_organisation_roles r
        LEFT JOIN tija_job_titles jt ON r.jobTitleID = jt.jobTitleID
        LEFT JOIN tija_organisation_data o ON r.orgDataID = o.orgDataID
        LEFT JOIN tija_entities e ON r.entityID = e.entityID
        {$where}
        ORDER BY jt.jobTitle ASC";
        $rows = $DBConn->fetch_all_rows($sql,$params);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }
    public static function employee_categorised ($whereArr, $single, $DBConn ) {
        $params= array();
        $where= '';
        $rows=array();
        $people = array('ID', 'DateAdded', 'FirstName', 'Surname', 'OtherNames', 'Email', 'profile_image', 'Valid');
        $userDetails = array('UID', 'DateAdded', 'orgDataID', 'entityID', 'prefixID', 'phoneNo', 'payrollNo', 'pin', 'dateOfBirth', 'gender', 'workTypeID', 'jobTitleID', 'jobCategoryID', 'jobBandID', 'employmentStatusID', 'payGradeID', 'employmentStartDate', 'employmentEndDate', 'costPerHour', 'dailyHours', 'overtimeAllowed', 'supervisorID', 'weekWorkDays', 'workHourRoundingID', 'setUpProfile', 'LastUpdatedByID', 'nationalID', 'nhifNumber', 'nssfNumber', 'basicSalary', 'LastUpdate', 'Lapsed', 'Suspended');
        $jobTitle = array('jobTitle', 'jobDescription');
        $prefix = array('prefixName');

        if (count($whereArr) > 0) {
            $i = 0;
            foreach ($whereArr as $col => $val) {
                if ($where == '') {
                    $where = "WHERE ";
                } else {
                    $where .= " AND ";
                }
                // Check if the column is in the people table
                if (in_array($col, $people)) {
                    $where .= "u.{$col} = ?";
                } elseif (in_array($col, $userDetails)) {
                    $where .= "d.{$col} = ?";
                } elseif (in_array($col, $prefix)) {
                    $where .= "p.{$col} = ?";
                } elseif (in_array($col, $jobTitle)) {
                    $where .= "jt.{$col} = ?";
                }
                else {
                    // If the column is not found in any of the tables, you can choose to skip it or handle it differently
                    continue;
                }
                // $where .= "u.{$col} = ?";
                $params[] = array($val, 's');
                $i++;
            }
        }
        $sql = "SELECT U.ID, u.DateAdded, u.FirstName, u.Surname, u.OtherNames, u.Email, u.profile_image, u.Valid,
            d.ID AS userID, d.UID, d.DateAdded, d.orgDataID, d.entityID, d.prefixID, d.phoneNo, d.payrollNo, d.pin, d.dateOfBirth, d.gender,  d.businessUnitID, d.supervisorID,
            d.supervisingJobTitleID,  d.workTypeID,  d.jobTitleID, d.jobCategoryID, d.jobBandID, d.employmentStatusID, d.payGradeID, d.employmentStartDate,  d.employmentEndDate,
            d.costPerHour, d.dailyHours, d.overtimeAllowed, d.weekWorkDays, d.workHourRoundingID, d.setUpProfile, d.nationalID, d.nhifNumber, d.nssfNumber,
            d.basicSalary, d.bonusEligible, d.commissionEligible, d.commissionRate, d.profileImageFile, d.LastUpdatedByID, d.LastUpdate, d.Lapsed, d.Suspended,
            p.prefixName,
            jt.jobTitle,
            jt.jobDescription,
            CONCAT(u.FirstName, ' ', u.Surname) AS employeeName,
        CONCAT(supervisor_u.FirstName, ' ', supervisor_u.Surname) AS supervisorName,
        FROM user_details d
        LEFT JOIN people u ON d.ID = u.ID
        LEFT JOIN tija_name_prefixes p ON d.prefixID = p.prefixID
        LEFT JOIN tija_job_titles jt ON d.jobTitleID = jt.jobTitleID
        LEFT JOIN people supervisor_u ON d.supervisorID = supervisor_u.ID
        {$where}
        ORDER BY  employeeName ASC";
        $rows = $DBConn->fetch_all_rows($sql,$params);
        // var_dump($rows);
        if($rows){
            $categorizedRows = array();
            foreach($rows as $row) {
                $categorizedRows[$row->jobTitle][] = $row;
            }
        }
        // var_dump($categorizedRows);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
        // return $rows;
        // return $params;
        // return $sql;


    }

    public static function categorise_employee($rows, $field='jobTitle') {
        $categorizedRows = array();
        foreach($rows as $row) {
            $categorizedRows[$row->$field][] = $row;
        }
        return $categorizedRows;
    }

    public static function 	user_unit_assignments( $whereArr, $single, $DBConn) {
        $params= array();
        $where= '';
        $rows=array();
        $unitAssignments = array(
            'unitAssignmentID', 'DateAdded', 'orgDataID', 'entityID', 'userID', 'unitID', 'unitTypeID', 'assignmentStartDate', 'assignmentEndDate',  'LastUpdatedByID', 'LastUpdate', 'Lapsed', 'Suspended'

        );
        $units = array(
            'unitID', 'unitCode', 'unitName', 'unitDescription', 'unitTypeID', 'headOfUnitID', 'parentUnitID', 'unitDescription'
        );
        if (count($whereArr) > 0) {
            $i = 0;
            foreach ($whereArr as $col => $val) {
                if ($where == '') {
                    $where = "WHERE ";
                } else {
                    $where .= " AND ";
                }
                // Check if the column is in the unitAssignments table
                if (in_array($col, $unitAssignments)) {
                    $where .= "ua.{$col} = ?";
                } elseif (in_array($col, $units)) {
                    $where .= "u.{$col} = ?";
                } else {
                    // If the column is not found in any of the tables, you can choose to skip it or handle it differently
                    continue;
                }
                // $where .= "u.{$col} = ?";
                $params[] = array($val, 's');
                $i++;
            }
        }

        $sql = "SELECT ua.unitAssignmentID, ua.DateAdded, ua.orgDataID, ua.entityID, ua.userID, ua.unitID, ua.unitTypeID, ua.assignmentStartDate, ua.assignmentEndDate, ua.LastUpdatedByID, ua.LastUpdate, ua.Lapsed, ua.Suspended,
            u.unitCode, u.unitName, u.unitDescription, u.unitTypeID, u.headOfUnitID, u.parentUnitID,
            CONCAT(p.FirstName, ' ', p.Surname) AS employeeName
        FROM tija_user_unit_assignments ua
        LEFT JOIN tija_units u ON ua.unitID = u.unitID
        LEFT JOIN people p ON ua.userID = p.ID
        {$where}
        ORDER BY u.unitName ASC";
        $rows = $DBConn->fetch_all_rows($sql,$params);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }









    // ============================================================================
    // ENHANCED LEAVE MANAGEMENT METHODS
    // ============================================================================

    /**
     * Get direct report for an employee
     *
     * @param int $employeeID Employee ID
     * @param object $DBConn Database connection object
     * @return mixed Direct report details or false on failure
     */
    public static function get_direct_report($employeeID, $DBConn) {
        $employee = self::employees(array('ID' => $employeeID), true, $DBConn);

        if (!$employee || !$employee->supervisorID) {
            return false;
        }

        return self::employees(array('ID' => $employee->supervisorID), true, $DBConn);
    }

    /**
     * Get department head for a specific unit
     *
     * @param int $unitID Unit ID
     * @param object $DBConn Database connection object
     * @return mixed Department head details or false on failure
     */
    public static function get_department_head($unitID, $DBConn) {
        if (!$unitID) {
            return false;
        }

        $sql = "SELECT u.ID, u.FirstName, u.Surname, u.Email,
                ud.jobTitleID, jt.jobTitle,
                un.unitName, un.headOfUnitID, ut.unitTypeName
                FROM user_details ud
                LEFT JOIN people u ON ud.ID = u.ID
                LEFT JOIN tija_user_unit_assignments ua ON ud.ID = ua.userID
                LEFT JOIN tija_units un ON ua.unitID = un.unitID
                LEFT JOIN tija_unit_types ut ON un.unitTypeID = ut.unitTypeID
                LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
                WHERE ua.unitID = ?
                AND (un.headOfUnitID = ud.ID OR jt.jobTitle LIKE '%Head%' OR jt.jobTitle LIKE '%Manager%')
                AND ud.Lapsed = 'N'
                AND ud.Suspended = 'N'
                AND (ua.assignmentEndDate IS NULL OR ua.assignmentEndDate >= CURDATE())
                AND ua.Lapsed = 'N'
                AND ua.Suspended = 'N'
                AND un.Lapsed = 'N'
                AND un.Suspended = 'N'
                ORDER BY jt.jobTitle DESC
                LIMIT 1";

        $params = array(array($unitID, 'i'));
        $rows = $DBConn->fetch_all_rows($sql, $params);

        return ($rows && count($rows) > 0) ? $rows[0] : false;
    }

    /**
     * Get HR manager for an organization
     *
     * @param int $orgDataID Organization data ID
     * @param int $entityID Entity ID
     * @param object $DBConn Database connection object
     * @return mixed HR manager details or false on failure
     */
    public static function get_hr_manager($orgDataID, $entityID, $DBConn) {
        if (!$entityID) {
            return false;
        }

        if (self::hrAssignmentsEnabled($DBConn)) {
            $assignmentSql = "SELECT u.ID, u.FirstName, u.Surname, u.Email,
                    ud.jobTitleID, jt.jobTitle,
                    ud.orgDataID, ud.entityID,
                    a.roleType
                FROM tija_entity_hr_assignments a
                LEFT JOIN user_details ud ON a.userID = ud.ID
                LEFT JOIN people u ON ud.ID = u.ID
                LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
                WHERE a.entityID = ?
                  AND a.Lapsed = 'N' AND a.Suspended = 'N'
                  AND ud.Lapsed = 'N' AND ud.Suspended = 'N'
                ORDER BY FIELD(a.roleType, 'primary','substitute'), a.DateAdded ASC
                LIMIT 2";

            $assignmentRows = $DBConn->fetch_all_rows($assignmentSql, array(array($entityID, 'i')));
            if ($assignmentRows && count($assignmentRows) > 0) {
                $assignmentRows[0]->hrRoleType = $assignmentRows[0]->roleType ?? 'primary';
                return $assignmentRows[0];
            }
        }

        // Fallback to legacy isHRManager flag
        $sql = "SELECT u.ID, u.FirstName, u.Surname, u.Email,
                ud.jobTitleID, jt.jobTitle,
                ud.orgDataID, ud.entityID
                FROM user_details ud
                LEFT JOIN people u ON ud.ID = u.ID
                LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
                WHERE ud.orgDataID = ?
                AND ud.entityID = ?
                AND ud.isHRManager = 'Y'
                AND ud.Lapsed = 'N'
                AND ud.Suspended = 'N'
                ORDER BY jt.jobTitle ASC
                LIMIT 1";

        $params = array(
            array($orgDataID, 'i'),
            array($entityID, 'i')
        );

        $rows = $DBConn->fetch_all_rows($sql, $params);

        if ($rows && count($rows) > 0) {
            $rows[0]->hrRoleType = 'general';
            return $rows[0];
        }

        // Fallback to job title heuristic
        $sql = "SELECT u.ID, u.FirstName, u.Surname, u.Email,
                ud.jobTitleID, jt.jobTitle,
                ud.orgDataID, ud.entityID
                FROM user_details ud
                LEFT JOIN people u ON ud.ID = u.ID
                LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
                WHERE ud.orgDataID = ?
                AND ud.entityID = ?
                AND (jt.jobTitle LIKE '%HR%' OR jt.jobTitle LIKE '%Human Resource%' OR jt.jobTitle LIKE '%Personnel%')
                AND ud.Lapsed = 'N'
                AND ud.Suspended = 'N'
                ORDER BY jt.jobTitle ASC
                LIMIT 1";

        $rows = $DBConn->fetch_all_rows($sql, $params);

        if ($rows && count($rows) > 0) {
            $rows[0]->hrRoleType = 'general';
            return $rows[0];
        }

        return false;
    }

    /**
     * Get all HR managers for an entity
     *
     * @param int $entityID Entity ID
     * @param object $DBConn Database connection object
     * @return array Array of HR manager objects
     */
    public static function get_hr_managers_for_entity($entityID, $DBConn) {
        if (empty($entityID)) {
            return array();
        }

        $results = array();

        if (self::hrAssignmentsEnabled($DBConn)) {
            $assignmentSql = "SELECT u.ID, u.FirstName, u.Surname, u.Email,
                    ud.jobTitleID, jt.jobTitle,
                    ud.orgDataID, ud.entityID,
                    a.roleType
                FROM tija_entity_hr_assignments a
                LEFT JOIN user_details ud ON a.userID = ud.ID
                LEFT JOIN people u ON ud.ID = u.ID
                LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
                WHERE a.entityID = ?
                  AND a.Lapsed = 'N' AND a.Suspended = 'N'
                  AND ud.Lapsed = 'N' AND ud.Suspended = 'N'
                ORDER BY FIELD(a.roleType, 'primary','substitute'), a.DateAdded ASC";

            $assignmentRows = $DBConn->fetch_all_rows($assignmentSql, array(array($entityID, 'i')));
            $assignedUserIDs = array();
            if ($assignmentRows) {
                foreach ($assignmentRows as $assignment) {
                    $assignment->hrRoleType = $assignment->roleType ?? 'primary';
                    $results[] = $assignment;
                    $assignedUserIDs[] = (int)$assignment->ID;
                }
            }

            $additionalSql = "SELECT u.ID, u.FirstName, u.Surname, u.Email,
                    ud.jobTitleID, jt.jobTitle,
                    ud.orgDataID, ud.entityID
                FROM user_details ud
                LEFT JOIN people u ON ud.ID = u.ID
                LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
                WHERE ud.entityID = ?
                  AND ud.isHRManager = 'Y'
                  AND ud.Lapsed = 'N'
                  AND ud.Suspended = 'N'";

            $params = array(array($entityID, 'i'));
            if (!empty($assignedUserIDs)) {
                $placeholders = implode(',', array_fill(0, count($assignedUserIDs), '?'));
                $additionalSql .= " AND ud.ID NOT IN ({$placeholders})";
                foreach ($assignedUserIDs as $assignedId) {
                    $params[] = array($assignedId, 'i');
                }
            }

            $additionalSql .= " ORDER BY jt.jobTitle ASC";

            $additionalRows = $DBConn->fetch_all_rows($additionalSql, $params);
            if ($additionalRows) {
                foreach ($additionalRows as $row) {
                    $row->hrRoleType = 'general';
                    $results[] = $row;
                }
            }

            if (!empty($results)) {
                return $results;
            }
        }

        // Legacy fallback
        $sql = "SELECT u.ID, u.FirstName, u.Surname, u.Email,
                ud.jobTitleID, jt.jobTitle,
                ud.orgDataID, ud.entityID
                FROM user_details ud
                LEFT JOIN people u ON ud.ID = u.ID
                LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
                WHERE ud.entityID = ?
                AND ud.isHRManager = 'Y'
                AND ud.Lapsed = 'N'
                AND ud.Suspended = 'N'
                ORDER BY jt.jobTitle ASC";

        $params = array(array($entityID, 'i'));
        $rows = $DBConn->fetch_all_rows($sql, $params);

        if ($rows) {
            foreach ($rows as $row) {
                $row->hrRoleType = 'general';
            }
        }

        return $rows ? $rows : array();
    }

    /**
     * Get team members for a manager
     *
     * @param int $managerID Manager ID
     * @param object $DBConn Database connection object
     * @return array Team members
     */
    public static function get_team_members($managerID, $DBConn) {
        $sql = "SELECT u.ID, u.FirstName, u.Surname, u.Email,
                ud.jobTitleID, jt.jobTitle,
                ud.entityID, ud.orgDataID, ud.businessUnitID
                FROM user_details ud
                LEFT JOIN people u ON ud.ID = u.ID
                LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
                WHERE ud.supervisorID = ?
                AND ud.Lapsed = 'N'
                AND ud.Suspended = 'N'
                ORDER BY u.FirstName, u.Surname ASC";

        $params = array(array($managerID, 'i'));
        $rows = $DBConn->fetch_all_rows($sql, $params);

        return $rows ? $rows : array();
    }

    /**
     * Get department members for a business unit
     *
     * @param int $businessUnitID Business unit ID
     * @param object $DBConn Database connection object
     * @return array Department members
     */
    public static function get_department_members($businessUnitID, $DBConn) {
        if (!$businessUnitID) {
            return array();
        }

        $sql = "SELECT u.ID, u.FirstName, u.Surname, u.Email,
                ud.jobTitleID, jt.jobTitle,
                ud.entityID, ud.orgDataID, ud.businessUnitID, ud.supervisorID
                FROM user_details ud
                LEFT JOIN people u ON ud.ID = u.ID
                LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
                WHERE ud.businessUnitID = ?
                AND ud.Lapsed = 'N'
                AND ud.Suspended = 'N'
                ORDER BY u.FirstName, u.Surname ASC";

        $params = array(array($businessUnitID, 'i'));
        $rows = $DBConn->fetch_all_rows($sql, $params);

        return $rows ? $rows : array();
    }

    /**
     * Get all employees for an organization
     *
     * @param int $orgDataID Organization data ID
     * @param int $entityID Entity ID
     * @param object $DBConn Database connection object
     * @return array All employees
     */
    public static function get_all_employees($orgDataID, $entityID, $DBConn) {
        $sql = "SELECT u.ID, u.FirstName, u.Surname, u.Email,
                ud.jobTitleID, jt.jobTitle,
                ud.entityID, ud.orgDataID, ud.businessUnitID, ud.supervisorID
                FROM user_details ud
                LEFT JOIN people u ON ud.ID = u.ID
                LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
                WHERE ud.orgDataID = ?
                AND ud.entityID = ?
                AND ud.Lapsed = 'N'
                AND ud.Suspended = 'N'
                ORDER BY u.FirstName, u.Surname ASC";

        $params = array(
            array($orgDataID, 'i'),
            array($entityID, 'i')
        );

        $rows = $DBConn->fetch_all_rows($sql, $params);

        return $rows ? $rows : array();
    }

    /**
     * Check if user is a manager
     *
     * @param int $employeeID Employee ID
     * @param object $DBConn Database connection object
     * @return bool True if user is a manager
     */
    public static function is_manager($employeeID, $DBConn) {
        $sql = "SELECT COUNT(*) as teamCount
                FROM user_details
                WHERE supervisorID = ?
                AND Lapsed = 'N'
                AND Suspended = 'N'";

        $params = array(array($employeeID, 'i'));
        $rows = $DBConn->fetch_all_rows($sql, $params);

        return ($rows && count($rows) > 0) ? ($rows[0]->teamCount > 0) : false;
    }

    /**
     * Check if user is a department head
     *
     * @param int $employeeID Employee ID
     * @param object $DBConn Database connection object
     * @return bool True if user is a department head
     */
    public static function is_department_head($employeeID, $DBConn) {
        $sql = "SELECT u.headOfUnitID, jt.jobTitle, ut.unitTypeName
                FROM user_details ud
                LEFT JOIN tija_user_unit_assignments ua ON ud.ID = ua.userID
                LEFT JOIN tija_units u ON ua.unitID = u.unitID
                LEFT JOIN tija_unit_types ut ON u.unitTypeID = ut.unitTypeID
                LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
                WHERE ud.ID = ?
                AND (u.headOfUnitID = ud.ID OR jt.jobTitle LIKE '%Head%' OR jt.jobTitle LIKE '%Manager%')
                AND ud.Lapsed = 'N'
                AND ud.Suspended = 'N'
                AND (ua.assignmentEndDate IS NULL OR ua.assignmentEndDate >= CURDATE())
                AND ua.Lapsed = 'N'
                AND ua.Suspended = 'N'
                AND u.Lapsed = 'N'
                AND u.Suspended = 'N'";

        $params = array(array($employeeID, 'i'));
        $rows = $DBConn->fetch_all_rows($sql, $params);

        return ($rows && count($rows) > 0);
    }

    /**
     * Check if user is an HR manager
     *
     * @param int $employeeID Employee ID
     * @param object $DBConn Database connection object
     * @return bool True if user is an HR manager
     */
    public static function get_hr_manager_scope($employeeID, $DBConn) {
        $scope = array(
            'isHRManager' => false,
            'hasGlobalScope' => false,
            'entityIDs' => array(),
            'orgDataIDs' => array(),
            'scopes' => array()
        );

        if (!$employeeID) {
            return $scope;
        }

        $employee = self::employees(array('ID' => $employeeID), true, $DBConn);
        if ($employee && isset($employee->isHRManager) && strtoupper((string)$employee->isHRManager) === 'Y') {
            $scope['isHRManager'] = true;

            $entityID = isset($employee->entityID) ? $employee->entityID : null;
            $orgDataID = isset($employee->orgDataID) ? $employee->orgDataID : null;

            if ($entityID !== null && $entityID !== '') {
                $entityID = (int)$entityID;
                $scope['entityIDs'][] = $entityID;
                $scope['scopes'][] = array(
                    'entityID' => $entityID,
                    'orgDataID' => $orgDataID !== null && $orgDataID !== '' ? (int)$orgDataID : null,
                    'global' => false
                );
            } else {
                $scope['hasGlobalScope'] = true;
                $scope['scopes'][] = array(
                    'entityID' => null,
                    'orgDataID' => $orgDataID !== null && $orgDataID !== '' ? (int)$orgDataID : null,
                    'global' => true
                );
            }

            if ($orgDataID !== null && $orgDataID !== '') {
                $scope['orgDataIDs'][] = (int)$orgDataID;
            }
        }

        // Deduplicate scope arrays
        $scope['entityIDs'] = array_values(array_unique(array_filter($scope['entityIDs'], function ($value) {
            return $value !== null;
        })));
        $scope['orgDataIDs'] = array_values(array_unique(array_filter($scope['orgDataIDs'], function ($value) {
            return $value !== null;
        })));

        if (!empty($scope['scopes'])) {
            $uniqueScopes = array();
            $seen = array();
            foreach ($scope['scopes'] as $entry) {
                $orgId = $entry['orgDataID'] ?? null;
                $scopeIdentifier = null;

                if (!empty($entry['global'])) {
                    $scopeIdentifier = $orgId !== null ? $orgId . ':*' : '*';
                } elseif ($orgId !== null && isset($entry['entityID']) && $entry['entityID'] !== null && $entry['entityID'] !== '') {
                    $scopeIdentifier = $orgId . ':' . (int)$entry['entityID'];
                }

                if ($scopeIdentifier === null) {
                    continue;
                }

                if (!isset($seen[$scopeIdentifier])) {
                    $seen[$scopeIdentifier] = true;
                    $uniqueScopes[] = $entry;
                }
            }
            $scope['scopes'] = $uniqueScopes;
        }

        return $scope;
    }

    public static function is_hr_manager($employeeID, $DBConn, $entityID = null) {
        $scope = self::get_hr_manager_scope($employeeID, $DBConn);
        if (!$scope['isHRManager']) {
            return false;
        }

        if ($entityID === null) {
            return true;
        }

        if ($scope['hasGlobalScope']) {
            return true;
        }

        $entityID = (int)$entityID;
        return in_array($entityID, $scope['entityIDs'], true);
    }

    public static function get_hr_managed_employees($employeeID, $DBConn) {
        $scope = self::get_hr_manager_scope($employeeID, $DBConn);
        if (empty($scope['isHRManager'])) {
            return array();
        }

        $managerDetails = self::employees(array('ID' => $employeeID), true, $DBConn);
        $defaultOrgID = $managerDetails && isset($managerDetails->orgDataID) ? (int)$managerDetails->orgDataID : null;
        $defaultEntityID = $managerDetails && isset($managerDetails->entityID) ? (int)$managerDetails->entityID : null;

        $entries = $scope['scopes'] ?? array();
        if (empty($entries)) {
            $entries[] = array(
                'entityID' => $defaultEntityID,
                'orgDataID' => $defaultOrgID,
                'global' => false
            );
        }

        $employeeMap = array();

        foreach ($entries as $entry) {
            $orgID = isset($entry['orgDataID']) && $entry['orgDataID'] !== null && $entry['orgDataID'] !== ''
                ? (int)$entry['orgDataID']
                : $defaultOrgID;

            if (!$orgID) {
                continue;
            }

            if (!empty($entry['global'])) {
                $entities = Data::entities_full(array('orgDataID' => $orgID, 'Suspended' => 'N'), false, $DBConn);
                if ($entities) {
                    foreach ($entities as $entityRow) {
                        $records = self::get_all_employees($orgID, $entityRow->entityID, $DBConn);
                        if ($records) {
                            foreach ($records as $record) {
                                $employeeMap[$record->ID] = $record;
                            }
                        }
                    }
                }
                continue;
            }

            $entityID = isset($entry['entityID']) && $entry['entityID'] !== null && $entry['entityID'] !== ''
                ? (int)$entry['entityID']
                : $defaultEntityID;

            if (!$entityID) {
                continue;
            }

            $records = self::get_all_employees($orgID, $entityID, $DBConn);
            if ($records) {
                foreach ($records as $record) {
                    $employeeMap[$record->ID] = $record;
                }
            }
        }

        if (isset($employeeMap[$employeeID])) {
            unset($employeeMap[$employeeID]);
        }

        return array_values($employeeMap);
    }

    /**
     * Get all unit assignments for an employee
     *
     * @param int $employeeID Employee ID
     * @param object $DBConn Database connection object
     * @return array Unit assignments
     */
    public static function get_employee_unit_assignments($employeeID, $DBConn) {
        $sql = "SELECT ua.unitAssignmentID, ua.unitID, ua.unitTypeID, ua.assignmentStartDate, ua.assignmentEndDate,
                u.unitName, u.unitCode, u.headOfUnitID, u.parentUnitID,
                ut.unitTypeName,
                CONCAT(p.FirstName, ' ', p.Surname) AS headOfUnitName
                FROM tija_user_unit_assignments ua
                LEFT JOIN tija_units u ON ua.unitID = u.unitID
                LEFT JOIN tija_unit_types ut ON u.unitTypeID = ut.unitTypeID
                LEFT JOIN people p ON u.headOfUnitID = p.ID
                WHERE ua.userID = ?
                AND (ua.assignmentEndDate IS NULL OR ua.assignmentEndDate >= CURDATE())
                AND ua.Lapsed = 'N'
                AND ua.Suspended = 'N'
                AND u.Lapsed = 'N'
                AND u.Suspended = 'N'
                ORDER BY ut.unitOrder ASC, u.unitName ASC";

        $params = array(array($employeeID, 'i'));
        $rows = $DBConn->fetch_all_rows($sql, $params);

        return $rows ? $rows : array();
    }

    /**
     * Get employees assigned to a specific unit
     *
     * @param int $unitID Unit ID
     * @param object $DBConn Database connection object
     * @return array Employees in the unit
     */
    public static function get_unit_employees($unitID, $DBConn) {
        $sql = "SELECT u.ID, u.FirstName, u.Surname, u.Email,
                ud.jobTitleID, jt.jobTitle,
                ua.assignmentStartDate, ua.assignmentEndDate,
                ut.unitTypeName
                FROM user_details ud
                LEFT JOIN people u ON ud.ID = u.ID
                LEFT JOIN tija_user_unit_assignments ua ON ud.ID = ua.userID
                LEFT JOIN tija_units un ON ua.unitID = un.unitID
                LEFT JOIN tija_unit_types ut ON un.unitTypeID = ut.unitTypeID
                LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
                WHERE ua.unitID = ?
                AND (ua.assignmentEndDate IS NULL OR ua.assignmentEndDate >= CURDATE())
                AND ud.Lapsed = 'N'
                AND ud.Suspended = 'N'
                AND ua.Lapsed = 'N'
                AND ua.Suspended = 'N'
                AND un.Lapsed = 'N'
                AND un.Suspended = 'N'
                ORDER BY u.FirstName, u.Surname ASC";

        $params = array(array($unitID, 'i'));
        $rows = $DBConn->fetch_all_rows($sql, $params);

        return $rows ? $rows : array();
    }

    /**
     * Get department head for employee's primary unit (department type)
     *
     * @param int $employeeID Employee ID
     * @param object $DBConn Database connection object
     * @return mixed Department head details or false on failure
     */
    public static function get_employee_department_head($employeeID, $DBConn) {
        $sql = "SELECT u.ID, u.FirstName, u.Surname, u.Email,
                ud.jobTitleID, jt.jobTitle,
                un.unitName, un.headOfUnitID, ut.unitTypeName
                FROM user_details ud
                LEFT JOIN tija_user_unit_assignments ua ON ud.ID = ua.userID
                LEFT JOIN tija_units un ON ua.unitID = un.unitID
                LEFT JOIN tija_unit_types ut ON un.unitTypeID = ut.unitTypeID
                LEFT JOIN people u ON un.headOfUnitID = u.ID
                LEFT JOIN tija_job_titles jt ON u.ID = ud.jobTitleID
                WHERE ua.userID = ?
                AND ut.unitTypeName LIKE '%Department%'
                AND (ua.assignmentEndDate IS NULL OR ua.assignmentEndDate >= CURDATE())
                AND ud.Lapsed = 'N'
                AND ud.Suspended = 'N'
                AND ua.Lapsed = 'N'
                AND ua.Suspended = 'N'
                AND un.Lapsed = 'N'
                AND un.Suspended = 'N'
                ORDER BY ut.unitOrder ASC
                LIMIT 1";

        $params = array(array($employeeID, 'i'));
        $rows = $DBConn->fetch_all_rows($sql, $params);

        return ($rows && count($rows) > 0) ? $rows[0] : false;
    }

    /**
     * Get employee's active projects
     *
     * @param int $employeeID Employee ID
     * @param object $DBConn Database connection object
     * @return array Active projects
     */
    public static function get_employee_active_projects($employeeID, $DBConn) {
        $sql = "SELECT p.projectID, p.projectName, p.projectCode,
                p.projectOwnerID AS projectManagerID, p.projectStatus,
                CONCAT(pm.FirstName, ' ', pm.Surname) as projectManagerName
                FROM tija_projects p
                LEFT JOIN people pm ON p.projectOwnerID = pm.ID
                LEFT JOIN tija_project_team pa ON p.projectID = pa.projectID
                WHERE pa.userID = ?

                AND p.Lapsed = 'N'
                AND p.Suspended = 'N'
                AND pa.Lapsed = 'N'
                AND pa.Suspended = 'N'
                ORDER BY p.projectName ASC";

        $params = array(array($employeeID, 'i'));
        $rows = $DBConn->fetch_all_rows($sql, $params);

        return $rows ? $rows : array();
    }

    /**
     * Get employee bank accounts
     *
     * @param array $whereArr Parameters to filter bank accounts
     * @param bool $single Whether to return a single record or not
     * @param object $DBConn Database connection object
     * @return mixed Employee bank accounts or false on failure
     */
    public static function employee_bank_accounts($whereArr = array(), $single = false, $DBConn) {
        $params = array();
        $where = '';
        $rows = array();

        // Prepare the WHERE clause based on the provided conditions
        if (count($whereArr) > 0) {
            $i = 0;
            foreach ($whereArr as $col => $val) {
                if ($where == '') {
                    $where = "WHERE ";
                } else {
                    $where .= " AND ";
                }
                $where .= "{$col} = ?";
                $params[] = array($val, 's');
                $i++;
            }
        }

        $sql = "SELECT * FROM tija_employee_bank_accounts
                {$where}
                ORDER BY isPrimary DESC, DateAdded DESC";

        $rows = $DBConn->fetch_all_rows($sql, $params);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get employee certifications
     *
     * @param array $whereArr Parameters to filter certifications
     * @param bool $single Whether to return a single record or not
     * @param object $DBConn Database connection object
     * @return mixed Employee certifications or false on failure
     */
    public static function employee_certifications($whereArr = array(), $single = false, $DBConn) {
        $params = array();
        $where = '';
        $rows = array();

        // Prepare the WHERE clause based on the provided conditions
        if (count($whereArr) > 0) {
            $i = 0;
            foreach ($whereArr as $col => $val) {
                if ($where == '') {
                    $where = "WHERE ";
                } else {
                    $where .= " AND ";
                }
                $where .= "{$col} = ?";
                $params[] = array($val, 's');
                $i++;
            }
        }

        $sql = "SELECT * FROM tija_employee_certifications
                {$where}
                ORDER BY issueDate DESC";

        $rows = $DBConn->fetch_all_rows($sql, $params);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get employee licenses
     *
     * @param array $whereArr Parameters to filter licenses
     * @param bool $single Whether to return a single record or not
     * @param object $DBConn Database connection object
     * @return mixed Employee licenses or false on failure
     */
    public static function employee_licenses($whereArr = array(), $single = false, $DBConn) {
        $params = array();
        $where = '';
        $rows = array();

        // Prepare the WHERE clause based on the provided conditions
        if (count($whereArr) > 0) {
            $i = 0;
            foreach ($whereArr as $col => $val) {
                if ($where == '') {
                    $where = "WHERE ";
                } else {
                    $where .= " AND ";
                }
                $where .= "{$col} = ?";
                $params[] = array($val, 's');
                $i++;
            }
        }

        $sql = "SELECT * FROM tija_employee_licenses
                {$where}
                ORDER BY issueDate DESC";

        $rows = $DBConn->fetch_all_rows($sql, $params);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get benefit types
     *
     * @param array $whereArr Parameters to filter benefit types
     * @param bool $single Whether to return a single record or not
     * @param object $DBConn Database connection object
     * @return mixed Benefit types or false on failure
     */
    public static function benefit_types($whereArr = array(), $single = false, $DBConn) {
        $params = array();
        $where = '';
        $rows = array();

        // Prepare the WHERE clause based on the provided conditions
        if (count($whereArr) > 0) {
            $i = 0;
            foreach ($whereArr as $col => $val) {
                if ($where == '') {
                    $where = "WHERE ";
                } else {
                    $where .= " AND ";
                }
                $where .= "{$col} = ?";
                $params[] = array($val, 's');
                $i++;
            }
        }

        $sql = "SELECT * FROM tija_benefit_types
                {$where}
                ORDER BY sortOrder, benefitName";

        $rows = $DBConn->fetch_all_rows($sql, $params);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get additional supervisor relationships for an employee
     *
     * @param int $employeeID Employee ID
     * @param object $DBConn Database connection object
     * @return array Additional supervisor relationships
     */
    public static function get_additional_supervisors($employeeID, $DBConn) {
        if (!$employeeID || !$DBConn) {
            return [];
        }

        $sql = "SELECT sr.*,
               CONCAT(p.FirstName, ' ', p.Surname) as supervisorName,
               p.Email as supervisorEmail,
               p.profile_image as supervisorImage,
               ud.phoneNo as supervisorPhone,
               jt.jobTitle as supervisorJobTitle,
               bu.businessUnitName as supervisorBusinessUnit
        FROM tija_employee_supervisor_relationships sr
        LEFT JOIN people p ON sr.supervisorID = p.ID
        LEFT JOIN user_details ud ON p.ID = ud.ID
        LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
        LEFT JOIN tija_business_units bu ON ud.businessUnitID = bu.businessUnitID
        WHERE sr.employeeID = ?
          AND sr.Suspended = 'N'
        ORDER BY sr.isPrimary DESC, sr.relationshipType ASC";

        $params = [[$employeeID, 'i']];
        $rows = $DBConn->fetch_all_rows($sql, $params);

        return is_array($rows) ? $rows : [];
    }

    /**
     * Get additional subordinates (employees with this person as additional supervisor)
     *
     * @param int $supervisorID Supervisor ID
     * @param object $DBConn Database connection object
     * @return array Additional subordinate relationships
     */
    public static function get_additional_subordinates($supervisorID, $DBConn) {
        if (!$supervisorID || !$DBConn) {
            return [];
        }

        $sql = "SELECT sr.*,
               CONCAT(p.FirstName, ' ', p.Surname) as employeeName,
               p.Email as employeeEmail,
               p.profile_image as employeeImage,
               ud.phoneNo as employeePhone,
               jt.jobTitle as employeeJobTitle
        FROM tija_employee_supervisor_relationships sr
        LEFT JOIN people p ON sr.employeeID = p.ID
        LEFT JOIN user_details ud ON p.ID = ud.ID
        LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
        WHERE sr.supervisorID = ?
          AND sr.isPrimary = 'N'
          AND sr.isActive = 'Y'
          AND sr.Suspended = 'N'
        ORDER BY sr.relationshipType ASC, employeeName ASC";

        $params = [[$supervisorID, 'i']];
        $rows = $DBConn->fetch_all_rows($sql, $params);

        return is_array($rows) ? $rows : [];
    }

    /**
     * Get a supervisor relationship by ID
     *
     * @param int $relationshipID Relationship ID
     * @param object $DBConn Database connection object
     * @param bool $includeSuspended Whether to include suspended relationships
     * @return mixed Supervisor relationship object or false if not found
     */
    public static function get_supervisor_relationship($relationshipID, $DBConn, $includeSuspended = false) {
        if (!$relationshipID || !$DBConn) {
            return false;
        }

        $sql = "SELECT * FROM tija_employee_supervisor_relationships WHERE relationshipID = ?";
        if (!$includeSuspended) {
            $sql .= " AND Suspended = 'N'";
        }

        $params = [[$relationshipID, 'i']];
        $rows = $DBConn->fetch_all_rows($sql, $params);

        if (is_array($rows) && count($rows) > 0) {
            return $rows[0];
        }

        return false;
    }

    /**
     * Delete a supervisor relationship (soft delete)
     *
     * @param int $relationshipID Relationship ID
     * @param object $DBConn Database connection object
     * @return array Result array with 'success' and 'message' keys
     */
    public static function delete_supervisor_relationship($relationshipID, $DBConn) {
        if (!$relationshipID || !$DBConn) {
            return ['success' => false, 'message' => 'Relationship ID is required'];
        }

        // Get the relationship details before deleting (include suspended to check if exists)
        $relationship = self::get_supervisor_relationship($relationshipID, $DBConn, true);

        if (!$relationship) {
            return ['success' => false, 'message' => 'Supervisor relationship not found'];
        }

        // Soft delete
        $updateResult = $DBConn->update_table('tija_employee_supervisor_relationships',
            ['Suspended' => 'Y', 'isActive' => 'N'],
            ['relationshipID' => $relationshipID]);

        if ($updateResult === false) {
            return ['success' => false, 'message' => 'Failed to delete supervisor relationship'];
        }

        // If this was the primary supervisor, clear it from user_details
        if (isset($relationship->isPrimary) && $relationship->isPrimary == 'Y') {
            $DBConn->update_table('user_details',
                ['supervisorID' => null],
                ['ID' => $relationship->employeeID]);
        }

        return ['success' => true, 'message' => 'Supervisor relationship deleted successfully'];
    }
}?>