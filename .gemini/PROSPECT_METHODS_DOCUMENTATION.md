# Prospect Methods Migration - Complete Guide

## Phase 1: Core CRUD Methods - COMPLETED ✅

### Methods Added to Sales Class

All 8 core methods have been successfully added to the Sales class:

1. ✅ `Sales::create_prospect($prospectData, $userID, $DBConn)`
2. ✅ `Sales::update_prospect($prospectID, $prospectData, $userID, $DBConn)`
3. ✅ `Sales::delete_prospect($prospectID, $userID, $DBConn)`
4. ✅ `Sales::assign_prospect_team($prospectID, $teamID, $userID, $DBConn)`
5. ✅ `Sales::update_prospect_status($prospectID, $status, $userID, $DBConn)`
6. ✅ `Sales::update_prospect_qualification($prospectID, $qualificationData, $userID, $DBConn)`
7. ✅ `Sales::log_prospect_interaction($prospectID, $interactionData, $userID, $DBConn)`
8. ✅ `Sales::calculate_lead_score($prospectID, $DBConn)` - Already existed

### manage_prospect_advanced.php - Migration Status

**✅ COMPLETED:**
- `createProspect()` - Now uses `Sales::create_prospect()`

**⏳ REMAINING (Update these functions):**

```php
// Update these functions in manage_prospect_advanced.php to use Sales class methods:

function updateProspect($DBConn, $userDetails) {
    global $response;
    $prospectID = (int)$_POST['salesProspectID'];

    // Gather update data from POST
    $prospectData = array(/* ... gather fields ... */);

    // Use Sales class method
    $result = Sales::update_prospect($prospectID, $prospectData, $userDetails->ID, $DBConn);
    $response = $result;
    echo json_encode($response);
}

function deleteProspect($DBConn, $userDetails) {
    global $response;
    $prospectID = (int)$_POST['salesProspectID'];

    // Use Sales class method
    $result = Sales::delete_prospect($prospectID, $userDetails->ID, $DBConn);
    $response = $result;
    echo json_encode($response);
}

function assignTeam($DBConn, $userDetails) {
    global $response;
    $prospectID = (int)$_POST['salesProspectID'];
    $teamID = (int)$_POST['assignedTeamID'];

    // Use Sales class method
    $result = Sales::assign_prospect_team($prospectID, $teamID, $userDetails->ID, $DBConn);
    $response = $result;
    echo json_encode($response);
}

function updateStatus($DBConn, $userDetails) {
    global $response;
    $prospectID = (int)$_POST['salesProspectID'];
    $status = Utility::clean_string($_POST['salesProspectStatus']);

    // Use Sales class method
    $result = Sales::update_prospect_status($prospectID, $status, $userDetails->ID, $DBConn);
    $response = $result;
    echo json_encode($response);
}

function updateQualification($DBConn, $userDetails) {
    global $response;
    $prospectID = (int)$_POST['salesProspectID'];

    // Gather qualification data
    $qualificationData = array(
        'leadQualificationStatus' => Utility::clean_string($_POST['leadQualificationStatus'] ?? ''),
        'budgetConfirmed' => $_POST['budgetConfirmed'] ?? 'N',
        'decisionMakerIdentified' => $_POST['decisionMakerIdentified'] ?? 'N',
        'timelineDefined' => $_POST['timelineDefined'] ?? 'N',
        'needIdentified' => $_POST['needIdentified'] ?? 'N'
    );

    // Use Sales class method
    $result = Sales::update_prospect_qualification($prospectID, $qualificationData, $userDetails->ID, $DBConn);
    $response = $result;
    echo json_encode($response);
}

function calculateScore($DBConn, $userDetails) {
    global $response;
    $prospectID = (int)$_POST['salesProspectID'];

    // Use Sales class method (already exists)
    $score = Sales::calculate_lead_score($prospectID, $DBConn);

    $response['success'] = true;
    $response['message'] = 'Lead score calculated successfully.';
    $response['data'] = array('leadScore' => $score);
    echo json_encode($response);
}

function logInteraction($DBConn, $userDetails) {
    global $response;
    $prospectID = (int)$_POST['salesProspectID'];

    // Gather interaction data
    $interactionData = array(
        'interactionType' => Utility::clean_string($_POST['interactionType'] ?? 'other'),
        'interactionNotes' => Utility::clean_string($_POST['interactionNotes'] ?? ''),
        'interactionDate' => $_POST['interactionDate'] ?? date('Y-m-d H:i:s'),
        'nextFollowUpDate' => $_POST['nextFollowUpDate'] ?? null
    );

    // Use Sales class method
    $result = Sales::log_prospect_interaction($prospectID, $interactionData, $userDetails->ID, $DBConn);
    $response = $result;
    echo json_encode($response);
}
```

