<?php
/**
 * Goal Matrix Class
 *
 * Manages matrix organization and cross-border goal assignments
 *
 * @package    TIJA_PMS
 * @subpackage Goals
 * @version    1.0.0
 */

class GoalMatrix {

    /**
     * Assign matrix goal
     *
     * @param string $goalUUID Goal UUID
     * @param int $employeeID Employee user ID
     * @param int $matrixManagerID Matrix manager user ID
     * @param object $DBConn Database connection
     * @return int|false Assignment ID or false
     */
    public static function assignMatrixGoal($goalUUID, $employeeID, $matrixManagerID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        require_once 'employee.php';
        $employee = Employee::employees(array('ID' => $employeeID), true, $DBConn);
        if (!$employee) {
            return false;
        }

        $assignmentData = array(
            'goalUUID' => $goalUUID,
            'employeeUserID' => $employeeID,
            'matrixManagerID' => $matrixManagerID,
            'administrativeManagerID' => $employee->supervisorID ?? null,
            'assignmentType' => 'Matrix',
            'allocationPercent' => 100.00,
            'startDate' => date('Y-m-d'),
            'status' => 'Active'
        );

        // Check if assignment already exists
        $existing = $DBConn->retrieve_db_table_rows(
            'tija_goal_matrix_assignments',
            array('assignmentID'),
            array('goalUUID' => $goalUUID, 'employeeUserID' => $employeeID, 'status' => 'Active'),
            true
        );

        if ($existing) {
            return $existing->assignmentID;
        }

        $result = $DBConn->insert_data('tija_goal_matrix_assignments', $assignmentData);
        return $result ? $DBConn->lastInsertId() : false;
    }

    /**
     * Get matrix goals for employee
     *
     * @param int $employeeID Employee user ID
     * @param object $DBConn Database connection
     * @return array|false Matrix goals or false
     */
    public static function getMatrixGoals($employeeID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array('assignmentID', 'goalUUID', 'matrixManagerID', 'assignmentType', 'status');
        $where = array('employeeUserID' => $employeeID, 'status' => 'Active');

        $assignments = $DBConn->retrieve_db_table_rows(
            'tija_goal_matrix_assignments',
            $cols,
            $where,
            false
        );

        if (!$assignments) {
            return false;
        }

        require_once 'goal.php';
        $goals = array();

        foreach ($assignments as $assignment) {
            $goal = Goal::getGoal($assignment->goalUUID, $DBConn);
            if ($goal) {
                $goal->matrixAssignmentID = $assignment->assignmentID;
                $goal->matrixManagerID = $assignment->matrixManagerID;
                $goals[] = $goal;
            }
        }

        return $goals;
    }

    /**
     * Get matrix team
     *
     * @param int $managerID Manager user ID
     * @param int $projectID Project ID (optional)
     * @param object $DBConn Database connection
     * @return array|false Team members or false
     */
    public static function getMatrixTeam($managerID, $projectID = null, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array('assignmentID', 'employeeUserID', 'goalUUID', 'projectID', 'status');
        $where = array('matrixManagerID' => $managerID, 'status' => 'Active');

        if ($projectID) {
            $where['projectID'] = $projectID;
        }

        $assignments = $DBConn->retrieve_db_table_rows(
            'tija_goal_matrix_assignments',
            $cols,
            $where,
            false
        );

        if (!$assignments) {
            return false;
        }

        require_once 'employee.php';
        $team = array();

        foreach ($assignments as $assignment) {
            $employee = Employee::employees(array('ID' => $assignment->employeeUserID), true, $DBConn);
            if ($employee) {
                $team[] = array(
                    'employeeID' => $employee->ID,
                    'employeeName' => $employee->employeeName ?? ($employee->FirstName . ' ' . $employee->Surname),
                    'assignmentID' => $assignment->assignmentID,
                    'goalUUID' => $assignment->goalUUID
                );
            }
        }

        return $team;
    }

    /**
     * Resolve evaluator in matrix context
     *
     * @param string $goalUUID Goal UUID
     * @param string $evaluatorType Evaluator type
     * @param object $DBConn Database connection
     * @return int|false Evaluator user ID or false
     */
    public static function resolveEvaluator($goalUUID, $evaluatorType, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        require_once 'goal.php';
        $goal = Goal::getGoal($goalUUID, $DBConn);
        if (!$goal) {
            return false;
        }

        // Check if this is a matrix-assigned goal
        $assignment = self::getMatrixAssignment($goalUUID, $goal->ownerUserID, $DBConn);

        if ($assignment && $evaluatorType === 'Manager') {
            // For matrix goals, manager is the matrix manager
            return $assignment->matrixManagerID;
        }

        // Default: use administrative manager
        require_once 'employee.php';
        $employee = Employee::employees(array('ID' => $goal->ownerUserID), true, $DBConn);
        return $employee ? ($employee->supervisorID ?? false) : false;
    }

    /**
     * Get matrix assignment
     *
     * @param string $goalUUID Goal UUID
     * @param int $employeeID Employee user ID
     * @param object $DBConn Database connection
     * @return object|false Assignment or false
     */
    public static function getMatrixAssignment($goalUUID, $employeeID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array(
            'assignmentID', 'goalUUID', 'employeeUserID', 'matrixManagerID',
            'administrativeManagerID', 'assignmentType', 'allocationPercent',
            'projectID', 'status'
        );

        $where = array(
            'goalUUID' => $goalUUID,
            'employeeUserID' => $employeeID,
            'status' => 'Active'
        );

        return $DBConn->retrieve_db_table_rows(
            'tija_goal_matrix_assignments',
            $cols,
            $where,
            true
        );
    }
}

