<?php
/**
 * Invoice Class - Enhanced Version
 *
 * This class provides comprehensive functionality for invoice management
 * including CRUD operations, status management, and reporting functions.
 *
 * @author TIJA PMS System
 * @version 2.0
 * @since 2024
 */

class Invoice {

    /**
     * Retrieve invoices with optional filtering
     *
     * @param array $whereArr - Filter conditions
     * @param boolean $single - Return single record or array
     * @param object $DBConn - Database connection
     * @return mixed - Single invoice object or array of invoices
     */
    public static function invoices($whereArr, $single, $DBConn) {
        $cols = array(
            'invoiceID',
            'DateAdded',
            'invoiceNumber',
            'clientID',
            'salesCaseID',
            'projectID',
            'invoiceDate',
            'dueDate',
            'invoiceAmount',
            'taxAmount',
            'totalAmount',
            'currency',
            'invoiceStatusID',
            'orgDataID',
            'entityID',
            'LastUpdate',
            'LastUpdatedByID',
            'Lapsed',
            'Suspended'
        );
        $rows = $DBConn->retrieve_db_table_rows('tija_invoices', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get invoices with client and status information
     *
     * @param array $whereArr - Filter conditions
     * @param boolean $single - Return single record or array
     * @param object $DBConn - Database connection
     * @return mixed - Enhanced invoice data with client and status info
     */
    public static function invoices_full($whereArr, $single, $DBConn) {
        $query = "
            SELECT
                i.*,
                c.clientName,
                c.clientCode,
                s.statusName as invoiceStatusName,
                s.statusColor as invoiceStatusColor,
                sc.salesCaseName,
                p.projectName,
                CONCAT(u.FirstName, ' ', u.Surname) as lastUpdatedByName
            FROM tija_invoices i
            LEFT JOIN tija_clients c ON i.clientID = c.clientID
            LEFT JOIN tija_invoice_status s ON i.invoiceStatusID = s.statusID
            LEFT JOIN tija_sales_cases sc ON i.salesCaseID = sc.salesCaseID
            LEFT JOIN tija_projects p ON i.projectID = p.projectID
            LEFT JOIN people u ON i.LastUpdatedByID = u.ID
            WHERE 1=1
        ";

        $params = array();

        // Add where conditions
        if(isset($whereArr['orgDataID'])) {
            $query .= " AND i.orgDataID = ?";
            $params[] = array($whereArr['orgDataID'], 'i');
        }

        if(isset($whereArr['entityID'])) {
            $query .= " AND i.entityID = ?";
            $params[] = array($whereArr['entityID'], 'i');
        }

        if(isset($whereArr['clientID'])) {
            $query .= " AND i.clientID = ?";
            $params[] = array($whereArr['clientID'], 'i');
        }

        if(isset($whereArr['invoiceStatusID'])) {
            $query .= " AND i.invoiceStatusID = ?";
            $params[] = array($whereArr['invoiceStatusID'], 'i');
        }

        if(isset($whereArr['invoiceDate'])) {
            $query .= " AND i.invoiceDate >= ?";
            $params[] = array($whereArr['invoiceDate'], 'i');
        }

        if(isset($whereArr['dueDate'])) {
            $query .= " AND i.dueDate <= ?";
            $params[] = array($whereArr['dueDate'], 'i');
        }

        if(isset($whereArr['Suspended'])) {
            $query .= " AND i.Suspended = ?";
            $params[] = array($whereArr['Suspended'], 'i');
        }

        $query .= " ORDER BY i.invoiceDate DESC, i.invoiceNumber DESC";

        $rows = $DBConn->retrieve_db_table_rows_custom($query, $params);

        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get invoice statuses
     *
     * @param array $whereArr - Filter conditions
     * @param boolean $single - Return single record or array
     * @param object $DBConn - Database connection
     * @return mixed - Status data
     */
    public static function invoice_statuses($whereArr, $single, $DBConn) {
        $cols = array(
            'statusID',
            'statusName',
            'statusDescription',
            'statusColor',
            'isActive',
            'sortOrder',
            'DateAdded',
            'LastUpdate'
        );
        $rows = $DBConn->retrieve_db_table_rows('tija_invoice_status', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get overdue invoices
     *
     * @param array $whereArr - Filter conditions
     * @param object $DBConn - Database connection
     * @return array - Overdue invoices
     */
    public static function overdue_invoices($whereArr, $DBConn) {
        $query = "
            SELECT
                i.*,
                c.clientName,
                c.clientCode,
                s.statusName as invoiceStatusName,
                s.statusColor as invoiceStatusColor,
                DATEDIFF(CURDATE(), i.dueDate) as daysOverdue
            FROM tija_invoices i
            LEFT JOIN tija_clients c ON i.clientID = c.clientID
            LEFT JOIN tija_invoice_status s ON i.invoiceStatusID = s.statusID
            WHERE i.dueDate < CURDATE()
            AND i.invoiceStatusID NOT IN (3, 6, 8) -- Exclude paid, cancelled, refunded
            AND i.Suspended = 'N'
        ";

        $params = array();

        if(isset($whereArr['orgDataID'])) {
            $query .= " AND i.orgDataID = ?";
            $params[] = array($whereArr['orgDataID'], 'i');
        }

        if(isset($whereArr['entityID'])) {
            $query .= " AND i.entityID = ?";
            $params[] = array($whereArr['entityID'], 'i');
        }

        $query .= " ORDER BY i.dueDate ASC";

        $rows = $DBConn->retrieve_db_table_rows_custom($query, $params);
        return is_array($rows) ? $rows : array();
    }

    /**
     * Get monthly billing summary
     *
     * @param array $whereArr - Filter conditions
     * @param object $DBConn - Database connection
     * @return array - Monthly billing data
     */
    public static function monthly_billing_summary($whereArr, $DBConn) {
        $query = "
            SELECT
                DATE_FORMAT(invoiceDate, '%Y-%m') as billing_month,
                COUNT(*) as invoice_count,
                SUM(invoiceAmount) as total_invoice_amount,
                SUM(taxAmount) as total_tax_amount,
                SUM(totalAmount) as total_billed,
                AVG(totalAmount) as average_invoice_amount
            FROM tija_invoices
            WHERE Suspended = 'N'
        ";

        $params = array();

        if(isset($whereArr['orgDataID'])) {
            $query .= " AND orgDataID = ?";
            $params[] = array($whereArr['orgDataID'], 'i');
        }

        if(isset($whereArr['entityID'])) {
            $query .= " AND entityID = ?";
            $params[] = array($whereArr['entityID'], 'i');
        }

        if(isset($whereArr['startDate'])) {
            $query .= " AND invoiceDate >= ?";
            $params[] = array($whereArr['startDate'], 'i');
        }

        if(isset($whereArr['endDate'])) {
            $query .= " AND invoiceDate <= ?";
            $params[] = array($whereArr['endDate'], 'i');
        }

        $query .= " GROUP BY DATE_FORMAT(invoiceDate, '%Y-%m') ORDER BY billing_month DESC";

        $rows = $DBConn->retrieve_db_table_rows_custom($query, $params);
        return is_array($rows) ? $rows : array();
    }

    /**
     * Get client billing summary
     *
     * @param array $whereArr - Filter conditions
     * @param object $DBConn - Database connection
     * @return array - Client billing data
     */
    public static function client_billing_summary($whereArr, $DBConn) {
        $query = "
            SELECT
                c.clientID,
                c.clientName,
                c.clientCode,
                COUNT(i.invoiceID) as total_invoices,
                SUM(i.totalAmount) as total_billed,
                AVG(i.totalAmount) as average_invoice_amount,
                MAX(i.invoiceDate) as last_invoice_date,
                MIN(i.invoiceDate) as first_invoice_date
            FROM tija_clients c
            LEFT JOIN tija_invoices i ON c.clientID = i.clientID AND i.Suspended = 'N'
            WHERE 1=1
        ";

        $params = array();

        if(isset($whereArr['orgDataID'])) {
            $query .= " AND c.orgDataID = ?";
            $params[] = array($whereArr['orgDataID'], 'i');
        }

        if(isset($whereArr['entityID'])) {
            $query .= " AND c.entityID = ?";
            $params[] = array($whereArr['entityID'], 'i');
        }

        $query .= " GROUP BY c.clientID, c.clientName, c.clientCode ORDER BY total_billed DESC";

        $rows = $DBConn->retrieve_db_table_rows_custom($query, $params);
        return is_array($rows) ? $rows : array();
    }

    /**
     * Get current month billing
     *
     * @param array $whereArr - Filter conditions
     * @param object $DBConn - Database connection
     * @return array - Current month billing data
     */
    public static function current_month_billing($whereArr, $DBConn) {
        $query = "
            SELECT
                SUM(totalAmount) as total_billed,
                COUNT(*) as invoice_count,
                SUM(invoiceAmount) as total_invoice_amount,
                SUM(taxAmount) as total_tax_amount
            FROM tija_invoices
            WHERE YEAR(invoiceDate) = YEAR(CURDATE())
            AND MONTH(invoiceDate) = MONTH(CURDATE())
            AND Suspended = 'N'
        ";

        $params = array();

        if(isset($whereArr['orgDataID'])) {
            $query .= " AND orgDataID = ?";
            $params[] = array($whereArr['orgDataID'], 'i');
        }

        if(isset($whereArr['entityID'])) {
            $query .= " AND entityID = ?";
            $params[] = array($whereArr['entityID'], 'i');
        }

        $rows = $DBConn->retrieve_db_table_rows_custom($query, $params);
        return is_array($rows) && count($rows) > 0 ? $rows[0] : false;
    }

    /**
     * Get invoice statistics
     *
     * @param array $whereArr - Filter conditions
     * @param object $DBConn - Database connection
     * @return object - Invoice statistics
     */
    public static function invoice_statistics($whereArr, $DBConn) {
        $query = "
            SELECT
                COUNT(*) as total_invoices,
                SUM(CASE WHEN invoiceStatusID = 1 THEN 1 ELSE 0 END) as draft_count,
                SUM(CASE WHEN invoiceStatusID = 2 THEN 1 ELSE 0 END) as sent_count,
                SUM(CASE WHEN invoiceStatusID = 3 THEN 1 ELSE 0 END) as paid_count,
                SUM(CASE WHEN invoiceStatusID = 5 THEN 1 ELSE 0 END) as overdue_count,
                SUM(totalAmount) as total_value,
                SUM(CASE WHEN invoiceStatusID = 3 THEN totalAmount ELSE 0 END) as paid_value,
                AVG(totalAmount) as average_invoice_value
            FROM tija_invoices
            WHERE Suspended = 'N'
        ";

        $params = array();

        if(isset($whereArr['orgDataID'])) {
            $query .= " AND orgDataID = ?";
            $params[] = array($whereArr['orgDataID'], 'i');
        }

        if(isset($whereArr['entityID'])) {
            $query .= " AND entityID = ?";
            $params[] = array($whereArr['entityID'], 'i');
        }

        if(isset($whereArr['startDate'])) {
            $query .= " AND invoiceDate >= ?";
            $params[] = array($whereArr['startDate'], 'i');
        }

        if(isset($whereArr['endDate'])) {
            $query .= " AND invoiceDate <= ?";
            $params[] = array($whereArr['endDate'], 'i');
        }

        $rows = $DBConn->retrieve_db_table_rows_custom($query, $params);
        return is_array($rows) && count($rows) > 0 ? $rows[0] : false;
    }

    /**
     * Generate next invoice number
     *
     * @param array $whereArr - Filter conditions
     * @param object $DBConn - Database connection
     * @return string - Next invoice number
     */
    public static function generate_invoice_number($whereArr, $DBConn) {
        $prefix = isset($whereArr['prefix']) ? $whereArr['prefix'] : 'INV';
        $year = date('Y');

        $query = "
            SELECT MAX(CAST(SUBSTRING(invoiceNumber, LOCATE('-', invoiceNumber) + 1) AS UNSIGNED)) as max_number
            FROM tija_invoices
            WHERE invoiceNumber LIKE ?
        ";

        $params = array($prefix . '-' . $year . '-%');

        $rows = $DBConn->retrieve_db_table_rows_custom($query, $params);
        $maxNumber = is_array($rows) && count($rows) > 0 ? $rows[0]->max_number : 0;

        $nextNumber = $maxNumber + 1;
        return $prefix . '-' . $year . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get invoice items for an invoice
     *
     * @param array $whereArr - Filter conditions (must include invoiceID)
     * @param boolean $single - Return single record or array
     * @param object $DBConn - Database connection
     * @return mixed - Invoice items
     */
    public static function invoice_items($whereArr, $single, $DBConn) {
        $cols = array(
            'invoiceItemID',
            'invoiceID',
            'itemType',
            'itemReferenceID',
            'itemCode',
            'itemDescription',
            'quantity',
            'unitPrice',
            'discountPercent',
            'discountAmount',
            'taxPercent',
            'taxAmount',
            'lineTotal',
            'sortOrder',
            'metadata',
            'DateAdded',
            'LastUpdate',
            'Suspended'
        );
        $rows = $DBConn->retrieve_db_table_rows('tija_invoice_items', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get invoice templates
     *
     * @param array $whereArr - Filter conditions
     * @param boolean $single - Return single record or array
     * @param object $DBConn - Database connection
     * @return mixed - Invoice templates
     */
    public static function invoice_templates($whereArr, $single, $DBConn) {
        $cols = array(
            'templateID',
            'templateName',
            'templateCode',
            'templateDescription',
            'templateType',
            'headerHTML',
            'footerHTML',
            'bodyHTML',
            'cssStyles',
            'logoURL',
            'companyName',
            'companyAddress',
            'companyPhone',
            'companyEmail',
            'companyWebsite',
            'companyTaxID',
            'defaultTerms',
            'defaultNotes',
            'currency',
            'taxEnabled',
            'defaultTaxPercent',
            'isDefault',
            'isActive',
            'orgDataID',
            'entityID',
            'createdBy',
            'DateAdded',
            'LastUpdate',
            'LastUpdatedByID',
            'Suspended'
        );
        $rows = $DBConn->retrieve_db_table_rows('tija_invoice_templates', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get invoice payments
     *
     * @param array $whereArr - Filter conditions
     * @param boolean $single - Return single record or array
     * @param object $DBConn - Database connection
     * @return mixed - Invoice payments
     */
    public static function invoice_payments($whereArr, $single, $DBConn) {
        $cols = array(
            'paymentID',
            'invoiceID',
            'paymentNumber',
            'paymentDate',
            'paymentAmount',
            'paymentMethod',
            'paymentReference',
            'bankAccountID',
            'currency',
            'exchangeRate',
            'notes',
            'receivedBy',
            'verifiedBy',
            'verificationDate',
            'status',
            'orgDataID',
            'entityID',
            'DateAdded',
            'LastUpdate',
            'LastUpdatedByID',
            'Suspended'
        );
        $rows = $DBConn->retrieve_db_table_rows('tija_invoice_payments', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get invoice with all related data (items, payments, etc.)
     *
     * @param int $invoiceID - Invoice ID
     * @param object $DBConn - Database connection
     * @return object|false - Complete invoice data
     */
    public static function invoice_complete($invoiceID, $DBConn) {
        $invoice = self::invoices_full(array('invoiceID' => $invoiceID), true, $DBConn);
        if (!$invoice) {
            return false;
        }

        // Get invoice items
        $invoice->items = self::invoice_items(array('invoiceID' => $invoiceID, 'Suspended' => 'N'), false, $DBConn);

        // Get payments
        $invoice->payments = self::invoice_payments(array('invoiceID' => $invoiceID, 'Suspended' => 'N'), false, $DBConn);

        // Calculate totals
        $invoice->paidAmount = 0;
        if ($invoice->payments && is_array($invoice->payments)) {
            foreach ($invoice->payments as $payment) {
                if ($payment->status == 'verified') {
                    $invoice->paidAmount += $payment->paymentAmount;
                }
            }
        }
        $invoice->outstandingAmount = $invoice->totalAmount - $invoice->paidAmount;

        // Get template if exists
        if (isset($invoice->templateID) && $invoice->templateID) {
            $invoice->template = self::invoice_templates(array('templateID' => $invoice->templateID), true, $DBConn);
        }

        return $invoice;
    }

    /**
     * Get billable work hours for a project
     *
     * @param int $projectID - Project ID
     * @param string $startDate - Start date (Y-m-d)
     * @param string $endDate - End date (Y-m-d)
     * @param object $DBConn - Database connection
     * @return array - Billable work hours
     */
    public static function get_billable_hours($projectID, $startDate = null, $endDate = null, $DBConn) {
        $where = "WHERE tl.projectID = ? AND tl.Suspended = 'N'";
        $params = array(array($projectID, 'tl'));

        if ($startDate) {
            $where .= " AND tl.taskDate >= ?";
            $params[] = array($startDate, 'tl');
        }

        if ($endDate) {
            $where .= " AND tl.taskDate <= ?";
            $params[] = array($endDate, 'tl');
        }

        // Exclude already invoiced hours
        $where .= " AND tl.timelogID NOT IN (
            SELECT timelogID FROM tija_invoice_work_hours iwh
            INNER JOIN tija_invoice_items ii ON iwh.invoiceItemID = ii.invoiceItemID
            INNER JOIN tija_invoices i ON ii.invoiceID = i.invoiceID
            WHERE i.Suspended = 'N' AND i.invoiceStatusID NOT IN (6, 8)
        )";

        $query = "
            SELECT
                tl.timelogID,
                tl.taskDate,
                tl.employeeID,
                tl.projectID,
                tl.projectTaskID,
                tl.projectPhaseID,
                tl.taskDuration,
                tl.taskNarrative,
                tl.startTime,
                tl.endTime,
                p.projectName,
                pt.projectTaskName,
                pp.projectPhaseName,
                CONCAT(emp.FirstName, ' ', emp.Surname) as employeeName,
                COALESCE(p.billableRateValue, 0) as billingRate,
                CASE
                    WHEN p.billable = 'Y' THEN 1
                    ELSE 0
                END as isBillable
            FROM tija_tasks_time_logs tl
            LEFT JOIN tija_projects p ON tl.projectID = p.projectID
            LEFT JOIN tija_project_tasks pt ON tl.projectTaskID = pt.projectTaskID
            LEFT JOIN tija_project_phases pp ON tl.projectPhaseID = pp.projectPhaseID
            LEFT JOIN people emp ON tl.employeeID = emp.ID
            {$where}
            ORDER BY tl.taskDate ASC, tl.startTime ASC
        ";

        $rows = $DBConn->fetch_all_rows($query, $params);
        return is_array($rows) ? $rows : array();
    }

    /**
     * Get billable expenses for a project
     *
     * @param int $projectID - Project ID
     * @param string $startDate - Start date (Y-m-d)
     * @param string $endDate - End date (Y-m-d)
     * @param object $DBConn - Database connection
     * @return array - Billable expenses
     */
    public static function get_billable_expenses($projectID, $startDate = null, $endDate = null, $DBConn) {
        $where = "WHERE e.projectID = ? AND e.expenseStatus = 'approved' AND e.Suspended = 'N'";
        $params = array(array($projectID, 'e'));

        if ($startDate) {
            $where .= " AND e.expenseDate >= ?";
            $params[] = array($startDate, 'e');
        }

        if ($endDate) {
            $where .= " AND e.expenseDate <= ?";
            $params[] = array($endDate, 'e');
        }

        // Exclude already invoiced expenses
        $where .= " AND e.expenseID NOT IN (
            SELECT expenseID FROM tija_invoice_expenses ie
            INNER JOIN tija_invoice_items ii ON ie.invoiceItemID = ii.invoiceItemID
            INNER JOIN tija_invoices i ON ii.invoiceID = i.invoiceID
            WHERE i.Suspended = 'N' AND i.invoiceStatusID NOT IN (6, 8)
        )";

        $query = "
            SELECT
                e.expenseID,
                e.expenseDate,
                e.expenseAmount,
                e.expenseDescription,
                e.expenseCategory,
                e.receiptURL,
                et.typeName as expenseTypeName,
                p.projectName
            FROM tija_project_expenses e
            LEFT JOIN tija_projects p ON e.projectID = p.projectID
            LEFT JOIN tija_expense_types et ON e.expenseTypeID = et.expenseTypeID
            {$where}
            ORDER BY e.expenseDate ASC
        ";

        $rows = $DBConn->fetch_all_rows($query, $params);
        return is_array($rows) ? $rows : array();
    }

    /**
     * Get billable fee expenses for a project
     *
     * @param int $projectID - Project ID
     * @param string $startDate - Start date (Y-m-d)
     * @param string $endDate - End date (Y-m-d)
     * @param object $DBConn - Database connection
     * @return array - Billable fee expenses
     */
    public static function get_billable_fee_expenses($projectID, $startDate = null, $endDate = null, $DBConn) {
        $where = "WHERE fe.projectID = ? AND fe.billable = 'Y' AND fe.Suspended = 'N'";
        $params = array(array($projectID, 'fe'));

        if ($startDate) {
            $where .= " AND fe.dateOfCost >= ?";
            $params[] = array($startDate, 'fe');
        }

        if ($endDate) {
            $where .= " AND fe.dateOfCost <= ?";
            $params[] = array($endDate, 'fe');
        }

        // Exclude already invoiced fee expenses
        $where .= " AND fe.projectFeeExpenseID NOT IN (
            SELECT feeExpenseID FROM tija_invoice_expenses ie
            INNER JOIN tija_invoice_items ii ON ie.invoiceItemID = ii.invoiceItemID
            INNER JOIN tija_invoices i ON ii.invoiceID = i.invoiceID
            WHERE i.Suspended = 'N' AND i.invoiceStatusID NOT IN (6, 8)
        )";

        $query = "
            SELECT
                fe.projectFeeExpenseID,
                fe.dateOfCost,
                fe.feeCostName,
                fe.feeCostDescription,
                fe.productQuantity,
                fe.productUnit,
                fe.unitPrice,
                fe.unitCost,
                fe.vat,
                (fe.productQuantity * fe.unitPrice) as totalAmount,
                pt.productTypeName,
                p.projectName
            FROM tija_project_fee_expenses fe
            LEFT JOIN tija_projects p ON fe.projectID = p.projectID
            LEFT JOIN tija_product_types pt ON fe.productTypeID = pt.productTypeID
            {$where}
            ORDER BY fe.dateOfCost ASC
        ";

        $rows = $DBConn->fetch_all_rows($query, $params);
        return is_array($rows) ? $rows : array();
    }
}

?>