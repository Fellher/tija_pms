<?php
/**
 * Salary Components API
 * Handles AJAX requests for salary components management
 * - Component CRUD operations
 * - Category CRUD operations
 * - Employee component assignments
 */

session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

try {
    if (!$isValidUser) {
        throw new Exception('You must be logged in to perform this action');
    }

    if (!$isAdmin && !$isValidAdmin) {
        throw new Exception('You do not have permission to perform this action');
    }

    $action = isset($_GET['action']) ? Utility::clean_string($_GET['action']) : (isset($_POST['action']) ? Utility::clean_string($_POST['action']) : '');

    if (!$action) {
        throw new Exception('No action specified');
    }

    $DBConn->begin();

    switch ($action) {
        // ========================================
        // COMPONENT OPERATIONS
        // ========================================

        case 'save_component':
            $componentID = isset($_POST['salaryComponentID']) && !empty($_POST['salaryComponentID']) ?
                Utility::clean_string($_POST['salaryComponentID']) : null;

            $data = [
                'componentCode' => Utility::clean_string($_POST['componentCode'] ?? ''),
                'salaryComponentTitle' => Utility::clean_string($_POST['salaryComponentTitle'] ?? ''),
                'salaryComponentDescription' => Utility::clean_string($_POST['salaryComponentDescription'] ?? ''),
                'salaryComponentCategoryID' => Utility::clean_string($_POST['salaryComponentCategoryID'] ?? ''),
                'salaryComponentType' => Utility::clean_string($_POST['salaryComponentType'] ?? ''),
                'salaryComponentValueType' => Utility::clean_string($_POST['salaryComponentValueType'] ?? ''),
                'defaultValue' => floatval($_POST['defaultValue'] ?? 0),
                'applyTo' => Utility::clean_string($_POST['applyTo'] ?? 'basic_salary'),
                'isStatutory' => isset($_POST['isStatutory']) ? 'Y' : 'N',
                'isMandatory' => isset($_POST['isMandatory']) ? 'Y' : 'N',
                'isTaxable' => isset($_POST['isTaxable']) ? 'Y' : 'N',
                'isVisible' => isset($_POST['isVisible']) ? 'Y' : 'N',
                'sortOrder' => intval($_POST['sortOrder'] ?? 0),
                'LastUpdatedByID' => $userDetails->ID
            ];

            // Validate required fields
            if (empty($data['componentCode']) || empty($data['salaryComponentTitle']) || empty($data['salaryComponentCategoryID'])) {
                throw new Exception('Component code, title, and category are required');
            }

            if ($componentID) {
                // Update - preserve existing orgDataID and entityID
                $existingComponent = Data::salary_components(['salaryComponentID' => $componentID], true, $DBConn);
                if (!$existingComponent) {
                    throw new Exception('Component not found');
                }

                // Only update orgDataID/entityID if they're provided and valid, otherwise keep existing
                if (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) {
                    $data['orgDataID'] = Utility::clean_string($_POST['orgDataID']);
                } elseif ($existingComponent->orgDataID) {
                    $data['orgDataID'] = $existingComponent->orgDataID;
                } elseif ($userDetails->orgDataID) {
                    $data['orgDataID'] = $userDetails->orgDataID;
                }

                if (isset($_POST['entityID']) && !empty($_POST['entityID'])) {
                    $data['entityID'] = Utility::clean_string($_POST['entityID']);
                } elseif ($existingComponent->entityID) {
                    $data['entityID'] = $existingComponent->entityID;
                } elseif ($userDetails->entityID) {
                    $data['entityID'] = $userDetails->entityID;
                }

                if (!$DBConn->update_table('tija_salary_components', $data, ['salaryComponentID' => $componentID])) {
                    throw new Exception('Failed to update salary component');
                }
                $response['message'] = 'Salary component updated successfully';
            } else {
                // Insert - use provided values or fall back to user's values
                $data['orgDataID'] = isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])
                    ? Utility::clean_string($_POST['orgDataID'])
                    : ($userDetails->orgDataID ?? null);

                $data['entityID'] = isset($_POST['entityID']) && !empty($_POST['entityID'])
                    ? Utility::clean_string($_POST['entityID'])
                    : ($userDetails->entityID ?? null);

                // Ensure we have entityID - critical for multi-tenant data integrity
                if (empty($data['entityID'])) {
                    throw new Exception('EntityID is required. Please ensure you are logged in with proper entity assignment.');
                }

                // Ensure we have orgDataID
                if (empty($data['orgDataID'])) {
                    throw new Exception('Organization ID is required. Please ensure you are logged in with proper organization assignment.');
                }

                if (!$DBConn->insert_data('tija_salary_components', $data)) {
                    throw new Exception('Failed to create salary component');
                }
                $response['message'] = 'Salary component created successfully';
            }

            $response['success'] = true;
            break;

        case 'get':
            $componentID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$componentID) {
                throw new Exception('Component ID is required');
            }

            $component = Data::salary_components(['salaryComponentID' => $componentID], true, $DBConn);
            if (!$component) {
                throw new Exception('Component not found');
            }

            $response['success'] = true;
            $response['data'] = $component;
            break;

        case 'delete':
            $componentID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$componentID) {
                throw new Exception('Component ID is required');
            }

            // Soft delete
            if (!$DBConn->update_table('tija_salary_components', ['Suspended' => 'Y'], ['salaryComponentID' => $componentID])) {
                throw new Exception('Failed to delete salary component');
            }

            $response['success'] = true;
            $response['message'] = 'Salary component deleted successfully';
            break;

        // ========================================
        // CATEGORY OPERATIONS
        // ========================================

        case 'save_category':
            $categoryID = isset($_POST['salaryComponentCategoryID']) && !empty($_POST['salaryComponentCategoryID']) ?
                Utility::clean_string($_POST['salaryComponentCategoryID']) : null;

            $data = [
                'categoryCode' => Utility::clean_string($_POST['categoryCode'] ?? ''),
                'salaryComponentCategoryTitle' => Utility::clean_string($_POST['salaryComponentCategoryTitle'] ?? ''),
                'salaryComponentCategoryDescription' => Utility::clean_string($_POST['salaryComponentCategoryDescription'] ?? ''),
                'categoryType' => Utility::clean_string($_POST['categoryType'] ?? ''),
                'sortOrder' => intval($_POST['categorySortOrder'] ?? 0),
                'LastUpdatedByID' => $userDetails->ID
            ];

            // Validate required fields
            if (empty($data['categoryCode']) || empty($data['salaryComponentCategoryTitle']) || empty($data['categoryType'])) {
                throw new Exception('Category code, title, and type are required');
            }

            if ($categoryID) {
                // Update - preserve existing orgDataID and entityID
                $existingCategory = Data::salary_component_categories(['salaryComponentCategoryID' => $categoryID], true, $DBConn);
                if (!$existingCategory) {
                    throw new Exception('Category not found');
                }

                // Only update orgDataID/entityID if they're provided and valid, otherwise keep existing
                if (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) {
                    $data['orgDataID'] = Utility::clean_string($_POST['orgDataID']);
                } elseif ($existingCategory->orgDataID) {
                    $data['orgDataID'] = $existingCategory->orgDataID;
                } elseif ($userDetails->orgDataID) {
                    $data['orgDataID'] = $userDetails->orgDataID;
                }

                if (isset($_POST['entityID']) && !empty($_POST['entityID'])) {
                    $data['entityID'] = Utility::clean_string($_POST['entityID']);
                } elseif ($existingCategory->entityID) {
                    $data['entityID'] = $existingCategory->entityID;
                } elseif ($userDetails->entityID) {
                    $data['entityID'] = $userDetails->entityID;
                }

                if (!$DBConn->update_table('tija_salary_component_category', $data, ['salaryComponentCategoryID' => $categoryID])) {
                    throw new Exception('Failed to update category');
                }
                $response['message'] = 'Category updated successfully';
            } else {
                // Insert - use provided values or fall back to user's values
                $data['orgDataID'] = isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])
                    ? Utility::clean_string($_POST['orgDataID'])
                    : ($userDetails->orgDataID ?? null);

                $data['entityID'] = isset($_POST['entityID']) && !empty($_POST['entityID'])
                    ? Utility::clean_string($_POST['entityID'])
                    : ($userDetails->entityID ?? null);

                // Ensure we have entityID - critical for multi-tenant data integrity
                if (empty($data['entityID'])) {
                    throw new Exception('EntityID is required. Please ensure you are logged in with proper entity assignment.');
                }

                // Ensure we have orgDataID
                if (empty($data['orgDataID'])) {
                    throw new Exception('Organization ID is required. Please ensure you are logged in with proper organization assignment.');
                }

                if (!$DBConn->insert_data('tija_salary_component_category', $data)) {
                    throw new Exception('Failed to create category');
                }
                $response['message'] = 'Category created successfully';
            }

            $response['success'] = true;
            break;

        case 'delete_category':
            $categoryID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$categoryID) {
                throw new Exception('Category ID is required');
            }

            // Check if it's a system category
            $category = Data::salary_component_categories(['salaryComponentCategoryID' => $categoryID], true, $DBConn);
            if ($category && $category->isSystemCategory == 'Y') {
                throw new Exception('Cannot delete system categories');
            }

            // Check if any components are using this category
            $components = Data::salary_components(['salaryComponentCategoryID' => $categoryID, 'Suspended' => 'N'], false, $DBConn);
            if ($components) {
                throw new Exception('Cannot delete category with associated components. Please reassign or delete the components first.');
            }

            // Soft delete
            if (!$DBConn->update_table('tija_salary_component_category', ['Suspended' => 'Y'], ['salaryComponentCategoryID' => $categoryID])) {
                throw new Exception('Failed to delete category');
            }

            $response['success'] = true;
            $response['message'] = 'Category deleted successfully';
            break;

        // ========================================
        // EMPLOYEE COMPONENT ASSIGNMENT OPERATIONS
        // ========================================

        case 'save_employee_component':
            $employeeComponentID = isset($_POST['employeeComponentID']) && !empty($_POST['employeeComponentID']) ?
                Utility::clean_string($_POST['employeeComponentID']) : null;

            $employeeID = Utility::clean_string($_POST['employeeID'] ?? '');
            $salaryComponentID = Utility::clean_string($_POST['salaryComponentID'] ?? '');
            $componentValue = floatval($_POST['componentValue'] ?? 0);
            $effectiveDate = Utility::clean_string($_POST['effectiveDate'] ?? date('Y-m-d'));
            $frequency = Utility::clean_string($_POST['frequency'] ?? 'monthly');
            $isActive = isset($_POST['isActive']) && $_POST['isActive'] == 'Y' ? 'Y' : 'N';
            $notes = Utility::clean_string($_POST['notes'] ?? '');
            $oneTimePayrollDate = isset($_POST['oneTimePayrollDate']) && !empty($_POST['oneTimePayrollDate']) ?
                Utility::clean_string($_POST['oneTimePayrollDate']) : null;

            // Validate required fields
            if (empty($employeeID) || empty($salaryComponentID)) {
                throw new Exception('Employee and salary component are required');
            }

            // Get component details to determine value type
            $component = Data::salary_components(['salaryComponentID' => $salaryComponentID], true, $DBConn);
            if (!$component) {
                throw new Exception('Invalid salary component');
            }

            $data = [
                'employeeID' => $employeeID,
                'salaryComponentID' => $salaryComponentID,
                'componentValue' => $componentValue,
                'valueType' => $component->salaryComponentValueType,
                'applyTo' => $component->applyTo,
                'effectiveDate' => $effectiveDate,
                'isCurrent' => 'Y',
                'isActive' => $isActive,
                'frequency' => $frequency,
                'notes' => $notes,
                'updatedBy' => $userDetails->ID,
                'updatedAt' => date('Y-m-d H:i:s')
            ];

            if ($oneTimePayrollDate) {
                $data['oneTimePayrollDate'] = $oneTimePayrollDate;
            }

            if ($employeeComponentID) {
                // Update existing assignment
                if (!$DBConn->update_table('tija_employee_salary_components', $data, ['employeeComponentID' => $employeeComponentID])) {
                    throw new Exception('Failed to update component assignment');
                }
                $response['message'] = 'Component assignment updated successfully';
            } else {
                // Create new assignment
                $data['assignedBy'] = $userDetails->ID;
                $data['assignedAt'] = date('Y-m-d H:i:s');

                // Check if this component is already assigned and active
                $existing = Data::employee_salary_components([
                    'employeeID' => $employeeID,
                    'salaryComponentID' => $salaryComponentID,
                    'isCurrent' => 'Y',
                    'Suspended' => 'N'
                ], true, $DBConn);

                if ($existing) {
                    throw new Exception('This component is already assigned to the employee. Please edit the existing assignment.');
                }

                if (!$DBConn->insert_data('tija_employee_salary_components', $data)) {
                    throw new Exception('Failed to create component assignment');
                }
                $response['message'] = 'Component assigned successfully';
            }

            $response['success'] = true;
            break;

        case 'get_employee_component':
            $employeeComponentID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$employeeComponentID) {
                throw new Exception('Employee component ID is required');
            }

            $assignment = Data::employee_salary_components(['employeeComponentID' => $employeeComponentID], true, $DBConn);
            if (!$assignment) {
                throw new Exception('Component assignment not found');
            }

            $response['success'] = true;
            $response['data'] = $assignment;
            break;

        case 'remove_employee_component':
            $employeeComponentID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$employeeComponentID) {
                throw new Exception('Employee component ID is required');
            }

            // Soft delete
            if (!$DBConn->update_table('tija_employee_salary_components',
                ['Suspended' => 'Y', 'isCurrent' => 'N', 'isActive' => 'N'],
                ['employeeComponentID' => $employeeComponentID])) {
                throw new Exception('Failed to remove component assignment');
            }

            $response['success'] = true;
            $response['message'] = 'Component assignment removed successfully';
            break;

        default:
            throw new Exception('Invalid action specified');
    }

    if ($response['success']) {
        $DBConn->commit();
    } else {
        $DBConn->rollback();
    }

} catch (Exception $e) {
    $DBConn->rollback();
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
