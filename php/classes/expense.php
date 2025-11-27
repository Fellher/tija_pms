<?php
/**
 * Unified Expense Management Class - Single Source of Truth
 * Handles all expense-related database operations using the unified tija_expense table
 * @package    Tija CRM
 * @subpackage Expense Management
 * @version    3.0 - Unified System
 * @created    2024-12-15
 * @updated    2024-12-15 - Unified System Implementation
 */

class Expense {
    
    // ========================================
    // UNIFIED EXPENSE SYSTEM - SINGLE SOURCE OF TRUTH
    // ========================================
    
    /**
     * Get expenses with full details (UNIFIED METHOD)
     * This is the main method for retrieving expenses - all other methods redirect here
     * @param array $whereArr - Array of WHERE conditions
     * @param bool $single - Return single record or array
     * @param object $DBConn - Database connection object
     * @return mixed - Single record or array of records
     */
    public static function get_expenses($whereArr = array(), $single = false, $DBConn) {
        $query = "SELECT e.*, 
                         CONCAT(emp.FirstName, ' ', emp.Surname) as employeeName,
                         ud.payrollNo as employeeCode,
                         et.typeName as expenseTypeName,
                         et.typeCode as expenseTypeCode,
                         et.typeDescription as expenseTypeDescription,
                         et.isReimbursable as expenseTypeReimbursable,
                         et.isPettyCash as expenseTypePettyCash,
                         et.requiresReceipt as expenseTypeRequiresReceipt,
                         et.approvalLevel as expenseTypeApprovalLevel,
                         et.maxAmount as expenseTypeMaxAmount,
                         et.minAmount as expenseTypeMinAmount,
                         et.autoApproveLimit as expenseTypeAutoApproveLimit,
                         et.hasBudgetLimit as expenseTypeHasBudgetLimit,
                         et.monthlyBudgetLimit as expenseTypeMonthlyBudgetLimit,
                         et.yearlyBudgetLimit as expenseTypeYearlyBudgetLimit,
                         et.budgetPeriod as expenseTypeBudgetPeriod,
                         et.isTaxable as expenseTypeTaxable,
                         et.taxRate as expenseTypeTaxRate,
                         et.taxInclusive as expenseTypeTaxInclusive,
                         et.reimbursementRate as expenseTypeReimbursementRate,
                         et.reimbursementMethod as expenseTypeReimbursementMethod,
                         et.requiresJustification as expenseTypeRequiresJustification,
                         et.requiresProjectLink as expenseTypeRequiresProjectLink,
                         et.requiresClientLink as expenseTypeRequiresClientLink,
                         et.requiresSalesCaseLink as expenseTypeRequiresSalesCaseLink,
                         et.defaultCurrency as expenseTypeDefaultCurrency,
                         et.expenseValidityDays as expenseTypeValidityDays,
                         et.submissionDeadlineDays as expenseTypeSubmissionDeadline,
                         et.approvalDeadlineDays as expenseTypeApprovalDeadline,
                         et.paymentDeadlineDays as expenseTypePaymentDeadline,
                         ec.categoryName as expenseCategoryName,
                         ec.categoryCode as expenseCategoryCode,
                         ec.categoryDescription as expenseCategoryDescription,
                         es.statusName as expenseStatusName,
                         es.statusDescription as expenseStatusDescription,
                         es.statusColor as expenseStatusColor,
                         p.projectName,
                         p.projectCode,
                         c.clientName,
                         c.clientCode,
                         sc.salesCaseName,
                         CONCAT(approver.FirstName, ' ', approver.Surname) as approverName,
                         CONCAT(payer.FirstName, ' ', payer.Surname) as payerName,
                         CONCAT(creator.FirstName, ' ', creator.Surname) as createdByName,
                         CONCAT(deleter.FirstName, ' ', deleter.Surname) as deletedByName
                  FROM tija_expense e 
                  LEFT JOIN people emp ON e.employeeID = emp.ID
                  LEFT JOIN user_details ud ON emp.ID = ud.ID
                  LEFT JOIN tija_expense_types et ON e.expenseTypeID = et.expenseTypeID
                  LEFT JOIN tija_expense_categories ec ON e.expenseCategoryID = ec.expenseCategoryID
                  LEFT JOIN tija_expense_status es ON e.expenseStatusID = es.expenseStatusID
                  LEFT JOIN tija_projects p ON e.projectID = p.projectID
                  LEFT JOIN tija_clients c ON e.clientID = c.clientID
                  LEFT JOIN tija_sales_cases sc ON e.salesCaseID = sc.salesCaseID
                  LEFT JOIN people approver ON e.approvedBy = approver.ID
                  LEFT JOIN people payer ON e.paidBy = payer.ID
                  LEFT JOIN people creator ON e.createdBy = creator.ID
                  LEFT JOIN people deleter ON e.deletedBy = deleter.ID
                  WHERE e.isDeleted = 'N'";
        
        $params = array();
        
        // Build WHERE conditions
        if(isset($whereArr['expenseID'])) {
            $query .= " AND e.expenseID = ?";
            $params[] = array($whereArr['expenseID'], 'i');
        }
        
        if(isset($whereArr['expenseNumber'])) {
            $query .= " AND e.expenseNumber = ?";
            $params[] = array($whereArr['expenseNumber'], 's');
        }
        
        if(isset($whereArr['employeeID'])) {
            $query .= " AND e.employeeID = ?";
            $params[] = array($whereArr['employeeID'], 'i');
        }
        
        if(isset($whereArr['expenseTypeID'])) {
            $query .= " AND e.expenseTypeID = ?";
            $params[] = array($whereArr['expenseTypeID'], 'i');
        }
        
        if(isset($whereArr['expenseCategoryID'])) {
            $query .= " AND e.expenseCategoryID = ?";
            $params[] = array($whereArr['expenseCategoryID'], 'i');
        }
        
        if(isset($whereArr['expenseStatusID'])) {
            $query .= " AND e.expenseStatusID = ?";
            $params[] = array($whereArr['expenseStatusID'], 'i');
        }
        
        if(isset($whereArr['projectID'])) {
            $query .= " AND e.projectID = ?";
            $params[] = array($whereArr['projectID'], 'i');
        }
        
        if(isset($whereArr['clientID'])) {
            $query .= " AND e.clientID = ?";
            $params[] = array($whereArr['clientID'], 'i');
        }
        
        if(isset($whereArr['salesCaseID'])) {
            $query .= " AND e.salesCaseID = ?";
            $params[] = array($whereArr['salesCaseID'], 'i');
        }
        
        if(isset($whereArr['orgDataID'])) {
            $query .= " AND e.orgDataID = ?";
            $params[] = array($whereArr['orgDataID'], 'i');
        }
        
        if(isset($whereArr['entityID'])) {
            $query .= " AND e.entityID = ?";
            $params[] = array($whereArr['entityID'], 'i');
        }
        
        if(isset($whereArr['dateFrom'])) {
            $query .= " AND e.expenseDate >= ?";
            $params[] = array($whereArr['dateFrom'], 's');
        }
        
        if(isset($whereArr['dateTo'])) {
            $query .= " AND e.expenseDate <= ?";
            $params[] = array($whereArr['dateTo'], 's');
        }
        
        if(isset($whereArr['amountFrom'])) {
            $query .= " AND e.amount >= ?";
            $params[] = array($whereArr['amountFrom'], 'd');
        }
        
        if(isset($whereArr['amountTo'])) {
            $query .= " AND e.amount <= ?";
            $params[] = array($whereArr['amountTo'], 'd');
        }
        
        if(isset($whereArr['Suspended'])) {
            $query .= " AND e.Suspended = ?";
            $params[] = array($whereArr['Suspended'], 's');
        }
        
        if(isset($whereArr['isUrgent'])) {
            $query .= " AND e.isUrgent = ?";
            $params[] = array($whereArr['isUrgent'], 's');
        }
        
        if(isset($whereArr['isBillable'])) {
            $query .= " AND e.isBillable = ?";
            $params[] = array($whereArr['isBillable'], 's');
        }
        
        if(isset($whereArr['vendor'])) {
            $query .= " AND e.vendor LIKE ?";
            $params[] = array('%' . $whereArr['vendor'] . '%', 's');
        }
        
        if(isset($whereArr['location'])) {
            $query .= " AND e.location LIKE ?";
            $params[] = array('%' . $whereArr['location'] . '%', 's');
        }
        
        $query .= " ORDER BY e.submissionDate DESC, e.expenseDate DESC";
        
        $rows = $DBConn->retrieve_db_table_rows_custom($query, $params);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }
    
