# Multi-Expense System Implementation

## Overview
This implementation allows employees to add multiple expense line items to a single activity. For example, an employee can separately track travel costs, meal expenses, and material purchases all for one client meeting.

## Database Structure

### Migration File
**File:** `add_activity_multi_expenses.sql`

### New Table: `tija_activity_expenses`
Stores multiple expense entries per activity.

**Fields:**
- `expenseID` (INT, PK) - Unique expense identifier
- `activityID` (INT) - Links to tija_activities
- `expenseDate` (DATE) - When expense was incurred
- `expenseCategory` (VARCHAR) - Category (Travel, Meals, etc.)
- `expenseAmount` (DECIMAL) - Expense amount
- `expenseDescription` (TEXT) - Description of expense
- `expenseCurrency` (VARCHAR) - Currency code (default: KES)
- `receiptNumber` (VARCHAR) - Receipt/invoice number
- `receiptAttached` (ENUM) - Y/N flag
- `receiptPath` (VARCHAR) - Path to receipt file
- `paymentMethod` (VARCHAR) - Cash, Card, Mpesa, etc.
- `reimbursable` (ENUM) - Y/N reimbursable flag
- `reimbursementStatus` (ENUM) - pending/approved/rejected/paid
- `approvedBy` (INT) - Who approved reimbursement
- `approvedOn` (DATETIME) - Approval timestamp
- `paidOn` (DATETIME) - Payment timestamp
- `addedBy` (INT) - User who added expense
- `addedOn` (DATETIME) - Creation timestamp
- `LastUpdate` (DATETIME) - Last modification
- `LastUpdatedByID` (INT) - Last modifier
- `Suspended` (ENUM) - Y/N active flag

**Indexes:**
- `idx_activity_expenses` - Fast activity lookup
- `idx_expense_date` - Date queries
- `idx_expense_category` - Category filtering
- `idx_reimbursement_status` - Reimbursement tracking

### New Table: `tija_expense_categories`
Reference table for standardized expense categories.

**Pre-loaded Categories:**
1. **Travel** 🚕 - Transportation and mileage
2. **Meals** 🍽️ - Client entertainment and meals (Max: KES 5,000)
3. **Materials** 📋 - Sales collateral and materials
4. **Accommodation** 🏨 - Hotel and lodging
5. **Technology** 💻 - Software, tools, subscriptions
6. **Communication** 📞 - Phone, internet, data (Max: KES 2,000)
7. **Parking** 🅿️ - Parking fees (Max: KES 500)
8. **Fuel** ⛽ - Vehicle fuel
9. **Gifts** 🎁 - Client gifts and giveaways (Max: KES 10,000)
10. **Other** 📌 - Miscellaneous expenses

### View: `view_activity_expense_totals`
Provides easy aggregation of expenses per activity.

**Calculated Fields:**
- `expenseCount` - Number of expense items
- `totalExpenses` - Sum of all expenses
- `totalReimbursable` - Sum of reimbursable expenses
- `totalNonReimbursable` - Sum of non-reimbursable expenses
- `pendingReimbursement` - Pending approval amount
- `approvedReimbursement` - Approved but unpaid
- `paidReimbursement` - Already reimbursed

## Frontend Implementation

### Updated Wizard (Step 4)

#### New UI Components:

**1. Add Expense Button**
- Floating button to add expense rows
- Primary blue styling

**2. Expense Items Container**
- Scrollable container (max-height: 400px)
- Each expense displayed as a card
- Color-coded left border by category

**3. Expense Row Fields:**
- **Category** (Required) - Dropdown with emoji icons
- **Amount** (Required) - KES currency input
- **Description** - Brief description
- **Remove Button** - Delete expense row

**4. Collapsible Details:**
- Payment Method
- Receipt Number
- Reimbursable flag

**5. Total Display Card:**
- Real-time calculation
- Shows total expenses
- Displays expense item count

#### Empty State:
When no expenses added:
- Icon display
- Helpful message
- Call-to-action to add expense

### Summary Tab (Step 5)

**Expense Breakdown Table:**
- Lists all expense items
- Columns: Category | Description | Amount
- Footer row with bold total
- Hover effects on rows
- Only displays if expenses exist

### Features:

#### Dynamic Row Management
```javascript
// Add expense row
addExpenseRow()

// Remove expense row
removeExpenseRow(index)

// Calculate total
calculateExpenseTotal()
```

#### Visual Feedback
- Color-coded borders per category
- Fade-in animation on add
- Fade-out animation on remove
- Real-time total updates

