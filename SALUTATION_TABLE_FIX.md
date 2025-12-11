# Salutation Table Fix

## Issue
Fatal error when loading client wizard:
```
SQLSTATE[42S02]: Base table or view not found: 1146
Table 'sbsl_pms_tija_re.sbsl_salutation' doesn't exist
```

## Root Cause
The `Utility::salutation()` method was querying the wrong table name:
- **Incorrect**: `sbsl_salutation` (old table name)
- **Correct**: `tija_salutation` (actual table name)

## Solution

### 1. Created New Method in Admin Class
Added `Admin::salutations()` method to properly query the correct table.

**File**: `php/classes/admin.php`

```php
/**
 * Get salutations from database
 *
 * @param array $whereArr Where clause conditions
 * @param bool $single Return single record or array
 * @param object $DBConn Database connection
 * @return mixed Single object or array of objects, or false
 */
public static function salutations($whereArr, $single, $DBConn) {
    $cols = array('salutationID', 'DateAdded', 'salutation', 'LastUpdate', 'LastUpdateByID', 'Lapsed', 'Suspended');
    $rows = $DBConn->retrieve_db_table_rows('tija_salutation', $cols, $whereArr);
    return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
}
```

### 2. Updated Client Wizard
Changed the wizard to use the new Admin class method.

**File**: `html/includes/scripts/clients/modals/manage_client_wizard.php`

**Before**:
```php
$salutations = Utility::salutation([], false, $DBConn);
```

**After**:
```php
$salutations = Admin::salutations([], false, $DBConn);
```

## Table Schema

### tija_salutation
```sql
- salutationID (PK)
- DateAdded
- salutation (e.g., 'Mr.', 'Mrs.', 'Dr.', 'Ms.')
- LastUpdate
- LastUpdateByID
- Lapsed
- Suspended
```

## Why Admin Class?

The `Admin` class is the appropriate location for this method because:
1. ✅ Salutations are administrative/reference data
2. ✅ Consistent with other lookup tables in Admin class
3. ✅ Follows the pattern of other Admin methods (job_titles, job_bands, etc.)
4. ✅ Proper table naming convention (`tija_salutation`)

## Note on Utility Class

The `Utility::salutation()` method still exists but queries the old `sbsl_salutation` table. This should be:
- **Deprecated** if no longer used elsewhere
- **Updated** to use `tija_salutation` if still needed
- **Removed** if the Admin method replaces it entirely

## Testing

✅ Client wizard now loads without errors
✅ Salutation dropdown populates correctly
✅ Data retrieved from correct table: `tija_salutation`

## Status: ✅ RESOLVED

The client wizard now successfully loads salutations from the correct database table.
