# Converting Prospects to Sales Opportunities

## Overview
This guide explains how to convert qualified prospects into sales opportunities within the system. This feature allows you to seamlessly transition prospects through your sales pipeline.

## Prerequisites

### Database Setup
Before using this feature, ensure the database migration has been run:

```sql
-- Add conversion tracking columns
ALTER TABLE tija_sales_prospects
ADD COLUMN convertedToSale ENUM('Y', 'N') DEFAULT 'N' AFTER salesProspectStatus,
ADD COLUMN salesCaseID INT NULL AFTER convertedToSale,
ADD COLUMN conversionDate DATETIME NULL AFTER salesCaseID,
ADD COLUMN convertedByID INT NULL AFTER conversionDate,
ADD INDEX idx_converted (convertedToSale, salesCaseID);
```

### Prospect Requirements
To convert a prospect to a sales opportunity, the prospect must:
- ✅ Have qualification status set to **"Qualified"**
- ✅ Not already be converted (`convertedToSale = 'N'`)
- ✅ Be active (`Suspended = 'N'`)

---

## Step-by-Step Conversion Process

### Step 1: Qualify the Prospect
1. Navigate to the prospect details page
2. Ensure the prospect's **Lead Qualification Status** is set to **"Qualified"**
3. Update qualification status if needed via the Edit Prospect form

### Step 2: Initiate Conversion
1. On the prospect details page, locate the **"Convert to Sale"** button (green button in the header)
2. Click the **"Convert to Sale"** button
3. The conversion modal will open with pre-filled prospect information

### Step 3: Review Prospect Summary
The modal displays a summary of the prospect:
- Prospect name
- Case/opportunity name
- Estimated value
- Current qualification status

### Step 4: Select Sales Stage
1. Choose the appropriate **Sales Stage** from the dropdown
   - Sales stages are loaded from the `tija_sales_status_levels` table
   - Stages are entity-specific if applicable
2. The probability percentage will auto-update based on the selected stage

### Step 5: Configure Sale Details

**Required Fields:**
- **Sale Name**: Pre-filled with prospect name (editable)
- **Deal Value**: Pre-filled with estimated value (editable)
- **Sales Stage**: Must be selected

**Optional Fields:**
- **Probability (%)**: Auto-filled based on stage (editable)
- **Expected Close Date**: Target date for closing the deal
- **Sales Owner**: Pre-filled from prospect owner (editable)
- **Assigned Team**: Select from available prospect teams

### Step 6: Add Additional Information
- **Sale Description**: Pre-filled with prospect case name
- **Next Steps**: Outline immediate actions needed
- **Initial Notes**: Any relevant information for the sales team

### Step 7: Confirm Conversion
1. Read the warning message: *"This will deactivate the prospect and create a new sales opportunity"*
2. Check the confirmation box: *"I understand this action will close the prospect"*
3. Click **"Convert to Sale"** button

### Step 8: Verify Conversion
- Upon successful conversion, you'll be redirected to the new sales opportunity details page
- The original prospect will be:
  - Marked as **Closed** (`salesProspectStatus = 'closed'`)
  - Suspended (`Suspended = 'Y'`)
  - Linked to the created sale (`salesCaseID` populated)
  - Timestamped with conversion date and user

---

## What Happens During Conversion

### Data Transfer
The following data is transferred from prospect to sale:

| Prospect Field | → | Sale Field |
|----------------|---|------------|
| `salesProspectName` | → | `saleCaseName` |
| `prospectCaseName` | → | `saleDescription` |
| `estimatedValue` | → | `saleValue` |
| `probability` | → | `probability` |
| `clientID` | → | `clientID` |
| `ownerID` | → | `saleOwnerID` |
| `businessUnitID` | → | `businessUnitID` |
| `expectedCloseDate` | → | `expectedCloseDate` |
| `orgDataID` | → | `orgDataID` |
| `entityID` | → | `entityID` |
| `leadSourceID` | → | `leadSourceID` |
| `assignedTeamID` | → | `assignedTeamID` |

