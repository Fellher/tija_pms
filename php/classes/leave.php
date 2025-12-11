<?php
/**
 * Leave class
 *
 * This class handles leave-related operations such as managing leave periods, leave types, and leave requests.
 *
 * @package    Leave Management System
 * @category   Leave
 * @version    1.0
 */
class Leave {
    private static $workflowSchemaChecked = false;
    private static $workflowHasStepApproverId = false;
    private static $approvalCommentsTableChecked = false;
    private static $manualBalancesTableChecked = false;
    private static $manualBalancesTableExists = false;

    private static function ensure_leave_comments_table($DBConn) {
        if (self::$approvalCommentsTableChecked || !isset($DBConn)) {
            return;
        }

        self::$approvalCommentsTableChecked = true;

        try {
            $tableExists = $DBConn->fetch_all_rows("SHOW TABLES LIKE 'tija_leave_approval_comments'", array());
            if ($tableExists && count($tableExists) > 0) {
                return;
            }

            $createSql = "
                CREATE TABLE IF NOT EXISTS `tija_leave_approval_comments` (
                    `commentID` INT(11) NOT NULL AUTO_INCREMENT,
                    `leaveApplicationID` INT(11) NOT NULL,
                    `approverID` INT(11) DEFAULT NULL,
                    `approverUserID` INT(11) DEFAULT NULL,
                    `approvalLevel` VARCHAR(50) DEFAULT NULL,
                    `comment` TEXT,
                    `commentType` VARCHAR(30) DEFAULT NULL,
                    `commentDate` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `Lapsed` CHAR(1) NOT NULL DEFAULT 'N',
                    `Suspended` CHAR(1) NOT NULL DEFAULT 'N',
                    PRIMARY KEY (`commentID`),
                    KEY `idx_comments_application` (`leaveApplicationID`),
                    KEY `idx_comments_approver` (`approverUserID`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ";
            $DBConn->query($createSql);
        } catch (Exception $e) {
        }
    }

    public static function ensure_leave_approval_comments_table($DBConn) {
        self::ensure_leave_comments_table($DBConn);
    }

    /**
     * Track workflow schema verification
     */
    private static function ensure_workflow_schema($DBConn) {
        if (self::$workflowSchemaChecked && self::$workflowHasStepApproverId) {
            return;
        }

        if (!isset($DBConn)) {
            return;
        }

        if (self::$workflowSchemaChecked && !self::$workflowHasStepApproverId) {
            // already attempted, avoid repeated work
            return;
        }

        self::$workflowSchemaChecked = true;

        try {
            $tableExists = $DBConn->fetch_all_rows("SHOW TABLES LIKE 'tija_leave_approval_step_approvers'", array());
            if (!$tableExists || count($tableExists) === 0) {
                self::$workflowHasStepApproverId = false;
                return;
            }

            $columnExists = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_step_approvers LIKE 'stepApproverID'", array());
            if ($columnExists && count($columnExists) > 0) {
                $columnInfo = is_object($columnExists[0]) ? (array)$columnExists[0] : $columnExists[0];
                $isPrimary = isset($columnInfo['Key']) && strtoupper($columnInfo['Key']) === 'PRI';
                $isAuto = isset($columnInfo['Extra']) && stripos($columnInfo['Extra'], 'auto_increment') !== false;

                if (!$isAuto) {
                    try {
                        $DBConn->query("ALTER TABLE tija_leave_approval_step_approvers MODIFY COLUMN stepApproverID INT(11) NOT NULL AUTO_INCREMENT");
                    } catch (Exception $e) {
                    }
                }

                if (!$isPrimary) {
                    try {
                        $existingPrimary = $DBConn->fetch_all_rows("SHOW INDEX FROM tija_leave_approval_step_approvers WHERE Key_name = 'PRIMARY'", array());
                        if ($existingPrimary && count($existingPrimary) > 0) {
                            $DBConn->query("ALTER TABLE tija_leave_approval_step_approvers DROP PRIMARY KEY");
                        }
                        $DBConn->query("ALTER TABLE tija_leave_approval_step_approvers ADD PRIMARY KEY (stepApproverID)");
                    } catch (Exception $e) {
                    }
                }

                self::$workflowHasStepApproverId = true;
                return;
            }

            // Column missing; attempt to add
            try {
                $DBConn->query("ALTER TABLE tija_leave_approval_step_approvers ADD COLUMN stepApproverID INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
                self::$workflowHasStepApproverId = true;
            } catch (Exception $primaryError) {
                try {
                    $DBConn->query("ALTER TABLE tija_leave_approval_step_approvers ADD COLUMN stepApproverID INT(11) NOT NULL FIRST");
                    self::$workflowHasStepApproverId = true;
                } catch (Exception $secondaryError) {
                    self::$workflowHasStepApproverId = false;
                }
            }
        } catch (Exception $outer) {
            self::$workflowHasStepApproverId = false;
        }
    }

    public static function leave_workflow_has_step_approver_id($DBConn) {
        self::ensure_workflow_schema($DBConn);
        return self::$workflowHasStepApproverId;
    }

    private static function setup_manual_balance_table($DBConn) {
        if (self::$manualBalancesTableChecked) {
            return self::$manualBalancesTableExists;
        }

        self::$manualBalancesTableChecked = true;
        self::$manualBalancesTableExists = false;

        if (!isset($DBConn)) {
            return false;
        }

        try {
            $tableExists = $DBConn->fetch_all_rows("SHOW TABLES LIKE 'tija_leave_manual_balances'", array());
            if ($tableExists && count($tableExists) > 0) {
                self::$manualBalancesTableExists = true;
                return true;
            }

            $createSql = "
                CREATE TABLE IF NOT EXISTS `tija_leave_manual_balances` (
                    `manualBalanceID` INT(11) NOT NULL AUTO_INCREMENT,
                    `employeeID` INT(11) NOT NULL,
                    `entityID` INT(11) NOT NULL,
                    `leaveTypeID` INT(11) NOT NULL,
                    `payrollNumber` VARCHAR(120) DEFAULT NULL,
                    `openingBalanceDays` DECIMAL(8,2) NOT NULL DEFAULT 0,
                    `asOfDate` DATE DEFAULT NULL,
                    `uploadBatch` VARCHAR(64) DEFAULT NULL,
                    `notes` VARCHAR(255) DEFAULT NULL,
                    `createdBy` INT(11) DEFAULT NULL,
                    `updatedBy` INT(11) DEFAULT NULL,
                    `createdDate` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `updatedDate` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    `Lapsed` CHAR(1) NOT NULL DEFAULT 'N',
                    `Suspended` CHAR(1) NOT NULL DEFAULT 'N',
                    PRIMARY KEY (`manualBalanceID`),
                    UNIQUE KEY `uniq_manual_balance_employee_leave` (`employeeID`,`leaveTypeID`),
                    KEY `idx_manual_balance_entity` (`entityID`),
                    KEY `idx_manual_balance_leave_type` (`leaveTypeID`),
                    KEY `idx_manual_balance_payroll` (`payrollNumber`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ";

            $DBConn->query($createSql);
            $DBConn->execute();
            self::$manualBalancesTableExists = true;
        } catch (Exception $e) {
            self::$manualBalancesTableExists = false;
        }

        return self::$manualBalancesTableExists;
    }

    /**
     * Ensure manual balance table exists (public facade)
     */
    public static function ensure_manual_balances_table($DBConn) {
        return self::setup_manual_balance_table($DBConn);
    }

    /**
     * Get manual balance override for employee/leave type
     */
    public static function get_manual_balance_entry($employeeID, $leaveTypeID, $DBConn) {
        if (!$employeeID || !$leaveTypeID) {
            return false;
        }

        if (!self::setup_manual_balance_table($DBConn)) {
            return false;
        }

        $sql = "SELECT *
                FROM tija_leave_manual_balances
                WHERE employeeID = ?
                AND leaveTypeID = ?
                AND Lapsed = 'N'
                AND Suspended = 'N'
                LIMIT 1";
        $params = array(
            array((int)$employeeID, 'i'),
            array((int)$leaveTypeID, 'i')
        );
        $rows = $DBConn->fetch_all_rows($sql, $params);
        return ($rows && count($rows) > 0) ? $rows[0] : false;
    }

    /**
     * Save or update manual balance override
     */
    public static function save_manual_balance_entry($employeeID, $entityID, $leaveTypeID, $balanceDays, $asOfDate, $payrollNumber, $uploadedByID, $uploadBatch, $DBConn) {
        if (!$employeeID || !$entityID || !$leaveTypeID) {
            throw new Exception('Invalid parameters supplied for manual balance save.');
        }

        if (!self::setup_manual_balance_table($DBConn)) {
            throw new Exception('Manual balance table is unavailable.');
        }

        $sql = "
            INSERT INTO `tija_leave_manual_balances`
                (`employeeID`, `entityID`, `leaveTypeID`, `payrollNumber`,
                 `openingBalanceDays`, `asOfDate`, `uploadBatch`, `createdBy`, `updatedBy`,
                 `Lapsed`, `Suspended`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'N', 'N')
            ON DUPLICATE KEY UPDATE
                `openingBalanceDays` = VALUES(`openingBalanceDays`),
                `asOfDate` = VALUES(`asOfDate`),
                `uploadBatch` = VALUES(`uploadBatch`),
                `payrollNumber` = VALUES(`payrollNumber`),
                `updatedBy` = VALUES(`updatedBy`),
                `updatedDate` = CURRENT_TIMESTAMP,
                `Lapsed` = 'N',
                `Suspended` = 'N'
        ";

        $DBConn->query($sql);
        $DBConn->bind(1, (int)$employeeID);
        $DBConn->bind(2, (int)$entityID);
        $DBConn->bind(3, (int)$leaveTypeID);
        $DBConn->bind(4, $payrollNumber);
        $DBConn->bind(5, (float)$balanceDays);
        $DBConn->bind(6, $asOfDate);
        $DBConn->bind(7, $uploadBatch);
        $DBConn->bind(8, $uploadedByID ? (int)$uploadedByID : null);
        $DBConn->bind(9, $uploadedByID ? (int)$uploadedByID : null);

        $DBConn->execute();
        return $DBConn->rowCount();
    }

    // Class methods will be added here
      /**
      * Get leave period details
      *
      * @param array $params Parameters to filter leave periods
      * @param bool $single Whether to return a single record or not
      * @param object $DBConn Database connection object
      * @return mixed Leave period details or false on failure
      */
      public static function leave_Periods($whereArr = array(), $single = false, $DBConn) {
         $cols= array("leavePeriodID", "leavePeriodName", "leavePeriodStartDate", "leavePeriodEndDate", "orgDataID", "entityID", "LastUpdate", "LastUpdateByID", "Lapsed", "Suspended");
         $rows = $DBConn->retrieve_db_table_rows('tija_leave_periods', $cols, $whereArr);
         return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);

      }
   /**
    * Get leave type details
      *
      * @param array $params Parameters to filter leave types
      * @param bool $single Whether to return a single record or not
      * @param object $DBConn Database connection object
      * @return mixed Leave type details or false on failure
      */
      public static function leave_types($whereArr = array(), $single = false, $DBConn) {
         $cols= array("leaveTypeID", "leaveTypeName", "leaveTypeDescription", 'leaveTypeCode',"LastUpdate", "LastUpdateByID", "Lapsed", "Suspended");
         $rows = $DBConn->retrieve_db_table_rows('tija_leave_types', $cols, $whereArr);
         return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);

      }

      public static function bradford_threshold($whereArr, $single, $DBConn) {
         $cols= array("bradfordFactorID", "bradfordFactorName", "bradfordFactorValue", "LastUpdate", "LastUpdateByID", "Lapsed", "Suspended");
         $rows = $DBConn->retrieve_db_table_rows('tija_bradford_factor', $cols, $whereArr);
         return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);

      }

      public static function leave_status($whereArr, $single= false, $DBConn) {
         $cols= array("leaveStatusID", 'DateAdded', "leaveStatusName", 'leaveStatusDescription',  "LastUpdate", "LastUpdateByID", "Lapsed", "Suspended");
         $rows = $DBConn->retrieve_db_table_rows('tija_leave_status', $cols, $whereArr);
         return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);

      }

      public static function leave_entitlement($whereArr, $single= false, $DBConn) {
         $cols= array("leaveEntitlementID", 'DateAdded', "leaveTypeID",  "entitlement", "maxDaysPerApplication", 'entityID', "orgDataID", "LastUpdate", "LastUpdateByID", "Lapsed", "Suspended");
         $rows = $DBConn->retrieve_db_table_rows('tija_leave_entitlement', $cols, $whereArr);
         return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);

      }
      public static function leave_entitlements ($whereArr, $single, $DBConn) {
         $params= array();
         $where= '';
         $rows=array();
        ;

         if (count($whereArr) > 0) {
            $i = 0;
            foreach ($whereArr as $col => $val) {
               if ($where == '') {
                  $where = "WHERE ";
               } else {
                  $where .= " AND ";
               }
               $where .= "e.{$col} = ?";
               $params[] = array($val, 's');
               $i++;
            }
         }

         $sql = "SELECT e.*,
         l.leaveTypeName, l.leaveTypeDescription, l.leaveSegment,
         s.entityName

         FROM tija_leave_entitlement e
         LEFT JOIN tija_leave_types l ON e.leaveTypeID = l.leaveTypeID
        LEFT JOIN tija_entities s ON e.entityID = s.entityID
         {$where}
         ORDER BY e.DateAdded DESC";
      $rows = $DBConn->fetch_all_rows($sql,$params);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
      }

      /**
       * Get entitlements filtered by scope
       *
       * @param int $entityID Entity ID (required for Entity and Cadre scope)
       * @param string $scope Policy scope (Global, Entity, Cadre)
       * @param int $jobCategoryID Job category ID (for Cadre scope)
       * @param int $jobBandID Job band ID (for Cadre scope)
       * @param bool $activeOnly Whether to return only active entitlements
       * @param object $DBConn Database connection object
       * @return array Array of entitlement objects
       */
      public static function get_entitlements_by_scope($entityID, $scope, $jobCategoryID = null, $jobBandID = null, $activeOnly = true, $DBConn) {
          $where = " WHERE e.policyScope = ?";
          $params = array(array($scope, 's'));

          if ($scope === 'Global') {
              // Global entitlements: parentEntityID = 0
              $where .= " AND (e.parentEntityID = 0 OR e.parentEntityID IS NULL)";
          } elseif ($scope === 'Entity') {
              // Entity entitlements: specific entityID
              if ($entityID) {
                  $where .= " AND e.entityID = ?";
                  $params[] = array($entityID, 'i');
              }
          } elseif ($scope === 'Cadre') {
              // Cadre entitlements: entityID + jobCategoryID or jobBandID
              if ($entityID) {
                  $where .= " AND e.entityID = ?";
                  $params[] = array($entityID, 'i');
              }
              if ($jobCategoryID) {
                  $where .= " AND e.jobCategoryID = ?";
                  $params[] = array($jobCategoryID, 'i');
              }
              if ($jobBandID) {
                  $where .= " AND e.jobBandID = ?";
                  $params[] = array($jobBandID, 'i');
              }
          }

          if ($activeOnly) {
              $where .= " AND e.Lapsed = 'N' AND e.Suspended = 'N'";
          }

          $sql = "SELECT e.*,
                  l.leaveTypeName, l.leaveTypeDescription, l.leaveSegment,
                  s.entityName
                  FROM tija_leave_entitlement e
                  LEFT JOIN tija_leave_types l ON e.leaveTypeID = l.leaveTypeID
                  LEFT JOIN tija_entities s ON e.entityID = s.entityID
                  $where
                  ORDER BY e.DateAdded DESC";

          $rows = $DBConn->fetch_all_rows($sql, $params);
          return (is_array($rows) && count($rows) > 0) ? $rows : false;
      }

      /**
       * Merge entitlements with precedence (cadre > entity > global)
       *
       * @param mixed $globalEntitlement Global entitlement object or null
       * @param mixed $entityEntitlement Entity entitlement object or null
       * @param mixed $cadreEntitlement Cadre entitlement object or null
       * @return mixed Merged entitlement object
       */
      public static function merge_entitlements($globalEntitlement, $entityEntitlement, $cadreEntitlement) {
          // Start with global entitlement as base
          $merged = $globalEntitlement ? (array)$globalEntitlement : array();

          // Apply entity overrides
          if ($entityEntitlement) {
              $entityArray = (array)$entityEntitlement;
              foreach ($entityArray as $key => $value) {
                  // Skip null values and scope-related fields
                  if ($value !== null && !in_array($key, ['policyScope', 'parentEntityID', 'jobCategoryID', 'jobBandID'])) {
                      $merged[$key] = $value;
                  }
              }
          }

          // Apply cadre overrides (highest precedence)
          if ($cadreEntitlement) {
              $cadreArray = (array)$cadreEntitlement;
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
       * Resolve entitlement hierarchy for a specific employee
       *
       * @param int $employeeID Employee ID
       * @param int $leaveTypeID Leave type ID
       * @param int $entityID Entity ID
       * @param object $DBConn Database connection object
       * @return mixed Resolved entitlement object or false if not found
       */
      public static function resolve_entitlement_for_employee($employeeID, $leaveTypeID, $entityID, $DBConn) {
          // Get employee details
          $employee = Employee::employees(array('ID' => $employeeID), true, $DBConn);
          if (!$employee) {
              return false;
          }

          $employeeEntityID = $employee->entityID ?? $entityID;
          $jobCategoryID = $employee->jobCategoryID ?? null;
          $jobBandID = $employee->jobBandID ?? null;

          // Get parent entity ID for global entitlements
          $parentEntityID = 0;
          if ($employeeEntityID) {
              $entitySql = "SELECT entityParentID FROM tija_entities WHERE entityID = ?";
              $entityParams = array(array($employeeEntityID, 'i'));
              $entityRows = $DBConn->fetch_all_rows($entitySql, $entityParams);
              if ($entityRows && count($entityRows) > 0) {
                  // Handle both object and array returns
                  $firstRow = $entityRows[0];
                  $parentEntityID = is_object($firstRow) ? ($firstRow->entityParentID ?? 0) : ($firstRow['entityParentID'] ?? 0);
              }
          }

          // Fetch entitlements in hierarchy order
          $globalEntitlement = null;
          $entityEntitlement = null;
          $cadreEntitlement = null;

          // 1. Get global entitlement (parentEntityID = 0)
          $globalSql = "SELECT e.*,
                       l.leaveTypeName, l.leaveTypeDescription, l.leaveSegment,
                       s.entityName
                       FROM tija_leave_entitlement e
                       LEFT JOIN tija_leave_types l ON e.leaveTypeID = l.leaveTypeID
                       LEFT JOIN tija_entities s ON e.entityID = s.entityID
                       WHERE e.policyScope = 'Global'
                       AND e.leaveTypeID = ?
                       AND (e.parentEntityID = 0 OR e.parentEntityID IS NULL)
                       AND e.Lapsed = 'N' AND e.Suspended = 'N'
                       ORDER BY e.DateAdded DESC
                       LIMIT 1";
          $globalParams = array(array($leaveTypeID, 'i'));
          $globalRows = $DBConn->fetch_all_rows($globalSql, $globalParams);
          if ($globalRows && count($globalRows) > 0) {
              $globalEntitlement = $globalRows[0];
          }

          // 2. Get entity entitlement
          if ($employeeEntityID) {
              $entitySql = "SELECT e.*,
                           l.leaveTypeName, l.leaveTypeDescription, l.leaveSegment,
                           s.entityName
                           FROM tija_leave_entitlement e
                           LEFT JOIN tija_leave_types l ON e.leaveTypeID = l.leaveTypeID
                           LEFT JOIN tija_entities s ON e.entityID = s.entityID
                           WHERE e.policyScope = 'Entity'
                           AND e.entityID = ?
                           AND e.leaveTypeID = ?
                           AND e.Lapsed = 'N' AND e.Suspended = 'N'
                           ORDER BY e.DateAdded DESC
                           LIMIT 1";
              $entityParams = array(
                  array($employeeEntityID, 'i'),
                  array($leaveTypeID, 'i')
              );
              $entityRows = $DBConn->fetch_all_rows($entitySql, $entityParams);
              if ($entityRows && count($entityRows) > 0) {
                  $entityEntitlement = $entityRows[0];
              }
          }

          // 3. Get cadre entitlement (jobCategoryID or jobBandID match)
          if ($employeeEntityID && ($jobCategoryID || $jobBandID)) {
              $cadreWhere = "e.policyScope = 'Cadre' AND e.entityID = ? AND e.leaveTypeID = ?";
              $cadreParams = array(
                  array($employeeEntityID, 'i'),
                  array($leaveTypeID, 'i')
              );

              if ($jobCategoryID) {
                  $cadreWhere .= " AND e.jobCategoryID = ?";
                  $cadreParams[] = array($jobCategoryID, 'i');
              }
              if ($jobBandID) {
                  $cadreWhere .= " AND e.jobBandID = ?";
                  $cadreParams[] = array($jobBandID, 'i');
              }

              $cadreSql = "SELECT e.*,
                          l.leaveTypeName, l.leaveTypeDescription, l.leaveSegment,
                          s.entityName
                          FROM tija_leave_entitlement e
                          LEFT JOIN tija_leave_types l ON e.leaveTypeID = l.leaveTypeID
                          LEFT JOIN tija_entities s ON e.entityID = s.entityID
                          WHERE $cadreWhere
                          AND e.Lapsed = 'N' AND e.Suspended = 'N'
                          ORDER BY e.DateAdded DESC
                          LIMIT 1";
              $cadreRows = $DBConn->fetch_all_rows($cadreSql, $cadreParams);
              if ($cadreRows && count($cadreRows) > 0) {
                  $cadreEntitlement = $cadreRows[0];
              }
          }

          // Merge entitlements with precedence
          $mergedEntitlement = self::merge_entitlements($globalEntitlement, $entityEntitlement, $cadreEntitlement);

          // If no entitlement found at any level, return false
          if (empty($mergedEntitlement) || (is_object($mergedEntitlement) && !isset($mergedEntitlement->leaveEntitlementID))) {
              return false;
          }

          return $mergedEntitlement;
      }

      /**
       * Get effective entitlement for an employee (alias for resolve_entitlement_for_employee)
       *
       * @param int $employeeID Employee ID
       * @param int $leaveTypeID Leave type ID
       * @param int $entityID Entity ID
       * @param object $DBConn Database connection object
       * @return mixed Effective entitlement object or false if not found
       */
      public static function get_effective_entitlement($employeeID, $leaveTypeID, $entityID, $DBConn) {
          return self::resolve_entitlement_for_employee($employeeID, $leaveTypeID, $entityID, $DBConn);
      }

    /**
     * Determine if a handover is required for a leave request.
     *
     * @param int $entityID
     * @param int $leaveTypeID
     * @param float $noOfDays
     * @param object $DBConn
     * @return array
     */
    public static function check_handover_requirement($entityID, $leaveTypeID, $noOfDays, $DBConn)
    {
        if (!class_exists('LeaveHandover')) {
            return array('required' => false, 'policy' => null);
        }

        return LeaveHandover::check_handover_policy($entityID, $leaveTypeID, $noOfDays, $DBConn);
    }

      public static function leave_applications($whereArr, $single= false, $DBConn) {
        $cols= array("leaveApplicationID", 'DateAdded', "leaveTypeID",  "leavePeriodID", "startDate", "endDate", "leaveStatusID", 'employeeID', 'leaveComments',  'leaveFiles', 'leaveEntitlementID', 'orgDataID', 'entityID', 'noOfDays', "LastUpdate", "LastUpdateByID", "Lapsed", "Suspended", "emergencyContact", "handoverNotes", "handoverRequired", "handoverStatus", "handoverCompletedDate", "createdBy", "createdDate", "modifiedBy", "modifiedDate", "halfDayLeave", "halfDayPeriod", "dateApplied", "appliedByID");
         $rows = $DBConn->retrieve_db_table_rows('tija_leave_applications', $cols, $whereArr);
         return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);

      }

      public static function leave_applications_full($whereArr, $single= false, $DBConn) {
         $params= array();
         $where= '';
         $rows=array();


         // Prepare the WHERE clause based on the provided conditions
         // This will build the SQL query dynamically based on the $whereArr

         if (count($whereArr) > 0) {
            $i = 0;
            foreach ($whereArr as $col => $val) {
               if ($where == '') {
                  $where = "WHERE ";
               } else {
                  $where .= " AND ";
               }
               $where .= "e.{$col} = ?";
               $params[] = array($val, 's');
               $i++;
            }
         }

       $sql = "SELECT e.leaveApplicationID, e.DateAdded, e.leaveTypeID, e.leavePeriodID, e.startDate, e.endDate, e.leaveStatusID, e.employeeID, e.leaveFiles, e.leaveComments, e.noOfDays, e.entityID, e.orgDataID, e.LastUpdate, e.LastUpdateByID, e.Lapsed, e.Suspended, e.emergencyContact, e.handoverNotes, e.handoverRequired, e.handoverStatus, e.handoverCompletedDate, e.createdBy, e.createdDate, e.modifiedBy, e.modifiedDate, e.halfDayLeave, e.halfDayPeriod, e.dateApplied, e.appliedByID, le.leaveEntitlementID, le.entitlement, le.entityID, le.orgDataID,
         l.leaveTypeName, l.leaveTypeDescription, s.entityName,
         st.leaveStatusName, st.leaveStatusDescription, p.leavePeriodName, p.leavePeriodStartDate, p.leavePeriodEndDate,
         CONCAT(u.FirstName, ' ', u.Surname) AS employeeName, u.Email

         FROM tija_leave_applications e
         LEFT JOIN tija_leave_types l ON e.leaveTypeID = l.leaveTypeID
         LEFT JOIN tija_leave_entitlement le ON e.leaveEntitlementID = le.leaveEntitlementID
         LEFT JOIN tija_entities s ON le.entityID = s.entityID

         LEFT JOIN tija_leave_status st ON e.leaveStatusID = st.leaveStatusID
         LEFT JOIN tija_leave_periods p ON e.leavePeriodID = p.leavePeriodID
         LEFT JOIN people u ON e.employeeID = u.ID
         {$where}
         ORDER BY e.DateAdded DESC";
         $rows = $DBConn->fetch_all_rows($sql,$params);
         return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);

      }

