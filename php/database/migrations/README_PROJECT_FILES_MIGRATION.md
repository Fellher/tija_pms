# Project Files Table Migration

## Quick Setup Instructions

The `tija_project_files` table is required for the project file management feature.

### ⚡ Easiest Method: Run PHP Script

1. Open your browser
2. Navigate to: `http://localhost/demo-pms.tija.ke/php/database/migrations/run_project_files_migration.php`
3. The script will automatically create the table
4. You'll see a success message when done

### Alternative Method: phpMyAdmin

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select your database (`tija_pms_skm_vs_local`)
3. Click the "SQL" tab
4. Open the file: `php/database/migrations/create_project_files_table.sql`
5. Copy all contents (Ctrl+A, Ctrl+C)
6. Paste into the SQL query box
7. Click "Go"
8. ✅ Done!

### Verify Success

Run this query in phpMyAdmin:

```sql
SHOW TABLES LIKE 'tija_project_files';
```

You should see the table listed.

Or check the table structure:

```sql
DESCRIBE tija_project_files;
```

You should see columns like:
- fileID
- projectID
- fileName
- fileURL
- fileType
- fileSize
- category
- uploadedBy
- etc.

## What This Table Does

The `tija_project_files` table stores:
- File metadata (name, size, type)
- File location (URL)
- Project and task associations
- Upload information (who, when)
- File categories and descriptions
- Public/private access settings
- Download tracking

## After Migration

Once the table is created, you can:
- ✅ Upload files to projects
- ✅ View file details
- ✅ Download files
- ✅ Delete files
- ✅ Link files to tasks
- ✅ Set file categories
- ✅ Control public/private access

## Troubleshooting

**Error: "Table already exists"**
- This is OK! The table was already created.
- You can safely ignore this message.

**Error: "Access denied"**
- Check your database user permissions
- Ensure the user has CREATE TABLE privileges

**Error: "Database connection failed"**
- Check your database credentials in `php/config/database.php`
- Ensure MySQL/MariaDB is running

## Files Created

- `create_project_files_table.sql` - SQL migration file
- `run_project_files_migration.php` - PHP migration runner
- `README_PROJECT_FILES_MIGRATION.md` - This file