    /**
     * Create a new expense (UNIFIED METHOD)
     * @param array $expenseData - Array of expense data
     * @param object $DBConn - Database connection object
     * @return mixed - Expense ID on success, false on failure
     */
    public static function create_expense($expenseData, $DBConn) {
        // Required fields validation
        $requiredFields = ['employeeID', 'expenseTypeID', 'expenseCategoryID', 'expenseDate', 'description', 'amount', 'orgDataID', 'entityID', 'createdBy'];
        
        foreach ($requiredFields as $field) {
            if (!isset($expenseData[$field]) || empty($expenseData[$field])) {
                return array('success' => false, 'message' => "Required field '$field' is missing");
            }
        }
        
        // Set default values
        $defaults = array(
            'expenseStatusID' => 1, // Draft
            'currency' => 'KES',
            'receiptRequired' => 'Y',
            'receiptAttached' => 'N',
            'approvalRequired' => 'Y',
            'approvalLevel' => 1,
            'reimbursementRate' => 100.00,
            'reimbursementMethod' => 'BANK_TRANSFER',
            'paymentMethod' => 'BANK_TRANSFER',
            'isRecurring' => 'N',
            'isBillable' => 'N',
            'isTaxDeductible' => 'Y',
            'requiresJustification' => 'N',
            'isUrgent' => 'N',
            'taxAmount' => 0.00,
            'taxRate' => 0.00,
            'exchangeRate' => 1.000000,
            'Suspended' => 'N',
            'isDeleted' => 'N'
        );
        
        // Merge with defaults
        $expenseData = array_merge($defaults, $expenseData);
        
        // Generate expense number if not provided
        if (empty($expenseData['expenseNumber'])) {
            $expenseData['expenseNumber'] = self::generate_expense_number($expenseData['expenseDate'], $DBConn);
        }
        
        // Generate expense code if not provided
        if (empty($expenseData['expenseCode'])) {
            $expenseData['expenseCode'] = 'EXP-' . date('Ymd') . '-' . substr(md5(uniqid()), 0, 6);
        }
        
        // Set short description if not provided
        if (empty($expenseData['shortDescription'])) {
            $expenseData['shortDescription'] = substr($expenseData['description'], 0, 255);
        }
        
        // Set base amount and net amount
        $expenseData['baseAmount'] = $expenseData['amount'];
        $expenseData['netAmount'] = $expenseData['amount'];
        
        // Insert the expense
        $result = $DBConn->insert_db_table_row('tija_expense', $expenseData);
        
        if ($result) {
            return array('success' => true, 'expenseID' => $result, 'expenseNumber' => $expenseData['expenseNumber']);
        } else {
            return array('success' => false, 'message' => 'Failed to create expense');
        }
    }
    
