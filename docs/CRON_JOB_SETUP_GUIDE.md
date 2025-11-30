# Cron Job Setup Guide for Operational Tasks

## Overview
This guide provides instructions for setting up automated processing of scheduled operational tasks using cron jobs on both Windows and Linux servers.

## Processing Modes

The TIJA Operational Work system supports three processing modes:

1. **Cron Mode** (`cron`) - Tasks are automatically processed by cron jobs
2. **Manual Mode** (`manual`) - Users receive notifications on login and manually activate tasks
3. **Both Mode** (`both`) - Combines both automatic and manual processing

## Cron Script Location

The cron script is located at:
```
php/scripts/cron/process_operational_tasks.php
```

## Linux/Unix Cron Setup

### Method 1: Using crontab (Recommended)

1. **Open crontab editor**:
   ```bash
   crontab -e
   ```

2. **Add cron job** (runs every hour):
   ```cron
   0 * * * * /usr/bin/php /path/to/your/project/php/scripts/cron/process_operational_tasks.php >> /path/to/your/project/php/logs/operational_tasks_cron.log 2>&1
   ```

3. **Alternative schedules**:

   **Every 30 minutes**:
   ```cron
   */30 * * * * /usr/bin/php /path/to/your/project/php/scripts/cron/process_operational_tasks.php >> /path/to/your/project/php/logs/operational_tasks_cron.log 2>&1
   ```

   **Every 15 minutes**:
   ```cron
   */15 * * * * /usr/bin/php /path/to/your/project/php/scripts/cron/process_operational_tasks.php >> /path/to/your/project/php/logs/operational_tasks_cron.log 2>&1
   ```

   **Daily at 8:00 AM**:
   ```cron
   0 8 * * * /usr/bin/php /path/to/your/project/php/scripts/cron/process_operational_tasks.php >> /path/to/your/project/php/logs/operational_tasks_cron.log 2>&1
   ```

4. **Verify cron job is added**:
   ```bash
   crontab -l
   ```

5. **Check cron service is running**:
   ```bash
   sudo systemctl status cron    # Ubuntu/Debian
   sudo systemctl status crond   # CentOS/RHEL
   ```

### Method 2: Using System Cron (System-wide)

1. **Create cron file**:
   ```bash
   sudo nano /etc/cron.d/tija-operational-tasks
   ```

2. **Add cron job**:
   ```cron
   # TIJA Operational Tasks Processor
   # Runs every hour
   0 * * * * www-data /usr/bin/php /path/to/your/project/php/scripts/cron/process_operational_tasks.php >> /path/to/your/project/php/logs/operational_tasks_cron.log 2>&1
   ```

3. **Set proper permissions**:
   ```bash
   sudo chmod 644 /etc/cron.d/tija-operational-tasks
   ```

### Method 3: Using systemd Timer (Modern Linux)

1. **Create service file** (`/etc/systemd/system/tija-operational-tasks.service`):
   ```ini
   [Unit]
   Description=TIJA Operational Tasks Processor
   After=network.target

   [Service]
   Type=oneshot
   User=www-data
   ExecStart=/usr/bin/php /path/to/your/project/php/scripts/cron/process_operational_tasks.php
   StandardOutput=append:/path/to/your/project/php/logs/operational_tasks_cron.log
   StandardError=append:/path/to/your/project/php/logs/operational_tasks_cron.log
   ```

2. **Create timer file** (`/etc/systemd/system/tija-operational-tasks.timer`):
   ```ini
   [Unit]
   Description=TIJA Operational Tasks Timer
   Requires=tija-operational-tasks.service

   [Timer]
   OnCalendar=hourly
   Persistent=true

   [Install]
   WantedBy=timers.target
   ```

3. **Enable and start timer**:
   ```bash
   sudo systemctl daemon-reload
   sudo systemctl enable tija-operational-tasks.timer
   sudo systemctl start tija-operational-tasks.timer
   ```

4. **Check timer status**:
   ```bash
   sudo systemctl status tija-operational-tasks.timer
   ```

## Windows Task Scheduler Setup

### Method 1: Using Task Scheduler GUI

1. **Open Task Scheduler**:
   - Press `Win + R`, type `taskschd.msc`, press Enter

2. **Create Basic Task**:
   - Click "Create Basic Task" in the right panel
   - Name: `TIJA Operational Tasks Processor`
   - Description: `Processes scheduled operational tasks`

3. **Set Trigger**:
   - Trigger: "Daily" or "When the computer starts"
   - For hourly: Choose "Daily", then set to repeat every 1 hour

4. **Set Action**:
   - Action: "Start a program"
   - Program/script: `C:\php\php.exe` (or your PHP path)
   - Add arguments: `C:\wamp64\www\sbsl.tija.sbsl.co.ke\php\scripts\cron\process_operational_tasks.php`
   - Start in: `C:\wamp64\www\sbsl.tija.sbsl.co.ke\php\scripts\cron`

5. **Finish**:
   - Review settings and click "Finish"

### Method 2: Using PowerShell (Advanced)

1. **Open PowerShell as Administrator**

2. **Create scheduled task**:
   ```powershell
   $action = New-ScheduledTaskAction -Execute "C:\php\php.exe" -Argument "C:\wamp64\www\sbsl.tija.sbsl.co.ke\php\scripts\cron\process_operational_tasks.php" -WorkingDirectory "C:\wamp64\www\sbsl.tija.sbsl.co.ke\php\scripts\cron"

   $trigger = New-ScheduledTaskTrigger -Once -At (Get-Date) -RepetitionInterval (New-TimeSpan -Hours 1) -RepetitionDuration (New-TimeSpan -Days 365)

   Register-ScheduledTask -TaskName "TIJA Operational Tasks" -Action $action -Trigger $trigger -Description "Processes scheduled operational tasks" -User "SYSTEM" -RunLevel Highest
   ```

