# Sales Document Management System

## Overview
Comprehensive document management system for sales lifecycle with stage tracking, version control, access logging, and sharing capabilities.

## Features Implemented

### 1. **Stage Tracking** 🎯
Track exactly which sales stage a document was created/shared at:
- Lead
- Opportunity
- Proposal
- Closed-Won
- Closed-Lost

### 2. **Document Maturity Stages**
Track document lifecycle:
- Draft
- Under Revision
- Final Version
- Approved
- Signed

### 3. **Version Control**
- Multiple versions per document
- Version history
- Mark current version
- Version notes

### 4. **Access Logging**
- Track views, downloads, shares, edits
- IP address and user agent logging
- View count and download count
- Last accessed timestamp

### 5. **Sharing Management**
- Track sharing with clients
- Share date tracking
- Multiple sharing methods (email, link, portal)
- Access expiry for shared links

### 6. **Enhanced Metadata**
- Tags for easy searching
- Expiry dates for time-sensitive documents
- Link to activities
- Receipt attachments
- Approval workflow

## Database Schema

### Migration File
**File:** `enhance_sales_documents.sql`

### Enhanced Table: `tija_sales_documents`

**New Fields Added:**
1. `salesStage` - VARCHAR(50) - Sales stage when document was added
2. `saleStatusLevelID` - INT - FK to tija_sales_status_levels
3. `documentStage` - ENUM - Document maturity (draft, final, revision, approved, signed)
4. `sharedWithClient` - ENUM(Y/N) - Flag if shared with client
5. `sharedDate` - DATETIME - When shared with client
6. `tags` - TEXT - Comma-separated tags
7. `expiryDate` - DATE - For time-sensitive documents
8. `linkedActivityID` - INT - FK to tija_activities
9. `viewCount` - INT - Number of views
10. `lastAccessedDate` - DATETIME - Last access timestamp

**Indexes Added:**
- `idx_sales_stage` - Stage queries
- `idx_document_category` - Category filtering
- `idx_uploaded_by` - User lookups

### New Table: `tija_sales_document_access_log`
Comprehensive access tracking.

**Fields:**
- `accessID` - INT, PK
- `documentID` - INT, FK
- `accessedBy` - INT, User ID
- `accessType` - ENUM(view, download, share, edit)
- `accessDate` - DATETIME
- `ipAddress` - VARCHAR(45)
- `userAgent` - TEXT

### New Table: `tija_sales_document_versions`
Version history tracking.

**Fields:**
- `versionID` - INT, PK
- `documentID` - INT, FK
- `versionNumber` - VARCHAR(20)
- `fileName` - VARCHAR(255)
- `fileURL` - VARCHAR(500)
- `fileSize` - BIGINT
- `versionNotes` - TEXT
- `uploadedBy` - INT
- `uploadedOn` - DATETIME
- `isCurrent` - ENUM(Y/N)

### New Table: `tija_sales_document_shares`
Track document sharing with external parties.

**Fields:**
- `shareID` - INT, PK
- `documentID` - INT, FK
- `sharedWith` - VARCHAR(255) - Email or user ID
- `sharedBy` - INT
- `sharedDate` - DATETIME
- `shareMethod` - ENUM(email, link, portal)
- `accessLink` - VARCHAR(500)
- `accessExpiry` - DATETIME
- `accessCount` - INT
- `lastAccessedDate` - DATETIME

### View: `view_sales_document_summary`
Provides comprehensive document overview with joins.

**Includes:**
- Document details
- Sales case information
- Uploader name
- Status level name
- Document status (Active, Expired, Pending Approval, etc.)

## Frontend Implementation

### Document Upload Modal
**File:** `html/includes/scripts/sales/modals/manage_sales_document.php`

**Enhanced Fields:**
1. **Document Name** (Required)
2. **Document Category** (Required) - 9 predefined categories
3. **Document Type** - Custom type or subtype
4. **Document Stage** - Draft, Revision, Final, Approved, Signed
5. **File Upload** (Required) - PDF, Word, Excel, PowerPoint, Images
6. **Description** - Detailed notes
7. **Tags** - Comma-separated for searching
8. **Expiry Date** - With Flatpickr date picker
9. **Current Sales Stage** (Read-only display) - Auto-captured
10. **Confidential** - Checkbox switch
11. **Visible to Client** - Checkbox switch
12. **Requires Approval** - Checkbox switch
13. **Shared with Client** - Checkbox with date picker
14. **Link to Activity** - Dropdown of recent activities
15. **Version** - Version number input
16. **Link to Proposal** - If applicable

**Features:**
- Sales stage auto-captured from current sales case status
- Flatpickr for date fields
- File preview before upload
- Conditional fields (shared date appears when shared checkbox checked)
- Validation for required fields
- AJAX submission with loading states

### Document Display
**File:** `html/includes/scripts/sales/sales_documents_display.php`

**Enhanced Display:**
- Sales stage badge with color coding
- Document stage badge
- Tags display
- View and download counts
- Shared with client indicator
- Approval status indicators
- Category filtering
- File type icons
- Hover effects

