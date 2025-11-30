# Task Processing Modes Documentation

## Overview
The TIJA Operational Work system supports three processing modes for scheduled tasks, providing flexibility for different organizational needs and technical environments.

## Processing Modes

### 1. Cron Mode (`cron`)
**Description**: Tasks are automatically processed by server cron jobs at scheduled intervals.

**Best For**:
- Organizations with server access and cron capability
- Tasks that must run at specific times
- High-volume, routine tasks
- Production environments

**How It Works**:
1. Cron job runs the processor script at configured intervals (e.g., hourly)
2. Script evaluates all active templates with `processingMode = 'cron'` or `'both'`
3. Creates task instances automatically when due
4. Logs results to log file

**Setup Required**:
- Cron job configuration (see `CRON_JOB_SETUP_GUIDE.md`)
- Server access for cron setup

### 2. Manual Mode (`manual`)
**Description**: Users receive notifications on login and manually activate scheduled tasks.

**Best For**:
- Organizations without server cron access
- Tasks requiring user review before starting
- Shared hosting environments
- Tasks that benefit from human oversight

**How It Works**:
1. On user login, system checks for pending scheduled tasks
2. Creates notifications in `tija_operational_task_notifications` table
3. User sees notification badge/alert
4. User clicks "Process Now" to create task instance
5. Task is created immediately when activated

**Setup Required**:
- No server configuration needed
- Login hook is automatic (already implemented)

### 3. Both Mode (`both`)
**Description**: Combines automatic and manual processing - tasks are processed by cron, but users also receive notifications.

**Best For**:
- Organizations wanting redundancy
- Tasks where both automatic and manual options are desired
- Transition periods when moving between modes

**How It Works**:
1. Cron job processes tasks automatically (if configured)
2. Users also receive notifications for pending tasks
3. If cron hasn't run, users can manually activate
4. Provides fallback if cron fails

## Configuration

### Setting Processing Mode

#### Via Database
```sql
UPDATE tija_operational_task_templates
SET processingMode = 'cron'  -- or 'manual' or 'both'
WHERE templateID = [template_id];
```

#### Via Admin Interface (Future)
- Navigate to Template Management
- Edit template
- Select Processing Mode
- Save

## Manual Processing Workflow

### Step 1: User Login
- System automatically checks for pending tasks
- Creates notifications for due tasks
- Stores count in session: `$_SESSION['pendingOperationalTasks']`

### Step 2: Notification Display
- Notification widget appears on dashboard
- Shows list of pending scheduled tasks
- Displays task name and due date
- "Process Now" button for each task

### Step 3: User Action
- User clicks "Process Now" button
- AJAX request to `process_pending_task.php`
- Task instance is created immediately
- Notification is marked as processed
- Success message displayed

### Step 4: Task Created
- Task appears in user's task list
- User can now work on the task
- Notification is removed from list

## API Endpoints

### Get Pending Notifications
**Endpoint**: `php/scripts/operational/tasks/get_pending_notifications.php`
**Method**: GET
**Response**:
```json
{
    "success": true,
    "notifications": [
        {
            "notificationID": 1,
            "templateID": 5,
            "templateName": "Month-End Close",
            "dueDate": "2025-01-31",
            "notificationType": "scheduled_task_ready",
            "status": "pending"
        }
    ],
    "count": 1
}
```

### Process Pending Task
**Endpoint**: `php/scripts/operational/tasks/process_pending_task.php`
**Method**: POST
**Parameters**:
- `notificationID` (required) - Notification ID
- `action` (required) - 'process' or 'dismiss'

**Response**:
```json
{
    "success": true,
    "message": "Task created successfully",
    "taskID": 123
}
```

## Database Schema

### Template Processing Mode
**Table**: `tija_operational_task_templates`
**Field**: `processingMode`
**Type**: ENUM('cron','manual','both')
**Default**: 'cron'

