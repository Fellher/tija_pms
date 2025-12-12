# Prospect Methods Consolidation Plan

## Overview
Consolidate all prospect-related methods from various script files into the Sales class for better reusability and maintainability.

## Current State - Functions Found

### 1. prospect_notes.php (4 functions)
- `getNotes()` - Retrieve notes for a prospect
- `addNote()` - Add a new note
- `editNote()` - Edit existing note
- `deleteNote()` - Delete a note

**Status:** ✅ Already migrated to new sales_case_notes system
**Action:** Keep for backward compatibility or deprecate

### 2. manage_prospect_advanced.php (8 functions)
- `createProspect()` - Create new prospect
- `updateProspect()` - Update prospect details
- `deleteProspect()` - Soft delete prospect
- `assignTeam()` - Assign prospect to team
- `updateStatus()` - Update prospect status
- `updateQualification()` - Update qualification status
- `calculateScore()` - Calculate lead score
- `logInteraction()` - Log interaction with prospect

**Status:** ⚠️ Should be moved to Sales class
**Priority:** HIGH - Core CRUD operations

### 3. bulk_prospect_operations.php (5 functions)
- `bulkAssignTeam()` - Assign multiple prospects to team
- `bulkUpdateStatus()` - Update status for multiple prospects
- `bulkUpdateQualification()` - Update qualification for multiple
- `bulkDelete()` - Delete multiple prospects
- `bulkCalculateScores()` - Calculate scores for multiple prospects

**Status:** ⚠️ Should be moved to Sales class
**Priority:** MEDIUM - Bulk operations

### 4. import_prospects.php (3 functions)
- `handleFileUpload()` - Handle CSV file upload
- `detectColumnMapping()` - Auto-detect CSV columns
- `importProspects()` - Import prospects from CSV

**Status:** ⚠️ Should be moved to Sales class
**Priority:** LOW - Import functionality

### 5. convert_prospect_to_sale.php (2 functions)
- `getSalesStages()` - Get available sales stages
- `convertProspectToSale()` - Convert prospect to sale

**Status:** ✅ Already using Sales class methods
**Action:** Keep as API endpoint

## Recommended Consolidation

### Phase 1: Core CRUD Methods (HIGH PRIORITY)

Move to Sales class as static methods:

```php
// In Sales class
public static function create_prospect($prospectData, $DBConn) { }
public static function update_prospect($prospectID, $prospectData, $DBConn) { }
public static function delete_prospect($prospectID, $userID, $DBConn) { }
public static function assign_prospect_team($prospectID, $teamID, $userID, $DBConn) { }
public static function update_prospect_status($prospectID, $status, $userID, $DBConn) { }
public static function update_prospect_qualification($prospectID, $qualificationData, $userID, $DBConn) { }
public static function calculate_prospect_score($prospectID, $DBConn) { }
public static function log_prospect_interaction($prospectID, $interactionData, $DBConn) { }
```

**Benefits:**
- Reusable across multiple scripts
- Consistent API
- Easier testing
- Better organization

### Phase 2: Bulk Operations (MEDIUM PRIORITY)

Move to Sales class:

```php
public static function bulk_assign_team($prospectIDs, $teamID, $userID, $DBConn) { }
public static function bulk_update_status($prospectIDs, $status, $userID, $DBConn) { }
public static function bulk_update_qualification($prospectIDs, $qualificationData, $userID, $DBConn) { }
public static function bulk_delete_prospects($prospectIDs, $userID, $DBConn) { }
public static function bulk_calculate_scores($prospectIDs, $DBConn) { }
```

### Phase 3: Import Functions (LOW PRIORITY)

Move to Sales class:

```php
public static function import_prospects_from_csv($filePath, $columnMapping, $userID, $DBConn) { }
public static function detect_csv_column_mapping($headers) { }
public static function validate_prospect_import_data($rowData) { }
```

## Migration Strategy

### Step 1: Create Methods in Sales Class
1. Copy function logic from script files
2. Convert to static methods
3. Add proper parameter validation
4. Add return value documentation
5. Add error handling

### Step 2: Update Script Files
1. Replace function calls with Sales::method_name()
2. Keep script files as thin API wrappers
3. Maintain backward compatibility

### Step 3: Update Calling Code
1. Search for direct function calls
2. Replace with Sales class method calls
3. Test thoroughly

## Example Migration

