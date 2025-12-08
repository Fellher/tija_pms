<?php
/**
 * Goal Management Script
 * Handles CRUD operations for goals
 *
 * @package    TIJA_PMS
 * @subpackage Goals
 */

session_start();
$base = '../../../';
set_include_path($base);
include 'php/includes.php';

header('Content-Type: application/json');

// Check authentication
if (!$isValidUser) {
    echo json_encode(array('success' => false, 'message' => 'Unauthorized access'));
    exit;
}

require_once 'php/classes/goal.php';
require_once 'php/classes/goalpermissions.php';

$action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : '';
$response = array('success' => false, 'message' => 'Invalid action');

try {
    switch ($action) {
        case 'create':
            if (!GoalPermissions::canCreate($userDetails->ID, $DBConn)) {
                $response = array('success' => false, 'message' => 'Permission denied: Cannot create goals');
            } else {
                $response = createGoal($DBConn, $userDetails);
            }
            break;

        case 'update':
            $goalUUID = isset($_POST['goalUUID']) ? Utility::clean_string($_POST['goalUUID']) : '';
            if (!GoalPermissions::canEdit($goalUUID, $userDetails->ID, $DBConn)) {
                $response = array('success' => false, 'message' => 'Permission denied: Cannot edit this goal');
            } else {
                $response = updateGoal($DBConn, $userDetails);
            }
            break;

        case 'delete':
            $goalUUID = isset($_POST['goalUUID']) ? Utility::clean_string($_POST['goalUUID']) : '';
            if (!GoalPermissions::canDelete($goalUUID, $userDetails->ID, $DBConn)) {
                $response = array('success' => false, 'message' => 'Permission denied: Cannot delete this goal');
            } else {
                $response = deleteGoal($DBConn, $userDetails);
            }
            break;

        case 'get':
            $response = getGoal($DBConn);
            break;

        case 'list':
            $response = listGoals($DBConn);
            break;

        case 'validate_weights':
            $response = validateWeights($DBConn);
            break;

        default:
            $response = array('success' => false, 'message' => 'Invalid action');
    }
} catch (Exception $e) {
    $response = array('success' => false, 'message' => 'Error: ' . $e->getMessage());
}

echo json_encode($response);
exit;

/**
 * Create a new goal
 */
function createGoal($DBConn, $userDetails) {
    $errors = array();

    // Required fields
    $goalType = isset($_POST['goalType']) ? Utility::clean_string($_POST['goalType']) : 'Strategic';
    $goalTitle = isset($_POST['goalTitle']) ? Utility::clean_string($_POST['goalTitle']) : '';
    $startDate = isset($_POST['startDate']) ? Utility::clean_string($_POST['startDate']) : date('Y-m-d');
    $endDate = isset($_POST['endDate']) ? Utility::clean_string($_POST['endDate']) : date('Y-m-d', strtotime('+1 year'));

    // Validation
    if (empty($goalTitle)) {
        $errors[] = 'Goal title is required';
    }
    if (empty($startDate) || empty($endDate)) {
        $errors[] = 'Start date and end date are required';
    }
    if (strtotime($endDate) < strtotime($startDate)) {
        $errors[] = 'End date must be after start date';
    }

    if (count($errors) > 0) {
        return array('success' => false, 'message' => implode(', ', $errors));
    }

    // Prepare goal data
    $goalData = array(
        'parentGoalUUID' => isset($_POST['parentGoalUUID']) ? Utility::clean_string($_POST['parentGoalUUID']) : null,
        'ownerEntityID' => isset($_POST['ownerEntityID']) ? intval($_POST['ownerEntityID']) : null,
        'ownerUserID' => isset($_POST['ownerUserID']) ? intval($_POST['ownerUserID']) : $userDetails->ID,
        'libraryRefID' => isset($_POST['libraryRefID']) ? intval($_POST['libraryRefID']) : null,
        'goalType' => $goalType,
        'goalTitle' => $goalTitle,
        'goalDescription' => isset($_POST['goalDescription']) ? Utility::clean_string($_POST['goalDescription']) : null,
        'propriety' => isset($_POST['propriety']) ? Utility::clean_string($_POST['propriety']) : 'Medium',
        'weight' => isset($_POST['weight']) ? floatval($_POST['weight']) : 0.0000,
        'jurisdictionID' => isset($_POST['jurisdictionID']) ? intval($_POST['jurisdictionID']) : null,
        'visibility' => isset($_POST['visibility']) ? Utility::clean_string($_POST['visibility']) : 'Private',
        'cascadeMode' => isset($_POST['cascadeMode']) ? Utility::clean_string($_POST['cascadeMode']) : 'None',
        'startDate' => $startDate,
        'endDate' => $endDate,
        'status' => isset($_POST['status']) ? Utility::clean_string($_POST['status']) : 'Draft',
        'LastUpdatedByID' => $userDetails->ID
    );

    // Progress metric
    if (isset($_POST['progressMetric'])) {
        $goalData['progressMetric'] = json_decode($_POST['progressMetric'], true);
    }

    // Evaluator config
    if (isset($_POST['evaluatorConfig'])) {
        $goalData['evaluatorConfig'] = json_decode($_POST['evaluatorConfig'], true);
    }

    // Type-specific data
    if ($goalType === 'OKR' && isset($_POST['okrData'])) {
        $goalData['okrData'] = json_decode($_POST['okrData'], true);
    } elseif ($goalType === 'KPI' && isset($_POST['kpiData'])) {
        $goalData['kpiData'] = json_decode($_POST['kpiData'], true);
    }

    // Evaluator weights
    if (isset($_POST['evaluatorWeights'])) {
        $goalData['evaluatorWeights'] = json_decode($_POST['evaluatorWeights'], true);
    }

    $goalUUID = Goal::createGoal($goalData, $DBConn);

    if ($goalUUID) {
        return array('success' => true, 'goalUUID' => $goalUUID, 'message' => 'Goal created successfully');
    } else {
        return array('success' => false, 'message' => 'Failed to create goal');
    }
}