### Method 3: Using schtasks Command

1. **Open Command Prompt as Administrator**

2. **Create scheduled task** (runs every hour):
   ```cmd
   schtasks /create /tn "TIJA Operational Tasks" /tr "C:\php\php.exe C:\wamp64\www\sbsl.tija.sbsl.co.ke\php\scripts\cron\process_operational_tasks.php" /sc hourly /mo 1 /ru SYSTEM
   ```

3. **Verify task**:
   ```cmd
   schtasks /query /tn "TIJA Operational Tasks"
   ```

## Manual Processing Mode (Alternative to Cron)

If cron jobs are not available or preferred, you can use the manual processing mode:

### How It Works

1. **Template Configuration**:
   - Set `processingMode` to `'manual'` or `'both'` in the template
   - Tasks will not be automatically created

2. **User Login**:
   - When users log in, the system checks for pending scheduled tasks
   - Notifications are created for tasks that are due

3. **User Notification**:
   - Users see a notification badge/alert on login
   - Notification shows pending scheduled tasks

4. **Manual Activation**:
   - Users can click "Process Now" to create the task instance
   - Task is created immediately when user activates it

### Benefits of Manual Mode

- No server cron configuration needed
- Users have control over when tasks are created
- Works on any hosting environment
- Better for tasks that require user review before starting

### Setting Up Manual Mode

1. **Update Template Processing Mode**:
   ```sql
   UPDATE tija_operational_task_templates
   SET processingMode = 'manual'
   WHERE templateID = [template_id];
   ```

2. **Login Hook** (Already implemented):
   - The system automatically checks for pending tasks on login
   - Notifications are created in `tija_operational_task_notifications` table

3. **User Interface**:
   - Users will see notifications in their dashboard
   - API endpoint: `php/scripts/operational/tasks/get_pending_notifications.php`
   - Process endpoint: `php/scripts/operational/tasks/process_pending_task.php`

## Testing Cron Jobs

### Linux/Unix

1. **Test script manually**:
   ```bash
   php /path/to/your/project/php/scripts/cron/process_operational_tasks.php
   ```

2. **Check log file**:
   ```bash
   tail -f /path/to/your/project/php/logs/operational_tasks_cron.log
   ```

3. **Verify cron execution**:
   ```bash
   grep CRON /var/log/syslog | grep operational_tasks
   ```

### Windows

1. **Test script manually**:
   ```cmd
   C:\php\php.exe C:\wamp64\www\sbsl.tija.sbsl.co.ke\php\scripts\cron\process_operational_tasks.php
   ```

2. **Check Task Scheduler history**:
   - Open Task Scheduler
   - Find "TIJA Operational Tasks"
   - Click "History" tab to see execution logs

3. **Check log file**:
   ```
   C:\wamp64\www\sbsl.tija.sbsl.co.ke\php\logs\operational_tasks_cron.log
   ```

## Troubleshooting

### Common Issues

1. **Script not executing**:
   - Check PHP path is correct
   - Verify file permissions (should be executable)
   - Check PHP error logs

2. **Database connection errors**:
   - Verify database credentials in `php/config/config.inc.php`
   - Check database server is running
   - Verify network connectivity

3. **No tasks being created**:
   - Check template `isActive` = 'Y'
   - Verify `processingMode` is 'cron' or 'both'
   - Check template frequency settings
   - Review log file for errors

4. **Permission errors**:
   - Ensure cron user has read/write access to log directory
   - Check file permissions on script files
   - Verify database user has necessary permissions

### Log File Location

Logs are written to:
```
php/logs/operational_tasks_cron.log
```

Ensure this directory exists and is writable:
```bash
mkdir -p php/logs
chmod 755 php/logs
```

## Recommended Schedule

- **High-frequency tasks** (daily): Run every hour
- **Medium-frequency tasks** (weekly): Run daily at 8:00 AM
- **Low-frequency tasks** (monthly/quarterly): Run daily at 8:00 AM

## Security Considerations

1. **File Permissions**:
   - Script should be readable only by web server user
   - Log files should be writable but not world-readable

2. **Database Access**:
   - Use dedicated database user with minimal required permissions
   - Don't use root database user

3. **Error Handling**:
   - Errors are logged, not displayed
   - Sensitive information is not logged

4. **Execution Context**:
   - Script runs in CLI mode (not web context)
   - No user input is processed

## Monitoring

### Check Last Execution

Query the log file for last execution:
```bash
tail -n 20 php/logs/operational_tasks_cron.log
```

### Database Query

Check when tasks were last created:
```sql
SELECT MAX(DateAdded) as last_task_created
FROM tija_operational_tasks;
```

### Health Check Script

Create a health check endpoint:
```php
// php/scripts/cron/health_check.php
// Returns JSON with last execution time and status
```

## Best Practices

1. **Test First**: Always test the script manually before setting up cron
2. **Monitor Logs**: Regularly check log files for errors
3. **Start Conservative**: Begin with less frequent execution, increase if needed
4. **Document Changes**: Keep track of cron job changes
5. **Backup**: Ensure database backups are in place
6. **Alerting**: Set up alerts for cron job failures (optional)

## Support

For issues or questions:
1. Check log files first
2. Verify cron job is running
3. Test script manually
4. Review template configuration
5. Contact system administrator

