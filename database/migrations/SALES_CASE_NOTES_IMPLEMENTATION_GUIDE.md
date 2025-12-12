# Sales Case Notes and Next Steps - Implementation Guide

## Overview
Complete database and class implementation for managing sales case notes and next steps with privacy controls, recipient management, and comprehensive tracking.

## Database Tables Created

### 1. tija_sales_case_notes
**Purpose:** Store notes related to sales cases with privacy controls

**Key Fields:**
- `salesCaseNoteID` - Primary key
- `salesCaseID` - Foreign key to sales case
- `saleStatusLevelID` - Sales stage when note was created
- `noteText` - Content of the note
- `noteType` - ENUM('general', 'private')
- `isPrivate` - Y/N quick check
- `createdByID` - User who created the note
- `targetUserID` - If private, the specific user it's for
- Standard tracking fields (DateAdded, LastUpdate, etc.)

**Features:**
- General notes visible to all team members
- Private notes visible only to creator, target user, and specified recipients
- Tracks which sales stage the note relates to
- Soft delete support (Suspended field)

### 2. tija_sales_case_next_steps
**Purpose:** Track action items and next steps for sales cases

**Key Fields:**
- `salesCaseNextStepID` - Primary key
- `salesCaseID` - Foreign key to sales case
- `saleStatusLevelID` - Sales stage when step was created
- `nextStepDescription` - What needs to be done
- `dueDate` - When it should be completed
- `priority` - ENUM('low', 'medium', 'high', 'urgent')
- `status` - ENUM('pending', 'in_progress', 'completed', 'cancelled')
- `assignedToID` - User responsible for the step
- `completedDate` - When it was completed
- `completedByID` - Who completed it
- `createdByID` - Who created the step

**Features:**
- Priority levels for importance
- Status tracking through workflow
- Assignment to team members
- Due date tracking
- Completion tracking with timestamp and user

### 3. tija_sales_case_note_recipients
**Purpose:** Link private notes to multiple recipients

**Key Fields:**
- `salesCaseNoteRecipientID` - Primary key
- `salesCaseNoteID` - Foreign key to note
- `recipientUserID` - User who can view the note
- `hasRead` - Y/N tracking if note has been read
- `readDate` - When it was read

**Features:**
- Many-to-many relationship (one note, many recipients)
- Read status tracking
- Unique constraint prevents duplicate recipients

## Class Methods Added to Sales Class

### Sales Case Notes Methods

#### 1. `Sales::sales_case_notes($whereArr, $single, $DBConn)`
**Purpose:** Basic retrieval of notes
**Returns:** Note object(s) or false
**Usage:**
```php
$notes = Sales::sales_case_notes(array('salesCaseID' => 123), false, $DBConn);
```

#### 2. `Sales::sales_case_notes_full($salesCaseID, $userID, $DBConn)`
**Purpose:** Get notes with full details and privacy filtering
**Features:**
- Joins user names, stage information
- Filters based on privacy (user only sees notes they have access to)
- Includes recipient information for private notes
- Ordered by date (newest first)

**Privacy Logic:**
User can see a note if:
- It's a general note, OR
- They created it, OR
- They are the target user, OR
- They are listed as a recipient

**Usage:**
```php
$notes = Sales::sales_case_notes_full(123, $userID, $DBConn);
foreach ($notes as $note) {
    echo $note->noteText;
    echo $note->addedByName;
    if ($note->isPrivate === 'Y') {
        print_r($note->recipients);
    }
}
```

#### 3. `Sales::get_note_recipients($salesCaseNoteID, $DBConn)`
**Purpose:** Get all recipients for a private note
**Returns:** Array of recipient objects with names
**Usage:**
```php
$recipients = Sales::get_note_recipients(456, $DBConn);
```

#### 4. `Sales::add_sales_case_note($noteData, $recipients, $DBConn)`
**Purpose:** Create a new note with optional recipients
**Parameters:**
- `$noteData` - Array of note fields
- `$recipients` - Array of user IDs (for private notes)
- `$DBConn` - Database connection