      public static function countWeekdays($startDate, $endDate) {
         $start = new DateTime($startDate);
         $end = new DateTime($endDate);
         $end = $end->modify('+1 day'); // Include the end date in the range

         $interval = new DateInterval('P1D'); // 1 day interval
         $dateRange = new DatePeriod($start, $interval, $end);

         $weekdayCount = 0;
         foreach ($dateRange as $date) {
             if ($date->format('N') < 6) { // 1 (Monday) to 5 (Friday)
                 $weekdayCount++;
             }
         }
         return $weekdayCount;
     }

   //   get array of weekdays
      public static function getWeekdays($startDate, $endDate) {
         $start = new DateTime($startDate);
         $end = new DateTime($endDate);
         $end = $end->modify('+1 day'); // Include the end date in the range

         $interval = new DateInterval('P1D'); // 1 day interval
         $dateRange = new DatePeriod($start, $interval, $end);

         $weekdays = [];
         foreach ($dateRange as $date) {
             if ($date->format('N') < 6) { // 1 (Monday) to 5 (Friday)
               //   $weekdays[] = $date->format('Y-m-d');
             $weekdays[] = (object)['date'=>$date->format('Y-m-d'), 'day'=>$date->format('l')];
             }
         }
         return $weekdays;
     }

     public static function leave_approvals($whereArr, $single, $DBConn) {
      $params= array();
      $where= '';
      $rows=array();
      $leaveApproval = array(
         'leaveApprovalID', 'DateAdded', 'leaveApplicationID', 'employeeID', 'leaveTypeID', 'leavePeriodID', 'leaveApproverID', 'leaveDate', 'LastUpdate', 'leaveStatus', 'leaveStatusID', 'approversComments', 'LastUpdateByID', 'LastUpdate', 'Lapsed', 'Suspended'
      );
      $leaveApplications = array(
         "leaveApplicationID", 'DateAdded', "leaveTypeID",  "leavePeriodID", "startDate", "endDate", "leaveStatusID", 'employeeID', 'leaveComments',  'leaveFiles', 'leaveEntitlementID', 'orgDataID', 'entityID', 'noOfDays'
      );
      $leaveTypes = array(
         "leaveTypeID", "leaveTypeName", "leaveTypeDescription", 'leaveTypeCode'
      );
      $leavePeriods = array(
         "leavePeriodID", "leavePeriodName", "leavePeriodStartDate", "leavePeriodEndDate"
      );

      // Prepare the WHERE clause based on the provided conditions
      if (count($whereArr) > 0) {
         $i = 0;
         foreach ($whereArr as $col => $val) {
            if ($where == '') {
               $where = "WHERE ";
            } else {
               $where .= " AND ";
            }

            // check if the column is in the leaveApproval array
            if (in_array($col, $leaveApproval)) {
               $where .= "a.{$col} = ?"; // Use alias 'a' for leave_approvals
            } elseif (in_array($col, $leaveApplications)) {
               $where .= "la.{$col} = ?"; // Use alias 'la' for leave_applications
            } elseif (in_array($col, $leaveTypes)) {
               $where .= "lt.{$col} = ?"; // Use alias 'lt' for leave_types
            } elseif (in_array($col, $leavePeriods)) {
               $where .= "lp.{$col} = ?"; // Use alias 'lp' for leave_periods
            } else {
               // If the column is not in any of the arrays, you can choose to skip it or handle it differently
               continue; // Skip this column
            }
            // $where .= "e.{$col} = ?";
            $params[] = array($val, 's');
            $i++;
         }
      }
      // create the SQL query
      $sql = "SELECT a.leaveApprovalID, a.DateAdded AS approvalDateAdded, a.leaveApplicationID, a.employeeID AS approverID, a.leaveTypeID, a.leavePeriodID, a.leaveApproverID, a.leaveDate, a.LastUpdate AS approvalLastUpdate, a.leaveStatus, a.leaveStatusID, a.approversComments, a.LastUpdateByID AS approvalLastUpdateByID, a.Lapsed AS approvalLapsed, a.Suspended AS approvalSuspended,
      la.leaveApplicationID, la.DateAdded AS applicationDateAdded, la.leaveTypeID AS applicationLeaveTypeID, la.leavePeriodID AS applicationLeavePeriodID, la.startDate, la.endDate, la.leaveStatusID AS applicationLeaveStatusID, la.employeeID AS applicationEmployeeID, la.leaveComments, la.leaveFiles, la.leaveEntitlementID, la.orgDataID, la.entityID, la.noOfDays,
      lt.leaveTypeID AS typeLeaveTypeID, lt.leaveTypeName, lt.leaveTypeDescription, lt.leaveTypeCode,
      lp.leavePeriodID, lp.leavePeriodName, lp.leavePeriodStartDate, lp.leavePeriodEndDate,
      CONCAT(u.FirstName, ' ', u.Surname) AS approverName, u.Email AS approverEmail
      FROM tija_leave_approvals a
      LEFT JOIN tija_leave_applications la ON a.leaveApplicationID = la.leaveApplicationID
      LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
      LEFT JOIN tija_leave_periods lp ON la.leavePeriodID = lp.leavePeriodID
      LEFT JOIN people u ON a.leaveApproverID = u.ID
      {$where}
      ORDER BY a.DateAdded DESC";
      $rows = $DBConn->fetch_all_rows($sql,$params);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);



     }

    // ============================================================================
    // ENHANCED LEAVE MANAGEMENT METHODS
    // ============================================================================

    /**
     * Get current leave period for an entity
     *
     * @param int $entityID Entity ID
     * @param object $DBConn Database connection object
     * @return mixed Current leave period or false on failure
     */
    public static function get_current_leave_period($entityID, $DBConn) {
        $currentDate = date('Y-m-d');

        // Check if entityID column exists in the table
        $tableCheck = "SHOW COLUMNS FROM tija_leave_periods LIKE 'entityID'";
        $columnExists = $DBConn->fetch_all_rows($tableCheck, array());

        if ($columnExists && count($columnExists) > 0) {
            // Use entity-specific filtering
            // First try to find period where current date is between start and end
            $sql = "SELECT * FROM tija_leave_periods
                    WHERE entityID = ?
                    AND leavePeriodStartDate <= ?
                    AND leavePeriodEndDate >= ?
                    AND Lapsed = 'N'
                    AND Suspended = 'N'
                    ORDER BY leavePeriodStartDate DESC LIMIT 1";

            $params = array(
                array($entityID, 'i'),
                array($currentDate, 's'),
                array($currentDate, 's')
            );

            $rows = $DBConn->fetch_all_rows($sql, $params);

            if ($rows && count($rows) > 0) {
                return $rows[0];
            }

            // If no current period found, get the most recent period for this entity
            $sql = "SELECT * FROM tija_leave_periods
                    WHERE entityID = ?
                    AND Lapsed = 'N'
                    AND Suspended = 'N'
                    ORDER BY leavePeriodStartDate DESC LIMIT 1";

            $params = array(array($entityID, 'i'));
            $rows = $DBConn->fetch_all_rows($sql, $params);

            return ($rows && count($rows) > 0) ? $rows[0] : false;
        } else {
            // Fallback: use original query without entityID filtering
            // First try to find period where current date is between start and end
            $sql = "SELECT * FROM tija_leave_periods
                    WHERE leavePeriodStartDate <= ?
                    AND leavePeriodEndDate >= ?
                    AND Lapsed = 'N'
                    AND Suspended = 'N'
                    ORDER BY leavePeriodStartDate DESC LIMIT 1";

            $params = array(
                array($currentDate, 's'),
                array($currentDate, 's')
            );

            $rows = $DBConn->fetch_all_rows($sql, $params);

            if ($rows && count($rows) > 0) {
                return $rows[0];
            }

            // If no current period found, get the most recent period
            $sql = "SELECT * FROM tija_leave_periods
                    WHERE Lapsed = 'N'
                    AND Suspended = 'N'
                    ORDER BY leavePeriodStartDate DESC LIMIT 1";

            $params = array();
            $rows = $DBConn->fetch_all_rows($sql, $params);

            return ($rows && count($rows) > 0) ? $rows[0] : false;
        }
    }

    /**
     * Get leave accumulation policy for an entity
     *
     * @param int $entityID Entity ID
     * @param object $DBConn Database connection object
     * @return mixed Leave accumulation policy or false on failure
     */
    public static function get_leave_accumulation_policy($entityID, $DBConn) {
        // Check if the table exists first
        $tableCheck = "SHOW TABLES LIKE 'tija_leave_accumulation_policies'";
        $tableExists = $DBConn->fetch_all_rows($tableCheck, array());

        if (!$tableExists || count($tableExists) == 0) {
            // Table doesn't exist, return false
            return false;
        }

        $sql = "SELECT * FROM tija_leave_accumulation_policies
                WHERE entityID = ?
                AND Lapsed = 'N'
                AND Suspended = 'N'
                ORDER BY DateAdded DESC LIMIT 1";

        $params = array(array($entityID, 'i'));
        $rows = $DBConn->fetch_all_rows($sql, $params);

        return ($rows && count($rows) > 0) ? $rows[0] : false;
    }

    /**
     * Calculate leave balances for an employee
     *
     * @param int $employeeID Employee ID
     * @param int $entityID Entity ID
     * @param object $DBConn Database connection object
     * @return array Leave balances
     */
    public static function calculate_leave_balances($employeeID, $entityID, $DBConn) {
        $balances = array();

        // Get all leave types
        $leaveTypes = self::leave_types(array('Lapsed' => 'N'), false, $DBConn);
        $manualOverridesAvailable = self::ensure_manual_balances_table($DBConn);

        if ($leaveTypes) {
            foreach ($leaveTypes as $leaveType) {
                // Resolve entitlement for this employee and leave type using hierarchy
                $entitlement = self::resolve_entitlement_for_employee($employeeID, $leaveType->leaveTypeID, $entityID, $DBConn);
                $manualEntry = $manualOverridesAvailable
                    ? self::get_manual_balance_entry($employeeID, $leaveType->leaveTypeID, $DBConn)
                    : false;

                if (!$entitlement && !$manualEntry) {
                    continue;
                }

                //convert leaveTypeName to lowercase and replace spaces with underscore
                $typeName = $entitlement->leaveTypeName ?? $leaveType->leaveTypeName ?? 'leave';
                $type = strtolower(str_replace(' ', '_', $typeName));

                $source = 'policy';
                $entitlementDays = 0;
                $openingBalance = null;
                $asOfDate = null;

                if ($manualEntry) {
                    $entitlementDays = isset($manualEntry->openingBalanceDays)
                        ? (float)$manualEntry->openingBalanceDays
                        : 0;
                    $source = 'manual_upload';
                    $openingBalance = $entitlementDays;
                    $asOfDate = $manualEntry->asOfDate ?? null;
                } elseif ($entitlement) {
                    $entitlementDays = isset($entitlement->entitlement) ? (float)$entitlement->entitlement : 0;
                }

                // Calculate used days from applications
                $usedDays = self::calculate_used_leave_days($employeeID, $leaveType->leaveTypeID, $DBConn);

                $balances[$type] = array(
                    'total' => $entitlementDays,
                    'used' => $usedDays,
                    'available' => max(0, $entitlementDays - $usedDays),
                    'percentage' => $entitlementDays > 0 ?
                        ($usedDays / $entitlementDays) * 100 : 0,
                    'source' => $source,
                    'as_of' => $asOfDate,
                    'opening_balance' => $openingBalance
                );
            }
        }

        return $balances;
    }

    /**
     * Calculate used leave days for an employee and leave type
     *
     * @param int $employeeID Employee ID
     * @param int $leaveTypeID Leave type ID
     * @param object $DBConn Database connection object
     * @return int Used leave days
     */
    public static function calculate_used_leave_days($employeeID, $leaveTypeID, $DBConn) {


        $sql = "SELECT SUM(noOfDays) as totalDays
                FROM tija_leave_applications
                WHERE employeeID = ?
                AND leaveTypeID = ?
                AND leaveStatusID IN (3, 4)
                AND Lapsed = 'N'
                AND Suspended = 'N'";

        $params = array(
            array($employeeID, 'i'),
            array($leaveTypeID, 'i')
        );

        $rows = $DBConn->fetch_all_rows($sql, $params);

        return ($rows && count($rows) > 0) ? ($rows[0]->totalDays ?? 0) : 0;
    }

    /**
     * Calculate working days excluding weekends and holidays
     *
     * @param string $startDate Start date
     * @param string $endDate End date
     * @param int $entityID Entity ID
     * @param object $DBConn Database connection object
     * @return int Working days
     */
    public static function calculate_working_days($startDate, $endDate, $entityID, $DBConn) {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $workingDays = 0;

        // Get holidays for the entity
        $holidays = self::get_global_holidays('Kenya', null, $DBConn);
        $holidayDates = array();

        if ($holidays) {
            foreach ($holidays as $holiday) {
                $holidayDates[] = $holiday->holidayDate;
            }
        }

        $current = clone $start;
        while ($current <= $end) {
            $dayOfWeek = $current->format('N');
            $dateString = $current->format('Y-m-d');

            // Skip weekends (Saturday = 6, Sunday = 7)
            if ($dayOfWeek < 6) {
                // Skip holidays
                if (!in_array($dateString, $holidayDates)) {
                    $workingDays++;
                }
            }

            $current->add(new DateInterval('P1D'));
        }

        return $workingDays;
    }

    /**
     * Get global holidays for a jurisdiction
     *
     * @param string $country Country code
     * @param string $state State/region (optional)
     * @param object $DBConn Database connection object
     * @return array Global holidays
     */
    public static function get_global_holidays($country, $state = null, $DBConn) {
        // Check if the table exists first
        $tableCheck = "SHOW TABLES LIKE 'tija_global_holidays'";
        $tableExists = $DBConn->fetch_all_rows($tableCheck, array());

        if (!$tableExists || count($tableExists) == 0) {
            // Table doesn't exist, return empty array
            return array();
        }

        $where = "WHERE (jurisdiction = ? OR jurisdiction = 'Global')";
        $params = array(array($country, 's'));

        if ($state) {
            $where .= " OR jurisdiction = ?";
            $params[] = array($state, 's');
        }

        $sql = "SELECT * FROM tija_global_holidays
                {$where}
                AND holidayDate >= CURDATE()
                AND Lapsed = 'N'
                AND Suspended = 'N'
                ORDER BY holidayDate ASC";

        $rows = $DBConn->fetch_all_rows($sql, $params);

        return $rows ? $rows : array();
    }

    /**
     * Get blackout periods for an entity
     *
     * @param int $entityID Entity ID
     * @param object $DBConn Database connection object
     * @return array Blackout periods
     */
    public static function get_blackout_periods($entityID, $DBConn) {
        // Check if the table exists first
        $tableCheck = "SHOW TABLES LIKE 'tija_leave_blackout_periods'";
        $tableExists = $DBConn->fetch_all_rows($tableCheck, array());

        if (!$tableExists || count($tableExists) == 0) {
            // Table doesn't exist, return empty array
            return array();
        }

        $sql = "SELECT * FROM tija_leave_blackout_periods
                WHERE entityID = ?
                AND Lapsed = 'N'
                AND Suspended = 'N'
                AND (endDate >= CURDATE())
                ORDER BY startDate ASC";

        $params = array(array($entityID, 'i'));
        $rows = $DBConn->fetch_all_rows($sql, $params);

        return $rows ? $rows : array();
    }

    /**
     * Get approval workflow for an employee
     *
     * @param int $employeeID Employee ID
     * @param int $entityID Entity ID
     * @param object $DBConn Database connection object
     * @return array Approval workflow
     */
    public static function get_approval_workflow($employeeID, $entityID, $DBConn) {
        // Get employee details to determine hierarchy
        $employee = Employee::employees(array('ID' => $employeeID), true, $DBConn);

        if (!$employee) {
            return array();
        }

        $workflow = array();

        // Direct report approval
        if ($employee->supervisorID) {
            $workflow[] = (object)array(
                'approverID' => $employee->supervisorID,
                'approvalLevel' => 1,
                'approvalType' => 'direct_report'
            );
        }

        // Department head approval (if different from supervisor)
        $departmentHead = Employee::get_employee_department_head($employeeID, $DBConn);
        if ($departmentHead && $departmentHead->ID != $employee->supervisorID) {
            $workflow[] = (object)array(
                'approverID' => $departmentHead->ID,
                'approvalLevel' => 2,
                'approvalType' => 'department_head'
            );
        }

        // HR approval
        $hrManager = Employee::get_hr_manager($employee->orgDataID, $entityID, $DBConn);
        if ($hrManager) {
            $workflow[] = (object)array(
                'approverID' => $hrManager->ID,
                'approvalLevel' => 3,
                'approvalType' => 'hr_manager'
            );
        }

        return $workflow;
    }

    /**
     * Get leave analytics for an employee
     *
     * @param int $employeeID Employee ID
     * @param object $DBConn Database connection object
     * @return array Leave analytics
     */
    public static function get_leave_analytics($employeeID, $DBConn) {
        $analytics = array(
            'totalLeaveDays' => 0,
            'leaveUtilization' => 0,
            'averageDuration' => 0,
            'totalApplications' => 0,
            'approvalRate' => 0,
            'peakLeaveMonths' => '',
            'averageAdvanceNotice' => 0
        );

        // Get current year applications using custom query
        $currentYear = date('Y');
        $sql = "SELECT e.leaveApplicationID, e.DateAdded, e.leaveTypeID, e.leavePeriodID, e.startDate, e.endDate, e.leaveStatusID, e.employeeID, e.leaveFiles, e.leaveComments, e.noOfDays, e.entityID, e.orgDataID, e.LastUpdate, e.LastUpdateByID, e.Lapsed, e.Suspended, e.emergencyContact, e.handoverNotes, e.handoverRequired, e.handoverStatus, e.handoverCompletedDate, e.createdBy, e.createdDate, e.modifiedBy, e.modifiedDate, e.halfDayLeave, e.halfDayPeriod, e.dateApplied, e.appliedByID, le.leaveEntitlementID, le.entitlement, le.entityID, le.orgDataID,
                l.leaveTypeName, l.leaveTypeDescription, s.entityName,
                st.leaveStatusName, st.leaveStatusDescription, p.leavePeriodName, p.leavePeriodStartDate, p.leavePeriodEndDate,
                CONCAT(u.FirstName, ' ', u.Surname) AS employeeName, u.Email
                FROM tija_leave_applications e
                LEFT JOIN tija_leave_types l ON e.leaveTypeID = l.leaveTypeID
                LEFT JOIN tija_leave_entitlement le ON e.leaveEntitlementID = le.leaveEntitlementID
                LEFT JOIN tija_entities s ON le.entityID = s.entityID
                LEFT JOIN tija_leave_status st ON e.leaveStatusID = st.leaveStatusID
                LEFT JOIN tija_leave_periods p ON e.leavePeriodID = p.leavePeriodID
                LEFT JOIN people u ON e.employeeID = u.ID
                WHERE e.employeeID = ?
                AND YEAR(e.startDate) = ?
                AND e.Lapsed = 'N'
                AND e.Suspended = 'N'
                ORDER BY e.DateAdded DESC";

        $params = array(
            array($employeeID, 'i'),
            array($currentYear, 's')
        );

        $applications = $DBConn->fetch_all_rows($sql, $params);

        if ($applications) {
            $totalDays = 0;
            $approvedApplications = 0;
            $totalApplications = count($applications);
            $advanceNoticeTotal = 0;
            $monthlyData = array();

            foreach ($applications as $app) {
                $totalDays += $app->noOfDays ?? 0;

                if ($app->leaveStatusID == 4) { // Approved
                    $approvedApplications++;
                }

                // Calculate advance notice
                $startDate = new DateTime($app->startDate);
                $appliedDate = new DateTime($app->DateAdded);
                $advanceNotice = $startDate->diff($appliedDate)->days;
                $advanceNoticeTotal += $advanceNotice;

                // Track monthly data
                $month = date('F', strtotime($app->startDate));
                if (!isset($monthlyData[$month])) {
                    $monthlyData[$month] = 0;
                }
                $monthlyData[$month] += $app->noOfDays ?? 0;
            }

            $analytics['totalLeaveDays'] = $totalDays;
            $analytics['totalApplications'] = $totalApplications;
            $analytics['approvalRate'] = $totalApplications > 0 ?
                round(($approvedApplications / $totalApplications) * 100, 1) : 0;
            $analytics['averageDuration'] = $totalApplications > 0 ?
                round($totalDays / $totalApplications, 1) : 0;
            $analytics['averageAdvanceNotice'] = $totalApplications > 0 ?
                round($advanceNoticeTotal / $totalApplications, 1) : 0;

            // Find peak months
            if (!empty($monthlyData)) {
                arsort($monthlyData);
                $analytics['peakLeaveMonths'] = implode(', ', array_slice(array_keys($monthlyData), 0, 2));
            }
        }

        return $analytics;
    }

    /**
     * Get filtered analytics data
     *
     * @param int $employeeID Employee ID
     * @param string $timePeriod Time period (month, quarter, year)
     * @param object $DBConn Database connection object
     * @return array Filtered analytics
     */
    public static function get_filtered_analytics($employeeID, $timePeriod, $DBConn) {
        $analytics = array(
            'monthlyLabels' => array(),
            'monthlyData' => array(),
            'leaveTypeLabels' => array(),
            'leaveTypeData' => array(),
            'monthlyApplications' => array(),
            'monthlyDays' => array()
        );

        // Determine date range based on time period
        $endDate = date('Y-m-d');
        switch ($timePeriod) {
            case 'month':
                $startDate = date('Y-m-d', strtotime('-1 month'));
                break;
            case 'quarter':
                $startDate = date('Y-m-d', strtotime('-3 months'));
                break;
            case 'year':
            default:
                $startDate = date('Y-m-d', strtotime('-1 year'));
                break;
        }

        // Get applications within date range using custom query
        $sql = "SELECT e.leaveApplicationID, e.DateAdded, e.leaveTypeID, e.leavePeriodID, e.startDate, e.endDate, e.leaveStatusID, e.employeeID, e.leaveFiles, e.leaveComments, e.noOfDays, e.entityID, e.orgDataID, e.LastUpdate, e.LastUpdateByID, e.Lapsed, e.Suspended, e.emergencyContact, e.handoverNotes, e.handoverRequired, e.handoverStatus, e.handoverCompletedDate, e.createdBy, e.createdDate, e.modifiedBy, e.modifiedDate, e.halfDayLeave, e.halfDayPeriod, e.dateApplied, e.appliedByID, le.leaveEntitlementID, le.entitlement, le.entityID, le.orgDataID,
                l.leaveTypeName, l.leaveTypeDescription, s.entityName,
                st.leaveStatusName, st.leaveStatusDescription, p.leavePeriodName, p.leavePeriodStartDate, p.leavePeriodEndDate,
                CONCAT(u.FirstName, ' ', u.Surname) AS employeeName, u.Email
                FROM tija_leave_applications e
                LEFT JOIN tija_leave_types l ON e.leaveTypeID = l.leaveTypeID
                LEFT JOIN tija_leave_entitlement le ON e.leaveEntitlementID = le.leaveEntitlementID
                LEFT JOIN tija_entities s ON le.entityID = s.entityID
                LEFT JOIN tija_leave_status st ON e.leaveStatusID = st.leaveStatusID
                LEFT JOIN tija_leave_periods p ON e.leavePeriodID = p.leavePeriodID
                LEFT JOIN people u ON e.employeeID = u.ID
                WHERE e.employeeID = ?
                AND e.startDate >= ?
                AND e.startDate <= ?
                AND e.Lapsed = 'N'
                AND e.Suspended = 'N'
                ORDER BY e.DateAdded DESC";

        $params = array(
            array($employeeID, 'i'),
            array($startDate, 's'),
            array($endDate, 's')
        );

        $applications = $DBConn->fetch_all_rows($sql, $params);

        if ($applications) {
            $monthlyData = array();
            $leaveTypeData = array();
            $monthlyApps = array();

            foreach ($applications as $app) {
                $month = date('M Y', strtotime($app->startDate));
                $leaveType = $app->leaveTypeName;

                // Monthly data
                if (!isset($monthlyData[$month])) {
                    $monthlyData[$month] = 0;
                    $monthlyApps[$month] = 0;
                }
                $monthlyData[$month] += $app->noOfDays ?? 0;
                $monthlyApps[$month]++;

                // Leave type data
                if (!isset($leaveTypeData[$leaveType])) {
                    $leaveTypeData[$leaveType] = 0;
                }
                $leaveTypeData[$leaveType] += $app->noOfDays ?? 0;
            }

            $analytics['monthlyLabels'] = array_keys($monthlyData);
            $analytics['monthlyData'] = array_values($monthlyData);
            $analytics['monthlyApplications'] = array_values($monthlyApps);
            $analytics['monthlyDays'] = array_values($monthlyData);
            $analytics['leaveTypeLabels'] = array_keys($leaveTypeData);
            $analytics['leaveTypeData'] = array_values($leaveTypeData);
        }

        return $analytics;
    }

    /**
     * Get leave application details by ID
     *
     * @param int $applicationId Application ID
     * @param object $DBConn Database connection object
     * @return mixed Application details or false on failure
     */
    public static function get_leave_application_details($applicationId, $DBConn) {
        return self::leave_applications_full(
            array('leaveApplicationID' => $applicationId),
            true,
            $DBConn
        );
    }

    /**
     * Get comprehensive leave application details including employee, supervisor, and documents
     *
     * @param int $applicationId Application ID
     * @param object $DBConn Database connection object
     * @return mixed Comprehensive application details or false on failure
     */
    public static function get_leave_application_full_details($applicationId, $DBConn) {
        $sql = "SELECT
                    la.*,
                    lt.leaveTypeName,
                    ls.leaveStatusName,
                    e.FirstName,
                    e.Surname,
                    e.Email,
                    CONCAT(e.FirstName, ' ', e.Surname) as employeeName,
                    ud.jobTitleID,
                    jt.jobTitle,
                    ud.employmentStartDate,
                    ud.supervisorID,
                    ud.businessUnitID,
                    bu.businessUnitName as departmentName,
                    supervisor.FirstName as supervisorFirstName,
                    supervisor.Surname as supervisorSurname,
                    CONCAT(supervisor.FirstName, ' ', supervisor.Surname) as supervisorName
                FROM tija_leave_applications la
                LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
                LEFT JOIN tija_leave_status ls ON la.leaveStatusID = ls.leaveStatusID
                LEFT JOIN people e ON la.employeeID = e.ID
                LEFT JOIN user_details ud ON e.ID = ud.ID
                LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
                LEFT JOIN tija_business_units bu ON ud.businessUnitID = bu.businessUnitID
                LEFT JOIN people supervisor ON ud.supervisorID = supervisor.ID
                WHERE la.leaveApplicationID = ?
                AND la.Lapsed = 'N'
                AND la.Suspended = 'N'";

        $params = array(array($applicationId, 'i'));
        $rows = $DBConn->fetch_all_rows($sql, $params);

        return ($rows && count($rows) > 0) ? $rows[0] : false;
    }

    /**
     * Get supporting documents for a leave application
     *
     * @param int $applicationId Application ID
     * @param object $DBConn Database connection object
     * @return array Supporting documents
     */
    public static function get_leave_application_documents($applicationId, $DBConn) {
        // Check if dedicated documents table exists
        $tableCheck = "SHOW TABLES LIKE 'tija_leave_documents'";
        $tableExists = $DBConn->fetch_all_rows($tableCheck, array());

        if ($tableExists && count($tableExists) > 0) {
            // Use dedicated documents table (preferred method)
            $sql = "SELECT
                        ld.documentID,
                        ld.fileName,
                        ld.filePath,
                        ld.fileSize,
                        ld.fileType,
                        ld.uploadDate,
                        ld.documentType,
                        ld.uploadedByID
                    FROM tija_leave_documents ld
                    WHERE ld.leaveApplicationID = ?
                    AND ld.Lapsed = 'N'
                    ORDER BY ld.uploadDate ASC";

            $params = array(array($applicationId, 'i'));
            $rows = $DBConn->fetch_all_rows($sql, $params);

            return $rows ? $rows : array();
        } else {
            // Fallback: Use leaveFiles column from tija_leave_applications (legacy method)
            $application = self::leave_applications(array('leaveApplicationID' => $applicationId), true, $DBConn);

            if (!$application || empty($application->leaveFiles)) {
                return array();
            }

            // Decode base64 if encoded, otherwise use as-is
            $leaveFiles = $application->leaveFiles;
            if (base64_decode($leaveFiles, true) !== false) {
                $leaveFiles = base64_decode($leaveFiles);
            }

            // Parse comma-separated file paths
            $filePaths = explode(',', $leaveFiles);
            $documents = array();

            foreach ($filePaths as $index => $filePath) {
                $filePath = trim($filePath);
                if (empty($filePath)) continue;

                // Extract filename from path
                $fileName = basename($filePath);

                // Get file info if file exists
                $fullPath = '../../../uploads/' . $filePath;
                $fileSize = file_exists($fullPath) ? filesize($fullPath) : 0;
                $fileType = file_exists($fullPath) ? mime_content_type($fullPath) : 'application/octet-stream';

                $documents[] = (object)array(
                    'documentID' => 'legacy_' . $index,
                    'fileName' => $fileName,
                    'filePath' => $filePath,
                    'fileSize' => $fileSize,
                    'fileType' => $fileType,
                    'uploadDate' => $application->DateAdded ?? null,
                    'documentType' => 'supporting',
                    'uploadedByID' => $application->employeeID ?? null
                );
            }

            return $documents;
        }
    }

    /**
     * Check user permissions for a leave application
     *
     * @param object|array $leaveApplication Leave application object or array
     * @param int $userId Current user ID
     * @return array Permissions array with canEdit, canCancel, canApprove, canView
     */
    public static function check_leave_application_permissions($leaveApplication, $userId) {
        global $DBConn;

        $permissions = array(
            'canEdit' => false,
            'canCancel' => false,
            'canApprove' => false,
            'canView' => false,
            'isHRManager' => false
        );

        if (!$leaveApplication || !$userId) {
            return $permissions;
        }

        $getValue = function ($key, $default = null) use ($leaveApplication) {
            if (is_array($leaveApplication) && array_key_exists($key, $leaveApplication)) {
                return $leaveApplication[$key];
            }

            if (is_object($leaveApplication)) {
                if (isset($leaveApplication->$key)) {
                    return $leaveApplication->$key;
                }

                if (property_exists($leaveApplication, $key)) {
                    return $leaveApplication->$key;
                }
            }

            return $default;
        };

        $employeeID = (int)$getValue('employeeID');
        $leaveStatusID = (int)$getValue('leaveStatusID');
        $entityID = $getValue('entityID');
        $applicationId = $getValue('leaveApplicationID', $getValue('leaveApplicationId'));

        // Employee can view/manage own application
        if ($employeeID === (int)$userId) {
            $permissions['canView'] = true;
            $permissions['canEdit'] = in_array($leaveStatusID, array(1), true);
            $permissions['canCancel'] = in_array($leaveStatusID, array(1, 2, 3), true);
        }

        // HR manager scope
        if (isset($DBConn) && Employee::is_hr_manager($userId, $DBConn, $entityID)) {
            $permissions['isHRManager'] = true;
            $permissions['canView'] = true;
        }

        // Temporarily ignore workflow tables until refactor is complete
        if ($permissions['isHRManager'] && in_array($leaveStatusID, array(2, 3, 4, 6), true)) {
            $permissions['canApprove'] = true;
        }

        // Allow direct supervisors / workflow approvers to act even if they are not HR managers.
        if (
            !$permissions['canApprove']
            && isset($DBConn)
            && in_array($leaveStatusID, array(2, 3), true)
            && self::is_user_leave_approver($leaveApplication, $userId, $DBConn)
        ) {
            $permissions['canApprove'] = true;
            $permissions['canView'] = true;
        }

        return $permissions;
    }

    /**
     * Determine if a user is an approver (saved or dynamic) for the supplied leave application.
     */
    private static function is_user_leave_approver($leaveApplication, $userId, $DBConn) {
        if (!$leaveApplication || !$userId) {
            return false;
        }

        $application = is_object($leaveApplication)
            ? (array)$leaveApplication
            : ( (array)$leaveApplication );

        $employeeID = isset($application['employeeID']) ? (int)$application['employeeID'] : 0;
        $applicationID = isset($application['leaveApplicationID'])
            ? (int)$application['leaveApplicationID']
            : (isset($application['leaveApplicationId']) ? (int)$application['leaveApplicationId'] : 0);

        if ($employeeID === 0) {
            return false;
        }

        // Direct supervisor can always act as first-level approver
        $employee = Employee::employees(array('ID' => $employeeID), true, $DBConn);
        if ($employee && !empty($employee->supervisorID) && (int)$employee->supervisorID === (int)$userId) {
            return true;
        }

        if ($applicationID === 0) {
            return false;
        }

        // Look for an active workflow instance
        $params = array(array($applicationID, 'i'));
        $whereClause = "leaveApplicationID = ?";
        $lapsedCheck = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_instances LIKE 'Lapsed'", array());
        if ($lapsedCheck && count($lapsedCheck) > 0) {
            $whereClause .= " AND Lapsed = 'N'";
        }

        $workflowInstance = $DBConn->fetch_all_rows(
            "SELECT * FROM tija_leave_approval_instances WHERE {$whereClause} LIMIT 1",
            $params
        );

        if (!$workflowInstance || count($workflowInstance) === 0) {
            return false;
        }

        $instance = is_object($workflowInstance[0]) ? (array)$workflowInstance[0] : $workflowInstance[0];
        $policyID = isset($instance['policyID']) ? (int)$instance['policyID'] : 0;

        if ($policyID === 0) {
            return false;
        }

        // First check saved approvers
        $approvers = self::get_workflow_approvers($policyID, $DBConn);
        foreach ($approvers as $approver) {
            $approverUserID = isset($approver['approverUserID']) ? (int)$approver['approverUserID'] : 0;
            if ($approverUserID === (int)$userId) {
                return true;
            }
        }

        // Then resolve dynamic approvers (supervisor, department head, etc.)
        $dynamicApprovers = self::resolve_dynamic_workflow_approvers($policyID, $employeeID, $DBConn);
        foreach ($dynamicApprovers as $approver) {
            $approverUserID = isset($approver['approverUserID']) ? (int)$approver['approverUserID'] : 0;
            if ($approverUserID === (int)$userId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get workflow summary for a leave application.
     *
     * @param int $applicationId
     * @param object $DBConn
     * @return array
     */
    /**
     * Get workflow summary for a leave application
     * Returns counts of approved/rejected/pending approvers and their names
     *
     * @param int $applicationId Leave application ID
     * @param object $DBConn Database connection object
     * @return array|null Summary with counts and names, or null if no workflow
     */
    public static function get_leave_workflow_summary($applicationId, $DBConn) {
        if (empty($applicationId)) {
            return null;
        }

        // Check if workflow tables exist
        $tableCheck = $DBConn->fetch_all_rows("SHOW TABLES LIKE 'tija_leave_approval_instances'", array());
        if (!$tableCheck || count($tableCheck) === 0) {
            return null;
        }

        // Get workflow instance
        $lapsedCheck = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_instances LIKE 'Lapsed'", array());
        $hasLapsedColumn = ($lapsedCheck && count($lapsedCheck) > 0);

        $whereClause = "leaveApplicationID = ?";
        $params = array(array($applicationId, 'i'));

        if ($hasLapsedColumn) {
            $whereClause .= " AND Lapsed = 'N'";
        }

        $instance = $DBConn->fetch_all_rows(
            "SELECT * FROM tija_leave_approval_instances WHERE {$whereClause}",
            $params
        );

        if (!$instance || count($instance) === 0) {
            return null;
        }

        $inst = is_object($instance[0]) ? (array)$instance[0] : $instance[0];
        $instanceID = $inst['instanceID'] ?? null;
        $policyID = $inst['policyID'] ?? null;

        if (!$instanceID || !$policyID) {
            return null;
        }

        // Get approval status
        $approvalStatus = self::check_workflow_approval_status($instanceID, $policyID, $DBConn);

        $approvedCount = 0;
        $rejectedCount = 0;
        $pendingCount = 0;
        $approvedBy = array();
        $rejectedBy = array();

        if (isset($approvalStatus['steps']) && is_array($approvalStatus['steps'])) {
            foreach ($approvalStatus['steps'] as $step) {
                if (isset($step['approvers']) && is_array($step['approvers'])) {
                    foreach ($step['approvers'] as $approver) {
                        if (isset($approver['hasActed']) && $approver['hasActed']) {
                            $action = isset($approver['action']) ? strtolower($approver['action']) : '';
                            $approverName = $approver['approverName'] ?? 'Unknown';

                            if ($action === 'approved') {
                                $approvedCount++;
                                $approvedBy[] = $approverName;
                            } elseif ($action === 'rejected') {
                                $rejectedCount++;
                                $rejectedBy[] = $approverName;
                            }
                        } else {
                            $pendingCount++;
                        }
                    }
                }
            }
        }

        return array(
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'pendingCount' => $pendingCount,
            'approvedBy' => implode(', ', $approvedBy),
            'rejectedBy' => implode(', ', $rejectedBy),
            'hasWorkflow' => true
        );
    }

    /**
     * Get approval comments for a leave application
     *
     * @param int $applicationId Application ID
     * @param object $DBConn Database connection object
     * @return array Approval comments
     */
    public static function get_leave_approval_comments($applicationId, $DBConn) {
        self::ensure_leave_comments_table($DBConn);

        // Check if table exists first
        $tableCheck = "SHOW TABLES LIKE 'tija_leave_approval_comments'";
        $tableExists = $DBConn->fetch_all_rows($tableCheck, array());

        if (!$tableExists || count($tableExists) == 0) {
            // Table doesn't exist yet, return empty array
            // Comments functionality will be available after running migration
            return array();
        }

        $sql = "SELECT
                    lac.commentID,
                    lac.comment,
                    lac.commentDate,
                    lac.approvalLevel,
                    lac.approverID,
                    lac.commentType,
                    CONCAT(p.FirstName, ' ', p.Surname) as approverName,
                    jt.jobTitle as approverJobTitle
                FROM tija_leave_approval_comments lac
                LEFT JOIN people p ON lac.approverID = p.ID
                LEFT JOIN user_details ud ON p.ID = ud.ID
                LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
                WHERE lac.leaveApplicationID = ?
                AND lac.Lapsed = 'N'
                ORDER BY lac.commentDate ASC";

        $params = array(array($applicationId, 'i'));
        $rows = $DBConn->fetch_all_rows($sql, $params);

        return $rows ? $rows : array();
    }

    /**
     * Get team leave overview for a manager
     *
     * @param int $managerID Manager ID
     * @param object $DBConn Database connection object
     * @return array Team leave overview
     */
    public static function get_team_leave_overview($managerID, $DBConn, $hrManagerScope = null) {
        $teamMemberMap = array();

        $teamMembers = Employee::get_team_members($managerID, $DBConn);
        if ($teamMembers) {
            foreach ($teamMembers as $member) {
                $teamMemberMap[$member->ID] = $member;
            }
        }

        if ($hrManagerScope === null) {
            $hrManagerScope = Employee::get_hr_manager_scope($managerID, $DBConn);
        }
        if (!empty($hrManagerScope['isHRManager'])) {
            $hrManagedEmployees = Employee::get_hr_managed_employees($managerID, $DBConn);
            if ($hrManagedEmployees) {
                foreach ($hrManagedEmployees as $member) {
                    $teamMemberMap[$member->ID] = $member;
                }
            }
        }

        if (empty($teamMemberMap)) {
            return array();
        }

        $teamMembers = array_values($teamMemberMap);

        $overview = array();
        foreach ($teamMembers as $member) {
            $memberOverview = (object)array(
                'ID' => $member->ID,
                'firstName' => $member->FirstName,
                'lastName' => $member->Surname,
                'jobTitle' => $member->jobTitle ?? 'Employee',
                'availableDays' => 0,
                'usedDays' => 0,
                'pendingRequests' => 0,
                'isOnLeave' => false
            );

            // Get leave balances
            $balances = self::calculate_leave_balances($member->ID, $member->entityID ?? 1, $DBConn);
            if (!empty($balances)) {
                $totalAvailable = 0;
                $totalUsed = 0;
                foreach ($balances as $balance) {
                    $totalAvailable += $balance['available'];
                    $totalUsed += $balance['used'];
                }
                $memberOverview->availableDays = $totalAvailable;
                $memberOverview->usedDays = $totalUsed;
            }

            // Get pending requests
            $pendingRequests = self::leave_applications_full(
                array(
                    'employeeID' => $member->ID,
                    'leaveStatusID' => 3, // Pending
                    'Lapsed' => 'N',
                    'Suspended' => 'N'
                ),
                false,
                $DBConn
            );
            $memberOverview->pendingRequests = $pendingRequests ? count($pendingRequests) : 0;

            // Check if currently on leave
            $currentDate = date('Y-m-d');
            $currentLeaveSql = "SELECT e.leaveApplicationID
                               FROM tija_leave_applications e
                               WHERE e.employeeID = ?
                               AND e.startDate <= ?
                               AND e.endDate >= ?
                               AND e.leaveStatusID = 4
                               AND e.Lapsed = 'N'
                               AND e.Suspended = 'N'
                               LIMIT 1";

            $currentLeaveParams = array(
                array($member->ID, 'i'),
                array($currentDate, 's'),
                array($currentDate, 's')
            );

            $currentLeave = $DBConn->fetch_all_rows($currentLeaveSql, $currentLeaveParams);
            $memberOverview->isOnLeave = $currentLeave ? true : false;

            $overview[] = $memberOverview;
        }

        return $overview;
    }

    /**
     * Get team leave analytics for a manager
     *
     * @param int $managerID Manager ID
     * @param object $DBConn Database connection object
     * @return array Team leave analytics
     */
    public static function get_team_leave_analytics($managerID, $DBConn, $hrManagerScope = null) {
        $analytics = array(
            'totalTeamMembers' => 0,
            'teamLeaveUtilization' => 0,
            'pendingApprovals' => 0,
            'approvedThisMonth' => 0,
            'currentlyOnLeave' => 0,
            'teamMemberNames' => array(),
            'teamMemberData' => array()
        );

        $teamMemberMap = array();

        $teamMembers = Employee::get_team_members($managerID, $DBConn);
        if ($teamMembers) {
            foreach ($teamMembers as $member) {
                $teamMemberMap[$member->ID] = $member;
            }
        }

        if ($hrManagerScope === null) {
            $hrManagerScope = Employee::get_hr_manager_scope($managerID, $DBConn);
        }
        if (!empty($hrManagerScope['isHRManager'])) {
            $hrManagedEmployees = Employee::get_hr_managed_employees($managerID, $DBConn);
            if ($hrManagedEmployees) {
                foreach ($hrManagedEmployees as $member) {
                    $teamMemberMap[$member->ID] = $member;
                }
            }
        }

        if (empty($teamMemberMap)) {
            return $analytics;
        }

        $teamMembers = array_values($teamMemberMap);
        $analytics['totalTeamMembers'] = count($teamMembers);
        $totalUsedDays = 0;
        $totalAvailableDays = 0;
        $currentDate = date('Y-m-d');
        $currentMonth = date('Y-m');

        foreach ($teamMembers as $member) {
            $memberName = $member->FirstName . ' ' . $member->Surname;
            $analytics['teamMemberNames'][] = $memberName;

            // Get leave balances
            $balances = self::calculate_leave_balances($member->ID, $member->entityID ?? 1, $DBConn);
            $memberUsedDays = 0;
            $memberAvailableDays = 0;

            if (!empty($balances)) {
                foreach ($balances as $balance) {
                    $memberUsedDays += $balance['used'];
                    $memberAvailableDays += $balance['available'];
                }
            }

            $analytics['teamMemberData'][] = $memberUsedDays;
            $totalUsedDays += $memberUsedDays;
            $totalAvailableDays += $memberAvailableDays;

            // Count pending approvals
            $pendingRequests = self::leave_applications_full(
                array(
                    'employeeID' => $member->ID,
                    'leaveStatusID' => 3, // Pending
                    'Lapsed' => 'N',
                    'Suspended' => 'N'
                ),
                false,
                $DBConn
            );
            $analytics['pendingApprovals'] += $pendingRequests ? count($pendingRequests) : 0;

            // Count approved this month
            $monthlyApprovedSql = "SELECT e.leaveApplicationID
                                  FROM tija_leave_applications e
                                  WHERE e.employeeID = ?
                                  AND e.leaveStatusID = 4
                                  AND DATE_FORMAT(e.startDate, '%Y-%m') = ?
                                  AND e.Lapsed = 'N'
                                  AND e.Suspended = 'N'";

            $monthlyApprovedParams = array(
                array($member->ID, 'i'),
                array($currentMonth, 's')
            );

            $monthlyApproved = $DBConn->fetch_all_rows($monthlyApprovedSql, $monthlyApprovedParams);
            $analytics['approvedThisMonth'] += $monthlyApproved ? count($monthlyApproved) : 0;

            // Check if currently on leave
            $currentLeaveSql2 = "SELECT e.leaveApplicationID
                                FROM tija_leave_applications e
                                WHERE e.employeeID = ?
                                AND e.startDate <= ?
                                AND e.endDate >= ?
                                AND e.leaveStatusID = 4
                                AND e.Lapsed = 'N'
                                AND e.Suspended = 'N'
                                LIMIT 1";

            $currentLeaveParams2 = array(
                array($member->ID, 'i'),
                array($currentDate, 's'),
                array($currentDate, 's')
            );

            $currentLeave = $DBConn->fetch_all_rows($currentLeaveSql2, $currentLeaveParams2);
            if ($currentLeave) {
                $analytics['currentlyOnLeave']++;
            }
        }

        // Calculate team utilization
        $analytics['teamLeaveUtilization'] = $totalAvailableDays > 0 ?
            round(($totalUsedDays / ($totalUsedDays + $totalAvailableDays)) * 100, 1) : 0;

        return $analytics;
    }

    /**
     * Get pending approvals for a manager
     *
     * @param int $managerID Manager ID
     * @param object $DBConn Database connection object
     * @return array Pending approvals
     */
    public static function get_pending_approvals_for_manager($managerID, $DBConn, $hrManagerScope = null) {
        $teamMemberMap = array();

        $teamMembers = Employee::get_team_members($managerID, $DBConn);
        if ($teamMembers) {
            foreach ($teamMembers as $member) {
                $teamMemberMap[$member->ID] = $member;
            }
        }

        if ($hrManagerScope === null) {
            $hrManagerScope = Employee::get_hr_manager_scope($managerID, $DBConn);
        }

        $isHrManager = !empty($hrManagerScope['isHRManager']);

        // Check if workflow tables exist
        $workflowTablesExist = true;
        $tableCheck = $DBConn->fetch_all_rows("SHOW TABLES LIKE 'tija_leave_approval_instances'", array());
        if (!$tableCheck || count($tableCheck) === 0) {
            $workflowTablesExist = false;
        }

        if ($isHrManager && $workflowTablesExist) {
            // HR managers see applications where they are approvers in any step (parallel workflow)
            // or applications at final step (sequential workflow)
            $lapsedCheck = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_instances LIKE 'Lapsed'", array());
            $hasLapsedColumn = ($lapsedCheck && count($lapsedCheck) > 0);

            $lapsedCondition = $hasLapsedColumn ? "AND i.Lapsed = 'N'" : "";

            // Build entity ID list for HR manager scope
            $entityIDs = array();
            if (!empty($hrManagerScope['scopes'])) {
                foreach ($hrManagerScope['scopes'] as $scope) {
                    if (!empty($scope['entityID'])) {
                        $entityIDs[] = (int)$scope['entityID'];
                    }
                }
            }
            // If no specific entity scopes, use HR manager's own entity
            if (empty($entityIDs)) {
                $hrManagerDetails = Employee::employees(array('ID' => $managerID), true, $DBConn);
                if ($hrManagerDetails && isset($hrManagerDetails->entityID)) {
                    $entityIDs[] = (int)$hrManagerDetails->entityID;
                }
            }

            // Check which columns exist in tija_leave_approval_actions
            $actionsColumns = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_actions", array());
            $actionsColumnNames = array();
            if ($actionsColumns && count($actionsColumns) > 0) {
                foreach ($actionsColumns as $col) {
                    $col = is_object($col) ? (array)$col : $col;
                    $actionsColumnNames[] = $col['Field'] ?? $col['field'] ?? '';
                }
            }

            $hasApproverUserID = in_array('approverUserID', $actionsColumnNames);
            $hasApproverID = in_array('approverID', $actionsColumnNames);

            // Build action join condition
            $actionJoinCondition = "act.instanceID = i.instanceID AND act.stepID = s.stepID";
            $useActionFilter = false;

            if ($hasApproverUserID) {
                $actionJoinCondition .= " AND act.approverUserID = ?";
                $useActionFilter = true;
            } elseif ($hasApproverID) {
                $actionJoinCondition .= " AND act.approverID = ?";
                $useActionFilter = true;
            }

            $entityCondition = '';
            $params = array();
            if (!empty($entityIDs)) {
                $entityPlaceholders = str_repeat('?,', count($entityIDs) - 1) . '?';
                $entityCondition = "OR (i.instanceID IS NULL AND la.entityID IN ({$entityPlaceholders}))";
                foreach ($entityIDs as $eid) {
                    $params[] = array($eid, 'i');
                }
            }

            if ($useActionFilter) {
                // Show applications where HR manager is approver in any step and hasn't acted
                $sql = "SELECT DISTINCT la.*, lt.leaveTypeName,
                        CONCAT(emp.FirstName, ' ', emp.Surname) as employeeName,
                        jt.jobTitle,
                        s.stepOrder,
                        s.stepName,
                        i.policyID,
                        (SELECT MAX(stepOrder) FROM tija_leave_approval_steps WHERE policyID = i.policyID AND Suspended = 'N') as maxStepOrder
                        FROM tija_leave_applications la
                        LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
                        LEFT JOIN people emp ON la.employeeID = emp.ID
                        LEFT JOIN user_details ud ON emp.ID = ud.ID
                        LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
                        INNER JOIN tija_leave_approval_instances i ON la.leaveApplicationID = i.leaveApplicationID {$lapsedCondition}
                        INNER JOIN tija_leave_approval_steps s ON i.policyID = s.policyID AND s.Suspended = 'N'
                        INNER JOIN tija_leave_approval_step_approvers sa ON s.stepID = sa.stepID
                            AND sa.approverUserID = ?
                            AND sa.Suspended = 'N'
                        LEFT JOIN tija_leave_approval_actions act ON {$actionJoinCondition}
                            AND act.action IN ('approved', 'rejected')
                        WHERE la.leaveStatusID = 3
                        AND la.Lapsed = 'N'
                        AND la.Suspended = 'N'
                        AND act.actionID IS NULL
                        {$entityCondition}
                        ORDER BY la.DateAdded ASC";
            } else {
                // Fallback: show applications at final step or with no workflow
                $sql = "SELECT DISTINCT la.*, lt.leaveTypeName,
                        CONCAT(emp.FirstName, ' ', emp.Surname) as employeeName,
                        jt.jobTitle,
                        i.currentStepOrder,
                        i.policyID,
                        (SELECT MAX(stepOrder) FROM tija_leave_approval_steps WHERE policyID = i.policyID AND Suspended = 'N') as maxStepOrder
                        FROM tija_leave_applications la
                        LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
                        LEFT JOIN people emp ON la.employeeID = emp.ID
                        LEFT JOIN user_details ud ON emp.ID = ud.ID
                        LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
                        LEFT JOIN tija_leave_approval_instances i ON la.leaveApplicationID = i.leaveApplicationID {$lapsedCondition}
                        WHERE la.leaveStatusID = 3
                        AND la.Lapsed = 'N'
                        AND la.Suspended = 'N'
                        AND (
                            -- Applications at final workflow step
                            (i.instanceID IS NOT NULL AND i.currentStepOrder >= (SELECT MAX(stepOrder) FROM tija_leave_approval_steps WHERE policyID = i.policyID AND Suspended = 'N'))
                            {$entityCondition}
                        )
                        ORDER BY la.DateAdded ASC";
            }

            // Add manager ID parameter for action filter if needed
            if ($useActionFilter) {
                array_unshift($params, array($managerID, 'i'));
                if ($hasApproverUserID || $hasApproverID) {
                    $params[] = array($managerID, 'i');
                }
            }

            $rows = $DBConn->fetch_all_rows($sql, $params);
            return $rows ? $rows : array();
        }

        // Regular managers - only see applications at their workflow step
        if (empty($teamMemberMap)) {
            return array();
        }

        $teamMemberIDs = array_keys($teamMemberMap);
        $placeholders = str_repeat('?,', count($teamMemberIDs) - 1) . '?';

        if ($workflowTablesExist) {
            // Filter by workflow - show applications where manager is approver in any step and hasn't acted yet
            $lapsedCheck = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_instances LIKE 'Lapsed'", array());
            $hasLapsedColumn = ($lapsedCheck && count($lapsedCheck) > 0);

            $lapsedCondition = $hasLapsedColumn ? "AND i.Lapsed = 'N'" : "";

            // Check which columns exist in tija_leave_approval_actions
            $actionsColumns = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_actions", array());
            $actionsColumnNames = array();
            if ($actionsColumns && count($actionsColumns) > 0) {
                foreach ($actionsColumns as $col) {
                    $col = is_object($col) ? (array)$col : $col;
                    $actionsColumnNames[] = $col['Field'] ?? $col['field'] ?? '';
                }
            }

            $hasApproverUserID = in_array('approverUserID', $actionsColumnNames);
            $hasApproverID = in_array('approverID', $actionsColumnNames);

            // Build the join condition for actions table
            $actionJoinCondition = "act.instanceID = i.instanceID AND act.stepID = s.stepID";
            $useActionFilter = false;

            if ($hasApproverUserID) {
                $actionJoinCondition .= " AND act.approverUserID = ?";
                $useActionFilter = true;
            } elseif ($hasApproverID) {
                $actionJoinCondition .= " AND act.approverID = ?";
                $useActionFilter = true;
            }
            // If neither column exists, we'll use a subquery approach instead

            if ($useActionFilter) {
                // Use LEFT JOIN with approver filter - includes both saved and dynamic approvers
                $sql = "SELECT DISTINCT la.*, lt.leaveTypeName,
                        CONCAT(emp.FirstName, ' ', emp.Surname) as employeeName,
                        jt.jobTitle,
                        s.stepOrder,
                        s.stepName,
                        i.policyID,
                        i.currentStepOrder,
                        (SELECT MAX(stepOrder) FROM tija_leave_approval_steps WHERE policyID = i.policyID AND Suspended = 'N') as maxStepOrder
                        FROM tija_leave_applications la
                        LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
                        LEFT JOIN people emp ON la.employeeID = emp.ID
                        LEFT JOIN user_details ud ON emp.ID = ud.ID
                        LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
                        INNER JOIN tija_leave_approval_instances i ON la.leaveApplicationID = i.leaveApplicationID {$lapsedCondition}
                        INNER JOIN tija_leave_approval_steps s ON i.policyID = s.policyID AND s.Suspended = 'N'
                        LEFT JOIN tija_leave_approval_step_approvers sa ON s.stepID = sa.stepID AND sa.approverUserID = ? AND sa.Suspended = 'N'
                        LEFT JOIN tija_leave_approval_actions act ON {$actionJoinCondition}
                            AND act.action IN ('approved', 'rejected')
                        WHERE la.employeeID IN ({$placeholders})
                        AND la.leaveStatusID = 3
                        AND la.Lapsed = 'N'
                        AND la.Suspended = 'N'
                        AND act.actionID IS NULL
                        AND (
                            -- Saved approvers (explicitly assigned) - check if LEFT JOIN found a match
                            sa.stepID IS NOT NULL
                            OR
                            -- Dynamic approvers (supervisor, department_head, etc.)
                            (
                                s.stepOrder = i.currentStepOrder
                                AND (
                                    -- Supervisor check
                                    (s.stepType = 'supervisor' AND ud.supervisorID = ?)
                                    OR
                                    -- Department head check
                                    (s.stepType = 'department_head' AND EXISTS (
                                        SELECT 1 FROM user_details ud_emp
                                        INNER JOIN tija_user_unit_assignments ua_emp ON ud_emp.ID = ua_emp.userID
                                        INNER JOIN tija_units un_emp ON ua_emp.unitID = un_emp.unitID
                                        INNER JOIN tija_unit_types ut_emp ON un_emp.unitTypeID = ut_emp.unitTypeID
                                        WHERE ud_emp.ID = emp.ID
                                        AND un_emp.headOfUnitID = ?
                                        AND ut_emp.unitTypeName LIKE '%Department%'
                                        AND (ua_emp.assignmentEndDate IS NULL OR ua_emp.assignmentEndDate >= CURDATE())
                                        AND ud_emp.Lapsed = 'N' AND ud_emp.Suspended = 'N'
                                        AND ua_emp.Lapsed = 'N' AND ua_emp.Suspended = 'N'
                                        AND un_emp.Lapsed = 'N' AND un_emp.Suspended = 'N'
                                    ))
                                    OR
                                    -- Project manager (fallback to supervisor)
                                    (s.stepType = 'project_manager' AND ud.supervisorID = ?)
                                )
                            )
                        )
                        ORDER BY la.DateAdded ASC";
            } else {
                // Use subquery approach when approver columns don't exist
                $sql = "SELECT DISTINCT la.*, lt.leaveTypeName,
                        CONCAT(emp.FirstName, ' ', emp.Surname) as employeeName,
                        jt.jobTitle,
                        s.stepOrder,
                        s.stepName,
                        i.policyID,
                        i.currentStepOrder,
                        (SELECT MAX(stepOrder) FROM tija_leave_approval_steps WHERE policyID = i.policyID AND Suspended = 'N') as maxStepOrder
                        FROM tija_leave_applications la
                        LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
                        LEFT JOIN people emp ON la.employeeID = emp.ID
                        LEFT JOIN user_details ud ON emp.ID = ud.ID
                        LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
                        INNER JOIN tija_leave_approval_instances i ON la.leaveApplicationID = i.leaveApplicationID {$lapsedCondition}
                        INNER JOIN tija_leave_approval_steps s ON i.policyID = s.policyID AND s.Suspended = 'N'
                        LEFT JOIN tija_leave_approval_step_approvers sa ON s.stepID = sa.stepID AND sa.approverUserID = ? AND sa.Suspended = 'N'
                        WHERE la.employeeID IN ({$placeholders})
                        AND la.leaveStatusID = 3
                        AND la.Lapsed = 'N'
                        AND la.Suspended = 'N'
                        AND (
                            -- Saved approvers
                            sa.stepApproverID IS NOT NULL
                            OR
                            -- Dynamic approvers
                            (
                                s.stepOrder = i.currentStepOrder
                                AND (
                                    (s.stepType = 'supervisor' AND emp.supervisorID = ?)
                                    OR
                                    (s.stepType = 'department_head' AND EXISTS (
                                        SELECT 1 FROM user_details ud2
                                        INNER JOIN tija_business_units bu ON ud2.businessUnitID = bu.businessUnitID
                                        WHERE ud2.ID = ? AND bu.headID = emp.ID
                                    ))
                                    OR
                                    (s.stepType = 'project_manager' AND emp.supervisorID = ?)
                                )
                            )
                        )
                        AND NOT EXISTS (
                            SELECT 1 FROM tija_leave_approval_actions act
                            WHERE act.instanceID = i.instanceID
                            AND act.stepID = s.stepID
                            AND act.action IN ('approved', 'rejected')
                        )
                        ORDER BY la.DateAdded ASC";
            }

            $params = array(array($managerID, 'i'));

            // Add approver parameter for action join if column exists
            if ($useActionFilter) {
                $params[] = array($managerID, 'i');
                // Add parameters for dynamic approver checks
                $params[] = array($managerID, 'i'); // supervisor check
                $params[] = array($managerID, 'i'); // department head check
                $params[] = array($managerID, 'i'); // project manager check
            } else {
                // Add parameters for dynamic approver checks
                $params[] = array($managerID, 'i'); // supervisor check
                $params[] = array($managerID, 'i'); // department head check
                $params[] = array($managerID, 'i'); // project manager check
            }

            foreach ($teamMemberIDs as $id) {
                $params[] = array($id, 'i');
            }

            $rows = $DBConn->fetch_all_rows($sql, $params);
            return $rows ? $rows : array();
        } else {
            // Fallback: no workflow tables - use legacy method
            $sql = "SELECT la.*, lt.leaveTypeName,
                    CONCAT(emp.FirstName, ' ', emp.Surname) as employeeName,
                    jt.jobTitle
                    FROM tija_leave_applications la
                    LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
                    LEFT JOIN people emp ON la.employeeID = emp.ID
                    LEFT JOIN user_details ud ON emp.ID = ud.ID
                    LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
                    WHERE la.employeeID IN ({$placeholders})
                    AND la.leaveStatusID = 3
                    AND la.Lapsed = 'N'
                    AND la.Suspended = 'N'
                    ORDER BY la.DateAdded ASC";

            $params = array();
            foreach ($teamMemberIDs as $id) {
                $params[] = array($id, 'i');
            }

            $rows = $DBConn->fetch_all_rows($sql, $params);
            return $rows ? $rows : array();
        }
    }

    /**
     * Get pending approvals for a department
     *
     * @param int $departmentID Department ID
     * @param object $DBConn Database connection object
     * @return array Pending approvals
     */
    public static function get_pending_approvals_for_department($departmentID, $DBConn) {
        if (!$departmentID) {
            return array();
        }

        $sql = "SELECT la.*, lt.leaveTypeName,
                CONCAT(emp.FirstName, ' ', emp.Surname) as employeeName,
                jt.jobTitle
                FROM tija_leave_applications la
                LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
                LEFT JOIN people emp ON la.employeeID = emp.ID
                LEFT JOIN user_details ud ON emp.ID = ud.ID
                LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
                WHERE ud.businessUnitID = ?
                AND la.leaveStatusID = 3
                AND la.Lapsed = 'N'
                AND la.Suspended = 'N'
                ORDER BY la.DateAdded ASC";

        $params = array(array($departmentID, 'i'));
        $rows = $DBConn->fetch_all_rows($sql, $params);
        return $rows ? $rows : array();
    }

    /**
     * Get all pending approvals for HR
     * Returns applications at final workflow step or with no workflow
     *
     * @param int $orgDataID Organization data ID
     * @param int $entityID Entity ID
     * @param object $DBConn Database connection object
     * @return array All pending approvals
     */
    public static function get_all_pending_approvals($orgDataID, $entityID, $DBConn) {
        // Check if workflow tables exist
        $tableCheck = $DBConn->fetch_all_rows("SHOW TABLES LIKE 'tija_leave_approval_instances'", array());
        if (!$tableCheck || count($tableCheck) === 0) {
            // No workflow tables - return all pending for entity
            $sql = "SELECT la.*, lt.leaveTypeName,
                    CONCAT(emp.FirstName, ' ', emp.Surname) as employeeName,
                    jt.jobTitle
                    FROM tija_leave_applications la
                    LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
                    LEFT JOIN people emp ON la.employeeID = emp.ID
                    LEFT JOIN user_details ud ON emp.ID = ud.ID
                    LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
                    WHERE la.orgDataID = ?
                    AND la.entityID = ?
                    AND la.leaveStatusID = 3
                    AND la.Lapsed = 'N'
                    AND la.Suspended = 'N'
                    ORDER BY la.DateAdded ASC";

            $params = array(
                array($orgDataID, 'i'),
                array($entityID, 'i')
            );

            $rows = $DBConn->fetch_all_rows($sql, $params);
            return $rows ? $rows : array();
        }

        // With workflow - return applications where HR managers are approvers
        // For parallel workflow: show all steps where HR manager is assigned
        // For sequential workflow: show only final step
        $lapsedCheck = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_instances LIKE 'Lapsed'", array());
        $hasLapsedColumn = ($lapsedCheck && count($lapsedCheck) > 0);

        $lapsedCondition = $hasLapsedColumn ? "AND i.Lapsed = 'N'" : "";

        // Get HR managers for this entity to check if they're in workflow
        $hrManagers = Employee::get_hr_managers_for_entity($entityID, $DBConn);
        $hrManagerIDs = array();
        if ($hrManagers && count($hrManagers) > 0) {
            foreach ($hrManagers as $hr) {
                $hrID = is_object($hr) ? $hr->ID : (is_array($hr) ? $hr['ID'] : null);
                if ($hrID) {
                    $hrManagerIDs[] = (int)$hrID;
                }
            }
        }

        // If no HR managers found, still check for applications at final step (legacy behavior)
        // This handles cases where HR managers might not be explicitly in workflow but should still see final approvals

        // Check which columns exist in tija_leave_approval_actions
        $actionsColumns = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_actions", array());
        $actionsColumnNames = array();
        if ($actionsColumns && count($actionsColumns) > 0) {
            foreach ($actionsColumns as $col) {
                $col = is_object($col) ? (array)$col : $col;
                $actionsColumnNames[] = $col['Field'] ?? $col['field'] ?? '';
            }
        }

        $hasApproverUserID = in_array('approverUserID', $actionsColumnNames);
        $hasApproverID = in_array('approverID', $actionsColumnNames);

        // Build HR manager placeholders
        $hrPlaceholders = str_repeat('?,', count($hrManagerIDs) - 1) . '?';

        // Build action join condition
        $actionJoinCondition = "act.instanceID = i.instanceID AND act.stepID = s.stepID";
        $useActionFilter = false;

        if ($hasApproverUserID) {
            $actionJoinCondition .= " AND act.approverUserID IN ({$hrPlaceholders})";
            $useActionFilter = true;
        } elseif ($hasApproverID) {
            $actionJoinCondition .= " AND act.approverID IN ({$hrPlaceholders})";
            $useActionFilter = true;
        }

        if ($useActionFilter && !empty($hrManagerIDs)) {
            // Show applications where HR manager is approver in any step OR at final step (even if not explicitly assigned)
            // This handles cases where HR managers receive notifications but aren't in step_approvers table
            $finalStepNotExistsCondition = '';
            if ($hasApproverUserID) {
                $finalStepNotExistsCondition = "AND act2.approverUserID IN ({$hrPlaceholders})";
            } elseif ($hasApproverID) {
                $finalStepNotExistsCondition = "AND act2.approverID IN ({$hrPlaceholders})";
            }

            $sql = "SELECT DISTINCT la.*, lt.leaveTypeName,
                    CONCAT(emp.FirstName, ' ', emp.Surname) as employeeName,
                    jt.jobTitle,
                    COALESCE(s.stepOrder, i.currentStepOrder) as stepOrder,
                    s.stepName,
                    i.policyID,
                    i.currentStepOrder,
                    (SELECT MAX(stepOrder) FROM tija_leave_approval_steps WHERE policyID = i.policyID AND Suspended = 'N') as maxStepOrder
                    FROM tija_leave_applications la
                    LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
                    LEFT JOIN people emp ON la.employeeID = emp.ID
                    LEFT JOIN user_details ud ON emp.ID = ud.ID
                    LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
                    INNER JOIN tija_leave_approval_instances i ON la.leaveApplicationID = i.leaveApplicationID {$lapsedCondition}
                    LEFT JOIN tija_leave_approval_steps s ON i.policyID = s.policyID AND s.Suspended = 'N'
                    LEFT JOIN tija_leave_approval_step_approvers sa ON s.stepID = sa.stepID
                        AND sa.approverUserID IN ({$hrPlaceholders})
                        AND sa.Suspended = 'N'
                    LEFT JOIN tija_leave_approval_actions act ON {$actionJoinCondition}
                        AND act.action IN ('approved', 'rejected')
                    WHERE la.orgDataID = ?
                    AND la.entityID = ?
                    AND la.leaveStatusID = 3
                    AND la.Lapsed = 'N'
                    AND la.Suspended = 'N'
                    AND (
                        -- HR manager is explicitly assigned as approver and hasn't acted
                        (sa.stepID IS NOT NULL AND act.actionID IS NULL)
                        OR
                        -- HR manager should see final step applications (even if not explicitly assigned)
                        (i.currentStepOrder >= (SELECT MAX(stepOrder) FROM tija_leave_approval_steps WHERE policyID = i.policyID AND Suspended = 'N')
                         AND NOT EXISTS (
                             SELECT 1 FROM tija_leave_approval_actions act2
                             WHERE act2.instanceID = i.instanceID
                             AND act2.action IN ('approved', 'rejected')
                             {$finalStepNotExistsCondition}
                         ))
                    )
                    ORDER BY la.DateAdded ASC";
        } else {
            // Fallback: show applications at final step or with no workflow
            $sql = "SELECT DISTINCT la.*, lt.leaveTypeName,
                    CONCAT(emp.FirstName, ' ', emp.Surname) as employeeName,
                    jt.jobTitle,
                    i.currentStepOrder,
                    i.policyID,
                    (SELECT MAX(stepOrder) FROM tija_leave_approval_steps WHERE policyID = i.policyID AND Suspended = 'N') as maxStepOrder
                    FROM tija_leave_applications la
                    LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
                    LEFT JOIN people emp ON la.employeeID = emp.ID
                    LEFT JOIN user_details ud ON emp.ID = ud.ID
                    LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
                    LEFT JOIN tija_leave_approval_instances i ON la.leaveApplicationID = i.leaveApplicationID {$lapsedCondition}
                    WHERE la.orgDataID = ?
                    AND la.entityID = ?
                    AND la.leaveStatusID = 3
                    AND la.Lapsed = 'N'
                    AND la.Suspended = 'N'
                    AND (
                        -- Applications at final workflow step
                        (i.instanceID IS NOT NULL AND i.currentStepOrder >= (SELECT MAX(stepOrder) FROM tija_leave_approval_steps WHERE policyID = i.policyID AND Suspended = 'N'))
                        OR
                        -- Applications with no workflow (legacy)
                        i.instanceID IS NULL
                    )
                    ORDER BY la.DateAdded ASC";
        }

        $params = array();

        // Add HR manager IDs for approver filter
        if ($useActionFilter && !empty($hrManagerIDs)) {
            // For step approvers join
            foreach ($hrManagerIDs as $hrID) {
                $params[] = array($hrID, 'i');
            }
            // For action join condition
            foreach ($hrManagerIDs as $hrID) {
                $params[] = array($hrID, 'i');
            }
            // For final step check (NOT EXISTS subquery) - may need twice if both approverUserID and approverID checks
            if ($hasApproverUserID || $hasApproverID) {
                foreach ($hrManagerIDs as $hrID) {
                    $params[] = array($hrID, 'i');
                }
            }
        }

        // Add org and entity filters
        $params[] = array($orgDataID, 'i');
        $params[] = array($entityID, 'i');

        $rows = $DBConn->fetch_all_rows($sql, $params);
        return $rows ? $rows : array();
    }

    /**
     * Get employee leave balances (alias for calculate_leave_balances for backward compatibility)
     *
     * @param int $employeeID Employee ID
     * @param int $entityID Entity ID
     * @param object $DBConn Database connection object
     * @return array Leave balances
     */
    public static function get_employee_leave_balances($employeeID, $entityID, $DBConn) {
        return self::calculate_leave_balances($employeeID, $entityID, $DBConn);
    }

    /**
     * Get leave approval policies with step counts
     *
     * @param array $whereArr Parameters to filter leave approval policies
     * @param bool $single Whether to return a single record or not
     * @param object $DBConn Database connection object
     * @return mixed Leave approval policies or false on failure
     */
    public static function leave_approval_policies($whereArr = array(), $single = false, $DBConn) {
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
                $where .= "p.{$col} = ?";
                $params[] = array($val, 's');
                $i++;
            }
        }

        $sql = "SELECT p.*,
                COUNT(DISTINCT s.stepID) as totalSteps,
                COUNT(DISTINCT CASE WHEN s.isRequired = 'Y' THEN s.stepID END) as requiredSteps,
                CONCAT(u.FirstName, ' ', u.Surname) as createdByName
                FROM tija_leave_approval_policies p
                LEFT JOIN tija_leave_approval_steps s ON p.policyID = s.policyID AND s.Suspended = 'N'
                LEFT JOIN people u ON p.createdBy = u.ID
                {$where}
                GROUP BY p.policyID
                ORDER BY p.isDefault DESC, p.isActive DESC, p.createdAt DESC";

        $rows = $DBConn->fetch_all_rows($sql, $params);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get leave approval steps
     *
     * @param array $whereArr Parameters to filter leave approval steps
     * @param bool $single Whether to return a single record or not
     * @param object $DBConn Database connection object
     * @return mixed Leave approval steps or false on failure
     */
    public static function leave_approval_steps($whereArr = array(), $single = false, $DBConn) {
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

        $sql = "SELECT * FROM tija_leave_approval_steps
                {$where}
                ORDER BY stepOrder";

        $rows = $DBConn->fetch_all_rows($sql, $params);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get leave workflow templates with step counts
     *
     * @param array $whereArr Parameters to filter workflow templates
     * @param bool $single Whether to return a single record or not
     * @param object $DBConn Database connection object
     * @return mixed Leave workflow templates or false on failure
     */
    public static function leave_workflow_templates($whereArr = array(), $single = false, $DBConn) {
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
                $where .= "t.{$col} = ?";
                $params[] = array($val, 's');
                $i++;
            }
        }

        $sql = "SELECT t.*, COUNT(s.templateStepID) as stepCount
                FROM tija_leave_workflow_templates t
                LEFT JOIN tija_leave_workflow_template_steps s ON t.templateID = s.templateID
                {$where}
                GROUP BY t.templateID
                ORDER BY t.usageCount DESC";

        $rows = $DBConn->fetch_all_rows($sql, $params);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get active approval workflow for an entity
     * Checks for active workflow, prioritizing default workflow
     *
     * @param int $entityID Entity ID
     * @param object $DBConn Database connection object
     * @return mixed Active approval policy or false if none found
     */
    public static function get_active_approval_workflow($entityID, $DBConn) {
        if (empty($entityID)) {
            return false;
        }

        // First try to get default active workflow
        $defaultPolicy = self::leave_approval_policies(
            array(
                'entityID' => $entityID,
                'isDefault' => 'Y',
                'isActive' => 'Y',
                'Lapsed' => 'N'
            ),
            true,
            $DBConn
        );

        if ($defaultPolicy) {
            $policyID = is_object($defaultPolicy) ? $defaultPolicy->policyID : (is_array($defaultPolicy) ? $defaultPolicy['policyID'] : null);
            $policyName = is_object($defaultPolicy) ? $defaultPolicy->policyName : (is_array($defaultPolicy) ? $defaultPolicy['policyName'] : 'N/A');
            return $defaultPolicy;
        }

        // If no default, get any active workflow
        $activePolicies = self::leave_approval_policies(
            array(
                'entityID' => $entityID,
                'isActive' => 'Y',
                'Lapsed' => 'N'
            ),
            false,
            $DBConn
        );

        if ($activePolicies && count($activePolicies) > 0) {
            $policyID = is_object($activePolicies[0]) ? $activePolicies[0]->policyID : (is_array($activePolicies[0]) ? $activePolicies[0]['policyID'] : null);
            $policyName = is_object($activePolicies[0]) ? $activePolicies[0]->policyName : (is_array($activePolicies[0]) ? $activePolicies[0]['policyName'] : 'N/A');
            return $activePolicies[0];
        }

        return false;
    }

    /**
     * Create approval instance for a leave application
     *
     * @param int $leaveApplicationID Leave application ID
     * @param int $policyID Approval policy ID
     * @param object $DBConn Database connection object
     * @return mixed Instance ID or false on failure
     */
    public static function create_approval_instance($leaveApplicationID, $policyID, $DBConn) {
        if (empty($leaveApplicationID) || empty($policyID)) {
            return false;
        }

        // Check if instance already exists
        $existingInstance = $DBConn->fetch_all_rows(
            "SELECT instanceID FROM tija_leave_approval_instances WHERE leaveApplicationID = ?",
            array(array($leaveApplicationID, 'i'))
        );

        if ($existingInstance && count($existingInstance) > 0) {
            $existing = is_object($existingInstance[0]) ? (array)$existingInstance[0] : $existingInstance[0];
            return isset($existing['instanceID']) ? $existing['instanceID'] : false;
        }

        // Get first step order
        $firstStep = self::leave_approval_steps(
            array('policyID' => $policyID, 'Suspended' => 'N'),
            false,
            $DBConn
        );

        $firstStepOrder = 1;
        $firstStepID = null;
        if ($firstStep && count($firstStep) > 0) {
            $step = is_object($firstStep[0]) ? (array)$firstStep[0] : $firstStep[0];
            $firstStepOrder = isset($step['stepOrder']) ? (int)$step['stepOrder'] : 1;
            $firstStepID = isset($step['stepID']) ? (int)$step['stepID'] : null;
        }

        // Create instance
        // Check which columns exist in the table
        $allColumns = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_instances", array());
        $columnNames = array();
        if ($allColumns && count($allColumns) > 0) {
            foreach ($allColumns as $col) {
                $col = is_object($col) ? (array)$col : $col;
                $columnNames[] = $col['Field'] ?? $col['field'] ?? '';
            }
        }

        $hasStatusColumn = in_array('status', $columnNames);
        $hasWorkflowStatusColumn = in_array('workflowStatus', $columnNames);
        $hasCurrentStepIDColumn = in_array('currentStepID', $columnNames);
        $hasLapsedColumn = in_array('Lapsed', $columnNames);
        $hasSuspendedColumn = in_array('Suspended', $columnNames);

        $instanceData = array(
            'leaveApplicationID' => $leaveApplicationID,
            'policyID' => $policyID,
            'currentStepOrder' => $firstStepOrder,
            'startedAt' => date('Y-m-d H:i:s')
        );

        // Add currentStepID if column exists
        if ($firstStepID !== null && $hasCurrentStepIDColumn) {
            $instanceData['currentStepID'] = $firstStepID;
        }

        // Add status column based on what exists
        if ($hasStatusColumn) {
            $instanceData['status'] = 'pending';
        } elseif ($hasWorkflowStatusColumn) {
            $instanceData['workflowStatus'] = 'pending';
        }

        // Add Lapsed and Suspended if columns exist
        if ($hasLapsedColumn) {
            $instanceData['Lapsed'] = 'N';
        }
        if ($hasSuspendedColumn) {
            $instanceData['Suspended'] = 'N';
        }

        $result = $DBConn->insert_data('tija_leave_approval_instances', $instanceData);

        if ($result) {
            return $DBConn->lastInsertId();
        }

        return false;
    }

    /**
     * Get all approvers from workflow steps for a policy
     *
     * @param int $policyID Approval policy ID
     * @param object $DBConn Database connection object
     * @return array Array of approvers with step information
     */
    public static function get_workflow_approvers($policyID, $DBConn) {
        if (empty($policyID)) {
            return array();
        }

        // Check which columns exist in the step_approvers table
        $allColumns = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_step_approvers", array());
        $columnNames = array();
        if ($allColumns && count($allColumns) > 0) {
            foreach ($allColumns as $col) {
                $col = is_object($col) ? (array)$col : $col;
                $columnNames[] = $col['Field'] ?? $col['field'] ?? '';
            }
        }
        $hasStepApproverID = in_array('stepApproverID', $columnNames);
        $hasApproverID = in_array('approverID', $columnNames);
        $hasApproverType = in_array('approverType', $columnNames);
        $hasIsBackup = in_array('isBackup', $columnNames);
        $hasSuspended = in_array('Suspended', $columnNames);

        // Build SELECT clause based on available columns
        $selectFields = array();
        if ($hasStepApproverID) {
            $selectFields[] = 'sa.stepApproverID';
        } elseif ($hasApproverID) {
            $selectFields[] = 'sa.approverID AS stepApproverID';
        }

        $selectFields[] = 'sa.stepID';
        $selectFields[] = 'sa.approverUserID';

        if ($hasApproverType) {
            $selectFields[] = 'sa.approverType';
        } else {
            $selectFields[] = "NULL AS approverType";
        }

        if ($hasIsBackup) {
            $selectFields[] = 'sa.isBackup';
        } else {
            $selectFields[] = "'N' AS isBackup";
        }

        $selectFields[] = 'sa.notificationOrder';
        $selectFields[] = 's.stepOrder';
        $selectFields[] = 's.stepName';
        $selectFields[] = 's.stepDescription';
        $selectFields[] = "CONCAT(p.FirstName, ' ', p.Surname) as approverName";
        $selectFields[] = 'p.Email as approverEmail';

        $whereConditions = array('s.policyID = ?');
        $params = array(array($policyID, 'i'));

        if ($hasSuspended) {
            $whereConditions[] = "sa.Suspended = 'N'";
            $whereConditions[] = "s.Suspended = 'N'";
        } else {
            $whereConditions[] = "s.Suspended = 'N'";
        }

        $sql = "SELECT " . implode(', ', $selectFields) . "
                FROM tija_leave_approval_step_approvers sa
                INNER JOIN tija_leave_approval_steps s ON sa.stepID = s.stepID
                LEFT JOIN people p ON sa.approverUserID = p.ID
                WHERE " . implode(' AND ', $whereConditions) . "
                ORDER BY s.stepOrder, sa.notificationOrder";

        $records = $DBConn->fetch_all_rows($sql, $params);

        if (!$records) {
            // If no approvers found, try to get steps and resolve dynamic approvers
            $steps = self::leave_approval_steps(
                array('policyID' => $policyID, 'Suspended' => 'N'),
                false,
                $DBConn
            );

            return array();
        }

        $mappedRecords = array_map(function($record) {
            $mapped = is_object($record) ? (array)$record : $record;
            return $mapped;
        }, $records);

        return $mappedRecords;
    }

    /**
     * Resolve dynamic approvers for workflow steps based on employee relationships
     *
     * @param int $policyID Approval policy ID
     * @param int $employeeID Employee ID
     * @param object $DBConn Database connection object
     * @return array Array of resolved approvers with step information
     */
    public static function resolve_dynamic_workflow_approvers($policyID, $employeeID, $DBConn) {
        if (empty($policyID) || empty($employeeID)) {
            return array();
        }

        // Get workflow steps
        $steps = self::leave_approval_steps(
            array('policyID' => $policyID, 'Suspended' => 'N'),
            false,
            $DBConn
        );

        if (!$steps || count($steps) === 0) {
            return array();
        }

        // Get employee details
        $employee = Employee::employees(array('ID' => $employeeID), true, $DBConn);
        if (!$employee) {
            return array();
        }

        $resolvedApprovers = array();

        foreach ($steps as $step) {
            $stepObj = is_object($step) ? $step : (object)$step;
            $stepType = $stepObj->stepType ?? '';
            $stepOrder = isset($stepObj->stepOrder) ? (int)$stepObj->stepOrder : 0;
            $stepID = $stepObj->stepID ?? null;

            $approverUserID = null;
            $approverName = '';
            $approverEmail = '';

            switch ($stepType) {
                case 'supervisor':
                    if (!empty($employee->supervisorID)) {
                        $approverUserID = (int)$employee->supervisorID;
                        $supervisor = Employee::employees(array('ID' => $approverUserID), true, $DBConn);
                        $approverName = $supervisor ? ($supervisor->employeeName) : 'Supervisor';
                        $approverEmail = $supervisor && isset($supervisor->Email) ? $supervisor->Email : '';
                    }
                    break;

                case 'department_head':
                    $deptHead = Employee::get_employee_department_head($employeeID, $DBConn);
                    if ($deptHead && !empty($deptHead->ID)) {
                        $approverUserID = (int)$deptHead->ID;
                        $approverName = is_object($deptHead) ? ($deptHead->employeeName) : 'Department Head';
                        $approverEmail = is_object($deptHead) && isset($deptHead->Email) ? $deptHead->Email : '';
                    }
                    break;

                case 'project_manager':
                    // Try to get project manager - you may need to implement this based on your schema
                    // For now, fall back to supervisor
                    if (!empty($employee->supervisorID)) {
                        $approverUserID = (int)$employee->supervisorID;
                        $supervisor = Employee::employees(array('ID' => $approverUserID), true, $DBConn);
                        $approverName = $supervisor ? ($supervisor->employeeName) : 'Project Manager';
                        $approverEmail = $supervisor && isset($supervisor->Email) ? $supervisor->Email : '';
                    }
                    break;

                case 'hr_manager':
                    $hrManager = Employee::get_hr_manager($employee->orgDataID ?? null, $employee->entityID ?? null, $DBConn);
                    if ($hrManager && !empty($hrManager->ID)) {
                        $approverUserID = (int)$hrManager->ID;
                        $approverName = is_object($hrManager) ? ($hrManager->FirstName . ' ' . $hrManager->Surname) : 'HR Manager';
                        $approverEmail = is_object($hrManager) && isset($hrManager->Email) ? $hrManager->Email : '';
                    }
                    break;
            }

            if ($approverUserID) {
                // Ensure stepID and stepOrder are both set
                if (!$stepID || !$stepOrder) {
                    continue; // Skip this approver if step info is missing
                }

                $resolvedApprovers[] = array(
                    'stepID' => (int)$stepID,
                    'stepOrder' => (int)$stepOrder,
                    'stepName' => $stepObj->stepName ?? 'Approval Step',
                    'stepDescription' => $stepObj->stepDescription ?? null,
                    'approverUserID' => (int)$approverUserID,
                    'approverName' => $approverName,
                    'approverEmail' => $approverEmail,
                    'isBackup' => 'N',
                    'notificationOrder' => 1
                );
            }
        }

        return $resolvedApprovers;
    }

    /**
     * Get leave approval step approvers
     *
     * @param array $whereArr Parameters to filter step approvers
     * @param bool $single Whether to return a single record or not
     * @param object $DBConn Database connection object
     * @return mixed Leave approval step approvers or false on failure
     */
    public static function leave_approval_step_approvers($whereArr = array(), $single = false, $DBConn) {
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

        $sql = "SELECT a.*, CONCAT(p.FirstName, ' ', p.Surname) as approverName
                FROM tija_leave_approval_step_approvers a
                LEFT JOIN people p ON a.approverUserID = p.ID
                {$where}
                ORDER BY a.notificationOrder, a.isBackup";

        $rows = $DBConn->fetch_all_rows($sql, $params);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    // ============================================================================
    // HOLIDAYS MANAGEMENT METHODS
    // ============================================================================

    /**
     * Get holidays with enhanced jurisdiction support
     *
     * @param array $whereArr Parameters to filter holidays
     * @param bool $single Whether to return a single record or not
     * @param object $DBConn Database connection object
     * @return mixed Holidays or false on failure
     */
    public static function holidays($whereArr = array(), $single = false, $DBConn) {
        $params = array();
        $where = '';

        if (count($whereArr) > 0) {
            foreach ($whereArr as $col => $val) {
                if ($where == '') {
                    $where = "WHERE ";
                } else {
                    $where .= " AND ";
                }
                $where .= "h.{$col} = ?";
                $params[] = array($val, 's');
            }
        }

        $sql = "SELECT
            h.holidayID,
            h.DateAdded,
            h.holidayName,
            h.holidayDate,
            h.holidayType,
            h.countryID,
            h.repeatsAnnually,
            h.jurisdictionLevel,
            h.regionID,
            h.cityID,
            h.entitySpecific,
            h.applyToEmploymentTypes,
            h.excludeBusinessUnits,
            h.affectsLeaveBalance,
            h.holidayNotes,
            h.CreatedByID,
            h.LastUpdateByID,
            h.CreateDate,
            h.generatedFrom,
            h.LastUpdate,
            h.Lapsed,
            h.Suspended,
            c.countryName
        FROM tija_holidays h
        LEFT JOIN african_countries c ON h.countryID = c.countryID
        {$where}
        ORDER BY h.holidayDate ASC";

        $rows = $DBConn->fetch_all_rows($sql, $params);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get holidays applicable to a specific employee
     *
     * @param int $employeeID Employee ID
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @param object $DBConn Database connection object
     * @return array Applicable holidays
     */
    public static function get_employee_holidays($employeeID, $startDate, $endDate, $DBConn) {
        // Get employee details
        $employee = Employee::employees(['ID' => $employeeID], true, $DBConn);

        if (!$employee) {
            return array();
        }

        // Get all holidays in date range
        $params = array(
            array($startDate, 's'),
            array($endDate, 's')
        );

        $sql = "SELECT
            h.*,
            c.countryName
        FROM tija_holidays h
        LEFT JOIN african_countries c ON h.countryID = c.countryID
        WHERE h.holidayDate >= ?
        AND h.holidayDate <= ?
        AND h.Lapsed = 'N'
        AND h.Suspended = 'N'
        ORDER BY h.holidayDate ASC";

        $allHolidays = $DBConn->fetch_all_rows($sql, $params);

        if (!$allHolidays) {
            return array();
        }

        // Filter based on applicability
        $applicableHolidays = array();

        foreach ($allHolidays as $holiday) {
            if (self::is_holiday_applicable_to_employee($holiday, $employee)) {
                $applicableHolidays[] = $holiday;
            }
        }

        return $applicableHolidays;
    }

    /**
     * Check if a holiday applies to a specific employee
     *
     * @param object $holiday Holiday object
     * @param object $employee Employee object
     * @return bool True if applicable
     */
    public static function is_holiday_applicable_to_employee($holiday, $employee) {
        // Check if holiday affects leave balance
        if (isset($holiday->affectsLeaveBalance) && $holiday->affectsLeaveBalance === 'N') {
            return false;
        }

        // Check jurisdiction match
        $jurisdictionLevel = $holiday->jurisdictionLevel ?? 'country';

        switch ($jurisdictionLevel) {
            case 'global':
                $jurisdictionMatch = true;
                break;

            case 'country':
                $jurisdictionMatch = (empty($holiday->countryID) ||
                                    $holiday->countryID === 'all' ||
                                    $employee->entityCountry == $holiday->countryID);
                break;

            case 'region':
                $countryMatch = (empty($holiday->countryID) ||
                               $employee->entityCountry == $holiday->countryID);
                $regionMatch = false;

                if (!empty($holiday->regionID) && !empty($employee->entityCity)) {
                    $regionMatch = (stripos($employee->entityCity, $holiday->regionID) !== false);
                }

                $jurisdictionMatch = $countryMatch && $regionMatch;
                break;

            case 'city':
                $jurisdictionMatch = false;
                if (!empty($holiday->cityID) && !empty($employee->entityCity)) {
                    $jurisdictionMatch = (stripos($employee->entityCity, $holiday->cityID) !== false);
                }
                break;

            case 'entity':
                $jurisdictionMatch = false;
                if (!empty($holiday->entitySpecific)) {
                    $entities = explode(',', $holiday->entitySpecific);
                    $jurisdictionMatch = in_array($employee->entityID, $entities) || in_array('all', $entities);
                }
                break;

            default:
                $jurisdictionMatch = true;
        }

        if (!$jurisdictionMatch) {
            return false;
        }

        // Check employment type
        if (!empty($holiday->applyToEmploymentTypes) && $holiday->applyToEmploymentTypes !== 'all') {
            $types = explode(',', $holiday->applyToEmploymentTypes);
            if (!empty($employee->employmentType) && !in_array($employee->employmentType, $types)) {
                return false;
            }
        }

        // Check excluded business units
        if (!empty($holiday->excludeBusinessUnits) && !empty($employee->businessUnitID)) {
            $excludedUnits = explode(',', $holiday->excludeBusinessUnits);
            if (in_array($employee->businessUnitID, $excludedUnits)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate working days excluding applicable holidays and weekends
     *
     * @param int $employeeID Employee ID
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @param object $DBConn Database connection object
     * @return array Calculation details
     */
    public static function calculate_working_days_with_holidays($employeeID, $startDate, $endDate, $DBConn) {
        // Get applicable holidays
        $holidays = self::get_employee_holidays($employeeID, $startDate, $endDate, $DBConn);

        // Calculate total calendar days
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $end->modify('+1 day'); // Include end date
        $interval = $start->diff($end);
        $totalDays = $interval->days;

        // Get holiday dates
        $holidayDates = array();
        foreach ($holidays as $holiday) {
            $holidayDates[] = $holiday->holidayDate;
        }

        // Count weekends and working days
        $weekendDays = 0;
        $workingDays = 0;
        $current = clone $start;

        while ($current < $end) {
            $currentDateStr = $current->format('Y-m-d');
            $dayOfWeek = $current->format('N'); // 1 = Monday, 7 = Sunday

            if ($dayOfWeek == 6 || $dayOfWeek == 7) {
                // Weekend
                $weekendDays++;
            } elseif (in_array($currentDateStr, $holidayDates)) {
                // Holiday (already counted separately)
            } else {
                // Working day
                $workingDays++;
            }

            $current->modify('+1 day');
        }

        return array(
            'totalDays' => $totalDays,
            'workingDays' => $workingDays,
            'holidays' => $holidays,
            'holidayCount' => count($holidays),
            'weekendDays' => $weekendDays,
            'holidayDates' => $holidayDates
        );
    }

    /**
     * Get recurring holidays for annual generation
     *
     * @param object $DBConn Database connection object
     * @return array Recurring holidays
     */
    public static function get_recurring_holidays($DBConn) {
        return self::holidays([
            'repeatsAnnually' => 'Y',
            'Lapsed' => 'N'
        ], false, $DBConn);
    }

    /**
     * Check workflow approval status for an instance
     *
     * @param int $instanceID Instance ID
     * @param int $policyID Policy ID
     * @param object $DBConn Database connection object
     * @return array Approval status with steps and approvers
     */
    public static function check_workflow_approval_status($instanceID, $policyID, $DBConn) {
        if (empty($instanceID) || empty($policyID)) {
            return array(
                'steps' => array(),
                'allRequiredApproved' => false,
                'isFinalStepComplete' => false,
                'hasRejection' => false
            );
        }

        // Get all steps for the policy
        $steps = self::leave_approval_steps(
            array('policyID' => $policyID, 'Suspended' => 'N'),
            false,
            $DBConn
        );

        if (!$steps || count($steps) === 0) {
            return array(
                'steps' => array(),
                'allRequiredApproved' => false,
                'isFinalStepComplete' => false,
                'hasRejection' => false
            );
        }

        // Get workflow instance to get employee ID for dynamic approver resolution
        $instance = $DBConn->fetch_all_rows(
            "SELECT * FROM tija_leave_approval_instances WHERE instanceID = ?",
            array(array($instanceID, 'i'))
        );

        $employeeID = null;
        if ($instance && count($instance) > 0) {
            $inst = is_object($instance[0]) ? (array)$instance[0] : $instance[0];
            $leaveAppID = isset($inst['leaveApplicationID']) ? (int)$inst['leaveApplicationID'] : null;
            if ($leaveAppID) {
                $leaveApp = self::leave_applications_full(array('leaveApplicationID' => $leaveAppID), true, $DBConn);
                if ($leaveApp) {
                    $leaveApp = is_object($leaveApp) ? (array)$leaveApp : $leaveApp;
                    $employeeID = isset($leaveApp['employeeID']) ? (int)$leaveApp['employeeID'] : null;
                }
            }
        }

        // Get all approvers for the policy

        // $approvers = self::get_workflow_approvers($policyID, $DBConn);
        // echo "<h4>approvers</h4>";
        // echo $approvers ? "<pre>" . htmlspecialchars(print_r($approvers, true)) . "</pre>" : "No approvers found";

        // If no saved approvers and we have employee ID, resolve dynamic approvers
        if (empty($approvers) && $employeeID) {
            $approvers = self::resolve_dynamic_workflow_approvers($policyID, $employeeID, $DBConn);
        }

        // Get all approval actions for this instance
        $actions = $DBConn->fetch_all_rows(
            "SELECT * FROM tija_leave_approval_actions WHERE instanceID = ?",
            array(array($instanceID, 'i'))
        );

        $actionMap = array();
        if ($actions) {
            foreach ($actions as $action) {
                $action = is_object($action) ? (array)$action : $action;
                $key = $action['stepID'] . '_' . $action['approverUserID'];
                $actionMap[$key] = $action;
            }
        }

        // Get policy approval type
        $policy = self::leave_approval_policies(array('policyID' => $policyID), true, $DBConn);
        $approvalType = 'parallel';
        if ($policy) {
            $policy = is_object($policy) ? (array)$policy : $policy;
            $approvalType = isset($policy['approvalType']) ? $policy['approvalType'] : 'parallel';
        }

        // Build step status array
        $stepsData = array();
        $maxStepOrder = 0;
        $allRequiredApproved = true;
        $hasRejection = false;

        foreach ($steps as $step) {
            $step = is_object($step) ? (array)$step : $step;
            $stepID = isset($step['stepID']) ? (int)$step['stepID'] : null;
            $stepOrder = isset($step['stepOrder']) ? (int)$step['stepOrder'] : 0;
            $approvalRequired = isset($step['approvalRequired']) ? $step['approvalRequired'] : 'all';
            $isRequired = isset($step['isRequired']) && strtoupper($step['isRequired']) === 'Y';

            if ($stepOrder > $maxStepOrder) {
                $maxStepOrder = $stepOrder;
            }

            // Get approvers for this step
            $stepApprovers = array();
            foreach ($approvers as $approver) {
                if (isset($approver['stepID']) && (int)$approver['stepID'] === $stepID) {
                    $approverUserID = isset($approver['approverUserID']) ? (int)$approver['approverUserID'] : null;
                    $isBackup = isset($approver['isBackup']) && strtoupper($approver['isBackup']) === 'Y';

                    // Check if this approver has acted
                    $actionKey = $stepID . '_' . $approverUserID;
                    $hasActed = isset($actionMap[$actionKey]);
                    $action = $hasActed ? $actionMap[$actionKey] : null;

                    // Use actionDate (new column name) or fall back to decisionDate (old column name)
                    $dateActed = null;
                    if ($hasActed) {
                        if (isset($action['actionDate'])) {
                            $dateActed = $action['actionDate'];
                        } elseif (isset($action['decisionDate'])) {
                            $dateActed = $action['decisionDate'];
                        }
                    }

                    $stepApprovers[] = array(
                        'approverUserID' => $approverUserID,
                        'approverName' => isset($approver['approverName']) ? $approver['approverName'] : 'Unknown',
                        'approverEmail' => isset($approver['approverEmail']) ? $approver['approverEmail'] : '',
                        'isBackup' => $isBackup,
                        'hasActed' => $hasActed,
                        'action' => $hasActed ? (isset($action['action']) ? $action['action'] : null) : null,
                        'comments' => $hasActed ? (isset($action['comments']) ? $action['comments'] : null) : null,
                        'actionDate' => $dateActed
                    );

                    // Check for rejection
                    if ($hasActed && isset($action['action']) && strtolower($action['action']) === 'rejected') {
                        $hasRejection = true;
                    }
                }
            }

            // Determine step status
            $stepStatus = 'pending';
            $approvedCount = 0;
            $rejectedCount = 0;
            $pendingCount = 0;

            foreach ($stepApprovers as $approver) {
                if ($approver['hasActed']) {
                    if (strtolower($approver['action']) === 'approved') {
                        $approvedCount++;
                    } elseif (strtolower($approver['action']) === 'rejected') {
                        $rejectedCount++;
                    }
                } else {
                    $pendingCount++;
                }
            }

            if ($rejectedCount > 0) {
                $stepStatus = 'rejected';
            } elseif ($approvalRequired === 'all') {
                // All approvers must approve
                $totalRequired = count($stepApprovers);
                if ($approvedCount === $totalRequired && $totalRequired > 0) {
                    $stepStatus = 'approved';
                } elseif ($approvedCount > 0) {
                    $stepStatus = 'partial';
                }
            } elseif ($approvalRequired === 'any') {
                // Any approver can approve
                if ($approvedCount > 0) {
                    $stepStatus = 'approved';
                }
            }

            // Check if step is fully approved
            $isStepApproved = false;
            if ($approvalRequired === 'all') {
                $isStepApproved = ($approvedCount === count($stepApprovers) && count($stepApprovers) > 0);
            } else {
                $isStepApproved = ($approvedCount > 0);
            }

            if ($isRequired && !$isStepApproved && $stepStatus !== 'rejected') {
                $allRequiredApproved = false;
            }

            $stepsData[] = array(
                'stepID' => $stepID,
                'stepOrder' => $stepOrder,
                'stepName' => isset($step['stepName']) ? $step['stepName'] : ('Step ' . $stepOrder),
                'stepDescription' => isset($step['stepDescription']) ? $step['stepDescription'] : '',
                'approvalRequired' => $approvalRequired,
                'isRequired' => $isRequired,
                'stepStatus' => $stepStatus,
                'approvedCount' => $approvedCount,
                'rejectedCount' => $rejectedCount,
                'pendingCount' => $pendingCount,
                'totalApprovers' => count($stepApprovers),
                'approvers' => $stepApprovers
            );
        }

        // Sort steps by order
        usort($stepsData, function($a, $b) {
            return $a['stepOrder'] - $b['stepOrder'];
        });

        // Check if final step is complete
        $isFinalStepComplete = false;
        $finalStepData = null;
        if ($maxStepOrder > 0) {
            foreach ($stepsData as $step) {
                if ($step['stepOrder'] === $maxStepOrder) {
                    $finalStepData = $step;
                    $isFinalStepComplete = ($step['stepStatus'] === 'approved');
                    break;
                }
            }
        }

        // Additional check: If final step has HR manager approvals (not explicitly in workflow),
        // we need to check if all HR managers for the entity have approved
        // This handles cases where HR managers are final approvers but not in workflow steps
        if ($finalStepData) {
            // Get the leave application to find entity
            $instance = $DBConn->fetch_all_rows(
                "SELECT leaveApplicationID FROM tija_leave_approval_instances WHERE instanceID = ?",
                array(array($instanceID, 'i'))
            );

            if ($instance && count($instance) > 0) {
                $inst = is_object($instance[0]) ? (array)$instance[0] : $instance[0];
                $leaveApplicationID = isset($inst['leaveApplicationID']) ? (int)$inst['leaveApplicationID'] : null;

                if ($leaveApplicationID) {
                    $leaveApp = self::leave_applications_full(
                        array('leaveApplicationID' => $leaveApplicationID),
                        true,
                        $DBConn
                    );

                    if ($leaveApp) {
                        $leaveApp = is_object($leaveApp) ? (array)$leaveApp : $leaveApp;
                        $entityID = isset($leaveApp['entityID']) ? (int)$leaveApp['entityID'] : null;

                        if ($entityID) {
                            // Get all HR managers for this entity
                            $hrManagers = Employee::get_hr_managers_for_entity($entityID, $DBConn);
                            $hrManagerIDs = array();
                            if ($hrManagers && count($hrManagers) > 0) {
                                foreach ($hrManagers as $hr) {
                                    $hrID = is_object($hr) ? (is_array($hr) ? $hr['ID'] : null) : (is_array($hr) ? ($hr['ID'] ?? null) : null);
                                    if ($hrID) {
                                        $hrManagerIDs[] = (int)$hrID;
                                    }
                                }
                            }

                            // Check if HR managers have approved in the final step
                            // Count HR manager approvals in the final step
                            $hrApprovedCount = 0;
                            $hrRejectedCount = 0;
                            if (!empty($hrManagerIDs)) {
                                foreach ($hrManagerIDs as $hrID) {
                                    $actionKey = $finalStepData['stepID'] . '_' . $hrID;
                                    if (isset($actionMap[$actionKey])) {
                                        $hrAction = isset($actionMap[$actionKey]['action']) ? strtolower($actionMap[$actionKey]['action']) : null;
                                        if ($hrAction === 'approved') {
                                            $hrApprovedCount++;
                                        } elseif ($hrAction === 'rejected') {
                                            $hrRejectedCount++;
                                        }
                                    }
                                }
                            }

                            // If there are HR managers and the step requires 'all', check if all have approved
                            if (!empty($hrManagerIDs) && $finalStepData['approvalRequired'] === 'all') {
                                // For 'all' requirement: all explicitly assigned approvers AND all HR managers must approve
                                // If no explicit approvers, only check HR managers
                                $hasExplicitApprovers = ($finalStepData['totalApprovers'] > 0);
                                $allExplicitApproversApproved = $hasExplicitApprovers
                                    ? ($finalStepData['approvedCount'] === $finalStepData['totalApprovers'])
                                    : true; // No explicit approvers means this condition is satisfied
                                $allHRManagersApproved = ($hrApprovedCount === count($hrManagerIDs));

                                // Step is complete only if BOTH conditions are met
                                if ($allExplicitApproversApproved && $allHRManagersApproved) {
                                    $isFinalStepComplete = true;
                                } else {
                                    // Not all required approvers have approved yet
                                    $isFinalStepComplete = false;
                                    $allRequiredApproved = false;
                                }
                            } elseif (!empty($hrManagerIDs) && $finalStepData['approvalRequired'] === 'any') {
                                // If 'any' is required, check if at least one HR manager or explicitly assigned approver has approved
                                if ($hrApprovedCount > 0 || $finalStepData['approvedCount'] > 0) {
                                    $isFinalStepComplete = true;
                                }
                            }

                            // If any HR manager rejected, mark as rejected
                            if ($hrRejectedCount > 0) {
                                $hasRejection = true;
                                $isFinalStepComplete = false;
                                $allRequiredApproved = false;
                            }
                        }
                    }
                }
            }
        }

        return array(
            'steps' => $stepsData,
            'allRequiredApproved' => $allRequiredApproved && !$hasRejection,
            'isFinalStepComplete' => $isFinalStepComplete,
            'hasRejection' => $hasRejection,
            'approvalType' => $approvalType,
            'maxStepOrder' => $maxStepOrder
        );
    }

    /**
     * Get all pending approvers for a leave application
     *
     * @param int $leaveApplicationID Leave application ID
     * @param object $DBConn Database connection object
     * @return array Array of pending approvers
     */
    public static function get_all_pending_approvers($leaveApplicationID, $DBConn) {
        // Check if workflow tables exist
        $tableCheck = $DBConn->fetch_all_rows("SHOW TABLES LIKE 'tija_leave_approval_instances'", array());
        if (!$tableCheck || count($tableCheck) === 0) {
            return array();
        }

        // Get workflow instance
        $lapsedCheck = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_instances LIKE 'Lapsed'", array());
        $hasLapsedColumn = ($lapsedCheck && count($lapsedCheck) > 0);

        $whereClause = "leaveApplicationID = ?";
        $params = array(array($leaveApplicationID, 'i'));

        if ($hasLapsedColumn) {
            $whereClause .= " AND Lapsed = 'N'";
        }

        $instance = $DBConn->fetch_all_rows(
            "SELECT * FROM tija_leave_approval_instances WHERE {$whereClause}",
            $params
        );

        if (!$instance || count($instance) === 0) {
            return array();
        }

        $inst = is_object($instance[0]) ? (array)$instance[0] : $instance[0];
        $instanceID = $inst['instanceID'] ?? null;
        $policyID = $inst['policyID'] ?? null;

        if (!$instanceID || !$policyID) {
            return array();
        }

        // Get all approvers for the policy
        $approvers = self::get_workflow_approvers($policyID, $DBConn);

        // Get all actions for this instance
        $actions = $DBConn->fetch_all_rows(
            "SELECT stepID, approverUserID FROM tija_leave_approval_actions WHERE instanceID = ?",
            array(array($instanceID, 'i'))
        );

        $actedMap = array();
        if ($actions) {
            foreach ($actions as $action) {
                $action = is_object($action) ? (array)$action : $action;
                $key = $action['stepID'] . '_' . $action['approverUserID'];
                $actedMap[$key] = true;
            }
        }

        // Filter to only pending approvers
        $pendingApprovers = array();
        foreach ($approvers as $approver) {
            $stepID = isset($approver['stepID']) ? (int)$approver['stepID'] : null;
            $approverUserID = isset($approver['approverUserID']) ? (int)$approver['approverUserID'] : null;

            if ($stepID && $approverUserID) {
                $key = $stepID . '_' . $approverUserID;
                if (!isset($actedMap[$key])) {
                    $pendingApprovers[] = $approver;
                }
            }
        }

        return $pendingApprovers;
    }

    /**
     * Check if step is fully approved
     *
     * @param int $instanceID Instance ID
     * @param int $stepID Step ID
     * @param object $DBConn Database connection object
     * @return bool True if step is fully approved
     */
    public static function is_step_fully_approved($instanceID, $stepID, $DBConn) {
        // Get step details
        $step = self::leave_approval_steps(array('stepID' => $stepID), true, $DBConn);
        if (!$step) {
            return false;
        }

        $step = is_object($step) ? (array)$step : $step;
        $approvalRequired = isset($step['approvalRequired']) ? $step['approvalRequired'] : 'all';
        $policyID = isset($step['policyID']) ? (int)$step['policyID'] : null;

        // Get all approvers for this step
        $approvers = self::get_workflow_approvers($policyID, $DBConn);
        $stepApprovers = array();
        foreach ($approvers as $approver) {
            if (isset($approver['stepID']) && (int)$approver['stepID'] === $stepID) {
                $stepApprovers[] = $approver;
            }
        }

        if (count($stepApprovers) === 0) {
            return false;
        }

        // Get approval actions for this step
        $actions = $DBConn->fetch_all_rows(
            "SELECT approverUserID, action FROM tija_leave_approval_actions
             WHERE instanceID = ? AND stepID = ?",
            array(
                array($instanceID, 'i'),
                array($stepID, 'i')
            )
        );

        $approvedCount = 0;
        foreach ($actions as $action) {
            $action = is_object($action) ? (array)$action : $action;
            if (isset($action['action']) && strtolower($action['action']) === 'approved') {
                $approvedCount++;
            }
        }

        if ($approvalRequired === 'all') {
            return $approvedCount === count($stepApprovers);
        } else {
            return $approvedCount > 0;
        }
    }

    /**
     * Generate annual instances of recurring holidays
     *
     * @param int $year Target year
     * @param int $userID User performing the action
     * @param object $DBConn Database connection object
     * @return array Result with created/skipped counts
     */
    public static function generate_annual_holidays($year, $userID, $DBConn) {
        $recurringHolidays = self::get_recurring_holidays($DBConn);

        if (!$recurringHolidays) {
            return array('created' => 0, 'skipped' => 0, 'errors' => array('No recurring holidays found'));
        }

        $created = 0;
        $skipped = 0;
        $errors = array();

        foreach ($recurringHolidays as $holiday) {
            // Get original date parts
            $originalDate = new DateTime($holiday->holidayDate);
            $month = $originalDate->format('m');
            $day = $originalDate->format('d');

            // Create date for target year
            $newDate = sprintf('%04d-%02d-%02d', $year, $month, $day);

            // Check if already exists
            $existing = self::holidays([
                'holidayName' => $holiday->holidayName,
                'holidayDate' => $newDate
            ], true, $DBConn);

            if ($existing) {
                $skipped++;
                continue;
            }

            // Create new instance
            $newHoliday = array(
                'holidayName' => $holiday->holidayName,
                'holidayDate' => $newDate,
                'holidayType' => $holiday->holidayType,
                'countryID' => $holiday->countryID,
                'repeatsAnnually' => 'Y',
                'jurisdictionLevel' => $holiday->jurisdictionLevel ?? 'country',
                'regionID' => $holiday->regionID ?? null,
                'cityID' => $holiday->cityID ?? null,
                'entitySpecific' => $holiday->entitySpecific ?? null,
                'applyToEmploymentTypes' => $holiday->applyToEmploymentTypes ?? 'all',
                'excludeBusinessUnits' => $holiday->excludeBusinessUnits ?? null,
                'affectsLeaveBalance' => $holiday->affectsLeaveBalance ?? 'Y',
                'holidayNotes' => $holiday->holidayNotes ?? null,
                'CreatedByID' => $userID,
                'LastUpdateByID' => $userID,
                'generatedFrom' => $holiday->holidayID,
                'Lapsed' => 'N',
                'Suspended' => 'N'
            );

            if ($DBConn->insert_data('tija_holidays', $newHoliday)) {
                $created++;
            } else {
                $errors[] = "Failed to create: " . $holiday->holidayName;
            }
        }

        return array(
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors
        );
    }

    /**
     * Get organization-wide leave analytics
     *
     * @param int $orgDataID Organization data ID
     * @param int $entityID Entity ID (null for all entities)
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @param object $DBConn Database connection object
     * @return array Comprehensive analytics data
     */
    public static function get_organization_leave_analytics($orgDataID, $entityID, $startDate, $endDate, $DBConn) {
        $analytics = array(
            'totalApplications' => 0,
            'approvedApplications' => 0,
            'rejectedApplications' => 0,
            'pendingApplications' => 0,
            'totalLeaveDays' => 0,
            'approvedLeaveDays' => 0,
            'averageApplicationDays' => 0,
            'utilizationRate' => 0,
            'totalEmployees' => 0,
            'employeesOnLeave' => 0,
            'topLeaveType' => 'N/A',
            'peakAbsencePeriod' => 'N/A'
        );

        // Build WHERE clause
        $where = array('la.Lapsed = ?', 'la.Suspended = ?');
        $params = array(array('N', 's'), array('N', 's'));

        if ($orgDataID) {
            $where[] = 'la.orgDataID = ?';
            $params[] = array($orgDataID, 'i');
        }

        if ($entityID) {
            $where[] = 'la.entityID = ?';
            $params[] = array($entityID, 'i');
        }

        $where[] = 'la.startDate >= ?';
        $where[] = 'la.startDate <= ?';
        $params[] = array($startDate, 's');
        $params[] = array($endDate, 's');

        $whereClause = implode(' AND ', $where);

        // Get application statistics
        $sql = "SELECT
                    COUNT(*) as totalApplications,
                    SUM(CASE WHEN la.leaveStatusID = 6 THEN 1 ELSE 0 END) as approvedApplications,
                    SUM(CASE WHEN la.leaveStatusID = 4 THEN 1 ELSE 0 END) as rejectedApplications,
                    SUM(CASE WHEN la.leaveStatusID = 3 THEN 1 ELSE 0 END) as pendingApplications,
                    SUM(la.noOfDays) as totalLeaveDays,
                    SUM(CASE WHEN la.leaveStatusID = 6 THEN la.noOfDays ELSE 0 END) as approvedLeaveDays,
                    AVG(la.noOfDays) as averageApplicationDays
                FROM tija_leave_applications la
                WHERE {$whereClause}";

        $result = $DBConn->fetch_all_rows($sql, $params);
        if ($result && count($result) > 0) {
            $row = is_object($result[0]) ? (array)$result[0] : $result[0];
            $analytics['totalApplications'] = (int)($row['totalApplications'] ?? 0);
            $analytics['approvedApplications'] = (int)($row['approvedApplications'] ?? 0);
            $analytics['rejectedApplications'] = (int)($row['rejectedApplications'] ?? 0);
            $analytics['pendingApplications'] = (int)($row['pendingApplications'] ?? 0);
            $analytics['totalLeaveDays'] = (float)($row['totalLeaveDays'] ?? 0);
            $analytics['approvedLeaveDays'] = (float)($row['approvedLeaveDays'] ?? 0);
            $analytics['averageApplicationDays'] = round((float)($row['averageApplicationDays'] ?? 0), 1);
        }

        // Get employee count
        $empWhere = array('ud.Lapsed = ?', 'ud.Suspended = ?');
        $empParams = array(array('N', 's'), array('N', 's'));

        if ($orgDataID) {
            $empWhere[] = 'ud.orgDataID = ?';
            $empParams[] = array($orgDataID, 'i');
        }

        if ($entityID) {
            $empWhere[] = 'ud.entityID = ?';
            $empParams[] = array($entityID, 'i');
        }

        $empWhereClause = implode(' AND ', $empWhere);

        $empSql = "SELECT COUNT(DISTINCT ud.ID) as totalEmployees
                   FROM user_details ud
                   WHERE {$empWhereClause}";

        $empResult = $DBConn->fetch_all_rows($empSql, $empParams);
        if ($empResult && count($empResult) > 0) {
            $empRow = is_object($empResult[0]) ? (array)$empResult[0] : $empResult[0];
            $analytics['totalEmployees'] = (int)($empRow['totalEmployees'] ?? 0);
        }

        // Calculate utilization rate
        if ($analytics['totalEmployees'] > 0) {
            $analytics['utilizationRate'] = round(($analytics['approvedLeaveDays'] / ($analytics['totalEmployees'] * 20)) * 100, 1);
        }

        // Get employees currently on leave
        $currentDate = date('Y-m-d');
        $onLeaveSql = "SELECT COUNT(DISTINCT la.employeeID) as employeesOnLeave
                       FROM tija_leave_applications la
                       WHERE {$whereClause}
                       AND la.startDate <= ?
                       AND la.endDate >= ?
                       AND la.leaveStatusID = 6";

        $onLeaveParams = array_merge($params, array(array($currentDate, 's'), array($currentDate, 's')));
        $onLeaveResult = $DBConn->fetch_all_rows($onLeaveSql, $onLeaveParams);
        if ($onLeaveResult && count($onLeaveResult) > 0) {
            $onLeaveRow = is_object($onLeaveResult[0]) ? (array)$onLeaveResult[0] : $onLeaveResult[0];
            $analytics['employeesOnLeave'] = (int)($onLeaveRow['employeesOnLeave'] ?? 0);
        }

        // Get top leave type
        $typeSql = "SELECT lt.leaveTypeName, COUNT(*) as count
                    FROM tija_leave_applications la
                    LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
                    WHERE {$whereClause}
                    GROUP BY la.leaveTypeID
                    ORDER BY count DESC
                    LIMIT 1";

        $typeResult = $DBConn->fetch_all_rows($typeSql, $params);
        if ($typeResult && count($typeResult) > 0) {
            $typeRow = is_object($typeResult[0]) ? (array)$typeResult[0] : $typeResult[0];
            $analytics['topLeaveType'] = $typeRow['leaveTypeName'] ?? 'N/A';
        }

        // Get peak absence period (month with most leave days)
        $peakSql = "SELECT DATE_FORMAT(la.startDate, '%Y-%m') as month,
                           DATE_FORMAT(la.startDate, '%M %Y') as monthName,
                           SUM(la.noOfDays) as totalDays
                    FROM tija_leave_applications la
                    WHERE {$whereClause}
                    AND la.leaveStatusID = 6
                    GROUP BY month
                    ORDER BY totalDays DESC
                    LIMIT 1";

        $peakResult = $DBConn->fetch_all_rows($peakSql, $params);
        if ($peakResult && count($peakResult) > 0) {
            $peakRow = is_object($peakResult[0]) ? (array)$peakResult[0] : $peakResult[0];
            $analytics['peakAbsencePeriod'] = $peakRow['monthName'] ?? 'N/A';
        }

        return $analytics;
    }

    /**
     * Get departmental leave breakdown
     *
     * @param int $orgDataID Organization data ID
     * @param int $entityID Entity ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @param object $DBConn Database connection object
     * @return array Department-wise breakdown
     */
    public static function get_departmental_leave_breakdown($orgDataID, $entityID, $startDate, $endDate, $DBConn) {
        $where = array('la.Lapsed = ?', 'la.Suspended = ?');
        $params = array(array('N', 's'), array('N', 's'));

        if ($orgDataID) {
            $where[] = 'la.orgDataID = ?';
            $params[] = array($orgDataID, 'i');
        }

        if ($entityID) {
            $where[] = 'la.entityID = ?';
            $params[] = array($entityID, 'i');
        }

        $where[] = 'la.startDate >= ?';
        $where[] = 'la.startDate <= ?';
        $params[] = array($startDate, 's');
        $params[] = array($endDate, 's');

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT
                    COALESCE(bu.businessUnitName, 'Unassigned') as departmentName,
                    COALESCE(bu.businessUnitID, 0) as departmentID,
                    COUNT(*) as totalApplications,
                    SUM(CASE WHEN la.leaveStatusID = 6 THEN 1 ELSE 0 END) as approvedApplications,
                    SUM(CASE WHEN la.leaveStatusID = 4 THEN 1 ELSE 0 END) as rejectedApplications,
                    SUM(la.noOfDays) as totalDays,
                    SUM(CASE WHEN la.leaveStatusID = 6 THEN la.noOfDays ELSE 0 END) as approvedDays,
                    AVG(la.noOfDays) as averageDays,
                    COUNT(DISTINCT la.employeeID) as uniqueEmployees
                FROM tija_leave_applications la
                LEFT JOIN people p ON la.employeeID = p.ID
                LEFT JOIN user_details ud ON p.ID = ud.ID
                LEFT JOIN tija_business_units bu ON ud.businessUnitID = bu.businessUnitID
                WHERE {$whereClause}
                GROUP BY bu.businessUnitID
                ORDER BY approvedDays DESC";

        $rows = $DBConn->fetch_all_rows($sql, $params);

        $breakdown = array();
        if ($rows) {
            foreach ($rows as $row) {
                $rowData = is_object($row) ? (array)$row : $row;
                $breakdown[] = array(
                    'departmentName' => $rowData['departmentName'],
                    'departmentID' => (int)$rowData['departmentID'],
                    'totalApplications' => (int)$rowData['totalApplications'],
                    'approvedApplications' => (int)$rowData['approvedApplications'],
                    'rejectedApplications' => (int)$rowData['rejectedApplications'],
                    'totalDays' => (float)$rowData['totalDays'],
                    'approvedDays' => (float)$rowData['approvedDays'],
                    'averageDays' => round((float)$rowData['averageDays'], 1),
                    'uniqueEmployees' => (int)$rowData['uniqueEmployees'],
                    'utilizationRate' => $rowData['uniqueEmployees'] > 0
                        ? round(($rowData['approvedDays'] / ($rowData['uniqueEmployees'] * 20)) * 100, 1)
                        : 0
                );
            }
        }

        return $breakdown;
    }

    /**
     * Get leave type distribution
     *
     * @param int $orgDataID Organization data ID
     * @param int $entityID Entity ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @param object $DBConn Database connection object
     * @return array Leave type distribution
     */
    public static function get_leave_type_distribution($orgDataID, $entityID, $startDate, $endDate, $DBConn) {
        $where = array('la.Lapsed = ?', 'la.Suspended = ?', 'la.leaveStatusID = ?');
        $params = array(array('N', 's'), array('N', 's'), array(6, 'i')); // Only approved

        if ($orgDataID) {
            $where[] = 'la.orgDataID = ?';
            $params[] = array($orgDataID, 'i');
        }

        if ($entityID) {
            $where[] = 'la.entityID = ?';
            $params[] = array($entityID, 'i');
        }

        $where[] = 'la.startDate >= ?';
        $where[] = 'la.startDate <= ?';
        $params[] = array($startDate, 's');
        $params[] = array($endDate, 's');

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT
                    lt.leaveTypeName,
                    lt.leaveTypeID,
                    COUNT(*) as applicationCount,
                    SUM(la.noOfDays) as totalDays,
                    AVG(la.noOfDays) as averageDays,
                    COUNT(DISTINCT la.employeeID) as uniqueEmployees
                FROM tija_leave_applications la
                LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
                WHERE {$whereClause}
                GROUP BY la.leaveTypeID
                ORDER BY totalDays DESC";

        $rows = $DBConn->fetch_all_rows($sql, $params);

        $distribution = array();
        $totalDaysAll = 0;

        if ($rows) {
            foreach ($rows as $row) {
                $rowData = is_object($row) ? (array)$row : $row;
                $totalDaysAll += (float)$rowData['totalDays'];
            }

            foreach ($rows as $row) {
                $rowData = is_object($row) ? (array)$row : $row;
                $totalDays = (float)$rowData['totalDays'];
                $distribution[] = array(
                    'leaveTypeName' => $rowData['leaveTypeName'],
                    'leaveTypeID' => (int)$rowData['leaveTypeID'],
                    'applicationCount' => (int)$rowData['applicationCount'],
                    'totalDays' => $totalDays,
                    'averageDays' => round((float)$rowData['averageDays'], 1),
                    'uniqueEmployees' => (int)$rowData['uniqueEmployees'],
                    'percentage' => $totalDaysAll > 0 ? round(($totalDays / $totalDaysAll) * 100, 1) : 0
                );
            }
        }

        return $distribution;
    }

    /**
     * Get approval workflow metrics
     *
     * @param int $orgDataID Organization data ID
     * @param int $entityID Entity ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @param object $DBConn Database connection object
     * @return array Workflow performance metrics
     */
    public static function get_approval_workflow_metrics($orgDataID, $entityID, $startDate, $endDate, $DBConn) {
        $metrics = array(
            'averageApprovalTime' => 0,
            'medianApprovalTime' => 0,
            'rejectionRate' => 0,
            'approvalRate' => 0,
            'averageStepsToApproval' => 0,
            'bottleneckStep' => 'N/A',
            'fastestApprover' => 'N/A',
            'slowestApprover' => 'N/A',
            'stepMetrics' => array()
        );

        // Build WHERE clause
        $where = array('la.Lapsed = ?', 'la.Suspended = ?');
        $params = array(array('N', 's'), array('N', 's'));

        if ($orgDataID) {
            $where[] = 'la.orgDataID = ?';
            $params[] = array($orgDataID, 'i');
        }

        if ($entityID) {
            $where[] = 'la.entityID = ?';
            $params[] = array($entityID, 'i');
        }

        $where[] = 'la.startDate >= ?';
        $where[] = 'la.startDate <= ?';
        $params[] = array($startDate, 's');
        $params[] = array($endDate, 's');
        $where[] = 'la.leaveStatusID IN (4, 6)'; // Approved or Rejected

        $whereClause = implode(' AND ', $where);

        // Calculate approval times
        $sql = "SELECT
                    la.leaveApplicationID,
                    la.leaveStatusID,
                    la.dateApplied,
                    la.LastUpdate,
                    TIMESTAMPDIFF(HOUR, la.dateApplied, la.LastUpdate) as approvalTimeHours
                FROM tija_leave_applications la
                WHERE {$whereClause}
                AND la.dateApplied IS NOT NULL";

        $result = $DBConn->fetch_all_rows($sql, $params);

        if ($result && count($result) > 0) {
            $approvalTimes = array();
            $approvedCount = 0;
            $rejectedCount = 0;

            foreach ($result as $row) {
                $rowData = is_object($row) ? (array)$row : $row;
                $statusID = (int)$rowData['leaveStatusID'];
                $hours = (float)($rowData['approvalTimeHours'] ?? 0);

                if ($hours > 0) {
                    $approvalTimes[] = $hours;
                }

                if ($statusID == 6) {
                    $approvedCount++;
                } elseif ($statusID == 4) {
                    $rejectedCount++;
                }
            }

            $totalProcessed = $approvedCount + $rejectedCount;

            if (count($approvalTimes) > 0) {
                $metrics['averageApprovalTime'] = round(array_sum($approvalTimes) / count($approvalTimes), 1);
                sort($approvalTimes);
                $metrics['medianApprovalTime'] = $approvalTimes[floor(count($approvalTimes) / 2)];
            }

            if ($totalProcessed > 0) {
                $metrics['rejectionRate'] = round(($rejectedCount / $totalProcessed) * 100, 1);
                $metrics['approvalRate'] = round(($approvedCount / $totalProcessed) * 100, 1);
            }
        }

        // Get step-level metrics (if workflow tables exist)
        $tableCheck = $DBConn->fetch_all_rows("SHOW TABLES LIKE 'tija_leave_approval_actions'", array());
        if ($tableCheck && count($tableCheck) > 0) {
            $stepSql = "SELECT
                            s.stepName,
                            s.stepOrder,
                            COUNT(*) as actionCount,
                            SUM(CASE WHEN act.action = 'approved' THEN 1 ELSE 0 END) as approvedCount,
                            SUM(CASE WHEN act.action = 'rejected' THEN 1 ELSE 0 END) as rejectedCount,
                            AVG(act.responseTime) as avgResponseTime
                        FROM tija_leave_approval_actions act
                        INNER JOIN tija_leave_approval_instances i ON act.instanceID = i.instanceID
                        INNER JOIN tija_leave_approval_steps s ON act.stepID = s.stepID
                        INNER JOIN tija_leave_applications la ON i.leaveApplicationID = la.leaveApplicationID
                        WHERE {$whereClause}
                        GROUP BY s.stepID
                        ORDER BY s.stepOrder";

            $stepResult = $DBConn->fetch_all_rows($stepSql, $params);
            if ($stepResult) {
                foreach ($stepResult as $row) {
                    $rowData = is_object($row) ? (array)$row : $row;
                    $metrics['stepMetrics'][] = array(
                        'stepName' => $rowData['stepName'],
                        'stepOrder' => (int)$rowData['stepOrder'],
                        'actionCount' => (int)$rowData['actionCount'],
                        'approvedCount' => (int)$rowData['approvedCount'],
                        'rejectedCount' => (int)$rowData['rejectedCount'],
                        'avgResponseTime' => round((float)($rowData['avgResponseTime'] ?? 0), 1)
                    );
                }
            }
        }

        return $metrics;
    }

    /**
     * Get concurrent absence analysis
     *
     * @param int $orgDataID Organization data ID
     * @param int $entityID Entity ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @param object $DBConn Database connection object
     * @return array Concurrent absence data
     */
    public static function get_concurrent_absence_analysis($orgDataID, $entityID, $startDate, $endDate, $DBConn) {
        $analysis = array(
            'maxConcurrentAbsences' => 0,
            'maxConcurrentDate' => null,
            'averageConcurrentAbsences' => 0,
            'highRiskDates' => array(),
            'departmentalImpact' => array(),
            'dailyAbsences' => array()
        );

        $where = array('la.Lapsed = ?', 'la.Suspended = ?', 'la.leaveStatusID = ?');
        $params = array(array('N', 's'), array('N', 's'), array(6, 'i'));

        if ($orgDataID) {
            $where[] = 'la.orgDataID = ?';
            $params[] = array($orgDataID, 'i');
        }

        if ($entityID) {
            $where[] = 'la.entityID = ?';
            $params[] = array($entityID, 'i');
        }

        $whereClause = implode(' AND ', $where);

        // Get daily absence counts
        $sql = "SELECT
                    DATE(calendar.date) as absenceDate,
                    COUNT(DISTINCT la.employeeID) as employeesAbsent,
                    GROUP_CONCAT(DISTINCT CONCAT(p.FirstName, ' ', p.Surname) SEPARATOR ', ') as employeeNames
                FROM (
                    SELECT DATE_ADD(? , INTERVAL seq.seq DAY) as date
                    FROM (
                        SELECT 0 as seq UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION
                        SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION
                        SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29 UNION
                        SELECT 30 UNION SELECT 31 UNION SELECT 32 UNION SELECT 33 UNION SELECT 34 UNION SELECT 35 UNION SELECT 36 UNION SELECT 37 UNION SELECT 38 UNION SELECT 39 UNION
                        SELECT 40 UNION SELECT 41 UNION SELECT 42 UNION SELECT 43 UNION SELECT 44 UNION SELECT 45 UNION SELECT 46 UNION SELECT 47 UNION SELECT 48 UNION SELECT 49 UNION
                        SELECT 50 UNION SELECT 51 UNION SELECT 52 UNION SELECT 53 UNION SELECT 54 UNION SELECT 55 UNION SELECT 56 UNION SELECT 57 UNION SELECT 58 UNION SELECT 59 UNION
                        SELECT 60 UNION SELECT 61 UNION SELECT 62 UNION SELECT 63 UNION SELECT 64 UNION SELECT 65 UNION SELECT 66 UNION SELECT 67 UNION SELECT 68 UNION SELECT 69 UNION
                        SELECT 70 UNION SELECT 71 UNION SELECT 72 UNION SELECT 73 UNION SELECT 74 UNION SELECT 75 UNION SELECT 76 UNION SELECT 77 UNION SELECT 78 UNION SELECT 79 UNION
                        SELECT 80 UNION SELECT 81 UNION SELECT 82 UNION SELECT 83 UNION SELECT 84 UNION SELECT 85 UNION SELECT 86 UNION SELECT 87 UNION SELECT 88 UNION SELECT 89 UNION
                        SELECT 90 UNION SELECT 91 UNION SELECT 92 UNION SELECT 93 UNION SELECT 94 UNION SELECT 95 UNION SELECT 96 UNION SELECT 97 UNION SELECT 98 UNION SELECT 99 UNION
                        SELECT 100 UNION SELECT 101 UNION SELECT 102 UNION SELECT 103 UNION SELECT 104 UNION SELECT 105 UNION SELECT 106 UNION SELECT 107 UNION SELECT 108 UNION SELECT 109 UNION
                        SELECT 110 UNION SELECT 111 UNION SELECT 112 UNION SELECT 113 UNION SELECT 114 UNION SELECT 115 UNION SELECT 116 UNION SELECT 117 UNION SELECT 118 UNION SELECT 119 UNION
                        SELECT 120 UNION SELECT 121 UNION SELECT 122 UNION SELECT 123 UNION SELECT 124 UNION SELECT 125 UNION SELECT 126 UNION SELECT 127 UNION SELECT 128 UNION SELECT 129 UNION
                        SELECT 130 UNION SELECT 131 UNION SELECT 132 UNION SELECT 133 UNION SELECT 134 UNION SELECT 135 UNION SELECT 136 UNION SELECT 137 UNION SELECT 138 UNION SELECT 139 UNION
                        SELECT 140 UNION SELECT 141 UNION SELECT 142 UNION SELECT 143 UNION SELECT 144 UNION SELECT 145 UNION SELECT 146 UNION SELECT 147 UNION SELECT 148 UNION SELECT 149 UNION
                        SELECT 150 UNION SELECT 151 UNION SELECT 152 UNION SELECT 153 UNION SELECT 154 UNION SELECT 155 UNION SELECT 156 UNION SELECT 157 UNION SELECT 158 UNION SELECT 159 UNION
                        SELECT 160 UNION SELECT 161 UNION SELECT 162 UNION SELECT 163 UNION SELECT 164 UNION SELECT 165 UNION SELECT 166 UNION SELECT 167 UNION SELECT 168 UNION SELECT 169 UNION
                        SELECT 170 UNION SELECT 171 UNION SELECT 172 UNION SELECT 173 UNION SELECT 174 UNION SELECT 175 UNION SELECT 176 UNION SELECT 177 UNION SELECT 178 UNION SELECT 179 UNION
                        SELECT 180 UNION SELECT 181 UNION SELECT 182 UNION SELECT 183 UNION SELECT 184 UNION SELECT 185 UNION SELECT 186 UNION SELECT 187 UNION SELECT 188 UNION SELECT 189 UNION
                        SELECT 190 UNION SELECT 191 UNION SELECT 192 UNION SELECT 193 UNION SELECT 194 UNION SELECT 195 UNION SELECT 196 UNION SELECT 197 UNION SELECT 198 UNION SELECT 199 UNION
                        SELECT 200 UNION SELECT 201 UNION SELECT 202 UNION SELECT 203 UNION SELECT 204 UNION SELECT 205 UNION SELECT 206 UNION SELECT 207 UNION SELECT 208 UNION SELECT 209 UNION
                        SELECT 210 UNION SELECT 211 UNION SELECT 212 UNION SELECT 213 UNION SELECT 214 UNION SELECT 215 UNION SELECT 216 UNION SELECT 217 UNION SELECT 218 UNION SELECT 219 UNION
                        SELECT 220 UNION SELECT 221 UNION SELECT 222 UNION SELECT 223 UNION SELECT 224 UNION SELECT 225 UNION SELECT 226 UNION SELECT 227 UNION SELECT 228 UNION SELECT 229 UNION
                        SELECT 230 UNION SELECT 231 UNION SELECT 232 UNION SELECT 233 UNION SELECT 234 UNION SELECT 235 UNION SELECT 236 UNION SELECT 237 UNION SELECT 238 UNION SELECT 239 UNION
                        SELECT 240 UNION SELECT 241 UNION SELECT 242 UNION SELECT 243 UNION SELECT 244 UNION SELECT 245 UNION SELECT 246 UNION SELECT 247 UNION SELECT 248 UNION SELECT 249 UNION
                        SELECT 250 UNION SELECT 251 UNION SELECT 252 UNION SELECT 253 UNION SELECT 254 UNION SELECT 255 UNION SELECT 256 UNION SELECT 257 UNION SELECT 258 UNION SELECT 259 UNION
                        SELECT 260 UNION SELECT 261 UNION SELECT 262 UNION SELECT 263 UNION SELECT 264 UNION SELECT 265 UNION SELECT 266 UNION SELECT 267 UNION SELECT 268 UNION SELECT 269 UNION
                        SELECT 270 UNION SELECT 271 UNION SELECT 272 UNION SELECT 273 UNION SELECT 274 UNION SELECT 275 UNION SELECT 276 UNION SELECT 277 UNION SELECT 278 UNION SELECT 279 UNION
                        SELECT 280 UNION SELECT 281 UNION SELECT 282 UNION SELECT 283 UNION SELECT 284 UNION SELECT 285 UNION SELECT 286 UNION SELECT 287 UNION SELECT 288 UNION SELECT 289 UNION
                        SELECT 290 UNION SELECT 291 UNION SELECT 292 UNION SELECT 293 UNION SELECT 294 UNION SELECT 295 UNION SELECT 296 UNION SELECT 297 UNION SELECT 298 UNION SELECT 299 UNION
                        SELECT 300 UNION SELECT 301 UNION SELECT 302 UNION SELECT 303 UNION SELECT 304 UNION SELECT 305 UNION SELECT 306 UNION SELECT 307 UNION SELECT 308 UNION SELECT 309 UNION
                        SELECT 310 UNION SELECT 311 UNION SELECT 312 UNION SELECT 313 UNION SELECT 314 UNION SELECT 315 UNION SELECT 316 UNION SELECT 317 UNION SELECT 318 UNION SELECT 319 UNION
                        SELECT 320 UNION SELECT 321 UNION SELECT 322 UNION SELECT 323 UNION SELECT 324 UNION SELECT 325 UNION SELECT 326 UNION SELECT 327 UNION SELECT 328 UNION SELECT 329 UNION
                        SELECT 330 UNION SELECT 331 UNION SELECT 332 UNION SELECT 333 UNION SELECT 334 UNION SELECT 335 UNION SELECT 336 UNION SELECT 337 UNION SELECT 338 UNION SELECT 339 UNION
                        SELECT 340 UNION SELECT 341 UNION SELECT 342 UNION SELECT 343 UNION SELECT 344 UNION SELECT 345 UNION SELECT 346 UNION SELECT 347 UNION SELECT 348 UNION SELECT 349 UNION
                        SELECT 350 UNION SELECT 351 UNION SELECT 352 UNION SELECT 353 UNION SELECT 354 UNION SELECT 355 UNION SELECT 356 UNION SELECT 357 UNION SELECT 358 UNION SELECT 359 UNION
                        SELECT 360 UNION SELECT 361 UNION SELECT 362 UNION SELECT 363 UNION SELECT 364 UNION SELECT 365
                    ) seq
                    WHERE DATE_ADD(?, INTERVAL seq.seq DAY) <= ?
                ) calendar
                LEFT JOIN tija_leave_applications la ON calendar.date BETWEEN la.startDate AND la.endDate
                    AND {$whereClause}
                LEFT JOIN people p ON la.employeeID = p.ID
                GROUP BY absenceDate
                HAVING employeesAbsent > 0
                ORDER BY absenceDate";

        $calendarParams = array(
            array($startDate, 's'),
            array($startDate, 's'),
            array($endDate, 's')
        );
        $calendarParams = array_merge($calendarParams, $params);

        $dailyResult = $DBConn->fetch_all_rows($sql, $calendarParams);

        if ($dailyResult) {
            $maxCount = 0;
            $totalAbsences = 0;
            $dayCount = 0;

            foreach ($dailyResult as $row) {
                $rowData = is_object($row) ? (array)$row : $row;
                $count = (int)$rowData['employeesAbsent'];
                $date = $rowData['absenceDate'];

                $analysis['dailyAbsences'][] = array(
                    'date' => $date,
                    'count' => $count,
                    'employees' => $rowData['employeeNames'] ?? ''
                );

                $totalAbsences += $count;
                $dayCount++;

                if ($count > $maxCount) {
                    $maxCount = $count;
                    $analysis['maxConcurrentDate'] = $date;
                }

                // High risk if more than 10% of workforce absent
                if ($count > ($metrics['totalEmployees'] ?? 0) * 0.1 && $count >= 3) {
                    $analysis['highRiskDates'][] = array(
                        'date' => $date,
                        'count' => $count,
                        'employees' => $rowData['employeeNames'] ?? ''
                    );
                }
            }

            $analysis['maxConcurrentAbsences'] = $maxCount;
            $analysis['averageConcurrentAbsences'] = $dayCount > 0 ? round($totalAbsences / $dayCount, 1) : 0;
        }

        return $analysis;
    }

    /**
     * Get employee leave detailed analysis
     *
     * @param int $employeeID Employee ID
     * @param int $year Year (null for all)
     * @param object $DBConn Database connection object
     * @return array Detailed employee leave data
     */
    public static function get_employee_leave_detailed($employeeID, $year, $DBConn) {
        $where = array('la.employeeID = ?', 'la.Lapsed = ?', 'la.Suspended = ?');
        $params = array(array($employeeID, 'i'), array('N', 's'), array('N', 's'));

        if ($year) {
            $where[] = 'YEAR(la.startDate) = ?';
            $params[] = array($year, 'i');
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT
                    la.*,
                    lt.leaveTypeName,
                    ls.leaveStatusName,
                    lp.leavePeriodName,
                    CONCAT(approver.FirstName, ' ', approver.Surname) as approverName
                FROM tija_leave_applications la
                LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
                LEFT JOIN tija_leave_status ls ON la.leaveStatusID = ls.leaveStatusID
                LEFT JOIN tija_leave_periods lp ON la.leavePeriodID = lp.leavePeriodID
                LEFT JOIN tija_leave_approvals lappr ON la.leaveApplicationID = lappr.leaveApplicationID
                LEFT JOIN people approver ON lappr.leaveApproverID = approver.ID
                WHERE {$whereClause}
                ORDER BY la.startDate DESC";

        $rows = $DBConn->fetch_all_rows($sql, $params);

        $applications = array();
        if ($rows) {
            foreach ($rows as $row) {
                $rowData = is_object($row) ? (array)$row : $row;
                $applications[] = $rowData;
            }
        }

        // Get summary statistics
        $summary = array(
            'totalApplications' => count($applications),
            'totalDaysTaken' => 0,
            'approvedApplications' => 0,
            'rejectedApplications' => 0,
            'pendingApplications' => 0
        );

        foreach ($applications as $app) {
            $summary['totalDaysTaken'] += (float)($app['noOfDays'] ?? 0);

            $statusID = (int)($app['leaveStatusID'] ?? 0);
            if ($statusID == 6) {
                $summary['approvedApplications']++;
            } elseif ($statusID == 4) {
                $summary['rejectedApplications']++;
            } elseif ($statusID == 3) {
                $summary['pendingApplications']++;
            }
        }

        return array(
            'applications' => $applications,
            'summary' => $summary
        );
    }

    /**
     * Get monthly leave trends
     *
     * @param int $orgDataID Organization data ID
     * @param int $entityID Entity ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @param object $DBConn Database connection object
     * @return array Monthly trends data
     */
    public static function get_monthly_leave_trends($orgDataID, $entityID, $startDate, $endDate, $DBConn) {
        $where = array('la.Lapsed = ?', 'la.Suspended = ?', 'la.leaveStatusID = ?');
        $params = array(array('N', 's'), array('N', 's'), array(6, 'i'));

        if ($orgDataID) {
            $where[] = 'la.orgDataID = ?';
            $params[] = array($orgDataID, 'i');
        }

        if ($entityID) {
            $where[] = 'la.entityID = ?';
            $params[] = array($entityID, 'i');
        }

        $where[] = 'la.startDate >= ?';
        $where[] = 'la.startDate <= ?';
        $params[] = array($startDate, 's');
        $params[] = array($endDate, 's');

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT
                    DATE_FORMAT(la.startDate, '%Y-%m') as month,
                    DATE_FORMAT(la.startDate, '%b %Y') as monthLabel,
                    COUNT(*) as applicationCount,
                    SUM(la.noOfDays) as totalDays,
                    COUNT(DISTINCT la.employeeID) as uniqueEmployees,
                    AVG(la.noOfDays) as averageDays
                FROM tija_leave_applications la
                WHERE {$whereClause}
                GROUP BY month
                ORDER BY month";

        $rows = $DBConn->fetch_all_rows($sql, $params);

        $trends = array();
        if ($rows) {
            foreach ($rows as $row) {
                $rowData = is_object($row) ? (array)$row : $row;
                $trends[] = array(
                    'month' => $rowData['month'],
                    'monthLabel' => $rowData['monthLabel'],
                    'applicationCount' => (int)$rowData['applicationCount'],
                    'totalDays' => (float)$rowData['totalDays'],
                    'uniqueEmployees' => (int)$rowData['uniqueEmployees'],
                    'averageDays' => round((float)$rowData['averageDays'], 1)
                );
            }
        }

        return $trends;
    }

}?>
