# Quick Start Guide: Task Processing Modes

## Overview
This guide helps you quickly set up task processing for operational tasks in TIJA.

## Step 1: Run Database Migration

Run the migration to add processing mode support:
```sql
-- Execute: database/migrations/add_task_processing_mode.sql
```

This adds:
- `processingMode` field to templates
- `tija_operational_task_notifications` table

## Step 2: Choose Your Processing Mode

### Option A: Manual Mode (Easiest - No Server Setup)

**Best for**: Shared hosting, quick setup, tasks needing review

1. **Set template to manual mode**:
   ```sql
   UPDATE tija_operational_task_templates
   SET processingMode = 'manual'
   WHERE templateID = [your_template_id];
   ```

2. **That's it!**
   - Users will receive notifications on login
   - They can manually activate tasks
   - No server configuration needed

### Option B: Cron Mode (Automatic)

**Best for**: Production environments, routine tasks, exact timing

1. **Set template to cron mode**:
   ```sql
   UPDATE tija_operational_task_templates
   SET processingMode = 'cron'
   WHERE templateID = [your_template_id];
   ```

2. **Set up cron job** (see `CRON_JOB_SETUP_GUIDE.md`):

   **Linux**:
   ```bash
   crontab -e
   # Add: 0 * * * * /usr/bin/php /path/to/php/scripts/cron/process_operational_tasks.php
   ```

   **Windows**:
   - Use Task Scheduler (see guide for details)

### Option C: Both Mode (Recommended)

**Best for**: Critical tasks, redundancy

1. **Set template to both mode**:
   ```sql
   UPDATE tija_operational_task_templates
   SET processingMode = 'both'
   WHERE templateID = [your_template_id];
   ```

2. **Set up cron job** (optional but recommended)
3. **Users also get notifications** as backup

## Step 3: Test Your Setup

### Test Manual Mode

1. Create a test template with `processingMode = 'manual'`
2. Set due date to today
3. Assign to a test user
4. Log in as that user
5. You should see notification on dashboard
6. Click "Process Now"
7. Task should be created

### Test Cron Mode

1. Create a test template with `processingMode = 'cron'`
2. Set due date to today
3. Run cron script manually:
   ```bash
   php php/scripts/cron/process_operational_tasks.php
   ```
4. Check if task was created
5. Check log file: `php/logs/operational_tasks_cron.log`

## Step 4: Monitor

### Check Notifications (Manual Mode)
- Query: `SELECT * FROM tija_operational_task_notifications WHERE status = 'pending'`
- Or check user dashboard

### Check Cron Execution (Cron Mode)
- Log file: `php/logs/operational_tasks_cron.log`
- Last execution time
- Any errors

## Common Issues

**Notifications not appearing?**
- Check template `processingMode` is 'manual' or 'both'
- Check template `isActive` = 'Y'
- Check user is assigned to template
- Check task is actually due

**Cron not working?**
- Verify cron job is running
- Check PHP path is correct
- Check file permissions
- Review log file for errors

## Next Steps

- Read full documentation: `CRON_JOB_SETUP_GUIDE.md`
- Understand modes: `TASK_PROCESSING_MODES.md`
- Configure templates via admin interface (when available)