**Usage:**
```php
$noteData = array(
    'salesCaseID' => 123,
    'saleStatusLevelID' => 2,
    'noteText' => 'Client requested pricing adjustment',
    'noteType' => 'private',
    'isPrivate' => 'Y',
    'createdByID' => $userID
);
$recipients = array(5, 7, 9); // User IDs
$result = Sales::add_sales_case_note($noteData, $recipients, $DBConn);
```

#### 5. `Sales::delete_sales_case_note($noteID, $userID, $DBConn)`
**Purpose:** Soft delete a note (only creator can delete)
**Security:** Verifies user is the creator before deleting
**Usage:**
```php
$result = Sales::delete_sales_case_note(456, $userID, $DBConn);
```

### Sales Case Next Steps Methods

#### 1. `Sales::sales_case_next_steps($whereArr, $single, $DBConn)`
**Purpose:** Basic retrieval of next steps
**Returns:** Next step object(s) or false
**Usage:**
```php
$steps = Sales::sales_case_next_steps(array('salesCaseID' => 123), false, $DBConn);
```

#### 2. `Sales::sales_case_next_steps_full($salesCaseID, $DBConn)`
**Purpose:** Get next steps with full details
**Features:**
- Joins user names (creator, assigned, completed by)
- Includes stage information
- Smart ordering: by status, then priority, then due date

**Ordering Logic:**
1. Status: pending → in_progress → completed → cancelled
2. Priority: urgent → high → medium → low
3. Due date: earliest first

**Usage:**
```php
$steps = Sales::sales_case_next_steps_full(123, $DBConn);
foreach ($steps as $step) {
    echo $step->nextStepDescription;
    echo $step->assignedToName;
    echo $step->priority;
    echo $step->status;
}
```

#### 3. `Sales::add_sales_case_next_step($stepData, $DBConn)`
**Purpose:** Create a new next step
**Usage:**
```php
$stepData = array(
    'salesCaseID' => 123,
    'saleStatusLevelID' => 2,
    'nextStepDescription' => 'Send proposal to client',
    'dueDate' => '2025-12-20',
    'priority' => 'high',
    'status' => 'pending',
    'assignedToID' => 5,
    'createdByID' => $userID
);
$result = Sales::add_sales_case_next_step($stepData, $DBConn);
```

#### 4. `Sales::update_next_step_status($stepID, $status, $userID, $DBConn)`
**Purpose:** Update the status of a next step
**Auto-Features:**
- When status = 'completed', automatically sets completedDate and completedByID

**Usage:**
```php
$result = Sales::update_next_step_status(789, 'completed', $userID, $DBConn);
```

#### 5. `Sales::delete_sales_case_next_step($stepID, $userID, $DBConn)`
**Purpose:** Soft delete a next step
**Usage:**
```php
$result = Sales::delete_sales_case_next_step(789, $userID, $DBConn);
```

## Database Relationships

```
tija_sales_cases (1) ──────── (many) tija_sales_case_notes
                                            │
                                            │ (1)
                                            │
                                            └─── (many) tija_sales_case_note_recipients

tija_sales_cases (1) ──────── (many) tija_sales_case_next_steps

tija_sales_status_levels (1) ─── (many) tija_sales_case_notes
                              └─── (many) tija_sales_case_next_steps

sbsl_users ─── creates ──── tija_sales_case_notes
           └─── assigned ─── tija_sales_case_next_steps
           └─── recipient ── tija_sales_case_note_recipients
```

## Usage Examples

### Example 1: Add a General Note
```php
$noteData = array(
    'salesCaseID' => 123,
    'saleStatusLevelID' => 1, // Prospecting stage
    'noteText' => 'Had great conversation with CEO. Very interested in our solution.',
    'noteType' => 'general',
    'isPrivate' => 'N',
    'createdByID' => $userID
);
$result = Sales::add_sales_case_note($noteData, array(), $DBConn);
```

### Example 2: Add a Private Note with Recipients
```php
$noteData = array(
    'salesCaseID' => 123,
    'saleStatusLevelID' => 2,
    'noteText' => 'Client mentioned budget concerns. May need to adjust pricing by 15%.',
    'noteType' => 'private',
    'isPrivate' => 'Y',
    'createdByID' => $userID
);
$recipients = array(5, 7); // Share with sales manager and finance
$result = Sales::add_sales_case_note($noteData, $recipients, $DBConn);
```