#### Validation
- Category required
- Amount required (must be > 0)
- Auto-calculation on input

#### Edit Mode Support
- Loads existing expenses
- Populates all fields
- Maintains totals

## Backend Implementation

### Updated Files

#### 1. `php/scripts/sales/manage_activity_wizard.php`

**Changes:**
- Removed single `activityCost`, `costCategory`, `costNotes` fields
- Added multi-expense array processing
- New function: `saveActivityExpenses()`

**Expense Processing:**
```php
// Process expenses array
if (isset($_POST['expenses']) && is_array($_POST['expenses'])) {
   foreach ($_POST['expenses'] as $expenseData) {
      // Validate and clean each expense
      // Store in $expenses array
   }
}

// Save to database
saveActivityExpenses($activityID, $expenses, $activityDate, $userID, $DBConn);
```

**Update Logic:**
- Delete all existing expenses for activity
- Insert new expense array
- Maintains transaction integrity

#### 2. `php/scripts/sales/get_activity.php`

**Changes:**
- Fetches expenses from `tija_activity_expenses`
- Returns as array in activity data
- Filters by `Suspended='N'`

**Response Format:**
```json
{
  "success": true,
  "activity": {
    "activityID": "123",
    "activityName": "Client Meeting",
    ...
    "expenses": [
      {
        "expenseID": "1",
        "category": "Travel",
        "amount": "500.00",
        "description": "Taxi to client office",
        "paymentMethod": "Cash",
        "receiptNumber": "TX-001",
        "reimbursable": "Y"
      },
      {
        "expenseID": "2",
        "category": "Meals",
        "amount": "3500.00",
        "description": "Lunch with client",
        "paymentMethod": "Card",
        "receiptNumber": "",
        "reimbursable": "Y"
      }
    ]
  }
}
```

### Helper Function: `saveActivityExpenses()`

```php
function saveActivityExpenses($activityID, $expenses, $activityDate, $userID, $DBConn) {
   foreach ($expenses as $expense) {
      $expenseData = array(
         'activityID' => $activityID,
         'expenseDate' => $activityDate,
         'expenseCategory' => $expense['category'],
         'expenseAmount' => $expense['amount'],
         'expenseDescription' => $expense['description'],
         'expenseCurrency' => 'KES',
         'paymentMethod' => $expense['paymentMethod'],
         'receiptNumber' => $expense['receiptNumber'],
         'reimbursable' => $expense['reimbursable'],
         'reimbursementStatus' => 'pending',
         'addedBy' => $userID,
         'LastUpdatedByID' => $userID
      );

      $DBConn->insert_data('tija_activity_expenses', $expenseData);
   }
}
```

## Data Migration

### Automatic Migration
The SQL script automatically migrates existing single expense data:

```sql
-- Moves data from tija_activities to tija_activity_expenses
INSERT INTO tija_activity_expenses (...)
SELECT activityID, activityDate, costCategory, activityCost, costNotes, ...
FROM tija_activities
WHERE activityCost > 0;
```

### Backward Compatibility
Old fields marked as deprecated but preserved:
- `activityCost` - Deprecated comment added
- `costCategory` - Deprecated comment added
- `costNotes` - Deprecated comment added

## Usage Examples

### Example 1: Client Meeting Expenses
```
Travel (Taxi): KES 500.00
Meals (Lunch): KES 3,500.00
Parking: KES 200.00
Materials (Brochures): KES 1,000.00
---------------------------------
Total: KES 5,200.00
```

### Example 2: Site Visit
```
Fuel: KES 2,500.00
Accommodation (Hotel): KES 8,000.00
Meals (Dinner): KES 2,000.00
Communication (Data): KES 500.00
---------------------------------
Total: KES 13,000.00
```

## Deployment Steps

### 1. Run Database Migration
```bash
mysql -u username -p database_name < database/migrations/add_activity_multi_expenses.sql
```

### 2. Verify Tables
```sql
-- Check new table exists
SHOW TABLES LIKE 'tija_activity_expenses';

-- Check expense categories
SELECT * FROM tija_expense_categories;

-- Check view
SELECT * FROM view_activity_expense_totals LIMIT 5;

-- Verify old data migrated
SELECT COUNT(*) FROM tija_activity_expenses;
```

### 3. Test Frontend
1. Open activity wizard
2. Navigate to Step 4 (Outcomes & Expenses)
3. Click "Add Expense"
4. Fill in multiple expenses
5. Check real-time total calculation
6. Navigate to Step 5
7. Verify expense breakdown table
8. Save and verify in database

