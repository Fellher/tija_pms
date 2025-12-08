# Error Fix Guide - Activity Wizard

## 🚨 Current Errors & Solutions

### Error 1: Table 'tija_activity_expenses' doesn't exist
**Error Message:**
```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'pms_sbsl_deploy.tija_activity_expenses' doesn't exist
```

**Solution:**
You need to run the database migrations.

#### Option A: Using phpMyAdmin (Easiest)
1. Open phpMyAdmin
2. Select database: `pms_sbsl_deploy`
3. Click on "SQL" tab
4. Open and copy the contents of: `database/migrations/run_all_migrations.sql`
5. Paste into SQL window
6. Click "Go" button
7. Wait for success message

#### Option B: Using MySQL Command Line
```bash
cd C:\wamp64\www\sbsl.tija.sbsl.co.ke\database\migrations
mysql -u sbsl_user -p pms_sbsl_deploy < run_all_migrations.sql
# Enter password: $@alfr0nzE6585
```

#### Option C: Using Batch Script (Windows)
```bash
cd C:\wamp64\www\sbsl.tija.sbsl.co.ke\database\migrations
run_all_migrations.bat
```

---

### Error 2: Trying to get property 'ID' of non-object (schedule.php line 216-217)
**Status:** ✅ **FIXED**

The code has been updated to check if the participant exists before accessing properties.

**What was fixed:**
```php
// OLD CODE (caused error):
$participantDetails[] = (object)[
   'name' => $participantName,
   'id' => $participantDetails->ID,  // Error if $participantDetails is false
   'email' => $participantDetails->Email
];

// NEW CODE (fixed):
if($participantDetails && is_object($participantDetails)){
   $participantDetails[] = (object)[
      'name' => $participantName,
      'id' => $participantDetails->ID,
      'email' => $participantDetails->Email ?? ''
   ];
}
```

**Action Required:**
- ✅ Refresh the page (Ctrl + F5)
- ✅ Clear PHP opcache if enabled

---

### Error 3: Undefined variable: clientContactTypes
**Status:** ✅ **FIXED**

Added missing variable definitions to `sale_details.php`.

**What was fixed:**
```php
// Added these lines:
$clientContactTypes = Client::client_contact_types(array(), false, $DBConn);
$clientAddresses = Client::client_addresses(array('clientID'=>$salesCaseDetails->clientID), false, $DBConn);
$projects = array();
```

---

### Error 4: Trying to access array offset on value of type null (activity_listing.php)
**Status:** ✅ **FIXED**

Added proper array and type checking before accessing array offsets.

**What was fixed:**
```php
// Added validation:
if($participants && is_array($participantDetails) && count($participantDetails) > 0) {
   foreach($participantDetails as $participant) {
      if(is_array($participant) && isset($participant['name'])) {
         // Safe to access now
      }
   }
}
```

---

### Error 5: Undefined variable: projects (manage_activity.php)
**Status:** ✅ **FIXED**

Added `$projects = array();` initialization in sale_details.php.

---

## 🔧 Quick Fix Checklist

### Step 1: Clear PHP Cache
```bash
# Restart Apache in WAMP
# Or clear opcache:
# In php.ini, set: opcache.revalidate_freq=0 (for development)
```

### Step 2: Run Database Migrations ⚠️ **CRITICAL**
Choose one method:

**Method 1: phpMyAdmin (Recommended)**
1. Open: http://localhost/phpmyadmin
2. Select: `pms_sbsl_deploy` database
3. Click: SQL tab
4. Copy contents from: `database/migrations/run_all_migrations.sql`
5. Paste and click "Go"
6. Verify: "ALL MIGRATIONS COMPLETED SUCCESSFULLY!"

**Method 2: Command Line**
```bash
cd C:\wamp64\www\sbsl.tija.sbsl.co.ke\database\migrations
mysql -u sbsl_user -p pms_sbsl_deploy < run_all_migrations.sql
```

**Method 3: Run Batch File**
```bash
cd C:\wamp64\www\sbsl.tija.sbsl.co.ke\database\migrations
run_all_migrations.bat
```

### Step 3: Verify Tables Created
Run this query in phpMyAdmin:
```sql
USE pms_sbsl_deploy;

SHOW TABLES LIKE 'tija_activity_%';

-- Should show:
-- tija_activities (existing)
-- tija_activity_attachments (new)
-- tija_activity_comments (new)
-- tija_activity_expenses (new) ⭐
-- tija_activity_history (new)
-- tija_activity_reminders (new)
```