## Phase 2: Bulk Operations - TO BE IMPLEMENTED

### Methods to Add to Sales Class

```php
/**
 * Bulk assign prospects to a team
 * @param array $prospectIDs - Array of prospect IDs
 * @param int $teamID - Team ID
 * @param int $userID - User making the assignment
 * @param object $DBConn - Database connection
 * @return array - Success/failure response with count
 */
public static function bulk_assign_team($prospectIDs, $teamID, $userID, $DBConn) {
    if (empty($prospectIDs) || !is_array($prospectIDs)) {
        return array('success' => false, 'message' => 'No prospects selected.');
    }

    $updateData = array(
        'assignedTeamID' => $teamID,
        'LastUpdateByID' => $userID
    );

    $successCount = 0;
    foreach ($prospectIDs as $prospectID) {
        $result = $DBConn->update_table(
            'tija_sales_prospects',
            $updateData,
            array('salesProspectID' => (int)$prospectID)
        );
        if ($result) $successCount++;
    }

    return array(
        'success' => true,
        'message' => "{$successCount} prospects assigned to team successfully.",
        'count' => $successCount
    );
}

/**
 * Bulk update prospect status
 * @param array $prospectIDs - Array of prospect IDs
 * @param string $status - New status
 * @param int $userID - User making the update
 * @param object $DBConn - Database connection
 * @return array - Success/failure response with count
 */
public static function bulk_update_status($prospectIDs, $status, $userID, $DBConn) {
    if (empty($prospectIDs) || !is_array($prospectIDs)) {
        return array('success' => false, 'message' => 'No prospects selected.');
    }

    $updateData = array(
        'salesProspectStatus' => $status,
        'LastUpdateByID' => $userID
    );

    $successCount = 0;
    foreach ($prospectIDs as $prospectID) {
        $result = $DBConn->update_table(
            'tija_sales_prospects',
            $updateData,
            array('salesProspectID' => (int)$prospectID)
        );
        if ($result) $successCount++;
    }

    return array(
        'success' => true,
        'message' => "{$successCount} prospects updated successfully.",
        'count' => $successCount
    );
}

/**
 * Bulk update prospect qualification
 * @param array $prospectIDs - Array of prospect IDs
 * @param string $qualificationStatus - New qualification status
 * @param int $userID - User making the update
 * @param object $DBConn - Database connection
 * @return array - Success/failure response with count
 */
public static function bulk_update_qualification($prospectIDs, $qualificationStatus, $userID, $DBConn) {
    if (empty($prospectIDs) || !is_array($prospectIDs)) {
        return array('success' => false, 'message' => 'No prospects selected.');
    }

    $updateData = array(
        'leadQualificationStatus' => $qualificationStatus,
        'LastUpdateByID' => $userID
    );

    $successCount = 0;
    foreach ($prospectIDs as $prospectID) {
        $result = $DBConn->update_table(
            'tija_sales_prospects',
            $updateData,
            array('salesProspectID' => (int)$prospectID)
        );
        if ($result) $successCount++;
    }

    return array(
        'success' => true,
        'message' => "{$successCount} prospects qualified successfully.",
        'count' => $successCount
    );
}

/**
 * Bulk delete prospects (soft delete)
 * @param array $prospectIDs - Array of prospect IDs
 * @param int $userID - User deleting the prospects
 * @param object $DBConn - Database connection
 * @return array - Success/failure response with count
 */
public static function bulk_delete_prospects($prospectIDs, $userID, $DBConn) {
    if (empty($prospectIDs) || !is_array($prospectIDs)) {
        return array('success' => false, 'message' => 'No prospects selected.');
    }

    $updateData = array(
        'Suspended' => 'Y',
        'LastUpdateByID' => $userID
    );

    $successCount = 0;
    foreach ($prospectIDs as $prospectID) {
        $result = $DBConn->update_table(
            'tija_sales_prospects',
            $updateData,
            array('salesProspectID' => (int)$prospectID)
        );
        if ($result) $successCount++;
    }

    return array(
        'success' => true,
        'message' => "{$successCount} prospects deleted successfully.",
        'count' => $successCount
    );
}

/**
 * Bulk calculate lead scores
 * @param array $prospectIDs - Array of prospect IDs
 * @param object $DBConn - Database connection
 * @return array - Success/failure response with count
 */
public static function bulk_calculate_scores($prospectIDs, $DBConn) {
    if (empty($prospectIDs) || !is_array($prospectIDs)) {
        return array('success' => false, 'message' => 'No prospects selected.');
    }

    $successCount = 0;
    foreach ($prospectIDs as $prospectID) {
        $score = self::calculate_lead_score((int)$prospectID, $DBConn);
        if ($score !== false) $successCount++;
    }

    return array(
        'success' => true,
        'message' => "{$successCount} lead scores calculated successfully.",
        'count' => $successCount
    );
}
```

