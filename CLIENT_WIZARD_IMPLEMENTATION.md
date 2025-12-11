# Client Wizard Implementation Summary

## Overview
A comprehensive multi-step client onboarding wizard has been implemented with advanced features for managing addresses, contacts, and documents. The wizard follows the UI/UX patterns from existing project and proposal wizards in the system.

## Features Implemented

### 1. **Multi-Step Wizard Interface** (5 Steps)
- **Step 1: Basic Information**
  - Client name with auto-generated client code
  - PIN/Tax ID
  - Account owner selection (grouped by job title)
  - Client description
  - In-house client flag

- **Step 2: Addresses** (Multiple)
  - Add unlimited addresses
  - Each address includes:
    - Full address, city, postal code, country
    - Address type (Postal/Office)
    - Headquarters flag (only one can be marked)
    - Billing address flag
  - Dynamic add/remove functionality
  - Auto-renumbering when addresses are removed

- **Step 3: Contacts** (Multiple)
  - Add unlimited contacts
  - Each contact includes:
    - Name, position/title
    - Email, phone
    - Link to specific address (optional)
    - Primary contact flag (only one can be marked)
  - Dynamic add/remove functionality
  - Contact-to-address linking

- **Step 4: Documents** (Multiple)
  - Upload multiple documents
  - Each document includes:
    - Document type (predefined types + custom)
    - Document name
    - File upload (PDF, DOC, DOCX, JPG, PNG up to 10MB)
    - Notes
  - Supported document types:
    - Certificate of Incorporation
    - Business License
    - PIN Certificate
    - Tax Compliance
    - Service Agreements
    - NDAs
    - Contracts
    - Other

- **Step 5: Review & Submit**
  - Comprehensive review of all entered data
  - Organized by category with counts
  - Edit capability (go back to any step)

### 2. **UI/UX Enhancements**
- **Progress Indicator**
  - Visual step progress bar with icons
  - Active, completed, and pending states
  - Smooth animations between steps

- **Validation**
  - Client name and account owner required
  - At least one address required
  - One address must be marked as headquarters
  - Email format validation for contacts
  - File type and size validation for documents

- **User Experience**
  - Auto-generation of client code from name
  - Only one headquarters address allowed
  - Only one primary contact allowed
  - Responsive design for all screen sizes
  - Smooth transitions and animations
  - Clear visual feedback

### 3. **Backend Processing**
- **File Upload Handling**
  - Secure file upload with validation
  - Organized storage: `uploads/clients/{clientID}/documents/`
  - Unique filename generation
  - File type and size restrictions

- **Database Operations**
  - Transaction-based processing (all-or-nothing)
  - Creates records in:
    - `tija_clients` - Main client record
    - `tija_client_addresses` - Multiple addresses
    - `client_relationship_assignments` - Account owner relationship
    - `tija_client_contacts` - Multiple contacts
    - `tija_client_documents` - Document metadata with file paths

- **Error Handling**
  - Comprehensive validation
  - Transaction rollback on errors
  - User-friendly error messages

## Files Modified/Created

### Frontend
1. **`html/includes/scripts/clients/modals/manage_client_wizard.php`**
   - Complete wizard modal HTML structure
   - Progress indicator
   - All 5 wizard steps
   - Responsive styling

2. **`assets/js/src/pages/user/clients/wizard.js`**
   - Wizard state management
   - Dynamic address/contact/document management
   - Form validation
   - Data collection and submission
   - Review population

### Backend
3. **`php/scripts/clients/process_client_wizard.php`**
   - Handles multipart form data
   - Processes addresses, contacts, and documents
   - File upload handling
   - Database transaction management

## Technical Highlights

### Address Management
```javascript
- Dynamic card creation with unique IDs
- Auto-renumbering on removal
- Headquarters exclusivity (radio-like behavior)
- Linked to contacts for relationship mapping
```

### Contact Management
```javascript
- Links to specific addresses
- Primary contact exclusivity
- Email validation
- Optional fields with smart defaults
```

### Document Management
```javascript
- File upload with preview
- Type categorization
- Size and format validation
- Secure storage with metadata
```

### Data Flow
```
User Input → JavaScript Collection → FormData → PHP Processing → Database + File Storage
```

## Usage

### Opening the Wizard
```javascript
// Triggered by button in home.php
<button data-bs-toggle="modal" data-bs-target="#clientWizardModal">
    Client Wizard
</button>
```

### Data Structure
```javascript
wizardData = {
    basic: {
        clientName, clientCode, vatNumber, accountOwnerID,
        clientDescription, inHouse, orgDataID, entityID
    },
    addresses: [
        { address, city, postalCode, country, addressType,
          headquarters, billingAddress }
    ],
    contacts: [
        { contactName, contactPosition, contactEmail, contactPhone,
          linkedAddress, primaryContact }
    ],
    documents: [
        { documentType, documentName, documentNotes, file }
    ]
}
```

## Validation Rules

1. **Required Fields**
   - Client name
   - Account owner
   - At least one address with city and country
   - One address marked as headquarters

2. **Optional Fields**
   - VAT/PIN number
   - Client description
   - Contacts (recommended but not required)
   - Documents (optional)

3. **File Constraints**
   - Max size: 10MB per file
   - Allowed types: PDF, DOC, DOCX, JPG, PNG
   - Stored in organized directory structure

## Benefits

1. **Comprehensive Data Collection** - All client information in one workflow
2. **User-Friendly** - Step-by-step guidance with validation
3. **Flexible** - Add as many addresses/contacts/documents as needed
4. **Relationship Mapping** - Link contacts to specific addresses
5. **Document Management** - Secure upload and storage
6. **Data Integrity** - Transaction-based processing ensures consistency
7. **Professional UI** - Matches existing system design patterns

## Future Enhancements (Potential)

- Drag-and-drop file upload
- Bulk contact import from CSV
- Address autocomplete/validation
- Contact photo upload
- Document preview before upload
- Save as draft functionality
- Email notification to account owner
- Integration with CRM workflows

## Browser Compatibility
- Modern browsers (Chrome, Firefox, Safari, Edge)
- Responsive design for mobile/tablet
- Graceful degradation for older browsers
