# Invoice System - Complete Implementation

## Overview
A comprehensive enterprise-level invoicing system integrated with the projects module, allowing users to invoice clients based on projects, tasks, employee work hours, expenses, and licenses.

## Features Implemented

### 1. PDF Generation with DomPDF ✅
- **Library**: DomPDF (via Composer)
- **Location**: `php/scripts/invoices/generate_pdf.php`
- **Features**:
  - Generates PDF invoices from HTML templates
  - Supports HTML preview mode
  - Template-based rendering
  - Fallback to HTML if DomPDF not installed

**Installation**:
```bash
composer install
```

### 2. Template Management UI ✅
- **Location**: `html/pages/user/invoices/templates.php`
- **Features**:
  - Create, edit, delete templates
  - Logo upload functionality
  - Template preview
  - Set default template
  - Template types: Standard, Hourly, Expense, Milestone, Recurring, Custom

### 3. Logo Upload ✅
- **Location**: `php/scripts/invoices/upload_logo.php`
- **Features**:
  - Upload company logos for templates
  - Supported formats: JPG, PNG, GIF, WebP
  - Max file size: 2MB
  - Automatic old logo deletion
  - Preview functionality

### 4. Template Preview ✅
- **Location**: `html/pages/user/invoices/template_preview.php`
- **Features**:
  - Preview templates with sample data
  - HTML and PDF preview modes
  - Sample invoice generation

### 5. Invoice Reports ✅
- **Location**: `html/pages/user/invoices/reports.php`
- **Features**:
  - Date range filtering
  - Status filtering
  - Client filtering
  - Statistics dashboard:
    - Total invoices
    - Total value
    - Total paid
    - Outstanding amounts
    - Overdue count
  - Detailed invoice table
  - Export functionality (ready for implementation)

### 6. Sample System Templates ✅
- **Location**: `database/sample_invoice_templates.sql`
- **Templates Included**:
  1. Standard Invoice Template (Default)
  2. Hourly Billing Template
  3. Expense-Based Template
  4. Milestone Payment Template
  5. Recurring Billing Template

**Installation**:
```sql
-- Run the SQL file to create sample templates
SOURCE database/sample_invoice_templates.sql;
```

## File Structure

```
php/
├── scripts/invoices/
│   ├── generate_pdf.php          # PDF generation with DomPDF
│   ├── manage_template.php       # Template CRUD operations
│   └── upload_logo.php          # Logo upload handler

html/pages/user/invoices/
├── list.php                      # Invoice list page
├── create.php                    # Create/edit invoice page
├── view.php                      # Invoice view page
├── templates.php                 # Template management UI
├── template_preview.php          # Template preview page
└── reports.php                   # Invoice reports page

database/
├── invoicing_system_schema.sql   # Database schema
└── sample_invoice_templates.sql  # Sample templates

composer.json                     # DomPDF dependency
```

## Usage

### Installing DomPDF
```bash
cd C:\wamp64\www\demo-pms.tija.ke
composer install
```

### Creating Sample Templates
Run the SQL file in your database:
```sql
SOURCE database/sample_invoice_templates.sql;
```

### Managing Templates
1. Navigate to: **Invoicing > Invoice Templates**
2. Click "Add New Template" to create a template
3. Fill in company details, payment terms, etc.
4. Upload logo (optional)
5. Set as default if needed
6. Click "Preview" to see how invoices will look

### Generating PDFs
1. View any invoice
2. Click "Download PDF" button
3. PDF will be generated using DomPDF (or HTML fallback)

### Viewing Reports
1. Navigate to: **Invoicing > Reports**
2. Apply filters (date range, status, client)
3. View statistics and detailed invoice list
4. Export report (if export script implemented)

## Configuration

### Logo Upload Directory
Logos are stored in: `data/uploaded_files/invoice_templates/logos/`

### Template Default Settings
- Default currency: KES
- Default tax: 16%
- Payment terms: 30 days

## Notes

1. **DomPDF Installation**: The system will work without DomPDF but will show HTML preview instead of PDF. Install via Composer for full PDF functionality.

2. **Template Management**: Only administrators can manage templates. Regular users can select templates when creating invoices.

3. **Sample Templates**: The sample templates are created with `orgDataID=1` and `entityID=1`. Update these values or create organization-specific templates.

4. **Logo Upload**: Logos are automatically resized and optimized. Old logos are deleted when new ones are uploaded.

5. **Preview Mode**: Template preview uses sample data. Actual invoices use real client and project data.

## Future Enhancements

- [ ] Export reports to Excel/CSV
- [ ] Email invoice functionality
- [ ] Invoice numbering customization
- [ ] Multi-currency support
- [ ] Recurring invoice automation
- [ ] Payment gateway integration
- [ ] Advanced reporting with charts

## Support

For issues or questions, refer to:
- Database schema: `database/invoicing_system_schema.sql`
- Invoice class: `php/classes/invoice.php`
- Backend scripts: `php/scripts/invoices/`

