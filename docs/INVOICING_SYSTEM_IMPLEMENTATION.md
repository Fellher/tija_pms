# Enterprise Invoicing System - Implementation Summary

## Overview
A comprehensive invoicing system integrated with the projects module, allowing users to invoice clients based on projects, tasks, employee work hours, expenses, and licenses.

## Database Schema Created

### New Tables
1. **`tija_invoice_items`** - Invoice line items (projects, tasks, hours, expenses)
2. **`tija_invoice_templates`** - Reusable invoice templates
3. **`tija_invoice_payments`** - Payment tracking
4. **`tija_invoice_work_hours`** - Maps work hours to invoice items
5. **`tija_invoice_expenses`** - Maps expenses to invoice items
6. **`tija_invoice_licenses`** - Licenses/subscriptions that can be billed

### Updated Tables
- **`tija_invoices`** - Added fields: `templateID`, `subtotal`, `discountPercent`, `discountAmount`, `notes`, `terms`, `pdfURL`, `sentDate`, `paidDate`, `paidAmount`, `outstandingAmount`

## Backend Implementation

### Enhanced Classes
- **`php/classes/invoice.php`** - Enhanced with methods for:
  - Invoice items management
  - Invoice templates
  - Invoice payments
  - Billable hours retrieval
  - Billable expenses retrieval
  - Billable fee expenses retrieval
  - Complete invoice data retrieval

### Backend Scripts
- **`php/scripts/invoices/manage_invoice.php`** - Handles:
  - Create/Update/Delete invoices
  - Add/Update/Delete invoice items
  - Add/Update/Delete payments
  - Status updates
  - Total calculations

## Frontend Pages (To Be Created)

### 1. Invoice List Page (`html/pages/user/invoices/list.php`)
- Display all invoices with filters
- Status badges
- Payment tracking
- Quick actions (view, edit, delete, send)
- Statistics dashboard

### 2. Invoice Create/Edit Page (`html/pages/user/invoices/create.php`)
- Project selection
- Billable hours selection (with date range)
- Expense selection
- Fee expense selection
- License selection
- Custom line items
- Template selection
- Real-time total calculation
- Preview functionality

### 3. Invoice Template Management (`html/pages/user/invoices/templates.php`)
- List templates
- Create/Edit templates
- Template preview
- Set default template

### 4. Invoice Reports (`html/pages/user/invoices/reports.php`)
- Revenue reports
- Outstanding invoices
- Overdue invoices
- Client billing summary
- Monthly/yearly reports

### 5. Invoice View/Print (`html/pages/user/invoices/view.php`)
- Invoice display
- PDF generation
- Print functionality
- Payment history

## Features Implemented

### ✅ Completed
1. Database schema
2. Enhanced Invoice class
3. Backend CRUD operations
4. Navigation menu updates

### 🔄 In Progress
1. Invoice list page
2. Invoice create/edit page

### ⏳ Pending
1. Invoice template management UI
2. Invoice reports page
3. PDF generation
4. Email sending functionality

## Integration Points

### Projects Module
- Links invoices to projects
- Retrieves billable hours from `tija_tasks_time_logs`
- Retrieves expenses from `tija_project_expenses`
- Retrieves fee expenses from `tija_project_fee_expenses`

### Time Tracking
- Uses `tija_tasks_time_logs` for work hours
- Respects project billing rates
- Excludes already invoiced hours

### Expenses
- Links project expenses to invoices
- Supports markup percentages
- Tracks expense reimbursement

### Clients
- Links invoices to clients
- Client billing summaries
- Payment tracking per client

## Usage Examples

### Creating an Invoice from Project Hours
```php
// Get billable hours for a project
$hours = Invoice::get_billable_hours($projectID, $startDate, $endDate, $DBConn);

// Create invoice with hours
$invoiceData = array(
    'clientID' => $clientID,
    'projectID' => $projectID,
    'items' => array(
        array(
            'itemType' => 'work_hours',
            'itemDescription' => 'Development work - January 2025',
            'quantity' => $totalHours,
            'unitPrice' => $billingRate,
            'timelogIDs' => array(1, 2, 3) // Time log IDs
        )
    )
);
```

### Adding Expenses to Invoice
```php
// Get billable expenses
$expenses = Invoice::get_billable_expenses($projectID, $startDate, $endDate, $DBConn);

// Add expense item
$expenseItem = array(
    'itemType' => 'expense',
    'itemDescription' => 'Travel expenses',
    'quantity' => 1,
    'unitPrice' => $expenseAmount,
    'expenseIDs' => array(1, 2, 3),
    'markupPercent' => 10 // 10% markup
);
```

## Next Steps

1. **Create Invoice List Page** - Display invoices with filters and actions
2. **Create Invoice Form** - Comprehensive form for creating/editing invoices
3. **Template Management** - UI for managing invoice templates
4. **PDF Generation** - Generate PDF invoices using templates
5. **Reports Dashboard** - Financial reports and analytics
6. **Email Integration** - Send invoices via email
7. **Payment Processing** - Integration with payment gateways (optional)

## File Structure

```
database/
  └── invoicing_system_schema.sql

php/
  ├── classes/
  │   └── invoice.php (enhanced)
  └── scripts/
      └── invoices/
          └── manage_invoice.php

html/
  ├── includes/
  │   └── nav/
  │       └── side_nav.php (updated)
  └── pages/
      └── user/
          └── invoices/
              ├── list.php (to be created)
              ├── create.php (to be created)
              ├── view.php (to be created)
              ├── templates.php (to be created)
              └── reports.php (to be created)
```

## Notes

- The system is designed to be enterprise-level with comprehensive reporting
- All invoice operations respect entity and organization boundaries
- Payment tracking is integrated with invoice status management
- The system prevents double-billing by tracking already invoiced items
- Templates allow for customization of invoice appearance
- The system supports multiple currencies and tax calculations

