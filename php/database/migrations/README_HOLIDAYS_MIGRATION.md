# Holidays Database Migration Guide

**Migration File**: `holidays_multi_jurisdiction_migration.sql`
**Date**: November 6, 2025
**Purpose**: Add multi-jurisdiction and recurring holidays support

---

## 🎯 What This Migration Does

### 1. **Creates/Updates `tija_holidays` Table**

Ensures the table exists with all necessary columns:

#### Original Columns
- `holidayID` - Primary key
- `DateAdded` - Creation timestamp
- `holidayName` - Holiday name
- `holidayDate` - Date of holiday
- `holidayType` - full_day or half_day
- `countryID` - Country reference
- `repeatsAnnually` - Y/N for recurring
- `LastUpdate` - Last modification
- `Lapsed`, `Suspended` - Status flags

#### New Columns Added
- `jurisdictionLevel` - global, country, region, city, entity
- `regionID` - Region/state identifier
- `cityID` - City identifier
- `entitySpecific` - Comma-separated entity IDs
- `applyToEmploymentTypes` - Employment type filters
- `excludeBusinessUnits` - Business units to exclude
- `affectsLeaveBalance` - Whether to exclude from leave calculations
- `holidayNotes` - Additional observance details
- `CreatedByID` - User who created
- `LastUpdateByID` - User who last updated
- `CreateDate` - Creation datetime
- `generatedFrom` - Source holiday for auto-generated instances

### 2. **Creates `tija_holiday_audit_log` Table** (Optional)

Tracks all changes to holidays:
- Who created/updated/deleted
- When it happened
- What changed (JSON details)
- IP address tracking

### 3. **Updates Existing Data**

Sets default values for existing holidays:
- `jurisdictionLevel` → 'country'
- `affectsLeaveBalance` → 'Y'
- `applyToEmploymentTypes` → 'all'

---

## 🚀 How to Run the Migration

### Option 1: Using phpMyAdmin (Recommended for WAMP)

1. **Open phpMyAdmin**
   - Navigate to: http://localhost/phpmyadmin

2. **Select Your Database**
   - Click on your PMS database

3. **Go to SQL Tab**
   - Click the "SQL" tab at the top

4. **Copy and Paste**
   - Open `holidays_multi_jurisdiction_migration.sql`
   - Copy ALL contents
   - Paste into the SQL query box

5. **Execute**
   - Click "Go" button
   - Wait for success message

6. **Verify**
   - Check the table structure: `SHOW COLUMNS FROM tija_holidays;`
   - Should see all new columns

### Option 2: Using Command Line

```bash
# Navigate to project root
cd C:\wamp64\www\demo-pms.tija.ke

# Run migration
mysql -u your_username -p your_database_name < php/database/migrations/holidays_multi_jurisdiction_migration.sql
```

### Option 3: Using PHP Script

Create a temporary migration runner:

```php
<?php
require_once 'php/includes.php';

$sql = file_get_contents('php/database/migrations/holidays_multi_jurisdiction_migration.sql');
$statements = explode(';', $sql);

foreach ($statements as $statement) {
    $statement = trim($statement);
    if (!empty($statement)) {
        $DBConn->execute_query($statement);
    }
}

echo "Migration completed!";
?>
```

---

## ✅ Verification Steps

After running the migration:

### 1. Check Table Structure

```sql
SHOW COLUMNS FROM `tija_holidays`;
```

**Expected Result**: Should show all 24+ columns including new ones

### 2. Check Existing Data

```sql
SELECT holidayID, holidayName, jurisdictionLevel, affectsLeaveBalance
FROM `tija_holidays`
LIMIT 5;
```

**Expected Result**: Existing holidays should have default values set

### 3. Check Indexes

```sql
SHOW INDEX FROM `tija_holidays`;
```

**Expected Result**: Should see indexes on new columns

### 4. Test Insert

```sql
INSERT INTO `tija_holidays`
(holidayName, holidayDate, holidayType, countryID, repeatsAnnually, jurisdictionLevel)
VALUES
('Test Holiday', '2025-12-31', 'full_day', '114', 'Y', 'country');
```

**Expected Result**: Insert successful

### 5. Test Retrieve

