<?php
/**
 * Entity Details - Statistics Configuration
 *
 * This file defines the statistics cards displayed on the entity details page.
 * Each statistic is configured with label, count, description, icon, and color.
 */

// Define statistics cards configuration array
$statisticsCards = array(
    'employees' => array(
        'label' => 'Employees',
        'count' => $employeeCount,
        'description' => 'Active employees',
        'icon' => 'fa-users',
        'color' => 'primary',
        'enabled' => true,
        'link' => '?s=core&ss=admin&p=entity_details&entityID=' . $entityID . '&tab=employees'
    ),
    'units' => array(
        'label' => 'Units',
        'count' => $unitsCount,
        'description' => 'Dept/Sections/Teams',
        'icon' => 'fa-sitemap',
        'color' => 'success',
        'enabled' => true,
        'link' => '?s=core&ss=admin&p=entity_details&entityID=' . $entityID . '&tab=units'
    ),
    'business_units' => array(
        'label' => 'Business Units',
        'count' => $businessUnitsCount,
        'description' => 'Cost/Profit centers',
        'icon' => 'fa-chart-line',
        'color' => 'warning',
        'enabled' => true,
        'link' => '?s=core&ss=admin&p=entity_details&entityID=' . $entityID . '&tab=business_units'
    ),
    'departments' => array(
        'label' => 'Departments',
        'count' => $departmentCount,
        'description' => 'Active departments',
        'icon' => 'fa-building',
        'color' => 'info',
        'enabled' => true,
        'link' => '?s=core&ss=admin&p=entity_details&entityID=' . $entityID . '&tab=overview'
    ),
    'child_entities' => array(
        'label' => 'Child Entities',
        'count' => $childCount,
        'description' => 'Sub-entities',
        'icon' => 'fa-project-diagram',
        'color' => 'secondary',
        'enabled' => true,
        'link' => '?s=core&ss=admin&p=entity_details&entityID=' . $entityID . '&tab=overview'
    ),
    'status' => array(
        'label' => 'Status',
        'count' => null, // Special handling - shows badge instead of number
        'description' => 'Current status',
        'icon' => (isset($entity->Suspended) && $entity->Suspended == 'N') ? 'fa-check-circle' : 'fa-ban',
        'color' => 'danger',
        'badge' => array(
            'text' => (isset($entity->Suspended) && $entity->Suspended == 'N') ? 'Active' : 'Suspended',
            'color' => (isset($entity->Suspended) && $entity->Suspended == 'N') ? 'success' : 'danger'
        ),
        'enabled' => true,
        'link' => null
    )
);
?>