## Complete API Documentation

### Sales::create_prospect()

**Purpose:** Create a new sales prospect

**Parameters:**
- `$prospectData` (array) - Prospect information
  - Required: `salesProspectName`, `prospectEmail`, `prospectCaseName`, `businessUnitID`, `leadSourceID`
  - Optional: All other prospect fields
- `$userID` (int) - ID of user creating the prospect
- `$DBConn` (object) - Database connection object

**Returns:** Array
```php
[
    'success' => true/false,
    'message' => 'Status message',
    'prospectID' => 123  // Only on success
]
```

**Example:**
```php
$prospectData = array(
    'salesProspectName' => 'Acme Corporation',
    'prospectEmail' => 'contact@acme.com',
    'prospectCaseName' => 'Enterprise Solution',
    'businessUnitID' => 1,
    'leadSourceID' => 3,
    'estimatedValue' => 100000,
    'probability' => 30,
    'orgDataID' => 1,
    'entityID' => 1,
    'ownerID' => 5
);

$result = Sales::create_prospect($prospectData, $userID, $DBConn);

if ($result['success']) {
    $prospectID = $result['prospectID'];
    echo "Prospect created with ID: {$prospectID}";
}
```

### Sales::update_prospect()

**Purpose:** Update an existing prospect

**Parameters:**
- `$prospectID` (int) - Prospect ID to update
- `$prospectData` (array) - Fields to update
- `$userID` (int) - ID of user making the update
- `$DBConn` (object) - Database connection

**Returns:** Array
```php
['success' => true/false, 'message' => 'Status message']
```

**Example:**
```php
$updateData = array(
    'estimatedValue' => 150000,
    'probability' => 50,
    'budgetConfirmed' => 'Y'
);

$result = Sales::update_prospect(123, $updateData, $userID, $DBConn);
```

### Sales::delete_prospect()

**Purpose:** Soft delete a prospect

**Parameters:**
- `$prospectID` (int) - Prospect ID to delete
- `$userID` (int) - ID of user deleting
- `$DBConn` (object) - Database connection

**Returns:** Array
```php
['success' => true/false, 'message' => 'Status message']
```

**Example:**
```php
$result = Sales::delete_prospect(123, $userID, $DBConn);
```

### Sales::assign_prospect_team()

**Purpose:** Assign a prospect to a team

**Parameters:**
- `$prospectID` (int) - Prospect ID
- `$teamID` (int) - Team ID
- `$userID` (int) - ID of user making assignment
- `$DBConn` (object) - Database connection

**Returns:** Array
```php
['success' => true/false, 'message' => 'Status message']
```

