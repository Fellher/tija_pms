# Goals Module - Cron Jobs Setup Guide

## Overview

The Goals Module includes automated cron jobs for daily and weekly maintenance tasks. This guide explains how to set up and configure these cron jobs.

## Available Cron Jobs

### 1. Daily Cron Job (`goals_daily.php`)

**Purpose**: Daily maintenance tasks for goal management

**Tasks Performed**:
- Generate daily snapshots for active goals
- Send reminders for pending evaluations
- Calculate aggregate scores for entities
- Update cascade status for pending cascades
- Check for goals approaching deadline and send alerts

**Recommended Schedule**: Daily at 2:00 AM

**Command**:
```bash
0 2 * * * /usr/bin/php /path/to/php/scripts/cron/goals_daily.php >> /var/log/goals_daily.log 2>&1
```

**Windows Task Scheduler**:
```
Program: C:\wamp64\bin\php\php.exe
Arguments: C:\wamp64\www\sbsl.tija.sbsl.co.ke\php\scripts\cron\goals_daily.php
Start in: C:\wamp64\www\sbsl.tija.sbsl.co.ke
Schedule: Daily at 2:00 AM
```

### 2. Weekly Cron Job (`goals_weekly.php`)

**Purpose**: Weekly maintenance and reporting tasks

**Tasks Performed**:
- Create weekly performance snapshots for data warehouse
- Update currency rates (manual update required - API integration pending)
- Run compliance checks (jurisdiction data retention)
- Generate weekly performance summary
- Archive old snapshots (older than 2 years)

**Recommended Schedule**: Weekly on Sunday at 3:00 AM

**Command**:
```bash
0 3 * * 0 /usr/bin/php /path/to/php/scripts/cron/goals_weekly.php >> /var/log/goals_weekly.log 2>&1
```

**Windows Task Scheduler**:
```
Program: C:\wamp64\bin\php\php.exe
Arguments: C:\wamp64\www\sbsl.tija.sbsl.co.ke\php\scripts\cron\goals_weekly.php
Start in: C:\wamp64\www\sbsl.tija.sbsl.co.ke
Schedule: Weekly on Sunday at 3:00 AM
```

## Setup Instructions

### Linux/Unix Setup

1. **Edit crontab**:
   ```bash
   crontab -e
   ```

2. **Add cron jobs**:
   ```bash
   # Goals Module - Daily Cron Job
   0 2 * * * /usr/bin/php /var/www/sbsl.tija.sbsl.co.ke/php/scripts/cron/goals_daily.php >> /var/log/goals_daily.log 2>&1

   # Goals Module - Weekly Cron Job
   0 3 * * 0 /usr/bin/php /var/www/sbsl.tija.sbsl.co.ke/php/scripts/cron/goals_weekly.php >> /var/log/goals_weekly.log 2>&1
   ```

3. **Verify cron jobs**:
   ```bash
   crontab -l
   ```

4. **Check logs**:
   ```bash
   tail -f /var/log/goals_daily.log
   tail -f /var/log/goals_weekly.log
   ```

### Windows Setup (WAMP)

1. **Open Task Scheduler**:
   - Press `Win + R`, type `taskschd.msc`, press Enter

2. **Create Daily Task**:
   - Click "Create Basic Task"
   - Name: "Goals Daily Automation"
   - Trigger: Daily at 2:00 AM
   - Action: Start a program
   - Program: `C:\wamp64\bin\php\php.exe`
   - Arguments: `C:\wamp64\www\sbsl.tija.sbsl.co.ke\php\scripts\cron\goals_daily.php`
   - Start in: `C:\wamp64\www\sbsl.tija.sbsl.co.ke`

3. **Create Weekly Task**:
   - Click "Create Basic Task"
   - Name: "Goals Weekly Automation"
   - Trigger: Weekly on Sunday at 3:00 AM
   - Action: Start a program
   - Program: `C:\wamp64\bin\php\php.exe`
   - Arguments: `C:\wamp64\www\sbsl.tija.sbsl.co.ke\php\scripts\cron\goals_weekly.php`
   - Start in: `C:\wamp64\www\sbsl.tija.sbsl.co.ke`

### Manual Execution

You can also run these scripts manually for testing:

**Daily Job**:
```bash
php php/scripts/cron/goals_daily.php
```

**Weekly Job**:
```bash
php php/scripts/cron/goals_weekly.php
```

## Automation Settings

Users can control automation preferences through the Goals Module interface:

1. Navigate to **Goals → Settings → Automation**
2. Configure preferences for:
   - Score calculation (automatic/manual/scheduled)
   - Snapshot generation (automatic/manual/scheduled)
   - Evaluation reminders (automatic/manual/scheduled)
   - Deadline alerts (automatic/manual/scheduled)
   - Cascade updates (automatic/manual/scheduled)

### Execution Modes

- **Automatic**: Runs via cron jobs automatically
- **Manual**: User triggers manually through UI
- **Scheduled**: Runs at user-specified time/frequency

## Notification Preferences

Users can choose how to receive notifications:
- **Email**: Email notifications
- **In-App**: In-application notifications
- **Both**: Email and in-app
- **None**: No notifications

## Troubleshooting

### Cron Job Not Running

1. **Check PHP path**:
   ```bash
   which php
   # or
   /usr/bin/php -v
   ```

2. **Check file permissions**:
   ```bash
   chmod +x php/scripts/cron/goals_daily.php
   chmod +x php/scripts/cron/goals_weekly.php
   ```

3. **Test manually**:
   ```bash
   php php/scripts/cron/goals_daily.php
   ```

4. **Check cron logs**:
   ```bash
   grep CRON /var/log/syslog
   ```

### Database Connection Issues

Ensure the cron scripts can access the database:
- Check `php/config/db.config.inc.php` is accessible
- Verify database credentials are correct
- Test database connection from command line

### Permission Issues

- Ensure PHP has read/write access to necessary directories
- Check file ownership matches web server user
- Verify database user has required permissions

## Performance Considerations

- **Daily Job**: Typically runs in 1-5 minutes depending on number of goals
- **Weekly Job**: Typically runs in 5-15 minutes depending on data volume
- **Large Organizations**: Consider running during off-peak hours
- **Monitoring**: Set up alerts if cron jobs fail

## Logging

Both cron jobs output to:
- **Standard Output**: Execution summary
- **Error Output**: Error messages and stack traces

Redirect logs for monitoring:
```bash
0 2 * * * /usr/bin/php /path/to/goals_daily.php >> /var/log/goals_daily.log 2>&1
```

## Security Considerations

1. **File Permissions**: Restrict access to cron scripts
2. **Database Credentials**: Store securely, not in version control
3. **Error Messages**: Don't expose sensitive information in logs
4. **Execution Time**: Set timeouts to prevent long-running processes

## Monitoring

Recommended monitoring:
- Cron job execution status
- Execution time
- Error rates
- Database query performance
- Memory usage

## Integration with Notification System

The cron jobs are designed to integrate with the Tija notification system. When notifications are enabled:

1. Evaluation reminders are sent via notification system
2. Deadline alerts are sent via notification system
3. Users receive notifications based on their preferences

**Note**: Notification system integration requires:
- `php/classes/notification.php` to be available
- Notification queue system to be configured
- Email/SMS services to be configured

## Future Enhancements

- Real-time webhook notifications
- SMS notifications
- Slack/Teams integration
- Custom notification templates
- Batch notification optimization

