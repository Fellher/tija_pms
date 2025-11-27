# Leave Module CSS Files

This directory contains page-specific CSS files for the Leave Management module.

## How It Works

CSS files in this directory are automatically loaded by `html/index.php` based on the URL parameters:

```
URL: ?s=admin&ss=leave&p=leave_policy_management
CSS Loaded: assets/css/src/pages/admin/leave/leave_policy_management.php
```

## CSS Files

### Main Pages

1. **leave_policy_management.php**
   - Enterprise-grade styles for leave policy management
   - Includes sidebar, headers, modals, buttons, alerts
   - ~415 lines of CSS

2. **leave_types.php**
   - Leave types table and card styling
   - Action buttons and badges
   - ~155 lines of CSS

3. **accumulation_policies.php**
   - Policy cards and rules styling
   - Accrual badges
   - ~30 lines of CSS

4. **leave_entitlements.php**
   - Entitlement card styling
   - Hover effects
   - ~10 lines of CSS

## Adding New CSS Files

When creating CSS for a new page:

1. **File Naming**: Match the page name exactly
   - Page: `html/pages/admin/leave/my_page.php`
   - CSS: `assets/css/src/pages/admin/leave/my_page.php`

2. **File Structure**:
```php
<style>
/* Your CSS here */
.my-custom-class {
    /* styles */
}
</style>
```

3. **Best Practices**:
   - Use descriptive class names (BEM notation recommended)
   - Include comments for complex sections
   - Use CSS variables for colors (see `:root` in leave_policy_management.php)
   - Test responsive design
   - Follow existing patterns

## CSS Variables

Enterprise color palette (defined in `leave_policy_management.php`):

```css
:root {
    --enterprise-primary: #0052CC;
    --enterprise-secondary: #5E6C84;
    --enterprise-success: #00875A;
    --enterprise-danger: #DE350B;
    --enterprise-warning: #FF8B00;
    --enterprise-info: #0065FF;
    --enterprise-dark: #172B4D;
    --enterprise-light: #F4F5F7;
    --enterprise-border: #DFE1E6;
}
```

Use these variables for consistent theming across pages.

## Common Classes

### From leave_policy_management.php:
- `.enterprise-sidebar` - Sidebar navigation
- `.enterprise-page-header` - Page headers
- `.help-button-float` - Floating help button
- `.btn-enterprise` - Enterprise-style buttons
- `.enterprise-card` - Card components

### From leave_types.php:
- `.leave-type-table-card` - Table containers
- `.enterprise-table` - Data tables
- `.action-btn` - Action buttons
- `.empty-state` - Empty state messages

## Directory Structure

```
assets/css/src/pages/admin/leave/
├── README.md (this file)
├── leave_policy_management.php
├── leave_types.php
├── accumulation_policies.php
├── leave_entitlements.php
├── views/
│   └── (view-specific CSS files)
└── leave_policy_management/
    └── views/
        └── (nested view CSS files)
```

## Testing

After adding or modifying CSS:

1. Clear browser cache
2. Access the page via URL
3. Verify styles are applied
4. Test responsive design (mobile, tablet, desktop)
5. Check hover/active states
6. Verify cross-browser compatibility

## Notes

- All CSS files must use `<style>` tags (they are PHP-included)
- Files are loaded automatically - no manual inclusion needed
- CSS is only loaded for the specific page being accessed
- Maintain consistent naming and structure

---
Last Updated: November 6, 2025