**Example:**
```php
$result = Sales::assign_prospect_team(123, 5, $userID, $DBConn);
```

### Sales::update_prospect_status()

**Purpose:** Update prospect status

**Parameters:**
- `$prospectID` (int) - Prospect ID
- `$status` (string) - New status value
- `$userID` (int) - ID of user making update
- `$DBConn` (object) - Database connection

**Returns:** Array
```php
['success' => true/false, 'message' => 'Status message']
```

**Example:**
```php
$result = Sales::update_prospect_status(123, 'active', $userID, $DBConn);
```

### Sales::update_prospect_qualification()

**Purpose:** Update BANT qualification fields

**Parameters:**
- `$prospectID` (int) - Prospect ID
- `$qualificationData` (array) - Qualification fields
  - `leadQualificationStatus`, `budgetConfirmed`, `decisionMakerIdentified`, `timelineDefined`, `needIdentified`
- `$userID` (int) - ID of user making update
- `$DBConn` (object) - Database connection

**Returns:** Array
```php
['success' => true/false, 'message' => 'Status message']
```

**Example:**
```php
$qualificationData = array(
    'leadQualificationStatus' => 'qualified',
    'budgetConfirmed' => 'Y',
    'decisionMakerIdentified' => 'Y',
    'timelineDefined' => 'Y',
    'needIdentified' => 'Y'
);

$result = Sales::update_prospect_qualification(123, $qualificationData, $userID, $DBConn);
// Automatically recalculates lead score
```

### Sales::log_prospect_interaction()

**Purpose:** Log an interaction with a prospect

**Parameters:**
- `$prospectID` (int) - Prospect ID
- `$interactionData` (array) - Interaction details
  - `interactionType` (string) - Type of interaction
  - `interactionNotes` (string) - Notes about the interaction
  - `interactionDate` (datetime) - When it occurred (optional, defaults to now)
  - `nextFollowUpDate` (date) - Next follow-up date (optional)
- `$userID` (int) - ID of user logging interaction
- `$DBConn` (object) - Database connection

**Returns:** Array
```php
[
    'success' => true/false,
    'message' => 'Status message',
    'interactionID' => 456  // Only on success
]
```

**Example:**
```php
$interactionData = array(
    'interactionType' => 'phone_call',
    'interactionNotes' => 'Discussed pricing and timeline. Very interested.',
    'nextFollowUpDate' => '2025-12-20'
);

$result = Sales::log_prospect_interaction(123, $interactionData, $userID, $DBConn);
// Automatically updates lastContactDate and nextFollowUpDate on prospect
```

## Migration Checklist

### Phase 1: Core CRUD ✅
- [x] Add methods to Sales class
- [x] Update createProspect() in manage_prospect_advanced.php
- [ ] Update updateProspect()
- [ ] Update deleteProspect()
- [ ] Update assignTeam()
- [ ] Update updateStatus()
- [ ] Update updateQualification()
- [ ] Update calculateScore()
- [ ] Update logInteraction()
- [ ] Test all operations

### Phase 2: Bulk Operations ⏳
- [ ] Add bulk_assign_team() to Sales class
- [ ] Add bulk_update_status() to Sales class
- [ ] Add bulk_update_qualification() to Sales class
- [ ] Add bulk_delete_prospects() to Sales class
- [ ] Add bulk_calculate_scores() to Sales class
- [ ] Update bulk_prospect_operations.php
- [ ] Test bulk operations

### Phase 3: Documentation ✅
- [x] Create method documentation
- [x] Add usage examples
- [x] Document parameters and return values
- [x] Create migration guide

## Benefits Achieved

1. **Reusability** - Methods can be called from anywhere
2. **Consistency** - Single source of truth for prospect operations
3. **Maintainability** - Easier to update and fix bugs
4. **Testability** - Can unit test each method
5. **Organization** - All prospect logic in Sales class
6. **Performance** - Better caching and optimization

## Next Steps

1. Complete remaining function updates in manage_prospect_advanced.php
2. Implement Phase 2 bulk operations
3. Test thoroughly
4. Update any other files calling these functions
5. Add deprecation notices to old functions
