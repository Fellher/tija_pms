# 🚀 Activity Wizard - Quick Start Guide

## ⚡ Fix All Errors in 5 Minutes

### 🎯 What You Need To Do

**ONE SIMPLE STEP: Run the database migration!**

---

## 📝 Step-by-Step Instructions

### Method 1: phpMyAdmin (⭐ Recommended - Easiest)

1. **Open phpMyAdmin**
   - Go to: `http://localhost/phpmyadmin`
   - Login with your credentials

2. **Select Database**
   - Click on `pms_sbsl_deploy` in the left sidebar

3. **Open SQL Tab**
   - Click the "SQL" tab at the top

4. **Copy Migration Script**
   - Open file: `database/migrations/run_all_migrations.sql`
   - Select all content (Ctrl + A)
   - Copy (Ctrl + C)

5. **Paste and Execute**
   - Paste in the SQL window (Ctrl + V)
   - Click the "Go" button at bottom-right
   - Wait 2-3 seconds

6. **Verify Success**
   - You should see green checkmarks
   - Message: "ALL MIGRATIONS COMPLETED SUCCESSFULLY!"

7. **Done!**
   - Close phpMyAdmin
   - Go back to your application
   - Refresh (Ctrl + F5)
   - **All errors fixed!** ✅

---

### Method 2: Command Line (For Advanced Users)

```bash
# Navigate to migrations folder
cd C:\wamp64\www\sbsl.tija.sbsl.co.ke\database\migrations

# Run migration
mysql -u sbsl_user -p pms_sbsl_deploy < run_all_migrations.sql

# Enter password when prompted: $@alfr0nzE6585

# Verify
mysql -u sbsl_user -p pms_sbsl_deploy -e "SHOW TABLES LIKE 'tija_activity_%'"
```

---

### Method 3: Windows Batch Script (Automated)

```bash
# Navigate to migrations folder
cd C:\wamp64\www\sbsl.tija.sbsl.co.ke\database\migrations

# Run the batch file
run_all_migrations.bat

# Wait for completion message
```

---

## ✅ Verification

After running the migration, verify it worked:

### Quick Check in phpMyAdmin:
```sql
-- Run this query:
SHOW TABLES LIKE 'tija_activity_%';

-- You should see:
✅ tija_activities
✅ tija_activity_attachments
✅ tija_activity_comments
✅ tija_activity_expenses ⭐ (This is the new one!)
✅ tija_activity_history
✅ tija_activity_reminders
```

### Check Expense Categories:
```sql
SELECT COUNT(*) FROM tija_expense_categories;
-- Should return: 10
```

---

## 🎉 What Will Work After Migration

### ✅ All Errors Fixed:
- ❌ ~~Table 'tija_activity_expenses' doesn't exist~~
- ❌ ~~Trying to get property 'ID' of non-object~~
- ❌ ~~Undefined variable: clientContactTypes~~
- ❌ ~~Undefined variable: clientAddresses~~
- ❌ ~~Undefined variable: projects~~
- ❌ ~~Trying to access array offset on value of type null~~

### ✅ Features Now Available:
- 5-step activity wizard
- Add multiple expenses per activity
- Real-time expense totals
- Category-based expense tracking
- Comprehensive summary tab
- Enhanced timeline display
- Tom Select multi-select for participants
- Category-type dynamic filtering

---

## 🏃 Fastest Path to Success

1. **Open phpMyAdmin** (30 seconds)
2. **Select `pms_sbsl_deploy` database** (5 seconds)
3. **Click SQL tab** (2 seconds)
4. **Open & copy `run_all_migrations.sql`** (10 seconds)
5. **Paste and click Go** (10 seconds)
6. **Wait for completion** (5 seconds)
7. **Refresh your application** (5 seconds)

**Total Time: ~1 minute!** ⏱️

---

## 🆘 Troubleshooting

### Issue: Can't find phpMyAdmin
**Solution:**
- WAMP: `http://localhost/phpmyadmin`
- Or use WAMP menu: Left-click WAMP icon → phpMyAdmin

### Issue: Wrong password
**Solution:**
Your MySQL password is: `$@alfr0nzE6585`