### 4. Test Edit Mode
1. Open existing activity with expenses
2. Verify expenses load correctly
3. Add/remove expenses
4. Save and verify changes

## Benefits

### For Employees
✅ **Detailed Tracking** - Separate line items for clarity
✅ **Easy Entry** - Add/remove rows dynamically
✅ **Visual Feedback** - Color-coded categories, real-time totals
✅ **Complete Information** - Payment method, receipts, reimbursable flag

### For Finance
✅ **Reimbursement Workflow** - Status tracking (pending/approved/paid)
✅ **Category Analysis** - Report by expense type
✅ **Audit Trail** - Who added, when, approval chain
✅ **Receipt Management** - Track receipt numbers and attachments

### For Management
✅ **Cost Breakdown** - Understand expense composition
✅ **Budget Control** - Category-level limits
✅ **Trend Analysis** - View spending patterns
✅ **Approval Process** - Review before reimbursement

## Future Enhancements

### Planned Features
1. **Receipt Uploads** - Attach images/PDFs to expenses
2. **Approval Workflow** - Multi-level approval process
3. **Budget Alerts** - Notify when limits exceeded
4. **Expense Reports** - Generate PDF reports
5. **Mobile Capture** - Take photo of receipt on mobile
6. **Exchange Rates** - Support multiple currencies
7. **Mileage Calculator** - Auto-calculate travel costs
8. **Expense Templates** - Pre-fill common expenses

## API Reference

### GET /php/scripts/sales/get_activity.php
**Parameters:**
- `activityID` (required)

**Returns:**
```json
{
  "success": true,
  "activity": {
    ...
    "expenses": [...]
  }
}
```

### POST /php/scripts/sales/manage_activity_wizard.php
**Parameters:**
```
expenses[0][category]: "Travel"
expenses[0][amount]: "500.00"
expenses[0][description]: "Taxi"
expenses[0][paymentMethod]: "Cash"
expenses[0][receiptNumber]: "TX-001"
expenses[0][reimbursable]: "Y"

expenses[1][category]: "Meals"
expenses[1][amount]: "3500.00"
expenses[1][description]: "Lunch"
...
```

## Reporting Queries

### Total Expenses by Activity
```sql
SELECT * FROM view_activity_expense_totals;
```

### Expenses by Category
```sql
SELECT
   expenseCategory,
   COUNT(*) as count,
   SUM(expenseAmount) as total
FROM tija_activity_expenses
WHERE Suspended = 'N'
GROUP BY expenseCategory
ORDER BY total DESC;
```

### Pending Reimbursements
```sql
SELECT
   a.activityName,
   e.expenseCategory,
   e.expenseAmount,
   e.addedBy,
   e.addedOn
FROM tija_activity_expenses e
JOIN tija_activities a ON e.activityID = a.activityID
WHERE e.reimbursementStatus = 'pending'
AND e.reimbursable = 'Y'
AND e.Suspended = 'N'
ORDER BY e.addedOn DESC;
```

### Employee Expense Summary
```sql
SELECT
   u.FirstName,
   u.Surname,
   COUNT(e.expenseID) as expense_count,
   SUM(e.expenseAmount) as total_expenses
FROM tija_activity_expenses e
JOIN people u ON e.addedBy = u.ID
WHERE e.Suspended = 'N'
GROUP BY e.addedBy
ORDER BY total_expenses DESC;
```

## Version History

### v2.1.0 (2025-12-02)
- Multi-expense system implementation
- New tables: tija_activity_expenses, tija_expense_categories
- View: view_activity_expense_totals
- Frontend: Dynamic expense rows
- Backend: Array processing, saveActivityExpenses()
- Data migration from single expense fields
- Complete CRUD support

## Support

### Common Issues

**Issue:** Expenses not saving
**Solution:** Check that amounts are > 0 and category is selected. Check PHP error logs.

**Issue:** Total not calculating
**Solution:** Verify JavaScript console for errors. Check amount inputs are numeric.

**Issue:** Old expenses not loading
**Solution:** Verify migration completed successfully. Check tija_activity_expenses table has data.

**Issue:** Can't remove expense row
**Solution:** Check browser console for JavaScript errors. Ensure jQuery/Bootstrap loaded.

## License
Proprietary - Tija CRM System

---

**Implementation Complete! Multi-expense tracking is now fully functional.** 🎉


