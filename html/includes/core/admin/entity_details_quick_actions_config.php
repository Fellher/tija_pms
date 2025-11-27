<?php
/**
 * Entity Details - Quick Actions Configuration
 *
 * This file defines the quick action buttons displayed on the entity details page.
 * Each action is configured with label, icon, button style, and action (modal/function/link).
 */

// Define quick actions configuration array
$quickActions = array(
    'edit_entity' => array(
        'label' => 'Edit Entity',
        'icon' => 'fa-edit',
        'btn_class' => 'btn-primary',
        'action_type' => 'modal',
        'action_target' => '#manageEntity',
        'data_attributes' => array(
            'data-id' => $entityID
        ),
        'additional_classes' => 'editEntity',
        'enabled' => true,
        'permission' => 'edit_entity'
    ),
    'add_department' => array(
        'label' => 'Add Department',
        'icon' => 'fa-plus',
        'btn_class' => 'btn-success',
        'action_type' => 'modal',
        'action_target' => '#manageUnitModal',
        'onclick' => "addUnitForEntity({$entityID}, 'Department')",
        'enabled' => true,
        'permission' => 'add_department'
    ),
    'add_employee' => array(
        'label' => 'Add Employee',
        'icon' => 'fa-user-plus',
        'btn_class' => 'btn-info',
        'action_type' => 'modal',
        'action_target' => '#addEmployeeModal',
        'onclick' => "addEmployee({$entityID})",
        'enabled' => true,
        'permission' => 'add_employee'
    ),
    'toggle_status' => array(
        'label' => (isset($entity->Suspended) && $entity->Suspended == 'N') ? 'Suspend' : 'Activate',
        'icon' => (isset($entity->Suspended) && $entity->Suspended == 'N') ? 'fa-ban' : 'fa-check',
        'btn_class' => (isset($entity->Suspended) && $entity->Suspended == 'N') ? 'btn-warning' : 'btn-success',
        'action_type' => 'function',
        'onclick' => "toggleEntitySuspension({$entityID}, '" . (isset($entity->Suspended) ? $entity->Suspended : 'N') . "')",
        'enabled' => true,
        'permission' => 'manage_entity_status'
    ),
    'separator' => array(
        'type' => 'separator',
        'enabled' => true
    ),
    'back_to_org' => array(
        'label' => 'Back to Organization',
        'icon' => 'fa-arrow-left',
        'btn_class' => 'btn-light',
        'action_type' => 'link',
        'href' => $base . 'html/?s=core&ss=admin&p=tenant_details&orgDataID=' . (isset($entity->orgDataID) ? $entity->orgDataID : ''),
        'additional_classes' => 'ms-auto',
        'enabled' => true,
        'permission' => null
    )
);

/**
 * Check if user has permission for an action
 * You can customize this function based on your permission system
 */
function hasQuickActionPermission($permission) {
    // Placeholder - implement your permission checking logic here
    // For now, return true (all actions allowed)
    // You can check against user roles, permissions table, etc.

    if ($permission === null) {
        return true; // No permission required
    }

    // Example permission check (customize as needed):
    // global $userPermissions;
    // return isset($userPermissions[$permission]) && $userPermissions[$permission];

    return true; // Default: allow all actions
}
?>