### Prospect Updates
After conversion, the prospect record is updated:
- `salesProspectStatus` = `'closed'`
- `Suspended` = `'Y'`
- `convertedToSale` = `'Y'`
- `salesCaseID` = [ID of created sale]
- `conversionDate` = [Current timestamp]
- `convertedByID` = [Your user ID]

### Sale Creation
A new record is created in `tija_sales_cases` with:
- All transferred prospect data
- Selected sales stage
- User-entered sale details
- Current timestamp as creation date

---

## Validation & Error Handling

### Common Errors

**"Only qualified prospects can be converted to sales"**
- **Cause**: Prospect qualification status is not "Qualified"
- **Solution**: Update prospect qualification status to "Qualified" first

**"This prospect has already been converted to a sale"**
- **Cause**: Prospect was previously converted
- **Solution**: View the linked sale using the prospect's `salesCaseID`

**"Prospect ID and sales stage are required"**
- **Cause**: Missing required form fields
- **Solution**: Ensure sales stage is selected

**"Prospect not found"**
- **Cause**: Invalid prospect ID
- **Solution**: Verify you're accessing a valid prospect

---

## Best Practices

### Before Conversion
1. ✅ Ensure all prospect information is complete and accurate
2. ✅ Log all relevant interactions and notes
3. ✅ Verify client information is up-to-date
4. ✅ Confirm estimated value reflects current opportunity size
5. ✅ Set appropriate qualification status

### During Conversion
1. ✅ Select the most accurate sales stage
2. ✅ Adjust deal value if estimates have changed
3. ✅ Set realistic expected close date
4. ✅ Assign to appropriate sales owner
5. ✅ Document next steps clearly

### After Conversion
1. ✅ Review the created sales opportunity
2. ✅ Update sales stage as progress is made
3. ✅ Continue logging activities in the sales record
4. ✅ Monitor pipeline progression

---

## Frequently Asked Questions

**Q: Can I convert a prospect back after conversion?**
A: No, conversion is a one-way process. The prospect is deactivated and linked to the sale. However, you can create a new prospect if needed.

**Q: What happens to prospect interactions after conversion?**
A: Prospect interactions remain in the `tija_prospect_interactions` table and are still accessible via the prospect record.

**Q: Can I convert multiple prospects at once?**
A: Currently, prospects must be converted individually through the conversion modal.

**Q: What if I select the wrong sales stage?**
A: You can update the sales stage after conversion by editing the sales opportunity.

**Q: Will the prospect appear in prospect lists after conversion?**
A: No, converted prospects are filtered out of active prospect lists as they are marked as closed and suspended.

**Q: Can I see which sale a prospect was converted to?**
A: Yes, the prospect record contains a `salesCaseID` field that links to the created sale.

---

## Technical Details

### API Endpoint
**File**: `php/scripts/sales/convert_prospect_to_sale.php`

**Actions**:
- `getSalesStages` - Retrieves available sales stages
- `convertToSale` - Performs the conversion

### Database Tables Affected
- `tija_sales_prospects` - Updated with conversion flags
- `tija_sales_cases` - New sale record created
- `tija_sales_status_levels` - Referenced for sales stages

### Permissions
Users must have appropriate permissions to:
- View prospect details
- Create sales opportunities
- Update prospect status

---

## Troubleshooting

### Conversion Button Not Visible
**Check**:
1. Is prospect qualification status "Qualified"?
2. Is prospect already converted?
3. Is prospect suspended?

### Sales Stages Not Loading
**Check**:
1. Database connection is active
2. `tija_sales_status_levels` table exists and has data
3. Entity ID is correct (if entity-specific stages)

### Conversion Fails
**Check**:
1. All required fields are filled
2. Confirmation checkbox is checked
3. User has necessary permissions
4. Database connection is stable

### Redirect Not Working
**Check**:
1. Sale was created successfully (check database)
2. Browser console for JavaScript errors
3. Base URL configuration is correct

---

## Support

For additional assistance with prospect conversion:
1. Check system logs for detailed error messages
2. Verify database migrations have been applied
3. Contact your system administrator
4. Review the implementation plan documentation
