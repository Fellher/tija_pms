<?php
/**
 * Category Management API
 *
 * Create, update, delete categories
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

require_once __DIR__ . '/../../../includes.php';

header('Content-Type: application/json');

global $DBConn, $isValidUser, $userID;

// Check authentication
if (!$isValidUser) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check admin permissions
if (!$isAdmin && !$isValidAdmin) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Administrator privileges required']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($method) {
        case 'POST':
            if ($action === 'create') {
                $data = [
                    'categoryCode' => $_POST['categoryCode'] ?? '',
                    'categoryName' => $_POST['categoryName'] ?? '',
                    'categoryDescription' => $_POST['categoryDescription'] ?? '',
                    'displayOrder' => $_POST['displayOrder'] ?? 0,
                    'isActive' => $_POST['isActive'] ?? 'Y'
                ];

                if (empty($data['categoryCode']) || empty($data['categoryName'])) {
                    throw new Exception('Category Code and Name are required');
                }

                $categoryID = BAUTaxonomy::createCategory($data, $DBConn);

                if ($categoryID) {
                    echo json_encode(['success' => true, 'categoryID' => $categoryID, 'message' => 'Category created successfully']);
                } else {
                    throw new Exception('Failed to create category');
                }
            } elseif ($action === 'update') {
                $categoryID = $_POST['categoryID'] ?? null;
                if (!$categoryID) {
                    throw new Exception('Category ID is required');
                }

                $data = [];
                $allowedFields = ['categoryCode', 'categoryName', 'categoryDescription', 'displayOrder', 'isActive'];
                foreach ($allowedFields as $field) {
                    if (isset($_POST[$field])) {
                        $data[$field] = $_POST[$field];
                    }
                }

                $success = BAUTaxonomy::updateCategory($categoryID, $data, $DBConn);

                if ($success) {
                    echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
                } else {
                    throw new Exception('Failed to update category');
                }
            } else {
                throw new Exception('Invalid action');
            }
            break;

        case 'GET':
            if ($action === 'get') {
                $categoryID = $_GET['categoryID'] ?? null;
                if (!$categoryID) {
                    throw new Exception('Category ID is required');
                }

                $category = BAUTaxonomy::getCategories(['categoryID' => $categoryID], true, $DBConn);
                if ($category) {
                    echo json_encode(['success' => true, 'category' => $category]);
                } else {
                    throw new Exception('Category not found');
                }
            } else {
                throw new Exception('Invalid action');
            }
            break;

        default:
            throw new Exception('Method not allowed');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