    /**
     * Update an existing expense (UNIFIED METHOD)
     * @param int $expenseID - Expense ID to update
     * @param array $expenseData - Array of expense data to update
     * @param int $updatedBy - ID of user making the update
     * @param object $DBConn - Database connection object
     * @return bool - Success status
     */
    public static function update_expense($expenseID, $expenseData, $updatedBy, $DBConn) {
        // Add update tracking
        $expenseData['lastUpdatedBy'] = $updatedBy;
        $expenseData['lastUpdated'] = date('Y-m-d H:i:s');
        
        // Update the expense
        $whereClause = "expenseID = " . intval($expenseID);
        $result = $DBConn->update_db_table_row('tija_expense', $expenseData, $whereClause);
        
        return $result;
    }
    
    /**
     * Delete an expense (UNIFIED METHOD - Soft Delete)
     * @param int $expenseID - Expense ID to delete
     * @param int $deletedBy - ID of user deleting the expense
     * @param object $DBConn - Database connection object
     * @return bool - Success status
     */
    public static function delete_expense($expenseID, $deletedBy, $DBConn) {
        $expenseData = array(
            'isDeleted' => 'Y',
            'deletedBy' => $deletedBy,
            'deletedDate' => date('Y-m-d H:i:s')
        );
        
        return self::update_expense($expenseID, $expenseData, $deletedBy, $DBConn);
    }
    
