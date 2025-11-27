# Sales Documents Table Migration

## Quick Setup Instructions

The `tija_sales_documents` table is required for the sales document management feature.

### ⚡ Easiest Method: Run PHP Script

1. Open your browser
2. Navigate to: `http://localhost/demo-pms.tija.ke/php/database/migrations/run_sales_documents_migration.php`
3. The script will automatically create the table
4. You'll see a success message when done

### Alternative Method: phpMyAdmin

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select your database
3. Click the "SQL" tab
4. Open the file: `php/database/migrations/create_sales_documents_table.sql`
5. Copy all contents (Ctrl+A, Ctrl+C)
6. Paste into the SQL query box
7. Click "Go"
8. ✅ Done!

### Verify Success

Run this query in phpMyAdmin:

```sql
SHOW TABLES LIKE 'tija_sales_documents';
```

You should see the table listed.

Or check the table structure:

```sql
DESCRIBE tija_sales_documents;
```

## What This Table Does

The `tija_sales_documents` table stores:
- Document metadata (name, size, type, category)
- File location (URL)
- Sales case and proposal associations
- Upload information (who, when)
- Confidentiality and approval settings
- Download tracking
- Expense document linkage

## Document Categories

- **sales_agreement**: Sales agreements and contracts
- **tor**: Terms of Reference
- **proposal**: Proposals and quotes
- **engagement_letter**: Engagement letters
- **confidentiality_agreement**: NDA and confidentiality agreements
- **expense_document**: Expense receipts, invoices, etc.
- **correspondence**: Email correspondence, letters
- **meeting_notes**: Meeting minutes and notes
- **other**: Other documents

## Access Control

- **Management**: Full access to all documents
- **Finance**: Full access to all documents (especially expense documents)
- **Sales Team**: Access to documents for their sales cases
- **Confidential Documents**: Only accessible to authorized users

## Features

- ✅ Document upload with category classification
- ✅ File type validation (PDF, Word, Excel, Images, etc.)
- ✅ Size limit: 50MB per file
- ✅ Confidential document flagging
- ✅ Approval workflow for sensitive documents
- ✅ Client visibility control
- ✅ Expense document linkage
- ✅ Proposal document linkage
- ✅ Download tracking
- ✅ Category-based filtering

## After Migration

Once the table is created, you can:
- ✅ Upload documents to sales cases
- ✅ Categorize documents by type
- ✅ Mark documents as confidential
- ✅ Link documents to proposals or expenses
- ✅ Set approval requirements
- ✅ Control client visibility
- ✅ Track downloads

## Troubleshooting

**Error: "Table already exists"**
- This is OK! The table was already created.
- You can safely ignore this message.

**Error: "Access denied"**
- Check your database user permissions
- Ensure the user has CREATE TABLE privileges

**Error: "Foreign key constraint fails"**
- Ensure `tija_sales_cases` table exists
- Ensure `tija_users` table exists
- The foreign keys will be created automatically

**Error: "Database connection failed"**
- Check your database credentials in `php/config/database.php`

