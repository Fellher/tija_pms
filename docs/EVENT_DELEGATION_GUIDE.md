# Event Delegation Implementation Guide
## Global Event Delegation System for TIJA PMS

### Overview
This guide explains how to use the global event delegation system implemented across the TIJA PMS application. Event delegation ensures that event listeners work with dynamically added/removed DOM elements without needing to reattach listeners after DOM updates.

---

## Benefits

1. **Works with Dynamic Content** - Event listeners automatically work with elements added/removed from the DOM
2. **Better Performance** - Fewer event listeners (one per event type instead of one per element)
3. **No Reattachment Needed** - No need to reattach listeners after DOM updates
4. **Consistent Pattern** - Standardized approach across the entire application

---

## Global Event Delegation System

The global event delegation system is loaded automatically via `html/includes/core/footer_scripts.php` and provides:

- `EventDelegation` - Main delegation manager object
- `delegateClick()` - Convenience function for click events
- `delegateChange()` - Convenience function for change events
- `delegateSubmit()` - Convenience function for submit events

---

## Usage Examples

### Method 1: Using EventDelegation Object (Recommended)

```javascript
// Register a click handler for delete buttons
EventDelegation.on('.deleteProject', 'click', function(e, target) {
    e.preventDefault();
    const projectId = target.getAttribute('data-project-id');
    handleDeleteProject(target);
}, {}, document);

// Register a change handler for select elements
EventDelegation.on('.status-select', 'change', function(e, target) {
    const newStatus = target.value;
    updateStatus(newStatus);
}, {}, document);
```

### Method 2: Using Convenience Functions

```javascript
// Click delegation
delegateClick('.editProjectCase', function(e, target) {
    e.preventDefault();
    const projectId = target.getAttribute('data-project-id');
    editProject(projectId);
});

// Change delegation
delegateChange('.filter-select', function(e, target) {
    applyFilter(target.value);
});
```

### Method 3: Fallback Pattern (If EventDelegation Not Available)

```javascript
// Use document-level event delegation as fallback
document.addEventListener('click', function(e) {
    const deleteBtn = e.target.closest('.deleteProject');
    if (deleteBtn) {
        e.preventDefault();
        handleDeleteProject(deleteBtn);
    }

    const editBtn = e.target.closest('.editProjectCase');
    if (editBtn) {
        e.preventDefault();
        handleEditProject(editBtn);
    }
});
```

---

## Migration from Direct Event Listeners

### Before (Direct Event Listeners - DON'T USE)

```javascript
// ❌ This pattern breaks with dynamically added elements
document.querySelectorAll('.deleteProject').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        handleDeleteProject(this);
    });
});
```

### After (Event Delegation - USE THIS)

```javascript
// ✅ This pattern works with dynamically added elements
if (typeof EventDelegation !== 'undefined') {
    EventDelegation.on('.deleteProject', 'click', function(e, target) {
        e.preventDefault();
        handleDeleteProject(target);
    }, {}, document);
} else {
    // Fallback
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.deleteProject');
        if (btn) {
            e.preventDefault();
            handleDeleteProject(btn);
        }
    });
}
```

---

## Common Patterns

### Delete Buttons
```javascript
EventDelegation.on('.delete-item', 'click', function(e, target) {
    e.preventDefault();
    const itemId = target.getAttribute('data-item-id');
    deleteItem(itemId);
}, {}, document);
```

### Edit Buttons
```javascript
EventDelegation.on('.edit-item', 'click', function(e, target) {
    e.preventDefault();
    const itemId = target.getAttribute('data-item-id');
    editItem(itemId);
}, {}, document);
```

### Form Submissions
```javascript
EventDelegation.on('.ajax-form', 'submit', function(e, target) {
    e.preventDefault();
    submitForm(target);
}, {}, document);
```

### Select Changes
```javascript
EventDelegation.on('.filter-select', 'change', function(e, target) {
    applyFilter(target.value);
}, {}, document);
```

---

## Scoped Delegation (Container-Specific)

For better performance, you can scope delegation to specific containers:

```javascript
const tableContainer = document.getElementById('projectsTable');
EventDelegation.on('.deleteProject', 'click', function(e, target) {
    handleDeleteProject(target);
}, {}, tableContainer);
```

---

## Best Practices

1. **Always use event delegation** for elements that may be dynamically added/removed
2. **Use `e.target.closest()`** in fallback patterns to find the actual button
3. **Use `target` parameter** instead of `this` in delegation handlers
4. **Prevent default** when handling link/button clicks
5. **Check for EventDelegation availability** before using it
6. **Provide fallback** for compatibility

---

## Files Already Updated

- ✅ `html/pages/user/projects/home.php` - Project delete/edit buttons
- ✅ `html/includes/core/admin/entity_details_scripts.php` - Edit unit/business unit buttons
- ✅ `html/includes/scripts/clients/client_relationship_management_script.php` - Relationship buttons

---

## Files That Need Migration

The following files still use direct event listeners and should be migrated:

- `html/includes/scripts/projects/project_phase.php` - Task management buttons
- `html/includes/scripts/projects/modals/manage_project_plan_templates.php` - Template buttons
- Other files using `querySelectorAll().forEach().addEventListener()` pattern

---

## API Reference

### EventDelegation.on(selector, eventType, handler, options, container)

Register an event delegation handler.

**Parameters:**
- `selector` (string) - CSS selector for target elements
- `eventType` (string) - Event type ('click', 'change', 'submit', etc.)
- `handler` (Function) - Handler function `(event, target) => {}`
- `options` (Object) - Event listener options (optional)
- `container` (HTMLElement|Document) - Container element (default: document)

**Returns:** Unregister function

### EventDelegation.off(selector, eventType, handler, container)

Unregister an event delegation handler.

**Parameters:**
- `selector` (string) - CSS selector
- `eventType` (string) - Event type
- `handler` (Function) - Handler function (optional, removes all if not provided)
- `container` (HTMLElement|Document) - Container element (default: document)

---

## Support

For questions or issues with event delegation, refer to this guide or contact the development team.