    /**
     * Search expenses by text (UNIFIED METHOD)
     * @param string $searchText - Text to search for
     * @param array $whereArr - Additional WHERE conditions
     * @param object $DBConn - Database connection object
     * @return array - Search results
     */
    public static function search_expenses($searchText, $whereArr = array(), $DBConn) {
        $query = "SELECT e.*,
                         CONCAT(emp.FirstName, ' ', emp.Surname) as employeeName,
                         ud.payrollNo as employeeCode,
                         et.typeName as expenseTypeName,
                         et.typeCode as expenseTypeCode,
                         ec.categoryName as expenseCategoryName,
                         ec.categoryCode as expenseCategoryCode,
                         es.statusName as expenseStatusName,
                         es.statusDescription as expenseStatusDescription,
                         es.statusColor as expenseStatusColor
                  FROM tija_expense e
                  LEFT JOIN people emp ON e.employeeID = emp.ID
                  LEFT JOIN user_details ud ON emp.ID = ud.ID
                  LEFT JOIN tija_expense_types et ON e.expenseTypeID = et.expenseTypeID
                  LEFT JOIN tija_expense_categories ec ON e.expenseCategoryID = ec.expenseCategoryID
                  LEFT JOIN tija_expense_status es ON e.expenseStatusID = es.expenseStatusID
                  WHERE (e.description LIKE ? OR e.shortDescription LIKE ? OR e.vendor LIKE ? OR e.location LIKE ?)
                  AND e.isDeleted = 'N'";
        
        $params = array(
            array('%' . $searchText . '%', 's'),
            array('%' . $searchText . '%', 's'),
            array('%' . $searchText . '%', 's'),
            array('%' . $searchText . '%', 's')
        );
        
        // Add additional WHERE conditions
        if(isset($whereArr['employeeID'])) {
            $query .= " AND e.employeeID = ?";
            $params[] = array($whereArr['employeeID'], 'i');
        }
        
        if(isset($whereArr['orgDataID'])) {
            $query .= " AND e.orgDataID = ?";
            $params[] = array($whereArr['orgDataID'], 'i');
        }
        
        if(isset($whereArr['entityID'])) {
            $query .= " AND e.entityID = ?";
            $params[] = array($whereArr['entityID'], 'i');
        }
        
        if(isset($whereArr['dateFrom'])) {
            $query .= " AND e.expenseDate >= ?";
            $params[] = array($whereArr['dateFrom'], 's');
        }
        
        if(isset($whereArr['dateTo'])) {
            $query .= " AND e.expenseDate <= ?";
            $params[] = array($whereArr['dateTo'], 's');
        }
        
        $query .= " ORDER BY e.submissionDate DESC";
        
        $rows = $DBConn->retrieve_db_table_rows_custom($query, $params);
        return (is_array($rows) && count($rows) > 0) ? $rows : false;
    }
    