### Before (in manage_prospect_advanced.php):
```php
function createProspect($DBConn, $userDetails) {
    // Validation
    $prospectName = Utility::clean_string($_POST['salesProspectName']);

    // Create data array
    $prospectData = array(
        'salesProspectName' => $prospectName,
        // ... more fields
    );

    // Insert
    $prospectID = $DBConn->insert_data('tija_sales_prospects', $prospectData);

    return array('success' => true, 'prospectID' => $prospectID);
}

// Called as:
$result = createProspect($DBConn, $userDetails);
```

### After (in Sales class):
```php
public static function create_prospect($prospectData, $userID, $DBConn) {
    // Validation
    if (empty($prospectData['salesProspectName'])) {
        return array('success' => false, 'message' => 'Prospect name is required');
    }

    // Add audit fields
    $prospectData['createdByID'] = $userID;
    $prospectData['DateAdded'] = date('Y-m-d H:i:s');

    // Insert
    $prospectID = $DBConn->insert_data('tija_sales_prospects', $prospectData);

    if ($prospectID) {
        return array('success' => true, 'prospectID' => $prospectID, 'message' => 'Prospect created successfully');
    }

    return array('success' => false, 'message' => 'Failed to create prospect');
}

// Called as:
$result = Sales::create_prospect($prospectData, $userDetails->ID, $DBConn);
```

### Script file becomes thin wrapper:
```php
// manage_prospect_advanced.php
case 'createProspect':
    $prospectData = array(
        'salesProspectName' => Utility::clean_string($_POST['salesProspectName']),
        'prospectEmail' => Utility::clean_string($_POST['prospectEmail']),
        // ... gather all fields from POST
    );

    $response = Sales::create_prospect($prospectData, $userDetails->ID, $DBConn);
    echo json_encode($response);
    break;
```

## Benefits of Consolidation

### 1. Reusability
- Methods can be called from anywhere
- No code duplication
- Consistent behavior

### 2. Maintainability
- Single source of truth
- Easier to update logic
- Centralized bug fixes

### 3. Testability
- Static methods easy to test
- Mock database connections
- Unit test each method

### 4. Organization
- All prospect logic in one place
- Clear API surface
- Better documentation

### 5. Performance
- No function redefinition
- Autoloading optimized
- Better caching

## Implementation Checklist

### Phase 1: Core Methods
- [ ] Create Sales::create_prospect()
- [ ] Create Sales::update_prospect()
- [ ] Create Sales::delete_prospect()
- [ ] Create Sales::assign_prospect_team()
- [ ] Create Sales::update_prospect_status()
- [ ] Create Sales::update_prospect_qualification()
- [ ] Create Sales::calculate_prospect_score()
- [ ] Create Sales::log_prospect_interaction()
- [ ] Update manage_prospect_advanced.php to use new methods
- [ ] Test all CRUD operations

### Phase 2: Bulk Operations
- [ ] Create Sales::bulk_assign_team()
- [ ] Create Sales::bulk_update_status()
- [ ] Create Sales::bulk_update_qualification()
- [ ] Create Sales::bulk_delete_prospects()
- [ ] Create Sales::bulk_calculate_scores()
- [ ] Update bulk_prospect_operations.php
- [ ] Test bulk operations

### Phase 3: Import Functions
- [ ] Create Sales::import_prospects_from_csv()
- [ ] Create Sales::detect_csv_column_mapping()
- [ ] Create Sales::validate_prospect_import_data()
- [ ] Update import_prospects.php
- [ ] Test import functionality

### Phase 4: Cleanup
- [ ] Remove deprecated functions
- [ ] Update documentation
- [ ] Add method comments
- [ ] Create usage examples

## Estimated Effort

- **Phase 1:** 2-3 hours (8 methods)
- **Phase 2:** 1-2 hours (5 methods)
- **Phase 3:** 1-2 hours (3 methods)
- **Testing:** 2-3 hours
- **Total:** 6-10 hours

## Next Steps

1. **Immediate:** Start with Phase 1 - Core CRUD methods
2. **This Week:** Complete Phase 1 and test
3. **Next Week:** Implement Phase 2 - Bulk operations
4. **Future:** Phase 3 - Import functions

## Notes

- Keep script files as thin API wrappers
- Maintain backward compatibility during transition
- Add deprecation notices to old functions
- Update all calling code gradually
- Document new Sales class methods
- Create migration guide for developers