### Notifications Table
**Table**: `tija_operational_task_notifications`
**Fields**:
- `notificationID` - Primary key
- `templateID` - FK to template
- `employeeID` - FK to user
- `dueDate` - Task due date
- `notificationType` - Type of notification
- `status` - pending/sent/acknowledged/processed/dismissed
- `taskInstanceID` - FK to created task (when processed)

## Notification Types

1. **scheduled_task_ready** - Scheduled task is due and ready to be activated
2. **task_overdue** - Task is overdue (future enhancement)
3. **task_due_soon** - Task is due soon (future enhancement)

## User Experience

### Notification Display
- Appears on dashboard after login
- Shows up to 5 tasks initially
- "View all" link if more than 5
- Dismissible alert
- Real-time updates via AJAX

### Processing Action
- One-click activation
- Immediate feedback
- Success/error messages
- Automatic list update
- Toast notifications (if SweetAlert available)

## Best Practices

### Choosing a Mode

**Use Cron Mode When**:
- You have server access
- Tasks are routine and don't need review
- You want fully automated processing
- Tasks must run at exact times

**Use Manual Mode When**:
- No server cron access
- Tasks need user review before starting
- You want user control over task creation
- Tasks are infrequent or critical

**Use Both Mode When**:
- You want redundancy
- Some users prefer manual, others automatic
- Transitioning between modes
- Critical tasks that need backup

### Recommendations

1. **Start with Manual Mode**: Easier to set up, no server configuration
2. **Move to Cron Mode**: When you have many tasks or need automation
3. **Use Both Mode**: For critical tasks requiring redundancy
4. **Monitor Performance**: Check which mode works best for your organization

## Troubleshooting

### Manual Mode Not Working

**Issue**: Notifications not appearing
- **Check**: User is assigned to template
- **Check**: Template `processingMode` is 'manual' or 'both'
- **Check**: Template `isActive` = 'Y'
- **Check**: Task is actually due (due date <= today)
- **Check**: Login hook is executing (check session variable)

**Issue**: "Process Now" button not working
- **Check**: JavaScript console for errors
- **Check**: API endpoint is accessible
- **Check**: User has permission
- **Check**: Notification belongs to current user

### Cron Mode Not Working

**Issue**: Tasks not being created
- **Check**: Cron job is running (see `CRON_JOB_SETUP_GUIDE.md`)
- **Check**: Log file for errors
- **Check**: Template `processingMode` is 'cron' or 'both'
- **Check**: Template `isActive` = 'Y'
- **Check**: Database connection in cron context

## Migration Between Modes

### From Manual to Cron

1. Set up cron job (see `CRON_JOB_SETUP_GUIDE.md`)
2. Test cron execution
3. Update templates: `processingMode = 'cron'`
4. Monitor for a few days
5. Remove manual notifications if desired

### From Cron to Manual

1. Update templates: `processingMode = 'manual'`
2. Stop/disable cron job
3. Users will start receiving notifications on login
4. Monitor user adoption

### To Both Mode

1. Ensure cron job is running
2. Update templates: `processingMode = 'both'`
3. Both automatic and manual processing will work
4. Users can manually activate if cron hasn't run

## Security Considerations

1. **Notification Access**: Users can only see their own notifications
2. **Task Creation**: Users can only process notifications assigned to them
3. **API Authentication**: All endpoints require valid user session
4. **Database Permissions**: Proper foreign key constraints prevent orphaned records

## Performance

### Manual Mode
- **Login Impact**: Minimal - single query on login
- **Notification Load**: Lightweight - only checks assigned templates
- **User Experience**: No noticeable delay

### Cron Mode
- **Server Load**: Minimal - runs at scheduled intervals
- **Database Impact**: Efficient queries with indexes
- **Scalability**: Handles thousands of templates

## Future Enhancements

1. **Email Notifications**: Send email when tasks are ready
2. **SMS Notifications**: SMS alerts for critical tasks
3. **Bulk Processing**: Process multiple tasks at once
4. **Scheduling Preferences**: User preferences for notification timing
5. **Mobile App Integration**: Push notifications for mobile users