```sql
SELECT * FROM `tija_holidays` WHERE holidayName = 'Test Holiday';
```

**Expected Result**: Holiday retrieved with all fields

---

## 🔄 Rollback Instructions

If you need to undo the migration:

```sql
-- Remove new columns
ALTER TABLE `tija_holidays`
  DROP COLUMN IF EXISTS `jurisdictionLevel`,
  DROP COLUMN IF EXISTS `regionID`,
  DROP COLUMN IF EXISTS `cityID`,
  DROP COLUMN IF EXISTS `entitySpecific`,
  DROP COLUMN IF EXISTS `applyToEmploymentTypes`,
  DROP COLUMN IF EXISTS `excludeBusinessUnits`,
  DROP COLUMN IF EXISTS `affectsLeaveBalance`,
  DROP COLUMN IF EXISTS `holidayNotes`,
  DROP COLUMN IF EXISTS `CreatedByID`,
  DROP COLUMN IF EXISTS `LastUpdateByID`,
  DROP COLUMN IF EXISTS `CreateDate`,
  DROP COLUMN IF EXISTS `generatedFrom`;

-- Drop audit log table
DROP TABLE IF EXISTS `tija_holiday_audit_log`;
```

---

## ⚠️ Important Notes

### Before Running

1. **Backup your database**
   ```bash
   mysqldump -u username -p database_name > backup_before_migration.sql
   ```

2. **Note current record count**
   ```sql
   SELECT COUNT(*) FROM tija_holidays;
   ```

3. **Check for conflicts**
   ```sql
   SHOW COLUMNS FROM tija_holidays LIKE 'jurisdictionLevel';
   ```

### After Running

1. **Verify count unchanged**
   ```sql
   SELECT COUNT(*) FROM tija_holidays;
   ```
   (Should be same as before)

2. **Check no nulls in critical fields**
   ```sql
   SELECT COUNT(*) FROM tija_holidays WHERE jurisdictionLevel IS NULL;
   ```
   (Should be 0)

3. **Test the application**
   - Go to Holidays page
   - Create a new holiday
   - Verify all fields save correctly

---

## 🐛 Troubleshooting

### Error: "Column already exists"

**Solution**: This is normal if running migration multiple times. The migration uses `ADD COLUMN IF NOT EXISTS` which is safe.

**MySQL < 8.0 Alternative**: If your MySQL version doesn't support `IF NOT EXISTS`, run this check first:

```sql
-- Check if column exists
SELECT COLUMN_NAME
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'tija_holidays'
AND COLUMN_NAME = 'jurisdictionLevel';
```

If it returns a row, skip that ALTER statement.

### Error: "Table doesn't exist"

**Solution**: The CREATE TABLE statement will create it. Make sure database name is correct.

### Error: "Unknown column in WHERE clause"

**Solution**: Your existing code may be querying old column names. This migration is backward compatible.

---

## 📊 Expected Schema After Migration

```
tija_holidays
├── holidayID (PK)
├── DateAdded
├── holidayName
├── holidayDate
├── holidayType
├── countryID
├── repeatsAnnually
│
├── [NEW] jurisdictionLevel
├── [NEW] regionID
├── [NEW] cityID
├── [NEW] entitySpecific
├── [NEW] applyToEmploymentTypes
├── [NEW] excludeBusinessUnits
├── [NEW] affectsLeaveBalance
├── [NEW] holidayNotes
├── [NEW] CreatedByID
├── [NEW] LastUpdateByID
├── [NEW] CreateDate
├── [NEW] generatedFrom
│
├── LastUpdate
├── Lapsed
└── Suspended
```

---

## ✅ Success Criteria

Migration is successful when:

- [x] All new columns exist
- [x] Existing data preserved
- [x] Default values set
- [x] Indexes created
- [x] Audit table created
- [x] No errors in application
- [x] Can create new holidays
- [x] Can generate recurring holidays

---

## 📞 Support

If you encounter issues:

1. Check error logs: `php_error.log`
2. Verify MySQL version: `SELECT VERSION();`
3. Check table status: `SHOW TABLE STATUS LIKE 'tija_holidays';`
4. Review migration file for syntax errors

---

**After successful migration, all holiday features will be fully functional!** ✅