/**
 * Update goal
 */
function updateGoal($DBConn, $userDetails) {
    $goalUUID = isset($_POST['goalUUID']) ? Utility::clean_string($_POST['goalUUID']) : '';

    if (empty($goalUUID)) {
        return array('success' => false, 'message' => 'Goal UUID is required');
    }

    // Prepare update data
    $updateData = array();

    if (isset($_POST['goalTitle'])) {
        $updateData['goalTitle'] = Utility::clean_string($_POST['goalTitle']);
    }
    if (isset($_POST['goalDescription'])) {
        $updateData['goalDescription'] = Utility::clean_string($_POST['goalDescription']);
    }
    if (isset($_POST['propriety'])) {
        $updateData['propriety'] = Utility::clean_string($_POST['propriety']);
    }
    if (isset($_POST['weight'])) {
        $updateData['weight'] = floatval($_POST['weight']);
    }
    if (isset($_POST['status'])) {
        $updateData['status'] = Utility::clean_string($_POST['status']);
    }
    if (isset($_POST['startDate'])) {
        $updateData['startDate'] = Utility::clean_string($_POST['startDate']);
    }
    if (isset($_POST['endDate'])) {
        $updateData['endDate'] = Utility::clean_string($_POST['endDate']);
    }
    if (isset($_POST['progressMetric'])) {
        $updateData['progressMetric'] = json_decode($_POST['progressMetric'], true);
    }
    if (isset($_POST['completionPercentage'])) {
        $updateData['completionPercentage'] = floatval($_POST['completionPercentage']);
    }

    $updateData['LastUpdatedByID'] = $userDetails->ID;

    $result = Goal::updateGoal($goalUUID, $updateData, $DBConn);

    if ($result) {
        return array('success' => true, 'message' => 'Goal updated successfully');
    } else {
        return array('success' => false, 'message' => 'Failed to update goal');
    }
}

/**
 * Delete goal
 */
function deleteGoal($DBConn, $userDetails) {
    $goalUUID = isset($_POST['goalUUID']) ? Utility::clean_string($_POST['goalUUID']) : '';

    if (empty($goalUUID)) {
        return array('success' => false, 'message' => 'Goal UUID is required');
    }

    // Check if Critical goal - would need approval logic here
    $goal = Goal::getGoal($goalUUID, $DBConn);
    if ($goal && $goal->propriety === 'Critical') {
        // In production, check for L+2 approval
    }

    $result = Goal::deleteGoal($goalUUID, $DBConn);

    if ($result) {
        return array('success' => true, 'message' => 'Goal deleted successfully');
    } else {
        return array('success' => false, 'message' => 'Failed to delete goal');
    }
}

/**
 * Get goal
 */
function getGoal($DBConn) {
    $goalUUID = isset($_POST['goalUUID']) ? Utility::clean_string($_POST['goalUUID']) : '';

    if (empty($goalUUID)) {
        return array('success' => false, 'message' => 'Goal UUID is required');
    }

    $goal = Goal::getGoal($goalUUID, $DBConn);

    if ($goal) {
        return array('success' => true, 'goal' => $goal);
    } else {
        return array('success' => false, 'message' => 'Goal not found');
    }
}

/**
 * List goals
 */
function listGoals($DBConn) {
    $ownerID = isset($_POST['ownerID']) ? intval($_POST['ownerID']) : 0;
    $ownerType = isset($_POST['ownerType']) ? Utility::clean_string($_POST['ownerType']) : 'User';

    if (!$ownerID) {
        return array('success' => false, 'message' => 'Owner ID is required');
    }

    $filters = array();
    if (isset($_POST['status'])) {
        $filters['status'] = Utility::clean_string($_POST['status']);
    }
    if (isset($_POST['goalType'])) {
        $filters['goalType'] = Utility::clean_string($_POST['goalType']);
    }
    if (isset($_POST['propriety'])) {
        $filters['propriety'] = Utility::clean_string($_POST['propriety']);
    }

    $goals = Goal::getGoalsByOwner($ownerID, $ownerType, $filters, $DBConn);

    return array('success' => true, 'goals' => $goals ? $goals : array());
}

/**
 * Validate goal weights
 */
function validateWeights($DBConn) {
    $goals = isset($_POST['goals']) ? json_decode($_POST['goals'], true) : array();

    if (empty($goals)) {
        return array('success' => false, 'message' => 'Goals array is required');
    }

    $isValid = Goal::validateWeightSum($goals, $DBConn);

    return array('success' => true, 'isValid' => $isValid);
}