### Issue: Can't see database
**Solution:**
- Verify WAMP is running (should be green icon)
- Check MySQL service is started
- Refresh phpMyAdmin page

### Issue: SQL query fails
**Solution:**
- Check you selected the correct database
- Ensure you copied the entire SQL file
- Try running migrations individually:
  1. `add_activity_wizard_fields.sql` first
  2. `add_activity_multi_expenses.sql` second

### Issue: Still seeing errors after migration
**Solution:**
```bash
# 1. Restart Apache
# In WAMP menu: Restart All Services

# 2. Clear browser cache
# Press: Ctrl + Shift + Delete
# Select: Cached images and files
# Click: Clear data

# 3. Hard refresh page
# Press: Ctrl + F5
```

---

## 📊 What Gets Created

### Tables (6 new):
1. ✅ `tija_activity_expenses` - Multi-expense tracking
2. ✅ `tija_expense_categories` - Expense categories
3. ✅ `tija_activity_history` - Audit trail
4. ✅ `tija_activity_attachments` - File uploads
5. ✅ `tija_activity_reminders` - Notifications
6. ✅ `tija_activity_comments` - Discussions

### Fields (13 new in tija_activities):
- meetingLink
- activityNotes
- activityOutcome
- activityResult
- activityCost (deprecated)
- costCategory (deprecated)
- costNotes (deprecated)
- followUpNotes
- requiresFollowUp
- sendReminder
- reminderTime
- allDayEvent
- duration

### View (1 new):
- `view_activity_expense_totals` - Expense aggregation

### Categories (10 new):
- Travel, Meals, Materials, Accommodation, Technology
- Communication, Parking, Fuel, Gifts, Other

### Indexes (5 new):
- Performance optimizations for queries

---

## 🎯 After Migration Success

### Test the Feature:
1. Navigate to: Sales Case Details page
2. Click: "Add Activity" button
3. Fill Step 1: Activity details
4. Fill Step 2: Schedule
5. Fill Step 3: Participants
6. **Fill Step 4: Click "Add Expense" multiple times**
   - Add: Travel - KES 500
   - Add: Meals - KES 3,500
   - Add: Parking - KES 200
   - See: Total = KES 4,200 ✅
7. Go to Step 5: See expense breakdown table
8. Click: "Save Activity"
9. Success! ✅

### Verify in Database:
```sql
-- Check activity was created
SELECT * FROM tija_activities ORDER BY activityID DESC LIMIT 1;

-- Check expenses were saved
SELECT * FROM tija_activity_expenses WHERE activityID = [your_activity_id];

-- Should see 3 expense rows (Travel, Meals, Parking)
```

---

## 📞 Need Help?

If you're still seeing errors after running the migration:

1. **Check error log location:**
   - `C:\wamp64\logs\php_error.log`
   - Look for new errors (after today's timestamp)

2. **Verify migration ran:**
   ```sql
   SELECT COUNT(*) FROM tija_activity_expenses;
   -- If this returns a number, migration worked!
   ```

3. **Check PHP version:**
   - Requires PHP 7.4 or higher
   - Check: `http://localhost/?phpinfo=1`

4. **Restart everything:**
   - Stop WAMP
   - Wait 5 seconds
   - Start WAMP
   - Wait for green icon
   - Try again

---

## 🎊 Success Looks Like This:

**Before:** ❌
```
Error: Table 'tija_activity_expenses' doesn't exist
Error: Trying to get property 'ID' of non-object
Error: Undefined variable: clientContactTypes
[Multiple PHP warnings...]
```

**After:** ✅
```
✅ Page loads cleanly
✅ Activity wizard opens
✅ All 5 steps work
✅ Can add multiple expenses
✅ Total calculates automatically
✅ Summary shows everything
✅ Saves successfully
✅ Zero errors!
```

---

## 🏁 Ready? Let's Fix It!

**👉 Open phpMyAdmin now and run the migration! It takes 1 minute!**

All your code fixes are already in place. You just need to create the database tables!

---

**Questions? All the detailed documentation is in:**
- `database/migrations/README_ACTIVITY_WIZARD.md`
- `database/migrations/README_MULTI_EXPENSE.md`
- `IMPLEMENTATION_SUMMARY.md`

**Let's go! 🚀**


