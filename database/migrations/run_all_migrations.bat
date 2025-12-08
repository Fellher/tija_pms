@echo off
REM ============================================================================
REM Activity Wizard Database Migrations Runner
REM ============================================================================
REM This script runs all required migrations for the Activity Wizard feature
REM ============================================================================

echo ============================================================
echo Activity Wizard - Database Migration Script
echo ============================================================
echo.

REM Set your MySQL credentials
set MYSQL_USER=sbsl_user
set MYSQL_PASSWORD=$@alfr0nzE6585
set MYSQL_DATABASE=pms_sbsl_deploy
set MYSQL_HOST=localhost

echo Database: %MYSQL_DATABASE%
echo User: %MYSQL_USER%
echo.

REM Check if MySQL is accessible
echo Checking MySQL connection...
mysql -h %MYSQL_HOST% -u %MYSQL_USER% -p%MYSQL_PASSWORD% -e "SELECT 1" 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Cannot connect to MySQL!
    echo Please check your credentials in this script.
    pause
    exit /b 1
)
echo MySQL connection successful!
echo.

REM Migration 1: Activity Wizard Fields
echo ============================================================
echo [1/2] Running: add_activity_wizard_fields.sql
echo ============================================================
mysql -h %MYSQL_HOST% -u %MYSQL_USER% -p%MYSQL_PASSWORD% %MYSQL_DATABASE% < add_activity_wizard_fields.sql
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Migration 1 failed!
    pause
    exit /b 1
)
echo Migration 1 completed successfully!
echo.

REM Migration 2: Multi-Expense System
echo ============================================================
echo [2/3] Running: add_activity_multi_expenses.sql
echo ============================================================
mysql -h %MYSQL_HOST% -u %MYSQL_USER% -p%MYSQL_PASSWORD% %MYSQL_DATABASE% < add_activity_multi_expenses.sql
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Migration 2 failed!
    pause
    exit /b 1
)
echo Migration 2 completed successfully!
echo.

REM Migration 3: Sales Documents Enhancement
echo ============================================================
echo [3/3] Running: enhance_sales_documents.sql
echo ============================================================
mysql -h %MYSQL_HOST% -u %MYSQL_USER% -p%MYSQL_PASSWORD% %MYSQL_DATABASE% < enhance_sales_documents.sql
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Migration 3 failed!
    pause
    exit /b 1
)
echo Migration 3 completed successfully!
echo.

REM Verify tables exist
echo ============================================================
echo Verifying Tables Created...
echo ============================================================

mysql -h %MYSQL_HOST% -u %MYSQL_USER% -p%MYSQL_PASSWORD% %MYSQL_DATABASE% -e "SHOW TABLES LIKE 'tija_activity%%'"
echo.

echo ============================================================
echo All migrations completed successfully!
echo ============================================================
echo.
echo New Tables Created:
echo.
echo Activity Management:
echo - tija_activity_expenses
echo - tija_activity_history
echo - tija_activity_attachments
echo - tija_activity_reminders
echo - tija_activity_comments
echo - tija_expense_categories
echo.
echo Document Management:
echo - tija_sales_document_access_log
echo - tija_sales_document_versions
echo - tija_sales_document_shares
echo.
echo New Fields Added:
echo.
echo tija_activities:
echo - meetingLink, activityNotes, activityOutcome, activityResult
echo - followUpNotes, requiresFollowUp, sendReminder, reminderTime
echo - allDayEvent, duration
echo.
echo tija_sales_documents:
echo - salesStage, saleStatusLevelID, documentStage
echo - sharedWithClient, sharedDate, tags, expiryDate
echo - linkedActivityID, viewCount, lastAccessedDate
echo.
echo ============================================================
echo Enhanced Features Now Available:
echo ============================================================
echo - 5-Step Activity Wizard with Summary
echo - Multi-Expense Tracking System
echo - Sales Documents with Stage Tracking
echo - Complete Timeline Display
echo - Tom Select Multi-Select
echo - Category-Type Filtering
echo.
echo The complete Sales Lifecycle Management system is ready!
echo.
pause