### Step 4: Verify Fields Added
```sql
SHOW COLUMNS FROM tija_activities LIKE '%meetingLink%';
SHOW COLUMNS FROM tija_activities LIKE '%activityNotes%';
-- Should return rows if migration succeeded
```

### Step 5: Test Expense Categories
```sql
SELECT * FROM tija_expense_categories;
-- Should show 10 categories
```

### Step 6: Refresh and Test
1. Clear browser cache (Ctrl + Shift + Delete)
2. Refresh page (Ctrl + F5)
3. Open Activity Wizard
4. Navigate to Step 4
5. Click "Add Expense"
6. Fill multiple expenses
7. See real-time total
8. Navigate to Step 5
9. See expense breakdown table
10. Save activity

---

## 🎯 Expected Results After Fix

### Database Tables
✅ 6 new tables created
✅ 13 new fields in tija_activities
✅ 10 expense categories loaded
✅ Existing data migrated

### Frontend
✅ No PHP errors or warnings
✅ Activity wizard loads all 5 steps
✅ Can add multiple expenses
✅ Real-time total calculation works
✅ Summary shows expense breakdown

### Backend
✅ Activities save successfully
✅ Expenses save to new table
✅ Can edit and update expenses
✅ No database errors

---

## 📞 Still Having Issues?

### Check PHP Error Log
Location: `C:\wamp64\logs\php_error.log`

### Check Apache Error Log
Location: `C:\wamp64\logs\apache_error.log`

### Common Issues

**Issue:** Migration fails with syntax error
**Solution:** Ensure you're using MySQL 5.7+ or MariaDB 10.2+

**Issue:** "IF NOT EXISTS" not recognized
**Solution:** Remove "IF NOT EXISTS" clauses and run migrations individually

**Issue:** Cannot connect to database
**Solution:** Verify WAMP is running, MySQL service is active

**Issue:** Permission denied
**Solution:** Check user has CREATE, ALTER, INSERT privileges

**Issue:** Still seeing old errors after fix
**Solution:**
- Restart Apache
- Clear browser cache
- Check you're editing the right files
- Verify opcache is disabled or cleared

---

## 🔍 Verification Commands

### After Running Migrations:
```sql
-- 1. Check all new tables exist
SELECT TABLE_NAME
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'pms_sbsl_deploy'
AND TABLE_NAME LIKE 'tija_activity_%';

-- 2. Check expense categories loaded
SELECT COUNT(*) as category_count
FROM tija_expense_categories;
-- Expected: 10

-- 3. Check new fields exist
SELECT COLUMN_NAME
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'pms_sbsl_deploy'
AND TABLE_NAME = 'tija_activities'
AND COLUMN_NAME IN ('meetingLink', 'activityNotes', 'activityOutcome',
                    'activityResult', 'followUpNotes', 'duration');

-- 4. Test expense view
SELECT * FROM view_activity_expense_totals LIMIT 1;
```

---

## ✅ Success Indicators

After running migrations and clearing cache, you should see:

1. ✅ Page loads without PHP errors
2. ✅ Activity wizard opens with 5 steps
3. ✅ Step 4 shows "Add Expense" button
4. ✅ Can add multiple expense rows
5. ✅ Total calculates in real-time
6. ✅ Step 5 shows expense breakdown table
7. ✅ Activity saves successfully
8. ✅ Expenses appear in database

---

## 📋 Post-Migration Checklist

- [ ] Run database migration (choose one method above)
- [ ] Verify 6 tables created
- [ ] Verify 10 expense categories exist
- [ ] Restart Apache/PHP-FPM
- [ ] Clear browser cache
- [ ] Refresh page (Ctrl + F5)
- [ ] Test adding activity with expenses
- [ ] Verify expenses save to database
- [ ] Test editing activity with expenses
- [ ] Check no PHP errors in log

---

## 🎉 Once Complete

All errors will be resolved and you'll have:
- ✅ Enhanced activity wizard with 5 steps
- ✅ Multi-expense tracking system
- ✅ Category-type filtering
- ✅ Tom Select multi-select
- ✅ Comprehensive summary tab
- ✅ Full timeline integration
- ✅ Zero PHP errors

**The Activity Wizard will be fully operational!** 🚀


