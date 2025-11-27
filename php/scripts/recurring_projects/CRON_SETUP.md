# Recurring Projects - Notification-Based System

This document explains the notification-based system for managing recurring projects.

## System Overview

Instead of automated cron jobs, the system uses **notifications** to alert project managers when action is needed:

1. **On User Login**: System checks for recurring projects needing attention
2. **Manual Activation**: Project managers manually generate next billing cycles
3. **Manual Invoice Creation**: Project managers manually create invoice drafts when notified

## Notification Types

### 1. Generate Billing Cycles Notification
- **Trigger**: When last billing cycle ends within 7 days or has ended
- **Recipient**: Project owners and managers
- **Action**: Click "Generate Next Cycles" button to create new cycles

### 2. Create Invoice Draft Notification
- **Trigger**: When billing cycle reaches billing date and no draft exists
- **Recipient**: Project owners and managers
- **Action**: Click "Create Invoice Draft" button to generate draft

## Implementation

### Notification Check Script
**Script:** `php/scripts/recurring_projects/check_recurring_notifications.php`
**Purpose:** Checks for recurring projects needing attention
**Called:** On user login and periodically via AJAX

### Manual Cycle Generation
**Script:** `php/scripts/recurring_projects/manage_billing_cycle.php`
**Action:** `generate_next_cycles`
**Purpose:** Allows project managers to manually generate next billing cycles

### Manual Invoice Draft Creation
**Script:** `php/scripts/recurring_projects/create_invoice_draft_manual.php`
**Purpose:** Allows project managers to manually create invoice drafts for billing cycles

## Setup Instructions

### Linux/Unix (crontab)

1. Open the crontab editor:
```bash
crontab -e
```

2. Add the following lines (adjust paths as needed):
```bash
# Billing Cycle Generator - Runs daily at 2:00 AM
0 2 * * * /usr/bin/php /path/to/your/project/php/scripts/recurring_projects/generate_billing_cycles.php >> /path/to/logs/billing_cycles.log 2>&1

# Invoice Draft Generator - Runs daily at 3:00 AM
0 3 * * * /usr/bin/php /path/to/your/project/php/scripts/recurring_projects/generate_invoice_drafts.php >> /path/to/logs/invoice_drafts.log 2>&1
```

3. Replace `/path/to/your/project` with your actual project path
4. Replace `/path/to/logs` with your desired log directory path
5. Ensure the log directory exists and is writable:
```bash
mkdir -p /path/to/logs
chmod 755 /path/to/logs
```

### Windows Task Scheduler

1. Open Task Scheduler
2. Create a new task for each script:
   - **Name:** Billing Cycle Generator
   - **Trigger:** Daily at 2:00 AM
   - **Action:** Start a program
   - **Program:** `C:\path\to\php.exe`
   - **Arguments:** `C:\path\to\your\project\php\scripts\recurring_projects\generate_billing_cycles.php`
   - **Start in:** `C:\path\to\your\project`

3. Repeat for Invoice Draft Generator (3:00 AM)

### Using WAMP/XAMPP on Windows

If you're using WAMP/XAMPP, you can create a batch file:

**billing_cycles.bat:**
```batch
@echo off
cd /d C:\wamp64\www\demo-pms.tija.ke
C:\wamp64\bin\php\php8.x.x\php.exe php\scripts\recurring_projects\generate_billing_cycles.php
```

Then schedule this batch file in Task Scheduler.

## Verification

After setting up the cron jobs:

1. Check the log files to ensure scripts are running
2. Manually run the scripts to test:
```bash
php php/scripts/recurring_projects/generate_billing_cycles.php
php php/scripts/recurring_projects/generate_invoice_drafts.php
```

3. Verify in the database that:
   - New billing cycles are being created
   - Cycle statuses are being updated
   - Invoice drafts are being generated

## Troubleshooting

### Scripts not running
- Check PHP path is correct
- Verify file permissions (should be executable)
- Check PHP error logs
- Ensure database connection is working

### No cycles generated
- Verify projects have `isRecurring = 'Y'`
- Check recurrence settings are valid
- Review script logs for errors

### No invoice drafts created
- Verify `autoGenerateInvoices = 'Y'` for projects
- Check billing cycles have reached billing date
- Review script logs for errors

## Manual Execution

You can also run these scripts manually via web browser or command line for testing:

**Via Web Browser:**
- Navigate to: `http://your-domain/php/scripts/recurring_projects/generate_billing_cycles.php`
- Navigate to: `http://your-domain/php/scripts/recurring_projects/generate_invoice_drafts.php`

**Via Command Line:**
```bash
cd /path/to/your/project
php php/scripts/recurring_projects/generate_billing_cycles.php
php php/scripts/recurring_projects/generate_invoice_drafts.php
```

## Notes

- Scripts include error logging to help diagnose issues
- Scripts use database transactions for data integrity
- Scripts are designed to be idempotent (safe to run multiple times)
- Consider running scripts during low-traffic hours