### Backend Processing
**File:** `php/scripts/sales/manage_sales_document.php`

**Enhanced Features:**
- Handles all new fields
- Automatic stage capturing
- Access logging on upload
- Version creation
- Error handling with table existence checks
- Graceful degradation if new tables don't exist yet

**File:** `php/classes/sales.php`

**Updated Method:** `sales_documents()`
- Includes all new fields in SELECT
- Maintains backward compatibility

## Usage Examples

### Example 1: Upload Proposal Document
```
Document Name: "Technical Proposal for CRM Implementation"
Category: Proposal
Document Stage: Final
Tags: technical, proposal, crm, pricing
Current Sales Stage: Proposal ← Auto-captured
Linked Activity: "Proposal Presentation Meeting"
Expiry Date: 2025-12-31
Visible to Client: ✅ Yes
Requires Approval: ✅ Yes
```

### Example 2: Upload Meeting Notes
```
Document Name: "Client Discovery Meeting Notes"
Category: Meeting Notes
Document Stage: Final
Tags: discovery, requirements, notes
Current Sales Stage: Opportunity ← Auto-captured
Linked Activity: "Client Discovery Meeting"
Shared with Client: ✅ Yes
Shared Date: 2025-12-02
```

### Example 3: Upload Contract
```
Document Name: "Master Service Agreement"
Category: Sales Agreement
Document Stage: Signed
Tags: contract, legal, signed
Current Sales Stage: Closed-Won ← Auto-captured
Confidential: ✅ Yes
Version: 2.0
```

## Document Categories

1. **Sales Agreement** - Contracts and agreements
2. **Terms of Reference (TOR)** - Project scope documents
3. **Proposal** - Business proposals and quotes
4. **Engagement Letter** - Engagement documents
5. **Confidentiality Agreement** - NDAs
6. **Expense Document** - Receipts and expense reports
7. **Correspondence** - Emails and letters
8. **Meeting Notes** - Minutes and notes
9. **Other** - Miscellaneous documents

## Stage-Based Filtering

### Documents by Stage Query:
```sql
-- Get all documents from Proposal stage
SELECT * FROM tija_sales_documents
WHERE salesStage = 'Proposal'
AND Suspended = 'N';

-- Count documents per stage
SELECT
   salesStage,
   COUNT(*) as document_count,
   SUM(fileSize) as total_size
FROM tija_sales_documents
WHERE Suspended = 'N'
GROUP BY salesStage;
```

### Stage Timeline View:
```sql
-- Documents across sales journey
SELECT
   salesStage,
   documentCategory,
   documentName,
   DateAdded,
   uploadedByName
FROM view_sales_document_summary
WHERE salesCaseID = ?
ORDER BY
   FIELD(salesStage, 'Lead', 'Opportunity', 'Proposal', 'Closed-Won', 'Closed-Lost'),
   DateAdded;
```

## Access Tracking

### Recent Document Activity:
```sql
SELECT
   al.accessType,
   al.accessDate,
   d.documentName,
   CONCAT(u.FirstName, ' ', u.Surname) as accessedBy
FROM tija_sales_document_access_log al
JOIN tija_sales_documents d ON al.documentID = d.documentID
JOIN people u ON al.accessedBy = u.ID
WHERE d.salesCaseID = ?
ORDER BY al.accessDate DESC
LIMIT 20;
```

### Most Viewed Documents:
```sql
SELECT
   documentName,
   documentCategory,
   viewCount,
   downloadCount,
   uploadedByName
FROM view_sales_document_summary
WHERE salesCaseID = ?
ORDER BY viewCount DESC
LIMIT 10;
```

## Deployment Steps

### 1. Run Migration
```bash
# Option A: phpMyAdmin
# - Copy contents of enhance_sales_documents.sql
# - Paste in SQL tab
# - Execute

# Option B: Command Line
mysql -u sbsl_user -p pms_sbsl_deploy < database/migrations/enhance_sales_documents.sql
```

### 2. Verify Schema
```sql
-- Check new fields
SHOW COLUMNS FROM tija_sales_documents LIKE '%salesStage%';
SHOW COLUMNS FROM tija_sales_documents LIKE '%documentStage%';

-- Check new tables
SHOW TABLES LIKE '%document%';
-- Should show:
-- tija_sales_documents
-- tija_sales_document_access_log
-- tija_sales_document_versions
-- tija_sales_document_shares

-- Check view
SELECT * FROM view_sales_document_summary LIMIT 1;
```

### 3. Test Upload
1. Navigate to sales case details
2. Click Documents tab
3. Click "Upload Document"
4. Fill all fields
5. Notice sales stage is auto-displayed
6. Upload file
7. Verify in database:
```sql
SELECT documentName, salesStage, documentStage, tags
FROM tija_sales_documents
ORDER BY documentID DESC LIMIT 1;
```

### 4. Verify Display
- Document should show sales stage badge
- Tags should display
- View/download counts should show
- Shared indicator should appear if checked

## Security Features

### Access Control
- Role-based permissions (Management, Finance can approve)
- Confidential documents hidden from regular users
- Owner can always see their documents
- Approval workflow for sensitive documents