### Example 3: Create Next Step with Assignment
```php
$stepData = array(
    'salesCaseID' => 123,
    'saleStatusLevelID' => 3,
    'nextStepDescription' => 'Prepare detailed proposal with pricing breakdown',
    'dueDate' => '2025-12-18',
    'priority' => 'urgent',
    'status' => 'pending',
    'assignedToID' => 8, // Assign to proposal specialist
    'createdByID' => $userID
);
$result = Sales::add_sales_case_next_step($stepData, $DBConn);
```

### Example 4: Mark Next Step as Complete
```php
$stepID = 789;
$result = Sales::update_next_step_status($stepID, 'completed', $userID, $DBConn);
// Automatically sets completedDate and completedByID
```

### Example 5: Get All Notes for a Sales Case (with Privacy)
```php
$salesCaseID = 123;
$notes = Sales::sales_case_notes_full($salesCaseID, $userID, $DBConn);

foreach ($notes as $note) {
    echo "By: " . $note->addedByName . "\n";
    echo "Date: " . $note->DateAdded . "\n";
    echo "Stage: " . $note->stageName . "\n";
    echo "Note: " . $note->noteText . "\n";

    if ($note->isPrivate === 'Y') {
        echo "Private - Recipients: ";
        foreach ($note->recipients as $recipient) {
            echo $recipient->recipientName . ", ";
        }
    }
    echo "\n---\n";
}
```

### Example 6: Get All Next Steps for a Sales Case
```php
$salesCaseID = 123;
$steps = Sales::sales_case_next_steps_full($salesCaseID, $DBConn);

foreach ($steps as $step) {
    echo "Step: " . $step->nextStepDescription . "\n";
    echo "Assigned to: " . $step->assignedToName . "\n";
    echo "Due: " . $step->dueDate . "\n";
    echo "Priority: " . $step->priority . "\n";
    echo "Status: " . $step->status . "\n";

    if ($step->status === 'completed') {
        echo "Completed by: " . $step->completedByName . "\n";
        echo "Completed on: " . $step->completedDate . "\n";
    }
    echo "\n---\n";
}
```

## Migration Instructions

1. **Run the Migration:**
   ```sql
   SOURCE database/migrations/create_sales_case_notes_and_next_steps.sql;
   ```

2. **Verify Tables Created:**
   ```sql
   SHOW TABLES LIKE 'tija_sales_case%';
   ```

3. **Check Table Structure:**
   ```sql
   DESCRIBE tija_sales_case_notes;
   DESCRIBE tija_sales_case_next_steps;
   DESCRIBE tija_sales_case_note_recipients;
   ```

## Security Features

1. **Privacy Controls:**
   - General notes visible to all
   - Private notes filtered by user access
   - Only creator can delete their notes

2. **Audit Trail:**
   - Tracks who created each note/step
   - Tracks who completed each step
   - Timestamps for all actions
   - Soft delete preserves history

3. **Access Control:**
   - `sales_case_notes_full()` automatically filters by user permissions
   - Recipient system for granular access control
   - Read tracking for private notes

## Best Practices

1. **Always use `_full()` methods for display:**
   - They include user names and stage information
   - They handle privacy filtering automatically

2. **For private notes, always specify recipients:**
   - Even if targeting one user, use the recipients array
   - This allows for future expansion

3. **Use appropriate priority levels:**
   - `urgent` - Needs immediate attention
   - `high` - Important, do soon
   - `medium` - Normal priority
   - `low` - Can wait

4. **Update next step status as work progresses:**
   - `pending` → `in_progress` → `completed`
   - Or `cancelled` if no longer needed

5. **Always include stage information:**
   - Helps track context of notes/steps
   - Useful for reporting and analysis

## Files Created/Modified

**Created:**
- `database/migrations/create_sales_case_notes_and_next_steps.sql`

**Modified:**
- `php/classes/sales.php` - Added 10 new methods

## Next Steps

1. Create UI for adding/viewing notes
2. Create UI for managing next steps
3. Add notifications for assigned next steps
4. Add email alerts for overdue next steps
5. Create reporting dashboard for next steps completion