    /**
     * Generate expense number (UNIFIED METHOD)
     * @param string $expenseDate - Expense date
     * @param object $DBConn - Database connection object
     * @return string - Generated expense number
     */
    public static function generate_expense_number($expenseDate, $DBConn) {
        $year = date('Y', strtotime($expenseDate));
        $month = date('m', strtotime($expenseDate));
        
        $query = "SELECT COUNT(*) as count FROM tija_expense WHERE YEAR(expenseDate) = ? AND MONTH(expenseDate) = ?";
        $params = array(
            array($year, 'i'),
            array($month, 'i')
        );
        
        $result = $DBConn->retrieve_single_row_custom($query, $params);
        $count = $result ? $result->count + 1 : 1;
        
        return 'EXP-' . $year . $month . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
    
    // ========================================
    // LEGACY COMPATIBILITY METHODS
    // ========================================
    
    /**
     * Legacy method - redirects to get_expenses
     */
    public static function expenses_full($whereArr, $single, $DBConn) {
        return self::get_expenses($whereArr, $single, $DBConn);
    }
    
    /**
     * Legacy method - redirects to get_expenses
     */
    public static function get_expenses_enhanced($whereArr = array(), $single = false, $DBConn) {
        return self::get_expenses($whereArr, $single, $DBConn);
    }
    
    /**
     * Legacy method - redirects to create_expense
     */
    public static function create_expense_enhanced($expenseData, $DBConn) {
        return self::create_expense($expenseData, $DBConn);
    }
    
    /**
     * Legacy method - redirects to update_expense
     */
    public static function update_expense_enhanced($expenseID, $expenseData, $updatedBy, $DBConn) {
        return self::update_expense($expenseID, $expenseData, $updatedBy, $DBConn);
    }
    
    /**
     * Legacy method - redirects to delete_expense
     */
    public static function delete_expense_enhanced($expenseID, $deletedBy, $DBConn) {
        return self::delete_expense($expenseID, $deletedBy, $DBConn);
    }
    
    /**
     * Legacy method - redirects to search_expenses
     */
    public static function search_expenses_enhanced($searchText, $whereArr = array(), $DBConn) {
        return self::search_expenses($searchText, $whereArr, $DBConn);
    }
    
    /**
     * Legacy method - redirects to generate_expense_number
     */
    public static function generate_expense_number_enhanced($expenseDate, $DBConn) {
        return self::generate_expense_number($expenseDate, $DBConn);
    }
    
    // ========================================
    // SUPPORTING METHODS (UNIFIED)
    // ========================================
    
    /**
     * Get expense categories
     */
    public static function expense_categories($whereArr, $single, $DBConn) {
        $query = "SELECT * FROM tija_expense_categories WHERE 1=1";
        $params = array();
        
        if(isset($whereArr['expenseCategoryID'])) {
            $query .= " AND expenseCategoryID = ?";
            $params[] = array($whereArr['expenseCategoryID'], 'i');
        }
        
        if(isset($whereArr['orgDataID'])) {
            $query .= " AND orgDataID = ?";
            $params[] = array($whereArr['orgDataID'], 'i');
        }
        
        if(isset($whereArr['entityID'])) {
            $query .= " AND entityID = ?";
            $params[] = array($whereArr['entityID'], 'i');
        }
        
        if(isset($whereArr['isActive'])) {
            $query .= " AND isActive = ?";
            $params[] = array($whereArr['isActive'], 's');
        }
        
        $query .= " ORDER BY categoryName ASC";
        
        $rows = $DBConn->retrieve_db_table_rows_custom($query, $params);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }
    
    /**
     * Get expense status
     */
    public static function expense_status($whereArr, $single, $DBConn) {
        $query = "SELECT * FROM tija_expense_status WHERE 1=1";
        $params = array();
        
        if(isset($whereArr['expenseStatusID'])) {
            $query .= " AND expenseStatusID = ?";
            $params[] = array($whereArr['expenseStatusID'], 'i');
        }
        
        if(isset($whereArr['orgDataID'])) {
            $query .= " AND orgDataID = ?";
            $params[] = array($whereArr['orgDataID'], 'i');
        }
        
        if(isset($whereArr['entityID'])) {
            $query .= " AND entityID = ?";
            $params[] = array($whereArr['entityID'], 'i');
        }
        
        if(isset($whereArr['isActive'])) {
            $query .= " AND isActive = ?";
            $params[] = array($whereArr['isActive'], 's');
        }
        
        $query .= " ORDER BY statusName ASC";
        
        $rows = $DBConn->retrieve_db_table_rows_custom($query, $params);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }
    
    /**
     * Get expense types
     */
    public static function expense_types($whereArr, $single, $DBConn) {
        $query = "SELECT * FROM tija_expense_types WHERE 1=1";
        $params = array();

        if(isset($whereArr['expenseTypeID'])) {
            $query .= " AND expenseTypeID = ?";
            $params[] = array($whereArr['expenseTypeID'], 'i');
        }

        if(isset($whereArr['orgDataID'])) {
            $query .= " AND orgDataID = ?";
            $params[] = array($whereArr['orgDataID'], 'i');
        }

        if(isset($whereArr['entityID'])) {
            $query .= " AND entityID = ?";
            $params[] = array($whereArr['entityID'], 'i');
        }

        if(isset($whereArr['isActive'])) {
            $query .= " AND isActive = ?";
            $params[] = array($whereArr['isActive'], 's');
        }

        if(isset($whereArr['isReimbursable'])) {
            $query .= " AND isReimbursable = ?";
            $params[] = array($whereArr['isReimbursable'], 's');
        }

        if(isset($whereArr['isPettyCash'])) {
            $query .= " AND isPettyCash = ?";
            $params[] = array($whereArr['isPettyCash'], 's');
        }

        if(isset($whereArr['requiresReceipt'])) {
            $query .= " AND requiresReceipt = ?";
            $params[] = array($whereArr['requiresReceipt'], 's');
        }

        if(isset($whereArr['approvalLevel'])) {
            $query .= " AND approvalLevel = ?";
            $params[] = array($whereArr['approvalLevel'], 'i');
        }

        if(isset($whereArr['hasBudgetLimit'])) {
            $query .= " AND hasBudgetLimit = ?";
            $params[] = array($whereArr['hasBudgetLimit'], 's');
        }

        if(isset($whereArr['budgetPeriod'])) {
            $query .= " AND budgetPeriod = ?";
            $params[] = array($whereArr['budgetPeriod'], 's');
        }

        if(isset($whereArr['parentTypeID'])) {
            $query .= " AND parentTypeID = ?";
            $params[] = array($whereArr['parentTypeID'], 'i');
        }

        if(isset($whereArr['typeLevel'])) {
            $query .= " AND typeLevel = ?";
            $params[] = array($whereArr['typeLevel'], 'i');
        }

        if(isset($whereArr['isTaxable'])) {
            $query .= " AND isTaxable = ?";
            $params[] = array($whereArr['isTaxable'], 's');
        }

        if(isset($whereArr['defaultCurrency'])) {
            $query .= " AND defaultCurrency = ?";
            $params[] = array($whereArr['defaultCurrency'], 's');
        }

        if(isset($whereArr['requiresJustification'])) {
            $query .= " AND requiresJustification = ?";
            $params[] = array($whereArr['requiresJustification'], 's');
        }

        if(isset($whereArr['requiresProjectLink'])) {
            $query .= " AND requiresProjectLink = ?";
            $params[] = array($whereArr['requiresProjectLink'], 's');
        }

        if(isset($whereArr['requiresClientLink'])) {
            $query .= " AND requiresClientLink = ?";
            $params[] = array($whereArr['requiresClientLink'], 's');
        }

        if(isset($whereArr['requiresSalesCaseLink'])) {
            $query .= " AND requiresSalesCaseLink = ?";
            $params[] = array($whereArr['requiresSalesCaseLink'], 's');
        }

        // Order by typeName (sortOrder may not exist in legacy tables)
        // TODO: Update to use sortOrder when available: ORDER BY sortOrder ASC, typeName ASC
        $query .= " ORDER BY typeName ASC";

        $rows = $DBConn->retrieve_db_table_rows_custom($query, $params);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }
    
    /**
     * Get expense summary by employee
     */
    public static function employee_expense_summary($whereArr, $DBConn) {
        $query = "SELECT 
                    e.employeeID,
                    CONCAT(emp.FirstName, ' ', emp.Surname) as employeeName,
                    ud.payrollNo as employeeCode,
                    COUNT(*) as total_expenses,
                    SUM(e.amount) as total_amount,
                    AVG(e.amount) as average_amount,
                    SUM(CASE WHEN e.expenseStatusID = 4 THEN e.amount ELSE 0 END) as approved_amount,
                    SUM(CASE WHEN e.expenseStatusID = 6 THEN e.amount ELSE 0 END) as paid_amount,
                    COUNT(CASE WHEN e.expenseStatusID = 4 THEN 1 END) as approved_count,
                    COUNT(CASE WHEN e.expenseStatusID = 6 THEN 1 END) as paid_count,
                    SUM(CASE WHEN e.expenseStatusID NOT IN (4, 6) AND DATEDIFF(NOW(), e.expenseDate) > 30 THEN e.amount ELSE 0 END) as overdue_amount,
                    COUNT(CASE WHEN e.expenseStatusID NOT IN (4, 6) AND DATEDIFF(NOW(), e.expenseDate) > 30 THEN 1 END) as overdue_count
                  FROM tija_expense e
                  LEFT JOIN people emp ON e.employeeID = emp.ID
                  LEFT JOIN user_details ud ON emp.ID = ud.ID
                  WHERE e.isDeleted = 'N'";
        
        $params = array();
        
        if(isset($whereArr['orgDataID'])) {
            $query .= " AND e.orgDataID = ?";
            $params[] = array($whereArr['orgDataID'], 'i');
        }
        
        if(isset($whereArr['entityID'])) {
            $query .= " AND e.entityID = ?";
            $params[] = array($whereArr['entityID'], 'i');
        }
        
        if(isset($whereArr['dateFrom'])) {
            $query .= " AND e.expenseDate >= ?";
            $params[] = array($whereArr['dateFrom'], 's');
        }
        
        if(isset($whereArr['dateTo'])) {
            $query .= " AND e.expenseDate <= ?";
            $params[] = array($whereArr['dateTo'], 's');
        }
        
        $query .= " GROUP BY e.employeeID, emp.FirstName, emp.Surname ORDER BY total_amount DESC";
        
        $rows = $DBConn->retrieve_db_table_rows_custom($query, $params);
        return (is_array($rows) && count($rows) > 0) ? $rows : false;
    }
    
    /**
     * Get expense summary by category
     */
    public static function category_expense_summary($whereArr, $DBConn) {
        $query = "SELECT 
                    e.expenseCategoryID,
                    ec.categoryName,
                    ec.categoryCode,
                    COUNT(*) as total_expenses,
                    SUM(e.amount) as total_amount,
                    AVG(e.amount) as average_amount,
                    SUM(CASE WHEN e.expenseStatusID = 4 THEN e.amount ELSE 0 END) as approved_amount,
                    SUM(CASE WHEN e.expenseStatusID = 6 THEN e.amount ELSE 0 END) as paid_amount,
                    COUNT(CASE WHEN e.expenseStatusID = 4 THEN 1 END) as approved_count,
                    COUNT(CASE WHEN e.expenseStatusID = 6 THEN 1 END) as paid_count,
                    SUM(CASE WHEN e.expenseStatusID NOT IN (4, 6) AND DATEDIFF(NOW(), e.expenseDate) > 30 THEN e.amount ELSE 0 END) as overdue_amount,
                    COUNT(CASE WHEN e.expenseStatusID NOT IN (4, 6) AND DATEDIFF(NOW(), e.expenseDate) > 30 THEN 1 END) as overdue_count
                  FROM tija_expense e
                  LEFT JOIN tija_expense_categories ec ON e.expenseCategoryID = ec.expenseCategoryID
                  WHERE e.isDeleted = 'N'";
        
        $params = array();
        
        if(isset($whereArr['orgDataID'])) {
            $query .= " AND e.orgDataID = ?";
            $params[] = array($whereArr['orgDataID'], 'i');
        }
        
        if(isset($whereArr['entityID'])) {
            $query .= " AND e.entityID = ?";
            $params[] = array($whereArr['entityID'], 'i');
        }
        
        if(isset($whereArr['dateFrom'])) {
            $query .= " AND e.expenseDate >= ?";
            $params[] = array($whereArr['dateFrom'], 's');
        }
        
        if(isset($whereArr['dateTo'])) {
            $query .= " AND e.expenseDate <= ?";
            $params[] = array($whereArr['dateTo'], 's');
        }
        
        $query .= " GROUP BY e.expenseCategoryID, ec.categoryName, ec.categoryCode ORDER BY total_amount DESC";
        
        $rows = $DBConn->retrieve_db_table_rows_custom($query, $params);
        return (is_array($rows) && count($rows) > 0) ? $rows : false;
    }
    
    /**
     * Get overdue expenses
     */
    public static function overdue_expenses($whereArr, $DBConn) {
        $query = "SELECT e.*,
                         CONCAT(emp.FirstName, ' ', emp.Surname) as employeeName,
                         et.typeName as expenseTypeName,
                         ec.categoryName as expenseCategoryName,
                         es.statusName as expenseStatusName,
                         DATEDIFF(NOW(), e.expenseDate) as days_overdue
                  FROM tija_expense e
                  LEFT JOIN people emp ON e.employeeID = emp.ID
                  LEFT JOIN tija_expense_types et ON e.expenseTypeID = et.expenseTypeID
                  LEFT JOIN tija_expense_categories ec ON e.expenseCategoryID = ec.expenseCategoryID
                  LEFT JOIN tija_expense_status es ON e.expenseStatusID = es.expenseStatusID
                  WHERE e.isDeleted = 'N'
                  AND e.expenseStatusID NOT IN (4, 6) -- Not approved or paid
                  AND DATEDIFF(NOW(), e.expenseDate) > 30"; // Over 30 days old
        
        $params = array();
        
        if(isset($whereArr['orgDataID'])) {
            $query .= " AND e.orgDataID = ?";
            $params[] = array($whereArr['orgDataID'], 'i');
        }
        
        if(isset($whereArr['entityID'])) {
            $query .= " AND e.entityID = ?";
            $params[] = array($whereArr['entityID'], 'i');
        }
        
        $query .= " ORDER BY days_overdue DESC";
        
        $rows = $DBConn->retrieve_db_table_rows_custom($query, $params);
        return (is_array($rows) && count($rows) > 0) ? $rows : false;
    }
    
    /**
     * Get monthly expense trends
     */
    public static function monthly_expense_trends($whereArr, $DBConn) {
        $query = "SELECT 
                    DATE_FORMAT(e.expenseDate, '%Y-%m') as month,
                    DATE_FORMAT(e.expenseDate, '%M %Y') as month_name,
                    COUNT(*) as total_expenses,
                    SUM(e.amount) as total_amount,
                    SUM(CASE WHEN e.expenseStatusID = 4 THEN e.amount ELSE 0 END) as approved_amount,
                    SUM(CASE WHEN e.expenseStatusID = 6 THEN e.amount ELSE 0 END) as paid_amount,
                    COUNT(CASE WHEN e.expenseStatusID = 4 THEN 1 END) as approved_count,
                    COUNT(CASE WHEN e.expenseStatusID = 6 THEN 1 END) as paid_count
                  FROM tija_expense e
                  WHERE e.isDeleted = 'N'";
        
        $params = array();
        
        if(isset($whereArr['orgDataID'])) {
            $query .= " AND e.orgDataID = ?";
            $params[] = array($whereArr['orgDataID'], 'i');
        }
        
        if(isset($whereArr['entityID'])) {
            $query .= " AND e.entityID = ?";
            $params[] = array($whereArr['entityID'], 'i');
        }
        
        if(isset($whereArr['dateFrom'])) {
            $query .= " AND e.expenseDate >= ?";
            $params[] = array($whereArr['dateFrom'], 's');
        }
        
        if(isset($whereArr['dateTo'])) {
            $query .= " AND e.expenseDate <= ?";
            $params[] = array($whereArr['dateTo'], 's');
        }
        
        $query .= " GROUP BY DATE_FORMAT(e.expenseDate, '%Y-%m'), DATE_FORMAT(e.expenseDate, '%M %Y') ORDER BY month ASC";
        
        $rows = $DBConn->retrieve_db_table_rows_custom($query, $params);
        return (is_array($rows) && count($rows) > 0) ? $rows : false;
    }
}