### File Security
- Allowed file types enforced
- File size limits (50MB max)
- MIME type validation
- Secure file storage
- Soft delete (file preserved)

### Audit Trail
- Who uploaded (uploadedBy)
- When uploaded (DateAdded)
- Who accessed (access_log)
- Who approved (approvedBy)
- All actions timestamped

## API Reference

### Upload Document
**Endpoint:** `/php/scripts/sales/manage_sales_document.php`
**Method:** POST (multipart/form-data)

**Parameters:**
```
action: "upload"
salesCaseID: "123"
documentName: "Proposal Document"
documentCategory: "proposal"
documentType: "Technical"
documentStage: "final"
documentFile: [file upload]
description: "Technical proposal for..."
tags: "technical, proposal, pricing"
expiryDate: "2025-12-31"
linkedActivityID: "45"
salesStage: "Proposal" (auto-captured)
saleStatusLevelID: "3" (auto-captured)
sharedWithClient: "Y"
sharedDate: "2025-12-02"
version: "1.0"
isConfidential: "N"
isPublic: "Y"
requiresApproval: "Y"
```

**Response:**
```json
{
  "success": true,
  "message": "Document uploaded successfully at Proposal stage",
  "data": {
    "documentID": "456",
    "documentName": "Proposal Document",
    "fileName": "doc_123456.pdf",
    "fileURL": "/uploads/sales_documents/doc_123456.pdf",
    "salesStage": "Proposal"
  }
}
```

## Reporting Queries

### Documents by Stage Report:
```sql
SELECT
   salesStage,
   documentCategory,
   COUNT(*) as doc_count,
   SUM(fileSize) as total_size_bytes,
   ROUND(SUM(fileSize)/1024/1024, 2) as total_size_mb
FROM tija_sales_documents
WHERE Suspended = 'N'
GROUP BY salesStage, documentCategory
ORDER BY salesStage, documentCategory;
```

### Stage Progression Report:
```sql
SELECT
   sc.salesCaseName,
   sc.saleStage as current_stage,
   GROUP_CONCAT(DISTINCT sd.salesStage) as stages_with_documents,
   COUNT(sd.documentID) as total_documents
FROM tija_sales_cases sc
LEFT JOIN tija_sales_documents sd ON sc.salesCaseID = sd.salesCaseID AND sd.Suspended = 'N'
GROUP BY sc.salesCaseID
HAVING total_documents > 0;
```

### Document Activity Timeline:
```sql
SELECT
   DATE(al.accessDate) as activity_date,
   al.accessType,
   COUNT(*) as count,
   GROUP_CONCAT(DISTINCT d.documentName SEPARATOR ', ') as documents
FROM tija_sales_document_access_log al
JOIN tija_sales_documents d ON al.documentID = d.documentID
WHERE d.salesCaseID = ?
AND al.accessDate >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(al.accessDate), al.accessType
ORDER BY activity_date DESC;
```

## Benefits

### For Sales Teams
✅ Track documents at each sales stage
✅ Know what was shared when
✅ Easy tagging and search
✅ Version control
✅ Link documents to activities

### For Management
✅ See document progression through stages
✅ Approval workflow
✅ Access analytics
✅ Compliance tracking
✅ Audit trail

### For Clients
✅ Marked documents visible to them
✅ Shared documents trackable
✅ Expiry dates for quotes/proposals
✅ Version history

## Future Enhancements

### Planned Features
1. **E-signature Integration** - Digital signatures
2. **Document Templates** - Pre-built templates
3. **Bulk Upload** - Upload multiple files
4. **Advanced Search** - Full-text search
5. **Document Comparison** - Compare versions
6. **Automated Sharing** - Email documents automatically
7. **Client Portal** - Client document access
8. **OCR** - Extract text from images
9. **Document Expiry Alerts** - Notify before expiry
10. **Approval Routing** - Multi-level approval

## Version History

### v2.0.0 (2025-12-02)
- Sales stage tracking
- Document maturity stages
- Version control system
- Access logging
- Sharing management
- Enhanced metadata (tags, expiry)
- Activity linking
- View/download counts
- Comprehensive reporting

## Support

### Common Issues

**Issue:** Documents not showing stage badge
**Solution:** Run migration to add salesStage field

**Issue:** Upload fails
**Solution:** Check upload directory permissions, verify file size < 50MB

**Issue:** Stage not captured
**Solution:** Ensure hidden fields in modal have values from $salesCaseDetails

**Issue:** Access log not working
**Solution:** Run migration to create access_log table

## Files Modified

### Database (1 file):
✅ `database/migrations/enhance_sales_documents.sql`

### Frontend (2 files):
✅ `html/includes/scripts/sales/modals/manage_sales_document.php`
✅ `html/includes/scripts/sales/sales_documents_display.php`

### Backend (2 files):
✅ `php/scripts/sales/manage_sales_document.php`
✅ `php/classes/sales.php`

## License
Proprietary - Tija CRM System

---

**Sales Document Management with Stage Tracking is Complete!** 🎉


