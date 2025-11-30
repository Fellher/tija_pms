<?php
/**
 * API Endpoint: Manage Functional Areas
 * Handles CRUD operations for functional areas
 */

require_once '../../../includes.php';

header('Content-Type: application/json');

global $DBConn, $isValidUser;

try {
    $action = $_GET['action'] ?? $_POST['action'] ?? null;
    if (!$action) {
        throw new Exception('Action is required');
    }

    // Check authentication
    if (!$isValidUser) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $userID = $_SESSION['userID'] ?? null;
    if (!$userID) {
        throw new Exception('User not authenticated');
    }

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            if ($action === 'create') {
                $functionalAreaCode = $_POST['functionalAreaCode'] ?? null;
                $functionalAreaName = $_POST['functionalAreaName'] ?? null;
                $functionalAreaDescription = $_POST['functionalAreaDescription'] ?? '';
                $isShared = $_POST['isShared'] ?? 'Y';
                $displayOrder = $_POST['displayOrder'] ?? 0;

                if (!$functionalAreaCode || !$functionalAreaName) {
                    throw new Exception('Functional Area Code and Name are required');
                }

                // Check if code already exists
                $existing = $DBConn->retrieve_db_table_rows(
                    'tija_functional_areas',
                    ['functionalAreaID'],
                    ['functionalAreaCode' => $functionalAreaCode]
                );
                if ($existing && count($existing) > 0) {
                    throw new Exception('Functional Area Code already exists');
                }

                $cols = [
                    'functionalAreaCode', 'functionalAreaName', 'functionalAreaDescription',
                    'isShared', 'isActive', 'displayOrder', 'createdByID'
                ];
                $values = [
                    $functionalAreaCode, $functionalAreaName, $functionalAreaDescription,
                    $isShared, 'Y', $displayOrder, $userID
                ];

                $functionalAreaID = $DBConn->insert_db_table_row('tija_functional_areas', $cols, $values);

                // If not shared, automatically link to user's organization
                if ($isShared === 'N') {
                    $orgDataID = $_SESSION['orgDataID'] ?? null;
                    if ($orgDataID) {
                        $linkCols = ['orgDataID', 'functionalAreaID', 'isActive', 'LastUpdatedByID'];
                        $linkValues = [$orgDataID, $functionalAreaID, 'Y', $userID];
                        $DBConn->insert_db_table_row('tija_organization_functional_areas', $linkCols, $linkValues);
                    }
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Functional area created successfully',
                    'functionalAreaID' => $functionalAreaID
                ]);
            } elseif ($action === 'update') {
                $functionalAreaID = $_POST['functionalAreaID'] ?? null;
                if (!$functionalAreaID) {
                    throw new Exception('Functional Area ID is required');
                }

                $updateData = [];
                if (isset($_POST['functionalAreaCode'])) $updateData['functionalAreaCode'] = $_POST['functionalAreaCode'];
                if (isset($_POST['functionalAreaName'])) $updateData['functionalAreaName'] = $_POST['functionalAreaName'];
                if (isset($_POST['functionalAreaDescription'])) $updateData['functionalAreaDescription'] = $_POST['functionalAreaDescription'];
                if (isset($_POST['isShared'])) $updateData['isShared'] = $_POST['isShared'];
                if (isset($_POST['isActive'])) $updateData['isActive'] = $_POST['isActive'];
                if (isset($_POST['displayOrder'])) $updateData['displayOrder'] = $_POST['displayOrder'];
                $updateData['LastUpdatedByID'] = $userID;

                if (empty($updateData)) {
                    throw new Exception('No data to update');
                }

                // Check code uniqueness if updating code
                if (isset($updateData['functionalAreaCode'])) {
                    $existing = $DBConn->retrieve_db_table_rows(
                        'tija_functional_areas',
                        ['functionalAreaID'],
                        ['functionalAreaCode' => $updateData['functionalAreaCode']]
                    );
                    if ($existing && count($existing) > 0 && $existing[0]['functionalAreaID'] != $functionalAreaID) {
                        throw new Exception('Functional Area Code already exists');
                    }
                }

                $DBConn->update_table(
                    'tija_functional_areas',
                    $updateData,
                    ['functionalAreaID' => $functionalAreaID]
                );

                echo json_encode([
                    'success' => true,
                    'message' => 'Functional area updated successfully'
                ]);
            } elseif ($action === 'delete') {
                $functionalAreaID = $_POST['functionalAreaID'] ?? null;
                if (!$functionalAreaID) {
                    throw new Exception('Functional Area ID is required');
                }

                // Check if functional area is in use
                $tables = [
                    'tija_bau_processes',
                    'tija_workflows',
                    'tija_sops',
                    'tija_operational_task_templates',
                    'tija_operational_projects',
                    'tija_function_head_assignments'
                ];

                foreach ($tables as $table) {
                    $inUse = $DBConn->retrieve_db_table_rows(
                        $table,
                        ['COUNT(*) as count'],
                        ['functionalAreaID' => $functionalAreaID]
                    );
                    if ($inUse && $inUse[0]['count'] > 0) {
                        throw new Exception("Cannot delete functional area: it is in use by {$table}");
                    }
                }

                // Soft delete
                $DBConn->update_table(
                    'tija_functional_areas',
                    ['isActive' => 'N', 'LastUpdatedByID' => $userID],
                    ['functionalAreaID' => $functionalAreaID]
                );

                echo json_encode([
                    'success' => true,
                    'message' => 'Functional area deleted successfully'
                ]);
            } elseif ($action === 'link_organization') {
                $functionalAreaID = $_POST['functionalAreaID'] ?? null;
                $orgDataID = $_POST['orgDataID'] ?? $_SESSION['orgDataID'] ?? null;

                if (!$functionalAreaID || !$orgDataID) {
                    throw new Exception('Functional Area ID and Organization ID are required');
                }

                // Check if link already exists
                $existing = $DBConn->retrieve_db_table_rows(
                    'tija_organization_functional_areas',
                    ['linkID'],
                    ['orgDataID' => $orgDataID, 'functionalAreaID' => $functionalAreaID]
                );

                if ($existing && count($existing) > 0) {
                    // Update existing link
                    $DBConn->update_table(
                        'tija_organization_functional_areas',
                        ['isActive' => 'Y', 'LastUpdatedByID' => $userID],
                        ['linkID' => $existing[0]['linkID']]
                    );
                } else {
                    // Create new link
                    $cols = ['orgDataID', 'functionalAreaID', 'isActive', 'LastUpdatedByID'];
                    $values = [$orgDataID, $functionalAreaID, 'Y', $userID];
                    $DBConn->insert_db_table_row('tija_organization_functional_areas', $cols, $values);
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Functional area linked to organization successfully'
                ]);
            } elseif ($action === 'unlink_organization') {
                $functionalAreaID = $_POST['functionalAreaID'] ?? null;
                $orgDataID = $_POST['orgDataID'] ?? $_SESSION['orgDataID'] ?? null;

                if (!$functionalAreaID || !$orgDataID) {
                    throw new Exception('Functional Area ID and Organization ID are required');
                }

                $DBConn->update_table(
                    'tija_organization_functional_areas',
                    ['isActive' => 'N', 'LastUpdatedByID' => $userID],
                    ['orgDataID' => $orgDataID, 'functionalAreaID' => $functionalAreaID]
                );

                echo json_encode([
                    'success' => true,
                    'message' => 'Functional area unlinked from organization successfully'
                ]);
            } else {
                throw new Exception('Invalid action');
            }
            break;

        case 'GET':
            if ($action === 'get') {
                $functionalAreaID = $_GET['functionalAreaID'] ?? null;
                if (!$functionalAreaID) {
                    throw new Exception('Functional Area ID is required');
                }

                $cols = [
                    'functionalAreaID', 'functionalAreaCode', 'functionalAreaName',
                    'functionalAreaDescription', 'isShared', 'isActive', 'displayOrder',
                    'DateAdded', 'LastUpdate'
                ];
                $functionalArea = $DBConn->retrieve_db_table_rows(
                    'tija_functional_areas',
                    $cols,
                    ['functionalAreaID' => $functionalAreaID],
                    true
                );

                if ($functionalArea) {
                    echo json_encode(['success' => true, 'functionalArea' => $functionalArea]);
                } else {
                    throw new Exception('Functional area not found');
                }
            } elseif ($action === 'list') {
                $orgDataID = $_GET['orgDataID'] ?? $_SESSION['orgDataID'] ?? null;
                $includeShared = $_GET['includeShared'] ?? 'Y';
                $isActive = $_GET['isActive'] ?? 'Y';

                // Get all functional areas
                $cols = [
                    'functionalAreaID', 'functionalAreaCode', 'functionalAreaName',
                    'functionalAreaDescription', 'isShared', 'isActive', 'displayOrder'
                ];

                $where = ['isActive' => $isActive];
                $allFunctionalAreas = $DBConn->retrieve_db_table_rows('tija_functional_areas', $cols, $where);

                // If organization specified, filter by shared or linked
                if ($orgDataID) {
                    $linkedAreas = $DBConn->retrieve_db_table_rows(
                        'tija_organization_functional_areas',
                        ['functionalAreaID'],
                        ['orgDataID' => $orgDataID, 'isActive' => 'Y']
                    );
                    $linkedIDs = array_column($linkedAreas, 'functionalAreaID');

                    $functionalAreas = array_filter($allFunctionalAreas, function($fa) use ($linkedIDs, $includeShared) {
                        return $fa['isShared'] === 'Y' || in_array($fa['functionalAreaID'], $linkedIDs);
                    });
                } else {
                    if ($includeShared === 'Y') {
                        $functionalAreas = array_filter($allFunctionalAreas, function($fa) {
                            return $fa['isShared'] === 'Y';
                        });
                    } else {
                        $functionalAreas = $allFunctionalAreas;
                    }
                }

                // Sort by display order and name
                usort($functionalAreas, function($a, $b) {
                    if ($a['displayOrder'] != $b['displayOrder']) {
                        return $a['displayOrder'] <=> $b['displayOrder'];
                    }
                    return strcmp($a['functionalAreaName'], $b['functionalAreaName']);
                });

                echo json_encode([
                    'success' => true,
                    'functionalAreas' => array_values($functionalAreas)
                ]);
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

